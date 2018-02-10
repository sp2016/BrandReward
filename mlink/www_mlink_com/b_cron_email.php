<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init_back.php');
$obj = new Account();

$nowtime = date("Y-m-d H:i:s");
$onehourago = date('Y-m-d H:i:s',strtotime("$nowtime-1 hour"));
$twohourago = date('Y-m-d H:i:s',strtotime("$nowtime-2 hour"));
/*此脚本加入定时任务，一小时执行一次。注册超过1小时且还未有domain信息，则认定为未填写个人信息
 * 1h < nowtime-addtime < 2h
 * 1h+addtime < nowtime < 2h+addtime
 * nowtime-2h < addtime < nowtime-1h
 *
 */
$sql = 'SELECT ID,Email FROM publisher WHERE Domain IS NULL AND AddTime > "'.$twohourago.'" AND AddTime < "'.$onehourago.'"';
$arr = $obj->getRows($sql);
if(count($arr) > 0){
    $to = 'signup@brandreward.com';
    $subject = count($arr)>1?'New Publishers':'New Publisher';
    $subject = count($arr).' '.$subject;
    $message = 'These publishers below were just registered while they left nothing except email :<br>';
    foreach($arr as $v){
        $message = $message.$v['Email'].'<br>';
    }
    $result = send_email($to,$subject,$message,'Signup Notice');
    
    
    /* $to = 'signup@brandreward.com';
    $subject = count($arr)>1?'New Publishers':'New Publisher';
    $subject = count($arr).' '.$subject;
    $message = 'These publishers below were just registered while they left nothing except email :<br>';
    foreach($arr as $v){
        $message = $message.$v['Email'].'<br>';
    }
// 当发送 HTML 电子邮件时，请始终设置 content-type
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
// 更多报头
    $headers .= 'From: <support@brandreward.com>' . "\r\n";
    $result = mail($to, $subject, $message, $headers); */
}
?>
