<?php
namespace NewdichControllerApp;
use NewdichDto\AnsofraDto;
use NewdichMiddleware\Index;
use NewdichApp\Query\LoginMarchant;
use NewdichAuth\Authentication;

$incoming = $_POST;
$cleanData = [];
$newMiddleware = new Index();

foreach($incoming as $k => $v){
    if($k ==="password"){
        $cleanData[$k] = $v;
    }
    else{
        $cleanData[$k] = $newMiddleware->cleanData($v);
    }
}

$dto = new AnsofraDto($cleanData);
$newLogin = new LoginMarchant($dto);
$log = $newLogin->process();
$logDec = json_decode($log, true);
if($logDec["status"] ==="success"){
    $response = $logDec["response"][0];
    $password = $response["password"];
    $password2 = $cleanData["password"];
    if($newMiddleware->verifyHash($password2, $password) || $newMiddleware->verifyHash($password2, $password) === true){
        //now authenticate
        $role ="marchant";
        $newAuthentication = new Authentication();
        $auth = $newAuthentication->auth($cleanData["email"], $role);
        echo $log;
        exit;
    }
    else{
        echo json_encode([
            "status"=>"failed",
            "response"=>"Incorrect password"
        ], JSON_PRETTY_PRINT);
        exit;
    }
}
else{
    echo $log;
    exit;
}
?>