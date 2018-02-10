<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init3.php');
$objTools = new Tools;

global $db,$objTpl,$sys_footer;

$affiList = $objTools->getClawerAff();

if(isset($_POST['table']) && !empty($_POST['table'])){
    $page = $_POST['start'];
    $pagesize = $_POST['length'];
    $res = array();
    $params = json_decode($_POST['params'], true);

    if ($_POST['table'] == 1){
        $data = array();
        $where = ' 1=1 ';
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
                $res['data'] = $data;
                echo json_encode($res);
                exit;
            }
            $where .= ' AND AffId='. $AffId;
        }

        if (isset($params['HaveKeyword']) && !empty($params['HaveKeyword'])){
            $where .= " AND AffField='{$params['HaveKeyword']}'";
        }

        $countSql = "SELECT DISTINCT AffId FROM aff_info_select_show WHERE " . $where;
        $affIdList = $db->getRows($countSql, 'AffId');
        $affIdList = array_keys($affIdList);
        if (isset($params['NoKeyword']) && !empty($params['NoKeyword'])){
            $where .= " AND AffField='{$params['NoKeyword']}'";
            $countSql = "SELECT DISTINCT AffId FROM aff_info_select_show WHERE " . $where;
            $affIdList_no = $db->getRows($countSql, 'AffId');
            $affIdList_no = array_keys($affIdList_no);
            $affIdList = array_diff($affIdList, $affIdList_no);
        }

        $sql ="SELECT ID,Name FROM wf_aff  WHERE ID IN ('". join("','", $affIdList) ."') LIMIT $page,$pagesize";
        $list = $db->getRows($sql);
        foreach ($list as $v){
            $typre_arr = array();
            $affid = $v['ID'];
            $typre_arr['NetworkName'] = $v['Name'];
            $typre_arr['NetworkID'] = $affid;
            $sql = 'SELECT DISTINCT Type FROM aff_info_select_show WHERE AffId=' . $affid;
            $typeList = $db->getRows($sql);
            if (!empty($typeList)) {
                foreach ($typeList as $tv) {
                    $type = $tv['Type'];
                    $typre_arr[$type] = $type;
                }
                $data[] = $typre_arr;
            }
        }

        $res['start'] = $page / $pagesize + 1;
        $res['recordsFiltered'] = count($affIdList);
        $res['data'] = $data;

        echo json_encode($res);

    }else {
        if (!isset($params['NetworkID']) || !intval($params['NetworkID'])) {
            echo json_encode('Params error!');exit;
        }
        if (!isset($params['Type']) && !$params['Type']) {
            echo json_encode('Params error!');exit;
        }

        $sql = "SELECT a.ID,a.AffId as NetworkID,b.Name as NetworkName,a.Type,a.AffField as Field,a.BrField,a.DataSourceType,a.AddTime FROM aff_info_select_show a INNER JOIN wf_aff b ON a.AffId=b.ID WHERE ";
        $NetworkID = intval($params['NetworkID']);
        $sql .= " a.affId=$NetworkID AND a.Type='{$params['Type']}'";
        $list = $db->getRows($sql);

        array_multisort(array_map(function($c){return $c['Field'];},$list),SORT_ASC, $list);

        echo json_encode($list);
    }

    exit;
}

$sys_header['js'][] = BASE_URL.'/js/dataTables.min.js';
$sys_header['js'][] = BASE_URL.'/js/dataTables.semanticui.min.js';
$sys_header['css'][] = BASE_URL.'/css/DateTimePicker.css';
$sys_footer['js'][] = BASE_URL.'/js/DateTimePicker.js';

$objTpl->assign('search', $_POST);
$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('sys_footer', $sys_footer);
$objTpl->assign("title","Aff Crawl Info Select Show");
$objTpl->display('b_tools_aff_info_select_oversee.html');
