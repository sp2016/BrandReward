<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
include_once(INCLUDE_ROOT . "func/func.php");

$id_arr = array();
$is_debug = false;
$pid = "";
$is_fast = false;
if(isset($_SERVER["argc"]) && $_SERVER["argc"] > 1)
{
	foreach($_SERVER["argv"] as $v){
		$tmp = explode("=", $v);		
		if($tmp[0] == "--affid"){			
			$id_arr = explode(",", $tmp[1]);
		}elseif($tmp[0] == "--debug"){
			$is_debug = true;
		}elseif($tmp[0] == "--pid"){
			$pid = " and id = " .intval($tmp[1]);
		}elseif($tmp[0] == "--fast"){
			$is_fast = true;
		}
	}			
}



echo "<< Start @ ".date("Y-m-d H:i:s")." >>\r\n";

$date = date("Y-m-d H:i:s");

$objProgram = New Program();

killProcess();

$affiliate_list = $objProgram->getAllAffiliate($id_arr);

foreach($affiliate_list as $affid => $aff_v){
	if($affid == 191) continue;
	
	while(1){
		if(checkProcess()){
			$cmd = "php /home/bdg/program/cron/first_set_program_intell.php --affid=$affid --onlyactive --nottoredis > /home/bdg/program/cron/test/temp_$affid.log  2>&1 &";
			system($cmd);
			echo $cmd."\r\n";
			//sleep(1);
			break;
		}else{
			echo "sleep...";
			sleep(3);
		}
	}
	
}

//$cmd = "php /home/bdg/program/cron/set_domain_program.php --all --redis > /home/bdg/program/cron/test/temp_set_domain_program.log  2>&1 &";
//system($cmd);


exit;

function checkProcess(){
	$cmd = 'ps aux | grep /home/bdg/program/cron/first_set_program_intell.php -c';
	$xx = intval(system($cmd));
	if($xx > 5){
		return false;
	}else{
		return true;
	}
}



function killProcess(){
	$xx = `ps ax | grep /home/bdg/program/cron/first_set_program_intell.php`;
	$xx = ''.$xx.'';
	
	$xxx = explode("\n", $xx);
	
	
	foreach($xxx as $v){
		$yy = explode(" ", trim($v));
		//print_r($yy);
		$id = $yy[0];
		
		if($id){
			echo $id."\r\n";
			echo system("kill ".$id);
		}
		
	}
	//exit;
}

echo "end";
exit;

?>