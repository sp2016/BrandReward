<?php
global $_cf,$_req,$_db,$_user;

echo "<< Start @ " . date("Y-m-d H:i:s") . " >>\r\n";
//找出在 2017 10-01 之前只有一条violation的publisher
$sql = "SELECT a.PublisherId AS pid, b.`Email`, COUNT(1) AS c FROM block_relationship a LEFT JOIN publisher b ON a.`PublisherId` = b.`ID` WHERE a.`Status` = 'Active' AND a.`AddTime` <= '2017-09-30 23:59:59' AND b.ViolationsStatus=0 GROUP BY a.PublisherId HAVING COUNT(1) = 1";
$checkres = $_db->getRows($sql,'pid');
$sql = "SELECT a.PublisherId AS pid, b.`Email`, COUNT(1) AS c FROM block_relationship a LEFT JOIN publisher b ON a.`PublisherId` = b.`ID` WHERE a.`Status` = 'Active' AND a.`AddTime` > '2017-09-30 23:59:59' AND b.ViolationsStatus=0 GROUP BY a.PublisherId";
$res = $_db->getRows($sql);
if(!empty($res)){
    foreach($res as $k){
        //大于一条记录关闭publisher
        if($k['c'] > 1){
            echo $k['pid']."\r\n";
            $EmailTo = $k['Email'];
            $BatchName = 'auto_'.date('YmdHis').'_'.$EmailTo.'_signupsucc';
            $time = date('Y-m-d H:i:s');
            $emailUniqueID = $BatchName.'_'.floor(rand(0,999)*10000);
            $subject = 'BRANDREWARD ACCOUNT SUSPENSION NOTICE';
            $MessageName = 'violation';
            $SITEID = "s03";
            $email_info = array(
                "method" => "bronto-template",
                "Type" => "edm_couponalert",
                "Site" => $SITEID,
                "Key" => $emailUniqueID,
                "BatchName" => $BatchName,
                "EmailTo" => $EmailTo,
                "EmailSubject" => $subject,
                "EmailFrom" => "support@brandreward.com",
                "EmailCharset" => "utf-8",
                "EmailFormat" => "HTML",
                "MessageName" => $MessageName,
                "templateMailSubject" => $subject,
                "template_publishername" => 'Brandreward',
                "template_time" => $time,
                "template_baseurl" => 'http://www.brandreward.com/',
            );
            $sql = "update publisher set Status='Inactive',ViolationsStatus=1 where ID=".$k['pid'];
            $_db->query($sql);
			$logsql = "insert into publisher_violation_log(`pid`,`email`,`addtime`)VALUES('{$k['pid']}','{$k['email']}','$time')";
            $_db->query($logsql);
            send_bronto_email($email_info);
        }else if($k['c'] == 1){
            if(isset($checkres[$k['pid']])){
                echo $k['pid']."\r\n";
                $EmailTo = $k['Email'];
                $BatchName = 'auto_'.date('YmdHis').'_'.$EmailTo.'_signupsucc';
                $time = date('Y-m-d H:i:s');
                $emailUniqueID = $BatchName.'_'.floor(rand(0,999)*10000);
                $subject = 'BRANDREWARD ACCOUNT SUSPENSION NOTICE';
                $MessageName = 'violation';
                $SITEID = "s03";
                $email_info = array(
                    "method" => "bronto-template",
                    "Type" => "edm_couponalert",
                    "Site" => $SITEID,
                    "Key" => $emailUniqueID,
                    "BatchName" => $BatchName,
                    "EmailTo" => $EmailTo,
                    "EmailSubject" => $subject,
                    "EmailFrom" => "support@brandreward.com",
                    "EmailCharset" => "utf-8",
                    "EmailFormat" => "HTML",
                    "MessageName" => $MessageName,
                    "templateMailSubject" => $subject,
                    "template_publishername" => 'Brandreward',
                    "template_time" => $time,
                    "template_baseurl" => 'http://www.brandreward.com/',
                );
                $sql = "update publisher set Status='Inactive',ViolationsStatus=1 where ID=".$k['pid'];
                $_db->query($sql);
				$logsql = "insert into publisher_violation_log(`pid`,`email`,`addtime`)VALUES('{$k['pid']}','{$k['email']}','$time')";
				$_db->query($logsql);
                send_bronto_email($email_info);
            }
        }
    }
}
//发送Email
function send_bronto_email(&$_info){
    $mailSender = "http://edm.bwe.io/sendmail.php";
    $ch = curl_init($mailSender);
    curl_setopt($ch, CURLOPT_URL,$mailSender);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
    curl_setopt($ch, CURLOPT_TIMEOUT, 90);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, "sendmail_edm");
    curl_setopt($ch, CURLOPT_ENCODING, "gzip, deflate");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$_info);
    $pagecontent = curl_exec($ch);
    if(isset($_info["RemoteDebug"]) && $_info["RemoteDebug"]) return $pagecontent;
    $curl_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if($curl_code != 200) return false;
    if(substr($pagecontent,0,1) != "1") return false;
    return true;

}
echo "<< End @ " . date("Y-m-d H:i:s") . " >>\r\n";
exit;

?>
