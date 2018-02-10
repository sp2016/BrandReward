<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');
mysql_query("SET NAMES 'latin1'");

$OperationLog = new OperationLog();
$list = $OperationLog->get_operation_log_list($_GET);
$pageHtml = get_page_html($list);

$sys_header['css'][] = BASE_URL.'/css/front.css';
foreach($_GET as $key => $value){
    $objTpl->assign($key,$value);
}
$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('pageHtml',$pageHtml);
$objTpl->assign('list',$list['data']);
$objTpl->assign('title','Network Change Log');
$sys_footer['js'][] = BASE_URL.'/js/b_tran.js';
$objTpl->assign('sys_footer', $sys_footer);
$objTpl->display('b_operation_log.html');
//$sys_footer['js'][] = BASE_URL.'/js/back.js';


?>