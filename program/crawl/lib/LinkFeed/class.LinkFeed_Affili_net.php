<?php
require_once 'text_parse_helper.php';
require_once 'pdf_parse.php';

class LinkFeed_Affili_net
{
	
	protected $ctgr_postdata_checked,
		$ctgr_postdata_event,
		$ctgr_postdata_view,
		$ctgr_postdata_view_origin,
		$ctgr_postdata_common1,
		$ctgr_postdata_common2,
		$ctgr_postdata_common3,
		$ctgr_postdata_common4;
	
	function LoginIntoAffService()
	{
		//get para __VIEWSTATE and then process default login
		if(!isset($this->info["AffLoginPostStringOrig"])) {
			$this->info["AffLoginPostStringOrig"] = $this->info["AffLoginPostString"];
		}
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "",);
		$url = $this->info["AffLoginUrl"];
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r["content"];
		$param = array(
				'__EVENTTARGET' => 'ctl00$body$btnLogin',
				'__EVENTARGUMENT' => '',
				'__VIEWSTATE' => '',
				'__VIEWSTATEGENERATOR' => '',
				'__EVENTVALIDATION' => '',
				);
		$keywords = array('__VIEWSTATE', '__VIEWSTATEGENERATOR', '__EVENTVALIDATION');
		foreach ($keywords as $keyword){
			if (preg_match(sprintf('@id="%s" value="(.*?)"@', $keyword), $content, $g)){
				$param[$keyword] = $g[1];
			}else{
				mydie("login failed: $keyword");
			}
		}
		$this->info["AffLoginPostString"] = http_build_query($param) . "&" . $this->info["AffLoginPostStringOrig"];
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info, 2, true, true, false);
		return "stophere";
	}
	
	private function getCredentialToken()
	{
		if (empty($this->credentialToken))
		{
			$client = new SoapClient('https://api.affili.net/V2.0/Logon.svc?wsdl', array('trace'=>true));
			$this->credentialToken = $client->Logon(array('Username' => $this->API_USERNAME, 'Password' => $this->API_PASSWORD, 'WebServiceType' => 'Publisher'));
		}
		return $this->credentialToken;
	}

	function getCouponFeed()
	{
	    $check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0,"UpdatedCount" => 0,"Detail" => array());
		$SearchVoucherCodesRequestMessage = array(
						'ProgramId' => null,
						'VoucherCode' => null,
						'Query' => null,
						'VoucherCodeContent' => 'Any',
						'StartDate' => date('Y-m-d', time() + 86400),
						'EndDate' => date('Y-m-d', time() + 31536000),
						'VoucherType' => null,
						'CustomerRestriction' => 'NoRestrictions',
					);
		$page = 1;
		$limit = 100;
		$HasNextPage = true;
		$errorPage = 0;
		
		//var_dump($data);exit;
		while($HasNextPage)
		{
			$links = array();
			$data = $this->soapGetVoucherCodes($SearchVoucherCodesRequestMessage, $page, $limit);
			if (!empty($data) && !empty($data->VoucherCodeCollection) && !empty($data->VoucherCodeCollection->VoucherCodeItem) && is_array($data->VoucherCodeCollection->VoucherCodeItem))
			{
				if($data->TotalResults <= $page * $limit)
					$HasNextPage = false;
				foreach ($data->VoucherCodeCollection->VoucherCodeItem as $v)
				{
					$link = array(
							"AffId" => $this->info["AffId"],
							"AffMerchantId" => $v->ProgramId,
							"LinkDesc" => $v->Description,
							"LinkStartDate" => '0000-00-00 00:00:00',
							"LinkEndDate" => '0000-00-00 00:00:00',
							"LinkPromoType" => 'DEAL',
							"LinkOriginalUrl" => "",
							"LinkHtmlCode" => $v->IntegrationCode,
							"AffLinkId" => $v->Id,
							"LinkName" => $v->Title,
							"LinkCode" => $v->Code,
							"LinkImageUrl" => "",
							"LinkAffUrl" => "",
							"DataSource" => $this->DataSource,
					        "Type"       => 'promotion'
					);
					$desc = array();
					if(isset($v->VoucherTypes))
					{
						foreach ($v->VoucherTypes as $p){
							if($p == 'AllProducts' && stripos($v->Description, 'all products') == false)
								$desc[] = 'Discount on all products';
							if($p == 'MultiBuyDiscount' && stripos($v->Description, 'Multi Buy') == false)
								$desc[] = 'Multi-buy discount';
							if($p == 'SpecificProducts' && stripos($v->Description, 'specific') == false)
								$desc[] = 'Discount on specific products';
							if($p == 'FreeShipping' && stripos($v->Description, 'Free shipping') == false)
								$desc[] = 'Free shipping';
							if($p == 'FreeProduct' && stripos($v->Description, 'Free Product') == false)
								$desc[] = 'Free product';
							if($p == 'Competition' && stripos($v->Description, 'Competition') == false)
								$desc[] = 'Competition';
						}
					}
					if(isset($v->CustomerRestriction))
					{
						if($v->CustomerRestriction == 'AllCustomers' && stripos($v->Description, 'all customers'))
							$desc[] = 'For all customers';
						if($v->CustomerRestriction == 'OnlyNewCustomers' && stripos($v->Description, 'new customers'))
							$desc[] = 'For new customers only';
					}
					if(!empty($desc))
					{
						$link['LinkDesc'] .= "  \n\r" . 'Properties: ' . implode(";\n\r",$desc) . '.';
					}
					if (!empty($v->StartDate))
					{
						$date = strtotime($v->StartDate);
						if ($date > 946713600)
							$link['LinkStartDate'] = date('Y-m-d 00:00:00', $date);
					}
					if (!empty($v->EndDate))
					{
						$date = strtotime($v->EndDate);
						if ($date > 946713600)
							$link['LinkEndDate'] = date('Y-m-d 23:59:59', $date);
					}
					if (preg_match('@<a href="(.*?)"@', $link['LinkHtmlCode'], $g))
						$link['LinkAffUrl'] = $g[1];
					if (!empty($link['LinkCode']))
						$link['LinkPromoType'] = 'COUPON';
					else
						$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
					if (empty($link['AffLinkId']) || empty($link['LinkName']) || empty($link['LinkAffUrl']))
						continue;
					$links[] = $link;
				}
			}else{
				$errorPage++;
				if($errorPage > 5)
					mydie(" No data in page $page from API");
				continue;
			}
			echo sprintf("page:%s,get coupon by api...%s link(s) found.\n", $page, count($links));
			if (count($links) > 0)
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
			$page ++;
		}
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'promotion');
		return $arr_return;
	}

	function GetAllLinksByAffId()
	{
		$check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, );
		$arr_IdInAff = array();
		$arr_merchant = $this->oLinkFeed->getApprovalAffMerchant($this->info["AffId"]);
		foreach ($arr_merchant as $merchant)
		{
			$arr_IdInAff[] = $merchant['IdInAff'];
		}
		//print_r($arr_IdInAff);
		$IdInAffLimit = array_chunk($arr_IdInAff,10);
		
		foreach ($IdInAffLimit as $vl){
		    
		    
		    list($page, $limit, $total, $links) = array(0, 100, 0, array());
		    do
		    {
		        $page ++;
		        $links = array();
		        $r = $this->soapSearchCreatives($vl, $page, $limit, 6);
		        if (empty($r) || empty($r->CreativeCollection) || empty($r->CreativeCollection->Creative))
		            break;
		        if($total == 0){
		            $total = (int)$r->TotalResults;
		            echo "Total:($total)\r\n";
		        }
		        	
		        $data = $r->CreativeCollection->Creative;
		        if (!is_array($data) && !empty($data))
		            $data = array($data);
		        foreach ((array)$data as $v)
		        {
		            $link = array(
		                "AffId" => $this->info["AffId"],
		                "AffMerchantId" => $v->ProgramId,
		                "AffLinkId" => sprintf('%s_%s_%s', $v->ProgramId, $v->CreativeTypeEnum, $v->CreativeNumber),
		                "LinkName" => html_entity_decode(trim($v->Title)),
		                "LinkDesc" => '',
		                "LinkStartDate" => '0000-00-00',
		                "LinkEndDate" => '0000-00-00',
		                "LinkPromoType" => 'N/A',
		                "LinkHtmlCode" => $v->IntegrationCode,
		                "LinkOriginalUrl" => '',
		                "LinkImageUrl" => '',
		                "LinkAffUrl" => '',
		                "DataSource" => $this->DataSource,
		                "Type"       => 'link'
		            );
		            if (!empty($v->BannerStub))
		            {
		                $link['LinkImageUrl'] = $v->BannerStub->BannerURL;
		                $link['LinkDesc'] = $v->BannerStub->AltTag;
		            }
		            if (!empty($v->TextStub))
		                $link['LinkDesc'] = $v->TextStub->Header;
		            $code = get_linkcode_by_text_de($link['LinkName'] . '|' . $link['LinkDesc']);
		            if(empty($code))
		                $code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc'] . '|' . $link['LinkHtmlCode']);
		            if (!empty($code))
		            {
		                $link['LinkPromoType'] = 'COUPON';
		                $link['LinkCode'] = $code;
		            }
		            else
		                $link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
		            if (preg_match('@a href="(.*?)"@i', $link['LinkHtmlCode'], $g))
		                $link['LinkAffUrl'] = $g[1];
		            if (empty($link['AffLinkId']) || empty($link['LinkHtmlCode']))
		                continue;
		            elseif(empty($link['LinkName'])){
		                $link['LinkPromoType'] = 'link';
		            }
		            $this->oLinkFeed->fixEnocding($this->info, $link, "feed");
		            $links[] = $link;
		            $arr_return["AffectedCount"] ++;
		        }
		        echo sprintf("page:%s, %s links(s) found. \n", $page, count($links));
		        if(count($links) > 0)
		            $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		        	
		        if($page * $limit > 99999){
		            mydie("Page is overloaded:($page)");
		            break;
		        }
		        //sleep(1);
		    }while($page * $limit < $total);
		    
		}
		
		
		echo "Get:({$arr_return["AffectedCount"]})\r\n"; 
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link');
		return $arr_return;
		
	}
	
	function GetAllLinksFromAffByMerID($merinfo)
	{
	    $check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, );
		list($page, $limit, $total, $links) = array(0, 100, 0, array());
		do
		{
			$page ++;
			$links = array();
			$r = $this->soapSearchCreatives($merinfo['IdInAff'], $page, $limit, 6);
			if (empty($r) || empty($r->CreativeCollection) || empty($r->CreativeCollection->Creative))
				break;
			$total = (int)$r->TotalResults;
			$data = $r->CreativeCollection->Creative;
			if (!is_array($data) && !empty($data))
				$data = array($data);
			foreach ((array)$data as $v)
			{
				$link = array(
					"AffId" => $this->info["AffId"],
					"AffMerchantId" => $merinfo['IdInAff'],
					"AffLinkId" => sprintf('%s_%s_%s', $merinfo['IdInAff'], $v->CreativeTypeEnum, $v->CreativeNumber),
					"LinkName" => html_entity_decode(trim($v->Title)),
					"LinkDesc" => '',
					"LinkStartDate" => '0000-00-00',
					"LinkEndDate" => '0000-00-00',
					"LinkPromoType" => 'N/A',
					"LinkHtmlCode" => $v->IntegrationCode,
					"LinkOriginalUrl" => '',
					"LinkImageUrl" => '',
					"LinkAffUrl" => '',
					"DataSource" => $this->DataSource,
				    "Type"       => 'link'
				);
				if (!empty($v->BannerStub))
				{
					$link['LinkImageUrl'] = $v->BannerStub->BannerURL;
					$link['LinkDesc'] = $v->BannerStub->AltTag;
				}
				if (!empty($v->TextStub))
					$link['LinkDesc'] = $v->TextStub->Header;
				$code = get_linkcode_by_text_de($link['LinkName'] . '|' . $link['LinkDesc']);
                                if(empty($code))
                                    $code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc'] . '|' . $link['LinkHtmlCode']);
				if (!empty($code))
				{
					$link['LinkPromoType'] = 'COUPON';
					$link['LinkCode'] = $code;
				}
				else
					$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
				if (preg_match('@a href="(.*?)"@i', $link['LinkHtmlCode'], $g))
					$link['LinkAffUrl'] = $g[1];
				if (empty($link['AffLinkId']) || empty($link['LinkHtmlCode']))
					continue;
                elseif(empty($link['LinkName'])){
                    $link['LinkPromoType'] = 'link';
                }
				$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
				$links[] = $link;
				$arr_return["AffectedCount"] ++;
			}
			echo sprintf("program:%s, page:%s, %s links(s) found. \n", $merinfo['IdInAff'], $page, count($links));
			if(count($links) > 0)
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
			sleep(1);
		}while($page * $limit < $total && $page * $limit < 9999);
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
		
		//print_r($arr_merchant);exit;
		$mcount = 0;
		foreach ($arr_merchant as $merchatInfo)
		{
		    echo $merchatInfo['IdInAff'].PHP_EOL;
		    $crawlMerchantsActiveNum = 0;
		    $setMaxNum  = isset($productNumConfig[$merchatInfo['IdInAff']]) ? $productNumConfig[$merchatInfo['IdInAff']]['limit'] :  100;
		    $isAssignMerchant = isset($productNumConfig[$merchatInfo['IdInAff']]) ? TRUE : FALSE;
		    
			$strurl = "https://product-api.affili.net/V3/productservice.svc/XML/GetShopList?PublisherId=".$this->API_USERNAME."&Password=".$this->PRODUCT_PASSWORD."&PageSize=5&Query=".$merchatInfo['IdInAff'];
			//echo $strurl;exit;
			$r = $this->oLinkFeed->GetHttpResult($strurl, $request);
			$r = simplexml_load_string($r['content']);
			$r = json_decode(json_encode($r), true);
			//var_dump($r);exit;
			if (!$r['GetShopListSummary']['TotalRecords'])
				continue;
			
			if (isset($r['Shops']['Shop'][0]))
				$value = $r['Shops']['Shop'][0];
			else 
				$value = $r['Shops']['Shop'];
			
			$currentPage = 1;
			$url = "https://product-api.affili.net/V3/productservice.svc/JSON/SearchProducts";
			$CategoryIds_arr = range(1, 100);
			$CategoryIds = implode(',', $CategoryIds_arr);
			$params['PublisherId'] = $this->API_USERNAME; // your publisher ID
			$params['Password'] = $this->PRODUCT_PASSWORD; // your product web services password
			$params['ShopIds'] = $value['ShopId'];
			$params['WithImageOnly'] = 'true';
			$params['ImageScales'] = 'Image180';
			$params['PageSize'] = 50;
			//$params['CurrentPage'] = $currentPage;
			$params['Query'] = '';
			$params['CategoryIds'] = $CategoryIds;
			$params['UseAffilinetCategories'] = 'true';
			$params['ExcludeSubCategories'] = 'false';
			do{
			    echo 'currentPage:'.$currentPage.PHP_EOL;
			    $params['CurrentPage'] = $currentPage;
			    	
			    $Apiurl = $url . '?' . http_build_query($params);
			    //echo $url.PHP_EOL;
			    $re = $this->oLinkFeed->GetHttpResult($Apiurl, $request);
			    $response = $re['content'];
			    if(substr($response,0,3) == pack("CCC", 0xEF,0xBB,0xBF)) {
			        $response = substr($response,3);
			    }
			    $re = json_decode($response, true);
			    
			    if (empty($re['ProductsSummary']['TotalRecords']) || $re['ProductsSummary']['TotalRecords'] <= 0)
			        break;
			    $totalPage = $re['ProductsSummary']['TotalPages'];
			    $TotalCount = $re['ProductsSummary']['TotalRecords'];
			    foreach ($re['Products'] as $v)
			    {
			        
			        $ProductId = trim($v['ProductId']);
			        $ProductImage = trim($v['Images'][0][0]['URL']);
			        	
			        $product_path_file = $this->oLinkFeed->getProductImgFilePath($this->info["AffId"],"{$merchatInfo['IdInAff']}_".urlencode($ProductId).".png", PRODUCTDIR);
			        if(!$this->oLinkFeed->fileCacheIsCached($product_path_file))
			        {
			            $file_content = $this->oLinkFeed->downloadImg($ProductImage);
			            if(!$file_content) //下载不了跳过。
			                continue;
			            $this->oLinkFeed->fileCachePut($product_path_file, $file_content);
			        }
			        if(!isset($v['ProductName']) || empty($v['ProductName']) || !isset($ProductId))
			        {
			            continue;
			        }
			    
			        if (!empty($v['PriceInformation']['DisplayBasePrice'])){
			            $ProductOriginalPrice = $v['PriceInformation']['DisplayBasePrice'];
			        }
			        else
			            $ProductOriginalPrice = '';
			        if (!empty($v['Description']))
			            $ProductDesc = $v['Description'];
			        else
			            $ProductDesc = '';
			    
			        $link = array(
			            "AffId" => $this->info["AffId"],
			            "AffMerchantId" => $merchatInfo['IdInAff'],
			            "AffProductId" => $ProductId,
			            "ProductName" => addslashes($v['ProductName']),
			            "ProductCurrency" => trim($v['PriceInformation']['Currency']),
			            "ProductPrice" => trim($v['PriceInformation']['DisplayPrice']),
			            "ProductOriginalPrice" =>trim($ProductOriginalPrice),
			            "ProductRetailPrice" =>'',
			            "ProductImage" => addslashes($ProductImage),
			            "ProductLocalImage" => addslashes($product_path_file),
			            "ProductUrl" => addslashes($v['Deeplink1']),
			            "ProductDestUrl" => '',
			            "ProductDesc" => addslashes($ProductDesc),
			            "ProductStartDate" => date('Y-m-d H:i:s', strtotime($v['LastProductChange'])),
			            "ProductEndDate" => '',
			        );
			        $links[] = $link;
			        $arr_return['AffectedCount'] ++;
			        $crawlMerchantsActiveNum ++;
			    }
			    if (count($links))
			    {
			        $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateProductToDB($links);
			        $links = array();
			        //echo sprintf("get product complete. %s links(s) found. \n", $arr_return["UpdatedCount"]);
			    }
			    //大于最大数跳出
			    if($crawlMerchantsActiveNum >= $setMaxNum){
			        break;
			    }
			    
			    
			    $currentPage ++;
			}while($currentPage <= $totalPage);
			
			
			if($isAssignMerchant){
			    $productNumConfigAlert .= "AFFID:".$this->info["AffId"].",Program({$merchatInfo['MerchantName']}),Crawl Count($crawlMerchantsActiveNum),Total Count({$TotalCount}) \r\n";
			}
			$mcount ++;
		}
		echo 'merchant count:'.$mcount.PHP_EOL;
		$this->oLinkFeed->checkProductExists($this->info["AffId"], $check_date);
		echo $productNumConfigAlert.PHP_EOL;	
		echo 'END time'.date('Y-m-d H:i:s').PHP_EOL;
		return $arr_return;
	
	}
	
	private function getSoapToken()
	{
		if ($this->soapToken)
			return $this->soapToken;
		$this->oLinkFeed->clearHttpInfos($this->info["AffId"]);
		$logon = new SoapClient("https://api.affili.net/V2.0/Logon.svc?wsdl", array('trace'=> true));
		$token = $logon->Logon(array(
				'Username'  => $this->API_USERNAME,
				'Password'  => $this->API_PASSWORD,
				'WebServiceType' => 'Publisher'
		));
		$this->soapToken = $token;
		echo sprintf("Logon token %s created at %s.\n", $token, date('Y-m-d H:i:s', time()));
		return $this->soapToken;
	}
	
	private function soapGetVoucherCodes($SearchVoucherCodesRequestMessage, $page, $limit, $retry = 2){
		$token = $this->getSoapToken();
		$client = $this->soapInboxClient;
		$displaySettings = array(
				'CurrentPage' => $page,
				'PageSize' => $limit,
				'SortBy' => 'LastChangeDate',
				'SortOrder' => 'Descending'
		);
		if (!$client)
		{
			$client = new SoapClient('https://api.affili.net/V2.0/PublisherInbox.svc?wsdl', array('trace'=> true));
			$this->soapInboxClient = $client;
		}
		try
		{
			$r = $client->SearchVoucherCodes(array(
				'CredentialToken' => $token,
				'DisplaySettings' => $displaySettings,
				'SearchVoucherCodesRequestMessage' => $SearchVoucherCodesRequestMessage
			));
		}
		catch (Exception $e)
		{
			if (preg_match('@Illegal characters@', $e->getMessage()))
			{
				// this exception may caused by the server catch it and return null
				echo sprintf("%s Exception return null\n", $e->getMessage());
				return null;
			}
			// try to relogon.
			$this->soapToken = null;
			$retry --;
			if ($retry < 0)
				throw $e;
			echo sprintf("%s Exception sleep 120...\n", $e->getMessage());
			sleep(120);
			return $this->soapGetVoucherCodes($SearchVoucherCodesRequestMessage, $page, $limit, $retry);
		}
		return $r;
	}

	private function soapSearchCreatives($arr_IdInAff, $page, $limit, $retry = 2)
	{
		$token = $this->getSoapToken();
		$client = $this->soapClient;
		if (!$client)
		{
			$client = new SoapClient('https://api.affili.net/V2.0/PublisherCreative.svc?wsdl', array('trace'=> true));
			$this->soapClient = $client;
		}
		try 
		{
            $this->showCrawlPageTime('request the \'SearchCreatives\' by SoapClient!');
			$r = $client->SearchCreatives(array(
				'CredentialToken' => $token,
				'DisplaySettings' => array('CurrentPage' => $page, 'PageSize' => $limit),
				'SearchCreativesQuery' => array(
						'CreativeTypes' => array('Text', 'Banner'),
						'ProgramIds' => $arr_IdInAff
						)
			));
		}
		catch (Exception $e)
		{
			if (preg_match('@Illegal characters@', $e->getMessage()))
			{
				// this exception may caused by the server catch it and return null
				echo sprintf("%s Exception return null\n", $e->getMessage());
				return null;
			}
			// try to relogon.
			$this->soapToken = null;
			$retry --;
			if ($retry < 0)
				throw $e;
			echo sprintf("%s Exception sleep 120...\n", $e->getMessage());
			sleep(120);
			return $this->soapSearchCreatives($arr_IdInAff, $page, $limit, $retry);
		}
		return $r;
	}

	function GetProgramFromAff()
	{
        ini_set("max_execution_time", "21600");
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
		$this->GetProgramByApi();
		$this->GetProgramMobileFriendly();
		$this->checkProgramOffline($this->info["AffId"], $check_date);

		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}
	
	function GetProgramMobileFriendly()
	{
		/*$IdInAff = 2906;
		$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"t&c_".date("Ym")."_{$IdInAff}.pdf", "program", true);	
		$parser = new \Smalot\PdfParser\Parser();
							$pdf = $parser->parseFile($cache_file);							
							$text = $pdf->getText();
							
						echo	$text = str_replace("\r\n", ' ', $text);
						echo stripos($text, "freigegeben wurden oder");
						
						echo "\r\n";
						
	if(stripos($text, "Publisher dürfen nur Gutscheine bewerben, die explizit von as-garten.de für das Affiliate-Partnerprogramm" !== false)){
								echo '222';								
							}else{
								echo 'no';
							}
							exit;*/
		
		echo "\tGetProgramMobileFriendly.\n";
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		$request = array("AffId" => $this->info["AffId"], "method" => "get",);
		$url = sprintf("http://publisher.affili.net/Programs/ProgramListExport.aspx?wspw=ONBGkuGeq0u0yKBFwGzg");

		$this->showCrawlPageTime($url);

		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r['content'];
		$content = str_replace("\r", "\n", $content);
		$rows = fgetcsv_str($content, 0, ';', '"');
		$programs = array();
		$objProgram = new ProgramDb();
		foreach ($rows as $row){
			if (empty($row) || empty($row['Program ID']) || !is_numeric($row['Program ID']))
				continue;
			$IdInAff = $row['Program ID'];
			$programs[$IdInAff] = array("AffId" => $this->info["AffId"], 'IdInAff' => $IdInAff, 'MobileFriendly' => 'UNKNOWN', 'Name' => addslashes(trim($row['Title'])));
			$IdInAff = $row['Program ID'];
			if (!empty($row['Mobile'])){
				$programs[$IdInAff]['MobileFriendly'] = 'YES';
			}
			
			
			if(count($programs) >= 100){
				$objProgram->updateProgram($this->info["AffId"], $programs);
				$programs = array();
			}
		}
		if(count($programs)){
			$objProgram->updateProgram($this->info["AffId"], $programs);
		}
	}

	function GetProgramByApi()
	{
		echo "\tGet Program by api start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		$request = array("AffId" => $this->info["AffId"], "method" => "get",);
		$prgm_homepage = array();

		$arr_prgm_ctgr = array();
		
		/* <LIVE DATA> */
		define ("WSDL_LOGON", "https://api.affili.net/V2.0/Logon.svc?wsdl");
		define ("WSDL_PROG",  "https://api.affili.net/V2.0/PublisherProgram.svc?wsdl");

		$Username = $this->API_USERNAME; // the publisher ID
		$Password = $this->API_PASSWORD; // the publisher web services password
		$SOAP_LOGON = new SoapClient(WSDL_LOGON, array('trace'=> true));
		$Token = $SOAP_LOGON->Logon(array(
				'Username'  => $Username,
				'Password'  => $Password,
				'WebServiceType' => 'Publisher'
				));

		$params = array('Query' => '');
		try {
			$SOAP_REQUEST = new SoapClient(WSDL_PROG, array('trace'=> true));

//			$this->showCrawlPageTime('request the \'GetAllPrograms\' by SoapClient!');

			$req = $SOAP_REQUEST->GetAllPrograms(array(
					'CredentialToken' => $Token,
					'GetProgramsRequestMessage' => $params
					));
			$total = $req->TotalRecords;
			foreach($req->Programs->ProgramSummary as $prgm){
				$IdInAff = $prgm->ProgramId;
				if(!$IdInAff) continue;
				
				$Partnership = "NoPartnership";
				$StatusInAffRemark = $prgm->PartnershipStatus;
				if($StatusInAffRemark == 'Active'){
					$Partnership = 'Active';
				}elseif($StatusInAffRemark == 'Declined'){
					$Partnership = 'Declined';
				}elseif($StatusInAffRemark == 'Waiting'){
					$Partnership = 'Pending';
				}elseif($StatusInAffRemark == 'Paused'){
					$Partnership = 'Expired';
				}elseif($StatusInAffRemark == 'NotApplied'){
					$Partnership = 'NoPartnership';
				}
				$StatusInAff = 'Active';
				
				if ($this->info["AffId"] == 26 && $IdInAff == '12489')
				{
					// the api return Expired
					// but the partnership is active in the page
					// change to get it in the page
					// temporarily just set to active
					$Partnership = 'Active';
					$StatusInAff = 'TempOffline';
				}

				$CommissionExt = '
				PayPerSale: '.$prgm->CommissionRates->PayPerSale->MinRate.' - '.$prgm->CommissionRates->PayPerSale->MaxRate.',
				PayPerLead: '.$prgm->CommissionRates->PayPerLead->MinRate.' - '.$prgm->CommissionRates->PayPerLead->MaxRate.',
				PayPerClick: '.$prgm->CommissionRates->PayPerClick->MinRate.' - '.$prgm->CommissionRates->PayPerClick->MaxRate.'
				';
				
				//$homepage = "";
				//$homepage =  $this->oLinkFeed->findFinalUrl($prgm->Url, array("nobody" => "unset"));
				
				$prgm_cagr[$IdInAff] = array(
					"AffId" => $this->info["AffId"],
					"IdInAff" => $IdInAff,
					"CategoryExt" => ''
				);

				$arr_prgm[$IdInAff] = array(
					"AffId" => $this->info["AffId"],
					"IdInAff" => $IdInAff,
					"Name" => addslashes($prgm->ProgramTitle),
					//"Homepage" => strlen($homepage) ? $homepage : $prgm->Url,
					"Description" => addslashes($prgm->Description),
					"TermAndCondition" => addslashes($prgm->Limitations),
					"StatusInAffRemark" => addslashes($StatusInAffRemark),
					"StatusInAff" => $StatusInAff,						//'Active','TempOffline','Offline'
					"Partnership" => $Partnership,					//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
					"DetailPage" => "http://publisher.affili.net/Programs/programInfo.aspx?pid=$IdInAff",
					"LastUpdateTime" => date("Y-m-d H:i:s"),
					"CommissionExt" => addslashes($CommissionExt),
				);
				
				$prgm_homepage[$IdInAff] = array(
													"IdInAff" => $IdInAff,
													"Homepage" => $prgm->Url
												);
												
						
				/*
				 * t&c
				 */
//				$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"t&c_".date("Ym")."_{$IdInAff}.pdf", "program", true);
//				if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
//				{
//					if($this->info["AffId"] == 26){
//						$tmp_url = "http://publisher.affili.net/Programs/TermsAndConditions.aspx?showCloseButton=false&pid=$IdInAff&pubid=".$this->API_USERNAME;
//					}else{
//						$tmp_url = "http://publisher.affili.net/Programs/ProgramInfo.aspx?pid=$IdInAff";
//					}
//                    $this->showCrawlPageTime($tmp_url);
//
//					$tmp_arr = $this->oLinkFeed->GetHttpResult($tmp_url, $request);
//					if($tmp_arr['code'] == 200){
//						$results = $tmp_arr['content'];
//						if($this->info["AffId"] == 26){
/*							preg_match("/<a.*?id=\"content_lnkLinkToAdditionalContracts\".*?href=\"(.*?)\".*?>T&C<\/a>/s", $results, $m);*/
//						}else{
/*							preg_match("/<a[^>]?href=\"(.*?)\"[^>]?>AGB[^<]?<\/a>/", $results, $m);*/
//						}
//
//						if(isset($m[1]) && count($m[1])){
//							$pdf_url = trim(htmlspecialchars_decode($m[1]));
//							$tmp_arr = $this->oLinkFeed->GetHttpResult($pdf_url, $request);
//							if($tmp_arr['code'] == 200){
//								$results = $tmp_arr['content'];
//								$this->oLinkFeed->fileCachePut($cache_file, $results);
//
//								$parser = new \Smalot\PdfParser\Parser();
//								$pdf = $parser->parseFile($cache_file);
//								$text = $pdf->getText();
//
//								if(stripos($text, 'If so, can they utilise any code') !== false && stripos($text, 'Affiliates may only use codes with the express permission') !== false){
//									$programs[$IdInAff]['AllowNonaffCoupon'] = 'NO';
//								}elseif(stripos($text, "Publisher dürfen nur Gutscheine bewerben, die explizit von as-garten.de für das Affiliate-Partnerprogramm" !== false)){
//									$programs[$IdInAff]['AllowNonaffCoupon'] = 'NO';
//									$programs[$IdInAff]['AllowNonaffPromo'] = 'NO';
//								}elseif(stripos($prgm->Limitations, "Es dürfen nur im Netzwerk bereitgestellte Gutscheincodes genutzt werden." !== false)){
//									$programs[$IdInAff]['AllowNonaffCoupon'] = 'NO';
//								}else{
//									$programs[$IdInAff]['AllowNonaffCoupon'] = 'UNKNOWN';
//									$programs[$IdInAff]['AllowNonaffPromo'] = 'UNKNOWN';
//								}
//							}
//						}
//					}
//				}
				//print_r($arr_prgm);exit;

				$program_num++;
				if(count($arr_prgm) >= 100){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
			}
		} catch( Exception $e ) {
			mydie("die: Api error.\n");
		}

		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
			unset($arr_prgm);
		}

		echo "\tGet Program by api end\r\n";
		if($program_num < 10){
			mydie("die: program count < 10, please check program.\n");
		}
		echo "\tUpdate ({$program_num}) program.\r\n";
		echo "\tSet program country int.\r\n";
		$objProgram->setCountryInt($this->info["AffId"]);
		
//		echo "start check program url";
//		$arr_prgm = array();
//		foreach($prgm_homepage as $v){
//			if($v["Homepage"]){
//				$homepage =  $this->oLinkFeed->findFinalUrl($v["Homepage"], array("nobody" => "unset"));
//				if($homepage){
//					$arr_prgm[$v["IdInAff"]] = array(
//													"AffId" => $this->info["AffId"],
//													"IdInAff" => $v["IdInAff"],
//													"Homepage" => addslashes($homepage)
//												);
//					if(count($arr_prgm) >= 100){
//						$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
//						$arr_prgm = array();
//					}
//				}
//			}
//		}
//		if(count($arr_prgm)){
//			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
//			unset($arr_prgm);
//		}
		
		echo "\tSet program category int.\r\n";
		$this->getCategoryBypage($prgm_cagr,$objProgram);
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
	
	function getCategoryBypage($prgm_cagr,$objProgram)
	{
		echo "\tGet Category by page start\r\n";
		
		$ctgr_arr = $this->getCategoryList();

		print_r($ctgr_arr);

		$father_cagr = '##############';
		$outside_prgm = array();
		$father_cagr_name = '';
		
		foreach ($ctgr_arr as $key => &$val)
		{
//			print_r($val);
			if (strpos($val[0],$father_cagr) === false)
			{
				$father_cagr = $val[0];
				$father_cagr_name = $val[1] . ' > ';
				$ctgr_name = $val[1];
				$prgm_id_list = $this->getCtgrPrgmList($val);
				unset($val);
			} else
			{
				$ctgr_name = $father_cagr_name . $val[1];
				$prgm_id_list = $this->getCtgrPrgmList($val);
				unset($val);
			}
//			print_r($prgm_id_list);
			
			if (empty($prgm_id_list)) continue;
			
			foreach ($prgm_id_list as $v)
			{
				if (key_exists($v,$prgm_cagr))
				{
					if (empty($prgm_cagr[$v]['CategoryExt']) || strpos($ctgr_name,$prgm_cagr[$v]['CategoryExt']) !== false)
						$prgm_cagr[$v]['CategoryExt'] = $ctgr_name;
					else
						$prgm_cagr[$v]['CategoryExt'] .= ",$ctgr_name";
				}else
				{
					$outside_prgm[] = $v;
				}
			}
		}

        $noCtgrIdList = $this->rememberNoCategoryProgram($this->info['AffId'], $prgm_cagr);

		echo "\nThe programsId list of no category :" . join(',', $noCtgrIdList) . "\n";

        if (count($noCtgrIdList) > 100)
            mydie("\nToo many programs have no category! Id list :" . join(',', $noCtgrIdList) . "\n");
		
//		print_r($prgm_cagr);exit;
		
		$objProgram->updateProgram($this->info["AffId"], $prgm_cagr);
		//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
		unset($prgm_cagr);
	}
	
	function getCtgrPrgmList($ctgr_arr)
	{
		$prgm = array();
		$url = 'http://publisher.affili.net/Programs/ProgramSearch.aspx?nr=1&pnp=3';
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "post",
			"postdata" => $this->ctgr_postdata_common1
				. $this->ctgr_postdata_event . 'ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24btnSearch'
				. $this->ctgr_postdata_common2
				. $this->ctgr_postdata_view . $this->ctgr_postdata_view_origin
				. $this->ctgr_postdata_common3
				. $this->ctgr_postdata_checked . $ctgr_arr['checked_value']
				. $this->ctgr_postdata_common4
		);
		$this->showCrawlPageTime($url);
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$result = $r['content'];
		
		$prgm_str = trim($this->oLinkFeed->ParseStringBy2Tag($result, 'Hide/Show column:', '</table>'));
		
		if (empty($prgm_str)) return $prgm;
//		echo $prgm_str;exit;
		preg_match_all('/ProgramInfo\.aspx\?pid=(\d+)["#]/',$prgm_str,$m);
		
		if (!isset($m[1]) || empty($m[1])) mydie("The result of program ID is empty when search category, please check the Regular expression");
		
		foreach (array_unique($m[1]) as $val)
			$prgm[] = $val;
		
		if (strpos($result,'ctl00$ctl00$ContentPlaceHolderContent$Frame1Content$ucPaging$ibForward') !== false)
		{
			$max_page = intval($this->oLinkFeed->ParseStringBy2Tag($result, array('ContentPlaceHolderContent_Frame1Content_ucPaging_lMaxPage','"maxPage">'), '</span>'));
			$pre_num = intval($this->oLinkFeed->ParseStringBy2Tag($result, array('ContentPlaceHolderContent_Frame1Content_ucPaging_ddlItemsPerPage','selected="selected" value="'), '"'));
			$viewstate = trim($this->oLinkFeed->ParseStringBy2Tag($result, '|__VIEWSTATE|', '|'));
			
			if (!$max_page || !is_numeric($max_page))
				mydie("Can't find the max page !");
			
			preg_match_all('/id="ContentPlaceHolderContent_Frame1Content_ucPaging_LinkButton(\d+)"/',$result,$page_link);
			if (!isset($page_link[1]) || empty($page_link[1])) mydie("Can't find the next page link !");
			
			for ($i = 1; $i <= count($page_link[1]); $i ++)
			{
				$current_page_num = intval($this->oLinkFeed->ParseStringBy2Tag($result, 'id="ContentPlaceHolderContent_Frame1Content_ucPaging_HiddenCurrentPage" value="', '"'));
				
				$request['postdata'] = $this->ctgr_postdata_common1
					. $this->ctgr_postdata_event . 'ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucPaging%24ibForward'
					. $this->ctgr_postdata_common2
					. $this->ctgr_postdata_view . urlencode($viewstate)
					. $this->ctgr_postdata_common3
					. $this->ctgr_postdata_checked . $ctgr_arr['checked_value']
					. $this->ctgr_postdata_common4
					. urlencode('ctl00$ctl00$ContentPlaceHolderContent$Frame1Content$ucPaging$ddlItemsPerPage') . "=$pre_num&"
					. urlencode('ctl00$ctl00$ContentPlaceHolderContent$Frame1Content$ucPaging$HiddenCurrentPage') . "=$current_page_num&"
					. urlencode('ctl00$ctl00$ContentPlaceHolderContent$Frame1Content$ucPaging$HiddenMaxPage') . "=$max_page";

				$this->showCrawlPageTime($url);

				$r = $this->oLinkFeed->GetHttpResult($url, $request);
				$result = $r['content'];
				
				$prgm_str = trim($this->oLinkFeed->ParseStringBy2Tag($result, 'Hide/Show column:', '</table>'));
				
				if (empty($prgm_str)){
					mydie("Result of {$ctgr_arr[1]} is null");
				}
				
				preg_match_all('/ProgramInfo\.aspx\?pid=(\d+)["#]/',$prgm_str,$m);
				
				if (!isset($m[1]) || empty($m[1])) mydie("The result of program ID is empty when search category, please check the Regular expression");
				
				foreach (array_unique($m[1]) as $val)
					$prgm[] = $val;
			}
		}
		return $prgm;
	}
	
	function getCategoryList()
	{
		$category_list = array();
		$this->LoginIntoAffService();
		
		$request = array("AffId" => $this->info["AffId"], "method" => "get",);
		$url = 'http://publisher.affili.net/Programs/ProgramSearch.aspx?nr=1&pnp=3';

		$this->showCrawlPageTime($url);

		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r['content'];
		$content = str_replace("\r", "\n", $content);
		$ctgr_str = $this->oLinkFeed->ParseStringBy2Tag($content, array('ContentPlaceHolderContent_Frame1Content_radCategoryTreeClientData','[['), ']]');
		
		try {
			$ctgr_arr = explode('],[',$ctgr_str);
			
			foreach ($ctgr_arr as $key => $val)
			{
				$ctgr_arr_son = explode(',',$val);
				foreach ($ctgr_arr_son as $k => $v)
				{
					$vv = trim($v,"'");
					if (!empty($vv) && !is_numeric($vv) && !in_array($vv,array('false','true','{}')))
						$category_list[$key][] = $vv;
				}
			}
			
			for ($i = 0; $i < count($category_list); $i ++)
			{
				$check_val = '';
				for ($j = 0; $j < count($category_list); $j ++)
				{
					if ($j == $i)
						$check_val .= '1';
					else
						$check_val .= '0';
				}
				$category_list[$i]['checked_value'] = $check_val;
			}
		} catch (Exception $e) {
			mydie("Get category list failed : {$e->getMessage()} \n");
		}
		
		return $category_list;
	}

	function showCrawlPageTime($Url)
    {
        echo "\n", $Url,"\t",date('Y-m-d H:i:s', time()), "\n";
    }

    function rememberNoCategoryProgram($affId, $prgm_cagr)
    {
        if (!$affId || empty($prgm_cagr))
            return false;

        $programIdList = array();

        foreach ($prgm_cagr as $val){
            if (empty($val['CategoryExt']))
                $programIdList[] = $val['IdInAff'];
        }
        $pIdList = join(',', $programIdList);

        $cache_file = $this->oLinkFeed->fileCacheGetFilePath($affId, "aff_{$affId}_noCategoryProgramList.dat", 'categoryRemaindFile', true);
        if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {

            $this->oLinkFeed->fileCachePut($cache_file, $pIdList);
        }else {
            $result = file_get_contents($cache_file);
            $old_programIdList = explode(',', $result);
            if (count($programIdList) - count($old_programIdList) > 10) {
                mydie("More than 10 programs lose category, please check the page!");
            }else {
                unlink($cache_file);
                $this->oLinkFeed->fileCachePut($cache_file, $pIdList);
            }
        }

        return $programIdList;
    }

}

?>