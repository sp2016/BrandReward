<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');
mysql_query("SET NAMES 'latin1'");

$Publisher = new Publisher();
isset($_GET['p']) ? $p = $_GET['p'] : $p = 1;
$pagesize = isset($_GET['pagesize']) ? $_GET['pagesize'] : 30;
$list = $Publisher->getpublierupdate($_GET,$p,$pagesize);
$page_html = get_page_html($list);

$objTpl->assign('url','http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
$sys_header['css'][] = BASE_URL.'/css/front.css';
$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('pagesize', $pagesize);
$objTpl->assign('list',$list['data']);
$objTpl->assign("title","Publisher Update");
$objTpl->assign("pageHtml",$page_html);
$objTpl->display('b_publisher_update.html');