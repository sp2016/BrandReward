<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');

$countryOption = getDictionary('country');

$sys_header['css'][] = BASE_URL.'/css/front.css';
$objTpl->assign('countryOption', $countryOption);
$objTpl->assign('sys_header', $sys_header);
$objTpl->display('signup.html');
?>