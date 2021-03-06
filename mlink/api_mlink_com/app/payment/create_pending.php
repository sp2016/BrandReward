<?php
global $_cf,$_db,$_req;
include_once(INCLUDE_ROOT.'/lib/PHPExcel.php');
selfcheck();

echo "begin\t|\t".$_req['act']."\t|\t".date('Y-m-d H:i:s')."\n";

$month_first = date('Y-m-01');
if(isset($_req['datemonth']) && !empty($_req['datemonth'])){
    $month_first = $_req['datemonth'].'-01';
}
$pending_date = getMonthPaidDate($month_first);
$where = getTransactionWhereByPendingDate($pending_date);
$sql = "select site,sum(showcommission) cc from rpt_transaction_unique ".$where." and site != 'unknown' and site != '' group by site";
$rows = $_db->getRows($sql);

$sites = _array_column($rows,'site');
$sql = "SELECT distinct(Site) FROM payments_pending WHERE PendingDate = '".$pending_date."' AND Site IN ('".join("','",$sites)."')";
$rows_exist = $_db->getRows($sql);
$site_exist = _array_column($rows_exist,'Site');


$rows_not_exist = array();
foreach($rows as $k=>$v){
    if(!in_array($v['site'],$site_exist)){
        $rows_not_exist[] = $v;
    }
}

print_r("info\t|\tpending site nums\t|\t".count($rows)." - ".count($rows_exist)." = ".count($rows_not_exist)."\n");
$lastversion = date('ymdhis');
print_r("info\t|\tlastversion\t|\t".$lastversion."\n");

foreach($rows_not_exist as $value_site){
    print_r("info\t|\tupdate site\t|\t".$value_site['site']."\n");
    $sql = "SELECT COUNT(*) as c,SUM(ShowCommission) as cc FROM rpt_transaction_unique ".$where." and site = '".$value_site['site']."' AND BRID NOT IN (SELECT BRID FROM payments_pending_invoice WHERE lastversion != '".$lastversion."')";
    $row = $_db->getFirstRow($sql);
    if($row['c'] > 0){
        $page_size = 500;
        $page_total = ceil($row['c']/$page_size);
        print_r("info\t|\tpending-invoice new INFO\t|\tsum:".$row['cc'].", total:".$row['c'].", page size:".$page_size.", page total:".$page_total."\n");
        for($i=0; $i < $page_total; $i++){
            print_r("info\t|\tpending-invoice new PAGE\t|\t(".$page_total."/".($i+1).")\n");
            $sql = "SELECT CreatedDate,VisitedDate,BRID,ShowCommission as Commission,Site,Af,AffId,programId,domainId FROM rpt_transaction_unique ".$where." and site = '".$value_site['site']."' AND BRID NOT IN (SELECT BRID FROM payments_pending_invoice WHERE lastversion != '".$lastversion."') ORDER BY ID LIMIT ".$i*$page_size.",".$page_size;
            $rows_transaction = $_db->getRows($sql);

            foreach($rows_transaction as $k=>$v){
                $rows_transaction[$k]['PendingDate'] = $pending_date; 
                $rows_transaction[$k]['OriginDate'] = getOriginDate($v['VisitedDate'],$v['CreatedDate']); 
                $rows_transaction[$k]['lastversion'] = $lastversion;
            }
            $sql = getInsertBatchSql('payments_pending_invoice',$rows_transaction);
            //echo $sql."\n";
            $_db->query($sql);
        }
    }

    $sql = "SELECT COUNT(*) as c,SUM(ShowCommission) as cc FROM rpt_transaction_unique ".$where." and site = '".$value_site['site']."' AND BRID IN (SELECT BRID FROM payments_pending_invoice WHERE lastversion != '".$lastversion."')";
    $row = $_db->getFirstRow($sql);
    print_r("info\t|\tpending-invoice exist COUNT\t|\t".$row['c']."\n");
    if($row['c'] > 0){
        $sql = "SELECT BRID FROM rpt_transaction_unique ".$where." and site = '".$value_site['site']."' AND BRID IN (SELECT BRID FROM payments_pending_invoice WHERE lastversion != '".$lastversion."')";
        $rows_BRID = $_db->getRows($sql);
        $brids = _array_column($rows_BRID,'BRID');
        $sql = "SELECT aa.*,bb.ShowCommission AS NowCommission FROM (
        SELECT CreatedDate,VisitedDate,BRID,sum(Commission) as OldCommission,Site,Af,AffId,programId,domainId,OriginDate FROM payments_pending_invoice WHERE BRID IN ('".join("','",$brids)."') GROUP BY BRID
        ) AS aa LEFT JOIN rpt_transaction_unique AS bb ON aa.BRID = bb.BRID WHERE aa.OldCommission != bb.ShowCommission";
        $rows_diff = $_db->getRows($sql);
        if(!empty($rows_diff)){
            $data_diff = array();
            $diff_num = count($rows_diff);
            $diff_sum = 0;
            foreach($rows_diff as $k=>$v){
                $tmp = $v;
                $tmp['Commission'] = bcsub($tmp['NowCommission'],$tmp['OldCommission'],4);
                $tmp['PendingDate'] = $pending_date;
                $tmp['lastversion'] = $lastversion;
                unset($tmp['NowCommission']);
                unset($tmp['OldCommission']);
                $data_diff[] = $tmp;
                $diff_sum += $tmp['Commission'];
            }
            $sql = getInsertBatchSql('payments_pending_invoice',$data_diff);
            //echo $sql."\n";
            $_db->query($sql);
            print_r("info\t|\tpending-invoice diff COUNT\t|\t".$diff_num."\n");
            print_r("info\t|\tpending-invoice diff SUM\t|\t".$diff_sum."\n");
        }
    }
}
$sql = "SELECT Site,PendingDate,OriginDate,SUM(Commission) as Amount FROM payments_pending_invoice WHERE lastversion = '".$lastversion."' GROUP BY Site,PendingDate,OriginDate";
$rows = $_db->getRows($sql);
foreach($rows as $k=>$v){
    $rows[$k]['lastversion'] = $lastversion;
}
$sql = getInsertBatchSql('payments_pending',$rows);
$_db->query($sql);
$sql = "update payments_pending as a left join publisher_account as b on a.Site = b.ApiKey set a.`PublisherId` = b.`PublisherId` where a.`PublisherId` = 0";
$_db->query($sql);
$sql = "update payments_pending_invoice as a left join payments_pending as b on (a.`Site` = b.`Site` and a.`PendingDate` = b.`PendingDate` and a.`OriginDate` = b.`OriginDate`) set a.`PendingID` = b.`ID` where a.lastversion = '".$lastversion."'";
$_db->query($sql);

echo "end\t|\t".$_req['act']."\t|\t".date('Y-m-d H:i:s')."\n";

function getInsertBatchSql($table,$data){
    $column_arr = array_keys($data[0]);
    $sql = "INSERT INTO ".$table." (".join(',',$column_arr).") VALUES ";
    $values_arr = array();
    foreach($data as $k=>$v){
        $tmp = array();
        foreach($v as $a){
            $tmp[] = addslashes(trim($a));
        }
        $values_arr[] = "('".join("','",$tmp)."')";
    }
    $sql .= join(",\n",$values_arr);
    return $sql;
}

function getOriginDate($visiteddate,$createddate){
    $origin_date = '';
    if(strtotime($visiteddate) < strtotime('2017-03-01')){
        $origin_date = getNextPaidDate($visiteddate,'90');
    }elseif(strtotime($visiteddate) < strtotime('2017-08-01')){
        $origin_date = getNextPaidDate($visiteddate,'60');
    }else{
        $origin_date = getNextPaidDate($createddate,'60');
    }
    return $origin_date;
}

function getNextPaidDate($input_date,$cycle='60'){
    list($y,$m,$d) = explode('-',$input_date);
    if($cycle == '90'){
       $m += 3;
    }else{
       $m += 2;
    }
    if($m > 12){
        $m = $m - 12;
        $y += 1;
    }
    if($m < 10){
        $m = '0'.$m;
    }

    $month_first = $y.'-'.$m;
    $month_lastday = date('Y-m-t',strtotime($month_first));
    $objD = new Datetime($month_lastday);
    while($objD->format('w') < 1 || $objD->format('w') > 5){
        $objD->modify('-1 day');
    }
    $payment_date = $objD->format('Y-m-d');

    return $payment_date;
}


function getTransactionWhereByPendingDate($paiddate){
    global $_db;
    $sql = "select ApiKey from publisher_account where PubLisherId in (select ID FROm publisher where PaymentStatus = 'stop')";
    $rows = $_db->getRows($sql);
    $site_not_pay = _array_column($rows,$rows); 


    list($year,$month) = explode('-',$paiddate);
    if($month < 2){
        $month = '12';
        $year = $year - 1;
    }else{
        $month = $month - 1;
    }
    if($month <  10){
        $month = '0'.$month;
    }
    $date = $year.'-'.$month.'-01';
    $where  = " where createddate < '".$date."'";
    $where .= " and paiddate = '0000-00-00' and af not in ('mega','mk','bdg') AND Site !='' AND Site != 'unknown' AND isReceive IN ('yes','ontheway') AND Site NOT IN ('".join("','",$site_not_pay)."')";
    return $where;
}

function getMonthPaidDate($input_date){
    $month_lastday = date('Y-m-t',strtotime($input_date));
    $objD = new Datetime($month_lastday);
    while($objD->format('w') < 1 || $objD->format('w') > 5){
        $objD->modify('-1 day');
    }
    $payment_date = $objD->format('Y-m-d');
    return $payment_date;
}

function selfcheck(){
    global $_req;
    $cmd = "ps aux | grep '\-act=".$_req['act']."' | wc -l";
    //echo $cmd."\n";
    exec($cmd, $res);
    //print_r($res);exit();
    if($res[0] > 3){
        echo "stop\t|\tprocess is doing\n";exit();
    }else{
        return true;
    }
}

function _array_column($input,$column_key,$index_key=null){
    if(empty($input)){
        return array();
    }

    if(!is_array($input)){
        return array();
    }

    $column_arr = array();
    $index_arr = array();
    foreach($input as $k=>$v){
        if(!empty($column_key) && isset($v[$column_key])){
            $column_arr[] = $v[$column_key];
        }

        if(!empty($index_key) && isset($v[$index_key])){
            $index_arr[] = $v[$index_key];
        }
    }

    if(!empty($index_key)){
        $output = array();
        foreach($index_arr as $k=>$v){
            $output[$v] = $column_arr[$k];
        }
        return $output;
    }else{
        return $column_arr;
    }
}
?>
