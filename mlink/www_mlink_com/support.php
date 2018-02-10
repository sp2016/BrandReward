<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');


if(isset($LANG[$language])){
	$sys_header['js'][] = BASE_URL.'/js/b_account_china.js';
}else{
	$sys_header['js'][] = BASE_URL.'/js/b_account.js';
}
$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('title', 'Support');
$objTpl->display('support.html');
?>