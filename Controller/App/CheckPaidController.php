<?php
namespace NewdichControllerApp;
use NewdichDto\AnsofraDto;
use NewdichMiddleware\Index;
use NewdichApp\Query\CheckPaid;

// Sanitize incoming GET parameters
$middle = new Index();
$incoming = $_GET;
$extractedData = [];
foreach($incoming as $key => $value){
    $extractedData[$key] = $middle->cleanData($value);
}

// Ensure current_time is set (from Node.js)
if(!isset($extractedData['current_time']) && isset($extractedData['time'])){
    $extractedData['current_time'] = $extractedData['time'];
}

// Create DTO
$newDto = new AnsofraDto($extractedData);

// Process subscription check
$newCheckPaid = new CheckPaid($newDto);

// Return JSON response
header('Content-Type: application/json');
try {
    $response = $newCheckPaid->process();
    echo json_encode($response); // always output JSON
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "response" => $e->getMessage()
    ]);
}
exit;
?>