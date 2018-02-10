<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');
$msg = array();

//1,2,3分别对应前天，昨天，今天
$d = new DateTime();
$d = $d->format("Y-m-d");

//$d = "2012-7-15";
$day3 = date('Y-m-d', strtotime("$d"));
$day2 = date('Y-m-d', strtotime("$day3 -1 day"));
$day1 = date('Y-m-d', strtotime("$day3 -2 day"));



$time3 = date('Y-m-d H:i:s', strtotime("$day3"));
$time4 = date('Y-m-d H:i:s', strtotime("$day3 +1 day"));
$time2 = date('Y-m-d H:i:s', strtotime("$day3 -1 day"));
$time1 = date('Y-m-d H:i:s', strtotime("$day3 -2 day"));







$sql1 = 'SELECT DISTINCT(AffId) FROM program WHERE AddTime >= "'.$time1.'" AND AddTime < "'.$time2.'"';
$affid1 = array();
$arr1 = array();
$result1 = mysql_query($sql1);
if($result1){
	while($affid1 = mysql_fetch_array($result1)){
		$arr1[] = $affid1['AffId'];
	}
}
$str1 = implode("|", $arr1);
$count1 = count($arr1);



$sql2 = 'SELECT DISTINCT(AffId) FROM program WHERE AddTime >= "'.$time2.'" AND AddTime < "'.$time3.'"';
$affid2 = array();
$arr2 = array();
$result2 = mysql_query($sql2);
if($result2){
	while($affid2 = mysql_fetch_array($result2,MYSQL_ASSOC)){
		$arr2[] = $affid2['AffId'];
	}

}
$str2 = implode("|", $arr2);
$count2 = count($arr2);



$sql3 = 'SELECT DISTINCT(AffId) FROM program WHERE AddTime >="'.$time3.'"AND AddTime < "'.$time4.'"';
$affid3 = array();
$arr3 = array();
$result3 = mysql_query($sql3);
if($result3){
	while($affid3 = mysql_fetch_array($result3)){
		$arr3[] = $affid3['AffId'];
	}
}
$str3 = implode("|", $arr3);
$count3 = count($arr3);

$msg['day1'] = $day1;
$msg['day2'] = $day2;
$msg['day3'] = $day3;
$msg['count1'] = $count1;
$msg['count2'] = $count2;
$msg['count3'] = $count3;
$msg['str1'] = $str1;
$msg['str2'] = $str2;
$msg['str3'] = $str3;

echo json_encode($msg);

?>