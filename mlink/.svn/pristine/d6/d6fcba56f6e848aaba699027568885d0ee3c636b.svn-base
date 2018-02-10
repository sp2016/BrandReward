<?php
/*
 * Created on 2007-10-1
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

function getOpenSearchStr()
{
	$img = "";
	if(stripos($_SERVER['HTTP_USER_AGENT'], "firefox") !== false)
		$img = CDN_LINK_ROOT."image/opensearch_firefox.gif";
	else if(stripos($_SERVER['HTTP_USER_AGENT'], "msie 7") !== false)
		$img = CDN_LINK_ROOT."image/opensearch_ie7.gif";
	if($img)
	{
		$res = "<a href='#' rel='nofollow' onClick=\"";
		$res .= "javascript:window.external.AddSearchProvider('".LINK_ROOT."opensearch.xml');\"";
		$res .= "><img src='$img' border='0' align=\"absmiddle\"></a>";
		return $res;
	}
	return "";
}

function get_header($objTpl,$title="",$keyword="",$page2ndTitle="",$desc="",$rssFileInfo="")
{
	$objTpl->define(array('header' => 'header.tpl'));
	
	if(is_array($title))
	{
		$rssFileInfo = $title["rssFileInfo"];
		$keyword = $title["metaKw"];
		$page2ndTitle = $title["seoHeadLine"];
		$desc = $title["metaDesc"];
		//title must be in last line here 
		$title = $title["metaTitle"];
	}
	
	$title = trim($title, "\r\n");
	$keyword = trim($keyword, "\r\n");
	$page2ndTitle = trim($page2ndTitle, "\r\n");
	$desc = trim($desc, "\r\n");
	$rssFileInfo = trim($rssFileInfo, "\r\n");
	
	if(!$title) $title = DEFAULT_PAGE_TITLE;
	if(!$keyword) $keyword = DEFAULT_PAGE_KW;
	if(!$page2ndTitle) $page2ndTitle = DEFAULT_PAGE_2ND_TITLE;
	if(!$desc) $desc = DEFAULT_PAGE_DESC;
	
	/*
	 * customize topbarlink
	 */
	global $g_topbarlink;
	$topbarlink="";
	if(isset($g_topbarlink) && count($g_topbarlink)){
		foreach($g_topbarlink as $k=>$v){
			if(!empty($topbarlink))$topbarlink.="&nbsp;|&nbsp;&nbsp;";
			$url_id="";
			if(!empty($v[3]))$url_id="id='".$v[3]."'";
			$keyword_text=str_replace('\"','"',$v[0]);
			$topbarlink.="<a href=\"".htmlspecialchars($v[2])."\" title='".htmlspecialchars($v[1])."' $url_id>".htmlspecialchars($keyword_text)."</a>";			
		}
	}
	
	$objTpl->assign(array('link_path' => LINK_ROOT,
				'cdn_link_path' => CDN_LINK_ROOT,
				'page_title' => $title,
				'all_stores_url' => get_rewrited_url('allstores'),
				'new_coupon_url' => get_rewrited_url('newcoupon'),
				'expiring_coupon_url' => get_rewrited_url('expirecoupon'),
				'popular_coupon_url' => get_rewrited_url('hotcoupon'),
				'rss_url' => get_rewrited_url('rssfeed'),
				'page_keyword' => $keyword,
				'popular_coupon_tag'=>get_rewrited_url('taglist'),
				'page_description' => $desc,
				//'user_info' => $myAcctInfo,
				'page_second_title' => $page2ndTitle,
				'rss_file_info'=>$rssFileInfo,
				'topbarlink'=>$topbarlink
	));
	$objTpl->parse('OUT', "header");
	return $objTpl->TEMPLATE['header']['result'];
}

function get_footer($objTpl, $arrParameters='', $arrBlocks='')
{
	$objTpl->define(array( 'footer' => 'footer.tpl'));
	
	for($i = ord('A'); $i <= ord('Z'); $i++)
	{
		$nav .= "<a href='".get_rewrited_url('storelist', chr($i), '')."'>".chr($i)."</a>&nbsp;&nbsp;&nbsp;";
	}
	$nav .= "<a href='".get_rewrited_url('storelist', 'other', '')."'>Other</a>";
	
	/*
	 * customize footerlink
	 */
	global $g_footerlink_line1;
	$footerlink_line1="";
	if(isset($g_footerlink_line1) && count($g_footerlink_line1)){
		foreach($g_footerlink_line1 as $k=>$v){
			if(!empty($footerlink_line1))$footerlink_line1.="&nbsp;|&nbsp;&nbsp;";
			$url_id="";
			if(!empty($v[3]))$url_id="id='".$v[3]."'";
			$keyword_text=str_replace('\"','"',$v[0]);
			$footerlink_line1.="<a href=\"".htmlspecialchars($v[2])."\" title='".htmlspecialchars($v[1])."' $url_id>".htmlspecialchars($keyword_text)."</a>";			
		}
	}
	global $g_footerlink_line2;
	$footerlink_line2="";
	if(isset($g_footerlink_line2) && count($g_footerlink_line2)){
		foreach($g_footerlink_line2 as $k=>$v){
			if(!empty($footerlink_line2))$footerlink_line2.="&nbsp;|&nbsp;&nbsp;";
			$url_id="";
			if(!empty($v[3]))$url_id="id='".$v[3]."'";
			$keyword_text=str_replace('\"','"',$v[0]);
			$footerlink_line2.="<a href=\"".htmlspecialchars($v[2])."\" title='".htmlspecialchars($v[1])."' $url_id>".htmlspecialchars($keyword_text)."</a>";			
		}
	}
	global $g_footerlink_line3;
	$footerlink_line3="";
	if(isset($g_footerlink_line3) && count($g_footerlink_line3)){
		foreach($g_footerlink_line3 as $k=>$v){
			if(!empty($footerlink_line3))$footerlink_line3.="&nbsp;|&nbsp;&nbsp;";
			$url_id="";
			if(!empty($v[3]))$url_id="id='".$v[3]."'";
			$keyword_text=str_replace('\"','"',$v[0]);
			$footerlink_line3.="<a href=\"".htmlspecialchars($v[2])."\" title='".htmlspecialchars($v[1])."' $url_id>".htmlspecialchars($keyword_text)."</a>";			
		}
	}
	
	$objTpl->assign(array(
		'link_path' => LINK_ROOT,
		'cdn_link_path' => CDN_LINK_ROOT,
		'copyright_year' => date("Y"),
		'archive_url' => get_rewrited_url('archive', 'history', 'all'),
		'partner_url' => get_rewrited_url('partner'),
		'help_url' => get_rewrited_url('help'),
		'policy_url' => get_rewrited_url('policy'),
		'hot_search_url' => get_rewrited_url('hotkeyword'),
		'link_cur_url' => urlencode(rtrim(LINK_ROOT, "/").$_SERVER['REQUEST_URI']),
		'opensearch_str' => getOpenSearchStr(),
		'merchant_alphabet_nav' =>$nav,
		'footerlink_line1' =>$footerlink_line1,
		'footerlink_line2' =>$footerlink_line2,
		'footerlink_line3' =>$footerlink_line3
	));

	//add by ryan 2009-05-04 //
	if ($arrParameters != ''){
		$objTpl->assign($arrParameters);
	}
	//
	if ($arrBlocks == ''){
		$arrBlocks = array(
			'footer_desc_for_normal_page'	=> true,
			);
	}
	//
	if ($arrBlocks != ''){
		foreach($arrBlocks as $strBlockName => $bBlockShow){
			if ($bBlockShow){
				$objTpl->parse('OUT', $strBlockName);
			}
		}
	}
	///////////////////////////

	$objTpl->parse('OUT', "footer");
	return $objTpl->TEMPLATE['footer']['result'];
}

function get_merchant_name_by_get_var($objMysql)
{
	$merchantId = intval(get_get_var('mid'));
	$objMerchant = new NormalMerchant($objMysql);
	$arrMer = $objMerchant->getMerchantById($merchantId);
	$name = trim($arrMer['name']);
	return $name;
}

function get_category_name_by_get_var($objMysql)
{
	$cateId = intval(get_get_var('cateid'));
	$objCategory = new NormalCategory($objMysql);
	$arrCate = $objCategory->getCategoryById($cateId);
	$name = trim($arrCate['name']);
	return $name;
}

/*
 * format coupon expiration date
 */
function format_coupon_expire_date($date, $default="unknown", $returnType=0)
{
	if(strcmp($date, '0000-00-00 00:00:00') == 0)
		return $default;
	elseif($returnType == 1)
		return date("m/d/Y", strtotime($date));
	else
		return date("M j, Y", strtotime($date));
}


function get_main_content($objTpl, $objMysql, &$mainContent='', $seKW='', $specificalH1="",$pagename="")
{
	global $g_mainpage_sidebar_arrFeatureMer;
	global $g_mainpage_sidebar_arrFeatureMer_specialName;
	global $g_catepage_arrBanneCfg;
	
	$objTpl->define(array( 'main' => 'main.tpl'));
	
	$couponalerturl = LINK_ROOT."front/couponalert.php";
	$arrCouponalerturlGetVar = array();

	//get merchant list
	$curMerchantId = intval(get_get_var('mid'));
	$objMerchant = new NormalMerchant($objMysql);
	
	if(count($g_mainpage_sidebar_arrFeatureMer))
		$whereStr = "ID in (".implode(",", $g_mainpage_sidebar_arrFeatureMer).")";
	else
		$whereStr = "ID < 1"; //fake, just do not want to be an error here when the $g_mainpage_sidebar_arrFeatureMer is empty

	$arrMer = $objMerchant->getMerBaseInfoByLimitStr("", $whereStr, " ID ASC ");

	//allow manually set the order, modified by jimmy @ 20090711
	$arrMerKeyWithMID = array();
	foreach($arrMer as $m)
	{
		$arrMerKeyWithMID[$m['id']] = $m;
	}

	$arrMer = array();
	foreach($g_mainpage_sidebar_arrFeatureMer as $m)
	{
		$arrMer[] = $arrMerKeyWithMID[$m];
	}
	//end
	
	reset($arrMer);
	while(list($k, $v) = each($arrMer))
	{
		$id = intval($v['id']);
		$name = trim($v['name']);
		$urlname = trim($v['UrlName']);
		$merchantUrlInfo = array("name"=>$name, "urlname" =>$urlname);
		$merUrl  = get_rewrited_url('merchant', $merchantUrlInfo, $id);
		
		//allow use to customize merchant name 
		if(isset($g_mainpage_sidebar_arrFeatureMer_specialName[$id]))
		{
			$customizeMerName = $g_mainpage_sidebar_arrFeatureMer_specialName[$id];
			$name = str_ireplace("{merchantname}", $name, $customizeMerName);
		}
		//end
		$name = $curMerchantId == $id ? "<img src='".LINK_ROOT."image/currentItem.gif' " .
										"border=0>&nbsp;<b>{$name}</b>"
										: "<a href='$merUrl'>{$name}</a>";
		
		$objTpl->assign(array('merchant_url' => $merUrl,
							'merchant_name' => $name,
							'all_stores_url' => get_rewrited_url('allstores'),
							'link_path' => LINK_ROOT));
		$objTpl->parse('OUT', '.nav_merchant');
	}
	//end
	if($curMerchantId) $arrCouponalerturlGetVar[] = "selmid=$curMerchantId";

	//get category list
	$curCateId = intval(get_get_var('cateid'));
	$curCateName = "";
	$objCate = new NormalCategory($objMysql);
	$arrCate = $objCate->getCategoryListByLimitStr("", "", "Navigation ASC");
	foreach ($arrCate as $v)
	{
		$id = intval($v['id']);
		$name = trim($v['name']);
		$urlname = trim($v['urlname']);
		$catUrlInfo = array("name" => $name, "urlname" => $urlname);
		$cateUrl = get_rewrited_url('category', $catUrlInfo, $id);
		if($curCateId == $id)
			$curCateName = $name;
//		$objTpl->assign(array('category_name' => htmlspecialchars($name),
//					'category_url' => $cateUrl,
//					'link_path' => LINK_ROOT));
//		$objTpl->parse('OUT', '.nav_category');
	}
	//end
	if($curCateId && $curCateName) $arrCouponalerturlGetVar[] = "selkw=".urlencode($curCateName);

	//get tag name, if it is tag page
	$curTagId = intval(get_get_var('tagid'));
	if($curTagId)
	{
		$objTag = new Tag($objMysql);
		$tagName = $objTag->getTagNameByID($curTagId);
	}
	if($curTagId && $tagName) $arrCouponalerturlGetVar[] = "selkw=".urlencode($tagName);
	//end
	
	//get coupon cnt and last update time
	$objCoupon = new NormalCoupon($objMysql);
	if(COUPON_CNT_CACHE_FILE && is_readable(COUPON_CNT_CACHE_FILE))
	{
		$cachefileContent = @file_get_contents(COUPON_CNT_CACHE_FILE);
		list($activeCouponCnt, $lastUpdateTime) = explode("\t", $cachefileContent);
		$activeCouponCnt = number_format($activeCouponCnt);
		$lastUpdateTime = trim($lastUpdateTime);
	}
	else
	{
		$activeCouponCnt = $objCoupon->getAcitiveCouponCnt();
		$activeCouponCnt = $activeCouponCnt >= 1500 ? $activeCouponCnt : 1500;
		$activeCouponCnt = number_format($activeCouponCnt);
		$updateTime = $objCoupon->getCouponLastUpdateTime();
		$lastUpdateTime = date("M j, Y", strtotime($updateTime));
	}
	//end
	
	//get H1 title
	$page3rdTitle = DEFAULT_PAGE_3RD_TITLE;
	if($specificalH1)
	{
		$page3rdTitle = $specificalH1;
	}
	else 
	{
		if($curMerchantId)//merchant page
		{
			$arrTmp = $objMerchant->getMerchantById($curMerchantId);
			$name = $arrTmp['name'];
			$seotitle1 = $arrTmp['SEOTitle1'];
			$page3rdTitle = $seotitle1 ? $seotitle1 : str_ireplace("{name}", $name, PAGE_3RD_TITLE_TPL);
		}
		else if($curCateId) //cate page
		{
			$arrTmp = $objCate->getCategoryById($curCateId);
			$name = $arrTmp['name'];
			$page3rdTitle = str_ireplace("{name}", $name, PAGE_3RD_TITLE_TPL);
		}
		else if($curTagId) //tag page
		{
			$page3rdTitle = str_ireplace("{name}", $tagName, PAGE_3RD_TITLE_TPL);
		}
		else if($seKW)
		{
			$page3rdTitle = str_ireplace("{name}", $seKW, PAGE_3RD_TITLE_TPL2);
		}
	}
	//end
	
	if($pagename == "")
	{
		if($curMerchantId) $pagename = "merchant";
		elseif($curCateId) $pagename = "category";
		elseif($curTagId) $pagename = "tag";
		elseif($seKW) $pagename = "search";
	}

	include_once(INCLUDE_ROOT."lib/Class.Ads.mod.php");
	$objAds = new Ads($objMysql);
	$objAds->fillPageAds($objTpl,"frame",$pagename);
	
	$objTpl->assign(array('link_path' => LINK_ROOT,
				'cdn_link_path' => CDN_LINK_ROOT,
				'home_url' => get_rewrited_url('homepage'),
				'dosearch_url' => get_rewrited_url('dosearch'),
				'search_keyword' => htmlspecialchars(trim(get_get_var('keyword'))),
				'coupon_cnt' => $activeCouponCnt,
				'update_time' => $lastUpdateTime,
				'main_content' => $mainContent,
				'page_3rd_title' => $page3rdTitle,
				//'ads_top1' => $objAds->getAdsCodeByPos('frame-leftsidebar', 0, "Class='mar_bot'"),
				'coupon_alert_url' => count($arrCouponalerturlGetVar) ? $couponalerturl."?".implode("&", $arrCouponalerturlGetVar) : $couponalerturl,
							));
	$objTpl->parse('OUT', "main");
	return $objTpl->TEMPLATE['main']['result'];	
}

function permanent_header($url='')
{
	$url = !trim($url) ? get_rewrited_url("homepage") : $url;
	Header( "HTTP/1.1 301 Moved Permanently");
	Header( "Location: $url");
	exit;
}

function filter_source_tag()
{
	$tags = array("mktsrc","ca");
	foreach($tags as $i => $_tag) $tags[] = strtoupper($_tag);
	
	$filtered_url = "";
	$url = $_SERVER['REQUEST_URI'];
	$pattern = "/(.*)(\\/|html|php)(&|\\?)(.*)/i";
	if(! preg_match($pattern,$url,$matches))
	{
		//something wrong here: not standard format
		return false;
	}
	
	$filtered_url = $matches[1] . $matches[2];
	$arr_vars = preg_split("/[&\\?]/",$matches[4]);
	$matchedcount = 0;
	foreach($arr_vars as $i => $_var)
	{
		list($var_name) = explode("=",$_var);
		if(in_array($var_name,$tags) || $_var == "")
		{
			$matchedcount ++;
			unset($arr_vars[$i]);
		} 
	}

	if($matchedcount > 0)
	{
		if(sizeof($arr_vars)) $filtered_url .= $matches[3] . implode("&",$arr_vars);
		permanent_header($filtered_url);
	}
}

/**
 * coupon item block
 *
 * @param unknown_type $objMysql
 * @param unknown_type $objTpl
 * @param unknown_type $arrCouponId
 * @param unknown_type merchant, homepage, archive, category, tag, homepage~newest, homepage~pop, 
 * @param string $sorting
 * @return string
 */

function get_coupon_list_smarty($objMysql,$returnType,$arrCouponId, $pageType='merchant',$sorting="",$_arrWhere=array())
{
	global $g_merListWithoutSeeCouponButton;
	global $g_merListUseRealSeeDealButton;

	if($returnType == "array")
	{
		$default_return = array();
		$cdn_link_root = "/";
	}
	else
	{
		$default_return = "";
		$cdn_link_root = CDN_LINK_ROOT;
	}
	if(!count($arrCouponId)) return $default_return;
	
	$objCoupon = new NormalCoupon($objMysql);

	//by ike:we always use sorting
	$arrCoupon = $objCoupon->getCouponByCouponID($arrCouponId,$sorting,$_arrWhere);
	$allGottenCouponId = array();

	//preprocessing coupon tags and category
	$objTag = new Tag($objMysql);
	$objCate = new NormalCategory($objMysql);

	$arrAllCategoryId = array();
	$arrAllTagId = array();
	$arrTags = array();
	$arrCate = array();
	foreach($arrCoupon as $k => $arrSingleCoupon)
	{
		$tag = $arrSingleCoupon['tag'];
		$categoryid = $arrSingleCoupon['categoryid'];
		$arrCoupon[$k]["arrTagId"] = array();
		$arrAllCategoryId[$categoryid] = $categoryid;
		
		if(!$tag) continue;
		$arrTmpTags = explode(',', $tag);
		foreach($arrTmpTags as $_tagId)
		{
			$_tagId = trim($_tagId);
			if($_tagId == "" || !is_numeric($_tagId)) continue;
			$arrCoupon[$k]["arrTagId"][] = $_tagId;
			$arrAllTagId[$_tagId] = $_tagId;
		}
	}
	
	if(sizeof($arrAllTagId))
	{
		$arrTags = $objTag->getAllTags('id','keepcase',$arrAllTagId);
	}
	
	if(sizeof($arrAllCategoryId))
	{
		$arrTmp = $objCate->getCategoryListByLimitStr("","ID in (" . implode(",",$arrAllCategoryId) . ")");
		foreach($arrTmp as $k => $cate)
		{
			$arrCate[$cate['id']] = $cate['name'];
		}
	}
	
	foreach($arrCoupon as $k => $arrSingleCoupon)
	{
		$Id = $arrSingleCoupon['id'];
		$allGottenCouponId[$Id] = $k;

		$img = $arrSingleCoupon['imgurl'] ? $arrSingleCoupon['imgurl'] : "/resizedMerImage/".$arrSingleCoupon['merchantlogo'];

		//for merchant page, we only show coupon image
		if ($pageType == "merchant" && !$arrSingleCoupon['imgurl'])
		{
			$img = "";
		}	
		//for home page, we only show merchant image
		if($pageType == "homepage" || $pageType == "channel")
		{
			$img = "/resizedMerImage/".$arrSingleCoupon['merchantlogo'];
		}
		
		$isExpired = false;
		if((strcmp($arrSingleCoupon['expiration'], '0000-00-00 00:00:00') != 0) 
			&& strcmp($arrSingleCoupon['expiration'], date("Y-m-d 00:00:00")) < 0)
		{
			$isExpired = true;
			$arrCoupon[$k]["isExpiredCoupon"] = 1;
		}
//		if($_GET['debug']) print_R($arrSingleCoupon);
		if(!$isExpired && ($pageType == "merchant" || $pageType == "category" || $pageType == "tag" || $pageType == "homepage")
		|| $pageType == "homepage~newest" || $pageType == "bd")
		{
			if(((time() - strtotime($arrSingleCoupon['addtime'])) < 86400*2))
			{
				$arrCoupon[$k]["isNewCoupon"] = 1;
			}
			if((strcmp($arrSingleCoupon['expiration'], '0000-00-00 00:00:00') != 0) && (strtotime($arrSingleCoupon['expiration']) - time()) < 86400*2)
			{
				$arrCoupon[$k]["isHurriedCoupon"] = 1;
			}
		}
		
		$arrCoupon[$k]["isExclusive"] = 0;
		if($arrSingleCoupon["code"] != '' AND $arrSingleCoupon["type"] == 5) $arrCoupon[$k]["isExclusive"] = 1;
		
		$arrCoupon[$k]["coupon_expire_date"] = format_coupon_expire_date($arrSingleCoupon['expiration'], "");
		$arrCoupon[$k]["coupon_expire_date_simple"] = format_coupon_expire_date($arrSingleCoupon['expiration'],"",1);
		
		$cate = $arrCate[$arrSingleCoupon['categoryid']];
		$arrTagId = $arrSingleCoupon["arrTagId"];
		$arrCoupon[$k]["arrCouponTag"] = array();
		if(sizeof($arrTagId))
		{
			$tagCount = 0;
			foreach($arrTagId as $tmpTag)
			{
				if(!isset($arrTags[$tmpTag])) continue;
				$tagUrlName = $objTag->getUrlNameById($tmpTag);
				$tagUrlInfo = array("name" => trim($arrTags[$tmpTag]), "urlname" => $tagUrlName);
				$arrTagInfo = array(
								'single_tag_url' => get_rewrited_url('tag', $tagUrlInfo, $tmpTag),
								'single_tag_name' => $arrTags[$tmpTag],
							);
				$arrCoupon[$k]["arrCouponTag"][] = $arrTagInfo;
				$tagCount ++;
			}
			
			$arrCoupon[$k]["CouponTagCount"] = $tagCount;
		}
		
		$mer = $arrSingleCoupon['merchantname'];
		$catURlName = $objCate->getCatUrlNameById($arrSingleCoupon['categoryid']);
		$merchnatUrlInfo = array("name" => trim($mer), "urlname" => trim($arrSingleCoupon['urlname']));
		$catUrlInfo = array("name" => trim($cate), "urlname" => $catURlName);
		if($img && file_exists(INCLUDE_ROOT.ltrim($img, "/")))
		{
			$arrCoupon[$k]["hasImage"] = 1;
			
			if(stripos($pageType, "homepage") === 0){
				$strcoupon_image_url = get_rewrited_url('merchant', $merchnatUrlInfo, $arrSingleCoupon['merchantid']);
			}
			else if(stripos($pageType, "tag") === 0){
				$strcoupon_image_url = get_rewrited_url('merchant', $merchnatUrlInfo, $arrSingleCoupon['merchantid']);
			}
			else if($pageType == "bd"){
				$strcoupon_image_url = get_rewrited_url('merchant', $merchnatUrlInfo, $arrSingleCoupon['merchantid']);
			}
			else{
				$strcoupon_image_url = get_rewrited_url('redirect', 'coupon', $Id)."&ca=".$pageType."_coupon_image";
			}
			
			//$arrCoupon[$k]["ImageInfo"] = array('coupon_image' => $cdn_link_root.ltrim($img, "/"),
			$arrCoupon[$k]["ImageInfo"] = array('coupon_image' => "/" . ltrim($img, "/"),
							'merchant_name' => $mer,
							'coupon_image_url' => $strcoupon_image_url,
							);
		}
		else
		{
			$arrCoupon[$k]["hasImage"] = 0;
		}
		
		$arrCoupon[$k]["merchant_name"] = $mer;
		$arrCoupon[$k]["merchant_url"] = get_rewrited_url('merchant', $merchnatUrlInfo, $arrSingleCoupon['merchantid']);
		$arrCoupon[$k]["coupon_description"] = $arrSingleCoupon['remark'];
		$arrCoupon[$k]["coupon_rd_url"] = get_rewrited_url('redirect', 'coupon', $Id);
		$arrCoupon[$k]["relative_coupon_rd_url"] = preg_replace("/^http:\/\/.*?\//i",'/', get_rewrited_url('redirect', 'coupon', $Id));
		$arrCoupon[$k]["coupon_detail"] = get_rewrited_url('coupondetail', 'coupon', $Id);
		$arrCoupon[$k]["cate_url"] = get_rewrited_url('category', $catUrlInfo, $arrSingleCoupon['categoryid']);
		$arrCoupon[$k]["category_name"] = $cate;
		$arrCoupon[$k]["comm_url"] = get_rewrited_url('couponcomment', $mer, $Id);

		/////////////////check if we can show see coupon/deal button/////////////////
		$arrCoupon[$k]["showSeeCouponBtn"] = 1;
		if(isset($g_merListWithoutSeeCouponButton) && in_array($arrSingleCoupon['merchantid'], $g_merListWithoutSeeCouponButton))
		{
			$arrCoupon[$k]["showSeeCouponBtn"] = 0;
		}
		/////////////////end////////////////////////////////////////////////////////////////

		if($arrSingleCoupon['code'])
		{
			$arrCoupon[$k]["coupon_or_deal"] = 'c';
			
			//coupon, use see coupon button
			if($arrCoupon[$k]["showSeeCouponBtn"] && ($pageType == "merchant" || $pageType == "category" || $pageType == "tag" || $pageType == "search" || $pageType == "bd"))
			{
				$arrCoupon[$k]["showSeeCouponBtn"] = 1;
			}
			else
			{
				$arrCoupon[$k]["showSeeCouponBtn"] = 0;
			}
		}
		else
		{
			//we might use see deals button in the future
			$arrCoupon[$k]["coupon_or_deal"] = 'd';
			$arrCoupon[$k]["showSeeDealBtn"] = 0;
			if($arrCoupon[$k]["showSeeCouponBtn"] && ($pageType == "merchant" || $pageType == "category" || $pageType == "tag" || $pageType == "search" || $pageType == "bd"))
			{
				if(isset($g_merListUseRealSeeDealButton) && in_array($arrSingleCoupon['merchantid'], $g_merListUseRealSeeDealButton))
				{
					$arrCoupon[$k]["showSeeDealBtn"] = 1;
				}
				else
				{
					$arrCoupon[$k]["showSeeDealBtn"] = 2;
				}
			}
		}
		
		$counter++;
	}
	
	if($sorting == "")
	{
		//by ike:here we re-sort it by default
		$arrTmp = array();
		foreach($arrCouponId as $_id)
		{
			if(!isset($allGottenCouponId[$_id])) continue;
			$arrTmp[$_id] = $arrCoupon[$allGottenCouponId[$_id]];
		}
		$arrCoupon = array_values($arrTmp);
	}
	
	//return array
	if($returnType == "array") return $arrCoupon;
	//return html
	global $blockid;
	if(!isset($blockid)) $blockid = 1;
	else $blockid++;
	include_once(INCLUDE_ROOT."lib/Class.TemplateSmarty.php");
	$objTpl = new TemplateSmarty();
	$objTpl->assign('cdn_link_path',rtrim(CDN_LINK_ROOT,"/"));
	$objTpl->assign('pageType',$pageType);
	$objTpl->assign('iBlockId',$blockid);
	$objTpl->assign_by_ref('arrCoupon',$arrCoupon);
	return $objTpl->fetch("smarty_coupon_block.tpl");
}

function getLoginUserInfo()
{
	$arrUser = array();
	$cspuser = trim(get_cookie_var('cspuser'));
	if($cspuser)
	{
		list($uid, $userName) = explode("|", $cspuser);
		$arrUser['uid'] = intval($uid);
		$arrUser['name'] = trim($userName);
	}
	return $arrUser;
}

function adJsStrReplace($sourString, $keywordsArr=array("http://partner.googleadservices.com/gampad/google_service.js", 'GS_googleAddAdSenseService("ca-pub-9981015761401060");', 'GS_googleEnableAllServices();', "GA_googleFetchAds();"), $moveKey = "GA_googleAddSlot(" ){
	
	$roolbackHtml = $sourString;
	
	$fatchPos = 0;
	try{
		foreach ($keywordsArr as $keywords){
			$pos = stripos($sourString, $keywords);
			if($pos == false){
				return $roolbackHtml;
			}
			if($keywords == "GA_googleFetchAds();"){
				$fatchPos = $pos - 1 ;
			}
			$temp = str_ireplace($keywords,"",$sourString);
			if($temp == false){
				return $roolbackHtml;
			}
			$temp = substr_replace($temp, $keywords, $pos, 0);
			if($temp == false){
				return $roolbackHtml;
			}
			$sourString = $temp;
		
		}
		$GA_googleAddSlotArray = array();
		$pos = stripos($temp, $moveKey);
		$pos = stripos($temp, ");", $pos);
		$pos++;
		$posRestart = $pos;
		$i = 0;
		while(true){
			$posStart = stripos($temp,$moveKey, $posRestart);
			if($posStart == false){
				break;
			}
			$posEnd = searchStrFun($temp, $moveKey, $posStart, ");");
			$posRestart = $posStart;
			$GA_googleAddSlotArray[$i] = substr($temp, $posStart, $posEnd - $posStart + 1);
			$temp = substr_replace($temp, " ", $posStart, $posEnd + 2 - $posStart);
			if($temp == false){
				return  $roolbackHtml;
			}
			$i++;
		}
		$fatchPos = stripos($temp, "GA_googleFetchAds();") -1;
		foreach ($GA_googleAddSlotArray as $value){
			$temp = substr_replace($temp, $value, $fatchPos, 0);
			if($temp == false){
				return  $roolbackHtml;
			}
			$fatchPos = $fatchPos + strlen($value);
		}
	}catch(Exception $e){
		return $roolbackHtml;
	}
	return trimNullScript($temp) ;
}

function searchStrFun($sourceStr, $keyword, $startPos, $endWord){
	$pos = stripos($sourceStr, $keyword, $startPos);
	if($pos == false){
		return false;
	}
	$pos = stripos($sourceStr, $endWord, $pos);
	if($pos == false){
		return false;
	}
	return $pos + strlen($endWord);
}


function trimNullScript($sourceString ){
	$scriptStringStart = "<script type='text/javascript'>";
	$scriptStringEnd = "</script>";
	$res = preg_replace("/<script type='text\/javascript'>\s+<\/script>/", " ",$sourceString);
	if($res === false){
		return $sourceString;
	}
	$res = preg_replace("/<script type='text\/javascript' src=''>\s+<\/script>/", " ",$res);
	if($res === false){
		return $sourceString;
	}
	return $res;
}

/*add by andy: parse url type -----start*/
function parseUrlType($url = '', $site = '') {
	$data = array('site' => '', 'info' => array());
	$allSite = array('csus', 'csca', 'csuk', 'csde', 'csie', 'csnz', 'csau');
	
	if (empty($url)) return $data;
	
	$url = trim($url);
	$urlInfo = parse_url($url);
	if (!isset($urlInfo['path']) || empty($urlInfo['path']) || ($urlInfo['path'] == '/')) return $data;
	$site = strtolower($site);
	
	if (!in_array($site, $allSite)) {
		if (!isset($urlInfo['host']) || empty($urlInfo['host']) || (!empty($site) && !in_array($site, $allSite))) return $data;
		$site = judgeSiteByHost($urlInfo['host']);
		if (!in_array($site, $allSite)) return $data;
	}
	
	$data['site'] = $site;
	if ($data['info'] = isMerchantByUrl($urlInfo['path'], $site));
    elseif ($data['info'] = isTagByUrl($urlInfo['path'], $site));
	elseif ($data['info'] = isCategoryByUrl($urlInfo['path'], $site));
	
	return $data;
}

function judgeSiteByHost($host = '') {
	$site = '';
	if (empty($host)) return $site;
	
	preg_match('/www.([a-zA-Z0-9]+)\.(.+)/is', trim($host), $matches);
	
	if (empty($matches)) return $site;
	
	if ($matches[1] == 'irelandvouchercodes') {
		$site = 'csie';
	} elseif ($matches[2] == 'com') {
		$site = 'csus';
	} elseif ($matches[2] == 'ca') {
		$site = 'csca';
	} elseif ($matches[2] == 'de') {
		$site = 'csde';
	} elseif ($matches[2] == 'com.au') {
		$site = 'csau';
	} elseif ($matches[2] == 'co.uk') {
		$site = 'csuk';
	} elseif ($matches[2] == 'co.nz') {
		$site = 'csnz';
	}
	
	return $site;
}

function isMerchantByUrl($urlpath = '', $site = '') {
	$data = array();
	if (empty($urlpath) || empty($site)) return $data;
	
	switch ($site) {
		case 'csca':
			if (preg_match('/m([0-9]+)-(.*)-online-coupons-codes\.html(?:.*)/is', $urlpath, $matches)) {
				$data['type'] = 'merchant';
				$data['id'] = $matches[1];
			}
			break;
		case 'csde':
			if (preg_match('/Kaufmann-(.*)-Gutscheine-Angebote-([0-9]+)\.html(?:.*)/is', $urlpath, $matches)) {
				$data['type'] = 'merchant';
				$data['id'] = $matches[2];
			}
			break;
		default:
			if (preg_match('/merchant-(.*)-(coupons|vouchers)-deals-([0-9]+)\.html(?:.*)/is', $urlpath, $matches)) {
				$data['type'] = 'merchant';
				$data['id'] = $matches[3];
			}
			break;
	}
	
	return $data;
}

function isTagByUrl($urlpath = '', $site = '') {
	$data = array();
	if (empty($urlpath) || empty($site)) return $data;
	
	switch ($site) {
		case 'csde':
			if (preg_match('/etikettierte-Online-Gutscheine-mit-(.*)-([0-9]+)\.html(?:.*)/is', $urlpath, $matches)) {
				$data['type'] = 'tag';
				$data['id'] = $matches[2];
			}
			break;
		default:
			if (preg_match('/online-(coupons|vouchers)-tagged-with-(.*)-([0-9]+)\.html(?:.*)/is', $urlpath, $matches)) {
				$data['type'] = 'tag';
				$data['id'] = $matches[3];
			}
			break;
	}
	
	return $data;
} 

function isCategoryByUrl($urlpath = '', $site = '') {
	$data = array();
	if (empty($urlpath) || empty($site)) return $data;
	
	switch ($site) {
		case 'csca':
			if (preg_match('/c-(.*)-(coupons|vouchers)-deals-([0-9]+)\.html(?:.*)/is', $urlpath, $matches)) {
				$data['type'] = 'category';
				$data['id'] = $matches[3];
			}
			break;
		case 'csde':
			if (preg_match('/Kategorie-(.*)-Gutscheine-Angebote-([0-9]+)\.html(?:.*)/is', $urlpath, $matches)) {
				$data['type'] = 'category';
				$data['id'] = $matches[2];
			}
			break;
		default:
			if (preg_match('/category-(.*)-(coupons|vouchers)-deals-([0-9]+)\.html(?:.*)/is', $urlpath, $matches)) {
				$data['type'] = 'category';
				$data['id'] = $matches[3];
			}
			break;
	}
	
	return $data;
}

function checkUrl($url, $account = ""){ 
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_HEADER, 1);
	curl_setopt($curl,CURLOPT_NOBODY,true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	if($account != ""){
		curl_setopt($curl, CURLOPT_USERPWD, $account);
	}
	$data = curl_exec($curl);
	$info = curl_getinfo($curl,CURLINFO_HTTP_CODE);
	curl_close($curl);
	return $info; 
} 
/*parse url type -----end*/

?>