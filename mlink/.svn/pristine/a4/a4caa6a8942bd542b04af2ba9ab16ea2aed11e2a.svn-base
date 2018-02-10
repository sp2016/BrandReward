<?php
include_once("../../etc/const.php");
define("PAGE_DEBUG",TRUE);
$oAPIClientCodeBook = new APIClientCodeBook();
$oAPIClientCodeBook->client_id = "cla593fba4bb81d4";
$oAPIClientCodeBook->client_name = "br_aff_crawler";
$oAPIClientCodeBook->token_salt = "f3fd6e17ba6b02a3362d72205ff0917f";
$oAPIClientCodeBook->base_url = "http://codebook.ike.com/api/";

$res = $oAPIClientCodeBook->get_methods();
if($res !== false)
{
	echo "succ: \n";
	print_r($res);
}
else echo $oAPIClientCodeBook->get_error_info();

$res = $oAPIClientCodeBook->get_version();
if($res !== false)
{
	echo "succ: \n";
	print_r($res);
}
else echo $oAPIClientCodeBook->get_error_info();

$user_id = "test";
$user_name = "test@test.com";
$res = $oAPIClientCodeBook->get_aff_password($user_id,$user_name);
if($res !== false)
{
	echo "succ: ";
	print_r($res);
}
else echo $oAPIClientCodeBook->get_error_info();
?>