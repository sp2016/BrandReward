<?php
global $_cf,$_req,$_db,$_objPendingMysql;
echo "<< Start @ ".date("Y-m-d H:i:s")." >>\r\n";


$nowDay = date('Y-m-d H:i:s',time());
$objProgram = new Mysql(DB_NAME, DB_HOST, DB_USER, DB_PASS);
$_objPendingMysql = new Mysql(PENDING_DB_NAME, PENDING_DB_HOST, PENDING_DB_USER, PENDING_DB_PASS);
$sql_names_set = 'SET NAMES latin1';
$_objPendingMysql->query($sql_names_set);
define('CRUL_NAME', 'mg_comm_user');
define('CRUL_PASSWORD', 'mg_comm_user');

//AffiliateUrlKeywords
$sql = "SELECT ID,AffiliateUrlKeywords,AffiliateUrlKeywords2,IsActive FROM wf_aff WHERE IsInHouse='NO'";
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
    }
}
//print_r($affUrlKeyWords);

//获取返回的有效promotion Patrick
//$getDate = date('Y-m-d',time());
//date('Y-m-d',strtotime("-7 day"));

//$getDate = '2017-09-11';
$getDate = date('Y-m-d',strtotime("-2 day"));

echo $getDate.'\r\n';
do{   
    
    $url = "http://us-bcg.bwe.io/?act=emailpromo&date=$getDate&status=duplicate";
    $http_data['file_temp'] = DATA_ROOT.'correctFeed/email_duplicate'. date('Ymd',strtotime($getDate)).'.dat';
     
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
        //if($istitle == 1) continue;
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
            $data['FileDate'] = $getDate;
            $data['programId'] = 0;
            $data['SourceType'] = 'duplicate';
            if(!$data['AffUrl']) continue; 
            
            $affId = 0;
            $fixAffUrl = '';
            $find  = false;
            if($data['AffUrl']){
                foreach ($affUrlKeyWords as $affIdKey=>$affKy){
                    foreach ($affKy['affurl'] as $affKyValue){
                        $affKyValue = trim($affKyValue);
                        if(!preg_match('/\S+\.\S+/', $affKyValue)){
                            continue;
                        }
                        if(stripos($data['AffUrl'],$affKyValue)){
                            if($affKy['isactive'] == 'YES'){
                                $affId = $affIdKey;
                                $find = TRUE;
                            }else 
                                $affId = $affIdKey;
                        }
                    }
                    if($find) break;
                }
            }
            //找到affid 替换参数， 否则跳过这条
            if($affId){
                $fixAffUrl = replace_Affurl($affId,$data['AffUrl'],$affUrlKeyWords);
            }
            if(!$fixAffUrl){
                
                continue;
            }
            
            $data['AffUrl'] = $fixAffUrl;
            $data['Affid'] = $affId;
            
            
            //分析affMerchantStr
            $affMerchantStr = $data['affMerchantStr'];
            $affMerchantArr = array();
            if($affMerchantStr){
                $affMerchantTemp =  explode('|',$affMerchantStr);
                if(isset($affMerchantTemp[0])){
                    //print_r($affMerchantTemp);
                    if(preg_match('/(1|2)-(\d+)-(\d+)/',$affMerchantTemp[0],$matches)){
                         
                        $affMerchantArr[] = $matches[2].'-'.$matches[3];
                        foreach($affMerchantTemp as $amtKey=>$amt){
                            if($amtKey == 0) continue;
                            $affMerchantArr[] = $amt;
                        }
                    }
                }
            }
            //有正常的对应关系，去系统找是否存在。
            if($affMerchantArr){
                $affidFlag = false;
                foreach ($affMerchantArr as $amrf){
                
                    $tempAmr = explode('-', $amrf);
                    $tempAffid = $tempAmr[0];
                    if($affId && $tempAffid == $affId){
                        $affidFlag = true;
                    }
                
                }
                $data['MatchesStr'] = '';
                foreach ($affMerchantArr as $amr){
                    $tempAmr = explode('-', $amr);
                    $tempAffid = $tempAmr[0];
                    $tempAffMerchantId = $tempAmr[1];
                    if($affId && $tempAffid == $affId && $affidFlag){
                        $sql = "select id from program where affid = $tempAffid and idinaff = '{$tempAffMerchantId}' limit 1";
                        $programInfo = $objProgram->getFirstRow($sql);
                        if($programInfo){//找到了。
            
                            $data['MatchesStr'] = $amr.'|';
                            $data['Affid'] =  $tempAffid;
                            $data['AffMerchantId'] =  $tempAffMerchantId;
                            $data['programId'] =  $programInfo['id'];
                            break;
                        }
                    }elseif(!$affidFlag){
                        //可能有多组关系。就是能匹配多个programid
                        $sql = "select id from program where affid = $tempAffid and idinaff = '{$tempAffMerchantId}' limit 1";
                        $programInfo = $objProgram->getFirstRow($sql);
                        if($programInfo){
            
                            $data['MatchesStr'] = $amr.'|';
                            $data['Affid'] =  $tempAffid;
                            $data['AffMerchantId'] =  $tempAffMerchantId;
                            $data['programId'] =  $programInfo['id'];
            
                            $links[] = $data;
                        }
                    }
                }
            }
            
            
            $links[] = $data;
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
    
    $getDate = date('Y-m-d',strtotime("$getDate+1day"));

}while ($getDate<date('Y-m-d'));


//affid:1,2,115, 
function replace_Affurl($affId,$AffUrl,$affUrlKeyWords){
    
    $fixUrl = '';
    
    switch ($affId){
        case "1":
            //http://www.kqzyfj.com/click-8030429-13009624-1499888138000
            $affKeyword2 = trim($affUrlKeyWords[$affId]['affKeyword2'][0]);
            if(preg_match('/click-(\d)+-/i',$AffUrl)){
                $fixUrl = preg_replace('/click-(\d)+-/', 'click-'.$affKeyword2.'-',$AffUrl);
            }
            break;
        case "2":  
            //http://click.linksynergy.com/fs-BIN/click?id=pITOEqOhvpQ&offerid=518421.8040&TYPE=3&subid=0
            $affKeyword2 = trim($affUrlKeyWords[$affId]['affKeyword2'][0]);
            if(preg_match('/click\?id=[^\&]+\&/i',$AffUrl)){
                $fixUrl = preg_replace('/click\?id=[^\&]+\&/i', 'click?id='.$affKeyword2.'&',$AffUrl);
            }
            break;
        case "115":
            //https://t.cfjump.com/32460/o/30678
            $affKeyword2 = trim($affUrlKeyWords[$affId]['affKeyword2'][0]);
            if(preg_match('/\/\d+\/\w{1}\/\d+/i',$AffUrl)){
                $fixUrl = preg_replace('/\/\d+\/(\w{1})\//i', '/'.$affKeyword2.'/${1}/',$AffUrl);
            }
            break;
        case "10":    
            //http://www.awin1.com/awclick.php?awinaffid=311227&awinmid=3109&p=http://www.crocs.co.uk/crocs-bistro-pro-clog/15010,en_GB,pd.html?intid=workbanner
            $affKeyword2 = trim($affUrlKeyWords[$affId]['affKeyword2'][0]);
            if(preg_match('/awinaffid=\d+/i',$AffUrl)){
                $fixUrl = preg_replace('/awinaffid=\d+/i', 'awinaffid='.$affKeyword2,$AffUrl);
            }
            break;
        case "7":
            //http://shareasale.com/u.cfm?d=417774&m=66874&u=1418817&afftrack=
            $affKeyword2 = trim($affUrlKeyWords[$affId]['affKeyword2'][0]);
            if(preg_match('/u=\d+/i',$AffUrl)){
                $fixUrl = preg_replace('/u=\d+/i', 'u='.$affKeyword2,$AffUrl);
            }
            break;
        case "6":
            //http://www.pntra.com/t/2-301597-151725-150166
            $affKeyword2 = trim($affUrlKeyWords[$affId]['affKeyword2'][0]);
            if(preg_match('/-\d+-\d+-\d+/i',$AffUrl)){
                $fixUrl = preg_replace('/-(\d+)-(\d+)-(\d+)/i', '-${1}-'.$affKeyword2.'-${3}',$AffUrl);
            }
            break;
        case "28":
            //http://t.dgm-au.com/c/344776/377309/1397
            $affKeyword2 = trim($affUrlKeyWords[$affId]['affKeyword2'][0]);
            if(preg_match('/\/c\/\d+/i',$AffUrl)){
                $fixUrl = preg_replace('/\/c\/\d+/i', '/c/'.$affKeyword2,$AffUrl);
            }
            break;
        case "58":
            //http://mountainsteals.evyy.net/c/344780/375090/4515
            $affKeyword2 = trim($affUrlKeyWords[$affId]['affKeyword2'][0]);
            if(preg_match('/\/c\/\d+/i',$AffUrl)){
                $fixUrl = preg_replace('/\/c\/\d+/i', '/c/'.$affKeyword2,$AffUrl);
            }
            break;
        case "13":
            //http://track.webgains.com/click.html?wgcampaignid=207235&wgprogramid=11493&wgtarget=https://www.newfrog.com?utm_source=Webgains&utm_medium=affiliate&utm_campaign=clfathersday0609
            $affKeyword2 = trim($affUrlKeyWords[$affId]['affKeyword2'][0]);
            if(preg_match('/wgcampaignid=\d+/i',$AffUrl)){
                $fixUrl = preg_replace('/wgcampaignid=\d+/i', 'wgcampaignid='.$affKeyword2,$AffUrl);
            }
            break;
        case "22":
            //http://scripts.affiliatefuture.com/AFClick.asp?affiliateID=341940&merchantID=5127&ProgrammeID=13650&mediaID=0&tracking=&url=http://www.speckyfoureyes.com/89-summer-fun
            $affKeyword2 = trim($affUrlKeyWords[$affId]['affKeyword2'][0]);
            if(preg_match('/affiliateID=\d+/i',$AffUrl)){
                $fixUrl = preg_replace('/affiliateID=\d+/i',$affKeyword2,$AffUrl);
            }
            break;
        case "500":
            //http://clic.reussissonsensemble.fr/click.aspx?ref=790533&site=16228&TYPE=TEXT&tnb=5&diurl=http://www.mistergooddeal.com/nav/extra/LIST?seller=0&s=avis_desc&cat=515&context=generateur&mgdcid=aff_$ref$_
            $affKeyword2 = trim($affUrlKeyWords[$affId]['affKeyword2'][0]);
            if(preg_match('/ref=\d+/i',$AffUrl)){
                $fixUrl = preg_replace('/ref=\d+/i',$affKeyword2,$AffUrl);
            }
            break;
        case "52":
            //http://tc.tradetracker.net/?c=14629&m=1124927&a=263276&r=&u=
            $affKeyword2 = trim($affUrlKeyWords[$affId]['affKeyword2'][0]);
            if(preg_match('/a=\d+/i',$AffUrl)){
                $fixUrl = preg_replace('/a=\d+/i','a='.$affKeyword2,$AffUrl);
            }
            break;
            
         default:
                echo "not found this affid:$affId, please check!".PHP_EOL;
                mail('merlinxu@brandreward.com', 'Email Duplicate Alert', "not found this affid:$affId, please check!");
                //邮件报警
                
    }
    
    return $fixUrl;
}

function update_promotion($data){
    //global $_db;
    //$table = 'affiliate_email_promo_test';
    $table = 'affiliate_email_promo';
    global $_objPendingMysql;
    $i = 0;
    $j = 0;
    foreach ($data as $v){
        
        $Sql = "SELECT ID FROM $table WHERE Country = '{$v['Country']}' AND CouponID = '".addslashes($v['CouponID'])."' AND programId = '{$v['programId']}'";
        $ePArr = $_objPendingMysql->getRows($Sql);
        if(!empty($ePArr)){
            $updateArr = array();
            foreach ($v as $k1=>$v1){
                $updateArr[] = "`$k1` = '".addslashes($v1)."'";
            }
            $updateArr[] = "`LastUpdateTime` = '".date('Y-m-d H:i:s')."'";
            $updateSql = "update $table SET ".implode(',', $updateArr)."  WHERE Country = '{$v['Country']}' AND CouponID = '".addslashes($v['CouponID'])."' AND programId = '{$v['programId']}'";
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
            $insertSql = "insert into $table (".implode(',', $insert_col).") values ( ".implode(',', $insert_value)." )";
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
    var_dump($rs);
    fclose($fw);

    if($return){
        return file_get_contents($file_temp);
    }else{
        return $return;
    }
}

?>
