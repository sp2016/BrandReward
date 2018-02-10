<?php
require_once 'text_parse_helper.php';
class LinkFeed_TradeTracker
{
	function getCouponFeed()
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		$check_date = date('Y-m-d H:i:s');
		$affiliateSiteID = $this->affiliateSiteID;
		$client = $this->getSoapClient();
		$methods = array('getMaterialIncentiveVoucherItems', 'getMaterialIncentiveOfferItems');
		foreach ($methods as $method)
		{
			$option = array();
			$option['limit'] = 200;
			$option['offset'] = 0;
			$option['sort'] = 'modificationDate';
			$option['SortDirection'] = 'descending';
			$page = 1;
			$data = $client->$method($affiliateSiteID, 'html', $option);
			while (!empty($data) && is_array($data) && count($data) > 0 && $page < 100)
			{
				$links = array();
				foreach ($data as $v)
				{
					if (empty($v))
						continue;
					$link = array(
						"AffId" => $this->info["AffId"],
						"AffMerchantId" => $v->campaign->ID,
						"AffLinkId" => $v->ID,
						"LinkName" => $v->name,
						"LinkDesc" => '' . $v->description,
						"LinkStartDate" => parse_time_str(empty($v->validFromDate) ? $v->creationDate : $v->validFromDate, 'Y-m-d H:i:s', false),
						"LinkEndDate" => parse_time_str($v->validToDate, 'Y-m-d H:i:s', false),
						"LinkPromoType" => 'N/A',
						"LinkHtmlCode" => $v->code,
						"LinkOriginalUrl" => '',
						"LinkImageUrl" => '',
						"LinkAffUrl" => '',
						"DataSource" => $this->DataSource,
				        "Type"       => 'promotion'
					);
					switch ($method)
					{
						case 'getMaterialIncentiveOfferItems':
						case 'getMaterialIncentiveVoucherItems':
							$link['LinkPromoType'] = 'COUPON';
							$link['LinkCode'] = $v->voucherCode;
							break;
						default:
							break;
					}
					if (preg_match('@a href="(.*?)"@', $link['LinkHtmlCode'], $g))
						$link['LinkAffUrl'] = html_entity_decode($g[1]);
						
					if (!preg_match('@http://tc.tradetracker.net/.*@', $link['LinkAffUrl'], $g))
					{
						$link['LinkAffUrl'] = preg_replace("/&r=.*/", "[SUBTRACKING]", $link['LinkAffUrl']);
					}
					
					
					if (empty($link['AffMerchantId']) || empty($link['AffLinkId'])  || empty($link['LinkHtmlCode']))
						continue;
                    elseif(empty($link['LinkName'])){
                        $link['LinkPromoType'] = 'link';
                    }
					$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
					$links[] = $link;
					$arr_return["AffectedCount"] ++;
				}
				echo sprintf("api method:%s, page:%s, %s link(s) found.\n", $method, $page, count($links));
				if (count($links) > 0)
					$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
				$option['offset'] = $page * $option['limit'];
				$page ++;
				/*if(($page%10) == 0){
					unset($this->soapClient);
					$client = $this->getSoapClient();
				}*/
				try{
					$data = $client->$method($affiliateSiteID, 'html', $option);
				
				}catch (Exception $e) {
					echo $e->getMessage()."\n";
					
					unset($this->soapClient);
					$client = $this->getSoapClient();
					
					$data = $client->$method($affiliateSiteID, 'html', $option);
				}
				
			}
		}
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'promotion');
		return $arr_return;
	}

	function GetAllLinksByAffId()
	{
	    
	    $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
	    $check_date = date('Y-m-d H:i:s');
	    $affiliateSiteID = $this->affiliateSiteID;
	    $client = $this->getSoapClient();
	    $methods = array('getMaterialTextItems', 'getMaterialBannerImageItems', 'getMaterialHTMLItems');
	    foreach ($methods as $method)
	    {
	        $option = array();
	        $option['limit'] = 200;
	        $option['offset'] = 0;
	        $option['sort'] = 'modificationDate';
	        $option['SortDirection'] = 'descending';
	        $page = 1;
	        $data = $client->$method($affiliateSiteID, 'html', $option);
	        while (!empty($data) && is_array($data) && count($data) > 0 && $page < 100)
	        {
	            $links = array();
	            foreach ($data as $v)
	            {
	                if (empty($v))
	                    continue;
	                $link = array(
	                    "AffId" => $this->info["AffId"],
	                    "AffMerchantId" => $v->campaign->ID,
	                    "AffLinkId" => $v->ID,
	                    "LinkName" => $v->name,
	                    "LinkDesc" => '' . $v->description,
	                    "LinkStartDate" => parse_time_str(empty($v->validFromDate) ? $v->creationDate : $v->validFromDate, 'Y-m-d H:i:s', false),
	                    "LinkEndDate" => parse_time_str($v->validToDate, 'Y-m-d H:i:s', false),
	                    "LinkPromoType" => 'N/A',
	                    "LinkHtmlCode" => $v->code,
	                    "LinkOriginalUrl" => '',
	                    "LinkImageUrl" => '',
	                    "LinkAffUrl" => '',
	                    "DataSource" => $this->DataSource,
	                    "Type"       => 'link'
	                );
	                switch ($method)
	                {
	                    case 'getMaterialBannerImageItems':
	                        if (!empty($v->materialBannerDimension))
	                            $link['LinkName'] .= sprintf('%s (%s X %s)', $link['LinkName'], $v->materialBannerDimension->width, $v->materialBannerDimension->height);
	                            if (preg_match('@<img src="(.*?)"@', $link['LinkHtmlCode'], $g))
	                                $link['LinkImageUrl'] = html_entity_decode($g[1]);
	                            break;
	                    case 'getMaterialTextItems':
	                        $code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc']);
	                        if (!empty($code))
	                        {
	                            $link['LinkCode'] = $code;
	                            $link['LinkPromoType'] = 'COUPON';
	                        }
	                        else
	                            $link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName'] . $link['LinkDesc']);
	                        break;
	                    case 'getMaterialHTMLItems':
	                    default:
	                        break;
	                }
	                if (preg_match('@a href="(.*?)"@', $link['LinkHtmlCode'], $g))
	                    $link['LinkAffUrl'] = html_entity_decode($g[1]);
	    
	                if (!preg_match('@http://tc.tradetracker.net/.*@', $link['LinkAffUrl'], $g))
	                {
	                    $link['LinkAffUrl'] = preg_replace("/&r=.*/", "[SUBTRACKING]", $link['LinkAffUrl']);
	                }
	    
	    
	                if (empty($link['AffMerchantId']) || empty($link['AffLinkId'])  || empty($link['LinkHtmlCode']))
	                    continue;
	                elseif(empty($link['LinkName'])){
	                    $link['LinkPromoType'] = 'link';
	                }
	                $this->oLinkFeed->fixEnocding($this->info, $link, "feed");
	                $links[] = $link;
	                $arr_return["AffectedCount"] ++;
	            }
	            echo sprintf("api method:%s, page:%s, %s link(s) found.\n", $method, $page, count($links));
	            if (count($links) > 0)
	                $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
	            $option['offset'] = $page * $option['limit'];
	            $page ++;
	            
	            try{
	                $data = $client->$method($affiliateSiteID, 'html', $option);
	    
	            }catch (Exception $e) {
	                echo $e->getMessage()."\n";
	                
	                unset($this->soapClient);
	                $client = $this->getSoapClient();
	                
	                $data = $client->$method($affiliateSiteID, 'html', $option);
	            }
	    
	        }
	    }
	    $this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link');
	    return $arr_return;
	    
	}
	
	private function getSoapClient()
	{
		if (empty($this->soapClient))
		{
			$client = new SoapClient("http://ws.tradetracker.com/soap/affiliate?wsdl", array('trace'=> true));
			$client->authenticate($this->customerID, $this->passphrase);
			$this->soapClient = $client;
		}
		return $this->soapClient;
	}

	function GetAllLinksFromAffByMerID($merinfo)
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		return $arr_return;
	}

	function login()
	{
		$r = $this->oLinkFeed->GetHttpResult($this->info['AffLoginUrl']);
		$re = $r['content'];
		$token = trim($this->oLinkFeed->ParseStringBy2Tag($re, 'name="__FORM" value="', '"'));
		$this->info['AffLoginPostString'] .= $token;
		//print_r($this->info['AffLoginPostString']);
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);
	}
	
	function getMessage()
	{
		$this->login();
		$messages = array();
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", );
		//$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);
		$url = 'https://affiliate.tradetracker.com/affiliateTicket?desc=1&outputType=1&sort=&c=&r=&limit=500';
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r['content'];
		if(preg_match('@<tbody>.*?<tbody>(.*?)</tbody>@ms', $content, $g))
			$content = $g[1];
		preg_match_all('@<tr.*?>(.*?)</tr>@ms', $content, $chapters);
		if (empty($chapters) || !is_array($chapters) || empty($chapters[1]) || !is_array($chapters[1]))
			return 'no message found.';
		foreach ($chapters[1] as $chapter)
		{
			preg_match_all('@<td.*?>(.*?)</td>@ms', $chapter, $tds);
			//print_r($tds);exit;
			if (empty($tds) || empty($tds[1]) || !is_array($tds[1]) || count($tds[1]) < 3)
				continue;
			$data = array(
					'affid' => $this->info["AffId"],
					'messageid' => '',
					'sender' => trim(html_entity_decode(strip_tags($tds[1][1]))),
					'title' => '',
					'content' => '',
					'created' => parse_time_str(trim(html_entity_decode(strip_tags($tds[1][2]))), 'd/m/Y H:i', false),
			);
			if (preg_match('@^<a href="(https://affiliate\.tradetracker\.com/affiliateTicket/view/ID/(\d+)\?.*?)"@', $tds[1][0], $g))
			{
				$data['messageid'] = $g[2];
				$data['content_url'] = $g[1];
			}
			$data['title'] = trim(html_entity_decode($this->oLinkFeed->ParseStringBy2Tag($tds[1][0], array('<a href="', 'title="'), '"')));
			
			if (empty($data['messageid']) || empty($data['title']))
				continue;
			$messages[] = $data;
		}
		return $messages;
	}
	
	function getMessageDetail($data)
	{
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", );
		$url = $data['content_url'];
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r['content'];
		/* if (preg_match('@<div class="component-header"><h2>(.*?)</h2>@ms', $content, $g))
			$data['title'] = trim(html_entity_decode($g[1])); */
		if (preg_match('@<div id="ticket-messages">(.*?)<h4@ms', $content, $g))
			$data['content'] = trim(html_entity_decode($g[1]));
		return $data;
	}

	function GetProgramFromAff()
	{
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
		//$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);
		$this->GetProgramByApi();
		$this->checkProgramOffline($this->info["AffId"], $check_date);
		
		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}
	
	function GetProgramByApi()
	{
		echo "\tGet Program by api start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"{$this->info["AffId"]}_".date("YW").".dat", "program", true);
		$cache = array();
		if($this->oLinkFeed->fileCacheIsCached($cache_file)){
			$cache = file_get_contents($cache_file);
			$cache = json_decode($cache,true);
		}
		
		$client  = new SoapClient("http://ws.tradetracker.com/soap/affiliate?wsdl", array('trace'=> true));
		$client->authenticate($this->customerID, $this->passphrase);
		$aff_site_arr = $client->getAffiliateSites();
		//foreach($aff_site_arr as $aff_site){
		//foreach ($client->getCampaigns($aff_site->ID, '') as $prgm) {
		foreach ($client->getCampaigns($this->affiliateSiteID, '') as $prgm) {
			$strMerID = $prgm->ID;
			if(!$strMerID) continue;
			
			$CategoryExt = "";
			if(isset($prgm->info->category)) $CategoryExt = $prgm->info->category->name;
			
			$Partnership = "NoPartnership";
			$StatusInAffRemark = $prgm->info->assignmentStatus;
			if($StatusInAffRemark == 'accepted'){
				$Partnership = 'Active';
			}elseif($StatusInAffRemark == 'rejected'){
				$Partnership = 'Declined';
			}elseif($StatusInAffRemark == 'pending'){
				$Partnership = 'Pending';
			}

			$SupportDeepurl = $prgm->info->deeplinkingSupported;
			if($SupportDeepurl == 1){
				$SupportDeepurl = "YES";
			}else{
				$SupportDeepurl = "NO";
			}
			
			$AffDefaultUrl = $prgm->info->trackingURL;
			$CommissionExt = 'Lead:'.$prgm->info->commission->leadCommission.',Sales:'.$prgm->info->commission->saleCommissionFixed.',Sales(%):'.$prgm->info->commission->saleCommissionVariable;
			$LogoUrl = $prgm->info->imageURL;
			
			$arr_prgm[$strMerID] = array(
				"AffId" => $this->info["AffId"],
				"IdInAff" => $strMerID,
				"Name" => addslashes($prgm->name),
				"Homepage" => addslashes($prgm->URL),
				"CategoryExt" => addslashes($CategoryExt),
				"Description" => addslashes($prgm->info->campaignDescription),
				"CreateDate" => $prgm->info->startDate,
				"StatusInAffRemark" => addslashes($StatusInAffRemark),
				"StatusInAff" => 'Active',						//'Active','TempOffline','Offline'
				"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
				"LastUpdateTime" => date("Y-m-d H:i:s"),
				"SupportDeepUrl" => $SupportDeepurl,
				"AffDefaultUrl" => addslashes($AffDefaultUrl),
				"CommissionExt" => addslashes($CommissionExt),
				"AllowNonaffCoupon" => "UNKNOWN",
			    "TermAndCondition" => addslashes($prgm->info->characteristics),
				"LogoUrl" => addslashes($LogoUrl),				
			);
			
			if(SID == 'bdg02'){				
				$arr_prgm[$strMerID]['PublisherPolicy'] = addslashes($prgm->info->policyDiscountCodeStatus);
			}
			
			/*$request = $request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "",);
			$prgm_url = "https://affiliate.tradetracker.com/affiliateCampaign/view/ID/{$strMerID}?returnURL=affiliateCampaign%2Flist%3FsubCategoryID%3D0%26desc%3D%26offset%3D0%26outputType%3D1%26rand%3D2c7a39d";
			if(!isset($cache[$strMerID]['allow'])){
				$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
				if($prgm_arr['code'] == 200){
					$prgm_detail = $prgm_arr["content"];
					$prgm_detail = str_replace(array("\r\n", "\r", "\n", " "), "", $prgm_detail);
					//print_r($prgm_detail);
					$cache[$strMerID]['allow'] = "1";
					$allowstr1 = "Affiliates may only promote codes that are provided through  a1travel.com affiliate programme. Only generic codes supplied through the Tradetracker account are authorised for use by all affiliates";
					$allowstr2 = "Affiliates may ONLY advertise coupon codes that are distributed by the merchant. Any sales registered through other coupon codes will not be considered as valid and will be rejected";
					$allowstr3 = "Affiliates are not allowed to promote coupon codes that have not been issued via the affiliate channel";
					$allowstr1 = str_replace(array("\r\n", "\r", "\n", " "), "", $allowstr1);
					$allowstr2 = str_replace(array("\r\n", "\r", "\n", " "), "", $allowstr2);
					$allowstr3 = str_replace(array("\r\n", "\r", "\n", " "), "", $allowstr3);
					if(stripos($prgm_detail, $allowstr1) !== false || stripos($prgm_detail, $allowstr2) !== false || stripos($prgm_detail, $allowstr3) !== false){
						$arr_prgm[$strMerID]['AllowNonaffCoupon'] = "NO";
					}
				}
			}*/
			
			$program_num++;
			
			if(count($arr_prgm) >= 100){
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
				$arr_prgm = array();
			}
		}
		//}
		
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
			unset($arr_prgm);
		}
		
		$cache = json_encode($cache);
		$this->oLinkFeed->fileCachePut($cache_file, $cache);
		echo "\tGet Program by api end\r\n";
		
		if($program_num < 10){
			mydie("die: program count < 10, please check program.\n");
		}
		
		echo "\tUpdate ({$program_num}) program.\r\n";
		echo "\tSet program country int.\r\n";
		
		$objProgram->setCountryInt($this->info["AffId"]);
	}
	
	function checkProgramOffline($AffId, $check_date){
		$objProgram = new ProgramDb();
		$prgm = array();
		$prgm = $objProgram->getNotUpdateProgram($this->info["AffId"], $check_date);
		
		if(count($prgm) > 30){
			mydie("die: too many offline program (".count($prgm).").\n");
		}else{
			$objProgram->setProgramOffline($this->info["AffId"], $prgm);
			echo "\tSet (".count($prgm).") offline program.\r\n";
		}
	}
}

"	[ID] => 9325
    [name] => 100dayloans.co.uk
    [URL] => http://www.100dayloans.co.uk/
    [info] => stdClass Object
        (
            [category] => stdClass Object
                (
                    [ID] => 13
                    [name] => Financial products
                    [categories] =>
                )

            [subCategories] => Array
                (
                    [0] => stdClass Object
                        (
                            [ID] => 160
                            [name] => Loans
                        )

                )

            [campaignDescription] => 100DayLoans UK is an online marketplace where consumers can apply and get matched with one of our many lenders within minutes. 100DayLoans UK is the solution to the short-term money problems millions of Britons face each year. Applicants can qualify for an unsecured, personal loan for up to 鎷�,000 cash which is directly deposited into their checking account. We are dedicated to continually optimizing our site to be user friendly and secure.
            [shopDescription] => 
            [targetGroup] => 
            [characteristics] => Possible reasons for deletion of sales: Lead is fraudulent in any way - customer information is fake, customer's info is stolen and put in, lead is fired manually.<br />De-duplication Rules: Last click wins across affiliate channels.
            [imageURL] => http://static.tradetracker.net/gb/campaign_image_small/9325.png
            [trackingURL] => http://tc.tradetracker.net/?c=9325&m=0&a=62862&r=&u=
            [commission] => stdClass Object
                (
                    [impressionCommission] => 0.000
                    [clickCommission] => 0.000
                    [fixedCommission] => 0.000
                    [leadCommission] => 30.000
                    [saleCommissionFixed] => 0.000
                    [saleCommissionVariable] => 0.000
                    [iLeadCommission] => 0.000
                    [iSaleCommissionFixed] => 0.000
                    [iSaleCommissionVariable] => 0.000
                )

            [assignmentStatus] => accepted
            [startDate] => 2013-02-07
            [stopDate] => 
            [clickToConversion] => P30D
            [policySearchEngineMarketingStatus] => disallowed
            [policyEmailMarketingStatus] => allowed
            [policyCashbackStatus] => disallowed
            [policyDiscountCodeStatus] => disallowed
            [deeplinkingSupported] => 1
            [referencesSupported] => 1
            [leadMaximumAssessmentInterval] => P1M
            [leadAverageAssessmentInterval] => P26DT1H25M23S
            [saleMaximumAssessmentInterval] => P1M
            [saleAverageAssessmentInterval] => "
?>
