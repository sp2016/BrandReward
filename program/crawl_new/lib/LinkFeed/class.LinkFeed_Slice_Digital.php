<?php
require_once 'text_parse_helper.php';
require_once INCLUDE_ROOT."wsdl/adcell_api/adcell.php";

class LinkFeed_Slice_Digital
{
	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;

		$this->API_Key = 'f5e4fd82b0feec812e86a98da43fb882490f43ee';

		$this->batchProgram = date('Ymd')."_program_".$this->oLinkFeed->batchid;
	}

	function GetProgramFromAff($accountid)
	{
		$this->account = $this->oLinkFeed->getAffAccountById($accountid);
		$this->info['AffLoginUrl'] = $this->account['LoginUrl'];
		$this->info['AffLoginPostString'] = $this->account['LoginPostString'];
		$this->info['AffLoginVerifyString'] = $this->account['LoginVerifyString'];
		$this->info['AffLoginMethod'] = $this->account['LoginMethod'];
		$this->info['AffLoginSuccUrl'] = $this->account['LoginSuccUrl'];
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";


		$this->site = $this->oLinkFeed->getAccountSiteById($accountid);
		foreach($this->site as $v){
			echo 'Site:' . $v['Name']. "\r\n";
			$this->GetProgramByApi($v['SiteID']);
		}
		echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";

		$this->CheckBatch();
	}


	function GetProgramByApi($SiteID)
	{
		echo "\tGet Program by api start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;

		$request = array(
				"AffId" => $this->info["AffID"],
				"method" => "get",
				"addheader" => array("API-Key: $this->API_Key"),
		);
		$page = 1;
		$limit = 100;
		$HasNextPage = true;
		while ($HasNextPage)
		{
			$list_url = "http://api.slice.digital/3.0/offers?page=$page&limit=$limit";
			$list_r = $this->oLinkFeed->GetHttpResult($list_url, $request);
			$list_r = json_decode($list_r['content'], true);
			//var_dump($list_r);exit;
			if ($list_r['status'] != 1)
				mydie("die: Crawl status is error");
			$count = $list_r['pagination']['total_count'];
			if (($page * $limit) >= $count)
				$HasNextPage = false;
			foreach ($list_r['offers'] as $v)
			{
				$IdInAff = $v['id'];
				$prgm_name = $v['title'];
		
				$desc = $v['description'];
				$LogoUrl = $v['logo'];
		
				$AllowTrafficSources_arr = array();
				foreach ($v['sources'] as $s)
				{
					if ($s['allowed'])
						$AllowTrafficSources_arr[] = $s['title'];
				}
				$AllowTrafficSources = implode(';', $AllowTrafficSources_arr);
				
				//CategoryExt
				$category_arr = array();
				foreach ($v['categories'] as $category)
					$category_arr[] = $category;
				$Categories = implode(',', $category_arr);
		
				//TargetCountryExt
				$country_arr = array();
				foreach ($v['countries'] as $country)
					$country_arr[] = $country;
				$Countries = implode(',', $country_arr);
		
				//CommissionExt
				$commission_arr = array();
				foreach ($v['payments'] as $commission)
				{
					if ($commission['type'] == 'fixed')
						$commission_arr[] = $commission['title'].': '.$commission['currency'].' '.$commission['revenue'];
					if ($commission['type'] == 'percent')
						$commission_arr[] = $commission['title'].': '.$commission['revenue'].'%';
				}
				$Commission = implode('|', $commission_arr);
				
				$prgm_url = "https://publisher.slice.digital/offer/$IdInAff";
		
				//allow_deeplink
				$detail_url = "http://api.slice.digital/3.0/offer/$IdInAff";
				$detail_r = $list_r = $this->oLinkFeed->GetHttpResult($detail_url, $request);
				$detail_r = json_decode($detail_r['content'], true);
				//var_dump($detail_r);exit;
				$AllowDeeplink = $detail_r['offer']['allow_deeplink'];
		
				$arr_prgm[$IdInAff] = array(
						"SiteID" => $SiteID,
						"AccountID" => $this->account['AccountID'],
						"Name" => addslashes($prgm_name),
						"BatchID" => $this->oLinkFeed->batchid,
						"AffID" => $this->info["AffID"],
						"IdInAff" => $IdInAff,
						"PreviewUrl" => addslashes($v['preview_url']),
						"Description" => addslashes($desc),
						"CR" => $v['cr'],
						"EPC" => $v['epc'],
						"LogoUrl" => addslashes($LogoUrl),
						"LogoSource" => addslashes($v['logo_source']),
						"AllowTrafficSources" => addslashes($AllowTrafficSources),
						"Categories" => addslashes($Categories),
						"Countries" => $Countries,
						"Commission" => addslashes($Commission),
						"CAP" => $v['cap'],
						"RequiredApproval" => $v['required_approval'],
						"StrictlyCountry" => $v['strictly_country'],
						"IsCpi" => $v['is_cpi'],
						"CreativesZip" => addslashes($v['creatives_zip']),
						"MacroUrl" => addslashes($v['macro_url']),
						"Link" => addslashes($v['link']),					//DefaultUrl
						"UseHttps" => $v['use_https'],
						"UseHttp" => $v['use_http'],
						"HoldPeriod" => $v['hold_period'],
						//"StatusInAff" => 'Active',						//'Active','TempOffline','Offline'
						//"Partnership" => 'Active',						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
						"AllowDeeplink" => $AllowDeeplink,
				);
		
				$program_num++;
				if(count($arr_prgm) >= 100){
					$objProgram->updateProgram($this->info["AffID"], $arr_prgm);
					$arr_prgm = array();
				}
			}
			if(count($arr_prgm)){
				$objProgram->updateProgram($this->info["AffID"], $arr_prgm);
				$arr_prgm = array();
			}
			$page++;
		}
		
		echo "\tGet Program by api end\r\n";
		if ($program_num < 10) {
			mydie("die: program count < 10, please check program.\n");
		}
		echo "\tUpdate ({$program_num}) program.\r\n";
		
	}
	
	function CheckBatch()
	{
		$objProgram = new ProgramDb();
		//$this->oLinkFeed->batchid;
		$objProgram->syncBatchToProgram($this->info["AffID"], $this->oLinkFeed->batchid);
	}
		
		
		
		
}
?>