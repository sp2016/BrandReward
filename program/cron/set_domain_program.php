<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
include_once(INCLUDE_ROOT . "func/func.php");


echo "<< Start @ ".date("Y-m-d H:i:s")." >>\r\n";

$objProgram = New Program();

$is_debug = $all = $fake =  $redis = $self = false;
$did_arr = array();
$check_did = array();
if(isset($_SERVER["argc"]) && $_SERVER["argc"] > 1)
{
	foreach($_SERVER["argv"] as $v){
		$tmp = explode("=", $v);		
		if($tmp[0] == "--did"){			
			$did_arr = array_flip(explode(",", $tmp[1]));
			$check_did = $did_arr; 
		}elseif($tmp[0] == "--debug"){
			$is_debug = true;
		}elseif($tmp[0] == "--all"){
			$all = true;
		}elseif($tmp[0] == "--fake"){
			$fake = true;
		}elseif($tmp[0] == "--redis"){
			$redis = true;
		}elseif($tmp[0] == "--self"){
			$self = true;
		}
		
	}			
}

$cnt = 0;


/*echo "check domain union start\r\n";
$i = $j = $k = 0;
$wheres_str = "";
if($did_arr){
	$wheres_str = "where a.id in (".implode(",", array_keys($did_arr)).")";
}	
while(1){
	$sql = "select a.id, b.key from domain a left join domain_outgoing_default b on a.id = b.did $wheres_str order by a.id limit " . ($i * 1000) . ", 1000";
	$domain_arr = array();
	$domain_arr = $objProgram->objMysql->getRows($sql);
	
	if(!count($domain_arr)) break;
	echo $i++;
	
	foreach($domain_arr as $domain){
		if(empty($domain["key"])){
			//start check domain union
			$sql = "select DomainFromid, DomainToid from r_domain_union where DomainFromid = {$domain["id"]} or DomainToid = {$domain["id"]}";
			$tmp_arr = array();
			$tmp_arr = $objProgram->objMysql->getRows($sql);
			if(count($tmp_arr)){
				$rel_did = array();
				foreach($tmp_arr as $v){
					if($v["DomainToid"] != $domain["id"]){
						$rel_did[$v["DomainToid"]] = $v["DomainToid"];
					}elseif($v["DomainFromid"] != $domain["id"]){
						$rel_did[$v["DomainFromid"]] = $v["DomainFromid"];
					}
				}
				if(count($rel_did)){
					$sql = "SELECT pid from r_domain_program a INNER JOIN program_intell b ON a.pid = b.programid WHERE a.Status = 'active' AND b.isactive = 'active' AND a.did in (".implode(",", $rel_did).")";
					$p_arr = array();
					$p_arr = $objProgram->objMysql->getRows($sql, "pid");
					$j++;
					
					foreach($p_arr as $pid => $null){				
						$objProgram->addDomainProgramRelationship(array($pid => array($domain["id"])));
						$k++;
						$check_did[$domain["id"]] = $domain["id"];
						//print_r($v);					
					}
				}
			}
		}
	}
}
echo "check domain union end $j _ $k\r\n";


echo "check fake domain start\r\n";
$i = $j = $k = 0;

$wheres_str = "";
if($did_arr){
	$wheres_str = "where a.id in (".implode(",", array_keys($did_arr)).")";
}

while(1){
	$sql = "select a.domain, b.storeid, b.domainid from domain a inner join r_store_domain b on a.id = b.domainid $wheres_str order by a.id limit " . ($i * 1000) . ", 1000";
	$domain_arr = array();
	$domain_arr = $objProgram->objMysql->getRows($sql);
	
	if(!count($domain_arr)) break;
	echo $i++;
	
	foreach($domain_arr as $v){
		$sql = "SELECT * FROM r_domain_program a INNER JOIN program_intell b ON a.pid = b.programid 
WHERE a.status = 'active' AND b.isactive = 'active' AND a.did = {$v["domainid"]} limit 1";
		$tmp_arr = array();
		$tmp_arr = $objProgram->objMysql->getFirstRow($sql);
		if(!count($tmp_arr)){
			//$sql = "SELECT c.pid FROM r_store_domain a INNER JOIN domain b ON a.domainid = b.id INNER JOIN r_domain_program c ON a.domainid = c.did WHERE c.Status = 'active' and a.storeid = {$v["storeid"]}";
			$sql = "SELECT c.pid FROM r_store_domain a INNER JOIN domain b ON a.domainid = b.id INNER JOIN r_domain_program c ON a.domainid = c.did INNER JOIN program_intell d ON c.pid = d.programid WHERE c.Status = 'active' AND d.isactive = 'active' AND a.storeid = {$v["storeid"]}";
			$p_arr = array();
			$p_arr = $objProgram->objMysql->getRows($sql, "pid");
			$j++;			
			
			foreach($p_arr as $pid => $null){				
				$objProgram->addDomainProgramRelationship(array($pid => array($v["domainid"])));
				$k++;
				$check_did[$v["domainid"]] = $v["domainid"];
				
				//print_r($v);					
			}
		}
	}
}
echo "check fake domain end $j _ $k\r\n";*/


echo "checkDomainProgramRel start\r\n";
if($all){
	echo "all\r\n";
	$i = 0;
	$pos = 0;
	while(1){
		//$sql = "select id from domain order by id limit " . $i * 1000 . ", 1000";
		$sql = "select id from domain where domain not like '%/%' and id > $pos ORDER BY id LIMIT 1000";
		$check_did = array();
		$check_did = $objProgram->objMysql->getRows($sql, "id");
	
		if(!count($check_did)) break;
		echo $i++;
		
		$tmp = array_keys($check_did);
		$tmp_pos = end($tmp);
		if($tmp_pos > $pos) $pos = $tmp_pos;
				
		$cnt += $objProgram->checkDomainProgramRel($check_did);
	}
	
}else{
	$cnt = $objProgram->checkDomainProgramRel($check_did);	
}
echo "checkDomainProgramRel end, update ($cnt) rel\r\n";

/*if($split){
	echo "split\r\n";
	
	$site_arr = array("au", "de", "us", "uk", "ca");
	foreach($site_arr as $site){
		$objProgram->base_rel = array();
		$i = 0;
		while(1){
			$sql = "select id from domain order by id limit " . $i * 1000 . ", 1000";
			$check_did = array();
			$check_did = $objProgram->objMysql->getRows($sql, "id");
		
			if(!count($check_did)) break;
			echo $i++;
					
			$cnt += $objProgram->checkDomainProgramRel_Sp($check_did, $site);
		}
	}
}*/

/*if($redis){
	echo "to redis\r\n";
	include_once("to_redis.php");
}*/

echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";
exit;



?>