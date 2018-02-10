<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init3.php');


$Keyword = isset($_GET['Keyword']) ? $_GET['Keyword'] : '' ;
$where = 'where 1=1 ';
if($Keyword){
    $where .= " AND (a.semKeywords LIKE '%$Keyword%' OR a.semRTextAds LIKE '%$Keyword%' OR a.whois LIKE '%$Keyword%')";
}

if(isset($_GET['country']) && !empty($_GET['country'])){
    $country = $_GET['country'];
}else{
    $country = 'US';
}



//$sql_names_set = 'SET NAMES latin1';
//$db->query($sql_names_set);

$sql = "select a.*, b.name,b.UserName,b.Status,b.domain from crawl_publish_domain_follow_new as a left join publisher b on a.publisherId = b.id $where";

$followArr = $db->getRows($sql);
//print_r($followArr);exit;
foreach ($followArr as $key=>$value){
    
    $followArr[$key]['semKeywords'] = json_decode($value['semKeywords'],true);
    $followArr[$key]['semKeywords'] = $followArr[$key]['semKeywords'][$country];
    $followArr[$key]['semRTextAds'] = json_decode($value['semRTextAds'],true);
    $followArr[$key]['semRTextAds'] = $followArr[$key]['semRTextAds'][$country];
    
    if(preg_match('/Error Occured/', $value['whois'])){
        $followArr[$key]['whois'] = array();
    }else{
        $followArr[$key]['whois'] = json_decode($value['whois'],true);
    }
} 

//print_r($followArr);exit;
 

$objTpl->assign("title","Publisher Domain Follow");

$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_header['css'][] = BASE_URL.'/css/bootstrap/bootstrap.min.css';
$sys_header['css'][] = BASE_URL.'/css/select2.min.css';
$sys_header['css'][] = BASE_URL.'/css/select2-bootstrap.min.css';

$sys_header['css'][] = BASE_URL.'/css/datatables/dataTables.bootstrap.min.css';
$sys_header['js'][] = BASE_URL.'/js/datatables/jquery.dataTables.min.js';
$sys_header['js'][] = BASE_URL.'/js/datatables/dataTables.bootstrap.min.js';
$sys_header['js'][] = BASE_URL.'/js/select2.min.js';

$objTpl->assign('list', $followArr);
$objTpl->assign('search', $_GET);
$objTpl->assign('sys_header', $sys_header);
$objTpl->display('b_publisher_domain_follow.html');