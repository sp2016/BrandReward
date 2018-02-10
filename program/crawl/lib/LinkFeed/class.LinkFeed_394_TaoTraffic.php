<?php

class LinkFeed_394_TaoTraffic
{
	var $API_KEY_394 = '1f24a8ecf1988b71f2a6d0537c35537111f9a9b9d62beb97d7c8b5c8ef5b447c';
	var $NETWORK_ID_394 = 'silk';

	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
	}

	function GetAllLinksFromAffByMerID($merinfo)
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "addheader" => array(sprintf('authorization:%s', $this->CJ_API_KEY)), );
		return $arr_return;
	}

	function getCouponFeed()
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
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

	function GetProgramByApi()
	{
		echo "\tGet Program by api start\r\n";
		$objProgram = new ProgramDb();
		$request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "",);
		list($limit, $page, $pages, $count) = array(100, 1, 0, 0);
		do
		{
			$programs = array();
			$url = sprintf("https://api.hasoffers.com/Apiv3/json?NetworkId=%s&Target=Affiliate_Offer&Method=findAll&api_key=%s&limit=%s&page=%s",
					$this->NETWORK_ID_394, $this->API_KEY_394, $limit, $page);
			$page ++;
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$content = $r['content'];
			$data = @json_decode($content, true);
			if (!is_array($data) || empty($data) || empty($data['response']['httpStatus']) || $data['response']['httpStatus'] != 200)
			{
				$content = substr($content, 0, 100);
				mydie("$content unrecongized api return.");
			}
			if (empty($pages))
			{
				$pages = $data['response']['data']['pageCount'];
			}
			if (empty($data['response']['data']['data']))
			{
				echo "empty data\n";
				continue;
			}
			foreach ($data['response']['data']['data'] as $v)
			{
				$v = $v['Offer'];
				$id = $v['id'];
				$programs[$id] = array(
						"Name" => addslashes(trim($v['name'])),
						"IdInAff" => $v['id'],
						"AffId" => $this->info["AffId"],
						"Description" => addslashes($v['description']),
						"SupportDeepUrl" => empty($v['allow_website_links']) ? 'NO' : 'YES',
						"CommissionExt" => addslashes(sprintf('percent_payout:%s, conversion_cap:%s, currency:%s, payout_cap:%s, payout_type:%s', $v['percent_payout'], $v['conversion_cap'], $v['currency'], $v['payout_cap'], $v['payout_type'])),
						"Homepage" => addslashes($v['preview_url']),
						"StatusInAff" => $v['status'] == 'active' ? 'Active' : 'Offline',
						"StatusInAffRemark" => addslashes($v['status']),
						"Partnership" => 'Active',		//'NoPartnership','Active','Pending','Declined','Expired','Removed'
						"LastUpdateTime" => date("Y-m-d H:i:s"),
						"MobileFriendly" => 'UNKNOWN',
						"TermAndCondition" => addslashes($v['terms_and_conditions']),
						"TargetCountryInt" => '',
				);
				if ($v['is_expired'])
				{
					$programs[$id]['Partnership'] = 'Expired';
				}
				$count ++;
			}
			$url = sprintf('https://api.hasoffers.com/Apiv3/json?NetworkId=%s&Target=Affiliate_Offer&Method=getTargetCountries&api_key=%s&ids%%5B%%5D=%s',
					$this->NETWORK_ID_394, $this->API_KEY_394, implode('&ids%5B%5D=', array_keys($programs)));
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$content = $r['content'];
			$data = @json_decode($content, true);
			if (is_array($data) && !empty($data) && !empty($data['response']['httpStatus']) && $data['response']['httpStatus'] == 200)
			{
				foreach ($data['response']['data'] as $v)
				{
					if (!empty($v['countries']))
					{
						$id = $v['offer_id'];
						if (!empty($programs[$id]))
						{
							$programs[$id]['TargetCountryInt'] = addslashes(implode(',', array_keys($v['countries'])));
						}
					}
				}
			}
			$objProgram->updateProgram($this->info["AffId"], $programs);
		}while($page <= $pages);
		echo "\tUpdate ({$count}) program.\r\n";
	}

	function checkProgramOffline($AffId, $check_date){
		$objProgram = new ProgramDb();
		$prgm = array();
		$prgm = $objProgram->getNotUpdateProgram($this->info["AffId"], $check_date);
		if(count($prgm) > 6){
			mydie("die: too many offline program (".count($prgm).").\n");
		}else{
			$objProgram->setProgramOffline($this->info["AffId"], $prgm);
			echo "\tSet (".count($prgm).") offline program.\r\n";
		}
	}
}
