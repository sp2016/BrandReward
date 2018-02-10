<?php
	include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
	include_once(dirname(dirname(__FILE__)) . "/func/func.php");
	
	$cmd = 'ps aux | grep check_outbound_url.php | grep -v grep -c';
	$processCount = trim(exec($cmd));
	if(is_numeric($processCount) && $processCount > 1)
	{
		mydie('check_outbound_url is running now.Stopped!');
	}
	
	$objProgram = New Program();
	
	$count = $count_error = $count_default_url_error = $count_default_url_tpl_error = $count_default_url_tpl_no_url = $count_no_tpl = $count_tpl_no_url = $count_empty_domain = $count_tpl_error = $error_to_ok = 0;
	$i = 0;
	$startTime = date("Y-m-d H:i:s");
	echo "Start @ $startTime" . PHP_EOL;
	$checkTime = date("Y-m-d H:i:s");
	$validTime = date("Y-m-d H:i:s",strtotime("-1 month"));
	while(true) {
		$sql = "select distinct a.`PID`,a.`DID`,a.`AffiliateDefaultUrl`,a.`DeepUrlTemplate`,b.`AffId`,b.`IdInAff`,b.`AffDefaultUrl`,b.Domain,d.`TplDeepUrlTpl`,d.`SupportDeepUrlTpl`,d.`TplAffDefaultUrl` from domain_outgoing_default_other a inner join program_intell b on a.`PID`=b.`ProgramID` left join check_outbound_log c on a.`DID`=c.`DID` and a.`PID`=c.`PID` inner join aff_url_pattern d on b.`AffId` = d.`AffId` where a.IsFake='NO' and ( c.`UpdateTime`<='{$validTime}' or c.`UpdateTime` is null) and ( c.`OverDate` = 'NO' or c.`OverDate` is null) and b.`AffId` != 1  order by a.`ID` asc limit $i,1000";
		$domain_arr = $objProgram->objMysql->getRows($sql);
		$i += 1000;
		if(empty($domain_arr))
			break;
		
		foreach ($domain_arr as $domain_info) {
			$count++;
			$error_flag = false;
			
			//get the first domain in domain list
			preg_match('/^.+?\s/', $domain_info['Domain'], $matches);
			if (!empty($matches))
				$domain = $matches[0];
			else
				$domain = $domain_info['Domain'];
			$idinaff = addslashes($domain_info['IdInAff']);
			
			if (empty($domain)) {
				echo "Error:\tEmpty domain(homepage)." . PHP_EOL;
				$count_empty_domain++;
				$sql = "insert into check_outbound_log (PID,Affid,UrlOrTpl,ErrorType,UpdateTime,`DID`) values ('{$domain_info['PID']}', '{$domain_info['AffId']}' ,'{$domain_info['Domain']}','DomainNull','{$checkTime}','{$domain_info['DID']}')";
				$objProgram->objMysql->query($sql);
				continue;
			}
			
			$network_default_url = empty($domain_info['AffDefaultUrl']) ? $domain_info['AffiliateDefaultUrl'] : $domain_info['AffDefaultUrl'];
			
			//default url is deep tpl
			if (preg_match('/\[(PURE_DEEPURL|DEEPURL|DOUBLE_ENCODE_DEEPURL|URI|ENCODE_URI|DOUBLE_ENCODE_URI)\]/', $network_default_url)) {
				//Default url is deep tpl,pick url from homepage
				$url_inner = get_url_in_site($domain);
				if (empty($url_inner)) {
					//Error,DefaultAffUrlTplNoUrl-No useful url in homepage to use!
					$count_default_url_tpl_no_url++;
					$error_flag = true;
					$sql = "insert into check_outbound_log (PID,Affid,UrlOrTpl,ErrorType,UpdateTime,`DID`) values ('{$domain_info['PID']}', '{$domain_info['AffId']}','{$network_default_url}','DefaultAffUrlTplNoUrl','{$checkTime}','{$domain_info['DID']}')";
					$objProgram->objMysql->query($sql);
				} else {
					$innerTplUrl = getDeepUrl($url_inner, $domain_info['DeepUrlTemplate']);
					$url_inner_after = getTrueUrl($innerTplUrl)['final_url'];
					if (stripos($url_inner_after, $domain) === false) {
						//need to replace
						//Error,Deep url tpl is Error
						$error_flag = true;
						$count_default_url_tpl_error ++;
						$sql = "insert into check_outbound_log (PID,Affid,UrlOrTpl,Origin,Dealt,ErrorType,UpdateTime,`DID`) values ('{$domain_info['PID']}', '{$domain_info['AffId']}','{$network_default_url}','{$url_inner}','{$url_inner_after}','DefaultAffUrlTplError','{$checkTime}','{$domain_info['DID']}')";
						$objProgram->objMysql->query($sql);
					}
					else
					{
						$sql = "insert into check_outbound_log (PID,Affid,ErrorType,UpdateTime,`DID`) values ('{$domain_info['PID']}', '{$domain_info['AffId']}','DefaultAffUrlTplOK','{$checkTime}','{$domain_info['DID']}')";
						$objProgram->objMysql->query($sql);
					}
				}
			} else {
				//check outbound default url
				$urlInfo = getTrueUrl($network_default_url);
				if ($urlInfo['http_code'] != 200) {
					if(stripos($urlInfo['final_url'],$domain) !== false)
					{
						//Error,Default url may can not visit
						$sql = "insert into check_outbound_log (PID,Affid,UrlOrTpl,ErrorType,UpdateTime,HttpCode,`DID`) values ('{$domain_info['PID']}', '{$domain_info['AffId']}','{$network_default_url}','DefaultAffUrlWarning','{$checkTime}','{$urlInfo['http_code']}','{$domain_info['DID']}')";
						$objProgram->objMysql->query($sql);
					}
					else
					{
						//Error,Default url can not visit
						$sql = "insert into check_outbound_log (PID,Affid,UrlOrTpl,ErrorType,UpdateTime,HttpCode,`DID`) values ('{$domain_info['PID']}', '{$domain_info['AffId']}','{$network_default_url}','DefaultAffUrlError','{$checkTime}','{$urlInfo['http_code']}','{$domain_info['DID']}')";
						$objProgram->objMysql->query($sql);
						$error_flag = true;
						$count_default_url_error ++;
					}
				}
				else
				{
					$sql = "insert into check_outbound_log (PID,Affid,ErrorType,UpdateTime,`DID`) values ('{$domain_info['PID']}', '{$domain_info['AffId']}','DefaultAffUrlOK','{$checkTime}','{$domain_info['DID']}')";
						$objProgram->objMysql->query($sql);
				}
			}
			
			//check tpl
			if ($domain_info['SupportDeepUrlTpl'] == 'YES') {
				//DeepUrlTemplate exist or not
				if (!empty($domain_info['DeepUrlTemplate'])) {
					$url_inner = get_url_in_site($domain);
					if (empty($url_inner)) {
						//Error,SupportDeepUrlTpl-No useful url in homepage to use!
						$count_tpl_no_url++;
						$error_flag = true;
						$sql = "insert into check_outbound_log (PID,Affid,UrlOrTpl,ErrorType,UpdateTime,`DID`) values ('{$domain_info['PID']}', '{$domain_info['AffId']}','{$domain}','TplNoUrl','{$checkTime}','{$domain_info['DID']}')";
						$objProgram->objMysql->query($sql);
					} else {
						$innerTplUrl = getDeepUrl($url_inner, $domain_info['DeepUrlTemplate']);
						$url_inner_after = getTrueUrl($innerTplUrl)['final_url'];
						if (stripos($url_inner_after, $domain) === false) {
							//need to replace
							//Error,Deep url tpl is Error
							$error_flag = true;
							$count_tpl_error ++;
							$sql = "insert into check_outbound_log (PID,Affid,UrlOrTpl,Origin,Dealt,ErrorType,UpdateTime,`DID`) values ('{$domain_info['PID']}', '{$domain_info['AffId']}','{$domain_info['DeepUrlTemplate']}','{$url_inner}','{$url_inner_after}','TplError','{$checkTime}','{$domain_info['DID']}')";
							$objProgram->objMysql->query($sql);
						}
						else
						{
							$sql = "insert into check_outbound_log (PID,Affid,ErrorType,UpdateTime,`DID`) values ('{$domain_info['PID']}', '{$domain_info['AffId']}','TplOK','{$checkTime}','{$domain_info['DID']}')";
							$objProgram->objMysql->query($sql);
						}
					}
				} else {
					//Error,Tpl is empty
					$sql = "insert into check_outbound_log (PID,Affid,UrlOrTpl,ErrorType,UpdateTime,`DID`) values ('{$domain_info['PID']}','{$domain_info['AffId']}','{$domain}','TplNoTpl','{$checkTime}','{$domain_info['DID']}')";
					$objProgram->objMysql->query($sql);
					$count_no_tpl++;
					$error_flag = true;
				}
			}
			if ($error_flag)
				$count_error++;
		}
	}
	
	$sql = "select `ID`,UrlOrTpl from check_outbound_log where ErrorType in ('DefaultAffUrlError','DefaultAffUrlWarning') and Correct in ('Unknown','YES')";
	$to_deal = $objProgram->objMysql->getRows($sql);
	$checkTime = date("Y-m-d H:i:s");
	foreach ($to_deal as $value)
	{
		$urlInfo = getTrueUrl($value['UrlOrTpl']);
		//check outbound default url
		if($urlInfo['http_code'] == 200)
		{
			$error_to_ok ++;
			$sql = "update check_outbound_log set `Correct`='Auto',ErrorType='DefaultAffUrlOK',UrlOrTpl='',HttpCode=200,UpdateTime='{$checkTime}' where `ID`='{$value['ID']}'";
			$objProgram->objMysql->query($sql);
		}
	}
	
	$sql = "update check_outbound_log set OverDate='YES' where UpdateTime<='{$validTime}'";
	$objProgram->objMysql->query($sql);
	
	$end_time = date('Y-m-d H:i:s');
	$use_time = get_time_interval($startTime, $end_time);
	echo "End @ $end_time" . PHP_EOL . "Cost $use_time" . PHP_EOL . "\tTotal:$count\n\tError:$count_error\n\tNo tpl:$count_no_tpl\n\tTpl Error:$count_tpl_error\n\tTpl no url:$count_tpl_no_url\n\tEmpty domain:$count_empty_domain\n\tDefault url error:$count_default_url_error\n\tDefault url tpl error:$count_default_url_tpl_error\n\tDefault url tpl no url:$count_default_url_tpl_no_url\n\tBack to OK:$error_to_ok" . PHP_EOL;
	
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
	
	function sort_by_length($a, $b)
	{
		return (strlen($a) <= strlen($b)) ? 1 : -1;
	}