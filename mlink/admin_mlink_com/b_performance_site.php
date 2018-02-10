<?php
error_reporting(E_ALL^E_NOTICE^E_WARNING);
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init2.php');
$stime = date('Y-m-d',strtotime("-7 day"));
$etime = date('Y-m-d',time());
$objTran = new Transaction;
$objOutlog = new Outlog;
if(isset($_POST['table']) && !empty($_POST['table'])){
	$number = $_POST['order'][0]['column'];
	$page = $_POST['start'];
	$pagesize = $_POST['length'];
	$search = $_POST['search'];
	$search['order'] = $_POST['order'][0]['dir'];
	$search['oname'] = $_POST['columns'][$number]['data'];
	$search['type'] = $_POST['type'];
	$search['datatype'] = $_POST['datatype'];
	$search['timetype'] = $_POST['timetype'];
	$search['from'] = $_POST['stime'];
	$search['sitetype'] = $_POST['sitetype'];
	$search['to'] = $_POST['etime'];
	$search['status'] = $_POST['status'];
	$search['advertiser'] = $_POST['advertiser'];
	$search['manager'] = $_POST['manager'];
	$search['site'] = $_POST['site'];
	$country = '';
	$affiliate = '';
	if(!empty($_POST['country'])){
		foreach($_POST['country'] as $k){
			if($k == 'UK'){
				$country.='gb,uk,';
				continue;
			}
			$country.=strtolower($k).',';
		}
	}
	if(!empty($_POST['affiliate'])){
		foreach($_POST['affiliate'] as $k){
			$affiliate.=$k.',';
		}
	}
	$search['country'] = rtrim($country,',');
	$search['affiliate'] = rtrim($affiliate,',');
	$outLogTotal = $objTran->getTransactionRptSite($search,$page,$pagesize);
	$data = $outLogTotal['data'];
	$res['clicks'] = number_format($outLogTotal['sum_clicks']);
	$res['total'] = number_format($outLogTotal['sum_total']);
	$res['com'] =  "$".number_format($outLogTotal['sum_commission'],2);
	$res['sales'] =  "$".number_format($outLogTotal['sum_sales'],2);
	$res['order'] =  number_format($outLogTotal['sum_order']);
	$res['rob'] =  number_format($outLogTotal['sum_rob']);
	$res['robp'] =  number_format($outLogTotal['sum_robp']);
	$res['data'] = $data;
	$res['start'] = $page/$pagesize+1;
	$res['recordsFiltered'] = $outLogTotal['count'];
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
$affname = $objOutlog->get_affname();
$affname[] = array('ID'=>-1,'Name'=>'Other');
$objTpl->assign('affname', $affname);
$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_footer['js'][] = BASE_URL.'/js/back.js';
$sys_footer['js'][] = BASE_URL.'/js/b_performance.js';
$objTpl->assign('title', $title);
$objTpl->assign('stime', $stime);
$objTpl->assign('etime', $etime);
$objTpl->assign('title','Performance');
$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('sys_footer', $sys_footer);
$objTpl->display('b_performance_site.html');
?>
