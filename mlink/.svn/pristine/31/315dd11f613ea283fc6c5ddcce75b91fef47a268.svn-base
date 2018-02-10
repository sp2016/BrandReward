<?php 
define('MAX_RTRY', 5);

try {

	echo 'Start Time:'.date('Y-m-d H:i:s').PHP_EOL;

	define('AFF_NAME', AFFILIATE_NAME);
	define('API_UNAME', AFFILIATE_USER);
	define('API_PASS', AFFILIATE_PASS);
	define('PUBLISH_ID', "387850");
	define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");
	$file_temp = PATH_TMP . '/' . AFF_NAME . '.tmp';


	if (defined('START_TIME') && defined('END_TIME')) {
		$end_dt = date('Y-m-d', strtotime(END_TIME));
		$begin_dt = date('Y-m-d', strtotime(START_TIME));
	} else {
		$end_dt = date('Y-m-d', strtotime('+1 day'));
		$begin_dt = date('Y-m-d', strtotime('-90 days', strtotime($end_dt)));
	}

	echo $begin_dt .'===>>'. $end_dt .PHP_EOL;

	//联盟交易数据 一次一天 因为返回的数据没有日期。
	$page = 1;
	while(1){
			
		$fw = fopen($file_temp, 'w');
		if (!$fw)
			throw new Exception("File open failed {$file_temp}");

		
		$reportws_url = "https://services.daisycon.com/publishers/".PUBLISH_ID."/transactions?page=$page&per_page=100&start={BEGIN_DATE}%2000%3A00%3A00&end={END_DATE}%2000%3A00%3A00&order_direction=asc";
		$url = str_replace(array('{BEGIN_DATE}', '{END_DATE}'), array($begin_dt, $end_dt), $reportws_url);
		echo $url.PHP_EOL;
		//$url = "https://services.daisycon.com/publishers/381734/transactions?page=1&per_page=100&start=2017-01-01%2000%3A00%3A00&end=2017-06-12%2000%3A00%3A00&order_direction=asc";
		$ch = curl_init($url);
		$curl_opts = array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
				CURLOPT_FILE => $fw,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_HTTPHEADER => array('Authorization: Basic ' . base64_encode( API_UNAME . ':' . API_PASS )),
		);
		curl_setopt_array($ch, $curl_opts);
		//$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$reponseData = curl_exec($ch);
		curl_close($ch);
		fclose($fw);
		
		//判断是否爬到数据
		$ch = curl_init($url);
		$curl_opts = array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_HTTPHEADER => array('Authorization: Basic ' . base64_encode( API_UNAME . ':' . API_PASS )),
		);
		curl_setopt_array($ch, $curl_opts);
		//$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$reponseData = curl_exec($ch);
		curl_close($ch);
		
		if (empty($reponseData))
			break;
		
		//echo $code."\r\n";
		$contents = file_get_contents($file_temp);
		if($contents) $tranData = json_decode($contents,true);
		
		//var_dump($reponseData);exit;

		//$rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . str_replace('-', '', substr($date, 0, 10)) . '.upd';
		//echo $rev_file.PHP_EOL;
		//$file_res = fopen($rev_file, 'w');
		
		
		/* array (size=16)
			'affiliatemarketing_id' => string '17QQV1AFOKQ9TPAQ99ZPJX' (length=22)
			'date' => string '2017-06-05 12:22:55' (length=19)
			'program_id' => int 11794
			'country_id' => int 63
			'country_code' => string 'FR' (length=2)
			'region_id' => int 0
			'gender' => null
			'age' => null
			'device_type' => string 'pc' (length=2)
			'device_model_id' => int 2
			'device_platform_id' => int 461
			'device_browser_id' => int 3428
			'parts' =>
				array (size=1)
					0 =>
						array (size=22)
							'id' => string '7VIR' (length=4)
							'date' => string '2017-06-05 12:22:55' (length=19)
							'date_click' => string '2017-06-05 12:18:54' (length=19)
							'ad_id' => int 1530614
							'media_id' => int 268310
							'publisher_description' => string 'Astruc Annick' (length=13)
							'extra_1' => string '' (length=0)
							'extra_2' => string '' (length=0)
							'extra_3' => string '' (length=0)
							'extra_4' => string '' (length=0)
							'extra_5' => string '' (length=0)
							'revenue' => float 32.76
							'approval_date' => null
							'disapproved_reason' => null
							'status' => string 'open' (length=4)
							'media_name' => string 'Codespromofr.com' (length=16)
							'commission' => float 16.38
							'last_modified' => string '2017-06-05 12:22:55' (length=19)
							'referencenumber' => string '' (length=0)
							'subid' => string '3e76d9c470df592605db756e1404f96f' (length=32)
							'subid_2' => string '' (length=0)
							'subid_3' => string '' (length=0)
			'anonymous_ip' => string '?.57.217.84' (length=11)
			'fourhash' => string 'fa6c34ad25ee7290798b634347de0eb9' (length=32)
			'program_name' => string 'AVS4You' (length=7)
		 */
							
		
		foreach ($tranData as $value)
		{
			$date = date('Y-m-d', strtotime($value['date']));
			foreach ($value['parts'] as $v)
			{
				$OldCommission = $v['commission'];
				if(!isset($curr['currency'][$date])){
					$curr['currency'][$date] = cur_exchange('EUR', 'USD', $date);
				}
				$cur_exr = $curr['currency'][$date];
				$commission = round($OldCommission * $cur_exr, 4);
				$sales = round($v['revenue'] * $cur_exr, 4);
                                $cancelreason = trim($v['disapproved_reason']);
				
				$replace_array = array(
						'{createtime}'      => $v['date'],
						'{updatetime}'      => $v['date'],
						'{sales}'           => $sales,
						'{commission}'      => $commission,
						'{idinaff}'         => $value['program_id'],
						'{programname}'     => $value['program_name'],
						'{sid}'             => $v['subid'],
						'{orderid}'         => $v['id'],
						'{clicktime}'       => $v['date_click'],
						'{tradeid}'         => $v['id'],
						'{tradestatus}'     => $v['status'],
						'{oldcur}'          => 'EUR',
						'{oldsales}'        => $v['revenue'],
						'{oldcommission}'   => $OldCommission,
						'{tradetype}'       => '',
						'{referrer}'        => '',
                                                '{cancelreason}'    => $cancelreason,
				);
					
				$rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . date('Ymd', strtotime($v['date'])) . '.upd';
				if (!isset($fws[$rev_file])) {
					$fws[$rev_file] = fopen($rev_file, 'w');
					$comms[$rev_file] = 0;
				}
				fwrite($fws[$rev_file], strtr(FILE_FORMAT,$replace_array) . "\n");
			}
		}
		$page++;
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
