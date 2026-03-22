<?php
namespace NewdichControllerApp;

use NewdichDto\AnsofraDto;
use NewdichMiddleware\Index;
use NewdichApp\Query\GetReservedAccount;

header('Content-Type: application/json');

// ===== GET INPUT =====
$incoming = $_POST;

if (empty($incoming)) {
    $incoming = json_decode(file_get_contents("php://input"), true);
}

$incoming = is_array($incoming) ? $incoming : [];

// ===== CLEAN INPUT =====
$middleware = new Index();
$cleanData = [];

foreach ($incoming as $k => $v) {
    $cleanData[$k] = ($k === "password")
        ? $middleware->hashData($v)
        : $middleware->cleanData($v);
}

try {
    $dto = new AnsofraDto($cleanData);
    $service = new GetReservedAccount($dto);

    $result = $service->process();

    //ALWAYS JSON HERE
    echo json_encode($result);

} catch (\Throwable $e) {
    echo json_encode([
        "status" => "failed",
        "response" => "Server error",
        "error" => $e->getMessage() // remove in production if needed
    ]);
}

exit;

?>