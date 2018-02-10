<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');

$objPayments = new Payments();
$pendingMonthNoPaid = $objPayments->getPaymentsPendingBatchTime('month','no');
$pendingDayNoPaid = $objPayments->getPaymentsPendingBatchTime('day','no');
$remitDate = array_combine($pendingMonthNoPaid,$pendingDayNoPaid);



$next_payment_date = $pendingDayNoPaid[0];
if(isset($_GET['remitmonth']) && !empty($_GET['remitmonth']) && isset($remitDate[$_GET['remitmonth']])){
    $next_payment_date = $remitDate[$_GET['remitmonth']];
}

if(isset($_POST['act']) && $_POST['act'] == 'save_next_invoice'){
    $opts = array();
    $opts['Site'] = explode(',',trim($_POST['site'],','));
    $opts['Amount'] = $_POST['amount'];
    $opts['PaidDate'] = $next_payment_date;
    $opts['PaymentType'] = $_POST['paytype'];
    $opts['TransactionId'] = $_POST['transactionid'];
    $opts['PaidTime'] = $_POST['paidtime'];
    $opts['PaymentDetail'] = $_POST['paydetail'];

    $rs = $objPayments->save_next_payments($opts);
    echo json_encode($rs);
    exit();
}

$opts = array();
$opts['amountfrom'] = isset($_GET['amountfrom'])?$_GET['amountfrom']:'';
$opts['amountto'] = isset($_GET['amountto'])?$_GET['amountto']:'';
$opts['hasbank'] = isset($_GET['hasbank'])?$_GET['hasbank']:'';
$opts['ptype'] = isset($_GET['ptype'])?$_GET['ptype']:'';
$opts['groupby'] = 'site';

$payments_data_total = $objPayments->nextPaymentTotal($opts);
$payments_data_group = $objPayments->groupPaymentInfo($payments_data_total);

$total_count = 0;
$total_sum = 0;
if(isset($_GET['type']) && $_GET['type'] == 'group'){
    foreach($payments_data_group as $k=>$v){
        if($v['sum'] < 10){
            unset($payments_data_group[$k]);
            continue;
        }
        $total_count++;
        $total_sum = bcadd($total_sum, $v['sum'], 2);
    }
}else{
    foreach($payments_data_total as $k=>$v){
        $total_count++;
        $total_sum = bcadd($total_sum, $v['commission'], 2);
    }
}
$key_sites = _array_column($payments_data_total,'Site');
$payments_data_total = array_combine($key_sites,$payments_data_total);

$objTpl->assign('remitDate',$remitDate);
$objTpl->assign('next_payment_date',$next_payment_date);
$objTpl->assign('total_count',$total_count);
$objTpl->assign('total_sum',$total_sum);
$objTpl->assign('search',$_GET);
$objTpl->assign('list',$payments_data_total);
$objTpl->assign('list_json',json_encode($payments_data_total));
$objTpl->assign('list_group',$payments_data_group);
$objTpl->assign('list_group_json',json_encode($payments_data_group));
$objTpl->assign("title","Remit Pending Payments<br>".$next_payment_date);
$sys_header['css'][] = BASE_URL.'/css/front.css';
$objTpl->assign('sys_header', $sys_header);
$objTpl->display('b_payments_publisher_remit.html');
