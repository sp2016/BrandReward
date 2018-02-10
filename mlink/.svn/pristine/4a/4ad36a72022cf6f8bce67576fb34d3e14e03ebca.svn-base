<?php
/*
 * Created on 2007-10-1
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
if (!defined("__FUNC_GPC__"))
{
	define("__FUNC_GPC__", 1);
	
	function is_magic_open(){
		if(@get_magic_quotes_gpc())	return true;
		return false;
	}
	
	function get_post_var($name){
		if (!$name || !isset($_POST[$name])) return "";
		if(is_magic_open())	return stripslashes($_POST[$name]);
		return $_POST[$name]; 
	}
	
	function get_get_var($name){
		if (!$name || !isset($_GET[$name])) return "";
		if(is_magic_open()) return stripslashes($_GET[$name]);
		return $_GET[$name];
	}
	
	function get_request_var($name){
		if (!$name || !isset($_REQUEST[$name])) return "";
		if(is_magic_open()) return stripslashes($_REQUEST[$name]);
		return $_REQUEST[$name]; 
	}
	
	function get_cookie_var($name){
		if (!$name || !isset($_COOKIE[$name])) return "";
		if(is_magic_open()) return stripslashes($_COOKIE[$name]);
		return $_COOKIE[$name]; 
	}
	
	function get_ssl_rd_url($url){
		return "https://edm.megainformationtech.com/rd.php?url=" . urlencode($url);
	}
	
	function getFirstLevelDomain($url = '') {
		if (empty($url)) return false;
		$url = strtolower($url);
		$topDomain = array('.com', '.net', '.org', '.gov', '.mobi', '.info', '.biz', '.cc', '.tv', '.asia', '.me', '.travel', '.tel', '.name', '.co', '.so');
		$pattern = "^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(([a-z]|\d|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])*([a-z]|\d|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])))\.)+(([a-z]|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(([a-z]|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])*([a-z]|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\x{E000}-\x{F8FF}]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$";
		
		mb_regex_encoding("utf-8");
		if (!mb_ereg($pattern, trim($url))) return false;
		
		$url = trim(preg_replace("/https?:\/\//i", '', trim($url)));
		
		if (strpos($url, '/') !== false) {
			$url = substr($url, 0, strpos($url, '/'));
		} elseif (strpos($url, '?') !== false) {
			$url = substr($url, 0, strpos($url, '?'));
		}
		
		$pos = false;
		foreach ($topDomain as $k => $v) {
			$curValLen = strlen($v);
			$pos = stripos($url, $v);
			if ($pos !== false && (empty($url{$pos + $curValLen}) || $url{$pos + $curValLen} == '.')) break;
		}
		
		if ($pos === false) $pos = strripos($url, '.');
		
		if ($pos === false) return false;
		
		for ($i = $pos - 1; ; $i--) {
			if ($i == 0) {
				$firstDomain = substr($url, $i);
				break;
			} else {
				if ($url[$i] == '.') {
					$firstDomain = substr($url, $i + 1);
					break;
				}
			}
		}
		
		return $firstDomain;
	}
	
	function replaceProgramUrlTemplate($idInAff = '', $programUrlTemplate = '') {
		$res = $programUrlTemplate;
		if (empty($idInAff) || empty($programUrlTemplate)) return '';
		
		$pattern = "/\[IDINAFF(?:\|(split|ltrim|rtrim):([^\]]+))?\]/";
		
		while (true) {
			if (empty($res)) return '';
			
			preg_match($pattern, $res, $matches);
			if (empty($matches)) return $res;
			if (count($matches) == 1) {
				$res = str_replace($matches[0], $idInAff, $res);
			} else {
				switch ($matches[1]) {
					case "split":
						$idInAffTmp = explode('_', $idInAff);
						if ($matches[2] == 1) {
							$res = (count($idInAffTmp) > 0) ? str_replace($matches[0], $idInAffTmp[0], $res): '';
					    } elseif ($matches[2] == 2) {
					    	$res = (count($idInAffTmp) > 1) ? str_replace($matches[0], $idInAffTmp[1], $res): '';
					    }
						break;
				    case "ltrim":
				    	if (!preg_match('/^' . $matches[2] . '/', $idInAff, $match)) return '';
				    	$idInAffSplit = preg_replace(array('/^' . $matches[2] . '/'), '', $idInAff);
				    	$res = !empty($idInAffSplit) ? str_replace($matches[0], $idInAffSplit, $res) : '';
				    	break;
				    case "rtrim":
				    	if (preg_match('/' . $matches[2] . '$/', $idInAff, $match)) return '';
				    	$idInAffSplit = preg_replace(array('/' . $matches[2] . '$/'), '', $idInAff);
				    	$res = !empty($idInAffSplit) ? str_replace($matches[0], $idInAffSplit, $res) : '';
				    	break;
				}
			}
		}
	}
	
	function getUrlDomain($url = '') {
		if (empty($url)) return false;
		$url = strtolower($url);
		$topDomain = array('.com', '.net', '.org', '.gov', '.mobi', '.info', '.biz', '.cc', '.tv', '.asia', '.me', '.travel', '.tel', '.name', '.co', '.so', '.com.au', '.co.uk', '.ca');
		$pattern = "^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(([a-z]|\d|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])*([a-z]|\d|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])))\.)+(([a-z]|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(([a-z]|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])*([a-z]|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\x{E000}-\x{F8FF}]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$";
		
		mb_regex_encoding("utf-8");
		if (!mb_ereg($pattern, trim($url))) return false;
		
		$url = trim(preg_replace("/https?:\/\//i", '', trim($url)));
		
		if (strpos($url, '/') !== false) {
			$url = substr($url, 0, strpos($url, '/'));
		}
		
		if (strpos($url, '.') !== false) {
			$firstPart = substr($url, 0, strpos($url, '.') + 1);
			$leftPart = substr($url, strpos($url, '.') + 1);
			if (strpos($firstPart, 'www') !== false) {
				if (!in_array('.' . $leftPart, $topDomain)) return $leftPart;
			}
		}
		
		return $url;
	}
	
	function getAddresAndName($address){
    	$res = array();
    	$index = stristr($address, "<");
    	if($index === false){
    		$res["name"] = "";
    		$res["address"] = $address;
    		return $res;
    	}else{
    		$pos = strpos($address, "<");
    		$name = trim(substr($address, 0, $pos));
    		$namedecode = imap_mime_header_decode($name);
    		if (!empty($namedecode)) {
    			$res["name"] = $namedecode[0]->text;
    		} else {
    			$res["name"] = '';
    		}
    		$res["address"] = trim(substr($index, 1, strlen($index ) -2 ));
    		return $res;
    	}
    }
   function getTimeStampString(){
		$str = microtime();
		$tmpArr = explode(" ", $str);
		$str = $tmpArr[1] . str_ireplace("0.", "", $tmpArr[0]);
		return $str;
	} 
}
	function remote_filesize($uri,$user='',$pw='')
	{
		 ob_start();
		 $ch = curl_init($uri);
		 curl_setopt($ch, CURLOPT_HEADER, 1);
		 curl_setopt($ch, CURLOPT_NOBODY, 1);
		 if (!empty($user) && !empty($pw))
		 {
			 $headers = array('Authorization: Basic ' . base64_encode($user.':'.$pw)); 
			 curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		 }
		 $okay = curl_exec($ch);
		 curl_close($ch);
		 $head = ob_get_contents();
		 ob_end_clean();
		 $regex = '/Content-Length:\s([0-9].+?)\s/';
		 $count = preg_match($regex, $head, $matches);
		 if (isset($matches[1]))
		 {
		 $size = $matches[1];
		 }
		 else
		 {
		 $size = 'unknown';
		 }
		 $last=round($size/(1024*1024),3);
		 return $last.' MB';
	}

	function timezoneConvert($time, $timeZoneTo = "PRC", $reverse = false){
		if($time == '0000-00-00 00:00:00') return $time;
		if(trim($time) == ""){
			return "";
		}
		if(trim($timeZoneTo) == ""){
			$timeZoneTo = "PRC";
		}
		$timezoneOld = date_default_timezone_get();
		$curTime = strtotime($time);
		date_default_timezone_set($timeZoneTo);
		if($reverse){
			$curTime = strtotime($time);
			date_default_timezone_set($timezoneOld);
		}
		$curDate = date("Y-m-d H:i:s", $curTime);
		date_default_timezone_set($timezoneOld);
		return $curDate; 
	}
	function sqlCacheSave($key,$time,$arr=array()){
		$dir = INCLUDE_ROOT.'data/sqlcache/';
		if(!is_dir($dir)) @mkdir($dir);
		$file = $dir.$key.'.php';
		if(count($arr)>0){
			file_put_contents($file,"<?php\n return ".var_export($arr, true)."\n?>");
			return $arr;
		}
		if(is_file($file)){
			if(time()-filemtime($file)>$time*60) return false;
			$data = include $file;
			if(count($data)>0) return $data;
			return false;
		}
		return false;
	}
?>
