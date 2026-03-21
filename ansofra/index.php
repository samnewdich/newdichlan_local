<?php
require_once __DIR__ ."/../vendor/autoload.php";
require __DIR__ . '/../boostrap.php';
//$url = $_SERVER["REQUEST_URI"];
$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
require_once __DIR__ .'/../route/index.php';
?>