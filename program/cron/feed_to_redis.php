<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");

$objProgram = New ProgramDb();
echo "<< Start @ ".date("Y-m-d H:i:s")." >>\r\n";
$nowDay = date('Y-m-d H:i:s',time());


if(!checkProcess(__FILE__)){
    echo 'process still runing.\r\n';
    echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";
    exit;
}
function checkProcess($process_name){
	$cmd = `ps aux | grep $process_name | grep 'grep' -v | grep '/bin/sh' -v -c`;
	$return = ''.$cmd.'';
	if($return > 1){
		return false;
	}else{
		return true;
	}
}
$objRedis = new Redis();
$objRedis->pconnect(REDIS_HOST, REDIS_PORT);
$tmp_redis = array();
$tmp_redis = $objRedis->keys(":CF:*");
$tmp_redis = array_flip($tmp_redis);
echo "REDIS :ContentFeed::".count($tmp_redis)."\r\n";

//content feed redis.

$i = $j = 0;
$checkAffUrl = 0;
while(1){
    //$sql = "select a.id, a.encodeid, a.affurl, a.OriginalUrl, a.programid, a.storeid, b.affid, b.domain from content_feed_new a left join program_intell b on a.programid = b.programid where a.encodeid > 0 and a.status = 'active' limit " . $i * 100 . ", 100";
    //$sql = "SELECT a.id,a.encodeid,a.affurl,a.OriginalUrl,a.programid,a.storeid,d.affid,c.domain,c.id AS domainid 
    //     FROM content_feed_new a  LEFT JOIN `r_domain_program` b   ON a.programid = b.pid  LEFT JOIN `domain` c ON b.did = c.id  LEFT JOIN program_intell d  ON a.programid = d.programid  
    //     WHERE a.encodeid > 0  AND a.status = 'active' limit " . $i * 100 . ", 100";
    
    $sql = "SELECT a.id,a.encodeid,a.affurl,a.OriginalUrl,a.programid,a.storeid,d.affid
         FROM content_feed_new a    LEFT JOIN program_intell d  ON a.programid = d.programid
         WHERE a.encodeid > 0  AND a.status = 'active' limit " . $i * 1000 . ", 1000";
    
    $tmp_arr = $objProgram->objMysql->getRows($sql);
    if(!count($tmp_arr)) break;
    foreach($tmp_arr as $v){
         
        //check aff url InActive
        $InActive_arr = array();
        $sql = "select ContentFeedId from check_aff_url where ContentFeedId = {$v['id']} and Status = 'InActive'";
        $InActive_arr = $objProgram->objMysql->getRows($sql);
        if(count($InActive_arr)){
            $checkAffUrl ++ ;
            $sql = "update content_feed_new set status = 'InActive',LastUpdateTime = '$nowDay' where ID = {$v['id']}";
            $objProgram->objMysql->query($sql);
            continue;
        }
        
        //domain
        $sql = "SELECT c.domain,c.id AS domainid FROM content_feed_new a LEFT JOIN `r_domain_program` b  ON a.programid = b.pid  LEFT JOIN `domain` c ON b.did = c.id WHERE  a.id = {$v['id']}";
        $tmp_domain = $objProgram->objMysql->getRows($sql);
        foreach ($tmp_domain as $dv){
            $v['domain'] = $dv['domain'];
            $v['domainid'] = $dv['domainid'];
        }
         
        if(strlen($v['domain']) && strpos($v['domain'], ',') !== false){
            $v['domain'] = substr($v['domain'], 0, strpos($v['domain'], ','));
        }
        $redisArr = array(
            'programid'=>$v['programid'],
            'storeid'=>$v['storeid'],
            'affurl'=> $v['affurl'] ? $v['affurl'] : $v['OriginalUrl'],
            'originalUrl' =>$v['OriginalUrl'], 
            'affid'=>$v['affid'],
            'domain'=>$v['domain'],
            'domainid'=>$v['domainid'],
            'status'=>'active',
            'time'=>date('Y-m-d H:i:s')
        );
        $redisValue = json_encode($redisArr);
        $objRedis->set(":CF:".$v['encodeid'], $redisValue);
        unset($tmp_redis[":CF:".$v['encodeid']]);
        $j++;
    }

    $i++;
    if($i > 10000){
        echo 'warning: ContentFeed 10000 ';
        exit;
    }
}
echo "Check AffUrl To Inactive  ContentFeed :($checkAffUrl)\r\n";
echo date("Y-m-d H:i:s")."REDIS Update ContentFeed :($j)\r\n";


//检查一个时间范围内大变为inactive的数据。
$m = 0;
$endTime   = date('Y-m-d H:i:s',time());
$startTime = date('Y-m-d H:i:s',time()-12*60*60);
$sql = "select encodeid,OriginalUrl,status from content_feed_new where lastupdatetime > '{$startTime}' and `status`= 'inactive'";
$inactiveArr= $objProgram->objMysql->getRows($sql);
foreach($inactiveArr as $inactiveValue){

    if(isset($tmp_redis[':CF:'.$inactiveValue['encodeid']])){
        $inactiveRedisKey = ':CF:'.$inactiveValue['encodeid'];
        $redisInfo = $objRedis->get($inactiveRedisKey);
        $tmp_arr = json_decode($redisInfo,true);
        if($tmp_arr['status']=='active'){
            $originalUrl = $inactiveValue['OriginalUrl'];
            $redisInactiveArr = array(
                'programid'=>$tmp_arr['programid'],
                'storeid'=>$tmp_arr['storeid'],
                'affurl'=>$originalUrl ? $originalUrl : $tmp_arr['affurl'],
                'originalUrl' => $originalUrl,
                'status'=>'inactive',
                'affid'=>$tmp_arr['affid'],
                'domain'=>$tmp_arr['domain'],
                'domainid'=>$tmp_arr['domainid'],
                'time'=>date('Y-m-d H:i:s'),
            );
            //print_r($redisInactiveArr);exit;
            $redisInactiveValue = json_encode($redisInactiveArr);
            $objRedis->set($inactiveRedisKey, $redisInactiveValue);
            $m++;
        }
    }


}

echo "REDIS to inactive ContentFeed :(".$m.")\r\n";
/*
$m = 0;
if(count($tmp_redis)){
    foreach($tmp_redis as $k => $iv){
        $encodeid = 0;
        $encodeid =  substr(strrchr($k, ":CF:"), 1);
        if(!$encodeid){
            continue;
        }
        //content inactive;
        //$sql = "SELECT a.id, a.encodeid, a.affurl,a.programid,a.storeid,a.OriginalUrl,b.`AffId` FROM content_feed_new a LEFT JOIN program b ON a.programid = b.id WHERE a.encodeid = $encodeid";
        //$sql = "SELECT a.id, a.encodeid, a.affurl, a.OriginalUrl, a.programid, a.storeid, d.affid, c.domain,c.id AS domainid FROM
        //       content_feed_new a LEFT JOIN `r_domain_program` b ON a.programid = b.pid LEFT JOIN `domain` c ON b.did = c.id LEFT JOIN program_intell d ON a.programid = d.programid
        //       WHERE  a.encodeid = $encodeid";
        //$tmp_arr = $objProgram->objMysql->getFirstRow($sql);
        
        $redisInfo = $objRedis->get($k);
        $tmp_arr = json_decode($redisInfo,true);
        $inActiveInfo = array();
        $originalUrl = '';
        $sql = "SELECT OriginalUrl FROM content_feed_new WHERE encodeid = $encodeid";
        $inActiveInfo = $objProgram->objMysql->getFirstRow($sql);
        if($inActiveInfo){
            print_r($inActiveInfo);
            $originalUrl = $inActiveInfo['OriginalUrl'];
            $redisInactiveArr = array(
                'programid'=>$tmp_arr['programid'],
                'storeid'=>$tmp_arr['storeid'],
                'affurl'=>$originalUrl ? $originalUrl : $tmp_arr['affurl'],
                'originalUrl' => $originalUrl,
                'status'=>'inactive',
                'affid'=>$tmp_arr['affid'],
                'domain'=>$tmp_arr['domain'],
                'domainid'=>$tmp_arr['domainid'],
                'time'=>date('Y-m-d H:i:s'),
            );
            $redisInactiveValue = json_encode($redisInactiveArr);
            $objRedis->set($k, $redisInactiveValue);
            $m++;
        }
        
    }
}
echo "REDIS to inactive ContentFeed :(".$m.")\r\n";
*/
echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";
exit;
?>
