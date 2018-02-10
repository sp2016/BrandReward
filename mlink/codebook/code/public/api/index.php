<?php
include_once(dirname(dirname(dirname(__FILE__))) . "/etc/const.php");
define("PAGE_DEBUG",false);
$oRequest = new Request();
$oMysql = new Mysql();
//$oStats = new Stats($oMysql);
$oAPI = new APIServer();
$oCBLoginAccounts = new CBLoginAccounts($oMysql);
$oCBLogin = new CBLogin();

//add tracking here
//$session_id = $oStats->setPageVisitLog($arr_request);
/*
	//note: following const will be defined after $oStats->setPageVisitLog()
	define("_CLIENT_CRAWLER_SESSION_ID",$session_id);
	define("_CLIENT_CRAWLER_CLIENT_IP",$client_ip);
	define("_CLIENT_CRAWLER_CLIENT_ID",$client_id);
*/
//main
$arr_request = array();
$arr_request["method"] = $oRequest->getStrNoSlashes("method");
$arr_request["ret_type"] = $oRequest->getStrNoSlashes("ret_type");
$arr_request["debug"] = $oRequest->getStrNoSlashes("debug");
$arr_request["client_id"] = trim($oRequest->getStrNoSlashes("client_id"));
$arr_request["token"] = trim($oRequest->getStrNoSlashes("token"));
$arr_request["client_version"] = $oRequest->getStrNoSlashes("client_version");

if($arr_request["client_id"] == "")
{
	$str_error = "client_id is required.";
	echo $oAPI->get_simple_error_result($arr_request["ret_type"],$str_error);
	exit;
}

$arr_account = $oCBLoginAccounts->get_account_by_userid($arr_request["client_id"]);
if(empty($arr_account))
{
	$str_error = "Invalid client id.";
	echo $oAPI->get_simple_error_result($arr_request["ret_type"],$str_error);
	exit;
}

$arr_no_token_method = array("GetMethods","GetTimestamp","GetVersion");
if(!in_array($arr_request["method"],$arr_no_token_method))
{
	if($arr_request["token"] == "")
	{
		$str_error = "token is required.";
		echo $oAPI->get_simple_error_result($arr_request["ret_type"],$str_error);
		exit;
	}

	$res = $oCBLogin->check_token($arr_request["token"],$arr_account["UserName"],$arr_account["UserAPITokenSalt"]);
	if(!$res)
	{
		$str_error = "Invalid token.";
		echo $oAPI->get_simple_error_result($arr_request["ret_type"],$str_error);
		exit;
	}
	$oCBLogin->account = $arr_account;
}

$method_file = API_ROOT . "method." . $arr_request["method"] . ".php";
if(file_exists($method_file))
{
	include_once($method_file);
	exit;
}
else
{
	$str_error = "Invalid method name";
	echo $oAPI->get_simple_error_result($arr_request["ret_type"],$str_error);
	exit;
}
exit;
?>