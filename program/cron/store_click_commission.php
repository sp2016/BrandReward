<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
include_once(INCLUDE_ROOT . "func/func.php");

echo "<< Start @ " . date("Y-m-d H:i:s") . " >>\r\n";

$objProgram = new Program();
$sql = "UPDATE statis_domain AS a,r_store_domain AS b SET a.`storeId` = b.`StoreId` WHERE  a.`storeId` = 0  AND a.`domainId` = b.`DomainId`";
$objProgram->objMysql->query($sql);

$sql = "UPDATE statis_domain_br AS a,r_store_domain AS b SET a.`storeId` = b.`StoreId` WHERE  a.`storeId` = 0  AND a.`domainId` = b.`DomainId`";
$objProgram->objMysql->query($sql);

$sql = "SELECT storeId,SUM(clicks) AS clicks,SUM(clicks_robot) AS clicks_robot,SUM(clicks_robot_p) AS clicks_robot_p,SUM(sales) AS sales,SUM(revenues) AS commission FROM statis_domain_br WHERE StoreId > 0 GROUP BY StoreId";
$res = $objProgram->objMysql->getRows($sql);

$sql = "SELECT `ApiKey` FROM publisher_account WHERE PublisherId <= 10 OR PublisherId IN (90692,54,432)";
$rows = $objProgram->objMysql->getRows($sql);
$site_arr = array();
foreach($rows as $k=>$v){
    $site_arr[] = $v['ApiKey'];
}

$sql = "SELECT storeId,SUM(clicks) AS clicks,SUM(clicks_robot) AS clicks_robot,SUM(clicks_robot_p) AS clicks_robot_p,SUM(sales) AS sales,SUM(revenues) AS commission FROM statis_domain_br WHERE StoreId > 0 AND site NOT IN ('".join("','",$site_arr)."') GROUP BY StoreId";
$res2 = $objProgram->objMysql->getRows($sql);
$res_pub = array();
foreach($res2 as $k=>$v){
    $res_pub[$v['storeId']] = $v;
}


if(!empty($res)){

    $ids = array();

    foreach($res as $k){
        $storeid = $k['storeId'];
        $clicks = $k['clicks'];
        $sales = $k['sales'];
        $commission = $k['commission'];
        $clicks_robot = $k['clicks_robot'];
        $clicks_robot_p = $k['clicks_robot_p'];
        if(isset($res_pub[$storeid])){
            $pclicks = $k['clicks'];
            $sales_publisher = $k['sales'];
            $commission_publisher = $k['commission'];
            $pclicks_robot = $k['clicks_robot'];
            $pclicks_robot_p = $k['clicks_robot_p'];
        }else{
            $pclicks = 0;
            $sales_publisher = 0.0000;
            $commission_publisher = 0.0000;
            $pclicks_robot = 0;
            $pclicks_robot_p = 0;    
        }
     
        $sql = "update store set clicks=".$clicks.",clicks_robot = ".$clicks_robot.",clicks_robot_p = ".$clicks_robot_p.",PClicks = ".$pclicks.",PClicks_robot = ".$pclicks_robot.",PClicks_robot_p = ".$pclicks_robot_p.",commission='$commission',Commission_publisher = ".$commission_publisher.",sales='$sales',Sales_publisher = ".$sales_publisher." where `ID` = ".$storeid;
        $objProgram->objMysql->query($sql);

        $ids[] = $storeid;
    }

    if(!empty($ids)){
        $sql = "UPDATE store SET clicks = 0,clicks_robot = 0,clicks_robot_p = 0,PClicks = 0,PClicks_robot = 0,PClicks_robot_p = 0,Commission_publisher = 0,commission = 0.0000,Sales_publisher = 0,sales = 0.0000 WHERE ID NOT IN (".join(',',$ids).")";
        $objProgram->objMysql->query($sql);
    }
}
echo "<< End @ " . date("Y-m-d H:i:s") . " >>\r\n";
exit;


?>
