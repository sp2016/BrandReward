<?php
include_once(dirname(dirname(__FILE__))."/etc/const.php");

echo "Start @ ".date("Y-m-d H:i:s")."\r\n";

$day = 0;
$hours = 1;
if(isset($_SERVER["argc"]) && $_SERVER["argc"] > 1)
{
	foreach($_SERVER["argv"] as $v){
		$tmp = explode("=", $v);		
		if($tmp[0] == "--day"){			
			$day = intval($tmp[1]);
		}		
	}			
}

$objMysql = new Mysql();

$sql = "SELECT apikey FROM publisher_account WHERE publisherid <= 10";
$mega_key_arr = array();
$mega_key_arr = $objMysql->getRows($sql, 'apikey');

$ignore_mega_sql = '';
if(count($mega_key_arr)){
	$ignore_mega_sql = "AND site NOT IN ('" . implode("','", array_keys($mega_key_arr)) . "')";
}


//for($i=$day;$i>=0;$i--){
for($i=$hours;$i>=0;$i--){
	$date = date("Y-m-d", strtotime(" -$i hours"));
	$hour = date("Y-m-d H", strtotime(" -$i hours"));
	echo $hour."\t";
	$sql = "SELECT * FROM bd_out_tracking WHERE createddate = '$date' AND created >= '$hour:00:00' AND created <= '$hour:59:59' " . $ignore_mega_sql . " #limit 3 ";
	$tmp_arr = $sql_insert_value = array();
	$tmp_arr = $objMysql->getRows($sql);
	//print_r($tmp_arr);
	if(count($tmp_arr)){
		$sql_insert_key = "INSERT IGNORE INTO bd_out_tracking_publisher ( `" . implode('`,`', array_keys(current($tmp_arr))) . "`, `pageUrlMD5`) VALUES (";		
		foreach($tmp_arr as $value){
			$md5 = '';
			foreach($value as $k => $v){				
				$value[$k] = addslashes($v);
				if($k == 'pageUrl'){
					$md5 = md5($v);
				}
			}
			$value['pageUrlMD5'] = $md5;
			$sql_insert_value[] = "'".implode("','", $value)."'";
		}
		$sql_insert_value = implode('), (', $sql_insert_value);
	//echo "######\r\n";
		$sql = $sql_insert_key . $sql_insert_value . ')';
	//echo "\r\n######";
		$objMysql->query($sql);
	}
	echo "--(".count($tmp_arr).")\r\n";
}

echo "Finished @ ".date("Y-m-d H:i:s")."\r\n";
?>