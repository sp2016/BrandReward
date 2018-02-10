<?php 

try {

	echo 'Start Time:'.date('Y-m-d H:i:s').PHP_EOL;

	define('AFF_NAME', AFFILIATE_NAME);
	define('API_TOKEN', '8923739f-44a4-42fe-831d-4281a27c7330');
	define('PUBLISH_ID', "274181");
	define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}");
	$file_temp = PATH_TMP . '/' . AFF_NAME . '.tmp';


	if (defined('START_TIME') && defined('END_TIME')) {
		$end_dt = date('Y-m-d', strtotime(END_TIME));
		$begin_dt = date('Y-m-d', strtotime(START_TIME));
	} else {
		$end_dt = date('Y-m-d', strtotime('+1 day'));
		$begin_dt = date('Y-m-d', strtotime('-90 days', strtotime($end_dt)));
	}

	echo $begin_dt .'===>>'. $end_dt .PHP_EOL;
	
	
	$endDate = $end_dt;
	
	while (strtotime($endDate) >= strtotime($begin_dt))
	{
		$beginDate = date('Y-m-d', strtotime('-1 days', strtotime($endDate)));
		$url = "https://api.awin.com/publishers/".PUBLISH_ID."/transactions/?startDate=".$beginDate."T00%3A00%3A00&endDate=".$endDate."T00%3A00%3A00&timezone=Canada/Pacific&accessToken=".API_TOKEN;
		
		$ch = curl_init($url);
		$curl_opts = array(
				CURLOPT_HEADER => false,
				CURLOPT_NOBODY => false,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				//CURLOPT_FILE => $fw,
				CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
		);
		curl_setopt_array($ch, $curl_opts);
		
		echo "req => {$url}".PHP_EOL;
		
		$re = curl_exec($ch);
		if($re === false) {
			throw new \RuntimeException('API call failed with cURL error: ' . curl_error($ch));
		}
		curl_close($ch);
		//fclose($fw);
		
		$re = json_decode($re, true);
		//var_dump($re);exit;
		
		foreach ($re as $v)
		{
			$clicktime = date('Y-m-d H:i:s', strtotime($v['clickDate']));
			$createtime = date('Y-m-d H:i:s', strtotime($v['transactionDate']));
			$updatetime = date('Y-m-d H:i:s', strtotime($v['validationDate']));
			
			$day = date('Y-m-d',strtotime($createtime));
			
			$tradestatus = trim($v['commissionStatus']);
			$sid = (isset($v['clickRefs']['clickRef']))?$v['clickRefs']['clickRef']:$v['clickRefs']['clickRef2'];
			
			$oldsales = ($tradestatus == "declined")?0:$v['saleAmount']['amount'];
			$oldcommission = ($tradestatus == "declined")?0:$v['commissionAmount']['amount'];
			$cur = $v['commissionAmount']['currency'];
			
			$cur_exr = cur_exchange($cur, 'USD', $day);
			$sales = round($oldsales * $cur_exr, 4);
			$commission = round($oldcommission * $cur_exr, 4);
			
			$cancelreason = '';
			if($tradestatus == "declined"){
				$cancelreason = trim($v['declineReason']);
			}
			
			$replace_array = array(
					'{createtime}'      => $createtime,
					'{updatetime}'      => $updatetime,
					'{sales}'           => $sales,
					'{commission}'      => $commission,
					'{idinaff}'         => trim($v['advertiserId']),
					'{programname}'     => '',
					'{sid}'             => trim($sid),
					'{orderid}'         => trim($v['id']),
					'{clicktime}'       => $clicktime,
					'{tradeid}'         => trim($v['id']),
					'{tradestatus}'     => trim($tradestatus),
					'{oldcur}'          => $cur,
					'{oldsales}'        => $oldsales,
					'{oldcommission}'   => $oldcommission,
					'{tradetype}'       => trim($v['type']),
					'{referrer}'        => '',
					'{cancelreason}'    => $cancelreason,
			);
			
			$rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . $day . '.upd';
			if (!isset($fws[$rev_file])) {
				$fws[$rev_file] = fopen($rev_file, 'w');
				$comms[$rev_file] = 0;
			}
			
			fwrite($fws[$rev_file], strtr(FILE_FORMAT,$replace_array) . "\n");
		}
		
		 $endDate = $beginDate;
	}
	
	foreach ($fws as $file => $f) {
		fclose($f);
	}
	echo 'End Time:'.date('Y-m-d H:i:s').PHP_EOL;

}
catch (Exception $e) {
	var_dump($e);
}




?>