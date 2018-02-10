<?php
	include_once('conf_ini.php');
	include_once(dirname(__FILE__).'/'.'init3.php');
	global $db,$objTpl,$sys_header,$sys_footer;
	mysql_query("SET NAMES UTF8");
	
	if(isset($_POST['table']) && !empty($_POST['table'])){
		//delete these content feed log that is inactive
		$sql = "DELETE a.* FROM check_aff_url a, content_feed_new b WHERE a.`ContentFeedId` = b.`ID` AND b.`Status` = 'InActive' AND a.Correct = 'Unknown'";
		$db->query($sql);
		
		$page = $_POST['start'];
		$pagesize = $_POST['length'];
		$condition = '';
		$sql = "select * from check_aff_url where Correct='Unknown' $condition ORDER BY `AddTime` Limit $page,$pagesize";
		$error_list = $db->getRows($sql);
		$sql = "SELECT COUNT(1) FROM check_aff_url a where Correct='Unknown' $condition ";
		$res['recordsFiltered'] = $db->getFirstRowColumn($sql);
		$res['data'] = $error_list;
		$res['start'] = $page/$pagesize+1;
		echo json_encode($res);
		die;
	}
	

	$sys_header['js'][] = BASE_URL.'/js/dataTables.min.js';
	$sys_header['js'][] = BASE_URL.'/js/dataTables.semanticui.min.js';
	$objTpl->assign('title','Check content log');
	$objTpl->assign('sys_header', $sys_header);
	$objTpl->assign('sys_footer', $sys_footer);
	$objTpl->display('b_check_content.html');
?>