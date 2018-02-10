<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');


$Publisher = new Publisher();
$id = $_POST['id'];
$type = $_POST['type'];
$res = $Publisher->loginfo($id,$type);
$objTpl->assign('res', $res);
$objTpl->assign('type',$type);
$objTpl->display('loginfo.html');