<?php
	/**
	 * Created by PhpStorm.
	 * User: Sky Ting
	 * Date: 2017/10/13
	 * Time: 14:09
	 */
	
	include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
	include_once(INCLUDE_ROOT . "func/func.php");
	include_once(INCLUDE_ROOT . "func/nodejs.php");
	
	$date = date("Y-m-d H:i:s");
	$id_arr = array();
	$is_debug = false;
	$is_fast  = $onlyactive = $debug_sql = $forcecc = false;
	$in_house = false;
	if(isset($_SERVER["argc"]) && $_SERVER["argc"] > 1)
	{
		foreach($_SERVER["argv"] as $v){
			$tmp = explode("=", $v);
			if($tmp[0] == "--affid"){
				$id_arr = explode(",", $tmp[1]);
			}elseif($tmp[0] == "--debug"){
				$is_debug = true;
			}elseif($tmp[0] == "--fast"){
				$is_fast = true;
			}elseif($tmp[0] == "--forcecc"){
				$forcecc = true;
			}
		}
	}

	if(!checkProcess(__FILE__)){
		echo "process still runing".PHP_EOL;
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
	echo "<< Start to collect program @ $date >>\r\n";
	/**
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

	$objProgram = New Program();
	$objProgram->objMysql->query('SET NAMES latin1');

	$fieldArr = array('CommissionExt','Partnership','Homepage','SupportDeepUrl','Name','StatusInAff','AffDefaultUrl','TargetCountryExt','CommissionUsed','SupportType','StatusInBdg');
	$manual_program = $manual_program_2 = $country_code = $country_code_arr = $country_name_code = $aff_keyword = $country_name_code_no_blank = $aff_marketing = $aff_domain =  $update_sql = $domain_arr = array();
	$country_name_pattern = "";
	$max = $count_test = 0;
	$affiliate_list = $objProgram->getAllAffiliate($id_arr);
	foreach($affiliate_list as $v){
		if($v["IsInHouse"] != "NO" || !strlen($v["AffiliateUrlKeywords"])) continue;
		if($v["AffiliateUrlKeywords"]){
			$aff_keyword[$v['ID']] = explode("\r\n", $v["AffiliateUrlKeywords"]);
		}
		$tmp_arr = explode("\r\n", $v["AffiliateUrlKeywords"]);
		foreach($tmp_arr as $vv){
			$tmp_domain = array();
			$tmp_domain = $objProgram->getDomainByHomepage($vv, "fi");
			if(count($tmp_domain)){
				$domain = current($tmp_domain["domain"]);
				if(!empty($domain))
					$aff_domain[$domain] = $domain;
			}
		}
	}
	$sql = "select lower(countrycode) as countrycode, lower(countryname) as countryname, countrykeywords as countrykeywords from country_codes where countrystatus = 'on'";
	$tmp_arr = $objProgram->objMysql->getRows($sql);
	foreach($tmp_arr as $v){
		$v['countryname'] = str_replace(".", "\.", $v['countryname']);
		$country_name_code[$v['countryname']] = $v['countrycode'];
		$country_code[$v['countrycode']] = $v['countrycode'];
		$country_code_arr[$v['countrycode']] = $v['countrycode'];
		if(!empty($v['countrykeywords'])){
			$v['countrykeywords'] = strtolower($v['countrykeywords']);
			$tmp = explode("|", $v['countrykeywords']);
			foreach($tmp as $tmp_v){
				$tmp_v = str_replace(".", "\.", $tmp_v);
				$country_name_code[$tmp_v] = $v['countrycode'];
				$country_code[$tmp_v] = $v['countrycode'];
				$country_code_arr[$tmp_v] = $v['countrycode'];
			}
		}
	}
	$country_name_pattern = implode("|", array_keys($country_name_code));
	$country_code = strtoupper(preg_replace("/\s/", "", implode("|", array_keys($country_code))));
	$sql = "SELECT id, lower(MarketingContinent) as MarketingContinent, lower(MarketingCountry) as MarketingCountry FROM wf_aff WHERE IsActive = 'yes' AND ( MarketingContinent <> '' OR MarketingCountry <> '')";
	$aff_marketing = $objProgram->objMysql->getRows($sql, "id");
	$sql = "select programid, SupportDeepUrlOut from program_internal";
	$manual_program = $objProgram->objMysql->getRows($sql, "programid");
	if (SID == 'bdg01')
		$sql = "select programid, statusinbdg, realdomain, CommissionUsed, CommissionCurrency, CommissionType from program_manual";
	else
		$sql = "select programid, statusinbdg, realdomain, CommissionUsed, CommissionCurrency, CommissionType,SupportType from program_manual";
	$manual_program_2 = $objProgram->objMysql->getRows($sql, "programid");
	
	$sql = "select `ConditionName`,`ConditionValue`,`FieldName`,`FieldValue` from program_intell_control";
	$db_control = $objProgram->objMysql->getRows($sql);
	$control = array();
	foreach ($db_control as $item){
		$control[$item['ConditionName']][$item['ConditionValue']][$item['FieldName']] = $item['FieldValue'];
	}
	while (true)
	{
		$sql = "select ProgramID,group_concat(FieleName) FieleName from program_update_queue where `Status`='NEW' group by ProgramID order by `ID` asc limit 0,1000";
		$data = $objProgram->objMysql->getRows($sql,'ProgramID');
		if(empty($data))
			break;
		echo count($data) . "prorgam have changed" . PHP_EOL;
		$update_sql = array();
		$pids = implode(',',array_keys($data));
		$sql = "update program_update_queue set `Status`='PROCESSED' where ProgramID in ($pids)";
		$objProgram->objMysql->query($sql);
		if(SID == 'bdg01'){
			$sql = "SELECT a.ID, a.`Name`, a.Description, a.AffId, a.IdInAff, a.AffDefaultUrl, a.Partnership, a.StatusInAff, a.CommissionExt, a.Homepage, a.SupportDeepUrl, a.TargetCountryExt, a.TargetCountryInt, a.TargetCountryIntOld FROM program a left join program_manual b on a.`ID` = b.`ProgramId` WHERE a.ID in ($pids)";
		}else{
			$sql = "SELECT a.ID, a.`Name`, a.Description, a.AffId, a.IdInAff, a.AffDefaultUrl, a.Partnership, a.StatusInAff, a.CommissionExt, a.Homepage, a.SupportDeepUrl, a.TargetCountryExt, b.TargetCountryInt, a.TargetCountryIntOld, a.PublisherPolicy FROM program a left join program_manual b on a.`ID` = b.`ProgramId` WHERE a.ID in ($pids)";
		}
		$prgm_arr = $objProgram->objMysql->getRows($sql,"ID");
		if(SID == 'bdg01'){
			$sql = "select a.ProgramId, a.HomepageInt, b.ShippingCountry, b.IsActive, b.Domain, b.Order, b.LastChangeTime FROM program_int a left join program_intell b on a.programid = b.programid WHERE a.ProgramId in ($pids)";
		}else{
			$sql = "select a.ProgramId, a.HomepageInt, b.ShippingCountry, b.IsActive, b.Domain, b.Order, b.LastChangeTime, b.SupportType FROM program_int a left join program_intell b on a.programid = b.programid WHERE a.ProgramId in ($pids)";
		}
		$prgm_int = $objProgram->objMysql->getRows($sql,'ProgramId');
		foreach ($data as $datum) {
			$fields = array_unique(explode(',',$datum["FieleName"]));
			$flag_is_active = $flag_shipping_country = $flag_commission = $flag_domain = $flag_support_type = $support_deep_url = false;

			foreach ($fields as $field)
			{
				switch ($field)
				{
					case 'Partnership':
					case 'StatusInAff':
					case 'StatusInBdg':
						$flag_is_active = true;
						break;
					case 'Name':
					case 'TargetCountryExt':
						$flag_shipping_country  =true ;
						break;
					case 'CommissionExt':
					case 'CommissionUsed':
						$flag_commission = true;
						break;
					case 'Homepage':
						$flag_domain = true;
						break;
					case 'AffDefaultUrl':
					case 'SupportDeepUrl':
						$support_deep_url = true;
						break;
					case 'SupportType':
						$flag_support_type = true;
						break;
					default:
						break;
				}
			}
			$update_sql[$datum["ProgramID"]] = array(
				"ProgramId" => $datum["ProgramID"],
				"AffId" => $prgm_arr[$datum['ProgramID']]["AffId"],
				"IdInAff" => $prgm_arr[$datum['ProgramID']]["IdInAff"],
				"LastUpdateTime" => $date
			);
			
			if(isset($prgm_int[$datum["ProgramID"]])){
				if(!empty($prgm_int[$datum["ProgramID"]]["HomepageInt"])){
					$prgm_arr[$datum['ProgramID']]["Homepage"] = $prgm_int[$datum["ProgramID"]]["HomepageInt"];
				}
				if(isset($prgm_int[$datum["ProgramID"]]["ShippingCountry"])){
					$prgm_arr[$datum['ProgramID']]["ShippingCountry"] = $prgm_int[$datum["ProgramID"]]["ShippingCountry"];
				}
				else
					$prgm_arr[$datum['ProgramID']]["ShippingCountry"] = '';
			}
			
			if($flag_is_active)
			{
				$isactive = ($prgm_arr[$datum['ProgramID']]["StatusInAff"] == "Active" && $prgm_arr[$datum['ProgramID']]["Partnership"] == "Active") ? "Active" : "Inactive";
				if(isset($manual_program_2[$datum['ProgramID']]) && $manual_program_2[$datum['ProgramID']]["statusinbdg"] != "" && $manual_program_2[$datum['ProgramID']]["statusinbdg"] != "Unknown"){
					$isactive = $manual_program_2[$datum['ProgramID']]["statusinbdg"];
				}
				$update_sql[$datum["ProgramID"]]['IsActive'] = $isactive;
				if(!isset($prgm_int[$datum["ProgramID"]]["IsActive"]) || $prgm_int[$datum["ProgramID"]]['IsActive'] != $isactive ){
					$update_sql[$datum["ProgramID"]]['LastChangeTime'] = $date;
				}
			}
			
			if($flag_shipping_country)
			{
				$shipping_country = "";
				$shipping_arr = $m = array();
				if(stripos($prgm_arr[$datum['ProgramID']]["TargetCountryExt"], 'all Countries') !== false || trim($prgm_arr[$datum['ProgramID']]["TargetCountryExt"]) == 'all' || trim($prgm_arr[$datum['ProgramID']]["TargetCountryExt"]) == 'worldwide'){
						$shipping_country = '';
				}else{
					if(!empty($prgm_arr[$datum['ProgramID']]["TargetCountryExt"]))
					{
						$targetCountryExtArr = explode(",", $prgm_arr[$datum['ProgramID']]["TargetCountryExt"]);
						foreach($country_name_code as $k => $v){
							$k = preg_replace("/\s/", "", $k);
							foreach($targetCountryExtArr as $targetCountry){
								$targetCountry = preg_replace("/\s/", "", $targetCountry);
								if(strcasecmp($k, $targetCountry) == 0)
								{
									$shipping_arr[$v]=$v;
								}
								if(strcasecmp($v, $targetCountry) == 0)
								{
									$shipping_arr[$v]=$v;
								}
							}
						}
						if(empty($shipping_arr)){
							preg_match_all("/(?:[^a-zA-Z]|)($country_name_pattern)(?:[^a-zA-Z]|$)/i", $prgm_arr[$datum['ProgramID']]["TargetCountryExt"], $m);
							if(count($m) && !empty($m[1]) && is_array($m[1])){
								foreach($m[1] as $cc){
									$cc = strtolower($cc);
									$shipping_arr[$country_name_code[$cc]] = $country_name_code[$cc];
								}
							}
						}
					}
					$check_name_country = false;
					if(SID=='bdg02') {
						$check_name_country = true;
					}
					$this_aff_allow_country = array();
					if(isset($aff_marketing[$prgm_arr[$datum['ProgramID']]['AffId']])){
						if($aff_marketing[$prgm_arr[$datum['ProgramID']]['AffId']]["MarketingContinent"] == "global"){
							$check_name_country = true;
						}elseif(!empty($aff_marketing[$prgm_arr[$datum['ProgramID']]['AffId']]["MarketingContinent"])){
							if(empty($aff_marketing[$prgm_arr[$datum['ProgramID']]['AffId']]["MarketingCountry"])){
								foreach($objProgram->country_rel as $country => $continent){
									if($aff_marketing[$prgm_arr[$datum['ProgramID']]['AffId']]["MarketingContinent"] == $continent)
										$this_aff_allow_country[$country] = $country;
								}
							}else{
								$this_aff_allow_country[] = $aff_marketing[$prgm_arr[$datum['ProgramID']]['AffId']]["MarketingCountry"];
							}
						}
					}
					if(empty($shipping_arr)){
						preg_match("/[^a-zA-Z]+($country_name_pattern)(?:[^a-zA-Z]|$)/i", $prgm_arr[$datum['ProgramID']]["Name"] . " ", $m);
						if(count($m) && !empty($m[1]) && isset($country_name_code[strtolower($m[1])])){
							$shipping_arr[$country_name_code[strtolower($m[1])]] = $country_name_code[strtolower($m[1])];
						}else {
							if($prgm_arr[$datum['ProgramID']]['AffId'] == 152 || $prgm_arr[$datum['ProgramID']]['AffId']==2002 || $prgm_arr[$datum['ProgramID']]['AffId']== 2043)
								preg_match_all("/(?:[^a-zA-Z]|\s|^)($country_code)(?:[^a-zA-Z]|\s|$)/", $prgm_arr[$datum['ProgramID']]["Name"]. " ", $m);
							else
								preg_match_all("/(?:[^a-zA-Z]|\s)($country_code)(?:[^a-zA-Z]|\s)/", $prgm_arr[$datum['ProgramID']]["Name"]. " ", $m);
							if(count($m) && !empty($m[1]) && is_array($m[1])){
								foreach($m[1] as $cc){
									if($cc == strtoupper($cc)){
										$cc = strtolower($cc);
										$shipping_arr[$country_code_arr[$cc]] = $country_code_arr[$cc];
									}
								}
							}
						}
					}
		
					if(count($shipping_arr))
					{
						$shipping_country = strtolower(implode(",", $shipping_arr));
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
					if($prgm_arr[$datum['ProgramID']]['AffId'] == 30){
						$pattern_language = implode("|", array_keys($language_arr));
						preg_match_all("/(?:[^a-zA-Z]|)($pattern_language)(?:[^a-zA-Z]+|$)/i", $prgm_arr[$datum['ProgramID']]["TargetCountryExt"], $m);
						if(count($m) && !empty($m[1]) && is_array($m[1])){
							$shipping_arr = array();
							foreach($m[1] as $cc){
								$cc = strtolower($cc);
								if(isset($language_arr[$cc])){
									$shipping_arr[] = implode(",", $language_arr[$cc]);
								}
							}
							$shipping_country = implode(",", $shipping_arr);
						}
					}
					if(empty($shipping_country) && isset($prgm_arr[$datum['ProgramID']]['TargetCountryIntOld'])){
						preg_match_all("/(?:[^a-zA-Z]|)($country_code)(?:[^a-zA-Z]+|$)/i", $prgm_arr[$datum['ProgramID']]["TargetCountryIntOld"], $m);
						if(count($m) && !empty($m[1]) && is_array($m[1])){
							$shipping_country = strtolower(implode(",", $m[1]));
						}
					}
		
					//print_r($this_aff_allow_country);
					if(empty($shipping_country) && count($this_aff_allow_country)){
						$shipping_country = implode(",", $this_aff_allow_country);
					}
					if(stripos($prgm_arr[$datum['ProgramID']]["TargetCountryExt"], 'GLOBAL') !== false){
						$shipping_country = '';
					}
					if(!empty($prgm_arr[$datum['ProgramID']]["TargetCountryInt"])){
						if($prgm_arr[$datum['ProgramID']]["TargetCountryInt"] == 'GLOBAL'){
							$shipping_country = '';
						}else{
							preg_match_all("/(?:[^a-zA-Z]|)($country_code)(?:[^a-zA-Z]+|$)/i", $prgm_arr[$datum['ProgramID']]["TargetCountryInt"], $m);
							if(count($m) && !empty($m[1]) && is_array($m[1])){
								$shipping_country = strtolower(implode(",", $m[1]));
							}
						}
					}else{
						if(!$forcecc){
							if(!isset($prgm_arr[$datum['ProgramID']]["ShippingCountry"]))
								$prgm_arr[$datum['ProgramID']]["ShippingCountry"] = '';
							$tmp_cc = explode(",", $prgm_arr[$datum['ProgramID']]["ShippingCountry"]);
							if(empty($shipping_country)){
								$shipping_country = $prgm_arr[$datum['ProgramID']]["ShippingCountry"];
							}elseif(!empty($prgm_arr[$datum['ProgramID']]["ShippingCountry"])){
								foreach($tmp_cc as $v_cc){
									if(!empty($v_cc))
									{
										if(strpos(",".$shipping_country.",", $v_cc) === false){
											$shipping_country .= ",".$v_cc;
										}
									}
								}
							}
						}
					}
					$shipping_country = str_ireplace("gb", "uk", $shipping_country);
					$shipping_country = strtolower(implode(",", array_unique(explode(',',$shipping_country))));
					if(SID == 'bdg02'){
						if(isset($control['AffId'][$prgm_arr[$datum['ProgramID']]]['ShippingCountry']))
							$shipping_country = $control['AffId'][$prgm_arr[$datum['ProgramID']]]['ShippingCountry'];
					}
					$update_sql[$datum['ProgramID']]['ShippingCountry'] = $shipping_country;
					if(!isset($prgm_int[$datum["ProgramID"]]['ShippingCountry']) || $prgm_int[$datum["ProgramID"]]['ShippingCountry'] != $shipping_country ){
						$update_sql[$datum["ProgramID"]]['LastChangeTime'] = $date;
					}
				}
			}
			if($flag_domain)
			{
				$c_code = '';
				if(isset($manual_program_2[$datum['ProgramID']]["realdomain"])  && !empty($manual_program_2[$datum['ProgramID']]["realdomain"])){
					if(isset($prgm_arr[$datum['ProgramID']]["Homepage"])){
						$tmp_domain_manual = $objProgram->getDomainByHomepage($prgm_arr[$datum['ProgramID']]["Homepage"], "fi");
						if(count($tmp_domain_manual)){
							$tmp_domain_manual = current($tmp_domain_manual["domain"]);
							if($tmp_domain_manual){
								$domain_arr[$tmp_domain_manual] = $tmp_domain_manual;
							}
						}
					}
					foreach(explode("\r\n", strip_tags($manual_program_2[$datum['ProgramID']]["realdomain"])) as $v){
						$tmp_domain_manual = $objProgram->getDomainByHomepage($v, "fi");
						if(count($tmp_domain_manual)){
							$tmp_domain_manual = current($tmp_domain_manual["domain"]);
							if($tmp_domain_manual){
								$domain_arr[$tmp_domain_manual] = $tmp_domain_manual;
							}
						}
					}
				}else{
					if($datum["ProgramID"] == 223 && !empty($prgm_arr[$datum['ProgramID']]["Description"]) && empty($prgm_arr[$datum['ProgramID']]["HomepageInt"])){
						$domain_arr = explode(",", strip_tags($prgm_arr[$datum['ProgramID']]["Description"]));
						foreach($domain_arr as $k => $v){
							$domain_arr[$k] = trim($v);
						}
					}else{
						if(((isset($isactive) && $isactive == "Active") || (isset($prgm_int[$datum["ProgramID"]]["IsActive"]) && $prgm_int[$datum["ProgramID"]]["IsActive"] == 'Active')) && $prgm_arr[$datum['ProgramID']]['AffId'] == 125){
							$real_url = $objProgram->getRealUrl($prgm_arr[$datum['ProgramID']]["Homepage"]);
							if($real_url["httpcode"] == 200 && !empty($real_url["url"])){
								$prgm_arr[$datum['ProgramID']]["Homepage"] = $real_url["url"];
							}
						}
						
						$domain_arr = $objProgram->getDomainByHomepage($prgm_arr[$datum['ProgramID']]["Homepage"], "fi");
						if(isset($domain_arr["country"])){
							$c_code = implode("," ,$domain_arr["country"]);
						}
						if(isset($domain_arr["domain"])){
							$domain_arr = $domain_arr["domain"];
						}
					}
				}
				
				$sql = "select homepage from program_homepage_history where programid = {$datum["ProgramID"]}";
				$tmp_homepage = array();
				$tmp_homepage = $objProgram->objMysql->getRows($sql);
				
				$sql = "SELECT b.domain FROM r_domain_program a inner join domain b on a.did = b.id WHERE a.PID = {$datum["ProgramID"]} and a.status = 'inactive'";
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
						if(!($prgm_arr[$datum['ProgramID']]['AffId'] == 604 && SID == 'bdg01'))
							unset($domain_arr[$k_d]);
					}
				}
				if(count($domain_arr)){
					$objProgram->checkProgramDomain($datum['ProgramID'], $domain_arr);
				}else{
					$update_sql[$datum["ProgramID"]]['IsActive'] = 'Inactive';
					$update_sql[$datum["ProgramID"]]['LastChangeTime'] = $date;
				}
				if(empty($c_code)){
					foreach($domain_arr as $dd){
						if($c_code = $objProgram->findDomainCountry($dd)){
							break;
						}
					}
				}
				$prgm_arr[$datum['ProgramID']]["Domain"] = @$domain_arr[0];
				$update_sql[$datum["ProgramID"]]['CountryCode'] = $c_code;
				$update_sql[$datum["ProgramID"]]['Domain'] = implode("\r\n", $domain_arr);
				if((isset($prgm_int[$datum['ProgramID']]['Domain']) && $prgm_int[$datum['ProgramID']]['Domain'] != implode("\r\n", $domain_arr)) || !isset($prgm_int[$datum['ProgramID']]['Domain'])){
					$update_sql[$datum["ProgramID"]]['LastChangeTime'] = $date;
				}
			}
			
			if($flag_commission)
			{
				$hasCommFormatFunc = false;
				$LinkFeedName = 'LinkFeed_'.$prgm_arr[$datum["ProgramID"]]["AffId"];
				$class_file = INCLUDE_ROOT . 'lib/aff/Class.' . $LinkFeedName . '.php';
				if(file_exists($class_file)){
					include_once($class_file);
					$objBDG = new $LinkFeedName;
					$hasCommFormatFunc = true;
				}
				if(isset($manual_program_2[$datum["ProgramID"]]) && $manual_program_2[$datum["ProgramID"]]["CommissionType"] != "Unknown" && $manual_program_2[$datum["ProgramID"]]["CommissionUsed"] > 0.00){
					$update_sql[$datum["ProgramID"]]["CommissionType"] = $manual_program_2[$datum["ProgramID"]]["CommissionType"];
					$update_sql[$datum["ProgramID"]]["CommissionUsed"] = $manual_program_2[$datum["ProgramID"]]["CommissionUsed"];
					$update_sql[$datum["ProgramID"]]["CommissionCurrency"] = $manual_program_2[$datum["ProgramID"]]["CommissionCurrency"];
				}else{
					$tmp_comm = array();
					if($hasCommFormatFunc && strlen($prgm_arr[$datum["ProgramID"]]["CommissionExt"])){
						$tmp_comm = $objBDG->processCommissionTxt($prgm_arr[$datum["ProgramID"]]["CommissionExt"], $prgm_arr[$datum["ProgramID"]]['TargetCountryExt']);
					}
					$update_sql[$datum["ProgramID"]]["CommissionValue"] = isset($tmp_comm["CommissionValue"]) ? $tmp_comm["CommissionValue"] : "";
					$update_sql[$datum["ProgramID"]]["CommissionType"] = (@$tmp_comm["CommissionType"] == "percent") ? "Percent" : "Value";
					$update_sql[$datum["ProgramID"]]["CommissionUsed"] = isset($tmp_comm["CommissionUsed"]) ? $tmp_comm["CommissionUsed"] : "";
					$update_sql[$datum["ProgramID"]]["CommissionIncentive"] = (@$tmp_comm["CommissionIncentive"] == "1") ? 1 : 0;
					$update_sql[$datum["ProgramID"]]["CommissionCurrency"] = isset($tmp_comm["CommissionCurrency"]) ? $tmp_comm["CommissionCurrency"] : "";
				}
				
				if($prgm_arr[$datum["ProgramID"]]['AffId'] == 123 && ((isset($isactive) && $isactive == "Active") || $prgm_int[$datum["ProgramID"]]["IsActive"] == 'Active') && !empty($links_arr["OutGoingUrl"])){
					$test_deep = "http://".$prgm_arr[$datum["ProgramID"]]["Domain"];
					$test_url = $objProgram->pureUrl($links_arr["OutGoingUrl"], array("[IDINAFF]" => $prgm_arr[$datum["ProgramID"]]["IdInAff"], "[DEFAULTURL]" => $links_arr["AffDefaultUrl"], "[SUBTRACKING]" => "", "[SITEIDINAFF]" => "2567387", "[PURE_DEEPURL]" => $test_deep, "[DEEPURL]" => urlencode($test_deep)));
					$tmp_code = getUrlByNode($test_url, "code");
					if($tmp_code != 200){
						$update_sql[$datum["ProgramID"]]["CommissionUsed"] = '0';
						if($tmp_code == 404){
							$update_sql[$datum["ProgramID"]]["IsActive"] = 'Inactive';
							$update_sql[$datum["ProgramID"]]['LastChangeTime'] = date("Y-m-d H:i:s");
						}
					}
				}
				$order = $objProgram->getProgramRank($datum["ProgramID"]);
				if((isset($prgm_int[$datum["ProgramID"]]['Order']) && $prgm_int[$datum["ProgramID"]]['Order'] != $order) || !isset($prgm_int[$datum["ProgramID"]]['Order'])){
					$update_sql[$datum["ProgramID"]]['Order'] = $objProgram->getProgramRank($datum["ProgramID"]);
					$update_sql[$datum["ProgramID"]]['LastChangeTime'] = $date;
				}
			}
			
			if($support_deep_url)
			{
				if(isset($manual_program[$datum["ProgramID"]]["SupportDeepUrlOut"]) && $manual_program[$datum["ProgramID"]]["SupportDeepUrlOut"] != 'UNKNOWN'){
				$prgm_arr[$datum['ProgramID']]["SupportDeepUrl"] = $manual_program[$datum["ProgramID"]]["SupportDeepUrlOut"];
				}
				$links_arr = array("AffDefaultUrl" => "", "DeepUrlTpl" => "", "OutGoingUrl" => "");
				$links_arr = $objProgram->getProgramOutUrl($prgm_arr[$datum['ProgramID']], $aff_keyword);
				if(empty($links_arr["OutGoingUrl"])){
					$update_sql[$datum["ProgramID"]]["IsActive"] = 'Inactive';
					$update_sql[$datum["ProgramID"]]['LastChangeTime'] = date("Y-m-d H:i:s");
				}
				
				if($prgm_arr[$datum['ProgramID']]["AffId"] == 1 && $prgm_arr[$datum['ProgramID']]["SupportDeepUrl"] <> 'YES' && strpos($links_arr["DeepUrlTpl"], '[SITEIDINAFF]') !== false){
					$links_arr["DeepUrlTpl"] = '';
					$links_arr["OutGoingUrl"] = $links_arr["AffDefaultUrl"];
				}
				$update_sql[$datum["ProgramID"]]['AffDefaultUrl'] = $links_arr["AffDefaultUrl"];
				$update_sql[$datum["ProgramID"]]['DeepUrlTpl'] = $links_arr["DeepUrlTpl"];
				$update_sql[$datum["ProgramID"]]['OutGoingUrl'] = $links_arr["OutGoingUrl"];
				$update_sql[$datum["ProgramID"]]['SupportDeepUrl'] = $prgm_arr[$datum['ProgramID']]["SupportDeepUrl"];
				
				if(SID == 'bdg02' && $prgm_arr[$datum['ProgramID']]['AffId'] == 2032){	
					$sql = "UPDATE r_domain_program a INNER JOIN program_intell b ON a.pid = b.programid
							SET a.affdefaulturl = '".addslashes($links_arr["AffDefaultUrl"])."', a.deepurltpl = '".addslashes($links_arr["DeepUrlTpl"])."', a.LastUpdateTime = '".date("Y-m-d H:i:s")."'
							WHERE b.programid = {$datum["ProgramID"]} AND b.affid = 2032";
					$objProgram->objMysql->query($sql);
				}
			}
			
			if($flag_support_type && SID != 'bdg01')
			{
				if($prgm_arr[$datum["ProgramID"]]['AffId'] == 10 && $prgm_int[$datum["ProgramID"]]['SupportType'] == ''){
					$update_sql[$datum["ProgramID"]]["SupportType"] = 'Content';
				}else{
					if(isset($manual_program_2[$datum["ProgramID"]]["SupportType"]) && $manual_program_2[$datum["ProgramID"]]["SupportType"] != ""){
						$update_sql[$datum["ProgramID"]]["SupportType"] = $manual_program_2[$datum["ProgramID"]]["SupportType"];
					}else{
						if(in_array($prgm_arr[$datum['ProgramID']]['AffId'], $objProgram->aff_tt)){
							if($prgm_arr[$datum['ProgramID']]['PublisherPolicy'] == 'disallowed'){
								$update_sql[$prgm_arr[$datum['ProgramID']]]["SupportType"] = 'Content';
							}elseif($prgm_arr[$datum['ProgramID']]['PublisherPolicy'] == 'limited'){
								$update_sql[$prgm_arr[$datum['ProgramID']]]["SupportType"] = 'All';
							}else{
								$update_sql[$prgm_arr[$datum['ProgramID']]]["SupportType"] = 'All';
							}
						}					
					}					
				}
			}
		}
		if(count($update_sql)){
			$objProgram->insertProgramIntell($update_sql);
		}
		if($max > 90000) break;
		$max++;
	}
	$sql = "UPDATE r_domain_program a INNER JOIN program_intell b ON a.pid = b.programid SET a.affdefaulturl = b.affdefaulturl, a.deepurltpl = b.deepurltpl	WHERE b.isactive = 'active' AND a.affdefaulturl = '' AND (b.affdefaulturl <> '' OR b.deepurltpl <> '')";
	$objProgram->objMysql->query($sql);
	$sql = "UPDATE r_domain_program a INNER JOIN program_intell b ON a.pid = b.programid SET a.deepurltpl = b.deepurltpl WHERE b.isactive = 'active' AND a.IsHandle = '0' AND a.deepurltpl = '' AND b.deepurltpl <> '' AND b.SupportDeepUrl = 'YES'";
	$objProgram->objMysql->query($sql);
	$sql = "UPDATE r_domain_program a INNER JOIN program_intell b ON a.pid = b.programid SET a.affdefaulturl = b.affdefaulturl, a.deepurltpl = b.deepurltpl	WHERE b.isactive = 'active' AND b.affid = 1 AND a.deepurltpl = '' AND b.deepurltpl <> '' AND b.SupportDeepUrl = 'YES'";
	$objProgram->objMysql->query($sql);
	$sql = "UPDATE r_domain_program a INNER JOIN program_intell b ON a.pid = b.programid SET a.affdefaulturl = b.affdefaulturl, a.deepurltpl = b.deepurltpl WHERE b.isactive = 'active' AND b.affid = 1 AND a.deepurltpl LIKE '%[SITEIDINAFF]%' AND b.deepurltpl = '' AND b.SupportDeepUrl <> 'YES'";
	$objProgram->objMysql->query($sql);
	
	
	
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
		$sql = "update r_domain_program set LastUpdateTime = '{$date}' where pid in (".implode(",", array_keys($tmp_arr)).")";
		$objProgram->objMysql->query($sql);
	}

//active affiliate
	$sql = "SELECT a.batchprimarykeyvalue FROM `table_change_log_batch` a INNER JOIN `table_change_log_detail` b ON a.batchid = b.batchid WHERE b.FiledName = 'isactive' AND a.batchtablename = 'wf_aff' AND a.batchaction = 'edit' AND b.filedvalueto = 'yes' AND a.batchprimarykeyvalue > 0 and a.batchcreationtime > '".date("Y-m-d H:i", strtotime(" -5 minutes"))."'";
	$tmp_arr = $objProgram->objMysql->getRows($sql, 'batchprimarykeyvalue');
	if(count($tmp_arr)){
		foreach($tmp_arr as $affid => $tmp){
			$cmd = "php /home/bdg/program/cron/first_set_program_intell.php --affid=$affid --onlyactive --nottoredis > /home/bdg/program/cron/test/temp_$affid.log  2>&1 &";
			system($cmd);
			echo $cmd."\r\n";
		}
	}

	echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";
?>