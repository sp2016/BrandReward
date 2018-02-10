<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');

$objPayments = new Payments();

if(isset($_POST['act']) && $_POST['act'] == 'get_remit_info'){
    $data = $objPayments->getPaymentsRemitInfoById($_POST['paymentid']);
    print_r(json_encode($data));exit();
}

if(isset($_POST['act']) && $_POST['act'] == 'edit_payments'){
    $return = array();
    $return['rs'] = 1;
    $data = array();
    $data['PaymentsID'] = $_POST['paymentid'];
    $data['TransactionId'] = $_POST['transactionid'];
    $data['PaymentType'] = $_POST['paytype'];
    $data['PaymentDetail'] = $_POST['paymentdetail'];
    $data['Amount'] = $_POST['amount'];
    $data['PaidTime'] = $_POST['paidtime'];
    if(empty($_POST['paymentid'])){
        $return['rs'] = 0;
        $return['msg'] = 'PaymentID error.';
    }
    if(empty($_POST['transactionid'])){
        $return['rs'] = 0;
        $return['msg'] = 'transactionid can not be empty.';
    }
    if(empty($_POST['paytype'])){
        $return['rs'] = 0;
        $return['msg'] = 'paytype can not be empty.';
    }
    if(empty($_POST['paidtime'])){
        $return['rs'] = 0;
        $return['msg'] = 'paidtime can not be empty.';
    }
    if(!is_numeric($_POST['amount'])){
        $return['rs'] = 0;
        $return['msg'] = 'amount must be a number.';
    }
    if(empty($_POST['paymentdetail'])){
        $return['rs'] = 0;
        $return['msg'] = 'can not be empty.';
    }
    if($return['rs']){
        $return = $objPayments->edit_payments($data);
    }
    echo json_encode($return);exit();
}

$search = $_GET;
$page = isset($_GET['p'])?$_GET['p']:1;
$page_size = isset($_GET['pagesize'])?$_GET['pagesize']:'50';
$list = $objPayments->getPayments($search,$page,$page_size);

$search['return_t'] = 'pagination';
$page_info = $objPayments->getPayments($search,$page,$page_size);
$page_html = get_page_html($page_info);

$search['return_t'] = 'statis';
$list_statis = $objPayments->getPayments($search,$page,$page_size);

$paidMonth_list = $objPayments->getPaymentsBatchTime('month');

$paymentDate_this = $objPayments->get_payment_date('this');
$paymentDate_prev = $objPayments->get_payment_date('prev');

$objTpl->assign('list',$list);
$objTpl->assign('list_statis',$list_statis);
$objTpl->assign('paidMonth_list',$paidMonth_list);
$objTpl->assign('search',$search);
$objTpl->assign("title","Payments Histyory");
$objTpl->assign("pageHtml",$page_html);
$objTpl->assign("pageInfo",$page_info);
$objTpl->assign('title','Publisher Payments');
$sys_header['css'][] = BASE_URL.'/css/front.css';
$objTpl->assign('sys_header', $sys_header);
$objTpl->display('b_payments_publisher.html');
