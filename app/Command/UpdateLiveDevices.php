<?php
namespace NewdichApp\Command;
use NewdichDto\AnsofraDto;
use NewdichSchema\Migration;
use NewdichSchema\Platform;

class UpdateLiveDevices{
    private $dto;
    private $table = Platform::USERS_TABLE;

    public function __construct(AnsofraDto $dto){
        $this->dto = $dto;
    }

    public function process(){
        $dataToSave = [
            "live_now" => $this->dto->live_now
        ];

        $condition = [
            "mac"=>$this->dto->mac,
            "marchant_code" => $this->dto->marchant_code
        ];

        $newMigration = new Migration(null, $this->table);
        $save = $newMigration->edit($dataToSave, $condition);
        return $save;
    }
}
?>