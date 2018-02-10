<?php
include_once(dirname(dirname(__FILE__))."/etc/const.php");

$type = "mega";
if(isset($_SERVER["argc"]) && $_SERVER["argc"] > 1)
{
	foreach($_SERVER["argv"] as $v){
		$tmp = explode("=", $v);		
		if($tmp[0] == "--type"){			
			$type = trim($tmp[1]);
		}		
	}			
}

if(!in_array($type, array("mega", "mlink"))){
	echo "type not author";
	exit;
}

if(checkProcess(__FILE__, $type)){
	echo "Start @ ".date("Y-m-d H:i:s")."\r\n";	
	$objRedis = new Redis();
	
	if($type == "mega"){
		$objRedis->pconnect(REDIS_HOST, REDIS_PORT_API_WRITE);
	}else{	
		$objRedis->pconnect(REDIS_HOST, REDIS_PORT_API_WRITE_Mlink);
	}
	
	$i = 0;
	$rest_num = 10000;
	$f_date = date("Y-m-d-H");
	$fp = "";
	$fp = openFileByDate($fp, $f_date, $type);
	while(1){
		$tmp_log = "";
		$tmp_log = $objRedis->Rpop('track_log_list');
		if($tmp_log){
			//$sql = "insert into node_log(content) value('".addslashes($tmp_log)."')";
			//$objMysql->query($sql);		
			$tmp_json = json_decode($tmp_log);
			$created = substr($tmp_json->created, 0, 10)."-".substr($tmp_json->created, 11, 2);
			
			if($created != $f_date){
				$f_date = $created;
				$fp = openFileByDate($fp, $f_date, $type);
			}
			
			fputs($fp, $tmp_log."\r\n|\r\n");	
			
			$i++;
			
			if($i > $rest_num){
				$i = 0;
				sleep(1);
			}
		}else{
			$i = 0;
			sleep(10);
		}	
	}
}


function openFileByDate($fp, $date, $type){
	if($fp){
		fclose($fp);
	}
	//$dir = "/home/bdg/logs/tracking/";
	$file = $date.SERVER_NAME.".json";	
	$fp = fopen(LOG_DIR.$type."_tracking/".$file, "a");
	
	return $fp;
}

function checkProcess($process_name, $type){	
	$cmd = `ps aux | grep -v '/bin/sh' | grep -v 'grep' | grep '$type' | grep 'php $process_name' -c`;
	$return = ''.$cmd.'';
	//echo $return."\r\n";
	if($return > 1){
		return false;
	}else{
		return true;
	}
}

?>