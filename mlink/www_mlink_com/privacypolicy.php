<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');


$sys_header['js'][] = BASE_URL.'/js/b_account.js';
$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('title', 'Privacy Policy');
$objTpl->display('privacypolicy.html');
?>