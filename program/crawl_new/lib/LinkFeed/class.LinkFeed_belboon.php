<?php
/**
 * User: rzou
 * Date: 2017/8/9
 * Time: 19:06
 */
class LinkFeed_belboon
{
	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		
		$this->batchProgram = date('Ymd') . "_program_" . $this->oLinkFeed->batchid;
		

		
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		$this->soapClient = null;
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
			$this->config = array(
				'login' => $this->account['Account'],
				'password' => $v['APIKey'],
				'trace' => true
			);
			print_r($this->config);
			echo 'Site:' . $v['Name'] .  $v['SiteIdInAff']  . "\r\n";
			$this->GetProgramByApi($v['SiteID'], $v['SiteIdInAff']);
		}
		echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";
		
		$this->CheckBatch();
	}
	
	function GetProgramByApi($SiteID, $SiteIdInAff)
	{
		echo "\tGet Program by Api start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		$use_true_file_name = true;
		$request = array("AffId" => $this->info["AffID"], "method" => "get", "postdata" => "",);
		
		$this->oLinkFeed->LoginIntoAffService($this->info["AffID"], $this->info, 6, true, false, false);
		
		$client  = new SoapClient("http://api.belboon.com/?wsdl", $this->config);
		
		$active_program = array();
		foreach(array('PARTNERSHIP', null) as $partnershipStatus){
			try {
				$client  = new SoapClient("http://api.belboon.com/?wsdl", $this->config);
				$page = 1;
				$hasNextPage = true;
				while($hasNextPage){
					echo "Page:{$page}\t";
					
					$limit = 100;
					$start = $limit * ($page - 1);
					$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "list_" . date("Ym") . "_data_page{$page}.dat", $this->batchProgram, $use_true_file_name);
					if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
						$result = $client->getPrograms(
							$SiteIdInAff, // adPlatformId
							null, // programLanguage
							$partnershipStatus, // partnershipStatus
							null, // query
							array('programid' => 'ASC'), // orderBy
							$limit, // limit
							$start // offset
						);
						$result = json_encode($result);
						$this->oLinkFeed->fileCachePut($cache_file, $result);
					}
					$result = json_decode(file_get_contents($cache_file),true);
					
					if (!isset($result['handler']['programs'])) {
						mydie('Can\'t get data, please check the api !');
					}
					
					if(empty($result['handler']['programs'])){
						$hasNextPage = false;
						break;
					}
					if($page > 100){
						mydie("die: page max > 100.\n");
					}
					
					foreach($result['handler']['programs'] as $prgm){
						$ProgramID = intval($prgm['programid']);
						if(!$ProgramID) continue;
						
						if(isset($active_program[$ProgramID])){
							continue;
						}
						if($prgm['partnershipstatus'] == 'PARTNERSHIP'){
							$active_program[$ProgramID] = intval($prgm['partnershipid']);
						}
						
						//$result = $client->getProgramDetails(342);
						$arr_prgm[$ProgramID] = array(
							"SiteID" => $SiteID,
							"AccountID" => $this->account['AccountID'],
							"AffID" => $this->info["AffID"],
							"IdInAff" => $ProgramID,
							'ProgramID' => $ProgramID,
							'BatchID' => $this->oLinkFeed->batchid,
							"Name" => addslashes($prgm['programname']),
							'ProgramLanguage' => addslashes($prgm['programlanguage']),
							'ProgramDescription' => addslashes($prgm['programdescription']),
							'ProgramTerms' => addslashes($prgm['programterms']),
							'AdvertiserUrl' => addslashes($prgm['advertiserurl']),
							'ProgramLogo' => addslashes($prgm['programlogo']),
							'ProgramCurrency' => addslashes($prgm['programcurrency']),
							'CommissionViewMin' => addslashes($prgm['commissionviewmin']),
							'CommissionViewMax' => addslashes($prgm['commissionviewmax']),
							'CommissionClickMin' => addslashes($prgm['commissionclickmin']),
							'CommissionClickMax' => addslashes($prgm['commissionclickmax']),
							'CommissionLeadMin' => addslashes($prgm['commissionleadmin']),
							'CommissionLeadMax' => addslashes($prgm['commissionleadmax']),
							'CommissionSaleMinFix' => addslashes($prgm['commissionsaleminfix']),
							'CommissionSaleMaxFix' => addslashes($prgm['commissionsalemaxfix']),
							'CommissionSaleMinPercent' => addslashes($prgm['commissionsaleminpercent']),
							'CommissionSaleMaxPercent' => addslashes($prgm['commissionsalemaxpercent']),
							'PartnershipStatus' => addslashes($prgm['partnershipstatus']),
							'ProgramRegisterUrl' => addslashes($prgm['programregisterurl']),
						);
						
						$detailPage = sprintf('https://ui.belboon.com/ShowApplicationOverview,mid.41/DoHandleApplication,programid.%s,platformid.%s.en.html#t=overview', $ProgramID, $SiteIdInAff);
						$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "detail_" . date("Ym") . "_{$ProgramID}.dat", $this->batchProgram, $use_true_file_name);
						if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
							$detail_page = $this->GetHttpResultMoreTry($detailPage, $request);
							if (!$detail_page) {
								mydie("Can't get detailpage!");
							} else {
								$this->oLinkFeed->fileCachePut($cache_file, $detail_page);
							}
						}
						$result = file_get_contents($cache_file);
						$result = preg_replace('@>\s+<@','><',$result);
						
						$strPosition = 0;
						if ($prgm['partnershipstatus'] == 'PARTNERSHIP') {
							$StartOfPartnership = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('Start of partnership:', 'txtbold">'), '</', $strPosition));
							$LastChange = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('Last change:', 'txtbold">'), '</', $strPosition));
						}
						
						$SEM = trim($this->oLinkFeed->ParseStringBy2Tag($result, '>SEM', '<'));
						$Keywords = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('Keywords:','>'), '</div')));
						$CommissionDetail = $this->oLinkFeed->ParseStringBy2Tag($result, array('class="conditiontable"','>'), '</div>', $strPosition);
						
						$AutoAccept = $this->oLinkFeed->ParseStringBy2Tag($result, array('AutoAccept:','valign="top">'), '</td', $strPosition);
						$ForcedClicksAreAllowed = $this->oLinkFeed->ParseStringBy2Tag($result, array('Forced clicks are allowed:','valign="top">'), '</td', $strPosition);
						$ReleasedForTheFollowingAdPlatformTypes = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('Released for the following AdPlatform types:','">'), '</td', $strPosition)));
						$CookieLifetimeUnit = trim($this->oLinkFeed->ParseStringBy2Tag($result, 'Cookie Lifetime', ':', $strPosition));
						$CookieLifetime = trim($this->oLinkFeed->ParseStringBy2Tag($result, 'class="txtbold" valign="top">', '</td', $strPosition)) . $CookieLifetimeUnit;
						$TrackingTechniques = strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('Tracking Techniques:','valign="top">'), '</td', $strPosition));
						
						$TradingArea = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('Trading area:','>'), '<br', $strPosition));
						$StartOfTheAffiliateProgram = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, 'Start of the affiliate program:', '<strong', $strPosition)));
						$EndOfTheAffiliateProgram = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, 'End of the affiliate program:', '</div', $strPosition)));
						
						$EPHC90Day = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, '90 Day EPHC:', '</td', $strPosition)));
						$AverageProcessingTime = strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, 'Average processing time:', '</td', $strPosition));
						$EPHCOfTheBestPublisher90Days = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, '90 Days EPHC of the best publisher:', '</td', $strPosition)));
						$CancellationRate = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, 'Cancellation Rate:', '</td', $strPosition)));
						
						$AdvertiserContactUrl ='https://ui.belboon.com' . trim($this->oLinkFeed->ParseStringBy2Tag($result, array('class="content_links_double"','a href="'), '"', $strPosition));
						$AffContactName = trim($this->oLinkFeed->ParseStringBy2Tag($result, 'title="Your belboon contact person for this partner program:', '"', $strPosition));
						$AffContactPhone = $this->oLinkFeed->ParseStringBy2Tag($result, 'div>Phone:', '</div', $strPosition);
						$AffContactEmail = $this->oLinkFeed->ParseStringBy2Tag($result, 'a href="mailto:', '"', $strPosition);
						
						$arr_prgm[$ProgramID] += array(
							'StartOfPartnership' => isset($StartOfPartnership) ? addslashes($StartOfPartnership) : '',
							'LastChange' => isset($LastChange) ? addslashes($LastChange) : '',
							'SEM' => addslashes($SEM),
							'Keywords' => addslashes($Keywords),
							'CommissionDetail' => addslashes($CommissionDetail),
							'AutoAccept' => addslashes($AutoAccept),
							'ForcedClicksAreAllowed' => addslashes($ForcedClicksAreAllowed),
							'ReleasedForTheFollowingAdPlatformTypes' => addslashes($ReleasedForTheFollowingAdPlatformTypes),
							'CookieLifetime' => addslashes($CookieLifetime),
							'TrackingTechniques' => addslashes($TrackingTechniques),
							'TradingArea' => addslashes($TradingArea),
							'StartOfTheAffiliateProgram' => addslashes($StartOfTheAffiliateProgram),
							'EndOfTheAffiliateProgram' => addslashes($EndOfTheAffiliateProgram),
							'EPHC90Day' => addslashes($EPHC90Day),
							'AverageProcessingTime' => addslashes($AverageProcessingTime),
							'EPHCOfTheBestPublisher90Days' => addslashes($EPHCOfTheBestPublisher90Days),
							'CancellationRate' => addslashes($CancellationRate),
							'AdvertiserContactUrl' => addslashes($AdvertiserContactUrl),
							'AffContactName' => addslashes($AffContactName),
							'AffContactPhone' => addslashes($AffContactPhone),
							'AffContactEmail' => addslashes($AffContactEmail),
						);
						
						$program_num++;
						
						if(count($arr_prgm)){
							$objProgram->updateProgram($this->info["AffID"], $arr_prgm);
							$arr_prgm = array();
						}
					}
					$page++;
				}
			} catch( Exception $e ) {
				mydie("die: Api error . $e\n");
			}
			if(count($arr_prgm)){
				$objProgram->updateProgram($this->info["AffID"], $arr_prgm);
				$arr_prgm = array();
			}
			if($partnershipStatus == 'PARTNERSHIP'){
				$Status = 'Active';
			}else{
				$Status = 'NoActive';
			}
			echo "\tUpdate siteID is $SiteIdInAff ($Status) : ({$program_num}) program.\r\n";
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