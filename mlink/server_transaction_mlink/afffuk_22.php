<?php

define('URL', 'http://ws-external.afnt.co.uk/apiv1/AFFILIATES/affiliatefuture.asmx/GetTransactionListbyDate?username={USER}&password={PASS}&startDate={FD}&endDate={TD}');
define('AFF', AFFILIATE_NAME);
define('REV_DATA', PATH_CODE . '/log/' . AFF . '.dat');
define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");

$fd = defined('START_TIME') ? START_TIME : date("Y-m-d", strtotime("-120 days"));
$td = defined('END_TIME') ? END_TIME : date("Y-m-d");

$begin_dt = $fd;
$end_dt = $td;

if ($fd > $td){
    echo 'start date can not bigger than end date';exit();
}

echo "{$fd}~{$td}\n";
$filename = PATH_TMP . '/' . AFF . ".xml";
$dump = array();
$startDate = $endDate = strtotime($td);
$fws = array();

while ($startDate > strtotime($fd)) {
    if ($startDate > strtotime("+30 days", strtotime($fd)))
        $startDate = date("d-m-Y", strtotime("-30 days", $startDate));
    else
        $startDate = date("d-m-Y", strtotime($fd));

	$min_start_date = date('Y-m-d', strtotime($startDate));
    $endDate = date("d-m-Y", $endDate);

	if (file_exists($filename))
		unlink($filename);

	$fw = fopen($filename, 'w');

    $url = str_replace(array('{USER}', '{PASS}', '{FD}', '{TD}'), array(AFFILIATE_USER, urlencode(AFFILIATE_PASS), $startDate, $endDate), URL);
    $rtry = 0;
	do {
		echo "req => {$url} \n";
		$ch = curl_init($url);
		$curl_opts = array(CURLOPT_HEADER => false,
							CURLOPT_NOBODY => false,
							CURLOPT_RETURNTRANSFER => true,
							CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
							CURLOPT_FILE => $fw,
							CURLOPT_SSL_VERIFYPEER => false,
							);
		curl_setopt_array($ch, $curl_opts);
		if ($rtry > 0) {
			curl_setopt($ch, CURLOPT_TIMEOUT, 180+30*$rtry);
		}

		$pass = curl_exec($ch);
		curl_close($ch);
		
		if (!$pass) {
			$rtry++;
			if ($rtry > MAX_TRY)
				throw new Exception ("reach at max times");
		}
	} while (!$pass);
	fclose($fw);


    $startDate = strtotime($startDate);
    $endDate = strtotime("-1 day", $startDate);


    $xml = simplexml_load_file($filename);
    $xml = json_decode(json_encode($xml),true);

    foreach ($xml['TransactionList'] as $val) {			
        $reg_date = date("Y-m-d", strtotime($val['TransactionDate']));

		if ($reg_date < $min_start_date)
			continue;

        $date = date("Y-m-d H:i:s", strtotime($val['TransactionDate']));
        $oldsales = $val['SaleValue'];
        $oldcommission = $val['SaleCommission'];
        $oldcur = 'GBP';
        $idinaff = $val['MerchantID'];
        $programname = $val['MerchantName'];
        $sid = $val['TrackingReference'];
        $orderid = '';
        $clicktime = '';
        $tradeid = $val['TransactionID'];
        $tradestatus = '';
        $tradetype = '';
        $referrer = '';

        $_day = date("Y-m-d", strtotime($val['TransactionDate']));

        $cur_exr = cur_exchange($oldcur, 'USD', $_day);

        $sales = round($oldsales * $cur_exr, 4);
        $commission = round($oldcommission * $cur_exr, 4);
        
        $cancelreason = '';

        $replace_array = array(
                    '{createtime}'      => trim($date),
                    '{updatetime}'      => trim($date),
                    '{sales}'           => $sales,
                    '{commission}'      => $commission,
                    '{idinaff}'         => trim($idinaff),
                    '{programname}'     => trim($programname),
                    '{sid}'             => trim($sid),
                    '{orderid}'         => trim($orderid),
                    '{clicktime}'       => trim($clicktime),
                    '{tradeid}'         => trim($tradeid),
                    '{tradestatus}'     => trim($tradestatus),
                    '{oldcur}'          => $oldcur,
                    '{oldsales}'        => $oldsales,
                    '{oldcommission}'   => $oldcommission,
                    '{tradetype}'       => trim($tradetype),
                    '{referrer}'        => $referrer,
                    '{cancelreason}'    => $cancelreason,
                    );

        $rev_file = PATH_DATA . '/' . AFF . '/revenue_' . str_replace('-', '', $_day) . '.upd';
        if (!isset($fws[$rev_file])) {
            $fws[$rev_file] = fopen($rev_file, 'w');
        }

        fwrite($fws[$rev_file], strtr(FILE_FORMAT,$replace_array) . "\n");
    }
}

foreach ($fws as $file => $f) {
    fclose($f);
}
?>
