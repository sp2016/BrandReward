<?php
include_once("../../etc/const.php");
$oUUID = new UniqueID();
for($i=0;$i<20;$i++) echo $oUUID->get_uuid("",false) . "\n";
echo "=================\n";
for($i=0;$i<20;$i++) echo $oUUID->get_uuid("xx",true) . "\n";
?>