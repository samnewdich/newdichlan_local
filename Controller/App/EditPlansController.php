<?php
namespace NewdichControllerApp;
use NewdichDto\AnsofraDto;
use NewdichMiddleware\Index;
use NewdichApp\Command\EditPlans;
use NewdichAuth\Authorization;

$incoming = json_decode(file_get_contents("php://input"), true);
$cleanData = [];
$newMiddleware = new Index();

foreach($incoming as $k => $v){
    if($k ==="password"){
        $cleanData[$k] = $newMiddleware->hashData($v);
    }
    elseif($k ==="marchant_code_set"){
        $cleanData["marchant_code"] = $newMiddleware->cleanData($v);
    }
    else{
        $cleanData[$k] = $newMiddleware->cleanData($v);
    }
}

//check authorization
$newAuth = new Authorization();
$auth = $newAuth->authorize();
$authDec = json_decode($auth, true);
if($authDec["status"] ==="success"){
    $response = $authDec["response"];
    $role = $response["role"];
    if($role ==="marchant" || $role ==="admin"){
        $dto = new AnsofraDto($cleanData);
        $newAddPlans = new EditPlans($dto);
        echo $newAddPlans->process();
        exit;
    }
    else{
        echo json_encode([
            "status"=>"failed",
            "response"=>"Access Denied"
        ], JSON_PRETTY_PRINT);
        exit;
    }
}
else{
    echo $auth;
    exit;
}
?>