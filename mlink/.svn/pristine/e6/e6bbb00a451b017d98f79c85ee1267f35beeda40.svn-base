<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');

$objFB = new Feedback();
if(isset($_POST) && !empty($_POST)){
    if(isset($_POST['setting']) && isset($_POST['id'])){
        $save_data = array();
        foreach($_POST['setting'] as $type=>$name_list){
            foreach($name_list as $order=>$name){
                $save_data[] = array('Type'=>$type,'Name'=>$name,'LastVersion'=>date('YmdHis'),'ID'=>$_POST['id'][$type][$order],'Order'=>$order);
            }
        }
    }
    $objFB->setting_save($save_data);
}

$setting_list = $objFB->setting_get();

$objTpl->assign('setting_list',$setting_list);
$objTpl->assign('title','Feed Back Setting');
$sys_header['css'][] = BASE_URL.'/css/front.css';
$objTpl->assign('sys_header', $sys_header);
$objTpl->display('b_feedback_setting.html');