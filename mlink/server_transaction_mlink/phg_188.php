<?php
define('AFF_NAME', AFFILIATE_NAME);
define('USR_NAME', AFFILIATE_USER);
define('USR_PASS', AFFILIATE_PASS);
define('MAX_RTRY', 5);
define('REV_DATA', PATH_CODE . '/log/' . AFF_NAME . '.dat');
define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");

$REPORTWS_URI = 'https://p3tew145y3tag41n:vbn64GMc@api.performancehorizon.com/reporting/export/export/conversion.csv?start_date={START_DATE}+00%3A00&end_date={END_DATE}+00%3A00&publisher_id=1100l8645';
if (defined('START_TIME') && defined('END_TIME')) {
    $end_dt = date('Y-m-d', strtotime(END_TIME));
    $begin_dt = date('Y-m-d', strtotime(START_TIME));
} else {
    $end_dt = date('Y-m-d');
    $begin_dt = date('Y-m-d', strtotime('-120 days'));
}
echo "Date setting: ST:{$begin_dt} ET:{$end_dt} \n";

//download signature order report for all networks

$file_temp = PATH_TMP . '/' . AFF_NAME . '.tmp';
if (file_exists($file_temp))
    unlink($file_temp);

$fw = fopen($file_temp, 'w');
if (!$fw)
    throw new Exception("File open failed {$file_temp}");


$url = str_replace(array('{START_DATE}', '{END_DATE}'), array($begin_dt, $end_dt), $REPORTWS_URI);
$rtry = 0;
do {
    if(++$rtry > 3)
        break;
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

    if (!$pass)
        sleep(60);
} while (!$pass);
fclose($fw);

/*
  1===>conversion_id
  2===>publisher_id
  3===>campaign_id
  4===>conversion_time
  5===>conversion_date
  6===>conversion_date_time
  7===>click_time
  8===>click_date
  9===>click_date_time
  10===>currency
  11===>publisher_reference
  12===>advertiser_reference
  13===>conversion_reference
  14===>campaign_title
  15===>publisher_name
  16===>source_referer
  17===>conversion_status
  18===>conversion_lag
  19===>publisher_commission
  20===>creative_type
  21===>creative_id
  22===>specific_creative_id
  23===>value
  24===>booked_date
  25===>insert_date
 */

$fp = fopen($file_temp, 'r');
if (!$fp)
    die('temp file open failed');

$fws = array();
$k = 0;
while (!feof($fp)) {
    $lr = fgetcsv($fp);

    if (++$k == 1)
        continue;

    if ($lr[0] == "")
        continue;

    $created = trim($lr[5]);
    $day = date('Y-m-d',strtotime($created));
   
    $idinaff = trim($lr[2]);
    $programname = trim($lr[13]);
    $sid = trim($lr[10]);
    $orderid = trim($lr[12]);
    $click_dt = trim($lr[8]);
    $tradeid = trim($lr[0]);

    $status = trim($lr[16]);
    $oldcur = trim($lr[9]);
    $tradetype = trim($lr[26]);
    $referrer = trim($lr[15]);


    if (strtolower($status) != 'approved' && strtolower($status) != 'pending') {
        $sales = $commission = $oldsales = $oldcommission = 0;
    }else{
        $oldsales = str_replace(',', '', $lr[22]);
        $oldcommission = str_replace(',', '', $lr[18]);
        
        $cur_exr = cur_exchange($oldcur, 'USD', $day);
        $sales = round($oldsales * $cur_exr, 4);
        $commission = round($oldcommission * $cur_exr, 4);
    }

    $cancelreason = '';

    $replace_array = array(
                    '{createtime}'      => $created,
                    '{updatetime}'      => $created,
                    '{sales}'           => $sales,
                    '{commission}'      => $commission,
                    '{idinaff}'         => $idinaff,
                    '{programname}'     => $programname,
                    '{sid}'             => $sid,
                    '{orderid}'         => $orderid,
                    '{clicktime}'       => '',
                    '{tradeid}'         => $tradeid,
                    '{tradestatus}'     => $status,
                    '{oldcur}'          => $oldcur,
                    '{oldsales}'        => $oldsales,
                    '{oldcommission}'   => $oldcommission,
                    '{tradetype}'       => $tradetype,
                    '{referrer}'        => $referrer,
                    '{cancelreason}'    => $cancelreason,
                    );

    $rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . str_replace('-','',$day) . '.upd';
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
