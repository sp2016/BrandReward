<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init3.php');

$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_header['css'][] = BASE_URL.'/css/bootstrap/bootstrap.min.css';
$sys_header['css'][] = BASE_URL.'/css/select2.min.css';
$sys_header['css'][] = BASE_URL.'/css/select2-bootstrap.min.css';
$sys_header['js'][] = BASE_URL.'/js/dataTables.min.js';
$sys_header['js'][] = BASE_URL.'/js/dataTables.semanticui.min.js';
$sys_header['js'][] = BASE_URL.'/js/select2.min.js';
$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('sys_header', $sys_header);
$act = isset($_POST['act']) ? $_POST['act'] : 'list';
switch ($act) {
    case 'list' :
        //list display section
        $smo = new StoreMerge();
        $dmn = isset($_GET['domain']) ? $_GET['domain'] : null;
        $als = isset($_GET['alias']) ? $_GET['alias'] : null;
        $sml = $smo->getStoreMergeList($als,$dmn);
        $objTpl->assign('list', $sml);
        $objTpl->assign('domain', $dmn);
        $objTpl->assign('alias', $als);
        $objTpl->assign("title","Advertiser Merge List");
        $objTpl->display('b_store_merge.html');
        break;
    case 'add' :
        //add section
        $objTpl->assign("title","Advertiser Merge Add");
        $objTpl->display('d_add_store_merge.html');
        break;
    case 'doAdd' :
    case 'doEdit':
        $alias = isset($_POST['alias']) ? $_POST['alias'] : '';
        $merged = isset($_POST['merged']) ? intval($_POST['merged']) : 0;
        $domains = isset($_POST['domains']) ? $_POST['domains'] : array();
        $smo = new StoreMerge();
        $res = $smo->doMergeStoreDomains($alias,$merged,$domains);
        echo $res;
        break;
    case 'search' :
        $keyword = isset($_POST['keywords']) ? $_POST['keywords'] : '';
        $smo = new StoreMerge();
        $res = $smo->doSearchStore($keyword);
        echo $res;
        break;
    case 'searchDomain' :
        $keyword = isset($_POST['keywords']) ? $_POST['keywords'] : '';
        $smo = new StoreMerge();
        $res = $smo->doSearchStoreDomain($keyword);
        echo $res;
        break;
    case 'edit' :
        //edit section
        $alias = isset($_POST['alias']) ? $_POST['alias'] : '';
        $merged = isset($_POST['merged']) ? $_POST['merged'] : '';
        $smo = new StoreMerge();
        $info = $smo->getStoreMerge($alias,$merged);
        $objTpl->assign('info', $info);
        $objTpl->assign("title","Advertiser Merge Edit");
        $objTpl->display('d_store_merge.html');
        break;
    case 'doDelete' :
        $alias = isset($_POST['alias']) ? $_POST['alias'] : '';
        $merged = isset($_POST['merged']) ? $_POST['merged'] : '';
        $smo = new StoreMerge();
        $res = $smo->doSetStoreActive($alias,$merged,'InActive');
        echo $res;
        break;
    case 'doRestore' :
        $alias = isset($_POST['alias']) ? $_POST['alias'] : '';
        $merged = isset($_POST['merged']) ? $_POST['merged'] : '';
        $smo = new StoreMerge();
        $res = $smo->doSetStoreActive($alias,$merged,'Active');
        echo $res;
        break;
}

die();


