<?php
	/**
	 * Created by PhpStorm.
	 * User: Sky Ting
	 * Date: 2018/01/23
	 * Time: 16:28
	 */
	
	include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
	include_once(dirname(dirname(__FILE__)) . "/func/func.php");
	$objProgram = New Program();
	$count = 0;
	$check_data_end = date("Y-m-d 00:00:00");
	$check_data_start = date("Y-m-d 00:00:00",strtotime('-1 day'));
	$sql = "SELECT ProgramID, AffId, GROUP_CONCAT(FieldName) AS FieldName , GROUP_CONCAT(left(FieldValueOld, 200)  SEPARATOR ';;') AS FieldValueOld, GROUP_CONCAT(left(FieldValueNew, 200) SEPARATOR ';;') AS FieldValueNew , `AddTime` FROM program_intell_change_log WHERE FieldName IN ('CommissionType', 'CommissionCurrency', 'CommissionUsed','CommissionValue') and `AddTime` < '{$check_data_end}' and `AddTime` >= '{$check_data_start}' GROUP BY ProgramID, `AddTime` having FieldName like '%CommissionUsed%' ORDER BY `ID`";
	$data = $objProgram->objMysql->getRows($sql);
	foreach ($data as $datum){
		$fields = explode(',',$datum['FieldName']);
		$values_old = explode(';;',$datum['FieldValueOld']);
		$values_new = explode(';;',$datum['FieldValueNew']);
		if(!(count($fields)==count($values_old) && count($values_new)==count($values_old))){
			print_r($datum);
			echo "Different:" . $datum['ProgramID'].PHP_EOL;
		}
//		print_r($fields);
//		print_r($values_old);
//		print_r($values_new);
		$commissionValueOld = $commissionValueNew = $commissionType = '';
		$commissionChangeValue = $commissionUsedOld = $commissionUsedNew = 0;
		$flag = true;
		if((empty($values_old[0])&&empty($values_old[1])&&empty($values_old[2])&&empty($values_old[3])) || (empty($values_new[0])&&empty($values_new[1])&&empty($values_new[2])&&empty($values_new[3]))){
			$flag = false;
			continue;
		}
		for($i = 0;$i < count($fields); $i++){
			
			if($fields[$i] == 'CommissionValue'){
				$commissionValueOld = $values_old[$i];
				$commissionValueNew = $values_new[$i];
			}
			
			
			if($fields[$i] == 'CommissionType'){
				if($values_new[$i] != $values_old[$i] && !empty($values_new[$i]) && !empty($values_old[$i])){
					$flag = false;
					break;
				}
				if($values_new[$i] == 'Percent')
					$commissionType = 'Percent';
				if($values_old[$i] == 'Percent')
					$commissionType = 'Percent';
			}
			
			if($fields[$i] == 'CommissionUsed'){
				$commissionUsedOld = $values_old[$i];
				$commissionUsedNew = $values_new[$i];
				$commissionChangeValue = round($commissionUsedNew - $commissionUsedOld,2);
				if(abs($commissionChangeValue) <= 0.01){
					$flag = false;
					break;
				}
			}
			
			if($fields[$i] == 'CommissionCurrency')
			{
				if($values_new[$i] != $values_old[$i] && !empty($values_new[$i]) && !empty($values_old[$i])){
					$flag = false;
					break;
				}
				if(!empty($values_new[$i]))
					$commissionType = $values_new[$i];
				if(!empty($values_old[$i]))
					$commissionType = $values_old[$i];
			}
		}
		if($flag){
			if(empty($commissionType)){
				$sql = "select CommissionType,CommissionCurrency from program_intell where ProgramId = '{$datum['ProgramID']}'";
				$tmp = $objProgram->objMysql->getFirstRow($sql);
				if(!empty($tmp['CommissionType']) && $tmp['CommissionType'] == 'Percent')
					$commissionType = 'Percent';
				else
					$commissionType = empty($tmp['CommissionCurrency'])?'':$tmp['CommissionCurrency'];
			}
			if(!empty($commissionType) && !empty($commissionUsedOld) && !empty($commissionUsedNew)) {
				$sql = "select b.`StoreId` from r_domain_program a inner join r_store_domain b on a.`DID`=b.`DomainId` where a.`PID` = {$datum['ProgramID']} and  a.`Status`='Active'";
				$storeid = $objProgram->objMysql->getFirstRowColumn($sql);
				if (!empty($storeid)) {
					$sql = "insert ignore into program_commission_change_log (`ProgramId`,`StoreId`,`AffId`,`CommissionValueOld`,`CommissionValueNew`,`CommissionUsedOld`,`CommissionUsedNew`,`CommissionChangeValue`,`CommissionCurrency`,`CommissionUpdateTime`) values ('{$datum['ProgramID']}','$storeid','{$datum['AffId']}','$commissionValueOld','$commissionValueNew','$commissionUsedOld','$commissionUsedNew','$commissionChangeValue','$commissionType','{$datum['AddTime']}')";
					$objProgram->objMysql->query($sql);
					$count++;
				}
			}
		}
//		die;
		
	}
	
	echo $count.PHP_EOL;