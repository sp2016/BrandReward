<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');




if(isset($_POST['add_domain'])){
	$d = new DateTime();
	$timeNow = $d->format("Y-m-d H:i:s");
	$add_domain = trim($_POST['add_domain']);
	$query = 'SELECT ID FROM domain WHERE Domain ="'.$add_domain.'"';
	$result = mysql_query($query);
	$arr = mysql_fetch_array($result);
	$domain_id = $arr[0];
	$add_program = trim($_POST['add_program']);
	$query = 'SELECT ID FROM program WHERE Name ="'.$add_program.'"';
	$result = mysql_query($query);
	$arr = mysql_fetch_array($result);
	$program_id = $arr[0];
	
	
	if(isset($_SERVER['PHP_AUTH_USER'])){
		$user = $_SERVER['PHP_AUTH_USER'];
	}else{
		$user = 'test';
	}
	$status = $_POST['add_status'];
	$add[]=array();
	$add['DomainId'] = $domain_id;
	$add['ProgramId'] = $program_id;
	$add['Status'] = $status;
	$add['AddUser'] = $user;
	$add['AddTime'] = $timeNow;
	$add['LastUpdateTime'] = $timeNow;
	$table_name = "r_domain_program_ctrl";
	
	
	//js判断add条件之后，再次通过php判断
	if($status=="Active"){
		$sql = 'SELECT COUNT(*) AS count FROM r_domain_program_ctrl a inner join domain b on a.DomainId = b.ID  WHERE  b.Domain  ="'.$add_domain.'" AND a.Status = "Active"';
		
		$count = mysql_query($sql);
		$result = mysql_fetch_array($count,MYSQL_ASSOC);
		if($result['count']==0){
			update_add($table_name, $add);
		}
	}elseif($status=="Inactive"){

		update_add($table_name, $add);
	}




}




if(isset($_POST['edit_id'])){
	$d = new DateTime();
	$timeNow = $d->format("Y-m-d H:i:s");
	$edit[] = array();
	$edit_domain = trim($_POST['edit_domain']);
	$query = 'SELECT ID FROM domain WHERE Domain ="'.$edit_domain.'"';
	$result = mysql_query($query);
	$arr = mysql_fetch_array($result);
	$edit_domain_id = $arr[0];
	
	$edit_program = trim($_POST['edit_program']);
	$query = 'SELECT ID FROM program WHERE Name ="'.$edit_program.'"';
	$result = mysql_query($query);
	$arr = mysql_fetch_array($result);
	$edit_program_id = $arr[0];
	
	$where = 'ID = '.$_POST['edit_id'];
	$status = $_POST['edit_status'];
	$edit['DomainId'] = $edit_domain_id;
	$edit['ProgramId'] = $edit_program_id;
	$edit['Status'] = $_POST['edit_status'];
	$edit['LastUpdateTime'] = $timeNow;
	$table_name = "r_domain_program_ctrl";
	
	//js判断edit条件之后，再次通过php判断
	if($status=="Active"){
	$sql = 'SELECT COUNT(*) AS count FROM r_domain_program_ctrl a inner join domain b on a.DomainId = b.ID  WHERE  b.Domain  ="'.$_POST['edit_domain'].'" AND `Status` = "Active" AND a.ID <> "'.$_POST['edit_id'].'"';
	
	$count = mysql_query($sql);
	$result = mysql_fetch_array($count,MYSQL_ASSOC);
		
		
	if($result['count']==0){
		update_edit($table_name, $edit, $where);
		
	}
	}elseif($status=="Inactive"){

		update_edit($table_name, $edit, $where);
	}
	
	
	

}
















$page = isset($_GET['p'])?$_GET['p']:1;
$objTrans = new Domain;
$PDCTotal = $objTrans->getPDCListPage($_GET,$page);

$PDCList = $PDCTotal['data'];  //满足搜索条件和页数的所有记录
unset($PDCTotal['data']);
$pageHtml = get_page_html($PDCTotal);//分页栏

$objTpl->assign('PDCList', $PDCList);

$objTpl->assign('pageHtml', $pageHtml);
$objTpl->assign('search', $_GET);




$sys_header['css'][] = BASE_URL.'/css/front.css';
$objTpl->assign('sys_header', $sys_header);
$objTpl->display('b_pdc.html');
