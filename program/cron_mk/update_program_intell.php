<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
include_once(INCLUDE_ROOT . "func/func.php");
include_once(INCLUDE_ROOT . "func/nodejs.php");

echo "<< Start @ ".date("Y-m-d H:i:s")." >>\r\n";

$id_arr = array();
$is_debug = false;
$pid = "";
$is_fast = $nottoredis = $onlyactive = $debug_sql = $forcecc = false;
$in_house = false;
if(isset($_SERVER["argc"]) && $_SERVER["argc"] > 1)
{
	foreach($_SERVER["argv"] as $v){
		$tmp = explode("=", $v);		
		if($tmp[0] == "--affid"){			
			$id_arr = explode(",", $tmp[1]);
		}elseif($tmp[0] == "--debug"){
			$is_debug = true;
		}elseif($tmp[0] == "--pid"){
			$pid = " and id in (" . trim($tmp[1], ",") . ")";
		}elseif($tmp[0] == "--fast"){
			$is_fast = true;
		}elseif($tmp[0] == "--nottoredis"){
			$nottoredis = true;
		}elseif($tmp[0] == "--onlyactive"){
			$onlyactive = true;
		}elseif($tmp[0] == "--in_house"){
			$in_house = true;
		}elseif($tmp[0] == "--sql"){
			$debug_sql = true;
		}elseif($tmp[0] == "--forcecc"){
			$forcecc = true;
		}
	}			
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
/*
 * #AffDefaultUrl
 * 1, provided direct by program. no need select from links
 * 2, has regular pattern. no need select from links
 * 3, select from links
 * 
 * 
 * #DeepUrlTpl		mostly base on AffDefaultUrl
 * 1, provided direct by program. no need select from links
 * 2, has regular pattern. no need select from links
 * 3, select from links
 * 4, not support 
 * 
 */

$tmp_skimlinks_nocomm = array();

$date = date("Y-m-d H:i:s");

$objProgram = New Program();
$objProgram->objMysql->query('SET NAMES latin1');

$affiliate_list = $objProgram->getAllAffiliate($id_arr);
$aff_domain = array();
foreach($affiliate_list as $v){
	if($v["IsInHouse"] != "NO" || !strlen($v["AffiliateUrlKeywords"])) continue;
	$tmp_arr = explode("\r\n", $v["AffiliateUrlKeywords"]);
	foreach($tmp_arr as $vv){
		$tmp_domain = array();
		$tmp_domain = $objProgram->getDomainByHomepage($vv, "fi");
		//print_r($domain_arr);
		if(count($tmp_domain)){
			$domain = current($tmp_domain["domain"]);
			if(!empty($domain))$aff_domain[$domain] = $domain;
		}
	}
}
//$aff_url_pattern = $objProgram->getAffUrlPattern();

//$country_code = implode("|", array_keys($objProgram->country_rel));

$sql = "select lower(countrycode) as countrycode, lower(countryname) as countryname, countrykeywords as countrykeywords from country_codes where countrystatus = 'on'";
$tmp_arr = $objProgram->objMysql->getRows($sql);
$country_name_code = array();
foreach($tmp_arr as $v){
	$v['countryname'] = str_replace(".", "\.", $v['countryname']);
	$country_name_code[$v['countryname']] = $v['countrycode'];
	
	if(!empty($v['countrykeywords'])){
		$v['countrykeywords'] = strtolower($v['countrykeywords']);
		$tmp = explode("|", $v['countrykeywords']);
		foreach($tmp as $tmp_v){
			$tmp_v = str_replace(".", "\.", $tmp_v);
			$country_name_code[$tmp_v] = $v['countrycode'];
		}
	}
}
$country_name_pattern = implode("|", array_keys($country_name_code));

//$country_code = implode("|", $country_name_code);
$country_code = trim(str_replace(",", "|", $objProgram->global_c), "|");

$aff_keyword = array();

$non_comm_format = array();
$j = 0;
$aff_marketing = array();
$sql = "SELECT id, lower(MarketingContinent) as MarketingContinent, lower(MarketingCountry) as MarketingCountry FROM wf_aff WHERE IsActive = 'yes' AND ( MarketingContinent <> '' OR MarketingCountry <> '')";			
$aff_marketing = $objProgram->objMysql->getRows($sql, "id");

$sql = "select affid, programid, manualstatus, domainfixed, SupportDeepUrlOut, shippingcountry, url from program_internal";
$manual_program = array();
$manual_program = $objProgram->objMysql->getRows($sql, "programid");

$sql = "select programid, statusinbdg, realdomain, CommissionUsed, CommissionCurrency, CommissionType from program_manual";
$manual_program_2 = array();
$manual_program_2 = $objProgram->objMysql->getRows($sql, "programid");

foreach($affiliate_list as $affid => $aff_v){
	//echo "AffId($affid) start\r\n";
	$i = 0;
	
	$max = 0;
	
	if($aff_v["AffiliateUrlKeywords"]){
		$aff_keyword[$affid] = explode("\r\n", $aff_v["AffiliateUrlKeywords"]);
	}
	
	$hasCommFormatFunc = false;
	$LinkFeedName = 'LinkFeed_'.$affid;
	$class_file = INCLUDE_ROOT . 'lib/aff/Class.' . $LinkFeedName . '.php';
	if(file_exists($class_file)){
		include_once($class_file);
		$objBDG = new $LinkFeedName;
		$hasCommFormatFunc = true;
	}
	
	$update_sql = array();	
//	echo "AffId($affid) start";
//	if($affid == 191){
//		echo "ignore viglink(191)\r\n";
//		continue;
//	}
	
	$check_name_country = false;
	if(SID=='bdg02') {
		$check_name_country = true;
	}
	
	$this_aff_allow_country = array();
	if(isset($aff_marketing[$affid])){
		if($aff_marketing[$affid]["MarketingContinent"] == "global"){
			$check_name_country = true;
		}elseif(!empty($aff_marketing[$affid]["MarketingContinent"])){
			if(empty($aff_marketing[$affid]["MarketingCountry"])){
				foreach($objProgram->country_rel as $country => $continent){
					if($aff_marketing[$affid]["MarketingContinent"] == $continent)
						$this_aff_allow_country[$country] = $country;
				}
			}else{
				$this_aff_allow_country[] = $aff_marketing[$affid]["MarketingCountry"];
			}
		}		
	}
	$check_ps = false;
	
	$checked_p = array();
	
	unset($objProgram->old_ps);
	
	while(!$check_ps)
	{
		$logid_list = $pid_list = array();
		$log_p = array();
		$log_p = $objProgram->getNewChangedProgramInfoByAff($affid, 100);			
		
		foreach($log_p as $v){
			$logid_list[$v["logid"]] = $v["logid"];
			if(!isset($checked_p[$v["pid"]])){
				$pid_list[$v["pid"]] = $v["pid"];
				$checked_p[$v["pid"]] = 1;
			}
		}
//		echo "\r\n[".count($log_p)."]\r\n";
		if(count($logid_list)) $logid_list = implode(",", $logid_list);
		else $logid_list = "";
		
		if(count($pid_list)) $pid_list = implode(",", $pid_list);
		else $pid_list = "";
		
		if(!strlen($pid_list) || !strlen($logid_list)){
			if($affid == 639){
				echo "ignore digdip\r\n";
				$check_ps = true;
				continue;
			}
			
			$pid_list = array();
			
			$log_p = array();
			$sql = "select a.pid from r_domain_program a inner join program b on a.pid = b.id where b.affid = $affid and a.LastUpdateTime >= '".date("Y-m-d H:i:s", strtotime(" -5 minutes"))."'";
			$log_p = $objProgram->objMysql->getRows($sql, "pid");
			if(count($log_p))
				$pid_list = array_keys($log_p);
			
			$log_p = array();
			$sql = "select DISTINCT a.ProgramId AS pid from program_manual a inner join program_intell b on a.programid = b.programid where b.affid = $affid and a.LastUpdateTime >= '".date("Y-m-d H:i:s", strtotime(" -5 minutes"))."'";
			$log_p = $objProgram->objMysql->getRows($sql, "pid");
			if(count($log_p))
				$pid_list = array_merge($pid_list ,array_keys($log_p));
			
			$log_p = array();
			$sql = "select a.id as pid from program a where a.affid = $affid and (a.AddTime >= '".date("Y-m-d H:i:s", strtotime(" -5 minutes"))."' or a.LastUpdateTime >= '".date("Y-m-d H:i:s", strtotime(" -5 minutes"))."')";
			$log_p = $objProgram->objMysql->getRows($sql, "pid");
			if(count($log_p))
				$pid_list = array_merge($pid_list ,array_keys($log_p));
			
			//not in program_intell
			if($affid != 191){
				$log_p = array();
//				$sql = "select a.`ID` pid from program a left join program_intell b on a.`ID`=b.`ProgramId` where a.`AffId` = $affid and b.`ProgramId` is null";
				$sql = "select a.id as pid from program a where a.affid = $affid and a.id not in (select programid from program_intell where affid = $affid)";
				$log_p = $objProgram->objMysql->getRows($sql, "pid");			
				if(count($log_p))
					$pid_list = array_merge($pid_list ,array_keys($log_p));
			}
			
			//isactive not correct
			$log_p = array();
			$sql = "SELECT a.programid FROM program_intell a INNER JOIN program b ON a.programid = b.id WHERE a.affid = '$affid' AND a.isactive = 'active' AND (b.statusinaff <> 'active' OR b.partnership <> 'active') AND a.programid NOT IN (SELECT programid FROM program_manual WHERE statusinbdg = 'active')";
			$log_p = $objProgram->objMysql->getRows($sql, "programid");
			if(count($log_p))
				$pid_list = array_merge($pid_list ,array_keys($log_p));
			
			$log_p = array();
			$sql = "SELECT a.programid FROM program_intell a INNER JOIN program b ON a.programid = b.id LEFT JOIN program_manual c ON a.programid = c.programid WHERE a.affid = '$affid' and a.isactive = 'inactive' AND b.statusinaff = 'active' AND b.partnership = 'active' AND c.statusinbdg <> 'inactive'";
			$log_p = $objProgram->objMysql->getRows($sql, "programid");
			if(count($log_p))
				$pid_list = array_merge($pid_list ,array_keys($log_p));
			
			//check statusinaff partnership, & isactive
			$log_p = array();
			$sql = "SELECT a.id FROM program a INNER JOIN program_intell b ON a.id = b.programid WHERE a.statusinaff = 'active' AND a.partnership = 'active' AND b.isactive = 'inactive' and a.affid = '$affid'";
			$log_p = $objProgram->objMysql->getRows($sql, "id");
			if(count($log_p))
				$pid_list = array_merge($pid_list ,array_keys($log_p));
				

			$log_p = array();
			$sql = "SELECT a.programid FROM program_manual a INNER JOIN program_intell b ON a.programid = b.programid AND b.isactive = 'active' AND a.statusinbdg = 'inactive' and b.affid = '$affid'";
			$log_p = $objProgram->objMysql->getRows($sql, "programid");
			if(count($log_p))
				$pid_list = array_merge($pid_list ,array_keys($log_p));
				
				
			$pid_list = array_unique($pid_list);
			//print_r($pid_list);
			foreach($pid_list as $tmp_k => $tmp_pid){
				if(!isset($checked_p[$tmp_pid])){					
					$checked_p[$tmp_pid] = 1;
				}else{
					unset($pid_list[$tmp_k]);
				}
			}
			
			if(count($pid_list)) $pid_list = implode(",", $pid_list);
			else $pid_list = "";
			
			$check_ps = true;
		}
		
		if(!(strlen($pid_list) && (strlen($logid_list) || $check_ps))) break;
				
		$prgm_arr = array();
		if($pid_list){
			if(SID == 'bdg01'){
				$sql = "SELECT a.ID, a.`Name`, a.Description, a.AffId, a.IdInAff, a.AffDefaultUrl, a.Partnership, a.StatusInAff, a.CommissionExt, a.Homepage, a.SupportDeepUrl, a.TargetCountryExt, a.TargetCountryInt, a.TargetCountryIntOld, a.SecondIdInAff FROM program a left join program_manual b on a.`ID` = b.`ProgramId` WHERE a.ID in ($pid_list)";
			}else{
				$sql = "SELECT a.ID, a.`Name`, a.Description, a.AffId, a.IdInAff, a.AffDefaultUrl, a.Partnership, a.StatusInAff, a.CommissionExt, a.Homepage, a.SupportDeepUrl, a.TargetCountryExt, b.TargetCountryInt, a.TargetCountryIntOld, a.SecondIdInAff FROM program a left join program_manual b on a.`ID` = b.`ProgramId` WHERE a.ID in ($pid_list)";
			}
			$prgm_arr = $objProgram->objMysql->getRows($sql, "ID");
		}
		
		if(SID == 'bdg01'){
			$sql = "select a.ProgramId, a.HomepageInt, b.ShippingCountry, b.IsActive, b.Domain, b.Order, b.LastChangeTime FROM program_int a left join program_intell b on a.programid = b.programid WHERE a.ProgramId in ($pid_list)";
		}else{
			$sql = "select a.ProgramId, a.HomepageInt, b.ShippingCountry, b.IsActive, b.Domain, b.Order, b.LastChangeTime, b.SupportType FROM program_int a left join program_intell b on a.programid = b.programid WHERE a.ProgramId in ($pid_list)";
		}
		$prgm_int = array();
		$prgm_int = $objProgram->objMysql->getRows($sql, "ProgramId");
		
		//echo "||".count($prgm_arr)."||";
		foreach($prgm_arr as $p_v){
			//$time_1 = time();	
			$isactive = ($p_v["StatusInAff"] == "Active" && $p_v["Partnership"] == "Active") ? "Active" : "Inactive";
			if($affid == 223 && $isactive == "Active" && in_array($p_v["ID"] , $tmp_skimlinks_nocomm)){
				$isactive = 'Inactive';
			}
			if(isset($manual_program_2[$p_v["ID"]])){
				if($manual_program_2[$p_v["ID"]]["statusinbdg"] == "Active"){			
					$isactive = 'Active';
				}elseif($manual_program_2[$p_v["ID"]]["statusinbdg"] == "Inactive"){
					$isactive = 'Inactive';
				}
			}
			
			if($isactive == 'Inactive'){
				if($prgm_int[$p_v["ID"]]["IsActive"] != 'Inactive'){
					$tmp_sql[$p_v["ID"]] = array("ProgramId" => $p_v["ID"], 
												"AffId" => $p_v["AffId"], 
												"IdInAff" => $p_v["IdInAff"], 													
												"IsActive" => $isactive,													
												"LastUpdateTime" => date("Y-m-d H:i:s")													
											);
					$objProgram->insertProgramIntell($tmp_sql);
				}else{
					continue;
				}
			}
			
			if(isset($prgm_int[$p_v["ID"]])){
				if(!empty($prgm_int[$p_v["ID"]]["HomepageInt"])){			
					$p_v["Homepage"] = $prgm_int[$p_v["ID"]]["HomepageInt"];
				}
				if(isset($prgm_int[$p_v["ID"]]["ShippingCountry"])){
					$p_v["ShippingCountry"] = $prgm_int[$p_v["ID"]]["ShippingCountry"];
				}
				else
					$p_v["ShippingCountry"] = '';
			}
			
			//print_r($p_v);
			$c_code = "";
			$shipping_country = "";
			$shipping_arr = $m = array();
			if($affid == 152 || $affid == 360){
				$country_name_pattern = preg_replace("/\s/", "", $country_name_pattern);
				foreach($country_name_code as $kkk => $vvv){
					$country_name_code_152[preg_replace("/\s/", "", $kkk)] = $vvv;
				}

				$m = explode(",", $p_v["TargetCountryExt"]);
				foreach($m as $cc){
					$cc = strtolower($cc);
					if(isset($country_name_code_152[$cc]))
						$shipping_arr[$country_name_code_152[$cc]] = $country_name_code_152[$cc];
					//$shipping_arr[$cc] = $cc;
				}
			}else{
				if(stripos($p_v["TargetCountryExt"], 'all Countries') !== false || trim($p_v["TargetCountryExt"]) == 'all'){
					$p_v["TargetCountryExt"] = 'global';
				}else{
					preg_match_all("/(?:[^a-zA-Z]|)($country_name_pattern)(?:[^a-zA-Z]|$)/i", $p_v["TargetCountryExt"], $m);
					//print_r($m);
					if(count($m) && !empty($m[1]) && is_array($m[1])){
						foreach($m[1] as $cc){
							$cc = strtolower($cc);
							$shipping_arr[$country_name_code[$cc]] = $country_name_code[$cc];
							//$shipping_arr[$cc] = $cc;
						}
					}
				}
			}
			
			if(!count($shipping_arr) && $affid != 2 && $affid != 10 && $affid != 2034){
				$m = array();
				preg_match_all("/(?:[^a-zA-Z]|)($country_code)(?:[^a-zA-Z]|$)/i", $p_v["TargetCountryExt"], $m);
				//print_r($m);
				if(count($m) && !empty($m[1]) && is_array($m[1])){
					foreach($m[1] as $cc){
						$cc = strtolower($cc);
						$shipping_arr[$cc] = $cc;
					}
				}
			}
			
			//print_r($shipping_arr);
			if(!count($shipping_arr) && $check_name_country && $affid != 152 && $affid != 360 && $affid != 557){
				/*if($affid == 10){
					$shipping_arr = array();
				}*/
				preg_match_all("/(?:[^a-zA-Z]|\s)($country_code)(?:[^a-zA-Z]|\s)/", $p_v["Name"]. " ", $m);
				if(count($m) && !empty($m[1]) && is_array($m[1])){
					foreach($m[1] as $cc){
						if($cc == strtoupper($cc)){
							$cc = strtolower($cc);
							$shipping_arr[$cc] = $cc;
						}
					}
				}else{
					preg_match("/[^a-zA-Z]+($country_name_pattern)(?:[^a-zA-Z]|$)/i", $p_v["Name"] . " ", $m);
					if(count($m) && !empty($m[1]) && isset($country_name_code[strtolower($m[1])])){
						$shipping_arr = array();
						$shipping_arr[$country_name_code[strtolower($m[1])]] = $country_name_code[strtolower($m[1])];
					}
				}
				
				if(empty($shipping_arr) && $affid == 223){
					preg_match("/($country_name_pattern)/i", $p_v["TargetCountryExt"], $m);
					//print_r($m);
					if(count($m) && !empty($m[1]) && isset($country_name_code[strtolower($m[1])])){
						$shipping_arr[$country_name_code[strtolower($m[1])]] = $country_name_code[strtolower($m[1])];
					}
				}
				
				if(empty($shipping_arr) && $affid == 762 &&  SID == 'bdg01'){
					$shipping_arr['uk'] = 'uk';
				}
			}
		
			//if($affid == 10 || $affid == 191){
			if($affid == 1 || $affid == 15 || $affid == 189 || $affid == 191){
				$tmp_arr = explode(",", strtolower($p_v['TargetCountryExt']));
				foreach($tmp_arr as $t_v){
					if(!empty($t_v)){
						$shipping_arr[$t_v] = $t_v;
					}
				}
				//$shipping_country = strtolower($p_v['TargetCountryExt']);
			}elseif($affid == 539 && $p_v['TargetCountryExt'] == 'uk'){
				preg_match_all("/(?:[^a-zA-Z]|\s)($country_code)(?:[^a-zA-Z]|\s)/", $p_v["Name"]. " ", $m);				
				if(count($m) && !empty($m[1]) && is_array($m[1])){
					$shipping_arr = array();
					foreach($m[1] as $cc){
						if($cc == strtoupper($cc)){
							$cc = strtolower($cc);
							$shipping_arr[$cc] = $cc;
						}
					}
				}
			}
			
			if(count($shipping_arr)) $shipping_country = strtolower(implode(",", $shipping_arr));
			
			if(empty($shipping_country) && in_array($affid, array(13, 14, 18, 34, 208, 395))){				
				preg_match("/(?:[^a-zA-Z]|)($country_name_pattern)(?:[^a-zA-Z]+|$)/i", $p_v["TargetCountryExt"], $m);
				if(count($m) && !empty($m[1]) && isset($country_name_code[strtolower($m[1])])){							
					$shipping_country = $country_name_code[strtolower($m[1])];
				}else{					
					preg_match_all("/(?:[^a-zA-Z]|)($country_code)(?:[^a-zA-Z]+|$)/i", $p_v["TargetCountryExt"], $m);
					if(count($m) && !empty($m[1]) && is_array($m[1])){
						$shipping_country = strtolower(implode(",", $m[1]));
					}
				}
			}
			
			$language_arr = array(	'english' => array('us','ca','au','uk','ie'), 
									'danish' => array('dk'),
									'dutch' => array('nl'),
									'french' => array('fr', 'ca', 'be'),
									'german' => array('de','at'),
									'italian' => array('it'),
									'spanish' => array('es'), 
									'swedish' => array('se')
									);
			if($affid == 30){ // English => us,ca,au,uk,ie  , Danish => dk, Dutch => nl, French => fr, ca , German => de,  Italian => it, Spanish => es, Swedish => se
				$pattern_language = implode("|", array_keys($language_arr));				
				preg_match_all("/(?:[^a-zA-Z]|)($pattern_language)(?:[^a-zA-Z]+|$)/i", $p_v["TargetCountryExt"], $m);
				if(count($m) && !empty($m[1]) && is_array($m[1])){
					$shipping_arr = array();				
					foreach($m[1] as $cc){
						$cc = strtolower($cc);
						if(isset($language_arr[$cc])){
							//print_r($language_arr[$cc]);						
							$shipping_arr[] = implode(",", $language_arr[$cc]);
						}
					}
					$shipping_country = implode(",", $shipping_arr);										
				}
			}
			
			if(empty($shipping_country) && isset($p_v['TargetCountryIntOld'])){
				preg_match_all("/(?:[^a-zA-Z]|)($country_code)(?:[^a-zA-Z]+|$)/i", $p_v["TargetCountryIntOld"], $m);						
				if(count($m) && !empty($m[1]) && is_array($m[1])){
					$shipping_country = strtolower(implode(",", $m[1]));
				}
			}

			//print_r($this_aff_allow_country);
			if(empty($shipping_country) && count($this_aff_allow_country)){
				$shipping_country = implode(",", $this_aff_allow_country);
			}
			
			//print_r($manual_program);
			$domain_arr = array();
			if(isset($manual_program_2[$p_v["ID"]]["realdomain"])  && !empty($manual_program_2[$p_v["ID"]]["realdomain"])){
				if(isset($p_v["Homepage"])){
					$tmp_domain_manual = $objProgram->getDomainByHomepage($p_v["Homepage"], "fi");
					if(count($tmp_domain_manual)){
						$tmp_domain_manual = current($tmp_domain_manual["domain"]);
						if($tmp_domain_manual){
							$domain_arr[$tmp_domain_manual] = $tmp_domain_manual;
						}
					}
				}
				foreach(explode("\r\n", strip_tags($manual_program_2[$p_v["ID"]]["realdomain"])) as $v){
					$tmp_domain_manual = $objProgram->getDomainByHomepage($v, "fi");
					if(count($tmp_domain_manual)){
						$tmp_domain_manual = current($tmp_domain_manual["domain"]);
						if($tmp_domain_manual){
							$domain_arr[$tmp_domain_manual] = $tmp_domain_manual;
						}
					}
				}	
			}else{
				if(in_array($affid, array(223,639)) && !empty($p_v["Description"]) && empty($p_v["HomepageInt"])){
					$domain_arr = explode(",", strip_tags($p_v["Description"]));
					foreach($domain_arr as $k => $v){
						$domain_arr[$k] = trim($v);
					}
				}else{
					if($isactive == "Active" && $affid == 125){
						$real_url = $objProgram->getRealUrl($p_v["Homepage"]);
						if($real_url["httpcode"] == 200 && !empty($real_url["url"])){
							$p_v["Homepage"] = $real_url["url"];
						}
					}
					
					$domain_arr = $objProgram->getDomainByHomepage($p_v["Homepage"], "fi");
					
					//if($is_debug){
						if(isset($domain_arr["country"])){
							$c_code = implode("," ,$domain_arr["country"]);
						}
						if(isset($domain_arr["domain"])){
							$domain_arr = $domain_arr["domain"];					
						}
				}
			}
						
			if(!empty($p_v["TargetCountryInt"])){				
				preg_match_all("/(?:[^a-zA-Z]|)($country_code)(?:[^a-zA-Z]+|$)/i", $p_v["TargetCountryInt"], $m);
				//print_r($m);						
				if(count($m) && !empty($m[1]) && is_array($m[1])){
					$shipping_country = strtolower(implode(",", $m[1]));
				}
			}else{
				if(!$forcecc){
					if(!isset($p_v["ShippingCountry"]))
						$p_v["ShippingCountry"] = '';
					$tmp_cc = explode(",", $p_v["ShippingCountry"]);
					if(empty($shipping_country)){
						$shipping_country = $p_v["ShippingCountry"];
					}elseif(!empty($p_v["ShippingCountry"])){					
						$shipping_country = str_ireplace("gb", "uk", $shipping_country);					
						foreach($tmp_cc as $v_cc){
							if(strpos(",".$shipping_country.",", $v_cc) === false){
								$shipping_country .= ",".$v_cc;
							}
						}
					}
				}
			}
			if($affid == 32){
				$shipping_country = '';
			}
			$shipping_country = str_ireplace("gb", "uk", $shipping_country);
			
			if($p_v["TargetCountryInt"] == 'GLOBAL'){
				$shipping_country = '';
			}
			if(stripos($p_v["TargetCountryExt"], 'GLOBAL') !== false){
				$shipping_country = '';
			}
			
			$sql = "select homepage from program_homepage_history where programid = {$p_v["ID"]}";
			$tmp_homepage = array();
			$tmp_homepage = $objProgram->objMysql->getRows($sql);
			
			$sql = "SELECT b.domain FROM r_domain_program a inner join domain b on a.did = b.id WHERE a.PID = {$p_v["ID"]} and a.status = 'inactive'";
			$tmp_und = array();
			$tmp_und = $objProgram->objMysql->getRows($sql, "domain");
			foreach($tmp_homepage as $v_homepage){
				$tmp_domain = array();
				$tmp_domain = $objProgram->getDomainByHomepage($v_homepage["homepage"], "fi");
				if(count($tmp_domain)){
					$tmp_domain = current($tmp_domain["domain"]);
					if($tmp_domain && !isset($tmp_und[$tmp_domain])){
						$domain_arr[$tmp_domain] = $tmp_domain;
					}
				}
			}
			foreach($domain_arr as $k_d => $v_d){ //ensure these domain in array $domain_arr is active
				if(isset($aff_domain[$v_d])){
					if(!($affid == 604 && SID == 'bdg01'))
						unset($domain_arr[$k_d]);
				}
			}
			//print_r($domain_arr);
			if(count($domain_arr)){
				$objProgram->checkProgramDomain($p_v["ID"], $domain_arr);
			}else{
				$isactive = 'Inactive';
			}
			
			
			if(empty($c_code)){
				foreach($domain_arr as $dd){
					if($c_code = $objProgram->findDomainCountry($dd)){
						break;
					}				
				}
			}			
			$p_v["Domain"] = @$domain_arr[0];
			
			if(isset($manual_program[$p_v["ID"]]["SupportDeepUrlOut"]) && $manual_program[$p_v["ID"]]["SupportDeepUrlOut"] != 'UNKNOWN'){
				$p_v["SupportDeepUrl"] = $manual_program[$p_v["ID"]]["SupportDeepUrlOut"];
			}
			//echo "\r\n#get link 1..";
			$links_arr = array("AffDefaultUrl" => "", "DeepUrlTpl" => "", "OutGoingUrl" => "");			
			$links_arr = $objProgram->getProgramOutUrl($p_v, $aff_keyword);			
			//echo "2..\r\n";
			if(empty($links_arr["OutGoingUrl"])){
				$isactive = 'Inactive';
			}			
			
			if($p_v["AffId"] == 1 && $p_v["SupportDeepUrl"] <> 'YES' && strpos($links_arr["DeepUrlTpl"], '[SITEIDINAFF]') !== false){
				$links_arr["DeepUrlTpl"] = '';
				$links_arr["OutGoingUrl"] = $links_arr["AffDefaultUrl"];
			}
			
			//array("ID", "AffId", "IdInAff", "AffDefaultUrl", "DeepUrlTpl", "IsActive", "TrackingPattern", "CommissionVal", "Domain", "SEMPolicy", "CouponPolicy")
			if(isset($order))
				unset($order);
			//$order = $objProgram->getProgramRank($p_v["ID"]);
			
			$update_sql[$p_v["ID"]] = array("ProgramId" => $p_v["ID"], 
											"AffId" => $p_v["AffId"], 
											"IdInAff" => $p_v["IdInAff"], 
											"AffDefaultUrl" => $links_arr["AffDefaultUrl"], 
											"DeepUrlTpl" => $links_arr["DeepUrlTpl"], 
											"OutGoingUrl" => $links_arr["OutGoingUrl"],
											"IsActive" => $isactive, 
											"Domain" => implode("\r\n", $domain_arr),
											"LastUpdateTime" => date("Y-m-d H:i:s"),
											"SupportDeepUrl" => $p_v["SupportDeepUrl"],
											"CountryCode" => $c_code,
											"ShippingCountry" => $shipping_country,
											//"Order" => $order,
											);
											
			if($prgm_int[$p_v["ID"]]['ShippingCountry'] != $shipping_country || $prgm_int[$p_v["ID"]]['IsActive'] != $isactive || $prgm_int[$p_v["ID"]]['Domain'] != implode("\r\n", $domain_arr)){// || $prgm_int[$p_v["ID"]]['Order'] != $order){
				$update_sql[$p_v["ID"]]['LastChangeTime'] = date("Y-m-d H:i:s");
			}else{
				$update_sql[$p_v["ID"]]['LastChangeTime'] = $prgm_int[$p_v["ID"]]['LastChangeTime'];
			}
											
			if(!$is_fast){
				if(isset($manual_program_2[$p_v["ID"]]) && $manual_program_2[$p_v["ID"]]["CommissionType"] != "Unknown" && $manual_program_2[$p_v["ID"]]["CommissionUsed"] > 0.00){					
					$update_sql[$p_v["ID"]]["CommissionType"] = $manual_program_2[$p_v["ID"]]["CommissionType"];
					$update_sql[$p_v["ID"]]["CommissionUsed"] = $manual_program_2[$p_v["ID"]]["CommissionUsed"];
					$update_sql[$p_v["ID"]]["CommissionCurrency"] = $manual_program_2[$p_v["ID"]]["CommissionCurrency"];
				}else{
					$tmp_comm = array();
					if($hasCommFormatFunc && strlen($p_v["CommissionExt"])){				
						$tmp_comm = $objBDG->processCommissionTxt($p_v["CommissionExt"], $p_v['TargetCountryExt']);					
					}
					
					$update_sql[$p_v["ID"]]["CommissionValue"] = isset($tmp_comm["CommissionValue"]) ? $tmp_comm["CommissionValue"] : "";				
					$update_sql[$p_v["ID"]]["CommissionType"] = (@$tmp_comm["CommissionType"] == "percent") ? "Percent" : "Value";
					$update_sql[$p_v["ID"]]["CommissionUsed"] = isset($tmp_comm["CommissionUsed"]) ? $tmp_comm["CommissionUsed"] : "";
					$update_sql[$p_v["ID"]]["CommissionIncentive"] = (@$tmp_comm["CommissionIncentive"] == "1") ? 1 : 0;
					$update_sql[$p_v["ID"]]["CommissionCurrency"] = isset($tmp_comm["CommissionCurrency"]) ? $tmp_comm["CommissionCurrency"] : "";
				}
				
				//check url
				if($affid == 123){
					$update_sql[$p_v["ID"]]["DeniedPubCode"] = "";		
					if($isactive == "Active" && !empty($links_arr["OutGoingUrl"]) && $affid != 223){
						$test_deep = "http://".$p_v["Domain"];
						$test_url = $objProgram->pureUrl($links_arr["OutGoingUrl"], array("[IDINAFF]" => $p_v["IdInAff"], "[DEFAULTURL]" => $links_arr["AffDefaultUrl"], "[SUBTRACKING]" => "", "[SITEIDINAFF]" => "2567387", "[PURE_DEEPURL]" => $test_deep, "[DEEPURL]" => urlencode($test_deep)));
						$tmp_code = getUrlByNode($test_url, "code");
						if($tmp_code != 200){
							$update_sql[$p_v["ID"]]["DeniedPubCode"] = $tmp_code.$test_url;		
							//$isactive = 'Inactive';
							//$update_sql[$p_v["ID"]]["IsActive"] = 'Inactive';
							if($affid == 123){
								$update_sql[$p_v["ID"]]["CommissionUsed"] = '0';
								if($tmp_code == 404){
									$update_sql[$p_v["ID"]]["IsActive"] = 'Inactive';
								}
							}
						}
					}
					
				}
				
			}
			
			if(SID != 'bdg01'){
				if($p_v["AffId"] == 10){
					$update_sql[$p_v["ID"]]["SupportType"] = 'Content';
				}elseif($p_v["AffId"] == 1 || $p_v["AffId"] == 2 || $p_v["AffId"] == 6){
					if($prgm_int[$p_v["ID"]]['SupportType'] == ''){
						$update_sql[$p_v["ID"]]["SupportType"] = 'Content';
					}else{
						$update_sql[$p_v["ID"]]["SupportType"] = $prgm_int[$p_v["ID"]]['SupportType'];
					}
				}
			}
			
			$i++;
			
			if($i == 1) echo "AffId $affid ";
			
			if($is_debug){
				print_r($update_sql[$p_v["ID"]]);
				exit;
			}

			if(count($update_sql) > 30){
				$objProgram->insertProgramIntell($update_sql);
				$update_sql = array();
			}
			
			$objProgram->setChangeProgramProcessed($pid_list);
			
			if($max > 90000) break;
			$max++;
			
		}		
	}
	if(count($update_sql)){
		$objProgram->insertProgramIntell($update_sql);		
		unset($update_sql);
	}
	if($i){
		echo "end $i \r\n";
		$j += $i;
	}
	
	if(SID == 'bdg01' && $affid == 762){
		$sql = "UPDATE program_intell SET supportFake = 'NO' WHERE affid = 762 and supportFake <> 'no'";
		$objProgram->objMysql->query($sql);	
	}
	//exit;
}

echo "$j changed. \r\n";

if($j){
	$sql = "UPDATE r_domain_program a INNER JOIN program_intell b ON a.pid = b.programid 
			SET a.affdefaulturl = b.affdefaulturl, a.deepurltpl = b.deepurltpl 
			WHERE b.isactive = 'active' AND a.affdefaulturl = '' AND (b.affdefaulturl <> '' OR b.deepurltpl <> '')";
	$objProgram->objMysql->query($sql);
	
	$sql = "UPDATE r_domain_program a INNER JOIN program_intell b ON a.pid = b.programid 
			SET a.deepurltpl = b.deepurltpl 
			WHERE b.isactive = 'active' AND a.IsHandle = '0' AND a.deepurltpl = '' AND b.deepurltpl <> '' AND b.SupportDeepUrl = 'YES'";
	$objProgram->objMysql->query($sql);
	
	$sql = "UPDATE r_domain_program a INNER JOIN program_intell b ON a.pid = b.programid 
			SET a.affdefaulturl = b.affdefaulturl, a.deepurltpl = b.deepurltpl 
			WHERE b.isactive = 'active' AND b.affid = 1 AND a.deepurltpl = '' AND b.deepurltpl <> '' AND b.SupportDeepUrl = 'YES'";
	$objProgram->objMysql->query($sql);
	
	$sql = "UPDATE r_domain_program a INNER JOIN program_intell b ON a.pid = b.programid 
			SET a.affdefaulturl = b.affdefaulturl, a.deepurltpl = b.deepurltpl 
			WHERE b.isactive = 'active' AND b.affid = 1 AND a.deepurltpl LIKE '%[SITEIDINAFF]%' AND b.deepurltpl = '' AND b.SupportDeepUrl <> 'YES'";
	$objProgram->objMysql->query($sql);
}


//Inactive affiliate
$i = 0;

	$sql = "select a.programid from program_intell a inner join wf_aff b on a.affid = b.id where a.isactive = 'active' and b.isactive = 'no'";
	
	$tmp_arr = $objProgram->objMysql->getRows($sql, 'programid');
	$update_sql = array();
	foreach($tmp_arr as $v){
		$update_sql[] = array("ProgramId" => $v["programid"], 											
										"IsActive" => 'Inactive',											
										"LastUpdateTime" => date("Y-m-d H:i:s"),																				
										);
		$i++;
		if(count($update_sql) > 100){
			$objProgram->insertProgramIntell($update_sql);
			$update_sql = array();
		}
	}
	if(count($update_sql)){
		$objProgram->insertProgramIntell($update_sql);
		$update_sql = array();	
	}
	if(count($tmp_arr)){
		$sql = "update r_domain_program set LastUpdateTime = '".date('Y-m-d H:i:s')."' where pid in (".implode(",", array_keys($tmp_arr)).")";
		$objProgram->objMysql->query($sql);
	}

//active affiliate
$sql = "SELECT a.batchprimarykeyvalue FROM `table_change_log_batch` a INNER JOIN `table_change_log_detail` b ON a.batchid = b.batchid
		WHERE b.FiledName = 'isactive' AND a.batchtablename = 'wf_aff' AND a.batchaction = 'edit' AND b.filedvalueto = 'yes' AND a.batchprimarykeyvalue > 0
		and a.batchcreationtime > '".date("Y-m-d H:i", strtotime(" -5 minutes"))."'";
$tmp_arr = $objProgram->objMysql->getRows($sql, 'batchprimarykeyvalue');
if(count($tmp_arr)){
	foreach($tmp_arr as $affid => $tmp){
		$cmd = "php /home/bdg/program/cron/first_set_program_intell.php --affid=$affid --onlyactive --nottoredis > /home/bdg/program/cron/test/temp_$affid.log  2>&1 &";
		system($cmd);
		echo $cmd."\r\n";
	}	
}

echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";
exit;



?>
