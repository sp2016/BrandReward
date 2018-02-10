<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');
mysql_query("SET NAMES 'utf8'");
$Publisher = new Publisher();

$managers = array(
    'monica',
    'aira',
    'sarah',
    'alain',
    'ashton',
    'ishmael',
    'nicolas',
);

$upload_res = '';
$upload_info = array();
if(isset($_REQUEST['act']) && $_REQUEST['act'] == 'upfile'){

    $upload_data = array();
    $upload_data['dir'] = INCLUDE_ROOT.'data/upload';
    $uploadFile = do_upload_file($upload_data);
    
    $file = current($uploadFile);
    if($file['res']){
        $upload_res = $Publisher->save_file_to_potential($file);
        $upload_info = $Publisher->get_potential_upload_info();
    }
}

if(isset($_REQUEST['act']) && $_REQUEST['act'] == 'confirm'){
    $Publisher->save_upload_to_potential();
}

if(isset($_REQUEST['act']) && $_REQUEST['act'] == 'cancel'){
    $Publisher->truncate_potential_upload();
}

if(isset($_REQUEST['act']) && ($_REQUEST['act'] == 'download' || $_REQUEST['act'] == 'download_coldcall') ){
    if($_REQUEST['act'] == 'download_coldcall'){
        $Publisher->publisher_potential_mail(array('datafile'=>$_REQUEST['datafile']),'coldcall_1');
    }
    
    $data = $Publisher->getPotentialData(array('datafile'=>$_REQUEST['datafile'],'pagesize'=>-1));
    list($filename) = explode('.',$_REQUEST['datafile']);
    header("Content-type:  application/octet-stream ");
    header("Accept-Ranges:  bytes ");
    header("Content-Disposition:  attachment;  filename= ".$filename.".csv");
    echo "\"COUNTRY\",\"CATEGORY\",\"URL\",\"BLOGNAME\",\"NAME\",\"EMAIL\",\"COMMENT\"\n";
    if(!empty($data)){
        foreach($data as $k=>$v){
            echo "\"".$v['country']."\",\"".$v['category']."\",\"".$v['url']."\",\"".$v['blogname']."\",\"".$v['name']."\",\"".$v['email']."\",\"".$v['comment']."\"\n";
        }
    }
    exit();
}

$upload_history = $Publisher->get_upload_history();

$objTpl->assign('upload_res',$upload_res);
$objTpl->assign('upload_info',$upload_info);
$objTpl->assign('upload_history',$upload_history);
$objTpl->assign('managers',$managers);
$sys_header['css'][] = BASE_URL.'/css/front.css';
$objTpl->assign('sys_header', $sys_header);
$objTpl->display('b_publisher_crm_upload.html');