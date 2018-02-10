<?php
if(!isset($oAPI) || !is_object($oAPI)) die("access deined.");
$result = array();

$files = scandir(API_ROOT);
foreach($files as $file)
{
	if(preg_match("/method\\.([0-9a-zA-Z_]+)\\.php/",$file,$matches))
	{
		$method_name = $matches[1];
		if(substr($method_name,0,3) == "Sys" && $oCBLogin->account["UserName"] != "admin") continue;
		
		$the_method = array();
		$the_method["method_name"] = $method_name;
		$the_method["parameters"] = array();
		$result["method"][] = $the_method;
	}
}

echo $oAPI->get_succ_result($arr_request["ret_type"],$result);
exit;
?>