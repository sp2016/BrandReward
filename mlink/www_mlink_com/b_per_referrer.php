<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init_back.php');

check_user_login();
include_once('auth_ini.php');

$d = new DateTime();
$_GET['tran_to'] = isset($_GET['tran_to'])&&$_GET['tran_to']?$_GET['tran_to']:$d->format('Y-m-d');
$_GET['tran_from'] = isset($_GET['tran_from'])&&$_GET['tran_from']?$_GET['tran_from']:$d->modify('-7 day')->format('Y-m-d');


$_GET['uid'] = $USERINFO['ID'];
$objTran = new Transaction;

$search = array();
$search['visitFrom'] = $_GET['tran_from'];
$search['visitTo'] = $_GET['tran_to'];
$search['uid'] = $_GET['uid'];

$tranData = $objTran->getReferrerCommission($search,'daily');
$tmpTranData = array();
foreach($tranData as $v){
	$tmpTranData[$v['VisitedDate']] = $v;
}

$da = array();
$i = $search['visitFrom'];
while($i != $search['visitTo']){
	$da[] = $i;
	$i = date('Y-m-d',strtotime($i." +1 day"));
}
$formatTranData = array();
foreach($da as $d){
	$RefCommission = isset($tmpTranData[$d])?$tmpTranData[$d]['RefCommission']:0;
	$formatTranData[] = array('VisitedDate'=>$d,'RefCommission'=>$RefCommission);
}

$objTpl->assign('search', $_GET);
$objTpl->assign('tranData', $formatTranData);

$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_footer['js'][] = BASE_URL.'/js/back.js';
$sys_footer['js'][] = BASE_URL.'/js/b_performance.js';
$sys_header['js'][] = BASE_URL.'/js/Chart.js';

$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('sys_footer', $sys_footer);
$objTpl->assign('sys_userinfo', $USERINFO);
$objTpl->display('b_per_referrer.html');
?>
