<?php

require_once 'text_parse_helper.php';

class LinkFeed_8_AvantLink
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->getStatus = false;
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		if(SID == 'bdg01'){
			$this->AffiliateID = '166227';
			$this->WebiteID = '207767';
			$this->SubscriptionID = '68281';
			$this->API_key = '02c0781678c24e8b6bb2cd7724b2b8f0';
		}else{
			$this->AffiliateID = '';
			$this->WebiteID = '';
		}
	}
	
	function Login(){
		$islogined = false;
		/* $this->oLinkFeed->clearHttpInfos($this->info["AffId"]);
		$strUrl = "https://www.avantlink.com/signin";
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "get",
			"postdata" => "",
		);
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];
		$_token = urlencode($this->oLinkFeed->ParseStringBy2Tag($result, array('name="_token"', 'value="'), '"'));		

		$this->info["AffLoginPostString"] .= "&_token=".$_token; */
		//echo $this->info["AffLoginPostString"];		
		
		/* $request = array(
				"AffId" => $this->info["AffId"],
				"method" => 'OPTIONS',
				"no_ssl_verifyhost" => true,
				"postdata" => '',
				"addheader" => array(
						"Access-Control-Request-Headers: authorization, content-type, x-api-locale, x-api-version",
						"Access-Control-Request-Method: POST",
						"Host: api.avantlink.com",
						"Origin: https://dashboard.avantlink.com",
						"Referer: https://dashboard.avantlink.com/login",
						"X-API-Version: 2.0",
				)
		);
		$re = $this->oLinkFeed->GetHttpResult($this->info["AffLoginUrl"], $request);
		print_r($re);exit; */
		
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => $this->info["AffLoginMethod"],
			//"no_ssl_verifyhost" => true,
			"postdata" => $this->info["AffLoginPostString"]."&user_device_id=e070660a-65ae-469a-bce9-11db45129119",
																		   //e070660a-65ae-469a-bce9-11db45129119
																		   //e070660a-65ae-469a-bce9-11db45129119 eadffcc3-6f28-4ac1-971b-13ee16bcbce6
			"addheader" => array(
					"authorization: ecbd33850ec41b6f41700db1575f5adb75db3a48;403f2c5c7b754d94b95752f23b4e380d4b994e55;1478209357;0067300c-5389-4158-abb7-5e701888b098;P100Y",
					/* "content-type: application/json",
					"Host: api.avantlink.com",
					"Origin: https://dashboard.avantlink.com",
					"Referer: https://dashboard.avantlink.com/login", */
					//"X-API-Locale: en_US",
					"X-API-Version: 2.0",
					//"user_device_id: e070660a-65ae-469a-bce9-11db45129119",
			)
		);
		
		$re = $this->oLinkFeed->GetHttpResult($this->info["AffLoginUrl"], $request);
		print_r($re);exit;
		if($re["code"] != 200){
			print_r($re['content']."<br>\n");
			mydie("verify login failed( Authorization failed) <br>\n");
		}
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => 'get',
			"no_ssl_verifyhost" => true,
			/* "addheader" => array(
					"Upgrade-Insecure-Requests: 1",
					"Pragma: no-cache",
					"Host: classic.avantlink.com",
					"Referer: https://dashboard.avantlink.com/login"
			) */
		);
		$r = $this->oLinkFeed->GetHttpResult("https://classic.avantlink.com/affiliate/", $request);
		//$r = $this->oLinkFeed->GetHttpResult("https://classic.avantlink.com/affiliate/merchant_details.php?lngMerchantId=13978", $request);
		
		print_r($r);exit;
		if($r["code"] == 200){
			if(stripos($r["content"], $this->info["AffLoginVerifyString"]) !== false)
			{
				echo "verify succ: " . $this->info["AffLoginVerifyString"] . "\n";
				$islogined = true;
			}else{
				echo "verify login failed(".$this->info["AffLoginVerifyString"].") <br>\n";
			}
		}
		
		if(!$islogined){
			mydie("die: login failed for aff({$this->info["AffId"]}) <br>\n");
		}else{
			$_md5 = urlencode($this->oLinkFeed->ParseStringBy2Tag($r["content"], 'href="/signin/account/3894/', '"'));
			$this->info["AffLoginSuccUrl"] .= $_md5;
			$request = array("AffId" => $this->info["AffId"], "method" => "get");
			$r = $this->oLinkFeed->GetHttpResult($this->info["AffLoginSuccUrl"], $request);			
		}
	}

	function getCouponFeed()
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", );
		$url = "http://www.avantlink.com/coupons/coupon_feed.php?cfi=$this->SubscriptionID&pw=$this->WebiteID";
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r["content"];
		$data = @fgetcsv_str($content);
		$links = array();
		foreach ((array)$data as $v)
		{
			if (empty($v['Coupon Link']))
				continue;
			$link = array(
					"AffId" => $this->info["AffId"],
					"LinkName" =>  $v['Coupon Offer'],
					"LinkDesc" =>  '',
					"LinkStartDate" => parse_time_str($v['Coupon Start'], null, false),
					"LinkEndDate" => parse_time_str($v['Coupon Expiration'], null, true),
					"LinkPromoType" => 'COUPON',
					"LinkHtmlCode" => '',
					"LinkCode" => $v['Coupon Code'],
					"LinkOriginalUrl" => '',
					"LinkImageUrl" => '',
					"LinkAffUrl" => $v['Coupon Link'],
					"DataSource" => 11,
			);
			if (preg_match('@\&mi=(\d+)&mli=(\d+)@', $link['LinkAffUrl'], $g))
			{
				$link['AffMerchantId'] = $g[1];
				$link['AffLinkId'] = 'c_' . $g[2];
			}
			if (empty($link['LinkCode']))
			{
				$code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc']);
				if (!empty($code))
					$link['LinkCode'] = $code;
			}
			if ($v["Coupon Type"] == 'image')
			{
				if (preg_match('@^http:@', $v['Coupon Offer']))
					$link['LinkImageUrl'] = $v['Coupon Offer'];
				if (!empty($link['LinkCode']))
					$link['LinkName'] = sprintf('%s, Use Coupon Code: %s', $v['Merchant Name'], $link['LinkCode']);
				else 
					$link['LinkName'] = sprintf('%s', $v['Merchant Name']);
			}
			$link['LinkHtmlCode'] = create_link_htmlcode_image($link);
			if (empty($link['AffLinkId']) || empty($link['AffLinkId']) )
				continue;
            elseif(empty($link['LinkName'])){
                $link['LinkPromoType'] = 'link';
            }
			$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
			$arr_return["AffectedCount"] ++;
			$links[] = $link;
			if (($arr_return["AffectedCount"] % 100) == 0)
			{
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
				$links = array();
			}
		}
		//print_r($links);exit;
		echo sprintf("get coupons complete. %s links(s) found. \n", $arr_return["AffectedCount"]);
		if(count($links) > 0)
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);

		return $arr_return;
	}

	/* function GetAllLinksFromAffByMerID($merinfo)
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, );
		$request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "", );
		$this->Login();
		$url = sprintf("https://classic.avantlink.com/affiliate/ads.php?lngMerchantId=%s", $merinfo['IdInAff']);
		$r = $this->oLinkFeed->GetHttpResult($url,$request);
		$content = $r["content"];
		preg_match_all('@\[(\'<a.*?)\]\s,@', $content, $chapters);
		if (!empty($chapters) && !empty($chapters[1]) && is_array($chapters[1]))
		{
			$links = array();
			foreach ($chapters[1] as $line)
			{
				$v = mem_getcsv($line, ',', "'");
				if (empty($v) || !is_array($v) || count($v) < 10)
					continue;
				$link = array(
						"AffId" => $this->info["AffId"],
						"AffMerchantId" => $merinfo['IdInAff'],
						"LinkName" =>  '',
						"LinkDesc" =>  '',
						"LinkStartDate" => parse_time_str($v[6], null, false),
						"LinkEndDate" => parse_time_str($v[7], null, false),
						"LinkPromoType" => '',
						"LinkHtmlCode" => '',
						"LinkCode" => '',
						"LinkOriginalUrl" => '',
						"LinkImageUrl" => '',
						"LinkAffUrl" => '',
						"DataSource" => 12,
				);
				if (preg_match('@<img src="(.*?)"@', $v[2], $g))
				{
					$link['LinkImageUrl'] = 'http://www.avantlink.com/affiliate/' . $g[1];
					$link['LinkName'] = trim(html_entity_decode(strip_tags($v[3])));
				}
				else
				{
					$link['LinkName'] = trim(html_entity_decode(strip_tags($v[3])));
				}
				$link['AffLinkId'] = $v[1];
				                                
				$link['LinkAffUrl'] = sprintf("http://www.avantlink.com/click.php?tt=ml&ti=%s&pw={$this->WebiteID}", $link['AffLinkId']);
				
				if (empty($link['AffLinkId']) || empty($link['LinkAffUrl']))
					continue;
                elseif(empty($link['LinkName'])){
                    $link['LinkPromoType'] = 'link';
                }
					
				$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName'] . ' ' . $link['LinkDesc']);
				$code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc']);
				if (!empty($code) || $v[8] == 'Yes')
				{
					$link['LinkCode'] = $code;
					$link['LinkPromoType'] = 'COUPON';
				}
				$link['LinkHtmlCode'] = create_link_htmlcode_image($link);
				$link['AffLinkId'] = 'l_' . $link['AffLinkId'];
				$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
				$arr_return["AffectedCount"] ++;
				
				$links[] = $link;
			}
			echo sprintf("program:%s, %s links(s) found.\n", $merinfo['IdInAff'], count($links));
			if(count($links) > 0)
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		}
		return $arr_return;
	} */
	
	function GetAllLinksFromAffByMerID($merinfo)
	{
		$aff_id = $this->info["AffId"];
		$AffMerchantId = $merinfo["AffMerchantId"];
	
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0,);
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "",);
		$url = sprintf("http://www.avantlink.com/api.php?affiliate_id=%s&module=AdSearch&output=tab&website_id=%s&merchant_id=%s", $this->AffiliateID, $this->WebiteID, $merinfo['IdInAff']);
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		if (empty($r) || empty($r['content']))
			return $arr_return;
		$data = csv_string_to_array($r['content'], "\t","\r\n");
		if (empty($data) || !is_array($data))
			return $arr_return;
		$links = array();
		//var_dump($data);exit;
		foreach ($data as $v)
		{
			$link = array(
					"AffId" => $merinfo["AffId"],
					"AffMerchantId" => $merinfo["AffMerchantId"],
					"AffLinkId" => $v['Ad Id'],
					"LinkName" => $v['Ad Title'],
					"LinkDesc" => '',
					"LinkOriginalUrl" => "",
					"LinkImageUrl" => "",
					"LinkPromoType" => 'link',
					"LinkAffUrl" => $v['Ad Url'],
					"DataSource" => "12",
			);
			if (empty($link['AffLinkId']) )
				continue;
				
			if (!empty($v['Coupon Code']))
				$link['LinkCode'] = $v['Coupon Code'];
			
			if ($v['Coupon'] == 'Yes')
				$link['LinkPromoType'] = 'DEAL';
			
			if ($v['Ad Type'] == 'video')
				continue;
			elseif ($v['Ad Type'] == 'image')
				$link['LinkImageUrl'] = $v['Ad Content'];
			else 
				$link['LinkDesc'] = $v['Ad Content'];
			
			if (!empty($v['Ad Start Date']))
				$link['LinkStartDate'] = date('Y-m-d H:i:s', strtotime($v['Ad Start Date']));
			if (!empty($v['Ad Expiration Date']))
				$link['LinkEndDate'] = date('Y-m-d H:i:s', strtotime($v['Ad Expiration Date'].' 23:59:59'));
			$link['LinkHtmlCode'] = create_link_htmlcode($link);
			$links[] = $link;
			$arr_return["AffectedCount"] ++;
			//print_r($link);
		}
		if(sizeof($links) > 0)
		{
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
			$arrToUpdate = array();
		}
		return $arr_return;
	}
			
	function getProgramByStatus($status, $country)	
	{	
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "post",
			"postdata" => "", 
		);
		
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		
		echo "get $status merchants for " . $this->info["AffName"] . "\n";
		
		if($country == "ca"){
			$domain = "https://classic.avantlink.ca";
			$TargetCountryExt = 'CA';
		}else{			
			$domain = "https://classic.avantlink.com";
			$TargetCountryExt = 'US';
		}
		$strUrl = "{$domain}/affiliate/merchants.php";
		$request["postdata"] = "strRelationStatus=" . $status . "&lngMerchantCategoryId=0&strProductKeywords=&cmdFilter=Get+Merchants";
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];
		$Cnt = 0;
		$UpdateCnt = 0;

		//parse HTML
		$nLineStart = 0;
		$strLinksArrayData = $this->oLinkFeed->ParseStringBy2Tag($result, 'var g_arrData_rptlist_201=[', '];', $nLineStart);
		$arrTmpLine = explode("\n", $strLinksArrayData);
		foreach($arrTmpLine as $strLine)
		{
			$strLine = trim($strLine);
			if ($strLine == '') continue;
			
			if(preg_match("/^,?\\[(.*)\\]$/",$strLine,$matches)) $strLine = $matches[1];
			else
			{
				echo "line skipped: $strLine\n";
			}
			
			eval("\$js_mer_info = array($strLine);");
			if($js_mer_info === false) mydie("die: eval failed: $strLine\n");
			$StatusInAff = "Active";
			$StatusInAffRemark = "";
			if($status == "active"){
				list($temp,$temp2,$strMerID,$strMerName,$CategoryExt,$Commission_Action,$Commission_Type,$Commission_Rate,$ReturnDays,$Sales_Volume,$Conversion_Rate,$Reversal_Rate,$Average_Sale_Amount,$Date_Joined) = $js_mer_info;
				$JoinDate = date("Y-m-d H:i:s", strtotime($Date_Joined));			
				$CommissionExt = $Commission_Rate;
				$Partnership = "Active";	
			}
			elseif($status == "no-relationship"){
				list($temp,$strMerName,$CategoryExt,$Commission_Action,$Commission_Type,$Commission_Rate,$ReturnDays,$Sales_Volume,$Conversion_Rate,$Reversal_Rate,$Average_Sale_Amount) = $js_mer_info;
				
				$JoinDate = "";
				$CommissionExt = $Commission_Rate;
				$Partnership = "NoPartnership";
			}
			elseif($status == "pending"){
				list($temp,$strMerName,$CategoryExt,$Commission_Action,$Commission_Type,$Commission_Rate,$ReturnDays,$Sales_Volume,$Conversion_Rate,$Reversal_Rate,$Average_Sale_Amount) = $js_mer_info;
				
				$JoinDate = "";
				$CommissionExt = $Commission_Rate;
				$Partnership = "Pending";				
			}
			else{
				list($temp,$reason,$strMerName,$CategoryExt,$Commission_Action,$Commission_Type,$Commission_Rate,$ReturnDays,$Sales_Volume,$Conversion_Rate,$Reversal_Rate,$Average_Sale_Amount) = $js_mer_info;
				
				$JoinDate = "";
				$CommissionExt = $Commission_Rate;
				//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
				if($reason == "No longer active in the AvantLink network."){
					$StatusInAff = "Offline";
					$Partnership = "Expired";
				}elseif($reason == "Merchant denied your application to their program."){
					$Partnership = "Declined";
				}elseif($reason == "Merchant terminated their association with you."){
					$Partnership = "Expired";
				}else{
					$Partnership = "Expired";					
				}
				$StatusInAffRemark = $reason;
			}
			
			//a href="merchant_details.php?lngMerchantId=10072">800-Ski-Shop.com</a>
			//'Merchant denied your application to their program.','Altrec.com Outdoors','Outdoor/Recreation','sale','percent',' 10.00%','120','1.48%','15.31%','Configure inactive link handling '
			//pending: '<a href="merchant_details.php?lngMerchantId=11109">Brooklyn Battery Works</a>'
			if(preg_match("/lngMerchantId=([0-9]*)\\\">(.*)<\\/a>/",$strMerName,$matches))
			{
				//double check $strMerID
				$strMerID = $matches[1];
				$strMerName = trim(strip_tags($matches[2]));
			}
			else
			{
				mydie("die: parse failed: $strLine\n");
			}
			
			/*if($status == "active"){
				$StatusInAff = "Active";
			}
			elseif($status == "inactive"){				
				$StatusInAff = "Active";
			}
			elseif($status == "no-relationship"){			
				$StatusInAff = "Active";
			}
			elseif($status == "pending"){
				$StatusInAff = "Active";
			}
			else{
				mydie("die: wrong status($status)");
			}*/
			
			$RankInAff = trim($this->oLinkFeed->ParseStringBy2Tag($Sales_Volume, array('volume_'), '.'));
									
			//program
            if(!$this->getStatus) {
                //program_detail
                $prgm_url = "{$domain}/affiliate/merchant_details.php?lngMerchantId=$strMerID";
                $prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
                $prgm_detail = $prgm_arr["content"];

                $prgm_line = 0;

                //$StatusInAff = $strStatus;//'Active','TempOffline','Expired'
                //statusinfaffremark
                //$StatusInAffRemark = $strStatus;

                $Homepage = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<strong>Merchant:</strong>', '<td><a href="'), '"', $prgm_line));
                $Contact = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<strong>Contact:</strong>', '<td>'), '</td>', $prgm_line));
                $Contact_email = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<strong>Contact Email:</strong>', 'mailto:'), '">', $prgm_line));
                $Contacts = $Contact . ", Emial: " . $Contact_email;

                $desc = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<strong>Program Description:</strong>', '<td>'), '</td>', $prgm_line));
                $NumberOfOccurrences = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<strong>Referral Ocurrences:</strong>', '<td>'), '</td>', $prgm_line));
                /*if($NumberOfOccurrences == "Unlimited"){
                    $NumberOfOccurrences = -1;
                }*/

                $SubAffPolicyExt = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<strong>Reversal Policy:</strong>', '<td>'), '</td>', $prgm_line));

                $BonusExt = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<strong>Performance Incentives:</strong>', '<td>'), '</table>', $prgm_line));
                if ($BonusExt) {
                    $BonusExt .= "</table>";
                }

                $TermAndCondition = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<strong>Terms and Conditions:</strong>', '<td valign="top">'), '</td>', $prgm_line));
                if (empty($TermAndCondition))
                    $TermAndCondition = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<strong>Terms and Conditions:</strong>', '<td>'), '</td>', $prgm_line));

                $arr_prgm[$strMerID] = array(
                    "Name" => addslashes(html_entity_decode(trim($strMerName))),
                    "AffId" => $this->info["AffId"],
                    "CategoryExt" => addslashes($CategoryExt),
                    "TargetCountryExt" => $TargetCountryExt,
                    "Contacts" => addslashes($Contacts),
                    "IdInAff" => $strMerID,
                    "RankInAff" => intval($RankInAff),
                    "CreateDate" => $JoinDate,
                    "StatusInAff" => $StatusInAff,                        //'Active','TempOffline','Offline'
                    "StatusInAffRemark" => $StatusInAffRemark,
                    "Partnership" => $Partnership,                        //'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
                    "Description" => addslashes($desc),
                    "Homepage" => $Homepage,
                    "CommissionExt" => addslashes($CommissionExt),
                    "BonusExt" => addslashes($BonusExt),
                    "CookieTime" => $ReturnDays,
                    "NumberOfOccurrences" => addslashes($NumberOfOccurrences),
                    "TermAndCondition" => addslashes($TermAndCondition),
                    "SubAffPolicyExt" => addslashes($SubAffPolicyExt),
                    "LastUpdateTime" => date("Y-m-d H:i:s"),
                    "DetailPage" => $prgm_url,
                    "SupportDeepUrl" => "YES"
                );
            } else {
                $arr_prgm[$strMerID] = array(
                    "Name" => addslashes(html_entity_decode(trim($strMerName))),
                    "AffId" => $this->info["AffId"],
                    "CategoryExt" => addslashes($CategoryExt),
                    "TargetCountryExt" => $TargetCountryExt,
                    "IdInAff" => $strMerID,
                    "RankInAff" => intval($RankInAff),
                    "CreateDate" => $JoinDate,
                    "StatusInAff" => $StatusInAff,                        //'Active','TempOffline','Offline'
                    "StatusInAffRemark" => $StatusInAffRemark,
                    "Partnership" => $Partnership,                        //'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
                    "CommissionExt" => addslashes($CommissionExt),
                    "CookieTime" => $ReturnDays,
                    "LastUpdateTime" => date("Y-m-d H:i:s"),
                    "SupportDeepUrl" => "YES"
                );
            }
			
			$Cnt++;
	
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
		
		$objProgram->setCountryInt($this->info["AffId"]);		
		
		return $Cnt;
	}

    function GetStatus(){
        $this->getStatus = true;
        $this->GetProgramFromAff();
    }

	function GetProgramByPage()
	{
		echo "\tGet Program by page start\r\n";		
		$program_num = 0;
		//step 1,login
		$this->Login();

		//step 2,get all exists merchant
		//$arrAllExistsMerchants = $this->oLinkFeed->GetAllExistsAffMerIDForCheckByAffID($this->info["AffId"]);

		$arrStatus4List = array("active","inactive","no-relationship","pending");
		foreach($arrStatus4List as $status)
		{
			$program_num += $this->getProgramByStatus($status, "us");
		}
		
		echo "\tGet Program by page end\r\n";
		
		if($program_num < 10){
			mydie("die: program count < 10, please check program.\n");
		}
		
		echo "\tUpdate ({$program_num}) program.\r\n";
		
	}
	
	function GetProgramFromAff()
	{	
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
		
		$this->GetProgramByApi();
		//$this->GetProgramByPage();		
		$this->checkProgramOffline($this->info["AffId"], $check_date);

		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
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
	
	function GetProgramByApi ()
	{
		echo "\tGet Program by api start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "",);
		
		$url = "https://classic.avantlink.com/api.php?affiliate_id=$this->AffiliateID&auth_key=$this->API_key&module=AssociationFeed&output=xml";
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$r = simplexml_load_string($r['content']);
		$data = json_decode(json_encode($r), true);
		//var_dump($data);exit;
		if(!empty($data)){
			foreach ($data['Table1'] as $v) {
				$IdInAff = $v['Merchant_Id'];
				
				$StatusInAffRemark = $v["Association_Status"];
				if ($StatusInAffRemark == 'active'){
					$StatusInAff = 'Active';
					$Partnership = 'Active';
				}else if ($StatusInAffRemark == 'pending'){
					$StatusInAff = 'Active';
					$Partnership = 'Pending';
				}else if ($StatusInAffRemark == 'denied'){
					$StatusInAff = 'Active';
					$Partnership = 'Declined';
				}else{
					$StatusInAff = 'Active';
					$Partnership = 'NoPartnership';
				}
				
				if (!empty($v['Commission_Rate']))
					$CommissionExt = $v['Commission_Rate'];
				else 
					$CommissionExt = $v['Default_Program_Commission_Rate'];
				
				if (!empty($v['Merchant_Logo']))
					$logoUrl = $v['Merchant_Logo'];
				
				$JoinData = (!empty($v['Date_Joined']))?$v['Date_Joined']:'';
				
				$CategoryExt = $v['Merchant_Category_Name'];
				$AffDefaultUrl = (!empty($v['Default_Tracking_URL']))?$v['Default_Tracking_URL']:'';
				
				$arr_prgm[$IdInAff] = array(
						"Name" => addslashes(trim($v['Merchant_Name'])),
						"IdInAff" => $v['Merchant_Id'],
						"AffId" => $this->info["AffId"],
						"Homepage" => addslashes($v['Merchant_URL']),
						//"RankInAff" => $RankInAff,
						"CreateDate" => date('Y-m-d H:i:s', strtotime($JoinData)),
						"StatusInAffRemark" => addslashes($StatusInAffRemark),
						"StatusInAff" => $StatusInAff,                        //'Active','TempOffline','Offline'
						"Partnership" => $Partnership,                        //'NoPartnership','Active','Pending','Declined','Expired','Removed'
						"LastUpdateTime" => date("Y-m-d H:i:s"),
						"MobileFriendly" => 'UNKNOWN',
						"SupportDeepUrl" => 'UNKNOWN',
						"CommissionExt" => addslashes($CommissionExt),
						"CategoryExt" => addslashes($CategoryExt),
						"AffDefaultUrl" => addslashes($AffDefaultUrl),
				);
				$program_num++;
				if (count($arr_prgm) >= 100) {
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					$arr_prgm = array();
				}
			}
			if (count($arr_prgm)) {
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				$arr_prgm = array();
			}
		}
		echo "\tGet Program by api end\r\n";
		if ($program_num < 10) {
			mydie("die: program count < 10, please check program.\n");
		}
		echo "\tUpdate ({$program_num}) program.\r\n";
		echo "\tSet program country int.\r\n";
		if (!$this->getStatus) {
			$objProgram->setCountryInt($this->info["AffId"]);
		}
	}
}
?>
