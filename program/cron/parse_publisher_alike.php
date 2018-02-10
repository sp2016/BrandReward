<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");


//分析相似的publisher。
$objProgram = New ProgramDb();
$sql_names_set = 'SET NAMES latin1';
$objProgram->objMysql->query($sql_names_set);

$sql = "select * from publisher_domain_whois  order by id asc";
$whoisArr = $objProgram->objMysql->getRows($sql);

$matchesArr = array();
foreach ($whoisArr as $value){
  
    $arr = array();
    $arr['ID'] = $value['ID'];
    $arr['publisherId'] = $value['publisherId'];
    
    if(preg_match_all('/<div class="df-label">(.*?)<\/div><div class="df-value">(.*?)<\/div>/', $value['administrativeContact'],$matches))
    {
        foreach ($matches[1] as $k=>$v){
            $arr[$v] = $matches[2][$k];
        }
    }
     
    $matchesArr[$value['ID']] = $arr;
}

$matID = array_keys($matchesArr);
foreach ($matID as $vID){
    $info = $matchesArr[$vID];
    
    foreach ($info as $infoKey=>$infoDetail){
        
        if($infoKey == 'ID' || $infoKey == 'publisherId' || $infoKey == 'Email:' || $infoKey == 'City:' || $infoKey == 'State:' || $infoKey == 'Country:'){
            continue;
        }else{
            
            foreach ($matID as $vSubID){
                if($vSubID == $vID) continue;
                
                if(isset($matchesArr[$vSubID][$infoKey]) && $matchesArr[$vSubID][$infoKey] == $infoDetail){
                    
                    $matchesArr[$vID]['alike'][$vSubID]['publisherId'] = $matchesArr[$vSubID]['publisherId'];
                    $matchesArr[$vID]['alike'][$vSubID]['content'][$infoKey] = $infoDetail;
                    //echo $vSubID.PHP_EOL;
                }
            }
            
        }
    }
}



foreach ($matchesArr as $alikeValue){
    
    
    if(isset($alikeValue['alike']) && !empty($alikeValue['alike'])){
        
        
        foreach ($alikeValue['alike'] as $alikeInfo){
            
            $sql = "SELECT ID FROM publisher_alike WHERE PublisherId = {$alikeValue['publisherId']} AND AlikePublisherId = {$alikeInfo['publisherId']}";
            $data = $objProgram->objMysql->getFirstRow($sql);
            if($data){
            
            }else {
                $content = json_encode($alikeInfo['content']);
                $insert_col = array(
                    'PublisherId','AlikePublisherId','AlikeContent'
                );
                $insert_value = array($alikeValue['publisherId'],$alikeInfo['publisherId'],addslashes($content));
                $insertSql = "insert into publisher_alike (".implode(',', $insert_col).") values ( '".implode("','", $insert_value)."' )";
                $objProgram->objMysql->query($insertSql);
            }
        }
    }
}

//同步publisher_domain_whois表里的alinkCount数。
$sql = "select publisherid from  publisher_domain_whois";
$whoisArr = $objProgram->objMysql->getRows($sql);
foreach ($whoisArr as $value){
     
    $sql = "select count(*) as ac from publisher_alike a left join publisher b on a.alikepublisherId = b.id where b.status = 'Active' and a.publisherId = {$value['publisherid']}";
    $acc = $objProgram->objMysql->getRows($sql);
    $count = $acc[0]['ac'];

    $sql = "update publisher_domain_whois set alinkCount = $count where publisherid = {$value['publisherid']}";
    $objProgram->objMysql->query($sql);
}

exit;

?>