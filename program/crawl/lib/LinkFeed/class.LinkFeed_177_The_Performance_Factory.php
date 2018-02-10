<?php

require_once 'text_parse_helper.php';

class LinkFeed_177_The_Performance_Factory
{
	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->full_crawl = isset($oLinkFeed->full_crawl) ? $oLinkFeed->full_crawl : false;
		
		$this->api_key = 'AFFMBmMwxGz5z2NOmvMmwjsYo7ocd4';
		$this->api_key_v3 = 'd325a49604dc0bc22b5607f9ffd6eded86c824cef0bc8a8b6adc335856f92471';
	}

	function GetAllLinksFromAffByMerID($merinfo)
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		$request = array("AffId" => $this->info["AffId"], "method" => "get");
		return $arr_return;
	}

	function getCouponFeed()
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		$request = array("AffId" => $this->info["AffId"], "method" => "get");
		return $arr_return;
	}

	private function getProgramObj()
	{
		if (!empty($this->objProgram))
			return $this->objProgram;
		$this->objProgram = new ProgramDb();
		return $this->objProgram;
	}
	
	function GetProgramFromAff()
	{
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
		$this->GetProgramByApi();
		$this->checkProgramOffline($check_date);
		
		echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";
	}
	
	function GetProgramByApi()
	{
		echo "\tGet Program by api start\r\n";
		
		$objProgram = $this->getProgramObj();
		$arr_prgm = array();
		$program_num = 0;
		
		$request = array("AffId" => $this->info["AffId"], "method" => "get");
		
		$retry = 1;
		
		while ($retry) {
			$url = sprintf('http://partners.offerfactory.com.au/offers/offers.json?api_key=%s', $this->api_key);
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$content = $r['content'];
			$data = @json_decode($content, true);
			if (isset($data['data']['offers']) && !empty($data['data']['offers'])) {
				break;
			}
			if ($retry > 3) {
				mydie('wrong format of api result.');
			}
			$retry ++;
		}
		
		foreach ($data['data']['offers'] as $v)
		{
			$id = $v['id'];
			if (empty($id))
				continue;
			
			$arr_prgm[$id] = array(
				"Name" => addslashes(trim(html_entity_decode($v['name']))),
				"AffId" => $this->info["AffId"],
				"TargetCountryExt" => addslashes(trim($v['countries_short'])),
				//"TargetCountryInt" => addslashes(str_replace(' ', '', $v['countries_short'])),				
				"CategoryExt" => addslashes(trim($v['categories'])),
				"IdInAff" => $id,
				//"RankInAff" => 0,
				//"JoinDate" => '',
				//"StatusInAff" => 'Active',				//'Active','TempOffline','Offline'
				//"StatusInAffRemark" => '',
				//"Partnership" => 'Active',				//'NoPartnership','Active','Pending','Declined','Expired','Removed'
				"Homepage" => addslashes(trim(html_entity_decode($v['preview_url']))),
				//"EPCDefault" => '',
				//"EPC90d" => '',
				"Description" => addslashes(trim(html_entity_decode($v['description']))),
				"CommissionExt" => addslashes(sprintf('%s %s', $v['currency'], $v['payout'])),
				//"CookieTime" => 0,
				"AffDefaultUrl" => addslashes(trim(html_entity_decode($v['tracking_url']))),
				"LastUpdateTime" => date("Y-m-d H:i:s"),
			);			
			$program_num ++;
			
		}
		//print_r($arr_prgm);
		echo "get api 1 $program_num\r\n";

		//get partnership
		$program_num = 0;
		$request = array("AffId" => $this->info["AffId"], "method" => "get");
		$url = sprintf('https://offerfactory.api.hasoffers.com/Apiv3/json?api_key=%s&Target=Affiliate_Offer&Method=findAll', $this->api_key_v3);
		
		$retry = 1;
		while ($retry) {
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$content = $r['content'];
			$data = @json_decode($content, true);
			if (isset($data['response']['data']) && !empty($data['response']['data'])) {
				break;
			}
			if ($retry > 3) {
				mydie('wrong format of api result.');
			}
			$retry ++;
		}
		
		foreach ($data['response']['data'] as $v)
		{
			$v = current($v);
			$id = $v['id'];
			if (empty($id))
				continue;
				
			switch($v['status']){
				case 'active':
					$StatusInAff = 'Active';
					break;
				
				default:
					mydie("die: new StatusInAff [{$v['status']}].\n");
					break;
			}
			
			switch($v['approval_status']){
				case 'approved':
					$Partnership = 'Active';
					break;
				case 'pending':
					$Partnership = 'Pending';
					break;
				case 'rejected':
					$Partnership = 'Declined';
					break;
				case '':
					$Partnership = 'NoPartnership';
					break;					
				default:					
					mydie("die: new partnership [{$v['approval_status']}].\n");
					break;
			}
			if (!isset($arr_prgm[$id])) continue;
			$arr_prgm[$id] += array(
				"AffId" => $this->info["AffId"],
				"IdInAff" => $id,
				//"Name" => addslashes(trim(html_entity_decode($v['name']))),
				//"Description" => addslashes(trim(html_entity_decode($v['description']))),
				"TermAndCondition" =>  addslashes(trim($v['terms_and_conditions'])),
				//"Homepage" => addslashes(trim(html_entity_decode($v['preview_url']))),
				//"CommissionExt" => addslashes(sprintf('%s: %s', $v['currency'], $v['default_payout'])),	
				"StatusInAffRemark" => addslashes($v['status']),
				"StatusInAff" => addslashes($StatusInAff),				//'Active','TempOffline','Offline'
				"Partnership" => addslashes($Partnership),			//'NoPartnership','Active','Pending','Declined','Expired','Removed'			
				"LastUpdateTime" => date("Y-m-d H:i:s"),
			);
			
			$program_num ++;
		}
		echo "get api 2 $program_num\r\n";
		
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
			$arr_prgm = array();
		}
		
		echo "\tGet Program by Api end\r\n";
		if($program_num < 10){
			mydie("die: program count < 10, please check program.\n");
		}
		echo "\tUpdate ({$program_num}) program.\r\n";
	}

	private function checkProgramOffline($check_date)
	{
		$p = $this->getProgramObj();
		$prgm = $p->getNotUpdateProgram($this->info["AffId"], $check_date);
		if(count($prgm) > 30)
			mydie("die: too many offline program (".count($prgm).").\n");
		else
		{
			$p->setProgramOffline($this->info["AffId"], $prgm);
			echo "\tSet (".count($prgm).") offline program.\r\n";
		}
	}
}
