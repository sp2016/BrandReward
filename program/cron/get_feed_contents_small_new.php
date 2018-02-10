<?php
//从affiliate_links_all_simple表里取出isactive 跟 ispromotion的数据  只读取新增跟变化的数据
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
include_once(dirname(dirname(__FILE__)) . "/func/func.php");
$length = 1000; //每次取1000条

echo "<< Start @ ".date("Y-m-d H:i:s")." >>\r\n";

$objProgram = New ProgramDb();
$sql_names_set = 'SET NAMES latin1';
$objProgram->objMysql->query($sql_names_set);

$sql_names_set = 'SET NAMES latin1';
$objProgram->objPendingMysql->query($sql_names_set);


if(!checkProcess()){
    echo 'process still runing.\r\n';
    echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";
    exit;
}



echo "Network promo script Start >>\r\n";

$nowDay = date('Y-m-d H:i:s',time());
$endTime   = date('Y-m-d H:i:s',time());
$startTime = date('Y-m-d H:i:s',time()-30*60);

$i = 0;
$j = 0;
$new_content = 0;
$toInactive = 0;
$toActive = 0;
$column_keys = array('SimpleId','ProgramId','StoreId','CouponCode','Title','`Desc`','StartDate','EndDate','AffUrl','`AddTime`','LastChangeTime',
            '`language`','`Status`','`Type`'
        );


//select all active feed.
$sql = "select id,SimpleId,title,CouponCode from content_feed_new  where `Status` = 'Active' and source = 'site'";
$allActiveFeedTemp = $objProgram->objMysql->getRows($sql);
$allActiveFeed = array();
foreach ($allActiveFeedTemp as $allActiveFeedValue){
    $key = md5($allActiveFeedValue['title'].$allActiveFeedValue['CouponCode']);
    $allActiveFeed[$key]['SimpleId'][$allActiveFeedValue['SimpleId']] =  $allActiveFeedValue['id'];
}


do{
    //echo $i.PHP_EOL;
    $offset = $length*$i- 1 > 0 ? $length*$i- 1 : 0;
    $sql = "select * from affiliate_links_all_simple where   ScriptTime >= '$startTime' AND ScriptTime < '$endTime'
            limit $offset, $length";
    //echo $sql.PHP_EOL;
    $data = $objProgram->objMysql->getRows($sql);
    if(count($data)<=0) break;
    //echo $sql.PHP_EOL;
    
    $programidArr = array();
    foreach ($data as $simpleInfo){
        $programidArr[$simpleInfo['ProgramId']] = $simpleInfo['ProgramId'];
    }
    $storeInfo = array();
    $storeSql = "select a.pid,b.StoreId from r_domain_program a left join r_store_domain b on a.did = b.domainid where a.pid IN (".implode(",",$programidArr).")";
    $storeInfoArr = $objProgram->objMysql->getRows($storeSql);
    foreach ($storeInfoArr as $storeInfoValue){
        $storeInfo[$storeInfoValue['pid']] = $storeInfoValue['StoreId'];
    }
   
     
    $i++;
    //echo '11111:'.date('Y-m-d H:i:s',time()).PHP_EOL;
    foreach ($data as $v){
        
        $linkInfo = array();
        $table = 'affiliate_links_'.$v['affid'];        
        $linkSql = "select * from $table where AffMerchantId = '{$v['PidInaff']}' and  AffLinkId = '".addslashes($v['AffLinkId'])."'";
        
        $linkInfo = $objProgram->objPendingMysql->getFirstRow($linkSql);
        
        if(empty($linkInfo)){
            continue;
        }
        
        //对取到的link进行过滤
        //if($linkInfo['LinkEndDate'] != '0000-00-00 00:00:00' && $linkInfo['LinkEndDate'] < $nowDay) continue;
        if(empty($linkInfo['LinkAffUrl'])) continue;
        if(stripos($linkInfo['LinkName'],'.png')) continue;
        if(stripos($linkInfo['LinkName'],'.jpg')) continue;
        if(stripos($linkInfo['LinkName'],'.gif')) continue;
        //Biltmore Estates 160x600 500 x 500
        if(preg_match('/\d(\*|x)\d/i',$linkInfo['LinkName'],$matches))
        {
            continue;
        }
        $linkInfo['LinkName'] = trim(preg_replace('/\d+\s*x\s*\d+/i','',$linkInfo['LinkName']));
        $linkInfo['LinkName'] = str_replace(array('(',')','-'),'',$linkInfo['LinkName']);
        
        if(!$linkInfo['LinkAffUrl']){
            continue;
        }
        
        if($v['affid'] == 58 || $v['affid'] == 28){
            if(preg_match('/^\/\/.+/',$linkInfo['LinkAffUrl'],$matches)){
                $linkInfo['LinkAffUrl'] = 'http:'.$linkInfo['LinkAffUrl'];
                //echo $linkInfo['AffLinkId'].'===>'.$linkInfo['LinkAffUrl'].PHP_EOL;
            }
        }
        
        if(!preg_match('/^http/',$linkInfo['LinkAffUrl'],$matches) ){
            continue;
        }
        
        if(!isset($storeInfo[$v['ProgramId']])){
            continue;
        }
        
        $programSql = "select a.ID,b.ShippingCountry from program a inner join program_intell b on a.id = b.programid where a.StatusInAff = 'Active' and a.Partnership = 'Active' and b.isactive = 'active' and a.id = {$v['ProgramId']}";
        $programInfo = $objProgram->objMysql->getFirstRow($programSql);
        if(empty($programInfo)){
            continue;
        }
        
        
        //查询是否有这条记录
        $selContentFeedSql  = "select id,status,OriginalUrl,IsParaOptimized from content_feed_new where `SimpleId` = {$v['ID']} and source = 'site'";
        $contentFeedInfo = $objProgram->objMysql->getFirstRow($selContentFeedSql);
        
        //时间过滤开始
        $tmpEndDate = preg_commonRule($linkInfo['LinkName'].','.$linkInfo['LinkDesc'],$linkInfo['LinkStartDate']);
        if(!$tmpEndDate)
            $tmpEndDate = preg_strtotime($linkInfo['LinkName'].','.$linkInfo['LinkDesc'],$linkInfo['LinkStartDate']);
         
         
        if($tmpEndDate && strtotime($linkInfo['LinkEndDate']) < strtotime($nowDay)){
            $linkInfo['LinkStartDate'] = $tmpEndDate['startDate']; //重置匹配到的开始时间
            if(strtotime($tmpEndDate['endDate']) > strtotime($nowDay)){ //如果能匹配到，并且过期时间大于现在的时候
                $linkInfo['LinkEndDate'] = $linkInfo['LinkEndDate'] != '0000-00-00 00:00:00' ? $linkInfo['LinkEndDate']:$tmpEndDate['endDate'];
            }
            else{
                //有这条记录就UPDATE Status = InActive，没有就continue
                if($contentFeedInfo){
                    $updateContentFeedSql = "update content_feed_new set `Status` = 'InActive',EndDate = '".$tmpEndDate['endDate']."',LastUpdateTime = '$nowDay' where `SimpleId` = {$v['ID']} and source = 'site'";
                    $objProgram->objMysql->query($updateContentFeedSql);
                    $toInactive ++;
                    continue;
                }else {
                    continue;
                }
            }
        }
        
        //对StartDate为0的定一个时间
        if($linkInfo['LinkStartDate'] == '0000-00-00 00:00:00'){
            $linkInfo['LinkStartDate'] = $linkInfo['LinkAddTime'];
            $linkInfo['LinkEndDate'] = date('Y-m-d H:i:s', strtotime($linkInfo['LinkAddTime'])+6*30*86400) ;
        }
        
        //对EndDate为0的定一个时间
        if($linkInfo['LinkEndDate'] == '0000-00-00 00:00:00' && $linkInfo['LinkStartDate'] != '0000-00-00 00:00:00'){
            $fixDateMonth = date('m',strtotime($linkInfo['LinkStartDate']));
            $fixDateYear = date('Y',strtotime($linkInfo['LinkStartDate']));
            if($fixDateMonth<5){
                $linkInfo['LinkEndDate'] = $fixDateYear.'-12-30 00:00:00' ;//本年年底
            }else{
                $linkInfo['LinkEndDate'] = date('Y-m-d H:i:s',strtotime('+1 year +1 month',strtotime($linkInfo['LinkStartDate']))) ;//加一年 加一月
            }
            //echo $v['ID'].PHP_EOL;
        }
        
        //如果EndDate过期，并且status = InActive 跳过
        if($contentFeedInfo && $contentFeedInfo['status'] == 'InActive' && $linkInfo['LinkEndDate'] < $nowDay){
            continue;
        }
        //时间过滤结束
        
         
        
        //过滤重复
        $keyWhere =  analyze_promo_keywords($v['KeyWords']);
        $isFilter = true;
        if($linkInfo['LinkCode']){
            if(strtolower($linkInfo['LinkCode']) == 'na' || strtolower($linkInfo['LinkCode']) == 'n/a'){
                $isFilter = false;
            }
            if($isFilter && preg_match('/(no|none|Not|ohne)\s+/i',$linkInfo['LinkCode'],$matches)){
                $isFilter = false;
            }
            
            if($isFilter){
                $sql = "select id from content_feed_new  where  CouponCode='".addslashes($linkInfo['LinkCode'])."' and ProgramId = {$v['ProgramId']} and `Status` = 'Active'  and SimpleId != {$v['ID']}";
                $reqInfo = $objProgram->objMysql->getRows($sql);
                if(!empty($reqInfo)){
                    
                    //把所有的状态变为inActive
                    $ids = array();
                    foreach ($reqInfo as $reqInfoV1){
                        $ids[] = $reqInfoV1['id'];
                    }
                    $updateRep = "update content_feed_new set LastUpdateTime = '$nowDay',`Status` = 'InActive'  where id IN (".implode(',', $ids).") ";
                    $objProgram->objMysql->query($updateRep);
                    
                    $toInactive  = $toInactive + count($ids);
                }
            }
        }
        
        
        //过滤掉title coupon  相同掉links
        $ids = array();
        $md5Key = md5($linkInfo['LinkName'].$linkInfo['LinkCode']);
        if(isset($allActiveFeed[$md5Key])){
            $ids = array_diff_key($allActiveFeed[$md5Key]['SimpleId'],array($v['ID']=>1));
            if($ids){
                //print_r($ids);exit;
                $updateRep = "update content_feed_new set LastUpdateTime = '$nowDay',`Status` = 'InActive',`EndDate` = '".$linkInfo['LinkEndDate']."' where id IN (".implode(',', $ids).") ";
                $objProgram->objMysql->query($updateRep);
                $toInactive  = $toInactive + count($ids);
            }
        }
         
        /*$sql = "select id,title from content_feed_new  where Title='".addslashes($linkInfo['LinkName'])."' and CouponCode='".addslashes($linkInfo['LinkCode'])."' and ProgramId = {$v['ProgramId']} and `Status` = 'Active'   and SimpleId != {$v['ID']}";
        $reqInfo = $objProgram->objMysql->getRows($sql);
        if(!empty($reqInfo)){
        
            $ids = array();
            foreach ($reqInfo as $reqInfoV2){
                $ids[] = $reqInfoV2['id'];
            }
            $updateRep = "update content_feed_new set LastUpdateTime = '$nowDay',`Status` = 'InActive',`EndDate` = '".$linkInfo['LinkEndDate']."' where id IN (".implode(',', $ids).") ";
            $objProgram->objMysql->query($updateRep);
            $toInactive  = $toInactive + count($ids);
        }*/
        
        
        
        
        
        //确定type
        if(!empty($linkInfo['LinkCode'])){
            if(preg_match('/(no|none|Not)\s+/i',$linkInfo['LinkCode'],$matches) || $linkInfo['LinkCode']=='none'){
                $type = 'Promotion';
                $linkInfo['LinkCode'] = '';
            }else{
                $type = 'Coupon';
            }
        }else {
            $type = 'Promotion';
        }
        $type = is_freeshipping($linkInfo['LinkName'].$linkInfo['LinkDesc'],$type);
        
        
        $allPromo =  analyze_promo_keywords($v['KeyWords'],'all');
        
        $Status = ($v['IsActive'] == 'YES' && $v['IsPromotion'] == 'YES') ? 'Active' : 'InActive';
        
        if($contentFeedInfo && $contentFeedInfo['status'] == 'Active' && $Status == 'InActive'){
            $toInactive += 1;
        }
        
        if($Status == 'Active'){
            $toActive++;
        }
        
        if($contentFeedInfo){
            $OriginalUrl = $contentFeedInfo['OriginalUrl'];
            $IsParaOptimized = $contentFeedInfo['IsParaOptimized'];
            
        }else{
            $OriginalUrl = '';
            $sql = "select b.Domain from `r_domain_program` a left join domain b on a.did = b.id where a.pid = {$v['ProgramId']}";
            $domainInfo = $objProgram->objMysql->getFirstRow($sql);
            if($linkInfo['LinkOriginalUrl']){
                $OriginalUrl = removeAffParas(trim($linkInfo['LinkOriginalUrl']),trim($domainInfo['Domain']));
            }
            $IsParaOptimized = $OriginalUrl ? 'ORIGIN':'NO';
        }
        
        
        $tmp_data = array(
            'SimpleId' => $v['ID'],
            'ProgramId' => $v['ProgramId'],
            'StoreId' => $storeInfo[$v['ProgramId']], 
            'CouponCode' => $linkInfo['LinkCode'],
            'Title' => $linkInfo['LinkName'],
            '`Desc`' => strip_tags($linkInfo['LinkDesc']),
            'StartDate' => $linkInfo['LinkStartDate'],
            'EndDate' => $linkInfo['LinkEndDate'],
            'AffUrl' => $linkInfo['LinkAffUrl'],
            'OriginalUrl' => $OriginalUrl,
            'LastUpdateTime' => $nowDay,
            'AddTime' => $nowDay,
            'LastChangeTime'=>$linkInfo['LastChangeTime'],
            '`Status`' => $Status, //Active InActive
            '`Type`' => $type, //$v['LinkCode'] ?  'Coupon' : 'Promotion'//Coupon Promotion
            '`source`'=> 'site',
            'IsParaOptimized' => $IsParaOptimized,
        );
        $tmp_data += $allPromo;
        
        
        $column_keys = array('SimpleId','ProgramId','StoreId','CouponCode','Title','`Desc`','StartDate','EndDate','AffUrl','OriginalUrl','`AddTime`','LastChangeTime',
            '`Status`','`Type`','`source`','IsParaOptimized'
        );
        
        $allPromoKey = array_keys($allPromo);
        $column_keys = array_merge($column_keys,$allPromoKey);
        
        if(!$contentFeedInfo){ //new content
            
            if ($v['affid'] == 360 || $v['affid'] == 63 || $v['affid'] == 65)
                $language = 'de';
            elseif($v['affid'] == 2026){
                $language = 'it';
            }
            elseif($v['affid'] == 2027){
                $language = 'nl';
            }
            elseif($v['affid'] == 1){
                $language = $v['language'];
            }else
                $language = analyze_language($linkInfo['LinkName'].$linkInfo['LinkDesc']);
            
            $column_keys[] = 'EncodeId';
            $tmp_data['EncodeId'] = intval(getEncodeId());
            $column_keys[] = '`language`';
            $tmp_data['`language`'] = $language;
            $new_content++ ;
        }
        
        foreach ($tmp_data as $tk=>$tv){
            if($tk != 'LastUpdateTime')
                $tmp_insert[] = addslashes($tv);
            if($tk != 'AddTime')
                $tmp_update[] = "$tk = '".addslashes($tv)."'";
        }
        $insertSql = "INSERT INTO content_feed_new (".implode(",", $column_keys).") VALUES ('".implode("','", $tmp_insert)."') ON DUPLICATE KEY UPDATE " . implode(",", $tmp_update) . ";";
        //echo $insertSql;exit;
        $objProgram->objMysql->query($insertSql);
        $j++;
        unset($tmp_insert);
        unset($tmp_update);
        
        
    }
    //echo '22222:'.date('Y-m-d H:i:s',time()).PHP_EOL;
    
}while(count($data)>0);


echo "Update total Network promo ($j)\r\n";
echo "Add new Network promo ($new_content)\r\n";
echo "Set Network promo $toActive Active\r\n";
echo "Set Network promo $toInactive Inactive.\r\n";


$sql = "select count(*) from content_feed_new where  status = 'active' and ISNULL(adduser) and source = 'site'";
$cnt = $objProgram->objMysql->getFirstRowColumn($sql);

if($cnt < 30000){
    $to = "merlinxu@brandreward.com";
    AlertEmail::SendAlert('content feed to inactive too much',nl2br("to inactive count:".$cnt), $to);
    exit;
}

echo "<< Content Feed End  @ ".date("Y-m-d H:i:s")." >>\r\n";



//email promo start
echo "Email promo script Start >>\r\n";
$i = 0;
$j = 0;
$new_content = 0;
$nowDay = date('Y-m-d H:i:s',time());

do{
    $offset = $length*$i- 1 > 0 ? $length*$i- 1 : 0;
    $sql = "select * from affiliate_email_promo WHERE programid > 0 AND AddTime >= '$startTime' AND AddTime < '$endTime' limit $offset, $length";
    $sql_names_set = 'SET NAMES latin1';
    $objProgram->objPendingMysql->query($sql_names_set);
    $data = $objProgram->objPendingMysql->getRows($sql);
    $i++;
    foreach ($data as $value){

        //对取到的email进行过滤
        if($value['ExpireTime'] != '0000-00-00 00:00:00' && $value['ExpireTime'] < $nowDay) continue;
        if(empty($value['Merchant_Originanl_Url'])) continue;
         
        
        $programid = $value['programId'];
        //查询storeId
        $storeInfo = array();
        $storeSql = "select StoreId from `r_store_program` where ProgramId = {$programid}";
        $storeInfo = $objProgram->objMysql->getFirstRow($storeSql);
        if(!empty($storeInfo)){
            $storeId = $storeInfo['StoreId'];
        }else
            continue;

        //检查是否是有效的storeid
        $isValidStore = array();
        $sql = "SELECT a.storeid FROM `r_store_domain` AS a LEFT JOIN r_domain_program AS b ON a.domainid = b.did LEFT JOIN program_intell AS c ON b.pid =  c.ProgramId WHERE a.storeid = $storeId AND c.isactive = 'Active'";
        $isValidStore = $objProgram->objMysql->getFirstRow($sql);
        if(empty($isValidStore))
            continue;
         
         

        //查询表中是否有相同的promo
        $feedInfo = array();
        $sql = "select * from content_feed_new where StoreId = $storeId and programid = {$value['programId']} and CouponCode = '{$value['Code']}' and Title = '".addslashes($value['Title'])."' and source = 'site'";
        $feedInfo = $objProgram->objMysql->getFirstRow($sql);
        if(!empty($feedInfo)){
            continue;
        }

        $source = 'email';
        if($value['SourceType'] == 'duplicate'){
            $source = 'email_duplicate';
        }

        //查询是否有这条记录
        $selContentFeedSql  = "select id from content_feed_new where `SimpleId` = {$value['ID']} and source = '$source'";
        $contentFeedInfo = $objProgram->objMysql->getFirstRow($selContentFeedSql);


        //确定type
        if(!empty($value['Code'])){
            if(preg_match('/(no|none|Not)\s+/i',$value['Code'],$matches) || $value['Code']=='none'){
                $type = 'Promotion';
            }else{
                $type = 'Coupon';
            }
        }else {
            $type = 'Promotion';
        }

        $affUrl =  $value['AffUrl'] ? $value['AffUrl'] : $value['DestUrl'];
        if(!$affUrl) continue;

        
        $tmp_data = array(
            'SimpleId' => $value['ID'],
            'ProgramId' => $programid,
            'StoreId' => $storeId,
            'CouponCode' => $value['Code'],
            'Title' => addslashes($value['Title']),
            '`Desc`' => strip_tags(addslashes($value['Description'])),
            'StartDate' => $value['StartTime'],
            'EndDate' => $value['ExpireTime'],
            'AffUrl' => $affUrl,
            'OriginalUrl' => $value['DestUrl'],
            'LastUpdateTime' => $nowDay,
            'AddTime' => $nowDay,
            'LastChangeTime'=>'0000-00-00 00:00:00',
            '`language`' => analyze_language($value['Title'].$value['Description']),
            '`Status`' => 'Active', //Active InActive
            '`Type`' => $type, //$v['LinkCode'] ?  'Coupon' : 'Promotion'//Coupon Promotion
            '`source`'=> $source,
        );

        $column_keys = array('SimpleId','ProgramId','StoreId','CouponCode','Title','`Desc`','StartDate','EndDate','AffUrl','OriginalUrl','`AddTime`','LastChangeTime',
            '`language`','`Status`','`Type`','`source`'
        );
        if(!$contentFeedInfo){ //new content
            $column_keys[] = 'EncodeId';
            $tmp_data['EncodeId'] = intval(getEncodeId());
            $new_content++ ;
        }

        foreach ($tmp_data as $tk=>$tv){
            if($tk != 'LastUpdateTime')
                $tmp_insert[] = addslashes($tv);
            if($tk != 'AddTime')
                $tmp_update[] = "$tk = '".addslashes($tv)."'";
        }
        $insertSql = "INSERT INTO content_feed_new (".implode(",", $column_keys).") VALUES ('".implode("','", $tmp_insert)."') ON DUPLICATE KEY UPDATE " . implode(",", $tmp_update) . ";";
        //echo $insertSql;exit;
        $objProgram->objMysql->query($insertSql);
        $j++;
        unset($tmp_insert);
        unset($tmp_update);

    }
    echo "Email promo $j\r\n";

}while(count($data)>0);


//check expire
$sql = "select count(*) from content_feed_new where status = 'active' and enddate <= '".date('Y-m-d H:i')."' and enddate != '0000-00-00 00:00:00'";
$cnt = $objProgram->objMysql->getFirstRowColumn($sql);
$sql = "update content_feed_new set status = 'InActive',LastUpdateTime = '$nowDay' where status = 'active' and enddate <= '".date('Y-m-d H:i')."' and enddate != '0000-00-00 00:00:00'";
$objProgram->objMysql->query($sql);
echo "Set All Promo $cnt Expire.\r\n";


$i = 0;
$key = substr(strtotime(" - " . date("s") . "days"), -5);
while(1){
	$i++;
	$sql = "select id from content_feed_new where encodeid = 0 limit 100";
	$tmp_arr = $objProgram->objMysql->getRows($sql);
	if(!count($tmp_arr)) break;
	
	foreach($tmp_arr as $v){
		$encodeid = intval(getEncodeId());
		if($encodeid){
			$sql = "update content_feed_new set encodeid = $encodeid where id = {$v['id']}";
			$objProgram->objMysql->query($sql);
		}
	}
	
	if($i > 10000){
		echo 'warning: 10000 ';
		exit;
	}
}


echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";
exit;

//过滤一些人为整理的过期规律
function preg_commonRule($str,$LinkStartDate){
    
    $tmpDate = array();
    //1.Ends 04/12/2016.
    if(preg_match('@End.*?(\d+/\d+/\d+)@im',$str,$matches)){
        $tmpEndDate = preg_replace('@(\d+)/(\d+)/(\d+)@','$3-$1-$2 00:00:00',$matches[1]);
        return $tmpDate = array('startDate'=>$LinkStartDate,'endDate'=>$tmpEndDate);
    }
    
    //2.Ends: 2/15
    if(preg_match('@Ends.*?(\d+/\d+)@im',$str,$matches)){
        if($LinkStartDate == '0000-00-00 00:00:00') //如果是这样，取当前年份 
            $tmpEndDate = preg_replace('@(19|20)(\d{2})-.*?\|\|(\d{1,2})/(\d{2})@iU','$1$2-$3-$4 00:00:00',date('Y-00-00 00:00:00').'||'.$matches[1]);
        else
            $tmpEndDate = preg_replace('@(19|20)(\d{2})-.*?\|\|(\d{1,2})/(\d{2})@iU','$1$2-$3-$4 00:00:00',$LinkStartDate.'||'.$matches[1]);
        return $tmpDate = array('startDate'=>$LinkStartDate,'endDate'=>$tmpEndDate);
    }
    
    
    //3.valid:7/1-7/5.
    if(preg_match('@Valid.*?(\d{1,2}/\d{1,2})-(\d{1,2}/\d{1,2})@im',$str,$matches)){
        if($LinkStartDate == '0000-00-00 00:00:00'){ //如果是这样，取当前年份 ,从新定义$LinkStartDate
            $tmpEndDate = preg_replace('@(19|20)(\d{2})-.*?\|\|(\d{1,2})/(\d{1,2})@i','$1$2-$3-$4 00:00:00',date('Y-00-00 00:00:00').'||'.$matches[2]);
			$LinkStartDate =  date('Y-m-d H:i:s',strtotime(date('Y').'/'.$matches[1])) ;
		}
        else
            $tmpEndDate = preg_replace('@(19|20)(\d{2})-.*?\|\|(\d{1,2})/(\d{1,2})@i','$1$2-$3-$4 00:00:00',$LinkStartDate.'||'.$matches[2]);

        return $tmpDate = array('startDate'=>$LinkStartDate,'endDate'=>$tmpEndDate);
    }
    //3.valid:7/5 (9.30).
    if(preg_match('@Valid.*?(\d{1,2}(/|.)\d{1,2})@im',$str,$matches)){
        if($LinkStartDate == '0000-00-00 00:00:00') //如果是这样，取当前年份
            $tmpEndDate = preg_replace('@(19|20)(\d{2})-.*?\|\|(\d{1,2})(/|.)(\d{1,2})@i','$1$2-$3-$5 00:00:00',date('Y-00-00 00:00:00').'||'.$matches[1]);
        else 
            $tmpEndDate = preg_replace('@(19|20)(\d{2})-.*?\|\|(\d{1,2})(/|.)(\d{1,2})@i','$1$2-$3-$5 00:00:00',$LinkStartDate.'||'.$matches[1]);
        return $tmpDate = array('startDate'=>$LinkStartDate,'endDate'=>$tmpEndDate);
    }
    
    //4.Valid:2/1-2/28/15.
    if(preg_match('@Valid.*?(\d+/\d+(/\d+)?)-(\d+/\d+/\d+)@im',$str,$matches)){
         
        $tmpEndDate = preg_replace('@(\d+)/(\d+)/(\d+)@','$3-$1-$2 00:00:00',$matches[3]);
        $tmpEndDate = date('Y-m-d H:i:s',strtotime($tmpEndDate));
         
        if($LinkStartDate == '0000-00-00 00:00:00')
        {
            $tmpStartDateArr = explode('/',$matches[1]);
            if(count($tmpStartDateArr)==3){ //  2/1/15
                $LinkStartDate = $tmpStartDateArr[2].'-'.$tmpStartDateArr[0].'-'.$tmpStartDateArr[1];
                $LinkStartDate = date('Y-m-d H:i:s',strtotime($LinkStartDate));
            }
            else{
                $LinkStartDate = preg_replace('@(19|20)(\d{2})-.*?\|\|(\d+)/(\d+)@','$1$2-$3-$4 00:00:00',$tmpEndDate.'||'.$matches[1]);
                $LinkStartDate = date('Y-m-d H:i:s',strtotime($LinkStartDate));
            }
        }
        return $tmpDate = array('startDate'=>$LinkStartDate,'endDate'=>$tmpEndDate);
    }
    
    //5. Expires: 2/29/2016
    if(preg_match('@Exp.*?(\d+/\d+/\d+)@im',$str,$matches)){
        $tmpEndDate = preg_replace('@(\d+)/(\d+)/(\d+)@','$3-$1-$2 00:00:00',$matches[1]);
        return $tmpDate = array('startDate'=>$LinkStartDate,'endDate'=>$tmpEndDate);
    }
    
    //6. From 9/16/16 -|to 9/22/16
    if(preg_match('@from.*?(\d+/\d+(/\d+)?)\s+(-|to)?\s+(\d+/\d+(/\d+)?)@im',$str,$matches)){
    
        $mat_arr =  explode('/',$matches[4]);
        if(count($mat_arr) == 3){
            if(strlen($mat_arr[2]) == 2){
                $tmpEndDate = '20'.$mat_arr[2].'-'.$mat_arr[0].'-'.$mat_arr[1].' 00:00:00';
            }
            elseif(strlen($mat_arr[2]) == 4){
                $tmpEndDate = $mat_arr[2].'-'.$mat_arr[0].'-'.$mat_arr[1].' 00:00:00';
            }
        }elseif(count($mat_arr) == 2){
            $tmpEndDate = substr($LinkStartDate,0,4).'-'.$mat_arr[0].'-'.$mat_arr[1].' 00:00:00';
        }
    
        if($LinkStartDate == '0000-00-00 00:00:00')
        {
            $tmpStartDateArr = explode('/',$matches[1]);
            if(count($tmpStartDateArr)==3){ //  2/1/15
                $LinkStartDate = $tmpStartDateArr[2].'-'.$tmpStartDateArr[0].'-'.$tmpStartDateArr[1];
                $LinkStartDate = date('Y-m-d H:i:s',strtotime($LinkStartDate));
            }
            else{
                $LinkStartDate = preg_replace('@(19|20)(\d{2})-.*?\|\|(\d+)/(\d+)@','$1$2-$3-$4 00:00:00',$tmpEndDate.'||'.$matches[1]);
                $LinkStartDate = date('Y-m-d H:i:s',strtotime($LinkStartDate));
            }
        }
         
        if(isset($tmpEndDate)){
            return $tmpDate = array('startDate'=>$LinkStartDate,'endDate'=>$tmpEndDate);
        }
    
    }
    
    //7. only until February 29th 2016
    $month = 0;
    $months = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
    foreach($months as $mk=>$mv){
        if(stripos($str,$mv)){
            $month = $mk;
            break;
        }
    }
    if($month){
        preg_match('/until.*?(\d+).*?(\d+)/i',$str,$matches);
        if($matches){
            if(strlen($matches[2]) == 4)
                $tmpEndDate = $matches[2].'-'.($month+1).'-'.$matches[1].' 00:00:00';
            elseif(strlen($matches[2]) == 2)
            $tmpEndDate = '20'.$matches[2].'-'.($month+1).'-'.$matches[1].' 00:00:00';
        }
        if(isset($tmpEndDate))
        {
           return $tmpDate = array('startDate'=>$LinkStartDate,'endDate'=>$tmpEndDate);
        }
    }
    
    //8. bis 30.04.2018
    if(preg_match('@bis.*?(\d+\.\d+\.\d+)@im',$str,$matches)){
        $tmpEndDate = preg_replace('@(\d+)\.(\d+)\.(\d+)@','$3-$2-$1 00:00:00',$matches[1]);
        return $tmpDate = array('startDate'=>$LinkStartDate,'endDate'=>$tmpEndDate);
    }
    
    //9. vom 22.12.2016
    if(preg_match('@Gültig.*?(\d+\.\d+\.\d+)@im',$str,$matches)){
        $tmpEndDate = preg_replace('@(\d+)\.(\d+)\.(\d+)@','$3-$2-$1 00:00:00',$matches[1]);
        return $tmpDate = array('startDate'=>$LinkStartDate,'endDate'=>$tmpEndDate);
    }
    
    return $tmpDate;
}


//满足strtotime时间格式
function preg_strtotime($str,$LinkStartDate){

    $tmpEndDate = '';
    //格式1：YYYY-MM-DD
    if(preg_match('/\d{2,4}-\d{1,2}-\d{1,2}/',$str,$matches)){
        $tmpEndDate = $matches[0];
    }

    //格式2：m/d/y
    if(preg_match('/\d{1,2}\/\d{1,2}\/\d{2,4}/',$str,$matches)){
        $tmpEndDate = $matches[0];
    }

    //格式3：d-m-y
    if(preg_match('/\d{1,2}-\d{1,2}-\d{2,4}/',$str,$matches))
    {
        $tmpEndDate = $matches[0];
    }
     
    //格式4：15 October 1980
    $monthStr = "January|February|March|April|May|June|July|August|September|October|November|December";
    if(preg_match('/\d{1,2}\s+('.$monthStr.')\s+\d{2,4}/',$str,$matches)){
        $tmpEndDate = $matches[0];
    }


    //如果能匹配到，并且有过期字眼，则认为是过期时间
    if($tmpEndDate){
         
        if(preg_match('@End.*?'.$tmpEndDate.'@i',$str,$tmpMatchs))  //Ends $tmpEndDate.
            return array('startDate'=>$LinkStartDate,'endDate'=>date('Y-m-d H:i:s',strtotime($tmpEndDate)));
        elseif(preg_match('@valid.*?-?.*?'.$tmpEndDate.'@i',$str,$tmpMatchs)) //valid $tmpEndDate.
            return array('startDate'=>$LinkStartDate,'endDate'=>date('Y-m-d H:i:s',strtotime($tmpEndDate)));
        elseif(preg_match('@exp.*?'.$tmpEndDate.'@i',$str,$tmpMatchs)) //exp $tmpEndDate
            return array('startDate'=>$LinkStartDate,'endDate'=>date('Y-m-d H:i:s',strtotime($tmpEndDate)));
        elseif(preg_match('@from.*?(-|to).*?'.$tmpEndDate.'@i',$str,$tmpMatchs)) //From   -|to $tmpEndDate
            return array('startDate'=>$LinkStartDate,'endDate'=>date('Y-m-d H:i:s',strtotime($tmpEndDate)));
        elseif(preg_match('@only until.*?'.$tmpEndDate.'@i',$str,$tmpMatchs)) //only until $tmpEndDate
            return array('startDate'=>$LinkStartDate,'endDate'=>date('Y-m-d H:i:s',strtotime($tmpEndDate)));
        else
            return false;
    }
    return false;

}

function analyze_language($str){
    
    $language = 'en';
    $strUnicode =  utf8_unicode($str);
    $pattern = '/([\w]+)|(\\\u([\w]{4}))/i';
    preg_match_all($pattern, $strUnicode, $matches);
    $isEnglish = true;
    if (!empty($matches))
    {
        for ($j = 0; $j < count($matches[0]); $j++)
        {
            //echo   base_convert($matches[0][$j], 16, 10).'<br/>';
            $strUnicodeToten = base_convert($matches[0][$j], 16, 10);
            if($strUnicodeToten<0 || $strUnicodeToten>127){
                $isEnglish = false;
                break;
            }
        }
    }
    if(!$isEnglish){
        $language = analyze_language_byKeywords($str);
        if($language == 'en'){
            //根据域名后缀关键字
            $language = analyze_language_byDomain($str);
        }
    }
    return $language;
}

function analyze_language_byKeywords($str){    
    
    $language = 'en';
    $fr_keywords = array("Réduction","Réductions","Privé","Privée","Privées","Jusqu'à","Remise","Remises","Livraison","Gratuit","Gratuite","Gratuites","Expédition","Solde","Soldes","Expédié","Expédiés","Expédiées","Expédier","Dès","Livraison","Livraisons","Moins de","Démarque","Démarques","Frais","Cadeau","Sur votre","Bon plan","Sans frais","votre","vôtre","sur une","sélection","d'articles","d'article","valeur","à partir","commande","Obtenez","Fidé
lité","Fidélités","Récompensée","Récompense","Rabais","Gagnez","Gagner","Supplémentaire","Découvrez","Au lieu","avec","avant","le code","prix","Bénéficiez","Bénéficier","Bénéficié","Profitez","Bienvenue","meilleur","à partir","réservez","réservé","Nouveaux","Nouveau","Nouvelles","Nouvelle","d'achat","Départ","Utilisez","Utilisé","Dernière","commandes","spécial","exceptionnel","cadeau","arrivée","arrivé","économisez","économisé","achetez","acheté","de plus",
        "personnes","personne","première","de remise","de rabais","réduc","prévente","après","le coupon","du coupon","des coupons","de coupons","les coupons","jouets","jouet","modèle","sur les","sur le","valable","Précommande","à petit","à",
        "bon","traiter","offres","pièces justificatives","code de réduction","codes de réduction","remise","coupon de réduction","économie","vente","Ventes","les","livraison gratuite","livraison","seulement","prendre","avoir","vacances","du quotidien",
        "jusqu'à","dépensez","Cadeaux"
    );
    
    $de_keywords = array('Gutschein','Sparen','Rabatt','Aktion','Saleangebot','Angebot','Sortiment','Schnäppchen','Nachlass','jetzt','Frühbucher','Buchen','Buchung','Lieferung','Frühling','Sommer','Herbst','Reduktion','Reduziert','Bestellung','Bestellungen','Versand','Bestellwert','Mindestbestellwert','Anmeldung','kostenlos','Rabattiert','Gutscheincode','Erhalten','Warenkorb','Skonto','gültig','Kunde','Für','Herren','Damen','versandkostenfrei','versandkosten','Artikel','günstig','Prozent','Überweisung','exklusiv','Muttertag','Valentinstag','Attraktiv','weihnachten',
        'Gutschein','Angebote','Gutscheine','Gutscheincode','Gutscheincodes','Rabatte','Rabattcode','Rabattcodes','speichern','Ersparnisse','Verkauf','Der Umsatz','das','Angebote','Kostenloser Versand','Versand','nur','nehmen','haben','Urlaub','Täglich','Geschenkset','KOSTENLOSEN',
    
    );
    
    
    $ru_keywords = array('Б','б','Г','г','Д','д','Ё','ё','Ж','ж','З','з','И','и','Й','й','Л','л','Ц','ц','Ч','ч','Ш','ш','Щ','щ','Ъ','ъ','Ы','ы','Ь','ь','Э','э','Ю','ю','Я','я',
        'ваучер','купон','по рукам','предложения','купоны','ваучеры','код купона','коды купонов','код ваучера','коды ваучера','скидка','скидки','код скидки','скидочные коды','купон на скидку','экономия','продажа','продажи','бесплатная доставка','Перевозка','только','принимать','иметь','день отдыха','ежедневно',
        'vremeni'
    );
    
    $it_keywords = array("Riduzione", "sconti", "sconto", "consegna", "Privato","spedizione","Vendite", "inviato", "Spedito", "avanti", "da", "consegna","ribassi","fresco","regalo","sui tuoi","Consigli","tuo","selezione","articoli","voce","valore","da","ordine","Fidelity","lealtà","Onorato","Agon","sconto","Vincere","invece", "avanti","theCode", "prezzo", "beneficiato", "Benvenuto",
        "migliore","apartir","libro","riservato","Nuov","acquisto","usato","ultimo","comando","speciale","unico","dono","arrivo","vieni","salvare","salvato","acquistare","acquisti","persone", "primo", "deremise", "derabais", "Prevendita", "dopo", "buoni", "giocattoli", "giocattolo", "stile", "valido", "piccolo","tagliando","buono","affare","offerte","tagliandi","codici promozionali","codice promozionale","codici voucher","codice di sconto","Codici Sconto","buono sconto","risparmi","saldi","Speciali","spedizione gratuita","spedizione","prendere","avere","vacanza","quotidiano",
        "Spese di"
    );
    
    $es_keywords = array("Reducción", "descuentos","descuento", "Libre","envío", "Equilibrio", "Ventas", "Enviado", "Adelante", "menos", "rebajas","fresco","regalo","en sus","Consejos","suyo","selección","artículos","elemento","orden","fidelidad","lealtad","Recompensa","Ganar", "en vez",  "adelante", "código", "precio", "beneficio", "disfrutar", "bienvenida", "mejor", "desde", "libro", "reservado", "nuevo",
        "nuevo", "noticias", "nuevo", "comenzar", "usar", "usado","Última", "especial", "único", "regalo", "llegada", "ven", "salvar", "salvado", "comprar", "compra", "más","personas", "descuento","después de","cupón","cupón","cupones","cupones","cupones","juguetes","juguete","estilo","en la el","en el","válido","Pre-orden","pequeño","comprobante","acuerdo","ofertas","comprobantes",
        "Código promocional","códigos de cupones","Código de cupón","descontar","descuentos","código","ahorro","ahorros","venta","el","especiales","solamente","tomar","tener","fiesta","diario",
    
    );
    
    $pt_keywords = array("Redução", "descontos","desconto","Vendas", "Encaminhar", "Encolher", "descontos", "presente", "no seu", "Conselho", "Toll", "seu",  "seleção", "itens", "ordem", "Obter", "lealdade","Recompensar","Ganhar","Descobrir","em vez","adiante","preço","benefício","usar","usado","último","controle","especial","exclusivo","presente","chegada", "salvar","mais", "pessoas", "pessoa", "primeiro", "desconto", "duque", "adiantamento", "após", "cupão", "cupons", "brinquedos", "brinquedo", "estilo", "Pré-encomenda",
        "pequeno","comprovante","cupom","Código","desconto","salvando","poupança","venda","especiais","frete grátis","Remessa","só","levar","ter","feriado","diariamente"
    );
    
    $nl_keywords = array("Korting","Levering", "Privé", "Verzending", "Verkoop", "Verzonden", "Doorsturen", "Levering", "Korting", "vers","op uw","Advies","selectie","artikelen", "waarde","volgorde","Trouw","loyaliteit","korting","in plaats daarvan","vooruit","prijs","voordeel","Welkom","beste","apartir","boek","gereserveerd","Nieuwe","aankoop","Gebruik", "gebruikt", "laatste","opdracht","speciaal","alleen","aankomst","kom","opslaan",
        "opgeslagen","kopen","mensen","eerste", "Voorverkoop", "kortingsbonnen", "speelgoed", "speelgoed", "stijl", "geldig", "aanbiedingen","waardebonnen","korting","besparing","spaargeld","verkoop","het","geen","Verzenden","enkel en alleen","nemen","hebben","vakantie","dagelijks"
    );
    
    $se_keywords = array("Rabatt","Frakt", "Försäljning","Skickat", "Framåt", "Från", "Leverans", "Rabatt","färsk","gåva","på din","Råd","artiklar","objekt","värde","från","i stället","framåt", "sista","kommandot","gåva","ankomst","kom","spara","sparat","köp","människor","först","deremise","derabais",
        "kuponger", "leksaker", "leksak", "giltiga","handla","erbjudanden","kupongerna","kuponger","kupongskod","kupongkoder","rabattkod","sparande","besparingar","försäljning","specialare","endast","helgdag","dagligen"
    
    );
    
    $ukr_language = array("Зниження", "Знижка", "Знижка", "Доставка", "Приватна", "Доставка", "Продаж", "Відправлені", "Відвантажені", "Вперед", "Доставка", "Знижка", "подарунок","статтях" , "від", "замовлення", "вірність", "лояльність",  "Вибрати","замість","вперед","код","ціну","вигоду","Ласкаво просимо ","найкраще","книга","зарезервована",
        "Нова покупка", "Використовувати","останній","команда","спеціальний","тільки","подарунок","прибуття","прийти","зберегти","зберегти","купити","купити","деплюс","люди","людина","перша" , "доработа", "Авансовий продаж", "після", "купони", "іграшки", "іграшка", "стиль", "вгору", "дійсна", "мала","угода","угоди","ваучери","коди купонів","знижки",
        "код на знижку","коди знижки","дисконтний купон","економія","заощадження","продаж","мати","свято","щодня"
    );
    $jp_language = array("割引","配達","プライベート","出荷","販売","送信","転送","出庫","配達","あなたの","選択","記事 ","アイテム","値","忠実","忠誠 ","オノラト","歓迎","顺序 ","予約 ","新規","購入","最後","コマンド","のみ","贈り物","到着する","来る","保存する","デプラス","最初に","脱remise", "デパート","前売り","後","クーポン","おもちゃ","おもちゃ","スタイル","対処","お得","クーポン",
        "ク","ウチ","貯蓄","節約","削減","送料無料","運送","取る","持ってる","休日",
    );
    foreach($fr_keywords as $v){
        if(preg_match('/\b'.$v.'\b/is',$str,$matches)){
            $language = 'fr';
            return $language;
        }
    }
    
    foreach($de_keywords as $v){
        if(preg_match('/\b'.$v.'\b/is',$str,$matches)){
            $language = 'de';
            return $language;
        }
    }
    
    foreach($ru_keywords as $v){
        if(preg_match('/'.$v.'/is',$str) || preg_match('/^'.$v.'/is',$str) || preg_match('/'.$v.'$/is',$str)){
            $language = 'ru';
            return $language;
        }
    }
    
    foreach($it_keywords as $v){
        if(preg_match('/\b'.$v.'\b/is',$str,$matches)){
            $language = 'it';
            return $language;
        }
    }
    
    foreach($es_keywords as $v){
        if(preg_match('/\b'.$v.'\b/is',$str,$matches)){
            $language = 'es';
            return $language;
        }
    }
    
    foreach($pt_keywords as $v){
        if(preg_match('/\b'.$v.'\b/is',$str,$matches)){
            $language = 'pt';
            return $language;
        }
    }
    
    foreach($nl_keywords as $v){
        if(preg_match('/\b'.$v.'\b/is',$str,$matches)){
            $language = 'nl';
            return $language;
        }
    }
    
    foreach($se_keywords as $v){
        if(preg_match('/\b'.$v.'\b/is',$str,$matches)){
            $language = 'se';
            return $language;
        }
    }
    
    foreach($ukr_language as $v){
        if(preg_match('/\b'.$v.'\b/is',$str,$matches)){
            $language = 'ukr';
            return $language;
        }
    }
    foreach($jp_language as $v){
        if(preg_match('/'.$v.'/is',$str,$matches)){
            $language = 'jp';
            return $language;
        }
    }
    return $language;
}

function analyze_language_byDomain($str){
    $language = 'en';
    $keywords = array(
        'fr'=>array('\.fr','fr\.')
    );
    $keywords += array(
        'de'=>array('\.de'.'de\.')
    );
    $keywords += array(
        'ru'=>array('\.ru','ru\.')
    );
    $keywords += array(
        'it'=>array('\.it')
    );
    $keywords += array(
        'es'=>array('\.es','es\.')
    );
    $keywords += array(
        'pt'=>array('\.pt','pt\.')
    );
    $keywords += array(
        'nl'=>array('\.nl','nl\.')
    );
    $keywords += array(
        'se'=>array('\.se','se\.')
    );
    $keywords += array(
        'ukr'=>array('\.ua','ua\.')
    );
    $keywords += array(
        'jp'=>array('\.jp','jp\.')
    );
    
    foreach($keywords as $contry=>$domainSuffix){
        foreach ($domainSuffix as $domainCountry)
        {
            if(preg_match('/\b'.$domainCountry.'\b/',$str,$matches)){
                $language = $contry;
                return $language;
            }
        }
    
    }
    return $language;
}

function utf8_unicode($name){
    $name = @iconv('UTF-8', 'UCS-4', $name);
    //print_r($name);
    $len  = strlen($name);
    $str  = '';
    for ($i = 0; $i < $len - 1; $i = $i + 2){
        $c  = $name[$i];
        $c2 = $name[$i + 1];
        //echo '<br/>'.$c.'--'.$c2.'<br/>';
        if (ord($c) > 0){   //两个字节的文字
            $str .= '\u'.base_convert(ord($c), 10, 16).str_pad(base_convert(ord($c2), 10, 16), 2, 0, STR_PAD_LEFT);
            //$str .= base_convert(ord($c), 10, 16).str_pad(base_convert(ord($c2), 10, 16), 2, 0, STR_PAD_LEFT);
        } else {
            $str .= '\u'.str_pad(base_convert(ord($c2), 10, 16), 4, 0, STR_PAD_LEFT);
            //$str .= str_pad(base_convert(ord($c2), 10, 16), 4, 0, STR_PAD_LEFT);
        }
    }
    $str = strtoupper($str);//转换为大写
    return $str;
}


/*
 *  money    减多少钱
 from       最低价
 precent   减％
 free_trial  免费试用
 free_download 免费下载
 free_gift    免费送礼物
 free_sample 免费送小样
 free_shipping 免邮
 bngn  买X送X
 sale_clearance 清仓
 reward   奖励
 rebate    返利
 other    其他
 * */
function analyze_promo_keywords($str,$all=''){
    $arr = array();
    $allKey = array();
    $where = '';
    $checkKey = explode('|', $str);

    $keywords = array('money'=>array(':','money_currency'),'from'=>array(':','from_currency'),'percent'=>':','free_trial'=>'','free_download'=>'','free_gift'=>'',
        'free_sample'=>'','free_shipping'=>'','bngn'=>'','sale_clearance'=>'','reward'=>'','rebate'=>'','other'=>'');

    if($checkKey){
         

        foreach ($checkKey as $value){
            foreach ($keywords as $k=>$v){
                if(strripos($value,$k)!==FALSE){
                    $fieldValue = 1;
                    if(isset($v[0]) && !empty($v[0])){
                        $fieldValue = explode($v[0], $value);
                        if(isset($v[1])){
                            $arr[$k] = isset($fieldValue[2]) ? $fieldValue[2] : '';
                            $arr[$v[1]] = $fieldValue[1];
                        }else{
                            $arr[$k] = $fieldValue[1];
                        }

                    }else{
                        $arr[$k] = $fieldValue;
                    }


                }
            }
        }
        if($arr){
            foreach ($arr as $filterK=>$filterV){
                $where .= ' AND key_'.$filterK. " = '$filterV'";
            }
        }

    }



    if(isset($all) && $all == 'all'){
        foreach ($keywords as $k1=>$v1){

            if(isset($arr[$k1])){
                $allKey['key_'.$k1] = $arr[$k1];
                if(isset($v1[1])){
                    $allKey['key_'.$v1[1]] = $arr[$v1[1]];
                }
            }else {
                $allKey['key_'.$k1] = '';
            }
        }
        foreach ($allKey as $fkey=>$fvalue){
            $allKey[$fkey] = str_replace(',', '', $fvalue);
        }
        return $allKey;
    }


    return $where;
}

function is_freeshipping($str,$type){


    $freeShippingKeywordRegx = array(
        '/free\s*(shipping|delivery|S&H)/is',
        '/\bfree\s+[^\s]+\s+shipping\b/is',
        '/\bflat-rate\s+shipping\b/is',
        '/\bfree\s+[^\s]+\s+.*delivery\b/is',
        '/ships\s+free/is'
    );

    if ($type == 'Promotion') {
        foreach ($freeShippingKeywordRegx as $val) {
            if (preg_match($val, $str, $match)) {
                $type = 'FreeShipping';
                break;
            }
        }
    }
    return $type;

}

function checkProcess(){
    $cmd = `ps aux | grep /home/bdg/program/cron/get_feed_contents_small_new.php | grep 'grep' -v | grep '/bin/sh' -v -c`;
    $return = ''.$cmd.'';
    //var_dump($return);exit;
    if($return > 1){
        return false;
    }else{
        return true;
    }
}


?>
