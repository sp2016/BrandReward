<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init_back.php');
check_user_login();
include_once('auth_ini.php');
$uid = $USERINFO['ID'];
//category
$merchant = new MerchantExt();
$uid = $USERINFO['ID'];
$category = $merchant->MineCategoryList($uid);
include_once('auth_ini.php');

if(!isset($_SESSION['u']['apikey'])){
    $account_info = $objAccount->get_account_info($_SESSION['u']['ID']);
    $_SESSION['u']['apikey'] = $account_info['site'][0]['ApiKey'];
}
	//test
	//$USERINFO['Level'] = 'TIER1';
    if($USERINFO['Level'] == 'TIER1')
    {
        $topCilckPromotions = $merchant->TopCilckPromotions($uid);
		foreach ($topCilckPromotions as &$v){
			if($v['StartDate'] == '0000-00-00 00:00:00'){
				$v['StartDate'] = 'N/A';
			}else{
				$v['StartDate'] = date('Y-m-d H:i:s',strtotime($v['StartDate']));
			}
			if($v['EndDate'] == '0000-00-00 00:00:00'){
				$v['EndDate'] = 'N/A';
			}else{
				$v['EndDate'] = date('Y-m-d',strtotime($v['EndDate']));
			}
			$v['StoreName'] = ucwords($v['StoreName']);
			$v['AffUrl'] = 'http://r.brandreward.com/?key='.$_SESSION['u']['apikey'].'&url='.urlencode($v['AffUrl']);
			unset($v);
		}
		//var_dump($topCilckPromotions);
		$objTpl->assign('topCilckPromotions',$topCilckPromotions);
	}
//country
$sql = 'SELECT CountryName,CountryCode FROM country_codes';
$arr = $db->getRows($sql);
if(isset($_POST['search'])){
        if($_POST['search'] == 'domain'){
            $val = 'vals1';
            $left = 'margin-left:58px;';
        }else{
            $val = 'vals';
            $left = 'margin-left:70px;';
        }
        $res = $merchant->GetsSearch($_POST['search'],$uid);
        $html ='<ul class="dropdown-menu" style="display: block;'.$left.'height:200px;overflow-y:scroll;">';
        foreach($res as $k){
            $html.="<li style='text-align: center;'><a class='".$val."' href='javascript:void(0);'  data-val=".$k['svalue']." data-id=".$k['ID'].">".$k['svalue']."</a></li>";
        }
        $html.='</ul>';
        echo $html;
        die;
}
if(isset($_POST['fid'])){
    $uid = $USERINFO['ID'];
    $res = $merchant->addfavorite($_POST['fid'],$uid,$_POST['aname'],$_POST['type']);
    echo $res;
    die;
}
if(isset($_POST['check'])){
    $uid = $USERINFO['ID'];
    $res = $merchant->checkfavorite($uid);
    if(!empty($res)){
        $num = '';
        foreach($res as $k){
            $num.=$k['cid'].',';
        }
        echo rtrim($num,',');
    }else{
        echo 0;
    }

    die;
}
foreach($arr as $v){
    $countryArr[$v['CountryName']] = $v['CountryCode'];
}
$countryArr['global'] = 'Global';
$countryArr['United Kingdom'] = 'UK';

$p = isset($_GET['p']) ? $_GET['p'] : 1;
$pagesize = isset($_GET['pagesize']) ? $_GET['p'] : 20;
$return = $merchant->GetContent($_GET,$p,$pagesize,$uid);
$page_html = get_page_html($return);

if(is_array($return['content']) && count($return['content'])){
    foreach ($return['content'] as $k => &$v){
        //$v['StartTime'] = date('Y-m-d',strtotime($v['StartDate']));
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
        /*if($v['ImgIsDownload'] == 'NO')
            $v['ImgFile'] = 'http://api.brandreward.com/data/linksIMG/BDG/no_image.png';
        else{
            $v['ImgFile'] = json_decode($v['ImgFile'],TRUE);
            $v['ImgFile'] = "http://api.brandreward.com/data/linksIMG" . $v['ImgFile']['advertiser'];
        }*/
        $v['LinkUrl'] = 'http://r.brandreward.com/?key='.$_SESSION['u']['apikey'].'&url='.urlencode($v['AffUrl']);
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
        //unset($sel_cate[count($sel_cate) - 1]);
    }
    $objTpl->assign('level',$USERINFO['Level']);
	$objTpl->assign('sel_cate', $sel_cate);
    $objTpl->assign('pageHtml', $page_html);
    $objTpl->assign('search', $_GET);
    $objTpl->assign('category', $category);
    $objTpl->assign('countryArr', $countryArr);
    $objTpl->assign('content', $return['content']);
    $objTpl->assign('adv', $return['adv']);
    $objTpl->assign('dom', $return['dom']);
    $sys_header['css'][] = BASE_URL.'/css/front.css';
	$sys_header['css'][] = BASE_URL.'/css/jquery.bxslider.css';
	$sys_header['js'][] = BASE_URL.'/js/jquery.bxslider.min.js';
	$sys_header['js'][] = BASE_URL.'/js/jquery.zclip.min.js';
    $sys_footer['js'][] = BASE_URL.'/js/b_tran.js';
	$sys_footer['js'][] = BASE_URL.'/js/back.js';
	$objTpl->assign('sys_header', $sys_header);
    $objTpl->assign('sys_footer', $sys_footer);
    $objTpl->display('b_content.html');