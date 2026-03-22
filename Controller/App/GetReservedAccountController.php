<?php
namespace NewdichControllerApp;
use NewdichDto\AnsofraDto;
use NewdichMiddleware\Index;
use NewdichApp\Query\GetReservedAccount;

header('Content-Type: application/json');

// Support both form-data and JSON
$incoming = $_POST;

if (empty($incoming)) {
    $incoming = json_decode(file_get_contents("php://input"), true);
}

$incoming = is_array($incoming) ? $incoming : [];

$cleanData = [];
$newMiddleware = new Index();

foreach ($incoming as $k => $v) {
    $cleanData[$k] = ($k === "password")
        ? $newMiddleware->hashData($v)
        : $newMiddleware->cleanData($v);
}

try {
    $newDto = new AnsofraDto($cleanData);
    $newRegister = new GetReservedAccount($newDto);

    $result = $newRegister->process();

    // Ensure consistent output
    if (is_string($result)) {
        echo $result;
    } else {
        echo json_encode($result);
    }

} catch (\Throwable $e) {
    echo json_encode([
        "status" => "failed",
        "response" => $e->getMessage()
    ]);
}

exit;
?>











<?php
/*
namespace NewdichControllerApp;
use NewdichDto\AnsofraDto;
use NewdichMiddleware\Index;
use NewdichApp\Query\GetReservedAccount;

$incoming = json_decode(file_get_contents("php://input"), true);
$cleanData = [];
$newMiddleware = new Index();

foreach($incoming as $k => $v){
    if($k ==="password"){
        $cleanData[$k] = $newMiddleware->hashData($v);
    }
    else{
        $cleanData[$k] = $newMiddleware->cleanData($v);
    }
}

$newDto = new AnsofraDto($cleanData);
$newRegister = new GetReservedAccount($newDto);
echo $newRegister->process();
exit;
*/
?>