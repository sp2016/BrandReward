 <?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init_back.php');
check_user_login();
include_once('auth_ini.php');


$uid = $USERINFO['ID'];
$page = isset($_GET['p'])?$_GET['p']:1;
$pagesize = isset($_GET['pagesize'])?$_GET['pagesize']:10;
$d = new DateTime();
$_GET['tran_to'] = isset($_GET['tran_to'])&&$_GET['tran_to']?$_GET['tran_to']:$d->format('Y-m-d');
$_GET['tran_from'] = isset($_GET['tran_from'])&&$_GET['tran_from']?$_GET['tran_from']:$d->modify('-3 day')->format('Y-m-d');

if(!isset($_SESSION['u']['apikey'])){
    $account_info = $objAccount->get_account_info($_SESSION['u']['ID']);
    $_SESSION['u']['apikey'] = $account_info['site'][0]['ApiKey'];
}

$objTran = new Transaction;

if(isset($_GET['act'])){
    $objTran->GetTranCsvFile($uid,$_GET);
    die;
}

$TranTotal = $objTran->getTransactionListPage($uid,$_GET,$page,$pagesize);
$TranData = $TranTotal['data'];
unset($TranTotal['data']);
$pageHtml = get_page_html($TranTotal);

//get referrer data
$referrerData = array();
if($USERINFO['isreferrer']){
	$search = array();
	$search['uid'] = $uid;
	$search['visitFrom'] = $_GET['tran_from'];
	$search['visitTo'] = $_GET['tran_to'];
	$referrerData = $objTran->getReferrerCommission($search,'daily');
}

$sys_header['css'][] = BASE_URL.'/css/daterangepicker.css';
$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_header['css'][] = BASE_URL.'/css/site.css';
$sys_footer['js'][] = BASE_URL.'/js/back.js';
$sys_footer['js'][] = BASE_URL.'/js/b_tran.js';
// $sys_header['js'][] = BASE_URL.'/js/jquery.zclip.min.js';
$sys_header['js'][] = BASE_URL.'/js/clipboard.min.js';
$sys_header['js'][] = BASE_URL.'/js/moment.min.js';
$sys_header['js'][] = BASE_URL.'/js/daterangepicker.js';

/* $sql = "select ApiKey,Alias,Domain from publisher_account WHERE PublisherId =".$uid;
$site = $db->getRows($sql);
$objTran = new Transaction;
$sites = $objTran->table('publisher_account')->where('PublisherId = '.intval($uid))->find();
$objTpl->assign('sites', $sites); */
$sites = array();
$i = 0;
foreach ($_SESSION['pubAccList'] as $temp){
    $sites[$i]['Domain'] = $temp['Domain'];
    $sites[$i]['ApiKey'] = $temp['ApiKey'];
    $i++;
}
$objTpl->assign('sites', $sites);

$objTpl->assign('username',$_SESSION['u']['UserName']);
$objTpl->assign('total', $TranTotal['total']);
$objTpl->assign('TranData', $TranData);
$objTpl->assign('referrerData', $referrerData);
$objTpl->assign('pageHtml', $pageHtml);
$objTpl->assign('search', $_GET);
$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('sys_footer', $sys_footer);
$objTpl->assign('sys_userinfo', $USERINFO);
$objTpl->display('b_transaction.html');
?>
