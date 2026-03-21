<?php
namespace NewdichApp\Query;
use NewdichDto\AnsofraDto;
use NewdichSchema\Migration;
use NewdichSchema\Platform;

class GetPlans{
    private $dto;
    private $table = Platform::PLANS_TABLE;
    private $marchant_code ="";

    public function __construct(AnsofraDto $dto){
        $this->dto = $dto;
    }

    public function process(){
        $dataToCheck = [
            "marchant_code"=>$this->marchant_code
        ];

        $newMigration = new Migration(null, $this->table);
        $get = $newMigration->get($dataToCheck, 0, 1000);
        return $get;
    }
}
?>