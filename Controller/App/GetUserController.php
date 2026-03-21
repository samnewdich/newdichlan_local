<?php
namespace NewdichControllerApp;
use NewdichDto\AnsofraDto;
use NewdichMiddleware\Index;
use NewdichApp\Query\GetUser;

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
$newRegister = new GetUser($newDto);
echo $newRegister->process();
exit;
?>