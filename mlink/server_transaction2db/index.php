<?php
require "etc/const.php";
//require "etc/aff_conf.php";
require "func.php";
require "route.php";

$objRoute = new route();

$appCore = $objRoute->getAppCore();

$appCore->run();
?>
