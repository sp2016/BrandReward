<?php

require_once 'text_parse_helper.php';
require_once 'xml2array.php';


class LinkFeed_721_Prime_Digital
{
	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->getStatus = false;
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		if(SID == 'bdg02'){
			$this->Affiliate_ID = '';
			$this->apikey = '';
		}else{
			$this->Affiliate_ID = '1706';
			$this->apikey = 'c1c980dec0655657596492dbe1a725568dedd584f4c9e610012da33c286a073b';
		}
		$this->NetworkId ='primedigital';
	}
	
	function getCouponFeed()
	{
		$check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
		return $arr_return;
	}
	
	function GetAllLinksByAffId()
	{
		$check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, );
		
		/* $strUrl = "https://api.hasoffers.com/Apiv3/json?NetworkId={$this->NetworkId}&api_key=".$this->apikey."&Target=Affiliate_OfferFile&Method=findAll";
		$result = $this->oLinkFeed->GetHttpResult($strUrl);
		//var_dump($result);exit;
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
					"DataSource" =>'',
					"IsDeepLink" => 'UNKNOWN',
					"Type"       => 'link'
			);
			
			if (empty($link['LinkAffUrl']))
				$link['LinkAffUrl'] = "http://primedigital.go2cloud.org/aff_c?offer_id=$offer_id&aff_id=$this->Affiliate_ID&file_id=$link[AffLinkId]";
			
			if (empty($link['LinkHtmlCode']))
				$link['LinkHtmlCode'] = create_link_htmlcode($link);
			
			if (empty($link['AffLinkId']) || empty($link['AffMerchantId']) || empty($link['LinkAffUrl']))
				continue;
			
			$links[] = $link;
			$arr_return["AffectedCount"] ++;
		} */
		//以上是从API中爬取link的正规代码，因为联盟没有提供link数据，顾在下面用defaultUrl代替
		$sql = "SELECT AffId,IdInAff,IdInAff as AffMerchantId,Name as MerchantName, AffDefaultUrl FROM program WHERE AffId = {$this->info['AffId']} AND StatusInAff in ('Active') AND Partnership in ('Active')";
		$arr_merchant = $this->oLinkFeed->objMysql->getRows($sql, "IdInAff");
		//print_r($arr_merchant);exit;
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => "get",
				"postdata" => '',
		);
		foreach ($arr_merchant as $AffMerchantId => $merinfo)
		{
			$link = array(
					"AffId" => $this->info["AffId"],
					"AffMerchantId" => $merinfo['AffMerchantId'],
					"AffLinkId" => $merinfo['AffMerchantId'],
					"LinkName" => $merinfo['MerchantName'],
					"LinkDesc" => '',
					"LinkStartDate" => '0000-00-00 00:00:00',
					"LinkEndDate" => '0000-00-00 00:00:00',
					"LinkPromoType" => 'link',
					"LinkHtmlCode" => '',
					"LinkCode" => '',
					"LinkOriginalUrl" => '',
					"LinkImageUrl" => '',
					"LinkAffUrl" => addslashes($merinfo['AffDefaultUrl']),
					"DataSource" =>'',
					"IsDeepLink" => 'UNKNOWN',
					"Type"       => 'link'
			);
			if (empty($link['LinkHtmlCode']))
				$link['LinkHtmlCode'] = create_link_htmlcode($link);
			if (empty($link['AffLinkId']) || empty($link['AffMerchantId']) || empty($link['LinkAffUrl']))
				continue;
			
			$links[] = $link;
			$arr_return["AffectedCount"] ++;
			
			if(sizeof($links) > 100)
			{
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
				$links = array();
			}
		}
		if (sizeof($links) > 0)
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link');
		return $arr_return;
	}
	
	function GetProgramFromAff()
	{
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
	
		$this->GetProgramByApi();
		$this->checkProgramOffline($this->info["AffId"], $check_date);
	
		echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";
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
		//var_dump($apiResponse);exit;
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
			$LogoUrl_url = "https://primedigital.api.hasoffers.com/Apiv3/json?api_key=$this->apikey&Target=Affiliate_Offer&Method=getThumbnail&ids%5B%5D=$IdInAff";
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