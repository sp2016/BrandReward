<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init_back.php');

check_user_login();
include_once('auth_ini.php');
$uid = $USERINFO['ID'];
$objTran = new Transaction;
$sites = $objTran->table('publisher_account')->where('PublisherId = '.intval($uid))->find();

$objTpl->assign('sites', $sites);

$sys_header['css'][] = BASE_URL.'/css/front.css';

$sys_footer['js'][] = BASE_URL.'/js/back.js';
$sys_footer['js'][] = BASE_URL.'/js/b_tran.js';

$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('sys_footer', $sys_footer);
$objTpl->display('b_tools.html');
?>