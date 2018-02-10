<?php

require_once 'text_parse_helper.php';

class LinkFeed_TD
{

	function LoginIntoAffService()
	{
		//get para __VIEWSTATE and then process default login
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "post",
			"postdata" => $this->info["AffLoginPostString"],
			"maxredirs" => 4,//if we dont set this, it will be failed at the fifth redir
			//"verbose" => 1, //for debug
			//"referer" => "https://publisher.tradedoubler.com/index.html",
			//"autoreferer" => 1,
		);
		$strUrl = $this->info["AffLoginUrl"];
		$arr = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$request = array("AffId" => $this->info["AffId"], "method" => "get",);
		$strUrl = "https://publisher.tradedoubler.com/publisher/aStartPage.action";
		$arr = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		if($this->info["AffLoginVerifyString"] && stripos($arr["content"], $this->info["AffLoginVerifyString"]) !== false)
		{
			echo "verify succ: " . $this->info["AffLoginVerifyString"] . "\n";
			return true;
		}
		else
		{
			print_r($arr);
			echo "verify failed: " . $this->info["AffLoginVerifyString"] . "\n";
		}
		return false;
	}

	function getSiteId()
	{
		$arr_return = array();
		if(SID =='bdg01'){
			switch ($this->info["AffId"]){
				case 5: // UK
					$arr_return["en_UK"]['2964534'] = "UK";
					break;
				case 27: //IE
					$arr_return["en_IE"]['2935399'] = "IE";
					break;
				case 35: // DE
					$arr_return["de_DE"]['2916622'] = "DE";
					break;
				case 133: // US
					$arr_return["en_GB"]["2914698"] = "US";
					break;
				case 415: //AT
					$arr_return["en_AT"]['2935407'] = "AT";
					break;
				case 429: //CH
					$arr_return["en_CH"]['2935401'] = "CH";
					break;
				case 469: //FR
					$arr_return["en_FR"]['2916623'] = "FR";
					break;
				default:
					mydie("die:Wrong AffID for LinkFeed_TD\n");
			}
		}else{
			switch ($this->info["AffId"]){
				case 5: // UK
					$arr_return["en_UK"]['2834470'] = "UK";
					break;
				case 27: // IE
					$arr_return["en_IE"]['2912160'] = "IE";
					break;
				case 35: // DE
					$arr_return["de_DE"]['2912154'] = "DE";
					break;
				case 415: //AT
					$arr_return["en_AT"]['2912155'] = "AT";
					break;
				case 429: //CH
					$arr_return["en_CH"]['2912156'] = "CH";
					break;
				case 469: //FR
					$arr_return["en_FR"]['2872046'] = "FR";
					break;
				case 667: //BE
					$arr_return["en_BE"]['2912157'] = "BE";
					break;
                case 769: //HK
                    $arr_return["en_HK"]['2990235'] = "HK";
                    break;
                case 770: //SG
                    $arr_return["en_SG"]['2912152'] = "SG";
                    break;
                case 2036: //MY
                    $arr_return["en_MY"]['2960162'] = "MY";
                    break;
                case 2037: //PH
                    $arr_return["en_PH"]['2960220'] = "PH";
                    break;
                case 2038: //AU
                    $arr_return["en_AU"]['2988825'] = "AU";
                    break;
                case 2039: //NZ
                    $arr_return["en_NZ"]['2988841'] = "NZ";
                    break;
                case 2040: //RU
                    $arr_return["en_RU"]['2996388'] = "RU";
                    break;
                case 2050: //US
                    $arr_return["en_US"]['2953198'] = "US";
                    break;
				default:
					mydie("die:Wrong AffID for LinkFeed_TD\n");
			}
		}
		
		return $arr_return;
	}

	function getSiteId_simple()
	{	
		if(SID =='bdg01'){
			switch ($this->info["AffId"]){
				case 5: // UK
					return 2964534;
				case 27: // IE
					return 2935399;
				case 35: // DE
			    	return 2916622;
			    case 133: // UK
			   		return 2914698;
			   	case 415: //AT
			   		return 2935407;
			   	case 429: //CH
			   		return 2935401;
			   	case 469: //FR
			   		return 2916623;
			   	default:
			   		mydie("die:Wrong AffID for LinkFeed_TD\n");
			}
		}else{
			switch ($this->info["AffId"]){
				case 5: // UK
					return 2834470;
				case 27: // IE
					return 2912160;
				case 35: // DE
					return 2912154;
				case 415: //AT
					return 2912155;
				case 429: //CH
					return 2912156;
				case 469: //FR
					return 2872046;
				case 667: //BE
					return 2912157;
                case 769: //HK
                    return 2990235;
                case 770: //SG
                    return 2912152;
                case 2036: //MY
                    return 2960162;
                case 2037: //PH
                    return 2960220;
                case 2038: //AU
                    return 2988825;
                case 2039: //NZ
                    return 2988841;
                case 2040: //RU
                    return 2996388;
                case 2050: //US
                    return 2953198;
				default:
					mydie("die:Wrong AffID for LinkFeed_TD\n");
			}
		}
		
	}

	function getToken(){
	    $token = '';
	    if(SID =='bdg01'){
	    	switch ($this->info["AffId"]){
	    		case 5:  //UK
	    			$token='1C7A38A7A6B03405FEC64E100BFF2327649E6CC0';
	    			break;
	    		case 27: //IE
	    			$token='70F42A82EDC67057C5808E6B9226AA6302FA169B';
	    			break;
	    		case 35: //DE
	    			$token='070117D499FA47A86FF8601215C3F6BCD0C27EA8';
	    			break;
	    		case 133://US
	    			$token='A63F5FB4236FB7053AD60AFD3C00331F99923183';
	    			break;
	    		case 415: //AT
	    			$token='D1B91BFE1F45C1AEA46C06CBC31E4FAE7EB7A63E';
	    			break;
	    		case 429: //CH
	   				$token='F219CA2E5C05B4F4D5775FF0153FAA6089B673AE';
	    			break;
	    		case 469://FR
	    			$token='2B33D4B350E9356D39B712C366E967052A67550D';
	    			break;
	    	}
	    }else{
	    	switch ($this->info["AffId"]){
	    		case 5:  //UK
	    			$token='8FC18CCB2C804DC1F7BE27CDED3921325A857F2F';
	    			break;
	    		case 27: // IE
	    			$token='0ABBAB6E8E637B49BDFEB06BB981D109A678FBF2';
	    			break;
	    		case 35: //DE
	    			$token='CD4DB06A02A311E14FA0A4D348B052D749317E52';
	    			break;
	    		case 415: //AT
	    			$token='41EECA434CCD0254AE75F15CE2D39ADFFBB290FD';
	    			break;
	    		case 429: //CH
	    			$token='47E8820C186CFA0471B7E8990219F423071B307B';
	    			break;
	    		case 469: //FR
	    			$token='A969ED4291691DFDBDBB876772F2828F7BF3DFC9';
	    			break;
	    		case 667: //BE
	    			$token='6EF476DB395AF109D608E95BF5AFADA660167CC5';
	    			break;
                case 769: //HK
                    $token='ECED12187DF9B0FC9CFA5CB882DBF7F397872FF2';
                    break;
                case 770: //SG
                    $token='3F7278A8FBEA3E29F5C3D3B06D58F98089EF52A6';
                    break;
                case 2036: //MY
                    $token='79ADCB399A8BEE83C7B4F4706907BFED81B6ADF7';
                    break;
                case 2037: //PH
                    $token='3B21BD03557E4F48A0C2E5B43A93AA9E0C4ECAFC';
                    break;
                case 2038: //AU
                    $token='6A59296663630CC1C2AD4A88F6C679FC7DC4BD07';
                    break;
                case 2039: //NZ
                    $token='5BF40AA4923B243A410586EBAB831E142E6FF3C1';
                    break;
                case 2040: //RU
                    $token='B9A6DA6074C9C39F106D09046A3B3C2F2AA9B2EB';
                    break;
                case 2050: //US
                    $token='55B8E5A78C3E171FC4417CCF5371E96FC3108D6D';
                    break;
	    		default:
	    			mydie("die:Wrong AffID for LinkFeed_TD\n");
	    	}
	    }
	    
	    return $token;
	}
	
	function getProductToken(){
	    $token = '';
	    if(SID =='bdg01'){
	        
	    }else{
	        switch ($this->info["AffId"]){
	            case 5:  //UK
	                $token='D51D1116F754422851F7596496E5238A66FBCBD0';
	                break;
	            case 27: // IE
	                $token='F765417FE67683CC63E9DEEE97DE4AB4527B0456';
	                break;
	            case 35: //DE
	                $token='05C63CD538703D5D572FE69763248CDDF50AFF59';
	                break;
	            case 415: //AT
	                $token='051F7689573BB26E1C22F37F696BC2B46AABAAB3';
	                break;
	            case 429: //CH
	                $token='6A9F8B39C989E6B8B1CDBF06EC37B6487F205852';
	                break;
	            case 469: //FR
	                $token='6692086EA9A4DA926ECA1C719CD7DEB40EA67B98';
	                break;
	            case 667: //BE
	                $token='926CBD64F7F21915C43B733BCCC0D42EFE54F93D';
	                break;
                case 769: //HK
                    $token='99F958E4A8171ED3D90B5FD1FD5DFC1330B97E4C';
                    break;
                case 770: //SG
                    $token='757BC8C580981674A7CCDF4783F1DF779395171A';
                    break;
                case 2036: //MY
                    $token='F6F42E15F7F69458EDD763CE75DD7BEF2ECA7829';
                    break;
                case 2037: //PH
                    $token='F14D9B2E4ED4B49E4A78D2713B8B49F46EB7D7FC';
                    break;
                case 2038: //AU
                    $token='C7314A334DF46BF4A703B96BFCD6EBEEF121F1EF';
                    break;
                case 2039: //NZ
                    $token='22F393FFA1F3149C3E58BD3012E8EB075FC16AC8';
                    break;
                case 2040: //RU
                    $token='A6DA5CD5A7E02942A4F14EDD9B46627535F8E658';
                    break;
                case 2050: //US
                    $token='C26D64864B305E89D45BF43334B9E45A61A96EBA';
                    break;
	            default:
	                mydie("die:Wrong AffID for LinkFeed_TD\n");
	        }
	    }
	     
	    return $token;
	}
	
	function GetAllProductsByAffId()
	{
	    ##TD 是全部爬取product的，所以不用做限制数量 
	    $check_date = date('Y-m-d H:i:s');
	    $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
	    $request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "");
	
	    $token = $this->getProductToken();
	    if(empty($token))
	        mydie("Api token not exist. \n");
	    
	    $pageSize = 100;
	    $pageNumber = 0;
	    do{
	        $pageNumber ++;
	        $url = "http://api.tradedoubler.com/1.0/products.json;page=$pageNumber;pageSize=$pageSize?token={$token}";
	        echo $url.PHP_EOL;
	        $r = $this->oLinkFeed->GetHttpResult($url, $request);
	        $content = $r['content'];
	        $data = @json_decode($content);
	        $totalMatched = $data->productHeader->totalHits;
	        
	        foreach ($data->products as $value){
	            
	            //print_r($value);exit; 
	            $offers = $value->offers;
	            $offer = $offers[0];
	            $productUrl = $offer->productUrl;
	            if(preg_match('/p\((\d+)\)/',$productUrl,$matches)){
	                $merchantId = $matches[1];
	            }
	            else{
	                continue;
	            }
	            $linkid = $offer->id;
	             
	            
	            //下载图片
	            $product_path_file = $this->oLinkFeed->getProductImgFilePath($this->info["AffId"],"{$merchantId}_".urlencode($linkid).".png", PRODUCTDIR);
	            //echo $product_path_file;exit;
	            if(!$this->oLinkFeed->fileCacheIsCached($product_path_file)){
	                $file_content = $this->oLinkFeed->downloadImg($value->productImage->url);
	                if(!$file_content) //下载不了跳过。
	                    continue;
	                $this->oLinkFeed->fileCachePut($product_path_file, $file_content);
	            
	            }
	            
	            $priceHistorys = $offer->priceHistory;
	            $priceHistory = $priceHistorys[0];
	            $currency = $priceHistory->price->currency;
	            $price   = $priceHistory->price->value;
	             
	            $link = array(
	                "AffId" => $this->info["AffId"],
	                "AffMerchantId" => $merchantId,
	                "AffProductId" => $linkid,
	                "ProductName" => addslashes($value->name),
	                "ProductCurrency" => $currency,
	                "ProductPrice" => $price,
	                "ProductOriginalPrice" => '',
	                "ProductRetailPrice" =>'',
	                "ProductImage" => addslashes($value->productImage->url),
	                "ProductLocalImage" => addslashes($product_path_file),
	                "ProductUrl" => addslashes($productUrl),
	                "ProductDestUrl" => '',
	                "ProductDesc" => addslashes($value->description),
	                "ProductStartDate" => '',
	                "ProductEndDate" => '',
	            );
	            //print_r($link);exit;
	            $links[] = $link;
	            $arr_return['AffectedCount'] ++;
	        }
	        
	        if (count($links))
	        {
	            $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateProductToDB($links);
	            $links = array();
	        }
	    }while ($pageSize * $pageNumber < $totalMatched);
	    
	    $this->oLinkFeed->checkProductExists($this->info["AffId"], $check_date);
	    	
	    echo 'END time'.date('Y-m-d H:i:s').PHP_EOL;
	    return $arr_return;
	}
	
	function getCouponFeed()
	{
	    $check_date = date('Y-m-d H:i:s');
	    $arr_return = array("AffectedCount" => 0,"UpdatedCount" => 0,"Detail" => array());
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "");
		$token = $this->getToken();
		if(empty($token))
			mydie("Api token not exist. \n");
		$url = "http://api.tradedoubler.com/1.0/vouchers.json?token={$token}";
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		 
		$content = $r['content'];
		// sometime the td api server input charater 7bf at start of the josn and 0 at end of the json
		// and the end of the ] is missing.
		// this maybe the api server wrong.
		// fix the json and wait td change it.
		// remove the 7bf and the 0 charater and add ].
		$content = trim($content);
		if (substr($content, 0, 3) == '7bf')
			$content = substr($content, 3);
		if (substr($content, -1, 1) == '0')
			$content = substr($content, 0, -1);
		$content = trim($content);
		if (substr($content, -1, 1) == '}')
			$content .= ']';
		// above code is the fixing json code.
		// delete when the server runs normal.
		$data = @json_decode($content);
		$links = array();
		foreach((array)$data as $v)
		{
			$link = array(
					"AffId" => $this->info["AffId"],
					"AffMerchantId" => $v->programId,
					"AffLinkId" => $v->id,
					"LinkName" => trim($v->title),
					"LinkDesc" => trim($v->description),
					"LinkStartDate" => parse_time_str(trim($v->startDate), 'millisecond', false),
					"LinkEndDate" => parse_time_str(trim($v->endDate), 'millisecond', false),
					"LinkPromoType" => 'COUPON',
					"LinkHtmlCode" => '',
					"LinkCode" => trim($v->code),
					"LinkOriginalUrl" => empty($v->landingUrl) ? '' : trim($v->landingUrl),
					"LinkImageUrl" => empty($v->logoPath) ? '' : trim($v->logoPath),
					"LinkAffUrl" => trim($v->defaultTrackUri),
					"DataSource" => $this->DataSource["feed"],
			        "IsDeepLink" => 'UNKNOWN',
			        "Type"       => 'promotion'
			);
			$link['LinkHtmlCode'] = create_link_htmlcode($link);
			if (empty($link['AffMerchantId']) || empty($link['LinkName']) || empty($link['AffLinkId']))
				continue;
			$arr_return["AffectedCount"] ++;
			$links [] = $link;
			if(sizeof($links) > 100)
			{
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
				$links = array();
			}
		}
		if (sizeof($links) > 0)
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		echo sprintf("get coupon by api...%s link(s) found.\n", $arr_return['AffectedCount']);
		if($arr_return['AffectedCount'] > 0){
		    $this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'promotion');
		}
		return $arr_return;
	}
	
	function GetAllLinksByAffId(){
	    
	    
	    $check_date = date('Y-m-d H:i:s');
	    $arr_return = array("AffectedCount" => 0,"UpdatedCount" => 0,);
	    $request = array("AffId" => $this->info["AffId"],"method" => "post","postdata" => "",);
	    $site_id = $this->getSiteId_simple();
	    $searchTypes = array('ge', 'textLinks');
	    $links = array();
	    $this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
	    
	    $arr_merchant = $this->oLinkFeed->getApprovalAffMerchant($this->info["AffId"]);
	    foreach ($arr_merchant as $merinfo){
	        
	        list($nNumPerPage, $bHasNextPage, $nPageNo) = array(100, true, 1);
	        while($bHasNextPage && $nPageNo < 50)
	        {
	            $aff_mer_id = $merinfo["AffMerchantId"];
	            $request["method"] = "post";
	            $url = "https://publisher.tradedoubler.com/pan/aGEList.action";
	            $request["postdata"] = sprintf("programGEListParameterTransport.currentPage=%s&searchPerformed=true&searchType=ge&programGEListParameterTransport.programIdOrName=%s&programGEListParameterTransport.deepLinking=&programGEListParameterTransport.tariffStructure=&programGEListParameterTransport.siteId=%s&programGEListParameterTransport.orderBy=lastUpdated&programGEListParameterTransport.websiteStatusId=&programGEListParameterTransport.pageSize=%s&programAdvancedListParameterTransport.directAutoApprove=&programGEListParameterTransport.graphicalElementTypeId=&programGEListParameterTransport.specialConditionId=&programGEListParameterTransport.graphicalElementSize=&programGEListParameterTransport.width=&programGEListParameterTransport.height=&programGEListParameterTransport.lastUpdated=&programGEListParameterTransport.graphicalElementNameOrId=&programGEListParameterTransport.showGeGraphics=true&programAdvancedListParameterTransport.pfAdToolUnitName=&programAdvancedListParameterTransport.pfAdToolProductPerCell=&programAdvancedListParameterTransport.pfAdToolDescription=&programAdvancedListParameterTransport.pfTemplateTableRows=&programAdvancedListParameterTransport.pfTemplateTableColumns=&programAdvancedListParameterTransport.pfTemplateTableWidth=&programAdvancedListParameterTransport.pfTemplateTableHeight=&programAdvancedListParameterTransport.pfAdToolContentUnitRule=",
	                $nPageNo, $aff_mer_id, $site_id, $nNumPerPage);
	            $r = $this->oLinkFeed->GetHttpResult($url,$request);
	            $content = $r["content"];
	            preg_match_all('@onClick="hideGEIframe\((\d+)\);(.*?)<td colspan="14".*?>.*?</tr>@ms', $content, $chapters);
	            foreach ((array)$chapters[0] as $key => $chapter)
	            {
	                $link = array(
	                    "AffId" => $this->info["AffId"],
	                    "AffMerchantId" => $aff_mer_id,
	                    "AffLinkId" => $chapters[1][$key],
	                    "LinkName" => '',
	                    "LinkDesc" => '',
	                    "LinkStartDate" => '0000-00-00',
	                    "LinkEndDate" => '0000-00-00',
	                    "LinkPromoType" => 'N/A',
	                    "LinkHtmlCode" => '',
	                    "LinkCode" => '',
	                    "LinkOriginalUrl" => '',
	                    "LinkImageUrl" => '',
	                    "LinkAffUrl" => '',
	                    "DataSource" => $this->DataSource["website"],
	                    "IsDeepLink" => 'UNKNOWN',
	                    "Type"       => 'link'
	                );
	                $link['LinkAffUrl'] = sprintf('http://clkuk.tradedoubler.com/click?p=%s&a=%s&g=%s', $aff_mer_id, $site_id, $link['AffLinkId']);
	                $link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
	                if (preg_match('@<span id="geText\d+">(.*?)</span>@ms', $chapter, $g))
	                    $link['LinkName'] = html_entity_decode(strip_tags(trim($g[1])));
	                if (preg_match('@<td>\s+(\d+/\d+/\d+)</td>@', $chapter, $g))
	                    $link['LinkStartDate'] = parse_time_str($g[1], 'd/m/Y', false);
	                if (preg_match('@<td>\s+JPG/GIF</td>@i', $chapter))
	                {
	                    $link['LinkImageUrl'] = sprintf('http://www.tradedoubler.com/pan/aGEGraphicalElementPreview.action?programId=%s&graphicalElementId=%s', $aff_mer_id, $link['AffLinkId']);
	                    if (preg_match('@<td colspan="14" style="z-index: 1; position: relative;">(.*?)</td>@ms', $chapter, $g))
	                    {
	                        $g[1] = str_replace('src="/pan/', 'src="http://www.tradedoubler.com/pan/', $g[1]);
	                        $link['LinkDesc'] = str_replace('<iframe  src="/', '<iframe  src="http://www.tradedoubler.com/', $g[1]);
	                    }
	                }
	                else if (preg_match('@<td>\s+Text links</td>@i', $chapter))
	                {
	                    if (preg_match('@<td colspan="14" style="z-index: 1; position: relative;">(.*?)</td>@ms', $chapter, $g))
	                        $link['LinkName'] = trim(html_entity_decode(strip_tags($g[1])));
	                    $link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
	                    $link['LinkHtmlCode'] = create_link_htmlcode($link);
	                }
	                else
	                    $link['LinkHtmlCode'] = create_link_htmlcode($link);
	                $code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc']);
	                if (!empty($code))
	                {
	                    $link['LinkPromoType'] = 'COUPON';
	                    $link['LinkCode'] = $code;
	                }
	        
	                if (empty($link['AffLinkId']))
	                    continue;
	                elseif(empty($link['LinkName'])){
	                    $link['LinkPromoType'] = 'link';
	                }
	                $this->oLinkFeed->fixEnocding($this->info,$link,"link");
	                $links[] = $link;
	                $arr_return["AffectedCount"] ++;
	            }
	            echo sprintf("get link by page...program:%s, page:%s, %s link(s) found.\n", $aff_mer_id, $nPageNo, count($links));
	            if (count($links) > 0)
	                $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
	            $links = array();
	            $bHasNextPage = false;
	            if (preg_match('@\&nbsp;\d+\&nbsp;(.*?)</div>@', $content, $g))
	            {
	                if (preg_match('@A HREF="@i', $g[1]))
	                    $bHasNextPage = true;
	            }
	            $nPageNo ++;
	        }
	        
	    }
	    $this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link');
	    return $arr_return;
	    
	}

	function GetAllLinksFromAffByMerID($merinfo)
	{
		// get banner & text links by page.
		$aff_mer_id = $merinfo["AffMerchantId"];
		$check_date = date('Y-m-d H:i:s');
		 
		$arr_return = array("AffectedCount" => 0,"UpdatedCount" => 0,);
		$request = array("AffId" => $this->info["AffId"],"method" => "post","postdata" => "",);
		$site_id = $this->getSiteId_simple();
		$searchTypes = array('ge', 'textLinks');
		$links = array();
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		list($nNumPerPage, $bHasNextPage, $nPageNo) = array(100, true, 1);
		while($bHasNextPage && $nPageNo < 50)
		{
			$request["method"] = "post";
			$url = "https://publisher.tradedoubler.com/pan/aGEList.action";
			$request["postdata"] = sprintf("programGEListParameterTransport.currentPage=%s&searchPerformed=true&searchType=ge&programGEListParameterTransport.programIdOrName=%s&programGEListParameterTransport.deepLinking=&programGEListParameterTransport.tariffStructure=&programGEListParameterTransport.siteId=%s&programGEListParameterTransport.orderBy=lastUpdated&programGEListParameterTransport.websiteStatusId=&programGEListParameterTransport.pageSize=%s&programAdvancedListParameterTransport.directAutoApprove=&programGEListParameterTransport.graphicalElementTypeId=&programGEListParameterTransport.specialConditionId=&programGEListParameterTransport.graphicalElementSize=&programGEListParameterTransport.width=&programGEListParameterTransport.height=&programGEListParameterTransport.lastUpdated=&programGEListParameterTransport.graphicalElementNameOrId=&programGEListParameterTransport.showGeGraphics=true&programAdvancedListParameterTransport.pfAdToolUnitName=&programAdvancedListParameterTransport.pfAdToolProductPerCell=&programAdvancedListParameterTransport.pfAdToolDescription=&programAdvancedListParameterTransport.pfTemplateTableRows=&programAdvancedListParameterTransport.pfTemplateTableColumns=&programAdvancedListParameterTransport.pfTemplateTableWidth=&programAdvancedListParameterTransport.pfTemplateTableHeight=&programAdvancedListParameterTransport.pfAdToolContentUnitRule=",
				$nPageNo, $aff_mer_id, $site_id, $nNumPerPage);
			$r = $this->oLinkFeed->GetHttpResult($url,$request);
			$content = $r["content"];
			preg_match_all('@onClick="hideGEIframe\((\d+)\);(.*?)<td colspan="14".*?>.*?</tr>@ms', $content, $chapters);
			foreach ((array)$chapters[0] as $key => $chapter)
			{
				$link = array(
						"AffId" => $this->info["AffId"],
						"AffMerchantId" => $aff_mer_id,
						"AffLinkId" => $chapters[1][$key],
						"LinkName" => '',
						"LinkDesc" => '',
						"LinkStartDate" => '0000-00-00',
						"LinkEndDate" => '0000-00-00',
						"LinkPromoType" => 'N/A',
						"LinkHtmlCode" => '',
						"LinkCode" => '',
						"LinkOriginalUrl" => '',
						"LinkImageUrl" => '',
						"LinkAffUrl" => '',
						"DataSource" => $this->DataSource["website"],
				        "IsDeepLink" => 'UNKNOWN',
				        "Type"       => 'link'
				);
				$link['LinkAffUrl'] = sprintf('http://clkuk.tradedoubler.com/click?p=%s&a=%s&g=%s', $aff_mer_id, $site_id, $link['AffLinkId']);
				$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
				if (preg_match('@<span id="geText\d+">(.*?)</span>@ms', $chapter, $g))
					$link['LinkName'] = html_entity_decode(strip_tags(trim($g[1])));
				if (preg_match('@<td>\s+(\d+/\d+/\d+)</td>@', $chapter, $g))
					$link['LinkStartDate'] = parse_time_str($g[1], 'd/m/Y', false);
				if (preg_match('@<td>\s+JPG/GIF</td>@i', $chapter))
				{
					$link['LinkImageUrl'] = sprintf('http://www.tradedoubler.com/pan/aGEGraphicalElementPreview.action?programId=%s&graphicalElementId=%s', $aff_mer_id, $link['AffLinkId']);
					if (preg_match('@<td colspan="14" style="z-index: 1; position: relative;">(.*?)</td>@ms', $chapter, $g))
					{
						$g[1] = str_replace('src="/pan/', 'src="http://www.tradedoubler.com/pan/', $g[1]);
						$link['LinkDesc'] = str_replace('<iframe  src="/', '<iframe  src="http://www.tradedoubler.com/', $g[1]);
					}
				}
				else if (preg_match('@<td>\s+Text links</td>@i', $chapter))
				{
					if (preg_match('@<td colspan="14" style="z-index: 1; position: relative;">(.*?)</td>@ms', $chapter, $g))
						$link['LinkName'] = trim(html_entity_decode(strip_tags($g[1])));
					$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
					$link['LinkHtmlCode'] = create_link_htmlcode($link);
				}
				else
					$link['LinkHtmlCode'] = create_link_htmlcode($link);
				$code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc']);
				if (!empty($code))
				{
					$link['LinkPromoType'] = 'COUPON';
					$link['LinkCode'] = $code;
				}
				
				if (empty($link['AffLinkId']))
					continue;
                elseif(empty($link['LinkName'])){
                    $link['LinkPromoType'] = 'link';
                }
				$this->oLinkFeed->fixEnocding($this->info,$link,"link");
				$links[] = $link;
				$arr_return["AffectedCount"] ++;
			}
			echo sprintf("get link by page...program:%s, page:%s, %s link(s) found.\n", $aff_mer_id, $nPageNo, count($links));
			if (count($links) > 0)
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
			$links = array();
			$bHasNextPage = false;
			if (preg_match('@\&nbsp;\d+\&nbsp;(.*?)</div>@', $content, $g))
			{
				if (preg_match('@A HREF="@i', $g[1]))
					$bHasNextPage = true;
			}
			$nPageNo ++;
		}
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link', $merinfo['IdInAff']);
		return $arr_return;
	}

	function GetProgramByPage()
	{
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		$arrCountrySite = $this->getSiteId();
		foreach($arrCountrySite as $conutry => $sites)
		{
			foreach($sites as $site_id => $contry_code)
			{
				$arr_result = $this->GetProgramBySiteId($site_id,$contry_code);
				$this->GetCommissionBySiteId($site_id);
			}
		}
	}

	function GetProgramFromAff()
	{
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
		$this->GetProgramByPage();
		$this->checkProgramOffline($this->info["AffId"], $check_date);
		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}

    function GetStatus(){
        $this->getStatus =true;
        $this->GetProgramFromAff();
    }

	function GetProgramBySiteId($site_id,$contry_code)
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

		$arr_return = array();
		$request = array("AffId" => $this->info["AffId"],"method" => "post","postdata" => "",);

		$nNumPerPage = 100;
		$nPageNo = 1;

		while(1)
		{
			if ($nPageNo == 1){
				$strUrl = "https://publisher.tradedoubler.com/pan/aProgramList.action";
				$request["method"] = "post";
				$request["postdata"] = "programGEListParameterTransport.currentPage=".$nPageNo."&searchPerformed=true&searchType=prog&programGEListParameterTransport.programIdOrName=&programGEListParameterTransport.deepLinking=&programGEListParameterTransport.tariffStructure=&programGEListParameterTransport.siteId=" . $site_id . "&programGEListParameterTransport.orderBy=statusId&programAdvancedListParameterTransport.websiteStatusId=&programGEListParameterTransport.pageSize=" . $nNumPerPage . "&programAdvancedListParameterTransport.directAutoApprove=&programAdvancedListParameterTransport.mobile=&programGEListParameterTransport.graphicalElementTypeId=&programGEListParameterTransport.graphicalElementSize=&programGEListParameterTransport.width=&programGEListParameterTransport.height=&programGEListParameterTransport.lastUpdated=&programGEListParameterTransport.graphicalElementNameOrId=&programGEListParameterTransport.showGeGraphics=true&programAdvancedListParameterTransport.pfAdToolUnitName=&programAdvancedListParameterTransport.pfAdToolProductPerCell=&programAdvancedListParameterTransport.pfAdToolDescription=&programAdvancedListParameterTransport.pfTemplateTableRows=&programAdvancedListParameterTransport.pfTemplateTableColumns=&programAdvancedListParameterTransport.pfTemplateTableWidth=&programAdvancedListParameterTransport.pfTemplateTableHeight=&programAdvancedListParameterTransport.pfAdToolContentUnitRule=";
				$this->oLinkFeed->GetHttpResult($strUrl,$request);
			}
			$strUrl = "https://publisher.tradedoubler.com/pan/aProgramList.action?categoryChoosen=false&programGEListParameterTransport.currentPage=".$nPageNo."&programGEListParameterTransport.pageSize=".$nNumPerPage."&programGEListParameterTransport.pageStreamValue=true";

			$request["postdata"] = "";
			$request["method"] = "get";

			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$result = $r["content"];

			//parse HTML
			$strLineStart = 'showPopBox(event, getProgramCodeAffiliate';
			$nLineStart = 0;
			$bStart = 1;
			while(1)
			{
				$nLineStart = stripos($result,$strLineStart,$nLineStart);
				if($nLineStart === false && $bStart == 1) break 2;
				if($nLineStart === false) break;
				$bStart = 0;

				$strMerID = $this->oLinkFeed->ParseStringBy2Tag($result, 'getProgramCodeAffiliate(', ',', $nLineStart);
				if($strMerID === false) break;
				$strMerID = trim($strMerID);
				if(empty($strMerID)) continue;
				
				//name
				$strMerName = $this->oLinkFeed->ParseStringBy2Tag($result, ">","</a>", $nLineStart);
				if($strMerName === false) break;
				$strMerName = html_entity_decode(trim($strMerName));
				
				$CategoryExt = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
				$CategoryExt = trim(str_replace("&nbsp;","",$CategoryExt));
				$arr_pattern = array();
				for($i=0;$i<8;$i++) $arr_pattern[] = "<td>";
				$EPC90d = $this->oLinkFeed->ParseStringBy2Tag($result,$arr_pattern, '</td>', $nLineStart);
				if($EPC90d === false) break;
				$EPC90d = trim(html_entity_decode(strip_tags($EPC90d)));
				if($EPC90d == "") $EPC90d = "";
				
				$EPCDefault = $this->oLinkFeed->ParseStringBy2Tag($result,'<td>','</td>', $nLineStart);
				if($EPCDefault === false) break;
				$EPCDefault = trim(html_entity_decode(strip_tags($EPCDefault)));
				if($EPCDefault == "") $EPCDefault = "";
				$MobileFriendly = trim(strtoupper($this->oLinkFeed->ParseStringBy2Tag($result, array('<td>','<td>'), '</td>', $nLineStart)));
				if ($MobileFriendly != 'YES' && $MobileFriendly != 'NO')
					$MobileFriendly = 'UNKNOWN';
				$strStatus = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
				if($strStatus === false) break;
				$strStatus = trim(strip_tags($strStatus));
				if(0 && $contry_code == "DE")
				{
					//warning: im not very sure for those de status ...
					if(stripos($strStatus,'Akzeptiert') !== false) $strStatus = 'approval';
					elseif(stripos($strStatus,'Unter Beobachtung') !== false) $strStatus = 'pending';
					elseif(stripos($strStatus,'Beendet') !== false) $strStatus = 'declined';
					elseif(stripos($strStatus,'In Bearbeitung') !== false) $strStatus = 'pending';
					elseif(stripos($strStatus,'Programmbewerbung') !== false) $strStatus = 'not apply';
					elseif(stripos($strStatus,'Abgelehnt') !== false) $strStatus = 'declined';
					else mydie("die: Unknown Status: $strStatus <br>\n");
				}
				else
				{
					if(stripos($strStatus,'Accepted') !== false) $strStatus = 'approval';
					elseif(stripos($strStatus,'Under Consideration') !== false) $strStatus = 'pending';
					elseif(stripos($strStatus,'Denied') !== false) $strStatus = 'declined';
					elseif(stripos($strStatus,'On Hold') !== false) $strStatus = 'not apply';
					elseif(stripos($strStatus,'Apply') !== false) $strStatus = 'not apply';
					elseif(stripos($strStatus,'Ended') !== false) $strStatus = 'declined';
					else mydie("die: Unknown Status: $strStatus <br>\n");
				}

				if(stripos($strMerName,'Closed') !== false) $strStatus = 'siteclosed';
				// when closing get the closing time and set to siteclosed when the time is less than now
				elseif(stripos($strMerName,'closing') !== false || stripos($strMerName,'pausing') !== false)
				{
					if (preg_match('@\d\d\.\d\d\.\d\d\d\d@', $strMerName, $g))
					{
						if (isset($g[0]) && strtotime($g[0]) < time())
							$strStatus = 'siteclosed';
					}
					if (preg_match('@(\d+)\/(\d+)\/(\d\d)@', $strMerName, $g))
					{
						if (strtotime(sprintf("20%s-%s-%s", $g[3], $g[2], $g[1])) < time())
							$strStatus = 'siteclosed';
					}
					if (preg_match('@(\d+)\/(\d+)\/(\d\d\d\d)@', $strMerName, $g))
					{
						if (strtotime(sprintf("%s-%s-%s", $g[3], $g[2], $g[1])) < time())
							$strStatus = 'siteclosed';
					}
					echo $strMerName."---".$strStatus."\n";
				}
				elseif(stripos($strMerName,'paused') !== false) $strStatus = 'siteclosed';
				elseif(stripos($strMerName,'set to pause') !== false) $strStatus = 'siteclosed';
				if($strStatus == 'approval'){
					$Partnership = "Active";
					$StatusInAff = "Active";
				}elseif($strStatus == 'pending'){
					$Partnership = "Pending";
					$StatusInAff = "Active";
				}elseif($strStatus == 'declined'){
					$Partnership = "Declined";
					$StatusInAff = "Active";
				}elseif($strStatus == 'not apply'){
					$Partnership = "NoPartnership";
					$StatusInAff = "Active";
				}else{
					$Partnership = "NoPartnership";
					$StatusInAff = "Offline";
				}

                if(!$this->getStatus) {
                	/*if($site_id == "$this->td_uk"){
                	 $TargetCountryExt = "UK";
                	 }elseif($site_id == "1470197"){
                	 $TargetCountryExt = "US";
                	 }elseif($site_id == "1634367"){
                	 $TargetCountryExt = "IE";
                	 }elseif($site_id == "1781705"){
                	 $TargetCountryExt = "DE";
                	 }elseif($site_id == "2489540"){
                	 $TargetCountryExt = "AT";
                	 }else{
                	 break;
                	 }*/
                	$TargetCountryExt = $contry_code;
                	$arr_prgm[$strMerID] = array(
                			"Name" => addslashes($strMerName),
                			"AffId" => $this->info["AffId"],
                			//"Contacts" => $Contacts,
                			"TargetCountryExt" => $TargetCountryExt,
                			"IdInAff" => $strMerID,
                			//"JoinDate" => date("Y-m-d H:i:s", strtotime($row["joinDate"])),
                			"StatusInAffRemark" => $strStatus,
                			"StatusInAff" => $StatusInAff,                        //'Active','TempOffline','Offline'
                			"Partnership" => $Partnership,                        //'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
                			//"Description" => addslashes($desc),
                			//"Homepage" => $Homepage,
                			"EPCDefault" => preg_replace("/[^0-9.]/", "", $EPCDefault),
                			"EPC90d" => preg_replace("/[^0-9.]/", "", $EPC90d),
                			//"TermAndCondition" => addslashes($TermAndCondition),
                			//"SupportDeepUrl" => $SupportDeepUrl,
                			"LastUpdateTime" => date("Y-m-d H:i:s"),
                			//"DetailPage" => $prgm_url,
                			"MobileFriendly" => addslashes($MobileFriendly),
                			//"AffDefaultUrl" => addslashes($AffDefaultUrl),
                			//"CommissionExt" => addslashes($CommissionExt),
                			"CategoryExt" => addslashes($CategoryExt),
                			//"AllowNonaffCoupon" => $AllowNonaffCoupon
                			"CommissionApd"=>'',
                	        "CategorySecond"=>'',
                	);
                	
                    $request["method"] = "get";
                    $request["postdata"] = "";
                    $prgm_url = "https://publisher.tradedoubler.com/pan/aProgramTextRead.action?programId={$strMerID}&affiliateId={$site_id}";
                    $arr_prgm[$strMerID]['DetailPage'] = addslashes($prgm_url);

                    if(!isset($cache[$strMerID]['detail'])){
                    	$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
                    	if($prgm_arr['code'] == 200){
                    		$results = $prgm_arr['content'];
                    		$prgm_detail = $prgm_arr["content"];
                    		$cache[$strMerID]['detail'] = "1";
                    		//print_r($cache);
                    		$desc = "<div>" . trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, '<div id="publisher-body">', '<div id="publisher-footer">'));
		                    $desc = preg_replace("/[\\r|\\n|\\r\\n|\\t]/is", '', $desc);
		                    $desc = preg_replace('/<([a-z]+?)\s+?.*?>/i', '<$1>', $desc);
		                    preg_match_all('/<([a-z]+?)>/i', $desc, $res_s);
		                    preg_match_all('/<\/([a-z]+?)>/i', $desc, $res_e);

		                    //egg's pain
		                    $tags_arr = array();
		                    foreach ($res_s[1] as $v) {
		                        if (strtolower($v) != "br") {
		                            if (isset($tags_arr[$v])) {
		                                $tags_arr[$v] += 1;
		                            } else {
		                                $tags_arr[$v] = 1;
		                            }
		                        }
		                    }
		                    foreach ($res_e[1] as $v) {
		                        if (strtolower($v) != "br" && isset($tags_arr[$v])) {
		                            $tags_arr[$v] -= 1;
		                        }
		                    }
		                    foreach ($tags_arr as $k => $v) {
		                        for ($i = 0; $i < $v; $i++) {
		                            $desc .= "</$k>";
		                        }
		                    }
		                    $arr_prgm[$strMerID]['Description'] = addslashes($desc);
		                    $AllowNonaffCoupon = 'UNKNOWN';
		                    if(		stripos($prgm_detail, 'Voucher codes are not redeemable') !== false 
									|| preg_match("/Voucher codes.*used with authorisation/i", $prgm_detail) 
									|| stripos($prgm_detail, "not offer discount codes or voucher codes") !== false
								)
							{
								$AllowNonaffCoupon = 'NO';
								$arr_prgm[$strMerID]['AllowNonaffCoupon'] = addslashes($AllowNonaffCoupon);
							}
                    	}
                    }

					$overview_url = "https://publisher.tradedoubler.com/pan/aProgramInfoApplyRead.action?programId={$strMerID}&affiliateId={$site_id}";
					if(!isset($cache[$strMerID]["contact"])){
						$overview_arr = $this->oLinkFeed->GetHttpResult($overview_url, $request);
						if($prgm_arr['code'] == 200){
							$overview_detail = $overview_arr["content"];
							$cache[$strMerID]["contact"] = "1";
							$Homepage = trim($this->oLinkFeed->ParseStringBy2Tag($overview_detail, array('Visit the site', '<a href="'), '"'));

							/*
							$CommissionExtStringStart = "Business information";
							$CommissionExtLineStart = stripos($overview_detail,$CommissionExtStringStart);
							$CommissionExt = trim($this->oLinkFeed->ParseStringBy2Tag($overview_detail, '<td valign', '<td valign', $CommissionExtLineStart));
							if(strlen($CommissionExt)) $CommissionExt = '<td valign'.$CommissionExt;
							*/

							$SupportDeepUrl = strtoupper(trim($this->oLinkFeed->ParseStringBy2Tag($overview_detail, array('Deep linking', '<td nowrap="nowrap">'), '</td>')));
							$LogoUrl = trim($this->oLinkFeed->ParseStringBy2Tag($overview_detail, array('<table border="0">', '<td><img src="'), '"></td>'));
							$CookieTime = intval(trim($this->oLinkFeed->ParseStringBy2Tag($overview_detail, array('Cookie time', '<td nowrap="nowrap">'), 'day')));
							$PaymentDays = intval(trim(html_entity_decode($this->oLinkFeed->ParseStringBy2Tag($overview_detail, array('Time to auto accept', '<td>', '<td>'), 'Day'))));
							
							//find homepage
		                    if ($tmp_url = $this->oLinkFeed->findFinalUrl($Homepage)) {
		                        $Homepage = $tmp_url;
		                    }
		                    //echo "$Homepage </br>";
							$arr_prgm[$strMerID]['Homepage'] = addslashes($Homepage);
//							$arr_prgm[$strMerID]['CommissionExt'] = addslashes($CommissionExt);
							$arr_prgm[$strMerID]['SupportDeepUrl'] = addslashes($SupportDeepUrl);
							$arr_prgm[$strMerID]['LogoUrl'] = addslashes($LogoUrl);
							$arr_prgm[$strMerID]['CookieTime'] = $CookieTime;
							$arr_prgm[$strMerID]['PaymentDays'] = $PaymentDays;
						}
                    }
                    $links_url = "https://publisher.tradedoubler.com/pan/aProgramInfoLinksRead.action?programId={$strMerID}&affiliateId={$site_id}";
                    if(!isset($cache[$strMerID]["links_detail"])){
                        $arr_prgm[$strMerID]['AffDefaultUrl'] = "";
	                    $links_arr = $this->oLinkFeed->GetHttpResult($links_url, $request);
	                    if($prgm_arr['code'] == 200){
		                    $links_detail = $links_arr["content"];
		                    $cache[$strMerID]["links_detail"] = "1";
		                    $g_id = intval($this->oLinkFeed->ParseStringBy2Tag($links_detail, array('/pan/aInfoCenterLinkInfo.action', 'geId='), '&'));
		                    if ($g_id > 0) {
		                        $AffDefaultUrl = "http://clkuk.tradedoubler.com/click?p({$strMerID})a({$site_id})g({$g_id})";
		                        $arr_prgm[$strMerID]['AffDefaultUrl'] = addslashes($AffDefaultUrl);
		                    }
	                    }
                    }

                }else{
                    $arr_prgm[$strMerID] = array(
                        "Name" => addslashes($strMerName),
                        "AffId" => $this->info["AffId"],
                        //"Contacts" => $Contacts,
                        "IdInAff" => $strMerID,
                        //"JoinDate" => date("Y-m-d H:i:s", strtotime($row["joinDate"])),
                        "StatusInAffRemark" => $strStatus,
                        "StatusInAff" => $StatusInAff,                        //'Active','TempOffline','Offline'
                        "Partnership" => $Partnership,                        //'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
                        "EPCDefault" => preg_replace("/[^0-9.]/", "", $EPCDefault),
                        "EPC90d" => preg_replace("/[^0-9.]/", "", $EPC90d),
                        //"TermAndCondition" => addslashes($TermAndCondition),
                        "LastUpdateTime" => date("Y-m-d H:i:s"),
                        "MobileFriendly" => addslashes($MobileFriendly),
                    );
                }
                //print_r($arr_prgm);exit;
				$program_num++;
				if(count($arr_prgm) >= 100){
				    $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				    //$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
			}
			$nPageNo++;
			if(count($arr_prgm)){
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
				$arr_prgm = array();
			}
		}
		$cache = json_encode($cache);
        $this->oLinkFeed->fileCachePut($cache_file, $cache);
		echo "\tGet Program by page end\r\n";
		if($program_num < 1){
			mydie("die: program count < 1, please check program.\n");
		}
		echo "\tUpdate ({$program_num}) program.\r\n";
		echo "\tSet program country int.\r\n";
		$objProgram->setCountryInt($this->info["AffId"]);
	}

	function GetCommissionBySiteId($site_id)
    {
        $objProgram = new ProgramDb();
        $arr_prgm = array();

        $arr_merchant = $this->oLinkFeed->getAllAffMerchant($this->info["AffId"]);
        $arr_merchant_id = array_keys($arr_merchant);
        $request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "",);
        $strUrl = "https://reports.tradedoubler.com/pan/aReport3Key.action?metric1.summaryType=NONE&metric1.lastOperator=/&metric1.columnName2=programId&metric1.operator1=/&metric1.columnName1=programId&metric1.midOperator=/&customKeyMetricCount=0&columns=programTariffPercentage&columns=programTariffCurrency&columns=programTariffAmount&columns=programId&columns=programName&sortBy=orderDefault&includeWarningColumn=true&affiliateId=$site_id&latestDayToExecute=0&setColumns=true&reportTitleTextKey=REPORT3_SERVICE_REPORTS_AAFFILIATEMYPROGRAMSREPORT_TITLE&interval=MONTHS&reportName=aAffiliateMyProgramsReport&key=731a61f9409131c6a22f415c179853ea";
        $result = $this->oLinkFeed->GetHttpResult($strUrl,$request);
        if (empty($result['content'])){
            mydie("Can't get data from api.");
        }
        $result = preg_replace('@>\s+<@', '><', $result['content']);
        $listStr = $this->oLinkFeed->ParseStringBy2Tag($result,array('Sub total:Brandreward', '<tbody','>'),'</tbody>');
        $listArr = explode('href="/pan/aProgramInfoApplyRead.action?programId', $listStr);

        $programArr = array();
        foreach ($listArr as $pStr){
            $programId = intval($this->oLinkFeed->ParseStringBy2Tag($pStr,'=', '&'));
            if (!$programId){
                continue;
            }
            $programArr[$programId] = explode('</tr><tr', $pStr);
            $count = count($programArr[$programId]);
            unset($programArr[$programId][$count-1]);
        }
        foreach ($programArr as $programId => $val){
            if (!in_array($programId, $arr_merchant_id)){
                continue;
            }

            $commission = '';
            foreach ($val as $key => $cV){
                preg_match('@<td class=".+">(\d+\.\d+)</td><td>([A-Z]{3})</td><td class=".+">((?:\d+\.\d+%)|(?:&nbsp;))</td>@', $cV, $m);
                if (!isset($m[2]) || empty($m[2]) || (($m[1] == 0.00 && ($m[3] == '0.00%' || $m[3] == '&nbsp;')))){
                    continue;
                }
                if ($m[3] != '100.00%'){
                    $commission .= $m[3] . ',';
                }elseif ($m[1] != 0.00){
                    $commission .= $m[2] . ' ' . $m[1] . ',';
                }
            }

            $arr_prgm[$programId] = array(
                "AffId" => $this->info["AffId"],
                "IdInAff" => $programId,
                "CommissionExt" => rtrim($commission, ','),
                "LastUpdateTime" => date("Y-m-d H:i:s")
            );
        }
        $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
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

