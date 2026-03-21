<?php
namespace NewdichApp\Query;
use NewdichDto\AnsofraDto;
use NewdichSchema\Migration;
use NewdichSchema\Platform;
use NewdichSchema\Settings;

class GetEachPlans{
    private $dto;
    private $marchant_code = Settings::MARCHANT_CODE;
    private $table = Platform::PLANS_TABLE;

    public function __construct(AnsofraDto $dto){
        $this->dto = $dto;
    }

    public function process(){
        $dataToCheck = [
            "plans_id" => $this->dto->plans_id,
            "marchant_code"=>$this->marchant_code
        ];

        $newMigration = new Migration(null, $this->table);
        $get = $newMigration->get($dataToCheck, 0, 1);
        return $get;
    }
}
?>