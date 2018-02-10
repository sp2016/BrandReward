<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT . 'init.php');
$email = '';
$token = '';
$submit_time = '';
if (isset($_GET) && !empty($_GET['email']) && !empty($_GET['token']) && !empty($_GET['time'])) {
    $email = $_GET['email'];
    $token = $_GET['token'];
    $submit_time = $_GET['time'];
}else{
    echo 'URL format error';
    exit;
}
//check token
$sql = 'SELECT p.email,a.ApiKey,p.UserName,p.UserPass,p.ID FROM publisher as p left join publisher_account as a on p.ID = a.PublisherId WHERE p.Email = "'.$email.'"';
$arr = $db->getRows($sql);
$creat_token = md5($arr[0]['ID'] . $arr[0]['UserName'] . $arr[0]['UserPass']);//组合验证项
//check time
$now_time = date('Y-m-d H:i:s');
// $diff_time = date('H',strtotime($now_time) - strtotime($submit_time));
$diff_time = intval((strtotime($now_time) - strtotime($submit_time))/60/60);

if(empty($arr[0])||empty($arr[0]['email'])||$creat_token != $token||$diff_time>48){
    echo 'You are not authorized';
    exit;
}


$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('email', $email);
$objTpl->assign('creat_token', $creat_token);
$objTpl->assign('title', 'Retrieve Password');
$objTpl->display('retrievePwd.html');
?>
