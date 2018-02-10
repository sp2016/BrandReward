<?php

require_once 'text_parse_helper.php';
require_once 'xml2array.php';


class LinkFeed_698_EcommerceAffiliates
{
	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->getStatus = false;
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
	}
	
	function LoginIntoAffService()
	{
		echo "Login ...\r\n";
		$url = $this->info["AffLoginUrl"];
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => "get",
				"postdata" => ""
		);
		$re = $this->oLinkFeed->GetHttpResult($url, $request);
		$token = urlencode($this->oLinkFeed->ParseStringBy2Tag($re['content'], '<meta name="csrf-token" content="', '"'));
		if (empty($token))
			mydie("Token does not exist ! Please check the login page\r\n");
		
		$this->info["AffLoginPostString"] = str_replace('{TOKEN}', $token, $this->info["AffLoginPostString"]);
		//$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info, 3);
		
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => "post",
				"postdata" => str_replace('{TOKEN}', $token, $this->info["AffLoginPostString"]),
		);
		$re = $this->oLinkFeed->GetHttpResult($url, $request);
		//print_r($re);
		if($this->info["AffLoginVerifyString"] && stripos($re["content"], $this->info["AffLoginVerifyString"]) !== false)
		{
			echo "verify succ: " . $this->info["AffLoginVerifyString"] . "\n";
			return true;
		}
		else
		{
			print_r($re);
			mydie("verify failed: " . $this->info["AffLoginVerifyString"] . "\n");
		}
		return false;
	
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
		
		//login
		$this->LoginIntoAffService();
		
		//get program by page
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => "get",
				"postdata" => ""
		);
		
		$page = 1;
		$HasNextPage = true;
		while ($HasNextPage)
		{
			echo "Crawl page $page\r\n";
			$list_url = "https://e-commerceaffiliates.com/?page=$page";
			$list_r = $this->oLinkFeed->GetHttpResult($list_url, $request);
			$list_r = $list_r['content'];
			$nLineStart = strpos($list_r, '<div class="offer-wrapper">');
			if (!$nLineStart)
				$HasNextPage = false;
			while (1)
			{
				$strMerID = trim($this->oLinkFeed->ParseStringBy2Tag($list_r, '<div id="details-', '"', $nLineStart));
				if (empty($strMerID))
					break;
				$LogoUrl = trim($this->oLinkFeed->ParseStringBy2Tag($list_r, "class='logo-img ' src='", "'", $nLineStart));
				$strMerName = trim($this->oLinkFeed->ParseStringBy2Tag($list_r, '<h4 class="eca-red">', '</h4>', $nLineStart));
				$Homepage = trim($this->oLinkFeed->ParseStringBy2Tag($list_r, '<small><a class=""><a href="', '"', $nLineStart));
				$CategoryExt = trim($this->oLinkFeed->ParseStringBy2Tag($list_r, 'muted pad-right-6"></span>', '<', $nLineStart));
				$desc = trim($this->oLinkFeed->ParseStringBy2Tag($list_r, '<p class="">', '<', $nLineStart));
				$CommissionExt = trim($this->oLinkFeed->ParseStringBy2Tag($list_r, '<div class="centered comp Aligner mh170">', '<', $nLineStart));
				$TermAndCondition = trim($this->oLinkFeed->ParseStringBy2Tag($list_r, array('<p class="skinnyer">', '<p class="skinnyer">'), '<', $nLineStart));
				$detail_url = 'https://e-commerceaffiliates.com' . trim($this->oLinkFeed->ParseStringBy2Tag($list_r, '<span class="white"><a href="', '"', $nLineStart));
				
				$detail_r = $this->oLinkFeed->GetHttpResult($detail_url, $request);
				$detail_r = $detail_r['content'];
				$LineStart = 0;
				$StatusInAffRemark = trim($this->oLinkFeed->ParseStringBy2Tag($detail_r, '<b>Status:', '<', $LineStart));
				if ($StatusInAffRemark == 'approved')
					$Partnership = 'Active';
				elseif ($StatusInAffRemark == 'pending')
					$Partnership = 'Pending';
				else 
					$Partnership = 'NoPartnership';
				$JoinDate = trim($this->oLinkFeed->ParseStringBy2Tag($detail_r, '<small class="less-muted">Applied:', '<', $LineStart));
				$CreateDate = trim($this->oLinkFeed->ParseStringBy2Tag($detail_r, '<small class="less-muted">Approved:', '<', $LineStart));
				
				$arr_prgm[$strMerID] = array(
						"Name" => addslashes($strMerName),
						"AffId" => $this->info["AffId"],
						//"Contacts" => $Contacts,
						//"TargetCountryExt" => addslashes($TargetCountryExt),
						"IdInAff" => $strMerID,
						"JoinDate" => !empty($JoinDate)?date('Y-m-d H:i:s', strtotime($JoinDate)):'',
						"CreateDate" => !empty($CreateDate)?date('Y-m-d H:i:s', strtotime($CreateDate)):'',
						"StatusInAffRemark" => $StatusInAffRemark,
						"StatusInAff" => 'Active',                        //'Active','TempOffline','Offline'
						"Partnership" => $Partnership,                        //'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
						"Description" => addslashes($desc),
						"Homepage" => addslashes($Homepage),
						"TermAndCondition" => addslashes($TermAndCondition),
						"LastUpdateTime" => date("Y-m-d H:i:s"),
						"DetailPage" => $detail_url,
						//"AffDefaultUrl" => addslashes($AffDefaultUrl),
						"CommissionExt" => addslashes($CommissionExt),
						"CategoryExt" => addslashes($CategoryExt),
						"LogoUrl" => addslashes($LogoUrl),
				);
				//print_r($arr_prgm[$strMerID]);
				$program_num++;
				if(count($arr_prgm) >= 100){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
			}
			$page++;
		}
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
			$arr_prgm = array();
		}
		//pending,invites
		/* $Partnership_arr = array('Active', 'Pending', 'NoPartnership');
		foreach ($Partnership_arr as $Partnership)
		{
			if ($Partnership == 'Active')
				$StatusInAffRemark = '';
			elseif ($StatusInAffRemark == 'Pending')
				$StatusInAffRemark = 'pending';
			elseif ($StatusInAffRemark == 'NoPartnership')
				$StatusInAffRemark = 'invites';
			
			$str_url = "https://e-commerceaffiliates.com/memberships/" . $StatusInAffRemark;
			$str_r = $this->oLinkFeed->GetHttpResult($str_url, $request);
			$str_r = $this->oLinkFeed->ParseStringBy2Tag($str_r['content'], '<tbody>', '</tbody>');
			//print_r($str_r);exit;
			$str_arr = explode('<tr>', $str_r);
			unset($str_r);
			foreach ($str_arr as $k => $str)
			{
				if ($k == 0)
					continue;
				$arr_prgm[$strMerID] = array(
						"Name" => addslashes($strMerName),
						"AffId" => $this->info["AffId"],
						"Contacts" => $Contacts,
						"TargetCountryExt" => addslashes($TargetCountryExt),
						"IdInAff" => $strMerID,
						"SecondIdInAff" => isset($SecondIdInAff)?$SecondIdInAff:'',
						"JoinDate" => $CreateDate,
						"RankInAff" => isset($v['advertiserWeight'])?$v['advertiserWeight']:'',
						//"StatusInAffRemark" => $StatusInAffRemark,
						"StatusInAff" => $StatusInAff,                        //'Active','TempOffline','Offline'
						"Partnership" => $Partnership,                        //'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
						"Description" => addslashes($desc),
						"Homepage" => isset($v['url'])?addslashes($v['url']):'',
						"TermAndCondition" => isset($v['terms'])?addslashes($v['terms']):'',
						"LastUpdateTime" => date("Y-m-d H:i:s"),
						"DetailPage" => $detail_url,
						"AffDefaultUrl" => addslashes($AffDefaultUrl),
						"CommissionExt" => addslashes($CommissionExt),
						"CategoryExt" => addslashes(trim($CategoryExt)),
						"LogoUrl" => isset($v['logo'])?addslashes($v['logo']):'',
				);	
			}
		} */
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