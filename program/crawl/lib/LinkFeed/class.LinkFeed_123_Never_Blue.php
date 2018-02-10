<?php

require_once 'text_parse_helper.php';

class LinkFeed_123_Never_Blue
{
	private $api_affiliate_id = '251165';
	private $api_key = 'AO4WoYw9jFMNS89tiWtQQ';

	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->hasLogin_twice = false;
	}
	
	private function login_twice()
	{
		// this site must login twice
		if ($this->hasLogin_twice)
			return true;
		$request = array("AffId" => $this->info["AffId"], "method" => "post");
		$url = 'http://network.neverblue.com/affiliates/login.ashx';
		$request['postdata'] = 'u=info%40couponsnapshot.com&p=i4RldEWQld&tpl=';
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r['content'];
		$res = json_decode($content,true);
		if($res['success']){
			echo "login twice... ok.\n";
			$this->hasLogin_twice = true;
		}else{
			mydie('login failed');
		}
	}

	function GetAllLinksFromAffByMerID($merinfo)
	{
	    $check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		$request = array("AffId" => $this->info["AffId"], "method" => "get");
		$p = $this->getProgramObj();
		$program = $p->getProgramByAffIdAndIdInAff($this->info["AffId"], $merinfo['IdInAff']);
		if (!empty($program))
		{
			if (preg_match('@campaign_id\((\d+)\)@', $program['Remark'], $g))
			{
				$campaign_id = $g[1];
			}
		}
		if (empty($campaign_id))
		{
			echo sprintf("program: %s,no campaign_id found.\n", $merinfo['IdInAff']);
			return $arr_return;
		}

		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		//$this->login_twice();
		$url = "http://network.neverblue.com/affiliates/Extjs.ashx?s=creatives&cont_id=$campaign_id";
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r['content'];
		$data = @json_decode($content, true);
		if (empty($data) || empty($data['rows']))
		{
			echo sprintf("program: %s,empty result.\n", $merinfo['IdInAff']);
			return $arr_return;
		}
		foreach ($data['rows'] as $key => $v)
		{
			$link = array(
					"AffId" => $this->info["AffId"],
					"AffMerchantId" => $merinfo['IdInAff'],
					"AffLinkId" => $v['id'],
					"LinkName" =>  $v['name'],
					"LinkDesc" =>  '',
					"LinkStartDate" => '0000-00-00 00:00:00',
					"LinkEndDate" => '0000-00-00 00:00:00',
					"LinkPromoType" => 'DEAL',
					"LinkHtmlCode" => '',
					"LinkCode" => $v['voucher_code'],
					"LinkOriginalUrl" => '',
					"LinkImageUrl" => '',
					"LinkAffUrl" => $v['unique_link'],
					"DataSource" => 102,
			        "Type"       => 'link'
			);
			if (!empty($v['preview_src']))
				$link['LinkImageUrl'] = sprintf('http://network.neverblue.com/assets/533/creatives/%s/%s', $link['AffLinkId'], $v['preview_src']);
			$link['LinkHtmlCode'] = create_link_htmlcode_image($link);
			if (empty($link['LinkCode']))
			{
				$code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc']);
				if (!empty($code))
				{
					$link['LinkCode'] = $code;
					$link['LinkPromoType'] = 'coupon';
				}
				else
				{
					$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
				}
			}
			else
			{
				$link['LinkPromoType'] = 'coupon';
			}
			if (empty($link['AffLinkId']))
				continue;
            elseif(empty($link['LinkName'])){
                $link['LinkPromoType'] = 'link';
            }
			$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
			$links[] = $link;
			$arr_return["AffectedCount"] ++;
			if ($arr_return["AffectedCount"] % 100 == 0)
			{
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
				$links = array();
			}
		}
		if(count($links) > 0)
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link', $merinfo['IdInAff']);
		echo sprintf("program:%s, %s links(s) found. \n", $merinfo['IdInAff'], $arr_return["AffectedCount"]);
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
		list($page, $limit, $programs) = array(0, 50, array());
		do
		{
			$p = array(
					'api_key' => $this->api_key,
					'affiliate_id' => $this->api_affiliate_id,
					'campaign_name' => '',
					'media_type_category_id' => 0,
					'vertical_category_id' => 0,
					'vertical_id' => 0,
					'offer_status_id' => 0,
					'tag_id' => 0,
					'start_at_row' => $page * $limit + 1,
					'row_limit' => $limit,
			);
			$url = "http://network.neverblue.com/affiliates/api/2/offers.asmx/OfferFeed?" . http_build_query($p);
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$content = $r['content'];
			
			
			preg_match('@<row_count>(.*?)</row_count>@', $content, $row_count);			
			if(intval($row_count[1]) < 1) break;	
			
			preg_match_all('@<offer>(.*?)</offer>@ms', $content, $chapters);
			
			foreach ((array)$chapters[1] as $key => $chapter)
			{
				if (preg_match('@<offer_id>(\d+)</offer_id>@', $chapter, $g))
					$id = $g[1];
				$program = array(
						"Name" => '',
						"AffId" => $this->info["AffId"],
						"TargetCountryExt" => '',
						"TargetCountryInt" => '',
						"IdInAff" => $id,
						"RankInAff" => 0,
						"StatusInAff" => 'Active',						//'Active','TempOffline','Offline'
						"StatusInAffRemark" => '',
						"Partnership" => 'NoPartnership',				//'NoPartnership','Active','Pending','Declined','Expired','Removed'
						"Homepage" => '',
						"Description" => '',
						"CommissionExt" => '',
						"LastUpdateTime" => date("Y-m-d H:i:s"),
						'TermAndCondition' => '',
						'Remark' => '',
				);
				if (preg_match('@<campaign_id>(\d+)</campaign_id>@', $chapter, $g))
					$program['Remark'] = sprintf("campaign_id(%s)", $g[1]);
				if (preg_match('@<offer_name>(.*?)</offer_name>@', $chapter, $g))
					$program['Name'] = addslashes(trim($g[1]));
				if (preg_match('@<status_id>(\d+)</status_id>@', $chapter, $g))
				{
					switch ($g[1])
					{
						case '1': // Active
							$program['Partnership'] = 'Active';
							break;
						case '2': // Public
						case '3': // Apply To Run
							$program['Partnership'] = 'NoPartnership';
							break;
						case '4': // pending
							$program['Partnership'] = 'Pending';
							break;
						default:
							mydie(sprintf("unkown status found. (%s)\n", $g[1]));
					}
				}
				if (preg_match('@<price_format>(.*?)</price_format>@', $chapter, $g))
				{
					$program['CommissionExt'] = $g[1]. ' ';
					if (preg_match('@<payout>(.*?)</payout>@', $chapter, $g))
						$program['CommissionExt'] .= $g[1];
					$program['CommissionExt'] = addslashes($program['CommissionExt']);
				}
				if (preg_match('@<preview_link>(.*?)</preview_link>@', $chapter, $g))
					$program['Homepage'] = addslashes(html_entity_decode($g[1]));
				if (preg_match('@<description>(.*?)</description>@ms', $chapter, $g))
				{
//					if (strlen($g[1]) > 1000)
//						$g[1] = substr($g[1], 0, 1000) . '...';
					$program['Description'] = addslashes(html_entity_decode($g[1]));
				}
				if (preg_match('@<restrictions>(.*?)</restrictions>@ms', $chapter, $g))
				{
//					if (strlen($g[1]) > 1000)
//						$g[1] = substr($g[1], 0, 1000) . '...';
					$program['TermAndCondition'] = addslashes(html_entity_decode($g[1]));
				}
				if (preg_match_all('@<country_code>(.*?)</country_code>@', $chapter, $g))
					$program['TargetCountryInt'] = addslashes(html_entity_decode(implode(',', $g[1])));
				if (preg_match_all('@<country_name>(.*?)</country_name>@', $chapter, $g))
					$program['TargetCountryExt'] = addslashes(html_entity_decode(implode(',', $g[1])));
				if (empty($program['Name']))
					continue;
				$programs[$id] = $program;
			}
			$p = $this->getProgramObj();
			$p->updateProgram($this->info["AffId"], $programs);
			$programs = array();
			$page ++;
		}while ($page < 1000);
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
