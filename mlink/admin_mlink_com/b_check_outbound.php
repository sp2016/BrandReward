<?php
	include_once('conf_ini.php');
	include_once(dirname(__FILE__).'/'.'init3.php');
	global $db,$objTpl,$sys_header,$sys_footer;
	mysql_query("SET NAMES UTF8");
	
	if(isset($_POST['table']) && !empty($_POST['table'])){
		$validTime = date("Y-m-d H:i:s",strtotime("-1 month"));
		$page = $_POST['start'];
		$pagesize = $_POST['length'];
		$condition = ($_POST['network'] == 'All') ? '':" and a.Affid='{$_POST['network']}'";
		$condition .= ($_POST['status'] == 'All') ? '' : " and a.Correct='{$_POST['status']}'";
		$condition .= ($_POST['type'] == 'All') ? '' : " and a.ErrorType='{$_POST['type']}'";
		$sql = "select a.ID,a.`PID`,b.`Name` Network,a.`UrlOrTpl`,a.`Origin`,a.`Dealt`,a.`ErrorType`,a.`UpdateTime`,a.`Correct`,c.`Name` Program,c.`Homepage`,a.HttpCode,a.Alternative,a.Confirmed from check_outbound_log a inner join wf_aff b on a.`Affid`=b.`ID` inner join program c on c.`ID`=a.`PID` where a.`UpdateTime`>='{$validTime}' and a.`OverDate`>='NO'  $condition ORDER BY a.`ID` Limit $page,$pagesize";
		$error_list = $db->getRows($sql);
		$sql = "SELECT COUNT(1) FROM check_outbound_log a where a.`UpdateTime`>='{$validTime}'$condition ";
		$count = $db->getFirstRowColumn($sql);
		$res['data'] = $error_list;
		$res['start'] = $page/$pagesize+1;
		$res['recordsFiltered'] = $count;
		echo json_encode($res);
		die;
	}
	
	$sql = "select distinct a.ID,a.`Name` from wf_aff a inner join check_outbound_log b on a.ID=b.Affid";
	$networks = $db->getRows($sql);
	$sql = "select distinct ErrorType from check_outbound_log";
	$error_types = $db->getRows($sql);
	$sql = "select distinct Correct from check_outbound_log";
	$corrects = $db->getRows($sql);
	$objTpl->assign('networks', $networks);
	$objTpl->assign('error_types', $error_types);
	$objTpl->assign('corrects', $corrects);
	$sys_header['js'][] = BASE_URL.'/js/dataTables.min.js';
	$sys_header['js'][] = BASE_URL.'/js/dataTables.semanticui.min.js';
	$objTpl->assign('title','Check outbound log');
	$objTpl->assign('sys_header', $sys_header);
	$objTpl->assign('sys_footer', $sys_footer);
	$objTpl->display('b_check_outbound.html');
?>