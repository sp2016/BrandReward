<?php

include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');



$fin_rev_acc_list = $tmpdata = array();
$query = "select * from `fin_rev_acc` order by Name";
$result=mysql_query($query);
while($row = mysql_fetch_array($result,MYSQL_ASSOC))
{
	$tmpdata[] = $row;
}
foreach ($tmpdata as $v){
	$fin_rev_acc_list[$v['ID']]=$v['Name'];
}
$objTpl->assign("fin_rev_acc_list", $fin_rev_acc_list);



$d = new DateTime();
$timeNow = $d->format("Y-m-d H:i:s");
$sys_header['css'][] = BASE_URL.'/css/front.css';
$objTpl->assign('timeNow', $timeNow);
$objTpl->assign('sys_header', $sys_header);
$objTpl->display('add_affiliates.html');
?>