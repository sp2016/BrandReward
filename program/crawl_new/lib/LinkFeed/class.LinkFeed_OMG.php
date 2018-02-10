<?php
require_once 'text_parse_helper.php';


class LinkFeed_OMG
{
	
    
    function __construct($aff_id, $oLinkFeed)
    {
    
        $this->oLinkFeed = $oLinkFeed;
        $this->info = $oLinkFeed->getAffById($aff_id);
        $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
         
        
         
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

		$this->info["AffId"] = $this->info["AffID"];
		$this->site = $this->oLinkFeed->getAccountSiteById($accountid);
		 
		foreach($this->site as $v){
		    
			echo 'Site:' . $v['Name']. "\r\n";
			$this->GetProgramByApiAndPage($v);
		}
		echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";
		
		$this->CheckBatch();
	}
	
	
	function GetProgramByApiAndPage($Site)
	{
	   
	    echo "\tGet Program by api start\r\n";
	    $objProgram = new ProgramDb();
	    $arr_prgm = array();
	    $program_num = 0;
	    $use_true_file_name = true;
	    $Agency = array(
	        '1023249'=>array('Agency'=>118,'private_key'=>'515f6b7921b241dc93397096175e2449'),
	        '1030347'=>array('Agency'=>95,'private_key'=>'bf6264dd1f664e549e7ab0b7c7b7ffd7'),
	        '1059391'=>array('Agency'=>172,'private_key'=>'bafc339fc6fb40ce969cf8a8d40a2774'),
	    );
	    
	    $request = array(
	        "AffId" => $this->info["AffId"],
	        "method" => "get",
	    );
	    
	    //login
	    $this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
	    
	    //find  var AgencyId = '95';
	    /*$DashboardUrl = "https://admin.optimisemedia.com/v2/Dashboard/NewAffiliate.aspx";
	    $DashboarArr = $this->oLinkFeed->GetHttpResult($DashboardUrl,$request);
	    if($DashboarArr['code'] == 200){
	        if(preg_match('/AgencyId = \'(\d+)\'/', $DashboarArr['content'],$matchAgencyId)){
	            $Agency = $matchAgencyId[1];
	        }
	    }else{
	        mydie("die: , failed get AgencyId,url: $DashboardUrl");
	    }*/
	    $AgencyId = $Agency[$Site['SiteIdInAff']]['Agency'];
	    //echo $AgencyId;exit;
	    
	    
	    $Affiliate = $Site['SiteIdInAff'];
	    
	    //get Details url start
	    date_default_timezone_set("UTC");
	    $t = microtime(true);
	    $micro = sprintf("%03d",($t - floor($t)) * 1000);
	    $utc = gmdate('Y-m-d H:i:s.', $t).$micro;
	    $sig_data= $utc;
	    $API_Key = $Site['APIKey'];
	    $private_key = $Agency[$Site['SiteIdInAff']]['private_key'];
	    
	    $concateData = $private_key.$sig_data;
	    $sig = md5($concateData);
	    $progm_url = "https://api.omgpm.com/network/OMGNetworkApi.svc/v1.2/GetProgrammes?AID=$Affiliate&AgencyID=$AgencyId&CountryCode=&Key=$API_Key&Sig=$sig&SigData=".urlencode($sig_data);
	     
	    //http://admin.optimisemedia.com/v2/Reports/Affiliate/ProgrammesExport.aspx?Agency=118&Country=0&Affiliate=1023249&Search=&Sector=0&UidTracking=False&PayoutTypes=&ProductFeedAvailable=False&Format=XML&AuthHash=370DB2E3E2C949D8B9E2134D43DD025B&AuthAgency=118&AuthContact=1023249&ProductType=0
	    date_default_timezone_set("America/Los_Angeles");
	    
	    // get program from csv.
	    $str_header = "Product Feed Available";
	    $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "list_" . date("Ym") . "_data_API_CSV.dat", $this->batchProgram, $use_true_file_name);
	    if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
	    {
	        $r = $this->oLinkFeed->GetHttpResult($progm_url,$request);
	        $result = $r["content"];
	        //var_dump($result);exit;
	        if(stripos($result,$str_header) === false) mydie("die: wrong csv file: $cache_file, url: $progm_url");
	        $this->oLinkFeed->fileCachePut($cache_file,$result);
	    }
	    
	    //Open CSV File
	    $fhandle = file_get_contents($cache_file, 'r');
	    $res = json_decode($fhandle,true);
	    $res = $res['GetPublisherProgrammesResult'];
	    
	    foreach($res as $key=>$k)
	    {
	        //if(!$k['CampaignID']){
	        //    continue;
	        //}
	        
	        $MerchantName = $k['Merchant Name'];
	        $PID = intval($k['PID']);
	        $CampaignName = $k['Product Name'];
	        $Commission = $k['Commission'];
	        $PayoutType = $k['Payout Type'];
	        $Country = trim($k['Country Code']);
	        $Platform = ''; //get by page
	        $CookieTime = $k['Cookie Duration'];
	        $Status = trim($k['Programme Status']);
	        $LogoUrl = $k['Merchant Logo URL'];
	        
	        //program Detail
	        $ContactWebsiteID = $k['Contact WebsiteID'];
	        $cache_file = '';
	        $request = array(
	            "AffId" => $this->info["AffId"],
	            "method" => "get",
	        );
	        $prgmDetail_url = "https://admin.optimisemedia.com/v2/programmes/affiliate/viewprogramme.aspx?ProductID=$PID&ContactWebsiteID=$ContactWebsiteID";
	        $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"],"detail_".date("Ym")."_{$PID}_{$ContactWebsiteID}.dat", $this->batchProgram, $use_true_file_name);
	        if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
	        {
	            $r = $this->oLinkFeed->GetHttpResult($prgmDetail_url,$request);
	            if($r['code'] == 200){
	                $result = $r["content"];
	                $this->oLinkFeed->fileCachePut($cache_file,$result);
	            }
	            
	        }
	        $programDetailInfo = file_get_contents($cache_file, 'r');
	        $DailyCap = trim($this->oLinkFeed->ParseStringBy2Tag($programDetailInfo, array('Daily Cap','borderBottom','<p>'), '</p>'));
	        
	        $Category = trim($this->oLinkFeed->ParseStringBy2Tag($programDetailInfo, array('Sector','borderBottom','<p>'), '</p>'));
	        $ProductFeed = trim($this->oLinkFeed->ParseStringBy2Tag($programDetailInfo, array('Product Feed:','borderBottom','<p>'), '</p>'));
	        $UidAndSubId = trim($this->oLinkFeed->ParseStringBy2Tag($programDetailInfo, array('UID/SubID:','borderBottom','<p>'), '</p>'));
	        $FirstAndLastCookie = trim($this->oLinkFeed->ParseStringBy2Tag($programDetailInfo, array('First/Last Cookie','borderBottom','<p>'), '</p>'));
	        $Cashback = trim($this->oLinkFeed->ParseStringBy2Tag($programDetailInfo, array('Cashback:','borderBottom','<p>'), '</p>'));
	        $VoucherAndCoupon = trim($this->oLinkFeed->ParseStringBy2Tag($programDetailInfo, array('Voucher/Coupon:','borderBottom','<p>'), '</p>'));
	        $BrandBidding = trim($this->oLinkFeed->ParseStringBy2Tag($programDetailInfo, array('Brand Bidding:','borderBottom','<p>'), '</p>'));
	        $CampaignRestrictionEmail = trim($this->oLinkFeed->ParseStringBy2Tag($programDetailInfo, array('Email:','borderBottom','<p>'), '</p>'));
	        $CampaignRestrictionSocialMedia = trim($this->oLinkFeed->ParseStringBy2Tag($programDetailInfo, array('Social Media:','borderBottom','<p>'), '</p>'));
	        $CampaignRestrictionBehavioralRetargeting = trim($this->oLinkFeed->ParseStringBy2Tag($programDetailInfo, array('Behavioral retargeting:','borderBottom','<p>'), '</p>'));
	        $CampaignRestrictionAdultTraffic = trim($this->oLinkFeed->ParseStringBy2Tag($programDetailInfo, array('Adult traffic:','borderBottom','<p>'), '</p>'));
	        $CampaignRestrictionPopUpAndPopUnder = trim($this->oLinkFeed->ParseStringBy2Tag($programDetailInfo, array('Pop up/Pop Under:','borderBottom','<p>'), '</p>'));
	        
	        $Description = trim($this->oLinkFeed->ParseStringBy2Tag($programDetailInfo, array('Campaign Description','<div class="subbox">'), '</div>'));
	        $CommissionDetails = trim($this->oLinkFeed->ParseStringBy2Tag($programDetailInfo, array('Commission Details','<div class="subbox">'), '</div>'));
	        $TrackingUrl = $k['Tracking URL'];
	        
	        //Term And Condition
	        $termAndConditionUrl =  "https://admin.optimisemedia.com/v2/programmes/affiliate/ViewTermsAndConditions.aspx?productid=$PID&affiliateid=$Affiliate";
	        $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"],"TermAndCondition".date("Ym")."_{$PID}_{$Affiliate}.dat", $this->batchProgram, $use_true_file_name);
	        if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
	        {
	            $r = $this->oLinkFeed->GetHttpResult($termAndConditionUrl,$request);
	            if($r['code'] == 200){
	                $result = $r["content"];
	                $this->oLinkFeed->fileCachePut($cache_file,$result);
	            }
	        }
	        $TermAndConditionInfo = file_get_contents($cache_file, 'r');
	        $TermAndCondition = trim(htmlspecialchars_decode($TermAndConditionInfo));
	        
	        //Brand Bidding
	        /*
	         * __EVENTTARGET
	         * __EVENTARGUMENT
	         * __LASTFOCUS
	         * __VIEWSTATE
	         * __VIEWSTATEGENERATOR
	         * ctl00$Uc_Navigation1$ddlNavSelectMerchant
	         *
	         * */
	        $SearchTermGoogleAndYahooAndBingIsThereAClosedBiddingGroup = '';
	        $SearchTermGoogleAndYahooAndBingBiddingOnBrand = '';
	        $SearchTermGoogleAndYahooAndBingBiddingOnMisSpellsOfBrand = '';
	        $SearchTermGoogleAndYahooAndBingBiddingOnBrandPlusGeneric = '';
	        $SearchTermGoogleAndYahooAndBingBiddingOnMisSpellsOfBrandPlusGeneric = '';
	        $SearchTermGoogleAndYahooAndBingBiddingOnBrandURL = '';
	        $SearchTermGoogleAndYahooAndBingBroadmatchBidding = '';
	        $SearchTermGoogleAndYahooAndBingNegativeMatching = '';
	        
	        $AdvertContentGoogleAndYahooAndBingUseOfTheBrandInAdvertTitle = '';
	        $AdvertContentGoogleAndYahooAndBingUseOfTheBrandInAdvertCopy = '';
	        $AdvertContentGoogleAndYahooAndBingUseOfTheBrandInTheDisplayURL = '';
	        $AdvertContentGoogleAndYahooAndBingUseTheBrandAsTheURL = '';
	        
	        $AdvertDestinationGoogleAndYahooAndBingDirectToMerchant = '';
	        $AdvertDestinationGoogleAndYahooAndBingDedicatedLandingPage = '';
	        $AdvertDestinationGoogleAndYahooAndBingGeneralPage = '';
	        
	        
	        $request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "");
	        preg_match('/<a class="tabUnselected" href="javascript:__doPostBack\(&#39;(.*?)&#39;,&#39;&#39;\)"><span>Brand Bidding<\/span><\/a>/i',$programDetailInfo,$matches1);
	        $__EVENTTARGET = urlencode($matches1[1]);
	        preg_match('/<input type="hidden" name="__EVENTARGUMENT" id="__EVENTARGUMENT" value="(.*?)" \/>/i',$programDetailInfo,$matches2);
	        $__EVENTARGUMENT = $matches2[1];
	        preg_match('/<input type="hidden" name="__LASTFOCUS" id="__LASTFOCUS" value="(.*?)" \/>/i',$programDetailInfo,$matches3);
	        $__LASTFOCUS = $matches3[1];
	        preg_match('/<input type="hidden" name="__VIEWSTATE" id="__VIEWSTATE" value="(.*?)" \/>/i',$programDetailInfo,$matches4);
	        $__VIEWSTATE = urlencode($matches4[1]);
	        preg_match('/<input type="hidden" name="__VIEWSTATEGENERATOR" id="__VIEWSTATEGENERATOR" value="(.*?)" \/>/i',$programDetailInfo,$matches5);
	        $__VIEWSTATEGENERATOR = $matches5[1];
	        $request['postdata'] = '__EVENTTARGET='.$__EVENTTARGET.'&__EVENTARGUMENT='.$__EVENTARGUMENT.'&__LASTFOCUS='.$__LASTFOCUS.'&__VIEWSTATE='.$__VIEWSTATE.'&__VIEWSTATEGENERATOR='.$__VIEWSTATEGENERATOR.'&ctl00$Uc_Navigation1$ddlNavSelectMerchant=0';
	        
	        $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"],"BrandBidding".date("Ym")."_{$PID}_{$ContactWebsiteID}.dat", $this->batchProgram, $use_true_file_name);
	        if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
	        {
	            $r = $this->oLinkFeed->GetHttpResult($prgmDetail_url,$request);
	            if($r['code'] == 200){
	                $result = $r["content"];
	                $this->oLinkFeed->fileCachePut($cache_file,$result);
	            }
	        }
	        $BrandBiddingInfo = file_get_contents($cache_file, 'r');
	        $BrandBiddingInfo = trim($this->oLinkFeed->ParseStringBy2Tag($BrandBiddingInfo, array('<div id="tab">'), '</div>'));
	        $BrandBiddingTempArr = array();
	        if(preg_match_all('/<table border="0" cellspacing="0" cellpadding="0">(.*?)<\/table>/i', $BrandBiddingInfo,$matchBB)){
	            
	            foreach ($matchBB[0] as $vaBB){
	                preg_match_all('/<tr class="normal">(.*?)<\/tr>/',$vaBB,$matchBBDetail);
	                $BrandBiddingTempArr[] = $matchBBDetail[1];
	            }
	        }
	        //$BrandBiddingTempArr 0:SearchTerm, 1:AdvertContent, 2: AdvertDestination
	        if(isset($BrandBiddingTempArr[0])){
	            
	            $SearchTermGoogleAndYahooAndBingIsThereAClosedBiddingGroup = $BrandBiddingTempArr[0][0];
	            $SearchTermGoogleAndYahooAndBingBiddingOnBrand = $BrandBiddingTempArr[0][1];
	            $SearchTermGoogleAndYahooAndBingBiddingOnMisSpellsOfBrand = $BrandBiddingTempArr[0][2];
	            $SearchTermGoogleAndYahooAndBingBiddingOnBrandPlusGeneric = $BrandBiddingTempArr[0][3];
	            $SearchTermGoogleAndYahooAndBingBiddingOnMisSpellsOfBrandPlusGeneric = $BrandBiddingTempArr[0][4];
	            $SearchTermGoogleAndYahooAndBingBiddingOnBrandURL = $BrandBiddingTempArr[0][5];
	            $SearchTermGoogleAndYahooAndBingBroadmatchBidding = $BrandBiddingTempArr[0][6];
	            $SearchTermGoogleAndYahooAndBingNegativeMatching = $BrandBiddingTempArr[0][7];
	        }
	        
	        if(isset($BrandBiddingTempArr[1])){
	             
	            $AdvertContentGoogleAndYahooAndBingUseOfTheBrandInAdvertTitle = $BrandBiddingTempArr[1][0];
	            $AdvertContentGoogleAndYahooAndBingUseOfTheBrandInAdvertCopy = $BrandBiddingTempArr[1][1];
	            $AdvertContentGoogleAndYahooAndBingUseOfTheBrandInTheDisplayURL = $BrandBiddingTempArr[1][2];
	            $AdvertContentGoogleAndYahooAndBingUseTheBrandAsTheURL = $BrandBiddingTempArr[1][3];
	        }
	        
	        if(isset($BrandBiddingTempArr[2])){
	        
	            $AdvertDestinationGoogleAndYahooAndBingDirectToMerchant = $BrandBiddingTempArr[2][0];
	            $AdvertDestinationGoogleAndYahooAndBingDedicatedLandingPage = $BrandBiddingTempArr[2][1];
	            $AdvertDestinationGoogleAndYahooAndBingGeneralPage = $BrandBiddingTempArr[2][2];
	        }
	        
	        
	        //Technical
	        /*
	         * __EVENTTARGET
	         * __EVENTARGUMENT
	         * __LASTFOCUS
	         * __VIEWSTATE
	         * __VIEWSTATEGENERATOR
	         * ctl00$Uc_Navigation1$ddlNavSelectMerchant
	         *
	         * */
	        $request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "");
	                      
	        preg_match('/<span>Brand Bidding<\/span><\/a><a class="tabUnselected" href="javascript:__doPostBack\(&#39;(.*?)&#39;,&#39;&#39;\)"><span>Technical<\/span><\/a>/isU',$programDetailInfo,$matches1);
	        $__EVENTTARGET = urlencode('ctl00$ContentPlaceHolder1$ctl24');
	        preg_match('/<input type="hidden" name="__EVENTARGUMENT" id="__EVENTARGUMENT" value="(.*?)" \/>/i',$programDetailInfo,$matches2);
	        $__EVENTARGUMENT = $matches2[1];
	        preg_match('/<input type="hidden" name="__LASTFOCUS" id="__LASTFOCUS" value="(.*?)" \/>/i',$programDetailInfo,$matches3);
	        $__LASTFOCUS = $matches3[1];
	        preg_match('/<input type="hidden" name="__VIEWSTATE" id="__VIEWSTATE" value="(.*?)" \/>/i',$programDetailInfo,$matches4);
	        $__VIEWSTATE = urlencode($matches4[1]);
	        preg_match('/<input type="hidden" name="__VIEWSTATEGENERATOR" id="__VIEWSTATEGENERATOR" value="(.*?)" \/>/i',$programDetailInfo,$matches5);
	        $__VIEWSTATEGENERATOR = $matches5[1];
	        $request['postdata'] = '__EVENTTARGET='.$__EVENTTARGET.'&__EVENTARGUMENT='.$__EVENTARGUMENT.'&__LASTFOCUS='.$__LASTFOCUS.'&__VIEWSTATE='.$__VIEWSTATE.'&__VIEWSTATEGENERATOR='.$__VIEWSTATEGENERATOR.'&ctl00$Uc_Navigation1$ddlNavSelectMerchant=0';
	        $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"],"Technical".date("Ym")."_{$PID}_{$ContactWebsiteID}.dat", $this->batchProgram, $use_true_file_name);
	        if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
	        {
	            $r = $this->oLinkFeed->GetHttpResult($prgmDetail_url,$request);
	            if($r['code'] == 200){
	                $result = $r["content"];
	                $this->oLinkFeed->fileCachePut($cache_file,$result);
	            }
	        }
	        $TechnicalInfo = file_get_contents($cache_file, 'r');
	        $TechnicalLeadGeneration = trim($this->oLinkFeed->ParseStringBy2Tag($TechnicalInfo, array('Lead Generation','<td>'), '</td>'));
	        $TechnicalStatisticsUploadFrequency = trim($this->oLinkFeed->ParseStringBy2Tag($TechnicalInfo, array('Statistics Upload Frequency','<td>'), '</td>'));
	        $DeepLinking = $k['Deep Link Enabled'];
	        $CurrencyCode = $k['Currency Code'];
	        $CurrencySymbol = $k['Currency Symbol'];
	        $Homepage = $k['Website URL'];
	        
	        
	        $arr_prgm[$PID] = array(
	            
	            "SiteID" => $Site['SiteID'],
	            "AccountID" => $this->account['AccountID'],
	            "BatchID" => $this->oLinkFeed->batchid,
	            "AffID" => $this->info["AffID"],
	            "IdInAff" => $PID,
	            
	            "MerchantName" => addslashes($MerchantName),
	            "PID" => $PID,
	            "CampaignName" => addslashes($CampaignName),
	            "CampaignName" => addslashes($CampaignName),
	            "Commission" => addslashes($Commission),
	            "PayoutType" => addslashes($PayoutType),
	            "Country" => addslashes($Country),
	            "Platform" => addslashes($Platform),
	            "CookieTime" => addslashes($CookieTime),
	            "Status" => addslashes($Status),
	            "LogoUrl" => addslashes($LogoUrl),
	            "DailyCap" => addslashes($DailyCap),
	            "Category" => addslashes($Category),
	            "ProductFeed" => addslashes($ProductFeed),
	            "UidAndSubId" => addslashes($UidAndSubId),
	            "FirstAndLastCookie" => addslashes($FirstAndLastCookie),
	            "Cashback" => addslashes($Cashback),
	            "VoucherAndCoupon" => addslashes($VoucherAndCoupon),
	            "BrandBidding" => addslashes($BrandBidding),
	            "CampaignRestrictionEmail"=>addslashes($CampaignRestrictionEmail),
	            "CampaignRestrictionSocialMedia"=> addslashes($CampaignRestrictionSocialMedia),
	            "CampaignRestrictionBehavioralRetargeting" => addslashes($CampaignRestrictionBehavioralRetargeting),
	            "CampaignRestrictionAdultTraffic" => addslashes($CampaignRestrictionAdultTraffic),
	            "CampaignRestrictionPopUpAndPopUnder" => addslashes($CampaignRestrictionPopUpAndPopUnder),
	            "Description" => addslashes($Description),
	            "CommissionDetails" => addslashes($CommissionDetails),
	            "TrackingUrl" => addslashes($TrackingUrl),
	            "TermAndCondition" => addslashes($TermAndCondition),
	            "SearchTermGoogleAndYahooAndBingIsThereAClosedBiddingGroup" => addslashes($SearchTermGoogleAndYahooAndBingIsThereAClosedBiddingGroup),
	            "SearchTermGoogleAndYahooAndBingBiddingOnBrand" => addslashes($SearchTermGoogleAndYahooAndBingBiddingOnBrand),
	            "SearchTermGoogleAndYahooAndBingBiddingOnMisSpellsOfBrand" => addslashes($SearchTermGoogleAndYahooAndBingBiddingOnMisSpellsOfBrand),
	            "SearchTermGoogleAndYahooAndBingBiddingOnBrandPlusGeneric" => addslashes($SearchTermGoogleAndYahooAndBingBiddingOnBrandPlusGeneric),
	            "SearchTermGoogleAndYahooAndBingBiddingOnMisSpelBrandGeneric" => addslashes($SearchTermGoogleAndYahooAndBingBiddingOnMisSpellsOfBrandPlusGeneric),
	            "SearchTermGoogleAndYahooAndBingBiddingOnBrandURL" => addslashes($SearchTermGoogleAndYahooAndBingBiddingOnBrandURL),
	            "SearchTermGoogleAndYahooAndBingBroadmatchBidding" => addslashes($SearchTermGoogleAndYahooAndBingBroadmatchBidding),
	            "SearchTermGoogleAndYahooAndBingNegativeMatching" => addslashes($SearchTermGoogleAndYahooAndBingNegativeMatching),
	            "AdvertContentGoogleAndYahooAndBingUseOfTheBrandInAdvertTitle" => addslashes($AdvertContentGoogleAndYahooAndBingUseOfTheBrandInAdvertTitle),
	            "AdvertContentGoogleAndYahooAndBingUseOfTheBrandInAdvertCopy" => addslashes($AdvertContentGoogleAndYahooAndBingUseOfTheBrandInAdvertCopy),
	            "AdvertContentGoogleAndYahooAndBingUseOfTheBrandInTheDisplayURL" => addslashes($AdvertContentGoogleAndYahooAndBingUseOfTheBrandInTheDisplayURL),
	            "AdvertContentGoogleAndYahooAndBingUseTheBrandAsTheURL" => addslashes($AdvertContentGoogleAndYahooAndBingUseTheBrandAsTheURL),
	            "AdvertDestinationGoogleAndYahooAndBingDirectToMerchant" => addslashes($AdvertDestinationGoogleAndYahooAndBingDirectToMerchant),
	            "AdvertDestinationGoogleAndYahooAndBingDedicatedLandingPage" => addslashes($AdvertDestinationGoogleAndYahooAndBingDedicatedLandingPage),
	            "AdvertDestinationGoogleAndYahooAndBingGeneralPage" => addslashes($AdvertDestinationGoogleAndYahooAndBingGeneralPage),
	            "TechnicalLeadGeneration" => addslashes($TechnicalLeadGeneration),
	            "TechnicalStatisticsUploadFrequency" => addslashes($TechnicalStatisticsUploadFrequency),
	            "DeepLinking" => addslashes($DeepLinking),
	            "CurrencyCode" => addslashes($CurrencyCode),
	            "CurrencySymbol" => addslashes($CurrencySymbol),
	            "HomePage" => addslashes($Homepage),
	            
	        );
	        
	        $program_num++;
	        if(count($arr_prgm) >= 100){
	            $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
	            $arr_prgm = array();
	        }
	    }//while
	    
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
