<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');

$objFB = new Feedback();
if(isset($_GET['eventid']) && !empty($_GET['eventid'])){
    $title = "Event [".intval($_GET['eventid'])."]";
}else{
    $title = "New Event";
}

$objTpl->assign('title',$title);
$sys_header['css'][] = BASE_URL.'/css/front.css';
$objTpl->assign('sys_header', $sys_header);
$objTpl->display('b_feedback_event.html');
