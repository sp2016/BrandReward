<?php
 
define('AFF_NAME', AFFILIATE_NAME);
define('User', AFFILIATE_USER);
define('Password', AFFILIATE_PASS);
define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");

if(defined('START_TIME') && defined('END_TIME')) {
    $end_dt   = date('Y-m-d', strtotime(END_TIME));
    $begin_dt = date('Y-m-d', strtotime(START_TIME));
} else {
    $end_dt   = date('Y-m-d');
    $begin_dt = date('Y-m-d', strtotime('-90 days'));
}
echo "Date setting: ST:{$begin_dt} ET:{$end_dt} \n";

$file_temp = PATH_TMP . '/' . AFF_NAME . '.csv';
$file_cook = PATH_COOKIE.'/'.AFF_NAME.'.cook';


//login
$url = 'http://connect.glopss.com/';
$ch = curl_init($url);
$curl_opts = array(
    CURLOPT_HEADER => false,
    CURLOPT_NOBODY => false,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
    //CURLOPT_POST => true,
    //CURLOPT_POSTFIELDS => '_method=POST&data%5B_Token%5D%5Bkey%5D=433338235ab63d732e4ed16d01005217e048a577&data%5BUser%5D%5Bemail%5D=info%40couponsnapshot.com&data%5BUser%5D%5Bpassword%5D=Uska258sdd&data%5B_Token%5D%5Bfields%5D=56b682232e568ff7c2e5968393c245234b610de2%253An%253A0%253A%257B%257D',
);
curl_setopt_array($ch, $curl_opts);
$pass = curl_exec($ch);
preg_match_all('#name=\"data\[\_Token\]\[key\]\" value=\"(.*)\" id#',$pass,$match);
$token = $match[1][0];
preg_match_all('#name=\"data\[\_Token\]\[fields\]\" value=\"(.*)\" id#',$pass,$match);
$field = $match[1][0];


$url = 'http://connect.glopss.com/';
$ch = curl_init($url);
$curl_opts = array(
    CURLOPT_HEADER => true,
    CURLOPT_NOBODY => false,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_COOKIEJAR=>$file_cook,
    CURLOPT_COOKIEFILE=>$file_cook,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => '_method=POST&data%5B_Token%5D%5Bkey%5D='.urlencode($token).'&data%5BUser%5D%5Bemail%5D='.urlencode(User).'&data%5BUser%5D%5Bpassword%5D='.urlencode(Password).'&data%5B_Token%5D%5Bfields%5D='.urlencode($field),
);

curl_setopt_array($ch, $curl_opts);
$pass = curl_exec($ch);


//get sessionToken
$url = 'http://connect.glopss.com/publisher/js/config.php';
$ch = curl_init($url);
$curl_opts = array(
    CURLOPT_HEADER => false,
    CURLOPT_NOBODY => false,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_COOKIEJAR=>$file_cook,
    CURLOPT_COOKIEFILE=>$file_cook,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
);
curl_setopt_array($ch, $curl_opts);
$pass = curl_exec($ch);
preg_match_all('#session_token\":\"(.*)\",\"api_endpoint#',$pass,$match);
$sessionToken = $match[1][0];


//get csv file
$steps[0]['url'] = 'https://api-p03.hasoffers.com/v3/Affiliate_DownloadReport.json';
$steps[0]['req'] = 'POST';
$steps[0]['dat'] = 'method=getConversions&start_date='.$begin_dt.'&end_date='.$end_dt.'&arguments%5Bfields%5D%5B%5D=Stat.datetime&arguments%5Bfields%5D%5B%5D=Offer.name&arguments%5Bfields%5D%5B%5D=Stat.conversion_status&arguments%5Bfields%5D%5B%5D=Stat.payout&arguments%5Bfields%5D%5B%5D=Stat.conversion_sale_amount&arguments%5Bfields%5D%5B%5D=Stat.ad_id&arguments%5Bfields%5D%5B%5D=Stat.affiliate_info1&arguments%5Bfields%5D%5B%5D=Stat.offer_id&arguments%5Bsort%5D%5BStat.datetime%5D=desc&arguments%5Bfilters%5D%5BStat.date%5D%5Bconditional%5D=BETWEEN&arguments%5Bfilters%5D%5BStat.date%5D%5Bvalues%5D%5B%5D='.$begin_dt.'&arguments%5Bfilters%5D%5BStat.date%5D%5Bvalues%5D%5B%5D='.$end_dt.'&arguments%5Bdata_start%5D='.$begin_dt.'&arguments%5Bdata_end%5D='.$end_dt.'&arguments%5Bhour_offset%5D=3&Method=getDownloadReportLink&NetworkId=glopss&SessionToken='.urlencode($sessionToken);

$steps[1]['url'] = 'https://www.baidu.com';
$steps[1]['req'] = 'GET';
$steps[1]['dat'] = '';

foreach ($steps as $k => &$v) {
    $rtry = 0;
    $i = 0;
    do {
        $i++;
        $url = $v['url'];
        $ch = curl_init($url);
        $curl_opts = array(CURLOPT_HEADER => 0,
            CURLOPT_NOBODY => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36',
        );

        if ($v['dat'] != '') {
            $curl_opts[CURLOPT_POST] = true;
            $curl_opts[CURLOPT_POSTFIELDS] = $v['dat'];
        }
        if ($k == 1) {
            if (file_exists($file_temp))
                unlink($file_temp);
            $fw = fopen($file_temp, 'w');
            if (!$fw){
               echo "File open failed {$file_temp}";exit;
            }
                
            $curl_opts[CURLOPT_FILE] = $fw;
        }
        curl_setopt_array($ch, $curl_opts);

        echo "req => {$url}<br />";
        $pass = curl_exec($ch);
        curl_close($ch);
        if ($k == 1) {
            fclose($fw);
        }
        if ($i > 3){
            exit;
        }
    } while (!$pass);


    if ($k == 0){
        $arr = object_array(json_decode($pass));
        $steps[1]['url'] = $arr['response']['data'];
    }
}




$fp = fopen($file_temp, 'r');
if (!$fp){
    echo "File open failed {$file_temp}";exit;
}
     
$k = 0;
$dump = array();
$curr = array();
while (!feof($fp)) {
    $lr = fgetcsv($fp, 0, ',', '"');
    if (++$k <= 1)
        continue;
    if(empty($lr[0]))
        continue;
    
    $sid = trim($lr[6]);
    $oid = trim($lr[5]);
    $mname = trim($lr[1]);
    $mid = trim($lr[7]);
    $sale = round(floatval(trim($lr[4])), 4);
    $rev = round(floatval(trim($lr[3])), 4);
    $status = trim($lr[2]);

    $d = new DateTime(trim($lr[0]));
    $click_dt=$event_dt = $process_dt = $d->format('Y-m-d H:i:s');
    $tradeId = '';
    $currency = 'USD';
    $oldsale = $sale;
    $oldcomm = $rev;
    $tradeType = '';

    $date = date('Y-m-d', strtotime($event_dt));


    if(!isset($curr[$currency][$date])){
        $curr[$currency][$date] = cur_exchange($currency, 'USD', $date);
    }
    $cur_exr = $curr[$currency][$date];
    $rev = round($rev * $cur_exr, 4);
    $sale = round($sale * $cur_exr, 4);

    $dump[$date][] = $event_dt . "\t" . $process_dt . "\t" . $sale . "\t" . $rev . "\t" . $mid . "\t" . $mname . "\t" . $sid . "\t" . $oid . "\t" . $click_dt . "\t" . $tradeId . "\t" . $status . "\t" . $currency . "\t" . $oldsale . "\t" . $oldcomm . "\t" . $tradeType . "\n";
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
        
        $lr = explode("\t", $v);
        $replace_array = array(
            '{createtime}'      => $lr[0],
            '{updatetime}'      => $lr[1],
            '{sales}'           => $lr[2],
            '{commission}'      => $lr[3],
            '{idinaff}'         => $lr[4],
            '{programname}'     => $lr[5],
            '{sid}'             => $lr[6],
            '{orderid}'         => $lr[7],
            '{clicktime}'       => $lr[8],
            '{tradeid}'         => $lr[7],
            '{tradestatus}'     => $lr[10],
            '{oldcur}'          => $lr[11],
            '{oldsales}'        => $lr[12],
            '{oldcommission}'   => $lr[13],
            '{tradetype}'       => '',
            '{referrer}'        => '',
            '{cancelreason}'    => '',
        );
        
        fwrite($fw, strtr(FILE_FORMAT,$replace_array) . "\n");
    }
    fclose($fw);
}    
 

function object_array($array) {
    if(is_object($array)) {
        $array = (array)$array;
    } if(is_array($array)) {
        foreach($array as $key=>$value) {
            $array[$key] = object_array($value);
        }
    }
    return $array;
}

?>
