<?php
namespace NewdichApp\Query;
use NewdichSchema\Migration;
use NewdichSchema\Platform;
use NewdichDto\AnsofraDto;

class CheckPaid{
    private AnsofraDto $dto;
    private $table = Platform::PAID_DEVICES_TABLE;
    public function __construct(AnsofraDto $dto){
        $this->dto = $dto;
    }

    public function process(){
        $condition = [
            "mac" => $this->dto->mac,
            "marchant_code" => $this->dto->marchant_code
        ];
        $newMigration = new Migration(null, $this->table);
        $get = $newMigration->get($condition, 0, 1);
        $getDec = json_decode($get, true);
        if($getDec["status"] ==="success"){
          $response = $getDec["response"][0];
          $active = (int) $response["active"];
          $expiresAt = (int) $response["expires_at"]; //timestamp in seconds to expire
          $hoursPaidFor = (int) $response["hours_paid_for"];
          if($expiresAt > (int) $this->dto->current_time){
            $response["paid"] = true;
            return json_encode($response, JSON_PRETTY_PRINT);
          }
          else{
            //now update that it has expired
            $dataediting = ["active" => 0];
            $edit = $newMigration->edit($dataediting, $condition);
            $response["paid"] = false;
            return json_encode($response, JSON_PRETTY_PRINT);
          }
        }
        else{
          return $get;
        }
    }
}
?>


<?php
/*
header('Content-Type: application/json');
require_once '../config.php';

$mac = $_GET['mac'] ?? '';

if (!$mac) {
  http_response_code(400);
  echo json_encode(['paid' => false, 'error' => 'MAC missing']);
  exit;
}

$pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
$stmt = $pdo->prepare("SELECT * FROM paid_devices WHERE mac = ? AND active = 1 AND expires_at > NOW()");
$stmt->execute([$mac]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
  'paid' => (bool)$row,
  'expires_at' => $row ? $row['expires_at'] : null
]);
*/
?>