<?php
/*
 * FileName: Class.NormalMerchant.mod.php
 * Author: t
 * Create Date: 2013-06-08
 * Package: package_name
 * Project: package_name
 * Remark: 
*/
if (!defined("__MOD_CLASS_MOBILESYNC__"))
{
   define("__MOD_CLASS_MOBILESYNC__",1);
   
   class MobileSync
   {
   		public $fromObjMysql;
   		public $toObjMysql;
   		public $taskObjMysql;
   		public $fromSite;
   		public $toSite;
   		private $fromOBjID;
   		private $toOBjID;
   		public $merchantMaps;
   		public $onceCnt = 100;
   		public $debug = false;
   		function MobileSync($fromObjMysql, $toObjMysql)
   		{
   			$this->fromObjMysql = $fromObjMysql;
   			$this->toObjMysql = $toObjMysql;
   		}
   		function setParas($parasArr){
   			foreach ($parasArr as $key => $value){
   				switch ($key){
   					case "fromObjMysql":
   						$this->fromObjMysql = $value;
   						break;
   					case "toObjMysql":
   						$this->toObjMysql = $value;
   						break;
   					case "fromSite":
   						$this->fromSite = $value;
   						break;
   					case "toSite":
   						$this->toSite = $value;
   						break;
   				}
   			}
   		}
   		function copy_Merchants(){
   			$rows = $this->getMappingInfo("MERCHANT", "TOADD");
			foreach($rows as $row){
				$normalMerchantInfoArr = $this->getTableByName($this->fromObjMysql, "normalmerchant", " ID = '{$row["FromObjId"]}'");
				$normalMerchantInfo = $normalMerchantInfoArr[0];
				$toOBjID = $this->copy_NormalMerchant($row["FromObjId"], $normalMerchantInfoArr);
			}	
   		}

   		/* function copyImage($fromImageName, $toImageName, $imageType){
   			return true;
   			$fromUrl = "";
   			$toUrl = "";
   			$fromShortSite = substr($this->fromSite, 2, 2);
   			$toShortSite = substr($this->toSite, 2, 2);
   			if(trim($fromImageName) == ""){
   				return true;
   			}
   			$imageTypeFinal = 'IMAGE';
   			switch($imageType){
   				case "MERCHANTRESIZED":
   					$fromUrl = $this->baseDir . "site_" . $fromShortSite . "/resizedMerImage/" ;
   					$toUrl = $this->baseDir . "site_" . $toShortSite . "/resizedMerImage/";
   					$imageTypeFinal = "RESIZEDMERIMAGE";
   					break;
   				case "MERCHANT":
   					$fromUrl = $this->baseDir . "site_" . $fromShortSite . "/merImage/";
   					$toUrl = $this->baseDir . "site_" . $toShortSite. "/merImage/";
   					$imageTypeFinal = "MERIMAGE";
   					break;
   				case "MERCHANTTHUMB":
   					$fromUrl = $this->baseDir . "site_" . $fromShortSite . "/thumbMerImage/";
   					$toUrl = $this->baseDir . "site_" . $toShortSite . "/thumbMerImage/";
   					$imageTypeFinal = "THUMBMERIMAGE";
   					break;
   				case "TAG":
   					$fromUrl = $this->baseDir . "site_" . $fromShortSite;
   					$toUrl = $this->baseDir . "site_" . $toShortSite;
   					$imageTypeFinal = "TAGIMAGE";
   					break;
   				case "COUPON":
   					$fromUrl = $this->baseDir . "site_" . $fromShortSite . "/couponImage/";
   					$toUrl = $this->baseDir . "site_" . $toShortSite . "/couponImage/";
   					$imageTypeFinal = "COUPONIMAGE";
   					break;
   			}
   			$fromFile = $fromUrl . $fromImageName;
   			if($imageType == "TAG"){
   				$tagImageNameArr = explode("/", $fromImageName);
   				$toFile = $toUrl . "/" . $tagImageNameArr[1] . "/8" . $tagImageNameArr[2];
   			}else{
   				$toFile = $toUrl . "8" . $toImageName;
   			}
   			if(!@copy($fromFile, $toFile)){
   				return false;
   			}else{
   				$this->insetIntoMoveLog("0", "0", $imageTypeFinal, $fromFile, $toFile);
   				return true;
   			}
   		} */

   		
   		function getTableByName($mysqlObj, $tableName, $whereStr = "", $id = ""){
   			$sql = "select * from $tableName ";
   			if($whereStr != ""){
   				$sql .= " where " . $whereStr;
   			}
//   			echo $sql . "\n";
			$rows = array();
			if($id == ""){
				$rows = $mysqlObj->getRows($sql);
			}else{
   				$rows = $mysqlObj->getRows($sql, $id);
			}
   			return $rows;
   		}
   		
   		function deleteTableByName($mysqlObj, $tableName, $whereStr = ""){
   			$sql = "delete from $tableName ";
   			if($whereStr != ""){
   				$sql .= " where " . $whereStr;
   			}
   			$res = true;
   			try{
   				$res = $mysqlObj->query($sql);
   			}catch(Exception $e){
   				return false;
   			}
   			return $res;
   		}
   		/*
   		 * 
   		 * @paras:  Array $values fields 
   		 *
   		 * 
   		 */
   		function updateTableByName($mysqlObj, $tableName, $values, $whereStr = ""){
   			if(count($values) < 1){
   				return true;
   			}
   			$valStr = "";
   			foreach ($values as $key => $value){
   				if($valStr == "" ){
   					$valStr = " $key = '" . addslashes($value) . "'";
   				}else{
   					$valStr .= ", $key = '" . addslashes($value) . "'";
   				}
   			}
   			
   			$sql = "update $tableName set $valStr";
   			if($whereStr != ""){
   				$sql .= " where " . $whereStr;
   			}
   			$res = true;
   			echo $sql;
   			try{
   				$res = $mysqlObj->query($sql);
   			}catch(Exception $e){
   				return false;
   			}
   			return $res;
   			
   		}
   		function insertIntoTable($objMysql, $tableName, $valueArr){
   			if(count($valueArr) == 0){
   				return false;
   			}
   			$sql = "insert into $tableName";
   			$fieldStr = "";
   			$valueStr = "";
   			foreach ($valueArr as $field => $value){
   				if($fieldStr == ""){
   					$fieldStr .= "`$field`";
   					$valueStr .= "'" . addslashes($value) . "'";
   				}else{
   					$fieldStr .= ", `$field`";
   					$valueStr .= ", '" . addslashes($value) . "'";
   				}
   			}
   			
   			$sql = $sql . "($fieldStr)values($valueStr)";
   			try{
   				$objMysql->query($sql);
   				
   				return true;
   			}catch(Exception $e){
   				return false;
   			}
   			
   			return true;
   		}
   		
		function checkAndCreateTable($table, $needCreate = true){
			$sql = "SHOW TABLES LIKE '$table'";
			$rows = $this->toObjMysql->getRows($sql);
			if(count($rows) == 0){
				if($needCreate){
					$sql = "SHOW CREATE TABLE r_mer_category";
					$row = $this->fromObjMysql->getFirstRow($sql);
					//Create table
					$sql = $row["Create Table"];
					if(trim($sql) != ""){
						$this->toObjMysql->query($sql);
					}
				}else{
					//Table not exist
					return false;
				}
			}else{
				//Table existed
				return true;
			}
			return true;
		}
   		
   		/*
   		 * @param string $tableName
   		 * @param string $where 	Sample: MerchantID = '3' and MerchantName = 'dell'
   		 * @param array $whereCheckFild 	Sample: array("MerchantID", "MerchantName")
   		 * 
   		 * */	 
   		function copyByTableName($tableName, $where = "", $whereCheckFild = array(), $newValue = array() /**/, $toObjMysql = "", $toTableName = ""){
   			if($toObjMysql == ""){
   				$toObjMysql = $this->toObjMysql;
   			}
   			$sql = "desc $tableName";
   			$filds = $this->toObjMysql->getRows($sql, "Field");
   			$fieldsNameArr= array();
   			foreach($filds as $key => $val){
   				$fieldsNameArr[$key] = $key;
   			}
   			$sql = "select * from `$tableName` ";
   			if($where != ""){
   				$sql .= " where ($where) limit " . $this->onceCnt;
   			}
   			$dataInfoArr = $this->fromObjMysql->getRows($sql);
   			if(count($dataInfoArr) == 0){
   				return true;
   			}
//   		$dataInfo = $dataInfoArr[0];
   			foreach ($dataInfoArr as $dataInfo){
   				
	   			$sqlIns = "insert into $tableName (`" . implode("`,`", $fieldsNameArr) . "`)values (";
	   			if($toTableName != ""){
	   				$sqlIns = "insert into $toTableName (`" . implode("`,`", $fieldsNameArr) . "`)values (";
	   			}
	   			$sqlInsVals = "";
	   			if(count($whereCheckFild) != 0){
	   				$chkSql = "select * from $tableName ";
	   				$tmpWhere = "";
	   				foreach ($whereCheckFild as $fieldName){
	   					if($tmpWhere == ""){
	   						if($tableName == 'normalmerchant'){
	   							$tmpWhere = " where $fieldName = '" . addslashes($newValue["Name"]) . "'";
	   						}else{
	   							$tmpWhere = " where $fieldName = '" . addslashes($dataInfo[$fieldName]) . "'";
	   						}
	   					}else{
	   						if($tableName == 'normalmerchant'){
	   							$tmpWhere = " where $fieldName = '" . addslashes($newValue["Name"]) . "'";
	   						}else{
	   							$tmpWhere .= " and $fieldName = '" . addslashes($dataInfo[$fieldName]) ."'";
	   						}
	   					}
	   				}
	   			}
	   			foreach ($filds as $fildInfo){
	   				$fieldName = $fildInfo["Field"];
	   				$fieldType = $fildInfo["Type"];
	   				$fieldKey  = $fildInfo["Key"];
	   				$fieldNull = $fildInfo["Null"];
	   				$fieldExtra= $fildInfo["Extra"];
	   				
	   				if(isset($newValue[$fieldName])){
	   					if($sqlInsVals == ""){
	   						$sqlInsVals .= "'" . addslashes($newValue[$fieldName]) . "'";
	   					}else{
	   						$sqlInsVals .= ", '" . addslashes($newValue[$fieldName]) . "'";
	   					}
	   					continue;
	   				}
	   				/* if($fieldExtra == "auto_increment" ){
	   					if($sqlInsVals == ""){
	   						$sqlInsVals .= " NULL"	;
	   					}else{
	   						$sqlInsVals .= ", NULL"	;
	   					}
	   				}else  */
	   				if(stripos($fieldType, "int") !== false ){
	   					if(trim($dataInfo[$fieldName]) == ""){
		   					if($sqlInsVals == ""){
		   						$sqlInsVals .= " 0"	;
		   					}else{
		   						$sqlInsVals .= ", 0";
		   					}
	   					}else{
	   						if($sqlInsVals == ""){
		   						$sqlInsVals .= "'" . addslashes($dataInfo[$fieldName]) . "'";
		   					}else{
		   						$sqlInsVals .= ", '" . addslashes($dataInfo[$fieldName]) . "'";
		   					}
	   					}
	   				}else if(stripos($fieldType, "char") !== false ){
	   					if($sqlInsVals == ""){
	   						$sqlInsVals .= "'" . addslashes($dataInfo[$fieldName]) . "'";
	   					}else{
	   						$sqlInsVals .= ", '" . addslashes($dataInfo[$fieldName]) . "'";
	   					}
	   				
	   				}else if(stripos($fieldType, "enum") !== false ){
	   					if($sqlInsVals == ""){
	   						if(trim($dataInfo[$fieldName]) == ""){
	   							$sqlInsVals .= "NULL";
	   						}else{
	   							$sqlInsVals .= "'" . addslashes($dataInfo[$fieldName]) . "'";
	   						}
	   					}else{
	   						if(trim($dataInfo[$fieldName]) == ""){
	   							$sqlInsVals .= ",'" . addslashes($dataInfo[$fieldName]) . "'";
	   						}else{
	   							$sqlInsVals .= ", '" . addslashes($dataInfo[$fieldName]) . "'";
	   						}
	   					}
	   				}
	   				else{
	   					if($sqlInsVals == ""){
	   						$sqlInsVals .= "'" .addslashes( $dataInfo[$fieldName]) . "'";
	   					}else{
	   						$sqlInsVals .= ", '" . addslashes( $dataInfo[$fieldName]) . "'";
	   					}
	   				}
	   				
	   			}
	   			
	   			$sql = $sqlIns . $sqlInsVals . ")";
   				$qid = $toObjMysql->query($sql);
   			}
   			$insId = $toObjMysql->getLastInsertId($qid);
   			return $insId;
   		}
   		function checkKey($tableName, $keyField, $keyFieldValue){
   			$sql = "select count(*) as cnt from $tableName where $keyField > '" . addslashes($keyFieldValue) . "'";
   			$row = $this->toObjMysql->getFirstRow($sql);
   			if($row["cnt"] > "0"){
   				return false;
   			}else{
   				return true;
   			}
   		}
   		function updateSignal($tableName, $keyField, $keyFieldValue, $latestTime = ""){
   			$sql = "update mobile_golabl_signal set KeyFieldValue = '$keyFieldValue' where TableName = '$tableName' and KeyField = '$keyField'";
   			$this->toObjMysql->query($sql);
   		}
   		function getLastKey($tableName, $keyField){
   			$sql = "select $keyField from $tableName order by $keyField desc limit 1";
   			$row = $this->toObjMysql->getFirstRow($sql);
   			return $row[$keyField];
   		}
   		//End Func
   }
}
?>
