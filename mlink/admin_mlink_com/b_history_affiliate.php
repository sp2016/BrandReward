<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');

$d = new DateTime();
$_GET['now_to'] = isset($_GET['now_to'])&&$_GET['now_to']?$_GET['now_to']:$d->modify('-1 day')->format('Y-m-d');
$_GET['now_from'] = isset($_GET['now_from'])&&$_GET['now_from']?$_GET['now_from']:$d->modify('-6 day')->format('Y-m-d');
$_GET['his_to'] = isset($_GET['his_to'])&&$_GET['his_to']?$_GET['his_to']:$d->modify('-1 day')->format('Y-m-d');
$_GET['his_from'] = isset($_GET['his_from'])&&$_GET['his_from']?$_GET['his_from']:$d->modify('-6 day')->format('Y-m-d');



$objTran = new Transaction;
$transaction = $objTran->get_history_affiliate_rpt($_GET);
$transactionData = isset($transaction['aff_data'])?$transaction['aff_data']:array();
$transactionTotal = isset($transaction['total'])?$transaction['total']:array();


$objTpl->assign('search', $_GET);
$objTpl->assign('transactionData', $transactionData);
$objTpl->assign('transactionTotal', $transactionTotal);

$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_footer['js'][] = BASE_URL.'/js/back.js';
$sys_footer['js'][] = BASE_URL.'/js/b_performance.js';

$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('sys_footer', $sys_footer);
$objTpl->display('b_history_affiliate.html');
?>