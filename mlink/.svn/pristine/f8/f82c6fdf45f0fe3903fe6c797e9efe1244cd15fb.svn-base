<?php
define('AFF_NAME', AFFILIATE_NAME);
define('AFF_USER', AFFILIATE_USER);
define('AFF_PASS', AFFILIATE_PASS);
               
define('URL_API','https://partner.groupon.com/analytics-proxy/order.csv?date=[{BEGIN_DATE}&date={END_DATE}]&group=date.day%7Corder%7Cdeal%7CTopCategory%7Cplatform&order.currency={CURRENCY}&order.country={COUNTRY}&sort=-date.day&timezone=America%2FChicago');
                  https://partner.groupon.com/analytics-proxy/order.csv?date=%5B2016-12-18&date=2017-01-16%5D&group=date.day%7Corder%7Cdeal%7CTopCategory%7Cplatform&order.currency=USD&order.country=US&sort=-date.day&timezone=America%2FChicago
define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");
define('LOGIN_API','https://partner.groupon.com/j_spring_security_check?origin=US');
                    
$file_cook = PATH_CODE.'/cookie/'.AFF_NAME.'.cook';
$user_agent = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2';
$file_temp = PATH_TMP . '/' . AFF_NAME . '.csv';


//国家
$countryJson = '["USD_US"]';

$countryArr = json_decode($countryJson,true);

//日期
if (defined('START_TIME') && defined('END_TIME')) {
    $end_dt = END_TIME;
    $begin_dt = START_TIME;
} else {
    $end_dt = date('Y-m-d');
    $begin_dt = date('Y-m-d', strtotime("-90 days"));
}
echo "Date setting: ST:{$begin_dt} ET:{$end_dt} \n";

//登录第一步：csrf
$url = 'https://partner.groupon.com/login';
echo "$url \n";
$ch = curl_init($url);
$curl_opts = array(CURLOPT_HEADER => false,
    CURLOPT_NOBODY => false,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_USERAGENT => $user_agent,
    CURLOPT_COOKIEJAR => $file_cook,
    CURLOPT_COOKIEFILE => $file_cook,
);
curl_setopt_array($ch, $curl_opts);
$content = curl_exec($ch);
curl_close($ch);

preg_match('/<input type="hidden" name="_csrf" value="([^"]*)" \/>/is',$content,$m);
$_csrf = isset($m[1])?$m[1]:'';

 

//账号密码登录
$ch = curl_init(LOGIN_API);
$curl_opts = array(CURLOPT_HEADER => false,
    CURLOPT_NOBODY => false,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_USERAGENT => $user_agent,
    CURLOPT_COOKIEJAR => $file_cook,
    CURLOPT_COOKIEFILE => $file_cook,
    CURLOPT_POST=> true,
    CURLOPT_POSTFIELDS => 'j_username='.urlencode(AFF_USER).'&j_password='.urlencode(AFF_PASS).'&_csrf='.$_csrf.'&hashForward=',
);

curl_setopt_array($ch, $curl_opts);
$pass = curl_exec($ch);
curl_close($ch);


//交易数据

$dateArr = get_date_range_arr($begin_dt,$end_dt,'30 day');
krsort($dateArr);
foreach ($countryArr as $value){
    
    $countryOne = explode('_', $value);
     
    
    
    foreach($dateArr as $k=>$v){
    
        $url = str_replace(array('{BEGIN_DATE}', '{END_DATE}', '{CURRENCY}', '{COUNTRY}'), array($v['start_dt'], $v['end_dt'], $countryOne[0], $countryOne[1]), URL_API);
        echo $url.PHP_EOL; 
    
        if (file_exists($file_temp))
            unlink($file_temp);
    
        $fw = fopen($file_temp, 'w');
        if (!$fw){
            throw new Exception("File open failed {$file_temp}");
        }
    
        $ch = curl_init($url);
        $curl_opts = array(CURLOPT_HEADER => false,
            CURLOPT_NOBODY => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => $user_agent,
            CURLOPT_COOKIEJAR => $file_cook,
            CURLOPT_COOKIEFILE => $file_cook,
            CURLOPT_FILE => $fw,
        );
        curl_setopt_array($ch, $curl_opts);
        $data = curl_exec($ch);
        curl_close($ch);
        fclose($fw);
    
    
        //读取csv
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
    
            $createTime = substr($lr[0],0,10);
            $day = date('Y-m-d', strtotime(trim($createTime)));
            //echo $createTime.'=='.$day.PHP_EOL;
             
            $cur_exr    = cur_exchange($lr[5], 'USD', $day);
            $sales      = round($lr[14] * $cur_exr, 4);
            $commission = round($lr[17] * $cur_exr, 4);
            $cancelreason = '';
    
            $replace_array[$day][] = array(
                '{createtime}'      => date('Y-m-d H:i:s', strtotime(trim($createTime))),
                '{updatetime}'      => date('Y-m-d H:i:s', strtotime(trim($createTime))),
                '{sales}'           => $sales,
                '{commission}'      => $commission,
                '{idinaff}'         => '',
                '{programname}'     => '',
                '{sid}'             => $lr[2],
                '{orderid}'         => $lr[1],
                '{clicktime}'       => '',
                '{tradeid}'         => $lr[1],
                '{tradestatus}'     => $lr[6],
                '{oldcur}'          => $lr[5],
                '{oldsales}'        => $lr[14],
                '{oldcommission}'   => $lr[17],
                '{tradetype}'       => '',
                '{referrer}'        => '',
                '{cancelreason}'    => $cancelreason,
            );
        }
    
        fclose($fp);
    }
    
}


$fws = array();
foreach ($replace_array as $dateKey=>$dataValue){
    $rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . date('Ymd', strtotime($dateKey)) . '.upd';
    
    if (!isset($fws[$rev_file])) {
        $fws[$rev_file] = fopen($rev_file, 'w');
        $comms[$rev_file] = 0;
    }
    
    foreach ($dataValue as $realdata){
        fwrite($fws[$rev_file], strtr(FILE_FORMAT,$realdata) . "\n");
    }
}



if($fws){
    foreach ($fws as $file => $f) {
        fclose($f);
    }
}




function get_date_range_arr($startDate,$endDate,$range,$format='Y-m-d'){
    $startDate = date('Y-m-d',strtotime($startDate));
    $endDate = date('Y-m-d',strtotime($endDate));
    $d = new DateTime($startDate);

    $return_d = array();

    while($d->format('Y-m-d') <= $endDate){

        $start_dt = $d->format($format);
        $d->modify('+'.$range);
        if($d->format('Y-m-d') > $endDate){
            $end_dt = date($format,strtotime($endDate));
        }else{
            $end_dt = $d->format($format);
        }
        $d->modify('+1 day');

        $return_d[] = array('start_dt'=>$start_dt,'end_dt'=>$end_dt);
    }
    return $return_d;
}
 

?>
