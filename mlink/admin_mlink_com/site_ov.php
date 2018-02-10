<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');

// echo "<pre>";print_r($sys_menu);exit();
$AffNameIdMap = $sys_aff_name_id_map;
$site_tracking_code = $sys_site_tracking_code;
unset($site_tracking_code['s10']);
unset($site_tracking_code['s32']);
unset($site_tracking_code['s46']);
unset($site_tracking_code['s40']);
unset($site_tracking_code['s05']);
unset($site_tracking_code['s06']);
unset($site_tracking_code['s07']);
unset($site_tracking_code['s38']);
unset($site_tracking_code['s03']);
unset($site_tracking_code['s04']);
unset($site_tracking_code['s36']);
unset($site_tracking_code['s37']);
unset($site_tracking_code['s45']);
unset($site_tracking_code['s39']);


$objTpl->assign('AffNameIdMap', $AffNameIdMap);
$objTpl->assign('site_tracking_code', $site_tracking_code);

if(!isset($_GET['sel_createddate_end']) && !isset($_GET['sel_createddate_start'])){
	$d = new DateTime();
	$_GET['sel_createddate_end'] = $d->format('Y-m-d');
	$_GET['sel_createddate_start'] = $d->modify('-3 day')->format('Y-m-d');
}
$objOutlog = new Outlog();
$out_data = $objOutlog->get_out_ov($_GET,'site');
$out_row = $out_data['row'];

$objTran = new Transaction();
$tran_data = $objTran->get_aff_ov($_GET,'Site');

// echo "<pre>";print_r($tran_data);exit();
$row = $tran_data['rows'];
unset($tran_data['rows']);
$total_data = $tran_data;
$total_data['total_click'] = $out_data['c'];
unset($out_data);

$param = array();
$param[] = 'sel_createddate_end='.$_GET['sel_createddate_end'];
$param[] = 'sel_createddate_start='.$_GET['sel_createddate_start'];
if(!isset($_GET['sel_aff']) || empty($_GET['sel_aff']) || in_array('All',$_GET['sel_aff'])){
}else{
	foreach($_GET['sel_aff'] as $k=>$v){
		$param[] = urlencode('sel_aff[]').'='.urlencode($v);
	}
}
$param_str = join('&',$param);
$daily_url = BASE_URL.'/site_daily.php?'.$param_str;

// echo "<pre>";print_r($tran_data);exit();
$objTpl->assign('out_data', $out_row);
$objTpl->assign('daily_url', $daily_url);
$objTpl->assign('total_data', $total_data);
$objTpl->assign('tran_data', $row);
$objTpl->assign('S_GET', $_GET);
// echo "<pre>";print_r($_GET);exit();
$objTpl->assign('sys_header', $sys_header);
$objTpl->display('site_ov.html');
?>