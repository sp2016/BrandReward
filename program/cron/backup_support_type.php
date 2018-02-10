<?php
	/**
	 * Created by PhpStorm.
	 * User: Sky Ting
	 * Date: 2017/10/24
	 * Time: 9:58
	 */
	include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
	include_once(dirname(dirname(__FILE__)) . "/func/func.php");
	
	$objProgram = new Program();
	$date = date("md");
	$sql = "alter table `program_support_type` add column `{$date}` enum('All','Content','Promotion','None') not null ;";
	$objProgram->objMysql->query($sql);
	$i = 0;
	while(true)
	{
		$sql = "select ProgramID,SupportType from program_intell order by ProgramID limit $i,1000";
		$data = $objProgram->objMysql->getRows($sql);
		$i += 1000;
		if(empty($data))
			break;
		foreach ($data as $datum)
		{
			$sql = "insert into program_support_type (`ProgramID`,`{$date}`) values ('{$datum['ProgramID']}','{$datum['SupportType']}') on duplicate key update `{$date}`=values(`{$date}`)";
			$objProgram->objMysql->query($sql);
		}
	}