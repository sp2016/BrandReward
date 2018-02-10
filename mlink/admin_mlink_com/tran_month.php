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

$objTran = new Transaction();
$sql = 'SELECT MIN(createddate) AS mindate,MAX(createddate) AS maxdate FROM `rpt_transaction_base` ';
$row = $objTran->getRow($sql);

$mindate = $row['mindate'];
$maxdate = $row['maxdate'];

$ds = new DateTime($mindate);
$de = new DateTime($maxdate);

$date_list = array();
while($ds->format('Y-m') != $de->format('Y-m')){
	$date_list[] = $ds->format('Y-m');
	$ds->modify('+1 month');
}
$date_list[] = $de->format('Y-m');
$objTpl->assign('date_list', $date_list);

if(isset($_GET['sel_site']) && in_array('All', $_GET['sel_site']))
	unset($_GET['sel_site']);

if(isset($_GET['sel_aff']) && in_array('All', $_GET['sel_aff']))
	unset($_GET['sel_aff']);

if(!isset($_GET['sel_start']) || !isset($_GET['sel_end'])){
	$_GET['sel_start'] = $de->format('Y-m');
	$_GET['sel_end'] = $de->format('Y-m');
}

$_GET['sel_mode'] = isset($_GET['sel_mode']) && $_GET['sel_mode']=='Site'?'Site':'Af';

$tran_data = $objTran->get_transaction_data($_GET);

// echo "<pre>";print_r($tran_data);exit();
$objTpl->assign('tran_data', $tran_data);
$objTpl->assign('S_GET', $_GET);

$objTpl->assign('sys_header', $sys_header);
$objTpl->display('tran_month.html');
?>