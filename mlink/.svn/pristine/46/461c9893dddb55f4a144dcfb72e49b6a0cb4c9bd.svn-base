<?php
global $_cf,$_req,$_db;

if(!isset($_req['date']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$_req['date'])){
	echo '';exit();
}

$file = DATA_ROOT.'inner_clicks/clicks_'.date('Ymd',strtotime($_req['date'])).'.dat';

//控制下载速度和因文件大小导致的内存占用问题
if (file_exists($file)) {
	$handle = fopen($file,'r');
	while(!feof($handle)){
        echo fread($handle,1024);
	}
    exit;
}
?>