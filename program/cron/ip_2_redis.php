<?php
include_once(dirname(dirname(__FILE__))."/etc/const.php");

echo "Start @ ".date("Y-m-d H:i:s")."\r\n";	
$objRedis = new Redis();
$objRedis->pconnect(REDIS_HOST, REDIS_PORT);
	
$objMysql = New MysqlExt();

$page = 0;
//$objRedis->zRemRangeByScore('ip4', 0, 1000000000);
$objRedis->del('ip4');
while(true){
	$sql = "select * from ip_country_v4 limit ". $page * 1000 .", 1000";
	$ip4_arr = array();
	$ip4_arr = $objMysql->getRows($sql);
	
	if(!count($ip4_arr)) break;
	$page ++;
	
	$i = 0;
	foreach($ip4_arr as $v){	
		$z_member = $v["ip_from"].",".$v["ip_to"].",".strtoupper($v["country_code"]);
		if($v["ip_to"] && $z_member){
			$objRedis->zAdd('ip4', $v["ip_to"], $z_member);
			$i++;	
		}	
	}
}

echo $i;
?>