<?php

require_once 'text_parse_helper.php';
if (SID == 'bdg01'){
	define('API_SID_28', 'IRcNBDKVBKGY344776PgAB9TZRc3izSuo3');
	define('API_TOKEN_28', 'Z2uLb@wFddScT#DzvDwjFnC9FvDxcsqg');
	define('AFFID_INAFF_28', '344776');
}else{
	define('API_SID_28', 'IRRpkMxEbHdJ345716EhMkqsdHwm5coaR3');
	define('API_TOKEN_28', '+yoHa(Ppqrj2otadLPJJoi7KjKxPEBjg');
	define('AFFID_INAFF_28', '345716');
}

class LinkFeed_28_DgmPro_AU
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
	}
	
	function getMerAffIDByURL($strURL)
	{
		return $this->getDgmMerIDByURL($strURL);
	}
	
	function getDgmMerIDByURL($strURL){
		//http://www.dgmpro.com/affiliates/index.cfm?fuseaction=dgmPro.moreinfo&cpid=16128&cmid=906
		// MerID_CampaignID
		
		$strURL = trim($strURL);
		if (substr($strURL, 0, 7) == 'http://'){
			$arrUrl = parse_url($strURL);
			if ($arrUrl['scheme'] == ''){
			}
			else{
				parse_str($arrUrl['query'], $arrQuery);
//				echo $arrUrl['query'];
//				print_r($arrQuery);
				$strMerID = trim($arrQuery['cmid']);
				$strCampaignID = trim($arrQuery['cpid']);
//				echo $strMerID.'_'.$strCampaignID;

				if (($strMerID == '') || ($strCampaignID == '')){
				}
				else{
					return $strMerID.'_'.$strCampaignID;
				}
			}
		}
		else{
		}
		return $strURL;
	}

	function getCouponFeed()
	{
		$check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, );
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", "no_ssl_verifyhost" => true);
		
		$AccountSid = API_SID_28;
		$AccountToken = API_TOKEN_28;
		$base_url = "https://{$AccountSid}:{$AccountToken}@api.impactradius.com";
		$nextPage = '';
		$perPage = 100;
		$pages = 1;
		$type = 'COUPON';
		do
		{
			if (empty($nextPage))
				$url = $base_url . "/2010-09-01/Mediapartners/{$AccountSid}/Ads.json?Type=$type&PageSize={$perPage}&Page=$pages";
			else
				$url = $base_url . $nextPage;
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			if (empty($r) || $r['code'] != 200 || empty($r['content']))
				break;
			$r = json_decode($r['content'], true);
			if (empty($r) || !is_array($r) || empty($r['Ads']))
				break;
			$nextPage = $r['@nextpageuri'];
			$data = $r['Ads'];
			$links = array();
			if (!empty($data) && is_array($data))
			{
				if (!empty($data['Id']) && (int)$data['Id']) // only one record.
					$data = array($data);
				
				foreach ($data as $v)
				{
					$link = array(
							"AffId" => $this->info["AffId"],
							"AffMerchantId" => $v['CampaignId'],
							"AffLinkId" => $v['Id'],
							"LinkName" => $v['Name'],
							"LinkDesc" => $v['Description'],
							"LinkStartDate" => parse_time_str($v['StartDate'], null, false),
							"LinkEndDate" => parse_time_str($v['EndDate'], null, true),
							"LinkPromoType" => 'link',
							"LinkHtmlCode" => $v['Code'],
							"LinkOriginalUrl" => $v['LandingPageUrl'],
							"LinkImageUrl" => $v['BogoGetImageUrl'],			//目前都为空
							"LinkAffUrl" => $v['TrackingLink'],
							"DataSource" => "33",
					        "Type" => "promotion",
					);
					
					if (!empty($v['DealDefaultPromoCode']))
					{
						$link['LinkCode'] = $v['DealDefaultPromoCode'];
						$link['LinkPromoType'] = 'coupon';
					} elseif(!empty($v['DealId'])) {
						$link['LinkPromoType'] = 'DEAL';
					}
					if (!empty($v['DealScope']))
					{
						if (!empty($v['DealCategories']))
							$link['LinkDesc'] .= sprintf('Discount Classification: %s, %s, ', ucwords(strtolower($v['DealScope'])), $v['DealCategories']);
						else
							$link['LinkDesc'] .= sprintf('Discount Classification: %s, ', ucwords(strtolower($v['DealScope'])));
					}
					
					if (!empty($v['DiscountAmount']))
						$link['LinkDesc'] .= sprintf('Discount Amount: %s, ', $v['DiscountAmount']);
					
					if (!empty($v['DiscountPercent']))
						$link['LinkDesc'] .= sprintf('Discount Percent: %s, ', $v['DiscountPercent']);
					
					if (empty($link['AffLinkId']) )
						continue;
					elseif(empty($link['LinkName'])){
						$link['LinkPromoType'] = 'link';
					}
					
					$links[] = $link;
					$arr_return['AffectedCount'] ++;
				}
			}
			echo sprintf("get link by api...%s link(s) found.\n", count($links));
			sleep(1);
			if (count($links) > 0)
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
			$pages ++;
		}while(!empty($nextPage) && ($pages < 100));

		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'promotion');
		return $arr_return;
	}

	function GetAllLinksByAffId()
	{
		$check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, );
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", "no_ssl_verifyhost" => true);
		
		$AccountSid = API_SID_28;
		$AccountToken = API_TOKEN_28;
		$base_url = "https://{$AccountSid}:{$AccountToken}@api.impactradius.com";
		$nextPage = '';
		$perPage = 100;
		$pages = 1;
		do
		{
			if (empty($nextPage))
				$url = $base_url . "/2010-09-01/Mediapartners/{$AccountSid}/Ads.json?PageSize={$perPage}&Page=$pages";
			else
				$url = $base_url . $nextPage;
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			if (empty($r) || $r['code'] != 200 || empty($r['content']))
				break;
			$r = json_decode($r['content'], true);
			if (empty($r) || !is_array($r) || empty($r['Ads']))
				break;
			$nextPage = $r['@nextpageuri'];
			$data = $r['Ads'];
			$links = array();
			if (!empty($data) && is_array($data))
			{
				if (!empty($data['Id']) && (int)$data['Id']) // only one record.
					$data = array($data);
				foreach ($data as $v)
				{
				    if($v['Type'] == 'COUPON') continue;
					$link = array(
							"AffId" => $this->info["AffId"],
							"AffMerchantId" => $v['CampaignId'],
							"AffLinkId" => $v['Id'],
							"LinkName" => $v['Name'],
							"LinkDesc" => $v['Description'],
							"LinkStartDate" => parse_time_str($v['StartDate'], null, false),
							"LinkEndDate" => parse_time_str($v['EndDate'], null, true),
							"LinkPromoType" => 'link',
							"LinkHtmlCode" => $v['Code'],
							"LinkOriginalUrl" => $v['LandingPageUrl'],
							"LinkImageUrl" => $v['BogoGetImageUrl'],			//目前都为空
							"LinkAffUrl" => $v['TrackingLink'],
							"DataSource" => "33",
					        "Type" => "link",
					);
					
					if (!empty($v['DealDefaultPromoCode']))
					{
						$link['LinkCode'] = $v['DealDefaultPromoCode'];
						$link['LinkPromoType'] = 'coupon';
					} elseif(!empty($v['DealId'])) {
						$link['LinkPromoType'] = 'DEAL';
					}
					if (!empty($v['DealScope']))
					{
						if (!empty($v['DealCategories']))
							$link['LinkDesc'] .= sprintf('Discount Classification: %s, %s, ', ucwords(strtolower($v['DealScope'])), $v['DealCategories']);
						else
							$link['LinkDesc'] .= sprintf('Discount Classification: %s, ', ucwords(strtolower($v['DealScope'])));
					}
					
					if (!empty($v['DiscountAmount']))
						$link['LinkDesc'] .= sprintf('Discount Amount: %s, ', $v['DiscountAmount']);
					
					if (!empty($v['DiscountPercent']))
						$link['LinkDesc'] .= sprintf('Discount Percent: %s, ', $v['DiscountPercent']);
					
					if (empty($link['AffLinkId']) )
						continue;
					elseif(empty($link['LinkName'])){
						$link['LinkPromoType'] = 'link';
					}
					
					$links[] = $link;
					$arr_return['AffectedCount'] ++;
				}
			}
			echo sprintf("get link by api...%s link(s) found.\n", count($links));
			sleep(1);
			if (count($links) > 0)
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
			$pages ++;
		}while(!empty($nextPage) && ($pages < 100));

		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link');
		return $arr_return;
		
	}
	
	function GetAllLinksFromAffByMerID($merinfo)
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", "no_ssl_verifyhost" => true);

		// coupon, text & banner by page
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info,4,false);
		$adTypes = array('COUPON', 'TEXT_LINK', 'BANNER');
		foreach ($adTypes as $adType)
		{
			$url = sprintf('https://www.dgmperformance.com.au/secure/nositemesh/directory/mediapartner/listads/pListAds.ihtml?campaignId=%s&accountId=%s&adType=%s',
				$merinfo['IdInAff'], AFFID_INAFF_28, $adType);
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$content = $r["content"];
			if (!preg_match('@data:(\{".*?\}),\s+pageSize:\d+@ms', $content, $g))
				continue;
			$data = @json_decode($g[1]);
			if (empty($data) || empty($data->records) || !is_array($data->records))
				continue;
			$links = array();
			foreach ($data->records as $v)
			{
				$link = array(
						"AffId" => $this->info["AffId"],
						"AffMerchantId" => $merinfo['IdInAff'],
						"AffLinkId" => $v->adId->dv,
						"LinkName" => $v->name->dv,
						"LinkDesc" => '',
						"LinkStartDate" => '0000-00-00',
						"LinkEndDate" => '0000-00-00',
						"LinkPromoType" => 'N/A',
						"LinkHtmlCode" => '',
						"LinkOriginalUrl" => '',
						"LinkImageUrl" => '',
						"LinkAffUrl" => sprintf('http://t.dgm-au.com/c/%s/%s/%s', AFFID_INAFF_28, $v->adId->dv, $merinfo['IdInAff']),
						"DataSource" => "33",
				);
				if ($adType == 'BANNER')
					$link['LinkImageUrl'] = sprintf('http://adn.impactradius.com/display-ad/%s-%s', $merinfo['IdInAff'], $v->adId->dv);
				if ($adType == 'COUPON')
				{
					$link['LinkPromoType'] = 'COUPON';
					$link['LinkCode'] = $this->get_linkcode_by_text_28($link['LinkName']);
					if (empty($link['LinkCode']))
						$link['LinkPromoType'] == $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
				}
				if ($adType == 'TEXT_LINK')
					$link['LinkPromoType'] == $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
				if (empty($link['AffLinkId']))
					continue;
                elseif(empty($link['LinkName'])){
                    $link['LinkPromoType'] = 'link';
                }
				$link['LinkHtmlCode'] = create_link_htmlcode_image($link);
				$links[] = $link;
				$arr_return['AffectedCount'] ++;
			}
			echo sprintf("get %s by page...%s link(s) found.\n", $adType, count($links));
			if (count($links) > 0)
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		}

		// coupon by api
		$AccountSid = API_SID_28;
		$AccountToken = API_TOKEN_28;
		$base_url = "https://{$AccountSid}:{$AccountToken}@api.impactradius.com";
		$nextPage = null;
		$pages = 1;
		do
		{
			if (empty($nextPage))
				$url = $base_url . "/2010-09-01/Mediapartners/{$AccountSid}/PromoAds.json?CampaignId={$merinfo['IdInAff']}";
			else
				$url = $base_url . $nextPage;
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			if (empty($r) || $r['code'] != 200 || empty($r['content']))
				break;
			$r = json_decode($r['content'], true);
			if (empty($r) || !is_array($r) || empty($r['PromotionalAd']))
				break;
			$nextPage = $r['@nextpageuri'];
			$data = $r['PromotionalAd'];
			$links = array();
			if (!empty($data) && is_array($data))
			{
				if (!empty($data['Id']) && (int)$data['Id']) // only one record.
					$data = array($data);
				foreach ($data as $v)
				{
					if ($v['Status'] == 'DEACTIVATED')
						continue;
					$link = array(
							"AffId" => $this->info["AffId"],
							"AffMerchantId" => $v['CampaignId'],
							"AffLinkId" => $v['Id'],
							"LinkName" => $v['LinkText'],
							"LinkDesc" => '',
							"LinkStartDate" => parse_time_str($v['StartDate'], null, false),
							"LinkEndDate" => parse_time_str($v['EndDate'], null, true),
							"LinkPromoType" => 'N/A',
							"LinkHtmlCode" => '',
							"LinkOriginalUrl" => "",
							"LinkImageUrl" => $v['ProductImageUrl'],
							"LinkAffUrl" => $v['TrackingLink'],
							"DataSource" => "33",
					);
					if ($v['PromoType'] == 'FREESHIPPING')
						$link['LinkPromoType'] = 'free shipping';
					if (!empty($v['PromoCode']))
					{
						$link['LinkCode'] = $v['PromoCode'];
						$link['LinkPromoType'] = 'coupon';
					}
					if (!empty($v['DiscountClassification']))
					{
						if (!empty($v['DiscountClassificationDetail']))
							$link['LinkDesc'] .= sprintf('Discount Classification: %s, %s, ', ucwords(strtolower($v['DiscountClassification'])), $v['DiscountClassificationDetail']);
						else
							$link['LinkDesc'] .= sprintf('Discount Classification: %s, ', ucwords(strtolower($v['DiscountClassification'])));
					}
					if (!empty($v['DiscountAmount']))
						$link['LinkDesc'] .= sprintf('Discount Amount: %s, ', $v['DiscountAmount']);
					if (!empty($v['DiscountPercent']))
						$link['LinkDesc'] .= sprintf('Discount Percent: %s, ', $v['DiscountPercent']);
					if (!empty($v['AdHtml']))
						$link['LinkHtmlCode'] .= str_replace('</h3>', '', str_replace('<h3>', '', $v['AdHtml']));
					if (empty($link['AffLinkId']) )
						continue;
                    elseif(empty($link['LinkName'])){
                        $link['LinkPromoType'] = 'link';
                    }
					$links[] = $link;
					$arr_return['AffectedCount'] ++;
				}
			}
			echo sprintf("get coupon by api...%s link(s) found.\n", count($links));
			sleep(1);
			if (count($links) > 0)
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
			$pages ++;
		}
		while(!empty($nextPage) && ($pages < 100));

		return $arr_return;
	}
	
	function GetAllProductsByAffId()
	{
	
		$check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
		$request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "");
	
		$arr_merchant = $this->oLinkFeed->getApprovalAffMerchant($this->info["AffId"]);
		$productNumConfig = $this->oLinkFeed->product_program_num_config($this->info["AffId"]);
		$productNumConfigAlert = '';
		$isAssignMerchant = FALSE;
		
		$mcount = 0;
		$AccountSid = API_SID_28;
		$AccountToken = API_TOKEN_28;
		foreach ($arr_merchant as $merchatInfo)
		{
		    echo $merchatInfo['IdInAff'].PHP_EOL;
		    $crawlMerchantsActiveNum = 0;
		    $setMaxNum  = isset($productNumConfig[$merchatInfo['IdInAff']]) ? $productNumConfig[$merchatInfo['IdInAff']]['limit'] :  100;
		    $isAssignMerchant = isset($productNumConfig[$merchatInfo['IdInAff']]) ? TRUE : FALSE;
		    
			$Catelog_url = "https://{$AccountSid}:{$AccountToken}@products.api.impactradius.com/Mediapartners/{$AccountSid}/Catalogs?CampaignId=".$merchatInfo['IdInAff'];
			$request['addheader'] = array('accept: application/json');
			$r = $this->oLinkFeed->GetHttpResult($Catelog_url,$request);
			$r = json_decode($r['content'], true);
			
			if (!$r['@total'])
				continue;
			
			$links = array();
			foreach ($r['Catalogs'] as $value)
			{
			    $TotalCount = $value['NumberOfItems'];
			    $pages = 1;
				do{
				    
				    //$url = "https://{$AccountSid}:{$AccountToken}@products.api.impactradius.com".$value['ItemsUri']."?PageSize=50&Page=$pages";
				    if($pages==1)
				        $url = "https://{$AccountSid}:{$AccountToken}@products.api.impactradius.com".$value['ItemsUri']."?PageSize=100";
				    else 
				        $url = "https://{$AccountSid}:{$AccountToken}@products.api.impactradius.com".$nextPageUri;
				    echo $url.PHP_EOL;
				    $re = $this->oLinkFeed->GetHttpResult($url,$request);
				    $re = json_decode($re['content'], true);
				    if(!isset($re['Items']) || count($re['Items']) <= 0){
				        break;
				    }
				    $nextPageUri = $re['@nextpageuri'];
				    
				    foreach ($re['Items'] as $v)
				    {
				    
				        $product_path_file = $this->oLinkFeed->getProductImgFilePath($this->info["AffId"],"{$merchatInfo['IdInAff']}_".urlencode($v['Id']).".png", PRODUCTDIR);
				        if(!$this->oLinkFeed->fileCacheIsCached($product_path_file))
				        {
				            $file_content = $this->oLinkFeed->downloadImg($v['ImageUrl']);
				            if(!$file_content) //下载不了跳过。
				                continue;
				            $this->oLinkFeed->fileCachePut($product_path_file, $file_content);
				        }
				        if(!isset($v['Name']) || empty($v['Name']) || !isset($v['Id']))
				        {
				            continue;
				        }
				        
				        $link = array(
				            "AffId" => $this->info["AffId"],
				            "AffMerchantId" => $merchatInfo['IdInAff'],
				            "AffProductId" => trim($v['Id']),
				            "ProductName" => addslashes($v['Name']),
				            "ProductCurrency" => trim($v['Currency']),
				            "ProductPrice" => trim($v['CurrentPrice']),
				            "ProductOriginalPrice" =>trim($v['OriginalPrice']),
				            "ProductRetailPrice" =>'',
				            "ProductImage" => addslashes($v['ImageUrl']),
				            "ProductLocalImage" => addslashes($product_path_file),
				            "ProductUrl" => addslashes($v['Url']),
				            "ProductDestUrl" => '',
				            "ProductDesc" => addslashes($v['Description']),
				            "ProductStartDate" => '',
				            "ProductEndDate" => '',
				        );
				        
				        if (empty($link['ProductUrl']) || empty($link['ProductImage']))
				            continue;
				        	
				        $links[] = $link;
				        $arr_return['AffectedCount'] ++;
				        $crawlMerchantsActiveNum ++;
				    }
				    if (count($links))
				    {
				        $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateProductToDB($links);
				        $links = array();
				    }
				    $pages++; 
				    //大于最大数跳出
				    if($crawlMerchantsActiveNum >= $setMaxNum){
				        break;
				    }
				}while(1);
				
			}
			if($isAssignMerchant){
			    $productNumConfigAlert .= "AFFID:".$this->info["AffId"].",Program({$merchatInfo['MerchantName']}),Crawl Count($crawlMerchantsActiveNum),Total Count({$TotalCount}) \r\n";
			}
			$mcount ++;
		}
		echo 'merchant count:'.$mcount.PHP_EOL;
		$this->oLinkFeed->checkProductExists($this->info["AffId"], $check_date);
		echo $productNumConfigAlert.PHP_EOL;	
		echo 'END time'.date('Y-m-d H:i:s').PHP_EOL;
		return $arr_return;
	}

	private function get_linkcode_by_text_28($text)
	{
		if (preg_match('@ - (\w+)$@', $text, $g))
			return $g[1];
		return '';
	}

	function GetProgramByPage()
	{
		echo "\tGet Program by page start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		
		//step 1,login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "get",
			"postdata" => "",
			//"no_ssl_verifyhost" => 1
		);
		
		$strUrl = "https://www.apdperformance.com.au/secure/mediapartner/campaigns/mp-manage-active-ios-flow.ihtml?execution=e1s1";
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		
		$page = 1;
		$hasNextPage = true;
		while($hasNextPage){
			$start = ($page - 1) * 100;
			$strUrl = "https://www.apdperformance.com.au/secure/nositemesh/mediapartner/mpCampaignsJSON.ihtml?startIndex=$start&pageSize=100&tableId=myCampaignsTable&q=&page=$page";
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$result = $r["content"];
			$result = json_decode($result);
			//var_dump($result);exit;
			$total = intval($result->totalCount);
			if($total < ($page * 100)){
				$hasNextPage = false;
			}
			$page++;

			$data = $result->records;
			foreach($data as $v){
				//id
				$strMerID = intval($v->id->crv);
				if (empty($strMerID)) break;
				//name
				$strMerName = trim($this->oLinkFeed->ParseStringBy2Tag($v->name->dv, '">' , "</a>"));
				if ($strMerName === false) break;
				$CommissionExt = '';
				
				$re = json_encode($v);
				//var_dump($re);exit;
				$CommissionExt =trim($this->oLinkFeed->ParseStringBy2Tag($re, '<div class=\"textSpaced\">' , "<\/div>"));
				/* $term_url = trim($this->oLinkFeed->ParseStringBy2Tag($re, '<div class="textSemiBold"><a href="' , '">Terms</a>'));
				$term_url = "https://www.apdperformance.com.au" . $term_url;
				print_r($term_url);exit;
				$term_r = $this->oLinkFeed->GetHttpResult($term_url,$request);
				$term = $term_r['content']; */
				
				//contact
				$con_url = "https://www.apdperformance.com.au/secure/directory/campaign.ihtml?d=lightbox&n=footwear+etc&c=$strMerID";
				$con_r = $this->oLinkFeed->GetHttpResult($con_url,$request);
				$con_r = $con_r['content'];
				//print_r($con_r);exit;

                $CategoryExt = trim($this->oLinkFeed->ParseStringBy2Tag($con_r, array('id="categoryLink"','>') , '<'));
				$con_name = trim($this->oLinkFeed->ParseStringBy2Tag($con_r, 'id="contactName">' , "</div>"));
				$con_detail = trim($this->oLinkFeed->ParseStringBy2Tag($con_r, array('Send email' , '<div class="truncate dirContactDetails">') , "</div>"));
				$Contacts = $con_name . ':' . $con_detail;
				
				
				$arr_prgm[$strMerID] = array(
					"Name" => addslashes(html_entity_decode($strMerName)),
					"AffId" => $this->info["AffId"],
					"IdInAff" => $strMerID,
					"CommissionExt" => addslashes($CommissionExt),
					"Contacts" => addslashes($Contacts),
					"LastUpdateTime" => date("Y-m-d H:i:s"),
                    'CategoryExt' => addslashes($CategoryExt),
				);
				$program_num++;
				//var_dump($arr_prgm);exit;
				if(count($arr_prgm) >= 100){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
			}
		}
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
			unset($arr_prgm);
		}
		
		//https://www.dgmperformance.com.au/secure/account/emaillist/myCampaignContacts.csv
		/*$str_header = "First Name,Last Name,Email,Campaign,Campaign Id";
		$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"myCampaignContacts.csv","cache_contact");
		if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
		{
			$strUrl = "https://www.dgmperformance.com.au/secure/account/emaillist/myCampaignContacts.csv";
			$request["postdata"] =  "";	
			
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$result = $r["content"];
			print "Get Contacts <br>\n";
			$this->oLinkFeed->fileCachePut($cache_file,$result);
			if(stripos($result,$str_header) === false) mydie("die: wrong csv file: $cache_file");
			
		}
		else 
		{
			echo "using previous file $cache_file <br>\n";
		}
		//Open CSV File
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$fhandle = fopen($cache_file, 'r');
		
		$arr_prgm = array();
		while($line = fgetcsv ($fhandle, 5000))
		{
			foreach($line as $k => $v) $line[$k] = trim($v);
			
			if ($line[0] == '' || $line[0] == 'First Name') continue;
			if(!isset($line[4])) continue;
			if(!isset($line[2])) continue;
			$arr_prgm[$line[4]] = array(					
				"AffId" => $this->info["AffId"],				
				"IdInAff" => $line[4],
				"Contacts" => $line[0]." ".$line[1]. ", Email:" . $line[2],				
			);			
			if(count($arr_prgm) >= 100){
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);				
				$arr_prgm = array();
			}
		}
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);			
			unset($arr_prgm);
		}*/
		echo "\tGet Program by page end\r\n";
		
		if($program_num < 10){
			mydie("die: program count < 10, please check program.\n");
		}
		
		echo "\tUpdate ({$program_num}) program.\r\n";
		echo "\tSet program country int.\r\n";
		$objProgram->setCountryInt($this->info["AffId"]);
	}
	
	function GetProgramDetailByPage()
	{
		echo "\tGet Program detail by page start\r\n";
	}
	
	function GetProgramByApi()
	{
		echo "\tGet Program by api start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		//login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		$request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "");
		
		$AccountSid = urlencode(API_SID_28);
		$AccountToken = urlencode(API_TOKEN_28);
		
		$hasNextPage = true;			
		$perPage = 100;				
		$page = 1;		
		
		while($hasNextPage){
			$strUrl = "https://{$AccountSid}:{$AccountToken}@api.impactradius.com/2010-09-01/Mediapartners/{$AccountSid}/Campaigns.json?PageSize={$perPage}&Page=$page";
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$result = $r["content"];
				
			$result = json_decode($result);
			//var_dump($result);exit;
			
			$page++;
			
			$numpages = "@numpages";
			$numReturned = intval($result->$numpages);
			if(!$numReturned) break;					
			if($page > $numReturned){
				$hasNextPage = false;
			}
						
			$mer_list = $result->Campaigns;
	
			//var_dump($mer_list);exit;
			foreach($mer_list as $v)
			{				
				$strMerID = intval($v->CampaignId);
				if(!$strMerID) continue;
				
				$strMerName = $v->CampaignName;				
				$Homepage = $v->CampaignUrl;
				
				$StatusInAffRemark = $v->InsertionOrderStatus;
				if($StatusInAffRemark == "Expired"){
					$Partnership = "Expired";
				}elseif($StatusInAffRemark == "Active"){
					$Partnership = "Active";
				}else{
					$Partnership = "NoPartnership";
				}
				
				$LogoUrl = "https://www.apdperformance.com.au".$v->CampaignLogoUri;
				$prgm_url = "https://member.impactradius.com/secure/directory/campaign.ihtml?d=lightbox&n=footwear+etc&c=$strMerID";
				$TrackingLink = $v->TrackingLink;
				$AllowsDeeplinking = $v->AllowsDeeplinking;
				$termUrl = "https://www.apdperformance.com.au/secure/mediapartner/campaigns/mp-view-io-by-campaign-flow.ihtml?c=$strMerID";
				$term = $this->oLinkFeed->GetHttpResult($termUrl,$request)['content'];
				$search = array ("/<script[^>]*?>.*?<\/script>/si", // 去掉 javascript
						"/<style[^>]*?>.*?<\/style>/si", // 去掉 css
						"/<[\/!]*?[^<>]*?>/si", // 去掉 HTML 标记
						"/<!--[\/!]*?[^<>]*?>/si", // 去掉 注释标记
						"/([\r\n])[\s]+/", // 去掉空白字
				);
				$replace = array ("",
						"",
						"",
						"",
						",\t",
				);
				$TermAndCondition = preg_replace($search, $replace, $term);
				//print_r($TermAndCondition);exit;
				if(stripos($AllowsDeeplinking, "true") !== false){
					$SupportDeepurl = 'YES';
				}else{
					$SupportDeepurl = 'NO';
				}
				if(empty($v->ShippingRegions)){
					$TargetCountryExt = "";
				}else{
					$TargetCountryExt = implode(',', $v->ShippingRegions);
				}
				$desc = $v->CampaignDescription;
				
				$StatusInAff = 'Active';
				if(stripos($strMerName, 'paused') !== false){
					$StatusInAff = 'Offline';
				}
				
				$arr_prgm[$strMerID] = array(
					"Name" => addslashes(html_entity_decode($strMerName)),
					"AffId" => $this->info["AffId"],					
					"IdInAff" => $strMerID,
					"StatusInAffRemark" => $StatusInAffRemark,
					"StatusInAff" => $StatusInAff,						//'Active','TempOffline','Offline'
					"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
					"TargetCountryExt" => $TargetCountryExt,
					"Description" => addslashes($desc),
					"Homepage" => addslashes($Homepage),
					"LastUpdateTime" => date("Y-m-d H:i:s"),
					"DetailPage" => addslashes($prgm_url),
					"SupportDeepUrl" => $SupportDeepurl,
					"AffDefaultUrl" => addslashes($TrackingLink),
					"LogoUrl" => addslashes($LogoUrl),
					"TermAndCondition" => addslashes($TermAndCondition),
				);
				$program_num++;
				
				if(count($arr_prgm) >= 100){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
			}			
		}
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
			$arr_prgm = array();
		}
		
		echo "\tGet Program by api end\r\n";
		
		if($program_num < 10){
			mydie("die: program count < 10, please check program.\n");
		}
		
		echo "\tUpdate ({$program_num}) program.\r\n";
		echo "\tSet program country int.\r\n";
		$objProgram->setCountryInt($this->info["AffId"]);
	}
	
	function GetProgramFromAff()
	{	
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
		
		$this->GetProgramByApi();
		$this->GetProgramByPage();
		$this->checkProgramOffline($this->info["AffId"], $check_date);
		

		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
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

