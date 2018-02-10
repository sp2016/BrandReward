<?php
include_once(dirname(__FILE__) . "/const.php");

$id_arr = array();
$is_debug = false;
$method = "";
if(isset($_SERVER["argc"]) && $_SERVER["argc"] > 1)
{
	foreach($_SERVER["argv"] as $v){
		$tmp = explode("=", $v);		
		if($tmp[0] == "--affid"){			
			$id_arr = array_flip(explode(",", $tmp[1]));
		}elseif($tmp[0] == "--debug"){
			$is_debug = true;
		}elseif($tmp[0] == "--method"){
			$method = trim($tmp[1])."CrawlStatus";
		}
	}			
}



echo "<< Start @ ".date("Y-m-d H:i:s")." >>\r\n";

$date = date("Y-m-d H:i:s");
$objProgram = New ProgramDb();
$process = "/home/bdg/program/crawl/job.data.php";

//killProcess($process);


$sql = "select * from aff_crawl_config where status = 'active'";

$crawl_config = $objProgram->objPendingMysql->getRows($sql, "AffId");
$method_config = array(	"LinkCrawlStatus" => "getallpagelinks", "ProgramCrawlStatus" => "getprogram", "InvaildLinkCrawlStatus" => "getinvalidlinks", 
						"FeedCrawlStatus" => "getallfeeds", "MessageCrawlStatus" => "getmessages", "ProductCrawlStatus" => "getproduct","StatsCrawlStatus" => "gettransaction");


foreach($crawl_config as $affid => $aff_v){
	if($affid == 191) continue;	
	if(count($id_arr) && !isset($id_arr[$affid])) continue;
	//检查wf_aff isactive
	$affSql = "SELECT id,TransactionCrawlStatus FROM wf_aff WHERE isactive = 'YES' AND ID = $affid LIMIT 1";
	$affArr = $objProgram->objMysql->getFirstRow($affSql);
	if(count($affArr) <= 0)
	{
	    continue;
	}
	if($method == 'Stats' && $affArr['TransactionCrawlStatus'] == 'NO'){
	    continue;
	}
	
	if(!isset($crawl_config[$affid]) || !isset($method_config[$method]) || $crawl_config[$affid][$method] != "Yes") continue;
	
	$sleep = 10;
	$cmd = "php " . $process . " --affid=$affid --method=$method_config[$method] --daemon --silent &";
	while(1){
		if(checkProcess("$process | grep $method_config[$method] ", 20)){
			if(checkProcess("'$process --affid=$affid ' | grep $method_config[$method] ", 0)){							
				system($cmd);
				echo $cmd." | start @ ".date("y-m-d H:i:s")."\r\n";
			}else{				
				echo $cmd." | not finished @ ".date("y-m-d H:i:s")."\r\n";
			}
			//sleep(1);
			break;
		}else{
			//echo "sleep...";
			sleep($sleep);
			if($sleep >= 60){
				$sleep = 60;
			}else{
				$sleep += 10;
			}			
		}
	}
}

/*if($method == 'ProgramCrawlStatus'){
	echo "waiting crawl finished.\r\n";
	$sleep = 30;
	while(1){
		$xx = array();
		$cmd = "ps aux | grep grep -v | grep '/bin/sh' -v | grep getprogram | grep " . $process . " -c";
		exec($cmd, $xx);		
		if($xx[0] == 0){
			echo "jobs finished.\r\n";
			
			$sql = "select affid, count(*) as cnt from program_change_log where AddTime >= '$date' and FieldName = 'statusinaff' and FieldValueOld = 'active' group by affid order by cnt desc";
			$q1 = $objProgram->objMysql->getRows($sql);
			
			$sql = "select affid, count(*) as cnt from program_change_log where AddTime >= '$date' and FieldName = 'partnership' and FieldValueOld = 'active' group by affid order by cnt desc";
			$q2 = $objProgram->objMysql->getRows($sql);
			
			$sql = "select affid, count(*) as cnt from program_change_log where AddTime >= '$date' and FieldName = 'homepage' group by affid order by cnt desc";
			$q3 = $objProgram->objMysql->getRows($sql);
			
			$sql = "select affid, count(*) as cnt from program_change_log where AddTime >= '$date' and FieldName = 'TargetCountryExt' group by affid order by cnt desc";
			$q4 = $objProgram->objMysql->getRows($sql);
			
			$alert_subject = "program crawl status $date @ " . date("Y-m-d H:i:s");
			
			$alert_body = "<table border=1>";
			$alert_body .= "<tr><th>statusinaff:</th><th></th></tr>";
			$alert_body .= "<tr><th>affid<th><th>cnt</th></th>";
			foreach($q1 as $v){
				$alert_body .= "<tr><td>".explode("</td><td>", $v)."</td></tr>";			
			}
			
			$alert_body .= "<tr><td>partnership:<td></tr>";
			$alert_body .= "<tr><th>affid<th><th>cnt</th></th>";
			foreach($q2 as $v){
				$alert_body .= "<tr><td>".explode("</td><td>", $v)."</td></tr>";			
			}
			
			$alert_body .= "<tr><td>homepage:<td></tr>";
			$alert_body .= "<tr><th>affid<th><th>cnt</th></th>";
			foreach($q3 as $v){
				$alert_body .= "<tr><td>".explode("</td><td>", $v)."</td></tr>";			
			}
			
			$alert_body .= "<tr><td>TargetCountryExt:<td></tr>";
			$alert_body .= "<tr><th>affid<th><th>cnt</th></th>";
			foreach($q4 as $v){
				$alert_body .= "<tr><td>".explode("</td><td>", $v)."</td></tr>";			
			}
			
			$to = "stanguan@meikaitech.com";
			
			AlertEmail::SendAlert($alert_subject,nl2br($alert_body), $to);
			
			echo "send alert.\r\n";
			break;
		}else{
			sleep($sleep);			
		}
	}
}*/
//$cmd = "php /home/bdg/program/cron/set_domain_program.php --all --redis > /home/bdg/program/cron/test/temp_set_domain_program.log  2>&1 &";
//system($cmd);
echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";

exit;

function checkProcess($process, $cnt){
	if($cnt < 0 || $cnt > 30) $cnt = 30;
	$cmd = "ps aux | grep grep -v | grep " . $process . " -c";
	exec($cmd, $xx);
	//print_r($xx);
	//echo $xx;exit;
	if($xx[0] > $cnt){
		return false;
	}else{
		return true;
	}
}


function killProcess($process){
	$xx = `ps ax | grep ` . $process ;
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
