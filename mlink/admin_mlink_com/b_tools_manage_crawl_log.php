<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init2.php');
$objTools = new Tools;




if($_POST){
    $html = '<table class="table table-striped" id="table_aff">
<thead>
<tr>
<th>Id</th>
<th>affid</th>
<th>Idinaff</th>
<th>Name</th>
<th>Homepage</th>
<th>StatusInAff</th>     
<th>Partnership</th>         
</tr>
</thead>';
    $data = $objTools->getAnaylezeProgram($_POST);

    if(!empty($data)){
        foreach ($data as $v){
            $html .= '
                <tr>
<td>'.$v['id'].'</td>
<td>'.$v['affid'].'</td>
<td>'.$v['idinaff'].'</td>
<td>'.$v['name'].'</td>
<td>'.$v['homepage'].'</td>
<td>'.$v['StatusInAff'].'</td> 
<td>'.$v['Partnership'].'</td>   
<tr>';
        }
    }
    $html .= '</table>';
    echo $html;exit;
}

$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_header['js'][] = BASE_URL.'/js/b_account.js';
$sys_header['js'][] = BASE_URL.'/js/Chart.js';

$sys_footer['js'][] = BASE_URL.'/js/back.js';
$sys_footer['js'][] = BASE_URL.'/js/b_performance.js';


$affiList = $objTools->getClawerAff();

$logList  = $objTools->getCrawlerLog($_GET);

$list = array();
foreach ($logList as $value){
    $list[$value['affid']] = $value;
}

//print_r($affiList);exit;
$objTpl->assign('list', $logList);
$objTpl->assign('affiList', $affiList);

$sys_header['css'][] = BASE_URL.'/css/DateTimePicker.css';
$sys_footer['js'][] = BASE_URL.'/js/DateTimePicker.js';
$objTpl->assign('search', $_GET);
$objTpl->assign('title','Crawl Log');
$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('sys_footer', $sys_footer);
$objTpl->display('b_tools_manage_crawl_log.html');
?>