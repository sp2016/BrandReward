<?php
global $_cf,$_req,$_db;
//$_objPendingMysql = new Mysql('pendinglinks', 'localhost', 'bdg_go', 'shY12Nbd8J');
//$_objPendingMysql = new Mysql('pendinglinks', 'localhost', 'root', '');
$_objPendingMysql = new Mysql(PENDING_DB_NAME, PENDING_DB_HOST, PENDING_DB_USER, PENDING_DB_PASS);



echo "<< Start @ ".date("Y-m-d H:i:s")." >>\r\n";

$sql = "select ID from wf_aff where IsActive = 'YES'";
$affArr = $_db->getRows($sql);

$tables = array();
foreach ($affArr as $v){
    $exist = $_objPendingMysql->isTableExisting('affiliate_links_'.$v['ID']);
    if($exist)
        $tables[$v['ID']] = 'affiliate_links_'.$v['ID'];
}


echo "------------dump affiliate links date to file \r\n";
$nowDay = date('Y-m-d H:i:s',time());
$endTime   = date('Y-m-d H:i:s',time());
$startTime = date('Y-m-d H:i:s',time()-24*60*60);

 
$column_keys = array('AffMerchantId','AffLinkId','LinkCode','LinkName','LinkDesc','LinkStartDate','LinkEndDate','LinkPromoType','LinkHtmlCode','LinkAffUrl','LinkImageUrl','LastUpdateTime',
    'LinkAddTime','LastChangeTime','IsDeepLink','SupportDeepUrlTpl','IsActive'
);
//导出所有links的 新增跟更新的links

$dumpSql = "SELECT * FROM affiliate_links_all_simple WHERE ( (LinkAddTime >= '$startTime' and LinkAddTime < '$endTime') OR (LastChangeTime >= '$startTime' and LastChangeTime < '$endTime') ) ";
$simpleArr = $_db->getRows($dumpSql);
$content =  "Affid\t".join("\t",$column_keys)."\n";

$sql_names_set = 'SET NAMES latin1';
$_objPendingMysql->query($sql_names_set);
foreach ($simpleArr as $sv){
    
    $table = isset($tables[$sv['affid']]) ? $tables[$sv['affid']] : '';
    if(!empty($table)){
        $sql = "select $sv[affid],".implode(',',$column_keys)." from $table where AffMerchantId = '$sv[PidInaff]' and  AffLinkId = '".addslashes($sv['AffLinkId'])."' ";
         
        $row = $_objPendingMysql->getRows($sql);
        if(!empty($row)){
            foreach($row as $k1=>$v1){
                $content .= join("\t",$v1)."\n";
            }
        }
        
    }
}

$f = DATA_ROOT."links/dumpAffiliateLinksAllTemp.dat";
if(file_exists($f))
    unlink($f);
file_put_contents($f, $content);

$date = date('Ymd',strtotime($nowDay));
$cmd = 'mv '.DATA_ROOT.'links/dumpAffiliateLinksAllTemp.dat '.DATA_ROOT.'links/dumpAffiliateLinksAll'.$date.'.dat';
system($cmd,$retval);

if($retval > 0){

    echo "upmp file error!!!\r\n";

}


echo "------------dump Manual feeds to file \r\n";

$column_keys = array('AffId,AffMerchantId','LinkCode','LinkName','LinkDesc','LinkStartDate','LinkEndDate','LinkPromoType','LinkAffUrl','OriginalUrl','LastUpdateTime',
    'LinkAddTime','IsActive'
);
$sql = "SELECT * FROM content_feed_new  WHERE source = 'manual' AND programid > 0";
$manualFeed = $_db->getRows($sql);
$manualContent = join("\t",$column_keys)."\n";
$tempManual = array();
foreach ($manualFeed as $manual){
    $key = md5($manual['ProgramId'].$manual['CouponCode'].$manual['Title']);
    
    $sql = "select AffId,IdInAff from program where id = {$manual['ProgramId']} limit 1";
    $programInfo = $_db->getFirstRow($sql);
    $tempManual[$key]['AffId'] = $programInfo['AffId'];
    $tempManual[$key]['AffMerchantId'] = $programInfo['IdInAff'];
    $tempManual[$key]['LinkCode'] = $manual['CouponCode'];
    $tempManual[$key]['LinkName'] = $manual['Title'];
    $tempManual[$key]['LinkDesc'] = str_replace(array("\r\n", "\r", "\n"), "", $manual['Desc']);
    $tempManual[$key]['LinkStartDate'] = $manual['StartDate'];
    $tempManual[$key]['LinkEndDate'] = $manual['EndDate'];
    $tempManual[$key]['LinkPromoType'] = $manual['Type'];
    $tempManual[$key]['LinkAffUrl'] = $manual['AffUrl'];
    $tempManual[$key]['OriginalUrl'] = $manual['OriginalUrl'];
    $tempManual[$key]['LastUpdateTime'] = $manual['LastUpdateTime'];
    $tempManual[$key]['LinkAddTime'] = $manual['AddTime'];
    $tempManual[$key]['IsActive'] = $manual['Status'];
}

foreach ($tempManual as $tempManualV){
    $manualContent .= join("\t",$tempManualV)."\n";
}



//echo $manualContent;
$f = DATA_ROOT."links/dumpManualFeedsTemp.dat";
if(file_exists($f))
    unlink($f);
file_put_contents($f, $manualContent);

$date = date('Ymd',strtotime($nowDay));
$cmd = 'mv '.DATA_ROOT.'links/dumpManualFeedsTemp.dat '.DATA_ROOT.'links/dumpManualFeeds'.$date.'.dat';
system($cmd,$retval);

if($retval > 0){

    echo "upmp Manual file error!!!\r\n";

}


echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";
exit;


?>
