<?php

require_once 'text_parse_helper.php';
require_once 'xml2array.php';

class LinkFeed_46_ClixGalore
{
	var $info = array(
		"ID" => "46",
		"Name" => "clixGalore",
		"IsActive" => "YES",
		"ClassName" => "LinkFeed_46_ClixGalore",
		"LastCheckDate" => "1970-01-01",
	);
	
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
        $this->getStatus = false;
        
        if(SID == 'bdg02'){
        	$this->AfID = 284865;
        	$this->CID = 238939;
        }else{
        	$this->AfID = 284887;
        	$this->CID = 238963;
        }
	}
		
	function Login()
	{
		$request = array("method" => "get", "postdata" => "", );

		$r = $this->oLinkFeed->GetHttpResult($this->info['AffLoginUrl'],$request);
		$result = $r["content"];
		$__EVENTVALIDATION = urlencode($this->oLinkFeed->ParseStringBy2Tag($result, array('name="__EVENTVALIDATION"', 'value="'), '"'));
		$__VIEWSTATE = urlencode($this->oLinkFeed->ParseStringBy2Tag($result, array('name="__VIEWSTATE"', 'value="'), '"'));
		$this->info["AffLoginPostString"] = "__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE={$__VIEWSTATE}&__EVENTVALIDATION={$__EVENTVALIDATION}&txt_UserName=".urlencode($this->info["Account"])."&txt_Password=".urlencode($this->info["Password"])."&cmd_login.x=53&cmd_login.y=12";
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
	}

	private function csv_string_to_array_46($content)
	{
		$r = array();
		$delimiter = ",";
		$line_delimiter = "\n";
		$lines = explode($line_delimiter, $content);
		if (empty($lines) || !is_array($lines))
			return $r;
		for($i = 0; $i < count($lines); $i ++)
		{
			if ($i == 0)
				continue;
			$line = str_force_utf8($lines[$i]);
			$fields = mem_getcsv($line, ',', '"', '"');
			if (empty($fields) || !is_array($fields) || count($fields) < 1)
				continue;
			$r[] = $fields;
		}
		return $r;
	}
	
	function GetAllProductsByAffId()
	{
	     
	    $check_date = date('Y-m-d H:i:s');
	    $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
	    $request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "");
	    
	    $this->Login();
	    
	    //下载program对应的product数量
	    $url = "https://www.clixgalore.com/AffiliateDataFileMerchants_Export.aspx?AfID=284865&CID=238939&MN=&LR=0&type=csv";
	    $str = $this->oLinkFeed->GetHttpResult($url, $request);
	    $data = $this->csv_string_to_array_46($str['content']);
	     
	    $xml = new XML2Array();
	    foreach ($data as $value){
	        if($value[4]<1000){ //取小于1000数量的
	            $url = $value[5];
	            echo $url.PHP_EOL;
	            $arr = $this->oLinkFeed->GetHttpResult($url, $request);
	            
		        $result = $xml->createArray($arr['content']);
				if($result && isset($result['Catalogue_Items']['Product'])){
		            foreach ($result['Catalogue_Items']['Product'] as $product){
		                
		                $finalUrl = $product['Product_URL'];
		                $productId =  md5($finalUrl);
		                //下载图片
		                $product_path_file = $this->oLinkFeed->getProductImgFilePath($this->info["AffId"],"{$value[0]}_".urlencode($productId).".png", PRODUCTDIR);
		                if(!$this->oLinkFeed->fileCacheIsCached($product_path_file)){
		                    $file_content = $this->oLinkFeed->downloadImg($product['Product_Image_URL']);
		                    if(!$file_content) //下载不了跳过。
		                        continue;
		                    $this->oLinkFeed->fileCachePut($product_path_file, $file_content);
		                }
		                 
		                $link = array(
		                    "AffId" => $this->info["AffId"],
		                    "AffMerchantId" => $value[0],
		                    "AffProductId" => $productId,
		                    "ProductName" => addslashes($product['Product_Name']),
		                    "ProductCurrency" =>$product['Product_Currency'],
		                    "ProductPrice" =>$product['Product_Price'],
		                    "ProductOriginalPrice" =>'',
		                    "ProductRetailPrice" =>'',
		                    "ProductImage" => addslashes($product['Product_Image_URL']),
		                    "ProductLocalImage" => addslashes($product_path_file),
		                    "ProductUrl" => addslashes($product['Product_Tracking_URL']),
		                    "ProductDestUrl" => $finalUrl,
		                    "ProductDesc" => addslashes($product['Product_Desc']),
		                    "ProductStartDate" => '',
		                    "ProductEndDate" => '',
		                );
		                $links[] = $link;
		                $arr_return['AffectedCount'] ++;
		                if (count($links > 100))
		                {
		                    $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateProductToDB($links);
		                    $links = array();
		                }
		            }
		            if (count($links))
		            {
		                $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateProductToDB($links);
		                $links = array();
		            }
		        }
		        
	        }
	    }
	    
	    $this->oLinkFeed->checkProductExists($this->info["AffId"], $check_date);
	    //echo "\r\nprogram num:".$programNum."\r\n";
	    return $arr_return;
	    
	}
	
	function getCouponFeed()
	{
	    $check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0,"UpdatedCount" => 0,"Detail" => array());
		$request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "");

		//coupon		
		$url = "http://www.clixgalore.com/AffiliateSearchCoupons_Export.aspx?PT=1,2,3,4&C=&K=&CID=0&R=0&AfID=" . $this->AfID . "&ID=" . $this->CID . "&JO=0&type=csv";
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r['content'];
		$data = $this->csv_string_to_array_46($content);
		$links = array();
		if (is_array($data)){
			foreach ($data as $v){
				if (count($v) < 13)
					continue;
				$link = array(
						"AffId" => $this->info["AffId"],
						"LinkName" => $v[4],
						"LinkDesc" => $v[7],
						"LinkStartDate" => parse_time_str($v[5], null, false),
						"LinkEndDate" => parse_time_str($v[6], null, true),
						"LinkPromoType" => 'COUPON',
						"LinkHtmlCode" => $v[11],
						"LinkCode" => $v[3],
						"LinkOriginalUrl" => '',
						"LinkImageUrl" => $v[9],
						"DataSource" => 75,
				        "Type"       => 'promotion'
				);
				if (empty($link['LinkHtmlCode']))
					continue;
				$link['LinkHtmlCode'] = $link['LinkHtmlCode'];
				if (preg_match('@<a href="(.*?)"@', $link['LinkHtmlCode'], $g))
					$link['LinkAffUrl']	= $g[1];
				if (preg_match('@AdID=(\d+)\&@', $link['LinkHtmlCode'], $g))
					$link['AffMerchantId'] = $g['1'];
				if (empty($link['LinkCode'])){
					$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
				}
				$link['AffLinkId'] = md5(sprintf('%s_%s_%s_%s', $link['AffMerchantId'], $link['LinkName'], $link['LinkCode'], $link['LinkStartDate']));
				if (empty($link['AffMerchantId']) || empty($link['AffLinkId'])  || empty($link['LinkHtmlCode']))
					continue;
                elseif(empty($link['LinkName'])){
                    $link['LinkPromoType'] = 'link';
                }
				$links[] = $link;
				$arr_return["AffectedCount"] ++;
			}
		}
		echo sprintf("%s coupon(s) found. \n", count($links));
		if (count($links) > 0)
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'promotion');
/*
		//text & banner
		$url = "http://www.clixGalore.com/AffiliateViewLinkCode_Export.aspx?AfID=284887&CID=179533&BT=0&MI=2&H=&W=&S=&type=csv";
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r['content'];
		$data = $this->csv_string_to_array_46($content);
		$links = array();
		if (is_array($data))
		{
			$links = array();
			foreach ($data as $v)
			{
				if (count($v) < 11)
					continue;
				$link = array(
						"AffId" => $this->info["AffId"],
						"LinkName" => $v[2],
						"LinkDesc" => '',
						"LinkStartDate" => parse_time_str($v[0], null, false),
						"LinkEndDate" => '0000-00-00',
						"LinkPromoType" => 'DEAL',
						"LinkHtmlCode" => $v[10],
						"LinkOriginalUrl" => '',
						"DataSource" => 75,
				);
				if (empty($link['LinkHtmlCode']))
					continue;
				if (preg_match('@<a href="(.*?)"@i', $link['LinkHtmlCode'], $g))
					$link['LinkAffUrl']	= $g[1];
				if (preg_match('@AdID=(\d+)\&@', $link['LinkHtmlCode'], $g))
					$link['AffMerchantId'] = $g[1];
				if (preg_match('@BID=(\d+)\&@', $link['LinkHtmlCode'], $g))
					$link['AffLinkId'] = $g[1];
				if (empty($link['AffMerchantId']) || empty($link['AffLinkId']) || empty($link['LinkName']) || empty($link['LinkHtmlCode']))
					continue;
				$links[] = $link;
				$arr_return["AffectedCount"] ++;
			}
		}
		echo sprintf("%s links(s) found. \n", count($links));
		if (count($links) > 0)
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
*/
		return $arr_return;
	}

	function GetAllLinksByAffId()
	{
	    $check_date = date('Y-m-d H:i:s');
		$aff_id = $this->info["AffId"];
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, );

        $this->Login();
        
        $links = array();
//        $csvurl = "http://www.clixGalore.com/AffiliateViewLinkCode_Export.aspx?AfID=221993&CID=179533&BT=0&MI=1&H=&W=&S=&type=cs";
        $csvurl = "http://www.clixgalore.com/AffiliateViewLinkCode_Export.aspx?AfID=" . $this->AfID . "&CID=" . $this->CID . "&BT=0&MI=1&H=&W=&S=&type=xml";
        $request = array("AffId" => $this->info["AffId"], "method" => "get");
        $arr = $this->oLinkFeed->GetHttpResult($csvurl,$request);

//        var_dump($arr['content']);die;
       // $arr = csv_string_to_array($arr['content']);
        
        $xml = new XML2Array();
		$result = $xml->createArray($arr['content']);
        
        //print_r(array_keys($result['DocumentElement']));die;
		foreach($result['DocumentElement']['ReportData'] as $data){
           // print_r($data);exit;
            preg_match('@(href="|clickTAG=)(.*?)"@',$data['Tracking_Code'],$tmp_link);
            preg_match('@<image src="(.*?)"@',$data['Tracking_Code'],$imagelink);
            preg_match('@AdID=(\d+)@',$tmp_link[2],$AffMerchantId);

                       
            if(empty($tmp_link[2])) continue;           
            if(empty($AffMerchantId[1])) continue;
           
            $link = array(
                'AffId' => $this->info['AffId'],
                'AffMerchantId' => $AffMerchantId[1],
                'AffLinkId' => md5($data['Tracking_Code']),
                'LinkName' => $data['Banner_Name'],
                'LinkDesc' => '',
                'LinkStartDate' => '0000-00-00 00:00:00',
                'LinkEndDate' => '0000-00-00 00:00:00',
                'LinkPromoType' => 'link',
                'LinkHtmlCode' => $data['Tracking_Code'],
                'LinkCode' => '',
                'LinkOriginalUrl' => '',
                'LinkImageUrl' => @$imagelink[1],
                'LinkAffUrl' => @$tmp_link[2],
                'DataSource' => 75,
                "Type"       => 'link'
            );
           
            $links[] = $link;
			$arr_return["AffectedCount"] ++;
        }
        echo sprintf("%s link(s) found. \n", count($links));
		if (count($links) > 0)
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link');
			
		return $arr_return;
	}
	
	function GetProgramFromAff()
	{
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";

		$this->GetProgramFromByPage();
		$this->checkProgramOffline($this->info["AffId"], $check_date);

		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}

    function GetStatus(){
        $this->getStatus = true;
        $this->GetProgramFromAff();
    }

	function GetProgramFromByPage()
	{
		echo "\tGet Program by page start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;

		//step 1,login
		$this->Login();

		$request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "",);
		$tmp_request = array("AffId" => $this->info["AffId"], "method" => "get");
		$prgm_records = array();

		//"http://www.clixGalore.com/AffiliateViewJoinRequests_Export.aspx?AfID=284887&CID=179533&RS=10&BT=0&MS=0&EF=csv";//active
		//"http://www.clixGalore.com/AffiliateViewJoinRequests_Export.aspx?AfID=284887&CID=179533&RS=10&BT=0&MS=2&EF=csv";//low 
		//"http://www.clixGalore.com/AffiliateViewJoinRequests_Export.aspx?AfID=284887&CID=179533&RS=10&BT=0&MS=1&EF=csv";//inactive

		//program management adv
		echo "get program \r\n";
		$dd_filter_arr = array( 0 => 'Active',1 => 'Offline', 2 => 'TempOffline');
		$__EVENTTARGET = '';
		foreach($dd_filter_arr as $dd_filter => $Status){
			$strUrl = "http://www.clixgalore.com/AffiliateViewJoinRequests.aspx";
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$result = $r["content"];
			$hasNextPage = true;
			$page = 1;
			$arr_prgm = array();
			while($hasNextPage){
				echo "\t page $page.";
				if(!empty($result)){
					if($page == 1){
						$__EVENTVALIDATION = urlencode($this->oLinkFeed->ParseStringBy2Tag($result, array('name="__EVENTVALIDATION"', 'value="'), '"'));
						$__VIEWSTATE = urlencode($this->oLinkFeed->ParseStringBy2Tag($result, array('name="__VIEWSTATE"', 'value="'), '"'));
						$request["postdata"] = '__VIEWSTATE='.$__VIEWSTATE.'&__EVENTVALIDATION='.$__EVENTVALIDATION.'&dd_RequestStatus=10&AffProgramDropDown1%24aff_program_list=0&dd_BannerType=0&dd_filter='.$dd_filter.'&cmd_report=Retrieve+Details';
					}else{
						$__EVENTVALIDATION = urlencode($this->oLinkFeed->ParseStringBy2Tag($result, array('name="__EVENTVALIDATION"', 'value="'), '"'));
						$__VIEWSTATE = urlencode($this->oLinkFeed->ParseStringBy2Tag($result, array('name="__VIEWSTATE"', 'value="'), '"'));
						$request["postdata"] = '__EVENTTARGET='.$__EVENTTARGET.'&__EVENTARGUMENT=&__VIEWSTATE='.$__VIEWSTATE.'&__EVENTVALIDATION='.$__EVENTVALIDATION.'&dd_RequestStatus=10&AffProgramDropDown1%24aff_program_list=0&dd_BannerType=0&dd_filter='.$dd_filter.'&txt_advsearch=';
					}
				}else{
					mydie("die: postdata error.\n");
				}

				$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
				$result = $r["content"];
				$tmp_target = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('Pages Found:', "<span>$page</span>", '__doPostBack(\'dg_Merchants$ctl24$ctl'), "'"));
				if($tmp_target == false) $hasNextPage = false;
				$__EVENTTARGET = urlencode('dg_Merchants$ctl24$ctl'.$tmp_target);

				$strLineStart = 'class="StdLink" title="View Merchant Details"';

				$nLineStart = 0;
				while ($nLineStart >= 0){
					$nLineStart = stripos($result, $strLineStart, $nLineStart);
					if ($nLineStart === false) break;
					//class
					$StatusInAff = $Status;
					if($Status == 'Active'){
						$tmp_start = $nLineStart - 170;
						$class = $this->oLinkFeed->ParseStringBy2Tag($result, '<tr class="', '"', $tmp_start);
						if($class == 'lowbalanceMediumWarning'){//lowbalanceHighWarning
							$StatusInAff = 'TempOffline';
						}
					}

					//id
					$strMerID = intval($this->oLinkFeed->ParseStringBy2Tag($result, "OpenDetails(", ")", $nLineStart));
					if (!$strMerID) break;

					if(isset($prgm_records[$strMerID])) continue;
					//name
					$strMerName = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, '>' , "</a>", $nLineStart)));
					if ($strMerName === false) break;

					$StatusInAffRemark = $this->oLinkFeed->ParseStringBy2Tag($result, array('<td align="center">','<td align="center">','<td align="center">','<td align="center">'), "</td>", $nLineStart);
					if(stripos($StatusInAffRemark, 'Approved') != false){
						$Partnership = 'Active';
					}elseif(stripos($StatusInAffRemark, 'Pending') != false){
						$Partnership = 'Pending';
					}elseif(stripos($StatusInAffRemark, 'Declined') != false){
						$Partnership = 'Declined';
					}else{
						mydie("die: unknown $strMerName partnership: $StatusInAffRemark.\n");
					}
					if($StatusInAff == 'TempOffline'){
						$StatusInAffRemark = 'Low Balance';
					}elseif ($StatusInAff == 'Offline'){
						$StatusInAffRemark = 'Inactive';
					}
					$arr_prgm[$strMerID] = array(
						"Name" => addslashes(html_entity_decode(trim($strMerName))),
						"AffId" => $this->info["AffId"],
						"IdInAff" => $strMerID,
						"StatusInAff" => $StatusInAff,						//'Active','TempOffline','Offline'
						"StatusInAffRemark" => $StatusInAffRemark,
						"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
						"LastUpdateTime" => date("Y-m-d H:i:s"),
						"SupportDeepUrl" => 'YES'
					);
					$prgm_records[$strMerID] = 1;
					$program_num++;
					//print_r($arr_prgm[$strMerID]);
					if(count($arr_prgm) >= 100){
						$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
						$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
						$arr_prgm = array();
					}
				}
				$page++;
				if($page > 1000){
					mydie("die: Page overload.\n");
				}
			}
			if(count($arr_prgm)){
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
				unset($arr_prgm);
			}
		}
		//program management adv
        if(!$this->getStatus) {
            echo "get program from program management adv\r\n";
            $strUrl = "http://www.clixgalore.com/AffiliateNotificationReport.aspx";
            $result = "";
            $request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "",);
            $hasNextPage = true;
            $page = 1;
            while ($hasNextPage) {
                echo "\t page $page.";
                if (!empty($result)) {
                    //$__EVENTTARGET = urlencode('dg_Merchants$ctl44$ctl01');
                    $__EVENTVALIDATION = urlencode($this->oLinkFeed->ParseStringBy2Tag($result, array('name="__EVENTVALIDATION"', 'value="'), '"'));
                    $__VIEWSTATE = urlencode($this->oLinkFeed->ParseStringBy2Tag($result, array('name="__VIEWSTATE"', 'value="'), '"'));
                    $request["method"] = "post";
                    $request["postdata"] = '__EVENTTARGET=' . $__EVENTTARGET . '&__EVENTARGUMENT=&__LASTFOCUS=&__VIEWSTATE=' . $__VIEWSTATE . '&__EVENTVALIDATION=' . $__EVENTVALIDATION . '&AffProgramDropDown1%24aff_program_list=' . $this->AfID . '&txt_advsearch=';
                } elseif ($page != 1) {
                    mydie("die: postdata error.\n");
                }
                $r = $this->oLinkFeed->GetHttpResult($strUrl, $request);
                $result = $r["content"];
                $tmp_target = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('Pages Found:', "<span>$page</span>", '__doPostBack(\'dg_Merchants$ctl44$ctl'), "'"));
                if ($tmp_target == false) $hasNextPage = false;
                $__EVENTTARGET = urlencode('dg_Merchants$ctl44$ctl' . $tmp_target);

                //$strLineStart = '<tr class="ColumnHeading">';
                $strLineStart = 'class="StdLink" title="View Merchant Details"';
                $nLineStart = 0;
                $nLineStart = stripos($result, $strLineStart, $nLineStart);
                //$strLineStart = '<tr';
                while ($nLineStart >= 0) {
                    $nLineStart = stripos($result, $strLineStart, $nLineStart);
                    if ($nLineStart === false) break;
                    //class
                    /*$StatusInAff = 'Active';
                    $class = intval($this->oLinkFeed->ParseStringBy2Tag($result, '<tr class="', '"', $nLineStart));
                    if($class == 'lowbalanceMediumWarning'){//lowbalanceHighWarning
                        $StatusInAff = 'TempOffline';
                    }elseif($class == 'lowbalanceHighWarning'){
                        $StatusInAff = 'Offline';
                    }elseif($class != 'celldetail' && $class != 'Alternatecelldetail'){
                        break;
                    }*/
                    //id
                    $strMerID = intval($this->oLinkFeed->ParseStringBy2Tag($result, "OpenDetails(", ")", $nLineStart));
                    if (!$strMerID) break;
                    //if(isset($prgm_records[$strMerID])) continue;
                    //name
                    $strMerName = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, '>', "</a>", $nLineStart)));
                    if ($strMerName === false) break;

                    $tmpStr = trim($this->oLinkFeed->ParseStringBy2Tag($result, 'NAME="Label1">', "</span>", $nLineStart));
                    $TargetCountryExt = substr($tmpStr, 0, 2);
                    $EPC30d = substr($tmpStr, 2);

                    $TermAndCondition = "";
                    $tmpStr = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('javascript:TermsCondition', $strMerID, '>'), "</a>", $nLineStart));
                    if ($tmpStr == 'View T&C') {
                        $tc_url = "http://www.clixgalore.com/popup_ViewMerchantTC.aspx?ID=$strMerID";
                        $tc_arr = $this->oLinkFeed->GetHttpResult($tc_url, $tmp_request);
                        $tc_detail = $tc_arr["content"];
                        $TermAndCondition = trim($this->oLinkFeed->ParseStringBy2Tag($tc_detail, array('<textarea name="txt_tc"', '>'), "</textarea>"));
                    }

                    $arr_prgm[$strMerID] = array(
                        "Name" => addslashes(html_entity_decode(trim($strMerName))),
                        "AffId" => $this->info["AffId"],
                        "TargetCountryExt" => $TargetCountryExt,
                        "IdInAff" => $strMerID,
                        //"StatusInAff" => 'Active',						//'Active','TempOffline','Offline'
                        //"StatusInAffRemark" => '',
                        //"Partnership" => 'Active',						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
                        //"Description" => addslashes($desc),
                        //"Homepage" => $Homepage,
                        //"CommissionExt" => addslashes($CommissionExt),
                        "EPC30d" => $EPC30d,
                        //"CookieTime" => $CookieTime,
                        "TermAndCondition" => addslashes($TermAndCondition),
                        //"SubAffPolicyExt" => addslashes($SubAffPolicyExt),
                        "LastUpdateTime" => date("Y-m-d H:i:s"),
                        "SupportDeepUrl" => 'YES'
                    );
                    $prgm_records[$strMerID] = 1;
                    $program_num++;
                    if (count($arr_prgm) >= 100) {
                        $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
                        $this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
                        $arr_prgm = array();
                    }
                }
                $page++;
                if ($page > 100) {
                    mydie("die: Page overload.\n");
                }
            }

            if (count($arr_prgm)) {
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
                $this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
                unset($arr_prgm);
            }
            echo "\tGet Program by page end\r\n";
            if ($program_num < 10) {
                mydie("die: program count < 10, please check program.\n");
            }

            $this->getProgramDetail();
        }
		echo "\tUpdate ({$program_num}) program.\r\n";
		echo "\tSet program country int.\r\n";

        $this->getProgramCategory();
		
		$objProgram->setCountryInt($this->info["AffId"]);
	}
	
	function getProgramDetail(){
		echo "\tGet Program detail start @ ".date("Y-m-d H:i:s")."\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$tmp_request = array("AffId" => $this->info["AffId"], "method" => "get");
		
		$this->Login();
		
		$sql = "select idinaff from program where affid = {$this->info["AffId"]} and statusinaff = 'active' and partnership = 'active'";
		$idinaff_arr = $objProgram->objMysql->getRows($sql);
		
		foreach($idinaff_arr as $v){
			$prgm_url = "http://www.clixgalore.com/PopupMerchantDetails.aspx?ID=".$v["idinaff"];
			$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $tmp_request);
			$prgm_detail = $prgm_arr["content"];
	
			$LogoUrl = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, '<img id="small_image" src="', '"'));
			if (stripos('http://www.clixGalore.com/images/merchant/', $LogoUrl) == false)
				$LogoUrl = '';
			$CookieTime = intval($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, 'id="lbl_cookie_expiry">', '</span>'));
			if ($CookieTime == 'N/A')
				$CookieTime = 0;
			$PaymentDays = intval(trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, '<span id="lbl_approve_after">After', 'day')));
			if (empty($PaymentDays))
				$PaymentDays = 0;
			$Homepage = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Website URL', 'href="'), '"'));
			$CommissionExt = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, 'id="lbl_commission_rate">', '</span>'));
			$desc = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, 'id="lbl_description">', '</span>'));
			
			$SubAffPolicyExt = "";
			$lbl_traffic = strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, 'id="lbl_traffic">', '</span>'));
			if($lbl_traffic){
				$SubAffPolicyExt = "Not Accepting Traffic From: " . $lbl_traffic;
			}
			
			$arr_prgm[$v["idinaff"]] = array(					
				"AffId" => $this->info["AffId"],					
				"IdInAff" => $v["idinaff"],					
				"Homepage" => $Homepage,
				"CommissionExt" => addslashes($CommissionExt),
				"Description" => addslashes($desc),					
				"CookieTime" => $CookieTime,					
				"SubAffPolicyExt" => addslashes($SubAffPolicyExt),
				"DetailPage" => $prgm_url,
				"LogoUrl" => addslashes($LogoUrl),
				"PaymentDays" => $PaymentDays
			);
			if(count($arr_prgm) >= 100){
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
		echo "\tGet Program detail end @ ".date("Y-m-d H:i:s")."\r\n";
	}

    function getProgramCategory()
    {
        echo "\tGet Program category start @ ".date("Y-m-d H:i:s")."\r\n";
        $objProgram = new ProgramDb();
        $arr_prgm = array();

        $request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "", 'SSLV'=> 3);
        $results = '';

        $page = 1;
        $hasNextPage = true;
        $__EVENTTARGET = '';
        while ($hasNextPage) {
            echo "\t page $page.";

            if ($page == 1) {
                $request['method'] = 'get';
            } else {
                $request['method'] = 'post';
                $__EVENTVALIDATION = urlencode($this->oLinkFeed->ParseStringBy2Tag($results, array('name="__EVENTVALIDATION"', 'value="'), '"'));
                $__VIEWSTATE = urlencode($this->oLinkFeed->ParseStringBy2Tag($results, array('name="__VIEWSTATE"', 'value="'), '"'));
                $request["postdata"] = "__EVENTTARGET={$__EVENTTARGET}&__EVENTARGUMENT=&__VIEWSTATE={$__VIEWSTATE}&__EVENTVALIDATION={$__EVENTVALIDATION}&AffProgramdropdown1%24aff_program_list={$this->AfID}&dd_category=0&txt_advsearch=";
            }

            $results = $this->oLinkFeed->GetHttpResult('http://www.clixgalore.com/AffiliateMerchantCategoryAnalysis.aspx', $request);
            $results = $results['content'];
            if (strpos($results, 'clixGalore - Category Analysis') === false) {
                mydie("Can't get category page!");
            }

            $strLineStart = "javascript:window.status='View Merchant Details'";

            $nLineStart = 0;
            while ($nLineStart >= 0) {
                $nLineStart = stripos($results, $strLineStart, $nLineStart);
                if ($nLineStart === false) {
                    break;
                }

                $strMerID = intval($this->oLinkFeed->ParseStringBy2Tag($results, 'href="javascript:DisplayMerchant(', ')', $nLineStart));
                if (!$strMerID) {
                    continue;
                }

                $ctgr_arr = array();
                $ctgr_arr[] = trim($this->oLinkFeed->ParseStringBy2Tag($results, array('<td','<td','<td','<td','>'), '<', $nLineStart));
                $ctgr_arr[] = trim($this->oLinkFeed->ParseStringBy2Tag($results, array('<td','>'), '<', $nLineStart));
                $ctgr_arr[] = trim($this->oLinkFeed->ParseStringBy2Tag($results, array('<td','>'), '<', $nLineStart));
                $ctgr_arr[] = trim($this->oLinkFeed->ParseStringBy2Tag($results, array('<td','>'), '<', $nLineStart));

                foreach ($ctgr_arr as $key => $ctVal) {
                    if ($ctVal == '&nbsp;') {
                        unset($ctgr_arr[$key]);
                    }
                }

                $arr_prgm[$strMerID] = array(
                    "AffId" => $this->info["AffId"],
                    "IdInAff" => $strMerID,
                    "CategoryExt" => addslashes(join(' ; ', $ctgr_arr)),
                );

                if(count($arr_prgm) >= 100){
                    $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
                    $this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
                    $arr_prgm = array();
                }
            }

            $tmp_target = trim($this->oLinkFeed->ParseStringBy2Tag($results, array('Pages Found:', "<span>$page</span>", 'href="javascript:__doPostBack(\'dg_Merchants$ctl24$ct'), "'"));
            if ($tmp_target == false) {
                $hasNextPage = false;
                break;
            } else {
                $__EVENTTARGET = urlencode('dg_Merchants$ctl24$ct' . $tmp_target);
                $page++;
            }
        }

        if(count($arr_prgm) > 0){
            $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
            $this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
            $arr_prgm = array();
        }

        echo "\n\tGet Program category end @ ".date("Y-m-d H:i:s")."\r\n";
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

