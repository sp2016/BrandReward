<?php
require_once 'text_parse_helper.php';
require_once 'xml2array.php';

class LinkFeed_Zanox
{
	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		
		$this->ConnectId = '842953543798E1C7D191';
		$this->SecretKey = '93bfeAd13ced4c+2bEc3b47e71410d/6e0930c4a';
		$this->UserId = '2261055';
		$this->SpaceId = '2182939';
		
		$this->batchProgram = date('Ymd')."_program_".$this->oLinkFeed->batchid;
	}
	
	function GetProgramFromAff($accountid)
	{
		$this->account = $this->oLinkFeed->getAffAccountById($accountid);
		$this->info['AffLoginUrl'] = $this->account['LoginUrl'];	
		$this->info['AffLoginPostString'] = $this->account['LoginPostString'];	
		$this->info['AffLoginVerifyString'] = $this->account['LoginVerifyString'];	
		$this->info['AffLoginMethod'] = $this->account['LoginMethod'];	
		$this->info['AffLoginSuccUrl'] = $this->account['LoginSuccUrl'];	
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";	

		
		$this->site = $this->oLinkFeed->getAccountSiteById($accountid);
		foreach($this->site as $v){
			echo 'Site:' . $v['Name']. "\r\n";
			$this->GetProgramByApi($v['SiteID']);
		}
		echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";
		
		$this->CheckBatch();
	}
	
	private function key_implode($glue, $array, $key)
	{
		$t = array();
		if (key_exists($key, $array) && !is_array($array[$key]))
			return $array[$key];
		foreach ($array as $v)
		{
			if (is_array($v) && key_exists($key, $v))
			{
				if (is_array($v[$key]))
					$t[] = implode(',', $v[$key]);
				else
					$t[] = $v[$key];
			}
		}
		return implode($glue, $t);
	}
	
	function GetProgramByApi($SiteID)
	{
		echo "\tGet Program by api start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		$request = array("AffId" => $this->info["AffID"], "method" => "get", "postdata" => "",);
		
	//	print_r($this->info);
		//step 1,login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffID"], $this->info);
		
		$use_true_file_name = true;
		
		
		//step 2,get program by API
		list ($page, $items, $total, $arr_prgm, $cnt) = array(0, 50, 0, array(), 0);
		do
		{		
			$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"],"list_".date("Ymd")."_{$page}.dat", $this->batchProgram, $use_true_file_name);
				
			if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
			{
				$prgm_url = sprintf("http://api.zanox.com/json/2011-03-01/programs?page=%s&connectid=".$this->ConnectId."&items=%s", $page, $items);
				$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
				if($prgm_arr['code'] == 200){
					$results = $prgm_arr['content'];
					$this->oLinkFeed->fileCachePut($cache_file, $results);
				}
			}
			$cache_file = file_get_contents($cache_file);
			$data = json_decode($cache_file, true);
			
			if (empty($total))
				$total = (int)$data['total'];
			echo "\tpage: $page\r\n";
			foreach ((array)$data['programItems']['programItem'] as $prgm)
			{
				$strMerID = (int)$prgm['@id'];
				if (!$strMerID)
					continue;
		
				$arr_prgm[$strMerID] = array(
						"SiteID" => $SiteID,
						"AccountID" => $this->account['AccountID'],
						"Name" => addslashes(html_entity_decode($prgm['name'])),
						"BatchID" => $this->oLinkFeed->batchid,
						"AffID" => $this->info["AffID"],
						"RankInAff" => $prgm['adrank'],
						"StatusInAffRemark" => addslashes($prgm['status']),
						"IdInAff" => $strMerID,
						"CookieTime" => intval(@$prgm['returnTimeSales']) / 86400,
						"StatusInAff" => ucfirst(addslashes($prgm['status'])),						//'Active','TempOffline','Offline'
						"Homepage" => addslashes($prgm['url']),
						"Category" => '',
						"LaunchDate" => isset($prgm['startDate']) ? date("Y-m-d H:i:s", strtotime($prgm['startDate'])) : "",
						"regions" => '',
						"Currency" => $prgm['currency'],
						"DetailPage" => "https://marketplace.zanox.com/zanox/affiliate/".$this->UserId."/".$this->SpaceId."/merchant-profile/$strMerID",
						"LastUpdateTime" => date("Y-m-d H:i:s"),
						"LogoUrl" => addslashes($prgm['image']),
				);
				
				if(isset($prgm['categories']) && is_array($prgm['categories']))
					$arr_prgm[$strMerID]['Category'] =$this->key_implode(',', $prgm['categories'], '$');
				
				if(isset($prgm['regions']) && is_array($prgm['regions']))
					$arr_prgm[$strMerID]['regions'] =$this->key_implode(',', $prgm['regions'], 'region');
				
				//step 2,get program detail by page
				/*
				 * detail
				 */
				$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"],"detail_".date("Ym")."_{$strMerID}.dat", $this->batchProgram, $use_true_file_name);
				if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
				{
					$prgm_url = $arr_prgm[$strMerID]['DetailPage'];
					$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
					if($prgm_arr['code'] == 200){
						$results = $prgm_arr['content'];
						$this->oLinkFeed->fileCachePut($cache_file, $results);
					}
				}
				$cache_file = file_get_contents($cache_file);
				$nLineStart = 0;
				$PartnershipInAff = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, array('<h1 id="accountName">', '<div>('), ')</div>', $nLineStart));
				if ($PartnershipInAff == 'Accepted')
				{
					$Partnership = 'Active';
				}elseif ($PartnershipInAff == 'Not Applied')
				{
					$Partnership = 'NoPartnership';
				}elseif ($PartnershipInAff == 'Pending')
				{
					$Partnership = 'Pending';
				}elseif ($PartnershipInAff == 'Suspended')
				{
					$Partnership = 'NoPartnership';
				}elseif ($PartnershipInAff == 'Not Accepted')
				{
					$Partnership = 'Declined';
				}elseif ($PartnershipInAff == 'Closed')
				{
					$Partnership = 'Expired';
				}else{
					mydie('there is new partnership :'.$PartnershipInAff.' ,and its IdInAff is '.$strMerID);
				}
				$arr_prgm[$strMerID]['Partnership'] = $Partnership;
				
				$arr_prgm[$strMerID]['MobileOptimised'] = strtoupper(trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, '<i class="fa fa-mobile"></i>' ,'<', $nLineStart)));
				$arr_prgm[$strMerID]['BlogUrl'] = addslashes(trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, array('</i> Website</a>', '<a href="'), '" class="list-group-item"><i class="fa fa-feed fa-fw">', $nLineStart)));
				$arr_prgm[$strMerID]['Twitter'] = addslashes(trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, '<a href="', '" class="list-group-item"><i class="fa fa-twitter fa-fw">', $nLineStart)));
				$arr_prgm[$strMerID]['ContactPerson'] = addslashes(trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, '<h3 id="name">', '<', $nLineStart)));
				$arr_prgm[$strMerID]['ContactAddress'] = addslashes(trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, '<p id="address">', '</p>', $nLineStart)));
				$arr_prgm[$strMerID]['ContactTelephone'] = addslashes(trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, '<p id="mob">Cell phone:', '<', $nLineStart)));
				$arr_prgm[$strMerID]['ContactEmail'] = addslashes(trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, '<p id="email">Email:', '<', $nLineStart)));
				$arr_prgm[$strMerID]['Description'] = addslashes(trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, '<div id="descriptionLongContent" class="inlineTextArea">', '</div>', $nLineStart)));
				/*
				 * terms
				 */
				$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"],"terms".date("Ym")."_{$strMerID}.dat", $this->batchProgram, $use_true_file_name);
				if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
				{
					$prgm_url = "https://marketplace.zanox.com/zanox/affiliate/".$this->UserId."/".$this->SpaceId."/merchant-profile-terms/$strMerID";
					$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
					if($prgm_arr['code'] == 200){
						$results = $prgm_arr['content'];
						$this->oLinkFeed->fileCachePut($cache_file, $results);
					}
				}
				$cache_file = file_get_contents($cache_file);
				$arr_prgm[$strMerID]['TermAndCondition'] = addslashes(trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, '<div id="termsFreeTextContent" class="inlineTextArea">', '</div>')));
				/*
				 * Paid Search
				 */
				$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"],"paidSearchTerm".date("Ym")."_{$strMerID}.dat", $this->batchProgram, $use_true_file_name);
				if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
				{
					$prgm_url = "https://marketplace.zanox.com/zanox/affiliate/".$this->UserId."/".$this->SpaceId."/merchant-profile-terms/$strMerID/paidSearch";
					$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
					if($prgm_arr['code'] == 200){
						$results = $prgm_arr['content'];
						$this->oLinkFeed->fileCachePut($cache_file, $results);
					}
				}
				$cache_file = file_get_contents($cache_file);
				$LineStart = 0;
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'send traffic directly to your website?</b></td><td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick_grey.png')
				{
					$arr_prgm[$strMerID]['AllowSendTrafficDirectly'] = 'NO';
				}
				elseif ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$arr_prgm[$strMerID]['AllowSendTrafficDirectly'] = 'YES';
				}
				elseif (!$whether_Image)
				{
					$arr_prgm[$strMerID]['AllowSendTrafficDirectly'] = 'UNKNOWN';
				}
				else
				{
					mydie("AllowSendTrafficDirectly is '".$whether_Image."', please check it\n\r");
				}
				
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'search keyword (e.g. mybrand)?</b></td><td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick_grey.png')
				{
					$arr_prgm[$strMerID]['AllowBrandNameEnteredSearchKeywords'] = 'NO';
				}
				elseif ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$arr_prgm[$strMerID]['AllowBrandNameEnteredSearchKeywords'] = 'YES';
				}
				elseif (!$whether_Image)
				{
					$arr_prgm[$strMerID]['AllowBrandNameEnteredSearchKeywords'] = 'UNKNOWN';
				}
				else
				{
					mydie("AllowBrandNameEnteredSearchKeywords is '".$whether_Image."', please check it\n\r");
				}
				
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, '(e.g. mybrent)?</b></td><td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick_grey.png')
				{
					$arr_prgm[$strMerID]['AllowMisspellingsBrandNameEnteredSearchKeywords'] = 'NO';
				}
				elseif ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$arr_prgm[$strMerID]['AllowMisspellingsBrandNameEnteredSearchKeywords'] = 'YES';
				}
				elseif (!$whether_Image)
				{
					$arr_prgm[$strMerID]['AllowMisspellingsBrandNameEnteredSearchKeywords'] = 'UNKNOWN';
				}
				else
				{
					mydie("AllowMisspellingsBrandNameEnteredSearchKeywords is '".$whether_Image."', please check it\n\r");
				}
				
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, '(e.g. mybrand voucher)?</b></td><td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick_grey.png')
				{
					$arr_prgm[$strMerID]['AllowBrandNameAndGenericWordsEnteredSearchKeywords'] = 'NO';
				}
				elseif ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$arr_prgm[$strMerID]['AllowBrandNameAndGenericWordsEnteredSearchKeywords'] = 'YES';
				}
				elseif (!$whether_Image)
				{
					$arr_prgm[$strMerID]['AllowBrandNameAndGenericWordsEnteredSearchKeywords'] = 'UNKNOWN';
				}
				else
				{
					mydie("AllowBrandNameAndGenericWordsEnteredSearchKeywords is '".$whether_Image."', please check it\n\r");
				}
				
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'display URL?</b></td><td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick_grey.png')
				{
					$arr_prgm[$strMerID]['AllowDisplayBrandNameInURL'] = 'NO';
				}
				elseif ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$arr_prgm[$strMerID]['AllowDisplayBrandNameInURL'] = 'YES';
				}
				elseif (!$whether_Image)
				{
					$arr_prgm[$strMerID]['AllowDisplayBrandNameInURL'] = 'UNKNOWN';
				}
				else
				{
					mydie("AllowDisplayBrandNameInURL is '".$whether_Image."', please check it\n\r");
				}
				
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'description?</b></td><td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick_grey.png')
				{
					$arr_prgm[$strMerID]['AllowDisplayBrandNameInTitleAndDesc'] = 'NO';
				}
				elseif ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$arr_prgm[$strMerID]['AllowDisplayBrandNameInTitleAndDesc'] = 'YES';
				}
				elseif (!$whether_Image)
				{
					$arr_prgm[$strMerID]['AllowDisplayBrandNameInTitleAndDesc'] = 'UNKNOWN';
				}
				else
				{
					mydie("AllowDisplayBrandNameInTitleAndDesc is '".$whether_Image."', please check it\n\r");
				}
				
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'generic)?</b></td><td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick_grey.png')
				{
					$arr_prgm[$strMerID]['AllowBrandNameIntoNegativeKeywords'] = 'NO';
				}
				elseif ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$arr_prgm[$strMerID]['AllowBrandNameIntoNegativeKeywords'] = 'YES';
				}
				elseif (!$whether_Image)
				{
					$arr_prgm[$strMerID]['AllowBrandNameIntoNegativeKeywords'] = 'UNKNOWN';
				}
				else
				{
					mydie("AllowBrandNameIntoNegativeKeywords is '".$whether_Image."', please check it\n\r");
				}
				
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'ads?</b></td><td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick_grey.png')
				{
					$arr_prgm[$strMerID]['NotAllowSpecificKeywordsInAds'] = 'NO';
				}
				elseif ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$arr_prgm[$strMerID]['NotAllowSpecificKeywordsInAds'] = 'YES';
				}
				elseif (!$whether_Image)
				{
					$arr_prgm[$strMerID]['NotAllowSpecificKeywordsInAds'] = 'UNKNOWN';
				}
				else
				{
					mydie("NotAllowSpecificKeywordsInAds is '".$whether_Image."', please check it\n\r");
				}
				
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Google<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$SupportSearchEnginesForPaidSearch[] = 'Google';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Yahoo<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$SupportSearchEnginesForPaidSearch[] = 'Yahoo';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Bing<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$SupportSearchEnginesForPaidSearch[] = 'Bing';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Other<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$SupportSearchEnginesForPaidSearch[] = 'Other';
				}
				if (isset($SupportSearchEnginesForPaidSearch))
				{
					$arr_prgm[$strMerID]['SupportSearchEnginesForPaidSearch'] = implode(',', $SupportSearchEnginesForPaidSearch);
				}
				else
				{
					$arr_prgm[$strMerID]['SupportSearchEnginesForPaidSearch'] = '';
				}
				/*
				 * transaction
				 */
				$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"],"transactionTerm".date("Ym")."_{$strMerID}.dat", $this->batchProgram, $use_true_file_name);
				if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
				{
					$prgm_url = "https://marketplace.zanox.com/zanox/affiliate/".$this->UserId."/".$this->SpaceId."/merchant-profile-terms/$strMerID/transaction";
					$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
					if($prgm_arr['code'] == 200){
						$results = $prgm_arr['content'];
						$this->oLinkFeed->fileCachePut($cache_file, $results);
					}
				}
				$cache_file = file_get_contents($cache_file);
				$LineStart = 0;
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'information box.)</b></td><td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick_grey.png')
				{
					$arr_prgm[$strMerID]['ExcludeProductsInAdvertiserShop'] = 'NO';
				}
				elseif ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$arr_prgm[$strMerID]['ExcludeProductsInAdvertiserShop'] = 'YES';
				}
				elseif (!$whether_Image)
				{
					$arr_prgm[$strMerID]['ExcludeProductsInAdvertiserShop'] = 'UNKNOWN';
				}
				else
				{
					mydie("ExcludeProductsInAdvertiserShop is '".$whether_Image."', please check it\n\r");
				}
				
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Order cancelled<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$CommissionDeclinedReson[] = 'Order cancelled';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Credit card not accepted<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$CommissionDeclinedReson[] = 'Credit card not accepted';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Payment not received<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$CommissionDeclinedReson[] = 'Payment not received';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Order returned<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$CommissionDeclinedReson[] = 'Order returned';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Credit check failed<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$CommissionDeclinedReson[] = 'Credit check failed';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Fraudulent order/application<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$CommissionDeclinedReson[] = 'Fraudulent order/application';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Partial delivery (commission adjustment)<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$CommissionDeclinedReson[] = 'Partial delivery (commission adjustment)';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Internal order / test order<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$CommissionDeclinedReson[] = 'Internal order / test order';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Breach of program terms<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$CommissionDeclinedReson[] = 'Breach of program terms';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Application incomplete<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$CommissionDeclinedReson[] = 'Application incomplete';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Application denied<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$CommissionDeclinedReson[] = 'Application denied';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Double data (leads)<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$CommissionDeclinedReson[] = 'Double data (leads)';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Blacklisted user<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$CommissionDeclinedReson[] = 'Blacklisted user';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Bounced e-mail<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$CommissionDeclinedReson[] = 'Bounced e-mail';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'De-duplication<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$CommissionDeclinedReson[] = 'De-duplication';
				}
				if (isset($CommissionDeclinedReson))
				{
					$arr_prgm[$strMerID]['CommissionDeclinedReson'] = implode(',', $CommissionDeclinedReson);
				}
				else
				{
					$arr_prgm[$strMerID]['CommissionDeclinedReson'] = '';
				}
				
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'VAT?<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$CommissionArePaidOnInclude[] = 'VAT';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'delivery charges?<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$CommissionArePaidOnInclude[] = 'delivery charges';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'credit card fees?<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$CommissionArePaidOnInclude[] = 'credit card fees';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'other service charges?<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$CommissionArePaidOnInclude[] = 'other service charges';
				}
				if (isset($CommissionArePaidOnInclude))
				{
					$arr_prgm[$strMerID]['CommissionArePaidOnInclude'] = implode(',', $CommissionArePaidOnInclude);
				}
				else
				{
					$arr_prgm[$strMerID]['CommissionArePaidOnInclude'] = '';
				}
				/*
				 * traffic policy
				 */
				$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"],"TrafficPolicy".date("Ym")."_{$strMerID}.dat", $this->batchProgram, $use_true_file_name);
				if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
				{
					$prgm_url = "https://marketplace.zanox.com/zanox/affiliate/".$this->UserId."/".$this->SpaceId."/merchant-profile-terms/$strMerID/trafficPolicy";
					$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
					if($prgm_arr['code'] == 200){
						$results = $prgm_arr['content'];
						$this->oLinkFeed->fileCachePut($cache_file, $results);
					}
				}
				$cache_file = file_get_contents($cache_file);
				$LineStart = 0;
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Pop-ups / Pop-unders allowed<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$GeneralTrafficPolicyAllow[] = 'Pop-ups / Pop-unders allowed';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Layers, site unders allowed<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$GeneralTrafficPolicyAllow[] = 'Layers, site unders allowed';
				}
				if (isset($GeneralTrafficPolicyAllow))
				{
					$arr_prgm[$strMerID]['GeneralTrafficPolicyAllow'] = implode(',', $GeneralTrafficPolicyAllow);
				}
				else
				{
					$arr_prgm[$strMerID]['GeneralTrafficPolicyAllow'] = '';
				}
				
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Loyalty<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$AllowPromotionalMethod[] = 'Loyalty';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Cashback<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$AllowPromotionalMethod[] = 'Cashback';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Soft incentives<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$AllowPromotionalMethod[] = 'Soft incentives';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Standalone e-mail campaign<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$AllowPromotionalMethod[] = 'Standalone e-mail campaign';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'E-mail allowed<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$AllowPromotionalMethod[] = 'E-mail allowed';
				}
				if (isset($AllowPromotionalMethod))
				{
					$arr_prgm[$strMerID]['AllowPromotionalMethod'] = implode(',', $AllowPromotionalMethod);
				}
				else
				{
					$arr_prgm[$strMerID]['AllowPromotionalMethod'] = '';
				}
				/*
				 * Branding Terms
				 */
				$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"],"BrandingTerms".date("Ym")."_{$strMerID}.dat", $this->batchProgram, $use_true_file_name);
				if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
				{
					$prgm_url = "https://marketplace.zanox.com/zanox/affiliate/".$this->UserId."/".$this->SpaceId."/merchant-profile-terms/$strMerID/brand";
					$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
					if($prgm_arr['code'] == 200){
						$results = $prgm_arr['content'];
						$this->oLinkFeed->fileCachePut($cache_file, $results);
					}
				}
				$cache_file = file_get_contents($cache_file);
				$arr_prgm[$strMerID]['BrandingTerms'] = addslashes(trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, '<div id="termsFreeTextContent" class="inlineTextArea">', '</div>')));
				/*
				 * Notice Periods
				 */
				$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"],"NoticePeriods".date("Ym")."_{$strMerID}.dat", $this->batchProgram, $use_true_file_name);
				if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
				{
					$prgm_url = "https://marketplace.zanox.com/zanox/affiliate/".$this->UserId."/".$this->SpaceId."/merchant-profile-terms/$strMerID/period";
					$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
					if($prgm_arr['code'] == 200){
						$results = $prgm_arr['content'];
						$this->oLinkFeed->fileCachePut($cache_file, $results);
					}
				}
				$cache_file = file_get_contents($cache_file);
				$LineStart = 0;
				$arr_prgm[$strMerID]['ChangeTermsAndConditionDays'] = intval(trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'program?</b></td><td><p>', '<', $LineStart)));
				$arr_prgm[$strMerID]['UpdateWebsiteDays'] = intval(trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'publishers?</b></td><td><p>', '<', $LineStart)));
				$arr_prgm[$strMerID]['LowerCommissionDays'] = intval(trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'commissions?</b></td><td><p>', '<', $LineStart)));
				/*
				 * De-Duplicate
				 */
				$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"],"De-Duplicate".date("Ym")."_{$strMerID}.dat", $this->batchProgram, $use_true_file_name);
				if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
				{
					$prgm_url = "https://marketplace.zanox.com/zanox/affiliate/".$this->UserId."/".$this->SpaceId."/merchant-profile-terms/$strMerID/dedupe";
					$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
					if($prgm_arr['code'] == 200){
						$results = $prgm_arr['content'];
						$this->oLinkFeed->fileCachePut($cache_file, $results);
					}
				}
				$cache_file = file_get_contents($cache_file);
				$LineStart = 0;
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'de-duplication.</b></td><td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick_grey.png')
				{
					$arr_prgm[$strMerID]['WhetherDeduplicateTransactions'] = 'NO';
				}
				elseif ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$arr_prgm[$strMerID]['WhetherDeduplicateTransactions'] = 'YES';
				}
				elseif (!$whether_Image)
				{
					$arr_prgm[$strMerID]['WhetherDeduplicateTransactions'] = 'UNKNOWN';
				}
				else
				{
					mydie("WhetherDeduplicateTransactions is '".$whether_Image."', please check it\n\r");
				}
				
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Do you use zanox tracking or a third party to de-duplicate?<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$DeduplicateTransactionsType[] = 'Do you use zanox tracking or a third party to de-duplicate?';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Paid search with brand keywords<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$DeduplicateTransactionsType[] = 'Paid search with brand keywords';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Paid search with generic keywords<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$DeduplicateTransactionsType[] = 'Paid search with generic keywords';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'E-mail advertising<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$DeduplicateTransactionsType[] = 'E-mail advertising';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Display advertising<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$DeduplicateTransactionsType[] = 'Display advertising';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Direct partnerships<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$DeduplicateTransactionsType[] = 'Direct partnerships';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Price comparison<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$DeduplicateTransactionsType[] = 'Price comparison';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Organic/direct traffic<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$DeduplicateTransactionsType[] = 'Organic/direct traffic';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Other affiliate networks<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$DeduplicateTransactionsType[] = 'Other affiliate networks';
				}
				$whether_Image = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Is your operation modus realtime?<td><img src="', '"', $LineStart));
				if ($whether_Image == 'https://images.awin.com/common/icons/16x16/tick.png')
				{
					$DeduplicateTransactionsType[] = 'Is your operation modus realtime?';
				}
				if (isset($DeduplicateTransactionsType))
				{
					$arr_prgm[$strMerID]['DeduplicateTransactionsType'] = implode(',', $DeduplicateTransactionsType);
				}
				else
				{
					$arr_prgm[$strMerID]['DeduplicateTransactionsType'] = '';
				}
				/*
				 * performance
				 */
				$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"],"performance".date("Ym")."_{$strMerID}.dat", $this->batchProgram, $use_true_file_name);
				if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
				{
					$prgm_url = "https://marketplace.zanox.com/zanox/affiliate/".$this->UserId."/".$this->SpaceId."/merchant-profile/$strMerID/performance";
					$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
					if($prgm_arr['code'] == 200){
						$results = $prgm_arr['content'];
						$this->oLinkFeed->fileCachePut($cache_file, $results);
					}
				}
				$cache_file = file_get_contents($cache_file);
				$arr_prgm[$strMerID]['Performance'] = addslashes(trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, '<div class="table-responsive">', '</div>')));
				/*
				 * commission
				 */
				$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"],"commission".date("Ym")."_{$strMerID}.dat", $this->batchProgram, $use_true_file_name);
				if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
				{
					$prgm_url = "https://marketplace.zanox.com/zanox/affiliate/".$this->UserId."/".$this->SpaceId."/merchant-profile/$strMerID/commission-groups";
					$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
					if($prgm_arr['code'] == 200){
						$results = $prgm_arr['content'];
						$this->oLinkFeed->fileCachePut($cache_file, $results);
					}
				}
				$cache_file = file_get_contents($cache_file);
				$arr_prgm[$strMerID]['Commission'] = addslashes(trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($cache_file, 'Standard Commission Rate', '</div>'))));
				/*
				 * commission details
				 */
				$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"],"CommissionDetails".date("Ym")."_{$strMerID}.dat", $this->batchProgram, $use_true_file_name);
				if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
				{
					$prgm_url = "https://marketplace.zanox.com/zanox/affiliate/".$this->UserId."/".$this->SpaceId."/merchant-profile/$strMerID/xhr-commission-group-search";
					$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
					if($prgm_arr['code'] == 200){
						$results = $prgm_arr['content'];
						$this->oLinkFeed->fileCachePut($cache_file, $results);
					}
				}
				$cache_file = file_get_contents($cache_file);
				$arr_prgm[$strMerID]['CommissionDetails'] = addslashes(trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, '<div class="cgTable trackingCategoryTable table-reponsive">', '</div>')));
				
				$cnt++;
		
				if (count($arr_prgm)) 
				{
					$objProgram->updateProgram($this->info["AffID"], $arr_prgm);
					$arr_prgm = array();
				}
			}
			if(count($arr_prgm) > 0)
			{
				$objProgram->updateProgram($this->info["AffID"], $arr_prgm);
					$arr_prgm = array();
			}
			$page ++;
		}while ($page < 1000 && $page * $items < $total);
		
		echo "\tGet Program by api end\r\n";
		if ($program_num < 10) {
			mydie("die: program count < 10, please check program.\n");
		}
		echo "\tUpdate ({$program_num}) program.\r\n";
		
	}
	
	function CheckBatch()
	{
		$objProgram = new ProgramDb();
		//$this->oLinkFeed->batchid;
		$objProgram->syncBatchToProgram($this->info["AffID"], $this->oLinkFeed->batchid);
	}
}
?>		