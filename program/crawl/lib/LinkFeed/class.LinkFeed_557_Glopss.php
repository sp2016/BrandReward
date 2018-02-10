<?php

require_once 'text_parse_helper.php';

class LinkFeed_557_Glopss
{
	function __construct($aff_id,$oLinkFeed)
	{	
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);                            //杩斿洖涓�缁存暟缁勶紝瀛樺偍褰撳墠aff_id瀵瑰簲鐨勫悇涓瓧娈靛��
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;	
		if(SID == 'bdg02'){
			$this->Affiliate_ID = '307';
			$this->apikey = 'e4640ac12f610a53fc7c2d33250f8b674b39e0e5959357a362cb1d638e6bc03e';
		}else{
			//$this->apikey = 'b23f8fc3f7fb93664fe980884317ea8b96e9cff1927349f965a54799e276c8f3';
			//MK
			$this->Affiliate_ID = '315';
			$this->apikey = 'faf012482943091e037f88f6342a26155523e150af971ae87fc2e7eff68fef7d';
		}
		$this->NetworkId ='glopss';
	}
	
	function Login()
	{
		$LoginURL = $this->info["AffLoginUrl"];
		$request = array("AffId" => $this->info["AffId"], "method" => "get");
		$result = $this->oLinkFeed->GetHttpResult($LoginURL, $request);
		//print_r($result);exit;
		$content = $result['content'];
		$Token_key = $this->oLinkFeed->ParseStringBy2Tag($content, 'name="data[_Token][key]" value="', '"');
		$Token_fields = $this->oLinkFeed->ParseStringBy2Tag($content, 'name="data[_Token][fields]" value="', '"');
		//print_r($Token_key."\r\n".urlencode($Token_fields));exit;
		/* $request = array(
				"AffId" => $this->info["AffId"], 
				"method" => "post",
				"postdata" => "_method=POST&data%5B_Token%5D%5Bkey%5D=".$Token_key."&".$this->info["AffLoginPostString"]."&data%5B_Token%5D%5Bfields%5D=".urlencode($Token_fields),
				//"addheader" => "User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36",
		); */
		$this->info["AffLoginPostString"] .= '&'.urlencode('data[_Token][key]').'='.urlencode($Token_key).'&'.urlencode('data[_Token][fields]').'='.urlencode($Token_fields);
		//print_r($this->info);exit;
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);
		$result = $this->oLinkFeed->GetHttpResult($LoginURL, $request);
		//print_r($result);exit;
	}

	function GetAllLinksByAffId()
	{
		$check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, );
		$strUrl = "https://api.hasoffers.com/Apiv3/json?NetworkId={$this->NetworkId}&api_key=".$this->apikey."&Target=Affiliate_OfferFile&Method=findAll";
		$result = $this->oLinkFeed->GetHttpResult($strUrl);
		if ($result['code'] != 200)
			mydie("offerFile API resquest failed");
		else 
			echo "offerFile API resquest succ";
		$result = json_decode($result['content'], true);
		if ($result['response']['httpStatus'] != 200)
			mydie("offerFile API resquest failed");
		//var_dump($result);exit;
		$links = array();
		foreach ($result['response']['data']['data'] as $v)
		{
			$offer_id = $v['OfferFile']['offer_id'];
			$link = array(
					"AffId" => $this->info["AffId"],
					"AffMerchantId" => $offer_id,
					"AffLinkId" => $v['OfferFile']['id'],
					"LinkName" => addslashes($v['OfferFile']['filename']),
					"LinkDesc" => addslashes($v['OfferFile']['display']),
					"LinkStartDate" => $v['OfferFile']['created'],
					"LinkEndDate" => '0000-00-00 00:00:00',
					"LinkPromoType" => 'link',
					"LinkHtmlCode" => '',
					"LinkCode" => '',
					"LinkOriginalUrl" => '',
					"LinkImageUrl" => addslashes($v['OfferFile']['url']),
					"LinkAffUrl" => '',
					"DataSource" =>'421',
					"IsDeepLink" => 'UNKNOWN',
					"Type"       => 'link'
			);
			
			if (empty($link['LinkAffUrl']))
				$link['LinkAffUrl'] = "http://glopss.go2cloud.org/aff_c?offer_id=$offer_id&aff_id=$this->Affiliate_ID&file_id=$link[AffLinkId]";
			
			if (empty($link['LinkHtmlCode']))
				$link['LinkHtmlCode'] = create_link_htmlcode($link);
			
			if (empty($link['AffLinkId']) || empty($link['AffMerchantId']) || empty($link['LinkAffUrl']))
				continue;
			
			$links[] = $link;
			$arr_return["AffectedCount"] ++;
		}
		if(count($links) > 0){
			$c_links = array_chunk($links,100);
			foreach ($c_links as $links) {
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
			}
		}
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link');
		return $arr_return;
	}
	
	
	function getCouponFeed()
	{
		$check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array());
		
		$this->Login();
		$url = "http://core.glopss.com/affiliates/index.php?aff_id=".$this->Affiliate_ID . '';
		$request = array("AffId" => $this->info["AffId"], "method" => "get");
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		
		
		$url = "http://core.glopss.com/affiliates/index.php";
		$request = array(
		 "AffId" => $this->info["AffId"],
		 "method" => "post",
		 "postdata" => "command=downLoadXML&promotion_offset=10&aff_id=".$this->Affiliate_ID,
		);
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$data = simplexml_load_string($r['content'], null, LIBXML_NOCDATA);
		$data = json_decode(json_encode($data), true);
		//print_r($data);exit;
		$links = array();
		if(!empty($data)){
			foreach ($data['promotion'] as $v) {
			    
			    $link = array(
						"AffId" => $this->info["AffId"],
						"AffMerchantId" => $v['campaign_id'],
						"AffLinkId" => $v['promotion_id'],
						"LinkName" =>  addslashes($v['title']),
						"LinkDesc" =>  addslashes($v['description']),
						"LinkStartDate" => date('Y-m-d H:i:s',  strtotime($v['start_date'])),
						"LinkEndDate" => date('Y-m-d H:i:s',  strtotime($v['end_date'])),
						"LinkPromoType" => 'N/A',
						"LinkHtmlCode" => '',
						"LinkCode" => '',
						"LinkOriginalUrl" => '',
						"LinkImageUrl" => '',
						"LinkAffUrl" => $v['tracking_url'],
						"DataSource" =>'421',
						"IsDeepLink" => 'UNKNOWN',
						"Type"       => 'promotion'
				);
				
				if($v['coupon_code']){
				    $link['LinkCode'] = $v['coupon_code'];
				    $link['LinkPromoType'] = 'coupon';
				}else{
				    $link['LinkCode'] = '';
				}
				 
	            if (empty($link['AffLinkId']) || empty($link['AffMerchantId']) || empty($link['LinkName']))
					continue;
				if (empty($link['LinkHtmlCode']))
					$link['LinkHtmlCode'] = create_link_htmlcode($link);
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
	
	function GetProgramFromAff()
	{
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
		$this->GetProgramByApi();
		$this->checkProgramOffline($this->info["AffId"], $check_date);
		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}

    function GetStatus(){
        $this->getStatus = true;
        $this->GetProgramFromAff();
    }
	function GetProgramByApi()
	{
		echo "\tGet Program by api start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		$strUrl = "https://api.hasoffers.com/Apiv3/json?NetworkId={$this->NetworkId}&api_key=".$this->apikey."&Target=Affiliate_Offer&Method=findAll";
		$r = $this->oLinkFeed->GetHttpResult($strUrl);
		if($r['content'] === false)
		{
			mydie("Error type is can not get infomation from Api");
		}

		$apiResponse = json_decode($r['content'], true);
		$jsonErrorCode = json_last_error();
		if($jsonErrorCode !== JSON_ERROR_NONE) {
			mydie("die: Page overload.\n");
		}
		if($apiResponse['response']['status'] === 1)
		{
			echo 'API call successful'.PHP_EOL;
		}
		else
		{
			// An error occurred
			mydie("API call failed ({$apiResponse['response']['errorMessage']})");
		}
		print_r($apiResponse);exit;
		$result = $apiResponse['response']['data'];
		foreach($result as $item)
		{
			$v = $item['Offer'];
			$IdInAff = intval(trim($v['id']));
			if(!$IdInAff)
				continue;
			
			//get AffDefaultUrl
			$default_url = "https://api.hasoffers.com/Apiv3/json?NetworkId={$this->NetworkId}&api_key={$this->apikey}&Target=Affiliate_Offer&Method=generateTrackingLink&offer_id={$IdInAff}";
			$default_result = $this->oLinkFeed->GetHttpResult($default_url);
			$default_result = json_decode($default_result['content'], true);
			isset($default_result['response']['data']['click_url']) ? $AffDefaultUrl = addslashes($default_result['response']['data']['click_url']) : $AffDefaultUrl = '';
			
			//get TargetCountry
			$countries_url = "https://api.hasoffers.com/Apiv3/json?NetworkId={$this->NetworkId}&Target=Affiliate_Offer&Method=getTargetCountries&api_key={$this->apikey}&ids%5B%5D={$IdInAff}";
			$countries_result = $this->oLinkFeed->GetHttpResult($countries_url);
			$countries_result = json_decode($countries_result['content'], true);
			$CountryExt = array();
			if ($countries_result['response']['status'] == 1) {
				foreach ($countries_result['response']['data'][0]['countries'] as $k=>$val) {
					$CountryExt[] = $k;
				}
				if (!empty($CountryExt)) {
					$TargetCountryExt = addslashes(implode(",", $CountryExt));
				} else {
					$TargetCountryExt = '';
				}
			} else {
				$TargetCountryExt = '';
			}
			
			//get CategoryExt
			$category_url = "https://api.hasoffers.com/Apiv3/json?NetworkId={$this->NetworkId}&Target=Affiliate_Offer&Method=getCategories&api_key={$this->apikey}&ids%5B%5D={$IdInAff}";
			$category_result = $this->oLinkFeed->GetHttpResult($category_url);
			$category_result = json_decode($category_result['content'], true);
			$Category = array();
			if ($category_result['response']['status'] == 1) {
				foreach ($category_result['response']['data'][0]['categories'] as $val) {
					$Category[] = $val['name'];
				}
				if (!empty($Category)) {
					$CategoryExt = addslashes(implode(",", $Category));
				} else {
					$CategoryExt = '';
				}
			} else {
				echo "CategoryExt crawl failed of idinaff is $IdInAff, it's empty," . $category_result['response']['errorMessage'] . "\n\r";
				$CategoryExt = '';
			}
			
			//get LogoUrl
			$LogoUrl_url = "https://glopss.api.hasoffers.com/Apiv3/json?api_key=$this->apikey&Target=Affiliate_Offer&Method=getThumbnail&ids%5B%5D=$IdInAff";
			$LogoUrl_result = $this->oLinkFeed->GetHttpResult($LogoUrl_url);
			$LogoUrl_result = json_decode($LogoUrl_result['content'], true);
			//var_dump($LogoUrl_result);exit;
			if ($LogoUrl_result['response']['status'] == 1) {
				$Logo = end($LogoUrl_result['response']['data'][0]['Thumbnail']);
				$LogoUrl = $Logo['url'];
			} else {
				echo "LogoUrl crawl failed of idinaff is $IdInAff, it's empty," . $LogoUrl_result['response']['errorMessage'] . "\n\r";
				$LogoUrl = '';
			}
			
			$desc = $v['description'];
			$StatusInAffRemark = $v['status'];
			if($StatusInAffRemark == 'active')
				$StatusInAff = 'Active';
			else
			{
				$StatusInAff = 'Offline';
				mydie("New StatusInAffRemark appeared: $StatusInAffRemark ");
			}
			
			$TermAndCondition = '';
			if($v['require_terms_and_conditions']) $TermAndCondition = addslashes($v['terms_and_conditions']);
			
			$require_approval = $v['require_approval'];
			$approval_status = $v['approval_status'];
			if($approval_status == 'approved')
			{
				$Partnership = 'Active';
			}
			else if($approval_status == 'pending')
			{
				$Partnership = 'Pending';
			}
			else if($approval_status == 'rejected')
			{
				$Partnership = 'Declined';
			}
			else if(is_null($approval_status) && $require_approval == '1')
			{
				$Partnership = 'NoPartnership';
			}
			else
			{
				mydie("New approval_status appeared: $approval_status ");
			}

			if($v['payout_type'] == 'cpa_percentage') 
				$CommissionExt = $v['percent_payout'].'%';
			else
				$CommissionExt =  empty($v['currency']) ? '$'.round($v['default_payout'],2) : $v['currency'].round($v['default_payout'],2);
			$Homepage = $v['preview_url'];
			
			if($v['allow_website_links'])
				$SupportDeepUrl = 'YES';
			else
				$SupportDeepUrl = 'NO';

			$arr_prgm[$IdInAff] = array(
				"AffId" => $this->info["AffId"],
				"IdInAff" => $IdInAff,
				"Name" => addslashes((trim($v['name']))),
				"Description" => addslashes($desc),
				"Homepage" => addslashes($Homepage),
				"StatusInAffRemark" => addslashes($StatusInAffRemark),
				"StatusInAff" => $StatusInAff,						//'Active','TempOffline','Offline'
				"Partnership" => addslashes($Partnership),						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
				"CommissionExt" => addslashes($CommissionExt),
				"LastUpdateTime" => date("Y-m-d H:i:s"),
				"TermAndCondition" => $TermAndCondition,
				"SupportDeepUrl" => $SupportDeepUrl,
				'TargetCountryExt'=> addslashes($TargetCountryExt),
				'AffDefaultUrl' => $AffDefaultUrl,
				'CategoryExt' => $CategoryExt,
				'LogoUrl' => addslashes($LogoUrl),
			);
			$program_num++;
			if(count($arr_prgm) >= 100){
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
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
?>


