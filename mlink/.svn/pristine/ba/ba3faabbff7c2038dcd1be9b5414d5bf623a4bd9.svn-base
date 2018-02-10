<?php
include_once("../../etc/const.php");
$oMcryptString = new McryptString();
$source = "sjfalkdjskljfdks";
$salt = ";445454";
$encoded = $oMcryptString->encode($source,$salt);
$decoded = $oMcryptString->decode($encoded,$salt);

echo "source=$source\n";
echo "encoded=$encoded\n";
echo "decoded=$decoded\n";
?>