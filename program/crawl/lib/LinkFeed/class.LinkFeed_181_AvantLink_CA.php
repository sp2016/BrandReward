<?php

require_once 'text_parse_helper.php';

class LinkFeed_181_AvantLink_CA
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
        $this->getStatus = false;
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		if(SID == 'bdg01'){
			$this->AffiliateID = '166479';
			$this->WebiteID = '208115';
			$this->SubscriptionID = '68285';
			$this->API_key = '1595ce58844f11745ccd798d78b70406';
		}else{
			$this->AffiliateID = '';
			$this->WebiteID = '';
		}
	}	

	function Login(){
		$islogined = false;
		$this->oLinkFeed->clearHttpInfos($this->info["AffId"]);
		$strUrl = "https://www.avantlink.com/signin";
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "get",
			"postdata" => "",
		);
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];
		$_token = urlencode($this->oLinkFeed->ParseStringBy2Tag($result, array('name="_token"', 'value="'), '"'));		

		$this->info["AffLoginPostString"] .= "&_token=".$_token;
		//echo $this->info["AffLoginPostString"];		
		
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => $this->info["AffLoginMethod"],
			"postdata" => $this->info["AffLoginPostString"]			
		);
		$r = $this->oLinkFeed->GetHttpResult($this->info["AffLoginUrl"], $request);		
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
		
		//从US跳转到CA
		$time = explode (" ", microtime () );
		$time = $time [1] . ($time [0] * 1000);
		$time2 = explode ( ".", $time );
		$time = $time2 [0];//取毫秒级的时间戳
		
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => 'post',
				"postdata" => "xjxfun=ajaxChangeLogin&xjxr={$time}&xjxargs[]=CA_180171&xjxargs[]=%3C!%5BCDATA%5B%2Faffiliate%2F%5D%5D%3E"
		);
		//echo $time;exit;
		$skip_url = "https://classic.avantlink.com/affiliate/index.php";
		$skip_r = $this->oLinkFeed->GetHttpResult($skip_url, $request);
		
		if($skip_r["code"] == 200){
			$check_url = $this->oLinkFeed->ParseStringBy2Tag($skip_r['content'], "window.location.href = '", "';]]></cmd>");
			//print_r($check_url);exit;
			$request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "",);
			$r = $this->oLinkFeed->GetHttpResult($check_url,$request);
			if($r["code"] == 200){
				if(stripos($r["content"], $this->info["AffLoginVerifyString"]) !== false)
				{
					echo "verify succ: " . $this->info["AffLoginVerifyString"] . "change site succ"."\n";
				}else{
					echo "verify change site failed(".$this->info["AffLoginVerifyString"].") <br>\n";
				}
			}
		}
	}
	
	function getMerchantByStatus($status,&$arrAllExistsMerchants)
	{
		$arr_return = array("AffectedCount" => 0,"UpdatedCount" => 0,);
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "post",
			"postdata" => "", 
		);
		
		echo "get $status merchants for " . $this->info["AffName"] . "\n";
		
		$strUrl = "https://www.avantlink.ca/affiliate/merchants.php";
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
			
			if($status == "active") list($temp,$temp2,$strMerID,$strMerName,) = $js_mer_info;
			elseif($status == "no-relationship") list($temp,$strMerName) = $js_mer_info;
			elseif($status == "pending") list($temp,$strMerName) = $js_mer_info;
			else list($temp,$reason,$strMerName,,,) = $js_mer_info;
			
			//a href="merchant_details.php?lngMerchantId=10072">800-Ski-Shop.com</a>
			//'Merchant denied your application to their program.','Altrec.com Outdoors','Outdoor/Recreation','sale','percent',' 10.00%','120','1.48%','15.31%','Configure inactive link handling '
			//pending: '<a href="merchant_details.php?lngMerchantId=11109">Brooklyn Battery Works</a>'
			if(preg_match("/lngMerchantId=([0-9]*)\\\">(.*)<\\/a>/",$strMerName,$matches))
			{
				//double check $strMerID
				$strMerID = $matches[1];
				$strMerName = trim($matches[2]);
			}
			else
			{
				mydie("die: parse failed: $strLine\n");
			}
			
			if($status == "active") $strStatus = 'approval';
			elseif($status == "inactive") $strStatus = 'siteclosed';
			elseif($status == "no-relationship") $strStatus = 'approval';
			elseif($status == "pending") $strStatus = 'pending';
			else mydie("die: wrong status($status)");
			
			$arr_return["AffectedCount"] ++;
			$arr_update = array(
				"AffMerchantId" => $strMerID,
				"AffId" => $this->info["AffId"],
				"MerchantName" => $strMerName,
				"MerchantEPC30d" => "-1",
				"MerchantEPC" => "-1",
				"MerchantStatus" => $strStatus,
				"MerchantRemark" => "", //here,we save programmeid to MerchantRemark
			);
			$this->oLinkFeed->fixEnocding($this->info,$arr_update,"merchant");
			if($this->oLinkFeed->UpdateMerchantToDB($arr_update,$arrAllExistsMerchants)) $arr_return["UpdatedCount"] ++;
		}
		echo "get $status merchants for " . $this->info["AffName"] . " finish\n";
		//print_r($arr_return);
		return $arr_return;
	}
	
	function GetMerchantListFromAff()
	{
		//step 1,login
		$this->Login();

		//step 2,get all exists merchant
		$arrAllExistsMerchants = $this->oLinkFeed->GetAllExistsAffMerIDForCheckByAffID($this->info["AffId"]);

		$arr_return = array("AffectedCount" => 0,"UpdatedCount" => 0,);

		$arrStatus4List = array("active","inactive","no-relationship","pending");
		foreach($arrStatus4List as $status)
		{
			$one_status_result = $this->getMerchantByStatus($status,$arrAllExistsMerchants);
			$arr_return["AffectedCount"] += $one_status_result["AffectedCount"];
			$arr_return["UpdatedCount"] += $one_status_result["UpdatedCount"];
		}

		$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateAllExistsAffMerIDButCannotFetched($this->info["AffId"], $arrAllExistsMerchants);
		return $arr_return;
	}

	/* function getCouponFeed()
	{
		$arr_return = array(
			"AffectedCount" => 0,
			"UpdatedCount" => 0,
			"Detail" => array(),
		);
		return $arr_return; //added by liwei 2013 08 15
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "post",
			"postdata" => "", 
		);
		
		if($this->debug) print "Getting CouponFeed for LC <br>\n";

		$title = '"Merchant Name","Coupon Type","Coupon Offer","Coupon Code","Coupon Link","Coupon Start","Coupon Expiration","Coupon Last Modified"';
		$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"feed.dat","cache_feed");
		if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
		{
			//login
			$this->Login();
			
			//step 1, download coupon feed directly
			if($this->debug) print "Get CouponFeed Data  <br>\n";
			$strUrl = "http://www.avantlink.ca/coupons/coupon_feed.php?cfi=4257&pw=4724";
			$request["method"] = "get";
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$result = $r["content"];
			
			if(stripos($result,$title) === false)
			{
				print_r($r);
				mydie("die: get feed failed, title not found \n");
			}
			
			$this->oLinkFeed->fileCachePut($cache_file,$result);
		}
		if(!file_exists($cache_file)) return $arr_return;
		
		$all_merchant = $this->oLinkFeed->getAllAffMerchant($this->info["AffId"]);
		
		//Open CSV File
		$arr_title = explode(",",$title);
		foreach($arr_title as $i => $v) $arr_title[$i] = trim($v,'"');
		$col_count = sizeof($arr_title);
		
		$fhandle = fopen($cache_file, 'r');
		$arrToUpdate = array();
		while($line = fgetcsv($fhandle, 50000))
		{
			//$title = '"Merchant Name","Coupon Type","Coupon Offer","Coupon Code","Coupon Link","Coupon Start","Coupon Expiration","Coupon Last Modified"';
			if($line[0] == '' || $line[0] == 'Merchant Name') continue;
			if(sizeof($line) != $col_count)
			{
				echo "warning: invalid line found: " . implode(",",$line) . "\n";
				continue;
			}
			
			$row = array();
			foreach($arr_title as $i => $title) $row[$title] = $line[$i];
			
			$url_parsed = parse_url($row["Coupon Link"]);
			if($url_parsed === false)
			{
				echo "warning: Coupon Link is wrong: " . implode(",",$line) . "\n";
				continue;
			}
			$query_string = $url_parsed["query"];
			$para_pair = explode("&",$query_string);
			$arr_para = array();
			foreach($para_pair as $one_para)
			{
				$arr_temp = explode("=",$one_para,2);
				if(sizeof($arr_temp) != 2) continue;
				$arr_para[$arr_temp[0]] = urldecode($arr_temp[1]);
			}
			
			if(!isset($arr_para["mli"]) || !isset($arr_para["mi"]))
			{
				echo "warning: Coupon Link is wrong: " . implode(",",$line) . "\n";
				continue;
			}
			
			$link_id = 'c_' . $arr_para["mli"];
			$aff_mer_id = $arr_para["mi"];
			
			if(!isset($all_merchant[$aff_mer_id]))
			{
				echo "warning: Merchant Id not found \n";
				continue;
			}
			
			$link_desc = $row["Coupon Offer"];
			if (trim($row["Coupon Code"]) != '') $link_desc .= '. Coupon Code: '.$row["Coupon Code"];
			
			if ($row["Coupon Type"] == 'image'){
				$html_code = '<a href="'.$row["Coupon Link"].'"><img src="'.$row["Coupon Offer"].'" /></a>';
			}
			else{
				$html_code = '<a href="'.$row["Coupon Link"].'">'.$row["Coupon Offer"].'</a>';
			}
			
			$promo_type = 'coupon';
			
			$arr_one_link = array(
				"AffId" => $this->info["AffId"],
				"AffMerchantId" => $aff_mer_id,
				"AffLinkId" => $link_id,
				"LinkName" => $link_desc,
				"LinkDesc" => $link_desc,
				"LinkStartDate" => $row["Coupon Start"],
				"LinkEndDate" => $row["Coupon Expiration"],
				"LinkPromoType" => $promo_type,
				"LinkHtmlCode" => $html_code,
				"LinkOriginalUrl" => "",
				"LinkImageUrl" => "",
				"LinkAffUrl" => "",
				"DataSource" => "11",
			);
			
			$this->oLinkFeed->fixEnocding($this->info,$arr_one_link,"feed");
			$arrToUpdate[] = $arr_one_link;
			$arr_return["AffectedCount"] ++;
			if(!isset($arr_return["Detail"][$aff_mer_id]["AffectedCount"])) $arr_return["Detail"][$aff_mer_id]["AffectedCount"] = 0;
			$arr_return["Detail"][$aff_mer_id]["AffectedCount"] ++;

			if(sizeof($arrToUpdate) > 100)
			{
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($arrToUpdate);
				$arrToUpdate = array();
			}
		}
		fclose($fhandle);
		
		if(sizeof($arrToUpdate) > 0)
		{
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($arrToUpdate);
			$arrToUpdate = array();
		}
		return $arr_return;
	} */
	
	function getCouponFeed()
	{
	    $check_date = date('Y-m-d H:i:s');
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
					"DataSource" => 406,
			        "Type"       => 'promotion'
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
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'promotion');
		
	
		return $arr_return;
	}

	function GetAllLinksFromAffByMerID($merinfo)
	{
	    $check_date = date('Y-m-d H:i:s');
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
					"DataSource" => "406",
			        "Type"       => 'link'
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
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link', $merinfo['IdInAff']);
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
				$strMerName = trim($matches[2]);
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
            }else{
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
		
		//$objProgram->setProgramOffline($this->info["AffId"]);
		$objProgram->setCountryInt($this->info["AffId"]);
		
		return $Cnt;
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
			$program_num += $this->getProgramByStatus($status, "ca");
		}
		
		echo "\tGet Program by page end\r\n";
		
		if($program_num < 10){
			mydie("die: program count {$program_num} < 10, please check program.\n");
		}
		
		echo "\tUpdate ({$program_num}) program.\r\n";
		
	}

    function GetStatus(){
        $this->getStatus = true;
        $this->GetProgramFromAff();
    }

    function GetProgramFromAff()
	{	print_r($this->info);exit;
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
