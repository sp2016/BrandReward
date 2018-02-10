<?php

define('AFF_NAME', AFFILIATE_NAME);
define('SECURITY_TOKEN', 'EPPPSWWEKKPUHKYLQQWZ');
define('APIAFFID', '47119');

define('MAX_RTRY', 3);
define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");
define('REPORTWS_URI', 'http://affiliate.paidonresults.com/api/transactions?apikey={SECURITY_TOKEN}&DateFrom={BEGIN_DATE}&DateTo={END_DATE}&AffiliateID={APIAFFID}&GetNewSales=YES&PendingSales=YES&ValidatedSales=YES&VoidSales=YES&DateFormat=YYYY-MM-DD%20HH:MN:SS&Format=CSV&Fields=NetworkOrderID,MerchantName,MerchantID,DateAdded,DateUpdated,ClickDate,CustomTrackingID,AffiliateCommission,OrderValue,TransactionType,OrderDate,DatePaidToAffiliate');
define('REV_DATA', PATH_CODE . '/log/' . AFF_NAME . '.dat');



if (defined('START_TIME') && defined('END_TIME')) {
    $end_dt = date('Y-m-d', strtotime(END_TIME));
    $begin_dt = date('Y-m-d', strtotime(START_TIME));
} else {
    $end_dt = date('Y-m-d');
    $begin_dt = date('Y-m-d', strtotime('-90 days', strtotime($end_dt)));
}

echo "Date setting: ST:{$begin_dt} ET:{$end_dt} \n";

$file_temp = PATH_TMP . '/' . AFF_NAME . '.tmp';


if (file_exists($file_temp))
    unlink($file_temp);

$fw = fopen($file_temp, 'w');
if (!$fw){
    echo "File open failed {$file_temp}";
    exit;
}

$url = str_replace(array('{SECURITY_TOKEN}', '{BEGIN_DATE}', '{END_DATE}', '{APIAFFID}'), array(SECURITY_TOKEN, $begin_dt, $end_dt, APIAFFID), REPORTWS_URI);

$rtry = 0;
do {
    echo "req => {$url} \n";
    $ch = curl_init($url);
    $curl_opts = array(CURLOPT_HEADER => false,
        CURLOPT_NOBODY => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
        CURLOPT_FILE => $fw,
    );
    curl_setopt_array($ch, $curl_opts);

    $pass = curl_exec($ch);
    curl_close($ch);
    fclose($fw);

    if (!$pass) {
        $rtry++;

        if ($rtry > MAX_TRY){
            echo "reach at max login times";exit;
        }
    }
} while (!$pass);

echo $file_temp . "\n";

$fp = fopen($file_temp, 'r');
if (!$fp){
    echo "File open failed {$file_temp}";exit;
}
 
$k = 0;
$dump = array();
while (!feof($fp)) {
    $lr = fgetcsv($fp);

    if (++$k == 1) {
        if (stripos($lr[0], 'ERROR') !== false){
            echo 'data error';exit;
        }
        continue;
    }

    if(!$lr[0] || !$lr[1] || !$lr[2]){
        continue;
    }

    $valid = $lr[9] == 'VOID' ? 0 : 1;

    $sid = trim($lr[6]);
    if(preg_match('/^s\d{2,3}.*/', $sid))
        $sid = str_replace('aa', '_', $sid);
    $oid = $lr[0];
    $mid = $lr[2];
    $mname = $lr[1];
    $sale = str_replace(',', '', $lr[8]) * $valid;
    $rev = str_replace(',', '', $lr[7]) * $valid;
    $tid = $oid;
    $status = $lr[9];

    //transaction date time
    $event_dt = $lr[3];

    //process date time
    $process_dt = $lr[4];

    $reg_date = date('Y-m-d', strtotime($event_dt));
    $dump[$reg_date][] = $process_dt . "\t" . $event_dt . "\t" . $sale . "\t" . $rev . "\t" . $mid . "\t" . $mname . "\t" . $sid . "\t" . $oid . "\t" . $lr[5] . "\t" . $tid . "\t" . $lr[9];
}
fclose($fp);

$comm_all = 0;
//dump file to date
if(!$dump) {
    echo "there are no commision data";exit;
}

foreach ($dump as $d => $v) {

    $cur_exr = cur_exchange('GBP', 'USD', $d);
    $file_new = PATH_DATA . '/' . AFF_NAME . '/revenue_' . str_replace('-', '', $d) . '.upd';
    $fw = fopen($file_new, 'w');
    if (!$fw)
        continue;

    $comm_new = 0;
    $cancelreason = '';

    foreach ($v as $l) {
        $lr = explode("\t", $l);
        $replace_array = array(
            '{createtime}'      => $lr[0],
            '{updatetime}'      => $lr[1],
            '{sales}'           => round($lr[2] * $cur_exr, 4),
            '{commission}'      => round($lr[3] * $cur_exr, 4),
            '{idinaff}'         => $lr[4],
            '{programname}'     => $lr[5],
            '{sid}'             => $lr[6],
            '{orderid}'         => $lr[7],
            '{clicktime}'       => $lr[8],
            '{tradeid}'         => $lr[9],
            '{tradestatus}'     => $lr[10],
            '{oldcur}'          => 'GBP',
            '{oldsales}'        => $lr[2],
            '{oldcommission}'   => $lr[3],
            '{tradetype}'       => '',
            '{referrer}'        => '',
            '{cancelreason}'    => $cancelreason,
        );

        fwrite($fw, strtr(FILE_FORMAT,$replace_array) . "\n");
        $comm_new += $lr[3];
    }
    fclose($fw);
    $comm_all+=$comm_new;

}


?>
