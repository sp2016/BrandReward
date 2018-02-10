 <?php
require_once 'text_parse_helper.php';
class LinkFeed_7_SAS
{
	
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		
		if(SID == 'bdg02'){
			define('AFFID_INAFF_7', '1273061');
			define('API_TOKEN_7', 'fseaGaeoRUGCc3Tb');
			define('API_SECRET_KEY_7', 'RIv8sm9p7RSnuz7aBGd6xk0q7EZulz8z');
		}else{
			define('AFFID_INAFF_7', '1418817');
			define('API_TOKEN_7', '37TT1VQcJVPaH6mn');
			define('API_SECRET_KEY_7', 'AWd6qm7b9VLrao2cUHy3ez8q7CItls0q');
		}
		define('API_VERSION_7', 1.8);		
	}

	function getCouponFeed()
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", );

		$check_date = date('Y-m-d H:i:s');
		
		//coupons
		$actionVerb = "couponDeals";
		$t = gmdate(DATE_RFC1123);
		$sigHash = hash("sha256", sprintf('%s:%s:%s:%s', API_TOKEN_7, $t, $actionVerb, API_SECRET_KEY_7));
		$request['addheader'] = array("x-ShareASale-Date: $t", "x-ShareASale-Authentication: $sigHash");
		$url = sprintf("https://shareasale.com/x.cfm?action=%s&affiliateId=%s&token=%s&version=%s", $actionVerb, AFFID_INAFF_7, API_TOKEN_7, API_VERSION_7);
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		
		$content = trim($r['content']);
		$data = @csv_string_to_array($content, '|', "\r\n");
		if (empty($data) || !is_array($data))
			return $arr_return;
		$links = array();
		foreach ($data as $v)
		{
			$link = array(
					"AffId" => $this->info["AffId"],
					"AffMerchantId" => $v['Merchant Id'],
					"AffLinkId" => $v['Deal Id'],
					"LinkName" => html_entity_decode($v['Title']),
					"LinkDesc" => html_entity_decode(sprintf('%s', $v['Description'])),
					"LinkStartDate" => '0000-00-00 00:00:00',
					"LinkEndDate" => '0000-00-00 00:00:00',
					"LinkPromoType" => 'COUPON',
					"LinkHtmlCode" => '',
					"LinkCode" => $v['Coupon Code'],
					"LinkOriginalUrl" => '',
					"LinkImageUrl" => $v['BigImage'],
					"LinkAffUrl" => $v['Tracking URL'],
					"DataSource" => 39,
			        "IsDeepLink" => 'UNKNOWN',
			        "Type"       => 'promotion'
				);
			if (!empty($v['Start Date']))
			{
				$date = strtotime($v['Start Date']);
				if ($date > 946713600)
					$link['LinkStartDate'] = date('Y-m-d H:i:s', $date);
			}
			if (!empty($v['End Date']))
			{
				$date = strtotime($v['End Date']);
				if ($date > 946713600)
					$link['LinkEndDate'] = date('Y-m-d 23:59:59', $date);
			}
			if (!empty($v['Restrictions']))
				$link['LinkDesc'] .= ', |Restrictions:' . html_entity_decode($v['Restrictions']);
			if (empty($link['LinkCode']))
			{
				$code = get_linkcode_by_text($link['LinkName'] .'|'. $link['LinkDesc']);
				if (!empty($code))
					$link['LinkCode'] = $code;
			}
			$link['LinkHtmlCode'] = create_link_htmlcode_image($link);
			if (empty($link['AffLinkId']) || empty($link['LinkAffUrl']) )
				continue;
            elseif(empty($link['LinkName'])){
                $link['LinkPromoType'] = 'link';
            }
			$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
			$arr_return["AffectedCount"] ++;
			$links[] = $link;
			if (count($links) > 100)
			{
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
				$links = array();
			}
		}		
		if (count($links))
		{			
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
			$links = array();
		}
		echo sprintf("get coupons complete. %s links(s) found. \n", $arr_return["AffectedCount"]);
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'promotion');
		
		return $arr_return;
	}
	
	function GetAllProductsByAffId(){
	    
	    
	    exit("Can't crawler the page");
	    
	    $check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
		$request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "");
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		
		$pageNum = 1;
		do{
		    
		    $productListHtml = $this->oLinkFeed->GetHttpResult('https://account.shareasale.com/a-datafeeds.cfm?merchantid=&keyword=&belong=1&category=&datestart=&order=featureRank&sortorder=DESC&pageNumber=$pageNum',$request);
		    $productListHtml = $productListHtml['content'];
		    
		    if(stripos($productListHtml,'<tr class="dfRow"') === false){
		        break;
		    }
		    
		    //idinaff productNum
		    $strLineStart = '<tr class="dfRow"';
		    $nLineStart = 0;
		    while ($nLineStart >= 0){

		        $nLineStart = stripos($productListHtml, $strLineStart, $nLineStart);
		        if ($nLineStart === false) break;
		     
		        $merchantId = intval($this->oLinkFeed->ParseStringBy2Tag($productListHtml, array('<span class="mer-id-body">'), '</span>', $nLineStart));
		        $merProductNum = intval(str_replace(',','',$this->oLinkFeed->ParseStringBy2Tag($productListHtml, array('<div class="productCount">PRODUCTS:'), '</div>', $nLineStart)));
		        
		         
		        if($merProductNum > 2000) continue;
		        echo $merchantId .'---'.$merProductNum;
		        //download product file
		        $fileName =  'product_feed_'.$merchantId.'.zip';
		        $downloadUrl = 'https://jokey.shareasale.com/a-downloadproducts-bulk.cfm?merchantID='.$merchantId;
		        $product_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],$fileName, "product", true);
		        //if(!$this->oLinkFeed->fileCacheIsCached($product_file)){
		            $r = $this->oLinkFeed->GetHttpResult($downloadUrl,$request);
		            $this->oLinkFeed->fileCachePut($product_file,$r['content']);
		            //echo $product_file;exit;
		            //解压
		            $zip = new ZipArchive() ;
		            if ($zip->open($product_file) !== TRUE) {
		                die ("Could not open archive");
		            }
		            $zip->extractTo('E:\wamp\www\newmega\program\crawl\data\LinkFeed_7_SAS\product');
		            //关闭zip文档
		            $zip->close();
		        //}
		        //read download content
		        $file = fopen("E:/wamp/www/newmega/program/crawl/data/LinkFeed_7_SAS/product/".$merchantId.".txt","r");
		        while(! feof($file))
		        {
		           $productData[] = fgetcsv($file,'','|');
		        }
		        fclose($file);
		        
		        
		        foreach ($productData as $pValue){
		        
		            $AffProductId = $pValue[0];
		            $ProductImage = $pValue[6];
		            $ProductUrl =  str_replace("YOURUSERID","1273061",$pValue[4]);
		            
		            $product_path_file = $this->oLinkFeed->getProductImgFilePath($this->info["AffId"],"{$merchantId}_".urlencode($AffProductId).".png", PRODUCTDIR);
		            if(!$this->oLinkFeed->fileCacheIsCached($product_path_file))
		            {
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
		                "ProductCurrency" => '$',
		                "ProductPrice" => $pValue[7],
						"ProductOriginalPrice" =>'',
						"ProductRetailPrice" =>$pValue[8],
		                "ProductImage" => addslashes($ProductImage),
		                "ProductLocalImage" => addslashes($product_path_file),
		                "ProductUrl" => $ProductUrl,
		                "ProductDestUrl" => '',
		                "ProductDesc" => html_entity_decode(addslashes($pValue[11])),
		                "ProductStartDate" => '',
		                "ProductEndDate" => '',
		            );
		            
		            //print_r($link);exit;
		            $links[] = $link;
		            
		            if (count($links) >= 100)
		            {
		                $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateProductToDB($links);
		                $links = array();
		                break;
		            }
		        }
		    }
		    $pageNum ++;
		}while(true);
		
		$this->oLinkFeed->checkProductExists($this->info["AffId"], $check_date);
		return $arr_return;
	}
	
    function GetAllLinksByAffId()
	{
		$check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", );
		// Get the text and banner links by modifiedDate from the getCouponFeed function.
		// Get the text and banner links by merchantId from the this function.
		// Note: API report requests are limited to 1000 per month.
		//text links and banners by modifiedDate
		
		$actionVerb = "merchantCreative";
		$t = gmdate(DATE_RFC1123);
		$sigHash = hash("sha256", sprintf('%s:%s:%s:%s', API_TOKEN_7, $t, $actionVerb, API_SECRET_KEY_7));
		$request['addheader'] = array("x-ShareASale-Date: $t", "x-ShareASale-Authentication: $sigHash");
		$modifiedDate = urlencode(date('m/d/Y', time() - 2592000));
		$url = sprintf("https://shareasale.com/x.cfm?action=%s&affiliateId=%s&token=%s&modifiedDate=%s&version=%s", $actionVerb, AFFID_INAFF_7, API_TOKEN_7, $modifiedDate, API_VERSION_7);
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = trim($r['content']);
		$data = @csv_string_to_array($content, '|', "\r\n");
		if (empty($data) || !is_array($data))
		    return $arr_return;
		$links = array();
		foreach ($data as $v)
		{
		    $link = array(
		        "AffId" => $this->info["AffId"],
		        "AffMerchantId" => $v['Merchant ID'],
		        "AffLinkId" => $v['Banner Id'],
		        "LinkName" => html_entity_decode($v['Name']),
		        "LinkDesc" => html_entity_decode(sprintf('%s', $v['Text'])),
		        "LinkStartDate" => '0000-00-00 00:00:00',
		        "LinkEndDate" => '0000-00-00 00:00:00',
		        "LinkPromoType" => 'DEAL',
		        "LinkHtmlCode" => '',
		        "LinkCode" => '',
		        "LinkOriginalUrl" => '',
		        "LinkImageUrl" => $v['Image Url'],
		        "LinkAffUrl" => $v['Click Url'],
		        "DataSource" => 39,
		        "IsDeepLink" => 'UNKNOWN',
		        "Type"       => 'link'
		    );
		    if (!empty($v['Modified Date']))
		    {
		        $date = strtotime($v['Modified Date']);
		        if ($date > 946713600)
		            $link['LinkStartDate'] = date('Y-m-d H:i:s', $date);
		    }
		    if (!empty($v['Alt Text']) && ($v['Alt Text'] != $v['Text']))
		    {
		        if (empty($link['LinkDesc']))
		            $link['LinkDesc'] .= html_entity_decode($v['Alt Text']);
		        else
		            $link['LinkDesc'] .= '. ' . html_entity_decode($v['Alt Text']);
		    }
		    $code = get_linkcode_by_text($link['LinkName'] .'|'. $link['LinkDesc']);
		    if (!empty($code))
		    {
		        $link['LinkCode'] = $code;
		        $link['LinkPromoType'] = 'COUPON';
		    }
		    else
		        $link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName'] . $link['LinkDesc']);
		    $link['LinkHtmlCode'] = create_link_htmlcode_image($link);
		    if (empty($link['AffLinkId']) || empty($link['LinkAffUrl']))
		        continue;
		    elseif( empty($link['LinkName'])){
		        $link['LinkPromoType'] = 'link';
		    }
		    $this->oLinkFeed->fixEnocding($this->info, $link, "feed");
		    $arr_return["AffectedCount"] ++;
		    $links[] = $link;
		    if (count($links) > 100)
		    {
		        $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		        $links = array();
		    }
		}
		
		if(count($links) > 0)
		    $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
			
		echo sprintf("get text & banner links complete, %s links(s) found. \n", $arr_return["AffectedCount"]);
		
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link');
		return $arr_return;
	}
	
	function GetAllLinksFromAffByMerID($merinfo)
	{
	    $check_date = date('Y-m-d H:i:s');
	    $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", );
		// Get the text and banner links by modifiedDate from the getCouponFeed function.
		// Get the text and banner links by merchantId from the this function.
		// Note: API report requests are limited to 1000 per month.
		$actionVerb = "merchantCreative";
		$t = gmdate(DATE_RFC1123);
		$sigHash = hash("sha256", sprintf('%s:%s:%s:%s', API_TOKEN_7, $t, $actionVerb, API_SECRET_KEY_7));
		$request['addheader'] = array("x-ShareASale-Date: $t", "x-ShareASale-Authentication: $sigHash");
		$url = sprintf("https://shareasale.com/x.cfm?action=%s&affiliateId=%s&token=%s&merchantId=%s&version=%s", $actionVerb, AFFID_INAFF_7, API_TOKEN_7, $merinfo['IdInAff'], API_VERSION_7);
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = trim($r['content']);
		$data = @csv_string_to_array($content, '|', "\r\n");
		if (empty($data) || !is_array($data))
			return $arr_return;
		$links = array();
		foreach ($data as $v)
		{
			$link = array(
					"AffId" => $this->info["AffId"],
					"AffMerchantId" => $merinfo['IdInAff'],
					"AffLinkId" => $v['Banner Id'],
					"LinkName" => html_entity_decode($v['Name']),
					"LinkDesc" => html_entity_decode(sprintf('%s', $v['Text'])),
					"LinkStartDate" => '0000-00-00 00:00:00',
					"LinkEndDate" => '0000-00-00 00:00:00',
					"LinkPromoType" => 'DEAL',
					"LinkHtmlCode" => '',
					"LinkCode" => '',
					"LinkOriginalUrl" => '',
					"LinkImageUrl" => $v['Image Url'],
					"LinkAffUrl" => $v['Click Url'],
					"DataSource" => 39,
			        "IsDeepLink" => 'UNKNOWN',
			        "Type"       => 'link'
				);
			if (!empty($v['Modified Date']))
			{
				$date = strtotime($v['Modified Date']);
				if ($date > 946713600)
					$link['LinkStartDate'] = date('Y-m-d H:i:s', $date);
			}
			if (!empty($v['Alt Text']) && ($v['Alt Text'] != $v['Text']))
			{
				if (empty($link['LinkDesc']))
					$link['LinkDesc'] .= html_entity_decode($v['Alt Text']);
				else
					$link['LinkDesc'] .= '. ' . html_entity_decode($v['Alt Text']);
			}
			$code = get_linkcode_by_text($link['LinkName'] .'|'. $link['LinkDesc']);
			if (!empty($code))
			{
				$link['LinkCode'] = $code;
				$link['LinkPromoType'] = 'COUPON';
			}
			else {
                $link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName'] . $link['LinkDesc']);
            }
            $link['LinkHtmlCode'] = create_link_htmlcode_image($link);
			if (empty($link['AffLinkId']) || empty($link['LinkAffUrl']))
				continue;
            elseif( empty($link['LinkName'])){
                $link['LinkPromoType'] = 'link';
            }
			$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
			$arr_return["AffectedCount"] ++;
			$links[] = $link;
			if (($arr_return["AffectedCount"] % 100) == 0)
			{
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
				$links = array();
			}
		}
		echo sprintf("program: %s, %s links(s) found. \n", $merinfo['IdInAff'], $arr_return["AffectedCount"]);
		if(count($links) > 0)
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		// the AffectedCount is not a regular value so the AffectedCount is set to 0.
		// $arr_return['AffectedCount'] = 0;
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link', $merinfo['IdInAff']);
		return $arr_return;
	}
	
	function getInvalidLinks()
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", );
		$actionVerb = "invalidLinks";
		$t = gmdate(DATE_RFC1123);
		$sigHash = hash("sha256", sprintf('%s:%s:%s:%s', API_TOKEN_7, $t, $actionVerb, API_SECRET_KEY_7));
		$request['addheader'] = array("x-ShareASale-Date: $t", "x-ShareASale-Authentication: $sigHash");
		$url = sprintf("https://shareasale.com/x.cfm?action=%s&affiliateId=%s&token=%s&version=%s", $actionVerb, AFFID_INAFF_7, API_TOKEN_7, API_VERSION_7);
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = trim($r['content']);
		$data = @csv_string_to_array($content, '|', "\r\n");
		if (empty($data) || !is_array($data) || count($data) < 1)
			return;
		$links = array();
		foreach ($data as $v)
		{
			if (empty($v['Banner ID']))
				continue;
			$link = array(
					'affiliate' => $this->info["AffId"],
					'LinkID' => trim($v['Banner ID']),
					'ReferralUrl' => trim($v['Referrer']),
					'ProgramID' => trim($v['Merchant ID']),
					'Reason' => trim($v['Reason']),
					'OccuredDate' => parse_time_str($v['Date']),
			);
			$links[] = $link;
		}
		return $links;
	}

	function GetProgramByPage()
	{
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "post",
			"postdata" => "", 
		);
		
		//step 1,login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);

		//step 2,get all exists merchant
		//$arrAllExistsMerchants = $this->oLinkFeed->GetAllExistsAffMerIDForCheckByAffID($this->info["AffId"]);

		//Step 3, Get all merchants
		list($nNumPerPage, $bHasNextPage, $nPageNo, $start, $max_start, $Cnt, $UpdateCnt) = array(50, true, 1, 1, 2, 0, 0);

		$objProgram = new ProgramDb();
		$arr_prgm = array();

		while($bHasNextPage && $max_start >= $start)
		{
			$start = ($nPageNo - 1) * $nNumPerPage + 1;
			$temp = time() . rand(1000,9999);

			$strUrl = "https://www.shareasale.com/a-ajaxsearch.cfm?searchType=basicKeyword&keyword=&start=" . $start .  "&order=&resultFilter=&cookielength=Any&epc=&avgsale=&reversalrate=&ascordesc=DESC&lstExclude=&temp=" . $temp;

			$request["method"] = "get";
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$result = $r["content"];

			print "<br>\n Get Merchant List : Page: $nPageNo <br>\n";
			//parse HTML
			$strLineStart = '<td class="col1"';
			$nLineStart = 0;
			$bStart = true;
			while ($nLineStart >= 0)
			{
				if($this->debug) print "Process $Cnt  ";

				$nLineStart = stripos($result, $strLineStart, $nLineStart);
				if ($nLineStart === false)
				{
					echo "strLineStart: $strLineStart not found, break\n";
					if ($bStart == true){
						$bHasNextPage = false;
					}
					break;
				}
				$bStart = false;
				// ID 	Name 	EPC 	Status
				$strEPC = 0;

				//ID <a href="/a-viewmerchant.cfm?merchantID=29560" target="_blank">GoPhoto</a>
				$strMerID = $this->oLinkFeed->ParseStringBy2Tag($result,'"/a-viewmerchant.cfm?merchantID=','"',$nLineStart);
				if($strMerID === false)
				{
					echo "strMerID not found, break\n";
					break;
				}
				$strMerID = trim($strMerID);
				if(!$strMerID) continue;

				//name
				$strMerName = $this->oLinkFeed->ParseStringBy2Tag($result,">","<",$nLineStart);
				if($strMerName === false)
				{
					echo "strMerName not found, continue\n";
					continue;
				}
				$strMerName = html_entity_decode(trim($strMerName));
				if($this->debug) echo "strMerName: $strMerName\n";
				if(!$strMerName) continue;
				
				//Homepage
				$Homepage = $this->oLinkFeed->ParseStringBy2Tag($result, array('<div style="margin-bottom:3px;text-align:left;">', '<a href="'), '"', $nLineStart);
				//CategoryExt
				$CategoryExt = "";
				$homepage_link = str_replace(array("http://", "https://"),"",$Homepage);
				$category = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, "{$homepage_link}</a></div>", 'ID:', $nLineStart)));
				if($category){
					$CategoryExt = $category;
				}
				//JoinDate
				$JoinDate = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<b>Active on:</b>', '</div>', $nLineStart));
				if($JoinDate){
					$JoinDate = date("Y-m-d H:i:s", strtotime($JoinDate));
				}
				
				$strStatus = $this->oLinkFeed->ParseStringBy2Tag($result, array('margin-top:6px;margin-left:20px;','[ '),' ]', $nLineStart);
				$strStatus = trim(strip_tags($strStatus));
				if($this->debug) echo "strStatus: $strStatus\n";//'Active','TempOffline','Expired'

				if($strStatus == 'ENROLLED IN PROGRAM'){
					$StatusInAff = 'Active';
					$Partnership = "Active";
				}
				elseif($strStatus == 'Pending'){
					$StatusInAff = 'Active';
					$Partnership = "Pending";
				}
				elseif($strStatus == 'Declined'){
					$StatusInAff = 'Active';
					$Partnership = "Declined";
				}
				elseif($strStatus == 'JOIN PROGRAM'){
					$StatusInAff = 'Active';
					$Partnership = "NoPartnership";
				}
				else{
					mydie("strStatus is wrong: $strStatus\n");
				}
				
				//commission
				$CommissionExt = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, '<td class="col2" valign="top" align="center" width="90">', '</td>', $nLineStart)));
				//ReturnDays
				$ReturnDays = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<div class="quickTip" helpkey="TG">', '</div>', $nLineStart));
				//EPCDefault 7d
				$EPCDefault = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('7 Day</td>', '<td>'), '</td>', $nLineStart));
				//EPC30d
				$EPC30d = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('30 Day</td>', '<td>'), '</td>', $nLineStart));
				
				//program
				//program_detail
				$prgm_url = "https://www.shareasale.com/a-viewmerchant.cfm?merchantID=$strMerID";
				$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
				$prgm_detail = $prgm_arr["content"];
				
				$country = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('/flags/', 'alt="'), '"'));
				$desc = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<strong>Merchant provided description:</strong>', '<div class="sbar mertxt">'), '</div>'));
				$TermAndCondition = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<strong>Merchant provided <i>Terms of Agreement</i>:</strong>', '<div class="sbar mertxt">'), '</div>'));
				$program_status = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, 'Program Status:', '<br>')));
				if(strpos($program_status,"Closed") !== false){
					$StatusInAff = "Offline";
				}
				if(empty($CategoryExt)){
					$CategoryExt = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<table cellspacing="0" class="sbar mpStats">', $strMerName, 'target="_blank"', '<br/>'), '<br/>'));
				}
				
				//$StatusInAff = trim(strip_tags(($this->oLinkFeed->ParseStringBy2Tag($result, 'Program Status:', '<br>', $nLineStart))));
				/*if(strpos($prgm_detail, "a-joinprogram.cfm")){
					$Partnership = "Active";
				}*/

				$SEMPolicyExt = "";
				$sem_url = "https://www.shareasale.com/a-viewPPCkeywords.cfm?merchantID=$strMerID";
				$sem_arr = $this->oLinkFeed->GetHttpResult($sem_url, $request);
				$sem_detail = $sem_arr["content"];
				$sem_Start = 0;

				$sem_tmp = trim($this->oLinkFeed->ParseStringBy2Tag($sem_detail, '<table width=75% align=center class=sbar cellpadding=8>', '</table>', $sem_Start));
				$sem_arr_tmp = explode("<tr>", $sem_tmp);
				if(count($sem_arr_tmp) > 2){
					$SEMPolicyExt = "<table>".$sem_tmp."</table>";
					$SEMPolicyExt = str_replace(array("<br>","<br/>"),"",$SEMPolicyExt);
				}

				$sem_tmp = trim($this->oLinkFeed->ParseStringBy2Tag($sem_detail, '<table width=75% align=center class=sbar cellpadding=8>', '</table>', $sem_Start));
				if($sem_tmp){
					$SEMPolicyExt .= "<table>".$sem_tmp."</table>";
				}

				$BonusExt = "";
				$bonus_url = "https://www.shareasale.com/a-viewbonuscampaign.cfm?merchantID=$strMerID";
				$bonus_arr = $this->oLinkFeed->GetHttpResult($bonus_url, $request);
				$bonus_detail = $bonus_arr["content"];
				$bonus_tmp = trim($this->oLinkFeed->ParseStringBy2Tag($bonus_detail, '<table align=center class=sbar width="95%" border="0" cellspacing="0" cellpadding="5">', '</table>'));
				$bonus_arr_tmp = explode("<tr>", $bonus_tmp);
				if(count($bonus_arr_tmp) > 3){
					$BonusExt = "<table>".$bonus_tmp."</table>";
					$BonusExt = str_replace(array("<br>","<br/>"),"",$BonusExt);
				}

				$arr_prgm[$strMerID] = array(
					"Name" => addslashes(html_entity_decode(trim($strMerName))),
					"AffId" => $this->info["AffId"],
					"CategoryExt" => addslashes($CategoryExt),
					"IdInAff" => addslashes($strMerID),
					"JoinDate" => $JoinDate,
					"StatusInAff" => $StatusInAff,						//'Active','TempOffline','Offline'
					"StatusInAffRemark" => addslashes($strStatus),
					"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','Removed'
					"Description" => addslashes($desc),
					"Homepage" => addslashes($Homepage),
					"CommissionExt" => addslashes($CommissionExt),
					"EPCDefault" => addslashes(preg_replace("/[^0-9.]/", "", $EPCDefault)),
					"EPC30d" => addslashes(preg_replace("/[^0-9.]/", "", $EPC30d)),
					"CookieTime" => addslashes($ReturnDays),
					"TermAndCondition" => addslashes($TermAndCondition),
					"TargetCountryExt" => addslashes($country),
					"SEMPolicyExt" => addslashes($SEMPolicyExt),
					"BonusExt" => addslashes($BonusExt),
					"LastUpdateTime" => date("Y-m-d H:i:s"),
					"DetailPage" => $prgm_url,
				);
				//print_r($arr_prgm);
				//exit;
				if(count($arr_prgm) >= 200){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
				$Cnt++;
			}

			if(count($arr_prgm)){
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
				unset($arr_prgm);
			}

			if($nPageNo == 1 && preg_match("/\\[([0-9]*[01]) - ([0-9]+)\\]/",$result,$matches))
			{
				//try to fix pagesize [1 - 50]
				$new_pagesize = $matches[2] - $matches[1] + 1;
				if($new_pagesize >= 50 && $new_pagesize <= 500)
				{
					$nNumPerPage = $new_pagesize;
				}
				//try to find max page
				preg_match_all("/\\[([0-9]*[01]) - ([0-9]+)\\]/",$result,$matches,PREG_SET_ORDER);
				foreach($matches as $set => $set_matches)
				{
					if($set_matches[1] > $max_start) $max_start = $set_matches[1];
				}
			}
			$nPageNo++;
		}//per page	
		
		$objProgram->setProgramOffline($this->info["AffId"]);
		$objProgram->setCountryInt($this->info["AffId"]);
	}

    function GetProgramFromTxt($file){
        $objProgram = new ProgramDb();

        $handle = fopen($file,'r');
        $arr_prgm = array();
        while($r = fgets($handle)){
            $row = explode('|',$r);
            if(!count($row) || !isset($row[0])){
                continue;
            }
            $strMerID = intval($row[0]);
            if(!$strMerID) continue;
            $name = isset($row[1]) ? trim($row[1]) : "";
            //print_r($row);exit;
            //if(trim($row[1]) != "") continue;
            $StatusInAffRemark = "";
            $StatusInAff = "Offline";
            if(isset($row[5])){
                $StatusInAffRemark = $row[5];
                if($row[5] == "Closed"){
                    $StatusInAff = "Offline";
                }elseif($row[5] == "LowFunds"){
                    $StatusInAff = "Active";
                }elseif($row[5] == "Online"){
                    $StatusInAff = "Active";
                }elseif($row[5] == "TemporarilyOffline"){
                    $StatusInAff = "TempOffline";
                }else{
                    $StatusInAff = "Offline";
                }
            }

            $Partnership = "NoPartnership";
            if(isset($row[28])){
                if($row[28] == "Yes"){
                    $Partnership = "Active";
                }elseif($row[28] == "Pending"){
                    $Partnership = "Pending";
                }elseif($row[28] == "Declined"){
                    $Partnership = "Declined";
                }else{
                    $Partnership = "NoPartnership";
                }
            }

            $Homepage = isset($row[4]) ? trim($row[4]) : "";
            //$CommissionExt = isset($row[5]) ? trim($row[5]) : trim($row[6]);

            $CommissionExt = "Sale Comm:".trim($row[6])."|";
            $CommissionExt .= "Lead Comm:".trim($row[7])."|";
            $CommissionExt .= "Hit Comm:".trim($row[8]);
            
            isset($row[10]) ? $CategoryExt = addslashes($row[10]) : $CategoryExt = "";
            $CategoryExt = str_replace('acc', 'Accessories', $CategoryExt);
            $CategoryExt = str_replace('art', 'Art/Music/Photography', $CategoryExt);
            $CategoryExt = str_replace('auction', 'Auction Services', $CategoryExt);
            $CategoryExt = str_replace('bus', 'Business', $CategoryExt);
            $CategoryExt = str_replace('car', 'Automotive', $CategoryExt);
            $CategoryExt = str_replace('clo', 'Clothing', $CategoryExt);
            $CategoryExt = str_replace('com', 'Commerce/Classifieds', $CategoryExt);
            $CategoryExt = str_replace('cpu', 'Computers/Electronics', $CategoryExt);
            $CategoryExt = str_replace('dating', 'Online Dating Services', $CategoryExt);
            $CategoryExt = str_replace('domain', 'Domain Names', $CategoryExt);
            $CategoryExt = str_replace('edu', 'Education', $CategoryExt);
            $CategoryExt = str_replace('fam', 'Family', $CategoryExt);
            $CategoryExt = str_replace('fin', 'Financial', $CategoryExt);
            $CategoryExt = str_replace('free', 'Freebies, Free Stuff, Rewards Programs', $CategoryExt);
            $CategoryExt = str_replace('fud', 'Food/Drink', $CategoryExt);
            $CategoryExt = str_replace('gif', 'Gifts', $CategoryExt);
            $CategoryExt = str_replace('gourmet', 'Gourmet', $CategoryExt);
            $CategoryExt = str_replace('green', 'Green', $CategoryExt);
            $CategoryExt = str_replace('hea', 'Health', $CategoryExt);
            $CategoryExt = str_replace('hom', 'Home & Garden', $CategoryExt);
            $CategoryExt = str_replace('hosting', 'Web Hosting', $CategoryExt);
            $CategoryExt = str_replace('ins', 'Insurance', $CategoryExt);
            $CategoryExt = str_replace('job', 'Career/Jobs/Employment', $CategoryExt);
            $CategoryExt = str_replace('legal', 'Legal', $CategoryExt);
            $CategoryExt = str_replace('lotto', 'Gaming and Lotto', $CategoryExt);
            $CategoryExt = str_replace('mal', 'Shopping Malls', $CategoryExt);
            $CategoryExt = str_replace('mar', 'Marketing', $CategoryExt);
            $CategoryExt = str_replace('med', 'Books/Media', $CategoryExt);
            $CategoryExt = str_replace('military', 'Military', $CategoryExt);
            $CategoryExt = str_replace('mov', 'Moving/Moving Supplies', $CategoryExt);
            $CategoryExt = str_replace('rec', 'Recreation', $CategoryExt);
            $CategoryExt = str_replace('res', 'Real Estate', $CategoryExt);
            $CategoryExt = str_replace('search', 'Search Engine Submission', $CategoryExt);
            $CategoryExt = str_replace('spf', 'Sports/Fitness', $CategoryExt);
            $CategoryExt = str_replace('toy', 'Games/Toys', $CategoryExt);
            $CategoryExt = str_replace('tvl', 'Travel', $CategoryExt);
            $CategoryExt = str_replace('web', 'General Web Services', $CategoryExt);
            $CategoryExt = str_replace('webmaster', 'Webmaster Tools', $CategoryExt);
            $CategoryExt = str_replace('weddings', 'Weddings', $CategoryExt);

            if(trim($name)){
                $arr_prgm_name[$strMerID] = array(
                    "AffId" => $this->info["AffId"],
                    "Name" => addslashes($name),
                    "Homepage" => addslashes($Homepage),
                    "CategoryExt" => $CategoryExt,
                    "CommissionExt" => addslashes($CommissionExt),
                    "IdInAff" => addslashes($strMerID),
                    "StatusInAff" => $StatusInAff,						//'Active','TempOffline','Offline'
                    "StatusInAffRemark" => addslashes($StatusInAffRemark),
                    "Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','Removed'
                    "LastUpdateTime" => date("Y-m-d H:i:s"),
                );
            }else{
                $arr_prgm[$strMerID] = array(
                    "AffId" => $this->info["AffId"],
                    "Homepage" => addslashes($Homepage),
                    "CategoryExt" => $CategoryExt,
                    "CommissionExt" => addslashes($CommissionExt),
                    "IdInAff" => addslashes($strMerID),
                    "StatusInAff" => $StatusInAff,						//'Active','TempOffline','Offline'
                    "StatusInAffRemark" => isset($row[5]) ? addslashes($row[5]) : "",
                    "Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','Removed'
                    "LastUpdateTime" => date("Y-m-d H:i:s"),
                );
            }
//            print_r($arr_prgm);
            if(count($arr_prgm) >= 100){
                $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
                echo "saving...\n";
                $arr_prgm = array();
            }
        }
        if(count($arr_prgm) >= 0){
            $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
            echo "saving...\n";
            $arr_prgm = array();
        }
    }

	function GetProgramFromAff()
	{
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
        $file = INCLUDE_ROOT."data/LinkFeed_7_SAS/".date("Ymd")."_7.txt";

        if(file_exists($file)){
            $this->GetProgramFromTxt($file);
            unlink($file);
        }else {
            $this->GetProgramByApi();
        }
        $this->checkProgramOffline($this->info["AffId"], $check_date);

		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}
	
	function GetProgramByApi(){
		echo "\tGet Program by api start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		$arr_prgm_name = array();
		
		$myTimeStamp = gmdate(DATE_RFC1123);

		$APIVersion = 1.2;
		$actionVerb = "merchantStatus";
		$sig = API_TOKEN_7.':'.$myTimeStamp.':'.$actionVerb.':'.API_SECRET_KEY_7;
		$sigHash = hash("sha256",$sig);
		$myHeaders = array("x-ShareASale-Date: $myTimeStamp","x-ShareASale-Authentication: $sigHash");

		$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"programs_".date("YmdH").".dat", "program");
		if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
		{
			$ch = curl_init();
			$url = "https://shareasale.com/x.cfm?action=$actionVerb&affiliateId=".AFFID_INAFF_7."&token=".API_TOKEN_7."&version=$APIVersion";
			//Merchant Id, Merchant, WWW, Program Status, Program Category, Sale Comm, Lead Comm, Hit Comm, Approved, Link Url
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER,$myHeaders);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$returnResult = curl_exec($ch);
			
			if (stripos($returnResult,"Error Code ")) {
				// error occurred
				trigger_error($returnResult,E_USER_ERROR);
			}else{
				$arr_feed = explode("\n",$returnResult);
				$arr_feed = json_encode($arr_feed);
				$this->oLinkFeed->fileCachePut($cache_file, $arr_feed);
			}
			curl_close($ch);
		}
		
		$cache_file = file_get_contents($cache_file);
		

		if ($cache_file) {
			echo "\tProcessing Data.\r\n";
			//parse HTTP Body to determine result of request
			if (stripos($cache_file,"Error Code ")) {
				// error occurred
				trigger_error($cache_file,E_USER_ERROR);
			}
			else{
				// success
				//echo $returnResult;
				$arr_feed = array();
				$arr_feed = json_decode($cache_file);		
//				print_r($arr_feed);exit;
				//echo count($arr_feed);
				$line_number = 0;
				$cnt = 0;
				$line_one = "Merchant Id|Merchant|WWW|Program Status|Program Category|Sale Comm|Lead Comm|Hit Comm|Approved|Link Url";
				$arrToUpdate = array();
				foreach($arr_feed as $line){		
					$line_number++;
					if($line_number == 1){
						if(trim($line) != $line_one){
							echo "$line","\n";
							mydie("die: wrong API format: at line $line\n");
						}
						continue;
					}

					$row = array();
					$row = explode("|",$line);

					if(!count($row) || !isset($row[0])){
						//print_r($row);
						continue;
					}
					
					$strMerID = intval($row[0]);
					if(!$strMerID) continue;
					
					
					$name = isset($row[1]) ? trim($row[1]) : "";
					//print_r($row);exit;
					//if(trim($row[1]) != "") continue;
					$StatusInAffRemark = "";
					$StatusInAff = "Offline";
					if(isset($row[3])){
						$StatusInAffRemark = $row[3];
						if($row[3] == "Closed"){
							$StatusInAff = "Offline";
						}elseif($row[3] == "LowFunds"){
							$StatusInAff = "Active";
						}elseif($row[3] == "Online"){
							$StatusInAff = "Active";
						}elseif($row[3] == "TemporarilyOffline"){
							$StatusInAff = "TempOffline";
						}else{
							$StatusInAff = "Offline";
						}
					}

					$Partnership = "NoPartnership";
					if(isset($row[8])){
						if($row[8] == "Yes"){
							$Partnership = "Active";
						}elseif($row[8] == "Pending"){
							$Partnership = "Pending";
						}elseif($row[8] == "Declined"){
							$Partnership = "Declined";
						}else{
							$Partnership = "NoPartnership";
						}
					}

					$AffDefaultUrl = isset($row[9]) ? trim($row[9]) : "";					
					$Homepage = isset($row[2]) ? trim($row[2]) : "";
					//$CommissionExt = isset($row[5]) ? trim($row[5]) : trim($row[6]);
					
					$CommissionExt = "Sale Comm:".trim($row[5])."|";
					$CommissionExt .= "Lead Comm:".trim($row[6])."|";
					$CommissionExt .= "Hit Comm:".trim($row[7]);
					
					isset($row[4]) ? $CategoryExt = addslashes($row[4]) : $CategoryExt = "";
					$CategoryExt = str_replace('acc', 'Accessories', $CategoryExt);
					$CategoryExt = str_replace('art', 'Art/Music/Photography', $CategoryExt);
					$CategoryExt = str_replace('auction', 'Auction Services', $CategoryExt);
					$CategoryExt = str_replace('bus', 'Business', $CategoryExt);
					$CategoryExt = str_replace('car', 'Automotive', $CategoryExt);
					$CategoryExt = str_replace('clo', 'Clothing', $CategoryExt);
					$CategoryExt = str_replace('com', 'Commerce/Classifieds', $CategoryExt);
					$CategoryExt = str_replace('cpu', 'Computers/Electronics', $CategoryExt);
					$CategoryExt = str_replace('dating', 'Online Dating Services', $CategoryExt);
					$CategoryExt = str_replace('domain', 'Domain Names', $CategoryExt);
					$CategoryExt = str_replace('edu', 'Education', $CategoryExt);
					$CategoryExt = str_replace('fam', 'Family', $CategoryExt);
					$CategoryExt = str_replace('fin', 'Financial', $CategoryExt);
					$CategoryExt = str_replace('free', 'Freebies, Free Stuff, Rewards Programs', $CategoryExt);
					$CategoryExt = str_replace('fud', 'Food/Drink', $CategoryExt);
					$CategoryExt = str_replace('gif', 'Gifts', $CategoryExt);
					$CategoryExt = str_replace('gourmet', 'Gourmet', $CategoryExt);
					$CategoryExt = str_replace('green', 'Green', $CategoryExt);
					$CategoryExt = str_replace('hea', 'Health', $CategoryExt);
					$CategoryExt = str_replace('hom', 'Home & Garden', $CategoryExt);
					$CategoryExt = str_replace('hosting', 'Web Hosting', $CategoryExt);
					$CategoryExt = str_replace('ins', 'Insurance', $CategoryExt);
					$CategoryExt = str_replace('job', 'Career/Jobs/Employment', $CategoryExt);
					$CategoryExt = str_replace('legal', 'Legal', $CategoryExt);
					$CategoryExt = str_replace('lotto', 'Gaming and Lotto', $CategoryExt);
					$CategoryExt = str_replace('mal', 'Shopping Malls', $CategoryExt);
					$CategoryExt = str_replace('mar', 'Marketing', $CategoryExt);
					$CategoryExt = str_replace('med', 'Books/Media', $CategoryExt);
					$CategoryExt = str_replace('military', 'Military', $CategoryExt);
					$CategoryExt = str_replace('mov', 'Moving/Moving Supplies', $CategoryExt);
					$CategoryExt = str_replace('rec', 'Recreation', $CategoryExt);
					$CategoryExt = str_replace('res', 'Real Estate', $CategoryExt);
					$CategoryExt = str_replace('search', 'Search Engine Submission', $CategoryExt);
					$CategoryExt = str_replace('spf', 'Sports/Fitness', $CategoryExt);
					$CategoryExt = str_replace('toy', 'Games/Toys', $CategoryExt);
					$CategoryExt = str_replace('tvl', 'Travel', $CategoryExt);
					$CategoryExt = str_replace('web', 'General Web Services', $CategoryExt);
					$CategoryExt = str_replace('webmaster', 'Webmaster Tools', $CategoryExt);
					$CategoryExt = str_replace('weddings', 'Weddings', $CategoryExt);
					
					
					if(trim($name)){
						$arr_prgm_name[$strMerID] = array(
							"AffId" => $this->info["AffId"],
							"Name" => addslashes($name),
							"Homepage" => addslashes($Homepage),
							"CategoryExt" => $CategoryExt,
							"CommissionExt" => addslashes($CommissionExt),
							"IdInAff" => addslashes($strMerID),
							"StatusInAff" => $StatusInAff,						//'Active','TempOffline','Offline'
							"StatusInAffRemark" => addslashes($StatusInAffRemark),
							"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','Removed'
							"LastUpdateTime" => date("Y-m-d H:i:s"),
							"AffDefaultUrl" => addslashes($AffDefaultUrl)
						);
					}else{
						$arr_prgm[$strMerID] = array(
							"AffId" => $this->info["AffId"],
							"Homepage" => addslashes($Homepage),
							"CategoryExt" => $CategoryExt,
							"CommissionExt" => addslashes($CommissionExt),
							"IdInAff" => addslashes($strMerID),
							"StatusInAff" => $StatusInAff,						//'Active','TempOffline','Offline'
							"StatusInAffRemark" => isset($row[3]) ? addslashes($row[3]) : "",
							"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','Removed'
							"LastUpdateTime" => date("Y-m-d H:i:s"),
							"AffDefaultUrl" => addslashes($AffDefaultUrl)
						);
					}
					$program_num++;
					if(count($arr_prgm) >= 100){
						$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
						$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
						echo "saving...\n";
						$arr_prgm = array();
					}
					if(count($arr_prgm_name) >= 100){
						$objProgram->updateProgram($this->info["AffId"], $arr_prgm_name);
						$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm_name);
						echo "saving...\n";
						$arr_prgm_name = array();
					}	 			
				}
				if(count($arr_prgm)){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					unset($arr_prgm);
				}
				if(count($arr_prgm_name)){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm_name);
					$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm_name);
					unset($arr_prgm_name);
				}
			}
		}
		else{
			// connection error
			trigger_error(curl_error($ch),E_USER_ERROR);
			mydie("die: get info by Api failed.\n");
		}
		
		echo "\tGet Program by api end\r\n";

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

        $timestamp = gmdate(DATE_RFC1123);
        $sig = API_TOKEN_7 . ':' . $timestamp . ':activity:' . API_SECRET_KEY_7;
        $sig = hash("sha256", $sig);
        $request = array(
            "AffId" => $this->info["AffId"],
            "method" => 'get',
            "addheader" => array("x-ShareASale-Date: {$timestamp}", "x-ShareASale-Authentication: {$sig}")
        );

        $date_arr = $this->get_date_range_arr($start_date, $end_date, '30 day', 'm/d/Y');
        krsort($date_arr);
        foreach ($date_arr as $k => $v) {
            $start_dt = $v['start_dt'];
            $end_dt = $v['end_dt'];

            $cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"], "data_" . date("YmdH") . "_Transaction_$k.csv", 'Transaction', true);
            if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
                $fw = fopen($cache_file, 'w');
                if (!$fw) {
                    throw new Exception("File open failed {$cache_file}");
                }
                $url = "https://shareasale.com/x.cfm?action=activity&affiliateId=" . AFFID_INAFF_7 . "&token=" . API_TOKEN_7 . "&dateStart={$start_dt}&dateEnd={$end_dt}&version=1.7";
                echo "req => {$url} \n";
                $request['file'] = $fw;
                $result = $this->oLinkFeed->GetHttpResult($url, $request);
                if ($result['code'] != 200) {
                    mydie("Download {$k} csv file failed.");
                }
                fclose($fw);
            }

            $fp = fopen($cache_file, 'r');
            if (!$fp) {
                throw new Exception("File open failed {$cache_file}");
            }

            $k = 0;
            while (!feof($fp)) {
                $lr = fgetcsv($fp, 0, '|', '"');

                if (++$k == 1)
                    continue;
                if ($lr[0] == "")
                    continue;
                /*
                  0===>Trans ID
                  1===>User ID
                  2===>Merchant ID
                  3===>Trans Date
                  4===>Trans Amount
                  5===>Commission
                  6===>Comment
                  7===>Voided
                  8===>Pending Date
                  9===>Locked
                  10===>Aff Comment
                  11===>Banner Page
                  12===>Reversal Date
                  13===>Click Date
                  14===>Click Time
                  15===>Banner Id
                  16===>SKU List
                  17===>Quantity List
                  18===>Lock Date
                  19===>Paid Date
                  20===>Merchant Organization
                  21===>Merchant Website
                 */
                if (count($lr) < 22) {
                    continue;
                }

                if ($lr[7]) {
                    $sales = $commission = 0;
                } else {
                    $sales = trim($lr[4]);
                    $commission = trim($lr[5]);
                }

                $day = date('Y-m-d H:i:s', strtotime($lr[3]));

                $tradestatus = 'Pending';
                $lr[7] = trim($lr[7]);
                $lr[9] = trim($lr[9]);
                if (!empty($lr[9])) {
                    $tradestatus = 'Locked';
                }
                if (!empty($lr[7])) {
                    $tradestatus = 'Voided';
                }

                $cancelreason = '';
                if ($tradestatus == 'Voided') {
                    $cancelreason = trim($lr[6]);
                }

                $replace_array = array(
                    '{createtime}' => date('Y-m-d H:i:s', strtotime(trim($lr[3]))),
                    '{updatetime}' => date('Y-m-d H:i:s', strtotime(trim($lr[3]))),
                    '{sales}' => $sales,
                    '{commission}' => $commission,
                    '{idinaff}' => trim($lr[2]),
                    '{programname}' => trim($lr[20]),
                    '{sid}' => trim($lr[10]),
                    '{orderid}' => '',
                    '{clicktime}' => date('Y-m-d H:i:s', strtotime(date('Y-m-d', strtotime(trim($lr[13]))) . ' ' . trim($lr[14]))),
                    '{tradeid}' => trim($lr[0]),
                    '{tradestatus}' => $tradestatus,
                    '{oldcur}' => 'USD',
                    '{oldsales}' => $sales,
                    '{oldcommission}' => $commission,
                    '{tradetype}' => '',
                    '{referrer}' => $lr[11],
                    '{cancelreason}' => $cancelreason,
                );

                //this kind of transaction is a total pay
                if ($replace_array['{sales}'] == 0 && $replace_array['{commission}'] < 0 && $replace_array['{programname}'] == 'shareasale.com' && $replace_array['{idinaff}'] == 47) //$mid == 47
                    continue;

                //should have merchant id
                if ($replace_array['{idinaff}'] == 0 || $replace_array['{idinaff}'] == '' || $lr[3] == '')
                    continue;
                $type = '';
                $orderid = '';
                if (stripos($lr[6], ' - '))
                    list($type, $orderid) = explode(' - ', trim($lr[6]));

                $replace_array['{orderid}'] = trim($orderid);
                $replace_array['{tradetype}'] = trim($type);

                $rev_file = AFF_TRANSACTION_DATA_PATH . '/revenue_' . date('Ymd', strtotime($day)) . '.upd';
                if (!isset($fws[$rev_file])) {
                    $fws[$rev_file] = fopen($rev_file, 'w');
                    $comms[$rev_file] = 0;
                }

                fwrite($fws[$rev_file], strtr(FILE_FORMAT, $replace_array) . "\n");
            }
            fclose($fp);
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

    function get_date_range_arr($startDate,$endDate,$range,$format='Y-m-d'){
        $startDate = date('Y-m-d',strtotime($startDate));
        $endDate = date('Y-m-d',strtotime($endDate));
        $d = new DateTime($startDate);

        $return_d = array();

        while($d->format('Y-m-d') <= $endDate){

            $start_dt = $d->format($format);
            $d->modify('+'.$range);
            if($d->format('Y-m-d') > $endDate){
                $end_dt = date($format,strtotime($endDate));
            }else{
                $end_dt = $d->format($format);
            }
            $d->modify('+1 day');

            $return_d[] = array('start_dt'=>$start_dt,'end_dt'=>$end_dt);
        }
        return $return_d;
    }

}

