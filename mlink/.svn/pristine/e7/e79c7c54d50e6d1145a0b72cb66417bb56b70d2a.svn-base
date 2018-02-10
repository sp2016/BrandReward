<?php
header ( "Content-type:application/vnd.ms-excel" );
header ( "Content-Disposition:filename=".$_GET['his_from']."_".$_GET['his_to']."_".$_GET['now_from']."_".$_GET['now_to'].".xls" );
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');

$objTran = new Transaction;
$transaction = $objTran->get_history_domain_rpt($_GET,1,1000000);
$transactionData = isset($transaction['aff_data'])?$transaction['aff_data']:array();
$transactionTotal = isset($transaction['total'])?$transaction['total']:array();

$checked = explode("|", $_GET['checked_str']);
foreach ($checked as $val){
	$temp = explode("_", $val);
	$col[] = $temp[0];                  //$col数组中存有num，sale...
}
$colspan = array_count_values($col);    //$colspan数组中存有num,sale....及其对应的个数，用于判断colspan属性

print_r($checked);


$objTpl->assign('transactionData', $transactionData);
$objTpl->assign('transactionTotal', $transactionTotal);
$objTpl->assign('search', $_GET);
$objTpl->assign('checked', $checked);
$objTpl->assign('colspan', $colspan);
$objTpl->display('excel.html');
?>