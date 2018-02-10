<?php
global $_cf,$_req,$_db;


//track guest impression data 
//not do yet


//update publisher JsCode stats
$sql = 'SELECT * FROM publisher_account where ApiKey = "'.addslashes($_req['key']).'"';
$row = $_db->getFirstRow($sql);

$sql = 'INSERT INTO publisher_stats (PID,JsCode,JsLastTime) VALUE ('.$row['ID'].',"YES","'.date('Y-m-d H:i:s').'") ON DUPLICATE KEY UPDATE JsCode="YES",JsLastTime="'.date('Y-m-d H:i:s').'"';
$_db->query($sql);
echo 'success';
exit();
?>