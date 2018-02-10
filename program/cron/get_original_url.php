<?php
	include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
	include_once(dirname(dirname(__FILE__)) . "/func/func.php");
	
	$objProgram = New Program();
	$date = date("Y-m-d H:i:s");
	//deal with the links from content feed
	$sql = "select KeyWord from affiliate_url_keywords";
	$keywords = $objProgram->objMysql->getRows($sql,'KeyWord');
	$keywords = array_keys($keywords);
	$i = 0;
	while (true)
	{
		$sql = "select a.`ID`,a.`ProgramId`,a.`StoreId`,a.`SimpleId`,a.`AffUrl`,a.`OriginalUrl` from content_feed_new a inner join program b on a.`ProgramId`=b.`ID` where a.`Status` = 'Active' and a.`OriginalUrl` != '' and b.`AffId` != 1 limit $i,1000";
		$i += 1000;
		$data = $objProgram->objMysql->getRows($sql);
		if (empty($data))
			break;
		print_r($data);
		foreach ($data as $datum)
		{
			$tmp = getTrueUrl($datum['AffUrl']);
			if (isset($tmp['final_url']) && $tmp['http_code'] == 200)
			{
				$url = urldecode($tmp['final_url']);
				$parse = parse_url($url);
				if (isset($parse['query'])) {
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
					$url = http_build_url($parse);
				}
				$url = addslashes($url);
				echo $url.PHP_EOL;
				die;
				$sql = "update content_feed_new set OriginalUrl = '{$url}' where `ID`='{$datum['ID']}'";
				$objProgram->objMysql->query($sql);
			}
		}
	}
	
	
	
	
	//deal with the links from pending_links
	if(1 == 0) {
		$sql = "select `TABLE_NAME` from `INFORMATION_SCHEMA`.`TABLES` where `TABLE_SCHEMA` = 'pendinglinks' and `TABLE_NAME` like 'affiliate_links_%' and `TABLE_NAME` regexp '[0-9]+';";
		$tableArr = $objProgram->objMysql->getRows($sql, 'TABLE_NAME');
		foreach ($tableArr as $table => $v) {
			$affid = str_replace('affiliate_links_', '', $table);
			
			$i = 0;
			while (0) {
				$sql = "select AffMerchantId,AffLinkId,LinkAffUrl from {$table} where IsActive='YES' and (FinalUrl is null or FinalUrl = '') and FinalUrl != 'Invalid' limit $i,1000";
				$i += 1000;
				$data = $objProgram->objPendingMysql->getRows($sql);
				if (empty($data))
					break;
				foreach ($data as $item) {
					$aff_merchant_id = addslashes($item['AffMerchantId']);
					$aff_link_id = addslashes($item['AffLinkId']);
					$tmp = getTrueUrl($item['LinkAffUrl']);
					if (isset($tmp['final_url']))
						$url = addslashes($tmp['final_url']);
					else
						$url = '';
					
					if ($tmp['http_code'] != 200) {
						$sql = "update {$table} set HttpCode='{$tmp['http_code']}' where AffMerchantId='{$aff_merchant_id}' and AffLinkId='{$aff_link_id}'";
						$objProgram->objPendingMysql->query($sql);
					} else {
						$sql = "select Domain from program_intell where AffId='{$affid}' and IdInAff='{$aff_merchant_id}'";
						$domain = $objProgram->objMysql->getFirstRowColumn($sql);
						$url = addslashes($url);
						if (stripos($url, $domain) !== false) {
							$sql = "update {$table} set `FinalUrl`='{$url}' where AffMerchantId='{$aff_merchant_id}' and AffLinkId='{$aff_link_id}'";
						} else {
							$sql = "update {$table} set `FinalUrl`='Invalid' where AffMerchantId='{$aff_merchant_id}' and AffLinkId='{$aff_link_id}'";
						}
						$objProgram->objPendingMysql->query($sql);
					}
				}
			}
			
			
			//get keywords
			$i = 0;
			$keywords_tmp = array();
			$keywords = array();
			while (1) {
				$sql = "select AffMerchantId,FinalUrl from {$table} where IsActive='YES' and FinalUrl is not null and FinalUrl != '' and FinalUrl != 'Invalid' limit $i,1000";
				$i += 1000;
				$data = $objProgram->objPendingMysql->getRows($sql);
				if (empty($data))
					break;
				foreach ($data as $item) {
					$pos = stripos($item['FinalUrl'], '?');
					if ($pos !== false) {
						$parse = substr($item['FinalUrl'], $pos + 1);
						$parse = explode('&', $parse);
						foreach ($parse as $para) {
							$pos = stripos($para, '=');
							if ($pos !== false) {
								$para = trim(substr($para, 0, $pos));
								$keywords_tmp[$para][] = $item['AffMerchantId'];
							}
						}
					}
				}
			}

			//delete useless keywords
			foreach ($keywords_tmp as $keyword => $sum) {
				$sum = array_unique($sum);
				if (count($sum) > 20) {
					$keywords[] = $keyword;
					$sql = "insert into affiliate_url_keywords (Affid,Keyword,AddTime) values ('{$affid}','{$keyword}','{$date}')";
					$objProgram->objMysql->query($sql);
				}
			}
			unset($keywords_tmp);

			//handle FinalUrl
			$i = 0;
			while (0) {
				$sql = "select AffMerchantId,AffLinkId,FinalUrl from {$table} where IsActive='YES' and FinalUrl is not null and FinalUrl != '' and FinalUrl != 'Invalid' limit $i,1000";
				$i += 1000;
				$data = $objProgram->objPendingMysql->getRows($sql);
				if (empty($data))
					break;
				foreach ($data as $item) {
					$aff_merchant_id = addslashes($item['AffMerchantId']);
					$aff_link_id = addslashes($item['AffLinkId']);
					$url = $item['FinalUrl'];
					$parse = parse_url($item['FinalUrl']);
					if (isset($parse['query'])) {
						$paras = explode('&', $parse['query']);
						$paras = array_diff($paras, $keywords);
						$parse['query'] = implode('&', $paras);
						$url = http_build_url($parse);
					}
					$url = addslashes($url);
					$sql = "update {$table} set `LinkOriginalUrl`='{$url}',LastChangeTime='{$date}' where AffMerchantId='{$aff_merchant_id}' and AffLinkId='{$aff_link_id}'";
					$objProgram->objPendingMysql->query($sql);
				}
			}
		}
	}
