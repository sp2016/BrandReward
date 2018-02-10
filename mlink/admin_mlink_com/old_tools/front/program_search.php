<?php
include_once(dirname(dirname(__FILE__))."/etc/const.php");	

include_once(INCLUDE_ROOT."func/gpc.func.php");
//header('Content-Type: text/html; charset=iso-8859-1');	
header('Content-Type: text/html; charset=utf-8');	

$programModel = new Program();	

$ajaxTag = get_get_var("ajaxTag");
switch($ajaxTag){
	case "searchProgram":
		$searchQuery = addslashes(trim(get_get_var("q")));
		$affid = intval(get_get_var("affiliatetype"));
		if(!$searchQuery){
		 	exit();
		}
		
		$condition = array();
		if($affid){
			$condition[] = "AffId = $affid";
		}
		
		/*$pattern = "/[^A-Za-z0-9_]/";		
		$searchQuery = preg_replace($pattern, " ", $searchQuery);	*/	
		$searchQuery = str_remove_repeat(" ",$searchQuery);
		
		$prgm_id_arr = array();		
		/*if(is_numeric($searchQuery)){
			//$sql = "SELECT ID FROM program where ID = '$searchQuery'";
			$prgm_id_arr = $programModel->getProgramByID($searchQuery);
			echo $prgm_id_arr["ID"] . "|" . $prgm_id_arr["Name"] . "\n";
		}*/
		
		if(empty($prgm_id_arr))
		{
			//$sql = "SELECT distinct ID FROM program WHERE (MATCH (Name) AGAINST ('{$aga_str}' IN BOOLEAN MODE))";
			$prgm_id_arr = $programModel->getProgramsByKwAndCondition($searchQuery, $condition);
			foreach($prgm_id_arr as $v)
			{
				echo $v["IdInAff"] . "|" . $v["Name"] . "|" . $v["ID"] . "\n";
			}
		}		
		exit;
		
	case "searchAffiliate":
		$searchQuery = addslashes(trim(get_get_var("q")));		
		if(!$searchQuery){
		 	exit();
		}
		$pattern = "/[^A-Za-z0-9_\\.]/";		
		$searchQuery = preg_replace($pattern, " ", $searchQuery);		
		$searchQuery = str_remove_repeat(" ",$searchQuery);
		
		$prgm_id_arr = array();		
		/*if(is_numeric($searchQuery)){			
			$prgm_id_arr = $programModel->getAffiliateInfoById($searchQuery);
		}*/
		
		if(empty($prgm_id_arr))
		{			
			$prgm_id_arr = $programModel->getAffiliateByKw($searchQuery);
		}
		
		//print_r($prgm_id_arr);		
		foreach($prgm_id_arr as $v)
		{
			echo $v["ID"] . "|" . $v["Name"] . "\n";			
		}	
		exit;
		
	case "getAffiliateByName":
		$searchQuery = addslashes(trim(get_get_var("q")));		
		if(!$searchQuery){
		 	exit();
		}
		$id = "";
		$aff_info = array();
		$aff_info = $programModel->getAffiliateByName($searchQuery);
		//print_r($aff_info);
		if(count($aff_info)){
			$id = $aff_info["ID"];
		}
		echo $id;
		exit;
	
	case "searchMerName":
		$searchQuery = addslashes(trim(get_get_var("q")));
		if(!$searchQuery){
		 	exit();
		}
		$site = addslashes(trim(get_get_var("site")));
		
		$pattern = "/[^A-Za-z0-9_]/";		
		$searchQuery = preg_replace($pattern, " ", $searchQuery);		
		$searchQuery = str_remove_repeat(" ",$searchQuery);
		
		$condition = "AND (`MerchantID` like '%". $searchQuery ."%' or `MerchantName` like '%". $searchQuery ."%')";
		if($site){
			$condition .= " AND Site = '$site'";
		}
		$condition .= "GROUP BY MerchantId, MerchantName";
		$merprgm_arr = array();	
		$merprgm_arr = $programModel->getMerchantProgram($condition);
		
		//print_r($prgm_id_arr);		
		foreach($merprgm_arr as $v)
		{
			echo $v["MerchantId"] . "|" . $v["MerchantName"] . "|" . $v["Site"] . "\n";			
		}	
		exit;
		
	case "getProgramIdByName":
		$searchQuery = addslashes(trim(get_get_var("q")));		
		if(!$searchQuery){
		 	exit();
		}
		$id = "";
		$prgm_info = array();
		$prgm_info = $programModel->getProgramsByKwAndCondition($searchQuery);
		foreach($prgm_id_arr as $v)
		{
			echo $v["ID"];
		}		
		echo $id;
		exit;
		
	case "searchStore":
		$searchQuery = addslashes(trim(get_get_var("q")));
		if(!$searchQuery){
		 	exit();
		}
		$objMysql = new Mysql(TASK_DB_NAME, TASK_DB_HOST, TASK_DB_USER, TASK_DB_PASS);
		$sql = "SELECT ID, Domain FROM domain WHERE domain LIKE '%$searchQuery%' LIMIT 100";
		$store_arr = array();
		$store_arr = $objMysql->getRows($sql);
		foreach($store_arr as $v)
		{
			echo $v["ID"] . "|" . $v["Domain"] . "|" . $v["Domain"] . "\n";			
		}	
		exit;
		
	case "searchStoreByMer":
		$searchQuery = addslashes(trim(get_get_var("q")));
		if(!$searchQuery){
		 	exit();
		}
		$site = addslashes(trim(get_get_var("site")));
		
		/*$pattern = "/[^A-Za-z0-9_\\'\\\]/";		
		$searchQuery = preg_replace($pattern, " ", $searchQuery);		
		$searchQuery = str_remove_repeat(" ",$searchQuery);	*/
		
		$condition = "WHERE (`MerchantID` like '%". $searchQuery ."%' or `MerchantName` like '%". $searchQuery ."%')";
		if($site){
			$condition .= " AND SiteName = '$site'";
		}
		$sql = "SELECT * FROM `store_merchant_relationship` $condition GROUP BY MerchantID, MerchantName, SiteName";
		$objMysqlTask = new Mysql(TASK_DB_NAME, TASK_DB_HOST, TASK_DB_USER, TASK_DB_PASS);		
		$merprgm_arr = array();	
		$merprgm_arr = $objMysqlTask->getRows($sql);
		
		//print_r($prgm_id_arr);		
		foreach($merprgm_arr as $v)
		{
			echo $v["MerchantID"] . "|" . $v["MerchantName"] . "|" . $v["SiteName"] . "|" . $v["StoreID"] . "\n";			
		}	
		exit;
	case "searchGroup":
		$searchQuery = addslashes(trim(get_get_var("q")));
		if(!$searchQuery){
		 	exit();
		}
		$sql = "SELECT * FROM `program_int` WHERE GroupInc like '%{$searchQuery}%' AND GroupInc IS NOT NULL AND GroupInc != '' GROUP BY GroupInc";
		$objMysqlTask = new Mysql(TASK_DB_NAME, TASK_DB_HOST, TASK_DB_USER, TASK_DB_PASS);		
		$group_arr = array();	
		$group_arr = $objMysqlTask->getRows($sql);
	
		foreach($group_arr as $v)
		{
			echo $v["GroupInc"] . "\n";			
		}
		exit;
	case "searchDomain":
		$searchQuery = addslashes(trim(get_get_var("q")));		
		if(!$searchQuery){
		 	exit();
		}	
		
		$pattern = "/[^A-Za-z0-9_\\.]/";		
		$searchQuery = preg_replace($pattern, "", $searchQuery);		
		
		$objMysqlTask = new Mysql();	
		$sql = "SELECT id, domain FROM `domain` where domain like '%$searchQuery%' ";		
		$tmp_arr = array();	
		$tmp_arr = $objMysqlTask->getRows($sql);
		foreach($tmp_arr as $v)
		{
			echo $v["id"] . "|" . $v["domain"] . "|" . $v["id"] . "\n";
		}
			
		exit;
	default: 
		break;
}
exit;


	function str_remove_repeat($findme,$str,$live=1){
		if($str=="" || $findme==""){
			return $str;
			exit;
		}			
		$num=substr_count($str,$findme);
		$findme_replace=str_repeat($findme,$live+1);			
		//for($i=0;$i<$num;$i++){		
		while(strpos($str,$findme_replace)!==false){
			$str = str_replace($findme_replace,$findme,$str);			
		}
		return $str;
	}
?>