<?php
	include_once('conf_ini.php');
	include_once('init3.php');
	global $db,$objTpl,$sys_footer;
	
	if(isset($_POST['table']) && !empty($_POST['table'])){
		$page = $_POST['start'];
		$pagesize = $_POST['length'];
		$sql = "select count(1) as count  from check_aff_url";
		$count = $db->getFirstRowColumn($sql);
		
		$sql = "select * from check_aff_url limit $page,$pagesize";
		$data = $db->getRows($sql);
		
		$res['data'] = $data;
		$res['start'] = $page/$pagesize+1;
		$res['recordsFiltered'] = $count;
		echo json_encode($res);
		die;
	}

	$sys_header['js'][] = BASE_URL.'/js/dataTables.min.js';
	$sys_header['js'][] = BASE_URL.'/js/dataTables.semanticui.min.js';
	$objTpl->assign('sys_header', $sys_header);
	$objTpl->assign('sys_footer', $sys_footer);
	$objTpl->assign("title","Content Check Log");
	$objTpl->display('b_content_check_log.html');