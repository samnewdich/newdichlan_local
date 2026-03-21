<?php
namespace NewdichApp;
$serverDir = $_SERVER["DOCUMENT_ROOT"]; //server directory
//let apis request go to apis controller
//let app request go to app controller
//let src request go to src controler
$rootDir ="/"; //the root directory of the project
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
require_once $serverDir.$rootDir."/app/swagger/index.html";
?>