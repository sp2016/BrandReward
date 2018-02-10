<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");

$objProgram = New ProgramDb();

$alertStr = '';
$startTime = date('Y-m-d H:i:s', strtotime('-24 hours'));
$endTime = date('Y-m-d H:i:s',time());


$sql = "select * from crawl_links_logs where `CheckTime` >= '$startTime' and `CheckTime` < '$endTime' ";
$alterArr = $objProgram->objMysql->getRows($sql);

foreach ($alterArr as $value){

    $alertStr .= $value['Type'].':AffID:'.$value['Affid']." ,  To Inactive Too Much(total count:{$value['Amount']},inactive count:{$value['ToInactive']})".PHP_EOL;
    
}


$to = "merlinxu@brandreward.com,stanguan@meikaitech.com";
//$to = "merlinxu@brandreward.com";
AlertEmail::SendAlert('Check Crawl Links Data',nl2br($alertStr), $to);

exit;



?>