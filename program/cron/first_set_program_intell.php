<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
include_once(INCLUDE_ROOT . "func/func.php");
include_once(INCLUDE_ROOT . "func/nodejs.php");
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
			$pid = " and a.id in (" . trim($tmp[1], ",") . ")";
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
if($debug_sql){
	define("SQL_CONFIG", 1);
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
$date = date("Y-m-d H:i:s");

echo "<< Start @ $date >>\r\n";

#$tmp_skimlinks_nocomm = array(95603,95747,95808,96015,96290,96506,97600,98091,98792,98997,99442,100236,100242,100327,101525,101670,101673,101692,102120,102131,106364,106833,108077,108388,111355,111495,111802,111901,112011,113514,114906,115636,116445,117761,117766,117794,117829,117898,118114,118532,118533,119889,135919,136866,138483,141222,141680,144679,149463,152733,159512,160721,161905,161913,163931,164062,165858,167297,169075,171368,172374,176033,184552,185048,229992,230921);

$tmp_skimlinks_nocomm = array();

$objProgram = New Program();
$objProgram->objMysql->query('SET NAMES latin1');
$affiliate_list = $objProgram->getAllAffiliate($id_arr);
$aff_domain = array();
foreach($affiliate_list as $v){
	if($v["IsInHouse"] != "NO" || !strlen($v["AffiliateUrlKeywords"])) continue;//affiliate who is not in house and has AffiliateUrlkeywords can go on
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

if(!count($affiliate_list) && count($id_arr) && !empty($pid)){
	$sql = "SELECT ID, Name, ShortName, Domain, AffiliateUrlKeywords, AffiliateUrlKeywords2, DeepUrlParaName, SupportDeepUrl, SubTracking, SubTracking2, ProgramCrawled, IsInHouse, ImportanceRank FROM wf_aff WHERE ID IN ('" . implode("','", $id_arr) . "')";
	$affiliate_list = $objProgram->objMysql->getRows($sql, "ID");
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

$country_code = trim(str_replace(",", "|", $objProgram->global_c), "|");

$sql = "select `ConditionName`,`ConditionValue`,`FieldName`,`FieldValue` from program_intell_control";
$db_control = $objProgram->objMysql->getRows($sql);
$control = array();
foreach ($db_control as $item){
	$control[$item['ConditionName']][$item['ConditionValue']][$item['FieldName']] = $item['FieldValue'];
}
$aff_keyword = array();

$non_comm_format = array();

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
	//
	if($in_house){
		if($aff_v["IsInHouse"] <> 'YES') continue;//affiliate who is inHouse can go on(table wf_aff)
	}
	
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

	echo "AffId($affid) start";
	if($affid == 191 && empty($pid)){
		echo "ignore viglink(191)\r\n";
		continue;
	}
	
	unset($objProgram->old_ps);
	
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
	
	$i = 0;
	$start = 0;
	$limit = 100;
	$pos = 0;
	$coupon_site_policy_count = $coupon_site_policy_error = 0;
	$coupon_site_policy = '';
	while(true){
		$where_str = "";
		$sql = "select a.ID, a.Name, a.CommissionExt, c.SupportType, a.AffId,a.CategoryExt,a.IdInAff, a.Homepage, a.Description, a.StatusInAff, a.Partnership, a.AffDefaultUrl, a.SupportDeepUrl, a.CommissionExt, a.TargetCountryExt, d.TargetCountryInt, a.TargetCountryIntOld, a.LastUpdateTime, b.HomepageInt,c.ShippingCountry,a.PublisherPolicy, a.SecondIdInAff from program a left join program_int b on a.id = b.programid left join program_intell c on a.id = c.programid left join program_manual d on a.`ID` = d.`ProgramId` where a.affid = $affid $pid ";

		if($onlyactive){
			$where_str .= " and a.StatusInAff = 'active' and a.Partnership = 'active' ";
		}
		
		$sql .= $where_str . " and a.ID > $pos order by a.ID limit 100";//. ($start) * $limit . ", $limit";


		$prgm_arr = $objProgram->objMysql->getRows($sql, "ID");

		echo "\tget program(".count($prgm_arr).")";
		if(!count($prgm_arr)){
			break;
		}

		$tmp = array_keys($prgm_arr);

		$tmp_pos = end($tmp);

		if($tmp_pos > $pos) $pos = $tmp_pos;
		
		$start++;

		foreach($prgm_arr as $p_v){
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

			if(!empty($p_v["HomepageInt"])){
				$p_v["Homepage"] = $p_v["HomepageInt"];
			}
			
			
			$c_code = "";
			$shipping_country = "";

			if(empty($shipping_country)){
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
					if($affid == 152 || $affid == 2002 || $affid == 2043){
						preg_match_all("/(?:[^a-zA-Z]|\s|^)($country_code)(?:[^a-zA-Z]|\s|$)/", $p_v["Name"]. " ", $m);
					}else{
						preg_match_all("/(?:[^a-zA-Z]|\s)($country_code)(?:[^a-zA-Z]|\s)/", $p_v["Name"]. " ", $m);
					}

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
				}
			}

			//if($affid == 10 || $affid == 191){
			if($affid == 191){
				$tmp_arr = explode(",", strtolower($p_v['TargetCountryExt']));
				foreach($tmp_arr as $t_v){
					if(!empty($t_v)){
						$shipping_arr[$t_v] = $t_v;
					}
				}
			}elseif($affid == 1 || $affid == 15 || $affid == 189){
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
	
			if(empty($shipping_country) && count($this_aff_allow_country))
			{
				$shipping_country = implode(",", $this_aff_allow_country);
			}


			//print_r($manual_program);
			$domain_arr = array();
			if(isset($manual_program_2[$p_v["ID"]]["realdomain"])  && !empty($manual_program_2[$p_v["ID"]]["realdomain"])){
				if(isset($p_v["Homepage"])){
					$tmp_domain_manual = $objProgram->getDomainByHomepage($p_v["Homepage"], "fi");
					$tmp_domain_manual = current($tmp_domain_manual["domain"]);
					if($tmp_domain_manual){
						$domain_arr[$tmp_domain_manual] = $tmp_domain_manual;
					}
				}
				foreach(explode("\r\n", strip_tags($manual_program_2[$p_v["ID"]]["realdomain"])) as $v){
					$tmp_domain_manual = $objProgram->getDomainByHomepage($v, "fi");
					$tmp_domain_manual = current($tmp_domain_manual["domain"]);
					if($tmp_domain_manual){
						$domain_arr[$tmp_domain_manual] = $tmp_domain_manual;
					}
				}
			}else{
				if($affid == 223 && !empty($p_v["Description"]) && empty($p_v["HomepageInt"])){
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
					
					/*foreach($domain_arr as $tmp_dom){
						if(isset($aff_domain[$tmp_dom])){
							$real_url = $objProgram->getRealUrl($p_v["Homepage"]);
							if($real_url["httpcode"] == 200 && !empty($real_url["url"])){
								$p_v["Homepage"] = $real_url["url"];
								
								$domain_arr = $objProgram->getDomainByHomepage($p_v["Homepage"], "fi");
							}
						}
					}*/
					
					if(isset($domain_arr["country"])){
						$c_code = implode("," ,$domain_arr["country"]);
					}
					if(isset($domain_arr["domain"])){
						$domain_arr = $domain_arr["domain"];
					}
				}
			}

			if(stripos($p_v["TargetCountryExt"], 'GLOBAL') !== false){
				$shipping_country = '';
			}
			
			if(!empty($p_v["TargetCountryInt"])){
				if($p_v["TargetCountryInt"] == 'GLOBAL'){
					$shipping_country = '';
				}else{
					preg_match_all("/(?:[^a-zA-Z]|)($country_code)(?:[^a-zA-Z]+|$)/i", $p_v["TargetCountryInt"], $m);
					//print_r($m);
					if(count($m) && !empty($m[1]) && is_array($m[1])){
						$shipping_country = strtolower(implode(",", $m[1]));
					}
				}
			}else{
				if(!$forcecc){
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
			$shipping_country = str_ireplace("gb", "uk", $shipping_country);
			
			$sql = "select homepage from program_homepage_history where programid = {$p_v["ID"]}";
			$tmp_homepage = array();
			$tmp_homepage = $objProgram->objMysql->getRows($sql);
			
			$sql = "SELECT b.domain FROM r_domain_program a inner join domain b on a.did = b.id WHERE a.PID = {$p_v["ID"]} and a.status = 'inactive'";
			$tmp_und = array();
			$tmp_und = $objProgram->objMysql->getRows($sql, "domain");
			foreach($tmp_homepage as $v_homepage){
				$tmp_domain = $objProgram->getDomainByHomepage($v_homepage["homepage"], "fi");
				$tmp_domain = current($tmp_domain["domain"]);
				if($tmp_domain && !isset($tmp_und[$tmp_domain])){
					$domain_arr[$tmp_domain] = $tmp_domain;
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
			
			$links_arr = array("AffDefaultUrl" => "", "DeepUrlTpl" => "", "OutGoingUrl" => "");
			$links_arr = $objProgram->getProgramOutUrl($p_v, $aff_keyword);
			
			if(empty($links_arr["OutGoingUrl"])){
				$isactive = 'Inactive';
			}
			
			
			if($p_v["AffId"] == 1 && $p_v["SupportDeepUrl"] <> 'YES' && strpos($links_arr["DeepUrlTpl"], '[SITEIDINAFF]') !== false){
				$links_arr["DeepUrlTpl"] = '';
				$links_arr["OutGoingUrl"] = $links_arr["AffDefaultUrl"];
			}

			if(isset($order))
				unset($order);
			if(SID == 'bdg02'){
				if(isset($control['AffId'][$p_v["AffId"]]['ShippingCountry']))
					$shipping_country = $control['AffId'][$p_v["AffId"]]['ShippingCountry'];
			}
			$order = $objProgram->getProgramRank($p_v["ID"]);
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
											"Order" => $order,
											);
			if(SID=='bdg02') {
				$CategoryId = $cateId = $sql ='';
				$categoryExt = trim($p_v['CategoryExt'],"-, \t\n\r\0\x0B");
				$CategoryId = checkCategoryExist($categoryExt,$p_v["AffId"]);
				//存在分类信息的入库
				if($CategoryId){
					$update_sql[$p_v["ID"]]['CategoryId'] = $CategoryId;
				}else{
					$update_sql[$p_v["ID"]]['CategoryId'] = '';
				}
				
				//deal with coupon site policy
//				if($p_v['StatusInAff'] == 'Active' && $p_v['Partnership'] == 'Active')
//				{
//					$coupon_site_policy_count ++;
//					$sql = "select SupportType from program_manual where ProgramId = '{$p_v["ID"]}'";
//					$support_type_flag = $objProgram->objMysql->getFirstRowColumn($sql);
//					if($support_type_flag != 'YES')
//					{
//						switch ($affid)
//						{
//							case 10:
//								preg_match("/Discount Code.*?src=\"(.+?)\"/i",$p_v['PublisherPolicy'],$match_content);
//								$publisher_policy = $match_content[1];
//								if($publisher_policy == 'https://images.awin.com/common/icons/16x16/tick.png')
//									$update_sql[$p_v["ID"]]['SupportType'] = 'All';
//								else if($publisher_policy == 'https://images.awin.com/common/icons/16x16/tick_grey.png')
//									$update_sql[$p_v["ID"]]['SupportType'] = 'Content';
//								else
//								{
//									$coupon_site_policy_error++;
//									$coupon_site_policy .= $p_v['ID'] . ',';
//								}
//							    break;
//							case 49:
//							case 124:
//							case 163:
//							case 196:
//							case 197:
//							case 240:
//							case 557:
//							case 574:
//							case 2001:
//							case 2003:
//								$update_sql[$p_v["ID"]]['SupportType'] = 'All';
//								break;
//							default:
//						}
//					}
//
//				}
				//if($p_v["AffId"] == 1 || $p_v["AffId"] == 2 || $p_v["AffId"] == 6 || $p_v["AffId"] == 10){
				if($p_v["AffId"] == 10){
					if($p_v['SupportType'] == ''){
						$update_sql[$p_v["ID"]]["SupportType"] = 'All';
					}else{
						$update_sql[$p_v["ID"]]["SupportType"] = $p_v['SupportType'];
					}
				}
				
				//TT
				if(in_array($p_v["AffId"], $objProgram->aff_tt)){
					if($p_v['PublisherPolicy'] == 'disallowed'){
						$update_sql[$p_v["ID"]]["SupportType"] = 'Content';
					}elseif($p_v['PublisherPolicy'] == 'limited'){
						$update_sql[$p_v["ID"]]["SupportType"] = 'All';
					}else{
						$update_sql[$p_v["ID"]]["SupportType"] = 'All';
					}				
				}
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
			$i++;
			
			if($is_debug){
				print_r($update_sql);
				exit;
			}
			if(count($update_sql) >= 100){
				$objProgram->insertProgramIntell($update_sql);
				$update_sql = array();
				//echo "\t($i)";
			}
			
		}	//program foreach
	}
	if(count($update_sql)){
		$objProgram->insertProgramIntell($update_sql);
		unset($update_sql);
	}

	if($coupon_site_policy_error != 0)
	{
		$mail_body = "Error ids are {$coupon_site_policy}error NO. and total NO. are {$coupon_site_policy_error}/{$coupon_site_policy_count}";
		AlertEmail::SendAlert('Coupon site policy Error in '.$affid,'mcskyding@meikaitech.com,stanguan@meikaitech.com');
	}
	echo "\t___end($i)";
	//exit;
	if($i == 0){
		echo "\tError($affid)";
		$non_comm_format[] = $affid;
	}
	
	echo "\r\n";
	
	if(SID == 'bdg02' && $affid == 2032){	
		$sql = "UPDATE r_domain_program a INNER JOIN program_intell b ON a.pid = b.programid
				SET a.affdefaulturl = b.affdefaulturl, a.deepurltpl = b.deepurltpl, a.LastUpdateTime = '".date("Y-m-d H:i:s")."'
				WHERE b.isactive = 'active' AND b.affid = 2032";
		$objProgram->objMysql->query($sql);
	}
	//exit;
}//affiliate foreach

$sql = "UPDATE program_manual a INNER JOIN program b ON a.programid = b.id SET a.statusinbdg = 'Unknown' WHERE a.statusinbdg = 'Active' AND b.affid NOT IN (223,191) AND b.statusinaff = 'active' AND b.partnership = 'active'";
$objProgram->objMysql->query($sql);

$sql = "UPDATE program_manual a INNER JOIN program b ON a.programid = b.id SET a.statusinbdg = 'Unknown' WHERE a.statusinbdg = 'Inactive' AND b.affid NOT IN (223,191) AND (b.statusinaff <> 'active' OR b.partnership <> 'active')";
$objProgram->objMysql->query($sql);

	$sql = "UPDATE r_domain_program a INNER JOIN program_intell b ON a.pid = b.programid
			SET a.affdefaulturl = b.affdefaulturl, a.deepurltpl = b.deepurltpl
			WHERE b.isactive = 'active' AND a.affdefaulturl = '' AND (b.affdefaulturl <> '' OR b.deepurltpl <> '')";
	$objProgram->objMysql->query($sql);
	
	$sql = "UPDATE r_domain_program a INNER JOIN program_intell b ON a.pid = b.programid
			SET a.deepurltpl = b.deepurltpl
			WHERE b.isactive = 'active' AND a.IsHandle = '0' AND a.deepurltpl = '' AND b.deepurltpl <> '' AND b.SupportDeepUrl = 'YES'";
	$objProgram->objMysql->query($sql);
	


echo "###################";
print_r($non_comm_format);
echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";

/*echo "checkDomainProgramRel start\r\n";
$did_arr = $objProgram->getNeedCheckDomain($date);
$cnt = $objProgram->checkDomainProgramRel($did_arr);
echo "checkDomainProgramRel end, update ($cnt) rel\r\n";*/

/*if(!$nottoredis){
	include_once("to_redis.php");
}*/
exit;


?>