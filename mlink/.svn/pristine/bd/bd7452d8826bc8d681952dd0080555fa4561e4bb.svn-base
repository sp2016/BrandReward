<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');

$T = new Transaction();

$list = $T->getDailySum($_GET,29);
$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_header['css'][] = BASE_URL.'/css/DateTimePicker.css';
$sys_footer['js'][] = BASE_URL.'/js/DateTimePicker.js';
$sys_footer['js'][] = BASE_URL.'/js/b_tran.js';
$sys_footer['js'][] = BASE_URL.'/js/back.js';
$objTpl->assign('search', $_GET);
$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('sys_footer', $sys_footer);

$objTpl->assign('info',$list);
$objTpl->display('b_daily_sum.html');