<?php
include_once("../../etc/const.php");
define("PAGE_DEBUG",false);
$oMysql = new Mysql();

//$orig_password = "[this is plaintext password]"

$oUserPassword = new UserPassword($oMysql);
$account = $oUserPassword->get_account_by_username("AFFILIATE","test", "test@test.com");
if($account)
{
	echo "get_password_by_username succ\n";
	echo "test user password is: '" . $oUserPassword->decode_password($account) . "'\n";
}
?>