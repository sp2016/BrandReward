<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');
// $affs = array('cj','ls','ond','sas','td','zanox','afffuk','affwin','avangate','pjn','wg','afffus','avt','dgmnew_au','dgmnew_nz','tt','tt_de','lc','cm','cf','sr','cg','tagau','taguk','tagsg','tagas','belboon','por','affili','affili_de','impradus','impraduk','silvertap','viglink','skimlinks','phg','phg_irisa','phg_conv','phg_horiz','adcell','zoobax','gameladen','ebay','tao');

$d = new DateTime();
$_GET['now_to'] = isset($_GET['now_to'])&&$_GET['now_to']?$_GET['now_to']:$d->modify('-1 day')->format('Y-m-d');
$_GET['now_from'] = isset($_GET['now_from'])&&$_GET['now_from']?$_GET['now_from']:$d->modify('-6 day')->format('Y-m-d');
$_GET['his_to'] = isset($_GET['his_to'])&&$_GET['his_to']?$_GET['his_to']:$d->modify('-1 day')->format('Y-m-d');
$_GET['his_from'] = isset($_GET['his_from'])&&$_GET['his_from']?$_GET['his_from']:$d->modify('-6 day')->format('Y-m-d');
$_GET['af'] = isset($_GET['af'])?$_GET['af']:'cj';

$page = isset($_GET['p'])?$_GET['p']:1;
$objTran = new Transaction;
$transaction = $objTran->get_history_program_rpt($_GET,$page);
$transactionData = isset($transaction['aff_data'])?$transaction['aff_data']:array();
$transactionTotal = isset($transaction['total'])?$transaction['total']:array();

$pageHtml = get_page_html($transaction);
$objTpl->assign('search', $_GET);
$objTpl->assign('transactionData', $transactionData);
$objTpl->assign('transactionTotal', $transactionTotal);
// $objTpl->assign('affs', $affs);

$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_footer['js'][] = BASE_URL.'/js/back.js';
$sys_footer['js'][] = BASE_URL.'/js/b_performance.js';
$objTpl->assign('pageHtml', $pageHtml);
$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('sys_footer', $sys_footer);
$objTpl->display('b_history_program.html');
?>