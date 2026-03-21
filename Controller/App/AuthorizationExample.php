<?php
namespace NewdichControllerApp;
use NewdichSchema\Settings;
use NewdichAuth\Authorization;

$newAuth = new Authorization();
$auth = $newAuth->authorize();
echo $auth; //returns an encoded array of ["status"=>"failed or success", "response"=>"user id or error response"]
exit;
?>