<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
define("PAGE_DEBUG",false);
$oMysql = new Mysql();
$oBoAttr= new BoAttr($oMysql);
if(defined("PAGE_DEBUG") && PAGE_DEBUG) $oBoAttr->debug = true;
$oBoAttr->verbose = true;
$oBoAttr->check_update_all_bo();
?>