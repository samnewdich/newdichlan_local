<?php
namespace NewdichApp\Query;
use NewdichDto\AnsofraDto;
use NewdichSchema\Migration;
use NewdichSchema\Platform;

class GetActiveUser{
    private $dto;
    private $table = Platform::USERS_TABLE;

    public function __construct(Ansofra $dto){
        $this->dto = $dto;
    }

    public function process(){
        $dataToCheck = [
            "mac" => $this->dto->mac,
            "sub_status" => $this->dto->sub_status,
            "marchant_code" => $this->dto->marchant_code
        ];
        $offset = $this->dto->offset ? (int) $this->dto->offset : 0;
        $limit = $this->dto->limit ? (int) $this->dto->limit : 1000;

        $newMigration = new Migration(null, $this->table);
        $get = $newMigration->get($dataToCheck, $offset, $limit);
        return $get;
    }
}
?>