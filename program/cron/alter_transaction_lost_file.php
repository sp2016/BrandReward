<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");

$objProgram = New ProgramDb();

$alertStr = '';
$startTime = date('Y-m-d H:i:s', strtotime('-24 hours'));
$endTime = date('Y-m-d H:i:s',time());


$sql = "select * from crawl_transaction_lost_file_logs where `AddTime` >= '$startTime' and `AddTime` < '$endTime' ";

$alterArr = $objProgram->objMysql->getRows($sql);

foreach ($alterArr as $value){

    $alertStr .= $value['AffName'].'=>'.$value['AffId']." ,LostFile: ".$value['LostFile'].PHP_EOL;
    
}


$to = "merlinxu@brandreward.com,stanguan@meikaitech.com";
//$to = "merlinxu@brandreward.com";
AlertEmail::SendAlert('Crawl Transaction Lost File:',nl2br($alertStr), $to);

exit;



?>