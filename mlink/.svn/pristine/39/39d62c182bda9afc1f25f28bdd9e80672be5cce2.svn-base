<?php
define('MAX_RTRY', 5);

try {

    define('AFF_NAME', AFFILIATE_NAME);
    define('SECURITY_TOKEN', "00973c68e5c0d8ac6eba2706f9e81dfb02c087749be2d9380dd706ad63bda85326376fd3eec5cbce3735d1df7bebcac234ac52c37fa0cc4fd3e284a6515ca01e7d/469ac94e19ce0e12538dcccff6f1a8320cb83054667f8a8fcb872e839613735d74bc62ed454aa7e6372c8d681e627729831a383a09f7aac1cca2d04b9ee26d81");
    define('REV_DATA', PATH_CODE . '/log/' . AFF_NAME . '.dat');

    //CJ web service 
    define('REPORTWS_URI', 'https://commission-detail.api.cj.com/v3/commissions?date-type=event&start-date={BEGIN_DATE}&end-date={END_DATE}');
    define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}");


    if (defined('START_TIME') && defined('END_TIME')) {
        $end_dt = date('Y-m-d', strtotime(END_TIME));
        $begin_dt = date('Y-m-d', strtotime(START_TIME));
    } else {
        $end_dt = date('Y-m-d', strtotime('+1 day'));
        $begin_dt = date('Y-m-d', strtotime('-120 days', strtotime($end_dt)));
    }

    echo "Date setting: ST:{$begin_dt} ET:{$end_dt} \n";

    $file_temp = PATH_TMP . '/' . AFF_NAME . '.tmp';
	
    $comm_all = 0;
    while ($begin_dt <= $end_dt) {
        //$td = date('Y-m-d', strtotime('+5 days', strtotime($begin_dt)));
        $td = date('Y-m-d', strtotime('-5 days', strtotime($end_dt)));

        //download signature order report for all networks
        if (file_exists($file_temp))
            unlink($file_temp);


        $url = str_replace(array('{BEGIN_DATE}', '{END_DATE}'), array($begin_dt > $td ? $begin_dt : $td, $end_dt), REPORTWS_URI);
        $rtry = 0;
        do {
            $fw = fopen($file_temp, 'w');
            if (!$fw)
                throw new Exception("File open failed {$file_temp}");

            echo "req => {$url} \n";
            $ch = curl_init($url);
            $curl_opts = array(CURLOPT_HEADER => false,
                CURLOPT_NOBODY => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
                CURLOPT_FILE => $fw,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_HTTPHEADER => array('Authorization: ' . SECURITY_TOKEN),
            );
            curl_setopt_array($ch, $curl_opts);

            $pass = curl_exec($ch);
			//echo $pass;
            $rs = curl_close($ch);
            fclose($fw);
            if (!$pass) {
                $rtry++;
                sleep(20);
                if ($rtry > MAX_TRY)
                    throw new Exception("reach at max login times");
            }
        } while (!$pass);
        $xml = simplexml_load_file($file_temp);

        if (isset($xml->commissions) && isset($xml->commissions->commission) && count($xml->commissions->commission) > 0) {
            $fws = $comms = array();
            $col_postdt = 'posting-date';
            $col_eventdt = 'event-date';
            $col_sid = 'sid';
            $col_sale = 'sale-amount';
            $col_comm = 'commission-amount';
            $col_mname = 'advertiser-name';
            $col_mid = 'cid';
            $col_oid = 'order-id';
            $col_status = 'action-status';
            $col_tid = 'commission-id';
            $col_actiontp = 'action-type';

            foreach ($xml->commissions->commission as $d) {
                $file_day = date('Y-m-d', strtotime((string) $d->$col_eventdt));
               	if ($file_day > $end_dt)
                    continue;

                $rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . str_replace('-', '', $file_day) . '.upd';
                if (!isset($fws[$rev_file])) {
                    $fws[$rev_file] = fopen($rev_file, 'w');
                    $comms[$rev_file] = 0;
                }

                //if($d->$col_status == 'closed')
                //    continue;

                $replace_array = array(
                    '{createtime}'      => date('Y-m-d H:i:s', strtotime((string) $d->$col_eventdt)),
                    '{updatetime}'      => date('Y-m-d H:i:s', strtotime((string) $d->$col_postdt)),
                    '{sales}'           => (string) $d->$col_sale,
                    '{commission}'      => (string) $d->$col_comm,
                    '{idinaff}'         => (string) $d->$col_mid,
                    '{programname}'     => (string) $d->$col_mname,
                    '{sid}'             => (string) $d->$col_sid,
                    '{orderid}'         => (string) $d->$col_oid,
                    '{clicktime}'       => date('Y-m-d H:i:s', strtotime((string) $d->$col_eventdt)),
                    '{tradeid}'         => (string) $d->$col_tid,
                    '{tradestatus}'     => (string) $d->$col_status,
                    '{oldcur}'          => 'USD',
                    '{oldsales}'        => (string) $d->$col_sale,
                    '{oldcommission}'   => (string) $d->$col_comm,
                    '{tradetype}'       => (string) $d->$col_actiontp,
                    '{referrer}'        => '',
                    );

                fwrite($fws[$rev_file], strtr(FILE_FORMAT,$replace_array) . "\n");
                $comms[$rev_file] += (string) $d->$col_comm;
                $comm_all+=(string) $d->$col_comm;
            }

            //update revenue file
            foreach ($fws as $file => $f) {
                fclose($f);
            }
        }

        $end_dt = $td;
    }
}
catch (Exception $e) {
    var_dump($e);
    mail('13816878570@139.com', 'CJ Crawler Failed', $e->getMessage());
}
?>
