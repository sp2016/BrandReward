<?php
require_once 'text_parse_helper.php';


class LinkFeed_Trade_Tracker
{
	
	function __construct($aff_id, $oLinkFeed)
	{
		
	    $this->oLinkFeed = $oLinkFeed;
	    $this->info = $oLinkFeed->getAffById($aff_id);
	    $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
	    $this->customerID = 144691;
	    $this->CompanyID = array(
	       '264725'=>5,				//52    uk
	 	   '264723'=>4,				//65
		   '265244'=>18,			//425
		   '265245'=>20,			//426
	       '261296'=>10,			//427
	       '278842'=>8,			    //2026
		   '265438'=>3,			    //2027
		   '281158'=>19,			//2028
		   '265014'=>11,			//2029
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

		echo "login to affservice\n\t";
		$this->info["AffId"] = $this->info["AffID"];
		$this->oLinkFeed->LoginIntoAffService($this->info["AffID"],$this->info);
		
		$this->site = $this->oLinkFeed->getAccountSiteById($accountid);
		
		foreach($this->site as $v){
		    
			echo 'Site:' . $v['Name']. "\r\n";
			$v['CompanyID'] = $this->CompanyID[$v['SiteIdInAff']];
			$this->GetProgramByApiAndPage($v);
		}
		echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";
		
		$this->CheckBatch();
	}
	
	
	function GetProgramByApiAndPage($Site)
	{
	    echo "\tGet Program by api and page start\r\n";
	    $use_true_file_name = true;
	    $objProgram = new ProgramDb();
	    $arr_prgm = array();
	    $program_num = 0;
	    
	    
	    $request = array(
	        "AffId" => $this->info["AffId"],
	        "method" => "post",
	        "postdata" => "",
	    );
	    
	    //fix site
	    $fixSiteUrl = "https://affiliate.tradetracker.com/customerSite/list?setCompanyID={$Site['CompanyID']}&setCustomerSiteIDs={$Site['SiteIdInAff']}";
	    //echo $fixSiteUrl.PHP_EOL;
	    $this->oLinkFeed->GetHttpResult($fixSiteUrl, $request);
	    
	    
	    $client  = new SoapClient("http://ws.tradetracker.com/soap/affiliate?wsdl", array('trace'=> true));
	    $client->authenticate($this->customerID, $Site['APIKey']);
	    $aff_site_arr = $client->getAffiliateSites();
	    
	    foreach ($client->getCampaigns($Site['SiteIdInAff'], '') as $prgm) {
	        
	        $strMerID = $prgm->ID;
	        if(!$strMerID) continue;
	        
	        $Name = addslashes($prgm->name);
	        $Category = $prgm->info->category->name;
	        
	        //sub category
	        $SubCategories = '';
	        foreach ($prgm->info->subCategories as $subCateValue){
	            $SubCategories .= $subCateValue->name;
	        }
	        $ImpressionCommission = $prgm->info->commission->impressionCommission;
	        $ClickCommission = $prgm->info->commission->clickCommission;
	        $LeadCommission = $prgm->info->commission->leadCommission;
	        $SaleCommissionFixed = 	$prgm->info->commission->saleCommissionFixed;
	        $SaleCommissionVariable =  $prgm->info->commission->saleCommissionVariable;
	        $AttributionModelLead = $prgm->info->attributionModelLead;
	        $AttributionModelSales = $prgm->info->attributionModelSales;
	        $LogoUrl = $prgm->info->imageURL;
	        $HomePage = $prgm->URL;
	        $StartDate = $prgm->info->startDate;
	        $ExclusiveWithTT = ''; //get by page
	        $CookieTime = $prgm->info->clickToConversion;
	        $LeadMaximumAssessmentInterval = $prgm->info->leadMaximumAssessmentInterval;
	        $SaleMaximumAssessmentIntrrval = $prgm->info->saleMaximumAssessmentInterval;
	        $PrePayment = ''; //get by page
	        $Description = $prgm->info->campaignDescription;
	        $Remarks = $prgm->info->characteristics;
	        $Status = $prgm->info->assignmentStatus;
	        $Fees = ''; //get by page
	        $EarningsCurrentMonth = ''; //get by page
	        $EaringsCurrentYear = ''; //get by page
	        $DeeplinkingSupported = $prgm->info->deeplinkingSupported;
	        if($DeeplinkingSupported == 1){
	            $DeeplinkingSupported = "YES";
	        }else{
	            $DeeplinkingSupported = "NO";
	        }	
	        $ReferencesSupported = $prgm->info->referencesSupported;
	        if($ReferencesSupported == 1){
	            $ReferencesSupported = "YES";
	        }else{
	            $ReferencesSupported = "NO";
	        }
	        
	        $PolicySearchEngineMarkingStatus = $prgm->info->policySearchEngineMarketingStatus;
	        $PolicyEmailMarkingStatus = $prgm->info->policyEmailMarketingStatus;
	        $PolicyCashbackStatus = $prgm->info->policyCashbackStatus;
	        $PolicyDiscountCodeStaus = $prgm->info->policyDiscountCodeStatus;
	    
	        $AffDefaultUrl = $prgm->info->trackingURL;
	        $CommissionExt = 'Lead:'.$prgm->info->commission->leadCommission.',Sales:'.$prgm->info->commission->saleCommissionFixed.',Sales(%):'.$prgm->info->commission->saleCommissionVariable;
	        $LogoUrl = $prgm->info->imageURL;
	        $TrackingURL = $prgm->info->trackingURL;
	        
	        //other param by page
	        $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"],"detail_".date("Ym")."_{$strMerID}.dat", $this->batchProgram, $use_true_file_name);
	        if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
	        {
	            $programDetailUrl = "https://affiliate.tradetracker.com/affiliateCampaign/view/ID/{$strMerID}";
	            $programDetailArr = $this->oLinkFeed->GetHttpResult($programDetailUrl, $request);
	            $programDetailInfo = $programDetailArr['content'];
	            $this->oLinkFeed->fileCachePut($cache_file, $programDetailInfo);
	            	
	        }
	        $programDetailInfo = file_get_contents($cache_file);
	        
	        if(preg_match('/<p><strong>Exclusive with TradeTracker<\/strong>:(.*?)<\/p>/', $programDetailInfo,$mate))
	            $ExclusiveWithTT = trim(htmlspecialchars_decode($mate[1]));
	        
	        if(preg_match('/<p><strong>Pre-payment<\/strong>:(.*?)<\/p>/', $programDetailInfo,$matp))
	            $PrePayment = trim(htmlspecialchars_decode($matp[1]));
	        
	        $Fees = trim($this->oLinkFeed->ParseStringBy2Tag($programDetailInfo, array('<h3 class="bottom-border">Fees</h3>', '<div class="dotted-table col-sm-12">'), "</div>"));
	        
	        if(preg_match('/<th>Current month<\/th><td class="text-right">(.*?)<\/td>/', $programDetailInfo,$matm))
	            $EarningsCurrentMonth = trim(htmlspecialchars_decode($matm[1]));
	        
	        if(preg_match('/<th>Current year<\/th><td class="text-right">(.*?)<\/td>/', $programDetailInfo,$maty))
	            $EaringsCurrentYear = trim(htmlspecialchars_decode($maty[1]));
	        
	        
	        $arr_prgm[$strMerID] = array(
	            
	            "SiteID" => $Site['SiteID'],
	            "AccountID" => $this->account['AccountID'],
	            "BatchID" => $this->oLinkFeed->batchid,
	            "AffID" => $this->info["AffID"],
	            "IdInAff" => $strMerID,
	            "Name" => addslashes($Name),
	            "Category"=>addslashes($Category),
	            "SubCategories"=>addslashes($SubCategories),
	            "ImpressionCommission"=>addslashes($ImpressionCommission),
	            "ClickCommission"=>addslashes($ClickCommission),
	            "LeadCommission"=>addslashes($LeadCommission),
	            "SaleCommissionFixed"=>addslashes($SaleCommissionFixed),
	            "SaleCommissionVariable"=>addslashes($SaleCommissionVariable),
	            "SaleCommissionVariable"=>addslashes($SaleCommissionVariable),
	            "AttributionModelLead"=>addslashes($AttributionModelLead),
	            "AttributionModelSales"=>addslashes($AttributionModelSales),
	            "LogoUrl" => addslashes($LogoUrl),
	            "HomePage" => addslashes($HomePage),
	            "StartDate" => addslashes($StartDate),
	            "ExclusiveWithTT"=> addslashes($ExclusiveWithTT),
	            "CookieTime" => addslashes($CookieTime),
	            "LeadMaximumAssessmentInterval" => addslashes($LeadMaximumAssessmentInterval),
	            "SaleMaximumAssessmentIntrrval" => addslashes($SaleMaximumAssessmentIntrrval),
	            "PrePayment" => addslashes($PrePayment),
	            "Description" => addslashes($Description),
	            "Remarks" => addslashes($Remarks),
	            "Status" => addslashes($Status),
	            "Fees" => addslashes($Fees),
	            "EarningsCurrentMonth" => addslashes($EarningsCurrentMonth),
	            "EaringsCurrentYear" => addslashes($EaringsCurrentYear),
	            "DeeplinkingSupported"=> addslashes($DeeplinkingSupported),
	            "ReferencesSupported"=> addslashes($ReferencesSupported),
	            "PolicySearchEngineMarkingStatus" => addslashes($PolicySearchEngineMarkingStatus),
	            "PolicyEmailMarkingStatus" => addslashes($PolicyEmailMarkingStatus),
	            "PolicyCashbackStatus" => addslashes($PolicyCashbackStatus),
	            "PolicyDiscountCodeStaus" => addslashes($PolicyDiscountCodeStaus),
	            "TrackingURL" => addslashes($TrackingURL),
	            
	        );
	        $program_num++;
	        	
	        if(count($arr_prgm) >= 100){
	            $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
	            $arr_prgm = array();
	        }
	    }
	    
	    
	    if(count($arr_prgm)){
	        $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
	        unset($arr_prgm);
	    }
	    
	     
	    echo "\tGet Program by api end\r\n";
	    
	    if($program_num < 10){
	        mydie("die: program count < 10, please check program.\n");
	    }
	    
	    echo "\tUpdate ({$program_num}) program.\r\n";
	}

	
	
    function CheckBatch(){
		$objProgram = new ProgramDb();
		//$this->oLinkFeed->batchid;
		$objProgram->syncBatchToProgram($this->info["AffID"], $this->oLinkFeed->batchid);
	}
}
