<?php
class CBLoginAccounts
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
	
	function get_account_by_userid($user_id)
	{
		$sql = "select * from cb_login_accounts where UUID = '" . mysql_real_escape_string($user_id) . "'";
		$row = $this->oMysql->getFirstRow($sql,"UUID");
		return $row;
	}
}
?>