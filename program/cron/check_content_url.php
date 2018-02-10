<?php
	include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
	include_once(dirname(dirname(__FILE__)) . "/func/func.php");

	$objProgram = New Program();
	$cmd = "ps aux | grep check_content_url.php | grep -v grep | grep -v /bin/sh -c";
	
	$processCount = trim(exec($cmd));
	
	echo "Current pending process count:$processCount".PHP_EOL;
	if(is_numeric($processCount))
	{
		if($processCount > 1){
			echo "One check_content_url is running now.Stoped!\n";
			die();
		}
	}
	else
	{
		echo "Error!\n";
		die();
	}
	
	$count_all = $count_error = 0;
	$error_ids = '';
	$date = date("Y-m-d H:i:s");
	echo "Content url check start @ {$date}".PHP_EOL;

	$mail = New AlertEmail();

	$sql = "select a.`ID`, a.AffUrl, b.AffId FROM content_feed_new a INNER JOIN program b ON a.`ProgramId` = b.`ID` LEFT JOIN check_aff_url c ON c.`ContentFeedID` = a.`ID` WHERE a.`Status` = 'Active' AND c.`ContentFeedID` IS NULL and b.AffId != 1 order by b.AffId asc, a.`ID` asc LIMIT 0, 1000";
	
	$link_arr = $objProgram->objMysql->getRows($sql);
	$count_all = count($link_arr);
	$error_sql = array();
	$error_log = '';
	foreach ($link_arr as $link)
	{
		echo $link['ID']."\t";
		$urlInfo = getTrueUrl($link['AffUrl']);
		if($urlInfo['http_code'] != 200)
		{
			if($urlInfo['http_code'] >300 && $urlInfo['http_code'] < 400)
			{
				$parameters = array('timeout'=>20);
				$urlInfo = getTrueUrl($link['AffUrl'],$parameters);
				if($urlInfo['http_code'] != 200)
				{
					echo $link['ID']."\t".$urlInfo['http_code']."\t".$link['AffUrl'].PHP_EOL;
					$sql = "insert into check_aff_url (`ContentFeedId`,`AddTime`,`AffUrl`,`StatusDesc`,`AffId`) values ('{$link['ID']}','{$date}','{$link['AffUrl']}','{$urlInfo['http_code']}','{$link['AffId']}')";
					$error_log .= $link['ID']."\t".$urlInfo['http_code']."\t".$link['AffUrl']."<br />";
					$error_sql[] = $sql;
				}
			}
			else
			{
				echo $link['ID']."\t".$urlInfo['http_code']."\t".$link['AffUrl'].PHP_EOL;
				$sql = "insert into check_aff_url (`ContentFeedId`,`AddTime`,`AffUrl`,`StatusDesc`,`AffId`) values ('{$link['ID']}','{$date}','{$link['AffUrl']}','{$urlInfo['http_code']}','{$link['AffId']}')";
				$error_log .= $link['ID']."\t".$urlInfo['http_code']."\t".$link['AffUrl']."<br />";
				$error_sql[] = $sql;
			}
		}
	}
	$count_error = count($error_sql);
	if($count_all == 0)
		exit;
	$end_time = date("Y-m-d H:i:s");
	$cost_time = get_time_interval($date,$end_time);
	if($count_error/$count_all >= 0.9)
	{
		$error_log = "Too many errors in this time<br /><br />" ."Total num is $count_all,error num is $count_error,cost '{$cost_time}'<br /><br />" . $error_log;
		$error_log .= "<br /><br />sqls are" . implode("<br />",$error_sql);
		$mail->SendAlert ('Too many errors appear at this time!' , $error_log , "mcskyding@meikaitech.com" , false);
	}
	else
	{
		foreach ($error_sql as $sql)
		{
			$objProgram->objMysql->query($sql);
		}
		$error_log = "Total num is $count_all,error num is $count_error,cost '{$cost_time}'<br /><br />" .$error_log;
		$mail->SendAlert ('Incorrect content feed info!' , $error_log , "merlinxu@meikaitech.com" , false);
	}
	echo "End @". $end_time ."Total num is $count_all,error num is $count_error".PHP_EOL;
//	$mail_warning = 'These contend feed are incorrect,IDs are:' . trim($error_ids,',');
//	$mail->SendAlert ('Incorrect content feed ids' , $mail_warning , "merlinxu@meikaitech.com,stanguan@meikaitech.com" , false);
//	echo $mail_warning;