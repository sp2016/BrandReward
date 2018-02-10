<?php
//分析crawl_script_run_log
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");

$objProgram = New ProgramDb();
echo "<< Start @ ".date("Y-m-d H:i:s")." >>\r\n";





$date = date('Y-m-d',time());
$sql = "SELECT * FROM crawl_script_run_log WHERE `date` >= '2017-04-25' and platform = 'BR' and `status` = 'finish' and (method = 'getallfeeds' or method = 'getallpagelinks') and analyze_flag=0";
//$sql ="SELECT * FROM crawl_script_run_log WHERE `date` = '2017-04-23' AND platform = 'BR' AND `status` = 'finish' AND (method = 'getallfeeds' ) AND affid = 15 and analyze_flag=0";
$logData = $objProgram->objMysql->getRows($sql);


//getallfeeds and getallpagelinks analyze
foreach ($logData as $value){
    
    $table = 'affiliate_links_'.$value['affid'];
    
    $exist = $objProgram->objPendingMysql->isTableExisting($table);
    if(!$exist) continue;
    
    $sql = "select count(*) as count from $table  WHERE lastupdatetime >='{$value['startTime']}' AND LastUpdateTime <='{$value['endTime']}' AND isactive = 'YES'";
    
    $updateCount = $objProgram->objPendingMysql->getFirstRow($sql);
    $updateCount = $updateCount['count'];
    
    $sql = "select count(*) as count from $table  WHERE LinkAddTime >='{$value['startTime']}' AND LinkAddTime <='{$value['endTime']}'";
    $newCount = $objProgram->objPendingMysql->getFirstRow($sql);
    $newCount = $newCount['count'];
    
    $sql = "select count(*) as count from $table  WHERE LastChangeTime >='{$value['startTime']}' AND LastChangeTime <='{$value['endTime']}' AND isactive = 'NO'";
    $toInactiveCount = $objProgram->objPendingMysql->getFirstRow($sql);
    $toInactiveCount = $toInactiveCount['count'];
    
    $total = $updateCount + $newCount;
    $updateSql = "update crawl_script_run_log set `analyze_flag`= 1,`total` = $total,`new` = $newCount,`toInactive` = $toInactiveCount where id = ".$value['id'];
    $objProgram->objMysql->query($updateSql);
    
    echo $value['affid'].'--'.$value['method'].'---'.$value['date']."\r\n";
}


//crawl getproduct analyze
$sql = "SELECT * FROM crawl_script_run_log WHERE `date` >= '2017-04-25' and platform = 'BR' and `status` = 'finish' and (method = 'getproduct') and analyze_flag=0";
$logData = $objProgram->objMysql->getRows($sql);
foreach ($logData as $value){

    $table = 'affiliate_product_'.$value['affid'];

    $exist = $objProgram->objPendingMysql->isTableExisting($table);
    if(!$exist) continue;

    $sql = "select count(*) as count from $table  WHERE lastupdatetime >='{$value['startTime']}' AND LastUpdateTime <='{$value['endTime']}' AND isactive = 'YES'";

    $updateCount = $objProgram->objPendingMysql->getFirstRow($sql);
    $updateCount = $updateCount['count'];

    $sql = "select count(*) as count from $table  WHERE AddTime >='{$value['startTime']}' AND AddTime <='{$value['endTime']}'";
    $newCount = $objProgram->objPendingMysql->getFirstRow($sql);
    $newCount = $newCount['count'];

    $sql = "select count(*) as count from $table  WHERE LastChangeTime >='{$value['startTime']}' AND LastChangeTime <='{$value['endTime']}' AND isactive = 'NO'";
    $toInactiveCount = $objProgram->objPendingMysql->getFirstRow($sql);
    $toInactiveCount = $toInactiveCount['count'];

    $total = $updateCount + $newCount;
    $updateSql = "update crawl_script_run_log set `analyze_flag`= 1,`total` = $total,`new` = $newCount,`toInactive` = $toInactiveCount where id = ".$value['id'];
    $objProgram->objMysql->query($updateSql);

    echo $value['affid'].'--'.$value['method'].'---'.$value['date']."\r\n";
}


//getprogram analyeze
$sql ="SELECT * FROM crawl_script_run_log WHERE `date` >= '2017-04-25' AND platform = 'BR' AND `status` = 'finish' AND (method = 'getprogram' ) AND  analyze_flag=0";
$logData = $objProgram->objMysql->getRows($sql);

foreach ($logData as $v1){
    
    //new
    $newcount = 0;
    $newStr = '';
    $newArr = array();
    $sql = "SELECT a.ID,a.Name FROM
        program a
        WHERE a.statusinaff = 'active' AND a.partnership = 'active' AND a.ADDTIME > '".$v1['startTime']."' AND a.AddTime <= '".$v1['endTime']."' and a.affid = {$v1['affid']}";
    
    $changelogArrNew = $objProgram->objMysql->getRows($sql);
    if($changelogArrNew){
        foreach ($changelogArrNew as $ll){
            $newArr[$ll['ID']] = addslashes($ll['Name']);
        }
        
        $newcount = count($newArr);
        $newStr = serialize($newArr);
    }
    
    //update
    $changetoUpArr = array();
    $updatecount = 0;
    $updateStr = '';
    $sql = "select b.ID,b.Name FROM
        program_change_log a 
        inner join program b on a.programid = b.id
        where a.AddTime >= '".$v1['startTime']."' AND a.AddTime <= '".$v1['endTime']."' 
            AND (a.FieldName = 'Partnership' OR a.FieldName = 'StatusInAff') AND a.FieldValueNew = 'Active' and a.affid = {$v1['affid']}";
    
    $changelogArrUpdate = $objProgram->objMysql->getRows($sql);
    if($changelogArrUpdate){
        foreach ($changelogArrUpdate as $vlu){
            $changetoUpArr[$vlu['ID']] = addslashes($vlu['Name']);
        }
        $updatecount = count($changetoUpArr);
        $updateStr = serialize($changetoUpArr);
    }
    
    
   
    
    
    //toinActive
    $storeOffcount = 0;
    $changetoNPArr = array();
    $toInctive = 0;
    $toInctiveStr = '';
    $sql = "select b.ID,b.Name FROM
        program_change_log a
        inner join program b on a.programid = b.id
        where a.AddTime >= '".$v1['startTime']."' AND a.AddTime <= '".$v1['endTime']."'
            AND (a.FieldName = 'Partnership' OR a.FieldName = 'StatusInAff') AND a.FieldValueNew != 'Active' AND a.FieldValueOld = 'Active' and b.affid = {$v1['affid']}";
    
    $changelogArr1 = $objProgram->objMysql->getRows($sql);
    if($changelogArr1){
        foreach ($changelogArr1 as $vnl){
            $changetoNPArr[$vnl['ID']] = addslashes($vnl['Name']);
        }
        
        $toInctive = count($changetoNPArr);
        
        
        //store off
        $storeOffcount = 0;
        foreach ($changetoNPArr as $sokey=>$sovalue){
        
            $sql = "SELECT b.StoreId FROM   r_domain_program a LEFT JOIN r_store_domain b ON a.did = b.domainid WHERE a.pid = $sokey";
            //echo $sql.PHP_EOL;
            $storeInfo = $objProgram->objMysql->getFirstRow($sql);
            $storeid   = $storeInfo['StoreId'];
        
            if($storeid){
                $sql = "SELECT c.StatusInAff,c.Partnership FROM r_store_domain a INNER JOIN r_domain_program b ON a.domainid = b.did INNER JOIN program c ON b.pid = c.id
                WHERE a.storeid = $storeid AND c.StatusInAff = 'Active' AND c.Partnership = 'Active'";
                $programstoreInfo = $objProgram->objMysql->getFirstRow($sql);
                if(empty($programstoreInfo)){
                    $storeOffcount ++;
                    $changetoNPArr[$sokey] = $sovalue."|Off";
                }else{
                    $changetoNPArr[$sokey] = $sovalue."|";
                }
            }else{
                $changetoNPArr[$sokey] = $sovalue."|";
            }
            
        }
        
        $toInctiveStr = serialize($changetoNPArr);
    }
    
    
    
    
    
    //notfound
    $notfoundcount = 0;
    $notfoundStr = '';
    $changetoArrnotfound = array();
    $sql = "SELECT a.ID,a.Name FROM
        program a
        WHERE  a.LastUpdateTime > '".$v1['startTime']."' AND a.LastUpdateTime <= '".$v1['endTime']."' and StatusInAff = 'Offline' and a.affid = {$v1['affid']}";
    $changelogArrNotFound = $objProgram->objMysql->getRows($sql);
    if($changelogArrNotFound){
        foreach ($changelogArrNotFound as $vnl2){
            $changetoArrnotfound[$vnl['ID']] = addslashes($vnl2['Name']);
        }
        $notfoundcount = count($changetoArrnotfound);
        $notfoundStr = serialize($changetoArrnotfound);
    }
    
    $total = $newcount+$updatecount;
    $updateSql = "update crawl_script_run_log set `analyze_flag`= 1,`total` = $total,`new` = $newcount,`ext1`='{$newStr}',`update`=$updatecount,`ext2`='{$updateStr}',`notfound`=$notfoundcount,`ext3`='{$notfoundStr}',`toInactive`=$toInctive,`ext4`='{$toInctiveStr}',`storeOffcount`=$storeOffcount  where id = ".$v1['id'];
    $objProgram->objMysql->query($updateSql);
    
}


//crawl transaction
$sql = "SELECT a.id,b.alias FROM crawl_script_run_log a left join wf_aff b on a.affid = b.id WHERE a.`date` >= '2017-05-09' and a.platform = 'BR' and a.`status` = 'finish' and a.method = 'transactionCrawl' and a.analyze_flag=0";
$logData = $objProgram->objMysql->getRows($sql);
foreach ($logData as $vct){
    //$dir = 'E:/wamp/www/mlink/server_transaction_mlink/data/'.$vct['alias'].'/';
    $dir = '/home/bdg/transaction/server_transaction/data/'.$vct['alias'].'/';
    if(!is_dir($dir)) continue;
    $updateflag = 0;
    //echo $dir.PHP_EOL;
    $file = scandir($dir);
    $unknownSum = 0;
    foreach ($file as $ctfile){
        if($ctfile == '.' || $ctfile == '..')
            continue;
        $suffix = substr(strrchr($ctfile, '.'), 1);
        if($suffix == 'upd'){
            $updateflag ++;
            $filePath = $dir.$ctfile;
            //echo $filePath.PHP_EOL;
            $fp = fopen($filePath, 'r');
            while (!feof($fp)) {
                $tempArr = explode("\t",trim(fgets($fp)));
                if($tempArr[0]){
                    $sid = trim($tempArr[6]);
                    if(empty($sid)){
                        //echo $tempArr[3].PHP_EOL;
                        $unknownSum += $tempArr[3];
                    }
                }
            }
            fclose($fp);
        }
    }
    if($updateflag > 10){
        $updateSql = "update crawl_script_run_log set `analyze_flag`= 1,`total` = $unknownSum where id = ".$vct['id'];
        $objProgram->objMysql->query($updateSql);
    }
    
     
}


echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";

?>
