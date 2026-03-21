<?php
namespace NewdichApp\Command;
use NewdichDto\AnsofraDto;
use NewdichSchema\Migration;
use NewdichSchema\Platform;

class EditPlans{
    private $dto;
    private $table = Platform::PLANS_TABLE;

    public function __construct(AnsofraDto $dto){
        $this->dto = $dto;
    }

    public function process(){
        $action = $this->dto->action;
        if($action ==="delete"){
            $condition = [
                "plans_id" => $this->dto->plans_id,
                "marchant_code" => $this->dto->marchant_code
            ];
            $newMigration = new Migration(null, $this->table);
            $save = $newMigration->remove($condition);
            return $save;
        }
        elseif($action ==="update"){
            $dataToSave = [
                "plan"=> $this->dto->plan,
                "duration" => $this->dto->duration,
                "quantity" => $this->dto->quantity,
                "price" => $this->dto->price,
                "currency" => $this->dto->currency,
                "discount" => $this->dto->discount
            ];

            $condition = [
                "plans_id" => $this->dto->plans_id,
                "marchant_code" => $this->dto->marchant_code
            ];
            $newMigration = new Migration(null, $this->table);
            $save = $newMigration->edit($dataToSave, $condition);
            return $save;
        }
    }
}
?>