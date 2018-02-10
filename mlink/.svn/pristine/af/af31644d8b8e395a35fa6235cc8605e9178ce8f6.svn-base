<?php
error_reporting(E_ALL);
set_time_limit(86400);

umask(0066); //default permission is 600
define("DEBUG_MODE", false);

$server_name = php_uname("n");
list($short_server_name) = explode(".",$server_name);

define("SERVER_NAME",$server_name);
define("SHORT_SERVER_NAME",$short_server_name);

define("TIME_ZONE", "America/Los_Angeles");
define("MYSQL_TIME_ZONE", "America/Los_Angeles");
date_default_timezone_set(TIME_ZONE);

define("INCLUDE_ROOT", dirname(dirname(__FILE__))."/");
define("APP_ROOT", dirname(INCLUDE_ROOT) . "/");
define("PUBLIC_ROOT", INCLUDE_ROOT . "public/");
define("API_ROOT", PUBLIC_ROOT . "api/");
define("LOG_DIR", APP_ROOT . "logs/");
define("DATA_DIR", APP_ROOT . "data/");

define("PROD_DB_HOST", "localhost");
define("PROD_DB_USER", "br_cb");
define("PROD_DB_PASS", "hRTAverv9yCXE");
define("PROD_DB_NAME", "codebook_base");

function __autoload($class)
{
	$class_file = INCLUDE_ROOT . "lib/class.{$class}.php";
	if(file_exists($class_file)) include_once($class_file);
}

function mydie($str="")
{
	if($str) echo $str;
	exit(1);
}
?>