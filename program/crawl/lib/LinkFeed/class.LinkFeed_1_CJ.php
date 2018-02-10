<?php

require_once 'text_parse_helper.php';
require_once 'xml2array.php';

class LinkFeed_1_CJ
{
	var $CJ_API_KEY, $CJ_API_PID, $CJ_API_CID;
	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->full_crawl = isset($oLinkFeed->full_crawl) ? $oLinkFeed->full_crawl : false;
		$this->getStatus = false;
		$this->file = "programlog_{$aff_id}_" . date("Ymd_His") . ".csv";
		$this->soapClient = null;
		$this->XML2Array_max_try = 3;
		
		if(SID == 'bdg02'){
			$this->CJ_API_KEY = '00973c68e5c0d8ac6eba2706f9e81dfb02c087749be2d9380dd706ad63bda85326376fd3eec5cbce3735d1df7bebcac234ac52c37fa0cc4fd3e284a6515ca01e7d/469ac94e19ce0e12538dcccff6f1a8320cb83054667f8a8fcb872e839613735d74bc62ed454aa7e6372c8d681e627729831a383a09f7aac1cca2d04b9ee26d81';
			$this->CJ_API_PID = '8030429';
			$this->CJ_API_CID = '4708894';
			$this->UserID = '4217409'; 
		}else{
			$this->CJ_API_KEY = '0088103a9fe3910c0531ad2c35754df6e93ca53cde6ecc34b103365e4aece59dc4de98ec4364acc358eb2663bf3e042195e023511689914bba4027820a00cc9a5f/183dabf4287b030539e07c6bd9c656231fa6e0bd5048196b200363b13d9b7b0c00565d9a32241dd70957933821acdc31849f518a9dcaf1694a639847da470c21';
			$this->CJ_API_PID = '8229378';
			$this->CJ_API_CID = '4854984';
			$this->UserID = '4333835';
			$this->FID = '23495601';
		}
	}

	function GetStatus()
	{
		$this->getStatus = true;
		$this->GetProgramFromAff();
	}

	function GetAllLinksByAffId()
	{
//        $url = "https://members.cj.com/member/publisher/2149839/ads.json?page=0&relationshipStatus=joined&isEmpty=false&pageSize=1000&sortColumn=advertiserName&sortDescending=true";
//
//        $this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);
//        $request = array("AffId" => $this->info["AffId"], "method" => "get", "addheader" => array(
//            'X-Requested-With:XMLHttpRequest',
//            )
//        );
//        https://members.cj.com/member/publisher/2149839/export/links.xml?page=0&relationshipStatus=joined&isEmpty=false
	    $check_date = date('Y-m-d H:i:s');
		$link_mer_ids = array();
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "addheader" => array(sprintf('authorization: %s', $this->CJ_API_KEY)),);
		$url = sprintf("https://linksearch.api.cj.com/v2/link-search?website-id=%s&page-number=%s&records-per-page=%s&advertiser-ids=joined", $this->CJ_API_PID, 1, 1);
		
		$try_no = 1;
		while ($try_no) {
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$result = $this->xml_parser($r['content']);
			if ($result) {
				break;
			}
			if ($try_no > $this->XML2Array_max_try) {
				mydie('API XML format error in ' . __FILE__ . ' on line ' . __LINE__);
			}
			$try_no ++;
		}
		
//        print_r($result);die;
		$totalpage = $result['links']['@attributes']['total-matched'];
		if(!$totalpage)
			mydie('error:get data failed');
		$totalpage = ceil($totalpage/100);
		for($i=1;$i<=$totalpage;$i++){
			$url = sprintf("https://linksearch.api.cj.com/v2/link-search?website-id=%s&page-number=%s&records-per-page=%s&advertiser-ids=joined", $this->CJ_API_PID, $i, 100);
			
			$try_no = 1;
			while ($try_no) {
				$r = $this->oLinkFeed->GetHttpResult($url, $request);
				$result = $this->xml_parser($r['content']);
				if ($result) {
					break;
				}
				if ($try_no > $this->XML2Array_max_try) {
					mydie('API XML format error in ' . __FILE__ . ' on line ' . __LINE__);
				}
				$try_no ++;
			}
			
			if (empty($result) || !isset($result['links']['link']))
				continue;
			//print_r($result);exit;
			foreach($result['links']['link'] as $data){
			    
			    if($data['promotion-type'] == 'Coupon' || $data['promotion-type'] == 'Sale/Discount' || $data['promotion-type'] == 'Free Shipping'){
			        continue;
			    }
			    
				$link['LinkPromoType'] = 'link';
				$link['AffId'] = $this->info["AffId"];
				$link['AffMerchantId']=$data['advertiser-id'];
				$link['AffLinkId'] = $data['link-id'];

				$link['LinkStartDate'] = !empty($data['promotion-start-date']) ? date('Y-m-d H:i:s',strtotime($data['promotion-start-date'])):'';
				$link['LinkEndDate'] = !empty($data['promotion-end-date'])?date('Y-m-d H:i:s',strtotime($data['promotion-end-date'])):'';
				$link['LinkName'] = $data['link-name']?$data['link-name']:' ';

				if (!empty($data['link-code-html'])) {
					preg_match('/<a href="(.*?)"/',$data['link-code-html'],$linkurl);
					preg_match('/<img src="(.*?)"/',$data['link-code-html'],$imgurl);
					$link['LinkHtmlCode'] = $data['link-code-html'];
				}else {
					$link['LinkHtmlCode'] = '';
				}
				
				if(isset($data['clickUrl']) && $data['clickUrl']){
					$link['LinkAffUrl'] = $data['clickUrl'];
				}elseif(isset($linkurl[1]) && $linkurl[1]){
					$link['LinkAffUrl'] = $linkurl[1];
				}else{
					//$link['LinkAffUrl']='';
					continue;
				}
				
				if(isset($data['destination']) && $data['destination'])
					$link['LinkOriginalUrl'] = $data['destination'];

				
				$link['LinkImageUrl'] = isset($imgurl[1])?$imgurl[1]:'';
				$link['LastUpdateTime'] = date('Y-m-d H:i:s');
				$link['LinkDesc'] = $data['description'];

				$link['LinkCode'] = $data['coupon-code'] ? $data['coupon-code'] : '';
				if($link['LinkCode']){
					$link['LinkPromoType'] = 'coupon';
				}else{
					$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
					
					$link['LinkDesc'] = empty($link['LinkDesc']) ? '' : $link['LinkDesc'];
					$link['LinkName'] = empty($link['LinkName']) ? '' : $link['LinkName'];
					$code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc'] . '|' . $link['LinkHtmlCode']);
					if (!empty($code)) {
						$link['LinkCode'] = $code;
						$link['LinkPromoType'] = 'coupon';
					}
				}
				$link['LinkCode'] = check_linkcode_exclude_sym($link['LinkCode']);
				if($data['link-type']=='Banner'){
				    $link['LinkPromoType'] = 'banner';
				}
				if(!$link['AffMerchantId'] || !$link['AffLinkId'] || !$link['LinkAffUrl'])
					continue;
				$link['DataSource'] = 4;
				$link['Type'] = 'link';
				
				$link['IsDeepLink'] = 'UNKNOWN';
				if(stripos($link['LinkName'],'deep link') !== false|| stripos($link['LinkName'],'deeplink') !== false|| stripos($link['LinkName'],'redirect')!== false){
				    $link['IsDeepLink'] = 'YES';
				}
				
				$arr_return['AffectedCount']++;
				$links[] = $link;
				
			}
			if (empty($links)){
				$links = array();
				continue;
			}
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
			$links = array();
		}
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link');
		
		/*
		 * get deep link sign
		 */
		/* $this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);
		if(count($link_mer_ids)){
			$mids_list = array();
			foreach($link_mer_ids as $mids => $linkids_arr){        		
				$mids_list[] = $mids;
				
				if(count($mids_list) > 100){
					$page = 0;
					$url = "https://members.cj.com/member/publisher/".$this->CJ_API_PID."/ads.json?page=$page&advertiserIds=".implode('%2C',$mids_list)."&linksRedirect=true&isEmpty=false&pageSize=200";
					echo "$url\n";
					$r = $this->oLinkFeed->GetHttpResult($url, $request);
					$data = @json_decode($r["content"], true);
					if (empty($data) || !is_array($data) || empty($data['totalRecords']) || empty($data['records']) || !is_array($data['records']))
						break;
					$count = $data['totalRecords'];
					foreach ($data['records'] as $v) {
						
						
					}
				}
			}
			
			$url = "https://members.cj.com/member/publisher/".$this->CJ_API_PID."/ads.json?page=0&advertiserIds=&relationshipStatus=joined&linksRedirect=true&isEmpty=false&pageSize=200";   		
			
		} */


		return $arr_return;

	}

	function getCouponFeed()
	{
	    $check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
		
		$feedType = array('Coupon'=>'coupon','Sale/Discount'=>'DEAL','Free Shipping'=>'free shipping');
		$xml2arr = new XML2Array();
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "","addheader" => array(sprintf('authorization: %s', $this->CJ_API_KEY)));
		
		$recordsReturned = 10;
		
		foreach ($feedType as $typeKey=>$valueType){
		    $pageNumber = 0;
		    do{
		        $pageNumber ++;
		        $url = "https://link-search.api.cj.com/v2/link-search?website-id=$this->CJ_API_PID&promotion-type=".urlencode($typeKey)."&advertiser-ids=joined&page-number=$pageNumber";
		        $reponseData = $this->oLinkFeed->GetHttpResult($url, $request);
		        $content = $reponseData['content'];
		        if (empty($content))
		            continue;
		        $data = $xml2arr->createArray($content);
		        
		        $totalMatched = strip_tags($data['cj-api']['links']['@attributes']['total-matched']);    //2212
		    
		        //print_r($data);exit;
		        $links = array();
		        foreach ($data['cj-api']['links']['link'] as $v){
		            
		            $couponcode = $v['coupon-code'];
		            if($couponcode == ''){
		                $code = get_linkcode_by_text($v['description']);
		                if (!empty($code))
		                {
		                    $couponcode = $code;
		                }
		            }
		            
		            $link = array(
		                "AffId" => $this->info["AffId"],
		                "AffMerchantId" => $v['advertiser-id'],
		                "AffLinkId" => $v['link-id'],
		                "LinkName" => $v['link-name'],
		                "LinkDesc" => $v['description'],
		                "LinkStartDate" => isset($v['promotion-start-date']) && !empty($v['promotion-start-date']) ? date('Y-m-d H:i:s',strtotime($v['promotion-start-date'])) : '',
		                "LinkEndDate" => isset($v['promotion-end-date']) && !empty($v['promotion-end-date']) ? date('Y-m-d H:i:s',strtotime($v['promotion-end-date'])) : '',
		                "LinkPromoType" => isset($feedType[$v['promotion-type']]) ? $feedType[$v['promotion-type']] : 'N/A',
		                "LinkHtmlCode" => $v['link-code-html'],
		                "LinkCode" => $couponcode,
		                "LinkOriginalUrl" => (!empty($v['destination']))?$v['destination']:'',
		                "LinkImageUrl" => '',
		                "LinkAffUrl" => isset($v['clickUrl']) ? $v['clickUrl'] : '',
		                "DataSource" => 4,
		                "IsDeepLink" => 'UNKNOWN',
		                "Type"       => 'promotion',
		                "Language"   => $v['language']
		            );
		            
		            if(!$link['LinkAffUrl'] || !$link['LinkName'] || !$link['AffLinkId']) continue;
		            $links[] = $link;
		            $arr_return["AffectedCount"]++;
		        }
		    
		        if (count($links) > 0)
		            $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		    
		        echo sprintf("page:%s, %s links(s) found. \n", $pageNumber, count($links));
		         
		    }while ($recordsReturned * $pageNumber < $totalMatched);
		}
		
		
		/*$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);
		$nNumPerPage = 100;
		$bHasNextPage = true;
		$nPageNo = 0;
		do {
			$links = array();
			$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "",);
			$url = "https://members.cj.com/member/publisher/".$this->CJ_API_CID."/ads.json?page={$nPageNo}&promotionTypes=1%2C2%2C3%2C4%2C5%2C7&isEmpty=false&pageSize={$nNumPerPage}&sortColumn=dateLastModified&sortDescending=true";
			echo "$url\n";
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$data = @json_decode($r["content"], true);
			if (empty($data) || !is_array($data) || empty($data['totalRecords']) || empty($data['records']) || !is_array($data['records']))
				break;
			$count = $data['totalRecords'];
			foreach ($data['records'] as $v) {
				$link = array(
					"AffId" => $this->info["AffId"],
					"AffMerchantId" => $v['cid'],
					"AffLinkId" => $v['id'],
					"LinkName" => $v['name'],
					"LinkDesc" => $v['description'],
					"LinkStartDate" => '0000-00-00 00:00:00',
					"LinkEndDate" => '0000-00-00 00:00:00',
					"LinkPromoType" => 'DEAL',
					"LinkHtmlCode" => '',
					"LinkCode" => '',
					"LinkOriginalUrl" => '',
					"LinkImageUrl" => '',
					"LinkAffUrl" => '',
					"DataSource" => 4,
				    "IsDeepLink" => 'UNKNOWN',
				    "Type"       => 'promotion'
				);
				
				if (!empty($v['promotionalStartDate']) && !empty($v['promotionalStartDate']['millisecDate'])) {
					$date = (int)($v['promotionalStartDate']['millisecDate'] / 1000);
					if ($date > 946713600)
						$link['LinkStartDate'] = date('Y-m-d H:i:s', $date);
				}
				if (!empty($v['endDate']) && !empty($v['endDate']['millisecDate'])) {
					$date = (int)($v['endDate']['millisecDate'] / 1000);
					if ($date > 946713600)
						$link['LinkEndDate'] = date('Y-m-d H:i:s', $date);
				}
				if ($v['promotionalTypeId'] == 5)
					$link['LinkPromoType'] = 'free shipping';
				else if ($v['promotionalTypeId'] == 1) {
					$link['LinkPromoType'] = 'coupon';
					if (!empty($v['couponCode']))
						$link['LinkCode'] = $v['couponCode'];
				} else {
					$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
					$code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc'] . '|' . $link['LinkHtmlCode']);
					if (!empty($code)) {
						$link['LinkCode'] = $code;
						$link['LinkPromoType'] = 'coupon';
					}
				}
		
            	if (empty($link['LinkCode'])) { 
            		$code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc'] . '|' . $link['LinkHtmlCode']);
            		if (!empty($code)) {
            			$link['LinkCode'] = $code;
            			$link['LinkPromoType'] = 'coupon';		
            		}
            	}
				$link['LinkCode'] = check_linkcode_exclude_sym($link['LinkCode']);
		
				if (empty($link['AffLinkId']) || empty($link['AffMerchantId']) || empty($link['LinkName']))
					continue;
				$links[] = $link;
				$arr_return["AffectedCount"]++;
			}
			$ids = array();
			foreach ($links as $link)
				$ids[] = $link['AffLinkId'];

			$url = "https://members.cj.com/member/publisher/".$this->CJ_API_CID."/export/links.xml";
			$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "",);
			$url .= "?ids=" . implode(",", $ids) . "&CONTID=2040083&jsContactId=2040083&jsCompanyId=".$this->CJ_API_CID."&jsCu=USD&jsDt=d-MMM-yyyy&jsLa=en&cjuMember=0";
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$content = $r['content'];
			$data = @fgetcsv_str($content);
			foreach ($links as $key => $link) {
				foreach ($data as $v) {
					if ($v['LINK ID'] == $link['AffLinkId']) {
						if (!empty($v['CLICK URL']))
							$links[$key]['LinkAffUrl'] = $v['CLICK URL'];
						if (!empty($v['HTML LINKS'])) {
							$links[$key]['LinkHtmlCode'] = $v['HTML LINKS'];
							break;
						}
					}
				}
				if (empty($link['LinkCode'])) {
					$code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc'] . '|' . $link['LinkHtmlCode']);
					if (!empty($code)) {
						$link['LinkCode'] = $code;
						$link['LinkPromoType'] = 'coupon';
					}
				}
				$link['LinkCode'] = check_linkcode_exclude_sym($link['LinkCode']);
			}
			echo sprintf("page:%s, %s links(s) found. \n", $nPageNo, count($links));
			if (count($links) > 0)
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
			$nPageNo++;
			sleep(1);
		} while ($nNumPerPage * ($nPageNo + 1) < $count && $nPageNo < 1000);*/
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'promotion');
		return $arr_return;
	}
	
	function GetAllProductsByAffId()
	{
	    
	    $check_date = date('Y-m-d H:i:s');
	    $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
	    
	    $xml2arr = new XML2Array();
	    $request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "","addheader" => array(sprintf('authorization: %s', $this->CJ_API_KEY)));
	    $arr_merchant = $this->oLinkFeed->getApprovalAffMerchant($this->info["AffId"]);
	    $productNumConfig = $this->oLinkFeed->product_program_num_config($this->info["AffId"]);
	    $productNumConfigAlert = '';
	    $isAssignMerchant = FALSE;
	    //$arr_merchant = array(array('IdInAff'=>2729793));
	    
	    $checkSKU = array();
	    $links = array();
	    $recordsReturned = 100;
	    $programNum = 0;
	    foreach ($arr_merchant as $Merchant){
	        echo 'merchant id:'.$Merchant['IdInAff'].PHP_EOL;
	        $programNum ++;
	        $crawlMerchantsActiveNum = 0;
	        $ProcessNum = 0;
	        $pageNumber = 0;
	        $setMaxNum  = isset($productNumConfig[$Merchant['IdInAff']]) ? $productNumConfig[$Merchant['IdInAff']]['limit'] :  100;
	        $isAssignMerchant = isset($productNumConfig[$Merchant['IdInAff']]) ? TRUE : FALSE;
	        $totalMatched = 0;
	        do{
	            $pageNumber ++;
	            //$url = "https://product-search.api.cj.com/v2/product-search?website-id=$this->CJ_API_PID&advertiser-ids={$Merchant['IdInAff']}&page-number=$pageNumber";
	             
	            $url = "https://product-search.api.cj.com/v2/product-search?website-id=$this->CJ_API_PID&advertiser-ids={$Merchant['IdInAff']}&page-number=$pageNumber";
	            echo $url.PHP_EOL;
	            $reponseData = $this->oLinkFeed->GetHttpResult($url, $request);
	            $content = $reponseData['content'];
	            if (empty($content))
	                break;
	            
	            //$data = $xml2arr->createArray($content);
	            $data = $this->xml_parser($content);
	            if(!$data) break; 
	            if(!isset($data['products'])) break;
	            $totalMatched = strip_tags($data['products']['@attributes']['total-matched']);    //2212
	            
	            
	            $link = array();
	            if($totalMatched && isset($data['products']['product'])){
	                
	                foreach ($data['products']['product'] as $v){
	                    $ProcessNum ++;
	                    echo 'ProcessNum:'.$ProcessNum.PHP_EOL;
	                    if(!isset($v['buy-url'])) continue;
	                    $finalUrl = '';
                        $parseAffUrlArr = parse_url($v['buy-url']);
                         
	                    if(preg_match('/url=(.+)/i',$parseAffUrlArr['query'],$matches)){
                            $finalUrl = $matches[1];
                        }else 
                            continue;
                        
                        if(is_array($v['image-url']) && !$v['image-url']){
                            continue;
                        } 
                        
                        $productId =  substr(md5($finalUrl),8,16);
                        //下载图片
	                    $product_path_file = $this->oLinkFeed->getProductImgFilePath($this->info["AffId"],"{$Merchant['IdInAff']}_".urlencode($productId).".png", PRODUCTDIR);
	                    if(!$this->oLinkFeed->fileCacheIsCached($product_path_file)){
	                        $file_content = $this->oLinkFeed->downloadImg($v['image-url']);
	                        if(!$file_content) //下载不了跳过。
	                            continue;
	                        $this->oLinkFeed->fileCachePut($product_path_file, $file_content);
	                    }
	                    
	                    if(isset($checkSKU[$v['sku']])){
	                        $checkSKU[$v['sku']]['num'] ++;
	                    }else{
	                        $checkSKU[$v['sku']]['num'] = 1;
	                        $checkSKU[$v['sku']]['value'] = $v['sku'];
	                    }
	                    $link = array(
	                        "AffId" => $this->info["AffId"],
	                        "AffMerchantId" => $Merchant['IdInAff'],
	                        "AffProductId" => $productId,
	                        "ProductName" => addslashes($v['name']),
	                        "ProductCurrency" =>$v['currency'],
	                        "ProductPrice" =>$v['price'],
	                        "ProductOriginalPrice" =>'',
	                        "ProductRetailPrice" => is_array($v['retail-price']) ? '':$v['retail-price'],
	                        "ProductImage" => addslashes($v['image-url']),
	                        "ProductLocalImage" => addslashes($product_path_file),
	                        "ProductUrl" => addslashes($v['buy-url']),
	                        "ProductDestUrl" => '',
	                        "ProductDesc" => addslashes(strip_tags($v['description'])),
	                        "ProductStartDate" => '',
	                        "ProductEndDate" => '',
	                    );
	                    
	                     
	                    $crawlMerchantsActiveNum ++;
	                    $links[] = $link;
	                    $arr_return['AffectedCount'] ++;
	                }
	                
	                if(count($links) >= 100){
	                    $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateProductToDB($links);
	                    $links = array();
	                }
	            }
	            if($crawlMerchantsActiveNum>=$setMaxNum || ($ProcessNum >= 500 && $setMaxNum <= 100)){
	                break;
	            }
	            
	             
	        }while ($recordsReturned * $pageNumber < $totalMatched);
	        
	        if (count($links))
	        {
	            $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateProductToDB($links);
	            $links = array();
	        }
	        if($isAssignMerchant){
	            $productNumConfigAlert .= "AFFID:".$this->info["AffId"].",Program({$Merchant['MerchantName']}),Crawl Count($crawlMerchantsActiveNum),Total Count({$totalMatched}) \r\n";
	        }
	        //一个merchant结束
	        
	        
	    }
	    if($checkSKU){
	        //写入日志
	        $file = fopen('./checkSKU'.date('d').'.csv','a+');
	        foreach($checkSKU as $skuV){
	            fputcsv($file, $skuV);
	        }
	        fclose($file);
	    }
	    $this->oLinkFeed->checkProductExists($this->info["AffId"], $check_date);
	    echo $productNumConfigAlert.PHP_EOL;
	    echo "\r\nprogram num:".$programNum."\r\n";
	    return $arr_return;
	    
	    
	}

	function getInvalidLinks()
	{
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "",);
		$url = 'https://members.cj.com/member/publisher/'.$this->CJ_API_CID.'/performanceReport/invalidLinks.csvx';
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r['content'];
		$data = @fgetcsv_str($content);
		if (empty($data) || !is_array($data))
			return;
		$links = array();
		$ids = array();
		foreach ($data as $v) {
			if (empty($v['LINK ID']))
				continue;
			if (!empty($ids[$v['LINK ID']])) {
//				echo sprintf("duplicate id: %s.\n", $v['LINK ID']);
				continue;
			}
			$ids[$v['LINK ID']] = 1;
			$link = array(
				'affiliate' => $this->info["AffId"],
				'LinkID' => $v['LINK ID'],
				'ReferralUrl' => $v['REFERRING URL'],
				'ProgramName' => $v['ADVERTISER NAME'],
				'AffiliationStatus' => $v['AFFILIATION STATUS'],
				'Clicks' => $v['CLICKS'],
			);
			// http://mantis.megainformationtech.com/view.php?id=432
			// 过滤CJ联盟状态为Declined Application/Advertiser Expired/Pending Application/No Relationship的Tasks，仅将联盟状态为Active的生成Tasks
			if (trim(strtolower($link['AffiliationStatus'])) != 'active')
				continue;
			$links[] = $link;
		}
		return $links;
	}

	function getMessage()
	{
		$messages = array();
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "",);
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);
		$url = "https://members.cj.com/member/$this->UserID/accounts/publisher/mail/affil_mail_home.jsp?ar_inbox=1&ar_FID=$this->FID";
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r['content'];
		preg_match_all('@<tr bgcolor="#eeeeee">(.*?)</tr>@mis', $content, $chapters);
		if (empty($chapters) || !is_array($chapters) || empty($chapters[1]) || !is_array($chapters[1]))
			return 'no message found.';
		foreach ($chapters[1] as $chapter) {
			$data = array(
				'affid' => $this->info["AffId"],
				'messageid' => '',
				'sender' => '',
				'title' => '',
				'content' => '',
				'created' => '0000-00-00',
			);
			if (preg_match('@<a href="#".*?>(.*?)<@', $chapter, $g))
				$data['sender'] = $g[1];
			if (preg_match('@ar_MSGID=(\d+)@', $chapter, $g))
				$data['messageid'] = $g[1];
			if (preg_match('@href="(.*?)".*?OpenWithCrumbTrail\(.*?return false">(.*?)<@', $chapter, $g)) {
				$data['content_url'] = 'https://members.cj.com' . $g[1];
				$data['title'] = $g[2];
			}
			if (preg_match('@"rightCellText">(.*?)<@', $chapter, $g))
				$data['created'] = parse_time_str($g[1], null, false);
			if (empty($data['messageid']) || empty($data['title']))
				continue;
			$messages[] = $data;
		}
		return $messages;
	}

	function getMessageDetail($data)
	{
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "",);
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);
		$url = $data['content_url'];
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r['content'];
		if (preg_match('@class="displayIndentCol">(.*?)</td>@ms', $content, $g))
			$data['content'] = str_force_utf8(trim(html_entity_decode($g[1])));
		return $data;
	}

	function GetProgramFromAff()
	{
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
		//$this->GetProgramByPage();
		$this->GetProgramByApi();
		$this->checkProgramOffline($this->info["AffId"], $check_date);

		echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";
	}

	function GetProgramByApi()
	{
		echo "\tGet Program by api start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		$request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "",);

		//step 1,login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);
		$link = "https://advertiser-lookup.api.cj.com/v3/advertiser-lookup?";
		$order_arr = array("joined", "notjoined");


		//status only
		echo "\t get Support Deep Url\r\n";
		$arr_deepdomain = $this->getSupportDeepUrl();
		$xml2arr = new XML2Array();

		foreach ($order_arr as $vvv) {
			list($nPageNo, $nNumPerPage, $bHasNextPage, $nPageTotal) = array(1, 100, true, 1);
			while ($bHasNextPage) {
				$param = array(
					"advertiser-ids=$vvv",    //CIDs,joined,notjoined
					"advertiser-name=",
					"keywords=",
					"page-number={$nPageNo}",
					"records-per-page={$nNumPerPage}",
					//"mobile-tracking-certified=false",
				);
//                print_r($param);
				$postdata = implode("&", $param);
				$strUrl = $link . $postdata;
				$request = array("method" => "get", "addheader" => array("authorization: {$this->CJ_API_KEY}"),);
				$r = $this->oLinkFeed->GetHttpResult($strUrl, $request);
				$result = $r["content"];
				if (empty($result))
					continue;
				$re = $xml2arr->createArray($result);
				$re = $re['cj-api']['advertisers'];

//                print_r($re['advertiser'][0]);die;
//                print_r($re['@attributes']);
				$total_matched = $re['@attributes']["total-matched"];
				$records_returned = $re['@attributes']["records-returned"];
				$page_number = $re['@attributes']["page-number"];

				$nPageTotal = ceil($total_matched / $nNumPerPage);
				$bHasNextPage = $page_number;
				if ($nPageNo >= $nPageTotal || $records_returned < 100) {
					$bHasNextPage = false;
					if ($this->debug) print " NO NEXT PAGE  <br>\n";
				} else {
					$nPageNo++;
					if ($this->debug) print " Have NEXT PAGE  <br>\n";
				}

				foreach($re['advertiser'] as $advertiser){                	
					$StatusInAff = $advertiser["account-status"];
					$CategoryExt = $advertiser['primary-category']['parent'].'-'.$advertiser['primary-category']['child'];
					if(!$advertiser['primary-category']['parent']){
						$CategoryExt = $advertiser['primary-category']['child'];
					}
					$EPCDefault = $advertiser["seven-day-epc"];
					$EPC90d = $advertiser["three-month-epc"];
					$Homepage = strtolower($advertiser["program-url"]);
					$Partnership = $advertiser["relationship-status"];
					$RankInAff = intval($advertiser["network-rank"]);
					$Commission = array();
//                    print_r($advertiser['actions']);
					$c=$advertiser['actions'];
					if(isset($c['action']['name'])){
						   //action 只有一个
						if(isset($c['action']['commission']['itemlist'])) {
							if(is_array($c['action']['commission']['default'])){
								$Commission[] = $c['action']['name'].":".$c['action']['commission']['default']['@attributes']['type'].":".$c['action']['commission']['default']['@value'];

							}else {
								$Commission[] = $c['action']['name'] . ":" . $c['action']['type'] . ":" . $c['action']['commission']['default'];
							}
							if(isset($c['action']['commission']['itemlist'][0])) {
								foreach ($c['action']['commission']['itemlist'] as $item) {
									$Commission[] = $item["@attributes"]['name'] . ":sub:" . $item["@value"];
								}
							}else{
								$Commission[] = $c['action']['commission']['itemlist']['@attributes']['name'].":".$c['action']['type'].":".$c['action']['commission']['itemlist']['@value'];
							}
						}
						else{
							if(is_array($c['action']['commission']['default'])){
								$Commission[] = $c['action']['name'].":".$c['action']['commission']['default']['@attributes']['type'].":".$c['action']['commission']['default']['@value'];
							}
							else
								$Commission[] = $c['action']['name'].":".$c['action']['type'].":".$c['action']['commission']['default'];
						}
					}elseif(isset($c['action'][0])){

						foreach($c['action'] as $v){
							if(isset($v['commission']['itemlist'])) {
								if(is_array($v['commission']['default'])){
									$Commission[] = $v['name'].":".$v['commission']['default']['@attributes']['type'].":".$v['commission']['default']['@value'];
								}else{
									$Commission[] = $v['name'] . ":" . $v['type'] . ":" . $v['commission']['default'];
								}
								if(isset($v['commission']['itemlist'][0])) {
									foreach ($v['commission']['itemlist'] as $item) {
										$Commission[] = $item["@attributes"]['name'] . ":sub:" . $item["@value"];
									}
								}else{
									$Commission[] = $v['commission']['itemlist']['@attributes']['name'].":".$v['type'].":".$v['commission']['itemlist']['@value'];
								}
							}
							else{
								if(is_array($v['commission']['default'])){
									$Commission[] = $v['name'].":".$v['commission']['default']['@attributes']['type'].":".$v['commission']['default']['@value'];
								}else
								$Commission[] = $v['name'].":".$v['type'].":".$v['commission']['default'];
							}
						}
					}

					$CommissionExt  = implode('|',$Commission);
					$IdInAff = trim($advertiser["advertiser-id"]);
					$Name = $advertiser["advertiser-name"];					
					if (empty($IdInAff) || empty($Name)) continue;
					if ($RankInAff == 6) {//means this program is new, do not have rank yet.
						$RankInAff = 0;
					}
					if ($Partnership == "joined") {
						$Partnership = "Active";
					} else {
						$Partnership = "NoPartnership";
					}
					if (!in_array($StatusInAff, array('Active', 'TempOffline', 'Offline'))) {
						$StatusInAff = 'Offline';
					}
					$arr_prgm[$IdInAff] = array("Name" => addslashes(trim($Name)),
						"IdInAff" => $IdInAff,
						"AffId" => $this->info["AffId"],
						"Homepage" => addslashes($Homepage),
						"RankInAff" => $RankInAff,
						"StatusInAffRemark" => addslashes($advertiser["account-status"]),
						"StatusInAff" => $StatusInAff,                        //'Active','TempOffline','Offline'
						"Partnership" => $Partnership,                        //'NoPartnership','Active','Pending','Declined','Expired','Removed'
						"EPCDefault" => addslashes(trim(preg_replace("/[^0-9.-]/", "", $EPCDefault))),
						"EPC90d" => addslashes(trim(preg_replace("/[^0-9.-]/", "", $EPC90d))),
						"LastUpdateTime" => date("Y-m-d H:i:s"),
						"MobileFriendly" => 'UNKNOWN',
						"SupportDeepUrl" => 'UNKNOWN',
						"CommissionExt" => addslashes($CommissionExt),
						"CategoryExt" => addslashes($CategoryExt),
					);
					
					if ($advertiser['mobile-tracking-certified'] == 'true')
						$arr_prgm[$IdInAff]['MobileFriendly'] = 'YES';
					else if ($advertiser['mobile-tracking-certified'] == 'false')
						$arr_prgm[$IdInAff]['MobileFriendly'] = 'NO';
						
					if (count($arr_deepdomain)) {
						$Homepage = preg_replace("/^https?:\\/\\/(.*?)\\/?/i", "\$1", $Homepage);                        
						
						if (isset($arr_deepdomain[$Homepage])) {
							$arr_prgm[$IdInAff]['SupportDeepUrl'] = "YES";
						} else {
							$Homepage = preg_replace("/^ww.{0,2}\./i", "", $Homepage);
							if (isset($arr_deepdomain[$Homepage])) {
								$arr_prgm[$IdInAff]['SupportDeepUrl'] = "YES";
							}
						}
					}

                    if (!$this->getStatus) {
                        $request = array("AffId" => $this->info["AffId"], "method" => "get",);

                        $use_true_file_name = true;

                        /*
                         * detail
                         */
                        $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"detail_".date("Ym")."_{$IdInAff}.dat", "program", $use_true_file_name);
                        if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
                        {
                            $prgm_url = "https://members.cj.com/member/advertiser/$IdInAff/detail.json";
                            $prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
                            if($prgm_arr['code'] == 200){
                                $results = $prgm_arr['content'];
                                $this->oLinkFeed->fileCachePut($cache_file, $results);
                            }
                        }
                        $cache_file = file_get_contents($cache_file);
                        if($cache_file){
                            $cache_file = json_decode($cache_file);
                            //$CategoryExt = $cache_file->categoryName;
                            $desc = $cache_file->advertiser->description;
                            $arr_prgm[$IdInAff]['Description'] = addslashes($desc);
                        }
                        /*
                         * contact
                         */
                        $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"contact_".date("Ym")."_{$IdInAff}.dat", "program", $use_true_file_name);
                        if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
                        {
                            $prgm_url = "https://members.cj.com/member/advertiser/$IdInAff/contact/".$this->CJ_API_CID.".json";
                            $prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
                            if($prgm_arr['code'] == 200){
                                $results = $prgm_arr['content'];
                                $this->oLinkFeed->fileCachePut($cache_file, $results);
                            }
                        }
                        $cache_file = file_get_contents($cache_file);
                        if($cache_file){
                            $cache_file = json_decode($cache_file);
                            $contact = "Contact: " .  $cache_file->contact->name . "; Email: ". $cache_file->contact->email;
                            $arr_prgm[$IdInAff]['Contacts'] = addslashes($contact);
                        }

                        /*
                         * terms
                         */
                        $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"terms_".date("Ym")."_{$IdInAff}.dat", "program", $use_true_file_name);
                        if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
                        {
                            $prgm_url = "https://members.cj.com/member/publisher/".$this->CJ_API_CID."/advertiser/$IdInAff/activeProgramTerms.json";
                            $prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
                            if($prgm_arr['code'] == 200){
                                $results = $prgm_arr['content'];
                                $this->oLinkFeed->fileCachePut($cache_file, $results);
                                $cache_file = file_get_contents($cache_file);
                                if($cache_file){
                                    $cache_file = json_decode($cache_file);
                                    //echo $IdInAff."\t";
                                    $TermAndCondition = '';
                                    $AllowNonaffPromo = 'UNKNOWN';
                                    $AllowNonaffCoupon = 'UNKNOWN';
                                    $str_allownonaff = 'Publishers may use any coupons or promotional codes that are provided through the affiliate program or otherwise available to the public';
                                    $str_notallownonaff = 'Publishers may only use coupons and promotional codes that are provided exclusively through the affiliate program';
                                    if(isset($cache_file->activeProgramTerms->policies->policiesList)){
                                        foreach($cache_file->activeProgramTerms->policies->policiesList as $tmp_policy){
                                            $TermAndCondition .= '<b>' . $tmp_policy->policyTitle . '</b><br />&nbsp;&nbsp;&nbsp;&nbsp;'. $tmp_policy->policyText . '<br /><br />';
                                            if ($tmp_policy->policyId == "coupons_and_promotional_codes"){
                                                if (strstr($tmp_policy->policyText, $str_allownonaff)){
                                                    $AllowNonaffPromo = 'YES';
                                                    $AllowNonaffCoupon = 'YES';
                                                }
                                                if (strstr($tmp_policy->policyText, $str_notallownonaff)){
                                                    $AllowNonaffCoupon = 'NO';
                                                }
                                            }
                                        }
                                        $arr_prgm[$IdInAff]['TermAndCondition'] = addslashes($TermAndCondition);
                                        $arr_prgm[$IdInAff]['AllowNonaffPromo'] = addslashes($AllowNonaffPromo);
                                        $arr_prgm[$IdInAff]['AllowNonaffCoupon'] = addslashes($AllowNonaffCoupon);
                                    }
                                }
                            }
                        }
                    }

					$program_num++;

					if (count($arr_prgm) >= 100) {
						echo $program_num;
						if (!$this->getStatus) {
							$request = array("AffId" => $this->info["AffId"], "method" => "get",);
							$id_list = implode(",", array_keys($arr_prgm));
							$prgm_url = "https://members.cj.com/member/publisher/".$this->CJ_API_CID."/advertiserSearch.json?pageNumber=1&publisherId=".$this->CJ_API_CID."&pageSize=100&advertiserIds=$id_list&geographicSource=&sortColumn=advertiserName&sortDescending=false";
							$return_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
							if ($return_arr["code"] == 200) {
								$prgm_json = json_decode($return_arr["content"]);
								foreach ($prgm_json->advertisers as $v_j) {
									if (isset($arr_prgm[$v_j->advertiserId])) {
										if (is_array($v_j->serviceableAreas) && count($v_j->serviceableAreas)) {
											$arr_prgm[$v_j->advertiserId]["TargetCountryExt"] = addslashes(implode(",", $v_j->serviceableAreas));
										}
									}									
								}
							}							
						}
						$objProgram->updateProgram($this->info["AffId"], $arr_prgm);						
						$arr_prgm = array();
					}
				}

			}
			if (count($arr_prgm)) {
				if (!$this->getStatus) {
					$request = array("AffId" => $this->info["AffId"], "method" => "get",);
					$id_list = implode(",", array_keys($arr_prgm));
					$prgm_url = "https://members.cj.com/member/publisher/".$this->CJ_API_CID."/advertiserSearch.json?pageNumber=1&publisherId=".$this->CJ_API_CID."&pageSize=100&advertiserIds=$id_list&geographicSource=&sortColumn=advertiserName&sortDescending=false";
					$return_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
					if ($return_arr["code"] == 200) {
						$prgm_json = json_decode($return_arr["content"]);
						foreach ($prgm_json->advertisers as $v_j) {
							if (isset($arr_prgm[$v_j->advertiserId])) {
								if (is_array($v_j->serviceableAreas) && count($v_j->serviceableAreas)) {
									$arr_prgm[$v_j->advertiserId]["TargetCountryExt"] = addslashes(implode(",", $v_j->serviceableAreas));
								}
							}							
						}
					}
				}
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);				 
				unset($arr_prgm);
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

	function checkProgramOffline($AffId, $check_date)
	{
		$objProgram = new ProgramDb();
		$prgm = array();
		$prgm = $objProgram->getNotUpdateProgram($this->info["AffId"], $check_date);
		$this->recheckProgram($prgm);
		$prgm = $objProgram->getNotUpdateProgram($this->info["AffId"], $check_date);
		if (count($prgm) > 30) {
			mydie("die: too many offline program (" . count($prgm) . ").\n");
			echo print_r($prgm, 1);
		} else {
			$objProgram->setProgramOffline($this->info["AffId"], $prgm);
			echo "\tSet (" . count($prgm) . ") offline program.\r\n";
		}
	}

	/* function GetProgramByPage()
	{
		//get other program
		echo "\tGet Program by page start\r\n";
	
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		$nPageNo = 1;
		$nPageTotal = 2;
		$nNumPerPage = 100;
		$bHasNextPage = true;
		$request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "",);
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);//登录成功，返回true
		$a = 0;
		while($bHasNextPage)
		{
			if ($nPageNo >= $nPageTotal) {
				$bHasNextPage = false;
				if ($this->debug) print " NO NEXT PAGE  <br>\n";
			}
			//$str_url = "https://members.cj.com/member/publisher/{$this->CJ_API_CID}/advertiserSearch.json?pageNumber={$nPageNo}&publisherId={$this->CJ_API_CID}&pageSize={$nNumPerPage}&geographicSource=&sortColumn=advertiserName&sortDescending=false";
			$str_url = "https://members.cj.com/member/publisher/{$this->CJ_API_CID}/advertiserSearch.json?pageNumber={$nPageNo}&publisherId={$this->CJ_API_CID}&pageSize={$nNumPerPage}&geographicSource=&relationshipStatus=declined_applications&sortColumn=advertiserName&sortDescending=false";

			$tmp_arr = $this->oLinkFeed->GetHttpResult($str_url, $request);
			$result = $tmp_arr["content"];
			$result = json_decode($result,true);var_dump($result);exit;
			$nPageTotal = ceil($result['totalResults'] / $nNumPerPage);
			foreach($result['advertisers'] as $r){
				 if(isset($r['statuses'][0]) && $r['statuses'][0] == 'pending_applications'){
					echo $r['advertiserId']."\n\r";
				}
				if(isset($r['statuses'][0]) && $r['statuses'][0] == 'declined_applications'){
					var_dump($r);
				}
				if(!isset($r['statuses'][0])){
					$a++;
				}
				//echo $r['advertiserId']."\n\r";
			}
			//var_dump($result);exit;
			unset($tmp_arr);
			unset($result);
			unset($r);
			$nPageNo++;
		}echo $a;
		exit;
	} */

	function recheckProgram($prgm)
	{
		$arr_prgm = array();
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);
		$request = array("AffId" => $this->info["AffId"], "method" => "get",);
		foreach ($prgm as $v) {
			$prgm_url = "https://members.cj.com/member/publisher/".$this->CJ_API_CID."/ads.json?page=0&advertiserIds={$v['IdInAff']}&isEmpty=false&pageSize=50&sortColumn=advertiserName&sortDescending=false";
			$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
			//$prgm_detail = $prgm_arr["content"];
			if ($prgm_arr["code"] == 200) {
				$tmp_arr = array();
				$tmp_arr = json_decode($prgm_arr["content"]);
				if ($tmp_arr->totalRecords > 0) {
					$arr_prgm[$v['IdInAff']] = array(
						"AffId" => $this->info["AffId"],
						"IdInAff" => $v['IdInAff'],
						"LastUpdateTime" => date("Y-m-d H:i:s")
					);
				}
			}
		}
		if (count($arr_prgm)) {
			$objProgram = new ProgramDb();
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
		}
	}

	function getSupportDeepUrl()
	{
		//http://[CJDOMAINS]/links/[SITEIDINAFF]/type/dlg/sid/[SUBTRACKING]/[PURE_DEEPURL]
		$domains_arr = array();
		$url = "http://www.yceml.net/am_gen/".$this->CJ_API_PID."/include/allCj/am.js";
		$tmp_arr = $this->oLinkFeed->GetHttpResult($url, array("method" => "get"));
		if ($tmp_arr["code"] == 200) {
			$domains = trim($this->oLinkFeed->ParseStringBy2Tag($tmp_arr["content"], 'domains=[', ']'));
			$domains_arr = array_flip(explode("','", trim($domains, "'")));
		}
		return $domains_arr;
	}
	
	function xml_parser($str){
	    $arr_return = array();
		$xml_parser = xml_parser_create();
		if(xml_parse($xml_parser,$str)){
            $arr_return = json_decode(json_encode(simplexml_load_string($str)),true);
		}else {
		    echo "\t" . xml_error_string( xml_get_error_code($xml_parser)) . "\r\n";
        }
        xml_parser_free($xml_parser);
        return $arr_return;
	}
	
	 
	 
	
	
}
