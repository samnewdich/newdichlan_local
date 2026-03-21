<?php
namespace NewdichControllerApp;
use NewdichDto\AnsofraDto;
use NewdichMiddleware\Index;
use NewdichApp\Query\Login;
use NewdichAuth\Authentication;

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

$dto = new AnsofraDto($cleanData);
$newLogin = new Login($dto);
$log = $newLogin->process();
$logDec = json_decode($log, true);
if($logDec["status"] ==="success"){
    $response = $logDec["response"][0];
    $password = $response["password"];
    if($cleanData["password"] === $password){
        //now authenticate
        $role ="user";
        $newAuthentication = new Authentication();
        $auth = $newAuthentication->auth($this->dto->email, $role);
        echo $log;
        exit;
    }
    else{
        echo json_encode([
            "status"=>"failed",
            "response"=>"Password incorrect"
        ], JSON_PRETTY_PRINT);
        exit;
    }
}
else{
    echo $log;
    exit;
}
?>