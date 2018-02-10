<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init3.php');

$whiteListModel = new WhiteList();
if(isset($_GET['name']) && isset($_GET['page'])){
    $rs = $whiteListModel->getStoreList($_GET['name'],$_GET['page']);
    echo json_encode($rs);
    exit;
}

if(isset($_POST['action'])){
    if($_POST['action']=='submit'){
        if(!isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['status']) || !isset($_POST['remark']) || !isset($_POST['store']) || !isset($_POST['id'])){
            $result['code'] = 2;
            $result['msg'] = 'miss param';
            echo json_encode($result);
            exit;
        }
        $result = $whiteListModel->saveAccount($_POST);
        echo json_encode($result);
        exit;
    }else if($_POST['action']=='getAccountDetail'){
        if(isset($_POST['id'])){
            $rs = $whiteListModel->getAccountDetail($_POST['id']);
            $result['code'] = 1;
            $result['data'] = $rs;
            echo json_encode($result);
            exit;
        }
    }
    echo 111;
    exit;
}

$accountList = $whiteListModel->getAccountList();















$objTpl->assign("title","White List Account");
$objTpl->assign("accountList",$accountList);
// $objTpl->assign("storeList",$storeList);

$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_header['css'][] = BASE_URL.'/css/bootstrap/bootstrap.min.css';
$sys_header['css'][] = BASE_URL.'/css/select2.min.css';
$sys_header['css'][] = BASE_URL.'/css/select2-bootstrap.min.css';

$sys_header['css'][] = BASE_URL.'/css/datatables/dataTables.bootstrap.min.css';
$sys_header['js'][] = BASE_URL.'/js/datatables/jquery.dataTables.min.js';
$sys_header['js'][] = BASE_URL.'/js/datatables/dataTables.bootstrap.min.js';
$sys_header['js'][] = BASE_URL.'/js/select2.min.js';

$objTpl->assign('sys_header', $sys_header);
$objTpl->display('w_store_account.html');