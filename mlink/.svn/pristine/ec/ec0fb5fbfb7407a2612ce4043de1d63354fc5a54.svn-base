<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init_back.php');
//$db = New Mysql('bdg_go_base','bdg02.i.mgsvr.com','bdg_slave','SHDbdsg32B');

$long = $_REQUEST['long'];
if (!preg_match('/[a-zA-z]+:\/\/[\S]*/', $long)) {
	echo 2;
	exit;
}

$accountid = intval($_REQUEST['ac']);
$short = '';

if(isset($_SERVER["argc"]) && $_SERVER["argc"] > 1)
{
	foreach($_SERVER["argv"] as $v){
		$tmp = explode("=", $v);		
		if($tmp[0] == "--url"){			
			$long = trim($tmp[1]);
		}
	}			
}

if(strlen($long)){
	$md5 = md5($long.$accountid);
	//换成redis
	$sql = "select short from short_url where LongMD5 = '$md5'";
	$short = $db->getFirstRowColumn($sql);

	//long字段为什么可以null？
	if(!$short){
		$short = getShort();
		if(strlen($short) == 7){
			$sql = "update short_url set `long` = '".addslashes($long)."', `longmd5` = '$md5', AccountId = $accountid, addtime = '".date("Y-m-d H:i:s")."' where `short` = '$short'";
			$db->query($sql);
		}
	}
}

echo $short;
exit;


function getShort($retry = 0){
	global $db;
	$short = '';
	$sql = 'select short from short_pool ORDER BY id limit 1';
	$tmp_short = $db->getFirstRowColumn($sql);
	$sql = "delete from short_pool where short = '$tmp_short'"; //delete last short
	$db->query($sql);

	if($db->getAffectedRows() == false){		
		if($retry < 10){
			$retry++;
			$short = getShort($retry);			
		}
	}else{
		$short = $tmp_short;
	}
	return $short;
}

?>