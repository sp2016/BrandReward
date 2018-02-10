<?php

require_once 'text_parse_helper.php';
require_once 'xml2array.php';


class LinkFeed_2_LS
{
	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		
		$this->cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"{$this->info["AffId"]}_".date("YW").".dat", "program", true);
		$this->cache = array();
		$this->productDir = '/app/site/ezconnexion.com/web/img/';
		if($this->oLinkFeed->fileCacheIsCached($this->cache_file)){
			$this->cache = file_get_contents($this->cache_file);
			$this->cache = json_decode($this->cache,true);
		}
		
		if(SID == 'bdg02'){
			define('API_TOKEN_2', '9f48285de7d00f0aed822788c0144da206901ad94e8e3b68f930082d9ab2a17d');
			define('MOUSE_OVER_OID_2', '223073');
			define('AUTH_TOKEN', 'NkplZXZQcDYzakpmQVQxeUlTRkp0M2h4QTZjYTpsMnlGeXhSal90T1JKcXluS1Bja01vaDZlRFFh');
			define('UID', 3310876);
		}else{
	    	define('API_TOKEN_2', '4224a3cc267a2b6b6f32d8e8ec90a2058b11fb3eeadd9cfc9f85665ceccfd2de');
			define('MOUSE_OVER_OID_2', '276349');
			define('AUTH_TOKEN', 'aEg0Y3dyQ21vRTBoUXdLRVRaUkhUWF9aRTVzYTpMREhNWE5kaHE3RWdacHZPcGZxdGxqMEo2RU1h');
			define('UID', 3368752);
		}		
	}

	function getStatusByStr($str)
	{
		if(stripos($str,'No Relationship') !== false) return 'not apply';
		elseif(stripos($str,'Pending') !== false) return 'pending';
		elseif(stripos($str,'Approved') !== false) return 'approval';
		elseif(stripos($str,'Discontinued') !== false) return 'siteclosed';
		elseif(stripos($str,'Declined') !== false) return 'declined';
		elseif(stripos($str,'Removed') !== false) return 'expired';
		elseif(stripos($str,'Extended') !== false) return 'approval';
		return false;
	}
	
	function GetAllLinksByAffId()
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, );
		$request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "", );
		$check_date = date('Y-m-d H:i:s');
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);
		$arr_merchant = $this->oLinkFeed->getApprovalAffMerchant($this->info["AffId"]);
	
		//get linkID from feed
		$linkID_byfeed_array = array();
		$sql = "select AffLinkId from affiliate_links_2 where DataSource = 2";
		$linkID_byfeed_array = $this->oLinkFeed->objMysql->getRows($sql);
		
		// MerID_NetworkID
		foreach ($arr_merchant as $AffMerchantId => $merinfo)
		{
			$r = explode('_', $merinfo["IdInAff"]);
			if(sizeof($r) != 2 || !is_numeric($r[1]) || !is_numeric($r[0]))
			{
				echo " Invalid Merchant ID({$merinfo['AffMerchantId']}). It should be 'MerchantID_NetworkID' .Please check <a href='/editor/coupon.php?action=editmerchant&merchantid=$r[0]'>merchant setting</a>. <br> \n";
				return $arr_return;
			}
			$mer_id = $r[0];
			$network = $r[1];
			$types = array('banner', 'text');
			foreach ($types as $type)
			{
				$url = "http://cli.linksynergy.com/cli/publisher/links/link_list.php?mid=$mer_id&nid=$network&type=$type";
				$request['method'] = 'get';
				$r = $this->GetHttpResult_2($url, $request);
				$content = $r["content"];
				preg_match('@name=\'__csrf_magic\'\s+value=\"(.*?)\"@', $content, $g);
				$__csrf_magic = '';
				if(count($g[1])){
					$__csrf_magic = $g[1];
				}
				$request['method'] = 'post';
					
				$limit = 100;
				$page = 1;
				do
				{
					$links = array();
					$url = "http://cli.linksynergy.com/cli/publisher/links/link_list.php?mid=$mer_id&nid=$network&type=$type";
					$request["postdata"] = sprintf("currec=%s&pagesize=%s&__csrf_magic=$__csrf_magic", $limit * ($page - 1) + 1, $limit);
					$r = $this->GetHttpResult_2($url, $request);
					$content = $r["content"];
					preg_match_all('@"creative\[\]"\s+value=\'(.*?)\'@', $content, $g);
					if (empty($g) || empty($g[0]) || !is_array($g[0]))
						break;
					$count = count($g[1]);
					$params = array();
					foreach ($g[1] as $v)
					{
						$params[] = 'creative%5B%5D=' . urlencode($v);
						$params[] = urlencode($v) . '=1';
					}
					$url = 'http://cli.linksynergy.com/cli/publisher/links/output_data.php?download=download&model=linksearch';
					$request['postdata'] = sprintf("mouseOverOid=%s&mname=%s_%s&%s&__csrf_magic=$__csrf_magic", MOUSE_OVER_OID_2, $mer_id, MOUSE_OVER_OID_2, implode('&', $params));
					$r = $this->GetHttpResult_2($url, $request);
					$content = $r['content'];
					$data = @fgetcsv_str($content);
					foreach ((array)$data as $v)
					{
						if (empty($v['LINK CODE']))
							continue;
						$link = array(
								"AffId" => $this->info["AffId"],
								"AffMerchantId" => $merinfo['IdInAff'],
								"LinkName" => $v['LINK NAME'],
								"LinkDesc" => '',
								"LinkStartDate" => parse_time_str($v['START DATE'], null, false),
								"LinkEndDate" => parse_time_str($v['END DATE'], null, false),
								"LinkPromoType" => 'DEAL',
								"LinkHtmlCode" => $v['LINK CODE'],
								"LinkCode" => '',
								"LinkOriginalUrl" => '',
								"LinkImageUrl" => '',
								"LinkAffUrl" => '',
								"DataSource" => 6,
								"IsDeepLink" => 'UNKNOWN',
								"Type"       => 'link'
						);
							
						//link desc
						if($type == 'banner'){
							if (preg_match('@<IMG alt="(.*?)"@', $link['LinkHtmlCode'], $g)){
								$link['LinkDesc'] = $g[1];
							}
						}
						if($type == 'text'){
							if (preg_match('@<a[^>]*>(.*?)</a>@', $link['LinkHtmlCode'], $g)){
								$link['LinkDesc'] = $g[1];
							}
						}
							
						if (preg_match('@a href="(.*?)"@', $link['LinkHtmlCode'], $g))
							$link['LinkAffUrl'] = $g[1];
						if (preg_match('/<IMG.+src="(.*?)"/i', $link['LinkHtmlCode'], $g))
							$link['LinkImageUrl'] = $g[1];
						if (preg_match('@\&offerid=(\d+\.\d+)\&@', $link['LinkAffUrl'], $g))
							$link['AffLinkId'] = $g[1];
						
						$is_feed = false;
						foreach ($linkID_byfeed_array as $linkID_byfeed){
							if($link['AffLinkId'] == $linkID_byfeed['AffLinkId']){
								$is_feed = true;
								break;
							}
						}
						if($is_feed)
							continue;
						
						
						$code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkHtmlCode']);
						if (!empty($code))
						{
							$link['LinkCode'] = $code;
							$link['LinkPromoType'] = 'coupon';
						}
						else{
						    if($type == 'banner'){
						        $link['LinkPromoType'] = 'banner';
						    }else{
						        $link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
						    }
						}
						//if (empty($link['AffLinkId']) || empty($link['LinkHtmlCode']) || empty($link['LinkName']) || $link['LinkPromoType'] == 'N/A')
						//need all links
						if (empty($link['AffLinkId']) || empty($link['LinkHtmlCode'])){
							continue;
						}elseif(empty($link['LinkName'])){
							$link['LinkPromoType'] = 'link';
						}
						$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
						$arr_return["AffectedCount"] ++;
						$links[] = $link;
					}
					echo sprintf("program:%s, page:%s, %s links(s) found. \n", $merinfo['IdInAff'], $page, count($links));
					if(count($links) > 0)
						$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
					$page ++;
				}while ($count >= $limit && $page < 100);
			}
		}
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link');
		return $arr_return;
	}

	function GetAllLinksFromAffByMerID($merinfo, $newonly=true)
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, );
		$request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "", );
		$check_date = date('Y-m-d H:i:s');
		
		// MerID_NetworkID
		$r = explode('_', $merinfo["IdInAff"]);
		if(sizeof($r) != 2 || !is_numeric($r[1]) || !is_numeric($r[0]))
		{
			echo " Invalid Merchant ID({$merinfo['AffMerchantId']}). It should be 'MerchantID_NetworkID' .Please check <a href='/editor/coupon.php?action=editmerchant&merchantid=$internal_merid'>merchant setting</a>. <br> \n";
			return $arr_return;
		}
		$mer_id = $r[0];
		$network = $r[1];
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		$types = array('banner', 'text');
		foreach ($types as $type)
		{
			$url = "http://cli.linksynergy.com/cli/publisher/links/link_list.php?mid=$mer_id&nid=$network&type=$type";
			$request['method'] = 'get'; 
			$r = $this->GetHttpResult_2($url, $request);
			$content = $r["content"];			
			preg_match('@name=\'__csrf_magic\'\s+value=\"(.*?)\"@', $content, $g);			
			$__csrf_magic = '';
			if(count($g[1])){
				$__csrf_magic = $g[1];
			}
			$request['method'] = 'post'; 
			
			$limit = 100;
			$page = 1;
			do
			{
				$links = array();
				$url = "http://cli.linksynergy.com/cli/publisher/links/link_list.php?mid=$mer_id&nid=$network&type=$type";
				$request["postdata"] = sprintf("currec=%s&pagesize=%s&__csrf_magic=$__csrf_magic", $limit * ($page - 1) + 1, $limit);
				$r = $this->GetHttpResult_2($url, $request);
				$content = $r["content"];
				preg_match_all('@"creative\[\]"\s+value=\'(.*?)\'@', $content, $g);
				if (empty($g) || empty($g[0]) || !is_array($g[0]))
					break;
				$count = count($g[1]);
				$params = array();
				foreach ($g[1] as $v)
				{
					$params[] = 'creative%5B%5D=' . urlencode($v);
					$params[] = urlencode($v) . '=1';
				}
				$url = 'http://cli.linksynergy.com/cli/publisher/links/output_data.php?download=download&model=linksearch';
	 			$request['postdata'] = sprintf("mouseOverOid=%s&mname=%s_%s&%s&__csrf_magic=$__csrf_magic", MOUSE_OVER_OID_2, $mer_id, MOUSE_OVER_OID_2, implode('&', $params));
				$r = $this->GetHttpResult_2($url, $request);
				$content = $r['content'];
				$data = @fgetcsv_str($content);
				foreach ((array)$data as $v)
				{
					if (empty($v['LINK CODE']))
						continue;
					$link = array(
							"AffId" => $this->info["AffId"],
							"AffMerchantId" => $merinfo['IdInAff'],
							"LinkName" => $v['LINK NAME'],
							"LinkDesc" => '',
							"LinkStartDate" => parse_time_str($v['START DATE'], null, false),
							"LinkEndDate" => parse_time_str($v['END DATE'], null, false),
							"LinkPromoType" => 'DEAL',
							"LinkHtmlCode" => $v['LINK CODE'],
							"LinkCode" => '',
							"LinkOriginalUrl" => '',
							"LinkImageUrl" => '',
							"LinkAffUrl" => '',
							"DataSource" => 6,
					        "IsDeepLink" => 'UNKNOWN',
					        "Type"       => 'link'
					);
					
					//link desc
					if($type == 'banner'){
					    if (preg_match('@<IMG alt="(.*?)"@', $link['LinkHtmlCode'], $g)){
					        $link['LinkDesc'] = $g[1];
					    }
					}
					if($type == 'text'){
					    if (preg_match('@<a[^>]*>(.*?)</a>@', $link['LinkHtmlCode'], $g)){
					        $link['LinkDesc'] = $g[1];
					    }
					}
					
					if (preg_match('@a href="(.*?)"@', $link['LinkHtmlCode'], $g))
						$link['LinkAffUrl'] = $g[1];
					if (preg_match('@border="0" src="(.*?)"@', $link['LinkHtmlCode'], $g))
						$link['LinkImageUrl'] = $g[1];
					if (preg_match('@\&offerid=(\d+\.\d+)\&@', $link['LinkAffUrl'], $g))
						$link['AffLinkId'] = $g[1];
					$code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkHtmlCode']);
					if (!empty($code))
					{
						$link['LinkCode'] = $code;
						$link['LinkPromoType'] = 'coupon';
					}
					else
						$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
					//if (empty($link['AffLinkId']) || empty($link['LinkHtmlCode']) || empty($link['LinkName']) || $link['LinkPromoType'] == 'N/A')
					//need all links 
					if (empty($link['AffLinkId']) || empty($link['LinkHtmlCode'])){
                        continue;
                    }elseif(empty($link['LinkName'])){
                        $link['LinkPromoType'] = 'link';
                    }
					$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
					$arr_return["AffectedCount"] ++;
					$links[] = $link;
				}
				echo sprintf("program:%s, page:%s, %s links(s) found. \n", $merinfo['IdInAff'], $page, count($links));
				if(count($links) > 0)
					$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
				$page ++;
			}while ($count >= $limit && $page < 100);
		}
		
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link', $merinfo['IdInAff']);
		return $arr_return;
	}

	// LS webservice sometimes return 503. 
	// when api server service unavailable, try another 5 times
	private function GetHttpResult_2($url, $request)
	{
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		if ($r['code'] == 503 || $r['code'] == 0)
		{
			for ($i = 0; $i < 5; $i ++)
			{
				$r = $this->oLinkFeed->GetHttpResult($url, $request);
				if ($r['code'] != 503 && $r['code'] != 0)
						break;
			}
		}
		return $r;
	}

	function getCouponFeed()
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", );
		$check_date = date('Y-m-d H:i:s');

		
		 
		//get feed data
		$resultsperpage = 100;
		$page = 1;
		//$promotiontype = '1|2|3|5|6|7|8|9|11|12|13|14|30|31';
		
		
		do{
		    
		    //get token key
		    $url = "https://api.rakutenmarketing.com/token";
		    $request = array(
		        "AffId" => $this->info["AffId"],
		        "method" => "post",
		        "postdata" => 'grant_type=password&username='.$this->info["Account"].'&password='.$this->info["Password"].'&scope='.UID,
		        "addheader" => array("Authorization:Basic ".AUTH_TOKEN)
		    );
		    $r = $this->GetHttpResult_2($url, $request);
		    if($r['code'] == 200){
		        $tokenArr = json_decode($r['content'],true);
		    }
		    //get access key
		    $request = array(
		        "AffId" => $this->info["AffId"],
		        "method" => "post",
		        "postdata" => "grant_type=refresh_token&refresh_token={$tokenArr['refresh_token']}&scope=PRODUCTION",
		        "addheader" => array("Authorization:Basic ".AUTH_TOKEN)
		    );
		    $autoInfo = $this->GetHttpResult_2($url, $request);
		    if($autoInfo['code'] == 200){
		        $accessArr = json_decode($autoInfo['content'],true);
		    }
		    
		    //$feedUrl = "https://api.rakutenmarketing.com/coupon/1.0?promotiontype=".urlencode($promotiontype)."&resultsperpage=$resultsperpage&pagenumber=$page";
		    $feedUrl = "https://api.rakutenmarketing.com/coupon/1.0?resultsperpage=$resultsperpage&pagenumber=$page";
		    echo $feedUrl.PHP_EOL;
		    $request = array(
		        "AffId" => $this->info["AffId"],
		        "method" => "get",
		        "postdata" => "",
		        "addheader" => array('authorization:Bearer '.$accessArr['access_token'])
		    );
		    $responseData = $this->GetHttpResult_2($feedUrl, $request);
		    
		    if ($responseData['code']!= 200 || empty($responseData['content']))
		        break;
		    
		    $dom = new DomDocument();
		    @$dom->loadXML($responseData['content']);
		    $data = @XML2Array::createArray($dom);
		    
		    if(empty($data['couponfeed']) || $data['couponfeed']['TotalMatches']==0)
		        break;
		    $total = $data['couponfeed']['TotalPages'];
		    
		    
		    foreach ($data['couponfeed']['link'] as $v)
			{
				$link = array(
						"AffId" => $this->info["AffId"],
						"AffMerchantId" => $v['advertiserid'] . '_' . $v['network']['@attributes']['id'],
						"LinkName" => html_entity_decode($v['offerdescription']),
						"LinkDesc" => '',
						"LinkStartDate" => parse_time_str($v['offerstartdate'], null, false),
						"LinkEndDate" => parse_time_str($v['offerenddate'], null, false),
						"LinkPromoType" => 'DEAL',
						"LinkHtmlCode" => '',
						"LinkCode" => '',
						"LinkOriginalUrl" => '',
						"LinkImageUrl" => '',
						"LinkAffUrl" => html_entity_decode($v['clickurl']),
						"DataSource" => 2,
				        "IsDeepLink" => 'UNKNOWN',
				        "Type"       => 'promotion'
				);
				if (!empty($v['couponrestriction']))
					$link['LinkDesc'] .= $v['couponrestriction'];
				
				 
				if (!empty($v['couponcode']))
					$link['LinkCode'] = $v['couponcode'];
				
				$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
				
				if (!empty($v['imageurl']))
					$link['LinkImageUrl'] = $v['imageurl'];
				if (preg_match('@\&offerid=(\d+\.\d+)\&@', $link['LinkAffUrl'], $g))
					$link['AffLinkId'] = $g[1];
				$link['LinkHtmlCode'] = create_link_htmlcode_image($link);
				if (empty($link['AffLinkId']) || empty($link['LinkHtmlCode']) || empty($link['LinkName']))
					continue;
				$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
				$arr_return["AffectedCount"] ++;
				$links[] = $link;
			}
			
			echo sprintf("page:%s, %s links(s) found. \n", $page, count($links));
			if(count($links) > 0)
			    $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		    
		    $links = array();
		    $page ++;
		} while ($page <= $total);
		
		 
        $this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'promotion');
		return $arr_return;
	}
	
	function GetAllProductsByAffId()
	{
	     
	    $check_date = date('Y-m-d H:i:s');
	    $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
	    
	    $arr_merchant = $this->oLinkFeed->getApprovalAffMerchant($this->info["AffId"]);
	    $productNumConfig = $this->oLinkFeed->product_program_num_config($this->info["AffId"]);
	    $productNumConfigAlert = '';
	    $isAssignMerchant = FALSE;
	    $mcount = 0;
	    
	    foreach ($arr_merchant as $merchatInfo){
	        
	        
	        $PageNumber = 0;
	        $limitPageNum = 100;
	        $crawlMerchantsActiveNum = 0;
	        $setMaxNum  = isset($productNumConfig[$merchatInfo['IdInAff']]) ? $productNumConfig[$merchatInfo['IdInAff']]['limit'] :  100;
	        $isAssignMerchant = isset($productNumConfig[$merchatInfo['IdInAff']]) ? TRUE : FALSE;
	    
	        $mid =  explode('_', $merchatInfo['IdInAff']);
	        //$feedUrl = "https://api.rakutenmarketing.com/coupon/1.0?promotiontype=".urlencode($promotiontype)."&resultsperpage=$resultsperpage&pagenumber=$page";
	        do{
	            
	            $PageNumber ++;
	            
	            //get token key
	            $url = "https://api.rakutenmarketing.com/token";
	            $request = array(
	                "AffId" => $this->info["AffId"],
	                "method" => "post",
	                "postdata" => 'grant_type=password&username='.$this->info["Account"].'&password='.$this->info["Password"].'&scope='.UID,
	                "addheader" => array("Authorization:Basic ".AUTH_TOKEN)
	            );
	            $r = $this->GetHttpResult_2($url, $request);
	            if($r['code'] == 200){
	                $tokenArr = json_decode($r['content'],true);
	            }
	            //get access key
	            $request = array(
	                "AffId" => $this->info["AffId"],
	                "method" => "post",
	                "postdata" => "grant_type=refresh_token&refresh_token={$tokenArr['refresh_token']}&scope=PRODUCTION",
	                "addheader" => array("Authorization:Basic ".AUTH_TOKEN)
	            );
	            $autoInfo = $this->GetHttpResult_2($url, $request);
	            if($autoInfo['code'] == 200){
	                $accessArr = json_decode($autoInfo['content'],true);
	            }
	            
	            $feedUrl = "https://api.rakutenmarketing.com/productsearch/1.0?&pagenumber=$PageNumber&max=$limitPageNum&mid={$mid[0]}&sort=productname&sorttype=asc";
	            echo $feedUrl.PHP_EOL;
	            $request = array(
	                "AffId" => $this->info["AffId"],
	                "method" => "get",
	                "postdata" => "",
	                "addheader" => array('authorization:Bearer '.$accessArr['access_token'])
	            );
	            $responseData = $this->GetHttpResult_2($feedUrl, $request);
	             
	            if ($responseData['code']!= 200 || empty($responseData['content'])){
	                print_r($responseData);
	                continue;
	            }
	             
	            $dom = new DomDocument();
	            @$dom->loadXML($responseData['content']);
	            $data = @XML2Array::createArray($dom);
	             
	            $TotalMatches = $data['result']['TotalMatches'];
	            $TotalPages = $data['result']['TotalPages'];
	             
	             
	            if(isset($data['result']['item']) && $data['result']['item']){
	                foreach ($data['result']['item'] as $value){
	                    
	                    if(!isset($value['linkid']) || !isset($value['imageurl'])){
	                        continue;
	                    }
	                     
	                    //下载图片
	                    $product_path_file = $this->oLinkFeed->getProductImgFilePath($this->info["AffId"],"{$merchatInfo['IdInAff']}_".urlencode($value['linkid']).".png", PRODUCTDIR);
	                    if(!$this->oLinkFeed->fileCacheIsCached($product_path_file)){
	                        $file_content = $this->oLinkFeed->downloadImg($value['imageurl']);
	                        if(!$file_content) //下载不了跳过。
	                            continue;
	                        $this->oLinkFeed->fileCachePut($product_path_file, $file_content);
	            
	                    }
	                    if(!isset($value['productname']) || empty($value['productname']) || !isset($value['linkid'])){
	                        continue;
	                    }
	                     
	                    $link = array(
	                        "AffId" => $this->info["AffId"],
	                        "AffMerchantId" => $merchatInfo['IdInAff'],
	                        "AffProductId" => $value['linkid'],
	                        "ProductName" => addslashes($value['productname']),
	                        "ProductCurrency" =>$value['price']['@attributes']['currency'],
	                        "ProductPrice" =>$value['saleprice']['@value'],
	                        "ProductOriginalPrice" =>$value['price']['@value'],
	                        "ProductRetailPrice" =>'',
	                        "ProductImage" => addslashes($value['imageurl']),
	                        "ProductLocalImage" => addslashes($product_path_file),
	                        "ProductUrl" => addslashes($value['linkurl']),
	                        "ProductDestUrl" => '',
	                        "ProductDesc" => addslashes($value['description']['long']),
	                        "ProductStartDate" => '',
	                        "ProductEndDate" => '',
	                    );
	                    $crawlMerchantsActiveNum ++;
	                    $links[] = $link;
	                    $arr_return['AffectedCount'] ++;
	                }
	            }
	            if (count($links))
	            {
	                $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateProductToDB($links);
	                $links = array();
	                //echo sprintf("get product complete. %s links(s) found. \n", $arr_return["UpdatedCount"]);
	            }
	            //大于最大数跳出
	            if($crawlMerchantsActiveNum>=$setMaxNum){
	                break;
	            }
	            
	            
	        }while($PageNumber <= $TotalPages);
	        if($isAssignMerchant){
	            $productNumConfigAlert .= "AFFID:".$this->info["AffId"].",Program({$merchatInfo['MerchantName']}),Crawl Count($crawlMerchantsActiveNum),Total Count({$TotalMatches}) \r\n";
	        }
	        $mcount ++;
	        
	    }
	    echo 'merchant count:'.$mcount.PHP_EOL;
	    $this->oLinkFeed->checkProductExists($this->info["AffId"], $check_date);
	    echo $productNumConfigAlert.PHP_EOL;
	    echo 'END time'.date('Y-m-d H:i:s').PHP_EOL;
	    return $arr_return;
	}

	function getMessage()
	{
		$messages = array();
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", );
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);
		$url = 'http://cli.linksynergy.com/cli/publisher/messages/generalMessages.php?folderID=1';
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r['content'];
		if (preg_match('@<tbody>(.*?)</tbody>@ms', $content, $g))
			$content = $g[1];
		else 
			return 'parse html error.';
		preg_match_all('@<tr.*?>(.*?)</tr>@ms', $content, $chapters);
		if (empty($chapters) || !is_array($chapters) || empty($chapters[1]) || !is_array($chapters[1]))
			return 'no message found.';
		foreach ($chapters[1] as $chapter)
		{
			$data = array(
					'affid' => $this->info["AffId"],
					'messageid' => '',
					'sender' => '',
					'title' => '',
					'content' => '',
					'created' => '0000-00-00',
			);
			if (preg_match('@td_180_left">(.*?)</td>@', $chapter, $g))
				$data['sender'] = trim(html_entity_decode(strip_tags($g[1])));
			if (preg_match('@select_emailid\[\]" value="(\d+)"@', $chapter, $g))
				$data['messageid'] = $g[1];
			if (preg_match('@td_auto_left"><a href="(.*?)">(.*?)</a>@', $chapter, $g))
			{
				$data['content_url'] = $g[1];
				$data['title'] = trim(html_entity_decode(strip_tags($g[2])));;
			}
			if (preg_match('@"td_80">(.*?)<@', $chapter, $g))
				$data['created'] = parse_time_str($g[1], 'm-d-Y', false);
			if (empty($data['messageid']) || empty($data['title']))
				continue;
			$messages[] = $data;
		}
		return $messages;
	}

	function getMessageDetail($data)
	{
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", );
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);
		$url = $data['content_url'];
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r['content'];
		if (preg_match('@<b>Subject:.*?<div class="divider"></div>(.*?)</div>\s+</div>\s+<div class="commonBoxFooter">@ms', $content, $g))
			$data['content'] = str_force_utf8(trim(html_entity_decode($g[1])));
		if (preg_match('@iframe src="(.*?)"@ms', $data['content'], $g))
		{
			$url = $g[1];
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			if (!empty($r['content']))
				$data['content'] = $r['content'];
		}
		return $data;
	}

	function GetProgramFromAff()
	{	
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";	
		//$this->settermforexcel();
		//$this->writetoCSV();
		$this->GetProgramFromByPage();		
		$this->GetPolicyFromByPage();		
		$this->cache = json_encode($this->cache);
		$this->oLinkFeed->fileCachePut($this->cache_file, $this->cache);
		$this->checkProgramOffline($this->info["AffId"], $check_date);
		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}
	
	function GetPolicyFromByPage()
	{
		echo "\tGet Policy start\r\n";
		
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$use_true_file_name = true;
		$program_num = 0;
		$request = array("AffId" => $this->info["AffId"], "method" => "get");
		//step 1, login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		
		$sql = 'select id, IdInAff from program where affid = 2';
		$check_prgm = array();
		$check_prgm = $objProgram->objMysql->getRows($sql);
		foreach($check_prgm as $v){
			$IdInAff = intval(substr($v['IdInAff'], 0, strpos($v['IdInAff'], '_')));
			if(!$IdInAff) continue;
			
			if(!isset($this->cache[$v['IdInAff']]["allow"])){
				$prgm_url = "http://cli.linksynergy.com/cli/publisher/programs/Policies/coupons.php?&mid=$IdInAff";
				$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);	
				if($prgm_arr['code'] == 200){
					$results = $prgm_arr['content'];
					$this->cache[$v['IdInAff']]["allow"] = "1";
				}else{
					continue;
				}
			}else{
				continue;
			}
			
			$AllowNonaffCoupon = 'UNKNOWN';
						
			$tmp_txt = $this->oLinkFeed->ParseStringBy2Tag($results, array('Allows use of coupon codes available to the public (i.e. coupon codes that are NOT exclusive to your affiliate program)?', '<img', 'src=\''), '\'');
		
			if(strpos($tmp_txt, 'green') !== false){
				$AllowNonaffCoupon = 'YES';
			}elseif(strpos($tmp_txt, 'red') !== false){				
				$AllowNonaffCoupon = 'NO';
			}
			
			if($AllowNonaffCoupon == 'YES' || $AllowNonaffCoupon == 'NO'){
				$arr_prgm[$v['IdInAff']] = array(
										"AffId" => $this->info["AffId"],
										"IdInAff" => $v['IdInAff'],
										"AllowNonaffCoupon" => $AllowNonaffCoupon
										);
				$program_num++;
			}			
			
			if(count($arr_prgm) >= 100){
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);				
				$arr_prgm = array();
			}			
		}
		
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);				
			$arr_prgm = array();
		}
		
		echo "\tGet Policy finished($program_num)\r\n";
	}

	function GetProgramFromByPage()
	{
		echo "\tGet Program by page start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		$working_dir = $this->oLinkFeed->getWorkingDirByAffID($this->info["AffId"]);
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "",);
		$country_arr = array();

		//step 1, login
		echo "login to affservice\n\t";
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		
		//step 2, get program from csv.
		echo "start get program from csv";
		$exist_prgm = array();
		$str_header = '"Advertiser Name","Advertiser URL","MID","Advertiser Description","Link to T&C","Link to Program History","Link to Home Page","Status","Contact Name","Contact Title","State","City","Address","Zip","Country","Phone","Email Address","Commission Terms","Offer","Offer Type","Year Joined","Expiration Date","Return Days","Transaction Update Window","TrueLock","Premium Status","Baseline Commission Terms","Baseline Offer","Baseline Offer Type","Baseline Expiration Date","Baseline Return Days","Baseline Transaction Update Window","Baseline TrueLock"';
		$cache_filecsv = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"caReport.csv","cache_merchant");
		if(!$this->oLinkFeed->fileCacheIsCached($cache_filecsv))
		{
			$strUrl = "http://cli.linksynergy.com/cli/publisher/programs/consolidatedAdvertiserReport.php";
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$result = $r["content"];
			$file_id = intval($this->oLinkFeed->ParseStringBy2Tag($result, "http://cli.linksynergy.com/cli/publisher/programs/carDownload.php?id=", "'"));

			$strUrl = "http://cli.linksynergy.com/cli/publisher/programs/carDownload.php?id=$file_id";
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$result = $r["content"];
			print "Get files <br>\n";
			if(stripos($result,$str_header) === false) mydie("die: wrong csv file: $cache_filecsv");
			$this->oLinkFeed->fileCachePut($cache_filecsv,$result);
		}

		echo "csv file has download.\n\t";
		
		$handle = fopen($cache_filecsv, 'r');
		while($line = fgetcsv ($handle, 5000))
		{
			foreach($line as $k => $v) $line[$k] = trim($v);
			if ($line[0] == '' || $line[0] == 'Advertiser Name') continue;
			if(!isset($line[2])) continue;
			if(!isset($line[5])) continue;

			$strMerName = $line[0];
			$Homepage = $line[1];
			$strTmpMerID = $line[2];
			$desc = $line[3];
			//$Term_url = $line[4];
			$Offer_url = $line[5];
			$AffDefaultUrl = $line[6];
			$StatusInAffRemark = $line[7];
			$Contact_Name = $line[8];
			$Contact_Title = $line[9];
			$Contact_State = $line[10];
			$Contact_City = $line[11];
			$Contact_Address = $line[12];
			$Contact_Zip = $line[13];
			$Contact_Country = $line[14];
			$Contact_Phone = $line[15];
			$Contact_Email = $line[16];
			$CommissionExt = $line[17];
			$Offer = $line[18];
			$Offer_Type = $line[19];
			$JoinDate = $line[20];
			//$Expiration = $line[21];
			$ReturnDays = $line[22];
			$Contact_Zip = $line[23];
			$Contact_Country = $line[24];
			$Premium_Status = $line[25];

			preg_match("/nid=(\d+)/i", $Offer_url, $matches);
			$nid = $matches[1];
			preg_match("/offerid=(\d+)/i", $AffDefaultUrl, $matches);
			$strTmpOfferID = $matches[1];

			$prgm_url = "http://cli.linksynergy.com/cli/publisher/programs/advertiser_detail.php?oid=$strTmpOfferID&mid=$strTmpMerID&offerNid=$nid&controls=1:1:1:1:1:0";
			$strMerID = $strTmpMerID."_".$nid;

			if($StatusInAffRemark == "Active"){
				$StatusInAff = "Active";
				$Partnership = "Active";
			}elseif($StatusInAffRemark == "Declined"){
				$StatusInAff = "Active";
				$Partnership = "Declined";
			}elseif($StatusInAffRemark == "Hold"){
				$StatusInAff = "Offline";
				$Partnership = "Active";
			}else{
				$StatusInAff = "Offline";
				$Partnership = "NoPartnership";
			}

			$Contacts = "$Contact_Name($Contact_Title), Email: $Contact_Email, Phone: $Contact_Phone, Zip: $Contact_Zip, Address: $Contact_State $Contact_City $Contact_Address  $Contact_Country.";
			$JoinDate = $JoinDate. "-01-01 00:00:00";
			$RankInAff = 3;
			if($Premium_Status == "Premium"){
				$RankInAff = 5;
			}

			//program
			$arr_prgm[$strMerID] = array(
				"Name" => addslashes(trim($strMerName)),
				"AffId" => $this->info["AffId"],
				"Homepage" => addslashes($Homepage),
				"Contacts" => addslashes($Contacts),
				"TargetCountryExt" => '',
				"RankInAff" => $RankInAff,
				"IdInAff" => $strMerID,
				"JoinDate" => $JoinDate,
				//"TermAndCondition" => '',
				"StatusInAff" => $StatusInAff,						//'Active','TempOffline','Offline'
				"StatusInAffRemark" => addslashes($StatusInAffRemark),
				"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','Removed'
				"Description" => addslashes($desc),
				"CommissionExt" => addslashes($CommissionExt),
				"CookieTime" => $ReturnDays,
				"LastUpdateTime" => date("Y-m-d H:i:s"),
				"SecondIdInAff" => $strTmpOfferID,
				"DetailPage" => $prgm_url,
				"AffDefaultUrl" => $AffDefaultUrl,
				//"SupportDeepUrl" => 'UNKNOWN',
				//'CategoryExt' => "",
			    'MobileFriendly' => 'UNKNOWN'
			);
			/*$prgm_detail = '';
			$detail_url = "http://cli.linksynergy.com/cli/publisher/programs/advertiserInformationFrame.php?oid=$strTmpOfferID&mid=$strTmpMerID&offerNid=$nid&controls=1:1:3";
			if(!isset($this->cache[$strMerID]["detail"])){
				$prgm_arr = $this->oLinkFeed->GetHttpResult($detail_url, $request);
				if($prgm_arr['code'] == 200){
					$prgm_detail = $prgm_arr["content"];
					$this->cache[$strMerID]["detail"] = "1";
				
					$strMerFlag = strtoupper($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<!-- Flags -->', 'images/common/flag_'), "."));
					
					$url = "http://cli.linksynergy.com/cli/publisher/programs/shipping_availability.php?mid=$strTmpMerID";
					$Country_r = $this->oLinkFeed->GetHttpResult($url,$request);
					$Country_result = $Country_r["content"];
					$Country_re = preg_match_all('/<td>(.*?)<\/td>/',$Country_result,$matches);
					foreach ($matches[1] as $ke => $m){
						if(empty($m))
							unset($matches[1][$ke]);
					}
					$TargetCountryExt = implode(',', $matches[1]);
					
					$strMerName = trim($strMerName) . ' ('.$strMerFlag.') ';
					$arr_prgm[$strMerID]['TargetCountryExt'] = $TargetCountryExt;
					$arr_prgm[$strMerID]['Name'] = addslashes(trim($strMerName));
				}
			}*/
			
			$arr_prgm[$strMerID]['TargetCountryExt'] = '';
			if(!isset($country_arr[$strTmpMerID])){
				$url = "http://cli.linksynergy.com/cli/publisher/programs/shipping_availability.php?mid=$strTmpMerID";
				$Country_r = $this->oLinkFeed->GetHttpResult($url, $request);
				$Country_result = $Country_r["content"];
				$Country_re = preg_match_all('/<td>(.*?)<\/td>/',$Country_result, $matches);
				foreach ($matches[1] as $ke => $m){
					if(empty($m))
						unset($matches[1][$ke]);
				}
				if(count($matches[1])){
					$TargetCountryExt = implode(',', $matches[1]);
					$arr_prgm[$strMerID]['TargetCountryExt'] = $TargetCountryExt;
					$country_arr[$strTmpMerID] = $TargetCountryExt;
				}
			}else{
				$arr_prgm[$strMerID]['TargetCountryExt'] = $TargetCountryExt;
			}
			
			$more_info = $this->getSupportDUT($strTmpMerID, $strTmpOfferID, $request, true, $strMerID);
			if(isset($more_info['CategoryExt'])){
				$arr_prgm[$strMerID]['CategoryExt'] = addslashes($more_info['CategoryExt']);
			}
			if(isset($more_info['TermAndCondition'])){
				$arr_prgm[$strMerID]['TermAndCondition'] = addslashes($more_info['TermAndCondition']);
			}
			$arr_prgm[$strMerID]['SupportDeepUrl'] = $more_info['SupportDeepUrl'];
			
			$MobileFriendly = $this->getMobileFriendly($strTmpMerID, $strMerID);
			if(!empty($MobileFriendly)){
            	$arr_prgm[$strMerID] = array_merge($MobileFriendly, $arr_prgm[$strMerID]);
			}
			$exist_prgm[$strMerID] = 1;
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
		fclose($handle);
		echo "Finish get program from csv";

		//step 3, Get all new merchants
		echo "Get all new merchants";
		$request["method"] = "get";
		$nNumPerPage = 100;
		$bHasNextPage = true;
		$nPageNo = 1;
		$arr_prgm = array();
		$Cnt = 0;
		$UpdateCnt = 0;
		while($bHasNextPage){
			if($nPageNo != 1){				
				$request["method"] = "post";
				$request["postdata"] = "__csrf_magic=".urlencode($__csrf_magic)."&analyticchannel=&analyticpage=&singleApply=&update=&remove_mid=&remove_oid=&remove_nid=&filter_open=&cat=&advertiserSearchBox=&category=-1&filter_status=all&filter_networks=all&filter_type=all&filter_banner_size=-1&orderby=&direction=&currec=".($nNumPerPage * ($nPageNo - 1) + 1)."&pagesize=".$nNumPerPage;
			}
			$strUrl = "http://cli.linksynergy.com/cli/publisher/programs/advertisers.php";						
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$result = $r["content"];
		
			$__csrf_magic = $this->oLinkFeed->ParseStringBy2Tag($result, array("name='__csrf_magic'", 'value="'), '"');

			print "Get Merchant List new : Page - $nPageNo <br>\n";
			//parse HTML
			$strLineStart = '<td class="td_left_edge">';
			$nLineStart = 0;
			while ($nLineStart >= 0){
				if($this->debug) print "Process $Cnt  ";

				$nLineStart = stripos($result, $strLineStart, $nLineStart);
				if ($nLineStart === false) break;

				// ID 	Name 	EPC 	Status
				$strMerID = $this->oLinkFeed->ParseStringBy2Tag($result, array('select_mid[]', 'value="'), '"', $nLineStart);
				//$strMerID = str_replace('~', '_', $strMerID);
				//LogoUrl
				$LogoUrl = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('<img ', 'src="'), '"', $nLineStart));
				
				list($strTmpMerID, $strTmpOfferID, $strTmpNetworkID) = explode('~', $strMerID);
				$strMerID = $strTmpMerID.'_'.$strTmpNetworkID;
				if(isset($exist_prgm[$strMerID])) continue;
				$strMerName = $this->oLinkFeed->ParseStringBy2Tag($result, 'helpMessage(this);">', '</a>', $nLineStart);
				//us or uk
				$strMerFlag = strtoupper($this->oLinkFeed->ParseStringBy2Tag($result, 'images/common/flag_', ".", $nLineStart));
				//$TargetCountryExt = $strMerFlag;
				//$strMerName = html_entity_decode($strMerName);
				$strMerName = trim($strMerName) . ' ('.$strMerFlag.') ';
				//desc
				$desc = strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, "</span>", '<img src', $nLineStart));
				$aff_mer_url = $this->oLinkFeed->ParseStringBy2Tag($result, array('images/arrows/caret.gif" alt="Arrow">', '<a href="'), '">View Links</a>', $nLineStart);
				//join date
				$JoinDate = $this->oLinkFeed->ParseStringBy2Tag($result, array('<td class="td_date_joined">'), '</td>', $nLineStart);
				$JoinDate = $JoinDate. "-01-01 00:00:00";
				//CommissionExt
				$CommissionExt = $this->oLinkFeed->ParseStringBy2Tag($result, array('<td class="td_commission">'), '</td>', $nLineStart);
				//ReturnDays
				$ReturnDays = $this->oLinkFeed->ParseStringBy2Tag($result, array('<td class="td_return">'), '</td>', $nLineStart);
				//class="td_status" or class="td_status_temp"
				$strStatusShow = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td class="td_status', "<br>", $nLineStart));
				$strStatus = $this->getStatusByStr($strStatusShow);
				if($strStatus === false)
				{
					print_r($result);
					mydie("Unknown Status : $strStatusShow <br>\n");
				}
				$StatusInAffRemark = strip_tags(str_ireplace(array('">','_temp">'),"",$strStatusShow));
				//$StatusInAff = $strStatus;//'Active','TempOffline','Expired'
				if(stripos($strStatusShow,'Approved') !== false){
					$Partnership = "Active";
					$StatusInAff = "Active";
				}
				elseif(stripos($strStatusShow,'Pending') !== false){
					$Partnership = "Pending";
					$StatusInAff = "Active";
				}
				elseif(stripos($strStatusShow,'No Relationship') !== false){
					$Partnership = "NoPartnership";
					$StatusInAff = "Active";
				}
				elseif(stripos($strStatusShow, "Declined") !== false){
					$Partnership = "Declined";
					$StatusInAff = "Active";
				}
				elseif(stripos($strStatusShow, "Removed") !== false){
					$Partnership = "Removed";
					$StatusInAff = "Active";
				}
				elseif(stripos($strStatusShow, "Discontinued") !== false){
					$Partnership = "NoPartnership";
					$StatusInAff = "TempOffline";
				}
				elseif(stripos($strStatusShow, "Extended") !== false){
					$Partnership = "Active";
					$StatusInAff = "Active";
				}
				else{
					$Partnership = "NoPartnership";
					$StatusInAff = "Active";
				}
				
				$TargetCountryExt = '';
				if(!isset($country_arr[$strTmpMerID])){
					$url = "http://cli.linksynergy.com/cli/publisher/programs/shipping_availability.php?mid=$strTmpMerID";
					$Country_r = $this->oLinkFeed->GetHttpResult($url, $request);
					$Country_result = $Country_r["content"];
					$Country_re = preg_match_all('/<td>(.*?)<\/td>/',$Country_result, $matches);
					foreach ($matches[1] as $ke => $m){
						if(empty($m))
							unset($matches[1][$ke]);
					}
					if(count($matches[1])){
						$TargetCountryExt = implode(',', $matches[1]);						
						$country_arr[$strTmpMerID] = $TargetCountryExt;
					}
				}else{
					$TargetCountryExt = $country_arr[$strTmpMerID];
				}
				
				$prgm_url = "http://cli.linksynergy.com/cli/publisher/programs/advertiser_detail.php?oid=$strTmpOfferID&mid=$strTmpMerID&offerNid=$strTmpNetworkID&controls=1:1:1:1:1:0";
				//program
				$arr_prgm[$strMerID] = array(
					"Name" => addslashes(trim($strMerName)),
					"AffId" => $this->info["AffId"],
					//"Homepage" => '',
					//"CategoryExt" => "",
					//"Contacts" => '',
					"TargetCountryExt" => $TargetCountryExt,
					"RankInAff" => 3,
					"IdInAff" => $strMerID,
					"JoinDate" => $JoinDate,
					"CreateDate" => "0000-00-00 00:00:00",
					"DropDate" => "0000-00-00 00:00:00",
					//"TermAndCondition" => '',
					"StatusInAff" => $StatusInAff,						//'Active','TempOffline','Offline'
					"StatusInAffRemark" => addslashes($StatusInAffRemark),
					"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','Removed'					
					"Description" => $desc,
					"CommissionExt" => addslashes($CommissionExt),
					"CookieTime" => $ReturnDays,
					"LastUpdateTime" => date("Y-m-d H:i:s"),
					"SecondIdInAff" => $strTmpOfferID,
					"DetailPage" => $prgm_url,
				    'MobileFriendly' => 'UNKNOWN',
					'LogoUrl' => $LogoUrl,
					 
				);

				//program_detail
				
				$more_info = $this->getSupportDUT($strTmpMerID, $strTmpOfferID, $request, true, $strMerID);
				$arr_prgm[$strMerID]['SupportDeepUrl'] = $more_info['SupportDeepUrl'];
				if(isset($more_info['CategoryExt'])){
					$arr_prgm[$strMerID]['CategoryExt'] = addslashes($more_info['CategoryExt']);
				}
				if(isset($more_info['TermAndCondition'])){
					$arr_prgm[$strMerID]['TermAndCondition'] = addslashes($more_info['TermAndCondition']);
				}
				if(isset($more_info['Homepage'])){
					$arr_prgm[$strMerID]['Homepage'] = addslashes($more_info['Homepage']);
				}
				if(isset($more_info['Contacts'])){
					$arr_prgm[$strMerID]['Contacts'] = addslashes($more_info['Contacts']);
				}
				$MobileFriendly = $this->getMobileFriendly($strTmpMerID, $strMerID);
				if(!empty($MobileFriendly)){
					$arr_prgm[$strMerID] = array_merge($MobileFriendly, $arr_prgm[$strMerID]);
				}
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
			//Check if have next page;
			if (false === $this->oLinkFeed->ParseStringBy2Tag($result, "document.myform.submit();return false;'>Next", '</a></div></div>', $nLineStart))
			{
				$bHasNextPage = false;
				if($this->debug) print " NO NEXT PAGE  <br>\n";
			}
			else{
				if($this->debug) print " Have NEXT PAGE  <br>\n";
			}
			if ($bHasNextPage){
				$nPageNo++;
			}
		}//per page

		//step 4, Get all my merchants
		$request["method"] = "get";
		$nNumPerPage = 100;
		$bHasNextPage = true;
		$nPageNo = 1;
		$arr_prgm = array();
		
		$Cnt = 0;
		$UpdateCnt = 0;
		while($bHasNextPage){			
			if($nPageNo != 1){				
				$request["method"] = "post";
				$request["postdata"] = "__csrf_magic=".urlencode($__csrf_magic)."&analyticchannel=Programs&analyticpage=My+Advertisers&singleApply=&update=&remove_mid=&remove_oid=&remove_nid=&filter_open=&cat=&advertiserSerachBox_old=&advertiserSerachBox=&category=-1&filter_networks=all&filter_promotions=-1&filter_type=all&filter_banner_size=+--+All+Sizes+--&my_programs=1&filter_status_program=all&orderby=&direction=&currec=".($nNumPerPage * ($nPageNo - 1) + 1)."&pagesize=".$nNumPerPage;
			}			
			
			$strUrl = "http://cli.linksynergy.com/cli/publisher/programs/advertisers.php?my_programs=1";			
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$result = $r["content"];
		
			$__csrf_magic = $this->oLinkFeed->ParseStringBy2Tag($result, array("name='__csrf_magic'", 'value="'), '"');
			
			
			print "Get All My Merchant List - Apporved : Page - $nPageNo <br>\n";

			//parse HTML
			$strLineStart = '<td class="td_left_edge">';
			$nLineStart = 0;
			while ($nLineStart >= 0){
				if($this->debug) print "Process $Cnt  ";
				$nLineStart = stripos($result, $strLineStart, $nLineStart);
				if ($nLineStart === false) break;
				// ID 	Name 	EPC 	Status
				$strMerID = $this->oLinkFeed->ParseStringBy2Tag($result, array('select_mid[]', 'value="'), '"', $nLineStart);
				//LogoUrl
				$LogoUrl = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('<img ', 'src="'), '"', $nLineStart));

				//$strMerID = str_replace('~', '_', $strMerID);
				list($strTmpMerID, $strTmpOfferID, $strTmpNetworkID) = explode('~', $strMerID);
				$strMerID = $strTmpMerID.'_'.$strTmpNetworkID;
				if(isset($exist_prgm[$strMerID])) continue;
				$strMerName = $this->oLinkFeed->ParseStringBy2Tag($result, 'helpMessage(this);">', '</a>', $nLineStart);
				//us or uk
				$strMerFlag = strtoupper($this->oLinkFeed->ParseStringBy2Tag($result, 'images/common/flag_', ".", $nLineStart));
				//$TargetCountryExt = $strMerFlag;

				//$strMerName = html_entity_decode($strMerName);
				$strMerName = trim($strMerName) . ' ('.$strMerFlag.') ';
				//desc
				$desc = strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, "</span>", '<img src', $nLineStart));
				$aff_mer_url = $this->oLinkFeed->ParseStringBy2Tag($result, array('images/arrows/caret.gif" alt="Arrow">', '<a href="'), '">View Links</a>', $nLineStart);
				//join date
				$JoinDate = $this->oLinkFeed->ParseStringBy2Tag($result, array('<td class="td_date_joined">'), '</td>', $nLineStart);
				$JoinDate = $JoinDate. "-01-01 00:00:00";
				//CommissionExt
				$CommissionExt = $this->oLinkFeed->ParseStringBy2Tag($result, array('<td class="td_commission">'), '</td>', $nLineStart);
				//ReturnDays
				$ReturnDays = $this->oLinkFeed->ParseStringBy2Tag($result, array('<td class="td_return">'), '</td>', $nLineStart);

				//class="td_status" or class="td_status_temp"
				$strStatusShow = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td class="td_status', "<br>", $nLineStart));
				$strStatus = $this->getStatusByStr($strStatusShow);
				if($strStatus === false)
				{
					print_r($result);
					mydie("Unknown Status : $strStatusShow <br>\n");
				}

				$StatusInAffRemark = strip_tags(str_ireplace(array('">','_temp">'),"",$strStatusShow));
				//$StatusInAff = $strStatus;//'Active','TempOffline','Expired'
				if(stripos($strStatusShow,'Approved') !== false){
					$Partnership = "Active";
					$StatusInAff = "Active";
				}
				elseif(stripos($strStatusShow,'Pending') !== false){
					$Partnership = "Pending";
					$StatusInAff = "Active";
				}
				elseif(stripos($strStatusShow,'No Relationship') !== false){
					$Partnership = "NoPartnership";
					$StatusInAff = "Active";
				}
				elseif(stripos($strStatusShow, "Declined") !== false){
					$Partnership = "Declined";
					$StatusInAff = "Active";
				}
				elseif(stripos($strStatusShow, "Removed") !== false){
					$Partnership = "Removed";
					$StatusInAff = "Active";
				}
				elseif(stripos($strStatusShow, "Discontinued") !== false){
					$Partnership = "NoPartnership";
					$StatusInAff = "TempOffline";
				}
				elseif(stripos($strStatusShow, "Extended") !== false){
					$Partnership = "Active";
					$StatusInAff = "Active";
				}
				else{
					$Partnership = "NoPartnership";
					$StatusInAff = "Active";
				}

				$TargetCountryExt = '';
				if(!isset($country_arr[$strTmpMerID])){
					$url = "http://cli.linksynergy.com/cli/publisher/programs/shipping_availability.php?mid=$strTmpMerID";
					$Country_r = $this->oLinkFeed->GetHttpResult($url, $request);
					$Country_result = $Country_r["content"];
					$Country_re = preg_match_all('/<td>(.*?)<\/td>/',$Country_result, $matches);
					foreach ($matches[1] as $ke => $m){
						if(empty($m))
							unset($matches[1][$ke]);
					}
					if(count($matches[1])){
						$TargetCountryExt = implode(',', $matches[1]);						
						$country_arr[$strTmpMerID] = $TargetCountryExt;
					}
				}else{
					$TargetCountryExt = $country_arr[$strTmpMerID];
				}
				
				//program
				$arr_prgm[$strMerID] = array(
						"Name" => addslashes(trim($strMerName)),
						"AffId" => $this->info["AffId"],
						//"Homepage" => '',
						//"CategoryExt" => "",
						//"Contacts" => '',
						"TargetCountryExt" => $TargetCountryExt,
						"RankInAff" => 3,
						"IdInAff" => $strMerID,
						"JoinDate" => $JoinDate,
						"CreateDate" => "0000-00-00 00:00:00",
						"DropDate" => "0000-00-00 00:00:00",
						//"TermAndCondition" => '',
						"StatusInAff" => $StatusInAff,						//'Active','TempOffline','Offline'
						"StatusInAffRemark" => addslashes($StatusInAffRemark),
						"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','Removed'
						"Description" => $desc,
						"CommissionExt" => addslashes($CommissionExt),
						"CookieTime" => $ReturnDays,
						"LastUpdateTime" => date("Y-m-d H:i:s"),
						"SecondIdInAff" => $strTmpOfferID,
						"DetailPage" => '',
				        'MobileFriendly' => 'UNKNOWN',
						'LogoUrl' => $LogoUrl,
						
				);
				
				//program_detail
				$prgm_url = "http://cli.linksynergy.com/cli/publisher/programs/advertiser_detail.php?oid=$strTmpOfferID&mid=$strTmpMerID&offerNid=$strTmpNetworkID&controls=1:1:1:1:1:0";
				$arr_prgm[$strMerID]['DetailPage'] = $prgm_url;
				$more_info = $this->getSupportDUT($strTmpMerID, $strTmpOfferID, $request, true, $strMerID);
				$arr_prgm[$strMerID]['SupportDeepUrl'] = $more_info['SupportDeepUrl'];
				if(isset($more_info['CategoryExt'])){
					$arr_prgm[$strMerID]['CategoryExt'] = addslashes($more_info['CategoryExt']);
				}
				if(isset($more_info['TermAndCondition'])){
					$arr_prgm[$strMerID]['TermAndCondition'] = addslashes($more_info['TermAndCondition']);
				}
				if(isset($more_info['Homepage'])){
					$arr_prgm[$strMerID]['Homepage'] = addslashes($more_info['Homepage']);
				}
				if(isset($more_info['Contacts'])){
					$arr_prgm[$strMerID]['Contacts'] = addslashes($more_info['Contacts']);
				}
				$MobileFriendly = $this->getMobileFriendly($strTmpMerID, $strMerID);
				if(!empty($MobileFriendly)){
            		$arr_prgm[$strMerID] = array_merge($MobileFriendly, $arr_prgm[$strMerID]);
				}
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

			//Check if have next page;
			if (false === $this->oLinkFeed->ParseStringBy2Tag($result, "document.myform.submit();return false;'>Next", '</a></div></div>', $nLineStart)){
				$bHasNextPage = false;
				if($this->debug) print " NO NEXT PAGE  <br>\n";
			}
			else{
				if($this->debug) print " Have NEXT PAGE  <br>\n";
			}
			if ($bHasNextPage){
				$nPageNo++;
			}
		}//per page

		//step 5, Get all Premium merchants
		$nNumPerPage = 100;
		$bHasNextPage = true;
		$nPageNo = 1;
		$arr_prgm = array();
		$request["method"] = "get";
		$Cnt = 0;
		$UpdateCnt = 0;
		while($bHasNextPage){			
			if($nPageNo != 1){				
				$request["method"] = "post";
				$request["postdata"] = "__csrf_magic=".urlencode($__csrf_magic)."&analyticchannel=Programs&analyticpage=Premium+Advertisers&singleApply=&update=&remove_mid=&remove_oid=&remove_nid=&filter_open=&cat=&advertiserSerachBox_old=&advertiserSerachBox=&category=-1&filter_status=all&filter_networks=all&filter_promotions=-1&filter_type=all&filter_banner_size=+--+All+Sizes+--&orderby=&direction=&currec=".($nNumPerPage * ($nPageNo - 1) + 1)."&pagesize=".$nNumPerPage;
			}			
			$strUrl = "http://cli.linksynergy.com/cli/publisher/programs/advertisers.php?advertisers=1";
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$result = $r["content"];
		
			$__csrf_magic = $this->oLinkFeed->ParseStringBy2Tag($result, array("name='__csrf_magic'", 'value="'), '"');
			
			print "Get Merchant List - Premium : Page - $nPageNo <br>\n";

			//parse HTML
			$strLineStart = '<td class="td_left_edge">';

			$nLineStart = 0;
			while ($nLineStart >= 0){
				if($this->debug) print "Process $Cnt  ";

				$nLineStart = stripos($result, $strLineStart, $nLineStart);
				if ($nLineStart === false) break;
				// ID 	Name 	EPC 	Status
				$strMerID = $this->oLinkFeed->ParseStringBy2Tag($result, array('select_mid[]', 'value="'), '"', $nLineStart);
				//$strMerID = str_replace('~', '_', $strMerID);
				//LogoUrl
				$LogoUrl = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('<img ', 'src="'), '"', $nLineStart));
				list($strTmpMerID, $strTmpOfferID, $strTmpNetworkID) = explode('~', $strMerID);
				$strMerID = $strTmpMerID.'_'.$strTmpNetworkID;
				if(isset($exist_prgm[$strMerID])) continue;

				$strMerName = $this->oLinkFeed->ParseStringBy2Tag($result, 'helpMessage(this);">', '</a>', $nLineStart);
				//us or uk
				$strMerFlag = strtoupper($this->oLinkFeed->ParseStringBy2Tag($result, 'images/common/flag_', ".", $nLineStart));
				//$TargetCountryExt = $strMerFlag;
				//$strMerName = html_entity_decode($strMerName);
				$strMerName = trim($strMerName) . ' ('.$strMerFlag.') ';
				//desc
				$desc = strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, "</span>", '<img src', $nLineStart));
				$aff_mer_url = $this->oLinkFeed->ParseStringBy2Tag($result, array('images/arrows/caret.gif" alt="Arrow">', '<a href="'), '">View Links</a>', $nLineStart);
				//join date
				$JoinDate = $this->oLinkFeed->ParseStringBy2Tag($result, array('<td class="td_date_joined">'), '</td>', $nLineStart);
				$JoinDate = $JoinDate. "-01-01 00:00:00";
				//CommissionExt
				$CommissionExt = $this->oLinkFeed->ParseStringBy2Tag($result, array('<td class="td_commission">'), '</td>', $nLineStart);
				//ReturnDays
				$ReturnDays = $this->oLinkFeed->ParseStringBy2Tag($result, array('<td class="td_return">'), '</td>', $nLineStart);

				//class="td_status" or class="td_status_temp"
				$strStatusShow = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td class="td_status', "<br>", $nLineStart));
				$strStatus = $this->getStatusByStr($strStatusShow);
				if($strStatus === false)
				{
					print_r($result);
					mydie("Unknown Status : $strStatusShow <br>\n");
				}

				$StatusInAffRemark = strip_tags(str_ireplace(array('">','_temp">'),"",$strStatusShow));
				//$StatusInAff = $strStatus;//'Active','TempOffline','Expired'
				if(stripos($strStatusShow,'Approved') !== false){
					$Partnership = "Active";
					$StatusInAff = "Active";
				}
				elseif(stripos($strStatusShow,'Pending') !== false){
					$Partnership = "Pending";
					$StatusInAff = "Active";
				}
				elseif(stripos($strStatusShow,'No Relationship') !== false){
					$Partnership = "NoPartnership";
					$StatusInAff = "Active";
				}
				elseif(stripos($strStatusShow, "Declined") !== false){
					$Partnership = "Declined";
					$StatusInAff = "Active";
				}
				elseif(stripos($strStatusShow, "Removed") !== false){
					$Partnership = "Removed";
					$StatusInAff = "Active";
				}
				elseif(stripos($strStatusShow, "Discontinued") !== false){
					$Partnership = "NoPartnership";
					$StatusInAff = "TempOffline";
				}
				elseif(stripos($strStatusShow, "Extended") !== false){
					$Partnership = "Active";
					$StatusInAff = "Active";
				}
				else{
					$Partnership = "NoPartnership";
					$StatusInAff = "Active";
				}
				
				$TargetCountryExt = '';
				if(!isset($country_arr[$strTmpMerID])){
					$url = "http://cli.linksynergy.com/cli/publisher/programs/shipping_availability.php?mid=$strTmpMerID";
					$Country_r = $this->oLinkFeed->GetHttpResult($url, $request);
					$Country_result = $Country_r["content"];
					$Country_re = preg_match_all('/<td>(.*?)<\/td>/',$Country_result, $matches);
					foreach ($matches[1] as $ke => $m){
						if(empty($m))
							unset($matches[1][$ke]);
					}
					if(count($matches[1])){
						$TargetCountryExt = implode(',', $matches[1]);						
						$country_arr[$strTmpMerID] = $TargetCountryExt;
					}
				}else{
					$TargetCountryExt = $country_arr[$strTmpMerID];
				}
				
				//program
				$arr_prgm[$strMerID] = array(
						"Name" => addslashes(trim($strMerName)),
						"AffId" => $this->info["AffId"],
						//"Homepage" => '',
						//"CategoryExt" => "",
						//"Contacts" => '',
						"TargetCountryExt" => $TargetCountryExt,
						"RankInAff" => 5,
						"IdInAff" => $strMerID,
						"JoinDate" => $JoinDate,
						"CreateDate" => "0000-00-00 00:00:00",
						"DropDate" => "0000-00-00 00:00:00",
						//"TermAndCondition" => '',
						"StatusInAff" => $StatusInAff,						//'Active','TempOffline','Inactive'
						"StatusInAffRemark" => addslashes($StatusInAffRemark),
						"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','Removed'
						"Description" => $desc,
						"CommissionExt" => addslashes($CommissionExt),
						"CookieTime" => $ReturnDays,
						"LastUpdateTime" => date("Y-m-d H:i:s"),
						"SecondIdInAff" => $strTmpOfferID,
						"DetailPage" => '',
				        'MobileFriendly' => 'UNKNOWN',
						'LogoUrl' => $LogoUrl,
				);
				
				//program_detail
				$prgm_url = "http://cli.linksynergy.com/cli/publisher/programs/advertiser_detail.php?oid=$strTmpOfferID&mid=$strTmpMerID&offerNid=$strTmpNetworkID&controls=1:1:1:1:1:0";
				$arr_prgm[$strMerID]['DetailPage'] = $prgm_url;
				$more_info = $this->getSupportDUT($strTmpMerID, $strTmpOfferID, $request, true, $strMerID);
				$arr_prgm[$strMerID]['SupportDeepUrl'] = $more_info['SupportDeepUrl'];
				if(isset($more_info['CategoryExt'])){
					$arr_prgm[$strMerID]['CategoryExt'] = addslashes($more_info['CategoryExt']);
				}
				if(isset($more_info['TermAndCondition'])){
					$arr_prgm[$strMerID]['TermAndCondition'] = addslashes($more_info['TermAndCondition']);
				}
				if(isset($more_info['Homepage'])){
					$arr_prgm[$strMerID]['Homepage'] = addslashes($more_info['Homepage']);
				}
				if(isset($more_info['Contacts'])){
					$arr_prgm[$strMerID]['Contacts'] = addslashes($more_info['Contacts']);
				}
				$MobileFriendly = $this->getMobileFriendly($strTmpMerID, $strMerID);
				if(!empty($MobileFriendly)){
            		$arr_prgm[$strMerID] = array_merge($MobileFriendly, $arr_prgm[$strMerID]);
				}
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
			//Check if have next page;
			if (false === $this->oLinkFeed->ParseStringBy2Tag($result, "document.myform.submit();return false;'>Next", '</a></div></div>', $nLineStart)){
				$bHasNextPage = false;
				if($this->debug) print " NO NEXT PAGE  <br>\n";
			}
			else{
				if($this->debug) print " Have NEXT PAGE  <br>\n";
			}

			if ($bHasNextPage){
				$nPageNo++;
			}
		}//per page
		echo "\tGet Program by page end\r\n";
		echo "<hr>\r\n";
		echo count($exist_prgm)."/".$program_num;
		echo "<hr>\r\n";
		if($program_num < 10){
			mydie("die: program count < 10, please check program.\n");
		}
		echo "\tUpdate ({$program_num}) program.\r\n";
		echo "\tSet program country int.\r\n";
		$objProgram->setCountryInt($this->info["AffId"]);
	}

    function GetTransactionFromAff($start_date, $end_date)
    {
        echo "Craw Transaction from $start_date to $end_date start @ " . date('Y-m-d H:i:s') . "\r\n";

        $url_api = 'https://ran-reporting.rakutenmarketing.com/en/reports/signature-orders-report-2/filters?start_date={BDATE}&end_date={EDATE}&include_summary=N&network={NID}&tz=GMT&date_type=process&token={TOKEN}';
        $request = array("AffId" => $this->info["AffId"], "method" => 'get');
        $NIDS = array(
            '1'=>array('country'=>'US','currency'=>'USD'),
            '3'=>array('country'=>'UK','currency'=>'GBP'),
            '5'=>array('country'=>'CA','currency'=>'CAD'),
            '7'=>array('country'=>'FR','currency'=>'EUR'),
            '9'=>array('country'=>'GE','currency'=>'EUR'),
            '41'=>array('country'=>'AU','currency'=>'AUD'),
        );

        foreach ($NIDS as $nid => $network) {
            $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"], "data_" . date("YmdH") . "_Transaction_{$network['country']}.csv", 'Transaction', true);;
            if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
                $fw = fopen($cache_file, 'w');
                if (!$fw) {
                    throw new Exception("File open failed {$cache_file}");
                }
                $url = str_replace(array('{TOKEN}', '{BDATE}', '{EDATE}', '{NID}'), array($this->info['TransactionApiKey'], $start_date, $end_date, $nid), $url_api);
                echo "req => {$url} \n";
                $request['file'] = $fw;
                $result = $this->oLinkFeed->GetHttpResult($url, $request);
                if ($result['code'] != 200){
                    mydie("Download {$network['country']} csv file failed.");
                }
                fclose($fw);
            }

            $fp = fopen($cache_file, 'r');
            if (!$fp) {
                throw new Exception("File open failed {$cache_file}");
            }
            $curr_code = isset($network['currency']) ? $network['currency'] : 'USD';

            $k = 0;
            while (!feof($fp)) {
                $lr = fgetcsv($fp, 0, ',', '"');
                if (++$k == 1) {
                    continue;
                }
                if ($lr[0] == "No Results Found") {
                    continue;
                }
                if (empty($lr)){
                    break;
                }

                //process date time
                $_day = $process_dt = date('Y-m-d',strtotime($lr[10]));
                $cur_exr = $this->oLinkFeed->cur_exchange($curr_code, 'USD', $_day);
                $oldsales = (float)str_replace(',','',trim($lr[7]));
                $oldcommission = (float)str_replace(',','',trim($lr[9]));
                $sales = round($oldsales * $cur_exr, 4);
                $commission = round($oldcommission * $cur_exr, 4);

                $replace_array = array(
                    '{createtime}'      => date('Y-m-d',strtotime($lr[10])).' '.$lr[11],
                    '{updatetime}'      => date('Y-m-d',strtotime($lr[4])).' '.$lr[5],
                    '{sales}'           => $sales,
                    '{commission}'      => $commission,
                    '{idinaff}'         => trim($lr[1]),
                    '{programname}'     => trim($lr[2]),
                    '{sid}'             => trim($lr[0]),
                    '{orderid}'         => trim($lr[3]),
                    '{clicktime}'       => date('Y-m-d',strtotime($lr[4])).' '.$lr[5],
                    '{tradeid}'         => empty($lr[12])?'':trim($lr[12]),
                    '{tradestatus}'     => '',
                    '{oldcur}'          => $curr_code,
                    '{oldsales}'        => $oldsales,
                    '{oldcommission}'   => $oldcommission,
                    '{tradetype}'       => '',
                    '{referrer}'        => empty($lr[13])?'':trim($lr[13]),
                );

                $rev_file = AFF_TRANSACTION_DATA_PATH . '/revenue_' . str_replace('-', '', $_day) . '.upd';
                if (!isset($fws[$rev_file])) {
                    $fws[$rev_file] = fopen($rev_file, 'w');
                }
                fwrite($fws[$rev_file], strtr(FILE_FORMAT,$replace_array) . "\n");
            }
            fclose($fp);
            sleep(12);//api allows 5 req per min.
        }
        foreach ($fws as $file => $f) {
            fclose($f);
        }

        echo "Craw Transaction end @ " . date("Y-m-d H:i:s") . "\r\n";
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

	function getSupportDUT($mid, $oid, $request, $needmoreinfo = false, $IdInAff)
	{

		$mid = intval($mid);
		$oid = intval($oid);
		$SupportDeepUrl = "UNKNOWN";
		$return_arr = array('SupportDeepUrl' => $SupportDeepUrl);
		if($mid && $oid){
			if(!isset($this->cache[$IdInAff]["contact"])){
				$prgm_url = "http://cli.linksynergy.com/cli/publisher/programs/adv_info.php?mid=$mid&oid=$oid";
				$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
				if($prgm_arr['code'] == 200){
					$prgm_detail = $prgm_arr["content"];
					$this->cache[$IdInAff]["contact"] = "1";
				
					$SupportDeepUrl = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Deep Linking Enabled', '<td>'), '</td>')));
					if(stripos($SupportDeepUrl, "yes") !== false){
						$SupportDeepUrl = "YES";
					}else{
						$SupportDeepUrl = "NO";
					}
					$return_arr = array('SupportDeepUrl' => $SupportDeepUrl);
		
					if($needmoreinfo){
						$CategoryExt = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Categories:', '<td>'), "</td>");
						$CategoryExt = trim(strip_tags(str_replace("<br>", ", ", $CategoryExt)), ",");
						
                        $return_arr['CategoryExt'] = $CategoryExt;
						$Homepage = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Website:', '<td>'), "</td>")));
						$Contact_Name = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Contact Name:', '<td>'), "</td>");
						$Contact_Title = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Contact Title:', '<td>'), "</td>");
						$Contact_Phone = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Phone Number:', '<td>'), "</td>");
						$Contact_Email = strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Email Address:', '<td>'), "</td>"));
						$Contact_Address = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Company Address:', '<td>'), "</td>");
						$Contact_Address = trim(strip_tags(str_replace("<br>", ", ", $Contact_Address)));
						$Contacts = "$Contact_Name($Contact_Title), Email: $Contact_Email, Phone: $Contact_Phone, Address: $Contact_Address.";
						
						if(!isset($this->cache[$IdInAff]["terms"])){
							$term_url = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Terms & Conditions:', 'href="'), '"');
							$term_arr = $this->oLinkFeed->GetHttpResult($term_url, $request);
							if($term_arr['code'] == 200){
								$TermAndCondition = $term_arr["content"];
								$return_arr['TermAndCondition'] = $TermAndCondition;
								$this->cache[$IdInAff]["terms"] = "1";	
							}
						}
						$return_arr['Homepage'] = $Homepage;
						$return_arr['Contacts'] = $Contacts;
					}
				}
			}
		}
		return $return_arr;
	}
	
	function getMobileFriendly($mid, $IdInAff)
	{
		$request = array("AffId" => $this->info["AffId"], "method" => "get");
		$url = "http://cli.linksynergy.com/cli/publisher/programs/Tracking/mobile_tracking_detail.php?mid=$mid";
		
		if(!isset($this->cache[$IdInAff]["MFdetail"])){
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			if($r['code'] == 200){
				$content = $r['content'];
				$this->cache[$IdInAff]["MFdetail"] = "1";
				if (preg_match('@green_check\.png"@', $content))
				{
					return array('MobileFriendly' => 'YES');
				}
				if (preg_match('@red_x\.png"@', $content))
				{
					return array('MobileFriendly' => 'NO');
				}
				if (!preg_match('@Pending\s+</td>@', $content))
				{
					echo "error page format\n";
					echo "$url\n";
					//exit(1);	
				}
				return array('MobileFriendly' => 'UNKNOWN');
			}else{
				return array();
			}
		}else{
			return array();
		}
	}
	
	function settermforexcel(){
		require_once dirname(dirname(dirname(dirname(__FILE__)))).'/cron/PHPExcel.php';
		include dirname(dirname(dirname(dirname(__FILE__)))).'/cron/PHPExcel/Writer/Excel2007.php';
		
		$filename1 = dirname(__FILE__).'/Linkshare Advertisers 2.7.17.xlsx';
		$objReader1 = PHPExcel_IOFactory::createReaderForFile($filename1);
		$objPHPExcel1 = $objReader1->load($filename1);						//载入excel文件
		$objPHPExcel1->setActiveSheetIndex(0);							//设置第一个工作表为活动工作表
		$objWorksheet1 = $objPHPExcel1->getActiveSheet();					//取出活动表
		//var_dump($objWorksheet);exit;
		
		/* $objProgram = new ProgramDb();
		$sql = "select NAME,homepage,idinaff,TermAndCondition from program WHERE affid = 2 AND statusinaff = 'active' AND partnership = 'active'";
		$content = $objProgram->objMysql->getRows($sql); */
		
		
		$filename2 = dirname(__FILE__).'/Linkshare bdg02.xlsx';
		$objReader2 = PHPExcel_IOFactory::createReaderForFile($filename2);
		$objPHPExcel2 = $objReader2->load($filename2);						//载入excel文件
		$objPHPExcel2->setActiveSheetIndex(0);							//设置第一个工作表为活动工作表
		$objWorksheet2 = $objPHPExcel2->getActiveSheet();
		
		/* $fhandle = fopen($csvfile, 'r');
		//print_r($fhandle);exit;
		while($line = fgetcsv($fhandle, 500000)){
			print_r($line);exit;
		} */
		
		for($l=2; $l < 949; $l++){
			$term = $objWorksheet2->getCell('D'.$l)->getValue();
			$IdInAff = $objWorksheet2->getCell('C'.$l)->getValue();
			echo "<< start write {$IdInAff} rows >>\r\n";
			for($r=2; $r < 1047; $r++) {
				$mid = $objWorksheet1->getCell('C'.$r)->getValue();
				if($IdInAff == $mid){
					if(stripos($term, "promotion") != false){
						$objWorksheet1->setCellValue('G'.$r,'YES');
					}
					if(stripos($term, "discount") != false){
						$objWorksheet1->setCellValue('H'.$r,'YES');
					}
					if(stripos($term, "coupon") != false){
						$objWorksheet1->setCellValue('I'.$r,'YES');
					}
					$KeySection = stripos($term, "Additional Terms");
					if($KeySection != false){
						$value = substr($term, $KeySection);
						$objWorksheet1->setCellValue('J'.$r,$value);
					}
					echo "<< finish write {$IdInAff} rows >>\r\n";
					break;
				}
			}
		}
		
		/* $r = 2;
		for ($r=2; $r < 1047; $r++) {
			
			$url = $objWorksheet1->getCell('F'.$r)->getValue();
			//print_r($url);exit;
			//$result = file_get_contents($url);
			$result = $this->oLinkFeed->GetHttpResult($url);
			$result = $result['content'];
			print_r($result);exit;
			if(stripos($result, "promotion") != false){
				$objWorksheet1->setCellValue('G'.$r,'promotion');
			}
			if(stripos($result, "discount") != false){
				$objWorksheet1->setCellValue('G'.$r,'discount');
			}
			if(stripos($result, "coupon") != false){
				$objWorksheet1->setCellValue('G'.$r,'coupon');
			}
		
			$KeySection = stripos($result, "Additional Terms");
			if($KeySection != false){
				$value = substr($result, $KeySection);
				$objWorksheet1->setCellValue('H'.$r,$value);
			}
		} */
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel1, 'Excel2007');
		$objWriter->save(str_replace('.php', '.xlsx', __FILE__));
	}
	
	function writetoCSV (){
		require_once dirname(dirname(dirname(dirname(__FILE__)))).'/cron/PHPExcel.php';
		include dirname(dirname(dirname(dirname(__FILE__)))).'/cron/PHPExcel/Writer/Excel2007.php';
		
		$objProgram = new ProgramDb();
		/* $filename = dirname(__FILE__).'/Linkshare Advertisers 2.7.17.xlsx';
		$objReader = PHPExcel_IOFactory::createReader('Excel2007');
		$objPHPExcel = $objReader->load($filename);
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
		$objWriter->save(str_replace('.xlsx', '.csv',$filename)); */
		$filename = dirname(__FILE__).'/Linkshare Advertisers 2.7.17.csv';
		
		$fp = fopen($filename, "r");
		
		$sql = "select NAME,homepage,idinaff,TermAndCondition from program WHERE affid = 2 AND statusinaff = 'active' AND partnership = 'active'";
		$content = $objProgram->objMysql->getRows($sql);
		$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"{$this->info["AffId"]}_".date("YW").".dat", "program", true);
		if(!$this->oLinkFeed->fileCacheIsCached($cache_file)){
			$this->oLinkFeed->fileCachePut($cache_file, $content);
		}
		$handle = fopen($cache_file, 'r');print_r($handle);exit;
		while($line = fgetcsv ($handle, 5000)){
			
		
		}
		
	}
	

}

?>