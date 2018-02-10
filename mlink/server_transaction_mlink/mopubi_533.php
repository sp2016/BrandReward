<?php
try{
    define('AFF_NAME', AFFILIATE_NAME);
    define('USR_NAME', AFFILIATE_USER);
    define('USR_PASS', AFFILIATE_PASS);
    define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");


    if(defined('START_TIME') && defined('END_TIME')) {
        $end_dt   = date('m/d/Y', strtotime(END_TIME));
        $begin_dt = date('m/d/Y', strtotime(START_TIME));
    }
    else {
        $end_dt   = date('m/d/Y');
        $begin_dt = date('m/d/Y', strtotime('-91 days'));
    }

    echo "Date setting: ST:{$begin_dt} ET:{$end_dt} \n";

    $file_temp = PATH_TMP.'/'.AFF_NAME.'.tmp';
    $file_cook = PATH_COOKIE.'/'.AFF_NAME.'.cook';

    //setting request steps



    $steps[0]['url'] = 'http://console.mopubi.com/login.ashx';
    $steps[0]['req'] = 'POST';
    $steps[0]['dat'] = 'u='.urlencode(USR_NAME).'&p='.urlencode(USR_PASS);


    $steps[1]['url'] = 'http://console.mopubi.com/affiliates/Extjs.ashx?s=leadreport';
    $steps[1]['req'] = 'POST';
    $steps[1]['dat'] = 'start_date='.urlencode($begin_dt).'&end_date='.urlencode($end_dt).'&show_changes=0&offer=&campaign=&disposition_type=&date_range=custom&change_since=&include_new_conversions=0&o=date&d=ASC&report_id=97&report_view_id=97&csv=1';


    foreach ($steps as $k=> $v) {
        $rtry = 0;
        do {
            //download signature order report for all networks
            if (file_exists($file_temp))
                unlink($file_temp);

            $fw = fopen ($file_temp, 'w');
            if (!$fw)
                throw new Exception ("File open failed {$file_temp}");

            $url = $v['url'];

            $ch = curl_init($url);
            $curl_opts = array(CURLOPT_HEADER=>false,
                CURLOPT_NOBODY=>false,
//                CURLOPT_HTTPHEADER=>array("X-Requested-With:XMLHttpRequest"),
                CURLOPT_RETURNTRANSFER=>true,
                CURLOPT_FOLLOWLOCATION=>true,
                CURLOPT_SSL_VERIFYPEER=>false,
                CURLOPT_SSL_VERIFYHOST=>false,
                CURLOPT_COOKIEJAR=>$file_cook,
                CURLOPT_COOKIEFILE=>$file_cook,
                CURLOPT_USERAGENT=>'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
                CURLOPT_FILE => $fw,
            );

            if ($v['dat'] != '') {
                $curl_opts[CURLOPT_POST] = true;
                $curl_opts[CURLOPT_POSTFIELDS] = $v['dat'];
            }
            curl_setopt_array($ch, $curl_opts);

            echo "req => {$url} ";
            $pass = curl_exec($ch);
            curl_close($ch);
            fclose($fw);

            if (!$pass) {
                echo " failed, waiting for 60 seconds retry \n";
                sleep(60);
            }else{
                echo " successed \n";
            }
            if (!$pass && 3 < ++$rtry)
                throw new Exception ("Exceed max re-try limits 3");

        }while (!$pass);
    }




    //get program name
    $dsn = 'mysql:dbname='.DB_BDG_NAME.';host='.DB_BDG_HOST;
    $db = new PDO($dsn, DB_BDG_USER, DB_BDG_PASS);
    $sql = "SELECT IDINAFF, NAME FROM program WHERE AFFID = 533";
    $sth = $db->prepare($sql);
    $sth->execute();
    $row = $sth->fetchAll(PDO::FETCH_ASSOC);
    $mers = array();
    foreach($row as $v){
        $k = strtolower(trim($v['NAME']));
        if(!empty($k)){
            $mers[$k] = $v['IDINAFF'];
        }
    }
    $sth->closeCursor();
    $fp = fopen ($file_temp, 'r');
    if (!$fp)
        throw new Exception ("File open failed {$file_temp}");

    $k = 0;
    $dump = array();
    $curr = array();
    while (!feof($fp)) {
        $lr = fgetcsv($fp,0,',','"');

        if (++$k <= 1)
            continue;
//        $lr = explode('|',$lr[0]);
        if(empty($lr[0]))
            continue;

        $sid =  trim($lr[8]);
        $oid   = trim($lr[1]);
        $mname = trim($lr[4]);
        $mid   = isset($mers[strtolower($mname)])?$mers[strtolower($mname)] : 0;
        $sale  = round(floatval(trim(trim($lr[13]),'$')),4);
        $rev   = round(floatval(trim(trim($lr[12]),'$')),4);
        $tid   = $lr[0];
        $status = $lr[16];
        $d = new DateTime($lr[2]);
        $event_dt = $process_dt = $d->format('Y-m-d H:i:s');
        $date = date('Y-m-d', strtotime($event_dt));
        $click_dt = '';
	    $currency = 'USD';
        $oldsale = $sale;
	    $oldcomm = $rev;
	    $tradeType = $lr[6];

        $dump[$date][] = $event_dt."\t".$process_dt."\t".$sale."\t".$rev."\t".$mid."\t".$mname."\t".$sid."\t".$oid."\t".$click_dt."\t".$tid."\t".$status."\t"."$currency"."\t".$oldsale."\t".$oldcomm."\t".$tradeType;
    }

    
    $file = PATH_DATA . '/' . AFF_NAME;
    echo $file . '/n';
    if (!is_dir($file))
        mkdir($file, 0755);
    foreach ($dump as $d => $val) {
        
        $file_new = PATH_DATA . '/' . AFF_NAME . '/revenue_' . str_replace('-', '', $d) . '.upd';
        $fw = fopen($file_new, 'w');
        if (!$fw)
            continue;
        
        foreach ($val as $k => $v) {
            
            $data = explode("\t",$v);
            $cancelreason = '';
            $replace_array = array(
                '{createtime}'      => $data[0],
                '{updatetime}'      => $data[1],
                '{sales}'           => $data[2],
                '{commission}'      => $data[3],
                '{idinaff}'         => $data[4],
                '{programname}'     => $data[5],
                '{sid}'             => $data[6],
                '{orderid}'         => $data[7],
                '{clicktime}'       => '',
                '{tradeid}'         => $data[9],
                '{tradestatus}'     => $data[10],
                '{oldcur}'          => 'USD',
                '{oldsales}'        => $data[12],
                '{oldcommission}'   => $data[13],
                '{tradetype}'       => $data[14],
                '{referrer}'        => '',
                '{cancelreason}'    => $cancelreason,
            );
            
            //fwrite($fw, $v);
            fwrite($fw, strtr(FILE_FORMAT,$replace_array) . "\n");
        }
        fclose($fw);
    }
}
catch (Exception $e) {
    echo $e->getMessage();
    exit(1);
}


?>
