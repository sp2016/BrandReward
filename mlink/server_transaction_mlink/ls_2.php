<?php
define('AFF_NAME', AFFILIATE_NAME);
define('REV_DATA', PATH_CODE . '/log/' . AFF_NAME . '.dat');

define('TOKEN','ZW5jcnlwdGVkYToyOntzOjU6IlRva2VuIjtzOjY0OiIzZGQwZTE4MTE0MmI4MzA4YTczNGY5MzBkNmVjZTNhNjFiMzhjZjAyYjlkYzFiNWUyNWNmZGNiZDY5MmQ5MWJiIjtzOjg6IlVzZXJUeXBlIjtzOjk6IlB1Ymxpc2hlciI7fQ%3D%3D');
define('URL_API','https://ran-reporting.rakutenmarketing.com/en/reports/signature-orders-report-2/filters?start_date={BDATE}&end_date={EDATE}&include_summary=N&network={NID}&tz=GMT&date_type=process&token={TOKEN}');
define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}");


$NIDS = array(
    '1'=>array('country'=>'US','currency'=>'USD'),
    '3'=>array('country'=>'UK','currency'=>'GBP'),
    '5'=>array('country'=>'CA','currency'=>'CAD'),
    '7'=>array('country'=>'FR','currency'=>'EUR'),
    '9'=>array('country'=>'GE','currency'=>'EUR'),
    '41'=>array('country'=>'AU','currency'=>'AUD'),
);

if (defined('START_TIME') && defined('END_TIME')) {
    $end_dt = date('Y-m-d', strtotime(END_TIME));
    $begin_dt = date('Y-m-d', strtotime(START_TIME));
} else {
    $end_dt = date('Y-m-d');
    $begin_dt = date('Y-m-d', strtotime('-2 month', strtotime($end_dt)));
}

echo "Date setting: ST:{$begin_dt} ET:{$end_dt} \n";

$fws = array();

foreach ($NIDS as $nid => $network) {
    $file_temp = PATH_TMP . '/' . AFF_NAME .$nid. '.csv';

    //download signature order report for all networks
    if (file_exists($file_temp))
        unlink($file_temp);

    $fw = fopen($file_temp, 'w');
    if (!$fw)
        throw new Exception("File open failed {$file_temp}");

    $pass = null;
    $retry = 0;
    $url = str_replace(array('{TOKEN}', '{BDATE}', '{EDATE}', '{NID}'), array(TOKEN, $begin_dt, $end_dt, $nid), URL_API);
    do {
        $retry++;
        if($retry >3)break;
        echo "req => {$url} \n";
        $ch = curl_init($url);
        $curl_opts = array(CURLOPT_HEADER => false,
            CURLOPT_NOBODY => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
            CURLOPT_FILE => $fw,
        );
        curl_setopt_array($ch, $curl_opts);

        $pass = curl_exec($ch);
        curl_close($ch);

        if (!$pass)
            sleep(10);
    } while (!$pass);
    fclose($fw);

    if(!$pass){
        throw new Exception("ERROR:LS get report data failed");
    }

    $fp = fopen($file_temp, 'r');
    if (!$fp)
        throw new Exception("File open failed {$file_temp}");

    $curr_code = isset($network['currency']) ? $network['currency'] : 'USD';
    
    $k = 0;
    while (!feof($fp)) {
        $lr = fgetcsv($fp, 0, ',', '"');

        if (++$k == 1)
            continue;

        if ($lr[0] == "No Results Found")
            continue;

        //process date time
        $_day = $process_dt = date('Y-m-d',strtotime($lr[10]));

        $cur_exr = cur_exchange($curr_code, 'USD', $_day);
        
        $oldsales = (float)str_replace(',','',trim($lr[7]));
        $oldcommission = (float)str_replace(',','',trim($lr[9]));

        $sales = round($oldsales * $cur_exr, 4);
        $commission = round($oldcommission * $cur_exr, 4);

        $replace_array = array(
                    '{createtime}'      => date('Y-m-d',strtotime($lr[10])).' '.$lr[11],
                    '{updatetime}'      => date('Y-m-d',strtotime($lr[4])).' '.$lr[5],
                    '{sales}'           => $sales,
                    '{commission}'      => $commission,
                    '{idinaff}'         => trim($lr[1]),
                    '{programname}'     => trim($lr[2]),
                    '{sid}'             => trim($lr[0]),
                    '{orderid}'         => trim($lr[3]),
                    '{clicktime}'       => date('Y-m-d',strtotime($lr[4])).' '.$lr[5],
                    '{tradeid}'         => empty($lr[12])?'':trim($lr[12]),
                    '{tradestatus}'     => '',
                    '{oldcur}'          => $curr_code,
                    '{oldsales}'        => $oldsales,
                    '{oldcommission}'   => $oldcommission,
                    '{tradetype}'       => '',
                    '{referrer}'        => empty($lr[13])?'':trim($lr[13]),
                    );



        $rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . str_replace('-', '', $_day) . '.upd';
        if (!isset($fws[$rev_file])) {
            $fws[$rev_file] = fopen($rev_file, 'w');
        }

        fwrite($fws[$rev_file], strtr(FILE_FORMAT,$replace_array) . "\n");

    }
    fclose($fp);
    sleep(12);//api allows 5 req per min.
}

foreach ($fws as $file => $f) {
    fclose($f);
}
?>
