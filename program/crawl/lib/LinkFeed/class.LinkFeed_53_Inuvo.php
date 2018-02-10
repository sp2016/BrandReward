<?php

require_once 'text_parse_helper.php';

class LinkFeed_53_Inuvo
{
	var $info = array(
		"ID" => "53",
		"Name" => "inuvo",
		"IsActive" => "YES",
		"ClassName" => "LinkFeed_53_Inuvo",
		"LastCheckDate" => "1970-01-01",
	);

	function __construct($aff_id,$oLinkFeed)
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
		$url = sprintf("https://platform.inuvo.com/publishers/15541/offers/%s/creatives", $merinfo['IdInAff']);
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r['content'];
		$links = array();
		preg_match_all('@/publishers/15541/creatives/(\d+)@ms', $content, $chapters);
		foreach ((array)$chapters[1] as $key => $id)
		{
			$link = array(
					"AffId" => $this->info["AffId"],
					"AffMerchantId" => $merinfo['IdInAff'],
					"AffLinkId" => $id,
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
					"DataSource" => 101,
			);
			if (empty($link['AffLinkId']))
				continue;
			$url = sprintf('https://platform.inuvo.com/publishers/15541/creatives/%s/token.html', $link['AffLinkId']);
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$content = $r['content'];
			$data = @json_decode($content, true);
			if (empty($data))
				continue;
			$link['LinkHtmlCode'] = $data['standard'];
			$link['LinkName'] = trim(html_entity_decode(strip_tags($link['LinkHtmlCode'])));
			if (preg_match('@href="(.*?)"@', $link['LinkHtmlCode'], $g))
				$link['LinkAffUrl'] = urldecode($g[1]);
			else if (preg_match('@^http@', $link['LinkHtmlCode'], $g))
			{
				$link['LinkAffUrl'] = $link['LinkName'];
				$link['LinkHtmlCode'] = create_link_htmlcode($link);
			}
			$code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc']);
			if (!empty($code))
			{
				$link['LinkCode'] = $code;
				$link['LinkPromoType'] = 'coupon';
			}
			else
				$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
			if (empty($link['AffLinkId']) )
				continue;
            elseif(empty($link['LinkName'])){
                $link['LinkPromoType'] = 'link';
            }
			$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
			$links[] = $link;
			$arr_return["AffectedCount"] ++;
		}
		echo sprintf("program:%s, %s links(s) found. \n", $merinfo['IdInAff'], count($links));
		if(count($links) > 0)
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
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
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);
		list($page, $last_page, $programs) = array(1, 0, array());
		do
		{
			$url = sprintf("https://platform.inuvo.com/publishers/15541/subscriptions/pages/%s", $page);
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$content = $r['content'];
			if (empty($last_page) && (preg_match_all('@/publishers/15541/subscriptions/pages/(\d+)@', $content, $g)))
				$last_page = max($g[1]);
			if (!preg_match_all('@dashboard_subscription_item_area" id="o(\d+)">(.*?)<div class="clear"></div>@ms', $content, $chapters))
				continue;
			foreach ($chapters[2] as $key => $chapter)
			{
				$id = $chapters[1][$key];
				if (empty($id))
					continue;
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
						"Partnership" => 'NoPartnership',				//'NoPartnership','Active','Pending','Declined','Expired','Removed'
						"Homepage" => '',
						"EPCDefault" => '',
						"EPC90d" => '',
						"Description" => '',
						"CommissionExt" => '',
						"CookieTime" => 0,
						"LastUpdateTime" => date("Y-m-d H:i:s"),
				);
				if (preg_match('@<div class="descr">(.*?)</div>@ms', $chapter, $g))
					$program['Description'] = addslashes(trim(html_entity_decode($g[1])));
				if (preg_match('@<strong>Commission:</strong>(.*?)<@', $chapter, $g))
					$program['CommissionExt'] = addslashes(trim(html_entity_decode(str_ireplace('&nbsp;', '', $g[1]))));
//				if (preg_match('@<strong>Advertiser:</strong>(.*?)<br/>@', $chapter, $g))
//					$program['Name'] = addslashes(trim(html_entity_decode(strip_tags($g[1]))));
				if (preg_match('@title="\d+: (.*?)"@', $chapter, $g))
					$program['Name'] = addslashes(trim(html_entity_decode($g[1])));
				if (preg_match('@<strong>Categories:</strong> <span title="(.*?)"@', $chapter, $g))
					$program['CategoryExt'] = addslashes(trim(html_entity_decode(strip_tags($g[1]))));
				if (preg_match('@title="Subscription Status - (.*?)" /> My Status</p>@', $chapter, $g))
				{
					switch ($g[1])
					{
						case 'approved':
							$program['Partnership'] = 'Active';
							break;
						case 'suspended':
						case 'pending':
							$program['Partnership'] = 'Pending';
							break;
						case 'denied':
							$program['Partnership'] = 'Declined';
							break;
						case 'opted out':
						default:
							$program['Partnership'] = 'NoPartnership';
							break;
					}
				}
				if (preg_match('@Ico_(\w+)" class="subscription_status".*?> Network@', $chapter, $g))
				{
					switch (strtolower($g[1]))
					{
						case 'approved':
							$program['StatusInAff'] = 'Active';
							break;
						case 'denied':
						default:
							$program['StatusInAff'] = 'Offline';
							break;
					}
				}
//				$program['AffDefaultUrl'] = addslashes(sprintf('https://platform.inuvo.com/publishers/15541/offers/%s/landing_url', $id));
				$url = sprintf("https://platform.inuvo.com/publishers/15541/offers/%s", $id);
				$r = $this->oLinkFeed->GetHttpResult($url, $request);
				$content = $r['content'];
				if (preg_match('@<h3>Approved Traffic Sources</h3>\s+<p class="step">(.*?)</p>@ms', $content, $g))
					$program['TargetCountryExt'] = addslashes(trim(html_entity_decode(strip_tags(str_ireplace('<br/>', ',', $g[1])))));
				if (preg_match('@<h3>Cookie Duration \(In Days\)</h3>\s+<p class="step">\s+(\d+)\s+</p>@ms', $content, $g))
					$program['CookieTime'] = $g[1];
				if (preg_match('@<h3>Offer Description</h3>\s+<p class="step">(.*?)</p>@ms', $content, $g))
					$program['Description'] = addslashes(trim(html_entity_decode(strip_tags($g[1]))));
				$programs[$id] = $program;
			}
			$p = $this->getProgramObj();
			$p->updateProgram($this->info["AffId"], $programs);
			$programs = array();
			$page ++;
		}while ($page <= $last_page);
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

