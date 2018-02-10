<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init_back.php');
check_user_login();
include_once('auth_ini.php');
$_GET['id'] = $USERINFO['ID'];
$page = isset($_GET['p'])?$_GET['p']:1;
$pagesize = isset($_GET['pagesize'])?$_GET['pagesize']:20;
$objDomain = new Domain;
$merchant = new MerchantExt();
$category = $merchant->GetCategoryList();
$_GET['uid'] = $USERINFO['ID'];
$DomainTotal = $objDomain->getDomainListPage($_GET,$page,$pagesize);
$sql = 'SELECT CountryName,CountryCode FROM country_codes';
$arr = $db->getRows($sql);
$catArr = getCategory();
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
if(isset($_POST['type'])){
    $id = $_POST['id'];
    $uid = $USERINFO['ID'];
    $res = $objDomain->delcollect($id,$uid);
    echo $res;
    die;
}
foreach($arr as $v){
    $countryArr[$v['CountryName']] = $v['CountryCode'];
}
$countryArr['global'] = 'Global';
$countryArr['United Kingdom'] = 'UK';


    $DomainList = $DomainTotal['data'];
    unset($DomainTotal['data']);
    $pageHtml = get_page_html($DomainTotal);

$sel_cate = array();
if(isset($_GET['categories'])){
    $sel_cate = explode(',',$_GET['categories']);
    unset($sel_cate[count($sel_cate) - 1]);
}
if(isset($_POST['type'])){
    $id = $_POST['id'];
    $uid = $USERINFO['ID'];
    $res = $objDomain->delcollect($id,$uid);
    echo $res;
    die;
}
$objTpl->assign('sel_cate', $sel_cate);
$objTpl->assign('category', $category);
$objTpl->assign('catArr', $catArr);
$objTpl->assign('DomainList', $DomainList);
$objTpl->assign('pageHtml', $pageHtml);
$objTpl->assign('pagesize', $pagesize);
$objTpl->assign('search', $_GET);
$objTpl->assign('countryArr', $countryArr);
$sys_header['css'][] = BASE_URL.'/css/front.css';
$objTpl->assign('sys_header', $sys_header);
$objTpl->display('b_collect.html');
?>