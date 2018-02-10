<?php
global $_db,$_cf;
$_db = new Mysql(DB_NAME, DB_HOST, DB_USER, DB_PASS);
$_cf = new LibFactory;

global $_req;
$_req = array_merge($_GET,$_POST);

global $argv;
if(isset($argv) && count($argv) > 1){
    $param = parseArgv($argv);
    $_req = array_merge($_req,$param);
}

$_req = _trim($_req);
?>