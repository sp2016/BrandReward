<?php 
	include_once(dirname(dirname(__FILE__))."/etc/const.php");	

	/*include_once(INCLUDE_ROOT."func/front.func.php");
	include_once(INCLUDE_ROOT."func/rewrite.func.php");
	include_once(INCLUDE_ROOT."func/string.func.php");*/
	include_once(INCLUDE_ROOT."func/gpc.func.php");
	include_once(INCLUDE_ROOT."func/stats.func.php");

	$strDeepUrl = trim(get_get_var('url'));
	$strDeepTpl = trim(get_get_var('tpl'));
	
	$type = trim(get_get_var('type'));
	
	$strDeepUrl = str_replace('&amp;', '&', $strDeepUrl);
	$strDeepTpl = str_replace('&amp;', '&', $strDeepTpl);
	
	if($strDeepUrl && $strDeepTpl){		
		$dstUrl = getDeepUrl($strDeepUrl, $strDeepTpl);	
	}else{		
		echo "no url";
		exit;
	}
	
	if($dstUrl == ""){
		echo "empty url";
		exit;
	}
//http://csusbackend.megainformationtech.com
	$dstUrl = str_replace('&amp;', '&', $dstUrl);
	
/*echo $dstUrl."<hr>";
echo	$dstUrl = "|".getAffUrlWithSID($dstUrl, $g_sessionID, $outgoingId)."|";
exit;*/

	//$dstUrl = "http://www.shareasale.com/r.cfm?b=108389&u=252822&m=15699&afftrack=&urllink=1040return.com%2F&afftrack=s01_22_22__";
	//$dstUrl = "http://www.shareasale.com/r.cfm?b=108389&u=252822&m=15699&afftrack=s01_22_22__&urllink=1040return.com%2F%2F";


	header("Cache-Control: no-cache");	
	Header( "HTTP/1.1 302 Moved Temporarily");
	Header( "Location: https://edm.megainformationtech.com/rd.php?url=".urlencode($dstUrl));
	exit;


	function getDeepUrl($strDeepUrl, $strDeepTpl) {
		//check if have custom deep url template
		//return -1 if have not template yet.
		/*global $g_SiteUrl;
    	foreach ($g_SiteUrl as $v){
			if(stripos($strDeepUrl, $v["front"]) !== false){
				return $strDeepUrl;
			}
		}*/
		
		$result = $strDeepTpl;
		//add by ran 2009-05-19
		//spec for SAS
		if (stripos($result, 'shareasale.com')) {
			$nTmp = stripos($strDeepUrl, 'http://');
			if ($nTmp !== false) {
				$strDeepUrl = substr($strDeepUrl, $nTmp + 7);
			} else {
				$nTmp = stripos($strDeepUrl, urlencode('http://'));
				if ($nTmp !== false) {
				    $strDeepUrl = substr($strDeepUrl, strlen(urlencode('http://')));
				}
	    	}
		}
	    //
	    //changed by jimmy @ 2010-01-18
	    //changed by Pani @2012-03-29
	    //To handle all the custome links which were start with PURE_DEEPURL,DEEPURL,DOUBLE_ENCODE_DEEPURL
	    //Normalize the Query Mark [?|&] in destional url
	    /*$start_w_tpl = (stripos($result, '[PURE_DEEPURL]') === 0)? true : false; 
	    $result = str_replace('[PURE_DEEPURL]', $strDeepUrl, $result);
	    
	    $result = str_replace('[SUBTRACKING]', 0, $result);
	
	    if (stripos($result, '[DEEPURL]') === 0){
	         $result = str_replace('[DEEPURL]', $strDeepUrl, $result);
	         $start_w_tpl = true;
	    }
	    else {
	        $result = str_replace('[DEEPURL]', urlencode($strDeepUrl), $result);
	    }
	
	    if (stripos($result, '[DOUBLE_ENCODE_DEEPURL]') === 0) {
	        $result = str_replace('[DOUBLE_ENCODE_DEEPURL]', $strDeepUrl, $result);
	        $start_w_tpl = true;
	    }
	    else {
	        $result = str_replace('[DOUBLE_ENCODE_DEEPURL]', urlencode(urlencode($strDeepUrl)), $result);
	    }
	
	    $strDeepUrl = preg_replace("/^http(|s):\/\/(.*)\//U", "", $strDeepUrl);
	    $result = str_replace('[URI]', $strDeepUrl, $result);
		if (stripos($result, '[URI]') === 0) {        
	        $start_w_tpl = true;
	    }				
	    
		if (stripos($result, '[ENCODE_URI]') === 0) {
	        $result = str_replace('[ENCODE_URI]', $strDeepUrl, $result);
	        $start_w_tpl = true;
	    }
		else {
	        $result = str_replace('[ENCODE_URI]', urlencode($strDeepUrl), $result);
	    }
	    
		if (stripos($result, '[DOUBLE_ENCODE_URI]') === 0) {
	        $result = str_replace('[DOUBLE_ENCODE_URI]', $strDeepUrl, $result);
	        $start_w_tpl = true;
	    }
		else {
	        $result = str_replace('[DOUBLE_ENCODE_URI]', urlencode(urlencode($strDeepUrl)), $result);
	    }
	    
	   */
		
		$mark_and = '&';
		$mark_que = '?';
		$has_deep_mark = false;
		if (preg_match('/(.*)\[(PURE_DEEPURL|DEEPURL|DOUBLE_ENCODE_DEEPURL|URI|ENCODE_URI|DOUBLE_ENCODE_URI)\](\[\?\|&\])*/', $result, $m)) {
		
		    preg_match('/^http(s)?:\/\/[^\/]+(\/)?(.*)/', $strDeepUrl, $q);
		    $has_deep_mark = $m[3] != ''? true : $has_deep_mark;
		
		    switch ($m[2]) {
		case 'PURE_DEEPURL':
		    $result = str_ireplace('[PURE_DEEPURL]', $strDeepUrl, $result);    
		    break;
		case 'DEEPURL':
		    $result = str_ireplace('[DEEPURL]', ($m[1] == ''? $strDeepUrl: urlencode($strDeepUrl)), $result);
		    if ($m[3] == '[?|&]' && $m[1] != '') {
				$mark_and = urlencode($mark_and);
				$mark_que = urlencode($mark_que);    
		    }
		    break;
		case 'DOUBLE_ENCODE_DEEPURL':
		    $result = str_ireplace('[DOUBLE_ENCODE_DEEPURL]', ($m[1] == ''? $strDeepUrl : urlencode(urlencode($strDeepUrl))), $result);    
		    if ($m[3] == '[?|&]' && $m[1] != '') {
				$mark_and = urlencode(urlencode($mark_and));
				$mark_que = urlencode(urlencode($mark_que));    
		    }
		    break;       
		case 'URI':
		    $result = preg_replace('/([^:])\/{2,}/', '\1/', str_ireplace('[URI]', '/'.(isset($q[3]) && $q[3] != ''? $q[3] : ''), $result));       
		    break;
		case 'ENCODE_URI':
		    $result = preg_replace('/([^:])\/{2,}/', '\1/',  str_ireplace('[ENCODE_URI]', urlencode('/'.(isset($q[3]) && $q[3] != ''? $q[3] : '')), $result));
		    if ($m[3] == '[?|&]' && $m[1] != '') {
				$mark_and = urlencode($mark_and);
				$mark_que = urlencode($mark_que);    
		    }
		    break;
		case 'DOUBLE_ENCODE_URI':
		    $result = preg_replace('/([^:])\/{2,}/', '\1/',  str_ireplace('[DOUBLE_ENCODE_URI]', urlencode(urlencode('/'.(isset($q[3]) && $q[3] != ''? $q[3] : ''))), $result));
		    if ($m[3] == '[?|&]' && $m[1] != '') {
				$mark_and = urlencode(urlencode($mark_and));
				$mark_que = urlencode(urlencode($mark_que));    
		    }
		    break;
		    }
		}

		$m = array();
		if (preg_match('/(.*)(\[\?\|&\].*)/', $result, $m)) { //&& $start_w_tpl
		    if ($has_deep_mark) {
				$m[1] = $strDeepUrl;
		    }
		    
		    if (preg_match('/[\?&][^&]+=[^&]*/U', $m[1]))
				$result = str_replace('[?|&]', $mark_and, $result);
			else
				$result = str_replace('[?|&]', $mark_que, $result);
		}
	
		$result = str_ireplace('[SUBTRACKING]', 't', $result);	    
		
		$result = str_ireplace('[SITEIDINAFF]', '2567387', $result);
		/*if($result == $strDeepTpl)	{
			$result = "";
		}*/
	
		return $result;
	}
?>