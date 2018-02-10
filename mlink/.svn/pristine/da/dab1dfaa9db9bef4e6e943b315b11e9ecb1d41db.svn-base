<?php
define('AFF_NAME',AFFILIATE_NAME);
define('USER_NAME', "IRYrCKQmWhbn245060XeRFfN3HQ8QboRi1");
define('USER_PASS', "vN3jiEFiYDrJ6rV7GSFcDk9dcwfgGyKE");

define('PAGE_SIZE', 1000);

define("REST_SERVER", "https://{AccountSid}:{AuthToken}@api.impactradius.com");
define("REST_API", "/2010-09-01/Mediapartners/{AccountSid}/Actions.json?ActionDateStart={StartDate}&ActionDateEnd={EndDate}&PageSize={PageSize}&Page={PageNo}");

define('REV_DATA', PATH_CODE . '/log/' . AFF_NAME . '.dat');
define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");

if (defined('START_TIME') && defined('END_TIME')) {
    $end_dt = date('Y-m-d', strtotime(END_TIME));
    $start_dt = date('Y-m-d', strtotime(START_TIME));
} else {
    $end_dt = date('Y-m-d');
    $start_dt = date('Y-m-d', strtotime('-100 days', strtotime($end_dt)));
}

$start_date = $start_dt;
$end_date = $end_dt;

echo "Date setting: ST:{$start_dt} ET:{$end_dt} \n";
$start_dt .= 'T00:00:00'.date('P');
$end_dt .= 'T23:59:59'.date('P');


$host = str_replace(array('{AuthToken}', '{AccountSid}'), array(USER_PASS, USER_NAME), REST_SERVER);
$url = $host . str_replace(array('{AccountSid}', '{StartDate}', '{EndDate}', '{PageSize}', '{PageNo}'), array(USER_NAME, $start_dt, $end_dt, PAGE_SIZE, 1), REST_API);

$fws = array();

do {
    echo "req => {$url} \n";
    $ch = curl_init($url);
    $curl_opts = array(CURLOPT_HEADER => false,
        CURLOPT_NOBODY => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
    );
    curl_setopt_array($ch, $curl_opts);
    $json = curl_exec($ch);
    curl_close($ch);
    $json = json_decode($json, true);

    if (!isset($json['@total']) || $json['@total'] == 0) {
        throw new Exception("No Data Found");
    }
	$k = 0;
    foreach ($json['Actions'] as $v) {
        $day = date('Y-m-d', strtotime(trim($v['EventDate'])));

        $currency = cur_exchange(trim($v['Currency']), 'USD', $day);
        $sales = str_replace(',', '', trim($v['Amount'])) * $currency;
        $rev   = str_replace(',', '', trim($v['IntendedPayout'])) * $currency;

        $cancelreason = '';

        $replace_array = array(
                    '{createtime}'      => date('Y-m-d H:i:s', strtotime(trim($v['EventDate']))),
                    '{updatetime}'      => date('Y-m-d H:i:s', strtotime(trim($v['CreationDate']))),
                    '{sales}'           => $sales,
                    '{commission}'      => $rev,
                    '{idinaff}'         => trim($v['CampaignId']),
                    '{programname}'     => trim($v['CampaignName']),
                    '{sid}'             => trim($v['SubId1']),
                    '{orderid}'         => '',
                    '{clicktime}'       => date('Y-m-d H:i:s', strtotime(trim($v['EventDate']))),
                    '{tradeid}'         => trim($v['Id']),
                    '{tradestatus}'     => trim($v['State']),
                    '{oldcur}'          => trim($v['Currency']),
                    '{oldsales}'        => str_replace(',', '', trim($v['Amount'])),
                    '{oldcommission}'   => str_replace(',', '', trim($v['IntendedPayout'])),
                    '{tradetype}'       => trim($v['ActionTrackerName']),
                    '{referrer}'        => trim($v['ReferringDomain']),
                    '{cancelreason}'    => $cancelreason,
                    );

        $rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . date('Ymd', strtotime($day)) . '.upd';
        if (!isset($fws[$rev_file])) {
            $fws[$rev_file] = fopen($rev_file, 'w');
            $comms[$rev_file] = 0;
        }

        fwrite($fws[$rev_file], strtr(FILE_FORMAT,$replace_array) . "\n");
    }

    $url = $host . $json['@nextpageuri'];
} while ($json['@nextpageuri'] != '');

foreach ($fws as $file => $f) {
    fclose($f);
}
?>
