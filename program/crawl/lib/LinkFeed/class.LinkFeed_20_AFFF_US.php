<?php

require_once 'text_parse_helper.php';

class LinkFeed_20_AFFF_US
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		if(SID == 'bdg01'){
			$this->API_KEY = '';
			$this->API_Password = '';
		}else{
			$this->API_KEY = 'D545A73C68';
			$this->API_Password = 'B067974BBE';
		}
	}
	
	function LoginIntoAffService()
	{
		//get para __VIEWSTATE and then process default login		
		if(!isset($this->info["AffLoginPostStringOrig"])) $this->info["AffLoginPostStringOrig"] = $this->info["AffLoginPostString"];
		$request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "",);
		if(isset($this->info["loginUrl"])){
			$this->info["AffLoginUrl"] = $this->info["loginUrl"];
		}
		$strUrl = $this->info["AffLoginUrl"];
		
		echo "login url:".$strUrl."\r\n";
		
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];
		
		$this->info["referer"] = $strUrl;
		
		if(isset($this->info["loginUrl"])){
			if (!preg_match('@id="__VIEWSTATE" value="(.*?)".*?id="__VIEWSTATEGENERATOR" value="(.*?)"@ms', $result, $g))
				mydie("die: login for LinkFeed_20_AFFF_US failed, param not found\n");
			$this->info["AffLoginPostString"] = sprintf('__VIEWSTATE=%s&__VIEWSTATEGENERATOR=%s&%s', urlencode($g[1]), urlencode($g[2]), $this->info["AffLoginPostStringOrig"]);
		}else{
			if (!preg_match('@id="__VIEWSTATE" value="(.*?)".*?id="__VIEWSTATEGENERATOR" value="(.*?)".*?id="__EVENTVALIDATION" value="(.*?)"@ms', $result, $g))
			mydie("die: login for LinkFeed_20_AFFF_US failed, param not found\n");
			$this->info["AffLoginPostString"] = sprintf('__VIEWSTATE=%s&__VIEWSTATEGENERATOR=%s&__EVENTVALIDATION=%s&%s', urlencode($g[1]), urlencode($g[2]), urlencode($g[3]), $this->info["AffLoginPostStringOrig"]);
		}
		
		if (preg_match('@id="__EVENTTARGET" value="(.*?)"@ms', $result, $g))
			$this->info['AffLoginPostString'] .= '&__EVENTTARGET=' . urlencode($g[1]);
		if (preg_match('@id="__EVENTARGUMENT" value="(.*?)"@ms', $result, $g))
			$this->info['AffLoginPostString'] .= '&__EVENTARGUMENT=' . urlencode($g[1]);
		if (preg_match('@id="topinclude$txtUsername" value="(.*?)"@ms', $result, $g))
			$this->info['AffLoginPostString'] .= '&topinclude$txtUsername=' . urlencode($g[1]);
		if (preg_match('@id="topinclude$txtPassword" value="(.*?)"@ms', $result, $g))
			$this->info['AffLoginPostString'] .= '&topinclude$txtPassword=' . urlencode($g[1]);
		
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info,6,true,true,false);
		return "stophere";
	}

	function simplest_xml_to_array($xmlstring) {
		return json_decode(json_encode((array) simplexml_load_string($xmlstring)), true);
	}
	
	function getCouponFeed()
	{
		$check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		$url = "https://api.affiliatefuture.com/PublisherService.svc/getAllVouchers?key=$this->API_KEY&passcode=$this->API_Password";
		//$url = 'https://api.affiliatefuture.com/PublisherService.svc/getAllVouchers?key=BD499DEC3A&passcode=510978B6D7';
		$request = array("AffId" => $this->info["AffId"], "method" => "get");
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$data = $this->simplest_xml_to_array($r['content'])['Vouchers']['Voucher'];
		//print_r($data);exit;
		$links = array();
		if(!empty($data)){
			foreach ($data as $v) {
				if ($v['Joined'] == 'No')
					continue;
				$link = array(
						"AffId" => $this->info["AffId"],
						"AffMerchantId" => "",
						"AffLinkId" => "",
						"LinkName" =>  "",
						"LinkDesc" =>  "",
						"LinkStartDate" => '0000-00-00 00:00:00',
						"LinkEndDate" => '0000-00-00 00:00:00',
						"LinkPromoType" => 'DEAL',
						"LinkHtmlCode" => '',
						"LinkCode" => '',
						"LinkOriginalUrl" => '',
						"LinkImageUrl" => '',
						"LinkAffUrl" => '',
						"DataSource" =>32,
						"IsDeepLink" => 'UNKNOWN',
						"Type"       => 'promotion'
				);
				if(!empty($v['VoucherDescription'])){
					$link['LinkName'] = $link['LinkDesc'] = $v['VoucherDescription'];
					if(!empty($v['TermsAndCondition'])){
						$link['LinkDesc'] .= " Condition:".$v['TermsAndCondition'];
					}
					if(!empty($v['CategoryName'])){
						$link['LinkDesc'] .= " Category:".$v['CategoryName'];
					}
				}
				if(!empty($v['VoucherCode'])){
					$link['LinkCode'] = $v['VoucherCode'];
					$link['LinkPromoType'] = "COUPON";
				}
				if(!empty($v['StartDate']))
					$link['LinkStartDate'] = date("Y-m-d H:i:s", strtotime($v['StartDate']));
		
				if(!empty($v['EndDate']))
					$link['LinkEndDate'] = date("Y-m-d 23:59:59", strtotime($v['EndDate']));
		
				if (empty($link['AffMerchantId']))
					$link['AffMerchantId'] = $v['ProgrammeID'];
		
				if (empty($link['AffLinkId']))
					$link['AffLinkId'] = md5($link['AffMerchantId'].$v['VoucherID']);
					
				if (empty($link['LinkAffUrl'])){
					if($v['LandingPage'])
						$link['LinkAffUrl'] = $v['LandingPage'];
					if($v['Tracking_URL'])
						$link['LinkAffUrl'] = $v['Tracking_URL'];
				}
				if(!empty($v['ImageURL']))
					$link['LinkImageUrl'] = $v['ImageURL'];
		
				if (empty($link['AffLinkId']) || empty($link['AffMerchantId']) || empty($link['LinkName']))
					continue;
				$links[] = $link;
				$arr_return["AffectedCount"] ++;
			}
		
		}
		if(count($links) > 0){
			$c_links = array_chunk($links,100);
			foreach ($c_links as $links) {
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
			}
		}
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'promotion');
		return $arr_return;
	}

	function GetAllLinksByAffId()
	{
		$check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, );
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", );
		$retry = 3;
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info, 1, false);
		$arr_merchant = $this->oLinkFeed->getApprovalAffMerchant($this->info["AffId"]);
		foreach ($arr_merchant as $AffMerchantId => $merinfo)
		{
			if (is_string($merinfo)) {
				$arr_temp = $this->getApprovalAffMerchantFromTask($this->info["AffId"], $merinfo);
				if (empty($arr_temp)) mydie("die:GetAllLinksFromAffByMerID failed, merchant id($merinfo) not found.\n");
				$merinfo = $arr_temp;
			}
			$aff_mer_id = $merinfo['IdInAff'];
			$url = sprintf("http://affiliates.affiliatefuture.com/myprogrammes/MerchantProgramme.aspx?id=%s", $merinfo['IdInAff']);
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$content = $r["content"];
			if($r["code"] == 500 || $r['code'] == 0)
			{
				if($retry > 0)
				{
					$retry --;
					echo "warning: their system may be crashed, sleep 10s and retry(left $retry)\n";
					sleep(10);
					$this->oLinkFeed->clearHttpInfos($this->info["AffId"]);
					return $this->GetAllLinksFromAffByMerID($merinfo, $retry);
				}
				mydie("die: their system may be crashed\n");
			}
			preg_match_all("/\\/merchants\\/ChooseBanners.aspx(.*)={$aff_mer_id}/i", $content, $matches);
			foreach((array)$matches[0] as $param)
			{
				$links = array();
				$param = html_entity_decode($param);
				$url = sprintf("http://affiliates.affiliatefuture.com/%s&ap=0&z=0&cb=1", $param);
				$r = $this->oLinkFeed->GetHttpResult($url, $request);
				$content = $r["content"];
				$content = preg_replace("/<!--.*?-->/si", "", $content);
				$content = preg_replace("/<textarea name=\"txtGenericCode\".*?>(.*?)<\\/textarea>/si", "", $content);
				preg_match_all('@<textarea name="(.*?)\$.*?>(.*?)</textarea>(.*?)</table>@si', $content, $match_links);
				if (empty($match_links) || empty($match_links[1]))
					continue;
				$link_type = $match_links[1];
				$link_htmlcode = $match_links[2];
				$link_info = $match_links[3];
				foreach((array)$link_info as $key => $v)
				{
					$promo_type = $link_type[$key];
					if($promo_type == "bannerRepeater")
					{
						$promo_type = "N/A";
						$link_name = "banner";
						$html_code = html_entity_decode(trim($link_htmlcode[$key]));
						preg_match_all("/window\.open.*?amp;id=(.*?)&amp;/si", $v, $match_span);
						if(!count($match_span))
							continue;
						$link_id = $match_span[1][0];
					}
					elseif($promo_type == "textLinkRepeater")
					{
						$promo_type = "coupon";
						$link_name = html_entity_decode(trim($link_htmlcode[$key]));
						preg_match_all("/<textarea.*?>(.*?)<\\/textarea>/si", $v, $match_span);
						if(!count($match_span))
							continue;
						$html_code = html_entity_decode(trim($match_span[1][0]));
						if(!$html_code)
							continue;
						preg_match_all("/window\.open.*?amp;id=(.*?)&amp;/si", $v, $match_span);
						if(!count($match_span))
							continue;
						$link_id = $match_span[1][0];
					}
					$link = array(
							"AffId" => $this->info["AffId"],
							"AffMerchantId" => $aff_mer_id,
							"AffLinkId" => $link_id,
							"LinkName" => html_entity_decode(trim($link_name)),
							"LinkDesc" => "",
							"LinkStartDate" => date("Y-m-d"),
							"LinkEndDate" => "0000-00-00",
							"LinkPromoType" => $promo_type,
							"LinkHtmlCode" => $html_code,
							"LinkOriginalUrl" => "",
							"LinkImageUrl" => "",
							"LinkAffUrl" => "",
							"DataSource" => "32",
					        "Type"       => 'link'
					);
					$link['LinkCode'] = get_linkcode_by_text($link_name);
					if ( empty($link['AffLinkId']))
						continue;
					elseif(empty($link['LinkName'])){
						$link['LinkPromoType'] = 'link';
					}
					$this->oLinkFeed->fixEnocding($this->info, $link, "link");
					$links[] = $link;
					$arr_return["AffectedCount"] ++;
				}
				if(sizeof($links) > 0)
					$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
	
			}
			echo sprintf("%s link(s) found.\n", $arr_return["AffectedCount"]);
			 
		}
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link');
		return $arr_return;
	}
	
	/* function GetAllLinksFromAffByMerID($merinfo,$retry = 3)
	{
		$aff_mer_id = $merinfo['IdInAff'];
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, );
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", );

		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info, 1, false);
		$url = sprintf("http://affiliates.affiliatefuture.com/myprogrammes/MerchantProgramme.aspx?id=%s", $merinfo['IdInAff']);
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r["content"];
		if($r["code"] == 500 || $r['code'] == 0)
		{
			if($retry > 0)
			{
				$retry --;
				echo "warning: their system may be crashed, sleep 10s and retry(left $retry)\n";
				sleep(10);
				$this->oLinkFeed->clearHttpInfos($this->info["AffId"]);
				return $this->GetAllLinksFromAffByMerID($merinfo, $retry);
			}
			mydie("die: their system may be crashed\n");
		}
		preg_match_all("/\\/merchants\\/ChooseBanners.aspx(.*)={$aff_mer_id}/i", $content, $matches);
		foreach((array)$matches[0] as $param)
		{
			$links = array();
			$param = html_entity_decode($param);
			$url = sprintf("http://affiliates.affiliatefuture.com/%s&ap=0&z=0&cb=1", $param);
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$content = $r["content"];
			$content = preg_replace("/<!--.*?-->/si", "", $content);
			$content = preg_replace("/<textarea name=\"txtGenericCode\".*?>(.*?)<\\/textarea>/si", "", $content);
			preg_match_all('@<textarea name="(.*?)\$.*?>(.*?)</textarea>(.*?)</table>@si', $content, $match_links);
			if (empty($match_links) || empty($match_links[1]))
				continue;
			$link_type = $match_links[1];
			$link_htmlcode = $match_links[2];
			$link_info = $match_links[3];
			foreach((array)$link_info as $key => $v)
			{
				$promo_type = $link_type[$key];
				if($promo_type == "bannerRepeater")
				{
					$promo_type = "N/A";
					$link_name = "banner";
					$html_code = html_entity_decode(trim($link_htmlcode[$key]));
					preg_match_all("/window\.open.*?amp;id=(.*?)&amp;/si", $v, $match_span);
					if(!count($match_span)) 
						continue;
					$link_id = $match_span[1][0];
				}
				elseif($promo_type == "textLinkRepeater")
				{
					$promo_type = "coupon";
					$link_name = html_entity_decode(trim($link_htmlcode[$key]));
					preg_match_all("/<textarea.*?>(.*?)<\\/textarea>/si", $v, $match_span);
					if(!count($match_span)) 
						continue;
					$html_code = html_entity_decode(trim($match_span[1][0]));
					if(!$html_code) 
						continue;
					preg_match_all("/window\.open.*?amp;id=(.*?)&amp;/si", $v, $match_span);
					if(!count($match_span))
						continue;
					$link_id = $match_span[1][0];
				}
				$link = array(
					"AffId" => $this->info["AffId"],
					"AffMerchantId" => $aff_mer_id,
					"AffLinkId" => $link_id,
					"LinkName" => html_entity_decode(trim($link_name)),
					"LinkDesc" => "",
					"LinkStartDate" => date("Y-m-d"),
					"LinkEndDate" => "0000-00-00",
					"LinkPromoType" => $promo_type,
					"LinkHtmlCode" => $html_code,
					"LinkOriginalUrl" => "",
					"LinkImageUrl" => "",
					"LinkAffUrl" => "",
					"DataSource" => "32",
				);
				$link['LinkCode'] = get_linkcode_by_text($link_name);
				if ( empty($link['AffLinkId']))
					continue;
                elseif(empty($link['LinkName'])){
                    $link['LinkPromoType'] = 'link';
                }
				$this->oLinkFeed->fixEnocding($this->info, $link, "link");
				$links[] = $link;
				$arr_return["AffectedCount"] ++;
			}
			if(sizeof($links) > 0)
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		}
		echo sprintf("%s link(s) found.\n", $arr_return["AffectedCount"]);
		sleep(1);
		return $arr_return;
	} */

	function getMessage()
	{
		$messages = array();
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", );
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info, 1, false);
		$urls = array(
				'http://affiliates.affiliatefuture.com/messagecentre/fullListing.aspx?t=2',
				'http://affiliates.affiliatefuture.com/messagecentre/fullListing.aspx?t=1',
				'http://affiliates.affiliatefuture.com/messagecentre/fullListing.aspx?t=3',
				);
		foreach ($urls as $url)
		{
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$content = $r['content'];
			preg_match_all('@<span class="boldgreen">(.*?)<br>@ms', $content, $chapters);
			foreach ((array)$chapters[1] as $v)
			{
				$data = array(
						'affid' => $this->info["AffId"],
						'messageid' => '',
						'sender' => '',
						'title' => '',
						'content' => '',
						'created' => '0000-00-00',
				);
				if (preg_match('@Repeater3__ctl\d+_Label\d+">(.*?)</span@', $v, $g))
					$data['created'] = parse_time_str($g[1], 'd/m/Y', false);
				if (preg_match('@detail.aspx\?id=(\d+)">(.*?)</a>@ms', $v, $g))
				{
					$data['messageid'] = $g[1];
					$data['title'] = trim(html_entity_decode(strip_tags($g[2])));
					$data['content_url'] = sprintf('http://affiliates.affiliatefuture.com/messagecentre/detail.aspx?id=%s', $data['messageid']);
				}
				if (empty($data['messageid']) || empty($data['content_url']))
					continue;
				$messages[] = $data;
			}
		}
		return $messages;
	}

	function getMessageDetail($data)
	{
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", );
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);
		$url = $data['content_url'];
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r['content'];
		if (preg_match('@"NewsArticleFull">(.*?)</span></P>@ms', $content, $g))
			$data['content'] = str_force_utf8(trim(html_entity_decode($g[1])));
		return $data;
	}

	function GetProgramFromAff()
	{
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
		$this->oLinkFeed->clearHttpInfos($this->info["AffId"]);
		$this->GetProgramByPage();		
		$this->checkProgramOffline($this->info["AffId"], $check_date);

		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}
	
	function GetProgramByPage()
	{
		echo "\tGet Program by page start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "get",
			//"postdata" => "",
		);
		
		//login af.affiliates
		$tmp_info = $this->info;
		$this->info["loginUrl"] = "http://af.affiliates.affiliatefuture.com/login.aspx";		
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info, 1, false);
		//get DEEP url TPL
		$prgm_default_url = array();
		
		$r = $this->oLinkFeed->GetHttpResult("http://af.affiliates.affiliatefuture.com/programmes/MerchantsJoined.aspx", $request);
		$result = $r["content"];
	// <textarea cols="" rows="" style="font-size: 11px; width: 400px">http://scripts.affiliatefuture.com/AFClick.asp?affiliateID=157529&merchantID=3741&programmeID=9652&mediaID=0&tracking=&url=</textarea>
	
		//preg_match_all("/http:\\/\\/scripts\\.affiliatefuture\\.com.*programmeID=(\d+).+&url=/i", $result, $m);
		preg_match_all("/http:\/\/scripts\.affiliatefuture\.com.*merchantID=(\d+).+programmeID=(\d+).+&url=/i", $result, $m);
		if(count($m))
		{
			foreach($m[2] as $k => $v)
			{
				if(strlen($m[0][$k]))
				{
					$prgm_default_url[$v] = $m[0][$k];
				}
				if(strlen($m[1][$k]))
				{
					$strMerID_arr[$v] = $m[1][$k];
				}
			}
		}
		
		//login
		$this->oLinkFeed->clearHttpInfos($this->info["AffId"]);		
		$this->info = $tmp_info;
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info, 1, false);
		//$r = $this->oLinkFeed->GetHttpResult("http://affiliates.affiliatefuture.com/login.aspx?username=couponsnapshot&password=L3u^&04Dcdld&yc&Submit=Login+Now", $request);
		//$r = $this->oLinkFeed->GetHttpResult("http://affiliates.affiliatefuture.com/login.aspx?txtUsername=couponsnapshot&txtPassword=L3u%5e&04Dcdld&yc&btnLogin=Login", $request);
		
		// for get new program, ignore isset program
		$old_prgm = array();

		$r = $this->oLinkFeed->GetHttpResult("http://affiliates.affiliatefuture.com/myprogrammes/default.aspx", $request);
		$result = $r["content"];
		//parse HTML
		$strLineStart = '<td>Merchant</td><td>Programme Name</td>';
		$nLineStart = stripos($result, $strLineStart, 0);

		$strLineStart = '<tr style="color:Black;';
		while ($nLineStart >= 0){
			$nLineStart = stripos($result, $strLineStart, $nLineStart);
			if ($nLineStart === false) break;

			$StatusInAffRemark = trim($this->oLinkFeed->ParseStringBy2Tag($result, 'background-color:', ';', $nLineStart));
			if($StatusInAffRemark == "White"){
				$StatusInAff = "Active";
			}elseif($StatusInAffRemark == "Red"){
				$StatusInAff = "Offline";
			}else{
				break;
			}
			
			//name
			$strMerName = trim($this->oLinkFeed->ParseStringBy2Tag($result, 'NAME="Hyperlink1">', "</span>", $nLineStart));
			if ($strMerName === false) continue;
			
			$prgm_id = intval($this->oLinkFeed->ParseStringBy2Tag($result, array('NAME="Hyperlink2" href="MerchantProgramme.aspx?id='), "\"", $nLineStart));
			if ($prgm_id === false) continue;
			
			$LogoUrl = '';
			$TermAndCondition = '';
			$strMerID = '';
			if (isset($strMerID_arr[$prgm_id])){
				$strMerID = $strMerID_arr[$prgm_id];
				$mer_url = "http://affiliates.affiliatefuture.com/merchants/AddProgramme.aspx?cat=&id=$strMerID";
				$mer_arr = $this->oLinkFeed->GetHttpResult($mer_url, $request);
				$mer_detail = $mer_arr["content"];
				$TermAndCondition = trim($this->oLinkFeed->ParseStringBy2Tag($mer_detail, array('<span id="Description">', '<b>'), '</span>'));
				$LogoUrl = trim($this->oLinkFeed->ParseStringBy2Tag($mer_detail, '<img id="Image1" src="', '"'));
			}
			
			$Partnership = "Active";
			
			$prgm_url = "http://affiliates.affiliatefuture.com/myprogrammes/MerchantProgramme.aspx?id=$prgm_id";
			$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
			$prgm_detail = $prgm_arr["content"];
			
			$Homepage = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Hyperlink1', 'href="'), '">'));
			$desc = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, '<span id="MerchantDescription">', '</span>'));
			$CommissionExt = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, '<span id="Offer">', '</span>'));

			$arr_prgm[$prgm_id] = array(
				"AffId" => $this->info["AffId"],
				"IdInAff" => $prgm_id,
				"Name" => addslashes($strMerName),
				"Homepage" => addslashes($Homepage),
				"Description" => addslashes($desc),
				"CommissionExt" => addslashes($CommissionExt),
				"StatusInAffRemark" => addslashes($StatusInAffRemark),
				"StatusInAff" => $StatusInAff,						//'Active','TempOffline','Offline'
				"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
				"LastUpdateTime" => date("Y-m-d H:i:s"),
				"DetailPage" => $prgm_url,
				"SupportDeepUrl" => "YES",
				"TermAndCondition" => addslashes($TermAndCondition),
				"LogoUrl" => addslashes($LogoUrl)
			);
			
			if(count($prgm_default_url)){
				$arr_prgm[$prgm_id]["AffDefaultUrl"] = isset($prgm_default_url[$prgm_id]) ? addslashes($prgm_default_url[$prgm_id]) : "";
			}
			
			if (!empty($strMerID))
				$old_prgm[$strMerID] = 1;
			
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
		
		
		$arr_prgm = array();

		$r = $this->oLinkFeed->GetHttpResult("http://affiliates.affiliatefuture.com/merchants/categoryListing.aspx", $request);
		$result = $r["content"];
		//print_r($result);exit;
		//parse HTML
		$strLineStart = 'Merchant</span>:</b>';
		
		$nLineStart = 0;
		while ($nLineStart >= 0){
			$nLineStart = stripos($result, $strLineStart, $nLineStart);
			if ($nLineStart === false) break;
			//name
			$strMerName = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('<span id="datalist', 'Name2">'), "</span>", $nLineStart));
			if ($strMerName === false) break;
			
			$Homepage = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('Site</span>:</b>', '<a' ,'>'), '</a>', $nLineStart));
			$desc = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('Merchant Description</span></b>', 'Description2">'), '</span>', $nLineStart));
			
			$tmp_Partnership = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('lnkSubscribe"', '">'), '</a>', $nLineStart));
			$Partnership = "NoPartnership";
			if($tmp_Partnership == "SUBSCRIBED"){
				$Partnership = "Active";
			}elseif($tmp_Partnership == "JOIN PROGRAMME"){
				$Partnership = "NoPartnership";
			}
			
			//only get no partnership merchant
			if($tmp_Partnership != "JOIN PROGRAMME") continue;
			
			//id
			$strMerID = intval($this->oLinkFeed->ParseStringBy2Tag($result, array('Hyperlink4"', 'id='), "\"", $nLineStart));
			if ($strMerID === false) break;
			
			// for get new program, ignore isset program
			if(isset($old_prgm[$strMerID])) continue;
			
			
			$mer_url = "http://affiliates.affiliatefuture.com/merchants/AddProgramme.aspx?cat=&id=$strMerID";
			$mer_arr = $this->oLinkFeed->GetHttpResult($mer_url, $request);
			$mer_detail = $mer_arr["content"];
			
			$TermAndCondition = trim($this->oLinkFeed->ParseStringBy2Tag($mer_detail, array('<span id="Description">', '<b>'), '</span>'));
			$LogoUrl = trim($this->oLinkFeed->ParseStringBy2Tag($mer_detail, '<img id="Image1" src="', '"'));
			$CommissionExt = trim($this->oLinkFeed->ParseStringBy2Tag($mer_detail, '<span id="datalist1_ctl00_OfferDetails">', '</span>'));
			$prgm_id = intval($this->oLinkFeed->ParseStringBy2Tag($mer_detail, 'href="Creatives.aspx?id=', '"'));
			//if ($prgm_id != $strMerID) continue;
			
			$prgm_url = "http://affiliates.affiliatefuture.com/myprogrammes/MerchantProgramme.aspx?id=$prgm_id";
			
			$arr_prgm[$prgm_id] = array(
				"AffId" => $this->info["AffId"],
				"IdInAff" => $prgm_id,
				"Name" => addslashes($strMerName),
				"Homepage" => addslashes($Homepage),
				"Description" => addslashes($desc),
				//"CreateDate" => $CreateDate,
				"StatusInAff" => 'Active',						    //'Active','TempOffline','Offline'
				"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
				"LastUpdateTime" => date("Y-m-d H:i:s"),
				"CommissionExt" => addslashes($CommissionExt),
				"DetailPage" => $prgm_url,
				"SupportDeepUrl" => "YES",
				"TermAndCondition" => addslashes($TermAndCondition),
				"LogoUrl" => addslashes($LogoUrl)
			);
			
			if(count($prgm_default_url)){
				$arr_prgm[$prgm_id]["AffDefaultUrl"] = isset($prgm_default_url[$prgm_id]) ? addslashes($prgm_default_url[$prgm_id]) : "";
			}
			
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
		
		echo "\tGet Program by page end\r\n";
		
		if($program_num < 2){
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

