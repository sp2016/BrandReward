<?php
if(!defined("__CLASS_MYSQL__")) 
{
	define("__CLASS_MYSQL__",1);
	
	class Mysql
	{
		var $host     = "";
		var $database = "";
		var $user     = "";
		var $password = "";
		var $record   = array();
		var $isPConnect = FALSE;
		var $linkID   = NULL;
		var $queryID  = NULL;
		private $unbuffer = false;

		function Mysql($database = PROD_DB_NAME, $host = PROD_DB_HOST, $user = PROD_DB_USER, $password = PROD_DB_PASS, $unbuffer = false)
		{
			$this->host     = $host;
			$this->database = $database;
			$this->user     = $user;
			$this->password = $password;
			$this->unbuffer = $unbuffer;
			$this->connect();
			$this->query('SET NAMES latin1');
			if(TIME_ZONE)
			{
				$sql = "SET time_zone = '".TIME_ZONE."'";
				$this->query($sql);
			}
		}

		function connect()
		 {
			if (is_null($this->linkID) || !is_resource($this->linkID) || strcasecmp(get_resource_type($this->linkID), "mysql link") <> 0)
			{
				if (!$this->isPConnect)
				{
					$this->linkID = @mysql_connect($this->host, $this->user, $this->password, true);
				}
				else
				{
					$this->linkID = @mysql_pconnect($this->host, $this->user, $this->password);
				}
			}
			if (!is_resource($this->linkID) || strcasecmp(get_resource_type($this->linkID), "mysql link") <> 0)
			{
				MyException::raiseError("can not connect to ".$this->host.", ".mysql_error().", ".mysql_errno(), __FILE__, __LINE__);
			}
		}
		
		function reconnect()
		{
			$this->close();
			$this->connect();
			$this->query('SET NAMES latin1');
		}
	
		function query($sql)
		{
			$result = null;
			if($sql == "") MyException::raiseError("query string was empty", __FILE__, __LINE__);
			if($this->queryID) $this->queryID = NULL;
			//by ike,
            if(!mysql_ping($this->linkID)) $this->reconnect();
			



			if (!mysql_select_db($this->database, $this->linkID))
			{
				//very strange here: sometimes changing DB was failed ...
				$this->reconnect();
				if (!mysql_select_db($this->database, $this->linkID))
				{
					//throw new MegaException("can not use the database ".$this->database.", ".mysql_error($this->linkID).", ".mysql_errno($this->linkID));
					$err_msg = "sql: $sql\n";
					$err_msg .= "can not use the database " . $this->database . ", " . mysql_error($this->linkID) . ", " . mysql_errno($this->linkID) . "\n";
					$err_msg .= "connection info: {$this->host} {$this->database} {$this->user} \n";
					MyException::raiseError($err_msg,__FILE__,__LINE__);
				}
			}


			if ($this->unbuffer)
				$this->queryID = @mysql_unbuffered_query($sql, $this->linkID);
			else
				$this->queryID = @mysql_query($sql, $this->linkID);

			if(!$this->queryID) MyException::raiseError("query failed: $sql, ".mysql_error().", ".mysql_errno(), __FILE__, __LINE__);
			return $this->queryID;
		}
		
		function selectdb($name){
			if(mysql_select_db($this->database, $this->linkID)){
				$this->database = $name;
				return true;
			}
			return false;
		}

		function getRow($queryID = "", $fetchType = MYSQL_ASSOC)
		{
			$result = array();
			if(!$queryID) $queryID = $this->queryID;
			if(!is_resource($queryID))
			{	
				MyException::raiseError("invalid query id, can not get the result from DB result", __FILE__, __LINE__);
			}
			$this->record = @mysql_fetch_array($queryID, $fetchType);
			if(is_array($this->record)) $result = $this->record;
			return $result;
		}

		function getNumRows($qryId = "")
		{
			if(is_resource($qryId)) return  @mysql_num_rows($qryId);
			return @mysql_num_rows($this->queryID);
		}

		function getAffectedRows()
		{	
			return @mysql_affected_rows($this->linkID);
		}
		
		function getLastInsertId()
		{
			return @mysql_insert_id($this->linkID);
		}
		
		function freeResult($queryID = "")
		{
			if(!is_resource($queryID)) return @mysql_free_result($this->queryID);	
			return @mysql_free_result($queryID);
		}
		
		function close()
		{
			if($this->linkID) @mysql_close($this->linkID);
			$this->linkID = null;
		}
		
		function getFirstRow(&$sql)
		{
			$rows = $this->getRows($sql);
			if(is_array($rows) && sizeof($rows) > 0) return current($rows);
			return array();
		}
		
		function getFirstRowColumn(&$sql,$keyname="")
		{
			$first_row = $this->getFirstRow($sql);
			if(sizeof($first_row) == 0) return "";
			if($keyname == "") return current($first_row);
			if(isset($first_row[$keyname])) return $first_row[$keyname];
			return "";
		}
		
		function getRows(&$sql,$keyname="",$foundrows=false)
		{
			$arr_return = array();
			if($foundrows && strpos(substr($sql,0,30),"SQL_CALC_FOUND_ROWS") === false)
			{
				if(stripos($sql,"select") === 0) $sql = "select SQL_CALC_FOUND_ROWS" . substr($sql,6);
			}
			//@file_put_contents('./esql.txt',$sql."\n",FILE_APPEND | LOCK_EX);
			$qryId = $this->query($sql);
			if(!$qryId) return $arr_return;

			if($keyname) $keys = explode(",",$keyname);
			else $i = 0;

			while($row = mysql_fetch_array($qryId,MYSQL_ASSOC))
			{
				if($keyname)
				{
					$arr_temp = array();
					foreach($keys as $key) $arr_temp[] = $row[$key];
					$key_value = implode("\t",$arr_temp);
				}
				else
				{
					$key_value = $i++;
				}
				$arr_return[$key_value] = $row;
			}
			if($foundrows) $this->getFoundRows();
			$this->freeResult($qryId);
			return $arr_return;
		}
		
		function getFoundRows()
		{
			$sql = "SELECT FOUND_ROWS()";
			$this->FOUND_ROWS = $this->getFirstRowColumn($sql);
			if(!is_numeric($this->FOUND_ROWS)) $this->FOUND_ROWS = 0;
			return $this->FOUND_ROWS;
		}
		
		function getCreateTableSql($_table_name)
		{
			$sql = "SHOW CREATE TABLE `$_table_name`";
			return $this->getFirstRowColumn($sql,"Create Table");
		}
		
		function moveTable($_table_name_from,$_table_name_to,$_table_name_backup="")
		{
			$arr_sql_rename = array();
			if($this->isTableExisting($_table_name_to))
			{
				if($_table_name_backup)
				{
					$this->dropTable($_table_name_backup);
					$arr_sql_rename[] = "`$_table_name_to` to `$_table_name_backup`";
				}
				else
				{
					$this->dropTable($_table_name_to);
				}
			}
			$arr_sql_rename[] = "`$_table_name_from` to `$_table_name_to`";
			$sql = "rename table " . implode(",",$arr_sql_rename);
			return $this->query($sql);
		}
		
		function swapTable($_table_name_1,$_table_name_2,$_table_name_swap="")
		{
			//the max length of mysql table name is 64
			if(!$_table_name_swap)
			{
				$_table_name_swap = "swap_" . $_table_name_1 . "_". $_table_name_2;
				if(strlen($_table_name_swap) > 50) $_table_name_swap = substr($_table_name_swap,0,50);
				$_table_name_swap .= "_" . time();
			}
			
			$this->dropTable($_table_name_swap);
			
			$arr_sql_rename = array();
			$arr_sql_rename[] = "`$_table_name_1` to `$_table_name_swap`";
			$arr_sql_rename[] = "`$_table_name_2` to `$_table_name_1`";
			$arr_sql_rename[] = "`$_table_name_swap` to `$_table_name_2`";
			$sql = "rename table " . implode(",",$arr_sql_rename);
			return $this->query($sql);
		}
		
		function isTableExisting($_table_name)
		{
			$sql = "SHOW TABLES LIKE '$_table_name'";
			if($this->getFirstRowColumn($sql)) return true;
			return false;
		}
		
		function showTables($_table_names)
		{
			$arr_return = array();
			$sql = "SHOW TABLES LIKE '$_table_names'";
			$arr = $this->getRows($sql);
			foreach($arr as $row)
			{
				$arr_return[] = current($row);
			}
			return $arr_return;
		}
		
		function dropTables($_table_names)
		{
			//support % in table name
			$tables = $this->showTables($_table_names);
			$this->dropTable($tables);
		}
		
		function getTableIndex($_table_name)
		{
			//Table,Non_unique,Key_name,Seq_in_index,Column_name,Index_type,Sub_part
			$arr_return = array();
			$sql = "SHOW INDEX FROM `$_table_name`";
			$arrRow = $this->getRows($sql);
			foreach($arrRow as $row)
			{
				$index_name = $row["Key_name"];//PRIMARY,index1,index2...
				$seq_in_index = $row["Seq_in_index"];//1,2,3,4,...
				$arr_return[$index_name]["details"][$seq_in_index - 1] = $row;
				$arr_return[$index_name]["Index_type"] = $row["Index_type"];//BTREE,FULLTEXT
				$arr_return[$index_name]["Non_unique"] = $row["Non_unique"];//0:unique index,1:normal index
				
				$column_with_sub_part = "`" . $row["Column_name"] . "`";
				if(is_numeric($row["Sub_part"])) $column_with_sub_part .= "(" . $row["Sub_part"] . ")";
				
				$arr_return[$index_name]["arr_column"][] = $row["Column_name"];
				$arr_return[$index_name]["arr_column_with_sub_part"][] = $column_with_sub_part;
			}
			
			foreach($arr_return as $index_name => $col_index)
			{
				$columns = implode(",",$col_index["arr_column_with_sub_part"]);
				if($index_name == "PRIMARY")
				{
					$arr_return[$index_name]["dropsql"] = "DROP PRIMARY KEY";
					$arr_return[$index_name]["addsql"] = "ADD PRIMARY KEY ($columns)";
				}
				elseif($col_index["Index_type"] == "FULLTEXT")
				{
					$arr_return[$index_name]["dropsql"] = "DROP KEY `$index_name`";
					$arr_return[$index_name]["addsql"] = "ADD FULLTEXT `$index_name` ($columns)";
				}
				elseif($col_index["Non_unique"] == 1)
				{
					$arr_return[$index_name]["dropsql"] = "DROP KEY `$index_name`";
					$arr_return[$index_name]["addsql"] = "ADD INDEX `$index_name` ($columns)";
				}
				else
				{
					$arr_return[$index_name]["dropsql"] = "DROP KEY `$index_name`";
					$arr_return[$index_name]["addsql"] = "ADD UNIQUE `$index_name` ($columns)";
				}
			}
			
			if(!empty($arr_return)) $this->lastTableIndexInfo[$_table_name] = $arr_return;
			return $arr_return;
		}
		
		function dropAllIndex($_table_name,$_index_info="")
		{
			return $this->dropIndex($_table_name,$_index_info);
		}
		
		function addAllIndex($_table_name,$_index_info="")
		{
			return $this->addIndex($_table_name,$_index_info);
		}
		
		function dropIndex($_table_name,$_index_info="",$_arr_index_name=array())
		{
			return $this->dropOrAddIndex("drop",$_table_name,$_index_info,$_arr_index_name);
		}

		function addIndex($_table_name,$_index_info="",$_arr_index_name=array())
		{
			return $this->dropOrAddIndex("add",$_table_name,$_index_info,$_arr_index_name);
		}
		
		function dropOrAddIndex($_act,$_table_name,$_index_info="",$_arr_index_name=array())
		{
			if($_index_info === "") $_index_info = $this->getTableIndex($_table_name);
			if(empty($_index_info)) return true;
			$arr_drop_sql = array();
			foreach($_index_info as $index_name => $index)
			{
				if(empty($_arr_index_name))
				{
					$arr_drop_sql[] = $index[$_act . "sql"];
				}
				else
				{
					if(in_array($index_name,$_arr_index_name))
					{
						$arr_drop_sql[] = $index[$_act . "sql"];
					}
				}
				
			}
			$sql = "ALTER TABLE `$_table_name` " . implode(",",$arr_drop_sql);
			$this->query($sql);
		}
		
		function duplicateTable($_table_name_1,$_table_name_2)
		{
			$sql = $this->getCreateTableSql($_table_name_1);
			$search_text = "CREATE TABLE `$_table_name_1`";
			$pos = strpos($sql, $search_text);
			if($pos === false || $pos > 0) die("something not right here:$sql");
			$sql = "CREATE TABLE `$_table_name_2`" . substr($sql,strlen($search_text));
			$this->query($sql);
		}
		
		function dropTable($_table_name,$_is_temp_table=false)
		{
			if(is_array($_table_name)) $_table_name = implode(",",$_table_name);
			$str_temp_table = $_is_temp_table ? "TEMPORARY" : "";
			$sql = "drop $str_temp_table table if exists $_table_name";
			$this->query($sql);
		}
	
		function getFieldNames($_table_name)
		{
			$sql = "desc $_table_name";
   			$fields = $this->getRows($sql,"Field");
   			$arr = array();
   			foreach($fields as $key => $val){
   				$arr[$key] = $key;
   			}
			return $arr;
		}
	}
}
?>
