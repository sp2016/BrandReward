<?php
 
define('AFF_NAME', AFFILIATE_NAME);
define('NetworkId', 'offerfactory');
define('API_Key', 'd325a49604dc0bc22b5607f9ffd6eded86c824cef0bc8a8b6adc335856f92471');
define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}");

if(defined('START_TIME') && defined('END_TIME')) {
	$end_dt   = date('Y-m-d', strtotime(END_TIME));
	$begin_dt = date('Y-m-d', strtotime(START_TIME));
} else {
	$end_dt   = date('Y-m-d');
	$begin_dt = date('Y-m-d', strtotime('-90 days'));
}
echo "Date setting: ST:{$begin_dt} ET:{$end_dt} \n";

$file_temp = PATH_TMP . '/' . AFF_NAME . '.tmp';
// Specify method arguments
$args = array(
		'NetworkId' => NetworkId,
		'Target' => 'Affiliate_Report',
		'Method' => 'getConversions',
		'api_key' => API_Key,
		'fields' => array(
				'Stat.id',
				'Stat.datetime',
				'Offer.name',
				'Stat.conversion_status',
				'Stat.approved_payout',
				'Stat.sale_amount',
				'Stat.ad_id',
				'Stat.affiliate_info1',
				'Stat.offer_id',
				'Stat.currency',
				'Stat.refer'
		),
		'data_start' => $begin_dt,
		'data_end' => $end_dt
);

if (file_exists($file_temp))
	unlink($file_temp);
$fw = fopen($file_temp, 'w');
if (!$fw){
	echo "File open failed {$file_temp}";exit;
}

$url = "https://api.hasoffers.com/Apiv3/json?".http_build_query($args);			//写成https://NetworkId.api.hasoffers.com/Apiv3/json?也可以，$args参数里就不用带NetworkId了
$ch = curl_init($url);

$curl_opts = array(
		CURLOPT_HEADER => false,
		CURLOPT_NOBODY => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_FILE => $fw,
		CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
);
curl_setopt_array($ch, $curl_opts);

echo "req => {$url}<br />";

$jsonEncodedApiResponse = curl_exec($ch);
if($jsonEncodedApiResponse === false) {
	throw new \RuntimeException(
			'API call failed with cURL error: ' . curl_error($ch)
	);
}
curl_close($ch);
fclose($fw);

$fp = file_get_contents($file_temp);
$fp = json_decode($fp, true);
//print_r($fp);exit;
foreach ($fp['response']['data']['data'] as $v) {

	$TransactionTime = date('Y-m-d H:i:s', strtotime($v['Stat']['datetime']));
	$orderid = $v['Stat']['ad_id'];
	$Sid = $v['Stat']['affiliate_info1'];
	$programname = $v['Offer']['name'];
	$TransactionId = $v['Stat']['ad_id'];
	$Commission = $v['Stat']['approved_payout'];
	$tradestatus = $v['Stat']['conversion_status'];
	$Curency = $v['Stat']['currency'];
	$tradetype = '';
	$referrer = $v['Stat']['refer'];
	$ProgramId = $v['Stat']['offer_id'];
	$oldsales = (!empty($v['Stat']['sale_amount']))?$v['Stat']['sale_amount']:'';
	$sales = '';
	
	$replace_array = array(
			'{createtime}'      => trim($TransactionTime),
			'{updatetime}'      => trim($TransactionTime),
			'{sales}'           => $sales,
			'{commission}'      => $Commission,
			'{idinaff}'         => $ProgramId,
			'{programname}'     => trim($programname),
			'{sid}'             => trim($Sid),
			'{orderid}'         => trim($orderid),
			'{clicktime}'       => trim($TransactionTime),
			'{tradeid}'         => trim($TransactionId),
			'{tradestatus}'     => trim($tradestatus),
			'{oldcur}'          => $Curency,
			'{oldsales}'        => $oldsales,
			'{oldcommission}'   => $Commission,
			'{tradetype}'       => trim($tradetype),
			'{referrer}'        => $referrer,
	);
	$_day = date("Y-m-d", strtotime($v['Stat']['datetime']));
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