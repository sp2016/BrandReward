<?php
/**
 * User: rzou
 * Date: 2017/8/4
 * Time: 19:34
 */
class LinkFeed_Commission_Factory
{
	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		
		$this->batchProgram = date('Ymd')."_program_".$this->oLinkFeed->batchid;
	}
	
	function Login()
	{
		$strUrl = "https://dashboard.commissionfactory.com/LogIn/";
		$request = array(
			"method" => "get",
			"postdata" => "",
		);
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		
		$result = $r["content"];
		$__EVENTVALIDATION = urlencode($this->oLinkFeed->ParseStringBy2Tag($result, array('name="__EVENTVALIDATION"', 'value="'), '"'));
		$__VIEWSTATE = urlencode($this->oLinkFeed->ParseStringBy2Tag($result, array('name="__VIEWSTATE"', 'value="'), '"'));
		$strUrl = "https://dashboard.commissionfactory.com/LogIn/";
		$request = array(
			"AffId" => $this->info["AffID"],
			"method" => "post",
			"postdata" => "",
		);
		$request["postdata"] = "ctl05=ctl05%7CbtnLogIn&{$this->account['LoginPostString']}&txtResetPassword=&txtContactFirstName=&txtContactLastName=&txtContactEmail=&txtMerchFullName=&txtMerchCompany=&txtMerchEmail=&txtMerchPhone=&txtMerchWebsite=&txtAgencyFullName=&txtAgencyCompany=&txtAgencyEmail=&txtAgencyPhone=&txtAgencyWebsite=&__EVENTTARGET=&__EVENTARGUMENT=&__LASTFOCUS=&__VIEWSTATE={$__VIEWSTATE}&__VIEWSTATEGENERATOR=25748CED&__EVENTVALIDATION={$__EVENTVALIDATION}&__ASYNCPOST=true&btnLogIn=Log%20In";
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];
		if(stripos($result,'Affiliate') === false)
		{
			mydie("die: failed to login.\n");
		} else {
			echo "login succ.\n";
		}
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
			$this->GetProgramByApi($v['SiteID'],$v['APIKey']);
		}
		echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";
		
		$this->CheckBatch();
	}
	
	function GetProgramByApi($SiteID,$APIKey)
	{
		echo "\tGet Program by Api start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		$use_true_file_name = true;
		$request = array("AffId" => $this->info["AffID"], "method" => "get", "postdata" => "",);
		
		//step 1,login
		$this->Login();
		
		$strUrl = sprintf("https://api.commissionfactory.com/V1/Affiliate/Merchants?apiKey=%s", $APIKey);
		
		$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "list_" . date("Ym") . "_data_API.dat", $this->batchProgram, $use_true_file_name);
		if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
			$results = $this->GetHttpResultMoreTry($strUrl, $request);
			if (!$results) {
				mydie("Can't get API data!");
			}
			$this->oLinkFeed->fileCachePut($cache_file, $results);
		}
		$result = file_get_contents($cache_file);
		$result = json_decode($result, true);
		
		foreach ($result as $v) {
			$ProgramID = intval($v['Id']);
			if ($ProgramID < 1)
				continue;
			
			$Categories = trim($v['Category']);
			if (!empty($v['Category2'])) {
				$Categories .= ',' . trim($v['Category2']);
			}
			if (!empty($v['Category3'])) {
				$Categories .= ',' . trim($v['Category3']);
			}
			
			$arr_prgm[$ProgramID] = array(
				"SiteID" => $SiteID,
				"AccountID" => $this->account['AccountID'],
				'AffID' => $this->info['AffID'],
				'IdInAff' => $ProgramID,
				'ProgramID' => $ProgramID,
				'BatchID' => $this->oLinkFeed->batchid,
				'Name' => addslashes(trim($v['Name'])),
				'DateCreated' => addslashes(trim($v['DateCreated'])),
				'DateModified' => addslashes(trim($v['DateModified'])),
				'AvatarUrl' => addslashes(trim($v['AvatarUrl'])),
				'CommissionType' => addslashes(trim($v['CommissionType'])),
				'CommissionRate' => addslashes(trim($v['CommissionRate'])),
				'Categories' => addslashes($Categories),
				'Summary' => addslashes(trim($v['Summary'])),
				'TargetUrl' => addslashes(trim($v['TargetUrl'])),
				'Status' => addslashes(trim($v['Status'])),
				'TrackingUrl' => addslashes(trim($v['TrackingUrl'])),
				'TrackingCode' => addslashes(trim($v['TrackingCode'])),
				'TargetMarket' => addslashes(trim($v['TargetMarket'])),
				'TermsAndConditions' => addslashes($v['TermsAndConditions']),
			);
			
			$DetailPage = sprintf('https://dashboard.commissionfactory.com/Affiliate/Merchants/%s/', $ProgramID);
			
			$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "detail_" . date("Ym") . "_{$ProgramID}.dat", $this->batchProgram, $use_true_file_name);
			if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
				$detail_page = $this->GetHttpResultMoreTry($DetailPage, $request);
				if (!$detail_page) {
					mydie("Can't get detailpage!");
				} else {
					$this->oLinkFeed->fileCachePut($cache_file, $detail_page);
				}
			}
			$result = file_get_contents($cache_file);
			$result = preg_replace('@>\s+<@', '><', $result);
			
			$strPosition = 0;
			$Message = $this->oLinkFeed->ParseStringBy2Tag($result, array('class="divSocialLinks"', 'Message Merchant'), '>', $strPosition);
			preg_match('@href="(.*)"@i', $Message, $mess);
			$MessageSupportUrl = (isset($mess[1]) && !empty($mess[1])) ? trim($mess[1]) : '';
			if (!empty($MessageSupportUrl) && strpos($MessageSupportUrl,'http') === false) {
				$MessageSupportUrl = 'https://dashboard.commissionfactory.com/Affiliate' . str_replace('../..','',$MessageSupportUrl);
			}
			
			$twitter = $this->oLinkFeed->ParseStringBy2Tag($result, 'social_twitter', '>', $strPosition);
			preg_match('@href="([^"]*)"@i', $twitter, $tt);
			$TwitterSupportUrl = (isset($tt[1]) && !empty($tt[1])) ? trim($tt[1]) : '';
			
			$facebook = $this->oLinkFeed->ParseStringBy2Tag($result, 'social_facebook', '>', $strPosition);
			preg_match('@href="([^"]*)"@i', $facebook, $fb);
			$FacebookSupportUrl = (isset($fb[1]) && !empty($fb[1])) ? trim($fb[1]) : '';
			
			$instagram = $this->oLinkFeed->ParseStringBy2Tag($result, 'social_instagram', '>', $strPosition);
			preg_match('@href="([^"]*)"@i', $instagram, $inst);
			$InstagramSupportUrl = (isset($inst[1]) && !empty($inst[1])) ? trim($inst[1]) : '';
			
			$googleplus = $this->oLinkFeed->ParseStringBy2Tag($result, 'social_googleplus', '>', $strPosition);
			preg_match('@href="([^"]*)"@i', $googleplus, $ggl);
			$GoogleplusSupportUrl = (isset($ggl[1]) && !empty($ggl[1])) ? trim($ggl[1]) : '';
			
			$linkedin = $this->oLinkFeed->ParseStringBy2Tag($result, 'social_linkedin', '>', $strPosition);
			preg_match('@href="([^"]*)"@i', $linkedin, $lkd);
			$LinkedinSupportUrl = (isset($lkd[1]) && !empty($lkd[1])) ? trim($lkd[1]) : '';
			
			$Commission = $this->oLinkFeed->ParseStringBy2Tag($result, array('class="programInfo', 'div>'), '</div', $strPosition);
			$Commission = strip_tags($Commission);
			
			$TrackingPeriod = $this->oLinkFeed->ParseStringBy2Tag($result, 'div>', '</div', $strPosition);
			$TrackingPeriod = strip_tags($TrackingPeriod, '<br />');
			$TrackingPeriod = preg_replace('<br />', ' ', $TrackingPeriod);
			
			$ValidationPeriod = $this->oLinkFeed->ParseStringBy2Tag($result, 'div>', '</div', $strPosition);
			$ValidationPeriod = strip_tags($ValidationPeriod, '<br />');
			$ValidationPeriod = preg_replace('<br />', ' ', $ValidationPeriod);
			
			$MobileOptimised = $this->oLinkFeed->ParseStringBy2Tag($result, array('cphBody_cphBody_divWebsiteMobile', 'class="'), '"', $strPosition);
			$MobileOptimised = (strpos($MobileOptimised, 'inactive') !== false) ? 'NO' : 'Yes';
			
			$WorldwideShipping = $this->oLinkFeed->ParseStringBy2Tag($result, array('cphBody_cphBody_divWebsiteInternational', 'class="'), '"', $strPosition);
			$WorldwideShipping = (strpos($WorldwideShipping, 'inactive') !== false) ? 'NO' : 'Yes';
			
			$ClicklessTracking = $this->oLinkFeed->ParseStringBy2Tag($result, array('cphBody_cphBody_divClicklessTracking', 'class="'), '"', $strPosition);
			$ClicklessTracking = (strpos($ClicklessTracking, 'inactive') !== false) ? 'NO' : 'Yes';
			
			$InviteOnlyProgram = $this->oLinkFeed->ParseStringBy2Tag($result, array('cphBody_cphBody_divInviteOnly', 'class="'), '"', $strPosition);
			$InviteOnlyProgram = (strpos($InviteOnlyProgram, 'inactive') !== false) ? 'NO' : 'Yes';
			
			$Description = strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('class="bodyLeft"', 'Description'), '</div>', $strPosition));
			$CommissionStructureOverview = strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, 'Commission structure overview', '</div>'));
			
			$strpos = 0;
			$ManagerName = $this->oLinkFeed->ParseStringBy2Tag($result, array('cphBody_cphBody_pnlManagedBy', '<b', '>'), '</b', $strpos);
			$ManagerContact = $this->oLinkFeed->ParseStringBy2Tag($result, array('cphBody_cphBody_lnkMessageMerchant', 'href="'), '"', $strpos);
			if (!empty($ManagerContact) && strpos($ManagerContact,'http') === false) {
				$ManagerContact = 'https://dashboard.commissionfactory.com/Affiliate' . str_replace('../..','',$ManagerContact);
			}
			
			preg_match_all('@p style="color:#575757;">([a-zA-Z& ]*)</p@i', $result, $pb);
			if (isset($pb[1]) && !empty($pb[1])) {
				$PopularBrands = implode(',', $pb[1]);
			} else {
				$PopularBrands = '';
			}
			
			$Restrictions_str = $this->oLinkFeed->ParseStringBy2Tag($result, ' Program Restrictions', 'Traffic sources');
			preg_match_all('@div class="divTagPill ([a-z]+)"@i', $Restrictions_str, $Restrictions);
			if (!isset($Restrictions[1]) || empty($Restrictions[1]) || count($Restrictions[1]) != 21) {
				//mydie("Program Restrictions content have changed!");
			}
			$Restrictions_arr = $Restrictions[1];
			
			$arr_prgm[$ProgramID] += array(
				'MessageSupportUrl' => addslashes($MessageSupportUrl),
				'TwitterSupportUrl' => addslashes($TwitterSupportUrl),
				'FacebookSupportUrl' => addslashes($FacebookSupportUrl),
				'InstagramSupportUrl' => addslashes($InstagramSupportUrl),
				'GoogleplusSupportUrl' => addslashes($GoogleplusSupportUrl),
				'LinkedinSupportUrl' => addslashes($LinkedinSupportUrl),
				'Commission' => addslashes($Commission),
				'TrackingPeriod' => addslashes($TrackingPeriod),
				'ValidationPeriod' => addslashes($ValidationPeriod),
				'MobileOptimised' => addslashes($MobileOptimised),
				'WorldwideShipping' => addslashes($WorldwideShipping),
				'ClicklessTracking' => addslashes($ClicklessTracking),
				'InviteOnlyProgram' => addslashes($InviteOnlyProgram),
				'Description' => addslashes($Description),
				'CommissionStructureOverview' => addslashes($CommissionStructureOverview),
				'ManagerName' => addslashes($ManagerName),
				'ManagerContact' => addslashes($ManagerContact),
				'PopularBrands' => addslashes($PopularBrands),
				'ContentSite' => addslashes(trim($Restrictions_arr[0])),
				'CouponSites' => addslashes(trim($Restrictions_arr[1])),
				'BlogSite' => addslashes(trim($Restrictions_arr[2])),
				'DirectorySite' => addslashes(trim($Restrictions_arr[3])),
				'ContentSyndication' => addslashes(trim($Restrictions_arr[4])),
				'ProductAggregator' => addslashes(trim($Restrictions_arr[5])),
				'DealAggregator' => addslashes(trim($Restrictions_arr[6])),
				'MediaBuying' => addslashes(trim($Restrictions_arr[7])),
				'DisplayNetwork' => addslashes(trim($Restrictions_arr[8])),
				'EmailMarketing' => addslashes(trim($Restrictions_arr[9])),
				'Loyalty' => addslashes(trim($Restrictions_arr[10])),
				'PriceComparisonSites' => addslashes(trim($Restrictions_arr[11])),
				'SocialMedia' => addslashes(trim($Restrictions_arr[12])),
				'CPANetworks' => addslashes(trim($Restrictions_arr[13])),
				'IncentivisedCashbackSite' => addslashes(trim($Restrictions_arr[14])),
				'Video' => addslashes(trim($Restrictions_arr[15])),
				'Software' => addslashes(trim($Restrictions_arr[16])),
				'MobileApp' => addslashes(trim($Restrictions_arr[17])),
				'CartAbandonment' => addslashes(trim($Restrictions_arr[18])),
				'OnSiteAbandonment' => addslashes(trim($Restrictions_arr[19])),
				'PaidSearch' => addslashes(trim($Restrictions_arr[20])),
			);
			
			$program_num++;
			
			if (count($arr_prgm) > 10) {
				$objProgram->updateProgram($this->info["AffID"], $arr_prgm);
				$arr_prgm = array();
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

?>