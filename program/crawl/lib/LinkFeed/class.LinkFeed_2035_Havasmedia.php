<?php
require_once 'text_parse_helper.php';

class LinkFeed_2035_Havasmedia
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->getStatus = false;
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		
		$this->site_ID = '2977629';
		$this->Token = '644DB33C9E0022924FF569410C27684625720287';
	}
	
	function LoginIntoAffService()
	{
		//get para __VIEWSTATE and then process default login
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => "post",
				"postdata" => $this->info["AffLoginPostString"],
				"maxredirs" => 4,//if we dont set this, it will be failed at the fifth redir
				//"verbose" => 1, //for debug
				//"referer" => "https://affiliate.havasmedia.co.uk/index.html",
				//"autoreferer" => 1,
		);
		$strUrl = $this->info["AffLoginUrl"];
		$arr = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$request = array("AffId" => $this->info["AffId"], "method" => "get",);
		$strUrl = "https://affiliate.havasmedia.co.uk/publisher/aStartPage.action";
		$arr = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		if($this->info["AffLoginVerifyString"] && stripos($arr["content"], $this->info["AffLoginVerifyString"]) !== false)
		{
			echo "verify succ: " . $this->info["AffLoginVerifyString"] . "\n";
			return true;
		}
		else
		{
			print_r($arr);
			echo "verify failed: " . $this->info["AffLoginVerifyString"] . "\n";
		}
		return false;
	}
	
	function GetAllLinksByAffId()
	{
		$check_date = date('Y-m-d H:i:s');
	    $arr_return = array("AffectedCount" => 0,"UpdatedCount" => 0,);
	    $request = array("AffId" => $this->info["AffId"],"method" => "post","postdata" => "",);
	    $site_id = $this->site_ID;
	    $searchTypes = array('ge', 'textLinks');
	    $links = array();
	    $this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
	    
	    $arr_merchant = $this->oLinkFeed->getApprovalAffMerchant($this->info["AffId"]);
	    foreach ($arr_merchant as $merinfo){
	        
	        list($nNumPerPage, $bHasNextPage, $nPageNo) = array(100, true, 1);
	        while($bHasNextPage && $nPageNo < 50)
	        {
	            $aff_mer_id = $merinfo["AffMerchantId"];
	            $request["method"] = "post";
	            $url = "https://affiliate.havasmedia.co.uk/pan/aGEList.action";
	            $request["postdata"] = sprintf("programGEListParameterTransport.currentPage=%s&searchPerformed=true&searchType=ge&programGEListParameterTransport.programIdOrName=%s&programGEListParameterTransport.deepLinking=&programGEListParameterTransport.tariffStructure=&programGEListParameterTransport.siteId=%s&programGEListParameterTransport.orderBy=lastUpdated&programGEListParameterTransport.websiteStatusId=&programGEListParameterTransport.pageSize=%s&programAdvancedListParameterTransport.directAutoApprove=&programGEListParameterTransport.graphicalElementTypeId=&programGEListParameterTransport.specialConditionId=&programGEListParameterTransport.graphicalElementSize=&programGEListParameterTransport.width=&programGEListParameterTransport.height=&programGEListParameterTransport.lastUpdated=&programGEListParameterTransport.graphicalElementNameOrId=&programGEListParameterTransport.showGeGraphics=true&programAdvancedListParameterTransport.pfAdToolUnitName=&programAdvancedListParameterTransport.pfAdToolProductPerCell=&programAdvancedListParameterTransport.pfAdToolDescription=&programAdvancedListParameterTransport.pfTemplateTableRows=&programAdvancedListParameterTransport.pfTemplateTableColumns=&programAdvancedListParameterTransport.pfTemplateTableWidth=&programAdvancedListParameterTransport.pfTemplateTableHeight=&programAdvancedListParameterTransport.pfAdToolContentUnitRule=",
	                $nPageNo, $aff_mer_id, $site_id, $nNumPerPage);
	            $r = $this->oLinkFeed->GetHttpResult($url,$request);
	            $content = $r["content"];
	            preg_match_all('@onClick="hideGEIframe\((\d+)\);(.*?)<td colspan="14".*?>.*?</tr>@ms', $content, $chapters);
	            foreach ((array)$chapters[0] as $key => $chapter)
	            {
	                $link = array(
	                    "AffId" => $this->info["AffId"],
	                    "AffMerchantId" => $aff_mer_id,
	                    "AffLinkId" => $chapters[1][$key],
	                    "LinkName" => '',
	                    "LinkDesc" => '',
	                    "LinkStartDate" => '0000-00-00',
	                    "LinkEndDate" => '0000-00-00',
	                    "LinkPromoType" => 'N/A',
	                    "LinkHtmlCode" => '',
	                    "LinkCode" => '',
	                    "LinkOriginalUrl" => '',
	                    "LinkImageUrl" => '',
	                    "LinkAffUrl" => '',
	                    "DataSource" => '',
	                    "IsDeepLink" => 'UNKNOWN',
	                    "Type"       => 'link'
	                );
	                $link['LinkAffUrl'] = sprintf('http://clkuk.pvnsolutions.com/brand/contactsnetwork/click?p=%s&a=%s&g=%s', $aff_mer_id, $site_id, $link['AffLinkId']);
	                $link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
	                if (preg_match('@<span id="geText\d+">(.*?)</span>@ms', $chapter, $g))
	                    $link['LinkName'] = html_entity_decode(strip_tags(trim($g[1])));
	                if (preg_match('@<td>\s+(\d+/\d+/\d+)</td>@', $chapter, $g))
	                    $link['LinkStartDate'] = parse_time_str($g[1], 'd/m/Y', false);
	                if (preg_match('@<td>\s+JPG/GIF</td>@i', $chapter))
	                {
	                    $link['LinkImageUrl'] = sprintf('http://www.pvnsolutions.com/pan/aGEGraphicalElementPreview.action?programId=%s&graphicalElementId=%s', $aff_mer_id, $link['AffLinkId']);
	                    if (preg_match('@<td colspan="14" style="z-index: 1; position: relative;">(.*?)</td>@ms', $chapter, $g))
	                    {
	                        $g[1] = str_replace('src="/pan/', 'src="http://www.pvnsolutions.com/pan/', $g[1]);
	                        $link['LinkDesc'] = str_replace('<iframe  src="/', '<iframe  src="http://www.pvnsolutions.com/', $g[1]);
	                    }
	                }
	                else if (preg_match('@<td>\s+Text links</td>@i', $chapter))
	                {
	                    if (preg_match('@<td colspan="14" style="z-index: 1; position: relative;">(.*?)</td>@ms', $chapter, $g))
	                        $link['LinkName'] = trim(html_entity_decode(strip_tags($g[1])));
	                    $link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
	                    $link['LinkHtmlCode'] = create_link_htmlcode($link);
	                }
	                else
	                    $link['LinkHtmlCode'] = create_link_htmlcode($link);
	                $code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc']);
	                if (!empty($code))
	                {
	                    $link['LinkPromoType'] = 'COUPON';
	                    $link['LinkCode'] = $code;
	                }
	        
	                if (empty($link['AffLinkId']))
	                    continue;
	                elseif(empty($link['LinkName'])){
	                    $link['LinkPromoType'] = 'link';
	                }
	                $this->oLinkFeed->fixEnocding($this->info,$link,"link");
	                $links[] = $link;
	                $arr_return["AffectedCount"] ++;
	            }
	            echo sprintf("get link by page...program:%s, page:%s, %s link(s) found.\n", $aff_mer_id, $nPageNo, count($links));
	            if (count($links) > 0)
	                $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
	            $links = array();
	            $bHasNextPage = false;
	            if (preg_match('@\&nbsp;\d+\&nbsp;(.*?)</div>@', $content, $g))
	            {
	                if (preg_match('@A HREF="@i', $g[1]))
	                    $bHasNextPage = true;
	            }
	            $nPageNo ++;
	        }
	        
	    }
	    $this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link');
	    return $arr_return;
		
	}
	
	function getCouponFeed()
	{
	    $check_date = date('Y-m-d H:i:s');
	    $arr_return = array("AffectedCount" => 0,"UpdatedCount" => 0,"Detail" => array());
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "");
		$token = $this->Token;
		if(empty($token))
			mydie("Api token not exist. \n");
		$url = "http://api.pvnsolutions.com/1.0/vouchers.json?token={$token}";
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		 
		$content = $r['content'];
		// sometime the td api server input charater 7bf at start of the josn and 0 at end of the json
		// and the end of the ] is missing.
		// this maybe the api server wrong.
		// fix the json and wait td change it.
		// remove the 7bf and the 0 charater and add ].
		$content = trim($content);
		if (substr($content, 0, 3) == '7bf')
			$content = substr($content, 3);
		if (substr($content, -1, 1) == '0')
			$content = substr($content, 0, -1);
		$content = trim($content);
		if (substr($content, -1, 1) == '}')
			$content .= ']';
		// above code is the fixing json code.
		// delete when the server runs normal.
		$data = @json_decode($content);
		$links = array();
		foreach((array)$data as $v)
		{
			$link = array(
					"AffId" => $this->info["AffId"],
					"AffMerchantId" => $v->programId,
					"AffLinkId" => $v->id,
					"LinkName" => trim($v->shortDescription),
					"LinkDesc" => trim($v->description),
					"LinkStartDate" => parse_time_str(trim($v->startDate), 'millisecond', false),
					"LinkEndDate" => parse_time_str(trim($v->endDate), 'millisecond', false),
					"LinkPromoType" => 'COUPON',
					"LinkHtmlCode" => '',
					"LinkCode" => trim($v->code),
					"LinkOriginalUrl" => empty($v->landingUrl) ? '' : trim($v->landingUrl),
					"LinkImageUrl" => empty($v->logoPath) ? '' : trim($v->logoPath),
					"LinkAffUrl" => trim($v->defaultTrackUri),
					"DataSource" => '',
			        "IsDeepLink" => 'UNKNOWN',
			        "Type"       => 'promotion'
			);
			$link['LinkHtmlCode'] = create_link_htmlcode($link);
			if (empty($link['AffMerchantId']) || empty($link['LinkName']) || empty($link['AffLinkId']))
				continue;
			$arr_return["AffectedCount"] ++;
			$links [] = $link;
			if(sizeof($links) > 100)
			{
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
				$links = array();
			}
		}
		if (sizeof($links) > 0)
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		echo sprintf("get coupon by api...%s link(s) found.\n", $arr_return['AffectedCount']);
		if($arr_return['AffectedCount'] > 0){
		    $this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'promotion');
		}
		return $arr_return;
	}
	
	function GetStatus()
	{
		$this->getStatus =true;
		$this->GetProgramFromAff();
	}
	
	function GetProgramFromAff()
	{
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
		$this->GetProgramByPage();
		$this->checkProgramOffline($this->info["AffId"], $check_date);
		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}
	
	function GetProgramByPage()
	{
		echo "\tGet Program by page start\r\n";
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);
		
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		
		$arr_return = array();
		$request = array("AffId" => $this->info["AffId"],"method" => "post","postdata" => "",);
		
		$nNumPerPage = 100;
		$nPageNo = 1;
		$site_id = $this->site_ID;
		while(1)
		{
			if ($nPageNo == 1){
				$strUrl = "https://affiliate.havasmedia.co.uk/pan/aProgramList.action";
				$request["method"] = "post";
				$request["postdata"] = "programGEListParameterTransport.currentPage=".$nPageNo."&searchPerformed=true&searchType=prog&programGEListParameterTransport.programIdOrName=&programGEListParameterTransport.deepLinking=&programGEListParameterTransport.tariffStructure=&programGEListParameterTransport.siteId=" . $site_id . "&programGEListParameterTransport.orderBy=statusId&programAdvancedListParameterTransport.websiteStatusId=&programGEListParameterTransport.pageSize=" . $nNumPerPage . "&programAdvancedListParameterTransport.directAutoApprove=&programAdvancedListParameterTransport.mobile=&programGEListParameterTransport.graphicalElementTypeId=&programGEListParameterTransport.graphicalElementSize=&programGEListParameterTransport.width=&programGEListParameterTransport.height=&programGEListParameterTransport.lastUpdated=&programGEListParameterTransport.graphicalElementNameOrId=&programGEListParameterTransport.showGeGraphics=true&programAdvancedListParameterTransport.pfAdToolUnitName=&programAdvancedListParameterTransport.pfAdToolProductPerCell=&programAdvancedListParameterTransport.pfAdToolDescription=&programAdvancedListParameterTransport.pfTemplateTableRows=&programAdvancedListParameterTransport.pfTemplateTableColumns=&programAdvancedListParameterTransport.pfTemplateTableWidth=&programAdvancedListParameterTransport.pfTemplateTableHeight=&programAdvancedListParameterTransport.pfAdToolContentUnitRule=";
				$this->oLinkFeed->GetHttpResult($strUrl,$request);
			}
			$strUrl = "https://affiliate.havasmedia.co.uk/pan/aProgramList.action?categoryChoosen=false&programGEListParameterTransport.currentPage=".$nPageNo."&programGEListParameterTransport.pageSize=".$nNumPerPage."&programGEListParameterTransport.pageStreamValue=true";
		
			$request["postdata"] = "";
			$request["method"] = "get";
		
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$result = $r["content"];
		
			//parse HTML
			$strLineStart = 'showPopBox(event, getProgramCodeAffiliate';
			$nLineStart = 0;
			$bStart = 1;
			while(1)
			{
				$nLineStart = stripos($result,$strLineStart,$nLineStart);
				if($nLineStart === false && $bStart == 1) break 2;
				if($nLineStart === false) break;
				$bStart = 0;
		
				$strMerID = $this->oLinkFeed->ParseStringBy2Tag($result, 'getProgramCodeAffiliate(', ',', $nLineStart);
				if($strMerID === false) break;
				$strMerID = trim($strMerID);
				if(empty($strMerID)) continue;
		
				//name
				$strMerName = $this->oLinkFeed->ParseStringBy2Tag($result, ">","</a>", $nLineStart);
				if($strMerName === false) break;
				$strMerName = html_entity_decode(trim($strMerName));
		
				$CategoryExt = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
				$CategoryExt = trim(str_replace("&nbsp;","",$CategoryExt));
				$arr_pattern = array();
				for($i=0;$i<8;$i++) $arr_pattern[] = "<td>";
				$EPC90d = $this->oLinkFeed->ParseStringBy2Tag($result,$arr_pattern, '</td>', $nLineStart);
				if($EPC90d === false) break;
				$EPC90d = trim(html_entity_decode(strip_tags($EPC90d)));
				if($EPC90d == "") $EPC90d = "";
		
				$EPCDefault = $this->oLinkFeed->ParseStringBy2Tag($result,'<td>','</td>', $nLineStart);
				if($EPCDefault === false) break;
				$EPCDefault = trim(html_entity_decode(strip_tags($EPCDefault)));
				if($EPCDefault == "") $EPCDefault = "";
				$strStatus = $this->oLinkFeed->ParseStringBy2Tag($result, array('<td>','<td>'), '</td>', $nLineStart);
				if($strStatus === false) break;
				$strStatus = trim(strip_tags($strStatus));
				
				if(stripos($strStatus,'Accepted') !== false) $strStatus = 'approval';
				elseif(stripos($strStatus,'Under Consideration') !== false) $strStatus = 'pending';
				elseif(stripos($strStatus,'Denied') !== false) $strStatus = 'declined';
				elseif(stripos($strStatus,'On Hold') !== false) $strStatus = 'siteclosed';
				elseif(stripos($strStatus,'Apply') !== false) $strStatus = 'not apply';
				elseif(stripos($strStatus,'Ended') !== false) $strStatus = 'declined';
				else mydie("die: Unknown Status: $strStatus <br>\n");
		
				if(stripos($strMerName,'Closed') !== false) $strStatus = 'siteclosed';
				// when closing get the closing time and set to siteclosed when the time is less than now
				elseif(stripos($strMerName,'closing') !== false || stripos($strMerName,'pausing') !== false)
				{
					if (preg_match('@\d\d\.\d\d\.\d\d\d\d@', $strMerName, $g))
					{
						if (isset($g[0]) && strtotime($g[0]) < time())
							$strStatus = 'siteclosed';
					}
					if (preg_match('@(\d+)\/(\d+)\/(\d\d)@', $strMerName, $g))
					{
						if (strtotime(sprintf("20%s-%s-%s", $g[3], $g[2], $g[1])) < time())
							$strStatus = 'siteclosed';
					}
					if (preg_match('@(\d+)\/(\d+)\/(\d\d\d\d)@', $strMerName, $g))
					{
						if (strtotime(sprintf("%s-%s-%s", $g[3], $g[2], $g[1])) < time())
							$strStatus = 'siteclosed';
					}
					echo $strMerName."---".$strStatus."\n";
				}
				elseif(stripos($strMerName,'paused') !== false) $strStatus = 'siteclosed';
				elseif(stripos($strMerName,'set to pause') !== false) $strStatus = 'siteclosed';
				if($strStatus == 'approval'){
					$Partnership = "Active";
					$StatusInAff = "Active";
				}elseif($strStatus == 'pending'){
					$Partnership = "Pending";
					$StatusInAff = "Active";
				}elseif($strStatus == 'declined'){
					$Partnership = "Declined";
					$StatusInAff = "Active";
				}elseif($strStatus == 'not apply'){
					$Partnership = "NoPartnership";
					$StatusInAff = "Active";
				}else{
					$Partnership = "NoPartnership";
					$StatusInAff = "Offline";
				}
		
				if(!$this->getStatus) 
				{
					$arr_prgm[$strMerID] = array(
							"Name" => addslashes($strMerName),
							"AffId" => $this->info["AffId"],
							//"Contacts" => $Contacts,
							//"TargetCountryExt" => $TargetCountryExt,
							"IdInAff" => $strMerID,
							//"JoinDate" => date("Y-m-d H:i:s", strtotime($row["joinDate"])),
							"StatusInAffRemark" => $strStatus,
							"StatusInAff" => $StatusInAff,                        //'Active','TempOffline','Offline'
							"Partnership" => $Partnership,                        //'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
							//"Description" => addslashes($desc),
							//"Homepage" => $Homepage,
							"EPCDefault" => preg_replace("/[^0-9.]/", "", $EPCDefault),
							"EPC90d" => preg_replace("/[^0-9.]/", "", $EPC90d),
							//"TermAndCondition" => addslashes($TermAndCondition),
							//"SupportDeepUrl" => $SupportDeepUrl,
							"LastUpdateTime" => date("Y-m-d H:i:s"),
							//"DetailPage" => $prgm_url,
							//"AffDefaultUrl" => addslashes($AffDefaultUrl),
							//"CommissionExt" => addslashes($CommissionExt),
							"CategoryExt" => addslashes($CategoryExt),
							//"AllowNonaffCoupon" => $AllowNonaffCoupon,
							"SupportDeepUrl"=>'NO',
							"AllowNonaffCoupon"=>'UNKNOWN'
					);
					 
					$request["method"] = "get";
					$request["postdata"] = "";
					$prgm_url = "https://affiliate.havasmedia.co.uk/pan/aProgramTextRead.action?programId={$strMerID}&affiliateId={$site_id}";
					$arr_prgm[$strMerID]['DetailPage'] = addslashes($prgm_url);
					
					$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
					if($prgm_arr['code'] == 200){
						$results = $prgm_arr['content'];
						$prgm_detail = $prgm_arr["content"];
						//print_r($cache);
						$desc = "<div>" . trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, '<div id="publisher-body">', '<div id="publisher-footer">'));
						$desc = preg_replace("/[\\r|\\n|\\r\\n|\\t]/is", '', $desc);
						$desc = preg_replace('/<([a-z]+?)\s+?.*?>/i', '<$1>', $desc);
						preg_match_all('/<([a-z]+?)>/i', $desc, $res_s);
						preg_match_all('/<\/([a-z]+?)>/i', $desc, $res_e);
	
						//egg's pain very much
						$tags_arr = array();
						foreach ($res_s[1] as $v) {
							if (strtolower($v) != "br") {
								if (isset($tags_arr[$v])) {
									$tags_arr[$v] += 1;
								} else {
									$tags_arr[$v] = 1;
								}
							}
						}
						foreach ($res_e[1] as $v) {
							if (strtolower($v) != "br" && isset($tags_arr[$v])) {
								$tags_arr[$v] -= 1;
							}
						}
						foreach ($tags_arr as $k => $v) {
							for ($i = 0; $i < $v; $i++) {
								$desc .= "</$k>";
							}
						}
						$arr_prgm[$strMerID]['Description'] = addslashes($desc);
						$AllowNonaffCoupon = 'UNKNOWN';
						if(		stripos($prgm_detail, 'Voucher codes are not redeemable') !== false
								|| preg_match("/Voucher codes.*used with authorisation/i", $prgm_detail)
								|| stripos($prgm_detail, "not offer discount codes or voucher codes") !== false
						)
						{
							$AllowNonaffCoupon = 'NO';
							$arr_prgm[$strMerID]['AllowNonaffCoupon'] = addslashes($AllowNonaffCoupon);
						}
					}
		
					$overview_url = "https://affiliate.havasmedia.co.uk/pan/aProgramInfoApplyRead.action?programId={$strMerID}&affiliateId={$site_id}";
					$overview_arr = $this->oLinkFeed->GetHttpResult($overview_url, $request);
					if($prgm_arr['code'] == 200){
						$overview_detail = $overview_arr["content"];
						$Homepage = trim($this->oLinkFeed->ParseStringBy2Tag($overview_detail, array('Visit the site', '<a href="'), '"'));
						$CommissionExtStringStart = "Business information";
						$CommissionExtLineStart = stripos($overview_detail,$CommissionExtStringStart);
						$CommissionExt = trim($this->oLinkFeed->ParseStringBy2Tag($overview_detail, '<td valign', '<td valign', $CommissionExtLineStart));
						if(strlen($CommissionExt)) $CommissionExt = '<td valign'.$CommissionExt;
						$SupportDeepUrl = strtoupper(trim($this->oLinkFeed->ParseStringBy2Tag($overview_detail, array('Deep linking', '<td nowrap="nowrap">'), '</td>')));
						$LogoUrl = trim($this->oLinkFeed->ParseStringBy2Tag($overview_detail, array('<table border="0">', '<td><img src="'), '"></td>'));
						$CookieTime = intval(trim($this->oLinkFeed->ParseStringBy2Tag($overview_detail, array('Cookie time', '<td nowrap="nowrap">'), 'day')));
						$PaymentDays = intval(trim(html_entity_decode($this->oLinkFeed->ParseStringBy2Tag($overview_detail, array('Time to auto accept', '<td>', '<td>'), 'Day'))));
							
						//find homepage
						if ($tmp_url = $this->oLinkFeed->findFinalUrl($Homepage)) {
							$Homepage = $tmp_url;
						}
						//echo "$Homepage </br>";
						$arr_prgm[$strMerID]['Homepage'] = addslashes($Homepage);
						$arr_prgm[$strMerID]['CommissionExt'] = addslashes($CommissionExt);
						$arr_prgm[$strMerID]['SupportDeepUrl'] = addslashes($SupportDeepUrl);
						$arr_prgm[$strMerID]['LogoUrl'] = addslashes($LogoUrl);
						$arr_prgm[$strMerID]['CookieTime'] = $CookieTime;
						$arr_prgm[$strMerID]['PaymentDays'] = $PaymentDays;
					}
					
					/* $links_url = "https://affiliate.havasmedia.co.uk/pan/aProgramInfoLinksRead.action?programId={$strMerID}&affiliateId={$site_id}";
					$arr_prgm[$strMerID]['AffDefaultUrl'] = "";
					$links_arr = $this->oLinkFeed->GetHttpResult($links_url, $request);
					if($prgm_arr['code'] == 200){
						$links_detail = $links_arr["content"];
						$g_id = intval($this->oLinkFeed->ParseStringBy2Tag($links_detail, array('/pan/aInfoCenterLinkInfo.action', 'geId='), '&'));
						if ($g_id > 0) {
							$AffDefaultUrl = "http://clkuk.pvnsolutions.com/click?p({$strMerID})a({$site_id})g({$g_id})";
							$arr_prgm[$strMerID]['AffDefaultUrl'] = addslashes($AffDefaultUrl);
						}
					} */
		
				}else{
					$arr_prgm[$strMerID] = array(
							"Name" => addslashes($strMerName),
							"AffId" => $this->info["AffId"],
							//"Contacts" => $Contacts,
							"IdInAff" => $strMerID,
							//"JoinDate" => date("Y-m-d H:i:s", strtotime($row["joinDate"])),
							"StatusInAffRemark" => $strStatus,
							"StatusInAff" => $StatusInAff,                        //'Active','TempOffline','Offline'
							"Partnership" => $Partnership,                        //'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
							"EPCDefault" => preg_replace("/[^0-9.]/", "", $EPCDefault),
							"EPC90d" => preg_replace("/[^0-9.]/", "", $EPC90d),
							//"TermAndCondition" => addslashes($TermAndCondition),
							"LastUpdateTime" => date("Y-m-d H:i:s"),
					);
				}
				//print_r($arr_prgm);exit;
				$program_num++;
				if(count($arr_prgm) >= 100){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
			}
			$nPageNo++;
			if(count($arr_prgm)){
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
				$arr_prgm = array();
			}
		}
		echo "\tGet Program by page end\r\n";
		/* if($program_num < 10){
			mydie("die: program count < 10, please check program.\n");
		} */
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