<?php

require_once 'text_parse_helper.php';

class LinkFeed_189_Paydot
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
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "no_ssl_verifyhost" => true,);
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		list($page, $last_page, $links) = array(1, 0, array());
		do
		{
			$url = sprintf("https://affiliates.paydot.com/links/banners/id/%s/page/%s", $merinfo['IdInAff'], $page);
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$content = $r['content'];
			if (empty($last_page))
			{
				if (preg_match_all('@<a href="/links/banners/id/\d+/page/(\d+)">@', $content, $g))
				{
					foreach ($g[1] as $p)
					{
						if ($p > $last_page)
							$last_page = $p;
					}
				}
				$link = array(
						"AffId" => $this->info["AffId"],
						"AffMerchantId" => $merinfo['IdInAff'],
						"AffLinkId" => 'd_' . $merinfo['IdInAff'],
						"LinkName" =>  sprintf('Default Link:%s', $merinfo['MerchantName']),
						"LinkDesc" =>  '',
						"LinkStartDate" => '0000-00-00 00:00:00',
						"LinkEndDate" => '0000-00-00 00:00:00',
						"LinkPromoType" => 'DEAL',
						"LinkHtmlCode" => '',
						"LinkCode" => '',
						"LinkOriginalUrl" => '',
						"LinkImageUrl" => '',
						"LinkAffUrl" => sprintf('http://track.paydot.com/hit.php?w=100175&s=%s', $merinfo['IdInAff']),
						"DataSource" => 100,
				);
				$link['LinkHtmlCode'] = create_link_htmlcode($link);
				$links[] = $link;
			}
			preg_match_all('@"dl-resource".*?value="(.*?)".*?<textarea.*?id="linkcode(\d+)".*?a href="(.*?)".*?img src="(.*?)"@ms', $content, $chapters);
			foreach ((array)$chapters[1] as $key => $chapter)
			{
				$link = array(
						"AffId" => $this->info["AffId"],
						"AffMerchantId" => $merinfo['IdInAff'],
						"AffLinkId" => $chapters[2][$key],
						"LinkName" =>  html_entity_decode($chapters[1][$key]),
						"LinkDesc" =>  '',
						"LinkStartDate" => '0000-00-00 00:00:00',
						"LinkEndDate" => '0000-00-00 00:00:00',
						"LinkPromoType" => 'DEAL',
						"LinkHtmlCode" => '',
						"LinkCode" => '',
						"LinkOriginalUrl" => '',
						"LinkImageUrl" => html_entity_decode($chapters[4][$key]),
						"LinkAffUrl" => html_entity_decode($chapters[3][$key]),
						"DataSource" => 100,
				);
				$code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc']);
				if (!empty($code))
				{
					$link['LinkPromoType'] = 'COUPON';
					$link['LinkCode'] = $code;
				}
				$link['LinkHtmlCode'] = create_link_htmlcode_image($link);
				if (empty($link['AffMerchantId']) || empty($link['AffLinkId']) || empty($link['LinkAffUrl']))
					continue;
                elseif(empty($link['LinkName'])){
                    $link['LinkPromoType'] = 'link';
                }
				$links[] = $link;
				$arr_return["AffectedCount"] ++;
			}
			if (count($links) > 0)
			{
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
				$links = array();
			}
			$page ++;
		}while ($page <= $last_page && $page < 1000);
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
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "no_ssl_verifyhost" => true,);
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);

		list($status, $page, $last_page, $programs, $ids) = array(2, 1, 0, array(), array());
		$programObj = $this->getProgramObj();
		do
		{
			$url = sprintf("https://affiliates.paydot.com/merchantoffers/mymerchants/page/%s/status/%s", $page, $status);
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$content = $r['content'];
			if (preg_match_all('@<a href="/merchantoffers/mymerchants/page/(\d+)/status/\d+">@', $content, $g))
			{
				foreach ($g[1] as $p)
				{
					if ($p > $last_page)
						$last_page = $p;
				}
			}
			preg_match_all('@<a href="/merchantoffers/view/page/\d+/status/\d+/id/(\d+)" class="bold">\s+(.*?)<@', $content, $chapters);
			foreach ((array)$chapters[2] as $key => $chapter)
			{
				$id = $chapters[1][$key];
				if (empty($id) || in_array($id, $ids))
					continue;
				$ids[] = $id;
				$program = array(
						"Name" => addslashes(trim($chapter)),
						"AffId" => $this->info["AffId"],
						//"TargetCountryExt" => '',
						//"TargetCountryInt" => '',
						"Contacts" => '',
						"IdInAff" => $id,
						"RankInAff" => 0,
						"JoinDate" => '',
						"StatusInAff" => 'Active',						//'Active','TempOffline','Offline'
						"StatusInAffRemark" => '',
						"Partnership" => 'NoPartnership',				//'NoPartnership','Active','Pending','Declined','Expired','Removed'
						"Homepage" => '',
						"EPCDefault" => '',
						"EPC90d" => '',
						"Description" => '',
						"CommissionExt" => '',
						"CookieTime" => 0,
						"AffDefaultUrl" => 'https://track.paydot.com/hit.php?w=100175&s='.$id,
						"LastUpdateTime" => date("Y-m-d H:i:s"),
				);
				$url = sprintf('https://affiliates.paydot.com/merchantoffers/view/id/%s', $id);
				$program['DetailPage'] = addslashes($url);
				$r = $this->oLinkFeed->GetHttpResult($url, $request);
				$content = $r['content'];
				if (preg_match('@<span>Send Message</span>@', $content))
					$program['Partnership'] = 'Declined';
				else
					$program['Partnership'] = 'Active';
					
				if (preg_match_all('@<img src="/public/images/flags/\w{2}.png" alt="(\w{2})" title="(\w{2})".*?/>@', $content, $g))
					$program['TargetCountryExt'] = addslashes(implode(',', $g[1]));				
				if (preg_match('@<th>Website:</th>\s+<td><a rel="nofollow" href="(.*?)"@', $content, $g))
					$program['Homepage'] = addslashes($g[1]);
				if (preg_match('@<th>Commission:</th>\s+<td>(.*?)</td>@', $content, $g))
					$program['CommissionExt'] = addslashes($g[1]);
				if (preg_match('@<th>Cookie Duration:</th>\s+<td>\s?(\d+)\s+@', $content, $g))
					$program['CookieTime'] = addslashes($g[1]);
				if (preg_match('@<th>Category:</th>\s+<td title="(.*?)"@', $content, $g))
					$program['CookieTime'] = addslashes(html_entity_decode($g[1]));
				if (preg_match('@Conditions</option>.*?<div class="inner">(.*?)<div class="clear">@ms', $content, $g))
					$program['Description'] = addslashes(trim(html_entity_decode($g[1])));
				if (preg_match('@<h3 class="subtitle">Terms \&amp; Conditions</h3>\s+<table class="marketplace-siteinfo">(.*?)</table>@ms', $content, $g))
					$program['TermAndCondition'] = addslashes(trim(html_entity_decode(strip_tags($g[1]))));
										
				$programs[$id] = $program;
			}
			$programObj->updateProgram($this->info["AffId"], $programs);
			$programs = array();
			$page ++;
		}while ($page <= $last_page && $page < 1000);

		list($page, $last_page, $programs) = array(1, 13, array());
		do
		{
			$url = sprintf("https://affiliates.paydot.com/merchantoffers/offers/cid/1/page/%s", $page);
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$content = $r['content'];
			if (preg_match_all('@<a href="/merchantoffers/offers/cid/1/page/(\d+)">@', $content, $g))
			{
				foreach ($g[1] as $p)
				{
					if ($p > $last_page)
						$last_page = $p;
				}
			}
			preg_match_all('@<a href="/merchantoffers/view/cid/1/id/(\d+)" class="bold">\s+(.*?)<@', $content, $chapters);
			foreach ((array)$chapters[2] as $key => $chapter)
			{
				$id = $chapters[1][$key];
				if (empty($id) || in_array($id, $ids))
					continue;
				$ids[] = $id;
				$program = array(
						"Name" => addslashes(trim($chapter)),
						"AffId" => $this->info["AffId"],
						//"TargetCountryExt" => '',
						//"TargetCountryInt" => '',
						"Contacts" => '',
						"IdInAff" => $id,
						"RankInAff" => 0,
						"JoinDate" => '',
						"StatusInAff" => 'Active',						//'Active','TempOffline','Offline'
						"StatusInAffRemark" => '',
						"Partnership" => 'NoPartnership',				//'NoPartnership','Active','Pending','Declined','Expired','Removed'
						"Homepage" => '',
						"EPCDefault" => '',
						"EPC90d" => '',
						"Description" => '',
						"CommissionExt" => '',
						"CookieTime" => 0,
						"AffDefaultUrl" => 'https://track.paydot.com/hit.php?w=100175&s='.$id,
						"LastUpdateTime" => date("Y-m-d H:i:s"),
				);
				$url = sprintf('https://affiliates.paydot.com/merchantoffers/view/id/%s', $id);
				$program['DetailPage'] = addslashes($url);
				$r = $this->oLinkFeed->GetHttpResult($url, $request);
				$content = $r['content'];
				if (preg_match('@<span>Send Message</span>@', $content))
					$program['Partnership'] = 'NoPartnership';
				else
					$program['Partnership'] = 'Active';
				if (preg_match_all('@<img src="/public/images/flags/\w{2}.png" alt="(\w{2})" title="(\w{2})".*?/>@', $content, $g))
					$program['TargetCountryExt'] = addslashes(implode(',', $g[1]));
				if (preg_match('@<th>Website:</th>\s+<td><a rel="nofollow" href="(.*?)"@', $content, $g))
					$program['Homepage'] = addslashes($g[1]);
				if (preg_match('@<th>Commission:</th>\s+<td>(.*?)</td>@', $content, $g))
					$program['CommissionExt'] = addslashes($g[1]);
				if (preg_match('@<th>Cookie Duration:</th>\s+<td>\s?(\d+)\s+@', $content, $g))
					$program['CookieTime'] = addslashes($g[1]);
				if (preg_match('@<th>Category:</th>\s+<td title="(.*?)"@', $content, $g))
					$program['CookieTime'] = addslashes(html_entity_decode($g[1]));
				if (preg_match('@Conditions</option>.*?<div class="inner">(.*?)<div class="clear">@ms', $content, $g))
					$program['Description'] = addslashes(trim(html_entity_decode($g[1])));
				if (preg_match('@<h3 class="subtitle">Terms \&amp; Conditions</h3>\s+<table class="marketplace-siteinfo">(.*?)</table>@ms', $content, $g))
					$program['TermAndCondition'] = addslashes(trim(html_entity_decode(strip_tags($g[1]))));
				$programs[$id] = $program;
			}
			$programObj->updateProgram($this->info["AffId"], $programs);
			$programs = array();
			$page ++;
		}while ($page <= $last_page && $page < 1000);
		$this->checkProgramOffline($this->info["AffId"], date("Y-m-d"));
	}

	private function checkProgramOffline($AffId, $check_date)
	{
		$p = $this->getProgramObj();
		$prgm = $p->getNotUpdateProgram($this->info["AffId"], $check_date);
		if(count($prgm) > 50)
			mydie("die: too many offline program (".count($prgm).").\n");
		else
		{
			$p->setProgramOffline($this->info["AffId"], $prgm);
			echo "\tSet (".count($prgm).") offline program.\r\n";
		}
	}
}
