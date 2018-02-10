<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
$length = 100; //每次取100条

$objProgram = New ProgramDb();
$sql = "select ID from wf_aff where IsActive = 'YES'";
$affArr = $objProgram->objMysql->getRows($sql);
 
$tables = array();
foreach ($affArr as $v){
    $exist = $objProgram->objPendingMysql->isTableExisting('affiliate_links_'.$v['ID']);
    if($exist)
        $tables[$v['ID']] = 'affiliate_links_'.$v['ID'];
}

echo "<< Start @ ".date("Y-m-d H:i:s")." >>\r\n";

// affiliate_links_\d 的所有表
$new_content = 0;
$nowDay = date('Y-m-d H:i:s',time());
$column_keys = array("ProgramId", "AffLinkId", "CouponCode", "Title", "`Desc`", "StartDate", "EndDate", "AffUrl", "`AddTime`", "`Status`", "`Type`");
$check_change_field = array("CouponCode","Title","`Desc`","StartDate","EndDate");
foreach ($tables as $key=>$value){
    echo $value;
    //一个表一个do--while循环，每次100条数据。
    $i = 0;
    $j = 0;
    do{
        $offset = $length*$i- 1 > 0 ? $length*$i- 1 : 0;
        $LinkPromoType = "'coupon'";
        if($value == 'affiliate_links_1' || $value == 'affiliate_links_2' || $value == 'affiliate_links_7' || $value == 'affiliate_links_6' || $value == 'affiliate_links_58' || $value == 'affiliate_links_10' 
            || $value == 'affiliate_links_13' || $value == 'affiliate_links_15' || $value == 'affiliate_links_22'){
            $LinkPromoType = "'coupon','DEAL'";
        }
        $sql = "select * from ".$value." where LinkPromoType in (".$LinkPromoType.") and isactive = 'YES' GROUP BY AffMerchantId,LinkName,LinkDesc,LinkStartDate,LinkEndDate limit $offset, $length";




        $data = $objProgram->objPendingMysql->getRows($sql);
        $i++;
        foreach ($data as $v){
            
            if(!isset($programInfo[$key.'_'.$v['AffMerchantId']])){
                $programSql = "select a.ID from program a inner join program_intell b on a.id = b.programid where a.`AffId` = {$key} AND a.`IdInAff` = '{$v['AffMerchantId']}' and b.isactive = 'active'";
                $programInfo[$key.'_'.$v['AffMerchantId']] = $objProgram->objMysql->getFirstRow($programSql);
            }
            if(empty($programInfo[$key.'_'.$v['AffMerchantId']])) {
                //echo " no program {$v['AffMerchantId']}\t";
                continue;
            }
            //过滤字段
            //if($v['LinkStartDate'] != '0000-00-00 00:00:00' && $v['LinkStartDate'] > $nowDay) continue;
            //if($v['LinkStartDate'] == '0000-00-00 00:00:00') continue;   //edit date:2016-10-12
            if($v['LinkEndDate'] != '0000-00-00 00:00:00' && $v['LinkEndDate'] < $nowDay) continue;
            if($key != 7){
                if($v['LinkImageUrl'] && strlen($v['LinkImageUrl']) <= 7) continue;
            }
            
            if(empty($v['LinkAffUrl'])) continue;
            if($v['LinkPromoType'] == 'coupon' && empty($v['LinkCode'])) continue;
            //判断是否为deal
            if($v['LinkPromoType'] == 'DEAL' && !isDeal($v['LinkName'])) continue;
            
            //查询是否有这条记录
            $selContentFeedSql  = "select AffLinkId from content_feed where `ProgramId` = {$programInfo[$key.'_'.$v['AffMerchantId']]['ID']} AND `AffLinkId` = '".addslashes($v['AffLinkId'])."' ";
            $contentFeedInfo = $objProgram->objMysql->getFirstRow($selContentFeedSql);
             
            //根据信息里过滤掉一些过期条目；
            $tmpEndDate = preg_commonRule($v['LinkName'].','.$v['LinkDesc'],$v['LinkStartDate']);
            if(!$tmpEndDate)
                $tmpEndDate = preg_strtotime($v['LinkName'].','.$v['LinkDesc'],$v['LinkStartDate']);
            
             
            if($tmpEndDate && strtotime($v['LinkEndDate']) < strtotime($nowDay)){
                $v['LinkStartDate'] = $tmpEndDate['startDate']; //重置匹配到的开始时间
                if(strtotime($tmpEndDate['endDate']) > strtotime($nowDay)){ //如果能匹配到，并且过期时间大于现在的时候
                    $v['LinkEndDate'] = $v['LinkEndDate'] != '0000-00-00 00:00:00' ? $v['LinkEndDate']:$tmpEndDate['endDate'];
                }
                else{
                    //有这条记录就UPDATE Status = InActive，没有就continue
                    if($contentFeedInfo){
                        $updateContentFeedSql = "update content_feed set `Status` = 'InActive',EndDate = '".$tmpEndDate['endDate']."',LastUpdateTime = '$nowDay' where `ProgramId` = {$programInfo[$key.'_'.$v['AffMerchantId']]['ID']} AND `AffLinkId` = '{$v['AffLinkId']}' ";
                        $objProgram->objMysql->query($updateContentFeedSql);
                        continue;
                    }else {
                        continue;
                    }
                }
            }
            //过滤结束
             
            //确定type
            if(!empty($v['LinkCode'])){
                if(preg_match('/(no|none|Not)\s+/i',$v['LinkCode'],$matches) || $v['LinkCode']=='none'){
                    $type = 'Promotion';
                }else{
                    $type = 'Coupon';
                }
            }else {
                $type = 'Promotion';
            }
            
            $tmp_data = array(
                'ProgramId' => $programInfo[$key.'_'.$v['AffMerchantId']]['ID'],
                'AffLinkId' => $v['AffLinkId'],
                'CouponCode' => $v['LinkCode'],
                'Title' => $v['LinkName'],
                '`Desc`' => $v['LinkDesc'],
                'StartDate' => $v['LinkStartDate'],
                'EndDate' => $v['LinkEndDate'],
                //'HtmlCode' => $v['LinkHtmlCode'],
                'AffUrl' => $v['LinkAffUrl'],
                //'ImgeUrl' => $v['LinkImageUrl'],
                'LastUpdateTime' => $nowDay,
                'AddTime' => $nowDay,
                '`Status`' => 'Active', //Active InActive
                '`Type`' => $type, //$v['LinkCode'] ?  'Coupon' : 'Promotion'//Coupon Promotion
            );
            
            $column_keys = array("ProgramId", "AffLinkId", "CouponCode", "Title", "`Desc`", "StartDate", "EndDate", "AffUrl", "`AddTime`", "`Status`", "`Type`");
            
            if(!$contentFeedInfo){ //new content
            	$column_keys[] = 'EncodeId';
            	$tmp_data['EncodeId'] = intval(getEncodeId());
            	$new_content++ ;
            }
            
            $isChange = false;
            foreach ($tmp_data as $tk=>$tv){
                if($tk != 'LastUpdateTime')
                    $tmp_insert[] = addslashes($tv);
                if($tk != 'AddTime')
                    $tmp_update[] = "$tk = '".addslashes($tv)."'";
                
                if(in_array($tk,$check_change_field)){
                    if(!$isChange && isset($contentFeedInfo[$tk]) && $contentFeedInfo[$tk] != $tv) //找到不相等的，更新LastChangeTime
                        $isChange = true;
                }
            }
            if($isChange) $tmp_update[] = "LastChangeTime = '".$nowDay."'";
            
            $insertSql = "INSERT INTO content_feed (".implode(",", $column_keys).") VALUES ('".implode("','", $tmp_insert)."') ON DUPLICATE KEY UPDATE " . implode(",", $tmp_update) . ";";
            $objProgram->objMysql->query($insertSql);
            $j++;
            unset($tmp_insert);
            unset($tmp_update);
        }
        //echo 'IMPORT-'.$value."\r\n";
        //echo $sql ."\r\n";
        //echo 'IMPORT count:'.count($data) ."\r\n";
         
    }while(count($data)>0);
    
    echo "($j)\r\n";
}

echo "Add new ($new_content)\r\n";

//check not update
$sql = "select count(*) from content_feed where LastUpdateTime < '$nowDay' and status = 'active' and ISNULL(adduser)";
$cnt = $objProgram->objMysql->getFirstRowColumn($sql);
$sql = "update content_feed set status = 'InActive' where LastUpdateTime < '$nowDay' and status = 'active' and ISNULL(adduser)";
$objProgram->objMysql->query($sql);
echo "Set $cnt Inactive.\r\n";

//check expire
$sql = "select count(*) from content_feed where status = 'active' and enddate <= '".date('Y-m-d H:i')."' and enddate != '0000-00-00 00:00:00'";
$cnt = $objProgram->objMysql->getFirstRowColumn($sql);
$sql = "update content_feed set status = 'InActive' where status = 'active' and enddate <= '".date('Y-m-d H:i')."' and enddate != '0000-00-00 00:00:00'";
$objProgram->objMysql->query($sql);
echo "Set $cnt Expire.\r\n";


$i = 0;
$key = substr(strtotime(" - " . date("s") . "days"), -5);
while(1){
	$i++;
	$sql = "select id from content_feed where encodeid = 0 limit 100";
	$tmp_arr = $objProgram->objMysql->getRows($sql);
	if(!count($tmp_arr)) break;
	
	foreach($tmp_arr as $v){
		$encodeid = intval(getEncodeId());
		if($encodeid){
			$sql = "update content_feed set encodeid = $encodeid where id = {$v['id']}";
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
	$sql = "select id, encodeid, affurl from content_feed where encodeid > 0 and status = 'active' limit " . $i * 100 . ", 100";
	$tmp_arr = $objProgram->objMysql->getRows($sql);
	if(!count($tmp_arr)) break;
	
	foreach($tmp_arr as $v){
		$objRedis->set(":CF:".$v['encodeid'], $v['affurl']);
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
	foreach($tmp_redis as $k => $v){
		$objRedis->del($k);			
	}
}
echo "DEL ContentFeed :(".count($tmp_redis).")\r\n";

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
	$sql = "select encodeid from content_feed where encodeid = '{$encodeid}'";
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

?>
