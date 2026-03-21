<?php
namespace NewdichApp\Query;
use NewdichDto\AnsofraDto;
use NewdichSchema\Migration;
use NewdichSchema\Platform;

class GetPaymentHistory{
    private $dto;
    private $table = Platform::PAYMENT_TABLE;

    public function __construct(AnsofraDto $dto){
        $this->dto = $dto;
    }

    public function process(){
        $dataToCheck = [
            "marchant_code" => $this->dto->marchant_code
        ];
        $offset = (int) $this->dto->offset;
        $limit = (int) $this->dto->limit;
        $newMigration = new Migration(null, $this->table);
        $get = $newMigration->get($dataToCheck, $offset, $limit);
        return $get;
    }
}
?>