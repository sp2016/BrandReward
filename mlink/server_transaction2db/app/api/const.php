<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'bdg_go_base');
define('DB_USER', 'root');
define('DB_PASS', 'Meikai@12345');

global $db;
$db = new Mysql(DB_NAME, DB_HOST, DB_USER, DB_PASS);
?>
