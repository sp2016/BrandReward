<?php

require_once 'text_parse_helper.php';

class LinkFeed_62_Commission_Monster
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->api_key = "AFF8GMXcZ5GZRmn3rEsgV3Np2tbSgY";

		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
	}

	function getCouponFeed()
	{
		$affid_inaff = 1014;
		$arr_return = array("AffectedCount" => 0,"UpdatedCount" => 0,"Detail" => array());
		$request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "");
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);		
		$url = "http://offers.commissionmonster.com/custom_pages/show/6";
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r["content"];
		preg_match_all('@<tr>(.*?)</tr>@ms', $content, $chapters);
		if (empty($chapters) || empty($chapters[0]) || !is_array($chapters[0]))
			return $arr_return;
		$links = array();
		foreach ($chapters[0] as $chapter)
		{
			preg_match_all('@<td>(.*?)</td>@ms', $chapter, $v);
			if (empty($v) || empty($v[1]) || !is_array($v[1]) || count($v[1]) < 5)
				continue;
			$link = array(
					"AffId" => $this->info["AffId"],
					"AffMerchantId" => '',
					"LinkDesc" => '',
					"LinkStartDate" => parse_time_str($v[1][2], null, false),
					"LinkEndDate" => parse_time_str($v[1][3], null, true),
					"LinkPromoType" => 'COUPON',
					"LinkOriginalUrl" => "",
					"LinkHtmlCode" => '',
					"AffLinkId" => '',
					"LinkName" => $v[1][4],
					"LinkCode" => $v[1][1],
					"LinkImageUrl" => "",
					"LinkAffUrl" => "",
					"DataSource" => 81,
			);
			if (preg_match('@href=".*?/view/(\d+)"@', $v[1][0], $g))
			{
				$link['AffMerchantId'] = $g[1];
				$link['LinkAffUrl'] = sprintf('http://tracking.cmjump.com.au/aff_c?offer_id=%s&aff_id=1014', $link['AffMerchantId']);
			}
			if (empty($link['LinkCode']) || empty($link['LinkName']) || empty($link['LinkAffUrl']) || empty($link['AffMerchantId']))
				continue;
			$link['AffLinkId'] = md5($link['AffMerchantId'] . '_' . $link['LinkCode']);
			$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
			$arr_return['AffectedCount'] ++;
			$links[] = $link;
			if ($arr_return['AffectedCount'] % 100 == 0)
			{
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
				$links = array();
			}
		}
		echo sprintf("get coupon by page...%s link(s) found.\n", $arr_return['AffectedCount']);
		if (count($links) > 0)
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		return $arr_return;
	}

	function GetAllLinksFromAffByMerID($merinfo)
	{
		$arr_return = array("AffectedCount" => 0,"UpdatedCount" => 0,"Detail" => array());
		$request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "");
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		$limit = 100;
		$page = 1;
		$count = 0;
		do 
		{
			$url = sprintf('http://offers.commissionmonster.com/offers/ajax_creative?offer_id=%s&page=%s&limit=%s', $merinfo['IdInAff'], $page, $limit);
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$content = $r["content"];
			$data = @json_decode($content, true);
			if (empty($data) || empty($data['count']) || empty($data['data']) || !is_array($data['data']))
				break;
			$count = intval($data['count']);
			$links = array();
			foreach ($data['data'] as $key => $val)
			{
				if (empty($val['OfferFile']) || !is_array($val['OfferFile']))
					continue;
				$v = $val['OfferFile'];
				if ($v['status'] != 'active' || $v['type'] == 'xml feed')
					continue;
				$link = array(
						"AffId" => $this->info["AffId"],
						"AffMerchantId" => $merinfo['IdInAff'],
						"LinkDesc" => '',
						"LinkStartDate" => '0000-00-00 00:00:00',
						"LinkEndDate" => '0000-00-00 00:00:00',
						"LinkPromoType" => 'N/A',
						"LinkOriginalUrl" => "",
						"LinkHtmlCode" => '',
						"AffLinkId" => $v['id'],
						"LinkName" => $v['display'],
						"LinkImageUrl" => $v['url'],
						"LinkAffUrl" => "",
						"DataSource" => 81,
				);
				if (empty($link['LinkName']))
					$link['LinkName'] = $v['filename'];
				if (!empty($v['modified']) && $v['modified'] != '0000-00-00 00:00:00')
					$date = $v['modified'];
				else
					$date = $v['created'];
				$link['LinkStartDate'] = parse_time_str($date, 'Y-m-d H:i:s', false);
				$link['LinkAffUrl'] = sprintf('http://tracking.cmjump.com.au/aff_c?offer_id=%s&aff_id=1014', $link['AffMerchantId']);
				$link['LinkHtmlCode'] = create_link_htmlcode_image($link);
				$code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc']);
				if (!empty($code))
				{
					$link['LinkCode'] = $code;
					$link['LinkPromoType'] = 'COUPON';
				}
				else
					$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
				if (empty($link['AffLinkId']) || empty($link['LinkName']) || empty($link['LinkAffUrl']) || empty($link['AffMerchantId']))
					continue;
				$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
				$links[] = $link;
				$arr_return['AffectedCount'] ++;
			}
			echo sprintf("program: %s, page:%s, %s link(s) found.\n", $merinfo['IdInAff'], $page, count($links));
			if (count($links) > 0)
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
			$page ++;
		}while ($page < 100 && $count >= $limit);
		return $arr_return;
	}

	function getMessage()
	{
		$messages = array();
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", );
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);
		$url = 'http://offers.commissionmonster.com/alerts';
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
	echo	$content = $r['content'];
		if (preg_match('@<tbody id="pagingBody">(.*?)</tbody>@ms', $content, $g))
			$content = $g[1];
		else
			return 'parse html error.';
		preg_match_all('@<tr.*?>(.*?)</tr>@ms', $content, $chapters);
		if (empty($chapters) || !is_array($chapters) || empty($chapters[1]) || !is_array($chapters[1]))
			return 'no message found.';
		foreach ($chapters[1] as $chapter)
		{
			preg_match_all('@<td.*?>(.*?)</td>@ms', $chapter, $tds);
			if (empty($tds) || empty($tds[1]) || !is_array($tds[1]) || count($tds[1]) < 4)
				continue;
			$data = array(
					'affid' => $this->info["AffId"],
					'messageid' => '',
					'sender' => '',
					'title' => trim(html_entity_decode($tds[1][2])),
					'content' => '',
					'created' => parse_time_str(trim($tds[1][0])),
			);

			if (preg_match('@<a href="(.*?/dismiss/(\d+))">(.*?)</a>@', $tds[1][3], $g))
			{
				$data['messageid'] = $g[2];
			}
			if (preg_match('@<a href="(.*?/view/(\d+))">(.*?)</a>@', $tds[1][1], $g))
			{
				$data['content_url'] = 'http://offers.commissionmonster.com' . $g[1];
				$data['contentType'] = '1';
			}elseif (preg_match('@<a href="(.*?/view/(\d+))">(.*?)</a>@', $tds[1][2], $g))
			{
				$data['content_url'] = 'http://offers.commissionmonster.com' . $g[1];
				$data['contentType'] = '2';
			}
			if (preg_match('@<a href="/offers/view/\d+">(.*?)</a>@', $tds[1][2], $g))
				$data['sender'] = $g[1];
			if (empty($data['messageid']) || empty($data['title']))
				continue;
			$messages[] = $data;
		}
		return $messages;
	}

	function getMessageDetail($data)
	{
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", );
		$url = $data['content_url'];
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r['content'];

		if($data['contentType'] == '1'){
			if (preg_match('@</strong>\s+</p>\s+<p>(.*?)</p>\s+</div>@ms', $content, $g))
				$data['content'] = str_force_utf8(trim(html_entity_decode($g[1])));	
		}elseif($data['contentType'] == '2'){
				$objDomTools = new DomTools($content);
				$objDomTools->select('div .grid_12');
				$m = $objDomTools->get();
				
				$data['content'] = str_force_utf8(trim(html_entity_decode($m[0]['Content'])));	
		}
		unset($data['contentType']);
		return $data;
	}

	function GetProgramFromAff()
	{	
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";		
		
		$this->GetProgramFromByApi();		
		$this->checkProgramOffline($this->info["AffId"], $check_date);

		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}
	
	function GetProgramFromByApi()
	{
		echo "\tGet Program by api\r\n";
		$program_num = 0;
			
		//step 1,login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);		

		$objProgram = new ProgramDb();
		$arr_prgm = array();
		
		$api_key = $this->api_key;
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "get",
			"postdata" => "",
		);

		$nNumPerPage = 100;
		$bHasNextPage = true;
		$nPageNo = 1;
		while($bHasNextPage){
			$strUrl = "http://offers.commissionmonster.com/offers/offers.json?api_key={$api_key}&limit=$nNumPerPage&page=$nPageNo";
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$result = $r["content"];
			$result = json_decode($result);
			$total = intval($result->total_rows);
			if(($nPageNo * $nNumPerPage) >= $total)
				$bHasNextPage = false;

			$program_list = $result->data;
			foreach($program_list->offers as $v){
				$strMerID = intval($v->id);
				if(!$strMerID) continue;

				$strMerName = trim($v->name);
				$desc = urldecode(trim($v->description));
				$CategoryExt = trim($v->categories);
				$TargetCountryExt = trim($v->countries_short);
				$Homepage = urldecode(trim($v->preview_url));

				$pay_out = trim($v->payout);
				$pay_out_type = trim($v->payout_type);

				$CommissionExt = $pay_out. " " . $pay_out_type;

				$prgm_url = "http://offers.commissionmonster.com/offers/view/$strMerID";
				$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
				$prgm_detail = $prgm_arr["content"];
				if (empty($prgm_detail) || $prgm_arr['code'] != 200)
					continue;

				$Partnership = "Active";
				$statusInAffRemark = "";
				if(stripos($prgm_detail, "GENERATE TRACKING") === false){
					$statusInAffRemark = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Offer Application', 'Status:</label>'), '</li>'));
					if($statusInAffRemark !== false){
						if(stripos($statusInAffRemark, "pending") !== false){
							$Partnership = "Pending";
						}elseif(stripos($statusInAffRemark, "not allowed") !== false){
							$Partnership = "Declined";
						}else{
							$statusInAffRemark = strip_tags($statusInAffRemark);
							$Partnership = "NoPartnership";
						}
					}else{
						$statusInAffRemark = "";
						$Partnership = "NoPartnership";
					}
				}
				$arr_prgm[$strMerID] = array(
					"Name" => addslashes($strMerName),
					"AffId" => $this->info["AffId"],
					"Homepage" => addslashes($Homepage),
					"IdInAff" => $strMerID,
					"CategoryExt" => addslashes($CategoryExt),
					"TargetCountryExt" => addslashes($TargetCountryExt),
					"CommissionExt" => addslashes($CommissionExt),
					"StatusInAffRemark" => addslashes($statusInAffRemark),
					"StatusInAff" => 'Active',					//'Active','TempOffline','Offline'
					"Partnership" => $Partnership,				//'NoPartnership','Active','Pending','Declined','Expired','Removed'
					"Description" => addslashes($desc),
					"LastUpdateTime" => date("Y-m-d H:i:s"),
					"DetailPage" => $prgm_url,
				);
				echo sprintf("Program: %s, Name:%s, Partnership: %s\n", $strMerID, $strMerName, $Partnership);
				$program_num++;
				if(count($arr_prgm) >= 100){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
			}
			$nPageNo++;
		}
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
			unset($arr_prgm);
		}
		echo "\tGet Program by api\r\n";
		if($program_num < 10){
			mydie("die: program count < 10, please check program.\n");
		}
		echo "\tUpdate ({$program_num}) program.\r\n";
		echo "\tSet program country int.\r\n";
		$objProgram->setCountryInt($this->info["AffId"]);
	}

	function checkProgramOffline($AffId, $check_date){
		$objProgram = new ProgramDb();
		$prgm = array();
		$prgm = $objProgram->getNotUpdateProgram($AffId, $check_date);
		if(count($prgm) > 30){
			mydie("die: too many offline program (".count($prgm).").\n");
		}else{
			$objProgram->setProgramOffline($this->info["AffId"], $prgm);
			echo "\tSet (".count($prgm).") offline program.\r\n";
		}
	}
}
?>
