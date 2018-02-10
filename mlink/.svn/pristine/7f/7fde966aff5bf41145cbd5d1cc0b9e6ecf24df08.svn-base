<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');

$objPayments = new Payments();
$paidtime_list = $objPayments->getPaymentsTimeList();

$last_workday_month = date('Y-m-t');
$objD = new Datetime($last_workday_month);
while($objD->format('w') < 1 || $objD->format('w') > 5){
    $objD->modify('-1 day');
}
$next_payment_date = $objD->format('Y-m-d');
/*
if(isset($paidtime_list[0]) && $paidtime_list[0] == $last_workday_month){
    $m = $objD->format('m');
    $y = $objD->format('Y');
    if($m == '12'){
        $m_n = '01';
        $y_n = $y + 1;
    }else{
        $m_n = $m+1;
        $y_n = $y;
    }
    $next_month_firstday = $y_n.'-'.$m_n.'-01';
    $next_month_lastday = date('Y-m-t',strtotime($next_month_firstday));
    $objD = new Datetime($next_month_lastday);
    while($objD->format('w') < 1 || $objD->format('w') > 5){
        $objD->modify('-1 day');
    }
    $next_payment_date = $objD->format('Y-m-d');
}
*/
$next_payment_date = '2017-10-31';

if(isset($_POST['act']) && $_POST['act'] == 'save_next_invoice'){
    $opts = array();
    $opts['Site'] = explode(',',trim($_POST['site'],','));
    $opts['PaidDate'] = $_POST['paiddate'];
    $opts['GroupId'] = $_POST['groupid'];
    $opts['PaymentType'] = $_POST['paytype'];
    $opts['TransactionId'] = $_POST['transactionid'];
    $opts['PaidTime'] = $_POST['paidtime'];

    $rs = $objPayments->save_next_payments($opts);
    if($rs){
        echo 1;exit();
    }else{
        echo 0;exit();
    }
}

$payments_data_total = $objPayments->nextPaymentTotal($next_payment_date);
$payments_data_group = $objPayments->groupPaymentInfo($payments_data_total);

$total_count = 0;
$total_sum = 0;
foreach($payments_data_total as $k=>$v){
    $total_count++;
    $total_sum = bcadd($total_sum, $v['commission'], 2);
}


$objTpl->assign('next_payment_date',$next_payment_date);
$objTpl->assign('total_count',$total_count);
$objTpl->assign('total_sum',$total_sum);
$objTpl->assign('search',$_GET);
$objTpl->assign('list',$payments_data_total);
$objTpl->assign('list_group',$payments_data_group);
$objTpl->assign('list_group_json',json_encode($payments_data_group));
$objTpl->assign("title","Preview next payments<br>".$next_payment_date);
$sys_header['css'][] = BASE_URL.'/css/front.css';
$objTpl->assign('sys_header', $sys_header);
$objTpl->display('b_payments_view.html');
