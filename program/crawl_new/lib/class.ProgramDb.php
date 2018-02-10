<?php
class ProgramDb
{
	var $countryCodeVal;
	var $htmlEncodeArr = array('&#36;', '&#163;', '&#8364;', '&#8369;', '&#162;', '&#164;', '&#165;', '&#8377;', '&euro;', '&copy;', '&curren;', '&cent;', '&pound;', '&yen;');

	function __construct()
	{
		if(!isset($this->objMysql)) $this->objMysql = new MysqlPdo();

	}
	
	function  getProgramBatchName($aff_id)
    {
    	if (isset($this->programBatchDB[$aff_id])) return $this->programBatchDB[$aff_id];
    	$sql = "select name from affiliate where affid = '".addslashes($aff_id)."'";
    	$db_name = $this->objMysql->getFirstRowColumn($sql);
        $db_name = trim($db_name);
        if (($pos = strpos($db_name, "(")) !== false) $db_name = trim(substr($db_name, 0, $pos));
        $db_name = str_replace(array(" ", ".", "-"), "_", $db_name);
        $db_name = strtolower($db_name);
        if (!$db_name) mydie("get getProgramBatchName wrong");    
        $this->programBatchDB[$aff_id] = $db_name;  
        return $db_name;
    }
	
	function updateProgram($affId, $arr_info)//$arr_info是一个二维数组，键是IdInAff
	{	
// 		echo "<pre>";
// 		print_r($arr_info);
		$this->batchdb = $this->getProgramBatchName($affId);

		$arr_update = array();
		$idInAff = array_keys($arr_info);//所有键名生成一个新数组
		$sql = "SELECT IdInAff, ProgramID FROM program WHERE AffId ='".trim($affId)."' AND IdInAff IN ('".implode("','",$idInAff)."')";//查询数据库中已经存在的program
		$return_arr = $this->objMysql->getRows($sql,"IdInAff");
		foreach($return_arr as $k => $v)
		{
			if(isset($arr_info[$k]) === true)
			{
				$arr_update[$k] = $arr_info[$k];
				$arr_update[$k]['ProgramID'] = $v['ProgramID'];
				unset($arr_info[$k]);
			}
		}
		unset($return_arr);

		if(count($arr_info)){//$arr_info中存储需要插入的记录
			$this->InsertProgram($arr_info);		
		}
		if(count($arr_update)){//$arr_update中存储需要更新的记录
			$this->InsertProgramBatch($arr_update);		
		}
		
		return true;
	}
	
	function InsertProgram($arr)
	{
		$value_list = array();
		foreach($arr as $k => $v){
			$vv = array();
			$vv["ProgramID"] = uniqid('p'.substr($v["IdInAff"], 0, 2),true);
			$arr[$k]["ProgramID"] = $vv["ProgramID"];
			$vv["AffID"] = $v["AffID"];
			$vv["IdInAff"] = $v["IdInAff"];			
			$vv["AddTime"] = date("Y-m-d H:i:s");
			$vv["LastUpdateTime"] = $vv["AddTime"];
			$value_list[] = "('".implode("','", array_values($vv))."')";
		}
		$sql = "INSERT IGNORE INTO program(ProgramID, AffID, IdInAff, AddTime, LastUpdateTime) VALUES ".implode(",",$value_list);
		try
		{
			$this->objMysql->query($sql);
		}
		catch (Exception $e) 
		{
			echo $e->getMessage()."\n";			
		}
		
		$this->InsertProgramBatch($arr);
	}
		
	function InsertProgramBatch($arr)
	{
		//program_batch
		$field_list = array();
		$value_list = array();
		foreach($arr as $k => $v){			
			unset($v["Partnership"]);
			unset($v["SiteID"]);
			unset($v["LastUpdateTime"]);
			unset($v["AccountID"]);
			unset($v["AffID"]);
				unset($v["IdInAff"]);
			if(!count($field_list)){
				$field_list = array_keys($v);				
			}			
			$value_list[] = "('".implode("','", array_values($v))."')";
		}
		$sql = "REPLACE INTO {$this->batchdb}_program_batch (".implode(",",$field_list).") VALUES ".implode(",",$value_list);
		try
		{
//			echo $sql;
			$this->objMysql->query($sql);
		}
		catch (Exception $e) 
		{
			echo $e->getMessage()."\n";			
		}
		
		//r_site_program
		$value_list = array();
		foreach($arr as $k => $v){
			$vv = array();
			$vv["ProgramID"] = $v["ProgramID"];
			$vv["SiteID"] = $v["SiteID"];
            $vv["BatchID"] = $v["BatchID"];
			$vv["AccountID"] = $v["AccountID"];			
			$vv["AddTime"] = date("Y-m-d H:i:s");
			$vv["LastUpdateTime"] = $vv["AddTime"];
			$vv["Partnership"] = @$v["Partnership"];
			$value_list[] = "('".implode("','", array_values($vv))."')";
		}
		$sql = "REPLACE INTO {$this->batchdb}_r_site_program_batch(ProgramID, SiteID, BatchID, AccountID, AddTime, LastUpdateTime, Partnership) VALUES ".implode(",",$value_list);
		try
		{
			$this->objMysql->query($sql);
		}
		catch (Exception $e) 
		{
			echo $e->getMessage()."\n";
		}
		
		//r_account_program
		$value_list = array();
		foreach($arr as $k => $v){
			$vv = array();
			$vv["ProgramID"] = $v["ProgramID"];
			$vv["AccountID"] = $v["AccountID"];			
			$vv["AddTime"] = date("Y-m-d H:i:s");
			$vv["LastUpdateTime"] = $vv["AddTime"];
			$value_list[] = "('".implode("','", array_values($vv))."')";
		}
		$sql = "REPLACE INTO r_account_program(ProgramID, AccountID, AddTime, LastUpdateTime) VALUES ".implode(",",$value_list);
		try
		{
			$this->objMysql->query($sql);
		}
		catch (Exception $e) 
		{
			echo $e->getMessage()."\n";			
		}
	}
	
	function syncBatchToProgram($affId, $batchId, $siteId)
	{
        echo "Sync batch data to program start @ " . date("Y-m-d H:i:s") . "\r\n";
		if(!count($affId) || !count($batchId) || !count($siteId)) {
			mydie("Params are wrong when sync batch data to program!\r\n");
		}
		$this->batchdb = $this->getProgramBatchName($affId);

		$program_id_list = array();
		
		$pos = $i = 0;
		$limit = 1;
		$warning = 100000;
		while(1){
			$sql = "SELECT * FROM {$this->batchdb}_program_batch WHERE BatchID ='".trim($batchId)."' limit $pos, $limit";
			$rSql = "SELECT * FROM {$this->batchdb}_r_site_program_batch WHERE BatchID ='".trim($batchId)."' AND SiteID='".trim($siteId)."' limit $pos, $limit";
			$program_arr = $this->objMysql->getRows($sql);
			$program_r_arr = $this->objMysql->getRows($rSql);

			if(count($program_arr)){
				$pos += $limit;
			}else{
				break;
			}
			$p_field_list = array();
			$p_value_list = array();
			foreach($program_arr as $v){
                $program_id_list[] = $v['ProgramID'];
                $p_field_list = array_keys($v);
				$value_arr = array_values($v);
				foreach($value_arr as &$val){
					$val = addslashes($val);
				}
                $p_value_list[] = "('".implode("','", $value_arr)."')";
			}
			
			$sql = "REPLACE INTO {$this->batchdb}_program (".implode(",",$p_field_list).") VALUES".implode(",",$p_value_list);
			try{			
				$this->objMysql->query($sql);
			}
			catch (Exception $e) {
				echo $e->getMessage()."\n";			
			}

            $rp_field_list = array();
            $rp_value_list = array();
            foreach($program_r_arr as $rv){
                $rp_field_list = array_keys($rv);
                $value_arr = array_values($rv);
                foreach($value_arr as &$rval){
                    $rval = addslashes($rval);
                }
                $rp_value_list[] = "('".implode("','", $value_arr)."')";
            }

            $sql = "REPLACE INTO {$this->batchdb}_r_site_program (".implode(",",$rp_field_list).") VALUES".implode(",",$rp_value_list);
            try{
                $this->objMysql->query($sql);
            }
            catch (Exception $e) {
                echo $e->getMessage()."\n";
            }

			$i++;

			if($i * $limit > $warning){
				mydie("\tsyncBatchToProgram > ". $i);
			}
		}

		//删除以前有而现在未抓取到的数据
		$sql = "DELETE FROM {$this->batchdb}_program WHERE ProgramID NOT IN ('" . join("','", $program_id_list) . "')";
		$r_sql = "DELETE FROM {$this->batchdb}_r_site_program WHERE SiteID='".trim($siteId)."' AND ProgramID NOT IN ('".join("','", $program_id_list)."')";

		try{
			$this->objMysql->query($sql);
            $this->objMysql->query($r_sql);
		}catch (PDOException $e) {
			mydie("Failed to delete the offline data : " . $e->getMessage());
		}

        echo "Sync batch data to program end @ " . date("Y-m-d H:i:s") . "\r\n";
	}
	
	function doInsertProgram($arr)
	{
		$value_list = array();
		foreach($arr as $k => $v){
			$vv = array();
			$vv["ProgramID"] = uniqid('p');
			$arr[$k]["ProgramID"] = $vv["ProgramID"];
			$vv["AffID"] = $v["AffID"];
			$vv["IdInAff"] = $v["IdInAff"];			
			$vv["AddTime"] = date("Y-m-d H:i:s");
			$vv["LastUpdateTime"] = $vv["AddTime"];
			$value_list[] = "('".implode("','", array_values($vv))."')";
		}
		$sql = "INSERT IGNORE INTO program(ProgramID, AffID, IdInAff, AddTime, LastUpdateTime) VALUES ".implode(",",$value_list);
		try
		{
			$this->objMysql->query($sql);
		}
		catch (Exception $e) 
		{
			echo $e->getMessage()."\n";			
		}
		
		//program_batch
		$field_list = array();
		$value_list = array();
		foreach($arr as $k => $v){			
			unset($v["Partnership"]);
			unset($v["SiteID"]);
			unset($v["LastUpdateTime"]);
			unset($v["AccountID"]);
			unset($v["AffID"]);
			unset($v["IdInAff"]);
			if(!count($field_list)){
				$field_list = array_keys($v);				
			}			
			$value_list[] = "('".implode("','", array_values($v))."')";
		}
		$sql = "INSERT IGNORE INTO {$this->batchdb}_program_batch (".implode(",",$field_list).") VALUES ".implode(",",$value_list);
		try
		{
			$this->objMysql->query($sql);
		}
		catch (Exception $e) 
		{
			echo $e->getMessage()."\n";			
		}
		
		//r_site_program
		$value_list = array();
		foreach($arr as $k => $v){
			$vv = array();
			$vv["ProgramID"] = $v["ProgramID"];
			$vv["SiteID"] = $v["SiteID"];
            $vv["BatchID"] = $v["BatchID"];
			$vv["AccountID"] = $v["AccountID"];			
			$vv["AddTime"] = date("Y-m-d H:i:s");
			$vv["LastUpdateTime"] = $vv["AddTime"];
			$value_list[] = "('".implode("','", array_values($vv))."')";
		}
		$sql = "INSERT IGNORE INTO {$this->batchdb}_r_site_program_batch(ProgramID, SiteID, BatchID, AccountID, AddTime, LastUpdateTime) VALUES ".implode(",",$value_list);
		try
		{
			$this->objMysql->query($sql);
		}
		catch (Exception $e) 
		{
			echo $e->getMessage()."\n";			
		}
		
		//r_account_program
		$value_list = array();
		foreach($arr as $k => $v){
			$vv = array();
			$vv["ProgramID"] = $v["ProgramID"];
			$vv["AccountID"] = $v["AccountID"];			
			$vv["AddTime"] = date("Y-m-d H:i:s");
			$vv["LastUpdateTime"] = $vv["AddTime"];
			$value_list[] = "('".implode("','", array_values($vv))."')";
		}
		$sql = "INSERT IGNORE INTO r_account_program(ProgramID, AccountID, AddTime, LastUpdateTime) VALUES ".implode(",",$value_list);
		try
		{
			$this->objMysql->query($sql);
		}
		catch (Exception $e) 
		{
			echo $e->getMessage()."\n";			
		}
	}
	
	function getLinkFromNewProgram()
	{
		
	}
	
	function doUpdateProgram($arr)
	{	
		foreach($arr as $key => $val){
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
	function insertProgramChangeLog($row, $affId) {
		if (empty($row) || !($row["ProgramID"]) || !($row["SiteID"]) || !$affId) return false;

		$programInfo = $this->getProgramByProgramID($row["ProgramID"], $row["SiteID"]);

		$allChangeData = array();
		if (isset($row) && !empty($row)) {
			$allChangeData = $this->compareFieldValue($affId, $programInfo, $row);
		}
//		print_r($allChangeData);exit;
		$insertConstantData = array(
			'ProgramID' => $programInfo['ProgramID'],
			'OldBatchID' => $programInfo['BatchID'],
            'NewBatchID' => $row['BatchID'],
		    'AddTime'   => date("Y-m-d H:i:s"), 			    
		);

		if (!empty($allChangeData['rule'])) { //规则内变化的记录log
			
			foreach ($allChangeData['rule'] as $key => $val) {	
				if($key == "LastUpdateTime" || $key == "BatchID") continue;
				$insertData = $insertConstantData;
				$insertData['FieldName'] = $key;
				//$insertData['FieldValueOld'] = $val['old'];
				//$insertData['FieldValueNew'] = $val['new'];
				//$insertData['Status'] = 'NEW';
				
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
		foreach ($allChangeData as $key => $av){
		    if($key == 'rule' && !empty($av)){
		        foreach ($av as $avk=>$avv)
		            $change_key[$avk] = $avk;
		    }
		}
		return $change_key;
		
	}
	
	function getProgramByProgramID($ProgramID, $SiteID)
	{
		$data = array();
		if (empty($ProgramID) || empty($SiteID)) return $data;
		
		$sql = "SELECT a.*,b.Partnership FROM {$this->batchdb}_program a LEFT JOIN {$this->batchdb}_r_site_program b ON a.BatchID=b.BatchID AND a.ProgramID=b.ProgramID WHERE a.ProgramID = '".addslashes($ProgramID)."' AND b.SiteID='".addslashes($SiteID)."'";
		if ($query = $this->objMysql->query($sql)) {
			$data = $this->objMysql->getRow($query);
		}
		return $data;
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

	function getCompareField($affId){
		global $compare_field;
		if(is_array($compare_field) && count($compare_field)){
			return $compare_field;
		}else{
			$sql = "SELECT Name,NeedCheckChange FROM program_prototype_rel WHERE AffID='" . trim($affId) . "'";
			$data = $this->objMysql->getRows($sql, 'Name');
			return $data;
		}
	}

	function compareFieldValue($affId, $from = array(), $to = array()) {
		$data['normal'] = $data['rule'] = array();
		$field_arr = $this->getCompareField($affId);

		if (empty($from)) return $data;
		if (empty($to)) {
			foreach ($from as $k => $v) {
			    if (!isset($field_arr[$k]['NeedCheckChange']) || $field_arr[$k]['NeedCheckChange'] == 'NO'){
			        $data['normal'][$k]['old'] = trim(stripslashes($v));
			        $data['normal'][$k]['new'] = $to[$k];
			    }else{
			        $data['rule'][$k]['old'] = trim(stripslashes($v));
			        $data['rule'][$k]['new'] = $to[$k];
			    }
			}
			return $data;
		}
		foreach ($from as $k => $v) {
		    if (!isset($to[$k]) || !strcmp($v, $to[$k])) continue;
		    if (!isset($field_arr[$k]['NeedCheckChange']) || $field_arr[$k]['NeedCheckChange'] == 'NO'){
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
			$this->objMysql->query($sql);
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

	function checkBatchDbData($affId, array $dataArr)
	{
		if (!$affId || !is_array($dataArr) || empty($dataArr)) {
			return false;
		}

		$errorFieldNameList = array();
		foreach ($dataArr as $key=>$val) {
            //如果为空值暂时先这样处理
            if (empty($val) || in_array($key,array('ProgramID', 'BatchID'))) {
            	continue;
			}

            $sql = "SELECT * FROM program_prototype_rel WHERE AffID = '" . trim($affId) . "' AND Name='" . trim($key) . "' LIMIT 100";
            $pro_rel_arr = $this->objMysql->getRows($sql);

            if (empty($pro_rel_arr)) {
            	mydie("\n\tCan't find the $key from program_prototype_rel where AffId=$affId !");
			}

			foreach ($pro_rel_arr as $value) {
                if ($value['NeedCheck'] == 'NO') {
                    continue;
                }

                //白名单处理方式
                if (isset($value['ValWhiteList']) && !empty($value['ValWhiteList'])) {
                	$ValWhiteList = strtolower($value['ValWhiteList']);
                    $whileList = explode(',', $ValWhiteList);
                    foreach ($whileList as &$wv){
                        $wv = trim($wv);
					}
                    $lowerVal = strtolower($val);
                    if (in_array($lowerVal, $whileList)) {
                        continue;
                    }elseif ($value['ValDataType'] == 'Enum') {
                        $val_arr = explode(',', $lowerVal);
                        foreach ($val_arr as $vk => &$vv) {
                            $vv = trim($vv);
                        	if (in_array($vv, $whileList)) {
                        		unset($val_arr[$vk]);
                        		$val = join(',', $val_arr);
							}
						}
					}
                }

                $ruleArr = $value;
                unset($ruleArr['ID']);
                unset($ruleArr['AffID']);
                unset($ruleArr['Name']);
                unset($ruleArr['NeedCheck']);
                unset($ruleArr['ValWhiteList']);

                if ($value['UseSpecialRule'] == 'NO') {
                	if (empty($value['PrototypeName'])) {
                		continue;
					}
                    $sql = "SELECT * FROM program_prototype WHERE PrototypeName='" . trim($value['PrototypeName']) . "'";
                    $ruleArr = $this->objMysql->getFirstRow($sql);
                    if (empty($ruleArr)) {
                        mydie("\n\tCan't find the {$value['PrototypeName']} from program_prototype!");
                    }
                }
                $result = $this->checkFieldValue($val, $ruleArr);

                if ($result['code'] == 0) {
                    echo $result['error'];
                    if (!in_array(trim($key), $errorFieldNameList)) {
                        $errorFieldNameList[] = trim($key);
                    }
                }
            }
		}
		return $errorFieldNameList;
	}

	function checkFieldValue($fieldVal, $ruleArr)
	{
		$arr_return = array('code' => 0, 'error' => '');
		if (empty($fieldVal)) {
			return array('code' => 1, 'error' => '');
		}

		if ($ruleArr['ValDataType'] == 'Float' || $ruleArr['ValDataType'] == 'Int') {
			preg_match_all('@.*(\d+\.{0,1}\d*).*@', $fieldVal, $m);
			if (!isset($m[1]) || empty($m[1])) {
                $arr_return['error'] = "\tThere can't find Float/Int data from the fieldValue:'{$fieldVal}\n\r";
                return $arr_return;
			}
			foreach ($m[1] as $mVal) {
				if (!empty($ruleArr['ValLengthMax']) && $mVal > $ruleArr['ValLengthMax']) {
                    $arr_return['error'] ="\tThe fieldValue:{$fieldVal} more than the maximum value({$ruleArr['ValLengthMax']})!\n\r";
                    return $arr_return;
				}
				if (!empty($ruleArr['ValLengthMin']) && $mVal < $ruleArr['ValLengthMin']) {
                    $arr_return['error'] ="\tThe fieldValue:{$fieldVal} less than the minimum value({$ruleArr['ValLengthMin']})!\n\r";
                    return $arr_return;
				}
			}

		} elseif ($ruleArr['ValDataType'] == 'String') {
            if (!empty($ruleArr['ValLengthMin']) && strlen($fieldVal) > $ruleArr['ValLengthMax']) {
                $arr_return['error'] ="\tThe fieldValue:{$fieldVal} more than the maximum value({$ruleArr['ValLengthMax']})!\n\r";
                return $arr_return;
            }
            if (!empty($ruleArr['ValLengthMin']) && strlen($fieldVal) < $ruleArr['ValLengthMin']) {
                $arr_return['error'] ="\tThe fieldValue:{$fieldVal} less than the minimum value({$ruleArr['ValLengthMin']})!\n\r";
                return $arr_return;
            }
            if (!empty($ruleArr['ValPattern'])) {
            	if (!preg_match($ruleArr['ValPattern'],$fieldVal)) {

            		//处理HTML转码的问题
            		if (!empty($this->htmlEncodeArr)) {
            			foreach ($this->htmlEncodeArr as $hCode) {
            				if (stripos($fieldVal, $hCode) !== false){
                                $fieldVal = html_entity_decode($fieldVal);
                                break;
							}
						}
					}
                    if (!preg_match($ruleArr['ValPattern'],$fieldVal)) {
                        $arr_return['error'] = "\tThe fieldValue:'{$fieldVal}'\n\r";
                        return $arr_return;
                    }
				}
			}
			if (!empty($ruleArr['ValStrNeedle'])) {
                if (stripos($fieldVal, $ruleArr['ValStrNeedle']) === false) {
                    $arr_return['error'] ="\tCan't find the key needle:({$ruleArr['ValStrNeedle']}) in {$fieldVal}\n\r";
                    return $arr_return;
                }
			}
		} elseif ($ruleArr['ValDataType'] == 'Datetime') {
            if (!empty($ruleArr['ValLengthMin']) && strtotime($fieldVal) > strtotime($ruleArr['ValLengthMax'])) {
                $arr_return['error'] ="\tThe fieldValue:{$fieldVal} more than the maximum value({$ruleArr['ValLengthMax']})!\n\r";
                return $arr_return;
            }
            if (!empty($ruleArr['ValLengthMin']) && strtotime($fieldVal) < strtotime(['ValLengthMin'])) {
                $arr_return['error'] ="\tThe fieldValue:{$fieldVal} less than the minimum value({$ruleArr['ValLengthMin']})!\n\r";
                return $arr_return;
            }
		} elseif ($ruleArr['ValDataType'] == 'Enum') {
			if (!empty($ruleArr['ValEnumList'])) {
				$enumList = explode(',', $ruleArr['ValEnumList']);
			} elseif (stripos($ruleArr['PrototypeName'], 'country') !== false) {
				if (!$this->countryCodeVal) {
                    $sql = 'SELECT CountryCode,CountryName FROM country_codes LIMIT 1000';
                    $ctCodeArr = $this->objMysql->getRows($sql, 'CountryCode');
                    $countryCodeArr = array_keys($ctCodeArr);
                    $ctNameArr = $this->objMysql->getRows($sql, 'CountryName');
                    $countryNameArr = array_keys($ctNameArr);
                    $enumList = array_merge($countryCodeArr, $countryNameArr);
                    $this->countryCodeVal = $enumList;
                } else {
                    $enumList = $this->countryCodeVal;
				}
			} else {
                $arr_return['error'] ="\tThe value of ValEnumList is empty!\n\r";
                return $arr_return;
			}

			foreach ($enumList as &$v) {
				$v = strtolower($v);
			}

			if (!in_array(strtolower($fieldVal), $enumList)) {
                $fieldValArr = explode(',', strtolower($fieldVal));
                foreach ($fieldValArr as $val) {
                	if (!in_array(trim($val),$enumList)) {
                        $arr_return['error'] = "\tThe value:({$val}) not in The Enumlist!\n\r";
                        return $arr_return;
					}
				}
			}

		} else {
			mydie("There find wrong data type:{$ruleArr['ValDataType']}!");
		}

        $arr_return['code'] = 1;
		return $arr_return;
	}

    /*********************************************************
	 * 说明：该函数用来检测给定联盟的指定版本爬取的指定字段的正确性
     * @param $affId
     * @param $batchid
     * @param $fields 给定的检测字段列表，可以单个字段，也可以是以‘,’链接的多个字段，也可以是字段组成的数组，默认为检查全部字段。
     * @return array 返回值若为空数组则表示所有值都通过检测，
	 * 				若存在不满足条件的值，则返回array('batchId'=>xx,'errorList'=>array('programId1'=>xx,'programId2'=>xx,...)形式的数组
     ********************************************************/
	function checkGiveFeildsValue($affId, $batchid, $siteid, $fields = '*')
	{
		echo "Verify batch(batchid:$batchid) data start @" . date("Y-m-d H:i:s", time()) . "\r\n";
        $arr_return = array('code' => 0, 'batchId' => $batchid);

		if (!$affId || !$batchid || !$siteid || empty($fields)) {
            mydie("\r\nParameter error!");
		}

		if (is_string($fields)) {
			if (trim($fields) == '*') {
                $fields = '*,Partnership';
			}
            $fields = explode(',', $fields);
            foreach ($fields as $key => &$val) {
            	$val = trim($val);
            	if (!$val) {
            		unset($fields[$key]);
				}
			}
		}
		if (!in_array('*', $fields) && !in_array('ProgramID', $fields)) {
            $fields[] = 'ProgramID';
		}

        $this->batchdb = $this->getProgramBatchName($affId);

		//检验batchid,siteid的正确性及feilds的正确性
        $sql = "SELECT a.*,b.Partnership FROM {$this->batchdb}_program_batch a LEFT JOIN {$this->batchdb}_r_site_program_batch b ON a.BatchID=b.BatchID AND a.ProgramID=b.ProgramID WHERE a.BatchID='".trim($batchid)."' AND b.SiteID='".trim($siteid)."' limit 1";
        $firstData = $this->objMysql->getFirstRow($sql);
        if (!$firstData){
        	mydie("Can't find data from {$this->batchdb}_program_batch and {$this->batchdb}_r_site_program_batch where BatchID=$batchid and SiteID=$siteid\r\n");
		}
		$fields_arr = array_keys($firstData);

        foreach ($fields as &$val){
			if (trim($val) != '*' && !in_array($val, $fields_arr)) {
        		mydie("\nCan't find the field:$val from {$this->batchdb}_program_batch and {$this->batchdb}_r_site_program_batch!\r\n");
			}
            if (stripos($val, 'Partnership') !== false) {
                $val = 'b.' . $val;
			} else {
                $val = 'a.' . $val;
			}
		}

		if (in_array('b.Partnership', $fields)) {
        	$tempSql = "SELECT ". join(',', $fields) ." FROM {$this->batchdb}_program_batch a LEFT JOIN {$this->batchdb}_r_site_program_batch b ON a.BatchID=b.BatchID AND a.ProgramID=b.ProgramID WHERE a.BatchID='".trim($batchid)."' AND b.SiteID='".trim($siteid)."' ";
		} else {
            $tempSql = "SELECT ". join(',', $fields) ." FROM {$this->batchdb}_program_batch a WHERE a.BatchID='".trim($batchid)."' ";
		}

        $pos = $i = 0;
        $limit = 1;
        $warning = 100000;
        while(1){
            $reqSql = $tempSql . "limit $pos, $limit";
            $fieldsVal = $this->objMysql->getRows($reqSql);

            if(count($fieldsVal)){
                foreach ($fieldsVal as $val) {
                    $result = $this->checkBatchDbData($affId, $val);
                    if (!empty($result)) {
                        $arr_return['errorList'][$val['ProgramID']] = join(',', $result);
					}
                }
                $pos += $limit;
            }else{
                break;
            }
            $i++;
            if($i * $limit > $warning){
                mydie('checkBatchDataNum > '. $i);
            }
        }

		if (!isset($arr_return['errorList'])) {
            $arr_return['code'] = 1;
            $arr_return['msg'] = 'Very good, no wrong data in this batch!';
            echo "\tVery good, no wrong data in this batch! " . date("Y-m-d H:i:s", time()) . "\r\n";
		}else {
            echo "\nFailed to check the program data, there find wrong value!\n";
            print_r($arr_return);
		}

        echo "Verify batch(batchid:$batchid) data end @" . date("Y-m-d H:i:s", time()) . "\r\n";
		return $arr_return;
	}

	function checkBatchDataChange($affId, $new_batchid, $siteid)
	{
        echo "Check program change start @" . date("Y-m-d H:i:s", time()) . "\r\n";

        if (!$affId || !$new_batchid || !$siteid) {
            mydie("\r\nParameter error!");
        }
        $this->batchdb = $this->getProgramBatchName($affId);

        //保证两张表中有数据
        $sql = "SELECT a.*,b.Partnership FROM {$this->batchdb}_program_batch a LEFT JOIN {$this->batchdb}_r_site_program_batch b ON a.BatchID=b.BatchID AND a.ProgramID=b.ProgramID WHERE a.BatchID='".trim($new_batchid)."' AND b.SiteID='".trim($siteid)."' limit 1";
        $firstData = $this->objMysql->getFirstRow($sql);
        if (!$firstData){
            mydie("Can't find data from {$this->batchdb}_program_batch and {$this->batchdb}_r_site_program_batch where BatchID=$new_batchid and SiteID=$siteid\r\n");
        }

        $allProegramNum = 0;
        $programChangeNum = 0;

        $pos = $i = 0;
        $limit = 1;
        $warning = 100000;
        while(1){
            $sql = "SELECT a.*,b.Partnership,b.SiteID FROM {$this->batchdb}_program_batch a LEFT JOIN {$this->batchdb}_r_site_program_batch b ON a.BatchID=b.BatchID AND a.ProgramID=b.ProgramID WHERE a.BatchID='".trim($new_batchid)."' AND b.SiteID='".trim($siteid)."' limit $pos, $limit";
            $fieldsVal = $this->objMysql->getRows($sql);
            if(count($fieldsVal)){
                foreach ($fieldsVal as $val) {
                    $allProegramNum ++;
                    $cResult = $this->insertProgramChangeLog($val, $affId);
                    if ($cResult) {
                        $programChangeNum ++;
					}
                }
                $pos += $limit;
            } else {
                break;
            }
            $i++;
            if($i * $limit > $warning){
                mydie('The num of compareProgramBatchDataChange > '. $i);
            }
        }

        echo "\tThis batch has ($allProegramNum) programs, and ($programChangeNum) programs have changed!\r\n";

        if ($programChangeNum/$allProegramNum > 0.2) {
        	mydie("\n\tThis batch(batchid=$new_batchid) data have a big difference with programDB data!\r\n");
        	return false;
		}

        echo "Check program change end @ " . date("Y-m-d H:i:s", time()) . "\r\n";

		return true;
	}
	/*function syncProgramToPending($affid){
		if(!isset($this->objMysql)) $this->objMysql = new MysqlExt();
		
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
					$this->objMysql->query($sql);
				}
				catch (Exception $e) {
					echo $e->getMessage()."\n";			
				}
			}
		}
	}*/
	
}//end class
?>
