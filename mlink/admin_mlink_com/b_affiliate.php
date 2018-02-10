<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');

$typeList = array('daily','merchants','sites');
if(!isset($_GET['type']) || !in_array($_GET['type'],$typeList)){
	$_GET['type'] = 'daily';
}

$d = new DateTime();
$_GET['tran_to'] = isset($_GET['tran_to'])&&$_GET['tran_to']?$_GET['tran_to']:$d->format('Y-m-d');
$_GET['tran_from'] = isset($_GET['tran_from'])&&$_GET['tran_from']?$_GET['tran_from']:$d->modify('-7 day')->format('Y-m-d');


$objTran = new Transaction;
$tranInfo = $objTran->get_affiliate_rpt($_GET);
$tranRow = $tranInfo['tran_row'];
$affRow = $tranInfo['aff_row'];

$afList = array();
$cdList = array();
$tranData = array();
foreach($tranRow as $v){
	if(!isset($afList[$v['affid']]))
		$afList[$v['affid']] = $v['revenues']."";
	else
		$afList[$v['affid']] = ($afList[$v['affid']]+$v['revenues'])."";

	if(!isset($cdList[$v['createddate']]))
		$cdList[$v['createddate']] = $v['revenues']."";
	else
		$cdList[$v['createddate']] = ($cdList[$v['createddate']]+$v['revenues'])."";

	$tranData[$v['createddate']][$v['affid']] = $v['revenues'];
}
arsort($afList);


$objTpl->assign('search', $_GET);
$objTpl->assign('tranData', $tranData);
$objTpl->assign('afList', $afList);
$objTpl->assign('cdList', $cdList);
$objTpl->assign('affRow', $affRow);

$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_footer['js'][] = BASE_URL.'/js/back.js';
$sys_footer['js'][] = BASE_URL.'/js/b_performance.js';

$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('sys_footer', $sys_footer);
$objTpl->display('b_affiliate.html');
?>