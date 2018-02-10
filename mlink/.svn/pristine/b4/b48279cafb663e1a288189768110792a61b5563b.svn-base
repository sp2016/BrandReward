<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');
mysql_query("SET NAMES 'latin1'");
$add = array();
if(isset($_POST['add_domain'])&&$_POST['add_domain']){
	$add['Domain'] = $_POST['add_domain'];
	$table_name = "domain";
	update_add($table_name,$add);
}


//$_GET['site'] = isset($_GET['site'])?$_GET['site']:'us';
$page = isset($_GET['p'])?$_GET['p']:1;
if(isset($_GET['fakeProgram']) && !trim($_GET['fakeProgram']))
	$_GET['Program'] = '';
$objDomain = new Domain;

$DomainTotal = $objDomain->getDomainListPage($_GET,$page);

$DomainList = $DomainTotal['data'];
// echo "<pre>";
// print_r($DomainList) ;
$dids = array();
$site = array();
if(!empty($DomainList)){
foreach($DomainList as $k=>$v){
	$dids[] = $v['ID'];
	if(isset($v['Site'])){
		$temp[$v['ID'].'_'.$v['Site']] = $v;
	}else{
		$temp[$v['ID'].'_NoAff'] = $v;
	}
}
	$DomainList = $temp;
}else{
	$DomainList = array();
} 
// echo "<pre>";
// print_r($DomainList) ;
$programInfo = $objDomain->get_domain_program_info($dids);

$title = 'Domain-List';
unset($DomainTotal['data']);
$pageHtml = get_page_html($DomainTotal);
$objTpl->assign('DomainList', $DomainList);
$objTpl->assign('programInfo', $programInfo);
$objTpl->assign('pageHtml', $pageHtml);
$objTpl->assign('search', $_GET);
$objTpl->assign('title', $title);
$sys_header['css'][] = BASE_URL.'/css/front.css';
$objTpl->assign('sys_header', $sys_header);
$objTpl->display('b_merchant.html');
?>