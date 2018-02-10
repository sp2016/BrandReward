<?php
class LinkFeedDb
{
	private $CSSites=array(
			"couponsnapshot.com"	=>	"CSUS",
			"couponsnapshot.co.uk"	=>	"CSUK",
			"halfdiscount.com"		=>	"CSUK",
			"couponsnapshot.com.au"	=>	"CSAU",
			"ozsavings.com"			=>	"CSAU",
			"couponsnapshot.ca"		=>	"CSCA",
			"yessavings.com"		=>	"CSCA",
			"couponsnapshot.co.nz"	=>	"CSNZ",
			"couponsnapshot.de"		=>	"CSDE",
			"irelandvouchercodes.com"=>	"CSIE",
			"promocodes2012.org"	=>	"PC2012",
			"discountstory.com"		=>	"PC2012",
			"discountcode2012.com"	=>	"DC2012",
			"coupon4laptop.com"		=>	"C4LP",
			"anypromocodes.com"		=>	"ANYP",
			"aperfectcoupon.com"	=>	"APC",
			"task.megainformationtech.com"=>"TASK",
			"anycodes.com"			=>	"CSUS",
			"walletsaving.com"		=>	"WALLETSAVING",
			"hotdeals.com"			=>	"HOTDEALS",
			"paydayloanguides.com"	=>	"PDLG",
		);

	protected $links;
	protected $invalidLinks;
	protected $message;

	function __construct()
	{
		if(!isset($this->objMysql)) 
			$this->objMysql = new MysqlPdo();
		if(!isset($this->taskDB))
			$this->taskDB = new MysqlPdo(TASK_DB_NAME, TASK_DB_HOST, TASK_DB_USER, TASK_DB_PASS);
		if(!isset($this->links))
			$this->links = array();
		if(!isset($this->invalidLinks)) 
			$this->invalidLinks = array();
		if(!isset($this->message))
			$this->message = array();
	}
	
	function getAllAff()
	{
		$sql = "select * from affiliate";
		$this->affiliates = $this->objMysql->getRows($sql,"AffId");
	}

	function GetAllAffWithMerchant()
	{
		$sql = "select AffId,AffName,AffStatus from affiliate where AffId in (select AffId from program group by AffId) order by AffName";
		return $this->objMysql->getRows($sql,"AffId");
	}
	
	function getAffById($aff_id)
	{
		if(isset($this->affiliates[$aff_id])) return $this->affiliates[$aff_id];                  //affiliate是动态成员函数，所谓动态成员函数，就是在函数中定义，而不是在类的一开始定义。与成员函数无区别。
		$sql = "select * from affiliate where AffId = '$aff_id'";
		$arr = $this->objMysql->getFirstRow($sql);
		if(empty($arr)) mydie("die: getAffById failed, AffId = '$aff_id' not found\n");
        /*$sql = "select Account,Password from wf_aff where ID = ".$aff_id;
        $aff_AP = $this->taskDB->query($sql);
        $aff_AP = mysql_fetch_array($aff_AP);
        $arr['AffLoginPostString'] = str_replace("XXXXXX",urlencode($aff_AP['Account']),$arr['AffLoginPostString']);
        $arr['AffLoginPostString'] = str_replace("YYYYYY",urlencode($aff_AP['Password']),$arr['AffLoginPostString']);
        $arr['Account'] = $aff_AP['Account'];
        $arr['Password'] = $aff_AP['Password'];*/
        //$arr['apikey'] = $aff_AP['apikey'];
        $this->affiliates[$aff_id] = $arr;
	}
	
	function getAffAccountById($accountid)
	{
		$sql = "select * from affiliate_account where AccountId = '$accountid'";
		$arr = $this->objMysql->getFirstRow($sql);
		print_r($arr);
		if(empty($arr)) mydie("die: getAffAccountById failed, AccountId = '$accountid' not found\n");
		return $arr;
	}
	
	function getAccountSiteById($accountid)
	{
		$sql = "select * from affiliate_account_site where AccountId = '$accountid'";
		$arr = $this->objMysql->getRows($sql);
		print_r($arr);
		if(empty($arr)) mydie("die: getAccountSiteById failed, AccountId = '$accountid' not found\n");
		return $arr;
	}

    function getAffAccountSiteByName($affSiteAccName)
    {
        $sql = "select * from affiliate_account_site where Name = '$affSiteAccName'";
        $arr = $this->objMysql->getFirstRow($sql);
        if(empty($arr)) mydie("die: getAffAccountSiteByName failed, Name = '$affSiteAccName' not found\n");
        return $arr;
    }

    function getAffNamesById($reqAffid, $reqAccid, $reqSite)
    {
        if (!$reqAffid && !$reqAccid && !$reqSite) {
            mydie("die: getAffNamesById failed, Please pass in any one ID\n");
        }

        $where = 'WHERE 1=1';
        if ($reqAffid) {
            $where .= sprintf(" AND AffID='%s'", $reqAffid);
        }
        if ($reqAccid) {
            $where .= sprintf(" AND AccountID='%s'", $reqAccid);
        }
        if ($reqSite) {
            $where .= sprintf(" AND SiteID='%s'", $reqSite);
        }

        $sql = sprintf('select Name from affiliate_account_site %s', $where);
        $arr = $this->objMysql->getRows($sql,"Name");
        if(empty($arr)) mydie("die: getAffNamesById failed," . str_replace('WHERE 1=1 AND','', $where) . " not found\n");
        return $arr;
    }

	function transAffId($aff_id)
	{
		$sql = "select MegaAffId from map_wf_aff where MlinkAffId = ".$aff_id;
		$arr = array();
		$arr = $this->taskDB->getFirstRow($sql);
		if(isset($arr['MegaAffId']) && $arr['MegaAffId'])
			return $arr['MegaAffId'];
		else
			return $aff_id;
	}
	
	function GetAllExistsAffMerIDForCheckByAffID($aff_id)
	{
		$sql = "SELECT IdInAff,StatusInAff,Partnership FROM program WHERE AffId = $aff_id";
		$arr = $this->objMysql->getRows($sql,"IdInAff");
		foreach($arr as $k => $v)
		{
			if($v["StatusInAff"] != "Active") $arr[$k] = 0;
			else $arr[$k] = 1;
		}
		return $arr;
	}
	
	function getMerchantStautsByProgramStatus(&$row)
	{
//MerchantStatus      enum('not apply','pending','approval','declined','expired','siteclosed')
//StatusInAff                     enum('Active','TempOffline','Offline')
//Partnership                     enum('NoPartnership','Active','Pending','Declined','Expired','Removed')
		if($row["StatusInAff"] != "Active") return "siteclosed";
		if($row["Partnership"] == "NoPartnership") return "not apply";
		if($row["Partnership"] == "Active") return "approval";
		if($row["Partnership"] == "Pending") return "pending";
		if($row["Partnership"] == "Declined") return "declined";
		if($row["Partnership"] == "Expired") return "expired";
		if($row["Partnership"] == "Removed") return "declined";
		return "siteclosed";
	}
	
	function getApprovalAffMerchant($aff_id, $meridinaff="")
	{
		$cond = $meridinaff ? "and IdInAff = '" . addslashes($meridinaff) . "'" : "";
		$sql = "SELECT AffId,IdInAff,IdInAff as AffMerchantId,Name as MerchantName,EPCDefault as MerchantEPC,EPC30d as MerchantEPC30d,LastUpdateTime,LastUpdateLinkTime,MerchantLinkCount,MerchantFeedCount,LastUpdateFeedTime,MerchantCountry,StatusInAff,Partnership,'' as MerchantStatus,Remark as MerchantRemark FROM program WHERE AffId = $aff_id AND StatusInAff in ('Active') AND Partnership in ('Active') $cond ";
		$rows = $this->taskDB->getRows($sql, "IdInAff");
		foreach($rows as $k => $v)
		{
			$rows[$k]["MerchantStatus"] = $this->getMerchantStautsByProgramStatus($v);
		}
		
		if($meridinaff) return current($rows);
		else return $rows;
	}
	
	function getApprovalAffMerchantFromTask($aff_id, $meridinaff="")
	{
		$cond = $meridinaff ? "and IdInAff = '" . addslashes($meridinaff) . "'" : "";
		$sql = "SELECT AffId,IdInAff,IdInAff as AffMerchantId,Name as MerchantName,EPCDefault as MerchantEPC,EPC30d as MerchantEPC30d,LastUpdateTime,StatusInAff,Partnership,'' as MerchantStatus,Remark as MerchantRemark FROM program WHERE AffId = $aff_id AND StatusInAff = 'Active' AND Partnership = 'Active' $cond ";
		$rows = $this->taskDB->getRows($sql, "IdInAff");
		foreach($rows as $k => $v)
		{
			$rows[$k]["MerchantStatus"] = $this->getMerchantStautsByProgramStatus($v);
		}
		if($meridinaff) return current($rows);
		else return $rows;
	}

	function getAllAffMerchant($aff_id,$mer_id="",$key="",$cond=array())
	{
		$sql = "SELECT AffId,IdInAff,IdInAff as AffMerchantId,Name as MerchantName,EPCDefault as MerchantEPC,EPC30d as MerchantEPC30d,LastUpdateTime,LastUpdateLinkTime,MerchantLinkCount,MerchantFeedCount,LastUpdateFeedTime,MerchantCountry,StatusInAff,Partnership,'' as MerchantStatus,Remark as MerchantRemark FROM program WHERE AffId = '$aff_id'";
		if($mer_id) $sql .= " and IdInAff = '".addslashes($mer_id)."'";
		if(isset($cond["SiteCountry"]) && $cond["SiteCountry"]) $sql .= " and (MerchantCountry is null or MerchantCountry in ('','".addslashes($cond["SiteCountry"])."'))";
		if(isset($cond["Ids"]) && is_array($cond["Ids"]))
		{
			$arr_ids = array();
			foreach($cond["Ids"] as $id) $arr_ids[] = "'" . addslashes($id) . "'";
			$sql .= " and IdInAff in (" . implode(",",$arr_ids) . ")";
		}
		if(!$key) $key = "IdInAff";
		$rows = $this->objMysql->getRows($sql,$key);
		foreach($rows as $k => $v)
		{
			$rows[$k]["MerchantStatus"] = $this->getMerchantStautsByProgramStatus($v);
		}
		return $rows;
	}
	
	function updateLinkInfoInMerchantTable($aff_id,$mer_id,$link_count,$tp="link")
	{
		//update lastupdatetime;
		if($tp == "link")
		{
			$field1 = "LastUpdateLinkTime";
			$field2 = "MerchantLinkCount";
		}
		elseif($tp == "feed")
		{
			$field1 = "LastUpdateFeedTime";
			$field2 = "MerchantFeedCount";
		}
		else mydie("die: updateLinkInfoInMerchantTable tp($tp) is wrong\n");
		
		$sql = "UPDATE affiliate_merchant SET $field1 = NOW(),$field2 = '$link_count' WHERE AffId = '$aff_id' and AffMerchantId = '" . addslashes($mer_id) . "'";
		$this->objMysql->query($sql);
	}
	
	function updateLinkInfoInAffTable($aff_id,$link_count,$tp="link")
	{
		//update lastupdatetime;
		if($tp == "link")
		{
			$field1 = "AffLastUpdateLinkTime";
			$field2 = "AffLinkCount";
		}
		elseif($tp == "feed")
		{
			$field1 = "AffLastUpdateFeedTime";
			$field2 = "AffFeedCount";
		}
		else mydie("die: updateLinkInfoInAffTable tp($tp) is wrong\n");
		
		if(!$this->ignorecheck)
		{
			$sql = "select $field2 from affiliate WHERE AffId = '$aff_id'";
			$pre_count = $this->objMysql->getFirstRowColumn($sql);
			if(is_numeric($pre_count) && $pre_count > 20)
			{
				if($link_count < $pre_count * 0.5)
				{
					mydie("die: variance check failed : last $field2 is $pre_count and new $field2 is $link_count for aff($aff_id) <br>\n");
				}
			}
		}
		$sql = "UPDATE affiliate SET $field1 = NOW(),$field2 = '$link_count' WHERE AffId = '$aff_id'";
		$this->objMysql->query($sql);
	}

	function UpdateMerchantToDB($_arr,&$arrAllExistsMerchants)
	{
		if(!isset($_arr["MerchantName"]) || !isset($_arr["AffMerchantId"])) mydie("die: UpdateMerchantToDB failed: MerchantName or AffMerchantId not found\n");
		if(trim($_arr["MerchantName"]) == "" || trim($_arr["AffMerchantId"]) == "" ) mydie("die: UpdateMerchantToDB failed: MerchantName or AffMerchantId not found\n");
		if(!isset($_arr["MerchantCountry"])) $_arr["MerchantCountry"] = "";
		if($_arr["MerchantCountry"] == "")
		{
			$_arr["MerchantCountry"] = $this->GetCountryCodeByStr($_arr["MerchantName"]);
		}
		
		//$arrAllExistsMerchants[$arr_update["AffId"]] = 1;
		$logfile = $this->getWorkingDirByAffID($_arr["AffId"]) . "merchants_" . date("Ymd") . ".dat";
		$this->logarray($logfile,$_arr);
		
		//$arrCol4Check = array("MerchantName","MerchantStatus","MerchantEPC","MerchantEPC30d","MerchantRemark","MerchantCountry");
		$arrCol4Check = array("MerchantName","MerchantStatus","MerchantRemark","MerchantCountry");
		
		$sql = "SELECT " . implode(",",$arrCol4Check) . " FROM affiliate_merchant WHERE AffMerchantId = '".addslashes($_arr["AffMerchantId"])."' AND AffId = '".addslashes($_arr["AffId"])."'";
		$existing_merchant = $this->objMysql->getFirstRow($sql);
		if(isset($existing_merchant["MerchantStatus"]))
		{
			//found
			/*
			if($existing_merchant["MerchantStatus"] == 'approvaled but neednot add')
			{
				$arrAllExistsMerchants[$_arr["AffMerchantId"]] = 1; //approvaled but neednot add
				return 0;
			}
			*/
			
			$isChanged = false;
			foreach($arrCol4Check as $col)
			{
				if($existing_merchant[$col] != $_arr[$col])
				{
					echo "merchant " . $_arr["AffMerchantId"] . ", $col is changed from " . $existing_merchant[$col] . " to " . $_arr[$col] . "\n";
					$isChanged = true;
					break;
				}
			}
			
			if(!$isChanged)
			{
				$arrAllExistsMerchants[$_arr["AffMerchantId"]] = 2; //not changed
				return 0;
			}
		}
		
		$bStatusChanged = false;
		if(sizeof($existing_merchant))
		{
			$arrAllExistsMerchants[$_arr["AffMerchantId"]] = 3; //udpate
			//update
			$sql = "update affiliate_merchant set MerchantName = '".addslashes($_arr["MerchantName"])."',MerchantEPC = '".addslashes($_arr["MerchantEPC"])."',MerchantEPC30d = '".addslashes($_arr["MerchantEPC30d"])."',`MerchantStatus` = '".addslashes($_arr["MerchantStatus"])."',`MerchantRemark` = '".addslashes($_arr["MerchantRemark"])."',LastUpdateTime=now(),MerchantCountry = '".addslashes($_arr["MerchantCountry"])."' WHERE AffMerchantId = '".$_arr["AffMerchantId"]."' AND AffId = '".$_arr["AffId"]."'";
			$bStatusChanged = ($existing_merchant["MerchantStatus"] != $_arr["MerchantStatus"]);
		}
		else
		{
			//new record
			$arrAllExistsMerchants[$_arr["AffMerchantId"]] = 4; //insert
			$sql = "insert INTO affiliate_merchant (AffId, AffMerchantId, MerchantName, MerchantEPC, MerchantEPC30d, MerchantStatus,MerchantRemark,LastUpdateTime,MerchantCountry) VALUES ('".$_arr["AffId"]."', '".$_arr["AffMerchantId"]."', '".addslashes($_arr["MerchantName"])."', '".addslashes($_arr["MerchantEPC"])."', '".addslashes($_arr["MerchantEPC30d"])."', '".addslashes($_arr["MerchantStatus"])."','".addslashes($_arr["MerchantRemark"])."',now(),'".addslashes($_arr["MerchantCountry"])."')";
			$bStatusChanged = true;
		}
			
		if($this->debug) echo $sql . " <br>\n";
		$this->objMysql->query($sql);
		
		if($bStatusChanged)
		{
			$_arr["FromStatus"] = isset($existing_merchant["MerchantStatus"]) ? $existing_merchant["MerchantStatus"] : "";
			$_arr["ToStatus"] = $_arr["MerchantStatus"];
			$this->addMerchantStatusLog($_arr);
		}
		return 1;
	}
	
	function addMerchantStatusLog(&$_arr)
	{
		$sql = "INSERT INTO affiliate_merchant_status_log (LogId, AffId, AffMerchantId, FromStatus, ToStatus, `AddTime`)
	VALUES	(null, 	'".addslashes($_arr["AffId"])."', 	'".addslashes($_arr["AffMerchantId"])."', 	'".addslashes($_arr["FromStatus"])."', 	'".addslashes($_arr["ToStatus"])."',now());";
		$this->objMysql->query($sql);
	}
	
	function addLinksChangeLog($aff_id,&$_arr)
	{
		// do not insert log now 
//		$sql = "INSERT INTO affiliate_links_change_log (LogId, AffId, AffMerchantId, AffLinkId, ChangeLog, `AddTime`)
//	VALUES	(null, 	'$aff_id', 	'".addslashes($_arr["AffMerchantId"])."', 	'".addslashes($_arr["AffLinkId"])."', 	'".addslashes($_arr["ChangeLog"])."',now());";
//		$this->objMysql->query($sql);
	}
	
	function UpdateAllExistsAffMerIDButCannotFetched($aff_id,&$arrAllExistsMerchants)
	{
		$UpdateCnt = 0;
		$arr_id = array();
		$online_merchant = 0;
		$preview_count = 0;
		foreach($arrAllExistsMerchants as $strMerID => $nUpdated)
		{
			if ($nUpdated == 1)
			{
				$preview_count ++;
				echo "UpdateAllExistsAffMerIDButCannotFetched ",$preview_count,",$strMerID\n";
				$arr_id[] = "'" . addslashes($strMerID) . "'";
			}
			if($nUpdated > 0) $online_merchant ++;
		}
		
		//here's the protection
		if(!$this->ignorecheck && $online_merchant > 20 && sizeof($arr_id) > $online_merchant * 0.5)
		{
			mydie("die: variance check failed : there are too many merchants(" . sizeof($arr_id) . ":" . $online_merchant . ") to be set to siteclosed for aff($aff_id) <br>\n");
		}
		
		if(sizeof($arr_id))
		{
			$sql = "UPDATE affiliate_merchant SET `MerchantStatus` = 'siteclosed',LastUpdateTime=now() WHERE AffId = $aff_id AND AffMerchantId in (" . implode(",",$arr_id) . ")";
			$this->objMysql->query($sql);
			return mysql_affected_rows();
		}
		
		return 0;
	}
	
	function fixDate($str)
	{
		if(empty($str) || $str == "N/A" || $str == "0000-00-00" || $str == "0000-00-00 00:00:00" || $str == "Never") return "0000-00-00";
		$timestamp = strtotime($str);
		if($timestamp === false) return "0000-00-00";
		return date("Y-m-d H:i:s",$timestamp);
	}
	
	function fixLinkData(&$_arr,$affid=0)
	{
	    foreach($_arr as $k => $v) $_arr[$k] = trim($v);
	    
	    $affInfo = array();
	    if($affid && !isset($affInfo[$affid])){
	        $sql = "SELECT IsCheckTimeZone,TimeZoneName,TimeZoneDiff from wf_aff where ID = $affid ";
	        $affInfo[$affid] = $this->taskDB->getFirstRow($sql);
	    }
	    
	    if(isset($_arr["LinkStartDate"])){
		    $_arr["LinkStartDate"] = $this->fixDate($_arr["LinkStartDate"]);
		    if(isset($affInfo[$affid]) && isset($affInfo[$affid]['IsCheckTimeZone']) && $affInfo[$affid]['IsCheckTimeZone'] == 'YES'){
		        $_arr["LinkStartDate"]  = date('Y-m-d H:i:s',strtotime($_arr["LinkStartDate"]) - $affInfo[$affid]['TimeZoneDiff']*60*60 -8*60*60);
		    }
		}
		
		if(isset($_arr["LinkEndDate"])){
		    $_arr["LinkEndDate"] = $this->fixDate($_arr["LinkEndDate"]);
		    if(isset($affInfo[$affid]) && isset($affInfo[$affid]['IsCheckTimeZone']) && $affInfo[$affid]['IsCheckTimeZone'] == 'YES'){
		        $_arr["LinkEndDate"]  = date('Y-m-d H:i:s',strtotime($_arr["LinkEndDate"]) - $affInfo[$affid]['TimeZoneDiff']*60*60 -8*60*60);
		    }
		}
		
		if(!isset($_arr["Country"])) $_arr["Country"] = "";
	}
	
	function getLinkTableName($aff_id)
	{
		if(!is_numeric($aff_id)) mydie("die:getLinkTableName($aff_id) failed\n");
		if(isset($this->link_tables[$aff_id])) return $this->link_tables[$aff_id];
		
		$link_table_name = "affiliate_links_" . $aff_id;
		if(! $this->objMysql->isTableExisting($link_table_name))
		{
			$this->objMysql->duplicateTable("affiliate_links_default",$link_table_name);
		}
		$this->link_tables[$aff_id] = $link_table_name;
		return $link_table_name;
	}
	
	function getAllLinksByAffAndMerchant($aff_id,$mer_id,$site_country="",$recentday=0)
	{
		$link_table_name = $this->getLinkTableName($aff_id);
		$sql = "SELECT * FROM $link_table_name WHERE AffMerchantId = '" . addslashes($mer_id) . "'";
		if($site_country) $sql .= " and (Country is null or Country in ('','".addslashes($site_country)."'))";
		if(is_numeric($recentday) && $recentday > 0)
		{
			$recentday = intval($recentday);
			$sql .= " and `LastUpdateTime` > now() - interval $recentday day";
		}
		return $this->objMysql->getRows($sql,"AffLinkId");
	}
	
	function UpdateLinkToDB(&$arr_list)
	{	
		if(!is_array($arr_list) || !isset($arr_list[0]))
			mydie("UpdateLinkToDB failed: array is needed. <br>\n");
		$first_row = $arr_list[0];
		if(!isset($first_row["AffId"]))
			mydie("UpdateLinkToDB failed: AffId not provided. <br>\n");
		if((!isset($first_row["LinkName"]) || $first_row["LinkName"] == "") && $first_row['LinkPromoType'] != 'link')
			mydie("UpdateLinkToDB failed: LinkName not provided. <br>\n");
		if(!isset($first_row["AffLinkId"]) || $first_row["AffLinkId"] == "")
			mydie("UpdateLinkToDB failed: AffLinkId not provided. <br>\n");
		$aff_id = $first_row["AffId"];
		$arr_col = array("AffMerchantId", "AffLinkId", "LinkName", "LinkDesc", "LinkStartDate", "LinkEndDate", "LinkPromoType", "LinkHtmlCode", "LinkOriginalUrl", "LinkImageUrl", "LastUpdateTime", "Country", "LinkAffUrl", "DataSource", "LinkAddTime", "LinkCode", "Type", "IsDeepLink","Language");
		//do not check LinkHtmlCode now.
		$col_for_check = array("LinkName", "LinkDesc", "LinkStartDate", "LinkEndDate", "LinkPromoType", "LinkOriginalUrl","LinkAffUrl","LinkCode", "Type", "IsDeepLink","DataSource","Language");
		$logfile = $this->getWorkingDirByAffID($aff_id) . "links_" . date("Ymd") . ".dat";
		if(!file_exists($logfile))
			$this->logarray($logfile,$arr_col);//logarray方法将$arr_col转成字符串，并写入$logfile文件中
		$link_table_name = $this->getLinkTableName($aff_id);//生成linkfeed表名
		$sql_check_pre = "SELECT " . implode(",",$col_for_check) . " FROM $link_table_name WHERE ";
		$arr_insert = array();
		$count = 0;
		$updated = 0;
		foreach($arr_list as $row){
			$arr_temp = array();
			foreach($arr_col as $col) $arr_temp[$col] = isset($row[$col]) ? $row[$col] : "";
			$row = $arr_temp;
			
			$this->fixLinkData($row,$aff_id);//fixLinkData把时间变成时间戳
			
			$this->logarray($logfile,$row);
			//check
			$sql_check = $sql_check_pre . " AffMerchantId = '".addslashes($row["AffMerchantId"])."' AND AffLinkId = '".addslashes($row["AffLinkId"])."'";
			$result = $this->objMysql->getFirstRow($sql_check);
			if(isset($result["LinkName"])){//说明数据表中已经有这个link，更新与否，首先看linkName，其次看各个字段
				$arr_changed = array();
				$arr_changed_col = array();
				foreach($col_for_check as $col){//检查那个字段有改变
					$is_same = false;
					if($col == "LinkStartDate"){
						//if(substr($result[$col],0,10) == substr($row[$col],0,10)) $is_same = true;
						if($result[$col] == $row[$col]) $is_same = true;
						//if both start date <= now, we think they are same
						if(!$is_same && substr($result[$col],0,10) <= date("Y-m-d") && substr($row[$col],0,10) <= date("Y-m-d")) $is_same = true;
					}elseif($col == "LinkEndDate"){
						//if(substr($result[$col],0,10) == substr($row[$col],0,10)) $is_same = true;
						if($result[$col] == $row[$col]) $is_same = true;
						if(strtotime($result[$col]) == strtotime($row[$col])) $is_same = true;
					}elseif($col == "LinkHtmlCode"){
						$linkhtmlcode = trim($result["LinkHtmlCode"]);
						if(empty($linkhtmlcode)){
							if($result[$col] == $row[$col]) $is_same = true;
						}
					}else{
						if(strtolower($result[$col]) == strtolower($row[$col])) $is_same = true;
					}
					if(!$is_same){
						
						$arr_changed[] = "$col is changed from '" . $result[$col] . "' to '" . $row[$col] . "'";
						//echo "$col is changed from '" . $result[$col] . "' to '" . $row[$col] . "'".PHP_EOL;
						$arr_changed_col[$col] = "";
					}
				}
				if(sizeof($arr_changed)){
					$updated ++;
					$row["ChangeLog"] = implode("\n",$arr_changed);
					$this->addLinksChangeLog($aff_id,$row);
					//If check param changed LinkDesc LinkHtmlCode LinkAffUrl need to be update too.
					$arr_changed_col['LinkDesc'] = $row['LinkDesc'];
					$arr_changed_col['LinkHtmlCode'] = $row['LinkHtmlCode'];
					$arr_changed_col['LinkAffUrl'] = $row['LinkAffUrl'];
					foreach($arr_changed_col as $col => $v){
						$arr_changed_col[$col] = "$col = '" . addslashes($row["$col"]) . "'";
					}
					$arr_changed_col['LastChangeTime'] = "LastChangeTime = '" . date('Y-m-d H:i:s') . "'";//edit time 2017/03/28
					if(SID != 'bdg01'){
					    $arr_changed_col['isactive'] = "isactive = 'YES'";//edit time 2017/04/13
					}
					$sql = "update $link_table_name set " . implode(",",$arr_changed_col) . ",LastUpdateTime = '".date('Y-m-d H:i:s')."' where AffMerchantId = '".addslashes($row["AffMerchantId"])."' AND AffLinkId = '".addslashes($row["AffLinkId"])."'";
					//echo $sql.PHP_EOL;
					$this->objMysql->query($sql);//将有改变的字段进行更新
				}else{					
					//FOR mlink
					if(SID != 'bdg01'){
						$sql = "update $link_table_name set LastUpdateTime = '".date('Y-m-d H:i:s')."', isactive = 'YES', LinkName = '".addslashes($row['LinkName'])."', LinkDesc = '".addslashes($row['LinkDesc'])."'  where AffMerchantId = '".addslashes($row["AffMerchantId"])."' AND AffLinkId = '".addslashes($row["AffLinkId"])."'";
						//echo $sql.PHP_EOL;
						$this->objMysql->query($sql);
					}else{					
						continue;
					}
				}
			}else{
				//for insert
				foreach($row as $k => $v){
					$row[$k] = "'" . addslashes($v) . "'";
				}
				$row["LastUpdateTime"] = "'" . date('Y-m-d H:i:s') . "'";
				$row["LinkAddTime"] = "'" . date('Y-m-d H:i:s') . "'";
				$arr_insert[] = "(" . implode(",",$row) . ")";
			}
			$count ++;
		}
		if(sizeof($arr_insert)){
			$sql = "insert ignore into $link_table_name(" . implode(",",$arr_col) . ") values " . implode(",",$arr_insert);
			$this->objMysql->query($sql);
		}
		echo sprintf("updating links(%s)... New:%s, Updated:%s, Unchanged:%s.\n", count($arr_list), count($arr_insert), $updated, count($arr_list)-count($arr_insert)-$updated);
		return $count;
	}
	
	// check link exists on network
	function checkLinkExists($aff_id, $check_date, $type='', $AffMerchantId=0)
	{
		//FOR mlink
		$where = '';
		if($type) $where = " AND `Type` = '".$type."'";
		if($AffMerchantId) $where .= " AND `AffMerchantId` = '".$AffMerchantId."'";
		
		if(SID != 'bdg01'){
			$link_table_name = $this->getLinkTableName($aff_id);
			$sql = "select count(*) from `$link_table_name` where LastUpdateTime < '$check_date' and isactive = 'YES' $where";
			$cnt = $this->objMysql->getFirstRowColumn($sql);
			
			
			$sql = "select count(*) from `$link_table_name` where LastUpdateTime >= '$check_date' and isactive = 'YES' $where";
			$activeCount = $this->objMysql->getFirstRowColumn($sql);
			
			if($aff_id == 22){
			    $threshold = 6;
			}
			else{
			    $threshold = 5;
			}
			if($activeCount==0 || ($activeCount && floor(($cnt/$activeCount)*100)>$threshold)){
			    $to = "merlinxu@brandreward.com";
			    AlertEmail::SendAlert( $type.':'.$aff_id.' affid to inactive too much',nl2br("total count:$activeCount,inactive count:$cnt"), $to);
			    mydie("die: too many Links $cnt .\n");
			    exit;
			}else{
				$sql = "update `$link_table_name` set isactive = 'NO', LastChangeTime = '".date('Y-m-d H:i:s')."' where LastUpdateTime < '$check_date' and isactive = 'YES' $where";
				$this->objMysql->query($sql);
				echo "\tSet $cnt Inactive.\r\n";
			}
		}		
	}
	
	// For links from affiliate278 affiliate279 etc
	// Update the Links to the db according to the LinkOriginalUrl specified.
	function UpdateLinkToAffiliateDB(&$links)
	{
		if(!is_array($links) || !is_array($links))
			mydie("UpdateLinkToDB failed: array is needed. <br>\n");
		$update_count = 0;
		foreach ($links as $row)
		{
			if (empty($row['AffId']) || empty($row['LinkName']) || empty($row['AffLinkId']) || empty($row['AffLinkId']) || empty($row['LinkOriginalUrl']))
				continue;
			if (strtolower($row['LinkPromoType']) != 'coupon' || empty($row['LinkCode'])) //update coupon only
				continue;
			$this->fixLinkData($row);
			$row['AffId'] = (int)$row['LinkOriginalUrl'];
			$link_table_name = 'affiliate_links_' . $row['AffId'];
			//check the LinkCode of a program, igore if exist.
			$sql = sprintf("select AffLinkId from `%s` where `AffMerchantId`='%s' and LinkCode='%s' limit 1", $link_table_name, $row['AffMerchantId'], $row['LinkCode']);
			$r = $this->objMysql->getFirstRow($sql);
			if (!empty($r['AffLinkId']))
				continue;
			//insert
			foreach($row as $k => $v)
				$row[$k] = "'" . addslashes($v) . "'";
			$row["LastUpdateTime"] = "now()";
			$row["LinkAddTime"] = "now()";
			unset($row['AffId']);
			$sql = sprintf("insert ignore into `%s` (%s) values (%s)", $link_table_name, implode(",", array_keys($row)), implode(",", $row));
			$this->objMysql->query($sql);
			$update_count ++;
		}
		return $update_count;
	}
	
	function fixJobName($_name)
	{
		$arr = explode(":",$_name);
		if(sizeof($arr) > 1) return $arr[sizeof($arr) - 1];
		return $_name;
	}
	
	function getJobLastId()
	{
		if(!isset($this->job_stack)) $this->job_stack = array();
		$stack_length = sizeof($this->job_stack);
		if($stack_length == 0) return 0;
		return $this->job_stack[$stack_length - 1];
	}
	
	function addJob(&$_arr)
	{
		if(!isset($_arr["JobName"])) mydie("die: JobName not provided\n");
		//add by ike: 20120215
		if(isset($_arr["AffId"]) && $_arr["AffId"] > 0 && $_arr["JobName"] == "syncLinksByAff")
		{
			$this->setLock($aff_id);
		}
		$_arr["JobName"] = $this->fixJobName($_arr["JobName"]);
		$parent_id = $this->getJobLastId();
		$sql = "INSERT INTO job (JobId, JobName, JobAddTime, AffId, MerchantId,SiteId,AffectedCount, UpdatedCount, Detail,JobEndTime,ParentJobId) VALUES (null, '".addslashes($_arr["JobName"])."', now(), '".addslashes($_arr["AffId"])."', '".addslashes($_arr["MerchantId"])."','".addslashes($_arr["SiteId"])."', '".addslashes($_arr["AffectedCount"])."', '".addslashes($_arr["UpdatedCount"])."', '".addslashes($_arr["Detail"])."',null,'$parent_id')";
		$this->objMysql->query($sql);
		$job_id = $this->objMysql->getLastInsertId();
		array_push($this->job_stack,$job_id);
		$this->job_id = $job_id;
		return $job_id;
	}
	
	function getLockFile($aff_id)
	{
		return INCLUDE_ROOT . "data/" . $aff_id . ".lock";
	}
	
	function setLock($aff_id)
	{
		$file = $this->getLockFile($aff_id);
		if(file_exists($file))
		{
			mydie("die: setLock failed, $file exists\n");
		}
		return touch($file);
	}
	
	function releaseLock($aff_id)
	{
		$file = $this->getLockFile($aff_id);
		@unlink($file);
	}
	
	function endJob(&$_arr)
	{
		$job_id = array_pop($this->job_stack);
		if($job_id == null) mydie("die: something wrong here, trying to end a undefined job\n");
		
		$sql = "select AffId,JobName from job where JobId = '$job_id'";
		$row = $this->objMysql->getFirstRow($sql);
		if(isset($row["AffId"]) && $row["JobName"] == "syncLinksByAff")
		{
			$this->releaseLock($row["AffId"]);
		}
		
		$sql = "update job set JobEndTime = now()";
		if(isset($_arr["AffectedCount"])) $sql .= ",AffectedCount = '" . $_arr["AffectedCount"] . "'";
		if(isset($_arr["UpdatedCount"])) $sql .= ",UpdatedCount = '" . $_arr["UpdatedCount"] . "'";
		if(isset($_arr["Detail"])) $sql .= ",Detail = '" . addslashes($_arr["Detail"]) . "'";
		$sql .= " where JobId = '$job_id'";
		$this->objMysql->query($sql);
	}
	
	function getAllSite($para=array())
	{
		$arr_where = array();
		$str_where = "";
		if(isset($para["canSync"])) $arr_where[] = "SiteMysqlUser is not null and SiteMysqlUser <> '' and SiteAllowSync = 'Yes'";
		if(sizeof($arr_where)) $str_where = "where " . implode(" and ",$arr_where);
		$sql = "select * from site $str_where";
		return $this->objMysql->getRows($sql,"SiteId");
	}
	
	function getSiteIdByShortId($short_id)
	{
		$sql = "select SiteId from site where SiteShortId = '" . addslashes($short_id) . "'";
		return $this->objMysql->getFirstRowColumn($sql);
	}
	
	function getCountryCode($full_country_name)
	{
		$sql = "SELECT CountryCode FROM country_codes where CountryName = '" . addslashes($full_country_name) . "'";
		return $this->objMysql->getFirstRowColumn($sql);
	}
	
	function getAllActiveCountry()
	{
		$sql = "SELECT * FROM country_codes where CountryStatus = 'On'";
		return $this->objMysql->getRows($sql,"CountryCode");
	}
	
	function GetCountryCodeByStr($str)
	{
		$str = trim($str);
		if(isset($this->countries_mapping[$str])) return $this->countries_mapping[$str];
		
		if(strlen($str) == 0 || strlen($str) == 1) $country_code = "";
		elseif(strlen($str) == 2) $country_code = $str;
		else
		{
			$country_code = $this->getCountryCode($str);
			if($country_code == "") $CountryPatterns = $this->getCountryMatchPatterns();
			
			if($country_code == "")
			{
				foreach($CountryPatterns["level_1_pattern"] as $pattern => $_code)
				{
					if(preg_match($pattern,$str))
					{
						$country_code = $_code;
						break;
					}
				}
			}
			
			if($country_code == "")
			{
				foreach($CountryPatterns["level_2_pattern"] as $pattern => $_code)
				{
					if(preg_match($pattern,$str))
					{
						if($country_code == "") $country_code = $_code;
						elseif($country_code == $_code)
						{
							//do nothing here
						}
						else
						{
							//found more than one country, leave it empty
							echo "warning: there have two or more countries in the code: $str\n";
							$country_code = "";
							break;
						}
					}
				}
			}
		}
		$this->countries_mapping[$str] = $country_code;
		return $country_code;
	}
	
	function getCountryMatchPatterns()
	{
		if(isset($this->CountryPatterns)) return $this->CountryPatterns;
		
		$all_country = $this->getAllActiveCountry();
		$arr_top_pattern_template = array(
			'/ - {keyword}$/i',
			'/\\({keyword}\\)$/i',
			'/\\[{keyword}\\]$/i',
		);
		
		$arr_second_pattern_template = array(
			'/\\b{keyword}\\b/i',
		);
		
		$arr_top_pattern = array();
		$arr_secode_pattern = array();
		foreach($all_country as $code => $info)
		{
			if(isset($info["CountryDomain"]) && $info["CountryDomain"])
			{
				$pattern = '/' . preg_quote($info["CountryDomain"]) . '\\b/i';
				$arr_top_pattern[$pattern] = $code;
			}
			
			foreach($arr_top_pattern_template as $_pattern)
			{
				$pattern = str_replace("{keyword}",preg_quote($info["CountryCode"]),$_pattern);
				$arr_top_pattern[$pattern] = $code;
				$pattern = str_replace("{keyword}",preg_quote($info["CountryName"]),$_pattern);
				$arr_top_pattern[$pattern] = $code;
			}
			
			foreach($arr_second_pattern_template as $_pattern)
			{
				$pattern = str_replace("{keyword}",preg_quote($info["CountryCode"]),$_pattern);
				$arr_secode_pattern[$pattern] = $code;
				$pattern = str_replace("{keyword}",preg_quote($info["CountryName"]),$_pattern);
				$arr_secode_pattern[$pattern] = $code;
				
				if(isset($info["CountryKeywords"]) && $info["CountryKeywords"])
				{
					$arr_keyword = explode("|",$info["CountryKeywords"]);
					foreach($arr_keyword as $keyword)
					{
						if(empty($keyword)) continue;
						$pattern = str_replace("{keyword}",preg_quote($keyword),$_pattern);
						$arr_secode_pattern[$pattern] = $code;
					}
				}
			}
		}
		
		$this->CountryPatterns = array(
			"level_1_pattern" => $arr_top_pattern,
			"level_2_pattern" => $arr_secode_pattern,
		);
		return $this->CountryPatterns;
	}

	function getLinkById($affid, $AffLinkId, $AffMerchantId = null)
	{
		if (empty($AffMerchantId))
			$q = sprintf("select * from `affiliate_links_%s` where `AffLinkId`='%s' limit 1", (int)$affid, addslashes($AffLinkId));
		else
			$q = sprintf("select * from `affiliate_links_%s` where `AffLinkId`='%s' limit 1", (int)$affid, addslashes($AffLinkId), addslashes($AffMerchantId));
		$r = $this->objMysql->getFirstRow($q);
		if (is_array($r))
			return $r;
	}

	private function getTaskDB()
	{
		if (is_object($this->taskDB))
			return $this->taskDB;
		$db = new Mysql(TASK_DB_NAME, TASK_DB_HOST, TASK_DB_USER, TASK_DB_PASS);
		$this->taskDB = $db;
		return $this->taskDB;
	}
	
	private function getBcgDB()
	{
		if (is_object($this->bcgDB))
			return $this->bcgDB;
		$db = new Mysql(BCG_DB_NAME, BCG_DB_HOST, BCG_DB_USER, BCG_DB_PASS);
		$this->bcgDB = $db;
		return $this->bcgDB;
	}

	protected function saveInvalidLinks()
	{
		$count = 0;
		if (empty($this->invalidLinks) || !is_array($this->invalidLinks) || count($this->invalidLinks) < 1 || empty($this->invalidLinks[0]['affiliate']))
			return $count;
		$affiliate = $this->invalidLinks[0]['affiliate'];
		$programs = $this->getAllAffMerchant($affiliate);
		$db = $this->getBcgDB();
		foreach ($this->invalidLinks as $v)
		{
			if (empty($v['affiliate']) || empty($v['LinkID']))
				continue;
			// fill the fields with the default value.
			$v['AddTime'] = date('Y-m-d h:i:s');
			if (empty($v['OccuredDate']))
				$v['OccuredDate'] = '0000-00-00 00:00:00';
			if (empty($v['CSSites']) && !empty($v['ReferralUrl']))
			{
				$domain = parse_url($v['ReferralUrl']);
				if (!empty($domain['host']))
				{
					$host = str_replace('www.', '', $domain['host']);
					if (!empty($host) && !empty($this->CSSites[$host]))
						$v['CSSites'] = $this->CSSites[$host];
				}
			}
			if (empty($v['CSSites']))
				$v['CSSites'] = 'Unknown';
			$v['Status'] = 'NEW';
			// try to get the orginal link and program infomation and fill the empty fields.
			if (empty($v['ProgramID']))
				$record = $this->getLinkById($affiliate, $v['LinkID']);
			else
				$record = $this->getLinkById($affiliate, $v['LinkID'], $v['ProgramID']);
			if (is_array($record))
			{
				if (!empty($record['AffMerchantId']) && empty($v['ProgramID']))
					$v['ProgramID'] = $record['AffMerchantId'];
				if (!empty($record['LinkAffUrl']) && empty($v['MerLandingPage']))
					$v['MerLandingPage'] = $record['LinkAffUrl'];
			}
			$program = null;
			if (!empty($v['ProgramID']))
			{
				$program = null;
				$program = @$programs[$v['ProgramID']];
				if (!empty($program) && !empty($program['MerchantName']) && empty($v['ProgramName']))
					$v['ProgramName'] = $program['MerchantName'];
			}
			// a program must be found.
			// and the Status in Aff must Active and the Partnership must be Active.
			if (empty($v['ProgramID']) || empty($program) || $program['Partnership'] != 'Active' || $program['StatusInAff'] != 'Active')
				continue;
				
			/*$tmpcheckarr = $this->getCsMerchantRow($v['affiliate'],trim($v['ProgramID']),trim($v['ProgramName']));
			if(!is_array($tmpcheckarr) || empty($tmpcheckarr)){
				echo "affid:{$v['affiliate']}, programId:{$v['ProgramID']}, programname:{$v['ProgramName']} Not matched merchant \n";
				continue;
			}else{
//				var_dump($tmpcheckarr);
//				echo "\n";
			}*/
			
			foreach ($v as $key => $val)
				$v[$key] = mysql_real_escape_string($val);
			// if a record that Status='NEW' and same affiliate, LinkID, CSSites exists, ignore current record.
			$q = sprintf("select ID from `affiliate_invalid_link` where `Status`='%s' and `affiliate`=%s and `LinkID`='%s' and `CSSites`='%s' limit 1",
					'NEW', (int)$v['affiliate'], $v['LinkID'], $v['CSSites']);
			$r = $db->getFirstRowColumn($q);
			if (!empty($r))
				continue;
			$q = sprintf("insert ignore into `affiliate_invalid_link` (`%s`) values ('%s');",
					implode("`,`", array_keys($v)), implode("','", $v));
			$db->query($q);
			$count ++;
		}
		return $count;
	}
	
	function getCsMerchantRow($affid, $progid="", $progname="")
	{
		$row = array();
		if(!empty($progid))
		{
			$sql = "select Site,MerchantId,MerchantName from `merchant_program` WHERE AffId='{$affid}' AND AffMerchantId='".addslashes($progid)."' LIMIT 1";
			$row = $this->taskDB->getFirstRow($sql);
		}else
		{
			$sql = "select ID from `program` WHERE AffId='{$affid}' AND Name='".addslashes($progname)."' LIMIT 1";
			$program_id= $this->taskDB->getFirstRowColumn($sql);
			if(!empty($program_id))
			{
				$sql = "select Site,MerchantId,MerchantName from `merchant_program` WHERE ProgramId='{$program_id}' LIMIT 1";
				$row=$this->taskDB->getFirstRow($sql);
			}
		}
		return $row;
	}

	// save messages.
	// if the message is new message send email to MESSAGE_EMAIL
	protected function saveMessage()
	{
		$count = 0;
		if (empty($this->message) || !is_array($this->message))
			return $count;
		foreach ($this->message as $k => $v)
		{
			foreach ($v as $key => $val)
				$v[$key] = mysql_real_escape_string($val);
			$q = sprintf("insert into `affiliate_message` (`%s`) values ('%s');",
					implode("`,`", array_keys($v)), implode("','", $v));
			$duplicate = false;
			try 
			{
				$this->objMysql->query($q);
			}
			catch (Exception $e)
			{
				$msg = $e->getMessage();
				if (preg_match('@Duplicate entry \'@', $msg))
					$duplicate = true;
				else 
					throw $e;
			}
			if ($duplicate)
				continue;
			$this->sendMessageEmail($this->message[$k]);
			$count ++;
		}
		return $count;
	}

	protected function sendMessageEmail($data)
	{
		$sql = sprintf("select affname from affiliate where affid=%s", (int)$data['affid']);
		$affName = $this->objMysql->getFirstRowColumn($sql);
		$subject = sprintf("%s - %s - %s", $data['title'], $data['affid'], $affName);
		$body = sprintf("Affilate id: %s\nSender: %s\nContent:\n%s", $data['affid'], $data['sender'], $data['content']);
		return AlertEmail::SendAlert($subject, nl2br($body), MESSAGE_EMAIL, false);
	}

	protected function checkMessageExist($data)
	{
		if (empty($data) || !is_array($data) || empty($data['messageid']) || empty($data['affid']))
			return;
		$q = sprintf("select id from `affiliate_message` where `affid`=%s and `messageid`='%s' limit 1", 
				 (int)$data['affid'], addslashes($data['messageid']));
		$r = $this->objMysql->getFirstRowColumn($q);
		return (int)$r;
	}

    function getNewlyCrawlBatchId($affId)
    {
        $sql = sprintf("SELECT BatchID FROM crawl_batch WHERE AffID='%s' AND CrawlJobStatus!='Error' AND BatchStatus='Unchecked' ORDER BY EndTime DESC LIMIT 1", trim($affId));
        $batchId = $this->objMysql->getFirstRow($sql);
        return $batchId;
    }

    function saveCheckBatchResult($aff_id, $check_batch_id, $allUseful)
    {
        $this->objMysql = new MysqlPdo();
        $sql = sprintf("SELECT * FROM crawl_batch WHERE AffID='%s' AND BatchID='%s' LIMIT 1", $aff_id, $check_batch_id);
        $r = $this->objMysql->getFirstRow($sql);
        if (empty($r)) {
            mydie("Can't find batchId=$check_batch_id from crawl_batch where AffID=$aff_id\r\n");
        }
        if ($allUseful) {
            $BatchStatus = 'Goodtogo';
        } else {
            $BatchStatus = 'Warning';
        }
        $sql = sprintf("UPDATE crawl_batch SET BatchStatus ='%s' WHERE AffId = '%s' and BatchID ='%s'", $BatchStatus, $aff_id, $check_batch_id);

        try {
            $this->objMysql->query($sql);
        } catch (Exception $e) {
            echo $e->getMessage()."\n";
            return false;
        }
        return $allUseful;
    }
}

