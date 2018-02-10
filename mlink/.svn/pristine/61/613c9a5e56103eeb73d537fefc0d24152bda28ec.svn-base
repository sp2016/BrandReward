<?php



include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');
mysql_query("SET NAMES 'latin1'");

/*帐密修改---start---*/
$Account = new Crawl();
if(isset($_POST['Account']) && isset($_POST['Password'])){
    $retrun = $Account->doUpdateAffiliateAccount($_POST);
    $retrun = json_encode($retrun,true);
    echo $retrun;
    return;
}
/*帐密修改---end---*/


if(isset($_POST)&&!empty($_POST)){
	$edit = array();
	foreach($_POST as $k => $val){
		if($k !== 'Country'){          //把除了Country以外的传值，清除首位空值
		$edit[$k] = trim($val);
		}
	}
    $edit['id'] = $_GET['id'];
	if(isset($_POST['Country']) && !empty($_POST['Country'])){
		$value = $_POST['Country'];
		$country = implode('||', $value);	
		$edit['Country']=$country;        //更新Country字段，形成新的传值数组
	}	
	$table = 'wf_aff';
	$where = 'Id ='.$_GET['id'];
	$obj = new Affiliates();
	$obj->edit_aff($table,$edit,$where);

}









$d = new DateTime();
$timeNow = $d->format("Y-m-d H:i:s");


$action=$_GET['action'];
$id=$_GET['id'];
$query='SELECT * FROM wf_aff WHERE id="'.$id.'"';
//print_r($query);
$result=mysql_query($query);
$arr = mysql_fetch_array($result);

$mQuery = "SELECT `Manager` FROM `wf_aff` WHERE `Manager` IS NOT NULL GROUP BY `Manager`";
$mResult =mysql_query($mQuery);
$managers = array();
while($row = mysql_fetch_array($mResult,MYSQL_ASSOC))
{
	isset($row['Manager']) && array_push($managers, $row['Manager']);
}


$countryArr = explode('||', $arr['Country']);
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





if(!$Account->checkLimit()){
    $arr['Password'] = "********";
}

$objTpl->assign("fin_rev_acc_list", $fin_rev_acc_list);




$objTpl->assign('action', $action);

$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_header['css'][] = BASE_URL.'/css/table.css';
$objTpl->assign('id', $id);
$objTpl->assign('countryArr', $countryArr);
$objTpl->assign('arr', $arr);
$objTpl->assign('timeNow', $timeNow);
$objTpl->assign('managers', $managers);
$objTpl->assign('sys_header', $sys_header);
$objTpl->display('edit_affiliates.html');
?>