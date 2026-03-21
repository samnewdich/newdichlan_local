<?php
namespace NewdichSchema;
// MANUAL INCLUDES (REQUIRED)
require_once __DIR__ . '/Settings.php';
use NewdichSchema\Settings;
use PDO;
use PDOException;

$ileos = Settings::SERVER;
$ileone = Settings::SERVER_USER;
$ilekokoro = Settings::SERVER_PASS;
$iledb = Settings::SERVER_DB;

//when you already have db
$connnewdich = new PDO("mysql:host=$ileos; dbname=$iledb", $ileone, $ilekokoro);
$connnewdich->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//when you don't have db
$connnewdichdb = new PDO("mysql:host=$ileos", $ileone, $ilekokoro);
$connnewdichdb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>