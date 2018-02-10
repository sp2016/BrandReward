<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');
mysql_query("SET NAMES 'latin1'");

$Account = new Account();
if(isset($_POST)){
    $Account->doInsertAccount($_POST);
}


if(isset($_GET['id'])){
    $info = $Account->getInfoById($_GET['id']);
    $objTpl->assign('info',$info);
}



$sys_header['css'][] = BASE_URL.'/css/front.css';
$objTpl->assign('sys_header', $sys_header);
$objTpl->display('b_admin_edit.html');