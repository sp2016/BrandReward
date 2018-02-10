<?php

require_once 'text_parse_helper.php';

class LinkFeed_22_AFFF_UK
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		
		$this->accountName = $this->info["Account"];
		$this->accountPwd  = $this->info["Password"];	
		if(SID == 'bdg01'){
			$this->API_KEY = '21B0E790E5';
			$this->API_Password = '3D631A0ED9';
		}else{
			$this->API_KEY = 'BD499DEC3A';
			$this->API_Password = '510978B6D7';
		}
	}

	function LoginIntoAffService()
	{
		//get para __VIEWSTATE and then process default login
		if(!isset($this->info["AffLoginPostStringOrig"])) $this->info["AffLoginPostStringOrig"] = $this->info["AffLoginPostString"];
		
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "post",
			"postdata" => "",
		);

		$strUrl = $this->info["AffLoginUrl"];
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];
		
		if(stripos($result, "__VIEWSTATE") === false) mydie("die: login for LinkFeed_22_AFFF_UK failed, __VIEWSTATE not found\n");

		$nLineStart = 0;
		$strViewState = $this->oLinkFeed->ParseStringBy2Tag($result, 'id="__VIEWSTATE" value="', '" />', $nLineStart);
		
		if($strViewState === false) mydie("die: login for LinkFeed_22_AFFF_UK failed, __VIEWSTATE not found\n");
		
		$this->info["AffLoginPostString"] = '__VIEWSTATE=' . urlencode($strViewState) . '&'. $this->info["AffLoginPostStringOrig"];
		
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info,2,true,true,false);
		return "stophere";
	}
	
	function GetHttpResult_22($url, $request, $retry = 3)
	{
		$retry --;
		if ($this->total_error_count)
			$this->total_error_count ++;
		else
			$this->total_error_count = 1;
		if ($retry <= 0)
			mydie("die: their system may be crashed(retry <= 0)\n");
		if ($this->total_error_count > 10)
			mydie("die: their system may be crashed(total_error_count > 10)\n");

		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		if($r["code"] != 200)
			return GetHttpResult_22($url, $request, $retry);
		if (preg_match('@>Application Error<@', $r['content']))
			return GetHttpResult_22($url, $request, $retry);
		return $r;
	}
	
/*
function getCouponFeed()
{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		$nNumPerPage = 100;
		$bHasNextPage = true;
		$nPageNo = 0;
		do
		{
			$links = array();
			$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", );
			$url = "http://afuk.affiliate.affiliatefuture.co.uk/vouchers/all-vouchers.aspx?pg={$nPageNo}";
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$content = $r["content"];
			if(preg_match("@<br>\s+<table(.*?)</tr>\s+</tbody>\s+</table>@", $content,$g)){
				$content = $g[1];
			}
			if (empty($content) )
				break;
			if(preg_match_all('@<table width="100%" border="0" cellspacing="0" cellpadding="0" id="content">(.*?)<img src="../i/v5.gif">\s+</td>\s+</tr>\s+</table>@ms', $content,$data)){
				foreach ($data[1] as $v)
				{
					$link = array(
						"AffId" => $this->info["AffId"],
						"AffMerchantId" => "",
						"AffLinkId" => "",
						"LinkName" =>  "",
						"LinkDesc" =>  "",
						"LinkStartDate" => '0000-00-00 00:00:00',
						"LinkEndDate" => '0000-00-00 00:00:00',
						"LinkPromoType" => 'DEAL',
						"LinkHtmlCode" => '',
						"LinkCode" => '',
						"LinkOriginalUrl" => '',
						"LinkImageUrl" => '',
						"LinkAffUrl" => '',
						"DataSource" =>31,
					);
					
					if(preg_match('@<span id=".*?voucherName">(.*?)</span>@ms', $v,$g)){
						$link['LinkName'] = $g[1];
					}
					
					if(preg_match('@<span id=".*?voucherCode">Code :(.*?)</span>@ms', $v,$g)){
						if(trim($g[1]) != "N/A" && trim($g[1])=='no code required'){
							$link['LinkCode'] = trim($g[1]);
							$link['LinkPromoType'] = "COUPON";
						}
					}
					
					if(preg_match('@<span id=".*?voucherDesc">(.*?)</span>@ms', $v,$g)){
						$link['LinkDesc'] = $g[1];
					}
					if(preg_match('@<span id=".*?voucherValidity">(.*?)</span>@ms', $v,$g)){
						$dateArr = explode("-", $g[1]);
						if($dateArr[0]){
							$link['LinkStartDate'] = date("Y-m-d H:i:s", strtotime($dateArr[0]));
						}
						
						if($dateArr[1]){
							$link['LinkEndDate'] = date("Y-m-d 23:59:59", strtotime($dateArr[1]));
						}
						
					}
					
					if(preg_match('@<textarea.*?>(.*?)</textarea>@ms', $v,$g)){
						$link['LinkHtmlCode'] = trim($g[1]);
						$link['LinkAffUrl'] = trim($g[1]);
						$link['AffLinkId'] = md5($g[1].$link['LinkCode']);
						if(preg_match('@merchantID=(.*?)&@ms', $link['LinkHtmlCode'],$g)){
							$link['AffMerchantId'] = trim($g[1]);
						}
						
					}
					
					
					if (empty($link['AffLinkId']) || empty($link['AffMerchantId']) || empty($link['LinkName']))
						continue;
					$links[] = $link;
					$arr_return["AffectedCount"] ++;
				}
			}else{
				break;
			}	
			
			echo sprintf("page:%s, %s links(s) found. \n", $nPageNo, count($links));
			if(count($links) > 0)
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
			$nPageNo++;
			sleep(1);
		}while($nPageNo < 1000);
		return $arr_return;
	}
	
 * 
 */
        
	function simplest_xml_to_array($xmlstring) {
		return json_decode(json_encode((array) simplexml_load_string($xmlstring)), true);
	}




	function getCouponFeed()
	{
		$check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		$url = "https://api.affiliatefuture.com/PublisherService.svc/getAllVouchers?key=$this->API_KEY&passcode=$this->API_Password";
		//$url = 'https://api.affiliatefuture.com/PublisherService.svc/getAllVouchers?key=BD499DEC3A&passcode=510978B6D7';
		$request = array("AffId" => $this->info["AffId"], "method" => "get");
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$data = $this->simplest_xml_to_array($r['content'])['Vouchers']['Voucher'];
		//var_dump($data);exit;
		$links = array();
		if(!empty($data)){
			foreach ($data as $v) {
				if ($v['Joined'] == 'No')
					continue;
				$link = array(
						"AffId" => $this->info["AffId"],
						"AffMerchantId" => "",
						"AffLinkId" => "",
						"LinkName" =>  "",
						"LinkDesc" =>  "",
						"LinkStartDate" => '0000-00-00 00:00:00',
						"LinkEndDate" => '0000-00-00 00:00:00',
						"LinkPromoType" => 'DEAL',
						"LinkHtmlCode" => '',
						"LinkCode" => '',
						"LinkOriginalUrl" => '',
						"LinkImageUrl" => '',
						"LinkAffUrl" => '',
						"DataSource" =>31,
						"IsDeepLink" => 'UNKNOWN',
						"Type"       => 'promotion'
				);
				if(!empty($v['VoucherDescription'])){
					$link['LinkName'] = $link['LinkDesc'] = $v['VoucherDescription'];
					if(!empty($v['TermsAndCondition'])){
						$link['LinkDesc'] .= " Condition:".$v['TermsAndCondition'];
					}
					if(!empty($v['CategoryName'])){
						$link['LinkDesc'] .= " Category:".$v['CategoryName'];
					}
				}
				if(!empty($v['VoucherCode'])){
					$link['LinkCode'] = $v['VoucherCode'];
					$link['LinkPromoType'] = "COUPON";
				}
				if(!empty($v['StartDate']))
					$link['LinkStartDate'] = date("Y-m-d H:i:s", strtotime($v['StartDate']));
		
				if(!empty($v['EndDate']))
					$link['LinkEndDate'] = date("Y-m-d 23:59:59", strtotime($v['EndDate']));
		
				if (empty($link['AffMerchantId']))
					$link['AffMerchantId'] = $v['MerchantSiteID'];
		
				if (empty($link['AffLinkId']))
					$link['AffLinkId'] = md5($link['AffMerchantId'].$v['VoucherID']);
					
				if (empty($link['LinkAffUrl'])){
					if($v['LandingPage'])
						$link['LinkAffUrl'] = $v['LandingPage'];
					if($v['Tracking_URL'])
						$link['LinkAffUrl'] = $v['Tracking_URL'];
				}
				if(!empty($v['ImageURL']))
					$link['LinkImageUrl'] = $v['ImageURL'];
		
				if (empty($link['AffLinkId']) || empty($link['AffMerchantId']) || empty($link['LinkName']))
					continue;
				$links[] = $link;
				$arr_return["AffectedCount"] ++;
			}
		
		}
		if(count($links) > 0){
			$c_links = array_chunk($links,100);
			foreach ($c_links as $links) {
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
			}
		}
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'promotion');
		return $arr_return;
	}

	function GetAllLinksByAffId()
	{
		$check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, );
		$request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "", );
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info, 1, false);
		$arr_merchant = $this->oLinkFeed->getApprovalAffMerchant($this->info["AffId"]);
		foreach ($arr_merchant as $AffMerchantId => $merinfo) 
		{
			if (is_string($merinfo)) {
				$arr_temp = $this->getApprovalAffMerchantFromTask($this->info["AffId"], $merinfo);
				if (empty($arr_temp)) mydie("die:GetAllLinksFromAffByMerID failed, merchant id($merinfo) not found.\n");
				$merinfo = $arr_temp;
			}
			$url = sprintf("http://afuk.affiliate.affiliatefuture.co.uk/programmes/getlinks_url.aspx?p=%s&id=%s", trim($merinfo['MerchantRemark']), $merinfo["AffMerchantId"]);
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$content = $r['content'];
			preg_match_all('@<tr style="background-color:White;">.*?Link code:<br>\s+<textarea.*?</textarea>@ms', $content, $chapters);
			$links = array();
			foreach ((array)$chapters[0] as $chapter)
			{
				$link = array(
						"AffId" => $this->info["AffId"],
						"AffMerchantId" => $merinfo["AffMerchantId"],
						"LinkDesc" => "",
						"LinkStartDate" => date("Y-m-d"),
						"LinkEndDate" => "0000-00-00",
						"LinkOriginalUrl" => "",
						"LinkImageUrl" => "",
						"DataSource" => "31",
						"IsDeepLink" => 'UNKNOWN',
						"Type"       => 'link'
				);
				if (preg_match('@Full html code:<br>\s+<textarea.*?>(.*?)</textarea>@', $chapter, $g))
				{
					$link['LinkHtmlCode'] = trim(html_entity_decode($g[1]));
					if (preg_match('@mediaID=(-?\d+)@', $link['LinkHtmlCode'], $g))
					{
						$link['AffLinkId'] = $g[1];
						if ((int)$link['AffLinkId'] < 0)
							$link['AffLinkId'] = sprintf('%s%s', $merinfo["AffMerchantId"], $link['AffLinkId']);
					}
				}
				if (preg_match('@<span id="dg1_ctl\d+_lbl_size">(.*?)</span>@', $chapter, $g))
					$link['LinkName'] = trim(html_entity_decode($g[1]));
				if (preg_match('@Link code:<br>\s+<textarea.*?>(.*?)</textarea>@', $chapter, $g))
					$link['LinkAffUrl'] = trim(html_entity_decode($g[1]));
				if (preg_match('@Image URL:</span><br>\s+<textarea.*?>(.*?)</textarea>@', $chapter, $g))
					$link['LinkImageUrl'] = trim(html_entity_decode($g[1]));
				$code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc']);
				if (!empty($code))
				{
					$link['LinkPromoType'] = 'coupon';
					$link['LinkCode'] = $code;
				}
				else
					$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
				if (empty($link['AffLinkId'])  )
					continue;
				elseif(empty($link['LinkName'])){
					$link['LinkPromoType'] = 'link';
				}
				$this->oLinkFeed->fixEnocding($this->info, $link, "link");
				$links[] = $link;
				$arr_return["AffectedCount"] ++;
			}
			echo sprintf("%s link(s) found.\n", count($links));
			if(sizeof($links) > 0)
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
			
			
		}
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link');
		return $arr_return;
	}
	
	/* function GetAllLinksFromAffByMerID($merinfo, $retry = 3)
	{
		
	    $check_date = date('Y-m-d H:i:s');
	    $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, );
		$request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "", );
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info, 1, false);
		$url = sprintf("http://afuk.affiliate.affiliatefuture.co.uk/programmes/getlinks_url.aspx?p=%s&id=%s", trim($merinfo['MerchantRemark']), $merinfo["AffMerchantId"]);
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r['content'];
		preg_match_all('@<tr style="background-color:White;">.*?Link code:<br>\s+<textarea.*?</textarea>@ms', $content, $chapters);
		$links = array();
		foreach ((array)$chapters[0] as $chapter)
		{
			$link = array(
					"AffId" => $this->info["AffId"],
					"AffMerchantId" => $merinfo["AffMerchantId"],
					"LinkDesc" => "",
					"LinkStartDate" => date("Y-m-d"),
					"LinkEndDate" => "0000-00-00",
					"LinkOriginalUrl" => "",
					"LinkImageUrl" => "",
					"DataSource" => "31",
			        "IsDeepLink" => 'UNKNOWN',
			        "Type"       => 'link'
			);
			if (preg_match('@Full html code:<br>\s+<textarea.*?>(.*?)</textarea>@', $chapter, $g))
			{
				$link['LinkHtmlCode'] = trim(html_entity_decode($g[1]));
				if (preg_match('@mediaID=(-?\d+)@', $link['LinkHtmlCode'], $g))
				{
					$link['AffLinkId'] = $g[1];
					if ((int)$link['AffLinkId'] < 0)
						$link['AffLinkId'] = sprintf('%s%s', $merinfo["AffMerchantId"], $link['AffLinkId']);
				}
			}
			if (preg_match('@<span id="dg1_ctl\d+_lbl_size">(.*?)</span>@', $chapter, $g))
				$link['LinkName'] = trim(html_entity_decode($g[1]));
			if (preg_match('@Link code:<br>\s+<textarea.*?>(.*?)</textarea>@', $chapter, $g))
				$link['LinkAffUrl'] = trim(html_entity_decode($g[1]));
			if (preg_match('@Image URL:</span><br>\s+<textarea.*?>(.*?)</textarea>@', $chapter, $g))
				$link['LinkImageUrl'] = trim(html_entity_decode($g[1]));
			$code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc']);
			if (!empty($code))
			{
				$link['LinkPromoType'] = 'coupon';
				$link['LinkCode'] = $code;
			}
			else
				$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
			if (empty($link['AffLinkId'])  )
				continue;
            elseif(empty($link['LinkName'])){
                $link['LinkPromoType'] = 'link';
            }
			$this->oLinkFeed->fixEnocding($this->info, $link, "link");
			$links[] = $link;
			$arr_return["AffectedCount"] ++;
		}
		echo sprintf("%s link(s) found.\n", count($links));
		if(sizeof($links) > 0)
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		sleep(1);
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link', $merinfo['IdInAff']);
		return $arr_return;
	} */

	function getMessage()
	{
		$messages = array();
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", );
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info, 1, false);
		// messages in inbox folder.
		// postdata in array of message must be delete in getMessageDetail function after used.
		$url = 'http://afuk.affiliate.affiliatefuture.co.uk/communications/merchant-messages-received.aspx';
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r['content'];
		preg_match_all('@<tr OnMouseOver="this.style.backgroundColor = \'\#E7EBF4\';" OnMouseOut="this.style.backgroundColor = \'\#ffffff\';" style="background-color:White;">(.*?)</tr>@ms', $content, $chapters);
		if (preg_match('@__VIEWSTATE" value="(.*?)"@', $content, $g))
			$__VIEWSTATE = $g[1];
		else
			return 'parse html error.';
		foreach ((array)$chapters[1] as $chapter)
		{
			preg_match_all('@<td.*?>(.*?)</td>@ms', $chapter, $tds);
			if (empty($tds) || empty($tds[1]) || !is_array($tds[1]) || count($tds[1]) < 2)
				continue;
			$data = array(
					'affid' => $this->info["AffId"],
					'messageid' => '',
					'sender' => str_force_utf8(trim(html_entity_decode(strip_tags($tds[1][1])))),
					'title' => str_force_utf8(trim(html_entity_decode(strip_tags($tds[1][2])))),
					'content' => '',
					'created' => parse_time_str(trim(strip_tags($tds[1][3])), null, false),
			);
			if (preg_match('@__doPostBack\(\'(.*?)\'@', $tds[1][1], $g))
			{
				$data['content_url'] = 'http://afuk.affiliate.affiliatefuture.co.uk/communications/merchant-messages-received.aspx';
				$data['postdata'] = sprintf('__VIEWSTATE=%s&__EVENTTARGET=%s', urlencode($__VIEWSTATE), urlencode($g[1]));
			}
			else
				continue;
			if (preg_match('@_msgid" style="display: none;">(\d+)<@', $tds[1][0], $g))
				$data['messageid'] = $g[1];
			if (empty($data['messageid']))
				continue;
			$messages[] = $data;
		}
		// messages in network news folder.
		$url = 'http://afuk.affiliate.affiliatefuture.co.uk/communications/network-news.aspx';
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r['content'];
		preg_match_all('@<tr OnMouseOver="this.style.backgroundColor = \'\#E7EBF4\';" OnMouseOut="this.style.backgroundColor = \'\#ffffff\';" style="background-color:White;">(.*?)</tr>@ms', $content, $chapters);
		foreach ((array)$chapters[1] as $chapter)
		{
			preg_match_all('@<td.*?>(.*?)</td>@ms', $chapter, $tds);
			if (empty($tds) || empty($tds[1]) || !is_array($tds[1]) || count($tds[1]) < 2)
				continue;
			$data = array(
					'affid' => $this->info["AffId"],
					'messageid' => '',
					'sender' => '',
					'title' => str_force_utf8(trim(html_entity_decode(strip_tags($tds[1][1])))),
					'content' => '',
					'created' => parse_time_str(trim(strip_tags($tds[1][0])), 'd/m/Y', false),
			);
			if (preg_match('@aspx\?id=(\d+)@', $tds[1][0], $g))
				$data['messageid'] = $g[1];
			if (empty($data['messageid']))
				continue;
			$data['content_url'] = sprintf('http://afuk.affiliate.affiliatefuture.co.uk/communications/network-news-article.aspx?id=%s', $data['messageid']);
			$messages[] = $data;
		}
		return $messages;
	}

	function getMessageDetail($data)
	{
		$url = $data['content_url'];
		if (empty($data['postdata']))
		{
			$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", );
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$content = $r['content'];
			if (preg_match('@<span id="lblArticle">(.*?)</span>&nbsp;</p>@ms', $content, $g))
				$data['content'] = trim(html_entity_decode($g[1]));
		}
		else 
		{
			$request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => $data['postdata'], );
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$content = $r['content'];
			if (preg_match('@<div id="CommsInbox1_div_message_body">(.*?)</div>@ms', $content, $g))
				$data['content'] = trim(html_entity_decode($g[1]));
			// the postdata is not a field of message in the database.
			// delete postdata and do not write it to the database.
			unset($data['postdata']);
		}
		return $data;
	}

	function GetProgramFromAff()
	{
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
		$this->GetProgramByPage();
		$this->checkProgramOffline($this->info["AffId"], $check_date);

		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}

	function GetCategoryByListPage(){
		echo "\tGet Category by page start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();

		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info,1,false);
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "get",
			"postdata" => "",
		);
		$strUrl = "http://afuk.affiliate.affiliatefuture.co.uk/merchants/Default.aspx";
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];
		preg_match_all('/VERTICAL-ALIGN(.*?[\s\S]*?)<\/table>/',$result,$tables);
		if(!is_array($tables[1])) mydie("get category page failed !");
		$programs = array();
		$baseurl = "afuk.affiliate.affiliatefuture.co.uk/merchants/";
		foreach ($tables[1] as $key=> $table){
			//一级分类
			preg_match('/<a.*?boldblue.*?href="(.*?)".*?>(.*?)<\/a>/',$table,$first);
			//二级分类
			preg_match_all('/text.*?href="(.*?)".*?>(.*?)<\/a>/',$table,$second);
			if(!isset($first[1]) || empty($first[1])) continue;
			if(!isset($second[1]) && !$second[1]){
				//一级分类信息
				$info = $this->oLinkFeed->GetHttpResult($baseurl.$first[1],$request);
				$res = $info['content'];
				preg_match_all('/cat=[0-9]*?&id=(\d+)/',$res,$pid);
				if(isset($pid[1])){
					foreach ($pid[1] as $p){
						$programs[$p]['SubCate'] = '';
						$programs[$p]['MainCate'] = $first[2];
					}
				}

			}else{
				//二级分类信息
				foreach($second[1] as $k => $cateUrl){
					$info = $this->oLinkFeed->GetHttpResult($baseurl.$cateUrl,$request);
					$res = $info['content'];
					preg_match_all('/cat=[0-9]*?&id=(\d+)/',$res,$pid);
					if(isset($pid[1])){
						foreach ($pid[1] as $p){
							$programs[$p]['SubCate'] = $second[2][$k];
							$programs[$p]['MainCate'] = $first[2];
						}
					}
				}
			}
		}
		return $programs;
//		print_r($programs);
//		if(count($programs))
//			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
	}
	
	function GetProgramByPage()
	{
		$CategoryList = $this->GetCategoryByListPage();

		echo "\tGet Program by page start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		//step 1,login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info,1,false);		

		$arr_return = array("AffectedCount" => 0,"UpdatedCount" => 0,);
		
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "get",
			"postdata" => "", 
		);

		//Step1 Get all approval merchants
		$strUrl = "http://afuk.affiliate.affiliatefuture.co.uk/programmes/MerchantsJoined.aspx";
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];
	
	
		//parse HTML
		//<table width="100%" cellspacing="0" class="aftable">
		$strLineStart = '<tr onmouseover="bgColor=\'#E7EBF4\'" onmouseout="bgColor=\'#ffffff\'">';

		$nLineStart = 0;
		while ($nLineStart >= 0)
		{
			$nLineStart = stripos($result, $strLineStart, $nLineStart);
			if ($nLineStart === false) break;
				
			$Homepage = $this->oLinkFeed->ParseStringBy2Tag($result, array('merchantLnk', 'href="'), '"', $nLineStart);
			//name
			$strMerName = $this->oLinkFeed->ParseStringBy2Tag($result, 'target="_blank">', '</a>', $nLineStart);
			if ($strMerName === false) break;
			$strMerName = trim($strMerName);
			//$strStatus = 'approval';
			
			$StatusInAff = 'Active';
			if(stripos($strMerName,"closed") !== false){
				$StatusInAff = 'Offline';
				$strMerName = trim(str_ireplace("closed","",$strMerName));
			}
			if(stripos($strMerName,"paused") !== false){
				$StatusInAff = 'Offline';
				$strMerName = trim(str_ireplace("paused","",$strMerName));
			}

			//getlinks_url.aspx?p=5438&amp;id=1980"
			$str2id = $this->oLinkFeed->ParseStringBy2Tag($result,'getlinks_url.aspx?p=','"',$nLineStart);
			if($str2id === false) break;
			$arr = explode("&amp;id=",$str2id);
			if(sizeof($arr) != 2) mydie("die: wrong str2id $str2id\n");
			list($programmeid,$strMerID) = $arr;
			if(!is_numeric($programmeid) || !is_numeric($strMerID)) mydie("die: wrong str2id $str2id\n");
						
			$AffDefaultUrl = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('<textarea', '>'),'<',$nLineStart));
			
			
			/* $prgm_url = "http://afuk.affiliate.affiliatefuture.co.uk/merchants/AddProgramme.aspx?id=$strMerID";
			$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
			$prgm_detail = $prgm_arr["content"];

			$CommissionExt = $this->getCommissionExt($prgm_detail);
			$desc = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, '<span id="merchantListingPanel_datalist1_ctl00_Description2">', '</span>'));
			$TermAndCondition = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, '<span id="datalist1_ctl00_OfferDetails">', '</span>')); */
			
			$dLineStart = 0;
			$prgm_url = "http://afuk.affiliate.affiliatefuture.co.uk/programmes/Details.aspx?id=$strMerID";
			$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
			$prgm_detail = $prgm_arr["content"];
			
			$desc = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, '<div class="wordwrap">', '</div>', $dLineStart));
			$TermAndCondition = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<div id="tabs-2" class="wordwrap"', '>'), '</div>', $dLineStart));
			$logoUrl = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, '<img id="imgAdvertiserLogo" src="', '"', $dLineStart));
			$CookieTime = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, '<div id="divStatCookieLength" class="stat">', ' ', $dLineStart));
			$desc .= '\r<br>'.trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, '<div id="gvProgrammes_ctl02_progDetails" class="description" style="display: none">', '</div>', $dLineStart));
			$CommissionExt = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, '<b>', '</b>', $dLineStart));
			
			
			$arr_prgm[$strMerID] = array(
				"Name" => addslashes(html_entity_decode($strMerName)),
				"AffId" => $this->info["AffId"],				
				//"Contacts" => $Contacts,
				"IdInAff" => $strMerID,
				"StatusInAffRemark" => '',
				"StatusInAff" => $StatusInAff,						//'Active','TempOffline','Offline'
				"Partnership" => 'Active',						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'				
				"Description" => addslashes($desc),
				"Homepage" => $Homepage,				
				"TermAndCondition" => addslashes($TermAndCondition),
				"LastUpdateTime" => date("Y-m-d H:i:s"),
				"DetailPage" => $prgm_url,
				"SupportDeepUrl" => "YES",
				"AffDefaultUrl" => addslashes($AffDefaultUrl),
				"Remark" => $programmeid, //here,we save programmeid to MerchantRemark
				"CommissionExt" => addslashes($CommissionExt),
				"CategoryExt" => addslashes($CategoryList[$strMerID]['MainCate'].'-'.$CategoryList[$strMerID]['SubCate']),
				"LogoUrl" => addslashes($logoUrl),
				"CookieTime" => $CookieTime,
			);
			$program_num++;
			//print_r($arr_prgm[$strMerID]);
			if(count($arr_prgm) >= 100){
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
				$arr_prgm = array();
			}
		}
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
			$arr_prgm = array();
		}
		
		echo "\tGet Program by page end\r\n";
		
		if($program_num < 10){
			mydie("die: program count < 10, please check program.\n");
		}
		
		echo "\tUpdate ({$program_num}) program.\r\n";
		echo "\tSet program country int.\r\n";
		$objProgram->setCountryInt($this->info["AffId"]);
	}

	function getCommissionExt($content){
		$Dom = new DomTools($content);

		preg_match_all('/id="(datalist1_ctl(?:\d+)_programmeTbl)"/',$content,$m);
		$CommissionExt = '';
		$CommissionArr = array();
		if($m){
			foreach($m[1] as $k=>$v){
				list(,$id,) = explode('_',$v);
				$programNameId = 'datalist1_'.$id.'_ProgramName';
				$programDetailId = 'datalist1_'.$id.'_OfferDetails';
				$programSubscribed = 'datalist1_'.$id.'_subscribedLbl';

				if(preg_match('/id="'.$programSubscribed.'"/',$content)){
					$Dom->select('#'.$programNameId);
					$res =$Dom->get();
					$programName = $res[0]['Content'];

					$Dom->select('#'.$programDetailId);
					$res =$Dom->get();
					$programDetail = $res[0]['Content'];			
					
					$tmp = array();
					$tmp['name'] = $programName;
					$tmp['detail'] = $programDetail;

					$CommissionArr[] = $tmp;
				}
				
			}

			$CommissionExt .= '<table>';
			foreach($CommissionArr as $k=>$v){
				$CommissionExt .= '<tr><td>'.$v['name'].'</td><td>'.$v['detail'].'</td></tr>';
			}
			$CommissionExt .= '</table>';
		}

		return $CommissionExt;
	}
	
	function GetProgramByApi()
	{
		echo "\tGet Program by api start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		
		
		$params = array('username' => $this->accountName,
						'password' => $this->accountPwd,
						'merchantsJoined' => 'no',
						'newMerchants' => ''
						);		
		
		try { 
			$SOAP_REQUEST = new SoapClient("http://ws-external.afnt.co.uk/apiv1/affiliates/networkfeed.asmx?WSDL", array('trace'=> true));
			$req = $SOAP_REQUEST->GetAFMerchantList($params);
			//print_r($req);
			$prgm_arr = $req->GetAFMerchantListResult->any;			
			
			$advertiser_list = simplexml_load_string($prgm_arr);
			
			//echo count($advertiser_list);exit;
			
			
			foreach($advertiser_list as $advertiser)
			{			
				/*$advertiser_info = array();
				$childnodes = $advertiser->getElementsByTagName("*");
				foreach($childnodes as $node){
					$advertiser_info[$node->nodeName] = trim($node->nodeValue);				
				}*/
				
				//print_r($advertiser);
				
				//$strMerID = 
				
				$arr_prgm[$strMerID] = array(
					"AffId" => $this->info["AffId"],	
					"IdInAff" => $strMerID,	
					"Name" => addslashes($prgm->ProgramTitle),					
					"Homepage" => $prgm->Url,
					"Description" => addslashes($prgm->Description),
					"TermAndCondition" => addslashes($prgm->Limitations),						
					"StatusInAffRemark" => addslashes($StatusInAffRemark),
					"StatusInAff" => 'Active',						//'Active','TempOffline','Offline'
					"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
					"DetailPage" => "http://publisher.affili.net/Programs/programInfo.aspx?pid=$strMerID",				
					"LastUpdateTime" => date("Y-m-d H:i:s"),
					"SupportDeepUrl" => "YES"
				);
				
				$program_num++;					
				
				if(count($arr_prgm) >= 100){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
			}
			//echo "<hr>";		
			//exit;
		} catch( Exception $e ) { 
			mydie("die: Api error.\n");
		}
		
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
			unset($arr_prgm);
		}
		
		echo "\tGet Program by api end\r\n";
		
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
}
?>
