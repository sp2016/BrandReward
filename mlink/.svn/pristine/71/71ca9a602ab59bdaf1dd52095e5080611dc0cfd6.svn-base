<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');

$objPayments = new Payments();
$search = $_GET;
$list = $objPayments->getPayments($search,$_GET['p']);

$search['return_t'] = 'pagination';
$page_info = $objPayments->getPayments($search,$_GET['p']);
$page_html = get_page_html($page_info);

$paidMonth_list = $objPayments->getPaymentsBatchTime('month');

$objTpl->assign('list',$list);
$objTpl->assign('paidMonth_list',$paidMonth_list);
$objTpl->assign('search',$search);
$objTpl->assign("title","Payments Histyory");
$objTpl->assign("pageHtml",$page_html);
$objTpl->assign("pageInfo",$page_info);
$objTpl->assign('title','Publisher Payments');
$sys_header['css'][] = BASE_URL.'/css/front.css';
$objTpl->assign('sys_header', $sys_header);
$objTpl->display('b_payments.html');
