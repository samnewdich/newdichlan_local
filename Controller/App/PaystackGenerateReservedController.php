<?php
namespace NewdichControllerApp;
use NewdichApis\PaystackGenerateReserved;
use NewdichDto\AnsofraDto;
use NewdichMiddleware\Index;

$incoming = $_POST;
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
$newRegister = new PaystackGenerateReserved($newDto);
echo $newRegister->process();
exit;
?>