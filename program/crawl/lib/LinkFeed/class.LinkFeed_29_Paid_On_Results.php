<?php

require_once 'text_parse_helper.php';

if(SID == 'bdg02'){
    define('API_KEY_29', 'EPPPSWWEKKPUHKYLQQWZ');
    define('AFFID_INAFF_29', 47119);
}
else{
    //define('API_KEY_29', 'HTHJVBUTZOPAKSOROYXS');
    //define('AFFID_INAFF_29', 19933);
	//Jimmy
	define('API_KEY_29', 'HETHJOHUGHTNEUQSYLPN');
	define('AFFID_INAFF_29', 47133);
}
class LinkFeed_29_Paid_On_Results
{
	var $info = array(
		"ID" => "29",
		"Name" => "Paid On Results",
		"IsActive" => "YES",
		"ClassName" => "LinkFeed_29_Paid_On_Results",
		"LastCheckDate" => "1970-01-01",
	);

	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
	}

	function GetAllLinksFromAffByMerID($merinfo)
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0,);
		return $arr_return;
	}

	function getCouponFeed()
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "");
		$check_date = date('Y-m-d H:i:s');
		//coupon
		$links = array();
		$url = sprintf('http://vouchers.paidonresults.net/api?affiliate_id=%s&securitycode=fomEmuub&export=csv&fields=VoucherID,AffiliateURL,VoucherCode,VoucherDescription,StartDate,ExpiryDate,MerchantID&inc_upcoming=1&inc_all_merchants=1&date=YYYY-MM-DD&seperator=tab&inc_header=1', AFFID_INAFF_29);
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$count = 0;
		if (!empty($r) && is_array($r) && $r['code'] == 200 && !empty($r['content']))
		{
			$content = $r['content'];
			$data = csv_string_to_array($content, "\t", "\n");
			$links = array();
			foreach ((array)$data as $v)
			{
				$link = array(
						"AffId" => $this->info["AffId"],
						"AffMerchantId" => $v['Merchant ID'],
						"AffLinkId" => $v['Voucher ID'],
						"LinkName" => $v['Voucher Description'],
						"LinkDesc" => '',
						"LinkCode" => $v['Voucher Code'],
						"LinkStartDate" => parse_time_str($v['Start Date'], null, false),
						"LinkEndDate" => parse_time_str($v['Expiry Date'], null, true),
						"LinkPromoType" => 'COUPON',
						"LinkHtmlCode" => '',
						"LinkOriginalUrl" => '',
						"LinkImageUrl" => '',
						"LinkAffUrl" => $v['Affiliate URL'],
						"DataSource" => "79",
				        "Type" => 'promotion',
				);
				if (empty($link['AffLinkId']) || empty($link['LinkName']) || empty($link['LinkAffUrl']) || empty($link['AffMerchantId']))
					continue;
				if (empty($link['LinkHtmlCode']))
					$link['LinkHtmlCode'] = create_link_htmlcode($link);
				if(!$link['LinkCode']){
				    $link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
				    $code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc'] . '|' . $link['LinkHtmlCode']);
				    if (!empty($code)) {
				        $link['LinkCode'] = $code;
				        $link['LinkPromoType'] = 'coupon';
				    }
				}
				
				$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
				$links[] = $link;
				$count ++;
				$arr_return["AffectedCount"] ++;
			}
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
			$links = array();
		}
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'promotion');
		
		echo sprintf("get coupon from feed...%s link(s) found.\n", $count);

		// banner & text links
		$url = sprintf('http://affiliate.paidonresults.com/api/creative-feed?apikey=%s&AffiliateID=%s&&Format=CSV&FieldSeparator=tab&Fields=UniqueID,MerchantID,MerchantName,CreativeName,CreativeDescription,CreativeType,CreativeSize,DateAdded,ExpiryDate,AffiliateURL,CreativeURL,HTMLCode,AltText&BannerCreative=YES&TextCreative=YES', API_KEY_29, AFFID_INAFF_29);
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$count = 0;
		if (!empty($r) && is_array($r) && $r['code'] == 200 && !empty($r['content']))
		{
			$content = $r['content'];
			$data = csv_string_to_array($content, "\t", "\n");
			foreach ((array)$data as $v)
			{
				$link = array(
						"AffId" => $this->info["AffId"],
						"AffMerchantId" => $v['MerchantID'],
						"AffLinkId" => $v['UniqueID'],
						"LinkName" => $v['CreativeName'],
						"LinkDesc" => $v['CreativeDescription'],
						"LinkStartDate" => parse_time_str($v['DateAdded'], 'd/m/Y', false),
						"LinkEndDate" =>  parse_time_str($v['ExpiryDate'], 'd/m/Y', true),
						"LinkPromoType" => 'DEAL',
						"LinkHtmlCode" => $v['HTMLCode'],
						"LinkOriginalUrl" => '',
						"LinkImageUrl" => '',
						"LinkAffUrl" => $v['AffiliateURL'],
						"DataSource" => "79",
				        "Type" =>'link', 
				);
				if (empty($link['AffLinkId']) || empty($link['LinkName']) || empty($link['LinkAffUrl']) || empty($link['AffMerchantId']))
					continue;
				
				$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
				$code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc'] . '|' . $link['LinkHtmlCode']);
				if (!empty($code)) {
				    $link['LinkCode'] = $code;
				    $link['LinkPromoType'] = 'coupon';
				}
				
				$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
				$links[] = $link;
				$count ++;
				$arr_return["AffectedCount"] ++;
				if (($arr_return['AffectedCount'] % 100) == 0)
				{
					$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
					$links = array();
				}
			}
		}
		echo sprintf("get banner & text links from feed...%s link(s) found.\n", $count);
		if (count($links) > 0)
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link');
		return $arr_return;
	}

	function getMessage()
	{
		$messages = array();
		//login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "",);
		//$session = $this->LoginIntoAffService();
		//$url = sprintf('http://affiliate.paidonresults.com/cgi-bin/msg-inbox.pl?session=%s', $session);
		$url = 'http://affiliate.paidonresults.com/cgi-bin/msg-inbox.pl';
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r['content'];
	
		preg_match_all('@<tr>(.*?)</tr>@ms', $content, $chapters);
		if (empty($chapters) || !is_array($chapters) || empty($chapters[1]) || !is_array($chapters[1]))
			return 'no message found.';
		foreach ((array)$chapters[1] as $chapter)
		{
			preg_match_all('@<td.*?>(.*?)</td>@ms', $chapter, $g);
			if (empty($g) || empty($g[1]) || !is_array($g[1]) || count($g[1]) < 3)
				continue;
			if (!preg_match('@<a href="(.*?msg_id=(\d+\.\d+).*?)".*?>(.*?)</a>@', $g[1][2], $a))
				continue;
			$data = array(
					'affid' => $this->info["AffId"],
					'messageid' => $a[2],
					'sender' => trim(html_entity_decode(strip_tags($g[1][1]))),
					'title' => trim(html_entity_decode($a[3])),
					'content' => '',
					'created' => parse_time_str(trim($g[1][0]), 'd/m/Y', false),
					'content_url' => 'http://affiliate.paidonresults.com/cgi-bin/' . $a[1],
			);
			if (empty($data['messageid']))
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
		if (preg_match('@<tr><td align="left" class="norm">(.*?)</td></tr></table></div>@ms', $content, $g))
			$data['content'] = str_force_utf8(trim(html_entity_decode($g[1])));
		return $data;
	}

	function GetProgramFromAff()
	{   print_r($this->info);exit;
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";

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

		//login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		
		//get paymentDays
		$page_url = "https://affiliate.paidonresults.com/cgi-bin/merchant-dir.pl";
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => "post",
				"postdata" => "type=0",
		);
		$re = $this->oLinkFeed->GetHttpResult($page_url, $request);
		//print_r($re);exit;
		$re = $re['content'];
		$re = trim($this->oLinkFeed->ParseStringBy2Tag($re, '<img src="/images/inv-5x5px.gif" width="1" height="3"></td></tr>', '<Tr><td bgcolor="#077dbb"><img src="/images/inv-5x5px.gif" width="3" height="2"></td>'));
		$re = explode('<tr bgcolor', $re);
		unset($re[0]);
		//print_r($re);exit;
		$arr_prgmByPage = array();
		foreach ($re as $v)
		{
			$LogoUrl = "https://affiliate.paidonresults.com/logos/".trim($this->oLinkFeed->ParseStringBy2Tag($v, 'img src="/logos/', '"'));
			$strMerID = trim($this->oLinkFeed->ParseStringBy2Tag($v, 'img src="/logos/', '.'));
			if (!$strMerID)
				continue;
			$PaymentDays = trim($this->oLinkFeed->ParseStringBy2Tag($v, 'text-decoration:none;">', '</a>'));
			if ($PaymentDays == 'New<br>Merchant' || $PaymentDays == 'Average<br>Coming Soon'){
				$PaymentDays = 0;
			}elseif ($PaymentDays == 'Less than<br>24 Hours'){
				$PaymentDays = 1;
			}else{
				$PaymentDays = str_replace(' Days', '', $PaymentDays);
				$PaymentDays = str_replace(' Day', '', $PaymentDays);
				$PaymentDays = intval($PaymentDays);
			}
			$arr_prgmByPage[$strMerID] = array(
					"PaymentDays" => $PaymentDays,
					"LogoUrl" => $LogoUrl,
			);
		}
		//print_r($arr_prgmByPage);exit;
		$apiurl = sprintf("http://affiliate.paidonresults.com/api/merchant-directory?apikey=%s&Format=XML&AffiliateID=%s&MerchantCategories=ALL&Fields=MerchantID,MerchantCaption,MerchantCategory,AccountManager,MerchantName,MerchantStatus,DateLaunched,AffiliateStatus,ApprovalRate,DeepLinks,AccountManagerEmail,MerchantURL,CookieLength,AffiliateURL,SampleCommissionRates,AverageCommission&JoinedMerchants=YES&MerchantsNotJoined=YES", API_KEY_29, AFFID_INAFF_29);
		$request = array("method" => "get");
		$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"merchant_xml_".date("YmdH").".dat", "cache_feed");
		if(!$this->oLinkFeed->fileCacheIsCached($cache_file)){
			$r = $this->oLinkFeed->GetHttpResult($apiurl, $request);
			$result = $r["content"];
			$this->oLinkFeed->fileCachePut($cache_file,$result);
		}
		$xml = new DOMDocument();
		$xml->load($cache_file);
		
		
		//parse XML
		$advertiser_list = $xml->getElementsByTagName("Merchants");
		foreach($advertiser_list as $advertiser)
		{
			$advertiser_info = array();
			
			$childnodes = $advertiser->getElementsByTagName("*");
			foreach($childnodes as $node){
				$advertiser_info[$node->nodeName] = trim($node->nodeValue);
			}
			$strMerID = $advertiser_info['MerchantID'];
			$desc = $advertiser_info['MerchantCaption'];
			$CategoryExt = $advertiser_info['MerchantCategory'];
			$JoinDate = $advertiser_info['DateLaunched'];
			$JoinDate = date("Y-m-d H:i:s", strtotime($JoinDate));
			$Name = $advertiser_info['MerchantName'];
			$CookieTime = $advertiser_info['CookieLength'];
			$Homepage = $advertiser_info['MerchantURL'];
			$prgm_url = $advertiser_info['AffiliateURL'];
			$CommissionExt = empty($advertiser_info['SampleCommissionRates']) ? $advertiser_info['AverageCommission'] : $advertiser_info['SampleCommissionRates'];
			$Contacts = $advertiser_info['AccountManager']." Email:".$advertiser_info['AccountManagerEmail'];
			$LogoUrl = isset($arr_prgmByPage[$strMerID]['LogoUrl']) ? $arr_prgmByPage[$strMerID]['LogoUrl'] : '';
			$PaymentDays = isset($arr_prgmByPage[$strMerID]['PaymentDays']) ? $arr_prgmByPage[$strMerID]['PaymentDays'] : '';
			
			$AffiliateStatus = $advertiser_info['AffiliateStatus'];
			if($AffiliateStatus == "JOINED"){
				$Partnership = "Active";
			}else{
				$Partnership = "NoPartnership";
			}
			$StatusInAffRemark = $advertiser_info['MerchantStatus'];
			if($StatusInAffRemark == "LIVE"){
				$StatusInAff = "Active";
			}else{
				$StatusInAff = "Offline";
			}

			$SupportDeepurl = $advertiser_info['DeepLinks'];
			if(stripos($SupportDeepurl, "yes") !== false){
				$SupportDeepurl = "YES";
			}else{
				$SupportDeepurl = "NO";
			}
			$arr_prgm[$strMerID] = array(
				"AffId" => $this->info["AffId"],
				"IdInAff" => $strMerID,	
				"Name" => addslashes($Name),
				"Homepage" => addslashes($Homepage),
				"Description" => addslashes($desc),
				"CategoryExt" => addslashes($CategoryExt),
				"CookieTime" => addslashes($CookieTime),
				"CommissionExt" => addslashes($CommissionExt),
				"Contacts" => addslashes($Contacts),
				"JoinDate" => $JoinDate,
				"StatusInAffRemark" => addslashes($StatusInAffRemark),
				"StatusInAff" => $StatusInAff,						//'Active','TempOffline','Offline'
				"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'						
				"LastUpdateTime" => date("Y-m-d H:i:s"),
				"DetailPage" => addslashes($prgm_url),
				"SupportDeepUrl" => $SupportDeepurl,
				"TargetCountryExt" => '',
				"LogoUrl" => addslashes($LogoUrl),
				"PaymentDays" => $PaymentDays,
				);
			
			$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"detail_".date("Ym")."_{$strMerID}.dat", "program", true);
			if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
			{
				$prgm_url = "http://affiliate.paidonresults.com/cgi-bin/view-merchant.pl?site_id={$strMerID}";
				$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "",);
				$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
				
				if($prgm_arr['code'] == 200){
					$results = $prgm_arr['content'];
					$this->oLinkFeed->fileCachePut($cache_file, $results);
					//print_r($results);exit;
					$cache_file = file_get_contents($cache_file);
					//print_r($cache_file);exit;
					$AllowNonaffPromo = 'UNKNOWN';
					$AllowNonaffCoupon = 'UNKNOWN';
					$TermAndCondition = '';
					if($cache_file){
						if(stripos($cache_file,'Affiliates must not feature any other coupon/promotion codes such as those found in, but not limited to') !== false){
							$AllowNonaffCoupon = 'NO';
						}else if(stripos($cache_file, 'Advertiser reserves the right to reconcile or adjust the value on any transaction that is attributed to another marketing channel') !== false){
							$AllowNonaffCoupon = 'NO';
							$AllowNonaffPromo = 'NO';
							//通用条件
						}else if(stripos($cache_file,'affiliates can only use the voucher codes supplied') !==false){
							$AllowNonaffCoupon = 'NO';
						}else if(stripos($cache_file,'Voucher sites must only promote codes that have been designated for affiliate use') !==false){
							$AllowNonaffCoupon = 'NO';
						}else if(stripos($cache_file,'Affiliates shouldn’t post, use or feature any discount\/voucher codes from offline media sources.') !==false){
							$AllowNonaffCoupon = 'NO';
						}else if(stripos($cache_file,'publishers on the (.)+affiliate program should only use and monetise voucher codes (.)+ This includes user generated content, this cannot be monetised without the relevant permissions.') !==false){
							$AllowNonaffCoupon = 'NO';
						}else if(stripos($cache_file,'It is not allowed to promote vouchers that have not been communicated via the affiliate channel') !==false){
							$AllowNonaffCoupon = 'NO';
						}else if(stripos($cache_file,'affiliates may only promote voucher codes') !==false){
							$AllowNonaffCoupon = 'NO';
						}else if(stripos($cache_file,'Affiliates are not to promote any voucher codes that have not been provided') !==false){
							$AllowNonaffCoupon = 'NO';
						}else if(stripos($cache_file,'Affiliates should not display voucher\/discount codes that have been provided for use by other marketing channels.') !==false){
							$AllowNonaffCoupon = 'NO';
						}else if(stripos($cache_file,'Affiliates found to be promoting unauthorised discount codes or those issued through other marketing channels') !==false){
							$AllowNonaffCoupon = 'NO';
						}else if(stripos($cache_file,'Affiliates are ONLY allowed to use voucher codes issued to') !==false){
							$AllowNonaffCoupon = 'NO';
						}else if(stripos($cache_file,'Affiliates are requested not to use voucher codes') !==false){
							$AllowNonaffCoupon = 'NO';
						}else if(stripos($cache_file,'Voucher code sites may not list false voucher codes or voucher codes not associated with the affiliate program') !==false){
							$AllowNonaffCoupon = 'NO';
						}else if(stripos($cache_file,'Any sites found to be running voucher codes not specifically authorised') !==false){
							$AllowNonaffCoupon = 'NO';
						}else if(stripos($cache_file,'Publishers may only use coupons and promotional codes that are provided exclusively through the affiliate program.') !==false){
							$AllowNonaffCoupon = 'NO';
						}else if(stripos($cache_file,'Affiliates may not use misleading text on affiliate links	 buttons or images to imply that anything besides currently authorized affiliate deals or savings are available.') !==false){
							$AllowNonaffCoupon = 'NO';
							$AllowNonaffPromo = 'NO';
						}else if(stripos($cache_file,'Any discount promotion of our products by affiliates should be authorized') !==false){
							$AllowNonaffCoupon = 'NO';
							$AllowNonaffPromo = 'NO';
						}else if(stripos($cache_file,'The only coupons authorized for use are those that we make directly available to you.') !==false){
							$AllowNonaffCoupon = 'NO';
						}else if(stripos($cache_file,'All coupons must be publicly distributed coupons that are given to the affiliate.') !==false){
							$AllowNonaffCoupon = 'NO';
						}else if(stripos($cache_file,'Coupon sites may only post distributed coupons; that is coupons that are given to them or posted within the affiliate interface.') !==false){
							$AllowNonaffCoupon = 'NO';
						}else if(stripos($cache_file,'They need to promote the coupon which we will provide them.') !==false){
							$AllowNonaffCoupon = 'NO';
						}else if(stripos($cache_file,'Publishers may only use coupons and promotional codes that are provided through communication specifically intended for publishers in the affiliate program.') !==false){
							$AllowNonaffCoupon = 'NO';
						}else if(stripos($cache_file,'These are the ONLY promotion codes affiliates are authorized to use in their marketing efforts') !==false){
							$AllowNonaffCoupon = 'NO';
						}else if(stripos($cache_file,'will review each coupon offering before allowing an affiliate to use.') !==false){
							$AllowNonaffCoupon = 'NO';
						}
						
						//$TermAndCondition
						if(preg_match('@<td align="left" bgcolor="#FFF0F0" style="border:2px solid red;padding:10px;">(.*?)</td>@ms', $cache_file,$matches))
						    $TermAndCondition = $matches[1];
					}
					$arr_prgm[$strMerID]['AllowNonaffPromo'] = $AllowNonaffPromo;
					$arr_prgm[$strMerID]['AllowNonaffCoupon'] = $AllowNonaffCoupon;
					$arr_prgm[$strMerID]['TermAndCondition'] = addslashes($TermAndCondition);
				}
			}
			
			//print_r($arr_prgm[$strMerID]);
			$program_num++;
			if(count($arr_prgm) >= 100){
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
				$arr_prgm = array();
			}
		}

		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
			unset($arr_prgm);
		}
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

