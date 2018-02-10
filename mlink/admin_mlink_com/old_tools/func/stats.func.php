<?php
/*
 * FileName: func.common.stats.php
 * Author: Lee
 * Remark: 
*/
if(!defined("__FUNC_COMMON_STATS__"))
{	
	
	define("__FUNC_COMMON_STATS__", 1);
	
	function filter($var)
	{
		if(substr(trim($var), 0, 1) != "#" && trim($var) != "") return true;
	}
	
	function filterSpace($var)
	{
		return trim($var);
	}
	
	function getRobotsList()
	{
		$tmpArr = file(TRACKING_ROBOTS_FILE_PATH);
		if(!$tmpArr)
		{
			return array();
		}
		else
		{
			$tmpArr = array_filter($tmpArr, 'filter');
			$tmpArr = array_map('filterSpace', $tmpArr);
			return $tmpArr;
		}
	}
	
	function getFraudIPList()
	{
		$tmpArr = file(TRACKING_IGNOREDIP_FILE_PATH);
		if(!$tmpArr)
		{
			return array();
		}
		else
		{
			$tmpArr = array_filter($tmpArr, 'filter');
			$tmpArr = array_map('filterSpace', $tmpArr);
			return $tmpArr;
		}
	}
	
	function getDomain()
	{
		$arrDomain = explode(".",$_SERVER['SERVER_NAME']);	
		$domain = ".".$arrDomain[count($arrDomain)-2].".".$arrDomain[count($arrDomain)-1];
		return 	$domain;	
	}

######################################################
	function getAffNameWithUrl($dstUrl, $directory = "")
	{
		$aff = '';
		$arrAffUrl = array();
		$tmpArr = "";
		
		if($directory == ""){
			$tmpArr = file(INCLUDE_ROOT."data/t_affurl.txt");
		}else{
			$tmpArr = file($directory."/t_affurl.txt");
//			echo $directory."/t_affurl.txt";
		}
		
		foreach($tmpArr as $v)
		{
			if(!filter($v)) continue;
			list($affiliate, $url) = explode("\t", trim($v));
			if(stripos($affiliate, "~") !== false)
			{
				list($nothing, $affiliate) = explode('~', $affiliate);
			}
			$arrAffUrl[trim($url)] = trim(strtolower($affiliate));
		}
		foreach($arrAffUrl as $k=>$v)
		{
			$k = (string)$k;
			if(stripos($dstUrl, $k) !== false)
			{
				$aff = $arrAffUrl[$k];
				break;
			}
		}
		return $aff;
	}
	
	function getAffIdWithUrl($dstUrl, $directory = "")
	{
		$affid = 0;
		$arrAffUrl = array();
		$tmpArr = "";
		
		if($directory == ""){
			$tmpArr = file(INCLUDE_ROOT."data/t_affurl.txt");
		}else{
			$tmpArr = file($directory."/t_affurl.txt");
//			echo $directory."/t_affurl.txt";
		}
		
		foreach($tmpArr as $v)
		{
			if(!filter($v)) continue;
			list($affiliate, $url) = explode("\t", trim($v));
			if(stripos($affiliate, "~") !== false)
			{
				list($affiliate_id, $affiliate) = explode('~', $affiliate);
			}
			$arrAffUrl[trim($url)] = intval(trim($affiliate_id));
		}
		foreach($arrAffUrl as $k=>$v)
		{
			$k = (string)$k;
			if(stripos($dstUrl, $k) !== false)
			{
				$affid = $arrAffUrl[$k];
				break;
			}
		}
		return ($affid > 0 ? $affid : false);
	}

	function checkAffNameWithUrl($dstUrl, $affIdArr, $directory = "")
	{
		
		$aff = '';
		$arrAffUrl = array();
		$tmpArr = "";
		
		if($directory == ""){
			$tmpArr = file(INCLUDE_ROOT."etc/t_affurl.txt");
		}else{
			$tmpArr = file($directory."/t_affurl.txt");
//			echo $directory."/t_affurl.txt";
		}
		
		foreach($tmpArr as $v)
		{
			if(!filter($v)) continue;
			list($affiliate, $url) = explode("\t", trim($v));
			if(stripos($affiliate, "~") !== false)
			{
				list($nothing, $affiliate) = explode('~', $affiliate);
				
			}
			if(!isset($affIdArr[trim($nothing)])){
				continue;
			}
			
			$arrAffUrl[trim($url)] = trim(strtolower($affiliate));
		}
		
		foreach($arrAffUrl as $k=>$v)
		{
			$k = (string)$k;
			if(stripos($dstUrl, $k) !== false)
			{
				
				$aff = $arrAffUrl[$k];
				break;
			}
		}
	
		return $aff;
	}
	
	function getCJPIDFromURL($dstUrl, $dir = "")
	{
		$pid = "";
		$dstUrl = trim($dstUrl);
		$affiliate = getAffNameWithUrl($dstUrl, $dir);
		if(strcasecmp($affiliate, 'cj') == 0)
		{
			$pattern = "/\/click-([^-]+)-/i";
			if(preg_match($pattern, $dstUrl, $arrMatch))
			{
				$pid = trim($arrMatch[1]);
			}
		}
		return $pid;
	}
	
	function getAffUrlWithSID($dstUrl, $incomingId, $outgoingId , $dir = "")
	{
		$dstUrl = trim($dstUrl);
		$affiliate = getAffNameWithUrl($dstUrl, $dir);
		if(!$affiliate)
		{
			//add error log here, some destination url should be error
			error_log(date("Y-m-d H:i:s")."\t".$incomingId."\t".$dstUrl."\n", 3, LOG_LOCATION."badaffurl.txt");
			return $dstUrl;
		}
		
		//replace CJ PID
		global $g_arrPid;
		$CJPid = getCJPIDFromURL($dstUrl);
		if($CJPid && isset($g_arrPid[SITE_NAME]) &&  strcasecmp($g_arrPid[SITE_NAME], $CJPid) <> 0)
		{
			$pattern = "/\/click-$CJPid-/i";
			$dstUrl = preg_replace($pattern, "/click-".$g_arrPid[SITE_NAME]."-",$dstUrl);
		}
		//end
		
		$arrAffSID = array();
		$tmpArr = file(INCLUDE_ROOT."etc/t_affsid.txt");
		foreach($tmpArr as $v)
		{
			if(!filter($v)) continue;
			list($aff, $sidPattern) = explode("\t", trim($v));
			if(stripos($aff, "~") !== false)
			{
				list($nothing, $aff) = explode('~', $aff);
			}
			$arrAffSID[trim(strtolower($aff))] = trim($sidPattern);
		}

		//zanox is a sepcial case, only allow night digitals, we put incomingid and site id (site id is the last digital)
		/* 
		//we change to use GPP parameter as sub affiliateID
		if(strcasecmp($affiliate, 'zanox') == 0 && strlen($incomingId) < 9)
		{
			$siteid = substr(SID_PREFIX, -1) + 0; 
			$dstUrl =  preg_replace("/\/ppc\/\?([0-9]+)C([0-9]+)T/", "/ppc/?\\1C\\2S{$incomingId}{$siteid}T", $dstUrl);
		}
		*/
		if(isset($arrAffSID[$affiliate]) && (stripos($arrAffSID[$affiliate], "=") !== false))
		{
			$sid = str_replace('{site}', SID_PREFIX, $arrAffSID[$affiliate]);
			$sid = str_replace('{incomingid}', $incomingId, $sid);
			$sid = str_replace('{outgoingid}', $outgoingId, $sid);
			$sid = str_replace('{serverid}', getServerId(), $sid);
			$sid = str_replace('{currserverid}', getCurrServerId(), $sid);
			
			list($subAffIDGetVarName, $subAffIDGetVarValue) = explode("=", $sid);
			$dstUrl = addGetVar2Url($dstUrl, $subAffIDGetVarName, $subAffIDGetVarValue, $affiliate);
		}
		return $dstUrl;
	}

	function getServerId()
	{
		global $g_serverId;
		if(isset($g_serverId)) return $g_serverId;
		if(isset($_COOKIE["U_S"])) return $_COOKIE["U_S"];
		return getCurrServerId();
	}
	
	function getCurrServerId()
	{
		$server_name = php_uname("n");
		list($short_server_name) = explode(".",$server_name);
		if(preg_match("/(web|admin|backup)([0-9]+)/",$short_server_name,$matches))
		{
			$g = $matches[1];
			$s = $matches[2];
			if($g == "web") $short_server_name = intval($s);
			elseif($g == "admin") $short_server_name = intval($s) + 100;
			elseif($g == "backup") $short_server_name = intval($s) + 200;
		}
		return $short_server_name;
	}
	
	function addGetVar2Url($url, $varName, $value="", $affiliate="")
	{
		$urlRtn = "";
		$url = trim($url);

		/*
		for some stupid affiliates!!!, they do NOT allow to append a GET value to the url, if the url ends with a deep url
		we sparate the deep url and append it again at last
		*/
		$arrAffDeepUrl = array();
		$tmpArr = file(INCLUDE_ROOT."etc/t_affdeepurl.txt");
		foreach($tmpArr as $v)
		{
			if(!filter($v)) continue;
			list($aff, $var) = explode("\t", trim($v));
			if(stripos($aff, "~") !== false)
			{
				list($nothing, $aff) = explode('~', $aff);
			}
			$aff = strtolower(trim($aff));
			$arrAffDeepUrl[$aff] = trim($var);
		}

		$deepurl = "";
		if(isset($arrAffDeepUrl[$affiliate]) && preg_match("/[&?]{1}(".$arrAffDeepUrl[$affiliate]."=.*)$/i", $url, $match))
		{
			$deepurl = $match[0];
			$url = preg_replace("/[&?]{1}(".$arrAffDeepUrl[$affiliate]."=.*)$/i", "", $url);
		}

		$questionMarkPos = strpos($url, '?');
		$addressMarkPos = strpos($url, '&');
		
		if($questionMarkPos === false && $addressMarkPos === false)
		{
			if(stripos($affiliate, 'onenetworkdirect') !== false)
				$urlRtn .= $url . "&" . $varName . "=" . $value;
			else
				$urlRtn .= $url . "?" . $varName . "=" . $value;
		}
		else
		{
			if($questionMarkPos === false) // very strange, no '?' but have '&'
			{
				$urlRtn = substr($url, 0, $addressMarkPos);
				$subfix = substr($url, $addressMarkPos+1);
				$urlRtn .= "&";
			}
			else
			{
				$urlRtn = substr($url, 0, $questionMarkPos);
				$subfix = substr($url, $questionMarkPos+1);
				$urlRtn .= "?";
			}
			if(!$subfix) //ends with '?' or '&'
			{
				$urlRtn .= $varName."=".$value;
			}
			else
			{
				$arrTmp = explode("&", $subfix);
				$isMatched = false;
				foreach($arrTmp as $v)
				{
					if(strpos($v, '=') === false)
					{
						$urlRtn .= $v."&";
						continue;
					}
					list($a, $b) = explode("=", $v);
					if(strcmp($a, $varName) == 0)
					{
						$urlRtn .= $a."=".$value."&";
						$isMatched = true;
					}
					else
						$urlRtn .= $a."=".$b."&";
				}
				if(!$isMatched)
				{
					$urlRtn .= $varName."=".$value;
				}
				$urlRtn = rtrim($urlRtn, "&");
			}
		}
		if($deepurl)
			$urlRtn = $urlRtn."&".ltrim($deepurl, "&?");
		return $urlRtn;
	}

	function checkURLValidation($url)
	{
		$rtn = array();
		$badTagsInLP = array(); //should be valid perl regular expressions
		$badTagsInLP['sas'] = "/face=verdana>The link is not currently active.<BR><BR>/i";
		$badTagsInLP['pjn'] = "/^This link is not valid.$/i";
		$badTagsInLP['cj'] = "/<p>Sorry, the link you clicked is no longer active.<\/p>/i";
		$badTagsInLP['gan'] = "/<strong>This link is not active.&nbsp; <\/strong>/i";

		if(!trim($url))
		{
			$rtn['res'] = false;
			$rtn['err'] = "empty url";
			return $rtn;
		}
		//echo "URL:".$url."\n";
		$arrRtn = my_curl($url);
		//echo "URL:".$arrRtn['lastrepcode']."\n";
		//echo "URL:".$arrRtn['lastrdurl']."\n";
		
		if(stripos($arrRtn['content_type'], 'text/html') === false)
		{
			$rtn['res'] = false;
			$rtn['err'] = "url content type not text/html, it is ".$arrRtn['content_type'].", url is $url";
			return $rtn;
		}
		if($arrRtn['lastrepcode'] <> '200')
		{
			$rtn['res'] = false;
			$rtn['err'] = "url last rep code not 200, it is ".$arrRtn['lastrepcode'] .", url is $url";
			return $rtn;
		}
		foreach($badTagsInLP as $p)
		{
			if(@preg_match($p, $arrRtn['content']) !== false && @preg_match($p, $arrRtn['content']) !== 0)
			{
				$rtn['res'] = false;
				$rtn['err'] = "content with bad tags $p".", url is $url";
				return $rtn;
			}
		}
		$oldLastRdURL = $arrRtn['lastrdurl'];
		$oldContent = $arrRtn['content'];
		$newURL = getAffUrlWithSID($url, 0, 0);

		//echo "New URL:".$newURL."\n";
		$arrRtn = my_curl($newURL);
		//echo "New URL:".$arrRtn['lastrepcode']."\n";
		//echo "New URL:".$arrRtn['lastrdurl']."\n";

		if(stripos($arrRtn['content_type'], 'text/html') === false)
		{
			$rtn['res'] = false;
			$rtn['err'] = "new url content type not text/html, it is ".$arrRtn['content_type'].", url is $newURL";
			return $rtn;
		}

		if($arrRtn['lastrepcode'] <> '200')
		{
			$rtn['res'] = false;
			$rtn['err'] = "new url last rep code not 200 ".$arrRtn['lastrepcode'].", url is $newURL";
			return $rtn;
		}
		foreach($badTagsInLP as $p)
		{
			if(@preg_match($p, $arrRtn['content']) !== false && @preg_match($p, $arrRtn['content']) !== 0)
			{
				$rtn['res'] = false;
				$rtn['err'] = "new url content with bad tags $p".", url is $newURL";
				return $rtn;
			}
		}
		if(strcasecmp(trim($oldLastRdURL), trim($arrRtn['lastrdurl'])) <> 0)
		{
			if(strcmp(md5(trim(strtolower($oldContent))), md5(trim(strtolower($arrRtn['content'])))) <> 0)
			{
				$pagetitle1 = "";
				if(preg_match("/<\s*title\s*>([^<]*)<\s*\/\s*title\s*>/i", $oldContent, $matches))
				{
					$pagetitle1 = trim($matches[1]);
//					echo $pagetitle1."<br>";
				}

				$pagetitle2 = "";
				if(preg_match("/<\s*title\s*>([^<]*)<\s*\/\s*title\s*>/i", $arrRtn['content'], $matches))
				{
					$pagetitle2 = trim($matches[1]);
//					echo $pagetitle2."<br>";
				}

				if(($pagetitle1 || $pagetitle2) && strcmp(md5(trim(strtolower($pagetitle1))), md5(trim(strtolower($pagetitle2)))) <> 0)
				{
					$rtn['res'] = false;
					$rtn['err'] = "contents of tow urls not consistent $url | $newURL ";
					return $rtn;
				}
			}
		}
		$rtn['res'] = true;
		$rtn['err'] = "";
		return $rtn;
	}
		
	function my_curl($url,  $js_loop=0, $curlCookieFile="")
	{
		$rtnArr = array(); // array('lastrepcode'=>'', 'lastrdurl'='', 'content'=>'')
		
		$url = str_replace( "&amp;", "&", urldecode(trim($url)));
		if(!$curlCookieFile)
		{
			$curlCookieFile = tempnam("/tmp/",'curl_cookie');
		}
		$ch = curl_init($url);
		curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.8.1)  Gecko/20041001 Firefox/0.10.1");
		curl_setopt( $ch, CURLOPT_COOKIEJAR, $curlCookieFile);
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt( $ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt( $ch, CURLOPT_TIMEOUT, 30);
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10);
		$content = curl_exec($ch);
		$response = curl_getinfo($ch);
	//	echo $content;
	//	print_r($response);
		curl_close ($ch);
		
		if ( ( preg_match("/>.*?window\.location\.replace\('(.*?)'\)/i", $content, $value) 
			|| preg_match("/>.*?window\.location\=\"(.*?)\"/i", $content, $value)  
			|| preg_match("/<meta.*?content=\"[0-9]+;\s*url=(.*?)\"/i", $content, $value)
			|| preg_match("/<meta.*?http-equiv=\"redirect\"\s+content=\"(.*?)\"/i", $content, $value)
			&&  $js_loop < 5) )
		{
			return my_curl($value[1], $js_loop+1, $curlCookieFile);
		}
		else
		{
			if(file_exists($curlCookieFile)) unlink($curlCookieFile);
			$rtnArr['lastrepcode'] = $response['http_code'];
			$rtnArr['lastrdurl'] = $response['url'];
			$rtnArr['content_type'] = $response['content_type'];
			$rtnArr['content'] = $content;
			return $rtnArr;
		}
	}
	function convertEncoding($encode, $str, $isemail = false) 
	{
		$str = mb_convert_encoding($str, "UTF-16", $encode);
		for ($i = 0; $i < strlen($str); $i++,$i++) {
			$code = ord($str{$i}) * 256 + ord($str{$i + 1});
			if ($code < 128 and !$isemail) {
				$output .= chr($code);
			} else if ($code != 65279) {
				$output .= "&#".$code.";";
			}
		}
		return $output;
	} 
}
?>