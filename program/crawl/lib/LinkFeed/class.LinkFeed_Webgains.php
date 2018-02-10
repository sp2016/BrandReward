<?php
require_once 'text_parse_helper.php';
class LinkFeed_Webgains //for 14,13,18,34,208
{		
	function GetMerchantListFromAff()
	{
		$arr_return = array("AffectedCount" => 0,"UpdatedCount" => 0,);
		return $arr_return;
		//step 1,login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info, 6);
		$this->SwitchWebgainsToSelectWebSite();

		//step 2,get all exists merchant
		$arrAllExistsMerchants = $this->oLinkFeed->GetAllExistsAffMerIDForCheckByAffID($this->info["AffId"]);

		$request = array("AffId" => $this->info["AffId"],"method" => "post","postdata" => "",);

		print "<br>\n Get Merchant List   <br>\n";
		$request["postdata"] = "action=list&updatenumperscreen=1&sortby=liveDate&sortdir=desc&numperpage=0";
		$strUrl = "http://us.webgains.com/affiliates/program.html";
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];
		
		//parse HTML
		$strLineStart = '<td style="text-align:left;"><a href="/affiliates/program.html?action=view&programID=';

		$nLineStart = 0;
		while ($nLineStart >= 0){
			//print "Process $Cnt  ";
			$nLineStart = stripos($result, $strLineStart, $nLineStart);
			if ($nLineStart === false) {
				break;
			}
			
			// ID 	Name 	EPC 	Status
			//ID
			$strMerID = $this->oLinkFeed->ParseStringBy2Tag($result, 'programID=', '"', $nLineStart);
			if($strMerID === false) continue;
			$strMerID = trim($strMerID);

			//name
			$strMerName = $this->oLinkFeed->ParseStringBy2Tag($result, array('<span style', '">'), '</span>', $nLineStart);
			if($strMerName === false) mydie("die: parse merchant name failed.");
			$strMerName = trim($strMerName);
			$strMerName = html_entity_decode($strMerName);

			$arr_pattern = array();
			for($i=0;$i<5;$i++) $arr_pattern[] = "<td";
			$arr_pattern[] = '>';

			//EPC
			$strEPC = $this->oLinkFeed->ParseStringBy2Tag($result,$arr_pattern,'</td>',$nLineStart);
			$strEPC = trim($strEPC);
			if (strpos($strEPC, 'n/a') !== false) $strEPC = 0;
			else $strEPC = html_entity_decode($strEPC);

			//Status
			$strStatus_block = $this->oLinkFeed->ParseStringBy2Tag($result,'<td style="width:60px;" nowrap>', '</td>', $nLineStart);
			$strStatus = trim(strip_tags($strStatus_block));

			if (stripos($strStatus, 'LEAVE') !== false) $strStatus = 'approval';
			elseif (stripos($strStatus, 'JOIN') !== false) $strStatus = 'not apply';
			elseif (stripos($strStatus, 'MORE') !== false) $strStatus = 'not apply';
			elseif (stripos($strStatus, 'Suspended') !== false) $strStatus = 'siteclosed';
			elseif (stripos($strStatus, 'REAPPLY') !== false) $strStatus = 'declined';
			elseif (stripos($strStatus, 'Application under consideration') !== false) $strStatus = 'pending';
			elseif (stripos($strStatus, 'Membership under review') !== false) $strStatus = 'pending';
			else mydie("die: unknown Status: $strStatus \n");
			
			$arr_return["AffectedCount"] ++;
			$arr_update = array(
				"AffMerchantId" => $strMerID,
				"AffId" => $this->info["AffId"],
				"MerchantName" => html_entity_decode($strMerName),
				"MerchantEPC30d" => "-1",
				"MerchantEPC" => $strEPC,
				"MerchantStatus" => $strStatus,
				"MerchantRemark" => "",
			);
			$this->oLinkFeed->fixEnocding($this->info,$arr_update,"merchant");
			if($this->oLinkFeed->UpdateMerchantToDB($arr_update,$arrAllExistsMerchants)) $arr_return["UpdatedCount"] ++;
		}
		
		$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateAllExistsAffMerIDButCannotFetched($this->info["AffId"], $arrAllExistsMerchants);
		return $arr_return;
	}

	function getCouponFeed()
	{
		
	    $check_date = date('Y-m-d H:i:s');
	    $arr_return = array("AffectedCount" => 0,"UpdatedCount" => 0,"Detail" => array(),);
		$request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "",);
		
		//make sure title is english
		if(SID != 'bdg02' ){
			$this->oLinkFeed->GetHttpResult("http://us.webgains.com/lc.html?locale=en_GB&dest=http%3A//us.webgains.com/publisher/".$this->getSiteId()."/ad/vouchercodes%23",$request);
		}

		echo "get coupons\r\n";
//		$title = 'program,start date,expiry date,voucher code,description,discount,destination url,commission';
		$title = '"Voucher ID","Program ID","Program name",Network,"Start date","End date","Tracking link",Code,Discount,Commission,URL,Description';
		//step 2, download banner links
		$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"feed.dat","cache_feed");
		if(!$this->oLinkFeed->fileCacheIsCached($cache_file)){
			//step 1,login
			$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info, 6);
			
			if(SID == 'bdg02' ){				
				$strUrl = sprintf('http://www.webgains.com/publisher/%s/ad/vouchercodes/downloadcsv?', $this->getSiteId());
			}else{
				$this->SwitchWebgainsToSelectWebSite();
				// $strUrl = 'http://us.webgains.com/affiliates/vouchers.html?raw=downloadCSV';
				// use a new download url 
				// the old one not well formatted.
				$strUrl = sprintf('http://us.webgains.com/publisher/%s/ad/vouchercodes/downloadcsv?', $this->getSiteId());
			}
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$result = $r["content"];

			if(stripos($result,$title) === false){
				print_r($r);
				mydie("die: get feed failed, title not found \n");
			}
			$this->oLinkFeed->fileCachePut($cache_file,$result);
		}
		if(!file_exists($cache_file)) return $arr_return;

		$all_merchant_name = $this->oLinkFeed->getAllAffMerchant($this->info["AffId"],"","MerchantName");
		//Open CSV File
		$arr_title = explode(",", $title);
		foreach($arr_title as $i => $v){
			$arr_title[$i] = trim($v,'"');
		}
		$col_count = sizeof($arr_title);
		$fhandle = fopen($cache_file, 'r');
		$arrToUpdate = array();
		while($line = fgetcsv($fhandle, 50000, ',', '"', '\\')){
			//program,start date,expiry date,voucher code,description,discount,destination url,commission

			if($line[0] == '' || $line[1] == 'Program ID'){
				continue;
			}
			if(sizeof($line) != $col_count){
				echo "warning: invalid line found: " . implode(",",$line) . "\n";
				continue;
			}else{

			}
			foreach($arr_title as $i => $title){
				$row[$title] = $line[$i];
			}
			$aff_mer_name  = trim($row["Program name"]);
			if($aff_mer_name == ''){
				continue;
			}
			$start_date  =  preg_replace('/.*([\d]{2})\/([\d]+)\/([\d]+).*/','\3-\2-\1', trim($row["Start date"]));
			$end_date  =  preg_replace('/.*([\d]{2})\/([\d]+)\/([\d]+).*/','\3-\2-\1', trim($row["End date"]));
			$couponcode = trim($row["Code"]);
			$link_desc  = trim($row["Description"]);
			$link_name = $strDiscount = trim($row["Discount"]);
			$html_code = trim($row["Tracking link"]);
			
			if ($couponcode == '')
				$couponcode = get_linkcode_by_text($link_name." " .$link_desc);
			//http://track.webgains.com/click.html?wgcampaignid=48215&wgprogramid=867,7%,
			if(preg_match("/wgcampaignid=([^&]*)&wgprogramid=([^&]*)/i",$html_code,$matches)){
				$link_id = "c_" . $matches[1] . "_" . $matches[2];
				if($couponcode){
					$link_id .= "_" . $couponcode;
				}
			}else{
				mydie("die: unknown destination url format: $html_code \n");
			}
			if(!isset($all_merchant_name[$aff_mer_name])){
				echo "warning: program $aff_mer_name not found.\n";
				continue;
			}
			$aff_mer_id = $all_merchant_name[$aff_mer_name]["AffMerchantId"];
			$aff_mer_id = $row['Program ID'];
			if ($strDiscount != '') $link_desc  .= '. Discount Detail: ' . $strDiscount;
			if ($couponcode != '') $link_desc  .= '. Voucher Code: ' . $couponcode;
			$promo_type = 'coupon';
			$arr_one_link = array(
				"AffId" => $this->info["AffId"],
				"AffMerchantId" => $aff_mer_id,
				"AffLinkId" => md5($link_id),
				"LinkName" => $link_name,
				"LinkCode" => $couponcode,
				"LinkDesc" => $link_desc,
				"LinkStartDate" => $start_date,
				"LinkEndDate" => $end_date.' 23:59:59',
				"LinkPromoType" => $promo_type,
				"LinkHtmlCode" => $html_code,
				"LinkOriginalUrl" => trim($row["URL"]),
				"LinkImageUrl" => "",
				"LinkAffUrl" => $html_code,
				"DataSource" => $this->DataSource["feed"],
			    "IsDeepLink" => 'UNKNOWN',
			    "Type"       => 'promotion'
			);
			$this->oLinkFeed->fixEnocding($this->info,$arr_one_link,"feed");
			if (empty($arr_one_link['LinkName'])){
				continue;
			}
			$arrToUpdate[] = $arr_one_link;
			$arr_return["AffectedCount"] ++;
			if(!isset($arr_return["Detail"][$aff_mer_id]["AffectedCount"])) $arr_return["Detail"][$aff_mer_id]["AffectedCount"] = 0;
			$arr_return["Detail"][$aff_mer_id]["AffectedCount"] ++;
			if(sizeof($arrToUpdate) > 100){
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($arrToUpdate);
				$arrToUpdate = array();
			}
		}
		fclose($fhandle);
		if(sizeof($arrToUpdate) > 0){
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($arrToUpdate);
			$arrToUpdate = array();
		}
		
		echo "get offers\r\n";
		$title = '"Offer ID","Program name","Program ID",Network,"Destination URL","Tracking link",Type,"Start date","End date",Title,Description';
		//step 2, download banner links
		$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"offers.dat","cache_feed");
		if(!$this->oLinkFeed->fileCacheIsCached($cache_file)){			
			if(SID == 'bdg02' ){				
				$strUrl = sprintf('http://www.webgains.com/publisher/%s/ad/offers/downloadcsv?', $this->getSiteId());
			}else{
				$this->SwitchWebgainsToSelectWebSite();
				// $strUrl = 'http://us.webgains.com/affiliates/vouchers.html?raw=downloadCSV';
				// use a new download url 
				// the old one not well formatted.
				$strUrl = sprintf('http://us.webgains.com/publisher/%s/ad/offers/downloadcsv?', $this->getSiteId());
			}
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$result = $r["content"];

			if(stripos($result,$title) === false){
				print_r($r);
				mydie("die: get feed failed, title not found \n");
			}
			$this->oLinkFeed->fileCachePut($cache_file,$result);
		}
		if(!file_exists($cache_file)) return $arr_return;

		$all_merchant_name = $this->oLinkFeed->getAllAffMerchant($this->info["AffId"],"","MerchantName");
		//Open CSV File
		$arr_title = explode(",", $title);
		foreach($arr_title as $i => $v){
			$arr_title[$i] = trim($v,'"');
		}
		$col_count = sizeof($arr_title);
		$fhandle = fopen($cache_file, 'r');
		$arrToUpdate = array();
		while($line = fgetcsv($fhandle, 50000, ',', '"', '\\')){
			//program,start date,expiry date,voucher code,description,discount,destination url,commission

			if($line[0] == '' || $line[1] == 'Program name'){
				continue;
			}
			if(sizeof($line) != $col_count){
				echo "warning: invalid line found: " . implode(",",$line) . "\n";
				continue;
			}else{

			}
			foreach($arr_title as $i => $title){
				$row[$title] = $line[$i];
			}
			$aff_mer_name  = trim($row["Program name"]);
			if($aff_mer_name == ''){
				continue;
			}
			$start_date  =  preg_replace('/.*([\d]{2})\/([\d]+)\/([\d]+).*/','\3-\2-\1', trim($row["Start date"]));
			$end_date  =  preg_replace('/.*([\d]{2})\/([\d]+)\/([\d]+).*/','\3-\2-\1', trim($row["End date"]));
			$couponcode = '';
			$link_desc  = trim($row["Description"]);
			$link_name = $strDiscount = trim($row["Title"]);
			$html_code = trim($row["Tracking link"]);
			
			if ($couponcode == '')
				$couponcode = get_linkcode_by_text($link_name." " .$link_desc);
			//http://track.webgains.com/click.html?wgcampaignid=48215&wgprogramid=867,7%,
			if(preg_match("/wgcampaignid=([^&]*)&wgprogramid=([^&]*)/i",$html_code,$matches)){
				http://track.webgains.com/click.html?wgcampaignid=192821&wgprogramid=10777&wgtarget=http://www.ukk.fashion/collections/french-connection?sort_by=price-ascending
				
				$link_id = "c_" . $matches[1] . "_" . $matches[2];
				if($couponcode){
					$link_id .= "_" . $couponcode;
				}
			}else{
				mydie("die: unknown destination url format: $html_code \n");
			}
			if(!isset($all_merchant_name[$aff_mer_name])){
				echo "warning: program $aff_mer_name not found.\n";
				continue;
			}
			$aff_mer_id = $all_merchant_name[$aff_mer_name]["AffMerchantId"];
			$aff_mer_id = $row['Program ID'];
			if ($strDiscount != '') $link_desc  .= '. Discount Detail: ' . $strDiscount;
			if ($couponcode != '') $link_desc  .= '. Voucher Code: ' . $couponcode;
			
			$promo_type = 'deal';
			if($couponcode != ''){
				$promo_type = 'coupon';
			}
			
			$arr_one_link = array(
				"AffId" => $this->info["AffId"],
				"AffMerchantId" => $aff_mer_id,
				"AffLinkId" => md5($link_id),
				"LinkName" => $link_name,
				"LinkCode" => $couponcode,
				"LinkDesc" => $link_desc,
				"LinkStartDate" => $start_date,
				"LinkEndDate" => $end_date.' 23:59:59',
				"LinkPromoType" => $promo_type,
				"LinkHtmlCode" => $html_code,
				"LinkOriginalUrl" => trim($row["Destination URL"]),
				"LinkImageUrl" => "",
				"LinkAffUrl" => $html_code,
				"DataSource" => $this->DataSource["feed"],
			    "IsDeepLink" => 'UNKNOWN',
			    "Type"       => 'promotion'
			);
			//print_r($arr_one_link);exit;
			$this->oLinkFeed->fixEnocding($this->info,$arr_one_link,"feed");
			if (empty($arr_one_link['LinkName'])){
				continue;
			}
			$arrToUpdate[] = $arr_one_link;
			$arr_return["AffectedCount"] ++;
			if(!isset($arr_return["Detail"][$aff_mer_id]["AffectedCount"])) $arr_return["Detail"][$aff_mer_id]["AffectedCount"] = 0;
			$arr_return["Detail"][$aff_mer_id]["AffectedCount"] ++;
			if(sizeof($arrToUpdate) > 100){
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($arrToUpdate);
				$arrToUpdate = array();
			}
		}
		fclose($fhandle);
		if(sizeof($arrToUpdate) > 0){
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($arrToUpdate);
			$arrToUpdate = array();
		}
		
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'promotion');
		return $arr_return;
	}

    function GetAllProductsByAffId()
    {
        $check_date = date('Y-m-d H:i:s');
        $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
        $request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "");
        $productNumConfig = $this->oLinkFeed->product_program_num_config($this->info["AffId"]);
        $productNumConfigAlert = '';
        $isAssignMerchant = FALSE;

        //make sure title is english
        if(SID != 'bdg02' ){
            $this->oLinkFeed->GetHttpResult("http://us.webgains.com/lc.html?locale=en_GB&dest=http%3A//us.webgains.com/publisher/".$this->getSiteId()."/ad/vouchercodes%23",$request);
        }

        //step 1,login
        $this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info, 6);

        if(SID == 'bdg02' ){
            $strUrl = sprintf('http://www.webgains.com/publisher/%s/ad/datafeed/select-feed', $this->getSiteId());
        }else{
            $this->SwitchWebgainsToSelectWebSite();
            $strUrl = sprintf('http://www.webgains.com/publisher/%s/ad/datafeed/select-feed', $this->getSiteId());
        }
        $r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
        $result = $r["content"];
        $listStr = $this->oLinkFeed->ParseStringBy2Tag($result,"'aaData':", "]],");
        $marArr = explode('],[', $listStr);
        if (!count($marArr)) {
            mydie("get program list failed!");
        }

        foreach ($marArr as $val) {
            $links = array();
            $feedId = $this->oLinkFeed->ParseStringBy2Tag($val,'value=\"', '\"');
            preg_match("@',(\d+),'@", $val, $fn);
            $feedNum = $fn[1];
            preg_match("@'-(\d+)-'@", $val, $pId);
            $programId = $pId[1];
            $setMaxNum  = isset($productNumConfig[$programId]) ? $productNumConfig[$programId]['limit'] :  100;
            $crawlMerchantsActiveNum = 0;
            $isAssignMerchant = isset($productNumConfig[$programId]) ? TRUE : FALSE;
            $TotalCount = $feedNum;
            if ($feedNum > 30000 || !$feedId){
                continue;
            }
            echo $programId .'---'.$feedNum . "\r\n";
            //download product file
            $fileName = 'data_' . date('Ymd') . '_product_feed_'.$programId . '.csv';

            $downloadUrl = sprintf('http://www.webgains.com/affiliates/datafeed.html?action=download&campaign=%s&feeds=%s&categories=&fields=extended&fieldIds=deeplink,description,image_url,last_updated,price,product_id,product_name,program_id,currency,voucher_price&format=csv&separator=|&zipformat=none&stripNewlines=0&apikey=31636cc48a7cb972cea3f93b749b6d6f', $this->getSiteId(), $feedId);
            $product_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],$fileName, "product", true);
            if(!$this->oLinkFeed->fileCacheIsCached($product_file)){
                $r = $this->oLinkFeed->GetHttpResult($downloadUrl,$request);
                $this->oLinkFeed->fileCachePut($product_file,$r['content']);
            }

            //read download content
            $file = fopen($product_file,"r");
            while(! feof($file)) {
                $productData = fgetcsv($file,'','|');
                $ProductId = intval($productData[5]);
                $prgmId = intval($productData[7]);
                $price = $productData[9];
                $OriginalPrice = $productData[4];

                if (!$ProductId){
                    continue;
                }
                if (!$prgmId || $prgmId != $programId){
                    continue;
                }
                if (!intval(ceil($price))){
                    continue;
                }

                $ProductImage = $productData[2];
                $product_path_file = $this->oLinkFeed->getProductImgFilePath($this->info["AffId"],"{$programId}_".urlencode($ProductId).".png", PRODUCTDIR);
                if(!$this->oLinkFeed->fileCacheIsCached($product_path_file)) {
                    $file_content = $this->oLinkFeed->downloadImg($ProductImage);
                    if(!$file_content) {
                        continue;
                    }
                    $this->oLinkFeed->fileCachePut($product_path_file, $file_content);
                }

                $link = array(
                    "AffId" => $this->info["AffId"],
                    "AffMerchantId" => $programId,
                    "AffProductId" => $ProductId,
                    "ProductName" => html_entity_decode(addslashes($productData[6])),
                    "ProductCurrency" => addslashes($productData[8]),
                    "ProductPrice" => floatval($price),
                    "ProductOriginalPrice" =>$OriginalPrice,
                    "ProductRetailPrice" => '',
                    "ProductImage" => addslashes($ProductImage),
                    "ProductLocalImage" => addslashes($product_path_file),
                    "ProductUrl" => addslashes($productData[0]),
                    "ProductDestUrl" => '',
                    "ProductDesc" => html_entity_decode(addslashes($productData[1])),
                    "ProductStartDate" => '',
                    "ProductEndDate" => '',
                );
                $crawlMerchantsActiveNum ++;
                //print_r($link);exit;
                $links[] = $link;

                if (count($links) >= 100) {
                    $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateProductToDB($links);
                    $links = array();
                }
                //大于最大数跳出
                if($crawlMerchantsActiveNum>=$setMaxNum){
                    fclose($file);
                    break;
                }
                
            }
            
            if (count($links) > 0) {
                $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateProductToDB($links);
                $links = array();
            }
            
            if($isAssignMerchant){
                $productNumConfigAlert .= "AFFID:".$this->info["AffId"].",Program({$programId}),Crawl Count($crawlMerchantsActiveNum),Total Count({$TotalCount}) \r\n";
            }
        }

        $this->oLinkFeed->checkProductExists($this->info["AffId"], $check_date);
        echo $productNumConfigAlert.PHP_EOL;
        return $arr_return;
    }
	
	
	function GetAllLinksByAffId()
	{
	    $check_date = date('Y-m-d H:i:s');
	    $newonly=true;
	    $arr_IdInAff = array();
	    $arr_merchant = $this->oLinkFeed->getApprovalAffMerchant($this->info["AffId"]);
	    $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, );
	    
	    $strUKSiteID = $this->getSiteId();
	    $request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "", );
	    
	    $this->info["AffLoginUrl"] = "https://us.webgains.com/loginform.html?action=login";
	    $this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info, 6);
	    $this->SwitchWebgainsToSelectWebSite();
	    
	    foreach ($arr_merchant as $merinfo)
	    {
	        
	        $aff_id = $this->info["AffId"];
	        $AffMerchantId = $merinfo["AffMerchantId"];
	        
	        $existing_link_ids = array();
	        if($newonly)
	        {
	            //to speed up for link share
	            $existing_link_ids = $this->oLinkFeed->getAllLinksByAffAndMerchant($this->info["AffId"],$AffMerchantId);
	        }
	        
	        //step 2, download links
	        $nNumPerPage = 15;
	        $nTotalLinkCount = 0;
	        $bHasNextPage = true;
	        $nPageNo = 1;
	        $arrToUpdate = array();
	        
	        while($bHasNextPage)
	        {
	            if ($nPageNo == 1){
	                $strUrl = "http://us.webgains.com/affiliates/link.html?action=submitsearch&newsearch&programid=$AffMerchantId";
	            }
	            else{
	                $strUrl = "http://us.webgains.com/affiliates/link.html?action=submitsearch&paginate=1&startnum=".(($nPageNo - 1) * $nNumPerPage + 1)."&programid=$AffMerchantId";
	            }
	            	
	            $r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
	            $result = $r["content"];
	        
	            if($this->debug) print "Get links Data : Page - $nPageNo <br>\n";
	        
	            if ($nPageNo == 1){
	                //When open the first page. Try get total links count.
	                $nLineStart = 0;
	                $nTotalLinkCount = intval(trim($this->oLinkFeed->ParseStringBy2Tag($result, array('records', 'of'), '<br />', $nLineStart)));
	                if($this->debug) print "nTotalLinkCount $nTotalLinkCount  <br>\n";
	            }
	        
	            $strLineStart = 'style="text-decoration:none">+</a>';
	            $nLineStart = 0;
	            while ($nLineStart >= 0)
	            {
	                $nLineStart = stripos($result, $strLineStart, $nLineStart);
	                if ($nLineStart === false) break;
	        
	                $aff_mer_name = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
	                if ($aff_mer_name === false) break;
	                $aff_mer_name = trim($aff_mer_name);
	        
	                //Link Type
	                $link_type= $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
	                if ($link_type === false) break;
	                $link_type = trim($link_type);
	        
	                //get Link ID
	                $link_id = $this->oLinkFeed->ParseStringBy2Tag($result, '/affiliates/link.html?action=view&linkID=', '">view</a>', $nLineStart);
	                if ($link_id === false) break;
	                $link_id = trim($link_id);
	        
	                if($newonly && isset($existing_link_ids[$link_id]))
	                {
	                    $arr_return["AffectedCount"] ++;
	                    continue;
	                }
	        
	                //Open Detail Page
	                $strDetailUrl = 'http://us.webgains.com/affiliates/link.html?action=view&linkID='.$link_id;
	                $r = $this->oLinkFeed->GetHttpResult($strDetailUrl,$request);
	                $Detailresult = $r["content"];
	                if($r["code"] != 200)
	                {
	                    print "warning: get link detail page failed<br>\n";
	                    continue;
	                }
	        
	                //parse detail page
	                $nDetailLineStart = 0;
	                $link_name = trim($this->oLinkFeed->ParseStringBy2Tag($Detailresult, array('<b>Name</b>', '<td>'), '</td>', $nDetailLineStart));
	                //if($link_name === false) break;
	                if($link_name === false || empty($link_name))
	                	continue;
		            
	                //$original_url = $this->oLinkFeed->ParseStringBy2Tag($Detailresult, '<b>Target</b>', '<td>', '</td>', $nDetailLineStart);
	                //$original_url = trim($original_url);
	                //if ($original_url === false) break;
	        
	                $link_src_lastupdate = $this->oLinkFeed->ParseStringBy2Tag($Detailresult, array('<b>Last Modified</b>', '<td>'), '</td>', $nDetailLineStart);
	                if($this->debug) print "link_src_lastupdate $link_src_lastupdate  <br>\n";
	                if ($link_src_lastupdate === false) break;
	                $link_src_lastupdate = date('Y-m-d H:i:s', strtotime(trim($link_src_lastupdate)));
	                if($this->debug) print "link_src_lastupdate $link_src_lastupdate  <br>\n";
	        
	                if (trim($link_type) == 'graphic')
	                    $html_code = '<a href="http://track.webgains.com/click.html?wglinkid='.$link_id.'&wgcampaignid='.$strUKSiteID.'"><img border=0 src="http://track.webgains.com/link.html?wglinkid='.$link_id.'&wgcampaignid='.$strUKSiteID.'"/></a>';
	                else
	                    $html_code = '<iframe height="500" frameborder="0" width="700" src="http://us.webgains.com/link_preview.html?js=1&wglinkid='.$link_id.'&wgcampaignid='.$strUKSiteID.'"></iframe>';
	                $start_date = date("Y-m-d");
	                $end_date = '0000-00-00';
	                $link_desc = $link_name;
	        
	                $promo_type = $this->oLinkFeed->getPromoTypeByLinkContent($link_desc);
	                if($this->debug) print "promo_type $promo_type  <br>\n";
	        
	                $arr_one_link = array(
	                    "AffId" => $merinfo["AffId"],
	                    "AffMerchantId" => $merinfo["AffMerchantId"],
	                    "AffLinkId" => $link_id,
	                    "LinkName" => html_entity_decode($link_name),
	                    "LinkDesc" => html_entity_decode($link_desc),
	                    "LinkStartDate" => $start_date,
	                    "LinkEndDate" => $end_date,
	                    "LinkCode" => "",
	                    "LinkPromoType" => $promo_type,
	                    "LinkHtmlCode" => $html_code,
	                    "LinkOriginalUrl" => "",
	                    "LinkImageUrl" => "",
	                    "LinkAffUrl" => 'http://track.webgains.com/click.html?wglinkid='.$link_id.'&wgcampaignid='.$strUKSiteID,
	                    "DataSource" => $this->DataSource["website"],
	                    "Type"       => 'link'
	                );
	                $code = get_linkcode_by_text($arr_one_link['LinkName'] . '|' . $arr_one_link['LinkDesc'] . '|' . $arr_one_link['LinkHtmlCode']);
	                if (!empty($code)) {
	                    $arr_one_link['LinkCode'] = $code;
	                    $arr_one_link['LinkPromoType'] = 'coupon';
	                }
	                $this->oLinkFeed->fixEnocding($this->info,$arr_one_link,"link");
	                $arrToUpdate[] = $arr_one_link;
	                $arr_return["AffectedCount"] ++;
	                //exit;
	            }//page
	        
	            if(sizeof($arrToUpdate) > 0)
	            {
	                $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($arrToUpdate);
	                $arrToUpdate = array();
	            }
	            
	            //Check if have next page;
	            ///
	            if($this->debug) print " ($nTotalLinkCount > ($nNumPerPage * $nPageNo))  <br>\n";
	            if ($nTotalLinkCount > ($nNumPerPage * $nPageNo)){
	                $bHasNextPage = true;
	                if($this->debug) print " Have NEXT PAGE  <br>\n";
	            }
	            else{
	                $bHasNextPage = false;
	                if($this->debug) print " NO NEXT PAGE  <br>\n";
	            }
	            
	            if ($bHasNextPage){
	                $nPageNo++;
	            }
	        }//get links per page
	        
	        //step 3, get voucher code
	        $strUrl = "http://us.webgains.com/affiliates/vouchers.html?action=list&pid=$AffMerchantId";
	        $r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
	        $result = $r["content"];
	        
	        if($this->debug) print "Get Voucher Code List  <br>\n";
	        
	        $nLineStart = 0;
	        $aff_mer_name = $this->oLinkFeed->ParseStringBy2Tag($result, 'Generic voucher codes for program: ', '</h1>', $nLineStart);
	        if($this->debug) print "aff_mer_name $aff_mer_name  <br>\n";
	        if ($aff_mer_name !== false)
	        {
	            $strLineStart = '<tr>';
	            while ($nLineStart >= 0){
	                $nLineStart = stripos($result, $strLineStart, $nLineStart);
	                if ($nLineStart === false) break;
	        
	                $voucher_code = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
	                if($this->debug) print "voucher_code $voucher_code  <br>\n";
	                if ($voucher_code === false) break;
	        
	                $link_name = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);

	                $link_desc = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
	                if($this->debug) print "link_desc $link_desc  <br>\n";
	                if ($link_desc === false) break;

	                if (!trim($link_name)) {
	                    $link_name = trim($link_desc);
                    }
                    if (!trim($link_name)) {
	                    $link_name = trim($aff_mer_name);
                    }
                    if($this->debug) print "link_name $link_name  <br>\n";
                    if ($link_name === false) break;
	        
	                $original_url = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
	                if($this->debug) print "original_url $original_url  <br>\n";
	                //if ($original_url === false) break;
	        
	                $start_date = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
	                if($this->debug) print "start_date $start_date  <br>\n";
	                if ($start_date === false) break;
	        
	                $end_date = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
	                if($this->debug) print "end_date $end_date  <br>\n";
	                if ($end_date === false) break;
	                $end_date = date("Y-m-d 23:59:59", strtotime($end_date));
	        
	                //get Link ID
	                $link_id = 'voucher_'.$voucher_code.'_'.$link_name;
	                $promo_type = 'coupon';
	                //$html_code = 'Voucher Code: '.$voucher_code.'. Please just set URL as blank. (using the default url of merchant.). Please note do not add the voucher code if it shows: "not to be used by affiliates" or "Offline code" ';
	                $html_code = sprintf('<a href="http://track.webgains.com/click.html?wgcampaignid=%s&wgprogramid=%s&wgtarget=%s">%s</a>', $strUKSiteID, $merinfo['AffMerchantId'], $original_url, $link_name);
	                $arr_one_link = array(
	                    "AffId" => $merinfo["AffId"],
	                    "AffMerchantId" => $merinfo["AffMerchantId"],
	                    "AffLinkId" => $link_id,
	                    "LinkName" => html_entity_decode($link_name),
	                    "LinkDesc" => html_entity_decode($link_desc),
	                    "LinkStartDate" => $start_date,
	                    "LinkEndDate" => $end_date,
	                    "LinkPromoType" => $promo_type,
	                    "LinkHtmlCode" => $html_code,
	                    "LinkCode" => $voucher_code,
	                    "LinkOriginalUrl" => "",
	                    "LinkImageUrl" => "",
	                    "LinkAffUrl" => sprintf('http://track.webgains.com/click.html?wgcampaignid=%s&wgprogramid=%s&wgtarget=%s', $strUKSiteID, $merinfo['AffMerchantId'], $original_url),
	                    "DataSource" => $this->DataSource["website"],
	                    "Type"       => 'link'
	                );
	                if(empty($arr_one_link['LinkCode'])){
	                    $code = get_linkcode_by_text($arr_one_link['LinkName'] . '|' . $arr_one_link['LinkDesc'] . '|' . $arr_one_link['LinkHtmlCode']);
	                    if (!empty($code)) {
	                        $arr_one_link['LinkCode'] = $code;
	                        $arr_one_link['LinkPromoType'] = 'coupon';
	                    }
	                }
	                $this->oLinkFeed->fixEnocding($this->info,$arr_one_link,"link");
	                $arrToUpdate[] = $arr_one_link;
	                $arr_return["AffectedCount"] ++;
	            }
	        }
	        if(sizeof($arrToUpdate) > 0)
	        {
	            $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($arrToUpdate);
	            $arrToUpdate = array();
	        }
	    }
	    $this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link');
	    return $arr_return;
	    
	}

	function GetAllLinksFromAffByMerID($merinfo,$newonly=true)
	{
	    print_r($merinfo);exit;
	    $check_date = date('Y-m-d H:i:s');
	    
		$aff_id = $this->info["AffId"];
		$AffMerchantId = $merinfo["AffMerchantId"];
		$strUKSiteID = $this->getSiteId();
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, );
		$request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "", );
		
		$this->info["AffLoginUrl"] = "https://us.webgains.com/loginform.html?action=login";
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info, 6);
		$this->SwitchWebgainsToSelectWebSite();

		$existing_link_ids = array();
		if($newonly)
		{
			//to speed up for link share
			$existing_link_ids = $this->oLinkFeed->getAllLinksByAffAndMerchant($this->info["AffId"],$AffMerchantId);
		}
		
		//step 2, download links
		$nNumPerPage = 15;
		$nTotalLinkCount = 0;
		$bHasNextPage = true;
		$nPageNo = 1;
		$arrToUpdate = array();
		
		while($bHasNextPage)
		{
			if ($nPageNo == 1){
				$strUrl = "http://us.webgains.com/affiliates/link.html?action=submitsearch&newsearch&programid=$AffMerchantId";
			}
			else{
				$strUrl = "http://us.webgains.com/affiliates/link.html?action=submitsearch&paginate=1&startnum=".(($nPageNo - 1) * $nNumPerPage + 1)."&programid=$AffMerchantId";
			}
			
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$result = $r["content"];

			if($this->debug) print "Get links Data : Page - $nPageNo <br>\n";

			if ($nPageNo == 1){
				//When open the first page. Try get total links count.
				$nLineStart = 0;
				$nTotalLinkCount = intval(trim($this->oLinkFeed->ParseStringBy2Tag($result, array('records', 'of'), '<br />', $nLineStart)));
				if($this->debug) print "nTotalLinkCount $nTotalLinkCount  <br>\n";
			}

			$strLineStart = 'style="text-decoration:none">+</a>';
			$nLineStart = 0;
			while ($nLineStart >= 0)
			{
				$nLineStart = stripos($result, $strLineStart, $nLineStart);
				if ($nLineStart === false) break;

				$aff_mer_name = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
				if ($aff_mer_name === false) break;
				$aff_mer_name = trim($aff_mer_name);

				//Link Type
				$link_type= $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
				if ($link_type === false) break;
				$link_type = trim($link_type);

				//get Link ID
				$link_id = $this->oLinkFeed->ParseStringBy2Tag($result, '/affiliates/link.html?action=view&linkID=', '">view</a>', $nLineStart);
				if ($link_id === false) break;
				$link_id = trim($link_id);

				if($newonly && isset($existing_link_ids[$link_id]))
				{
					$arr_return["AffectedCount"] ++;
					continue;
				}
				
				//Open Detail Page
				$strDetailUrl = 'http://us.webgains.com/affiliates/link.html?action=view&linkID='.$link_id;
				$r = $this->oLinkFeed->GetHttpResult($strDetailUrl,$request);
				$Detailresult = $r["content"];
				if($r["code"] != 200)
				{
					print "warning: get link detail page failed<br>\n";
					continue;
				}
				
				//parse detail page
				$nDetailLineStart = 0;
				$link_name = $this->oLinkFeed->ParseStringBy2Tag($Detailresult, array('<b>Name</b>', '<td>'), '</td>', $nDetailLineStart);
				//if($link_name === false) break;
				if($link_name === false) mydie("die: link_name not found\n");
				$link_name = trim($link_name);

				//$original_url = $this->oLinkFeed->ParseStringBy2Tag($Detailresult, '<b>Target</b>', '<td>', '</td>', $nDetailLineStart);
				//$original_url = trim($original_url);
				//if ($original_url === false) break;

				$link_src_lastupdate = $this->oLinkFeed->ParseStringBy2Tag($Detailresult, array('<b>Last Modified</b>', '<td>'), '</td>', $nDetailLineStart);
				if($this->debug) print "link_src_lastupdate $link_src_lastupdate  <br>\n";
				if ($link_src_lastupdate === false) break;
				$link_src_lastupdate = date('Y-m-d H:i:s', strtotime(trim($link_src_lastupdate)));
				if($this->debug) print "link_src_lastupdate $link_src_lastupdate  <br>\n";

				if (trim($link_type) == 'graphic')
					$html_code = '<a href="http://track.webgains.com/click.html?wglinkid='.$link_id.'&wgcampaignid='.$strUKSiteID.'"><img border=0 src="http://track.webgains.com/link.html?wglinkid='.$link_id.'&wgcampaignid='.$strUKSiteID.'"/></a>';
				else
					$html_code = '<iframe height="500" frameborder="0" width="700" src="http://us.webgains.com/link_preview.html?js=1&wglinkid='.$link_id.'&wgcampaignid='.$strUKSiteID.'"></iframe>';
				$start_date = date("Y-m-d");
				$end_date = '0000-00-00';
				$link_desc = $link_name;

				$promo_type = $this->oLinkFeed->getPromoTypeByLinkContent($link_desc);
				if($this->debug) print "promo_type $promo_type  <br>\n";

				$arr_one_link = array(
					"AffId" => $merinfo["AffId"],
					"AffMerchantId" => $merinfo["AffMerchantId"],
					"AffLinkId" => $link_id,
					"LinkName" => html_entity_decode($link_name),
					"LinkDesc" => html_entity_decode($link_desc),
					"LinkStartDate" => $start_date,
					"LinkEndDate" => $end_date,
					"LinkCode" => "",
					"LinkPromoType" => $promo_type,
					"LinkHtmlCode" => $html_code,
					"LinkOriginalUrl" => "",
					"LinkImageUrl" => "",
					"LinkAffUrl" => 'http://track.webgains.com/click.html?wglinkid='.$link_id.'&wgcampaignid='.$strUKSiteID,
					"DataSource" => $this->DataSource["website"],
				    "Type"       => 'link'
				);
				$code = get_linkcode_by_text($arr_one_link['LinkName'] . '|' . $arr_one_link['LinkDesc'] . '|' . $arr_one_link['LinkHtmlCode']);
					if (!empty($code)) {
						$arr_one_link['LinkCode'] = $code;
						$arr_one_link['LinkPromoType'] = 'coupon';
					}
				$this->oLinkFeed->fixEnocding($this->info,$arr_one_link,"link");
				$arrToUpdate[] = $arr_one_link;
				$arr_return["AffectedCount"] ++;
				//exit;
			}//page

			if(sizeof($arrToUpdate) > 0)
			{
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($arrToUpdate);
				$arrToUpdate = array();
			}
			
			//Check if have next page;
			/// 
			if($this->debug) print " ($nTotalLinkCount > ($nNumPerPage * $nPageNo))  <br>\n";
			if ($nTotalLinkCount > ($nNumPerPage * $nPageNo)){
				$bHasNextPage = true;
				if($this->debug) print " Have NEXT PAGE  <br>\n";
			}
			else{
				$bHasNextPage = false;
				if($this->debug) print " NO NEXT PAGE  <br>\n";
			}
			
			if ($bHasNextPage){
				$nPageNo++;
			}
		}//get links per page

		//step 3, get voucher code
		$strUrl = "http://us.webgains.com/affiliates/vouchers.html?action=list&pid=$AffMerchantId";
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];

		if($this->debug) print "Get Voucher Code List  <br>\n";

		$nLineStart = 0;
		$aff_mer_name = $this->oLinkFeed->ParseStringBy2Tag($result, 'Generic voucher codes for program: ', '</h1>', $nLineStart);
		if($this->debug) print "aff_mer_name $aff_mer_name  <br>\n";
		if ($aff_mer_name !== false)
		{
			$strLineStart = '<tr>';
			while ($nLineStart >= 0){
				$nLineStart = stripos($result, $strLineStart, $nLineStart);
				if ($nLineStart === false) break;

				$voucher_code = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
				if($this->debug) print "voucher_code $voucher_code  <br>\n";
				if ($voucher_code === false) break;

				$link_name = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
				if($this->debug) print "link_name $link_name  <br>\n";
				if ($link_name === false) break;

				$link_desc = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
				if($this->debug) print "link_desc $link_desc  <br>\n";
				if ($link_desc === false) break;

				$original_url = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
				if($this->debug) print "original_url $original_url  <br>\n";
				//if ($original_url === false) break;

				$start_date = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
				if($this->debug) print "start_date $start_date  <br>\n";
				if ($start_date === false) break;

				$end_date = $this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart);
				if($this->debug) print "end_date $end_date  <br>\n";
				if ($end_date === false) break;
				$end_date = date("Y-m-d 23:59:59", strtotime($end_date));

				//get Link ID
				$link_id = 'voucher_'.$voucher_code.'_'.$link_name;
				$promo_type = 'coupon';
				//$html_code = 'Voucher Code: '.$voucher_code.'. Please just set URL as blank. (using the default url of merchant.). Please note do not add the voucher code if it shows: "not to be used by affiliates" or "Offline code" ';
				$html_code = sprintf('<a href="http://track.webgains.com/click.html?wgcampaignid=%s&wgprogramid=%s&wgtarget=%s">%s</a>', $strUKSiteID, $merinfo['AffMerchantId'], $original_url, $link_name);
				$arr_one_link = array(
						"AffId" => $merinfo["AffId"],
						"AffMerchantId" => $merinfo["AffMerchantId"],
						"AffLinkId" => $link_id,
						"LinkName" => html_entity_decode($link_name),
						"LinkDesc" => html_entity_decode($link_desc),
						"LinkStartDate" => $start_date,
						"LinkEndDate" => $end_date,
						"LinkPromoType" => $promo_type,
						"LinkHtmlCode" => $html_code,
						"LinkCode" => $voucher_code,
						"LinkOriginalUrl" => "",
						"LinkImageUrl" => "",
						"LinkAffUrl" => sprintf('http://track.webgains.com/click.html?wgcampaignid=%s&wgprogramid=%s&wgtarget=%s', $strUKSiteID, $merinfo['AffMerchantId'], $original_url),
						"DataSource" => $this->DataSource["website"],
				        "Type"       => 'link'
					);
				if(empty($arr_one_link['LinkCode'])){
					$code = get_linkcode_by_text($arr_one_link['LinkName'] . '|' . $arr_one_link['LinkDesc'] . '|' . $arr_one_link['LinkHtmlCode']);
					if (!empty($code)) {
						$arr_one_link['LinkCode'] = $code;
						$arr_one_link['LinkPromoType'] = 'coupon';
					}
				}
				$this->oLinkFeed->fixEnocding($this->info,$arr_one_link,"link");
				$arrToUpdate[] = $arr_one_link;
				$arr_return["AffectedCount"] ++;
			}
		}
		if(sizeof($arrToUpdate) > 0)
		{
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($arrToUpdate);
			$arrToUpdate = array();
		}
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link', $merinfo['IdInAff']);
		return $arr_return;
	}

	function getSiteId()
	{
		if(SID == 'bdg02' ){
			switch($this->info["AffId"])
			{
				case 13: // UK
					return '192821';
				case 18: // IE
					return '206753';
				default:
					mydie("die:Wrong AffID for LinkFeed_Webgains\n");
			}
		}else{
			switch($this->info["AffId"])
			{
				case 13: // UK
					return '207241';
				case 14: // US
					return '207233';
				case 18: // IE
					return '206803';
				case 34: // DE
					return '207237';
				case 208: // FR
					return '207235';
				case 395: // AU
					return '207239';
				default:
					mydie("die:Wrong AffID for LinkFeed_Webgains\n");
			}
		}		
	}
	
	function SwitchWebgainsToSelectWebSite($checkonly=false)
	{
		$strUKSiteID = $this->getSiteId();
		if(isset($this->oLinkFeed->WebgainsCurrentSite) && $this->oLinkFeed->WebgainsCurrentSite == $strUKSiteID) return true;

		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "post",
			"postdata" => "", 
		);
		
		$strUrl = "http://us.webgains.com/affiliates/index.html";
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];
		
		$nLineStart = 0;
		$strSiteSelected = trim($this->oLinkFeed->ParseStringBy2Tag($result, 'value="'.$strUKSiteID.'"', '>', $nLineStart));
		if (strtolower($strSiteSelected) == 'selected')
		{
			//is UK Site now, do nothing
			if($checkonly) echo "double check site switch result: ok! \n";
			else echo "is $strUKSiteID Site now, do nothing <br> \n";
			
			$this->oLinkFeed->WebgainsCurrentSite = $strUKSiteID;
			return true;
		}
		elseif($checkonly) mydie("die: SwitchWebgainsToSelectWebSite failed.\n");
		else{
			if($this->debug) echo "is NOT $strUKSiteID site now. do switch <br> \n";
			//is NOT $strUKSiteID site now. do switch
			$nLineStart = 0;
			$strUrl = 'http://us.webgains.com/affiliates/index.html';
			$param_globalaction = $this->oLinkFeed->ParseStringBy2Tag($result, array('action="http://us.webgains.com/affiliates/index.html"', 'name="globalaction" value="'), '" />', $nLineStart);
			$param___utma = $this->oLinkFeed->ParseStringBy2Tag($result, 'name="__utma" value="', '" />', $nLineStart);
			$param___utmz = $this->oLinkFeed->ParseStringBy2Tag($result, 'name="__utmz" value="', '" />', $nLineStart);
			$param___utmv = $this->oLinkFeed->ParseStringBy2Tag($result, 'name="__utmv" value="', '" />', $nLineStart);
			$param___utmc = $this->oLinkFeed->ParseStringBy2Tag($result, 'name="__utmc" value="', '" />', $nLineStart);
			$param___utmb = $this->oLinkFeed->ParseStringBy2Tag($result, 'name="__utmb" value="', '" />', $nLineStart);
			$param_wgsite = $this->oLinkFeed->ParseStringBy2Tag($result, 'name="wgsite" value="', '" />', $nLineStart);
			$param_WTSESSID = $this->oLinkFeed->ParseStringBy2Tag($result, 'name="WTSESSID" value="', '" />', $nLineStart);
			$param_usertype = $this->oLinkFeed->ParseStringBy2Tag($result, 'name="usertype" value="', '" />', $nLineStart);
			$param_campaignswitchid = $strUKSiteID;

			$request["postdata"] = 'globalaction='.urlencode($param_globalaction).'&__utma='.urlencode($param___utma).'&__utmz='.urlencode($param___utmz).'&__utmv='.urlencode($param___utmv).'&__utmc='.urlencode($param___utmc).'&__utmb='.urlencode($param___utmb).'&wgsite='.urlencode($param_wgsite).'&WTSESSID='.urlencode($param_WTSESSID).'&usertype='.urlencode($param_usertype).'&campaignswitchid='.urlencode($param_campaignswitchid);

			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			return $this->SwitchWebgainsToSelectWebSite(true);
		}
	}
	
	function SwitchWebgainsToSelectWebSiteNew($checkonly=false, $times = 3, $siteid = 0)
	{
		$strUKSiteID = $this->getSiteId();
		if(isset($this->oLinkFeed->WebgainsCurrentSite) && $this->oLinkFeed->WebgainsCurrentSite == $strUKSiteID) return true;
		
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "get",			
		);		
		
		if($times < 3){
			$request["method"] = "post";
			$request["postdata"] = "globalaction=switchcampaign&campaignswitchid=$strUKSiteID";
		}

		if($siteid == 0){
			$siteid = $strUKSiteID;
		}
		
		$strUrl = "http://www.webgains.com/publisher/$siteid";	
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];
		
		$strSiteSelected = trim($this->oLinkFeed->ParseStringBy2Tag($result, 'currentCampaign:', ','));
		if (intval($strSiteSelected) == $strUKSiteID)
		{
			//is UK Site now, do nothing
			if($checkonly) echo "double check site switch result: ok! \n";
			else echo "is $strUKSiteID Site now, do nothing <br> \n";
			
			$this->oLinkFeed->WebgainsCurrentSite = $strUKSiteID;
			return true;
		}
		elseif($checkonly) mydie("die: SwitchWebgainsToSelectWebSiteNew failed.\n");
		else{
			if($times > 0){
				$times--;
				echo "[$strSiteSelected] is NOT $strUKSiteID site now. do switch has $times chances.<br> \n";
				//is NOT $strUKSiteID site now. do switch			
				return $this->SwitchWebgainsToSelectWebSiteNew(false, $times, $strSiteSelected);
			}else{
				mydie("die: SwitchWebgainsToSelectWebSiteNew failed, try 3 times.\n");
			}
		}
	}
	

	
	function GetProgramFromAff()
	{
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
		
		$this->GetProgramByApi();
		$this->getProgramDetail($check_date);
		$this->checkProgramOffline($this->info["AffId"], $check_date);
		
		echo "\tSet program country int.\r\n";
		$objProgram = new ProgramDb();
		$objProgram->setCountryInt($this->info["AffId"]);
		
		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}
	
	function GetProgramByApi()
	{
		echo "\tGet Program by api start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;

		$campaignid = $this->getSiteId();

		//step 1,login
		/*$this->info["AffLoginUrl"] = "http://www.webgains.com/loginform.html?action=login";
		$this->info["AffLoginVerifyString"] = "Ran Chen";
		$this->info["AffLoginSuccUrl"] = "http://www.webgains.com/publisher/$campaignid";
		//$this->info["port"] = 443;
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info, 6);
		$this->SwitchWebgainsToSelectWebSiteNew();
		$request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "");*/

		// get SupportDeepurl
		/*$str_url = "http://www.webgains.com/publisher/$campaignid/ad/index/create-ad/";
		$tmp_arr = $this->oLinkFeed->GetHttpResult($str_url, $request);
		$result = $tmp_arr["content"];
		$result = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('linkGenerator', 'programs: ['), ']'));

		$matches = array();
		preg_match_all('/"key":(\d+),/', $result, $matches);
		$hasSupportDeepurl = false;
		$SupportDeepurl_arr = array();
		
		if(count($matches) && isset($matches[1]) && count($matches[1]) > 100){
			$hasSupportDeepurl = true;
			$SupportDeepurl_arr = array_flip($matches[1]);
		}*/
		
		$request_arr = array('username' => $this->info["Account"],
							'password' => $this->info["Password"],
							'campaignid' => $campaignid);

		$client  = new SoapClient(INCLUDE_ROOT."wsdl/webgains.wsdl", array('trace'=> true));
		$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"programs_".date("YmdH").".dat", "program");		
		if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
		{
			$retry = 1;
			while ($retry < 5) {
				$results = $client->__soapCall("getProgramsWithMembershipStatus", $request_arr);
				if (!empty($results)) {
					break;
				}
				
			}
			
			$results = json_encode($results);
			$this->oLinkFeed->fileCachePut($cache_file, $results);
		}
		$cache_file = file_get_contents($cache_file);
		$cache_file = json_decode($cache_file);		
		if(count($cache_file))
		{
			foreach($cache_file as $v)
			{				
				$strMerID = intval($v->programID);
				if(!$strMerID)
					continue;
				
				$TargetCountryExt = trim(str_ireplace("Webgains", "", $v->programNetworkName));
				$strMerName = trim($v->programName);
				$strStatus = $v->programMembershipStatusName;
				if (stripos($strStatus, 'Live') !== false || $strStatus == 'Joined')
				{
					$Partnership = 'Active';
					$StatusInAff = "Active";
				}
				elseif (stripos($strStatus, 'Not joined') !== false)
				{
					$Partnership = 'NoPartnership';
					$StatusInAff = "Active";
				}
				elseif (stripos($strStatus, 'Pending approval') !== false)
				{
					$Partnership = 'Pending';
					$StatusInAff = "Active";
				}
				elseif (stripos($strStatus, 'Suspended') !== false)
				{
					$Partnership = 'Expired';
					$StatusInAff = "Active";
				}
				elseif (stripos($strStatus, 'Rejected') !== false)
				{
					$Partnership = 'Declined';
					$StatusInAff = "Active";
				}
				elseif (stripos($strStatus, 'siteclosed') !== false)
				{
					$Partnership = 'Active';
					$StatusInAff = "Offline";
				}
				else{
					$Partnership = 'NoPartnership';
					$StatusInAff = "Active";
				}
				$desc = $v->programDescription;

				$AllowNonaffCoupon ='UNKNOWN';
				$AllowNonaffPromo ='UNKNOWN';
				$desc = $v->programDescription;
				if(preg_match('/Affiliates should only promote discount codes that have been provided to them through the Webgains platform./',$desc)){
					$AllowNonaffCoupon ='NO';
				}else if(preg_match('/Affiliates may ONLY advertise coupon codes that are distributed by the merchant (or AffiliRed on behalf of the merchant). Any sales registered through other coupon codes will not be considered as valid and will be canceled./',$desc)){
					$AllowNonaffCoupon ='NO';
				}else if(preg_match('/Affiliates are not allowed to promote coupon codes that have not been issued via the affiliate channel./',$desc)){
					$AllowNonaffCoupon ='NO';
				}
				//通用条件
				else if(preg_match('/affiliates can only use the voucher codes supplied/',$desc)){
					$AllowNonaffCoupon ='NO';
				}else if(preg_match('/Voucher sites must only promote codes that have been designated for affiliate use/',$desc)){
					$AllowNonaffCoupon ='NO';
				}else if(preg_match('/Affiliates shouldn’t post, use or feature any discount\/voucher codes from offline media sources./',$desc)){
					$AllowNonaffCoupon ='NO';
				}else if(preg_match('/publishers on the (.)+affiliate program should only use and monetise voucher codes (.)+ This includes user generated content, this cannot be monetised without the relevant permissions./',$desc)){
					$AllowNonaffCoupon ='NO';
				}else if(preg_match('/It is not allowed to promote vouchers that have not been communicated via the affiliate channel/',$desc)){
					$AllowNonaffCoupon ='NO';
				}else if(preg_match('/affiliates may only promote voucher codes/',$desc)){
					$AllowNonaffCoupon ='NO';
				}else if(preg_match('/Affiliates are not to promote any voucher codes that have not been provided/',$desc)){
					$AllowNonaffCoupon ='NO';
				}else if(preg_match('/Affiliates should not display voucher\/discount codes that have been provided for use by other marketing channels./',$desc)){
					$AllowNonaffCoupon ='NO';
				}else if(preg_match('/Affiliates found to be promoting unauthorised discount codes or those issued through other marketing channels/',$desc)){
					$AllowNonaffCoupon ='NO';
				}else if(preg_match('/Affiliates are ONLY allowed to use voucher codes issued to/',$desc)){
					$AllowNonaffCoupon ='NO';
				}else if(preg_match('/Affiliates are requested not to use voucher codes/',$desc)){
					$AllowNonaffCoupon ='NO';
				}else if(preg_match('/Voucher code sites may not list false voucher codes or voucher codes not associated with the affiliate program/',$desc)){
					$AllowNonaffCoupon ='NO';
				}else if(preg_match('/Any sites found to be running voucher codes not specifically authorised/',$desc)){
					$AllowNonaffCoupon ='NO';
				}else if(preg_match('/Publishers may only use coupons and promotional codes that are provided exclusively through the affiliate program./',$desc)){
					$AllowNonaffCoupon ='NO';
				}else if(preg_match('/Affiliates may not use misleading text on affiliate links	 buttons or images to imply that anything besides currently authorized affiliate deals or savings are available./',$desc)){
					$AllowNonaffCoupon ='NO';
					$AllowNonaffPromo = 'NO';
				}else if(preg_match('/Any discount promotion of our products by affiliates should be authorized/',$desc)){
					$AllowNonaffCoupon ='NO';
					$AllowNonaffPromo = 'NO';
				}else if(preg_match('/The only coupons authorized for use are those that we make directly available to you./',$desc)){
					$AllowNonaffCoupon ='NO';
				}else if(preg_match('/All coupons must be publicly distributed coupons that are given to the affiliate./',$desc)){
					$AllowNonaffCoupon ='NO';
				}else if(preg_match('/Coupon sites may only post distributed coupons; that is coupons that are given to them or posted within the affiliate interface./',$desc)){
					$AllowNonaffCoupon ='NO';
				}else if(preg_match('/They need to promote the coupon which we will provide them./',$desc)){
					$AllowNonaffCoupon ='NO';
				}else if(preg_match('/Publishers may only use coupons and promotional codes that are provided through communication specifically intended for publishers in the affiliate program./',$desc)){
					$AllowNonaffCoupon ='NO';
				}else if(preg_match('/These are the ONLY promotion codes affiliates are authorized to use in their marketing efforts/',$desc)){
					$AllowNonaffCoupon ='NO';
				}else if(preg_match('/will review each coupon offering before allowing an affiliate to use./',$desc)){
					$AllowNonaffCoupon ='NO';
				}
				if(stripos($strMerName, "closed") !== false) {
					//$strMerName = str_ireplace("closed", "", $strMerName);
					$strStatus = "closed";
					$StatusInAff = "Offline";
				}	

				
				$arr_prgm[$strMerID] = array(
											"Name" => addslashes(html_entity_decode($strMerName)),
											"AffId" => $this->info["AffId"],
											"Homepage" => addslashes($v->programURL),
											"IdInAff" => $strMerID,
											//"Contacts" => addslashes($Contacts),
											"TargetCountryExt" => $TargetCountryExt,
											"StatusInAffRemark" => addslashes($strStatus),
											"StatusInAff" => $StatusInAff,						//'Active','TempOffline','Offline'
											"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
											"Description" => addslashes($desc),
											"AllowNonaffCoupon"=>$AllowNonaffCoupon,
											"AllowNonaffPromo"=>$AllowNonaffPromo,
											//"JoinDate" => $JoinDate,
											//"CreateDate" => $CreateDate,
											//"CommissionExt" => addslashes($CommissionExt),
											//"CookieTime" => $ReturnDays,
											//"SEMPolicyExt" => addslashes($SEMPolicyExt),
											"LastUpdateTime" => date("Y-m-d H:i:s"),
											//"DetailPage" => $prgm_url,
											//"SupportDeepUrl" => $SupportDeepurl,
											);
				//print_r($arr_prgm);exit;					
				//program_detail
				//because webgains api is not complated, get info based on website
				//$prgm_url = "http://us.webgains.com/affiliates/program.html?action=view&programID=$strMerID";
				/*$prgm_url = "http://www.webgains.com/publisher/{$campaignid}/program/view?programID=$strMerID";
				//$prgm_url = "http://www.webgains.com/affiliates/{$campaignid}/program/view?programID={$strMerID}";
				//http://us.webgains.com/publisher/73616/program/view?programID=10989
				$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
				$prgm_detail = $prgm_arr["content"];
				
				$arr_prgm[$strMerID]["DetailPage"] = addslashes($prgm_url);
				
				$Contacts = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Contact details','Account manager:'), '</h2>')));
				$tmp_email = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Account manager','mailto:'), '"'));
				if(!empty($tmp_email)) $Contacts .= ", Email: ".$tmp_email;
				$arr_prgm[$strMerID]["Contacts"] = addslashes($Contacts);
				
				$ReturnDays = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Cookie period:','<h2>'), '</h2>'));
				$ReturnDays = preg_replace("/[^0-9]/", "", $ReturnDays);
				$arr_prgm[$strMerID]["CookieTime"] = addslashes($ReturnDays);
				
				$SEMPolicyExt = "PPC Policy Overview:". trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, 'PPC Policy Overview:', '<br/>'));
				$SEMPolicyExt .= ", Keyword policy details:". trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('id="keywordPolicyBox"', '<div class="modal-body">'), '</div>')));
				$arr_prgm[$strMerID]["SEMPolicyExt"] = addslashes($SEMPolicyExt); 
				
				$CommissionExt = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Commission details', '<h2>'), '<span class=')));
				$arr_prgm[$strMerID]["CommissionExt"] = addslashes($CommissionExt); 
				
				$prgm_status = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, '<div class="ribbon-red">', '</div>')));					
				if (stripos($prgm_status, 'Suspended') !== false){
					//$Partnership = 'Expired';
					$StatusInAff = "Offline";
					$strStatus = $prgm_status;
				}
				elseif (stripos($prgm_status, 'Rejected') !== false){
					$Partnership = 'Declined';
					$StatusInAff = "Active";
					$strStatus = $prgm_status;
				}
											
				//check support deep_links_	
				$deep_arr = array();			
				$deep_arr = $this->oLinkFeed->GetHttpResult("http://www.webgains.com/front/publisher/program/get-tools/programid/$strMerID", $request);				
				$tmp_obj = new stdClass();
				$tmp_obj = $deep_arr["content"];
				if(trim($tmp_obj) != "null"){
					$tmp_obj = json_decode($deep_arr["content"]);
					if(isset($tmp_obj->deep_links)){			
						if($tmp_obj->deep_links == "Allowed"){
							$arr_prgm[$strMerID]["SupportDeepurl"] = "YES";
						}else{
							$arr_prgm[$strMerID]["SupportDeepurl"] = "NO";
						}
					}
				}*/
				
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
		}else{
			mydie("die: get info by Api failed.\n");
		}
		echo "\tGet Program by api end\r\n";
		
		if($program_num < 10){
			mydie("die: program count < 10, please check program.\n");
		}
		
		echo "\tUpdate ({$program_num}) program.\r\n";		
	}
	
	function getCategoryByAff($affid, $objProgram){		
		$affid = intval($affid);
		if(!$affid) return false;
		$this->CategoryList = array();
		$sql = "SELECT a.Name as sname,b.Name as fname FROM category a LEFT JOIN category b ON a.PID = b.ID WHERE a.affid=$affid AND a.PID>0";
		$tmp_arr = $objProgram->objMysql->getRows($sql);
		foreach ($tmp_arr as $value){
			$this->CategoryList[$value['fname']][] = trim($value['sname']);
		}
	}
	

	function getProgramDetail($check_date){
		echo "\tGet Program detail start\r\n";
		$objProgram = new ProgramDb();
		$program_num = 0;
		$arr_prgm = array();
		
		$campaignid = $this->getSiteId();
		
//		if(SID == 'bdg02'){
//			$this->getCategoryByAff($this->info["AffId"], $objProgram);
//		}
		//step 1,login
		$this->info["AffLoginUrl"] = "http://www.webgains.com/loginform.html?action=login";
//		$this->info["AffLoginVerifyString"] = "Ran Chen";
		$this->info["AffLoginSuccUrl"] = "http://www.webgains.com/publisher/$campaignid";
		//$this->info["port"] = 443;		
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info, 6, true, false, false);
		$this->SwitchWebgainsToSelectWebSiteNew();
		$request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "");
		
		//get category
		echo "\tGet program by json start\r\n";
		$page = 1;
		$hasNextPage = true;
		while($hasNextPage)
		{
			$url = "http://www.webgains.com/publisher/$campaignid/program/list/get-data/joined/all/order/name/sort/asc/keyword//country//category//status/?columns%5B%5D=name&columns%5B%5D=status&columns%5B%5D=voucher_code_enabled&columns%5B%5D=categories&columns%5B%5D=keywords&columns%5B%5D=direct_ppc&columns%5B%5D=vouchers&columns%5B%5D=own_ppc&columns%5B%5D=seo&columns%5B%5D=action&subcategory=&page=$page";
			$retry = 1;
			while (true) {
				$re = $this->oLinkFeed->GetHttpResult($url, $request);
				$re = json_decode($re['content'],true);
				//var_dump($re);exit;
				if (isset($re['data']) && !empty($re['data'])) {
					break;
				}
				if ($retry > 3) {
					echo $url;
					mydie("data crawl is empty, please check the API");
				}
				sleep(3);
				$retry ++;
			}
			
			if ($re['pagesNumber'] == ($page + 1))
				$hasNextPage = false;
			
			foreach ($re['data'] as $v)
			{
				$strMerID = $v['id'];
				if (!empty($v['categories']['long']))
					$CategoryExt = $v['categories']['long'];
				elseif (!empty($v['categories']['short']))
					$CategoryExt = $v['categories']['short'];
				else 
					$CategoryExt = '';
				$CookieTime = intval($v['cookieLength']);
				
				$arr_prgm[$strMerID] = array(
						"AffId" => $this->info["AffId"],
						"IdInAff" => $strMerID,
						"CategoryExt" => addslashes($CategoryExt),
						"CookieTime" => $CookieTime,
						//"CommissionExt" => addslashes($v['commissionString']),
				);
				$program_num++;
				if (count($arr_prgm) >= 100) {
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
			}
			$page++;
			if ($page > 100)
				mydie("die: Page overload.\n");
		}
		if (count($arr_prgm)) {
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
			$arr_prgm = array();
		}
		echo "\tGet program by json end\r\n";
		if ($program_num < 10)
			mydie("die: program count < 10, please check program.\n");
		echo "\tUpdate ({$program_num}) program.\r\n";
		
		
		$program_num = 0;

		$sql = "SELECT idinaff FROM program WHERE AffId = ".intval($this->info["AffId"])." and ((statusinaff = 'active' and partnership = 'active') or addtime >= '$check_date')";

//		echo $sql;die;
		$prgm = array();
		$prgm = $objProgram->objMysql->getRows($sql);
		
		echo "\tget ".count($prgm)." p\r\n";
		
		foreach($prgm as $v){
			$strMerID = $v["idinaff"];
			if(!$strMerID) continue;
			//program_detail
			//because webgains api is not complated, get info based on website
			//$prgm_url = "http://us.webgains.com/affiliates/program.html?action=view&programID=$strMerID";
			$prgm_url = "http://www.webgains.com/publisher/{$campaignid}/program/view?programID=$strMerID";
			
			$prgm_detail = '';
			$retry = 1;
			while ($retry < 4) {
				$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
				if($prgm_arr["code"] == 200 && !empty($prgm_arr["content"])) {
					$prgm_detail = $prgm_arr["content"];
					break;
				}
				$retry ++;
			}
			
			if(!empty($prgm_detail)){
				
				$arr_prgm[$strMerID] = array(
					"AffId" => $this->info["AffId"],
					"IdInAff" => $strMerID
				);
				
				$Contacts = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Contact details','Account manager:'), '</h2>')));
				$tmp_email = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Account manager','mailto:'), '"'));
				if(!empty($tmp_email)) $Contacts .= ", Email: ".$tmp_email;
				$arr_prgm[$strMerID]["Contacts"] = addslashes($Contacts);
				
				/* $ReturnDays = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Cookie period:','<h2>'), '</h2>'));
				$ReturnDays = preg_replace("/[^0-9]/", "", $ReturnDays);
				$arr_prgm[$strMerID]["CookieTime"] = addslashes($ReturnDays); */
				
				$SEMPolicyExt = "PPC Policy Overview:". trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, 'PPC Policy Overview:', '<br/>'));
				$SEMPolicyExt .= ", Keyword policy details:". trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('id="keywordPolicyBox"', '<div class="modal-body">'), '</div>')));
				$arr_prgm[$strMerID]["SEMPolicyExt"] = addslashes($SEMPolicyExt); 
				
				$CommissionExt = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Commission details', '<h2>'), '<span class=')));
				$arr_prgm[$strMerID]["CommissionExt"] = addslashes($CommissionExt); 
				
				$LogoUrl = 'http://www.webgains.com'.trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<div class="wrapper">', '<img src="'), '"'));
				$arr_prgm[$strMerID]['LogoUrl'] = addslashes($LogoUrl);
				
				$prgm_status = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, '<div class="ribbon-red">', '</div>')));					
				if (stripos($prgm_status, 'Suspended') !== false){
					//$Partnership = 'Expired';
					$StatusInAff = "Offline";
					$strStatus = $prgm_status;
				}
				elseif (stripos($prgm_status, 'Rejected') !== false){
					$Partnership = 'Declined';
					$StatusInAff = "Active";
					$strStatus = $prgm_status;
				}
											
				//check support deep_links_	
				$deep_arr = array();			
				$deep_arr = $this->oLinkFeed->GetHttpResult("http://www.webgains.com/front/publisher/program/get-tools/programid/$strMerID", $request);				
				$tmp_obj = new stdClass();
				$tmp_obj = $deep_arr["content"];
				if(trim($tmp_obj) != "null"){
					$tmp_obj = json_decode($deep_arr["content"]);
					if(isset($tmp_obj->deep_links)){			
						if($tmp_obj->deep_links == "Allowed"){
							$arr_prgm[$strMerID]["SupportDeepurl"] = "YES";
						}else{
							$arr_prgm[$strMerID]["SupportDeepurl"] = "NO";
						}
					}
				}
				

				$program_num++;
				if(count($arr_prgm) >= 100){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
			}
		}
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
			unset($arr_prgm);
		}
		
		echo "\tUpdate detail ({$program_num}) program.\r\n";
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

