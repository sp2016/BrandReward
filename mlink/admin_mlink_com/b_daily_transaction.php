<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');
//通过联盟的id查询某一时间区间的统计信息
$t = new Transaction();
$data = array();
$data['sdate'] = isset($_GET['sdate']) ? $_GET['sdate'] : date('Y-m-d', strtotime('-9 days'));
$data['edate'] = isset($_GET['edate']) ? $_GET['edate'] : date('Y-m-d');
$data['store'] = isset($_GET['advertiser']) ? $_GET['advertiser'] : '';
$data['datatype'] = isset($_GET['datatype']) ? $_GET['datatype'] : 1;//1.publisher 2:all
$data['timetype'] = isset($_GET['timetype']) ? $_GET['timetype'] : 1;//1.createtime 2:clicktime
$data['site'] = isset($_GET['site']) ? $_GET['site'] : '';
$data['sitetype'] = isset($_GET['sitetype']) ? $_GET['sitetype'] : 0;//
$data['country'] = isset($_GET['country']) && $_GET['country'] != 'null' ? $_GET['country'] : '';
$data['ctype'] = isset($_GET['ctype']) ? $_GET['ctype'] : 0;
$type = isset($_GET['group']) ? $_GET['group'] : 'network';
switch ($type) {
    case 'network' :
        $data['affid'] = isset($_GET['id']) ? $_GET['id'] : 0;
        $sql = "SELECT * FROM wf_aff WHERE ID = " . $data['affid'];
        $item = $t->getRow($sql);
        break;
    case 'store' :
        $data['storeid'] = isset($_GET['id']) ? $_GET['id'] : 0;
        $sql = "SELECT IF(NameOptimized='' OR NameOptimized IS NULL,Name,NameOptimized) AS `Name` FROM store WHERE ID = " . $data['storeid'];
        $item = $t->getRow($sql);
        break;
}
$list = $t->getAffOrStoreDailyTransaction($data);
foreach ($list as $k => &$v) {
    $v['rclick'] = $v['click'] - $v['rob'];
    $v['commissionrate'] = $v['sales'] > 0 ? round($v['commission'] / $v['sales'] * 100, 2) : 0;
    $v['epc'] = $v['rclick'] > 0 ? round($v['commission'] / $v['rclick'], 4) : 0;
}
$total = array();
foreach ($list as &$v) {
    krsort($v);
    foreach ($v as $key => $value) {
        if (!isset($total[$key])) {
            $total[$key] = 0;
        }
        $total[$key] += $value;
    }
    $total['commissionrate'] = $total['sales'] > 0 ? round($total['commission'] / $total['sales'] * 100, 2) : 0;
    $total['epc'] = $total['rclick'] > 0 ? round($total['commission'] / $total['rclick'], 2) : 0;
}
$warning = array();
$objTpl->assign('total',$total);
$sys_header['css'][] = BASE_URL.'/css/front.css';
$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('title','Daily Transaction');
$objTpl->assign('info',$list);
$objTpl->assign('item',$item);
$objTpl->assign('type',$type);
$objTpl->display('b_affiliate_transaction.html');

