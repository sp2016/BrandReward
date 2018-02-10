<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
include_once(INCLUDE_ROOT . "func/func.php");

$date = date('Y-m-d H:i:s');
echo "<< Start @ $date >>\r\n";

define("NO_MYSQL_CACHE", true);

$objProgram = New Program();

$is_debug = $all = false;
global $is_debug;
$did_arr = array();
$check_did = array();
if(isset($_SERVER["argc"]) && $_SERVER["argc"] > 1)
{
	foreach($_SERVER["argv"] as $v){
		$tmp = explode("=", $v);		
		if($tmp[0] == "--did"){			
			$did_arr = explode(",", $tmp[1]);
			$check_did = $did_arr; 
		}elseif($tmp[0] == "--debug"){
			$is_debug = true;
		}elseif($tmp[0] == "--all"){
			$all = true;
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
$cnt = 0;

echo "checkDomainProgramRel start\r\n";

if($all){
	echo "all\r\n";
	$i = 0;
	$pos = 0;
	while(1){
		$sql = "select distinct a.id from domain a INNER JOIN r_domain_program b ON a.`ID` = b.`DID` WHERE b.`Status` = 'active' AND a.`Domain` NOT LIKE '%/%' AND a.`ID` > $pos ORDER BY id LIMIT 1000";
		$check_did = array();
		$check_did = $objProgram->objMysql->getRows($sql, "id");
	
		if(!count($check_did))
			break;
		echo $i++.PHP_EOL;
		
		$tmp = array_keys($check_did);
		$tmp_pos = end($tmp);
		if($tmp_pos > $pos)
			$pos = $tmp_pos;
		$cnt += $objProgram->checkDomainProgramRelCountry($check_did);
	}
	echo "checkDomainProgramRel end, update ($cnt) rel\r\n";
	
	$sql = "select count(*) from domain_outgoing_default_other where lastupdatetime < '$date' ";
	$del_cnt = $objProgram->objMysql->getFirstRowColumn($sql);
	
	if(SID == 'bdg02'){
		$sql = "select count(*) from redirect_default where lastupdatetime < '$date' ";
		$del_cont_cnt = $objProgram->objMysql->getFirstRowColumn($sql);
		echo "del :$del_cnt and $del_cont_cnt".PHP_EOL;
		$sql = "delete from domain_outgoing_default_other where lastupdatetime < '$date'";
	}else{
		echo "del :$del_cnt".PHP_EOL;
		$sql = "delete from domain_outgoing_default_other where lastupdatetime < '$date' and site not in ('us', 'uk', 'au', 'ca', 'de', 'fr')";
	}
	$objProgram->objMysql->query($sql);
	if(SID == 'bdg02'){
		$sql = "delete from redirect_default where lastupdatetime < '$date'";
		$objProgram->objMysql->query($sql);
		
		$sql = "delete from domain_outgoing_all where lastupdatetime < '$date'";
		$objProgram->objMysql->query($sql);
	}
	
}else{
	if(!count($check_did)){
		$sql = "select distinct DomainID from domain_update_queue where `Status`='NEW' and `AddTime`< '{$date}'";
		$did_arr = $objProgram->objMysql->getRows($sql,'DomainID');
		$check_did = array_keys($did_arr);
		echo "affect domain: ".count($check_did)."\r\n";
		$sql = "update domain_update_queue set `Status`='PROCESSED' where `Status`='NEW' and `AddTime`< '{$date}'";
		$objProgram->objMysql->query($sql);
	}
	if(count($check_did)){
		$check_did = array_flip($check_did);
		//$cnt = $objProgram->checkDomainProgramRel($check_did);	
		$cnt = $objProgram->checkDomainProgramRelCountry($check_did);
		
		
		if(SID == 'bdg01'){
			 $sql = "select did from domain_outgoing_default_other where did in (".implode(',', array_keys($check_did)).") and lastupdatetime < '$date' and site not in ('us', 'uk', 'au', 'ca', 'de', 'fr')";
		}elseif(SID == 'bdg02'){
			$sql = "select did from domain_outgoing_default_other where did in (".implode(',', array_keys($check_did)).") and lastupdatetime < '$date' union select did from redirect_default where did in ('".implode(',', array_keys($check_did))."') and lastupdatetime < '$date' ";
		}
		$del_arr = $objProgram->objMysql->getRows($sql, 'did');
		$del_cnt = count($del_arr);
		echo "del : ". $del_cnt . PHP_EOL;
		if($del_cnt < 1000 && $del_cnt > 0){
			if(SID == 'bdg01'){
				$sql = "select id, `key`, pid, did, site from domain_outgoing_default_other where did in (".implode(',', array_keys($check_did)).") and  lastupdatetime < '$date' ";
			}elseif(SID == 'bdg02'){
				$sql = "select id, `key`, pid, did, site from domain_outgoing_default_other where did in (".implode(',', array_keys($check_did)).") and  lastupdatetime < '$date' union select id, `key`, pid, did, site from redirect_default where did in ('".implode(',', array_keys($check_did))."' and  lastupdatetime < '$date' ";
			}
			$del_did_arr = $objProgram->objMysql->getRows($sql);
			$del_did = array();
			foreach($del_did_arr as $v){
				if(strpos($v["key"], "/") === false){
					$sql = "insert ignore into domain_outgoing_default_changelog_other(site, DID, `Key`, ProgramFrom, ProgramTo, Changetime) 
							values('".addslashes($v["site"])."', '".intval($v["did"])."', '".addslashes($v["key"])."', '".intval($v["pid"])."', 0, '".date("Y-m-d H:i:s")."')";
					$objProgram->objMysql->query($sql);
				}
				$del_did[$v["did"]] = $v["did"];
			}
			
			if(count($del_did)){
				$ww = "and did in (".implode(",", $del_did).")";
				$where_str = '';
				if(SID == 'bdg01'){
					$where_str = " and site not in ('us', 'uk', 'au', 'ca', 'de', 'fr')";		
				}
				$sql = "delete from domain_outgoing_default_other where lastupdatetime < '$date' $ww $where_str ";		
				$objProgram->objMysql->query($sql);
				
				echo "########$sql\r\n########\r\n";
				if(SID == 'bdg02'){
					$sql = "delete from redirect_default where lastupdatetime < '$date' $ww ";		
					$objProgram->objMysql->query($sql);
					$sql = "delete from domain_outgoing_all where lastupdatetime < '$date' $ww ";		
					$objProgram->objMysql->query($sql);
				}
			}
		}elseif($del_cnt >= 1000){
			$mail_body = "set domain program new del : $del_cnt";
			AlertEmail::SendAlert('set_d_p_site del :'.$del_cnt, 'stanguan@meikaitech.com');
		}		
	}
}

echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";
exit;



?>