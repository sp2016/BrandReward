<?php

	class CheckTools
	{
		function __construct($programId){
			include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
			include_once(dirname(dirname(__FILE__)) . "/func/func.php");
			$this->objProgram = new Program();
			$this->debug = false;
			$this->table_schema='pendinglinks';
			$this->programId = $programId;
			$this->checkTime = date("Y-m-d H:i:s");
			$this->pattern = '/(^h|H)(t|T){2}(p|P)(s|S)?(\:)(\/){2}/i';

		}

		function setDebug()
		{
			$this->debug = true;
			$this->table_schema = 'mcsky_pendinglinks_base';
		}

		function checkDatabase()
		{
			$sql = "SELECT `TABLE_NAME` FROM `INFORMATION_SCHEMA`.`TABLES` WHERE `TABLE_SCHEMA` = '$this->table_schema' AND `TABLE_NAME` LIKE 'affiliate_links_%';";
			$tableArr = $this->objMysql->objPendingMysql->getRows ($sql , 'TABLE_NAME');
			$tableArr = array_keys ($tableArr);
			foreach ($tableArr as $item)
			{
				printf("Start to modify table %-30s",$item);
				$sql = "SELECT COLUMN_NAME FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE TABLE_SCHEMA='$this->table_schema' AND TABLE_NAME='$item' AND COLUMN_NAME='SupportDeepUrlTpl';";
				if (!$this->objMysql->objPendingMysql->getRows ($sql))
				{
					$sql = "ALTER TABLE `$item` ADD SupportDeepUrlTpl ENUM('YES','NO')";
					$this->objMysql->objPendingMysql->query ($sql);
				} else
				{
					$sql = "ALTER TABLE `$item` CHANGE `SupportDeepUrlTpl` `SupportDeepUrlTpl` ENUM('YES','NO')";
					$this->objMysql->objPendingMysql->query ($sql);
				}

				$sql = "SELECT COLUMN_NAME FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE TABLE_SCHEMA='$this->table_schema' AND TABLE_NAME='$item' AND COLUMN_NAME='IsActive';";
				if (!$this->objMysql->objPendingMysql->getRows ($sql))
				{
					$sql = "ALTER TABLE `$item` ADD IsActive ENUM('YES','NO')";
					$this->objMysql->objPendingMysql->query ($sql);
				} else
				{
					$sql = "ALTER TABLE `$item` CHANGE IsActive IsActive ENUM('YES','NO')";
					$this->objMysql->objPendingMysql->query ($sql);
				}
				echo "Modify OK!\n";
			}
			echo "Modify finished!\n";
		}

		function check_deepurl_support()
		{
			$sql = "SELECT b.AffId,c.`IdInAff`,b.TplDeepUrlTpl FROM wf_aff a LEFT JOIN aff_url_pattern b ON a.ID = b.AffId INNER JOIN program_intell c ON c.`AffId`=b.`AffId` WHERE a.IsActive = 'YES' AND b.SupportDeepUrlTpl = 'YES'  AND c.`ProgramId`=$this->programId;";
			$data = $this->objProgram->objMysql->getFirstRow($sql);
			if(empty($data))
			{
				echo "Invalid program id: $this->programId.\n";
				return;
			}
			else
			{
				$tableName = "affiliate_links_".$data['AffId'];
				echo "table:$tableName\n";
				$sql = "SELECT `TABLE_NAME` FROM `INFORMATION_SCHEMA`.`TABLES` WHERE `TABLE_SCHEMA` = '$this->table_schema' AND `TABLE_NAME` = '$tableName';";

				if($this->objProgram->objPendingMysql->getRows($sql))
				{
					//if table exist
					$idinaff = $data['IdInAff'];
					$sql = "SELECT AffMerchantId,AffLinkId,LinkStartDate,LinkEndDate,LinkAffUrl,LastCheckTime,HttpCode FROM $tableName WHERE LinkAffUrl != '' AND LinkAffUrl IS NOT NULL AND (LinkEndDate ='0000-00-00 00:00:00' OR LinkEndDate > '$this->checkTime') AND AffMerchantId='$idinaff';";
					$result = $this->objProgram->objPendingMysql->getRows($sql);
					$process_count = 0;
					foreach($result as $record)
					{
						$time = date("Y-m-d H:i:s");
						printf("Current Status:%-4s\t%s\t%-15s\t%-80s\t%-3s\n",$process_count++,$time,$record['AffLinkId'],$record['LinkAffUrl'],$record['HttpCode']);
						$urlInfo = getTrueUrl($record['LinkAffUrl']);
						if($urlInfo['http_code'] == 0 || $urlInfo['http_code'] == 404){
							$sql = "UPDATE $tableName SET HttpCode=" . $urlInfo['http_code'] . ",IsActive = 'NO',SupportDeepUrlTpl = 'NO',LastCheckTime = '". date("Y-m-d H:i:s") . "' WHERE AffMerchantId = '" . $record['AffMerchantId'] ."' AND AffLinkId = '".$record['AffLinkId']."'";
							$this->objProgram->objPendingMysql->query($sql);
							continue;
						}
						else
						{
							$sql = "SELECT Homepage FROM program WHERE IdInAff = '".$record['AffMerchantId'] ."' AND AffId = '".$data['AffId'] ."'";
							$homePageInfo = $this->objProgram->objMysql->getFirstRow($sql);
							$homePage = preg_replace($this->pattern,'',$homePageInfo['Homepage']);
							if($urlInfo['http_code'] != 200){
								$urlJumpInfo = getTrueUrl($urlInfo['final_url']);
								unset($urlInfo);
								$urlInfo = $urlJumpInfo;
								if($urlInfo['http_code'] != 200){
									$sql = "UPDATE $tableName SET HttpCode=" . $urlInfo['http_code'] . ",IsActive = 'NO',SupportDeepUrlTpl = 'NO',LastCheckTime = '". date("Y-m-d H:i:s") . "' WHERE AffMerchantId = '" . $record['AffMerchantId'] ."' AND AffLinkId = '".$record['AffLinkId']."'";
									$this->objProgram->objPendingMysql->query($sql);
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
								$this->objProgram->objPendingMysql->query($sql);
								continue;
							}
							$pattern = array("AffId" => $data['AffId'],
								"IdInAff" => $record['AffMerchantId'],
								"AffDefaultUrl" => $record['LinkAffUrl']
							);
							$newTplDeepUrlTpl = $this->objProgram->getUrlByTpl($data['TplDeepUrlTpl'],$pattern);


							$innerUrlGet = $this->get_inner_url($urlDefaultInfo['final_url'],$homePage);
							$innerUrlBeforeInfo = getTrueUrl($innerUrlGet);
							$innerUrlBefore = $innerUrlBeforeInfo['final_url'];
							$innerTplUrl = $this->getDeepUrl($innerUrlBefore, $newTplDeepUrlTpl);
							$innerUrlAfterInfo = getTrueUrl($innerTplUrl);
							$innerUrlAfter = $innerUrlAfterInfo['final_url'];

							if($innerUrlAfterInfo['http_code'] == 0 || $innerUrlAfterInfo['http_code'] == 404){
								$sql = "UPDATE $tableName SET HttpCode=" . $innerUrlAfterInfo['http_code'] . ",IsActive = 'YES',SupportDeepUrlTpl = 'NO',LastCheckTime = '". date("Y-m-d H:i:s") . "' WHERE AffMerchantId = '" . $record['AffMerchantId'] ."' AND AffLinkId = '".$record['AffLinkId']."'";
								$this->objProgram->objPendingMysql->query($sql);
								continue;
							}
							if($urlInfo['http_code'] != 200){
								$urlJumpInfo = getTrueUrl($innerUrlAfterInfo['final_url']);
								unset($innerUrlAfterInfo);
								$innerUrlAfterInfo = $urlJumpInfo;
								if($innerUrlAfterInfo['http_code'] != 200){
									$sql = "UPDATE $tableName SET HttpCode=" . $innerUrlAfterInfo['http_code'] . ",IsActive = 'YES',SupportDeepUrlTpl = 'NO',LastCheckTime = '". date("Y-m-d H:i:s") . "' WHERE AffMerchantId = '" . $record['AffMerchantId'] ."' AND AffLinkId = '".$record['AffLinkId']."'";
									$this->objProgram->objPendingMysql->query($sql);
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
								$this->objProgram->objPendingMysql->query($sql);
								continue;
							}
							$innerUrlAfter = $innerUrlAfterInfo['final_url'];
							if($innerUrlBefore == $innerUrlAfter){
								$sql = "UPDATE $tableName SET HttpCode=" . $innerUrlAfterInfo['http_code'] . ",IsActive = 'YES',SupportDeepUrlTpl = 'YES',LastCheckTime = '". date("Y-m-d H:i:s") . "' WHERE AffMerchantId = '" . $record['AffMerchantId'] ."' AND AffLinkId = '".$record['AffLinkId'] . "'";
							} else {
								$sql = "UPDATE $tableName SET HttpCode=" . $innerUrlAfterInfo['http_code'] . ",IsActive = 'YES',SupportDeepUrlTpl = 'NO',LastCheckTime = '". date("Y-m-d H:i:s") . "' WHERE AffMerchantId = '" . $record['AffMerchantId'] ."' AND AffLinkId = '".$record['AffLinkId']."'";
							}
							$this->objProgram->objPendingMysql->query($sql);
						}
					}
				}
				else
				{
					echo "Table $tableName does not exist;\n";
					return;
				}
			}
			return;
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
	}