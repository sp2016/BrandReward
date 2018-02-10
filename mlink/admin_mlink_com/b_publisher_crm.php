<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');

$Publisher = new Publisher();


$map_status = array(
    'new',
    'coldcall_1',
    'coldcall_2',
    'coldcall_3',
    'welcome_1',
    'welcome_2',
    'welcome_3',
    'active'
    );
$search = $_GET;

if(isset($search['action']) && $search['action'] == 'delete'){
    $Publisher->deletePotential($search);
}

if(isset($search['action']) && $search['action'] == 'mailed' && isset($search['mail_type']) && !empty($search['mail_type'])){
    $Publisher->publisher_potential_mail($search,$search['mail_type']);
}

$list = $Publisher->getPotentialData($search);
$page_info = $Publisher->getPotentialData($search,'pagination');
$group_status = $Publisher->getPotentialData($search,'groupstatus');
$page_html = get_page_html($page_info);

$objTpl->assign('list',$list);
$objTpl->assign('search',$search);
$objTpl->assign('group_status',$group_status);
$objTpl->assign('map_status',$map_status);
$objTpl->assign('sys_am',get_sys_am());

$objTpl->assign("title","Publisher List");
$objTpl->assign("pageHtml",$page_html);
$objTpl->assign("pageInfo",$page_info);

$sys_header['css'][] = BASE_URL.'/css/front.css';
$objTpl->assign('sys_header', $sys_header);
$objTpl->display('b_publisher_crm.html');