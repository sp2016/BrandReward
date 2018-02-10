<?php

	include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
	include_once (dirname(dirname(__FILE__))."/func/func.php");

	$objProgram = New Program();

	$sql = "SELECT b.AffId,b.TplDeepUrlTpl FROM wf_aff a LEFT JOIN aff_url_pattern b ON a.ID = b.AffId WHERE a.IsActive = 'YES' AND b.SupportDeepUrlTpl = 'YES' AND b.AffId NOT IN (160,191,223,237,578,639,652,656) ;";


	$dealing_aff = $objProgram->objMysql->getRows($sql);
	$checkTime = date("Y-m-d H:i:s");
	$affId = '';
	$inInAff = '';
	$homePage = '';
	foreach($dealing_aff as $data){
		$tableName = "affiliate_links_".$data['AffId'];
		echo "table:$tableName\n";
		//if table exist
		$sql = "SELECT `TABLE_NAME` FROM `INFORMATION_SCHEMA`.`TABLES` WHERE `TABLE_SCHEMA` = 'pendinglinks' AND `TABLE_NAME` = '$tableName';";
		if($objProgram->objPendingMysql->getRows($sql)){
			//if column SupportDeepUrlTpl exist
			$sql = "SELECT COLUMN_NAME FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE TABLE_SCHEMA='pendinglinks' AND TABLE_NAME='$tableName' AND COLUMN_NAME='SupportDeepUrlTpl';";
			if(!$objProgram->objPendingMysql->getRows($sql)){
				$sql = "ALTER TABLE $tableName ADD SupportDeepUrlTpl TEXT";
				$objProgram->objPendingMysql->query($sql);
			}

			//if column IsActive exist
			$sql = "SELECT COLUMN_NAME FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE TABLE_SCHEMA='pendinglinks' AND TABLE_NAME='$tableName' AND COLUMN_NAME='IsActive';";
			if(!$objProgram->objPendingMysql->getRows($sql)){
				$sql = "ALTER TABLE $tableName ADD IsActive TEXT";
				$objProgram->objPendingMysql->query($sql);
			}
			if(isset($result))
				unset($result);
			if(isset($sql))
				unset($sql);

			$sql = "SELECT AffMerchantId,AffLinkId,LinkStartDate,LinkEndDate,LinkAffUrl,LastCheckTime,HttpCode FROM $tableName WHERE LinkAffUrl != '' AND LinkAffUrl IS NOT NULL AND (LinkEndDate ='0000-00-00 00:00:00' OR LinkEndDate > '$checkTime') limit 100;";
			$result = $objProgram->objPendingMysql->getRows($sql);
			foreach($result as $record){
				echo $record['AffMerchantId']."\t".$record['AffLinkId']."\t".$record['LinkAffUrl']."\t".$record['HttpCode']."\n";
				//$record['LinkAffUrl'] = "http://www.tkqlhce.com/click-2567387-10435229-1368190079000";
				//$record['LinkAffUrl'] = "http://www.tracfone.com/e_store.jsp?task=buyphone&lang=en";
				$urlInfo = getTrueUrl($record['LinkAffUrl']);
				var_dump($urlInfo);
				if($urlInfo['http_code'] == 0 || $urlInfo['http_code'] == 404){
					$sql = "UPDATE $tableName SET HttpCode=" . $urlInfo['http_code'] . ",IsActive = 'NO',SupportDeepUrlTpl = 'NO',LastCheckTime = '". date("Y-m-d H:i:s") . "' WHERE AffMerchantId = '" . $record['AffMerchantId'] ."' AND AffLinkId = '".$record['AffLinkId']."'";
					$objProgram->objPendingMysql->query($sql);
					continue;
				} else {
					if($inInAff != $record['AffMerchantId'] || $affId != $data['AffId']){
						$inInAff = $record['AffMerchantId'];
						$affId = $data['AffId'];
						$sql = "SELECT Homepage FROM program WHERE IdInAff = '".$record['AffMerchantId'] ."' AND AffId = '".$data['AffId'] ."'";
						$homePageInfo = $objProgram->objMysql->getFirstRow($sql);
						var_dump($homePageInfo);
						$pattern = '/(^h|H)(t|T){2}(p|P)(s|S)?(\:)(\/){2}/i';
						$homePage = preg_replace($pattern,'',$homePageInfo['Homepage']);
					}
					if($urlInfo['http_code'] != 200){
						$urlJumpInfo = getTrueUrl($urlInfo['final_url']);
						unset($urlInfo);
						$urlInfo = $urlJumpInfo;
						if($urlInfo['http_code'] != 200){
							$sql = "UPDATE $tableName SET HttpCode=" . $urlInfo['http_code'] . ",IsActive = 'NO',SupportDeepUrlTpl = 'NO',LastCheckTime = '". date("Y-m-d H:i:s") . "' WHERE AffMerchantId = '" . $record['AffMerchantId'] ."' AND AffLinkId = '".$record['AffLinkId']."'";
							$objProgram->objPendingMysql->query($sql);
							continue;
						}
					}

					if(stripos(stripslashes($urlInfo['final_url']),stripslashes($homePage)) === false){
						$patternReplace = "/window\.location\.replace\((\"|')(.*)\\1/i";
						$patternRefresh = "/<meta[^>]*http-equiv=[\"']refresh[\"'][^>]*?url=([^\"']*)/i";
						$js_url = '';
						if(preg_match($patternReplace,$urlInfo['response'],$jsResult)){
							if (count($jsResult) && strlen($jsResult[2])){
								$js_url = $jsResult[2];
							}
						}else if(preg_match($patternRefresh,$urlInfo['response'],$jsResult)){
							if(count($jsResult) && strlen($jsResult[1])){
								$js_url = $jsResult[1];
							}
						}
						$js_url = htmlspecialchars_decode($js_url);
						if(strlen($js_url)){
							$urlInfo['final_url'] = $js_url;
						}
					}
					if(stripos(stripslashes($urlInfo['final_url']),stripslashes($homePage)) === false){
						$urlDefaultInfo = getTrueUrl($urlInfo['final_url']);
						$urlDefault = $urlDefaultInfo['final_url'];
					} else {
						$urlDefaultInfo = $urlInfo;
						$urlDefault = $urlDefaultInfo['final_url'];
					}
					if(stripos(stripslashes($urlDefault),stripslashes($homePage)) === false){
						$sql = "UPDATE $tableName SET HttpCode=" . $urlDefaultInfo['http_code'] . ",IsActive = 'NO',SupportDeepUrlTpl = 'NO',LastCheckTime = '". date("Y-m-d H:i:s") . "' WHERE AffMerchantId = '" . $record['AffMerchantId'] ."' AND AffLinkId = '".$record['AffLinkId']."'";
						$objProgram->objPendingMysql->query($sql);
						continue;
					}

					if(isset($pattern))
						unset($pattern);
					$pattern = array("AffId" => $data['AffId'],
						"IdInAff" => $record['AffMerchantId'],
						"AffDefaultUrl" => $record['LinkAffUrl']
					);
					$newTplDeepUrlTpl = $objProgram->getUrlByTpl($data['TplDeepUrlTpl'],$pattern);


					$innerUrlGet = get_inner_url($urlDefaultInfo['final_url'],$homePage);
					$innerUrlBeforeInfo = getTrueUrl($innerUrlGet);
					$innerUrlBefore = $innerUrlBeforeInfo['final_url'];
					$innerTplUrl = getDeepUrl($innerUrlBefore, $newTplDeepUrlTpl);
					$innerUrlAfterInfo = getTrueUrl($innerTplUrl);
					$innerUrlAfter = $innerUrlAfterInfo['final_url'];

					if($innerUrlAfterInfo['http_code'] == 0 || $innerUrlAfterInfo['http_code'] == 404){
						$sql = "UPDATE $tableName SET HttpCode=" . $innerUrlAfterInfo['http_code'] . ",IsActive = 'YES',SupportDeepUrlTpl = 'NO',LastCheckTime = '". date("Y-m-d H:i:s") . "' WHERE AffMerchantId = '" . $record['AffMerchantId'] ."' AND AffLinkId = '".$record['AffLinkId']."'";
						$objProgram->objPendingMysql->query($sql);
						continue;
					}
					if($urlInfo['http_code'] != 200){
						$urlJumpInfo = getTrueUrl($innerUrlAfterInfo['final_url']);
						unset($innerUrlAfterInfo);
						$innerUrlAfterInfo = $urlJumpInfo;
						if($innerUrlAfterInfo['http_code'] != 200){
							$sql = "UPDATE $tableName SET HttpCode=" . $innerUrlAfterInfo['http_code'] . ",IsActive = 'YES',SupportDeepUrlTpl = 'NO',LastCheckTime = '". date("Y-m-d H:i:s") . "' WHERE AffMerchantId = '" . $record['AffMerchantId'] ."' AND AffLinkId = '".$record['AffLinkId']."'";
							$objProgram->objPendingMysql->query($sql);
							continue;
						}
					}
					if(stripos(stripslashes($innerUrlAfterInfo['final_url']),stripslashes($homePage)) === false){
						$patternReplace = "/window\.location\.replace\((\"|')(.*)\\1/i";
						$patternRefresh = "/<meta[^>]*http-equiv=[\"']refresh[\"'][^>]*?url=([^\"']*)/i";
						$js_url = '';
						if(preg_match($patternReplace,$innerUrlAfterInfo['response'],$jsResult)){
							if (count($jsResult) && strlen($jsResult[2])){
								$js_url = $jsResult[2];
							}
						}else if(preg_match($patternRefresh,$innerUrlAfterInfo['response'],$jsResult)){
							if(count($jsResult) && strlen($jsResult[1])){
								$js_url = $jsResult[1];
							}
						}
						$js_url = htmlspecialchars_decode($js_url);
						if(strlen($js_url)){
							$innerUrlAfterInfo['final_url'] = $js_url;
						}
					}
					if(stripos(stripslashes($innerUrlAfterInfo['final_url']),stripslashes($homePage)) === false){
						$urlTmpInfo = getTrueUrl($innerUrlAfterInfo['final_url']);
						$innerUrlAfterInfo = $urlTmpInfo;
					}
					if(stripos(stripslashes($innerUrlAfterInfo['final_url']),stripslashes($homePage)) === false){
						$sql = "UPDATE $tableName SET HttpCode=" . $innerUrlAfterInfo['http_code'] . ",IsActive = 'YES',SupportDeepUrlTpl = 'NO',LastCheckTime = '". date("Y-m-d H:i:s") . "' WHERE AffMerchantId = '" . $record['AffMerchantId'] ."' AND AffLinkId = '".$record['AffLinkId']."'";
						$objProgram->objPendingMysql->query($sql);
						continue;
					}
					$innerUrlAfter = $innerUrlAfterInfo['final_url'];
					if($innerUrlBefore == $innerUrlAfter){
						$sql = "UPDATE $tableName SET HttpCode=" . $innerUrlAfterInfo['http_code'] . ",IsActive = 'YES',SupportDeepUrlTpl = 'YES',LastCheckTime = '". date("Y-m-d H:i:s") . "' WHERE AffMerchantId = '" . $record['AffMerchantId'] ."' AND AffLinkId = '".$record['AffLinkId'] . "'";
					} else {
						$sql = "UPDATE $tableName SET HttpCode=" . $innerUrlAfterInfo['http_code'] . ",IsActive = 'YES',SupportDeepUrlTpl = 'NO',LastCheckTime = '". date("Y-m-d H:i:s") . "' WHERE AffMerchantId = '" . $record['AffMerchantId'] ."' AND AffLinkId = '".$record['AffLinkId']."'";
					}
					$objProgram->objPendingMysql->query($sql);
				}
			}
		} else {
			echo "Table $tableName does not exist;\n";
		}
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

		$result = str_ireplace('[SUBTRACKING]', '', $result);
		return $result;
	}
