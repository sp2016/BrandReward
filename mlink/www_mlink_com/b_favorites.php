<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init_back.php');
check_user_login();
include_once('auth_ini.php');

//category
$merchant = new MerchantExt();
$category = $merchant->GetCategoryList();

//country
$sql = 'SELECT CountryName,CountryCode FROM country_codes';
$arr = $db->getRows($sql);

foreach($arr as $v){
    $countryArr[$v['CountryName']] = $v['CountryCode'];
}
$countryArr['global'] = 'Global';
$countryArr['United Kingdom'] = 'UK';


$p = isset($_GET['p']) ? $_GET['p'] : 1;
$pagesize = isset($_GET['pagesize']) ? $_GET['p'] : 20;
$uid = $USERINFO['ID'];
$return = $merchant->GetFavorites($_GET,$p,$pagesize,$uid);

$page_html = get_page_html($return);
if(isset($_POST['did'])){
    $uid = $USERINFO['ID'];
    $did = $_POST['did'];
    $res = $merchant->delfavorite($did,$uid);
    echo $res;
    die;
}
if(is_array($return['content']) && count($return['content'])){
    foreach ($return['content'] as $k => &$v){
        $v['StartTime'] = date('Y-m-d',strtotime($v['StartDate']));
        if($v['EndDate'] == '0000-00-00 00:00:00'){
        	$v['ExpireTime'] = 'N/A';
        }else{
        	$v['ExpireTime'] = date('Y-m-d',strtotime($v['EndDate']));
        }
        /*if($v['ImgIsDownload'] == 'NO')
            $v['ImgFile'] = 'http://api.brandreward.com/data/linksIMG/BDG/no_image.png';
        else{
            $v['ImgFile'] = json_decode($v['ImgFile'],TRUE);
            $v['ImgFile'] = "http://api.brandreward.com/data/linksIMG" . $v['ImgFile']['advertiser'];
        }*/
        $v['LinkUrl'] = $v['AffUrl'];
    }
}
if(isset($return['group'])){
    $objTpl->assign('group', $return['group'].'-'.$return['sc']);
}else{
    $objTpl->assign('group','');
}
$sel_cate = array();
if(isset($_GET['categories'])){
    $sel_cate = explode(',',$_GET['categories']);
    unset($sel_cate[count($sel_cate) - 1]);
}

$objTpl->assign('sel_cate', $sel_cate);
$objTpl->assign('pageHtml', $page_html);
$objTpl->assign('search', $_GET);
$objTpl->assign('category', $category);
$objTpl->assign('countryArr', $countryArr);
$objTpl->assign('content', $return['content']);
$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_footer['js'][] = BASE_URL.'/js/b_tran.js';
$sys_footer['js'][] = BASE_URL.'/js/back.js';
$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('sys_footer', $sys_footer);
$objTpl->display('b_favorites.html');