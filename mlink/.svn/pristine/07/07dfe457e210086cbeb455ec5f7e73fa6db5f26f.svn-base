<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init3.php');
$objStatis = new Statis;
if(isset($_GET['act'])&&!empty($_GET['act'])){
    $search['stime'] = $_GET['startDate'];
    $search['etime'] = $_GET['endDate'];
    $search['datatype'] = $_GET['datatype'];
    $search['type'] = $_GET['datatype'];
    $search['dtype'] = $_GET['type'];
    $res = $objStatis->download($search);
}
if(isset($_POST['changeMark']) && !empty($_POST['changeMark'])){
    $search['stime'] = $_POST['startDate'];
    $search['etime'] = $_POST['endDate'];
    $search['datatype'] = $_POST['datatype'];
    $res = $objStatis->getCommission($search,0,10);
    $data = array();
    $sumclick = 0;
    $sumsales = 0;
    $sumcommission = 0;
    $sumrob = 0;
    $sumshow = 0;
    $sumrobp = 0;
    foreach($res['sum'] as $k1){
        $sumclick+=$k1['click'];
        $sumsales+=$k1['sales'];
        $sumcommission+=$k1['commission'];
        $sumshow+=$k1['showrevenue'];
        $sumrob+=$k1['rob'];
        $sumrobp+=$k1['robp'];
        $data['cday'][] = date('m-d',strtotime($k1['createddate']));
        $data['Commission'][] = $k1['commission'];
        $data['Sales'][] = $k1['sales'];
        $data['click'][] = $k1['click']-$k1['rob'];
        $data['rob'][] = $k1['rob'];
        $data['robp'][] = $k1['robp'];
    }
    $data['total']['sumsales'] =  "$".number_format($sumsales,2);
    $data['total']['sumcommission'] =  "$".number_format($sumcommission,2)."( $".number_format(bcsub($sumcommission,$sumshow,4))." )";
    $data['total']['sumclick'] = number_format($sumclick).' -- '.number_format($sumclick-$sumrob);
    $data['total']['sumrob'] = number_format($sumrob);
    $data['total']['sumrobp'] = number_format($sumrobp);
    $data['total']['sumpublisher'] = $res['publisher'];
    //TOP 20
    foreach($res['adv'] as $k){
        $data['newname'][] = $k['name'];
        $data['newrevenues'][] = $k['commission'];
        $data['newclicks'][] = $k['click'];
    }
    foreach($res['toppub'] as $k){
        $data['pub_name'][] = $k['name'];
        $data['pub_revenues'][] = $k['commission'];
    }
    echo json_encode($data,JSON_UNESCAPED_UNICODE);
    die;
}
if(isset($_POST['table']) && !empty($_POST['table'])){
    $search['stime'] = $_POST['startDate'];
    $search['etime'] = $_POST['endDate'];
    $search['datatype'] = $_POST['datatype'];
    $type = $_POST['type'];
    $page = $_POST['start'];
    $pagesize = $_POST['length'];
    if($type == 'sc' || $type == 'na'){
        $search['dtype'] = $type;
        $res = $objStatis->getCommission($search,$page,$pagesize);
    }else{
        $res = $objStatis->getpub($search,$page,$pagesize);
    }
    $data['data'] = $res['data'];
    $data['recordsFiltered'] = $res['count'];
    echo json_encode($data,JSON_UNESCAPED_UNICODE);
    die;
}

$sys_header['js'][] = BASE_URL.'/js/dataTables.min.js';
$sys_header['js'][] = BASE_URL.'/js/dataTables.semanticui.min.js';
$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_header['css'][] = BASE_URL.'/css/daterangepicker.css';
$sys_header['js'][] = BASE_URL.'/js/echarts.min.js';
$sys_header['js'][] = BASE_URL.'/js/moment.js';
$sys_header['js'][] = BASE_URL.'/js/daterangepicker.js';
$objTpl->assign('sys_header', $sys_header);
$detectOBJ = new MobileDetect();
if ($detectOBJ->isMobile()) {
    $objTpl->assign('data',$_GET);
    $objTpl->display('b_mobile_home.html');
} else {
    $objTpl->display('b_home.html');
}

?>
