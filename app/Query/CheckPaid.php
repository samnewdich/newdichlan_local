<?php
namespace NewdichApp\Query;
use NewdichSchema\Migration;
use NewdichSchema\Platform;
use NewdichDto\AnsofraDto;
use NewdichSchema\Settings;

class CheckPaid{
    private AnsofraDto $dto;
    private $table = Platform::USERS_TABLE;
    private $marchant_code = Settings::MARCHANT_CODE;
    public function __construct(AnsofraDto $dto){
        $this->dto = $dto;
    }

    public function process(){
        $condition = [
            "mac" => $this->dto->mac,
            "marchant_code" => $this->marchant_code
        ];
        $newMigration = new Migration(null, $this->table);
        $get = $newMigration->get($condition, 0, 1);
        $getDec = json_decode($get, true);
        if($getDec["status"] ==="success"){
          $response = $getDec["response"][0];
          $sub_status = $response["sub_status"];
          $active = (int) $response["active"];
          $expiresAt = (int) $response["expires_at"]; //timestamp in seconds to expire
          $hoursPaidFor = (int) $response["hours_paid_for"];
          if($expiresAt > (int) $this->dto->current_time){
            $response["sub_status"] = $sub_status;
            return json_encode($response, JSON_PRETTY_PRINT);
          }
          else{
            //now update that it has expired
            $dataediting = ["sub_status" =>"inactive"];
            $edit = $newMigration->edit($dataediting, $condition);
            $response["sub_status"] = "inactive";
            return json_encode($response, JSON_PRETTY_PRINT);
          }
        }
        else{
          return $get;
        }
    }
}
?>