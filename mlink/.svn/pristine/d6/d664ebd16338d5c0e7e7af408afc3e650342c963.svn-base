<?php
class Program
{
	function __construct()
	{
		$this->objMysql = new Mysql(TASK_DB_NAME, TASK_DB_HOST, TASK_DB_USER, TASK_DB_PASS);
	}
	
	function getProgramListByCondition($condition = array(), $fields = '*', $needAttachment = true) {
		$data = array();
		$sql = "select {$fields} from `program` ";
		
		if (!empty($condition['sql'])) $sql .= "where 1=1 {$condition['sql']} ";
		if (!empty($condition['order'])) $sql .= "order by {$condition['order']} ";
		if (!empty($condition['limit'])) $sql .= "limit {$condition['limit']} ";
		
		$data = $this->objMysql->getRows($sql);
		
		if ($needAttachment) {
			foreach ($data as $k => $v) {
				$row = $this->getProgramInternalInfoByProgramId($v['ID']);
				unset($row['ProgramId']);
				unset($row['LastUpdateTime']);
				$tmp = array_merge($v, $row);
				
				$data[$k] = $tmp;
			}
		}
		
		return $data;
	}
	
	function getProgramByID($id, $hasInternal = true) {
		$data = array();
		if (empty($id) && !is_numeric($id)) return $data;
		
		$sql = "select * from `program` where `ID`={$id}";
		if ($query = $this->objMysql->query($sql)) {
			$data = $this->objMysql->getRow($query);
			
			if ($hasInternal) {
				$row = $this->getProgramInternalInfoByProgramId($data['ID']);
				unset($row['ProgramId']);
				unset($row['LastUpdateTime']);
				$data = array_merge($data, $row);
			}
		}
		
		return $data;
	}
	
	function getProgramIdByName($name, $hasInternal = true) {
		$data = array();
		$name = trim($name);
		if (empty($name)) return $data;
		
		$sql = "select * from `program` where `Name` = '{$name}'";
		if ($query = $this->objMysql->query($sql)) {
			$data = $this->objMysql->getRow($query);
			
			if ($hasInternal) {
				$row = $this->getProgramInternalInfoByProgramId($data['ID']);
				unset($row['ProgramId']);
				unset($row['LastUpdateTime']);
				$data = array_merge($data, $row);
			}
		}
		
		return $data;
	}
	
	function getProgramInternalInfoByProgramId($id) {
		$data = array();
		
		if (empty($id) || !is_numeric($id)) return $data;
		
		$sql = "select * from `program_int` where `ProgramId`={$id}";
		$query = $this->objMysql->query($sql);
		$data = $this->objMysql->getRow($query);
		
		return $data;
	}
	
	function getAffiliateIdsByType($type = 'YES') {
		$sql = "select `ID` from `wf_aff` where `IsInHouse`='{$type}'";
		$dataTmp = $this->objMysql->getRows($sql);
		
		foreach ($dataTmp as $v) {
			$idArr[] = $v['ID'];
		}
		
		$ids = implode(',', (array)$idArr);
		
		return !empty($ids) ? $ids : 0;
	}
	
	function getAffiliateInfoById($id) {
		$data = array();
		
		if (empty($id) || !is_numeric($id)) return $data;
		
		$sql = "select * from `wf_aff` where `ID`={$id}";
		$query = $this->objMysql->query($sql);
		$data = $this->objMysql->getRow($query);
		
		return $data;
	}
	
	function getAffiliateByKw($kw){
		$data = array();
		$sql = '';
		if (trim($kw) == '') return $data;
	    
		preg_match('/[^\d]+/', trim($kw), $matches);
		if (!empty($matches)) {
			$sql = "select DISTINCT `ID`, `Name` from `wf_aff` where (`Name` like '%". trim($kw) ."%' OR ShortName like '". trim($kw) ."%')";
		} else {
			$sql = "select DISTINCT `ID`, `Name` from `wf_aff` where (`ID` like '%". trim($kw) ."%' or `Name` like '%". trim($kw) ."%' OR ShortName like '". trim($kw) ."%') ";
		}
		
		$data = $this->objMysql->getRows($sql);
		
		return $data;
	}
	
	function getAffiliateByName($name) {
		$data = array();
		
		if (empty($name)) return $data;
		$name = addslashes(trim($name));
		$sql = "select * from `wf_aff` where `Name`= '{$name}'";
		
		$data = $this->objMysql->getFirstRow($sql);
		return $data;
	}
	
	function getAllAffiliates() {
		$sql = "select * from `wf_aff` where `IsActive`='YES'";
		$data = $this->objMysql->getRows($sql);
		
		return $data;
	}
	
	function getMerchantProgram($condition = '') {
		$data = array();
		
		$sql = "select * from `merchant_program` ";
		if (!empty($condition)) $sql .= " where 1=1 {$condition} ";
		$data = $this->objMysql->getRows($sql);

		return $data;
	}	
	
	function getMerchantProgramFromSite($id, $site, $objMysqlTask){
		$objMysqlSite = $objMysqlTask->getSiteMysqlObj($site);
		$sql = "SELECT * FROM wf_mer_in_aff WHERE ProgramId = " . intval($id);
		$data = array();
		$data = $objMysqlSite->getRows($sql);
		
		return $data;
	}
	
	function getMerchantFromSite($id, $site, $objMysqlTask){
		$objMysqlSite = $objMysqlTask->getSiteMysqlObj($site);
		$sql = "SELECT Name FROM normalmerchant WHERE ID = " . intval($id);
		$data= array();
		$data = $objMysqlSite->getFirstRow($sql);
		
		return $data;
	}	
	
	function getMerchantsFromSite($ids, $site, $objMysqlTask){
		if(!count($ids)) return false;
		$objMysqlSite = $objMysqlTask->getSiteMysqlObj($site);
		$sql = "SELECT ID, Name FROM normalmerchant WHERE ID IN ('" . implode($ids, "','") . "')";
		$data= array();
		$data = $objMysqlSite->getRows($sql);
		
		return $data;	
	}
	function getMerchantsProgramInfo($ids, $site, $objMysqlTask){
		if(!count($ids)) return false;
		$objMysqlSite = $objMysqlTask->getSiteMysqlObj($site);
		$sql = "SELECT * FROM wf_mer_in_aff a, normalmerchant b WHERE a.MerID = b.ID and b.ID IN ('" . implode($ids, "','") . "')";
		$data= array();
		$data = $objMysqlSite->getRows($sql);
		return $data;	
	}
	
	function updateProgram($row, $id) {
		if (empty($row['external']) || !is_numeric($id)) return false;
		$row['external']['LastUpdateTime'] = date("Y-m-d H:i:s");
		
		$sql = "update `program` set ";
		$where = " where ";
		
		if (isset($row['external']['TargetCountryInt']) && !empty($row['external']['TargetCountryInt'])) {
			sort($row['external']['TargetCountryInt']);
			$row['external']['TargetCountryInt'] = implode(',', (array)$row['external']['TargetCountryInt']);
		} else {
			$row['external']['TargetCountryInt'] = '';
		}
		
		foreach ($row['external'] as $k => $v) 
		{
			$sql .= "`" . $k . "` = '" . addslashes($v) . "', ";
		}
		
		$sql = preg_replace("|, $|i", ' ', $sql);
		$sql .= " WHERE `ID`={$id}";
		
		if (!$this->objMysql->query($sql)) return false;
		
		if (empty($row['internal'])) return true;
		$row['internal']['LastUpdateTime'] = date("Y-m-d H:i:s");
		
		$checkProgramInt = $this->getProgramInternalInfoByProgramId($id);
		if (!empty($checkProgramInt)) {
			if (!$this->updateProgramInt($row['internal'], $id)) return false;
		}else {
			$row['internal']['ProgramId'] = $id;
			if (!$this->insertProgramInt($row['internal'])) return false;
		}
		
		return true;
	}
	
	function insertProgramInt($row = array()) {
		if (empty($row)) return false;
		
		$sql = "insert into `program_int` ";
		$fields = $values = '';
		
		foreach ($row as $k => $v) {
			$fields .= "`" . $k . "`, ";
			$values .= "'" . addslashes($v) . "', ";
		}
		
		$fields = preg_replace("|, $|i", '', $fields);
		$values = preg_replace("|, $|i", '', $values);
		$sqlQuery = $sql . '(' . $fields . ') values (' . $values . ');';
		
		if (!$this->objMysql->query($sqlQuery)) return false;
		
		return true;
	}
	
	function updateProgramInt($row, $id) {
		if (empty($row) || !is_numeric($id)) return false;
		
		$sql = "update `program_int` set ";
		$where = " where ";
		
		foreach ($row as $k => $v) 
		{
			$sql .= "`" . $k . "` = '" . addslashes($v) . "', ";
		}
		
		$sql = preg_replace("|, $|i", ' ', $sql);
		$sql .= " WHERE `ProgramId`={$id}";
		
		if (!$this->objMysql->query($sql)) return false;
		
		return true;
	}
	
	//special function for program edit
	function insertProgramChangeLog($row, $programid, $needtask = true) {
		if (empty($row) || !is_numeric($programid)) return false;

		$programInfo = $this->getProgramByID($programid, false);
		$programInternalInfo = $this->getProgramInternalInfoByProgramId($programid);
		
		$externalInfo = $internalInfo = array();
		if (isset($row['external']) && !empty($row['external'])) {
			$externalInfo = compareFieldValue($row['external'], $programInfo);
		}
		
		if (isset($row['internal']) && !empty($row['internal'])) {
			$internalInfo = compareFieldValue($row['internal'], $programInternalInfo);
		}
		
		$allChangeData = array_merge($externalInfo, $internalInfo);
		
		$insertData = array();
		$insertConstantData = array(
			'ProgramId' => $programid,
			'IdInAff'   => $programInfo['IdInAff'],
		    'Name'      => $programInfo['Name'],
		    'AffId'     => $programInfo['AffId'],
		    'AddTime'   => date("Y-m-d H:i:s"),
		    'LastUpdateTime' => date("Y-m-d H:i:s"),
		);
		
		//add by stan @ 2013-02-20
		$Status = ($needtask) ? 'NEW' : 'PROCESSED';
		
		if (!empty($allChangeData)) {
			foreach ($allChangeData as $key => $val) {
				$insertData = $insertConstantData;
				$insertData['FieldName'] = $key;
				$insertData['FieldValueOld'] = $val['old'];
				$insertData['FieldValueNew'] = $val['new'];
				$insertData['Status'] = $Status;
				
				$sql = "insert ignore into `program_change_log` ";
				$fields = $values = '';
				
				foreach ($insertData as $k => $v) {
					$fields .= "`" . $k . "`, ";
					$values .= "'" . addslashes($v) . "', ";
				}
				unset($insertData);
				
				$fields = preg_replace("|, $|i", '', $fields);
				$values = preg_replace("|, $|i", '', $values);
				$sqlQuery = $sql . '(' . $fields . ') values (' . $values . ');';
				
				if (!$this->objMysql->query($sqlQuery)) return false;
			}
		}
		
		return true;
	}
	
	function getProgramChangeLogByCondition($condition = array(), $fields = '*', $show_prgm = false) {
		$data = array();
		if($show_prgm){			
			$sql = "select program_change_log.*, program.TargetCountryInt from `program_change_log` INNER JOIN `program` ON (`program`.ID = `program_change_log`.ProgramId) ";
		}else{
			$sql = "select {$fields} from `program_change_log` ";
		}
		
		if (!empty($condition['sql'])) $sql .= "where 1=1 {$condition['sql']} ";
		if (!empty($condition['order'])) $sql .= "order by {$condition['order']} ";
		if (!empty($condition['limit'])) $sql .= "limit {$condition['limit']} ";
		
		$data = $this->objMysql->getRows($sql);
		
		return $data;
	}
	
	function getProgramChangeLogByID($id = '') {
		$data = array();
		if (empty($id) || !is_numeric($id)) return $data;
		
		$sql = "select * from `program_change_log` where `ID`={$id}";
		$query = $this->objMysql->query($sql);
		$data = $this->objMysql->getRow($query);
		
	    return $data;
	}
	
	function updateProgramChangeLog($row, $id) {
		if (empty($row) || !is_numeric($id)) return false;
		
		$sql = "update `program_change_log` set ";
		$where = " where ";
		
		foreach ($row as $k => $v) 
		{
			$sql .= "`" . $k . "` = '" . addslashes($v) . "', ";
		}
		
		$sql = preg_replace("|, $|i", ' ', $sql);
		$sql .= " WHERE `ID`={$id}";
		
		if (!$this->objMysql->query($sql)) return false;
		
		return true;
	} 
	
	function getProgramsByKw($kw) {
		$data = array();
		$sql = '';
		if (trim($kw) == '') return $data;
	    
		preg_match('/[^\d]+/', trim($kw), $matches);
		if (!empty($matches)) {
			$sql = "select `ID`, `Name` from `program` where `Name` like '%". trim($kw) ."%' ";
		} else {
			$sql = "select `ID`, `Name` from `program` where (`ID` like '%". trim($kw) ."%' or `Name` like '%". trim($kw) ."%') ";
		}
		
		$data = $this->objMysql->getRows($sql);
		
		return $data;
	}
	
	function getProgramsByKwAndCondition($kw, $condition = array()) {
		$data = array();
		$sql = '';
		if (trim($kw) == '') return $data;
		
		$str_where = "";
		if(count($condition)){
			$str_where = " AND " . implode($condition, " AND ");
		}
			    
		$sql = "select `ID`, `IdInAff`, `Name` from `program` where (`IdInAff` like '%". trim($kw) ."%' or `Name` like '%". trim($kw) ."%') $str_where";		
		
		$data = $this->objMysql->getRows($sql);
		
		return $data;
	}
	
	function getProgramChangeLogIds($condition = '') {
		$sql = "select `ID` from `program_change_log` where 1=1 {$condition} ";
		$tmp = $this->objMysql->getRows($sql);
		
		foreach ($tmp as $k => $v) {
			$datatmp[] = $v['ID'];
		}
		
		$data = implode(',', (array)$datatmp);
		
		return $data;
	}
	
	function getAllProgramRemindsByProgramId($programid = '') {
		$data = array();
		
		if (!is_numeric($programid)) return $data;
		
		$sql = "select * from `program_remind` where `ProgramId`={$programid} order by `RemindDate` asc";
		$data = $this->objMysql->getRows($sql);
		
		return $data;
	}
	
	function insertProgramRemind($row = array()) {
		if (empty($row)) return false;
		
		$sql = "insert into `program_remind` ";
		$fields = $values = '';
		
		foreach ($row as $k => $v) {
			$fields .= "`" . $k . "`, ";
			$values .= "'" . addslashes($v) . "', ";
		}
		
		$fields = preg_replace("|, $|i", '', $fields);
		$values = preg_replace("|, $|i", '', $values);
		$sqlQuery = $sql . '(' . $fields . ') values (' . $values . ');';
		
		if (!$this->objMysql->query($sqlQuery)) return false;
		
		return true;
	}
	
	function getProgramId4CurrentDate($format = true) {
		$date = date("Y-m-d");
		$sql = "select distinct(`ProgramId`) from `program_remind` where `RemindDate`='{$date}'";
		$data = $this->objMysql->getRows($sql);
		
		if ($format) {
			foreach ($data as $val) {
				$tmp[] = $val['ProgramId'];
			}
			
			$data = (isset($tmp) && !empty($tmp)) ? implode(',', $tmp) : '';
		}
		
		return $data;
	}
	
	function getPMInfoById($id){
		$sql = "SELECT * FROM merchant_program WHERE ProgramId = " . intval($id);
		$data = $this->objMysql->getRows($sql);		
		return $data;
	}
	
	function getPSInfoById($id){
		$sql = "SELECT r.ID, r.ProgramId, r.StoreId, r.AffiliateDefaultUrl, r.DeepUrlTemplate, r.Status, r.IsFake, s.Name, s.URL, s.Domain, r.IgnoreDate FROM program_store_relationship AS r LEFT JOIN store AS s ON (s.ID = r.StoreId) WHERE r.ProgramId = " . intval($id);
		$data = $this->objMysql->getRows($sql);		
		return $data;
	}
	
	function getStoreDefaultInfoByIds($ids = array()){
		if(count($ids)){
			//$sql = "SELECT r.ProgramId, r.StoreId, r.AffiliateDefaultUrl, r.DeepUrlTemplate, s.AffiliateDefaultUrl AS s_AffiliateDefaultUrl, s.DeepUrlTemplate AS s_DeepUrlTemplate, s.Name FROM program_store_relationship AS r INNER JOIN store AS s ON (s.ID = r.StoreId AND (r.AffiliateDefaultUrl = s.AffiliateDefaultUrl OR r.DeepUrlTemplate = s.DeepUrlTemplate)) WHERE r.Status = 'Active' AND r.AffiliateDefaultUrl <> '' AND r.DeepUrlTemplate <> '' AND r.StoreId IN ('".implode("','",$ids)."')";
			$sql = "SELECT r.ProgramId, r.StoreId, r.AffiliateDefaultUrl, r.DeepUrlTemplate, s.AffiliateDefaultUrl AS s_AffiliateDefaultUrl, s.DeepUrlTemplate AS s_DeepUrlTemplate, s.Name, s.SEM FROM program_store_relationship AS r INNER JOIN store AS s ON (s.ID = r.StoreId AND (r.AffiliateDefaultUrl = s.AffiliateDefaultUrl OR r.DeepUrlTemplate = s.DeepUrlTemplate)) WHERE r.Status = 'Active' AND r.StoreId IN ('".implode("','",$ids)."')";
			$data = $this->objMysql->getRows($sql);		
			return $data;
		}
	}
	
	function getProgramUrlTemplate() {
		$sql = "select `ID`, `ProgramUrlTemplate` from `wf_aff` where `ProgramUrlTemplate` IS NOT NULL AND `ProgramUrlTemplate`!=''";
		
		return $this->objMysql->getRows($sql, 'ID');
	}
	
	function doInsertProgram($arr)
	{		
		$field_list = array();
		$value_list = array();
		$field_list = array_keys($arr);
		$value_list[] = "('".implode("','", array_values($arr))."')";
		
		$sql = "INSERT IGNORE INTO program(".implode(",",$field_list).") VALUES".implode(",",$value_list);
		try{
			$this->objMysql->query($sql);			
			return $this->objMysql->getLastInsertId();
		}
		catch (Exception $e) {
			echo $e->getMessage()."\n";			
		}
		return false;
	}
	
	function checkTMS($programid, $TMPolicy, $TMTermsPolicy, $InquiryStatus, $SEMPolicyRemark){
		$sql = "SELECT i.ProgramId FROM program_int AS i INNER JOIN program AS p ON (p.ID = i.ProgramId) WHERE i.ProgramId = '$programid' AND i.TMTermsPolicy = '".addslashes($TMTermsPolicy)."' AND i.TMPolicy = '".addslashes($TMPolicy)."' AND i.InquiryStatus = '".addslashes($InquiryStatus)."' AND p.SEMPolicyRemark = '".addslashes($SEMPolicyRemark)."'";
		$data = $this->objMysql->getRows($sql);
		if (count($data)) return false;
		return true;
	}
	
	function checkPPC($programid, $InquiryStatus){
		$sql = "SELECT i.ProgramId FROM program_int AS i INNER JOIN program AS p ON (p.ID = i.ProgramId) WHERE i.ProgramId = '$programid' AND i.InquiryStatus = '".addslashes($InquiryStatus)."'";		
		$data = $this->objMysql->getRows($sql);
		if (count($data)) return false;
		return true;
	}
	
	function checkReApplyStatus($programid, $ReApplyStatus){
		$sql = "SELECT i.ProgramId FROM program_int AS i INNER JOIN program AS p ON (p.ID = i.ProgramId) WHERE i.ProgramId = '$programid' AND i.ReApplyStatus = '".addslashes($ReApplyStatus)."'";
		$data = $this->objMysql->getRows($sql);
		if (count($data)) return false;
		return true;
	}

	function checkSemPolicy($programid, $TMPolicy, $TMTermsPolicy){
		$sql = "SELECT i.ProgramId FROM program_int AS i INNER JOIN program AS p ON (p.ID = i.ProgramId) WHERE i.ProgramId = '$programid' AND i.TMTermsPolicy = '".addslashes($TMTermsPolicy)."' AND i.TMPolicy = '".addslashes($TMPolicy)."' ";
		$data = $this->objMysql->getRows($sql);
		if (count($data)) return false;
		return true;
	}
}
?>