<?php
/**
 * User: rzou
 * Date: 2017/8/11
 * Time: 10:08
 */
class LinkFeed_Affiliate_Future_US
{
	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		
		$this->batchProgram = date('Ymd') . "_program_" . $this->oLinkFeed->batchid;
		
	}
	
	function LoginIntoAffService()
	{
		//get para __VIEWSTATE and then process default login
		if(!isset($this->info["AffLoginPostStringOrig"])) $this->info["AffLoginPostStringOrig"] = $this->info["AffLoginPostString"];
		$request = array("AffId" => $this->info["AffID"], "method" => "post", "postdata" => "",);
		if(isset($this->info["loginUrl"])){
			$this->info["AffLoginUrl"] = $this->info["loginUrl"];
		}
		$strUrl = $this->info["AffLoginUrl"];
		
		echo "login url:".$strUrl."\r\n";
		
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];
		
		$this->info["referer"] = $strUrl;
		
		if(isset($this->info["loginUrl"])){
			if (!preg_match('@id="__VIEWSTATE" value="(.*?)".*?id="__VIEWSTATEGENERATOR" value="(.*?)"@ms', $result, $g))
				mydie("die: login for LinkFeed_20_AFFF_US failed, param not found\n");
			$this->info["AffLoginPostString"] = sprintf('__VIEWSTATE=%s&__VIEWSTATEGENERATOR=%s&%s', urlencode($g[1]), urlencode($g[2]), $this->info["AffLoginPostStringOrig"]);
		}else{
			if (!preg_match('@id="__VIEWSTATE" value="(.*?)".*?id="__VIEWSTATEGENERATOR" value="(.*?)".*?id="__EVENTVALIDATION" value="(.*?)"@ms', $result, $g))
				mydie("die: login for LinkFeed_20_AFFF_US failed, param not found\n");
			$this->info["AffLoginPostString"] = sprintf('__VIEWSTATE=%s&__VIEWSTATEGENERATOR=%s&__EVENTVALIDATION=%s&%s', urlencode($g[1]), urlencode($g[2]), urlencode($g[3]), $this->info["AffLoginPostStringOrig"]);
		}
		
		if (preg_match('@id="__EVENTTARGET" value="(.*?)"@ms', $result, $g))
			$this->info['AffLoginPostString'] .= '&__EVENTTARGET=' . urlencode($g[1]);
		if (preg_match('@id="__EVENTARGUMENT" value="(.*?)"@ms', $result, $g))
			$this->info['AffLoginPostString'] .= '&__EVENTARGUMENT=' . urlencode($g[1]);
		if (preg_match('@id="topinclude$txtUsername" value="(.*?)"@ms', $result, $g))
			$this->info['AffLoginPostString'] .= '&topinclude$txtUsername=' . urlencode($g[1]);
		if (preg_match('@id="topinclude$txtPassword" value="(.*?)"@ms', $result, $g))
			$this->info['AffLoginPostString'] .= '&topinclude$txtPassword=' . urlencode($g[1]);
		
		$this->oLinkFeed->LoginIntoAffService($this->info["AffID"],$this->info,6,true,true,false);
		return "stophere";
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
		foreach ($this->site as $v) {
			echo 'Site:' . $v['Name'] . "\r\n";
			$this->GetProgramByPage($v['SiteID'], $v['SiteIdInAff']);
		}
		echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";
		
		$this->CheckBatch();
	}
	
	function GetProgramByPage($SiteID, $SiteIdInAff)
	{
		echo "\tGet Program by Page start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		$use_true_file_name = true;
		$request = array("AffId" => $this->info["AffID"], "method" => "get", "postdata" => "",);
		
		//step 1,login
		$this->LoginIntoAffService();
		
		$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "list_" . date("Ym") . "_data.dat", $this->batchProgram, $use_true_file_name);
		if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
			$results = $this->GetHttpResultMoreTry("http://affiliates.affiliatefuture.com/myprogrammes/default.aspx", $request);
			$this->oLinkFeed->fileCachePut($cache_file, $results);
		}
		$result = file_get_contents($cache_file);
		$result = preg_replace('@>\s+<@', '><', $result);
		
		$nLineStart = stripos($result, '<td>Merchant</td><td>Programme Name</td>');
		if (!$nLineStart) {
			mydie('Can\'t get data, please check the Page !');
		}
		
		while ($nLineStart >= 0){
			$nLineStart = stripos($result, '<tr style="color:Black;', $nLineStart);
			if ($nLineStart === false)
				break;
			
			$StatusInAffRemark = trim($this->oLinkFeed->ParseStringBy2Tag($result, 'background-color:', ';', $nLineStart));
			if($StatusInAffRemark == "White"){
				$StatusInAff = "Active";
			}elseif($StatusInAffRemark == "Red"){
				$StatusInAff = "Offline";
			}else{
				break;
			}
			
			$MerchantName = trim($this->oLinkFeed->ParseStringBy2Tag($result, 'NAME="Hyperlink1">', "</span>", $nLineStart));
			if ($MerchantName === false)
				continue;
			
			$ProgramID = intval($this->oLinkFeed->ParseStringBy2Tag($result, 'NAME="Hyperlink2" href="MerchantProgramme.aspx?id=', '"', $nLineStart));
			if ($ProgramID === false)
				continue;
			
			$Name = trim($this->oLinkFeed->ParseStringBy2Tag($result, '>', "</a>", $nLineStart));
			
			$lnkClicks = 'http://affiliates.affiliatefuture.com/myprogrammes/' . html_entity_decode(trim($this->oLinkFeed->ParseStringBy2Tag($result, array('lnkClicks"','href="'), '"', $nLineStart)));
			$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "CommssionPage_" . date("Ym") . "_{$ProgramID}.dat", $this->batchProgram, $use_true_file_name);
			if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
				$commssionPage = $this->GetHttpResultMoreTry($lnkClicks, $request);
				if (!$commssionPage) {
					mydie("Can't get commssion page!");
				} else {
					$this->oLinkFeed->fileCachePut($cache_file, $commssionPage);
				}
			}
			$commssionPage = file_get_contents($cache_file);
			$commssionPage = preg_replace('@>\s+<@', '><', $commssionPage);
			$CommssionDetail = '<table>' . $this->oLinkFeed->ParseStringBy2Tag($commssionPage, array('id="DataGrid1"','>'), '</table') . '</table>';
			
			$CommissionClicks = trim($this->oLinkFeed->ParseStringBy2Tag($result, '>', '</', $nLineStart));
			$CommissionLeads = trim($this->oLinkFeed->ParseStringBy2Tag($result, 'lblNumLeads">', '</', $nLineStart));
			$CommissionSales = trim($this->oLinkFeed->ParseStringBy2Tag($result, 'lblNumSales">', '</', $nLineStart));
			$Revenue = trim($this->oLinkFeed->ParseStringBy2Tag($result, 'lblTotalValue">', '</', $nLineStart));
			$Revenue = preg_replace('@\s+@','',$Revenue);
			$ClickRate = trim($this->oLinkFeed->ParseStringBy2Tag($result, 'lblClickThru">', '</', $nLineStart));
			$ClickRate = preg_replace('@\s+@','',$ClickRate);
			$LeadRate = trim($this->oLinkFeed->ParseStringBy2Tag($result, 'lblLead">', '</', $nLineStart));
			$LeadRate = preg_replace('@\s+@','',$LeadRate);
			$SalesRate = trim($this->oLinkFeed->ParseStringBy2Tag($result, 'valign="top">', '</', $nLineStart));
			
			$detailPageUrl = sprintf('http://affiliates.affiliatefuture.com/myprogrammes/MerchantProgramme.aspx?id=%s',$ProgramID);
			$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffID"], "detailPage_" . date("Ym") . "_{$ProgramID}.dat", $this->batchProgram, $use_true_file_name);
			if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
				$detailPage = $this->GetHttpResultMoreTry($detailPageUrl, $request);
				if (!$detailPage) {
					mydie("Can't get commssion page!");
				} else {
					$this->oLinkFeed->fileCachePut($cache_file, $detailPage);
				}
			}
			$detailPage = file_get_contents($cache_file);
			$Homepage = trim($this->oLinkFeed->ParseStringBy2Tag($detailPage, array('>Site<', 'href="'), '"'));
			$Description = $this->oLinkFeed->ParseStringBy2Tag($detailPage, 'id="MerchantDescription">', '</p>');
			$PPCPolicy = $this->oLinkFeed->ParseStringBy2Tag($detailPage, 'PPC Policy:', '</span>');
			$Offer = strip_tags($this->oLinkFeed->ParseStringBy2Tag($detailPage, 'id="Offer">', 'id="'));
			
			$arr_prgm[$ProgramID] = array(
				"SiteID" => $SiteID,
				"AccountID" => $this->account['AccountID'],
				'AffID' => $this->info['AffID'],
				'IdInAff' => $ProgramID,
				'BatchID' => $this->oLinkFeed->batchid,
				'StatusInAff' => $StatusInAff,
				'MerchantName' => addslashes($MerchantName),
				'Name' => addslashes($Name),
				'CommissionClicks' => addslashes($CommissionClicks),
				'CommissionLeads' => addslashes($CommissionLeads),
				'CommissionSales' => addslashes($CommissionSales),
				'CommssionDetail' => addslashes($CommssionDetail),
				'Revenue' => addslashes($Revenue),
				'ClickRate' => addslashes($ClickRate),
				'LeadRate' => addslashes($LeadRate),
				'SalesRate' => addslashes($SalesRate),
				'Homepage' => addslashes($Homepage),
				'Description' => addslashes($Description),
				'PPCPolicy' => addslashes($PPCPolicy),
				'Offer' => addslashes($Offer)
			);
			$program_num ++;
			
			echo $program_num . "\t";
			
			if (count($arr_prgm) > 0) {
				$objProgram->updateProgram($this->info["AffID"], $arr_prgm);
				$arr_prgm = array();
			}
		}
		
		if (count($arr_prgm) > 0) {
			$objProgram->updateProgram($this->info["AffID"], $arr_prgm);
			unset($arr_prgm);
		}
		
		echo "\tGet Program by page end\r\n";
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
	
	function GetHttpResultMoreTry($url, $request, $checkstring = '', $retry = 3)
	{
		$result = '';
		while ($retry) {
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			if ($checkstring) {
				if (strpos($r['content'], $checkstring) !== false) {
					return $result = $r['content'];
				}
			} elseif (!empty($r['content'])) {
				return $result = $r['content'];
			}
			$retry--;
		}
		return $result;
	}

}


?>