<?php

define('AFF_NAME', AFFILIATE_NAME);
define('NetworkId', 'flip');
define('API_Key', '90178dc5a35e739be83b17e8d2235ec654332ffe3f7fa95ec23a73e18664558e');
define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");

if(defined('START_TIME') && defined('END_TIME')) {
    $end_dt   = date('Y-m-d', strtotime(END_TIME));
    $begin_dt = date('Y-m-d', strtotime(START_TIME));
} else {
    $end_dt   = date('Y-m-d');
    $begin_dt = date('Y-m-d', strtotime('-60 days'));
}
echo "Date setting: ST:{$begin_dt} ET:{$end_dt} \n";

$file_temp = PATH_TMP . '/' . AFF_NAME . '.tmp';
do{
    //一次取一天数据
    $end_time = $end_dt;
    $start_time = $end_time;

    // Specify method arguments
    $args = array(
        'NetworkId' => NetworkId,
        'Target' => 'Affiliate_Report',
        'Method' => 'getConversions',
        'api_key' => API_Key,
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
        'hour_offset'=>'+7',
        'data_start' => $start_time,
        'data_end' => $end_time
    );

    if (file_exists($file_temp))
        unlink($file_temp);
    $fw = fopen($file_temp, 'w');

    if (!$fw){
        echo "File open failed {$file_temp}";exit;
    }

    $url = "https://api.hasoffers.com/Apiv3/json?".http_build_query($args);
    $ch = curl_init($url);

    $curl_opts = array(
        CURLOPT_HEADER => false,
        CURLOPT_NOBODY => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_FILE => $fw,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
    );
    curl_setopt_array($ch, $curl_opts);

//    echo "req => {$url}\r\n";

    $jsonEncodedApiResponse = curl_exec($ch);
    if($jsonEncodedApiResponse === false) {
        throw new \RuntimeException(
            'API call failed with cURL error: ' . curl_error($ch)
        );
    }
    curl_close($ch);
    fclose($fw);

    $fp = file_get_contents($file_temp);
    $fp = json_decode($fp, true);

    foreach ($fp['response']['data']['data'] as $v) {
        $TransactionTime = date('Y-m-d H:i:s', strtotime($v['Stat']['datetime']) + 3600);
        $orderid = $v['Stat']['ad_id'];
        $Sid = $v['Stat']['affiliate_info1'];
        $programname = $v['Offer']['name'];
        $TransactionId = $v['Stat']['ad_id'];
        $oldcommission = $v['Stat']['approved_payout'];
        $tradestatus = $v['Stat']['conversion_status'];
        $Curency = $v['Stat']['currency'];
        $tradetype = '';
        $referrer = $v['Stat']['refer'];
        $ProgramId = $v['Stat']['offer_id'];
        $oldsales = (!empty($v['Stat']['sale_amount']))?$v['Stat']['sale_amount']:'';
        $sales = '';
        $day = date('Y-m-d',strtotime($TransactionTime));
        $cur_exr = cur_exchange($Curency, 'USD', $day);
        $sales = round($oldsales * $cur_exr, 4);
        $commission = round($oldcommission * $cur_exr, 4);
        $cancelreason = '';

        $replace_array = array(
            '{createtime}'      => trim($TransactionTime),
            '{updatetime}'      => trim($TransactionTime),
            '{sales}'           => $sales,
            '{commission}'      => $commission,
            '{idinaff}'         => $ProgramId,
            '{programname}'     => trim($programname),
            '{sid}'             => trim($Sid),
            '{orderid}'         => trim($orderid),
            '{clicktime}'       => trim($TransactionTime),
            '{tradeid}'         => trim($TransactionId),
            '{tradestatus}'     => trim($tradestatus),
            '{oldcur}'          => $Curency,
            '{oldsales}'        => $oldsales,
            '{oldcommission}'   => $oldcommission,
            '{tradetype}'       => trim($tradetype),
            '{referrer}'        => $referrer,
            '{cancelreason}'    => $cancelreason,
        );
        $_day = date("Y-m-d", strtotime($v['Stat']['datetime']) + 3600);
        if($_day == $end_dt){
            $rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . str_replace('-', '', $_day) . '.upd';
            if (!isset($fws[$rev_file])) {
                $fws[$rev_file] = fopen($rev_file, 'w');
            }
            fwrite($fws[$rev_file], strtr(FILE_FORMAT,$replace_array) . "\n");
        }


    }

    if(isset($fws)){
        foreach ($fws as $file => $f) {
            fclose($f);
            unset($fws[$file]);
        }
    }

    $end_dt = date('Y-m-d', strtotime("-1 day $end_dt"));
}while ($begin_dt <= $end_dt);





?>
