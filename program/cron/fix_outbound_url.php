<?php
	include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
	include_once(dirname(dirname(__FILE__)) . "/func/func.php");
	
	$objProgram = New Program();
	$checkTime = date("Y-m-d H:i:s");
	echo "start @ $checkTime".PHP_EOL;
	
	$sql = "select a.*,b.IdInAff,b.Domain from check_outbound_log a inner join program_intell b on a.PID = b.ProgramId where a.ErrorType='DefaultAffUrlError' and a.Alternative is null and a.Correct in ('Unknown','YES') and a.`OverDate`='NO'";
	$data = $objProgram->objMysql->getRows($sql);
	foreach($data as $datum)
	{
		echo $datum['PID'] . "\t";
		$table = 'affiliate_links_' . $datum['Affid'];
		$affMerchantId = addslashes($datum['IdInAff']);
		$sql = "select table_name,table_schema from information_schema.tables where table_name ='{$table}'";
		$table_exist = $objProgram->objPendingMysql->getFirstRow($sql);
		if(!empty($table_exist)) {
			$sql = "select LinkAffUrl from $table where AffMerchantId = '{$affMerchantId}' and LinkAffUrl != '' and IsActive='YES' limit 50";
			$link_data = $objProgram->objPendingMysql->getRows($sql);
			foreach ($link_data as $link_datum) {
				
				preg_match('/^.+?\s/', $datum['Domain'], $matches);
				if (!empty($matches))
					$domain = $matches[0];
				else
					$domain = $datum['Domain'];
				$urlInfo = getTrueUrl($link_datum['LinkAffUrl']);
				
//				if ($urlInfo['http_code'] == 200 && stripos($urlInfo['final_url'], $domain) !== false) {
				if ($urlInfo['http_code'] == 200) {
					$sql = "update check_outbound_log set Alternative = '{$link_datum['LinkAffUrl']}' where ID='{$datum['ID']}'";
					echo "find one";
					$objProgram->objMysql->query($sql);
					break;
//				$sql = "select TplDeepUrlTpl,TplDeepUrlTpl,SupportDeepUrlTpl,TplAffDefaultUrl from aff_url_pattern where AffId='{$datum['AffId']}'";
//				$info = $objProgram->objMysql->getFirstRow($sql);
//				$pattern_arr = array("AffId" => $domain['AffId'],"IdInAff" => $link_datum['AffMerchantId'],"AffDefaultUrl" => $link_datum['LinkAffUrl']);
//				$dealt = $objProgram->getUrlByTpl($info['TplAffDefaultUrl'], $pattern_arr);


//				TODO should replace deep url tpl
//							$pattern_arr = array("AffId" => $domain['AffId'],"IdInAff" => $idinaff,"AffDefaultUrl" => $domain['AffiliateDefaultUrl']);
//							$dealt = $objProgram->getUrlByTpl($info['TplDeepUrlTpl'], $pattern_arr);
//							$sql = "insert into `check_outbound_log` (`PID`, `DeepUrlTemplate`, `Target`, `Dealt`,`UrlBefore`,`UrlAfter`,`Homepage`) values ('{$domain['PID']}', '{$domain['DeepUrlTemplate']}', '{$domain['AffiliateDefaultUrl']}', '{$dealt}','{$innerUrlBefore}','{$innerUrlAfter}','{$domain['Homepage']}')";
//							$objProgram->objMysql->query($sql);
				}
			}
		}
		echo PHP_EOL;
	}
	$end_time = date('Y-m-d H:i:s');
	$use_time = get_time_interval($checkTime,$end_time);
	echo PHP_EOL."End @ $end_time,Cost $use_time".PHP_EOL;
	
	
	function getDeepUrl($strDeepUrl, $strDeepTpl) {
		$result = $strDeepTpl;
		$mark_and = '&';
		$mark_que = '?';
		$has_deep_mark = false;
	
		if (preg_match('/(.*)\[(PURE_DEEPURL|DEEPURL|DOUBLE_ENCODE_DEEPURL|URI|ENCODE_URI|DOUBLE_ENCODE_URI)\](\[\?\|&\])*/', $result, $m)) {
			preg_match('/^http(s)?:\/\/[^\/]+(\/)?(.*)/', $strDeepUrl, $q);
			if(isset($m[3]))
				$has_deep_mark = $m[3] != ''? true : $has_deep_mark;
			switch ($m[2]) {
				case 'PURE_DEEPURL':
					$result = str_ireplace('[PURE_DEEPURL]', $strDeepUrl, $result);
					break;
				case 'DEEPURL':
					$result = str_ireplace('[DEEPURL]', ($m[1] == ''? $strDeepUrl: urlencode($strDeepUrl)), $result);
					if (isset($m[3]) && $m[3] == '[?|&]' && $m[1] != '') {
						$mark_and = urlencode($mark_and);
						$mark_que = urlencode($mark_que);
					}
					break;
				case 'DOUBLE_ENCODE_DEEPURL':
					$result = str_ireplace('[DOUBLE_ENCODE_DEEPURL]', ($m[1] == ''? $strDeepUrl : urlencode(urlencode($strDeepUrl))), $result);
					if (isset($m[3]) && $m[3] == '[?|&]' && $m[1] != '') {
						$mark_and = urlencode(urlencode($mark_and));
						$mark_que = urlencode(urlencode($mark_que));
					}
					break;
				case 'URI':
					$result = preg_replace('/([^:])\/{2,}/', '\1/', str_ireplace('[URI]', '/'.(isset($q[3]) && $q[3] != ''? $q[3] : ''), $result));
					break;
				case 'ENCODE_URI':
					$result = preg_replace('/([^:])\/{2,}/', '\1/',  str_ireplace('[ENCODE_URI]', urlencode('/'.(isset($q[3]) && $q[3] != ''? $q[3] : '')), $result));
					if (isset($m[3]) && $m[3] == '[?|&]' && $m[1] != '') {
						$mark_and = urlencode($mark_and);
						$mark_que = urlencode($mark_que);
					}
					break;
				case 'DOUBLE_ENCODE_URI':
					$result = preg_replace('/([^:])\/{2,}/', '\1/',  str_ireplace('[DOUBLE_ENCODE_URI]', urlencode(urlencode('/'.(isset($q[3]) && $q[3] != ''? $q[3] : ''))), $result));
					if (isset($m[3]) && $m[3] == '[?|&]' && $m[1] != '') {
						$mark_and = urlencode(urlencode($mark_and));
						$mark_que = urlencode(urlencode($mark_que));
					}
					break;
			}
		}

		$m = array();
		if (preg_match('/(.*)(\[\?\|&\].*)/', $result, $m)) { //&& $start_w_tpl
			if ($has_deep_mark) {
				$m[1] = $strDeepUrl;
			}

			if (preg_match('/[\?&][^&]+=[^&]*/U', $m[1]))
				$result = str_replace('[?|&]', $mark_and, $result);
			else
				$result = str_replace('[?|&]', $mark_que, $result);
		}

		$replace = array(
			'[SUBTRACKING]' => '',
			'[SITEIDINAFF]' => '8030429',
		);
		
		$result = strtr($result,$replace);
		return $result;
	}
	
	function get_inner_url($urlPending,$homePage,$which = 1){
		$image = array( "jpg" , "jpeg" , "png" , "gif" );
		$pattern_illegal = '{^\/(.)+(\/)$}';
		$pattern = '/(^h|H)(t|T){2}(p|P)(s|S)?(\:)(\/){2}/i';
		$trueUrlInfo = getTrueUrl($urlPending);
		$dom = new DOMDocument();
		@$dom->loadHTML($trueUrlInfo['response']);
		$xpath = new DOMXPath($dom);
		$hrefs = $xpath->evaluate('/html/body//a');
		unset($dom);
		unset($xpath);
		$flag = false;
		$url = '';
		$count = 0;
		for ($i = 0; $i < $hrefs->length; $i++){
			$href = $hrefs->item($i);
			$url = $href->getAttribute('href');
			unset($href);

			//去除img
			$ext = explode(".",$url);
			$ext = strtolower(end($ext));
			if( !empty($ext) && in_array($ext , $image)){
				continue;
			}

			if (preg_match($pattern_illegal, $url)){
				continue;
			}

			if (!preg_match($pattern, $url)){
				continue;
			}

			if(stripos(stripslashes($url),stripslashes($homePage)) === false)
				continue;

			if(stripos(stripslashes($url),stripslashes($urlPending))){
				$count ++;
				if($count >= $which){
					$flag = true;
					break;
				}else{
					continue;
				}
			} else {
				$flag = false;
			}
		}
		if(!$flag){
			$url = $trueUrlInfo['final_url'];
		}

		return $url;
	}