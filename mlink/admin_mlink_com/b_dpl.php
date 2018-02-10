<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');
mysql_query("SET NAMES latin1");
$merge = array_merge($_GET,$_POST);
if(isset($_POST['act'])){//用于ajax传值
	$_SESSION['token'] = 'token';
	echo $_SESSION['token'];
	exit;
}
if(isset($_SESSION['token']) && $_SESSION['token'] =='token'){
	echo '<script>location.href="'.$_SERVER["HTTP_REFERER"].'"</script>';
	unset($_SESSION['token']);
}
$domain = $_GET;
$domain_id = $_GET['id'];
$objDomain = new Domain;
$pdl = $objDomain->getPDL($domain_id,$merge);
$program_related = $pdl['program_related'];

$history = "";
$relation = "";
$domain_name = "";
$domain_name = $pdl['domain_name'];
$history = $pdl['history'];
$from = $pdl['from'];
$to = $pdl['to'];
$relation = $pdl['relation'];

$title = 'Domain-PDL';

$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_header['css'][] = BASE_URL.'/css/table.css';
$objTpl->assign('program_related', $program_related);
$objTpl->assign('history', $history);
$objTpl->assign('from',$from);
$objTpl->assign('to',$to);
$objTpl->assign('domain_name', $domain_name);
$objTpl->assign('relation', $relation);
$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('domain', $domain);
$objTpl->assign('title', $title);
$objTpl->display('b_dpl.html');
?>