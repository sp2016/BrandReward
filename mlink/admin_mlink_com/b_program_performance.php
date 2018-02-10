<?php
	/**
	 * Created by PhpStorm.
	 * User: Sky Ting
	 * Date: 2017/11/30
	 * Time: 17:12
	 */
	
	include_once('conf_ini.php');
	include_once('init.php');
	global $objTpl,$db;
	
	if(isset($_POST['table']) && !empty($_POST['table'])){
		$condition = "";
		if (!empty($_POST['program']))
			$condition = "where a.`Name` like '%" . addslashes($_POST['program']) ."%'";
		$order_condition = (isset($_POST['order']))?"order by {$_POST['columns'][$_POST['order'][0]['column']]['data']} {$_POST['order'][0]['dir']}":'';
		$sql = "select a.`ID`,a.`Name` Program,a.`IdInAff`,b.`IsActive`,a.`Homepage`,ifnull(sum(c.`Clicks_BR`),0) Clicks_BR,ifnull(sum(c.`Sales_BR`),0) Sales_BR,ifnull(sum(c.`Commission_BR`),0) Commission_BR,ifnull(sum(c.`Clicks_MK`),0) Clicks_MK,ifnull(sum(c.`Sales_MK`),0) Sales_MK,ifnull(sum(c.`Commission_MK`),0) Commission_MK from program a inner join program_intell b on a.`ID`=b.`ProgramId` left join (select * from program_performance where `CreatedDate` >='{$_POST['startDate']}' and `CreatedDate` <='{$_POST['endDate']}') c on c.`ProgramId`=a.`ID` $condition group by a.`ID` $order_condition limit {$_POST['start']},{$_POST['length']}";
		$programs = $db->getRows($sql);
	    $data['data'] = $programs;
        $sql = "select count(*) from program a inner join program_intell b on a.`ID`=b.`ProgramId` $condition";
	    $data['recordsFiltered'] = $db->getFirstRowColumn($sql);
	    echo json_encode($data,JSON_UNESCAPED_UNICODE);
	    die;
	}

	$sys_header['js'][] = BASE_URL.'/js/moment.js';
	$sys_header['js'][] = BASE_URL.'/js/dataTables.min.js';
	$sys_header['js'][] = BASE_URL.'/js/dataTables.semanticui.min.js';
	$sys_header['css'][] = BASE_URL.'/css/front.css';
	$sys_header['css'][] = BASE_URL.'/css/daterangepicker.css';
	$sys_header['js'][] = BASE_URL.'/js/moment.js';
	$sys_header['js'][] = BASE_URL.'/js/daterangepicker.js';
	$sys_header['css'][] = BASE_URL . "/css/bootstrap.min.css";
	$sys_header['css'][] = BASE_URL . '/css/bootstrap-select.min.css';
	$sys_header['css'][] = BASE_URL . '/css/semantic.min.css';
	$sys_header['css'][] = BASE_URL . '/css/dataTables.semanticui.min.css';
	$sys_header['css'][] = BASE_URL . "/css/charisma-app.css";
	$sys_header['css'][] = BASE_URL . '/css/chosen.min.css';
	$sys_header['js'][] = BASE_URL . '/js/bootstrap-select.min.js';
	$objTpl->assign('sys_header', $sys_header);
    $objTpl->display('b_program_performance.html');
