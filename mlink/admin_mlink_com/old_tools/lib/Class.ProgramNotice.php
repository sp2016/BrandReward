<?php
class ProgramNotice
{
	function __construct()
	{
		$this->objMysql = new Mysql(TASK_DB_NAME, TASK_DB_HOST, TASK_DB_USER, TASK_DB_PASS);
		$this->aff_check_list = array(1,2,4,5,6,7,8,10,12,13,14,15,18,20,22,27,28,30,32,35,58,59,62,115,133,181,52,29,50,49,124,36,152,65,26,63,34,57,125,163,23,97,46,37,240,196,197,182,189,243,53,177);
		$this->getMainAffCrawled();
		$this->sub_aff_list = array(191,160,223);
	}
	
	function getMainAffCrawled() {
		$data = array();
		$sql = "SELECT id FROM wf_aff WHERE ProgramCrawled = 'yes' AND IsInHouse = 'NO' AND IsActive = 'YES' and id not in (191,160,223)";		
		$data = $this->objMysql->getRows($sql, "id");
		
		if(count($data) && is_array($data)){
			$this->aff_check_list = array_keys($data);
		}		
	}
	
	function getProgramNoticeListByCondition($condition = array(), $fields = '*') {
		$data = array();
		$sql = "select {$fields} from `task_program_notice` ";
		
		if (!empty($condition['sql'])) $sql .= "where 1=1 {$condition['sql']} ";
		if (!empty($condition['order'])) $sql .= "order by {$condition['order']} ";
		if (!empty($condition['limit'])) $sql .= "limit {$condition['limit']} ";
		
		$data = $this->objMysql->getRows($sql);
		
		return $data;
	}

    function getAllProgramNoticeCFG() {
    	$sql = "select * from `program_notice_cfg`";
    	$data = $this->objMysql->getRows($sql);
    	
    	return $data;
    }
    
    function getProgramNoticeCfgByCon($condition = '') {
    	$sql = "select * from `program_notice_cfg` ";
    	if (!empty($condition)) $sql .= " where 1=1 {$condition} ";
    	$data = $this->objMysql->getRows($sql);

    	return $data;
    }
    
    function insertProgramNotice($row = array()) {
    	if (empty($row)) return false;
    	
    	$sql = "insert into `task_program_notice` ";
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
    
	function insertProgramNoticePartnership($row = array()) {
    	if (empty($row)) return false;
    	
    	$sql = "insert into `task_program_notice_partnership` ";
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
    
	function insertProgramNoticePPC($row = array()) {
    	if (empty($row)) return false;
    	
    	$sql = "insert into `task_program_notice_ppc` ";
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
    
	function insertProgramStoreNotice($row = array()) {
    	if (empty($row)) return false;
    	
    	$sql = "insert into `task_program_store_notice` ";
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
    
	function updateProgramNotice($row, $id) {
		if (empty($row) || !is_numeric($id)) return false;
		
		$sql = "update `task_program_notice` set ";
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
    
    function getEnumValueByField($field = '') {
    	if (empty($field)) return false;
    	
    	$sql = "SHOW COLUMNS FROM `program_notice_cfg` LIKE '{$field}'";
    	$query = $this->objMysql->query($sql);
    	$tmp = $this->objMysql->getRow($query);
    	
    	preg_match('/enum\((.*)\)/is', trim($tmp['Type']), $match);
    	$valueTmp = explode(',', $match[1]);
    	
    	foreach ($valueTmp as $k => $v) {
    		$value = substr(trim($v), 0, -1);
    		$value = substr($value, 1);
    		$data[$value] = $value;
    	}
    	
    	return $data;
    }
    
    function getAllNoticeCfgFieldsForNoticeType() {
    	$data = array();
    	
    	$noticeTypeEnumValues = $this->getEnumValueByField('NoticeType');
    	foreach ($noticeTypeEnumValues as $k => $v) {
    		$filesArrTmp = $this->getProgramNoticeCfgByCon(" and `NoticeType`='{$v}' ");
    		if (empty($filesArrTmp)) {
    			$data[$v] = array();
    			continue;
    		}
    		
    		
    		foreach ($filesArrTmp as $k1 => $v1) {
    			$filedsTmp = preg_replace(array("/\\s+/is", "/[\\r|\\n|\\r\\n]/is"), '', trim($v1['Fields']));
				$filedsTmp1 = explode(',', $filedsTmp);
				foreach ($filedsTmp1 as $v2) {
					$fieldsHasKey[$v2] = $v2;
				}
    		}
    		
    		ksort($fieldsHasKey);
    		$data[$v] = $fieldsHasKey;
    		unset($fieldsHasKey);
    	}
        
        return $data;
    }
    
	function getBdAssignEditor(){
		$data = array();
		$sql = "SELECT * FROM task_assignment_bd";
		$tmp_arr = array();
		$tmp_arr = $this->objMysql->getRows($sql);
		foreach($tmp_arr as $v){
			$data[$v["TaskType"]][$v["Country"]][$v["AffiliateId"]] = $v["AssignToEditor"];
		}
		
		return $data;
	}
	
	function checkActiveMerchantProgram($id){
		$sql = "SELECT * FROM program AS p LEFT JOIN merchant_program AS m ON (m.ProgramId = " .intval($id). ") WHERE m.Status = 'Active' and p.PartnerShip = 'Active' AND p.ID = m.ProgramId";
		$data = array();
		$data = $this->objMysql->getRows($sql);
		if(count($data)){
			return true;
		}
		return false;
	}
	
	function addProgramStoreStatusTask(){
		$aff_arr = $this->aff_check_list;
		$return_count = 0;
		
		$sql = "select p.ID, p.StatusInAff, p.Partnership, p.TargetCountryInt, p.AffId, ps.Status from program as p, program_store_relationship as ps where p.id = ps.programid and p.AffId in (".implode(",",$aff_arr).") and ((p.Partnership = 'active' and p.StatusInAff = 'active' and ps.Status <> 'active') or ((p.Partnership <> 'active' OR p.StatusInAff <> 'active') and ps.Status = 'active')) group by p.id";
		$data = array();
		$value_list = array();
		
		$BdAssignEditor = array();
		$BdAssignEditor = $this->getBdAssignEditor();
		
		$data = $this->objMysql->getRows($sql);
		foreach($data as $v){
			$NoticeType = "PARTNERSHIP_OFF";
			if($v["Status"] != "Active"){
				$NoticeType = "PARTNERSHIP_ON";
			}
			
			//filter repeat task
			$has_task = array();
			$sql = "SELECT * FROM task_program_store_status_notice WHERE ProgramId = {$v["ID"]} AND NoticeType = '{$NoticeType}'";
			$has_task = $this->objMysql->getRows($sql);
			if(count($has_task)){				
				continue;
			}			
						
			if(empty($v["TargetCountryInt"])) $v["TargetCountryInt"] = "ALL";
			$country_arr = explode(",", $v["TargetCountryInt"]);
			if(count($country_arr) > 1){			
				foreach($country_arr as $k => $country_val){						
					$Resolver = $BdAssignEditor[$NoticeType][$country_val][$v["AffId"]];
					if(empty($Resolver)) $Resolver = $BdAssignEditor[$NoticeType][$country_val][0];
					if(!empty($Resolver)){						
						$value_list[] = "({$v["ID"]}, '{$NoticeType}', 'NEW', '".date("Y-m-d H:i:s")."', '".date("Y-m-d H:i:s")."', '{$Resolver}', '{$country_val}')";
						unset($country_arr[$k]);
					}
				}			
				if(count($country_arr)){
					$Resolver = $Resolver = $BdAssignEditor[$NoticeType]["ALL"][$v["AffId"]];
					if(empty($Resolver)) $Resolver = $BdAssignEditor[$NoticeType]["ALL"][0];
					if(empty($Resolver)) $Resolver = $BdAssignEditor["ALL"]["ALL"][0];
					
					$value_list[] = "({$v["ID"]}, '{$NoticeType}', 'NEW', '".date("Y-m-d H:i:s")."', '".date("Y-m-d H:i:s")."', '{$Resolver}', '{$country_arr}')";
				}
				
			}else{
				$Resolver = $BdAssignEditor[$NoticeType][$country_arr[0]][$v["AffId"]];
				if(empty($Resolver)) $Resolver = $BdAssignEditor[$NoticeType][$country_arr[0]][0];
				if(empty($Resolver)) $Resolver = $BdAssignEditor[$NoticeType]["ALL"][0];
				if(empty($Resolver)) $Resolver = $BdAssignEditor["ALL"]["ALL"][0];				
				
				$value_list[] = "({$v["ID"]}, '{$NoticeType}', 'NEW', '".date("Y-m-d H:i:s")."', '".date("Y-m-d H:i:s")."', '{$Resolver}', '{$country_arr[0]}')";
			}
			
			if(count($value_list) > 100){
				$sql = "REPLACE INTO task_program_store_status_notice(ProgramId,NoticeType,Status,AddTime,LastUpdateTime,Resolver,AssignCountry) VALUES".implode(",",$value_list);
				$this->objMysql->query($sql);
				$value_list = array();
			}
			$return_count++;
		}
		if(count($value_list)){
			$sql = "REPLACE INTO task_program_store_status_notice(ProgramId,NoticeType,Status,AddTime,LastUpdateTime,Resolver,AssignCountry) VALUES".implode(",",$value_list);
			$this->objMysql->query($sql);
			unset($value_list);
		}
		if($return_count){
			return $return_count;
		}
		return false;
	}
	
	function addProgramWithNoPSRNoticeTask(){
		$aff_arr = $this->aff_check_list;
		$return_count = 0;
		$batch_id = time();
		
		//$sql = "SELECT p.ID, p.AffId, p.IdInAff, p.TargetCountryInt FROM program AS p WHERE p.Partnership = 'Active' and p.StatusInAff = 'Active' AND p.AffId IN (".implode(",",$aff_arr).") AND p.ID NOT IN (SELECT programid FROM program_store_relationship GROUP BY programid) AND TargetCountryInt IN ('us','uk','ca','au','ie','nz','sg','in') AND p.ID NOT IN (SELECT ProgramId FROM task_program_store_notice WHERE NoticeReason = 'NEW_PROGRAM' AND Status = 'NEW')";
		$sql = "SELECT p.ID, p.AffId, p.IdInAff, p.TargetCountryInt FROM program AS p WHERE p.Partnership = 'Active' and p.StatusInAff = 'Active' AND p.AffId IN (".implode(",",$aff_arr).") AND TargetCountryInt IN ('us','uk','ca','au','ie','nz','sg','in')";
		$data = array();	
		$data = $this->objMysql->getRows($sql);
		
		$BdAssignEditor = array();
		$BdAssignEditor = $this->getBdAssignEditor();
		
		$NoticeType = 'PARTNERSHIP_ON';
		
		$webgains_country = array("UK" => 13, "US" => 14, "IE" => 18, "DE" => 34);
		
		foreach($data as $v){
			//filter has ps relationship
			$has_task = array();
			$sql = "SELECT programid FROM program_store_relationship WHERE programid = {$v["ID"]} LIMIT 1";
			$has_task = $this->objMysql->getRows($sql);
			if(count($has_task)){
				continue;
			}
			
			
			//filter repeat task
			$has_task = array();
			$sql = "SELECT ID FROM task_program_store_notice WHERE ProgramId = {$v["ID"]} AND (NoticeType = 'PARTNERSHIP_ON' OR NoticeReason = 'NEW_PROGRAM') AND StoreId = 0 AND (Status = 'NEW' OR IgnoreDate > '".date("Y-m-d H:i:s")."') LIMIT 1";
			$has_task = $this->objMysql->getRows($sql);
			if(count($has_task)){
				continue;
			}
			
			//webgains
			if($v['AffId'] == 13 || $v['AffId'] == 14 || $v['AffId'] == 18 || $v['AffId'] == 34){
				$check_webgains = true;
				if(isset($webgains_country[$v["TargetCountryInt"]]) && $webgains_country[$v["TargetCountryInt"]] != $v['AffId']){					
				//if($v['AffId'] == 13 && $v["TargetCountryInt"] != "UK"){
					$check_affid = $webgains_country[$v["TargetCountryInt"]];
					$sql = "SELECT ID FROM program WHERE affid = '{$check_affid}' and idinaff = '{$v['IdInAff']}' and Partnership = 'Active' and StatusInAff = 'Active' LIMIT 1";
					$tmp_arr = $this->objMysql->getFirstRow($sql);
					if(count($tmp_arr)){
						$check_webgains = false;
					}
				}
				if(!$check_webgains) continue;
			}
			
			/*$has_task = array();
			$sql = "SELECT ID FROM task_program_store_notice WHERE ProgramId = {$v["ID"]} AND NoticeType = 'PARTNERSHIP_ON' AND StoreId = 0 AND (Status = 'NEW' OR IgnoreDate > '".date("Y-m-d H:i:s")."') LIMIT 1";
			$has_task = $this->objMysql->getRows($sql);
			if(count($has_task)){
				continue;
			}*/
			
			$insertNoticeListData = array(
				'LogId' => 'NEWPROGRAM_'.$v["ID"]."_".$batch_id,
				'ProgramId' => $v["ID"],
				'AffId' => $v['AffId'],
				'IdInAff' => $v['IdInAff'],
				'NoticeType' => 'PARTNERSHIP_ON',			
				'NoticeReason' => 'NEW_PROGRAM',
				'Status' => 'NEW',
				'AddTime' => date("Y-m-d H:i:s"),				
				'LastUpdateTime' => date("Y-m-d H:i:s"),
				//'Resolver' => $Resolver,
				//'AssignCountry' => $AssignCountry,
			);
			
			if(empty($v["TargetCountryInt"])) $v["TargetCountryInt"] = "ALL";
			$country_arr = explode(",", $v["TargetCountryInt"]);
			if(count($country_arr) > 1){			
				foreach($country_arr as $k => $country_val){						
					$Resolver = $BdAssignEditor[$NoticeType][$country_val][$v["AffId"]];
					if(empty($Resolver)) $Resolver = $BdAssignEditor[$NoticeType][$country_val][0];
					if(!empty($Resolver)){						
						//$value_list[] = "({$v["ID"]}, '{$NoticeType}', 'NEW', '".date("Y-m-d H:i:s")."', '".date("Y-m-d H:i:s")."', '{$Resolver}', '{$country_val}')";
						$insertNoticeListData["Resolver"] = $Resolver;
						$insertNoticeListData["AssignCountry"] = $country_val;
						unset($country_arr[$k]);
					}
				}			
				if(count($country_arr)){
					$Resolver = $Resolver = $BdAssignEditor[$NoticeType]["ALL"][$v["AffId"]];
					if(empty($Resolver)) $Resolver = $BdAssignEditor[$NoticeType]["ALL"][0];
					if(empty($Resolver)) $Resolver = $BdAssignEditor["ALL"]["ALL"][0];
					
					//$value_list[] = "({$v["ID"]}, '{$NoticeType}', 'NEW', '".date("Y-m-d H:i:s")."', '".date("Y-m-d H:i:s")."', '{$Resolver}', '{$country_arr}')";
					$insertNoticeListData["Resolver"] = $Resolver;
					$insertNoticeListData["AssignCountry"] = $country_val;
				}
				
			}else{
				$Resolver = $BdAssignEditor[$NoticeType][$country_arr[0]][$v["AffId"]];
				if(empty($Resolver)) $Resolver = $BdAssignEditor[$NoticeType][$country_arr[0]][0];
				if(empty($Resolver)) $Resolver = $BdAssignEditor[$NoticeType]["ALL"][0];
				if(empty($Resolver)) $Resolver = $BdAssignEditor["ALL"]["ALL"][0];				
				
				//$value_list[] = "({$v["ID"]}, '{$NoticeType}', 'NEW', '".date("Y-m-d H:i:s")."', '".date("Y-m-d H:i:s")."', '{$Resolver}', '{$country_arr[0]}')";
				$insertNoticeListData["Resolver"] = $Resolver;
				$insertNoticeListData["AssignCountry"] = $country_val;
			}
			
			$this->insertProgramStoreNotice($insertNoticeListData);
			
			$return_count++;
		}
				
		return $return_count;
	}
	
	function addProgramStoreStatusNoticeTask(){
		$aff_arr = $this->aff_check_list;
		$return_count = 0;
		$batch_id = time();
		
		$sql = "select p.ID, p.IdInAff, p.StatusInAff, p.Partnership, p.TargetCountryInt, p.AffId, ps.Status, ps.StoreId from program as p, program_store_relationship as ps where p.id = ps.programid and p.AffId in (".implode(",",$aff_arr).") and ((p.Partnership = 'active' and p.StatusInAff = 'active' and ps.Status <> 'active') or ((p.Partnership <> 'active' OR p.StatusInAff <> 'active') and ps.Status = 'active')) and ps.IgnoreDate < '".date("Y-m-d H:i:s")."'";
		$data = array();
		$value_list = array();
		
		$BdAssignEditor = array();
		$BdAssignEditor = $this->getBdAssignEditor();
		
		$data = $this->objMysql->getRows($sql);
		//echo count($data);
		foreach($data as $v){
			$NoticeType = "PARTNERSHIP_OFF";
			if($v["Status"] != "Active"){
				$NoticeType = "PARTNERSHIP_ON";
			}
			
			//ignore ps_r lastupdatetime > ps_change log addtime 
			$check_p = array();
			//$sql = "SELECT * FROM program_store_relationship as r inner join program_change_log as c on (r.programid = c.programid) WHERE r.ProgramId = {$v["ID"]} AND r.StoreId = {$v["StoreId"]} AND c.FieldName IN ('StatusInAff','Partnership') AND r.LastUpdateTime > c.AddTime LIMIT 1";
			$sql = "SELECT * FROM program_store_relationship WHERE ProgramId = {$v["ID"]} AND StoreId = {$v["StoreId"]} AND LastUpdateTime > (SELECT MAX(`AddTime`) FROM program_change_log WHERE ProgramId = {$v["ID"]} AND FieldName IN ('StatusInAff','Partnership')) LIMIT 1";			
			$check_p = $this->objMysql->getRows($sql);
			if(count($check_p)){
				continue;
			}
			
			//ignore no change program
			$check_p = array();
			$sql = "SELECT id FROM program_change_log WHERE ProgramId = {$v["ID"]} AND FieldName IN ('StatusInAff','Partnership') LIMIT 1";
			$check_p = $this->objMysql->getRows($sql);
			if(!count($check_p)){
				continue;
			}
			
			//filter repeat task
			$has_task = array();
			$sql = "SELECT * FROM task_program_store_notice WHERE ProgramId = {$v["ID"]} AND NoticeType = '{$NoticeType}' AND StoreId = {$v["StoreId"]} AND Status = 'NEW' LIMIT 1";
			$has_task = $this->objMysql->getRows($sql);
			if(count($has_task)){
				continue;
			}
			
			//check program status
			if($NoticeType == "PARTNERSHIP_ON"){
				$check_p = array();
				//$sql = "SELECT * FROM program as p inner join program_int as pi on (p.id = pi.ProgramId and (pi.CommissionInt <> '0' or isnull(pi.CommissionInt))) WHERE p.id = '{$v["ID"]}' AND p.StatusInAff = 'active' AND p.Partnership = 'active'";
				$sql = "SELECT * FROM program as p inner join program_int as pi on (p.id = pi.ProgramId) WHERE p.id = '{$v["ID"]}' AND p.StatusInAff = 'active' AND p.Partnership = 'active'";
				$check_p = $this->objMysql->getRows($sql);
				if(!count($check_p)){
					continue;
				}
			}
			
			$insertNoticeListData = array(
				'LogId' => 'PSRELATIONSHIP_'.$v["ID"].'_'.$batch_id,
				'ProgramId' => $v["ID"],
				'StoreId' => $v["StoreId"],
				'AffId' => $v['AffId'],
				'IdInAff' => $v['IdInAff'],				
				'NoticeType' => $NoticeType,
				'NoticeReason' => 'PSRELATIONSHIP',
				'Status' => 'NEW',
				'AddTime' => date("Y-m-d H:i:s"),
				'LastUpdateTime' => date("Y-m-d H:i:s"),
				//'Resolver' => $Resolver,
				//'AssignCountry' => $AssignCountry,
			);
			
			if(empty($v["TargetCountryInt"])) $v["TargetCountryInt"] = "ALL";
			$country_arr = explode(",", $v["TargetCountryInt"]);
			if(count($country_arr) > 1){			
				foreach($country_arr as $k => $country_val){						
					$Resolver = $BdAssignEditor[$NoticeType][$country_val][$v["AffId"]];
					if(empty($Resolver)) $Resolver = $BdAssignEditor[$NoticeType][$country_val][0];
					if(!empty($Resolver)){						
						//$value_list[] = "({$v["ID"]}, '{$NoticeType}', 'NEW', '".date("Y-m-d H:i:s")."', '".date("Y-m-d H:i:s")."', '{$Resolver}', '{$country_val}')";
						$insertNoticeListData["Resolver"] = $Resolver;
						$insertNoticeListData["AssignCountry"] = $country_val;
						unset($country_arr[$k]);
					}
				}			
				if(count($country_arr)){
					$Resolver = $Resolver = $BdAssignEditor[$NoticeType]["ALL"][$v["AffId"]];
					if(empty($Resolver)) $Resolver = $BdAssignEditor[$NoticeType]["ALL"][0];
					if(empty($Resolver)) $Resolver = $BdAssignEditor["ALL"]["ALL"][0];
					
					//$value_list[] = "({$v["ID"]}, '{$NoticeType}', 'NEW', '".date("Y-m-d H:i:s")."', '".date("Y-m-d H:i:s")."', '{$Resolver}', '{$country_arr}')";
					$insertNoticeListData["Resolver"] = $Resolver;
					$insertNoticeListData["AssignCountry"] = $country_val;
				}
				
			}else{
				$Resolver = $BdAssignEditor[$NoticeType][$country_arr[0]][$v["AffId"]];
				if(empty($Resolver)) $Resolver = $BdAssignEditor[$NoticeType][$country_arr[0]][0];
				if(empty($Resolver)) $Resolver = $BdAssignEditor[$NoticeType]["ALL"][0];
				if(empty($Resolver)) $Resolver = $BdAssignEditor["ALL"]["ALL"][0];				
				
				//$value_list[] = "({$v["ID"]}, '{$NoticeType}', 'NEW', '".date("Y-m-d H:i:s")."', '".date("Y-m-d H:i:s")."', '{$Resolver}', '{$country_arr[0]}')";
				$insertNoticeListData["Resolver"] = $Resolver;
				$insertNoticeListData["AssignCountry"] = $country_val;
			}
			
			$this->insertProgramStoreNotice($insertNoticeListData);
			
			$return_count++;
		}
				
		return $return_count;		
	}
	
	function addSubProgramStoreTask(){
		//$sub_aff = array(191,160,223);
		$sub_aff = array(160,223); //not add viglink
		$return_count = 0;
		$BdAssignEditor = array();
		$BdAssignEditor = $this->getBdAssignEditor();		
		
		$batch_id = time();
		
		foreach($sub_aff as $affid){
			$prgm_arr = array();
			$sql = "SELECT p.ID, p.Homepage, p.IdInAff, p.TargetCountryInt FROM program as p inner join program_int as pi on (p.ID = pi.ProgramId and (pi.CommissionInt <> '0' or isnull(pi.CommissionInt))) WHERE p.AffId = {$affid} AND p.Partnership = 'Active' AND p.StatusInAff = 'Active' AND p.ID NOT IN (SELECT programid FROM program_store_relationship WHERE Status = 'Active')";			
			$prgm_arr = $this->objMysql->getRows($sql);			
			
			foreach($prgm_arr as $val){
				$store = array();				
				/*$domain = str_ireplace("http://", "" , $val['Homepage']);
				$domain = str_ireplace("https://", "" , $domain);
				$domain = str_ireplace("www.", "" , $domain);*/
				
				$domain = getUrlDomain($val['Homepage']);
				if(!$domain){
					$domain = $val['Homepage'];
				}
				//$sql = "SELECT s.ID, r.ID AS rid FROM store AS s INNER JOIN program_store_relationship AS r ON (s.ID = r.StoreId AND r.Status = 'Active') WHERE s.Domain = '{$domain}'";
				$sql = "SELECT ID FROM store WHERE Domain = '". addslashes($domain) ."'";
				$store = $this->objMysql->getFirstRow($sql);
				if(!count($store)) continue;
				
				$sql = "SELECT ID FROM program_store_relationship WHERE StoreId = '{$store['ID']}' AND Status = 'Active' LIMIT 1";
				$has_ps = array();
				$has_ps = $this->objMysql->getRows($sql);
				if(count($has_ps)){
					continue;
				}
				
				//filter repeat task
				$has_task = array();
				$sql = "SELECT ID FROM task_program_store_notice WHERE ProgramId = {$val["ID"]} AND NoticeType = 'PARTNERSHIP_ON' AND StoreId = {$store['ID']} AND (Status = 'NEW' OR IgnoreDate > '".date("Y-m-d H:i:s")."') LIMIT 1";
				$has_task = $this->objMysql->getRows($sql);
				if(count($has_task)){
					continue;
				}
				
				$insertNoticeListData = array(
					'LogId' => 'SUBAFFILIATE_'.$val["ID"].'_'.$batch_id,
					'ProgramId' => $val["ID"],
					'StoreId' => $store['ID'],
					'AffId' => $affid,
					'IdInAff' => $val['IdInAff'],				
					'NoticeType' => 'PARTNERSHIP_ON',
					'NoticeReason' => 'SUBAFFILIATE',
					'Status' => 'NEW',
					'AddTime' => date("Y-m-d H:i:s"),
					'LastUpdateTime' => date("Y-m-d H:i:s"),
					//'Resolver' => $Resolver,
					//'AssignCountry' => $AssignCountry,
				);
				
				if(empty($val["TargetCountryInt"])) $val["TargetCountryInt"] = "ALL";
				$country_arr = explode(",", $val["TargetCountryInt"]);
				if(count($country_arr) > 1){
					//print_r($country_arr);
					foreach($country_arr as $k => $country_val){
						//$Resolver = $BdAssignEditor[$val["AffId"]][$country_val][$noticeAndChangeType[0]];					
						$Resolver = $BdAssignEditor['PARTNERSHIP_ON'][$country_val][$val["AffId"]];
						if(empty($Resolver)) $Resolver = $BdAssignEditor['PARTNERSHIP_ON'][$country_val][0];
						if(!empty($Resolver)){						
							$insertNoticeListData["Resolver"] = $Resolver;
							$insertNoticeListData["AssignCountry"] = $country_val;
							unset($country_arr[$k]);
						}
					}
					
					if(count($country_arr)){
						$Resolver = $Resolver = $BdAssignEditor['PARTNERSHIP_ON']["ALL"][$val["AffId"]];
						if(empty($Resolver)) $Resolver = $BdAssignEditor['PARTNERSHIP_ON']["ALL"][0];
						if(empty($Resolver)) $Resolver = $BdAssignEditor["ALL"]["ALL"][0];
											
						$insertNoticeListData["Resolver"] = $Resolver;
						$insertNoticeListData["AssignCountry"] = implode(",", $country_arr);
						
					}
					
				}else{
					$Resolver = $BdAssignEditor['PARTNERSHIP_ON'][$country_arr[0]][$val["AffId"]];
					if(empty($Resolver)) $Resolver = $BdAssignEditor['PARTNERSHIP_ON'][$country_arr[0]][0];
					if(empty($Resolver)) $Resolver = $BdAssignEditor['PARTNERSHIP_ON']["ALL"][0];
					if(empty($Resolver)) $Resolver = $BdAssignEditor["ALL"]["ALL"][0];
					
					$insertNoticeListData["Resolver"] = $Resolver;
					$insertNoticeListData["AssignCountry"] = $country_arr[0];
					
				}
				
				$this->insertProgramStoreNotice($insertNoticeListData);
			
				$return_count++;
			}
		}		
		return $return_count;		
	}
	
	//clean up repeat or offset notice partnership
	function clearnUpTaskPartnership(){	
		//$sql = "SELECT t.ID as tid, t.LogId, t.NoticeType, t.ChangeType, t.AssignCountry, t.Resolver, c.ProgramId, c.FieldName, c.FieldValueOld, c.FieldValueNew FROM task_program_notice_partnership AS t INNER JOIN program_change_log AS c ON (t.logid = c.id) WHERE c.ProgramId IN (SELECT c.ProgramId FROM task_program_notice_partnership AS t INNER JOIN program_change_log AS c ON (t.logid = c.id) WHERE t.status = 'NEW' AND c.Status = 'PROCESSED' GROUP BY c.ProgramId having count(*) > 1)";
		$sql = "SELECT c.ProgramId FROM task_program_notice_partnership AS t INNER JOIN program_change_log AS c ON (t.logid = c.id) WHERE t.status = 'NEW' AND c.Status = 'PROCESSED' GROUP BY c.ProgramId having count(*) > 1";
		$data = array();
		$program_ids = array();
		$data = $this->objMysql->getRows($sql);
		foreach($data as $v){
			$program_ids[$v["ProgramId"]] = $v["ProgramId"];
		}
		
		if(count($program_ids)){
		
			$sql = "SELECT t.ID as tid, t.LogId, t.NoticeType, t.ChangeType, t.AssignCountry, t.Resolver, c.ProgramId, c.FieldName, c.FieldValueOld, c.FieldValueNew FROM task_program_notice_partnership AS t INNER JOIN program_change_log AS c ON (t.logid = c.id) WHERE c.ProgramId IN (".implode(",", array_keys($program_ids)).")";
			$data = array();
			$program = array();
			$data = $this->objMysql->getRows($sql);
			foreach($data as $v){
				$program[$v["ProgramId"]][$v["tid"]] = $v;
			}
			
			$i = 0;
			$ignored_arr = array();
			foreach($program as $v){
				$tmp_val = array();
				foreach($v as $tid => $vv){
					$i++;
					if(!count($tmp_val)){
						$tmp_val = $vv;
						continue;
					}
					//check repeat
					if($tmp_val["NoticeType"] == $vv["NoticeType"] && $tmp_val["ChangeType"] == $vv["ChangeType"] && $tmp_val["FieldName"] == $vv["FieldName"]){
						$sys_ignore_arr[$tmp_val["tid"]] = $tmp_val["tid"];					
					}
					//check offset 
					else if($tmp_val["NoticeType"] != $vv["NoticeType"] && $tmp_val["ChangeType"] != $vv["ChangeType"] && $tmp_val["FieldName"] == $vv["FieldName"]){
						$sys_ignore_arr[$tmp_val["tid"]] = $tmp_val["tid"];
						$sys_ignore_arr[$vv["tid"]] = $vv["tid"];
						$tmp_val = array();
						continue;
					}
					//check same type
					if($tmp_val["NoticeType"] == $vv["NoticeType"] && $tmp_val["ChangeType"] == $vv["ChangeType"]){
						if($tmp_val["FieldName"] == "Partnership"){
							$sys_ignore_arr[$vv["tid"]] = $vv["tid"];
							$tmp_val = array();
							continue;
						}elseif($vv["FieldName"] == "Partnership"){
							$sys_ignore_arr[$tmp_val["tid"]] = $tmp_val["tid"];						
						}			
					}
					
					
					$tmp_val = $vv;
				}
			}
			
			if(count($sys_ignore_arr)){
				$sql = "UPDATE task_program_notice_partnership SET Resolver = 'System', Status = 'IGNORED', LastUpdateTime = '".date("Y-m-d H:i:s")."' WHERE ID IN (".implode(",", array_keys($sys_ignore_arr)).")";
				$this->objMysql->query($sql);
			}
		}
		/*echo $i;
		echo "\r\n<hr>";
		echo count($sys_ignore_arr);
		echo "\r\n<hr>";*/		
		
		$sql = "select p.id as pid from program as p, program_store_relationship as ps where p.id = ps.programid and ((p.Partnership = 'active' and p.StatusInAff = 'active' and ps.Status = 'active') or ((p.Partnership <> 'active' OR p.StatusInAff <> 'active') and ps.Status <> 'active')) AND p.id IN (SELECT ProgramId FROM task_program_store_status_notice WHERE Status = 'NEW')";
		$data = array();
		$data = $this->objMysql->getRows($sql, "pid");
		
		if(count($data)){
			$sql = "UPDATE task_program_store_status_notice SET Status = 'IGNORED', Resolver = 'System', LastUpdateTime = '".date("Y-m-d H:i:s")."' WHERE ProgramId IN (".implode(",",array_keys($data)).")";
			$this->objMysql->query($sql);
		}
		
		return true;
	}
	
	//clean up repeat or offset notice partnership
	function clearnUpTaskPSNotice(){		
		$sys_ignore_arr = array();
		$notice_arr = array();
		
		//$sql = "SELECT ProgramId FROM task_program_store_notice WHERE status = 'NEW' GROUP BY ProgramId having count(*) > 1";
		$sql = "SELECT ProgramId FROM task_program_store_notice WHERE status = 'NEW' GROUP BY ProgramId";
		
		$data = array();
		$program_ids = array();
		$data = $this->objMysql->getRows($sql);
		foreach($data as $v){
			$program_ids[$v["ProgramId"]] = $v["ProgramId"];
		}
		
		if(count($program_ids)){
			$sql = "SELECT * FROM task_program_store_notice WHERE status = 'NEW' AND NoticeReason <> 'AUTOACTIVE' AND ProgramId IN (".implode(",", array_keys($program_ids)).")";
			$data = array();			
			$data = $this->objMysql->getRows($sql);
			foreach($data as $v){
				if($v['ProgramId'] && $v['StoreId']){
					$sql = "SELECT * FROM program_store_relationship WHERE ProgramId = {$v['ProgramId']} AND StoreId = {$v['StoreId']}";
					$ps_arr = $this->objMysql->getFirstRow($sql);					
					//if(!count($ps_arr))continue;
					if(count($ps_arr)){
						if(($v['NoticeType'] == 'PARTNERSHIP_ON' && $ps_arr['Status'] == 'Active') || ($v['NoticeType'] == 'PARTNERSHIP_OFF' && $ps_arr['Status'] == 'Inactive')){
							//remove notice = ps rel
							$sys_ignore_arr[] = $v['ID'];
						}
					}else{
						// remove no ps
						//not remove sub aff
						if($v['NoticeReason'] != 'SUBAFFILIATE'){							
							$sys_ignore_arr[] = $v['ID'];
						}
					}
				}
				
				$notice_arr[$v['ProgramId']][$v['ID']] = array('StoreId' => $v['StoreId'], 'NoticeType' => $v['NoticeType'], 'NoticeReason' => $v['NoticeReason']);
			}
			
			foreach($notice_arr as $pid => $v){
				$tmp_val = array();
				krsort($v);
				foreach($v as $tid => $vv){
					if(!count($tmp_val)){
						$tmp_val = $vv;
						continue;
					}
					//check repeat
					if($tmp_val["NoticeType"] == $vv["NoticeType"] && $tmp_val["NoticeReason"] == $vv["NoticeReason"] && $tmp_val["StoreId"] == $vv["StoreId"]){
						$sys_ignore_arr[] = $tid;
					}
					
					//check offset 
					else if($tmp_val["NoticeType"] != $vv["NoticeType"] && $tmp_val["NoticeReason"] == $vv["NoticeReason"] && $tmp_val["StoreId"] == $vv["StoreId"]){
						$sys_ignore_arr[] = $tid;
					}
					/*
					//check same type
					if($tmp_val["NoticeType"] == $vv["NoticeType"] && $tmp_val["NoticeReason"] == $vv["NoticeReason"]){
						if($tmp_val["FieldName"] == "Partnership"){
							$sys_ignore_arr[$vv["tid"]] = $vv["tid"];
							$tmp_val = array();
							continue;
						}elseif($vv["FieldName"] == "Partnership"){
							$sys_ignore_arr[$tmp_val["tid"]] = $tmp_val["tid"];						
						}			
					}
					*/
					
					//$tmp_val = $vv;
				}
			}
			
			if(count($sys_ignore_arr)){
				$sql = "UPDATE task_program_store_notice SET Resolver = 'System', Status = 'IGNORED', LastUpdateTime = '".date("Y-m-d H:i:s")."' WHERE ID IN (".implode(",", $sys_ignore_arr).")";
				$this->objMysql->query($sql);
			}
			
			//clean programStoreStatusNoticeTask
			/*$sql = "select p.id as pid from program as p, program_store_relationship as ps where p.id = ps.programid and ((p.Partnership = 'active' and p.StatusInAff = 'active' and ps.Status = 'active') or ((p.Partnership <> 'active' OR p.StatusInAff <> 'active') and ps.Status <> 'active')) AND p.id IN (SELECT ProgramId FROM task_program_store_notice WHERE Status = 'NEW')";			
			$data = array();
			$data = $this->objMysql->getRows($sql, "pid");			
			if(count($data)){
				$sql = "UPDATE task_program_store_notice SET Resolver = 'System', Status = 'IGNORED', LastUpdateTime = '".date("Y-m-d H:i:s")."' WHERE ProgramId IN (".implode(",",array_keys($data)).") AND Status = 'New' AND NoticeReason in ('PSRELATIONSHIP', 'PARTNERSHIP', 'AUTOACTIVE')";
				$this->objMysql->query($sql);
			}*/
			
			//clean program status != ps notice
			//$sql = "SELECT p.id as pid FROM task_program_store_notice AS t LEFT JOIN program AS p ON (p.id = t.ProgramId) WHERE t.NoticeReason = 'PARTNERSHIP' AND t.Status = 'NEW' AND ((t.NoticeType = 'PARTNERSHIP_ON' AND (p.statusinaff <> 'Active' OR p.partnership <> 'Active')) OR (t.NoticeType = 'PARTNERSHIP_OFF' AND p.statusinaff = 'Active' AND p.partnership = 'Active'))";
			$sql = "SELECT p.id AS pid, p.IdInAff, p.StatusInAff, p.Partnership,  ps.Status, ps.StoreId FROM program AS p, program_store_relationship AS ps 
 LEFT JOIN task_program_store_notice AS t ON (ps.programid = t.programid AND ps.storeid = t.storeid) WHERE p.id = ps.programid AND ((p.Partnership = 'active' AND p.StatusInAff = 'active' AND ps.Status = 'active') OR ((p.Partnership <> 'active' OR p.StatusInAff <> 'active') AND ps.Status <> 'active')) AND t.status = 'NEW'";
			$data = array();
			$data = $this->objMysql->getRows($sql, "pid");			
			if(count($data)){
				$sql = "UPDATE task_program_store_notice SET Resolver = 'System', Status = 'IGNORED', LastUpdateTime = '".date("Y-m-d H:i:s")."' WHERE ProgramId IN (".implode(",",array_keys($data)).") AND Status = 'New' AND NoticeReason = 'PARTNERSHIP'";
				$this->objMysql->query($sql);
			}
		}		
		
		
		//clean sub aff task
		$sql = "select id, programid, storeid from task_program_store_notice WHERE NoticeReason = 'SUBAFFILIATE' AND status = 'NEW'";
		$data = array();			
		$data = $this->objMysql->getRows($sql);
		foreach($data as $v){
			$sql = "SELECT id FROM program_store_relationship WHERE storeid = '{$v['storeid']}' AND Status = 'Active' LIMIT 1";
			$tmp_arr = array();
			$tmp_arr = $this->objMysql->getFirstRow($sql);
			if(count($tmp_arr)){
				$sql = "UPDATE task_program_store_notice SET Resolver = 'System', Status = 'IGNORED', LastUpdateTime = '".date("Y-m-d H:i:s")."' WHERE ID = '{$v['id']}'";
				$this->objMysql->query($sql);
			}
		}
		
		//clean new program task
		$sql = "SELECT id FROM program WHERE (partnership <> 'Active' OR statusinaff <> 'Active') AND id IN (SELECT ProgramId FROM task_program_store_notice WHERE status = 'NEW' AND (NoticeReason = 'NEW_PROGRAM' OR NoticeReason = 'SUBAFFILIATE'))";
		$data = array();
		$data = $this->objMysql->getRows($sql, "id");
		
		if(count($data)){
			$sql = "UPDATE task_program_store_notice SET Resolver = 'System', Status = 'IGNORED', LastUpdateTime = '".date("Y-m-d H:i:s")."' WHERE programid IN (".implode(",",array_keys($data)).") AND status = 'NEW' AND (NoticeReason = 'NEW_PROGRAM' OR NoticeReason = 'SUBAFFILIATE')";
			$this->objMysql->query($sql);
		}
		
		return true;
	}	
	
	function getNewActiveProgramToday(){
		$aff_arr = $this->aff_check_list;
		$sql = "SELECT * FROM program WHERE AddTime > '".date("Y-m-d")."' AND Partnership = 'Active' AND StatusInAff = 'Active' AND Creator = 'System' AND AffId IN (".implode(",",$aff_arr).")";
		$data = array();
		$data = $this->objMysql->getRows($sql, "ID");
		if(count($data)){
			return $data;
		}		
	}
	
	function getNewProgramToday($date = ''){
		if(empty($date) || (strtotime($date) < strtotime(date("Y-m-d")))) $date = date("Y-m-d");
		$aff_arr = $this->aff_check_list;
		$sql = "SELECT * FROM program WHERE AddTime > '{$date}' AND Creator = 'System' AND AffId IN (".implode(",",$aff_arr).")";
		$data = array();
		$data = $this->objMysql->getRows($sql, "ID");
		if(count($data)){
			return $data;
		}		
	}	
	
	function checkNewProgramPartnership($ids = array()){
		$sql = "SELECT DISTINCT ProgramId FROM task_program_notice_partnership WHERE ProgramId IN (".implode(",", array_keys($ids)).")";
		$data = array();		
		$data = $this->objMysql->getRows($sql,"ProgramId");
		if(count($data)){
			return $data;
		}
	}
	
	function checkNewProgramPSLog($ids = array()){
		$sql = "SELECT DISTINCT ProgramId FROM task_program_store_notice WHERE ProgramId IN (".implode(",", array_keys($ids)).")";
		$data = array();		
		$data = $this->objMysql->getRows($sql,"ProgramId");
		if(count($data)){
			return $data;
		}
	}
	
	
	function checkPSRelationshipConformity($id){
		$sql = "select p.ID from program as p, program_store_relationship as ps where p.id = ps.programid and ((p.Partnership = 'active' and p.StatusInAff = 'active' and ps.Status = 'active') or ((p.Partnership <> 'active' OR p.StatusInAff <> 'active') and ps.Status <> 'active')) and p.id = " . intval($id);
		$data = array();
		$data = $this->objMysql->getRows($sql);
		if(count($data)){
			return true;
		}
		return false;
	}
	
	function checkPSOnline($programId,$status,$user=''){
		if($status!='Active') return false;
		$sql = "SELECT b.*,s.Name as sname from bd_work_log b LEFT JOIN store s ON s.ID = b.StoreId where b.Type = 'DeclinedProgramHandle' AND b.Status ='In-Progress' AND b.ProgramId = ".$programId;
		$data = $this->objMysql->getRows($sql,"ProgramId");
		if(count($data)){
		}else{
			return false;
		}
		//change work log
		foreach($data as $v){
			$remark = !empty($v['Result']) ? $v['Result']."\n" : "";
			$remark .= "PS online from ".$v['sname'];
			$now = date("Y-m-d H:i:s");
			$tracelog = $v["TraceLog"];
			$tracelog = "{$status} by {$user} @ " . substr($now, 0, 10). "\n".$remark."\n".$tracelog;
			$bsql = "UPDATE bd_work_log SET Status = 'Positive', tracelog = '".addslashes($tracelog)."', result = '".addslashes($remark)."', lastupdatetime = '{$now}' WHERE id = {$v['ID']}";
			$this->objMysql->query($bsql);
		}
		//change program Reapply Status
		$sql = "UPDATE program_int SET ReApplyStatus = 'Positive' WHERE ProgramId = ".$programId;
		$this->objMysql->query($sql);
	}
}
?>