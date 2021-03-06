<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init_back.php');
check_user_login();
include_once('auth_ini.php');
$uid = $USERINFO['ID'];
//productfeed目前只开放给mk用
if($_SESSION['u']['ID']>10 && $_SESSION['u']['ID'] != 90706 && $_SESSION['u']['ID'] != 90741){
    header("Location:b_merchants.php");
    exit();
}
//category
$merchant = new MerchantExt();
$uid = $USERINFO['ID'];
$category = $merchant->MineCategoryList($uid);
include_once('auth_ini.php');

/* if(!isset($_SESSION['u']['apikey'])){
    $account_info = $objAccount->get_account_info($_SESSION['u']['ID']);
    $_SESSION['u']['apikey'] = $account_info['site'][0]['ApiKey'];
} */

//第一次进来时默认查询语言为该publisher的受众国家（主要客户是哪个国家的）里的第一个国家，id为3是英国，7为德国，82为法国，其它默认显示all
if (!isset($_GET['language'])){
    $sql = 'SELECT GeoBreakdown FROM publisher_detail where Publisherid = '.$uid;
    $geoBreakdown = $db->getFirstRow($sql);
    $countryList = explode('+', $geoBreakdown['GeoBreakdown']);
    if(isset($countryList[0]) && $countryList[0]!=''){
        $sql = 'SELECT language from country_codes where id = '.$countryList[0];
        $rs = $db->getFirstRow($sql);
        switch ($rs['language']){
            case 'en':
                $_GET['language'] = 'en';
                break;
            case 'de':
                $_GET['language'] = 'de';
                break;
            case 'fr':
                $_GET['language'] = 'fr';
                break;
        }
    }
}

$p = isset($_GET['p']) ? $_GET['p'] : 1;
$pagesize = isset($_GET['pagesize']) ? $_GET['p'] : 20;
$page_start = $pagesize * ($p - 1);

$return = $merchant->GetProductFeed($_GET,$page_start,$pagesize,$uid);
$page_html = get_page_html($return);

if(is_array($return['content']) && count($return['content'])){
    $apikeytxt = isset($_SESSION['pubAccActiveList']['active'])?reset($_SESSION['pubAccActiveList']['data'])['ApiKey']:$_SESSION['u']['apikey'];
    foreach ($return['content'] as $k => $v){
        if($return['content'][$k]['ProductCurrencySymbol'] != ''){
            $return['content'][$k]['ProductPrice'] = $v['ProductCurrencySymbol'].round($v['ProductPrice'],2);
        }else {
            $return['content'][$k]['ProductPrice'] = $v['ProductCurrency'].round($v['ProductPrice'],2);
        }
        ///app/site/ezconnexion.com/web/img/12/product/139795_GB78_150px.png
        //https://www.brandreward.com/img/12/product/105651_646446_150px.png
        if(preg_match('@/app/site/ezconnexion.com/web/img/(.*?)$@',$v['ProductLocalImage'],$matche)){
            $return['content'][$k]['ProductImage'] = "https://www.brandreward.com/img/".$matche[1];
        }
        $return['content'][$k]['ProductDesc'] = htmlspecialchars($v['ProductDesc']);
        $return['content'][$k]['LinkUrl'] = 'http://r.brandreward.com/?key='.$apikeytxt.'&linkid='.urlencode($v['EncodeId']);
    }
}

    $sel_cate = array();
    if(isset($_GET['categories'])){
        $sel_cate = explode(',',$_GET['categories']);
        //unset($sel_cate[count($sel_cate) - 1]);
    }
    
    //country
    $sql = 'SELECT CountryName,CountryCode FROM country_codes';
    $arr = $db->getRows($sql);
    foreach($arr as $val){
        $countryArr[$val['CountryName']] = $val['CountryCode'];
    }
    $countryArr['global'] = 'Global';
    $countryArr['United Kingdom'] = 'UK';
    $objTpl->assign('countryArr', $countryArr);
  
    /* $objTran = new Transaction;
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
    $objTpl->assign('sel_cate', $sel_cate);
    $objTpl->assign('pageHtml', $page_html);
    $objTpl->assign('search', $_GET);
    $objTpl->assign('category', $category);
     
    $objTpl->assign('content', $return['content']);
    
    $sys_header['css'][] = BASE_URL.'/css/front.css';
	$sys_header['css'][] = BASE_URL.'/css/jquery.bxslider.css';
	$sys_header['css'][] = BASE_URL.'/css/masonry/demo.css';
	$sys_header['js'][] = BASE_URL.'/js/jquery.bxslider.min.js';
// 	$sys_header['js'][] = BASE_URL.'/js/jquery.zclip.min.js';
	$sys_header['js'][] = BASE_URL.'/js/clipboard.min.js';
    $sys_footer['js'][] = BASE_URL.'/js/b_tran.js';
	$sys_footer['js'][] = BASE_URL.'/js/back.js';
	
	$sys_header['js'][] = BASE_URL.'/js/masonry/modernizr.custom.js';
	$sys_header['js'][] = BASE_URL.'/js/masonry/imagesloaded.pkgd.min.js';
	$sys_header['js'][] = BASE_URL.'/js/masonry/masonry.pkgd.min.js';
	$sys_header['js'][] = BASE_URL.'/js/masonry/classie.js';
	$sys_header['js'][] = BASE_URL.'/js/masonry/cbpgridgallery.js';
	
	$objTpl->assign('sys_header', $sys_header);
    $objTpl->assign('sys_footer', $sys_footer);
    $objTpl->display('b_product_feed.html');
