<?php
include_once("site.special.admin.func.php");
/*
 * Created on 2007-9-10
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
function getHeader($objTpl)
{
	$objTpl->define(array( 'admin_coupon' => 'admin_coupon.tpl'));
	$objTpl->define_dynamic('header','admin_coupon');
	$objTpl->assign(array('link_path' => LINK_ROOT));
	$objTpl->parse('OUT', 'header');
	return $objTpl->TEMPLATE['header']['result'];
}

function getFooter($objTpl)
{
	$objTpl->define(array( 'admin_coupon' => 'admin_coupon.tpl'));
	$objTpl->define_dynamic('footer','admin_coupon');
	$objTpl->parse('OUT', 'footer');
	return $objTpl->TEMPLATE['footer']['result'];
}

function getMerchantSelOpt($objMerchant, $merId=0, $autoSubmit=true)
{
	$arrAll404Merchant = $objMerchant->getAll404MerchantId();	
	$autoSubmitStr = $autoSubmit ? "onChange='this.form.submit()'" : '';
	$res = "<select name='merchant' id='merchant' $autoSubmitStr>\n";
	$arr = $objMerchant->getMerchantListQuickByLimitStr("", "", "Name ASC");
	$merId = $merId ?  $merId : intval(get_get_var('merchant'));
	array_push($arr, array('id'=>0, 'name'=>'All-Merchants'));
	foreach($arr as $v)
	{
		$id = $v['id'];
		$name = $v['name'];
		$tmpStr = "";
		if(isset($arrAll404Merchant[$id]))
		{
			$tmpStr = " - merpageremoved";
		}
		
		$LinkSource = $v['LinkSource'] ? "- ".$v['LinkSource'] : '';
		
		$selectedStr = ($id == $merId) ? "selected" : '';
		$res .= "<option value='$id' $selectedStr>$name - $id $LinkSource $tmpStr</option>\n";
	}
	$res .= "</select>";
	return $res;
}

function getMerchantBundleSelOpt($objMerchant, $merId=0, $autoSubmit=true)
{
	$autoSubmitStr = $autoSubmit ? "onChange='this.form.submit()'" : '';
	$res = "<select name='merchantbundle' id='merchantbundle' $autoSubmitStr>\n";
	
	//get all merchant bundle
	$sql = " SELECT DISTINCT BundleName FROM `normalmerchant_bundle` ORDER BY BundleName ASC ";
	$qry = $objMerchant->objMysql->query($sql);
	
	if ($merId > 0){
		//get single merchant bundle related for merId
		$sqlSingle = " SELECT * FROM `normalmerchant_bundle` WHERE MerchantID = $merId LIMIT 1 ";
		$qrySingle = $objMerchant->objMysql->query($sqlSingle);
		
		$arrSingle = mysql_fetch_array($qrySingle);
		$strSingleBundleName = $arrSingle['BundleName'];

		unset($arrSingle);
		mysql_free_result($qrySingle);
		unset($sqlSingle);
	}
	else{
		$strSingleBundleName = '';
	}

	while($arr = mysql_fetch_array($qry)){
		$selectedStr = ($arr['BundleName'] == $strSingleBundleName) ? "selected" : '';
		$res .= "<option value='".$arr['BundleName']."' $selectedStr>".$arr['BundleName']."</option>\n";

		unset($arr);
	}

	mysql_free_result($qry);
	unset($sql);

	$res .= "</select>";
	return $res;
}

function getActiveSelOpt($isActive=1)
{
	$arr = array('1'=>'active', '0'=>'inactive');
	$res = "<select name='isactive' id='isactive'>\n";
	foreach($arr as $k=>$v)
	{
		$selectedStr = ($k == $isActive) ? "selected" : '';
		$res .= "<option value='$k' $selectedStr>$v</option>\n";
	}
	$res .= "</select>";
	return $res;
}




function getOtherSelOpt($name, $isActive=0)
{
	$arr = array('1'=>'YES', '0'=>'NO');
	$res = "<select name='$name' id='$name'>\n";
	foreach($arr as $k=>$v)
	{
		$selectedStr = ($k == $isActive) ? "selected" : '';
		$res .= "<option value='$k' $selectedStr>$v</option>\n";
	}
	$res .= "</select>";
	return $res;
}


function getTypeSelOpt($type=1, array $no_deed=NULL)
{
	$arr = getTypeById(-1);
	if(!empty($no_deed)){
		foreach($no_deed as $key => $val){
			if(!empty($val)){
				unset($arr[$val]);
			}
		}
	}
	$res = "<select name='type' id='type'>\n";
	foreach($arr as $k=>$v)
	{
		$selectedStr = ($k == $type) ? "selected" : '';
		$res .= "<option value='$k' $selectedStr>$v</option>\n";
	}
	$res .= "</select>";
	return $res;
}
function getTypeById($id)
{
	$arr = array('1'=>'Coupon', '5'=>'Exclusive Coupon', '2'=>'Printable coupon', '3'=>'Deal', '4'=>'Product Deal', '6' => 'Premier');
	
	if($id == -1)
	{
		return $arr;
	}
	if(isset($arr[$id]))
	{
		return $arr[$id];
	}
	return '';
}

function getCategorySelOpt($objCategory, $cateId=0)
{
	$res = "<select name='category' id='category'>\n";
	$arr = $objCategory->getCategoryListByLimitStr("", "", "Navigation ASC");
	$cateId = $cateId ?  $cateId : intval(get_get_var('category'));
	array_push($arr, array('id'=>0, 'navigation'=>'No-Category'));
	foreach($arr as $v)
	{
		$id = $v['id'];
		$name = $v['name'];
		$navigation = $v['navigation'];
		$selectedStr = ($id == $cateId) ? "selected" : '';
		$res .= "<option value='$id' $selectedStr>$navigation</option>\n";
	}
	$res .= "</select>";
	return $res;
}

function getExpirationSelOpt()
{
	$res = "<select name='expiretype'  onChange='this.form.submit()'>\n";
	$arr = array(1 => 'all coupons',
				2 => 'expired coupons',
				3 => 'valid coupons');
	$typeId = intval(get_get_var('expiretype'));
	if($typeId == 0)
	{
		$typeId = 3; //by default, just show the valid coupon
	}
	foreach($arr as $k=>$v)
	{
		$selectedStr = ($k == $typeId) ? "selected" : '';
		$res .= "<option value='$k' $selectedStr>$v</option>\n";
	}
	$res .= "</select>";
	return $res;
}

function getPPCSelOpt($type = '')
{
	$res = "<select name='ppc'>\n";
	$arr = array(
				'unknown' => 'Unknown',
				'yes' => 'Allowed',
				'no' => 'Not Allowed',
				'needconfirm' => 'Need Confirm',
				);
	if($type == '')
	{
		$type = 'yes'; //by default, just show the valid coupon
	}
	foreach($arr as $k=>$v)
	{
		$selectedStr = ($k == $type) ? "selected" : '';
		$res .= "<option value='$k' $selectedStr>$v</option>\n";
	}
	$res .= "</select>";
	return $res;
}



function getSortSelOpt()
{
	global $g_arrSort;
	$res = "<select name='sorttype' onChange='this.form.submit()'>\n";
	$typeId = intval(get_get_var('sorttype'));
	foreach($g_arrSort as $k=>$v)
	{
		$selectedStr = ($k == $typeId) ? "selected" : '';
		$res .= "<option value='$k' $selectedStr>$v</option>\n";
	}
	$res .= "</select>";
	return $res;
}

function uploadPDF($fileOptName, $imagePath="couponImage/", $needZoom=false, $SITE_ROOT)
{
	if(!isset($_FILES[$fileOptName]) || $_FILES[$fileOptName]['error'] == 4) return ''; //no file uploaded
	
	if(trim($_FILES[$fileOptName]['name']) && $_FILES[$fileOptName]['error'] == 0)
	{
		$allowImgType = array('.gif','.jpg','.jpeg','.png', '.pdf');
		$tmpFile = $_FILES[$fileOptName]['tmp_name'];
		$postfix = strtolower(strrchr($_FILES[$fileOptName]['name'], "."));
		$filename_prefix = time();
		if($imagePath == 'couponImage/')
		{
			$sub_dir_name = date("Ym");
			$sub_dir_full = $SITE_ROOT . $imagePath . $sub_dir_name;
			check_create_dir($sub_dir_full);
			$dstFile = $imagePath . $sub_dir_name . "/" . $filename_prefix . $postfix;
		}
		else
		{
			$dstFile = $imagePath . $filename_prefix . $postfix;
		}
		
		$dstFilePullPath = $SITE_ROOT . $dstFile;
		if(in_array($postfix, $allowImgType) && move_uploaded_file($tmpFile, $dstFilePullPath))
		{				
			chmod($dstFilePullPath, 0775);
			return "/".$dstFile;
		}
	}
	return false;
}

function check_create_dir($dir)
{
	if(!is_dir($dir))
	{
		if(!mkdir($dir)) die("die: mkdir $dir failed\n");
		@chmod($dir,0775);
	}
}

function uploadImage($fileOptName, $imagePath="couponImage/", $needZoom=false, $SITE_ROOT,  $type = "coupon")
{
	if(!isset($_FILES[$fileOptName]) || $_FILES[$fileOptName]['error'] == 4) return ''; //no file uploaded

	$res = false; 
	global $site;
	$couponImageInit = "data/rawCouponImage/$site/";
	if(trim($_FILES[$fileOptName]['name']) && $_FILES[$fileOptName]['error'] == 0)
	{
		
		$allowImgType = array('.gif','.jpg','.jpeg','.png', '.bmp');
		if($imagePath == 'merImage/') $allowImgType[] = '.svg';
		$tmpFile = $_FILES[$fileOptName]['tmp_name'];
		$postfix = strtolower(strrchr($_FILES[$fileOptName]['name'], "."));
		$filename_prefix = time().rand(10,99);
		$dstFileSource = "";
		if($imagePath == 'couponImage/')
		{
			$sub_dir_name = date("Ym");
			$sub_dir_full = $SITE_ROOT . $imagePath . $sub_dir_name;
			check_create_dir($sub_dir_full);
			$dstFile = $imagePath . $sub_dir_name . "/" . $filename_prefix . $postfix;
			
			$sub_dir_full_init = INCLUDE_ROOT . $couponImageInit . $sub_dir_name;
			check_create_dir($sub_dir_full_init);
			$dstFileInit = $couponImageInit . $sub_dir_name . "/" . $filename_prefix . $postfix;
		}
		else
		{
			$dstFile = $imagePath . $filename_prefix . $postfix;
		}

		$dstFilePullPath = $SITE_ROOT . $dstFile;
		$dstFilePullPathInit = INCLUDE_ROOT . $dstFileInit;
		if(in_array($postfix, $allowImgType) && move_uploaded_file($tmpFile, $dstFilePullPath))
		{
			/*
			 * zoom the image file to 90*90
			 */
			if($imagePath == 'couponImage/'){
				copy($dstFilePullPath, $dstFilePullPathInit);
			}
			if($needZoom)
			{
				$oImageResize = new Image();
				if($type == "coupon"){
					$oImageResize->imageZoom($dstFilePullPath, $SITE_ROOT."image/tpl_90_90.jpg", $dstFilePullPath);
				}else if($type == "deal"){
					$oImageResize->imageZoom($dstFilePullPath, $SITE_ROOT."image/tpl_120_120.jpg", $dstFilePullPath);
				}else if ($type == "merchant"){
					//svg img
					if($postfix=='.svg'){
						copy($dstFilePullPath, $SITE_ROOT . 'mimg/merimg/' . $filename_prefix . $postfix);
						$im = new Imagick();
						$im->readImage($dstFilePullPath);
						$im->setImageFormat("png24");
						$dstFilePullPath = str_replace(".svg",".png",$dstFilePullPath);
						$dstFile = str_replace(".svg",".png",$dstFile);
						$postfix = '.png';
						$im->writeImage($dstFilePullPath);
					}
					uploadMerImage($dstFilePullPath,$filename_prefix,$SITE_ROOT,300);
					uploadMerImage($dstFilePullPath,$filename_prefix,$SITE_ROOT,150);
					$oImageResize->imageZoom($dstFilePullPath, $SITE_ROOT."image/tpl_200_50.jpg", $dstFilePullPath);
				}else if($type == "mobilemerchant"){
					$oImageResize->imageZoom($dstFilePullPath, $SITE_ROOT."image/tpl_150_150.jpg", $dstFilePullPath);
				}
			}
			/*
			* copy merchant logo and resize it to 120*30 to resizedMerImage/
			*/
			if($imagePath == 'merImage/')
			{
				$resizedMerImgFile = 'resizedMerImage/'.$filename_prefix.$postfix;
				$resizedMerImgFile = $SITE_ROOT.$resizedMerImgFile;
				if (!isset($oImageResize)) {
					$oImageResize = new Image();
				}
				$oImageResize->imageZoom($dstFilePullPath, $SITE_ROOT."image/tpl_120_30.jpg", $resizedMerImgFile);
				chmod($resizedMerImgFile, 0775);
				imgCompress($postfix,$resizedMerImgFile);
			}
			chmod($dstFilePullPath, 0775);
			imgCompress($postfix,$dstFilePullPath);
			
			return "/".$dstFile;
		}
	}
	return $res;
}

function uploadMerImage($srcImg,$filename,$site,$size){
	$postfix = strtolower(strrchr($srcImg, "."));
	$imagePath = 'mimg/merimg/';
	if($size==300) $prefix = 'b';
	if($size==150) $prefix = 's';
	$dstFilePullPath = $site . $imagePath . $prefix . '_' . $filename . $postfix;
	list($width, $height, $type) = getimagesize($srcImg);
	if($width<$size && $height < $size){
		copy($srcImg, $dstFilePullPath);
		imgCompress($postfix,$dstFilePullPath);
		return true;
	}
	$val = $width-$height;
	if($val==0){
		$newWidth = $newHeight = $size;
	}
	if($val>0){
		$newWidth = $size;
		$newHeight = $size/$width*$height;
	}
	if($val<0){
		$newHeight = $size;
		$newWidth = $size/$height*$width;
	}
	switch ($type) {
		case 1:
			$source = imagecreatefromgif($srcImg);
			$thumb = imagecreatetruecolor($newWidth, $newHeight);
			$otsc=imagecolortransparent($source);
			if($otsc >=0 && $otst < imagecolorstotal($source)){
			$tran=imagecolorsforindex($source, $otsc);
			$newt=imagecolorallocate($thumb, $tran["red"], $tran["green"], $tran["blue"]);
			imagefill($thumb, 0, 0, $newt);
			imagecolortransparent($thumb, $newt);
			}
			imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
			@imagegif($thumb, $dstFilePullPath);
			break;
		case 2:
			$source = imagecreatefromjpeg($srcImg);
			$thumb = imagecreatetruecolor($newWidth, $newHeight);
			imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
			@imagejpeg($thumb, $dstFilePullPath);
			imgCompress('jpg',$dstFilePullPath);
			break;
		case 3:
			$source = imagecreatefrompng($srcImg);
			imagesavealpha($source, true);
			$thumb = imagecreatetruecolor($newWidth, $newHeight);
			imagealphablending($thumb,false);
			imagesavealpha($thumb,true);
			imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
			@imagepng($thumb, $dstFilePullPath);
			imgCompress('png',$dstFilePullPath);
			break;
		default:
			return false;
			break;
	}
}
function imgCompress($type,$srcImg){
	/***********img soft*************
	***jpg:http://freecode.com/projects/jpegoptim/ 
	***png:http://optipng.sourceforge.net/
	*/
	$type = str_replace('.','',$type);
	if(!in_array($type,array('jpg','png'))) return false;
	$imgloc = '/data/imgloc';
	$pathArr = explode("/",$srcImg);
	for($i=0;$i<count($pathArr)-1;$i++){
		if($pathArr[$i]!=''){
			$imgloc .= '/'.$pathArr[$i];
			if(!is_dir($imgloc)){	
				mkdir($imgloc, 0775);
			}
		}
	}
	@copy($srcImg,$imgloc.'/'.end($pathArr));
	if($type=='jpg'){
		@exec('/usr/bin/jpegoptim '.$srcImg);
	}
	if($type=='png'){
		@exec('/usr/bin/optipng '.$srcImg);
	}
}
function getMatchTypeSelOpt()
{
	$type = trim(get_post_var('matchtype'));
	$res = "<select name='matchtype'>\n";
	$arr = array(
				'broad' => 'broad',
				'broadm' => 'broadm',
				'phrase' => 'phrase',
				'exact' => 'exact',
				'negative' => 'negative',
				);
	if($type == '')
	{
		$type = 'broad';
	}
	foreach($arr as $k=>$v)
	{
		$selectedStr = ($k == $type) ? "selected" : '';
		$res .= "<option value='$k' $selectedStr>$v</option>\n";
	}
	$res .= "</select>";
	return $res;
}


function getSEMTypeSelOpt()
{
	$type = trim(get_post_var('semtype'));
	$res = "<select name='semtype'>\n";
	$arr = array(
				'google' => 'google',
				'msn' => 'msn',
				'yahoo' => 'yahoo',
				);
	if($type == '')
	{
		$type = 'google';
	}
	foreach($arr as $k=>$v)
	{
		$selectedStr = ($k == $type) ? "selected" : '';
		$res .= "<option value='$k' $selectedStr>$v</option>\n";
	}
	$res .= "</select>";
	return $res;
}

//if mutiple merchant, category tag id, please use comma as separator
function removeCache($mid, $cid, $tid, $couponid=0, $site_url='', $site_id="")
{
	// remove the code, allow system auto refresh the cache, every 10 mins
	//return;

	//$objMysql = new Mysql();
	global $objMysql, $g_SiteUrl, $site;
	if(!$objMysql){
		die("mysql error.".__FILE__);
	}
	$arrClearCacheCmd = array();
	$arrToRefresh = array();
	if($mid)
	{
		$arrNameList = array();
		$sql = "select ID, Name, UrlName from normalmerchant where ID in (".$mid.")";
		$qryID = $objMysql->query($sql);
		while($arrTmp = $objMysql->getRow($qryID))
		{
			$merchantUrlInfo = array("name"=>trim($arrTmp['Name']), "urlname" =>trim($arrTmp['UrlName']));
			$arrNameList[intval($arrTmp['ID'])] = trim($arrTmp['Name']);
			//$arrToRefresh[] = get_rewrited_url('merchant', $merchantUrlInfo, $arrTmp['ID'], $site_url);
			$arrToRefresh[] = $g_SiteUrl[$site]["front"]."/front/merchant.php?mid={$arrTmp['ID']}";
		}
		$objMysql->freeResult($qryID);
		
		$arrTmp = explode(",", $mid);
		foreach($arrTmp as $id)
		{
			$id = intval($id);
			if(!$id) continue;
			
			$cacheFileName = INCLUDE_ROOT."data/cache_m".substr($id, 0, 1)."/cache_m{$id}.dat";
			expCacheFile($cacheFileName, $site_id);
		}
	}
	if($cid)
	{
		$arrNameList = array();
		$sql = "select ID, Name, UrlName from normalcategory where ID in (".$cid.")";
		$qryID = $objMysql->query($sql);
		while($arrTmp = $objMysql->getRow($qryID))
		{
			$arrNameList[intval($arrTmp['ID'])] = trim($arrTmp['Name']);
			$catUrlInfo = array("name" => trim($arrTmp['Name']), "urlname" => trim($arrTmp['UrlName']));
			$arrToRefresh[] = get_rewrited_url('category', $catUrlInfo, $arrTmp['ID'], $site_url);
		}
		$objMysql->freeResult($qryID);
		
		$arrTmp = explode(",", $cid);
		foreach($arrTmp as $id)
		{
			$id = intval($id);
			if(!$id) continue;
			
			//ignore the pagination, system would auto refresh it in two days
			$cacheFileName = INCLUDE_ROOT."data/cache_c".substr($id, 0, 1)."/cache_c{$id}__0.dat";
			expCacheFile($cacheFileName, $site_id);

		}
	}
	if($tid)
	{
		$arrNameList = array();
		$sql = "select ID, TagName, UrlName from tag where ID in (".$tid.")";
		$qryID = $objMysql->query($sql);
		while($arrTmp = $objMysql->getRow($qryID))
		{
			$arrNameList[intval($arrTmp['ID'])] = trim($arrTmp['TagName']);
			$tagUrlInfo = array("name" => trim($arrTmp['TagName']), "urlname" => trim($arrTmp['UrlName']));
			$arrToRefresh[] = get_rewrited_url('tag', $tagUrlInfo, $arrTmp['ID'], $site_url);
		}
		$objMysql->freeResult($qryID);
		
		$arrTmp = explode(",", $tid);
		foreach($arrTmp as $id)
		{
			$id = intval($id);
			if(!$id) continue;
			
			//ignore the pagination, system would auto refresh it in two days
			$cacheFileName = INCLUDE_ROOT."data/cache_t".substr($id, 0, 1)."/cache_t{$id}_0.dat";
			expCacheFile($cacheFileName, $site_id);
		}
	}
	
	if(!empty($couponid) && $couponid > 0)
	{
		//update CouponID
//		$arrAllTag = array();
		$arrTagInsert = array();
		if($tid)
		{
			$sql = "select ID,TagName from tag where ID in (".$tid.")";
			$qryID = $objMysql->query($sql);
			while($row = $objMysql->getRow($qryID))
			{
//				$arrAllTag[$row["ID"]] = $row["TagName"];
				$arrTagInsert[] = "($couponid, " . $row["ID"] . ", '" . addslashes($row["TagName"]) . "')";
			}
		}
		
		$sql = "DELETE FROM r_coupontag WHERE CouponID = '$couponid'";
		$objMysql->query($sql);
		
		if(sizeof($arrTagInsert))
		{
			$sql = "INSERT IGNORE INTO r_coupontag (CouponID, TagID, TagName) VALUES " . implode(",",$arrTagInsert);
			$objMysql->query($sql);
		}
		removeCacheByCouponId($couponid, $objMysql, $site_url);
	}
	
	if(sizeof($arrToRefresh))
	{
		$arrInsert = array();
		foreach($arrToRefresh as $_url)
		{
			$arrInsert[] = "(null, '" . addslashes($_url . "&forcerefresh=1") . "', 1, now())";
		}
		
		$sql = "INSERT INTO cache_torefresh (Id, Url, STATUS, UpdateTime) VALUES " . implode(",",$arrInsert);
		$objMysql->query($sql);
	}
}

function removeCacheByCouponId($couponID, $objMysql, $front = "")
{
//	include_once(INCLUDE_ROOT . "func/rewrite.func.php");
	//$objTag = new Tag($objMysql);
	$objCoupon = new NormalCoupon($objMysql);
	//$objMerchant = new NormalMerchant($objMysql);
	//$objCate = new NormalCategory($objMysql);
	$theCoupon = $objCoupon->getCouponById($couponID);
	
	$urlArr = array();
	/*
	//by ike
	$urlArr[] = get_rewrited_url('homepage', "", 0, $front)."&forcerefresh=1";
	if(isset($theCoupon["IsExclusive"]) && $theCoupon["IsExclusive"]=='YES'){
		$urlArr[] = get_rewrited_url('exclusive', "", 0, $front)."&forcerefresh=1&forcerefresh_pagesort=1";
		$urlArr[] = get_rewrited_url('exclusive', "", 0, $front)."&forcerefresh=1&forcerefresh_pagesort=2";
	}
	if(isset($theCoupon["type"]) && $theCoupon["type"]!='6'){
		$urlArr[] = get_rewrited_url('coupon', "", 0, $front)."&forcerefresh=1&forcerefresh_pagesort=1";
		$urlArr[] = get_rewrited_url('coupon', "", 0, $front)."&forcerefresh=1&forcerefresh_pagesort=2";
	}
	if(isset($theCoupon["type"]) && $theCoupon["type"]=='6'){
		$urlArr[] = get_rewrited_url('deal', "", 0, $front)."&forcerefresh=1&forcerefresh_pagesort=1";
		$urlArr[] = get_rewrited_url('deal', "", 0, $front)."&forcerefresh=1&forcerefresh_pagesort=2";
		$urlArr[] = get_rewrited_url('deal', "", 0, $front)."&forcerefresh=1&forcerefresh_pagesort=3";
		$urlArr[] = get_rewrited_url('deal', "", 0, $front)."&forcerefresh=1&forcerefresh_pagesort=4";
	}
	*/
	if(isset($theCoupon["type"]) && $theCoupon["type"]=='2'){
		$urlArr[] = get_rewrited_url('printable', "", 0, $front)."&forcerefresh=1";
	}

	foreach($urlArr as $url){
		$sql = "INSERT INTO cache_torefresh (Url, STATUS, UpdateTime) VALUES ('". addslashes($url) ."',1,now())";
        $objMysql->query($sql);
	}
}

function refreshPlacementCache($objMysql, $mid, $cid, $tid, $couponid=0, $site_url='', $site_id="", $homepage = "", $keyword = "")
{
	if(!$objMysql){
		die("mysql error.".__FILE__);
	}
	$arrClearCacheCmd = array();
	$arrToRefresh = array();
	
	/*if($homepage == "homepage"){
		$arrToRefresh[] = trim($site_url,"/") . "/";
	}
	if($homepage == "search"){
		$arrToRefresh[] =  get_rewrited_url('search', $keyword, "", $site_url);;
	}
	
	if($homepage == "exclusivepage"){
		$arrToRefresh[] =  get_rewrited_url('exclusive', $keyword, "", $site_url);;
	}
	if($homepage == "dealpage"){
		$arrToRefresh[] =  get_rewrited_url('deal', "", "", $site_url);;
	}
	if($homepage == "couponpage"){
		$arrToRefresh[] =  get_rewrited_url('coupon', $keyword, "", $site_url);;
	}
	*/
	
	if($mid)
	{
		$arrNameList = array();
		$sql = "select ID, Name, UrlName from normalmerchant where ID in (".$mid.")";
		$qryID = $objMysql->query($sql);
		while($arrTmp = $objMysql->getRow($qryID))
		{
			$merchantUrlInfo = array("name"=>trim($arrTmp['Name']), "urlname" =>trim($arrTmp['UrlName']));
			$arrNameList[intval($arrTmp['ID'])] = trim($arrTmp['Name']);
			$arrToRefresh[] = get_rewrited_url('merchant', $merchantUrlInfo, $arrTmp['ID'], $site_url);
		}
		$objMysql->freeResult($qryID);
		
	}
	if($cid)
	{
		$arrNameList = array();
		$sql = "select ID, Name, UrlName from normalcategory where ID in (".$cid.")";
		$qryID = $objMysql->query($sql);
		while($arrTmp = $objMysql->getRow($qryID))
		{
			$arrNameList[intval($arrTmp['ID'])] = trim($arrTmp['Name']);
			$catUrlInfo = array("name" => trim($arrTmp['Name']), "urlname" => trim($arrTmp['UrlName']));
			$arrToRefresh[] = get_rewrited_url('category',$catUrlInfo, $arrTmp['ID'], $site_url);
		}
		$objMysql->freeResult($qryID);
		
	}
	if($tid)
	{
		$arrNameList = array();
		$sql = "select ID, TagName, UrlName from tag where ID in (".$tid.")";
		$qryID = $objMysql->query($sql);
		while($arrTmp = $objMysql->getRow($qryID))
		{
			$arrNameList[intval($arrTmp['ID'])] = trim($arrTmp['TagName']);
			$tagUrlInfo = array("name" => trim($arrTmp['TagName']), "urlname" => trim($arrTmp['UrlName']));
			$arrToRefresh[] = get_rewrited_url('tag', $tagUrlInfo, $arrTmp['ID'], $site_url);
		}
		$objMysql->freeResult($qryID);
		
	}

	if(sizeof($arrToRefresh))
	{
		$arrInsert = array();
		foreach($arrToRefresh as $_url)
		{
			$arrInsert[] = "(null, '" . addslashes($_url . "&forcerefresh=1") . "', 1, now())";
		}
		
		$sql = "INSERT INTO cache_torefresh (Id, Url, STATUS, UpdateTime) VALUES " . implode(",",$arrInsert);
		$objMysql->query($sql);
	}
}

function getSelOpt($arrOpt, $name, $curValue, $otherHtml="")
{
	$res = "<select name='$name' id='$name' $otherHtml>\n";
	foreach($arrOpt as $k=>$v)
	{
		$selectedStr = (strtolower($k) == strtolower($curValue)) ? "selected" : '';
		$res .= "<option value='$k' $selectedStr>$v</option>\n";
	}
	$res .= "</select>";
	return $res;
}

function getSiteList($objMysql)
{
	$arrRtn[0] = '--select a site--';
	$sql = "select distinct Site as s from aff_srckw_daily where Site <>'' order by Site asc ";
	$qryId = $objMysql->query($sql);
	while($arrTmp = $objMysql->getRow($qryId))
	{
		$arrRtn[trim($arrTmp['s'])] = trim($arrTmp['s']);
	}
	$objMysql->freeResult($qryId);
	return $arrRtn;
}

function getAcctList($objMysql, $site)
{
	$arrRtn[0] = '--select an Acct--';
	$site = addslashes(trim($site));
	$sql = "select distinct Acct as a from aff_srckw_daily where Site = '$site' and Acct <> '' order by Acct asc ";
	$qryId = $objMysql->query($sql);
	while($arrTmp = $objMysql->getRow($qryId))
	{
		$arrRtn[trim($arrTmp['a'])] = trim($arrTmp['a']);
	}
	$objMysql->freeResult($qryId);
	return $arrRtn;
}

function getCampList($objMysql, $site, $acct='')
{
	$arrRtn[0] = '--select a Camp--';
	$site = addslashes(trim($site));
	$sql = "select distinct Camp as c from aff_srckw_daily where Site = '$site' and Camp <> ''";
	if($acct)
		$sql .= " AND Acct = '".addslashes($acct)."'";
	$qryId = $objMysql->query($sql." order by Camp asc");
	while($arrTmp = $objMysql->getRow($qryId))
	{
		$arrRtn[trim($arrTmp['c'])] = trim($arrTmp['c']);
	}
	$objMysql->freeResult($qryId);
	return $arrRtn;
}

function getAdgroupList($objMysql, $site, $acct='', $camp='')
{
	$arrRtn[0] = '--select an Adgroup--';
	$site = addslashes(trim($site));
	$sql = "select distinct Adgroup as g from aff_srckw_daily where Site = '$site' and Adgroup <> ''";
	if($acct)
		$sql .= " AND Acct = '".addslashes($acct)."'";
	if($camp)
		$sql .= " AND Camp = '".addslashes($camp)."'";
	$qryId = $objMysql->query($sql." order by Adgroup asc");
	while($arrTmp = $objMysql->getRow($qryId))
	{
		$arrRtn[trim($arrTmp['g'])] = trim($arrTmp['g']);
	}
	$objMysql->freeResult($qryId);
	return $arrRtn;
}

function expCacheFile($cachefile, $site_id="")
{
/*
	//by ike 20120309,temporarily
	$cachefilename = basename($cachefile);
	$oCache = new Cache($cachefilename, "./data/", "", $site_id);
	$oCache->expireCache();
*/
}

function expCacheFile_bk($cachefile)
{
	if(file_exists($cachefile))
	{
		//since the cron.refreshcache.php force refresh the file expires in two days.
		$fakeExpTime = date("Y-m-d H:i:s", strtotime("-2 day")); 

		$content = file_get_contents($cachefile);
		$pattern = "/<!-- last mod time:.*? -->/i";
		$newModTime = "<!-- last mod time:{$fakeExpTime} -->";
		$content = preg_replace($pattern, $newModTime, $content);
		file_put_contents($cachefile, $content);

		global $PHP_AUTH_USER;
		$editorname = isset($PHP_AUTH_USER) ? $PHP_AUTH_USER : $_SERVER["REMOTE_USER"];
		$logStr = date("Y-m-d H:i:s")."\t{$editorname}\t".$cachefile."\n";
		error_log($logStr, 3, INCLUDE_ROOT."data/cachermlog.txt");
	}
}

function getAffUrlByLinkShareApi($merid_in_aff, $dest_url, $merchantid = "", $objMerchant = "", $cron = "")
{
	$token = "fb80d7cf065af3909fdb367a99a0893b2ed058724315c0a9b4b282ca82ad18bb";
	
	if(strpos($dest_url,"#") !== false)
	{
		$request_url = "http://getdeeplink.linksynergy.com/createcustomlink.shtml?token=".$token."&mid=".$merid_in_aff."&murl=".urlencode($dest_url);
	}
	else
	{
		//ike: default, we dont urlencode dest_url
		$request_url = "http://getdeeplink.linksynergy.com/createcustomlink.shtml?token=".$token."&mid=".$merid_in_aff."&murl=".$dest_url;
	}
	
	
	$data = getContentByCurl($request_url);
	$url = trim($data);
	
	if($cron == "cron"){
		if(strpos($url, "http://")!==0 && strpos(trim($url), "https://")!==0){
			return false;
		}else{
			return $url;
		}
	}
	//http://click.linksynergy.com/fs-bin/click?id=AeuDahFBnDk&subid=0&offerid=246949.1&type=10&tmpid=7818&RD_PARM0=http%253A%252F%252Fwww.marksandspencer.com%252FOffers%252Fb%252F82867031%253Fie%253DUTF8%2526intid%253Dgnav_Offers%2526pf_rd_r%253D1B2MFDYNB8MYADAGK6BD%2526pf_rd_m%253DA2BO0OYVBKIQJM%2526pf_rd_t%253D101%2526pf_rd_i%253D42966030%2526pf_rd_p%253D257503947%2526pf_rd_s%253Dglobal-top-8&RD_PARM1=http%253A%252F%252Fwww.marksandspencer.com%252FOffers%252Fb%252F82867031%253Fie%253DUTF8%2526intid%253Dgnav_Offers%2526pf_rd_r%253D1B2MFDYNB8MYADAGK6BD%2526pf_rd_m%253DA2BO0OYVBKIQJM%2526pf_rd_t%253D101%2526pf_rd_i%253D42966030%2526pf_rd_p%253D257503947%2526pf_rd_s%253Dglobal-top-8t55tterror
	if(strpos(trim($url), "http://")!==0 && strpos(trim($url), "https://")!==0){
		$request_url = "http://www.linkshare.com/share/bookmarklet/linkproxytag2.php?token=".$token."&mid=".$merid_in_aff."&tag=bookmark-1&murl=".urlencode($dest_url);
		$data = getContentByCurl($request_url);
		$url = trim($data);
		
		if(strpos($url, "http://")!==0 && strpos(trim($url), "https://")!==0){
			if($cron != "auto"){
				if($objMerchant != "" && $merchantid != ""){
					$arrMerchant = $objMerchant->getMerchantById($merchantid);
					$affiliate = getAffIdWithUrl($arrMerchant['customurl']);
					
					if($affiliate==2||$affiliate==4){
						$url = $objMerchant->getMerchantUrlwithDeepUrl($merchantid, $dest_url);
						if($url == false){
							return false;
						}
					}else{
						return false;
					}
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
	}
	return $url;
}

function getAffUrlByLinkShareTpl($merid_in_aff, $dest_url, $merchantid = "", $objMerchant = "", $cron = "")
{
	$merid_in_aff = strpos($merid_in_aff, "_") ? substr($merid_in_aff, 0, strpos($merid_in_aff, "_")) : $merid_in_aff;
	$url = "http://click.linksynergy.com/deeplink?id=AeuDahFBnDk&mid=".$merid_in_aff."&murl=".urlencode($dest_url);
	
	if($cron == "cron"){
		if(strpos($url, "http://")!==0 && strpos(trim($url), "https://")!==0){
			return false;
		}else{
			return $url;
		}
	}
	
	if(strpos($url, "http://")!==0 && strpos(trim($url), "https://")!==0){
		if($cron != "auto"){
			if($objMerchant != "" && $merchantid != ""){
				$arrMerchant = $objMerchant->getMerchantById($merchantid);
				$affiliate = getAffIdWithUrl($arrMerchant['customurl']);
				
				if($affiliate == 2 || $affiliate == 4){
					$url = $objMerchant->getMerchantUrlwithDeepUrl($merchantid, $dest_url);
					if($url == false){
						return false;
					}
				}else{
					return false;
				}
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	return $url;
}

function getContentByCurl($url){
	$handle = curl_init();
	curl_setopt($handle, CURLOPT_URL, $url);
	curl_setopt($handle, CURLOPT_HEADER, 0);
	curl_setopt($handle, CURLOPT_NOBODY, 0);
	curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($handle, CURLOPT_AUTOREFERER, 1);
	curl_setopt($handle, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($handle, CURLOPT_USERAGENT, CURL_USER_AGENT);
	curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($handle, CURLOPT_TIMEOUT, 30); 
	curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 2);
	$data = curl_exec($handle);
	curl_close($handle);
	return $data;
}
?>
