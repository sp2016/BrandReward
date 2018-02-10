<?php
	/**
	 * Created by PhpStorm.
	 * User: Sky Ting
	 * Date: 2017/10/25
	 * Time: 17:41
	 */
	
	include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
	include_once(INCLUDE_ROOT . "func/func.php");
	
	$objProgram = New Program();
	
	$sql = "select concat(`date`,' ',`hour` + 1,':00:00') from bd_out_tracking_publisher_statistics order by `ID` desc limit 1";
	$end_date = date("Y-m-d H:i:s",strtotime($objProgram->objMysql->getFirstRowColumn($sql)));
	while (true)
	{
		$start_date = $end_date;
		$end_date = date('Y-m-d H:i:s',strtotime('+1 hour',strtotime($start_date)));
		$date = date("Y-m-d",strtotime($start_date));
		$hour = date("H",strtotime($start_date));
		if(date("Y-m-d H:00:00") < $end_date)
			break;
		$sql = "select count(*) from bd_out_tracking_publisher where created>='$start_date' and created<'$end_date'";
		$count = $objProgram->objMysql->getFirstRowColumn($sql);
		$sql = "select count(*) from bd_out_tracking_publisher where created>='$start_date' and created<'$end_date' and AffId!=0";
		$count_aff = $objProgram->objMysql->getFirstRowColumn($sql);
		$sql = "insert into bd_out_tracking_publisher_statistics (`date`,`hour`,clicks,clicks_Aff) values ('{$date}','{$hour}','$count','$count_aff')";
		$objProgram->objMysql->query($sql);
	}
	
