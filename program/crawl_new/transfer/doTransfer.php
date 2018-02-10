<?php
/**
 * User: rzou
 * Date: 2017/10/11
 * Time: 15:56
 */

include_once(dirname(__FILE__) . "/const.php");

$longopts = array("aff:", "affid", "accid", "siteid", "method:", "merid", "ignorecheck::", "daemon::", "logfile", "silent::", "alert", "accountid", "comparefield");
$options = GetOptions::get_long_options($longopts);

$transferObj = new transferHelper();
if (empty($options["aff"]))
{
    $reqAffid = isset($options['affid']) ? $options['affid'] : '';
    $reqAccid = isset($options['accid']) ? $options['accid'] : '';
    $reqSiteid = isset($options['siteid']) ? $options['siteid'] : '';
    $affSiteAccNames = $transferObj->getAffNamesById($reqAffid, $reqAccid, $reqSiteid);
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
    $aff_arr = $transferObj->getAffAccountSiteByName(trim($option['aff']));
    $aff_id = $aff_arr['AffID'];
    $account_id = $aff_arr['AccountID'];
    $site_id = $aff_arr['SiteID'];
    $oldSystemAffId = $aff_arr['IdInOldSystem'];


    if($option["daemon"])
    {
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
        system($cmd, $retval);

        if($retval > 0)
        {
            $error_descp = strip_tags(shell_exec("tail -n 50 " . $option["logfile"]));
            //send alert
            $alert_subject = "BR03 do: " . $option["method"] . " for aff {$option['aff']} failed @ " . date("Y-m-d H:i:s");
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

        if(!$option["silent"])
        {
            sleep(3);
            system("tail -f " . $option["logfile"]);
        }
        continue;
    }


    switch($option["method"])
    {
        case "transferData":
            $transferObj->transferDataToDb($aff_id, $option['aff'], $oldSystemAffId);
            break;
        case "compareData":
            $compareFields = empty($option['comparefield']) ? '*' : $option['comparefield'];
            $transferObj->compareDataWithTrueData($oldSystemAffId, $compareFields);
            break;
        default:
            mydie("die: wrong method: " . $option["method"] . "\n");
    }

    print "\t<< Succ >>\n\n";
}

exit;
