<?php
class UserPassword
{
	public $oMysql;
	public $debug = false;
	public $verbose = false;
	public function __construct($oMysql)
	{
		$this->oMysql = $oMysql;
	}
	
	function get_account_by_username($aff_source,$user_id,$aff_login_username)
	{
		$sql = "select * from user_password where LoginUserSource = '" . mysql_real_escape_string($aff_source) . "' and LoginUserId = '" . mysql_real_escape_string($user_id) . "' and LoginUserName = '" . mysql_real_escape_string($aff_login_username) . "'";
		$row = $this->oMysql->getFirstRow($sql,"UUID");
		return $row;
	}
	
	function decode_password($row)
	{
		$oMcryptString = new McryptString();
		return $oMcryptString->decode_for_transfer($row["LoginEncodedPassword"],$row["LoginUserName"],$row["LoginPasswordSalt"]);
	}
}
?>