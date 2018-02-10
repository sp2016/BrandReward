<?php

include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init_back.php');
check_user_login();
include_once('auth_ini.php');
mysql_query("SET NAMES UTF8");

if(!isset($_SESSION['u']['apikey'])){
    $account_info = $objAccount->get_account_info($_SESSION['u']['ID']);
    $_SESSION['u']['apikey'] = $account_info['site'][0]['ApiKey'];
}

$page = isset($_GET['p'])?$_GET['p']:1;
$pagesize = isset($_GET['pagesize'])?$_GET['pagesize']:20;
$sort = isset($_GET['sort'])?$_GET['sort']:'Commission';
$objDomain = new Domain;
$merchant = new MerchantExt();

if(isset($_POST['table']) && !empty($_POST['table'])){
    $page = $_POST['start'];
    $pagesize = $_POST['length'];
    $opt = json_decode($_POST['data'],true);
    $search = array();
    for($i = 0;$i<count($opt);$i++){
        $search[$opt[$i]['name']] = $opt[$i]['value'];
    }
    $search['sid']  = $_POST['id'];
    $data = $merchant->GetContentNew($search,$page,$pagesize,$USERINFO['ID']);
    foreach($data['content'] as $k => &$v){
        if($v['StartDate'] == '0000-00-00 00:00:00'){
            $v['StartTime'] = 'N/A';
        }else{
            $v['StartTime'] = date('Y-m-d H:i:s',strtotime($v['StartDate']));
        }
        if($v['EndDate'] == '0000-00-00 00:00:00'){
            $v['ExpireTime'] = 'N/A';
        }else{
            $v['ExpireTime'] = date('Y-m-d H:i:s',strtotime($v['EndDate']));
        }
    }

    $res['data'] = $data['content'];
    $res['start'] = $page/$pagesize+1;
    $res['recordsFiltered'] = $data['total'];
    echo json_encode($res);
    die;
}
if(isset($_POST['tableActivity']) && !empty($_POST['tableActivity'])){
    $page = $_POST['start'];
    $pagesize = $_POST['length'];
    $opt = json_decode($_POST['data'],true);
    $search = array();
    for($i = 0;$i<count($opt);$i++){
        $search[$opt[$i]['name']] = $opt[$i]['value'];
    }
    $search['sid']  = $_POST['id'];
    $data = $merchant->GetContentNew($search,$page,$pagesize,$USERINFO['ID'],false,false,'valentine');
    foreach($data['content'] as $k => &$v){
        if($v['StartDate'] == '0000-00-00 00:00:00'){
            $v['StartTime'] = 'N/A';
        }else{
            $v['StartTime'] = date('Y-m-d H:i:s',strtotime($v['StartDate']));
        }
        if($v['EndDate'] == '0000-00-00 00:00:00'){
            $v['ExpireTime'] = 'N/A';
        }else{
            $v['ExpireTime'] = date('Y-m-d H:i:s',strtotime($v['EndDate']));
        }
    }

    $res['data'] = $data['content'];
    $res['start'] = $page/$pagesize+1;
    $res['recordsFiltered'] = $data['total'];
    echo json_encode($res);
    die;
}
if(isset($_POST['sid']) && !empty($_POST['sid'])){
    $sid = $_POST['sid'];
    $type = $_POST['type'];
    $uid = $USERINFO['ID'];
    $res = $objDomain->addcollect($uid,$sid,$type);
    echo $res;
    die;
}
if(isset($_POST['check'])){
    $uid = $USERINFO['ID'];
    $res = $objDomain->checkCollect($uid);
    if(!empty($res)){
        $num = '';
        foreach($res as $k){
            $num.=$k['sid'].',';
        }
        echo rtrim($num,',');
    }else{
        echo 0;
    }
    die;
}

$_GET['uid'] = $USERINFO['ID'];
$uid = $USERINFO['ID'];
$key = isset($_GET['country']) && !empty($_GET['country'])?$_GET['country']:$USERINFO['Country'];
// $rstore = $objDomain->getRecommend($key);

//获取活动时的store数据
$activityStore = $objDomain->getActivityRecommend();
if(isset($_GET['new'])){
    $DomainTotal = $objDomain->getDomainListPageNew($_GET,$page,$pagesize);
}else {
 
$DomainTotal = $objDomain->getDomainListPage($_GET,$page,$pagesize);
   
}
$sql = 'SELECT CountryName,CountryCode FROM country_codes';
$arr = $db->getRows($sql);
$merchant = new MerchantExt();

$category = $merchant->MineCategoryList($uid);

foreach($arr as $v){
    $countryArr[$v['CountryName']] = $v['CountryCode'];
}

/* $sql = "select Domain,ApiKey from publisher_account where publisherId=".$uid;
$key = $db->getRows($sql); */
$key = array();
$i = 0;
foreach ($_SESSION['pubAccList'] as $temp){
    $key[$i]['Domain'] = $temp['Domain'];
    $key[$i]['ApiKey'] = $temp['ApiKey'];
    $i++;
}

$countryArr['global'] = 'Global';
$countryArr['United Kingdom'] = 'UK';
$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_header['css'][] = BASE_URL.'/css/semantic.min.css';
$sys_header['css'][] = BASE_URL.'/css/dataTables.bootstrap.min.css';
$sys_header['css'][] = BASE_URL.'/css/jquery.bxslider.css';
$sys_header['js'][] = BASE_URL.'/js/dataTables.min.js';
$sys_header['js'][] = BASE_URL.'/js/dataTables.bootstrap.min.js';
$sys_header['js'][] = BASE_URL.'/js/jquery.bxslider.min.js';
// $sys_header['js'][] = BASE_URL.'/js/jquery.zclip.min.js';
$sys_header['js'][] = BASE_URL.'/js/clipboard.min.js';
$DomainList = $DomainTotal['data'];
foreach($DomainList as &$value)
{
    //真实的click
    if($value['Clicks'] < $value['robotClicks']){
        $value['Clicks'] = 0;
    }else {
        $value['Clicks'] = $value['Clicks'] - $value['robotClicks'];
    }
    if($value['Clicks'] != 0){
        $value['epc'] =number_format(($value['Commission']/$value['Clicks']),2,'.',',');
    }else{
        $value['epc'] = 0.00;
    }
    if(isset($value['LogoName'] )) {
        if (strstr($value['LogoName'], ',')) {
            $logo = explode(',', $value['LogoName']);
            $value['LogoName'] = $logo[0];
        }
    }
    if(empty($value['NameOptimized'])){
        $value['NameOptimized'] = ucfirst($value['storeName']);
//         $value['LogoName'] = 'brandreward.png';
    }
    if(empty($value['LogoName'])){
        $value['LogoName'] = 'brandreward.png';
    }
    if(isset($value['Clicks'] )){
        $value['Clicks'] = number_format($value['Clicks']);
    }else{
        $value['Clicks'] = 0;
    }
    if(isset($value['Commission'])){
        $value['Commission'] = "$".number_format($value['Commission'],2);

    }else{
        $value['Commission'] = "$0.00";
    }
    unset($value);
}
unset($DomainTotal['data']);

$sel_cate = array();
if(isset($_GET['categories'])){
    $sel_cate = explode(',',$_GET['categories']);
}
$objTpl->assign('sel_cate', $sel_cate);

$pageHtml = get_page_html($DomainTotal);

$internal = array('csus','csde','csfr','Abby Liu','savelution','bdg','csin','ds','li wei','mk_sns');
$displayStoreType = 0;
if(in_array($_SESSION['u']['Name'], $internal)){
    $displayStoreType = 1;
}
$objTpl->assign('displayStoreType', $displayStoreType);


    $objTran = new Transaction;
    /* $sites = $objTran->table('publisher_account')->where('PublisherId = '.intval($uid))->find();
    $objTpl->assign('sites', $sites); */
    $objTpl->assign('sites', $key);
    $objTpl->assign('username',$_SESSION['u']['UserName']);
	$objTpl->assign('level',$USERINFO['Level']);
//     $objTpl->assign('rstore',$rstore);
    $objTpl->assign('activityStore',$activityStore);
    $objTpl->assign('key',$key);
	$objTpl->assign('category', $category);
	$objTpl->assign('DomainList', $DomainList);
	$objTpl->assign('pageHtml', $pageHtml);
	$objTpl->assign('pagesize', $pagesize);
	$objTpl->assign('search', $_GET);
	$objTpl->assign('countryArr', $countryArr);
	$objTpl->assign('sys_header', $sys_header);

$objTpl->display('b_merchant.html');
?>
