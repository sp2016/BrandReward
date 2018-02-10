<?php
define('AFF_NAME', AFFILIATE_NAME);
define('USR_NAME', AFFILIATE_USER);
define('USR_PASS', AFFILIATE_PASS);
define('MAX_RTRY', 5);
define('REV_DATA', PATH_CODE . '/log/' . AFF_NAME . '.dat');
define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");

$curr_map = array('UK' => 'GBP', 'AU' => 'AUD', 'US' => 'USD', 'EU' => 'EUR', 'NZ' => 'NZD', 'CA' => 'CAD', 'JP' => 'JPY');

if (defined('START_TIME') && defined('END_TIME')) {
    $end_dt = date('Y-m-d', strtotime(END_TIME));
    $begin_dt = date('Y-m-d', strtotime(START_TIME));
} else {
    $end_dt = date('Y-m-d');
    $begin_dt = date('Y-m-d', strtotime('-120 days'));
}

echo "Date setting: ST:{$begin_dt} ET:{$end_dt} \n";

$fws = array();
$file_temp = PATH_TMP . '/' . AFF_NAME . '.tmp';
if(file_exists($file_temp))
    unlink($file_temp);
$file_cook = PATH_COOKIE . '/' . AFF_NAME . '.cook';
if(file_exists($file_cook))
    unlink($file_cook);

//login
$url = 'https://www.clixgalore.com/Memberlogin.aspx';
$rtry = 0;
$pass = true;
do {
    echo "req=>{$url}\n";

    $ch = curl_init($url);
    $curl_opts = array(CURLOPT_HEADER => false,
        CURLOPT_NOBODY => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_COOKIEJAR => $file_cook,
        CURLOPT_COOKIEFILE => $file_cook,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
    );
    curl_setopt_array($ch, $curl_opts);
    $rs = curl_exec($ch);
    curl_close($ch);
    $posts = array();
    if (preg_match_all('/<input type="hidden" name="([^"]+)"[^>]*value="([^"]+)"[^>]*>/U', $rs, $m)) {
        foreach ($m[1] as $k => $qn) {
            array_push($posts, $qn . '=' . urlencode($m[2][$k]));
        }
    }


    array_push($posts, 'txt_UserName=' . urlencode(USR_NAME));
    array_push($posts, 'txt_Password=' . urlencode(USR_PASS));
    array_push($posts, '&cmd_login.x=33&cmd_login.y=15');


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


    $url = 'http://www.clixgalore.com/MemberHome.aspx';
    echo "req=>{$url}\n";

    $ch = curl_init($url);
    $curl_opts = array(CURLOPT_HEADER => false,
        CURLOPT_NOBODY => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_COOKIEJAR => $file_cook,
        CURLOPT_COOKIEFILE => $file_cook,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
    );
    curl_setopt_array($ch, $curl_opts);
    $rs = curl_exec($ch);
    curl_close($ch);

    if (!$rs || stripos($rs, 'clixGalore - Member Home') == false) {
        $rtry++;
        $pass = false;
        if ($rtry > MAX_RTRY)
            throw new Exception("Reach at MAX login times");
    }
}while (!$pass);



//dump approved data
/*
* url param explain
* SD: start date
* ED: end date
* ST: status (1:confirm 2:pending)
* B:  export by date type (1:confirm date 2:trans date)
*/

$url = 'http://www.clixgalore.com/AffiliateTransactionSentReport_Export.aspx?AfID=0&ST=1&RP=6&CID=238939&S2=&AdID=0&SD='.$begin_dt.'&ED='.$end_dt.'&B=1&type=exl';
$fw = fopen($file_temp, 'w+');
if (!$fw)
    throw new Exception("{$file_temp} open failed");


$rtry = 0;
$pass = true;
do {
    echo "req=>{$url}\n";

    $ch = curl_init($url);
    $curl_opts = array(CURLOPT_HEADER => false,
        CURLOPT_NOBODY => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_COOKIEJAR => $file_cook,
        CURLOPT_COOKIEFILE => $file_cook,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
        CURLOPT_FILE => $fw,
    );
    curl_setopt_array($ch, $curl_opts);

    $rs = curl_exec($ch);
    curl_close($ch);
    if (!$rs) {
        $rtry++;
        $pass = false;
        if ($rtry > MAX_RTRY)
            throw new Exception("Reach at MAX set cookie times");
    }
}while (!$pass);
fclose($fw);

/*
  [1] => Array
  (
  [0] => Confirmed. Date (GMT)
  [1] => Trans. Date (GMT)
  [2] => Merchant Site
  [3] => Currency
  [4] => Sale Value
  [5] => Commission
  [6] => Aff. Order ID
  [7] => Source
  [8] => Click Date (GMT)
  [9] => IP Address
  [10] => Status
  [11] => Banner
  [12] => Information
  )

 */
echo $file_temp . "\n";
$contents = file_get_contents($file_temp);
$chrType = checkBom($contents,true);
if($chrType != 'UTF-8'){
    $contents = iconv($chrType,'UTF-8//IGNORE',$contents);
}
$contents = checkBom($contents);
$lines = explode("\n",$contents);
foreach($lines as $k=>$v){
    $line = trim($v);
    if(empty($line))
        continue;

    if($k < 1)
        continue;

    $data = explode("\t",$line);

    $created = date('Y-m-d H:i:s', strtotime($data[1]));
    $day = date('Y-m-d', strtotime($data[1]));
    $sales_tmp = getCur($data[4]);
    $oldsales = $sales_tmp['num'];
    $oldcur = $sales_tmp['cur'];
    $oldcommission = $data[5];

    $mid = 0;
    $mname = trim($data[2]);
    $sid = trim($data[6]);
    $orderid = 0;
    $tradeid = 0;

    $cur_exr = cur_exchange($oldcur, 'USD', $day);
    $sales = round($oldsales * $cur_exr, 4);
    $commission = round($oldcommission * $cur_exr, 4);

    $tradestatus = trim($data[10]);
    $referrer = trim($data[7]);

    $cancelreason = '';

    $replace_array = array(
                    '{createtime}'      => $created,
                    '{updatetime}'      => $created,
                    '{sales}'           => $sales,
                    '{commission}'      => $commission,
                    '{idinaff}'         => $mid,
                    '{programname}'     => $mname,
                    '{sid}'             => $sid,
                    '{orderid}'         => $orderid,
                    '{clicktime}'       => '',
                    '{tradeid}'         => $tradeid,
                    '{tradestatus}'     => $tradestatus,
                    '{oldcur}'          => $oldcur,
                    '{oldsales}'        => $oldsales,
                    '{oldcommission}'   => $oldcommission,
                    '{tradetype}'       => '',
                    '{referrer}'        => $referrer,
                    '{cancelreason}'    => $cancelreason,
                    );

    $rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . str_replace('-', '', $day) . '.upd';
    if (!isset($fws[$rev_file])) {
        $fws[$rev_file] = fopen($rev_file, 'w');
    }
    fwrite($fws[$rev_file], strtr(FILE_FORMAT,$replace_array) . "\n");
}



//dump pending data
$url = 'http://www.clixgalore.com/AffiliateTransactionSentReport_Export.aspx?AfID=0&ST=2&RP=6&CID=238939&S2=&AdID=0&SD='.$begin_dt.'&ED='.$end_dt.'&B=1&type=exl';
$file_temp = $file_temp . '.pending';
$fw = fopen($file_temp, 'w+');
if (!$fw)
    throw new Exception("{$file_temp} open failed");

$rtry = 0;
$pass = true;
do {
    echo "req=>{$url}\n";

    $ch = curl_init($url);
    $curl_opts = array(CURLOPT_HEADER => false,
        CURLOPT_NOBODY => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_COOKIEJAR => $file_cook,
        CURLOPT_COOKIEFILE => $file_cook,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
        CURLOPT_FILE => $fw,
    );
    curl_setopt_array($ch, $curl_opts);

    $rs = curl_exec($ch);
    curl_close($ch);
    if (!$rs) {
        $rtry++;
        $pass = false;
        if ($rtry > MAX_RTRY)
            throw new Exception("Reach at MAX set cookie times");
    }
}while (!$pass);
fclose($fw);


/*
  [1] => Array
  (
  [0] => Trans. Date (GMT)
  [1] => Merchant Site
  [2] => Currency
  [3] => Sale Value
  [4] => Commission
  [5] => Aff. Order ID
  [6] => Source
  [7] => Click Date (GMT)
  [8] => IP Address
  [9] => Status
  [10] => Banner
  [11] => Information
  )

 */
echo $file_temp . "\n";
$contents = file_get_contents($file_temp);
$chrType = checkBom($contents,true);
if($chrType != 'UTF-8'){
    $contents = iconv($chrType,'UTF-8//IGNORE',$contents);
}
$contents = checkBom($contents);
$lines = explode("\n",$contents);
foreach($lines as $k=>$v){
    $line = trim($v);
    if(empty($line))
        continue;

    if($k < 1)
        continue;

    $data = explode("\t",$line);

    $created = date('Y-m-d H:i:s', strtotime($data[0]));
    $day = date('Y-m-d', strtotime($data[0]));
    $sales_tmp = getCur($data[3]);
    $oldsales = $sales_tmp['num'];
    $oldcur = $sales_tmp['cur'];
    $oldcommission = $data[4];

    $mid = 0;
    $mname = trim($data[1]);
    $sid = trim($data[5]);
    $orderid = 0;
    $tradeid = 0;

    $cur_exr = cur_exchange($oldcur, 'USD', $day);
    $sales = round($oldsales * $cur_exr, 4);
    $commission = round($oldcommission * $cur_exr, 4);

    $tradestatus = trim($data[9]);
    $referrer = trim($data[6]);

    $cancelreason = '';

    $replace_array = array(
                    '{createtime}'      => $created,
                    '{updatetime}'      => $created,
                    '{sales}'           => $sales,
                    '{commission}'      => $commission,
                    '{idinaff}'         => $mid,
                    '{programname}'     => $mname,
                    '{sid}'             => $sid,
                    '{orderid}'         => $orderid,
                    '{clicktime}'       => '',
                    '{tradeid}'         => $tradeid,
                    '{tradestatus}'     => $tradestatus,
                    '{oldcur}'          => $oldcur,
                    '{oldsales}'        => $oldsales,
                    '{oldcommission}'   => $oldcommission,
                    '{tradetype}'       => '',
                    '{referrer}'        => $referrer,
                    '{cancelreason}'    => $cancelreason,
                    );

    $rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . str_replace('-', '', $day) . '.upd';
    if (!isset($fws[$rev_file])) {
        $fws[$rev_file] = fopen($rev_file, 'w');
    }

    fwrite($fws[$rev_file], strtr(FILE_FORMAT,$replace_array) . "\n");
}

//dump decline data
$url = 'http://www.clixgalore.com/AffiliateTransactionSentReport_Export.aspx?AfID=0&ST=0&RP=6&CID=238939&S2=&AdID=0&SD='.$begin_dt.'&ED='.$end_dt.'&B=1&type=exl';
$file_temp = $file_temp . '.decline';
$fw = fopen($file_temp, 'w+');
if (!$fw)
    throw new Exception("{$file_temp} open failed");

$rtry = 0;
$pass = true;
do {
    echo "req=>{$url}\n";

    $ch = curl_init($url);
    $curl_opts = array(CURLOPT_HEADER => false,
        CURLOPT_NOBODY => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_COOKIEJAR => $file_cook,
        CURLOPT_COOKIEFILE => $file_cook,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
        CURLOPT_FILE => $fw,
    );
    curl_setopt_array($ch, $curl_opts);

    $rs = curl_exec($ch);
    curl_close($ch);
    if (!$rs) {
        $rtry++;
        $pass = false;
        if ($rtry > MAX_RTRY)
            throw new Exception("Reach at MAX set cookie times");
    }
}while (!$pass);
fclose($fw);


/*
  [1] => Array
  (
  [0] => Declined Date (GMT)
  [1] => Trans. Date (GMT)
  [2] => Merchant Site
  [3] => Currency
  [4] => Sale Value
  [5] => Commission
  [6] => Aff. Order ID
  [7] => Source
  [8] => Click Date (GMT)
  [9] => IP Address
  [10] => Status
  [11] => Banner
  [12] => Information
  )

 */
echo $file_temp . "\n";
$contents = file_get_contents($file_temp);
$chrType = checkBom($contents,true);
if($chrType != 'UTF-8'){
    $contents = iconv($chrType,'UTF-8//IGNORE',$contents);
}
$contents = checkBom($contents);
$lines = explode("\n",$contents);
foreach($lines as $k=>$v){
    $line = trim($v);
    if(empty($line))
        continue;

    if($k < 1)
        continue;

    $data = explode("\t",$line);

    $created = date('Y-m-d H:i:s', strtotime($data[1]));
    $day = date('Y-m-d', strtotime($data[1]));
    $sales_tmp = getCur($data[4]);
    $oldsales = $sales_tmp['num'];
    $oldcur = $sales_tmp['cur'];
    $oldcommission = $data[5];

    $mid = 0;
    $mname = trim($data[2]);
    $sid = trim($data[6]);
    $orderid = 0;
    $tradeid = 0;

    $cur_exr = cur_exchange($oldcur, 'USD', $day);
    $sales = round($oldsales * $cur_exr, 4);
    $commission = round($oldcommission * $cur_exr, 4);

    $tradestatus = trim($data[10]);
    $referrer = trim($data[7]);

    $cancelreason = trim($data[12]);

    $replace_array = array(
                    '{createtime}'      => $created,
                    '{updatetime}'      => $created,
                    '{sales}'           => $sales,
                    '{commission}'      => $commission,
                    '{idinaff}'         => $mid,
                    '{programname}'     => $mname,
                    '{sid}'             => $sid,
                    '{orderid}'         => $orderid,
                    '{clicktime}'       => '',
                    '{tradeid}'         => $tradeid,
                    '{tradestatus}'     => $tradestatus,
                    '{oldcur}'          => $oldcur,
                    '{oldsales}'        => $oldsales,
                    '{oldcommission}'   => $oldcommission,
                    '{tradetype}'       => '',
                    '{referrer}'        => $referrer,
                    '{cancelreason}'    => $cancelreason,
                    );

    $rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . str_replace('-', '', $day) . '.upd';
    if (!isset($fws[$rev_file])) {
        $fws[$rev_file] = fopen($rev_file, 'w');
    }
    fwrite($fws[$rev_file], strtr(FILE_FORMAT,$replace_array) . "\n");
}

foreach ($fws as $file => $f) {
    fclose($f);
}

function checkBom($str,$debug=false){
    if(empty($str))
        return '';

    $bomMap = array();
    $bomMap['UTF-8']        = array('239','187','191');
    $bomMap['UTF-16be']    = array('254','255');
    $bomMap['UTF-16le']    = array('255','254');
    $bomMap['UTF-32be']    = array('0','0','254','255');
    $bomMap['UTF-32le']    = array('255','254','0','0');
    $bomMap['UTF-1']        = array('247','100','76');
    $bomMap['UTF-EBCDIC']   = array('221','115','102','115');
    $bomMap['UTF-SCSU']     = array('14','254','255');
    $bomMap['UTF-BOCU-1']   = array('251','238','40');
    $bomMap['UTF-GB-18030'] = array('132','49','149','51');
    $bomMap['UTF-7-V8']     = array('43','47','118','56');
    $bomMap['UTF-7-V9']     = array('43','47','118','57');
    $bomMap['UTF-7-V+']     = array('43','47','118','43');
    $bomMap['UTF-7-V']      = array('43','47','118','47');
    $bomMap['UTF-7-V8-']    = array('43','47','118','56','45');

    $hasBom = 0;
    $bomType = '';

    foreach($bomMap as $k=>$v){
        $strBomStr = array();
        $bomNum = count($v);

        for ($i=0; $i < $bomNum; $i++) {
            $strBomStr[] = ord(substr($str, $i , 1));
        }

        if($v == $strBomStr){
            $hasBom = 1;
            $bomType = $k;
            break;
        }
    }

    if($hasBom){
        $bomLen = count($bomMap[$bomType]);
        $newStr = substr($str, $bomLen);
    }else{
        $newStr = $str;
    }


    return $debug?$bomType:$newStr;
}

function getCur($str){
    $currencyMap = array();
    $currencyMap['GBP'] = array('£','&#163;','&pound;','GBP','UK');
    $currencyMap['EUR'] = array('€','&#8364;','&euro;','EUR','EU');
    $currencyMap['SEK'] = array('kr','SEK');
    $currencyMap['INR'] = array('Rs','INR');
    $currencyMap['CNY'] = array('¥','CNY');
    $currencyMap['KER'] = array('WON','KER');
    $currencyMap['CHF'] = array('CHF');
    $currencyMap['PLN'] = array('PLN');
    $currencyMap['AUD'] = array('AU','AUD');
    $currencyMap['NZD'] = array('NZ','NZD');
    $currencyMap['CAD'] = array('CA','CAD');
    $currencyMap['JPY'] = array('JP','JPY');
    $currencyMap['USD'] = array('\$','&#36;','USD','Dollar','US');


    $cur = 'USD';
    $num = '0';
    $regx = '';
    foreach($currencyMap as $k=>$v){
        $regx = '/('.join('|',$v).')[^\d]*(\d+(?:\.\d+)?)/';

        if(preg_match($regx,$str,$c)){
            $cur = $k;
            $num = $c[2];
            break;
        }
    }

    if(empty($num)){
        if(preg_match('/\d+(\.\d+)?/',$str,$c))
            $num = $c[0];
    }

    $return_d = array();
    $return_d['cur'] = $cur;
    $return_d['num'] = $num;
    return $return_d;
}
?>
