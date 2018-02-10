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

if(!isset($_GET['sel_updateddate_end']) && !isset($_GET['sel_updateddate_start'])){
	$d = new DateTime();
	$_GET['sel_updateddate_end'] = $d->format('Y-m-d');
	$_GET['sel_updateddate_start'] = $d->modify('-3 day')->format('Y-m-d');
}

$objTran = new Transaction();
$tran_data = $objTran->get_transaction_data_daily($_GET);

$row = $tran_data['d'];
$page_data = array();
$page_data['page_now'] = $tran_data['p'];
$page_data['page_total'] = $tran_data['t'];
$page_html = get_page_html($page_data);


// echo "<pre>";print_r($tran_data);exit();
$objTpl->assign('tran_data', $row);
$objTpl->assign('page_html', $page_html);
$objTpl->assign('S_GET', $_GET);
// echo "<pre>";print_r($_GET);exit();
$objTpl->assign('sys_header', $sys_header);
$objTpl->display('tran_daily.html');
?>