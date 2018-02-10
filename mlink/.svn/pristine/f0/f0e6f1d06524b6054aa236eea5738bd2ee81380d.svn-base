<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');

$d = new DateTime();
$_GET['tran_to'] = isset($_GET['tran_to'])&&$_GET['tran_to']?$_GET['tran_to']:$d->format('Y-m-d');
$_GET['tran_from'] = isset($_GET['tran_from'])&&$_GET['tran_from']?$_GET['tran_from']:$d->modify('-3 day')->format('Y-m-d');

$objTran = new Transaction;
$AffovTotal = array();
$AffovTotal = $objTran->get_affiliate_ov($_GET);

$total = array();
$total['revenues'] = 0;
$total['sales'] = 0;
$total['clicks'] = 0;
$total['orders'] = 0;

foreach($AffovTotal as $k=>$v){
	$total['revenues'] += $v['revenues'];
	$total['sales'] += $v['sales'];
	$total['clicks'] += $v['clicks'];
	$total['orders'] += $v['orders'];
}



$objTpl->assign('total', $total);
$objTpl->assign('AffovTotal', $AffovTotal);
$objTpl->assign('search', $_GET);
$sys_header['css'][] = BASE_URL.'/css/front.css';

$sys_footer['js'][] = BASE_URL.'/js/back.js';
$sys_footer['js'][] = BASE_URL.'/js/b_tran.js';

$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('sys_footer', $sys_footer);
$objTpl->display('b_aff_ov.html');
?>