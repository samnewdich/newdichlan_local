<?php
namespace NewdichApis;
use NewdichDto\AnsofraDto;
use NewdichSchema\Migration;
use NewdichSchema\Platform;
use NewdichSchema\Settings;

class PaystackGenerateReserved{
    private $dto;
    private $table = Platform::RESERVED_TABLE;
    private $paystackSecretKey = Settings::PAYSTACK_SECRET_KEY;
    private $paystackgenerateReservedEndpoint = Settings::PAYSTACK_GENERATE_RESERVED_LINK;

    public function __construct(AnsofraDto $dto){
        $this->dto = $dto;
    }

    public function process(){
        $secretKey = $this->paystackSecretKey;
        $url = $this->paystackgenerateReservedEndpoint;

        $data = [
            "email" => $this->dto->email,
            "first_name" => $this->dto_first_name,
            "last_name" => $this->dto->last_name,
            "phone" => $this->dto->phone,
            "country" => "NG"
        ];

        // Initialize cURL
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $secretKey",
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
        ]);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($err) {
            return json_encode([
                "status"=>"failed",
                "response"=>"Error: " . $err
            ], JSON_PRETTY_PRINT);
        } else {
            $responseDec = json_decode($response, true);
            if($responseDec["status"] === true){
                //now insert
                $newMigration = new Migration(null, $this->table);
                $res = $responseDec["dedicated_accounts"];
                for($i=0; $i < count($res); $i++){
                    $eachDataToSave = [
                        "email" => $this->dto->email,
                        "fullname" => $this->dto->fullname,
                        "mac" => $this->dto->mac,
                        "gateway" => $res[$i]["gateway"],
                        "ip_used" => $this->dto->last_ip,
                        "account_name" => $res[$i]["account_name"],
                        "account_number" => $res[$i]["account_number"],
                        "bank" => $res[$i]["bank"]["name"],
                        "date_created" => $this->dto->current_time,
                        "account_type" => "",
                        "status" => "",
                        "gateway" => $this->dto->gateway,
                        "marchant_code" => $this->dto->marchant_code
                    ];
                    $eachUniqueCol="mac";
                    $eachUniqueVal = $this->dto->mac;
                    $eachSave = $newMigration->saveUnique($eachUniqueCol, $eachUniqueVal, $eachDataToSave);
                }
                return $response;
            }
            else{
                return $response;
            }
        }
        curl_close($ch);
    }
}
?>
