<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");

$objProgram = New ProgramDb();
$nowDay = date('Y-m-d H:i:s',time());
$sql = "select affid from affiliate_links_all_simple  group by affid";
$affArr = $objProgram->objMysql->getRows($sql);
$alertStr = '';

$startTime = date('Y-m-d H:i:s', strtotime('-24 hours'));
$endTime = date('Y-m-d H:i:s',time());
foreach ($affArr as $v){
    
    $toActive=0;
    $toInActive=0;
    $i = 0;
    $sql = "select afflinkid,pidinaff,isactive from affiliate_links_all_simple where affid = {$v['affid']}";
    $simp_links = $objProgram->objMysql->getRows($sql);
    $count = count($simp_links);
    //echo 'simp_links:'.$count.PHP_EOL;
    
    $sql = "select afflinkid,affmerchantid,isactive from affiliate_links_{$v['affid']} where `type` = 'promotion'";
    $links = $objProgram->objPendingMysql->getRows($sql);
    $count_links = count($links);
    //echo 'links:'.$count_links.PHP_EOL;
    
    $format_links = array();
    foreach ($links as $links_value){
        $format_links[$links_value['afflinkid'].'_'.$links_value['affmerchantid']] = $links_value['isactive'];
    }
    
    
    foreach ($simp_links as $value){
        $i++; 
        //if($i%1000===0){
        //    echo 'count:'.$count.'=====>'. $i.PHP_EOL;
        //}
        if(isset($format_links[$value['afflinkid'].'_'.$value['pidinaff']]) && $format_links[$value['afflinkid'].'_'.$value['pidinaff']] != $value['isactive']){
        
            //echo $value['afflinkid'].PHP_EOL;
            if($value['isactive'] == 'NO'){
                $toActive ++;
            }
            if($value['isactive'] == 'YES'){
                //echo $value['afflinkid'].PHP_EOL;
                $toInActive ++;
            }
        }
    }
    $alertStr .= 'affid:'.$v['affid'] .'===>toActive('.$toActive.")||".'toInActive('.$toInActive.')'.PHP_EOL;
    
    
    $sql = "select count(*) as amount from affiliate_links_{$v['affid']} where `LastUpdateTime` >= '$startTime' and `LastUpdateTime` < '$endTime'";
    $links = $objProgram->objPendingMysql->getFirstRow($sql);
    if($links['amount'] <= 0){
        $alertStr .= 'affid:'.$v['affid'].'crawl count 0'.PHP_EOL;
    }
    
}



//统计每天content_feed_new add toactive toinactive
$startTime = date('Y-m-d H:i:s', strtotime('-24 hours'));
$endTime = date('Y-m-d H:i:s',time());

//新增
$sql = "select count(*) as amount from content_feed_new where `AddTime` >= '$startTime' and `AddTime` < '$endTime' and  `status` = 'active'";

$addc = $objProgram->objMysql->getFirstRow($sql);

//toactive
$sql = "select count(*) as amount from content_feed_new where `LastUpdateTime` >= '$startTime' and `LastUpdateTime` < '$endTime' and  `status` = 'Active'";
$toactivec = $objProgram->objMysql->getFirstRow($sql);

//toinactive
$sql = "select count(*) as amount from content_feed_new where `LastUpdateTime` >= '$startTime' and `LastUpdateTime` < '$endTime' and  `status` = 'InActive'";
$toinactivec = $objProgram->objMysql->getFirstRow($sql);

$alertStr .= date('Y-m-d',strtotime($startTime)).'====>AddNewCount:'.$addc['amount'].'||'.'ToActive:'.$toactivec['amount'].'||'.'ToInactive:'.$toinactivec['amount'];

$to = "merlinxu@brandreward.com,stanguan@meikaitech.com";
//$to = "merlinxu@brandreward.com";
AlertEmail::SendAlert('Check Content Feed Data',nl2br($alertStr), $to);




exit;



?>