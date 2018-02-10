<?php
define('INCLUDE_ROOT', dirname(dirname(__FILE__)).'/');
define('APP_ROOT', dirname(dirname(__FILE__)).'/app/');
// define('TIME_ZONE', 'America/Los_Angeles');
// date_default_timezone_set(TIME_ZONE);

define('REWRITE_MODE', false);
define('DEBUG_MODE', true);

// define('DB_HOST', '192.168.1.86');
// define('DB_NAME', 'beta_bd2');
// define('DB_USER', 'beta_usr');
// define('DB_PASS', 'BJd98RFJ9dBm');

// define('REDIS_DB_ADDR', '192.168.1.86');
// define('REDIS_DB_PORT', 6379);

define('CRAWL_FILE_DIR','/home/bdg/transaction/server_transaction/data');

function __autoload($class)
{
	$class_file = INCLUDE_ROOT . 'lib/Class.' . $class . '.php';
	
	if(file_exists($class_file))
		return include_once($class_file);


	$class_file = INCLUDE_ROOT . 'lib/LinkFeed/Class.' . $class . '.php';
	
	if(file_exists($class_file))
		return include_once($class_file);
}

// global $db;
// $db = new Mysql(DB_NAME, DB_HOST, DB_USER, DB_PASS);
?>
