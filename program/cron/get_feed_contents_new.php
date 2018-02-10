<?php
//从affiliate_links_all_simple表里取出isactive 跟 ispromotion的数据
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
$length = 100; //每次取100条

echo "<< Start @ ".date("Y-m-d H:i:s")." >>\r\n";

$objProgram = New ProgramDb();
$sql_names_set = 'SET NAMES latin1';
$objProgram->objMysql->query($sql_names_set);

$sql_names_set = 'SET NAMES latin1';
$objProgram->objPendingMysql->query($sql_names_set);


//条数小于2万发警告邮件
echo "Network promo script Start >>\r\n";
$sql = "select count(*) as count from affiliate_links_all_simple where IsActive = 'YES' and IsPromotion = 'YES'";
$warningCount = $objProgram->objMysql->getRows($sql);
if($warningCount[0]['count'] < 10000){
    $to = "merlinxu@brandreward.com,stan@brandreward.com";
    AlertEmail::SendAlert('new content feed count too few',nl2br("new content feed count:".$warningCount[0]['count']), $to);
    exit;
}
$nowDay = date('Y-m-d H:i:s',time());
$i = 0;
$j = 0;
$new_content = 0;
$alertArr = array();
$column_keys = array('SimpleId','ProgramId','StoreId','CouponCode','Title','`Desc`','StartDate','EndDate','AffUrl','`AddTime`','LastChangeTime',
            '`language`','`Status`','`Type`'
        );
$storeInfo = array();
do{
    $offset = $length*$i- 1 > 0 ? $length*$i- 1 : 0;
    $sql = "select * from affiliate_links_all_simple where IsActive = 'YES' and IsPromotion = 'YES' limit $offset, $length";
    //$sql = "select * from affiliate_links_all_simple where IsActive = 'YES' and IsPromotion = 'YES'  and id = 17813";
    $data = $objProgram->objMysql->getRows($sql);
    $i++;
    foreach ($data as $v){
        
        $linkInfo = array();
        $table = 'affiliate_links_'.$v['affid'];        
        $linkSql = "select * from $table where AffMerchantId = '{$v['PidInaff']}' and  AffLinkId = '".addslashes($v['AffLinkId'])."' and isactive = 'YES'";
        $linkInfo = $objProgram->objPendingMysql->getFirstRow($linkSql);
         
        if(empty($linkInfo)){
            continue;
        }
        //if($v['affid']==360 && $linkInfo['Type'] != 'promotion')
        //    continue;
        
        //对取到的link进行过滤
        if($linkInfo['LinkEndDate'] != '0000-00-00 00:00:00' && $linkInfo['LinkEndDate'] < $nowDay) continue;
        if(empty($linkInfo['LinkAffUrl'])) continue;
        if(stripos($linkInfo['LinkName'],'.png')) continue;
        if(stripos($linkInfo['LinkName'],'.jpg')) continue;
        if(stripos($linkInfo['LinkName'],'.gif')) continue;
        //Biltmore Estates 160x600 500 x 500
        if(preg_match('/\dx\d/i',$linkInfo['LinkName'],$matches))
        {
            continue;
        }
        $linkInfo['LinkName'] = trim(preg_replace('/\d+\s*x\s*\d+/i','',$linkInfo['LinkName']));
        $linkInfo['LinkName'] = str_replace(array('(',')','-'),'',$linkInfo['LinkName']);
        
        
        
        //查询是否有这条记录
        $selContentFeedSql  = "select id from content_feed_new where `SimpleId` = {$v['ID']} and source = 'site'";
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
                    continue;
                }else {
                    continue;
                }
            }
        }
        //时间过滤结束
        //echo 'KeyWords:'.$v['KeyWords'].PHP_EOL;
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
                //$alertArr = array();
                $sql = "select id,title from content_feed_new  where  CouponCode='".addslashes($linkInfo['LinkCode'])."' and `Status` = 'Active' and ProgramId = {$v['ProgramId']} $keyWhere";
                $reqInfo = $objProgram->objMysql->getRows($sql);
                if(!empty($reqInfo)){
                    //$linkcode =  addslashes($linkInfo['LinkCode']);
                    //if(!isset($alertArr[$linkcode])){
                    //    $alertArr[$linkcode] = count($reqInfo);
                    //}
                    $updateRep = "update content_feed_new set LastUpdateTime = '$nowDay' where id = {$reqInfo[0]['id']}";
                    $objProgram->objMysql->query($updateRep);
                    continue;
                    //echo "filter {$reqInfo[0]['id']}--{$reqInfo[0]['title']} \r\n";
                    // continue;
                    //if($alertArr[$linkcode]>10)
                    //    echo "$linkcode ====>count('{$alertArr[$linkcode]}')\r\n";
                }        
            }
        }
        
        if ($v['affid'] == 360 || $v['affid'] == 63 || $v['affid'] == 65)
            $language = 'de';
        else
            $language = analyze_language($linkInfo['LinkName'].$linkInfo['LinkDesc']);
        
        
        
        //过滤掉title coupon  相同掉links
        $sql = "select id,title from content_feed_new  where Title='".addslashes($linkInfo['LinkName'])."' and CouponCode='".addslashes($linkInfo['LinkCode'])."' and `Status` = 'Active' and ProgramId = {$v['ProgramId']} and language = '$language' $keyWhere";
        $reqInfo = $objProgram->objMysql->getRows($sql);
        if(!empty($reqInfo)){
            
            $updateRep = "update content_feed_new set LastUpdateTime = '$nowDay',`EndDate` = '".$linkInfo['LinkEndDate']."' where id = {$reqInfo[0]['id']}";
            $objProgram->objMysql->query($updateRep);
            //echo "filter {$reqInfo[0]['id']}--{$reqInfo[0]['title']} \r\n";
            continue;
        }
        
        
        
        
        
        
        
        
        
        //找出这个links对应的store
        
        if(!isset($storeInfo[$v['ProgramId']])){
            $storeSql = "select b.StoreId from r_domain_program a left join r_store_domain b on a.did = b.domainid where a.pid = {$v['ProgramId']}";
            $storeInfo = $objProgram->objMysql->getFirstRow($storeSql);
            $storeInfo[$v['ProgramId']] = $storeInfo['StoreId'];
        }
        if(empty($storeInfo[$v['ProgramId']])){
            continue;
        }
       
        
        //确定type
        if(!empty($linkInfo['LinkCode'])){
            if(preg_match('/(no|none|Not)\s+/i',$linkInfo['LinkCode'],$matches) || $linkInfo['LinkCode']=='none'){
                $type = 'Promotion';
            }else{
                $type = 'Coupon';
            }
        }else {
            $type = 'Promotion';
        }
        
        $allPromo =  analyze_promo_keywords($v['KeyWords'],'all');
        
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
            'LastUpdateTime' => $nowDay,
            'AddTime' => $nowDay,
            'LastChangeTime'=>$linkInfo['LastChangeTime'],
            '`language`' => $language,
            '`Status`' => 'Active', //Active InActive
            '`Type`' => $type, //$v['LinkCode'] ?  'Coupon' : 'Promotion'//Coupon Promotion
            '`source`'=> 'site',
        );
        $tmp_data += $allPromo;
        
        
        $column_keys = array('SimpleId','ProgramId','StoreId','CouponCode','Title','`Desc`','StartDate','EndDate','AffUrl','`AddTime`','LastChangeTime',
            '`language`','`Status`','`Type`','`source`'
        );
        
        $allPromoKey = array_keys($allPromo);
        $column_keys = array_merge($column_keys,$allPromoKey);
        
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
        
        $objProgram->objMysql->query($insertSql);
        $j++;
        unset($tmp_insert);
        unset($tmp_update);
        
    }
    
    
    //echo $j."\r\n";
    
}while(count($data)>0);


echo "total Network ($j)\r\n";
echo "Add new Network ($new_content)\r\n";
//check not update
$sql = "select count(*) from content_feed_new where LastUpdateTime < '$nowDay' and status = 'active' and ISNULL(adduser) and source = 'site'";
$cnt = $objProgram->objMysql->getFirstRowColumn($sql);

if($cnt > 2000){
    $to = "merlinxu@brandreward.com,stan@brandreward.com";
    AlertEmail::SendAlert('content feed to inactive too much',nl2br("to inactive count:".$cnt), $to);
    exit;
}

$sql = "update content_feed_new set status = 'InActive' where LastUpdateTime < '$nowDay' and status = 'active' and ISNULL(adduser) and source = 'site'";
$objProgram->objMysql->query($sql);
echo "Set Network $cnt Inactive.\r\n";

//email promo start
echo "Email promo script Start >>\r\n";
$startTime = date('Y-m-d 00:00:00',time());
$i = 0;
$j = 0;
$new_content = 0;
do{
    $offset = $length*$i- 1 > 0 ? $length*$i- 1 : 0;
    $sql = "select * from affiliate_email_promo  limit $offset, $length";

    $data = $objProgram->objPendingMysql->getRows($sql);
    $i++;
    foreach ($data as $value){

        //对取到的email进行过滤
        if($value['ExpireTime'] != '0000-00-00 00:00:00' && $value['ExpireTime'] < $nowDay) continue;
        if(empty($value['Merchant_Originanl_Url'])) continue;
         
        if(preg_match('@(http|https)://(www\.)?([^/]+)/*@',$value['Merchant_Originanl_Url'],$matches)){
            $domain = $matches[3];
            //echo  $value['ID'].'---'.$domain.PHP_EOL;

        }else
            continue;

        //查询storeId
        $storeInfo = array();
        $storeSql = "select ID from store where Domains like '%".$domain."%'";
        $storeInfo = $objProgram->objMysql->getFirstRow($storeSql);
        if(!empty($storeInfo)){
            $storeId = $storeInfo['ID'];
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
        $sql = "select * from content_feed_new where StoreId = $storeId and CouponCode = '{$value['Code']}' and Title = '".addslashes($value['Title'])."' and source = 'site'";
        $feedInfo = $objProgram->objMysql->getFirstRow($sql);
        if(!empty($feedInfo)){
            continue;
        }

        //查询是否有这条记录
        $selContentFeedSql  = "select id from content_feed_new where `SimpleId` = {$value['ID']} and source = 'email'";
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
            'ProgramId' => '',
            'StoreId' => $storeId,
            'CouponCode' => $value['Code'],
            'Title' => addslashes($value['Title']),
            '`Desc`' => strip_tags(addslashes($value['Description'])),
            'StartDate' => $value['StartTime'],
            'EndDate' => $value['ExpireTime'],
            'AffUrl' => $affUrl,
            'LastUpdateTime' => $nowDay,
            'AddTime' => $nowDay,
            'LastChangeTime'=>'0000-00-00 00:00:00',
            '`language`' => analyze_language($value['Title'].$value['Description']),
            '`Status`' => 'Active', //Active InActive
            '`Type`' => $type, //$v['LinkCode'] ?  'Coupon' : 'Promotion'//Coupon Promotion
            '`source`'=> 'email',
        );

        $column_keys = array('SimpleId','ProgramId','StoreId','CouponCode','Title','`Desc`','StartDate','EndDate','AffUrl','`AddTime`','LastChangeTime',
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

//check not update email
$sql = "select count(*) from content_feed_new where LastUpdateTime < '$nowDay' and status = 'active' and ISNULL(adduser) and source = 'email'";
$cnt = $objProgram->objMysql->getFirstRowColumn($sql);
$sql = "update content_feed_new set status = 'InActive' where LastUpdateTime < '$nowDay' and status = 'active' and ISNULL(adduser) and source = 'email'";
$objProgram->objMysql->query($sql);
echo "Set Email $cnt Inactive.\r\n";


//check expire
$sql = "select count(*) from content_feed_new where status = 'active' and enddate <= '".date('Y-m-d H:i')."' and enddate != '0000-00-00 00:00:00'";
$cnt = $objProgram->objMysql->getFirstRowColumn($sql);
$sql = "update content_feed_new set status = 'InActive' where status = 'active' and enddate <= '".date('Y-m-d H:i')."' and enddate != '0000-00-00 00:00:00'";
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


//add to redis
$objRedis = new Redis();
$objRedis->pconnect(REDIS_HOST, REDIS_PORT);
$tmp_redis = array();
$tmp_redis = $objRedis->keys(":CF:*");
$tmp_redis = array_flip($tmp_redis);
echo ":ContentFeed::".count($tmp_redis)."\r\n";

$i = $j = 0;
while(1){	
	$sql = "select a.id, a.encodeid, a.affurl, a.programid, a.storeid, b.affid, b.domain from content_feed_new a left join program_intell b on a.programid = b.programid where a.encodeid > 0 and a.status = 'active' limit " . $i * 100 . ", 100";
	$tmp_arr = $objProgram->objMysql->getRows($sql);
	if(!count($tmp_arr)) break;
	
	foreach($tmp_arr as $v){
		if(strlen($v['domain']) && strpos($v['domain'], ',') !== false){
			$v['domain'] = substr($v['domain'], 0, strpos($v['domain'], ','));
		}
	    $redisArr = array(
	        'programid'=>$v['programid'],
	        'storeid'=>$v['storeid'],
	        'affurl'=>$v['affurl'],
	    	'affid'=>$v['affid'],
	    	'domain'=>$v['domain'],
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
echo "Update ContentFeed :($j)\r\n";

if(count($tmp_redis)){
     foreach($tmp_redis as $k => $iv){
         //$objRedis->del($k);
         $encodeid = 0;
         $encodeid =  substr(strrchr($k, ":CF:"), 1);
         if(!$encodeid){
             continue;
         }
         $sql = "select id, encodeid, affurl,programid,storeid,OriginalUrl from content_feed_new where encodeid = $encodeid";
         $tmp_arr = $objProgram->objMysql->getFirstRow($sql);
         if($tmp_arr){
             $redisInactiveArr = array(
                 'programid'=>$tmp_arr['programid'],
                 'storeid'=>$tmp_arr['storeid'],
                 'affurl'=>$tmp_arr['OriginalUrl'] ? $tmp_arr['OriginalUrl'] : $tmp_arr['affurl'],
                 'status'=>'inactive',
             );
             $redisInactiveValue = json_encode($redisInactiveArr);
             $objRedis->set($k, $redisInactiveValue);
         }
     }
 }
echo "to inactive ContentFeed :(".count($tmp_redis).")\r\n";

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
	$fr_keywords = array("Réduction","Réductions","Privé","Privée","Privées","Jusqu'à","Remise","Remises","Livraison","Gratuit","Gratuite","Gratuites","Expédition","Solde","Soldes","Expédié","Expédiés","Expédiées","Expédier","Dès","Livraison","Livraisons","Moins de","Démarque","Démarques","Frais","Cadeau","Sur votre","Bon plan","Sans frais","votre","vôtre","sur une","sélection","d'articles","d'article","valeur","à partir","commande","Obtenez","Fidé
lité","Fidélités","Récompensée","Récompense","Rabais","Gagnez","Gagner","Supplémentaire","Découvrez","Au lieu","avec","avant","le code","prix","Bénéficiez","Bénéficier","Bénéficié","Profitez","Bienvenue","meilleur","à partir","réservez","réservé","Nouveaux","Nouveau","Nouvelles","Nouvelle","d'achat","Départ","Utilisez","Utilisé","Dernière","commandes","spécial","exceptionnel","cadeau","arrivée","arrivé","économisez","économisé","achetez","acheté","de plus","personnes","personne","première","de remise","de rabais","réduc","prévente","après","le coupon","du coupon","des coupons","de coupons","les coupons","jouets","jouet","modèle","sur les","sur le","valable","Précommande","à petit","à");
	$de_keywords = array('Gutschein','Sparen','Rabatt','Aktion','Saleangebot','Angebot','Sortiment','Schnäppchen','Nachlass','jetzt','Frühbucher','Buchen','Buchung','Lieferung','Frühling','Sommer','Herbst','Reduktion','Reduziert','Bestellung','Bestellungen','Versand','Bestellwert','Mindestbestellwert','Anmeldung','kostenlos','Rabattiert','Gutscheincode','Erhalten','Warenkorb','Skonto','gültig','Kunde','Für','Herren','Damen','versandkostenfrei','versandkosten','Artikel','günstig','Prozent','Überweisung','exklusiv','Muttertag','Valentinstag','Attraktiv','weihnachten');
	$language = 'en';

    if(preg_match('/\$/is',$str)){
        return $language;
    }

    foreach($fr_keywords as $v){

        if(preg_match('/ '.$v.' /is',$str) || preg_match('/^'.$v.' /is',$str) || preg_match('/ '.$v.'$/is',$str)){
            //print_r($str."\n");
            //print_r($v."\n");
            $language = 'fr';
            return $language; 
        }
    }
    
    foreach($de_keywords as $v){
    
    	if(preg_match('/ '.$v.' /is',$str) || preg_match('/^'.$v.' /is',$str) || preg_match('/ '.$v.'$/is',$str)){
    		//print_r($str."\n");
    		//print_r($v."\n");
    		$language = 'de';
    		return $language;
    	}
    }
    return $language; 
}


//判断是否为DEAL
function isDeal($str){
    
    $flag = false;
    
    if (!preg_match('@\d+\s*x\s*\d+@i', $str, $match)) {
        if (preg_match('@\b(deal|save|less|purchase|give|discount|off|cash back|cashback|shop now)\b@i', $str, $match)) {
             
            $flag = true;
        
        }
    }
    
    
    return $flag;
}

function getEncodeId($retry = 0){
	global $key, $objProgram;
	$encodeid = '';
	$encodeid = random(8, $key);
	$sql = "select encodeid from content_feed_new where encodeid = '{$encodeid}'";
	$tmp_arr = array();
	$tmp_arr = $objProgram->objMysql->getFirstRow($sql);
	if(count($tmp_arr)){
		$retry++;
		if($retry < 10){
			$encodeid = getEncodeId($retry);
		}else{
			echo 'warning: retry > 10 , ';
			exit;
		}
	}
	return $encodeid;
}

function random($length, $key)
{
	$random = '';   
	$pool = '123456789';
	$pool .= substr(microtime(true), -2);//'1234567890';
	
	//srand ((double)microtime()*1000000);
	for($i = 0; $i < $length; $i++)	
	{   
		$random .= substr($pool,(rand()%(strlen ($pool))), 1);   
	}   

	return $random;   
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
                            $arr[$k] = $fieldValue[2];
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

        return $allKey;
    }


    return $where;
}

?>
