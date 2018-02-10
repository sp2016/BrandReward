<?php

require_once 'text_parse_helper.php';
require_once 'xml2array.php';

class LinkFeed_23_Silvertap
{
	var $info = array(
		"ID" => "23",
		"Name" => "Silvertap",
		"IsActive" => "YES",
		"ClassName" => "LinkFeed_23_Silvertap",
		"LastCheckDate" => "1970-01-01",
	);
	
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		$this->isLogined = false;
        $this->getStatus = false;
	}
	
	function LoginIntoAffService()
	{
		if ($this->isLogined)
			return true;
			
		$strUrl = "https://mats.silvertap.com/Login.aspx";
		$request = array(
			"method" => "get",
			"postdata" => "",
			"SSLV" => 5,
		);
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];	
		$__EVENTTARGET = urlencode($this->oLinkFeed->ParseStringBy2Tag($result, array('name="__EVENTTARGET"', 'value="'), '"'));
		$__EVENTARGUMENT = urlencode($this->oLinkFeed->ParseStringBy2Tag($result, array('name="__EVENTARGUMENT"', 'value="'), '"'));
		$__VIEWSTATE = urlencode($this->oLinkFeed->ParseStringBy2Tag($result, array('name="__VIEWSTATE"', 'value="'), '"'));		
		
		$strUrl = "https://mats.silvertap.com/Login.aspx";
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "post",
			"postdata" => "",
			"SSLV" => 5,
		);
		$request["postdata"] = "__EVENTTARGET={$__EVENTTARGET}&__EVENTARGUMENT={$__EVENTARGUMENT}&__VIEWSTATE={$__VIEWSTATE}&txtUsername=".$this->info["Account"]."&txtPassword=".$this->info["Password"]."&cmdSubmit=Login";		
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];
	
		if(stripos($result,'Ran Chen') === false)
		{
			mydie("die: failed to login.\n");
		}
		else
		{
			echo "login succ.\n";
			$this->isLogined = true;
		}
	}
	
	function getCouponFeed()
	{
		// wrong format of the csv or xml feed.
		// server error.
		// do not parse the feed now.
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", );
		return $arr_return;

		$url = "https://mats.silvertap.com/Feeds/VoucherCodes.aspx?user=6813&pwd=48833f925abf025a9cf5221c992c0048&url=13779";
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r['content'];
		$data = XML2Array::createArray($content);
		$links = array();
		foreach ((array)$data['VoucherCodes']['VoucherCode'] as $v)
		{
			$link = array(
					"AffId" => $this->info["AffId"],
					"AffMerchantId" => $v['ProgrammeID'],
					"LinkName" => html_entity_decode($v['Offer_Title']),
					"LinkDesc" => html_entity_decode($v['Description']),
					"LinkStartDate" => parse_time_str($v['Created'], 'd/m/Y', false),
					"LinkEndDate" => parse_time_str($v['Expires'], 'd/m/Y', true),
					"LinkPromoType" => 'coupon',
					"LinkHtmlCode" => '',
					"LinkCode" => trim($v['Code']),
					"LinkOriginalUrl" => '',
					"LinkImageUrl" => '',
					"LinkAffUrl" => @$v['LinkUrl'],
					"DataSource" => 90,
			);
			if ($v['Offer_Type'] == 'Deal')
				$link['LinkPromoType'] = 'DEAL';
			if (!empty($v['exclusive']) && $v['exclusive'] != 'No')
				$link['LinkDesc'] .= " {$v['Exclusive']}";
			$link['LinkAffUrl'] = preg_replace('@^\&url=@', '', $link['LinkAffUrl']);
			if (empty($link['LinkName']) || empty($link['LinkAffUrl']))
				continue;
			if (preg_match('@^https://mats.silvertap.com/Tracking/@i', $link['LinkAffUrl'], $g))
			{
			}
			else 
				continue;
			$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
			$links[] = $link;
			$arr_return['AffectedCount'] ++;
		}
		echo sprintf("get csv feed, %s links(s) found. \n", count($links));
		if(count($links) > 0)
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		return $arr_return;
	}
	
	function GetAllLinksFromAffByMerID($merinfo)
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0,);
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", );
		$this->LoginIntoAffService();
		$url = sprintf("https://mats.silvertap.com/Tools/GetCreatives.aspx?programme=%s", $merinfo['IdInAff']);
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r['content'];
		$link = array();
		if (preg_match('@<input name=".*?txtLink" type="text" value="(.*?)"@', $content, $g))
		{
			$link = array(
					"AffId" => $this->info["AffId"],
					"AffMerchantId" => $merinfo['IdInAff'],
					'AffLinkId' => "{$merinfo['IdInAff']}_1",
					"LinkName" => 'Text Link',
					"LinkDesc" => '',
					"LinkStartDate" => '0000-00-00',
					"LinkEndDate" => '0000-00-00',
					"LinkPromoType" => 'DEAL',
					"LinkHtmlCode" => '',
					"LinkCode" => '',
					"LinkOriginalUrl" => '',
					"LinkImageUrl" => '',
					"LinkAffUrl" => '',
					"DataSource" => 90,
			);
			if (preg_match('@href="(.*?)"@', html_entity_decode($g[1]), $a))
			{
				$link["LinkAffUrl"] = trim($a[1]);
				$link['LinkHtmlCode'] = create_link_htmlcode($link);
				$links[] = $link;
				$arr_return['AffectedCount'] ++;
			}
		}
		preg_match_all('@<tr class="contentRow.*?">(.*?)</tr>@ms', $content, $chapters);
		foreach ((array)$chapters[1] as $chapter)
		{
			preg_match_all('@<td.*?>(.*?)</td>@ms', $chapter, $tds);
			$link = array(
					"AffId" => $this->info["AffId"],
					"AffMerchantId" => $merinfo['IdInAff'],
					'AffLinkId' => '',
					"LinkName" => '',
					"LinkDesc" => '',
					"LinkStartDate" => parse_time_str($tds[1][4], 'd/m/Y', false),
					"LinkEndDate" => '0000-00-00',
					"LinkPromoType" => 'DEAL',
					"LinkHtmlCode" => '',
					"LinkCode" => '',
					"LinkOriginalUrl" => '',
					"LinkImageUrl" => '',
					"LinkAffUrl" => '',
					"DataSource" => 90,
			);
			if (preg_match('@alt="(.*?)"@', $tds[1][0], $g))
				$link['LinkName'] = $g[1];
			if (preg_match('@<textarea .*?>(.*?)</textarea>@ms', $tds[1][5], $g))
			{
				$link['LinkHtmlCode'] = trim(html_entity_decode($g[1]));
				if (preg_match('@href="(.*?\&i=(\d+).*?)".*?<img.*?src="(.*?)"@', $link['LinkHtmlCode'], $g))
				{
					$link['LinkAffUrl'] = $g[1];
					$link['AffLinkId'] = $g[2];
					$link['LinkImageUrl'] = $g[3];
				}
			}
			if (empty($link['LinkAffUrl']))
				continue;
            elseif(empty($link['LinkName'])){
                $link['LinkPromoType'] = 'link';
            }
			$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
			$links[] = $link;
			$arr_return['AffectedCount'] ++;
		}
		echo sprintf("program: %s, %s links(s) found. \n", $merinfo['IdInAff'], count($links));
		if(count($links) > 0)
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		return $arr_return;
	}
	
	function GetProgramFromAff()
	{	
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
		
		$this->GetProgramByPage();		
		$this->checkProgramOffline($this->info["AffId"], $check_date);

		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}

    function GetStatus(){
        $this->getStatus = true;
        $this->GetProgramFromAff();
    }

	function GetProgramByPage()
	{
		echo "\tGet Program by page start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;

		//step 1,login
		$this->LoginIntoAffService();

		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", "SSLV" => 5);

		$strUrl = "https://mats.silvertap.com/Programmes/Default.aspx";
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];

		$pager_area = $this->oLinkFeed->ParseStringBy2Tag($result, '<tr class="pager">', '</tr>');
		preg_match_all("/<.*?>(\d+)<\\/.*?>/i", $pager_area, $matches);

		$page_arr = array();
		if(count($matches[1])){
			$page_arr = $matches[1];
		}

		foreach($page_arr as $page){
			echo "\t get page $page\r\n";
			$__EVENTTARGET = 'ctl00$ContentPlaceHolder1$gvMerchants';
			$__LASTFOCUS = urlencode($this->oLinkFeed->ParseStringBy2Tag($result, array('name="__LASTFOCUS"', 'value="'), '"'));
			$__VIEWSTATE = urlencode($this->oLinkFeed->ParseStringBy2Tag($result, array('name="__VIEWSTATE"', 'value="'), '"'));

			$__EVENTARGUMENT = 'Page$'.$page;

			$strUrl = "https://mats.silvertap.com/Programmes/Default.aspx";
			$request["method"] = "post";
			$request["postdata"] = 'ctl00$ScriptManager1=ctl00$ContentPlaceHolder1$UpdatePanel2|ctl00$ContentPlaceHolder1$gvMerchants'."&__EVENTTARGET=$__EVENTTARGET&__EVENTARGUMENT=$__EVENTARGUMENT&__LASTFOCUS=$__LASTFOCUS&__VIEWSTATE=$__VIEWSTATE".'&ctl00$ContentPlaceHolder1$txtSearch=&ctl00$ContentPlaceHolder1$ddlCategory=&ctl00$ContentPlaceHolder1$ddlSubCategory=&ctl00$ContentPlaceHolder1$ddlStatus=&__ASYNCPOST=true';
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$result = $r["content"];
			$strLineStart = '<tr class="programmeContentRow';
			$nLineStart = 0;
			while ($nLineStart >= 0){	
				$nLineStart = stripos($result, $strLineStart, $nLineStart);
				if ($nLineStart === false) break;

				$Homepage = $this->oLinkFeed->ParseStringBy2Tag($result, array('<td>', 'href="'), '"', $nLineStart);
				$MerchantName = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('title="', '>'), '<', $nLineStart));
				$IdInAff = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('href="', 'programme='), '"', $nLineStart));
				$ProgramName = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('title="', '>'), '<', $nLineStart));
				$JoinDate = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
				$JoinDate = str_replace('/', '-', $JoinDate);
				$JoinDate = date("Y-m-d H:i:s", strtotime($JoinDate));
				$CommissionExt = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
				$CommissionType = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
				$CookieTime = intval($this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart));
				$ProductFeed = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
				$VoucherCodes = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
				$SubID = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
				$StatusInAffRemark = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart));

				$Name = $MerchantName . " - " . $ProgramName;
				if ($StatusInAffRemark == "Applied")
					$Partnership = "Pending";
				else if ($StatusInAffRemark == "Approved")
					$Partnership = "Active";
				else if($StatusInAffRemark == "Refused")
					$Partnership = "Declined";
				else
					$Partnership = "NoPartnership";


                //非获取状态   不采集某些信息
                if(!$this->getStatus) {
                    $request_detail = array(
                        "AffId" => $this->info["AffId"],
                        "method" => "get",
                        "postdata" => "",
                        "SSLV" => 5,
                    );
                    $prgm_url = "https://mats.silvertap.com/Programmes/ProgrammeDetails.aspx?programme=$IdInAff";
                    $prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request_detail);
                    $prgm_detail = $prgm_arr["content"];

                    $desc = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Additional Details', 'Description', '<span>'), '</span>');

                    $__EVENTTARGET = 'ctl00$ContentPlaceHolder1$ddlWebsite';
                    $__EVENTARGUMENT = urlencode($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('name="__EVENTARGUMENT"', 'value="'), '"'));
                    $__LASTFOCUS = urlencode($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('name="__LASTFOCUS"', 'value="'), '"'));
                    $__VIEWSTATE = urlencode($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('name="__VIEWSTATE"', 'value="'), '"'));

                    $request_detail["method"] = "post";
                    $request_detail["postdata"] = 'ctl00$ScriptManager1=ctl00$ContentPlaceHolder1$UpdatePanel3|ctl00$ContentPlaceHolder1$ddlWebsite&ctl00$ContentPlaceHolder1$ddlWebsite=8825&ctl00$ContentPlaceHolder1$txtSubIDValue=&ctl00$ContentPlaceHolder1$txtCustomDeeplink=&ctl00$ContentPlaceHolder1$txtTrackingLink=' . "&__EVENTTARGET=$__EVENTTARGET&__EVENTARGUMENT=$__EVENTARGUMENT&__LASTFOCUS=$__LASTFOCUS&__VIEWSTATE=$__VIEWSTATE&__ASYNCPOST=true";
                    $prgm_url = "https://mats.silvertap.com/Programmes/ProgrammeDetails.aspx?programme=$IdInAff";
                    $prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request_detail);
                    $prgm_detail = $prgm_arr["content"];

                    $AffDefaultUrl = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Tracking URL', '<textarea', '>'), '</textarea>');
                    $AffDefaultUrl = trim($this->oLinkFeed->ParseStringBy2Tag($AffDefaultUrl, 'href=&quot;', '&quot;'));

                    if (stripos($prgm_detail, "Custom Deeplink") !== false) {
                        $SupportDeepurl = "YES";
                    } else {
                        $SupportDeepurl = "NO";
                    }

                    $arr_prgm[$IdInAff] = array(
                        "AffId" => $this->info["AffId"],
                        "IdInAff" => $IdInAff,
                        "Name" => addslashes($Name),
                        "JoinDate" => $JoinDate,
                        //"CategoryExt" => addslashes($CategoryExt),
                        //"TargetCountryExt" => $TargetCountryExt,
                        "Homepage" => addslashes($Homepage),
                        "Description" => addslashes($desc),
                        "CommissionExt" => addslashes($CommissionExt),
                        "CookieTime" => addslashes($CookieTime),
                        "StatusInAffRemark" => addslashes($StatusInAffRemark),
                        "StatusInAff" => 'Active',                        //'Active','TempOffline','Offline'
                        "Partnership" => $Partnership,                        //'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
                        "LastUpdateTime" => date("Y-m-d H:i:s"),
                        "DetailPage" => $prgm_url,
                        "SupportDeepurl" => $SupportDeepurl,
                        "AffDefaultUrl" => addslashes($AffDefaultUrl)
                    );
                    //print_r($arr_prgm);exit;
                } else {
                    $arr_prgm[$IdInAff] = array(
                        "AffId" => $this->info["AffId"],
                        "IdInAff" => $IdInAff,
                        "Name" => addslashes($Name),
                        "JoinDate" => $JoinDate,
                        //"CategoryExt" => addslashes($CategoryExt),
                        //"TargetCountryExt" => $TargetCountryExt,
                        "Homepage" => addslashes($Homepage),
                        "CommissionExt" => addslashes($CommissionExt),
                        "CookieTime" => addslashes($CookieTime),
                        "StatusInAffRemark" => addslashes($StatusInAffRemark),
                        "StatusInAff" => 'Active',                        //'Active','TempOffline','Offline'
                        "Partnership" => $Partnership,                        //'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
                        "LastUpdateTime" => date("Y-m-d H:i:s"),
                    );
                }
				$program_num++;

				if(count($arr_prgm) >= 100){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
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
}

