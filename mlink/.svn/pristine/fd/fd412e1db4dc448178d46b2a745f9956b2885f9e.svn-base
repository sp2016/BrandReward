<?php
if(!isset($oAPI) || !is_object($oAPI)) die("access deined.");

$user_id = $oRequest->getStr('user_id');
$user_name = $oRequest->getStr('user_name');
if(empty($user_id) || empty($user_name))
{
	$str_error = "parameter missing.";
	echo $oAPI->get_simple_error_result($arr_request["ret_type"],$str_error);
	exit;
}

$oUserPassword = new UserPassword($oMysql);
$aff_login_account = $oUserPassword->get_account_by_username("AFFILIATE",$user_id,$user_name);
if(!$aff_login_account)
{
	$str_error = "account not found.";
	echo $oAPI->get_simple_error_result($arr_request["ret_type"],$str_error);
	exit;
}

$password = $oUserPassword->decode_password($aff_login_account);
if(!$password)
{
	$str_error = "internal error.";
	echo $oAPI->get_simple_error_result($arr_request["ret_type"],$str_error);
	exit;
}

$oMcryptString = new McryptString();
$result = $oMcryptString->encode_for_transfer($password,$aff_login_account["LoginUserName"]);

echo $oAPI->get_succ_result($arr_request["ret_type"],$result);
exit;
?>