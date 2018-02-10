<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init_back.php');
$objDomain = new Domain;
$_POST['uid'] = $USERINFO['ID'];
$DomainArr = $objDomain->showAdvertiserDomainList($_POST);



$DomainList = $DomainArr['data'];
unset($DomainArr['data']);


$pArr = array($_POST['name']);
foreach($DomainList as $k=>$v){           //1、筛选出在出站表里的program 2、将所有program(只要有联盟关系)的名字存入数组
    if($v['Name'] && !in_array($v['Name'],$pArr)){
        $pArr[] = $v['Name'];
    }
}

$objTpl->assign('search', $_POST);
$objTpl->assign('domains', $DomainList);
$objTpl->assign('pArr', $pArr);
$objTpl->assign('sys_header', $sys_header);
echo $objTpl->fetch('b_merchant_domains.html');
?>