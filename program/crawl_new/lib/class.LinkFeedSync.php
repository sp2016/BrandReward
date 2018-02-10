<?php
class LinkFeedSync extends LinkFeedDb
{
	function __construct($site_id="")
	{
		if(!isset($this->objMysql)) $this->objMysql = new MysqlExt();
		if($site_id) $this->connectSite($site_id);
		$this->ignoredmerchants = array();
	}
	
	function isIgnoredMerchant($aff_id,$mer_id,$internal_merid)
	{
		$key = "$aff_id\t$mer_id\t$internal_merid";
		$site_id = $this->current_site_id;
		$this->loadIgnoredMerchants($site_id);
		
		return isset($this->ignoredmerchants[$site_id][$key]);
	}
	
	function loadIgnoredMerchants($site_id)
	{
		if(isset($this->ignoredmerchants[$site_id])) return;
		$this->ignoredmerchants[$site_id] = array();
		
		// siteid,affid,merchantid => merchant name
		$main_merchant = array();
		//csus = 1
		$main_merchant[1][7][7651] = "CSN Stores";
		$main_merchant[1][1][11929] = "BOMC2";
		$main_merchant[1][1][7796] = "CPO Outlets";
		$main_merchant[1][1][2972] = "Cymax Stores";
		
		$main_merchant[1][1][9410] = "Celtic Hills";
		$main_merchant[1][1][5547] = "Sunbeam";
		$main_merchant[1][7][5698] = "TSC Pets";
		$main_merchant[1][7][12362] = "HelloLife";
		
		//csca = 3
		$main_merchant[3][1][110048] = "Cymax Stores";
		
		if(!isset($main_merchant[$site_id])) return;
		$this->connectSite($site_id);
		foreach($main_merchant[$site_id] as $affid => $affmerchants)
		{
			foreach($affmerchants as $merid => $mername)
			{
				//get main merchant's affmerchantid
				$sql = "select MerIDinAff from wf_mer_in_aff where AffID = '$affid' and MerID = '$merid' and IsUsing = 1";
				$MerIDinAff = $this->objRemoteMysql->getFirstRowColumn($sql);
				if(!$MerIDinAff) continue;
				$sql = "select MerID from wf_mer_in_aff where AffID = '$affid' and MerIDinAff = '" . addslashes($MerIDinAff) . "' and MerID <> '$merid' and IsUsing = 1";
				$toignored = $this->objRemoteMysql->getRows($sql,"MerID");
				foreach($toignored as $submerid => $null)
				{
					$key = "$affid\t$MerIDinAff\t$submerid";
					$this->ignoredmerchants[$site_id][$key] = $key;
				}
			}
		}
	}
	
	function connectSite($site_id)
	{
		if($site_id && isset($this->current_site_id) && $site_id == $this->current_site_id) return true;
		$this->current_site_id = $site_id;
		$site_info = $this->getSiteById($site_id);
		if(!isset($site_info["SiteId"])) mydie("die: SiteId $site_id not found\n");
		$this->objRemoteMysql = new Mysql($site_info["SiteMysqlDB"],$site_info["SiteMysqlHost"],$site_info["SiteMysqlUser"],$site_info["SiteMysqlPassword"]);
	}

	function connectCPQ()
	{
		if(!isset($this->objCPQMysql)){
			$this->objCPQMysql = new Mysql(TASK_DB_NAME, TASK_DB_HOST, TASK_DB_USER, TASK_DB_PASS);
		}
		return $this->objCPQMysql;
	}

	function getSiteById($site_id)
	{
		if(isset($this->sites[$site_id])) return $this->sites[$site_id];
		$sql = "select * from site where SiteId = '$site_id'";
		$this->site_info = $this->sites[$site_id] = $this->objMysql->getFirstRow($sql);
		return $this->getSiteById($site_id);
	}
	
	function syncAllSite($aff_id)
	{
		//add log
		$arr_log = array(
			"JobName" => __METHOD__,
			"AffId" => $aff_id,
			"MerchantId" => "",
			"SiteId" => 0,
			"AffectedCount" => 0,
			"UpdatedCount" => 0,
			"Detail" => "",
		);
		$this->addJob($arr_log);
		
		$all_site = $this->getAllSite(array("canSync" => 1));
		foreach($all_site as $site_id => $site_info)
		{
			$this->connectSite($site_id);
			$this->syncMerchantByAff($aff_id);
			$this->syncLinksByAff($aff_id);
		}
		
		$arr_log["AffectedCount"] = sizeof($all_site);
		$arr_log["UpdatedCount"] = sizeof($all_site);
		$this->endJob($arr_log);
	}
	
	function syncAllSiteMerchant($aff_id)
	{
		//add log
		$arr_log = array(
			"JobName" => __METHOD__,
			"AffId" => $aff_id,
			"MerchantId" => "",
			"SiteId" => 0,
			"AffectedCount" => 0,
			"UpdatedCount" => 0,
			"Detail" => "",
		);
		$this->addJob($arr_log);
		
		$all_site = $this->getAllSite(array("canSync" => 1));
		foreach($all_site as $site_id => $site_info)
		{
			$this->connectSite($site_id);
			$this->syncMerchantByAff($aff_id);
			//$this->syncLinksByAff($aff_id);
		}
		
		$arr_log["AffectedCount"] = sizeof($all_site);
		$arr_log["UpdatedCount"] = sizeof($all_site);
		$this->endJob($arr_log);
	}
	
	function syncAllSiteLinks($aff_id)
	{
		//add log
		$arr_log = array(
			"JobName" => __METHOD__,
			"AffId" => $aff_id,
			"MerchantId" => "",
			"SiteId" => 0,
			"AffectedCount" => 0,
			"UpdatedCount" => 0,
			"Detail" => "",
		);
		$this->addJob($arr_log);
		
		$all_site = $this->getAllSite(array("canSync" => 1));
		foreach($all_site as $site_id => $site_info)
		{
			$this->connectSite($site_id);
			//$this->syncMerchantByAff($aff_id);
			$this->syncLinksByAff($aff_id);
		}
		
		$arr_log["AffectedCount"] = sizeof($all_site);
		$arr_log["UpdatedCount"] = sizeof($all_site);
		$this->endJob($arr_log);
	}
	
	function syncMerchantByAff($aff_id,$mer_id="")
	{
		$arr_return = array(
			"LocalMerchantCount" => 0,
			"RemoteMerchantCount" => 0,
			"NotMatchedCount" => 0,
			"InsertedCount" => 0,
			"UpdatedCount" => 0,
			"NoNeedUpdatedCount" => 0,
		);
		
		//by ike 20130719
		return $arr_return;
		//add log
		$arr_log = array(
			"JobName" => __METHOD__,
			"AffId" => $aff_id,
			"MerchantId" => $mer_id,
			"SiteId" => $this->current_site_id,
			"AffectedCount" => 0,
			"UpdatedCount" => 0,
			"Detail" => "",
		);
		$this->addJob($arr_log);
		
		echo "start syncMerchantByAff($aff_id) for site " . $this->site_info["SiteName"] . " ...\n";
		
		$sql = "SELECT ID,`Status`,LastUpdateTime FROM aff_mer_list WHERE AffID = '$aff_id'";
		if($mer_id) $sql .= " and ID = '".addslashes($mer_id)."'";
		$mer_list_to = $this->objRemoteMysql->getRows($sql,"ID");
		$arr_return["RemoteMerchantCount"] = sizeof($mer_list_to);
		
		$mer_list_from = $this->getAllAffMerchant($aff_id,$mer_id,"",array("SiteCountry" => $this->site_info["SiteCountry"]));
		//try to find those merchant that country not matched ,but had been connetced.
		$sql = "SELECT DISTINCT	MerIDinAff,MerID FROM wf_mer_in_aff WHERE AffID = '$aff_id' and IsUsing = 1 and MerID IN (SELECT ID FROM normalmerchant)";
		$connected_merchants = $this->objRemoteMysql->getRows($sql,"MerIDinAff");
		$more_mer_id = array();
		foreach($connected_merchants as $remote_id => $info)
		{
			if(!isset($mer_list_from[$remote_id])) $more_mer_id[] = $remote_id;
		}
		
		if(sizeof($more_mer_id))
		{
			$more_mer_list = $this->getAllAffMerchant($aff_id,$mer_id,"",array("Ids" => $more_mer_id));
			foreach($more_mer_list as $k => $v) $mer_list_from[$k] = $v;
			print_r($more_mer_list);
		}
		
		$this->fixEnocding($mer_list_from,"merchant");
		$arr_return["LocalMerchantCount"] = sizeof($mer_list_from);
		
		$arr_to_siteclosed = array();
		foreach($mer_list_to as $_mer_id => &$_mer_to)
		{
			if(!isset($mer_list_from[$_mer_id]))
			{
				//change to siteclosed;
				$arr_to_siteclosed[] = "'" . addslashes($_mer_id) . "'";
				unset($mer_list_to[$_mer_id]);
				$arr_return["NotMatchedCount"] ++;
			}
		}
		
		if(sizeof($arr_to_siteclosed))
		{
			$sql = "update aff_mer_list set `Status` = 'siteclosed',LastUpdateTime = now() where ID in (" . implode(",",$arr_to_siteclosed) . ")";
			$this->objRemoteMysql->query($sql);
			unset($arr_to_siteclosed);
		}

		foreach($mer_list_from as $_mer_id => &$_mer_from)
		{
			if(isset($mer_list_to[$_mer_id]))
			{
				if($mer_list_to[$_mer_id]["LastUpdateTime"] == $_mer_from["LastUpdateTime"])
				{
					$_mer_from["syncFlag"] = "NoSync";
					$arr_return["NoNeedUpdatedCount"] ++;
				}
				elseif($mer_list_to[$_mer_id]["Status"] == 'approvaled but neednot add' || $mer_list_to[$_mer_id]["Status"] == 'approvaled but add next time')
				{
					$_mer_from["syncFlag"] = "UpdateExceptStatus";
					$arr_return["UpdatedCount"] ++;
				}
				else
				{
					$_mer_from["syncFlag"] = "Update";
					$arr_return["UpdatedCount"] ++;
				}
			}
			else
			{
				$arr_return["InsertedCount"] ++;
				$_mer_from["syncFlag"] = "Insert";
			}
		}
		
		$sql_insert = "INSERT ignore INTO aff_mer_list (AffID, ID, `Name`, EPC, EPC30d, `Status`, Remark, LastUpdateTime) VALUES ";
		$arr_insert = array();
		foreach($mer_list_from as $_mer_id => &$_mer_from)
		{
			switch($_mer_from["syncFlag"])
			{
				case "UpdateExceptStatus":
					//$sql = "UPDATE aff_mer_list SET	Name = '".addslashes($_mer_from["MerchantName"])."' , EPC = '".addslashes($_mer_from["MerchantEPC"])."' , EPC30d = '".addslashes($_mer_from["MerchantEPC30d"])."' , LastUpdateTime = '".addslashes($_mer_from["LastUpdateTime"])."' WHERE AffID = '$aff_id' AND ID = '".addslashes($_mer_from["AffMerchantId"])."'";
					//$this->objRemoteMysql->query($sql);
					break;
				case "Update":
					$sql = "UPDATE aff_mer_list SET	Name = '".addslashes($_mer_from["MerchantName"])."' , EPC = '".addslashes($_mer_from["MerchantEPC"])."' , EPC30d = '".addslashes($_mer_from["MerchantEPC30d"])."' , Status = '".addslashes($_mer_from["MerchantStatus"])."' , Remark = '".addslashes($_mer_from["MerchantRemark"])."' , LastUpdateTime = '".addslashes($_mer_from["LastUpdateTime"])."' WHERE AffID = '$aff_id' AND ID = '".addslashes($_mer_from["AffMerchantId"])."'";
					$this->objRemoteMysql->query($sql);
					break;
				case "Insert":
					$arr = array(
						$aff_id,
						"'".addslashes($_mer_from["AffMerchantId"])."'",
						"'".addslashes($_mer_from["MerchantName"])."'",
						"'".addslashes($_mer_from["MerchantEPC"])."'",
						"'".addslashes($_mer_from["MerchantEPC30d"])."'",
						"'".addslashes($_mer_from["MerchantStatus"])."'",
						"'".addslashes($_mer_from["MerchantRemark"])."'",
						"'".addslashes($_mer_from["LastUpdateTime"])."'",
					);
					$arr_insert[] = "(" . implode(",",$arr) . ")";
					if(sizeof($arr_insert) > 100)
					{
						$sql = $sql_insert . implode(",",$arr_insert);
						$this->objRemoteMysql->query($sql);
						$arr_insert = array();
					}
					break;
			}//end switch
		}//end foreach
		
		if(sizeof($arr_insert) > 0)
		{
			$sql = $sql_insert . implode(",",$arr_insert);
			$this->objRemoteMysql->query($sql);
			$arr_insert = array();
		}
		
		echo "syncMerchantByAff($aff_id) for site " . $this->site_info["SiteName"] . " finished:\n";
		print_r($arr_return);
		
		$arr_log["AffectedCount"] = $arr_return["LocalMerchantCount"];
		$arr_log["UpdatedCount"] = $arr_return["UpdatedCount"];
		$arr_log["Detail"] = print_r($arr_return,true);
		$this->endJob($arr_log);
		
		return $arr_return;
	}
	
	function syncLinksToCouponApproval($aff_id)
	{
		$arr_return = array(
			"RemoteLinksCount" => 0,
			"LocalLinksCount" => 0,
			"NotMatchedCount" => 0,
			"InsertedCount" => 0,
			"UpdatedCount" => 0,
			"NoNeedUpdatedCount" => 0,
		);
		
		if($aff_id == 10000 && strtolower($this->site_info["SiteName"]) != "csus") return $arr_return;
		
		$mer_id = $aff_id;
		echo "syncLinksToCouponApproval for $aff_id for site " . $this->site_info["SiteName"] . " ...\n";
		
		//get all link from pending links system
		$list_from = $this->getAllLinksByAffAndMerchant($aff_id,$mer_id,$this->site_info["SiteCountry"]);
		$this->fixEnocding($list_from,"link");
		$arr_return["LocalLinksCount"] = sizeof($list_from);
		
		//get all synced links from remote server
		//UserName -> PENDINGLINKS SYSTEM\t10000\tlinkid:
		//AddTime -> 

		$sync_prefix = "PENDINGLINKS";
		$sync_aff_mer = "$sync_prefix\t$aff_id\t$mer_id\t";
		
		$sql = "SELECT ID,UserName,AddTime,Status FROM normalcoupon_approval WHERE UserName like '" . $sync_aff_mer . "%'";
		$list_to = $this->objRemoteMysql->getRows($sql);
		$arr_return["RemoteLinksCount"] = sizeof($list_to);
		
		$list_to_temp = array();
		foreach($list_to as $k => $_link_to)
		{
			list($null,$null,$null,$_link_id) = explode("\t",$_link_to["UserName"]);
			if(isset($list_from[$_link_id]))
			{
				$list_to_temp[$_link_id] = $_link_to;
			}
			else
			{
				$arr_return["NotMatchedCount"] ++;
			}
			unset($list_to[$k]);
		}

		$list_to = $list_to_temp;
		
		foreach($list_from as $_link_id => &$_link_from)
		{
			if(isset($list_to[$_link_id]))
			{
				if($list_to[$_link_id]["AddTime"] == $_link_from["LastUpdateTime"] || $list_to[$_link_id]["Status"] != "initial")
				{
					$_link_from["syncFlag"] = "NoSync";
					$arr_return["NoNeedUpdatedCount"] ++;
				}
				else
				{
					$_link_from["syncFlag"] = "Update";
					$arr_return["UpdatedCount"] ++;
				}
			}
			else
			{
				$_link_from["syncFlag"] = "Insert";
				$arr_return["InsertedCount"] ++;
			}
		}
		
		$sql_insert = "INSERT INTO `normalcoupon_approval`(`Title`, `Code`, `Remark`, `URL`, `Tag`, `CategoryID`, `MerchantID`,`MerchantStyle`, `IsExclusive`, `ExpireTime`, `ImgUrl`, `UserName`,`Email`,`AddTime`,`LastChangeTime`,`Status`,`ApprovalDate`,`SessionId`,`IP`) VALUES ";
		$arr_insert = array();
		foreach($list_from as $_link_id => &$_link_from)
		{
			$code = "";
			if(preg_match("/enter code ([^ ]+) at checkout/i",$_link_from["LinkDesc"],$matches))
			{
				$code = $matches[1];
			}
			elseif(preg_match("/when you check out: ([^ ]+)\\./i",$_link_from["LinkDesc"],$matches))
			{
				$code = $matches[1];
			}
			elseif(preg_match("/when you enter code ([^ ]+)\\./i",$_link_from["LinkDesc"],$matches))
			{
				$code = $matches[1];
			}

			switch($_link_from["syncFlag"])
			{
				case "Update":
					$sql = "UPDATE normalcoupon_approval SET `Code` = '".addslashes($code)."',Title = '".addslashes($_link_from["LinkName"])."', Remark = '".addslashes($_link_from["LinkDesc"])."', ExpireTime = '".addslashes($_link_from["LinkEndDate"])."', Remark = '".addslashes($_link_from["LinkHtmlCode"])."', URL = '".addslashes($_link_from["LinkOriginalUrl"])."', ImgUrl = '".addslashes($_link_from["LinkImageUrl"])."', LastChangeTime = now(), AddTime = '".addslashes($_link_from["LastUpdateTime"])."' WHERE ID = '".addslashes($list_to[$_link_id]["ID"])."'";
					$this->objRemoteMysql->query($sql);
					break;
				case "Insert":
					$arr = array(
						"'".addslashes($_link_from["LinkName"])."'",
						"'".addslashes($code)."'",
						"'".addslashes($_link_from["LinkDesc"])."'",
						"'".addslashes($_link_from["LinkOriginalUrl"])."'",
						"''",
						17,	//Flowers & Gourmet
						178,	//Amazon
						1,	//MerchantStyle=1:internal merchant
						0,	//not Exclusive
						"'".addslashes($_link_from["LinkEndDate"])."'",
						"'".addslashes($_link_from["LinkImageUrl"])."'",
						"'" . $sync_aff_mer . addslashes($_link_from["AffLinkId"]) . "'",//username
						"''",
						"'".addslashes($_link_from["LastUpdateTime"])."'",
						"now()",
						"'initial'",
						"'".date("Y-m-d H:i:s",time()+345600)."'",
						0,
						"''",
					);
					$arr_insert[] = "(" . implode(",",$arr) . ")";
					if(sizeof($arr_insert) > 100)
					{
						$sql = $sql_insert . implode(",",$arr_insert);
						//echo "sql: $sql","\n";
						$this->objRemoteMysql->query($sql);
						$arr_insert = array();
					}
					break;
			}//end switch
		}//end foreach
		
		if(sizeof($arr_insert) > 0)
		{
			$sql = $sql_insert . implode(",",$arr_insert);
			//echo "sql: $sql","\n";
			$this->objRemoteMysql->query($sql);
			$arr_insert = array();
		}
		
		echo "syncLinksToCouponApproval($sync_aff_mer) for site " . $this->site_info["SiteName"] . " finished:\n";
		print_r($arr_return);
		return $arr_return;
	}
	
	function syncLinksToCPQByAffIdAndMerId($aff_id, $aff_mer_id, $aff_mer_name){
		$CPQdb = $this->connectCPQ();
		$arr_return = array(
			"RemoteLinksCount" => 0,
			"LocalLinksCount" => 0,
			"NotMatchedCount" => 0,
			"InsertedCount" => 0,
			"UpdatedCount" => 0,
			"NoNeedUpdatedCount" => 0,
		);
		
		$links = $this->getAllLinksByAffAndMerchant($aff_id, $aff_mer_id,"",3);
		if(empty($links)) return $arr_return;
		$links_cpq = $this->getAllLinksByAffIdAndMerIdInCPQ($aff_id, $aff_mer_id,false,90);
		
		$arr_return['RemoteLinksCount'] = count($links_cpq);
		$arr_return['LocalLinksCount'] = count($links);
		
		foreach($links_cpq as $key => $val) {
			if(!isset($links[$key])) {
				unset($links_cpq[$key]);
				$arr_return["NotMatchedCount"] ++;
			}
		}
		
		foreach($links as $key => &$val) {
			if(isset($links_cpq[$key])) {
//				if($links_cpq[$key]["SrcLastUpdate"] == $val["LastUpdateTime"]) {
					$val["syncFlag"] = "NoSync";
					$arr_return["NoNeedUpdatedCount"] ++;
//				} else {
//					$val["syncFlag"] = "Update";
//					$arr_return["UpdatedCount"] ++;
//				}
			} else {
				$val["syncFlag"] = "Insert";
				$arr_return["InsertedCount"] ++;
			}
		}
		
		$sql_insert = "INSERT ignore INTO `coupon_queue`(`AffiliateID`,`AffiliateMerchantID`,`AffiliateMerchantName`,`DataSource`,`LinkID`,`LinkName`,`LinkDesc`,`StartDate`,`EndDate`,`PromotionType`,`Code`,`HtmlCode`,`AffUrl`,`OriginalUrl`,`ImageUrl`,`AddTime`,`LastChangeTime`,`SrcLastUpdate`,`Status`,`SourceType`) VALUES ";
		$arr_insert = array();
		foreach($links as $key => &$val) {
			switch($val["LinkPromoType"]) {
				case 'N/A':
					$val["LinkPromoType"] = 'UNKNOWN';
					break;
				default:
					$val["LinkPromoType"] = in_array(strtoupper($val["LinkPromoType"]), array('UNKNOWN','COUPON','FREE SHIPPING')) ? strtoupper($val["LinkPromoType"]) : 'UNKNOWN';
			}
			$status_update = "UPDATE";
			$status_insert = "NEW";
			//if($val["LinkEndDate"] >= date("Y-m-d H:i:s")){
			if(($val["LinkEndDate"] < date("Y-m-d H:i:s")) && ($val["LinkEndDate"] != '0000-00-00 00:00:00')){
				$status_update = "IGNORED";
				$status_insert = "IGNORED";
			}elseif(isset($links_cpq[$key]) && $links_cpq[$key]['Status']=='NEW'){
				$status_update = "NEW";
			}else{
				$status_update = "UPDATE";
			}
			switch($val["syncFlag"]) {
				case "Update":
					$sql = "UPDATE `coupon_queue` SET 
						AffiliateMerchantName = '".addslashes($aff_mer_name)."', 
						DataSource = '".addslashes($val["DataSource"])."', 
						LinkName = '".addslashes($val["LinkName"])."', 
						LinkDesc = '".addslashes($val["LinkDesc"])."', 
						StartDate = '".addslashes($val["LinkStartDate"])."', 
						EndDate = '".addslashes($val["LinkEndDate"])."', 
						PromotionType = '".addslashes($val["LinkPromoType"])."', 
						Code = '".addslashes($val["LinkCode"])."', 
						HtmlCode = '".addslashes($val["LinkHtmlCode"])."', 
						AffUrl = '".addslashes($val["LinkAffUrl"])."', 
						OriginalUrl = '".addslashes($val["LinkOriginalUrl"])."', 
						ImageUrl = '".addslashes($val["LinkImageUrl"])."', 
						LastChangeTime = now(), 
						SrcLastUpdate = '".addslashes($val["LastUpdateTime"])."', 
						Status = '" . $status_update . "' ,
						SourceType = 'AFFILIATE'
					WHERE AffiliateID = '$aff_id' AND AffiliateMerchantID = '".addslashes($aff_mer_id)."' AND LinkID = '".addslashes($val["AffLinkId"])."' and SourceType = 'AFFILIATE'";
					$CPQdb->query($sql);
					break;
				case "Insert":
					$arr = array(
						 $aff_id,
						 "'".addslashes($aff_mer_id)."'",
						 "'".addslashes($aff_mer_name)."'",
						 "'".addslashes($val["DataSource"])."'",
						 "'".addslashes($val["AffLinkId"])."'",
						 "'".addslashes($val["LinkName"])."'",
						 "'".addslashes($val["LinkDesc"])."'",
						 "'".addslashes($val["LinkStartDate"])."'",
						 "'".addslashes($val["LinkEndDate"])."'",
						 "'".addslashes($val["LinkPromoType"])."'",
						 "'".addslashes($val["LinkCode"])."'",
						 "'".addslashes($val["LinkHtmlCode"])."'",
						 "'".addslashes($val["LinkAffUrl"])."'",
						 "'".addslashes($val["LinkOriginalUrl"])."'",
						 "'".addslashes($val["LinkImageUrl"])."'",
						 "NOW()",
						 "NOW()",
						 "'".addslashes($val["LastUpdateTime"])."'",
						 "'".$status_insert."'",
						 "'AFFILIATE'",
					);
					$arr_insert[] = "(" . implode(",",$arr) . ")";
					if(sizeof($arr_insert) > 100) {
						$sql = $sql_insert . implode(",",$arr_insert);
						$CPQdb->query($sql);
						$arr_insert = array();
					}
					break;
			}//end switch
		}//end foreach

		if(sizeof($arr_insert) > 0) {
			$sql = $sql_insert . implode(",",$arr_insert);
			$CPQdb->query($sql);
			$arr_insert = array();
		}
		
		return $arr_return;
	}

	function updateLinksStatusByAffIdAndMerIdInCPQ($site){
		$arr_return = array(
			"MaxAffectedRows"=>0,
			"MaxAffectedRowsSql"=>''
		);
		$CPQdb = $this->connectCPQ();
		$sql = "SELECT aff_id, aff_mer_id, link_id, internal_merid, proc_status FROM `wf_aff_links` WHERE proc_status='added' OR proc_status='pending'";
		$links = $this->objRemoteMysql->getRows($sql);

		foreach($links as $key => $val) {
			if(!empty($val['aff_id']) && !empty($val['aff_mer_id']) && !empty($val['link_id']) && !empty($val['internal_merid'])){
				$status = $val['proc_status']=='added' ? 'DONE' : 'NEW';
				$update_sql = "update task_coupon_pending set 
					Status='".$status."' WHERE Site='".$site."' AND 
					AffiliateID='".$val['aff_id']."' AND 
					AffiliateMerchantID='".$val['aff_mer_id']."' 
					AND LinkID='".addslashes($val['link_id'])."'";
				//echo $update_sql."\n";
				$CPQdb->query($update_sql);
				$AffectedRows = $CPQdb->getAffectedRows();
				if($AffectedRows > $arr_return["MaxAffectedRows"]){
					$arr_return["MaxAffectedRows"] = $AffectedRows;
					$arr_return["MaxAffectedRowsSql"] = $update_sql;
				}
				if(!isset($arr_return[$site])){
					$arr_return[$site] = array($val['proc_status']=>1);
				} else {
					if(!isset($arr_return[$site][$val['proc_status']])){
						$arr_return[$site][$val['proc_status']] = 1;
					} else {
						$arr_return[$site][$val['proc_status']]++;
					}
				}
			}
		}
		return $arr_return;
	}

	function syncMerchantByAffInCPQ($aff_id, $mer_id=""){
		$CPQdb = $this->connectCPQ();
		$arr_return = array(
			"LocalMerchantCount" => 0,
			"RemoteMerchantCount" => 0,
			"NotMatchedCount" => 0,
			"InsertedCount" => 0,
			"UpdatedCount" => 0,
			"NoNeedUpdatedCount" => 0,
		);

		$mer_list_to = $arr_mer_cpq = $this->getAllAffMerchantIdInCPQ($aff_id, $mer_id);
		$mer_list_from = $this->getAllAffMerchant($aff_id, $mer_id);
		
		//tmp sync program 
		//if($aff_id != 1 && $aff_id != 2 && $aff_id != 3 && $aff_id != 6 && $aff_id != 7 && $aff_id != 8 && $aff_id != 12 && $aff_id != 13 && $aff_id != 14 && $aff_id != 18 && $aff_id != 30 && $aff_id != 32 && $aff_id != 10){
		$aff_arr = array(1,2,3,5,6,7,8,10,12,13,14,15,18,20,22,27,28,30,32,35,58,133,181);
		if(!in_array($aff_id, $aff_arr)){
			$objProgram = new ProgramDb();
			$arr_prgm = array();
			foreach($mer_list_from as $_mer_id => $val) {				
				$arr_prgm[$val["AffMerchantId"]] = array(
						"AffId" => $aff_id,
						"IdInAff" => $val["AffMerchantId"],
						"Name" => addslashes($val["MerchantName"]) , 
						"EPCDefault" => addslashes($val["MerchantEPC"]) , 
						"EPC30d" => addslashes($val["MerchantEPC30d"]) , 
						"StatusInAffRemark" => addslashes($val["MerchantStatus"]) , 
						"Remark" => addslashes($val["MerchantRemark"]) , 
						"LastUpdateTime" => date("Y-m-d H:i:s"),
						"TargetCountryExt" => addslashes($val["MerchantCountry"]),
					);
				if(count($arr_prgm) >= 200){
					$objProgram->updateProgram($aff_id, $arr_prgm);
					$arr_prgm = array();
				}
			}
		
			if(count($arr_prgm) > 0){
				$objProgram->updateProgram($aff_id, $arr_prgm);
				$arr_prgm = array();
			}
		}

		$arr_return["LocalMerchantCount"] = sizeof($mer_list_from);
		$arr_return["RemoteMerchantCount"] = sizeof($mer_list_to);
		
		$arr_mer_delete = array();
		foreach($mer_list_to as $key => &$val) {
			if(!isset($mer_list_from[$key])) {
				$arr_mer_delete[] = "'" . addslashes($key) . "'";
				unset($mer_list_to[$key]);
				$arr_return["NotMatchedCount"] ++;
			}
		}
		
		if(sizeof($arr_mer_delete)) {
			$sql = "DELETE FROM `affiliate_merchant` WHERE AffId='".$aff_id."' AND AffMerchantId IN (" . implode(",", $arr_mer_delete) . ")";
			$CPQdb->query($sql);
		}

		foreach($mer_list_from as $key => &$val){
			if(isset($mer_list_to[$key])) {
				if($mer_list_to[$key]["LastUpdateTime"] == $val["LastUpdateTime"]){
					$val["syncFlag"] = "NoSync";
					$arr_return["NoNeedUpdatedCount"] ++;
				} else {
					$val["syncFlag"] = "Update";
					$arr_return["UpdatedCount"] ++;
				}
			} else {
				$arr_return["InsertedCount"] ++;
				$val["syncFlag"] = "Insert";
			}
		}
		$sql_insert = "INSERT INTO `affiliate_merchant`
		            (`ProgramId`,
		             `AffId`,
		             `AffMerchantId`,
		             `MerchantName`,
		             `MerchantEPC`,
		             `MerchantEPC30d`,
		             `MerchantStatus`,
		             `MerchantRemark`,
		             `LastUpdateTime`,
		             `LastUpdateLinkTime`,
		             `MerchantLinkCount`,
		             `MerchantFeedCount`,
		             `LastUpdateFeedTime`,
		             `MerchantCountry`)
		VALUES ";
		
		$arr_insert = array();
		foreach($mer_list_from as $_mer_id => &$val) {
			switch($val["syncFlag"]) {
				case "Update":
					$sql = "UPDATE affiliate_merchant SET	
						ProgramId = '".addslashes($val["ProgramId"])."' , 
						MerchantName = '".addslashes($val["MerchantName"])."' , 
						MerchantEPC = '".addslashes($val["MerchantEPC"])."' , 
						MerchantEPC30d = '".addslashes($val["MerchantEPC30d"])."' , 
						MerchantStatus = '".addslashes($val["MerchantStatus"])."' , 
						MerchantRemark = '".addslashes($val["MerchantRemark"])."' , 
						LastUpdateTime = '".addslashes($val["LastUpdateTime"])."' ,
						LastUpdateLinkTime = '".addslashes($val["LastUpdateLinkTime"])."' ,
						MerchantLinkCount = '".addslashes($val["MerchantLinkCount"])."' ,
						MerchantFeedCount = '".addslashes($val["MerchantFeedCount"])."' ,
						LastUpdateFeedTime = '".addslashes($val["LastUpdateFeedTime"])."' ,
						MerchantCountry = '".addslashes($val["MerchantCountry"])."' 
					WHERE AffID = '$aff_id' AND AffMerchantId = '".addslashes($val["AffMerchantId"])."'";
					$CPQdb->query($sql);
					break;
				case "Insert":
					$arr = array(
						"'".addslashes($val["ProgramId"])."'",
						$aff_id,
						"'".addslashes($val["AffMerchantId"])."'",
						"'".addslashes($val["MerchantName"])."'",
						"'".addslashes($val["MerchantEPC"])."'",
						"'".addslashes($val["MerchantEPC30d"])."'",
						"'".addslashes($val["MerchantStatus"])."'",
						"'".addslashes($val["MerchantRemark"])."'",
						"'".addslashes($val["LastUpdateTime"])."'",
						"'".addslashes($val["LastUpdateLinkTime"])."'",
						"'".addslashes($val["MerchantLinkCount"])."'",
						"'".addslashes($val["MerchantFeedCount"])."'",
						"'".addslashes($val["LastUpdateFeedTime"])."'",
						"'".addslashes($val["MerchantCountry"])."'",
					);
					$arr_insert[] = "(" . implode(",",$arr) . ")";
					if(sizeof($arr_insert) > 100) {
						$sql = $sql_insert . implode(",",$arr_insert);
						$CPQdb->query($sql);
						$arr_insert = array();
						
					}
					break;
			}//end switch
		}//end foreach
		
		if(sizeof($arr_insert) > 0) {
			$sql = $sql_insert . implode(",",$arr_insert);
			$CPQdb->query($sql);
			$arr_insert = array();			
		}		
		return $arr_return;
	}

	function getAllAffMerchantIdInCPQ($aff_id,$mer_id="",$key="",$cond=array()){
		$CPQdb = $this->connectCPQ();
		$sql = "SELECT * FROM affiliate_merchant WHERE AffId = '$aff_id'";
		if($mer_id) $sql .= " and AffMerchantId = '".addslashes($mer_id)."'";
		if(isset($cond["SiteCountry"]) && $cond["SiteCountry"]) $sql .= " and (MerchantCountry is null or MerchantCountry in ('','".addslashes($cond["SiteCountry"])."'))";
		if(isset($cond["Ids"]) && is_array($cond["Ids"]))
		{
			$arr_ids = array();
			foreach($cond["Ids"] as $id) $arr_ids[] = "'" . addslashes($id) . "'";
			$sql .= " and AffMerchantId in (" . implode(",",$arr_ids) . ")";
		}
		if(!$key) $key = "AffMerchantId";
		return $CPQdb->getRows($sql,$key);
	}

	function updateLinkStatusToDownInCPQ() {
		$CPQdb = $this->connectCPQ();
		if(isset($this->matchLinksInCPQData["update"]) && is_array($this->matchLinksInCPQData["update"])){
			$ids = array();
			foreach($this->matchLinksInCPQData["update"] as $key => $val){
				if(!empty($key)){
					$ids[] = $key;
				}
				if(count($ids)>500){
					$sql = "UPDATE coupon_queue SET `Status`='ASSIGNED' WHERE `ID` IN (".implode(",", $ids).")";
					$CPQdb->query($sql);
					$ids = array();
				}
			}
			if(count($ids)>0){
				$sql = "UPDATE coupon_queue SET `Status`='ASSIGNED' WHERE `ID` IN (".implode(",", $ids).")";
				$CPQdb->query($sql);
				$ids = array();
			}
		}
		
		if(isset($this->matchLinksInCPQData["insert"]) && is_array($this->matchLinksInCPQData["insert"])){
			$ids = array();
			foreach($this->matchLinksInCPQData["insert"] as $key => $val){
				if(!empty($key)){
					$ids[] = $key;
				}
				if(count($ids)>500){
					$sql = "UPDATE coupon_queue SET `Status`='ASSIGNED' WHERE `ID` IN (".implode(",", $ids).")";
					$CPQdb->query($sql);
					$ids = array();
				}
			}
			if(count($ids)>0){
				$sql = "UPDATE coupon_queue SET `Status`='ASSIGNED' WHERE `ID` IN (".implode(",", $ids).")";
				$CPQdb->query($sql);
				$ids = array();
			}
		}
	}
	
	function matchLinkToEditorInCPQ(){
		$CPQdb = $this->connectCPQ();
		$editors = $this->getAllEditorInCPQ();
		if(isset($this->matchLinksInCPQData["insert"]) && is_array($this->matchLinksInCPQData["insert"])){
			$ids = array();
			foreach($this->matchLinksInCPQData["insert"] as $key => $val){
				if(!empty($key)){
					$ids[] = $key;
				}
				if(count($ids)>5){
					$editor = array_rand($editors);
					$sql = "UPDATE task_coupon_pending SET `Editor`='".$editor."' WHERE `CouponQueueId` IN (".implode(",", $ids).")";
					$CPQdb->query($sql);
					$ids = array();
				}
			}
			if(count($ids)>0){
				$editor = array_rand($editors);
				$sql = "UPDATE task_coupon_pending SET `Editor`='".$editor."' WHERE `CouponQueueId` IN (".implode(",", $ids).")";
				$CPQdb->query($sql);
				$ids = array();
			}
		}
	}

	function getAllEditorInCPQ(){
		$CPQdb = $this->connectCPQ();
		$sql = "SELECT * FROM task_coupon_pending_editor";
		return $CPQdb->getRows($sql, 'Editor');
	}

	function getAllLinksByAffIdAndMerIdInCPQ($aff_id, $aff_mer_id, $isnotdone=false,$recentday=0){
		$CPQdb = $this->connectCPQ();
		$arr_where = array();
		if($isnotdone) $arr_where[] = "Status in ('NEW','UPDATE')";
		if(is_numeric($recentday) && $recentday > 0)
		{
			$recentday = intval($recentday);
			$arr_where[] = "`AddTime` > now() - interval $recentday day";
		}
		
		$sql = "SELECT ID, AffiliateMerchantID, LinkID, SrcLastUpdate, Status, AffiliateID, DataSource, PromotionType FROM `coupon_queue` WHERE SourceType = 'AFFILIATE' and `AffiliateID` = '$aff_id' AND `AffiliateMerchantID`='$aff_mer_id'";
		if(sizeof($arr_where) > 0)
		{
			$sql .= " and " . implode(" and ", $arr_where);
		}
		$result = $CPQdb->getRows($sql,"LinkID");
		return $result;
	}

	function syncLinksByAffAndMerchant($aff_id,$mer_id,$internal_merid)
	{
		if($aff_id == 10000)//for some inhause aff
		{
			return $this->syncLinksToCouponApproval($aff_id);
		}
		
		$arr_return = array(
			"RemoteLinksCount" => 0,
			"LocalLinksCount" => 0,
			"NotMatchedCount" => 0,
			"InsertedCount" => 0,
			"UpdatedCount" => 0,
			"NoNeedUpdatedCount" => 0,
		);
		
		//by ike 20130129: this func has been paused.
		return $arr_return;
		
		echo "sync links for $aff_id,$mer_id for site " . $this->site_info["SiteName"] . " ...\n";
		
		if($this->isIgnoredMerchant($aff_id,$mer_id,$internal_merid))
		{
			echo "warning: ignored merchant: $aff_id,$mer_id,$internal_merid\n";
			return $arr_return;
		}
		
		$merinfo = $this->getApprovalAffMerchant($aff_id,$mer_id);
		if(!isset($merinfo["MerchantName"]))
		{
			echo "warning: ($aff_id,$mer_id) is not approvaled or not exists ...\n";
			return $arr_return;
		}
		
		$list_from = $this->getAllLinksByAffAndMerchant($aff_id,$mer_id,$this->site_info["SiteCountry"]);
		$this->fixEnocding($list_from,"link");
		
		$arr_return["LocalLinksCount"] = sizeof($list_from);
		
		$sql = "SELECT link_id,proc_status,src_lastupdate FROM wf_aff_links WHERE aff_id = '$aff_id' and aff_mer_id = '".addslashes($mer_id)."' and internal_merid = '" . addslashes($internal_merid) . "'";
		$list_to = $this->objRemoteMysql->getRows($sql,"link_id");
		$arr_return["RemoteLinksCount"] = sizeof($list_to);
		
		foreach($list_to as $_link_id => &$_link_to)
		{
			if(!isset($list_from[$_link_id]))
			{
				//change to siteclosed;
				unset($list_to[$_link_id]);
				$arr_return["NotMatchedCount"] ++;
			}
		}
		
		foreach($list_from as $_link_id => &$_link_from)
		{
			if(isset($list_to[$_link_id]))
			{
				if($list_to[$_link_id]["src_lastupdate"] == $_link_from["LastUpdateTime"] || $list_to[$_link_id]["proc_status"] == "ignored")
				{
					$_link_from["syncFlag"] = "NoSync";
					$arr_return["NoNeedUpdatedCount"] ++;
				}
				else
				{
					$_link_from["syncFlag"] = "Update";
					$arr_return["UpdatedCount"] ++;
				}
			}
			else
			{
				$_link_from["syncFlag"] = "Insert";
				$arr_return["InsertedCount"] ++;
			}
		}
		
		$sql_insert = "INSERT ignore INTO wf_aff_links (id, aff_id, aff_mer_id, aff_mer_name, link_id, link_name, link_desc, start_date, end_date, promo_type, html_code, original_url, image_url, proc_status, lastchangetime, internal_merid, src_lastupdate) VALUES ";
		$arr_insert = array();
		foreach($list_from as $_link_id => &$_link_from)
		{
			switch($_link_from["syncFlag"])
			{
				case "Update":
					$sql = "UPDATE wf_aff_links SET aff_mer_name = '".addslashes($merinfo["MerchantName"])."', link_name = '".addslashes($_link_from["LinkName"])."', link_desc = '".addslashes($_link_from["LinkDesc"])."', start_date = '".addslashes($_link_from["LinkStartDate"])."', end_date = '".addslashes($_link_from["LinkEndDate"])."', promo_type = '".addslashes($_link_from["LinkPromoType"])."', html_code = '".addslashes($_link_from["LinkHtmlCode"])."', original_url = '".addslashes($_link_from["LinkOriginalUrl"])."', image_url = '".addslashes($_link_from["LinkImageUrl"])."', lastchangetime = now(), internal_merid = '$internal_merid', src_lastupdate = '".addslashes($_link_from["LastUpdateTime"])."' WHERE aff_id = '$aff_id' AND aff_mer_id = '".addslashes($mer_id)."' and link_id = '".addslashes($_link_from["AffLinkId"])."'";
					$this->objRemoteMysql->query($sql);
					break;
				case "Insert":
					$arr = array(
						"null",
						 $aff_id,
						 "'".addslashes($mer_id)."'",
						 "'".addslashes($merinfo["MerchantName"])."'",
						 "'".addslashes($_link_from["AffLinkId"])."'",
						 "'".addslashes($_link_from["LinkName"])."'",
						 "'".addslashes($_link_from["LinkDesc"])."'",
						 "'".addslashes($_link_from["LinkStartDate"])."'",
						 "'".addslashes($_link_from["LinkEndDate"])."'",
						 "'".addslashes($_link_from["LinkPromoType"])."'",
						 "'".addslashes($_link_from["LinkHtmlCode"])."'",
						 "'".addslashes($_link_from["LinkOriginalUrl"])."'",
						 "'".addslashes($_link_from["LinkImageUrl"])."'",
						 "'pending'",
						 "null",
						 "'$internal_merid'",
						 "'".addslashes($_link_from["LastUpdateTime"])."'",
					);
					$arr_insert[] = "(" . implode(",",$arr) . ")";
					if(sizeof($arr_insert) > 100)
					{
						$sql = $sql_insert . implode(",",$arr_insert);
						$this->objRemoteMysql->query($sql);
						$arr_insert = array();
					}
					break;
			}//end switch
		}//end foreach
		
		if(sizeof($arr_insert) > 0)
		{
			$sql = $sql_insert . implode(",",$arr_insert);
			$this->objRemoteMysql->query($sql);
			$arr_insert = array();
		}
		
		echo "syncLinksByAffAndMerchant($aff_id,$mer_id,$internal_merid) for site " . $this->site_info["SiteName"] . " finished:\n";
		print_r($arr_return);
		return $arr_return;
	}
	
	function syncLinksByAff($aff_id,$mer_id="")
	{
		$arr_return = array(
			"RemoteMerchantCount" => 0,
			"RemoteLinksCount" => 0,
			"LocalLinksCount" => 0,
			"NotMatchedCount" => 0,
			"InsertedCount" => 0,
			"UpdatedCount" => 0,
			"NoNeedUpdatedCount" => 0,
			"RemoteMerchantDetail" => array(),
		);
		
		//add log
		$arr_log = array(
			"JobName" => __METHOD__,
			"AffId" => $aff_id,
			"MerchantId" => $mer_id,
			"SiteId" => $this->current_site_id,
			"AffectedCount" => 0,
			"UpdatedCount" => 0,
			"Detail" => "",
		);
		$this->addJob($arr_log);
		
		if($aff_id == 10000)
		{
			$mer_list_to = array();
			$mer_list_to[] = array("MerIDinAff" => 10000,"MerID" => 178);
			$arr_return["RemoteMerchantCount"] = 1;
		}
		else
		{
			$sql = "SELECT DISTINCT	MerIDinAff,MerID FROM wf_mer_in_aff WHERE AffID = '$aff_id' and IsUsing = 1 and MerID IN (SELECT ID FROM normalmerchant)";
			if($mer_id) $sql .= " and MerIDinAff = '".addslashes($mer_id)."'";
			//$mer_list_to = $this->objRemoteMysql->getRows($sql,"MerIDinAff");
			$mer_list_to = $this->objRemoteMysql->getRows($sql);
			$arr_return["RemoteMerchantCount"] = sizeof($mer_list_to);
		}
		
		foreach($mer_list_to as &$_mer_info)
		{
			$_aff_mer_id = $_mer_info["MerIDinAff"];
			if(trim($_aff_mer_id) == "") continue;
			$arr_result = $this->syncLinksByAffAndMerchant($aff_id,$_aff_mer_id,$_mer_info["MerID"]);
			$arr_return["RemoteMerchantDetail"][$_aff_mer_id] = $arr_result;
			foreach($arr_result as $k => $v) $arr_return[$k] += $v;
		}
		
		unset($arr_return["RemoteMerchantDetail"]);
		echo "syncLinksByAff($aff_id,$mer_id) for site " . $this->site_info["SiteName"] . " finished.\n";
		print_r($arr_return);
		
		$arr_log["AffectedCount"] = $arr_return["LocalLinksCount"];
		$arr_log["UpdatedCount"] = $arr_return["UpdatedCount"];
		$arr_log["Detail"] = print_r($arr_return,true);
		$this->endJob($arr_log);
		
		return $arr_return;
	}
	
	function fixEnocding(&$arr_with_2_level,$forwhat)
	{
		$from_encoding = "UTF-8";
		if(!isset($this->site_info["SiteEncoding"])) return;
		$to_encoding = strtoupper($this->site_info["SiteEncoding"]);
		if(!$to_encoding) $to_encoding = "ISO-8859-1";
		if($from_encoding == $to_encoding) return;;
		
		if($forwhat == "merchant") $arrColNeedFix = array("MerchantName","MerchantRemark");
		elseif($forwhat == "feed" || $forwhat == "link") $arrColNeedFix = array("LinkName","LinkDesc","LinkHtmlCode");
		
		foreach($arr_with_2_level as $i => &$record)
		{
			foreach($arrColNeedFix as $col)
			{
				if(!isset($record[$col])) continue;
				if($record[$col] == "") continue;
				$iconvres = @iconv($from_encoding,$to_encoding,$record[$col]);
				if($iconvres === false)
				{
					echo "warning: iconv failed for string: " . $record[$col] . "\n";
					continue;
				}
				$record[$col] = $iconvres;
			}
		}
	}
}//end class
?>