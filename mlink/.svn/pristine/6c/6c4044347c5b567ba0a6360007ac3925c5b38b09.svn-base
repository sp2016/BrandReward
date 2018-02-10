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

$data_dir = PATH_DATA;

$fileList = getDir($data_dir,'file',false,1);

#chenck payments list & get update file

$TODOlist = array();
foreach($fileList as $k=>$v){
    $file = $v;
    if(substr($file,-3) == 'dat'){
        $TODOlist[] = $file;
    }
}


#do file data update to db

foreach($TODOlist as $file){
    $data_file = str_replace(PATH_DATA,'',$file);
    $file_name = basename($file);
    preg_match('/(\d+)_(\d+)\.dat/',$file_name,$m);
    $remitid = $m[1];
    
    $sql = "SELECT * FROM payments_network_remit WHERE ID = ".intval($remitid);
    $remit_row = $_db->getFirstRow($sql);
    if($remit_row['GetInvoice'] == 'yes'){
        continue;
    }

    $lastversion = date('Ymd');

    $content = file_get_contents($file);
    $lines = explode("\n",$content);
    $insert_data = array();
    $i = 0;
    foreach($lines as $line){
        if(empty($line))
            continue;
        list($c_date,$keyid,$sales,$comm,$sid,$curr) = explode("\t",$line);

        $tmp = array(
              'NetworkID' => $remit_row['NetworkID'],
              'NetworkRemitID' => $remit_row['IDinNetwork'],
              'RemitID' => $remit_row['ID'],
              'CreateTime' => $c_date,
              'UniqueID' => $keyid,
              'Sales' => $sales,
              'Commission' => $comm,
              'SID' => $sid,
              'Currency' => $curr,
              'DataFile' => $data_file,
              'LastVersion' => $lastversion,
        );
        $insert_data[] = $tmp;
        $i++;
        if(count($insert_data) > 500){
            $sql = getBatchInsertSql($insert_data,'payments_network_invoice');
            $_db->query($sql);
            $insert_data = array();
        }
    }
    if(count($insert_data) > 0){
        $sql = getBatchInsertSql($insert_data,'payments_network_invoice');
        $_db->query($sql);
    }
    if($i>2){
        $sql = "UPDATE payments_network_remit SET GetInvoice = 'yes' WHERE ID = ".intval($remitid);
        $_db->query($sql);
    }
    print_r("done file ".$file."\n");
}

echo 'done';exit();

function update_file_md5($payments,$file_md5){
    global $_db;
    $sql = 'UPDATE payments SET FileMd5 = "'.addslashes(trim($file_md5)).'" WHERE ID = '.$payments['ID'];
    $_db->query($sql);
}

function getKey($file){
    $filedir = basename(dirname($file));
    $filename = basename($file);

    list(,$affid) = explode('_',$filedir);
    preg_match('/\d+/', $filename,$m);

    $datenum = $m[0];

    $key = $affid.'_'.$datenum;
    return $key;
}


function info_data($payments){
    global $_db;
    $paymentsID = $payments['ID'];
    $af_short = array(
            '9999'=>'mega',
            '1'=>'cj',
            '2'=>'ls',
            '7'=>'sas',
            '6'=>'pjn',
            '22'=>'afffuk',
            '115'=>'cf',
            '13'=>'wg',
            '58'=>'impradus',
            );

    $sql = 'SELECT ID,MatchID,MatchKey,NetworkID,Currency,Commission FROM payments_invoice WHERE paymentsID = '.intval($paymentsID);
    $row_invoice = $_db->getRows($sql);
    if(empty($row_invoice))
        return ;

    $tmp = $row_invoice[0];
    $sql = 'SELECT a.ID,a.ShowRate,a.RefRate,a.RefPublisherId,a.Site,a.'.$tmp['MatchKey'].',b.PublisherId FROM rpt_transaction_unique as a left join publisher_account as b on a.Site = b.ApiKey WHERE a.af = "'.$af_short[$tmp['NetworkID']].'" AND '.$tmp['MatchKey'].' IN (SELECT MatchID FROM payments_invoice WHERE paymentsID = '.$paymentsID.')';
    $rows_match = $_db->getRows($sql);
    $transInfo = array();
    $key = $tmp['MatchKey'];
    foreach($rows_match as $k=>$v){
        $tmp = array(
                'ShowRate' => $v['ShowRate'],
                'RefRate' => $v['RefRate'],
                'RefPublisherId' => $v['RefPublisherId'],
                'Site' => $v['Site'],
                'publisherId' => $v['PublisherId']?$v['PublisherId']:0,
            );

        if(!isset($transInfo[$v[$key]])){
            $transInfo[$v[$key]] = $tmp;
        }elseif(!empty($tmp['publisherId'])){
            $transInfo[$v[$key]] = $tmp;
        }
    }

    $sql = 'SELECT * FROM payments WHERE ID = '.intval($paymentsID);
    $row_payments = $_db->getFirstRow($sql);
    $cur_exr = cur_exchange($row_payments['Currency'],'USD',$row_payments['CreatedDate']);
    $AmountUSD = round($row_payments['Amount'] * $cur_exr, 4);

    #update payments invoice
    #do commission currency exchange
    #do sync rate site info
    $invoiceUp = array();
    foreach($row_invoice as $k=>$v){
        $cur_exr = cur_exchange($v['Currency'],'USD',$row_payments['CreatedDate']);
        $CommissionUSD = round($v['Commission'] * $cur_exr, 4);

        $IsMatch = 'no';
        $ShowRate = 0;
        $RefRate = 0;
        $RefPublisherId = 0;
        $ShowSite = '';
        $ShowPublisherId = 0;
        if(isset($transInfo[$v['MatchID']])){
            $IsMatch = 'yes';
            $ShowRate = $transInfo[$v['MatchID']]['ShowRate'];
            $RefRate = $transInfo[$v['MatchID']]['RefRate'];
            $RefPublisherId = $transInfo[$v['MatchID']]['RefPublisherId'];
            $ShowSite = $transInfo[$v['MatchID']]['Site'];
            $ShowPublisherId = $transInfo[$v['MatchID']]['publisherId'];
        }

        $tmp = array(
            'ID' => $v['ID'],
            'CommissionUSD' => $CommissionUSD,
            'IsMatch' => $IsMatch,
            'ShowRate' => $ShowRate,
            'RefRate' => $RefRate,
            'RefPublisherId' => $RefPublisherId,
            'ShowSite' => $ShowSite,
            'ShowPublisherId' => $ShowPublisherId,
            );
        $invoiceUp[] = $tmp;
    }

    $sql = getBatchUpdateSql($invoiceUp,'payments_invoice','ID');
    $_db->query($sql);

    $sql = 'SELECT SUM(CommissionUSD) as InvoiceAmountUSD FROM payments_invoice WHERE paymentsID = '.intval($paymentsID);
    $row_sum = $_db->getFirstRow($sql);
    $InvoiceAmountUSD = $row_sum['InvoiceAmountUSD'];

    #update payments
    #do payments commission currency exchange
    #do update payments invoice amount to payments
    $sql = 'UPDATE payments SET AmountUSD = '.floatval($AmountUSD).',InvoiceAmountUSD = '.floatval($InvoiceAmountUSD).' WHERE ID = '.intval($paymentsID);
    $_db->query($sql);

    echo 'update payments @ AmountUSD = '.floatval($AmountUSD).',InvoiceAmountUSD = '.floatval($InvoiceAmountUSD)."\n";
}

function update_data($file,$payments){

    global $uniqueList,$_db;

    $sql = 'DELETE FROM payments_invoice WHERE PaymentsID = '.intval($payments['ID']);
    $_db->query($sql);

    $db_data = array();
    $c = 0;

    if(is_file($file)){
        $fp=fopen($file,'r');
        while(!feof($fp)){
            $line=fgets($fp,4000);
            $line = trim($line);
            if(empty($line))
                continue;

            $Arr = explode("\t",$line);
            if(empty($Arr[0]))
                continue;

            if(empty($Arr[2]))
                continue;

            $tmp = array(
                'PaymentsID'=>$payments['ID'],
                'MatchID'=>$Arr[2],
                'MatchKey'=>$uniqueList[$payments['NetworkID']],
                'Commission'=>$Arr[1],
                'Currency'=>$payments['Currency'],
                'PaymentKey'=>$payments['PaymentKey'],
                'NetworkID'=>$payments['NetworkID'],
                'Network'=>$payments['Network'],
                );

            $c++;
            $db_data[] = $tmp;
            if(count($db_data) > 499){
                $sql = getBatchInsertSql($db_data,'payments_invoice');
                $_db->query($sql);
                $db_data = array();
            }

        }
        fclose($fp);
        if(count($db_data) > 0){
            $sql = getBatchInsertSql($db_data,'payments_invoice');
            $_db->query($sql);
            $db_data = array();
        }
    }
    echo "update file @ ".$file." , nums: ".$c."\n";
}

function getDir($dir,$only='',$last_name=false,$loop=false){
    if (empty($dir)) {
        return array();
    }

    $content = array();

    if(is_array($dir)){
        foreach($dir as $d){
            $tmp = getDir($d,$only,$last_name,$loop);
            $content = array_merge($content,$tmp);
        }
    }else{
        $ch = '';
        if(substr($dir,-1) != '/')
            $ch = '/';

        $dc = scandir($dir);
        foreach($dc as $k=>$v){
            if($v == '.' || $v == '..')
                continue;
            if($only=='dir' && is_dir($dir.$ch.$v)){
                $content[] = $last_name?$v:$dir.$ch.$v;
            }
            if($only=='file' && is_file($dir.$ch.$v)){
                $content[] = $last_name?$v:$dir.$ch.$v;
            }

            if($loop && is_dir($dir.$ch.$v)){
                $tmp = getDir($dir.$ch.$v,$only,$last_name,$loop);
                $content = array_merge($content,$tmp);
            }
        }
    }

    return $content;
}
?>
