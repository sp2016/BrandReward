<?php
set_time_limit(0);
global $_cf,$_req,$_db,$_user;
echo "<< Start @ ".date("Y-m-d H:i:s")." >><br>";

//等级为1的联盟列表
$affSql = "SELECT ID FROM wf_aff wa WHERE wa.`IsActive` = 'YES' AND wa.`Level` = 'TIER1'";
$affIdList = $_db->getRows($affSql);
$affList = array();
foreach ($affIdList as $affId){
    $affList[] = $affId['ID'];
}

//每个publisher被系统禁用的联盟的列表以及没有数据在block中且状态为活跃等级为TIER2的publisher
$sql = "SELECT br.`PublisherId`, GROUP_CONCAT(DISTINCT br.`ObjId`) as AffId FROM block_relationship br 
     WHERE br.`Status` = 'Active' AND br.`Source` = 'SYSTEM' AND br.`ObjType` = 'Affiliate' GROUP BY br.`PublisherId` 
    UNION 
    SELECT ID,NULL FROM publisher WHERE `Status` = 'Active' AND `Level` = 'TIER2' AND ID NOT IN ( SELECT DISTINCT PublisherId FROM block_relationship br WHERE br.`Status` = 'Active' AND br.`Source` = 'SYSTEM' AND br.`ObjType` = 'Affiliate' )";
$pubList = $_db->getRows($sql);

$pubHandleList = array();
foreach ($pubList as $pub){
    if($pub['AffId'] != null){
        $pubAffList = explode(',', $pub['AffId']);
    }else {
        $pubAffList = array();
    }
    $pubHandleList[$pub['PublisherId']]['add'] = array_diff($affList, $pubAffList);
    $pubHandleList[$pub['PublisherId']]['delete'] = array_diff($pubAffList, $affList);
}

//将少block的数据补齐，移除多block的数据
foreach ($pubHandleList as $key=>$handle){
    if(!empty($handle['delete'])){
        $sql = "UPDATE block_relationship SET `Status` = 'Inactive',`LastUpdateTime` = '".date("Y-m-d H:i:s")."' WHERE `Status` = 'Active' AND `Source` = 'SYSTEM' AND `ObjType` = 'Affiliate' AND `PublisherId` = '".$key."' AND `ObjId` in ( ".implode($handle['delete'], ",")." ) ";
        $_db->query($sql);
        echo 'update publisherId---'.$key.',affId---'.implode($handle['delete'], ",").'<br>';
    }
    if(!empty($handle['add'])){
        $sql = "INSERT INTO block_relationship(`BlockBy`,`AccountId`,`AccountType`,`PublisherId`,`ObjId`,`ObjType`,`Status`,`AddTime`,`Source`) VALUES ";
        foreach ($handle['add'] as $temp){
            $sql .= "('Affiliate','".$key."','PublisherId','".$key."','".$temp."','Affiliate','Active','".date('Y-m-d H:i:s')."','SYSTEM'),";
            /* $sql = "INSERT INTO block_relationship(`BlockBy`,`AccountId`,`AccountType`,`PublisherId`,`ObjId`,`ObjType`,`Status`,`AddTime`,`Source`)
             VALUES('Affiliate','".$key."','PublisherId','".$key."','".$temp."','Affiliate','Active','".date('Y-m-d H:i:s')."','SYSTEM')"; */
            echo 'add publisherId---'.$key.',affId---'.$temp.'<br>';
        }
        $sql = trim($sql,',');
        $_db->query($sql);
    }
}



echo "<< End @ ".date("Y-m-d H:i:s")." >><br>";
exit;







?>
