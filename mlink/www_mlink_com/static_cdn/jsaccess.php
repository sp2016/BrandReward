<?php
define('ROOT',dirname(dirname(__FILE__)));
include_once(ROOT.'/conf_ini.php');
include_once(INCLUDE_ROOT . 'init.php');

global $db;
$sharelist = array(
'www.brandreward.com',
'www.googleadservices.com',
'www.facebook.com',
'www.twitter.com',
'www.pinterest.com',
'www.linkedin.com',
'www.reddit.com',
'www.tumblr.com',
'www.digg.com',
'www.google.com',
'www.stumbleupon.com',
'www.whatsapp.com',
'www.vk.com',
'connect.ok.ru',
'www.xing.com',
'share.flipboard.com',
'www.meneame.net',
'www.livejournal.com',
'del.icio.us',
'www.youtube.com',
'www.blogger.com',
'www.buffer.com'
);
$ignorelist = array();
$whitelist = array();
if(empty($_GET['key'])){
    $res = 0;
}else{
    $sql = 'SELECT * FROM publisher_account WHERE ApiKey = "'.addslashes(trim($_GET['key'])).'"';
    $rows = $db->getRows($sql);
    $row = $rows[0];

    if($row['JsWork'] == 'yes'){
        $res = 1;

        $sql = 'INSERT INTO publisher_stats (PID,JsCode,JsLastTime,JsFirstTime) VALUE ('.$row['ID'].',"YES","'.date('Y-m-d H:i:s').'","'.date('Y-m-d H:i:s').'") ON DUPLICATE KEY UPDATE JsCode="YES",JsLastTime="'.date('Y-m-d H:i:s').'"';
        $db->query($sql);
        
        $JsIgnoreDomain = trim($row['JsIgnoreDomain']);
        if(!empty($JsIgnoreDomain)){
            $ignorelist = explode("\n",$JsIgnoreDomain);
            foreach($ignorelist as $k=>$v){
                $ignorelist[$k] = trim($v);
            }
        }

        $JsWhiteDomain = trim($row['JsWhiteDomain']);
        if(!empty($JsWhiteDomain)){
            $whitelist = explode("\n",$JsWhiteDomain);
            foreach($whitelist as $k=>$v){
                $whitelist[$k] = trim($v);
            }
        }
    }else{
        $res = 0;
    }
}

$ignorelist = array_merge($sharelist,$ignorelist);
echo "callbackAccess(".$res.",".json_encode($ignorelist).",".json_encode($whitelist).");";
?>
