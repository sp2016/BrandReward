<?php
/**
 * User: rzou
 * Date: 2017/8/8
 * Time: 16:30
 */
class LinkFeed_Impact_Radius
{
	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		
		$this->batchProgram = date('Ymd') . "_program_" . $this->oLinkFeed->batchid;
		
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
		foreach ($this->site as $v) {
			echo 'Site:' . $v['Name'] . "\r\n";
			$this->GetProgramByApi($v['SiteID'], $v['SiteIdInAff'], $v['APIKey']);
		}
		echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";
		
		$this->CheckBatch();
	}
	
	function GetProgramByApi($SiteID, $SiteIdInAff, $APIKey)
	{
		echo "\tGet Program by Api start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		$use_true_file_name = true;
		$request = array("AffId" => $this->info["AffID"], "method" => "get", "postdata" => "",);
		
		$this->oLinkFeed->LoginIntoAffService($this->info["AffID"], $this->info, 6, true, false, false);
		
		$page = 1;
		$perPage = 100;
		$hasNextPage = true;
		while ($hasNextPage) {
			echo "Page:$page\t";
			
			$strUrl = sprintf('https://%s:%s@api.impactradius.com/2010-09-01/Mediapartners/%s/Campaigns.json?PageSize=%s&Page=%s', $SiteIdInAff, $APIKey, $SiteIdInAff, $perPage, $page);
			$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "list_" . date("Ym") . "_data_page{$page}.dat", $this->batchProgram, $use_true_file_name);
			if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
				$results = $this->GetHttpResultMoreTry($strUrl, $request);
				$this->oLinkFeed->fileCachePut($cache_file, $results);
			}
			$result = file_get_contents($cache_file);
			$data = json_decode($result, true);
			
			if (!isset($data['Campaigns']) || empty($data['Campaigns'])) {
				mydie('Can\'t get data, please check the api !');
			}
			if ($data['@total'] <= $page * $perPage) {
				$hasNextPage = false;
				if ($this->debug) print " NO NEXT PAGE  <br>\n";
			} else {
				$page++;
				if ($this->debug) print " Have NEXT PAGE  <br>\n";
			}
			
			foreach ($data['Campaigns'] as $val) {
				$ProgramID = intval($val['CampaignId']);
				
				if (!$ProgramID) {
					continue;
				}
				
				$arr_prgm[$ProgramID] = array(
					"SiteID" => $SiteID,
					"AccountID" => $this->account['AccountID'],
					'AffID' => $this->info['AffID'],
					'IdInAff' => $ProgramID,
					'ProgramID' => $ProgramID,
					'BatchID' => $this->oLinkFeed->batchid,
					'Name' => addslashes(trim($val['CampaignName'])),
					'Homepage' => addslashes(trim($val['CampaignUrl'])),
					'Description' => addslashes(trim($val['CampaignDescription'])),
					'ShippingRegions' => addslashes(join(',',$val['ShippingRegions'])),
					'LogoUrl' => 'https://member.impactradius.com' . addslashes(trim($val['CampaignLogoUri'])),
					'InsertionOrderStatus' => addslashes(trim($val['InsertionOrderStatus'])),
					'TrackingLink' => addslashes($val['TrackingLink']),
					'AllowsDeeplinking' => addslashes($val['AllowsDeeplinking']),
					'DeeplinkDomains' => addslashes(join(' ',$val['DeeplinkDomains'])),
				);
				$program_num++;
				
				if (strpos($val['InsertionOrderStatus'], 'Active') === false) {
					$objProgram->updateProgram($this->info["AffID"], $arr_prgm);
					$arr_prgm = array();
					continue;
				}
				
				$merDetailUrl = sprintf("https://member.impactradius.com/secure/directory/campaign.ihtml?d=lightbox&c=%s&fromPopup=1", $ProgramID);
				$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "detail_" . date("Ym") . "_{$ProgramID}.dat", $this->batchProgram, $use_true_file_name);
				if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
					$detail_page = $this->GetHttpResultMoreTry($merDetailUrl, $request);
					if (!$detail_page) {
						mydie("Can't get detailpage!");
					} else {
						$this->oLinkFeed->fileCachePut($cache_file, $detail_page);
					}
				}
				$result = file_get_contents($cache_file);
				$result = preg_replace('@>\s+<@', '><', $result);
				
				$strPosition = 0;
				$EPC = $this->oLinkFeed->ParseStringBy2Tag($result, array('id="epc"', '>'), '<', $strPosition);
				$Earnings = $this->oLinkFeed->ParseStringBy2Tag($result, array('id="revStats"', '>'), '<', $strPosition);
				
				preg_match_all('@class="fa fa-star"@',$result,$m);
				$StarRating = count($m[0]) . '/5';
				
				$Categories = $this->oLinkFeed->ParseStringBy2Tag($result, array('id="categoryLink"', '>'), '<', $strPosition);
				$Status = '';
				$JoinDate = $this->oLinkFeed->ParseStringBy2Tag($result, '<li>', '</li>', $strPosition);
				if (strpos($JoinDate, 'Active') !== false) {
					$Status = 'Active';
					$JoinDate = trim(str_replace('Active','',$JoinDate));
				}
				
				$Commission = strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('mpNotificationsList ioDetails','>'), '</ul>', $strPosition));
				$Notifications = strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('id="notificationListItems"', '>'), '</ul>', $strPosition));
				$FundingStatus = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('uitkTooltipInfo', 'Funding Status', '>'), '<', $strPosition),'&nbsp;');
				$RecentActivity = strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('id="recentActivityId"','>'), '</ul>', $strPosition));
				$ContactName = $this->oLinkFeed->ParseStringBy2Tag($result, array('id="contactName"','>'), '<');
				$ContactEmail = 'https://member.impactradius.com' . trim($this->oLinkFeed->ParseStringBy2Tag($result, 'parent.uitkPopup("', '"'));
				
				preg_match_all('@class="truncate dirContactDetails">([0-9+ ()-]+)</div@', $result, $cpn);
				$ContactPhoneNumber = empty($cpn[1])? '' : join(',',$cpn[1]);
				$CompanyMsg = $this->oLinkFeed->ParseStringBy2Tag($result, array('>Company<','class="dirMediaKitUrl">'), '</ul>');
				
				preg_match_all('@li>([^<]+)</li@',$CompanyMsg,$cm);
				$CompanyContactPhoneNumber = @$cm[1][3];
				if (isset($cm[1][3])){
					unset($cm[1][3]);
				}
				$CompanyAddress = join(' ',$cm[1]);
				
				$AdditionalInformation = strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('Additional Information','<ul','>'), ' </ul', $strPosition), '<a>');
				$ResponseRate = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('id="responseRate"', '>'), '</ul', $strPosition)));
				$AcceptanceRate = strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('id="acceptanceRate"', '>'), '</ul', $strPosition));
				$ServiceAreas = strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('id="serviceAreas"', '>'), '</div', $strPosition));
				
				preg_match_all('@span class="uitkCheck(True|False)"@', $result, $prgmAtt);
				if (empty($prgmAtt[1]) || count($prgmAtt[1]) != 8) {
					mydie("Get campaign Attributes List wrong!");
				}
				
				$termsUrl = sprintf('https://member.impactradius.com/secure/mediapartner/campaigns/mp-view-io-by-campaign-flow.ihtml?c=%s',$ProgramID);
				$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "termsPage_" . date("Ym") . "_{$ProgramID}.dat", $this->batchProgram, $use_true_file_name);
				if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
					$detail_page = $this->GetHttpResultMoreTry($termsUrl, $request);
					if (!$detail_page) {
						mydie("Can't get terms page!");
					} else {
						$this->oLinkFeed->fileCachePut($cache_file, $detail_page);
					}
				}
				$TermsAndConditions = file_get_contents($cache_file);
				
				$arr_prgm[$ProgramID] += array(
					'EPC' => addslashes($EPC),
					'Earnings' => addslashes($Earnings),
					'StarRating' => addslashes($StarRating),
					'Categories' => addslashes($Categories),
					'Status' => addslashes($Status),
					'JoinDate' => addslashes($JoinDate),
					'Commission' => addslashes($Commission),
					'Notifications' => addslashes($Notifications),
					'FundingStatus' => addslashes($FundingStatus),
					'RecentActivity' => addslashes($RecentActivity),
					'ContactName' => addslashes($ContactName),
					'ContactEmail' => addslashes($ContactEmail),
					'ContactPhoneNumber' => addslashes($ContactPhoneNumber),
					'CompanyAddress' => addslashes($CompanyAddress),
					'CompanyContactPhoneNumber' => addslashes($CompanyContactPhoneNumber),
					'AdditionalInformation' => addslashes($AdditionalInformation),
					'ResponseRate' => addslashes($ResponseRate),
					'AcceptanceRate' => addslashes($AcceptanceRate),
					'ServiceAreas' => addslashes($ServiceAreas),
					'CampaignAttributesProductCatalog' => (strpos($prgmAtt[1][0],'True') !== false) ? 'Yes' : 'No',
					'CampaignAttributesMobileSite' => (strpos($prgmAtt[1][1],'True') !== false) ? 'Yes' : 'No',
					'CampaignAttributesUniquePromoCodeTracking' => (strpos($prgmAtt[1][2],'True') !== false) ? 'Yes' : 'No',
					'CampaignAttributesMobileApps' => (strpos($prgmAtt[1][3],'True') !== false) ? 'Yes' : 'No',
					'CampaignAttributesAllowTrademark' => (strpos($prgmAtt[1][4],'True') !== false) ? 'Yes' : 'No',
					'CampaignAttributesPayoutsonGiftCards' => (strpos($prgmAtt[1][5],'True') !== false) ? 'Yes' : 'No',
					'CampaignAttributesPixelPiggyback' => (strpos($prgmAtt[1][6],'True') !== false) ? 'Yes' : 'No',
					'CampaignAttributesDeepLinking' => (strpos($prgmAtt[1][7],'True') !== false) ? 'Yes' : 'No',
					'TermsAndConditions' => addslashes($TermsAndConditions)
				);
				
				if (count($arr_prgm) > 0) {
					$objProgram->updateProgram($this->info["AffID"], $arr_prgm);
					$arr_prgm = array();
				}
			}
		}
		
		if (count($arr_prgm) > 0) {
			$objProgram->updateProgram($this->info["AffID"], $arr_prgm);
			unset($arr_prgm);
		}
		
		echo "\tGet Program by Api end\r\n";
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
	
	function GetHttpResultMoreTry($url, $request, $checkstring = '', $retry = 3)
	{
		$result = '';
		while ($retry) {
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			if ($checkstring) {
				if (strpos($r['content'], $checkstring) !== false) {
					return $result = $r['content'];
				}
			} elseif (!empty($r['content'])) {
				return $result = $r['content'];
			}
			$retry--;
		}
		return $result;
	}
}