<?php
include_once(dirname(__FILE__) . "/const.php");

define("ALERT_EMAIL", "stanguan@meikaitech.com,ryanzou@brandreward.com");
$longopts = array("affid:", "method:", "merid", "ignorecheck::", "daemon::", "logfile", "silent::", "siteid", "alert", "startdate", "enddate");
$options = GetOptions::get_long_options($longopts);

$paras = array();
if($options["daemon"])
{
	$options["alert"] = 1;
	if(!$options["logfile"])
	{
		$options["logfile"] = INCLUDE_ROOT . "logs/" . basename(__FILE__);
		$arr_log_para = array();
		$arr_log_para[] = $options["method"];
		$arr_log_para[] = $options["affid"];
		$arr_log_para[] = date("His") . "_" . date("Ymd");
		$arr_log_para[] = "log";
		$options["logfile"] .= "." . implode(".",$arr_log_para);
	}
	$cmd = "nohup php " . __FILE__ . " " . GetOptions::get_option_str($longopts,$options,array("daemon","silent")) . " > " . $options["logfile"] . " 2>&1 &";
	echo "start daemon mode ...\n";
	echo "log file is: " . $options["logfile"] . "\n";
	system($cmd);
	if(!$options["silent"])
	{
		sleep(3);
		system("tail -f " . $options["logfile"]);
	}
	exit;
}

if($options["alert"] == 1)
{
	$sessionId = md5($options["logfile"].SID);
	$error_descp = '';
	$cmd = "php " . __FILE__ . " " . GetOptions::get_option_str($longopts,$options,array("daemon","logfile","silent","alert"));
	
    //insert crawl log
    operate_crawl_log(array('sessionId'=>$sessionId,'affid'=>$options["affid"],'method'=>$options["method"],'logfile'=>$options["logfile"]),'insert');
    
    system($cmd,$retval);
	
	if($retval > 0)
	{
	    $error_descp = strip_tags(shell_exec("tail -n 50 " . $options["logfile"]));
		//send alert
		if(SID == 'bdg01'){
			$alert_subject = "MK Crawl issues: " . $options["method"] . " for aff " . $options["affid"] . " failed @ " . date("Y-m-d H:i:s");
		}else{
			$alert_subject = "BrandReward Crawl issues: " . $options["method"] . " for aff " . $options["affid"] . " failed @ " . date("Y-m-d H:i:s");
		}		
		$alert_body = "$cmd" . "\n";
		$alert_body .= "log file: " . $options["logfile"] . "\n";
		$alert_body .= "\n\n";
		if($options["logfile"] && file_exists($options["logfile"])){
			$alert_body .= strip_tags(shell_exec("tail -n 50 " . $options["logfile"]));
		}
		$to = "stanguan@meikaitech.com,ryanzou@brandreward.com";
		AlertEmail::SendAlert($alert_subject,nl2br($alert_body), $to);
		operate_crawl_log(array('sessionId'=>$sessionId,'error_descp'=>$error_descp,'status'=>'error'),'update');
		mydie("die: job failed, alert email was sent ... \n");
	}
	
	//insert crawl log
	operate_crawl_log(array('sessionId'=>$sessionId,'error_descp'=>'','status'=>'finish'),'update');
	exit;
}

if($options["ignorecheck"]) 
	$paras["ignorecheck"] = 1;
$oLinkFeed = new LinkFeed($paras);
$arr_aff_id = explode(",",$options["affid"]);
$start_date = isset($options["startdate"]) ? $options["startdate"] : '';
$end_date = isset($options["enddate"]) ? $options["enddate"] : '';

foreach($arr_aff_id as $aff_id)
{
	$aff_id = trim($aff_id);
	if(!is_numeric($aff_id))
		continue;
	switch($options["method"])
	{
		// syncall is now no longer necessary.
		// getandsyncall doing same thing as getalllink now.
		// getall doing same thing as getalllink now.
		case "getall":
		case "getandsyncall":
		case "getalllink":
		case "getalllinks":
			$oLinkFeed->GetAllLinksFromAff($aff_id);
			break;
		case "getallpagelink":
		case "getallpagelinks":
		case "getlink":
			$oLinkFeed->GetAllLinksFromAff($aff_id, "", "linkonly");
			break;
		case "getallfeed":
		case "getallfeeds":
		case "getfeed":
			$oLinkFeed->getCouponFeed($aff_id, "", "feedonly");
			break;
		case "getonemerchantpagelink":
		case "getonemerchantpagelinks":
		case "onepagelink":
		case "onepagelinks":
			if(!$options["merid"]) 
				mydie("die: merid not found!\n");
			$oLinkFeed->ignorecheck = true;
			$merids = explode(',', $options["merid"]);
			foreach ($merids as $merid)
			{
				$oLinkFeed->GetAllLinksFromAffByMerID($aff_id, $merid);
			}
			break;
		// get merchant is now no longer necessary. replaced by getprogram.
		case "getallmerchant":
		case "getallmerchants":
		case "getprogram":
			$oLinkFeed->GetAllProgram($aff_id);
			break;
        case "getstatus":
            $oLinkFeed->GetAllStatus($aff_id);
            break;
        case "getinvalidlink" :
		case "getinvalidlinks":
			$oLinkFeed->GetInvalidLinksFromAff($aff_id);
			break;
		case "getmessage":
		case "getmessages":
			$oLinkFeed->GetMessageFromAff($aff_id);
			break;
	    case "getproduct":
			$oLinkFeed->GetAllLinksFromAff($aff_id, "", "productonly");
			break;
        case "gettransaction":
            $oLinkFeed->GetAllTransaction($aff_id, $start_date, $end_date);
            break;
		// syncall is now no longer necessary.
		case "synconesitemerchant":
		case "synconesitelinks":
		case "synconesiteall":
		case "syncall":
			mydie("sync* is outdated: " . $options["method"] . "\n");
		default:
			mydie("die: wrong method: " . $options["method"] . "\n");
	}
}

function operate_crawl_log($data,$flag){
    
    $sessionId = $data['sessionId'];
    $markTime = date('Y-m-d H:i:s');
    if(SID == 'bdg01'){
        $platform = 'MK';
    }
    elseif(SID == 'bdg02'){    
        $platform = 'BR';
    }
    $mysqlTmp = new MysqlExt(TASK_DB_NAME, TASK_DB_HOST, TASK_DB_USER, TASK_DB_PASS);
    
    if($flag == 'insert'){
        $affid = $data['affid'];
        $method = $data['method'];
        $logfile = $data['logfile'];
        $date  = date('Y-m-d');
        
        $sql = "INSERT INTO crawl_script_run_log (sessionId,date,startTime,platform,affid,method,logfile)
            VALUES ('".$sessionId."','".$date."','".$markTime."','".$platform."',".$affid.",'".$method."','".$logfile."')";
        $mysqlTmp->query($sql);
        
    }elseif ($flag == 'update'){

        $error_descp = addslashes($data['error_descp']);
        $status = $data['status'];
        $sql = "SELECT * FROM crawl_script_run_log where sessionId = '".$sessionId."'";
        $logInfo = $mysqlTmp->getRows($sql);
        if($logInfo){
            $sql = "UPDATE crawl_script_run_log SET endTime = '".$markTime."', status = '".$status."', error_descp = '".$error_descp."'  WHERE sessionId = '".$sessionId."'";
            $mysqlTmp->query($sql);
        }
    }
    
    return;
}


print "<< Succ >>\n\n";
