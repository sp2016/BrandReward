<?php

include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');

if(isset($_POST['account']) && $_POST['account']){
	$account=$_POST['account'];
}else{
	$account="";
}
if(isset($_POST['password']) && $_POST['password']){
	$password=$_POST['password'];
}else{
	$password="";
}
if(isset($_POST['transactionCrawled']) && $_POST['transactionCrawled']){
	$transactionCrawled=$_POST['transactionCrawled'];
}else{
	$transactionCrawled="";
}
if(isset($_POST['id']) && $_POST['id']){
	$id=$_POST['id'];
}
if(isset($_POST['name']) && $_POST['name']){
	$name=addslashes($_POST['name']);
}else{
	$name="";
}
if(isset($_POST['domain']) && $_POST['domain']){
	$domain=$_POST['domain'];
}else{
	$domain="";
}
if(isset($_POST['programCrawled']) && $_POST['programCrawled']){
	$programCrawled=$_POST['programCrawled'];
}else{
	$programCrawled="";
}


$query="UPDATE wf_aff SET Account = '$account' , Name = '$name' , Domain = '$domain' , ProgramCrawled = '$programCrawled' , Password='$password' , TransactionCrawled='$transactionCrawled' WHERE Id = '$id'";
mysql_query($query);

echo json_encode($_POST);
?>