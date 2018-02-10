<?php
require_once 'text_parse_helper.php';


class LinkFeed_TAG
{
	
	 
	function GetProgramFromAff($accountid)
	{
	     
		$this->account = $this->oLinkFeed->getAffAccountById($accountid);
		$this->info['AffLoginUrl'] = $this->account['LoginUrl'];	
		$this->info['AffLoginPostString'] = $this->account['LoginPostString'];	
		$this->info['AffLoginVerifyString'] = $this->account['LoginVerifyString'];	
		$this->info['AffLoginMethod'] = $this->account['LoginMethod'];	
		$this->info['AffLoginSuccUrl'] = $this->account['LoginSuccUrl'];	
		$check_date = date("Y-m-d H:i:s");
		
		if(!$this->info['AffLoginUrl']){
		    mydie("die: No Aff Login Url \n");
		}
		
		$affLoginUrlArr =  parse_url($this->info['AffLoginUrl']);
		$this->affDomain = $affLoginUrlArr['scheme'].'://'.$affLoginUrlArr['host'];
		 
		echo "Craw Program start @ {$check_date}\r\n";	

		$this->site = $this->oLinkFeed->getAccountSiteById($accountid);
		//print_r($this->site);exit; 
		foreach($this->site as $v){
			echo 'Site:' . $v['Name']. "\r\n";
			$this->GetProgramFromByPage($v['SiteID']);
		}
		echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";
		
		$this->CheckBatch();
	}
	
	
	function GetProgramFromByPage($SiteID)
	{
	    echo "\tGet Program by page start\r\n";
	    $objProgram = new ProgramDb();
	    $arr_prgm = array();
	    $program_num = 0;
	    
	    //step 1,login
	    $this->info["AffId"] = $this->info["AffID"];
	    $this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
	    $request = array(
	        "AffId" => $this->info["AffId"],
	        "method" => "post",
	        "postdata" => "categoryId=-1&programName=&merchantName=&records=-1&p=&time=1&changePage=&oldColumn=programmeId&sortField=programmeId&order=down",
	        //"postdata" => "p=1&time=1&changePage=&oldColumn=programmeId&sortField=programmeId&order=down&records=-1",
	    );
	                                          
	    $r = $this->oLinkFeed->GetHttpResult($this->affDomain."/affiliate_directory.html",$request);
	    $result = $r["content"];
	    
	    
	    $title = 'PIDProgramNameMIDMerchantNameCategoryCommissionRateCookieDurationAverageApprovalStatus';
	    preg_match("/Affiliate Programs Directory.*?(<th.*?)<\/tr/is", $result, $m);
	    $tmp_arr = explode("</th>",$m[1]);
	    $tmp_title = '';
	    foreach($tmp_arr as $v){
	        $v = preg_replace("/\s/", '', strip_tags($v));
	        if($v){
	            $tmp_title .= $v;
	        }
	    }
	    if($title != $tmp_title){
	        mydie("die: Title Wrong $title | $tmp_title .\n");
	    }
	    
	    //parse HTML
	    $strLineStart = '<th>Cookie Duration</th>';
	    
	    $nLineStart = 0;
	    while ($nLineStart >= 0){
	        $nLineStart = stripos($result, $strLineStart, $nLineStart);
	        if ($nLineStart === false) break;
	        	
	        $strLineStart = "<tr";
	        	
	        //id
	        $strMerID = trim($this->oLinkFeed->ParseStringBy2Tag($result, "<td>", "</td>", $nLineStart));
	        if ($strMerID === false) break;
	        //echo $strMerID;exit;
	        	
	        //name
	        $strMerName = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, '<td>' , "</td>", $nLineStart)));
	        if ($strMerName === false) break;
	        	
	        //2016-09-01 new mid?
	        $tmpId = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, '<td>' , "</td>", $nLineStart)));
	        if ($tmpId === false) break;
	        
	        	
	        //name
	        $tmpName = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>' , "</td>", $nLineStart));
	        if ($tmpName === false) break;
	        	
	         
	        	
	        $CategoryExt = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>' , "</td>", $nLineStart));
	        $CommissionExt  = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>' , "</td>", $nLineStart));
	        $CookieTime  = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>' , "</td>", $nLineStart));
	        $AverageApproval  = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>' , "</td>", $nLineStart));
	        	
	        $StatusInAffRemark = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>' , "</td>", $nLineStart));
	        
	        
	        //program detail
	        $request = array(
                "AffId" => $this->info["AffId"],
                "method" => "get",
            );
    
            $prgm_url = $this->affDomain."/affiliate_program_detail.html?pId=$strMerID";
            $prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
            $prgm_detail = $prgm_arr["content"];
    
            $desc = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Program Description', '<div class="value w70 htmlDescription">'), "</div>"));
            $Homepage = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Program Landing URL', 'opennw(\''), "'")));
            $LogoUrl = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<div class="sideLogo" >','<img src="'), '"'));
    
            $Homepage = str_ireplace("?sourcecode=TAG", "", $Homepage);
    
            $AffDefaultUrl = trim(htmlspecialchars_decode($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('id="trackingString"', '>'), "</")));
    
            $TargetCountryExt = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Country Availability', '<div class="value w70">'), "</div>"));
    
            $TransactionType = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Transaction Type', '<div class="value w70">'), "</div>"));
            
            $CommissionType = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Commission Type', '<div class="value w70">'), "</div>"));
            $CurrentCommissionTier = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Current Commission Tier', '<div class="value w70">'), "</div>"));
            
            $TermAndCondition = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Policy / Terms', '<div class="value w70 htmlDescription">'), "</div>"));
            
            $ProductFeedAvailable = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Product Feed Available', '<div class="value w70">'), "</div>"));
            
            $TrackingCode = trim(htmlspecialchars_decode($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('id="trackingString"', '>'), "</")));
            
            //commission history
            $chUrl = $this->affDomain."/affiliate_program_commission_history.html?pId=$strMerID";
            $ch_arr = $this->oLinkFeed->GetHttpResult($chUrl, $request);
            $ch_detail = $ch_arr["content"];
            $CommissionHistory = trim(htmlspecialchars_decode($this->oLinkFeed->ParseStringBy2Tag($ch_detail, array('<div id="bluetablecontent">'), "</table>")));
            
            $arr_prgm[$strMerID] = array(
                
                "SiteID" => $SiteID,
                "AccountID" => $this->account['AccountID'],
                "BatchID" => $this->oLinkFeed->batchid,
                "AffID" => $this->info["AffID"],
                "IdInAff" => $strMerID,
                "PID" => $strMerID,
                "ProgramName" => addslashes($strMerName),
                "MID" => $tmpId,
                "MerchantName" => addslashes($tmpName),
                "Category" => addslashes($CategoryExt),
                "Commission" => addslashes($CommissionExt),
                "CookieTime" => addslashes($CookieTime),
                "AverageApproval" => addslashes($AverageApproval),
                "Status" => addslashes($StatusInAffRemark),
                "LogoUrl" => addslashes($LogoUrl),
                "Description" => addslashes($desc),
                "ProgramLandingUrl"=> addslashes($Homepage),
                "TargetCountryExt" => addslashes($TargetCountryExt),
                "TransactionType" => addslashes($TransactionType),
                "CommissionType" => addslashes($CommissionType),
                "CurrentCommissionTier" => addslashes($CurrentCommissionTier),
                "PolicyAndTerms" => addslashes($TermAndCondition),
                "ProductFeedAvailable" => addslashes($ProductFeedAvailable),
                "TrackingCode" => addslashes($TrackingCode),
                "CommissionHistory" => addslashes($CommissionHistory),
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
	    
	    echo "\tGet Program by page end\r\n";
	    
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
