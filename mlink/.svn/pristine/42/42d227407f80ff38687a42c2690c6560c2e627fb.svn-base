<?php


define('MAX_RTRY', 5);

try {
    define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");
    define('AFF_NAME', AFFILIATE_NAME);
    define('API_KEY', 'uttyjD1Pe4IqG6VncIKisVyokclFrheZ');

    define('REV_DATA', PATH_CODE . '/log/' . AFF_NAME . '.dat');
    //effil web service
    define('REPORTWS_URI', 'http://apiv2.effiliation.com/apiv2/transaction.csv?key={API-KEY}&start={START}&end={END}&type=datetran');


    /*if (defined('START_TIME') && defined('END_TIME')) {
        $end_dt = date('d/m/Y', strtotime(END_TIME));
        $begin_dt = date('d/m/Y', strtotime(START_TIME));
    } else {
        $end_dt = date('d/m/Y');
        $begin_dt = date('d/m/Y', strtotime('-90 days'));
    }*/
    if (defined('START_TIME') && defined('END_TIME')) {
        $end_dt = date('Y-m-d 23:59:59', strtotime(END_TIME));
        $begin_dt = date('Y-m-d 00:00:00', strtotime(START_TIME));
    } else {
        $end_dt = date('Y-m-d 23:59:59');
        $begin_dt = date('Y-m-d 00:00:00', strtotime('-90 days', strtotime($end_dt)));
    }

    echo "Date setting: ST:{$begin_dt} ET:{$end_dt} \n";
    
    
    while($begin_dt < $end_dt){
        
        $file_temp = PATH_TMP . '/' . AFF_NAME . '.tmp';
        if (file_exists($file_temp))
            unlink($file_temp);
        
        $fw = fopen($file_temp, 'w');
        if (!$fw)
            throw new Exception("File open failed {$file_temp}");
        
        //$temp_end_dt = date('Y-m-d H:i:s',strtotime('+30 days',strtotime($begin_dt)));
        $temp_begin_dt = date('Y-m-d H:i:s',strtotime('-30 days',strtotime($end_dt)));
        if($temp_begin_dt < $begin_dt)
            $temp_begin_dt = $begin_dt;
        
        $requestBegin = date('d/m/Y', strtotime($temp_begin_dt));
        $requestEnd   = date('d/m/Y', strtotime($end_dt));
        $Url = str_replace(array('{API-KEY}','{START}','{END}'), array(API_KEY,$requestBegin,$requestEnd), REPORTWS_URI);
        
        
        $rtry = 0;
        do {
            echo "req => " . $Url . " \n";
            $ch = curl_init($Url);
            $curl_opts = array(CURLOPT_HEADER => false,
                CURLOPT_NOBODY => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
                CURLOPT_FILE => $fw,
            );
            curl_setopt_array($ch, $curl_opts);
        
            $pass = curl_exec($ch);
            curl_close($ch);
        
            if (!$pass) {
                sleep(60);
            }
        
            if (++$rtry > MAX_RTRY)
                throw new Exception("reach at max retry times");
        } while (!$pass);
        fclose($fw);
        
        $fp = fopen($file_temp, 'r');
        if (!$fp)
            throw new Exception("File open failed {$file_temp}");
        $k = 0;
        $fws = $comms = array();
        
        /*
         *   [0] => id_transaction
         [1] => id_affilieur
         [2] => id_programme
         [3] => id_session
         [4] => effi_id
         [5] => effi_id2
         [6] => type
         [7] => montant
         [8] => commission
         [9] => statut
         [10] => date
         [11] => dateclic
         [12] => datetran
         [13] => datevalidation
         [14] => en_session
         [15] => ref
         [16] => ref2
         [17] => ref3
         [18] => ref4
         [19] => ref5
         [20] => ip
         [21] => payed
         [22] => nom_programme
         [23] => referer
         [24] => nom_support
         [25] => id_typelien
         [26] => appareil
         [27] => type_appareil
        
        * */
        
        $comm_all = 0;
        while (!feof($fp)) {
            $lr = fgetcsv($fp, 0, '|', '"');
            if (++$k == 1 || $lr[0] == '')
                continue;
        
            $sid = trim($lr[4]);
             
            $oid = $lr[0];
            $mid = $lr[2];
            $mname = $lr[22];
            $tid = $oid;
            $status = $lr[9];
            $oldsales = $lr[7];
            $oldcommission =  $lr[8];
        
        
            $event_dt = date('Y-m-d H:i:s',strtotime($lr[12]));
            $process_dt = $event_dt;
            $clicktime = date('Y-m-d H:i:s',strtotime($lr[11]));
            $tradetype = $lr[6];
            $referrer = $lr[23];
        
            $_day = date("Y-m-d", strtotime($event_dt));
            $cur_exr = cur_exchange('EUR', 'USD', $_day);
            $sale = round($oldsales * $cur_exr, 4);
            $rev = round($oldcommission * $cur_exr, 4);
        
        
            $rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . date('Ymd', strtotime($event_dt)) . '.upd';
        
            if (!isset($fws[$rev_file])) {
                $fws[$rev_file] = fopen($rev_file, 'w');
                $comms[$rev_file] = 0;
            }
            $cancelreason = '';
        
            $replace_array = array(
                '{createtime}'      => $event_dt,
                '{updatetime}'      => $process_dt,
                '{sales}'           => $sale,
                '{commission}'      => $rev,
                '{idinaff}'         => $mid,
                '{programname}'     => $mname,
                '{sid}'             => $sid,
                '{orderid}'         => $oid,
                '{clicktime}'       => $clicktime,
                '{tradeid}'         => $tid,
                '{tradestatus}'     => $status,
                '{oldcur}'          => 'EUR',
                '{oldsales}'        => $oldsales,
                '{oldcommission}'   => $oldcommission,
                '{tradetype}'       => $tradetype,
                '{referrer}'        => $referrer,
                '{cancelreason}'    => $cancelreason,
            );
        
            fwrite($fws[$rev_file], strtr(FILE_FORMAT,$replace_array) . "\n");
        
            $comms[$rev_file] += $rev;
            $comm_all += $rev;
        }
        fclose($fp);
        $end_dt = $temp_begin_dt;
    }
    
    
    foreach ($fws as $file => $f) {
        fclose($f);
    }
    
} catch (Exception $e) {
    var_dump($e);
}
?>
