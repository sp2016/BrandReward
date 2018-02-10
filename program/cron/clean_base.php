<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
include_once(INCLUDE_ROOT . "func/func.php");
include_once(INCLUDE_ROOT . "func/nodejs.php");

echo "<< Start @ ".date("Y-m-d H:i:s")." >>\r\n";

$cmd = 'ps aux | grep grep -v | grep /bin/sh -v | grep /home/bdg/program/cron/clean_base.php -c';
$xx = intval(system($cmd));
if($xx > 1){
	echo "process running. exit @ ".date("Y-m-d H:i:s")."\r\n";
	exit;
}

$cmd = 'ps aux | grep grep -v | grep /bin/sh -v | grep set_d_p_site.php -c';
$xx = intval(system($cmd));
if($xx > 0){
	echo "process running. exit @ ".date("Y-m-d H:i:s")."\r\n";
	exit;
}


$objProgram = New Program();

$date_now = date("Y-m-d H:i:s");

$full_check = false;
if(date("G") == 4 && intval(date("i")) > 40){
	$full_check = true;
}

$db_arr = array("", "_au", "_uk", "_ca", "_us", "_de", "_fr");
foreach($db_arr as $_db){
	$db_arr = array("", "_au", "_uk", "_ca", "_us", "_de", "_fr");
	$sql = "SELECT a.pid, b.domain FROM r_domain_program a INNER JOIN domain b ON a.did = b.id 
			INNER JOIN base_program_store_relationship{$_db} c ON a.pid = c.programid AND c.domainname = b.domain
			WHERE a.`status` = 'inactive' AND c.status = 'active'";
	$tmp_arr = $objProgram->objMysql->getRows($sql);	
	foreach($tmp_arr as $v){
		$sql = "update base_program_store_relationship{$_db} set status = 'Inactive', ps_edit_time = '".date("Y-m-d H:i:s")."' where programid = {$v["pid"]} and domainname = '".addslashes($v["domain"])."'";
		$objProgram->objMysql->query($sql);	
	}
}


$sql = "SELECT a.did, a.pid, a.affdefaulturl, a.deepurltpl, b.domain FROM `r_domain_program` a inner join domain b on a.did = b.id WHERE a.LastUpdateTime > '" . date("Y-m-d H:i:s", strtotime("-1 hour")) . "' AND a.ishandle = '1' and a.status = 'active'";
$tmp_arr = $objProgram->objMysql->getRows($sql);
foreach($tmp_arr as $v){
	$db_arr = array("", "_au", "_uk", "_ca", "_us", "_de", "_fr");
	foreach($db_arr as $_db){
		//echo "base_program_store_relationship{$_db}\r\n";
		
		$sql = "update base_program_store_relationship{$_db} set AffiliateDefaultUrl = '".addslashes($v["affdefaulturl"])."', DeepUrlTemplate = '".addslashes($v["deepurltpl"])."', ps_edit_time = '".date("Y-m-d H:i:s")."', lastupdatetime = '".date("Y-m-d H:i:s")."' 
				where programid = {$v["pid"]} and domainname = '".addslashes($v["domain"])."'";
		$objProgram->objMysql->query($sql);
	}
	
}

/*$sql = "select id, storeid, programid, AffiliateDefaultUrl, DeepUrlTemplate, `order`, IsFake, `status` from program_store_relationship where status = 'active'
		and storeid in (select storeid from store_merchant_relationship where sitename in ('csde','csau'))
";*/
/*$sql = "select id, storeid, programid, AffiliateDefaultUrl, DeepUrlTemplate, `order`, IsFake, `status` from program_store_relationship where status = 'active'
		and storeid in (select storeid from store_merchant_relationship)";*/
/*$sql = "select id, storeid, programid, AffiliateDefaultUrl, DeepUrlTemplate, `order`, IsFake, `status`, lastupdatetime as ps_edit_time from program_store_relationship where status = 'active'";
$ps_arr = $objProgram->objTaskMysql->getRows($sql, "id");

$sql = "SELECT a.id, a.url, lower(GROUP_CONCAT(distinct b.sitename)) as site FROM store a LEFT JOIN store_merchant_relationship b ON a.id = b.storeid WHERE a.status = 'active' group by a.id";
$store_arr = $objProgram->objTaskMysql->getRows($sql, "id");

echo "\tget store_merchant_relationship end\r\n";

foreach($ps_arr as $v){
	if(isset($store_arr[$v["storeid"]])){
		$store_arr[$v["storeid"]]["site"] = strtolower($store_arr[$v["storeid"]]["site"]);
		//if($store_arr[$v["storeid"]]["site"] == "csde" || $store_arr[$v["storeid"]]["site"] == "csau")
		

		$sql = "insert into base_program_store_relationship (id, storeid, programid, AffiliateDefaultUrl, DeepUrlTemplate, `order`, IsFake, `status`, storeurl, Site, ps_edit_time)
				values({$v["id"]}, {$v["storeid"]}, {$v["programid"]}, '".addslashes($v["AffiliateDefaultUrl"])."', '".addslashes($v["DeepUrlTemplate"])."', 
				{$v["order"]}, '".addslashes($v["IsFake"])."', '".addslashes($v["status"])."', '".addslashes($store_arr[$v["storeid"]]["url"])."', '".addslashes($store_arr[$v["storeid"]]["site"])."', '".addslashes($v["ps_edit_time"])."')
				 ON DUPLICATE KEY UPDATE storeid = {$v["storeid"]}, programid = {$v["programid"]}, AffiliateDefaultUrl = '".addslashes($v["AffiliateDefaultUrl"])."', DeepUrlTemplate = '".addslashes($v["DeepUrlTemplate"])."', `order` = {$v["order"]}, IsFake = '".addslashes($v["IsFake"])."', `status` = '".addslashes($v["status"])."', storeurl = '".addslashes($store_arr[$v["storeid"]]["url"])."', Site = '".addslashes($store_arr[$v["storeid"]]["site"])."', LastUpdateTime = '".date("Y-m-d H:i:s")."', ps_edit_time = '".addslashes($v["ps_edit_time"])."'";
				
		$objProgram->objMysql->query($sql);

		$site = strtolower(str_ireplace("cs", "", $store_arr[$v["storeid"]]["site"]));
		$xx = explode(",", $site);
		foreach($xx as $ss){
			if(!in_array($ss, array("au", "uk", "ca", "us", "de", "fr")))continue;
			
			$sql = "insert into base_program_store_relationship_{$ss} (id, storeid, programid, AffiliateDefaultUrl, DeepUrlTemplate, `order`, IsFake, `status`, storeurl, Site, ps_edit_time)
				values({$v["id"]}, {$v["storeid"]}, {$v["programid"]}, '".addslashes($v["AffiliateDefaultUrl"])."', '".addslashes($v["DeepUrlTemplate"])."', 
				{$v["order"]}, '".addslashes($v["IsFake"])."', '".addslashes($v["status"])."', '".addslashes($store_arr[$v["storeid"]]["url"])."', '".addslashes($store_arr[$v["storeid"]]["site"])."', '".addslashes($v["ps_edit_time"])."')
				ON DUPLICATE KEY UPDATE storeid = {$v["storeid"]}, programid = {$v["programid"]}, AffiliateDefaultUrl = '".addslashes($v["AffiliateDefaultUrl"])."', DeepUrlTemplate = '".addslashes($v["DeepUrlTemplate"])."', `order` = {$v["order"]}, IsFake = '".addslashes($v["IsFake"])."', `status` = '".addslashes($v["status"])."', storeurl = '".addslashes($store_arr[$v["storeid"]]["url"])."', Site = '".addslashes($store_arr[$v["storeid"]]["site"])."', LastUpdateTime = '".date("Y-m-d H:i:s")."', ps_edit_time = '".addslashes($v["ps_edit_time"])."'";
			$objProgram->objMysql->query($sql);
		}
	}
}
echo "\treplace base_program_store_relationship end\r\n";


$sql = "update base_program_store_relationship set status = 'Inactive', ps_edit_time = '".date("Y-m-d H:i:s")."' where lastupdatetime < '$date_now'";
$objProgram->objMysql->query($sql);

$sql = "update base_program_store_relationship_us set status = 'Inactive', ps_edit_time = '".date("Y-m-d H:i:s")."' where lastupdatetime < '$date_now'";
$objProgram->objMysql->query($sql);
$sql = "update base_program_store_relationship_uk set status = 'Inactive', ps_edit_time = '".date("Y-m-d H:i:s")."' where lastupdatetime < '$date_now'";
$objProgram->objMysql->query($sql);
$sql = "update base_program_store_relationship_au set status = 'Inactive', ps_edit_time = '".date("Y-m-d H:i:s")."' where lastupdatetime < '$date_now'";
$objProgram->objMysql->query($sql);
$sql = "update base_program_store_relationship_de set status = 'Inactive', ps_edit_time = '".date("Y-m-d H:i:s")."' where lastupdatetime < '$date_now'";
$objProgram->objMysql->query($sql);
$sql = "update base_program_store_relationship_ca set status = 'Inactive', ps_edit_time = '".date("Y-m-d H:i:s")."' where lastupdatetime < '$date_now'";
$objProgram->objMysql->query($sql);
$sql = "update base_program_store_relationship_fr set status = 'Inactive', ps_edit_time = '".date("Y-m-d H:i:s")."' where lastupdatetime < '$date_now'";
$objProgram->objMysql->query($sql);

echo "\tInactive base_program_store_relationship end\r\n";

$sql = "select programid from program_internal where SupportDeepUrlOut = 'NO'";
$tmp_arr = $objProgram->objMysql->getRows($sql, "programid");
if(count($tmp_arr)){	
	$sql = "update base_program_store_relationship set status = 'Inactive' where isfake = 'yes' and programid in (" . implode(",", array_keys($tmp_arr)) . ")";
	$objProgram->objMysql->query($sql);
	
	$sql = "update base_program_store_relationship_us set status = 'Inactive' where isfake = 'yes' and programid in (" . implode(",", array_keys($tmp_arr)) . ")";
	$objProgram->objMysql->query($sql);
	$sql = "update base_program_store_relationship_uk set status = 'Inactive' where isfake = 'yes' and programid in (" . implode(",", array_keys($tmp_arr)) . ")";
	$objProgram->objMysql->query($sql);
	$sql = "update base_program_store_relationship_au set status = 'Inactive' where isfake = 'yes' and programid in (" . implode(",", array_keys($tmp_arr)) . ")";
	$objProgram->objMysql->query($sql);
	$sql = "update base_program_store_relationship_de set status = 'Inactive' where isfake = 'yes' and programid in (" . implode(",", array_keys($tmp_arr)) . ")";
	$objProgram->objMysql->query($sql);
	$sql = "update base_program_store_relationship_ca set status = 'Inactive' where isfake = 'yes' and programid in (" . implode(",", array_keys($tmp_arr)) . ")";
	$objProgram->objMysql->query($sql);
	$sql = "update base_program_store_relationship_fr set status = 'Inactive' where isfake = 'yes' and programid in (" . implode(",", array_keys($tmp_arr)) . ")";
	$objProgram->objMysql->query($sql);
}
echo "\tSupportDeepUrlOut base_program_store_relationship end\r\n";
*/
if($full_check){
	
	$sql = "select id, storeid, storeurl, programid from base_program_store_relationship where status = 'active'";
	/*if($full_check){
		$sql = "select id, storeid, storeurl, programid from base_program_store_relationship where (programdomains = '' or isnull(programdomains))";
	}else{
		$sql = "select id, storeid, storeurl, programid from base_program_store_relationship where status = 'active'";
	}*/
	$tmp_arr = $objProgram->objMysql->getRows($sql, "id");
	
	//base_program_store_relationship
	//$sql = "TRUNCATE TABLE `base_ps_domain`";
	//$objProgram->objMysql->query($sql);
	
	foreach($tmp_arr as $k => $v){
		$sql = "select affid, domain, shippingcountry from program_intell where programid = {$v["programid"]} and isactive = 'active'";
		$p_arr = array();
		$p_arr =  $objProgram->objMysql->getFirstRow($sql);
		
		if(count($p_arr)){	
			/*$domain_arr = $objProgram->getDomainByHomepage($v["storeurl"], "fi");
			
			$dd = current($domain_arr["domain"]);
			$country = isset($domain_arr["country"]) ? current($domain_arr["country"]) : "";
			
			if(is_array($dd)){
				print_r($domain_arr);
				print_r($dd);
				exit;
			}
			
			$objProgram->insertDomain($dd);
	
			if($country){
				$dd .= "/".$country;
			}*/
			$sql = "update base_program_store_relationship set programdomains = '{$p_arr["domain"]}', shippingcountry = '{$p_arr["shippingcountry"]}' where id = $k";
			$objProgram->objMysql->query($sql);
			
			$sql = "update base_program_store_relationship_us set programdomains = '{$p_arr["domain"]}', shippingcountry = '{$p_arr["shippingcountry"]}' where id = $k";
			$objProgram->objMysql->query($sql);
			$sql = "update base_program_store_relationship_uk set programdomains = '{$p_arr["domain"]}', shippingcountry = '{$p_arr["shippingcountry"]}' where id = $k";
			$objProgram->objMysql->query($sql);
			$sql = "update base_program_store_relationship_de set programdomains = '{$p_arr["domain"]}', shippingcountry = '{$p_arr["shippingcountry"]}' where id = $k";
			$objProgram->objMysql->query($sql);
			$sql = "update base_program_store_relationship_fr set programdomains = '{$p_arr["domain"]}', shippingcountry = '{$p_arr["shippingcountry"]}' where id = $k";
			$objProgram->objMysql->query($sql);
			$sql = "update base_program_store_relationship_ca set programdomains = '{$p_arr["domain"]}', shippingcountry = '{$p_arr["shippingcountry"]}' where id = $k";
			$objProgram->objMysql->query($sql);
			$sql = "update base_program_store_relationship_au set programdomains = '{$p_arr["domain"]}', shippingcountry = '{$p_arr["shippingcountry"]}' where id = $k";
			$objProgram->objMysql->query($sql);
					
			//$objProgram->insertDomain($dd);
			
		}else{
			$sql = "update base_program_store_relationship set status = 'Inactive'  where id = $k";
			$objProgram->objMysql->query($sql);
			
			$sql = "update base_program_store_relationship_us set status = 'Inactive'  where id = $k";
			$objProgram->objMysql->query($sql);
			$sql = "update base_program_store_relationship_uk set status = 'Inactive'  where id = $k";
			$objProgram->objMysql->query($sql);
			$sql = "update base_program_store_relationship_de set status = 'Inactive'  where id = $k";
			$objProgram->objMysql->query($sql);
			$sql = "update base_program_store_relationship_fr set status = 'Inactive'  where id = $k";
			$objProgram->objMysql->query($sql);
			$sql = "update base_program_store_relationship_ca set status = 'Inactive'  where id = $k";
			$objProgram->objMysql->query($sql);
			$sql = "update base_program_store_relationship_au set status = 'Inactive'  where id = $k";
			$objProgram->objMysql->query($sql);
		}
	}
	
	echo "\tupdate base_program_store_relationship domainname end\r\n";
}

/*
if($full_check){
	echo "\tcheck merchantdomain !!!!! start\r\n";
	$sql = "select storeid, domainname from base_program_store_relationship where status = 'active'";
	$base_arr = $objProgram->objMysql->getRows($sql, "storeid");
	$base_arr_site = array();
	$site_arr = array("au", "uk", "ca", "sn", "de", "fr"); //fr
	foreach($site_arr as $si){		
		if($si == "de"){
			$objAUMysql = New Mysql("coupon{$si}_base", "bcg01.i.mgsvr.com", "couponde_usr", "c0up0nd3_usr");
		}elseif($si == "fr"){
			$objAUMysql = New Mysql("coupon{$si}_base", "bcg01.i.mgsvr.com", "frsite", "ejSBge2Bvs");	
			
		}else{
			$objAUMysql = New Mysql("coupon{$si}_base", "bcg01.i.mgsvr.com", "couponsn", "rrtTp)91aLL1");
		}
		
		$sql = " SELECT id, OriginalUrl FROM normalmerchant where isactive = 'yes'";
		$tmp_arr = $objAUMysql->getRows($sql, "id");
		
		if($si == "sn") $si = "us";
		$sql = "select storeid, merchantid from store_merchant_relationship where sitename = 'cs{$si}'";
		$ms_arr = $objProgram->objTaskMysql->getRows($sql, "merchantid");
		
		$sql = "select storeid, domainname from base_program_store_relationship_$si where status = 'active'";
		$base_arr_site[$si] = $objProgram->objMysql->getRows($sql, "storeid");
		
		foreach($ms_arr as $mid => $m_v){
			if(isset($tmp_arr[$mid]) && isset($base_arr[$m_v["storeid"]])){
				$domain_arr = $objProgram->getDomainByHomepage($tmp_arr[$mid]["OriginalUrl"], "fi");
				$dd = current($domain_arr["domain"]);
				$country = isset($domain_arr["country"]) ? current($domain_arr["country"]) : "";
				
				
				if($base_arr[$m_v["storeid"]]["domainname"] != $dd){			
				//foreach($domain_arr as $vv){
					$sql = "insert ignore into base_ps_domain (storedomain, domain, site) values('{$base_arr_site[$si][$m_v["storeid"]]["domainname"]}', '$dd', '{$si}')";
					$objProgram->objMysql->query($sql);
					//$objProgram->insertDomain($vv);
					$objProgram->insertDomain($dd);
				}
				
				$sql = "update base_program_store_relationship_$si set merchantdomain = '{$dd}' where storeid = {$m_v["storeid"]}";
				$objProgram->objMysql->query($sql);
				
				if($country){
					$dd .= "/".$country;
					
					if($base_arr[$m_v["storeid"]]["domainname"] != $dd){			
					//foreach($domain_arr as $vv){
						$sql = "insert ignore into base_ps_domain (storedomain, domain, site) values('{$base_arr[$m_v["storeid"]]["domainname"]}', '$dd', '')";
						$objProgram->objMysql->query($sql);
						//$objProgram->insertDomain($vv);					
					}
					
					$sql = "update base_program_store_relationship_$si set merchantdomain = '{$dd}' where storeid = {$m_v["storeid"]}";
					$objProgram->objMysql->query($sql);
					
					$sql = "insert ignore into base_ps_domain (storedomain, domain, site) values('{$base_arr_site[$si][$m_v["storeid"]]["domainname"]}', '$dd', '{$si}')";
					$objProgram->objMysql->query($sql);
					$objProgram->insertDomain($dd);
				}
			}
		}
	}
	echo "\tcheck merchantdomain !!!!! end\r\n";
}
$sql = "DELETE FROM `base_ps_domain` WHERE storedomain = domain";
$objProgram->objMysql->query($sql);

echo "\tupdate base_ps_domain end\r\n";*/

$all = "";
if($full_check){
	$all = "--all";
	$cmd = "php /home/bdg/program/cron/set_d_p_site.php $all >> /home/bdg/program/cron/test/set_d_p_site_all.log  2>&1 &";
	system($cmd);
	echo $cmd."\r\n";
}else{
	//foreach(array("us", "uk", "ca", "au", "de", "fr") as $site){		
		$cmd = "php /home/bdg/program/cron/set_d_p_site.php >> /home/bdg/program/cron/test/set_d_p_site.log  2>&1 &";
		system($cmd);
		echo $cmd."\r\n";
	//}
}

echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";
exit;


?>