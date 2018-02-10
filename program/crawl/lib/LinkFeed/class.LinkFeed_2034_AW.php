<?php
require_once 'text_parse_helper.php';

class LinkFeed_2034_AW
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);                            //返回一维数组，存储当前aff_id对应的各个字段值
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		
		$this->cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"{$this->info["AffId"]}_".date("YWd").".dat", "program", true);
		$this->cache = array();
		if($this->oLinkFeed->fileCacheIsCached($this->cache_file)){
			$this->cache = file_get_contents($this->cache_file);
			$this->cache = json_decode($this->cache,true);
		}
		
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		$this->isRedirect = false;
		
		if(SID == 'bdg02'){
			$this->API_KEY_10 = 'd309e0ed18bfcb71356882ab3a38612d';
			$this->USERID = '313605';
			$this->feed_key = '5ac28baf2cc450d54f5041976965fc48';
		}else{
	    	//$this->API_KEY_10 = 'ecbea00d726390bcb40252e4e35436fb';
	    	//$this->USERID = '80151';
	    	//$this->feed_key = '46d151f42ac6e0db6fbc1cf29edfcba2';

			$this->API_KEY_10 = '60777a93e20b8b19e01c44774c1b3a1a';
			$this->USERID = '311227';
			$this->feed_key = '883fd734438d114df4b2910d30ba815f';
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

	function GetProgramByPage()
	{
		echo "\tGet Program by page start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		$request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "",);
		//print_r($this->info);
		//exit;
		//step 1,login
		//$this->oLinkFeed->clearHttpInfos($this->info["AffId"]);//re-login each time
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);//登录成功，返回true

        $this->showDate('SupportDeepurl');

		//step 2,get SupportDeepurl
		$str_url = "https://ui.awin.com/awin/affiliate/{$this->USERID}/linkbuilder";
		$tmp_arr = $this->oLinkFeed->GetHttpResult($str_url, $request);
		$result = $tmp_arr["content"];
		$result = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('<div ng-app="LinkBuilder"', 'init.allAdvertiserList={"joined":'), ',"notJoined":[{"advertiserId"'));//获取select标签内的content
		$result = json_decode($result,true);
		//var_dump($result);exit;
		$SupportDeepurl_arr = array();
		foreach ($result as $a){
			if(isset($a['advertiserId']) && isset($a['deeplinkEnabled'])){
				if($a['deeplinkEnabled'] == true)
					$SupportDeepurl_arr[$a['advertiserId']] = 'YES';
				else 
					$SupportDeepurl_arr[$a['advertiserId']] = 'NO';
			}
		}

		//step 3, get programs from csv feed.
		$allstatus = array(
			"active" => "approval",
			"notJoined" => "not apply",
			"pendingApproval" => "pending",
			"merchantSuspended" => "declined",
			"merchantRejected" => "declined",
			"closed" => "siteclosed",
		);
		$title = '"advertiserId","programmeName"';
		foreach($allstatus as $status_aff => $status)
		{
			$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"$status_aff" . ".dat","cache_merchant");//返回.cache文件的路径
			// 			echo $cache_file;
			// 			exit;
			if(!$this->oLinkFeed->fileCacheIsCached($cache_file)) //fileCacheIsCached函数检查$cache_file是否存在
			{
				$request["method"] = "get";
				$strUrlAllMerchant = "https://ui.awin.com/awin/affiliate/".$this->USERID."/merchant-directory/export/?membershipStatus=" . $status_aff . "&view=+";
				$r = $this->oLinkFeed->GetHttpResult($strUrlAllMerchant,$request);
				$result = $r["content"];//所有“csv文件中的program信息”
				if(stripos($result,$title) === false)
				{
					print_r($r);
					mydie("die: get merchant csv file failed, title not found; $strUrlAllMerchant \n");
				}
				$this->oLinkFeed->fileCachePut($cache_file,$result);//生成.cache文件,并将cvs数据写入此文件
			}
			if(!file_exists($cache_file)) mydie("die: merchant csv file does not exist. \n");
			//Open CSV File
			$arr_title = array();
			$col_count = 0;
			$fhandle = fopen($cache_file, 'r');//只读方式打开文件
			$first = true;

            $this->showDate('getCSV');

			while($line = fgetcsv($fhandle, 50000))//fgetcsv函数返回csv文件的一行，while循环csv中所有记录
			{
				if($first)
				{
					// [0] => advertiserId [1] => programmeName [2] => conversionRate [3] => approvalRate [4] => validationTime [5] => epc [6] => joinDate [7] => paymentStatus [8] => paymentRiskLevel [9] => awinIndex [10] => feedEnabled [11] => productReporting [12] => commissionMin [13] => commissionMax [14] => leadMin [15] => leadMax [16] => cookieLength [17] => parentSectors [18] => subSectors [19] => primarySector
					if($line[0] != 'advertiserId') mydie("die: title is wrong. \n");
					$arr_title = $line;
					$col_count = sizeof($arr_title);
					$first = false;
					continue;
				}
				if($line[0] == '' || $line[0] == 'advertiserId') continue;
				if(sizeof($line) != $col_count)
				{
					echo "warning: invalid line found: " . implode(",",$line) . "\n";
					continue;
				}
				$row = array();
				foreach($arr_title as $i => $title)
					$row[$title] = $line[$i];//$row是一个存有当前记录的title和值的关联数组

				if($status_aff == "active"){
					$Partnership = "Active";//$Partnership代表我们与商家的关系
					$StatusInAff = "Active";//$StatusInAff代表商家在联盟的状态
				}elseif($status_aff == "notJoined"){
					$Partnership = "NoPartnership";
					$StatusInAff = "Active";
				}elseif($status_aff == "pendingApproval"){
					$Partnership = "Pending";
					$StatusInAff = "Active";
				}elseif($status_aff == "merchantSuspended" || $status_aff == "merchantRejected"){
					$Partnership = "Declined";
					$StatusInAff = "Active";
				}else{
					$Partnership = "NoPartnership";
					$StatusInAff = "Offline";
					//print_r($row["advertiserId"]);echo " ＆nbsp ";
				}

				$arr_prgm[$row["advertiserId"]] = array(
					"Name" => addslashes(html_entity_decode(trim($row["programmeName"]))),
					"AffId" => $this->info["AffId"],
					//"Contacts" => '',
					//"TargetCountryExt" => '',
					"IdInAff" => $row["advertiserId"],
					"JoinDate" => date("Y-m-d H:i:s", strtotime($row["joinDate"])),
					"StatusInAffRemark" => $status_aff,
					"StatusInAff" => $StatusInAff,						//'Active','TempOffline','Offline'
					"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
					//"Description" => '',
					//"Homepage" => '',
					"CookieTime" => addslashes($row["cookieLength"]),
					"EPCDefault" => $row["epc"],
					//"TermAndCondition" => '',
					"LastUpdateTime" => date("Y-m-d H:i:s"),
					//"DetailPage" => '',
					//"SupportDeepUrl" => '',
					//"CommissionExt" => '',
					//"MobileFriendly" => 'UNKNOWN',
					"CategoryExt" => addslashes(str_replace("|",",",$line[17])),
					//"AllowNonaffPromo" => 'UNKNOWN',
					//"AllowNonaffCoupon"=> 'UNKNOWN',
					"PaymentDays" => $row['averagePaymentTime'],
				);

				$prgm_url = "https://ui.awin.com/awin/affiliate/".$this->USERID."/merchant-profile/{$row["advertiserId"]}";
				$arr_prgm[$row["advertiserId"]]['DetailPage'] = $prgm_url;
				$request["method"] = "get";
				$request["postdata"] = "";
				$non_aff_coupon = 'UNKNOWN';
				$non_aff_promo  = 'UNKNOWN';
				if(!isset($this->cache[$row["advertiserId"]]['detail'])){
					$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
					if($prgm_arr['code'] == 200){
						$prgm_detail = $prgm_arr["content"];//返回每个program的具体页面content
						$this->cache[$row["advertiserId"]]['detail'] = "1";
						$MobileFriendly = 'UNKNOWN';
						if (preg_match('@<h4>Mobile Optimised</h4>\s+<div.*?>\s+(.*?)\s+<@', $prgm_detail, $g))//正则的表带式定界符可以自定义，此处为@
						{
							if (strtoupper($g[1]) == 'YES'){
								$MobileFriendly = 'YES';
							}
							else if (strtoupper($g[1]) == 'NO'){
								$MobileFriendly = 'NO';
							}
						}
						$arr_prgm[$row["advertiserId"]]['MobileFriendly'] = $MobileFriendly;
						
						$desc = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, '<div id="descriptionLongContent" class="inlineTextArea">', '</div>'));
						$arr_prgm[$row["advertiserId"]]['Description'] = addslashes($desc);
						//edit by 2016/8/5

						if(preg_match('/Affiliates may only promote(.*)[discount codes|codes](.*)affiliate programme/iU',$desc,$matches)){

							$non_aff_coupon = 'NO';
							if(!preg_match('/(voucher codes|discount codes|promote codes)/i',$matches[0],$matches1)){
								$non_aff_promo = 'NO';
							}
						}
						$arr_prgm[$row["advertiserId"]]['AllowNonaffCoupon'] = $non_aff_coupon;
						$arr_prgm[$row["advertiserId"]]['AllowNonaffPromo'] = $non_aff_promo;

						//ParseStringBy2Tag函数返回div标签之间的content
						$Homepage = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('text-center', '<a target="_blank" href="'), '"'));
						$arr_prgm[$row["advertiserId"]]['Homepage'] = $Homepage;
						$tmp_contacts = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('Email', '"mailto:'), '"'));
						$Contacts = "";
						if($tmp_contacts){
							$Contacts = "Email: {$tmp_contacts}";
						}
						$arr_prgm[$row["advertiserId"]]['Contacts'] = addslashes($Contacts);

						$region_area = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('<h2>Regions</h2>','<ul class="salesRegions list-inline">'), '</ul>')));
						if ($region_area) {
                            $arr_prgm[$row["advertiserId"]]['TargetCountryExt'] = addslashes($region_area);
                        }
						
						$LogoUrl = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, array('viewProfilePicture', '<img src="'), '"'));
						$arr_prgm[$row["advertiserId"]]['LogoUrl'] = addslashes($LogoUrl);
					}
				}
                $this->showDate('getDetail');

				//termAndCondition
				$term_url = "https://ui.awin.com/awin/affiliate/".$this->USERID."/merchant-profile-terms/{$row["advertiserId"]}";
				if(!isset($this->cache[$row["advertiserId"]]['term'])){
					$term_arr = $this->oLinkFeed->GetHttpResult($term_url, $request);
					if($term_arr['code'] == 200){
						$term_detail = $term_arr["content"];
						$this->cache[$row["advertiserId"]]['term'] = "1";
						$TermAndCondition = trim($this->oLinkFeed->ParseStringBy2Tag($term_detail, '<div id="termsFreeTextContent" class="inlineTextArea">', '</div>'));
						$arr_prgm[$row["advertiserId"]]['TermAndCondition'] = addslashes($TermAndCondition);
						//edit by 2016/8/5
						if(preg_match('/Affiliates may only promote(.*)[discount codes|codes](.*)affiliate programme/iU',$TermAndCondition,$matches)){
							$non_aff_coupon = 'NO';
							$arr_prgm[$row["advertiserId"]]['AllowNonaffCoupon'] = $non_aff_coupon;
							if(!preg_match('/(voucher codes|discount codes|promote codes)/i',$matches[0],$matches1)){
								$non_aff_promo = 'NO';
								$arr_prgm[$row["advertiserId"]]['AllowNonaffPromo'] = $non_aff_promo;
							}
						}
					}
				}
                $this->showDate('getTermAndCondition');
				
				//PublisherPolicy
				$policy_url = "https://ui.awin.com/awin/affiliate/".$this->USERID."/merchant-profile-terms/{$row["advertiserId"]}/affiliate";
				if (SID == 'bdg02'){
					$policy_arr = $this->oLinkFeed->GetHttpResult($policy_url, $request);
					if($policy_arr['code'] == 200){
						$policy_detail = $policy_arr["content"];
						$PublisherPolicy = trim($this->oLinkFeed->ParseStringBy2Tag($policy_detail, '<table class="table table-striped table-hover">', '</table>'));
						$arr_prgm[$row["advertiserId"]]['PublisherPolicy'] = addslashes($PublisherPolicy);
					}
				}
                $this->showDate('getPublisherPolicy');
				
				//$CommissionExt = count(array_filter(array($row['commissionMin'], $row['commissionMax']))) ? array($row['commissionMin'], $row['commissionMax']) : array($row['leadMin'], $row['leadMax']);
				$check_commission_url = "https://ui.awin.com/awin/affiliate/".$this->USERID."/merchant-profile/{$row["advertiserId"]}/xhr-commission-group-search/";
				if(!isset($this->cache[$row["advertiserId"]]['CommissionExt'])){
					$comm_r = $this->oLinkFeed->GetHttpResult($check_commission_url, $request);
					if($comm_r['code'] == 200){
						$comm_r = $comm_r["content"];
						$this->cache[$row["advertiserId"]]['CommissionExt'] = "1";
						preg_match_all('@commissionLevel current">(.*?)</td>@i', $comm_r, $m);
						if(count($m[1])){
							$tmp_comm = array();
							foreach($m[1] as $v){
								preg_match('@class="tooltipRight">(.*?)<@i', $v, $mm);
								if (!empty($mm[1])) {
									$tmp_comm[] = trim($mm[1]);
								}
							}
							$arr_prgm[$row["advertiserId"]]["CommissionExt"] = addslashes(implode('|', $tmp_comm));
						}else{
							preg_match_all('@commissionLevel">(.*?)</td>@i', $comm_r, $m);
							if(count($m[1])){
								$tmp_comm = array();
								foreach($m[1] as $v){
									preg_match('@class="tooltipRight">(.*?)<@i', $v, $mm);
									if (!empty($mm[1])) {
										$tmp_comm[] = trim($mm[1]);
									}
								}
								$arr_prgm[$row["advertiserId"]]["CommissionExt"] = addslashes(implode('|', $tmp_comm));
							}
							
							/* $tmp_cut = $this->oLinkFeed->ParseStringBy2Tag($comm_r, array('<table','Default','>'), '</td>');
							if ($tmp_cut) {
								preg_match('@class="tooltipRight">([a-zA-G]*\s*[0-9]+\.*[0-9]*%?)<@i', $tmp_cut, $mm);
								if (!empty($mm[1])) {
									$arr_prgm[$row["advertiserId"]]["CommissionExt"] = trim($mm[1]);
								}
							}else {
								//page 2
								$check_commission_url = "https://ui.awin.com/awin/affiliate/".$this->USERID."/merchant-profile/{$row["advertiserId"]}/xhr-commission-group-search/page/2/";
								$comm_detail = $this->oLinkFeed->GetHttpResult($check_commission_url, $request);
								if($comm_detail['code'] == 200) {
									$comm_detail = $comm_detail["content"];
									$tmp_cut = $this->oLinkFeed->ParseStringBy2Tag($comm_detail, array('<table','Default','>'), '</td>');
									preg_match('@class="tooltipRight">([a-zA-G]*\s*[0-9]+\.*[0-9]*%?)<@i', $tmp_cut, $mm);
									if (!empty($mm[1])) {
										$arr_prgm[$row["advertiserId"]]["CommissionExt"] = trim($mm[1]);
									}
								} 
							}*/
						}
					}
					
					if (!isset($arr_prgm[$row["advertiserId"]]["CommissionExt"]) || empty($arr_prgm[$row["advertiserId"]]["CommissionExt"])) {
						if (isset($comm_r['content'])&& stripos($comm_r['content'], 'The account you are trying to view is not active') !== false)
						{
							$arr_prgm[$row["advertiserId"]]["CommissionExt"] = '';
							$arr_prgm[$row["advertiserId"]]['StatusInAff'] = 'TempOffline';
							echo 'Program has offline that IdInAff is '.$row["advertiserId"];
						}else{
//							$arr_prgm[$row["advertiserId"]]["CommissionExt"] = '';
							echo ("Can't get CommissionExt IdInAFF:{$row["advertiserId"]}!");
						}
					}
				}
                $this->showDate('getCommissionExt');

				
				$SupportDeepurl = 'UNKNOWN';
				if(isset($SupportDeepurl_arr[$row["advertiserId"]])){
					$SupportDeepurl = $SupportDeepurl_arr[$row['advertiserId']];
				}
				$arr_prgm[$row["advertiserId"]]["SupportDeepUrl"] = $SupportDeepurl;
			
				//print_r($arr_prgm[$row["advertiserId"]]);
				$program_num++;
				if(count($arr_prgm) >= 100){//当$arr_prgm数组中的记录数大于100，开始往数据库中插入或更新
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					// 					echo "<pre>";
					// 					print_r($arr_prgm);
					// 					exit;
					//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
			}//while
			if(count($arr_prgm)){//记录数也许不是100的整数，所有会有余下的没有插进数据库的记录，在这里进行处理
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
				$arr_prgm = array();
			}
		}//foreach
		$this->cache = json_encode($this->cache);
		$this->oLinkFeed->fileCachePut($this->cache_file, $this->cache);
		echo "\tGet Program by page end\r\n";
		if($program_num < 10){
			mydie("die: program count < 10, please check program.\n");
		}
		echo "\tUpdate ({$program_num}) program.\r\n";
		echo "\tSet program country int.\r\n";
		$objProgram->setCountryInt($this->info["AffId"]);
	}//function


	private function getSoapClient()
	{
		if (empty($this->soapClient))
		{
			$AW_API_NAMESPACE = 'http://api.productserve.com/';
			$oUser = new stdClass();
			$oUser->sApiKey = $this->API_KEY_10;
			$client = new SoapClient('http://v3.core.com.productserve.com/ProductServeService.wsdl', array('trace'=>true, 'compression'=> SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE));
			$oHeader  = new SoapHeader($AW_API_NAMESPACE, 'UserAuthentication', $oUser, true, $AW_API_NAMESPACE);
			$aHeaders = array($oHeader);
			$aHeaders[] = new SoapHeader($AW_API_NAMESPACE, 'getQuota', true, true, $AW_API_NAMESPACE);
			$client->__setSoapHeaders($aHeaders);
			ini_set("soap.wsdl_cache_enabled", 1);
			ini_set('soap.wsdl_cache_ttl', 86400);
			ini_set('default_socket_timeout', 300);
			$this->soapClient = $client;
		}
		return $this->soapClient;
	}
	
	private function loginRedirect()
	{
		if (!$this->isRedirect)
		{
			$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
//			$successString = "/awin/affiliate/80151/navigation/merchants";
			$successString = '/awin/affiliate/'.$this->USERID;
			$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "",);
			$url = 'https://darwin.affiliatewindow.com/awin/affiliate/'.$this->USERID;
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			if(strpos($r['content'], $successString) === false)
				mydie("die: failed to Redirect.\n");
			if (preg_match('@href="(.*?)"\s+class=""\s+>\s+Banners\s+</a>\s+</li>@', $r['content'], $g))
			{
				$url = trim($g[1]);
				if (!empty($url))
				{
					$url = 'https://darwin.affiliatewindow.com' . $url;
					$r = $this->oLinkFeed->GetHttpResult($url, $request);
					$this->isRedirect = true;
				}
				else
					mydie("die: failed to Redirect.\n");
			}
			else
				mydie("die: failed to Redirect.\n");
		}
	}
	
	function GetAllLinksByAffId()
	{
		$check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "");
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		// banner and text
		$arr_type = array('Image Banner' => 3,'Text Link' => 5);
		foreach ($arr_type as $linkType => $TypeCode ){
				
			$url = "https://ui.awin.com/awin/affiliate/{$this->USERID}/my-creative";
			$request = array("AffId" => $this->info["AffId"], "method" => "get","postdata" => "",);
			$result = $this->oLinkFeed->GetHttpResult($url, $request);
			$re = $result['content'];
			$Authorization = "Authorization:Bearer " . trim($this->oLinkFeed->ParseStringBy2Tag($re, "creative.init('", "')"));
			//echo $Authorization;
				
			$nPageNo = 0;
			$nNumPerPage = 100;
			$bHasNextPage = true;
				
			while($bHasNextPage){
				$base_url = "https://ui.awin.com/creative-api/creatives?filters=%7B%22categoryIds%22:%5B{$TypeCode}%5D%7D&pId={$this->USERID}&page={$nPageNo}&size={$nNumPerPage}&sort=updatedDate,desc";
				$request = array("AffId" => $this->info["AffId"], "method" => "get","postdata" => "","addheader" => array($Authorization));//print_r($base_url);exit;
				$r = $this->oLinkFeed->GetHttpResult($base_url, $request);
				$r = $r['content'];
				$content = json_decode($r,true);
				if(empty($content['content'])) break;
				//var_dump($content);exit;
				foreach ($content['content'] as $v){
					$link = array(
							"AffId" => $this->info["AffId"],
							"AffMerchantId" => $v['advertiserId'],
							"AffLinkId" => 'L'.$v['id'],
							"LinkName" => $v['name'],
							"LinkDesc" => $v['description'],
							"LinkStartDate" =>  '',
							"LinkEndDate" => '',
							"LinkPromoType" => 'link',
							"LinkCode" => '',
							"LinkOriginalUrl" => $v['targetUrl'],
							"DataSource" => '347',
							"IsDeepLink" => 'UNKNOWN',
							"Type"       => 'link'
					);
					if(strpos($v['description'],'Code') != false)
						$link['LinkCode'] = trim($this->oLinkFeed->ParseStringBy2Tag($v['description'], "Code:", "'"));
					if(strpos($v['name'],'$') != false || strpos($v['name'],'£') != false || strpos($v['name'],'€') != false){
						$link['LinkPromoType'] = 'DEAL';
						$link['Type'] = 'link';
					}
					if(isset($v['startDate']))
						$link['LinkStartDate'] = date('Y-m-d H:i:s',strtotime($v['startDate']));
					if(isset($v['endDate']))
						$link['LinkEndDate'] = date('Y-m-d H:i:s',strtotime($v['endDate']));
						
					if($linkType == 'Image Banner'){
						$link['LinkAffUrl'] = sprintf("https://www.awin1.com/cread.php?s=%s&v=%s&q=%s&r=%s", $v['id'],$v['advertiserId'],$v['advertiserTags'][0]['id'],$this->USERID);
						$link['LinkImageUrl'] = $v['location'];
						$link['LinkHtmlCode'] = '<a href="'.$link['LinkAffUrl'].'"><img src="'.$link['LinkImageUrl'].'" border="0"></a>';
					}elseif ($linkType == 'Text Link'){
						$link['linkAffUrl'] = "http://www.awin1.com/awclick.php?gid={$v['advertiserTags'][0]['id']}&mid={$v['advertiserId']}&awinaffid={$this->USERID}&linkid={$v['id']}&clickref=&p={$v['targetUrl']}";
						$link['LinkHtmlCode'] = '<a href="'.$link['linkAffUrl'].'">'.$v['html'].'</a>"';
					}
					$this->oLinkFeed->fixEnocding($this->info, $link, "link");
					$links[] = $link;
					$arr_return['AffectedCount'] ++;
				}
				echo sprintf("get $linkType link...%s result(s) find.\n", count($links));
				sleep(1);
				if (count($links) > 0)
					$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
				$nPageNo++;
				if(count($content['content']) < $nNumPerPage)
					$bHasNextPage = false;
				$links = array();
			}
		}
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link');
		return $arr_return;
		
	}
	
	function GetAllLinksFromAffByMerID($merinfo)
	{	
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		$request = array("AffId" => $this->info["AffId"],"method" => "post","postdata" => "",);

		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		// banner and text
		$arr_type = array('Image Banner' => 3,'Text Link' => 5);
		foreach ($arr_type as $linkType => $TypeCode ){
			
			$url = "https://ui.awin.com/awin/affiliate/{$this->USERID}/my-creative/advertiser/{$merinfo['IdInAff']}";
			$request = array("AffId" => $this->info["AffId"], "method" => "get","postdata" => "",);
			$result = $this->oLinkFeed->GetHttpResult($url, $request);
			$re = $result['content'];
			$Authorization = "Authorization:Bearer " . trim($this->oLinkFeed->ParseStringBy2Tag($re, "creative.init('", "')"));
			//echo $Authorization;
			
			$nPageNo = 0;
			$nNumPerPage = 100;
			$bHasNextPage = true;
			
			while($bHasNextPage){
				$base_url = "https://ui.awin.com/creative-api/creatives?filters=%7B%22advertiserIds%22:%5B%22{$merinfo['IdInAff']}%22%5D,%22categoryIds%22:%5B{$TypeCode}%5D%7D&pId={$this->USERID}&page={$nPageNo}&size={$nNumPerPage}&sort=updatedDate,desc";
				//$base_url = 'https://ui.awin.com/creative-api/creatives?filters=%7B%22advertiserIds%22:%5B%22%22%5D,%22categoryIds%22:%5B3%5D%7D&pId=311227&page=0&size=100&sort=updatedDate,desc';
				$request = array("AffId" => $this->info["AffId"], "method" => "get","postdata" => "","addheader" => array($Authorization));//print_r($base_url);exit;
				$r = $this->oLinkFeed->GetHttpResult($base_url, $request);
				$r = $r['content'];
				$content = json_decode($r,true);
				if(empty($content['content'])) break;
				//var_dump($content);exit;
				foreach ($content['content'] as $v){
					if($v['advertiserId'] != $merinfo['IdInAff']) mydie("advertiserId error! advertiserId is $v[advertiserId] and IdInAff is $merinfo[IdInAff]");
					$link = array(
							"AffId" => $this->info["AffId"],
				            "AffMerchantId" => $merinfo['IdInAff'],
				            "AffLinkId" => $v['id'],
				            "LinkName" => $v['name'],
				            "LinkDesc" => $v['description'],
				            "LinkStartDate" =>  '',
				            "LinkEndDate" => '',
				            "LinkPromoType" => 'link',
				            "LinkCode" => '',
				            "LinkOriginalUrl" => $v['targetUrl'],
				            "DataSource" => '347',
				            "IsDeepLink" => 'UNKNOWN',
				            "Type"       => 'link'
					);
					if(strpos($v['description'],'Code') != false)
						$link['LinkCode'] = trim($this->oLinkFeed->ParseStringBy2Tag($result, "Code:", "'"));
					if(strpos($v['name'],'$') != false || strpos($v['name'],'£') != false || strpos($v['name'],'€') != false){
						$link['LinkPromoType'] = 'DEAL';
						$link['Type'] = 'link';
					}	
					if(isset($v['startDate']))
						$link['LinkStartDate'] = date('Y-m-d H:i:s',strtotime($v['startDate']));
					if(isset($v['endDate']))
						$link['LinkEndDate'] = date('Y-m-d H:i:s',strtotime($v['endDate']));
					
					if($linkType == 'Image Banner'){
						$link['LinkAffUrl'] = sprintf("https://www.awin1.com/cread.php?s=%s&v=%s&q=%s&r=%s", $v['id'],$merinfo['IdInAff'],$v['advertiserTags'][0]['id'],$this->USERID);
						$link['LinkImageUrl'] = $v['location'];
						$link['LinkHtmlCode'] = '<a href="'.$link['LinkAffUrl'].'"><img src="'.$link['LinkImageUrl'].'" border="0"></a>';
					}elseif ($linkType == 'Text Link'){
						$link['linkAffUrl'] = "http://www.awin1.com/awclick.php?gid={$v['advertiserTags'][0]['id']}&mid={$v['advertiserId']}&awinaffid={$this->USERID}&linkid={$v['id']}&clickref=&p={$v['targetUrl']}";
						$link['LinkHtmlCode'] = '<a href="'.$link['linkAffUrl'].'">'.$v['html'].'</a>"';
					}
					$this->oLinkFeed->fixEnocding($this->info, $link, "link");
					$links[] = $link;
					$arr_return['AffectedCount'] ++;
				}
				echo sprintf("get $linkType link...%s result(s) find.\n", count($links));
				if (count($links) > 0)
					$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
				$nPageNo++;
				if(count($content['content']) < $nNumPerPage)
					$bHasNextPage = false;
			}
		}
		return $arr_return;

	}

    function getCouponFeed()
	{
		// csv feed.
	    $check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0,"UpdatedCount" => 0,"Detail" => array(),);
		$request = array("AffId" => $this->info["AffId"], "method" => "get","postdata" => "",);
		//$url = "http://www.affiliatewindow.com/affiliates/discount_vouchers.php?user=".$this->USERID."&password=".$this->feed_key."&export=csv";
		$url = "https://ui.awin.com/export-promotions/".$this->USERID."/".$this->feed_key."?promotionType=&categoryIds=&regionIds=&advertiserIds=&membershipStatus=joined&promotionStatus=";
		
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r['content'];
		$data = @fgetcsv_str($content);
		$links = array();
		foreach ((array)$data as $v)
		{
		    $link = array(
					"AffId" => $this->info["AffId"],
					"AffMerchantId" => $v['Advertiser ID'],
					"AffLinkId" => '',
					"LinkName" => sprintf('%s', @$v['Description']),
					"LinkDesc" => sprintf('%s', @$v['Description']),
					"LinkStartDate" => '0000-00-00 00:00:00', 
					"LinkEndDate" =>  '0000-00-00 00:00:00',//parse_time_str(@$v['end_date']),
					"LinkPromoType" => 'COUPON',
					"LinkHtmlCode" => '',
					"LinkOriginalUrl" => "",
					"LinkImageUrl" => "",
					"LinkCode" => sprintf('%s', @$v['Code']),
					"LinkAffUrl" => sprintf('%s', @$v['Deeplink Tracking']),
					"DataSource" => "13",
			        "IsDeepLink" => 'UNKNOWN',
			        "Type"       => 'promotion'
			);
			
			if($v['Starts']){
			    if (preg_match('@(\d+)/(\d+)/(\d+) (\d+):(\d+):(\d+)@', $v['Starts'], $g))
			    {
			        $date = strtotime(sprintf("%s-%s-%s %s:%s:00", $g[3], $g[2], $g[1], $g[4], $g[5]));
			        $link['LinkStartDate'] =   date('Y-m-d H:i:s', $date);
			    }
			}
			
			if($v['Ends']){
			    if (preg_match('@(\d+)/(\d+)/(\d+) (\d+):(\d+):(\d+)@', $v['Ends'], $g))
			    {
			        $date = strtotime(sprintf("%s-%s-%s %s:%s:00", $g[3], $g[2], $g[1], $g[4], $g[5]));
			        $link['LinkEndDate'] =   date('Y-m-d H:i:s', $date);
			    }
			}
			
			
			
			if ($link['LinkCode'] == '*****')
				$link['LinkCode'] = '';
			
			if (empty($link['LinkName']) && !empty($link['LinkCode']))
			    $link['LinkName'] = sprintf('%s. Use code: %s', @$v['Advertiser'], $link['LinkCode']);
			$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
			
			if($link['LinkCode'])  $link['LinkPromoType'] = 'COUPON';
			$link['LinkHtmlCode'] = create_link_htmlcode($link);
			//$link['AffLinkId'] = md5(sprintf("%s_%s_%s_%s", $link['AffMerchantId'], $link['LinkCode'], $link['LinkEndDate'], $link['LinkName']));
			$link['AffLinkId'] = 'C'.$v['Promotion ID'];
			if (empty($link['AffMerchantId']) || empty($link['AffLinkId']) )
				continue;
            //$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
			$links[] = $link;
			$arr_return['AffectedCount'] ++;
			 
			if (($arr_return['AffectedCount'] % 100) == 0)
			{
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
				$links = array();
			}
		}
		echo sprintf("get coupon by csv...%s result(s) find.\n", $arr_return['AffectedCount']);
		if (count($links) > 0)
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'promotion');
		return $arr_return;
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

	function showDate($a)
    {
        echo $a . ': ' . date('Y-m-d H:i:s') . "\n";
    }
}

