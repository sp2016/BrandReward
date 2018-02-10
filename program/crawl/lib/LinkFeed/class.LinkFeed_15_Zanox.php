<?php

require_once 'text_parse_helper.php';

class LinkFeed_15_Zanox
{
	var $info = array(
		"ID" => "15",
		"Name" => "Zanox",
		"IsActive" => "YES",
		"ClassName" => "LinkFeed_15_Zanox",
		"LastCheckDate" => "1970-01-01",
	);

	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		$this->soapClient = null;
        $this->getStatus = false;
        
        $this->cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"{$this->info["AffId"]}_".date("YW").".dat", "program", true);
        $this->cache = array();
        if($this->oLinkFeed->fileCacheIsCached($this->cache_file)){
        	$this->cache = file_get_contents($this->cache_file);
        	$this->cache = json_decode($this->cache,true);
        }
        
		if(SID == 'bdg02'){
			$this->ConnectId = '842953543798E1C7D191';
			$this->SecretKey = '93bfeAd13ced4c+2bEc3b47e71410d/6e0930c4a';
			$this->UserId = '2261055';
			$this->SpaceId = '2182939';
		}else{
	    	$this->ConnectId = '68B202842FC821B4177D';
	    	$this->SecretKey = '0bB53d80b93649+683f688f17DB8A1/c05Edcd42';
	    	$this->UserId = '2283237';
	    	$this->SpaceId = '2213285';
		}
        
	}

	private function getSoapClient($force = false)
	{
		require_once INCLUDE_ROOT."wsdl/zanox-api_client/ApiClient.php";
	
		if (!is_object($this->soapClient) || $force)
		{
			$client = ApiClient::factory(PROTOCOL_SOAP);
			$client->setConnectId($this->ConnectId);
			$client->setSecretKey($this->SecretKey);
			$this->soapClient = $client;
		}
		return $this->soapClient;
	}

	// the soap call failed often.
	// try another 2 times when failed.
	private function soapCall_15($method)
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
	    
		/*$pagesize = 100;				
		$http_verb = 'GET';	
		$uri = '/incentives';
		$time_stamp = gmdate('D, d M Y H:i:s T', time());
		$nonce = uniqid() . uniqid();
		$string_to_sign = mb_convert_encoding($http_verb . $uri . $time_stamp . $nonce, 'UTF-8');
		$signature = base64_encode(hash_hmac('sha1', $string_to_sign, $this->SecretKey, true));
		$requestURL = 'http://api.zanox.com/json/2011-03-01' . $uri . '?connectid=' . $this->ConnectId . '&nonce=' . $nonce . '&signature=' . $signature . '&items=' . $pagesize;	
		
		$incentive_type = array("coupons", "samples", "bargains", "freeProducts", "noShippingCosts", "lotteries");
			
		foreach($incentive_type as $type){
			$page = $total = $cnt = $x = $y = 0;
			$hasNextPage = true;			
			while($hasNextPage)
			{			
				$r = $this->oLinkFeed->GetHttpResult($requestURL . '&incentiveType='.$type.'&page='.$page);*/
				$r = $this->oLinkFeed->GetHttpResult('http://api.zanox.com/json/2011-03-01/incentives/?connectid='.$this->ConnectId.'&adspace='.$this->SpaceId);
				$data = @json_decode($r['content'], true);
				/*if($page == 0){					
					$total = intval($data['total']);					
				}
						
				if(($total <= ($page + 1) * $pagesize) || count($data['incentiveItems']) == 0){
					$hasNextPage = false;
				}
				$page++;*/
				
				$links = array();
				
				if(isset($data['incentiveItems']['incentiveItem'])){
					foreach ($data['incentiveItems']['incentiveItem'] as $v)
					{
						$link = array(
								"AffId" => $this->info["AffId"],
								"AffMerchantId" => $v['program']['@id'],
								"AffLinkId" => $v['@id'],
								"LinkName" => $v['name'],
								"LinkStartDate" => parse_time_str(@$v['startDate'], 'Y-m-d H:i:s', false),
								"LinkEndDate" => parse_time_str(@$v['endDate'], 'Y-m-d H:i:s', false),
								"LinkPromoType" => 'COUPON',
								"LinkHtmlCode" => '',
								"LinkCode" => sprintf('%s', @$v['couponCode']),
								"LinkOriginalUrl" => '',
								"LinkImageUrl" => '',
								"LinkDesc" => '',
								"LinkAffUrl" => '',
								"DataSource" => 29,
						        "IsDeepLink" => 'UNKNOWN',
						        "Type"       => 'promotion'
						);
						if (!empty($v['admedia']['admediumItem']) && is_array($v['admedia']['admediumItem']) && !empty($v['admedia']['admediumItem']))
						{
							$i = $v['admedia']['admediumItem'];
							$link['LinkDesc'] = @$i['description'];
							if (!empty($i['trackingLinks']) && !empty($i['trackingLinks']['trackingLink']) && is_array($i['trackingLinks']['trackingLink'])
									&& !empty($i['trackingLinks']['trackingLink'][0]))
							{
								$link['LinkAffUrl'] = $i['trackingLinks']['trackingLink'][0]['ppc'];
								$link['LinkAffUrl'] = preg_replace('@\&zpar9=\[\[\w+\]\]@', '', $link['LinkAffUrl']);
								if (!empty($i['width']) && !empty($i['height']) && $i['width'] > 1 && $i['height'] > 1)
									$link['LinkImageUrl'] = $i['trackingLinks']['trackingLink'][0]['ppv'];
							}
							$link['LinkHtmlCode'] = @$i['code'];
						}
						
						if (!empty($v['restrictions']))
							$link['LinkDesc'] .= $v['restrictions'];
						if (!empty($v['info4customer']))
							$link['LinkDesc'] .= $v['info4customer'];
						if (empty($link['LinkHtmlCode']) && !empty($link['LinkAffUrl']))
							$link['LinkHtmlCode'] = create_link_htmlcode_image($link);
						if ($v['incentiveType'] == 'noShippingCosts')
							$link['LinkPromoType'] = 'free shipping';
						if (empty($link['AffLinkId']) || empty($link['AffMerchantId']) || empty($link['LinkName'])){
							$x++;
							continue;
						}
						if (empty($link['LinkAffUrl'])){
							$y++;
							continue;
						}
							
						$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
						$links[] = $link;
						$cnt++;
						$arr_return['AffectedCount'] ++;
					}
					//echo sprintf("searchIncentives $type ... page: %s, %s link(s) found.\n", $page, count($links));
				}
				if(count($links) > 0){
					$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
				}			
				
				//if(!$hasNextPage){
					//echo $type.":[".$total."]\t";
					echo "get:(".$cnt.")$x/$y\r\n";
				//}
				
			//}
		//}		
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'promotion');
		return $arr_return;
	}
	
	function GetAllProductsByAffId()
	{
	
	    $check_date = date('Y-m-d H:i:s');
	    $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
	    $request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "",);
	    
	     
	    $productNumConfig = $this->oLinkFeed->product_program_num_config($this->info["AffId"]);
	    $productNumConfigAlert = '';
	    $isAssignMerchant = FALSE;
	    $merchantAll = $this->oLinkFeed->getApprovalAffMerchant($this->info["AffId"]);
	    
	    foreach ($merchantAll as $merchatInfo){
	        $links = array();
	        $crawlMerchantsActiveNum = 0;
	        $setMaxNum  = isset($productNumConfig[$merchatInfo['IdInAff']]) ? $productNumConfig[$merchatInfo['IdInAff']]['limit'] :  100;
	        $isAssignMerchant = isset($productNumConfig[$merchatInfo['IdInAff']]) ? TRUE : FALSE;
	        $pageNumber = 0;
	        $recordsReturned = 10;
	        do{
	        
	            $url = "http://api.zanox.com/json/2011-03-01/products?connectid=$this->ConnectId&q=&programs=".$merchatInfo['IdInAff']."&items=$recordsReturned&page=$pageNumber";
	            $pageNumber ++;
	            $r = $this->oLinkFeed->GetHttpResult($url,$request);
	            $data = json_decode($r['content'],true);
	            $totalMatched = $data['total'];
	            if($totalMatched <= 0){ //大于舍弃 
	                break;
	            }
	             
	            foreach ($data['productItems']['productItem'] as $value){
	                
	                if(!isset($value['image']['large']) || empty($value['image']['large'])){
	                    continue;
	                }
	                
	                //下载图片
	                $product_path_file = $this->oLinkFeed->getProductImgFilePath($this->info["AffId"],"{$merchatInfo['IdInAff']}_".urlencode($value['@id']).".png", PRODUCTDIR);
	                if(!$this->oLinkFeed->fileCacheIsCached($product_path_file)){
	                    $file_content = $this->oLinkFeed->downloadImg($value['image']['large']);
	                    if(!$file_content) //下载不了跳过。
	                        continue;
	                    $this->oLinkFeed->fileCachePut($product_path_file, $file_content);
	                
	                }
	                $affUrl = $value['trackingLinks']['trackingLink'][0]['ppc'];
	                if(!isset($value['name']) || empty($value['name']) || !isset($value['@id']) || !$affUrl){
	                    continue;
	                }
	                
	                $link = array(
	                    "AffId" => $this->info["AffId"],
	                    "AffMerchantId" => $merchatInfo['IdInAff'],
	                    "AffProductId" => $value['@id'],
	                    "ProductName" => addslashes($value['name']),
	                    "ProductCurrency" =>$value['currency'],
	                    "ProductPrice" =>$value['price'],
	                    "ProductOriginalPrice" =>$value['priceOld'],
	                    "ProductRetailPrice" =>'',
	                    "ProductImage" => addslashes($value['image']['large']),
	                    "ProductLocalImage" => addslashes($product_path_file),
	                    "ProductUrl" => addslashes($affUrl),
	                    "ProductDestUrl" => '',
	                    "ProductDesc" => addslashes($value['description']),
	                    "ProductStartDate" => '',
	                    "ProductEndDate" => '',
	                );
	                 
	                $links[] = $link;
	                $crawlMerchantsActiveNum ++;
	                $arr_return['AffectedCount'] ++;
	                
	            }
	            if(isset($links) && count($links)) {
	                $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateProductToDB($links);
	                $links = array();
	            }
	            
	            //大于最大数跳出
	            if($crawlMerchantsActiveNum>=$setMaxNum){
	                break;
	            }
	            
	        }while ($recordsReturned * $pageNumber < $totalMatched);
	        if($isAssignMerchant){
	            $productNumConfigAlert .= "AFFID:".$this->info["AffId"].",Program({$merchatInfo['MerchantName']}),Crawl Count($crawlMerchantsActiveNum),Total Count({$totalMatched}) \r\n";
	        }
	    }
	    
	    $this->oLinkFeed->checkProductExists($this->info["AffId"], $check_date);
	    echo $productNumConfigAlert.PHP_EOL;
	    return $arr_return;
	    
	}

	private function getProgramObj()
	{
		if (!empty($this->objProgram))
			return $this->objProgram;
		$this->objProgram = new ProgramDb();
		return $this->objProgram;
	}
	
	function GetAllLinksByAffId(){
	    
	    
	    $check_date = date('Y-m-d H:i:s');
	    $aff_id = $this->info["AffId"];
	    $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, );
	    
	    $arr_merchant = $this->oLinkFeed->getApprovalAffMerchant($this->info["AffId"]);
	    foreach ($arr_merchant as $merinfo){
	        
	        $option = array('program' => $merinfo['AffMerchantId'], 'items' => 50, 'page' => 0);
	        $page = 0;
	        $count = 0;
	        $items = 50;
	        do
	        {
	            $data = $this->soapCall_15('GetAdmedia', $merinfo['AffMerchantId'], null, null, null, null, null, null, null, $page, $items);
	            if (empty($data) || empty($data->total))
	                break;
	            if (!empty($data->admediumItems) && !empty($data->admediumItems->admediumItem))
	                $admediumItems = $data->admediumItems->admediumItem;
	            else if (!empty($data->admediumItem))
	                $admediumItems = $data->admediumItem;
	            else
	                break;
	            	
	            $links = array();
	            foreach ($admediumItems as $v)
	            {
	                 
	                $linkName = $v->name;
	                if(preg_match('/^(0 )+(.+)/',$v->name,$matches)){
	                    $linkName = $matches[2];
	                }
	                $link = array(
	                    "AffId" => $this->info["AffId"],
	                    "AffMerchantId" => $merinfo['AffMerchantId'],
	                    "AffLinkId" => $v->id,
	                    "LinkName" =>  $linkName,
	                    "LinkDesc" =>  '',
	                    "LinkStartDate" => '0000-00-00 00:00:00',
	                    "LinkEndDate" => '0000-00-00 00:00:00',
	                    "LinkPromoType" => 'N/A',
	                    "LinkHtmlCode" => '',
	                    "LinkCode" => '',
	                    "LinkOriginalUrl" => '',
	                    "LinkImageUrl" => '',
	                    "LinkAffUrl" => '',
	                    "DataSource" => 29,
	                    "IsDeepLink" => 'UNKNOWN',
	                    "Type"       => 'link'
	                );
	                if (!empty($v->description)){
	                    $link['LinkDesc'] = $v->description;
	                    $timeFilter = false;
	                    if(preg_match_all('/\d{4}/',$v->description,$matches)){
	                        	
	                        foreach($matches[0] as $mv){
	        
	                            if($mv >= 2017 && $mv <= 2025){
	                                $timeFilter = true;
	                            }
	                        }
	                    }
	                    	
	                    if($timeFilter){
	                        if(preg_match('/Buchungszeitraum:\s+(\d{2}\.\d{2}\.\d{4})\s+(.*?)\s+(\d{2}\.\d{2}\.\d{4})/m',$v->description,$matches)){
	                            $link['LinkStartDate'] = date('Y-m-d H:i:s',strtotime($matches[1]));
	                            $link['LinkEndDate']   = date('Y-m-d H:i:s',strtotime($matches[3]));
	                        }
	                    }else
	                        continue;
	                }
	        
	        
	        
	                if (!empty($v->trackingLinks->trackingLink))
	                    $trackingLink = $v->trackingLinks->trackingLink;
	                else if (!empty($v->trackingLink))
	                    $trackingLink = $v->trackingLink;
	                if (empty($trackingLink))
	                    continue;
	                if (is_array($trackingLink) && !empty($trackingLink[0]->ppc))
	                    $trackingLink = $trackingLink[0];
	                $link['LinkAffUrl'] = $trackingLink->ppc;
	                if (preg_match('@(^.*?ppc/\?\w+)\&zpar9=@', $link['LinkAffUrl'], $g))
	                    $link['LinkAffUrl'] = $g[1] . 'T&ULP=[[XXX]]';
	                if ($v->admediumType == 'image' || $v->admediumType == 'image_text')
	                {
	                    $link['LinkImageUrl'] = $trackingLink->ppv;
	                    $link['LinkHtmlCode'] = create_link_htmlcode_image($link);
	                }
	                else if ($v->admediumType == 'script' && !empty($v->code))
	                    $link['LinkHtmlCode'] = $v->code;
	                else
	                    $link['LinkHtmlCode'] = create_link_htmlcode($link);
	                $code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc']);
	                if (!empty($code))
	                {
	                    $link['LinkCode'] = $code;
	                    $link['LinkPromoType'] = 'COUPON';
	                }
	                else
	                    $link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName'] . '|' . $link['LinkDesc']);
	                if ( empty($link['AffLinkId']))
	                    continue;
	                elseif(empty($link['LinkName'])){
	                    $link['LinkPromoType'] = 'link';
	                }
	                $links[] = $link;
	                $arr_return['AffectedCount'] ++;
	            }
	            $total = $data->total;
	            $page ++;
	            $count += $items;
	            echo sprintf("merchant: %s, page: %s, %s link(s) found.\n", $merinfo['AffMerchantId'], $page, count($links));
	        
	            if(count($links) > 0)
	                $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
	        }while ($page < 100 && $count < $total);
	        
	    }
	    
	    $this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link');
	    return $arr_return;
	}
	
	function GetAllLinksFromAffByMerID($merinfo)
	{
		
	    $check_date = date('Y-m-d H:i:s');
	    $aff_id = $this->info["AffId"];
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, );
		$option = array('program' => $merinfo['AffMerchantId'], 'items' => 50, 'page' => 0);
		$page = 0;
		$count = 0;
		$items = 50;
		do
		{
			$data = $this->soapCall_15('GetAdmedia', $merinfo['AffMerchantId'], null, null, null, null, null, null, null, $page, $items);
			if (empty($data) || empty($data->total))
				break;
			if (!empty($data->admediumItems) && !empty($data->admediumItems->admediumItem))
				$admediumItems = $data->admediumItems->admediumItem;
			else if (!empty($data->admediumItem))
				$admediumItems = $data->admediumItem;
			else
				break;
			
			$links = array();
			foreach ($admediumItems as $v)
			{
			    
			    $linkName = $v->name;
			    if(preg_match('/^(0 )+(.+)/',$v->name,$matches)){
			        $linkName = $matches[2];
			    }
			    $link = array(
						"AffId" => $this->info["AffId"],
						"AffMerchantId" => $merinfo['AffMerchantId'],
						"AffLinkId" => $v->id,
						"LinkName" =>  $linkName,
						"LinkDesc" =>  '',
						"LinkStartDate" => '0000-00-00 00:00:00',
						"LinkEndDate" => '0000-00-00 00:00:00',
						"LinkPromoType" => 'N/A',
						"LinkHtmlCode" => '',
						"LinkCode" => '',
						"LinkOriginalUrl" => '',
						"LinkImageUrl" => '',
						"LinkAffUrl" => '',
						"DataSource" => 29,
				        "IsDeepLink" => 'UNKNOWN',
				        "Type"       => 'link'
				);
				if (!empty($v->description)){
					$link['LinkDesc'] = $v->description;
					$timeFilter = false;
					if(preg_match_all('/\d{4}/',$v->description,$matches)){
					
					    foreach($matches[0] as $mv){
					         
					        if($mv >= 2017 && $mv <= 2025){
					            $timeFilter = true;
					        }
					    }
					}
					
					if($timeFilter){
					    if(preg_match('/Buchungszeitraum:\s+(\d{2}\.\d{2}\.\d{4})\s+(.*?)\s+(\d{2}\.\d{2}\.\d{4})/m',$v->description,$matches)){
					        $link['LinkStartDate'] = date('Y-m-d H:i:s',strtotime($matches[1]));
					        $link['LinkEndDate']   = date('Y-m-d H:i:s',strtotime($matches[3]));
					    }
					}else
					    continue;
				}
				
				
				
				if (!empty($v->trackingLinks->trackingLink))
					$trackingLink = $v->trackingLinks->trackingLink;
				else if (!empty($v->trackingLink))
					$trackingLink = $v->trackingLink;
				if (empty($trackingLink))
					continue;
				if (is_array($trackingLink) && !empty($trackingLink[0]->ppc))
					$trackingLink = $trackingLink[0];
				$link['LinkAffUrl'] = $trackingLink->ppc;
				if (preg_match('@(^.*?ppc/\?\w+)\&zpar9=@', $link['LinkAffUrl'], $g))
					$link['LinkAffUrl'] = $g[1] . 'T&ULP=[[XXX]]';
				if ($v->admediumType == 'image' || $v->admediumType == 'image_text')
				{
					$link['LinkImageUrl'] = $trackingLink->ppv;
					$link['LinkHtmlCode'] = create_link_htmlcode_image($link);
				}
				else if ($v->admediumType == 'script' && !empty($v->code))
					$link['LinkHtmlCode'] = $v->code;
				else 
					$link['LinkHtmlCode'] = create_link_htmlcode($link);
				$code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc']);
				if (!empty($code))
				{
					$link['LinkCode'] = $code;
					$link['LinkPromoType'] = 'COUPON';
				}
				else
					$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName'] . '|' . $link['LinkDesc']);
				if ( empty($link['AffLinkId']))
					continue;
                elseif(empty($link['LinkName'])){
                    $link['LinkPromoType'] = 'link';
                }
				$links[] = $link;
				$arr_return['AffectedCount'] ++;
			}
			$total = $data->total;
			$page ++;
			$count += $items;
			echo sprintf("merchant: %s, page: %s, %s link(s) found.\n", $merinfo['AffMerchantId'], $page, count($links));
			 
			if(count($links) > 0)
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		}while ($page < 100 && $count < $total);
		
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link', $merinfo['IdInAff']);
		return $arr_return;
	}
	

	function GetProgramByApi()
	{
		echo "\tGet Program by api start\r\n";

		require_once INCLUDE_ROOT."wsdl/zanox-api_client/ApiClient.php";
		$objProgram = $this->getProgramObj();
		$arr_prgm = array();
		$program_num = 0;
		$program_num = $this->getProgramDetailsQuick();
		
		if($program_num < 10){
			mydie("die: program count < 10, please check program.\n");
		}
		
		//$program_num += $this->getProgramPartnership($client,$objProgram);
		
		//$this->getProgramDeepLink($client,$objProgram);

		echo "\tGet Program by api end\r\n";
		echo "\tUpdate ({$program_num}) program.\r\n";
		echo "\tSet program country int.\r\n";
		$objProgram->setCountryInt($this->info["AffId"]);
	}

	function GetProgramFromAff()
	{
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
		$this->GetProgramByApi();
		$this->checkProgramOffline($this->info["AffId"], $check_date);
		$this->getProgramSubInfoQuick();
		$this->cache = json_encode($this->cache);
		$this->oLinkFeed->fileCachePut($this->cache_file, $this->cache);
		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}

	function checkProgramOffline($AffId, $check_date){
		$objProgram = $this->getProgramObj();
		$prgm = array();
		$prgm = $objProgram->getNotUpdateProgram($this->info["AffId"], $check_date);
		if(count($prgm)){
			$recheck_ids = array();
			foreach($prgm as $v){
				$recheck_ids[$v['IdInAff']] = $v['IdInAff'];
			}
			if(count($recheck_ids)){	
				//http://api.zanox.com/json/2011-03-01/programs/program/472?connectid=80A516E42488A9CD9505			
				$this->getProgramDetailsById($recheck_ids);
			}
		}

		$prgm = array();
		$prgm = $objProgram->getNotUpdateProgram($this->info["AffId"], $check_date);
		if(count($prgm) > 30){
			mydie("die: too many offline program (".count($prgm).").\n");
		}else{
			$objProgram->setProgramOffline($this->info["AffId"], $prgm);
			echo "\tSet (".count($prgm).") offline program.\r\n";
		}
	}

	private function key_implode($glue, $array, $key)
	{
		$t = array();
		if (key_exists($key, $array) && !is_array($array[$key]))
			return $array[$key];
		foreach ($array as $v)
		{
			if (is_array($v) && key_exists($key, $v))
			{
				if (is_array($v[$key]))
					$t[] = implode(',', $v[$key]);
				else
					$t[] = $v[$key];
			}
		}
		return implode($glue, $t);
	}
	
	function getProgramDetailsQuick($recheck_ids = array())
	{
		echo "start getProgramDetailsQuick\r\n";		
		$request = array("AffId" => $this->info["AffId"], "method" => "get",);
		list ($page, $items, $total, $arr_prgm, $cnt) = array(0, 50, 0, array(), 0);
		do
		{
			$url = sprintf("http://api.zanox.com/json/2011-03-01/programs?page=%s&connectid=".$this->ConnectId."&items=%s", $page, $items);
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$data = @json_decode($r['content'], true);
			if (empty($total))
				$total = (int)$data['total'];
			echo "\tpage: $page";
			foreach ((array)$data['programItems']['programItem'] as $prgm)
			{
				$strMerID = (int)$prgm['@id'];
				if (!$strMerID)
					continue;
				
				// for recheck not update program today 
				if(count($recheck_ids) && !isset($recheck_ids[$strMerID])) continue;

				$program = array(
						"Name" => addslashes(html_entity_decode($prgm['name'])),
						"AffId" => $this->info["AffId"],
						//"Contacts" => addslashes($Contacts),
						"RankInAff" => round($prgm['adrank']),
						"StatusInAffRemark" => addslashes($prgm['status']),
						"IdInAff" => $strMerID,
						"CookieTime" => intval(@$prgm['returnTimeSales']) / 86400,
						//"EPCDefault" => $EPCDefault,
						"StatusInAff" => ucfirst(addslashes($prgm['status'])),						//'Active','TempOffline','Offline'
						//"Partnership" => 'NoPartnership',											//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
						"Description" => '',
						"Homepage" => addslashes($prgm['url']),
						"TermAndCondition" => '',
						"SEMPolicyExt" => '',
						"CategoryExt" => '',
						//"AffDefaultUrl" => '',
						//"SupportDeepUrl" => 'UNKNOWN',
						"JoinDate" => isset($prgm['startDate']) ? date("Y-m-d H:i:s", strtotime($prgm['startDate'])) : "",
						"TargetCountryExt" => '',
						"DetailPage" => "https://marketplace.zanox.com/zanox/affiliate/".$this->UserId."/".$this->SpaceId."/merchant-profile/$strMerID",
						"LastUpdateTime" => date("Y-m-d H:i:s"),
						//"MobileFriendly" => 'UNKNOWN',
						"LogoUrl" => addslashes($prgm['image']),
				);

				if(isset($prgm['policies']['policy']) && is_array($prgm['policies']['policy']))
					$program['SEMPolicyExt'] = addslashes($this->key_implode(',', $prgm['policies']['policy'], '$'));
				if(isset($prgm['industries']['main']) && is_array($prgm['industries']['main']))
					$mainCate =addslashes($this->key_implode(',', $prgm['industries']['main'], '$'));
				if(isset($prgm['industries']['sub']) && is_array($prgm['industries']['sub']))
					$subCate =addslashes($this->key_implode(',', $prgm['industries']['sub'], '$'));
				if (!empty($subCate))
					$program['CategoryExt'] = $mainCate.'-'.$subCate;
				else 
					$program['CategoryExt'] = $mainCate;
				if(isset($prgm['regions']) && is_array($prgm['regions']))
					$program['TargetCountryExt'] =$this->key_implode(',', $prgm['regions'], 'region');
				if(isset($prgm['terms']))
					$program['TermAndCondition'] = addslashes($prgm['terms']);
				$desc = "";
				if(isset($prgm['description']))
					$desc = $prgm['description'];
				if(isset($prgm['descriptionLocal']))
				{
					if(empty($desc))
						$desc = "\r\r\r\r";
					$desc .= $prgm['descriptionLocal'];
				}
				$program['Description'] = addslashes($desc);

				$arr_prgm[$strMerID] = $program;
				
				$cnt++;
				
				if(count($arr_prgm) >= 100)
				{
					$objProgram = $this->getProgramObj();
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					$arr_prgm = array();
				}
			}
			if(count($arr_prgm) > 0)
			{
				$objProgram = $this->getProgramObj();
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
				$arr_prgm = array();
			}
			$page ++;
		}while ($page < 1000 && $page * $items < $total);
		echo "finish getProgramDetailsQuick($cnt)\r\n";	
		return $cnt;
	}
	
	function getProgramSubInfoQuick()
	{
		echo "start getProgramSubInfoQuick\r\n";
		$objProgram = $this->getProgramObj();
		$sql = "select IdInAff from program where affid = 15 and statusinaff = 'active'";
		$active_p_arr = $objProgram->objMysql->getRows($sql);

		$subinfo = array("pageinfo", "partnership", "supportdeepurl");
		 

        //getstatus  只采集partnership
        if($this->getStatus){
            $subinfo = array("partnership");
        }
		foreach($subinfo as $subactive){
			echo "start getProgramSubInfoQuick: $subactive\r\n";
			$arr_prgm = array();
			if($subactive == 'partnership'){
				$this->getSoapClient(true);
				foreach($active_p_arr as $v){
					$program = array(					
							"AffId" => $this->info["AffId"],					
							"IdInAff" => $v["IdInAff"],					
							"Partnership" => 'NoPartnership',											//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'							
							//"LastUpdateTime" => date("Y-m-d H:i:s"),
					);
					
					$tmp_arr = array();
					$tmp_arr = $this->getProgramPartnershipById($v["IdInAff"]);				
					//$program['CreateDate'] = @count($tmp_arr['CreateDate']) ? addslashes($tmp_arr['CreateDate']) : '';
					$program['Partnership'] = @count($tmp_arr['Partnership']) ? addslashes($tmp_arr['Partnership']) : 'NoPartnership';
					$partnership = @$tmp_arr['Partnership'];
										
					$arr_prgm[$v["IdInAff"]] = $program;
					
					if(count($arr_prgm) > 100)
					{
						$objProgram = $this->getProgramObj();
						$objProgram->updateProgram($this->info["AffId"], $arr_prgm);				
						$arr_prgm = array();
						$this->getSoapClient(true);
					}					
				}
				if(count($arr_prgm))
				{
					$objProgram = $this->getProgramObj();
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
			}
			else if($subactive == 'supportdeepurl'){
				foreach($active_p_arr as $v){
					$program = array(					
							"AffId" => $this->info["AffId"],					
							"IdInAff" => $v["IdInAff"],
							"SupportDeepUrl" => 'UNKNOWN',
							"AffDefaultUrl" => '',						
							"LastUpdateTime" => date("Y-m-d H:i:s"),
					);
					
					if(!isset($this->cache[$v["IdInAff"]]['supportdeepurl'])){						
					
						$tmp_arr = array();
						$tmp_arr = $this->getProgramDeepLinkById($v["IdInAff"]);
						
						if(count($tmp_arr)){
							$this->cache[$v["IdInAff"]]['supportdeepurl'] = "1";
							//$program['SupportDeepUrl'] = @count($tmp_arr['SupportDeepUrl']) ? addslashes($tmp_arr['SupportDeepUrl']) : 'UNKNOWN';
							//$program['AffDefaultUrl'] = @count($tmp_arr['AffDefaultUrl']) ? addslashes($tmp_arr['AffDefaultUrl']) : '';	
							if(isset($tmp_arr['SupportDeepUrl'])){
								$program['SupportDeepUrl'] = addslashes($tmp_arr['SupportDeepUrl']);
							}else{
								unset($program['SupportDeepUrl']);
							}
							if(isset($tmp_arr['AffDefaultUrl'])){
								$program['AffDefaultUrl'] = addslashes($tmp_arr['AffDefaultUrl']);
							}else{
								unset($program['AffDefaultUrl']);
							}
							
							$arr_prgm[$v["IdInAff"]] = $program;						
							
							if(count($arr_prgm) > 100)
							{
								$objProgram = $this->getProgramObj();
								$objProgram->updateProgram($this->info["AffId"], $arr_prgm);				
								$arr_prgm = array();
							}
						}
					}				
				}
				if(count($arr_prgm))
				{
					$objProgram = $this->getProgramObj();
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
			}
			else if($subactive == 'pageinfo'){
				$request = array("AffId" => $this->info["AffId"], "method" => "get",);
				$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info,1,false);
				
		        foreach($active_p_arr as $v){
					$program = array(					
							"AffId" => $this->info["AffId"],					
							"IdInAff" => $v["IdInAff"],	
							"MobileFriendly" => 'UNKNOWN',							
							"LastUpdateTime" => date("Y-m-d H:i:s"),
					);
					
					//$commissionDetailUrl = "https://marketplace.zanox.com/zanox/affiliate/".$this->UserId."/".$this->SpaceId."/merchant-profile/{$v["IdInAff"]}".'/commission-groups';
					$commissionDetailUrl = "https://marketplace.zanox.com/zanox/affiliate/".$this->UserId."/".$this->SpaceId."/merchant-profile/{$v["IdInAff"]}/xhr-commission-group-search/";
					//$CommissionExt = $this->getProgramCommissionExt($commissionDetailUrl);
					if(!isset($this->cache[$v["IdInAff"]]['CommissionExt']) || $this->cache[$v["IdInAff"]]['CommissionExt'] != '2'){
						//$r = $this->oLinkFeed->GetHttpResult("https://marketplace.zanox.com/zanox/affiliate/".$this->UserId."/".$this->SpaceId."/merchant-profile/{$v["IdInAff"]}", $request);
						$r = $this->oLinkFeed->GetHttpResult("https://marketplace.zanox.com/zanox/affiliate/".$this->UserId."/".$this->SpaceId."/merchant-profile/{$v["IdInAff"]}/commission-groups/", $request);
						if($r['code'] == 200){
							$content = $r['content'];
							$comm_r = $this->oLinkFeed->ParseStringBy2Tag($content, 'Standard Commission Rate</h4>', '</div>');
							if ($comm_r){
								if (stripos($comm_r, 'No commission details') != false)
									$program['CommissionExt'] = '0';
								else{
									$comm_r = str_replace('</li><li>', ',', $comm_r);
									$program['CommissionExt'] = addslashes(trim(strip_tags($comm_r)));
								}
								$this->cache[$v["IdInAff"]]['CommissionExt'] = 2;
							}
							//preg_match_all("/Sale.*?<\/td>.*?<\/td>.*?<td[^>]*>(.*?)<\/td>.*?<\/tr>/s", $content, $m);
							/* preg_match('@<div class="cgTable trackingCategoryTable">.*?(<tr[\s\S]*>.*?</tr>).*?</div>@is', $content, $m);
							if(count($m)){
								preg_match_all('/<tr[^>]*>\s*<td[^>]*>.*?<\/td>\s*<td[^>]*>.*?<\/td>\s*<td[^>]*>(.*?)<\/td>\s*<\/tr>/s',trim($m[1]),$m1);
								$pregStr = '';
								foreach ($m1[1] as $m1v){
								    $pregStr .= trim(strip_tags($m1v)).',';
								}
								if($pregStr){
									$program['CommissionExt'] = addslashes($pregStr);
									$this->cache[$v["IdInAff"]]['CommissionExt'] = 2;
								}
							} */
							//if(count($m[1])){
							//	$tmp_comm = array();
							//	foreach($m[1] as $vm){
							//		$tmp_comm[] = strip_tags($vm);
							//	}
							//	$program['CommissionExt'] = addslashes(trim(strip_tags(implode(' ',$tmp_comm))));
							//}					
						}
					} 
					if(!isset($this->cache[$v["IdInAff"]]['MobileFriendly'])){
						$r = $this->oLinkFeed->GetHttpResult("https://marketplace.zanox.com/zanox/affiliate/".$this->UserId."/".$this->SpaceId."/merchant-profile/{$v["IdInAff"]}", $request);
						if($r['code'] == 200){	
							$content = $r['content'];
							$this->cache[$v["IdInAff"]]['MobileFriendly'] = "1";
							if (preg_match('@<h4>Optimized for Mobile</h4>\s+<div.*?>\s+(.*?)\s+</div>@', $content, $g))
							{
								if (strtoupper(trim($g[1])) == 'YES')
									$program['MobileFriendly'] = 'YES';
								else
									$program['MobileFriendly'] = 'NO';
							}
						}else{
							unset($program['MobileFriendly']);
						}
					}
					//echo  "\tdo idInAff:".$v["IdInAff"]."-index:".count($arr_prgm);
					$arr_prgm[$v["IdInAff"]] = $program;
					 
					if(count($arr_prgm) > 100)
					{
						$objProgram = $this->getProgramObj();
						$objProgram->updateProgram($this->info["AffId"], $arr_prgm);				
						$arr_prgm = array();
					}					
				}
				if(count($arr_prgm))
				{
					$objProgram = $this->getProgramObj();
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);					
					$arr_prgm = array();
				}
			}
		}
		echo "finish getProgramSubInfoQuick\r\n";
	}

	function getProgramDetails($recheck_ids = array())
	{
		$request = array("AffId" => $this->info["AffId"], "method" => "get",);
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info,1,false);
		
		list ($page, $items, $total, $arr_prgm, $cnt) = array(0, 50, 0, array(), 0);
		do
		{
			$url = sprintf("http://api.zanox.com/json/2011-03-01/programs?page=%s&connectid=".$this->ConnectId."&items=%s", $page, $items);
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$data = @json_decode($r['content'], true);
			if (empty($total))
				$total = (int)$data['total'];
			foreach ((array)$data['programItems']['programItem'] as $prgm)
			{
				$strMerID = (int)$prgm['@id'];
				if (!$strMerID)
					continue;
				
				// for recheck not update program today 
				if(count($recheck_ids) && !isset($recheck_ids[$strMerID])) continue;

				$program = array(
						"Name" => addslashes(html_entity_decode($prgm['name'])),
						"AffId" => $this->info["AffId"],
						//"Contacts" => addslashes($Contacts),
						"RankInAff" => round($prgm['adrank']),
						"StatusInAffRemark" => addslashes($prgm['status']),
						"IdInAff" => $strMerID,
						"CookieTime" => intval($prgm['returnTimeSales']) / 86400,
						//"EPCDefault" => $EPCDefault,
						"StatusInAff" => ucfirst(addslashes($prgm['status'])),						//'Active','TempOffline','Offline'
						"Partnership" => 'NoPartnership',											//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
						"Description" => '',
						"Homepage" => addslashes($prgm['url']),
						"TermAndCondition" => '',
						"SEMPolicyExt" => '',
						"CategoryExt" => '',
						"AffDefaultUrl" => '',
						"SupportDeepUrl" => 'UNKNOWN',
						"JoinDate" => isset($prgm['startDate']) ? date("Y-m-d H:i:s", strtotime($prgm['startDate'])) : "",
						"TargetCountryExt" => '',
						"DetailPage" => "https://marketplace.zanox.com/zanox/affiliate/".$this->UserId."/".$this->SpaceId."/merchant-profile/$strMerID",
						"LastUpdateTime" => date("Y-m-d H:i:s"),
						"MobileFriendly" => 'UNKNOWN',
				);

				$commissionDetailUrl = $program['DetailPage'].'/commission-groups';
				$CommissionExt = $this->getProgramCommissionExt($commissionDetailUrl);
				$program['CommissionExt'] = addslashes($CommissionExt);
												
				if(isset($prgm['policies']['policy']) && is_array($prgm['policies']['policy']))
					$program['SEMPolicyExt'] = addslashes($this->key_implode(',', $prgm['policies']['policy'], '$'));
				if(isset($prgm['categories'][0]['category']) && is_array($prgm['categories'][0]['category']))
					$program['CategoryExt'] = addslashes($this->key_implode(',', $prgm['categories'][0]['category'], '$'));
				if(isset($prgm['regions']) && is_array($prgm['regions']))
					$program['TargetCountryExt'] =$this->key_implode(',', $prgm['regions'], 'region');
				if(isset($prgm['terms']))
					$program['TermAndCondition'] = addslashes($prgm['terms']);
				$desc = "";
				if(isset($prgm['description']))
					$desc = $prgm['description'];
				if(isset($prgm['descriptionLocal']))
				{
					if(empty($desc))
						$desc = "\r\r\r\r";
					$desc .= $prgm['descriptionLocal'];
				}
				$program['Description'] = addslashes($desc);

				$status = ucfirst($prgm['status']);
				$partnership = "";
				if($status == "Active"){
					$tmp_arr = array();
					$tmp_arr = $this->getProgramPartnershipById($strMerID);				
					//$program['CreateDate'] = @count($tmp_arr['CreateDate']) ? addslashes($tmp_arr['CreateDate']) : '';
					$program['Partnership'] = @count($tmp_arr['Partnership']) ? addslashes($tmp_arr['Partnership']) : 'NoPartnership';
					$partnership = @$tmp_arr['Partnership'];
				}				
								
				if($status == "Active" && $partnership == "Active"){
					$tmp_arr = array();
					$tmp_arr = $this->getProgramDeepLinkById($strMerID);				
					//$program['SupportDeepUrl'] = @count($tmp_arr['SupportDeepUrl']) ? addslashes($tmp_arr['SupportDeepUrl']) : 'UNKNOWN';
					//$program['AffDefaultUrl'] = @count($tmp_arr['AffDefaultUrl']) ? addslashes($tmp_arr['AffDefaultUrl']) : '';			
					if(isset($tmp_arr['SupportDeepUrl'])){
						$program['SupportDeepUrl'] = addslashes($tmp_arr['SupportDeepUrl']);
					}else{
						unset($program['SupportDeepUrl']);
					}
					if(isset($tmp_arr['AffDefaultUrl'])){
						$program['AffDefaultUrl'] = addslashes($tmp_arr['AffDefaultUrl']);
					}else{
						unset($program['AffDefaultUrl']);
					}
					
					if(!isset($this->cache[$strMerID]['DetailsMobileFriendly'])){				
						$r = $this->oLinkFeed->GetHttpResult($program['DetailPage'], $request);
						if($r['code'] == 200){
							$content = $r['content'];
							$this->cache[$strMerID]['DetailsMobileFriendly'] = "1";
							if (preg_match('@<h4>Optimized for Mobile</h4>\s+<div.*?>\s+(.*?)\s+</div>@', $content, $g))
							{
								if (strtoupper(trim($g[1])) == 'YES')
									$program['MobileFriendly'] = 'YES';
								else
									$program['MobileFriendly'] = 'NO';
							}
						}else{
							unset($program['MobileFriendly']);
						}
					}
				}
				$arr_prgm[$strMerID] = $program;
				
				$cnt++;
			}
			if(count($arr_prgm) > 0)
			{
				$objProgram = $this->getProgramObj();
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
				$arr_prgm = array();
			}
			$page ++;
		}while ($page < 1000 && $page * $items < $total);
		return $cnt;
	}
	
	function getProgramDetailsById($recheck_ids = array())
	{
		echo "start getProgramDetailsById ". count($recheck_ids) ."\r\n";		
		$cnt = 0;
		$request = array("AffId" => $this->info["AffId"], "method" => "get",);
		$arr_prgm = array();
		foreach($recheck_ids as $idinaff){
			$url = "http://api.zanox.com/json/2011-03-01/programs/program/$idinaff?connectid=".$this->ConnectId;
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$data = @json_decode($r['content'], true);
			
			$prgm = (array)$data['programItem'];
			
			$strMerID = (int)$prgm['@id'];
			if ($strMerID != $idinaff) continue;			
			
			$program = array(
					"Name" => addslashes(html_entity_decode($prgm['name'])),
					"AffId" => $this->info["AffId"],
					//"Contacts" => addslashes($Contacts),
					"RankInAff" => round($prgm['adrank']),
					"StatusInAffRemark" => addslashes($prgm['status']),
					"IdInAff" => $strMerID,
					"CookieTime" => intval(@$prgm['returnTimeSales']) / 86400,
					//"EPCDefault" => $EPCDefault,
					"StatusInAff" => ucfirst(addslashes($prgm['status'])),						//'Active','TempOffline','Offline'
					//"Partnership" => 'NoPartnership',											//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
					"Description" => '',
					"Homepage" => addslashes($prgm['url']),
					"TermAndCondition" => '',
					"SEMPolicyExt" => '',
					"CategoryExt" => '',
					//"AffDefaultUrl" => '',
					//"SupportDeepUrl" => 'UNKNOWN',
					"JoinDate" => isset($prgm['startDate']) ? date("Y-m-d H:i:s", strtotime($prgm['startDate'])) : "",
					"TargetCountryExt" => '',
					"DetailPage" => "https://marketplace.zanox.com/zanox/affiliate/".$this->UserId."/".$this->SpaceId."/merchant-profile/$strMerID",
					"LastUpdateTime" => date("Y-m-d H:i:s"),
					//"MobileFriendly" => 'UNKNOWN',
			);
											
			if(isset($prgm['policies']['policy']) && is_array($prgm['policies']['policy']))
				$program['SEMPolicyExt'] = addslashes($this->key_implode(',', $prgm['policies']['policy'], '$'));
			if(isset($prgm['categories'][0]['category']) && is_array($prgm['categories'][0]['category']))
				$program['CategoryExt'] = addslashes($this->key_implode(',', $prgm['categories'][0]['category'], '$'));
			if(isset($prgm['regions']) && is_array($prgm['regions']))
				$program['TargetCountryExt'] =$this->key_implode(',', $prgm['regions'], 'region');
			if(isset($prgm['terms']))
				$program['TermAndCondition'] = addslashes($prgm['terms']);
			$desc = "";
			if(isset($prgm['description']))
				$desc = $prgm['description'];
			if(isset($prgm['descriptionLocal']))
			{
				if(empty($desc))
					$desc = "\r\r\r\r";
				$desc .= $prgm['descriptionLocal'];
			}
			$program['Description'] = addslashes($desc);

			$arr_prgm[$strMerID] = $program;
			
			$cnt++;
			
			if(count($arr_prgm) >= 100)
			{
				$objProgram = $this->getProgramObj();
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);					
				$arr_prgm = array();
			}						
		}
		if(count($arr_prgm) > 0)
		{
			$objProgram = $this->getProgramObj();
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);			
			$arr_prgm = array();
		}
		echo "finish getProgramDetailsById($cnt)\r\n";		
	}

	function getProgramCommissionExt($url){
		$request = array("AffId" => $this->info["AffId"], "method" => "get",);
		
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$body = $r['content'];

		$objDomTools = new DomTools($body);
		$objDomTools->select('div #merchantKeyFigures');
		$m = $objDomTools->get();
		
		$CommissionExt = isset($m[0]['Content']) ? $m[0]['Content'] : '';
		return $CommissionExt;
	}

	function getProgramPartnership($client,$objProgram){
		$cnt = 0;
		//getProgramApplications ($adspaceId = NULL, $programId = NULL, $status = NULL, $page = 0, $items = 10 );
		//"open", "confirmed", "rejected", "deferred", "waiting", "blocked", "terminated", "canceled", "called", "declined", "deleted"
		$adspaceId_arr = array(797864,1398563,1197710,2073904);
		$ignore_prgm_arr = array();
		$inactive_prgm_arr = array();
		foreach($adspaceId_arr as $adspaceId){
			$pagesize = 50;
			$return_obj = $client->getProgramApplications ($adspaceId, '', '', 0, 1);
			$total = $return_obj->total;
			$total_page = $total / $pagesize;
			$arr_prgm = array();
			for ($page = 0; $page < $total_page; $page++) {
				$return_obj = $client->getProgramApplications ($adspaceId, '', '', $page, $pagesize);
				//echo $status.":".$return_obj->total."<br>";
				//print_r($return_obj);
				foreach ($return_obj->programApplicationItems->programApplicationItem as $prgm) {
					if(isset($prgm->program)){
						$strMerID = intval($prgm->program->id);
						$CreateDate = isset($prgm->createDate) ? date("Y-m-d H:i:s", strtotime($prgm->createDate)) : "";
						/*$StatusInAff = "Offline";
						if($prgm->program->active == 1){
							$StatusInAff = "Active";
						}*/
						$Partnership = "";
						if($prgm->status == "confirmed"){
							$Partnership = "Active";
						}elseif($prgm->status == "open"){
							$Partnership = "NoPartnership";
						}elseif($prgm->status == "waiting"){
							$Partnership = "Pending";
						}elseif($prgm->status == "deferred"){
							$Partnership = "Expired";
						}elseif($prgm->status == "rejected"){
							$Partnership = "Declined";
						}elseif(in_array($prgm->status, array("closed", "blocked", "terminated", "canceled", "called", "deleted"))){
							$Partnership = "NoPartnership";
						}else{
							continue;
						}
						//check DE prgm 
						if(isset($ignore_prgm_arr[$strMerID])){
							continue;
						}elseif($Partnership != "Active"){
							//$ignore_prgm_arr[$strMerID] = 1;
							$inactive_prgm_arr[$strMerID] = array(
										"Name" => addslashes(html_entity_decode($prgm->program->_)),
										"AffId" => $this->info["AffId"],
										"IdInAff" => $strMerID,
										"Partnership" => $Partnership,
										//"StatusInAff" => $StatusInAff,
										"CreateDate" => $CreateDate,
										"LastUpdateTime" => date("Y-m-d H:i:s"),
									);
						}else{
							$ignore_prgm_arr[$strMerID] = 1;
							$arr_prgm[$strMerID] = array(
										"Name" => addslashes(html_entity_decode($prgm->program->_)),
										"AffId" => $this->info["AffId"],
										"IdInAff" => $strMerID,
										"Partnership" => $Partnership,
										//"StatusInAff" => $StatusInAff,
										"CreateDate" => $CreateDate,
										"LastUpdateTime" => date("Y-m-d H:i:s"),
									);
						}
						//print_r($arr_prgm);
						if(count($arr_prgm) >= 100){
							$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
							$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
							$arr_prgm = array();
						}
						$cnt++;
					}
				}
			}
			if(count($arr_prgm)){
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
				$arr_prgm = array();
			}
		}
		foreach($ignore_prgm_arr as $id => $val){
			if(isset($inactive_prgm_arr[$id])){
				unset($inactive_prgm_arr[$id]);
			}
		}
		if(count($inactive_prgm_arr)){
			$objProgram->updateProgram($this->info["AffId"], $inactive_prgm_arr);
			$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $inactive_prgm_arr);
			$arr_prgm = array();
		}
		return $cnt;
	}
	
	function getProgramDeepLinkById($idinaff)
	{
		$return_arr = array();
		$request = array("AffId" => $this->info["AffId"], "method" => "get");
		$url = sprintf("https://api.zanox.com/json/2011-03-01/admedia?program=%s&connectid=".$this->ConnectId."&admediumtype=text", $idinaff);			
		if(!isset($this->cache[$idinaff]['DetailsMobileFriendly'])){
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			if($r['code'] == 200){
				$data = @json_decode($r['content'], true);
				
				
				if((int)$data['total'] > 1){
					foreach ((array)$data['admediumItems']['admediumItem'] as $prgm)
					{
						//print_r($prgm);
						$strMerID = (int)$prgm['program']['@id'];
						if (!$strMerID || $strMerID != $idinaff)
							continue;
						
						if(isset($prgm['trackingLinks']['trackingLink'])){
							foreach($prgm['trackingLinks']['trackingLink'] as $v) {
								if(isset($v['ppc']) && !empty($v['ppc'])){
									$SupportDeepurl = 'YES';
									$TrackingLink = $v['ppc'];
									$TrackingLink = substr($TrackingLink, 0, stripos($TrackingLink, "&zpar9"));
									$return_arr = array("SupportDeepUrl" => $SupportDeepurl,
														"AffDefaultUrl" => addslashes($TrackingLink)
														);
									break 2;
								}
							}
						}					
					}
				}
			}	
		}
		return $return_arr;
	}
	
	function getProgramPartnershipById($idinaff)
	{
		$return_arr = array();
		$request = array("AffId" => $this->info["AffId"], "method" => "get");

		$client = $this->getSoapClient();
		//getProgramApplications ($adspaceId = NULL, $programId = NULL, $status = NULL, $page = 0, $items = 10 );
		try{
			$return_obj = $client->getProgramApplications(null, $idinaff, null, 0, 10);
			//echo $status.":".$return_obj->total."<br>";
			//print_r($return_obj);exit;
			if(isset($return_obj->total) && $return_obj->total > 0){
				foreach ($return_obj->programApplicationItems->programApplicationItem as $prgm) {
					if(isset($prgm->program)){
						$strMerID = intval($prgm->program->id);
						if($strMerID != $idinaff) continue;
						
						$CreateDate = isset($prgm->createDate) ? date("Y-m-d H:i:s", strtotime($prgm->createDate)) : "";
						
						$Partnership = "";
						$StatusInAffRemark = "";
						if($prgm->status == "confirmed"){
							$Partnership = "Active";
						}elseif($prgm->status == "open"){
							$Partnership = "NoPartnership";
						}elseif($prgm->status == "waiting"){
							$Partnership = "Pending";
						}elseif($prgm->status == "deferred"){
							$Partnership = "Expired";
						}elseif($prgm->status == "rejected"){
							$Partnership = "Declined";
						}elseif(in_array($prgm->status, array("closed", "blocked", "terminated", "canceled", "called", "deleted"))){
							$Partnership = "NoPartnership";
						}else{
							$StatusInAffRemark = $prgm->status;
							$Partnership = "NoPartnership";
						}
															
						$return_arr = array("CreateDate" => $CreateDate, "Partnership" => $Partnership);
						
						if($Partnership == "Active"){
							break;
						}
					}
				}
			}
		}		
		catch (Exception $e) {
			echo $e->getMessage()."\n";
		}
		return $return_arr;
	}

	function getProgramDeepLink($client,$objProgram)
	{
		$cnt = 0;
		//getAdmedia ($programId = NULL, region = NULL, format = NULL, purpose = startpage, partnership = NULL, admediumtype = NULL, category = 0, adspace = 0, $page = 0, $items = 10);
		//purpose = "startpage", "productdeeplink", "categorydeeplink", "searchdeeplink".
		$prgm_mark = array();
		$adspaceId_arr = array(797864,1398563,1197710,2073904);
		$pagesize = 50;
		$return_obj = $client->GetAdmedia(NULL, NULL, NULL, NULL, NULL, "text", NULL, NULL, 0, 1);
		$total = $return_obj->total;
		$total_page = $total / $pagesize;
		$arr_prgm = array();
		for ($page = 0; $page < $total_page; $page++) 
		{
			$return_obj = $client->GetAdmedia(NULL, NULL, NULL, NULL, NULL, "text", NULL, NULL, $page, $pagesize);
			echo sprintf("call GetAdmedia %s/%s\n", $page, $total_page);

			foreach ($return_obj->admediumItems->admediumItem as $prgm)
			{
				if(isset($prgm->program))
					$strMerID = intval($prgm->program->id);
				if(isset($prgm_mark[$strMerID]))
					continue;
				$SupportDeepurl = false;
				if(isset($prgm->trackingLinks->trackingLink)){
					foreach($prgm->trackingLinks->trackingLink as $v) {
						if(isset($v->ppc) && !empty($v->ppc)){
							$SupportDeepurl = 'YES';
							$TrackingLink = $v->ppc;
							$TrackingLink = substr($TrackingLink, 0, stripos($TrackingLink, "&zpar9"));
							break;
						}
					}
				}
				if($SupportDeepurl == 'YES')
				{
					$prgm_mark[$strMerID] = 1;
					$arr_prgm[$strMerID] = array(
						"AffId" => $this->info["AffId"],
						"IdInAff" => $strMerID,
						//"LastUpdateTime" => date("Y-m-d H:i:s"),
						"SupportDeepUrl" => $SupportDeepurl,
						"AffDefaultUrl" => addslashes($TrackingLink)
					);
					if(count($arr_prgm) >= 100)
					{
						$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
						$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
						$arr_prgm = array();
					}
				}
			}
		}
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
			$arr_prgm = array();
		}
	}
}

