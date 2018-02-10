<?php
define('AFF_NAME', AFFILIATE_NAME);
define('API_KEY', '8190d20bc02a49f08ab32083cf9a414b');
define("REST_API", "https://api.commissionfactory.com.au/V1/Affiliate/Transactions?apiKey=[apiKey]&fromDate=[fromDate]&toDate=[toDate]");
define('REV_DATA', PATH_CODE . '/log/' . AFF_NAME . '.dat');
define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");

if (defined('START_TIME') && defined('END_TIME')) {
    $end_dt = date('Y-m-d', strtotime(END_TIME));
    $start_dt = date('Y-m-d', strtotime(START_TIME));
} else {
    $end_dt = date('Y-m-d');
    $start_dt = date('Y-m-d', strtotime('-60 days', strtotime($end_dt)));
}

echo "Date setting: ST:{$start_dt} ET:{$end_dt} \n";

$url = str_replace(array('[apiKey]', '[fromDate]', '[toDate]'), array(API_KEY, $start_dt, $end_dt), REST_API);
echo "req => {$url} \n";
$ch = curl_init($url);
$curl_opts = array(CURLOPT_HEADER => false,
    CURLOPT_NOBODY => false,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
	CURLOPT_FOLLOWLOCATION => true,
);
curl_setopt_array($ch, $curl_opts);
$json = curl_exec($ch);

if ($json == '')
    throw new Exception("API request failed");

//backup result
$file_temp = PATH_TMP . '/' . AFF_NAME . '.tmp';
file_put_contents($file_temp, $json);

//decode result
$json = json_decode($json);

if (count($json) == 0)
    throw new Exception("No result");

$fws = array();

foreach ($json as $v) {
    print_r($v);exit;
    $valid = strtolower($v->Status) == 'void' ? 0 : 1;

    $oldsales = str_replace(',', '', trim($v->SaleValue));
    $oldcommission = str_replace(',', '', trim($v->Commission));


    //transaction date time
    $day = date('Y-m-d', strtotime($v->DateCreated));
    $Currency = $v->ReportedCurrencyCode;
    if(!$Currency){
        $Currency = 'USD';
    }
    $cur_exr = cur_exchange($Currency, 'USD', $day);
    $sales = round($oldsales * $cur_exr, 4);
    $commission = round($oldcommission * $cur_exr, 4);

    $cancelreason = '';
    if(strtolower(trim($v->Status)) == 'void'){
         $cancelreason = trim($v->VoidReason);
    }

    $replace_array = array(
                    '{createtime}'      => date('Y-m-d H:i:s', strtotime(trim($v->DateCreated))),
                    '{updatetime}'      => date('Y-m-d H:i:s', strtotime(trim($v->DateModified))),
                    '{sales}'           => $sales,
                    '{commission}'      => $commission,
                    '{idinaff}'         => trim($v->MerchantId),
                    '{programname}'     => trim($v->MerchantName),
                    '{sid}'             => trim($v->UniqueId),
                    '{orderid}'         => trim($v->OrderId),
                    '{clicktime}'       => '',
                    '{tradeid}'         => trim($v->Id),
                    '{tradestatus}'     => trim($v->Status),
                    '{oldcur}'          => $Currency,
                    '{oldsales}'        => $oldsales,
                    '{oldcommission}'   => $oldcommission,
                    '{tradetype}'       => '',
                    '{referrer}'        => trim($v->TrafficSource),
                    '{cancelreason}'    => $cancelreason,
                    );

    $rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . date('Ymd', strtotime($day)) . '.upd';
    if (!isset($fws[$rev_file])) {
        $fws[$rev_file] = fopen($rev_file, 'w');
        $comms[$rev_file] = 0;
    }

    fwrite($fws[$rev_file], strtr(FILE_FORMAT,$replace_array) . "\n");
}

foreach ($fws as $file => $f) {
    fclose($f);
}

?>
