<?php
namespace NewdichControllerApp;
use NewdichDto\AnsofraDto;
use NewdichMiddleware\Index;
use NewdichApp\Query\CheckPaid;

$middle = new Index();
$incoming = $_GET;
$extractedData = [];
foreach($incoming as $key => $value){
    $extractedData[$key] = $middle->cleanData($value);
}

$newDto = new AnsofraDto($extractedData);
$newCheckPaid = new CheckPaid($newDto);
echo $newCheckPaid->process();
exit;
?>