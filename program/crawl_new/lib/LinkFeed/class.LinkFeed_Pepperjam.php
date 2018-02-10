<?php
require_once 'text_parse_helper.php';
require_once 'xml2array.php';

class LinkFeed_Pepperjam
{
	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		
		$this->API_KEY ='dbec64f90d497bca3a139cc8403f752fab6a0ce75855811cc9c56ac1b02ec0f9';
		
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
	
	function GetProgramByApi($SiteID)
	{
		echo "\tGet Program by api start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		$request = array("AffId" => $this->info["AffID"], "method" => "get", "postdata" => "",);
	
		//step 1,login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffID"], $this->info);
		
		//step 2,get program by api
		list($arr_prgm, $program_num, $page, $hasNextPage) = array(array(), 0, 1, true);
		$use_true_file_name = true;
		while($hasNextPage)
		{
			$apiurl = sprintf("http://api.pepperjamnetwork.com/20120402/publisher/advertiser?apiKey=%s&format=json&page=%s", $this->API_KEY, $page);
			//			echo $apiurl;
			//			die;
			$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"],"list_".date("Ym")."_{$page}.dat", $this->batchProgram, $use_true_file_name);
			if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
			{
				$r = $this->oLinkFeed->GetHttpResult($apiurl, $request);
				$results = $r["content"];
				$this->oLinkFeed->fileCachePut($cache_file, $results);
					
			}
			$result = file_get_contents($cache_file);
			$result = json_decode($result);
			if(isset($result->meta->status->code) && $result->meta->status->code==429)
				mydie($result->meta->status->message);
			$total_pages = $result->meta->pagination->total_pages;
			if($page >= $total_pages) $hasNextPage = false;
			$page++;
		
			$advertiser_list = $result->data;
			foreach($advertiser_list as $advertiser)
			{
				$strMerID = $advertiser->id;
				$strMerName = $advertiser->name;
				$desc = $advertiser->description;
				$pattern = '/(Terms & Conditions|terms and conditions)(.*?)$/is';
				if(preg_match($pattern,$desc,$matches)){
					$TermAndCondition = $matches[0];
				}else {
					$TermAndCondition = '';
				}
				$desc = trim(strip_tags($desc));
				
				$category_arr = array();
				foreach ($advertiser->category as $k)
				{
					$category_arr[] = $k->name;
				}
				$Category = implode(',', $category_arr);
				
				$StatusInAff = $advertiser->status;
				if($StatusInAff == "joined"){
					$Partnership = "Active";
				}elseif($StatusInAff == "revoked_advertiser"){
					$Partnership = "Expired";
				}elseif($StatusInAff == "applied"){
					$Partnership = "Pending";
				}elseif($StatusInAff == "declined_advertiser"){
					$Partnership = "Declined";
				}elseif($StatusInAff == "invited"){
					$Partnership = "Pending";
				}elseif($StatusInAff == "revoked_publisher"){
					$Partnership = "Removed";
				}elseif($StatusInAff == "declined_publisher"){
					$Partnership = "Removed";
				}else{
					$Partnership = "NoPartnership";
				}
				if(strstr($desc,'Publishers will not utilize any promotion, promotion code, coupon or other promotional opportunity that is not specifically authorized')){
					$AllowNonaffCoupon = 'NO';
				}elseif(strstr($desc,'Affiliates are not permitted to post any promotional or marketing material ；  ALL affiliates Must Use Current Promotions that are reflected through banners, text links, and coupons in Pepperjam, otherwise they may be subjected to removal from the program.')){
					$AllowNonaffCoupon = 'NO';
				}else{
					$AllowNonaffCoupon = 'UNKNOWN';
				}
				
				if (!empty($advertiser->percentage_payout))
					$Commission = round($advertiser->percentage_payout, 2).'%';
				elseif ($advertiser->flat_payout)
					$Commission = $advertiser->currency.$advertiser->flat_payout;
				
				//get program detail by page
				$detail_url = "http://www.pepperjamnetwork.com/affiliate/program/details?programId=$strMerID";
				$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"],"detail_".date("Ym")."_{$strMerID}.dat", $this->batchProgram, $use_true_file_name);
				if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
				{
					$prgm_arr = $this->oLinkFeed->GetHttpResult($detail_url, $request);
					if($prgm_arr['code'] == 200){
						$results = $prgm_arr['content'];
						$this->oLinkFeed->fileCachePut($cache_file, $results);
					}
				}
				$cache_file = file_get_contents($cache_file);
				$PromotionalMethod = addslashes(trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($cache_file, array('<h3>Promotional Methods</h3>', '<div class="spaced">'), '</div>'))));
				$Keywords = addslashes(trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($cache_file, '<h3>Suggested Keywords:</h3>', '</div>'))));
				$Countries = trim($this->oLinkFeed->ParseStringBy2Tag($cache_file, '<ul id="program-popup-countries">', '</ul>'));
				$Countries = trim(strip_tags(str_replace('</li><li>', ',', $Countries)));
				
				$arr_prgm[$strMerID] = array(
						"SiteID" => $SiteID,
						"AccountID" => $this->account['AccountID'],
						"Name" => addslashes(html_entity_decode(trim($strMerName))),
						"BatchID" => $this->oLinkFeed->batchid,
						"AffID" => $this->info["AffID"],
						"IdInAff" => $strMerID,
						"StatusInAff" => addslashes($StatusInAff),
						"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','Removed'
						"Description" => addslashes($desc),
						"TermAndCondition" => addslashes($TermAndCondition),
						"Category" => addslashes($Category),
						"LogoUrl" => addslashes($advertiser->logo),
						"MobileTracking" => addslashes($advertiser->mobile_tracking),
						"ContactEmail" => addslashes($advertiser->email),
						"ContactTelephone" => addslashes($advertiser->phone),
						"ContactPerson" => addslashes($advertiser->contact_name),
						"ContactAddress" => addslashes($advertiser->address1),
						"ContactCountry" => addslashes($advertiser->country_code),
						"ContactCity" => addslashes($advertiser->city),
						"ZipCode" => addslashes($advertiser->zip_code),
						"HomePage" => addslashes($advertiser->website),
						"Currency" => addslashes($advertiser->currency),
						"JoinDate" => !empty($advertiser->join_date)?date('Y-m-d H:i:s', strtotime($advertiser->join_date)):'',
						"CookieTime" => intval($advertiser->cookie_duration)/24,
						"Commission" => addslashes($Commission),
						"CommissionDetails" => addslashes($Commission),
						"DeepLinking" => strtoupper($advertiser->deep_linking),
						"HasProductFeed" => strtoupper($advertiser->product_feed),
						"AllowNonaffCoupon" => $AllowNonaffCoupon,
						"PromotionalMethod" => addslashes($PromotionalMethod),
						"Keywords" => addslashes($Keywords),
						"Countries" => addslashes($Countries),
						"LastUpdateTime" => date("Y-m-d H:i:s"),
						
				);
				$program_num++;
				if(count($arr_prgm)){
					$objProgram->updateProgram($this->info["AffID"], $arr_prgm);
					$arr_prgm = array();
				}
			}
		}
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffID"], $arr_prgm);
			unset($arr_prgm);
		}
		echo "\tGet Program by api end\r\n";
		if($program_num < 10)
			mydie("die: program count < 10, please check program.\n");
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