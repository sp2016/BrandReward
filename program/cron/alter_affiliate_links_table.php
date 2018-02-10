<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");


$objProgram = New ProgramDb();
$tables = array();
$tables = $objProgram->objPendingMysql->showTables('%affiliate_links_%');



echo "<< Start @ ".date("Y-m-d H:i:s")." >>\r\n";
if($tables){
    foreach ($tables as $table){
        $sql = "ALTER TABLE `$table` ADD `IsDeepLink` ENUM('YES','NO','UNKNOW') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'UNKNOW' AFTER `LastCheckTime`";
        $objProgram->objPendingMysql->query($sql);
        $sql = "ALTER TABLE `$table` ADD `Type` ENUM('promotion','link') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL AFTER `IsDeepLink`";
        $objProgram->objPendingMysql->query($sql);
        echo $table."\r\n"; 
    }
}

echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";
exit;


?>