<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');
$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('title', 'Our Team');
$objTpl->display('ourTeam.html');
?>
