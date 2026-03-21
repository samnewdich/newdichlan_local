<?php
namespace NewdichControllerApp;
use NewdichDto\AnsofraDto; //you only need dto here if you send other data alongside the files, so dto will collect those other data
use NewdichFiles\Upload;
header('Content-Type: application/json');

$incoming = $_POST ?? ''; //This is for other data that comes alongside the file(s)
$files = $_FILES["name_you_used_in_frontend"] ?? null;
$newUpload = new Upload($files);
$process = $newUpload->process();
$processRes = json_decode($process, true);
if($processRes["status"] ==="success"){
    //since it's successful, you can get the response
    //the response returns an array of the names of the files that was uploaded
    //you can then save the names in the database
    $response = $processRes["response"];

    //Then you can continue processing other data that comes alongside the file(s)
}
else{
    echo $process;
    exit;
}
?>