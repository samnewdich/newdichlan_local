<?php
namespace NewdichControllerApp;
use NewdichDto\AnsofraDto;
use NewdichMiddleware\Index;
use NewdichApp\Command\RegisterMarchant;

$incoming = $_POST;
$cleanData = [];
$newMiddleware = new Index();

foreach($incoming as $k => $v){
    if($k ==="password"){
        $cleanData[$k] = $newMiddleware->hashData($v);
    }
    elseif($k ==="account_type"){
        if(strtolower($v) ==="basic"){
            $cleanData["fee_rate"] = 10; //10%
            $cleanData["amount_paid"] = 2500000;
            $cleanData[$k] = $v;
        }
        elseif(strtolower($v) ==="premium"){
            $cleanData["fee_rate"] = 6; //6%
            $cleanData["amount_paid"] = 2200000;
            $cleanData[$k] = $v;
        }
        else{
            $cleanData["fee_rate"] = 4; //4%
            $cleanData["amount_paid"] = 2000000;
            $cleanData[$k] = $v;
        }
    }
    else{
        $cleanData[$k] = $newMiddleware->cleanData($v);
    }
}

$cleanData["status"] ="active";
$cleanData["marchant_code"] = $newMiddleware->marchantCode();
$cleanData["status"] = $newMiddleware->otp();


$newDto = new AnsofraDto($cleanData);
$newRegisterMarchant = new RegisterMarchant($newDto);
echo $newRegisterMarchant->process();
exit;
?>