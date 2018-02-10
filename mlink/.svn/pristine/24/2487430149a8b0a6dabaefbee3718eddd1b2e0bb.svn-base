<?php
define('AFF_NAME', AFFILIATE_NAME);
define('API_UNAME', AFFILIATE_USER);
define('API_PASS', AFFILIATE_PASS);
define('API_URL', "http://www.adcell.de/csv_affilistats.php?sarts=x&status=a&subid=&eventid=a&pid=a");

define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");
define('REV_DATA', PATH_CODE . '/log/' . AFF_NAME . '.dat');

if (defined('START_TIME') && defined('END_TIME')) {
    $start_dt = date('Y-m-d', strtotime(START_TIME));
    $end_dt = date('Y-m-d', strtotime(END_TIME));
} else {
    $end_dt = date('Y-m-d');
    $start_dt = date('Y-m-d', strtotime('-120 days', strtotime($end_dt)));
}
echo "Date setting: ST:{$start_dt} ET:{$end_dt} \n";
$comm_all = 0;
$file_temp = PATH_TMP . '/' . AFF_NAME . '.tmp';

if (file_exists($file_temp))
    unlink($file_temp);

$fw = fopen($file_temp, 'w');

$url = API_URL."&uname=".API_UNAME."&pass=".API_PASS."&timestart=".strtotime($start_dt)."&timeend=".strtotime($end_dt." 23:59:59");
echo "req => {$url} \n";
$ch = curl_init($url);
$curl_opts = array(CURLOPT_HEADER => false,
    CURLOPT_NOBODY => false,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
    CURLOPT_FOLLOWLOCATION => true,
    //CURLOPT_USERPWD => SECURITY_TOKEN,
    CURLOPT_FILE => $fw
);
curl_setopt_array($ch, $curl_opts);
$rs = curl_exec($ch);
curl_close($ch);
fclose($fw);




$fp = fopen ($file_temp, 'r');
$fws = $comm = $lr = array();
$k = 0;
while (!feof($fp)) {
    
    $lr = explode(";",trim(fgets($fp)));
    if (++$k == 1) {
        continue;
    }
    
    $TransactionId = trim($lr[0],"\"");
    $TransactionTime = date("Y-m-d H:i:s",strtotime(trim($lr[1],"\"")));
    $ProgramId = trim($lr[3],"\"");
    //$ProgramName = isset($names[$ProgramId])?$names[$ProgramId]:'';
    $ProgramName = '';
    $Sid = trim($lr[6],"\"");
    $Status = trim($lr[7],"\"");
    $Sales_Old = $Status=='bestätigt'?0:rtrim(str_replace(',', '.', trim($lr[8],"\"")),"€");
    $Commission_Old = $Status=='bestätigt'?0:rtrim(str_replace(',', '.', trim($lr[9],"\"")),"€");
    $Curency = 'EUR';

    $tdate = date("Y-m-d",strtotime($TransactionTime));
    $cur_exr = cur_exchange($Curency, 'USD',$tdate);

    $Sales = $Sales_Old>0?round($Sales_Old * $cur_exr, 4):0;
    $Commission = $Commission_Old>0?round($Commission_Old * $cur_exr, 4):0;
    
    $referrer = trim($lr[10]);

    $rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . date('Ymd', strtotime($TransactionTime)) . '.upd';

    if (!isset($fws[$rev_file])) {
        $fws[$rev_file] = fopen($rev_file, 'w');
        $comms[$rev_file] = 0;
    }
    $cancelreason = trim($lr[11]);
    $cancelreason = trim($lr[11],'"');

    $replace_array = array(
        '{createtime}'      => $TransactionTime,
        '{updatetime}'      => $TransactionTime,
        '{sales}'           => $Sales,
        '{commission}'      => $Commission,
        '{idinaff}'         => $ProgramId,
        '{programname}'     => $ProgramName,
        '{sid}'             => $Sid,
        '{orderid}'         => $TransactionId,
        '{clicktime}'       => $TransactionTime,
        '{tradeid}'         => $TransactionId,
        '{tradestatus}'     => $Status,
        '{oldcur}'          => $Curency,
        '{oldsales}'        => $Sales_Old,
        '{oldcommission}'   => $Commission_Old,
        '{tradetype}'       => '',
        '{referrer}'        => $referrer,
        '{cancelreason}'    => $cancelreason,
    );
    
    fwrite($fws[$rev_file], strtr(FILE_FORMAT,$replace_array) . "\n");
    
    $comms[$rev_file] += $Commission;
    $comm_all+=$Commission;
}
fclose($fp);

 
foreach ($fws as $file => $f) {
    fclose($f);
}
    

?>
