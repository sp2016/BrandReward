<?php


define('MAX_RTRY', 5);

try {
    define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");
    define('AFF_NAME', AFFILIATE_NAME);
    define('SECURITY_TOKEN', 'df5301448794c73f18487f17875ca0b5');

    define('REV_DATA', PATH_CODE . '/log/' . AFF_NAME . '.dat');
    //CJ web service 
    define('REPORTWS_URI', 'http://www.linkconnector.com/api/');


    if (defined('START_TIME') && defined('END_TIME')) {
        $end_dt = date('Y-m-d 23:59:59', strtotime(END_TIME));
        $begin_dt = date('Y-m-d 00:00:00', strtotime(START_TIME));
    } else {
        $end_dt = date('Y-m-d 23:59:59');
        $begin_dt = date('Y-m-d 00:00:00', strtotime('-90 days', strtotime($end_dt)));
    }

    echo "Date setting: ST:{$begin_dt} ET:{$end_dt} \n";

    $file_temp = PATH_TMP . '/' . AFF_NAME . '.tmp';
    if (file_exists($file_temp))
        unlink($file_temp);

    $fw = fopen($file_temp, 'w');
    if (!$fw)
        throw new Exception("File open failed {$file_temp}");

    $postdata = array('Key' => SECURITY_TOKEN, 'Function' => 'getReportTransaction', 'StartDate' => $begin_dt, 'EndDate' => $end_dt);

    $rtry = 0;
    do {
        echo "req => " . REPORTWS_URI . " \n";
        $ch = curl_init(REPORTWS_URI);
        $curl_opts = array(CURLOPT_HEADER => false,
            CURLOPT_NOBODY => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
            CURLOPT_FILE => $fw,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postdata,
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
      1===>"Original Date                                                                                                                                                                                2===>Funded Date
      3===>Merchant
      4===>Merchant ID
      5===>Campaign
      6===>Campaign ID
      7===>Event Type
      8===>Link ID
      9===>Commission
      10===>Web Name
      11===>ATID
      12===>Order ID
      13===>Status
      14===>Reason"
      
      
      0=="Original Date",
      1=="Funded Date",
      2=="Click Date",
      3=="Merchant",
      4=="Merchant ID",
      5=="Campaign",
      6=="Campaign ID",
      7=="Event Type",
      8=="Link ID",
      9=="Commission",
      10=="Web Name",
      11=="ATID",
      12=="Order ID",
      13=="Sale Amount",
      14=="Status",
      15=="Reason"
      
0,"Original Date"
1,"Funded Date"
2,"Click Date"
3,"Merchant"
4,"Merchant ID"
5,"Campaign"
6,"Campaign ID"
7,"Event ID"
8,"Event"
9,"Event Type"
10,"Link ID"
11,"Commission"
12,"Web Name"
13,"ATID"
14,"Order ID"
15,"Sale Amount"
16,"Status"
17,"Reason"
      
      
     */
    $comm_all = 0;
    while (!feof($fp)) {
        $lr = fgetcsv($fp, 0, ',', '"');

        if (++$k == 1 || $lr[0] == '')
            continue;

        $sid = trim($lr[13]);
        //if(preg_match('/^s\d{2,3}.*/', $sid))
        //  $sid = str_replace('aa', '_', $sid);

        $oid = $lr[14];
        $mid = $lr[4];
        $mname = $lr[3];
        $tid = $oid;
        $status = trim($lr[16]);

        if (strtolower($status) == 'invalidated') {
            $sale = $rev = 0;
        } else {
            $sale = str_replace(array(',', '$'), '', $lr[15]);
            $rev = str_replace(array(',', '$'), '', $lr[11]);
        }
        
        //transaction date time
        $event_dt = $lr[0];

        //process date time
        $process_dt = $lr[1] == '' ? $event_dt : $lr[1];

        $rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . date('Ymd', strtotime($event_dt)) . '.upd';

        if (!isset($fws[$rev_file])) {
            $fws[$rev_file] = fopen($rev_file, 'w');
            $comms[$rev_file] = 0;
        }
        $cancelreason = trim($lr[17]);
        
        $replace_array = array(
            '{createtime}'      => $event_dt,
            '{updatetime}'      => $process_dt,
            '{sales}'           => $sale,
            '{commission}'      => $rev,
            '{idinaff}'         => $mid,
            '{programname}'     => $mname,
            '{sid}'             => $sid,
            '{orderid}'         => $oid,
            '{clicktime}'       => $lr[2],
            '{tradeid}'         => $tid,
            '{tradestatus}'     => $status,
            '{oldcur}'          => 'USD',
            '{oldsales}'        => $sale,
            '{oldcommission}'   => $rev,
            '{tradetype}'       => '',
            '{referrer}'        => '',
            '{cancelreason}'    => $cancelreason,
        );
        
        fwrite($fws[$rev_file], strtr(FILE_FORMAT,$replace_array) . "\n");

        $comms[$rev_file] += $rev;
        $comm_all += $rev;
    }
    fclose($fp);

    //update revenue file
    foreach ($fws as $file => $f) {
        fclose($f);
	}

} catch (Exception $e) {
    var_dump($e);
}
?>
