<?php
define('INCLUDE_ROOT', dirname(dirname(__FILE__)).'/');
define('APP_ROOT', INCLUDE_ROOT.'app/');
define('DATA_ROOT', INCLUDE_ROOT.'/data/');
// define('TIME_ZONE', 'America/Los_Angeles');
date_default_timezone_set('America/Los_Angeles');

define('REWRITE_MODE', false);
define('DEBUG_MODE', true);

define('DB_HOST', '192.168.1.10');
define('DB_NAME', 'bdg_go_base');
define('DB_USER', 'gordon');
define('DB_PASS', 'xRj0ZfKY');

define('MYSQL_ENCODING','UTF8');
ini_set('memory_limit', '1024M');

define('DATA_TRANSACTION_PATH',dirname(dirname(dirname(INCLUDE_ROOT))).'/sem/murphy/data');

include_once(INCLUDE_ROOT.'etc/install.php');

function __autoload($class)
{
	$class_file = INCLUDE_ROOT . 'lib/Class.' . $class . '.php';
	
	if(file_exists($class_file))
		return include_once($class_file);
}

?>