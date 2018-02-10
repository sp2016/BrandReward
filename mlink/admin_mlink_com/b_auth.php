<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');




$_menu = array(
    'Home' => array('file' => 'b_home.php', 'abb' => 'ho', 'sub' => 'no'),
    'Performance' => array('file' => 'b_performance.php', 'abb' => 'pe', 'sub' => 'yes'),
    'Advertiser List' => array('file' => 'b_merchants.php', 'abb' => 'me', 'sub' => 'no'),
    'Transactions' => array('file' => 'b_transaction.php', 'abb' => 'tr', 'sub' => 'no'),
    'Tools' => array('file' => '', 'abb' => 'to', 'sub' => 'yes'),
);
$_subMenu = array(
    'Performance' => array(
        'Daily Report' => array('file' => 'b_performance.php?type=daily', 'abb' => 'da'),
        'Your Advertisers' => array('file' => 'b_performance.php?type=merchants', 'abb' => 'mer'),
        'Site Report' => array('file' => 'b_performance.php?type=sites', 'abb' => 'si'),
    ),
    'Tools' => array(
        'Create Link' => array('file' => 'b_tools_createlink.php', 'abb' => 'cr'),
        'API Document' => array('file' => 'b_tools_apidocs.php', 'abb' => 'ap'),
    ),

);




if(isset($_POST['careeOld'])){
/*    echo "<pre>";
    print_r($_REQUEST);
    exit;*/
    $oldName = $_POST['careeOld'];
    $newName = $_POST['careerChange'];
    $checked = array_keys($_POST);
    unset($checked[0]);
    unset($checked[1]);
    $auth = implode('|',$checked);
    $sql = 'UPDATE publisher_auth SET Auth = "'.$auth.'" WHERE Career = "'.$oldName.'"';
    $db->query($sql);
    if(!empty($newName) && $oldName!=$newName){
        $sql = 'UPDATE publisher_auth SET Career = "'.$newName.'" WHERE Career = "'.$oldName.'"';
        $db->query($sql);
        $sql = 'UPDATE publisher SET Career = "'.$newName.'" WHERE Career = "'.$oldName.'"';
        $db->query($sql);
    }

}

if(isset($_POST['addCareer'])){
  $sql = 'INSERT INTO publisher_auth (Career) VALUES ("'.$_POST['addCareer'].'")';
    $db->query($sql);
}



$sql = 'SELECT ID,UserName,Career FROM publisher';
$publisher = $db->getRows($sql);
$sql = 'SELECT ID,Career,Auth from publisher_auth';
$arr = $db->getRows($sql);
foreach($arr as $v){
    $career[$v['Career']]['ID'] = $v['ID'];
    $career[$v['Career']]['Auth'] = explode('|',$v['Auth']);
}

$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_header['css'][] = BASE_URL.'/css/font-awesome.min.css';
$sys_header['js'][] = BASE_URL.'/js/b_account.js';
$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('publisher', $publisher);
$objTpl->assign('career', $career);
$objTpl->assign('menu', $_menu);
$objTpl->assign('subMenu', $_subMenu);
$objTpl->display('b_auth.html');
?>