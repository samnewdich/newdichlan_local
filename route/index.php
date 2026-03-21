<?php
namespace NewdichRoute;
use NewdichSchema\Settings;

$serverDir = $_SERVER["DOCUMENT_ROOT"]; //server directory
$docRoot = Settings::DOC_ROOT;
//let apis request go to apis controller
//let app request go to app controller
//let src request go to src controler
$rootDir = Settings::ROOT_DIRECTORY; //the root directory of the project
//set it in the .env file
//$rootDir can be / and it can be something like /vtu
//for example, let's say you have one server/host and you have many project in it.
//Example, in your localhost(/var/www/html), let's say you have 3 different projects:
//ecommerce, vtu, fintech.
//inside your localhost(/var/www/html), you will have
// var/www/html/ecommerce
// var/www/html/vtu
// var/www/html/fintech
//so, for ecommerce, the root directory is /ecommerce
//for vtu, the root directory is /vtu and for fintech the root directory is /fintech
//and if it is only one project you have, and the one project is inside (/var/www/html)
// then the root directory will be /
$usersArea = $docRoot . $rootDir ."/api"; //the area that users can access
// let's say your root directory is / . Then the usersArea will be /api
// if your root directory is /ecommerce, your usersArea will be /ecommerce/api
// if your root directory is /vtu, your usersArea will be /vtu/api
$adminArea = $docRoot . $rootDir ."/apiadmin"; //the area that only admin can access
// let's say your root directory is /, your adminArea will be /apiadmin
// if your root directory is /ecommerce, your adminArea will be /ecommerce/apiadmin
//$appController = $serverDir.$rootDir."/Controller/App";
//$srcController = $serverDir.$rootDir."/Controller/Src";
$appController = "/../Controller/App";
$srcController = "/../Controller/Src";
if($url === $rootDir || $url === $rootDir . "/" || $url === $rootDir . "/index.html" || $url === $rootDir . "/index.php" || $url === $docRoot . $rootDir || $url === $docRoot . $rootDir . "/"){
    require_once __DIR__ . "/../ansofra/public/index.html";
    exit();
}
elseif($url === $usersArea || $url === $usersArea . "/"){
    require_once __DIR__ . $appController."/AppLanding.php";
    exit();
}
elseif($url === $adminArea."/run_migration"){
    require_once __DIR__ . $srcController."/RunMigration.php";
    exit();
}
elseif($url === $docRoot . $rootDir ."/online" || $url === $docRoot . $rootDir ."/online"."/" ){
    require_once __DIR__ . "/../ansofra/public/kali-landing.php";
    exit;
}

elseif($url === $docRoot . $rootDir ."/marchant" || $url === $docRoot . $rootDir ."/marchant"."/" ){
    require_once __DIR__ .'/../ansofra/public/marchant/login.html';
    exit;
}

elseif($url === $docRoot . $rootDir ."/marchant_portal" || $url === $docRoot . $rootDir ."/marchant_portal"."/" ){
    require_once __DIR__ .'/../ansofra/public/marchant/portal.html';
    exit;
}

elseif($url === $docRoot . $rootDir ."/marchant_admin" || $url === $docRoot . $rootDir ."/admin_portal"."/" ){
    require_once __DIR__ .'/../ansofra/public/marchant/admin.html';
    exit;
}


// /api endpoints
//register marchant
elseif($url === $usersArea."/registermarchant" || $url === $usersArea."/registermarchant"."/"){
    require_once __DIR__ . $appController."/RegisterMarchantController.php";
    exit;
}
//login marchant
elseif($url === $usersArea."/loginmarchant" || $url === $usersArea."/loginmarchant"."/"){
    require_once __DIR__ . $appController."/LoginMarchantController.php";
    exit;
}

//register normal user
elseif($url === $usersArea."/register" || $url === $usersArea."/register"."/"){
    require_once __DIR__ . $appController."/RegisterController.php";
    exit;
}

//login normal marchant
elseif($url === $usersArea."/login" || $url === $usersArea."/login"."/"){
    require_once __DIR__ . $appController."/LoginController.php";
    exit;
}

//generate paystack reserved account
elseif($url === $usersArea."/generatepaystackreserve" || $url === $usersArea."/generatepaystackreserve"."/"){
    require_once __DIR__ . $appController."/PaystackGenerateReservedController.php";
    exit;
}

//Get User details
elseif($url === $usersArea."/getuser" || $url === $usersArea."/getuser"."/"){
    require_once __DIR__ . $appController."/GetUserController.php";
    exit;
}


//Get Active User details(that still have sub active)
elseif($url === $usersArea."/getactiveuser" || $url === $usersArea."/getactiveuser"."/"){
    require_once __DIR__ . $appController."/GetActiveUserController.php";
    exit;
}



//upate live or not(update user if live or not)
elseif($url === $usersArea."/updatelivedevices" || $url === $usersArea."/updatelivedevices"."/"){
    require_once __DIR__ . $appController."/UpdateLiveDevicesController.php";
    exit;
}

//get live devices
elseif($url === $usersArea."/getlivedevices" || $url === $usersArea."/getlivedevices"."/"){
    require_once __DIR__ . $appController."/GetLiveDevicesController.php";
    exit;
}

elseif($url === $usersArea."/checkpaid" || $url === $usersArea."/checkpaid"."/" ){
    require_once __DIR__ . $appController. "/CheckPaidController.php";
    exit;
}


//add subscription plans
elseif($url === $usersArea."/addplans" || $url === $usersArea."/addplans"."/"){
    require_once __DIR__ . $appController."/AddPlansController.php";
    exit;
}


elseif($url === $usersArea."/getplans" || $url === $usersArea."/getplans"."/"){
    require_once __DIR__ . $appController."/GetPlansController.php";
    exit;
}

elseif($url === $usersArea."/geteachplans" || $url === $usersArea."/geteachplans"."/"){
    require_once __DIR__ . $appController."/GetEachPlansController.php";
    exit;
}

elseif($url === $usersArea."/getreserved" || $url === $usersArea."/getreserved"."/"){
    require_once __DIR__ . $appController."/GetReservedController.php";
    exit;
}

//Edit subscription plans
elseif($url === $usersArea."/editplans" || $url === $usersArea."/editplans"."/"){
    require_once __DIR__ . $appController."/EditPlansController.php";
    exit;
}


elseif($url === $usersArea."/getpaymenthistory" || $url === $usersArea."/getpaymenthistory"."/"){
    require_once __DIR__ . $appController."/GetPaymentHistoryController.php";
    exit;
}


else{
    require_once __DIR__ . "/.."."$rootDir/ansofra/public/error/404.html";
    exit();
}
?>