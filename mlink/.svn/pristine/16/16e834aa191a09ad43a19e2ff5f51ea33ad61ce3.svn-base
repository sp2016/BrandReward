<?php

define('AFF_NAME', AFFILIATE_NAME);
define('AffID', '679');
define('API_Key', 'Uz9Fp9pKDV39iAjqxuR0g');
define('API_URL','https://admin.shoogloo.media/affiliates/api/5/reports.asmx/Conversions?');
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
$filename = PATH_TMP . '/' . AFF_NAME . '.xml';

if (file_exists($filename))
	unlink($filename);

$fw = fopen($filename, 'w');

$url = API_URL."api_key=".API_Key."&affiliate_id=".AffID."&start_date=".date('m/d/Y+H:i:s', strtotime($start_dt))."&end_date=".date('m/d/Y', strtotime($end_dt))."+23:59:59"."&offer_id=0&start_at_row=1&row_limit=0";
echo "req => {$url} \n";
$ch = curl_init($url);
$curl_opts = array(CURLOPT_HEADER => false,
				CURLOPT_NOBODY => false,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_FILE => $fw
);
curl_setopt_array($ch, $curl_opts);
$rs = curl_exec($ch);
curl_close($ch);
fclose($fw);

$xml = simplexml_load_file($filename);
$xml = json_decode(json_encode($xml),true);
//print_r($xml);exit;

if (!$xml['success'])
	mydie("request ths API failed");
if ($xml['row_count'] != count($xml['conversions']['conversion']))
	mydie("returned counts error of data");
	
//get offer_contract_id
$offer_url = "http://admin.shoogloo.media/affiliates/api/4/offers.asmx/OfferFeed?api_key=".API_Key."&affiliate_id=".AffID."&campaign_name=&media_type_category_id=0&vertical_category_id=0&vertical_id=0&offer_status_id=0&tag_id=0&start_at_row=1&row_limit=0";
$ch = curl_init($offer_url);
$curl_opts = array(CURLOPT_HEADER => false,
		CURLOPT_NOBODY => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_SSL_VERIFYPEER => false,
);
curl_setopt_array($ch, $curl_opts);
$rs = curl_exec($ch);
curl_close($ch);
$oxml = simplexml_load_string($rs);
$oxml = json_decode(json_encode($oxml),true);
//print_r($oxml);exit;

foreach ($xml['conversions']['conversion'] as $v){
	
	
	
	
	$TransactionTime = date('Y-m-d H:i:s', strtotime($v['conversion_date']));
	$orderid = $v['offer_id'];
	$Sid = (!empty($v['subid_2']))?$v['subid_2']:'';
	$programname = $v['offer_name'];
	$TransactionId = $v['conversion_id'];
	$Commission = $v['price'];
	$tradestatus = $v['disposition'];
	if ($v['currency_symbol'] == '$')
		$Curency = 'USD';
	else 
		mydie("Curency is ".$v['currency_symbol']);
	$tradetype = '';
	$referrer = '';
	
	$ProgramId = '';
	foreach ($oxml['offers']['offer'] as $k){
		if($v['offer_id'] == $k['offer_id']){
			$ProgramId = $k['offer_id'] . '_' . $k['offer_contract_id'];
			break;
		}
	}
	
	//print_r($Sid."\r\n");
        $cancelreason = '';
	$replace_array = array(
			'{createtime}'      => trim($TransactionTime),
			'{updatetime}'      => trim($TransactionTime),
			'{sales}'           => '',
			'{commission}'      => $Commission,
			'{idinaff}'         => $ProgramId,
			'{programname}'     => trim($programname),
			'{sid}'             => trim($Sid),
			'{orderid}'         => trim($orderid),
			'{clicktime}'       => trim($TransactionTime),
			'{tradeid}'         => trim($TransactionId),
			'{tradestatus}'     => trim($tradestatus),
			'{oldcur}'          => $Curency,
			'{oldsales}'        => '',
			'{oldcommission}'   => $Commission,
			'{tradetype}'       => trim($tradetype),
			'{referrer}'        => $referrer,
                        '{cancelreason}'    => $cancelreason,
	);
	
	$_day = date("Y-m-d", strtotime($v['conversion_date']));
	$rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . str_replace('-', '', $_day) . '.upd';
	if (!isset($fws[$rev_file])) {
		$fws[$rev_file] = fopen($rev_file, 'w');
	}
	
	fwrite($fws[$rev_file], strtr(FILE_FORMAT,$replace_array) . "\n");
}

foreach ($fws as $file => $f) {
	fclose($f);
}


?>
