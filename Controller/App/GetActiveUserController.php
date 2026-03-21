<?php
namespace NewdichControllerApp;
use NewdichDto\AnsofraDto;
use NewdichMiddleware\Index;
use NewdichApp\Query\GetActiveUser;

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
$newRegister = new GetActiveUser($newDto);
echo $newRegister->process();
exit;
?>