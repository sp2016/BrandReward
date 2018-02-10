<?php
global $_install;

if(!isset($_req['act']))
	over('@error:act is empty');

if(isset($_install[$_req['act']]) && api_exist($_req)){
	security_check($_install[$_req['act']]['sl']);
	api_load($_req['act']);
}else{
	over('@error:api is not exist');
}

function security_check($sl){
	global $_req;

	switch ($sl) {
		case 'low':
			if(!isset($_req['key']) || !check_key($_req['key']) )
				over('@error:key is wrong or inactive');
			break;
		case 'high':
			if(!isset($_req['key']) || !isset($_req['user']) || !check_user_key($_req['user'],$_req['key']) )
				over('@error:key or user is wrong or inactive');
			break;
		case 'system':
			if(!isset($_req['mlink_root_sudo']))
				over('@error:Permission denied');
			break;	
		default:
			break;
	}
}

function check_key($key){
	global $_db,$_user;
	$sql = 'SELECT * FROM publisher AS p LEFT JOIN publisher_account AS pa ON p.`ID` = pa.`PublisherId` WHERE pa.`ApiKey` = "'.addslashes($key).'" AND p.`Status` = "Active" AND pa.`Status` = "Active"';
	$row = $_db->getRows($sql);
	if($row){
                $_user = $row[0];
		return true;
	}else{
		return false;
	}
}

function check_user_key($user,$key){
	global $_db,$_user;
	$sql = 'SELECT * FROM publisher AS p LEFT JOIN publisher_account AS pa ON p.`ID` = pa.`PublisherId` WHERE p.`UserName` = "'.addslashes($user).'" AND pa.`ApiKey` = "'.addslashes($key).'" AND p.`Status` = "Active" AND pa.`Status` = "Active"';
	$row = $_db->getRows($sql);
	if($row){
                $_user = $row[0];
		return true;
	}else{
		return false;
	}
}

function api_list(){
	$apiList = array();

	$appList = getDir(APP_ROOT,'dir','1');

	foreach($appList as $v){
		$dir = APP_ROOT.'/'.$v;
		$apiList[$v] = getDir($dir,'file','1');
	}
	return $apiList;
}

function api_exist($request){
	if(isset($request['act'])){
		list($app,$file) = explode('.',$request['act']);
		$file = $file.'.php';

		$apiList = api_list();
		if(!isset($apiList[$app])){
			return false;
		}

		if(!in_array($file, $apiList[$app])){
			return false;
		}

		$file_path = api_file_path($request['act']);
		if(!file_exists($file_path)){
			return false;
		}

		return true;
	}else{
		return false;
	}
}

function api_load($act){
        save_api_log();
	$file_path = api_file_path($act);
	include_once $file_path;
}

function api_file_path($act){
	list($app,$file) = explode('.',$act);
	$file = $file.'.php';
	$path = APP_ROOT.'/'.$app.'/'.$file;
	return $path;
}

function save_api_log(){
	global $_user,$_req,$_db;
	if(!empty($_user)){
		$sql = "INSERT INTO logapi_publisher (publisherid,site,act,param,addtime,updatetime) value (".intval($_user['PublisherId']).",'".addslashes($_user['ApiKey'])."','".addslashes($_req['act'])."','".$_SERVER['QUERY_STRING']."','".date('Y-m-d H:i:s')."','".date('Y-m-d H:i:s')."') on duplicate key update param=values(param),updatetime=values(updatetime)";

		$_db->query($sql);
	}
}
?>
