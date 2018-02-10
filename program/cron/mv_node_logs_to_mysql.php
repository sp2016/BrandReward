<?php
include_once(dirname(dirname(__FILE__))."/etc/const.php");

$date_now = date("Y-m-d H:i:s");
$date_log_pos = date("Y-m-d H:i:s", strtotime(" -13 minutes"));

echo "Start @ ".date("Y-m-d H:i:s")."\r\n";
$type = "mlink";
if(isset($_SERVER["argc"]) && $_SERVER["argc"] > 1)
{
	foreach($_SERVER["argv"] as $v){
		$tmp = explode("=", $v);		
		if($tmp[0] == "--type"){			
			$type = trim($tmp[1]);
		}		
	}			
}

if(!in_array($type, array("mega", "mlink"))){
	echo "type not author";
	exit;
}

if(!checkProcess(__FILE__)){
	echo 'process still runing.\r\n';
	echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";
	exit;
}

function checkProcess($process_name){
	$cmd = `ps aux | grep $process_name | grep 'grep' -v | grep '/bin/sh' -v -c`;
	$return = ''.$cmd.'';
	if($return > 1){
		return false;
	}else{
		return true;
	}
}

if($type == "mega"){
	$objMysql = new Mysql(PROD_DB_NAME, MEGA_DB_HOST, PROD_DB_USER, PROD_DB_PASS);
}else{
	$objMysql = new Mysql(PROD_DB_NAME, MLINK_DB_HOST, PROD_DB_USER, PROD_DB_PASS);
}

(float)$aa = 0; 
(float)$cc = 0; 
(float)$bb = 0; 
(float)$dd = 0; 
(float)$ee = 0; 
(float)$ff = 0; 

$i = $max_len = 0;
//$arr_file = array(LOG_DIR.$type."_tracking/".date("Y-m-d-H", strtotime(" -6 minutes")).SERVER_NAME.".json", LOG_DIR.$type."_tracking/".date("Y-m-d-H").SERVER_NAME.".json");
if(date("i") <= 10){
	$arr_file = array(LOG_DIR.$type."_tracking/".date("Y-m-d-H", strtotime(" -1 hours")).SERVER_NAME.".json", LOG_DIR.$type."_tracking/".date("Y-m-d-H").SERVER_NAME.".json");
}else{
	$arr_file = array(LOG_DIR.$type."_tracking/".date("Y-m-d-H").SERVER_NAME.".json");	
}
$arr_file = array_unique($arr_file);

$domain_cache = array();

$sql = 'SELECT id, apikey, alias FROM publisher_account';
$account_info = $objMysql->getRows($sql, 'apikey');

foreach($arr_file as $f_name){
	insertDb($f_name);
}
echo "End $i @ $max_len - " . $aa . "  - " . $bb . "  - " . $cc . "  - " . $dd . " - ".date("Y-m-d H:i:s")."\r\n";
exit;


function insertDb($f_name){
	global $i, $objMysql, $date_log_pos, $domain_cache, $date_now, $max_len, $aa, $bb, $cc, $dd, $ee, $ff, $account_info, $type;
	$yy = $zz = '';
	//$t = microtime(true);
	if(file_exists($f_name)){
		echo $f_name;
		$fp = fopen($f_name, "r");
		while(!feof($fp)){
			//$t1 = microtime(true);
		    $data = fgets($fp, 10000);		   
		    if(strlen($data) > $max_len) $max_len = strlen($data) ;
		    $data = json_decode($data);
			//$t2 = microtime(true);
			//(float)$dd += ($t2 - $t1);
					
					
			if(is_object($data)){
				//if(isset($data->created) && $data->created > $date_now) break;
				//if(!isset($data->created) || $data->created < $date_log_pos) continue;
				
				if(empty($yy)) echo $yy = date('Y-m-d H:i:s')."\r\n";
								
				if(isset($data->linkid) && isset($data->_link) && isset($data->_link->affid)){
					$affid = intval($data->_link->affid);					
				}else{
					$affid = intval(@$data->affId);
				}				
				
				if(isset($data->linkid) && isset($data->_link) && isset($data->_link->programid)){
					$programid = intval($data->_link->programid);
				}else{
					$programid = intval(@$data->programId);
				}
				
				if($affid == 191 && $programid == 0){
					$affid = 0;
				}
				if($affid == 639){
					$affid = 0;
				}
				
				
				if(isset($data->linkid) && isset($data->_link) && isset($data->_link->domain) && strlen($data->_link->domain)){
					$domain = trim($data->_link->domain);
					$did = intval(@$data->_link->domainid);
				}else{
					$domain = $data->hostname;
					$did = intval(@$data->domainId);
				}
				
				if($did == 0 && $domain){
					//$t1 = microtime(true);					
					if(isset($domain_cache[$domain])){
						$did = intval($domain_cache[$domain]);
					}else{
						$sql = "select id from domain where domain = '".addslashes($domain)."' limit 1";							
						$did = intval($objMysql->getFirstRowColumn($sql));
						$domain_cache[$domain] = $did;
					}					
					//$t2 = microtime(true);
					//(float)$aa += ($t2 - $t1);
				}
				/*
				$isfake = 0;
				if(isset($data->outgoing_info->IsFake) && $data->outgoing_info->IsFake == 'YES') $isfake = 1;*/
				$apikey = @$data->_account->apikey;
				if(!$apikey){					
					$apikey = $data->site;
					$data->site = $account_info[$apikey]['alias'];
				}
				
				//ip_from_mk
				$ip = addslashes(@$data->ip);
				if(@$data->ip_from_mk && @$data->ip_from_mk != ''){
					$ip = addslashes($data->ip_from_mk);
				}
				
				//linkid only in BR
				$linkid = intval(@$data->linkid);				
				
				//$t1 = microtime(true);
				$sql = 'select id from bd_out_tracking where createddate = "'.addslashes(substr($data->created, 0, 10)).'" and sessionId = "'.addslashes($data->sessionId).'" limit 1';
				$tmp_arr = array();
				$tmp_arr = $objMysql->getFirstRow($sql);
				//$t2 = microtime(true);
				//(float)$bb += ($t2 - $t1);
				if(!count($tmp_arr)){		
					//$t1 = microtime(true);					
					$sql = "INSERT IGNORE INTO bd_out_tracking SET 
									pageUrl 		= '".addslashes($data->pageUrl)."',
									outUrl 			= '".addslashes($data->outUrl)."',
									sessionId 		= '".addslashes($data->sessionId)."',
									created 		= '".addslashes($data->created)."',						
									publishTracking = '".addslashes($data->publishTracking)."',
									domainUsed 		= '".addslashes($domain)."',
									domainId 		= $did,
									programId 		= '$programid',
									affId 			= '".$affid."',									
									createddate 	= '".addslashes(substr($data->created, 0, 10))."',
									alias 			= '".addslashes($data->site)."',
									site 			= '".addslashes($apikey)."',
									referer			= '".addslashes(@$data->headers->referer)."',
									cookie			= '".addslashes(@$data->cookie_val)."',
									ip				= '".$ip."',
									country			= '".substr(addslashes(@$data->ip_cc),0,2)."'
							";
					if($type != 'mega'){
						$sql .= ", linkid = $linkid";
					}
					$objMysql->query($sql);
					$i++;
					
					if(empty($zz)) echo $zz = date('Y-m-d H:i:s')."\r\n";
					
					//$t2 = microtime(true);
					//(float)$cc += ($t2 - $t1);
				}
				
			}
		}
		fclose($fp);
	}else{
		echo "[err]no file: ".$f_name."\r\n";
	}
}


?>