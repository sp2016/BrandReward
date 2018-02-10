<?php
require_once 'text_parse_helper.php';

class LinkFeed_6_PJN
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;

		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";

		if(SID == 'bdg02'){
			define('API_KEY_6', 'dbec64f90d497bca3a139cc8403f752fab6a0ce75855811cc9c56ac1b02ec0f9');
		}else{
			//define('API_KEY_6', 'd8fb6cd3f139e75abe2ed10468155c05e678464eca2cb13c7fa29e691525847d');
			define('API_KEY_6', 'b78ce5175062370b55c7067bcb464889d13e5294c92295e25502387652ed4fdf');
		}
	}

	function GetProgramFromAff()
	{
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
		$this->getProgramByApi();
		$this->getProgramByPage();

		$this->checkProgramOffline($this->info["AffId"], $check_date);
		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}

	function getProgramByApi(){
		echo "\tGet Program by api start\r\n";
		$objProgram = new ProgramDb();
		list($arr_prgm, $program_num, $page, $hasNextPage) = array(array(), 0, 1, true);
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "",);

		while($hasNextPage)
		{
			$apiurl = sprintf("http://api.pepperjamnetwork.com/20120402/publisher/advertiser?apiKey=%s&format=json&page=%s", API_KEY_6, $page);
			//			echo $apiurl;
			//			die;
			$r = $this->oLinkFeed->GetHttpResult($apiurl, $request);
			$result = json_decode($r["content"]);
			if(isset($result->meta->status->code) && $result->meta->status->code==429)
				mydie($result->meta->status->message);
			$total_pages = $result->meta->pagination->total_pages;
			if($page >= $total_pages) $hasNextPage = false;
			$page++;

			$advertiser_list = $result->data;
			foreach($advertiser_list as $advertiser)
			{
				$strMerID = $advertiser->id;
				$strMerName = $advertiser->name;
				$desc = $advertiser->description;
				//$TermAndCondition = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($desc, 'Terms & Conditions:', '</div>')));
				$pattern = '/(Terms & Conditions|terms and conditions)(.*?)$/is';
				if(preg_match($pattern,$desc,$matches)){
				    $TermAndCondition = $matches[0];
				}else {
				    $TermAndCondition = '';
				}
				
				
				$desc = trim(strip_tags($desc));
				//$TargetCountryExt = $advertiser->country_code;
				$StatusInAffRemark = $advertiser->status;
				if($StatusInAffRemark == "joined"){
					$Partnership = "Active";
				}elseif($StatusInAffRemark == "revoked_advertiser"){
					$Partnership = "Expired";
				}elseif($StatusInAffRemark == "applied"){
					$Partnership = "Pending";
				}elseif($StatusInAffRemark == "declined_advertiser"){
					$Partnership = "Declined";
				}elseif($StatusInAffRemark == "invited"){
					$Partnership = "Pending";
				}elseif($StatusInAffRemark == "revoked_publisher"){
					$Partnership = "Removed";
				}elseif($StatusInAffRemark == "declined_publisher"){
					$Partnership = "Removed";
				}else{
					$Partnership = "NoPartnership";
				}
				if(strstr($desc,'Publishers will not utilize any promotion, promotion code, coupon or other promotional opportunity that is not specifically authorized')){
					$AllowNonaffCoupon = 'NO';
				}elseif(strstr($desc,'Affiliates are not permitted to post any promotional or marketing material ；  ALL affiliates Must Use Current Promotions that are reflected through banners, text links, and coupons in Pepperjam, otherwise they may be subjected to removal from the program.')){
					$AllowNonaffCoupon = 'NO';
				}else{
					$AllowNonaffCoupon = 'UNKNOWN';
				}
				$arr_prgm[$strMerID] = array(
					"Name" => addslashes(html_entity_decode(trim($strMerName))),
					"AffId" => $this->info["AffId"],
					"IdInAff" => $strMerID,
					"StatusInAff" => "Active",						//'Active','TempOffline','Offline'
					"StatusInAffRemark" => addslashes($StatusInAffRemark),
					"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','Removed'
					"Description" => addslashes($desc),
					"TermAndCondition" => addslashes($TermAndCondition),
					"LastUpdateTime" => date("Y-m-d H:i:s"),
					"MobileFriendly" => 'UNKNOWN',
					"AllowNonaffCoupon"=>$AllowNonaffCoupon,
					//"TargetCountryExt" => addslashes($TargetCountryExt),
					"LogoUrl" => addslashes($advertiser->logo),
				);
				if ($advertiser->mobile_tracking == 'Enabled')
					$advertiser->mobile_tracking = 'YES';
				$program_num++;
				if(count($arr_prgm) >= 100){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
			}
		}
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
			unset($arr_prgm);
		}
		echo "\tGet Program by api end\r\n";
		if($program_num < 10)
			mydie("die: program count < 10, please check program.\n");
		echo "\tUpdate ({$program_num}) program.\r\n";
	}

	function GetProgramByPage()
	{
		echo "\tGet Program by page start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", );
		// step 1,login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		// get SupportDeepurl
		print "\n Get SupportDeepurl\n";
		$hasSupportDeepurl = false;
		$SupportDeepurl_arr = array();
		$SupportDeepurl_arr = $this->getSupportDUT();
		if(count($SupportDeepurl_arr) > 100)
			$hasSupportDeepurl = true;

		// Step 1 Get all merchants
		$strUrl = "http://www.pepperjamnetwork.com/affiliate/program/manage?&csv=1";
		$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"merchant_csv_".date("YmdH").".dat", "cache_merchant");
		if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
		{
			$r = $this->oLinkFeed->GetHttpResult($strUrl, $request);
			$result = $r["content"];
			$this->oLinkFeed->fileCachePut($cache_file,$result);
		}
		$str_header = 'Program ID,Program Name,Deep Linking,Product Feed,Email,Phone,Allowed Promotional Methods,Prohibited States,Generic Link,Website URL,Logo,Locking Period,Cookie Duration,Commission,Join Date,Affidavit Required';
		$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"merchant_csv_".date("YmdH").".dat", "cache_merchant");
		if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
		{
			$strUrl = "http://www.pepperjamnetwork.com/affiliate/program/manage?&csv=1";
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$result = $r["content"];
			print "Get Merchant CSV.\n";
			$this->oLinkFeed->fileCachePut($cache_file,$result);
			if(stripos($result,$str_header) === false){
				mydie("die: wrong csv file: $cache_file");
				//				continue;
			}
		}

		//        print_r($cache_file);die;
		//Open CSV File
		$fhandle = fopen($cache_file, 'r');
		if(!$fhandle)
			mydie("open $cache_file failed.\n");
		while ($line = fgetcsv ($fhandle, 50000, ','))
		{
			//Program ID,Program Name,Deep Linking,Product Feed,Email,Phone,Allowed Promotional Methods,Prohibited States,Generic Link,Website URL,Logo,Locking Period,Cookie Duration,Commission,Join Date,Affidavit Required
			$strMerID = intval($line[0]);
			if ($strMerID < 1)
				continue;
			$strMerName = $line[1];
			$SupportDeepurl = $line[2];
			$tmp_email = $line[4];
			$tmp_phone = $line[5];
			//$tmp_email = $line[6];
			$tmp_prohibited_states = $line[7];
			$AffDefaultUrl = '';//$line[7];
			$country = $line[8];
			$Homepage = $line[10];
			//$tmp_logo = $line[10];
			//$tmp_phone = $line[11];
			$ReturnDays = $line[13];
			$CommissionExt_bk = $line[14];
			$JoinDate = $line[15];
			//$tmp_affidavit_required = $line[15];
			$SubAffPolicyExt = "";
			if($tmp_prohibited_states)
				$SubAffPolicyExt = "Prohibited States: ".$tmp_prohibited_states;
			if($JoinDate)
				$JoinDate = date("Y-m-d H:i:s", strtotime($JoinDate));
			//program_detail
			$prgm_url = "http://www.pepperjamnetwork.com/affiliate/program/details?programId=$strMerID";
			$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
			$prgm_detail = $prgm_arr["content"];
			//$TargetCountryExt = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<img src="/images/flags/', ' title="'), '"'));
			$TargetCountryExt = $this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<ul id="program-popup-countries">', '<li>'), '</ul>');
			$TargetCountryExt = trim(strip_tags(str_replace('</li><li>', ',', $TargetCountryExt)));
			$CategoryExt = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, '<strong>Categories:</strong>', '</div>'));
			$Contacts = "Manager: ".trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, '<strong>Manager:</strong>', '</div>')));
			$Contacts .= ", Email: ".$tmp_email;
			$Contacts .= ", Phone: ".$tmp_phone;
			$Contacts .= ", Address: ".trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<strong>Address:</strong>', '<div>'), '</div>')));
			$SEMPolicyExt = "Suggested Keywords:".trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, '<h3>Suggested Keywords:</h3>', '<h3>')));
			$SEMPolicyExt .= ", \nRestricted Keywords:".trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, '<h3>Restricted Keywords:</h3>', '</div>')));
			$CommissionExt = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, 'Default Terms', '* Incentives for a monthly period')));
			if(empty($CommissionExt))
				$CommissionExt = $CommissionExt_bk;
			if($hasSupportDeepurl && isset($SupportDeepurl_arr[$strMerID]))
			{
				$SupportDeepurl = $SupportDeepurl_arr[$strMerID]['SupportDeepurl'];
				$AffDefaultUrl = $SupportDeepurl_arr[$strMerID]['AffDefaultUrl'];
			}
			$arr_prgm[$strMerID] = array(
				"Name" => addslashes(html_entity_decode(trim($strMerName))),
				"AffId" => $this->info["AffId"],
				"TargetCountryExt" => addslashes($TargetCountryExt),
				"CategoryExt" => addslashes($CategoryExt),
				"Contacts" => addslashes($Contacts),
				"IdInAff" => $strMerID,
				"CreateDate" => $JoinDate,
				//"StatusInAff" => $StatusInAff,						//'Active','TempOffline','Offline'
				//"StatusInAffRemark" => addslashes($StatusInAffRemark),
				//"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','Removed'
				//"Description" => addslashes($desc),
				"Homepage" => addslashes($Homepage),
				"CommissionExt" => addslashes($CommissionExt),
				//"EPC30d" => addslashes(preg_replace("/[^0-9.]/", "", $EPC30d)),
				"CookieTime" => $ReturnDays,
				"SEMPolicyExt" => addslashes($SEMPolicyExt),
				//"TermAndCondition" => addslashes($TermAndCondition),
				"SubAffPolicyExt" => addslashes($SubAffPolicyExt),
				"LastUpdateTime" => date("Y-m-d H:i:s"),
				"DetailPage" => $prgm_url,
				"SupportDeepUrl" => $SupportDeepurl,
				"AffDefaultUrl" => $AffDefaultUrl,
			);
			if(count($arr_prgm) >= 100){
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
				$arr_prgm = array();
			}
			$program_num++;
		}
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
			unset($arr_prgm);
		}
		echo "\tGet Program by page end\r\n";
		if($program_num < 10)
			mydie("die: program count < 10, please check program.\n");
		echo "\tUpdate ({$program_num}) program.\r\n";
		echo "\tSet program country int.\r\n";
		$objProgram->setCountryInt($this->info["AffId"]);
	}

	function getInvalidLinks()
	{
		$request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "",);
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		list($page, $pages, $links, $ids) = array(0, 0, array(), array());
		do
		{
			$url = sprintf('http://www.pepperjamnetwork.com/affiliate/report/invalid-link?csv=csv&ajax=ajaxsortColumn=created&sortType=DESC&rowsPerPage=100&offset=%s', $page);
			$affid = $this->info["AffId"];
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$content = $r['content'];
			if (empty($pages))
			{
				if(preg_match('@</select> of (\d+)\s+</div>@', $content, $g))
					$pages = $g[1];
			}
			preg_match_all('@<tr class="tableEven reportGenRow">(.*?)</tr>@ms', $content, $chapters);
			foreach ($chapters[1] as $chapter)
			{
				preg_match_all('@<td>(.*?)</td>@ms', $chapter, $columns);
				$id = trim($columns[1][2]);
				if (!empty($ids[$id]))
				{
//					echo "duplicate id: $id.\n";
					continue;
				}
				$ids[$id] = 1;
				foreach ($links as $key => $link)
				{
					if ($link['LinkID'] == $id)
					{
						$links[$key]['Clicks'] += 1;
					}
				}
				$link = array(
						'affiliate' => $this->info["AffId"],
						'LinkID' => $id,
						'ProgramName' => trim($columns[1][1]),
						'AffiliationStatus' => trim($columns[1][4]),
						'ProgramID' => trim($columns[1][0]),
						'CreativeType' => trim($columns[1][3]),
						'Clicks' => 1,
				);
				foreach ($links as $exist)
				{
					if ($exist['LinkID'] == $link['LinkID'])
						continue;
				}
				if (preg_match('@cidtype\'\:\s+\'(.*?)\'@', $chapter, $g))
				{
					$url = sprintf("http://www.pepperjamnetwork.com/affiliate/report/creative-details-information?cid=%s&cidType=%s", $link['LinkID'], $g[1]);
					$r = $this->oLinkFeed->GetHttpResult($url, $request);
					$detail = $r['content'];
					if (preg_match('@<table>(.*?)</table>@ms', $detail, $g))
						$link['Details'] = trim($g[1]);
					$links[] = $link;
				}
			}
			$page ++;
		}while ($page < $pages);
		return $links;
	}

	function getCouponFeed()
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", );

		$check_date = date('Y-m-d H:i:s');
		$links = array();
		$methods = array('text','coupon');
		//$methods = array('coupon');
		foreach ($methods as $value){
		    
		    $page = 1;
		    do{
		        $count = 0;
		        $url = sprintf('http://api.pepperjamnetwork.com/20120402/publisher/creative/%s?apiKey=%s&format=json&page=%s', $value, API_KEY_6,$page);
		    
		        $r = $this->oLinkFeed->GetHttpResult($url, $request);
		        if($r['code'] != 200) break;
		        $content = json_decode($r["content"],true);
		        $totalPages = $content['meta']['pagination']['total_pages'];
		        //print_r($content);exit;
		        foreach ($content['data'] as $v)
		        {
		            $link = array(
		                "AffId" => $this->info["AffId"],
		                "AffMerchantId" => $v['program_id'],
		                "AffLinkId" => sprintf('c_%s_%s', $v['id'], $v['program_id']),
		                "LinkName" => sprintf('%s', $v['name']),
		                "LinkDesc" => sprintf('%s', $v['description']),
		                "LinkStartDate" => parse_time_str($v['start_date'], 'Y-m-d H:i:s', false),
		                "LinkEndDate" => parse_time_str($v['end_date'], 'Y-m-d H:i:s', false),
		                "LinkPromoType" => 'COUPON',
		                "LinkHtmlCode" => '',
		                "LinkCode" => isset($v['coupon']) ? $v['coupon']:'' ,
		                "LinkOriginalUrl" => '',
		                "LinkImageUrl" => '',
		                "LinkAffUrl" => '',
		                "DataSource" => '64',
		                "IsDeepLink" => 'UNKNOWN',
		                "Type"  => 'promotion'
		            );
		            
		            if($value == 'text'){
		                $link['LinkHtmlCode'] = $v['code'];
		            }
		            
		            if($value == 'text'){
		                $link['LinkAffUrl'] = $v['tracking_url'];
		            }elseif($value == 'coupon'){
		                $link['LinkAffUrl'] = $v['code'];
		            }
		            
		            
		            if(isset($v['allow_deep_link']) && $v['allow_deep_link'] == 1){
		                $link['IsDeepLink'] = 'YES';
		            }elseif(isset($v['allow_deep_link']) && $v['allow_deep_link'] == 0)
		                $link['IsDeepLink'] = 'NO';
		            
		            $link['LinkHtmlCode'] = create_link_htmlcode_image($link);
		            
		            if(empty($link['LinkCode'])){
		                $code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc']);
		                if (!empty($code))
		                {
		                    $link['LinkCode'] = $code;
		                    $link['LinkPromoType'] = 'COUPON';
		                }else{
		                    $link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
		                }
		            }
		             
		            if (empty($link['AffLinkId']) || empty($link['LinkName']))
		                continue;
		            $this->oLinkFeed->fixEnocding($this->info, $link, "feed");
		            $arr_return["AffectedCount"] ++;
		            $count ++;
		            $links[] = $link;
		            if (($arr_return['AffectedCount'] % 100) == 0)
		            {
		                $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		                $links = array();
		            }
		        }
		        echo sprintf("get $value couponCode BY page %s return count %s result(s) find.\n", $page, $count);
		        $page ++;
		    
		    
		    }while ($page<=$totalPages);
		    
		}
		
		
		if (count($links) > 0)
		    $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'promotion');
		
		return $arr_return;
	}
	
	function GetAllLinksByAffId(){
	    
	    $check_date = date('Y-m-d H:i:s');
	    $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
	    $request = array("AffId" => $this->info["AffId"], "method" => "get", );
	    $methods = array('banner');
	    $links = array();
	    foreach ($methods as $method)
	    {
	        
	        $page = 1;
	        do{
	            $count = 0;
	            $url = sprintf('http://api.pepperjamnetwork.com/20120402/publisher/creative/%s?apiKey=%s&format=json&page=%s', $method, API_KEY_6,$page);
	            
	            $r = $this->oLinkFeed->GetHttpResult($url, $request);
	            if($r['code'] != 200) break;
	            $content = json_decode($r["content"],true);
	            $totalPages = $content['meta']['pagination']['total_pages'];
	            
	            foreach ($content['data'] as $v){
	                $link = array(
	                    "AffId" => $this->info["AffId"],
	                    "AffMerchantId" => $v['program_id'],
	                    "AffLinkId" => sprintf('c_%s_%s', $v['id'], $v['program_id']),
	                    "LinkName" => sprintf('%s', $v['name']),
	                    "LinkDesc" => sprintf('%s', $v['description']),
	                    "LinkStartDate" => parse_time_str($v['start_date'], 'Y-m-d H:i:s', false),
	                    "LinkEndDate" => parse_time_str($v['end_date'], 'Y-m-d H:i:s', false),
	                    "LinkPromoType" => 'N/A',
	                    "LinkHtmlCode" => '',
	                    "LinkCode" => '',
	                    "LinkOriginalUrl" => '',
	                    "LinkImageUrl" => '',
	                    "LinkAffUrl" => '',
	                    "DataSource" => '64',
	                    "IsDeepLink" => 'UNKNOWN',
	                    "Type"       => 'link'
	                );
	                switch ($method)
	                {
	                    case 'banner':
	                        $link['LinkAffUrl'] = $v['tracking_url'];
	                        $link['LinkHtmlCode'] = $v['code'];
	                        $code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc']);
	                        if (!empty($code))
	                        {
	                            $link['LinkCode'] = $code;
	                            $link['LinkPromoType'] = 'COUPON';
	                        } else{
	                            $link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
	                        }
	                        if (preg_match('@img src="(.*?)"@', $link['LinkHtmlCode'], $g))
	                            $link['LinkImageUrl'] = $g[1];
	                        break;
	                    default:
	                        break;
	                }
	                if (empty($link['AffLinkId']) || empty($link['LinkName']))
	                    continue;
	                
	                if($v['allow_deep_link'] == 1){
	                    $link['IsDeepLink'] = 'YES';
	                }elseif($v['allow_deep_link'] == 0)
	                    $link['IsDeepLink'] = 'NO';
	                
	                $this->oLinkFeed->fixEnocding($this->info, $link, "feed");
	                $arr_return["AffectedCount"] ++;
	                $count ++;
	                $links[] = $link;
	                if (($arr_return['AffectedCount'] % 100) == 0)
	                {
	                    $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
	                    $links = array();
	                }
	            }
	            echo sprintf("get links method:%s,BY page %s return count %s result(s) find.\n", $method, $page, $count);
	            
	            $page ++;
	            
	        }while($page<=$totalPages);
	    }
	    if (count($links) > 0)
	        $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
	    $this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link');
	    return $arr_return;
	}

	function GetAllLinksFromAffByMerID($merinfo)
	{
	    $check_date = date('Y-m-d H:i:s');
	    $arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		$request = array("AffId" => $this->info["AffId"], "method" => "get", );
		$methods = array('text', 'banner');
		$links = array();
		foreach ($methods as $method)
		{
			$url = sprintf('http://api.pepperjamnetwork.com/20120402/publisher/creative/%s?apiKey=%s&format=csv&programId=%s', $method, API_KEY_6, $merinfo['IdInAff']);
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$content = $r["content"];
			$data = @fgetcsv_str($content);
			$count = 0;
			if(!$data) continue;
			
			foreach ((array)$data as $v)
			{
				$link = array(
						"AffId" => $this->info["AffId"],
						"AffMerchantId" => $v['program_id'],
						"AffLinkId" => sprintf('c_%s_%s', $v['id'], $v['program_id']),
						"LinkName" => sprintf('%s', $v['name']),
						"LinkDesc" => sprintf('%s', $v['description']),
						"LinkStartDate" => parse_time_str($v['start_date'], 'Y-m-d H:i:s', false),
						"LinkEndDate" => parse_time_str($v['end_date'], 'Y-m-d H:i:s', false),
						"LinkPromoType" => 'N/A',
						"LinkHtmlCode" => '',
						"LinkCode" => '',
						"LinkOriginalUrl" => '',
						"LinkImageUrl" => '',
						"LinkAffUrl" => '',
						"DataSource" => '64',
				        "IsDeepLink" => 'UNKNOWN',
				        "Type"       => 'link'
				);
				switch ($method)
				{
					case 'text':
						$link['LinkAffUrl'] = $v['tracking_url'];
						$link['LinkHtmlCode'] = $v['code'];
						$code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc']);
						if (!empty($code))
						{
							$link['LinkCode'] = $code;
							$link['LinkPromoType'] = 'COUPON';
						}else{
							$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
						}
						break;
					case 'banner':
						$link['LinkAffUrl'] = $v['tracking_url'];
						$link['LinkHtmlCode'] = $v['code'];
						$code = get_linkcode_by_text($link['LinkName'] . '|' . $link['LinkDesc']);
						if (!empty($code))
						{
							$link['LinkCode'] = $code;
							$link['LinkPromoType'] = 'COUPON';
						} else{
						    $link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
						}
						if (preg_match('@img src="(.*?)"@', $link['LinkHtmlCode'], $g))
							$link['LinkImageUrl'] = $g[1];
						break;
					default:
						break;
				}
				if (empty($link['AffLinkId']) || empty($link['LinkName']))
					continue;
				$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
				$arr_return["AffectedCount"] ++;
				$count ++;
				$links[] = $link;
				if (($arr_return['AffectedCount'] % 100) == 0)
				{
					$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
					$links = array();
				}
			}
			echo sprintf("program:%s, call api %s...%s result(s) find.\n", $merinfo['IdInAff'], $method, $count);
		}
		if (count($links) > 0)
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link', $merinfo['IdInAff']);
	}
	
	function GetAllProductsByAffId()
	{
	
		$check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
		$request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "");
	
		$arr_merchant = $this->oLinkFeed->getApprovalAffMerchant($this->info["AffId"]);
		$productNumConfig = $this->oLinkFeed->product_program_num_config($this->info["AffId"]);
		$productNumConfigAlert = '';
		$isAssignMerchant = FALSE;
		$mcount = 0;
		foreach ($arr_merchant as $merchatInfo)
		{
		    echo $merchatInfo['IdInAff'].PHP_EOL;
		    $crawlMerchantsActiveNum = 0;
		    $setMaxNum  = isset($productNumConfig[$merchatInfo['IdInAff']]) ? $productNumConfig[$merchatInfo['IdInAff']]['limit'] :  100;
		    $isAssignMerchant = isset($productNumConfig[$merchatInfo['IdInAff']]) ? TRUE : FALSE;
			$page = 1;
			
			do{
			    
			    $url = sprintf('http://api.pepperjamnetwork.com/20120402/publisher/creative/product?apiKey=%s&programIds=%s&format=json&page=%s', API_KEY_6 , $merchatInfo['IdInAff'], $page);
			    $r = $this->oLinkFeed->GetHttpResult($url, $request);
			    if($r['code'] != 200)
			        continue;
			    $r = json_decode($r["content"],true);
			    $total_pages = $r['meta']['pagination']['total_pages'];
			    if (empty($r['data']))
			        continue;
			    $totalPages = $r['meta']['pagination']['total_pages'];
			    $total_results = $r['meta']['pagination']['total_results'];
			    if(count($r['data']) == 0) break;
			    foreach ($r['data'] as $v)
			    {
			         
			        $ProductId = md5($merchatInfo['IdInAff'].$v['name']);
			    
			        $product_path_file = $this->oLinkFeed->getProductImgFilePath($this->info["AffId"],"{$merchatInfo['IdInAff']}_".urlencode($ProductId).".png", PRODUCTDIR);
			        if(!$this->oLinkFeed->fileCacheIsCached($product_path_file))
			        {
			            $file_content = $this->oLinkFeed->downloadImg($v['image_url']);
			            if(!$file_content) //下载不了跳过。
			                continue;
			            $this->oLinkFeed->fileCachePut($product_path_file, $file_content);
			        }
			        if(!isset($v['name']) || empty($v['name']) || !isset($ProductId))
			        {
			            continue;
			        }
			    
			        $link = array(
			            "AffId" => $this->info["AffId"],
			            "AffMerchantId" => $merchatInfo['IdInAff'],
			            "AffProductId" => $ProductId,
			            "ProductName" => addslashes($v['name']),
			            "ProductCurrency" => trim($v['currency']),
			            "ProductPrice" => trim($v['price']),
			            "ProductOriginalPrice" =>trim($v['price_sale']),
			            "ProductRetailPrice" =>trim($v['price_retail']),
			            "ProductImage" => addslashes($v['image_url']),
			            "ProductLocalImage" => addslashes($product_path_file),
			            "ProductUrl" => addslashes($v['buy_url']),
			            "ProductDestUrl" => '',
			            "ProductDesc" => addslashes($v['description_long']),
			            "ProductStartDate" => '',
			            "ProductEndDate" => '',
			        );
			        $links[] = $link;
			        $arr_return['AffectedCount'] ++;
			        $crawlMerchantsActiveNum ++;
			    }
			    unset($r);
			    if (count($links))
			    {
			        $arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateProductToDB($links);
			        $links = array();
			    }
			    //大于最大数跳出
			    if($crawlMerchantsActiveNum >= $setMaxNum){
			        break;
			    }
			    $page++;
			    
			}while($page < $total_pages);
			
			if($isAssignMerchant){
			    $productNumConfigAlert .= "AFFID:".$this->info["AffId"].",Program({$merchatInfo['MerchantName']}),Crawl Count($crawlMerchantsActiveNum),Total Count({$total_results}) \r\n";
			}
			
			$mcount ++;
		}
		echo 'merchant count:'.$mcount.PHP_EOL;
		$this->oLinkFeed->checkProductExists($this->info["AffId"], $check_date);
        echo $productNumConfigAlert.PHP_EOL;			
		echo 'END time'.date('Y-m-d H:i:s').PHP_EOL;
		return $arr_return;
		
	}

	function checkProgramOffline($AffId, $check_date){
		$objProgram = new ProgramDb();
		$prgm = array();
		$prgm = $objProgram->getNotUpdateProgram($this->info["AffId"], $check_date);

		if(count($prgm) > 50){
			mydie("die: too many offline program (".count($prgm).").\n");
		}else{
			$objProgram->setProgramOffline($this->info["AffId"], $prgm);
			echo "\tSet (".count($prgm).") offline program.\r\n";
		}
	}

	function getSupportDUT()
	{
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", );
		$str_url = "http://www.pepperjamnetwork.com/affiliate/creative/generic?website=&sid=&deep_link=&encrypted=0&rows_per_page=2000";
		$tmp_arr = $this->oLinkFeed->GetHttpResult($str_url, $request);
		$result = $tmp_arr["content"];
		$SupportDeepurl_arr = array();

		//parse HTML
		$strLineStart = '<td class="creative">';
		$nLineStart = 0;
		while ($nLineStart >= 0){
			$nLineStart = stripos($result, $strLineStart, $nLineStart);
			if ($nLineStart === false) break;
			$AffDefaultUrl = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('tracking-link', 'value="'), '"', $nLineStart)));
			$SupportDeepurl = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('Deep linking', '<span>'), '</span>', $nLineStart)));
			$strMerID = $this->oLinkFeed->ParseStringBy2Tag($result, 'data-id="', '"', $nLineStart);
			$strMerID = intval($strMerID);
			if($SupportDeepurl == "allowed"){
				$SupportDeepurl_arr[$strMerID]["SupportDeepurl"] = "YES";
			}else{
				$SupportDeepurl_arr[$strMerID]["SupportDeepurl"] = "NO";
			}
			$SupportDeepurl_arr[$strMerID]["AffDefaultUrl"] = $AffDefaultUrl;
		}
		// if there is a link named Deep Linking
		// the program is SupportDeepurl YES
		$q = "SELECT `AffMerchantId`,`LinkAffUrl` FROM `affiliate_links_6` WHERE `LinkName`='Deep Linking'";
		//echo $q."\n";
		$rows = $this->oLinkFeed->objMysql->getRows($q);
		foreach ($rows as $row)
		{
			$strMerID = $row['AffMerchantId'];
			if (!empty($strMerID) && !empty($row['LinkAffUrl']))
			{
				$SupportDeepurl_arr[$strMerID]["SupportDeepurl"] = "YES";
				$SupportDeepurl_arr[$strMerID]["AffDefaultUrl"] = $row['LinkAffUrl'];
			}
		}
		return $SupportDeepurl_arr;
	}

	public function GetStatus(){
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program Status start @ {$check_date}\r\n";
		$this->getProgramByApi();
		$this->checkProgramOffline($this->info["AffId"], $check_date);
		echo "Craw Program Status end @ ".date("Y-m-d H:i:s")."\r\n";
	}

}

