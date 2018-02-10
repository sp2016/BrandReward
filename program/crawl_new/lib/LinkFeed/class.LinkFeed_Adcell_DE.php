<?php
require_once 'text_parse_helper.php';
require_once INCLUDE_ROOT."wsdl/adcell_api/adcell.php";

class LinkFeed_Adcell_DE
{
	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		
		$this->token_option = array(
	        		'userName' => '215401',
	    			'password' => '^fdg9ERWKV8E_2ho',
		);
		
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
	
		//step 1, login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffID"], $this->info);
	 	
		//step 2, get program by api
		$api = new AdcellApi();
		$token = $api->getToken($this->token_option);
		 
		//getCategories
		$reponseCategory = $api->category(
				array(
						'token' => $token,
				)
		);
		$cateList = array();
		foreach ($reponseCategory->data->items as $cate){
			$cateList[$cate->categoryId] = $cate;
		}
		$count = 0;
		$page  = 1;
		do{
			echo 'Crawl Page:'.$page.PHP_EOL;
			$programs   = array();
			$programIds = array();
			$reponseData = $api->apply(
					array(
							'token' => $token,
							'page'  => $page
					)
			);
			 
			if($reponseData->status != 200) continue;
			$totalItems = $reponseData->data->total->totalItems;
			$row        = $reponseData->data->rows;
			$lastPage   = ceil($totalItems/$row);
			 
			$count += count($reponseData->data->items);
			//var_dump($reponseData->data->items);exit;
			foreach ($reponseData->data->items as $value)
			{
				$strMerID = $value->programId;
				if (empty($strMerID))
					continue;
				$arr_prgm[$strMerID] = array(
						"SiteID" => $SiteID,
						"AccountID" => $this->account['AccountID'],
						"Name" => addslashes($value->programName),
						"BatchID" => $this->oLinkFeed->batchid,
						"AffID" => $this->info["AffID"],
						"IdInAff" => $strMerID,
						"ProgramUrl" => addslashes($value->programUrl),
						"ProgramLogoUrl" => addslashes($value->programLogoUrl),
						"Description" => addslashes($value->description),
						"CommissionInformation" => addslashes($value->commissionInformation),
						"TermsAndConditions" => addslashes($value->termsAndConditions),
						"TermsSemConstraint" => addslashes($value->termsSemConstraint),
						"TermsCashbackConstraint" => addslashes($value->termsCashbackConstraint),
						"TermsIncentiveConstraint" => addslashes($value->termsIncentiveConstraint),
						"AllowedCountries" => $value->allowedCountries,
						"StartTime" => ($value->startTime != 'n.a.')?date('Y-m-d H:i:s', strtotime($value->startTime)):'',
						"EndTime" => ($value->startTime != 'n.a.')?date('Y-m-d H:i:s', strtotime($value->endTime)):'',
						"IsActive" => addslashes($value->isActive),
						"CookieLifetime" => intval($value->cookieLifetime),
						"PostViewCookieLifetime" => intval($value->postViewCookieLifetime),
						"AveragePaybackPeriod" => intval($value->averagePaybackPeriod),
						"MaximumPaybackPeriod" => intval($value->maximumPaybackPeriod),
						"CancelRatio" => $value->cancelRatio,
						"AffiliateStatus" => addslashes($value->affiliateStatus),
						"ProgramTags" => addslashes($value->programTags),
						"FingerprintTrackingAllowed" => addslashes($value->fingerprintTrackingAllowed),
						
						
				);
				//Commission
				$Commission_arr = array();
				foreach($value->commission->events as $v)
				{
					if ($v->eventType == 'lead')
						$Commission_arr[] = 'Lead: EUR'.$v->commission;
					if ($v->eventType == 'sale')
						$Commission_arr[] = 'Sale: '.$v->commission.'%';
				}
				$arr_prgm[$strMerID]['Commission'] = implode('|', $Commission_arr);
				
				//Category
				$arr_prgm[$strMerID]['Category'] = '';
				$programCategoryIdsArr = explode(',',$value->programCategoryIds);
				if(!empty($programCategoryIdsArr))
				{
					foreach ($programCategoryIdsArr as $cateId){
						if(isset($cateList[(int)$cateId]->categoryName))
							$arr_prgm[$strMerID]['Category'] .=$cateList[(int)$cateId]->categoryName.",";
					}
				}
				
				if (count($arr_prgm) >= 100)
				{
					$objProgram->updateProgram($this->info["AffID"], $arr_prgm);
					unset($arr_prgm);
				}
				$program_num++;
			}
			$page ++ ;
		}while($lastPage>=$page);
		if(count($arr_prgm) > 0)
		{
			$objProgram->updateProgram($this->info["AffID"], $arr_prgm);
			$arr_prgm = array();
		}
		
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