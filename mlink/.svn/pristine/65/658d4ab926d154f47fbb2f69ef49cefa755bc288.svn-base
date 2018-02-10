<?php
if (!defined("__MOD_CLASS_Store__"))
{
	define("__MOD_CLASS_Store__",1);
	class Store
	{
		function __construct()
		{
			$this->objMysql = new Mysql(TASK_DB_NAME, TASK_DB_HOST, TASK_DB_USER, TASK_DB_PASS);
		}
		
		function getAllCategoryByTree() {
			$data = array();
			$sql = "select * from `store_category`";
			$dataSource = $this->objMysql->getRows($sql);
			
			foreach ($dataSource as $k => $v) {
				if ($v['ParentID'] == 0) {
					$data[$v['ID']]['ParentCate'] = array('ID' => $v['ID'], 'Name' => $v['Name']);
				} else {
					$data[$v['ParentID']]['ChildCate'][] = array('ID' => $v['ID'], 'Name' => $v['Name']);
				}
			}
			
			return $data;
		}
		
		function getAllCategorySort(){
			$sql = "select * from `store_category` where `ParentID`!=0";
			$tmp = $this->objMysql->getRows($sql);
			foreach ($tmp as $v) {
				$data[$v['ParentID']][] = $v['ID'];
			}
			
			return $data;
		}
		
		function getCategory($condition = '')
		{
			$data = array();
			$sql = "select * from `store_category` ";
			if (!empty($condition)) $sql .= "where {$condition} ";
			$dataTmp = $this->objMysql->getRows($sql);
			foreach ((array)$dataTmp as $v) {
				$data[$v['ID']] = $v['Name'];
			}
			
			return $data;
		}
		
		function getCategoryInfoByID($id) {
			$data = array();
			if (!is_numeric($id)) return $data;
			$sql = "select * from `store_category` where `ID`={$id}";
			
			$query = $this->objMysql->query($sql);
			$data = $this->objMysql->getRow($query);
			return $data;
		}
		
		function getStoreListByCondition($condition = array(), $fields = '*')
		{
			$data = array();
			if (empty($condition)) return $data;
			$sql = "select {$fields} from `store` ";
			
			if (!empty($condition['sql'])) $sql .= "where 1=1 {$condition['sql']} ";
			if (!empty($condition['order'])) $sql .= "order by {$condition['order']} ";
			if (!empty($condition['limit'])) $sql .= "limit {$condition['limit']} ";
			
			$data = $this->objMysql->getRows($sql);
			
			return $data;
		}
		
		function checkUrluique($url, $originalurl = '') {
			$res = 0;
			if (!empty($originalurl) && $url == $originalurl) return $res;
			
			$httpurl  = preg_replace("/https:\/\//is", 'http://', $url);
			$httpsurl = preg_replace("/http:\/\//is", 'https://', $url);
			
			if (substr($url, -1) == '/') {
				$urlno_  = substr($url, 0, -1);
				$urlyes_ = $url;
			} else {
				$urlno_  = $url;
				$urlyes_ = $url . '/';
			}
			$url_arr = array($httpurl, $httpsurl, $urlyes_, $urlno_);
			
			$i = 0;
			$status = false;
			do {
				$sql = "select count(*) as cnt from `store` where `Url`='{$url_arr[$i]}'";
				$query = $this->objMysql->query($sql);
				$row = $this->objMysql->getRow($query);
				if ($row['cnt'] > 0) $status = ($status OR true);
				else $status = ($status OR false);
				$i++;
			} while (!$status && $i < count($url_arr));
			
			return $status;
		}
		
		function checkStoreNameUnique($storename = '', $storeid = '') {
			if (empty($storename)) return true;
			
			$sql = "select * from `store` where `Name`='" . addslashes($storename) . "'";
			if (!empty($storeid)) $sql = "select * from `store` where `Name`='" . addslashes($storename) . "' and ID!={$storeid}";
			
			$tmp = $this->objMysql->getRows($sql);
			
			if (empty($tmp)) return false;
			
			return true;
		}
		
		function checkDomain($domain = '', $storeid = '') {
			$res = '';
			if (empty($domain)) return $res;
			
			$sql = "select `Url` from `store` where Domain='{$domain}'";
			if (!empty($storeid)) $sql = "select `Url` from `store` where Domain='{$domain}' and ID!={$storeid}";
			
			$res = $this->objMysql->getRows($sql);
			
			return $res;
		}
		
		function tidyInsertData($data = array()) {
			if (isset($data['Url']) && !empty($data['Url'])) {
				$data['Url'] = preg_replace("/\s+/", "", trim($data['Url']));
			}
			if (isset($data['SupportedShippingCountry']) && !empty($data['SupportedShippingCountry'])) {
				$data['SupportedShippingCountry'] = implode(',', (array)$data['SupportedShippingCountry']);
			} else {
				$data['SupportedShippingCountry'] = '';
			}
			if (isset($data['CouponTitle']) && $data['CouponTitle'] == 'Other') {
				$data['CouponTitle'] = $data['CouponTitleOther'];
			}
			unset($data['CouponTitleOther']);
			unset($data['action']);
			unset($data['ID']);
			unset($data['etype']);
			
			return $data;
		}
		
		function insertSingleData($row) {
			$return = array('msg' => true);
			if (empty($row)) return $return;
			$storeRelationRow['MerchantID'] = $row['MerchantID'];
			
			$comment['Content'] = trim($row['Comment']);
			$comment['AddUser'] = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
			$comment['AddDate'] = date('Y-m-d H:i:s');
			
			$row['AddTime'] = date('Y-m-d H:i:s');
			unset($row['MerchantID']);
			unset($row['Comment']);
			
			$fields = '';
			$values = '';
			$storeRelFields = '';
			$storeRelValues = '';
			$commentFields = '';
			$commentValues = '';
			$commentRelFields = '';
			$commentRelValues = '';
			$sql = "insert into `store` ";
			$sqlStoreRelationship = "insert into `store_merchant_relationship` ";
			$sqlComment = "insert into `comment` ";
			$sqlCommentRelationship = "insert into `comment_relationship` ";
			
			foreach ($row as $k => $v) {
				$fields .= "`" . $k . "`, ";
				$values .= "'" . addslashes($v) . "', ";
			}
			$fields = preg_replace("|, $|i", '', $fields);
			$values = preg_replace("|, $|i", '', $values);
			$sqlQuery = $sql . '(' . $fields . ') values (' . $values . ');';
			
		    if (!$res = $this->objMysql->query($sqlQuery))
			{
				$return['msg'] = false;
				return $return;
			}
			else
			{
				$storeRelationRow['StoreID'] = $this->objMysql->getLastInsertId();
				if (!empty($storeRelationRow['MerchantID'])) {
					$storeRelFields .= " `StoreID`, `SiteName`, `MerchantID`, `AddTime` ";
					foreach ((array)$storeRelationRow['MerchantID'] as $k1 => $v1) {
						$vtmp = explode('-', $v1);
						$csql = "select count(*) as cnt from `store_merchant_relationship` where `SiteName`='{$vtmp[1]}' and `MerchantID`={$vtmp[0]}";
						$cquery = $this->objMysql->query($csql);
						$count = $this->objMysql->getRow($cquery);
						if (isset($count['cnt']) && $count['cnt'] > 0) continue;
					    $storeRelValues .= "('" . addslashes($storeRelationRow['StoreID']) . "', '" . addslashes($vtmp[1]) . "', '" . addslashes($vtmp[0]) . "', '" . date('Y-m-d H:i:s') . "'), ";
					}
					$storeRelValues = preg_replace("|, $|i", '', $storeRelValues);
			        $sqlStoreRelationshipQuery = $sqlStoreRelationship . '(' . $storeRelFields . ') values ' . $storeRelValues;
			        
			        if (!$res1 = $this->objMysql->query($sqlStoreRelationshipQuery)) {
			        	$return['msg'] = false;
			        	return $return;
			        }
				}
				
				if (!empty($comment['Content'])) {
					$commentFields .= " `AddUser`, `AddDate`, `Content` ";
					$commentValues .= "('" . addslashes($comment['AddUser']) . "', '" . addslashes($comment['AddDate']) . "', '" . addslashes($comment['Content']) . "')";
					$sqlCommentQuery = $sqlComment . '(' . $commentFields . ') values ' . $commentValues;
			        
			        if (!$res2 = $this->objMysql->query($sqlCommentQuery)) {
			        	$return['msg'] = false;
			        	return $return;
			        } else {
			        	$commentRel['CommentID'] = $this->objMysql->getLastInsertId();
			        	$commentRelFields .= " `CommentID`, `ObjectType`, `ObjectID` ";
			        	$commentRelValues .= "('" . addslashes($commentRel['CommentID']) . "', 'store', '" . addslashes($storeRelationRow['StoreID']) . "')";
			        	$sqlCommentRelationshipQuery = $sqlCommentRelationship . '(' . $commentRelFields . ') values ' . $commentRelValues;
			        	
			        	if (!$res3 = $this->objMysql->query($sqlCommentRelationshipQuery)) {
			        		$return['msg'] = false;
			        	    return $return;
			        	}
			        }
				}
			}
			$return = array('msg' => true, 'storeid' => $storeRelationRow['StoreID']);
			return $return;
		}
		
		function insertMerchantRequest($row = array(), $storeid = '') {
			if (empty($row) || empty($storeid)) return false;
			$sql = "select * from store where ID = '$storeid'";
			$rows = $this->objMysql->getRows($sql);
			$storeInfo = $rows[0];
			$AffiliateDefaultUrl = "";
			$DeepUrlTemplate = "";
			$affId = "";
			$programId = "";
			$storeTraffic = "";
			$now = date("Y-m-d H:i:s");
			if($storeid != ""){
				$sql = "select * from merchant_request where StoreID = '$storeid'";
				$existRows = $this->objMysql->getRows($sql);
				if(count($existRows) > 0){
					foreach($existRows as $existRow){
						$existSource = trim($existRow["Source"]);
						if($existSource == ""){
							$existSource = "BD";
						}else{
							if(stripos($existSource, "BD") === false){
								$existSource = $existSource . ",BD";
							}
						}
						$sql = "update merchant_request set Source = '" . addslashes($existSource) . "', LastUpdateTime = '$now' where ID = '{$existRow["ID"]}'";
						$existRows = $this->objMysql->query($sql);
					}
					return true;
				}
			}
			
			if(count($rows) != 0){
				$AffiliateDefaultUrl = $storeInfo["AffiliateDefaultUrl"];
				$DeepUrlTemplate = $storeInfo["DeepUrlTemplate"];
				$sql = "  SELECT a.*, b.AffId FROM `program_store_relationship` a, `program` b WHERE a.ProgramId = b.ID AND a.StoreId = '$storeid' AND `Status` = 'Active' ORDER BY `Order`";
				$affRows = $this->objMysql->getRows($sql);
				if(count($affRows) != 0){
					$affId = $affRows[0]["AffId"];
					$programId = $affRows[0]["ProgramId"];
				}
				foreach($affRows as $tmpRow){
					if(trim($AffiliateDefaultUrl) == "" && trim($tmpRow["AffiliateDefaultUrl"]) != ""){
						$AffiliateDefaultUrl = $tmpRow["AffiliateDefaultUrl"];
					}
					if(trim($DeepUrlTemplate) == "" && trim($tmpRow["DeepUrlTemplate"]) != ""){
						$DeepUrlTemplate = $tmpRow["DeepUrlTemplate"];
					}
				}
			}
			$sql = "insert into `merchant_request` (`Name`, `Homepage`, `Domain`, `StoreID`, `Source`, `Creator`, `CreateTime`, `TargetCountry`, `Analysis`,
					AlexaRank7d, AlexaRank3m, AlexaReach7d, AlexaReach1m, AlexaReach3m, CompeteUV, CompeteRank,
					Affiliation, AffProgramId, DefaultURL, DeepURLTemplate,Category) values 
					('" . addslashes($row['Name']) . "', '" . addslashes($row['Url']) . "', '" . addslashes($row['Domain']) . "', '" . $storeid . "', 'BD', 'system', NOW(), '" . addslashes($row['SupportedShippingCountry']) . "', '" . addslashes($row['Analysis']) .
					 "','" . addslashes($storeInfo["AlexaRank7d"]) . "', '" . addslashes($storeInfo["AlexaRank3m"]) . "', '". 
					addslashes($storeInfo["AlexaReach7d"]) . "', '" . addslashes($storeInfo["AlexaReach1m"]) . "', '" . 
					addslashes($storeInfo["AlexaReach3m"]) ."', '" . addslashes($storeInfo["CompeteUV"]) ."', '" . addslashes($storeInfo["CompeteRank"]) .
					"',	'" . addslashes($affId) . "', '" . addslashes($programId) . "', '". addslashes($AffiliateDefaultUrl) . "', '" . addslashes($DeepUrlTemplate) . "', '" . addslashes($Category) . "')";
			if (!$this->objMysql->query($sql)) return false;
			return true;
		}
		
		function updateData($row, $id) {
			if (empty($row)) return false;
			$storeRelationRow['MerchantID'] = $row['MerchantID'];
			
			$comment['Content'] = trim($row['Comment']);
			$comment['AddUser'] = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
			$comment['AddDate'] = date('Y-m-d H:i:s');
			
			unset($row['MerchantID']);
			unset($row['Comment']);
			
			$sqlQuery = "update `store` set ";
			$sqlStoreRelationship = "insert into `store_merchant_relationship` ";
			$sqlComment = "insert into `comment` ";
			$sqlCommentRelationship = "insert into `comment_relationship` ";
			
			$storeRelFields = '';
			$storeRelValues = '';
			$commentFields = '';
			$commentValues = '';
			$commentRelFields = '';
			$commentRelValues = '';
			
			foreach ($row as $k => $v) 
			{
				$sqlQuery .= "`" . $k . "` = '" . addslashes($v) . "', ";
			}
			
			$sqlQuery = preg_replace("|, $|i", ' ', $sqlQuery);
			$sqlQuery .= " WHERE `ID`={$id}";
			
			if (!$res = $this->objMysql->query($sqlQuery))
			{
				return false;
			}
			else
			{
				if (!empty($storeRelationRow['MerchantID'])) {
					$storeRelFields .= " `StoreID`, `SiteName`, `MerchantID`, `AddTime` ";
					foreach ((array)$storeRelationRow['MerchantID'] as $k1 => $v1) {
						$vtmp = explode('-', $v1);
						$csql = "select count(*) as cnt from `store_merchant_relationship` where `SiteName`='{$vtmp[1]}' and `MerchantID`={$vtmp[0]}";
						$cquery = $this->objMysql->query($csql);
						$count = $this->objMysql->getRow($cquery);
						if (isset($count['cnt']) && $count['cnt'] > 0) continue;
						
					    $storeRelValues .= "('" . addslashes($id) . "', '" . addslashes($vtmp[1]) . "', '" . addslashes($vtmp[0]) . "', '" . date('Y-m-d H:i:s') . "'), ";
					}
					$storeRelValues = preg_replace("|, $|i", '', $storeRelValues);
					if (empty($storeRelValues)) return true;
			        $sqlStoreRelationshipQuery = $sqlStoreRelationship . '(' . $storeRelFields . ') values ' . $storeRelValues;
			        
			        if (!$res1 = $this->objMysql->query($sqlStoreRelationshipQuery)) return false;
			        
					if (!$this->setStoreProgramRelToMer(array($id))) {
						return false;
					}
				}
				
			    if (!empty($comment['Content'])) {
					$commentFields .= " `AddUser`, `AddDate`, `Content` ";
					$commentValues .= "('" . addslashes($comment['AddUser']) . "', '" . addslashes($comment['AddDate']) . "', '" . addslashes($comment['Content']) . "')";
					$sqlCommentQuery = $sqlComment . '(' . $commentFields . ') values ' . $commentValues;
			        
			        if (!$res2 = $this->objMysql->query($sqlCommentQuery)) {
			        	return false;
			        } else {
			        	$commentRel['CommentID'] = $this->objMysql->getLastInsertId();
			        	$commentRelFields .= " `CommentID`, `ObjectType`, `ObjectID` ";
			        	$commentRelValues .= "('" . addslashes($commentRel['CommentID']) . "', 'store', '" . addslashes($id) . "')";
			        	$sqlCommentRelationshipQuery = $sqlCommentRelationship . '(' . $commentRelFields . ') values ' . $commentRelValues;
			        	
			        	if (!$res3 = $this->objMysql->query($sqlCommentRelationshipQuery)) return false;
			        }
				}
			}
			
			return true;
		}
		
		function getStoreByID($id) {
			$data = array();
			if (!is_numeric($id)) return $data;
			$sql = "select * from `store` where `ID`={$id}";
			if ($query = $this->objMysql->query($sql)) {
				$data = $this->objMysql->getRow($query);
			}
			
			return $data;
		}
		
		function getMerchantsByKw($kw, $site) {
			global $databaseInfo;
			$data = array();
			$sql = '';
			if (trim($kw) == '') return $data;
			$siteModel = new Mysql($databaseInfo["INFO_" . strtoupper(trim($site)) . "_DB_NAME"], $databaseInfo["INFO_" . strtoupper(trim($site)) . "_DB_HOST"], $databaseInfo["INFO_" . strtoupper(trim($site)) . "_DB_USER"], $databaseInfo["INFO_" . strtoupper(trim($site)) . "_DB_PASS"]);
		    
			preg_match('/[^\d]+/', trim($kw), $matches);
			if (!empty($matches)) {
				$sql = "select `ID`, `Name` from `normalmerchant` where `Name` like '%". addslashes($kw) ."%' ";
			} else {
				$sql = "select `ID`, `Name` from `normalmerchant` where (`ID` like '%". addslashes($kw) ."%' or `Name` like '%". addslashes($kw) ."%') ";
			}
			
			$data = $siteModel->getRows($sql);
			foreach ((array)$data as $k => $v) {
				$data[$k]['Site'] = $site;
			}
			
			return $data;
			
		}
		
		function getStoreRelationshipByStoreID($storeid, $addinfo = false) {
			global $databaseInfo;
			$data = array();
			if (!is_numeric($storeid)) return $data;
			$sql = "select * from `store_merchant_relationship` where `StoreID`={$storeid}";
			
			$data = $this->objMysql->getRows($sql);
			
			foreach ($data as $k => $v) {
				$siteModel = new Mysql($databaseInfo["INFO_" . strtoupper($v['SiteName']) . "_DB_NAME"], $databaseInfo["INFO_" . strtoupper($v['SiteName']) . "_DB_HOST"], $databaseInfo["INFO_" . strtoupper($v['SiteName']) . "_DB_USER"], $databaseInfo["INFO_" . strtoupper($v['SiteName']) . "_DB_PASS"]);
			    $sql = "select n.`Name`,na.`Grade` from `normalmerchant` n LEFT JOIN `normalmerchant_addinfo` na ON na.ID = n.ID where n.`ID`={$v['MerchantID']} ";
			    $query = $siteModel->query($sql);
			    $row = $siteModel->getRow($query);
			    $data[$k]['MerchantName'] = $row['Name'];
				$data[$k]['Grade'] = $row['Grade'];
			    if($addinfo){
			    	$sql = "select `NewsletterSubscription` from `normalmerchant_addinfo` where `ID`={$v['MerchantID']} ";
				    $query = $siteModel->query($sql);
				    $row = $siteModel->getRow($query);
				    if($row['NewsletterSubscription'] != ""){
				    	$data[$k]['NewsletterSubscription'] = $row['NewsletterSubscription'];
				    }else{
				    	$data[$k]['NewsletterSubscription'] = "UNKNOWN";
				    }
			    }
			}
			
			return $data;
		}
		
		function getStoreHDDRelationshipByStoreID($storeid) {
			global $databaseInfo;
			$data = array();
			if (!is_numeric($storeid)) return $data;
			$sql = "select * from `store_hdd_relationship` where `StoreID`={$storeid}";
			
			$data = $this->objMysql->getRows($sql);
			
			return $data;
		}
		
		function deleteStoreRel($condition = array()) {
			if (empty($condition)) return true;
			$sql = "delete from `store_merchant_relationship` where `StoreID`={$condition['StoreID']} and `SiteName`='{$condition['SiteName']}' and `MerchantID`={$condition['MerchantID']}";
			$res = $this->objMysql->query($sql);
			if ($res) {
				$objTask = new Task();
				$objMysqlSite = $objTask->getSiteMysqlObj(strtolower($condition['SiteName']));
				
				$sql = "delete FROM wf_mer_in_aff where `MerID` = {$condition['MerchantID']}";
				$objMysqlSite->query($sql);
					
				$sql = "UPDATE normalmerchant SET HasAffiliate = 'NO' WHERE ID = {$condition['MerchantID']}";
				$objMysqlSite->query($sql);
					
				if (!$this->setStoreProgramRelToMer(array($condition['StoreID']))) {
					return false;
				}
			} else {
				return false;
			}
			
			return true;
		}
		
		function getDistinctStoreID() {
			$data = array();
			$sql = "select distinct(`StoreID`) from `store_merchant_relationship`";
			$tmp = $this->objMysql->getRows($sql);
			foreach ((array)$tmp as $v) {
				$data[] = $v['StoreID'];
			}
			
			return $data;
		}
		
		function getCommentsByStoreID($id = '') {
			$data = array();
			if (empty($id)) return $data;
			$sql = "select `CommentID` from `comment_relationship` where `ObjectType`='store' and `ObjectID`={$id} order by `CommentID` desc";
			$tmp = $this->objMysql->getRows($sql);
			foreach ($tmp as $k => $v) {
				$sql1 = "select * from `comment` where `ID`={$v['CommentID']}";
				$query = $this->objMysql->query($sql1);
				$row = $this->objMysql->getRow($query);
				$data[$k] = $row;
			}
			
			return $data;
		}
		
		function getRelUrlsStoreID($id = '') {
			$data = array();
			if (empty($id)) return $data;
			
			$sql = "select * from `store_related_url` where `StoreID`={$id}";
			$data = $this->objMysql->getRows($sql);
			
			return $data;
		}
		
		function deleteRelUrl($condition = array()) {
			if (empty($condition)) return true;
			$sql = "delete from `store_related_url` where `ID`={$condition['ID']}";
			$res = $this->objMysql->query($sql);
			if ($res) return true;
			else return false;
		}
		
		function checkStoreRel($condition = array()) {
			if (empty($condition)) return true;
			$sql = "select count(*) as cnt from `store_merchant_relationship` where `SiteName`='{$condition['SiteName']}' and `MerchantID`={$condition['MerchantID']}";
			$query = $this->objMysql->query($sql);
			$res = $this->objMysql->getRow($query);
			if ($res['cnt'] > 0) return true;
			else return false;
		}
		
		function getStoreByKw($kw) {
			$data = array();
			if (trim($kw) == '') return $data;
			
			preg_match('/[^\d]+/', trim($kw), $matches);
			if (!empty($matches)) {
				$sql = "select `ID`, `Name`, `Url` from `store` where `Name` like '%". addslashes($kw) ."%'";
			} else {
				$sql = "select `ID`, `Name`, `Url` from `store` where `ID` like '%". addslashes($kw) ."%' or `Name` like '%". addslashes($kw) ."%'";
			}
			
			$data = $this->objMysql->getRows($sql);
			
			return $data;
		}
		
		function deleteStoreAndRel($storeid = '') {
			if (empty($storeid)) return true;
			$store_sql = "delete from `store` where `ID`={$storeid}";
			$storemer_sql = "delete from `store_merchant_relationship` where `StoreID`={$storeid}";
			$storecompetitor_sql = "delete from `store_competitor_relationship` where `StoreID`={$storeid}";
			$storeprogram_sql = "delete from `program_store_relationship` where `StoreId`={$storeid}";
			
			if (!$this->objMysql->query($storecompetitor_sql)) return false;
			if (!$this->objMysql->query($storemer_sql)) return false;
			if (!$this->objMysql->query($storeprogram_sql)) return false;
			if (!$this->objMysql->query($store_sql)) return false;
			
			return true;
		}
		
		function removeStoreAndRelToLog($row) {
			$storeinfo = $this->getStoreByID($row['FromStoreID']);
			if (!$this->deleteStoreAndRel($row['FromStoreID'])) return false;
			$sql = "insert into `store_remove_log` (`Type`, `FromStoreID`,`FromStoreName`, `FromStoreUrl`, `Operator`, `Reason`, `AddTime`) values (
			       'DELETE', {$row['FromStoreID']}, '" . $storeinfo['Name'] . "', '" . addslashes($row['FromStoreUrl']) . "', '" . addslashes($row['Operator']) . "', '" . addslashes($row['Reason']) . "', '" . date('Y-m-d H:i:s') . "'
			       )";
			if (!$this->objMysql->query($sql)) return false;
			
			return true;
		}
		
		function mergeStoreAndRelToLog($row) {
			$storeinfo = $this->getStoreByID($row['FromStoreID']);
			$tostoreinfo = $this->getStoreByID($row['ToStoreID']);
			$store_mer_rel_update_sql = "update `store_merchant_relationship` set `StoreID`={$row['ToStoreID']} where `StoreID`={$row['FromStoreID']}";
			$store_coment_rel_update_sql = "update `comment_relationship` set `ObjectID`={$row['ToStoreID']} where `ObjectType`='store' and `ObjectID`={$row['FromStoreID']}";
			$store_delete_sql = "delete from `store` where `ID`={$row['FromStoreID']}";
			$sql = "insert into `store_remove_log` (`Type`, `FromStoreID`, `FromStoreName`, `FromStoreUrl`, `ToStoreID`,`ToStoreName`,`ToStoreUrl`, `Operator`, `Reason`, `AddTime`) values (
			       'MERGE', {$row['FromStoreID']}, '" . $storeinfo['Name']."', '" . addslashes($row['FromStoreUrl']) . "', {$row['ToStoreID']}, '" .$tostoreinfo['Name']. "', '". $tostoreinfo['Url'] ."', '" . addslashes($row['Operator']) . "', '" . addslashes($row['Reason']) . "', '" . date('Y-m-d H:i:s') . "'
			       )";
			
			$from_store_competitor_rel_sql = "select `CompetitorId`, `Url` from `store_competitor_relationship` where `StoreID`={$row['FromStoreID']}";
			$from_store_competitor_rel_data = $this->objMysql->getRows($from_store_competitor_rel_sql);
			
			$from_store_competitor_rel_delete_sql = "delete from `store_competitor_relationship` where `StoreID`={$row['FromStoreID']}";
			
			foreach ((array)$from_store_competitor_rel_data as $fv) {
				$to_sql = "replace into `store_competitor_relationship` (`StoreID`, `CompetitorId`, `Url`) values ({$row['ToStoreID']}, '{$fv['CompetitorId']}', '" . addslashes($fv['Url']) . "')";
				if (!$this->objMysql->query($to_sql)) return false;
			}
			
			if (!$this->objMysql->query($from_store_competitor_rel_delete_sql)) return false;
			if (!$this->objMysql->query($store_mer_rel_update_sql)) return false;
			if (!$this->objMysql->query($store_coment_rel_update_sql)) return false;
			if (!$this->objMysql->query($store_delete_sql)) return false;
			if (!$this->objMysql->query($sql)) return false;
			
			return true;
			
		}
		
		function getProgramStoreRelByStoreId($storeid) {
			$data = array();
			if (!is_numeric($storeid)) return $data;
			$sql = "select a.*, b.`Name` as programname, b.`AffId`, b.`IdInAff`, c.`Name` as affname from `program_store_relationship` a 
			        inner join `program` b on a.`ProgramId`=b.`ID` 
			        inner join `wf_aff` c on b.`AffId`=c.`ID`              
			        where a.`StoreId`={$storeid} order by a.`Order` asc";
			
			$data = $this->objMysql->getRows($sql);
			
			return $data;
		}
		
		function getPSRelBySMP($storeid) {
			global $databaseInfo;
			
			$data = array();
			if (!is_numeric($storeid)) return $data;
			
			$sql = "select mp.`Site`, mp.`ProgramId`, mp.`MerchantName`, mp.`MerchantId`, mp.`Status`, mp.`AffMerchantId`, p.`Name` as programname, p.`AffId`, p.`IdInAff`, wa.`Name` as affname from `store_merchant_relationship` sm 
			        inner join `merchant_program` mp on (sm.`SiteName`=mp.`Site` and sm.`MerchantID`=mp.`MerchantId`) 
			        inner join `program` p on mp.`ProgramId`=p.`ID` 
			        inner join `wf_aff` wa on mp.`AffId`=wa.`ID` 
			        where sm.`StoreID`={$storeid}";
			$data = $this->objMysql->getRows($sql);
			
			$baseModel = array();
			foreach ($data as $key => $val) {
				if (!isset($baseModel[$val['Site']])) $baseModel[$val['Site']] = new Mysql($databaseInfo["INFO_" . strtoupper($val['Site']) . "_DB_NAME"], $databaseInfo["INFO_" . strtoupper($val['Site']) . "_DB_HOST"], $databaseInfo["INFO_" . strtoupper($val['Site']) . "_DB_USER"], $databaseInfo["INFO_" . strtoupper($val['Site']) . "_DB_PASS"]);
				
				$_sql = "select `MerDefaultAffUrl`, `MerDeepUrlTemplate`, `OrderByNum`, `IsUsing` from `wf_mer_in_aff` where `AffID`='{$val['AffId']}' and `MerID`='{$val['MerchantId']}'";
				$wf_mer_in_aff_info = $baseModel[$val['Site']]->getRow($baseModel[$val['Site']]->query($_sql));
				$data[$key]['AffiliateDefaultUrl'] = isset($wf_mer_in_aff_info['MerDefaultAffUrl']) ? $wf_mer_in_aff_info['MerDefaultAffUrl'] : '';
				$data[$key]['DeepUrlTemplate'] = isset($wf_mer_in_aff_info['MerDeepUrlTemplate']) ? $wf_mer_in_aff_info['MerDeepUrlTemplate'] : '';
				$data[$key]['Order'] = isset($wf_mer_in_aff_info['OrderByNum']) ? $wf_mer_in_aff_info['OrderByNum'] : 0;
			}
			
			return $data;
		}
		
		function updateProgramStoreRel($row) {
			global $databaseInfo;
			
			$res = array('status' => true, 'msg' => '');
			$urlPattern = "/^(http|https|ftp)\:\/\/[A-Za-z0-9\-]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"])*$/";
			
			list($id, $storeid, $programid) = explode('|', $row['ids']);
			/*$sql = "select s.`StoreID`, s.`SiteName`, s.`MerchantID`, m.`AffId` from `store_merchant_relationship` s inner join `merchant_program` m on (s.`SiteName`=m.`Site` and s.`MerchantID`=m.`MerchantId`) 
			        where s.`StoreID`={$storeid} and m.`ProgramId`={$programid}";
			$storeMerRel = $this->objMysql->getRows($sql);
			if (empty($storeMerRel)) {
				$res['status'] = false;
				return $res;
			}*/
			
			$sqlQuery = "update `program_store_relationship` set `LastUpdateTime`='" . date('Y-m-d H:i:s') . "', ";
			$fields = '';
			foreach ($row['task'] as $k => $v) 
			{
				if ($k == 'Order') {
					if (!is_numeric($v)) {
						$res['status'] = false;
						$res['msg'] = 'Order must be an integer';
						return $res;
						break;
					}
				} elseif ($k == 'AffiliateDefaultUrl' || $k ==  "DeepUrlTemplate") {
					if (!preg_match($urlPattern, $v)) {
						$res['status'] = false;
						$res['msg'] = 'Affiliate Default URL is invalid';
						return $res;
						break;
					}
					$sqlAff = "SELECT a.AffId, a.IdInAff, b.IsInHouse, b.IsActive FROM program a, wf_aff b  WHERE a.ID = '$programid' AND a.AffId = b.ID";
					$rowsAff = $this->objMysql->getRows($sqlAff);
					
					$isInhouse = $rowsAff[0]["IsInHouse"];
					if($k == 'AffiliateDefaultUrl' && $v == ""){
						$res['status'] = false;
						$res['msg'] = 'Affiliate Default URL is invalid';
						return $res;
						break;
					}
					if (trim($v) != ''){
						if(count($rowsAff) == 0){
							$res['status'] = false;
							$res['msg'] = 'No affiliate.';
							return $res;
							break;
						}
						$affObj = new Affiliate();
						$affId = $rowsAff[0]["AffId"];
						$affInfo = $affObj->getAffilicateById($affId);
						$sql = "SELECT * FROM task.`program_store_relationship` WHERE ID = '$id'";
						$rowsPSRes = $this->objMysql->getRows($sqlAff);
						$rowsPSR = $rowsPSRes[0];
						if(!$affObj->checkAffurlValid($affId, $v) && $rowsPSR["Status"] == "Active"){
							$res['status'] = false;
							$res['msg'] = "Invalid {$k}({$v}). Please make sure using affiliate url." . "$programid: $storeid: $id";
							return $res;
							break;
						}
						/*
						$newMerchantAff[$affId] = $rowsAff[0]["IdInAff"];
						
						$affiliate = checkAffNameWithUrl($v, $newMerchantAff,  FRONT_ROOT . "etc");
						if ($affiliate == ''){
							$res['status'] = false;
							$res['msg'] = 'Invalid url. Please make sure using affiliate url.';
							break;
						}
						*/
					}
				}
				
				$fields .= "`" . $k . "` = '" . addslashes($v) . "', ";
			}
			
			if (empty($fields)) return $res;
			
			$sqlQuery .= $fields;
			$sqlQuery = preg_replace("|, $|i", ' ', $sqlQuery);
			$sqlQuery .= " where `ID`={$id}";
			
			if (!$this->objMysql->query($sqlQuery)) {
				$res['status'] = false;
				return $res;
			}
			
			if (!$this->setStoreProgramRelToMer(array($storeid))) {
				$res['status'] = false;
				return $res;
			}
			
			return $res;
		}
		
		function getSMPRelationByStoreId($storeid) {
			global $databaseInfo;
			
			$data = array();
			if (!is_numeric($storeid)) return $data;
			$sql = "select sm.`StoreID`, sm.`SiteName`, sm.`MerchantID`, mp.`AffId`, mp.`MerchantName`, mp.`ProgramId`, wa.`Name` as affname, p.`Name` as programname, p.`IdInAff`, s.Name as storename from `store_merchant_relationship` sm 
			        inner join `store` s on sm.`StoreID`=s.`ID` 
			        left join `merchant_program` mp on (sm.`SiteName`=mp.`Site` and sm.`MerchantID`=mp.`MerchantId`) 
			        left join `program` p on mp.`ProgramId`=p.`ID` 
			        left join `wf_aff` wa on mp.`AffId`=wa.`ID`              
			        where sm.`StoreID`={$storeid}";
			
			$data = $this->objMysql->getRows($sql);
			
			$baseModel = array();
			foreach ($data as $key => $val) {
				if (!empty($val['MerchantName'])) continue;
				if (!isset($baseModel[strtoupper($val['SiteName'])])) $baseModel[strtoupper($val['SiteName'])] = new Mysql($databaseInfo["INFO_" . strtoupper($val['SiteName']) . "_DB_NAME"], $databaseInfo["INFO_" . strtoupper($val['SiteName']) . "_DB_HOST"], $databaseInfo["INFO_" . strtoupper($val['SiteName']) . "_DB_USER"], $databaseInfo["INFO_" . strtoupper($val['SiteName']) . "_DB_PASS"]);	
				
				$sql = "select `Name` from `normalmerchant` where `ID`={$val['MerchantID']}";
				$merinfo = $baseModel[strtoupper($val['SiteName'])]->getRow($baseModel[strtoupper($val['SiteName'])]->query($sql));
				$val['MerchantName'] = isset($merinfo['Name']) ? trim($merinfo['Name']) : '';
				$data[$key] = $val;
			}
			
			return $data;
		}
		
		function getStoreMerchantRelByStoreId($storeid = '') {
			$data = array();
			if (empty($storeid) || !is_numeric($storeid)) return $data;
			$sql = "select * from `store_merchant_relationship` where `StoreID`={$storeid}";
			
			$data = $this->objMysql->getRows($sql);
			
			return $data;
		}
		
		function getStoreMerchantRelByStoreIds($storeid = array()) {
			$data = array();
			if (!count($storeid)) return $data;
			$sql = "select * from `store_merchant_relationship` where `StoreID` IN ('" . implode("','", $storeid) . "')";
			
			$data = $this->objMysql->getRows($sql);
			
			return $data;
		}
		
		function insertStoreAndRel($originalStoreId, $baseInfo = array(), $SMRelInfo = array(), $SCRelInfo = array()) {
			$result = array('status' => true, 'newstoreid' => '', 'msg' => '');
			$baseFilterFields = array('ID', 'HasAffiliate');
			
			foreach ($SMRelInfo as $val) {
				$_sql = "select * from `store_merchant_relationship` where `StoreID`={$originalStoreId} and `SiteName`='" . addslashes($val['SiteName']) . "' and `MerchantID`='" . addslashes($val['MerchantID']) . "'";
				$checkInfo = $this->objMysql->getRows($_sql);
				if (empty($checkInfo)) {
					$result['status'] = false;
					$result['msg'] = 'Other in operation,refresh later';
				    return $result;
				}
			}
			
			$fields = '';
			$values = '';
			$sql = "insert into `store` ";
			$baseInfo['AddTime'] = date('Y-m-d H:i:s');
			foreach ($baseInfo as $k => $v) {
				if (in_array($k, $baseFilterFields)) continue;
				$fields .= "`" . $k . "`, ";
				$values .= "'" . addslashes($v) . "', ";
			}
			$fields = preg_replace("|, $|i", '', $fields);
			$values = preg_replace("|, $|i", '', $values);
			$sqlQuery = $sql . '(' . $fields . ') values (' . $values . ');';
			
			if (!$res = $this->objMysql->query($sqlQuery)) {
				$result['status'] = false;
				return $result;
			}
			
			$newStoreId = $this->objMysql->getLastInsertId();
			
			
			$sqlStoreRelationship = "update `store_merchant_relationship` set `StoreID`={$newStoreId} ";
			$SMConditions = "where ";
			
			foreach ($SMRelInfo as $row) {
				$SMConditions .= "(`SiteName`='" . addslashes($row['SiteName']) . "' and `MerchantID`='" . addslashes($row['MerchantID']) . "') or ";
			}
			
			$SMConditions = preg_replace("|or $|i", '', $SMConditions);
			$sqlQuery = $sqlStoreRelationship . $SMConditions;
			if (!$res = $this->objMysql->query($sqlQuery)) {
				$result['status'] = false;
				return $result;
			}
			
			
			if (!empty($SCRelInfo)) {
				$sqlStoreCompetitorRel = "insert into `store_competitor_relationship` (`StoreID`, `CompetitorId`, `Url`, `Purpose`) values ";
				$SCValues = '';
				
				foreach ($SCRelInfo as $row) {
					$SCValues .= "({$newStoreId}, '" . addslashes($row['CompetitorId']) . "', '" . addslashes($row['Url']) . "', '" . addslashes($row['Purpose']) . "'), ";
				}
				
				$SCValues = preg_replace("|, $|i", '', $SCValues);
				$sqlQuery = $sqlStoreCompetitorRel . $SCValues;
				if (!$res = $this->objMysql->query($sqlQuery)) {
					$result['status'] = false;
					return $result;
				}
			}
			
			$result['newstoreid'] = $newStoreId;
			return $result;
		}
		
		function storeInitialize($storeid, $newRel, $delRel) {
			global $databaseInfo;
			
			$result = array('status' => true, 'msg' => '');
			if (empty($newRel)) {
				$result['status'] = false;
				return $result;
			}
			
			$_sql = "select `PSInitialized` from `store` where `ID`={$storeid}";
			$checkInfo = $this->objMysql->getRow($this->objMysql->query($_sql));
			if (isset($checkInfo['PSInitialized']) && $checkInfo['PSInitialized'] == 'YES') {
				$result['status'] = false;
				$result['msg'] = 'The store initialized!';
				return $result;
			}
			
			$values = '';
			$sql = "insert ignore into `program_store_relationship` (`ProgramId`, `StoreId`, `AffiliateDefaultUrl`, `DeepUrlTemplate`, `Order`, `Status`, `LastUpdateTime`, `AddTime`) values ";
			foreach ($newRel as $val) {
				$values .= "(
					'" . addslashes($val['programid']) . "', 
					'" . addslashes($storeid) . "', 
					'" . (isset($val['affdefaulturl']) ? addslashes($val['affdefaulturl']) : '') . "', 
					'" . (isset($val['deepurl']) ? addslashes($val['deepurl']) : '') . "', 
					'" . (isset($val['order']) ? addslashes($val['order']) : 0) . "', 
					'" . (isset($val['status']) ? addslashes($val['status']) : 'Inactive') . "', 
					'" . date('Y-m-d H:i:s') . "',
					'" . date('Y-m-d H:i:s') . "'
				), ";
			}
			$values = preg_replace("|, $|i", '', $values);
			$sqlQuery = $sql . $values;
			
			$this->objMysql->query("delete from `program_store_relationship` where `StoreId`={$storeid}");
			
			if (!$res = $this->objMysql->query($sqlQuery)) {
				$result['status'] = false;
				return $result;
			}
			
			if (!$this->setStoreProgramRelToMer(array($storeid))) {
				$result['status'] = false;
				return $result;
			}
			
			$_sql = "select count(*) as cnt from `program_store_relationship` where `StoreId`={$storeid} and `Status`='Active'";
			$_info = $this->objMysql->getRow($this->objMysql->query($_sql));
			if ($_info['cnt'] > 0) $this->objMysql->query("update `store` set `PSInitialized`='YES', `HasAffiliate`='YES' where `ID`={$storeid}");
			else $this->objMysql->query("update `store` set `PSInitialized`='YES', `HasAffiliate`='NO' where `ID`={$storeid}");
			
			return $result;
		}
		
		function getProgramsByKw($kw) {
			$data = array();
			$sql = '';
			if (trim($kw) == '') return $data;
		    
			preg_match('/[^\d]+/', trim($kw), $matches);
			if (!empty($matches)) {
				$sql = "select `ID`, `Name`, `AffId`, `IdInAff` from `program` where `Name` like '%". trim($kw) ."%' ";
			} else {
				$sql = "select `ID`, `Name`, `AffId`, `IdInAff` from `program` where (`ID` like '%". trim($kw) ."%' or `Name` like '%". trim($kw) ."%') ";
			}
			
			$data = $this->objMysql->getRows($sql);
			
			foreach ($data as $key => $val) {
				$sql = "select `Name` from `wf_aff` where `ID`={$val['AffId']}";
				$affInfo = $this->objMysql->getRow($this->objMysql->query($sql));
				$data[$key]['affname'] = isset($affInfo['Name']) ? trim($affInfo['Name']): '';
			}
			
			return $data;
		}
		
		function newAddPS($storeid, $psinfo = array(), $merchantinfo = array()) {
			global $databaseInfo;
			
			$this->objMysql->query("update `store` set `PSInitialized`='YES' where `ID`={$storeid}");
			if (empty($psinfo)) return true;
			
			$values = '';
			$sql = "insert into `program_store_relationship` (`ProgramId`, `StoreId`, `AffiliateDefaultUrl`, `DeepUrlTemplate`, `Order`, `LastUpdateTime`, `AddTime`) values ";
			foreach ($psinfo as $val) {
				$values .= "(
					'" . addslashes($val['programid']) . "', 
					'" . addslashes($storeid) . "', 
					'" . (isset($val['affurldefault']) ? addslashes($val['affurldefault']) : '') . "', 
					'" . (isset($val['deepurltemplate']) ? addslashes($val['deepurltemplate']) : '') . "', 
					'" . (isset($val['order']) ? addslashes($val['order']) : 0) . "', 
					'" . date('Y-m-d H:i:s') . "',
					'" . date('Y-m-d H:i:s') . "'
				), ";
			}
			$values = preg_replace("|, $|i", '', $values);
			$sqlQuery = $sql . $values;
			
			if (!$this->objMysql->query($sqlQuery)) return false;
			
			
			$this->setStoreProgramRelToMer(array($storeid));

			$_sql = "select count(*) as cnt from `program_store_relationship` where `StoreId`={$storeid} and `Status`='Active'";
			$_info = $this->objMysql->getRow($this->objMysql->query($_sql));
			if ($_info['cnt'] > 0) $this->objMysql->query("update `store` set `HasAffiliate`='YES' where `ID`={$storeid}");
			else $this->objMysql->query("update `store` set `HasAffiliate`='NO' where `ID`={$storeid}");
			
			return true;
		}
		
		function checkAffUrl($psInfo, $merchantInfo){
			
		}
		
		function setStoreProgramRelToMer($storeid_arr = array()){
			if(!count($storeid_arr)) return false;
			
			foreach($storeid_arr as $storeid){
				$this->correctStoreMerPSInfo($storeid);
			}
			
			return true;
			
			
			
			$store_mer_arr = array();
			$default_url_arr = array();
			$default_url_correspond_affid_arr = array();
			
			$deep_url_arr = array();
			$deep_url_correspond_affid_arr = array();
			
			$site_arr = array();
			$wf_mer_arr = array();
			
			$user = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : $_SERVER["REMOTE_USER"];
			
			$sql = "SELECT s.MerchantID, lower(s.SiteName) AS SiteName, p.ProgramId, p.StoreId, p.AffiliateDefaultUrl, p.DeepUrlTemplate, p.Order, p.Status, po.AffId, po.IdInAff FROM store_merchant_relationship AS s LEFT JOIN program_store_relationship AS p ON (p.StoreId = s.StoreID) INNER JOIN program as po ON (po.ID = p.ProgramId) WHERE s.StoreId IN ('" . implode("','", $storeid_arr) . "')";
			$store_mer_arr = $this->objMysql->getRows($sql);
			//print_r($store_mer_arr);
			
			$norel_storeid_arr = array();
			$norel_storeid_arr = array_flip($storeid_arr);
			
			$tmp_order = array();
			$tmp_order_2 = array();
			$wf_prgm = array();
			$wf_mer = array();
			foreach($store_mer_arr as $v){
				unset($norel_storeid_arr[$v["StoreId"]]);
				$site_arr[$v["SiteName"]][$v["StoreId"]][$v["MerchantID"]] = $v["MerchantID"];			
				if($v["Status"] == "Active"){
					//AffiliateDefaultUrl 取Order最小的
					if(!isset($tmp_order[$v["StoreId"]]["Order"]))$tmp_order[$v["StoreId"]]["Order"] = $v["Order"];
					if($tmp_order[$v["StoreId"]]["Order"] >= $v["Order"]){
						$tmp_order[$v["StoreId"]]["Order"] = $v["Order"];
						$default_url_arr[$v["StoreId"]]["url"] = $v["AffiliateDefaultUrl"];
						$default_url_correspond_affid_arr[$v["StoreId"]]["affid"] = $v["AffId"];
						$default_url_correspond_affid_arr[$v["StoreId"]]["programid"] = $v["ProgramId"];
						$default_url_correspond_affid_arr[$v["StoreId"]]["idinaff"] = $v["IdInAff"];
					}
					
					//AffiliateDefaultUrl 取Order最小且不为空的
					if(!isset($tmp_order_2[$v["StoreId"]]["Order"]))$tmp_order_2[$v["StoreId"]]["Order"] = $v["Order"];					
					if(!empty($v["DeepUrlTemplate"])){
						if($tmp_order_2[$v["StoreId"]]["Order"] >= $v["Order"]){					
							$tmp_order_2[$v["StoreId"]]["Order"] = $v["Order"];
							$deep_url_arr[$v["StoreId"]]["url"] = $v["DeepUrlTemplate"];
							$deep_url_correspond_affid_arr[$v["StoreId"]]["affid"] = $v["AffId"];
							$deep_url_correspond_affid_arr[$v["StoreId"]]["programid"] = $v["ProgramId"];
							$deep_url_correspond_affid_arr[$v["StoreId"]]["idinaff"] = $v["IdInAff"];
						}
					}
					
					$wf_sql = "replace into `wf_mer_in_aff` (`ProgramId`, `AffID`, `MerID`, `MerIDinAff`, `IsUsing`, `MerDeepUrlTemplate`, `MerDefaultAffUrl`, `OrderByNum`, `LastUpdateLink`) values (
								'" . addslashes($v['ProgramId']) . "',
								'" . addslashes($v['AffId']) . "',
								'" . addslashes($v['MerchantID']) . "',
								'" . addslashes($v['IdInAff']) . "',
								'1',
								'" . addslashes($v['DeepUrlTemplate']) . "',
								'" . addslashes($v['AffiliateDefaultUrl']) . "',
								'" . addslashes($v['Order']) . "',
								'" . date("Y-m-d H:i:s") . "'
							)";
					$wf_mer_arr[$v["SiteName"]][] = $wf_sql;
					
					$wf_prgm[$v["SiteName"]][$v['ProgramId']] = $v['ProgramId'];
					$wf_mer[$v["SiteName"]][$v['MerchantID']] = $v['MerchantID'];
				}
				/*else{
					$wf_sql = "delete from `wf_mer_in_aff` where `ProgramId` = '" . addslashes($v['ProgramId']) . "' and `AffID` = '" . addslashes($v['AffID']) . "' and `MerID` = '" . addslashes($v['MerID']) . "' ";
				}*/			
			}		
			
			$objTask = new Task();
			foreach($site_arr as $site => $v){
				$objMysqlSite = $objTask->getSiteMysqlObj($site);
				$objMerchant = new NormalMerchant($objMysqlSite);
				
				if(count($wf_prgm[$site]) && is_array($wf_prgm[$site]) && count($wf_mer[$site]) && is_array($wf_mer[$site])){
					$sql = "delete FROM wf_mer_in_aff where `MerID` IN ('" . implode("','", array_keys($wf_mer[$site])) . "') AND `ProgramId` NOT IN ('" . implode("','", array_keys($wf_prgm[$site])) . "')";
					$objMysqlSite->query($sql);
				}
				
				if(is_array($wf_mer_arr[$site]) && count($wf_mer_arr[$site])){
					foreach($wf_mer_arr[$site] as $sql){
						$objMysqlSite->query($sql);
					}
				}
				
				foreach($v as $storeid => $vv){
					$Dsturl = isset($default_url_arr[$storeid]["url"]) ? $default_url_arr[$storeid]["url"] : "";
					$CustomLink = isset($deep_url_arr[$storeid]["url"]) ? $deep_url_arr[$storeid]["url"] : "";			
					$defaultAffiliate = "";
					$defaultProgram = "";
					$defaultIdInAff = "";
					if(!empty($Dsturl)){ 
						$defaultAffiliate = $default_url_correspond_affid_arr[$storeid]["affid"];
						$defaultProgram = $default_url_correspond_affid_arr[$storeid]["programid"];
						$defaultIdInAff = $default_url_correspond_affid_arr[$storeid]["idinaff"];
					}
					
					$deepUrlTemplateAffiliate = "";
					$deepUrlTemplateProgram = "";
					$deepUrlTemplateIdInAff = "";
					if(!empty($CustomLink)){
						 $deepUrlTemplateAffiliate =  $deep_url_correspond_affid_arr[$storeid]["affid"];	
						 $deepUrlTemplateProgram =  $deep_url_correspond_affid_arr[$storeid]["programid"];	
						 $deepUrlTemplateIdInAff =  $deep_url_correspond_affid_arr[$storeid]["idinaff"];	
					}
					
					$_sql = "select m.`ID`, m.`DstUrl`, ma.`CustomLink`, m.HasAffiliate from `normalmerchant` m left join `normalmerchant_addinfo` ma on m.`ID`=ma.`ID` where m.`ID` IN (" . implode(",", $vv) . ")";
					$merchantInfoOld = $objMysqlSite->getRows($_sql, 'ID');
					
					$sql = "UPDATE normalmerchant SET Dsturl = '" . addslashes($Dsturl) . "' WHERE ID IN (" . implode(",", $vv) . ")";
					$objMysqlSite->query($sql);
					
					$sql = "UPDATE normalmerchant_addinfo 
								SET CustomLink = '" . addslashes($CustomLink) . "', 
									DefaultAffiliate = '".intval($defaultAffiliate)."', 
									DefaultProgram = '".intval($defaultProgram)."',
									DefaultIdInAff = '". addslashes($defaultIdInAff) . "',
									DeepUrlTemplateAffiliate = '" . intval($deepUrlTemplateAffiliate) . "', 
									DeepUrlTemplateProgram = '" . intval($deepUrlTemplateProgram) . "',
									DeepUrlTemplateIdInAff = '" . addslashes($deepUrlTemplateIdInAff) . "' 
								WHERE ID IN (" . implode(",", $vv) . ")";
					$objMysqlSite->query($sql);
					
					//check HasAffiliate
					$tmp_arr = array();
					$hasAffMer = array();
					$notAffMer = $vv;
					$sql = "SELECT count(*) AS count, MerID FROM wf_mer_in_aff WHERE MerID IN (" . implode(",", $vv) . ") AND IsUsing = 1 GROUP BY MerID";
					$tmp_arr = $objMysqlSite->getRows($sql, "MerID");
					foreach($tmp_arr as $mid => $val){
						$hasAffMer[$mid] = $mid;
						unset($notAffMer[$mid]);
					}
					if(count($hasAffMer)){
						$sql = "UPDATE normalmerchant SET HasAffiliate = 'YES' WHERE ID IN (" . implode(",", $hasAffMer) . ")";
						$objMysqlSite->query($sql);
					}
					if(count($notAffMer)){
						$sql = "UPDATE normalmerchant SET HasAffiliate = 'NO' WHERE ID IN (" . implode(",", $notAffMer) . ")";
						$objMysqlSite->query($sql);
					}
					//For change log && mail for Merchant Affiliate Program Status Change
					$_sql = "select m.`ID`, m.`Name`, m.`DstUrl`, ma.`CustomLink`, m.HasAffiliate from `normalmerchant` m left join `normalmerchant_addinfo` ma on m.`ID`=ma.`ID` where m.`ID` IN (" . implode(",", $vv) . ")";
					$merchantInfoNew = $objMysqlSite->getRows($_sql, 'ID');
					/*$mailMerchantIds = "";
					$content = "";*/
					foreach ($merchantInfoNew as $merid => $_merinfo) {
						if (!isset($merchantInfoOld[$merid])) continue;
						$objMerchant->merchantFieldsChangeLog("normalmerchant", $merchantInfoOld[$merid], $merchantInfoNew[$merid], $user);
						$objMerchant->merchantFieldsChangeLog("normalmerchant_addinfo", $merchantInfoOld[$merid], $merchantInfoNew[$merid], $user);
						/*if($merchantInfoOld[$merid]["HasAffiliate"] != $merchantInfoNew[$merid]["HasAffiliate"]){
							if($content == ""){
								$content = "$site - Merchant {$_merinfo["Name"]}($merid)'s Affiliate Program status has been changed: from {$merchantInfoOld[$merid]["HasAffiliate"]} to {$merchantInfoNew[$merid]["HasAffiliate"]}.";
							}else{
								$content = $content . ";<br/>\n$site - Merchant {$_merinfo["Name"]}($merid)'s Affiliate Program status has been changed: from {$merchantInfoOld[$merid]["HasAffiliate"]} to {$merchantInfoNew[$merid]["HasAffiliate"]}.";
							}
						}*/
					}
					/*
					$subject = "Merchant Affiliate Program Status Change Notification";
					include_once(INCLUDE_ROOT . "lib/Class.Sendmail.php");
					$mailObj = new Sendmail();
					if($site == "csde"){
						$mailObj->sendMailByType("affchange_de", $subject, $content);
					}else{
						$mailObj->sendMailByType("affchange", $subject, $content);
					}*/
					
					
					$this->objMysql->query("update `store` set `AffiliateDefaultUrl`='" . addslashes($Dsturl) . "', DeepUrlTemplate = '" . addslashes($CustomLink) . "' where `ID`={$storeid}");
					
					$this->objMysql->query("update `store` set `PSInitialized`='YES' where `ID`={$storeid}");
				}
			}
			
			//set no s-m rel store
			if(count($norel_storeid_arr)){
				$sql = "SELECT p.ProgramId, p.StoreId, p.AffiliateDefaultUrl, p.DeepUrlTemplate, p.Order, p.Status, po.AffId, po.IdInAff FROM program_store_relationship AS p INNER JOIN program as po ON (po.ID = p.ProgramId) WHERE p.StoreId IN ('" . implode("','", $norel_storeid_arr) . "')";
				$store_mer_arr = $this->objMysql->getRows($sql);
				
				$default_url_arr = array();
				$tmp_order = array();
				$tmp_order_2 = array();
				foreach($store_mer_arr as $v){
					unset($norel_storeid_arr[$v["StoreId"]]);
					if($v["Status"] == "Active"){
						//AffiliateDefaultUrl 取Order最小的
						if(!isset($tmp_order[$v["StoreId"]]["Order"]))$tmp_order[$v["StoreId"]]["Order"] = $v["Order"];
						if($tmp_order[$v["StoreId"]]["Order"] >= $v["Order"]){
							$tmp_order[$v["StoreId"]]["Order"] = $v["Order"];
							$default_url_arr[$v["StoreId"]]["AffiliateDefaultUrl"] = $v["AffiliateDefaultUrl"];						
						}
						
						//AffiliateDefaultUrl 取Order最小且不为空的
						if(!isset($tmp_order_2[$v["StoreId"]]["Order"]))$tmp_order_2[$v["StoreId"]]["Order"] = $v["Order"];					
						if(!empty($v["DeepUrlTemplate"])){
							if($tmp_order_2[$v["StoreId"]]["Order"] >= $v["Order"]){					
								$tmp_order_2[$v["StoreId"]]["Order"] = $v["Order"];
								$default_url_arr[$v["StoreId"]]["DeepUrlTemplate"] = $v["DeepUrlTemplate"];
							}
						}
									
					}						
				}
				
				foreach($default_url_arr as $storeid => $val){
					$Dsturl = isset($val['AffiliateDefaultUrl']) ? $val['AffiliateDefaultUrl'] : '';
					$CustomLink = isset($val['DeepUrlTemplate']) ? $val['DeepUrlTemplate'] : '';
					
					$this->objMysql->query("update `store` set `AffiliateDefaultUrl`='" . addslashes($Dsturl) . "', DeepUrlTemplate = '" . addslashes($CustomLink) . "' where `ID`={$storeid}");
				}
				
			}
			
			//check none aff store
			if(count($storeid_arr)){
				$non_aff_mer = array();
				$non_aff_store = array();				
				$sql = "SELECT LOWER(sitename) AS sitename, storeid, merchantid FROM store_merchant_relationship 
WHERE storeid NOT IN (SELECT storeid FROM program_store_relationship WHERE STATUS = 'active') AND storeid IN ('" . implode("','", $storeid_arr) . "')";
				$tmp_arr = $this->objMysql->getRows($sql);
				
				if($tmp_arr){
					foreach($tmp_arr as $v){
						$non_aff_mer[$v["sitename"]][] = $v["merchantid"];
						$non_aff_store[$v["storeid"]] = $v["storeid"];
					}
					
					foreach($non_aff_mer as $site => $merchantIds){
						$objMysqlSite = $objTask->getSiteMysqlObj($site);
						$objMerchant = new NormalMerchant($objMysqlSite);
						
						$sql = "delete FROM wf_mer_in_aff where `MerID` IN ('" . implode("','", $merchantIds) . "')";
						$objMysqlSite->query($sql);
						
						$sql = "UPDATE normalmerchant SET HasAffiliate = 'NO', Dsturl = '' WHERE ID IN (" . implode(",", $merchantIds) . ")";
						$objMysqlSite->query($sql);
						
						$sql = "UPDATE normalmerchant_addinfo 
									SET CustomLink = '', 
										DefaultAffiliate = '', 
										DefaultProgram = '',
										DefaultIdInAff = '',
										DeepUrlTemplateAffiliate = '', 
										DeepUrlTemplateProgram = '',
										DeepUrlTemplateIdInAff = '' 
									WHERE ID IN (" . implode(",", $merchantIds) . ")";
						$objMysqlSite->query($sql);					
					}
					
					$this->objMysql->query("update `store` set `hasaffiliate` = 'NO', `AffiliateDefaultUrl`='', DeepUrlTemplate = '', `PSInitialized`='YES' where `ID` IN ('" . implode("','", array_keys($non_aff_store)) . "')");
				}				
			}
			return true;
		}
		
		function dropPSRel($storeid, $programid, $affid){
			$del_wf = array();
			$sql = "SELECT MerchantID, lower(SiteName) AS SiteName FROM store_merchant_relationship WHERE StoreID = {$storeid}";
			$oldstore_mer_arr = array();
			$oldstore_mer_arr = $this->objMysql->getRows($sql);
			foreach($oldstore_mer_arr as $v){
				$sql = "DELETE FROM merchant_program WHERE ProgramId = {$programid} AND Site = '{$v["SiteName"]}' AND MerchantId = '{$v["MerchantID"]}'";
				$this->objMysql->query($sql);
				$del_wf[$v["SiteName"]][] = $v["MerchantID"];
			}
			$user = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : $_SERVER["REMOTE_USER"];
			$objTask = new Task();
			$content = "";
			foreach($del_wf as $site => $v){
				$hasAffMerIds = "";
				$noAffMerIds = "";
				$objMysqlSite = $objTask->getSiteMysqlObj($site);	
				$objMerchant = new NormalMerchant($objMysqlSite);
						
				$sql = "DELETE FROM wf_mer_in_aff WHERE AffID = {$affid} AND MerID IN (". implode(",", $v) . ")";
				$objMysqlSite->query($sql);
				
				//check HasAffiliate
				$tmp_arr = array();
				$hasAffMer = array();
				$notAffMer = $v;
				$sql = "SELECT count(*) AS count, MerID FROM wf_mer_in_aff WHERE MerID IN (" . implode(",", $v) . ") AND IsUsing = 1 GROUP BY MerID";
				$tmp_arr = $objMysqlSite->getRows($sql, "MerID");
				foreach($tmp_arr as $mid => $val){
					$hasAffMer[$mid] = $mid;
					unset($notAffMer[$mid]);
				}
				$content = "";
				if(count($hasAffMer)){
					//For merchant log
					$_sql = "select m.`ID`, m.`DstUrl`, ma.`CustomLink`, m.HasAffiliate from `normalmerchant` m left join `normalmerchant_addinfo` ma on m.`ID`=ma.`ID` where m.`ID` IN (" . implode(",", $hasAffMer) . ")";
					$merchantInfoOld = $objMysqlSite->getRows($_sql, 'ID');
					
					$sql = "UPDATE normalmerchant SET HasAffiliate = 'YES' WHERE ID IN (" . implode(",", $hasAffMer) . ")";
					$objMysqlSite->query($sql);
					
					$_sql = "select m.`ID`, m.`Name`, m.`DstUrl`, ma.`CustomLink`, m.HasAffiliate from `normalmerchant` m left join `normalmerchant_addinfo` ma on m.`ID`=ma.`ID` where m.`ID` IN (" . implode(",", $hasAffMer) . ")";
					$merchantInfoNew = $objMysqlSite->getRows($_sql, 'ID');
					foreach($hasAffMer as $merid){
						$objMerchant->merchantFieldsChangeLog("normalmerchant", $merchantInfoOld[$merid], $merchantInfoNew[$merid], $user);
						if($merchantInfoOld[$merid]["HasAffiliate"] == "NO"){
							if($hasAffMerIds == ""){
								$hasAffMerIds = $merchantInfoNew[$merid]["Name"]."(".$merid.")";
							}else{
								$hasAffMerIds .= "," . $merchantInfoNew[$merid]["Name"]."(".$merid.")";
							}
						}
					}
				}
				if(count($notAffMer)){
					$_sql = "select m.`ID`, m.`DstUrl`, ma.`CustomLink`, m.HasAffiliate from `normalmerchant` m left join `normalmerchant_addinfo` ma on m.`ID`=ma.`ID` where m.`ID` IN (" . implode(",", $notAffMer) . ")";
					$merchantInfoOld = $objMysqlSite->getRows($_sql, 'ID');
					
					$sql = "UPDATE normalmerchant SET HasAffiliate = 'NO' WHERE ID IN (" . implode(",", $notAffMer) . ")";
					$objMysqlSite->query($sql);
					
					$_sql = "select m.`ID`, m.`Name`, m.`DstUrl`, ma.`CustomLink`, m.HasAffiliate from `normalmerchant` m left join `normalmerchant_addinfo` ma on m.`ID`=ma.`ID` where m.`ID` IN (" . implode(",", $notAffMer) . ")";
					$merchantInfoNew = $objMysqlSite->getRows($_sql, 'ID');
					foreach($notAffMer as $merid){
						$objMerchant->merchantFieldsChangeLog("normalmerchant", $merchantInfoOld[$merid], $merchantInfoNew[$merid], $user);
						if($merchantInfoOld[$merid]["HasAffiliate"] == "NO"){
							if($noAffMerIds == ""){
								$noAffMerIds = $merchantInfoNew[$merid]["Name"]."(".$merid.")";
							}else{
								$noAffMerIds .= "," . $merchantInfoNew[$merid]["Name"]."(".$merid.")";
							}
						}
					}
				}
				/*if($hasAffMerIds != ""){
					$content = "$site - Merchant $hasAffMerIds's Affiliate Program status has been changed: from NO to YES";
				}
				if($noAffMerIds != ""){
					$content = $content . "<br/>\n$site - Merchant $noAffMerIds's Affiliate Program status has been changed: from YES to NO";
				}
			
				$subject = "Merchant Affiliate Program Status Change Notification";
				include_once(INCLUDE_ROOT . "lib/Class.Sendmail.php"); 
				$mailObj = new Sendmail();				
				if($site == "csde"){
					$mailObj->sendMailByType("affchange_de", $subject, $content);
				}else{
					$mailObj->sendMailByType("affchange", $subject, $content);
				}*/
			}
			$_sql = "select count(*) as cnt from `program_store_relationship` where `StoreId`={$storeid} and `Status`='Active'";
			$_info = $this->objMysql->getRow($this->objMysql->query($_sql));
			if ($_info['cnt'] > 0) $this->objMysql->query("update `store` set `HasAffiliate`='YES' where `ID`={$storeid}");
			else $this->objMysql->query("update `store` set `HasAffiliate`='NO' where `ID`={$storeid}");
		}
		
		function editPSRel($storeid, $programid, $affid, $idinaff, $Status, $DeepUrlTemplate, $AffiliateDefaultUrl, $IsUsing){
			$sql = "SELECT MerchantID, lower(SiteName) AS SiteName FROM store_merchant_relationship WHERE StoreID = {$storeid}";
			$store_mer_arr = array();
			$store_mer_arr = $this->objMysql->getRows($sql);
			$sql_arr = array();
			$update_arr = array();			
			foreach($store_mer_arr as $v){					
				$sql_arr[$v["SiteName"]][] = "UPDATE wf_mer_in_aff SET MerDeepUrlTemplate = '{$DeepUrlTemplate}', MerDefaultAffUrl = '{$AffiliateDefaultUrl}', IsUsing = '{$IsUsing}', LastUpdateLink = '" .date("Y-m-d H:i:s"). "' WHERE AffID = {$affid} AND MerID = {$v["MerchantID"]}";			
				/*$sql_arr[$v["SiteName"]][] = "UPDATE normalmerchant SET Dsturl = '{$AffiliateDefaultUrl}' WHERE ID = {$v["MerchantID"]}";
				$sql_arr[$v["SiteName"]][] = "UPDATE normalmerchant_addinfo SET CustomLink = '{$DeepUrlTemplate}' WHERE ID = {$v["MerchantID"]}";*/
				
				$update_arr[] = "(AffId = '{$affid}' AND AffMerchantId = '" . addslashes($idinaff) . "' AND ProgramId = '{$programid}' AND Site = '{$v["SiteName"]}' AND MerchantId = '{$v["MerchantID"]}')";
			}
			
			if(count($update_arr)){
				$sql = "UPDATE merchant_program SET Status = '{$Status}' WHERE " . implode(" OR ", $update_arr);
				$this->objMysql->query($sql);			
			}
			
			$objTask = new Task();
			foreach($sql_arr as $site => $v){
				$objMysqlSite = $objTask->getSiteMysqlObj($site);		
				foreach($v as $sql){
					$objMysqlSite->query($sql);				
				}
			}
			
			$_sql = "select count(*) as cnt from `program_store_relationship` where `StoreId`={$storeid} and `Status`='Active'";
			$_info = $this->objMysql->getRow($this->objMysql->query($_sql));
			if ($_info['cnt'] > 0) $this->objMysql->query("update `store` set `HasAffiliate`='YES' where `ID`={$storeid}");
			else $this->objMysql->query("update `store` set `HasAffiliate`='NO' where `ID`={$storeid}");
		}
		
		function newPSRel($storeid, $programid, $affid, $idinaff, $Status, $DeepUrlTemplate, $AffiliateDefaultUrl, $IsUsing){
			$insert_arr = array(); 
			$insert_site = array();
			$mer_q = array();
			$mer_id_arr = array();
			
			$sql = "SELECT MerchantID, lower(SiteName) AS SiteName FROM store_merchant_relationship WHERE StoreID = {$storeid}";
			$store_mer_arr = array();
			$store_mer_arr = $this->objMysql->getRows($sql);
			
			foreach($store_mer_arr as $v){
				$insert_arr[] = "('{$affid}', '" . addslashes($idinaff) . "', '{$programid}', '{$v["SiteName"]}', '{$v["MerchantID"]}', '', '{$Status}', '" .date("Y-m-d H:i:s"). "')";
				
				$insert_site[$v["SiteName"]][] = "('{$affid}', '" . addslashes($idinaff) . "', '{$programid}', '{$v["MerchantID"]}', '{$IsUsing}', '{$DeepUrlTemplate}', '{$AffiliateDefaultUrl}')";
				
				$mer_q[$v["SiteName"]][] = $v["MerchantID"];
			}
			if(count($insert_arr)){
				$sql = "INSERT IGNORE INTO merchant_program(AffId,AffMerchantId,ProgramId,Site,MerchantId,MerchantName,Status,AddTime) VALUES" . implode(",", $insert_arr);
				$this->objMysql->query($sql);			
			}
			
			$objTask = new Task();
			foreach($insert_site as $site => $v){
				$objMysqlSite = $objTask->getSiteMysqlObj($site);
				
	//			$sql = "INSERT IGNORE INTO wf_mer_in_aff(AffID,MerIDinAff,ProgramId,MerID,IsUsing,MerDeepUrlTemplate,MerDefaultAffUrl) VALUES" . implode(",", $v);
	//			$objMysqlSite->query($sql);			
				
				$sql = "SELECT ID, Name FROM normalmerchant WHERE ID IN (" . implode(",", $mer_q[$site]) . ")";
				$mer_id_arr[$site] = $objMysqlSite->getRows($sql);
			}
			
			foreach($mer_id_arr as $site => $v){
				foreach($v as $vv){
					$sql = "UPDATE merchant_program SET MerchantName = '". addslashes($vv["Name"]) ."' WHERE MerchantId = {$vv["ID"]} AND Site = '$site'";
					$this->objMysql->query($sql);
				}
			}
			
			$_sql = "select count(*) as cnt from `program_store_relationship` where `StoreId`={$storeid} and `Status`='Active'";
			$_info = $this->objMysql->getRow($this->objMysql->query($_sql));
			if ($_info['cnt'] > 0) $this->objMysql->query("update `store` set `HasAffiliate`='YES' where `ID`={$storeid}");
			else $this->objMysql->query("update `store` set `HasAffiliate`='NO' where `ID`={$storeid}");
			
		}
		
		function getPsRelationship($programId, $order = "NO"){
			$sql = "SELECT * FROM program_store_relationship WHERE ProgramId = '$programId'";
			if($order == "YES"){
				$sql .= " order by `Order`";
			}
			$rows = $this->objMysql->getRows($sql, "StoreId");
			return $rows;
		}
		function getSpRelationship($storeId, $order = "NO"){
			$sql = "SELECT * FROM program_store_relationship WHERE StoreId = '$storeId'";
			if($order == "YES"){
				$sql .= " order by `Order`";
			}
			$rows = $this->objMysql->getRows($sql, "ProgramId");
			return $rows;
		}
		function getActiveSPARelationship($storeId, $order = "NO"){
			$sql = "SELECT a.*, b.AffId FROM program_store_relationship a inner join program b on (a.ProgramId = b.ID and a.Status = 'Active') WHERE a.StoreId = '$storeId'";
			if($order == "YES"){
				$sql .= " order by `Order`";
			}
			$rows = $this->objMysql->getRows($sql, "ProgramId");
			return $rows;
		}
		
		function getActivePsRelationship($programId){
			$sql = "SELECT * FROM program_store_relationship WHERE ProgramId = '$programId' and Status = 'Active' order by Order";
			$rows = $this->objMysql->getRows($sql, "StoreId");
			return $rows;
		}
		
		function newProgramStoreRelAudit($storeId,$order,$editor,$reason,$oldorder=""){
			$sql = "INSERT INTO task_ps_order_audit(Storeid,OldProgramOrderList,ProgramOrderList,Editor,Reason,AddTime,LastUpdateTime,Status) VALUES('{$storeId}','{$oldorder}','{$order}','".addslashes($editor)."','".addslashes($reason)."','".date("Y-m-d H:i:s")."','".date("Y-m-d H:i:s")."', 'NEW')";
			$this->objMysql->query($sql);
			return 'succ';
		}
		
		
		function insertPsChangeLog($programId, $oldRelationship, $reasonArr = "", $source = ""){
			$newRelationship = $this->getPsRelationship($programId);
			
			//print_r($oldRelationship);
			//print_r($newRelationship);
			
			$logFields = array("Status" => "Status", "AffiliateDefaultUrl" => "AffiliateDefaultUrl", "DeepUrlTemplate" => "DeepUrlTemplate", "Order" => "Order");
			foreach ($newRelationship as $relation){
				$storeId = $relation["StoreId"];
				if(isset($oldRelationship[$storeId])){
					foreach ($logFields as $field){
						if(isset($relation[$field])){
							if($relation[$field] != $oldRelationship[$storeId][$field]){
								$reason = "";
								if($oldRelationship[$storeId]["Status"] != $relation["Status"]){
									$reason = $reasonArr[$storeId];
									/*if($relation["Status"] == 'Inactive' && $oldRelationship[$storeId]["Status"] == 'Active'){
										
										$checkSite = $this->checkStoreMerchantStatus($oldRelationship[$storeId]["StoreId"], $oldRelationship[$storeId]["ID"]);
										if( $checkSite !== false ){
											$this->createMerchantIssue($relation, $checkSite, "statuschange");
										}
									}*/
								}
								$this->insertPsLog($programId, $storeId, $field, $oldRelationship[$storeId][$field], $relation[$field], "update", $reason, $source, $field);
							}
						}
					}
					unset($newRelationship[$storeId]);
					unset($oldRelationship[$storeId]);
				}
			}
			foreach ($oldRelationship as $old){
				$oldValue = $old["AffiliateDefaultUrl"] . "||" . $old["DeepUrlTemplate"] . "||" . "" . "||" . $old["Status"];
				/*$checkSite = $this->checkStoreMerchantStatus($old["StoreId"], $old["ID"]);
				if( $checkSite !== false ){
					$this->createMerchantIssue($old, $checkSite, "delete");
				}*/
				$this->insertPsLog($programId, $old["StoreId"], "delete", $oldValue, "", "delete", $reasonArr[$old["StoreId"]], $source);
				
			}
			
			foreach ($newRelationship as $new){
				$newValue = $new["AffiliateDefaultUrl"] . "||" . $new["DeepUrlTemplate"] . "||" . $new["Order"] . "||" . $new["Status"];
				$this->insertPsLog($programId, $new["StoreId"], "add", "", $newValue, "add", "", $source);
			}
			return true;
		}
		
		function checkStoreMerchantStatus($sotreId, $psId){
			$sql = "SELECT * FROM store_merchant_relationship WHERE StoreID = '$sotreId'";
			$merchants = $this->objMysql->getRows($sql);
			if(count($merchants) == 0){
				return false;
			}
			$sql = "SELECT * FROM program_store_relationship WHERE StoreId = '$sotreId' AND `Status` = 'Active' AND ID != '$psId'";
			$otherAvtivePs = $this->objMysql->getRows($sql);
			if(count($otherAvtivePs) == 0){
				return false;
			}
			return $merchants[0];
		}
		
		function createMerchantIssue($psRelationship, $merchantInfo, $flag){
			$siteName = strtolower($merchantInfo["SiteName"]);
			$mid = $merchantInfo["MerchantID"];
			$date = date("Y-m-d H:i:s");
			$sql = "select * from site_editor_arbitrator where Site = '" . strtolower($siteName) . "' and Type = 'Handler' and IssueObject = 'merchant' limit 1";
			$resArr = $this->objMysql->getRows ($sql);
			
			$assignTo = $resArr[0]["Editor"];
			include_once(INCLUDE_ROOT . "lib/Class.TaskEmail.php");
			include_once(INCLUDE_ROOT . "lib/Class.MerchantIssueComment.php");
			
			$oTaskEmail = new TaskEmail();
			$objComment = new Comment();
			$objMysqlBase = $oTaskEmail->getSiteMysqlObj($siteName);
			
			$sql = "SELECT a.*, b.AllowNonAffPromo FROM normalmerchant a, normalmerchant_addinfo b WHERE a.ID = b.ID and a.ID = '$mid'";
			$merchant = $objMysqlBase->getRows($sql);
			$merchantName = $merchantInfo[0]["Name"];
			$AllowNonAffPromo = $merchantInfo[0]["AllowNonAffPromo"];
			if($AllowNonAffPromo == "NO"){
				return true;
			}
			$programId = $psRelationship["ProgramId"];
			$storeId = $psRelationship["StoreId"];
			$sql = "SELECT a.Name AS ProgramName, a.AffId AS AffId, b.Name AS AffName, a.IdInAff AS IdInAff FROM program a LEFT JOIN wf_aff b ON a.AffId = b.ID where a.ID = '$programId'";
			$programArr = $this->objMysql->getRows($sql);
			$comment = "";
			if(count($programArr) >= 0){
				$comment = "<span style='font-weight:bold;'>Offline Program:</span><br/>\n {$programArr[0]["AffName"]}({$programArr[0]["AffId"]}) | {$programArr[0]["ProgramName"]}({$programId})\n<br/>";	
			}
			
			$sql = "SELECT * FROM program_store_relationship WHERE StoreId = '$storeId' AND `Status` = 'Active'";
			$programArrTmp = $this->objMysql->getRows($sql);
			if(count($programArrTmp) >= 0){
				$comment .= "<span style='font-weight:bold;'>Available Program: </span><br/>";
				foreach ($programArrTmp as $program){
					$sql = "SELECT a.ID as ProgramId, a.Name AS ProgramName, a.AffId AS AffId, b.Name AS AffName, a.IdInAff AS IdInAff FROM program a LEFT JOIN wf_aff b ON a.AffId = b.ID where a.ID = '{$program["ProgramId"]}'";
					$programArr = $this->objMysql->getRows($sql);
					if(count($programArr) >= 0){
						$comment .= "{$programArr[0]["AffName"]}({$programArr[0]["AffId"]}) | {$programArr[0]["ProgramName"]}({$programArr[0]["ProgramId"]})\n<br/>";	
					}
				}	
			}
			$sql = "insert into merchant_issue(Status,IssueType, Site, MerchantID, MerchantName,AddUser,AssignTo,AddDate,LastUpdateDate)
						values('NEW', '16', '$siteName', '$mid', '" . addslashes($merchantName) . "', 'system', '$assignTo', '$date', '$date')";
			$this->objMysql->query($sql);
			$issueID = $this->objMysql->getLastInsertId();
			$res = $objComment->insertComment("issue", $issueID, "system", $comment, true, $mid);
			return true;
		}
		
		
		function insertPsLog($programId, $storeId, $field, $valueFrom, $valueTo, $type, $reason = "", $source = "", $field = ""){
			$sql = "select * from program AS p left join program_int AS pi on (p.ID = pi.ProgramId) where p.ID = {$programId}";
			$user = $_SERVER['PHP_AUTH_USER'] ? $_SERVER['PHP_AUTH_USER'] : $_SERVER["REMOTE_USER"];
			if(trim($user) == ""){
				$user = get_cookie_var("edit_user");;
			}
			$rows = $this->objMysql->getRows($sql);
			if(count($rows) == 0){
				return false;
			}
			$programInfo = $rows[0];
			if(intval($programInfo["RevenueOrder"]) < 1) $programInfo["RevenueOrder"] = 9999999;
			$sql = "select * from store where ID = '$storeId'";
			$rows = $this->objMysql->getRows($sql);
			if(count($rows) == 0){
				return false;
			}
			$storeInfo = $rows[0];
			$sql = "insert ignore into program_store_relationship_change_log (
				ProgramId, 
				ProgramName, 
				StoreId, 
				StoreName, 
				Source, 
				Reason, 
				Type,
				Status, 
				ValueFrom, 
				ValueTo, 
				Creator, 
				FieldName,
				ProgramOrder,
				AddTime) values(
				'$programId',
				'" . addslashes($programInfo["Name"]) . "',
				'$storeId',
				'" . addslashes($storeInfo["Name"]) . "',
				'" . addslashes($source) . "',
				'" . addslashes($reason) . "',
				'$type',
				'NEW',
				'" . addslashes($valueFrom) . "',
				'" . addslashes($valueTo) . "',
				'$user',
				'" . addslashes($field) . "',
				'" . intval($programInfo["RevenueOrder"]) . "',
				'" . date("Y-m-d H:i:s") . "'
				)";
			$this->objMysql->query($sql);
			return true;
		}
		
		function getAffIdFromProgram($programId){
			$sql = "select * from program where ID = '$programId'";
			$rows = $this->objMysql->getRows($sql);
			if(count($rows) > 0){
				return $rows[0]["AffId"];
			}else{
				return "";
			}
		}
		
		function setOfflineAffCouponAffUrl($psOld, $programId){
			$psNew = $this->getPsRelationship($programId);
			foreach ($psOld as $storeId => $psInfo) {
				if($psInfo["Status"] == "Active"){
					
					if(isset($psNew[$storeId])){
						if($psNew[$storeId]["Status"] == "Inactive"){
							
							//set inactive relationships
							$affId = $this->getAffIdFromProgram($psInfo["ProgramId"]);
							$this->clearCouponAffUrl($affId, $storeId);
							unset($psOld[$storeId]);
							unset($psNew[$storeId]);
						}
					}else{
						//delete ps relationships
						$affId = $this->getAffIdFromProgram($psInfo["ProgramId"]);
						$this->clearCouponAffUrl($affId, $storeId);
						unset($psOld[$storeId]);
					}
				}
			}
			return true;
		}
		
		function getAffInfo($affId){
			$sql = "select ID, AffiliateUrlKeywords, AffiliateUrlKeywords2 from wf_aff where ID = '$affId'";
			$rows = $this->objMysql->getRows($sql);
			if(count($rows) > 0){
				return $rows[0]; 
			}else{
				return array();
			}
		}
		function getMerchantIdsFromStoreId($storeId){
			$sql = "SELECT * FROM `store_merchant_relationship` WHERE StoreId = '$storeId'";
			$rows = $this->objMysql->getRows($sql);
			$merchantIds = array();
			foreach($rows as $val){
				$merchantIds[$val["MerchantID"]] = $val;
			}
			return $merchantIds; 
		}
		
		function clearCouponAffUrl($affId, $storeId){
			$where1 = "";
			$where2 = "";
			$affInfo = $this->getAffInfo($affId);
			$merchantIds = $this->getMerchantIdsFromStoreId($storeId);
			$keyword1 = trim($affInfo["AffiliateUrlKeywords"]);
			$keyword2 = trim($affInfo["AffiliateUrlKeywords2"]);
			$keyword1Arr = array();
			$keyword2Arr = array();
			
			if($keyword1 != ""){
				$keyword1Arr = explode("\n", $keyword1);
				foreach ($keyword1Arr as $k => $v){
					$keyword1Arr[$k] = trim($v);
				}
				$where1 = " and (AffUrl like '%" . implode("%' or AffUrl like '%", $keyword1Arr) . "%')";
			}
			if($keyword2 != ""){
				$keyword2Arr = explode("\n", $keyword2);
				foreach ($keyword2Arr as $k => $v){
					$keyword2Arr[$k] = trim($v);
				}
				$where2 = "(AffUrl like '%" . implode("%' or AffUrl like '%", $keyword2Arr) . "%')";
			}
			if($where2 != ""){
				$where1 .=" and " . $where2;
			}
			global $databaseInfo;
			foreach ($merchantIds as $merchantId => $v){
				
				$baseObjMysql = new Mysql($databaseInfo["INFO_" . strtoupper($v['SiteName']) . "_DB_NAME"], $databaseInfo["INFO_" . strtoupper($v['SiteName']) . "_DB_HOST"], $databaseInfo["INFO_" . strtoupper($v['SiteName']) . "_DB_USER"], $databaseInfo["INFO_" . strtoupper($v['SiteName']) . "_DB_PASS"]);
				$sql = "update normalcoupon set AffUrl = '',AffId = '', ProgramId = '', IdInAff = ''  WHERE MerchantID = '$merchantId' $where1";
				$baseObjMysql->query($sql);
				/*delete 2014-10-29*$sql = "update deal set AffUrl = '' WHERE MerchantID = '$merchantId' $where1";
				$baseObjMysql->query($sql);*/
			}
			return true;
		}
		
		function changeOrderClearCouponAff($spOld, $storeIdSource){
			$spNew = $this->getSpRelationship($storeIdSource, "YES");
			$oldActivePs = array();
			$newActivePs = array();
			foreach ($spOld as $storeId => $psInfo){
				if($psInfo["Status"] == "Active"){
					$oldActivePs = $psInfo;
					break;
				}
			}
			
			foreach ($spNew as $storeId => $psInfo){
				if($psInfo["Status"] == "Active"){
					$newActivePs = $psInfo;
					break;
				}
			}
			if($oldActivePs["ID"] == $newActivePs["ID"]){
				return true;
			}else{
				$affId = $this->getAffIdFromProgram($oldActivePs["ProgramId"]);
				$this->clearCouponAffUrl($affId, $storeIdSource);
			}
			return true;
		}
		function getPsRelationshipFromMerId($mid, $site = "NO"){
			$siteName = strtoupper($site);
			$sql = "select * from store_merchant_relationship where MerchantID = '$mid'";
			if($siteName != "NO"){
				$sql .= " and SiteName = '$siteName'";
			}
			$rows = $this->objMysql->getRows($sql);
			return $rows; 
		}
		
		function getProgramIdFromMidAndAffId($merchantid, $programAffId, $site){
			$site = strtoupper($site);
			$sql = "select * from store_merchant_relationship where MerchantID = '$merchantid' and SiteName = '$site'";
			$storeList = $this->objMysql->getRows($sql);
			$merProgram = array();
			foreach($storeList as $storeInfo){
				$sql = "select * from program_store_relationship a inner join program b on (a.ProgramId = b.ID and a.Status = 'Active' and a.StoreId = '{$storeInfo["StoreID"]}' AND AffId = '$programAffId') order by a.Order desc";
				$programList = $this->objMysql->getRows($sql);
				if(count($programList) > 0){
					return $programList[0]["ProgramId"];
				}
			}
			return "";
		}
		
		function getMerchantActiveAffiliateFromMerId($merId , $site = "NO"){
			$rowsPM = $this->getPsRelationshipFromMerId($merId, $site);
	
			$res = array();
			foreach ($rowsPM as $ps){
				$rowTmp = $this->getSpRelationship($ps["StoreID"]);
				foreach ($rowTmp as $sp){
					if(isset($res[$sp["ProgramId"]])){
						continue;
					}else{
						
						if($sp["Status"] == 'Active'){
							$res[$sp["ProgramId"]] = $sp;
						}
					}
				
				}
			}
			return $res;
		}
		function checkPMSameToPS($storeId = "", $merchantId = "", $site = ""){
			global $databaseInfo;
			if($storeId != ""){
				$sql = "SELECT * FROM store WHERE ID = '$storeId'";
				$storeRows = $this->objMysql->getRows($sql);
				$sql = "SELECT * FROM store_merchant_relationship WHERE StoreID = '$storeId'";
				$storeMerchantRows = $this->objMysql->getRows($sql);
				$sql = "select * from program_store_relationship where StoreId = '$storeId' and Status = 'Active'";
				$psRows = $this->objMysql->getRows($sql, "ProgramId");
				foreach($storeMerchantRows as $v){
					$baseObjMysql = new Mysql($databaseInfo["INFO_" . strtoupper($v['SiteName']) . "_DB_NAME"], $databaseInfo["INFO_" . strtoupper($v['SiteName']) . "_DB_HOST"], $databaseInfo["INFO_" . strtoupper($v['SiteName']) . "_DB_USER"], $databaseInfo["INFO_" . strtoupper($v['SiteName']) . "_DB_PASS"]);
					$sql = "SELECT * FROM wf_mer_in_aff  WHERE MerID = '{$v["MerchantID"]}' AND IsUsing = '1'";
					$merchantRows = $baseObjMysql->getRows($sql);
					foreach($merchantRows as $merRow){
						$programFromMer = $merRow["ProgramId"];
						if(!isset($psRows[$programFromMer])){
							return false;	
						}
					}
				}
				return true;
			}
			if($merchantId != "" && $site != ""){
				$v['SiteName'] = $site;
				$sql = "SELECT * FROM store_merchant_relationship WHERE MerchantID = '$merchantId' and SiteName = '" . strtoupper($site) . "'";
				$storeMerchantRows = $this->objMysql->getRows($sql);
				$baseObjMysql = new Mysql($databaseInfo["INFO_" . strtoupper($v['SiteName']) . "_DB_NAME"], $databaseInfo["INFO_" . strtoupper($v['SiteName']) . "_DB_HOST"], $databaseInfo["INFO_" . strtoupper($v['SiteName']) . "_DB_USER"], $databaseInfo["INFO_" . strtoupper($v['SiteName']) . "_DB_PASS"]);
				$sql = "SELECT * FROM wf_mer_in_aff  WHERE MerID = '{$merchantId}'";
				$merchantRows = $baseObjMysql->getRows($sql);
				$psIds = array();
				foreach($storeMerchantRows as $storeMerchantRow){
					$storeId = $storeMerchantRow["StoreID"];
					$sql = "select * from program_store_relationship where StoreId = '$storeId'";
					$psRows = $this->objMysql->getRows($sql, "ProgramId");
					foreach($psRows as $row){
						$psIds[$row["ProgramId"]] =$row["ProgramId"]; 
					}
				}
				
				foreach($merchantRows as $pmRow){
					if(!isset($psIds[$pmRow["ProgramId"]])){
						
						return false;
					}
				}
				return true;
			}
			return false;
		}//func checkPMSameToPS end
		
		function checkStoreDomain($url, $getRelated = false){
			$domain = getUrlDomain($url);
			if(!$domain){
				$domain = $url;
			}
			if($getRelated){
				$return_arr = array();
			}else{
				$return_arr = false;
			}
			if(!$domain) return $return_arr;
			$domain_arr = $this->getDomainArr($domain);
			
			if($domain){
				$sql = "select id, domain from store where domain = '".addslashes($domain)."'";
				$tmp_arr = array();
				$tmp_arr = $this->objMysql->getRows($sql);
				
				foreach($tmp_arr as $v){					
					if(!$getRelated){
						return true;
					}else{
						$return_arr[$v["id"]] = $v["domain"];
					}
				}
			}
			
			foreach($domain_arr as $v){
				if($v == "uk" || $v == "us" || $v == "au" || $v == "de" || $v == "co" || $v == "ca"){
					continue;
				}
				$sql = "select id, domain from store where Domain like '%" . addslashes($v["domain"]) . "%'";
				$tmp_arr = array();
				$tmp_arr = $this->objMysql->getRows($sql);
				foreach($tmp_arr as $v){
					$store_domain_arr = $this->getDomainArr($v["domain"]);
					$tmp_result = array();
					$tmp_result = array_intersect($domain_arr, $store_domain_arr);
					if(count($tmp_result)){
						if(!$getRelated){
							return true;
						}else{
							$return_arr[$v["id"]] = $v["domain"];
						}
					}	
				}				
			}
			
			/*if(count($domain_arr)){
				$sql = "select id, domain from store";
				$tmp_arr = array();
				$tmp_arr = $this->objMysql->getRows($sql);
				
				foreach($tmp_arr as $v){
					$store_domain_arr = $this->getDomainArr($v["domain"]);
					$tmp_result = array();
					$tmp_result = array_intersect($domain_arr, $store_domain_arr);
					if(count($tmp_result)){
						if(!$getRelated){
							return true;
						}else{
							$return_arr[$v["id"]] = $v["domain"];
						}
					}					
				}
			}*/
			
			return $return_arr;
		}
		
		function getDomainArr($domain){
			$domain = strtolower($domain);
			$domian_arr = array();
			$domian_arr = explode(".", $domain);
			$ignoreDomain = array ('ac', 'ad', 'ae', 'aero', 'af', 'ag', 'ai', 'al', 'am', 'an', 'ao', 'aq', 'ar', 'arpa', 'as', 'asia', 'at', 'au', 'aw', 'ax', 'az', 'ba', 'bb', 'bd', 'be', 'bf', 'bg', 'bh', 'bi', 'biz', 'bj', 'bl', 'bm', 'bn', 'bo', 'bq', 'br', 'bs', 'bt', 'bv', 'bw', 'by', 'bz', 'ca', 'cat', 'cc', 'cd', 'cf', 'cg', 'ch', 'ci', 'ck', 'cl', 'cm', 'cn', 'co', 'com', 'coop', 'cr', 'cu', 'cv', 'cw', 'cx', 'cy', 'cz', 'de', 'dj', 'dk', 'dm', 'do', 'dz', 'ec', 'edu', 'ee', 'eg', 'eh', 'er', 'es', 'et', 'eu', 'fi', 'fj', 'fk', 'fm', 'fo', 'fr', 'ga', 'gb', 'gd', 'ge', 'gf', 'gg', 'gh', 'gi', 'gl', 'gm', 'gn', 'gov', 'gp', 'gq', 'gr', 'gs', 'gt', 'gu', 'gw', 'gy', 'hk', 'hm', 'hn', 'hr', 'ht', 'hu', 'id', 'ie', 'il', 'im', 'in', 'info', 'int', 'io', 'iq', 'ir', 'is', 'it', 'je', 'jm', 'jo', 'jobs', 'jp', 'ke', 'kg', 'kh', 'ki', 'km', 'kn', 'kp', 'kr', 'kw', 'ky', 'kz', 'la', 'lb', 'lc', 'li', 'lk', 'lr', 'ls', 'lt', 'lu', 'lv', 'ly', 'ma', 'mc', 'md', 'me', 'mf', 'mg', 'mh', 'mil', 'mk', 'ml', 'mm', 'mn', 'mo', 'mobi', 'mp', 'mq', 'mr', 'ms', 'mt', 'mu', 'museum', 'mv', 'mw', 'mx', 'my', 'mz', 'na', 'name', 'nc', 'ne', 'net', 'nf', 'ng', 'ni', 'nl', 'no', 'np', 'nr', 'nu', 'nz', 'om', 'org', 'pa', 'pe', 'pf', 'pg', 'ph', 'pk', 'pl', 'pm', 'pn', 'pr', 'pro', 'ps', 'pt', 'pw', 'py', 'qa', 're', 'ro', 'rs', 'ru', 'rw', 'sa', 'sb', 'sc', 'sd', 'se', 'sg', 'sh', 'si', 'sj', 'sk', 'sl', 'sm', 'sn', 'so', 'sr', 'ss', 'st', 'su', 'sv', 'sx', 'sy', 'sz', 'tc', 'td', 'tel', 'tf', 'tg', 'th', 'tj', 'tk', 'tl', 'tm', 'tn', 'to', 'tp', 'tr', 'travel', 'tt', 'tv', 'tw', 'tz', 'ua', 'ug', 'uk', 'um', 'us', 'uy', 'uz', 'va', 'vc', 've', 'vg', 'vi', 'vn', 'vu', 'wf', 'ws', 'xxx', 'ye', 'yt', 'za', 'zm', 'zw', 'shop', 'store');
			$domian_arr = array_diff ($domian_arr, $ignoreDomain);
			return $domian_arr;
		}
		
		
		function addStore($storeInfo, $mid = "", $site = "", $merName = ""){
			if(count($storeInfo) < 2){
				return false;
			}
			$fileds = array_keys($storeInfo);
			$sql = "insert into store (" . implode(",", $fileds) . ") values ";
			$sqlVla = "";
			foreach($storeInfo as $val){
				if($sqlVla == ""){
					$sqlVla = "'" . addslashes($val) . "'";
				}else{
					$sqlVla .= ",'" . addslashes($val) . "'";
				}
			}
			$sqlVla = "(" . $sqlVla . ")";
			$res = $this->objMysql->query($sql . $sqlVla);
			$storeId = $this->objMysql->getLastInsertId();
			if($mid != "" && $site != ""){
				$sqlStoreRel = "insert ignore into `store_merchant_relationship` (`StoreID`, `SiteName`, `MerchantID`, `MerchantName`, `AddTime`) values 
								(" . $storeId . ", '" . strtoupper($site) . "', 
		               			'" . addslashes($mid) . "', '" . addslashes($merName) . "', '" . date('Y-m-d H:i:s') . "')";
				$res1 = $this->objMysql->query($sqlStoreRel);
			}
			if($res !== false && $res1 !== false){
				return $storeId;
			}else{
				return false;
			}
			
		}
		
		function addSMRes($site, $mid, $storeid, $merName = ''){
			$sqlStoreRel = "insert ignore into `store_merchant_relationship` (`StoreID`, `SiteName`, `MerchantID`, `MerchantName`, `AddTime`) values 
								(" . $storeid . ", '" . strtoupper($site) . "', 
		               			'" . addslashes($mid) . "','" . addslashes($merName) . "' , '" . date('Y-m-d H:i:s') . "')";
			$res = $this->objMysql->query($sqlStoreRel);
			return $res;
		}
		
		function getMerchantRank($mid_arr = array()){
			if(count($mid_arr)){
				$sql = "select * from merchant_rank where MerchantId in(".implode(",", $mid_arr).")";			
				$data = $this->objMysql->getRows($sql);
				
				return $data;
			}
		}
		function checkCustomDeepUrl($deepUrl){
			$deepUrl = trim($deepUrl);
			if($deepUrl == ""){
				return true;
			}
			$tpls = array("[PURE_DEEPURL]",
							"[DEEPURL]",
							"[DOUBLE_ENCODE_DEEPURL]",
							"[URI]",
							"[ENCODE_URI]",
							"[DOUBLE_ENCODE_URI]",
							"[SUBTRACKING]",
							"[?|&]"
						);
			foreach($tpls as $tpl){
				if(stripos($deepUrl, $tpl) !== false){
					return true;
				}
			}
			return false;
		}
		
		function syncStoreToMerchant($storeId, $fields = array()){
			global $databaseInfo;
			if(count($fields) == 0){
				return true;
			}
			if($storeId == ''){
				return true;
			}
			$sql = "select * from store_merchant_relationship a, store b where a.StoreID = b.ID and a.StoreID = '$storeId'";
			$smRows = $this->objMysql->getRows($sql);
			if(count($smRows) == 0){
				return true;
			}
			foreach($smRows as $v){
				$baseObjMysql = new Mysql($databaseInfo["INFO_" . strtoupper($v['SiteName']) . "_DB_NAME"], $databaseInfo["INFO_" . strtoupper($v['SiteName']) . "_DB_HOST"], $databaseInfo["INFO_" . strtoupper($v['SiteName']) . "_DB_USER"], $databaseInfo["INFO_" . strtoupper($v['SiteName']) . "_DB_PASS"]);
				$fildSql = "";
				foreach($fields as $merFild => $storeFild){
					if($fildSql == ""){
						$fildSql = " $merFild='{$v[$storeFild]}'";
					}else{
						$fildSql .= ", $merFild='{$v[$storeFild]}'";
					}
				}
				$sql = "update normalmerchant_addinfo set $fildSql where ID = '{$v["MerchantID"]}'";
				$res = $baseObjMysql->query($sql);
				if($res == false){
					return false;
				}
			}
			return true;
		}
		
		/*
		 *	sync ps-r to base merchant
		 *	correct store and merchant AFF - Relationship 
		 */
		function correctStoreMerPSInfo($storeid){
			$storeid = intval($storeid);
			if($storeid > 0){				
				//get active ps info
				$sql = "SELECT psr.storeid, psr.programid, psr.AffiliateDefaultUrl, psr.DeepUrlTemplate, psr.`order`, p.affid, p.idinaff FROM program_store_relationship AS psr INNER JOIN program AS p ON (p.id = psr.programid) WHERE psr.storeid = '{$storeid}' AND psr.status = 'active' ORDER BY psr.`order`";
				$ps_arr = array();
				$ps_arr = $this->objMysql->getRows($sql);
				$store_update_arr = array("HasAffiliate" => "NO", "AffiliateDefaultUrl" => "", "DeepUrlTemplate" => "", "PSInitialized" => "YES");
				$mer_update_arr = array("HasAffiliate" => "NO", "DstUrl" => "");
				$meraddinfo_update_arr = array("CustomLink" => "", "DefaultAffiliate" => "", "DefaultProgram" => "", "DefaultIdInAff" => "" , "DeepUrlTemplateAffiliate" => "", "DeepUrlTemplateProgram" => "", "DeepUrlTemplateIdInAff" => "");
				$wf_mer_sql = array();
				if(count($ps_arr)){
					$meraddinfo_update_arr["DefaultAffiliate"] = $ps_arr[0]["affid"];
					$meraddinfo_update_arr["DefaultProgram"] = $ps_arr[0]["programid"];
					$meraddinfo_update_arr["DefaultIdInAff"] = $ps_arr[0]["idinaff"];
					
					$AffiliateDefaultUrl = $ps_arr[0]["AffiliateDefaultUrl"];		//AffiliateDefaultUrl 取Order最小的
					$DeepUrlTemplate = "";				//DeepUrlTemplate 取Order最小且不为空的
					
					$mer_update_arr["HasAffiliate"] = "YES";
					$mer_update_arr["DstUrl"] = $AffiliateDefaultUrl;
										
					foreach($ps_arr as $v){						
						if(empty($DeepUrlTemplate) && !empty($v["DeepUrlTemplate"])){
							$DeepUrlTemplate = $v["DeepUrlTemplate"];
							$meraddinfo_update_arr["CustomLink"] = $DeepUrlTemplate;							
							$meraddinfo_update_arr["DeepUrlTemplateAffiliate"] = $v["affid"];
							$meraddinfo_update_arr["DeepUrlTemplateProgram"] = $v["programid"];
							$meraddinfo_update_arr["DeepUrlTemplateIdInAff"] = $v["idinaff"];
						}
												
						$wf_mer_sql[] = array(	"ProgramId" => $v["programid"],
												"AffID" => $v["affid"],
												"MerIDinAff" => $v["idinaff"],
												"IsUsing" => 1,
												"MerDeepUrlTemplate" => $v["DeepUrlTemplate"],
												"MerDefaultAffUrl" => $v["AffiliateDefaultUrl"],
												"OrderByNum" => $v["order"],
												"LastUpdateLink" => date("Y-m-d H:i:s")
												);
					}
					
					$store_update_arr["HasAffiliate"] = "YES";
					$store_update_arr["AffiliateDefaultUrl"] = $AffiliateDefaultUrl;
					$store_update_arr["DeepUrlTemplate"] = $DeepUrlTemplate;
					
					
				}
				
				//get active sm info
				$wf_mer_arr = array();
				$sql = "SELECT storeid, lower(sitename) as sitename, merchantid FROM store_merchant_relationship where storeid = '{$storeid}'";
				$sm_arr = array();
				$sm_arr = $this->objMysql->getRows($sql);
				foreach($sm_arr as $v){
					$wf_mer_arr[$v["sitename"]][$v["merchantid"]] = intval($v["merchantid"]);
				}
				
				$objTask = new Task();
				$user = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : $_SERVER["REMOTE_USER"];
				
				foreach($wf_mer_arr as $site => $tmp_mer_id){					
					$objMysqlSite = $objTask->getSiteMysqlObj($site);
					$objMerchant = new NormalMerchant($objMysqlSite);
					
					//For merchant log
					$sql = "select m.`ID`, m.`Name`, m.`DstUrl`, ma.`CustomLink`, m.HasAffiliate from `normalmerchant` m inner join `normalmerchant_addinfo` ma on m.`ID`=ma.`ID` where m.`ID` IN ('".implode("','", $tmp_mer_id)."')";					
					$merchantInfoOld = $objMysqlSite->getRows($sql, 'ID');
					
					//edit normalmerchant
					$sql = "update normalmerchant set HasAffiliate = '".addslashes($mer_update_arr["HasAffiliate"])."', DstUrl = '".addslashes($mer_update_arr["DstUrl"])."' where id in ('".implode("','", $tmp_mer_id)."')";
					$objMysqlSite->query($sql);
					
					//edit normalmerchant_addinfo					
					$sql = "update normalmerchant_addinfo set CustomLink = '".addslashes($meraddinfo_update_arr["CustomLink"])."', DefaultAffiliate = '".addslashes($meraddinfo_update_arr["DefaultAffiliate"])."', DefaultProgram = '".addslashes($meraddinfo_update_arr["DefaultProgram"])."', DefaultIdInAff = '".addslashes($meraddinfo_update_arr["DefaultIdInAff"])."', DeepUrlTemplateAffiliate = '".addslashes($meraddinfo_update_arr["DeepUrlTemplateAffiliate"])."', DeepUrlTemplateProgram = '".addslashes($meraddinfo_update_arr["DeepUrlTemplateProgram"])."', DeepUrlTemplateIdInAff = '".addslashes($meraddinfo_update_arr["DeepUrlTemplateIdInAff"])."' where id in ('".implode("','", $tmp_mer_id)."')";
					$objMysqlSite->query($sql);
					
					//remove wf_mer_in_aff(sm_r)
					$sql = "delete from wf_mer_in_aff where merid in ('".implode("','", $tmp_mer_id)."')";
					$objMysqlSite->query($sql);
					
					//insert wf_mer_in_aff
					foreach($wf_mer_sql as $tmp_v){
						foreach($tmp_mer_id as $mer_id){
							$sql = "replace into wf_mer_in_aff set ProgramId = '".addslashes($tmp_v["ProgramId"])."',
													AffID = '".addslashes($tmp_v["AffID"])."',
													MerIDinAff = '".addslashes($tmp_v["MerIDinAff"])."',
													IsUsing = '".addslashes($tmp_v["IsUsing"])."',
													MerDeepUrlTemplate = '".addslashes($tmp_v["MerDeepUrlTemplate"])."',
													MerDefaultAffUrl = '".addslashes($tmp_v["MerDefaultAffUrl"])."',
													OrderByNum = '".addslashes($tmp_v["OrderByNum"])."',
													LastUpdateLink = '".addslashes($tmp_v["LastUpdateLink"])."',
													MerID = '$mer_id'";
							$objMysqlSite->query($sql);					
						}
					}
					
					//For change log && mail for Merchant Affiliate Program Status Change
					$_sql = "select m.`ID`, m.`Name`, m.`DstUrl`, ma.`CustomLink`, m.HasAffiliate from `normalmerchant` m inner join `normalmerchant_addinfo` ma on m.`ID` = ma.`ID` where m.`ID` IN ('".implode("','", $tmp_mer_id)."')";
					$merchantInfoNew = $objMysqlSite->getRows($_sql, 'ID');					
					foreach ($merchantInfoNew as $merid => $_merinfo) {
						if (!isset($merchantInfoOld[$merid])) continue;
						$objMerchant->merchantFieldsChangeLog("normalmerchant", $merchantInfoOld[$merid], $merchantInfoNew[$merid], $user);
						$objMerchant->merchantFieldsChangeLog("normalmerchant_addinfo", $merchantInfoOld[$merid], $merchantInfoNew[$merid], $user);						
					}	
				}
				
				// set store 
				$sql = "update store set HasAffiliate = '".addslashes($store_update_arr["HasAffiliate"])."', AffiliateDefaultUrl = '".addslashes($store_update_arr["AffiliateDefaultUrl"])."', DeepUrlTemplate = '".addslashes($store_update_arr["DeepUrlTemplate"])."', PSInitialized = '".addslashes($store_update_arr["PSInitialized"])."' where id = {$storeid}";
				$this->objMysql->query($sql);
			}
		}
//////////////Func end ///////////////////////////
	}
}
?>