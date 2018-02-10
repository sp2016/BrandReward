<?php

/**
 * User: rzou
 * Date: 2017/7/24
 * Time: 17:26
 */
class LinkFeed_2033_Payoom
{
	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		
		$this->ApiToken = '6994a47c8388c3934825f6688803e89b';
		$this->NetworkId = 'eploop';
		$this->AffID = 19742;
		
		$this->islogined = false;
	}
	
	function login($try = 6)
	{
		if ($this->islogined) {
			echo "verify succ: " . $this->info["AffLoginVerifyString"] . "\n";
			return true;
		}
		
		$this->oLinkFeed->clearHttpInfos($this->info['AffId']);//删除缓存文件，删除httpinfos[$aff_id]变量
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => 'get'
		);
		$r = $this->oLinkFeed->GetHttpResult($this->info['AffLoginUrl'], $request);
		$token = $this->oLinkFeed->ParseStringBy2Tag($r['content'], 'input type="hidden" name="_token" value="', '"');
		$this->info['AffLoginPostString'] = urldecode('_token=' . $token . '&') . $this->info['AffLoginPostString'];
		$this->info["referer"] = true;
		$request = array(
			"AffId" => $this->info["AffId"],
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
		$this->login();
		
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => 'get'
		);
		
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
			
			$total = $apiResponse['total'];
			if ($total < $page * 100) {
				$hasNextPage = false;
			}
			
			foreach ($apiResponse['data'] as $prgm_info) {
				$IdInAff = $prgm_info['offer_id'];
				if (!$IdInAff)
					continue;
				
				$SupportDeepUrl = 'UNKNOWN';
				$sup_deep = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_info['offer_description'], array('Deep-linking', '>'), '<'));
				if (strpos($sup_deep, 'Available') !== false) {
					$SupportDeepUrl = 'YES';
				}elseif(strpos($sup_deep, 'NotAvailable') !== false) {
					$SupportDeepUrl = 'NO';
				}
				
				$TermAndCondition = '';
				if (isset($prgm_info['offer_description']) && !empty($prgm_info['offer_description'])) {
					$TermAndCondition = addslashes(strip_tags(html_entity_decode($prgm_info['offer_description'])));
				}
				
				if ($prgm_info['offer_require_approval'] == 'no') {
					$Partnership = 'Active';
				} elseif ($prgm_info['offer_require_approval'] == 'yes') {
					$Partnership = 'NoPartnership';
				} else {
					mydie('Find new status of partnership :' . $prgm_info['offer_require_approval']);
				}
				
				$DetailPage = "http://platform.postback.in/affiliate/offers/$IdInAff";
				$r = $this->oLinkFeed->GetHttpResult($DetailPage,$request);
				
				$strPosition = 0;
				$LogoUrl = $this->oLinkFeed->ParseStringBy2Tag($r['content'], array('class="user-bg text-center', 'src="'), '"', $strPosition);
				$CommissionExt = $this->oLinkFeed->ParseStringBy2Tag($r['content'], array('<strong>Payout</strong>', '<p>'), '</p>', $strPosition);
				$CommissionExt = preg_replace("/\s*/",'',$CommissionExt);
				$CommissionExt = str_replace(array('\r\n','\r','\n'),'',$CommissionExt);
				$TargetCountryExt = $this->oLinkFeed->ParseStringBy2Tag($r['content'], array('<strong>Countries</strong>', '<p>'), '</p>', $strPosition);
				
				$arr_prgm[$IdInAff] = array(
					"AffId" => $this->info["AffId"],
					"IdInAff" => $IdInAff,
					"Name" => addslashes((trim($prgm_info['offer_name']))),
//					"Description" => $desc,
					"Homepage" => addslashes($prgm_info['offer_preview_url']),
//					"StatusInAffRemark" => addslashes($StatusInAffRemark),
					"StatusInAff" => 'Active',                        //'Active','TempOffline','Offline'
					"Partnership" => $Partnership,                        //'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
					"CommissionExt" => addslashes(trim($CommissionExt)),
					"LastUpdateTime" => date("Y-m-d H:i:s"),
					"TermAndCondition" => $TermAndCondition,
					'TargetCountryExt' => addslashes(trim($TargetCountryExt)),
					'AffDefaultUrl' => "https://eploop.go2pixel.org/tracking/track/?offer_id=$IdInAff&aff_id={$this->AffID}",
					'CategoryExt' => addslashes(trim($prgm_info['offer_categories'])),
					'LogoUrl' => addslashes($LogoUrl),
					"DetailPage" => $DetailPage,
					"SupportDeepUrl" => $SupportDeepUrl,
				);
				$program_num ++;
				
				if (count($arr_prgm) >= 100) {
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
//				$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
			}
			$page++;
		}
		
		if (count($arr_prgm)) {
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
			unset($arr_prgm);
		}
		echo "\tGet Program by api end\r\n";
		
		if ($program_num < 10) {
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