<?php
	/**
	 * Created by PhpStorm.
	 * User: Sky Ting
	 * Date: 2017/11/09
	 * Time: 16:18
	 */
	
	include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
	include_once(INCLUDE_ROOT . "func/func.php");
	$date = date("Y-m-d H:i:s");

	echo "<< Start @ $date >>";
	define("NO_MYSQL_CACHE", true);
	$objProgram = New Program();
	$check_date = date("Y-m-d H:i", strtotime('-1 minute',strtotime($date)));
	$check_date_to =  date("Y-m-d H:i",strtotime($date));
	
	$ddd_arr = array();
	$sql = "select did FROM `r_domain_program` WHERE LastUpdateTime >= '$check_date'";
	$did_arr = array_keys($objProgram->objMysql->getRows($sql, "did"));
	echo 'r_domain_program:' . count($did_arr) . "\r\n";
	
	$ddd_arr = array();
	$sql = "select domainid FROM `r_domain_program_ctrl` WHERE LastUpdateTime >= '$check_date'";
	$ddd_arr = array_keys($objProgram->objMysql->getRows($sql, "domainid"));
	echo 'r_domain_program_ctrl:' . count($ddd_arr) . "\r\n";
	if (count($ddd_arr)) $did_arr = array_merge($did_arr, $ddd_arr);
	
	$ddd_arr = array();
	$sql = "SELECT a.did FROM `r_domain_program` a INNER JOIN program_intell b ON a.pid = b.programid WHERE b.LastUpdateTime >= '$check_date' AND b.LastUpdateTime < '$check_date_to' and a.status = 'active'";
	$ddd_arr = array_keys($objProgram->objMysql->getRows($sql, "did"));
	echo 'recent update program did:' . count($ddd_arr) . "\r\n";
	if (count($ddd_arr)) $did_arr = array_merge($did_arr, $ddd_arr);
	
	$ddd_arr = array();
	if (SID == 'bdg02') {
		$sql = "SELECT b.did FROM program_manual a INNER JOIN r_domain_program b ON a.programid = b.pid WHERE b.status = 'active' AND a.LastUpdateTime >= '$check_date' AND a.LastUpdateTime < '$check_date_to' ";
	} else {
		$sql = "SELECT b.did FROM program_manual a INNER JOIN r_domain_program b ON a.programid = b.pid WHERE b.status = 'active' AND ((a.LastUpdateTime >= '$check_date' AND a.LastUpdateTime < '$check_date_to') OR (a.AddTime >= '$check_date' AND a.AddTime < '$check_date_to')) ";
	}
	$ddd_arr = array_keys($objProgram->objMysql->getRows($sql, "did"));
	echo 'program_ctrl related did:' . count($ddd_arr) . "\r\n";
	
	if (count($ddd_arr)) $did_arr = array_merge($did_arr, $ddd_arr);
	echo "update did: " . count($did_arr) . "\r\n";
	
	if (!count($did_arr)) {
		echo "NO did need update. \r\n";
	} else {
		$sql = "insert into domain_update_queue (`DomainID`) values ";
		foreach ($did_arr as $did)
		{
			$sql .= "('{$did}'),";
		}
		$sql = trim($sql,',');
		$objProgram->objMysql->query($sql);
	}
	
	echo "<< End @ " . date("Y-m-d H:i:s") . " >>\r\n";
