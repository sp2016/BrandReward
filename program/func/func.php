<?php
	include_once(dirname(dirname(__FILE__)) . "/crawl/lib/class.AlertEmail.php");
	global $api_server;
	global $api_obj;

	$api_server = array();
	$api_server['program_commission_format'] = array('name'=>'program_commission_format','argv'=>'pid','class'=>'Program','func'=>'processCommissionTxt');
	$api_server['program_domain_sort'] = array('name'=>'program_domain_sort','argv'=>'pid,did','class'=>'Program','func'=>'sortProgramInDomain');
	$api_server['program_domain_into_redis'] = array('name'=>'program_domain_into_redis','argv'=>'','class'=>'Program','func'=>'updateTempToRedis');
	$api_server['log_tracking_data_to_db'] = array('name'=>'log_tracking_data_to_db','argv'=>'','class'=>'Program','func'=>'log_tracking_data_to_db');
	$api_server['cron_set_check_outurl'] = array('name'=>'cron_set_check_outurl','argv'=>'','class'=>'Program','func'=>'cron_set_check_outurl');
	$api_server['cron_check_outurl_res'] = array('name'=>'cron_check_outurl_res','argv'=>'','class'=>'Program','func'=>'cron_check_outurl_res');
	$api_server['cron_set_check_pageurl'] = array('name'=>'cron_set_check_pageurl','argv'=>'','class'=>'Program','func'=>'cron_set_check_pageurl');
	$api_server['cron_check_pageurl_res'] = array('name'=>'cron_check_pageurl_res','argv'=>'','class'=>'Program','func'=>'cron_check_pageurl_res');

	$api_server['check_diff_in_final_page_url'] = array('name'=>'check_diff_in_final_page_url','argv'=>'','class'=>'Program','func'=>'check_diff_in_final_page_url');

	$api_server['test_coupon_affurl'] = array('name'=>'test_coupon_affurl','argv'=>'','class'=>'Program','func'=>'test_coupon_affurl');


	function call_api($param){
		global $api_server;
		global $api_obj;

		if(!isset($api_server[$param['act']])){
			over('error:api is not exist!');
		}

		$obj_name = $api_server[$param['act']]['class'];
		$func_name = $api_server[$param['act']]['func'];
		if(isset($api_obj[$obj_name]))
			$obj = $api_obj[$obj_name];
		else{
			$obj = new $obj_name;
			$api_obj[$obj_name] = $obj;
		}

		call_user_method($func_name,$obj,$param);
	}

	function guide(){
		global $api_server;
		foreach($api_server as $k=>$v){
			echo $k.':'.$v['name'].'('.$v['argv'].')'."\r\n";
		}
		exit();
	}

	function parseArgv($argv){
		unset($argv[0]);
		$data = array();
		foreach($argv as $k=>$v){
			if(empty($v))
				continue;

			list($key,$value) = explode('=',$v);

			if($key[0] == '-')
				$key = substr($key,1);

			$data[$key] = $value;
		}
		return $data;
	}

	function over($str){
		echo $str;
		exit();
	}

	function get_regex($type){
		$regx = '';
		switch ($type) {
			case 'number':
				$regx = '/((?:&#\d+;)|[^\d\s->\.\w,\|;]*)((?:\d+)(?:,\d+)?(?:\.\d+)?)((?:&#\d+;)|[^\d\s-<\(\)\w,\/]*)/i';
				break;
			default:
				break;
		}
		return $regx;
	}

	function currency_match_str($txt){//解析出value或者百分比
		$match = array();
		$currencyMap = currency_get_map();

		$cur_merge = array();
		foreach($currencyMap as $v){
			$cur_merge = array_merge($cur_merge,$v);
		}
		$regx = '/(\s?(?:'.join('|',$cur_merge).')\s*)?((?:\d+)(?:,\d+)?(?:\.\d+)?)(\s*(?:'.join('|',$cur_merge).'))?\s?/i';

		preg_match_all($regx, $txt, $m);
		if(!empty($m[0]))
			return $m[0];
		else
			return array();
	}

	function currency_match_str_new($txt){//解析出value或者百分比
		$match = array();
		$currencyMap = currency_get_map();

		$cur_merge = array();
		foreach($currencyMap as $v){
			$cur_merge = array_merge($cur_merge,$v);
		}
		$regx = '/(\s?(?:'.join('|',$cur_merge).')\s*)?((?:\d+)(?:,\d+)?(?:\.\d+)?)(\s*(?:'.join('|',$cur_merge).'))?\s?/i';

		preg_match($regx, $txt, $m);
		if(!empty($m[0]))
			return $m[0];
		else
			return null;
	}

	function currency_parse($txt,$currency=''){
		$info = array();
		$info['hasIncentive'] = 0;
		$info['str_head'] = '';
		$info['str_num'] = 0;
		$info['str_end'] = '';
		$info['currency'] = '';
		$info['str'] = '';

		$len = strlen($txt);
		if($len>2 && $txt[$len-2] == '|'){
			$info['hasIncentive'] = $txt[$len-1];
			$txt = substr($txt,0,-2);
		}

		$currencyMap = currency_get_map();
		$parse = array();

		foreach($currencyMap as $cur=>$tag){
			$regx = '/(\s?(?:'.join('|',$tag).')\s*)?((?:\d+)(?:,\d+)?(?:\.\d+)?)(\s*(?:'.join('|',$tag).'))?\s?/i';
			preg_match_all($regx, $txt, $m);

			if(!empty( $m[0] )){
				if(!empty($m[1][0]) || !empty($m[3][0])){
					$info['str_head'] = trim($m[1][0]);
					$info['str_num'] = trim($m[2][0]);
					$info['str_end'] = trim($m[3][0]);
					$info['str']    =  $info['str_head'].' '.$info['str_num'].' '.$info['str_end'];
					$info['currency'] = $cur == 'PER'?'':$cur;
					break;
				}
			}

		}


		if(empty($info['str']) && !empty($currency) && strpos($txt, $currency) ===false){
			$txt = $txt.' '.$currency;
			$info = currency_parse($txt, $currency);

		}

		return $info;
	}

	function currency_get_map(){
		$currencyMap = array();
		$currencyMap['SGD'] = array('SGD','S\$');
		$currencyMap['USD'] = array('\$','&#36;','USD','Dollar');
		$currencyMap['GBP'] = array('£','&#163;','&pound;','GBP','￡');
		$currencyMap['EUR'] = array('€','&#8364;','&euro;','EUR');
		$currencyMap['SEK'] = array('kr','SEK');
		$currencyMap['INR'] = array('Rs','INR');
		$currencyMap['CNY'] = array('￥','CNY');
		$currencyMap['KER'] = array('WON','KER');
		$currencyMap['CHF'] = array('CHF');
		$currencyMap['PLN'] = array('PLN');
		$currencyMap['AUD'] = array('AUD');
		$currencyMap['RUB'] = array('RUB','RUR');
		$currencyMap['CAD'] = array('CAD');
		$currencyMap['DHS'] = array('DHS','DH','DIRHAMS','AED');
		$currencyMap['ZAR'] = array('ZAR');
		$currencyMap['NZD'] = array('NZD','NZ\$');
		
		$currencyMap['PER'] = array('%','&#37;');
        $currencyMap['AED'] = array('AED');
        $currencyMap['ALL'] = array('ALL');
        $currencyMap['ANG'] = array('ANG');
        $currencyMap['ARS'] = array('ARS');
        $currencyMap['AWG'] = array('AWG');
        $currencyMap['BBD'] = array('BBD');
        $currencyMap['BDT'] = array('BDT');
        $currencyMap['BGN'] = array('BGN');
        $currencyMap['BHD'] = array('BHD');
        $currencyMap['BIF'] = array('BIF');
        $currencyMap['BMD'] = array('BMD');
        $currencyMap['BND'] = array('BND');
        $currencyMap['BOB'] = array('BOB');
        $currencyMap['BRL'] = array('BRL');
        $currencyMap['BSD'] = array('BSD');
        $currencyMap['BTN'] = array('BTN');
        $currencyMap['BWP'] = array('BWP');
        $currencyMap['BYR'] = array('BYR');
        $currencyMap['BZD'] = array('BZD');
        $currencyMap['CLP'] = array('CLP');
        $currencyMap['COP'] = array('COP');
        $currencyMap['CRC'] = array('CRC');
        $currencyMap['CUP'] = array('CUP');
        $currencyMap['CVE'] = array('CVE');
        $currencyMap['CZK'] = array('CZK');
        $currencyMap['DJF'] = array('DJF');
        $currencyMap['DKK'] = array('DKK');
        $currencyMap['DOP'] = array('DOP');
        $currencyMap['DZD'] = array('DZD');
        $currencyMap['EEK'] = array('EEK');
        $currencyMap['EGP'] = array('EGP');
        $currencyMap['ETB'] = array('ETB');
        $currencyMap['FJD'] = array('FJD');
        $currencyMap['FKP'] = array('FKP');
        $currencyMap['GHS'] = array('GHS');
        $currencyMap['GMD'] = array('GMD');
        $currencyMap['GNF'] = array('GNF');
        $currencyMap['GTQ'] = array('GTQ');
        $currencyMap['GYD'] = array('GYD');
        $currencyMap['HKD'] = array('HKD');
        $currencyMap['HNL'] = array('HNL');
        $currencyMap['HRK'] = array('HRK');
        $currencyMap['HTG'] = array('HTG');
        $currencyMap['HUF'] = array('HUF');
        $currencyMap['IDR'] = array('IDR');
        $currencyMap['ILS'] = array('ILS');
        $currencyMap['IQD'] = array('IQD');
        $currencyMap['IRR'] = array('IRR');
        $currencyMap['ISK'] = array('ISK');
        $currencyMap['JOD'] = array('JOD');
        $currencyMap['JPY'] = array('JPY');
        $currencyMap['KES'] = array('KES');
        $currencyMap['KGS'] = array('KGS');
        $currencyMap['KHR'] = array('KHR');
        $currencyMap['KMF'] = array('KMF');
        $currencyMap['KPW'] = array('KPW');
        $currencyMap['KRW'] = array('KRW');
        $currencyMap['KWD'] = array('KWD');
        $currencyMap['KYD'] = array('KYD');
        $currencyMap['KZT'] = array('KZT');
        $currencyMap['LKR'] = array('LKR');
        $currencyMap['MAD'] = array('MAD');
        $currencyMap['MDL'] = array('MDL');
        $currencyMap['MKD'] = array('MKD');
        $currencyMap['MMK'] = array('MMK');
        $currencyMap['MNT'] = array('MNT');
        $currencyMap['MOP'] = array('MOP');
        $currencyMap['MRO'] = array('MRO');
        $currencyMap['MUR'] = array('MUR');
        $currencyMap['MVR'] = array('MVR');
        $currencyMap['MWK'] = array('MWK');
        $currencyMap['MXN'] = array('MXN');
        $currencyMap['MYR'] = array('MYR');
        $currencyMap['NAD'] = array('NAD');
        $currencyMap['NGN'] = array('NGN');
        $currencyMap['NIO'] = array('NIO');
        $currencyMap['NOK'] = array('NOK');
        $currencyMap['NPR'] = array('NPR');
        $currencyMap['OMR'] = array('OMR');
        $currencyMap['PAB'] = array('PAB');
        $currencyMap['PEN'] = array('PEN');
        $currencyMap['PGK'] = array('PGK');
        $currencyMap['PHP'] = array('PHP');
        $currencyMap['PKR'] = array('PKR');
        $currencyMap['PYG'] = array('PYG');
        $currencyMap['QAR'] = array('QAR');
        $currencyMap['RON'] = array('RON');
        $currencyMap['RWF'] = array('RWF');
        $currencyMap['SAR'] = array('SAR');
        $currencyMap['SBD'] = array('SBD');
        $currencyMap['SCR'] = array('SCR');
        $currencyMap['SDG'] = array('SDG');
        $currencyMap['SHP'] = array('SHP');
        $currencyMap['SKK'] = array('SKK');
        $currencyMap['SLL'] = array('SLL');
        $currencyMap['SOS'] = array('SOS');
        $currencyMap['STD'] = array('STD');
        $currencyMap['SVC'] = array('SVC');
        $currencyMap['SYP'] = array('SYP');
        $currencyMap['SZL'] = array('SZL');
        $currencyMap['THB'] = array('THB');
        $currencyMap['TND'] = array('TND');
        $currencyMap['TOP'] = array('TOP');
        $currencyMap['TRY'] = array('TRY');
        $currencyMap['TTD'] = array('TTD');
        $currencyMap['TWD'] = array('TWD');
        $currencyMap['TZS'] = array('TZS');
        $currencyMap['UAH'] = array('UAH');
        $currencyMap['UGX'] = array('UGX');
        $currencyMap['UYU'] = array('UYU');
        $currencyMap['UZS'] = array('UZS');
        $currencyMap['VEF'] = array('VEF');
        $currencyMap['VND'] = array('VND');
        $currencyMap['VUV'] = array('VUV');
        $currencyMap['WST'] = array('WST');
        $currencyMap['XAF'] = array('XAF');
        $currencyMap['XCD'] = array('XCD');
        $currencyMap['XOF'] = array('XOF');
        $currencyMap['XPF'] = array('XPF');
        $currencyMap['YER'] = array('YER');
        $currencyMap['ZMK'] = array('ZMK');

		return $currencyMap;
	}


	function select_commission_used($commission,$cur=''){
		$usedCommission = '';
		$listCommssion = array();
		$CommissionUsed = '';
		$CommissionType = '';
		$str_head = '';
		$str_end = '';
		$hasIncentive = 0;
		$newCommissionTxt = '';
		$CommissionCurrency = '';

		$regex_number = get_regex('number');

		foreach($commission as $k=>$v){
			$commission[$k] = trim($v);
		}
		$num = count($commission);
		if($num < 1){

		}elseif($num == 1){
			$str = array_shift($commission);
			$info = currency_parse($str,$cur);//currency_parse����������������
			if(ceil($info['str_num']) != 0){
				$hasIncentive = $info['hasIncentive'];
				$listCommssion[] = $info['str_num'];
				$str_head = $info['str_head'];
				$str_end = $info['str_end'];
				$commission[0] = $info['str'];
				$CommissionCurrency = $info['currency'];
			}else{
				unset($commission[0]);
			}

		}else{
			$hasIncentive = 1;
			foreach($commission as $k=>$v){
				$str = $v;

				$info = currency_parse($str,$cur);
				if(ceil($info['str_num']) == 0){
					unset($commission[$k]);
					continue;
				}

				if(empty($str_head) && empty($str_end)){
					$str_head = $info['str_head'];
					$str_end = $info['str_end'];
				}

				if($info['str_head'] != $str_head || $info['str_end'] != $str_end){
					unset($commission[$k]);
					continue;
				}
				$commission[$k] = $info['str'];
				$listCommssion[] = $info['str_num'];
				$CommissionCurrency = $info['currency'];
			}

		}
		if(!empty($listCommssion)){
			if(count($listCommssion) > 1){
				$c = count($listCommssion);
				$all = '';
				foreach($listCommssion as $comis){
					$comis = $comis.'';
					$all = $all + $comis;
					$all = $all.'';
				}

				$CommissionUsed = number_format($all/$c,2,'.','');
			}else{
				$CommissionUsed = $listCommssion[0];
			}
			$usedCommission = $str_head.$CommissionUsed.$str_end;

			if(strpos($usedCommission,'%') !== false || strpos($usedCommission,'&#37;') !== false)
				$CommissionType = 'percent';
			else
				$CommissionType = 'value';

			$newCommissionTxt = '['.join(',',$commission).']|'.$hasIncentive.'|'.$usedCommission;
		}


		$returnData = array();
		$returnData['CommissionUsed'] = $CommissionUsed;
		$returnData['CommissionValue'] = $newCommissionTxt;
		$returnData['CommissionIncentive'] = $hasIncentive;
		$returnData['CommissionType'] = $CommissionType;
		$returnData['CommissionCurrency'] = $CommissionCurrency;

		return $returnData;
	}

	function checkUrl($url, $checkUrlCount = 0){
		global $checkUrlCount;
		$trueUrlInfo = getTrueUrl($url);
		$patternReplace = "/window\.location\.replace\((\"|')(.*)\\1/i";
		$patternRefresh = "/<meta[^>]*http-equiv=[\"']refresh[\"'][^>]*?url=([^\"']*)/i";
		$js_url = '';

		if($trueUrlInfo['http_code'] == '200' ){
			if(preg_match($patternReplace,$trueUrlInfo['response'],$result)){
				if (count($result) && strlen($result[2])){
					$js_url = $result[2];
				}
				echo "Replace\t";
			} else if(preg_match($patternRefresh,$trueUrlInfo['response'],$result)){
				if(count($result) && strlen($result[1])){
					$js_url = $result[1];
				}
				echo "Refresh\t";
			} else{
				echo "Location\t";
			}
			$js_url = htmlspecialchars_decode($js_url);
			$js_domain = get_domain($js_url);
			$trueUrlInfoDomain = get_domain($trueUrlInfo['final_url']);
			if ($js_domain == $trueUrlInfoDomain)
				$js_url = '';
			if(strlen($js_url)){
				if($checkUrlCount < 3){
					$checkUrlCount++;
					if(checkUrl($js_url, $checkUrlCount) == "Fail")
						return "Fail";
					else
						return true;
				} else {
					$urlTmp = getTrueUrl($js_url);
					if($urlTmp['http_code'] == '200'){
						echo $urlTmp['final_url'],"\n";
						return true;
					}
					else
						return "Fail";
				}
			} else {
				echo $trueUrlInfo['final_url'],"\n";
				return true;
			}
		} else {
			echo "Fail","\n";
			return "Fail";
		}
	}

	function getTrueUrl($url,$parameters = array()){
		$cookie_name = "/home/bdg/tmp/cookie.cookie";
		if(file_exists($cookie_name))
		{
			unlink($cookie_name);
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER , 1);
		curl_setopt($ch, CURLOPT_HEADER , 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER , array('Upgrade-Insecure-Requests:1'));
		$nobody = isset($parameters['nobody'])?1:0;
		$timeout = isset($parameters['timeout'])?$parameters['timeout']:10;
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION , 1);
		curl_setopt($ch, CURLOPT_NOBODY , $nobody);
		curl_setopt($ch, CURLOPT_TIMEOUT , $timeout);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST , 2);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36");
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_name);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_name);
		$response = curl_exec($ch);
		$finalUrl = strtolower(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
		$infoArr['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$infoArr['fail'] = curl_error($ch);
		curl_close($ch);
		
		// if time out,do it again and set timeout 30s
		if(stripos($infoArr['fail'],'Operation timed out') !== false || ($infoArr['http_code'] >300 && $infoArr['http_code'] < 400))
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER , 1);
			curl_setopt($ch, CURLOPT_HEADER , 1);
			curl_setopt($ch, CURLOPT_NOBODY , $nobody);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION , 1);
			curl_setopt($ch, CURLOPT_TIMEOUT , $timeout);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST , 2);
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36");
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_name);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_name);
			
			$response = curl_exec($ch);
			$finalUrl = strtolower(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
			$infoArr['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$infoArr['fail'] = curl_error($ch);
		}
		
		//url belongs to trade doubler
		if(stripos($finalUrl,'clkuk.tradedoubler.com') !== false)
		{
			$url = "http://clkuk.tradedoubler.com" . getStringBetweenKeywords(getStringBetweenKeywords($response,'<noscript>','</noscript>'),'url=','"');
			echo $url.PHP_EOL;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER , 1);
			curl_setopt($ch, CURLOPT_HEADER , 1);
			curl_setopt($ch, CURLOPT_NOBODY , $nobody);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION , 1);
			curl_setopt($ch, CURLOPT_TIMEOUT , $timeout);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST , 2);
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36");
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_name);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_name);
			
			$response = curl_exec($ch);
			$finalUrl = strtolower(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
			$infoArr['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$infoArr['fail'] = curl_error($ch);
			curl_close($ch);
		}
	
		$infoArr['response'] = $response;
		$infoArr['final_url'] = $finalUrl?$finalUrl:$url;;

		return $infoArr;
	}

	function get_domain($url)
	{
		if(!$url)
			return null;
		$url = strtolower($url);
		$tmpUrl = explode('.',$url);
		if(count($tmpUrl) == 1){
			return null;
		} else 	if(count($tmpUrl) == 2){
			foreach($tmpUrl as $v){
				if($v == '')
					return '';
			}
		}
		if(!preg_match("/^https?:\\/\\//i",$url)){
			$url = "http://".$url;
		}

		$rs = parse_url($url);
		$url = $rs["host"];
		global $objProgram;
		
		$sql = "select Domain from domain_top_level";
		$topDomain_tmp = $objProgram->objMysql->getRows($sql);
		$topDomain = array();
		foreach ($topDomain_tmp as $v)
		{
			$topDomain[] = '\.'.$v['Domain'];
		}
		$country_arr = explode(",", $objProgram->global_c);
		foreach ($country_arr as $country) {
			if ($country) {
				$country = "\." . strtolower($country);
				$topDomain[] = "\.com?" . $country;
				$topDomain[] = "\.org?" . $country;
				$topDomain[] = "\.net?" . $country;
				$topDomain[] = "\.gov?".$country;
				$topDomain[] = "\.edu?".$country;
				$topDomain[] =  $country."\.com";
				$topDomain[] = $country;
			}
		}

		//TODO add judgement of blogspot and wordpress
		if(stristr($url,'blogspot')){
			for($i = 0;$i < count($topDomain);$i++){
				$topDomain[$i] .= '\.blogspot';
			}
		} else if(stristr($url,'wordpress')){
			for($i = 0;$i < count($topDomain);$i++){
				$topDomain[$i] .= '\.wordpress';
			}
		}
		//$exception = array(
		//	'blogspot','wordpress'
		//);
		//foreach($exception as $item)
		//{
		//	if(stristr($url,$item)){
		//		for($i = 0;$i < count($topDomain);$i++){
		//			$topDomain[$i] = '.'.$item.$topDomain[$i];
		//		}
		//		break;
		//	}
		//}

		$pattern = "/([^\.]*)(".implode("|", $topDomain).")$/mi";
		preg_match($pattern, $url, $matches);
		if(count($matches) > 0)
		{
			return $matches[0];
		}else{
			$main_url = $url;
			if(!strcmp(long2ip(sprintf("%u",ip2long($main_url))),$main_url))
			{
				print_r($main_url);
				return $main_url;
			}else{
				$arr = explode(".",$main_url);
				$count=count($arr);
				$endArr = array("com","net","org");//com.cn net.cn
				if (in_array($arr[$count-2],$endArr))
				{
					$domain = $arr[$count-3].".".$arr[$count-2].".".$arr[$count-1];
				}else{
					$domain = $arr[$count-2].".".$arr[$count-1];
				}
				return $domain;
			}
		}
	}

	function checkCategoryExist($categoryExt,$affid)
	{
		global $objProgram;
		$date = date('Y-m-d H:i:s');
		if (empty($categoryExt))
			return '';
		if($affid == 46)
		{
			$categoryExt = explode(';',$categoryExt);
		}
		else
		{
			$categoryExt = explode(',',$categoryExt);
		}
		
		$cate_id = array();
		foreach ($categoryExt as $cate)
		{
			$cate = htmlspecialchars_decode(trim($cate,"-, \t\n\r\0\x0B"));
			if(!empty($cate))
			{
				$sql = "SELECT IdRelated,AffId,UpdateTime FROM category_ext WHERE `Name` = '" . addslashes($cate) . "'";
				$cate_tmp = $objProgram->objMysql->getFirstRow($sql);
				if($cate_tmp)
				{
					$cate_id = explode(',',$cate_tmp['IdRelated']);
					$cate_id = array_unique(array_filter($cate_id));
					//updated recently
					if((strtotime($date)-strtotime($cate_tmp['UpdateTime'])) < 6*3600)
					{
						$affid .= ',' .$cate_tmp['AffId'];
						$affid_arr  = explode(',',$affid);
						$affid_arr = array_unique(array_filter($affid_arr));
						asort($affid_arr);
						$affid = trim(implode(',',$affid_arr),"-, \t\n\r\0\x0B");
					}
//					echo $cate . ':'.$affid.PHP_EOL;
					$sql = "update category_ext set `AffId`='{$affid}',UpdateTime='{$date}' where `Name`='". addslashes($cate)."'";
					$objProgram->objMysql->query($sql);
				}
				else
				{
					$sql = 'SELECT MAX(ID) FROM category_ext';
					$id = $objProgram->objMysql->getFirstRowColumn($sql)+1;
					$sql = "INSERT IGNORE INTO category_ext (`ID`,`Name`,`AffId`,`UpdateTime`) VALUES ('$id','" . addslashes($cate) . "','{$affid}','{$date}')";
					$objProgram->objMysql->query($sql);
				}
			}
		}
		asort($cate_id);
		$cate_id = trim(implode(',',$cate_id),"-, \t\n\r\0\x0B");
		return $cate_id;
	}
	
	function get_store_name($domain,$topDomain)
	{
		preg_match("/([^\.]*)(" . implode("|", $topDomain) . ")$/mi", $domain, $matches);
		if (isset($matches[1]) && strlen($matches[1])) {
			$store_name = $matches[1];
			if(!empty($store_name))
				return $store_name;
			else
				return NULL;
		}
		return NULL;
	}
	
	function findDomainStore($domain, $did)
	{
		global $objProgram, $country_arr, $topDomain,$date;
		$tmp_domain = $domain;
		$cc = "";
		if (strpos($tmp_domain, "/") !== false) {
			$tmp_domain = substr($tmp_domain, 0, strpos($tmp_domain, "/"));
			$cc = substr($domain, strpos($domain, "/") + 1);
			if (strlen($cc) != 2) $cc = "";
		}

		preg_match("/([^\.]*)(" . implode("|", $topDomain) . ")$/mi", $tmp_domain, $matches);
		if (isset($matches[1]) && strlen($matches[1])) {
			$store_name = $matches[1];
			
		
			$country_code = "";
			$ss = $ee = "";
			//check tail
			if (isset($matches[2]) && strlen($matches[2])) {
				$tail_arr = explode(".", $matches[2]);
				$tail = array_pop($tail_arr);
				if (in_array(strtoupper($tail), $country_arr)) {
					$country_code = $tail;
				}
			}
			
			//check head
			if ($matches[0] != $domain) {
				//need check sec domain
				$sub_domain = trim(substr($domain, 0, strripos($domain, $matches[0])), ".");
				$ss = $sub_domain;
				if (in_array(strtoupper($sub_domain), $country_arr)) {
					//meas country
					$country_code = $sub_domain;
				}
			}
			
			$sql = "select CustomName from store_custom where `Domain`='{$domain}' and `IsActive`='Active'";
			$custom_name = $objProgram->objMysql->getFirstRowColumn($sql);
			$store_name = $custom_name == '' ? $store_name : $custom_name ;
			if (!empty($cc)) $country_code = $cc;
			$ee = $country_code;
//			echo PHP_EOL.$domain.PHP_EOL.$store_name.PHP_EOL;

			$sql = "select id, `name`, domains, subdomains, countrycode from store where name = '$store_name'";
			$store_info = $objProgram->objMysql->getFirstRow($sql);
			if (empty($store_info)) {
				$sql = "insert into store(name) value('$store_name')";
				$objProgram->objMysql->query($sql);
				$sql = "select id, `name`, domains, subdomains, countrycode from store where name = '$store_name'";
				$store_info = $objProgram->objMysql->getFirstRow($sql);
			}
			
			$storeid = $store_info['id'];
			if($storeid) {
				$sql = "replace into r_store_domain (storeid, domainid, subdomain, CountryCode,LastUpdateTime) values($storeid, {$did}, '$ss', '$ee','{$date}')";
				$objProgram->objMysql->query($sql);
				$sql = "update domain set DomainName = '$store_name', SubDomain = '$ss', countrycode = '$ee' where id = $did";
				$objProgram->objMysql->query($sql);
			}
		}
	}
	
	function save_image($url,$img_path,$img_name)
	{
		$ch = curl_init ();
		curl_setopt($ch,CURLOPT_CUSTOMREQUEST,'GET' );
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false );
		curl_setopt($ch,CURLOPT_URL,$url );
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION , 1);
		ob_start();
		curl_exec($ch);
		$content = ob_get_contents();
		ob_end_clean ();
		$return_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		curl_close($ch);
		if($return_code == 200)
		{
			$fp= fopen("{$img_path}{$img_name}","a");
			fwrite($fp,$content);
			fclose($fp);
			echo "image {$img_name} download success!".PHP_EOL;
			return true;
		}
		else{
			echo "file {$img_name} not exist on the server:",$url.PHP_EOL;
			return false;
		}
	}
	
	/*
	 * 时间差计算器，返回时间差值
	 *
	 * @access public
	 * @param $start_time   开始时间
	 * @param $end_time     结束时间
	 * @return string
	 */
	function get_time_interval($start_time,$end_time)
	{
		$difference = strtotime($end_time)-strtotime($start_time);
		$d = floor($difference / 3600 / 24);
		$difference -= $d * 3600 * 24;
		$h = floor($difference / 3600);
		$difference -= $h * 3600;
		$m = floor($difference / 60);
		$s = floor($difference % 60);
		if($d < 1)
		{
			if($h < 1)
			{
				if($m < 1)
					return "$s Seconds";
				else
					return "$m Minutes $s Seconds";
			}
			else
				return "$h Hours $m Minutes $s Seconds";
		}
		else
			return "$d Days $h Hours $m Minutes $s Seconds";
	}
	
	function image_download($url,$path,$id,$prefix='')
	{
		$ch = curl_init ();
		curl_setopt($ch,CURLOPT_CUSTOMREQUEST,'GET' );
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false );
		curl_setopt($ch,CURLOPT_URL,$url );
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION , 1);
		ob_start();
		curl_exec($ch);
		$content = ob_get_contents();
		ob_end_clean ();
		$return_info = curl_getinfo($ch);
		curl_close($ch);
		$pattern = "/^image\/(.+)/mi";
		$img_name = mt_rand(100, 999) . $id . mt_rand(100, 999);
		if($return_info['http_code'] == 200)
		{
			preg_match($pattern, $return_info['content_type'], $matches);
			if(!empty($matches))
			{
				$img_name .= '.' . $matches[1];
				if($prefix)
					$img_name = $prefix . '-' .$img_name;
				$fp= fopen("{$path}{$img_name}","a");
				fwrite($fp,$content);
				fclose($fp);
				echo "image {$img_name} download success!".PHP_EOL;
				return $img_name;
			}
			else
			{
				echo "file {$img_name} not a valid image:",$url.PHP_EOL;
				return '';
			}
		}
		else
		{
			echo "file {$img_name} not exist on the server:",$url.PHP_EOL;
			return '';
		}
	}

	function getStringBetweenKeywords($string,$keyword1,$keyword2)
	{
		$st =stripos($string,$keyword1) + strlen($keyword1) -1;
		$ed =stripos($string,$keyword2,$st);
		if(($st===false||$ed===false)||$st>=$ed)
			return '';
		$string=substr($string,($st+1),($ed-$st-1));
		return $string;
	}
	
	function getEncodeId($retry = 0){
	    global $key, $objProgram;
	    $encodeid = '';
	    $encodeid = random(8, $key);
	    $sql = "select encodeid from content_feed_new where encodeid = '{$encodeid}'";
	    $tmp_arr_content = array();
	    $tmp_arr_content = $objProgram->objMysql->getFirstRow($sql);
	
	    $sql = "select encodeid from product_feed where encodeid = '{$encodeid}'";
	    $tmp_arr_product = array();
	    $tmp_arr_product = $objProgram->objMysql->getFirstRow($sql);
	
	    if(count($tmp_arr_content) || count($tmp_arr_product)){
	        $retry++;
	        if($retry < 10){
	            $encodeid = getEncodeId($retry);
	        }else{
	            echo 'warning: retry > 10 , ';
	            exit;
	        }
	    }
	    return $encodeid;
	}
	
	function random($length, $key,$retry=0)
	{
	    $random = '';
	    $pool = '123456789';
	    $pool .= substr(microtime(true), -2);//'1234567890';
	    $pool =  str_replace('0','1',$pool); //replace zero
	    //srand ((double)microtime()*1000000);
	    for($i = 0; $i < $length; $i++)
	    {
	        $random .= substr($pool,(rand()%(strlen ($pool))), 1);
	    }
	    if(strlen($random)<$length){
	        $retry++;
	        if($retry < 5){
	            $random = random($length,$key,$retry);
	        }else{
	            echo 'create random warning: retry > 5 , ';
	            exit;
	        }
	    }  
	    return intval($random);
	}
	
	function get_url_in_site($domain){
		$pattern_illegal = '/^\/(.)+/';
		$pattern = '/^ht{2}ps?:\/{2}/i';
		$urlInfo = getTrueUrl($domain);
		if($urlInfo['http_code'] == '200' && $urlInfo['response'])
		{
			$homepage = $urlInfo['final_url'];
			$dom = new DOMDocument();
			@$dom->loadHTML($urlInfo['response']);
			$xpath = new DOMXPath($dom);
			$hrefs = $xpath->evaluate('/html/body//a');
			unset($dom);
			unset($xpath);
			$urls = array();
			for ($i = 0; $i < $hrefs->length; $i++){
				$href = $hrefs->item($i);
				$url = $href->getAttribute('href');
				unset($href);
				if (preg_match($pattern_illegal, $url))
					$url = trim($homepage,'/') . $url;
	
				if (!preg_match($pattern, $url))
					continue;

				if(stripos(stripslashes($url),stripslashes($domain)) === false || stripos(stripslashes($url),stripslashes($domain)) >= 10)
					continue;
				$urls[] = $url;
			}
			if(!empty($urls))
			{
				$urls = array_unique($urls);
				uasort($urls, 'sort_by_length');
				$urls = array_values($urls);
				foreach ($urls as $url)
				{
					$urlInfo = getTrueUrl($url);
					if($urlInfo['http_code'] == '200')
						return $urlInfo['final_url'];
				}
			}
			return '';
		}
		else
			return '';
	}
	
	function http_build_url($url_arr){
		$new_url = $url_arr['scheme'] . "://".$url_arr['host'];
		if(!empty($url_arr['port']))
			$new_url = $new_url.":".$url_arr['port'];
		$new_url = $new_url . $url_arr['path'];
		if(!empty($url_arr['query']))
			$new_url = $new_url . "?" . $url_arr['query'];
		if(!empty($url_arr['fragment']))
			$new_url = $new_url . "#" . $url_arr['fragment'];
		return $new_url;
	}
	
	/**
	 * @param string $url       - the url to deal with
	 * @param string $domain    - domain in table domain
	 * @return string           - url or empty
	 */
	function removeAffParas($url,$domain)
	{
		global $objProgram;
		$sql = "select distinct KeyWord from affiliate_url_keywords";
		$keywords = $objProgram->objMysql->getRows($sql,'KeyWord');
		$keywords = array_keys($keywords);
		$parse = parse_url(urldecode($url));
		if (isset($parse['query'])) {
			if(stripos($parse['host'],$domain) === false)
			{
				//In this case,make OriginalUrl in content_feed_new empty and IsParaOptimized NO
				return '';
			}
			else
			{
				$paras = explode('&', $parse['query']);
				foreach ($paras as $key=>$para)
				{
					$tmp = explode('=',$para);
					if(in_array($tmp[0],$keywords))
					{
						unset($paras[$key]);
					}
				}
				$parse['query'] = implode('&', $paras);
				//In this case,make IsParaOptimized in content_feed_new ORIGIN
				return http_build_url($parse);
			}
		}
		//In this case,make IsParaOptimized in content_feed_new ORIGIN
		return $url;
	}
	
	function i_array_column($input, $columnKey, $indexKey=null){
	    if(!function_exists('array_column')){
	    	$columnKeyIsNumber = (is_numeric($columnKey))?true:false;
	    	$indexKeyIsNull = (is_null($indexKey))?true :false;
	        $indexKeyIsNumber = (is_numeric($indexKey))?true:false;
	        $result = array();
	        foreach((array)$input as $key=>$row){
	            if($columnKeyIsNumber){
	                $tmp= array_slice($row, $columnKey, 1);
	                $tmp= (is_array($tmp) && !empty($tmp))?current($tmp):null;
	            }else{
	                $tmp= isset($row[$columnKey])?$row[$columnKey]:null;
	            }
	            if(!$indexKeyIsNull){
	                if($indexKeyIsNumber){
	                  $key = array_slice($row, $indexKey, 1);
	                  $key = (is_array($key) && !empty($key))?current($key):null;
	                  $key = is_null($key)?0:$key;
	                }else{
	                  $key = isset($row[$indexKey])?$row[$indexKey]:0;
	                }
	            }
	            $result[$key] = $tmp;
	        }
	        return $result;
	    }else{
	        return array_column($input, $columnKey, $indexKey);
	    }
	}
	?>