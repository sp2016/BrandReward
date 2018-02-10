<?php

require_once 'text_parse_helper.php';
require_once 'xml2array.php';


class LinkFeed_574_Shoogloo_Media
{
	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->getStatus = false;
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		$this->DataSource = '424';
		if(SID == 'bdg02'){
			$this->AffiliateID = 679;
			$this->API_Key = 'Uz9Fp9pKDV39iAjqxuR0g';
		}else{
			$this->AffiliateID = 601;
			$this->API_Key = 'bwltc87atjI6osKIJbIA';
		}
	}
	
	function GetAllLinksByAffId()
	{
		$links = array();
		$check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
		
		//step 1,login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => "get",
				"postdata" => ''
		);
		$offerDeed_url = "http://admin.shoogloo.media/affiliates/api/4/offers.asmx/OfferFeed?api_key={$this->API_Key}&affiliate_id={$this->AffiliateID}&campaign_name=&media_type_category_id=0&vertical_category_id=0&vertical_id=0&offer_status_id=0&tag_id=0&start_at_row=1&row_limit=0";
		$r = $this->oLinkFeed->GetHttpResult($offerDeed_url, $request);
		$xml = simplexml_load_string($r['content']);
		//var_dump($xml);exit;
		foreach ($xml->offers->offer as $v)
		{
			$link = array();
			$link['AffMerchantId'] = $v->offer_id . '_' . $v->offer_contract_id;
			$campaign_id = $v->campaign_id;
			if(empty($campaign_id))
				continue;
			$link_url = "https://admin.shoogloo.media/affiliates/api/2/offers.asmx/GetCampaign?api_key={$this->API_Key}&affiliate_id={$this->AffiliateID}&campaign_id={$campaign_id}";
			$re = $this->oLinkFeed->GetHttpResult($link_url, $request);
			if($re['code'] != 200)
				continue;
			$linkxml = simplexml_load_string($re['content']);
			if($linkxml->success == 'false')
				continue;
			//var_dump($linkxml);exit;
			//echo $link_url."\r\n";
			foreach ($linkxml->campaign->creatives->creative_info as $row)
			{
				$link['AffId'] = $this->info["AffId"];
				$link['LinkPromoType'] = 'link';
				$link['AffMerchantId'] = $v->offer_id . '_' . $v->offer_contract_id;
				$link['AffLinkId'] = $row->creative_id;

				$link['LinkStartDate'] = '';
				$link['LinkEndDate'] = '';
				$link['LinkName'] = $row->creative_name;

				if(stripos($link['LinkName'],'deeplink') !== false)
					$link['LinkPromoType'] = 'deeplink';
				elseif (stripos($link['LinkName'],'deal') !== false)
					$link['LinkPromoType'] = 'DEAL';
				
				if(isset($row->unique_link))
					$link['LinkAffUrl'] = $row->unique_link;
				
				if($row->creative_type->type_name == 'Image'){
					list($t1, $t2) = explode(' ', microtime());
					$timestrmp = (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
					$detailUrl = "http://admin.shoogloo.media/affiliates/Extjs.ashx?s=getcode&cid={$row->creative_id}&conid={$campaign_id}&_dc={$timestrmp}";
					$result = $this->oLinkFeed->GetHttpResult($detailUrl,$request);
					$result = json_decode($result['content'],true)['rows'];
					$link['LinkHtmlCode'] = $result[0]['content'];
					$link['LinkImageUrl'] = $this->oLinkFeed->ParseStringBy2Tag($link['LinkHtmlCode'], '<img src="', '"');
					//var_dump($result);exit;
				}
				if(empty($link['LinkHtmlCode']))
					$link['LinkHtmlCode'] = create_link_htmlcode($link);
				$link['LastUpdateTime'] = date('Y-m-d H:i:s');
				//$link['LinkDesc'] = $data['description'];
				//$link['LinkCode'] = check_linkcode_exclude_sym($link['LinkCode']);
				if(!$link['AffMerchantId'] || !$link['AffLinkId'] || !$link['LinkAffUrl'])
					continue;
				$link['DataSource'] = $this->DataSource;
				$link['Type'] = 'link';
				$link['IsDeepLink'] = 'UNKNOWN';
				$arr_return['AffectedCount']++;
				$links[] = $link;
			}
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
			echo sprintf("program:%s, %s links(s) found. \n", $link['AffMerchantId'], count($links));
			$links = array();
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
	
		echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";
	}
	
	function GetProgramByApi()
	{
		echo "\tGet Program by api start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		
		//step 1,login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => "get",
				"postdata" => ''
		);
		$offerDeed_url = "http://admin.shoogloo.media/affiliates/api/4/offers.asmx/OfferFeed?api_key={$this->API_Key}&affiliate_id={$this->AffiliateID}&campaign_name=&media_type_category_id=0&vertical_category_id=0&vertical_id=0&offer_status_id=0&tag_id=0&start_at_row=1&row_limit=0";
		
		$r = $this->oLinkFeed->GetHttpResult($offerDeed_url, $request);
		$xml = simplexml_load_string($r['content']);
		//var_dump($xml);exit;
		foreach ($xml->offers->offer as $v)
		{
			$strMerID = $v->offer_id . '_' . $v->offer_contract_id;
			if(!$strMerID) 
				continue;
			$StatusInAffRemark = $v->offer_status->status_name;
			$Partnership = '';
			$AffDefaultUrl = '';
			$SupportDeepUrl = 'UNKNOWN';
			if($StatusInAffRemark == 'Apply To Run')
				$Partnership = 'NoPartnership';
			elseif($StatusInAffRemark == 'Public')
				$Partnership = 'NoPartnership';
			elseif($StatusInAffRemark == 'Pending')
				$Partnership = 'Pending';
			elseif($StatusInAffRemark == 'Active'){
				$Partnership = 'Active';
				$detailDefaulUrl = "http://admin.shoogloo.media/affiliates/Extjs.ashx?s=creatives&cont_id={$v->campaign_id}";
				$detailDefaulUrlFull = $this->oLinkFeed->GetHttpResult($detailDefaulUrl,$request);
				$detailDefaul = json_decode($detailDefaulUrlFull['content'],true)['rows'];
				foreach ($detailDefaul as $de)
				{
					if($de['type'] == 'Link' && $de['show_destination_url'] == true){
						$SupportDeepUrl = 'YES';
						$AffDefaultUrl = $de['unique_link'];
						break;
					}
				}
				if($SupportDeepUrl == 'UNKNOWN'){
					$SupportDeepUrl = 'NO';
					$AffDefaultUrl = $detailDefaul[0]['unique_link'];
				}
				//var_dump($detailDefaul);exit;
			}
			$arr_prgm[$strMerID] = array(
					"AffId" => $this->info["AffId"],
					"IdInAff" => $strMerID,
					"Name" => addslashes($v->offer_name),
					"Homepage" => addslashes($v->preview_link),
					"CategoryExt" => addslashes($v->vertical_name),
					"Description" => addslashes($v->description),
					"TargetCountryExt" => '',
					"StatusInAffRemark" => addslashes($StatusInAffRemark),
					"StatusInAff" => 'Active',							//'Active','TempOffline','Offline'
					"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
					"LastUpdateTime" => date("Y-m-d H:i:s"),
					"CommissionExt" => addslashes($v->payout),
					"TermAndCondition" => addslashes($v->restrictions),
					"AffDefaultUrl" => addslashes($AffDefaultUrl),
					"SupportDeepUrl" => $SupportDeepUrl
			);
			if(isset($v->allowed_countries))
				$arr_prgm[$strMerID]['TargetCountryExt'] = addslashes($v->allowed_countries->country->country_code);
			
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

	function checkProgramOffline($AffId, $check_date)
	{
		$objProgram = new ProgramDb();
		$prgm = array();
		$prgm = $objProgram->getNotUpdateProgram($this->info["AffId"], $check_date);
	
		if (count($prgm) > 30) {
			mydie("die: too many offline program (" . count($prgm) . ").\n");
		} else {
			$objProgram->setProgramOffline($this->info["AffId"], $prgm);
			echo "\tSet (" . count($prgm) . ") offline program.\r\n";
		}
	}



















}
?>