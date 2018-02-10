<?php

require_once 'text_parse_helper.php';
define('MID_4', '24712');

class LinkFeed_4_LS_Lead
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
	}
	
	function SwitchToLead()
	{
		print "try to switch to Lead Tab <br>\n";
		if(isset($this->isSwitchedToLead) && $this->isSwitchedToLead) 
			return true;
		
		$request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "",);
		$strUrl = "http://cli.linksynergy.com/cli/publisher/home.php";
		$r = $this->oLinkFeed->GetHttpResult($strUrl, $request);
		$result = $r["content"];
		$str_verify = "lg_advertisers.php";
		if(stripos($result,$str_verify) !== false)
		{
			echo "current tab is Lead Advantage , no need to switch...\n";
			return true;
		}
		
		$request["method"] = "get";
		$strUrl = "http://cli.linksynergy.com/cli/publisher/common/switchMode.php?source=AffiliateNetwork&destination=LeadGen";
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];
		
		$request["referer"] = $strUrl;
		$strUrl .= "&redirect&" . rand(0,1000000000);
		$request["method"] = "post";
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];
		if(stripos($result,$str_verify) === false)
		{
			print_r($r);
			mydie("die: switch to lead tab failed.\n");
		}
		$this->isSwitchedToLead = true;
	}

	// LS webservice sometimes return 503.
	// when api server service unavailable, try another 5 times
	private function GetHttpResult_2($url, $request)
	{
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		if ($r['code'] == 503 || $r['code'] == 0)
		{
			for ($i = 0; $i < 5; $i ++)
			{
			$r = $this->oLinkFeed->GetHttpResult($strUrl);
			if ($r['code'] != 503)
				break;
			}
			}
			return $r;
	}

	function GetAllLinksFromAffByMerID($merinfo,$newonly=true)
	{
	    $check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0,"UpdatedCount" => 0,);
		$request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "",);
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		$this->SwitchToLead();
		$types = array('text', 'banner');
		foreach ($types as $type)
		{
			$limit = 100;
			$page = 0;
			do 
			{
				$links = array();
				$url = "http://cli.linksynergy.com/cli/publisher/links/lg_link_list.php?asyn=1";
				$request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "",);
				$request["postdata"] = sprintf("link_type=%s&direction=-1&orderby=startdate&start=%s&window=%s&oid=%s&mid=%s", $type, $limit * $page + 1, $limit, $merinfo['IdInAff'], '24712');
				$r = $this->GetHttpResult_2($url, $request);
				$content = $r["content"];
				$data = @json_decode($content, true);
				if (empty($data) || empty($data['total']) || empty($data['id']) || !is_array($data['id']))
					break;
				$total = $data['total'];
				$page ++;
				$params = array();
				foreach ($data['id'] as $v)
					$params[] = 'creative[]=' . $v['creative_id'] . '~' . strtolower($v['creative_type']) . '~' . MID_4 . '~' . $merinfo['IdInAff'];
				$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "",);
				$url = 'http://cli.linksynergy.com/cli/publisher/links/link_detail.php?maintab=2&' . implode('&', $params);
				$r = $this->GetHttpResult_2($url, $request);
				$content = $r['content'];

				preg_match_all('@<input type="hidden"(.*?)"images/common/question_mark.gif@ms', $content, $chapters);
				if (empty($chapters) || empty($chapters[1]) || !is_array($chapters[1]))
					continue;
				foreach ($chapters[1] as $chapter)
				{
					$link = array(
							"AffId" => $this->info["AffId"],
							"AffMerchantId" => $merinfo['IdInAff'],
							"LinkDesc" => '',
							"LinkStartDate" => '0000-00-00 00:00:00',
							"LinkEndDate" => '0000-00-00 00:00:00',
							"LinkPromoType" => 'DEAL',
							"LinkCode" => '',
							"LinkOriginalUrl" => '',
							"LinkImageUrl" => '',
							"DataSource" => 7,
					        "IsDeepLink" => 'UNKNOW',
					        "Type"       => 'link'
					);
					if (preg_match('@<textarea.*?>(.*)</textarea>@ms', $chapter, $g))
						$link['LinkHtmlCode'] = trim(html_entity_decode($g[1]));
					if (preg_match('@a href="(.*?)"@', $link['LinkHtmlCode'], $g))
						$link['LinkAffUrl'] = trim($g[1]);
					if (preg_match('@\&offerid=(\d+\.(\d+))\&@', $link['LinkAffUrl'], $g))
					{
						$lid = $g[2];
						foreach ($data['id'] as $v)
						{
							if ($v['creative_id'] == $lid)
							{
								$link['LinkName'] = urldecode($v['name']);
								$link['LinkDesc'] = urldecode($v['description']);
								if (!empty($v['startdate']))
								{
									$date = strtotime($v['startdate']);
									if ($date > 946713600)
										$link['LinkStartDate'] = date('Y-m-d 00:00:00', $date);
								}
								if (!empty($v['enddate']))
								{
									$date = strtotime($v['enddate']);
									if ($date > 946713600)
										$link['LinkEndDate'] = date('Y-m-d 23:59:59', $date);
								}
								break;
							}
						}
						$link['AffLinkId'] = trim($g[1]);
					}
					else
						continue;
					if (preg_match('@<tr>\s+<td.*?>Offer Name</td>\s+<td.*?>(.*?)</td>\s+</tr>@ms', $chapter, $g))
						$link['LinkDesc'] .= '|Offer Name:' . trim(html_entity_decode($g[1]));
					if ($type == 'banner')
					{
						if (preg_match('@border="0" src="(.*?)"@', $link['LinkHtmlCode'], $g))
							$link['LinkImageUrl'] = $g[1];
					}
					if (empty($link['AffLinkId']) || empty($link['LinkHtmlCode']) || empty($link['LinkName']))
						continue;
					$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link["LinkName"] . ' ' . $link["LinkDesc"] . ' ' . $link["LinkHtmlCode"]);
					$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
					$arr_return["AffectedCount"] ++;
					$links[] = $link;
				}
				echo sprintf("program:%s, page:%s, %s links(s) found. \n", $merinfo['IdInAff'], $page, count($links));
				if(count($links) > 0)
					$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
			}while ($page * $limit < $total && $page < 100 && !empty($total));
		}
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link', $merinfo['IdInAff']);
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
		
		$this->GetProgramFromByPage();		
		$this->checkProgramOffline($this->info["AffId"], $check_date);

		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}
	
	function GetProgramFromByPage()
	{
		echo "\tGet Program by page start\r\n";
		$program_num = 0;
		
		$working_dir = $this->oLinkFeed->getWorkingDirByAffID($this->info["AffId"]);
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "post",
			"postdata" => "",
		);
		
		//step 1,login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		$this->SwitchToLead();			

		$objProgram = new ProgramDb();
		$arr_prgm = array();
		
		echo "\tGet New Offer\r\n";
		$nNumPerPage = 100;
		$bHasNextPage = true;
		$nPageNo = 1;		
		while($bHasNextPage){
			$strUrl = "http://cli.linksynergy.com/cli/publisher/programs/lg_advertisers.php";
			$start = 1 + ($nPageNo - 1)*$nNumPerPage;
			$request["postdata"] = "asyn=1&direction=-1&orderby=begindate&start=$start&window=$nNumPerPage&tab_switch=0&maintab=1&subtab=all";
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$result = $r["content"];
			$result = json_decode($result);			
			//print_r($result);
			$total = intval($result->total);
			
			if(($start+$nNumPerPage) > $total){
				$bHasNextPage = false;
			}
			
			$program_list = $result->oid;
			
			foreach($program_list as $v){
				$strMerID = intval($v->oid);
				if(!$strMerID) continue;
				
				$strMerName = urldecode($v->offer_name);
				$desc = urldecode($v->description);				
				$SecondIdInAff = $v->mid;
				$Contacts = $v->contact;
				
				$JoinDate = $v->begindate;
				$JoinDate = date("Y-m-d H:i:s", strtotime($JoinDate));
				
				$CreateDate = $v->approve_date;
				$CreateDate = date("Y-m-d H:i:s", strtotime($CreateDate));
				
				$prgm_url = "http://cli.linksynergy.com/cli/publisher/programs/lg_offer_detail.php?oid=$strMerID&maintab=2";
				
				$arr_prgm[$strMerID] = array(
					"Name" => addslashes(trim($strMerName)),
					"AffId" => $this->info["AffId"],					
					"Contacts" => addslashes($Contacts),					
					"IdInAff" => $strMerID,					
					"JoinDate" => $JoinDate,
					"CreateDate" => $CreateDate,					
					"StatusInAff" => 'Active',						//'Active','TempOffline','Offline'					
					"Partnership" => 'NoPartnership',				//'NoPartnership','Active','Pending','Declined','Expired','Removed'					
					"Description" => addslashes($desc),
					"LastUpdateTime" => date("Y-m-d H:i:s"),
					"SecondIdInAff" => $SecondIdInAff,
					"DetailPage" => $prgm_url,
				);
				$program_num++;
				
				//print_r($arr_prgm);
				if(count($arr_prgm) >= 100){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
				
			}
			if(count($arr_prgm)){
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
				unset($arr_prgm);
			}
			$nPageNo++;
		}
		
		echo "\tGet My Offer\r\n";
		$nNumPerPage = 100;
		$bHasNextPage = true;
		$nPageNo = 1;		
		while($bHasNextPage){
			$strUrl = "http://cli.linksynergy.com/cli/publisher/programs/lg_advertisers.php";
			$start = 1 + ($nPageNo - 1)*$nNumPerPage;
			$request["postdata"] = "asyn=1&direction=-1&orderby=approve_date&start=$start&window=$nNumPerPage&tab_switch=0&maintab=2&subtab=all";
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$result = $r["content"];
			
			$result = json_decode($result);			
			//print_r($result);
			$total = intval($result->total);
			
			if(($start+$nNumPerPage) > $total){
				$bHasNextPage = false;
			}
			
			$program_list = $result->oid;
			
			foreach($program_list as $v){
				$strMerID = intval($v->oid);
				if(!$strMerID) continue;
				
				$strMerName = urldecode($v->offer_name);
				$desc = urldecode($v->description);
				$CommissionExt = urldecode($v->cpa_html);		
				$SecondIdInAff = $v->mid;
				$Contacts = $v->contact;
				
				$JoinDate = $v->approve_date;
				$JoinDate = date("Y-m-d H:i:s", strtotime($JoinDate));
				
				$CreateDate = $v->begindate;
				$CreateDate = date("Y-m-d H:i:s", strtotime($CreateDate));
				
				$prgm_url = "http://cli.linksynergy.com/cli/publisher/programs/lg_offer_detail.php?oid=$strMerID&maintab=2";
				
				$arr_prgm[$strMerID] = array(
					"Name" => addslashes(trim($strMerName)),
					"AffId" => $this->info["AffId"],					
					"Contacts" => addslashes($Contacts),					
					"IdInAff" => $strMerID,					
					"JoinDate" => $JoinDate,
					"CreateDate" => $CreateDate,					
					"StatusInAff" => 'Active',						//'Active','TempOffline','Offline'					
					"Partnership" => 'Active',						//'NoPartnership','Active','Pending','Declined','Expired','Removed'					
					"Description" => addslashes($desc),
					"LastUpdateTime" => date("Y-m-d H:i:s"),
					"SecondIdInAff" => $SecondIdInAff,
					"DetailPage" => $prgm_url,
					"CommissionExt" => addslashes($CommissionExt),
				);
				$program_num++;
				
				//print_r($arr_prgm);
				if(count($arr_prgm) >= 100){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
				
			}
			if(count($arr_prgm)){
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
				unset($arr_prgm);
			}
			$nPageNo++;
		}
		echo "\tGet Program by page end\r\n";
		
		/*if($program_num < 3){
			mydie("die: program count  ({$program_num})  < 3, please check program. closing soon.......\n");
		}*/
		
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
