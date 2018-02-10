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
	
	define("MYSQL_SET_NAMES", "latin1");

	define("ISMARSTER", 1);
	define("SID", "bdg02");
	define("LOG_DIR", dirname(dirname(dirname(__FILE__)))."/logs/");
	
	if(ISMARSTER){
		define("PROD_DB_HOST", "127.0.0.1");		//10.28.110.178 bdg_01
		define("PROD_DB_USER", "root");
		define("PROD_DB_PASS", "Meikai@12345");
		define("PROD_DB_NAME", "pendinglinks");
	}else{
		define("PROD_DB_HOST", "bdg01.i.mgsvr.com");	//bdg_01
		define("PROD_DB_USER", "bdg_slave");
		define("PROD_DB_PASS", "SHDbdsg32B");
		define("PROD_DB_NAME", "bdg_go_base");
	}

	define("MESSAGE_EMAIL", "info@couponsnapshot.com");

	define("TASK_DB_HOST", "127.0.0.1");
	define("TASK_DB_USER", "root");
	define("TASK_DB_PASS", "Meikai@12345");
	define("TASK_DB_NAME", "bdg_go_base");

	define("BCG_DB_HOST", "bcg01.i.mgsvr.com");		//bcg01 bcg01.i.mgsvr.com
	define("BCG_DB_USER", "task_usr");
	define("BCG_DB_PASS", "E9oqIUQO61GP0BA");
	define("BCG_DB_NAME", "task");

	define("PENDING_DB_HOST", "localhost");	//stats01
	define("PENDING_DB_USER", "pl_usr");
	define("PENDING_DB_PASS", "k&ehSl4378$%A");
	define("PENDING_DB_NAME", "pendinglinks");

	define("REDIS_PORT_API_GET", 6379);
	define("REDIS_PORT_API_WRITE", 6380);

	define("REDIS_PORT", 6379);
	define("REDIS_HOST", "localhost");//50.22.149.34

	define('PRODUCTDIR', 'E:\image');

	
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
}
?>
