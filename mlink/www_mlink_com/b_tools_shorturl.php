<?php
exit;
include_once('conf_ini.php');
include_once(INCLUDE_ROOT . 'init_back.php');
check_user_login();
include_once('auth_ini.php');

/* $uid = $USERINFO['ID'];
$objTran = new Transaction;
$sites = $objTran->table('publisher_account')->where('PublisherId = ' . intval($uid))->find(); */
$sites = array();
$i = 0;
foreach ($_SESSION['pubAccList'] as $temp){
    $sites[$i]['ID'] = $temp['ID'];
    $sites[$i]['Domain'] = $temp['Domain'];
    $sites[$i]['ApiKey'] = $temp['ApiKey'];
    $i++;
}
$objTpl->assign('sites', $sites);

$sys_header['css'][] = BASE_URL .'/css/front.css';
$sys_header['js'][] = BASE_URL.'/js/clipboard.min.js';
$objTpl->assign('sys_header', $sys_header);
$objTpl->display('b_tools_shorturl.html');
?>