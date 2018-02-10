<?php
require_once 'text_parse_helper.php';
require_once 'xml2array.php';

class LinkFeed_Commission_Junction
{
	var $CJ_API_KEY, $CJ_API_PID, $CJ_API_CID;
	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);		
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		
		$this->PASSWORD = 'BR80#hknt0';
		$this->CJ_API_KEY = '00973c68e5c0d8ac6eba2706f9e81dfb02c087749be2d9380dd706ad63bda85326376fd3eec5cbce3735d1df7bebcac234ac52c37fa0cc4fd3e284a6515ca01e7d/469ac94e19ce0e12538dcccff6f1a8320cb83054667f8a8fcb872e839613735d74bc62ed454aa7e6372c8d681e627729831a383a09f7aac1cca2d04b9ee26d81';
		$this->CJ_API_PID = '8030429';
		$this->CJ_API_CID = '4708894';
		$this->UserID = '4217409'; 
		
		
		$this->batchProgram = date('Ymd')."_program_".$this->oLinkFeed->batchID;
		
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

        echo 'Site:' . $this->site['Name']. "\r\n";
        $this->GetProgramByApi($this->site['SiteID']);

		echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";

        $this->oLinkFeed->checkBatchID = $this->oLinkFeed->batchID;
        $this->oLinkFeed->CheckCrawlBatchData($this->info["AffID"], $this->site['SiteID']);
	}

	function GetProgramByApi($SiteID)
	{
		echo "\tGet Program by api start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		$request = array("AffId" => $this->info["AffID"], "method" => "post", "postdata" => "",);

		//step 1,login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffID"], $this->info);
		$link = "https://advertiser-lookup.api.cj.com/v3/advertiser-lookup?";
		$order_arr = array("joined", "notjoined");

		$use_true_file_name = true;
		
		$xml2arr = new XML2Array();

        $arr_deepdomain = $this->getSupportDeepUrl();

		foreach ($order_arr as $vvv) {
			list($nPageNo, $nNumPerPage, $bHasNextPage, $nPageTotal) = array(1, 100, true, 1);
			while ($bHasNextPage) {
				$param = array(
					"advertiser-ids=$vvv",    //CIDs,joined,notjoined
					"advertiser-name=",
					"keywords=",
					"page-number={$nPageNo}",
					"records-per-page={$nNumPerPage}",
					//"mobile-tracking-certified=false",
				);
				echo "Page:$nPageNo\t";
//                print_r($param);
				$postdata = implode("&", $param);
				$strUrl = $link . $postdata;
				$request = array("method" => "get", "addheader" => array("authorization: {$this->CJ_API_KEY}"),);
				$result = $this->GetHttpResult($strUrl, $request, '', "list_{$vvv}_{$nPageNo}", $use_true_file_name);

				if (empty($result))
					continue;
				$re = $xml2arr->createArray($result);
				$re = $re['cj-api']['advertisers'];

//                print_r($re['advertiser'][0]);die;
//                print_r($re['@attributes']);
				$total_matched = $re['@attributes']["total-matched"];
				$records_returned = $re['@attributes']["records-returned"];
				$page_number = $re['@attributes']["page-number"];

				$nPageTotal = ceil($total_matched / $nNumPerPage);
				$bHasNextPage = $page_number;

				foreach($re['advertiser'] as $advertiser){
					$CategoryExt = $advertiser['primary-category']['parent'].'-'.$advertiser['primary-category']['child'];
					if(!$advertiser['primary-category']['parent']){
						$CategoryExt = $advertiser['primary-category']['child'];
					}

					$Commission = array();
					$CommissionVal = '';
//                    print_r($advertiser['actions']);
					$c=$advertiser['actions'];
					if(isset($c['action']['name'])){
						   //action 只有一个
						if(isset($c['action']['commission']['itemlist'])) {
							if(is_array($c['action']['commission']['default'])){
								$Commission[] = $c['action']['name'].":".$c['action']['commission']['default']['@attributes']['type'].":".$c['action']['commission']['default']['@value'];
								$CommissionVal = $c['action']['commission']['default']['@attributes']['type'].":".$c['action']['commission']['default']['@value'];
							}else {
								$Commission[] = $c['action']['name'] . ":" . $c['action']['type'] . ":" . $c['action']['commission']['default'];
								$CommissionVal = $c['action']['type'] . ":" . $c['action']['commission']['default'];
							}
							if(isset($c['action']['commission']['itemlist'][0])) {
								foreach ($c['action']['commission']['itemlist'] as $item) {
									$Commission[] = $item["@attributes"]['name'] . ":sub:" . $item["@value"];
								}
							}else{
								$Commission[] = $c['action']['commission']['itemlist']['@attributes']['name'].":".$c['action']['type'].":".$c['action']['commission']['itemlist']['@value'];
							}
						}
						else{
							if(is_array($c['action']['commission']['default'])){
								$Commission[] = $c['action']['name'].":".$c['action']['commission']['default']['@attributes']['type'].":".$c['action']['commission']['default']['@value'];
								$CommissionVal = $c['action']['commission']['default']['@attributes']['type'].":".$c['action']['commission']['default']['@value'];
							}else{
								$Commission[] = $c['action']['name'].":".$c['action']['type'].":".$c['action']['commission']['default'];
								$CommissionVal = $c['action']['type'].":".$c['action']['commission']['default'];
							}
						}
					}elseif(isset($c['action'][0])){
						   //action有多个
						foreach($c['action'] as $v){
							if(isset($v['commission']['itemlist'])) {
								if(is_array($v['commission']['default'])){
									$Commission[] = $v['name'].":".$v['commission']['default']['@attributes']['type'].":".$v['commission']['default']['@value'];
									$CommissionVal = $v['commission']['default']['@attributes']['type'].":".$v['commission']['default']['@value'];
								}else{
									$Commission[] = $v['name'] . ":" . $v['type'] . ":" . $v['commission']['default'];
									$CommissionVal = $v['type'] . ":" . $v['commission']['default'];
								}
								if(isset($v['commission']['itemlist'][0])) {
									foreach ($v['commission']['itemlist'] as $item) {
										$Commission[] = $item["@attributes"]['name'] . ":sub:" . $item["@value"];
									}
								}else{
									$Commission[] = $v['commission']['itemlist']['@attributes']['name'].":".$v['type'].":".$v['commission']['itemlist']['@value'];
								}
							}
							else{
								if(is_array($v['commission']['default'])){
									$Commission[] = $v['name'].":".$v['commission']['default']['@attributes']['type'].":".$v['commission']['default']['@value'];
									$CommissionVal = $v['commission']['default']['@attributes']['type'].":".$v['commission']['default']['@value'];
								}else{
									$Commission[] = $v['name'].":".$v['type'].":".$v['commission']['default'];
									$CommissionVal = $v['type'].":".$v['commission']['default'];
								}
							}
						}
					}

					$CommissionExt  = implode('|',$Commission);
					$IdInAff = trim($advertiser["advertiser-id"]);
					if (empty($IdInAff) || empty($advertiser["advertiser-name"])) continue;

					$homePage = strtolower($advertiser["program-url"]);


					$arr_prgm[$IdInAff] = array(
						"SiteID" => $SiteID,
						"AccountID" => $this->account['AccountID'],
						"Name" => addslashes(trim($advertiser["advertiser-name"])),
						"BatchID" => $this->oLinkFeed->batchID,
						"IdInAff" => $IdInAff,
						"AffID" => $this->info["AffID"],
						"Url" => addslashes($homePage),
						"RankInAff" => addslashes($advertiser["network-rank"]),
						"StatusInAff" => addslashes($advertiser["account-status"]),
						"Partnership" => addslashes($advertiser["relationship-status"]),
						"EPC7day" => addslashes($advertiser["seven-day-epc"]),
						"EPC3month" => addslashes($advertiser["three-month-epc"]),
						"LastUpdateTime" => date("Y-m-d H:i:s"),
						"MobileCertified" => addslashes($advertiser['mobile-tracking-certified']),
						"CommissionDetails" => addslashes($CommissionExt),
						"Commission" => addslashes($CommissionVal),
						"Category" => addslashes($CategoryExt),
						"Language" => addslashes($advertiser["language"]),
						"PerformanceIncentives" => 	addslashes($advertiser["performance-incentives"]),
						"AcceptedDate" => '',
						"ServiceableArea" => '',
                        "SupportDeepUrl" => 'UNKNOWN'
					);

                    if (count($arr_deepdomain)) {
                        $Homepage = preg_replace("/^https?:\\/\\/(.*?)\\/?/i", "\$1", $homePage);

                        if (isset($arr_deepdomain[$Homepage])) {
                            $arr_prgm[$IdInAff]['SupportDeepUrl'] = "YES";
                        } else {
                            $Homepage = preg_replace("/^ww.{0,2}\./i", "", $Homepage);
                            if (isset($arr_deepdomain[$Homepage])) {
                                $arr_prgm[$IdInAff]['SupportDeepUrl'] = "YES";
                            }
                        }
                    }

					//print_r($arr_prgm);exit;
					$request = array("AffId" => $this->info["AffID"], "method" => "get",);


						/*

					BathProcessTransactions

					   */
                    $prgm_url = "https://members.cj.com/member/advertiser/$IdInAff/batchTracking.json";
					$cache_file = $this->GetHttpResult($prgm_url, $request, '', "batchTracking_{$IdInAff}", $use_true_file_name);
					//if($cache_file){
						$cache_file = json_decode($cache_file);
						$arr_prgm[$IdInAff]['BathProcessTransactions'] = @addslashes($cache_file->advertiser->hasBatch);

					/*
					 * detail
					 */
                    $prgm_url = "https://members.cj.com/member/advertiser/$IdInAff/detail.json";
					$cache_file = $this->GetHttpResult($prgm_url, $request, '', "detail_{$IdInAff}", $use_true_file_name);
					//if($cache_file){
						$cache_file = json_decode($cache_file);
						$arr_prgm[$IdInAff]['Description'] = @addslashes($cache_file->advertiser->description);
						$arr_prgm[$IdInAff]['ContactCompany'] = @addslashes($cache_file->advertiser->organization);
						$arr_prgm[$IdInAff]['ContactCountry'] = @addslashes($cache_file->advertiser->countryName);
						$arr_prgm[$IdInAff]['Currency'] = @addslashes($cache_file->advertiser->currencyName);
						$arr_prgm[$IdInAff]['ContentCertified'] = @addslashes($cache_file->advertiser->crossDeviceEnabled);

						if(isset($cache_file->advertiser->liveDate)){
							$arr_prgm[$IdInAff]['JoinedNetworkDate'] = date('Y-m-d H:i:s', substr($cache_file->advertiser->liveDate, 0, -3));
						}else{
							$arr_prgm[$IdInAff]['JoinedNetworkDate'] = '';
						}

					//}

					/*
					 * contact
					 */
                    $prgm_url = "https://members.cj.com/member/advertiser/$IdInAff/contact/".$this->CJ_API_CID.".json";
					$cache_file = $this->GetHttpResult($prgm_url, $request, '', "contact_{$IdInAff}", $use_true_file_name);
					//if($cache_file){
						$cache_file = json_decode($cache_file);
						//print_r($cache_file);
						$arr_prgm[$IdInAff]['ContactEmail'] = addslashes($cache_file->contact->email);
						$arr_prgm[$IdInAff]['ContactPerson'] = addslashes($cache_file->contact->name);

					//}
					//print_r($arr_prgm);
					/*
					 * terms
					 */
                    $prgm_url = "https://members.cj.com/member/publisher/".$this->CJ_API_CID."/advertiser/$IdInAff/activeProgramTerms.json";
					$cache_file = $this->GetHttpResult($prgm_url, $request, '', "terms_{$IdInAff}", $use_true_file_name);
					//if($cache_file){
						$cache_file = json_decode($cache_file, true);
						$arr_prgm[$IdInAff]['SupportedCurrency'] = @addslashes($cache_file['activeProgramTerms']['advertiserFunctionalCurrency']);

						$policies = @$cache_file['activeProgramTerms']['policies']['policiesList'];

						$policies_arr = array();
                        $policiesStr = '';
						if(is_array($policies)){
							foreach($policies as $tmp_po){
								$policies_arr[$tmp_po['policyId']] = $tmp_po['policyText'];
							}
                            $policiesStr .= $tmp_po['policyId'] . '::' . $tmp_po['policyTitle'] . '::' .  $tmp_po['policyText'] . ' # ';
						}

                        $arr_prgm[$IdInAff]['Policies'] = addslashes(rtrim($policiesStr, ' # '));
						$arr_prgm[$IdInAff]['SearchCampaignsSpecialInstructionsForSearchMarketingPublishers'] = isset($policies_arr['special_instructions_for_search_marketing_publishers']) ? addslashes($policies_arr['special_instructions_for_search_marketing_publishers']) : '';
						$arr_prgm[$IdInAff]['SearchCampaignsNoncompeteSEMBiddingKeywords'] = isset($policies_arr['non_compete_sem_bidding_keywords']) ? addslashes($policies_arr['non_compete_sem_bidding_keywords']) : '';
						$arr_prgm[$IdInAff]['SearchCampaignsLimitedUseSEMDisplayURLContent'] = isset($policies_arr['limited_use_sem_display_url_content']) ? addslashes($policies_arr['limited_use_sem_display_url_content']) : '';
						$arr_prgm[$IdInAff]['SearchCampaignsLimitedUseSEMAdCopyContent'] = isset($policies_arr['limited_use_sem_ad_copy_content']) ? addslashes($policies_arr['limited_use_sem_ad_copy_content']) : '';
						$arr_prgm[$IdInAff]['SearchCampaignsAuthorizedSearchEngines'] = isset($policies_arr['authorized_search_engines']) ? addslashes($policies_arr['authorized_search_engines']) : '';
						$arr_prgm[$IdInAff]['SearchCampaignsRecommendedSEMBiddingKeywords'] = isset($policies_arr['recommended_sem_bidding_keywords']) ? addslashes($policies_arr['recommended_sem_bidding_keywords']) : '';
						$arr_prgm[$IdInAff]['SearchCampaignsProtectedSEMBiddingKeywords'] = isset($policies_arr['protected_sem_bidding_keywords']) ? addslashes($policies_arr['protected_sem_bidding_keywords']) : '';
						$arr_prgm[$IdInAff]['SearchCampaignsDirectLinking'] = isset($policies_arr['direct_linking']) ? addslashes($policies_arr['direct_linking']) : '';
						$arr_prgm[$IdInAff]['SearchCampaignsProhibitedSEMDisplayURLContent'] = isset($policies_arr['prohibited_sem_display_url_content']) ? addslashes($policies_arr['prohibited_sem_display_url_content']) : '';
						$arr_prgm[$IdInAff]['SearchCampaignsProhibitedSEMAdCopyContent'] = isset($policies_arr['prohibited_sem_ad_copy_content']) ? addslashes($policies_arr['prohibited_sem_ad_copy_content']) : '';
						$arr_prgm[$IdInAff]['WebSiteProhibitedWebSiteDomainKeywords'] = isset($policies_arr['prohibited_web_site_domain_keywords']) ? addslashes($policies_arr['prohibited_web_site_domain_keywords']) : '';
						$arr_prgm[$IdInAff]['WebSiteProhibitedWebSiteContent'] = isset($policies_arr['prohibited_web_site_content']) ? addslashes($policies_arr['prohibited_web_site_content']) : '';
						$arr_prgm[$IdInAff]['WebSiteUnacceptableWebSites'] = isset($policies_arr['unacceptable_web_sites']) ? addslashes($policies_arr['unacceptable_web_sites']) : '';
						$arr_prgm[$IdInAff]['WebSiteUseofLogosAndTrademarksInWebSites'] = isset($policies_arr['use_of_logos_and_trademarks_in_websites']) ? addslashes($policies_arr['use_of_logos_and_trademarks_in_websites']) : '';
						$arr_prgm[$IdInAff]['CouponsAndPromotionalCodes'] = isset($policies_arr['coupons_and_promotional_codes']) ? addslashes($policies_arr['coupons_and_promotional_codes']) : '';
						$arr_prgm[$IdInAff]['SubAffiliateMarketing'] = isset($policies_arr['sub_affiliates']) ? addslashes($policies_arr['sub_affiliates']) : '';
						$arr_prgm[$IdInAff]['MiscellaneousSpecialInstructions'] = isset($policies_arr['special_instructions_for_publishers']) ? addslashes($policies_arr['special_instructions_for_publishers']) : '';
						$arr_prgm[$IdInAff]['NegativeMatchingForProtecedKeywords'] = isset($policies_arr['negative_matching_for_protected_keywords']) ? addslashes($policies_arr['negative_matching_for_protected_keywords']) : '';
						$arr_prgm[$IdInAff]['NonCommissionItems'] = isset($policies_arr['non_commissionable_items']) ? addslashes($policies_arr['non_commissionable_items']) : '';
						$arr_prgm[$IdInAff]['WebSiteProhibitedWebSiteURLKeywords'] = isset($policies_arr['prohibited_web_site_url_keywords']) ? addslashes($policies_arr['prohibited_web_site_url_keywords']) : '';
						$arr_prgm[$IdInAff]['IncentivizedTraffic'] = isset($policies_arr['incentivized_traffic']) ? addslashes($policies_arr['incentivized_traffic']) : '';
						$arr_prgm[$IdInAff]['EmailMarketing'] = isset($policies_arr['email_promotional_method']) ? addslashes($policies_arr['email_promotional_method']) : '';
						$arr_prgm[$IdInAff]['ViewThroughCampaigns'] = isset($policies_arr['viewthrough_campaigns']) ? addslashes($policies_arr['viewthrough_campaigns']) : '';
						$arr_prgm[$IdInAff]['SoftwareMarketing'] = isset($policies_arr['software']) ? addslashes($policies_arr['software']) : '';
						$arr_prgm[$IdInAff]['SocialMediaMarketing'] = isset($policies_arr['social_media_promotional_method']) ? addslashes($policies_arr['social_media_promotional_method']) : '';



 						//print_r($arr_prgm);exit;
					//}
					$arr_prgm[$IdInAff]['Partnership'] = $vvv;
					/************************************************************************************************************************************************/

					$program_num++;

					if (count($arr_prgm) >= 1) {
						$request = array("AffId" => $this->info["AffID"], "method" => "get",);
						$id_list = implode(",", array_keys($arr_prgm));

                        $prgm_url = "https://members.cj.com/member/publisher/".$this->CJ_API_CID."/advertiserSearch.json?pageNumber=1&publisherId=".$this->CJ_API_CID."&pageSize=100&advertiserIds=$id_list&geographicSource=&sortColumn=advertiserName&sortDescending=false";
						$cache_file = $this->GetHttpResult($prgm_url, $request, '', "area_{$vvv}_{$nPageNo}", $use_true_file_name);
						//if($cache_file){
							$prgm_json = json_decode($cache_file);
							foreach ($prgm_json->advertisers as $v_j) {
								if (isset($arr_prgm[$v_j->advertiserId])) {
									//$arr_prgm[$v_j->advertiserId]["AcceptedDate"] = addslashes($v_j->activeStartDate);
									if(isset($v_j->activeStartDate)){
										$arr_prgm[$v_j->advertiserId]["AcceptedDate"] = date('Y-m-d H:i:s', substr($v_j->activeStartDate, 0, -3));
									}else{
										$arr_prgm[$v_j->advertiserId]["AcceptedDate"] = '';
									}
									//if (is_array($v_j->serviceableAreas) && count($v_j->serviceableAreas)) {
										$arr_prgm[$v_j->advertiserId]["ServiceableArea"] = addslashes(implode(",", $v_j->serviceableAreas));
									//}
									//print_r($arr_prgm);                 exit;
								}
							}
						//}

						$objProgram->updateProgram($this->info["AffID"], $arr_prgm);
						$arr_prgm = array();
						//exit;
					}
				}

                if ($nPageNo >= $nPageTotal || $records_returned < 100) {
                    $bHasNextPage = false;
                    if ($this->debug) print " NO NEXT PAGE  <br>\n";
                } else {
                    $nPageNo++;
                    if ($this->debug) print " Have NEXT PAGE  <br>\n";
                }

			}
			if (count($arr_prgm)) {
				$request = array("AffId" => $this->info["AffID"], "method" => "get",);
				$id_list = implode(",", array_keys($arr_prgm));
				$nPageNo+=1;
                $prgm_url = "https://members.cj.com/member/publisher/".$this->CJ_API_CID."/advertiserSearch.json?pageNumber=1&publisherId=".$this->CJ_API_CID."&pageSize=100&advertiserIds=$id_list&geographicSource=&sortColumn=advertiserName&sortDescending=false";
				$cache_file = $this->GetHttpResult($prgm_url, $request, '', "area_{$vvv}_{$nPageNo}", $use_true_file_name);
				//if($cache_file){
					$prgm_json = json_decode($cache_file);
					foreach ($prgm_json->advertisers as $v_j) {
						if (isset($arr_prgm[$v_j->advertiserId])) {
							if(isset($v_j->activeStartDate)){
								$arr_prgm[$v_j->advertiserId]["AcceptedDate"] = date('Y-m-d H:i:s', substr($v_j->activeStartDate, 0, -3));
							}else{
								$arr_prgm[$v_j->advertiserId]["AcceptedDate"] = '';
							}
							//if (is_array($v_j->serviceableAreas) && count($v_j->serviceableAreas)) {
								$arr_prgm[$v_j->advertiserId]["ServiceableArea"] = addslashes(implode(",", $v_j->serviceableAreas));								
							//}
						}							
					}
				//}			
				$objProgram->updateProgram($this->info["AffID"], $arr_prgm);				 
				unset($arr_prgm);
			}			
		}

		echo "\tGet Program by api end\r\n";
		if ($program_num < 10) {
			mydie("die: program count < 10, please check program.\n");
		}
		echo "\tUpdate ({$program_num}) program.\r\n";
	}


    function GetHttpResult($url, $request, $valStr, $cacheFileName, $useTrueName)
    {
        $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "data_" . date("Ym") . "_{$cacheFileName}.dat", $this->batchProgram, $useTrueName);
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

    function getSupportDeepUrl()
    {
        //http://[CJDOMAINS]/links/[SITEIDINAFF]/type/dlg/sid/[SUBTRACKING]/[PURE_DEEPURL]
        $domains_arr = array();
        $url = "http://www.yceml.net/am_gen/".$this->CJ_API_PID."/include/allCj/am.js";
        $tmp_arr = $this->oLinkFeed->GetHttpResult($url, array("method" => "get"));
        if ($tmp_arr["code"] == 200) {
            $domains = trim($this->oLinkFeed->ParseStringBy2Tag($tmp_arr["content"], 'domains=[', ']'));
            $domains_arr = array_flip(explode("','", trim($domains, "'")));
        }
        return $domains_arr;
    }

}
