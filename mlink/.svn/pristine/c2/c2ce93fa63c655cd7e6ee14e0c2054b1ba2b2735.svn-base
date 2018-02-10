<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');

$objPayments = new Payments();
$search = $_GET;
$list = $objPayments->getPaymentsStatisByPublisher($search);
$search['orderby'] = 'PaidTime DESC';
$history = $objPayments->getPayments($search,0);
$map_history = array();
foreach($history as $k=>$v){
    $map_history[$v['PublisherId']][] = $v;
}
$paidMonth_list = $objPayments->getPaymentsBatchTime('month');

$objTpl->assign('list',$list);
$objTpl->assign('map_history',$map_history);
$objTpl->assign('paidMonth_list',$paidMonth_list);
$objTpl->assign('search',$search);
$objTpl->assign('title','Publisher Payments Statis');
$sys_header['css'][] = BASE_URL.'/css/front.css';
$objTpl->assign('sys_header', $sys_header);
$objTpl->display('b_payments_publisher_statis.html');
