<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');

$u = new Account();
$sys_header['js'][] = BASE_URL.'/js/b_account.js';
$objTpl->assign('sys_header', $sys_header);
if(isset($_GET['type']) && !empty($_GET['type'])){
    $objTpl->assign('title', 'Full Help');
    $objTpl->display('fhelp.html');
}else if(isset($_GET['search']) && !empty($_GET['search'])){
    $res = $u->getanswer($_GET['search']);
    if(empty($res)){
        $count = 0;
    }else{
        $count = count($res);
    }
    $objTpl->assign('count',$count);
    $objTpl->assign('data',$res);
    $objTpl->assign('search',$_GET['search']);
    $objTpl->assign('title', 'Help');
    $objTpl->display('shelp.html');
}else{
    $objTpl->assign('title', 'Help');
    $objTpl->display('help.html');
}
?>