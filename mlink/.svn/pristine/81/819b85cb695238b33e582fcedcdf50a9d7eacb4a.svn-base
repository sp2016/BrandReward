<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init2.php');

$objTran = new Transaction;
$objOutlog = new Outlog;
if(isset($_POST['table']) && !empty($_POST['table'])){
    $page = $_POST['start'];
    $pagesize = $_POST['length'];
    $search = $_POST['search'];
    $search['stime'] = $_POST['stime'];
    $search['etime'] = $_POST['etime'];
    $search['advertiser'] = $_POST['advertiser'];
    $search['linkid'] = $_POST['linkid'];
    $search['site'] = $_POST['site'];
    $search['type'] = $_POST['type'];
    $search['status'] = $_POST['status'];
    $search['state']   = isset($_POST['state']) ? $_POST['state'] : '';
    $search['sitetype'] = $_POST['sitetype'];
    $search['timetype'] = $_POST['timetype'];
    $search['download'] = 0;
    $country = '';
    $affiliate = '';
    if(!empty($_POST['country'])){
        foreach($_POST['country'] as $k){
            if($k == 'UK'){
                $country.='"gb","uk",';
                continue;
            }
            $country.='"'.strtolower($k).'",';
        }
    }
    if(!empty($_POST['affiliate'])){
        foreach($_POST['affiliate'] as $k){
            $affiliate.=$k.',';
        }
    }
    if(isset($_POST['cid']) && !empty($_POST['cid'])){
        $sql = "select CountryCode from country_codes where CountryName ='{$_POST['cid']}'";
        $c = $objOutlog->getRow($sql);
        if($c['CountryCode'] == 'UK' || $c['CountryCode'] == 'GB'){
            $country = '"gb","uk"';
        }else{
            $country = '"'.strtolower($c['CountryCode']).'"';
        }
        $search['country'] = $country;
        $search['cid'] = 1;
    }else{
        $search['country'] = rtrim($country,',');
        $search['cid'] = 0;
    }
    $search['download'] = 1;
    $search['affiliate'] = rtrim($affiliate,',');
    $DomainTotal = $objTran->getTransactionListPage($search,$page,$pagesize);

    $DomainList = $DomainTotal['data'];

    foreach($DomainList as &$k){
        $k['Sales'] = "$".number_format($k['Sales'],2);
        $k['Commission'] = "$".number_format($k['Commission'],2);
        $k['ShowCommission'] = number_format($k['ShowCommission'],2);
        $k['TaxCommission'] = number_format($k['TaxCommission'],2);
        $k['RefCommission'] = number_format($k['RefCommission'],2);
    }
    if(!empty($DomainTotal['cinfo'])){
        foreach($DomainTotal['cinfo'] as $v){
            $res['name'][] = $v['name'];
            $res['dsales'][] = $v['sales'];
            $res['com'][] = $v['com'];
        }
    }
    $res['cinfo'] = !empty($DomainTotal['cinfo'])?$DomainTotal['cinfo']:'';
    $res['data'] = $DomainList;
    $res['start'] = $page/$pagesize+1;
    $res['compb'] = "$".number_format($DomainTotal['sum']['show'],2).' -- '."$".number_format($DomainTotal['sum']['tax'],2);
    $res['comf'] = "$".number_format($DomainTotal['sum']['ref'],2);
    $res['commission'] =  "$".number_format($DomainTotal['sum']['com'],2);
    $res['sales'] =  "$".number_format($DomainTotal['sum']['sales'],2);
    $res['recordsFiltered'] = $DomainTotal['total_num'];
    echo json_encode($res,JSON_UNESCAPED_UNICODE);
    die;
}

if(isset($_POST['check']) && !empty($_POST['check'])){
    $key = $_POST['key'];
    $sql = "SELECT a.Created, a.Updated, a.Sales, a.Commission, a.`ProgramName` , b.`Name` FROM `rpt_transaction_base` a LEFT JOIN wf_aff b ON a.`Affid` = b.id WHERE a.TradeKey = '$key' ORDER BY a.Updated";
    $res = $db->getRows($sql);
    if(!empty($res)){
        foreach($res as &$k){
            $k['Sales'] = '$'.number_format($k['Sales'],2);
            $k['Commission'] = '$'.number_format($k['Commission'],2);
        }
        echo json_encode($res);
    }else{
        echo 2;
    }
    die;
}
$d = new DateTime();
$etime = isset($_GET['tran_to'])&&$_GET['tran_to']?$_GET['tran_to']:$d->format('Y-m-d');
$stime  = isset($_GET['tran_from'])&&$_GET['tran_from']?$_GET['tran_from']:$d->modify('-3 day')->format('Y-m-d');
$sql = 'SELECT CountryName,CountryCode FROM country_codes';
$arr = $db->getRows($sql);
foreach($arr as $v){
    $countryArr[$v['CountryName']] = $v['CountryCode'];
}
$countryArr['global'] = 'Global';
$affname = $objOutlog->get_affname();
$affname[] = array('ID'=>-1,'Name'=>'Other');
$objTpl->assign('affname', $affname);
$objTpl->assign('countryArr', $countryArr);

$affiliateArr = array();
$sql = 'SELECT `Name`,`ID` FROM wf_aff WHERE isactive = "YES" ORDER BY Name asc';
$arr = $db->getRows($sql);
foreach($arr as $v){
    $affiliateArr[$v['ID']] = $v['Name'];
}
$objTpl->assign('affiliateArr', $affiliateArr);
$objTpl->assign('etime', $etime);
$objTpl->assign('stime', $stime);
$objTpl->assign('search', $_GET);
$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_header['css'][] = BASE_URL.'/css/chosen.min.css';
$sys_header['js'][] = BASE_URL.'/js/echarts.min.js';
$sys_footer['js'][] = BASE_URL.'/js/back.js';
$sys_footer['js'][] = BASE_URL.'/js/b_tran.js';
$objTpl->assign('title','Transaction');
$objTpl->assign('sys_footer', $sys_footer);
$objTpl->assign('sys_header', $sys_header);
$objTpl->display('b_transaction.html');
?>