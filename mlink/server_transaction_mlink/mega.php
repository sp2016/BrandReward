<?php

require_once 'comm.php';
define('AFF_NAME', 'mega'); # affid = 9999  programid = 14018
define('SITE_KEY','34173cb38f07f89ddbebc2ac9128303f');//sayweee:70efdf2ec9b086079795c442636b55fb | br:34173cb38f07f89ddbebc2ac9128303f
define('API_URL','http://api.mgsvc.com/?act=transaction.get_data&createddate=[date]&site=[key]');
define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}");

$start_time = isset($argv[1])?$argv[1]:'';
$end_time = isset($argv[2])?$argv[2]:'';
if ($start_time && $end_time) {
    $start_dt = date('Y-m-d', strtotime($start_time));
    $end_dt = date('Y-m-d', strtotime($end_time));
} else {
    $end_dt = date('Y-m-d');
    $start_dt = date('Y-m-d', strtotime('-90 days', strtotime($end_dt)));
}
$file_temp = PATH_TMP . '/' . AFF_NAME . '.tmp';
if (file_exists($file_temp))
    unlink($file_temp);

echo "date@ from:{$start_dt} ~ to:{$end_dt}\n";


$d = new DateTime($start_dt);

do{
    $fw = fopen($file_temp, 'a');
    $curDate = $d->format('Y-m-d');
    $d->modify('+1 day');
    $nextDate = $d->format('Y-m-d');
    $url = str_replace(array('[date]','[key]'),array($curDate,SITE_KEY),API_URL);
    
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
}while(strtotime($nextDate) < strtotime($end_dt));

echo "tmp => {$file_temp}\n";

$fp = fopen($file_temp, 'r');
if (!$fp){
    throw new Exception("File open failed {$file_temp} 2");
}

$fws = array();
while (!feof($fp)) {
    $lr = explode("\t",trim(fgets($fp)));

    if(empty($lr) || empty($lr[0]))
        continue;

    /*
        0=>Af
        1=>Created
        2=>Updated
        3=>Sales
        4=>Commission
        5=>IdInAff
        6=>ProgramName
        7=>OrderId
        8=>TradeKey
        9=>SID
        10=>PublishTracking
        11=>Site
        12=>Alias
        13=>ProgramId
    */

    $replace_array = array(
                    '{createtime}'      => trim($lr[1]),
                    '{updatetime}'      => trim($lr[2]),
                    '{sales}'           => floatval(trim($lr[3])),
                    '{commission}'      => floatval(trim($lr[4])),
                    '{idinaff}'         => trim($lr[13]),
                    '{programname}'     => trim($lr[6]),
                    '{sid}'             => trim($lr[10]),
                    '{orderid}'         => '',
                    '{clicktime}'       => '',
                    '{tradeid}'         => trim($lr[8]),
                    '{tradestatus}'     => '',
                    '{oldcur}'          => 'USD',
                    '{oldsales}'        => floatval(trim($lr[3])),
                    '{oldcommission}'   => floatval(trim($lr[4])),
                    '{tradetype}'       => '',
                    '{referrer}'        => '',
                    );

    $day = date('Y-m-d', strtotime($lr[1]));
    $rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . date('Ymd', strtotime($day)) . '.upd';
    if (!isset($fws[$rev_file])) {
        $fws[$rev_file] = fopen($rev_file, 'w');
        $comms[$rev_file] = 0;
    }

    fwrite($fws[$rev_file], strtr(FILE_FORMAT,$replace_array) . "\n");
}

foreach ($fws as $file => $f) {
    fclose($f);
}
?>
