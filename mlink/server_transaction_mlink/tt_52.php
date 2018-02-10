<?php
define('AFF_NAME', AFFILIATE_NAME);
define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");
//define('REPORT_TITLE', chr(hexdec('EF')) . chr(hexdec('BB')) . chr(hexdec('BF')) . 'Status,Origin,Device,ID,Campaign,"# TPs",Reference,"Product group",Description,"Order total",Commission,"Visitor IP",Country,"Registration date","Validation date","Originating click date","Click to Sales"');
  define('REPORT_TITLE', chr(hexdec('EF')) . chr(hexdec('BB')) . chr(hexdec('BF')) . 'Status,Origin,Device,ID,Campaign,"# TPs","# ATPs",Reference,"Product group",Description,"Order total","Attribution Model",Commission,"Visitor IP",Country,"Registration date","Validation date","Originating click date","Click to Sales"');

$config = array(
		array('id'=>5,'name'=>'tt_uk'),					//52
		array('id'=>4,'name'=>'tt_de'),					//65
		array('id'=>10,'name'=>'tt_fr'),				//427
		array('id'=>18,'name'=>'tt_at'),				//425
		array('id'=>20,'name'=>'tt_ch'),				//426
		array('id'=>8,'name'=>'tt_it'),					//2026
		array('id'=>3,'name'=>'tt_nl'),					//2027
		array('id'=>19,'name'=>'tt_ru'),				//2028
		array('id'=>11,'name'=>'tt_be'),				//2029
    //array('id'=>19,'name'=>'tt_in'),				//2053
        array('id'=>34,'name'=>'tt_uae'),				//2054
  );


if (defined('START_TIME') && defined('END_TIME')) {
    $end_dt = date('Y-m-d', strtotime(END_TIME));
    $begin_dt = date('Y-m-d', strtotime(START_TIME));
} else {
    $end_dt = date('Y-m-d');
    $begin_dt = date('Y-m-d', strtotime('-120 days', strtotime($end_dt)));
}

$file_temp = PATH_TMP . '/' . AFF_NAME . '.tmp';
if(file_exists($file_temp))
    unlink($file_temp);
$file_cook = PATH_COOKIE . '/' . AFF_NAME . '.cook';
if(file_exists($file_cook))
    unlink($file_cook);

echo "Date setting: ST:{$begin_dt} ET:{$end_dt} \n";

$url = 'https://affiliate.tradetracker.com/user/login';
$content = file_get_contents($url);
preg_match('/name="__FORM" value="([^"]*)"/is',$content,$m);
$__FORM = isset($m[1])?$m[1]:'';


$url = 'https://affiliate.tradetracker.com/user/login';

$posts = array();
$posts[] = 'username='.urlencode(AFFILIATE_USER);
$posts[] = 'password='.urlencode(AFFILIATE_PASS);
$posts[] = 'rememberMe=0';
$posts[] = 'redirectURL=';
$posts[] = 'submitLogin=Log+in';
$posts[] = '__FORM='.urlencode($__FORM);

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


foreach($config as $k=>$v){
    $affname = $v['name'];
    $temp_file = PATH_TMP . '/' . $affname . '.tmp';
    $fw = fopen($temp_file,'w');
    $fws = array();
    $CompanyID = $v['id'];
    $get_data = array();
    $get_data[] = 'desc=1&outputType=3&c=&r=0';
    $get_data[] = 'p[t]=1';
    $get_data[] = 'p[fd]='.date('j',strtotime($begin_dt));
    $get_data[] = 'p[fm]='.date('n',strtotime($begin_dt));
    $get_data[] = 'p[fy]='.date('Y',strtotime($begin_dt));
    $get_data[] = 'p[td]='.date('j',strtotime($end_dt));
    $get_data[] = 'p[tm]='.date('n',strtotime($end_dt));
    $get_data[] =  'p[ty]='.date('Y',strtotime($end_dt));
    $get_data[] = 'submit_period_p=Apply&offset=0&generate=2&setCompanyID='.$CompanyID;

    $url = 'https://affiliate.tradetracker.com/affiliateTransaction/sales?'.join('&',$get_data);
    print_r($url."\n");
    $ch = curl_init($url);
    $curl_opts = array(CURLOPT_HEADER => false,
        CURLOPT_NOBODY => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_COOKIEJAR => $file_cook,
        CURLOPT_COOKIEFILE => $file_cook,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
        CURLOPT_FILE => $fw,
    );
    curl_setopt_array($ch, $curl_opts);

    $pass = curl_exec($ch);
    curl_close($ch);

    fclose($fw);

    $contents_before = file_get_contents($temp_file);
    $contents_after = iconv('UCS-2','UTF-8',$contents_before);
    file_put_contents($temp_file, $contents_after);

    $fp = fopen($temp_file, 'r');
    if (!$fp)
        die("{$temp_file} open failed");

    $k = 0;
    while (!feof($fp)) {
        if (++$k == 1) {
            $l = trim(fgets($fp));
            if ($l != REPORT_TITLE) {
                echo $affname.PHP_EOL;
                var_dump(REPORT_TITLE, $l);
                mail('merlinxu@brandreward.com', 'FROM BRANDREWARD TradeTracker Crawler Failed', 'Title not correct');
                print_r("The title of dump file is not correct\n");
            }
            continue;
        }

        $lr = fgetcsv($fp, 0, ',', '"');
        
        if (!isset($lr[0]) || $lr[0] == '')
            
            continue;
        /*
0  = Status
1  = Origin
2  = Device
3  = ID
4  = Campaign
5  = "# TPs"
6  = "# ATPs"
7  = Reference
8  = "Product group"
9  = Description
10 = "Order total"
11 = "Attribution Model"
12 = Commission
13 = "Visitor IP"
14 = Country
15 = "Registration date"
16 = "Validation date"
17 = "Originating click date"
18 = "Click to Sales"     
         */
        
        $created = preg_replace('/([0-9]{2})\/([0-9]{2})\/([0-9]{4}), ([\d]+:[\d]+:[\d]+).*/', '\\3-\\2-\\1 \\4', $lr[15]);
        $tradestatus = trim($lr[0]);
        if(strtolower($tradestatus) == 'rejected'){
            $oldsales = 0;
            $oldcommission = 0;
        }else{
            $oldsales = preg_replace('/[^0-9\.-]+/', '', $lr[10]);
            $oldcommission = preg_replace('/[^0-9\.-]+/', '', $lr[12]);
        }
        
        //ru 特殊处理
        if($affname == 'tt_ru'){
            $oldsales = preg_replace('/^\./','',$oldsales);
            $oldcommission = preg_replace('/^\./','',$oldcommission);
        }
        
        $oldcur = 'USD';
        if(strpos($lr[10], '€') !== false){
          $oldcur = 'EUR';
        }elseif(strpos($lr[10], '$') !== false){
          $oldcur = 'USD';
        }elseif(strpos($lr[10], '£') !== false){
          $oldcur = 'GBP';
        }elseif(strpos($lr[10], 'р') !== false){
          $oldcur = 'RUB';
        }
        
        $_day = date('Y-m-d',strtotime($created));

        $cur_exr = cur_exchange($oldcur, 'USD', $_day);
        $sales = round($oldsales * $cur_exr, 4);
        $commission = round($oldcommission * $cur_exr, 4);

        $programname = trim($lr[4]);
        $sid = trim($lr[7]);
        $orderid = '';
        $tradeid = trim($lr[3]);
        $referrer = '';
        $cancelreason = '';
        
        $replace_array = array(
                    '{createtime}'      => $created,
                    '{updatetime}'      => $created,
                    '{sales}'           => $sales,
                    '{commission}'      => $commission,
                    '{idinaff}'         => '',
                    '{programname}'     => $programname,
                    '{sid}'             => $sid,
                    '{orderid}'         => '',
                    '{clicktime}'       => '',
                    '{tradeid}'         => $tradeid,
                    '{tradestatus}'     => $tradestatus,
                    '{oldcur}'          => $oldcur,
                    '{oldsales}'        => $oldsales,
                    '{oldcommission}'   => $oldcommission,
                    '{tradetype}'       => '',
                    '{referrer}'        => '',
                    '{cancelreason}'    => $cancelreason,
                    );
        $rev_file = PATH_DATA . '/' . $affname . '/revenue_' . str_replace('-', '', $_day) . '.upd';
        if (!isset($fws[$rev_file])) {
            $fws[$rev_file] = fopen($rev_file, 'w');
        }

        fwrite($fws[$rev_file], strtr(FILE_FORMAT,$replace_array) . "\n");
    }
    fclose($fp);

    foreach ($fws as $file => $f) {
        fclose($f);
    }
}



?>
