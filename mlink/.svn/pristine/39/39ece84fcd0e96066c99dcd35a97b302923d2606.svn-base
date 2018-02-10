<?php
	/**
	 * Created by PhpStorm.
	 * User: mding
	 * Date: 2017/07/31
	 * Time: 10:45
	 */
	
	include_once('conf_ini.php');
	include_once(dirname(__FILE__).'/'.'init3.php');
	global $db,$objTpl,$sys_header,$sys_footer;
	mysql_query("SET NAMES UTF8");
	
	if(isset($_POST['table']) && !empty($_POST['table'])){
		$page = $_POST['start'];
		$pagesize = $_POST['length'];
		$sql = "select `ID`,`PID`,`Old`,`New`,`Checked` from check_homepage_log ORDER BY `ID` asc Limit $page,$pagesize";
		$list = $db->getRows($sql);
		$sql = "SELECT COUNT(1) FROM check_homepage_log";
		$count = $db->getFirstRowColumn($sql);
		$res['data'] = $list;
		$res['start'] = $page/$pagesize+1;
		$res['recordsFiltered'] = $count;
		echo json_encode($res);
		die;
	}
	
	$sys_header['js'][] = BASE_URL.'/js/dataTables.min.js';
	$sys_header['js'][] = BASE_URL.'/js/dataTables.semanticui.min.js';
	$objTpl->assign('title','Check homepage');
	$objTpl->assign('sys_header', $sys_header);
	$objTpl->assign('sys_footer', $sys_footer);
	$objTpl->display('b_check_homepage.html');
?>