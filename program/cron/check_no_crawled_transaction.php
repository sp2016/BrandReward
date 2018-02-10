<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");

$objProgram = New ProgramDb();

$sql = "SELECT * FROM wf_aff WHERE ProgramCrawled = 'YES' AND IsActive = 'YES' AND StatsReportCrawled = 'NO'";
$affArr = $objProgram->objMysql->getRows($sql);

if(SID == 'bdg02')
    $title = 'BR craled Transaction!';
else 
    $title = 'MK craled Transaction!';
    
$str = 'Crawl Porgram, But not Crawl Transaction!<br/>';
foreach ($affArr as $value){
    
    $str .= "NetWork:".$value['Name'].'___affid:'.$value['ID'].PHP_EOL;
    
}


$sql = "SELECT * FROM wf_aff WHERE IsInHouse = 'yes' AND IsActive = 'YES' AND StatsReportCrawled = 'NO'";
$affArr = $objProgram->objMysql->getRows($sql);
foreach ($affArr as $valueH){

    $str .= "NetWork:".$valueH['Name'].'___affid:'.$valueH['ID'].PHP_EOL;

}

$str .= '-----------------------------------------------------------------------------<br/>';

$str .= 'Crawl Transaction, But not Crawl Porgram!<br/>';


$sql = "SELECT * FROM wf_aff WHERE ProgramCrawled = 'NO' AND IsActive = 'YES' AND StatsReportCrawled = 'YES' AND IsInHouse = 'NO'";
$affArr = $objProgram->objMysql->getRows($sql);
foreach ($affArr as $valueG){

    $str .= "NetWork:".$valueG['Name'].'___affid:'.$valueG['ID'].PHP_EOL;

}

if($str){
    $to = "stanguan@meikaitech.com,merlinxu@brandreward.com";
    //$to = "merlinxu@brandreward.com";
    AlertEmail::SendAlert($title,nl2br($str), $to, false);
}
exit;


?>