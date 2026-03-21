<?php
namespace NewdichApp\Query;
use NewdichDto\AnsofraDto;
use NewdichSchema\Migration;
use NewdichSchema\Platform;

class GetUser{
    private $dto;
    private $table = Platform::USERS_TABLE;

    public function __construct(AnsofraDto $dto){
        $this->dto = $dto;
    }

    public function process(){
        $dataToCheck = [
            "mac" => $this->dto->mac,
            "marchant_code" => $this->dto->marchant_code
        ];

        $newMigration = new Migration(null, $this->table);
        $get = $newMigration->get($dataToCheck, 0, 1);
        return $get;
    }
}
?>