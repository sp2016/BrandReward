<?php

require_once 'text_parse_helper.php';
require_once INCLUDE_ROOT."wsdl/adcell_api/adcell.php";
include_once(INCLUDE_ROOT . "../func/func.php");

class LinkFeed_360_Adcell_DE
{
	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
        $this->getStatus = false;
	    if(SID == 'bdg02') {
	        $this->token_option = array(
	        		'userName' => '215401',
	    			'password' => '^fdg9ERWKV8E_2ho',
		        );
	    }else{
	    	$this->token_option = array(
	    			'userName' => '215442',
		            'password' => 'Uh47vgf76RT89ovq',
	    	);
	    }
	}

	function GetAllLinksFromAffByMerID($merinfo)
	{
	    
	}

	function getCouponFeed()
	{
	    $check_date = date('Y-m-d H:i:s');
	    $temp = array();
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		$request = array("AffId" => $this->info["AffId"], "method" => "get");
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		
		list($page, $last_page, $links) = array(1, 0, array());
		do
		{
		    $url = sprintf("https://www.adcell.de/promotion/couponlist/page/%s", $page);
		    $r = $this->oLinkFeed->GetHttpResult($url, $request);
		    $content = $r['content'];
		    if (empty($last_page))
		    {
		        if (preg_match('@href="/promotion/couponlist/page/(\d+)/.*?">Letzte@', $content, $g))
		            $last_page = (int)$g[1];
		    }
		    preg_match_all('@<li data-promoid="(\d+)"(.*?)</li>@ms', $content, $chapters);
		    foreach ((array)$chapters[2] as $key => $chapter)
		    {
		        $link = array(
		            "AffId" => $this->info["AffId"],
		            "AffMerchantId" => '',
		            "AffLinkId" => $chapters[1][$key],
		            "LinkName" =>  '',
		            "LinkDesc" =>  '',
		            "LinkStartDate" => '0000-00-00 00:00:00',
		            "LinkEndDate" => '0000-00-00 00:00:00',
		            "LinkPromoType" => 'coupon',
		            "LinkHtmlCode" => '',
		            "LinkCode" => '',
		            "LinkOriginalUrl" => '',
		            "LinkImageUrl" => '',
		            "LinkAffUrl" => '',
		            "DataSource" => 98,
		            "Type"       => 'promotion'
		        );
		        // no partnership, skip.
		        if (preg_match('@"button">Anmelden</a>@', $chapter, $g))
		            continue;
		        if (preg_match('@data-programid="(\d+)"@', $chapter, $g))
		            $link['AffMerchantId'] = $g[1];
		        if (preg_match('@setPartnerId\(\&quot;(\d+)\&quot;\)@', $chapter, $g))
		            $link['LinkAffUrl'] = sprintf("http://www.adcell.de/promotion/click/promoId/%s/slotId/%s", $link['AffLinkId'], $g[1]);
		        if (preg_match('@<div class="description".*?<p.*?>(.*?)</p>@ms', $chapter, $g))
		            $link['LinkName'] = trim(html_entity_decode($g[1]));
		        if (preg_match('@<th.*?>Gutscheincode</th>\s+<td.*?>(.*?)</td>@', $chapter, $g))
		        {
		            $link['LinkCode'] = trim(html_entity_decode($g[1]));
		        }
		        else
		        {
		            $code = get_linkcode_by_text_de($link['LinkName'] . '|' . $link['LinkDesc']);
		            if (!empty($code))
		                $link['LinkCode'] = $code;
		        }
		        if (preg_match('@<th.*?>Gültigkeit</th>\s+<td.*?>(\d+\.\d+\.\d+) - (\d+\.\d+\.\d+|\w+)</td>@', $chapter, $g))
		        {
		            $link['LinkStartDate'] = parse_time_str($g[1], 'd.m.Y', false);
		            $link['LinkEndDate'] = parse_time_str($g[2], 'd.m.Y', true);
		        }
		        $link['LinkHtmlCode'] = create_link_htmlcode($link);
		        if (empty($link['AffMerchantId']) || empty($link['AffLinkId']) || empty($link['LinkName']) || empty($link['LinkAffUrl'])){		           
		            continue;
		        }		       
		        $temp[$chapters[1][$key]] = 1;
		        $links[] = $link;
		        $arr_return["AffectedCount"] ++;
		    }
		    if (count($links) > 0)
		    {
		        $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		        $links = array();
		    }
		   echo  $page ++;
		}while ($page <= $last_page && $page < 1000);
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'promotion');
		
		
		//link
		$r = $this->oLinkFeed->GetHttpResult('https://www.adcell.de/werbemittel/0', $request);
		preg_match('@var programs = {(.*?)}};@ms',$r['content'],$matches);
		$merchantStr = '{'.$matches[1].'}}';
		$merchantArr = json_decode($merchantStr,true);
		
		foreach ($merchantArr['data'] as $merchantKey=>$merchantValue){
		    list($page, $last_page, $links) = array(1, 0, array());
		    do
		    {
		        $url = sprintf("https://www.adcell.de/werbemittel/%s/page/%s", $merchantKey, $page);
		        $r = $this->oLinkFeed->GetHttpResult($url, $request);
		        $content = $r['content'];
		        if (empty($last_page))
		        {
		            if (preg_match_all('@href="/werbemittel/.*?/page/(\d+)/.*?"@', $content, $g))
		            {
		                foreach ($g[1] as $p)
		                {
		                    if ($last_page < $p)
		                        $last_page = $p;
		                }
		            }
		        }
		        preg_match_all('@<li data-promoid="(\d+)"(.*?)</li>@ms', $content, $chapters);
		        foreach ((array)$chapters[2] as $key => $chapter)
		        {
		            if(isset($temp[$chapters[1][$key]])){
		                continue;
		            }
		                
		            $link = array(
		                "AffId" => $this->info["AffId"],
		                "AffMerchantId" => $merchantKey,
		                "AffLinkId" => $chapters[1][$key],
		                "LinkName" =>  '',
		                "LinkDesc" =>  '',
		                "LinkStartDate" => '0000-00-00 00:00:00',
		                "LinkEndDate" => '0000-00-00 00:00:00',
		                "LinkPromoType" => 'DEAL',
		                "LinkHtmlCode" => '',
		                "LinkCode" => '',
		                "LinkOriginalUrl" => '',
		                "LinkImageUrl" => '',
		                "LinkAffUrl" => '',
		                "DataSource" => 98,
		                "Type"       => 'link'
		            );
		            if (preg_match('@href="/partnerprogramme/(\d+)"@', $chapter, $g))
		                $link['AffMerchantId'] = $g[1];
		            if (preg_match('@setPartnerId\(\&quot;(\d+)\&quot;\)@', $chapter, $g))
		                $link['LinkAffUrl'] = sprintf("http://www.adcell.de/promotion/click/promoId/%s/slotId/%s", $link['AffLinkId'], $g[1]);
		            else if (preg_match('@<input id="dlbtn_\d+-(\d+)"@', $chapter, $g))
		                $link['LinkAffUrl'] = sprintf("http://www.adcell.de/promotion/click/promoId/%s/slotId/%s", $link['AffLinkId'], $g[1]);
		            if (preg_match('@<h3>(.*?)</h3>@ms', $chapter, $g))
		                $link['LinkName'] = trim(html_entity_decode($g[1]));
		            if (preg_match('@<p.*?>[\s]*Gutscheincode[^<]*<b[^>]*>(.*)</b></p>@', $chapter, $g))
		            {
		                $link['LinkCode'] = trim(html_entity_decode($g[1]));
		                $link['LinkPromoType'] = 'coupon';
		            }
		            else
		            {
		                $code = get_linkcode_by_text_de($link['LinkName'] . '|' . $link['LinkDesc']);
		                if (!empty($code))
		                {
		                    $link['LinkPromoType'] = 'COUPON';
		                    $link['LinkCode'] = $code;
		                }
		                else
		                    $link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
		            }
		            if (preg_match('@<th.*?>Gültigkeit</th>\s+<td.*?>(\d+\.\d+\.\d+) - (\d+\.\d+\.\d+)</td>@', $chapter, $g))
		            {
		                $link['LinkStartDate'] = parse_time_str($g[1], 'd.m.Y', false);
		                $link['LinkEndDate'] = parse_time_str($g[2], 'd.m.Y', true);
		            }
		            if (preg_match('@src="(.*?)"@', $chapter, $g))
		                $link['LinkImageUrl'] = $g[1];
		            if (preg_match('@<a rel="nofollow" target="_blank" href="(.*?)">(.*?)</a>@ms', $chapter, $g))
		            {
		                $link['LinkAffUrl'] = trim($g[1]);
		                $link['LinkName'] = trim($g[2]);
		                $link['LinkHtmlCode'] = $g[0];
		            }
		            else
		                $link['LinkHtmlCode'] = create_link_htmlcode_image($link);
		            if (empty($link['AffMerchantId']) || empty($link['AffLinkId']) ||  empty($link['LinkAffUrl']))
		                continue;
		            elseif(empty($link['LinkName'])){
		                $link['LinkPromoType'] = 'link';
		            }
		            $links[] = $link;
		            $arr_return["AffectedCount"] ++;
		        }
		        if (count($links) > 0)
		        {
		            $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		            $links = array();
		        }
		        $page ++;
		    }while ($page <= $last_page && $page < 1000);
		}
		
		
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link');
		
		
		
		return $arr_return;
	}
	
	
	function GetAllProductsByAffId(){
	    
	    $check_date = date('Y-m-d H:i:s');
	    $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
	    $request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "");
	    $this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
	    $productNumConfig = $this->oLinkFeed->product_program_num_config($this->info["AffId"]);
	    $productNumConfigAlert = '';
	    $isAssignMerchant = FALSE;
	    $pageNum = 1;
	     
	    
	    do{
	        
	        $productListUrl = "https://www.adcell.de/werbemittel/0/page/$pageNum/promocategory/all/promotype/9";
	        $productListHtml = $this->oLinkFeed->GetHttpResult($productListUrl,$request);
	        $productListHtml = $productListHtml['content'];
	        
	        $strLineStart = '<li data-promoid="';
	        //翻页为空跳出
	        $hasContent = stripos($productListHtml, $strLineStart);
	        if ($hasContent === false) break;
	        
	        $nLineStart = 0;
	        while ($nLineStart >= 0){
	             
	            $nLineStart = stripos($productListHtml, $strLineStart, $nLineStart);
	            if ($nLineStart === false) break;
	            
	            $merchantInfo = $this->oLinkFeed->ParseStringBy2Tag($productListHtml, array('<li data-promoid="'), '</li>', $nLineStart);
	            $rules = '/<a rel="nofollow" data-colsort="0" data-promoid="\d+" data-url="(.*?)" href="(.*?)" target="_blank">/';
	            preg_match($rules, $merchantInfo, $matches);
	            if(!empty($matches)){
	                
	                //merchantid
	                $matIdRule = '/src="\/\/media\.adcell\.de\/partner\/(\d+)\.png"/';
	                preg_match($matIdRule,$merchantInfo,$matchesMatId);
	                if(!$matchesMatId[1]) continue;
	                $merchantId = $matchesMatId[1];
	                echo $merchantId.PHP_EOL;
	                $crawlMerchantsActiveNum = 0;
	                $setMaxNum  = isset($productNumConfig[$merchantId]) ? $productNumConfig[$merchantId]['limit'] :  100;
	                $isAssignMerchant = isset($productNumConfig[$merchantId]) ? TRUE : FALSE;
	                
	                $downLoadUrl = $matches[1];
	                preg_match('/promotion\/csv\/promoId\/(\d+)\/slotId\/(\d+)/',$downLoadUrl,$matchesId);
	                if(!empty($matchesId)){
	                    
	                    $promoId = $matchesId[1];
	                    $slotId  = $matchesId[2];
	                    $fileName =  'product_feed_'.$promoId.'_'.$slotId.'.csv';
	                     
	                    $product_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],$fileName, "product", true);
	                    if(!$this->oLinkFeed->fileCacheIsCached($product_file)){
	                        $r = $this->oLinkFeed->GetHttpResult($downLoadUrl,$request);
	                        $this->oLinkFeed->fileCachePut($product_file,$r['content']);
	                    }
	                    //if file too big, continue;
	                    $FileSize = filesize($product_file);
	                    echo 'File Size:'.$product_file.'('.$FileSize.')'.PHP_EOL;
	                    if($FileSize>10000000) continue;
	                    
	                    //read download content
	                    $productData = array();
	                    $file = fopen($product_file,"r");
	                    while(! feof($file))
	                    {
	                        $productData[] = fgetcsv($file,'',';','"');
	                    }
	                    fclose($file);
	                    
	                    foreach ($productData as $pk=>$pValue){
	                        if($pk == 0) continue;
	                        if(!isset($pValue[1])) continue;
	                        if(!$pValue[1]) continue;
	                         
	                        $AffProductId = $pValue[7] ? $pValue[7] : $pValue[8];
	                        if(!$AffProductId) continue;
	                        $AffProductId = md5($AffProductId);
	                        $ProductImage = $pValue[11];
	                        $product_path_file = $this->oLinkFeed->getProductImgFilePath($this->info["AffId"],"{$merchantId}_".urlencode($AffProductId).".png", PRODUCTDIR);
	                        if(!$this->oLinkFeed->fileCacheIsCached($product_path_file))
	                        {
	                            echo $product_path_file.' no img'.PHP_EOL;
	                            echo $ProductImage .PHP_EOL;
	                            $file_content = $this->oLinkFeed->downloadImg($ProductImage);
	                            if(!$file_content) //下载不了跳过。
	                                continue;
	                            $this->oLinkFeed->fileCachePut($product_path_file, $file_content);
	                        }
	                        
	                        $link = array(
	                            "AffId" => $this->info["AffId"],
	                            "AffMerchantId" => $merchantId,
	                            "AffProductId" => $AffProductId,
	                            "ProductName" => html_entity_decode(addslashes($pValue[1])),
	                            "ProductCurrency" => '€',
	                            "ProductPrice" =>  str_replace(',','.',str_replace('.', '',$pValue[4])),
	                            "ProductOriginalPrice" =>'',
	                            "ProductRetailPrice" =>'',
	                            "ProductImage" => addslashes($ProductImage),
	                            "ProductLocalImage" => addslashes($product_path_file),
	                            "ProductUrl" => $pValue[0],
	                            "ProductDestUrl" => '',
	                            "ProductDesc" => html_entity_decode(addslashes($pValue[2])),
	                            "ProductStartDate" => '',
	                            "ProductEndDate" => '',
	                        );
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
	                    if($isAssignMerchant){
	                        $productNumConfigAlert .= "AFFID:".$this->info["AffId"].",Program({$merchantId}),Crawl Count($crawlMerchantsActiveNum),Total Count(unknown) \r\n";
	                    }
	                    
	                }
	            }
	        
	        }
	         
	        $pageNum++;
	        
	    }while(true);
	    
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

	private function getTargetCountryInt($ext)
	{
		$rows = explode(',', $ext);
		$countries = array();
		foreach ($rows as $row)
		{
			switch ($row)
			{
				case 'Deutschland':
					$countries[] = 'DE';
					break;
				case 'Österreich':
					$countries[] = 'AT';
					break;
				case 'Schweiz':
					$countries[] = 'CH';
					break;
				default:
					break;
			}
		}
		if (!empty($countries))
			return implode(',', $countries);
	}

    function GetStatus(){
        $this->getStatus = true;
        $this->GetProgramFromAff();
    }

	function GetProgramFromAff()
	{   print_r($this->info);exit;
	    $api = new AdcellApi();
	    $token = $api->getToken($this->token_option);
	    
	    //getCategories
	    $reponseCategory = $api->category(
	        array(
	            'token' => $token,
	        )
	    );
	    $cateList = array(); //鍏堝彇鍑烘墍鏈夊垎绫讳俊鎭�
	    foreach ($reponseCategory->data->items as $cate){
	        $cateList[$cate->categoryId] = $cate;
	    }
	     
	    
	    $count = 0;
	    $page  = 1;
	    do{
	        echo 'Crawl Page:'.$page.PHP_EOL;
	        $programs   = array();
	        $programIds = array(); 
	        $reponseData = $api->apply(
	            array(
	                'token' => $token,
	                'page'  => $page
	            )
	        );
	        
	        if($reponseData->status != 200) continue;
	        $totalItems = $reponseData->data->total->totalItems;
	        $row        = $reponseData->data->rows;
	        $lastPage   = ceil($totalItems/$row);
	        
	        $count += count($reponseData->data->items); 
	        //var_dump($reponseData->data->items);exit;
	        foreach ($reponseData->data->items as $value){
	            
	            $program = array(
	                "Name" => addslashes($value->programName),
	                "AffId" => $this->info["AffId"],
	                "TargetCountryExt" => $value->allowedCountries,
	                "Description" => addslashes($value->description),
	                "IdInAff" => $value->programId,
	                "StatusInAff" => 'Active',						//'Active','TempOffline','Offline'
	                "StatusInAffRemark" => $value->isActive,
	                "Partnership" => 'NoPartnership',				//'NoPartnership','Active','Pending','Declined','Expired','Removed'
	                "Homepage" => addslashes($value->programUrl),
	                "CommissionExt" => '',
	                "CategoryExt" => '',
	                "CookieTime" => $value->cookieLifetime,
	                "DetailPage" => addslashes('https://www.adcell.de/partnerprogramme/' . $value->programId),
	                "LastUpdateTime" => date("Y-m-d H:i:s"),
	            	"LogoUrl" => addslashes($value->programLogoUrl),
	            	"PaymentDays" => $value->maximumPaybackPeriod, 
	            );
	            
	            if($value->affiliateStatus == 'accepted')
	                $program['Partnership'] = 'Active';
	            elseif($value->affiliateStatus == 'application')
	                $program['Partnership'] = 'Pending';
	            
	            if($value->isActive == 1)
	                $program['StatusInAff'] = 'Active';
	            else 
	                $program['StatusInAff'] = 'Offline';
	            
	            $program['TermAndCondition'] = addslashes($value->termsAndConditions);
	            
	            //commissionExt
	            $programIds[] =  $value->programId;
	             
	            //SupportDeepUrl
	            if (stripos("deeplink", $value->programTags) != false)
	            	$program['SupportDeepUrl'] = 'YES';
	            else 
	            	$program['SupportDeepUrl'] = 'UNKNOWN';
	            
	            //CategoryExt
	            $programCategoryIdsArr = explode(',',$value->programCategoryIds);
	            if(is_array($programCategoryIdsArr)){
	                foreach ($programCategoryIdsArr as $cateId){
	                	if(isset($cateList[(int)$cateId]->categoryName))
	                		$program['CategoryExt'] .=$cateList[(int)$cateId]->categoryName.",";
	                }
	            }
	            $program['CategoryExt'] = addslashes($program['CategoryExt']);
	            $programs[$value->programId] = $program;
	        }
	        
	        //commissionExt
	        //鍐嶆嬁涓�娆oken锛岄槻姝oken澶辨晥
	        $token = $api->getToken($this->token_option);
	        $reponseCommission = $api->commission(
	            array(
	                'programIds[]'=>$programIds,
	                'token' => $token,
	            )
	        );
	        if($reponseCommission) {
	            foreach ($programs as $pKey=>$pValue){
	                foreach ($reponseCommission->data->items as $comValue){
	                    if($pKey == $comValue->programId){
	                        foreach ($comValue->events as $comEvent){
	                        	if ($comEvent->currentCommission)
	                            	$programs[$pKey]['CommissionExt'] .= $comEvent->currentCommission.$comEvent->commissionUnit.'|';
	                        	elseif ($comEvent->minimumCommission == $comEvent->maximumCommission)
	                        		$programs[$pKey]['CommissionExt'] .= $comEvent->minimumCommission.$comEvent->commissionUnit.'|';
	                        	else 
	                        		$programs[$pKey]['CommissionExt'] .= $comEvent->minimumCommission.$comEvent->commissionUnit.'-'.$comEvent->maximumCommission.$comEvent->commissionUnit.'|';
	                        	
	                            $programs[$pKey]['CommissionExt'] = addslashes($programs[$pKey]['CommissionExt']);
	                        }
	                    }
	                }
	            }
	        }
	        
	         
	        $p = $this->getProgramObj();
	        $p->updateProgram($this->info["AffId"], $programs);
	         
	        $page ++ ;
	    }while($lastPage>=$page);
	    //echo $count;exit;
		/*$request = array("AffId" => $this->info["AffId"], "method" => "get");
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		list($page, $last_page, $programs) = array(1, 0, array());
		do
		{
			$url = sprintf("https://www.adcell.de/partnerprogramme/page/%s", $page);
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$content = $r['content'];
			if (empty($last_page))
			{
				if (preg_match('@<a class="next border-radius-all-orientations-style-2" href="/partnerprogramme/page/(\d+)">Letzte</a>@', $content, $g))
					$last_page = $g[1];
				else
					mydie("can not get program pages \n");
			}
			preg_match_all('@<li>\s+<div class="corporate">\s+<a href="/partnerprogramme/(\d+)"(.*?)</li>@ms', $content, $chapters);
			foreach ((array)$chapters[2] as $key => $chapter)
			{
				$id = $chapters[1][$key];
				$program = array(
						"Name" => '',
						"AffId" => $this->info["AffId"],
						"TargetCountryExt" => '',
						"TargetCountryInt" => '',
						"Contacts" => '',
						"IdInAff" => $id,
						"RankInAff" => 0,
						"JoinDate" => '',
						"StatusInAff" => 'Active',						//'Active','TempOffline','Offline'
						"StatusInAffRemark" => '',
						"Partnership" => 'NoPartnership',				//'NoPartnership','Active','Pending','Declined','Expired','Removed'
						//"Homepage" => addslashes(sprintf("https://www.adcell.de/default/partnerprogram/visit/programId/%s", $id)),
						"EPCDefault" => '',
						"EPC90d" => '',
						"Description" => '',
						"CommissionExt" => '',
						"CookieTime" => 0,
						"DetailPage" => addslashes('https://www.adcell.de/partnerprogramme/' . $id),
						"LastUpdateTime" => date("Y-m-d H:i:s"),
				);
				
				//find homepage
				if($tmp_url = $this->oLinkFeed->findFinalUrl("https://www.adcell.de/default/partnerprogram/visit/programId/$id")){
					$program["Homepage"] = $tmp_url;
				}

				if (preg_match('@<h2>(.*?)</h2>\s+</a>\s+<p>(.*?)</p>@ms', $chapter, $g))
				{
					$program['Name'] = addslashes($g[1]);
					$program['Description'] = addslashes($g[2]);
				}
				if (preg_match('@>\s+TOP-PARTNER\s+<@', $chapter, $g))
					$program['RankInAff'] = 1;
				if (preg_match_all('@class="flags.*?"\s+alt="(.*?)"@', $chapter, $g))
				{
					$program['TargetCountryExt'] = addslashes(implode(',', $g[1]));
					$program['TargetCountryInt'] = $this->getTargetCountryInt($program['TargetCountryExt']);
				}
				if (preg_match('@<th>Lead-Provisionen</th>\s+<td>(.*?)</td>@', $chapter, $g))
					$program['CommissionExt'][] = "Lead:" .$g[1];
				if (preg_match('@<th>Sale-Provisionen</th>\s+<td>(.*?)</td>@', $chapter, $g))
					$program['CommissionExt'][] = "Sale:" .$g[1];
				if (!empty($program['CommissionExt']))
					$program['CommissionExt'] = addslashes(implode('|', $program['CommissionExt']));
				if (preg_match('@<th>Cookie</th>\s+<td>(\d+)\s+\w+</td>@', $chapter, $g))
					$program['CookieTime'] = addslashes($g[1]);
				if (preg_match('@<th>Start</th>\s+<td>(.*?)</td>@', $chapter, $g))
					$program['JoinDate'] = parse_time_str($g[1], 'd.m.Y', false);
				if (preg_match('@<th>Status</th>\s+<td>\s+.*?title="(.*?)".*?</td>@', $chapter, $g))
				{
					$program['StatusInAffRemark'] = strtolower($g[1]);
					switch (strtolower($g[1]))
					{
						case 'bearbeitung':
							$program['Partnership'] = 'Pending';
							break;
						case 'angenommen':
							$program['Partnership'] = 'Active';
							break;
						case 'gekündigt':
							$program['Partnership'] = 'Declined';
							break;
					}
				}

                if(!$this->getStatus) {
                    $url = 'https://www.adcell.de/partnerprogramme/' . $id;
                    $r = $this->oLinkFeed->GetHttpResult($url, $request);
                    $content = $r['content'];
                    if (!empty($content)) {
                        $program = array_merge($program, array(
                            "CategoryExt" => '',
                            "SEMPolicyExt" => '',
                            "TermAndCondition" => '',
                            "ProtectedSEMBiddingKeywords" => '',
                            "NonCompeteSEMBiddingKeywords" => '',
                            "RecommendedSEMBiddingKeywords" => '',
                            "ProhibitedSEMDisplayURLContent" => '',
                            "LimitedUseSEMDisplayURLContent" => '',
                            "ProhibitedSEMAdCopyContent" => '',
                            "LimitedUseSEMAdCopyContent" => '',
                            "AuthorizedSearchEngines" => '',
                            "SpecialInstructionsForSEM" => '',
                            "ProhibitedWebSiteURLAndContent" => '',
                            "UnacceptableWebSitesExt" => '',
                            "CouponCodesPolicyExt" => '',
                            "SubAffPolicyExt" => '',
                            "AllowedDirectLink" => '',
                        ));
                        if (preg_match('@<div class="customerStyle">(.*?)</div>@ms', $content, $g))
                            $program['TermAndCondition'] = addslashes(trim($g[1]));
                        if (preg_match('@<tr>\s+<td>SEM</td>\s+<td>(.*?)</td>\s+<td>(.*?)</td>@ms', $content, $g))
                            $program['SEMPolicyExt'] = addslashes(sprintf("%s: %s", trim($g[1]), trim(strip_tags($g[2]))));
                        if (preg_match_all('@"/partnerprogramme/category/\d+">(.*?)</a>@', $content, $g))
                            $program['CategoryExt'] = addslashes(implode(',', $g[1]));
                    }
                }
				$programs[$id] = $program;
			}
			$p = $this->getProgramObj();
			$p->updateProgram($this->info["AffId"], $programs);
			$programs = array();
			$page ++;
		}while ($page <= $last_page && $page < 1000);*/
		
		
		$this->checkProgramOffline($this->info["AffId"], date("Y-m-d"));
	}

	private function checkProgramOffline($AffId, $check_date)
	{
		$p = $this->getProgramObj();
		$prgm = $p->getNotUpdateProgram($this->info["AffId"], $check_date);
		if(count($prgm) > 50)
			mydie("die: too many offline program (".count($prgm).").\n");
		else
		{
			$p->setProgramOffline($this->info["AffId"], $prgm);
			echo "\tSet (".count($prgm).") offline program.\r\n";
		}
	}
	
	function getMessage()
	{
		$messages = array();
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", );
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);
		
		$type_arr = array('condition_changes','partnership_leaved','new_coupon_promotion');
		$startDate = date("d.m.Y", strtotime("-7days"));
		foreach($type_arr as $type){
			$time = time();
			$page = 1;
			$user_data = array();			
			$hasNextPage = true;
			$total = 1;
			while($hasNextPage){
				$url = "https://www.adcell.de/default/messages/list?type=$type&startDate=$startDate&endDate=&matches=&_search=false&nd=$time&rows=13&page=$page&sidx=created&sord=desc";
				$r = $this->oLinkFeed->GetHttpResult($url, $request);
				$content = $r['content'];				
				$content = json_decode($content);
				
				$total = intval($content->total);
				if($total > $page){
					$page++;
				}else{
					$hasNextPage = false;
				}
				
				if(!count($user_data)){
					$user_data = $content->userdata;					
				}
				
				$rows = array();
				$rows = $content->rows;				
				
				foreach($rows as $tmp_msg){
					$cell_arr = array();				
					
					$i = 0;
					foreach($user_data as $k => $v){
						$cell_arr[$k] = $tmp_msg->cell[$i];
						$i++;
					}					
					
					if($tmp_msg->id != $cell_arr["id"]) continue;
					
					$data = array(
						'affid' => $this->info["AffId"],
						'messageid' => $cell_arr['id'],
						'sender' => $cell_arr["sendFromName"],
						'title' => $cell_arr["subject"],
						'content' => trim(html_entity_decode($cell_arr["content"])),
						'created' => date("Y-m-d H:i:s", strtotime($cell_arr["created"])),
						);
					$messages[] = $data;					
				}				
			}
		}
		return $messages;
	}

}
