<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");

echo "<< Start @ ".date("Y-m-d H:i:s")." >>\r\n";

$objMysql = New MysqlExt();

$max = 100000;
$j = 0;

$sql = 'select count(*) from short_pool';
$cnt = $objMysql->getFirstRowColumn($sql);

if($cnt < $max){	
	$key = substr(strtotime(" - " . date("s") . "days"), -5);
	$ready_cnt = $max - $cnt;
	
	for($i=0;$i<$ready_cnt;$i++){
		 $short = getShort();
		 if(strlen($short) == 7){
		 	$sql = "insert ignore into short_url (short) value ('$short')";
		 	$objMysql->query($sql);
		 	
		 	$sql = "insert ignore into short_pool (short) value ('$short')";
		 	$objMysql->query($sql);
		 	
		 	$j++;
		 }
	}
}
echo "<< End @$cnt|$j ".date("Y-m-d H:i:s")." >>\r\n";
exit;

function getShort($retry = 0){
	global $key, $objMysql;
	$short = '';
	$short = random(7, $key);
	$sql = "select short from short_url where short = '{$short}'";
	$tmp_arr = array();
	$tmp_arr = $objMysql->getFirstRow($sql);
	if(count($tmp_arr)){
		$retry++;
		if($retry < 10){
			$short = getShort($retry);
		}else{
			echo 'warning: retry > 10 , ';
			exit;
		}
	}
	return $short;
}

function random($length, $key)
{
	$random = '';   
	$pool = 'ZAQWSXCDERFVBGTYHNMJUIKLOPmnbvcxzasdfghjklpoiuytrewq';	
	$pool .= substr(microtime(true), -2);//'1234567890';
	
	//srand ((double)microtime()*1000000);
	for($i = 0; $i < $length; $i++)	
	{   
		$random .= substr($pool,(rand()%(strlen ($pool))), 1);   
	}   

	return $random;   
}   
   
?>