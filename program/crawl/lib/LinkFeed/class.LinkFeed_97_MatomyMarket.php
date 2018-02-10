<?php

require_once 'text_parse_helper.php';

class LinkFeed_97_MatomyMarket
{
	var $info = array(
		"ID" => "97",
		"Name" => "MatomyMarket",
		"IsActive" => "YES",
		"ClassName" => "LinkFeed_97_MatomyMarket",
		"LastCheckDate" => "1970-01-01",
	);

	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;

		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
	}

	function GetProgramFromAff()
	{
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
		
		
		$this->GetProgramFromByPage();
		$this->getProgramHomepage();
		//$this->getProgramDetail();
		$this->checkProgramOffline($this->info["AffId"], $check_date);

		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}

	function GetProgramFromByPage()
	{
		echo "\tGet Program by page start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;

		//step 1,login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);

		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "get",
			"postdata" => "",
		);

		$tmp_request = array(
			"AffId" => $this->info["AffId"],
			"method" => "get"
		);

		//get new program
		echo "get programs\r\n";

		$strUrl = "https://network.adsmarket.com/site/AffiliateExplorerFlexiGrid";

		$hasNextPage = true;
		$page = 1;
		$perPage = 25;
		while($hasNextPage){
			echo "\t page $page.";
			$request["postdata"] = 'page='.$page.'&fgId=AffiliateExplorerFg&rp='.$perPage.'&sortname=prg_RankOverride&sortorder=DESC&query=&qtype=&show_advanced=&search_string=&prgName=&prgId=&search_relation_status=0&affCountriesSearchExplorer=0&affCategoriesSearchExplorer=0&search_creative_type=0&search_TrackingType=0&sort_by=2';
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$result = $r["content"];

			$result = json_decode($result);

			$total = intval($result->total);
			if($page * $perPage >= $total) $hasNextPage = false;

			$program_list = $result->rows;
			foreach($program_list as $v){
				$strMerID = intval($v->id);
				if(!$strMerID) continue;

				$prgm_info = array();
				$prgm_info = $v->cell;

				$strMerName = trim(str_ireplace($strMerID . " -", "", strip_tags($prgm_info[1])));
				$StatusInAffRemark = trim(strip_tags($prgm_info[2]));
				if($StatusInAffRemark == "Approved"){
					$Partnership = "Active";
				}elseif($StatusInAffRemark == "Pending"){
					$Partnership = "Pending";
				}elseif($StatusInAffRemark == "Apply to AffiliateSolutions"){
					$Partnership = "NoPartnership";
				}else{
					$Partnership = "NoPartnership";
				}

				$CategoryExt = trim($prgm_info[3]);
				$TargetCountryExt = trim($prgm_info[4]);
				$CommissionExt = strip_tags($prgm_info[5]);
				$RankInAff = intval($this->oLinkFeed->ParseStringBy2Tag($prgm_info[8], '<img src="RankImage?rank=', '"'));
				$EPCDefault = preg_replace("/[^0-9.]/", "", $prgm_info[9]);			

				$prgm_url = "https://network.adsmarket.com/site/AffiliateExplorerDetails?uid=$strMerID&fgId=AffiliateExplorerFg";				
				/*$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $tmp_request);
				$prgm_detail = $prgm_arr["content"];

				$Homepage = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<span class="labelpopup_main">URL:', 'href="'), '"'));				
				$desc = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Description', '<td id="progdesc" class="top">'), '</td>'));
				$CookieTime = intval($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Cookie Duration (Days):', '</span>'), '</td>'));
				$TermAndCondition = strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Restrictions', '<td id="progresc" class="top">'), '</td>'));*/

				$arr_prgm[$strMerID] = array(
					"Name" => addslashes(html_entity_decode($strMerName)),
					"AffId" => $this->info["AffId"],
					"TargetCountryExt" => $TargetCountryExt,
					"IdInAff" => $strMerID,
					"RankInAff" => $RankInAff,
					"EPCDefault" => $EPCDefault,
					"StatusInAff" => 'Active',						//'Active','TempOffline','Offline'
					"StatusInAffRemark" => $StatusInAffRemark,
					"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
					"CommissionExt" => addslashes($CommissionExt),
					//"Description" => addslashes($desc),
					//"Homepage" => $Homepage,
					//"CookieTime" => $CookieTime,
					//"TermAndCondition" => addslashes($TermAndCondition),
					"LastUpdateTime" => date("Y-m-d H:i:s"),
					"DetailPage" => $prgm_url,

				);

				$program_num++;

				if(count($arr_prgm) >= 100){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
			}

			$page++;
			if($page > 1000){
				mydie("die: Page overload.\n");
			}
		}

		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
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

	function getProgramHomepage(){		
		echo "\tGet Program Homepage start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;

		//step 1,login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);

		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "get",
		);

		echo "\tGet Program Idinaff\r\n";
		$all_prgm = $objProgram->getAllProgramByAffId($this->info["AffId"], array("IdInAff"));
		
		echo "\tget detail page\r\n";
		foreach($all_prgm as $v){
			$strMerID = intval($v["IdInAff"]);
			if(!$strMerID) continue;
			
			$prgm_url = "https://network.adsmarket.com/affiliate/campaigns.json?crt[programs]=$strMerID";
			$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
			$prgm_detail = $prgm_arr["content"];
			$prgm_detail = json_decode($prgm_detail);
			
			
			
			$cpid = 0;
			$tmp_arr = $prgm_detail->data;
			//print_r($tmp_arr);
			foreach($tmp_arr as $vv){
				$cpid = $vv->id;
				break;
			}
			
			if($cpid){			
				$prgm_url = "https://network.adsmarket.com/affiliate/campaigns/$cpid.json";				
				$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
				$prgm_detail = $prgm_arr["content"];
				$prgm_detail = json_decode($prgm_detail);
				
			
				 $url = trim($prgm_detail->url);
				
				
				 $tmp_url = trim($this->oLinkFeed->findFinalUrl($url, array("nobody" => "unset")));
				
				if($url == $tmp_url){
					$tmp_url = trim($this->oLinkFeed->findFinalUrl($tmp_url, array("nobody" => "unset")));
				}

				$Homepage = '';
				
				if($url == $tmp_url){					
					//echo "\t";
					continue;
					
				}else{
					$Homepage = $tmp_url;
					echo "Old: $url \r\n \tNew: $tmp_url\r\n";
				}						
				//echo "[$url]\r\n[$tmp_url]\r\n";
				//exit;
				
				//$Homepage = $tmp_url;
				
				$arr_prgm[$strMerID] = array(
					"AffId" => $this->info["AffId"],
					"IdInAff" => $strMerID,				
					//"Homepage" => $Homepage			
					//"program" => $url
				);
				$program_num++;
				
				if($Homepage)
					$arr_prgm[$strMerID]["Homepage"] = $Homepage;
			
				if(count($arr_prgm) >= 100){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					$arr_prgm = array();
				}
			}
		}
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			unset($arr_prgm);
		}

		echo "\tGet Program Homepage end\r\n";
	}
	
	
	function getProgramDetail(){
		echo "\tGet Program Detail start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;

		//step 1,login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);

		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "get",
		);

		echo "\tGet Program Idinaff\r\n";
		$all_prgm = $objProgram->getAllProgramByAffId($this->info["AffId"], array("IdInAff"));
		
		echo "\tget detail page\r\n";
		foreach($all_prgm as $v){
			$strMerID = intval($v["IdInAff"]);
			if(!$strMerID) continue;
			
			$prgm_url = "https://network.adsmarket.com/site/AffiliateExplorerDetails?uid=$strMerID&fgId=AffiliateExplorerFg";
			$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
			$prgm_detail = $prgm_arr["content"];

			$Homepage = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<span class="labelpopup_main">URL:', 'href="'), '"'));
			
			if(stripos($Homepage, "matomy") !== false) $Homepage = "";
			
			$desc = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Description', '<td id="progdesc" class="top">'), '</td>'));
			$CookieTime = intval($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Cookie Duration (Days):', '</span>'), '</td>'));
			$TermAndCondition = strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Restrictions', '<td id="progresc" class="top">'), '</td>'));
			
			/*
			"id": 139663,
		    "title": "PlatiniumPromotions - US - Incentivized - OLFA",
		    "text": "139663 - PlatiniumPromotions - US - Incentivized - OLFA",
		    "countries": "United States",
		    "categories": "Incentivized, Lead Generation, Mobile Optimized",
		    "user_flows": "CPL, SOI",
		    "media_types": [{"id":22,"title":"Content Locking"},{"id":23,"title":"Display"},{"id":2,"title":"Email"},{"id":27,"title":"GDN"},{"id":11,"title":"Incentivized"},{"id":18,"title":"Mobile Display"},{"id":24,"title":"Pops"},{"id":4,"title":"Search"},{"id":25,"title":"Social"}],
		    "media_types_all": [{"id":22,"title":"Content Locking"},{"id":23,"title":"Display"},{"id":2,"title":"Email"},{"id":27,"title":"GDN"},{"id":28,"title":"Host & Post"},{"id":21,"title":"In App"},{"id":11,"title":"Incentivized"},{"id":18,"title":"Mobile Display"},{"id":9,"title":"Other"},{"id":24,"title":"Pops"},{"id":4,"title":"Search"},{"id":25,"title":"Social"}],
		    "creativeRestrictions" : "Requires Advertiser Approval",
		    "logo": "\/\/images.adsmarket.com\/prg\/139663\/186153.jpeg",
		    "description": "Offer Review:&nbsp;&nbsp; &nbsp;<br>A lead Generation offer with many offers for US.<br><br>Affiliate Benefits:&nbsp;&nbsp; &nbsp;<br>Higher rates available for high-performing affiliates, at the advertiser's discretion.<br><br>User Flow:&nbsp;&nbsp; &nbsp;<br>User submits valid email address and clicks on \"Submit\". At this point conversion occurs, Single Opt In.<br><br>Typical Demographics:&nbsp;&nbsp; &nbsp;<br>ALL<br><br>",
		    "trackingTypeId": 2,
		    "trackingType": "Server",
		    "trackingDuration": 45,
		    "restriction": "<br>",
		    "associationButton": "<button disabled=\"disabled\" class=\"btn btn-inverse mb5\" data-action=\"denied\" data-type=\"assoc_btn\" >Application Denied<\/button>",
		    "mobile_tags": "",
		    "platform" : [" Desktop"," Mobile "
			$prgm_url = "https://network.adsmarket.com/affiliate/programs/$strMerID.json";
			$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
			$prgm_detail = $prgm_arr["content"];			
			$prgm_detail = json_decode($prgm_detail);
		*/

			$arr_prgm[$strMerID] = array(
				"AffId" => $this->info["AffId"],
				"IdInAff" => $strMerID,
				"Description" => addslashes($desc),
				"Homepage" => $Homepage,
				"CookieTime" => $CookieTime,
				"TermAndCondition" => addslashes($TermAndCondition),
				"LastUpdateTime" => date("Y-m-d H:i:s"),
				"DetailPage" => $prgm_url,
			);
			$program_num++;

			if(count($arr_prgm) >= 100){
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				$arr_prgm = array();
			}
		}
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			unset($arr_prgm);
		}

		echo "\tGet Program Detail end\r\n";
	}

	function GetAllLinksFromAffByMerID($merinfo)
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		$request = array("AffId" => $this->info["AffId"], "method" => "post", "no_ssl_verifyhost" => true);
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		
		// trafficSourceUrl
		$request = array("AffId" => $this->info["AffId"], "method" => "get",);
		$url = sprintf("https://network.adsmarket.com/site/AffiliateExplorerDetails?uid=%s&fgId=AffiliateExplorerFg", $merinfo['IdInAff']);
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r['content'];
		preg_match_all('@a title="View Details" onclick=".*?cpn_id=(\d+).*?".*?>(.*?)</a>(.*?</tbody>.*?)</tbody>@ms', $content, $chapters);
		$links = array();
		foreach ((array)$chapters[3] as $key => $chapter)
		{
			$link = array(
					"AffId" => $this->info["AffId"],
					"AffMerchantId" => $merinfo['IdInAff'],
					"AffLinkId" => $chapters[1][$key],
					"LinkName" => trim($chapters[2][$key]),
					"LinkDesc" =>  '',
					"LinkStartDate" => '0000-00-00 00:00:00',
					"LinkEndDate" => '0000-00-00 00:00:00',
					"LinkPromoType" => 'DEAL',
					"LinkHtmlCode" => '',
					"LinkCode" => '',
					"LinkOriginalUrl" => '',
					"LinkImageUrl" => '',
					"LinkAffUrl" => '',
					"DataSource" => 91,
			);
			if (preg_match('@<td>(.*?)</td>\s+<td class="trafficSourceUrl" >(.*?)</td>@ms', $chapter, $g))
			{
				$link['LinkName'] .= ' - ' . $g[1];
				$link['LinkAffUrl'] .= $g[2];
			}
			$code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc']);
			if (!empty($code))
			{
				$link['LinkCode'] = $code;
				$link['LinkPromoType'] = 'coupon';
			}
			else
				$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
			$link['LinkHtmlCode'] = create_link_htmlcode_image($link);
			if (empty($link['AffLinkId']))
				continue;
            elseif(empty($link['LinkName'])){
                $link['LinkPromoType'] = 'link';
            }
			$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
			$links[] = $link;
			$arr_return["AffectedCount"] ++;
		}
		echo sprintf("program: %s trafficSourceUrl %s link(s) found.\n", $merinfo['IdInAff'], count($links));
		if(count($links) > 0)
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);

		// text and banner links.
		$request = array("AffId" => $this->info["AffId"], "method" => "post", "no_ssl_verifyhost" => true);
		$url = "https://network.adsmarket.com/site/AffiliateExplorerCreativesFlexiGrid";
		list($total, $page, $limit, $links) = array(0, 1, 100, array());
		do
		{
			$links = array();
			$request['postdata'] = array('page' => $page,
					'fgId' => 'AffiliateExplorerCreativesFg',
					'rp' => $limit,
					'sortname' => 'ctv_Id',
					'sortorder' => 'desc',
					'query' => '',
					'qtype' => '',
					'creativesPrgId' => $merinfo['IdInAff'],
					'creativesCampaignId' => 0,
					'showCreatives' => 1
			);
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			if ($r['code'] == 100)
			{
				// sleep 100 and try again.
				sleep(100);
				$this->oLinkFeed->clearHttpInfos($this->info["AffId"]);
				$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);
				$r = $this->oLinkFeed->GetHttpResult($url, $request);
				if ($r['code'] == 100)
					mydie("ssl error.\n");
			}
			$content = $r['content'];
			$data = json_decode($content, true);
			if (empty($total))
				$total = $data['total'];
			foreach ((array)$data['rows'] as $v)
			{
				if (empty($v['id']) || empty($v['cell']) || !is_array($v['cell']))
					continue;
				$row = $v['cell'];
				$link = array(
						"AffId" => $this->info["AffId"],
						"AffMerchantId" => $merinfo['IdInAff'],
						"AffLinkId" => $v['id'],
						"LinkName" => html_entity_decode(trim($row[2])),
						"LinkDesc" =>  '',
						"LinkStartDate" => '0000-00-00 00:00:00',
						"LinkEndDate" => '0000-00-00 00:00:00',
						"LinkPromoType" => 'DEAL',
						"LinkHtmlCode" => '',
						"LinkCode" => '',
						"LinkOriginalUrl" => '',
						"LinkImageUrl" => '',
						"LinkAffUrl" => '',
						"DataSource" => 91,
				);
				if (preg_match('@href="(http\://network\.adsmarket\.com/click/.*?)"@ms', $row[1], $g))
					$link['LinkAffUrl'] = $g[1];
				if (preg_match('@src="(https://network\.adsmarket\.com/ceas/.*?)"@ms', $row[1], $g))
					$link['LinkImageUrl'] = $g[1];
				$code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc']);
				if (!empty($code))
				{
					$link['LinkCode'] = $code;
					$link['LinkPromoType'] = 'coupon';
				}
				else
					$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
				$link['LinkHtmlCode'] = create_link_htmlcode_image($link);
				if (empty($link['AffLinkId']) || empty($link['LinkName']))
					continue;
				$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
				$links[] = $link;
				$arr_return["AffectedCount"] ++;
			}
			echo sprintf("program:%s, page:%s, %s links(s) found. \n", $merinfo['IdInAff'], $page, count($links));
			if(count($links) > 0)
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
			$page ++;
		}while ((($page - 1) *  $limit < $total) && $page <= 10); // get the first 10 pages
	}

	function getCouponFeed()
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		return $arr_return;
	}
}
