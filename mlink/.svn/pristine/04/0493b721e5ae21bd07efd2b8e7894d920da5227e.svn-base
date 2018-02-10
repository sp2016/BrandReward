<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init_back.php');
check_user_login();
include_once('auth_ini.php');

$d = new DateTime();
$_GET['tran_to'] = isset($_GET['tran_to'])&&$_GET['tran_to']?$_GET['tran_to']:$d->format('Y-m-d');
$_GET['tran_from'] = isset($_GET['tran_from'])&&$_GET['tran_from']?$_GET['tran_from']:$d->modify('-7 day')->format('Y-m-d');
$_GET['id'] = $USERINFO['Name'];
$obj = new Account();

$tranData = $obj->getTrafficRpt($_GET);
if(!empty($tranData['page'])){
	$pageHtml = get_page_html($tranData['page']);
	$objTpl->assign('pageHtml', $pageHtml);
}

$objTpl->assign('search', $_GET);
$objTpl->assign('tranData', $tranData);

$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_footer['js'][] = BASE_URL.'/js/back.js';
$sys_footer['js'][] = BASE_URL.'/js/b_performance.js';

$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('sys_footer', $sys_footer);
$objTpl->assign('sys_userinfo', $USERINFO);
$objTpl->display('b_aff_traffic.html');


?>