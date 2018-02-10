<?php

require_once 'text_parse_helper.php';

class LinkFeed_243_Affiliate_Advantage
{
	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
	}

	function GetAllLinksFromAffByMerID($merinfo)
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		$request = array("AffId" => $this->info["AffId"], "method" => "get");
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		$url = sprintf('http://www.affiliate-advantage.co.uk/index.php?action=advert_select&CompanyID=237064&AdvertCompanyID=%s', $merinfo['IdInAff']);
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r['content'];

		$links = array();
		preg_match_all('@<td><input type="button" name="btnAdvert(\d+)" value="(.*?)" onclick="document.location=\'(.*?)\'" class="button"></td>(.*?)</tr>@ms', $content, $chapters);
		foreach ($chapters[4] as $key => $chapter)
		{
			$link = array(
					"AffId" => $this->info["AffId"],
					"AffMerchantId" => $merinfo['IdInAff'],
					"AffLinkId" => $chapters[1][$key],
					"LinkName" =>  '',
					"LinkDesc" =>  '',
					"LinkStartDate" => '0000-00-00 00:00:00',
					"LinkEndDate" => '0000-00-00 00:00:00',
					"LinkPromoType" => 'DEAL',
					"LinkHtmlCode" => '',
					"LinkCode" => '',
					"LinkOriginalUrl" => '',
					"LinkImageUrl" => '',
					"LinkAffUrl" => '',
					"DataSource" => 105,
			);
			if (preg_match('@<td>.*?<td>(.*?)</td>@ms', $chapter, $g))
				$link['LinkName'] = trim(html_entity_decode(strip_tags($g[1])));
			if($chapters[2][$key] == 'Add')
			{
				$url = 'http://www.affiliate-advantage.co.uk/' . $chapters[3][$key];
				$r = $this->oLinkFeed->GetHttpResult($url, $request);
			}
			else if ($chapters[2][$key] == 'Remove')
			{
				echo sprintf("link id %s skipped.\n", $link['AffLinkId']);
				continue;
			}
			$url = sprintf('http://www.affiliate-advantage.co.uk/index.php?action=advert_select&AdvertID=%s&AdvertCompanyID=%s&CompanyID=237064&subaction=getcode', $link['AffLinkId'], $link['AffMerchantId']);
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$content = $r['content'];
			if (preg_match('@<textarea.*?src="(.*?)".*?</textarea>@', $content, $g))
			{
				$url = $g[1];
				$r = $this->oLinkFeed->GetHttpResult($url, $request);
				$content = $r['content'];
				if (preg_match('@a href="(http://www.af-ad.co.uk/.*?)"@ms', $content, $g))
					$link['LinkAffUrl'] = $g[1];
			}
			if (empty($link['LinkAffUrl']))
				continue;
			$link['LinkHtmlCode'] = create_link_htmlcode($link);
			$links[] = $link;
		}
		if (count($links) > 0)
		{
			$arr_return["AffectedCount"] ++;
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
			echo sprintf('merchant: %s, %s link(s) found.', $merinfo['IdInAff'], $arr_return["UpdatedCount"]);
		}
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
		$request = array("AffId" => $this->info["AffId"], "method" => "get");
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		$url = "http://www.affiliate-advantage.co.uk/index.php?action=company_select&CompanyID=237064";
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r['content'];
		preg_match_all('@name="btnCompany(\d+)" value="(.*?)".*?<td>(.*?)</td>.*?window.open\(\'(.*?)\'.*?<td>(.*?)</td>@ms', $content, $chapters);
		foreach ((array)$chapters[3] as $key => $chapter)
		{
			$id = $chapters[1][$key];
			$program = array(
					"Name" => addslashes(trim($chapter)),
					"AffId" => $this->info["AffId"],
					"TargetCountryExt" => '',
					"TargetCountryInt" => '',
					"Contacts" => '',
					"IdInAff" => $id,
					"RankInAff" => 0,
					"JoinDate" => '',
					"StatusInAff" => 'Active',						//'Active','TempOffline','Offline'
					"StatusInAffRemark" => '',
					"Partnership" => 'Active',				//'NoPartnership','Active','Pending','Declined','Expired','Removed'
					"Homepage" => addslashes(trim(html_entity_decode($chapters[4][$key]))),
					"EPCDefault" => '',
					"EPC90d" => '',
					"Description" => '',
					"CommissionExt" => addslashes(trim(html_entity_decode($chapters[5][$key]))),
					"CookieTime" => 0,
					"LastUpdateTime" => date("Y-m-d H:i:s"),
			);
			$url = sprintf("http://www.affiliate-advantage.co.uk/companyinfo.php?CompanyID=%s", $id);
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$content = $r['content'];
			if (preg_match('@<body>(.*?)</body>@ms', $content, $g))
				$program['Description'] = addslashes(trim(html_entity_decode($g[1])));
			$programs[$id] = $program;
		}
		$p = $this->getProgramObj();
		$p->updateProgram($this->info["AffId"], $programs);
		$programs = array();
		$this->checkProgramOffline($this->info["AffId"], date("Y-m-d"));
	}

	private function checkProgramOffline($AffId, $check_date)
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
