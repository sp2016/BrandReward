<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init2.php');
$sys_am = get_sys_am();

$objAffiliates = new Affiliates();
$networksList = $objAffiliates->getNetworklist(array('pagesize'=>0,'IsActive'=>'YES'));

$objFb = new Feedback();
$projectList = $objFb->setting_get('Project');
$categoryList = $objFb->setting_get('Category');
$publisherList = $objFb->get_publisher_option_rows();
$userList = $objFb->get_admin_user();


$objTpl->assign('userList',$userList);
$objTpl->assign('categoryList',$categoryList);
$objTpl->assign('publisherList',$publisherList);
$objTpl->assign('networksList',$networksList);
$objTpl->assign('title','Feedback List');
$sys_header['css'][] = BASE_URL.'/css/front.css';
$objTpl->assign('sys_header', $sys_header);
$objTpl->display('b_feedback_list.html');
