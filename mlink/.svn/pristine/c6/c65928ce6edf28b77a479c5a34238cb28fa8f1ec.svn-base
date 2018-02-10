<?php
try {

    set_time_limit(0);
    define('AFF_NAME', AFFILIATE_NAME);
    define('REV_DATA', PATH_CODE . '/log/' . AFF_NAME . '.dat');
    define('USERNAME', urlencode(AFFILIATE_USER));
    define('PASSWORD', urlencode(AFFILIATE_PASS));
    define('API_PASSWORD', urlencode('XewPHe4eJXd8'));

    if (defined('START_TIME') && defined('END_TIME')) {
        $end_dt = date('Y-m-d', strtotime(END_TIME));
        $begin_dt = date('Y-m-d', strtotime(START_TIME));
    } else {
        $end_dt = date('Y-m-d');
        $begin_dt = date('Y-m-d', strtotime('-90 days'));
    }
    echo "Date setting: ST:{$begin_dt} ET:{$end_dt} \n";
    $url = 'https://stat.netaffiliation.com/requete.php?authl='.USERNAME.'&authv='.API_PASSWORD.'&etat=vra&debut='.$begin_dt.'&fin='.$end_dt;
    //echo $url;exit;
    $data = file_get_contents($url);
    $dataarr = explode("\n",$data);
    unset($dataarr[0]);
    $fws = array();
    foreach($dataarr as $k){
        if(empty($k)){
            continue;
        }
        $val = explode(';',$k);
        $Created = toTimeZone(preg_replace('/UTC/','',$val[2]));
        $sid = $val[7];
        $oldcom = $val[5];
        $cur = $val[6];
        $idinaff = $val[0];
        $status  = $val[4];
        $time = explode(' ',$Created);
        $comval = cur_exchange($cur,'USD',$time[0]);
        $commission = ($comval > 0) ? round($oldcom * $comval, 4) : 0;
        if($status == 'v'){
            $status = 'validated';
        }/*elseif($status == 'r'){
            $status = 'refused';
        }*/elseif($status == 'a'){
            $status = 'pending';
        }else{
            //$status = 'unknown';
            continue;
        }
        $tmp_data_arr = array(
            'Updated' => $Created,
            'Created' => $Created,
            'Sales' => '',
            'Commission' => $commission,
            'IdInAff' => $idinaff,
            'ProgramName' => '',
            'SID' => $sid,
            'OrderId' => '',
            'ClickTime' => '',
            'TradeId' => '',
            'TradeStatus' => $status,
            'OldCur' => $cur,
            'OldSales' => '',
            'OldCommission' => $oldcom,
            'TradeType' => ''
        );
        $rev_file = PATH_DATA . '/' . AFFILIATE_NAME . '/revenue_' . str_replace('-', '', $time[0]) . '.upd';
        if (!isset($fws[$rev_file])) {
            $fws[$rev_file] = fopen($rev_file, 'w');
            $comms[$rev_file] = 0;
        }
        fwrite($fws[$rev_file], implode("\t", $tmp_data_arr) . "\n");
    }
    foreach ($fws as $file => $f) {
        fclose($f);
    }

}
catch(Exception $e){
    echo $e->getMessage();
    exit(1);
}
/*
   * 时区转换
   */
function toTimeZone($src, $from_tz = 'Europe/London', $to_tz = 'America/New_York', $fm = 'Y-m-d H:i:s'){
    $datetime = new DateTime($src, new DateTimeZone($from_tz));
    $datetime->setTimezone(new DateTimeZone($to_tz));
    return $datetime->format($fm);
}
?>
