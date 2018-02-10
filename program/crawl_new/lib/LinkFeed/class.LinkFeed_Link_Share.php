<?php
require_once 'text_parse_helper.php';


class LinkFeed_Link_Share
{
	
	function __construct($aff_id, $oLinkFeed)
	{
		
	    $this->oLinkFeed = $oLinkFeed;
	    $this->info = $oLinkFeed->getAffById($aff_id);
	    $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
	    
	    define('API_TOKEN_2', '9f48285de7d00f0aed822788c0144da206901ad94e8e3b68f930082d9ab2a17d');
	    define('MOUSE_OVER_OID_2', '223073');
	    define('AUTH_TOKEN', 'NkplZXZQcDYzakpmQVQxeUlTRkp0M2h4QTZjYTpsMnlGeXhSal90T1JKcXluS1Bja01vaDZlRFFh');
	    define('UID', 3310876);

        $this->batchProgram = date('Ymd') . "_program_" . $this->oLinkFeed->batchID;
	}
	
	function GetProgramFromAff($accountid, $affSiteAccName)
	{
		$this->account = $this->oLinkFeed->getAffAccountById($accountid);
		$this->info['AffLoginUrl'] = $this->account['LoginUrl'];	
		$this->info['AffLoginPostString'] = $this->account['LoginPostString'];	
		$this->info['AffLoginVerifyString'] = $this->account['LoginVerifyString'];	
		$this->info['AffLoginMethod'] = $this->account['LoginMethod'];	
		$this->info['AffLoginSuccUrl'] = $this->account['LoginSuccUrl'];	
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";

        $this->site = $this->oLinkFeed->getAffAccountSiteByName($affSiteAccName);

        echo 'Site:' . $this->site['Name'] . "\r\n";
        $this->GetProgramFromByPage($this->site['SiteID']);

        echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";

        $this->oLinkFeed->checkBatchID = $this->oLinkFeed->batchID;
        $this->oLinkFeed->CheckCrawlBatchData($this->info["AffID"], $this->site['SiteID']);
	}

	function GetProgramFromByPage($SiteID)
	{
		echo "\tGet Program by page start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		
		
		$request = array("AffId" => $this->info["AffID"], "method" => "get", "postdata" => "",);
		$country_arr = array();

		//step 1, login
		echo "login to affservice\n\t";
		$this->info["AffId"] = $this->info["AffID"];
		$this->oLinkFeed->LoginIntoAffService($this->info["AffID"],$this->info);


        //step 2, Get all new merchants
		echo "Get all new merchants";
		$request["method"] = "get";
		$nNumPerPage = 100;
		$bHasNextPage = true;
		$nPageNo = 1;
		$arr_prgm = array();
		$UpdateCnt = 0;
        $__csrf_magic = '';
		while($bHasNextPage){
			if($nPageNo != 1){
				$request["method"] = "post";
				$request["postdata"] = "__csrf_magic=".urlencode($__csrf_magic)."&analyticchannel=&analyticpage=&singleApply=&update=&remove_mid=&remove_oid=&remove_nid=&filter_open=&cat=&advertiserSearchBox=&category=-1&filter_status=all&filter_networks=all&filter_type=all&filter_banner_size=-1&orderby=&direction=&currec=".($nNumPerPage * ($nPageNo - 1) + 1)."&pagesize=".$nNumPerPage;
			}
			$strUrl = "http://cli.linksynergy.com/cli/publisher/programs/advertisers.php";
            $result = $this->GetHttpResult($strUrl,$request,'',"new_advertisers_page{$nPageNo}");

			$__csrf_magic = $this->oLinkFeed->ParseStringBy2Tag($result, array("name='__csrf_magic'", 'value="'), '"');

			print "Get Merchant List new : Page - $nPageNo <br>\n";
			//parse HTML
			$strLineStart = '<td class="td_left_edge">';
			$nLineStart = 0;
			while ($nLineStart >= 0){

                $nLineStart = stripos($result, $strLineStart, $nLineStart);

				if ($nLineStart === false) break;

				// ID 	Name 	EPC 	Status
				$strMerID = $this->oLinkFeed->ParseStringBy2Tag($result, array('select_mid[]', 'value="'), '"', $nLineStart);
				//$strMerID = str_replace('~', '_', $strMerID);
				//LogoUrl
				$LogoUrl = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('<img ', 'src="'), '"', $nLineStart));

				list($strTmpMerID, $strTmpOfferID, $strTmpNetworkID) = explode('~', $strMerID);
				$strMerID = $strTmpMerID.'_'.$strTmpNetworkID;

				//content
				$descContent = $this->oLinkFeed->ParseStringBy2Tag($result, array('<td class="td_description">'), "</td>", $nLineStart);

				preg_match('/<a id="m_name_\d+"([\s\S]*?)<\/a>/i',$descContent,$matches);
				$strMerName = trim(strip_tags($matches[0]));

				$strMerFlagCountry = '';
				if(preg_match('/images\/common\/flag_(\w+)\.gif/i', $descContent,$matches))
				    $strMerFlagCountry = $matches[1];

				$strMerFlagOffersMerchandiser = 'NO';
				if(preg_match('/Offers Merchandiser/i', $descContent,$matches))
				    $strMerFlagOffersMerchandiser = 'YES';

				$strMerFlagOffersMediaOptimizationReport = 'NO';
				if(preg_match('/Offers Media Optimization Report/i', $descContent,$matches))
				    $strMerFlagOffersMediaOptimizationReport = 'YES';

				preg_match('/<\/b>([\s\S]*?)<img src="/i',$descContent,$matches);
				$desc = trim(strip_tags($matches[0]));

				//join date
				$JoinDate = $this->oLinkFeed->ParseStringBy2Tag($result, array('<td class="td_date_joined">'), '</td>', $nLineStart);
				$JoinDate = $JoinDate. "-01-01 00:00:00";
				//CommissionExt
				$CommissionExt = $this->oLinkFeed->ParseStringBy2Tag($result, array('<td class="td_commission">'), '</td>', $nLineStart);
				//ReturnDays
				$ReturnDays = $this->oLinkFeed->ParseStringBy2Tag($result, array('<td class="td_return">'), '</td>', $nLineStart);
				//class="td_status" or class="td_status_temp"
				$strStatusShow = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('class="td_status', '>'), '<br', $nLineStart)));

				$TargetCountryExt = '';
				if(!isset($country_arr[$strTmpMerID])){
					$url = "http://cli.linksynergy.com/cli/publisher/programs/shipping_availability.php?mid=$strTmpMerID";
                    $Country_result = $this->GetHttpResult($url, $request, '', "new_sv_mid_{$strTmpMerID}");
					$Country_re = preg_match_all('/<td>(.*?)<\/td>/',$Country_result, $matches);
					foreach ($matches[1] as $ke => $m){
						if(empty($m))
							unset($matches[1][$ke]);
					}
					if(count($matches[1])){
						$TargetCountryExt = implode(',', $matches[1]);
						$country_arr[$strTmpMerID] = $TargetCountryExt;
					}
				}else{
					$TargetCountryExt = $country_arr[$strTmpMerID];
				}

				$prgm_url = "http://cli.linksynergy.com/cli/publisher/programs/advertiser_detail.php?oid=$strTmpOfferID&mid=$strTmpMerID&offerNid=$strTmpNetworkID&controls=1:1:1:1:1:0";
				//program
				$arr_prgm[$strMerID] = array(
				    "SiteID" => $SiteID,
				    "AccountID" => $this->account['AccountID'],
				    "BatchID" => $this->oLinkFeed->batchID,
				    "Name" => addslashes(trim($strMerName)),
					"TargetCountryExt" => $TargetCountryExt,
					"IdInAff" => $strMerID,
				    "AffID" => $this->info["AffID"],
					"JoinedNetworkDate" => $JoinDate,
					"Partnership" => $strStatusShow,
					"Description" => addslashes($desc),
					"Commission" => addslashes($CommissionExt),
					"CookieTime" => $ReturnDays,
					"DetailPageUrl" => $prgm_url,
				    'LogoUrl' => $LogoUrl,
				    'Country' => $strMerFlagCountry,
				    'OffersMerchandiser' => $strMerFlagOffersMerchandiser,
				    'OffersMediaOptimizationReport' => $strMerFlagOffersMediaOptimizationReport,
				);

				//program_detail
				$arr_prgm[$strMerID] += $this->getSupportDUT($strTmpMerID, $strTmpOfferID, $request, true, $strMerID);

				//Policies detail
				$arr_prgm[$strMerID] += $this->GetPolicyFromByPage($strTmpMerID, $request);

				//tracking
//				$arr_prgm[$strMerID] += $this->GetTrackingInfoFromByPage($strTmpMerID, $request);
				//print_r($arr_prgm);exit;
				$program_num++;
				if(count($arr_prgm) >= 10){
                    $objProgram = new ProgramDb();
				    $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				    $arr_prgm = array();
				}
			}

			if(count($arr_prgm)){
                $objProgram = new ProgramDb();
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				unset($arr_prgm);
			}
			//Check if have next page;
			if (false === $this->oLinkFeed->ParseStringBy2Tag($result, "document.myform.submit();return false;'>Next", '</a></div></div>', $nLineStart))
			{
				$bHasNextPage = false;
				if($this->debug) print " NO NEXT PAGE  <br>\n";
			}
			else{
				if($this->debug) print " Have NEXT PAGE  <br>\n";
			}
			if ($bHasNextPage){
				$nPageNo++;
			}
		}//per page

		//step 3, Get all my merchants
		echo "Get all my merchants";
		$request["method"] = "get";
		$nNumPerPage = 100;
		$bHasNextPage = true;
		$nPageNo = 1;
		$arr_prgm = array();
		while($bHasNextPage){
		    if($nPageNo != 1){
		        $request["method"] = "post";
		        $request["postdata"] = "__csrf_magic=".urlencode($__csrf_magic)."&analyticchannel=Programs&analyticpage=My+Advertisers&singleApply=&update=&remove_mid=&remove_oid=&remove_nid=&filter_open=&cat=&advertiserSerachBox_old=&advertiserSerachBox=&category=-1&filter_networks=all&filter_promotions=-1&filter_type=all&filter_banner_size=+--+All+Sizes+--&my_programs=1&filter_status_program=all&orderby=&direction=&currec=".($nNumPerPage * ($nPageNo - 1) + 1)."&pagesize=".$nNumPerPage;
		    }

		    $strUrl = "http://cli.linksynergy.com/cli/publisher/programs/advertisers.php?my_programs=1";
            $result = $this->GetHttpResult($strUrl,$request,'',"all_advertisers_page{$nPageNo}");

		    $__csrf_magic = $this->oLinkFeed->ParseStringBy2Tag($result, array("name='__csrf_magic'", 'value="'), '"');

		    print "Get All My Merchant List - Apporved : Page - $nPageNo <br>\n";

		    //parse HTML
		    $strLineStart = '<td class="td_left_edge">';
		    $nLineStart = 0;
		    while ($nLineStart >= 0){

		        $nLineStart = stripos($result, $strLineStart, $nLineStart);

		        if ($nLineStart === false) break;

		        // ID 	Name 	EPC 	Status
		        $strMerID = $this->oLinkFeed->ParseStringBy2Tag($result, array('select_mid[]', 'value="'), '"', $nLineStart);
		        //$strMerID = str_replace('~', '_', $strMerID);
		        //LogoUrl
		        $LogoUrl = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('<img ', 'src="'), '"', $nLineStart));

		        list($strTmpMerID, $strTmpOfferID, $strTmpNetworkID) = explode('~', $strMerID);
		        $strMerID = $strTmpMerID.'_'.$strTmpNetworkID;

		        //content
		        $descContent = $this->oLinkFeed->ParseStringBy2Tag($result, array('<td class="td_description">'), "</td>", $nLineStart);

		        preg_match('/<a id="m_name_\d+"([\s\S]*?)<\/a>/i',$descContent,$matches);
		        $strMerName = trim(strip_tags($matches[0]));

		        $strMerFlagCountry = '';
		        if(preg_match('/images\/common\/flag_(\w+)\.gif/i', $descContent,$matches))
		            $strMerFlagCountry = $matches[1];

		        $strMerFlagOffersMerchandiser = 'NO';
		        if(preg_match('/Offers Merchandiser/i', $descContent,$matches))
		            $strMerFlagOffersMerchandiser = 'YES';

		        $strMerFlagOffersMediaOptimizationReport = 'NO';
		        if(preg_match('/Offers Media Optimization Report/i', $descContent,$matches))
		            $strMerFlagOffersMediaOptimizationReport = 'YES';

		        preg_match('/<\/b>([\s\S]*?)<img src="/i',$descContent,$matches);
		        $desc = trim(strip_tags($matches[0]));


		        //join date
		        $JoinDate = $this->oLinkFeed->ParseStringBy2Tag($result, array('<td class="td_date_joined">'), '</td>', $nLineStart);
		        $JoinDate = $JoinDate. "-01-01 00:00:00";
		        //CommissionExt
		        $CommissionExt = $this->oLinkFeed->ParseStringBy2Tag($result, array('<td class="td_commission">'), '</td>', $nLineStart);
		        //ReturnDays
		        $ReturnDays = $this->oLinkFeed->ParseStringBy2Tag($result, array('<td class="td_return">'), '</td>', $nLineStart);
		        //class="td_status" or class="td_status_temp"
		        $strStatusShow = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('<td class="td_status','>'), "<br>", $nLineStart)));

		        $TargetCountryExt = '';
		        if(!isset($country_arr[$strTmpMerID])){
		            $url = "http://cli.linksynergy.com/cli/publisher/programs/shipping_availability.php?mid=$strTmpMerID";
                    $Country_result = $this->GetHttpResult($url, $request, '', "all_sv_mid_{$strTmpMerID}");

		            $Country_re = preg_match_all('/<td>(.*?)<\/td>/',$Country_result, $matches);
		            foreach ($matches[1] as $ke => $m){
		                if(empty($m))
		                    unset($matches[1][$ke]);
		            }
		            if(count($matches[1])){
		                $TargetCountryExt = implode(',', $matches[1]);
		                $country_arr[$strTmpMerID] = $TargetCountryExt;
		            }
		        }else{
		            $TargetCountryExt = $country_arr[$strTmpMerID];
		        }

		        $prgm_url = "http://cli.linksynergy.com/cli/publisher/programs/advertiser_detail.php?oid=$strTmpOfferID&mid=$strTmpMerID&offerNid=$strTmpNetworkID&controls=1:1:1:1:1:0";
		        //program
		        $arr_prgm[$strMerID] = array(
		            "SiteID" => $SiteID,
		            "AccountID" => $this->account['AccountID'],
		            "BatchID" => $this->oLinkFeed->batchID,
		            "Name" => addslashes(trim($strMerName)),
		            "TargetCountryExt" => $TargetCountryExt,
		            "IdInAff" => $strMerID,
		            "AffID" => $this->info["AffID"],
		            "JoinedNetworkDate" => $JoinDate,
		            "Partnership" => $strStatusShow,
		            "Description" => addslashes($desc),
		            "Commission" => addslashes($CommissionExt),
		            "CookieTime" => $ReturnDays,
		            "DetailPageUrl" => $prgm_url,
		            'LogoUrl' => $LogoUrl,
		            'Country' => $strMerFlagCountry,
		            'OffersMerchandiser' => $strMerFlagOffersMerchandiser,
		            'OffersMediaOptimizationReport' => $strMerFlagOffersMediaOptimizationReport,
		        );

		        //program_detail
		        $arr_prgm[$strMerID] += $this->getSupportDUT($strTmpMerID, $strTmpOfferID, $request, true, $strMerID);

		        //Policies detail
		        $arr_prgm[$strMerID] += $this->GetPolicyFromByPage($strTmpMerID, $request);

		        //tracking
//		        $arr_prgm[$strMerID] += $this->GetTrackingInfoFromByPage($strTmpMerID, $request);

		        $program_num++;
		        if(count($arr_prgm) >= 10){
                    $objProgram = new ProgramDb();
		            $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
		            $arr_prgm = array();
		        }

		    }
		    if(count($arr_prgm)){
                $objProgram = new ProgramDb();
		        $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
		        unset($arr_prgm);
		    }

		    //Check if have next page;
		    if (false === $this->oLinkFeed->ParseStringBy2Tag($result, "document.myform.submit();return false;'>Next", '</a></div></div>', $nLineStart)){
		        $bHasNextPage = false;
		        if($this->debug) print " NO NEXT PAGE  <br>\n";
		    }
		    else{
		        if($this->debug) print " Have NEXT PAGE  <br>\n";
		    }
		    if ($bHasNextPage){
		        $nPageNo++;
		    }
		}//per page

		//step 4, Get all Premium merchants
		echo "Get all Premium merchants";
		$nNumPerPage = 100;
		$bHasNextPage = true;
		$nPageNo = 1;
		$arr_prgm = array();
		$request["method"] = "get";
		while($bHasNextPage){
		    if($nPageNo != 1){
		        $request["method"] = "post";
		        $request["postdata"] = "__csrf_magic=".urlencode($__csrf_magic)."&analyticchannel=Programs&analyticpage=Premium+Advertisers&singleApply=&update=&remove_mid=&remove_oid=&remove_nid=&filter_open=&cat=&advertiserSerachBox_old=&advertiserSerachBox=&category=-1&filter_status=all&filter_networks=all&filter_promotions=-1&filter_type=all&filter_banner_size=+--+All+Sizes+--&orderby=&direction=&currec=".($nNumPerPage * ($nPageNo - 1) + 1)."&pagesize=".$nNumPerPage;
		    }
		    $strUrl = "http://cli.linksynergy.com/cli/publisher/programs/advertisers.php?advertisers=1";
            $result = $this->GetHttpResult($strUrl,$request,'',"all_pre_advertisers_page{$nPageNo}");

		    $__csrf_magic = $this->oLinkFeed->ParseStringBy2Tag($result, array("name='__csrf_magic'", 'value="'), '"');

		    print "Get Merchant List - Premium : Page - $nPageNo <br>\n";

		    //parse HTML
		    $strLineStart = '<td class="td_left_edge">';

		    $nLineStart = 0;
		    while ($nLineStart >= 0){

		        $nLineStart = stripos($result, $strLineStart, $nLineStart);

		        if ($nLineStart === false) break;

		        // ID 	Name 	EPC 	Status
		        $strMerID = $this->oLinkFeed->ParseStringBy2Tag($result, array('select_mid[]', 'value="'), '"', $nLineStart);
		        //$strMerID = str_replace('~', '_', $strMerID);
		        //LogoUrl
		        $LogoUrl = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('<img ', 'src="'), '"', $nLineStart));

		        list($strTmpMerID, $strTmpOfferID, $strTmpNetworkID) = explode('~', $strMerID);
		        $strMerID = $strTmpMerID.'_'.$strTmpNetworkID;

		        //content
		        $descContent = $this->oLinkFeed->ParseStringBy2Tag($result, array('<td class="td_description">'), "</td>", $nLineStart);

		        preg_match('/<a id="m_name_\d+"([\s\S]*?)<\/a>/i',$descContent,$matches);
		        $strMerName = trim(strip_tags($matches[0]));

		        $strMerFlagCountry = '';
		        if(preg_match('/images\/common\/flag_(\w+)\.gif/i', $descContent,$matches))
		            $strMerFlagCountry = $matches[1];

		        $strMerFlagOffersMerchandiser = 'NO';
		        if(preg_match('/Offers Merchandiser/i', $descContent,$matches))
		            $strMerFlagOffersMerchandiser = 'YES';

		        $strMerFlagOffersMediaOptimizationReport = 'NO';
		        if(preg_match('/Offers Media Optimization Report/i', $descContent,$matches))
		            $strMerFlagOffersMediaOptimizationReport = 'YES';

		        preg_match('/<\/b>([\s\S]*?)<img src="/i',$descContent,$matches);
		        $desc = trim(strip_tags($matches[0]));


		        //join date
		        $JoinDate = $this->oLinkFeed->ParseStringBy2Tag($result, array('<td class="td_date_joined">'), '</td>', $nLineStart);
		        $JoinDate = $JoinDate. "-01-01 00:00:00";
		        //CommissionExt
		        $CommissionExt = $this->oLinkFeed->ParseStringBy2Tag($result, array('<td class="td_commission">'), '</td>', $nLineStart);
		        //ReturnDays
		        $ReturnDays = $this->oLinkFeed->ParseStringBy2Tag($result, array('<td class="td_return">'), '</td>', $nLineStart);
		        //class="td_status" or class="td_status_temp"
		        $strStatusShow = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('<td class="td_status','>'), "<br>", $nLineStart)));

		        $TargetCountryExt = '';
		        if(!isset($country_arr[$strTmpMerID])){
		            $url = "http://cli.linksynergy.com/cli/publisher/programs/shipping_availability.php?mid=$strTmpMerID";
                    $Country_result = $this->GetHttpResult($url, $request, '', "all_pre_sv_mid_{$strTmpMerID}");
		            $Country_re = preg_match_all('/<td>(.*?)<\/td>/',$Country_result, $matches);
		            foreach ($matches[1] as $ke => $m){
		                if(empty($m))
		                    unset($matches[1][$ke]);
		            }
		            if(count($matches[1])){
		                $TargetCountryExt = implode(',', $matches[1]);
		                $country_arr[$strTmpMerID] = $TargetCountryExt;
		            }
		        }else{
		            $TargetCountryExt = $country_arr[$strTmpMerID];
		        }

		        $prgm_url = "http://cli.linksynergy.com/cli/publisher/programs/advertiser_detail.php?oid=$strTmpOfferID&mid=$strTmpMerID&offerNid=$strTmpNetworkID&controls=1:1:1:1:1:0";
		        //program
		        $arr_prgm[$strMerID] = array(
		            "SiteID" => $SiteID,
		            "AccountID" => $this->account['AccountID'],
		            "BatchID" => $this->oLinkFeed->batchID,
		            "Name" => addslashes(trim($strMerName)),
		            "TargetCountryExt" => $TargetCountryExt,
		            "IdInAff" => $strMerID,
		            "AffID" => $this->info["AffID"],
		            "JoinedNetworkDate" => $JoinDate,
		            "Partnership" => $strStatusShow,
		            "Description" => addslashes($desc),
		            "Commission" => addslashes($CommissionExt),
		            "CookieTime" => $ReturnDays,
		            "DetailPageUrl" => $prgm_url,
		            'LogoUrl' => $LogoUrl,
		            'Country' => $strMerFlagCountry,
		            'OffersMerchandiser' => $strMerFlagOffersMerchandiser,
		            'OffersMediaOptimizationReport' => $strMerFlagOffersMediaOptimizationReport,
		        );

		        //program_detail
		        $arr_prgm[$strMerID] += $this->getSupportDUT($strTmpMerID, $strTmpOfferID, $request, true, $strMerID);

		        //Policies detail
//		        $arr_prgm[$strMerID] += $this->GetPolicyFromByPage($strTmpMerID, $request);

		        //tracking
//		        $arr_prgm[$strMerID] += $this->GetTrackingInfoFromByPage($strTmpMerID, $request);

		        $program_num++;
		        if(count($arr_prgm) >= 10){
                    $objProgram = new ProgramDb();
		            $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
		            $arr_prgm = array();
		        }
		         
		        
		    }
		    if(count($arr_prgm)){
                $objProgram = new ProgramDb();
		        $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
		        unset($arr_prgm);
		    }
		    //Check if have next page;
		    if (false === $this->oLinkFeed->ParseStringBy2Tag($result, "document.myform.submit();return false;'>Next", '</a></div></div>', $nLineStart)){
		        $bHasNextPage = false;
		        if($this->debug) print " NO NEXT PAGE  <br>\n";
		    }
		    else{
		        if($this->debug) print " Have NEXT PAGE  <br>\n";
		    }
		
		    if ($bHasNextPage){
		        $nPageNo++;
		    }
		}//per page

		echo "\tGet Program by page end\r\n";
		echo "<hr>\r\n";

		if($program_num < 10){
			mydie("die: program count < 10, please check program.\n");
		}
		echo "\tUpdate ({$program_num}) program.\r\n";
	}
	
	
	function getSupportDUT($mid, $oid, $request, $needmoreinfo = false, $IdInAff)
	{
	
	    $mid = intval($mid);
	    $oid = intval($oid);
	    $return_arr = array();
	    
	    //programDetails 
	    $advDetails_url = "http://cli.linksynergy.com/cli/publisher/programs/program_details.php?mid=$mid&oid=$oid";
	    $advDetails_info = $this->GetHttpResult($advDetails_url, $request, '', "program_details_{$mid}_{$oid}");

	    $return_arr['ProgramDetails'] = addslashes($advDetails_info);

	    if($mid && $oid){
	        if(!isset($this->cache[$IdInAff]["DetailPage"])) {
                $prgm_url = "http://cli.linksynergy.com/cli/publisher/programs/adv_info.php?mid=$mid&oid=$oid";
                $prgm_detail = $this->GetHttpResult($prgm_url, $request, '', "advInfo_{$mid}_{$oid}");

                $SupportDeepUrl = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Deep Linking Enabled', '<td>'), '</td>')));
                if (stripos($SupportDeepUrl, "yes") !== false) {
                    $SupportDeepUrl = "YES";
                } else {
                    $SupportDeepUrl = "NO";
                }
                $return_arr['SupportDeepUrl'] = $SupportDeepUrl;

                if ($needmoreinfo) {
                    $CategoryExt = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Categories:', '<td>'), "</td>");
                    $CategoryExt = trim(strip_tags(str_replace("<br>", ", ", $CategoryExt)), ",");
                    $return_arr['Category'] = addslashes($CategoryExt);

                    $SignatureCompliant = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Signature Compliant:', '<td>'), "</td>")));
                    $return_arr['SignatureCompliant'] = $SignatureCompliant;

                    $CheckMailedBy = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Check Mailed by:', '<td>'), "</td>")));
                    $return_arr['CheckMailedBy'] = $CheckMailedBy;

                    $Homepage = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Website:', 'a href="'), '"')));
                    $Contact_Name = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Contact Name:', '<td>'), "</td>");
                    $Contact_Title = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Contact Title:', '<td>'), "</td>");
                    $Contact_Phone = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Phone Number:', '<td>'), "</td>");
                    $Contact_Email = strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Email Address:', '<td>'), "</td>"));
                    $Contact_Address = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Company Address:', '<td>'), "</td>");
                    $Contact_Address = trim(strip_tags(str_replace("<br>", ", ", $Contact_Address)));

                    $return_arr['ContactPerson'] = addslashes($Contact_Name);
                    $return_arr['ContactTitle'] = addslashes($Contact_Title);
                    $return_arr['ContactPhone'] = addslashes($Contact_Phone);
                    $return_arr['ContactEmail'] = addslashes($Contact_Email);
                    $return_arr['ContactCompany'] = addslashes($Contact_Address);

                    if (!isset($this->cache[$IdInAff]["terms"])) {
                        $term_url = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Terms & Conditions:', 'href="'), '"');
                        $TermAndCondition = $this->GetHttpResult($term_url, $request, '', "terms_{$mid}_{$oid}");
                        $return_arr['TermAndCondition'] = addslashes($TermAndCondition);
                    }
                    $return_arr['Homepage'] = addslashes($Homepage);
                }

            }
	    }
	    return $return_arr;
	}
	
	
	function GetPolicyFromByPage($mid,$request)
	{
	    
	    $return = array();
	    //paid_search
	    $policy_url = "http://cli.linksynergy.com/cli/publisher/programs/Policies/paid_search.php?&mid=$mid";
	    $policy_info = $this->GetHttpResult($policy_url, $request, '', "Policy_{$mid}");
	    preg_match_all('/<tr>([\s\S]*)<\/tr>/iU', $policy_info,$matches);
	    $paidSerchKey = array(
	        '1'=>'PaidSearchPoliciesAllowsBiddingBrandedOrTrademarkedTerms',
	        '2'=>'PaidSearchPoliciesAllowsBiddingBerivativesoFBrandedOrTrademarked',
	        '3'=>'PaidSearchPoliciesAllowsBiddingBroadMatchBrandedOrTrademarkedTer',
	        '4'=>'PaidSearchPoliciesAllowsBiddingCompetitorsBrandedOrTrademarkedTe',
	        '5'=>'PaidSearchPoliciesAllowsDirectLinking',
	        '6'=>'PaidSearchPoliciesAllowsBrandNameDisplayURL',
	        '7'=>'PaidSearchPoliciesAllowsUseBrandNameInTitleOrAdCopy',
	    );
	    foreach ($matches[0] as $key=>$values){
	        if($key == 0) continue;
	        if(strpos($values, 'green') !== false){
	            $return[$paidSerchKey[$key]] = 'YES';
	        }elseif(strpos($values, 'red') !== false){
	            $return[$paidSerchKey[$key]] = 'NO';
	        }else{
	            $return[$paidSerchKey[$key]] = '';
	        }
	    }
	    
	    foreach ($paidSerchKey as $v1){
	        
	        if(!isset($return[$v1]))
	            $return[$v1] = '';
	        
	    }
	    
	    //Coupon Policies
	    $policy_url = "http://cli.linksynergy.com/cli/publisher/programs/Policies/coupons.php?&mid=$mid";
	    $policy_info = $this->GetHttpResult($policy_url, $request, '', "coupon_policies_{$mid}");
	    preg_match_all('/<tr>([\s\S]*)<\/tr>/iU', $policy_info,$matches);
	    $paidSerchKey = array(
	        '1'=>'CouponPoliciesAdvertiserProvideCouponThroughPublisher',
	        '2'=>'CouponPoliciesAllowsUseCouponToPublic',
	    );
	    foreach ($matches[0] as $key=>$values){
	        if($key == 0) continue;
	        if(strpos($values, 'green') !== false){
	            $return[$paidSerchKey[$key]] = 'YES';
	        }elseif(strpos($values, 'red') !== false){
	            $return[$paidSerchKey[$key]] = 'NO';
	        }else{
	            $return[$paidSerchKey[$key]] = '';
	        }
	    }
	    
	    foreach ($paidSerchKey as $v2){
	        if(!isset($return[$v2]))
	            $return[$v2] = '';
	    }
	    
	    //Gift Card Policies
	    $policy_url = "http://cli.linksynergy.com/cli/publisher/programs/Policies/gift_cards.php?&mid=$mid";
	    $policy_info = $this->GetHttpResult($policy_url, $request, '', "card_policy_{$mid}");
	    preg_match_all('/<tr>([\s\S]*)<\/tr>/iU', $policy_info,$matches);
	    $paidSerchKey = array(
	        '1'=>'GiftCardPoliciesAdvertiserCommissionPurchaseGiftCards',
	        '2'=>'GiftCardPoliciesAdvertiserCommissionPurchaseEGiftCards',
	        '3'=>'GiftCardPoliciesAdvertiserCommissionCustomerRedeemsGiftCards',
	        '4'=>'GiftCardPoliciesAdvertiserCommissionCustomerRedeemsEGiftCards',
	    );
	    foreach ($matches[0] as $key=>$values){
	        if($key == 0) continue;
	        if(strpos($values, 'green') !== false){
	            $return[$paidSerchKey[$key]] = 'YES';
	        }elseif(strpos($values, 'red') !== false){
	            $return[$paidSerchKey[$key]] = 'NO';
	        }else{
	            $return[$paidSerchKey[$key]] = '';
	        }
	    }
	    
	    foreach ($paidSerchKey as $v3){
	    
	        if(!isset($return[$v3]))
	            $return[$v3] = '';
	    
	    }
	    
	    return $return;   
	}
	
	function GetTrackingInfoFromByPage($mid,$request){
	    
	    $return = array();
	    
	    //Transaction Reporting
	    $request_url = "http://cli.linksynergy.com/cli/publisher/programs/Tracking/transaction_reporting_detail.php?mid=$mid";
	    $track_info = $this->GetHttpResult($request_url, $request, '', "transaction_{$mid}");
	    preg_match_all('/<tr>([\s\S]*)<\/tr>/iU', $track_info,$matches);
	    $trackKey = array(
	        '1'=>'TransactionReportingMethod',
	        '2'=>'TransactionReportingLastProcessed',
	        '3'=>'TransactionReportingMediaOptReportEnabled',
	        '4'=>'TransactionReportingMediaOptReportApproved',
	    );
	    foreach ($matches[0] as $key=>$value){
	        
	        if($key == 0) continue;
	        preg_match_all('/<td class = "tdTextAlignLeft">([\s\S]*)<\/td>/iU', $value,$mat);
	        $return[$trackKey[$key]] = addslashes(trim(strip_tags($mat[1][1])));
	        
	    }
	    
	    foreach ($trackKey as $v1){
	        
	        if(!isset($return[$v1]))
	            $return[[$v1]] = '';
	        
	    }
	    
	    //Mobile Tracking Test Results  
	    $request_url = "http://cli.linksynergy.com/cli/publisher/programs/Tracking/mobile_tracking_detail.php?mid=$mid";
	    $track_info = $this->GetHttpResult($request_url, $request, '', "mobile_test_{$mid}");
	    preg_match_all('/<tr>([\s\S]*)<\/tr>/iU', $track_info,$matches);
	    $trackKeyRedirect = array(
	        '1'=>'MobileTrackingResultsiPhoneRedirectBehavior',
	        '2'=>'MobileTrackingResultsAndroidRedirectBehavior',
	        '3'=>'MobileTrackingResultsBlackberryRedirectBehavior',
	        '4'=>'MobileTrackingResultsiPadRedirectBehavior',
	    );
	    $trackKeyReported = array(
	        '1'=>'MobileTrackingResultsiPhoneTransactionReported',
	        '2'=>'MobileTrackingResultsAndroidTransactionReported',
	        '3'=>'MobileTrackingResultsBlackberryTransactionReported',
	        '4'=>'MobileTrackingResultsiPadTransactionReported',
	    );
	    foreach ($matches[0] as $key=>$value){
	         
	        if($key == 0) continue;
	        preg_match_all('/<td class = "tdTextAlignCenter">([\s\S]*)<\/td>/iU', $value,$mat);
	        $return[$trackKeyRedirect[$key]] = addslashes(trim($mat[1][0]));
	        $return[$trackKeyReported[$key]] = addslashes(trim($mat[1][1]));
	         
	    }
	    
	    foreach ($trackKeyRedirect as $v2){
	         
	        if(!isset($return[$v2]))
	            $return[[$v2]] = '';
	         
	    }
	    
	    foreach ($trackKeyReported as $v3){
	    
	        if(!isset($return[$v3]))
	            $return[[$v3]] = '';
	    
	    }
	    
	    return $return;
	}
	
    
	function GetHttpResult($url, $request, $valStr, $cacheFileName)
    {
        $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "data_" . date("Ym") . "{$cacheFileName}.dat", $this->batchProgram, true);
        if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
            $results = $this->GetHttpResultMoreTry($url, $request, $valStr);
            if (!$results) {
                mydie("Can't get the content of '{$url}', please check the val string !\r\n");
            }
            $this->oLinkFeed->fileCachePut($cache_file, $results);

            return $results;
        }
        $result = file_get_contents($cache_file);

        return $result;
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
