<?php
namespace NewdichApp\Query;
use NewdichDto\AnsofraDto;
use NewdichSchema\Migration;
use NewdichSchema\Platform;

class Login{
    private $dto;
    private $table = Platform::USERS_TABLE;

    public function __construct(Ansofra $dto){
        $this->dto = $dto;
    }

    public function process(){
        $dataToCheck = [
            "email" => $this->dto->email
        ];

        $newMigration = new Migration(null, $this->table);
        $get = $newMigration->get($dataToCheck, 0, 1);
        return $get;
    }
}
?>