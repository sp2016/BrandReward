<?php
include_once("comm.php");
include_once("func.php");
include_once("lib/currency_exchange.php");
global $_db;
$_db = new Mysql();

$uniqueList = array(
    2   =>  'orderid',  #ls,linkshare
    5   =>  'orderid',
    6   =>  'TradeId',  #pjn,pepperjamnetwork
    7   =>  'TradeId',  #sas,shareasale
    10  =>  'TradeId',
    13  =>  'SID',
    2034=>  'TradeId',
    52  =>  'TradeId',  #tt_uk
    58  =>  'TradeId',
    65  =>  'TradeId',  #tt_de
    425  =>  'TradeId',  #tt_de
    426  =>  'TradeId',  #tt_de
    427  =>  'TradeId',  #tt_de
    2027  =>  'TradeId',  #tt_de
    2028  =>  'TradeId',  #tt_de
    2029  =>  'TradeId',  #tt_de
    );
$sql = "SELECT * FROM payments_network_remit WHERE `Status` = 'confirmed' AND ChangeDataReceive = 'no' AND GetInvoice = 'yes'";
$rows = $_db->getRows($sql);

foreach($rows as $k=>$v){
    update_data_receive($v);
    $sql = "UPDATE payments_network_remit SET ChangeDataReceive = 'yes' WHERE ID = ".$v['ID'];
    $_db->query($sql);

    $sql = "SELECT COUNT(*) AS total,SUM(IF(Remark='DONE',1,0)) AS done FROM payments_network_invoice WHERE RemitID = ".$v['ID'];
    $row_summary = $_db->getFirstRow($sql);

    echo "NetworkID = ".$v['NetworkID'].",Date = ".$v['Remit_date']." Amount = ".$v['Remit_amount'].", Total = ".$row_summary['total'].", Done = ".$row_summary['done']."\n";
}

update_data_receive_deep();

function update_data_receive_deep(){
    global $_db;
    $sql = "SELECT ID,SID FROM payments_network_invoice WHERE Remark = 'NOTRANSACTION'";
    $rows = $_db->getRows($sql);

    $total = count($rows);
    foreach($rows as $k=>$v){
        $sql = "SELECT COUNT(*) as c FROM rpt_transaction_unique WHERE SID = '".$v['SID']."'";
        $row = $_db->getFirstRow($sql);
        if($row['c'] > 0){
            $sql = "UPDATE rpt_transaction_unique SET isReceive = 'yes' WHERE SID = '".$v['SID']."'";
            $_db->query($sql);
            $sql = "UPDATE payments_network_invoice SET Remark = 'DONE' WHERE ID = ".$v['ID'];
            $_db->query($sql);
        }
        echo "doing (".($k+1)."/".$total.")\n";
    }
    echo "end deep update\n";
}

function update_data_receive($remit){
    global $_db,$uniqueList;
    $sql = "SELECT ID,UniqueID,NetworkID,CreateTime FROM payments_network_invoice WHERE RemitID = ".$remit['ID'];
    $rows_invoice = $_db->getRows($sql);

    foreach($rows_invoice as $k=>$v){
        $field = $uniqueList[$v['NetworkID']];
        if($v['NetworkID'] == '5'){
            $network_str = "affid IN (5,35,415,429,469,667,769,770,2036,2037,2038,2039,2050)";
        }else{
            $network_str = "affid = ".$v['NetworkID'];
        }
        $sql = "SELECT COUNT(*) as c FROM rpt_transaction_unique WHERE CreatedDate = '".substr($v['CreateTime'],0,10)."' AND ".$network_str." AND ".$field." = '".$v['UniqueID']."'";
        $row = $_db->getFirstRow($sql);
        if($row['c'] > 0){
            $sql = "UPDATE rpt_transaction_unique SET isReceive = 'yes' WHERE CreatedDate = '".substr($v['CreateTime'],0,10)."' AND ".$network_str." AND ".$field." = '".$v['UniqueID']."'";
            $_db->query($sql);
            $sql = "UPDATE payments_network_invoice SET Remark = 'DONE' WHERE ID = ".$v['ID'];
            $_db->query($sql);
        }else{
            $sql = "UPDATE payments_network_invoice SET Remark = 'NOTRANSACTION' WHERE ID = ".$v['ID'];
            $_db->query($sql);
        }
    }
}

?>
