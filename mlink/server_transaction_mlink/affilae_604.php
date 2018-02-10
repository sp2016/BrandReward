<?php
 
define('AFF_NAME', AFFILIATE_NAME);
define('PROFILE_ID', '58d4b05ae8faceb33e8b4574');
define('API_USER', '58d0fd1193b58c3d422a51bb');
define('API_KEY', 'd40826f52897177e89b885ebb0fcc928');
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

//get transaction count
$args = array(
		'dateFrom' => strtotime($begin_dt),
		'dateTo' => strtotime($end_dt),
		'limit' => 1,
		'skip' => 0,
		'count' => 1,
);
$url = "https://api.affilae.com/2.0/publisher/".PROFILE_ID."/commissions?".http_build_query($args);
$ch = curl_init($url);

$curl_opts = array(
		CURLOPT_HEADER => false,
		CURLOPT_NOBODY => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_HTTPHEADER => array('Authorization: Basic '.base64_encode(API_USER.':'.API_KEY)),
		CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
);
curl_setopt_array($ch, $curl_opts);
echo "req => {$url}\r\n";
$Response = curl_exec($ch);
curl_close($ch);

if (is_numeric($Response))
{
	$count = $Response;
}else 
{
	mydie('get transaction count failed, please check it.');
}

//get transactions
$offset = 0;
$limit = 100;
$HasNextPage = true;
while ($HasNextPage)
{
	if ($offset + $limit >= $count)
	{
		$HasNextPage = false;
	}
	if (file_exists($file_temp))
	{
		unlink($file_temp);
	}
	$fw = fopen($file_temp, 'w');
	if (!$fw){
		echo "File open failed {$file_temp}";exit;
	}
	
	$args = array(
			'dateFrom' => strtotime($begin_dt),
			'dateTo' => strtotime($end_dt),
			'limit' => $limit,
			'skip' => $offset,
			//'count' => 1,
	);
	$url = "https://api.affilae.com/2.0/publisher/".PROFILE_ID."/commissions?".http_build_query($args);
	$ch = curl_init($url);
	
	$curl_opts = array(
			CURLOPT_HEADER => false,
			CURLOPT_NOBODY => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_FILE => $fw,
			CURLOPT_HTTPHEADER => array('Authorization: Basic '.base64_encode(API_USER.':'.API_KEY)),
			CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
	);
	curl_setopt_array($ch, $curl_opts);
	echo "req => {$url}\r\n";
	$Response = curl_exec($ch);
	curl_close($ch);
	fclose($fw);
	
	$fp = file_get_contents($file_temp);
	$fp = json_decode($fp, true);
	//var_dump($fp);exit;
	if (!$fp)
	{
		mydie('Crawl transaction failed !' );
	}
	

	foreach ($fp as $v)
	{	print_r($v);exit;
		$createtime = date('Y-m-d H:i:s', $v['created_at']);
		$clicktime = date('Y-m-d H:i:s', $v['cookie']);
		$orderid = $v['identifier'];
		$programname = '';
		$TransactionId = $v['identifier'];
		$oldcommission = $v['commission'];
		
		if ($v['is_pending']){
			$tradestatus = 'Pending';
		}elseif($v['refused_at']){ 
			$tradestatus = 'Refused';
		}else{
                        $tradestatus = 'Accepted';
                }

                		
		$Curency = $v['currency'];
		$tradetype = $v['payout_type'];
		$referrer = $v['referrer'];
		$ProgramId = '';
		$oldsales = (!empty($v['amount']))?$v['amount']:'';
		
		$m = array();
		preg_match('/sid=(.*)/', $v['landing_page'], $m);
		$Sid = $m[1];
		
		$tdate = date("Y-m-d",strtotime($createtime));
		$cur_exr = cur_exchange($Curency, 'USD',$tdate);
		$sales = $oldsales>0?round($oldsales * $cur_exr, 4):0;
		$Commission = $oldcommission>0?round($oldcommission * $cur_exr, 4):0;
                
                $cancelreason = trim($v['refuse_reason']);
		
		$replace_array = array(
				'{createtime}'      => trim($createtime),
				'{updatetime}'      => trim($createtime),
				'{sales}'           => $sales,
				'{commission}'      => $Commission,
				'{idinaff}'         => $ProgramId,
				'{programname}'     => trim($programname),
				'{sid}'             => trim($Sid),
				'{orderid}'         => trim($orderid),
				'{clicktime}'       => trim($clicktime),
				'{tradeid}'         => trim($TransactionId),
				'{tradestatus}'     => trim($tradestatus),
				'{oldcur}'          => $Curency,
				'{oldsales}'        => $oldsales,
				'{oldcommission}'   => $oldcommission,
				'{tradetype}'       => trim($tradetype),
				'{referrer}'        => $referrer,
                                '{cancelreason}'    => $cancelreason,
		);
		$rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . str_replace('-', '', $tdate) . '.upd';
		if (!isset($fws[$rev_file])) {
			$fws[$rev_file] = fopen($rev_file, 'w');
		}
		
		fwrite($fws[$rev_file], strtr(FILE_FORMAT,$replace_array) . "\n");
	}
	
	$offset += 100;
}

foreach ($fws as $file => $f) {
	fclose($f);
}



?>
