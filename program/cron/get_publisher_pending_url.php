<?php
/**
 * Created by PhpStorm.
 * User: Mcsky Ding
 * Date: 2016/6/1
 * Time: 15:33
 */
	header("Content-type: text/html; charset=utf-8");
	include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
	include_once(dirname(dirname(__FILE__)) . "/func/func.php");
	define("MAX_PROCESS_CNT", 6);
//	ini_set('xdebug.max_nesting_level', 200);

	echo 'Start@'.date('Y-m-d H:i:s').PHP_EOL;


	$objProgram = New Program();
	
	$cmd = "ps aux | grep get_publisher_pending_url.php | grep -v grep | grep -v /bin/sh -c";
	
	$processCount = trim(exec($cmd));
	
	echo "Current pending process count:\n";
	var_dump($processCount);
	echo "\n";
	if(is_numeric($processCount))
	{
		if($processCount > 1){
			echo "One get_publisher_pending_url is running now.Stoped!\n";
			die();
		}
	}
	else
	{
		echo "Error!\n";
		die();
	}
	
	
	
	$sql = "SELECT * FROM publisher_domain_info WHERE `Status` = 'pending';";
	$pending_data = $objProgram->objMysql->getRows($sql);
	if(!empty($pending_data))
	{
		foreach($pending_data as $pending)
		{
			dealData($pending['Url'],$pending['ID']);
		}
	}
	
	echo 'Finished@'.date('Y-m-d H:i:s')."\r\n";
	
	function dealData($url,$id){
		if(checkProcess()){
			$cmd = "nohup php /home/bdg/program/cron/get_publisher_ext_url_main.php -id=$id -url=$url>> /home/bdg/program/cron/log/{$id}.log 2>&1 &";
			echo system($cmd);
		}
		else
		{
			sleep(120);
			dealData($url,$id);
		}
	}
	
	function checkProcess(){
		$cmd = 'ps aux | grep get_publisher_ext_url_main.php | grep -v grep -c';
		$processCount = trim(exec($cmd));
		echo "Current main process count:\n";
		var_dump($processCount);
		echo "\n";
		if(is_numeric($processCount) && $processCount < MAX_PROCESS_CNT)
		{
			return true;
		}else{
			return false;
		}
	}