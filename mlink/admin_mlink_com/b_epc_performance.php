<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');
$epcObj = new StoreEpc();
$stores_data = array();
$type = isset($_GET['type']) ? $_GET['type'] : 1;
$dataType = isset($_GET['data']) ? $_GET['data'] : 1;
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$offset = isset($_GET['p']) ? $_GET['p'] : 1;
$offset = $offset < 1 ? 1: $offset;
$network = isset($_GET['networkid']) ? $_GET['networkid'] : 0;
$storeName = isset($_GET['store_name']) ? trim($_GET['store_name']) : '';
$publisher = isset($_GET['pulisher']) ? trim($_GET['pulisher']) : '';

$limit = isset($_GET['limit']) ? $_GET['limit'] : 15;
switch ($type) {
    case 1:
        $sd = !empty($startDate) ? $startDate : date('Y-m-d', strtotime("-9 DAYS"));
        $ed = !empty($endDate) ? $endDate : date('Y-m-d');
        break;
    case 2:
        $sd = !empty($startDate) ? $startDate : date('Y-m-d', strtotime("-9 WEEKS"));
        $ed = !empty($endDate) ? $endDate : date('Y-m-d');
        break;
    case 3:
        $sd = !empty($startDate) ? $startDate : date('Y-m-d', strtotime("-6 MONTHS"));
        $ed = !empty($endDate) ? $endDate : date('Y-m-d');
        break;
}
$sQuery = array(
    'store_name' => $storeName,
    'pulisher' => $publisher,
    'start_date' => $sd,
    'end_date' => $ed,
    'type' => $type,
    'network' => $network,
    'offset' => $offset,
    'limit' => $limit,
);
//获得分页商家ID
$storeIds = $epcObj->getActiveStoreId($sQuery);
//获得商家的总数
$storeCount = $epcObj->getActiveStoreIdCount($sQuery);
//获得分页商家信息
$storeArray = $epcObj->getStore($storeIds);
$stores = isset($storeArray['s']) ? $storeArray['s'] : array();
$programs = isset($storeArray['p']) ? $storeArray['p'] : array();
if (!empty($programs)) {
    $dQuery = array(
        'start_date' => $sd,
        'end_date'   => $ed,
        'program_id'   => $programs,
        'type' => $type
    );
    $stores_data = $epcObj->getStoreEpcData($dQuery);
}
$output = array();
//获取日期列表
$dateTitles = $epcObj->getDateTitle($sd,$ed,$type);
$cal = array();
//分组格式化数据
if (!empty($stores)) {
    foreach ($stores as $store) {
        $store_id = isset($store['ID']) ? $store['ID'] : 0;
        $store_name = isset($store['Name']) ? $store['Name'] : md5($store_id);
        $store_program = isset($store['program']) ? $store['program'] : array();
        if (empty($store) || empty($store_id)) {
            continue;
        }
        if (!isset($output[$store_name])) {
            $output[$store_name] = array();
        }
        foreach ($dateTitles as $title) {
            $rv = $ck = $ecp = 0;
            foreach ($store_program as $sp) {
                if (isset($stores_data[$sp . '_' . $title])) {
                    $rv += isset($stores_data[$sp . '_' . $title]['rv']) ? $stores_data[$sp . '_' . $title]['rv'] : 0;
                    $ck += isset($stores_data[$sp . '_' . $title]['ck']) ? $stores_data[$sp . '_' . $title]['ck'] : 0;
                }
            }
            $epc = $ck != 0 ? round($rv/$ck,4) : 0;
            if (!isset($cal[$title])) {
                $cal[$title] = array(
                    's' => 0,
                    'c' => 0
                );
            }
            $dataValue = 0;
            switch ($dataType) {
                case 1 :
                    $dataValue = $epc;
                    break;
                case 2 :
                    $dataValue = $rv;
                    break;
                case 3 :
                    $dataValue = $ck;
                    break;
            }
            $cal[$title]['s'] += $dataValue;
            $cal[$title]['c'] += 1;
            $output[$store_name][$title] = $dataValue;
        }
    }
}
//根据日期求平均数
$dateSumOutput = array();
foreach ($cal as $key => $av) {
    $s = isset($av['s']) ? $av['s'] : 0;
    $c = isset($av['c']) ? $av['c'] : 0;
    if ($c > 0) {
        $dateSumOutput[$key] = round ($s,4);
    } else {
        $dateSumOutput[$key] = round (0,4);
    }
}
//根据商家求平局数
$storeSumOutput = array();
foreach ($output as $storeName => $storeData) {
    $count = count($storeData);
    $sum = array_sum($storeData);
    if ($count > 0) {
        $storeSumOutput[$storeName] = round ($sum,4);
    } else {
        $storeSumOutput[$storeName] = round (0,4);
    }
}
//分页数据
$pageHtml = '';
$page = array(
    'page_now' => $offset,
    'page_total' => ceil($storeCount/$limit)
);
if(!empty($page) && $page['page_total'] > 1) {
    $pageHtml = get_page_html($page);
}
$objOutlog = new Outlog;
$affname = $objOutlog->get_affname();
$tSum = count($storeSumOutput) > 0 ? round(array_sum($storeSumOutput),4) : 0;
$sys_header['css'][] = BASE_URL . '/css/front.css';
$sys_header['js'][] = BASE_URL . '/js/Chart.js';
$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('dateTitle', $dateTitles);
$objTpl->assign('affname', $affname);
$objTpl->assign('network',$network);
$objTpl->assign('dSum', $dateSumOutput);
$objTpl->assign('sSum', $storeSumOutput);
$objTpl->assign('tSum', $tSum);
$objTpl->assign('out', $output);
$objTpl->assign('dateType',$dataType);
$objTpl->assign('query', $sQuery);
$objTpl->assign('pageHtml', $pageHtml);
$objTpl->assign('title', 'Store Performance');
$objTpl->display('b_epc_performance.html');