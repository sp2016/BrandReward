<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");


$id_arr = array();
$is_debug = false;
$pid = "";
$is_fast = $nottoredis = $onlyactive = false;
$in_house = false;
if(isset($_SERVER["argc"]) && $_SERVER["argc"] > 1)
{
	foreach($_SERVER["argv"] as $v){
		$tmp = explode("=", $v);		
		if($tmp[0] == "--affid"){			
			$id_arr = explode(",", $tmp[1]);
		}elseif($tmp[0] == "--debug"){
			$is_debug = true;
		}		
	}			
}

echo "<< Start @ ".date("Y-m-d H:i:s")." >>\r\n";

$date = date("Y-m-d H:i:s");

$objProgram = New Program();

$sql = "select id, affid, idinaff from program";
$tmp_arr = $objProgram->objTaskMysql->getRows($sql);
$p_info = array();
foreach($tmp_arr as $v){
	if($v["affid"] == 2){		
		$v["idinaff"] = substr($v["idinaff"], 0, strpos($v["idinaff"], "_"));	
	}
	$p_info[$v["affid"]][$v["idinaff"]] = $v["id"];
}

$no_p = array();

$date_circle = array("3D" => 3, "7D" => 7, "1M" => 30, "3M" => 90, "1Y" => 365);
foreach($date_circle as $time_val => $days){
	echo $time_val."\t";
	//3D	
	$days++;
	$sql = "SELECT affid, idinaff, programid, SUM(sales) AS sales, SUM(commission) AS commission, COUNT(*) AS orders
			FROM `rpt_transaction_unique` WHERE visiteddate >= '".date("Y-m-d", strtotime("-$days days"))."'
			GROUP BY affid, idinaff";
	$tmp_arr = $objProgram->objMysql->getRows($sql);
	foreach($tmp_arr as $v){
		$pid = isset($p_info[$v["affid"]][$v["idinaff"]]) ? intval($p_info[$v["affid"]][$v["idinaff"]]) : intval($v["programid"]);
		if($pid){
			$sql = "insert into program_stats(programid, affid, idinaff, Sales{$time_val}, Orders{$time_val}, Revenue{$time_val})
					values($pid, {$v["affid"]}, '{$v["idinaff"]}', '{$v["sales"]}', '{$v["orders"]}', '{$v["commission"]}')
					ON DUPLICATE KEY UPDATE Sales{$time_val} = '{$v["sales"]}', Orders{$time_val} = '{$v["orders"]}', Revenue{$time_val} = '{$v["commission"]}'";
			$objProgram->objMysql->query($sql);
			
			if($days <= 7){
				$sql = "SELECT COUNT(*) as clicks FROM `bd_out_tracking` WHERE programid = $pid AND createddate >= '".date("Y-m-d", strtotime("-$days days"))."'";
				$clicks = $objProgram->objMysql->getFirstRowColumn($sql);
				if($clicks > 0){
					$sql = "insert into program_stats(programid, clicks{$time_val})
							values($pid, '$clicks')
							ON DUPLICATE KEY UPDATE clicks{$time_val} = '$clicks'";
					$objProgram->objMysql->query($sql);
				}
			}
		}else{
			$no_p[] = $v;
		}
	}
	
	//clicks
	/*if($days > 7) continue;
	$sql = "SELECT programid, COUNT(*) as clicks FROM `bd_out_tracking` WHERE programid > 0 AND created >= '".date("Y-m-d", strtotime("-$days days"))."' GROUP BY programid";
	$tmp_arr = $objProgram->objMysql->getRows($sql);
	foreach($tmp_arr as $v){
		$pid = intval($v["programid"]);
		if($pid){
			$sql = "insert into program_stats(programid, clicks{$time_val})
					values($pid, '{$v["clicks"]}')
					ON DUPLICATE KEY UPDATE clicks{$time_val} = '{$v["clicks"]}'";
			$objProgram->objMysql->query($sql);
		}
	}*/
}
//print_r($no_p);
echo "no match: ".count($no_p)."\r\n";

	$sql = "SELECT COUNT(*) AS clicks, domainused FROM bd_out_tracking WHERE createddate >= '".date("Y-m-d", strtotime("-$days days"))."'
			GROUP BY domainused";
	$tmp_arr = $objProgram->objMysql->getRows($sql);
	foreach($tmp_arr as $v){
		$domain = str_ireplace(":domain:", "", $v["domainused"]);
				
		$sql = "select id from domain where domain = '{$domain}'";
		$domainid = $objProgram->objMysql->getFirstRowColumn($sql);
		
		if($domainid){
			$sql = "insert into domain_stats(domainid, clicks3d)
					values($domainid, '{$v["clicks"]}')
					ON DUPLICATE KEY UPDATE clicks3d = '{$v["clicks"]}'";
			$objProgram->objMysql->query($sql);
		}
	}
	
	$sql = "SELECT domainid, clicks3d FROM domain_stats WHERE clicks3d >= 100 and domainid not in (select did from domain_outgoing_default)";
	$tmp_arr = $objProgram->objMysql->getRows($sql);
	foreach($tmp_arr as $v){
		$sql = "select id from task_non_aff_domain where domainid = {$v["domainid"]} and (status = 'new' or addtime > '".date("Y-m-d", strtotime("-7 days"))."') limit 1";
		$has_task = array();
		$has_task = $objProgram->objMysql->getRows($sql);
		if(!count($has_task)){		
			$sql = "insert into task_non_aff_domain(domainid, clicks3d, addtime, lastupdatetime)
					values({$v["domainid"]}, '{$v["clicks3d"]}', '".date("Y-m-d H:i:s")."', '".date("Y-m-d H:i:s")."')";
			$objProgram->objMysql->query($sql);
		}
	}

echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";
exit;


?>