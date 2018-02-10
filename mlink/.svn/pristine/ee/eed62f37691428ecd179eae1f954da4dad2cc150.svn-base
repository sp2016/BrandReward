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


$sql = "SELECT publisherid,Apikey FROM publisher_account WHERE publisherid in (1024,1025,90333)";
$rows = $_db->getRows($sql);
$sites = _array_column($rows,'Apikey');

$sql = "SELECT ID,affid,orderid,TradeId,SID FROM rpt_transaction_unique WHERE CreatedDate>='2017-10-01' and CreatedDate < '2017-11-01' AND site IN ('".join("','",$sites)."') AND isReceive = 'no'";
$rows_noreceive = $_db->getRows($sql);

$count = count($rows_noreceive);
foreach($rows_noreceive as $k=>$v){
    if(!empty($v['SID'])){
        $sql = "SELECT * FROM payments_network_invoice WHERE SID = '".$v['SID']."'";
        $tmp = $_db->getRows($sql);
        if(!empty($tmp)){
            $sql = "UPDATE rpt_transaction_unique SET isReceive = 'ontheway' WHERE ID = ".$v['ID'];
            $_db->query($sql);
        }
    }

    if(isset($uniqueList[$v['affid']])){
        $key = $uniqueList[$v['affid']];
        if( isset($v[$key]) && !empty($v[$key])){
            $key = $uniqueList[$v['affid']];
            $sql = "SELECT * FROM payments_network_invoice WHERE NetworkID = ".$v['affid']." AND UniqueID = '".$v[$key]."'";
            $tmp = $_db->getRows($sql);
            if(!empty($tmp)){
                $sql = "UPDATE rpt_transaction_unique SET isReceive = 'ontheway' WHERE ID = ".$v['ID'];
                $_db->query($sql);
            }  
        }
    }
    echo "doing (".($k+1)."/".$count.")...\n";
}
?>
