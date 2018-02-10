<?php

require_once 'text_parse_helper.php';

require_once dirname(__FILE__).'./../../../func/func.php';

class LinkFeed_12_LC
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->getStatus = false;
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		$this->ftpHostname = 'lcpub.linkconnector.com';
		$this->ftpUsername = 'Brandrwd';
		$this->ftpPassword = 'BraFTP512';
		$this->ftpPort = '21';
		
		if(SID == 'bdg01')
			$this->API_KEY = 'e92c5ae65d15f6a732e5f602383215b8';
		else
			$this->API_KEY = 'df5301448794c73f18487f17875ca0b5';
	}

	function LoginIntoAffService()
	{
		//get para __VIEWSTATE and then process default login
		if(!isset($this->info["AffLoginPostStringOrig"])) $this->info["AffLoginPostStringOrig"] = $this->info["AffLoginPostString"];
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => "post",
				"postdata" => "",
		);

		$strUrl = $this->info["AffLoginUrl"];
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];
		$arr_hidden_name = array(
				"curdate" => "",
				"loginkey" => "",
		);
		$pattern = "/<input type=\\\"hidden\\\" name=\\\"(.*?)\\\" value=\\\"(.*?)\\\">/iu";
		if(!preg_match_all($pattern,$result,$matches)) mydie("die: LoginIntoAffService failed curdate not found\n");

		foreach($matches[1] as $i => $name)
		{
			if(isset($arr_hidden_name[$name])) $arr_hidden_name[$name] = $matches[2][$i];

		}
		foreach($arr_hidden_name as $name => $value)
		{
			if(empty($value)) mydie("die: LoginIntoAffService failed $name not found\n");
		}

		$this->getLoginCheckCode($arr_hidden_name);

		$arr_replace_from = array();
		$arr_replace_to = array();
		foreach($arr_hidden_name as $name => $value)
		{
			$arr_replace_from[] = "{" . $name . "}";
			$arr_replace_to[] = $value;
		}

		$this->info["AffLoginPostString"] = str_replace($arr_replace_from,$arr_replace_to,$this->info["AffLoginPostStringOrig"]);
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info,2,true,true,false);
		return "stophere";
	}

	function getLoginCheckCode(&$arr)
	{
		$t2 = strrev("123" . $arr["loginkey"] . $arr["curdate"]);
		$t = "";
		for($i=0;$i<strlen($t2);$i+=3)  $t .= $t2[$i];
		for($i=0;$i<strlen($t2);$i+=2)  $t .= $t2[$i];
		$arr["dest"] = substr($t,0,32);
	}
	
	function GetAllProductsByAffId()
	{
	    echo 'start time'.date('Y-m-d H:i:s').PHP_EOL;
	    $check_date = date('Y-m-d H:i:s');
	    $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
	    $request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "",);             
	    
	    $productNumConfig = $this->oLinkFeed->product_program_num_config($this->info["AffId"]);
	    $productNumConfigAlert = '';
	    $isAssignMerchant = FALSE;
	    //FTP
	    $config = array(
	        'hostname' => $this->ftpHostname,
	        'username' => $this->ftpUsername,
	        'password' => $this->ftpPassword,
	        'port' => $this->ftpPort,
	    );
	    
	    $ftp = new Ftp();
	    $ftp->connect($config);
	    $fileList =  $ftp->filelist('/feedsout');
	    
	    foreach ($fileList as $fileValue){
	        $maxCount = 0;
	        if($fileValue == '.' || $fileValue == '..'){
	            continue;
	        }
	        echo './feedsout/'.$fileValue.PHP_EOL;
	        $filemtime = $ftp->ftp_mdtm('./feedsout/'.$fileValue);
	        $filemtimeDay = date('Y-m-d',$filemtime);
	        $currentDay = date('Y-m-d'); 
	        
	        $product_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],$fileValue, "productftp", true);
	        //如果没有存在 || 更新时间在今天，重新更新一次
	        if(!$this->oLinkFeed->fileCacheIsCached($product_file) || $filemtimeDay>=$currentDay){
	            $downloadFlag = $ftp->download('./feedsout/'.$fileValue, $product_file);
	            if(!$downloadFlag){
	                
	                var_dump($downloadFlag);
	                echo 'download Failure!'.PHP_EOL;
	                $ftp->connect($config);
	            }
	            if(stripos($fileValue, '.gz')){
	                //$this->uncompress_gz($product_file);
	                continue;
	            }
	            echo $fileValue.PHP_EOL;
	            echo 'fileSize: '.filesize($product_file).PHP_EOL;
	             
	            
	            $istitle = 0;
	            $links = array();
	            $fileSource = fopen($product_file,"r");
	            if(!$fileSource) continue;
	            $crawlMerchantsActiveNum = 0;
	            while(!feof($fileSource)){
	                $maxCount ++;
	                $productData = array();
	                $istitle ++;
	                if($istitle == 1){
	                    $productFileFormat = array("MerchantID","CampaignID","ProductID","Title","Description","URL","ImageURL","Price");
	                    $productFileFix = array();
	                    $fileTitle = fgetcsv($fileSource);
	                    $fileTitle = array_flip($fileTitle);
	                    if(isset($fileTitle['Currency'])){ //如果有货币单位
	                        $productFileFormat[] = 'Currency'; 
	                    }
	                    //文件必须包含字段
	                    foreach ($productFileFormat as $valuePFF){
	                        if(isset($fileTitle[$valuePFF])){
	                            $productFileFix[$valuePFF] = $fileTitle[$valuePFF];
	                        }else{
	                            echo ("die: The file $fileValue----$valuePFF Format is error \n");
	                        }
	                    }
	                    continue;
	                }
	                
	                //正式读取文件内容
	                $fileData =  fgetcsv($fileSource);
                    if($fileData){
                        foreach ($productFileFix as $keyFF =>$valueFF){
                            $productData[$keyFF] = $fileData[$valueFF];
                        }
                    }
                    
                    if($productData){
                        
                        if(!isset($productData['Price'])){
                            print_r($productData);
                        }
                        if($productData['Price'] <= 0) continue;
                        
                        $affMerchantid = $productData['MerchantID'].'_'.$productData['CampaignID'];
                        $currency = isset($productData['Currency']) ? $productData['Currency'] : '';
                        $setMaxNum  = isset($productNumConfig[$affMerchantid]) ? $productNumConfig[$affMerchantid]['limit'] :  100;
                        $isAssignMerchant = isset($productNumConfig[$affMerchantid]) ? TRUE : FALSE;
                        //下载图片
                        $product_path_file = $this->oLinkFeed->getProductImgFilePath($this->info["AffId"],"{$affMerchantid}_".urlencode($productData['ProductID']).".png", PRODUCTDIR);
                        if(!$this->oLinkFeed->fileCacheIsCached($product_path_file)){
                            $file_content = $this->oLinkFeed->downloadImg($productData['ImageURL']);
                            if(!$file_content) //下载不了跳过。
                                continue;
                            $this->oLinkFeed->fileCachePut($product_path_file, $file_content);
                        }
                        
                        $link = array(
                            "AffId" => $this->info["AffId"],
                            "AffMerchantId" => $affMerchantid,
                            "AffProductId" => $productData['ProductID'],
                            "ProductName" => addslashes($productData['Title']),
                            "ProductCurrency" => $currency,
                            "ProductPrice" =>$productData['Price'],
                            "ProductImage" => addslashes($productData['ImageURL']),
                            "ProductLocalImage" => addslashes($product_path_file),
                            "ProductUrl" => $productData['URL'],
                            "ProductDestUrl" => '',
                            "ProductDesc" => addslashes($productData['Description']),
                            "ProductStartDate" => '',
                            "ProductEndDate" => '',
                        );
                        $links[] = $link;
                        $crawlMerchantsActiveNum ++;
                    }
                    if (count($links) >= 100)
                    {
                        $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateProductToDB($links);
                        $links = array();
                    }
	                if($crawlMerchantsActiveNum>=$setMaxNum){
					    break;
					}
                }
                
                if (count($links))
                {
                    $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateProductToDB($links);
                    $links = array();
                }
	            fclose($fileSource);
	        }
	    }
	    //关闭；
	    $ftp->close();
	    $this->oLinkFeed->checkProductExists($this->info["AffId"], $check_date);
	    return $arr_return;
	    exit;
	    
	    
	    
	     
	    $this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info, 1, false);
	    $arr_merchant = $this->oLinkFeed->getApprovalAffMerchant($this->info["AffId"]);
	    $merchants = array();
	    foreach ($arr_merchant as $key => $v)
	    {
	        $programKey = explode('_', $key);
	        if (isset($merchants[$programKey[0]]))
	            continue;
	        $merchants[$programKey[0]] = $v;
	    }
	     
	    foreach ($merchants as $k=>$value){
	        echo 'Merchant Id:'.$k.PHP_EOL;
	        $crawlMerchantsMaxNum = 0;
	        //if($k != 105085) continue;
	        //https://www.linkconnector.com/member/feedbrowser.htm?lcPFSelected=144360
	        $page = 1;
	        $limit = 25;
	        $HasNextPage = true;
	        while ($HasNextPage)
	        {
	             
	            $url = "https://www.linkconnector.com/member/inc/feedbrowser_items.php?lcPFMerchant=&lcPFSelected=$k&lcPFApproved=Approved&lcPFCatSearch=&lcPFViewAs=&lcPFPage=$page&lcPFHeader=&lcPFSearch=&lcPFSearchType=&lcPFLastTotal=0";
	            $r = $this->oLinkFeed->GetHttpResult($url, $request);
	            if($r['code'] != '200' || !$r['content']) continue;
	            $total_page = intval(trim($this->oLinkFeed->ParseStringBy2Tag($r['content'], array('<td style="padding-right: 10px;" class="lcPageNav">','of'), '</td>')));
	            if ($total_page <= $page){
	                $HasNextPage = false;
	            }
	            if(!$total_page) break;
	            $content =  $this->oLinkFeed->ParseStringBy2Tag($r['content'], '<td class="lcFeedContentLeft">&nbsp;</td>', '<td class="lcFeedContentRight">&nbsp;</td>');
	            //parse HTML
	            $strLineStart = '<table cellpadding="0" cellspacing="0" border="0" width="100%">';
	            $nLineStart = 0;
	            while ($nLineStart >= 0){
	                $cache_file = '';
	                $nLineStart = stripos($content, $strLineStart, $nLineStart);
	                if ($nLineStart === false) break;
	                 
	                $ProductName = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($content, array('<td style="padding: 0px 10px 0px 10px;">'), '</td>', $nLineStart)));
	                $Price = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($content, array('<td style="color: #333; padding: 5px 10px 10px 10px;">'), '</span>', $nLineStart)));
	                $Price = str_replace("$","",$Price);
	                $productDetail = $this->oLinkFeed->ParseStringBy2Tag($content, array('<td style="padding: 0px 10px;">'), 'span style="text-decoration: underline;">', $nLineStart);
	                if(preg_match('/<a href=\"(.*?)\"/is', $productDetail,$matches)){
	                    //https://www.linkconnector.com/member/inc/feedbrowser_html.php?PFMerchantID=144360&PFProductID=1034694-AAA
	                    $productDetailUrl = 'https://www.linkconnector.com/member/'.$matches[1];
	                    preg_match('/PFMerchantID\=(.*?)&/is',$matches[1],$matMid);
	                    $mid = $matMid[1];
	                    preg_match('/PFProductID\=(.*?)$/is',$matches[1],$matPid);
	                    $AffProductId = $matPid[1];
	                    $ImageURL = "http://www.linkconnector.com/images/products/{$mid}/".urlencode($AffProductId)."_150px.png";
	                     
	
	                    //下载图片
	                    $cache_file = $this->fileCacheGetFilePath($this->info["AffId"],"{$mid}_".urlencode($AffProductId)."_150px.png", "product", true);
	                    if(!$this->oLinkFeed->fileCacheIsCached($cache_file)){
	                        $picContent = file_get_contents($ImageURL);
	                        $this->oLinkFeed->fileCachePut($cache_file, $picContent);
	                    }
	                     
	                    //fileCacheIsCached
	                     
	                }else{
	                    continue;
	                }
	                if(!$mid || !$AffProductId ) continue;
	                 
	                 
	                $DetailArr = $this->oLinkFeed->GetHttpResult($productDetailUrl, $request);
	                if(preg_match('/http:\/\/www\.linkconnector\.com\/traffic_affiliate\.php(.*?)\'/is', $DetailArr['content'],$matpurl)){
	                    $ProductUrl =  "http://www.linkconnector.com/traffic_affiliate.php".(htmlspecialchars_decode($matpurl[1]));
	                }
	                else{
	                    continue;
	                }
	                 
	                if(!$ProductName) continue;
	                preg_match('/lcSetVar\(\'lcHTMLDesc\', \'(.*?)\'\);/is', $DetailArr['content'],$matdesc);
	                $desc = $matdesc[1];
	                 
	                 
	                $link = array(
	                    "AffId" => $this->info["AffId"],
	                    "AffMerchantId" => $merchants[$mid]['AffMerchantId'],
	                    "AffProductId" => $AffProductId,
	                    "ProductName" => addslashes($ProductName),
	                    "ProductCurrency" =>'USD',
	                    "ProductPrice" =>$Price,
	                    "ProductImage" => addslashes($ImageURL),
	                    "ProductLocalImage" => addslashes($cache_file),
	                    "ProductUrl" => addslashes($ProductUrl),
	                    "ProductDestUrl" => '',
	                    "ProductDesc" => $desc,
	                    "ProductStartDate" => '',
	                    "ProductEndDate" => '',
	                );
	                $links[] = $link;
	                $arr_return['AffectedCount'] ++;
	                $crawlMerchantsMaxNum ++;
	                if (count($links) >= 100)
	                {
	                    $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateProductToDB($links);
	                    $links = array();
	                }
	                //每个商家只爬1000
	                if($crawlMerchantsMaxNum >= 1000) break;
	            }
	             
	            if (count($links))
	            {
	                $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateProductToDB($links);
	                $links = array();
	            }
	            if($crawlMerchantsMaxNum >= 1000) break;
	            $page++;
	        }
	    }
	     
	    $this->oLinkFeed->checkProductExists($this->info["AffId"], $check_date);
	    echo sprintf("get product complete. %s links(s) found. \n", $arr_return["UpdatedCount"]);
	    echo 'END time'.date('Y-m-d H:i:s').PHP_EOL;
	    return $arr_return;
	}
	
	function GetAllProductsByAffIdOld()
	{
	    echo 'start time'.date('Y-m-d H:i:s').PHP_EOL;
	    $check_date = date('Y-m-d H:i:s');
	    $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
	    $request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "",);
	    $links = array();
	    
	    $this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info, 1, false);
	    $arr_merchant = $this->oLinkFeed->getApprovalAffMerchant($this->info["AffId"]);
	    $merchants = array();
	    foreach ($arr_merchant as $key => $v)
	    {
	        $programKey = explode('_', $key);
	        if (isset($merchants[$programKey[0]]))
	            continue;
	        $merchants[$programKey[0]] = $v;
	    }
	    
	    foreach ($merchants as $k=>$value){
	        echo 'Merchant Id:'.$k.PHP_EOL;
	        $crawlMerchantsMaxNum = 0;
	        //if($k != 105085) continue;
	        //https://www.linkconnector.com/member/feedbrowser.htm?lcPFSelected=144360
	        $page = 1;
	        $limit = 25;
	        $HasNextPage = true;
	        while ($HasNextPage)
	        {
	            
	            $url = "https://www.linkconnector.com/member/inc/feedbrowser_items.php?lcPFMerchant=&lcPFSelected=$k&lcPFApproved=Approved&lcPFCatSearch=&lcPFViewAs=&lcPFPage=$page&lcPFHeader=&lcPFSearch=&lcPFSearchType=&lcPFLastTotal=0";
	            $r = $this->oLinkFeed->GetHttpResult($url, $request);
	            if($r['code'] != '200' || !$r['content']) continue;
	            $total_page = intval(trim($this->oLinkFeed->ParseStringBy2Tag($r['content'], array('<td style="padding-right: 10px;" class="lcPageNav">','of'), '</td>')));
	            if ($total_page <= $page){
	                $HasNextPage = false;
	            }
	            if(!$total_page) break;
	            $content =  $this->oLinkFeed->ParseStringBy2Tag($r['content'], '<td class="lcFeedContentLeft">&nbsp;</td>', '<td class="lcFeedContentRight">&nbsp;</td>');
	            //parse HTML
			    $strLineStart = '<table cellpadding="0" cellspacing="0" border="0" width="100%">';
			    $nLineStart = 0;
	            while ($nLineStart >= 0){
	                $cache_file = '';
	                $nLineStart = stripos($content, $strLineStart, $nLineStart);
	                if ($nLineStart === false) break;
	                
	                $ProductName = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($content, array('<td style="padding: 0px 10px 0px 10px;">'), '</td>', $nLineStart)));
	                $Price = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($content, array('<td style="color: #333; padding: 5px 10px 10px 10px;">'), '</span>', $nLineStart)));
	                $Price = str_replace("$","",$Price);
	                $productDetail = $this->oLinkFeed->ParseStringBy2Tag($content, array('<td style="padding: 0px 10px;">'), 'span style="text-decoration: underline;">', $nLineStart);
	                if(preg_match('/<a href=\"(.*?)\"/is', $productDetail,$matches)){
	                    //https://www.linkconnector.com/member/inc/feedbrowser_html.php?PFMerchantID=144360&PFProductID=1034694-AAA
	                    $productDetailUrl = 'https://www.linkconnector.com/member/'.$matches[1];
	                    preg_match('/PFMerchantID\=(.*?)&/is',$matches[1],$matMid);
	                    $mid = $matMid[1];
	                    preg_match('/PFProductID\=(.*?)$/is',$matches[1],$matPid);
	                    $AffProductId = $matPid[1];
	                    $ImageURL = "http://www.linkconnector.com/images/products/{$mid}/".urlencode($AffProductId)."_150px.png";
	                    
	                     
	                    //下载图片
	                    $cache_file = $this->fileCacheGetFilePath($this->info["AffId"],"{$mid}_".urlencode($AffProductId)."_150px.png", "product", true);
	                    if(!$this->oLinkFeed->fileCacheIsCached($cache_file)){
	                        $picContent = file_get_contents($ImageURL);
	                        $this->oLinkFeed->fileCachePut($cache_file, $picContent);
	                    }
	                    
	                    //fileCacheIsCached
	                    
	                }else{
	                    continue;
	                }
	                if(!$mid || !$AffProductId ) continue;
	                
	                
	                $DetailArr = $this->oLinkFeed->GetHttpResult($productDetailUrl, $request);
	                if(preg_match('/http:\/\/www\.linkconnector\.com\/traffic_affiliate\.php(.*?)\'/is', $DetailArr['content'],$matpurl)){
	                   $ProductUrl =  "http://www.linkconnector.com/traffic_affiliate.php".(htmlspecialchars_decode($matpurl[1]));
	                }
	                else{
	                    continue;
	                }
	                
	                if(!$ProductName) continue;
	                preg_match('/lcSetVar\(\'lcHTMLDesc\', \'(.*?)\'\);/is', $DetailArr['content'],$matdesc);
	                $desc = $matdesc[1];
	                
	                
	                $link = array(
	                    "AffId" => $this->info["AffId"],
	                    "AffMerchantId" => $merchants[$mid]['AffMerchantId'],
	                    "AffProductId" => $AffProductId,
	                    "ProductName" => addslashes($ProductName),
	                    "ProductCurrency" =>'USD',
	                    "ProductPrice" =>$Price,
	                    "ProductImage" => addslashes($ImageURL),
	                    "ProductLocalImage" => addslashes($cache_file),
	                    "ProductUrl" => addslashes($ProductUrl),
	                    "ProductDestUrl" => '',
	                    "ProductDesc" => $desc,
	                    "ProductStartDate" => '',
	                    "ProductEndDate" => '',
	                );
	                $links[] = $link;
	                $arr_return['AffectedCount'] ++; 
	                $crawlMerchantsMaxNum ++;
	                if (count($links) >= 100)
	                {
	                    $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateProductToDB($links);
	                    $links = array();
	                }
	                //每个商家只爬1000
	                if($crawlMerchantsMaxNum >= 1000) break;
	            }
	            
	            if (count($links))
	            {
	                $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateProductToDB($links);
	                $links = array();
	            }
	            if($crawlMerchantsMaxNum >= 1000) break;
	            $page++;
	        }
	        
	        
	    }
	    
	    
	    
	    /*
	    $page = 1;
	    $limit = 50;
	    $HasNextPage = true;
	    while ($HasNextPage)
	    {
	        $url = 'https://www.linkconnector.com/member/merchantfeeds.htm';
	        $request['postdata'] = array("saveas" => true, "Page" => $page,"s_sort"=>'', "s_order"=>'','ddMerchants'=>'All','ddDisplay'=>$limit);
	        $r = $this->oLinkFeed->GetHttpResult($url, $request);
	        //print_r($r);exit;
	        if($r['code'] != '200' || !$r['content']) continue;
	        $total_page = intval(trim($this->oLinkFeed->ParseStringBy2Tag($r['content'], '<td class="lcTableFooterRight">', 'Found')));
	        if ($total_page <= $page * $limit)
	            $HasNextPage = false;
	        $LineStart = 0;
	        while (1)
	        {
	            $product_url = trim($this->oLinkFeed->ParseStringBy2Tag($r['content'], '<a href="javascript:windowHandle = window.open(\'', "'", $LineStart));
	            if (!$product_url)
	                break;
	            $mid = trim($this->oLinkFeed->ParseStringBy2Tag($product_url, 'mid=', '&'));
	            $product_url = "https://www.linkconnector.com/member/".$product_url;
	            $product_r = $this->oLinkFeed->GetHttpResult($product_url, $request);
	            $product_r = $product_r['content'];
	            $nLineStart = 0;
	            while (1)
	            {
	                $AffProductId = trim($this->oLinkFeed->ParseStringBy2Tag($product_r, '<td style="vertical-align:middle;" class="lcTable lcTableReport tblCellFirst">', '<', $nLineStart));
	                if (!$AffProductId)
	                    break;
	                $name = trim($this->oLinkFeed->ParseStringBy2Tag($product_r, array('lcTableReport">' ,'lcTableReport">', 'lcTableReport">'), '<', $nLineStart));
	                $desc = trim($this->oLinkFeed->ParseStringBy2Tag($product_r, 'lcTableReport">', '<', $nLineStart));
	                $Price = trim($this->oLinkFeed->ParseStringBy2Tag($product_r, 'lcTableReport">', '<', $nLineStart));
	                $ProductUrl = trim($this->oLinkFeed->ParseStringBy2Tag($product_r, 'lcTableReport">', '<', $nLineStart));
	                $ImageURL = trim($this->oLinkFeed->ParseStringBy2Tag($product_r, array('lcTableReport">', 'lcTableReport">'), '<', $nLineStart));
	                $link = array(
	                				"AffId" => $this->info["AffId"],
	                				"AffMerchantId" => $merchants[$mid]['AffMerchantId'],
	                				"AffProductId" => $AffProductId,
	                				"ProductName" => $name,
	                				"ProductCurrency" =>'USD',
	                				"ProductPrice" =>$Price,
	                				"ProductImage" => $ImageURL,
	                				"ProductUrl" => $ProductUrl,
	                				"ProductDestUrl" => '',
	                				"ProductDesc" => $desc,
	                				"ProductStartDate" => '',
	                				"ProductEndDate" => '',
	                );
	                $links[] = $link;
	                if (count($links) >= 100)
	                {
	                    $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateProductToDB($links);
	                    $links = array();break;
	                }
	    
	            }
	             
	        }
	        if (count($links))
	        {
	            $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateProductToDB($links);
	            $links = array();
	        }
	        $page++;
	    }*/
		
	    
	    
	    
	    
	        
	        
	        /*foreach ((array)$data as $value){
	             
	            $link = array(
	                "AffId" => $this->info["AffId"],
	                "AffMerchantId" => $proId,
	                "AffProductId" => $value['ProductID'],
	                "ProductName" => $value['Title'],
	                "ProductCurrency" => 'USD',
	                "ProductPrice" => $value['Price'],
	                "ProductImage" => $value['ImageURL'],
	                "ProductUrl" => $value['URL'],
	                "ProductDestUrl" => '',
	                "ProductDesc" => $value['Description'],
	                "ProductStartDate" => '',
	                "ProductEndDate" => '',
	                "LastUpdateTime" => '',
	                "AddTime" => $check_date,
	                "IsActive" => "YES",
	            );
	            
	            $final_url = '';
	            $final_content =  getTrueUrl($value['URL']);
	            $final_domain = parse_url($final_content['final_url']);
	            $program_domain = parse_url($programInfo['Homepage']);
	            if($final_domain['host'] != $program_domain['host']){
	                 
	                preg_match('/<meta http-equiv="refresh" content="1; url=(.*?)" \/>/', $final_content['response'],$matches);
	                $final_content = getTrueUrl(htmlspecialchars_decode($matches[1]));
	                $final_url = $final_content['final_url'];
	                 
	            }else{
	                $final_url = $final_content['final_url'];
	            }
	            
	            $dest_domain = parse_url($final_url);
	            $dest_url = $dest_domain['scheme'].'://'.$dest_domain['host'].$dest_domain['path'];
	            $link['ProductDestUrl'] = $dest_url;
	             
	            $links[] = $link;
	            if (count($links) >= 100)
	            {
	                $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateProductToDB($links);
	                $links = array();
	            }
	        }
	        if (count($links))
	        {
	            $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateProductToDB($links);
	            $links = array();
	        }*/
	        
	   
	    
	    
	    
	    $this->oLinkFeed->checkProductExists($this->info["AffId"], $check_date);
	    echo sprintf("get product complete. %s links(s) found. \n", $arr_return["UpdatedCount"]);
	    echo 'END time'.date('Y-m-d H:i:s').PHP_EOL;
	    return $arr_return;
	}

	function getCouponFeed()
	{
	    $check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		$request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "",);
		$links = array();
		// get promotion from api.
		// get coupon from csv feed is discard. login and use the url like this.
		// $url = 'https://www.linkconnector.com/member/coupons_feeds.php?key=VERSGgBDBmlfPVBnADdUZwNl&saveAs=true&types=CFOD&rptcols=,,0,2,3,4,5,6,8,9,10';
		// currently use the api to get the promotion data.
		// the api returns more records than the csv feed, and returns program id other than csv feed.
		$url = "http://www.linkconnector.com/api/";
		$request['postdata'] = array("Key" => $this->API_KEY, "Function" => "getFeedPromotion", "IncludeFields"=>"Description,Start Date");
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r['content'];
		$data = @csv_string_to_array($content);
		$count = 0;
		foreach ((array)$data as $v)
		{
			$link = array(
					"AffId" => $this->info["AffId"],
					"AffMerchantId" => sprintf('%s_%s', $v['MerchantID'], $v['CampaignID']),
					"AffLinkId" => sprintf('%s', $v['PromoID']),
					"LinkName" => $v['HeadLineTitle'],
					"LinkDesc" => $v['Description'],
					"LinkStartDate" => parse_time_str($v['Start Date'], null, false),
					"LinkEndDate" => parse_time_str($v['Expires'], null, true),
					"LinkPromoType" => 'COUPON',
					"LinkHtmlCode" => $v['Banner'],
					"LinkOriginalUrl" => '',
					"LinkImageUrl" => '',
					"LinkAffUrl" => $v['TrackingURL'],
					"DataSource" => "10",
			        "Type"       => 'promotion'
			);
			$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
			if ($v['Scope'] == 'Private')
				$link['LinkDesc'] .= '|Scope: Private';
			if (  empty($link['AffMerchantId']) || empty($link['AffLinkId']))
				continue;
			elseif(empty($link['LinkName'])){
				$link['LinkPromoType'] = 'link';
			}
			if (!empty($v['Coupon Code'])){
				$link['LinkPromoType'] = 'coupon';
				$link['LinkCode'] = $v['Coupon Code'];
			}else{
				$code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc'] . '|' . $link['LinkHtmlCode']);
				if (!empty($code)){
					$link['LinkPromoType'] = 'coupon';
					$link['LinkCode'] = $code;
				}else{
					$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
				}
			}
			$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
			$arr_return["AffectedCount"] ++;
			$count ++;
			$links[] = $link;
		}
		$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'promotion');
		echo sprintf("call api getFeedPromotion... %s links(s) found. \n", $count);

		// text or banner links and deep links
		$request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "",);
		$url = "http://www.linkconnector.com/api/";
		$functions = array('getLinkDeep', 'getLinkHTML');
		$links = array();
		foreach ($functions as $function)
		{
			$count = 0;
			$request['postdata'] = array("Key" => $this->API_KEY, "Function" => $function);
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$content = $r['content'];
			$data = @csv_string_to_array($content);
			foreach ((array)$data as $v)
			{
				$link = array(
						"AffId" => $this->info["AffId"],
						"AffMerchantId" => sprintf('%s_%s', $v['MerchantID'], $v['CampaignID']),
						"AffLinkId" => sprintf('%s', @$v['LinkID']),
						"LinkName" => @$v['LinkName'],
						"LinkDesc" => '',
						"LinkStartDate" => '0000-00-00',
						"LinkEndDate" => '0000-00-00',
						"LinkPromoType" => 'N/A',
						"LinkHtmlCode" => @$v['HTMLCode'],
						"LinkOriginalUrl" => '',
						"LinkImageUrl" => '',
						"LinkAffUrl" => '',
						"DataSource" => "10",
				        "Type"       => 'link'
				);
				if ($function == 'getLinkDeep'){
					$link['AffLinkId'] = sprintf('deep_%s_%s', $v['MerchantID'], $v['CampaignID']);
					$link['LinkName'] = sprintf('Deep link of %s - %s', $v['Merchant'], $v['Campaign']);
					$link['LinkAffUrl'] = $v['DeepLinkURL'];
					$link['LinkHtmlCode'] = create_link_htmlcode($link);
				}
				else{
					if (preg_match('@a href="(.*?)"@', $link['LinkHtmlCode'], $g))
						$link['LinkAffUrl'] = $g[1];
					if (preg_match('@img src="(.*?)"@', $link['LinkHtmlCode'], $g))
						$link['LinkImageUrl'] = $g[1];
				}
				if (!empty($v['Coupon Code'])){
					$link['LinkPromoType'] = 'coupon';
					$link['LinkCode'] = $v['Coupon Code'];
				}else{
					$code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc'] . '|' . $link['LinkHtmlCode']);
					if (!empty($code)){
						$link['LinkPromoType'] = 'coupon';
						$link['LinkCode'] = $code;
					}else{
						$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
					}
				}
				if (empty($link['AffLinkId']) || empty($link['LinkHtmlCode']) || empty($link['AffMerchantId']))
					continue;
				elseif(empty($link['LinkName'])){
					$link['LinkPromoType'] = 'link';
				}
				$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
				$arr_return["AffectedCount"] ++;
				$count ++;
				$links[] = $link;
				if (($arr_return['AffectedCount'] % 100) == 0 && count($links) > 0){
					$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
					$links = array();
				}
			}
			echo sprintf("call api %s... %s links(s) found. \n", $function, $count);
		}
		if(count($links) > 0)
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link');
		
		
		//The product to add here for the time being          //2017.07.25	by light
		if(SID == 'bdg01'){
		    $links = array();
		    
		    $this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info, 1, false);
		    $arr_merchant = $this->oLinkFeed->getApprovalAffMerchant($this->info["AffId"]);
		    $merchants = array();
		    foreach ($arr_merchant as $key => $v)
		    {
		        $programKey = explode('_', $key);
		        if (isset($merchants[$programKey[0]]))
		            continue;
		        $merchants[$programKey[0]] = $v;
		    }
		    $page = 1;
		    $limit = 50;
		    $HasNextPage = true;
		    while ($HasNextPage)
		    {
		        $url = 'https://www.linkconnector.com/member/merchantfeeds.htm';
		        $request = array(
		            "AffId" => $this->info["AffId"],
		            "method" => "post",
		            "postdata" => array("saveas" => true, "Page" => $page,"s_sort"=>'', "s_order"=>'','ddMerchants'=>'All','ddDisplay'=>$limit)
		        );
		        $r = $this->oLinkFeed->GetHttpResult($url, $request);
		        //print_r($r);exit;
		        if($r['code'] != '200' || !$r['content'])
		        {
		            echo "url request failed : $url !\r\n";
		            continue;
		        }
		        $total_page = intval(trim($this->oLinkFeed->ParseStringBy2Tag($r['content'], '<td class="lcTableFooterRight">', 'Found')));
		        if ($total_page <= $page * $limit)
		            $HasNextPage = false;
		        $LineStart = 0;
		        while (1)
		        {
		            $count = 0;
		            $product_url = trim($this->oLinkFeed->ParseStringBy2Tag($r['content'], '<a href="javascript:windowHandle = window.open(\'', "'", $LineStart));
		            if (!$product_url)
		                break;
		            $mid = trim($this->oLinkFeed->ParseStringBy2Tag($product_url, 'mid=', '&'));
		            $Mname = trim($this->oLinkFeed->ParseStringBy2Tag($product_url, '&co=', ''));
		            /* echo $mid.' '.$Mname."\r\n";
		             if ($mid != '149678')
		                continue; */
		    
		            $product_url = "https://www.linkconnector.com/member/".$product_url;
		            $request = array(
		                "AffId" => $this->info["AffId"],
		                "method" => "get",
		            );
		            $product_r = $this->oLinkFeed->GetHttpResult($product_url, $request);
		            if($product_r['code'] != '200' || !$product_r['content'])
		            {
		                for ($i=0; $i<6; $i++)
		                {
		                    $product_r = $this->oLinkFeed->GetHttpResult($product_url, $request);
		                    if($product_r['code'] == '200')
		                        break;
		                }
		                if ($product_r['code'] != '200')
		                {
		                    echo "url request failed : $product_url !\r\n";
		                    continue;
		                }
		            }
		            $product_r = $product_r['content'];
		            $nLineStart = 0;
		            while (1)
		            {
		                $AffProductId = trim($this->oLinkFeed->ParseStringBy2Tag($product_r, '<td style="vertical-align:middle;" class="lcTable lcTableReport tblCellFirst">', '<', $nLineStart));
		                if (!$AffProductId)
		                    break;
		                $name = trim($this->oLinkFeed->ParseStringBy2Tag($product_r, array('lcTableReport">' ,'lcTableReport">', 'lcTableReport">'), '<', $nLineStart));
		                $desc = trim($this->oLinkFeed->ParseStringBy2Tag($product_r, 'lcTableReport">', '<', $nLineStart));
		                $Price = trim($this->oLinkFeed->ParseStringBy2Tag($product_r, 'lcTableReport">', '<', $nLineStart));
		                $ProductUrl = trim($this->oLinkFeed->ParseStringBy2Tag($product_r, 'lcTableReport">', '<', $nLineStart));
		                $ImageURL = trim($this->oLinkFeed->ParseStringBy2Tag($product_r, array('lcTableReport">', 'lcTableReport">'), '<', $nLineStart));
		                	
		                $link = array(
		                    "AffId" => $this->info["AffId"],
		                    "AffMerchantId" => $merchants[$mid]['AffMerchantId'],
		                    "AffLinkId" => $AffProductId,
		                    "LinkName" => $name,
		                    "LinkDesc" => $desc,
		                    "LinkStartDate" => '0000-00-00',
		                    "LinkEndDate" => '0000-00-00',
		                    "LinkPromoType" => 'PRODUCT',
		                    "LinkHtmlCode" => '',
		                    "LinkOriginalUrl" => '',
		                    "LinkImageUrl" => $ImageURL,
		                    "LinkAffUrl" => $ProductUrl,
		                    "DataSource" => "10",
		                    "Type"       => 'link'
		                );
		                if (!empty($link['LinkImageUrl']))
		                    $link['LinkHtmlCode'] = create_link_htmlcode_image($link);
		                else
		                    $link['LinkHtmlCode'] = create_link_htmlcode($link);
		                if (empty($link['AffLinkId']) || empty($link['LinkHtmlCode']) || empty($link['AffMerchantId']))
		                    continue;
		                $arr_return["AffectedCount"] ++;
		                $count ++;
		                $links[] = $link;
		                if (($arr_return['AffectedCount'] % 100) == 0 && count($links) > 0){
		                    $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		                    $links = array();
		                }
		            }
		            echo sprintf("get products from program ".$merchants[$mid]['AffMerchantId']."... %s links(s) found. \n", $count);
		        }
		        if(count($links) > 0)
		            $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		        $this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link');
		        $page++;
		    }
		}
		
		
		
		
		
		return $arr_return;
	}

	function getInvalidLinks()
	{
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "",);
		$url = 'https://www.linkconnector.com/member/reports.htm?rpt=invalidclick&ddPeriod=Last_3_weeks&s_sort=0&s_order=desc&Display=250';
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info, 1, false);
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r['content'];
		if (preg_match('@<tbody class="lcTable lcTableReport">(.*?)</tbody>@ms', $content, $g))
			$content = $g[1];
		preg_match_all('@<tr id="row\d+" class="lcTable lcTableReport tblRow\d+">(.*?)</tr>@ms', $content, $chapters);
		$links = array();
		foreach ((array)$chapters[1] as $chapter)
		{
			preg_match_all('@<td style="text-align:left;" class="lcTable lcTableReport.*?>(.*?)</td>@ms', $chapter, $g);
			if (empty($g) || empty($g[1]) || !is_array($g[1]) || count($g[1]) != 6)
				continue;
			$link = array(
					'affiliate' => $this->info["AffId"],
					'LinkID' => '',
					'ReferralUrl' => trim($g[1][2]),
					'ProgramName' => trim(html_entity_decode($g[1][4])),
					'OccuredDate' => parse_time_str(trim($g[1][0]), null, false),
					'Reason' => str_force_utf8(trim(html_entity_decode($g[1][1]))),
			);
			if ($link['ReferralUrl'] == 'N/A')
				$link['ReferralUrl'] = '';
			if (preg_match('@a.*?href="(.*?lid=(\d+))@', $g[1][5], $a))
			{
				$link['LinkID'] = $a[2];
				$link['Details'] = str_force_utf8(trim(html_entity_decode(strip_tags($g[1][5]))));
			}
			if (empty($link['LinkID']))
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
		$url = 'https://www.linkconnector.com/member/home.htm';
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r['content'];
		if (preg_match('@<div class="tblHeadTitle">\s+Messages(.*?)<div class="tblHeadTitle">@ms', $content, $g));
		$content = $g[1];
		preg_match_all('@<tr class="lcTable lcTableList">(.*?)</tr>@ms', $content, $chapters);
		foreach ((array)$chapters[1] as $chapter)
		{
			$data = array(
					'affid' => $this->info["AffId"],
					'messageid' => '',
					'sender' => '',
					'title' => '',
					'content' => '',
					'created' => '0000-00-00',
			);
			if (preg_match('@<a href="(.*?msgID=(\d+))".*?>(.*?)</a>@ms', $chapter, $g))
			{
				$data['content_url'] = $g[1];
				$data['title'] = trim(html_entity_decode(strip_tags($g[3])));
				$data['messageid'] = $g[2];
			}
			else
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
		if (preg_match('@<td class="msgDate">(.*?)<@ms', $content, $g))
			$data['created'] = parse_time_str(trim($g[1]), null, false);
		if (preg_match('@<td class="msgText".*?>(.*?)</td>@ms', $content, $g))
			$data['content'] = str_force_utf8(html_entity_decode($g[1]));
		return $data;
	}

	function GetAllLinksFromAffByMerID($merinfo)
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, );
		return $arr_return;
	}

	function getProgramCategory()
	{
		global $mer_cat;
		if(count($mer_cat)) return $mer_cat;
		$categoryUrl = "https://www.linkconnector.com/member/list.htm";
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => "post",
				"postdata" => "",
		);
		$request["postdata"] = "refreshvariable=true";
		$r = $this->oLinkFeed->GetHttpResult($categoryUrl,$request);
		$result = $r["content"];

		$category_arr = array();
		$cate_tmp = $this->oLinkFeed->ParseStringBy2Tag($result ,array('select','ddCategory','>'), '</select>');
		$cate_tmp = preg_replace("/[\\r|\\n|\\r\\n|\\t]/is", '', $cate_tmp);
		$cate_tmp = explode("</option>", $cate_tmp);

		foreach($cate_tmp as $v){
			$cat_id = $this->oLinkFeed->ParseStringBy2Tag($v , '<option value = "', '"');
			if(empty($cat_id)) continue;
			$cat_val = $this->oLinkFeed->ParseStringBy2Tag($v , '>');
			$category_arr[$cat_id] = $cat_val;
		}
		//print_r($category_arr);

		foreach($category_arr as $cat_id => $cat_val){
			$nNumPerPage = 100;
			$bHasNextPage = true;
			$nPageNo = 1;
			if($cat_id == 23) continue;
			$categoryUrl = "https://www.linkconnector.com/member/list.htm";
			while($bHasNextPage)
			{
				$request["postdata"] = "refreshvariable=true&Page=".$nPageNo."&ddCategory=$cat_id&ddDisplay=".$nNumPerPage."&ddDisplay=".$nNumPerPage;
				$r = $this->oLinkFeed->GetHttpResult($categoryUrl,$request);
				$result = $r["content"];

				//parse HTML
				$nLineStart = 0;
				$nTotalPage = $this->oLinkFeed->ParseStringBy2Tag($result, array('per page | Page:','&nbsp;&nbsp;of '), '</td>', $nLineStart);
				if($nTotalPage === false) mydie("die: nTotalPage not found\n");
				$nTotalPage = intval($nTotalPage);
				if($nTotalPage < $nPageNo) break;

				$nLineStart = 0;
				$nTmpNoFound = stripos($result, 'No Records Found', $nLineStart);
				if($nTmpNoFound !== false) break;

				$strLineStart = '<tr class="lcTable lcTableReport tblRow';

				$nLineStart = 0;
				$bStart = true;
				$item_count = 0;
				while ($nLineStart >= 0)
				{
					//print "Process $Cnt  ";
					$nLineStart = stripos($result, $strLineStart, $nLineStart);
					if ($nLineStart === false) break;

					$strMerName = $this->oLinkFeed->ParseStringBy2Tag($result, '<td style="text-align:center;" class="lcTable lcTableReport tblCellFirst">', '&nbsp;', $nLineStart);
					if($strMerName === false) break;
					$strMerName = trim(html_entity_decode($strMerName));

					$mer_cat[$strMerName] = $cat_val;
				}

				$nPageNo++;
				if ($nTotalPage < $nPageNo) break;
			}
		}

		return $mer_cat;
	}

	function getProgramByStatus($status)
	{
		$arr_return = array("AffectedCount" => 0,"UpdatedCount" => 0,);
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => "post",
				"postdata" => "",
		);

		$objProgram = new ProgramDb();
		$arr_prgm = array();

		echo "get $status merchants for LC\n";
		$nNumPerPage = 100;
		$bHasNextPage = true;
		$nPageNo = 1;

		$cnt = 0;

		//$mer_cat = array();
		//$mer_cat = $this->getProgramCategory();
		//print_r($mer_cat);

		$strUrl = "https://www.linkconnector.com/member/amerchants.htm?Type=" . $status;
		while($bHasNextPage)
		{
			/*if($nPageNo == 1)
			{
				$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
				$result = $r["content"];
			}
			else
			{
				$request["postdata"] = "refreshvariable=true&Page=".$nPageNo."&s_sort=&s_order=&ddMerchants=&ddCampaignStatus=Active&ddDisplay=".$nNumPerPage."&ddDisplay=".$nNumPerPage;
				$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
				$result = $r["content"];
			}
			*/
			$request["postdata"] = "refreshvariable=true&Page=".$nPageNo."&s_sort=&s_order=&ddMerchants=&ddCampaignStatus=Active&ddDisplay=".$nNumPerPage."&ddDisplay=".$nNumPerPage;
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$result = $r["content"];

			print "Get $status Merchant List : Page: $nPageNo  <br>\n";

			//parse HTML
			$nLineStart = 0;
			$nTotalPage = $this->oLinkFeed->ParseStringBy2Tag($result, array('per page | Page:','&nbsp;&nbsp;of '), '</td>', $nLineStart);
			if($nTotalPage === false) mydie("die: nTotalPage not found\n");
			$nTotalPage = intval($nTotalPage);
			if($nTotalPage < $nPageNo) break;

			$nLineStart = 0;
			$nTmpNoFound = stripos($result, 'No Records Found', $nLineStart);
			if($nTmpNoFound !== false) break;

			$strLineStart = '<tr class="lcTable lcTableReport tblRow';

			$nLineStart = 0;
			$bStart = true;
			$item_count = 0;
			while ($nLineStart >= 0)
			{
				//print "Process $Cnt  ";
				$nLineStart = stripos($result, $strLineStart, $nLineStart);
				if ($nLineStart === false) break;
				// Merchant Campaign 	Campaign Type 	Events 	7 EPC 	90 EPC 	# Approved Websites 	Actions

				$item_count ++;
				echo "item_count=$item_count","\n";
				//name
				$strMerName = $this->oLinkFeed->ParseStringBy2Tag($result, '<td style="text-align:center;" class="lcTable lcTableReport tblCellFirst">', '</td>', $nLineStart);
				if($strMerName === false) break;
				$strMerName = html_entity_decode(trim($strMerName));

				//category
				$CategoryExt = isset($mer_cat[$strMerName]) ? $mer_cat[$strMerName] : "";

				//ID
				$strCampID = $this->oLinkFeed->ParseStringBy2Tag($result, 'campaigns.htm?cid=', '&mid=', $nLineStart);
				if($strCampID === false) break;
				$strCampID = trim($strCampID);

				$strMerID = $this->oLinkFeed->ParseStringBy2Tag($result, '&mid=', "',", $nLineStart);
				if($strMerID === false) break;
				$strMerID = trim($strMerID);
				if($strMerID == "")
				{
					echo "warning: strMerID not found\n";
					continue;
				}

				$mer_detail_url = "https://www.linkconnector.com/member/campaigns.htm?cid=$strCampID&mid=$strMerID";
				$strMerID = $strMerID.'_'.$strCampID;

				$strCampName = $this->oLinkFeed->ParseStringBy2Tag($result, array('OnMouseOut', '">'), '</a>', $nLineStart);
				if($strCampName === false) break;
				if($strMerName == "")
				{
					echo "warning: strMerName not found\n";
					continue;
				}

				$strCampName = html_entity_decode(trim($strCampName));
				$strMerName = $strMerName . ' - '. $strCampName;

				$strEPC = $strEPC90d = -1;
				$strEvents = "";

				if($status == "Sum")
				{
					$tofind = '<td style="text-align:center;white-space:nowrap" class="lcTable lcTableReport">';
					$strEvents = $this->oLinkFeed->ParseStringBy2Tag($result,$tofind,'</td>', $nLineStart);
					if($strEvents === false)
					{
						echo "warning: strEvents not found\n";
						continue;
					}
					$strEPC = $this->oLinkFeed->ParseStringBy2Tag($result,$tofind, '</td>', $nLineStart);
					if($strEPC === false)
					{
						echo "warning: strEPC not found\n";
						continue;
					}

					$strEPC90d = $this->oLinkFeed->ParseStringBy2Tag($result,$tofind, '</td>', $nLineStart);
					if($strEPC90d === false)
					{
						echo "warning: strEPC30d not found\n";
						continue;
					}

					$strEPC = trim($strEPC);
					$strEPC90d = trim($strEPC90d);
					$this->active_programs[] = $strMerID;
				}

				if($status == "Pending"){
					if (!empty($this->active_programs) && in_array($strMerID, $this->active_programs))
					{
						echo sprintf("program id: %s, name: %s is in active program list and ignore.\n", $strMerID, $strMerName);
						continue;
					}
					$Partnership = 'Pending';
					$StatusInAff = "Active";
				}
				elseif($status == "Declined"){
					$Partnership = 'Declined';
					$StatusInAff = "Active";
				}
				elseif($status == "Dropped"){
					$Partnership = 'NoPartnership';
					$StatusInAff = "Offline";
				}
				elseif($status == "Sum"){
					$Partnership = 'Active';
					$StatusInAff = "Active";
				}
				else{
					mydie("die: wrong status($status)");
				}

				//program
				//commission
				$CommissionExt = trim($strEvents);
				//EPCDefault 7d
				$EPCDefault = $strEPC;
				//EPC90d
				$EPC90d = $strEPC90d;

				//program_detail
				if(!$this->getStatus) {
					$prgm_url = $mer_detail_url;
					$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
					$prgm_detail = $prgm_arr["content"];

					$prgm_line = 0;
					$prgm_campname = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Campaign:', '<td style="font-weight:bold;text-align:left" class="lcTable lcTableForm tblCellLast">'), '</td>', $prgm_line);
					$prgm_camptype = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Campaign Type:', '<td style="font-weight:bold;text-align:left" class="lcTable lcTableForm tblCellLast">'), '</td>', $prgm_line);
					$Homepage = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Website:', '<td style="font-weight:bold;text-align:left" class="lcTable lcTableForm tblCellLast">'), '</td>', $prgm_line);
					$JoinDate = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Start Date:', '<td style="font-weight:bold;text-align:left" class="lcTable lcTableForm tblCellLast">'), '</td>', $prgm_line));
					if ($JoinDate) {
						//$JoinDate = date("Y-m-d H:i:s", strtotime($JoinDate));
						$JoinDate_tmp = $JoinDate;
						$JoinDate = substr($JoinDate_tmp, 6, 4) . "-" . substr($JoinDate_tmp, 0, 2) . "-" . substr($JoinDate_tmp, 3, 2) . " " . "00:00:00";
					}

					//$prgm_end = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail,array('End Date:','<td style="font-weight:bold;text-align:left" class="lcTable lcTableForm tblCellLast">'),'</td>', $prgm_line);
					$prgm_status = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Status:', '<td style="font-weight:bold;text-align:left" class="lcTable lcTableForm tblCellLast">'), '</td>', $prgm_line);

					$desc = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, "<span style='font-weight:bold'>Description: </span>", '</td>', $prgm_line));
					$TermAndCondition = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array("<span style='font-weight:bold'>Campaign Terms and Conditions: </span>", '<table style="margin:8px 0px">'), '</table>', $prgm_line));
					$AllowNonaffPromo ='UNKNOWN';
					$AllowNonaffCoupon ='UNKNOWN';
					if(preg_match('/Coupon Information\:/',$TermAndCondition)){
						if(preg_match('/you will not be able to earn commission as an affiliate unless the discount \/ voucher code has been created/',$TermAndCondition)){
							$AllowNonaffCoupon ='NO';
						}else if(preg_match('/Affiliates shouldn’t post, use or feature any discount\/voucher codes from offline media sources./',$TermAndCondition)){
							$AllowNonaffCoupon = 'NO';
						}else if(preg_match('/Publishers may only use coupons and promotional codes that are provided exclusively through the affiliate program./',$TermAndCondition)){
							$AllowNonaffCoupon = 'NO';
						}else if(preg_match('/Affiliates may not use misleading text on affiliate links	 buttons or images to imply that anything besides currently authorized affiliate deals or savings are available./',$TermAndCondition)){
							$AllowNonaffCoupon = 'NO';
							$AllowNonaffPromo ='NO';
						}else if(preg_match('/Please note commission will not be given for any code offer or promotion that is not available and listed as active in our program./',$TermAndCondition)){
							$AllowNonaffCoupon = 'NO';
							$AllowNonaffPromo ='NO';
						}else if(preg_match('/Any discount promotion of our products by affiliates should be authorized/',$TermAndCondition)){
							$AllowNonaffCoupon = 'NO';
							$AllowNonaffPromo ='NO';
						}else if(preg_match('/The only coupons authorized for use are those that we make directly available to you./',$TermAndCondition)){
							$AllowNonaffCoupon = 'NO';
						}else if(preg_match('/will review each coupon offering before allowing an affiliate to use./',$TermAndCondition)){
							$AllowNonaffCoupon = 'NO';
						}else if(preg_match('/These are the ONLY promotion codes affiliates are authorized to use in their marketing efforts./',$TermAndCondition)){
							$AllowNonaffCoupon = 'NO';
						}else if(preg_match('/Publishers may only use coupons and promotional codes that are provided through communication specifically intended for publishers in the affiliate program./',$TermAndCondition)){
							$AllowNonaffCoupon = 'NO';
						}else if(preg_match('/They need to promote the coupon which we will provide them./',$TermAndCondition)){
							$AllowNonaffCoupon = 'NO';
						}else if(preg_match('/Coupon sites may only post distributed coupons; that is coupons that are given to them or posted within the affiliate interface./',$TermAndCondition)){
							$AllowNonaffCoupon = 'NO';
						}else if(preg_match('/All coupons must be publicly distributed coupons that are given to the affiliate./',$TermAndCondition)){
							$AllowNonaffCoupon = 'NO';
						}else if(preg_match('/Publishers may only use coupons and promotional codes that are provided exclusively through the affiliate program./',$TermAndCondition)){
							$AllowNonaffCoupon = 'NO';
						}else if(preg_match('/Any sites found to be running voucher codes not specifically authorised/',$TermAndCondition)){
							$AllowNonaffCoupon = 'NO';
						}else if(preg_match('/Voucher code sites may not list false voucher codes or voucher codes not associated with the affiliate program/',$TermAndCondition)){
							$AllowNonaffCoupon = 'NO';
						}else if(preg_match('/Affiliates are requested not to use voucher codes/',$TermAndCondition)){
							$AllowNonaffCoupon = 'NO';
						}else if(preg_match('/Affiliates are ONLY allowed to use voucher codes issued to/',$TermAndCondition)){
							$AllowNonaffCoupon = 'NO';
						}else if(preg_match('/Affiliates found to be promoting unauthorised discount codes or those issued through other marketing channels/',$TermAndCondition)){
							$AllowNonaffCoupon = 'NO';
						}else if(preg_match('/Affiliates should not display voucher\/discount codes that have been provided for use by other marketing channels./',$TermAndCondition)){
							$AllowNonaffCoupon = 'NO';
						}else if(preg_match('/Affiliates are not to promote any voucher codes that have not been provided/',$TermAndCondition)){
							$AllowNonaffCoupon = 'NO';
						}else if(preg_match('/affiliates can only use the voucher codes supplied/',$TermAndCondition)){
							$AllowNonaffCoupon = 'NO';
						}else if(preg_match('/It is not allowed to promote vouchers that have not been communicated via the affiliate channel/',$TermAndCondition)){
							$AllowNonaffCoupon = 'NO';
						}else if(preg_match('/Affiliates shouldn’t post, use or feature any discount\/voucher codes from offline media sources/',$TermAndCondition)){
							$AllowNonaffCoupon = 'NO';
						}else if(preg_match('/affiliates may only promote voucher codes/',$TermAndCondition)){
							$AllowNonaffCoupon = 'NO';
						}else if(preg_match('/affiliates can only use the voucher codes supplied/',$TermAndCondition)){
							$AllowNonaffCoupon = 'NO';
						}else if(preg_match('/Voucher sites must only promote codes that have been designated for affiliate use/',$TermAndCondition)){
							$AllowNonaffCoupon = 'NO';
						}else if(preg_match('/publishers on the (.)+affiliate program should only use and monetise voucher codes (.)+ This includes user generated content, this cannot be monetised without the relevant permissions./',$TermAndCondition)){
							$AllowNonaffCoupon = 'NO';
						}
					}
					$ReturnDays = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Expire Tracking', "<div style='border:none;'>"), '</div>', $prgm_line);

					$SEMPolicyExt = "";
					$sem_tmp = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Search Engine Marketing Allowed:', '<td style="vertical-align:top;">'), '</td>');
					if ($sem_tmp) {
						$SEMPolicyExt = "Search Engine Marketing Allowed:" . $sem_tmp;
					}
					$sem_tmp = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Search Engine Marketing Restrictions:', '<td style="vertical-align:top;">'), '</td>');
					if ($sem_tmp) {
						$SEMPolicyExt .= "Search Engine Marketing Restrictions:" . $sem_tmp;
					}
					if (stripos($prgm_detail, 'International Traffic Allowed') !== false || stripos($prgm_detail, 'International Traffic Welcome') !== false)
						$TargetCountryExt = 'Global';
					else 
						$TargetCountryExt = '';

					$TermAndCondition = preg_replace("/[\\r|\\n|\\r\\n|\\t]/is", '', $TermAndCondition);
					$TermAndCondition = explode("</tr><tr>", $TermAndCondition);

					foreach ($TermAndCondition as $k => $v) {
						if (stripos($v, "Search Engine Marketing Allowed") || stripos($v, "Search Engine Marketing Restrictions")) {
							unset($TermAndCondition[$k]);
						}
						if ($v == '<td style="vertical-align:top;"></td>') {
							unset($TermAndCondition[$k]);
						}
					}
					$TermAndCondition = "<table>" . implode("</tr><tr>", $TermAndCondition) . "</table>";
				}
				if(!empty($AllowNonaffCoupon)){
					if($this->getStatus){
						$arr_prgm[$strMerID] = array(
								"Name" => addslashes(html_entity_decode(trim($strMerName))),
								"AffId" => $this->info["AffId"],
								"IdInAff" => $strMerID,
								"StatusInAff" => $StatusInAff,						//'Active','TempOffline','Offline'
								"StatusInAffRemark" => addslashes($status),
								"Partnership" => $Partnership,
								"AllowNonaffCoupon"=>$AllowNonaffCoupon,
								"AllowNonaffPromo"=>$AllowNonaffPromo,
								"TargetCountryExt"=>$TargetCountryExt,

						);
					} else {
						$arr_prgm[$strMerID] = array(
								"Name" => addslashes(html_entity_decode(trim($strMerName))),
								"AffId" => $this->info["AffId"],
								"IdInAff" => $strMerID,
								"StatusInAff" => $StatusInAff,                        //'Active','TempOffline','Offline'
								"StatusInAffRemark" => addslashes($status),
								"Partnership" => $Partnership,                        //'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
								"JoinDate" => $JoinDate,
								"CategoryExt" => $CategoryExt,
							//"CreateDate" => $prgm_start,
							//"DropDate" => $prgm_end,
								"SEMPolicyExt" => addslashes($SEMPolicyExt),
								"Description" => addslashes($desc),
								"AllowNonaffCoupon"=>$AllowNonaffCoupon,
								"AllowNonaffPromo"=>$AllowNonaffPromo,
								"Homepage" => addslashes($Homepage),
								"CommissionExt" => addslashes($CommissionExt),
								"EPCDefault" => addslashes(preg_replace("/[^0-9.]/", "", $EPCDefault)),
								"EPC90d" => addslashes(preg_replace("/[^0-9.]/", "", $EPC90d)),
								"CookieTime" => $ReturnDays,
								"TermAndCondition" => addslashes($TermAndCondition),
								"LastUpdateTime" => date("Y-m-d H:i:s"),
								"DetailPage" => $prgm_url,
								"TargetCountryExt"=> $TargetCountryExt,
						);
					}
				}

				$cnt++;
				if(count($arr_prgm) >= 200){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
			}
			if(count($arr_prgm)){
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
				$arr_prgm = array();
			}

			$nPageNo++;
			if ($nTotalPage < $nPageNo) break;
		}//per page
		//exit;
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
			$arr_prgm = array();
		}
		//$objProgram->setProgramOffline($this->info["AffId"]);
		$objProgram->setCountryInt($this->info["AffId"]);

		return $cnt;
	}

	function GetProgramByPage()
	{
		echo "\tGet Program by page start\r\n";
		$program_num = 0;
		//step 1,login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info,1,false);

		// some program in pending lists, but also in active lists, save the list of active program, and ignore when in pending list
		$this->active_programs = array();

		//step 2,get all exists merchant
		//$arrAllExistsMerchants = $this->oLinkFeed->GetAllExistsAffMerIDForCheckByAffID($this->info["AffId"]);

		//$arrStatus4List = array("Sum","Pending","Declined","Dropped");
		//ike 20101127, their Declined page is wrong? contains many dup merchants??
		$arrStatus4List = array("Sum","Pending");
		foreach($arrStatus4List as $status)
		{
			$program_num += $this->getProgramByStatus($status);
		}

		echo "\tGet Program by page end\r\n";

		if($program_num < 10){
			mydie("die: program count < 10, please check program.\n");
		}

		echo "\tUpdate ({$program_num}) program.\r\n";
	}

	function GetStatus(){
		$this->getStatus = true;
		$this->GetProgramFromAff();
	}

    function GetTransactionFromAff($start_date, $end_date)
    {
        echo "Craw Transaction from $start_date to $end_date start @ " . date('Y-m-d H:i:s') . "\r\n";

        $request = array("AffId" => $this->info["AffId"], "method" => 'post');

		$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"], "data_" . date("YmdH") . "_Transaction.csv", 'Transaction', true);
		if (!$this->oLinkFeed->fileCacheIsCached($cache_file)) {
			$fw = fopen($cache_file, 'w');
			if (!$fw) {
				throw new Exception("File open failed {$cache_file}");
			}
			$url = "http://www.linkconnector.com/api/";
			echo "req => {$url} \n";
			$request['file'] = $fw;
			$request['postdata'] = array('Key' => $this->API_KEY, 'Function' => 'getReportTransaction', 'StartDate' => $start_date, 'EndDate' => $end_date);
			$result = $this->oLinkFeed->GetHttpResult($url, $request);
			if ($result['code'] != 200) {
				mydie("Download data file failed.");
			}
			fclose($fw);
		}

		$fp = fopen($cache_file, 'r');
		if (!$fp) {
			throw new Exception("File open failed {$cache_file}");
		}

        $fws = $comms = array();
        $comm_all = 0;
        $k = 0;
        while (!feof($fp)) {
            $lr = fgetcsv($fp, 0, ',', '"');
            if (++$k == 1 || $lr[0] == '') {
                continue;
            }
            $sid = trim($lr[13]);
            $oid = $lr[14];
            $mid = $lr[4];
            $mname = $lr[3];
            $tid = $oid;
            $status = trim($lr[16]);

            if (strtolower($status) == 'invalidated') {
                $sale = $rev = 0;
            } else {
                $sale = str_replace(array(',', '$'), '', $lr[15]);
                $rev = str_replace(array(',', '$'), '', $lr[11]);
            }

            $event_dt = $lr[0];
            $process_dt = $lr[1] == '' ? $event_dt : $lr[1];
            $rev_file = AFF_TRANSACTION_DATA_PATH . '/revenue_' . date('Ymd', strtotime($event_dt)) . '.upd';
            if (!isset($fws[$rev_file])) {
                $fws[$rev_file] = fopen($rev_file, 'w');
                $comms[$rev_file] = 0;
            }
            $cancelreason = trim($lr[17]);

            $replace_array = array(
                '{createtime}'      => $event_dt,
                '{updatetime}'      => $process_dt,
                '{sales}'           => $sale,
                '{commission}'      => $rev,
                '{idinaff}'         => $mid,
                '{programname}'     => $mname,
                '{sid}'             => $sid,
                '{orderid}'         => $oid,
                '{clicktime}'       => $lr[2],
                '{tradeid}'         => $tid,
                '{tradestatus}'     => $status,
                '{oldcur}'          => 'USD',
                '{oldsales}'        => $sale,
                '{oldcommission}'   => $rev,
                '{tradetype}'       => '',
                '{referrer}'        => '',
                '{cancelreason}'    => $cancelreason,
            );

            fwrite($fws[$rev_file], strtr(FILE_FORMAT,$replace_array) . "\n");
            $comms[$rev_file] += $rev;
            $comm_all += $rev;
        }
        fclose($fp);

        foreach ($fws as $file => $f) {
            fclose($f);
        }

        echo "Craw Transaction end @ " . date("Y-m-d H:i:s") . "\r\n";
    }

    function GetProgramFromAff()
	{
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
		$s = 'All coupons must be publicly distributed coupons that are given to the affiliate';

		$this->GetProgramByPage();
		$this->checkProgramOffline($this->info["AffId"], $check_date);

		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
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
	
	function fileCacheGetFilePath($aff_id, $file_name, $group_name, $use_true_file_name = false)
	{
	    if(!$use_true_file_name) $file_name .= "." . date("YmdH") . ".cache";
	    $working_dir = $this->getWorkingDirByAffID($aff_id, $group_name);
	    return $working_dir . $file_name;
	}
	
	function getWorkingDirByAffID($aff_id, $group_name = "")
	{
	    if (isset($this->workingdirs[$aff_id][$group_name])) {
	        return $this->workingdirs[$aff_id][$group_name];
	    }
	
	    $is_mkdir = false;
	
	    $dir =   $this->productPicDir;            //创建data文件夹
	    if (!is_dir($dir)) {
	        $is_mkdir = true;
	        mkdir($dir);
	        chmod($dir, 0777);
	    }
	    $dir .= $aff_id . "/";                                    //在data文件夹下，创建LinkFeed_10_AW等文件夹
	    if (!is_dir($dir)) {
	        $is_mkdir = true;
	        mkdir($dir);
	        chmod($dir, 0777);
	    }
	
	    if ($group_name) {
	        $dir .= $group_name . "/";
	        if (!is_dir($dir)) {
	            $is_mkdir = true;
	            mkdir($dir);
	            chmod($dir, 0777);
	        }
	    }
	    if ($is_mkdir && !is_dir($dir)) mydie("make Working Dir failed: $dir\n");
	
	    $this->workingdirs[$aff_id][$group_name] = $dir;
	    return $dir;
	}
	
	function uncompress_gz($gzFile){
	    $file_name = $gzFile;
	    
	    // Raising this value may increase performance
	    $buffer_size = 4096; // read 4kb at a time
	    $out_file_name = str_replace('.gz', '', $file_name);
	    
	    // Open our files (in binary mode)
	    $file = gzopen($file_name, 'rb');
	    $out_file = fopen($out_file_name, 'wb');
	    
	    // Keep repeating until the end of the input file
	    while(!gzeof($file)) {
	        // Read buffer-size bytes
	        // Both fwrite and gzread and binary-safe
	        fwrite($out_file, gzread($file, $buffer_size));
	    }
	    
	    // Files are done, close files
	    fclose($out_file);
	    gzclose($file);
	}
}
?>
