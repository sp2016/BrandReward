<?php 
require_once 'text_parse_helper.php';

class LinkFeed_164_VCommission
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->getStatus = false;
		$this->NetworkId = 'vcm';
		if (SID == 'bdg01')
		{
			$this->Affiliate_ID = '57010';
			$this->apikey = '99b953d83075fdff6a8c54e5ba5e54910af827e64db54ed08e3cf3dd60a05a2a';
		}else{
			
		}
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
					"DataSource" => 242,
					"IsDeepLink" => 'UNKNOWN',
					"Type"       => 'link'
			);
				
			if (empty($link['LinkAffUrl']))
				$link['LinkAffUrl'] = "http://tracking.vcommission.com/aff_c?offer_id=$offer_id&aff_id=$this->Affiliate_ID&file_id=$link[AffLinkId]";
				
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
		
		//get all program
		$strUrl = "https://api.hasoffers.com/Apiv3/json?NetworkId="."$this->NetworkId"."&api_key=".$this->apikey."&Target=Affiliate_Offer&Method=findAll";
		$r = $this->oLinkFeed->GetHttpResult($strUrl);
		if($r['content'] === false)
		{
			mydie("Error type is can not get infomation from Api.");
		}

		$apiResponse = json_decode($r['content'], true);
		$jsonErrorCode = json_last_error();
		if($jsonErrorCode !== JSON_ERROR_NONE) {
			mydie("New approval_status appeared: API response not well-formed (json error code: $jsonErrorCode) ");
		}
		if($apiResponse['response']['status'] === 1)
		{
			echo 'API call successful'.PHP_EOL;
		}
		else
		{
			// An error occurred
			mydie("'API call failed ({$apiResponse['response']['errorMessage']})");
		}

		$result = $apiResponse['response']['data'];
		//var_dump($result);exit;
		foreach($result as $item)
		{
			$v = $item['Offer'];
			//var_dump($v);
			$IdInAff = intval(trim($v['id']));
			if(!$IdInAff)
				continue;
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
				$Partnership = 'Active';
			else if(is_null($approval_status) && $require_approval == '1')
				$Partnership = 'NoPartnership';
			else if($approval_status == 'rejected')
				$Partnership = 'Declined';
			else if($approval_status == 'pending')
				$Partnership = 'Pending';
			else
				mydie("New approval_status appeared: $approval_status");
			
			if($v['payout_type'] == 'cpa_percentage') 
				$CommissionExt = $v['percent_payout'].'%';
			else
				$CommissionExt =  empty($v['currency']) ? '$'.round($v['default_payout'],2) : $v['currency'].round($v['default_payout'],2);
			$Homepage = $v['preview_url'];
			
			if($v['allow_website_links'])
				$SupportDeepUrl = 'YES';
			else
				$SupportDeepUrl = 'NO';
			
			//get AffDefaultUrl
			$default_url = "https://api.hasoffers.com/Apiv3/json?NetworkId={$this->NetworkId}&api_key={$this->apikey}&Target=Affiliate_Offer&Method=generateTrackingLink&offer_id={$IdInAff}";
			$default_result = $this->oLinkFeed->GetHttpResult($default_url);
			$default_result = json_decode($default_result['content'], true);
			isset($default_result['response']['data']['click_url']) ? $AffDefaultUrl = addslashes($default_result['response']['data']['click_url']) : $AffDefaultUrl = '';
			
			//get TargetCountry
			$countries_url = "https://api.hasoffers.com/Apiv3/json?NetworkId={$this->NetworkId}&Target=Affiliate_Offer&Method=getTargetCountries&api_key={$this->apikey}&ids%5B%5D={$IdInAff}";
			$countries_result = $this->oLinkFeed->GetHttpResult($countries_url);
			$countries_result = json_decode($countries_result['content'], true);
			//var_dump($countries_result);exit;
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
			
			//CategoryExt
			$category_url = "https://api.hasoffers.com/Apiv3/json?NetworkId={$this->NetworkId}&Target=Affiliate_Offer&Method=getCategories&api_key={$this->apikey}&ids%5B%5D={$IdInAff}";
			$category_result = $this->oLinkFeed->GetHttpResult($category_url);
			$category_result = json_decode($category_result['content'], true);
			//var_dump($category_result);exit;
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
				$CategoryExt = '';
			}
			
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
				'SupportDeepUrl' => $SupportDeepUrl,
				'AffDefaultUrl' => $AffDefaultUrl,
				'TargetCountryExt' => $TargetCountryExt,
				'CategoryExt' => $CategoryExt
			);
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

?>