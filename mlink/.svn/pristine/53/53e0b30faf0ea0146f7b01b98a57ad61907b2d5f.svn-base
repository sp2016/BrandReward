<?php

include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');


if(isset($_POST['isActive']) && $_POST['isActive']){
	$isActive=$_POST['isActive'];
}else{
	$isActive="";
}

if(isset($_POST['id']) && $_POST['id']){
	$id=$_POST['id'];
}else{
	$id="";
}

if($_POST['isActive']=="YES"){
$query="UPDATE wf_aff SET IsActive = 'NO' WHERE Id = '$id'";
}
if($_POST['isActive']=="NO"){
	$query="UPDATE wf_aff SET IsActive = 'YES' WHERE Id = '$id'";
}
mysql_query($query);

echo json_encode($id);
?>