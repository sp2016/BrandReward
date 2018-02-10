<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');

$page = isset($_GET['p'])?$_GET['p']:1;
$d = new DateTime();

$objOutlog = new Outlog;
$outLogTotal = $objOutlog->get_check_jump_res_data($_GET);

$outLogList = $outLogTotal['data'];
unset($outLogTotal['data']);
$pageHtml = get_page_html($outLogTotal);

$objTpl->assign('outLogList', $outLogList);
$objTpl->assign('pageHtml', $pageHtml);
$objTpl->assign('search', $_GET);
$sys_header['css'][] = BASE_URL.'/css/front.css';

$sys_footer['js'][] = BASE_URL.'/js/back.js';

$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('sys_footer', $sys_footer);
$objTpl->display('b_chk_jump.html');
?>