<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');

$d = new DateTime();

$_GET['tran_to'] = isset($_GET['tran_to'])&&$_GET['tran_to']?$_GET['tran_to']:$d->format('Y-m-d');
$_GET['tran_from'] = isset($_GET['tran_from'])&&$_GET['tran_from']?$_GET['tran_from']:$d->modify('-7 day')->format('Y-m-d');

$objOut = new Outlog;
$outTotal = $objOut->getUnafiliatedRpt($_GET);
$outList = $outTotal['data'];
unset($outTotal['data']);
$pageHtml = get_page_html($outTotal);

$objTpl->assign('search', $_GET);
$objTpl->assign('outList', $outList);
$objTpl->assign('pageHtml', $pageHtml);

$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_footer['js'][] = BASE_URL.'/js/back.js';
$sys_footer['js'][] = BASE_URL.'/js/b_performance.js';

$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('sys_footer', $sys_footer);
$objTpl->display('b_unaffiliated.html');
?>