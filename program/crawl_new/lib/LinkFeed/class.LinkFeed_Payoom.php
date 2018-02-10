<?php
require_once 'text_parse_helper.php';
require_once INCLUDE_ROOT."wsdl/adcell_api/adcell.php";

class LinkFeed_Payoom
{
	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		
		$this->ApiToken = '6994a47c8388c3934825f6688803e89b';
		$this->NetworkId = 'eploop';
		$this->AffiliateID = 19742;
		$this->islogined = false;
		
		$this->batchProgram = date('Ymd')."_program_".$this->oLinkFeed->batchid;
	}
	
	function login($try = 6)
	{
		if ($this->islogined) {
			echo "verify succ: " . $this->info["AffLoginVerifyString"] . "\n";
			return true;
		}
	
		$this->oLinkFeed->clearHttpInfos($this->info['AffID']);//删除缓存文件，删除httpinfos[$aff_id]变量
		$request = array(
				"AffId" => $this->info["AffID"],
				"method" => 'get'
		);
		$r = $this->oLinkFeed->GetHttpResult($this->info['AffLoginUrl'], $request);
		$token = $this->oLinkFeed->ParseStringBy2Tag($r['content'], 'input type="hidden" name="_token" value="', '"');
		if (!$token) {
			$this->login(--$try);
		}
		$this->info['AffLoginPostString'] = urldecode('_token=' . $token . '&') . $this->info['AffLoginPostString'];
		$this->info["referer"] = true;
		$request = array(
				"AffId" => $this->info["AffID"],
				"method" => $this->info["AffLoginMethod"],
				"postdata" => $this->info["AffLoginPostString"],
				"no_ssl_verifyhost" => true,
				"header" => 1,
		);
	
		$arr = $this->oLinkFeed->GetHttpResult($this->info['AffLoginUrl'], $request);
		if ($arr["code"] == 0) {
			if (preg_match("/^SSL: certificate subject name .*? does not match target host name/i", $arr["error_msg"])) {
				$request["no_ssl_verifyhost"] = 1;
				$arr = $this->oLinkFeed->GetHttpResult($this->info['AffLoginUrl'], $request);
			}
		}
	
		if ($arr["code"] == 200) {
			if (stripos($arr["content"], $this->info["AffLoginVerifyString"]) !== false) {
				echo "verify succ: " . $this->info["AffLoginVerifyString"] . "\n";
				$this->islogined = true;
				return true;
			}
		}
	
		if (!$this->islogined) {
			if ($try < 0) {
				mydie("Failed to login!");
			} else {
				echo "login failed ... retry $try...\n";
				sleep(30);
				$this->login(--$try);
			}
		}
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
		$request = array("AffId" => $this->info["AffID"], "method" => "get", "postdata" => "",);
	
		//step 1, login
		$this->login();
	 	
		//step 2, get program from api and page
		$hasNextPage = true;
		$page = 1;
		
		while ($hasNextPage) {
			$strUrl = "https://{$this->NetworkId}.yeahpixel.com/api/v1/?api_token={$this->ApiToken}&method=getOffers&limit=100&page=$page";
			
			$re_try = 1;
			while ($re_try) {
				$r = $this->oLinkFeed->GetHttpResult($strUrl);
				$apiResponse = @json_decode($r['content'], true);
				
				if (isset($apiResponse['data']) && !empty($apiResponse['data'])) {
					break;
				}
				if ($re_try > 3) {
					mydie("Api is empty!");
				}
				$re_try++;
			}
			//var_dump($apiResponse);exit;
			$total = $apiResponse['total'];
			if ($total < $page * 100) {
				$hasNextPage = false;
			}
			
			foreach ($apiResponse['data'] as $prgm_info) {
				$IdInAff = $prgm_info['offer_id'];
				if (!$IdInAff)
					continue;
				
				$desc = $prgm_info['offer_description'];
				
				$SupportDeepUrl = 'UNKNOWN';
				$sup_deep = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_info['offer_description'], array('Deep-linking', '>'), '<'));
				if (strpos($sup_deep, 'Available') !== false) {
					$SupportDeepUrl = 'YES';
				}elseif(strpos($sup_deep, 'NotAvailable') !== false) {
					$SupportDeepUrl = 'NO';
				}
				
				$DetailPage = "http://platform.postback.in/affiliate/offers/$IdInAff";
				$r = $this->oLinkFeed->GetHttpResult($DetailPage,$request);
				
				$strPosition = 0;
				$LogoUrl = $this->oLinkFeed->ParseStringBy2Tag($r['content'], array('class="user-bg text-center', 'src="'), '"', $strPosition);
				$CommissionExt = $this->oLinkFeed->ParseStringBy2Tag($r['content'], array('<strong>Payout</strong>', '<p>'), '</p>', $strPosition);
				$CommissionExt = preg_replace("/\s*/",'',$CommissionExt);
				$CommissionExt = str_replace(array('\r\n','\r','\n'),'',$CommissionExt);
				
				$TargetCountryExt = $this->oLinkFeed->ParseStringBy2Tag($r['content'], array('<strong>Countries</strong>', '<p>'), '</p>', $strPosition);
				$Partnership = trim($this->oLinkFeed->ParseStringBy2Tag($r['content'], array('<div class="col-md-12">', '</span>'), '<', $strPosition));
				
				
				$arr_prgm[$IdInAff] = array(
						"SiteID" => $SiteID,
						"AccountID" => $this->account['AccountID'],
						"Name" => addslashes((trim($prgm_info['offer_name']))),
						"BatchID" => $this->oLinkFeed->batchid,
						"AffID" => $this->info["AffID"],
						"IdInAff" => $IdInAff,
						"Description" => addslashes($desc),
						"OfferPreviewUrl" => addslashes($prgm_info['offer_preview_url']),
						"OfferExpirationDate" => $prgm_info['offer_expiration_date'],
						"CurrencySymbol" => 'INR',
						"OfferPayoutType" => $prgm_info['offer_payout_type'],
						"Commission" => addslashes(trim($CommissionExt)),
						"OfferRequireApproval" => $prgm_info['offer_require_approval'],
						"OfferCategories" => addslashes(trim($prgm_info['offer_categories'])),
						"Countries" => addslashes(trim($TargetCountryExt)),
						"TrackingLink" => "https://eploop.go2pixel.org/tracking/track/?offer_id=$IdInAff&aff_id={$this->AffiliateID}",
						'LogoUrl' => addslashes($LogoUrl),
						"Partnership" => $Partnership,
				);
				$program_num ++;
				
				if (count($arr_prgm) >= 100) {
					$objProgram->updateProgram($this->info["AffID"], $arr_prgm);
					$arr_prgm = array();
				}
			}
			$page++;
		}
		if(count($arr_prgm) > 0)
		{
			$objProgram->updateProgram($this->info["AffID"], $arr_prgm);
			$arr_prgm = array();
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