<?php
if(!defined(__FILE__))
{
	define(__FILE__, 1);
	error_reporting(E_ALL);
	set_time_limit(0);
	
	umask(0022); //default permission is 644	
	
	define("DEBUG_MODE", false);
	
	$server_name = php_uname("n");
	list($short_server_name) = explode(".",$server_name);
	define("SERVER_NAME",$server_name);
	define("SHORT_SERVER_NAME",$short_server_name);

	define("INCLUDE_ROOT", dirname(__FILE__)."/");
	define("TIME_ZONE", "America/Los_Angeles");
	date_default_timezone_set("America/Los_Angeles");
	
	define("MYSQL_SET_NAMES", "utf8");

	define("ISMARSTER", 1);
	define("SID", "br03");
	define("LOG_DIR", dirname(dirname(dirname(__FILE__)))."/logs/");	
	
	define("PROD_DB_HOST", "127.0.0.1");
    define("PROD_DB_USER", "root");
    define("PROD_DB_PASS", "Meikai@12345");
	define("PROD_DB_NAME", "affiliate_data_base");
	define("PROD_DB_SOCKET", "/tmp/mysql.sock");

	define('PAGESIZE', 100);
	
	function __autoload($class)
	{
		$class_file = INCLUDE_ROOT . "lib/class.{$class}.php";
		if(file_exists($class_file)) include_once($class_file);
	}

	function paramsFilter($params)
	{
		if (is_string($params)) {
            $params = htmlspecialchars(stripslashes(trim($params)));
		}
        if (is_array($params) && count($params)) {
            foreach ($params as &$val) {
                $val = paramsFilter($val);
			}
        }
        return $params;
	}

	function echoJson($var) {
        if($var) {
        	echo json_encode($var);
		}
        exit(1);
	}

	function mydie($str="")
	{
		if($str) echo $str;
		exit(1);
	}
}
?>
