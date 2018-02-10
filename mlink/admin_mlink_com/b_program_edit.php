<?php

include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');
$currency = array('AUD','BRL','CAD','CZK','DKK','EUR','GBP','HKD','INR','JPY','KRW','MYR','NOK','NZD','PHP','PLN','SAR','SEK','SGD','THB','TRY','TWD','USD','ZAR');

$objProgram = new Program;

$flag = true;
if(!isset($_GET['id']) || empty($_GET['id'])){
	$flag = false;
}else{
	$row = $objProgram->table('program')->where('ID = '.intval($_GET['id']))->findone();
	if(!$row){
		$flag = false;
	}
}

if(!$flag){
	echo '<script>alert("Error: invalid id");window.close();</script>';
}


$Program = $objProgram->get_program_one($_GET['id']);

$objTpl->assign('program', $Program['p']);
$objTpl->assign('aff', $Program['aff']);
$objTpl->assign('pm', $Program['pm']);

$sys_header['css'][] = BASE_URL.'/css/front.css';

$sys_footer['js'][] = BASE_URL.'/js/back.js';
$sys_footer['js'][] = BASE_URL.'/js/b_tran.js';

$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('sys_footer', $sys_footer);
$objTpl->assign('currency', $currency);
$objTpl->display('b_program_edit.html');
?>