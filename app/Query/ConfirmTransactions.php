<?php
namespace NewdichApp\Query;
use NewdichDto\AnsofraDto;
use NewdichSchema\Migration;
use NewdichSchema\Platform;
use NewdichSchema\Settings;

class ConfirmTransactions{
    private $dto;
    private $table = Platform::USERS_TABLE;
    private $marchant_code = Settings::MARCHANT_CODE;

    public function __construct(AnsofraDto $dto){
        $this->dto = $dto;
    }

    public function process(){
        //gets data from api to confirm those who recently paid
        $dataToCheck = [
            "marchant_code" => $this->marchant_code
        ];
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://lan.newdich.tech/api/confirmtransactions",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS => json_encode($dataToCheck),
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
            $newMigration = new Migration(null, $this->table);
            $res = json_decode($response, true);
            if($res["status"] ==="success"){
                $resres = $res["response"];
                for($i=0; $i < count($resres); $i++){
                    //Update
                    $eachUser = $resres[$i];
                    $email = $eachUser["email"];
                    $mac = $eachUser["mac"];
                    $hashed_mac = $eachUser["hashed_mac"];
                    $sub_status = $eachUser["sub_status"];
                    $has_table_been_updated_locally = "yes";
                    $expires_at = $eachUser["expires_at"];

                    $dataToUpdate = [
                        "has_table_been_updated_locally" => $has_table_been_updated_locally,
                        "sub_status" => $sub_status,
                        "expires_at" => $expires_at,
                    ];

                    $condition = [
                        "email" => $email,
                        "mac" => $mac,
                        "hashed_mac" => $hashed_mac,
                        "marchant_code" => $this->marchant_code
                    ];

                    $newMigration->edit($dataToUpdate, $condition);
                }

                return $res;
            }
            else{
                return $res;
            }
        }
    }
}
?>