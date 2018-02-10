<?php
	/**
	 * Created by PhpStorm.
	 * User: mding
	 * Date: 2017/06/06
	 * Time: 17:53
	 */
	
	
	include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
	include_once(dirname(dirname(__FILE__)) . "/func/func.php");
	
	$objProgram = new Program();
	$date = date("Y-m-d H:i:s");
	$i = 0;
	while (true)
	{
		$sql = "select a.`ID`,a.`Name`,a.Homepage,a.Affid,a.IdInAff from program a left join check_homepage_log b on a.`ID`=b.`PID` where a.StatusInAff='Active' and a.Partnership='Active' and b.`PID` is null and a.Affid != 1 limit $i,1000";
		$i += 1000;
		$data = $objProgram->objMysql->getRows($sql);
		if(empty($data))
			break;
		foreach ($data as $datum)
		{
			$true_url = getTrueUrl($datum['Homepage'])['final_url'];
			if(get_domain($datum['Homepage']) != get_domain($true_url))
			{
				if(stripos($true_url,'play.google.com/store/apps') !== false)
				{
					$true_url = substr($true_url,0,stripos($true_url,'&referrer='));
				}
				echo $datum['ID'] . "\t" .$datum['Homepage'] . "\t" .$true_url . PHP_EOL;
				$idinaff = addslashes($datum['IdInAff']);
				
				$sql = "insert into check_homepage_log (PID,Old,New,UpdateTime) values ('{$datum['ID']}','{$datum['Homepage']}','{$true_url}','{$date}')";
				$objProgram->objMysql->query($sql);
			}
		}
	}