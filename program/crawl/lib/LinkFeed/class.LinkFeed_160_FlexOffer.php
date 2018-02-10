<?php

require_once 'text_parse_helper.php';
define('API_KEY_160', '51ab9632-905c-4080-a40d-9776e7dc11c2');
define('SOAP_ENDPOINT_160', 'http://services.flexoffers.com/WebServices/flexoffers.asmx');

class LinkFeed_160_FlexOffer
{
	var $info = array(
		"ID" => "160",
		"Name" => "FlexOffer",
		"IsActive" => "YES",
		"ClassName" => "LinkFeed_160_FlexOffer",
		"LastCheckDate" => "1970-01-01",
	);
	
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		$this->soapClient = null;
		$this->domain_ID = '1095181';
		$this->domain_url = 'http://www.promospro.com';
	}
		
	function Login()
	{
		$strUrl = "https://publisherpro.flexoffers.com/Login";
		//$strUrl = "https://publisherpro.flexoffers.com/Account/LegacyAccountValidation?Email=par.anflexoffers.list%40meikaitech.com&Password=fd8EY7WG%25FQ1&RememberMe=False";
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => "get",
				"postdata" => "",
		);
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];
		$Token = urlencode($this->oLinkFeed->ParseStringBy2Tag($result, array('name="__RequestVerificationToken"', 'value="'), '"'));
		
		$this->info["AffLoginPostString"] = str_ireplace('{__Token}', $Token, $this->info["AffLoginPostString"]);
		//print_r($this->info);exit;
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => 'post',
				"postdata" => $this->info["AffLoginPostString"],
				/* "addheader" => array(
						"upgrade-insecure-requests: 1",
				), */
		);
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];
		//print_r($r);exit;
		if (stripos($result, 'log off') !== false)
			echo "login succ\r\n";
		else 
			mydie("login failed");
		//$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info,2);
	}

	private function getSoapClient()
	{
		require_once INCLUDE_ROOT."wsdl/flexoffer_api/nusoap.php";
		if (!$this->soapClient)
		{
			$client = new nusoap_client(SOAP_ENDPOINT_160);
			$this->soapClient = $client;
			$err = $client->getError();
			if ($err)
				throw new Exception("Exception while create soap instance. $err.");
		}
		return $this->soapClient;
	}

	private function SoapClient_GetList($method, $program_id)
	{
		$default_campaign = 0;
		$default_product = 0;
		$client = $this->getSoapClient();
		$soapaction = SOAP_ENDPOINT_160 . "/$method";
		//create SOAP request
		$auth = sprintf('<AuthHeader xmlns="%s"><APIKey>%s</APIKey></AuthHeader>', SOAP_ENDPOINT_160, API_KEY_160);
		$body = sprintf('<%s xmlns="%s">
				<AdvertiserID>%s</AdvertiserID>
				<ProductID>%s</ProductID>
				<CampaignID>%s</CampaignID>
				</%s>', $method, SOAP_ENDPOINT_160, $program_id, $default_product, $default_campaign, $method);
		$msg = $client->serializeEnvelope($body, $auth, array(), 'document', 'literal');
		// Send the SOAP message and specify the soapaction
		$r = @$client->send($msg, $soapaction);
		if ($client->fault)
			throw new Exception($r);
		return $r;
	}

	function getCouponFeed()
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		return $arr_return;
	}

	/* function GetAllLinksFromAffByMerID($merinfo)
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0,);
		// text and banner links
		// method Banners_GetList is stopped Temporarily (no response).
		$methods = array(
				'Banners_GetList',
				'TextLinks_GetList');
		$links = array();
		foreach ($methods as $method)
		{
			$count = 0;
			$r = $this->SoapClient_GetList($method, $merinfo['IdInAff']);
			if (is_array(@$r[$method.'Result']['DataList']['Content']) && !empty($r[$method.'Result']['DataList']['Content']['ProductID']))
				$data = array($r[$method.'Result']['DataList']['Content']);
			else if (!empty($r[$method.'Result']['DataList']['Content']) && is_array($r[$method.'Result']['DataList']['Content']))
				$data = @$r[$method.'Result']['DataList']['Content'];
			else
				continue;
			foreach ((array)$data as $v)
			{
				$link = array(
						"AffId" => $this->info["AffId"],
						"AffMerchantId" => $merinfo['IdInAff'],
						"AffLinkId" => $v['ProductID'],
						"LinkName" =>  $v['ProductName'],
						"LinkDesc" =>  '',
						"LinkStartDate" => parse_time_str($v['ActiveDate'], null, false),
						"LinkEndDate" => parse_time_str($v['ExpireDate'], null, true),
						"LinkPromoType" => 'N/A',
						"LinkHtmlCode" => $v['Content'],
						"LinkCode" => '',
						"LinkOriginalUrl" => '',
						"LinkImageUrl" => '',
						"LinkAffUrl" => '',
						"DataSource" => 85,
				);
				if (preg_match('@a href="(.*?)"@', $link['LinkHtmlCode'], $g))
					$link['LinkAffUrl'] = $g[1];
				if (empty($link['LinkDesc']) && !empty($link['LinkHtmlCode']))
					$link['LinkDesc'] = trim(strip_tags($link['LinkHtmlCode']));
				if (empty($link['LinkCode']) && $link['LinkPromoType'] != 'COUPON')
				{
					$code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc']);
					if (!empty($code))
					{
						$link['LinkCode'] = $code;
						$link['LinkPromoType'] = 'COUPON';
					}
					else
						$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
				}
				if ($method == 'Banners_GetList' && preg_match('@img src="(.*?)"@', $link['LinkHtmlCode'], $g))
					$link['LinkImageUrl'] = $g[1];
				if (empty($link['AffLinkId']) )
					continue;
                elseif(empty($link['LinkName'])){
                    $link['LinkPromoType'] = 'link';
                }
				$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
				$count ++;
				$links[] = $link;
				$arr_return['AffectedCount'] ++;
				if ($arr_return['AffectedCount'] % 100 == 0)
				{
					$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
					$links = array();
				}
			}
			echo sprintf("program:%s, method:%s, %s links(s) found. \n", $merinfo['IdInAff'], $method, $count);
		}
		if(count($links) > 0)
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);

		return $arr_return;
	} */
	
	function GetAllLinksFromAffByMerID($merinfo)
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0,);
		// GET links
		$request = array("AffId" => $this->info["AffId"],
				"method" => "get",
				"addheader" => array(sprintf('apiKey: %s', API_KEY_160),
						'Accept: application/json'
				)
		);
		$pageNum = 1;
		$pageSize = 500;
		$links = array();
		$bHasNextPage = true;
		$count = 0;
		while($bHasNextPage)
		{
			$pgrm_url = "https://api.flexoffers.com/links?advertiserIds={$merinfo['IdInAff']}&page={$pageNum}&pageSize={$pageSize}";
			$result = $this->oLinkFeed->GetHttpResult($pgrm_url, $request);
			$r = $result['content'];
			$r = json_decode($r,true);
			//var_dump($r);exit;
			foreach ($r['results'] as $v)
			{
				$link = array(
						"AffId" => $this->info["AffId"],
						"AffMerchantId" => $merinfo['IdInAff'],
						"AffLinkId" => $v['productId'],
						"LinkName" =>  $v['linkText'],
						"LinkDesc" =>  $v['linkText'],
						"LinkStartDate" => parse_time_str($v['activeDate'], null, false),
						"LinkEndDate" => parse_time_str($v['expireDate'], null, true),
						"LinkPromoType" => 'N/A',
						"LinkHtmlCode" => $v['htmlCode'],
						"LinkCode" => '',
						"LinkOriginalUrl" => '',
						"LinkImageUrl" => $v['bannerUrl'],
						"LinkAffUrl" => $v['linkUrl'],
						"DataSource" => 85,
				);
				if($v['allowsDeeplinking'] == true){
					$link['LinkPromoType'] = 'deeplink';
				}else{
					$link['LinkPromoType'] = 'link';
				}
				
				if (isset($v['couponCode']))
				{	
					$code_array = explode(',', $v['couponCode']);
					$link['LinkCode'] = $code_array[0];
					$link['LinkPromoType'] = 'COUPON';
				}else
					$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
				
				$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
				$count ++;
				$links[] = $link;
				$arr_return['AffectedCount'] ++;
				if ($arr_return['AffectedCount'] % 100 == 0)
				{
					$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
					$links = array();
				}
			}
			echo sprintf("program:%s, %s links(s) found. \n", $merinfo['IdInAff'], $count);
			if ($r['totalCount'] <= $pageNum * $pageSize)
				$bHasNextPage = false;
			$pageNum++;
		}
		if(count($links) > 0)
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		
		return $arr_return;
	}

	function GetAllLinksByAffId()
    {
        $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0,);
        // GET links
        $request = array("AffId" => $this->info["AffId"],
            "method" => "get",
            "addheader" => array(
            	sprintf('apiKey: %s', API_KEY_160),
                'Accept: application/json'
            )
        );
        $pageNum = 1;
        $pageSize = 500;
        $links = array();
        $bHasNextPage = true;
        $count = 0;
        while($bHasNextPage) {
        	echo "page:$pageNum\r\n";

            $pgrm_url = "https://api.flexoffers.com/links?page={$pageNum}&pageSize={$pageSize}";
            $result = $this->oLinkFeed->GetHttpResult($pgrm_url, $request);
            $r = $result['content'];
            $r = json_decode($r,true);
//            print_r($r);exit;
            foreach ($r['results'] as $v) {

            	if (!intval($v['productId'])) {
            		continue;
				}
                if (!intval($v['advertiserId'])) {
                    continue;
                }
                if (empty($v['linkText'])) {
            		continue;
				}

                $link = array(
                    "AffId" => $this->info["AffId"],
                    "AffMerchantId" => intval($v['advertiserId']),
                    "AffLinkId" => intval($v['productId']),
                    "LinkName" =>  $v['linkText'],
                    "LinkDesc" =>  $v['linkText'],
                    "LinkStartDate" => parse_time_str($v['activeDate'], null, false),
                    "LinkEndDate" => parse_time_str($v['expireDate'], null, true),
                    "LinkPromoType" => 'N/A',
                    "LinkHtmlCode" => $v['htmlCode'],
                    "LinkCode" => '',
                    "LinkOriginalUrl" => '',
                    "LinkImageUrl" => $v['bannerUrl'],
                    "LinkAffUrl" => $v['linkUrl'],
                    "DataSource" => 85,
                );
                if($v['allowsDeeplinking'] == true){
                    $link['LinkPromoType'] = 'deeplink';
                }else{
                    $link['LinkPromoType'] = 'link';
                }

                if (isset($v['couponCode']))
                {
                    $code_array = explode(',', $v['couponCode']);
                    $link['LinkCode'] = $code_array[0];
                    $link['LinkPromoType'] = 'COUPON';
                }else
                    $link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);

                $this->oLinkFeed->fixEnocding($this->info, $link, "feed");
                $count ++;
                $links[] = $link;
                $arr_return['AffectedCount'] ++;
                if ($arr_return['AffectedCount'] % 100 == 0)
                {
                    $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
                    $links = array();
                }
            }

            if ($r['totalCount'] <= $pageNum * $pageSize)
                $bHasNextPage = false;
            $pageNum++;
        }
        if(count($links) > 0)
            $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);

        return $arr_return;
    }

	function getMessage()
	{	
		//$start_time = strtotime("-7 day");
		$messages = array();
		$page = 1;
		$pageSize = 30;
		$hasNextpage = true;
		$this->Login();
		while ($hasNextpage)
		{
			$request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "sort=&page=$page&pageSize=$pageSize&group=&filter=",);
			//$url = 'https://publisher.flexoffers.com/Members/Mail/default.aspx';
			$url = 'https://publisherpro.flexoffers.com/Notification/GetNotificationsKendo';
			
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$content = json_decode($r['content'], true);
			//var_dump($r);exit;
			$total = $content['Total'];
			if ($page * $pageSize > $total)
				$hasNextpage = false;
			if (empty($content['Data']) || !$content['Total'])
				mydie("die: data is empty from page $page!");
			if ($content['Errors'])
				mydie("die: data is error ".$content['Errors']);
					
			foreach ($content['Data'] as $v)
			{
				//$body = $this->filterPageCode(html_entity_decode($v['Body']));
				//$body = html_entity_decode($v['Body']);
				$body = $v['Body'];
				$body = str_replace('[%domain_id%]', $this->domain_ID, $body);
				$body = str_replace('[%domain_url%]', $this->domain_url, $body);
				$data = array(
						'affid' => $this->info["AffId"],
						'messageid' => $v['Id'],
						'sender' => '',
						'title' => addslashes(trim($v['Subject'])),
						'content' => trim($body),
						'created' => '',
				);
				preg_match('@\/Date\((.*)\)\/@', $v['DateSent'], $g);
				$created = round($g[1]/1000);
				$data['created'] = date('Y-m-d h:i:s', $created);
				/* if ($created < $start_time)
					break 2; */
				if (empty($data['title']) || $data['created'] == '0000-00-00')
					continue;
				$messages[] = $data;
				//print_r($data);
			}
			$page++;
		}
		return $messages;
	}

	function filterPageCode($string)
	{
		$search = array (
				'/<script[^>]*?>.*?<\/script>/isu', // 去掉 javascript 
				'/<style[^>]*?>.*?<\/style>/isu', // 去掉 css 
				'/<!--[\/!]*?[^<>]*?>/isu', // 去掉 注释标记 
				'/\s{2,}/isu', // 去掉空白字符 
		);
		$tarr = array(
				"", 
				"", 
				"", 
				"\n\t",
		);
		$str = preg_replace($search,$tarr,$string);
		return $str;
	}
	
	function getMessageDetail($data)
	{
		$url = $data['content_url'];
		$request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => $data['postdata'], );
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r['content'];
		if (preg_match('@<div class="message">(.*?)</div>\s+<br />\s+<br />@ms', $content, $g))
			$data['content'] = trim(html_entity_decode($g[1]));
		// the postdata is not a field of message in the database.
		// delete postdata and do not write it to the database.
		unset($data['postdata']);
		return $data;
	}

	function GetProgramFromAff()
	{	
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";		
		$this->getProgramByNewApi();
		//$this->getProgramByApi();
		$this->checkProgramOffline($this->info["AffId"], $check_date, $this->compare_prgmNum);

		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}
	
	function GetProgramFromByPage()
	{
		echo "\tGet Program by page start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
			
		//step 1,login		
		$this->Login();		
		
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "get",
			"postdata" => "",
		);
		
		$tmp_request = array(
			"AffId" => $this->info["AffId"],
			"method" => "get"
		);				
		
		//program management adv
		echo "get program list.\r\n";
		$strUrl = "https://publisher.flexoffers.com/Members/Advertisers/advertisers.aspx";
		$result = "";		
		
		$hasNextPage = true;
		$page = 1;
		while($hasNextPage){
			echo "\t page $page.";			
			if(!empty($result)){
				//$__EVENTTARGET = urlencode('dg_Merchants$ctl44$ctl01');				
				$__EVENTVALIDATION = urlencode($this->oLinkFeed->ParseStringBy2Tag($result, array('name="__EVENTVALIDATION"', 'value="'), '"'));	
				$__VIEWSTATE = urlencode($this->oLinkFeed->ParseStringBy2Tag($result, array('name="__VIEWSTATE"', 'value="'), '"'));
				$__SCROLLPOSITIONX = urlencode($this->oLinkFeed->ParseStringBy2Tag($result, array('name="__SCROLLPOSITIONX"', 'value="'), '"'));
				$__SCROLLPOSITIONY = urlencode($this->oLinkFeed->ParseStringBy2Tag($result, array('name="__SCROLLPOSITIONY"', 'value="'), '"'));
				$request["method"] = "post";
				$request["postdata"] = '__EVENTTARGET='.$__EVENTTARGET.'&__EVENTARGUMENT=&__LASTFOCUS=&__VIEWSTATE='.$__VIEWSTATE.'&__SCROLLPOSITIONX='.$__SCROLLPOSITIONX.'&__SCROLLPOSITIONY='.$__SCROLLPOSITIONY.'&ctl00%24DomainsDropDownList=1043725&ctl00%24ContentPlaceHolder1%24txtKeywords=&ctl00%24ContentPlaceHolder1%24CategoriesDropDownList=0&ctl00%24ContentPlaceHolder1%24StatusDropDownList=0&ctl00%24ContentPlaceHolder1%24NetworkRank=0&ctl00%24ContentPlaceHolder1%24lvAdvertisers%24drpEPCsort=&ctl00%24ContentPlaceHolder1%24DataPager2%24ctl00%24drpPageSize=10';
			}elseif($page != 1){
				mydie("die: postdata error.\n");
			}
			
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$result = $r["content"];
		
			$tmp_target = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('Go to page:', "<span class=\"active\">$page</span>", '<a class="NextPrevious"', '__doPostBack(&#39;'), '&#39;,&#39;&#39;)">Next'));
			
			if($tmp_target == false) $hasNextPage = false;		
			$__EVENTTARGET = urlencode($tmp_target);			
			
			$strLineStart = "<tr id='srow";
			
			$nLineStart = 0;
			while ($nLineStart >= 0){
				$nLineStart = stripos($result, $strLineStart, $nLineStart);
				if ($nLineStart === false) break;
				
				//id
				$IdInAff = intval($this->oLinkFeed->ParseStringBy2Tag($result, 'href="/Members/ProgramDetails.aspx?id=', '"', $nLineStart));
				if (!$IdInAff) break;				
				//name
				$strMerName = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<a class="fb-modal" href="/Members/ProgramDetails.aspx?id='.$IdInAff.'">' , "</a>", $nLineStart));
				if ($strMerName === false) break;

				$CategoryExt = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, '<td class="AdCatInfo">' , "</td>", $nLineStart)));
				$CategoryExt = str_ireplace("show more...", "", $CategoryExt);
		
				//$Rank = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, '<td class="AdNetworkInfo">' , "</td>", $nLineStart)));
				//$RankInAff = intval(trim($this->oLinkFeed->ParseStringBy2Tag($Rank, array('"current-rating"', 'style="width:'), "%")));

				$epc = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td class="Ad3MonthInfo">', "</td>", $nLineStart));				
				$EPC90d = substr(trim($this->oLinkFeed->ParseStringBy2Tag($epc, '<strong>', "</strong> <small>3 Month")), 1);
				$EPC30d = substr(trim($this->oLinkFeed->ParseStringBy2Tag($epc, array('3 Month', '<strong>'), "</strong> <small>30 Day")), 1);
				$EPCDefault = substr(trim($this->oLinkFeed->ParseStringBy2Tag($epc, array('30 Day', '<strong>'), "</strong> <small>7 Day")), 1);
				
				$JoinDate = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td class="AdCatInfo">' , "</td>", $nLineStart));
				$JoinDate = str_ireplace("/", "-", $JoinDate);
				$JoinDate = date("Y-m-d H:i:s", strtotime($JoinDate));
				
				$StatusInAffRemark = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, '<td class="AdStatusInfo">' , "</td>", $nLineStart)));
				
				if($StatusInAffRemark == "Get Links"){
					$StatusInAffRemark = "Approved";
					$StatusInAff = 'Active';
					$Partnership = 'Active';
				}elseif($StatusInAffRemark == "Pending"){
					$StatusInAff = 'Active';
					$Partnership = 'Pending';				
				}elseif($StatusInAffRemark == "Deactivated"){
					$StatusInAff = 'Active';
					$Partnership = 'Expired';				
				}elseif($StatusInAffRemark == "Declined"){
					$StatusInAff = 'Active';
					$Partnership = 'Declined';				
				}else{
					$StatusInAff = 'Active';
					$Partnership = 'NoPartnership';
				}				
				
				//$xxx[$StatusInAffRemark] = 1;
								
				
				$prgm_url = "https://publisher.flexoffers.com/Members/ProgramDetails.aspx?id=$IdInAff";
				$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $tmp_request);
				$prgm_detail = $prgm_arr["content"];
				
				$Homepage = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<a id="wsite"', 'href="'), '"'));
				$CommissionExt = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, '<span id="ctl00_ContentPlaceHolder1_lblPayout0">', '</span>'));
				$desc = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, '<span id="ctl00_ContentPlaceHolder1_lblDescription">', '</span>'));
				$TermAndCondition = strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, '<div id="tc" class="">', '</div>'));
				
				$Rank = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('Network Rank', '<span class="inline-rating">'), "</span>")));
				$RankInAff = intval(trim($this->oLinkFeed->ParseStringBy2Tag($Rank, array('"current-rating"', 'style="width:'), "%")));
				
				if($RankInAff == 25){
					$RankInAff = 1;
				}elseif($RankInAff == 50){
					$RankInAff = 2;
				}elseif($RankInAff == 75){
					$RankInAff = 3;
				}elseif($RankInAff == 100){
					$RankInAff = 4;
				}else{
					$RankInAff = 0;
				}
				
				$deep_info_url = "https://publisher.flexoffers.com/members/content/getlinks.aspx?ID=$IdInAff";
				$deep_arr = $this->oLinkFeed->GetHttpResult($deep_info_url, $tmp_request);
				$deep_info = $deep_arr["content"];			
				$SupportDeepurl = $this->oLinkFeed->ParseStringBy2Tag($deep_info, '<td align="center" style="width:1%;">', '</td>');			
				if($SupportDeepurl == "Yes"){
					$SupportDeepurl = "YES";
				}else{
					$SupportDeepurl = "NO";
				}
				
				$arr_prgm[$IdInAff] = array(
					"Name" => addslashes(html_entity_decode(trim($strMerName))),
					"AffId" => $this->info["AffId"],
					"CategoryExt" => addslashes($CategoryExt),
					"RankInAff" => addslashes($RankInAff),
					"JoinDate" => $JoinDate,					
					"IdInAff" => $IdInAff,
					"StatusInAff" => $StatusInAff,						//'Active','TempOffline','Offline'
					"StatusInAffRemark" => addslashes($StatusInAffRemark),
					"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'				
					"Description" => addslashes($desc),
					"Homepage" => $Homepage,
					"CommissionExt" => addslashes($CommissionExt),
					"EPC30d" => $EPC30d,
					"EPC90d" => $EPC90d,
					"EPCDefault" => $EPCDefault,					
					"TermAndCondition" => addslashes($TermAndCondition),					
					"LastUpdateTime" => date("Y-m-d H:i:s"),
					"DetailPage" => $prgm_url,
					"SupportDeepurl" => $SupportDeepurl,
				);
				//print_r($arr_prgm);exit;
				$program_num++;
				
				if(count($arr_prgm) >= 100){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
			}
			
			$page++;			
			if($page > 1000){
				mydie("die: Page overload.\n");
			}
		}
		
		//print_r($xxx);
		
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
			unset($arr_prgm);
		}
		
		echo "\tGet Program by page end\r\n";
		
		if($program_num < 10){
			mydie("die: program count < 10, please check program.\n");
		}
		
		echo "\tUpdate ({$program_num}) program.\r\n";
		echo "\tSet program country int.\r\n";
		
		$objProgram->setCountryInt($this->info["AffId"]);		
	}
	
	function getProgramByApi(){
		echo "\tGet Program by api start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		
		$aff_url_keyword = array();
		$aff_url_keyword = $objProgram->getAffiliateUrlKeywords();
		
		// for check if through main aff
		$commint_zero_prgm = array();
		$commint_zero_prgm = $objProgram->getCommIntProgramByAffId($this->info["AffId"]);
		
		// Internal EPC=0 
		/* $internal_prgm = array();
		$fp = fopen("http://couponsn:IOPkjmN1@reporting.megainformationtech.com/dataapi/offline_program.php?affid=".$this->info["AffId"], "r");
		if($fp){
			echo "\t get Internal EPC=0 program succeed.\n";
			$i = 0;
			while(!feof($fp))
			{
				$line = trim(fgets($fp));
				if(!$line) continue;
				$tmp_arr = explode("\t", $line);//Id in Aff, Program Name, Sales, Commission, CR(Commission Rate)
				
				$affid = intval($tmp_arr[0]);				
				$idinaff = trim($tmp_arr[1]);
				
			
				if($affid == $this->info["AffId"]){					
					$internal_prgm[$idinaff] = 1;
					$i++;
				}
			}
			fclose($fp);
			echo "\t get ($i) Internal EPC=0 program\n";
		}else{
			echo "\t Internal EPC=0 program failed.\n";
		} */
		
		$method = "Advertisers_GetList";
		$client = $this->getSoapClient();
		$soapaction = SOAP_ENDPOINT_160 . "/$method";
		//create SOAP request
		$auth = sprintf('<AuthHeader xmlns="%s"><APIKey>%s</APIKey></AuthHeader>', SOAP_ENDPOINT_160, API_KEY_160);
		$body = sprintf('<%s xmlns="%s">
			<CategoryID>0</CategoryID>
			</%s>', $method, SOAP_ENDPOINT_160, $method);
		$msg = $client->serializeEnvelope($body, $auth, array(), 'document', 'literal');
		// Send the SOAP message and specify the soapaction
		$r = @$client->send($msg, $soapaction);
		if ($client->fault)
			throw new Exception($r);
		
		$data = array();
		if (is_array(@$r[$method.'Result']['DataList']['Advertiser']))
			$data = $r[$method.'Result']['DataList']['Advertiser'];
		
		foreach ((array)$data as $v)
		{            
			$IdInAff = intval($v['ID']);
			if(!$IdInAff) continue;		

			$StatusInAffRemark = "";
			$Partnership = "Active";
			
			$through_main_aff = true;
			/*if(isset($internal_prgm[$IdInAff]) || isset($commint_zero_prgm[$IdInAff])){
				$through_main_aff = false;
				// check $aff_url_keyword
				$test_url = $objProgram->getPSDefaultAffUrlByIdInAff($IdInAff, $this->info["AffId"]);				
				$r = $this->oLinkFeed->GetHttpResult($test_url, array("header" => 1, "nobody" => 1));
				$header = $r["content"];
				//preg_match_all("/domain.=([A-Za-z0-9.]+)/i", $header, $matches);
				preg_match_all("/Location:(.*)\r\n/i", $header, $matches);
				//print_r($matches);
				if(count($matches[1])){
					//$domain_arr = array_unique($matches[1]);
					//$tmp_arr = array_intersect($aff_url_keyword, $domain_arr);			
					foreach($aff_url_keyword as $url_k){
						foreach($matches[1] as $loc){
							if(stripos($loc, $url_k) !== false){
								$through_main_aff = true;
								break 2;
							}
						}		
					}					
				}		
			}
			
			if($through_main_aff == false){				
				if(isset($internal_prgm[$IdInAff])){
					$StatusInAffRemark = "Internal EPC=0";
				}else{
					$StatusInAffRemark = "Not Through Main Aff";
				}
				$Partnership = "NoPartnership";
			}*/
                                    
            $arr_prgm[$IdInAff] = array(
				"Name" => addslashes(html_entity_decode(trim($v['Name']))),
				"AffId" => $this->info["AffId"],					
				"RankInAff" => addslashes($v['Rank']),					
				"IdInAff" => $IdInAff,
            	"StatusInAffRemark" => $StatusInAffRemark,
				"StatusInAff" => 'Active',					
				"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'				
				"Description" => addslashes($v['Description']),
				"Homepage" => addslashes($v['DomainURL']),					
				"LastUpdateTime" => date("Y-m-d H:i:s"),
			);

			//print_r($arr_prgm);exit;
			$program_num++;
				
			if(count($arr_prgm) >= 100){
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
				$arr_prgm = array();
			}
		}		
		
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
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
	
	function checkProgramOffline($AffId, $check_date, $compare_prgmNum){		
		$objProgram = new ProgramDb();
		$prgm = array();
		$prgm = $objProgram->getNotUpdateProgram($this->info["AffId"], $check_date);		
				
		if(count($prgm) > 30 && $compare_prgmNum['total'] != $compare_prgmNum['prgm_num']){
			mydie("die: too many offline program (".count($prgm).").\n");
		}else{
			$objProgram->setProgramOffline($this->info["AffId"], $prgm);
			echo "\tSet (".count($prgm).") offline program.\r\n";
		}
	}
	
	function getProgramByNewApi()
	{	
		echo "\tGet Program by api start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		
		//GET advertisers
		echo "\tGet advertisers start\r\n";
		$request = array("AffId" => $this->info["AffId"], 
				"method" => "get", 
				"addheader" => array(sprintf('apiKey: %s', API_KEY_160),
					'Accept: application/json'
				)
		);
		$pgrm_url = 'https://api.flexoffers.com/advertisers';
		$result = $this->oLinkFeed->GetHttpResult($pgrm_url, $request);
		$r = $result['content'];
		$r = json_decode($r,true);
		$total = count($r);
		echo "\tGet advertisers finish\r\n";
		
		//GET categories
		/* echo "\tGet categories start\r\n";
		$category_url = 'https://api.flexoffers.com/categories';
		$result = $this->oLinkFeed->GetHttpResult($category_url, $request);
		$re = $result['content'];
		$re = json_decode($re,true);
		echo "\tGet categories finish\r\n"; */
		//var_dump($re);exit;
	
		foreach ($r as $v){
			
			$IdInAff = $v['id'];
			$StatusInAffRemark = $v['applicationStatus'];
			if($StatusInAffRemark == 'Approved')
				$Partnership = "Active";
			else
				$Partnership = "NoPartnership";
			$categoryId = $v['categoryIds'];
			$CategoryExt = '';
			/* foreach ($re as $cates){
				foreach ($cates['subCategories'] as $cate){
					if ($cate['id'] == $categoryId){
						$CategoryExt = $cate['name'];
						break 2;
					}
				}
			} */
			
			//GET deep link
			$deep_url = "https://publisherpro.flexoffers.com/tfshandler/links/links/{$this->domain_ID}?deepOnly=true&pageNumber=1&pageSize=1&programIds={$IdInAff}&sortColumn=created&sortOrder=desc";
			$request = array("AffId" => $this->info["AffId"], "method" => "get");
			$result = $this->oLinkFeed->GetHttpResult($deep_url, $request);
			$res = $result['content'];
			$res = json_decode($res,true);
			//var_dump($r);exit;
			$SupportDeepUrl = 'NO';
			$AffDefaultUrl = '';
			if (isset($res['results'][0])){
				$productId = $res['results'][0]['productId'];
				$contentTypeId = $res['results'][0]['contentTypeId'];
				if(isset($res['results'][0]['allowsDeeplinking']) && $res['results'][0]['allowsDeeplinking'] == true){
					$SupportDeepUrl = 'YES';
					$AffDefaultUrl = "https://track.flexlinkspro.com/a.ashx?foid={$this->domain_ID}.{$productId}&foc={$contentTypeId}&fot=9999&fos=1&url=";
				}else{
					$SupportDeepUrl = 'NO';
					$AffDefaultUrl = "https://track.flexlinkspro.com/a.ashx?foid={$this->domain_ID}.{$productId}&foc={$contentTypeId}&fot=9999&fos=1";
				}
			}
			
			
			$arr_prgm[$IdInAff] = array(
					"Name" => addslashes(html_entity_decode(trim($v['name']))),
					"AffId" => $this->info["AffId"],
					"IdInAff" => $IdInAff,
					"StatusInAffRemark" => addslashes($StatusInAffRemark),
					"StatusInAff" => 'Active',
					"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
					"Description" => addslashes($v['description']),
					"Homepage" => addslashes($v['domainUrl']),
					"CommissionExt" => addslashes($v['payout']),
					//"CategoryExt" => addslashes($CategoryExt),
					"LastUpdateTime" => date("Y-m-d H:i:s"),
					"SupportDeepUrl" => addslashes($SupportDeepUrl),
					"AffDefaultUrl" => addslashes($AffDefaultUrl)
			);
			$program_num++;
			
			if(count($arr_prgm) >= 100){
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
				$arr_prgm = array();
				echo "\tupdate Program {$program_num}\r\n";
			}
		}
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
			unset($arr_prgm);
		}
		
		echo "\tGet Program by api end\r\n";
		
		if($program_num < 10){
			mydie("die: program count < 10, please check program.\n");
		}
		$this->compare_prgmNum = array(
				'total' => $total,
				'prgm_num' => $program_num
		);
		echo "\tUpdate ({$program_num}) program.\r\n";
		
		echo "\tSet program country int.\r\n";
		$objProgram->setCountryInt($this->info["AffId"]);
	}
}
?>
