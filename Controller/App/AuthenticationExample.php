<?php
namespace NewdichControllerApp;
use NewdichDto\AnsofraDto;
use NewdichMiddleware\Index;
use NewdichAuth\Authentication;
use NewdichApp\Query\Login;

$incoming = json_decode(file_get_contents("php://input"), true);
$newdto = new AnsofraDto($incoming);
$newMiddleware = new Index();
$newLogin = new Login($newdto, $newMiddleware);
$newLoginProcess = $newLogin->process();
$newLoginRes = json_decode($newLoginProcess, true);
if($newLoginRes["status"] ==="success"){
    //Now authenticate the user via JWT
    $newAuth = new Authentication();
    $auth = $newAuth->auth($newMiddleware->cleanData($incoming["user_email"]), 'user');
    $authRes = json_decode($auth, true);
    if($authRes["status"] ==="success"){
        echo json_encode(["status"=>"success", "response"=>$newLoginRes["response"]], JSON_PRETTY_PRINT);
        exit;
    }
    else{
        echo $auth;
        exit;
    }
}
else{
    echo $newLoginProcess;
    exit;
}
?>