<?php
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
?>