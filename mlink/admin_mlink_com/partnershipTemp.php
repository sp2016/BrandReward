<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');
 
$where = '1=1'; 
$date =  $_POST['sdate'] = isset($_POST['sdate']) ? $_POST['sdate'] : date('Y-m-d',strtotime('-7 days'));
$date = trim($date);
if($date){
    $where .= " and a.AddTime >= '$date 00:00:00'";
}
$edate = $_POST['edate'] = isset($_POST['edate']) ? $_POST['edate'] : date('Y-m-d');
$edate = trim($edate);
if($edate){
    $where .= " and a.AddTime <= '$edate 23:59:59'";
}

$program = isset($_POST['program']) ? $_POST['program'] : '';
$program = trim($program);
if($program){
    $where .= " and a.name like '%$program%'";
}

$network = isset($_POST['network']) ? $_POST['network'] : '';
$network = trim($network);
if($network){
    $where .= " and a.affname like '%$network%'";
}
$country = isset($_POST['country']) ?  $_POST['country'] : '';

if (!empty($country)) {
    foreach ($country as $ct) {
        $ct = strtolower(trim($ct));
        $where .= " and  FIND_IN_SET('$ct',b.`ShippingCountry`)";
    }
}
$status = isset($_POST['status']) ? $_POST['status'] : '';
if($status){
    $where .= " and b.SupportType = '$status'";
}
$homepage = isset($_POST['homepage']) ? $_POST['homepage'] : '';
$homepage = trim($homepage);
if($homepage){
    $where .= " and a.homepage like '%$homepage%'";
}
$merchant = new MerchantExt();
$sql_names_set = 'SET NAMES latin1';
$merchant->query($sql_names_set);
//$aff = $objTran->table('wf_aff')->find();
$tsql = "select COUNT(distinct a.Name) AS `total` from temp_partership a LEFT JOIN program_intell b ON b.ProgramId = a.ProgramId  where $where";
$trow = $merchant->getRow($tsql);
$sql = "select distinct a.Name,a.AffName,a.Homepage,a.AddTime,b.SupportType,a.ProgramID,b.CommissionType,b.CommissionUsed,b.CommissionCurrency from temp_partership a LEFT JOIN program_intell b ON b.ProgramId = a.ProgramId  where $where";
$list = array();
$list = $merchant->getRows($sql);
//统计commission
foreach($list as &$item)
{
    $programId = isset($item['ProgramID']) ? $item['ProgramID'] : '';
    if (empty($programId)) {
        continue;
    }
    $cType = isset($item['CommissionType']) ? $item['CommissionType'] : '';
    $cValue = isset($item['CommissionUsed']) ? $item['CommissionUsed'] : 0;
    $cCurrent = isset($item['CommissionCurrency']) ? $item['CommissionCurrency'] : 0;
    switch ($cType) {
        case  'Percent' :
            $item['cValue'] = $cValue . '%';
            break;
        case  'Value' :
            $item['cValue'] = !empty($cCurrent) ? $cCurrent . $cValue : $cValue;
            break;
        default :
            $item['cValue'] = '$' . $cValue ;
    }


    $sWhere = '';
    $tsdate =  $_POST['tsdate'] = isset($_POST['tsdate']) ? $_POST['tsdate'] : date('Y-m-d',strtotime('-7 days'));
    if(!empty($tsdate)){
        $sWhere .= " AND a.createddate >= '$tsdate'";
    }
    $tedate = $_POST['tedate'] = isset($_POST['tedate']) ? $_POST['tedate'] : date('Y-m-d');
    if(!empty($tedate)){
        $sWhere .= " AND a.createddate <= '$tedate'";
    }
    $sSql = "SELECT IFNULL(SUM(clicks),'0') AS `clicks`,IFNULL(SUM(`orders`),'0') AS `orders`,IFNULL(SUM(`sales`),'0.00') AS `sales`,IFNULL(SUM(`revenues`),'0.00') AS `revenues` FROM statis_program_br AS `a` WHERE 1 $sWhere AND ProgramId = $programId";
    $sRow = $merchant->getRow($sSql);

    $item = array_merge($item,$sRow);
}


$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_header['js'][] = BASE_URL.'/js/b_account.js';
$sys_header['js'][] = BASE_URL.'/js/Chart.js';

$sys_footer['js'][] = BASE_URL.'/js/back.js';
$sys_footer['js'][] = BASE_URL.'/js/b_performance.js';


//print_r($affiList);exit;
$objTpl->assign('list', $list);

$sys_header['css'][] = BASE_URL.'/css/DateTimePicker.css';
$sys_footer['js'][] = BASE_URL.'/js/DateTimePicker.js';
$objTpl->assign('search', $_POST);
$sql = 'SELECT CountryName,CountryCode FROM country_codes';
$arr = $db->getRows($sql);
foreach($arr as $v){
    $countryArr[$v['CountryName']] = $v['CountryCode'];
}
$countryArr['global'] = 'Global';
$countryArr['United Kingdom'] = 'UK';
$objTpl->assign('countryArr', $countryArr);
$objTpl->assign('country', $country);
$objTpl->assign('title','Advertiser partnership - Active');
$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('sys_footer', $sys_footer);
$objTpl->display('partnershipTemp.html');

?>