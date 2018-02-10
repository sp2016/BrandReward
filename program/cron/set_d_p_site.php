<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
include_once(INCLUDE_ROOT . "func/func.php");


echo "<< Start @ ".date("Y-m-d H:i:s")." >>\r\n";

define("NO_MYSQL_CACHE", false);

$objProgram = New Program();

$is_debug = $all = $fake =  $redis = $self = $debug_sql = $allactive = false;
$date = date('Y-m-d H:i:s');
$check_date = date("Y-m-d H:i", strtotime(" -6 minutes"));
$check_date_to =  date("Y-m-d H:i");
$did_arr = array();
$site_arr = array();
if(isset($_SERVER["argc"]) && $_SERVER["argc"] > 1)
{
	foreach($_SERVER["argv"] as $v){
		$tmp = explode("=", $v);		
		if($tmp[0] == "--did"){			
			$did_arr = explode(",", $tmp[1]);			
		}elseif($tmp[0] == "--debug"){
			$is_debug = true;
		}elseif($tmp[0] == "--all"){
			$all = true;
		}elseif($tmp[0] == "--site"){
			$site_arr = explode(",", $tmp[1]);;
		}elseif($tmp[0] == "--redis"){
			$redis = true;
		}elseif($tmp[0] == "--self"){
			$self = true;
		}elseif($tmp[0] == "--sql"){
			$debug_sql = true;
		}elseif($tmp[0] == "--allactive"){
			$allactive = true;
		}
		
	}			
}
if($debug_sql){
	define("SQL_CONFIG", 1);		
}

$cnt = 0;

echo "split\r\n";

if(!count($site_arr)){
	$site_arr = array("us", "uk", "au", "de", "fr", "ca");
}

if(!count($did_arr) && !$all){
	$ddd_arr = array();
	
	if($allactive){
		$sql = "select distinct did from domain_outgoing_default_site";
		$did_arr = array_keys($objProgram->objMysql->getRows($sql, "did"));
	}else{
		/*$sql = "select programid, domainname, merchantdomain from base_program_store_relationship where ps_edit_time >= '".date("Y-m-d H:i", strtotime(" -5 minutes"))."'";
		$p_arr = $objProgram->objMysql->getRows($sql);
			
		
		if(!count($p_arr)){
			$where_str = " LastUpdateTime >= '".date("Y-m-d H:i:s", strtotime(" -17 minutes"))."'";		
		}else{
			$pid_arr = array();		
			$domain_arr = array();
			foreach($p_arr as $v){
				if($v["domainname"])
					$domain_arr[$v["domainname"]] = $v["domainname"];
				if($v["merchantdomain"])
					$domain_arr[$v["merchantdomain"]] = $v["merchantdomain"];	
				$pid_arr[$v["programid"]] = $v["programid"];
			}
			if(count($domain_arr)){
				$sql = "select id from domain where domain in ('".implode("','", $domain_arr)."')";
				$ddd_arr = array_keys($objProgram->objMysql->getRows($sql, "id"));
			}
			
			$where_str = " LastUpdateTime >= '".date("Y-m-d H:i:s", strtotime(" -10 minutes"))."' or pid IN (".implode(",", $pid_arr).")"; 
		}*/
			
		$sql = "select did FROM `r_domain_program` WHERE LastUpdateTime >= '$check_date' AND LastUpdateTime < '$check_date_to'";		
		$did_arr = array_keys($objProgram->objMysql->getRows($sql, "did"));
		echo 'r_domain_program:'.count($did_arr)."\r\n";	
		
		$tmp_ddd_arr = array();
		$sql = "select domainid FROM `r_domain_program_ctrl` WHERE LastUpdateTime >= '$check_date' AND LastUpdateTime < '$check_date_to'";
		$tmp_ddd_arr = array_keys($objProgram->objMysql->getRows($sql, "domainid"));
		echo 'r_domain_program_ctrl:'.count($tmp_ddd_arr)."\r\n";
		
		if(count($tmp_ddd_arr)) $did_arr = array_merge($did_arr, $tmp_ddd_arr);
		
		$tmp_ddd_arr = array();
		$sql = "SELECT a.did FROM `r_domain_program` a INNER JOIN program_intell b ON a.pid = b.programid WHERE b.LastChangeTime >= '$check_date' AND b.LastChangeTime < '$check_date_to' and a.status = 'active'";
		//$sql .= " and b.isactive = 'active'";		
		$tmp_ddd_arr = array_keys($objProgram->objMysql->getRows($sql, "did"));
		echo 'recent update program did:'.count($tmp_ddd_arr)."\r\n";
		
		if(count($tmp_ddd_arr)) $did_arr = array_merge($did_arr, $tmp_ddd_arr);	
		
		$tmp_ddd_arr = array();
		$sql = "SELECT b.did FROM program_manual a INNER JOIN r_domain_program b ON a.programid = b.pid WHERE b.status = 'active' AND ((a.LastUpdateTime >= '$check_date' AND a.LastUpdateTime < '$check_date_to') OR (a.AddTime >= '$check_date' AND a.AddTime < '$check_date_to')) ";
		$tmp_ddd_arr = array_keys($objProgram->objMysql->getRows($sql, "did"));
		echo 'program_ctrl related did:'.count($tmp_ddd_arr)."\r\n";
		
		if(count($tmp_ddd_arr)) $did_arr = array_merge($did_arr, $tmp_ddd_arr);
		
		$tmp_ddd_arr = array();
		$sql = "SELECT did FROM domain_outgoing_default_site a INNER JOIN program_intell b ON a.pid = b.programid WHERE b.isactive = 'inactive' ";
		$tmp_ddd_arr = array_keys($objProgram->objMysql->getRows($sql, "did"));
		echo 'inactive programid related did:'.count($tmp_ddd_arr)."\r\n";
		
		if(count($tmp_ddd_arr)) $did_arr = array_merge($did_arr, $tmp_ddd_arr);
		
		/*if(count($did_arr)){
			$sql = "select domainname from domain where id in ('".implode("','", $did_arr)."')";
			$domain_name = array_keys($objProgram->objMysql->getRows($sql, "domainname"));
			if(count($domain_name)){
				$sql = "select id from domain where domainname in ('".implode("','", $domain_name)."')";
				$did_arr = array_keys($objProgram->objMysql->getRows($sql, "id"));
			}
		}*/		
		$did_arr = array_unique($did_arr);
		
		print_r($did_arr);
	}
		
	echo "update did: ".count($did_arr)."\r\n";
	
	if(!count($did_arr)){
		echo "NO did need update. \r\n";
		echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";
		exit;
	}
}

//$site_arr = array("au","de");
foreach($site_arr as $site){
	echo "$site\t";
	$objProgram->base_rel = array();	
	
	$i = 0;
	$pos = 0;
	while(1){
		$ww = '';
		if(count($did_arr)){
			$ww = "and id in (".implode(",", $did_arr).")";
		}
		//$sql = "select id from domain where 1=1 $ww order by id limit " . $i * 1000 . ", 1000";
		$qq = '';
		if($all){
			$qq = "and id in (select did from r_domain_program where status = 'active')";
		}
		$sql = "select id from domain where domain not like '%/%' and id > $pos $qq $ww ORDER BY id LIMIT 1000";
		$check_did = array();
		$check_did = $objProgram->objMysql->getRows($sql, "id");
	
		if(!count($check_did)) break;
		echo "p(" . $i++ . ")";
		
		$tmp = array_keys($check_did);
		$tmp_pos = end($tmp);
		if($tmp_pos > $pos) $pos = $tmp_pos;
		
		/*$sql = "select did as id from r_domain_program where did in (".implode(",", array_keys($check_did)).") and lastupdatetime >= '".date("Y-m-d H:is", strtotime("-1 days"))."'";
		$ff_ids = array();
		$ff_ids = $objProgram->objMysql->getRows($sql, "id");*/
				
		$cnt += $objProgram->checkDomainProgramRel_Sp($check_did, $site);
	}
	if($is_debug){		
		exit;
	}
	
	if(count($did_arr)){
		$ww = "and did in (".implode(",", $did_arr).")";
		$sql = "select count(*) from domain_outgoing_default_site where site = '$site' and lastupdatetime < '$date' $ww ";
		$del_cnt = $objProgram->objMysql->getFirstRowColumn($sql);
		
		echo "del :$del_cnt\r\n";
		if($del_cnt < 1000 && $del_cnt > 0){
			$sql = "select id, `key`, pid, did from domain_outgoing_default_site where site = '$site' and lastupdatetime < '$date' $ww ";
			$del_did_arr = $objProgram->objMysql->getRows($sql);
			$del_did = array();
			foreach($del_did_arr as $v){
				if(strpos($v["key"], "/") === false){
					$sql = "insert ignore into domain_outgoing_default_changelog_site(site, DID, `Key`, ProgramFrom, ProgramTo, Changetime) 
							values('$site', '".intval($v["did"])."', '".addslashes($v["key"])."', '".intval($v["pid"])."', 0, '".date("Y-m-d H:i:s")."')";
					$objProgram->objMysql->query($sql);
				}
				$del_did[$v["did"]] = $v["did"];
			}
			
			if(count($del_did)){
				$ww = "and did in (".implode(",", $del_did).")";
				$sql = "delete from domain_outgoing_default_site where site = '$site' and lastupdatetime < '$date' $ww ";		
				$objProgram->objMysql->query($sql);
				
				echo "########$sql\r\n########\r\n";
				
				$sql = "delete from domain_outgoing_default_other where site = '$site' and lastupdatetime < '$date' $ww ";		
				$objProgram->objMysql->query($sql);
			}
		//}elseif($del_cnt == 0){
			//$sql = "delete from domain_outgoing_default_other where site = '$site' and lastupdatetime < '$date' $ww ";		
			//$objProgram->objMysql->query($sql);
		}elseif($del_cnt >= 1000){
			$mail_body = "set_d_p_site del : $del_cnt";
			AlertEmail::SendAlert('set_d_p_site del :'.$del_cnt, 'stanguan@meikaitech.com');
		}
	}elseif($all){
		$sql = "select count(*) from domain_outgoing_default_site where site = '$site' and lastupdatetime < '$date' ";
		$del_cnt = $objProgram->objMysql->getFirstRowColumn($sql);
		
		echo "del :$del_cnt\r\n";
		$sql = "delete from domain_outgoing_default_site where site = '$site' and lastupdatetime < '$date' ";
		$objProgram->objMysql->query($sql);
		
		$sql = "delete from domain_outgoing_default_other where site = '$site' and lastupdatetime < '$date' ";		
		$objProgram->objMysql->query($sql);
	}
}

if(!$all && count($did_arr)){
	$did_arr = array_flip($did_arr);
	echo "other:";
	echo $objProgram->checkDomainProgramRelCountry($did_arr) . "\r\n";
}


//set tm policy
/*echo 'set tm policy'.date("Y-m-d H:i:s")."\r\n";
$sql = "select did, site from domain_outgoing_default_changelog_site where changetime > '$date'";
$tmp_arr = $objProgram->objMysql->getRows($sql);
$check_did_site = array();
foreach($tmp_arr as $v){
	$check_did_site[$v['site']][$v['did']] = $v['did'];
}
foreach($check_did_site as $site => $check_did){
	$objProgram->setDomainTMPolicy($check_did, $site);
}*/

echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";
exit;



?>