<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
include_once(INCLUDE_ROOT . "func/func.php");

$id_arr = array();
$is_debug = false;
$pid = "";
$is_quick = false;
if(isset($_SERVER["argc"]) && $_SERVER["argc"] > 1)
{
	foreach($_SERVER["argv"] as $v){
		$tmp = explode("=", $v);		
		if($tmp[0] == "--affid"){			
			$id_arr = explode(",", $tmp[1]);
		}elseif($tmp[0] == "--debug"){
			$is_debug = true;
		}elseif($tmp[0] == "--pid"){
			$pid = "a.PID = " .intval($tmp[1]);
		}elseif($tmp[0] == "--quick"){
			$is_quick = true;
		}
	}			
}


echo "<< Start @ ".date("Y-m-d H:i:s")." >>\r\n";

define("NO_MYSQL_CACHE", true);

$objProgram = New Program();

$objRedis = new Redis();
$objRedis->pconnect(REDIS_HOST, REDIS_PORT);


$update_time = date("Y-m-d H:i:s");

echo "size:".$objRedis->dbSize()."\t";
echo ":ACCOUNT::".count($objRedis->keys(":ACCOUNT:*"))."\t";
echo ":AFF::".count($objRedis->keys(":AFF:*"))."\t";
echo ":DOMAIN::".count($objRedis->keys(":DOMAIN:*"))."\r\n";
//echo ":DOMAIN::".$objRedis->sSize(":DOMAIN:*")."\r\n";


/*$time = date("Y-m-d");
$did_arr = $objProgram->getNeedCheckDomain("2015-04-01");
echo count($did_arr)."|\r\n";
$cnt = $objProgram->checkDomainProgramRel($did_arr);
echo $cnt."\r\n";*/

$where_arr = array("1=1");
if(count($id_arr)){
	$where_arr[] = "b.affid in (".implode(",", $id_arr).")";
}
if($pid){
	$where_arr[] = $pid;
}


/*$tmp_redis = array();
$tmp_redis = $objRedis->keys(":DOMAIN:*");
foreach($tmp_redis as $v){
	$objRedis->del($v);
}*/
$sql_quick = "";
if($is_quick){
	$sql_quick = " and a.LastUpdateTime > '".date("Y-m-d H:i:s", strtotime("-7 minutes"))."'";
}

$update_key = array();
$i = 0;
$j = 0;
$pos = 0;
$ss_ite = array("","au", "uk", "ca", "us", "de", "fr");

//for content publisher can use all type program
$start = 0;
$j = 0;
$pos = 0;
while(1) {
	$sql = "select a.id, a.Site, a.DID, a.PID, a.Key, b.IdInAff, b.AffId, b.CommissionUsed, b.CommissionType, b.CommissionIncentive, b.AffDefaultUrl as p_AffDefaultUrl, b.DeepUrlTpl as p_DeepUrlTpl, d.AffDefaultUrl, d.DeepUrlTpl, b.OutGoingUrl, c.Domain, a.LimitAccount, c.SubDomain, a.IsFake, a.AffiliateDefaultUrl as fake_AffDefaultUrl, a.DeepUrlTemplate as fake_DeepUrlTpl, b.SupportDeepUrl
	from redirect_default a inner join program_intell b on a.pid = b.programid inner join domain c on c.id = a.did left join r_domain_program d on a.did = d.did and a.pid = d.pid
	where " . implode(" and ", $where_arr) . " and b.isactive = 'active' $sql_quick AND a.id > $pos ORDER BY a.id LIMIT 1000";
	if ($is_debug) {
		echo $sql . "\r\n";
	}
	$tmp_arr = array();
	$tmp_arr = $objProgram->objMysql->getRows($sql);
	if (!count($tmp_arr)) break;
	if ($start > 1000) {
		break;
	}
	$start++;
	
	//print_r($p_arr);exit;
	foreach ($tmp_arr as $row) {
		if ($row["id"] > $pos) $pos = $row["id"];
		
		if ($row["Key"]) {
			if (empty($row["AffDefaultUrl"])) {
				$row["AffDefaultUrl"] = $row["p_AffDefaultUrl"];
				$row["DeepUrlTpl"] = $row["p_DeepUrlTpl"];
			}
			
			if ($row["IsFake"] == "YES" && !empty($row["DeepUrlTpl"])) {
				$row["AffDefaultUrl"] = $row["DeepUrlTpl"];
			}
			
			$row["OutGoingUrl"] = strlen($row["DeepUrlTpl"]) ? $row["DeepUrlTpl"] : $row["AffDefaultUrl"];
			
			unset($row["p_AffDefaultUrl"]);
			unset($row["p_DeepUrlTpl"]);
			unset($row["fake_DeepUrlTpl"]);
			unset($row["fake_AffDefaultUrl"]);
			
			$row["LastUpdateTime"] = $update_time;
			
			if ($row["PID"] == 137311) {
				foreach ($row as $k => $v_toutf8) {
					$row[$k] = iconv('ISO-8859-1', 'UTF-8', $v_toutf8);
				}
			}
			
			
			if (!empty($row["Site"])) {
				if ($row["Site"] == 'global') {
					$row["Key"] = $row["Key"]. ":" ."CONT";
					$objRedis->set(":DOMAIN:" . $row["Key"], json_encode($row));
					$update_key[$row["Key"]] = $row["Key"];
				} else {
					$row["Key"] = strtolower($row["Site"]) . ":" . $row["Key"]. ":" ."CONT";
					$objRedis->set(":DOMAIN:" . $row["Key"], json_encode($row));
					$update_key[$row["Key"]] = $row["Key"];
				}
				$i++;
				$j++;
			}
		}
	}
}
echo "for content publisher finish($j)\r\n";
	
	
//second choice
$start = 0;
$j = 0;
$pos = 0;
while(1) {
	$sql = "select a.DefaultOrder, a.SupportType, a.id, a.Site, a.DID, a.PID, a.Key, b.IdInAff, b.AffId, b.CommissionUsed, b.CommissionType, b.CommissionIncentive, b.AffDefaultUrl as p_AffDefaultUrl, b.DeepUrlTpl as p_DeepUrlTpl, d.AffDefaultUrl, d.DeepUrlTpl, b.OutGoingUrl, c.Domain, a.LimitAccount, c.SubDomain, a.IsFake, b.SupportDeepUrl
	from domain_outgoing_all a inner join program_intell b on a.pid = b.programid inner join domain c on c.id = a.did left join r_domain_program d on a.did = d.did and a.pid = d.pid
	where " . implode(" and ", $where_arr) . " and b.isactive = 'active' $sql_quick AND a.id > $pos ORDER BY a.id LIMIT 100";
	if ($is_debug) {
		echo $sql . "\r\n";
	}
	$tmp_arr = array();
	$tmp_arr = $objProgram->objMysql->getRows($sql);
	if (!count($tmp_arr)) break;
	if ($start > 10000) {
		break;
	}
	$start++;
	
	//print_r($p_arr);exit;
	foreach ($tmp_arr as $row) {
		if ($row["id"] > $pos) $pos = $row["id"];
		
		if ($row["Key"]) {
			if (empty($row["AffDefaultUrl"])) {
				$row["AffDefaultUrl"] = $row["p_AffDefaultUrl"];
				$row["DeepUrlTpl"] = $row["p_DeepUrlTpl"];
			}
			
			if ($row["IsFake"] == "YES" && !empty($row["DeepUrlTpl"])) {
				$row["AffDefaultUrl"] = $row["DeepUrlTpl"];
			}
			
			$row["OutGoingUrl"] = strlen($row["DeepUrlTpl"]) ? $row["DeepUrlTpl"] : $row["AffDefaultUrl"];
			
			unset($row["p_AffDefaultUrl"]);
			unset($row["p_DeepUrlTpl"]);
			unset($row["fake_DeepUrlTpl"]);
			unset($row["fake_AffDefaultUrl"]);
			
			$row["LastUpdateTime"] = $update_time;
			
			if ($row["PID"] == 137311) {
				foreach ($row as $k => $v_toutf8) {
					$row[$k] = iconv('ISO-8859-1', 'UTF-8', $v_toutf8);
				}
			}
			
			
			if (!empty($row["Site"]) && $row["DefaultOrder"] > 0) {
				if ($row["Site"] != 'global') {						
					$row["Key"] = strtolower($row["Site"]) . ":" . $row["Key"];
				}
				
				if ($row["SupportType"] == 'Content') {
					$row["Key"] = $row["Key"] . ":" . "CONT";
				}
				
				$row["Key"] = $row["Key"]. ":" . $row["DefaultOrder"];
				//echo $row["Key"]. "\r\n";
				$objRedis->set(":DOMAIN:" . $row["Key"], json_encode($row));
				$update_key[$row["Key"]] = $row["Key"];
				
				$i++;
				$j++;
			}
		}
	}
}
echo "second choice($j)\r\n";


//other site
$start = 0;
$j = 0;
$pos = 0;
while(1){
	$sql = "SELECT a.id, a.Site, a.DID, a.PID, a.Key, b.IdInAff, b.AffId, b.CommissionUsed, b.CommissionType, b.CommissionIncentive, b.AffDefaultUrl as p_AffDefaultUrl, b.DeepUrlTpl as p_DeepUrlTpl, d.AffDefaultUrl, d.DeepUrlTpl, b.OutGoingUrl, c.Domain, a.LimitAccount, c.SubDomain, a.IsFake, a.AffiliateDefaultUrl as fake_AffDefaultUrl, a.DeepUrlTemplate as fake_DeepUrlTpl, b.SupportDeepUrl
			FROM domain_outgoing_default_other a inner join program_intell b on a.pid = b.programid inner join domain c on c.id = a.did left join r_domain_program d on a.did = d.did and a.pid = d.pid
			where ". implode(" and ", $where_arr) ." and b.isactive = 'active'";	
	$sql .= " $sql_quick AND a.id > $pos ORDER BY a.id LIMIT 1000";
	//$sql .= "limit ". $start*1000 . ", 1000";
			//limit ". $start*1000 . ", 1000";
	//$sql = "SELECT a.DID, a.PID, a.Key, b.IdInAff, b.AffId, b.CommissionUsed, b.CommissionType, b.CommissionIncentive, b.AffDefaultUrl, b.DeepUrlTpl, c.Domain FROM domain_outgoing_default a left join program_intell b on a.pid = b.programid inner join domain c on c.id = a.did WHERE a.Key = 'affiliates.self-publishing-coach.com'";
	if($is_debug){
		echo $sql."\r\n";
	}
	$tmp_arr = array();
	$tmp_arr = $objProgram->objMysql->getRows($sql);
	if(!count($tmp_arr)) break;
	if($start > 1000){
		break;
	}
	$start++;

	//print_r($p_arr);exit;
	foreach($tmp_arr as $row){
		if($row["id"] > $pos) $pos = $row["id"];
		
		if($row["Key"]){
			if(empty($row["AffDefaultUrl"])){
				$row["AffDefaultUrl"] = $row["p_AffDefaultUrl"];
				$row["DeepUrlTpl"] = $row["p_DeepUrlTpl"];
			}
			
			if($row["IsFake"] == "YES" && !empty($row["DeepUrlTpl"])){
				$row["AffDefaultUrl"] = $row["DeepUrlTpl"];
			}
			
			$row["OutGoingUrl"] = strlen($row["DeepUrlTpl"]) ? $row["DeepUrlTpl"] : $row["AffDefaultUrl"];
			
			unset($row["p_AffDefaultUrl"]);
			unset($row["p_DeepUrlTpl"]);
			unset($row["fake_DeepUrlTpl"]);
			unset($row["fake_AffDefaultUrl"]);
			
			$row["LastUpdateTime"] = $update_time;
			
			
			if(!empty($row["Site"])){
				if($row["Site"] == 'global'){
					$row["Key"] = $row["Key"];
					$objRedis->set(":DOMAIN:".$row["Key"], json_encode($row));
					$update_key[$row["Key"]] = $row["Key"];
					
					/*if (SID == 'bdg02') {
						$row["Key"] = $row["Key"]. ":" ."CONT";
						$objRedis->set(":DOMAIN:" . $row["Key"], json_encode($row));
						$update_key[$row["Key"]] = $row["Key"];
					}*/
				}else{
					$row["Key"] = strtolower($row["Site"]).":".$row["Key"];
					$objRedis->set(":DOMAIN:".$row["Key"], json_encode($row));
					$update_key[$row["Key"]] = $row["Key"];
					
					/*if (SID == 'bdg02') {
						$row["Key"] = $row["Key"]. ":" ."CONT";
						$objRedis->set(":DOMAIN:" . $row["Key"], json_encode($row));
						$update_key[$row["Key"]] = $row["Key"];
					}*/
				}
				$i++;
				$j++;
			}
		}
	}
}

echo "site_other finish($j)\r\n";



/*
 * 
 * 
 * DB in REDIS
 * 
 * # [:ACCOUNT:{$accountapi}]	publisher account info FROM db:publisher_account
 * 								{apikey:, accountid:, publisherid:, status:,} 
 * 
 * 
 * # [:DOMAIN:{$domain}]		domain out going info FROM db:domain_outgoing_default
 * 								{domain:, pid:, did:, affid:, idinaff:, url_type: 'default_url'|'deep_url', outgoingurl:, commission_info:{}, key:, limitaccount:, }
 * 								
 * 
 * # [:AFF:{$affid}] 			affiliate info FROM db:wf_aff | in future db:affiliate
 * 								{affid:, affname:, affdomain:{}, affurl_para:{}, aff_deep_para:{}, aff_subtracking_para:{}, aff_sid:{}, aff_type:'NEWWORK'|'INHOUSE', aff_status:, }
 * 
 * 
 * # [:DOMAIN:{$aff_domain}] 	affiliate out going info FROM db:wf_aff 
 * 								{domain:, affid:, outgoingurl:, isaffurl: 'yes', limitaccount:, }
 * 								if 
 * 									desturl is affurl
 * 								then
 * 									add affurl subtracking 
 * 
 * 
 * 
 */
//$sql = "SELECT a.id AS accountid, a.publisherid, a.apikey, a.`name`, a.`status`, p.`status` AS p_status, a.alias, b.sitetype FROM publisher_account a INNER JOIN publisher p ON a.publisherid = p.id LEFT JOIN publisher_detail b ON a.publisherid = b.publisherid";
$sql = "SELECT a.id AS accountid, a.publisherid, a.apikey, a.`name`, a.`status`, p.`status` AS p_status, a.alias, a.siteoption FROM publisher_account a INNER JOIN publisher p ON a.publisherid = p.id";
$account_arr = array();
$account_arr = $objProgram->objMysql->getRows($sql);
$ii = 0;
foreach($account_arr as $v){
	//{apikey:, accountid:, publisherid:, status:, siteidinaff} 
	
	if($v['status'] == 'Active' && $v['p_status'] == 'Active'){
		$row = array();
		$row["apikey"] = $v["apikey"];
		$row["accountid"] = $v["accountid"];
		$row["publisherid"] = $v["publisherid"];
		$row["status"] = $v["status"];
		$row["alias"] = $v["alias"];
		$row["LastUpdateTime"] = $update_time;
		
		/*if(stripos($v["sitetype"], 'c') !== false){
			$row["isloyalty"] = '1';
		}else{
			$row["isloyalty"] = '0';
		}
		
		if(stripos($v["sitetype"], 'e') !== false){
			$row["iscoupon"] = '1';
		}else{
			$row["iscoupon"] = '0';
		}*/
		
		if($v["siteoption"] == 'Promotion'){
			$row["iscoupon"] = '1';
		}else{
			$row["iscoupon"] = '0';
		}
		
		$row["isloyalty"] = '0';
			
		$objRedis->set(":ACCOUNT:".$v["apikey"], json_encode($row));
	}else{
		$objRedis->del(":ACCOUNT:".$v["apikey"]);
		$ii++;
	}
	
	
	//$xx = $objRedis->get(":ACCOUNT:".$v["apikey"]);	
	//print_r($row);print_r($xx);exit;
}
echo "DEL ACCOUNT: ($ii)\r\n";	

$aff_domain_pattern_arr = array();
$aff_sid_arr = array();

$sql = "SELECT id, `name`, affiliateurlkeywords AS aff_redirect_domain, affiliateurlkeywords2 AS aff_sid, deepurlparaname, subtracking, isactive, supportdeepurl, supportsubtracking, isinhouse, isactive FROM wf_aff WHERE isactive = 'yes' order by id desc";
$aff_arr = array();
$aff_arr = $objProgram->objMysql->getRows($sql, "id");
foreach($aff_arr as $v){
	//{affid:, affname:, aff_domain:{}, affurl_para:{}, aff_deep_para:{}, aff_subtracking_para:{}, aff_sid:{}, aff_type:'NEWWORK'|'INHOUSE', aff_status:, }
	$row = array();
	$row["id"] = $v["id"];
	$row["aff_name"] = $v["name"];
	$row["type"] = ($v["isinhouse"] == "YES") ? "inhouse" : "newwork"; 
	$row["status"] = ($v["isactive"] == "YES") ? "active" : "inactive";
	$v["aff_redirect_domain"] = explode("\r\n", $v["aff_redirect_domain"]);
	$row["aff_domain"] = json_encode($v["aff_redirect_domain"]);
	
	$row["aff_deepurl_para"] = $v["deepurlparaname"];
	$row["aff_subtracking_para"] = preg_replace("/=.*/", "", $v["subtracking"]);
	
	//some account sid different
	$row["aff_sid"] = json_encode(explode("\r\n", $v["aff_sid"]));
	$aff_sid_arr[$v["id"]] = explode("\r\n", $v["aff_sid"]);
	
	if (SID == 'bdg01') {
		$sql = "SELECT affid, accountid, siteidinaff, `status` FROM aff_siteid WHERE `status` = 'active' and affid = {$v["id"]}";
		$aff_siteid_arr = array();
		$aff_siteid_arr = $objProgram->objMysql->getRows($sql);
		$tmp_arr = array();
		foreach($aff_siteid_arr as $vv){
			$tmp_arr[$vv["accountid"]] = $vv["siteidinaff"];
		}
		$row["aff_siteidinaff"] = json_encode($tmp_arr);
	}
	$row["LastUpdateTime"] = $update_time;
	
	//print_r($row);
	$objRedis->set(":AFF:".$v["id"], json_encode($row));
	
	//echo "set :AFF:".$v["id"]." '".json_encode($row)."'\r\n";
	
	//$row["aff_account_sid"] = $v["id"];
	
	foreach($v["aff_redirect_domain"] as $aff_out_domain){
		//{domain:, affid:, outgoingurl:, isaffurl: 'yes', limitaccount:, }
		if($aff_out_domain){
			$tmp_domain_arr = $objProgram->getDomainByHomepage($aff_out_domain);
			foreach($tmp_domain_arr as $tmp_domain){
				if($tmp_domain && ($tmp_domain != $aff_out_domain)){
					$aff_domain_row = array();
					$aff_domain_row["domain"] = $tmp_domain;
					$aff_domain_row["AffId"] = $v["id"];
					$aff_domain_row["isaffurl"] = "yes";
					$aff_domain_row["limitaccount"] = "";
					$aff_domain_row["outgoingurl"] = "";
					$aff_domain_row["LastUpdateTime"] = $update_time;
					
					if($v["isinhouse"] == "YES"){
						if(SID != 'bdg02'){
							foreach($ss_ite as $s_site){
								if(!empty($s_site)) $s_site .= ":";
								$tmp_arr = array();
								$tmp_arr = $objRedis->get(":DOMAIN:".$s_site.$aff_domain_row["domain"]);
								
								if($tmp_arr == false){
									$objRedis->set(":DOMAIN:".$s_site.$aff_domain_row["domain"], json_encode($aff_domain_row));								
									
									$update_key[$s_site.$aff_domain_row["domain"]] = $s_site.$aff_domain_row["domain"];								
								}else{
									$update_key[$s_site.$aff_domain_row["domain"]] = $s_site.$aff_domain_row["domain"];
								}
							}
						}
					}else{
						$objRedis->set(":DOMAIN:".$aff_domain_row["domain"], json_encode($aff_domain_row));
						$update_key[$aff_domain_row["domain"]] = $aff_domain_row["domain"];
						
						foreach($ss_ite as $s_site){
							if(empty($s_site)) continue;
							$objRedis->set(":DOMAIN:"."{$s_site}:".$aff_domain_row["domain"], json_encode($aff_domain_row));
							$update_key["{$s_site}:".$aff_domain_row["domain"]] = "{$s_site}:".$aff_domain_row["domain"];
						}						
						
						$aff_domain_pattern_arr[$v["id"]][$aff_domain_row["domain"]] = $aff_domain_row;
					}
				}
			}
								
			$aff_domain_row = array();
			$aff_domain_row["domain"] = $aff_out_domain;
			$aff_domain_row["AffId"] = $v["id"];
			$aff_domain_row["isaffurl"] = "yes";
			$aff_domain_row["limitaccount"] = "";
			$aff_domain_row["outgoingurl"] = "";
			$aff_domain_row["LastUpdateTime"] = $update_time;
			
			if($v["isinhouse"] == "YES"){
				if(SID != 'bdg02'){
					foreach($ss_ite as $s_site){
						if(!empty($s_site)) $s_site .= ":";
						$tmp_arr = array();
						$tmp_arr = $objRedis->get(":DOMAIN:".$s_site.$aff_domain_row["domain"]);
						
						if($tmp_arr == false){
							$objRedis->set(":DOMAIN:".$s_site.$aff_domain_row["domain"], json_encode($aff_domain_row));								
							
							$update_key[$s_site.$aff_domain_row["domain"]] = $s_site.$aff_domain_row["domain"];								
						}else{						
							if(isset($update_key[$s_site.$aff_domain_row["domain"]])){
								$update_key[$s_site.$aff_domain_row["domain"]] = $s_site.$aff_domain_row["domain"];
							}else{							
								unset($update_key[$s_site.$aff_domain_row["domain"]]);
							}
						}
					}
				}
			}else{				
				$objRedis->set(":DOMAIN:".$aff_domain_row["domain"], json_encode($aff_domain_row));
				$update_key[$aff_domain_row["domain"]] = $aff_domain_row["domain"];
				
				foreach($ss_ite as $s_site){
					if(empty($s_site)) continue;
					$objRedis->set(":DOMAIN:"."{$s_site}:".$aff_domain_row["domain"], json_encode($aff_domain_row));
					$update_key["{$s_site}:".$aff_domain_row["domain"]] = "{$s_site}:".$aff_domain_row["domain"];
				}
				
				$aff_domain_pattern_arr[$v["id"]][$aff_domain_row["domain"]] = $aff_domain_row;
			}
			
			
			//echo "set :DOMAIN:".$aff_out_domain." '".json_encode($aff_domain_row)."'\r\n";
			
		}
	}	
	
}




$tmp_redis = array();
$tmp_redis = $objRedis->keys(":AFF_KEY:*");
foreach($tmp_redis as $v){
	$objRedis->del($v);
}
//special never blue = five [a-zA_Z]{5}\.com + a=251165 , My Help Hub = afl=77881 , medialead = mlpid=2311
//TradeTracker DE tt= + 62862
//$special_aff = array_flip(array(123, 80, 423, 425, 426, 427, 52, 65));
$special_aff = array_flip(array(425, 426, 427, 52, 65, 2026, 2027, 2028, 2029, 2054));

//print_r($aff_sid_arr[123]);
$re_domain = array();
foreach($aff_domain_pattern_arr as $affid => $v){
	if(isset($special_aff[$affid])){
		if(in_array($affid, array(425, 426, 427, 52, 65, 2026, 2027, 2028, 2029, 2054))){
			$re_domain["tradetracker.net"] = "tradetracker\.net";
			
			$objRedis->set(":AFF_KEY:"."tradetracker.net", json_encode($v["tradetracker.net"]));
			foreach($aff_sid_arr[$affid] as $sid){
				$re_domain[$sid] = "(?:[?|&]tt=[0-9_]+)?(".trim($sid).")";
				
				$objRedis->set(":AFF_KEY:".$sid, json_encode($v["tradetracker.net"]));
			}			
		}
		continue;
	}
	foreach($v as $domain => $aff_domain_row){
		if($domain){
			$domain = trim($domain, ".");
			$domain = trim($domain);
			$domain = strtolower($domain);
			
			$k = $domain;
			
			$domain = str_ireplace(".", "\.", $domain);
			$domain = str_ireplace("/", "\/", $domain);
			$domain = str_ireplace("?", "\?", $domain);
			$domain = str_ireplace(":", "\:", $domain);
			$domain = "\.?(".$domain.")";
			
			$re_domain[$k] = $domain;
			$objRedis->set(":AFF_KEY:".$k, json_encode($aff_domain_row));
		}
	}
}



//print_r($re_domain);
$re = "/https?:\/\/.*(".implode("|", $re_domain).")/i";
$objRedis->set(":AFF_PATTERN:", $re);
//print_r($aff_domain_pattern_arr);

/*foreach($re_domain as $k => $v){
	$objRedis->set(":AFF_KEY:".$k, json_encode($v));
}*/
echo "aff finish\r\n";

//echo ":DOMAIN::".count($objRedis->keys(":DOMAIN:*"))."\r\n";


if(!$is_debug && !$is_quick){
	$j = 0;
	$del_key = array();
	$tmp_redis = array();
	$tmp_redis = $objRedis->keys(":DOMAIN:*");	
	foreach($tmp_redis as $v){
		if(!isset($update_key[str_ireplace(":DOMAIN:", "", $v)])){
			//if(strpos($v, "/") === false){
				$del_key[$v] = "1";
			//}
		}
	}
	$j_cnt = count($del_key);
	if($j_cnt > 50000){
		echo "Del Redis Key Warning, ($j_cnt) [";
		$domain_arr = array();
		foreach($del_key as $k => $v){
			$tm_k = substr($k, 11);
			$domain_arr[$tm_k] = 1;
		//      echo $k."]\r\n";
		}
		print_r($domain_arr);
		$domain_cnt = count($domain_arr);
		$to = "stanguan@meikaitech.com";
		AlertEmail::SendAlert('Del Redis Key Warning',nl2br("Del Redis Key Warning, ($j_cnt), domain ($domain_cnt)"), $to);
		exit;
		
	}else{
		foreach($del_key as $k => $v){
			$objRedis->del($k);
			$j++;
			/*if($j < 10){
				echo "\t".$v."\r\n";
			}*/
			/*if(empty($vv)){
				$vv = $v;
				echo $vv;
			}*/
		}			
	}
	unset($del_key);
	unset($tmp_redis);
	echo "del old finish($j)\r\n";
}


//add Has Aff domain info
$k = 0;
/*$sql = "SELECT a.id, a.domain, GROUP_CONCAT(DISTINCT b.site) as country FROM domain a INNER JOIN domain_outgoing_default_other b ON a.id = b.did GROUP BY a.id";
$tmp_arr = array();
$tmp_arr = $objProgram->objMysql->getRows($sql, "domain");
foreach($tmp_arr as $v){
	//$objRedis->set(":D:".$v["domain"], json_encode($v));
	$objRedis->set(":D:".$v["domain"], $v['country']);
	$k++;
}*/
$sql = "SELECT id, domain FROM domain ";
$tmp_arr = array();
$tmp_arr = $objProgram->objMysql->getRows($sql, "domain");
foreach($tmp_arr as $v){
	$objRedis->set(":D:".$v["domain"], json_encode($v));		
	$k++;
}
echo "Add Domain :($k)\r\n";
$k = 0;
$tmp_redis = array();
$tmp_redis = $objRedis->keys(":D:*");
foreach($tmp_redis as $v){
	if(!isset($tmp_arr[str_replace(':D:', '', $v)])){
		$objRedis->del($v);
		$k++;
	}
}
echo "DEL Domain :($k)\r\n";


//add active main program
//[sourceid]-[affid]-[idinaff]
//mk=1;br=2
if(SID == 'bdg02'){
	$sourceid = 2;
}else{
	$sourceid = 1;
}
$k = 0;
$tmp_key = array();
$sql = "select programid, affid, idinaff from program_intell where isactive = 'active' and affid NOT IN (191,223,578)"; //$this->sub_aff = array(160,191,223,237,578,639,652,656);
$tmp_arr = array();
$tmp_arr = $objProgram->objMysql->getRows($sql, 'programid');
foreach($tmp_arr as $v){
	$tmp_key[$sourceid.'-'.$v["affid"].'-'.$v["idinaff"]] = 1;
	$objRedis->set(":P:".$sourceid.'-'.$v["affid"].'-'.$v["idinaff"], 1);
	$k++;
}
echo "Add active P:($k)\r\n";
$k = 0;
$tmp_redis = array();
$tmp_redis = $objRedis->keys(":P:*");
foreach($tmp_redis as $v){
	if(!isset($tmp_key[str_replace(':P:', '', $v)])){
		$objRedis->del($v);
		$k++;
	}
}
echo "DEL inactive P:($k)\r\n";
unset($tmp_arr);



//advertiser restriction
$k = 0;
$sql = "SELECT c.id AS domainid, c.domain, b.SupportType FROM r_store_domain a INNER JOIN store b ON a.storeid = b.id INNER JOIN domain c ON a.domainid = c.id WHERE b.SupportType = 'Content' OR b.SupportType = 'None'";
$tmp_arr = array();
$tmp_arr = $objProgram->objMysql->getRows($sql, "domainid");
foreach($tmp_arr as $v){
	//if($v['supportcoupon'] == 'NO'){
		$objRedis->set(":RES_C:".$v["domainid"], 1);
		$k++;
	//}elseif($v['supportloyalty'] == 'NO'){
		//$objRedis->set(":RES_L:".$v["domainid"], 1);
		//$k++;
	//}
}
echo "Add RES_C+RES_L :($k)\r\n";
$k = 0;
$tmp_redis = array();
$tmp_redis = $objRedis->keys(":RES_C:*");
foreach($tmp_redis as $v){
	if(!isset($tmp_arr[str_replace(':RES_C:', '', $v)]) || $tmp_arr[str_replace(':RES_C:', '', $v)]['SupportType'] != 'Content'){
		$objRedis->del($v);
		$k++;
	}
}
echo "DEL RES_C :($k)\r\n";
$k = 0;
$tmp_redis = array();
$tmp_redis = $objRedis->keys(":RES_L:*");
foreach($tmp_redis as $v){
	if(!isset($tmp_arr[str_replace(':RES_L:', '', $v)]) || $tmp_arr[str_replace(':RES_L:', '', $v)]['SupportType'] != 'None'){
		$objRedis->del($v);
		$k++;
	}
}
echo "DEL RES_L :($k)\r\n";


//store
$k = 0;
$tmp_key = array();

$sql = "SELECT a.accountid, a.objid AS storeid, b.domainid FROM `block_relationship` a INNER JOIN r_store_domain b ON a.objid = b.storeid WHERE a.status = 'active' AND a.objtype = 'store' AND a.accounttype = 'accountid'";
$tmp_arr = array();
$tmp_arr = $objProgram->objMysql->getRows($sql);	
foreach($tmp_arr as $v){
	$tmp_key[$v["domainid"].":".$v["accountid"]] = 1;
	$objRedis->set(":RES_S:".$v["domainid"].":".$v["accountid"], 1);
	$k++;
}

$sql = "SELECT c.id as accountid, a.objid AS storeid, b.domainid FROM `block_relationship` a INNER JOIN r_store_domain b ON a.objid = b.storeid inner join publisher_account c on a.accountid = c.publisherid WHERE a.status = 'active' AND a.objtype = 'store' AND a.accounttype = 'publisherid'";
$tmp_arr = array();
$tmp_arr = $objProgram->objMysql->getRows($sql);	
foreach($tmp_arr as $v){
	$tmp_key[$v["domainid"].":".$v["accountid"]] = 1;
	$objRedis->set(":RES_S:".$v["domainid"].":".$v["accountid"], 1);
	$k++;
}


//affiliate || temp need edit 
$sql = "SELECT a.accountid, c.did as domainid FROM `block_relationship` a INNER JOIN program b ON a.objid = b.id INNER JOIN r_domain_program c ON b.id = c.pid WHERE a.status = 'active' AND c.status = 'active' AND a.blockby = 'affiliate' AND a.objtype = 'program' AND a.accounttype = 'accountid'";
$tmp_arr = array();
$tmp_arr = $objProgram->objMysql->getRows($sql);	
foreach($tmp_arr as $v){
	$tmp_key[$v["domainid"].":".$v["accountid"]] = 1;
	$objRedis->set(":RES_S:".$v["domainid"].":".$v["accountid"], 1);
	$k++;
}

$sql = "SELECT d.id as accountid, c.did as domainid FROM `block_relationship` a INNER JOIN program b ON a.objid = b.id INNER JOIN r_domain_program c ON b.id = c.pid inner join publisher_account d on a.accountid = d.publisherid  WHERE a.status = 'active' AND c.status = 'active' AND a.blockby = 'affiliate' AND a.objtype = 'program' AND a.accounttype = 'publisherid'";
$tmp_arr = array();
$tmp_arr = $objProgram->objMysql->getRows($sql);	
foreach($tmp_arr as $v){
	$tmp_key[$v["domainid"].":".$v["accountid"]] = 1;
	$objRedis->set(":RES_S:".$v["domainid"].":".$v["accountid"], 1);
	$k++;
}

//print_r($tmp_key);
echo "Add RES_S :($k)\r\n";
$k = 0;
$tmp_redis = array();
$tmp_redis = $objRedis->keys(":RES_S:*");
foreach($tmp_redis as $v){		
	if(!isset($tmp_key[str_replace(':RES_S:', '', $v)])){
		$objRedis->del($v);
		$k++;
	}		
}	
echo "DEL RES_S :($k)\r\n";


//block by affiliate
$sql = "SELECT accountid, objid as affid FROM `block_relationship` WHERE status = 'active' AND objtype = 'Affiliate' and accounttype = 'accountid'";
$tmp_arr = array();
$tmp_arr = $objProgram->objMysql->getRows($sql);	
foreach($tmp_arr as $v){
	$tmp_key[$v["accountid"].":".$v["affid"]] = 1;
	$objRedis->set(":BLOCK:".$v["accountid"].":".$v["affid"], 1);
	$k++;
}
$sql = "SELECT b.id as accountid, a.objid as affid FROM `block_relationship` a inner join publisher_account b on a.accountid = b.publisherid WHERE a.status = 'active' AND objtype = 'Affiliate' and accounttype = 'publisherid'";
$tmp_arr = array();
$tmp_arr = $objProgram->objMysql->getRows($sql);	
foreach($tmp_arr as $v){
	$tmp_key[$v["accountid"].":".$v["affid"]] = 1;
	$objRedis->set(":BLOCK:".$v["accountid"].":".$v["affid"], 1);
	$k++;
}
//print_r($tmp_key);
echo "Add BLOCK :($k)\r\n";
$k = 0;
$tmp_redis = array();
$tmp_redis = $objRedis->keys(":BLOCK:*");
foreach($tmp_redis as $v){		
	if(!isset($tmp_key[str_replace(':BLOCK:', '', $v)])){
		$objRedis->del($v);
		$k++;
	}		
}	
echo "DEL BLOCK :($k)\r\n";


//print_r($xx);
echo "size:".$objRedis->dbSize()."\t";
echo ":ACCOUNT::".count($objRedis->keys(":ACCOUNT:*"))."\t";
echo ":AFF::".count($objRedis->keys(":AFF:*"))."\t";
echo ":DOMAIN::".count($objRedis->keys(":DOMAIN:*"))."\r\n";

echo "<< End @$i|$j ".date("Y-m-d H:i:s")." >>\r\n";
exit;



?>