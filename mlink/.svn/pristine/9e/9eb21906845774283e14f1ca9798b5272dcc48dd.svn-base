<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init_back.php');
check_user_login();
include_once('auth_ini.php');
$typeList = array('daily','merchants','sites');
if(!isset($_GET['type']) || !in_array($_GET['type'],$typeList)){
	$_GET['type'] = 'daily';
}

$d = new DateTime();
$_GET['tran_to'] = isset($_GET['tran_to'])&&$_GET['tran_to']?$_GET['tran_to']:$d->format('Y-m-d');
$_GET['tran_from'] = isset($_GET['tran_from'])&&$_GET['tran_from']?$_GET['tran_from']:$d->modify('-7 day')->format('Y-m-d');


$_GET['uid'] = $USERINFO['ID'];
$objStatis = new Statis;

$tranData = $objStatis->getTransactionRpt($_GET);
/* if(!empty($tranData['page'])){
	$pageHtml = get_page_html($tranData['page']);
	$objTpl->assign('pageHtml', $pageHtml);
} */
/* $sql = "select ApiKey,Domain from publisher_account WHERE PublisherId =".$_GET['uid'];
$site = $db->getRows($sql);
$objTpl->assign('site', $site); */
$objTpl->assign('search', $_GET);
$objTpl->assign('tranData', $tranData);

$sys_header['css'][] = BASE_URL.'/css/daterangepicker.css';
$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_footer['js'][] = BASE_URL.'/js/back.js';
$sys_footer['js'][] = BASE_URL.'/js/b_performance.js';
$sys_header['js'][] = BASE_URL.'/js/Chart.js';
$sys_header['js'][] = BASE_URL.'/js/highcharts.js';
$sys_header['js'][] = BASE_URL.'/js/moment.min.js';
$sys_header['js'][] = BASE_URL.'/js/daterangepicker.js';

$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('sys_footer', $sys_footer);
$objTpl->assign('sys_userinfo', $USERINFO);
$objTpl->display('b_performance.html');
?>