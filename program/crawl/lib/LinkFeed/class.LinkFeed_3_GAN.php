<?php
class LinkFeed_3_GAN
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
	}
	
	function LoginIntoAffService()
	{
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => "post",
				"postdata" => "",
			);

		$strUrl = "https://www.google.com/accounts/ServiceLoginAuth?service=affiliatenetwork";
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];
		$result = str_replace(array("\r","\n"), "", $result);

		//parse the hidden post value
		$postVal = "";
		$pattern = "/<form\s+.*id=\"gaia_loginform\".*?>(.*?)<\/form>/i";
		
		if(preg_match($pattern, $result, $matches))
		{
			$formcontent = $matches[1];
			//echo $formcontent;
			$p = "/<input\s+type=\"hidden\"[^><]*?name=\"([^\"]+)\"[^><]*?value=\"([^\"]+)\"/i";
			if($formcontent && preg_match_all($p, $formcontent, $matches))
			{	
				//print_r($matches);
				foreach($matches[1] as $k=>$v)
				{
					if(strcmp("pstMsg", $v) == 0)
					{
						$postVal .= $v."=1&";
					}
					else if(strcmp("dnConn", $v) == 0)
					{
						//$postVal .= $v."=https%3A%2F%2Faccounts.youtube.com&";
					}
					else
						$postVal .= $v."=".urlencode($matches[2][$k])."&";
				}

				$postVal .= $this->info["AffLoginPostString"];
			}
		}
		
		//end

		//step 1, login and create the sessionid
		$request["postdata"] = $postVal ? $postVal : $this->info["AffLoginPostString"];
		$r = $this->oLinkFeed->GetHttpResult($this->info["AffLoginUrl"],$request);
		$result = $r["content"];
		
		/*
		<meta http-equiv="refresh" content="0; url=&#39;http://www.google.ru/accounts/SetSID?ssdc=1&amp;sidt=ALWU2csHG1V1WauKeMokB2o5qVbgwR3l49WLv%2B5EjoEZrqUdhmWXdTuqap5uD1SgfoznASSBDDwn1xepXuhH1irmOetHx%2BwX30us6VfwOzZUkxS3i6CRDNx3ipmraLpR%2FrSES6o6NSs3nYuIx6mRUfkZayBP06r5DtRllW0cuUy86VR8m51KhsbUTJre%2FtJIO6ee9kspdS1KWgLl7a3mWV87IcMKWXSwjUzPET8xPCPoTFNvSbI79jBZfdEfvJPoBc8JEMAT1EVNOTZOCmU%2BCD%2BFxXDYaaL%2BuF4X0fOMGqRKFk%2BsDmBDs6EtW7uVdggnD7cm7AM1DdoM3mpvb76MOi6HTez4s6exfzgf1gT%2BCpCaaMuRNRGNTixHttvi77KGDzlWiMYr%2FDJm&amp;continue=https%3A%2F%2Fwww.google.com%2Faccounts%2FServiceLogin%3Fpassive%3Dtrue%26go%3Dtrue%26continue%3Dhttps%253A%252F%252Fwww.connectcommerce.com%252Fbin%252Flogin.mx%26service%3Daffiliatenetwork%26fss%3D1&#39;"></head>
<body bgcolor="#ffffff" text="#000000" link="#0000cc" vlink="#551a8b" alink="#ff0000"><script type="text/javascript" language="javascript">
    location.replace("http://www.google.ru/accounts/SetSID?ssdc\x3d1\x26sidt\x3dALWU2csHG1V1WauKeMokB2o5qVbgwR3l49WLv%2B5EjoEZrqUdhmWXdTuqap5uD1SgfoznASSBDDwn1xepXuhH1irmOetHx%2BwX30us6VfwOzZUkxS3i6CRDNx3ipmraLpR%2FrSES6o6NSs3nYuIx6mRUfkZayBP06r5DtRllW0cuUy86VR8m51KhsbUTJre%2FtJIO6ee9kspdS1KWgLl7a3mWV87IcMKWXSwjUzPET8xPCPoTFNvSbI79jBZfdEfvJPoBc8JEMAT1EVNOTZOCmU%2BCD%2BFxXDYaaL%2BuF4X0fOMGqRKFk%2BsDmBDs6EtW7uVdggnD7cm7AM1DdoM3mpvb76MOi6HTez4s6exfzgf1gT%2BCpCaaMuRNRGNTixHttvi77KGDzlWiMYr%2FDJm\x26continue\x3dhttps%3A%2F%2Fwww.google.com%2Faccounts%2FServiceLogin%3Fpassive%3Dtrue%26go%3Dtrue%26continue%3Dhttps%253A%252F%252Fwww.connectcommerce.com%252Fbin%252Flogin.mx%26service%3Daffiliatenetwork%26fss%3D1")

		*/
		$pattern = "/<meta http-equiv=\"refresh\".*url=&#39;(.*)&#39;\">/";
		if(preg_match($pattern, $result, $matches))
		{
			$dest_url = html_entity_decode($matches[1]);
			$request["postdata"] = "";
			$request["method"] = "get";
			$r = $this->oLinkFeed->GetHttpResult($dest_url,$request);
			$result = $r["content"];
		}
		
		//$request["postdata"] = "";
		//$r = $this->oLinkFeed->GetHttpResult("http://www.connectcommerce.com/partner/monthly_stats.html",$request);
		//$result = $r["content"];
		
		//if(stripos($result, "<a href=\"/bin/logout.mx\">Sign out</a></li>") === false)
		if(stripos($result, "__gwt_historyFrame") === false)
		{
			print_r($r);
			echo ("login performics failed \n");
			$this->oLinkFeed->clearHttpInfos($this->info["AffId"]);
			return false;
		}
		return true;
	}
	
	function getMerAffIDByURL($strURL)
	{
		return $this->getShortGANMerID($strURL);
	}
	
	function getShortGANMerID($strLongGANMerID)
	{
		if ($this->isShortGANMerID(($strLongGANMerID))){
			return $strLongGANMerID;
		}
		if (!$this->isLongGANMerID($strLongGANMerID)){
			return '';
		}
		$result = substr($strLongGANMerID, 2);
		while ((strlen($result) > 0) && ($result[0] == '0')){
			$result = substr($result, 1);
		}
		return 'K'.$result;
	}
	
	function isShortGANMerID($strID){
		if (strtoupper($strID[0]) == 'K'){
			return true;
		}
		else{
			return false;
		}
	}

	function getLongGANMerID($strShortGANMerID){
		if ($this->isLongGANMerID($strShortGANMerID)){
			return $strShortGANMerID;
		}

		if (strlen($strShortGANMerID) < 1){
			return '';
		}
		if (strtoupper($strShortGANMerID[0]) != 'K'){
			return '';
		}
		$strLongMerID = substr($strShortGANMerID, 1);
		//21000000000160024  // 15 0 
		for ($i=strlen($strLongMerID); $i<15;$i++){
			$strLongMerID = '0'.$strLongMerID;
		}
		$strLongMerID = '21'.$strLongMerID;
		return $strLongMerID;
	}
	
	function isLongGANMerID($strID){
		if (strlen($strID) == 17){
			if (substr($strID, 0, 2) == '21'){
				return true;
			}
		}
		return false;
	}
	
	function GetMerchantsByReltype(&$arrAllExistsMerchants)
	{
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "post",
			"postdata" => "", 
		);
		
		//enum('not apply','pending','approval','declined','expired','siteclosed') NULL
		/*$allRelType = array(
			"APPROVED" => array(0,"approval"),
			"AVAILABLE" => array(1,"not apply"),
			"PENDING" => array(2,"pending"),
			"DECLINED" => array(3,"declined"),
			"DEACTIVATED" => array(4,"siteclosed"),
		);*/
		
		//$strStatus = $allRelType[$reltype][1];
		
		//$request["postdata"] = "downloadType=TXT&exportType=Advertisers&exportParams=7%257C0%257C9%257Chttps%253A%252F%252Fwww.google.com%252Faffiliatenetwork%252F%257CF19924BDF2DA9EE84FEFF06A56F2D500%257Ccom.google.ads.affiliatenetwork.frontend.publisher.shared.BrowseAdvertiserRequest%252F4000652275%257Cjava.util.ArrayList%252F4159755760%257Cjava.lang.Long%252F4227064769%257Ccom.google.ads.affiliatenetwork.frontend.shared.AdvertiserRelationshipOptions%252F1979885950%257Ccom.google.common.collect.EmptyImmutableSet%252F4023150908%257Ccom.google.ads.affiliatenetwork.frontend.shared.CompanySortOptions%252F2620327683%257C%257C1%257C2%257C3%257C4%257C0%257C0%257C0%257C0%257C4%257C0%257CA%257C10%257C0%257C5%257CKts%257C6%257C" . $allRelType[$reltype][0] . "%257C7%257CA%257C8%257C1%257C9%257C&locale=zh_CN&currencyCode=USD&timeZone=America%2FChicago";
		$request["postdata"] = 'downloadType=CSV&exportType=Advertisers&exportParams=7%7C0%7C10%7Chttps%3A%2F%2Fwww.google.com%2Faffiliatenetwork%2F%7C16587C6718ABAD517776D30360F2C1CB%7Ccom.google.ads.affiliatenetwork.frontend.publisher.shared.BrowseAdvertiserRequest%2F804179719%7Cjava.util.ArrayList%2F4159755760%7Cjava.lang.Long%2F4227064769%7Ccom.google.ads.affiliatenetwork.frontend.publisher.shared.options.AdvertiserRelationshipDateFilterOptions%2F1545543534%7Ccom.google.ads.affiliatenetwork.frontend.publisher.shared.options.AdvertiserRelationshipOptions%2F264060724%7Ccom.google.common.collect.EmptyImmutableSet%2F4023150908%7Ccom.google.ads.affiliatenetwork.frontend.shared.common.options.CompanySortOptions%2F2501073923%7C%7C1%7C2%7C3%7C0%7C0%7C0%7C4%7C0%7CA%7C10%7C0%7C5%7CKts%7C6%7C5%7C7%7C0%7C8%7CA%7C9%7C5%7C10%7C4%7C0%7C&locale=zh_CN&currencyCode=USD&timeZone=America/Chicago';
		$strUrl = "https://www.google.com/affiliatenetwork/pubdownloadlinks";
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];
		
		$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"google_program.csv","cache_merchant");
		$this->oLinkFeed->fileCachePut($cache_file,$result);
		$fhandle = fopen($cache_file, 'r');
		
		print "Get Merchant List <br>\n";
		
		$Cnt = $UpdateCnt = 0;
		
		while($line = fgetcsv ($fhandle, 5000))
		{
			//[0] => Advertiser Id [1] => Advertiser name [2] => Site URL [3] => Category [4] => Status [5] => Date of last status change [6] => Is product feed enabled [7] => Commission Duration [8] => Advertiser 3 month EPC [9] => Advertiser 7 day EPC [10] => Logo URL [11] => Email [12] => Phone 
			foreach($line as $k => $v) $line[$k] = trim($v);
			
			if ($line[0] == '' || $line[0] == 'Advertiser Id') continue;
			
			$arr_update = array(
					"AffMerchantId" => "K" . $line[0],
					"AffId" => $this->info["AffId"],
					"MerchantName" => $line[1],
					"MerchantEPC30d" => $line[8],
					"MerchantEPC" => $line[9],
					"MerchantStatus" => $line[4],
					"MerchantRemark" => "",
				);
			
			$Cnt++;
			$this->oLinkFeed->fixEnocding($this->info,$arr_update,"merchant");
			if($this->oLinkFeed->UpdateMerchantToDB($arr_update,$arrAllExistsMerchants)) $UpdateCnt ++;
		}
		
		echo " Total: $Cnt ; Updated: $UpdateCnt  <br>\n";
		return array($Cnt,$UpdateCnt);
	}
	
	function GetMerchantListFromAff()
	{
		return array("AffectedCount" => 0,"UpdatedCount" => 0);
		//step 1,login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info,1,false);

		//step 2,get all exists merchant
		$arrAllExistsMerchants = $this->oLinkFeed->GetAllExistsAffMerIDForCheckByAffID($this->info["AffId"]);

		$nMerCnt = 0;
		$nMerUpdateCnt = 0;
		
		//$allRelType = array("A","P","D","I");
		/*$allRelType = array(
			"APPROVED" => 0,
			"AVAILABLE" => 1,
			"PENDING" => 2,
			"DECLINED" => 3,
			"DEACTIVATED" => 4,
		);
		
		foreach($allRelType as $_tp => $_gan_code)
		{
			list($Cnt,$UpdateCnt) = $this->GetMerchantsByReltype($_tp,$arrAllExistsMerchants);
			$nMerCnt += $Cnt;
			$nMerUpdateCnt += $UpdateCnt;
		}*/
		list($Cnt,$UpdateCnt) = $this->GetMerchantsByReltype($arrAllExistsMerchants);
		$nMerCnt += $Cnt;
		$nMerUpdateCnt += $UpdateCnt;
		//Step1 Get all approved merchants

		$UpdateCnt = $this->oLinkFeed->UpdateAllExistsAffMerIDButCannotFetched($this->info["AffId"], $arrAllExistsMerchants);
		echo "Found Exists Merchants But Cannot get from Aff: $UpdateCnt  <br>\n";
		
		$nMerUpdateCnt += $UpdateCnt;
		return array("AffectedCount" => $nMerCnt,"UpdatedCount" => $nMerUpdateCnt,);
	}

	function trim_ftp_record($str)
	{
		if(strlen($str) > 2 && substr($str,0,1) == '"' && substr($str,-1) == '"')
		{
			return substr($str,1,-1);
		}
		return $str;
	}
	
	function getCouponFeed()
	{
		$aff_id = $this->info["AffId"];
		$check_date = date('Y-m-d H:i:s');
		$arr_return = array(
			"AffectedCount" => 0,
			"UpdatedCount" => 0,
			"Detail" => array(),
		);
		
		$oFH = new FileHandler();
		//Link_Subscriptions_43884_07102012_1341908648560.txt
		$ftpuri = "ftp://cs_gan:WaQuC3kORqOTkwL@data.megainformationtech.com/";
		$ftp_file_list = $oFH->getFtpRawList($ftpuri,"file","/^Link_Subscriptions_.*\\.txt$/i","time_asc");

		$all_merchant_id = $this->oLinkFeed->GetAllExistsAffMerIDForCheckByAffID($this->info["AffId"]);
		$working_dir = $this->oLinkFeed->getWorkingDirByAffID($this->info["AffId"],"cache_ftp_feed");
		
		$arrToUpdate = array();
		
		foreach($ftp_file_list as $i => $fileinfo)
		{
			echo "processing file " . $fileinfo["FileName"] . " ...\n";
			
			if($this->debug) print "start download GAN newest links data from ".$fileinfo["FileName"]." , date: @ ". date("Y-m-d H:i:s")."<br/>\n";
			$local_file = $working_dir . $fileinfo["FileName"];
			$remote_file = $ftpuri . $fileinfo["FileName"];
			
			if(file_exists($local_file) && filesize($local_file) == $fileinfo["FileSize"])
			{
				echo "localfile $local_file exists, continue.\n";
				continue;
			}
			
			if(!$oFH->saveFtpFile($remote_file,$local_file))
			{
				mydie("die: download remote file ".$fileinfo["FileName"]." failed!\n");
			}
				
			if($this->debug) print "start update new links from ".$fileinfo["FileName"]." , date: @ ". date("Y-m-d H:i:s")."<br/>\n";
			
			//read file
			//$title = "Advertiser Site Name	Link ID	Link Name	Merchandising Text	Alt Text	Start Date	End Date	Tracking URL	Image URL	Image Height	Image Width	Link URL	Promo Type	Merchant ID";
			$title = "Advertiser Site Name	Link ID	Link Name	Merchandising Text	Alt Text	Start Date	End Date	Tracking URL	Image URL	Image Height	Image Width	Link URL	Merchant ID	Promo Code	Percent Off	Percent Off Min	Price Cut	Price Cut Min	Free Shipping	Free Shipping Min	Free Gift";
			$arr_title = explode("\t",$title);
			
			$onefilecount = 0;
			$handle = fopen ($local_file, "r");
			$line_number = 0;
			while (!feof($handle))
			{
				$line_number ++;
				$line = rtrim(fgets($handle));
				if($line_number == 1)
				{
					if($line != $title) mydie("die: get feed failed, title not matched: $line \n");
					continue;
				}
				
				if($line == "") continue;
				$row = array();
				$arr_temp = explode("\t",$line);
				foreach($arr_title as $i => $title)
				{
					$row[$title] = isset($arr_temp[$i]) ? $this->trim_ftp_record($arr_temp[$i]) : "";
				}
				
				$aff_mer_id = $row["Merchant ID"];
				if ($aff_mer_id == 'K113304') continue; // K113304  New York & Company ask us can only using CJ links. 
				
				if(!isset($all_merchant_id[$aff_mer_id]))
				{
					echo "warning: aff_mer_id($aff_mer_id) does not exist in local merchant list\n";
					print_r($arr_temp);
					print_r($row);
					continue;
				}
					
				$row["End Date"] = trim($row["End Date"]);
				$row["Start Date"] = trim($row["Start Date"]);
				if($row["End Date"] == '') $row["End Date"] = '0000-00-00';
				if($row["Start Date"] == '') $row["Start Date"] = '0000-00-00';

				$promo_type = $this->oLinkFeed->getPromoTypeByLinkContent($row["Link Name"] . ' ' . $row["Merchandising Text"]);
				$html_code = "";
				
				if ($row["Image URL"] != '')
				{
					$html_code = '<a href="' . $row["Tracking URL"] . '"><img src="' . $row["Image URL"] . '" alt="' . htmlspecialchars($row["Link Name"]) . '"/></a>';
				}
				else{
					$html_code = '<a href="' . $row["Tracking URL"] . '">' . htmlspecialchars($row["Link Name"]) . '</a>';
				}

				$image_url = '';
				$proc_status = 'pending';
				$src_lastupdate = '';
				
				$arr_one_link = array(
					"AffId" => $this->info["AffId"],
					"AffMerchantId" => $aff_mer_id,
					"AffLinkId" => $row["Link ID"],
					"LinkName" => $row["Link Name"],
					"LinkDesc" => $row["Merchandising Text"],
					"LinkStartDate" => $row["Start Date"],
					"LinkEndDate" => $row["End Date"],
					"LinkPromoType" => $promo_type,
					"LinkHtmlCode" => $html_code,
					"LinkOriginalUrl" => $row["Link URL"],
					"LinkImageUrl" => $row["Image URL"],
					"LinkAffUrl" => "",
					"DataSource" => "1",
				    "IsDeepLink" => 'UNKNOW',
				    "Type"       => 'promotion'
				);
				$this->oLinkFeed->fixEnocding($this->info,$arr_one_link,"feed");
				$arrToUpdate[] = $arr_one_link;
				$arr_return["AffectedCount"] ++;
				$onefilecount ++;
				if(sizeof($arrToUpdate) > 100)
				{
					$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($arrToUpdate);
					$arrToUpdate = array();
				}
			}//end while
			
			fclose($handle);
			
			if(sizeof($arrToUpdate) > 0)
			{
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($arrToUpdate);
				$arrToUpdate = array();
			}
			
			if($this->debug) print $fileinfo["FileName"] . " is processed $onefilecount lines. @ ".date("Y-m-d H:i:s")." \n";
		}//end foreach
		
		if(sizeof($arrToUpdate) > 0)
		{
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($arrToUpdate);
			$arrToUpdate = array();
		}
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'promotion');
		return $arr_return;
	}//end fun

	function getCouponFeed_web($retry=2)
	{
		$aff_id = $this->info["AffId"];
		
		$arr_return = array(
			"AffectedCount" => 0,
			"UpdatedCount" => 0,
			"Detail" => array(),
		);
		
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "post",
			"postdata" => "", 
		);
		
		if($this->debug) print "Getting CouponFeed(CSV) <br>\n";

		$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"links.dat","cache_links");
		if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
		{
			//login
			$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info,1,false);
			
			$request["method"] = "get";
			$strUrl = "http://www.google.com/affiliatenetwork/c.html?repType=links&pli=1";
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			
			for($i=0;$i<3;$i++)
			{
				$strUrl = "https://www.google.com/affiliatenetwork/pubdownloadlinks";
				$request["method"] = "post";
				$request["no_encoding"] = 1;
				$request["postdata"] = 'downloadType=CSV&exportType=Advertisers&exportParams=7%7C0%7C10%7Chttps%3A%2F%2Fwww.google.com%2Faffiliatenetwork%2F%7C16587C6718ABAD517776D30360F2C1CB%7Ccom.google.ads.affiliatenetwork.frontend.publisher.shared.BrowseAdvertiserRequest%2F804179719%7Cjava.util.ArrayList%2F4159755760%7Cjava.lang.Long%2F4227064769%7Ccom.google.ads.affiliatenetwork.frontend.publisher.shared.options.AdvertiserRelationshipDateFilterOptions%2F1545543534%7Ccom.google.ads.affiliatenetwork.frontend.publisher.shared.options.AdvertiserRelationshipOptions%2F264060724%7Ccom.google.common.collect.EmptyImmutableSet%2F4023150908%7Ccom.google.ads.affiliatenetwork.frontend.shared.common.options.CompanySortOptions%2F2501073923%7C%7C1%7C2%7C3%7C0%7C0%7C0%7C4%7C0%7CA%7C10%7C0%7C5%7CKts%7C6%7C5%7C7%7C0%7C8%7CA%7C9%7C5%7C10%7C4%7C0%7C&locale=zh_CN&currencyCode=USD&timeZone=America/Chicago';
			
				$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
				$result = $r["content"];
			
				if(strlen($result) > 6 * 1024 * 1024)
				{
					$result = $this->fixGoogleCsvFile($result);
					$this->oLinkFeed->fileCachePut($cache_file,$result);
					break;
				}
				else
				{
					//<meta http-equiv="refresh" content="0; url=&#39;http://www.google.com/affiliatenetwork/pubdownloadlinks?pli=1&amp;auth=DQAAALwAAAAoF1VroRm5wKwgtm2i8J04QAgTaw1ErvjexJ7qMa49bL549cvWdtnvfRTUAmZKgN3I-VzuEUK6gx7Ajv2HMFFm9gnMy2AfzdaDjXb50ySDtBAD29arcA9riC0Pdnee7hCOsF5EhbL3q5drfoOfXU0axvWnmGgujZuyt-D7PlAc6yUzBI_aY6_mHv_y-aWUOWvsaU73jkcC-Dxv9UvyJBHTAYXeXHlWfO1MBF9hYgqlDNhWnJElBAF_J8mO2U-dv6o&#39;">
					//google want to fix something or set something ????, we just let it go ...
					if(preg_match("/<meta http-equiv=\\\"refresh\\\".*url=&#39;(.*)&#39;\\\">/iU",$result,$matches))
					{
						$redir_url = html_entity_decode($matches[1]);
						$request["postdata"] = "";
						$r = $this->oLinkFeed->GetHttpResult($redir_url,$request);
						continue;
					}
					//usually this is caused by cookie issue, so we do retry once here
					if($retry > 0)
					{
						echo "warning: pubdownloadlinks failed, csv file size is too small(" . strlen($result) . "), retry it \n";
						echo "sleep 5 ...\n";
						sleep(5);
						$this->oLinkFeed->clearHttpInfos($aff_id);
						return $this->getCouponFeed($retry - 1);
					}
					
					print_r($r);
					mydie("die: pubdownloadlinks failed, csv file size is too small \n");
				}
			}
		}
		
		if(!file_exists($cache_file) || filesize($cache_file) < 1024 * 1024) return $arr_return;
		
		$all_merchant_id = $this->oLinkFeed->GetAllExistsAffMerIDForCheckByAffID($this->info["AffId"]);
		//Open CSV File
		$str_title = "Id,Name,Advertiser Id,Advertiser name,Tracking URL,Creative URL,Image Size,Tracking html code,Start Date,End Date,Time Zone,Creative Type,Category,Promotion Type,Advertiser 3 month EPC,Advertiser 7 day EPC,Merchandising Text";
		$arr_title = explode(",",$str_title);
		$col_count = sizeof($arr_title);
		
		$fhandle = fopen($cache_file, 'r');
		$arrToUpdate = array();
		$line_number = 0;
		while($line = fgetcsv($fhandle, 50000))
		{
			$line_number ++;

			if($line_number == 1)
			{
				if(implode(",",$line) != $str_title)
				{
					mydie("die: wrong title found: " . implode(",",$line) . "\n");
				}
				continue;
			}
			
			if($line[0] == '') continue;
			if(sizeof($line) != $col_count)
			{
				echo "warning: invalid line found: " . implode(",",$line) . "\n";
				print_r($line);
				//mydie("die: wrong content found\n");
				continue;
			}
			
			$row = array();
			foreach($arr_title as $i => $title) $row[$title] = $line[$i];
			
			$aff_mer_id = "K" . $row["Advertiser Id"];
			if(!isset($all_merchant_id[$aff_mer_id]))
			{
				echo "warning: aff_mer_id($aff_mer_id) does not exist in local merchant list\n";
				continue;
			}

			if ($aff_mer_id == 'K113304') continue; // K113304  New York & Company ask us can only using CJ links. 

			$promo_type = $this->oLinkFeed->getPromoTypeByLinkContent($row["Name"] . ' ' . $row["Merchandising Text"]);

			$html_code = $row["Tracking html code"];
			if(!$html_code || strtolower($html_code) == "banner")
			{
				if($row["Creative URL"])
				{
					$html_code = '<a href="'.$row["Tracking URL"].'"><img src="'.$row["Creative URL"].'"/></a>';
				}
			}
			else
			{
				$html_code = '<a href="'.$row["Tracking URL"].'">' . htmlspecialchars($row["Name"]) . '</a>';
			}

			$arr_one_link = array(
				"AffId" => $this->info["AffId"],
				"AffMerchantId" => $aff_mer_id,
				"AffLinkId" => $row["Id"],
				"LinkName" => $row["Name"],
				"LinkDesc" => $row["Merchandising Text"]?$row["Merchandising Text"]:$row["Name"],
				"LinkStartDate" => $row["Start Date"],
				"LinkEndDate" => $row["End Date"],
				"LinkPromoType" => $promo_type,
				"LinkHtmlCode" => $html_code,
				"LinkOriginalUrl" => $row["Tracking URL"],
				"LinkImageUrl" => "",
				"LinkAffUrl" => "",
				"DataSource" => "1",
			    "IsDeepLink" => 'UNKNOW',
			    "Type"       => 'promotion'
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
	}
	
	function fixGoogleCsvFile(&$str)
	{
		$lines = explode("\n",$str);
		$linecount = sizeof($lines);
		$titles = array();
		$field_connt = 0;
		
		//$need_concatenate = false;
		$first_line_index = 0;
		
		for($i=0;$i<$linecount;$i++) $lines[$i] = trim($lines[$i]);
		
		for($i=0;$i<$linecount;$i++)
		{
			if($i == 0)
			{
				$titles = explode(",",$lines[$i]);
				$field_connt = sizeof($field_connt);
				continue;
			}
			
			$fields = explode(",",$lines[$i]);
			$isGoodLine = (sizeof($fields) >= $field_connt && is_numeric($fields[0]) && strlen($fields[0]) >= 5);
			
			if($isGoodLine)
			{
				$first_line_index = 0;
			}
			else
			{
				if(!$first_line_index) $first_line_index = $i - 1;
				if($lines[$i])
				{
					$lines[$first_line_index] .= " " . $lines[$i];
					$lines[$i] = "";
				}
			}
		}
		
		return implode("\n",$lines);
	}
	
	function GetProgramByReltype()
	{
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "post",
			"postdata" => "", 
		);
		
		$objProgram = new ProgramDb();
		$arr_prgm = array();		
		
		//$strStatus = $allRelType[$reltype][1];
		$strUrl = "https://www.google.com/affiliatenetwork/pubdownloadlinks";
		
		$request["postdata"] = 'downloadType=CSV&exportType=Advertisers&exportParams=7%7C0%7C10%7Chttps%3A%2F%2Fwww.google.com%2Faffiliatenetwork%2F%7C16587C6718ABAD517776D30360F2C1CB%7Ccom.google.ads.affiliatenetwork.frontend.publisher.shared.BrowseAdvertiserRequest%2F804179719%7Cjava.util.ArrayList%2F4159755760%7Cjava.lang.Long%2F4227064769%7Ccom.google.ads.affiliatenetwork.frontend.publisher.shared.options.AdvertiserRelationshipDateFilterOptions%2F1545543534%7Ccom.google.ads.affiliatenetwork.frontend.publisher.shared.options.AdvertiserRelationshipOptions%2F264060724%7Ccom.google.common.collect.EmptyImmutableSet%2F4023150908%7Ccom.google.ads.affiliatenetwork.frontend.shared.common.options.CompanySortOptions%2F2501073923%7C%7C1%7C2%7C3%7C0%7C0%7C0%7C4%7C0%7CA%7C10%7C0%7C5%7CKts%7C6%7C5%7C7%7C0%7C8%7CA%7C9%7C5%7C10%7C4%7C0%7C&locale=zh_CN&currencyCode=USD&timeZone=America/Chicago';
		
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];
		
		if($r["code"] == 404) {
			$alert_subject = "pending links job GAN CSV GET failed @ " . date("Y-m-d H:i:s");			
			AlertEmail::SendAlert($alert_subject,nl2br($alert_subject));
		}else{
		
			print "Get Merchant List <br>\n";
			
			$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"google_program.csv","cache_merchant");
			$this->oLinkFeed->fileCachePut($cache_file,$result);
			$fhandle = fopen($cache_file, 'r');
			while($line = fgetcsv ($fhandle, 5000))
			{
				//[0] => Advertiser Id [1] => Advertiser name [2] => Site URL [3] => Category [4] => Status [5] => Date of last status change [6] => Is product feed enabled [7] => Commission Duration [8] => Advertiser 3 month EPC [9] => Advertiser 7 day EPC [10] => Logo URL [11] => Email [12] => Phone 
				foreach($line as $k => $v) $line[$k] = trim($v);
				
				if ($line[0] == '' || $line[0] == 'Advertiser Id') continue;
				if (!isset($line[1]) || $line[1] == '') continue;			
				
				$strStatus = isset($line[4]) ? $line[4] : "";			
				if($strStatus == 'Approved'){
					$StatusInAff = 'Active';
					$Partnership = "Active";
				}
				elseif($strStatus == 'Available'){
					$StatusInAff = 'Active';					
					$Partnership = "Expired";
				}
				elseif($strStatus == 'Pending'){
					$StatusInAff = 'Active';					
					$Partnership = "Pending";
				} 
				elseif($strStatus == 'Declined'){
					$StatusInAff = 'Active';
					$Partnership = "Declined";
				}
				elseif($strStatus == 'Deactived'){
					$StatusInAff = 'Offline';
					$Partnership = "NoPartnership";
				}
				else{
					$StatusInAff = 'Offline';
					$Partnership = "NoPartnership";
				}
				
				$CookieTime = "";
				if(isset($line[7])) $CookieTime = preg_replace("/[^0-9]/", "", $line[7]);
				$Contacts = "";
				if(isset($line[11])) $Contacts = "Email: ".addslashes($line[11]).", ";			
				if(isset($line[12])) $Contacts .= "Phone: ".addslashes($line[12]);
				$EPCDefault = "";
				if(isset($line[9])) $EPCDefault = preg_replace("/[^0-9.]/", "", $line[9]);
				$EPC90d = "";
				if(isset($line[8])) $EPC90d = preg_replace("/[^0-9.]/", "", $line[8]);
				
				
				$arr_prgm["K" . $line[0]] = array(
						"Name" => addslashes(trim($line[1])),
						"AffId" => $this->info["AffId"],					
						"Homepage" => isset($line[2]) ? addslashes($line[2]) : "",
						"CategoryExt" => isset($line[3]) ? addslashes($line[3]) : "",
						"CommissionExt" => "",//Commission Duration:".addslashes($line["Commission Duration"]),
						"CookieTime" => $CookieTime,
						"Contacts" => $Contacts,
						"ContestExt" => "",
						"IdInAff" => "K" . $line[0],					
						"StatusInAff" => addslashes($StatusInAff),						//'Active','TempOffline','Offline'
						"StatusInAffRemark" => addslashes($strStatus),
						"Partnership" => $Partnership,									//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
						"EPCDefault" => $EPCDefault,
						"EPC90d" =>  $EPC90d,					
						"LastUpdateTime" => date("Y-m-d H:i:s"),
					);
					
				if(count($arr_prgm) >= 200){
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
			$objProgram->setProgramOffline($this->info["AffId"]);
			$objProgram->setCountryInt($this->info["AffId"]);
		}
	}
	
	
	function GetProgramFromAff()
	{	
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
		
		$this->getProgramByApi();		
		$this->checkProgramOffline($this->info["AffId"], $check_date);

		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}
	
	function getProgramByApi()
	{
		echo "\tGet Program by api start\r\n";
		$program_num = 0;
		
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		/* 
		 * This script is used to get GAN data via GAN API,
		 * 1. We need google account
		 * 2. We need Google API Account
		 * 3. Our google account should authorise the GAN API access once at least, and never revoke it
		 * 4. Once we authroise the API access, we will get the referesh token and access token
		 * 5. The refresh token is used to get a new access token via Google OAuth 2.0
		 * 6. The GAN API authorised is done by Patrick's PC, or we can use OAuth 2.0 playground: https://code.google.com/oauthplayground/
		 * 7. Google limited the GAN API requests 1K/day
		 */
		
		try{
		
			define('CLIENT_ID', '454034404107-qlrdrg6kb787mumal7lihfm0god8kdt7.apps.googleusercontent.com');
			define('CLIENT_SECRET', 'pI3vqIIpOtoZGbmX5-ouKNfj');
			define('REFRESH_TOKEN', '1/NCvzc_1OHVGco4psRA8h6b_DDEY4C5-s8UIQqhxhMBw');
			define('GAN_AUTH_URI', 'https://accounts.google.com/o/oauth2/token');
		
			define('ROLE', 'publishers');
			define('ROLE_ID', 'K43884');
			define('GAN_EVENTS_URI', 'https://www.googleapis.com/gan/v1beta1/'.ROLE.'/'.ROLE_ID.'/advertisers');
			define('MAX_ERROR_RTRY', 5);
		
		
		    $postdata = array( 'refresh_token' =>   REFRESH_TOKEN,
			                   'client_id'     =>   CLIENT_ID,
			                   'client_secret' =>   CLIENT_SECRET,
			                   'grant_type'    =>   'refresh_token',
			                  );
		
			$ch = curl_init();	   
			curl_setopt($ch, CURLOPT_URL, GAN_AUTH_URI);
		    curl_setopt($ch, CURLOPT_POST, 1);
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
			$rt = curl_exec($ch);
			curl_close($ch);
		
			$rt = json_decode($rt);
		
			if(!$rt || !isset($rt->access_token) || !isset($rt->token_type))
				throw new Exception ("Query GAN API Failed because of invalid token");
			
		
			$access_token = $rt->access_token;
			$fws = array();
			$comms = array();
			$retry = 0;
			$reqs  = 0;
			$query_previous = '';
			do {
		
				if (isset($o->nextPageToken) && $o->nextPageToken != "") {
					$query = "access_token=".$access_token."&pageToken=".$o->nextPageToken.'&maxResults=100';
					$retry = 0;
					$query_previous = $query;
				}
				elseif ($reqs == 0) {
					$query = "access_token=".$access_token.'&maxResults=100';
					#$query = "access_token=".$access_token.'&eventDateMin='.$from_day.'&maxResults=100';
					$retry = 0;
				}
				else {
					//retry previous request because of error return
					if ($retry++ > MAX_ERROR_RTRY) {
						var_dump($rt);
						print_r($comms);
						throw new Exception ("GAN API Error return, reach at max times");
					}
					$query = $query_previous;
				}
		
				echo "=>Request: ". GAN_EVENTS_URI.'?'.$query."\n";
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, GAN_EVENTS_URI.'?'.$query);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$rt = curl_exec($ch);
				curl_close($ch);
				$reqs++;
		
				$o = json_decode($rt);
				//var_dump($rt);
				if (isset($o->items) && count($o->items) > 0){
				    foreach ($o->items as $i) {
  						$EPCDefault = "";
						if(isset($i->epcSevenDayAverage) && isset($i->epcSevenDayAverage->amount)){
							$EPCDefault = preg_replace("/[^0-9.]/", "", $i->epcSevenDayAverage->amount);
						}
						$EPC90d = "";
						if(isset($i->epcNinetyDayAverage) && isset($i->epcNinetyDayAverage->amount)){
							$EPC90d = preg_replace("/[^0-9.]/", "", $i->epcNinetyDayAverage->amount);
						}


						$StatusInAffRemark = "";
						$Partnership = "";
						$StatusInAff = "";
						if(isset($i->status)){
							$StatusInAffRemark = $i->status;
							if(strcasecmp($i->status, "APPROVED") == 0){
								$Partnership = "Active";
								$StatusInAff = 'Active';
							}
							elseif(strcasecmp($i->status, 'AVAILABLE') == 0){								
								$Partnership = "NoPartnership";
								$StatusInAff = 'Active';
							}
							elseif(strcasecmp($i->status, 'PENDING') == 0){								
								$Partnership = "Pending";
								$StatusInAff = 'Active';
							} 
							elseif(strcasecmp($i->status, 'Declined') == 0){								
								$Partnership = "Declined";
								$StatusInAff = 'Active';
							}
							elseif(strcasecmp($i->status, 'DEACTIVATED') == 0){							
								$Partnership = "NoPartnership";
								$StatusInAff = 'Offline';
							}
							else{								
								$Partnership = "NoPartnership";
								$StatusInAff = 'Offline';
							}
						}
												
						if(strpos($i->name, "TERM") !== false && strpos($i->name, "TERM") == 0){
							$StatusInAff = 'Offline';
						}
				    	
				    	$Contacts = "";
				    	if(isset($i->contactEmail) && !empty($i->contactEmail)){
				    		$Contacts.= "Email: ".$i->contactEmail;
				    	}
						if(isset($i->contactPhone) && !empty($i->contactPhone)){
				    		$Contacts.= ", Phone: ".$i->contactPhone;
				    	}
				    	
				    	$CookieTime = "";
						if(isset($i->commissionDuration) && !empty($i->commissionDuration)){
							$CookieTime = intval($i->commissionDuration/86400);
						}
				    	
				    	$JoinDate = "0000-00-00 00:00:00";
				    	if(isset($i->joinDate) && !empty($i->joinDate)){
				    		$JoinDate = date("Y-m-d H:i:s", strtotime($i->joinDate));
				    	}
						
						$arr_prgm["K" . $i->id] = array(							
							"AffId" => $this->info["AffId"],							
							"IdInAff" => "K" . $i->id,
							"Name" => addslashes($i->name),
							"Homepage" => isset($i->siteUrl) ? addslashes($i->siteUrl) : "",
							"CategoryExt" => isset($i->category) ? addslashes($i->category) : "",
							"RankInAff" => isset($i->payoutRank) ? addslashes($i->payoutRank) : "",
							"Contacts" => addslashes($Contacts),
							"JoinDate" => $JoinDate,
							"StatusInAffRemark" => addslashes($StatusInAffRemark),
							"Partnership" => $Partnership,
							"StatusInAff" => $StatusInAff,
							"CookieTime" => $CookieTime,
							"EPCDefault" => $EPCDefault,
							"EPC90d" =>  $EPC90d,
							"LastUpdateTime" => date("Y-m-d H:i:s"),				    				    		
						);
						
						$program_num++;
						
					    if(count($arr_prgm) >= 200){
							$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
							$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
							$arr_prgm = array();
						}			
					}
				}
		
			} while((isset($o->nextPageToken) && $o->nextPageToken != "") || isset($o->error));
		
		}
		catch (Exception $e) {
			echo $e->getMessage()."\n";
			//exit(1);
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
