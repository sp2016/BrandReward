<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');

$uploadFile = array();
$up_result = array();
$go_result = array();

$objTran = new Transaction();
if(isset($_REQUEST['act'])){

	$upload_data = array();
	$upload_data['dir'] = DATA_TRANSACTION_UPLOAD_PATH;
	

	if($_REQUEST['act'] == 'show_info'){
		#upload files
		$uploadFile = do_upload_file($upload_data);
		#save files into info db
		$up_result = $objTran->get_upload_info($uploadFile);
	}elseif($_REQUEST['act'] == 'go_db'){
		$go_result = $objTran->save_upload_data();
	}elseif($_REQUEST['act'] == 'clear'){
		$objTran->clear_upload_tmp();
	}
}

$history = $objTran->table('rpt_transaction_upload')->order('id desc')->find();


$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_header['js'][] = BASE_URL.'/js/b_account.js';
$objTpl->assign('history', $history);
$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('up_result', $up_result);
$objTpl->assign('go_result', $go_result);
$objTpl->assign('_GET', $_GET);

$objTpl->display('b_tools_upload_transaction.html');
?>