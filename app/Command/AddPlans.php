<?php
namespace NewdichApp\Command;
use NewdichDto\AnsofraDto;
use NewdichSchema\Migration;
use NewdichSchema\Platform;

class AddPlans{
    private $dto;
    private $table = Platform::PLANS_TABLE;

    public function __construct(AnsofraDto $dto){
        $this->dto = $dto;
    }

    public function process(){
        // Initialize cURL
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://lan.newdich.tech/api/getplans",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
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
            if($responseDec["status"] ==="success"){

                $newMigration = new Migration(null, $this->table);
                $res = $responseDec["response"];
                for($i=0; $i < count($res); $i++){
                    //firstly check if the plan exist, if it exists update
                    $existplan = [
                        "plan"=> $res[$i]["plan"],
                        "marchant_code" => $res[$i]["marchant_code"]
                    ];
                    
                    $check = $newMigration->get($existplan, 0, 1);
                    $checkDec = json_decode($check, true);
                    if($checkDec["status"] ==="success"){
                        $updateData = [
                            "duration" => $res[$i]["duration"],
                            "quantity" => $res[$i]["quantity"],
                            "price" => $res[$i]["price"],
                            "currency" => $res[$i]["currency"],
                            "discount" => $res[$i]["discount"]
                        ];
                        
                        $edit = $newMigration->edit($updateData, $existplan);
                    }
                    else{
                        $dataToSave = [
                            "plan"=> $res[$i]["plan"],
                            "duration" => $res[$i]["duration"],
                            "quantity" => $res[$i]["quantity"],
                            "price" => $res[$i]["price"],
                            "currency" => $res[$i]["currency"],
                            "discount" => $res[$i]["discount"],
                            "marchant_code" => $res[$i]["marchant_code"]
                        ];

                        $uniqueCol ="plan";
                        $uniqueVal = $res[$i]["plan"];
                        $save = $newMigration->saveUnique($uniqueCol, $uniqueVal, $dataToSave);
                    }
                }
            }
            else{
                return $response;
            }
        }
    }
}
?>