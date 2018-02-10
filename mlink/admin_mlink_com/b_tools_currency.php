<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');

$d = new DateTime();
$_GET['tran_to'] = isset($_GET['tran_to'])&&$_GET['tran_to']?$_GET['tran_to']:$d->modify('-1 day')->format('Y-m-d');
$_GET['tran_from'] = isset($_GET['tran_from'])&&$_GET['tran_from']?$_GET['tran_from']:$d->modify('-6 day')->format('Y-m-d');

$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_header['js'][] = BASE_URL.'/js/b_account.js';
$sys_header['js'][] = BASE_URL.'/js/Chart.js';

$sys_footer['js'][] = BASE_URL.'/js/back.js';
$sys_footer['js'][] = BASE_URL.'/js/b_performance.js';

$objTools = new Tools;
$chartData = $objTools->get_currency_daily_chart($_GET['tran_from'],$_GET['tran_to']);
$curs = array_keys($chartData);
$labels = array_keys($chartData[$curs[0]]);

$chartInfo = array();
foreach($curs as $cur){
	$chartInfo['cur'][$cur]['color'] = rand(0,250).','.rand(0,250).','.rand(0,250);
	$chartInfo['cur'][$cur]['data'] = '['.join(',',$chartData[$cur]).']';
}
$chartInfo['label'] = '["'.join('","',$labels).'"]';


$objTpl->assign('chartInfo', $chartInfo);
$objTpl->assign('chartData', $chartData);
$objTpl->assign('curs', $curs);
$objTpl->assign('labels', $labels);
$objTpl->assign('search', $_GET);
$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('sys_footer', $sys_footer);
$objTpl->display('b_tools_currency.html');
?>