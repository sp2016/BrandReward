<?php


define('MAX_RTRY', 5);

try {
    define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");
    define('AFF_NAME', AFFILIATE_NAME);
    define('USR_NAME', AFFILIATE_USER);
    define('USR_PASS', AFFILIATE_PASS);
    
    
    $file_cook = PATH_COOKIE . '/' . AFF_NAME . '.cook';
    if(file_exists($file_cook))
        unlink($file_cook);
    //1 step login 
    //[\"S\",\"am7t31177k7mns5pmckvtshcq4\"]
    $LoginUrl = "http://affiliate.gamesdeal.com/affiliates/login.php#login";
    $content = file_get_contents($LoginUrl);
    preg_match('/\[\\\\"S\\\\",\\\\"(.*?)\\\\"\]/',$content,$m);
    $s = isset($m[1])?$m[1]:'';
    
    $url = 'http://affiliate.gamesdeal.com/scripts/server.php';
    
    $posts = array();
    $posts[] = 'D={"C":"Gpf_Rpc_Server", "M":"run", "requests":[{"C":"Gpf_Auth_Service", "M":"authenticate", "fields":[["name","value"],["Id",""],["username","'.USR_NAME.'"],["password","'.USR_PASS.'"],["rememberMe","Y"],["language","en-US"]]}], "S":"'.$s.'"}';
    
    
    $ch = curl_init($url);
    $curl_opts = array(CURLOPT_HEADER => false,
        CURLOPT_NOBODY => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_COOKIEJAR => $file_cook,
        CURLOPT_COOKIEFILE => $file_cook,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => implode('&', $posts),
    );
    curl_setopt_array($ch, $curl_opts);
    $rs = curl_exec($ch);
    curl_close($ch);
    
    
    

    define('REV_DATA', PATH_CODE . '/log/' . AFF_NAME . '.dat');
    //CJ web service 
    define('REPORTWS_URI', 'http://affiliate.gamesdeal.com/scripts/server.php?C=Pap_Affiliates_Reports_TransactionsGrid&M=getCSVFile&S='.$s.'&FormRequest=Y&FormResponse=Y');
                            

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

    

    $rtry = 0;
    do {
        echo "req => " . REPORTWS_URI . " \n";
        $ch = curl_init(REPORTWS_URI);
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
     *   [0] => 锘緾ommission
  [1] => Total Cost
  [2] => Fixed Cost
  [3] => Order ID
  [4] => Product ID
  [5] => Created
  [6] => Campaign Name
  [7] => Type
  [8] => Tier
  [9] => Affiliate Username
  [10] => Status
  [11] => Paid
  [12] => IP
  [13] => Referrer
  [14] => Recurring Commission ID
  [15] => Payout History ID
  [16] => Click Count
  [17] => First Click Time
  [18] => First Click Referer
  [19] => First Click IP
  [20] => First Click Data 1
  [21] => First Click Data 2
  [22] => Last Click Time
  [23] => Last Click Referer
  [24] => Last Click IP
  [25] => Last Click Data 1
  [26] => Last Click Data 2
  [27] => Extra Data 1
  [28] => Extra Data 2
  [29] => Extra Data 3
  [30] => Extra Data 4
  [31] => Extra Data 5
  [32] => Merchant Note
  [33] => Channel
  [34] => Payout Date
  [35] => id
     * 
     * */
    
    $comm_all = 0;
    while (!feof($fp)) {
        $lr = fgetcsv($fp, 0, ',', '"');
 
        if (++$k == 1 || $lr[0] == '')
            continue;

         
        
        $sid = '';
        //if(preg_match('/^s\d{2,3}.*/', $sid))
        //  $sid = str_replace('aa', '_', $sid);

        $oid = $lr[3];
        $mid = '30503';
        $mname = 'GamesDeal';
        $tid = $oid;
        $status = trim($lr[10]);

         
        $sale = $lr[1];
        $rev = $lr[0];
         

        //transaction date time
        $event_dt = $lr[5];

        //process date time
        $process_dt = $lr[5];

        $rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . date('Ymd', strtotime($event_dt)) . '.upd';

        if (!isset($fws[$rev_file])) {
            $fws[$rev_file] = fopen($rev_file, 'w');
            $comms[$rev_file] = 0;
        }
        $referrer = trim($lr[13]);
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
            '{clicktime}'       => $lr[2],
            '{tradeid}'         => $tid,
            '{tradestatus}'     => $status,
            '{oldcur}'          => 'USD',
            '{oldsales}'        => $sale,
            '{oldcommission}'   => $rev,
            '{tradetype}'       => '',
            '{referrer}'        => $referrer,
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
