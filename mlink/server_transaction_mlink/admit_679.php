<?php

define('MAX_RTRY', 5);

try {
    
    echo 'Start Time:'.date('Y-m-d H:i:s').PHP_EOL;

    define('AFF_NAME', AFFILIATE_NAME);
    define('Client_id', "01a4abea7cb2fed8e518ee93d4892a"); 
    define('Client_SECRET', "9eb5fe166ecec7f2a9932e1f18e787"); 
    define('REPORTWS_URI','https://api.admitad.com/statistics/actions/?date_start={BEGIN_DATE}&date_end={END_DATE}&limit=1000');
    define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");
    $file_temp = PATH_TMP . '/' . AFF_NAME . '.tmp';
    
    
    if (defined('START_TIME') && defined('END_TIME')) {
        $end_dt = date('d.m.Y', strtotime(END_TIME));
        $begin_dt = date('d.m.Y', strtotime(START_TIME));
    } else {
        $end_dt = date('d.m.Y', strtotime('+1 day'));
        $begin_dt = date('d.m.Y', strtotime('-90 days', strtotime($end_dt)));
    }

    echo $begin_dt .'===>>'. $end_dt .PHP_EOL;
    
    //admitad api  statistics
    $data_b64_encoded = base64_encode(Client_id . ':' . Client_SECRET);
    $query = array(
        'client_id' => Client_id,
        'scope' => 'statistics',
        'grant_type' => 'client_credentials'
    );
    $ch = curl_init('https://api.admitad.com/token/');
    $curl_opts = array(
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array('Authorization: Basic ' . $data_b64_encoded),
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($query)
    );
    curl_setopt_array($ch, $curl_opts);
    $reponseToken = curl_exec($ch);
    curl_close($ch);
    $tokenArr = json_decode($reponseToken,true);
     
    //联盟交易数据 一次一天 因为返回的数据没有日期。
    //$crawlerDate = $begin_dt;
    $crawlerDate = $end_dt;
    while(strtotime($crawlerDate)>=strtotime($begin_dt)){
         
        $fw = fopen($file_temp, 'w');
        if (!$fw)
            throw new Exception("File open failed {$file_temp}");
        
         
        $url = str_replace(array('{BEGIN_DATE}', '{END_DATE}'), array($crawlerDate, $crawlerDate), REPORTWS_URI);
        echo $url.PHP_EOL;
        $ch = curl_init($url);
        $curl_opts = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
            CURLOPT_FILE => $fw,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => array('Authorization: Bearer ' . $tokenArr['access_token']),
        );
        curl_setopt_array($ch, $curl_opts);
        
        $reponseData = curl_exec($ch);
        curl_close($ch);
        fclose($fw);
        
         
        $contents = file_get_contents($file_temp);
        if($contents) $tranData = json_decode($contents,true);
        
        $tmp_data_arr = array();
        
        $date = date('Y-m-d', strtotime($crawlerDate));
        //$rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . str_replace('-', '', substr($date, 0, 10)) . '.upd';
        //echo $rev_file.PHP_EOL;
        //$file_res = fopen($rev_file, 'w');
        
        foreach ($tranData['results'] as $value){
            echo $value['status'] . "\t";continue;
            
            $OldCommission = $value['payment'];
            
            /*if(!isset($curr['currency'][$date])){
                $curr['currency'][$date] = cur_exchange($value['currency'], 'USD', $date);
            }
            $cur_exr = $curr['currency'][$date];*/
            
            $cur_exr = cur_exchange($value['currency'], 'USD', $date);
            
            $commission = round($OldCommission * $cur_exr, 4);
            $sales = round($value['cart'] * $cur_exr, 4);
            $cancelreason = '';
            $referrer = trim($value['click_user_referer']);
            
            $replace_array = array(
                '{createtime}'      => $value['action_date'],
                '{updatetime}'      => $value['action_date'],
                '{sales}'           => $sales,
                '{commission}'      => $commission,
                '{idinaff}'         => $value['advcampaign_id'],
                '{programname}'     => $value['advcampaign_name'],
                '{sid}'             => $value['subid'],
                '{orderid}'         => $value['order_id'],
                '{clicktime}'       => $value['click_date'],
                '{tradeid}'         => $value['tariff_id'],
                '{tradestatus}'     => $value['status'],
                '{oldcur}'          => $value['currency'],
                '{oldsales}'        => $value['cart'],
                '{oldcommission}'   => $OldCommission,
                '{tradetype}'       => '',
                '{referrer}'        => $referrer,
                '{cancelreason}'    => $cancelreason,
            );
             
            $rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . date('Ymd', strtotime($value['action_date'])) . '.upd';
            if (!isset($fws[$rev_file])) {
                $fws[$rev_file] = fopen($rev_file, 'w');
                $comms[$rev_file] = 0;
            }
            fwrite($fws[$rev_file], strtr(FILE_FORMAT,$replace_array) . "\n");
            //fwrite($fws[$rev_file], implode("\t", $tmp_data_arr) . "\n");
            
        }
         
        //加一天
        $crawlerDate =  date('d.m.Y', strtotime('-1 days', strtotime($crawlerDate)));
    }
    foreach ($fws as $file => $f) {
        fclose($f);
    }
    echo 'End Time:'.date('Y-m-d H:i:s').PHP_EOL;
 
}
catch (Exception $e) {
    var_dump($e);
}
?>
