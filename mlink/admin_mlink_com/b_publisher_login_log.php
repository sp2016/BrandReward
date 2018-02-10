<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');
mysql_query("SET NAMES 'latin1'");
$sql = 'SELECT PublisherId,Ip,LoginTime FROM publisher_login_log WHERE PublisherId = "'.$_GET['pid'].'"';
$log = $db->getRows($sql);
$sys_header['css'][] = BASE_URL.'/css/front.css';
$objTpl->assign('log', $log);
$objTpl->assign('title', 'Login Log');
$objTpl->assign('sys_header', $sys_header);

$objTpl->display('b_publisher_login_log.html');