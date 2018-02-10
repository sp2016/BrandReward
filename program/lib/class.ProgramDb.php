<?php
class ProgramDb
{
	function __construct()
	{
		if(!isset($this->objMysql)) $this->objMysql = new MysqlExt();
		if(!isset($this->objPendingMysql)) $this->objPendingMysql = new MysqlExt(PENDING_DB_NAME, PENDING_DB_HOST, PENDING_DB_USER, PENDING_DB_PASS);
		//if(!isset($this->objTaskMysql)) $this->objTaskMysql = new MysqlExt(TASK_DB_NAME, TASK_DB_HOST, TASK_DB_USER, TASK_DB_PASS);		
	}
	
	
	########			BASE task				###########
	function getAffRank(){
		$data = array();
		$sql = "SELECT ID, ImportanceRank as Rank FROM wf_aff WHERE isactive = 'yes'";
		$data = $this->objMysql->getRows($sql, "ID");
		return $data;
	}
	
	function getAllAffiliate($id_arr = array()){
		$data = array();
		$id_list = "";
		if(count($id_arr)){
			foreach($id_arr as &$v) $v = intval($v);  
			$id_list = " AND ID IN ('" . implode("','", $id_arr) . "')";
		}
		$sql = "SELECT ID, Name, ShortName, Domain, AffiliateUrlKeywords, AffiliateUrlKeywords2, DeepUrlParaName, SupportDeepUrl, SubTracking, SubTracking2, ProgramCrawled, IsInHouse, ImportanceRank FROM wf_aff WHERE isactive = 'yes' $id_list";
		$data = $this->objMysql->getRows($sql, "ID");
		return $data;
	}	
	
	/*function getChangeProgramByAff($affid, $limit = 0){
		$data = array();
		if((int)$limit < 1000 && (int)$limit > 0){
			$limit_str = "LIMIT $limit";
		}else{
			$limit_str = "LIMIT 1000";
		}
		$sql = "SELECT a.ID, a.ProgramId, a.FieldName, a.FieldValueOld, a.FieldValueNew, a.Status, b.Name, b.AffId, b.IdInAff, b.AffDefaultUrl, b.Partnership, b.StatusInAff, b.CommissionExt, b.Homepage, b.SupportDeepUrl FROM `program_change_log_temp_for_bdg` a INNER JOIN program b ON a.ProgramId = b.ID WHERE b.AffId = ". intval($affid). " AND a.status = 'new' $limit_str";
		$data = $this->objTaskMysql->getRows($sql, "ID");
		return $data;
	}	*/
	
	function getProgramInfoById($id_str){
		$data = array();
		if($id_str){
			$sql = "SELECT ID, Name, Description, AffId, IdInAff, AffDefaultUrl, Partnership, StatusInAff, CommissionExt, Homepage, SupportDeepUrl, TargetCountryExt, TargetCountryInt, TargetCountryIntOld, SecondIdInAff FROM program WHERE ID in ($id_str)";
			$data = $this->objMysql->getRows($sql, "ID");
		}
		return $data;
	}
	
	/*function getOrginProgramDefaultUrl($pid){
		$data = array();
		if($pid){
			$sql = "SELECT a.`AffiliateDefaultUrl`, a.`DeepUrlTemplate`, a.StoreId, a.Order, b.Domain FROM program_store_relationship a INNER JOIN store b ON a.storeid = b.id WHERE a.programid = '".intval($pid)."' AND a.status = 'active' and a.isfake = 'NO'";
			$data = $this->objTaskMysql->getRows($sql);
		}
		return $data;
	}*/
	
	/*function getProgramStoreDefaultUrl(){
		$data = array();
		$sql = "SELECT a.`AffiliateDefaultUrl`, a.`DeepUrlTemplate`, a.ProgramId, a.Order FROM program_store_relationship a WHERE a.status = 'active' and a.isfake = 'NO' order by a.`order`";
		$data = $this->objTaskMysql->getRows($sql);
		
		return $data;
	}*/
	
	########			BASE pendinglinks				###########	
	function getLinksFromAffiliate($affid, $idinaff){
		$data = array();
		if (empty($affid) || !is_numeric($affid) || !$this->checkAffLinkDB($affid)) 
			return $data;
		//$sql = "SELECT LinkHtmlCode, LinkName, LinkEndDate, LinkAffUrl, LinkDesc, LinkImageUrl, LinkOriginalUrl FROM `affiliate_links_{$affid}` WHERE AffMerchantId = '".addslashes($idinaff)."' AND LastUpdateTime > '".date("Y-m-d", strtotime("-7 days"))."' AND LinkHtmlCode <> '' AND (LinkEndDate = 0 || LinkEndDate > '".date("Y-m-d", strtotime("30 days"))."') ORDER BY AffLinkId LIMIT 100";
		$sql_isactive = '';
		if(SID == 'bdg02'){
			$sql_isactive = " AND isactive = 'yes'";
		}
		if($affid == 539){
			$sql = "SELECT AffMerchantId, LinkHtmlCode, LinkName, LinkEndDate, LinkAffUrl, LinkDesc, LinkImageUrl, LinkOriginalUrl, Domain, FinalUrl, HttpCode, LinkPromoType FROM `affiliate_links_{$affid}` 
				WHERE AffMerchantId = '".addslashes($idinaff)."' 
				AND (LinkEndDate = 0 || LinkEndDate > '".date("Y-m-d", strtotime("30 days"))."') AND (LinkStartDate = 0 || LinkStartDate < '".date("Y-m-d H:i:s")."')
				AND LinkAddTime > '".date("Y-m-d", strtotime(" -200 days"))."' AND LastUpdateTime > '".date("Y-m-d", strtotime("-100 days"))."'
				$sql_isactive
				ORDER BY AffLinkId DESC LIMIT 100";
		}elseif($affid == 12 && SID == 'bdg02'){
			$sql = "SELECT AffMerchantId, ProductUrl AS LinkAffUrl, ProductDestUrl AS LinkOriginalUrl, ProductName AS LinkName , ProductDesc AS LinkDesc FROM affiliate_product
					WHERE AffMerchantId = '".addslashes($idinaff)."' AND AffId = '$affid' $sql_isactive";
			
		}elseif($affid != 46){
			$sql = "SELECT AffMerchantId, LinkHtmlCode, LinkName, LinkEndDate, LinkAffUrl, LinkDesc, LinkImageUrl, LinkOriginalUrl, Domain, FinalUrl, HttpCode, LinkPromoType FROM `affiliate_links_{$affid}` 
				WHERE AffMerchantId = '".addslashes($idinaff)."' 
				AND LinkHtmlCode <> '' AND (LinkEndDate = 0 || LinkEndDate > '".date("Y-m-d", strtotime("30 days"))."') AND (LinkStartDate = 0 || LinkStartDate < '".date("Y-m-d H:i:s")."')
				AND LinkAddTime > '".date("Y-m-d", strtotime(" -200 days"))."' AND LastUpdateTime > '".date("Y-m-d", strtotime("-100 days"))."'
				$sql_isactive
				ORDER BY AffLinkId DESC LIMIT 100";
		}else{
			 $sql = "SELECT AffMerchantId, LinkHtmlCode, LinkName, LinkEndDate, LinkAffUrl, LinkDesc, LinkImageUrl, LinkOriginalUrl, Domain, FinalUrl, HttpCode, LinkPromoType FROM `affiliate_links_{$affid}` 
				WHERE AffMerchantId = '".addslashes($idinaff)."' 
				AND LinkHtmlCode <> '' AND (LinkEndDate = 0 || LinkEndDate > '".date("Y-m-d", strtotime("30 days"))."') AND (LinkStartDate = 0 || LinkStartDate < '".date("Y-m-d H:i:s")."')
				AND LinkAddTime > '".date("Y-m-d", strtotime(" -200 days"))."' AND LastUpdateTime > '".date("Y-m-d", strtotime("-3 days"))."'
				$sql_isactive
				ORDER BY AffLinkId DESC LIMIT 100";

		}
		$data = $this->objPendingMysql->getRows($sql);
		return $data;
	}
	
	function getLinksFromAffiliateBdg($affid){
		$data = array();
		if (empty($affid) || !is_numeric($affid) || !$this->checkAffLinkDB($affid)) 
			return $data;
		
		if($affid == 12 && SID == 'bdg02'){
			$sql = "SELECT AffMerchantId, ProductUrl AS LinkAffUrl, ProductDestUrl AS LinkOriginalUrl, ProductName AS LinkName , ProductDesc AS LinkDesc FROM affiliate_product
					WHERE AffId = '$affid' AND IsActive = 'yes'";
			
		}if($affid != 97){
			$sql = "SELECT AffMerchantId, LinkHtmlCode, LinkName, LinkEndDate, LinkAffUrl, LinkDesc, LinkImageUrl, LinkOriginalUrl, Domain, FinalUrl, HttpCode, LinkPromoType FROM `affiliate_links_{$affid}` 
					WHERE LinkHtmlCode <> '' AND (LinkEndDate = 0 || LinkEndDate > '".date("Y-m-d", strtotime("30 days"))."') AND (LinkStartDate = 0 || LinkStartDate < '".date("Y-m-d H:i:s")."')
					AND LinkAddTime > '".date("Y-m-d", strtotime(" -200 days"))."'";
		}else{
			$sql = "SELECT AffMerchantId, LinkHtmlCode, LinkName, LinkEndDate, LinkAffUrl, LinkDesc, LinkImageUrl, LinkOriginalUrl, Domain, FinalUrl, HttpCode, LinkPromoType FROM `affiliate_links_{$affid}` 
					WHERE LinkHtmlCode <> '' AND (LinkEndDate = 0 || LinkEndDate > '".date("Y-m-d", strtotime("30 days"))."') AND (LinkStartDate = 0 || LinkStartDate < '".date("Y-m-d H:i:s")."')
					AND LinkAddTime > '".date("Y-m-d", strtotime(" -200 days"))."'";
		}
		if($affid == 46){
			$sql .= " AND LastUpdateTime > '".date("Y-m-d", strtotime("-3 days"))."'";
		}
		if(SID == 'bdg02'){
			$sql .= " AND isactive = 'yes'";
		}
		$data = $this->objPendingMysql->getRows($sql);
		return $data;
	}
	
	function checkAffLinkDB($affid){
		$hasDB = false;
		if(intval($affid)){
			$hasDB = $this->objPendingMysql->isTableExisting("affiliate_links_$affid");			
		}
		return $hasDB;
	}
	
	
	########			BASE BDG				###########
	/*function getLinksFromAffiliateBdg($affid){
		$data = array();
		if (empty($affid) || !is_numeric($affid) || !$this->checkAffLinkDB($affid)) 
			return $data;
		$sql = "SELECT AffMerchantId, LinkHtmlCode, LinkName, LinkEndDate, LinkAffUrl, LinkDesc, LinkImageUrl, LinkOriginalUrl FROM `affiliate_links` WHERE AffId = '".intval($affid)."' AND LinkHtmlCode <> '' AND (LinkEndDate = 0 || LinkEndDate > '".date("Y-m-d", strtotime("30 days"))."')";
		$data = $this->objMysql->getRows($sql);
		return $data;
	}	*/
	
	function getNewChangedProgramInfoByAff($affid, $limit = 0){
		if((int)$limit > 1000 && (int)$limit <= 0){
			$limit = 100;
		}
		$sql = "SELECT ID as logid, ProgramId AS pid FROM program_change_log WHERE `status` = 'new' AND affid = $affid LIMIT $limit";
		$data = $this->objMysql->getRows($sql);
		return $data;
	}
	
	function setChangeProgramProcessed($id_str){
		if(strlen($id_str)){
			$sql = "UPDATE `program_change_log` SET Status = 'PROCESSED', LastUpdateTime = '".date("Y-m-d H:i:s")."' WHERE programid in ($id_str) AND status = 'new'";
			$this->objMysql->query($sql);
		}		
	}	
	
	function getAffUrlPattern(){
		$data = array();
		$sql = "SELECT AffId, TplAffDefaultUrl, TplDeepUrlTpl, SupportDeepUrlTpl, NeedAffDefaultUrl FROM `aff_url_pattern` ";
		$data = $this->objMysql->getRows($sql, "AffId");
		return $data;
	}	
	
	function getPrgmIntellByAffId($affid){
		$data = array();
		if (empty($affid) || !is_numeric($affid)) 
			return $data;
		//$sql = "SELECT a.ProgramId, a.IdInAff, a.AffDefaultUrl, a.DeepUrlTpl, b.AffDefaultUrl as p_affurl FROM `program_intell` a inner join program b on a.ProgramId = b.id WHERE a.AffId = '$affid'";
		$sql = "SELECT ProgramId, IdInAff, AffDefaultUrl, DeepUrlTpl, OutGoingUrl, AffId FROM `program_intell` WHERE AffId = '$affid'";
		$data = $this->objMysql->getRows($sql, "ProgramId");
		return $data;
	}
	
	function getPrgmIntellById($pid){
		$data = array();
		if (empty($pid) || !is_numeric($pid)) 
			return $data;
		$sql = "SELECT ProgramId, IdInAff, AffDefaultUrl, DeepUrlTpl, OutGoingUrl FROM `program_intell` WHERE ProgramId = '$pid'";
		$data = $this->objMysql->getFirstRow($sql);
		return $data;
	}
	
	function updatePrgmDefaultUrl($update_sql = array()){
		foreach($update_sql as $pid => $v){
			$sql = "update program_intell set AffDefaultUrl = '".addslashes($v["AffDefaultUrl"])."', DeepUrlTpl = '".addslashes($v["DeepUrlTpl"])."' where ProgramId = '{$pid}'";
			$this->objMysql->query($sql);
		}
	}	
	
	function insertProgramIntell($insert_sql){
		global $date;
		$default_value = array(
			'IsActive'=>'Inactive',
			'CommissionType'=>'Unknown',
			'CommissionUsed' => 0.00,
			'CommissionIncentive' => 0,
			'SupportDeepUrl' => 'Unknown',
			'SupportDeepUrlOut' => 'Unknown',
			'SupportFake' => 'Unknown',
			'Order' => 0,
			'SupportType' => 'All',
			'LastUpdateTime' => $date
		);
		$column_keys = array("ProgramId", "AffId", "IdInAff", "AffDefaultUrl", "DeepUrlTpl", "IsActive", "Domain", "CommissionValue", "CommissionType", "CommissionUsed", "CommissionIncentive", "SupportDeepUrl", "LastUpdateTime", "OutGoingUrl", "DeniedPubCode", "CommissionCurrency", "CountryCode", "ShippingCountry", "CategoryId", "Order", "LastChangeTime","SupportType");
		$pidArr = i_array_column($insert_sql,'ProgramId');
		$pids = trim(implode(',',$pidArr),',');
		$sql = "select `ProgramId`,`AffDefaultUrl`,`DeepUrlTpl`,`IsActive`,`CommissionValue`,`CommissionType`,`CommissionUsed`,`CommissionIncentive`,`CommissionCurrency`,`Domain`,`SupportDeepUrl`,`DeniedPubCode`,`OutGoingUrl`,`CountryCode`,`ShippingCountry`,`CategoryId`,`Order`,`SupportType`,`LastChangeTime` from program_intell  where ProgramId in ($pids)";
		$program_old_infos = $this->objMysql->getRows($sql,'ProgramId');
		$log_sql = $ins_sql = '';
		foreach($insert_sql as $pid => $v){
			$tmp_insert = array();
			foreach($column_keys as $key){
				$tmp_insert[] = array_key_exists($key, $v) ? addslashes($v[$key]) : ((isset($program_old_infos[$v['ProgramId']][$key])) ? addslashes($program_old_infos[$v['ProgramId']][$key]) : (array_key_exists($key,$default_value)?$default_value[$key] : ''));
				
				if($key == "ProgramId" || $key == "IdInAff" || $key == "AffId")
					continue;
				
				if(isset($v[$key]) && isset($program_old_infos[$v['ProgramId']][$key]) && (strcasecmp($program_old_infos[$v['ProgramId']][$key],$v[$key]) != 0))
				{
					$v[$key] = is_numeric($program_old_infos[$v['ProgramId']][$key])?floatval($v[$key]):$v[$key];
					if($program_old_infos[$v['ProgramId']][$key] != $v[$key])
					{
						if($log_sql == '')
							$log_sql = "insert into program_intell_change_log (ProgramId,IdInAff,AffId,FieldName,FieldValueOld,FieldValueNew,AddTime) values ('{$v['ProgramId']}','{$v['IdInAff']}','{$v['AffId']}','{$key}','{$program_old_infos[$v['ProgramId']][$key]}','{$v[$key]}','{$date}')";
						else
							$log_sql .= ",('{$v['ProgramId']}','{$v['IdInAff']}','{$v['AffId']}','{$key}','{$program_old_infos[$v['ProgramId']][$key]}','{$v[$key]}','{$date}')";
					}
				}
			}
			
			if($ins_sql == '')
			{
				$ins_sql = "INSERT INTO program_intell (`".implode("`,`", $column_keys)."`) VALUES ('".implode("','", $tmp_insert)."')";
			}
			else
			{
				$ins_sql .= ",('".implode("','", $tmp_insert)."')";
			}
		}
		if($log_sql)
		{
			$this->objMysql->query($log_sql);
		}
		if($ins_sql)
		{
			$ins_sql .= " ON DUPLICATE KEY UPDATE `AffDefaultUrl`=values(`AffDefaultUrl`),`DeepUrlTpl`=values(`DeepUrlTpl`),`IsActive`=values(`IsActive`),`Domain`=values(`Domain`),`CommissionValue`=values(`CommissionValue`),`CommissionType`=values(`CommissionType`),`CommissionUsed`=values(`CommissionUsed`),`CommissionIncentive`=values(`CommissionIncentive`),`SupportDeepUrl`=values(`SupportDeepUrl`),`LastUpdateTime`=values(`LastUpdateTime`),`OutGoingUrl`=values(`OutGoingUrl`),`DeniedPubCode`=values(`DeniedPubCode`),`CommissionCurrency`=values(`CommissionCurrency`),`CountryCode`=values(`CountryCode`),`ShippingCountry`=values(`ShippingCountry`),`CategoryId`=values(`CategoryId`),`Order`=values(`Order`),`LastChangeTime`=values(`LastChangeTime`),`SupportType`=values(`SupportType`)";
			$this->objMysql->query($ins_sql);
		}
	}
	
	function insertDomain($domain_arr = array()){
		if(!is_array($domain_arr)){
			$domain_arr = array($domain_arr);
		}
		foreach($domain_arr as $k => $v){
			$v = trim($v);
			if(!empty($v) && is_string($v))
			{
				if(stripos($v,"\r") !== false || stripos($v,"\n") !== false || stripos($v,"\r\n") !== false )
					unset($domain_arr[$k]);
				else
					$domain_arr[$k] = addslashes(trim($v, "."));
			}
			else
				unset($domain_arr[$k]);
		}
		if(count($domain_arr))
		{
			$sql = "select domain from domain where domain in ('".implode("','", $domain_arr)."')";
			$tmp_arr = $this->objMysql->getRows($sql, 'domain');
			foreach($domain_arr as $k => $v){
				if(isset($tmp_arr[$v])){
					unset($domain_arr[$k]);
				}
			}
			if(count($domain_arr))
			{
				$sql = "INSERT IGNORE INTO domain (domain) values ('".implode("'),('", $domain_arr)."')";
				$this->objMysql->query($sql);
			}	
		}
	}
	
	function getDomainProgramRelationshipByPid($pid){
		$data = array();
		$pid = intval($pid);
		if($pid){
			$sql = "SELECT a.DID, a.PID FROM r_domain_program a WHERE a.PID = $pid and IsHandle <> '1' ";
			$data = $this->objMysql->getRows($sql, "DID");
		}
		return $data;
	}
	
	function getDomainInfoById($did){
		$sql = "SELECT ID, Domain, Existed, SubDomain, DomainName, CountryCode, SupportAff FROM domain WHERE id = '".intval($did)."'";
		$data = $this->objMysql->getFirstRow($sql);		
		return $data;
	}
	
	function getDomainInfoByDomain($domain_arr = array()){
		$data = array();
		if(count($domain_arr)){
			foreach($domain_arr as $k => $v){
				$domain_arr[$k] = addslashes($v);
			}		
			$sql = "SELECT ID, Domain FROM domain WHERE domain in ('".implode("','", $domain_arr)."')";
			$data = $this->objMysql->getRows($sql, "ID");
		}
		return $data;
	}
	
	function getDomainUnionByDomain($domain_arr = array()){
		$data = array();
		if(count($domain_arr)){
			foreach($domain_arr as $k => $v){
				$domain_arr[$k] = addslashes($v);
			}		
			$sql = "select DomainFromid, DomainToid from r_domain_union where DomainFromid in ('".implode("','", $domain_arr)."') or DomainToid in ('".implode("','", $domain_arr)."')";
			$data = $this->objMysql->getRows($sql);			
		}
		return $data;
	}
	
	function deleteDomainProgramRelationship($p_d_arr = array()){
		foreach($p_d_arr as $pid => $did_arr){			
			$sql = "update r_domain_program set status = 'Inactive', lastupdatetime = '".date("Y-m-d H:i:s")."' where pid = $pid and did in ('" . implode("','", $did_arr) . "')";
			$this->objMysql->query($sql);
		}
	}
	
	function addDomainProgramRelationship($p_d_arr = array()){		
		$date_now = date("Y-m-d H:i:s");
		foreach($p_d_arr as $pid => $did_arr){
			foreach($did_arr as $did){				
				$sql = "INSERT INTO r_domain_program (did, pid, lastupdatetime, STATUS) VALUES ('".intval($did)."', '".intval($pid)."', '$date_now', 'Active') ON DUPLICATE KEY UPDATE STATUS = 'Active' , lastupdatetime = '$date_now'";
				$this->objMysql->query($sql);
			}
		}		
	}
	
	function getNeedCheckDomain($time){
		$data = array();
		if($time){
			$sql = "select did from r_domain_program where lastupdatetime > '".addslashes($time)."' group by did";
			$data = $this->objMysql->getRows($sql, "did");
		}		
		return $data;
	}
	
	function getDefaultOutgoingByDomain($did){
		$data = array();
		$sql = "SELECT DID, PID, `Key`, LimitAccount FROM domain_outgoing_default WHERE DID = ".intval($did)." ";// when denied to 
		$data = $this->objMysql->getRows($sql, "Key");
		return $data;
	}
	
	function setDefaultDomainOutgoing($data, $site = ''){
		if(count($data)){
			if(!isset($data["IsFake"])) $data["IsFake"] = 'NO';
			if(!isset($data["AffiliateDefaultUrl"])) $data["AffiliateDefaultUrl"] = isset($data["AffDefaultUrl"]) ? $data["AffDefaultUrl"] : '';
			if(!isset($data["DeepUrlTemplate"])) $data["DeepUrlTemplate"] = isset($data["DeepUrlTpl"]) ? $data["DeepUrlTpl"] : '';
			
			if($site){
				if(strpos($data["Key"], "/") === false){
					$sql = "select pid from domain_outgoing_default_site where `key` = '".addslashes($data["Key"])."' and site = '$site' and DID = '".intval($data["DID"])."' limit 1";
					$old_pid = intval($this->objMysql->getFirstRowColumn($sql));
					if($old_pid <> $data["PID"]){
						$sql = "insert ignore into domain_outgoing_default_changelog_site(site, DID, `Key`, ProgramFrom, ProgramTo, Changetime) values('$site', '".intval($data["DID"])."', '".addslashes($data["Key"])."', '".intval($old_pid)."', '".intval($data["PID"])."', '".date("Y-m-d H:i:s")."')";
						$this->objMysql->query($sql);
					}
				}
				$sql = "insert into domain_outgoing_default_site(site, DID, PID, `Key`, LimitAccount, IsFake, AddTime, LastUpdateTime, AffiliateDefaultUrl, DeepUrlTemplate) values('$site','".intval($data["DID"])."', '".intval($data["PID"])."', '".addslashes($data["Key"])."', '".addslashes($data["LimitAccount"])."', '".addslashes($data["IsFake"])."', '".date("Y-m-d H:i:s")."', '".date("Y-m-d H:i:s")."', '".addslashes($data["AffiliateDefaultUrl"])."', '".addslashes($data["DeepUrlTemplate"])."') ON DUPLICATE KEY UPDATE PID = '".intval($data["PID"])."', LimitAccount = '".addslashes($data["LimitAccount"])."', IsFake = '".addslashes($data["IsFake"])."', LastUpdateTime = '".date("Y-m-d H:i:s")."', AffiliateDefaultUrl = '".addslashes($data["AffiliateDefaultUrl"])."', DeepUrlTemplate = '".addslashes($data["DeepUrlTemplate"])."'";
				$this->objMysql->query($sql);
				
				$sql = "insert into domain_outgoing_default_other(site, DID, PID, `Key`, LimitAccount, IsFake, AddTime, LastUpdateTime, AffiliateDefaultUrl, DeepUrlTemplate) values('$site','".intval($data["DID"])."', '".intval($data["PID"])."', '".addslashes($data["Key"])."', '".addslashes($data["LimitAccount"])."', '".addslashes($data["IsFake"])."', '".date("Y-m-d H:i:s")."', '".date("Y-m-d H:i:s")."', '".addslashes($data["AffiliateDefaultUrl"])."', '".addslashes($data["DeepUrlTemplate"])."') ON DUPLICATE KEY UPDATE PID = '".intval($data["PID"])."', LimitAccount = '".addslashes($data["LimitAccount"])."', IsFake = '".addslashes($data["IsFake"])."', LastUpdateTime = '".date("Y-m-d H:i:s")."', AffiliateDefaultUrl = '".addslashes($data["AffiliateDefaultUrl"])."', DeepUrlTemplate = '".addslashes($data["DeepUrlTemplate"])."'";
				$this->objMysql->query($sql);
			}else{
				$sql = "insert into domain_outgoing_default(DID, PID, `Key`, LimitAccount, IsFake, AddTime, LastUpdateTime, AffiliateDefaultUrl, DeepUrlTemplate) values('".intval($data["DID"])."', '".intval($data["PID"])."', '".addslashes($data["Key"])."', '".addslashes($data["LimitAccount"])."', '".addslashes($data["IsFake"])."', '".date("Y-m-d H:i:s")."', '".date("Y-m-d H:i:s")."', '".addslashes($data["AffiliateDefaultUrl"])."', '".addslashes($data["DeepUrlTemplate"])."') ON DUPLICATE KEY UPDATE PID = '".intval($data["PID"])."', LimitAccount = '".addslashes($data["LimitAccount"])."', IsFake = '".addslashes($data["IsFake"])."', LastUpdateTime = '".date("Y-m-d H:i:s")."', AffiliateDefaultUrl = '".addslashes($data["AffiliateDefaultUrl"])."', DeepUrlTemplate = '".addslashes($data["DeepUrlTemplate"])."'";
				$this->objMysql->query($sql);
			}
		}
	}
	
	function removeDefaultDomainOutgoing($data){
		if(count($data)){			
			$sql = "delete from domain_outgoing_default where `DID` = '".intval($data["DID"])."' AND `PID` = '".intval($data["PID"])."'";
			$this->objMysql->query($sql);
		}
	}
	
	function setDefaultDomainOutgoingChangelog($data){
		global $is_debug;
		if(count($data)){
			if($data["Old_PID"] != $data["New_PID"]){
				if($is_debug){
					echo "changed";
					//exit;
				}
				$sql = "select id from domain_outgoing_default_changelog where did = '".intval($data["DID"])."' and `key` = '".addslashes($data["Key"])."' and programfrom = '".intval($data["Old_PID"])."' and programto = '".intval($data["New_PID"])."' and changetime >= '".date("Y-m-d H:i:s", strtotime("-1 seconds"))."' limit 1";
				$tmp_arr = array();
				$tmp_arr = $this->objMysql->getFirstRow($sql);	
				if(!count($tmp_arr)){
					$sql = "insert ignore into domain_outgoing_default_changelog(DID, `Key`, ProgramFrom, ProgramTo, Changetime) values('".intval($data["DID"])."', '".addslashes($data["Key"])."', '".intval($data["Old_PID"])."', '".intval($data["New_PID"])."', '".date("Y-m-d H:i:s")."')";
					$this->objMysql->query($sql);
					return 1;
				}		
			}
		}
	}
	
	function getDomainRelatedProgramIntell($did){
		$data = array();
		$sql = "SELECT a.DID, a.PID, b.AffId, b.CommissionType, b.CommissionUsed, b.CommissionIncentive, b.DeniedPubCode, c.Domain, b.AffDefaultUrl, b.DeepUrlTpl, b.SupportDeepUrl, b.OutGoingUrl FROM r_domain_program a INNER JOIN program_intell b ON a.pid = b.programid INNER JOIN domain c ON a.DID = c.id WHERE b.isactive = 'active' AND a.status = 'active' AND c.countrycode = b.countrycode AND a.DID = ".intval($did);// when denied to 
		$data = $this->objMysql->getRows($sql);
		return $data;
	}
	
	function getDomainRelatedProgramIntellFake($did){
		$data = array();
		$sql = "SELECT a.DID, a.PID, b.AffId, b.CommissionType, b.CommissionUsed, b.CommissionIncentive, b.DeniedPubCode, c.Domain, b.AffDefaultUrl, b.DeepUrlTpl, b.SupportDeepUrl, b.OutGoingUrl FROM r_domain_program a INNER JOIN program_intell b ON a.pid = b.programid INNER JOIN domain c ON a.DID = c.id WHERE b.isactive = 'active' AND a.DID = ".intval($did);// when denied to 
		$data = $this->objMysql->getRows($sql);
		return $data;
	}
	
	function getBlockRelationship($where_arr = array()){
		$data = array();
		$tmp_arr = array("1 = 1");
		foreach($where_arr as $k => $v){
			$tmp_arr[] = "$k = '" . addslashes($v) . "'";
		}
		$sql = "select accountid, objid, objtype, status from block_relationship where " . implode(" AND ", $tmp_arr);
		$data = $this->objMysql->getRows($sql);
		return $data;
	}
	
	function updateDefaultOutgoingToRedis($objRedis, $time){
		$i = 0;
		if(is_object($objRedis)){
			$update_time = date("Y-m-d H:i:s");
			$sql = "SELECT a.DID, a.PID, a.Key, b.IdInAff, b.AffId, b.CommissionUsed, b.CommissionType, b.CommissionIncentive, b.AffDefaultUrl, b.DeepUrlTpl, b.OutGoingUrl, c.Domain FROM domain_outgoing_default a left join program_intell b on a.pid = b.programid inner join domain c on c.id = a.did WHERE a.LastUpdateTime > '".addslashes($time)."'";
			//$sql = "SELECT a.DID, a.PID, a.Key, b.IdInAff, b.AffId, b.CommissionUsed, b.CommissionType, b.CommissionIncentive, b.AffDefaultUrl, b.DeepUrlTpl, c.Domain FROM domain_outgoing_default a left join program_intell b on a.pid = b.programid inner join domain c on c.id = a.did WHERE a.Key = 'affiliates.self-publishing-coach.com'";
			$qryId = $this->objMysql->query($sql);
			while($row = $this->objMysql->getRow($qryId)){				
				if($row["Key"]){
					$i++;
					$row["LastUpdateTime"] = $update_time;
					$objRedis->set($row["Key"], json_encode($row));
					
					/*print_r($row);				
					echo json_encode($row);
					echo "\r\n\r\n";
					echo $objRedis->get($row["Key"]);
					echo "\r\n";
					exit;*/
				}
			}
			$this->objMysql->freeResult($qryId);
		}
		return $i;
	}
	
	function updateAffinfoToRedis($objRedis){
		$sql = "select id, name, AffiliateUrlKeywords, AffiliateUrlKeywords2, SubTracking, SubTracking2 from wf_aff where status = 'active'";
		$data = $this->objMysql->getRows($sql);
		foreach($data as $v){
			$key_domain = explode("\r\n", $v["AffiliateUrlKeywords"]);
			foreach($key_domain as $key){
				if($key){
					$i++;
					
					
					
					//$row["LastUpdateTime"] = $update_time;
					//$objRedis->set($row["Key"], json_encode($row));					
				}
			}
		}
		$objRedis->del();
		
	}
	
	function insertUpdateQueue($update_sql)
	{
		global $date;
		$sql = "insert into program_update_queue (`ProgramID`,`FieleName`,`Status`,`UpdateTime`) values ";
		foreach ($update_sql as $pid=>$data)
		{
			$field = implode(',',array_keys($data));
			$sql .= "('{$pid}','{$field}','NEW','$date'),";
		}
		$sql = trim($sql,',');
		$this->objMysql->query($sql);
	}

	
}//end class
?>
