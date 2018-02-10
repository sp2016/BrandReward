<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init2.php');

$stime = date('Y-m-d',strtotime("-7 day"));
$etime = date('Y-m-d',time());

$objOutlog = new Outlog;
if(isset($_POST['table']) && !empty($_POST['table'])){
    $page = $_POST['start'];
    $pagesize = $_POST['length'];
    $search = $_POST['search'];
    $search['from'] = $_POST['stime'];
    $search['to'] = $_POST['etime'];
    $search['advertiser'] = $_POST['advertiser'];
    $search['linkid'] = $_POST['linkid'];
    $search['pid'] = $_POST['site'];
    $search['type'] = $_POST['type'];
    $search['sitetype'] = $_POST['sitetype'];
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
    $search['download'] = 0;
    $search['affiliate'] = rtrim($affiliate,',');
    $DomainTotal = $objOutlog->get_out_going_log_data($search,$page,$pagesize);
    $DomainList = $DomainTotal['data'];
    if(!empty($DomainTotal['cinfo'])){
        foreach($DomainTotal['cinfo'] as $k){
            $res['name'][] = $k['name'];
            $res['c'][] = $k['country'];
            $res['click'][] = $k['total']-$k['rob'];
            $res['crobp'][] = $k['robp'];
            $res['crob'][] = $k['rob'];
        }
    }
    $res['cinfo'] = !empty($DomainTotal['cinfo'])?$DomainTotal['cinfo']:'';
    $res['data'] = $DomainList;
    $res['start'] = $page/$pagesize+1;
    $res['jumps'] = number_format($DomainTotal['clicks']);
    $res['rob'] = number_format($DomainTotal['rob']);
    $res['robp'] = number_format($DomainTotal['robp']);
    $res['commission'] =  "$".number_format($DomainTotal['sum']['commission'],2);
    $res['sales'] =  "$".number_format($DomainTotal['sum']['sales'],2);
    $res['recordsFiltered'] = $DomainTotal['total_num'];
    echo json_encode($res);
    die;
}

$sql = 'SELECT CountryName,CountryCode FROM country_codes';
$arr = $db->getRows($sql);
foreach($arr as $v){
    $countryArr[$v['CountryName']] = $v['CountryCode'];
}
$countryArr['global'] = 'Global';
$countryArr['United Kingdom'] = 'UK';
$objTpl->assign('countryArr', $countryArr);

$title = "OutLog";
$affname = $objOutlog->get_affname();
$affname[] = array('ID'=>-1,'Name'=>'Other');

$objTpl->assign('affname', $affname);

$objTpl->assign('search', $_GET);
$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_header['js'][] = BASE_URL.'/js/echarts.min.js';
$sys_footer['js'][] = BASE_URL.'/js/back.js';
$objTpl->assign('title', $title);
$objTpl->assign('stime', $stime);
$objTpl->assign('etime', $etime);
$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('sys_footer', $sys_footer);
$objTpl->display('b_outlog.html');
?>
