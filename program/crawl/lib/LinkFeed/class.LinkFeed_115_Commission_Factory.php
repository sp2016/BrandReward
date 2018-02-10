<?php
require_once 'text_parse_helper.php';

class LinkFeed_115_Commission_Factory
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;

		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		$this->getStatus = false;

		if(SID == 'bdg02'){
			define('API_KEY_115', '8190d20bc02a49f08ab32083cf9a414b');
			$this->password = urlencode($this->info["Password"]);
			$this->username = urlencode($this->info["Account"]);
		}else{
			define('API_KEY_115', '7d3661151d454f63aae1d3089e80269c');
			$this->password = urlencode($this->info["Password"]);
			$this->username = urlencode($this->info["Account"]);
		}
	}

	function Login()
	{
		$strUrl = "https://dashboard.commissionfactory.com/LogIn/";
		$request = array(
				"method" => "get",
				"postdata" => "",
		);
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);

		$result = $r["content"];
		$__EVENTVALIDATION = urlencode($this->oLinkFeed->ParseStringBy2Tag($result, array('name="__EVENTVALIDATION"', 'value="'), '"'));
		$__VIEWSTATE = urlencode($this->oLinkFeed->ParseStringBy2Tag($result, array('name="__VIEWSTATE"', 'value="'), '"'));
		$strUrl = "https://dashboard.commissionfactory.com/LogIn/";
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => "post",
				"postdata" => "",
		);
		$request["postdata"] = "ctl05=ctl05%7CbtnLogIn&txtUsername={$this->username}&txtPassword={$this->password}&txtResetPassword=&txtContactFirstName=&txtContactLastName=&txtContactEmail=&txtMerchFullName=&txtMerchCompany=&txtMerchEmail=&txtMerchPhone=&txtMerchWebsite=&txtAgencyFullName=&txtAgencyCompany=&txtAgencyEmail=&txtAgencyPhone=&txtAgencyWebsite=&__EVENTTARGET=&__EVENTARGUMENT=&__LASTFOCUS=&__VIEWSTATE={$__VIEWSTATE}&__VIEWSTATEGENERATOR=25748CED&__EVENTVALIDATION={$__EVENTVALIDATION}&__ASYNCPOST=true&btnLogIn=Log%20In";
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];
		if(stripos($result,'Affiliate') === false)
		{
			mydie("die: failed to login.\n");
		}
		else
		{
			echo "login succ.\n";
		}
	}

	function GetMerchantListFromAff()
	{
		$arr_return = array("AffectedCount" => 0,"UpdatedCount" => 0);
		$request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "");

		//step 2,get all exists merchant
		$arrAllExistsMerchants = $this->oLinkFeed->GetAllExistsAffMerIDForCheckByAffID($this->info["AffId"]);

		$strUrl = sprintf("https://api.commissionfactory.com/V1/Affiliate/Merchants?apiKey=%s", API_KEY_115);
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];

		$result = json_decode($result);

		foreach($result as $v){
			$strMerID = intval($v->Id);
			if($strMerID < 1) continue;

			$strMerName = trim($v->Name);
			$strStatus = trim($v->Status);

			if($strStatus == "Joined"){
				$strStatus = "approval";
			}elseif($strStatus == "Pending"){
				$strStatus = "pending";
			}elseif($strStatus == "Not Joined"){
				$strStatus = "not apply";
			}else{
				$strStatus = "siteclosed";
			}
			$arr_return["AffectedCount"] ++;
			$arr_update = array(
					"AffMerchantId" => $strMerID,
					"AffId" => $this->info["AffId"],
					"MerchantName" => $strMerName,
					"MerchantEPC30d" => "",
					"MerchantEPC" => "",
					"MerchantStatus" => $strStatus,	//'not apply','pending','approval','declined','expired','siteclosed'
					"MerchantRemark" => ""
			);
			$this->oLinkFeed->fixEnocding($this->info,$arr_update,"merchant");
			//print_r($arr_update);
			if($this->oLinkFeed->UpdateMerchantToDB($arr_update,$arrAllExistsMerchants))
				$arr_return["UpdatedCount"] ++;
		}
		$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateAllExistsAffMerIDButCannotFetched($this->info["AffId"], $arrAllExistsMerchants);
		return $arr_return;
	}

	function getCouponFeed()
	{
	    $check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0,"UpdatedCount" => 0,"Detail" => array());
		$request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "");

		$all_merchant = $this->oLinkFeed->getAllAffMerchant($this->info["AffId"]);
		$arrToUpdate = array();

		$promoType = array('Promotions','Coupons');
		foreach ($promoType as $value){
		    
		    $strUrl = sprintf("https://api.commissionfactory.com/V1/Affiliate/$value?apiKey=%s", API_KEY_115);
		    echo $strUrl.PHP_EOL;
		    $r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		    $result = $r["content"];
		    
		    $result = json_decode($result);
		    foreach($result as $vp){
		        $link_id = intval($vp->Id);
		        if($link_id < 1) continue;
		    
		        //<EndDate i:nil="true"/>
		    
		        $aff_mer_id = trim($vp->MerchantId);
		        if(!isset($all_merchant[$aff_mer_id])) continue;
		    
		        //$link_name  = trim($vp->MerchantName).'_'.$link_id;
		        $link_name  = trim($vp->Description);
		        $link_desc  = trim($vp->Description);
		        $html_code 	= trim($vp->TrackingCode);
		        $promo_type = "coupon";
		        $couponcode = '';
		        if($value == 'Coupons')
		            $couponcode = trim($vp->Code);
		        $LinkOriginalUrl = trim($vp->TargetUrl);
		        $LinkAffUrl = trim($vp->TrackingUrl);
		        $LinkImageUrl = trim($vp->MerchantAvatarUrl);
		         
		        if ($couponcode == "NA") $couponcode = "";
		    
		        
		        if ($couponcode != '') $link_desc .= '. Coupon Code: '.$couponcode;
		        
		        if (!empty($vp->TermsAndConditions))
		            $link_desc .= '. '.$vp->TermsAndConditions;
		        
		        if($couponcode == ''){
		            $code = get_linkcode_by_text($link_desc);
		            if (!empty($code))
		            {
		                $couponcode = $code;
		            }
		        }
		    
		        $start_date = "0000-00-00 00:00:00";
		        if(isset($vp->StartDate) && $vp->StartDate > 0){
		            $start_date = trim($vp->StartDate);
		            $start_date = date("Y-m-d H:i:s", strtotime($start_date));
		        }
		    
		        $end_date = "0000-00-00 00:00:00";
		        if(isset($vp->EndDate) && $vp->EndDate > 0){
		            $end_date  	= trim($vp->EndDate);
		            $end_date = date("Y-m-d H:i:s", strtotime($end_date));
		        }
		    
		        $arr_one_link = array(
		            "AffId" => $this->info["AffId"],
		            "AffMerchantId" => $aff_mer_id,
		            "AffLinkId" => $link_id,
		            "LinkName" =>  $link_name,
		            "LinkDesc" =>  $link_desc,
		            "LinkStartDate" => $start_date,
		            "LinkEndDate" => $end_date,
		            "LinkPromoType" => $promo_type,
		            "LinkHtmlCode" => $html_code,
		            "LinkCode" => $couponcode,
		            "LinkOriginalUrl" => $LinkOriginalUrl,
		            "LinkImageUrl" => $LinkImageUrl,
		            "LinkAffUrl" => $LinkAffUrl,
		            "DataSource" => 53,
		            "IsDeepLink" => 'UNKNOWN',
		            "Type"       => 'promotion'
		        );
		    
		        $this->oLinkFeed->fixEnocding($this->info,$arr_one_link,"feed");
		        $arrToUpdate[] = $arr_one_link;
		        $arr_return["AffectedCount"] ++;
		        if(!isset($arr_return["Detail"][$aff_mer_id]["AffectedCount"]))
		            $arr_return["Detail"][$aff_mer_id]["AffectedCount"] = 0;
		        $arr_return["Detail"][$aff_mer_id]["AffectedCount"] ++;
		        if(sizeof($arrToUpdate) > 100)
		        {
		            $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($arrToUpdate);
		            $arrToUpdate = array();
		        }
		    }
		    
		}
		
		if(sizeof($arrToUpdate) > 0)
		{
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($arrToUpdate);
			$arrToUpdate = array();
		}
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'promotion');
		return $arr_return;
	}
	
	function GetAllLinksByAffId(){
	     
	    $check_date = date('Y-m-d H:i:s');
	    $aff_id = $this->info["AffId"];
	    $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, );
	    $request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", );
	    $arr_merchant = $this->oLinkFeed->getApprovalAffMerchant($this->info["AffId"]);
	    
	    foreach ($arr_merchant as $merinfo){
	        $url = sprintf('https://api.commissionfactory.com/V1/Affiliate/Banners?apiKey=%s&merchantId=%s', API_KEY_115, $merinfo['IdInAff']);
	        $r = $this->oLinkFeed->GetHttpResult($url, $request);
	        if (empty($r) || empty($r['code']) || $r['code'] != 200 || empty($r['content']))
	            continue;
	        $content = $r['content'];
	        $data = @json_decode($content, true);
	        if (empty($data) || !is_array($data))
	            continue;
	        $links = array();
	        foreach ($data as $v)
	        {
	            $link = array(
	                "AffId" => $this->info["AffId"],
	                "AffMerchantId" => $merinfo['IdInAff'],
	                "AffLinkId" => $v['Id'],
	                "LinkName" => $v['Name'],
	                "LinkDesc" => $v['AltText'],
	                "LinkStartDate" => parse_time_str($v['DateModified'], 'Y-m-d H:i:s', false),
	                "LinkEndDate" => '0000-00-00',
	                "LinkPromoType" => 'N/A',
	                "LinkOriginalUrl" => "",
	                "LinkHtmlCode" => $v['TrackingCode'],
	                "LinkAffUrl" => $v['TrackingUrl'],
	                "DataSource" => "53",
	                "IsDeepLink" => 'UNKNOWN',
	                "Type"       => 'link'
	            );
	            $link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
	            if (empty($link['AffLinkId']) || empty($link['LinkName']))
	                continue;
	            $arr_return['AffectedCount'] ++;
	            $links[] = $link;
	        }
	        echo sprintf("%s link(s) found. sleep 5 secound...\n", count($links));
	        if (count($links) > 0)
	            $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
	    }
	    print_r($arr_return);
	    $this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link');
	    
	    return $arr_return;
	    
	}
	

	function GetAllLinksFromAffByMerID($merinfo)
	{
	    $check_date = date('Y-m-d H:i:s');
		$aff_id = $this->info["AffId"];
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, );
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", );

		$url = sprintf('https://api.commissionfactory.com/V1/Affiliate/Banners?apiKey=%s&merchantId=%s', API_KEY_115, $merinfo['IdInAff']);
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		if (empty($r) || empty($r['code']) || $r['code'] != 200 || empty($r['content']))
			return $arr_return;
		$content = $r['content'];
		$data = @json_decode($content, true);
		if (empty($data) || !is_array($data))
			return $arr_return;
		$links = array();
		foreach ($data as $v)
		{
			$link = array(
					"AffId" => $this->info["AffId"],
					"AffMerchantId" => $merinfo['IdInAff'],
					"AffLinkId" => $v['Id'],
					"LinkName" => $v['Name'],
					"LinkDesc" => $v['AltText'],
					"LinkStartDate" => parse_time_str($v['DateModified'], 'Y-m-d H:i:s', false),
					"LinkEndDate" => '0000-00-00',
					"LinkPromoType" => 'N/A',
					"LinkOriginalUrl" => "",
					"LinkHtmlCode" => $v['TrackingCode'],
					"LinkAffUrl" => $v['TrackingUrl'],
					"DataSource" => "53",
					"IsDeepLink" => 'UNKNOWN',
					"Type"       => 'link'
			);
			$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
			if (empty($link['AffLinkId']) || empty($link['LinkName']))
				continue;
			$arr_return['AffectedCount'] ++;
			$links[] = $link;
		}
		echo sprintf("%s link(s) found. sleep 5 secound...\n", count($links));
		sleep(5);
		if (count($links) > 0)
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link', $merinfo['IdInAff']);
		return $arr_return;
	}

	function GetAllProductsByAffId()
	{
	
		$check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
		$request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "");
		
		$arr_merchant = $this->oLinkFeed->getApprovalAffMerchant($this->info["AffId"]);
		$productNumConfig = $this->oLinkFeed->product_program_num_config($this->info["AffId"]);
		$productNumConfigAlert = '';
		$isAssignMerchant = FALSE;
		
		$mcount = 0;
		foreach ($arr_merchant as $merchatInfo)
		{
		    echo $merchatInfo['IdInAff'].PHP_EOL;
			$url = "https://api.commissionfactory.com/V1/Affiliate/DataFeeds?apiKey=".API_KEY_115."&merchantId=".$merchatInfo['IdInAff'];
			$result = $this->oLinkFeed->GetHttpResult($url, $request);
			$result = json_decode($result["content"], true);
			//var_dump($result);
			if (!$result)
				continue;
			//echo memory_get_usage()."\r\n";
			foreach($result as $k => $DataFeed)
			{
				if ($k == 0)
				{
					$ItemsCount = $DataFeed['ItemsCount'];
					$DataFeedId = $DataFeed['Id'];
				}else
				{
					if ($DataFeed['ItemsCount'] < $ItemsCount)
					{
						$ItemsCount = $DataFeed['ItemsCount'];
						$DataFeedId = $DataFeed['Id'];
					}
				}
			}
			$TotalCount = $ItemsCount;
			$strUrl = "https://api.commissionfactory.com/V1/Affiliate/DataFeeds/$DataFeedId?apiKey=".API_KEY_115;
			$result = $this->oLinkFeed->GetHttpResult($strUrl, $request);
			$result = json_decode($result["content"], true);
			//var_dump($result);exit;
			
			if (!$result)
				continue;
			//echo memory_get_usage()."\r\n";
			if(isset($result['Items']) && $result['Items'])
			{
				$crawlMerchantsActiveNum = 0;
				$setMaxNum  = isset($productNumConfig[$merchatInfo['IdInAff']]) ? $productNumConfig[$merchatInfo['IdInAff']]['limit'] :  100;
				$isAssignMerchant = isset($productNumConfig[$merchatInfo['IdInAff']]) ? TRUE : FALSE;
				foreach ($result['Items'] as $v)
				{   print_r($v);exit;
					$product_path_file = $this->oLinkFeed->getProductImgFilePath($this->info["AffId"],"{$merchatInfo['IdInAff']}_".urlencode($v['Id']).".png", PRODUCTDIR);
					if(!$this->oLinkFeed->fileCacheIsCached($product_path_file))
					{
						$file_content = $this->oLinkFeed->downloadImg($v['ImageUrl']);
						if(!$file_content) //下载不了跳过。
							continue;
						$this->oLinkFeed->fileCachePut($product_path_file, $file_content);
					}
					if(!isset($v['Name']) || empty($v['Name']) || !isset($v['Id']))
					{
						continue;
					}
					$Price_arr = explode(' ', $v['Price']);
					$ProductPrice = trim($Price_arr[0]);
					if (isset($Price_arr[1]))
						$Currency = trim($Price_arr[1]);
					else 
						$Currency = '';
					
					$link = array(
							"AffId" => $this->info["AffId"],
							"AffMerchantId" => $merchatInfo['IdInAff'],
							"AffProductId" => $v['Id'],
							"ProductName" => addslashes($v['Name']),
							"ProductCurrency" =>$Currency,
							"ProductPrice" =>$ProductPrice,
							"ProductOriginalPrice" =>'',
							"ProductRetailPrice" =>'',
							"ProductImage" => addslashes($v['ImageUrl']),
							"ProductLocalImage" => addslashes($product_path_file),
							"ProductUrl" => addslashes($v['TrackingUrl']),
							"ProductDestUrl" => addslashes($v['TargetUrl']),
							"ProductDesc" => addslashes($v['Description']),
							"ProductStartDate" => date('Y-m-d H:i:s', strtotime($v['DateCreated'])),
							"ProductEndDate" => '',
					);
					$links[] = $link;
					$arr_return['AffectedCount'] ++;
					$crawlMerchantsActiveNum++;
					//大于最大数跳出
					if($crawlMerchantsActiveNum>=$setMaxNum){
					    break;
					}
					if (count($links) >= 100)
					{
					    $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateProductToDB($links);
					    $links = array();
					}
					
				}
				unset($result);
				if (count($links))
				{
					$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateProductToDB($links);
					$links = array();
					//echo sprintf("get product complete. %s links(s) found. \n", $arr_return["UpdatedCount"]);
				}
			}
			if($isAssignMerchant){
			    $productNumConfigAlert .= "AFFID:".$this->info["AffId"].",Program({$merchatInfo['MerchantName']}),Crawl Count($crawlMerchantsActiveNum),Total Count({$TotalCount}) \r\n";
			}
			$mcount ++;
		}
		echo 'merchant count:'.$mcount.PHP_EOL;
		echo $productNumConfigAlert;
		$this->oLinkFeed->checkProductExists($this->info["AffId"], $check_date);
		 
		echo 'END time'.date('Y-m-d H:i:s').PHP_EOL;
		return $arr_return;
		
	}
	
	function GetStatus(){
		$this->getStatus = true;
		$this->GetProgramFromAff();
	}

	function GetProgramFromAff()
	{
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";

		$this->GetProgramFromByApi();
		$this->checkProgramOffline($this->info["AffId"], $check_date);

		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}

	function GetProgramFromByPage()
	{
		echo "\tGet Program by page start\r\n";
		$program_num = 0;

		//step 1,login
		//$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		$this->Login();

		//login
		/*$strUrl = "https://dashboard.commissionfactory.com/LogIn/";
		$request = array(
			"method" => "get",
			"postdata" => "",
		);
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];
		$__EVENTVALIDATION = urlencode($this->oLinkFeed->ParseStringBy2Tag($result, array('name="__EVENTVALIDATION"', 'value="'), '"'));
		$__VIEWSTATE = urlencode($this->oLinkFeed->ParseStringBy2Tag($result, array('name="__VIEWSTATE"', 'value="'), '"'));

		$strUrl = "https://dashboard.commissionfactory.com/LogIn/";
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "post",
			"postdata" => "",
		);
		$request["postdata"] = "__ASYNCPOST=true&__EVENTARGUMENT=&__EVENTTARGET=&__EVENTVALIDATION={$__EVENTVALIDATION}&__VIEWSTATE={$__VIEWSTATE}&ctl00%24cphBody%24btnLogIn=Log%20In&ctl00%24cphBody%24txtPassword=90xierHJ%5E%26&ctl00%24cphBody%24txtUsername=couponsnapshot&ctl00%24ctl05=ctl00%24ctl05%7Cctl00%24cphBody%24btnLogIn";
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);*/

		//get para
		$strUrl = "http://dashboard.commissionfactory.com/Affiliate/Merchants/";
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => "get",
				"postdata" => "",
		);
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];

		$__EVENTVALIDATION = urlencode($this->oLinkFeed->ParseStringBy2Tag($result, array('name="__EVENTVALIDATION"', 'value="'), '"'));
		$__VIEWSTATE = urlencode($this->oLinkFeed->ParseStringBy2Tag($result, array('name="__VIEWSTATE"', 'value="'), '"'));

		//	PageSize:50
		//  PageIndex:1


		$objProgram = new ProgramDb();
		$arr_prgm = array();

		echo "\tGet New Offer\r\n";
		$nNumPerPage = 10;
		$bHasNextPage = true;
		$nPageNo = 1;
		while($bHasNextPage){
			$strUrl = "http://dashboard.commissionfactory.com/Affiliate/Merchants/";
			$start = $nPageNo - 1;
			$request = array(
					"AffId" => $this->info["AffId"],
					"method" => "post",
					"postdata" => "",
			);
			$request["postdata"] = "__ASYNCPOST=true&__EVENTARGUMENT=PageIndex%3A{$start}&__EVENTTARGET=ctl00%24ctl00%24cphBody%24cphBody%24pgeMerchants&__EVENTVALIDATION={$__EVENTVALIDATION}&__LASTFOCUS=&__VIEWSTATE={$__VIEWSTATE}&ctl00%24ctl00%24cphBody%24cphBody%24lstCategory=0&ctl00%24ctl00%24cphBody%24cphBody%24lstStatus=0&ctl00%24ctl00%24cphBody%24cphBody%24lstType=0&ctl00%24ctl00%24cphBody%24cphBody%24txtSearch=&ctl00%24ctl00%24ctl05=ctl00%24ctl00%24ctl05%7Cctl00%24ctl00%24cphBody%24cphBody%24pgeMerchants";
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$result = $r["content"];

			$total = intval($this->oLinkFeed->ParseStringBy2Tag($result, array('<div id="cphBody_cphBody_pgeMerchants">', '</select>', 'of'), '('));
			if(($nPageNo) >= $total){
				$bHasNextPage = false;
			}

			$result = explode("|", $result);
			//print_r($result);

			$program_list = "";
			foreach($result as $v){
				if(stripos($v, '<table class="grid" cellpadding="0" cellspacing="0">') !== false){
					$program_list = $v;
					break;
				}
			}
			$strLineStart = '<span class="tooltip">';

			$nLineStart = 0;
			while ($nLineStart >= 0){
				$nLineStart = stripos($program_list, $strLineStart, $nLineStart);
				if ($nLineStart === false) break;

				$desc = $this->oLinkFeed->ParseStringBy2Tag($program_list, '<span class="content">', '<a style="color: #ffffff; ', $nLineStart);
				$Homepage = $this->oLinkFeed->ParseStringBy2Tag($program_list, 'href="', '"', $nLineStart);
				$strMerName = $this->oLinkFeed->ParseStringBy2Tag($program_list, '<td>', '<br />', $nLineStart);
				$Category = $this->oLinkFeed->ParseStringBy2Tag($program_list, '<span style="color: #808080;">', '</span>', $nLineStart);
				$Commission = $this->oLinkFeed->ParseStringBy2Tag($program_list, '<td class="nowrap">', '</td>', $nLineStart);
				$JoinDate = $this->oLinkFeed->ParseStringBy2Tag($program_list, '<td class="right">', '</td>', $nLineStart);
				$strMerID = $this->oLinkFeed->ParseStringBy2Tag($program_list, array('<td class="nowrap">','href="'), '/"', $nLineStart);
				$StatusInAffRemark = $this->oLinkFeed->ParseStringBy2Tag($program_list, '>', '</a></td>', $nLineStart);

				if($StatusInAffRemark == 'Joined'){
					$Partnership = 'Active';
				}elseif($StatusInAffRemark == 'Pending'){
					$Partnership = 'Pending';
				}else{
					$Partnership = 'NoPartnership';
				}

				$JoinDate = str_replace('/', '-', $JoinDate);
				$JoinDate = date("Y-m-d H:i:s", strtotime($JoinDate));
				$prgm_url = "http://dashboard.commissionfactory.com/Affiliate/Merchants/{$strMerID}/";

				$arr_prgm[$strMerID] = array(
						"Name" => addslashes(trim($strMerName)),
						"AffId" => $this->info["AffId"],
						"Homepage" => $Homepage,
						"IdInAff" => $strMerID,
						"JoinDate" => $JoinDate,
						"StatusInAffRemark" => addslashes($StatusInAffRemark),
						"StatusInAff" => 'Active',						//'Active','TempOffline','Offline'
						"Partnership" => $Partnership,				//'NoPartnership','Active','Pending','Declined','Expired','Removed'
						"Description" => addslashes($desc),
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
			$nPageNo++;
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

	function GetProgramFromByApi()
	{
		echo "\tGet Program by api start\r\n";

		$this->Login();
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		$request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "");
		$strUrl = "https://api.commissionfactory.com/V1/Affiliate/Merchants?apiKey=".API_KEY_115;
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];
		$result = json_decode($result);
		//var_dump($result);exit;

		foreach($result as $v){
			$strMerID = intval($v->Id);
			if($strMerID < 1) continue;

			$strMerName = trim($v->Name);
			$Homepage = trim($v->TargetUrl);
			$CategoryExt = trim($v->Category);
			$StatusInAffRemark = trim($v->Status);
			$desc = trim($v->Summary);
			$TermAndCondition = trim($v->TermsAndConditions);
			$LogoUrl = trim($v->AvatarUrl);
			if($v->CommissionType == 'Percent per Sale')
				$CommissionExt = trim($v->CommissionRate) . '%';
			else
				$CommissionExt = trim($v->CommissionRate) . " " . trim($v->CommissionType);
			$AffDefaultUrl = trim($v->TrackingUrl);

			$JoinDate = trim($v->DateCreated);
			$JoinDate = date("Y-m-d H:i:s", strtotime($JoinDate));

			if($StatusInAffRemark == "Joined"){
				$StatusInAff = "Active";
				$Partnership = "Active";
			}elseif($StatusInAffRemark == "Pending"){
				$strStatus = "Active";
				$Partnership = "Pending";
			}elseif($StatusInAffRemark == "Not Joined"){
				$strStatus = "Active";
				$Partnership = "NoPartnership";
			}else{
				$strStatus = "Active";
				$Partnership = "NoPartnership";
			}
			
			$prgm_url = "http://dashboard.commissionfactory.com/Affiliate/Merchants/{$strMerID}/";
			
			//get PaymetDays and CookieTime by page
			$re = $this->oLinkFeed->GetHttpResult($prgm_url,$request);
			$re = $re['content'];
			$PaymentDays = intval(trim($this->oLinkFeed->ParseStringBy2Tag($re, array('<span class="item">Tracking Period</span>', '<span class="value">'), '</span>')));
			$CookieTime = intval(trim($this->oLinkFeed->ParseStringBy2Tag($re, array('<span class="item">per sale</span>', '<span class="value">'), '</span>')));
			
			/* if(!$this->getStatus) {
				
				$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
				$prgm_detail = $prgm_arr["content"];

				$custom_commission = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, 'Custom Commission Rate', 'Description');
				$custom_commission = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, 'Custom Commission Rate', 'Description')));
				//$custom_commission = str_replace(PHP_EOL, '', $custom_commission);
				$custom_commission = str_replace(array("\r", "\n", "\t"), "", $custom_commission);
				if ($custom_commission) {
					$CommissionExt = $custom_commission;
				}
				
				$prgm_detail = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, '<div class="inner">', '</div>');
				preg_match('/<p[\s\S]*?<p[\s\S]*?<p[\s\S]*?<p[\s\S]*?.*?>([\s\S]*?.*?)<\/p>/',$prgm_detail,$TargetCountryExt);
				$TargetCountryExt = isset($TargetCountryExt[1])? trim($TargetCountryExt[1]) : '';
				
				
				
				//TermAndCondition
				$request = array(
				    "AffId" => $this->info["AffId"],
				    "method" => "post",
				    "postdata" => "",
				);
				preg_match('/<input type="hidden" name="__VIEWSTATE" id="__VIEWSTATE" value="(.*?)" \/>/i',$prgm_arr["content"],$matchesTC1);
				preg_match('/<input type="hidden" name="__VIEWSTATEGENERATOR" id="__VIEWSTATEGENERATOR" value="(.*?)" \/>/i',$prgm_arr["content"],$matchesTC2);
				preg_match('/<input type="hidden" name="__EVENTVALIDATION" id="__EVENTVALIDATION" value="(.*?)" \/>/i',$prgm_arr["content"],$matchesTC3);
				preg_match('/<input name="ctl00\$ctl00\$cphCalendarHolder\$CalendarComponent\$txtExport" type="text" value="(.*?)" readonly="readonly" id="cphCalendarHolder_CalendarComponent_txtExport"/i',$prgm_arr["content"],$matchesTC4);
				$request["postdata"] = 'ctl00$ctl00$ctl04=ctl00$ctl00$ctl04|ctl00$ctl00$cphBody$cphBody$btnTermsAndConditions&__EVENTTARGET=ctl00$ctl00$cphBody$cphBody$btnTermsAndConditions&__EVENTARGUMENT=&__LASTFOCUS=&__VIEWSTATE='.urlencode($matchesTC1[1]).'&__VIEWSTATEGENERATOR='.urlencode($matchesTC2[1]).'&__EVENTVALIDATION='.urlencode($matchesTC3[1]).'&ctl00$ctl00$cphCalendarHolder$CalendarComponent$txtExport='.urlencode($matchesTC4[1]).'&ctl00$ctl00$cphCalendarHolder$CalendarComponent$txtTaskName=&ctl00$ctl00$cphCalendarHolder$CalendarComponent$txtEventName=&ctl00$ctl00$cphSupportHolder$SupportComponent$txtSupportSearchQuery=&__ASYNCPOST=true';
				$tcContent = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
				preg_match('@<div\s+style="margin-bottom:\s+25px;\s+max-height:\s+300px;\s+overflow:\s+auto;\s+padding:\s+0px\s+25px\s+0px\s+25px;">(.*?)</div>@ms', $tcContent['content'],$matches);
 
				$TermAndCondition = $matches[1];
				
				$arr_prgm[$strMerID] = array(
						"Name" => addslashes(trim($strMerName)),
						"AffId" => $this->info["AffId"],
						"Homepage" => $Homepage,
						"CategoryExt" => addslashes($CategoryExt),
						"IdInAff" => $strMerID,
						"JoinDate" => $JoinDate,
						"CommissionExt" => addslashes($CommissionExt),
						"StatusInAffRemark" => addslashes($StatusInAffRemark),
						"StatusInAff" => 'Active',                        //'Active','TempOffline','Offline'
						"Partnership" => $Partnership,                //'NoPartnership','Active','Pending','Declined','Expired','Removed'
						"Description" => addslashes($desc),
						"LastUpdateTime" => date("Y-m-d H:i:s"),
						"DetailPage" => $prgm_url,
						"SupportDeepUrl" => 'YES',
						"AffDefaultUrl" => addslashes($AffDefaultUrl),
						"TargetCountryExt" => addslashes($TargetCountryExt),
				        "TermAndCondition" => addslashes($TermAndCondition),
				);
				
			} else { */
				$arr_prgm[$strMerID] = array(
						"Name" => addslashes(trim($strMerName)),
						"AffId" => $this->info["AffId"],
						"Homepage" => $Homepage,
						"CategoryExt" => addslashes($CategoryExt),
						"IdInAff" => $strMerID,
						"JoinDate" => $JoinDate,
						"CommissionExt" => addslashes($CommissionExt),
						"StatusInAffRemark" => addslashes($StatusInAffRemark),
						"StatusInAff" => 'Active',                        //'Active','TempOffline','Offline'
						"Partnership" => $Partnership,                //'NoPartnership','Active','Pending','Declined','Expired','Removed'
						"Description" => addslashes($desc),
						"LastUpdateTime" => date("Y-m-d H:i:s"),
						"DetailPage" => $prgm_url,
						"SupportDeepUrl" => 'YES',
						"AffDefaultUrl" => addslashes($AffDefaultUrl),
						"TermAndCondition" => addslashes($TermAndCondition),
						"LogoUrl" => addslashes($LogoUrl),
						"PaymentDays" => addslashes($PaymentDays),
						"CookieTime" =>addslashes($CookieTime),
				);
			//}
			
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

