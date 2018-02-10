<?php
class LinkFeed_2032_Kelkoo
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		
		$this->country_arr = array('fr', 'uk', 'be','se','no','br','it','es', 'nl');//,'at','de'

		$this->uk->TrackingId = '96952895';
		$this->uk->AffiliateKey = 'bRkkPX2a';

		$this->es->TrackingId = '96953923';
		$this->es->AffiliateKey = 'Lr6yX5f0';

		$this->fr->TrackingId = '96953926';
		$this->fr->AffiliateKey = 'r1a4ZYGg';
		
		
		$this->be->TrackingId = '96953962';
		$this->be->AffiliateKey = 'fcZC4Yfv';		
		
		$this->se->TrackingId = '96953925';
		$this->se->AffiliateKey = '8QSxyM1m';
		
		$this->no->TrackingId = '96953924';
		$this->no->AffiliateKey = 'IQq22Fk5';
		
		$this->br->TrackingId = '96953966';
		$this->br->AffiliateKey = 'JmDihg9k';
		
		$this->it->TrackingId = '96953967';
		$this->it->AffiliateKey = '073ufD7n';		
		
		$this->nl->TrackingId = '96953968';
		$this->nl->AffiliateKey = 'Q7681JI8';

		$this->de->TrackingId = '96953978';
		$this->de->AffiliateKey = '909DU4Nm';
		
		$this->at->TrackingId = '96953979';
		$this->at->AffiliateKey = 'EKOjkHae';
		

	}
	
	function UrlSigner($urlDomain, $urlPath, $country)
	 {
	 	if(!$country) mydie('UrlSigner no country. ');
		 settype($urlDomain, 'String');
		 settype($urlPath, 'String');
		 settype($this->$country->TrackingId, 'String');
		 settype($this->$country->AffiliateKey, 'String');
		 
		 $URL_sig = "hash";
		 $URL_ts = "timestamp";
		 $URL_partner = "aid"; 
		 $URLreturn = "";
		 $URLtmp = "";
		 $s = "";
		 // get the timestamp
		 $time = time();
		//echo $urlPath."\r\n";
		 // replace " " by "+"
		 $urlPath = str_replace(" ", "+", $urlPath);
		 // format URL
		 $URLtmp = $urlPath . "&" . $URL_partner . "=" . $this->$country->TrackingId . "&" . $URL_ts . "=" . $time;
		
		 // URL needed to create the tokken
		 $s = $urlPath . "&" . $URL_partner . "=" . $this->$country->TrackingId . "&" . $URL_ts . "=" . $time . $this->$country->AffiliateKey;
		 $tokken = "";
		 $tokken = base64_encode(pack('H*', md5($s)));
		 $tokken = str_replace(array("+", "/", "="), array(".", "_", "-"), $tokken);
		 $URLreturn = $urlDomain . $URLtmp . "&" . $URL_sig . "=" . $tokken;
		 return $URLreturn;		 
	 }
	 
	
	

	function GetAllProductsByAffId()
	{
	
		$check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
		$request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "");
	
		//$arr_merchant = $this->oLinkFeed->getApprovalAffMerchant($this->info["AffId"]);
		$objProgram = new ProgramDb();
		$sql = "SELECT ID, IdInAff, TargetCountryExt FROM program WHERE AffId = {$this->info["AffId"]} AND StatusInAff = 'Active' AND Partnership = 'Active' ";
		$arr_merchant = $objProgram->objMysql->getRows($sql);
		$productNumConfig = $this->oLinkFeed->product_program_num_config($this->info["AffId"]);
        $productNumConfigAlert = '';
        $isAssignMerchant = FALSE;
		
		$mcount = 0;
		$limit = 50;
		foreach ($arr_merchant as $merchatInfo)
		{
		    $crawlMerchantsActiveNum = 0;
		    $setMaxNum  = isset($productNumConfig[$merchatInfo['IdInAff']]) ? $productNumConfig[$merchatInfo['IdInAff']]['limit'] :  100;
		    $isAssignMerchant = isset($productNumConfig[$merchatInfo['IdInAff']]) ? TRUE : FALSE;
			if(!$merchatInfo['TargetCountryExt']){			
				mydie('No TargetCountryExt idinaff:[' . $merchatInfo['IdInAff']) . ']';
			}
			$apiPositionStart = 1;
			
			//分页
			do{
			    $url = $this->UrlSigner('http://' . $merchatInfo['TargetCountryExt'] . '.shoppingapis.kelkoo.com', '/V3/productSearch?merchantId=' . $merchatInfo['IdInAff'] . '&sort=default_ranking&logicalType=and&show_products=1&show_subcategories=0&show_refinements=0&custom1=[SUBTRACKING]&start='.$apiPositionStart.'&results=' . $limit, $merchatInfo['TargetCountryExt']);
			    echo $url."\r\n";
			    $r = $this->oLinkFeed->GetHttpResult($url,$request);
			    $r = simplexml_load_string($r['content']);
			    $r = json_decode(json_encode($r), true);
			    $totalResultsAvailable = $r['Products']['@attributes']['totalResultsAvailable'];
			    
			    if (!$r['Products']['@attributes']['totalResultsReturned'])
			        break;
			    if(count($r['Products']['Product']) <= 0 )
			        break;
			    
			    foreach ($r['Products']['Product'] as $value)
			    {
			        if (!isset($value['Offer'])){
			            continue;
			        }
			        $v = $value['Offer'];
			        $AffProductId = $v['@attributes']['id'];
			        
			        $ProductImage = $v['Images']['ZoomImage']['Url'];
			        $product_path_file = $this->oLinkFeed->getProductImgFilePath($this->info["AffId"],"{$merchatInfo['IdInAff']}_".urlencode($AffProductId).".png", PRODUCTDIR);
			        if(!$this->oLinkFeed->fileCacheIsCached($product_path_file))
			        {
			            $file_content = $this->oLinkFeed->downloadImg($ProductImage);
			            if(!$file_content) //下载不了跳过。
			            {
			                continue;
			            }
			            $this->oLinkFeed->fileCachePut($product_path_file, $file_content);
			        }
			        if(!isset($v['Title']) || empty($v['Title']) || !isset($AffProductId))
			        {
			            continue;
			        }
			    
			        $link = array(
			            "AffId" => $this->info["AffId"],
			            "AffMerchantId" => $merchatInfo['IdInAff'],
			            "AffProductId" => trim($AffProductId),
			            "ProductName" => addslashes($v['Title']),
			            "ProductCurrency" => trim($v['Price']['@attributes']['currency']),
			            "ProductPrice" => trim($v['Price']['Price']),
			            "ProductOriginalPrice" =>'',
			            "ProductRetailPrice" =>'',
			            "ProductImage" => addslashes($ProductImage),
			            "ProductLocalImage" => addslashes($product_path_file),
			            "ProductUrl" => addslashes($v['Url']),
			            "ProductDestUrl" => '',
			            "ProductDesc" => isset($v['Description'])?addslashes($v['Description']):'',
			            "Language" => $merchatInfo['TargetCountryExt'],
			            "ProductStartDate" => isset($v['LastModified'])?trim($v['LastModified']):'',
			            "ProductEndDate" => '',
			        );
			        if (empty($link['ProductUrl']) || empty($link['ProductImage'])){
			            continue;
			        }
			        //print_r($link);	
			        $links[] = $link;
			        if (count($links) >= 100)
			        {
			        
			            $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateProductToDB($links);
			            $links = array();
			        }
			        $arr_return['AffectedCount'] ++;
			        $crawlMerchantsActiveNum ++;
			    }
			    if (count($links))
			    {
			        $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateProductToDB($links);
			        $links = array();
			    }
			    //大于最大数跳出
			    if($crawlMerchantsActiveNum >= $setMaxNum){
			        break;
			    }
			    $apiPositionStart += $limit;
			    
			}while(1);
			
			if($isAssignMerchant){
			    $productNumConfigAlert .= "AFFID:".$this->info["AffId"].",Program({$merchatInfo['IdInAff']}),Crawl Count($crawlMerchantsActiveNum),Total Count({$totalResultsAvailable}) \r\n";
			}
			$mcount ++;
		}
		echo 'merchant count:'.$mcount.PHP_EOL;
		$this->oLinkFeed->checkProductExists($this->info["AffId"], $check_date);
		echo $isAssignMerchant.PHP_EOL;	
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
	
	
	function GetProgramByApi()
	{
		echo "\tGet Program by api start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = $no_product = 0;
		
		$tmp_prgm = array();
		
		foreach($this->country_arr as $country){
			$arr_prgm = array();
			$url = $this->UrlSigner('http://'.$country.'.shoppingapis.kelkoo.com', '/V2/categorySearch?format=Tree&shortcuts=false&features=None', $country);
			echo $url;
			$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"], "category_$country" . ".dat","cache_merchant");//返回.cache文件的路径
			if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
			{
				$request["method"] = "get";
				$r = $this->oLinkFeed->GetHttpResult($url,$request);
				$result = $r["content"];			
				$this->oLinkFeed->fileCachePut($cache_file,$result);
			}
			if(!file_exists($cache_file)) mydie("die: category $country file does not exist. \n");
			
			$categoryResult = simplexml_load_file($cache_file);
			$category = $categoryResult -> Category;
			//print_r($categoryResult);exit;
			$category_arr = array();
			$category_rel = array();
			foreach($category->Category as $lv1){
			
				$category_arr[(int)$lv1['id']]['name'] = (string)$lv1['name'];
				$category_arr[(int)$lv1['id']]['deadend'] = 0;
				$category_rel[(int)$lv1['id']] = array();
				
				foreach($lv1 as $lv2){
					$category_arr[(int)$lv2['id']]['name'] = (string)$lv2['name'];
				
					if($lv2->Category){
						$category_arr[(int)$lv2['id']]['deadend'] = 0;
						$category_rel[(int)$lv1['id']][(int)$lv2['id']] = array();
						foreach($lv2 as $lv3){				
							$category_arr[(int)$lv3['id']]['name'] = (string)$lv3['name'];
							$category_arr[(int)$lv3['id']]['deadend'] = 1;
							$category_arr[(int)$lv3['id']]['name_rel'] = (string)$lv1['name'] . ' >> ' . (string)$lv2['name'] . ' >> ' . (string)$lv3['name'];
							$category_rel[(int)$lv1['id']][(int)$lv2['id']][(int)$lv3['id']] = (string)$lv1['name'] . ' >> ' . (string)$lv2['name'] . ' >> ' . (string)$lv3['name'];
						}
					}else{
						$category_arr[(int)$lv2['id']]['deadend'] = 1;
						$category_arr[(int)$lv2['id']]['name_rel'] = (string)$lv1['name'] . ' >> ' . (string)$lv2['name'];
						$category_rel[(int)$lv1['id']][(int)$lv2['id']] = (string)$lv1['name'] . ' >> ' . (string)$lv2['name'];
					}
				}			
			}
			
			foreach($category_arr as $catid => $cat)
			{
				if($cat['deadend'] !== 1)		
					continue;
				
				$page = 1;
				$return_limit = 100;
				$total_page = 0;
				$total = 0;
				$cnt = 0;
				$hasNextPage = 1;
				while($hasNextPage){
					$url = $this->UrlSigner('http://'.$country.'.shoppingapis.kelkoo.com', '/V2/merchantSearch?category=' . $catid . '&enable=store_details,store_profile,store_ratings,store_payment&start=' . ((($page - 1) * $return_limit) + 1) . '&results=' . $return_limit, $country);
				
					$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"], date('Y-m-d') ."p_{$country}_{$catid}_{$page}" . ".dat","cache_merchant");//返回.cache文件的路径
					if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
					{
						$request["method"] = "get";
						$r = $this->oLinkFeed->GetHttpResult($url,$request);
						$result = $r["content"];
						$this->oLinkFeed->fileCachePut($cache_file,$result);
					}
					if(!file_exists($cache_file)) mydie("die: category file does not exist. \n");
					
					$result = simplexml_load_file($cache_file);
					
					if($total_page == 0){
						$total = $result['totalResultsAvailable'];		
						$total_page = ceil((int)$result['totalResultsAvailable'] / $return_limit);
					}
					
					if($page >= $total_page || $cnt > $total){
						$hasNextPage = 0;				
					}
					$page++;
					
					//print_r($result);exit;
					foreach($result as $v){
						$IdInAff = (int)$v['id'];
						if(!$IdInAff) continue;
						
						if(!isset($tmp_prgm[$IdInAff])){
							$tmp_prgm[$IdInAff] = $country;
						}elseif($tmp_prgm[$IdInAff] != $country){
							mydie("has repeat program. $IdInAff | {$tmp_prgm[$IdInAff]} : $country");
						}
						
						$CategoryExt = $cat['name_rel'];
						if(isset($arr_prgm[$IdInAff])){
							$CategoryExt = $arr_prgm[$IdInAff]['CategoryExt'] . ', ' . $CategoryExt;
						}
						$arr_prgm[$IdInAff] = array(
													"AffId" => $this->info["AffId"],
													"IdInAff" => $IdInAff,
													"Name" => addslashes(trim($v->Name)),
													"Homepage" => addslashes($v->MerchantUrl),
													"LogoUrl" => addslashes($v->Profile->Logo->Url),
													"TargetCountryExt" => $country,
													"CategoryExt" => $CategoryExt,												
												);
						$program_num++;
						$cnt++;
						//print_r($arr_prgm);exit;
					}				
				}
				
				//echo $cat['name'] . '[' . $catid . "] return:$total , get:$cnt \r\n";
			}
			//print_r($arr_prgm);
			echo count($arr_prgm)."/{$program_num}\r\n";
			
			echo "\tGet Program by api end\r\n";
			
			if(count($arr_prgm) < 10){
				mydie("die: program count < 10, please check program.\n");
			}
		
			if(count($arr_prgm)){
				foreach($arr_prgm as $idinaff => $v){
					$arr_prgm[$idinaff]['CategoryExt'] = addslashes($v['CategoryExt']);
					$arr_prgm[$idinaff]['StatusInAff'] = 'Active';						//'Active','TempOffline','Offline'
					$arr_prgm[$idinaff]['Partnership'] = 'Active';						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
					$arr_prgm[$idinaff]['LastUpdateTime'] = date('Y-m-d H:i:s');
					
					//get default aff url		
					$AffDefaultUrl = '';
							
					$url = $this->UrlSigner('http://'.$country.'.shoppingapis.kelkoo.com', '/V3/productSearch?merchantId=' . $idinaff . '&sort=default_ranking&logicalType=and&show_products=1&show_subcategories=0&show_refinements=0&custom1=x&start=1&results=1', $country);
					$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"], date('m_d_H') ."program_{$idinaff}" . ".dat","product");//返回.cache文件的路径
					if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
					{
						$request["method"] = "get";
						$r = $this->oLinkFeed->GetHttpResult($url,$request);
						$result = $r["content"];			
						$this->oLinkFeed->fileCachePut($cache_file,$result);
					}
					if(!file_exists($cache_file)) mydie("die: AffDefaultUrl does not exist. \n");
					$result = simplexml_load_file($cache_file);
					$result = $result -> Products -> Product;
					
					if(is_object($result) && strlen($result->Offer->Url)){
						$AffDefaultUrl = (string)$result->Offer->Url;
						$AffDefaultUrl = str_replace('custom1=x', 'custom1=[SUBTRACKING]', $AffDefaultUrl);						
					}else{
						//$arr_prgm[$idinaff]['StatusInAff'] = 'Offline';	//don't have product
						$no_product ++;
					}
					$arr_prgm[$idinaff]['AffDefaultUrl'] = addslashes($AffDefaultUrl);				
				}			
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);				
			}
			
			echo "\tUpdate $country (".count($arr_prgm).") program.\r\n";
			unset($arr_prgm);
		}
		
		echo "($no_product)Program don't have product\r\n";
	}
	
	function checkProgramOffline($AffId, $check_date){
		$objProgram = new ProgramDb();
		$prgm = array();
		$prgm = $objProgram->getNotUpdateProgram($this->info["AffId"], $check_date);
		
		if(count($prgm) > 300){
			mydie("die: too many offline program (".count($prgm).").\n");
		}else{
			$objProgram->setProgramOffline($this->info["AffId"], $prgm);
			echo "\tSet (".count($prgm).") offline program.\r\n";
		}
	}
}