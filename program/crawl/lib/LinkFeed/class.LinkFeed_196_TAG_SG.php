<?php

require_once 'text_parse_helper.php';

class LinkFeed_196_TAG_SG
{
	var $info = array(
		"ID" => "196",
		"Name" => "TAG SG",
		"IsActive" => "YES",
		"ClassName" => "LinkFeed_196_TAG_SG",
		"LastCheckDate" => "1970-01-01",
	);
	
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->getStatus = false;
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		if (SID == 'bdg01')
			$this->sid = 1688;
		else
			$this->sid = 1610;
	}	
	
	function getCouponFeed()
	{
		$links = array();
		$check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array());
		//step 1,login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
	
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => "get",
				"postdata" => ''
		);
	
		$PageSize = 100;
		$Page = 1;
		$HasNextPage = true;
		while ($HasNextPage)
		{
			$url = "https://www.tagadmin.sg/affiliate_program_vouchers_grid.html?responseType=json&pageSize=$PageSize&p=$Page";
			$result = $this->oLinkFeed->GetHttpResult($url, $request);
			$r = json_decode($result['content'], true);
			//var_dump($r);exit;
			if ($r['pageAmount'] == 0)
			{
				echo "without coupon of aff " . $this->info["AffId"] . "\r\n";
				break;
			}
			if ($r['pageNo'] != $Page)
			{
				mydie("pageNo error, please check the affiliate's data");
			}
			foreach ($r['results'] as $v)
			{
				if (empty($v['id']) || empty($v['programmeId']))
					continue;
				if ($v['status'] != 'Active')
					continue;
				$link = array(
						"AffId" => $this->info["AffId"],
						"AffMerchantId" => $v['programmeId'],
						"AffLinkId" => $v['id'],
						"LinkName" => $v['programmeName'],
						"LinkDesc" => $v['description'],
						"LinkStartDate" =>  isset($v['validityStartDate']) ? date('Y-m-d H:i:s',strtotime($v['validityStartDate'])) : '',
						"LinkEndDate" => isset($v['validityEndDate']) ? date('Y-m-d H:i:s',strtotime($v['validityEndDate'])) : '',
						"LinkPromoType" => 'coupon',
						"LinkHtmlCode" => '',
						"LinkCode" => $v['voucherCode'],
						"LinkOriginalUrl" => '',
						"LinkImageUrl" => '',
						"LinkAffUrl" => isset($v['landingURL']) ? $v['landingURL'] : '',
						"DataSource" => 368,
						"IsDeepLink" => 'UNKNOWN',
						"Type"       => 'promotion'
				);
				$link['LastUpdateTime'] = date('Y-m-d H:i:s');
				$link['LinkHtmlCode'] = create_link_htmlcode_image($link);
				if(!$link['AffMerchantId'] || !$link['AffLinkId'] || !$link['LinkAffUrl'])
					continue;
				//var_dump($link);
				$links[] = $link;
				$arr_return['AffectedCount']++;
			}
				
			if (count($links) > 0)
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
				
			echo sprintf("page:%s, %s coupon(s) found. \n", $Page, count($links));
				
			if($r['pageNo'] == $r['pageAmount'])
				$HasNextPage = false;
			$Page++;
		}
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'promotion');
		return $arr_return;
	}
	
	function GetAllLinksByAffId(){
	    
	    $check_date = date('Y-m-d H:i:s');
	    $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
	    $request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "");
	    $this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
	    
	    $arr_merchant = $this->oLinkFeed->getApprovalAffMerchant($this->info["AffId"]);
	    foreach ($arr_merchant as $merinfo){
	        
	        $url = sprintf('https://www.tagadmin.sg/affiliate_program_creative.html?pId=%s', $merinfo['IdInAff']);
	        $r = $this->oLinkFeed->GetHttpResult($url, $request);
	        $content = $r['content'];
	        preg_match_all('@<tr\s+.*?>(.*?)</tr>@ms', $content, $chapters);
	        if (empty($chapters) || empty($chapters[1]) || !is_array($chapters[1]))
	            continue;
	        if (preg_match('@id="_aid"\s+value="(\d+)"@', $content, $g))
	            $aid = $g[1];
	        if (preg_match('@id="_pid"\s+value="(\d+)"@', $content, $g))
	            $pid = $g[1];
	        if (preg_match('@id="_mid"\s+value="(\d+)"@', $content, $g))
	            $mid = $g[1];
	        if (empty($aid) || empty($pid) || empty($mid))
	            continue;
	        $links = array();
	        foreach ($chapters[1] as $chapter)
	        {
	            preg_match_all('@<td.*?>(.*?)</td>@ms', $chapter, $data);
	            if (empty($data) || empty($data[1]) || !is_array($data[1]))
	                continue;
	            $v = $data[1];
	            $link = array(
	                "AffId" => $this->info["AffId"],
	                "AffMerchantId" => $merinfo['IdInAff'],
	                "LinkDesc" => '',
	                "LinkStartDate" => '0000-00-00 00:00:00',
	                "LinkEndDate" => '0000-00-00 00:00:00',
	                "LinkPromoType" => 'link',
	                "LinkOriginalUrl" => '',
	                "LinkHtmlCode" => '',
	                "AffLinkId" => (int)$v[0],
	                "LinkName" => trim(html_entity_decode($v[1])),
	                "LinkCode" => '',
	                "LinkImageUrl" => '',
	                "LinkAffUrl" => '',
	                "DataSource" => 368,
	                "Type" => 'link'
	            );
	            $link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
	            $link['LinkAffUrl'] = sprintf('https://www.tagserve.sg/clickServlet?AID=%s&MID=%s&PID=%s&SID=%s&CID=%s&SUBID=' ,
	                $aid, $mid, $merinfo['IdInAff'], $this->sid, $link['AffLinkId']);
	            switch ($v[2])
	            {
	                case 'Banner';
	                $link['LinkImageUrl'] = sprintf('https://www.tagserve.sg/impressionServlet?AID=%s&MID=%s&PID=%s&SID=%s&CID=%s',
	                    $aid, $mid, $merinfo['IdInAff'], $this->sid, $link['AffLinkId']);
	                break;
	                case 'Text Link':
	                    break;
	                default:
	                    continue;
	                    break;
	            }
	            $link['LinkHtmlCode'] = create_link_htmlcode_image($link);
	            $links[] = $link;
	        }
	        echo sprintf("program:%s, %s link(s) found.\n", $merinfo['IdInAff'], count($links));
	        if (count($links) > 0)
	            $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
	        
	    }
	    $this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link');
	    return $arr_return;
	    
	}
	
	function GetAllLinksFromAffByMerID($merinfo)
	{
	    $check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "");
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		$url = sprintf('https://www.tagadmin.sg/affiliate_program_creative.html?pId=%s', $merinfo['IdInAff']);
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r['content'];
		preg_match_all('@<tr\s+.*?>(.*?)</tr>@ms', $content, $chapters);
		if (empty($chapters) || empty($chapters[1]) || !is_array($chapters[1]))
			return $arr_return;
		if (preg_match('@id="_aid"\s+value="(\d+)"@', $content, $g))
			$aid = $g[1];
		if (preg_match('@id="_pid"\s+value="(\d+)"@', $content, $g))
			$pid = $g[1];
		if (preg_match('@id="_mid"\s+value="(\d+)"@', $content, $g))
			$mid = $g[1];
		if (empty($aid) || empty($pid) || empty($mid))
			return $arr_return;
		$links = array();
		foreach ($chapters[1] as $chapter)
		{
			preg_match_all('@<td.*?>(.*?)</td>@ms', $chapter, $data);
			if (empty($data) || empty($data[1]) || !is_array($data[1]))
				continue;
			$v = $data[1];
			$link = array(
				"AffId" => $this->info["AffId"],
				"AffMerchantId" => $merinfo['IdInAff'],
				"LinkDesc" => '',
				"LinkStartDate" => '0000-00-00 00:00:00',
				"LinkEndDate" => '0000-00-00 00:00:00',
				"LinkPromoType" => 'link',
				"LinkOriginalUrl" => '',
				"LinkHtmlCode" => '',
				"AffLinkId" => (int)$v[0],
				"LinkName" => trim(html_entity_decode($v[1])),
				"LinkCode" => '',
				"LinkImageUrl" => '',
				"LinkAffUrl" => '',
				"DataSource" => 368,
				"type" => 'link'
			);
			$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
			$link['LinkAffUrl'] = sprintf('https://www.tagserve.sg/clickServlet?AID=%s&MID=%s&PID=%s&SID=%s&CID=%s&SUBID=' ,
					$aid, $mid, $merinfo['IdInAff'], $this->sid, $link['AffLinkId']);
			switch ($v[2])
			{
				case 'Banner';
				$link['LinkImageUrl'] = sprintf('https://www.tagserve.sg/impressionServlet?AID=%s&MID=%s&PID=%s&SID=%s&CID=%s',
						$aid, $mid, $merinfo['IdInAff'], $this->sid, $link['AffLinkId']);
				break;
				case 'Text Link':
					break;
				default:
					continue;
					break;
			}
			$link['LinkHtmlCode'] = create_link_htmlcode_image($link);
			$links[] = $link;
		}
		echo sprintf("program:%s, %s link(s) found.\n", $merinfo['IdInAff'], count($links));
		if (count($links) > 0)
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link', $merinfo['IdInAff']);
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
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "post",
			"postdata" => "categoryId=-1&programName=&merchantName=&records=-1&p=&time=1&changePage=&oldColumn=programmeId&sortField=programmeId&order=down",
			//"postdata" => "p=1&time=1&changePage=&oldColumn=programmeId&sortField=programmeId&order=down&records=-1",
		);		
		$r = $this->oLinkFeed->GetHttpResult("https://www.tagadmin.sg/affiliate_directory.html",$request);
		$result = $r["content"];
		
		$title = 'PIDProgramNameMIDMerchantNameCategoryCommissionRateCookieDurationAverageApprovalStatus';
		preg_match("/Affiliate Programs Directory.*?(<th.*?)<\/tr/is", $result, $m);		
		$tmp_arr = explode("</th>",$m[1]);
		$tmp_title = '';
		foreach($tmp_arr as $v){
			$v = preg_replace("/\s/", '', strip_tags($v));
			if($v){
				$tmp_title .= $v;
			}
		}
		if($title != $tmp_title){
			mydie("die: Title Wrong $title | $tmp_title .\n");
		}
				
		//parse HTML	
		$strLineStart = '<th>Cookie Duration</th>';
		
		$nLineStart = 0;
		while ($nLineStart >= 0){
			$nLineStart = stripos($result, $strLineStart, $nLineStart);
			if ($nLineStart === false) break;
			
			$strLineStart = "<tr";
			
			//id
			$strMerID = trim($this->oLinkFeed->ParseStringBy2Tag($result, "<td>", "</td>", $nLineStart));
			if ($strMerID === false) break;
			
			//name
			$strMerName = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, '<td>' , "</td>", $nLineStart)));
			if ($strMerName === false) break;
			
			//2016-09-01 new mid? 
			$tmp = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, '<td>' , "</td>", $nLineStart)));
			if ($tmp === false) break;
			
			//name
			$tmpName = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>' , "</td>", $nLineStart));
			if ($tmpName === false) break;
			
			$program_name = $strMerName." - ".$tmpName;
			
			$CategoryExt = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>' , "</td>", $nLineStart));
			$CommissionExt  = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>' , "</td>", $nLineStart));
			$CookieTime  = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>' , "</td>", $nLineStart));
			$CookieTime2  = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>' , "</td>", $nLineStart));
			
			$StatusInAffRemark = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>' , "</td>", $nLineStart));
			if($StatusInAffRemark == "Approved"){
				$Partnership = "Active";
			}elseif($StatusInAffRemark == "Pending"){
				$Partnership = "Pending";
			}elseif($StatusInAffRemark == "Declined"){
				$Partnership = "Declined";
			}else{
				$Partnership = "NoPartnership";
			}
			
			//activeDate
			/*$tmp_CreateDate = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td rowspan=5 valign=top>', '</td>', $nLineStart));
			if($tmp_CreateDate == "Application - Declined"){
				$Partnership = "NoPartnership";
				$CreateDate = "";
			}else{
				$Partnership = "Active";
				$CreateDate = str_ireplace("Member Since :", "", $tmp_CreateDate);
				$CreateDate = date("Y-m-d H:i:s", strtotime($CreateDate));
			}*/
			if(!$this->getStatus) {
                $request = array(
                    "AffId" => $this->info["AffId"],
                    "method" => "get",
                );

                $prgm_url = "https://www.tagadmin.sg/affiliate_program_detail.html?pId=$strMerID";
                $prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
                $prgm_detail = $prgm_arr["content"];

                //	$desc = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Program Description','<div class="value w70">'), "</div>"));
                $desc = preg_match("/Program Description.*?(<div class=\"value w70 htmlDescription\">.*?<\/div>)/is", $prgm_detail, $m);
                $desc = $m[0];

                $Homepage = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Program Landing URL', 'opennw(\''), "'")));
                $TermAndCondition = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Policy / Terms', '<div class="value w70 htmlDescription">'), "</div>"));
                $LogoUrl = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<div class="sideLogo" >','<img src="'), '"'));
                
                $Homepage = str_ireplace("?sourcecode=TAG", "", $Homepage);

                $AffDefaultUrl = trim(htmlspecialchars_decode($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('id="trackingString"', '>'), "</")));

                //$TargetCountryExt = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Country Availability','<div class="value w70">'), "</div>"));

                $arr_prgm[$strMerID] = array(
                    "AffId" => $this->info["AffId"],
                    "IdInAff" => $strMerID,
                    "Name" => addslashes($program_name),
                    "Homepage" => addslashes($Homepage),
                    //"TargetCountryExt" => $TargetCountryExt,
                    "TermAndCondition" => addslashes($TermAndCondition),
                    "CategoryExt" => addslashes($CategoryExt),
                    "CommissionExt" => addslashes($CommissionExt),
                    "CookieTime" => addslashes($CookieTime),
                    "StatusInAffRemark" => addslashes($StatusInAffRemark),
                    "StatusInAff" => 'Active',                        //'Active','TempOffline','Offline'
                    "Partnership" => $Partnership,                        //'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
                    "LastUpdateTime" => date("Y-m-d H:i:s"),
                    "DetailPage" => $prgm_url,
                    "AffDefaultUrl" => addslashes($AffDefaultUrl),
                    "Description" => addslashes($desc),
                	"LogoUrl" => addslashes($LogoUrl),
                );
            } else {
                $arr_prgm[$strMerID] = array(
                    "AffId" => $this->info["AffId"],
                    "IdInAff" => $strMerID,
                    "Name" => addslashes($program_name),
                    "CategoryExt" => addslashes($CategoryExt),
                    "CommissionExt" => addslashes($CommissionExt),
                    "CookieTime" => addslashes($CookieTime),
                    "StatusInAffRemark" => addslashes($StatusInAffRemark),
                    "StatusInAff" => 'Active',                        //'Active','TempOffline','Offline'
                    "Partnership" => $Partnership,                        //'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
                    "LastUpdateTime" => date("Y-m-d H:i:s"),
                );
            }
			//print_r($arr_prgm);exit;
			$program_num++;
			
			if(count($arr_prgm) >= 100){
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
				$arr_prgm = array();
			}
		}
		
				// for maybank
				$request = array(
					"AffId" => $this->info["AffId"],
					"method" => "post",
					//"postdata" => "categoryId=-1&programName=&merchantName=&records=-1&p=&time=1&changePage=&oldColumn=programmeId&sortField=programmeId&order=down",
					"postdata" => "p=1&time=1&changePage=&oldColumn=programmeId&sortField=programmeId&order=down&records=-1",
				);		
				$r = $this->oLinkFeed->GetHttpResult("https://www.tagadmin.sg/affiliate_merchant.html",$request);
				$result = $r["content"];
				
				//parse HTML	
				$strLineStart = '<th>Cookie Duration</th>';
				
				$nLineStart = 0;
				while ($nLineStart >= 0){
					$nLineStart = stripos($result, $strLineStart, $nLineStart);
					if ($nLineStart === false) break;
					
					$strLineStart = "<tr";
					
					//id
					$strMerID = trim($this->oLinkFeed->ParseStringBy2Tag($result, "<td>", "</td>", $nLineStart));
					if ($strMerID === false) break;
					
					//name
					$strMerName = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, '<td>' , "</td>", $nLineStart)));
					if ($strMerName === false) break;
					
					//name
					$tmpName = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>' , "</td>", $nLineStart));
					if ($tmpName === false) break;
					
					$program_name = $strMerName." - ".$tmpName;
										
					$CategoryExt = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>' , "</td>", $nLineStart));
					$CommissionExt  = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>' , "</td>", $nLineStart));
					$CookieTime  = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>' , "</td>", $nLineStart));
					$CookieTime2  = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>' , "</td>", $nLineStart));

					$StatusInAffRemark = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>' , "</td>", $nLineStart));
					if($StatusInAffRemark == "Approved"){
						$Partnership = "Active";
					}elseif($StatusInAffRemark == "Pending"){
						$Partnership = "Pending";
					}elseif($StatusInAffRemark == "Declined"){
						$Partnership = "Declined";
					}else{
						$Partnership = "NoPartnership";
					}
					//echo $StatusInAffRemark."\n";
					
					if(!in_array($strMerID, array(146,150,151,152,153,154,155,156))) continue;
					
					$request = array(
						"AffId" => $this->info["AffId"],
						"method" => "get",				
					);
					
					$prgm_url = "https://www.tagadmin.sg/affiliate_program_detail.html?pId=$strMerID";
					$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
					$prgm_detail = $prgm_arr["content"];
					
					$desc = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Program Description','<div class="value w70 htmlDescription">'), "</div>"));
					$Homepage = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Program Landing URL', 'opennw(\''), "'")));
					$TermAndCondition = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Policy / Terms','<div class="value w70 htmlDescription">'), "</div>"));
					
					$Homepage = str_ireplace("?sourcecode=TAG", "", $Homepage);
					
					$AffDefaultUrl = trim(htmlspecialchars_decode($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('id="trackingString"','>'), "</")));
					
							
					$arr_prgm[$strMerID] = array(
						"AffId" => $this->info["AffId"],	
						"IdInAff" => $strMerID,	
						"Name" => addslashes($program_name),
						"Homepage" => addslashes($Homepage),				
						"Description" => addslashes($desc),
						"TermAndCondition" => addslashes($TermAndCondition),
						"CategoryExt" => addslashes($CategoryExt),
						"CommissionExt" => addslashes($CommissionExt),
						"CookieTime" => addslashes($CookieTime),
						"StatusInAffRemark" => addslashes($StatusInAffRemark),
						"StatusInAff" => 'Active',						//'Active','TempOffline','Offline'
						"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'						
						"LastUpdateTime" => date("Y-m-d H:i:s"),
						"DetailPage" => $prgm_url,
						"AffDefaultUrl" => $AffDefaultUrl
					);
					//print_r($arr_prgm);exit;
					$program_num++;
					
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
?>
