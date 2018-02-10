<?php
define('AFF_NAME', AFFILIATE_NAME);
define('AFF_ID', '1273061');
define('AFF_API_TOKEN', 'fseaGaeoRUGCc3Tb');
define('AFF_API_SECRET_KEY', 'RIv8sm9p7RSnuz7aBGd6xk0q7EZulz8z');
define('AFF_API_VERSION', '1.7');
define('AFF_API_ACTION', 'activity');
define('AFF_API_URI', 'https://shareasale.com/x.cfm?action={ACTION}&affiliateId={AFFID}&token={TOKEN}&dateStart={FD}&dateEnd={TD}&version={VERSION}');
define('REV_DATA', PATH_CODE . '/log/' . AFF_NAME . '.dat');
define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");

if (defined('START_TIME') && defined('END_TIME')) {
    $end_dt = date('Y-m-d', strtotime(END_TIME));
    $begin_dt = date('Y-m-d', strtotime(START_TIME));
} else {
    $end_dt = date('Y-m-d');
    $begin_dt = date('Y-m-d', strtotime('-120 days', strtotime($end_dt)));
}

$runday = date('Y-m-d');
echo "Date setting: ST:{$begin_dt} ET:{$end_dt} \n";

$file_temp = PATH_TMP . '/' . AFF_NAME . '.tmp';
$file_cook = PATH_COOKIE . '/' . AFF_NAME . '.cook';

//download signature order report for all networks
if (file_exists($file_temp))
    unlink($file_temp);
if(file_exists($file_cook))
    unlink($file_cook);

$timestamp = gmdate(DATE_RFC1123);
$sig = AFF_API_TOKEN . ':' . $timestamp . ':' . AFF_API_ACTION . ':' . AFF_API_SECRET_KEY;
$sig = hash("sha256", $sig);
$headers = array("x-ShareASale-Date: {$timestamp}", "x-ShareASale-Authentication: {$sig}");

$begin_date = $begin_dt;
$comm_all = 0;
while ($begin_dt < $end_dt) {
    $tmp_dt = date('Y-m-d', strtotime('-30 day', strtotime($end_dt)));
    $tmp_dt = $tmp_dt < $begin_dt ? $begin_dt : $tmp_dt;
    $url = str_replace(array('{FD}', '{TD}', '{ACTION}', '{TOKEN}', '{VERSION}', '{AFFID}'), array(date('m/d/Y', strtotime($tmp_dt)), date('m/d/Y', strtotime($end_dt)), AFF_API_ACTION, AFF_API_TOKEN, AFF_API_VERSION, AFF_ID), AFF_API_URI);

    echo "req => {$url} \n";
    $ch = curl_init($url);

    $fw = fopen($file_temp, 'a+');
    if (!$fw)
        throw new Exception("File open failed {$file_temp}");

    $curl_opts = array(CURLOPT_NOBODY => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEJAR => $file_cook,
        CURLOPT_COOKIEFILE => $file_cook,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
        CURLOPT_FILE => $fw,
        CURLOPT_HTTPHEADER => $headers,
		CURLOPT_SSL_VERIFYPEER => false,

    );
    curl_setopt_array($ch, $curl_opts);
    curl_exec($ch);
	curl_error($ch);
    curl_close($ch);
    fclose($fw);

    $end_dt = date('Y-m-d', strtotime('-1 day', strtotime($tmp_dt)));
}



$fp = fopen($file_temp, 'r');
if (!$fp)
    throw new Exception("File open failed {$file_temp}");

$k = 0;
$dump = array();
$curr = array();
while (!feof($fp)) {
    $lr = fgetcsv($fp, 0, '|', '"');

    if (++$k == 1)
        continue;
    if ($lr[0] == "")
        continue;
    /*
      0===>Trans ID
      1===>User ID
      2===>Merchant ID
      3===>Trans Date
      4===>Trans Amount
      5===>Commission
      6===>Comment
      7===>Voided
      8===>Pending Date
      9===>Locked
      10===>Aff Comment
      11===>Banner Page
      12===>Reversal Date
      13===>Click Date
      14===>Click Time
      15===>Banner Id
      16===>SKU List
      17===>Quantity List
      18===>Lock Date
      19===>Paid Date
      20===>Merchant Organization
      21===>Merchant Website
     */
    if (count($lr) < 22){
        continue;
#            throw new Exception("Error data format");
    }

    if($lr[7]){
        $sales = $commission = 0;
    }else{
        $sales = trim($lr[4]);
        $commission = trim($lr[5]);
    }
    
    $day = date('Y-m-d H:i:s', strtotime($lr[3]));

    $tradestatus = 'Pending';
    $lr[7] = trim($lr[7]);
    $lr[9] = trim($lr[9]);
    if(!empty($lr[9])){
        $tradestatus = 'Locked';
    }
    if(!empty($lr[7])){
        $tradestatus = 'Voided';
    }

    $cancelreason = '';
    if($tradestatus == 'Voided'){
        $cancelreason = trim($lr[6]);
    }

    $replace_array = array(
                    '{createtime}'      => date('Y-m-d H:i:s', strtotime(trim($lr[3]))),
                    '{updatetime}'      => date('Y-m-d H:i:s', strtotime(trim($lr[3]))),
                    '{sales}'           => $sales,
                    '{commission}'      => $commission,
                    '{idinaff}'         => trim($lr[2]),
                    '{programname}'     => trim($lr[20]),
                    '{sid}'             => trim($lr[10]),
                    '{orderid}'         => '',
                    '{clicktime}'       => date('Y-m-d H:i:s', strtotime(date('Y-m-d', strtotime(trim($lr[13]))) . ' ' . trim($lr[14]))),
                    '{tradeid}'         => trim($lr[0]),
                    '{tradestatus}'     => $tradestatus,
                    '{oldcur}'          => 'USD',
                    '{oldsales}'        => $sales,
                    '{oldcommission}'   => $commission,
                    '{tradetype}'       => '',
                    '{referrer}'        => $lr[11],
                    '{cancelreason}'    => $cancelreason,
                    );

    //this kind of transaction is a total pay
    if ($replace_array['{sales}'] == 0 && $replace_array['{commission}'] < 0 && $replace_array['{programname}'] == 'shareasale.com' && $replace_array['{idinaff}'] == 47) //$mid == 47
        continue;

    //should have merchant id
    if ($replace_array['{idinaff}'] == 0 || $replace_array['{idinaff}'] == '' || $lr[3] == '')
        continue;
   $type = ''; $orderid = '';
   if (stripos($lr[6], ' - '))
	    list($type,$orderid) = explode(' - ',trim($lr[6]));
   
    $replace_array['{orderid}'] = trim($orderid);
    $replace_array['{tradetype}'] = trim($type);

    $rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . date('Ymd', strtotime($day)) . '.upd';
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
