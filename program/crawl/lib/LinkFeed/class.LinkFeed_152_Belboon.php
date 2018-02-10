<?php

require_once 'text_parse_helper.php';

class LinkFeed_152_Belboon
{
	var $info = array(
		"ID" => "152",
		"Name" => "Belboon",
		"IsActive" => "YES",
		"ClassName" => "LinkFeed_152_Belboon",
		"LastCheckDate" => "1970-01-01",
	);
	
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		
		if(SID == 'bdg01'){
			$this->platformid = '605247';
			$this->config = array(
					'login' => 'mkinfotech',
					'password' => 'buqQl6ucrFprJFeeREqe',
					'trace' => true
			);
		}else{
			$this->platformid = '605929';
			$this->config = array(
					'login' => 'brandreward',
					'password' => 'UEGbG8UV8hYyZ1Q6WLZV',
					'trace' => true
			);
		}
		
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		$this->soapClient = null;
	}
	
	function GetAllLinksFromAffByMerID($merinfo)
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		return $arr_return;
	}
	
	function GetAllLinksByAffId()
	{
		
	    $check_date = date('Y-m-d H:i:s');
	    $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		
		$request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "", );
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info,6);
		$request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "switchPlatform=$this->platformid");
		$url = 'https://ui.belboon.com/ShowPartnershipList,MID.43/DoHandlePartnershipList.en.html';
		$this->oLinkFeed->GetHttpResult($url, $request);
		
		list ($limit, $offset, $coupon) = array(100, 0, 0);
		do 
		{
			$request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "FilterPartnershipListAffiliateProgramStatus=Existing+Partner&PagingPartnershipListAffiliateProgram=100", );
			$url = sprintf('https://ui.belboon.com/ShowPartnershipList,mid.43,Offset.%s/DoHandlePartnershipList.en.html', $offset);
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$content = $r['content'];
			preg_match_all('@"(/ShowPartnershipOverview,mid\.(\d+)/DoHandlePartnership,id\.(\d+),partnershipid\.\d+,programid\.(\d+)\.en\.html)"@', $content, $programs);
			if (empty($programs) || empty($programs[1]) || !is_array($programs[1]))
				break;
			foreach ((array)$programs[1] as $key => $program)
			{
				$page_offset = 0;
				//do
				//{
					$request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "PagingAdlist=100", );
					$url = sprintf('https://ui.belboon.com/ShowPartnershipOverview,mid.%s,Offset.%s/DoHandlePartnership,id.%s,partnershipid.%s,programid.%s.en.html',
							$programs[2][$key], $page_offset, $programs[3][$key], $programs[3][$key], $programs[4][$key]);
					$r = $this->oLinkFeed->GetHttpResult($url, $request);
					$content = $r['content'];
					preg_match_all('@<\!-- Werbemittelname -->.*?toggleCode\((\d+)\).*?</textarea>@ms', $content, $chapters);
					if (empty($chapters) || empty($chapters[0]) || !is_array($chapters[0]))
						break;
					$links = array();
					$coupon = 0;
					foreach ((array)$chapters[0] as $link_key => $v)
					{
						$link = array(
								"AffId" => $this->info["AffId"],
								"AffMerchantId" => $programs[4][$key],
								"AffLinkId" => $chapters[1][$link_key],
								"LinkName" => '',
								"LinkDesc" => '',
								"LinkStartDate" => '0000-00-00',
								"LinkEndDate" => '0000-00-00',
								"LinkPromoType" => 'N/A',
								"LinkHtmlCode" => '',
								"LinkCode" => '',
								"LinkOriginalUrl" => "",
								"LinkImageUrl" => '',
								"LinkAffUrl" => '',
								"DataSource" => 54,
						        "Type"       => 'link'
						);
						if (preg_match('@margin:10px;float:left;"><strong>(.*?)</strong>@', $v, $g))
							$link['LinkName'] = trim(html_entity_decode(strip_tags($g[1])));
						if (preg_match('@<textarea.*?>(.*?)</textarea>@ms', $v, $g))
						{
							$link['LinkHtmlCode'] = $g[1];
							$link['LinkDesc'] = trim(html_entity_decode(strip_tags($g[1])));
						}
						//Voucher code推广类型，desc不一样
						if (preg_match('@style="background-color:#DDDDDD;padding-right:5px;color:#343468;"><!-- typ -->Voucher code</th>.*?<table border="0" cellpadding="3" cellspacing="0"><tr>.*?</tr><tr>.*?</tr><tr><td valign="top">:</td><td>(.*?)</td></tr></table>@is', $v, $g)){ 
						    $link['LinkDesc'] = trim(html_entity_decode(strip_tags($g[1])));
						}
						if (preg_match('@<img src="(.*?)"@', $link['LinkHtmlCode'], $g))
							$link['LinkImageUrl'] = $g[1];
						if (preg_match('@<a href="(.*?)"@', $link['LinkHtmlCode'], $g))
							$link['LinkAffUrl'] = $g[1];
						$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName'] . ' ' . $link['LinkDesc']);
						$code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc']);
						if (!empty($code))
						{
							$link['LinkCode'] = $code;
							$link['LinkPromoType'] = 'COUPON';
						}
						if (preg_match('@-->Voucher code</th>@', $v, $g))
						{
							$link['LinkPromoType'] = 'COUPON';
							if (preg_match('@\'normalnodeco\'.*?>(.*?)</a>@', $v, $g))
							{
								$link['LinkCode'] = trim(strip_tags($g[1]));
								$coupon ++;
							}
						}
						if (empty($link['LinkName']) || empty($link['AffLinkId']) || empty($link['LinkHtmlCode']))
							continue;							
						$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
						$links[] = $link;
						$arr_return["AffectedCount"] ++;
					}
					echo sprintf("get links by page...program:%s, offset:%s, %s links(s) found. (%s)\n", $programs[3][$key], $page_offset, count($links), $coupon);
					if(count($links) > 0)
						$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
					$page_offset += count($chapters[0]);
				//}while(count($chapters[0]) >= $limit);
			}
			$offset += count($programs[0]);
		}while(count($programs[1]) >= $limit);
		
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link');
		return $arr_return;		
	}
	
	private function getSoapClient()
	{
		if (!$this->soapClient)
		{
			$client  = new SoapClient("http://api.belboon.com/?wsdl", $this->config);
			$this->soapClient = $client;
		}
		return $this->soapClient;
	}

	// the soap call failed often.
	// try another 2 times when failed.
	private function soapCall_152($method)
	{
		$args = func_get_args();
		array_shift($args);
		$client = $this->getSoapClient();
		$handler = array($client, $method);
		$retry = 3;
		$r = null;
		do 
		{
			$retry --;
			try
			{
				$r = call_user_func_array($handler, $args);
			}
			catch (Exception $e)
			{
				echo sprintf("Exception raised: %s, Retry:%s\n", $e->getMessage(), $retry);
			}
		}while($retry >= 0 && empty($r));
		return $r;
	}

	function getCouponFeed()
	{
	    $check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0,"UpdatedCount" => 0,"Detail" => array());
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "");

		// coupon by api
		$limit = 100;
		$page = 0;
		do
		{
			$links = array();
			// api params:
			// adPlatformIds programId query hasPartnership voucherCode voucherType validFrom validTo orderBy limit offset
			$r = $this->soapCall_152('getVoucherCodes', array($this->platformid), null, null, true, null, null, null, null, array('programId'=> 'ASC'), $limit, $limit * $page);
			if(empty($r) || empty($r->handler) || empty($r->handler->voucherCodes) || !is_array($r->handler->voucherCodes) || count($r->handler->voucherCodes) < 1)
				break;
			foreach($r->handler->voucherCodes as $v)
			{
				$link = array(
						"AffId" => $this->info["AffId"],
						"AffMerchantId" => $v['programid'],
						"AffLinkId" => $v['id'],
						"LinkName" => trim(html_entity_decode($v['name'])),
						"LinkDesc" => trim(html_entity_decode($v['voucherdescription'])),
						"LinkStartDate" => parse_time_str($v['voucherstart']),
						"LinkEndDate" => parse_time_str($v['voucherend']),
						"LinkPromoType" => 'COUPON',
						"LinkHtmlCode" => trim(html_entity_decode($v['publishercode'])),
						"LinkCode" => $v['vouchercode'],
						"LinkOriginalUrl" => "",
						"LinkImageUrl" => '',
						"LinkAffUrl" => '',
						"DataSource" => 54,
				        "Type"       => 'promotion'
				);
				if (empty($link['LinkName']))
				{
					$link['LinkName'] = sprintf('%s Coupon.', trim(html_entity_decode($v['programname'])));
					if (!empty($link['LinkCode']))
						$link['LinkName'] .= sprintf('Use Code: %s', $link['LinkCode']);
				}
				if (preg_match('@a href="(.*?)"@', $link['LinkHtmlCode'], $g))
					$link['LinkAffUrl'] = $g[1];
				if (empty($link['AffMerchantId']) || empty($link['AffLinkId']))
					continue;
				$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
				$links[] = $link;
				$arr_return["AffectedCount"] ++;
			}
			echo sprintf("get link form api(voucherCodes)...platformId:%s, page:%s, %s link(s) found.\n", $this->platformid, $page, count($links));
			if (count($links) > 0)
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
			$page++;
		}while ($page < 1000);
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'promotion');

		// links by api.
		// stopped. the api Maximum of requests per hour reached.
		// changed to get the links form the page in the GetAllLinksFromAffByMerID function
		/*
		foreach ($this->platformids as $this->platformid)
		{
			$page = 0;
			do
			{
				$links = array();
				// api params: 
				// adPlatformIds, hasPartnership, programId, adType, adWidth, adHeight, orderBy, limit, offset
				$r = $this->soapCall_152('searchCommonAds', array($this->platformid), true, null, null, null, null, array('programId'=> 'ASC'), $limit, $limit * $page);
				if(empty($r) || empty($r->handler) || empty($r->handler->commonAds) || !is_array($r->handler->commonAds) || count($r->handler->commonAds) < 1)
					break;
				foreach($r->handler->commonAds as $v)
				{
					if (empty($v['adid']))
						continue;
					$link = array(
							"AffId" => $this->info["AffId"],
							"AffMerchantId" => $v['programid'],
							"AffLinkId" => $this->platformid . "_" . $v['adid'],
							"LinkName" => trim(html_entity_decode($v['adname'])),
							"LinkDesc" => trim(html_entity_decode($v['linktext'])),
							"LinkStartDate" => '0000-00-00',
							"LinkEndDate" => '0000-00-00',
							"LinkPromoType" => 'DEAL',
							"LinkHtmlCode" => trim(html_entity_decode($v['adcodecomplete'])),
							"LinkCode" => '',
							"LinkOriginalUrl" => "",
							"LinkImageUrl" => $v['viewimgurl'],
							"LinkAffUrl" => $v['linkurl'],
							"DataSource" => 54,
					);
					$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName'] . " " . $link['LinkDesc'] . " " . $link['LinkHtmlCode']);
					if (empty($link['AffMerchantId']) || empty($link['AffLinkId']) || empty($link['LinkName']))
						continue;
					$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
					$links[] = $link;
					$arr_return["AffectedCount"] ++;
				}
				echo sprintf("get link form api(commonAds)...platformId:%s, page:%s, %s link(s) found.\n", $this->platformid, $page, count($links));
				if (count($links) > 0)
					$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
				$page++;
			}while ($page < 1000);
		}
		*/

		// get links from page
		/*$request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "", );
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		list ($limit, $offset, $coupon) = array(100, 0, 0);
		do 
		{
			$request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "PagingPartnershipListAffiliateProgram=100", );
			$url = sprintf('https://ui.belboon.com/ShowPartnershipList,mid.43,Offset.%s/DoHandlePartnershipList.en.html', $offset);
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$content = $r['content'];
			preg_match_all('@"(/ShowPartnershipOverview,mid\.(\d+)/DoHandlePartnership,id\.(\d+),partnershipid\.\d+,programid\.(\d+)\.en\.html)"@', $content, $programs);
			if (empty($programs) || empty($programs[1]) || !is_array($programs[1]))
				break;
			foreach ((array)$programs[1] as $key => $program)
			{
				$page_offset = 0;
				//do
				//{
					$request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "PagingAdlist=100", );
					$url = sprintf('https://ui.belboon.com/ShowPartnershipOverview,mid.%s,Offset.%s/DoHandlePartnership,id.%s,partnershipid.%s,programid.%s.en.html',
							$programs[2][$key], $page_offset, $programs[3][$key], $programs[3][$key], $programs[4][$key]);
					$r = $this->oLinkFeed->GetHttpResult($url, $request);
					$content = $r['content'];
					preg_match_all('@<\!-- Werbemittelname -->.*?toggleCode\((\d+)\).*?</textarea>@ms', $content, $chapters);
					if (empty($chapters) || empty($chapters[0]) || !is_array($chapters[0]))
						break;
					$links = array();
					$coupon = 0;
					foreach ((array)$chapters[0] as $link_key => $v)
					{
						$link = array(
								"AffId" => $this->info["AffId"],
								"AffMerchantId" => $programs[4][$key],
								"AffLinkId" => $chapters[1][$link_key],
								"LinkName" => '',
								"LinkDesc" => '',
								"LinkStartDate" => '0000-00-00',
								"LinkEndDate" => '0000-00-00',
								"LinkPromoType" => 'N/A',
								"LinkHtmlCode" => '',
								"LinkCode" => '',
								"LinkOriginalUrl" => "",
								"LinkImageUrl" => '',
								"LinkAffUrl" => '',
								"DataSource" => 54,
						);
						if (preg_match('@margin:10px;float:left;"><strong>(.*?)</strong>@', $v, $g))
							$link['LinkName'] = trim(html_entity_decode(strip_tags($g[1])));
						if (preg_match('@<textarea.*?>(.*?)</textarea>@ms', $v, $g))
						{
							$link['LinkHtmlCode'] = $g[1];
							$link['LinkDesc'] = trim(html_entity_decode(strip_tags($g[1])));
						}
						if (preg_match('@<img src="(.*?)"@', $link['LinkHtmlCode'], $g))
							$link['LinkImageUrl'] = $g[1];
						if (preg_match('@<a href="(.*?)"@', $link['LinkHtmlCode'], $g))
							$link['LinkAffUrl'] = $g[1];
						$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName'] . ' ' . $link['LinkDesc']);
						$code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc']);
						if (!empty($code))
						{
							$link['LinkCode'] = $code;
							$link['LinkPromoType'] = 'COUPON';
						}
						if (preg_match('@-->Voucher code</th>@', $v, $g))
						{
							$link['LinkPromoType'] = 'COUPON';
							if (preg_match('@\'normalnodeco\'.*?>(.*?)</a>@', $v, $g))
							{
								$link['LinkCode'] = trim(strip_tags($g[1]));
								$coupon ++;
							}
						}
						if (empty($link['LinkName']) || empty($link['AffLinkId']) || empty($link['LinkHtmlCode']))
							continue;
						$count_link ++;
						$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
						$links[] = $link;
						$arr_return["AffectedCount"] ++;
					}
					echo sprintf("get links by page...program:%s, offset:%s, %s links(s) found. (%s)\n", $programs[3][$key], $page_offset, count($links), $coupon);
					if(count($links) > 0)
						$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
					$page_offset += count($chapters[0]);
				//}while(count($chapters[0]) >= $limit);
			}
			$offset += count($programs[0]);
		}while(count($programs[1]) >= $limit);*/
		return $arr_return;
	}
	
	function GetAllProductsByAffId()
	{
	
		$check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
		$request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "");
	
		//$arr_merchant = $this->oLinkFeed->getApprovalAffMerchant($this->info["AffId"]);
		$productNumConfig = $this->oLinkFeed->product_program_num_config($this->info["AffId"]);
		$productNumConfigAlert = '';
		$isAssignMerchant = FALSE;
		$mcount = 0;
		
		$oSmartFeed= new SoapClient( "http://smartfeeds.belboon.com/SmartFeedServices.php?wsdl");
		$Login = $this->config;
		$oSessionHash= $oSmartFeed->login($Login['login'], $Login['password']);
		if(!$oSessionHash->HasError){
			$sSessionHash= $oSessionHash->Records['sessionHash'];
			$aResult= $oSmartFeed->getFeeds($sSessionHash);
			if ($aResult->HasError)
				mydie("Get product feed failed: ".$aResult->ErrorMsg);
			//($aResult);exit;
			//$tempTestArr[] = $aResult->Records[0];
			//print_r($tempTestArr);exit;
			foreach ($aResult->Records as $value)
			{
			    $crawlMerchantsActiveNum = 0;
			    $offset = 0;
			    do{
			        
			        echo $offset.PHP_EOL;
			        $config = array(
			            'platforms' => array($this->platformid),
			            'feeds' => array($value['id']),
			            'limit' => 50,
			            'offset' => $offset,
			        );
			        $re= $oSmartFeed->getProductData ($sSessionHash, $config);
			        if ($re->HasError)
			            mydie("Get product feed failed: ".$re->ErrorMsg);
			        if(empty($re->Records)){
			            break;
			        } 
			        foreach ($re->Records as $v)
			        {
			            $ProductId = trim($v['belboon_productnumber']);
			            $AffMerchantId = trim($v['belboon_programid']);
			            	
			            $setMaxNum  = isset($productNumConfig[$AffMerchantId]) ? $productNumConfig[$AffMerchantId]['limit'] :  100;
			            $isAssignMerchant =  	isset($productNumConfig[$AffMerchantId]) ? TRUE : FALSE;
			            $product_path_file = $this->oLinkFeed->getProductImgFilePath($this->info["AffId"],"{$AffMerchantId}_".urlencode($ProductId).".png", PRODUCTDIR);
			            if(!$this->oLinkFeed->fileCacheIsCached($product_path_file))
			            {
			                $file_content = $this->oLinkFeed->downloadImg($v['imagebigurl']);
			                if(!$file_content) //下载不了跳过。
			                    continue;
			                $this->oLinkFeed->fileCachePut($product_path_file, $file_content);
			            }
			            if(!isset($v['productname']) || empty($v['productname']) || !isset($ProductId))
			            {
			                continue;
			            }
			            	
			            $link = array(
			                "AffId" => $this->info["AffId"],
			                "AffMerchantId" => $AffMerchantId,
			                "AffProductId" => $ProductId,
			                "ProductName" => addslashes(trim($v['productname'])),
			                "ProductCurrency" => trim($v['currency']),
			                "ProductPrice" => trim($v['currentprice']),
			                "ProductOriginalPrice" => trim($v['oldprice']),
			                "ProductRetailPrice" =>'',
			                "ProductImage" => addslashes($v['imagebigurl']),
			                "ProductLocalImage" => addslashes($product_path_file),
			                "ProductUrl" => addslashes($v['deeplinkurl']),
			                "ProductDestUrl" => '',
			                "ProductDesc" => addslashes($v['productdescriptionlong']),
			                "ProductStartDate" => trim($v['lastupdate']),
			                "ProductEndDate" => trim($v['validuntil']),
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
			        $offset += 50;
			        
			        
			    }while(1);
			    
			    if($isAssignMerchant){
			        $productNumConfigAlert .= "AFFID:".$this->info["AffId"].",Program({$value['program_name']}),Crawl Count($crawlMerchantsActiveNum),Total Count({$value['product_count']}) \r\n";
			    }
			    
				$mcount ++;
			}
		}else 
		{
			mydie("API login failed: ".$oSessionHash->ErrorMsg."\r\n");
		}
		echo 'merchant count:'.$mcount.PHP_EOL;
		echo $productNumConfigAlert;
		$this->oLinkFeed->checkProductExists($this->info["AffId"], $check_date);
			
		echo 'END time'.date('Y-m-d H:i:s').PHP_EOL;
		return $arr_return;
	}
	
	function GetProgramFromAff()
	{
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
		$this->GetProgramByApi();
		$this->checkProgramOffline($this->info["AffId"], $check_date);
		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}
	
	function RetryGetProgramFromAff($retry, $rePage)
	{
		$check_date = date("Y-m-d H:i:s");
		echo "retry craw Program start @ {$check_date}\r\n";
		$this->GetProgramByApi($retry, $rePage);
		$this->checkProgramOffline($this->info["AffId"], $check_date);
		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}
	
	function GetProgramByApi($retry = 5, $rePage = 0)
	{
		echo "\tGet Program by api start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		
		$client  = new SoapClient("http://api.belboon.com/?wsdl", $this->config);
		
		$active_program = array();
		$activeInAff_program = array();
		
//		foreach(array('PARTNERSHIP', null) as $partnershipStatus){	
			$partnershipStatus = null;
			$program_num = 0;
//			try {
				$client  = new SoapClient("http://api.belboon.com/?wsdl", $this->config);
				$page = $rePage;
				$hasNextPage = true;
				while($hasNextPage){
					$limit = 100;
					$start = $limit * $page;
					
					$a = array(
							//$this->platformid, // adPlatformId
							//null, // programLanguage
							//$partnershipStatus, // partnershipStatus
							//null, // query
							//array('programid' => 'ASC'), // orderBy
							$limit, // limit
							$start // offset
					);
					//print_r($a);
					
					try{
						$result = $client->getPrograms(
														$this->platformid, // adPlatformId
														null, // programLanguage
														$partnershipStatus, // partnershipStatus
														null, // query
														array('programid' => 'ASC'), // orderBy
														$limit, // limit
														$start // offset
														);
					} catch( Exception $e ) {
						$retry-=1;
						if ($retry == 0)
							mydie("die: Api error . $e\n");
						echo ("die: Api error . $e\n retry request the api {5-$retry}");
						$this->RetryGetProgramFromAff($retry, $page);
				     
					}   
					if(!count($result->handler->programs)){
						$hasNextPage = false;
						break;
					}
					if($page > 100){
						mydie("die: page max > 100.\n");
					}
					//print_r($result);exit;
					foreach($result->handler->programs as $prgm){							
						$strMerID = $prgm['programid'];
						if(!$strMerID) continue;
	
						if(isset($active_program[$strMerID])){
							continue;
						}

						$Partnership = "NoPartnership";
						$StatusInAffRemark = $prgm['partnershipstatus'];
						if($StatusInAffRemark == 'PARTNERSHIP'){
							$Partnership = 'Active';
						}elseif($StatusInAffRemark == 'REJECTED'){
							$Partnership = 'Declined';
						}elseif($StatusInAffRemark == 'PENDING'){
							$Partnership = 'Pending';
						}elseif($StatusInAffRemark == 'PAUSED'){
							$Partnership = 'Expired';
						}elseif($StatusInAffRemark == 'AVAILABLE'){
							$Partnership = 'NoPartnership';
						}

						if($Partnership == 'Active'){
							$active_program[$strMerID] = $prgm['partnershipid'];
						}
						if (!isset($activeInAff_program[$strMerID]))
							$activeInAff_program[$strMerID] = array(
								"AffId" => $this->info["AffId"],
								"IdInAff" => $strMerID,
								"CategoryExt" => ''
							);
	
						$CommissionExt = '
										commissionsaleminpercent: '.$prgm['commissionsaleminpercent'].',
										commissionsalemaxpercent: '.$prgm['commissionsalemaxpercent'].',
										commissionsaleminfix: '.$prgm['commissionsaleminfix'].',
										commissionsalemaxfix: '.$prgm['commissionsalemaxfix'].',
										commissionleadmin: '.$prgm['commissionleadmin'].',
										commissionleadmax: '.$prgm['commissionleadmax'].',
										commissionclickmin: '.$prgm['commissionclickmin'].',
										commissionclickmax: '.$prgm['commissionclickmax'].',
										commissionviewmin: '.$prgm['commissionviewmin'].',
										commissionviewmax: '.$prgm['commissionviewmax'];

						//$result = $client->getProgramDetails(342);
						$arr_prgm[$strMerID] = array(
							"AffId" => $this->info["AffId"],
							"IdInAff" => $strMerID,
							"Name" => addslashes($prgm['programname']),
							//"TargetCountryExt" => addslashes($TargetCountryExt),
							//"TargetCountryInt" => addslashes($prgm['programlanguage']),
							"Homepage" => $prgm['advertiserurl'],
							"Description" => addslashes($prgm['programdescription']),
							"TermAndCondition" => addslashes($prgm['programterms']),
							"StatusInAffRemark" => addslashes($StatusInAffRemark),
							"StatusInAff" => 'Active',						//'Active','TempOffline','Offline'
							"Partnership" => $Partnership,					//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
							"DetailPage" => $prgm['programregisterurl'],
							"LastUpdateTime" => date("Y-m-d H:i:s"),
							"CommissionExt" => addslashes($CommissionExt),
							"LogoUrl" => addslashes($prgm['programlogo']),
						);
						//print_r($arr_prgm);exit;
						$program_num++;
						if(count($arr_prgm) >= 100){
							$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
							$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
							$arr_prgm = array();
						}
					}
					$page++;
				}
//			} catch( Exception $e ) {
//				mydie("die: Api error . $e\n");
//			}
			if(count($arr_prgm)){
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
				unset($arr_prgm);
			}
			if($partnershipStatus == 'PARTNERSHIP'){
				$Status = 'Active';
			}else{
				$Status = 'NoActive';
			}
			echo "\tUpdate siteID is $this->platformid ($Status) : ({$program_num}) program.\r\n";
//		}
		echo "\tGet Program by api end\r\n";
		if($program_num < 10){
			mydie("die: program count < 10, please check program.\n");
		}
		
		echo "\tget program country ext.\r\n";
		$this->getProgramCountryByPage($active_program);
		
		echo "\tget program category ext.\r\n";
		$this->getProgramCategoryByPage($activeInAff_program,$objProgram);
		
		echo "\tSet program country int.\r\n";
		$objProgram->setCountryInt($this->info["AffId"]);
	}
	
	function getProgramCountryByPage($active_program){		
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", );
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info,6);
		
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		
		foreach($active_program as $strMerID => $partnershipid){
			$url = "https://ui.belboon.com/ShowPartnershipOverview,mid.43/DoHandlePartnership,id.$partnershipid,partnershipid.$partnershipid,programid.$strMerID.en.html";
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$content = $r['content'];
			
			$strLineStart = '<div id="content_tecdata" class="tabContentArea">';
			$nLineStart = stripos($content, $strLineStart);
			$CookieTime = trim($this->oLinkFeed->ParseStringBy2Tag($content, array('Cookie Lifetime (days):','valign="top">'), '</td>', $nLineStart));
			$TargetCountryExt = strip_tags($this->oLinkFeed->ParseStringBy2Tag($content, array('Trading area:','</strong>'), '<strong>', $nLineStart));
			$TargetCountryExt = preg_replace("/\s/", "", $TargetCountryExt);
			$PaymentDays = trim($this->oLinkFeed->ParseStringBy2Tag($content, array('Average processing time:</strong>','</strong>'), 'Day', $nLineStart));
			$arr_prgm[$strMerID] = array(
								"AffId" => $this->info["AffId"],
								"IdInAff" => $strMerID,								
								"TargetCountryExt" => addslashes($TargetCountryExt),
								"CookieTime" => addslashes($CookieTime),
								"PaymentDays" => addslashes($PaymentDays),
							);
			if(count($arr_prgm) >= 100){
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);				
				$arr_prgm = array();
			}			
		}
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);			
			unset($arr_prgm);
		}
	}
	
	function getProgramCategoryByPage($activeInAff_program,$objProgram){
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", );
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info,6);
		
		$url = 'https://ui.belboon.com/ShowProgramListAffiliate,Mode.ProgramCatalog,platformid.605929,mid.40/DoHandleProgramListAffiliate.en.html';
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = preg_replace("/>\\s+</i", "><", $r['content']);
		
		$strPosition = 0;
		$outside_program = array();
		
		while ($strPosition >= 0) {
			$strPosition = stripos($content, 'style="width:220px;padding:5px 3px;', $strPosition);
			if ($strPosition === false) break;
			
			$suffix_url = trim($this->oLinkFeed->ParseStringBy2Tag($content, array('style="width:220px;padding:5px 3px;','a href="'), '"', $strPosition));
			if (!$suffix_url) continue;
			
			$ctgr_url = "https://ui.belboon.com$suffix_url";
			
			preg_match("/catid\.(\d+),platformid/",$suffix_url,$m);
			
			$ctgr_name = trim($this->oLinkFeed->ParseStringBy2Tag($content, 'target="_self">', '</a>', $strPosition));
			
			if (!$ctgr_name) mydie("die: can't get catrgory name !");
			
			$ctgr_son = $this->oLinkFeed->GetHttpResult($ctgr_url, $request);
			$ctgr_son_page = preg_replace("/>\\s+</i", "><", $ctgr_son['content']);
			
			$sonStrPosition = 0;
			$more_ctgr = false;
			
			while ($sonStrPosition >= 0) {
				$suffix_url_son = trim($this->oLinkFeed->ParseStringBy2Tag($ctgr_son_page, array('style="width:220px;padding:5px 3px;','a href="'), '"', $sonStrPosition));
				
				if ($suffix_url_son) {
					$ctgr_son_url = "https://ui.belboon.com$suffix_url_son";
					
					preg_match("/catid\.(\d+),platformid/",$ctgr_son_url,$m_son);
					
					$ctgr_son_name = trim($this->oLinkFeed->ParseStringBy2Tag($ctgr_son_page, 'target="_self">', '</a>', $sonStrPosition));
					
					if (!$ctgr_son_name) mydie("die: can't get son catrgory name !");
					
					$ctgr_ext_name = $ctgr_name . ' > ' . $ctgr_son_name;
					
					while ($ctgr_son_url){
						$m_url = array();
						$ctgr_grandson = $this->oLinkFeed->GetHttpResult($ctgr_son_url, $request);
						$ctgr_grandson_page = preg_replace("/>\\s+</i", "><", $ctgr_grandson['content']);
						
						$ctgr_more_again = trim($this->oLinkFeed->ParseStringBy2Tag($ctgr_grandson_page, array('style="width:220px;padding:5px 3px;','a href="'), '"'));
						if ($ctgr_more_again) mydie("There has program of category 4 classification !");
						
						if (stripos($ctgr_grandson_page,'img src="https://ui.belboon.com/images/arrow_right.png') !== false){
							preg_match('/\/a><a\s+href=\"(.+)\"><img\s+src=\"https:\/\/ui\.belboon\.com\/images\/arrow_right\.png/i',$ctgr_grandson_page,$m_url);
							$ctgr_son_url = "https://ui.belboon.com{$m_url[1]}";
						} else
							$ctgr_son_url = false;
						
						$match_start_pos1 = strpos($ctgr_grandson_page,'Select category');
						preg_match_all('/https:\/\/ui\.belboon\.com\/images\/logos\/100\/logo_(\d+)\.gif"/U',$ctgr_grandson_page,$logo1_prId,PREG_PATTERN_ORDER,$match_start_pos1);
						preg_match_all('/programid\.(\d+)[,\.]/U',$ctgr_grandson_page,$m1_prId,PREG_PATTERN_ORDER,$match_start_pos1);
						
						$prgm1_id_list = array_unique(array_merge($logo1_prId[1],$m1_prId[1]));
						
						foreach ($prgm1_id_list as $val){
							if (isset($activeInAff_program[$val])) {
								if (empty($activeInAff_program[$val]['CategoryExt']))
									$activeInAff_program[$val]['CategoryExt'] = addslashes($ctgr_ext_name);
								else
									$activeInAff_program[$val]['CategoryExt'] = addslashes($activeInAff_program[$val]['CategoryExt'].','.$ctgr_ext_name);
							}else{
								if (!key_exists($val,$outside_program)){
									$logo_position = stripos($ctgr_grandson_page,"logo_{$val}.gif");
									$outside_program[$val] = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($ctgr_grandson_page, array('a href="','>'), '<',$logo_position)));
								}
							}
						}
					}
					$more_ctgr = true;
				}
				
				if (!$more_ctgr){
					while ($ctgr_url){
						$m2_url = array();
						$ctgr_son = $this->oLinkFeed->GetHttpResult($ctgr_url, $request);
						$ctgr_son_page = preg_replace("/>\\s+</i", "><", $ctgr_son['content']);
						
						if (stripos($ctgr_son_page,'img src="https://ui.belboon.com/images/arrow_right.png') !== false){
							preg_match('/\/a><a\s+href=\"(.+)\"><img\s+src=\"https:\/\/ui\.belboon\.com\/images\/arrow_right\.png/i',$ctgr_son_page,$m2_url);
							$ctgr_url = "https://ui.belboon.com{$m2_url[1]}";
						} else
							$ctgr_url = false;
						
						$match_start_pos2 = strpos($ctgr_son_page,'Select category');
						preg_match_all('/https:\/\/ui\.belboon\.com\/images\/logos\/100\/logo_(\d+)\.gif"/U',$ctgr_son_page,$logo2_prId,PREG_PATTERN_ORDER,$match_start_pos2);
						preg_match_all('/programid\.(\d+)[,\.]/U',$ctgr_son_page,$m2_prId,PREG_PATTERN_ORDER,$match_start_pos2);
						$prgm2_id_list = array_unique(array_merge($logo2_prId[1],$m2_prId[1]));
						
						foreach ($prgm2_id_list as $v){
							if (isset($activeInAff_program[$v])) {
								if (empty($activeInAff_program[$v]['CategoryExt']))
									$activeInAff_program[$v]['CategoryExt'] = addslashes($ctgr_name);
								else
									$activeInAff_program[$v]['CategoryExt'] = addslashes($activeInAff_program[$v]['CategoryExt'].','.$ctgr_name);
							}else{
								if (!key_exists($v,$outside_program)){
									$logo2_position = stripos($ctgr_grandson_page,"logo_{$v}.gif");
									$outside_program[$v] = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($ctgr_son_page, array('a href="','>'), '<',$logo2_position)));
								}
							}
						}
					}
				}
				if (stripos($ctgr_son_page,'style="width:220px;padding:5px 3px;', $sonStrPosition) === false) break;
			}
		}
		
//		print_r($activeInAff_program);exit;
		
		$objProgram->updateProgram($this->info["AffId"], $activeInAff_program);
		unset($activeInAff_program);
		
		if (!empty($outside_program)){
			echo "\tThe outside program list:\n";
			print_r($outside_program);
		}
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
