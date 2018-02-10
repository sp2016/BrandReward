<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');

$objProgram = new Program;

// $affs = array('cj','ls','ond','sas','td','zanox','afffuk','affwin','avangate','pjn','wg','afffus','avt','dgmnew_au','dgmnew_nz','tt','tt_de','lc','cm','cf','sr','cg','tagau','taguk','tagsg','tagas','belboon','por','affili','affili_de','impradus','impraduk','silvertap','viglink','skimlinks','phg','phg_irisa','phg_conv','phg_horiz','adcell','zoobax','gameladen','ebay','tao');
// sort($affs);


$d = new DateTime();
$_GET['tran_to'] = isset($_GET['tran_to'])&&$_GET['tran_to']?$_GET['tran_to']:$d->format('Y-m-d');
$_GET['tran_from'] = isset($_GET['tran_from'])&&$_GET['tran_from']?$_GET['tran_from']:$d->modify('-3 day')->format('Y-m-d');
$_GET['p'] =  isset($_GET['p'])?$_GET['p']:1;


$ProgramTotal = array();
$ProgramTotal = $objProgram->get_program_rpt($_GET);
$programList = isset($ProgramTotal['data'])?$ProgramTotal['data']:array();

if(isset($ProgramTotal['data']))
	unset($ProgramTotal['data']);

$pageHtml = '';
if(!empty($ProgramTotal))
	$pageHtml = get_page_html($ProgramTotal);

$objTpl->assign('programList', $programList);
$objTpl->assign('pageHtml', $pageHtml);
$objTpl->assign('search', $_GET);
$sys_header['css'][] = BASE_URL.'/css/front.css';

$sys_footer['js'][] = BASE_URL.'/js/back.js';
$sys_footer['js'][] = BASE_URL.'/js/b_tran.js';

$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('sys_footer', $sys_footer);
$objTpl->display('b_performance_program.html');
?>