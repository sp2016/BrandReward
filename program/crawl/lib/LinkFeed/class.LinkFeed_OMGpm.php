<?php

require_once 'text_parse_helper.php';

class LinkFeed_OMGpm //for 57,125,163.240
{
	function GetProgramFromAff()
	{	
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
		
			$this->GetProgramByApi();
		
		$this->checkProgramOffline($this->info["AffId"], $check_date);

		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}
	
	function getCouponFeed(){
	    
	    $check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0,"UpdatedCount" => 0,"Detail" => array());
		
		/* if(SID == 'bdg02'){
			$aff_info = array(	//57 => array("Agency" => 1, "Affiliate" => 141509, "Hash" => "5627c3994678168f14c89f2e0ac7d16c"),
								//125 => array("Agency" => 47, "Affiliate" => 191911, "Hash" => "8E1422CDB3AE0D628265F4B733C00EFB"),
								163 => array("Agency" => 118, "Affiliate" => 1023249, "Hash" => "370DB2E3E2C949D8B9E2134D43DD025B"),
								240 => array("Agency" => 95, "Affiliate" => 1030347, "Hash" => "18127EFF99D78FC02412E148499A8322")
			);
		}else{
			$aff_info = array(	57 => array("Agency" => 1, "Affiliate" => 1041949, "Hash" => "479704A06A9A7FD384BDBBB2F71EC5E1"),
								125 => array("Agency" => 47, "Affiliate" => 1031465, "Hash" => "F283ACEAC391AD2E01347D3CBE85A9D1"),
								163 => array("Agency" => 118, "Affiliate" => 1030990, "Hash" => "E138592C67BA3B6CD3E802CC06515CD3"),
								240 => array("Agency" => 95, "Affiliate" => 1045463, "Hash" => "5076F899ABE19BA931617AF2E3516836")
							);
		} */
		
		echo "getCouponFeed...\n";
		$request = array("AffId" => $this->info["AffId"], "method" => "get",);
		$affid = $this->info["AffId"];
		$url = sprintf("https://admin.optimisemedia.com/v2/VoucherCodes/Affiliate/ExportVoucherCodes.ashx?Auth=%s:%s:%s&Status=Active&Format=Csv&Agency=%s", $this->Agency, $this->Affiliate, $this->Hash, $this->Agency);
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r["content"];
		$lines = explode("\n", $content);
		$count = 0;
		$links = array();
		foreach ($lines as $line){
			$count ++;
			if ($count == 1){
				//VoucherCodeId	Code	Title	Description	ActivationDate	ExpiryDate	TrackingUrl	CategoryName	Status	Addedon	Merchant	Product	Type	Discount				
				continue;
			}
			$line = trim($line);
			if (empty($line)){
				echo "empty line...\n";
				continue;
			}
			$row = str_getcsv($line, ",", '"', "\\");
			$linkUrl = $row[6];
			if (!preg_match("@PID=(\d+)&@", $linkUrl, $g)){
				echo "unrecognized url format $line \n";
				continue;
			}
			$link = array(
				"AffId" => $this->info["AffId"],
				"AffMerchantId" => $g[1],
				"AffLinkId" => $row[0],
				"LinkName" => trim($row[2]),
				"LinkDesc" => trim($row[3]),
				"LinkStartDate" => parse_time_str($row[4], 'd/m/Y H:i', false),
				"LinkEndDate" => parse_time_str($row[5], 'd/m/Y H:i', false),
				"LinkPromoType" => 'deal',
				"LinkHtmlCode" => '',
				"LinkCode" => trim($row[1]),
				"LinkOriginalUrl" => "",
				"LinkImageUrl" => "",
				"LinkAffUrl" => $linkUrl,
				"DataSource" => $this->DataSource["feed"],
			    "Type"       => 'promotion'
			);			
			if (!empty($link['LinkCode'])){
				$link['LinkPromoType'] = 'coupon';
				if (strtolower($link['LinkCode']) == 'no voucher code'){
					$link['LinkPromoType'] = 'deal';
					$link['LinkCode'] = '';
				}
			}else{
				$code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc']);
				if (!empty($code)){
					$link['LinkCode'] = $code;
					$link['LinkPromoType'] = 'coupon';
				}
			}
			$link['LinkHtmlCode'] = create_link_htmlcode($link);
			if ( empty($link['AffLinkId']) || empty($link['LinkAffUrl'])){
				echo "unreconginzed link format $line \n";
				continue;
			}
            elseif(empty($link['LinkName'])){
                $link['LinkPromoType'] = 'link';
            }
			$arr_return["AffectedCount"] ++;
			$links[] = $link;
			if(sizeof($links) > 100)
			{
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links,$check_date,'promotion');
				$links = array();
			}
		}
		if (sizeof($links) > 0)
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links,$check_date,'promotion');
		echo sprintf("get coupon by api...%s link(s) found.\n", $arr_return['AffectedCount']);
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'promotion');
		return $arr_return;
	}


    function GetAllProductsByAffId()
	{
        $check_date = date('Y-m-d H:i:s');
        $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
        $request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "", 'timeout'=>600,);
        $productNumConfig = $this->oLinkFeed->product_program_num_config($this->info["AffId"]);
        $productNumConfigAlert = '';
        $isAssignMerchant = FALSE;
        $this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);

		$productListHtml = $this->oLinkFeed->GetHttpResult('https://admin.optimisemedia.com/v2/ProductFeeds/Management/Affiliate/AffiliateFeedExport.aspx',$request);
		$productListHtml = preg_replace('@>\s+<@', '><', $productListHtml['content']);

		//idinaff productNum
		$strLineStart = 'ContentPlaceHolder1_grdFeeds_DXDataRow';
		$nLineStart = 0;
		while ($nLineStart >= 0){

            $links = array();
			$nLineStart = stripos($productListHtml, $strLineStart, $nLineStart);
			if ($nLineStart === false) break;

			$merchantId = intval($this->oLinkFeed->ParseStringBy2Tag($productListHtml, array('<td', '>'), '<', $nLineStart));
			$ProductNum = intval(str_replace(',','', $this->oLinkFeed->ParseStringBy2Tag($productListHtml, array('<td','<td', '<td','>'), '<', $nLineStart)));
			$downloadUrl = html_entity_decode($this->oLinkFeed->ParseStringBy2Tag($productListHtml, 'class="button" href="', '"', $nLineStart));

			if($ProductNum == 0 || $ProductNum > 50000) continue;

            echo $merchantId .'---' . $ProductNum;
            $crawlMerchantsActiveNum = 0;
            $setMaxNum  = isset($productNumConfig[$merchantId]) ? $productNumConfig[$merchantId]['limit'] :  100;
            $isAssignMerchant =  	isset($productNumConfig[$merchantId]) ? TRUE : FALSE;
			//download product file
			$fileName =  "data_" . date("Ymd") .'product_feed_'.$merchantId.'.xml';
			$product_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],$fileName, "product", true);
			if(!$this->oLinkFeed->fileCacheIsCached($product_file)){
				$r = $this->oLinkFeed->GetHttpResult($downloadUrl,$request);
				$this->oLinkFeed->fileCachePut($product_file,$r['content']);
			}
            $data = json_decode(json_encode(simplexml_load_string(file_get_contents($product_file))),true);

			foreach ($data['Product'] as $pValue){
				$AffProductId = $pValue['ProductID'];
				
				if(is_array($pValue['ProductImageMediumURL']) && !empty($pValue['ProductImageMediumURL'])){
				    $ProductImage = $pValue['ProductImageMediumURL'][0];
				}else{
				    $ProductImage = $pValue['ProductImageMediumURL'];
				}
				if(!$ProductImage) continue;

				$product_path_file = $this->oLinkFeed->getProductImgFilePath($this->info["AffId"],"{$merchantId}_".urlencode($AffProductId).".png", PRODUCTDIR);
				if(!$this->oLinkFeed->fileCacheIsCached($product_path_file))
				{
					$file_content = $this->oLinkFeed->downloadImg($ProductImage);
					if(!$file_content) //下载不了跳过。
						continue;
					$this->oLinkFeed->fileCachePut($product_path_file, $file_content);
				}

				if(empty($pValue['ProductDescription']))
				    $pValue['ProductDescription'] = '';
				$link = array(
					"AffId" => $this->info["AffId"],
					"AffMerchantId" => $merchantId,
					"AffProductId" => $AffProductId,
					"ProductName" => html_entity_decode(addslashes($pValue['ProductName'])),
					"ProductCurrency" => addslashes($pValue['ProductPriceCurrency']),
					"ProductPrice" => $pValue['DiscountedPrice'] ? $pValue['DiscountedPrice'] : $pValue['ProductPrice'] ,
					"ProductOriginalPrice" => $pValue['ProductPrice'],
					"ProductRetailPrice" =>'',
					"ProductImage" => addslashes($ProductImage),
					"ProductLocalImage" => addslashes($product_path_file),
					"ProductUrl" => addslashes($pValue['ProductURL']),
					"ProductDestUrl" => '',
					"ProductDesc" => html_entity_decode(addslashes($pValue['ProductDescription'])),
					"ProductStartDate" => '',
					"ProductEndDate" => '',
				);

				//print_r($link);exit;
				$links[] = $link;
				$crawlMerchantsActiveNum ++;
                if (count($links) >= 100)
				{
					$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateProductToDB($links);
					$links = array();
				}
				//大于最大数跳出
				if($crawlMerchantsActiveNum >= $setMaxNum){
				    break;
				}
			}
			if (count($links)) {
                $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateProductToDB($links);
                $links = array();
            }
            
            if($isAssignMerchant){
                $productNumConfigAlert .= "AFFID:".$this->info["AffId"].",Program({$merName}),Crawl Count($crawlMerchantsActiveNum),Total Count({$ProductNum}) \r\n";
            }
		}

		echo $productNumConfigAlert.PHP_EOL;
        $this->oLinkFeed->checkProductExists($this->info["AffId"], $check_date);
        return $arr_return;
    }

	
	function GetAllLinksByAffId()
	{
	    $check_date = date('Y-m-d H:i:s');
	    $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
	    
	    $request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "");
	    $this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
	    $r = $this->oLinkFeed->GetHttpResult('https://admin.optimisemedia.com/v2/creative/affiliate/adcentre.aspx', $request);
	    
	    $WID = $this->WID;
	    $regionID = array(
	    		57 => 1,
	    		125 => 2,
	    		163 => 3,
	    		240 => 5
	    );
	    $request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "");
	    preg_match('/<input type="hidden" name="__VIEWSTATE" id="__VIEWSTATE" value="(.*?)" \/>/i',$r["content"],$matches1);
	    $__VIEWSTATE = $matches1[1];
	    preg_match('/<input type="hidden" name="__VIEWSTATEGENERATOR" id="__VIEWSTATEGENERATOR" value="(.*?)" \/>/i',$r["content"],$matches2);
	    $__VIEWSTATEGENERATOR = $matches2[1];
	    $request['postdata'] = '__EVENTTARGET=&__EVENTARGUMENT=&__LASTFOCUS=&__VIEWSTATE='.$__VIEWSTATE.'&__VIEWSTATEGENERATOR='.$__VIEWSTATEGENERATOR.'&ctl00$Uc_Navigation1$ddlNavSelectMerchant=0&ctl00$ContentPlaceHolder1$Uc_containersearch1$drpSize=&ctl00$ContentPlaceHolder1$Uc_containersearch1$txtSize=&ctl00$ContentPlaceHolder1$Uc_containersearch1$drpType=7&ctl00$ContentPlaceHolder1$Uc_containersearch1$drpMerchant=0&ctl00$ContentPlaceHolder1$Uc_containersearch1$drpProduct=-1&ctl00$ContentPlaceHolder1$Uc_containersearch1$cmdSearch=Search&ctl00$ContentPlaceHolder1$Uc_containersearch1$proghiddenfield=&ctl00$ContentPlaceHolder1$Uc_containersearch1$hdnUserRole=0';
	    $r = $this->oLinkFeed->GetHttpResult('https://admin.optimisemedia.com/v2/creative/affiliate/adcentre.aspx', $request);
	    
	    preg_match_all('/<li style="list-style: none;">(.*?)<\/li>/ms',$r["content"],$matches);
	    $links = array();
	    foreach ($matches[1] as $value){
	        
	        preg_match('/<input name="hiddenPid-\d+" type="hidden" id="hiddenPid-\d+" value="(.*?)" \/>/i',$value,$matches);
	        $AffMerchantId = $matches[1];
	        preg_match('/<input name="hiddencid-\d+" type="hidden" id="hiddencid-\d+" value="(.*?)" \/>/i',$value,$matches);
	        $AffLinkId = $matches[1];
	        preg_match('/<div class="name">(.*?)<\/div>/ims',$value,$matches);
	        $LinkName = trim(strip_tags($matches[1]));
	        
	        $TrackingUrl = "http://clk.omgt".$regionID[$this->info['AffId']].".com/?AID=$this->Affiliate&PID=$AffMerchantId&WID=$WID";
	        $LinkHtmlCode = '<a href="http://clk.omgt'.$regionID[$this->info['AffId']].'.com/?AID='.$this->Affiliate.'&PID='.$AffMerchantId.'&CRID='.$AffLinkId.'&WID='.$WID.'">Link Text Goes Here</a>';
	        $link = array(
	            "AffId" => $this->info["AffId"],
	            "AffMerchantId" => $AffMerchantId,
	            "AffLinkId" => $AffLinkId,
	            "LinkName" => $LinkName,
	            "LinkDesc" => $LinkName,
	            "LinkStartDate" => '0000-00-00',
	            "LinkEndDate" => '0000-00-00',
	            "LinkPromoType" => 'N/A',
	            "LinkOriginalUrl" => "",
	            "LinkHtmlCode" => $LinkHtmlCode,
	            "LinkAffUrl" => $TrackingUrl,
	            "DataSource" => "",
	            "IsDeepLink" => 'UNKNOWN',
	            "Type"       => 'link'
	        );
	        $link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
	        if (empty($link['AffLinkId']) || empty($link['LinkName']))
	            continue;
	        $arr_return['AffectedCount'] ++;
	        $links[] = $link;
	    }
	    
	    if (count($links) > 0)
	        $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
	    $this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link');
	    return $arr_return;
	}
	
	function GetProgramByPage()
	{
		echo "\tGet Program by page start\r\n";
		$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"{$this->info["AffId"]}_".date("YW").".dat", "program", true);
		$cache = array();
		if($this->oLinkFeed->fileCacheIsCached($cache_file)){
			$cache = file_get_contents($cache_file);
			$cache = json_decode($cache,true);
		}
		
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;

		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "get",
		);

		$aff_country_id = array(	57 => array(1, 2),		//United Kingdom, France, United States(20)
									125 => array(22, 24),	//Australia, New Zealand
									163 => array(0, 110, 135, 170, 189, 205, 228), //ALL, Indonesia, Malaysia, Philippines, Singapore, Thailand, Global
									240 => array(26)
								);

		$aff_info = array(	57 => array("Agency" => 1, "Affiliate" => 141509, "Hash" => "5627c3994678168f14c89f2e0ac7d16c", "WID" => 30634),
							125 => array("Agency" => 47, "Affiliate" => 1031465, "Hash" => "F283ACEAC391AD2E01347D3CBE85A9D1", "WID" => 66237),
							163 => array("Agency" => 118, "Affiliate" => 1030990, "Hash" => "22903A290D34C7A39487A7CDE43F3CBB", "WID" => 42902),
							240 => array("Agency" => 95, "Affiliate" => 1045463, "Hash" => "5076F899ABE19BA931617AF2E3516836", "WID" => 76932)
						);
		$country_id_arr = $aff_country_id[$this->info["AffId"]];
		$Agency = $aff_info[$this->info["AffId"]]["Agency"];
		$Affiliate = $aff_info[$this->info["AffId"]]["Affiliate"];
		$Hash = $aff_info[$this->info["AffId"]]["Hash"];
		$WID = "&WID=".$aff_info[$this->info["AffId"]]["WID"];
		
		
		//login step 1;
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		
		//get Details url start
		$offset = 0;
		$goToNextPage = true;
		$proListUrl   = array();
		while ($goToNextPage){
		    $r = $this->oLinkFeed->GetHttpResult("https://admin.optimisemedia.com/en/clientarea/affiliates/affiliate_campaigns.asp?offset=$offset", $request);
		    $result = $r["content"];
		    if(preg_match_all('/<a class="button" href="(.*?)">Details<\/a>/i', $result,$matches));
		    {
		        $proListUrl = array_merge($proListUrl,$matches[1]);
		    }
		    
		    if(stripos($result,'<a href="/en/clientarea/affiliates/affiliate_campaigns.asp?offset=-1" class="tableLink">Last</a>') === false){ //鏄惁鏈�鍚庝竴椤�
		        $goToNextPage = false;
		        break;
		    }
		    $offset += 50;
		}
		
		//get Details url end
		 
		foreach($country_id_arr as $country_id){			
			print "check country($country_id)\n";
			// get program from csv.
			$str_header = '"MerchantName","MerchantLogoURL","ProductName","ProductDescription","PID","Sector","CountryCode","PayoutType","CookieDuration","ProductFeedAvailable","DeepLinkEnabled","UidTracking","ProgrammeStatus","WebsiteURL","TrackingURL","Commission"';
			$cache_filecsv = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"program_country{$country_id}.csv","cache_merchant");
			if(!$this->oLinkFeed->fileCacheIsCached($cache_filecsv))
			{
				$strUrl = "http://admin.omgpm.com/v2/Reports/Affiliate/ProgrammesExport.aspx?Agency=$Agency&Country={$country_id}&Affiliate=$Affiliate&Search=&Sector=0&UidTracking=False&PayoutTypes=&ProductFeedAvailable=False&Format=CSV&AuthHash=$Hash&AuthAgency=$Agency&AuthContact=$Affiliate&ProductType=0";	
				$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
				$result = $r["content"];
				print "Get country($country_id) files \n";
				if(stripos($result,$str_header) === false) mydie("die: wrong csv file: $cache_filecsv, url: $strUrl");
				$this->oLinkFeed->fileCachePut($cache_filecsv,$result);	
			}
			
			//Open CSV File			
			$fhandle = fopen($cache_filecsv, 'r');
			
			while($line = fgetcsv ($fhandle, 5000))
			{
				foreach($line as $k => $v) $line[$k] = trim($v);			
				
				if (trim($line[1]) == 'MerchantLogoURL') continue;
				
				$MerchantName = $line[0];			
				$ProductName = $line[2];
				$desc = $line[3];
				$IdInAff = intval($line[4]);			
				$CategoryExt = $line[5];
				$TargetCountryExt = $line[6];
				$PayoutType = $line[7];			
				$ReturnDays = $line[8];
				//$ProductFeedAvailable = $line[9];
				$SupportDeepurl = $line[10];			
				//$UidTracking = $line[11];
				$StatusInAffRemark = $line[12];
				$Homepage = $line[13];			
				$AffDefaultUrl = $line[14];
				$CommissionExt = $line[15];
				
				
				$prgm_name = $MerchantName . "-" . $ProductName;
				
				$CommissionExt .= "PayoutType: ".$PayoutType;
				
				if($StatusInAffRemark == "Not Applied"){
					$Partnership = "NoPartnership";
				}elseif($StatusInAffRemark == "Rejected"){
					$Partnership = "Declined";
				}elseif($StatusInAffRemark == "Live"){
					$Partnership = "Active";
				}elseif($StatusInAffRemark == "Cancelled"){
					$Partnership = "Expired";
				}elseif($StatusInAffRemark == "Waiting"){
					$Partnership = "Pending";
				}else{
					$Partnership = "NoPartnership";
				}
				
				if($AffDefaultUrl) $AffDefaultUrl.=$WID;
				
				if(stripos($prgm_name, "closed") !== false){
					$StatusInAff = 'Offline';
				}else{
					$StatusInAff = 'Active';
				}
				//Terms And Conditions
			    //鎵�$Terms And Conditions瀵瑰簲鐨勮缁嗗湴鍧�;
				$AllowNonaffPromo = 'UNKNOWN';
				$AllowNonaffCoupon = 'UNKNOWN';
				foreach ($proListUrl as $proV){
					if(intval($this->oLinkFeed->ParseStringBy2Tag($proV, 'ProductID=', "&")) == $IdInAff) {
						$prgm_url = "https://admin.optimisemedia.com/".$proV;
						$request["method"] = "get";
						if(!isset($cache[$IdInAff]['term'])){
							$terms_arr = $this->oLinkFeed->GetHttpResult($prgm_url,$request);
							if($terms_arr['code'] == 200){
								$terms_info = trim($this->oLinkFeed->ParseStringBy2Tag($terms_arr['content'], '<div id="ContentPlaceHolder1_AcceptTermsPanel">', '</div>'));
								$cache[$IdInAff]['term'] = "1";
						        //print_r($terms_info);exit;
								$AllowNonaffPromo  = 'UNKNOWN';
								$AllowNonaffCoupon = 'UNKNOWN';
								$matches = array();
						        if(preg_match('/<a id="ContentPlaceHolder1_TermCHyperLink"\s+href="(\S+)"\s+target="_blank">/',$terms_info,$matches)){
						            $terms_detail_url = "https://admin.optimisemedia.com/v2/programmes/affiliate/$matches[1]";
						            $request["method"] = "get";
						            if(!isset($cache[$IdInAff]['allow'])){
										$terms_detail = $this->oLinkFeed->GetHttpResult($terms_detail_url,$request);
										if($terms_detail['code'] == 200){
											$cache[$IdInAff]['allow'] = "1";
											if(stripos($terms_detail['content'], "The Affiliate shall only use the Affiliate Hosted Content provided through the Affiliate Account to promote the Programme") !== false){
												$AllowNonaffPromo = 'NO';
												$AllowNonaffCoupon = 'NO';
								            }
							            }
						            }
						        }
					        }
				        }
				        break;
				    }
				}
				 
				$arr_prgm[$IdInAff] = array(
					"AffId" => $this->info["AffId"],	
					"IdInAff" => $IdInAff,
					"Name" => addslashes($prgm_name),
					"CategoryExt" => addslashes($CategoryExt),
					"TargetCountryExt" => $TargetCountryExt,
					"Homepage" => addslashes($Homepage),
					"Description" => addslashes($desc),				
					"CommissionExt" => addslashes($CommissionExt),
					"CookieTime" => addslashes($ReturnDays),
					"StatusInAffRemark" => addslashes($StatusInAffRemark),
					"StatusInAff" => $StatusInAff,						//'Active','TempOffline','Offline'
					"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'						
					"LastUpdateTime" => date("Y-m-d H:i:s"),
					//"DetailPage" => $prgm_url,
					"SupportDeepurl" => $SupportDeepurl,
					"AffDefaultUrl" => $AffDefaultUrl,
				    //"AllowNonaffPromo" => $AllowNonaffPromo,
				    //"AllowNonaffCoupon"=> $AllowNonaffCoupon
				);
				if($AllowNonaffPromo != 'UNKNOWN') $arr_prgm[$IdInAff]['AllowNonaffPromo'] = $AllowNonaffPromo;
				if($AllowNonaffCoupon != 'UNKNOWN') $arr_prgm[$IdInAff]['AllowNonaffCoupon'] = $AllowNonaffCoupon;
				$program_num++;
				if(count($arr_prgm) >= 100){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
			}//while
			fclose($fhandle);
		}//foreach
				
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
			unset($arr_prgm);
		}
		
		
		/*$r = $this->oLinkFeed->GetHttpResult("https://admin.omgpm.com/en/clientarea/affiliates/affiliate_campaigns.asp", $request);			
		$result = $r["content"];
				
		//parse HTML	
		$strLineStart = '<th>Merchant</th>';
		$nLineStart = stripos($result, $strLineStart, 0);		
	
		$strLineStart = '<tr';
		while ($nLineStart >= 0){
			$nLineStart = stripos($result, $strLineStart, $nLineStart);
			if ($nLineStart === false) break;
			
			$StatusInAff = 'Active';
			//merchant name
			$strMerName = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>', "</td>", $nLineStart));
			if ($strMerName === false) break;
			if(stripos($strMerName, "Closed") !== false){
				$StatusInAff = 'Offline';
			}

			//program name
			$prgm_name = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>', "</td>", $nLineStart));
			if ($prgm_name === false) break;
			if(stripos($prgm_name, "Closed") !== false){
				$StatusInAff = 'Offline';
			}
			
			$prgm_name = trim(str_ireplace(array("(Closed)", "Closed -"), "", $prgm_name));
			$CommissionExt = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>', "</td>", $nLineStart));
			$PayoutType = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>', "</td>", $nLineStart));
			$CookieTime = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>', "</td>", $nLineStart));
			$ProductFeed = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>', "</td>", $nLineStart));
			$UID = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>', "</td>", $nLineStart));
			$StatusInAffRemark = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>', "</td>", $nLineStart));
			
			$Partnership = "NoPartnership";
			if($StatusInAffRemark == "Live"){
				$Partnership = "Active";
			}elseif($StatusInAffRemark == "Cancelled"){
				$Partnership = "Expired";
			}elseif($StatusInAffRemark == "Rejected"){
				$Partnership = "Declined";
			}elseif($StatusInAffRemark == "Not Applied"){
				$Partnership = "Declined";
			}elseif($StatusInAffRemark == "Waiting"){
				$Partnership = "Pending";
			}
			
			$prgm_url = "https://admin.omgpm.com".trim($this->oLinkFeed->ParseStringBy2Tag($result, 'href="', '"', $nLineStart));
			$prgm_id = intval($this->oLinkFeed->ParseStringBy2Tag($prgm_url, 'ProductID=', "&"));
					
			$arr_prgm[$prgm_id] = array(
				"AffId" => $this->info["AffId"],	
				"IdInAff" => $prgm_id,
				"Name" => addslashes($prgm_name),
				//"Homepage" => addslashes($Homepage),
				//"Description" => addslashes($desc),
				"StatusInAffRemark" => $StatusInAffRemark,
				"CommissionExt" => addslashes($CommissionExt),
				"CookieTime" => $CookieTime,
				"StatusInAff" => $StatusInAff,						//'Active','TempOffline','Offline'
				"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'						
				"LastUpdateTime" => date("Y-m-d H:i:s"),
				"DetailPage" => $prgm_url,
			);
		}*/
		$cache = json_encode($cache);
		$this->oLinkFeed->fileCachePut($cache_file, $cache);
		
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
	
	function GetProgramByApi(){
		echo "\tGet Program by api start\r\n";
		$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"{$this->info["AffId"]}_".date("YW").".dat", "program", true);
		$cache = array();
		if($this->oLinkFeed->fileCacheIsCached($cache_file)){
			$cache = file_get_contents($cache_file);
			$cache = json_decode($cache,true);
		}
		
		
		//login step 1;
		if (SID == 'bdg02')
			$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
	
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
	
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => "get",
		);
	
		/* $aff_country_id = array(	57 => array(1, 2),		//United Kingdom, France, United States(20)
									125 => array(22, 24),	//Australia, New Zealand
									163 => array(0, 110, 135, 170, 189, 205, 228), //ALL, Indonesia, Malaysia, Philippines, Singapore, Thailand, Global
									240 => array(26)
								);
		if(SID == 'bdg02'){
			$aff_info = array(	//57 => array("Agency" => 1, "Affiliate" => 1023249, "API_Key" => "", "private_key" => ""),				//UK
								//125 => array("Agency" => 47, "Affiliate" => 1023249, "API_Key" => "", "private_key" => ""),			//AU
								163 => array("Agency" => 118, "Affiliate" => 1023249, "API_Key" => "92552a1f-2701-4cfb-9815-6e2e0023577d", "private_key" => "515f6b7921b241dc93397096175e2449"),			//SE Asia
								240 => array("Agency" => 95, "Affiliate" => 1030347, "API_Key" => "1489652a-adbe-4bca-adeb-433daecb89b9", "private_key" => "bf6264dd1f664e549e7ab0b7c7b7ffd7")				//IN
							);
		}else{
			$aff_info = array(	57 => array("Agency" => 1, "Affiliate" => 1041949, "API_Key" => "82d41f69-2d43-4d1f-b6ca-6ad512be570e", "private_key" => "49f305e4c43744c0bcafecb94562350a"),				//UK
								125 => array("Agency" => 47, "Affiliate" => 1031465, "API_Key" => "a64d7fbb-3858-46e3-b5cd-8e96b6b59cdc", "private_key" => "b487e9da76a240ea8f5c5ac69b97c5e5"),			//AU
								163 => array("Agency" => 118, "Affiliate" => 1030990, "API_Key" => "c5e663e3-e87f-4455-b2b9-9d36ec183208", "private_key" => "df4e69415924469f9c181259b957b408"),			//SE Asia
								240 => array("Agency" => 95, "Affiliate" => 428397, "API_Key" => "4356018f-7fb4-4a4b-a9c8-7ecfd4fd661a", "private_key" => "554b9f4086ca402a9a555ae75a6bc1e7")				//IN
							);
		}
		$country_id_arr = $aff_country_id[$this->info["AffId"]]; */
		
		$Agency = $this->Agency;
		$Affiliate = $this->Affiliate;
		
		//get Details url start
		date_default_timezone_set("UTC");
		$t = microtime(true);
		$micro = sprintf("%03d",($t - floor($t)) * 1000);
		$utc = gmdate('Y-m-d H:i:s.', $t).$micro;
		$sig_data= $utc;
		$API_Key = $this->API_Key;
		$private_key = $this->private_key;
		
		$concateData = $private_key.$sig_data;
		$sig = md5($concateData);
		$progm_url = "https://api.omgpm.com/network/OMGNetworkApi.svc/v1.2/GetProgrammes?AID=$Affiliate&AgencyID=$Agency&CountryCode=&Key=$API_Key&Sig=$sig&SigData=".urlencode($sig_data);
		date_default_timezone_set("America/Los_Angeles");

		// get program from csv.
		$str_header = "Product Feed Available";
		$cache_filecsv = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"program.csv","cache_merchant");
		if(!$this->oLinkFeed->fileCacheIsCached($cache_filecsv))
		{
			$r = $this->oLinkFeed->GetHttpResult($progm_url,$request);
			$result = $r["content"];
			//var_dump($result);exit;
			if(stripos($result,$str_header) === false) mydie("die: wrong csv file: $cache_filecsv, url: $progm_url");
			$this->oLinkFeed->fileCachePut($cache_filecsv,$result);
		}
	
		//Open CSV File
		$fhandle = file_get_contents($cache_filecsv, 'r');
		$res = json_decode($fhandle,true);
		$res = $res['GetPublisherProgrammesResult'];
		//var_dump($res);exit;
		foreach($res as $k)
		{
			$MerchantName = $k['Merchant Name'];
			$ProductName = $k['Product Name'];
			$desc = $k['Product Description'];
			$IdInAff = intval($k['PID']);
			$CategoryExt = $k['Sector'];
			$TargetCountryExt = trim($k['Country Code']);
			$PayoutType = $k['Payout Type'];
			$ReturnDays = $k['Cookie Duration'];
			$SupportDeepurl = $k['Deep Link Enabled'];
			$StatusInAffRemark = trim($k['Programme Status']);
			$Homepage = $k['Website URL'];
			$AffDefaultUrl = $k['Tracking URL'];
			$CommissionExt = $k['Commission'];
			$LogoUrl = $k['Merchant Logo URL'];
	
			$prgm_name = $MerchantName . "-" . $ProductName;
	
			$CommissionExt .= "PayoutType: ".$PayoutType;
	
			if($StatusInAffRemark == "Not Applied"){
				$Partnership = "NoPartnership";
			}elseif($StatusInAffRemark == "Rejected"){
				$Partnership = "Declined";
			}elseif($StatusInAffRemark == "Live"){
				$Partnership = "Active";
			}elseif($StatusInAffRemark == "Cancelled"){
				$Partnership = "Expired";
			}elseif($StatusInAffRemark == "Waiting"){
				$Partnership = "Pending";
			}else{
				$Partnership = "NoPartnership";
			}
	
			if(stripos($prgm_name, "closed") !== false){
				$StatusInAff = 'Offline';
			}else{
				$StatusInAff = 'Active';
			}
			
			//TermAndCondition
			/* $request = array(
			    "AffId" => $this->info["AffId"],
			    "method" => "get",
			);
			$tcUrl = "https://admin.optimisemedia.com/v2/programmes/affiliate/ViewTermsAndConditions.aspx?productid=$IdInAff&affiliateid=$this->Affiliate";
			$tCcontent = $this->oLinkFeed->GetHttpResult($tcUrl,$request);
			preg_match('@<div id="standardBoiler">([\s\S]*?)</div>@ms', $tCcontent['content'],$matches);
			if (isset($matches[1]))
				$TermAndCondition = $matches[1]; */
			$ContactWebsiteID = $k['Contact WebsiteID'];
			$prgm_url = "https://admin.optimisemedia.com/v2/programmes/affiliate/viewprogramme.aspx?ProductID=$IdInAff&ContactWebsiteID=$ContactWebsiteID";
			
			$arr_prgm[$IdInAff] = array(
					"AffId" => $this->info["AffId"],
					"IdInAff" => $IdInAff,
					"Name" => addslashes($prgm_name),
					"CategoryExt" => addslashes($CategoryExt),
					"TargetCountryExt" => $TargetCountryExt,
					"Homepage" => addslashes($Homepage),
					"Description" => addslashes($desc),
					"CommissionExt" => addslashes($CommissionExt),
					"CookieTime" => addslashes($ReturnDays),
					"StatusInAffRemark" => addslashes($StatusInAffRemark),
					"StatusInAff" => $StatusInAff,						//'Active','TempOffline','Offline'
					"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
					"LastUpdateTime" => date("Y-m-d H:i:s"),
					"DetailPage" => $prgm_url,
					"SupportDeepurl" => $SupportDeepurl,
					"AffDefaultUrl" => $AffDefaultUrl,
					//"AllowNonaffPromo" => $AllowNonaffPromo,
					//"AllowNonaffCoupon"=> $AllowNonaffCoupon
			        //"TermAndCondition" => addslashes($TermAndCondition),
					"LogoUrl" => addslashes($LogoUrl),
			);
			
			//get Homepag by page(BR)
			if (SID == 'bdg02')
			{
				unset($arr_prgm[$IdInAff]['Homepage']);
				if(!isset($cache[$IdInAff]['Homepage']))
				{
					$prgm_r = $this->oLinkFeed->GetHttpResult($prgm_url,$request);
					if ($prgm_r['code'] == 200)
					{
						$prgm_r = $prgm_r['content'];
						$Homepage = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_r, 'ContentPlaceHolder1_lbPreview" href="', '"'));
						$arr_prgm[$IdInAff]['Homepage'] = addslashes($Homepage);
						$cache[$IdInAff]['Homepage'] = "1";
					}
				}
			}
			
			$program_num++;
			if(count($arr_prgm) >= 100){
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
				$arr_prgm = array();
			}
		}//while
		unset($fhandle);
			
	
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
			unset($arr_prgm);
		}
		$cache = json_encode($cache);
		$this->oLinkFeed->fileCachePut($cache_file, $cache);
	
		echo "\tGet Program by api end\r\n";
	
		if($program_num < 10){
			mydie("die: program count < 10, please check program.\n");
		}
	
		echo "\tUpdate ({$program_num}) program.\r\n";
		echo "\tSet program country int.\r\n";
		$objProgram->setCountryInt($this->info["AffId"]);
	}
}
?>
