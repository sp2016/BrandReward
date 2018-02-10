<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init_back.php');
check_user_login();
include_once('auth_ini.php');

/* if(isset($_POST['url']) && isset($_POST['siteType'])){
    $url = $_POST['url'];
    $siteType = $_POST['siteType'];
    $tools = new Tools();
    $rs = $tools->find_is_our_domain($url,$siteType);
    echo $rs;
    exit;
} */

/* $uid = $USERINFO['ID'];
$objTran = new Transaction;
$sites = $objTran->table('publisher_account')->where('PublisherId = '.intval($uid))->find(); */

$sites = array();
$i = 0;
foreach ($_SESSION['pubAccList'] as $temp){
    $sites[$i]['Domain'] = $temp['Domain'];
    $sites[$i]['ApiKey'] = $temp['ApiKey'];
    $sites[$i]['ID'] = $temp['ID'];
    $i++;
}

$objTpl->assign('sites', $sites);

/* $sitetypeResult = $objTran->table('publisher_detail')->where('PublisherId = '.intval($uid))->field('sitetype')->findOne();
$sitearr = explode('+',$sitetypeResult['sitetype']);
$siteType = 'content';
foreach($sitearr as $k){
    if($k == '1_e' || $k == '2_e'){
        $siteType = 'coupon';
    }
}
$objTpl->assign('siteType', $siteType); */


$sys_header['css'][] = BASE_URL.'/css/front.css';

$sys_footer['js'][] = BASE_URL.'/js/back.js';
$sys_footer['js'][] = BASE_URL.'/js/b_tran.js';
$sys_header['js'][] = BASE_URL.'/js/clipboard.min.js';
$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('sys_footer', $sys_footer);
$objTpl->display('b_tools_createlink.html');
?>