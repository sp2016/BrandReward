<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');



$d = new DateTime();//系统类
$objTran = new Transaction;
$tranData = $objTran->get_history_domain_detail_rpt($_GET);           
// echo "<pre>";													
// print_r($tranData);

$objTpl->assign('search', $_GET);
$objTpl->assign('tranData', $tranData);

$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_footer['js'][] = BASE_URL.'/js/back.js';
$sys_footer['js'][] = BASE_URL.'/js/b_performance.js';

$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('sys_footer', $sys_footer);
$objTpl->display('b_history_domain_detail.html');
?>