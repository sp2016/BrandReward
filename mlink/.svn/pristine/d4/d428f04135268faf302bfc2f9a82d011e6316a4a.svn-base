<?php 
class TableChangeLog
{
	public $oMysql;
	public $debug = false;
	public $verbose = false;
	public $cache = array();
	public function __construct($oMysql,$debug=null,$verbose=null)
	{
		$this->oMysql = $oMysql;
		if(isset($debug)) $this->debug = $debug;
		if(isset($verbose)) $this->verbose = $verbose;
	}

	function get_table_config_fields($table_name)
	{
		if(!isset($this->cache[$table_name]))
		{
			$sql = "SELECT * FROM `bo_change_log_config` WHERE TableName = '" . addslashes($table_name) . "'";
			$this->cache[$table_name] = $this->oMysql->getRows($sql);
		}
		return $this->cache[$table_name];
	}
	
	function do_log($table_name,$primary_id,$arr_old, $arr_new, $operator="")
	{
		$job_created = false;
		$job_id = "";

		$rows = $this->get_table_config_fields($table_name);
		foreach ($rows as $row)
		{
			$field_to_check = $row["FieldName"];
			if(isset($arr_new[$field_to_check]) && ($arr_old[$field_to_check] != $arr_new[$field_to_check]))
			{
				if(!$job_created)
				{
					$job_id = $this->create_job($table_name, $operator,$primary_id);
					$job_created = true;
				}
				
				if($job_id)
				{
					$this->create_job_detail($job_id,$field_to_check,$arr_old[$field_to_check],$arr_new[$field_to_check]);
				}
			}
		}
		return $job_id;
	}

	function create_job($table_name, $operator, $primary_id)
	{
		$now = date("Y-m-d H:i:s");
		$sql = "INSERT INTO bo_change_log_job(JobId,TableName,Operator,CreatedTime,TablePrimaryKeyValue) VALUES(null,'" . addslashes($table_name) . "','" . addslashes($operator) . "','$now','" . addslashes($primary_id) . "');";
		if($this->debug || $this->verbose) echo "sql: $sql\n";
		if(!$this->debug) $this->oMysql->query($sql);
		if($this->debug) return 0;
		return $this->oMysql->getLastInsertId();
	}

	function create_job_detail($job_id, $field_name,$value_from,$value_to)
	{
		$sql = "INSERT INTO bo_change_log_job_detail (JobId, FiledName,FiledValueFrom,FiledValueTo) VALUES ('$job_id','" . addslashes($field_name) . "','" . addslashes($value_from) . "','" . addslashes($value_to) . "')";
		if($this->debug || $this->verbose) echo "sql: $sql\n";
		if(!$this->debug) $this->oMysql->query($sql);
	}
}
?>