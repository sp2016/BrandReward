<?php
	/**
	 * Created by PhpStorm.
	 * User: Sky Ting
	 * Date: 2017/12/04
	 * Time: 18:23
	 */
	
	include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
    include_once(dirname(dirname(__FILE__)) . "/func/func.php");
    
    echo "<< Start @ " . date("Y-m-d H:i:s") ." >>".PHP_EOL;
    $end_date = date("Y-m-d");
    $start_date = date("Y-m-d",strtotime('-2 day'));
	$objProgram = New Program();
	$bdg01 = New MysqlExt(MEGA_DB_NAME, MEGA_DB_HOST, MEGA_DB_USER, MEGA_DB_PASS);
	$sql = "select ProgramID,CreatedDate,sum(clicks) Clicks,sum(sales) Sales,sum(Revenues) Commission from statis_program_br  where ProgramID > 0 and CreatedDate >= '$start_date' and CreatedDate <= '$end_date' group by ProgramID,CreatedDate  order by CreatedDate asc ,ProgramID asc";
	$data = $objProgram->objMysql->getRows($sql);
	$sql_update = '';
	foreach ($data as $datum){
		if(empty($sql_update))
			$sql_update = "insert into program_performance (`ProgramId`,`CreatedDate`,`Clicks_BR`,`Sales_BR`,`Commission_BR`) values ('{$datum['ProgramID']}','{$datum['CreatedDate']}','{$datum['Clicks']}','{$datum['Sales']}','{$datum['Commission']}')";
		else
			$sql_update .= ",('{$datum['ProgramID']}','{$datum['CreatedDate']}','{$datum['Clicks']}','{$datum['Sales']}','{$datum['Commission']}')";
	}
	if (!empty($sql_update)) {
		$sql_update .= " ON DUPLICATE KEY UPDATE `Clicks_BR`=values(`Clicks_BR`),`Sales_BR`=values(`Sales_BR`),`Commission_BR`=values(`Commission_BR`)";
		$objProgram->objMysql->query($sql);
	}
	echo count($data) .' data have been updated!'.PHP_EOL;

	
	$sql = "select ProgramID,CreatedDate,sum(clicks) Clicks,sum(sales) Sales,sum(Revenues) Commission from statis_program_br  where ProgramID > 0 and CreatedDate >= '$start_date' and CreatedDate <= '$end_date' group by ProgramID,CreatedDate  order by ProgramID asc,CreatedDate asc";
	$data = $bdg01->getRows($sql);
	$pids = array_map(function($element){
		return $element['ProgramID'];
		}, $data
	);
	$pids = implode(',',array_unique($pids));
	$sql = "select a.`ID`,b.`Name` Network,b.`Domain`,a.`IdInAff` from program a inner join wf_aff b on b.`ID`=a.`AffId` where a.`ID` in ($pids)";
	$program_info = $bdg01->getRows($sql,'ID');
	$sql_update = '';
	foreach ($data as $datum) {
		if(isset($program_info[$datum['ProgramID']])){
			$program_info[$datum['ProgramID']]['IdInAff'] = addslashes($program_info[$datum['ProgramID']]['IdInAff']);
			$sql = "select a.`ID` from program a inner join wf_aff b on a.`AffId`=b.`ID` where a.`IdInAff`='{$program_info[$datum['ProgramID']]['IdInAff']}' and b.`Name` ='{$program_info[$datum['ProgramID']]['Network']}' and b.`Domain`='{$program_info[$datum['ProgramID']]['Domain']}'";
			$pid = $objProgram->objMysql->getFirstRowColumn($sql);
			if(!empty($pid)){
				if(empty($sql_update))
					$sql_update = "insert into program_performance (`ProgramId`,`CreatedDate`,`Clicks_MK`,`Sales_MK`,`Commission_MK`) values ('$pid','{$datum['CreatedDate']}','{$datum['Clicks']}','{$datum['Sales']}','{$datum['Commission']}')";
				else
					$sql_update .= ",('$pid','{$datum['CreatedDate']}','{$datum['Clicks']}','{$datum['Sales']}','{$datum['Commission']}')";
			}
		}
	}
	if (!empty($sql_update)){
		$sql_update .= " ON DUPLICATE KEY UPDATE `Clicks_MK`=values(`Clicks_MK`),`Sales_MK`=values(`Sales_MK`),`Commission_MK`=values(`Commission_MK`);";
		$objProgram->objMysql->query($sql_update);
	}
	echo count($data) .' data have been updated!'.PHP_EOL;
    echo "<< End @ " . date("Y-m-d H:i:s") ." >>".PHP_EOL;