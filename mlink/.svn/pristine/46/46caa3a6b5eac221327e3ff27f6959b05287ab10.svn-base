<?php
 
define('AFF_NAME', AFFILIATE_NAME);
define('API_Key', 'general');
define('API_Secret', 'hoBWrv75mjf3l1tNACQVkswRK9wR9clz1P/+ybpoyWM=');
define('URL', 'https://api.involve.asia/api/');
define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");

if(defined('START_TIME') && defined('END_TIME')) {
	$end_dt   = date('Y-m-d', strtotime(END_TIME));
	$begin_dt = date('Y-m-d', strtotime(START_TIME));
} else {
	$end_dt   = date('Y-m-d');
	$begin_dt = date('Y-m-d', strtotime('-90 days'));
}
echo "Date setting: ST:{$begin_dt} ET:{$end_dt} \n";

$file_temp = PATH_TMP . '/' . AFF_NAME . '.tmp';

//authentication, get token
$auth_url = URL.'authenticate';
$ch = curl_init($auth_url);

$curl_opts = array(
		CURLOPT_HEADER => false,
		CURLOPT_NOBODY => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYHOST => false,
		//CURLOPT_FILE => $fw,
		CURLOPT_POST => true,
		//CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
		CURLOPT_POSTFIELDS => 'secret='.urlencode(API_Secret).'&key='.urlencode(API_Key),
		CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
);
curl_setopt_array($ch, $curl_opts);
echo "req => {$auth_url}\r\n";
$auth_r = curl_exec($ch);
curl_close($ch);
//print_r($auth_r);exit;
$auth_r = json_decode($auth_r, true);
if ($auth_r['status'] == 'error')
	mydie('authentication failed: '.$auth_r['message']);
$token = $auth_r['data']['token'];

//get transcation
$url = URL.'conversions/range';
$page = 1;
$HasNextPage = true;
while ($HasNextPage)
{
	if (file_exists($file_temp))
		unlink($file_temp);
	$fw = fopen($file_temp, 'w');
	if (!$fw){
		echo "File open failed {$file_temp}";exit;
	}
	
	$retry = 1;
	do{
		$postfields = array(
				'start_date' => $begin_dt,
				'end_date' => $end_dt,
				'page' => $page,
				'limit' => 100,
		        'filters'=>array(
		            'preferred_currency'=>'USD',
		        ),
		);
		$ch = curl_init($url);
		$curl_opts = array(
				CURLOPT_HEADER => false,
				CURLOPT_NOBODY => false,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_FILE => $fw,
				CURLOPT_POST => true,
				CURLOPT_HTTPHEADER => array('Authorization: Bearer '.urlencode($token)),
				CURLOPT_POSTFIELDS => http_build_query($postfields),
				CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
		);
		curl_setopt_array($ch, $curl_opts);
		echo "req => {$url}\r\n";
		$result = curl_exec($ch);
		curl_close($ch);
		fclose($fw);
		
		$retry++;
		if ($retry > 3)
			mydie("CURL request failed in page $page\r\n");
	}while (!$result);
	
	$result = file_get_contents($file_temp);
	$result = json_decode($result, true);
	//print_r($result);exit;
	if ($result['status'] == 'error')
		mydie('transcation crawl failed: '.$result['message']);
	if (empty($result['data']['nextPage']))
		$HasNextPage = false;
	foreach ($result['data']['data'] as $v)
	{
		$TransactionTime = date('Y-m-d H:i:s', strtotime($v['datetime_conversion']));
		$orderid = $v['conversion_id'];
		$Sid = $v['aff_sub1'];
		$programname = $v['offer_name'];
		$TransactionId = $v['conversion_id'];
		$oldcommission = $v['payout'];
		$tradestatus = $v['conversion_status'];
		$Curency = 'USD';//$v['currency'];
		$ProgramId = $v['offer_id'];
		$oldsales = $v['sale_amount'];
		
		$tdate = date("Y-m-d",strtotime($TransactionTime));
		$cur_exr = cur_exchange($Curency, 'USD',$tdate);
		$sales = $oldsales;//$oldsales>0?round($oldsales * $cur_exr, 4):0;
		$Commission = $oldcommission;//$oldcommission>0?round($oldcommission * $cur_exr, 4):0;
                $cancelreason = '';
		
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
				'{oldcommission}'   => $oldcommission,
				'{tradetype}'       => '',
				'{referrer}'        => '',
                                '{cancelreason}'    => $cancelreason,
		);
		$rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . str_replace('-', '', $tdate) . '.upd';
		if (!isset($fws[$rev_file])) {
			$fws[$rev_file] = fopen($rev_file, 'w');
		}
		
		fwrite($fws[$rev_file], strtr(FILE_FORMAT,$replace_array) . "\n");
		
	}
	$page++;
	if ($page >= 20)
	{
		mydie("Page numbers is too many, more than 20 pages\r\n");
		break;
	}
}

foreach ($fws as $file => $f) {
	fclose($f);
}


















?>
