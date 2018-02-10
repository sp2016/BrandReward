<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');

check_user_login();

$objAccount = new Account;
$user_profile = $objAccount->get_account_info($USERINFO['ID']);
$countryOption = getDictionary('country');
$sitetypeOption = getDictionary('sitetype');
			
$objTpl->assign('user_profile', $user_profile);
$objTpl->assign('sitetypeOption', $sitetypeOption);
$objTpl->assign('countryOption', $countryOption);
$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_header['js'][] = BASE_URL.'/js/b_account.js';
$objTpl->assign('sys_header', $sys_header);
$objTpl->display('b_account.html');
?>