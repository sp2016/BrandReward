<?php
 
define('AFF_NAME', AFFILIATE_NAME);
define('API_Key', 'f5e4fd82b0feec812e86a98da43fb882490f43ee');
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
// Specify method arguments

/*  */

$page = 1;
$limit = 100;
$HasNextPage = true;
while ($HasNextPage)
{
	if (file_exists($file_temp))
		unlink($file_temp);
	$fw = fopen($file_temp, 'w');
	if (!$fw){
		echo "File open failed {$file_temp}";exit;
	}
	
	$args = array(
			'date_from' => $begin_dt,
			'date_to' => $end_dt,
			'limit' => $limit,
			'page' => $page,
	);
	
	$url = "http://api.slice.digital/3.0/stats/conversions?".http_build_query($args);
	$ch = curl_init($url);
	
	$curl_opts = array(
			CURLOPT_HEADER => false,
			CURLOPT_NOBODY => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_FILE => $fw,
			CURLOPT_HTTPHEADER => array("API-Key: ".API_Key),
			CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
	);
	curl_setopt_array($ch, $curl_opts);
	echo "req => {$url}\r\n";
	$Response = curl_exec($ch);
	curl_close($ch);
	fclose($fw);
	
	$fp = file_get_contents($file_temp);
	$fp = json_decode($fp, true);
	//print_r($fp);exit;
	if ($fp['status'] != 1)
		mydie('transcation crawl failed');
	
	$count = $fp['pagination']['total_count'];
	if (($page * $limit) >= $count)
		$HasNextPage = false;

	
	foreach ($fp['conversions'] as $v) 
	{
		$createtime = date('Y-m-d H:i:s', strtotime($v['created_at']));
		$clicktime = date('Y-m-d H:i:s', strtotime($v['click_time']));
		$orderid = $v['id'];
		$Sid = $v['sub1'];
		$programname = $v['offer']['title'];
		$TransactionId = $v['id'];
		$oldcommission = $v['revenue'];
		$tradestatus = $v['status'];
		$Curency = $v['currency'];
		$tradetype = '';
		$referrer = $v['referrer'];
		$ProgramId = $v['offer_id'];
		$oldsales = (!empty($v['sum']))?$v['sum']:'';
		
		$tdate = date("Y-m-d",strtotime($createtime));
		$cur_exr = cur_exchange($Curency, 'USD',$tdate);
		$sales = $oldsales>0?round($oldsales * $cur_exr, 4):0;
		$Commission = $oldcommission>0?round($oldcommission * $cur_exr, 4):0;
                
                $cancelreason = '';
		
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
	$page++;
}

foreach ($fws as $file => $f) {
	fclose($f);
}


?>
