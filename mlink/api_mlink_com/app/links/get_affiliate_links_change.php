<?php
global $_cf,$_req,$_db;
if(isset($_req['date'])){
    if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$_req['date'])){
        echo 'Date format is wrong!!!';exit();
    }
    $date = str_replace('-','',$_req['date']);
    
}else{
    $date = date('Ymd',time());
}

if(isset($_req['source']) && $_req['source'] == 'manual'){
    $f = DATA_ROOT."links/dumpManualFeeds$date.dat";
}elseif($_req['source'] && $_req['source'] == 'product'){
    $f = DATA_ROOT."products/dumpProductFeeds$date.dat";
}
else{
    $f = DATA_ROOT."links/dumpAffiliateLinksAll$date.dat";
}
//echo $f;exit;
//控制下载速度和因文件大小导致的内存占用问题
if (file_exists($f)) {
        $handle = fopen($f,'r');
        while(!feof($handle)){
        echo fread($handle,1024);
        }
    exit;
}
else{
    die('file not exist!!!');
}
?>
~                                                                                                                                                                                                                                                         
~                                                                                                                                                                                                                                                         
~                                                                                                                                                                                                                                                         
~                                                                           

~                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  
