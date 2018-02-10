 <?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init_back.php');
check_user_login();
include_once('auth_ini.php');

$objPayment = new Payment;

if(isset($_POST['act']) && $_POST['act'] == 'downloadInvoice'){
    if(isset($_POST['id'])){
        $rs = $objPayment->downloadInvoice($_POST['id']);
    }else {
        header('HTTP/1.1 404 Not Found');
        echo "Error: 404 Not Found.(server file path error)";
    }
    exit;
}

$paymentList = $objPayment->getHistoryPayment();

$uid = $USERINFO['ID'];
$page = isset($_GET['p'])?$_GET['p']:1;
$pagesize = isset($_GET['pagesize'])?$_GET['pagesize']:10;


$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_header['css'][] = BASE_URL.'/css/site.css';

// $objTpl->assign('pageHtml', $pageHtml);
$objTpl->assign('paymentList', $paymentList);
$objTpl->assign('search', $_GET);
$objTpl->assign('sys_header', $sys_header);
$objTpl->display('b_payment.html');
?>
