<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
define("PAGE_DEBUG",false);
$oMysql = new Mysql();
$oUUID = new UniqueID();
$uuid_prefix = "aff";
$sql = "select * from affiliate where UUID is null";
$rows = $oMysql->getRows($sql);
foreach($rows as $row)
{
	$uuid = $oUUID->get_uuid($uuid_prefix);
	$sql = "update affiliate set UUID = '$uuid' where AffId = '" . $row["AffId"] . "'";
	echo "sql: $sql\n";
	if(!PAGE_DEBUG) $oMysql->query($sql);
}
echo "=" . sizeof($rows) . " UUID updated.\n";
?>