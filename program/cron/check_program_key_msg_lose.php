<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");

define("PROCESS_CNT", 5);

if(SID == 'bdg02')
    $title = 'BR check program info missing!';
else
    $title = 'MK check program info missing!';
$str = 'Check program crawl info missing!<br/>';

echo "<< Start @ ".date("Y-m-d H:i:s")." >>\r\n";

$affid_arr = array();
$pId_arr = array();
$field_arr = array();

$field_list = array('Homepage','CategoryExt','StatusInAff','Partnership','CommissionExt','TargetCountryExt');

$is_debug = $is_child = false;

$checktime = $programid = '';
if(isset($_SERVER["argc"]) && $_SERVER["argc"] > 1)
{
    foreach($_SERVER["argv"] as $v){
        $tmp = explode("=", $v);
        if($tmp[0] == "--child"){
            $is_child = true;
        }elseif($tmp[0] == "--affid"){
            $affid_arr = explode(',', $tmp[1]);
            $affid_arr = array_map(function($person) { return intval($person);}, $affid_arr);
        }elseif($tmp[0] == "--debug"){
            $is_debug = true;
        }elseif($tmp[0] == "--pid"){
            $pId_arr = explode(',', $tmp[1]);
            $pId_arr = array_map(function($person) { return trim($person);}, $pId_arr);
        }elseif($tmp[0] == "--checktime"){
            $checktime = trim($tmp[1],"'");
        }elseif($tmp[0] == "--field"){
            $field_arr = explode(',', $tmp[1]);
            $field_arr = array_map(function($person) { return trim($person);}, $field_arr);
        }elseif($tmp[0] == "--pid"){
            $programid = trim($tmp[1]);
        }
    }
}

if (!$checktime)
    $checktime = date("Y-m-d",strtotime("-1 day"));
$endTime = date("Y-m-d", strtotime($checktime) + 86400);

if ($field_arr){
    foreach ($field_arr as $k => $v) {
        if (!in_array($v, $field_list)) {
            unset($field_list[$k]);
        }
    }
}

if(!$is_child){
    $process_name = __FILE__;
    killProcess($process_name);
    $tStr = strtotime($checktime);
    $cmd = "nohup php $process_name --checktime='$checktime' --child >> /home/bdg/logs/check_key_msg_lose_{$tStr}.log 2>&1 &";
    echo "\t".$cmd."\r\n";
    system($cmd);

}else{

    if (!$field_arr){
        $field_arr = $field_list;
    }

    $objProgram = New Program();
    $where_arr = '';
    if($checktime) $where_arr[] = " (AddTime > '$checktime' AND AddTime < '$endTime')";

    if (!count($affid_arr)) {
        $sql = "select Id from wf_aff where IsActive='YES' and IsInHouse='NO' order by ID";
        $aff_arr = $objProgram->objMysql->getRows($sql, "Id");
        $affid_arr = array_keys($aff_arr);
    }
    if(count($pId_arr)) $where_arr[] = " and ProgramId in ('".implode("','",  $pId_arr)."')";
    if(count($field_arr)) $where_arr[] = " and FieldName in ('".implode("','",  $field_arr)."')";
    $out_put = array();

    foreach ($affid_arr as $val) {
        $sql = "SELECT ProgramId,FieldName FROM program_change_log WHERE AffId={$val} AND (FieldValueOld <> '' or FieldValueOld is NOT NULL) AND (FieldValueNew='' or FieldValueNew is NULL ) and". implode($where_arr);
        if ($is_debug){
            echo $sql . "\r\n";
        }

        $change_log_arr = $objProgram->objMysql->getRows($sql);
        $show_arr = array();
        foreach ($change_log_arr as $cVal) {
            if (!isset($show_arr[$cVal['FieldName']])){
                $show_arr[$cVal['FieldName']] = array();
            }
            if (!in_array($cVal['ProgramId'], $show_arr[$cVal['FieldName']])) {
                $show_arr[$cVal['FieldName']][] = $cVal['ProgramId'];
            }
        }
        foreach ($show_arr as $sK => $sV){
            $out_put[] = array(
                'AffId' => $val,
                'MissingField' => $sK,
                'Count' => count($sV)
            );
        }
    }

    array_multisort(array_map(function($c){return $c['Count'];},$out_put),SORT_DESC, $out_put);

    $str .= "The network missing list like that:\r\n";
    $str .= "--------------------------------------------------------------------";
    $str .= "\r\n\tAffId\t\t\tMissingField\t\t\t\tCount";
    $str1 = $str2 = '';
    foreach ($out_put as $val) {
        if (in_array($val['AffId'], array(1,6,2,2025,10))) {
            $str1 .= "\t{$val['AffId']}\t\t\t{$val['MissingField']}\t\t\t\t{$val['Count']}\r\n";
        }else {
            $str2 .= "\r\n\t{$val['AffId']}\t\t\t{$val['MissingField']}\t\t\t\t{$val['Count']}";
        }
    }
    $str .= $str2 . "\r\n-------------------------------------------------------------------\r\n" . $str1;

    $str .= "\r\nNote:The networks affid in (1,6,2,2025,10) miss key field because them not allow crawl the programs that are haven't partnership with us!";
    $to = "stanguan@meikaitech.com,merlinxu@brandreward.com,ryanzou@brandreward.com";
    //$to = "ryanzou@brandreward.com";
    AlertEmail::SendAlert($title, nl2br($str), $to, false);
    echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";
}
exit;


function checkProcess($process_name){
    $cmd = `ps aux | grep $process_name -c`;
    $return = ''.$cmd.'';
    if($return > PROCESS_CNT){
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
        //print_r($yy);
        if(@intval($yy[0])){
            echo system("kill ".$yy[0]);
        }
    }
}