<?php

define('AFF_NAME', AFFILIATE_NAME);
define('SECURITY_TOKEN', "dbec64f90d497bca3a139cc8403f752fab6a0ce75855811cc9c56ac1b02ec0f9");
define('REPORTWS_URI', 'http://api.pepperjamnetwork.com/20120402/publisher/report/transaction-details?apiKey={TOKEN}&startDate={BEGIN_DATE}&endDate={END_DATE}&format=csv');
define('REPORT_FIELDS', "transaction_id,order_id,sid,creative_type,commission,sale_amount,type,date,status,program_name,program_id,sub_type");
define('REV_DATA', PATH_CODE . '/log/' . AFF_NAME . '.dat');
define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");


if (defined('START_TIME') && defined('END_TIME')) {
    $end_dt = date('Y-m-d', strtotime(END_TIME));
    $begin_dt = date('Y-m-d', strtotime(START_TIME));
} else {
    $end_dt = date('Y-m-d');
    $begin_dt = date('Y-m-d', strtotime('-90 days', strtotime($end_dt)));
}

echo "Date setting: ST:{$begin_dt} ET:{$end_dt} \n";

$file_temp = PATH_TMP . '/' . AFF_NAME . '.tmp';


//download signature order report for all networks
if (file_exists($file_temp))
    unlink($file_temp);

$fw = fopen($file_temp, 'w');
if (!$fw)
    throw new Exception("File open failed {$file_temp}");


$url = str_replace(array('{BEGIN_DATE}', '{END_DATE}', '{TOKEN}'), array($begin_dt, $end_dt, SECURITY_TOKEN), REPORTWS_URI);
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

$comm_all = 0;
$fp = fopen($file_temp, 'r');
if (!$fp)
    throw new Exception("File open failed {$file_temp}");
$k = 0;
$fws = $comms = array();
while (!feof($fp)) {
    if (++$k == 1) {
        $lr = trim(fgets($fp));
        if ($lr != REPORT_FIELDS)
            throw new Exception("Report Format changed!");

        continue;
    }

    $lr = fgetcsv($fp);
    if ($lr[0] == "No Results Found" || !$lr)
        continue;
    /*
      1===>transaction_id
      2===>order_id
      3===>sid
      4===>creative_type
      5===>commission
      6===>sale_amount
      7===>type
      8===>date
      9===>status
      10===>program_name
      11===>program_id
      12===>sub_type
     */

    //transaction date time
    
    if (!preg_match('/[\d]{4}-[\d]{2}-[\d]{2}/', trim($lr[7]))) {
        continue;
    }
    $cancelreason = '';

    $replace_array = array(
                    '{createtime}'      => trim($lr[7]),
                    '{updatetime}'      => trim($lr[7]),
                    '{sales}'           => str_replace(',', '', trim($lr[5])),
                    '{commission}'      => str_replace(',', '', trim($lr[4])),
                    '{idinaff}'         => trim($lr[10]),
                    '{programname}'     => trim($lr[9]),
                    '{sid}'             => trim($lr[2]),
                    '{orderid}'         => trim($lr[1]),
                    '{clicktime}'       => trim($lr[7]),
                    '{tradeid}'         => trim($lr[0]),
                    '{tradestatus}'     => trim($lr[8]),
                    '{oldcur}'          => 'USD',
                    '{oldsales}'        => str_replace(',', '', trim($lr[5])),
                    '{oldcommission}'   => str_replace(',', '', trim($lr[4])),
                    '{tradetype}'       => trim($lr[6]),
                    '{referrer}'        => '',
                    '{cancelreason}'    => $cancelreason,
                    );


    //process date time
    $process_dt = trim($lr[7]);

    $rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . date('Ymd', strtotime($process_dt)) . '.upd';
    if (!isset($fws[$rev_file])) {
        $fws[$rev_file] = fopen($rev_file, 'w');
        $comms[$rev_file] = 0;
    }

    fwrite($fws[$rev_file], strtr(FILE_FORMAT,$replace_array) . "\n");
}
fclose($fp);


foreach ($fws as $file => $f) {
    fclose($f);
}

?>
