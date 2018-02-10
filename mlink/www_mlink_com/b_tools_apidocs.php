<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init_back.php');

check_user_login();
include_once('auth_ini.php');

/* $uid = $USERINFO['ID'];
$objTran = new Transaction;
$sites = $objTran->table('publisher_account')->where('PublisherId = '.intval($uid))->find(); */
$sites = array();
$i = 0;
foreach ($_SESSION['pubAccList'] as $key=>$temp){
    $sites[$i]['Domain'] = $temp['Domain'];
    $sites[$i]['ApiKey'] = $temp['ApiKey'];
    $i++;
}

if(isset($_POST['jscode_check_url'])){
    $check_url = trim($_POST['jscode_check_url']);
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL, $check_url);//需要抓取的页面路径
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_TIMEOUT, 30);
    $file_contents = curl_exec($ch);//抓取的内容放在变量中
    curl_close($ch);
    //匹配是否含有n.brandreward.com/js/br.js
    preg_match('/n\.brandreward\.com\/js\/br\.js/', $file_contents,$arr);
    if(!empty($arr)){
        //匹配是否含有{ key: 'xxxxxxxxxx' };
        preg_match('/_BRConf *= *{ *key *: *\'([A-Za-z0-9]*)\' *}/', $file_contents,$keyVal);
        if(!empty($keyVal) && isset($keyVal[1])){
            foreach ($sites as $site){
                if($site['ApiKey'] == $keyVal[1]){
                    echo 1;
                    exit;
                }
            }
        }
    }
    echo 0;
    exit;
}

$objTpl->assign('sites', $sites);

$sys_header['css'][] = BASE_URL.'/css/front.css';

$sys_footer['js'][] = BASE_URL.'/js/back.js';
$sys_footer['js'][] = BASE_URL.'/js/b_tran.js';

$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('sys_footer', $sys_footer);
$objTpl->assign('userId', $_SESSION['u']['ID']);
$objTpl->display('b_tools_apidocs.html');
?>