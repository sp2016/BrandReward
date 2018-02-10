<?php
	/**
	 * Created by PhpStorm.
	 * User: Sky Ting
	 * Date: 2017/10/25
	 * Time: 18:48
	 */
	
	include_once('conf_ini.php');
	include_once(INCLUDE_ROOT.'init2.php');
	
	mysql_query("SET NAMES UTF8");
	global $db;
	$table = "<table border='1'><tr><td>date</td><td>All clicks</td><td>Aff clicks</td><td>Aff percent</td></tr>";
	$sql = "select `date`,sum(clicks) clicks,sum(clicks_Aff) clicks_Aff from `bd_out_tracking_publisher_statistics` group by `date` order by `date`;";
	$data = $db->getRows($sql);
	foreach ($data as $datum)
	{
		$table .= "<tr><td>{$datum['date']}</td><td>{$datum['clicks']}</td><td>{$datum['clicks_Aff']}</td><td>".sprintf("%2.2f%%",$datum['clicks_Aff']/$datum['clicks']*100)."</td></tr>";
	}
	$table.="</table>";
	echo $table;
?>
