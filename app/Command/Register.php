<?php
namespace NewdichApp\Command;
use NewdichDto\AnsofraDto;
use NewdichSchema\Migration;
use NewdichSchema\Platform;
use NewdichSchema\Settings;

class Register{
    private $dto;
    private $marchant_code = Settings::MARCHANT_CODE;
    private $table = Platform::USERS_TABLE;
    private $reservedTable = Platform::RESERVED_TABLE;

    public function __construct(AnsofraDto $dto){
        $this->dto = $dto;
    }

    public function process(){
        if(strtolower($this->dto->mac) ==="unknown" || strtolower($this->dto->mac) ==="" || strtolower($this->dto->mac) ==="null" || strtolower($this->dto->mac) ==="undefined" || strtolower($this->dto->mac) ===" "){
            return json_encode([
                "status"=>"failed",
                "response"=>"Your Device is hiding identity. You are probably using VPN or a MAC blocker Application"
            ], JSON_PRETTY_PRINT);
        }

        $emailToUse = "lan_". $this->marchant_code . "_". md5($this->dto->mac) ."@newdich.tech";
        $fullnameToUse = "lan_". $this->marchant_code . " ". md5($this->dto->mac);
        $dataToSave = [
            "email" => $emailToUse,
            "fullname" => $fullnameToUse,
            "mac" => $this->dto->mac,
            "hashed_mac" => md5($this->dto->mac),
            "last_ip" => $this->dto->last_ip ? $this->dto->last_ip : "",
            "device_name" => $this->dto->device_name ? $this->dto->device_name : "",
            "total_spent" => "",
            "last_sub_plan" => "",
            "total_data_used" => "",
            "date_created" => $this->dto->date_created,
            "last_seen" => $this->dto->date_created,
            "picture" => "",
            "username" => md5($this->dto->mac),
            "account_type" => "",
            "phone" => $this->dto->phone ? $this->dto->phone : "",
            "has_reserved_account" => "",
            "refer_code" => "",
            "refer_by" => $this->dto->refer_by ? $this->dto->refer_by : "",
            "sub_status" => "",
            "has_table_been_updated_locally" => "",
            "marchant_code" => $this->marchant_code
        ];

        //send to API, so that API can register it
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://lan.newdich.tech/api/register",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS => json_encode($dataToSave),
        ]);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($err) {
            return json_encode([
                "status"=>"failed",
                "response"=>"Error: " . $err
            ], JSON_PRETTY_PRINT);
        }
        else
        {
            $responseDec = json_decode($response, true);
            if($responseDec["status"] ==="success"){
                //now save locally
                $uniqueCol ="email";
                $uniqueVal = $emailToUse;
                $newMigration = new Migration(null, $this->table);
                $save = $newMigration->saveUnique($uniqueCol, $uniqueVal, $dataToSave);
                $saveDec = json_decode($save, true);
                if($saveDec["status"] ==="success"){
                    //now generate reserved account for him from the API
                    $explodeFullname = explode(' ', $fullnameToUse);
                    $dataToGen = [
                        "email" => $emailToUse,
                        "fullname" => $fullnameToUse,
                        "first_name" => $explodeFullname[1],
                        "last_name" => $explodeFullname[0],
                        "phone" => $this->dto->phone ? $this->dto->phone : "",
                        "country" => "NG",
                        "mac" => $this->dto->mac,
                        "hashed_mac" => md5($this->dto->mac),
                        "last_ip" => $this->dto->last_ip ? $this->dto->last_ip : "",
                        "marchant_code" => $this->marchant_code,
                        "date_created" => $this->dto->date_created,
                        "current_time" => $this->dto->date_created
                    ];
                    $ch = curl_init();
                    curl_setopt_array($ch, [
                        CURLOPT_URL => "https://lan.newdich.tech/api/generatepaystackreserve",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_POST => true,
                        CURLOPT_HTTPHEADER => [
                            "Content-Type: application/json"
                        ],
                        CURLOPT_POSTFIELDS => json_encode($dataToGen),
                    ]);
                    
                    $response = curl_exec($ch);
                    $err = curl_error($ch);
                    curl_close($ch);
                    if ($err) {
                        return json_encode([
                            "status"=>"failed",
                            "response"=>"Error: " . $err
                        ], JSON_PRETTY_PRINT);
                    }
                    else{
                        $res = json_decode($response, true);
                        if($res["status"] ==="success"){
                            $resres = $res["response"];
                            $newReserve = new Migration(null, $this->reservedTable);
                            for($i=0; $i < count($resres); $i++){
                                $eachDataToSave = [
                                    "email" => $emailToUse,
                                    "fullname" => $fullnameToUse,
                                    "mac" => $this->dto->mac,
                                    "ip_used" => $this->dto->last_ip,
                                    "account_name" => $resres[$i]["account_name"],
                                    "account_number" => $resres[$i]["account_number"],
                                    "bank" => $resres[$i]["bank"]["name"],
                                    "date_created" => $this->dto->date_created ? $this->dto->date_created : "",
                                    "account_type" => "",
                                    "status" => "",
                                    "gateway" => $this->dto->gateway ? $this->dto->gateway : '',
                                    "marchant_code" => $this->dto->marchant_code
                                ];
                                $eachUniqueCol="mac";
                                $eachUniqueVal = $this->dto->mac;
                                $eachSave = $newReserve->saveUnique($eachUniqueCol, $eachUniqueVal, $eachDataToSave);
                            }

                            return $res;
                        }
                        else{
                            return $response;
                        }
                    }
                }
                else{
                    return $save;
                }
            }
            else{
                return $response;
            }
        }
    }
}
?>