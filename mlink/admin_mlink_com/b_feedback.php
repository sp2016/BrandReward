<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');

$objFB = new Feedback();

$objTpl->assign('title','Feed Back Center');
$sys_header['css'][] = BASE_URL.'/css/front.css';
$objTpl->assign('sys_header', $sys_header);
$objTpl->display('b_feedback.html');