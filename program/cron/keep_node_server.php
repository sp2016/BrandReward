<?php
include_once(dirname(dirname(__FILE__))."/etc/const.php");

$process_name = __FILE__;
$cmd = `ps aux | grep -v '/bin/sh' | grep -v 'grep' | grep 'php $process_name' -c`;
$return = ''.$cmd.'';
//echo $return."\r\n";
if($return > 1) exit;

$i = 0;
$process_name = "/app/nodejs/server_outgoing_nodejs/mega_outgoing_svr.js";
//killProcess($process_name);
	
while(1){
	if(checkProcess($process_name)){		
		echo $i."\t".date("Y-m-d H:i:s")."\r\n";
		killProcess($process_name);
		$i++;		
		$cmd = "nohup /usr/bin/node $process_name >> ".LOG_DIR."nodejs_logs/mega_outgoing_svr.js.{$i}_".date("Y-m-d").".log 2>&1 &";
		system($cmd);
	}
	sleep(1);	
}
echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";
exit;


function checkProcess($process_name){
	$cmd = `ps aux | grep -v 'grep' | grep -v 'nohup' | grep $process_name -c`;
	$return = ''.$cmd.'';
	if($return > 1){
		return false;
	}else{
		return true;
	}
}

function killProcess($process_name){
	$cmd = `ps ax | grep $process_name | grep -v 'grep'`;
	$return = ''.$cmd.'';
	$return = explode("\n", $return);
		
	foreach($return as $v){
		$yy = explode(" ", trim($v));		
		if(@intval($yy[0])){
			echo "kill ".$yy[0]."\r\n";
			echo system("kill ".$yy[0]);
		}		
	}
}

?>