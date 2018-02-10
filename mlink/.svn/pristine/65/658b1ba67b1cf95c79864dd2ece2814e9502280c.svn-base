<?php
/**
 * User: rzou
 * Date: 2017/8/16
 * Time: 11:44
 */

define('API_TOKEN', '6994a47c8388c3934825f6688803e89b');
define('NETWORK_ID', 'eploop');
define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");

if(defined('START_TIME') && defined('END_TIME')) {
    $end_dt   = date('Y-m-d', strtotime(END_TIME));
    $begin_dt = date('Y-m-d', strtotime(START_TIME));
} else {
    $end_dt   = date('Y-m-d');
    $begin_dt = date('Y-m-d', strtotime('-90 days'));
}
echo "Date setting: ST:{$begin_dt} ET:{$end_dt} \n";

$curl_opts = array(CURLOPT_HEADER => 0,
    CURLOPT_NOBODY => false,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36',
);
$currency = 'INR';

$fws = array();
$hasNextPage = true;
$numPrePage = 100;
$page = 1;
while($hasNextPage) {
    $url = sprintf('https://%s.yeahpixel.com/api/v1/?api_token=%s&method=getConversions&limit=%s&page=%s&start=%s&end=%s', NETWORK_ID,API_TOKEN, $numPrePage, $page, $begin_dt, $end_dt);
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
    if (!isset($transaction['data']) || empty($transaction['data'])) {
        mydie("Can't get api data!");
    }


    foreach ($transaction['data'] as $key => $val) {
        $tdate = date("Y-m-d", strtotime($val['conversion_date_time']));
        $oldcommission = $val['conversion_affiliate_payout'];
        $cur_exr = cur_exchange($currency, 'USD', $tdate);
        $Commission = $oldcommission > 0 ? round($oldcommission * $cur_exr, 4) : 0;

        if (trim($val['conversion_status']) == 'pending') {
            $tradestatus = 'Pending';
        } else {
            $tradestatus = 'Accepted';
        }
        $cancelreason = '';

        $replace_array = array(
            '{createtime}' => trim($val['conversion_date_time']),
            '{updatetime}' => trim($val['conversion_date_time']),
            '{sales}' => '',
            '{commission}' => $Commission,
            '{idinaff}' => trim($val['offer_id']),
            '{programname}' => trim($val['offer_name']),
            '{sid}' => trim($val['conversion_affiliate_sub_id']),
            '{orderid}' => trim($val['conversion_order_id']),
            '{clicktime}' => trim($val['conversion_date_time']),
            '{tradeid}' => trim($val['conversion_transaction_id']),
            '{tradestatus}' => trim($tradestatus),
            '{oldcur}' => $currency,
            '{oldsales}' => '',
            '{oldcommission}' => $oldcommission,
            '{tradetype}' => '',
            '{referrer}' => '',
            '{cancelreason}'    => $cancelreason,
        );
        $rev_file = PATH_DATA . '/' . AFFILIATE_NAME . '/revenue_' . str_replace('-', '', $tdate) . '.upd';
        if (!isset($fws[$rev_file])) {
            $fws[$rev_file] = fopen($rev_file, 'w');
        }

        fwrite($fws[$rev_file], strtr(FILE_FORMAT, $replace_array) . "\n");
    }

    if ($page * $numPrePage >= $transaction['data']['total']) {
        $hasNextPage = false;
        break;
    }
    $page ++;
}

foreach ($fws as $file => $f) {
    fclose($f);
}

echo "\t<< Get transaction success >> ";

?>
