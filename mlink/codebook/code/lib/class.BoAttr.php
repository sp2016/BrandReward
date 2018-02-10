<?php
class BoAttr
{
	public $oMysql;
	public $debug = false;
	public $verbose = false;
	public function __construct($oMysql,$debug=null,$verbose=null)
	{
		$this->oMysql = $oMysql;
		if(isset($debug)) $this->debug = $debug;
		if(isset($verbose)) $this->verbose = $verbose;
	}

	function check_update_all_bo()
	{
		$sql = "select * from bo_main";
		$rows = $this->oMysql->getRows($sql,"BoId");
		foreach($rows as $bo_id => $one_bo)
		{
			$this->check_update_one_bo($one_bo);
		}
	}

	function check_update_bo_table_existent($one_bo,$bool_is_talbe_existent)
	{
		if($bool_is_talbe_existent) $is_talbe_existent = "YES";
		else $is_talbe_existent = "NO";

		if(!isset($one_bo["IsTableExistent"]) || $one_bo["IsTableExistent"] != $is_talbe_existent)
		{
			$sql = "update `bo_main` set IsTableExistent = '$is_talbe_existent' where BoId = '" . $one_bo["BoId"] . "'";
			if($this->debug || $this->verbose) echo "sql: $sql\n";
			if(!$this->debug) $this->oMysql->query($sql);
			
			$oTableChangeLog = new TableChangeLog($this->oMysql,$this->debug);
			$arr_new = array("IsTableExistent" => $is_talbe_existent);
			$oTableChangeLog->do_log("bo_main",$one_bo["BoId"],$one_bo, $arr_new);
		}
	}
	
	function check_update_one_bo($one_bo)
	{
		$oMysqlExt = new MysqlExt($this->oMysql);
		$arr_bo_attr = $this->get_attrs_by_bo($one_bo);
		$bool_is_talbe_existent = $oMysqlExt->isTableExisting($one_bo["TableName"]);
		$this->check_update_bo_table_existent($one_bo,$bool_is_talbe_existent);
		if(!$bool_is_talbe_existent) return false;

		$arr_table_field = $oMysqlExt->getDescTable($one_bo["TableName"]);

		foreach($arr_table_field as $filed_name => $table_field_detail)
		{
			$sql = "";
			$arr_col_info = $this->get_attr_coltype_by_mysql_type($table_field_detail);

			if(isset($arr_bo_attr[$filed_name]))
			{
				$bo_attr_id = $arr_bo_attr[$filed_name]["BoAttrId"];
				$flag_is_same = true;
				if($flag_is_same && $arr_bo_attr[$filed_name]["ColType"] != $arr_col_info["col_type"]) $flag_is_same = false;
				if($flag_is_same && $arr_bo_attr[$filed_name]["ColDataSet"] != $arr_col_info["col_data_set"]) $flag_is_same = false;
				
				$new_status = $flag_is_same ? "MATCHED" : "MISMATCH";
				
				if($new_status == "MISMATCH" && $arr_bo_attr[$filed_name]["CheckStatus"] != "MISMATCH" || $new_status == "MATCHED" && $arr_bo_attr[$filed_name]["CheckStatus"] == "MISMATCH")
				{
					$sql = "update `bo_attr` set CheckStatus = '$new_status' where BoAttrId = '" . $arr_bo_attr[$filed_name]["BoAttrId"] . "'";
					
					$oTableChangeLog = new TableChangeLog($this->oMysql,$this->debug);
					$arr_new = array("CheckStatus" => $new_status);
					$oTableChangeLog->do_log("bo_attr",$arr_bo_attr[$filed_name]["BoAttrId"],$arr_bo_attr[$filed_name], $arr_new);
				}
				
				if($this->verbose && $flag_is_same == false)
				{
					echo "BoAttrId:$bo_attr_id ColType:" . $arr_bo_attr[$filed_name]["ColType"] . "=>" . $arr_col_info["col_type"] . ",ColDataSet:" . $arr_bo_attr[$filed_name]["ColDataSet"] . "=>" . $arr_col_info["col_data_set"] . "\n";
				}
			}
			else
			{
				//insert
				$sql = "INSERT INTO `bo_attr` (`BoAttrId`, `BoId`, `ColName`, `ColType`, `ModifiedTime`, `ColDataSet`, `RefAttrId`, `CheckStatus`) VALUES ";
				$cols = array(
					"BoAttrId" => "null",
					"BoId" => "'" . $one_bo["BoId"] . "'",
					"ColName" => "'" . $table_field_detail["Field"] . "'",
					"ColType" => "'" . $arr_col_info["col_type"] . "'",
					"ModifiedTime" => "now()",
					"ColDataSet" => "'" . addslashes($arr_col_info["col_data_set"]) . "'",
					"RefAttrId" => 0,
					"CheckStatus" => "'AUTOADDED'",
				);
				$sql .= "(" . implode(",",$cols) . ")";
  			}
  			
  			if($sql)
  			{
  				if($this->debug || $this->verbose) echo "sql: $sql\n";
				if(!$this->debug) $this->oMysql->query($sql);
  			}
		}
		
		foreach($arr_bo_attr as $filed_name => $attr_detail)
		{
			if(!isset($arr_table_field[$filed_name]))
			{
				if($arr_bo_attr[$filed_name]["CheckStatus"] != "MISSED")
				{
					$sql = "update `bo_attr` set CheckStatus = 'MISSED' where BoAttrId = '" . $arr_bo_attr[$filed_name]["BoAttrId"] . "'";
					if($this->debug || $this->verbose) echo "sql: $sql\n";
					if(!$this->debug) $this->oMysql->query($sql);
					
					$oTableChangeLog = new TableChangeLog($this->oMysql,$this->debug);
					$arr_new = array("CheckStatus" => "MISSED");
					$oTableChangeLog->do_log("bo_attr",$arr_bo_attr[$filed_name]["BoAttrId"],$attr_detail, $arr_new);
				}
			}
		}
	}
	
	function get_attr_coltype_by_mysql_type($table_field_detail)
	{
		$arr_return = array(
			"col_type" => "",
			"col_data_set" => "",
		);

		//varchar(32), int(11)
		//enum('STRING','DATETIME','ENUM','SOBJ','MOBJ','NUMBER','SET')
		$arr_type_part = explode("(",$table_field_detail["Type"]);
		$mysql_field_type = strtolower($arr_type_part[0]);

		if(isset($arr_type_part[1]))
		{
			$arr_return["col_data_set"] = str_replace(array("'",")"),"",$arr_type_part[1]);
		}
		
		$col_type = "";
		switch($mysql_field_type)
		{
			case "bit":
			case "tinyint":
			case "bool":
			case "boolean":
			case "smallint":
			case "mediumint":
			case "int":
			case "integer":
			case "bigint":
			case "float":
			case "double":
			case "decimal":
			case "dec":
				$arr_return["col_type"] = "NUMBER";break;
			case "set":
				$arr_return["col_type"] = "SET";break;
			case "enum":
				$arr_return["col_type"] = "ENUM";break;
			case "char":
			case "varchar":
			case "binary":
			case "varbinary":
			case "tinyblob":
			case "tinytext":
			case "blob":
			case "text":
			case "mediumblob":
			case "mediumtext":
			case "longblob":
			case "longtext":
				$arr_return["col_type"] = "STRING";break;
			break;
			case "date":
			case "datetime":
			case "timestamp":
			case "time":
			case "year":
				$arr_return["col_type"] = "DATETIME";break;
			default:
				die("unknown mysql_field_type $mysql_field_type");
		}

		return $arr_return;
	}
	
	/*
	function check_update_one_attr($table_field_detail)
	{
		
	}
	*/
	
	function get_attrs_by_bo($one_bo)
	{
		$sql = "select * from bo_attr where BoId = '" . $one_bo["BoId"] . "'";
		return $this->oMysql->getRows($sql,"ColName");
	}
}
?>