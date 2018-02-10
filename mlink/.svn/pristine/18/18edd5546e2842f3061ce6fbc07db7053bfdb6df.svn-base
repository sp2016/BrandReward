<?php
/**
 * User: rzou
 * Date: 2017/8/16
 * Time: 11:22
 */

define('API_KEY', '62d901bb3b481c07e58822248431c231dee1a39f157fb065259a165fbf26dfce');
define('NETWORK_ID', 'fiverr');
define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");

if(defined('START_TIME') && defined('END_TIME')) {
    $end_dt   = date('Y-m-d', strtotime(END_TIME));
    $begin_dt = date('Y-m-d', strtotime(START_TIME));
} else {
    $end_dt   = date('Y-m-d');
    $begin_dt = date('Y-m-d', strtotime('-90 days'));
}
echo "Date setting: ST:{$begin_dt} ET:{$end_dt} \n";

$url = 'https://offerfactory.api.hasoffers.com/Apiv3/json?';
$args = array(
    'NetworkId' => NETWORK_ID,
    'api_key' => API_KEY,
    'Target' => 'Affiliate_Report',
    'Method' => 'getConversions',
    'fields' => array(
        'Stat.id',
        'Stat.datetime',
        'Offer.name',
        'Stat.conversion_status',
        'Stat.approved_payout',
        'Stat.sale_amount',
        'Stat.ad_id',
        'Stat.affiliate_info1',
        'Stat.offer_id',
        'Stat.currency',
        'Stat.refer'
    ),
    'data_start' => $begin_dt,
    'data_end' => $end_dt
);

$url .= http_build_query($args);

$curl_opts = array(CURLOPT_HEADER => 0,
    CURLOPT_NOBODY => false,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36',
);

$rtry = 0;
do {
    $ch = curl_init($url);
    curl_setopt_array($ch, $curl_opts);

    echo "req => {$url}\r\n";
    $pass = curl_exec($ch);
    curl_close($ch);
    if (!$pass) {
        $rtry++;
        sleep(10);
        if ($rtry > 3)
            mydie("reach at max try times");
    }
} while (!$pass);

$transaction = json_decode($pass, true);

if (!isset($transaction['response']['data']['data']) || empty($transaction['response']['data']['data'])) {
    mydie("Can't get api data!");
}

$fws = array();
foreach ($transaction['response']['data']['data'] as $key => $val) {
    $tdate = date("Y-m-d",strtotime($val['Stat']['datetime']));
    $oldcommission = $val['Stat']['approved_payout'];
    $cur_exr = cur_exchange($val['Stat']['currency'], 'USD', $tdate);
    $Commission = $oldcommission > 0 ? round($oldcommission * $cur_exr, 4) : 0;

    if(trim($val['Stat']['conversion_status']) == 'approved') {
        $tradestatus = 'Accepted';
    }else {
        $tradestatus = 'Pending';
    }
    $cancelreason = '';

    $replace_array = array(
        '{createtime}'      => trim($val['Stat']['datetime']),
        '{updatetime}'      => trim($val['Stat']['datetime']),
        '{sales}'           => '',
        '{commission}'      => $Commission,
        '{idinaff}'         => trim($val['Stat']['offer_id']),
        '{programname}'     => trim($val['Offer']['name']),
        '{sid}'             => trim($val['Stat']['affiliate_info1']),
        '{orderid}'         => trim($val['Stat']['id']),
        '{clicktime}'       => trim($val['Stat']['datetime']),
        '{tradeid}'         => trim($val['Stat']['ad_id']),
        '{tradestatus}'     => trim($tradestatus),
        '{oldcur}'          => trim($val['Stat']['currency']),
        '{oldsales}'        => '',
        '{oldcommission}'   => $oldcommission,
        '{tradetype}'       => '',
        '{referrer}'        => trim($val['Stat']['referrer']),
        '{cancelreason}'    => $cancelreason,
    );
    $rev_file = PATH_DATA . '/' . AFFILIATE_NAME . '/revenue_' . str_replace('-', '', $tdate) . '.upd';
    if (!isset($fws[$rev_file])) {
        $fws[$rev_file] = fopen($rev_file, 'w');
    }

    fwrite($fws[$rev_file], strtr(FILE_FORMAT,$replace_array) . "\n");
}

foreach ($fws as $file => $f) {
    fclose($f);
}

echo "\t<< Get transaction success >> ";

?>
