<?php
include_once("comm.php");
include_once("func.php");
include_once("lib/currency_exchange.php");
global $_db;
$_db = new Mysql();
$_db_fi = new Mysql('finance_br_base','bi01.bwe.io','br','br#fYuqHh');

$sql = "SELECT * FROM payments_network_remit WHERE MatchFinanceID = 0";
$row_network_remit = $_db->getRows($sql);

foreach($row_network_remit as $k=>$v){
    
    if($v['NetworkID'] == 13){
        $str = "Affiliate IN (13,14,18,34,208,395,559,20002)";
    }if($v['NetworkID'] == 5){
        $str = "Affiliate IN (5,35,415,429,469,667,769,770,2036,2037,2038,2039,2050)";
    }else{
        $str = "Affiliate = ".$v['NetworkID'];
    }
    $amount_min = $v['Remit_amount'] - 2;
    $amount_max = $v['Remit_amount'] + 2;
    $sql  = "SELECT * FROM receivables_management WHERE ".$str." AND RemittedTime = '".$v['Remit_date']."' AND Amount > ".$amount_min." AND Amount < ".$amount_max;
    $rows_tmp = $_db_fi->getRows($sql);
    if(count($rows_tmp) == 1){
        $sql = "UPDATE payments_network_remit SET MatchFinanceID = ".$rows_tmp[0]['ID']." WHERE ID = ".$v['ID'];
        $_db->query($sql);
    }
}
$sql = "SELECT COUNT(*) AS total,SUM(IF(matchfinanceID>0,1,0)) AS matched FROM payments_network_remit";
$row = $_db->getFirstRow($sql);

print_r('Match end, all payments = '.$row['total'].', matched payments = '.$row['matched']."\n");

$sql = "SELECT ID FROM receivables_management WHERE `Status` = 'confirmed'";
$rows_fi_confirm = $_db_fi->getRows($sql);
$fi_confirm = _array_column($rows_fi_confirm,'ID');

$sql = "UPDATE payments_network_remit SET `Status` = 'confirmed' WHERE MatchFinanceID IN (".join(',',$fi_confirm).")";
$_db->query($sql);

$sql = "SELECT COUNT(*) AS total,SUM(IF(`Status`='confirmed',1,0)) AS confirmed FROM payments_network_remit";
$row = $_db->getFirstRow($sql);

print_r('Confirm end, all payments = '.$row['total'].', confirmed payments = '.$row['confirmed']."\n");
?>
