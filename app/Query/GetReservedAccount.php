<?php
namespace NewdichApp\Query;
use NewdichDto\AnsofraDto;
use NewdichSchema\Migration;
use NewdichSchema\Platform;
use NewdichSchema\Settings;

class GetReservedAccount{
    private $dto;
    private $marchant_code = Settings::MARCHANT_CODE;
    private $table = Platform::RESERVED_TABLE;

    public function __construct(AnsofraDto $dto){
        $this->dto = $dto;
    }

    public function process(){
        $dataToCheck = [
            "mac" => $this->dto->mac,
            "marchant_code"=>$this->marchant_code
        ];

        $newMigration = new Migration(null, $this->table);
        $get = $newMigration->get($dataToCheck, 0, 10);
        $getDec = json_decode($get, true);
        if($getDec["status"] ==="success"){
            $resposne = $getDec["response"];
            $allReservec = [];
            for($i=0; $i < count($resposne); $i++){
                $eachAcc = $resposne[$i];
                $eachArr = [
                    "account_name" => $eachAcc["account_name"];
                    "account_number" => $eachAcc["account_number"];
                    "bank" => [
                        "name" => $eachAcc["bank"];
                    ];
                ];
                $allReservec[] = $eachArr;
            }

            return json_encode([
                "status"=>"success",
                "response"=>$allReservec
            ], JSON_PRETTY_PRINT);
        }
        else{
            return $get;
        }
    }
}
?>