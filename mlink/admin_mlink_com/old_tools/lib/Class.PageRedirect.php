<?php
class PageRedirect {
	public $siteArr =array(
						"csus" => "CSUS",
						"csuk" => "CSUK",
						"csca" => "CSCA",
						"csau" => "CSAU",
						//"csie" => "CSIE",
						"csde" => "CSDE"
						//"csnz" => "CSNZ"
					);
	public $redirectTypeArr = array( 'URL_HARD_MAPPING' 	=> 'URL_HARD_MAPPING',
//										 'URL_CUSTOMIZATION' 		=> "URL_CUSTOMIZATION",
									 'OBJ_MOVE' 				=> "OBJ_MOVE",
									 'OBJ_PAGE_CUSTOMIZATION' 	=> "OBJ_PAGE_CUSTOMIZATION"
							);
	public $ObjTypeArr = array(
								'URL' 		=> 'URL',
								'MERCHANT' 	=> 'MERCHANT',
								'TAG' 		=> 'TAG',
								'CATEGORY' 	=> 'CATEGORY',
								'SEARCH' 	=> 'SEARCH'
							);

	public $objMysql;
	function __construct($objMysql = null) {
		if ($objMysql){
			$this->objMysql = $objMysql;
		}
		else{
			$this->objMysql = new Mysql (TASK_DB_NAME,TASK_DB_HOST,TASK_DB_USER,TASK_DB_PASS);
		}
	}
	
	function getPageRedirectRows($sql,$key_col_name="RedirId"){
		$redirectInput = array();
		$rows = $this->objMysql->getRows($sql,$key_col_name);
		foreach ($rows as $key => $value){
			$redirectInput[$key]["redirid"] 	= $value["RedirId"];
			$redirectInput[$key]["redirtype"] 	= $value["RedirType"];
			
			$redirectInput[$key]["fromsite"] 	= strtolower($value["FromSiteName"]);
			$redirectInput[$key]["fromurl"] 	= $value["FromUrl"];
			$redirectInput[$key]["fromobjid"] 	= $value["FromObjId"];
			$redirectInput[$key]["fromobjidhidden"]= $value["FromObjId"];
			$redirectInput[$key]["fromobjtype"]	= $value["FromObjType"];
			
			$redirectInput[$key]["tosite"] 		= strtolower($value["ToSiteName"]);
			$redirectInput[$key]["tourl"] 		= $value["ToUrl"];
			$redirectInput[$key]["toobjid"] 	= $value["ToObjId"];
			$redirectInput[$key]["toobjidhidden"] = $value["ToObjId"];
			$redirectInput[$key]["toobjtype"]	= $value["ToObjType"];
			
			$redirectInput[$key]["addtime"]		= $value["AddTime"];
			$redirectInput[$key]["lastupdatetime"]= $value["LastUpdateTime"];
			$redirectInput[$key]["comments"]	= $value["Comments"];
			$redirectInput[$key]["operator"]	= $value["Operator"];
		}
		return $redirectInput;
	}
	
	function insertRedrect($redirectInput){
		if($redirectInput["toobjid"] == "" && $redirectInput["toobjidhidden"] != ""){
			$redirectInput["toobjid"] = $redirectInput["toobjidhidden"];
		}
		if($redirectInput["fromobjid"] == "" && $redirectInput["fromobjidhidden"] != ""){
			$redirectInput["fromobjid"] = $redirectInput["fromobjidhidden"];
		}
		
		$sql = "insert into page_redir_list(`RedirType`,
			`FromSiteName`, `FromObjType`,`FromObjId` ,`FromUrl` ,
			 `ToSiteName` ,`ToObjType` ,`ToObjId` ,`ToUrl` ,`AddTime` ,
			 `LastUpdateTime` ,`Comments`,`Operator` )values(
			 '" . $redirectInput["redirtype"] . "', 
			 '" . $redirectInput["fromsite"] . "', 
			 '" . $redirectInput["fromobjtype"] . "', 
			 '" . $redirectInput["fromobjid"] . "', 
			 '" . addslashes(strtolower($redirectInput["fromurl"])) . "', 
			 '" . $redirectInput["tosite"] . "', 
			 '" . $redirectInput["toobjtype"] . "', 
			 '" . $redirectInput["toobjid"] . "', 
			 '" . addslashes(strtolower($redirectInput["tourl"])) . "', 
			 '" . date("Y-m-d H:i:s") . "', 
			 '" . date("Y-m-d H:i:s") . "', 
			 '" . addslashes($redirectInput["comments"]) . "', 
			 '" . $redirectInput["operator"] . "'
			 )";

		try{
			
			$res = $this->objMysql->query($sql);
			if($res === false){
				return false;
			}
			$redirId = $this->objMysql->getLastInsertId();
			$this->redirectLog($redirId, $redirectInput, "ADD");
		}catch(Exception $e){
			return false;
		}
	
		return true;
	}
	
	function UpdateRedrect($redirectInput){
		if($redirectInput["toobjid"] == "" && $redirectInput["toobjidhidden"] != ""){
			$redirectInput["toobjid"] = $redirectInput["toobjidhidden"];
		}
		if($redirectInput["fromobjid"] == "" && $redirectInput["fromobjidhidden"] != ""){
			$redirectInput["fromobjid"] = $redirectInput["fromobjidhidden"];
		}
		
		$sql = "update page_redir_list set 
			 `RedirType`='" . $redirectInput["redirtype"] . "',
			 `FromSiteName`='" . $redirectInput["fromsite"] . "',
			 `FromObjType`='" . $redirectInput["fromobjtype"] . "',
			 `FromObjId`='" . addslashes($redirectInput["fromobjid"]) . "',
			 `FromUrl`='" . addslashes(strtolower($redirectInput["fromurl"])) . "',
			 `ToSiteName`='" . $redirectInput["tosite"] . "',
			 `ToObjType`='" . $redirectInput["toobjtype"] . "',
			 `ToObjId`='" . addslashes($redirectInput["toobjid"]) . "',
			 `ToUrl`='" . addslashes(strtolower($redirectInput["tourl"])) . "',
			 `LastUpdateTime`='" . date("Y-m-d H:i:s") . "',
			 `Comments`='" . addslashes($redirectInput["comments"]) . "',
			 `Operator`='" . $redirectInput["operator"] . "' WHERE RedirId=".$redirectInput["redirectid"]."";

		try{
			$res = $this->objMysql->query($sql);
			if($res === false){
				return false;
			}
			$this->redirectLog($redirectInput["redirectid"], $redirectInput, "UPDATE");
		}catch(Exception $e){
			return false;
		}
	
		return true;
	}
	
	function DeleteRedrect($id){
		$sql = "select * from page_redir_list where RedirId = '$id' limit 1";
		$rows =  $this->objMysql->getRows($sql);	
		$redirectInput = $rows[0];
		$redirectInput["fromurl"]=$redirectInput["FromUrl"];
		$redirectInput["tourl"]=$redirectInput["ToUrl"];
		$user = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : $_SERVER["REMOTE_USER"];
		$redirectInput["operator"]=$user;
		$sql = "delete from page_redir_list WHERE RedirId=".$id."  limit 1";

		try{
			$res = $this->objMysql->query($sql);
			if($res === false){
				return false;
			}
			$this->redirectLog($id, $redirectInput, "DELETE");
		}catch(Exception $e){
			return false;
		}
	
		return true;
	}
	
	function redirectLog($redirId, $redirectInput, $Type)
	{
		$sql = "insert into page_redir_log(`RedirId`,
			`RedirLogType`, `RedirLogAddTime`,`RedirLogDetail` ,`RedirLogOperator`
			)values(
			 '" . $redirId . "', 
			 '" . $Type . "', 
			 '" . date("Y-m-d H:i:s") . "', 
			 '" . addslashes($redirectInput["fromurl"]) . " direct to " . addslashes($redirectInput["tourl"]) . "', 
			 '" . $redirectInput["operator"] . "'
			 )";
		try{
			
			$res = $this->objMysql->query($sql);
			if($res === false){
				return false;
			}
		}catch(Exception $e){
			return false;
		}
		return true;
	}
	
	function checkLock(&$redirectInput,&$list=array(),$returnDirID=false,$show=false)
	{
		$tourl = isset($redirectInput["tourl"]) ? $redirectInput["tourl"] : $redirectInput["ToUrl"];
		$fromurl = isset($redirectInput["fromurl"]) ? $redirectInput["fromurl"] : $redirectInput["FromUrl"];
		$redirid = isset($redirectInput["redirid"]) ? $redirectInput["redirid"] : $redirectInput["RedirId"];

		//$tourl = trim(strtolower($tourl));
		//$fromurl = trim(strtolower($fromurl));
		$tourl = trim($tourl);
		$fromurl = trim($fromurl);
		
		if($tourl == $fromurl)
		{
			if($returnDirID) return $redirid;
			else return true;
		}
		
		$sql = "select RedirId,FromUrl,ToUrl from page_redir_list where FromUrl = '" . addslashes($tourl) . "' and RedirType <> 'URL_CUSTOMIZATION'";
		$rows = $this->objMysql->getRows($sql);
		
		//for case sensitive
		if(count($rows) > 0)
		{
			foreach($rows as $k => $v)
			{
				if($v["FromUrl"] != $tourl) unset($rows[$k]);
			}
		}
		
		if(count($rows) > 0)
		{
			if(empty($list)) $list = array();
			$list[$redirid] = $redirid;
			foreach($rows as $row)
			{
				$newid = $row["RedirId"];
				if(isset($list[$newid]))
				{
					if($returnDirID) return $newid;
					else return true;
				}
				else
				{
					return $this->checkLock($row,$list,true,true);
				}
			}
		}
		return false;
	}
	
	function checkFromUrl($redirectInput)
	{
		$sql = "select RedirId from page_redir_list where FromSiteName = '" . addslashes($redirectInput["fromsite"]) . "' and FromObjType = '" . addslashes($redirectInput["fromobjtype"]) . "' and FromObjId = '" . addslashes($redirectInput["fromobjid"]) . "'";
		$RedirId = $this->objMysql->getFirstRowColumn($sql,"RedirId");
		if($RedirId) return $RedirId;
		
		$sql = "select RedirId from page_redir_list where FromUrl = '" . addslashes($redirectInput["fromurl"]) . "'";
		$RedirId = $this->objMysql->getFirstRowColumn($sql,"RedirId");
		if($RedirId) return $RedirId;
		
		return false;
	}
	
	function checkHasRedirected($redirectInput){
		$sql = "select RedirId from page_redir_list where RedirType = '" . addslashes($redirectInput["redirtype"]) . "' and ToSiteName = '". addslashes($redirectInput["tosite"]) ."' and ToObjType = '" . addslashes($redirectInput["toobjtype"]) . "' and ToObjId = '" . addslashes($redirectInput["toobjid"]) . "'";
		return $this->objMysql->getFirstRowColumn($sql,"RedirId");
	}
	
	function checkAddCustomizedUrl($objMysql,$_in,$_old_uri="",$check_do="check")
	{
		//$check_test: check, do, check_do
		$is_check = ($check_do == "check" || $check_do == "check_do");
		$is_do = ($check_do == "do" || $check_do == "check_do");
		
		if($is_check && $this->checkDupUri($objMysql,$_in["fromobjid"],strtolower($_in["toobjtype"]),$_in["toobjid"],$_in["tosite"]) != false) return false;
		/*
		if($_old_uri && $_old_uri != $_in["fromobjid"])
		{
			$host = @parse_url($_in["fromurl"],PHP_URL_HOST);
			if(!$host) return false;
			$front = "http://" . $host;
			$redirectValue = $_in;
			$redirectValue["redirtype"] = "URL_HARD_MAPPING";
			$redirectValue["fromobjtype"] = "URL";
			$redirectValue["fromobjid"] = $_old_uri;
			$redirectValue["fromurl"] = $front . $_old_uri;
			$redirectValue["toobjtype"] = "URL";
			$redirectValue["toobjid"] = $_in["fromobjid"];
			$redirectValue["tourl"] = $front . $_in["fromobjid"];
			
			if($is_check && $this->checkFromUrl($redirectValue)) return false;
			//if($is_do) $this->insertRedrect($redirectValue);
		}*/

		if($is_do) $this->insertRedrect($_in);
		if($is_do) $this->doSync();
		return true;
	}
	
	function doSync()
	{
		$this->checkUrl(LINK_ROOT . "cron/cron.page_redirect.php");
	}
	
	function redirectMerchant($fromSite, $fromMerchantID, $toSite, $ToMerchantID, $comments, $user, $oprate = "insert", $fromurl = "", $tourl = ""){
		$redirectInput = array();
		$redirectInput["redirtype"] 	= "OBJ_MOVE";
		$redirectInput["fromsite"] 		= $fromSite;
		if(trim($fromurl) == ''){
			$redirectInput["fromurl"] 		= "/front/merchant.php?mid=$fromMerchantID";
		}else{
			$redirectInput["fromurl"] = $fromurl;
		}
		
		$redirectInput["fromobjid"] 	= $fromMerchantID;
		$redirectInput["fromobjidhidden"]= $fromMerchantID;
		$redirectInput["fromobjtype"]	= "MERCHANT";
		
		$redirectInput["tosite"] 		= $toSite;
		if(trim($tourl) == ''){
			$redirectInput["tourl"] 		= "/front/merchant.php?mid=$ToMerchantID";
		}else{
			$redirectInput["tourl"] = $tourl;
		}
		$redirectInput["toobjid"] 		= $ToMerchantID;
		$redirectInput["toobjidhidden"] = $ToMerchantID;
		$redirectInput["toobjtype"]		= "MERCHANT";

		$redirectInput["operator"] = $user;
		$redirectInput["comments"] = $comments;
		
		if($oprate == "check"){
			return $this->checkLock($redirectInput);
		}
		
		$res = $this->insertRedrect($redirectInput);
		if($res === false){
			return false;
		}
		return true;
	}
	
	function redirectTag($fromSite, $fromTagID, $toSite, $ToTagID, $comments, $user, $oprate = "insert", $fromurl = "", $tourl = ""){
		$redirectInput = array();
		$redirectInput["redirtype"] 	= "OBJ_MOVE";
		$redirectInput["fromsite"] 		= $fromSite;
		if(trim($fromurl) == ''){
			$redirectInput["fromurl"] 		= "/front/tag.php?tagid=$fromTagID";
		}else{
			$redirectInput["fromurl"] = $fromurl;
		}
		
		$redirectInput["fromobjid"] 	= $fromTagID;
		$redirectInput["fromobjidhidden"]= $fromTagID;
		$redirectInput["fromobjtype"]	= "TAG";
		
		$redirectInput["tosite"] 		= $toSite;
		if(trim($tourl) == ''){
			$redirectInput["tourl"] 		= "/front/tag.php?tagid=$ToTagID";
		}else{
			$redirectInput["tourl"] = $tourl;
		}
		$redirectInput["toobjid"] 		= $ToTagID;
		$redirectInput["toobjidhidden"] = $ToTagID;
		$redirectInput["toobjtype"]		= "TAG";

		$redirectInput["operator"] = $user;
		$redirectInput["comments"] = $comments;
		
		if($oprate == "check"){
			return $this->checkLock($redirectInput);
		}
		
		$res = $this->insertRedrect($redirectInput);
		if($res === false){
			return false;
		}
		return true;
	}
	
	function insertToBase($siteMysqlObj,$value,$_debug=false)
	{
		$fields = array(
			"RedirId" => "redirid",
			"FromSiteName" => "fromsite",
			"FromObjType" => "fromobjtype",
			"FromObjId" => "fromobjid",
			"ToSiteName" => "tosite",
			"ToObjType" => "toobjtype",
			"ToObjId" => "toobjid",
			"AddTime" => "addtime",
			"LastUpdateTime" => "lastupdatetime",
			"Comments" => "comments",
		);
		
		$sql = "select " . implode(",",array_keys($fields)) . " from page_redir_site where RedirId = " . $value["redirid"];
		$row = $siteMysqlObj->getFirstRow($sql);
		if(empty($row))
		{
			$sql = "replace into page_redir_site (RedirId, FromSiteName, FromObjType, FromObjId, ToSiteName, ToObjType, ToObjId, AddTime, LastUpdateTime, Comments ) values('" . addslashes($value["redirid"]) . "','" . addslashes($value["fromsite"]) . "','" . addslashes($value["fromobjtype"]) . "','" . addslashes($value["fromobjid"]) . "','" . addslashes($value["tosite"]) . "','" . addslashes($value["toobjtype"]) . "','" . addslashes($value["toobjid"]) . "','" . addslashes($value["addtime"]) . "','" . addslashes($value["lastupdatetime"]) . "','" . addslashes($value["comments"]) . "')";
			$siteMysqlObj->query($sql);
		}
		else
		{
			$arr_update = array();
			foreach($fields as $namefrom => $nameto)
			{
				if($row[$namefrom] != $value[$nameto])
				{
					$arr_update[] = "$namefrom = '" . addslashes($value[$nameto]) . "'";
				}
			}
			
			if(sizeof($arr_update))
			{
				$sql = "update page_redir_site set " . implode(",",$arr_update) . " where RedirId = " . $value["redirid"];
				$siteMysqlObj->query($sql);
			}
		}
		return true;
	}
	
	function insertToBaseBatch($siteMysqlObj,&$list,$_debug=false)
	{
		$fields = array(
			"RedirId" => "redirid",
			"FromSiteName" => "fromsite",
			"FromObjType" => "fromobjtype",
			"FromObjId" => "fromobjid",
			"ToSiteName" => "tosite",
			"ToObjType" => "toobjtype",
			"ToObjId" => "toobjid",
			"AddTime" => "addtime",
			"LastUpdateTime" => "lastupdatetime",
			"Comments" => "comments",
		);
		
		$sql = "select " . implode(",",array_keys($fields)) . " from page_redir_site where RedirId in (" . implode(",",array_keys($list)) . ")";
		$rows = $siteMysqlObj->getRows($sql,"RedirId");
		foreach($rows as $id => $row)
		{
			$arr_update = array();
			foreach($fields as $namefrom => $nameto)
			{
				if($row[$namefrom] != $list[$id][$nameto])
				{
					$arr_update[] = "$namefrom = '" . addslashes($list[$id][$nameto]) . "'";
				}
			}
			
			if(sizeof($arr_update))
			{
				$sql = "update page_redir_site set " . implode(",",$arr_update) . " where RedirId = " . $id;
				$siteMysqlObj->query($sql);
			}
			unset($list[$id]);
		}
		
		if(sizeof($list))
		{
			$arr_insert = array();
			foreach($list as $value)
			{
				$arr_insert[] = "('" . addslashes($value["redirid"]) . "','" . addslashes($value["fromsite"]) . "','" . addslashes($value["fromobjtype"]) . "','" . addslashes($value["fromobjid"]) . "','" . addslashes($value["tosite"]) . "','" . addslashes($value["toobjtype"]) . "','" . addslashes($value["toobjid"]) . "','" . addslashes($value["addtime"]) . "','" . addslashes($value["lastupdatetime"]) . "','" . addslashes($value["comments"]) . "')";
			}
			
			$sql = "replace into page_redir_site (RedirId, FromSiteName, FromObjType, FromObjId, ToSiteName, ToObjType, ToObjId, AddTime, LastUpdateTime, Comments ) values " . implode(",",$arr_insert);
			$siteMysqlObj->query($sql);
		}
		
		$list = array();
	}
	
	function updatePageRedirectFromUrl($redirectId, $fromUrl){
		try{
			$sql = "update page_redir_list set FromUrl = '" . addslashes($fromUrl) .  "' where RedirId = '$redirectId'";
			$this->objMysql->query($sql);
		}catch(Exception $e){
			return false;
		}
		return true;
	}
	
	function updatePageRedirectFromObjId($redirectId, $fromObjId){
		try{
			$sql = "update page_redir_list set FromObjId = '" . addslashes($fromObjId) .  "' where RedirId = '$redirectId'";
			$this->objMysql->query($sql);
		}catch(Exception $e){
			return false;
		}
		return true;
	}

	function updatePageRedirectToUrl($redirectId, $toUrl){
		try{
			$sql = "update page_redir_list set ToUrl = '" . addslashes($toUrl) .  "' where RedirId = '$redirectId'";
			$this->objMysql->query($sql);
		}catch(Exception $e){
			return false;
		}
		return true;
	}
	
	function getObjNameByObjId($mysqlObj, $objType, $objID,$p=0)
	{
		switch($objType){
			case "MERCHANT":
				$table = ($p == 0) ? "normalmerchant" : "normalmerchant_404";
				$sql = "select ID,Name,UrlName from $table where ID = '$objID' limit 1";
				break;
			case "TAG":
				$sql = "select ID,TagName,TagTypeID,UrlName from tag where ID = '$objID' limit 1";
				break;
			case "CATEGORY":
				$sql = "select ID,Name,UrlName from normalcategory where ID = '$objID' limit 1";
				break;
			default:
				return "";
		}
		$row = $mysqlObj->getFirstRow($sql);
		if(empty($row))
		{
		//by ike 20130117	if($objType == "MERCHANT" && $p == 0) return $this->getObjNameByObjId($mysqlObj, $objType, $objID,1);
		}
		return $row;
	}

	function getObjNameByObjIdBatch($mysqlObj,&$arr)
	{
		$page_size = 100;
		foreach($arr as $_type => &$_obj)
		{
			$sql_prefix = "";
			switch(strtoupper($_type)){
				case "MERCHANT":
					$sql_prefix = "select ID,Name,UrlName from normalmerchant where ID in ";
					break;
				case "TAG":
					$sql_prefix = "select ID,TagName,TagTypeID,UrlName from tag where ID in ";
					break;
				case "CATEGORY":
					$sql_prefix = "select ID,Name,UrlName from normalcategory where ID in ";
					break;
			}
			
			if(!$sql_prefix) continue;
			
			$all_ids = array_keys($_obj);
			$total_page = ceil(sizeof($all_ids) / $page_size);
			$offset = 0;
			for($i=0;$i<$total_page;$i++)
			{
				$ids = array_slice($all_ids,$offset,$page_size);
				$sql = $sql_prefix . "(" . implode(",",$ids) . ")";
				$offset += $page_size;
				$rows = $mysqlObj->getRows($sql,"ID");
				foreach($rows as $id => $row)
				{
					$_obj[$id] = $row;
				}
			}
		}
	}
	
	function is_valid_static_url($url)
	{
		return preg_match("|^/.*\\.html$|",$url);
	}

	function checkDupUri($objMysql,$uri,$table,$id,$site="",$all=false)
	{
		$this->arrCheckDupUriResult = array();
		if($uri == "") return false;
		$sql = "select ID from normalmerchant WHERE UrlName = '" . addslashes($uri) . "' limit 100";
		$rows = $objMysql->getRows($sql,"ID");
		if(sizeof($rows) > 0)
		{
			foreach($rows as $k => $v)
			{
				if($k == $id && $table == "merchant") unset($rows[$k]);
			}
		}
		
		if(sizeof($rows) > 0)
		{
			$sql = "select ID,Name from normalmerchant where ID in (" . implode(",",array_keys($rows)) . ")";
			$rows = $objMysql->getRows($sql,"ID");
			if(sizeof($rows) > 0)
			{
				$this->arrCheckDupUriResult["merchant"] = $rows;
				if(!$all) return $this->arrCheckDupUriResult;
			}
		}
		
		$sql = "select ID,TagName from tag WHERE UrlName = '" . addslashes($uri) . "'";
		if($table == "tag") $sql .= " and ID not in ($id)";
		$sql .= " limit 100";
		$rows = $objMysql->getRows($sql,"ID");
		if(sizeof($rows) > 0)
		{
			$this->arrCheckDupUriResult["tag"] = $rows;
			if(!$all) return $this->arrCheckDupUriResult;
		}
		
		if($site != "")
		{
			$sql = "select RedirId,RedirType,FromUrl,ToUrl,ToObjType,ToObjId from page_redir_list where RedirType in ('URL_CUSTOMIZATION','URL_HARD_MAPPING') and FromSiteName = '$site' and FromObjId = '" . addslashes($uri) . "'";
			$rows = $this->objMysql->getRows($sql,"RedirId");
			if(count($rows) > 0){
				$this->arrCheckDupUriResult["fromduplicate"] = $rows;
				if(!$all) return $this->arrCheckDupUriResult;
			}
			
			/*
			$sql = "select RedirId,FromUrl from page_redir_list where RedirType in ('URL_HARD_MAPPING') and ToSiteName = '$site' and ToObjType = 'URL' and ToObjId = '" . addslashes($uri) . "'";
			$rows = $this->objMysql->getRows($sql);
			if(count($rows) > 0){
				$this->arrCheckDupUriResult["toduplicate"] = $rows;
				if(!$all) return $this->arrCheckDupUriResult;
			}
			*/
		}

		if(sizeof($this->arrCheckDupUriResult) > 0) return $this->arrCheckDupUriResult;
		return false;
	}
	
	function checkUrl($url,$returncontent=false)
	{
		$account = "couponsn:IOPkjmN1";
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, 1);
		curl_setopt($curl,CURLOPT_NOBODY,true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		if($account != ""){
			curl_setopt($curl, CURLOPT_USERPWD, $account);
		}
		$data = curl_exec($curl);
		$info = curl_getinfo($curl,CURLINFO_HTTP_CODE);
		curl_close($curl);
		
		if($returncontent) return $data;
		else return $info;
	}
	
	function get_all_static_url(&$rows=array())
	{
		//load all static url
		if(empty($rows))
		{
			$sql = "select * from page_redir_list where RedirType = 'URL_CUSTOMIZATION' order by RedirId";
			$rows = $this->getPageRedirectRows($sql);
		}
		
		$static_url = array();
		foreach ($rows as $row)
		{
			$fromsite = strtolower($row["fromsite"]);
			$fromobjid = $row["fromobjid"];
			//$fromobjtype = strtolower($row["fromobjtype"]);
			$toobjid = $row["toobjid"];
			$toobjtype = strtolower($row["toobjtype"]);
			$static_url[$fromsite][$toobjtype][$toobjid] = $fromobjid;
		}
		return $static_url;
	}
}
?>