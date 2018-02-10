<?php
require_once 'text_parse_helper.php';


class LinkFeed_Link_Connector
{
	
	function __construct($aff_id, $oLinkFeed)
	{
		
	    $this->oLinkFeed = $oLinkFeed;
	    $this->info = $oLinkFeed->getAffById($aff_id);
	    $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
	     
	    $this->API_KEY = 'df5301448794c73f18487f17875ca0b5';
	     
	}
	
	function LoginIntoAffService()
	{
	    //get para __VIEWSTATE and then process default login
	    if(!isset($this->info["AffLoginPostStringOrig"])) $this->info["AffLoginPostStringOrig"] = $this->info["AffLoginPostString"];
	    $request = array(
	        "AffId" => $this->info["AffId"],
	        "method" => "post",
	        "postdata" => "",
	    );
	
	    $strUrl = $this->info["AffLoginUrl"];
	    $r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
	    $result = $r["content"];
	    $arr_hidden_name = array(
	        "curdate" => "",
	        "loginkey" => "",
	    );
	    $pattern = "/<input type=\\\"hidden\\\" name=\\\"(.*?)\\\" value=\\\"(.*?)\\\">/iu";
	    if(!preg_match_all($pattern,$result,$matches)) mydie("die: LoginIntoAffService failed curdate not found\n");
	
	    foreach($matches[1] as $i => $name)
	    {
	        if(isset($arr_hidden_name[$name])) $arr_hidden_name[$name] = $matches[2][$i];
	
	    }
	    foreach($arr_hidden_name as $name => $value)
	    {
	        if(empty($value)) mydie("die: LoginIntoAffService failed $name not found\n");
	    }
	
	    $this->getLoginCheckCode($arr_hidden_name);
	
	    $arr_replace_from = array();
	    $arr_replace_to = array();
	    foreach($arr_hidden_name as $name => $value)
	    {
	        $arr_replace_from[] = "{" . $name . "}";
	        $arr_replace_to[] = $value;
	    }
	
	    $this->info["AffLoginPostString"] = str_replace($arr_replace_from,$arr_replace_to,$this->info["AffLoginPostStringOrig"]);
	    $this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info,2,true,true,false);
	    return "stophere";
	}
	
	function getLoginCheckCode(&$arr)
	{
	    $t2 = strrev("123" . $arr["loginkey"] . $arr["curdate"]);
	    $t = "";
	    for($i=0;$i<strlen($t2);$i+=3)  $t .= $t2[$i];
	    for($i=0;$i<strlen($t2);$i+=2)  $t .= $t2[$i];
	    $arr["dest"] = substr($t,0,32);
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
			$this->GetProgramFromByPage($v['SiteID']);
		}
		echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";
		
		$this->CheckBatch();
	}
	
	
	function GetProgramFromByPage($SiteID)
	{
	    echo "\tGet Program by page start\r\n";
	    $program_num = 0;
	    //step 1,login
	    $this->info["AffId"] = $this->info["AffID"];
	    $this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info,1,false);
	
	    // some program in pending lists, but also in active lists, save the list of active program, and ignore when in pending list
	    $this->active_programs = array();
	
	    //$arrStatus4List = array("Sum","Pending","Declined","Dropped");
	    //ike 20101127, their Declined page is wrong? contains many dup merchants??
	    $arrStatus4List = array("Sum","Pending");
	    foreach($arrStatus4List as $status)
	    {
	        $program_num += $this->getProgramByStatus($SiteID,$status);
	    }
	
	    echo "\tGet Program by page end\r\n";
	
	    if($program_num < 10){
	        mydie("die: program count < 10, please check program.\n");
	    }
	
	    echo "\tUpdate ({$program_num}) program.\r\n";
	}

	function getProgramByStatus($SiteID,$status)
	{
		 
	    $arr_return = array("AffectedCount" => 0,"UpdatedCount" => 0,);
	    $request = array(
	        "AffId" => $this->info["AffId"],
	        "method" => "post",
	        "postdata" => "",
	    );
	    
	    $objProgram = new ProgramDb();
	    $arr_prgm = array();
	    
	    echo "get $status merchants for LC\n";
	    $nNumPerPage = 100;
	    $bHasNextPage = true;
	    $nPageNo = 1;
	    
	    $cnt = 0;
	    
	    $strUrl = "https://www.linkconnector.com/member/amerchants.htm?Type=" . $status;
	    while($bHasNextPage)
	    {
	        $request["postdata"] = "refreshvariable=true&Page=".$nPageNo."&s_sort=&s_order=&ddMerchants=&ddCampaignStatus=Active&ddDisplay=".$nNumPerPage."&ddDisplay=".$nNumPerPage;
	        $r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
	        $result = $r["content"];
	    
	        print "Get $status Merchant List : Page: $nPageNo  <br>\n";
	    
	        //parse HTML
	        $nLineStart = 0;
	        $nTotalPage = $this->oLinkFeed->ParseStringBy2Tag($result, array('per page | Page:','&nbsp;&nbsp;of '), '</td>', $nLineStart);
	        if($nTotalPage === false) mydie("die: nTotalPage not found\n");
	        $nTotalPage = intval($nTotalPage);
	        if($nTotalPage < $nPageNo) break;
	    
	        $nLineStart = 0;
	        $nTmpNoFound = stripos($result, 'No Records Found', $nLineStart);
	        if($nTmpNoFound !== false) break;
	    
	        $strLineStart = '<tr class="lcTable lcTableReport tblRow';
	    
	        $nLineStart = 0;
	        $bStart = true;
	        $item_count = 0;
	        while ($nLineStart >= 0)
	        {
	            //print "Process $Cnt  ";
	            $nLineStart = stripos($result, $strLineStart, $nLineStart);
	            if ($nLineStart === false) break;
	            // Merchant Campaign 	Campaign Type 	Events 	7 EPC 	90 EPC 	# Approved Websites 	Actions
	    
	            $item_count ++;
	            echo "item_count=$item_count","\n";
	            //name
	            $strMerName = $this->oLinkFeed->ParseStringBy2Tag($result, '<td style="text-align:center;" class="lcTable lcTableReport tblCellFirst">', '</td>', $nLineStart);
	            if($strMerName === false) break;
	            $strMerName = html_entity_decode(trim($strMerName));
	            
	            
	            //ID
	            $strCampID = $this->oLinkFeed->ParseStringBy2Tag($result, 'campaigns.htm?cid=', '&mid=', $nLineStart);
	            if($strCampID === false) break;
	            $strCampID = trim($strCampID);
	    
	            $strMerID = $this->oLinkFeed->ParseStringBy2Tag($result, '&mid=', "',", $nLineStart);
	            if($strMerID === false) break;
	            $strMerID = trim($strMerID);
	            if($strMerID == "")
	            {
	                echo "warning: strMerID not found\n";
	                continue;
	            }
	    
	            $mer_detail_url = "https://www.linkconnector.com/member/campaigns.htm?cid=$strCampID&mid=$strMerID";
	            $strMerID = $strMerID.'_'.$strCampID;
	    
	            $strCampName = $this->oLinkFeed->ParseStringBy2Tag($result, array('OnMouseOut', '">'), '</a>', $nLineStart);
	            if($strCampName === false) break;
	            if($strMerName == "")
	            {
	                echo "warning: strMerName not found\n";
	                continue;
	            }
	    
	            $strCampName = html_entity_decode(trim($strCampName));
	            
	            $CampaignType = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('class="lcTable lcTableReport">'), '</td>', $nLineStart));
	            
	             
	            $strEPC7d = $strEPC90d = '';
	            $strEvents = "";
	    
	            if($status == "Sum")
	            {
	                $tofind = '<td style="text-align:center;white-space:nowrap" class="lcTable lcTableReport">';
	                $strEvents = $this->oLinkFeed->ParseStringBy2Tag($result,$tofind,'</td>', $nLineStart);
	                if($strEvents === false)
	                {
	                    echo "warning: strEvents not found\n";
	                    continue;
	                }
	                
	                $strEPC7d = $this->oLinkFeed->ParseStringBy2Tag($result,$tofind, '</td>', $nLineStart);
	                if($strEPC7d === false)
	                {
	                    echo "warning: strEPC not found\n";
	                    continue;
	                }
	    
	                $strEPC90d = $this->oLinkFeed->ParseStringBy2Tag($result,$tofind, '</td>', $nLineStart);
	                if($strEPC90d === false)
	                {
	                    echo "warning: strEPC30d not found\n";
	                    continue;
	                }
	    
	                $strEPC7d = trim($strEPC7d);
	                $strEPC90d = trim($strEPC90d);
	                $ApporvedWebsites = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('OnMouseOut="window.status=\'\';return true">'), '</a>', $nLineStart));
	                 
	                $this->active_programs[] = $strMerID;
	            }
	    
	            if($status == "Pending"){
	                if (!empty($this->active_programs) && in_array($strMerID, $this->active_programs))
	                {
	                    echo sprintf("program id: %s, name: %s is in active program list and ignore.\n", $strMerID, $strMerName);
	                    continue;
	                }
	                $Partnership = 'Pending';
	            }
	            elseif($status == "Declined"){
	                $Partnership = 'Declined';
	            }
	            elseif($status == "Dropped"){
	                $Partnership = 'NoPartnership';
	            }
	            elseif($status == "Sum"){
	                $Partnership = 'Active';
	            }
	            else{
	                mydie("die: wrong status($status)");
	            }
	    
	            $CommissionExt = trim($strEvents);
	            
	    
	            //program_detail
	            
	                $prgm_url = $mer_detail_url;
	                $prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
	                $prgm_detail = $prgm_arr["content"];
	    
	                $prgm_line = 0;
	                $Homepage = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Website:', '<td style="font-weight:bold;text-align:left" class="lcTable lcTableForm tblCellLast">'), '</td>', $prgm_line);
	                
	                $JoinDate = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Start Date:', '<td style="font-weight:bold;text-align:left" class="lcTable lcTableForm tblCellLast">'), '</td>', $prgm_line));
	                if ($JoinDate) {
	                    //$JoinDate = date("Y-m-d H:i:s", strtotime($JoinDate));
	                    $JoinDate_tmp = $JoinDate;
	                    $JoinDate = substr($JoinDate_tmp, 6, 4) . "-" . substr($JoinDate_tmp, 0, 2) . "-" . substr($JoinDate_tmp, 3, 2) . " " . "00:00:00";
	                }
	                
	                $EndDate = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('End Date:', '<td style="font-weight:bold;text-align:left" class="lcTable lcTableForm tblCellLast">'), '</td>', $prgm_line));
	                if ($EndDate) {
	                    //$JoinDate = date("Y-m-d H:i:s", strtotime($JoinDate));
	                    $EndDate_tmp = $EndDate;
	                    $EndDate = substr($EndDate_tmp, 6, 4) . "-" . substr($EndDate_tmp, 0, 2) . "-" . substr($EndDate_tmp, 3, 2) . " " . "00:00:00";
	                }
	                
	    
	                //$prgm_end = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail,array('End Date:','<td style="font-weight:bold;text-align:left" class="lcTable lcTableForm tblCellLast">'),'</td>', $prgm_line);
	                $prgm_status = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Status:', '<td style="font-weight:bold;text-align:left" class="lcTable lcTableForm tblCellLast">'), '</td>', $prgm_line);
	    
	                $desc = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, "<span style='font-weight:bold'>Description: </span>", '</td>', $prgm_line));
	                $TermAndCondition = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array("<span style='font-weight:bold'>Campaign Terms and Conditions: </span>", '<table style="margin:8px 0px">'), '</table>', $prgm_line));
	                
	                //分析 term and condition 
	                preg_match_all('/<tr>([\s\S]*)<\/tr>/iU', $TermAndCondition,$matches);
	                $termAndConditionKey = array(
	                    'TermsAndConditionEmailMarketingAllowed',
	                    'TermsAndConditionSerchEngineMarketingAllowed',
	                    'TermsAndConditionSerchEngineMarketingRestrictions',
	                    'TermsAndConditionCouponsAllowed',
	                    'TermsAndConditionIncentiveOrRewardsSiteAllowed',
	                    'TermsAndConditionCouponInfomation',
	                ); 
	                foreach ($matches[0] as $tam){
	                    
	                    if(stripos($tam, 'Email Marketing Allowed:') !== false){
	                        if(stripos($tam, 'Yes') !== false){
	                            $TermsAndConditionEmailMarketingAllowed = 'Yes';
	                        }
	                    }
	                    
	                    if(stripos($tam, 'Search Engine Marketing Allowed:') !== false){
	                        if(stripos($tam, 'Yes') !== false){
	                            $TermsAndConditionSerchEngineMarketingAllowed = 'Yes';
	                        }
	                    }
	                    
	                    if(stripos($tam, 'Search Engine Marketing Restrictions:') !== false){
	                        preg_match_all('/<td [\s\S]*>([\s\S]*)<\/td>/iU', $tam,$semr);
	                        $TermsAndConditionSerchEngineMarketingRestrictions = $semr[1][1];
	                    }
	                    
	                    if(stripos($tam, 'Coupons Allowed:') !== false){
	                        if(stripos($tam, 'Yes') !== false){
	                            $TermsAndConditionCouponsAllowed = 'Yes';
	                        }
	                    }
	                    
	                    if(stripos($tam, 'Coupon Information:') !== false){
	                        preg_match_all('/<td [\s\S]*>([\s\S]*)<\/td>/iU', $tam,$mci);
	                        $TermsAndConditionCouponInfomation = $mci[1][1];
	                    }
	                    
	                    if(stripos($tam, 'Incentive/Rewards Sites Allowed:') !== false){
	                        if(stripos($tam, 'Yes') !== false){
	                            $TermsAndConditionIncentiveOrRewardsSiteAllowed = 'Yes';
	                        }
	                        else 
	                            $TermsAndConditionIncentiveOrRewardsSiteAllowed = '';
	                        
	                    }
	                    
	                }
	                
	                
	                //OtherTermsAndConditions
	                $otherTCUrl = "https://www.linkconnector.com/member/terms.htm?cid=".$strCampID;
	                $OtherTermsAndConditionsArr = $this->oLinkFeed->GetHttpResult($otherTCUrl, $request);
	                $OtherTermsAndConditions = $OtherTermsAndConditionsArr['content'];
	                
	                //CommissionDetail
	                $CommissionDetail = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, '<table name="" class="lcTable lcTableReport lcTableState" cellpadding="0" cellspacing="0" style="width:100%;height:100%;display:;">', '</table>', $prgm_line));
	                
	            
	                $arr_prgm[$strMerID] = array(
	                    
	                    "SiteID" => $SiteID,
	                    "AccountID" => $this->account['AccountID'],
	                    "BatchID" => $this->oLinkFeed->batchid,
	                    "AffID" => $this->info["AffID"],
	                    "IdInAff" => $strMerID,
	                    "MerchantName" => addslashes(html_entity_decode(trim($strMerName))),
	                    "CampaignName" => addslashes(html_entity_decode(trim($strCampName))),
	                    "CampaignType" => addslashes(trim($CampaignType)),
	                    "Commission" => addslashes($CommissionExt),
	                    "Str7EPC"=> trim($strEPC7d),
	                    "Str90EPC"=> trim($strEPC90d), 
	                    "ApporvedWebsites" => $ApporvedWebsites,
	                    "Partnership" => $Partnership,
	                    "DetailPageUrl" => $prgm_url,
	                    "HomePage" => addslashes(trim($Homepage)),
	                    "StartDate" => addslashes(trim($JoinDate)),
	                    "EndDate" => addslashes(trim($EndDate)),
	                    "CampaignStatus" => addslashes(trim($prgm_status)),
	                    "Description" => addslashes(trim($desc)),
	                    "TermsAndCondition" => addslashes(trim($TermAndCondition)),
	                    "TermsAndConditionEmailMarketingAllowed" => addslashes(trim($TermsAndConditionEmailMarketingAllowed)),
	                    "TermsAndConditionSerchEngineMarketingAllowed" =>addslashes(trim($TermsAndConditionSerchEngineMarketingAllowed)),
	                    "TermsAndConditionSerchEngineMarketingRestrictions" => addslashes(trim($TermsAndConditionSerchEngineMarketingRestrictions)),
	                    "TermsAndConditionCouponsAllowed" => addslashes(trim($TermsAndConditionCouponsAllowed)),
	                    "TermsAndConditionCouponInfomation" => addslashes(trim($TermsAndConditionCouponInfomation)),
	                    "TermsAndConditionIncentiveOrRewardsSiteAllowed" => addslashes(trim($TermsAndConditionIncentiveOrRewardsSiteAllowed)),
	                    "OtherTermsAndConditions" => addslashes(trim($OtherTermsAndConditions)),
	                    "CommissionDetail" => addslashes(trim($CommissionDetail)),
	                );
	                
	            $cnt++;
	            if(count($arr_prgm)){
	                
	                $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
	                $arr_prgm = array();
	            }
	        }
	        if(count($arr_prgm)){
	            $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
	            $arr_prgm = array();
	        }
	    
	        $nPageNo++;
	        
	        if ($nTotalPage < $nPageNo) break;
	    }//per page
	    //exit;
	    if(count($arr_prgm)){
	        $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
	        $arr_prgm = array();
	    }
	    
	    
	    return $cnt;
	    
	}
	
	
    function CheckBatch(){
		$objProgram = new ProgramDb();
		//$this->oLinkFeed->batchid;
		$objProgram->syncBatchToProgram($this->info["AffID"], $this->oLinkFeed->batchid);
	}
}
