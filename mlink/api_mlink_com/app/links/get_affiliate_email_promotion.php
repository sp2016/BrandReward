<?php
global $_cf,$_req,$_db,$_objPendingMysql;
echo "<< Start @ ".date("Y-m-d H:i:s")." >>\r\n";


$nowDay = date('Y-m-d H:i:s',time());
$objProgram = new Mysql(DB_NAME, DB_HOST, DB_USER, DB_PASS);
$_objPendingMysql = new Mysql(PENDING_DB_NAME, PENDING_DB_HOST, PENDING_DB_USER, PENDING_DB_PASS);
$sql_names_set = 'SET NAMES latin1';
$_objPendingMysql->query($sql_names_set);


//AffiliateUrlKeywords
$sql = "SELECT ID,AffiliateUrlKeywords,AffiliateUrlKeywords2,IsActive,Domain FROM wf_aff WHERE IsInHouse='NO'";
$aff_tmp = $objProgram->getRows($sql);

$affUrlKeyWords = array();
foreach ($aff_tmp as $vaff)
{
    if(!empty($vaff['AffiliateUrlKeywords']))
    {
        $tmp_arr = explode(PHP_EOL, $vaff["AffiliateUrlKeywords"]);
        if(!empty($tmp_arr)){
            foreach ($tmp_arr as $affUrlKy){
                $affUrlKeyWords[$vaff['ID']]['affurl'][] = $affUrlKy;
            }
        }
        $tmp_arr_ky2 = explode(PHP_EOL, $vaff["AffiliateUrlKeywords2"]);
        if(!empty($tmp_arr_ky2)){
            foreach ($tmp_arr_ky2 as $affUrlKy2){
                $affUrlKeyWords[$vaff['ID']]['affKeyword2'][] = $affUrlKy2;
            }
        }
        $affUrlKeyWords[$vaff['ID']]['isactive'] = $vaff['IsActive'];
        $domainKey = array();
        if($vaff['Domain']){
            $domainArr = parse_url($vaff['Domain']);
            $domainHost = explode('.', $domainArr['host']);
            if($domainHost[0] == 'www'){
                $domainKey[] = $domainHost[1].'.'.$domainHost[2];
            }
            else{
                $domainKey[] = $domainHost[0].'.'.$domainHost[1];
            }
            
            if($vaff['ID'] == 6){
                $domainKey[] = 'pepperjammanagement.com';
            }
            if($vaff['ID'] == 10 || $vaff['ID'] == 2034){
                $domainKey[] = 'awin.com';
            }
            //print_r($matches);exit;
        }
        $affUrlKeyWords[$vaff['ID']]['domainKey'] = $domainKey;
        
    }
}

//print_r($affUrlKeyWords);exit;
//获取返回的有效promotion Patrick
//$getDate = date('Y-m-d',time());
$getDate = date('Y-m-d',strtotime("-1day"));
define('CRUL_NAME', 'mg_comm_user');
define('CRUL_PASSWORD', 'mg_comm_user');
echo $getDate;
//$getDate = '2017-06-01';
//do{   
    
    $url = "http://us-bcg.bwe.io/?act=emailpromo&date=$getDate";
    $http_data['file_temp'] = DATA_ROOT.'correctFeed/email'. date('Ymd',strtotime($getDate)).'.dat';
    //$http_data['file_temp'] = './aff_br_data_20170405.txt';
    
    _http($url,$http_data);
    
    
    
    //读文件
    $fp = fopen($http_data['file_temp'], 'r');
    $links = array();
    $istitle = 0;
    while (!feof($fp)) {
    
        $lr = explode("\t",trim(fgets($fp)));
        /*
         [0] => Country
         [1] => CouponID
         [2] => Merchant Name
         [3] => Merchant Origianl Url
         [4] => Title
         [5] => Description
         [6] => StartTime
         [7] => ExpireTime / RemindDate
         [8] => AffUrl
         [9] => DestUrl
         [10] => Code
        */
    
        $istitle ++;
        if($istitle == 1) continue;
        
        if($lr[0]){
            $data = array();
            $data['Country'] = isset($lr[0]) ? $lr[0] : '';
            $data['CouponID'] = isset($lr[1]) ? $lr[1] : '';
            $data['Merchant'] = isset($lr[2]) ? $lr[2] : '';
            $data['Merchant_Originanl_Url'] = isset($lr[3]) ? $lr[3] : '';
            $data['Title'] = isset($lr[4]) ? $lr[4] : '';
            $data['Description'] = isset($lr[5]) ? $lr[5] : '';
            $data['StartTime'] = isset($lr[6]) ? $lr[6] : '';
            $data['ExpireTime'] = isset($lr[7]) ? $lr[7] : '';
            $data['AffUrl'] = isset($lr[8]) ? $lr[8] : '';
            $data['DestUrl'] = isset($lr[9]) ? $lr[9] : '';
            $data['Code'] = isset($lr[10]) ? $lr[10] : '';
            $data['affMerchantStr'] = isset($lr[11]) ? $lr[11] : '';
            $data['SenderAddr'] = isset($lr[12]) ? $lr[12] : '';
            $data['FileDate'] = $getDate;
            $data['programId'] = 0;
            $data['MatchesSenderAddr'] = '';
            
            //echo $data['affMerchantStr'];exit;
            
            //根据affurl分析affid
            $affId = array();
            //$find  = false;
            if($data['AffUrl']){
                foreach ($affUrlKeyWords as $affIdKey=>$affKy){
                    foreach ($affKy['affurl'] as $affKyValue){
                        $affKyValue = trim($affKyValue);
                        if(!preg_match('/\S+\.\S+/', $affKyValue)){
                            continue;
                        }
                        if(stripos($data['AffUrl'],$affKyValue)){
                            //if($affKy['isactive'] == 'YES'){
                                $affId[$affIdKey] = $affIdKey;
                                //$find = TRUE;
                            //}else
                            //    $affId[$affIdKey] = $affIdKey;
                        }
                    }
                    //if($find) break;
                }
            }
            
            //根据SenderAddr分析affid
            if(empty($affId)){
                foreach ($affUrlKeyWords as $affIdKey=>$affKy){
                
                    foreach ($affKy['affurl'] as $affKyValue){
                        if(stripos($data['SenderAddr'],$affKyValue) !== FALSE){
                            $data['MatchesSenderAddr'] .= $affIdKey.':'.$affKyValue.'|';
                            $affId[$affIdKey] = $affIdKey;
                        }
                    }
                
                    foreach ($affKy['domainKey'] as $affDKyValue){
                        if(stripos($data['SenderAddr'],$affDKyValue) !== FALSE){
                            $data['MatchesSenderAddr'] .= $affIdKey.':'.$affDKyValue.'|';
                            $affId[$affIdKey] = $affIdKey;
                        }
                    }
                }
            }
            
            
            
            
            //分析affMerchantStr
            $affMerchantStr = $data['affMerchantStr'];
            $affMerchantArr = array();
            if($affMerchantStr){
                $affMerchantTemp =  explode('|',$affMerchantStr);
                if(isset($affMerchantTemp[0])){
                    if(preg_match('/1|2-(\d+)-(\d+)/',$affMerchantTemp[0],$matches)){
                        //	  print_r($matches);exit;
                        $affMerchantArr[] = $matches[1].'-'.$matches[2];
                        foreach($affMerchantTemp as $amtKey=>$amt){
                            if($amtKey == 0) continue;
                            $affMerchantArr[] = $amt;
                        }
                    }
                }
            }
            //有正常的对应关系，去系统找是否存在。
            if($affMerchantArr){
                $matchesNum = 0;
                 
                $data['MatchesStr'] = '';
                foreach ($affMerchantArr as $amr){
                    $tempAmr = explode('-', $amr);
                    $tempAffid = $tempAmr[0];
                    $tempAffMerchantId = $tempAmr[1];
                    
                    if($affId){
                        if(in_array($tempAffid, $affId)){
                            $sql = "select id from program where affid = $tempAffid and idinaff = '{$tempAffMerchantId}' limit 1";
                            $programInfo = $objProgram->getFirstRow($sql);
                            if($programInfo){
                            
                                $data['MatchesStr'] = $amr.'|';
                                $data['Affid'] =  $tempAffid;
                                $data['AffMerchantId'] =  $tempAffMerchantId;
                                $data['programId'] =  $programInfo['id'];
                                $links[] = $data;
                                $matchesNum ++;
                            }
                        }
                    }
                    else{
                        //可能有多组关系。就是能匹配多个programid
                        $sql = "select id from program where affid = $tempAffid and idinaff = '{$tempAffMerchantId}' limit 1";
                        $programInfo = $objProgram->getFirstRow($sql);
                        if($programInfo){
                        
                            $data['MatchesStr'] = $amr.'|';
                            $data['Affid'] =  $tempAffid;
                            $data['AffMerchantId'] =  $tempAffMerchantId;
                            $data['programId'] =  $programInfo['id'];
                        
                            $links[] = $data;
                            $matchesNum ++;
                        }
                    }
                }
                
                if($matchesNum == 0){
                    $links[] = $data;
                }
                
            }else{
                $links[] = $data;
            }
            
            if(count($links)>=100){
                update_promotion($links);
                //print_r($links);exit;
                $links = array();
            }
        }
    }
    fclose($fp);
    if(count($links)>0){
        update_promotion($links);
    }
    
    //$getDate = date('Y-m-d',strtotime("$getDate+1day"));

//}while ($getDate<date('Y-m-d'));


//$sql = "update affiliate_email_promo set IsActive = 'NO' WHERE LastUpdateTime < '{$nowDay}' AND LastUpdateTime != '0000-00-00 00:00:00' AND  sourcetype = 'normal'";
//echo $sql.PHP_EOL;
//$_objPendingMysql->query($sql);

function update_promotion($data){
    //global $_db;
    global $_objPendingMysql;
    $i = 0;
    $j = 0;
    foreach ($data as $v){
        
        
        $Sql = "SELECT ID FROM affiliate_email_promo WHERE Country = '{$v['Country']}' AND CouponID = '".addslashes($v['CouponID'])."' AND programId = '{$v['programId']}'";
        $ePArr = $_objPendingMysql->getRows($Sql);
        if(!empty($ePArr)){
            $updateArr = array();
            foreach ($v as $k1=>$v1){
                $updateArr[] = "`$k1` = '".addslashes($v1)."'";
            }
            $updateArr[] = "`LastUpdateTime` = '".date('Y-m-d H:i:s')."'";
            $updateSql = "update affiliate_email_promo SET ".implode(',', $updateArr)."  WHERE Country = '{$v['Country']}' AND CouponID = '".addslashes($v['CouponID'])."' AND programId = '{$v['programId']}'";
            
            $_objPendingMysql->query($updateSql);
            $i++;
        }else{
            $insert_col = array();
            $insert_value = array();
            foreach ($v as $k2=>$v2){
                $insert_col[] = $k2;
                $insert_value[] = "'".addslashes($v2)."'";
            }
            $insert_col[] = 'AddTime';
            $insert_value[] = "'".date('Y-m-d H:i:s')."'";
            $insertSql = "insert into affiliate_email_promo (".implode(',', $insert_col).") values ( ".implode(',', $insert_value)." )";
            $_objPendingMysql->query($insertSql);
        }
        $j++;
    }
    
    echo "all total count:$j====>update count:$i\r\n";
    return;
}

echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";
exit;




function _http($url,$data=array(),$return=false){
     
    $file_temp =  $data['file_temp'];
    $fw = fopen($file_temp, 'w+');

    print_r("curl :".$url."\n");
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER         , false);
    curl_setopt($ch, CURLOPT_NOBODY         , false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER , true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION , true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
    curl_setopt($ch, CURLOPT_USERAGENT      , 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2');
    curl_setopt($ch, CURLOPT_FILE           , $fw);
    curl_setopt($ch, CURLOPT_REFERER        , $url);
    curl_setopt($ch, CURLOPT_TIMEOUT        , 300);
    curl_setopt($ch, CURLOPT_USERPWD, CRUL_NAME . ":" . CRUL_PASSWORD);
    
    if(isset($data['postdata']) && !empty($data['postdata'])){
        $post_query = http_build_query($data['postdata']);
        curl_setopt($ch, CURLOPT_POST , true);
        curl_setopt($ch, CURLOPT_POSTFIELDS , $post_query);
        print_r("curl_post :".$post_query."\n");
    }

    if(isset($data['headers']) && !empty($data['headers'])){
        curl_setopt($ch, CURLOPT_HTTPHEADER, $data['headers']);
    }

    $rs = curl_exec($ch);
    curl_close($ch);
    //var_dump($rs);
    fclose($fw);

    if($return){
        return file_get_contents($file_temp);
    }else{
        return $return;
    }
}

?>
