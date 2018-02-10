<?php
include_once(dirname(dirname(__FILE__))."/etc/const.php");
    function editPageSync($AffId){
        $model = new Mysql;
        mysql_query("set names latin1");
        $sql = 'SELECT `Name`, GROUP_CONCAT(ID SEPARATOR "|") AS idStr, GROUP_CONCAT(IdInAff SEPARATOR "|") AS idInAffStr, GROUP_CONCAT(Creator SEPARATOR "|") AS creatorStr, COUNT(*) FROM program WHERE AffId = '.$AffId.' GROUP BY `Name` HAVING COUNT(*) > 1';
        $arr = $model->getRows($sql);
        foreach($arr as $k=>$v){
//            if ($v['Name'] != 'Webdealauto - Coupons')
//                continue;
            $creator = explode('|',$v['creatorStr']);
            if(!in_array('System',$creator))
                continue;
            $id = explode('|',$v['idStr']);
            $creatorFlip = array_flip($creator);
            if(count(array_unique($creator)) == 1){
                return;
            }else{
                //sync commissionused to program_manual
                $sql = 'SELECT a.ID,a.IdInAff,a.Remark,b.CommissionUsed,b.CommissionCurrency,b.CommissionType FROM program AS a LEFT JOIN program_manual AS b ON a.ID = b.ProgramId WHERE a.AffId = '.$AffId.' AND a.Name = "'.$v['Name'].'" AND a.Creator <> "System" ORDER BY a.LastUpdateTime DESC';
                $arrNotSys = $model->getFirstRow($sql);
                if(!is_null($arrNotSys['CommissionUsed']) && $arrNotSys['CommissionUsed'] != 0){//如果program_manual中存在非system的program，则去更新system的commission
                    $sqlCommCheck = 'SELECT ProgramId,CommissionUsed FROM program_manual WHERE ProgramId = '.$id[$creatorFlip["System"]];//查看program_manual表中是否有creator为system的program
                    $arr = $model->getFirstRow($sqlCommCheck);
                    if(empty($arr)){
                        $sql = 'INSERT INTO program_manual (ProgramId,CommissionUsed,CommissionCurrency,CommissionType) VALUES ('.$id[$creatorFlip["System"]].','.$arrNotSys["CommissionUsed"].',"'.$arrNotSys["CommissionCurrency"].'","'.$arrNotSys["CommissionType"].'")';
                        mysql_query($sql);
                        echo "Insert commissionused of ".$v['Name']." successed<br>";
                    }elseif($arr['CommissionUsed'] = 0){
                        $sql = 'UPDATE program_manual SET CommissionUsed = '.$arrNotSys["CommissionUsed"].',CommissionType = "'.$arrNotSys["CommissionCurrency"].'",CommissionCurrency = '.$arrNotSys["CommissionCurrency"].'"';
                        mysql_query($sql);
                        echo "Update commissionused of ".$v['Name']." successed<br>";
                    }
                }
                //sync remark to program
                if(!empty($arrNotSys['Remark'])){
                    $sqlRemCheck = 'SELECT Remark FROM program WHERE `ID` = '.$id[$creatorFlip["System"]];
                    $arr = $model->getFirstRow($sqlRemCheck);
                    if(empty($arr['Remark'])){
                        mysql_query("set names latin1");
                        $sql = 'UPDATE program SET Remark = "'.addslashes($arrNotSys['Remark']).'" WHERE AffId = '.$AffId.' AND `Name` = "'.$v['Name'].'" AND Creator = "System"';
                        mysql_query($sql);
                        echo "syncing Remark of".$v['Name']."successed<br>";
                    }
                }
            }
        }
    }
    editPageSync(64);
?>