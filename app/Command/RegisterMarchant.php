<?php
namespace NewdichApp\Command;
use NewdichDto\AnsofraDto;
use NewdichSchema\Migration;
use NewdichSchema\Platform;

class RegisterMarchant{
    private $dto;
    private $table = Platform::MARCHANT_TABLE;

    public function __construct(AnsofraDto $dto){
        $this->dto = $dto;
    }

    public function process(){
        $dataToSave = [
            "email" => $this->dto->email,
            "password" => $this->dto->password,
            "fullname" => $this->dto->fullname,
            "phone" => $this->dto->phone,
            "marchant_code" => $this->dto->marchant_code,
            "date_created" => $this->dto->date_created,
            "address" => $this->dto->address,
            "city" => $this->dto->city,
            "state" => $this->dto->state,
            "country" => $this->dto->country,
            "status" => $this->dto->status,
            "account_type" => $this->dto->account_type,
            "business_name" => $this->dto->business_name,
            "fee_rate" => $this->dto->fee_rate,
            "refer_code" => $this->dto->refer_code,
            "amount_paid" => $this->dto->amount_paid ?? 0,
            "refer_by" => $this->dto->refer_by ?? ""
        ];

        $uniqueCol ="email";
        $uniqueVal = $this->dto->email;

        $newMigration = new Migration(null, $this->table);
        $save = $newMigration->saveUnique($uniqueCol, $uniqueVal, $dataToSave);
        return $save;
    }
}
?>