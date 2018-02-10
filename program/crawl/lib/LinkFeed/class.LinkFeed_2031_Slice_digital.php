<?php

require_once 'text_parse_helper.php';
require_once 'xml2array.php';


class LinkFeed_2031_Slice_digital
{
	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->getStatus = false;
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		if(SID == 'bdg02'){
			$this->API_Key = 'f5e4fd82b0feec812e86a98da43fb882490f43ee';
		}else{
			$this->API_Key = '';
		}
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
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
		$request = array(
				"AffId" => $this->info["AffId"],
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
				if (empty($v['creatives']))
					continue;
				if (empty($v['link']))
					continue;
				$AffMerchantId = $v['id'];
				$links = array();
				foreach ($v['creatives'] as $k => $c)
				{
					$AffLinkId = $this->oLinkFeed->ParseStringBy2Tag($k, '', '.');
					$LinkDesc = $k;
					$LinkName = $c['width'].'x'.$c['height'];
					$link = array(
							"AffId" => $this->info["AffId"],
							"AffMerchantId" => $AffMerchantId,
							"AffLinkId" => $AffLinkId,
							"LinkName" => $LinkName,
							"LinkDesc" => $LinkDesc,
							"LinkStartDate" => '0000-00-00',
							"LinkEndDate" => '0000-00-00',
							"LinkPromoType" => 'link',
							"LinkOriginalUrl" => "",
							"LinkImageUrl" => $c['file_name'],
							"LinkHtmlCode" => '',
							"LinkAffUrl" => $v['link'],
							"DataSource" => "",
							"IsDeepLink" => 'UNKNOWN',
							"Type"       => 'link'
					);
					$link['LinkHtmlCode'] = create_link_htmlcode_image($link);
					if (empty($link['AffLinkId']) || empty($link['LinkName']) || empty($link['LinkAffUrl']))
						continue;
					$arr_return['AffectedCount'] ++;
					$links[] = $link;
				}
				if (count($links) > 0)
					$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
			}
			$page++;
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
		echo "\tGet Program by Api start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		
		$request = array(
				"AffId" => $this->info["AffId"],
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
				
				//Homepage
				$final_url = $this->oLinkFeed->findFinalUrl($v['preview_url']);
				$Homepage_arr = parse_url($final_url);
				$Homepage = $Homepage_arr['scheme'].'://'.$Homepage_arr['host'];
				
				$desc = $v['description'];
				$LogoUrl = $v['logo'];
				
				//CategoryExt
				$category_arr = array();
				foreach ($v['categories'] as $category)
					$category_arr[] = $category;
				$CategoryExt = implode(',', $category_arr);
				
				//TargetCountryExt
				$country_arr = array();
				foreach ($v['countries'] as $country)
					$country_arr[] = $country;
				$TargetCountryExt = implode(',', $country_arr);
				
				//CommissionExt
				$commission_arr = array();	
				foreach ($v['payments'] as $commission)
				{
					if ($commission['type'] == 'fixed')
						$commission_arr[] = $commission['title'].': '.$commission['currency'].' '.$commission['revenue'];
					if ($commission['type'] == 'percent')
						$commission_arr[] = $commission['title'].': '.$commission['revenue'].'%';
				}
				$CommissionExt = implode('|', $commission_arr);
				
				$AffDefaultUrl = $v['link'];
				$prgm_url = "https://publisher.slice.digital/offer/$IdInAff";
				
				//SupportDeepurl
				$detail_url = "http://api.slice.digital/3.0/offer/$IdInAff";
				$detail_r = $list_r = $this->oLinkFeed->GetHttpResult($detail_url, $request);
				$detail_r = json_decode($detail_r['content'], true);
				//var_dump($detail_r);exit;
				if ($detail_r['offer']['allow_deeplink'])
					$SupportDeepurl = 'YES';
				else 
					$SupportDeepurl = 'NO';
				
				$arr_prgm[$IdInAff] = array(
						"AffId" => $this->info["AffId"],
						"IdInAff" => $IdInAff,
						"Name" => addslashes($prgm_name),
						"CategoryExt" => addslashes($CategoryExt),
						"TargetCountryExt" => $TargetCountryExt,
						"Homepage" => addslashes($Homepage),
						"Description" => addslashes($desc),
						"EPCDefault" => $v['epc'],
						"CommissionExt" => addslashes($CommissionExt),
						//"CookieTime" => addslashes($ReturnDays),
						//"StatusInAffRemark" => addslashes($StatusInAffRemark),
						"StatusInAff" => 'Active',						//'Active','TempOffline','Offline'
						"Partnership" => 'Active',						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
						"LastUpdateTime" => date("Y-m-d H:i:s"),
						"DetailPage" => $prgm_url,
						"SupportDeepurl" => $SupportDeepurl,
						"AffDefaultUrl" => $AffDefaultUrl,
						//"TermAndCondition" => addslashes($TermAndCondition),
						"LogoUrl" => addslashes($LogoUrl),
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
				$arr_prgm = array();
			}
			$page++;
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