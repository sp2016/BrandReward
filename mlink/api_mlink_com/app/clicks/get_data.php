<?php
global $_cf,$_req,$_db;

if(!isset($_req['starttime']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$_req['starttime'])){
        over('@error:param[starttime] is empty or wrong format');
}
if(!isset($_req['endtime']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$_req['endtime'])){
        over('@error:param[endtime] is empty or wrong format');
}
if($_req['starttime'] > $_req['endtime'])
        over('@error:the range between [starttime] and [endtime] need be less than 30 days');
if(date_range($_req['starttime'],$_req['endtime']) > 31)
        over('@error:the range between [starttime] and [endtime] need be less than 30 days');

if($_req['key'] == '3644a684f98ea8fe223c713b77189a77'){
	$sql  = 'SELECT id,created,publishTracking,pageUrl FROM bd_out_tracking WHERE  createddate >= "'.$_req['starttime'].'" AND createddate <= "'.$_req['endtime'].'" AND site = "70efdf2ec9b086079795c442636b55fb" AND PublishTracking LIKE "%mega%"';
}else{
	$sql  = 'SELECT id,created,publishTracking,pageUrl FROM bd_out_tracking WHERE  createddate >= "'.$_req['starttime'].'" AND createddate <= "'.$_req['endtime'].'" AND site = "'.addslashes($_req['key']).'"';
}
$data = $_db->getRows($sql);

echo "@PageTotal:1\t@PageNow:1\t@Num:".count($data)."\n";
echo "ClickID\tDatetime\tID\tClickPage\n";
foreach($data as $k=>$v){
	echo join("\t",$v)."\n";
}
exit();

function date_range($date1,$date2){
        $range = round( (strtotime($date2) - strtotime($date1))/86400  );
        return $range;
}

?>
