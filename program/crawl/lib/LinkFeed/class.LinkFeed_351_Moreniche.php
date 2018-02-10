<?php 
require_once 'text_parse_helper.php';

class LinkFeed_351_Moreniche
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->getStatus = false;
		if (SID == 'bdg01')
		{
			$this->AffiliateID = '134971';
		}else{
			
		}
	}
	
	function GetAllLinksByAffId()
	{
		$check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
		
		//1.login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info,2);
		
		$arr_merchant = $this->oLinkFeed->getApprovalAffMerchant($this->info["AffId"]);
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => "get",
				"postdata" => '',
		);
		foreach ($arr_merchant as $AffMerchantId => $merinfo)
		{
			$links = array();
			$strurl = "https://app.moreniche.com/merchants/resources/".$AffMerchantId;
			$r = $this->oLinkFeed->GetHttpResult($strurl,$request);
			$r = $r['content'];
			
			$nLineStart = stripos($r, '<h5 class="widget-title">Banners</h5>');
			while (1)
			{
				$banner_url = 'https://app.moreniche.com'.trim($this->oLinkFeed->ParseStringBy2Tag($r, array('<div class="col-md-3 text-center p-h-xs ">', '<a href="'), '"', $nLineStart));
				if ($banner_url == 'https://app.moreniche.com')
					break;
				$banner_r = $this->oLinkFeed->GetHttpResult($banner_url,$request);
				$banner_r = $banner_r['content'];
				$LineStart = 0;
				while (1)
				{
					//echo memory_get_usage()."\r\n";
					$LinkHtmlCode = trim(html_entity_decode($this->oLinkFeed->ParseStringBy2Tag($banner_r, array('The HTML code snippet is', '<code>'), '<', $LineStart)));
					if (empty($LinkHtmlCode))
						break;
					$LinkAffUrl = trim($this->oLinkFeed->ParseStringBy2Tag($LinkHtmlCode, 'href="', '"'));
					$name = trim($this->oLinkFeed->ParseStringBy2Tag($LinkHtmlCode, 'alt="', '"'));
					$LinkImageUrl = trim($this->oLinkFeed->ParseStringBy2Tag($LinkHtmlCode, 'src="', '"'));
					$AffLinkId = trim($this->oLinkFeed->ParseStringBy2Tag($LinkImageUrl, 'creatives/', '/'));
					$link = array(
							"AffId" => $this->info["AffId"],
							"AffMerchantId" => $AffMerchantId,
							"AffLinkId" => $AffLinkId,
							"LinkName" =>  addslashes($name),
							"LinkDesc" =>  '',
							"LinkStartDate" => '',
							"LinkEndDate" => '',
							"LinkPromoType" => 'link',
							"LinkHtmlCode" => $LinkHtmlCode,
							"LinkCode" => '',
							"LinkOriginalUrl" => '',
							"LinkImageUrl" => $LinkImageUrl,
							"LinkAffUrl" => $LinkAffUrl,
							"DataSource" => 431,
					);
					if (empty($link['AffMerchantId']) || empty($link['LinkName']) || empty($link['AffLinkId']))
						continue;
					
					$arr_return["AffectedCount"] ++;
					$links [] = $link;
					//print_r($link);
				}
				unset($banner_r);
			}
			if (empty($links))
					continue;
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
			echo sprintf("MerchantId:%s, %s links(s) found. \n", $AffMerchantId, count($links));
			unset($r);
		}
		return $arr_return;
	}
	
	function GetProgramFromAff()
	{
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
	
		$this->GetProgramByPage();
		$this->checkProgramOffline($this->info["AffId"], $check_date);
	
		echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";
	}
	
	function GetProgramByPage()
	{
		echo "\tGet Program by page start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		
		//1.login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info,2);
		
		//get merchants
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => "get",
				"postdata" => '',
		);
		$url = 'https://app.moreniche.com/merchants';
		$re = $this->oLinkFeed->GetHttpResult($url,$request);
		$re = $re['content'];
		//print_r($re);exit;
		$result = explode('<tr>', $re);
		foreach ($result as $k => $v)
		{
			if ($k == 0 || $k == 1)
				continue;
			$startLine = 0;
			$strMerID = trim($this->oLinkFeed->ParseStringBy2Tag($v, '<a href="/merchants/info/', '"', $startLine));
			$LogoUrl = trim($this->oLinkFeed->ParseStringBy2Tag($v, '<img src="', '"', $startLine));
			$detail_url = 'https://app.moreniche.com'.trim($this->oLinkFeed->ParseStringBy2Tag($v, '<a href="', '"', $startLine));
			$strMerName = trim($this->oLinkFeed->ParseStringBy2Tag($v, array('<a href="/merchants', '>'), '<', $startLine));
			$CategoryExt = trim($this->oLinkFeed->ParseStringBy2Tag($v, '<small class="media-meta">', '<', $startLine));
			$CommissionExt = trim($this->oLinkFeed->ParseStringBy2Tag($v, '<small class="media-meta">', '<', $startLine));
			$CommissionExt = str_replace(array(' ','\n','\r','<br>'), '', $CommissionExt);
			$countrty_arr = array();
			while (1)
			{
				$countrty = trim($this->oLinkFeed->ParseStringBy2Tag($v, '<img src="/images/flags/', '.', $startLine));
				if (empty($countrty))
					break;
				$countrty_arr[] = $countrty;
			}
			$TargetCountryExt = implode(',', $countrty_arr);
			
			//get program from detailPage
			$detail_r = $this->oLinkFeed->GetHttpResult($detail_url,$request);
			$detail_r = $detail_r['content'];
			$Homepage = trim($this->oLinkFeed->ParseStringBy2Tag($detail_r, array('<div class="avatar-xxl">', '<a href="'), '"'));
			$EPCDefault = trim($this->oLinkFeed->ParseStringBy2Tag($detail_r, '<small>Avg EPC: $', '<'));
			$desc = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($detail_r, '</a></h1>', '</div>')));
			//get AffDefaultUrl
			$link_url = "https://app.moreniche.com/merchants/links/$strMerID";
			$link_r = $this->oLinkFeed->GetHttpResult($link_url,$request);
			$link_r = $link_r['content'];
			$AffDefaultUrl = trim(html_entity_decode($this->oLinkFeed->ParseStringBy2Tag($link_r, '<code>', '<')));
			
			$arr_prgm[$strMerID] = array(
					"Name" => addslashes($strMerName),
					"AffId" => $this->info["AffId"],
					"IdInAff" => $strMerID,
					"TargetCountryExt" => $TargetCountryExt,
					"EPCDefault" => $EPCDefault,
					"StatusInAff" => 'Active',                        //'Active','TempOffline','Offline'
					"Partnership" => 'Active',                        //'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
					"Description" => addslashes($desc),
					"Homepage" => $Homepage,
					//"TermAndCondition" => addslashes($TermAndCondition),
					"LastUpdateTime" => date("Y-m-d H:i:s"),
					"DetailPage" => $detail_url,
					"AffDefaultUrl" => addslashes($AffDefaultUrl),
					"CommissionExt" => addslashes($CommissionExt),
					"CategoryExt" => addslashes($CategoryExt),
					"SupportDeepUrl"=>'UNKNOWN',
					"AllowNonaffCoupon"=>'NO',
			);
			//print_r($arr_prgm[$strMerID]);
			$program_num++;
			
			if(count($arr_prgm) >= 100)
			{
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
				$arr_prgm = array();
			}
		}
		if(count($arr_prgm))
		{
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
			unset($arr_prgm);
		}
		
		echo "\tGet Program by page end\r\n";
		
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
