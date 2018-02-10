<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
$objProgram = New ProgramDb();
$_db = $objProgram->objMysql;
$_objPendingMysql = $objProgram->objPendingMysql;

//获取所有links的 新增跟更新的links
$length = 1000; //每次取100条

echo "<< Start @ ".date("Y-m-d H:i:s")." >>\r\n";

$sql = "select ID from wf_aff where IsActive = 'YES'";
$affArr = $_db->getRows($sql);

$tables = array();
foreach ($affArr as $v){
    $exist = $_objPendingMysql->isTableExisting('affiliate_links_'.$v['ID']);
    if($exist)
        $tables[$v['ID']] = 'affiliate_links_'.$v['ID'];
}


// affiliate_links_\d 的所有表
$nowDay = date('Y-m-d H:i:s',time());
$endTime   = date('Y-m-d H:i:s',time());
$startTime = date('Y-m-d H:i:s',time()-20*60);

//$startTime = date('Y-m-d 00:00:00',time());
//$endTime = date('Y-m-d 00:00:00',strtotime('+1 day'));


$column_keys = array("affid","ProgramId","PidInaff","AffLinkId","`LinkAddTime`", "`LastUpdateTime`", "`LastChangeTime`","IsActive","IsPromotion","ScriptTime","`language`");
foreach ($tables as $key=>$value){
    echo $value;
    $sql = "select id,idinaff from program where affid = $key";
    $programInfo =  $_db->getRows($sql);
    $programByKey = array();
    foreach ($programInfo as $program){
        $programByKey[$key.'_'.$program['idinaff']]['id'] = $program['id'];
    }
    
    
    $i = 0;
    $j = 0;
    do{
        $offset = $length*$i- 1 > 0 ? $length*$i- 1 : 0;
        $LinkPromoType = "'coupon','DEAL','free shipping','N/A','link','deeplink'";
        $onlyPromotion = array(360,15,115,1,2,7,6,10,52,65,427,12,5,35,415,429,469,667,26,63,500,46,152,679,240,163,29,22,64,28,49,124,197,20,2034);
        $where = '';
        if(in_array($key,$onlyPromotion)){
            if($key==28){
                $where = '';
            }else{
                $where = " AND `Type` = 'promotion'";
            }
        }
        
        /*$sql = "select * from ".$value." where LinkPromoType in (".$LinkPromoType.") $where
                AND ( (LinkAddTime >= '$startTime' AND LinkAddTime < '$endTime') OR (LastChangeTime >= '$startTime' and LastChangeTime < '$endTime') )
                GROUP BY AffMerchantId,LinkName,LinkDesc,LinkStartDate,LinkEndDate limit $offset, $length";*/
        
        $data = array();
        if($key==2){ //Links Share 特殊处理。
                 $sql = "select * from ".$value." where 
                 ( (LinkAddTime >= '$startTime' AND LinkAddTime <= '$endTime') OR (LastChangeTime >= '$startTime' and LastChangeTime <= '$endTime') )
                      limit $offset,$length ";
                 
                 $dataLs = $_objPendingMysql->getRows($sql);
                 
                 foreach($dataLs as $valueLs){
                     if($valueLs['Type'] =='link' && $valueLs['IsActive']='yes' && $valueLs['LinkPromoType'] != 'banner'){
                         $endLs = strtotime($valueLs['LinkEndDate']);
                         $startLs = strtotime($valueLs['LinkStartDate']);
                         $dayLs =  ($endLs - $startLs)/86400;
                         if($dayLs<=60){
                             $data[]= $valueLs;
                         }
                     }else{
                         $data[] = $valueLs;
                     }
                 }
        }
        else{
            $sql = "select * from ".$value." where LinkPromoType in (".$LinkPromoType.") $where
                AND ( (LinkAddTime >= '$startTime' AND LinkAddTime < '$endTime') OR (LastChangeTime >= '$startTime' and LastChangeTime < '$endTime') )
                limit $offset, $length";
            $data = $_objPendingMysql->getRows($sql);
        }         
        
        
        
        //$sql = "select * from ".$value." where LinkPromoType in (".$LinkPromoType.") and isactive = 'YES'  GROUP BY AffMerchantId,LinkName,LinkDesc,LinkStartDate,LinkEndDate limit $offset, $length";
        
        //echo count($data).PHP_EOL;
        
         
        $i++;
        foreach ($data as $v){
            
            if($key == 2 || $key == 28){
                
            }else{
                if(in_array($key,$onlyPromotion)  && $v['Type'] != 'promotion'){
                    continue;
                }
            }
            
            
            if(empty($v['LinkAffUrl'])) continue;
             
            if(empty($programByKey[$key.'_'.$v['AffMerchantId']])) {
                //echo " no program {$v['AffMerchantId']}\n";
                continue;
            }
            $language = 'en'; 
            if($key == 1){
                if($v['Language'] == 'French')
                    $language = 'fr';
                elseif($v['Language'] == 'German')
                    $language = 'de';
                elseif($v['Language'] == 'Russian')
                    $language = 'ru';
            }
            
            $tmp_data = array(
                'affid'=> $key,
                'ProgramId' => $programByKey[$key.'_'.$v['AffMerchantId']]['id'],
                'PidInaff' => $v['AffMerchantId'],
                'AffLinkId' => $v['AffLinkId'],
                'LinkAddTime' => $v['LinkAddTime'],
                'LastUpdateTime' => $v['LastUpdateTime'],
                'LastChangeTime' => $v['LastChangeTime'],
                'IsActive' => $v['IsActive'],
                'IsPromotion' => in_array($key,$onlyPromotion) ? 'YES' : 'NO',
                'ScriptTime' => $nowDay,
                '`language`' => $language,
            );
            
            foreach ($tmp_data as $tk=>$tv){
                
                $tmp_insert[] = addslashes($tv);
                if($tk != 'IsPromotion'){
                    $tmp_update[] = "$tk = '".addslashes($tv)."'";
                }
            }
            
            $insertSql = "INSERT INTO affiliate_links_all_simple (".implode(",", $column_keys).") VALUES ('".implode("','", $tmp_insert)."') ON DUPLICATE KEY UPDATE " . implode(",", $tmp_update) . ";";
            $_db->query($insertSql);
            $j++;
            unset($tmp_insert);
            unset($tmp_update);
        }
       
        if($key == 2){
            $countPor = count($dataLs);
        }else{
            $countPor = count($data);
        }
         
    }while($countPor>0);
    
    echo " count:(".$j.")\r\n";
}

echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";
exit;


?>
