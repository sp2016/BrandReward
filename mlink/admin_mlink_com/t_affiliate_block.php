<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init3.php');

$affiliateModel = new AffiliateBlock();

if(isset($_POST['act']) && $_POST['act'] == 'violation'){
    $start = isset($_POST['start'])?$_POST['start']:0;
    $pagesize = isset($_POST['length'])?$_POST['length']:20;
    $data = json_decode($_POST['data'],true);
    $param = array();
    for($i = 0;$i<count($data);$i++){
        $param[$data[$i]['name']] = $data[$i]['value'];
    }
    $blockList = $affiliateModel->getBlockList($param,$start,$pagesize);
    $res['data'] = $blockList['data'];
    $res['recordsTotal'] = $blockList['num'];
    $res['recordsFiltered'] = $blockList['num'];
    echo json_encode($res);
    exit;
}

if(isset($_GET['name']) && isset($_GET['page']) && isset($_GET['type'])){
    $rs = $affiliateModel->getDataList($_GET['name'],$_GET['page'],$_GET['type']);
    echo json_encode($rs);
    exit;
}

if(isset($_POST['action'])){
    if($_POST['action']=='submit'){
        if(!isset($_POST['Publisher'])){
            $result['code'] = 2;
            $result['msg'] = 'Publisher is required';
            echo json_encode($result);
            exit;
        }
        if(!isset($_POST['BlockType']) || !isset($_POST['BlockBy'])){
            $result['code'] = 2;
            $result['msg'] = '`Block Type` and `Block By` are required';
            echo json_encode($result);
            exit;
        }
        if($_POST['BlockType'] == 'AccountId'){
            if(!isset($_POST['PublisherAccount'])){
                $result['code'] = 2;
                $result['msg'] = 'Publisher Account is required';
                echo json_encode($result);
                exit;
            }
        }
        if($_POST['BlockBy'] == 'Affiliate'){
            if(!isset($_POST['Affiliate'])){
                $result['code'] = 2;
                $result['msg'] = 'Network is required';
                echo json_encode($result);
                exit;
            }
        }else if($_POST['BlockBy'] == 'Program'){
            if(!isset($_POST['Affiliate'])){
                $result['code'] = 2;
                $result['msg'] = 'Network is required';
                echo json_encode($result);
                exit;
            }
            if(!isset($_POST['Program'])){
                $result['code'] = 2;
                $result['msg'] = 'Program is required';
                echo json_encode($result);
                exit;
            }
        }else if($_POST['BlockBy'] == 'Store'){
            if(!isset($_POST['Store'])){
                $result['code'] = 2;
                $result['msg'] = 'Store is required';
                echo json_encode($result);
                exit;
            }
        }else {
            $result['code'] = 2;
            $result['msg'] = 'Block By value error';
            echo json_encode($result);
            exit;
        }
        $_POST['AddUser'] = isset($_SERVER['PHP_AUTH_USER'])?$_SERVER['PHP_AUTH_USER']:'';
        $_POST['Remark'] = isset($_POST['Remark'])?$_POST['Remark']:'';
        $result = $affiliateModel->saveBlock($_POST);
        echo json_encode($result);
        exit;
    }else if($_POST['action']=='delete'){
        if(isset($_POST['id']) && $_POST['id']!=''){
            $result = $affiliateModel->deleteBlock($_POST);
            echo json_encode($result);
            exit;
        }else {
            $result['code'] = 2;
            $result['msg'] = 'miss param';
            echo json_encode($result);
            exit;
        }
    }else if($_POST['action']=='searchPublisherAccount'){
        if(isset($_POST['publisherId']) && $_POST['publisherId']!=''){
            $result = $affiliateModel->searchPublisherAccount($_POST);
            echo json_encode($result);
            exit;
        }else {
            $result['code'] = 2;
            $result['msg'] = 'miss param';
            echo json_encode($result);
            exit;
        }
    }else if($_POST['action']=='searchProgram'){
        if(isset($_POST['affId']) && $_POST['affId']!=''){
            $result = $affiliateModel->searchProgram($_POST);
            echo json_encode($result);
            exit;
        }else {
            $result['code'] = 2;
            $result['msg'] = 'miss param';
            echo json_encode($result);
            exit;
        }
    }
    $result['code'] = 2;
    $result['msg'] = 'miss param';
    echo json_encode($result);
    exit;
}

$affList = $affiliateModel->getAffiliateList();
$objTpl->assign("affList",$affList);

$objTpl->assign("title","Violations Index");

$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_header['css'][] = BASE_URL.'/css/bootstrap/bootstrap.min.css';
$sys_header['css'][] = BASE_URL.'/css/select2.min.css';
$sys_header['css'][] = BASE_URL.'/css/select2-bootstrap.min.css';

$sys_header['css'][] = BASE_URL.'/css/datatables/dataTables.bootstrap.min.css';
$sys_header['js'][] = BASE_URL.'/js/datatables/jquery.dataTables.min.js';
$sys_header['js'][] = BASE_URL.'/js/datatables/dataTables.bootstrap.min.js';
$sys_header['js'][] = BASE_URL.'/js/select2.min.js';

$managers = get_sys_am();
$managers[] = 'public';
$objTpl->assign('managers',$managers);
$objTpl->assign('search', $_GET);
$objTpl->assign('sys_header', $sys_header);
$objTpl->display('t_affiliate_block.html');