<?php
include_once(dirname(__FILE__) . "/etc/const.php");
$objMysql = New MysqlExt();
$objProgram = New Program();

$objMysql->query("SET NAMES latin1");

$site = trim($_GET["site"]);
$domain = trim($_GET["domain"]);
$page = intval($_GET["page"]);
$size = intval($_GET["size"]);
$type = trim($_GET["type"]);
$debug = trim($_GET["debug"]);
$key = trim($_GET["key"]);
$cc = trim($_GET["cc"]);
$need_name = 1; //intval($_GET["name"]);
$need_fake = intval($_GET["fake"]);
$need_all_aff = intval($_GET["all_aff"]);
$need_commission = intval($_GET["commission"]);
$need_affurl = intval($_GET["affurl"]);
$allinfo = intval($_GET["allinfo"]);
$pure = intval($_GET["pure"]);

$dd_bugg = intval($_GET["dd_bugg"]);
$dd_did = intval($_GET["did"]);
$dd = trim($_GET["dd"]);

$si = trim($_GET["si"]); 

if($key == "362aa4fd1ce9c73a3915f73bf568fb2f"){//BCG
	//$cc = "all";
	$acc_arr = array();
	$acc_arr['id'] = 9999999;
}else{
	$sql = "SELECT id, alias FROM publisher_account WHERE apikey = '".addslashes($key)."' AND STATUS = 'active' limit 1";
	$acc_arr = array();
	$acc_arr = $objProgram->objMysql->getFirstRow($sql);
	
	if(!count($acc_arr))exit;
}

$main_site = array("us","au","ca","de","uk","fr");
$other_site = array('at','ch','cn','dk','es','hk','id','ie','in','it','jp','my','nl','nz','ph','se','sg','th','tw');

/*$acc_arr["alias"] = str_ireplace("cs", "", $acc_arr["alias"]);
if(empty($cc)) $cc = $acc_arr["alias"];*/
$cc = strtolower($cc);
if(empty($cc)) $cc = '';
if($cc == 'all') $cc = '';
$db_used = "domain_outgoing_default_other";
	
	
if($domain){
	$return_val = "no";
	
	$domain_arr = array();
	$domain_arr = $objProgram->getDomainByHomepage($domain, "fi");

	if($debug ==1){
		print_r($domain_arr);exit;
	}
	
	if(count($domain_arr["domain"])){
		if($need_all_aff){
			$sql = "select id from domain where domain = '".addslashes(current($domain_arr["domain"]))."' and SupportAff = 'yes'";
			$domain_id = array_keys($objMysql->getRows($sql, "id"));
			
			if($cc){
				//$sql .= " and b.site = '".addslashes($cc)."' and ((b.key NOT LIKE '%|%' AND b.limitaccount <> '{$acc_arr["id"]}') OR b.key LIKE '%|{$acc_arr["id"]}%')";
				$shipping_cc = " and a.site = '".addslashes($cc)."' ";
			}
			
			if(count($domain_id)){
				$sql = "SELECT c.AllowInaccuratePromo, c.AllowNonaffPromo, c.AllowNonaffCoupon, 0 as isdefault, a.did, b.programid, b.affid, c.name as p_name, b.idinaff, d.name as aff_name, a.DeepUrlTpl as support_deep, '' as limitaccount from r_domain_program a inner join program_intell b on a.pid = b.programid inner join program c on b.programid = c.id inner join wf_aff d on c.affid = d.id where b.isactive = 'active' and a.status = 'active' and a.did in (".implode(",", $domain_id).")";
				//$sql .= "union SELECT a.did, b.programid, b.affid, c.name as p_name, b.idinaff, d.name as aff_name from r_domain_program_copy a inner join program_intell b on a.pid = b.programid inner join program c on b.programid = c.id inner join wf_aff d on c.affid = d.id where b.isactive = 'active' and a.status = 'active' and a.did in (".implode(",", $domain_id).")";
				$sql .= "union SELECT c.AllowInaccuratePromo, c.AllowNonaffPromo, c.AllowNonaffCoupon, 1 as isdefault, a.did, b.programid, b.affid, c.name as p_name, b.idinaff, d.name as aff_name, a.DeepUrlTemplate as support_deep, a.limitaccount from $db_used a inner join program_intell b on a.pid = b.programid inner join program c on b.programid = c.id inner join wf_aff d on c.affid = d.id where b.isactive = 'active' $shipping_cc and a.did in (".implode(",", $domain_id).")";
				$all_p_arr = array();
				$all_p_arr = $objMysql->getRows($sql);
				//print_r($all_p_arr);
				$aff_info = array();
				foreach($all_p_arr as $v){
					//$aff_info[$v["programid"]] = array("affid"=>$v["affid"],"aff_name"=>$v["aff_name"],"p_idinaff"=>$v["idinaff"],"p_name"=>$v["p_name"]);
					if(strlen($v['limitaccount'])){						
						$limit_acc = explode(",",$v['limitaccount']);						
						if(in_array($acc_arr['id'], $limit_acc)){
							unset($aff_info[$v["programid"]]);		
							continue;							
						}
					}
					$aff_info[$v["programid"]] = array("isdefault" => $v["isdefault"], "affid"=>$v["affid"],"aff_name"=>$v["aff_name"],"p_name"=>$v["p_name"], "idinaff" => $v["idinaff"]);
				}
				if($debug == 1){
					print_r($aff_info);
				}
				//print_r($aff_info);
				
				if(count($aff_info)){
					if($type == "json"){
						echo json_encode($aff_info);
					}else{
						foreach($aff_info as $v){
							echo "{$v["aff_name"]}|{$v["p_idinaff"]}";					
							echo "\r\n<br />";
						}
					}
					exit;
				}else{
					$return_val = "no";
				}		
			}else{
				$return_val = "no";
			}
		}else{		
			$sql = "SELECT a.domain, c.affid, c.idinaff, b.isfake, d.name as aff_name FROM domain a INNER JOIN $db_used b ON a.id = b.did 
					INNER JOIN program_intell c ON b.pid = c.programid left join wf_aff d on c.affid = d.id
					where c.isactive = 'active' and a.domain = '".addslashes(current($domain_arr["domain"]))."' and a.SupportAff = 'yes'";
			
			if($cc){
				$sql .= " and b.site = '".addslashes($cc)."'";
			}		
			$tmp_arr = array();
			$tmp_arr = $objMysql->getFirstRow($sql);
			
			if(count($tmp_arr)){
				if(in_array($tmp_arr["affid"], $objProgram->sub_aff)){
					$return_val = "sub";
				}elseif($tmp_arr["isfake"] == "YES"){
					$return_val = "fake";
				}else{
					$return_val = "yes";
				}			
			}
		}
		
		if($debug == 1){
			print_r($tmp_arr);
		}
	}	
	
	echo $return_val;
	exit;
	
}else{
	if($page < 1) $page = 1;
	if($size < 1 || $size > 1000) $size = 1000;
	
	$sql = "SELECT c.id AS domainid, c.domain, b.supportcoupon, b.supportloyalty FROM r_store_domain a INNER JOIN store b ON a.storeid = b.id INNER JOIN domain c ON a.domainid = c.id WHERE b.SupportType = 'Content' OR b.SupportType = 'None'";
	$no_coupon_did = array();
	$no_coupon_did = $objProgram->objMysql->getRows($sql, "domainid");
		
	//$sql = "SELECT a.domain, b.pid, c.affid, c.supportdeepurl, c.commissionvalue, c.commissionused, c.commissiontype FROM domain a INNER JOIN domain_outgoing_default b ON a.id = b.did INNER JOIN program_intell c ON b.pid = c.programid ORDER BY a.domain LIMIT " . ($page - 1) * $size . ", " . $size;
	$sql = "SELECT count(DISTINCT a.id) 
				FROM domain a INNER JOIN $db_used b ON a.id = b.did INNER JOIN program_intell c ON b.pid = c.programid inner join program d on c.programid = d.id where c.isactive = 'active' and a.domain not like '%/%' and a.SupportAff = 'yes'";
	
	if($cc){
		$sql .= " and (b.site = '".addslashes($cc)."' OR b.site = 'global')";
	}
	
	if(count($no_coupon_did)){
		$sql .= " and a.id not in (".implode(',', array_keys($no_coupon_did)).")";
	}
	
	$cnt1 = $objMysql->getFirstRowColumn($sql);
	
	if($debug) echo $sql."<br />######<br />";
	
	############
	$sql = "SELECT count(DISTINCT a.id) 
				FROM domain a INNER JOIN redirect_default b ON a.id = b.did INNER JOIN program_intell c ON b.pid = c.programid inner join program d on c.programid = d.id where c.isactive = 'active' and a.domain not like '%/%' and a.SupportAff = 'yes' and c.affid in (1,2,6)";
	
	if($cc){
		$sql .= " and (b.site = '".addslashes($cc)."' OR b.site = 'global')";
	}	
	$cnt2 = $objMysql->getFirstRowColumn($sql);
	##############
	
	$cnt = $cnt1 + $cnt2;
	
	if($debug) echo $sql."<br />######<br />";
	//c.commissionvalue 
	
	
	$sql = "SELECT DISTINCT a.id as id
				FROM domain a INNER JOIN $db_used b ON a.id = b.did INNER JOIN program_intell c ON b.pid = c.programid inner join program d on c.programid = d.id where c.isactive = 'active' and a.domain not like '%/%' and a.SupportAff = 'yes'";
	
	if($cc){
		$sql .= " and (b.site = '".addslashes($cc)."' OR b.site = 'global')";
	}
	if($dd){
		$sql .= " and a.domain = '".addslashes($dd)."'";
	}
	
	if(count($no_coupon_did)){
		$sql .= " and a.id not in (".implode(',', array_keys($no_coupon_did)).")";
	}
	
	$sql .= " ORDER BY a.domain LIMIT " . ($page - 1) * $size . ", " . $size;
	
	$did_arr = array();
	$did_arr = $objMysql->getRows($sql,'id');
	$tmp_arr = array();
	
	if($did_arr){
		$sql = "SELECT a.id, a.domain, c.affid, c.programid, b.isfake, b.site, CASE b.site WHEN 'global' THEN 0 ELSE 1 END AS sort
					FROM domain a INNER JOIN $db_used b ON a.id = b.did INNER JOIN program_intell c ON b.pid = c.programid inner join program d on c.programid = d.id where c.isactive = 'active' and a.domain not like '%/%' and a.SupportAff = 'yes'";
		
		if($cc){
			$sql .= " and (b.site = '".addslashes($cc)."' OR b.site = 'global')";
		}
		if($dd){
			$sql .= " and a.domain = '".addslashes($dd)."'";
		}
		
		if(count($no_coupon_did)){
			$sql .= " and a.id not in (".implode(',', array_keys($no_coupon_did)).")";
		}
		
		if(count($did_arr)) {
			$sql .= " and a.id in (".implode(',', array_keys($did_arr)).")";
		}
		
		#####################
		
		$sql .= "union SELECT a.id, a.domain, c.affid, c.programid, b.isfake, b.site, CASE b.site WHEN 'global' THEN 0 ELSE 1 END AS sort
					FROM domain a INNER JOIN redirect_default b ON a.id = b.did INNER JOIN program_intell c ON b.pid = c.programid inner join program d on c.programid = d.id where c.isactive = 'active' and a.domain not like '%/%' and a.SupportAff = 'yes' and c.affid in (1,2,6)";
		
		if($cc){
			$sql .= " and (b.site = '".addslashes($cc)."' OR b.site = 'global')";
		}
		if($dd){
			$sql .= " and a.domain = '".addslashes($dd)."'";
		}
		
		
		if(count($did_arr)) {
			$sql .= " and a.id in (".implode(',', array_keys($did_arr)).")";
		}
		
		####################
		
		$sql .= " ORDER BY domain, sort ";
	
		if($debug) echo $sql;
		$tmp_arr = array();
		$tmp_arr = $objMysql->getRows($sql, "domain");
	}
	
	if($debug){
		//echo $sql;
		if($dd) print_r($tmp_arr);
	}
	//echo count($tmp_arr);
	//$aff_arr = getAllAffiliateBDG($objMysql);
	//print_r($tmp_arr);
	$program_id = array();
	$domain_id = array();
	foreach($tmp_arr as $k => $v){
		//$tmp_arr[$k]["aff_name"] = $aff_arr[$v["affid"]]["Name"]."({$v["affid"]})";
		//$tmp_arr[$k]["commission"] = preg_match("/\\]\\|\d+\\|(.*)/", $v["commissionvalue"], $m) ? $m[1] : "";
		if($dd && $debug){
			echo $v["affid"];
			//print_r($objProgram->sub_aff);
			if(in_array($v["affid"], $objProgram->sub_aff)){
				echo "#######";
			}
		}
	
		if(in_array($v["affid"], $objProgram->sub_aff)){
			$tmp_arr[$k]["aff type"] = "sub";
		}elseif($need_fake && $v["isfake"] == "YES"){
			$tmp_arr[$k]["aff type"] = "fake";
		}else{
			$tmp_arr[$k]["aff type"] = "main";
		}
		unset($tmp_arr[$k]["isfake"]);
		unset($tmp_arr[$k]["affid"]);
		unset($tmp_arr[$k]["commissionvalue"]);
		unset($tmp_arr[$k]["sort"]);

		$domain_id[$v["id"]] = $v["id"];		
		if($need_name)	$program_id[$v["programid"]] = $v["programid"];
		else unset($tmp_arr[$k]["programid"]);
		
		$sql = "select domain from domain where id = {$v['id']}";
		$objMysql->query("SET NAMES utf8");
		$new_domain = $objMysql->getFirstRowColumn($sql);
		//print_r($new_domain);
		unset($tmp_arr[$k]);
		$v['domain'] = $new_domain;
		$tmp_arr[$new_domain] = $v;
	}	
	$objMysql->query("SET NAMES latin1");
	
	if($need_name && count($program_id)){
		$sql = "SELECT id, name, idinaff, CommissionExt from program where id in (".implode(",", $program_id).")";
		$name_arr = array();
		$name_arr = $objMysql->getRows($sql, "id");
		foreach($tmp_arr as $k => $v){
			if(isset($name_arr[$v["programid"]])){
				$tmp_arr[$k]["p_name"] = $name_arr[$v["programid"]]["name"];
				$tmp_arr[$k]["idinaff"] = $name_arr[$v["programid"]]["idinaff"];
				if($need_commission)
					$tmp_arr[$k]["commission"] = $name_arr[$v["programid"]]["CommissionExt"];
				
					
			}
			//unset($tmp_arr[$k]["programid"]);
		}		
	}
	
	//if($debug == 1){
		if($need_all_aff && count($domain_id)){
			if($cc){
				$shipping_cc_2 = " and (a.site = '".addslashes($cc)."' OR a.site = 'global')";			
			}
			
			/*if($pure && $cc == "fr"){
				$shipping_cc = "and (b.shippingcountry LIKE '%fr%' and b.affid NOT IN (160,223,191,578))";
				$shipping_cc_2 = " and (a.site = '".addslashes($cc)."')";
			}*/
			
			$sql = "SELECT a.did, a.isfake, b.programid, b.affid, c.name as p_name, b.idinaff, d.name as aff_name, a.DeepUrlTpl as support_deep, a.AffDefaultUrl, b.ShippingCountry 
					, c.CommissionExt, c.Description as `desc`, c.TermAndCondition as terms, c.CouponCodesPolicyExt
					from r_domain_program a inner join program_intell b on a.pid = b.programid inner join program c on b.programid = c.id inner join wf_aff d on c.affid = d.id
					where b.isactive = 'active' and a.status = 'active' and a.did in (".implode(",", $domain_id).")";
			//$sql .= "union SELECT a.did, a.isfake, b.programid, b.affid, c.name as p_name, b.idinaff, d.name as aff_name from r_domain_program_copy a inner join program_intell b on a.pid = b.programid inner join program c on b.programid = c.id inner join wf_aff d on c.affid = d.id where b.isactive = 'active' and a.status = 'active' and a.did in (".implode(",", $domain_id).")";
			$sql .= "union SELECT a.did, a.isfake, b.programid, b.affid, c.name as p_name, b.idinaff, d.name as aff_name, a.DeepUrlTemplate as support_deep, a.AffiliateDefaultUrl as AffDefaultUrl, b.ShippingCountry
					, c.CommissionExt, c.Description as `desc`, c.TermAndCondition as terms, c.CouponCodesPolicyExt
					from $db_used a inner join program_intell b on a.pid = b.programid inner join program c on b.programid = c.id inner join wf_aff d on c.affid = d.id 
					where b.isactive = 'active' $shipping_cc_2 and a.did in (".implode(",", $domain_id).")";
			$all_p_arr = array();
			$all_p_arr = $objMysql->getRows($sql);
			
			if($dd && $debug){
				echo $sql;
				print_r($all_p_arr);
				
			}
			
			$aff_info = array();
			foreach($all_p_arr as $v){				
				$aff_info[$v["did"]][$v["programid"]] = array("affid"=>$v["affid"],"aff_name"=>$v["aff_name"],"p_idinaff"=>$v["idinaff"],"p_name"=>$v["p_name"],"isfake"=>$v["isfake"],"support_deep"=>strlen($v["support_deep"])?"YES":"NO","country"=>$v["ShippingCountry"]);
				$aff_info[$v["did"]][$v["programid"]]["AllowInaccuratePromo"] = 'NO';
				$aff_info[$v["did"]][$v["programid"]]["AllowNonaffPromo"] = 'NO';
				$aff_info[$v["did"]][$v["programid"]]["AllowNonaffCoupon"] = 'NO';
				
				$aff_info[$v["did"]][$v["programid"]]["CouponCodesPolicy"] = $v["CouponCodesPolicyExt"];
				
				if($need_affurl){
					$aff_info[$v["did"]][$v["programid"]]["affurl"] = $v["AffDefaultUrl"];
					$aff_info[$v["did"]][$v["programid"]]["afftpl"] = $v["support_deep"];
				}
			
				if($allinfo){
					$aff_info[$v["did"]][$v["programid"]]["commission"] = $v["CommissionExt"];
					$aff_info[$v["did"]][$v["programid"]]["description"] = $v["desc"];
					$aff_info[$v["did"]][$v["programid"]]["terms"] = $v["terms"];
				}
			}
			
			foreach($tmp_arr as $k => $v){
				if(isset($aff_info[$v["id"]])){
					$tmp_arr[$k]["aff_info"] = $aff_info[$v["id"]];
				}				
			}
		}
	//}
	
	if($type == "json"){
		echo json_encode(array("total" => $cnt, "page" => $page, "count" => count($tmp_arr), "return" => $tmp_arr));
	}else{
		echo "domain\taff type";
		if($need_name){		
			echo "\tname";
		}
		echo "\r\n";
		foreach($tmp_arr as $v){
			echo "{$v["domain"]}\t{$v["aff type"]}";
			if($need_name){
				echo "\t{$v["name"]}";
			}
			echo "\r\n";
		}
	}
}
exit;
		

function getAllAffiliateBDG($objMysql, $id_arr = array()){
	$data = array();
	$id_list = "";
	if(count($id_arr)){
		foreach($id_arr as &$v) $v = intval($v);  
		$id_list = " AND ID IN ('" . implode("','", $id_arr) . "')";
	}
	$sql = "SELECT ID, Name, ShortName, Domain, AffiliateUrlKeywords, AffiliateUrlKeywords2, DeepUrlParaName, SupportDeepUrl, SubTracking, SubTracking2, ProgramCrawled, IsInHouse, ImportanceRank FROM wf_aff WHERE isactive = 'yes' $id_list";
	$data = $objMysql->getRows($sql, "ID");
	return $data;
}

?>
