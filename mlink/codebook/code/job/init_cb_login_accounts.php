<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
define("PAGE_DEBUG",false);
$oMysql = new Mysql();
$oUUID = new UniqueID();
$oMcryptString = new McryptString();

$uuid_prefix = "cla";
$sql = "select * from cb_login_accounts where UUID = ''";
$rows = $oMysql->getRows($sql);
foreach($rows as $row)
{
	$uuid = $oUUID->get_uuid($uuid_prefix);
	$sql = "update cb_login_accounts set UUID = '$uuid' where UserName = '" . mysql_real_escape_string($row["UserName"]) . "'";
	echo "sql: $sql\n";
	if(!PAGE_DEBUG) $oMysql->query($sql);
}
echo "=" . sizeof($rows) . " UUID updated.\n";

$sql = "select * from cb_login_accounts where UserAPITokenSalt = '' or UserAPITokenSalt is null";
$rows = $oMysql->getRows($sql);
foreach($rows as $row)
{
	$rand_salt = $oMcryptString->generate_rand_salt();
	$sql = "update cb_login_accounts set UserAPITokenSalt = '" . mysql_real_escape_string($rand_salt) . "' where UUID = '" . $row["UUID"] . "'";
	echo "sql: $sql\n";
	if(!PAGE_DEBUG) $oMysql->query($sql);
}
echo "=" . sizeof($rows) . " UUID updated.\n";



?>