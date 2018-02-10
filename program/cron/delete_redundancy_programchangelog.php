<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
include_once(INCLUDE_ROOT . "func/func.php");

$Mysql = new Mysql();
echo "<< Start @ ".date("Y-m-d H:i:s")." >>\r\n";
$sql = "SELECT COUNT(*) as count FROM program_change_log";
$count = mysql_query($sql);
$count = mysql_fetch_assoc($count);
echo "------Total {$count['count']} data------\n\r";
$start = -500;
$num = 0;
$size = 500;
do{
    $start+=500;
    $sql = "SELECT ID,FieldValueOld,FieldValueNew FROM program_change_log LIMIT ".$start;
    $data = $Mysql->getRows($sql);
    $delete_id = array();
    if(!empty($data)){
        foreach($data as $val){
            if($val['FieldValueOld'] == $val['FieldValueNew'] || addslashes($val['FieldValueOld']) == $val['FieldValueNew'] || $val['FieldValueOld'] == stripslashes($val['FieldValueNew'])) {
                $delete_id[] = intval($val['ID']);
                $num++;
            }
        }
        $delete_id = implode(",",$delete_id);
        $sql = "DELETE FROM program_change_log WHERE ID IN (".$delete_id.")";
        mysql_query($sql);
    }
}while($start < $count['count']);

echo "------Delete $num redundancy_data------\n\r";
echo "<< END @ ".date("Y-m-d H:i:s")." >>\r\n";