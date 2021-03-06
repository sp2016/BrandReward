<?php

include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');
mysql_query("SET NAMES 'latin1'");
if (isset($_POST) && !empty($_POST)) {
	$add = array();
	foreach ($_POST as $k => $val) {
		if ($k !== 'Country') {          //把除了Country以外的传值，清除首位空值
			$add[$k] = trim($val);
		}
	}
	$country = "";
	if (isset($_POST['Country'])) {
		$value = $_POST['Country'];
		$country = implode('||', $value);
	}
	$table = 'wf_aff';
	$add['Country'] = $country;        //更新Country字段，形成新的传值数组
	$obj = new Affiliates();
	$obj->add_aff($table, $add);
}


$fin_rev_acc_list = $tmpdata = array();
$query = "select * from `fin_rev_acc` order by Name";
$result=mysql_query($query);
while($row = mysql_fetch_array($result,MYSQL_ASSOC))
{
	$tmpdata[] = $row;
}
foreach ($tmpdata as $v){
	$fin_rev_acc_list[$v['ID']]=$v['Name'];
}
$objTpl->assign("fin_rev_acc_list", $fin_rev_acc_list);


$title = 'Network List';
$page = isset($_GET['p'])?$_GET['p']:1;
$limit = isset($_GET['limit'])?$_GET['limit']:20;
$objTrans = new Transaction;
$AffTotal = $objTrans->getAffiliatesListPage($_GET,$page,$limit);

$affids = array();
foreach($AffTotal['data'] as $v){
	$affids[] = $v['Id']; 
}
$data = array();
$data['affid'] = $affids;
$data['from'] = date('Y-m-d',strtotime('-30 day'));
$data['to'] = date('Y-m-d',strtotime('-1 day'));
$affComm30 = $objTrans->getAffiliateCommission($data);

$data = array();
$data['affid'] = $affids;
$data['from'] = date('Y-m-d',strtotime('-30 day'));
$data['to'] = date('Y-m-d',strtotime('-1 day'));
$affClick30 = $objTrans->getAffiliateClick($data);


$data = array();
$data['affid'] = $affids;
$data['from'] = date('Y-m-d',strtotime('-60 day'));
$data['to'] = date('Y-m-d',strtotime('-31 day'));
$affComm60 = $objTrans->getAffiliateCommission($data);


$AffList = $AffTotal['data'];  //满足搜索条件和页数的所有记录
$crawl = new Crawl();
if(!$crawl->checkLimit()){
    foreach($AffList as &$value){
        $value['Password'] = "********";
    }
}

unset($AffTotal['data']);
$pageHtml = get_page_html($AffTotal);

$objTpl->assign('AffList', $AffList);
$objTpl->assign('affClick30', $affClick30);
$objTpl->assign('affComm30', $affComm30);
$objTpl->assign('affComm60', $affComm60);

$objTpl->assign('pageHtml', $pageHtml);
$_GET['limit'] = $limit;
$objTpl->assign('search', $_GET);
$objTpl->assign('title', $title);

$sys_header['css'][] = BASE_URL.'/css/front.css';

$objTpl->assign('sys_header', $sys_header);
$objTpl->display('b_aff_aff.html');
?>
