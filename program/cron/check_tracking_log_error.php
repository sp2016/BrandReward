<?php
include_once(dirname(dirname(__FILE__))."/etc/const.php");

$file_1 = LOG_DIR."tracking/".date("Y-m-d-H", strtotime(" -2 hour")).SERVER_NAME.".json";
$file_2 = LOG_DIR."tracking/".date("Y-m-d-H", strtotime(" -1 hour")).SERVER_NAME.".json";

if(!file_exists($file_1) || !file_exists($file_2) || (filesize($file_2) * 2) < $file_1){
	echo "warning@".date("Y-m-d H:i:s").": $file_2\r\n";
	$subject = SID." log Error";	
	SendAlert($subject, $file_2, "stanguan@megainformationtech.com, 13918713195@139.com");
}else{
	echo "fine@".date("Y-m-d H:i:s")."\r\n";
}

function SendAlert($subject, $body="", $to="", $alter_subject = true)
{
	if(!$to) $to = defined("ALERT_EMAIL") ? ALERT_EMAIL : "stanguan@megainformationtech.com";
	if ($alter_subject)
		$subject = "bdg alert: " . trim($subject);
	
	$headers = array();
	$headers[] = 'MIME-Version: 1.0';
	$headers[] = 'Content-type: text/html; charset=utf-8';
	$headers[] = "Content-Transfer-Encoding: base64";
	
	$str_header = implode("\r\n",$headers);
	$body = chunk_split(base64_encode($body));
	echo "sending alert: <$to>, $subject\n";
	return mail($to,$subject,$body,$str_header);
}

?>