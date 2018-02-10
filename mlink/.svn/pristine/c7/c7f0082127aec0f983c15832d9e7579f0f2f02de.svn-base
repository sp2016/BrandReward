<?php
global $_cf,$_req,$_db;

$where_pid = isset($_req['pid'])?' AND publisherid= '.$_req['pid']:'';
$where_date = (isset($_req['from']) && isset($_req['to']))?'UpdatedDate >= "'.$_req['from'].'" AND UpdatedDate <= "'.$_req['to'].'"':'UpdatedDate = "'.date('Y-m-d').'"';


$postback_publisher = array(
    '90312'=>'http://tracking.goflyla.com/path/postback.php?tid={BRID}&offerid=&amount={showcommission}&subid={publishtracking}', // goflyla
);

$publisherids = array_keys($postback_publisher);

$sql = "select * from publisher_account where publisherid in (".join(",",$publisherids).") ".$where_pid;
$rows = $_db->getRows($sql);

$task_list = array();

foreach($rows as $k=>$v){
    $task_list[$v['PublisherId']]['backurl'] = $postback_publisher[$v['PublisherId']];
    $task_list[$v['PublisherId']]['site'][] = $v['ApiKey'];
}

foreach($task_list as $k=>$v){
    print_r('doing publisher '.$k.'...'."\n");
    $sql = "SELECT * FROM rpt_transaction_unique WHERE ".$where_date." AND site IN ('".join("','",$v['site'])."')";
    $rows = $_db->getRows($sql);

    foreach($rows as $kt=>$tr){
        $url_param = array(
            '{BRID}' => $tr['BRID'],
            '{showcommission}'=>number_format($tr['ShowCommission'],2,'.',''),
            '{publishtracking}'=>$tr['PublishTracking'],
        );
        $url = strtr($v['backurl'], $url_param);
        print_r($kt."\t".$url."\t");
        $ch = curl_init($url);
        $curl_opts = array(
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_COOKIEJAR => '/app/site/api.brandreward.com/web/data/postback.cookie',
            CURLOPT_COOKIEFILE => '/app/site/api.brandreward.com/web/data/postback.cookie',
            CURLOPT_TIMEOUT => 5,
        );
        curl_exec($ch);
        $curlinfo = curl_getinfo($ch);
        curl_close($ch);
        print_r($curlinfo['http_code']."\n");
    }
}


?>
