<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init3.php');
$objTools = new Tools;

global $db,$objTpl,$sys_footer;

$affiList = $objTools->getClawerAff();
$affIdList = array_keys($affiList);
$crawlMethods = array('Program','Link','Couponfeed','Product','Transaction');

if(isset($_POST['table']) && !empty($_POST['table'])){
    $page = $_POST['start'];
    $pagesize = $_POST['length'];
    $res = array();
    $params = json_decode($_POST['params'], true);

    $sql = "SELECT a.ID,a.AffId as NetworkID,b.Name as NetworkName,a.Type,a.AffField as Field,a.BrField,a.DataSourceType,a.AddTime FROM aff_info_select_show a INNER JOIN wf_aff b ON a.AffId=b.ID WHERE ";
    $where = ' 1=1 AND';
    if (isset($params['ID']) && intval($params['ID'])){
        $ID = intval($params['ID']);
        $sql .= " a.ID=$ID";
        $res['data'] = $db->getRows($sql);
        echo json_encode($res);
    }else {
        if (isset($params['NetworkName']) && !empty($params['NetworkName'])){
            $AffId = '';
            foreach ($affiList as $val){
                if (strtolower(trim($val['Name'])) == strtolower(trim($params['NetworkName']))){
                    $AffId = intval($val['id']);
                };
            }
            if (!$AffId){
                $res['start'] = $page / $pagesize + 1;
                $res['recordsFiltered'] = 0;
                $res['data'] = array();
                echo json_encode($res);
                exit;
            }
            $where .= " a.AffId=$AffId AND";
        }else {
            $where .= " a.affId in ('" . join("','", $affIdList) . "') AND";
        }

        if (isset($params['Type']) && $params['Type']) {
            if (in_array($params['Type'], $crawlMethods)) {
                $where .= " a.Type='{$params['Type']}' AND";
            }
        }

        if (isset($params['DataSourceType']) && $params['DataSourceType']) {
            if (in_array($params['DataSourceType'], array('API', 'Page'))) {
                $where .= " a.DataSourceType='{$params['DataSourceType']}' AND";
            }
        }

        if (isset($params['useStatus'])) {
            if ($params['useStatus'] == 1){
                $where .= " a.BrField <> '' AND";
            }elseif ($params['useStatus'] == -1){
                $where .= " a.BrField = '' AND";
            }
        }

        if (isset($params['keyword']) && $params['keyword']) {
            $where .= " a.AffField LIKE '%{$params['keyword']}%' AND";
        }
        $where = rtrim($where, 'AND');

        if ($_POST['table'] == 1) {
            $countSql = 'SELECT COUNT(1) FROM aff_info_select_show a WHERE ' . $where;
            $count = $db->getFirstRowColumn($countSql);
            $sql .= $where . " ORDER BY a.AddTime DESC LIMIT $page,$pagesize";
            $list = $db->getRows($sql);

            $res['start'] = $page / $pagesize + 1;
            $res['recordsFiltered'] = $count;
            $res['data'] = $list;
        }else{
            $sql .= $where . " ORDER BY a.AffField ASC";
            $list = $db->getRows($sql);
            $res['data'] = $list;
        }

        echo json_encode($res);
    }
    exit;
}elseif (isset($_POST['updateId']) && $_POST['updateId']) {
    $arr_return = array('code' => 1, 'msg' => 'success');
    if (!intval($_POST['updateId'])){
        $arr_return = array('code'=>0, 'msg'=>'Error! UpdateID lose.');
        echo json_encode($arr_return);
        exit;
    }

    $params = json_decode($_POST['params'], true);
    $set = '';
    if (!isset($params['NetworkID']) || !isset($params['NetworkName'])){
        $arr_return = array('code'=>0, 'msg'=>'Error! params lose networkId or networkName.');
        echo json_encode($arr_return);
        exit;
    }
    if (isset($params['Type']) && in_array($params['Type'], $crawlMethods)){
        $set .= 'Type="' . $params['Type'] . '",';
    }
    if (isset($params['Field']) && $params['Field']){
        $set .= 'AffField="' . addslashes($params['Field']) . '",';
    }
    if (isset($params['BrField'])){
        $set .= 'BrField="' . addslashes($params['BrField']) . '",';
    }
    if (isset($params['DataSourceType']) && in_array($params['DataSourceType'], array('API','Page'))){
        $set .= 'DataSourceType="' . $params['DataSourceType'] . '",';
    }

    if ($set){
        $sql = 'UPDATE aff_info_select_show SET '. rtrim($set, ',') . ' WHERE ID=' . intval($_POST['updateId']);
        try{
            $db->query($sql);
        }catch (PDOException $e) {
            $message = $e->getMessage();
            $arr_return = array('code'=>0, 'msg'=>'Update faild !','error'=>$message);
            echo json_encode($arr_return);
            exit;
        }
        echo json_encode($arr_return);
    }
    exit;
}

$sys_header['js'][] = BASE_URL.'/js/dataTables.min.js';
$sys_header['js'][] = BASE_URL.'/js/dataTables.semanticui.min.js';
$sys_header['css'][] = BASE_URL.'/css/DateTimePicker.css';
$sys_footer['js'][] = BASE_URL.'/js/DateTimePicker.js';

$objTpl->assign('affiList', $affiList);
$objTpl->assign('crawlMethods', $crawlMethods);
$objTpl->assign('search', $_POST);
$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('sys_footer', $sys_footer);
$objTpl->assign("title","Aff Crawl Info Select Show");
$objTpl->display('b_tools_aff_info_select_search.html');
