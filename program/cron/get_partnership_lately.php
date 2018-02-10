<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");

//记录状态变为active program log
$objProgram = New ProgramDb();

$tempDate = date("Y-m-d",strtotime("-1 day"));
$startTime  = $tempDate.' 00:00:00';
$endTime    = date("Y-m-d").' 00:00:00';
//$startTime = '2016-12-07';
$changelogArr = array();

echo $startTime.'=====>'.$endTime.PHP_EOL;

/*$sql = "select a.ID,a.ProgramId,a.IdInAff,a.Name,a.AddTime,b.Homepage, c.Name as AffName from 
        program_change_log a inner join program b on a.programid = b.id 
        inner join wf_aff c on b.AffId = c.ID
        where a.AddTime > '".$startTime."' AND a.AddTime < '".$endTime.
        "' AND (a.FieldName = 'Partnership' OR a.FieldName = 'StatusInAff') AND a.FieldValueNew = 'Active'  AND b.StatusInAff = 'Active' and b.Partnership = 'Active'";
//echo $sql.PHP_EOL;exit;
$changelogArr1 = $objProgram->objMysql->getRows($sql);

$sql = "SELECT * FROM program WHERE statusinaff = 'active' AND partnership = 'active' AND ADDTIME > '2016-12-07'";
$changelogArr2 = $objProgram->objMysql->getRows($sql);*/

$sql = "select b.ID,b.IdInAff,b.Name,a.AddTime,b.Homepage, c.Name as AffName from 
        program_change_log a inner join program b on a.programid = b.id 
        inner join wf_aff c on b.AffId = c.ID
        where a.AddTime >= '".$startTime."' AND a.AddTime < '".$endTime."'
            AND (a.FieldName = 'Partnership' OR a.FieldName = 'StatusInAff') AND a.FieldValueNew = 'Active'  AND b.StatusInAff = 'Active' and b.Partnership = 'Active'";
            
//echo $sql;exit;
$changelogArr1 = $objProgram->objMysql->getRows($sql);

$sql = "SELECT a.ID,a.Name,a.Homepage,a.AddTime,b.Name as AffName FROM program a inner join wf_aff b on a.AffId = b.ID WHERE a.statusinaff = 'active' AND a.partnership = 'active' AND a.ADDTIME > '".$startTime."'";
$changelogArr2 = $objProgram->objMysql->getRows($sql);

 

foreach ($changelogArr1 as $v1){
    $changelogArr[$v1['ID']] = $v1;
}

foreach ($changelogArr2 as $v2){
    $changelogArr[$v2['ID']] = $v2;
}

/*$alert_body =
        '<table border="1">
           <tr>
             <th>ProgramName</th>
             <th>Newwork</th>
             <th>Homepage</th>
             <th>date</th>
           </tr>';*/
$column_keys = array('ProgramId','Name','AffName','Homepage','AddTime'); 
 
$count = 0;
foreach ($changelogArr as $value){
   
    //same
    $sql = "SELECT count(*) as count FROM temp_partership WHERE ProgramId = {$value['ID']} AND `AddTime` = '{$value['AddTime']}'";
    $has = $objProgram->objMysql->getFirstRow($sql);
    if($has['count'] <= 0){
        
        $tmp_insert=array($value['ID'],addslashes($value['Name']),addslashes($value['AffName']),addslashes($value['Homepage']),$value['AddTime']);
        $sql="INSERT INTO temp_partership (".implode(",", $column_keys).") VALUES ('".implode("','", $tmp_insert)."')";
        $objProgram->objMysql->query($sql);
        $count++;
    }
    /*$alert_body .=
        '<tr>
           <td>'.$value['Name'].'</td>
           <td>'.$value['AffName'].'</td>
           <td>'.$value['Homepage'].'</td>
           <td>'.$value['AddTime'].'</td>
         </tr>';*/
}
echo $count.PHP_EOL;
exit;
/*$alert_body .= '</table>';

echo $alert_body;exit;*/

//$alert_subject = 'Program Partnership Change';
//AlertEmail::SendAlert($alert_subject,nl2br($alert_body), "merlinxu@brandreward.com");

?>