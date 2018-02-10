<?php
if(!defined(__FILE__))
{
	define(__FILE__, 1);
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
	set_time_limit(0);
	
	umask(0022); //default permission is 644	
	
	define("DEBUG_MODE", false);
	
	$server_name = php_uname("n");
	list($short_server_name) = explode(".",$server_name);
	define("SERVER_NAME",$server_name);
	define("SHORT_SERVER_NAME",$short_server_name);

	define("INCLUDE_ROOT", dirname(dirname(__FILE__))."/");
	define("TIME_ZONE", "America/Los_Angeles");
	date_default_timezone_set("America/Los_Angeles");

	define("ISMARSTER", 1);
	define("SID", "bdg01");
	define("LOG_DIR", dirname(dirname(dirname(__FILE__)))."/logs/");
	
	if(ISMARSTER){
		define("PROD_DB_HOST", "dev01.mgsvr.com");		//10.28.110.178 bdg_01
		define("PROD_DB_USER", "felix");
		define("PROD_DB_PASS", "8Arj6gXv");
		define("PROD_DB_NAME", "felix_base");
	}else{
		define("PROD_DB_HOST", "bdg01.i.mgsvr.com");	//bdg_01
		define("PROD_DB_USER", "bdg_slave");
		define("PROD_DB_PASS", "SHDbdsg32B");
		define("PROD_DB_NAME", "bdg_go_base");
	}
	
	define("TASK_DB_HOST", "dev01.mgsvr.com");		//bcg01
	define("TASK_DB_USER", "felix");
	define("TASK_DB_PASS", "8Arj6gXv");
	define("TASK_DB_NAME", "felix_base");
	
	define("PENDING_DB_HOST", "dev01.mgsvr.com");
	define("PENDING_DB_USER", "felix");
	define("PENDING_DB_PASS", "8Arj6gXv");
	define("PENDING_DB_NAME", "felix_tracking");
	
	
	function __autoload($class)
	{
		$class_file = INCLUDE_ROOT . "lib/Class.{$class}.php";
		if(file_exists($class_file)) include_once($class_file);
	}
	
	function mydie($str="")
	{
		if($str) echo $str;
		exit(1);
	}
}
?>
