 <?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
echo "<< Start @ ".date("Y-m-d H:i:s")." >>\r\n";
$objProgram = New ProgramDb();


//$sql = "SELECT ID,AffId,StatusInAff,Partnership,AddTime FROM program WHERE id = 9";
$sql = "SELECT ID,AffId,StatusInAff,Partnership,AddTime FROM program";
$programArr = $objProgram->objMysql->getRows($sql);

//每次跑一条数据。
$endTime   = date('Y-m-d H:i:s',time());
$startTime = date('Y-m-d H:i:s',time()- 2*24*60*60);

foreach ($programArr as $value){
    
   $sql = "SELECT ID,FieldName,FieldValueOld,FieldValueNew,AddTime,LastUpdateTime FROM `program_change_log` 
            WHERE affid = {$value['AffId']} AND programid = {$value['ID']} AND fieldname IN ('statusinaff', 'partnership') AND LastUpdateTime >= '{$startTime}' AND LastUpdateTime <= '{$endTime}'
            Order by ID asc";
    
   
    //echo $sql;exit;
    $logArr = $objProgram->objMysql->getRows($sql);
    $data = array();
     
    //有change过。分析change_program_log
    if($logArr){
        
        //确定第一次
        foreach ($logArr as $logValue){
        
             if(!isset($StatusInAff) && $logValue['FieldName'] == 'StatusInAff'){
                 $StatusInAff = $logValue['FieldValueOld'];
             } 
             if(!isset($Partnership) && $logValue['FieldName'] == 'Partnership'){
                 $Partnership = $logValue['FieldValueOld'];
             } 
        }
        
        $StatusInAffFirst = isset($StatusInAff) ? $StatusInAff : $value['StatusInAff'];
        $PartnershipFirst = isset($Partnership) ? $Partnership : $value['Partnership'];
        unset($StatusInAff);
        unset($Partnership);

        //分析log
        foreach ($logArr as $logValue){
            
            if($logValue['FieldName'] == 'StatusInAff'){
                $StatusInAffTemp = $logValue['FieldValueNew'];
            }
            if($logValue['FieldName'] == 'Partnership'){
                $PartnershipTemp = $logValue['FieldValueNew'];
            }
            
            
            $StatusInAffLog = isset($StatusInAffTemp) ? $StatusInAffTemp : $StatusInAffFirst;
            $PartnershipLog = isset($PartnershipTemp) ? $PartnershipTemp : $PartnershipFirst;
            
            
            $data[] = array(
                'LogId'=>$logValue['ID'],
                'ProgramId'=>$value['ID'],
                'AffId'=>$value['AffId'],
                'IsActive' => $StatusInAffLog == 'Active' && $PartnershipLog == 'Active' ? 'YES':'NO',
                'StatusInAff'=>$StatusInAffLog,
                'Partnership'=>$PartnershipLog,
                'Time' => $logValue['LastUpdateTime'],
            );
        }
        unset($StatusInAffTemp);
        unset($PartnershipTemp);
         
    }else{ //没有change，并且analyze_status_change_log ，program 表里，addtime
        $sql = "SELECT count(*) as count FROM analyze_status_change_log WHERE programid = {$value['ID']}";
        $cnt = $objProgram->objMysql->getFirstRowColumn($sql);
        if(!$cnt){
            $data[] = array(
                'LogId'=>0,
                'ProgramId'=>$value['ID'],
                'AffId'=>$value['AffId'],
                'IsActive' =>$value['StatusInAff'] == 'Active' && $value['Partnership'] == 'Active' ? 'YES':'NO',
                'StatusInAff'=>$value['StatusInAff'],
                'Partnership'=>$value['Partnership'],
                'Time' => $value['AddTime'],
            );
        }
    }
    //print_r($data);exit;
    inset_log_data($data);
    
}

echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";
exit;

function inset_log_data($data){
    
    global $objProgram;
    foreach ($data AS $dataValue){
        
        $column_keys = array("LogId","ProgramId","AffId","IsActive","StatusInAff","Partnership","`Time`");
        foreach ($dataValue as $tk=>$tv){
            $tmp_insert[] = addslashes($tv);
            $tmp_update[] = "`$tk` = '".addslashes($tv)."'";
        }
        
        $insertSql = "INSERT INTO analyze_status_change_log (".implode(",", $column_keys).") VALUES ('".implode("','", $tmp_insert)."') ON DUPLICATE KEY UPDATE " . implode(",", $tmp_update) . ";";
        //echo $insertSql;exit;
        $objProgram->objMysql->query($insertSql);
        
        unset($tmp_insert);
        unset($tmp_update);
    }
    
    
}

?>