<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init_back.php');
check_user_login();
include_once('auth_ini.php');

$objAccount = new Account();

if(isset($_POST['action']) && $_POST['action'] == 'ajax_trigger'){
	$StoreId = intval($_POST['StoreId']);
	$PAId = intval($_POST['PAId']);
	$ID = intval($_POST['ID']);
	$Status = addslashes(trim($_POST['Status']));
	if($StoreId > 0 && $PAId > 0){
		$sql = 'REPLACE INTO r_store_publisher_ctr SET StoreId = '.$StoreId.',PAId = '.$PAId.',Status="'.$Status.'",ID='.$ID;	
		$objAccount->query($sql);

		$sql = 'select * from r_store_publisher_ctr where StoreId = '.$StoreId.' AND Status = "inactive"';
		$rows = $objAccount->getRows($sql);

		$sql = 'delete from block_relationship where ObjType = "Store" AND ObjId = '.$StoreId;
		$objAccount->query($sql);
		if(!empty($rows)){
			foreach($rows as $k=>$v){
				$sql = 'INSERT INTO block_relationship  SET BlockBy = "Merchant",AccountId = '.$v['PAId'].',ObjId ='.$StoreId.',ObjType = "Store",`Status` = "Active",AddTime = "'.date('Y-m-d H:i:s').'"';
				$objAccount->query($sql);
			}
		}
		
	}
exit();
}

list(,$storeid) = explode('_',$_SESSION['u']['Name']);

$spactr_list = $objAccount->get_spactr_list($storeid,$_GET);

$objTpl->assign('search', $_GET);
$objTpl->assign('spactr_list', $spactr_list);

$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_footer['js'][] = BASE_URL.'/js/back.js';
$sys_footer['js'][] = BASE_URL.'/js/b_performance.js';

$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('sys_footer', $sys_footer);
$objTpl->assign('sys_userinfo', $USERINFO);
$objTpl->display('b_ad_traffic_ctr.html');


?>
