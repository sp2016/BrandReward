<?php
echo 'update tracking min start : '.date('Y-m-d H:i:s')."\n";
global $_cf,$_db,$_req;
$sql = 'SELECT id FROM bd_out_tracking_min ORDER BY id DESC LIMIT 1';
$row_last = $_db->getRows($sql);

$last_id = 0;
if(!empty($row_last)){
    $last_id = $row_last[0]['id'];
}

$tmp_file = '/tmp/mysql/bd_out_tracking_min.sql';
if(file_exists($tmp_file)){
    unlink($tmp_file);
}

$colums_ex = 'createddate,id,sessionId,publishTracking,domainId,programId,affId,site,SUBSTRING(created,12,2) as hour,country ';
$colums_in = 'createddate,id,sessionId,publishTracking,domainId,programId,affId,site,hour,country ';

#export increase data
$sql = "select ".$colums_ex." from bd_out_tracking where id > ".$last_id." into outfile '".$tmp_file."' fields terminated by '|' enclosed by '\"' lines terminated by '\r\n'";

$_db->query($sql);

#load file into database
$sql = "load data infile  '".$tmp_file."' into table bd_out_tracking_min fields terminated by '|' enclosed by '\"' lines terminated by '\r\n' (".$colums_in.")";
$_db->query($sql);

$sql = 'SELECT COUNT(*) as c FROM bd_out_tracking_min WHERE id > '.$last_id;
$row_update = $_db->getRows($sql);

echo 'update tracking min rows num : '.$row_update[0]['c']."\n";
    
echo 'update tracking min end : '.date('Y-m-d H:i:s')."\n";
?>
