<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
define("PAGE_DEBUG",false);
$oMysql = new Mysql();
$oUUID = new UniqueID();
$oMcryptString = new McryptString();
$uuid_prefix = "ala";
$sql = "select LoginUserSource,LoginUserId,LoginUserName from user_password where UUID is null";
$rows = $oMysql->getRows($sql);
foreach($rows as $row)
{
	$uuid = $oUUID->get_uuid($uuid_prefix);
	$sql = "update user_password set UUID = '$uuid' where LoginUserSource = '" . mysql_real_escape_string($row["LoginUserSource"]) . "',LoginUserId = " . $row["LoginUserId"] . " and LoginUserName = '" . mysql_real_escape_string($row["LoginUserName"]) . "'";
	echo "sql: $sql\n";
	if(!PAGE_DEBUG) $oMysql->query($sql);
}
echo "=" . sizeof($rows) . " UUID updated.\n";

$oUserPassword = new UserPassword($oMysql);
$sql = "select * from user_password where LoginPasswordSalt = ''";
$rows = $oMysql->getRows($sql);
foreach($rows as $row)
{
	$encoded_info = $oMcryptString->encode_for_transfer($row["LoginEncodedPassword"],$row["LoginUserName"]);
	$sql = "update user_password set LoginEncodedPassword = '" . mysql_real_escape_string($encoded_info["EncodedString"]) . "', LoginPasswordSalt = '" . mysql_real_escape_string($encoded_info["RandSalt"]) . "' where UUID = '" . mysql_real_escape_string($row["UUID"]) . "'";
	echo "sql: $sql\n";
	if(!PAGE_DEBUG) $oMysql->query($sql);
}
echo "=" . sizeof($rows) . " password updated.\n";
?>