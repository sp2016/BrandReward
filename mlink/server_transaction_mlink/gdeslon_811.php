<?php
define('AFF_NAME', AFFILIATE_NAME);
define('PARTNER_ID', 84794);
define('API_KEY', 'Znlhb_cPXm2onkvUkpp9');

define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");
define('REV_DATA', PATH_CODE . '/log/' . AFF_NAME . '.dat');

if (defined('START_TIME') && defined('END_TIME')) {
    $start_dt = date('Y-m-d', strtotime(START_TIME));
    $end_dt = date('Y-m-d', strtotime(END_TIME));
} else {
    $end_dt = date('Y-m-d');
    $start_dt = date('Y-m-d', strtotime('-120 days', strtotime($end_dt)));
}
echo "Date setting: ST:{$start_dt} ET:{$end_dt} \n";
$comm_all = 0;
$file_temp = PATH_TMP . '/' . AFF_NAME . '.tmp';

if (file_exists($file_temp))
    unlink($file_temp);

$fw = fopen($file_temp, 'w');

$postData = array(
    'created_at' => array(
        'date' => $start_dt,
        'period' => 121
    )
);

$url= 'https://www.gdeslon.ru/api/orders/';
echo "req => {$url} \n";
$ch = curl_init($url);
$curl_opts = array(
    CURLOPT_HEADER => false,
    CURLOPT_NOBODY => false,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($postData),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_USERPWD => PARTNER_ID . ':' . API_KEY,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_FILE => $fw
);
curl_setopt_array($ch, $curl_opts);
$rs = curl_exec($ch);
curl_close($ch);
fclose($fw);

$result = file_get_contents($file_temp);
$result = json_decode($result, true);

foreach ($result as $v){
    $transactionId = $v['id'];
    $createdTime = date('Y-m-d H:i:s', strtotime($v['created_at']) + 8 * 3600);
    $updateTime = date('Y-m-d H:i:s', strtotime($v['last_updated_at'])  + 8 * 3600);
    $orderid = $v['gdeslon_order_id'];
    $sid = $v['sub_id'];
    $programId = $v['merchant_id'];
    $programname = $v['merchant_name'];

    switch ($v['state']){
        case 0:
            $tradestatus = 'new';
            break;
        case 1:
            $tradestatus = 'canceled';
            break;
        case 2:
            $tradestatus = 'postponed ';
            break;
        case 3:
            $tradestatus = 'confirmed ';
            break;
        case 4:
            $tradestatus = 'paid';
            break;
        default :
            $tradestatus = '';
            break;
    }


    switch ($v['type']) {
        case 0:
            $tradetype = 'commodity order';
            break;
        case 1:
            $tradetype = 'lead';
            break;
        default :
            $tradetype = ' ';
            break;
    }

    $referrer = '';
    $curency = strtoupper($v['currency']);
    $oldSales = (!empty($v['order_payment'])) ? $v['order_payment'] : '';
    $oldCommission = $v['partner_payment'];

    $tdate = date("Y-m-d",strtotime($createdTime));
    $cur_exr = cur_exchange($curency, 'USD',$tdate);
    $sales = $oldSales > 0 ? round($oldSales * $cur_exr, 4) : 0;
    $commission = $oldCommission > 0 ? round($oldCommission * $cur_exr, 4) : 0;

    $replace_array = array(
        '{createtime}'      => trim($createdTime),
        '{updatetime}'      => trim($updateTime),
        '{sales}'           => $sales,
        '{commission}'      => $commission,
        '{idinaff}'         => $programId,
        '{programname}'     => trim($programname),
        '{sid}'             => trim($sid),
        '{orderid}'         => trim($orderid),
        '{clicktime}'       => trim($createdTime),
        '{tradeid}'         => trim($transactionId),
        '{tradestatus}'     => trim($tradestatus),
        '{oldcur}'          => $curency,
        '{oldsales}'        => $oldSales,
        '{oldcommission}'   => $oldCommission,
        '{tradetype}'       => trim($tradetype),
        '{referrer}'        => $referrer,
        '{cancelreason}'    => '',
    );
    $_day = date("Y-m-d", strtotime($createdTime));
    $rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . str_replace('-', '', $_day) . '.upd';
    if (!isset($fws[$rev_file])) {
        $fws[$rev_file] = fopen($rev_file, 'w');
    }

    fwrite($fws[$rev_file], strtr(FILE_FORMAT,$replace_array) . "\n");
}

foreach ($fws as $file => $f) {
    fclose($f);
}


?>
