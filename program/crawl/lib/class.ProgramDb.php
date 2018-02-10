<?php
class ProgramDb
{
	function __construct()
	{
		if(!isset($this->objMysql)) $this->objMysql = new MysqlExt(TASK_DB_NAME, TASK_DB_HOST, TASK_DB_USER, TASK_DB_PASS);
		if(!isset($this->objPendingMysql)) $this->objPendingMysql = new MysqlExt();
	}
	
	function updateProgram($affId, $arr_info)//$arr_info是一个二维数组，键是IdInAff
	{	
// 		echo "<pre>";
// 		print_r($arr_info);
		$arr_update = array();
		$idInAff = array_keys($arr_info);//所有键名生成一个新数组
		$sql = "SELECT IdInAff FROM program WHERE AffId = ".intval($affId)." AND IdInAff IN ('".implode("','",$idInAff)."')";//查询数据库中已经存在的program
		$return_arr = $this->objMysql->getRows($sql,"IdInAff");
		foreach($return_arr as $k => $v)
		{
			if(isset($arr_info[$k]) === true)
			{
				$arr_update[$k] = $arr_info[$k];
				unset($arr_info[$k]);
			}
		}
		unset($return_arr);
// 		echo "<pre>";
// 		print_r($arr_info);
// 		echo "<pre>";
// 		print_r($arr_update);
		if(count($arr_info)){//$arr_info中存储需要插入的记录
			$this->doInsertProgram($arr_info);		
		}
		if(count($arr_update)){//$arr_update中存储需要更新的记录
			$this->doUpdateProgram($arr_update);		
		}
		$this->setCountryInt($affId);

		$this->syncProgramToPendinglinks($affId, $idInAff);
		return true;
	}
	
	function syncProgramToPendinglinks($affId, $idInAff)
	{
		if(!count($idInAff)) return false;
		$sql = "SELECT * FROM program WHERE AffId = ".intval($affId)." AND IdInAff IN ('".implode("','",$idInAff)."')";
		$return_arr = $this->objMysql->getRows($sql);
		$field_list = array();
		$value_list = array();
		foreach($return_arr as $v){
			unset($v['LastCommissionExt']);
			unset($v['TargetCountryIntOld']);
			$field_list = array_keys($v);
			$value_arr = array_values($v);
			foreach($value_arr as &$val){
				$val = addslashes($val);
			}
			$value_list[] = "('".implode("','", $value_arr)."')";
		}
		
		$sql = "REPLACE INTO program(".implode(",",$field_list).") VALUES".implode(",",$value_list);
		try{			
			$this->objPendingMysql->query($sql);
		}
		catch (Exception $e) {
			echo $e->getMessage()."\n";			
		}		
	}
	
	function doInsertProgram($arr)
	{	
		$field_list = array();
		$value_list = array();
		foreach($arr as $k => $v){
			if(!count($field_list)){
				$field_list = array_keys($v);
				$field_list[] = "Creator";
				$field_list[] = "AddTime";
			}
			if (isset($v['Name']))
				$v['Name'] = html_entity_decode($v['Name']);
			$v["Creator"] = "System";
			$v["AddTime"] = date("Y-m-d H:i:s");
			$value_list[] = "('".implode("','", array_values($v))."')";
		}
		$sql = "INSERT IGNORE INTO program(".implode(",",$field_list).") VALUES ".implode(",",$value_list);
		try
		{
			$this->objMysql->query($sql);
		}
		catch (Exception $e) 
		{
			echo $e->getMessage()."\n";			
		}
		// delete this may lost cookie
/*
		try 
		{
			$merids = array();
			foreach ($arr as $v)
				$merids[] = $v['IdInAff'];
			$merid = implode(',', $merids);
			$cmd = sprintf("php %sjob.data.php --affid=%s --method=onepagelink --merid=%s --daemon --silent", INCLUDE_ROOT, $v['AffId'], $merid);
			echo sprintf("try to execute commad $cmd ...\n");
			system($cmd);
		}
		catch (Exception $e)
		{
			echo $e->getMessage()."\n";
		}
*/
	}
	
	function getLinkFromNewProgram()
	{
		
	}
	
	function doUpdateProgram($arr)
	{	
		foreach($arr as $key => $val){
			if (isset($val['Name']))
				$val['Name'] = html_entity_decode($val['Name']);
			$change_key = array();
			$change_key = $this->insertProgramChangeLog($val);
			$change_key["LastUpdateTime"] = 1;//temp
			if(count($change_key)){
				foreach($val as $stay_key => $tmp_v){
					if($stay_key == "LastUpdateTime" || $stay_key == "AffId" || $stay_key == "IdInAff") continue;
					if(!isset($change_key[$stay_key]) === true){
						unset($val[$stay_key]);
					}
				}
			
				$field_update = array();
				foreach($val as $k => $v){
					if(($k != "AffId") && ($k != "IdInAff")){
						//$field_update[] = "$k = '".addslashes($v)."'";
						if($k == "Homepage" || $k == "CommissionExt" || $k == "StatusInAff" || $k == "Partnership" || $k == "TargetCountryExt"){
							if(empty($v)) continue;
						}
						$field_update[] = "$k = '".$v."'";
						if($k == "StatusInAff" && $v != "Active"){
							$field_update[] = "DropDate = '".date("Y-m-d H:i:s")."'";
						}
						
						if($k == "Homepage"){							
							$sql = "SELECT id,homepage FROM program WHERE AffId = ".intval($val["AffId"])." AND IdInAff = '".addslashes($val["IdInAff"])."'";
							$tmp_pid = $this->objMysql->getFirstRow($sql);
							if(count($tmp_pid)){
								$sql = "INSERT ignore program_homepage_history (programid, homepage, changetime) values ({$tmp_pid['id']}, '".addslashes($tmp_pid['homepage'])."', '".date("Y-m-d H:i:s")."')";
								$this->objMysql->query($sql);
							}	
						}
					}
				}
				
				if(count($field_update)){
					$sql = "UPDATE program SET ".implode(",", $field_update)." WHERE AffId = ".intval($val["AffId"])." AND IdInAff = '".$val["IdInAff"]."'";
					try
					{
						$this->objMysql->query($sql);
					}
					catch (Exception $e) {
						echo $e->getMessage()."\n";
					}
				}
			}
		}
	}

	//special function for program edit
	function insertProgramChangeLog($row) {
		if (empty($row) || !is_numeric($row["AffId"])) return false;

		$programInfo = $this->getProgramByAffIdAndIdInAff($row["AffId"],$row["IdInAff"]);
		$this->setProgramPartnershipDate($programInfo, $row);
		
		$allChangeData = array();
		if (isset($row) && !empty($row)) {
			$allChangeData = $this->compareFieldValue($programInfo, $row);
		}
		
		$insertData = array();
		$insertConstantData = array(
			'ProgramId' => $programInfo['ID'],
			'IdInAff'   => $programInfo['IdInAff'],
		    'Name'      => $programInfo['Name'],
		    'AffId'     => $programInfo['AffId'],
		    'AddTime'   => date("Y-m-d H:i:s"),
		    'LastUpdateTime' => date("Y-m-d H:i:s"),
		);
		
		if (!empty($allChangeData['rule'])) { //规则内变化的记录log
			
			foreach ($allChangeData['rule'] as $key => $val) {	
				if($key == "LastUpdateTime") continue;
				$insertData = $insertConstantData;
				$insertData['FieldName'] = $key;
				$insertData['FieldValueOld'] = $val['old'];
				$insertData['FieldValueNew'] = $val['new'];
				$insertData['Status'] = 'NEW';
				
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
		//返回所有变化的字段
		$change_key = array();
		foreach ($allChangeData as $av){
		    if($av){
		        foreach ($av as $avk=>$avv)
		            $change_key[$avk] = $avk;
		    }
		}
		return $change_key;
		
	}
	
	function getProgramByAffIdAndIdInAff($AffId, $IdInAff)
	{
		$data = array();
		if (empty($AffId) || !is_numeric($AffId)) return $data;
		
		$sql = "SELECT * FROM program WHERE AffId = ".intval($AffId)." AND IdInAff = '".addslashes($IdInAff)."'";
		if ($query = $this->objMysql->query($sql)) {
			$data = $this->objMysql->getRow($query);
		}
		return $data;
	}
	
	function getAllProgramByAffId($AffId, $Fields = array())
	{
		$data = array();
		if (empty($AffId) || !is_numeric($AffId)) 
			return $data;
		
		if(!count($Fields)){
			$column = "*";
		}else{
			$column = implode(",", $Fields);
		}
			
		$sql = "SELECT $column FROM program WHERE AffId = ".intval($AffId);
		$r = $this->objMysql->getRows($sql);
		if (is_array($r))
			return $r;
		return $data;
	}

	function getCompareField(){
		global $compare_field;
		if(is_array($compare_field) && count($compare_field)){
			return $compare_field;
		}else{
			$data = array();
			$field_arr = array();
			$sql = "SELECT * FROM program_notice_cfg";
			$data = $this->objMysql->getRows($sql);
			foreach($data as $v){
				$filedsTmp = preg_replace(array("/\s+/is", "/[\\r|\\n|\\r\\n]/is"), '', trim($v['Fields']));
				$tmp_arr = array();
				$tmp_arr = explode(',', $filedsTmp);
				
				foreach($tmp_arr as $val){
					$field_arr[] = trim($val);
				}
			}
			$compare_field = array_unique($field_arr);
			return $compare_field;
		}
	}
	
	//special function for pragram edit
	//产生两个不同的数组，一个：不在规则内，且有变化， 二个：在规则内且有变化
	function compareFieldValue($from = array(), $to = array()) {
		$data['normal'] = $data['rule'] = array();
		$field_arr = array();
		$field_arr = $this->getCompareField();//only check field in cfg
		if (empty($from)) return $data;
		if (empty($to)) {
			foreach ($from as $k => $v) {
			    if (!in_array($k, $field_arr)){
			        $data['normal'][$k]['old'] = trim(stripslashes($v));
			        $data['normal'][$k]['new'] = $to[$k];
			    }else{
			        $data['rule'][$k]['old'] = trim(stripslashes($v));
			        $data['rule'][$k]['new'] = $to[$k];
			    }
				/*if (!in_array($k, $field_arr)) continue;
				$data[$k]['old'] = trim(stripslashes($v));
				$data[$k]['new'] = '';*/
			}
			return $data;
		}
		foreach ($from as $k => $v) {
		    if (!isset($to[$k]) || trim(addslashes($v)) == trim($to[$k])) continue;
		    if (!in_array($k, $field_arr)){
		        $data['normal'][$k]['old'] = trim(stripslashes($v));
		        $data['normal'][$k]['new'] = $to[$k];
		    }else{
		        $data['rule'][$k]['old'] = trim(stripslashes($v));
		        $data['rule'][$k]['new'] = $to[$k];
		    }
		    
			/*if (!in_array($k, $field_arr)) continue;//$field_arr很重要，如果更新descripton，就可以更新到数据库。如果想更新affdefaulturl，就更新不了数据库，因为continue跳出了循环
			if (!isset($to[$k]) || trim(addslashes($v)) == trim($to[$k])) continue;
			$data[$k]['old'] = trim(stripslashes($v));
			$data[$k]['new'] = $to[$k];*/
		}
		return $data;
	}
	
	//set program status to offline which not update more than 3 days
	function setProgramOffline($AffId, $prgm_arr = array()){
		/*$prgm_arr = array();
		$sql = "SELECT * FROM program WHERE AffId = ".intval($AffId)." AND LastUpdateTime < now() - interval 1 day AND StatusInAff = 'Active' AND Partnership = 'Active'";
		$prgm_arr = $this->objMysql->getRows($sql);*/
	
		foreach($prgm_arr as $v){
			$v["StatusInAff"] = 'Offline';
			foreach($v as &$vv){
				$vv = addslashes($vv);
			}
			$this->insertProgramChangeLog($v);			
			
			$sql = "UPDATE program SET StatusInAff = 'Offline', StatusInAffRemark = 'Can not find program in aff', LastUpdateTime = '".date("Y-m-d H:i:s")."' WHERE ID = {$v['ID']}";
			$this->objMysql->query($sql);
			$this->objPendingMysql->query($sql);
			//$v["Partnership"] = 'Expired';
		}
		
		$sql = "UPDATE program SET StatusInAff = 'Offline', StatusInAffRemark = 'Can not find program in aff', LastUpdateTime = '".date("Y-m-d H:i:s")."' WHERE AffId = ".intval($AffId)." AND LastUpdateTime < now() - interval 1 day AND StatusInAff = 'Active' AND Partnership <> 'Active'";
		$this->objMysql->query($sql);
	}
	
	function getNotUpdateProgram($AffId, $check_date){		
		$prgm_arr = array();
		$sql = "SELECT ID, IdInAff, AffId FROM program WHERE AffId = ".intval($AffId)." AND (LastUpdateTime < '{$check_date}' OR ISNULL(LastUpdateTime)) AND StatusInAff = 'Active' AND Partnership = 'Active'";
		$prgm_arr = $this->objMysql->getRows($sql);
		
		return $prgm_arr;
	}
	
	function getExpiredProgram($AffId){
		$prgm_arr = array();
		$sql = "SELECT ID,IdInAff FROM program WHERE AffId = ".intval($AffId)." AND Partnership = 'Expired' AND StatusInAff = 'Offline'";
		$prgm_arr = $this->objMysql->getRows($sql);
		
		return $prgm_arr;
	}
	
	function getSecondIdInAff($AffId){
		$prgm_arr = array();
		$sql = "SELECT IdInAff,SecondIdInAff FROM program WHERE AffId = ".intval($AffId)." AND StatusInAff = 'active' AND Partnership = 'active'";
		$prgm_arr = $this->objMysql->getRows($sql,'IdInAff');
		
		return $prgm_arr;
	}
	
	
	function setProgramPartnershipDate($old = array(), $new = array()){
		if($this->setPartnershipDropDate($old, $new)){
			$sql = "UPDATE program SET DropDate = '".date("Y-m-d H:i:s")."' WHERE ID = ".intval($old["ID"])."";
			$this->objMysql->query($sql);
		}
		if($this->setPartnershipCreateDate($old, $new)){
			$sql = "UPDATE program SET CreateDate = '".date("Y-m-d H:i:s")."' WHERE ID = ".intval($old["ID"])."";
			$this->objMysql->query($sql);
		}
	}
	
	function setPartnershipDropDate($old = array(), $new = array()) {
		//StatusInAff/Partnership offline Partnership Drop Date
		$setDropdate = false;
		if((!isset($new["DropDate"]) || intval(strtotime($new["DropDate"])) < 1)){
			if(isset($new["StatusInAff"]) && $new["StatusInAff"] != "Active"){
				if(isset($old["StatusInAff"]) && $old["StatusInAff"] == "Active"){
					$setDropdate = true;
				}
			}elseif(isset($new["Partnership"]) && $new["Partnership"] != "Active"){
				if(isset($old["Partnership"]) && $old["Partnership"] == "Active"){
					$setDropdate = true;
				}
			}
		}
		return $setDropdate;
	}
	
	function setPartnershipCreateDate($old = array(), $new = array()) {
		$setCreateDate = false;
		//echo $new["Partnership"].$old["Partnership"].$old["CreateDate"];
		if(isset($new["Partnership"]) && $new["Partnership"] == "Active" && isset($old["Partnership"]) && $old["Partnership"] != "Active" && intval(strtotime($old["CreateDate"])) < 1 && (!isset($new["CreateDate"]) || intval(strtotime($new["CreateDate"])) < 1)){
			$setCreateDate = true;			
		}
		return $setCreateDate;
	}
	
	function getCountryInt($affid){
		$country_arr = array();
		$fhandle = @fopen("ProgramTargetCountryExt.csv", 'r');
		if($fhandle){
			while($line = fgetcsv ($fhandle, 5000))
			{
				foreach($line as $k => $v) $line[$k] = trim($v);			
				if ($line[0] == '' || $line[0] == 'Affid') continue;	
				
				if($affid == $line[0]){
					preg_match("/(.*)\((.*)\)(.*)/i", $line[2], $matches);
					if(count($matches) && isset($matches[2])) $line[2] = $matches[2];
					$country_arr[] = array("ext" => $line[1], "int" => $line[2]);
				}
			}
		}
		return $country_arr;
	}
	
	function setCountryInt($affid){	
		if($affid == 32 || $affid == 12 || $affid == 3 || $affid == 30 || $affid == 8 || $affid == 50 || $affid == 58){//Avanlink,vangate,AN,OND,Avangate,Share Results,ImpactRadius US	
			$sql = "UPDATE program SET TargetCountryIntOld = 'US' WHERE AffId = $affid AND (TargetCountryIntOld = '' OR ISNULL(TargetCountryIntOld))";
			$this->objMysql->query($sql);
		}elseif($affid == 181){//	AvantLink.ca
			$sql = "UPDATE program SET TargetCountryIntOld = 'CA' WHERE AffId = $affid AND (TargetCountryIntOld = '' OR ISNULL(TargetCountryIntOld))";
			$this->objMysql->query($sql);
		}elseif($affid == 26 || $affid == 29 || $affid == 36 || $affid == 52 || $affid == 59 || $affid == 124 || $affid == 133 || $affid == 5){//affili.net UK,Paid On Results,AffiliateFuture EU,TradeTracker UK,ImpactRadius UK,TAG UK, tradedoubler,tradedoubler UK
			$sql = "UPDATE program SET TargetCountryIntOld = 'UK' WHERE AffId = $affid AND (TargetCountryIntOld = '' OR ISNULL(TargetCountryIntOld))";
			$this->objMysql->query($sql);
		}elseif($affid == 49 || $affid == 62 || $affid == 115){//TAG AU, Commission Monster, Commission Factory
			$sql = "UPDATE program SET TargetCountryIntOld = 'AU' WHERE AffId = $affid AND (TargetCountryIntOld = '' OR ISNULL(TargetCountryIntOld))";
			$this->objMysql->query($sql);
		}elseif($affid == 15 || $affid == 63 || $affid == 65 || $affid == 35){//zanox, affili.net DE, TradeTracker DE, tradedoubler DE
			$sql = "UPDATE program SET TargetCountryIntOld = 'DE' WHERE AffId = $affid AND (TargetCountryIntOld = '' OR ISNULL(TargetCountryIntOld))";
			$this->objMysql->query($sql);
		}elseif($affid == 27){//tradedoubler IE
			$sql = "UPDATE program SET TargetCountryIntOld = 'IE' WHERE AffId = $affid AND (TargetCountryIntOld = '' OR ISNULL(TargetCountryIntOld))";
			$this->objMysql->query($sql);
		}else{
			$country_arr = array();
			$country_arr = $this->getCountryInt($affid);
			if(count($country_arr)){
				foreach($country_arr as $v){			
					if(empty($v["int"])){//program name					
						$sql = "UPDATE program SET TargetCountryIntOld = '' WHERE AffId = ".intval($affid)." AND Name = '".addslashes($v["ext"])."' AND (TargetCountryIntOld = '' OR ISNULL(TargetCountryIntOld))";
					}else{					
						$sql = "UPDATE program SET TargetCountryIntOld = '".addslashes($v["int"])."' WHERE AffId = ".intval($affid)." AND TargetCountryExt = '".addslashes($v["ext"])."' AND (TargetCountryIntOld = '' OR ISNULL(TargetCountryIntOld))";
					}
					$this->objMysql->query($sql);
				}
			}
		}
		
		$this->addProgramInt($affid);
	}
	
	function addProgramInt($affid){
		$sql = "SELECT id FROM program WHERE affid = {$affid} AND id NOT IN (SELECT programid FROM program_int)";
		$prgm_arr = $this->objMysql->getRows($sql, "id");
		if(count($prgm_arr)){
			$value_list = "(".implode("),(", array_keys($prgm_arr)).")";			
			$sql = "INSERT IGNORE INTO program_int(programid) VALUES $value_list";
			$this->objMysql->query($sql);
		}
	}
	
	function updateVigLinkManually($idinaff_arr){
		if(count($idinaff_arr)){
			$sql = "update program set lastupdatetime = '".date("Y-m-d H:i:s")."' where affid = 191 and SecondIdInAff in (".implode(",", $idinaff_arr).")";
			$this->objMysql->query($sql);
		}
	}
	
	function getAffiliateUrlKeywords(){
		$return_arr = array("publicideas.com", "publicidees.com");
		$sql = "select AffiliateUrlKeywords from wf_aff where id not in (160,191,223) and IsActive = 'yes'";
		$tmp_arr = array();
		$tmp_arr = $this->objMysql->getRows($sql);
		foreach($tmp_arr as $v){
			if(trim($v['AffiliateUrlKeywords'])){
				$return_arr = array_merge($return_arr, preg_split("/[\r\n]+/", trim($v['AffiliateUrlKeywords']), -1, PREG_SPLIT_NO_EMPTY));
			}
		}
		return $return_arr;
	}

	function getPSDefaultAffUrlByIdInAff($idinaff, $affid = 0){
		$p_where = "";
		if(intval($affid) > 0){
			$p_where = " and p.affid = " . intval($affid);
		}
		$sql = "select r.AffiliateDefaultUrl from program_store_relationship as r inner join program as p on (p.id = r.programid) where p.idinaff = ".addslashes($idinaff)." and r.status = 'Active' and r.IsFake = 'NO' $p_where limit 1";
		$tmp_arr = array();
		$tmp_arr = $this->objMysql->getFirstRow($sql);
		if(isset($tmp_arr["AffiliateDefaultUrl"]) && !empty($tmp_arr["AffiliateDefaultUrl"])){
			return $tmp_arr["AffiliateDefaultUrl"];
		}else{
			return false;
		}
	}
	
	function getCommIntProgramByAffId($AffId)
	{
		$data = array();
		if (empty($AffId) || !is_numeric($AffId)) 
			return $data;
			
		$sql = "SELECT a.IdInAff FROM program a INNER JOIN program_int b ON a.id = b.programid WHERE b.commissionint = '0' AND (a.StatusInAff <> 'Active' OR a.Partnership <> 'Active') AND a.AffId = ".intval($AffId);
		$r = $this->objMysql->getRows($sql, "IdInAff");
		if (is_array($r))
			return $r;
		return $data;
	}
	
	function getClawerAff()
	{
		$data = array();
		$sql = "SELECT id FROM wf_aff WHERE ProgramCrawled = 'YES' AND isactive = 'YES'";
		$data = $this->objMysql->getRows($sql, "id");
		return $data;
	}
	
	function getNewActiveProgramByAffId($AffId, $datetime = '')
	{
		$data = array();		
		if (empty($AffId) || !is_numeric($AffId)) 
			return $data;
		
		$addtime = date("Y-m-d");
		if($datetime && $datetime > date("Y-m-d", strtotime("-2 days"))){
			$addtime = $datetime;
		}	
		$sql = "SELECT id, idinaff FROM program WHERE AffId = ".intval($AffId)." AND statusinaff = 'active' AND partnership = 'active' and AddTime >= '".addslashes($datetime)."'";
		$sql = $sql . "UNION SELECT b.id, b.idinaff FROM program_change_log a INNER JOIN program b ON a.programid = b.id WHERE b.partnership = 'active' AND b.statusinaff = 'active' AND (a.fieldname = 'partnership' OR a.fieldname = 'statusinaff') AND b.AffId = ".intval($AffId)." AND a.addtime > '".addslashes($datetime)."'";
		
		$data = $this->objMysql->getRows($sql, "id");
		return $data;
	}

	function getCountryCode()
	{
		$sql = 'SELECT CountryCode,CountryName FROM country_codes LIMIT 1000';
        $result = $this->objMysql->getRows($sql, "CountryCode");
        $data = array_map(function ($counArr) {return $counArr['CountryName'];}, $result);
        return $data;
	}

	/*function syncProgramToPending($affid){
		if(!isset($this->objPendingMysql)) $this->objPendingMysql = new MysqlExt();
		
		$sql = "SELECT * FROM program WHERE affid = {$affid}";
		$prgm_arr = $this->objMysql->getRows($sql);
		foreach($prgm_arr as $arr){
			foreach($arr as $key => $val){
				$field_update = array();
				foreach($val as $k => $v){
					if(($k != "AffId") && ($k != "IdInAff") && ($k != "ID")){
						$field_update[] = "$k = '".$v."'";
					}			
				}
				
				$sql = "UPDATE program SET ".implode(",", $field_update)." WHERE AffId = ".intval($val["AffId"])." AND IdInAff = '". addslashes($val["IdInAff"])."'";
				try{
					$this->objPendingMysql->query($sql);
				}
				catch (Exception $e) {
					echo $e->getMessage()."\n";			
				}
			}
		}
	}*/
	
}//end class
?>
