<?php
class TaskCPQ extends Task
{
	function __construct($objMysql=null,$debug=false)
	{
		parent::__construct($objMysql);
		
		$this->no_code_patterns = array(
			'/coupon\s*(coude|code)s?\s*:\s*(n\/a|not required|none)\b/ismS',
			'/no coupon code needed/ismS',
			'/no coupon required/ismS',
			'/no promo code need/ismS',
			'/no coupon necessary/ismS',
			'/no code needed/ismS',
			'/coupon\s*(coude|code)s?\s*:$/ismS',
		);
		
		$this->site_short_names = array(
			'CSUS' => 'CSUS',
			'CSUK' => 'CSUK',
			'CSCA' => 'CSCA',
			'CSAU' => 'CSAU',
			'CSDE' => 'CSDE',
			'CSFR' => 'CSFR',
			'CSIN' => 'CSIN',
		);
		
		//store frontend mysql obj
		$this->front_mysql_objs = array();
		
		//store merchant codes data
		$this->cache_front_merchant_codes_expdate = array();
		$this->cache_front_merchant_codes = array();
		$this->cache_cpq_aff_merchant_codes = array();
		$this->cache_cpq_aff_merchant_codes_expdate = array();
		$this->all_editor_for_cpq = array();
		$this->all_editor_for_cpq_update = array();
		$this->updateDataCntArr = array();
		$this->updateLogArr = array();
		$this->debug = $debug;
	}

	function get_aff_with_merchant()
	{
		$sql = "select `ID`, `Name` from `wf_aff` where `ID` IN (select `AffId` from program group by AffId) AND `IsActive`='YES' order by Name";
		return $this->objMysql->getRows($sql, "ID");
	}
	
	function create_tasks_by_one_link($aff_id, $aff_mer_id, $site_merchant, $source_type, $link)
	{
		$stats = array("insert" => 0, "update" => 0, "total" => 0,);
		$blackmsg = "";
		/*
		 *	Check source id Take over
		 *	$link["DataSource"]
		 */
		$mqs = new MessageQueue();
		$cpq_editor = $update_cpq_editor  = $this->checkSourceTakeOver($link["DataSource"]);
		$o_cpq_editor = "";		
		if(!$cpq_editor){
			$o_cpq_editor_lang = $cpq_editor_lang = $this->get_one_cpq_editor_eachlanguage_by_proportion($site_merchant, $link);
			
			$update_cpq_editor_lang = $this->getEditorLinkAndProportionUpdate($site_merchant, $link);
			
			foreach($cpq_editor_lang as $k => $tmp_editor){
				$cpq_editor_lang[$k] = $this->getEditorTakeOver($tmp_editor,true);
			}
			
			foreach($update_cpq_editor_lang as $k2 => $tmp_editor2){
				//$update_cpq_editor_lang[$k2] = $this->getEditorTakeOver($tmp_editor2,true);
			}
		}else{
			$o_cpq_editor = $update_cpq_editor = $cpq_editor;
			$cpq_editor = $update_cpq_editor =  $this->getEditorTakeOver($cpq_editor,true);
		}		
		
		
		
		$insert_ids = array();
		$editCnt = 0;
		// create additional task when the groups of the site_merchant exist.
		if (!empty($site_merchant['groups']) && is_array($site_merchant['groups']))
		{
			foreach ($site_merchant['groups'] as $group)
			{
				if (strtoupper($link['PromotionType']) == 'COUPON' && strtoupper($group['Coupon']) == 'YES')
					$site_merchant["{$group['Site']}\t{$group['MerchantID']}"] = array("Site" => $group['Site'], "MerchantId" => $group['MerchantID'], );
				if (strtoupper($link['PromotionType']) == 'FREE SHIPPING' && strtoupper($group['Freeshipping']) == 'YES')
					$site_merchant["{$group['Site']}\t{$group['MerchantID']}"] = array("Site" => $group['Site'], "MerchantId" => $group['MerchantID'], );
			}
		}
		unset($site_merchant['groups']);
		foreach($site_merchant as $v)
		{
		    
			
			
			$site = $v["Site"];
			$site2 =strtolower($site);
			//SourceType = COMPETITOR,check black keyword
			if($source_type == "COMPETITOR"){
				//global black keyword check
				//title
//				$sql = "select ID,Keyword from black_list where ObjType = 'MERCHANT' and Scope like '%title%' and (BlackListType = 'GLOBAL' AND (Site = '' OR Site = '$site2')) and Status = 'ACTIVE'";
//				$rows = $this->objMysql->getRows($sql, "ID");
//				foreach($rows as $row){
//					if(stripos($link["LinkName"], trim($row["Keyword"])) !== false){
//						$blackmsg .= "\n LinkName:".$link["LinkName"].", CouponQueueId:" . $row['CouponQueueId'] . ",Site:{$site}, has black keyword: ". $row["Keyword"]."\n";
//						continue 2;
//					}
//				}
				
				//desc
				$sql = "select ID, Keyword from black_list where ObjType = 'MERCHANT' and Scope like '%cpqdes%' and (BlackListType = 'GLOBAL' AND (Site = '' OR Site = '$site2')) and Status = 'ACTIVE'";
				$rows = $this->objMysql->getRows($sql, "ID");
				foreach($rows as $row){
					if(stripos($link["LinkDesc"], trim($row["Keyword"])) !== false){
						$blackmsg .= "\n LinkDesc:".$link["LinkDesc"].", CouponQueueId:" . $row['CouponQueueId'] . ",Site:{$site}, has black keyword: ". $row["Keyword"]."\n";
						continue 2;
					}
				}
				
				//code
				$sql = "select ID, Keyword from black_list where ObjType = 'MERCHANT' and Scope like '%code%' and (BlackListType = 'GLOBAL' AND (Site = '' OR Site = '$site2')) and Status = 'ACTIVE'";
				$rows = $this->objMysql->getRows($sql, "ID");
				foreach($rows as $row){
					if(stripos($link["Code"], trim($row["Keyword"])) !== false){
						$blackmsg .= "\n Code:".$link["Code"].", CouponQueueId:" . $row['CouponQueueId'] . ",Site:{$site}, has black keyword: ". $row["Keyword"]."\n";
						continue 2;
					}
				}
			}
			
			
			
			
			if(strtolower($site) == 'csfr'){
				$lang = "csfr";
			}elseif(strtolower($site) == 'csde'){
				$lang = "csde";
			}else{
				$lang = "csen";
			}
			
			$in_cpq_editor = isset($cpq_editor_lang[$lang]) ? $cpq_editor_lang[$lang] : $cpq_editor;
			$in_cpq_editor_update = isset($update_cpq_editor_lang[$lang]) ? $update_cpq_editor_lang[$lang] : $update_cpq_editor;
			
			$mer_id = $v["MerchantId"];
			
			//check all source type by black keywords
			
			//code
			$sql = "select ID, Keyword from black_list where ObjType = 'MERCHANT' and Scope like '%code%'  and (BlackListType = 'MERCHANT' AND Site = '$site2' and ObjID = '$mer_id') and Status = 'ACTIVE'";
			$rows = $this->objMysql->getRows($sql, "ID");
			foreach($rows as $row){
				if(stripos($link["Code"], trim($row["Keyword"])) !== false){
					$blackmsg .= "\n Code:".$link["Code"].", CouponQueueId:" . $row['CouponQueueId'] . " MerchantId:" . $mer_id . ",Site:{$site}, has black keyword: ". $row["Keyword"]."\n";
					continue 2;
				}
			}
			
//			//title
//			$sql = "select ID, Keyword from black_list where ObjType = 'MERCHANT' and Scope like '%title%'  and (BlackListType = 'MERCHANT' AND Site = '$site2' and ObjID = '$mer_id') and Status = 'ACTIVE'";
//			$rows = $this->objMysql->getRows($sql, "ID");
//			foreach($rows as $row){
//				if(stripos($link["LinkName"], trim($row["Keyword"])) !== false){
//					$blackmsg .= "\n LinkName:".$link["LinkName"].", CouponQueueId:" . $row['CouponQueueId'] . " MerchantId:" . $mer_id . ",Site:{$site}, has black keyword: ". $row["Keyword"]."\n";
//					continue 2;
//				}
//			}
//			
//			//desc
//			$sql = "select ID, Keyword from black_list where ObjType = 'MERCHANT' and Scope like '%cpqdes%'  and (BlackListType = 'MERCHANT' AND Site = '$site2' and ObjID = '$mer_id') and Status = 'ACTIVE'";
//			$rows = $this->objMysql->getRows($sql, "ID");
//			foreach($rows as $row){
//				if(stripos($link["LinkDesc"], trim($row["Keyword"])) !== false){
//					$blackmsg .= "\n LinkDesc:".$link["LinkDesc"].", CouponQueueId:" . $row['CouponQueueId'] . " MerchantId:" . $mer_id . ",Site:{$site}, has black keyword: ". $row["Keyword"]."\n";
//					continue 2;
//				}
//			}
			
			//cpq block tool
			$msg = $this->checkCPQByBlockTool($link['ID'],$mer_id,$site);
			if($msg!=''){
				$blackmsg .= $msg;
				continue;
			}
			
			$is_active_program = "UNKNOWN";
			if(isset($v["Status"]))
			{
				if($v["Status"] == "Inactive") $is_active_program = "NO";
				else $is_active_program = "YES";
			}
			
			//add by devin for aff deal start
			if($link['PromotionType'] == "DEAL"){
				$error_reason = "";
				$isignored = false;
				//only aff data
				if($link['SourceType'] == 'COMPETITOR'){
					$isignored = true;
					$error_reason = "now not need deal from competitors";
//				}else if($aff_id != 1){//testing for cj
//					return;
//					$isignored = true;
				}else if($link['AddTime'] < '2015-03-28'){//date must be big than 0328
					$isignored = true;
					$error_reason = "data must be after 20150328 ";
				}
				
				if(!$isignored){
					//filter by annia in en site
					if($site2 == 'csus' || $site2=='csuk' || $site2=='csca' || $site2=='csau' ){
						preg_match('@(\boff\b)|(\bdiscount\b)|(\bsavings\b)|(\breduction\b)|(\bsave\b)|(\bfree\b)|(\bdelivery\b)|(\bshipping\b)|(\bcoupon\b)|(\bvoucher\b)|(\bcode\b)|(\bup to\b)|(\bsale\b)|(\bbuy one get one\b)|(\bbuy 1 get 1\b)|(\bBOGO\b)|(\bBNGN\b)|(\boffer\b)|(\bclearance\b)|(\bpostage\b)@mis', $link['LinkName']." ".$link['LinkDesc']." ".$link['HtmlCode'], $g);
						if(empty($g[1])){
							$isignored = true;
							$error_reason = "data is not matched by the en role";
						}
					}else  if($site2 == 'csde'){
						preg_match('@(\brabatt\b)|(\bgutschein\b)|(\bgutscheincode\b)|(\bsparen\b)|(\bspar\b)|(\bgratis\b)|(\bkostenlos\b)|(\bbis zu\b)|(\blieferung\b)|(\bversand\b)|(\breduziert\b)|(\breduzieren\b)|(\bschlussverkauf\b)|(\bwsv\b)|(\bssv\b)@mis', $link['LinkName']." ".$link['LinkDesc']." ".$link['HtmlCode'], $g);
						if(empty($g[1])){
							$isignored = true;
							$error_reason = "data is not matched by the de role";
						}
							
					}else if($site2 == 'csfr'){
						preg_match('@(\bréduction\b)|(\boffert\b)|(\bofferte\b)|(\blivraison\b)|(\bgratuit\b)|(\bgratuite\b)|(\bjusqu’à\b)|(\bà partir de\b)|(\bcadeaux\b)|(\bversand\b)|(\bsoldes\b)|(\bfrais de port\b)|(\bdès\b)|(\béconomisez\b)|(\béconomie\b)|(\bdépensant\b)|(\bmoins de\b)|(\bcadeau\b)@mis', $link['LinkName']." ".$link['LinkDesc']." ".$link['HtmlCode'], $g);
						if(empty($g[1])){
							$isignored = true;
							$error_reason = "data is not matched by the fr role";
						}
					}else{
						$isignored = true;
						$error_reason = "not need unknow site data";
					}
				}
				
				if($isignored){
					$sql = "UPDATE coupon_queue SET `Status` = 'IGNORED', DuplicatedReason = '{$error_reason}'  WHERE `ID` = '" . $link['ID'] . "'";
					$this->objMysql->query($sql);
					return;
				}
				
			}
			//add by devin for aff deal end
			
			if($link['Status'] == "UPDATE")
			{
				$couponInfo = $this->fetch_front_merchant_codes_forupdate($site,$mer_id,$link['Code']);
				if(empty($couponInfo)){
					continue;
				}else{
					if($couponInfo['Code'] == $link['Code'] && ($couponInfo['StartTime']==$link['StartDate'] || $link['StartDate'] < date('Y-m-d H:i:s'))){
						continue;
					}
//					$timezone = "America/Los_Angeles";
					$tomorrowDate = date('Y-m-d H:i:s', strtotime("+1 day"));
					if($link['EndDate'] < $tomorrowDate && $link['EndDate'] !="0000-00-00 00:00:00" && !empty($link['EndDate'])){
						continue;
					}

                    

				}
				//check for update first
				$sql = "select TaskId,Editor from task_coupon_pending  WHERE CouponQueueId = '" . addslashes($link['ID']) . "' AND `Site` = '" . addslashes($site) . "' AND `MerchantId` = '" . addslashes($mer_id) . "' order by taskid desc ";
				$row = $this->objMysql->getFirstRow($sql);
				if(is_array($row) && is_numeric($row["TaskId"]))
				{
					$task_id = $row["TaskId"];
					$old_editor = $row["Editor"];
					if(!$this->is_valid_cpq_editor($old_editor)){
					 	$old_editor = $in_cpq_editor_update;
//					 	$editCnt++;
					}
					
					if(isset($update_cpq_editor_lang[$lang])){
				 		$this->add_cpq_editor_cnt($o_cpq_editor_lang[$lang], 1);
				 		$this->all_editor_for_cpq_update[$update_cpq_editor_lang[$lang]]["cnt"] = $this->all_editor_for_cpq_update[$update_cpq_editor_lang[$lang]]["cnt"] + 1;
				 	}
					
					$sql = "UPDATE task_coupon_pending SET `Status` = 'UPDATE',`AffiliateID` = '$aff_id',`AffiliateMerchantID` = '" . addslashes($aff_mer_id) . "',`LinkID` = '" . addslashes($link['LinkID']) . "',`DataSource` = '" . addslashes($link['DataSource']) . "',`PromotionType` = '" . addslashes($link['PromotionType']) . "',`LastUpdateTime` = '".date('Y-m-d H:i:s')."', Editor = '" . addslashes($in_cpq_editor_update) . "' ,Operator=IF(`Status`='NEW',NULL,NULL), AddTime=NOW(),IsActiveProgram = '$is_active_program' WHERE TaskId = '$task_id'";
				 	
					$this->objMysql->query($sql);
					$stats["update"]++;
					$this->updateLogArr[$link['ID']][] = $task_id;
					$insert_ids[] = $task_id;
					$mqs->insert(2,$task_id);
					continue;
				}
			}

			$sql = "INSERT IGNORE INTO task_coupon_pending(`CouponQueueId`,`Site`,`MerchantId`,`AffiliateID`,`AffiliateMerchantID`,`LinkID`,`PromotionType`,`DataSource`,`Editor`,`Status`,`AddTime`,`LastUpdateTime`,SourceType, IsActiveProgram) VALUES  ('" . $link['ID'] . "','" . addslashes($site) . "','$mer_id','$aff_id','" . addslashes($aff_mer_id) . "','" . addslashes($link["LinkID"]) . "','" . addslashes($link["PromotionType"]) . "','" . addslashes($link["DataSource"]) . "','" . addslashes($in_cpq_editor) . "','NEW','".date('Y-m-d H:i:s')."','" . addslashes($link['SrcLastUpdate']) . "','" . addslashes($source_type) . "','$is_active_program')";
			if($this->debug) echo "sql: $sql\n";
			$this->objMysql->query($sql);
			$affected_rows = $this->objMysql->getAffectedRows();
			if($affected_rows)
			{
				$last_id = $this->objMysql->getLastInsertId();
				if(is_numeric($last_id)){
					$insert_ids[] = $last_id;
					//$repeat = new CouponRepeat();
					//$repeat->insert(array('CpqID'=>$last_id,'site'=>$site,'mid'=>$mer_id,'code'=>$link['Code']));
					//@file_get_contents("http://couponsn:IOPkjmN1@task.megainformationtech.com/editor/coupon_repeat.php?act=cpq&do=insert&site={$site}&id={$cpqId}&code=" . urlencode($link['Code']) . "&mid=" . $mer_id);
				}
			}
			$stats["insert"]++;
			$editCnt++;
			
			if(isset($cpq_editor_lang[$lang])){
		 		$this->add_cpq_editor_cnt($o_cpq_editor_lang[$lang], 1);
		 	}
		 	
		 	
		 	//one deal one site
		 	if($link['PromotionType'] == "DEAL"){
		 		break;
		 	}
		}
		
		if($o_cpq_editor) $this->add_cpq_editor_cnt($o_cpq_editor, $editCnt);
		$str_ids = implode(",",$insert_ids);
		$str_ids_append = $str_ids ? "," . $str_ids : "";
		$sql = "UPDATE coupon_queue SET `Status` = 'ASSIGNED', DuplicatedReason = if(DuplicatedReason = '', '$str_ids', concat(DuplicatedReason,'$str_ids_append')) WHERE `ID` = '" . $link['ID'] . "'";
		$this->objMysql->query($sql);
		
		$stats["total"] = $stats["update"] + $stats["insert"];
		
		$stats["blackmsg"] = $blackmsg;
		
		return $stats;
	}
	
	function merge_site_merchant_groups($site_merchants)
	{
		$r = array();
		if (!isset($this->site_merchant_groups))
		{
			$sql = "select `ID`,`GroupID`,UPPER(`Site`) as `Site`,`MerchantID`,`Coupon`,`Freeshipping` from `site_merchant_group` where `Status`='active' and (`Coupon`='YES' or `Freeshipping`='YES')";
			$this->site_merchant_groups = $this->objMysql->getRows($sql, "ID");
		}
		if (empty($this->site_merchant_groups) || !is_array($this->site_merchant_groups))
			return $r;
		// get the `GroupID` in the group
		// return empty records while no matched GroupID.
		$GroupIDs = array();
		foreach ($site_merchants as $site_merchant)
		{
			$merchantId = $site_merchant['MerchantId'];
			foreach ($this->site_merchant_groups as $key => $group)
			{
				if ($group['MerchantID'] == $merchantId && !in_array($group['GroupID'], $GroupIDs))
					$GroupIDs[] = $group['GroupID'];
			}
		}
		if (empty($GroupIDs))
			return $r;
		// if a record match the GroupID
		// and not in the result records
		// and site - merchant not match the input site_merchant array
		// add the record into the result records
		foreach ($this->site_merchant_groups as $key => $group)
		{
			if (!in_array($group['GroupID'], $GroupIDs))
				continue;
			if (key_exists($group['ID'], $r))
				continue;
			foreach ($site_merchants as $site_merchant)
			{
				if ($site_merchant['Site'] == $group['Site'] && $site_merchant['MerchantId'] == $group['MerchantID'])
					continue 2;
			}
			$r[$group['ID']] = $group;
		}
		return $r;
	}
	
	function create_tasks_from_cpq_by_aff_id($aff_id)
	{
		$arr_return = array(
			"ProcessedAffMerchantCount" => 0,
			"ProcessedCpqCount" => 0,
			"IgnoredCpqCount" => 0,
			"AddedCpqCount" => 0,
			"CreatedTaskCount" => 0,//CreatedTaskCount >= AddedCpqCount
		);

		$aff_mer_list = array();
		$aff_mer_list_inactive = array();
		//$sql = "select AffMerchantId, Site, MerchantId from `merchant_program` where AffId = '$aff_id' and `Status` = 'Active'";
		$sql = "select AffMerchantId, Site, MerchantId, Status from `merchant_program` where AffId = '$aff_id' and IsCreatTask = 'YES' ";
		$rows = $this->objMysql->getRows($sql);
		foreach($rows as $row)
		{
			$AffMerchantId = $row["AffMerchantId"];
			$Site = strtoupper($row["Site"]);
			$MerchantId = $row["MerchantId"];
			
			if(!isset($this->site_short_names[$Site])) continue;
			
			$aff_mer_list[$AffMerchantId]["$Site\t$MerchantId"] = array(
				"Site" => $Site,
				"MerchantId" => $MerchantId,
				"Status" => $row["Status"],
			);
		}
		
		$sql = "select AffMerchantId, Site, MerchantId, Status from `merchant_program_inactive` where AffId = '$aff_id' and IsCreatTask = 'YES' ";
		$rows = $this->objMysql->getRows($sql);
		foreach($rows as $row)
		{
			$AffMerchantId = $row["AffMerchantId"];
			$Site = strtoupper($row["Site"]);
			$MerchantId = $row["MerchantId"];
			
			if(!isset($this->site_short_names[$Site])) continue;
			
			$aff_mer_list_inactive[$AffMerchantId]["$Site\t$MerchantId"] = array(
				"Site" => $Site,
				"MerchantId" => $MerchantId,
				"Status" => $row["Status"],
			);
		}
		
		$sql = "SELECT AffiliateMerchantID FROM coupon_queue WHERE SourceType = 'AFFILIATE' and AffiliateID = '$aff_id' AND Status in ('NEW','UPDATE') group by AffiliateMerchantID";
		$cpq_aff_mer_list = $this->objMysql->getRows($sql,"AffiliateMerchantID");
		$arr_return["ProcessedAffMerchantCount"] = sizeof($cpq_aff_mer_list);
		
		foreach($cpq_aff_mer_list as $aff_mer_id => $null)
		{
			$sql = "SELECT ID, LinkID, SrcLastUpdate, DataSource,SourceType,PromotionType, LinkName, LinkDesc, `Code`, StartDate, EndDate,Status,AddTime,HtmlCode FROM coupon_queue WHERE SourceType = 'AFFILIATE' and AffiliateID = '$aff_id' AND `AffiliateMerchantID`= '" . addslashes($aff_mer_id) . "' AND Status in ('NEW','UPDATE') order by ID desc limit 1000";
			$link_from = $this->objMysql->getRows($sql,"ID");
			$arr_return["ProcessedCpqCount"] += sizeof($link_from);
			
			if(isset($aff_mer_list[$aff_mer_id]))
			{
				$site_merchant = $aff_mer_list[$aff_mer_id];
				if (!isset($aff_mer_list[$aff_mer_id]['groups']))
				{
					$site_merchant['groups'] = $this->merge_site_merchant_groups($site_merchant);
					$aff_mer_list[$aff_mer_id]['groups'] = $site_merchant['groups'];
				}
			}
			elseif(isset($aff_mer_list_inactive[$aff_mer_id]))
			{
				$site_merchant = $aff_mer_list_inactive[$aff_mer_id];
				if (!isset($aff_mer_list_inactive[$aff_mer_id]['groups']))
				{
					$site_merchant['groups'] = $this->merge_site_merchant_groups($site_merchant);
					$aff_mer_list_inactive[$aff_mer_id]['groups'] = $site_merchant['groups'];
				}
			}
			else
			{
				if(sizeof($link_from))
				{
					$sql = "update coupon_queue set Status = 'NOMATCHEDMERCHANT',DuplicatedReason = 'no matched site+merchant' where Status in ('NEW','UPDATE') and ID in (" . implode(",",array_keys($link_from)) . ")";
					$this->objMysql->query($sql);
					$arr_return["IgnoredCpqCount"] += sizeof($link_from);
				}
				continue;
			}
			$arr_ignored_links = $this->filter_aff_links_to_ignore($link_from,$site_merchant,$aff_id);
			foreach($arr_ignored_links as $id => $info)
			{
				$sql = "update coupon_queue set Status = '" . addslashes($info["Status"]) . "',DuplicatedReason = '" . addslashes($info["Reason"]) . "' where ID = '$id' AND Status in ('NEW','UPDATE')";
				$this->objMysql->query($sql);
				$arr_return["IgnoredCpqCount"] ++;
			}
			
			//get today data cnt by devin 20141009
			$nowDateTime = date("Y-m-d H:i:s");
			$timePrc0 = $this->timezoneConvert($nowDateTime);
			$timePrc = date('Y-m-d', strtotime($timePrc0));
			//if now time big than 18'clock,the time must be add one day
			if($timePrc0 >= $timePrc.' 18:00:00'){
				$timePrc = date('Y-m-d', strtotime($timePrc." +1 day"));
			}
			$time1Prc = date('Y-m-d', strtotime($timePrc." -1 day"));
			$time1 =  "$time1Prc 18:00:00";
			$time1 = $this->timezoneConvert($time1,"",true);
			
			$time2 =  "$timePrc 17:59:59";
			$time2 = $this->timezoneConvert($time2,"",true);

			$sql = " select Editor, count( DISTINCT CouponQueueId ) cnt from task_coupon_pending   where Status='UPDATE' and AddTime>= '".date("Y-m-d 00:00:00")."' and AddTime<= '".date("Y-m-d 23:59:59")."' GROUP BY Editor ";
			$this->updateDataCntArr = $this->objMysql->getRows($sql,'Editor');
			$errorMsg = "";
			foreach($link_from as $id => $link)
			{
				$arr_return["AddedCpqCount"] ++;
				$stats = $this->create_tasks_by_one_link($aff_id, $aff_mer_id, $site_merchant, "AFFILIATE", $link);
				$arr_return["CreatedTaskCount"] += $stats["total"];
				if(!empty($stats['blackmsg'])){
					$errorMsg .= $stats['blackmsg'];
				}
			}
			echo "updated task info \n";
			print_r($this->updateLogArr);
			echo "print error msg \n";
			echo $errorMsg."\n";
		}
		return $arr_return;
	}
	
	function timezoneConvert($time, $timeZoneTo = "PRC", $reverse = false){
		if($time == '0000-00-00 00:00:00') return $time;
		if(trim($time) == ""){
			return "";
		}
		if(trim($timeZoneTo) == ""){
			$timeZoneTo = "PRC";
		}
		$timezoneOld = date_default_timezone_get();
		$curTime = strtotime($time);
		date_default_timezone_set($timeZoneTo);
		if($reverse){
			$curTime = strtotime($time);
			date_default_timezone_set($timezoneOld);
		}
		$curDate = date("Y-m-d H:i:s", $curTime);
		date_default_timezone_set($timezoneOld);
		return $curDate; 
	}
	
	function filter_aff_links_to_ignore(&$links,$site_merchant,$aff_id)
	{
		$arr_ignored_links = array();
		
		//filter promo type
		//ADD BY DEVIN,NOW NEED DEAL DATA
		$arr_allowed_promo_type = array('COUPON','FREE SHIPPING','DEAL');
		foreach($links as $link_id => $link)
		{
			if(!in_array($link["PromotionType"],$arr_allowed_promo_type))
			{
				$arr_ignored_links[$link_id]["Reason"] = "not coupon";
				$arr_ignored_links[$link_id]["Status"] = "IGNORED";
				unset($links[$link_id]);
				if(empty($links)) return $arr_ignored_links;
			}
		}
		
		//filter out expired coupon
		foreach($links as $link_id => $link)
		{
			if($link["EndDate"] > "1970-00-00 00:00:00" && $link["EndDate"] < date("Y-m-d 00:00:00"))
			{
				$arr_ignored_links[$link_id]["Reason"] = "Expired";
				$arr_ignored_links[$link_id]["Status"] = "IGNORED";
				unset($links[$link_id]);
				if(empty($links)) return $arr_ignored_links;
			}
		}
		
		//filter out free shipping && title words > 18
		foreach($links as $link_id => $link)
		{
			if($link["PromotionType"] != "FREE SHIPPING") continue;
			$word_count = sizeof(explode(" ",$link["LinkName"]));
			if($word_count > 18)
			{
				$arr_ignored_links[$link_id]["Reason"] = "too long free shipping title";
				$arr_ignored_links[$link_id]["Status"] = "IGNORED";
				unset($links[$link_id]);
				if(empty($links)) return $arr_ignored_links;
			}
		}
		
		//filter no coupon in title or desc
		foreach($links as $link_id => $link)
		{
			if($link["PromotionType"] != "COUPON") continue;
			if($this->has_no_code_words($link["LinkName"]) || $this->has_no_code_words($link["LinkDesc"]))
			{
				$arr_ignored_links[$link_id]["Reason"] = "no code words found";
				$arr_ignored_links[$link_id]["Status"] = "IGNORED";
				unset($links[$link_id]);
				if(empty($links)) return $arr_ignored_links;
			}
		}
		
		//filter out same coupon code+title+startdate+enddate
		$arr_check_same = array();
		foreach($links as $link_id => $link)
		{
			$key = trim($link["LinkName"]) . "\t" . $link["StartDate"] . "\t" . $link["EndDate"] . "\t" . $link["Code"];
			$key = strtolower($key);
			if(isset($arr_check_same[$key]))
			{
				$arr_ignored_links[$link_id]["Reason"] = "same aff+code+title+startdate+enddate, Affiliate: ID(" . $arr_check_same[$key] . ")";
				$arr_ignored_links[$link_id]["Status"] = "DUPLICATED";
				unset($links[$link_id]);
				if(empty($links)) return $arr_ignored_links;
			}
			else
			{
				$arr_check_same[$key] = $link_id;
			}
		}
		unset($arr_check_same);
		
		//filter out same code against other aff
		$aff_mer_list = $this->get_aff_mer_by_site_mer($site_merchant);
		foreach($aff_mer_list as $k => $v)
		{
			$other_aff_id = $v["AffId"];
			$other_aff_merchant_id = $v["AffMerchantId"];
			
			//ignore itself
			//if($other_aff_id == $site_merchant["AffId"] && $other_aff_merchant_id == $site_merchant["AffMerchantId"]) continue;
			if($other_aff_id == $aff_id) continue;
			
			foreach($links as $link_id => $link)
			{
				if(empty($link["Code"])) continue;
				$res = $this->is_dup_against_cpq_aff_mer($other_aff_id, $other_aff_merchant_id, $link, true);
				if($res)
				{
					$arr_ignored_links[$link_id]["Reason"] = "same code+expdate, Affiliate: ID(" . $res["CPQID"] . ")";
					$arr_ignored_links[$link_id]["Status"] = "DUPLICATED";
					unset($links[$link_id]);
					if(empty($links)) return $arr_ignored_links;
				}
			}
		}
		
		//filter out same code against front
		foreach($links as $link_id => $link)
		{
			if(empty($link["Code"])) continue;
			$res = $this->is_dup_against_front($site_merchant,$link, true);
			if($res)
			{
				$arr_ignored_links[$link_id]["Reason"] = "Base: " . $res['Site'] . " ID(" . $res["CouponId"] . ")";
				$arr_ignored_links[$link_id]["Status"] = "DUPLICATED";
				unset($links[$link_id]);
				if(empty($links)) return $arr_ignored_links;
			}
		}
		
		return $arr_ignored_links;
	}
	
	function has_no_code_words($str)
	{
		$str = trim($str);
		foreach($this->no_code_patterns as $pattern)
		{
			if(preg_match($pattern,$str)) return true;
		}
		
		return false;
	}
	
	function is_dup_against_cpq_aff_mer($aff_id, $aff_mer_id, &$link, $check_expdate)
	{
		if($check_expdate) $ref_cpq_aff = &$this->cache_cpq_aff_merchant_codes_expdate;
		else $ref_cpq_aff = &$this->cache_cpq_aff_merchant_codes;

		if(!isset($ref_cpq_aff[$aff_id][$aff_mer_id]))
		{
			$ref_cpq_aff[$aff_id][$mer_id] = array();
			$this->fetch_cpq_aff_merchant_codes($ref_cpq_aff[$aff_id][$aff_mer_id],$aff_id,$aff_mer_id,$check_expdate);
		}
		
		$ref_cpq_aff_merchant_codes = &$ref_cpq_aff[$aff_id][$aff_mer_id];
		
		$key_to_check = trim(strtolower($link["Code"]));
		if($check_expdate) $key_to_check .= "\t" . substr($link["EndDate"],0,10);
	
		if(isset($ref_cpq_aff_merchant_codes[$key_to_check]))
		{
			return array(
				"CPQID" => $ref_cpq_aff_merchant_codes[$key_to_check],
			);
		}
		return false;
	}
	
	function fetch_cpq_aff_merchant_codes(&$ref_data,$aff_id,$aff_mer_id,$check_expdate)
	{
		$ref_data = array();
		$sql = "select `ID`, `LinkDesc`, `Code`, EndDate from `coupon_queue` where AffiliateID = '$aff_id' and AffiliateMerchantID = '" . addslashes($aff_mer_id) . "' and `SourceType` = 'AFFILIATE' and `PromotionType` = 'COUPON' and `Status` in ('NEW','UPDATE','ASSIGNED')";
		$rows = $this->objMysql->getRows($sql);
		
		foreach($rows as $row)
		{
			$code = $row['Code'];
			$code = trim($code);
			if(!$code && preg_match('/CODE:\s+<b>(.*?)<\/b>/i', $row['LinkDesc'], $matches))
			{
				if(!empty($matches[1])) $code = trim(strtolower($matches[1]));
			}
			
			if(empty($code)) continue;
			$code = strtolower($code);
			$key = $code;
			if($check_expdate) $key .= "\t" . substr($row["EndDate"],0,10);
			
			$ref_data[$key] = $row['ID'];
		}
	}
	
	function is_dup_against_front($site_merchant, $link, $check_expdate)
	{
		if($check_expdate) $ref_front = &$this->cache_front_merchant_codes_expdate;
		else $ref_front = &$this->cache_front_merchant_codes;

		foreach($site_merchant as $v)
		{
			$site = $v["Site"];
			$mer_id = $v["MerchantId"];
			
			if(!isset($this->site_short_names[$site])) continue;
			
			if(!isset($ref_front[$site][$mer_id]))
			{
				$ref_front[$site][$mer_id] = array();
				$this->fetch_front_merchant_codes($ref_front[$site][$mer_id],$site,$mer_id,$check_expdate);
			}
			
			$ref_front_merchant_codes = &$ref_front[$site][$mer_id];
			
			$key_to_check = trim(strtolower($link["Code"]));
			if($check_expdate) $key_to_check .= "\t" . substr($link["EndDate"],0,10);

			if(isset($ref_front_merchant_codes[$key_to_check]))
			{
				return array(
					"Site" => $site,
					"CouponId" => $ref_front_merchant_codes[$key_to_check],
				);
			}
		}
		return false;
	}
	
	function get_front_mysql_obj($site)
	{
		$site = strtoupper($site);
		if(isset($this->front_mysql_objs[$site])) return $this->front_mysql_objs[$site];
		if(!isset($this->site_short_names[$site])) return false;
		
		global $databaseInfo;
		if(!isset($databaseInfo["INFO_" . $site . "_DB_NAME"])) die("die: new mysql obj for site $site failed.\n");
		$this->site_short_names[$site] = new Mysql($databaseInfo["INFO_" . $site . "_DB_NAME"], $databaseInfo["INFO_" . $site . "_DB_HOST"], $databaseInfo["INFO_" . $site . "_DB_USER"], $databaseInfo["INFO_" . $site . "_DB_PASS"]);
		return $this->site_short_names[$site];
	}
	
	function fetch_front_merchant_codes(&$ref_data,$site,$mer_id,$check_expdate)
	{
		$oMysql = $this->get_front_mysql_obj($site);
		$sql = "select `ID`, Code as LowerCode,ExpireTime from `normalcoupon` where MerchantID = '$mer_id' and `IsActive`='YES' and (`ExpireTime`='0000-00-00 00:00:00' or `ExpireTime` > now()) and Code <> ''";
		$rows = $oMysql->getRows($sql);
		foreach($rows as $row)
		{
			$key = trim(strtolower($row["Code"]));
			if($check_expdate) $key .= "\t" . substr($row["ExpireTime"],0,10);

			$ref_data[$key] = $row["ID"];
		}
	}
	function fetch_front_merchant_codes_forupdate($site,$mer_id,$code)
	{
		$oMysql = $this->get_front_mysql_obj($site);
		$mer_id = addslashes($mer_id);
		$code = addslashes($code);
		
		$sql = "SELECT * from normalcoupon where MerchantID='".$mer_id."' and code='" . $code. "' and `IsActive`='YES' ORDER BY ExpireTime desc " ;
		$rows = $oMysql->getFirstRow($sql);
		return $rows;
	}
	
	function get_aff_mer_by_site_mer($sitemer=array())
	{
		$data = array();
		if (empty($sitemer) || !is_array($sitemer)) return $data;
	
		$arr_where = array();
		foreach ($sitemer as $val)
		{
			$site = $mer_id = "";
			if(isset($val['SiteName'])) $site = $val['SiteName'];
			if(isset($val['Site'])) $site = $val['Site'];
			if(isset($val['MerchantID'])) $mer_id = $val['MerchantID'];
			if(isset($val['MerchantId'])) $mer_id = $val['MerchantId'];
			if(empty($site) || empty($mer_id)) continue;
			$arr_where[] = " (`Site` = '" . $site . "' and `MerchantId` = '$mer_id')";
		}
		
		$sql = "select `AffId`, `AffMerchantId` from `merchant_program` where " . implode(" or ",$arr_where);;
		return $this->objMysql->getRows($sql,"AffId,AffMerchantId");
	}
	
	function get_all_cpq_editor()
	{
		$sql = "SELECT Editor, Type, Proportion FROM task_coupon_pending_editor";
		$rows = $this->objMysql->getRows($sql, 'Editor');
		foreach($rows as $key => $val){
			$rows[$key]["cnt"] = 0;
		}
		$this->all_editor_for_cpq = $rows;
	}
	
	function is_valid_cpq_editor($editor)
	{
		if(!isset($this->all_editor_for_cpq)) $this->get_all_cpq_editor();
		return isset($this->all_editor_for_cpq[$editor]);
	}
	
	function get_one_cpq_editor_by_rand()
	{
		if(!isset($this->all_editor_for_cpq)) $this->get_all_cpq_editor();
		if(empty($this->all_editor_for_cpq)) return "";
		return array_rand($this->all_editor_for_cpq);
	}
	
	function get_one_cpq_editor_by_min($site_merchant, $link)
	{
		if(count($this->all_editor_for_cpq) < 1) $this->get_all_cpq_editor();
		$type = 'csen';
		if($this->isLinkCSFR($site_merchant, $link)) $type = 'csfr';
		elseif($this->isLinkCSDE($site_merchant, $link)) $type = 'csde';

		$site_editors = $this->all_editor_for_cpq;
		foreach ($site_editors as $editor => $v)
		{
			if (strtolower($v['Type']) != $type)
				unset($site_editors[$editor]);
		}
		if (empty($site_editors))
			$site_editors = $this->all_editor_for_cpq;
		$minEditor = "";
		$minCnt = 9999999;
		$this->shuffle_assoc($site_editors);
		foreach($site_editors as $editor => $val){
			if($minCnt > $val["cnt"]){
				$minCnt = $val["cnt"];
				$minEditor = $editor;
			}
		}
		return $minEditor;
	}
	
	
	function get_one_cpq_editor_by_proportion($site_merchant, $link)
	{		
		$all_count = 0;
		if(count($this->all_editor_for_cpq) < 1) $this->get_all_cpq_editor();
		$type = 'csen';
		if($this->isLinkCSFR($site_merchant, $link)) $type = 'csfr';
		elseif($this->isLinkCSDE($site_merchant, $link)) $type = 'csde';

		$site_editors = $this->all_editor_for_cpq;		
		foreach ($site_editors as $editor => $v)
		{
			if (strtolower($v['Type']) != $type){
				unset($site_editors[$editor]);
			}else{
				$all_count += $v['cnt'];
			}
		}
		if (empty($site_editors))
			$site_editors = $this->all_editor_for_cpq;
		
		$minEditor = "";
		$minCnt = 9999999;
		$this->shuffle_assoc($site_editors);
		foreach($site_editors as $editor => $val){
			if($val["Proportion"] <= 0 ) continue;
			if($val["cnt"] == 0 || $all_count == 0){
				$minEditor = $editor;
				break;
			}
			$tmp_num = $val["cnt"] / $all_count / $val["Proportion"];
			if($minCnt > $tmp_num){
				$minCnt = $tmp_num;
				$minEditor = $editor;
			}
		}
		return $minEditor;
	}
	
	function get_one_cpq_editor_eachlanguage_by_proportion($site_merchant, $link)
	{
		$return_editor = array();				
		$type_arr = array();		
		if($this->isLinkCSEN($site_merchant, $link)) $type_arr[] = 'csen';
		if($this->isLinkCSFR($site_merchant, $link)) $type_arr[] = 'csfr';
		if($this->isLinkCSDE($site_merchant, $link)) $type_arr[] = 'csde';

		if(count($this->all_editor_for_cpq) < 1) $this->get_all_cpq_editor();
		foreach($type_arr as $type){
			$all_count = 0;
			$site_editors = $this->all_editor_for_cpq;
			foreach ($site_editors as $editor => $v)
			{
				if (strtolower($v['Type']) != $type){
					unset($site_editors[$editor]);
				}else{
					$all_count += $v['cnt'];
				}
			}
			if (empty($site_editors))
				$site_editors = $this->all_editor_for_cpq;
			
			$minEditor = "";
			$minCnt = 9999999;
			$this->shuffle_assoc($site_editors);
			foreach($site_editors as $editor => $val){
				if($val["Proportion"] <= 0 ) continue;
				if($val["cnt"] == 0 || $all_count == 0){
					$minEditor = $editor;
					break;
				}
				$tmp_num = $val["cnt"] / $all_count / $val["Proportion"];
				if($minCnt > $tmp_num){
					$minCnt = $tmp_num;
					$minEditor = $editor;
				}
			}
			$return_editor[$type] = $minEditor;
		}
		return $return_editor;
	}
	
	function getEditorLinkAndProportionUpdate($site_merchant, $link)
	{
		$return_editor = array();				
		$type_arr = array();		
		if($this->isLinkCSEN($site_merchant, $link)) $type_arr[] = 'csen';
		if($this->isLinkCSFR($site_merchant, $link)) $type_arr[] = 'csfr';
		if($this->isLinkCSDE($site_merchant, $link)) $type_arr[] = 'csde';

		if(count($this->all_editor_for_cpq) < 1) $this->get_all_cpq_editor();
		
		if(count($this->all_editor_for_cpq_update)<1){
			$this->all_editor_for_cpq_update = $this->all_editor_for_cpq;
			foreach ($this->all_editor_for_cpq_update as $editor2 => $v2 ){
				$this->all_editor_for_cpq_update[$editor2]['cnt'] = 0;
			}
		}
		
		foreach($type_arr as $type){
			$all_count = 0;
			$site_editors = $this->all_editor_for_cpq_update;
			foreach ($site_editors as $editor => $v)
			{
				if(!empty($this->updateDataCntArr[$editor])){
					$all_count += intval($this->updateDataCntArr[$editor]['cnt']);
				}
			
				if (strtolower($v['Type']) != $type){
					unset($site_editors[$editor]);
				}else{
					$all_count += $v['cnt'];
				}
			}
			if (empty($site_editors))
				$site_editors = $this->all_editor_for_cpq_update;
			
			$minEditor = "";
			$minCnt = 9999999;
			$this->shuffle_assoc($site_editors);
			foreach($site_editors as $editor => $val){
				if($val["Proportion"] <= 0 ) continue;
				if($val["cnt"] == 0 || $all_count == 0){
					$minEditor = $editor;
					break;
				}
				$tmp_num = $val["cnt"] / $all_count / $val["Proportion"];
				if($minCnt > $tmp_num){
					$minCnt = $tmp_num;
					$minEditor = $editor;
				}
			}
			$return_editor[$type] = $minEditor;
		}
		return $return_editor;
	}
	
	function getEditorTakeOver($editor,$cpq=false)
	{
		$sql = "SELECT toeditorname, fromeditornames FROM editor_mapping WHERE startdate < '".date("Y-m-d H:i:s")."' and enddate > '".date("Y-m-d H:i:s")."'";
		$tmp_arr = array();
		$tmp_arr = $this->objMysql->getRows($sql);
		
		$return_editor = $editor;
		
		foreach($tmp_arr as $v){
			if(strpos($v["fromeditornames"], $editor) !== false){
				$toeditor_arr = explode(",", $v["toeditorname"]);				
				$tmp_editor = $toeditor_arr[array_rand($toeditor_arr)];
				if(!empty($tmp_editor) && $tmp_editor != $return_editor){
					$return_editor = $tmp_editor;
					break;
				}				
			}
		}
		if($cpq==true){
			if(!$this->is_valid_cpq_editor($return_editor)){
				return $editor;
			}
		}
		return $return_editor;
	}
	
	function checkSourceTakeOver($sourceid)
	{
		$sql = "select editorlist from task_coupon_pending_takeover where sourceid = $sourceid and status = 'active' and starttime < '".date("Y-m-d H:i:s")."' and (endtime > '".date("Y-m-d H:i:s")."' or endtime='0000-00-00 00:00:00' ) LIMIT 1";
		$tmp_arr = array();
		$tmp_arr = $this->objMysql->getFirstRow($sql);
		
		if(count($tmp_arr)){
			$tmp_arr = explode(",", $tmp_arr["editorlist"]);
			return $tmp_arr[array_rand($tmp_arr)];
		}else{
			return false;
		}
	}
	function checkMerchantTakeOver($merchantid)
	{
		$sql = "select editorlist from task_coupon_pending_takeover where merchantid = $merchantid and status = 'active' and starttime < '".date("Y-m-d H:i:s")."' and (endtime > '".date("Y-m-d H:i:s")."' or endtime='0000-00-00 00:00:00' ) LIMIT 1";
		$tmp_arr = array();
		$tmp_arr = $this->objMysql->getFirstRow($sql);
		
		if(count($tmp_arr)){
			$tmp_arr = explode(",", $tmp_arr["editorlist"]);
			return $tmp_arr[array_rand($tmp_arr)];
		}else{
			return false;
		}
	}
	
	function getTakeOver(){
		$sql = "select editorlist from task_coupon_pending_takeover where and status = 'active' and starttime < '".date("Y-m-d H:i:s")."' and (endtime > '".date("Y-m-d H:i:s")."' or endtime='0000-00-00 00:00:00' )";
		$tmp_arr = $this->objMysql->getRows($sql);
		$mer_editor = array();
		foreach($tmp_arr as $v){
			
		}
	}
	
	function add_cpq_editor_cnt($editor, $cnt = 1){
		$this->all_editor_for_cpq[$editor]["cnt"] = $this->all_editor_for_cpq[$editor]["cnt"] + $cnt;
		return $this->all_editor_for_cpq[$editor];
	}
	function shuffle_assoc(&$list) { 
		if (!is_array($list)) return $list; 
		$keys = array_keys($list); 
		shuffle($keys); 
		$random = array(); 
		foreach ($keys as $key) {
			$random[$key] = $list[$key];
		} 
		$list = $random; 
	}
	
	function isLinkCSDE($site_merchant, $link)
	{
		foreach ($site_merchant as $v)
		{
			if (strtolower($v['Site']) == 'csde')
				return true;
		}
		$de_keywords = array(
				'Gutschein', 'Gratis Versand', 'Kostenlose Lieferung ab \€', 'Versandkostenfrei', 'Rabatt', '\€ Rabatt auf',
				'geschenkt', 'Sparen', 'Nachlass', 'Reduzieren', 'Reduziert', 'ab einem Bestellwert von', 'auf Ihre Bestellung',
				'Gültig ab dem:', 'für Neu- \& Bestandskunden', 'gratis', 'Angebote', 'Kauf', );
		$preg = sprintf('@(\s|^)(%s)(\s|$)@i', implode('|', $de_keywords));
		if (preg_match($preg, $link['LinkName']))
			return true;
		return false;
	}

	function isLinkCSFR($site_merchant, $link)
	{
		foreach ($site_merchant as $v)
		{
			if (strtolower($v['Site']) == 'csfr')
				return true;
		}
		return false;
	}
	
	function isLinkCSEN($site_merchant, $link)
	{
		foreach ($site_merchant as $v)
		{
			if (!in_array(strtolower($v['Site']), array('csfr','csde')))
				return true;
		}
		return false;
	}
	
	function setCPQEditorProportion($editor, $proportion)
	{
		$sql = "UPDATE task_coupon_pending_editor SET Proportion = '".floatval($proportion)."' WHERE Editor = '".addslashes($editor)."'";
		$this->objMysql->query($sql);
	}
	function checkCPQByBlockTool($CouponQueueId,$mer_id,$site){
		$sql = "SELECT * FROM coupon_queue WHERE ID = ".$CouponQueueId;
		$row = $this->objMysql->getFirstRow($sql);
		
		$checkField = array('Code','LinkName','LinkDesc','HtmlCode');
		//Affiliate
		$sql = "select * from coupon_queue_block where Status = 'Active' AND SourceType = 'AFFILIATE' AND AffiliateID = '{$row['AffiliateID']}' AND AffiliateMerchantID = '".addslashes($row['AffiliateMerchantID'])."' AND AffiliateMerchantName = '".addslashes($row['AffiliateMerchantName'])."'";
		$blackRows = $this->objMysql->getRows($sql, "ID"); 
		foreach($blackRows as $blackrow){
			$keyArr = explode("::",$blackrow['Keyword']);
			if(trim($keyArr[1])!=''){
				$cpqKeyCon = '';
				if($keyArr[0] != 'All'){
					$cpqKeyCon = $row[$keyArr[0]];
					if(stripos($cpqKeyCon, trim($keyArr[1])) !== false){
						$blackmsg = "\n Cpq Block Tool ID:{$blackrow["ID"]} CouponQueueId:" . $CouponQueueId . " MerchantId:" . $mer_id . ",Site:{$site}, has black keyword: ". $blackrow["Keyword"]."\n";
						return $blackmsg;
					}
				}else{
					foreach($checkField as $field){
						if(stripos($row[$field], trim($keyArr[1])) !== false){
							$blackmsg = "\n Cpq Block Tool ID:{$blackrow["ID"]} CouponQueueId:" . $CouponQueueId . " MerchantId:" . $mer_id . ",Site:{$site}, has black keyword: ". $blackrow["Keyword"]."\n";
							return $blackmsg;
						}
					}
				}
			}else{
				$blackmsg = "\n Cpq Block Tool ID:{$blackrow["ID"]} CouponQueueId:" . $CouponQueueId . " MerchantId:" . $mer_id . ",Site:{$site}, has black keyword: ". $blackrow["Keyword"]."\n";
				return $blackmsg;
			}
		}
		//Merchant
		$bsite = strtoupper($site);
		$sql = "select * from coupon_queue_block where Status = 'Active' AND Site = '$bsite' AND MerchantID = '$mer_id'";
		$blackRows = $this->objMysql->getRows($sql, "ID");
		foreach($blackRows as $blackrow){
			if($blackrow['SourceType'] == 'CS_SITE') $blackrow['SourceType'] = 'COMPETITOR';
			if(($blackrow['SpecificSource'] == $row['DataSource']) || (($blackrow['SpecificSource'] == '0' || $blackrow['SpecificSource'] == '') && $row['SourceType'] == $blackrow['SourceType'])){
				$keyArr = explode("::",$blackrow['Keyword']);
				$cpqKeyCon = '';
				if(trim($keyArr[1])!=''){
					if($keyArr[0] != 'All'){
						$cpqKeyCon = $row[$keyArr[0]];
						if(stripos($cpqKeyCon, trim($keyArr[1])) !== false){
							return "\n Cpq Block Tool ID:{$blackrow["ID"]} CouponQueueId:" . $CouponQueueId . " MerchantId:" . $mer_id . ",Site:{$site}, has black keyword: ". $blackrow["Keyword"]."\n";
						}
					}else{
						foreach($checkField as $field){
							if(stripos($row[$field], trim($keyArr[1])) !== false){
								return "\n Cpq Block Tool ID:{$blackrow["ID"]} CouponQueueId:" . $CouponQueueId . " MerchantId:" . $mer_id . ",Site:{$site}, has black keyword: ". $blackrow["Keyword"]."\n";
							}
						}
					}
				}else{
					return "\n Cpq Block Tool ID:{$blackrow["ID"]} CouponQueueId:" . $CouponQueueId . " MerchantId:" . $mer_id . ",Site:{$site}, has black keyword: ". $blackrow["Keyword"]."\n";
				}
			}
		}
		return '';
	}
}
