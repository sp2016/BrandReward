<?php
	/**
	 * Created by PhpStorm.
	 * User: Sky Ting
	 * Date: 2017/11/23
	 * Time: 15:17
	 */
	
	include_once('conf_ini.php');
	include_once('init.php');
	global $objTpl,$db;
	$sql = "select DateRange from ( select distinct DateRange from store_no_commission a union select distinct DateRange from program_no_commission b union select distinct DateRange from store_in_subaff c ) d order by substring_index(DateRange, '~', 1) desc";
	$dateRange = $db->getRows($sql);
	$sql = "select `ID`,`Name` from wf_aff order by `Name`";
	$networks = $db->getRows($sql,'ID');
	if(isset($_POST['table']) && !empty($_POST['table'])){
		if($_POST['table'] == 'sub_network'){
			$network_condition = ($_POST['network'] != 0)?"and find_in_set({$_POST['network']},a.Affids)":'';
			$order_condition = (isset($_POST['order']))?"order by {$_POST['columns'][$_POST['order'][0]['column']]['data']} {$_POST['order'][0]['dir']}":'';
		    $sql = "select a.*,b.`Name` Advertiser from store_in_subaff a left join store b on a.`StoreID`=b.`ID` where a.DateRange='{$_POST['dateRange']}' $network_condition $order_condition limit {$_POST['start']},{$_POST['length']}";
		    $stores = $db->getRows($sql);
			foreach ($stores as $key=>$store){
		        $network = '';
				if(!empty($store['AffIds'])){
					$tmp = explode(',',$store['AffIds']);
					foreach ($tmp as $affid){
						$sql = "select * from r_store_domain a inner join r_domain_program b on a.`DomainId`=b.`DID` inner join program c on b.`PID`=c.`ID` where c.`AffId`='{$affid}' and a.`StoreId`='{$store['StoreID']}' AND a.Status = 'Active' and c.`StatusInAff`='Active' and c.`Partnership`!='Active'";
						if($db->getRows($sql))
							$network .= $networks[$affid]['Name'] . ',';
					}
				}
				$network = trim($network,',');
				$stores[$key]['Networks'] = $network;
			}
			$data['data'] = $stores;
		    $sql = "SELECT COUNT(1) FROM store_in_subaff a where DateRange='{$_POST['dateRange']}' $network_condition";
		    $data['recordsFiltered'] = $db->getFirstRowColumn($sql);
		    $data['start'] = $_POST['start']/$_POST['length']+1;
		    echo json_encode($data,JSON_UNESCAPED_UNICODE);
		} else if ($_POST['table'] ==  'no_partnership'){
			$network_condition = ($_POST['network'] != 0)?"and find_in_set({$_POST['network']},a.Affids)":'';
			$order_condition = (isset($_POST['order']))?"order by {$_POST['columns'][$_POST['order'][0]['column']]['data']} {$_POST['order'][0]['dir']}":'';
			$sql = "select a.*,b.`Name` Advertiser from store_no_commission a left join store b on a.`StoreID`=b.`ID` where a.DateRange='{$_POST['dateRange']}' $network_condition $order_condition limit {$_POST['start']},{$_POST['length']}";
		    $stores = $db->getRows($sql);
			foreach ($stores as $key=>$store){
		        $network = '';
				if(!empty($store['AffIds'])){
					$tmp = explode(',',$store['AffIds']);
					foreach ($tmp as $affid){
						$network .= $networks[$affid]['Name'] . ',';
					}
				}
				$network = trim($network,',');
				$stores[$key]['Networks'] = $network;
			}
			$data['data'] = $stores;
		    $sql = "SELECT COUNT(1) FROM store_no_commission a where DateRange='{$_POST['dateRange']}' $network_condition";
		    $data['recordsFiltered'] = $db->getFirstRowColumn($sql);
		    $data['start'] = $_POST['start']/$_POST['length']+1;
		    echo json_encode($data,JSON_UNESCAPED_UNICODE);
		} else if( $_POST['table'] == 'no_commission'){
			$network_condition = ($_POST['network'] != 0)?"and b.Affid={$_POST['network']}":'';
			$order_condition = (isset($_POST['order']))?"order by {$_POST['columns'][$_POST['order'][0]['column']]['data']} {$_POST['order'][0]['dir']}":'';
			$sql = "select a.*,b.`Name` Program,b.AffId from program_no_commission a left join program b on a.`ProgramID`=b.`ID` where a.DateRange='{$_POST['dateRange']}' $network_condition $order_condition limit {$_POST['start']},{$_POST['length']}";
		    $programs = $db->getRows($sql);
			foreach ($programs as $key=>$program){
		        $network = '';
				if(!empty($program['AffId'])){
					$network = $networks[$program['AffId']]['Name'];
				}
				$programs[$key]['Networks'] = $network;
			}
			$data['data'] = $programs;
		    $sql = "SELECT COUNT(1) FROM program_no_commission a left join program b on a.`ProgramID`=b.`ID` where DateRange='{$_POST['dateRange']}' $network_condition";
		    $data['recordsFiltered'] = $db->getFirstRowColumn($sql);
		    $data['start'] = $_POST['start']/$_POST['length']+1;
		    echo json_encode($data,JSON_UNESCAPED_UNICODE);
		}
	    die;
	}
	$objTpl->assign('dataRange', $dateRange);
	$objTpl->assign('networks',$networks);
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
    $objTpl->display('b_store_weekly_report.html');

?>
