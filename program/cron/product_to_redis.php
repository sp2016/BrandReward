<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");

$objProgram = New ProgramDb();
echo "<< Start @ ".date("Y-m-d H:i:s")." >>\r\n";
$nowDay = date('Y-m-d H:i:s',time());


$objRedis = new Redis();
$objRedis->pconnect(REDIS_HOST, REDIS_PORT);
$tmp_redis = array();
$tmp_redis = $objRedis->keys(":CF:*");
$tmp_redis = array_flip($tmp_redis);

echo "product feed redis.>>\r\n";
//product feed redis.
$i = $j = 0;
while(1){
    
    $sql = "SELECT id,encodeid,productUrl,productDestUrl,programid,storeid FROM product_feed WHERE encodeid > 0 and status = 'active' limit " . $i * 1000 . ", 1000";
    
    $tmp_product_arr = $objProgram->objMysql->getRows($sql);
    if(!count($tmp_product_arr)) break;
    foreach($tmp_product_arr as $pv){
        //domain
        if(isset($tmp_redis[':CF:'.$pv['encodeid']])){
            $pvredisInfo = $objRedis->get(':CF:'.$pv['encodeid']);
            $tmp_arr_pv = json_decode($pvredisInfo,true);
            $pv_affid = $tmp_arr_pv['affid'];
            $pv_domain = $tmp_arr_pv['domain'];
            $pv_domainid = $tmp_arr_pv['domainid'];
        }else{
            $sql = "SELECT c.domain,c.id AS domainid FROM product_feed a LEFT JOIN `r_domain_program` b  ON a.programid = b.pid  LEFT JOIN `domain` c ON b.did = c.id WHERE  a.id = {$pv['id']}";
            $tmp_product_domain = $objProgram->objMysql->getRows($sql);
            foreach ($tmp_product_domain as $pdv){
                $pv_domain = $pdv['domain'];
                $pv_domainid = $pdv['domainid'];
            }
            $sql = "select affid from program_intell where programid = {$pv['programid']} limit 1"; //affid
            $programInfo = $objProgram->objMysql->getFirstRow($sql);
            $pv_affid = $programInfo['affid'];
        }
            
        if(strlen($pv_domain) && strpos($pv_domain, ',') !== false){
            $pv_domain = substr($pv_domain, 0, strpos($pv_domain, ','));
        }
        $redisArr = array(
            'programid'=>$pv['programid'],
            'storeid'=>$pv['storeid'],
            'affurl'=> $pv['productUrl'] ? $pv['productUrl'] : $pv['productDestUrl'],
            'originalUrl' =>$pv['productDestUrl'],
            'affid'=>$pv_affid,
            'domain'=>$pv_domain,
            'domainid'=>$pv_domainid,
            'status'=>'active',
            'time'=>date('Y-m-d H:i:s')
        );
        $redisValue = json_encode($redisArr);
        $objRedis->set(":CF:".$pv['encodeid'], $redisValue);
        unset($tmp_redis[":CF:".$pv['encodeid']]);
        $j++;
    }

    $i++;
    if($i > 10000){
        echo 'warning: ContentFeed 10000 ';
        exit;
    }
}
echo date('Y-m-d H:i:s',time())."REDIS Update ProductFeed :($j)\r\n";



$m = 0;
if(count($tmp_redis)){
    foreach($tmp_redis as $k => $iv){
        $encodeid = 0;
        $encodeid =  substr(strrchr($k, ":CF:"), 1);
        if(!$encodeid){
            continue;
        }
        
        
        $redisInfo = $objRedis->get($k);
        $tmp_arr = json_decode($redisInfo,true);
        
        $inActiveInfo = array();
        $originalUrl = '';
        $sql = "SELECT ProductDestUrl FROM product_feed WHERE encodeid = $encodeid";
        $inActiveInfo = $objProgram->objMysql->getFirstRow($sql);
        if($inActiveInfo){
            $originalUrl = $inActiveInfo['ProductDestUrl'];
        }
        
        if($inActiveInfo){
            
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
            $m ++;
        }
        
    }
}
echo "REDIS to inactive ProductFeed :(".$m.")\r\n";

echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";
exit;
?>