<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');

$objPayments = new Payments();
$upload_res = '';
$upload_info = array();

if(isset($_REQUEST['act']) && $_REQUEST['act'] == 'confirm'){
    $objPayments->confirm_tmp_data();
}

if(isset($_REQUEST['act']) && $_REQUEST['act'] == 'cancel'){
    $objPayments->cancel_tmp_data();
}

if(isset($_REQUEST['act']) && $_REQUEST['act'] == 'upfile'){
    $upload_data = array();
    $upload_data['dir'] = INCLUDE_ROOT.'data/payments';
    $upload_data['file_rename_pre'] = $_REQUEST['NetworkID']."_";
    $uploadFile = do_upload_file($upload_data);
    $upload_data['NetworkID'] = $_REQUEST['NetworkID'];

    $file = current($uploadFile);
    if($file['res']){
        $file['NetworkID'] = $_REQUEST['NetworkID'];
        $upload_res = $objPayments->save_file_to_tmp($file);
        $upload_info = $objPayments->get_upload_file_tmp_data();
    }
}

$search = $_GET;

$objAffiliates = new Affiliates();
$networksList = $objAffiliates->getNetworklist(array('pagesize'=>0,'IsActive'=>'YES'));
$networksList = array(array('ID'=>'1','Name'=>'Commission Junction'));


$list = $objPayments->getPaymentsData($search);

$objTpl->assign('list',$list);
$objTpl->assign("title","Payments List");
$objTpl->assign("networksList",$networksList);
// $objTpl->assign("pageHtml",$page_html);

$objTpl->assign('upload_res',$upload_res);
$objTpl->assign('upload_info',$upload_info);

$sys_header['css'][] = BASE_URL.'/css/front.css';
$objTpl->assign('sys_header', $sys_header);
$objTpl->display('b_payments_upload.html');