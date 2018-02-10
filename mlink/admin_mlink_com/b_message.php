<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');
mysql_query("SET NAMES 'latin1'");
$Publisher = new Publisher();
//修改
if(isset($_POST['val']) && !empty($_POST['val'])){
    $res = $Publisher->updatemessage($_POST);
    echo $res;
    die;
}
isset($_GET['p']) ? $p = $_GET['p'] : $p = 1;
$pagesize = isset($_GET['pagesize']) ? $_GET['pagesize'] : 20;
$list = $Publisher->messagelist($_GET,$p,$pagesize);
$page_html = get_page_html($list);
$sys_header['css'][] = BASE_URL.'/css/front.css';
$objTpl->assign('sys_header', $sys_header);
if(isset($_GET['status']) && !empty($_GET['status'])) {
    $objTpl->assign('status', $_GET['status']);
}
$objTpl->assign("title",'Publisher Message');
$objTpl->assign('list',$list['info']);
$objTpl->assign("pageHtml",$page_html);
$objTpl->display('b_message.html');