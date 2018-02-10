<?php
	/**
	 * Created by PhpStorm.
	 * User: Sky Ting
	 * Date: 2017/11/06
	 * Time: 15:39
	 */
	
	include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
	include_once(INCLUDE_ROOT . "func/func.php");

	$date = date("Y-m-d H:i:s");
	$one_minute = date("Y-m-d H:i",strtotime('-1 minute',strtotime($date)));
	echo "<< Start to collect information @ $date >>\r\n";

	$objProgram = New Program();
	$objProgram->objMysql->query('SET NAMES latin1');

	$fieldArr = array('CommissionExt','Partnership','Homepage','SupportDeepUrl','Name','StatusInAff','AffDefaultUrl','TargetCountryExt','CommissionUsed','SupportType','StatusInBdg');

	//insert in to program_update_pending from program_change_log where Status is new,the set it to PROCESSED
	$error_controller = 0;
	while(true)
	{
		$update_sql = array();
		$sql = "select group_concat(ID) ID,ProgramID,group_concat(distinct FieldName) FieldName FROM program_change_log WHERE `Status` = 'NEW' and `LastUpdateTime` <= '$date' and FieldName in ('". implode("','",$fieldArr). "') group by ProgramID order by `ID` ASC limit 0,1000";
		$data = $objProgram->objMysql->getRows($sql,'ProgramID');
		$error_controller++;
		if(empty($data))
			break;
		$ids = implode(',',array_map('array_shift',$data));
		$sql = "UPDATE program_change_log SET Status = 'PROCESSED', LastUpdateTime = '{$date}' WHERE `Status` = 'NEW' and `ID` in ($ids)";
		$objProgram->objMysql->query($sql);
		foreach ($data as $datum)
		{
			$field_tmps = explode(',',$datum['FieldName']);
			foreach ($field_tmps as $field_tmp)
			{
				if(in_array($field_tmp,$fieldArr))
					$update_sql[$datum['ProgramID']][$field_tmp] = $field_tmp;
			}
		}
		if(!empty($update_sql))
			$objProgram->insertUpdateQueue($update_sql);
		
		if($error_controller > 10)
			break;
	}
	

	//insert in to program_update_pending from program_manual_change_log where Status is new,the set it to PROCESSED
	$error_controller = 0;
	while(true)
	{
		$update_sql = array();
		$sql = "SELECT group_concat(ID) ID,ProgramID,group_concat(distinct FieldName) FieldName FROM program_manual_change_log WHERE `Status` = 'NEW' and `LastUpdateTime` <= '$date' and FieldName in ('". implode("','",$fieldArr). "') group by ProgramID order by `ID` ASC limit 0,1000";
		$data = $objProgram->objMysql->getRows($sql,'ProgramID');
		$error_controller++;
		if(empty($data))
			break;
		$ids = implode(',',array_map('array_shift',$data));
		$sql = "UPDATE program_manual_change_log SET Status = 'PROCESSED', LastUpdateTime = '{$date}' WHERE `Status` = 'NEW' and `ID` in ($ids)";
		$objProgram->objMysql->query($sql);
		foreach ($data as $datum)
		{
			$field_tmps = explode(',',$datum['FieldName']);
			foreach ($field_tmps as $field_tmp)
			{
				if(in_array($field_tmp,$fieldArr))
					$update_sql[$datum['ProgramID']][$field_tmp] = $field_tmp;
			}
		}
		if(!empty($update_sql))
			$objProgram->insertUpdateQueue($update_sql);
		
		if($error_controller > 10)
			break;
	}
	
	$ignore_list = "";
	if(SID == 'bdg01') $ignore_list = "639";
	$sql = "select ID from wf_aff where isActive != 'YES' or isActive is null";
	$wf_list = $objProgram->objMysql->getRows($sql,'ID');
	$ignore_list = trim($ignore_list . ',' . implode(',',array_keys($wf_list)),',');
	
	$pid_list = array();
	
	//updated in r_domain_program
//	$log_p = array();
//	$sql = "select a.pid from r_domain_program a inner join program b on a.pid = b.id where b.affid not in ({$ignore_list}) and a.LastUpdateTime >= '{$one_minute}'";
//	$log_p = $objProgram->objMysql->getRows($sql, "pid");
//	if(count($log_p))
//	{
//		$pid_list = array_keys($log_p);
//		echo "add " . count(array_keys($log_p)) . " program from r_domain_program".PHP_EOL;
//	}

	//updated in program_manual
	//TODO will be deleted after every manual operation added in program_manual_change_log
	$log_p = array();
	$sql = "select DISTINCT a.ProgramId pid from program_manual a inner join program_intell b on a.programid = b.programid where b.affid not in ({$ignore_list}) and a.LastUpdateTime >= '{$one_minute}'";
	$log_p = $objProgram->objMysql->getRows($sql, "pid");
	if(count($log_p))
	{
		$pid_list = array_merge($pid_list ,array_keys($log_p));
		echo "add " . count(array_keys($log_p)) . " program from program_manual".PHP_EOL;
	}

	//new in program
	$log_p = array();
	$sql = "select a.id pid from program a where a.affid not in ({$ignore_list}) and (a.AddTime >= '{$one_minute}')";
	$log_p = $objProgram->objMysql->getRows($sql, "pid");
	if(count($log_p))
	{
		$pid_list = array_merge($pid_list ,array_keys($log_p));
		echo "add " . count(array_keys($log_p)) . " program from program".PHP_EOL;
	}

	//in program but not in program_intell,should ignore network 191
	$log_p = array();
	$sql = "select a.`ID` pid from program a left join program_intell b on a.`ID`=b.`ProgramId` where a.`AffId` != 191 and b.`ProgramId` is null and a.`Affid` not in ({$ignore_list})";
	$log_p = $objProgram->objMysql->getRows($sql, "pid");
	if(count($log_p))
	{
		$pid_list = array_merge($pid_list ,array_keys($log_p));
		echo "add " . count(array_keys($log_p)) . " program from program_intell".PHP_EOL;
	}
	
	$pid_list = array_unique($pid_list);
	$update_sql = array();
	if(!empty($pid_list))
	{
		foreach ($pid_list as $pid) {
			foreach ($fieldArr as $fieldName){
				$update_sql[$pid][$fieldName] = '';
			}
		}
	}
	if(!empty($update_sql))
		$objProgram->insertUpdateQueue($update_sql);
	echo "<< End @ ".date("Y-m-d H:i:s") .">>\r\n";

	