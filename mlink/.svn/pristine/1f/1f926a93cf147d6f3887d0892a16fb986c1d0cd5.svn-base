<?php
global $_cf,$_req,$_db,$_user;
$opt = array();
$outformat = isset($_req['outformat'])?$_req['outformat']:'txt';

$list = getCategoryList();
$content = array();
$content['response']['PageTotal'] = $content['response']['Num'] = $content['response']['NumReturn'] = count($list);
$content['response']['PageNow'] = 1;
$content['data'] = $list;

if(isset($_req['xo']) && $_req['xo'] == 'ox'){
    echo '<pre>';print_r($content);
}else{
    arr_out_format($content,$outformat);
}

exit();


function getCategoryList(){
    global $_db;
    $sql = "SELECT ID,`Name` FROM category_std ORDER BY `Name` ASC";
    $rows = $_db->getRows($sql);
    
    return $rows;
}
?>
