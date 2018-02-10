<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');

$d = new DateTime();
$_GET['now_to'] = isset($_GET['now_to'])&&$_GET['now_to']?$_GET['now_to']:$d->modify('-1 day')->format('Y-m-d');//modify函数返回$d，此时$d已经减小1
$_GET['now_from'] = isset($_GET['now_from'])&&$_GET['now_from']?$_GET['now_from']:$d->modify('-6 day')->format('Y-m-d');
$_GET['his_to'] = isset($_GET['his_to'])&&$_GET['his_to']?$_GET['his_to']:$d->modify('-1 day')->format('Y-m-d');
$_GET['his_from'] = isset($_GET['his_from'])&&$_GET['his_from']?$_GET['his_from']:$d->modify('-6 day')->format('Y-m-d');
$_GET['af'] = isset($_GET['af'])?$_GET['af']:'cj';


$td = array('num','sale','commission','cr','click');
$colume = array('his','now','diff','change');
$href_change = "no";
$col = array();
$colarr = array();
$count = array();
if(isset($_GET['colstr'])){
	$href_change = 'yes';
	$colarr = explode("|", $_GET['colstr']);
// 	 echo "<pre>";
// 	 print_r($colarr);
	foreach ($colarr as $val){   //把num，sale，commission,cr，click取出来，放到数组$col中
		$temp = explode("_", $val);
		$col[] = $temp[0];
	}
	$count = array_count_values($col);
 }




$page = isset($_GET['p'])?$_GET['p']:1;
$objTran = new Transaction;
$transaction = $objTran->get_history_domain_rpt($_GET,$page);
$transactionData = isset($transaction['aff_data'])?$transaction['aff_data']:array();
$transactionTotal = isset($transaction['total'])?$transaction['total']:array();


		


$pageHtml = get_page_html($transaction);
$objTpl->assign('search', $_GET);
$objTpl->assign('transactionData', $transactionData);
$objTpl->assign('transactionTotal', $transactionTotal);

$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_footer['js'][] = BASE_URL.'/js/back.js';
$sys_footer['js'][] = BASE_URL.'/js/b_performance.js';
$objTpl->assign('pageHtml', $pageHtml);
$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('sys_footer', $sys_footer);
$objTpl->assign('href_change', $href_change);
$objTpl->assign('colarr', json_encode($colarr));
$objTpl->assign('td', json_encode($td));
$objTpl->assign('colume', json_encode($colume));
$objTpl->assign('count', $count);
$objTpl->display('b_history_domain.html');
?>