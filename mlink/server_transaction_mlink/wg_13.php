<?php

define('AFF_NAME', AFFILIATE_NAME);
define('USER_NAME', AFFILIATE_USER);
define('USER_PASS', AFFILIATE_PASS);
define('REV_DATA', PATH_CODE . '/log/' . AFF_NAME . '.dat');
define('PAGE_SIZE', '-15 day');

$SITE_CAMPAIGNS = array('uk' => '192821',
				    );

define('AWS_LOCATION', 'http://ws.webgains.com/aws.php');
define('AWS_URI', 'http://ws.webgains.com/aws.php');
define('AWS_ACTION', 'getFullEarningsWithCurrency');
define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");

if (defined('START_TIME') && defined('END_TIME')) {
    $end_d = date('Y-m-d', strtotime(END_TIME));
    $start_d = date('Y-m-d', strtotime(START_TIME));
} 
else {
    $end_d = date('Y-m-d');
    $start_d = date('Y-m-d', strtotime('-100 days', strtotime($end_d)));
}

echo "Date setting: ST:{$start_d} ET:{$end_d} \n";

if( $start_d > $end_d ){
    echo "start_date is bigger than end_date \n";exit();
}

$client = new SoapClient(null, array('location' => AWS_LOCATION, 'uri' => AWS_URI, 'trace' => true));
$file_comm = array();
$comm_curr = array();
$comm_all = 0;
$fws = array();

$d = new DateTime($end_d);
while($d->format('Y-m-d') >= $start_d){

    $end_dt = $d->format('Y-m-d').'T23:59:59';
    $d->modify(PAGE_SIZE);
    if($d->format('Y-m-d') < $start_d){
        $start_dt = $start_d.'T00:00:00';
    }else{
        $start_dt = $d->format('Y-m-d').'T00:00:00';
    }
    $d->modify('-1 day');
    echo "Doing page: ST:{$start_dt} ET:{$end_dt} \n";

    #get api data
    foreach ($SITE_CAMPAIGNS as $site => $campaignid) {

        $st = new SoapVar($start_dt, XSD_DATETIME, 'startdate', 'xsd:dateTime');
        $et = new SoapVar($end_dt, XSD_DATETIME, 'enddate', 'xsd:dateTime');
        $us = new SoapVar(USER_NAME, XSD_STRING, 'username', 'xsd:string');
        $pa = new SoapVar(USER_PASS, XSD_STRING, 'password', 'xsd:string');
        $cp = new SoapVar($campaignid, XSD_INT, 'campaignid', 'xsd:int');

        $rtry = 0;
        $r = array();
        do {
            try {
                $r = $client->__soapCall(AWS_ACTION, array($st, $et, $cp, $us, $pa), array('location' => AWS_LOCATION,
                    'uri' => AWS_URI,
                    'soapaction' => 'http://ws.webgains.com/aws.php#' . AWS_ACTION)
                );
                
                $pass = false;
            }
            catch (Exception $e) {
                if (++$rtry <= 5) {                    
                    $pass = true;
                    sleep(30);
                }
                else {
                    $pass = false;
                    continue 2;
                }
            }
        }while ($pass);

        if (!is_array($r) || count($r) == 0)
            continue;
        
        foreach ($r as $o) {
            print_r($o);exit;
            //2011-12-18T09:37:52   
            if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}/', $o->date))
                continue;


            $day = date('Y-m-d', strtotime($o->date));

            $currency = cur_exchange($o->currency, 'USD', $day);
            $cancel = stripos($o->status, 'cancelled') !== false? 0 : 1;
            $sales = trim($o->saleValue) * $currency * $cancel;
            $rev   = trim($o->commission) * $currency * $cancel;
            $cancelreason = '';


            $replace_array = array(
                    '{createtime}'      => date('Y-m-d H:i:s', strtotime(trim($o->date))),
                    '{updatetime}'      => date('Y-m-d H:i:s', strtotime(trim($o->date))),
                    '{sales}'           => round($sales, 4),
                    '{commission}'      => round($rev, 4),
                    '{idinaff}'         => trim($o->programID),
                    '{programname}'     => trim($o->programName),
                    '{sid}'             => trim($o->clickRef),
                    '{orderid}'         => '',
                    '{clicktime}'       => date('Y-m-d H:i:s', strtotime(trim($o->clickthroughTime))),
                    '{tradeid}'         => trim($o->transactionID),
                    '{tradestatus}'     => trim($o->status),
                    '{oldcur}'          => trim($o->currency),
                    '{oldsales}'        => trim($o->saleValue),
                    '{oldcommission}'   => trim($o->commission),
                    '{tradetype}'       => '',
                    '{referrer}'        => trim($o->referrer),
                    '{cancelreason}'    => $cancelreason,
                    );

            $rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . date('Ymd', strtotime($day)) . '.upd';
            if (!isset($fws[$rev_file])) {
                $fws[$rev_file] = fopen($rev_file, 'w');
                $comms[$rev_file] = 0;
            }

            fwrite($fws[$rev_file], strtr(FILE_FORMAT,$replace_array) . "\n");

        }
    }

    

}
    
foreach ($fws as $file => $f) {
    fclose($f);
}

?>