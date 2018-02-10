<?php
include_once(dirname(__FILE__) . "/const.php");

$longopts = array("aff:", "affid", "accid", "siteid", "method:", "merid", "ignorecheck::", "daemon::", "logfile", "silent::", "alert", "accountid", "batchid", "checkbatchid", "checkfields");
$options = GetOptions::get_long_options($longopts);

$oLinkFeed = new LinkFeed();
if (empty($options["aff"]))
{
    $reqAffid = isset($options['affid']) ? $options['affid'] : '';
    $reqAccid = isset($options['accid']) ? $options['accid'] : '';
    $reqSiteid = isset($options['siteid']) ? $options['siteid'] : '';
    $affSiteAccNames = $oLinkFeed->getAffNamesById($reqAffid, $reqAccid, $reqSiteid);
    $affSiteAccNames = array_keys($affSiteAccNames);
} else {
    $affSiteAccNames = explode(",",$options["aff"]);
}

$options_arr = array();
foreach ($affSiteAccNames as $name) {
    if (!trim($name)) {
        continue;
    }
    $options_arr[$name] = $options;
    $options_arr[$name]['aff'] = $name;
}

foreach ($options_arr as $option)
{
    $paras = array();
    $aff_arr = $oLinkFeed->getAffAccountSiteByName(trim($option['aff']));
    $aff_id = $aff_arr['AffID'];
    $account_id = $aff_arr['AccountID'];
    $site_id = $aff_arr['SiteID'];

    if($option["daemon"])
    {
        $option["alert"] = 1;
        if(!$option["logfile"])
        {
            $option["logfile"] = INCLUDE_ROOT . "logs/" . basename(__FILE__);
            $arr_log_para = array();
            $arr_log_para[] = $option['method'];
            $arr_log_para[] = $option['aff'];
            $arr_log_para[] = date("His") . "_" . date("Ymd");
            $arr_log_para[] = "log";
            $option["logfile"] .= "." . implode(".",$arr_log_para);
        }
        $cmd = "nohup php " . __FILE__ . " " . GetOptions::get_option_str($longopts,$option,array("daemon","silent")) . " > " . $option["logfile"] . " 2>&1 &";
        echo "start daemon mode ...\n";
        echo "log file is: " . $option["logfile"] . "\n";
        system($cmd);
        if(!$option["silent"])
        {
            sleep(3);
            system("tail -f " . $option["logfile"]);
        }
        exit;
    }

    if($option["alert"] == 1)
    {
        $error_descp = '';
        $cmd = "php " . __FILE__ . " " . GetOptions::get_option_str($longopts,$option,array("daemon","logfile","silent","alert"));

        $oLinkFeed = new LinkFeed($paras);
        $batchid = uniqid('b');
        $cmd .= ' --batchid='.$batchid;
        $sql = "insert into crawl_batch(BatchID, CrawlType, AffID, StartTime, LogFile, CrawlJobStatus, BatchStatus) values('$batchid', 'Program', '{$aff_id}', '".date('Y-m-d H:i:s')."', '".addslashes($options["logfile"])."', 'Crawling', 'Unchecked')";
        $oLinkFeed->objMysql->query($sql);

        system($cmd,$retval);

        if($retval > 0)
        {
            $oLinkFeed = new LinkFeed($paras);
            $sql = "update crawl_batch set CrawlJobStatus = 'Error', EndTime = '".date('Y-m-d H:i:s')."' where BatchID = '$batchid'";
            $oLinkFeed->objMysql->query($sql);
            $error_descp = strip_tags(shell_exec("tail -n 50 " . $option["logfile"]));
            //send alert
            $alert_subject = "BR03 Crawl issues: " . $option["method"] . " for aff {$option['aff']} failed @ " . date("Y-m-d H:i:s");
            $alert_body = "$cmd" . "\n";
            $alert_body .= "log file: " . $option["logfile"] . "\n";
            $alert_body .= "\n\n";
            if($option["logfile"] && file_exists($option["logfile"])){
                $alert_body .= strip_tags(shell_exec("tail -n 50 " . $option["logfile"]));
            }
            $to = "ryanzou@brandreward.com";
            AlertEmail::SendAlert($alert_subject,nl2br($alert_body), $to);
            mydie("die: job failed, alert email was sent ... \n");
        }

        $oLinkFeed = new LinkFeed();
        $sql = "update crawl_batch set CrawlJobStatus='Finished',EndTime ='".date('Y-m-d H:i:s')."' where BatchID='$batchid'";
        $oLinkFeed->objMysql->query($sql);
        exit;
    }

    if($option["ignorecheck"])
        $paras["ignorecheck"] = 1;
    $oLinkFeed = new LinkFeed($paras);

    switch($option["method"])
    {
        case "getalllinks":
            $oLinkFeed->GetAllLinksFromAff($aff_id);
            break;
        case "getlink":
            $oLinkFeed->GetAllLinksFromAff($aff_id, "", "linkonly");
            break;
        case "getfeed":
            $oLinkFeed->getCouponFeed($aff_id, "", "feedonly");
            break;
        case "getonemerchantpagelink":
        case "getonemerchantpagelinks":
        case "onepagelink":
        case "onepagelinks":
            if(!$option["merid"])
                mydie("die: merid not found!\n");
            $oLinkFeed->ignorecheck = true;
            $merids = explode(',', $option["merid"]);
            foreach ($merids as $merid)
            {
                $oLinkFeed->GetAllLinksFromAffByMerID($aff_id, $merid);
            }
            break;
        case "getprogram":
            $oLinkFeed->batchID = $option["batchid"];
            $oLinkFeed->GetAllProgram($aff_id, $account_id, $option['aff']);
            break;
        case "getstatus":
            $oLinkFeed->GetAllStatus($aff_id);
            break;
        case "getinvalidlink" :
            $oLinkFeed->GetInvalidLinksFromAff($aff_id);
            break;
        case "getmessage":
            $oLinkFeed->GetMessageFromAff($aff_id);
            break;
        case "checkbatchdata":
            $oLinkFeed->checkBatchID = $option["checkbatchid"];
            $oLinkFeed->checkFields = $option["checkfields"];
            $oLinkFeed->CheckCrawlBatchData($aff_id, $site_id);
            break;
        default:
            mydie("die: wrong method: " . $option["method"] . "\n");
    }

    print "\t<< Succ >>\n\n";
}

exit;
