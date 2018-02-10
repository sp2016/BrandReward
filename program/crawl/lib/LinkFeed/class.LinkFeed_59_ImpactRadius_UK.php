<?php

require_once 'text_parse_helper.php';
define('API_SID_59', 'IRBThyEnkVou11290AjwqWsTeaWK5muKL2');
define('API_TOKEN_59', 'zndi4iidwyqqMwqumMKghG2niMm4UkAg');
define('AFFID_INAFF_59', '11290');

class LinkFeed_59_ImpactRadius_UK
{	
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
        $this->getStatus = false;

        $this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
	}
	
	function getCouponFeed()
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		return $arr_return;
	}
	
	function GetAllLinksFromAffByMerID($merinfo)
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", "no_ssl_verifyhost" => true);

		// coupon by api
		$AccountSid = API_SID_59;
		$AccountToken = API_TOKEN_59;
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
							"DataSource" => "78",
					);
					if ($v['PromoType'] == 'FREESHIPPING')
						$link['LinkPromoType'] = 'free shipping';
					if (!empty($v['PromoCode']))
					{
						$link['LinkCode'] = $v['PromoCode'];
						$link['LinkPromoType'] = 'coupon';
					}

					$code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc']);
					if (!empty($code))
					{
						$link['LinkPromoType'] = 'COUPON';
						$link['LinkCode'] = $code;
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

		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		$adTypes = array('COUPON', 'TEXT_LINK', 'BANNER');

		foreach ($adTypes as $adType)
		{

			$links = array();
			//get cookies
			$url = 'https://member.impactradius.com/secure/mediapartner/ads/searchAdsDirectoryMP.ihtml';
			$r = $this->oLinkFeed->GetHttpResult($url,$request);

			$startIndex = 0;
			$pageSize =150;

			//get total number
			$url = sprintf('https://member.impactradius.com/secure/nositemesh/campaigns/searchAdsDirectoryMPJSON.ihtml?dealType=ALL&adSubType=ALL&statsPeriod=&season=&deal=ALL&language=&mobileReady=&deepLinking=&searchString=&campaign=%s&adType=%s&dealType=ALL&tableId=207&page=1&startIndex=%s&pageSize=%s', $merinfo['IdInAff'],$adType,$startIndex,$pageSize);
			$r = $this->oLinkFeed->GetHttpResult($url,$request);
			$r = json_decode($r['content'],true);
			$count = isset($r['totalCount']) ? $r['totalCount']:0;
			if(!$count) continue;

			//get content pages
			while($startIndex <= $count){
				$url = sprintf('https://member.impactradius.com/secure/nositemesh/campaigns/searchAdsDirectoryMPJSON.ihtml?dealType=ALL&adSubType=ALL&statsPeriod=&season=&deal=ALL&language=&mobileReady=&deepLinking=&searchString=&campaign=%s&adType=%s&dealType=ALL&tableId=207&page=1&startIndex=%s&pageSize=%s', $merinfo['IdInAff'],$adType,$startIndex,$pageSize);
				$r = $this->oLinkFeed->GetHttpResult($url,$request);
				$r = json_decode($r['content'],true);
				foreach ($r['records'] as $row){


					$detail_url = sprintf('https://member.impactradius.com/secure/directory/mediapartner-gethtml-flow.ihtml?adType=%s&adId=%s&campaignId=%s&d=lightbox',$adType,$row['bulkActionCol']['dv'],$merinfo['IdInAff']);
					$r = $this->oLinkFeed->GetHttpResult($detail_url,$request);
					$r = $r['content'];
//					if($row['bulkActionCol']['dv'] == 294260){
//						print_r($r);
//						echo $detail_url;die;
//					}
					$link = array(
						"AffId" => $this->info["AffId"],
						"AffMerchantId" => $merinfo['IdInAff'],
						"AffLinkId" => $row['bulkActionCol']['dv'],
						"LinkName" => '',
						"LinkDesc" => '',
						"LinkStartDate" => '0000-00-00',
						"LinkEndDate" => '0000-00-00',
						"LinkPromoType" => 'link',
						"LinkHtmlCode" => '',
						"LinkOriginalUrl" => '',
						"LinkImageUrl" => '',
						"LinkAffUrl" => '',
						"DataSource" => "78",
					);

					$code_url = sprintf('https://member.impactradius.com/nositemesh/directory/mediapartner/listads/genhtml.ihtml?adid=%s&cid=%s&mpid=%s',$link['AffLinkId'],$link['AffMerchantId'],AFFID_INAFF_58);
					$code_detail = $this->oLinkFeed->GetHttpResult($code_url,$request);
					$code_detail = $code_detail['content'];
					preg_match('/class="adNameTitle">(.*?)<\/span>/',$row['name']['dv'],$linkName);
					if(isset($linkName[1]) && $linkName[1])
						$link['LinkName'] = $linkName[1];

					if(preg_match('/Description[\s\S]*?uitkColapsibleText[\S\s]*?>([\s\S]*?)<\/span>/',$r,$desc)){
						$link['LinkDesc'] = trim($desc[1]);
					}


					if (preg_match('@<textarea.*?>(.*?)</textarea>@ms', $code_detail, $g))
					{
						$link['LinkHtmlCode'] = trim(html_entity_decode($g[1]));
						if (preg_match('@a href="(.*?)"@', $link['LinkHtmlCode'], $g))
							$link['LinkAffUrl'] = "http:".$g[1];
					}
					if ($adType == 'BANNER')
					{
						if (preg_match('@img src="(.*?)"@', $link['LinkHtmlCode'], $g))
							$link['LinkImageUrl'] = "http:".$g[1];
					}
					if ($adType == 'COUPON')
					{
						$link['LinkPromoType'] = 'COUPON';
						$link['LinkCode'] = $this->get_linkcode_by_text_58($link['LinkName']);
						if (empty($link['LinkCode']))
							$link['LinkCode'] = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc']);

					}
					if(preg_match('/Code:<\/span>(.*?)<\/div>/',$r,$coupon_code)){
						$link['LinkCode'] = trim($coupon_code[1]);
						$link['LinkPromoType'] = 'COUPON';
					}
					if ($adType == 'TEXT_LINK')
					{
						$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
						$code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc']);
						if (!empty($code))
						{
							$link['LinkPromoType'] = 'COUPON';
							$link['LinkCode'] = $code;
						}
					}

					if(preg_match('/Dates Active:<\/span>(.*?)-(.*?),(.*?)<\/div>/',$r,$datearea)) {
						$tmp_year = (date('Y-m-d',strtotime($datearea[1])) > date('Y-m-d',strtotime($datearea[2])) ? $datearea[3]-1 : $datearea[3]);
						$link['LinkStartDate'] = date("Y-m-d H:i:s",strtotime($datearea[1]." ".$tmp_year));
						$link['LinkEndDate'] = date("Y-m-d 23:59:59",strtotime($datearea[2]." ".$datearea[3]));
					}

//					print_r($link);
					if (empty($link['AffLinkId']) || empty($link['LinkName']))
						continue;
					$links[] = $link;
					$arr_return['AffectedCount'] ++;


				}
				echo sprintf("get %s by page...%s link(s) found.\n", $adType, count($links));
				if (count($links) > 0)
					$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);

				$startIndex += $pageSize;
			};

		}

		return $arr_return;
	}

	private function get_linkcode_by_text_59($text)
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
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info,1,false);
		
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "get",
			"postdata" => "",
			"no_ssl_verifyhost" => 1
		);	

		$time = time()."123";
		
		$strUrl = "https://member.impactradius.com/secure/mediapartner/campaigns/mp-manage-active-ios-flow.ihtml?execution=e1s1";
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		
		$page = 1;
		$hasNextPage = true;
		while($hasNextPage){
			$start = ($page - 1) * 100;			
			$strUrl = "https://member.impactradius.com/secure/nositemesh/mediapartner/mpCampaignsJSON.ihtml?_dc=$time&tableId=myCampaignsTable&page=$page&startIndex=$start&pageSize=100";
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$result = $r["content"];
			
			$result = json_decode($result);			
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
												
				$StatusInAff = 'Active';
				if(stripos($strMerName,"closed") !== false){
					$StatusInAff = 'Offline';
					//$strMerName = trim(str_ireplace("(closed)","",$strMerName));
				}
				if(stripos($strMerName,"paused") !== false){
					$StatusInAff = 'Offline';
					//$strMerName = trim(str_ireplace("(paused)","",$strMerName));
				}
				if(stripos($strMerName,"ended") !== false){
					$StatusInAff = 'Offline';
					//$strMerName = trim(str_ireplace("(ended)","",$strMerName));
				}
				
				$desc = trim($this->oLinkFeed->ParseStringBy2Tag($v->name->dv, 'uitkHiddenInGridView\">' , "</p>"));				
				//$CommissionExt = trim($v->{'1696246188'}->dv);
				//activeDate
				$CreateDate = trim($v->launchDate->dv);
				if($CreateDate){
					$CreateDate = date("Y-m-d H:i:s", strtotime(str_replace(",", "", $CreateDate)));
				}
				
				$RankInAff = intval($v->irrating->crv);
						
				$prgm_url = "https://member.impactradius.com/secure/directory/campaign.ihtml?d=lightbox&n=footwear+etc&c=$strMerID";

				$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
				$prgm_detail = $prgm_arr["content"];								
				
				$detail_start = 0;			
				$StatusInAffRemark = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array($strMerName, "(".$strMerID.")", '-'), '</h2>', $detail_start));
				if($StatusInAffRemark == "Joined"){
					$Partnership = "Active";
				}
				preg_match('/id="serviceAreas".*?>(.*?)<\/div>/',$prgm_detail,$TargetCountryExt);
				$TargetCountryExt = isset($TargetCountryExt[1])?$TargetCountryExt[1]:"";
				$CategoryExt = strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('irRated','<li>'), '</li>', $detail_start));
				$JoinDate = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, 'Active Since:', '(', $detail_start));
				if($JoinDate){
					$JoinDate = date("Y-m-d H:i:s", strtotime(str_replace(",", "", $JoinDate)));
				}
				
				$desc = strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<div class="dirMainSectionBorderless">', '<p>'), '</p>', $detail_start));	
				$CommissionExt = "";	
				$CommissionExt = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Payment Details'), '</div>', $detail_start)));
				$CookieTime = intval($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<p class="uitkBoxHalfLast">', '<b>'), 'days</b>Click Referral Period', $detail_start));
				
				//$Contacts = strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Company Contacts','<li class="dirContactFname">'), '</li>', $detail_start));
				//$Contacts .= ", Email:". trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, '<li>', '</li>', $detail_start));
				$Homepage = "";
				//$Homepage = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Company Website','<a href="'), '"', $detail_start));
								
				preg_match("/<a href=(\"|')([^\"']*)\\1.*?>Company Home Page/i", $prgm_detail, $m);
				if(count($m) && strlen($m[2])){
					$Homepage = trim($m[2]);
				}
				
				
				$arr_prgm[$strMerID] = array(
					"Name" => addslashes(html_entity_decode($strMerName)),
					"AffId" => $this->info["AffId"],				
					"CategoryExt" => addslashes($CategoryExt),
					"CreateDate" => $CreateDate,
					//"Contacts" => $Contacts,
					"RankInAff" => $RankInAff,							
					"IdInAff" => $strMerID,
					"JoinDate" => $JoinDate,
					"StatusInAffRemark" => addslashes($StatusInAffRemark),
					"StatusInAff" => 'Active',						//'Active','TempOffline','Offline'
					"Partnership" => 'Active',						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'				
					"Description" => addslashes($desc),
					//"CommissionExt" => addslashes($CommissionExt),
					"CookieTime" => $CookieTime,
					//"Homepage" => $Homepage,				
					"LastUpdateTime" => date("Y-m-d H:i:s"),
					"DetailPage" => $prgm_url,
					"TargetCountryExt" => addslashes($TargetCountryExt),
				);
				
				if(!empty($CommissionExt)){
					$arr_prgm[$strMerID]["CommissionExt"] = $CommissionExt;
				}
				if(!empty($Homepage)){
					$arr_prgm[$strMerID]["Homepage"] = $Homepage;
				}
				
				
				$program_num++;
				//print_r($arr_prgm);exit;
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


        if(!$this->getStatus) {
            //通过csv仅获取contacts
            //https://member.impactradius.co.uk/secure/account/emaillist/myCampaignContacts.csv
            $str_header = "First Name,Last Name,Email,Campaign,Campaign Id";
            $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"], "myCampaignContacts.csv", "cache_contact");
            if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
                $strUrl = "https://member.impactradius.com/secure/account/emaillist/myCampaignContacts.csv";
                $request["postdata"] = "";

                $r = $this->oLinkFeed->GetHttpResult($strUrl, $request);
                $result = $r["content"];
                print "Get Contacts <br>\n";
                if (stripos($result, $str_header) === false) mydie("die: wrong csv file: $cache_file");
                $this->oLinkFeed->fileCachePut($cache_file, $result);

                //Open CSV File
                $objProgram = new ProgramDb();
                $arr_prgm = array();
                $fhandle = fopen($cache_file, 'r');

                $arr_prgm = array();
                while ($line = fgetcsv($fhandle, 5000)) {
                    foreach ($line as $k => $v) $line[$k] = trim($v);

                    if ($line[0] == '' || $line[0] == 'First Name') continue;
                    if (!isset($line[4])) continue;
                    if (!isset($line[2])) continue;
                    $arr_prgm[$line[4]] = array(
                        "AffId" => $this->info["AffId"],
                        "IdInAff" => $line[4],
                        "Contacts" => $line[0] . " " . $line[1] . ", Email:" . $line[2],
                        //"LastUpdateTime" => date("Y-m-d H:i:s"),
                    );

                    if (count($arr_prgm) >= 100) {
                        $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
                        //$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
                        $arr_prgm = array();
                    }
                }
                if (count($arr_prgm)) {
                    $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
                    //$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
                    $arr_prgm = array();
                }
            } else {
                echo "using previous file $cache_file <br>\n";
            }
        }
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
		
		$request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "");
		
		$AccountSid = "IRBThyEnkVou11290AjwqWsTeaWK5muKL2";
		$AccountToken = "zndi4iidwyqqMwqumMKghG2niMm4UkAg";
		
		$hasNextPage = true;			
		$perPage = 100;				
		$page = 1;		
		
		while($hasNextPage){
			$strUrl = "https://{$AccountSid}:{$AccountToken}@api.impactradius.com/2010-09-01/Mediapartners/{$AccountSid}/Campaigns.json?PageSize={$perPage}&Page=$page";
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$result = $r["content"];
				
			$result = json_decode($result);
			//print_r($result);exit;
			
			$page++;
			
			$numpages = "@numpages";
			$numReturned = intval($result->$numpages);
			if(!$numReturned) break;					
			if($page > $numReturned){
				$hasNextPage = false;
			}
						
			$mer_list = $result->Campaign;
			//print_r($mer_list);exit;
			foreach($mer_list as $v)
			{				
				$strMerID = intval($v->CampaignId);
				if(!$strMerID) continue;
				
				$strMerName = $v->CampaignName;				
				$Homepage = $v->AdvertiserUrl;
				
				$StatusInAffRemark = $v->InsertionOrderStatus;
				if($StatusInAffRemark == "Expired"){
					$Partnership = "Expired";
				}elseif($StatusInAffRemark == "Active"){
					$Partnership = "Active";
				}else{
					$Partnership = "NoPartnership";
				}
				
				$prgm_url = "https://member.impactradius.com/secure/directory/campaign.ihtml?d=lightbox&n=footwear+etc&c=$strMerID";
				$TrackingLink = $v->TrackingLink;
				$AllowsDeeplinking = $v->AllowsDeeplinking;
				
				if(stripos($AllowsDeeplinking, "true") !== false){
					$SupportDeepurl = 'YES';
				}else{
					$SupportDeepurl = 'NO';
				}
				
				$arr_prgm[$strMerID] = array(
					//"Name" => addslashes(html_entity_decode($strMerName)),
					"AffId" => $this->info["AffId"],					
					"IdInAff" => $strMerID,
					//"StatusInAffRemark" => $StatusInAffRemark,
					//"StatusInAff" => 'Active',						//'Active','TempOffline','Offline'
					//"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
					//"Homepage" => addslashes($Homepage),
					"LastUpdateTime" => date("Y-m-d H:i:s"),
					//"DetailPage" => $prgm_url,
					"SupportDeepUrl" => $SupportDeepurl,
					"AffDefaultUrl" => addslashes($TrackingLink)
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

    function GetStatus(){
        $this->getStatus = true;
        $this->GetProgramFromAff();
    }

	function GetProgramFromAff()
	{	
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
		
		$this->GetProgramByPage();
		$this->checkProgramOffline($this->info["AffId"], $check_date);
        if(!$this->getStatus) {
            $this->GetProgramByApi();
        }

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
?>
