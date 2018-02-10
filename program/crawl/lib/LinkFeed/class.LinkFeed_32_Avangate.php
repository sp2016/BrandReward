<?php

require_once 'text_parse_helper.php';

class LinkFeed_32_Avangate
{
	/*
	var $info = array(
		"ID" => "32",
		"Name" => "Avangate",
		"IsActive" => "YES",
		"ClassName" => "LinkFeed_32_Avangate",
		"LastCheckDate" => "1970-01-01",
		'loginurl'	=> 'https://secure.avangate.com/affiliates/login.php',
		'loginpostdata'	=> "email=info%40couponsnapshot.com&password=hvu&LYold07k&Login=Login&x=33&y=14",
		'method'	=> 'post',
	);*/
	
	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		$this->AffiliateID = '91495';
	}

	function GetProgramFromAff()
	{
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
		$this->GetProgramByPage();
		$this->checkProgramOffline($this->info["AffId"], $check_date);
		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}

	function GetAllLinksByAffId()
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0,);
		$check_date = date('Y-m-d H:i:s');
		// no text & banner links found in the avangate.com
		// generate links for the program like the avangate.com do
		$db = new ProgramDb();
		$programs = $db->getAllProgramByAffId($this->info["AffId"]);
		if (!empty($programs) && is_array($programs))
		{
			$links = array();
			foreach ($programs as $program)
			{
				if (empty($program['IdInAff']) || empty($program['Homepage']))
					continue;
				$link = array(
						"AffId" => $this->info["AffId"],
						"AffMerchantId" => $program['IdInAff'],
						"AffLinkId" => $program['IdInAff'],
						"LinkName" =>  $program['Name'],
						"LinkDesc" =>  '',
						"LinkStartDate" => '0000-00-00 00:00:00',
						"LinkEndDate" => '0000-00-00 00:00:00',
						"LinkPromoType" => 'link',
						"LinkCode" => '',
						"LinkOriginalUrl" => '',
						"LinkImageUrl" => '',
						"DataSource" => 84,
				);
				$link['LinkAffUrl'] = sprintf('https://secure.avangate.com/affiliate.php?ACCOUNT=%s&AFFILIATE=%s&PATH=%s',
						$link['AffMerchantId'], $this->AffiliateID, urlencode($program['Homepage']));
				$link['LinkHtmlCode'] = create_link_htmlcode($link);
				//$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
				$links[] = $link;
				$arr_return['AffectedCount'] ++;
				if ($arr_return['AffectedCount'] % 100 == 0){
					$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
					$links = array();
				}
			}
			if(count($links) > 0)
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		}
		echo sprintf("%s links(s) found. \n", $arr_return['AffectedCount']);
		return $arr_return;
	}
	
	function getCouponFeed()
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		$check_date = date('Y-m-d H:i:s');
		//login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		//$arr_merchant = $this->oLinkFeed->getApprovalAffMerchant($this->info["AffId"]);
		$sql = "SELECT AffId,IdInAff,IdInAff as AffMerchantId,Name as MerchantName,DetailPage FROM program WHERE AffId = {$this->info['AffId']} AND StatusInAff in ('Active') AND Partnership in ('Active')";
		$arr_merchant = $this->oLinkFeed->objMysql->getRows($sql, "IdInAff");
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => "get",
				"header" => 1,					//输出头文件，里面有cookie
				"postdata" => "",
		);
		$links_url = "https://secure.avangate.com/affiliates/find_partners_links.php?r=1";
		$header_r = $this->oLinkFeed->GetHttpResult($links_url, $request);
		//print_r($header_r['content']);exit;
		preg_match_all('/Set-Cookie: csrfp_token=(.*?);/i', $header_r['content'], $m);
		$csrfp_token = $m[1][0];
		unset($request['header']);
		//print_r($m);exit;
		
		foreach ($arr_merchant as $AffMerchantId => $merinfo)
		{
			$detail_url = $merinfo['DetailPage'];
			$request['method'] = 'get';
			$detail_r = $this->oLinkFeed->GetHttpResult($detail_url, $request);
			$detail_r = $detail_r['content'];
			$nLineStart = 0;
			while (1)
			{
				$link_url = trim($this->oLinkFeed->ParseStringBy2Tag($detail_r, array('<div style="padding:5px;border-color:#2F93B8;" >', '<a href="'), '"', $nLineStart));
				if (!$link_url)
					break;
				$title = trim($this->oLinkFeed->ParseStringBy2Tag($detail_r, '>', '<', $nLineStart));
				$link_url = 'https://secure.avangate.com/affiliates/'.$link_url;
				$request['method'] = 'post';
				$request['postdata'] = "product_target=checkcart&page_url=Insert+your+custom+URL&landingpage=&template=&trackingid=&GenerateLinks=Generate+link&csrfp_token=$csrfp_token";
				$link_r = $this->oLinkFeed->GetHttpResult($link_url, $request);
				$link_r = $link_r['content'];
				if (stripos($link_r, 'coupon:') == false)
					continue;
				$PRODS = trim($this->oLinkFeed->ParseStringBy2Tag($link_r, '<a href="promotions_coupons.php?type=regular&preselectedProduct=', '&'));
				if (empty($PRODS))
					$PRODS = trim($this->oLinkFeed->ParseStringBy2Tag($link_r, '<textarea class="blue" id="taURL', '"'));
				if (empty($PRODS))
				{
					echo "program:($AffMerchantId) don't crawl the PRODS, please check it\r\n";
					continue;
				}
				$info = trim($this->oLinkFeed->ParseStringBy2Tag($link_r, array('<h3>Product Info</h3>', '<form>'), '</form>'));
				$info = strip_tags(str_replace(array('  ','\r','\n'), '', $info));
				$desc = trim($this->oLinkFeed->ParseStringBy2Tag($link_r, array('<h3>Product Description:</h3>', '>'), '<'));
				$desc = strip_tags(str_replace(array('  ','\r','\n'), '', $desc));
				//echo $info.$desc;
				$ImageUrl = 'https://secure.avangate.com'.trim($this->oLinkFeed->ParseStringBy2Tag($link_r, array('<h3>Product Box Image </h3>', '<img src="'), '"'));
				$LineStart = stripos($link_r, '<div id="_product_promotions" >');
				$dLineStart = $LineStart;
				while (1)
				{
					$promotion_id = trim($this->oLinkFeed->ParseStringBy2Tag($link_r, 'id="promotion_id_', '"', $LineStart));
					if (!$promotion_id)
						break;
					$LinkName =$title.' - '.trim($this->oLinkFeed->ParseStringBy2Tag($link_r, array('>', '>'), '<', $LineStart));
					$AffLinkId = md5(sprintf("%s_%s", $promotion_id, $LinkName));
					$LinkCode = trim($this->oLinkFeed->ParseStringBy2Tag($link_r, 'Coupon:</b>', '<', $LineStart));
					$EndDate = trim($this->oLinkFeed->ParseStringBy2Tag($link_r, 'Validity:</b>', '<', $LineStart));
					preg_match('/\d{4}-\d{2}-\d{2}/', $EndDate,$m);
					if (isset($m[0]))
						$LinkEndDate = parse_time_str($m[0]);
					else 
						$LinkEndDate = '0000-00-00 00:00:00';
					$link = array(
							"AffId" => $this->info["AffId"],
							"AffMerchantId" => $AffMerchantId,
							"AffLinkId" => $AffLinkId,
							"LinkName" =>  $LinkName,
							"LinkDesc" =>  '',
							"LinkStartDate" => '0000-00-00 00:00:00',
							"LinkEndDate" => $LinkEndDate,
							"LinkPromoType" => 'coupon',
							"LinkCode" => $LinkCode,
							"LinkOriginalUrl" => '',
							"LinkHtmlCode" => '',
							"LinkAffUrl" => '',
							"LinkImageUrl" => $ImageUrl,
							"DataSource" => 84,
							"IsDeepLink" => 'UNKNOWN',
							"Type" => 'promotion',
					);
					$link['LinkDesc'] = strip_tags(trim($this->oLinkFeed->ParseStringBy2Tag($link_r, '<li>', '</ul>', $dLineStart)).$info.$desc);
					$link['LinkAffUrl'] = "https://secure.avangate.com/order/cart.php?PRODS=$PRODS&QTY=1&AFFILIATE={$this->AffiliateID}&COUPON={$link['LinkCode']}";
					$link['LinkHtmlCode'] = create_link_htmlcode($link);
					if (empty($link['AffLinkId']) || empty($link['LinkAffUrl']) || empty($link['LinkName']))
						continue;
					$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
					$arr_return["AffectedCount"] ++;
					$links[] = $link;
				}
			}
			echo sprintf("program:%s, %s links(s) found. \n", $AffMerchantId, count($links));
			if(count($links) > 0)
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
			
			$links = array();
		}
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'promotion');
		return $arr_return;
	}
	
	function processProgramXml($cache_file){
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		
		$objXML = simplexml_load_file($cache_file);
		foreach($objXML->Vendors->Vendor as $objXMLVendor)
		{
	//		print_r($objXMLVendor);
	//		exit;
			$strMerName = $objXMLVendor->Name;
			$strMerID = addslashes(trim($objXMLVendor->Code));
			$Homepage = addslashes(trim($objXMLVendor->Homepage));
	//		echo $extMerName."|".$extMID."\n";
			
			$arr_prgm[$strMerID] = array(
				"Name" => addslashes(html_entity_decode(trim($strMerName))),
				"AffId" => $this->info["AffId"],				
				//"CategoryExt" => $CategoryExt,
				"IdInAff" => $strMerID,
				//"CreateDate" => $CreateDate,
				//"StatusInAff" => $StatusInAff,						//'Active','TempOffline','Offline'
				//"StatusInAffRemark" => addslashes($strStatus),
				//"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'				
				//"Description" => addslashes($desc),
				"Homepage" => $Homepage,					
				"LastUpdateTime" => date("Y-m-d H:i:s"),
			);
			
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
	}

	function GetProgramByPage()
	{
		echo "\tGet Program by page start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		$request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "",);

		//generate product feed
		/*$reporturl = "https://secure.avangate.com/affiliates/feeds.php";
		$return_arr = $this->oLinkFeed->GetHttpResult($reporturl, $request);
		$result = $return_arr["content"];
		$feedUrl = "";
		$feedUrl = $this->oLinkFeed->ParseStringBy2Tag($result, array('Products like %', 'View feed results', '<a href="'), '">Download feed');
		$file_name = "feed_avangate_".date("YmdHis").".dat.gz";
		$working_dir = $this->oLinkFeed->getWorkingDirByAffID($this->info["AffId"],"cache_merchant");	
		$cache_file = $working_dir . $file_name;
		if($feedUrl)
		{
			echo "get feed url: $feedUrl \n";
			$return_arr = $this->oLinkFeed->GetHttpResult($feedUrl, $request);
			$result = $return_arr["content"];
			$this->oLinkFeed->fileCachePut($cache_file, $result);
			exec("gzip -d $cache_file");
			$cache_file = substr($cache_file, 0, -3);
		}
		$cache_file = $working_dir . "feed_avangate_20120814060711.dat";
		$this->processProgramXml($cache_file);	*/
		$nNumPerPage = 100;//will be fixed
		$bHasNextPage = true;
		$nPageNo = 1;
		$start = 1;
		$max_start = $start + 1;

		while($bHasNextPage && $max_start >= $start)
		{
			$start = ($nPageNo - 1) * $nNumPerPage + 1;
			$strUrl = "https://secure.avangate.com/affiliates/find_partners.php?submitted=1&page=$nPageNo&recOnPage=$nNumPerPage&order_by=4&order_dir=-1&product_promotions=0&coupons_self_generation=0&partnership_status=2&searchFor=&commisionFrom=0&commisionTo=0&showV=1&cl_1=1&subcl_2_1=1&subcl_8_1=1&subcl_127_1=1&subcl_4_1=1&subcl_5_1=1&subcl_6_1=1&subcl_7_1=1&subcl_9_1=1&subcl_128_1=1&subcl_10_1=1&subcl_11_1=1&subcl_124_1=1&cl_12=1&subcl_13_12=1&subcl_14_12=1&subcl_140_12=1&subcl_129_12=1&subcl_15_12=1&subcl_143_12=1&subcl_16_12=1&subcl_17_12=1&subcl_132_12=1&subcl_18_12=1&subcl_19_12=1&subcl_20_12=1&subcl_21_12=1&subcl_22_12=1&subcl_23_12=1&cl_24=1&subcl_125_24=1&subcl_25_24=1&subcl_26_24=1&subcl_27_24=1&subcl_28_24=1&subcl_29_24=1&subcl_30_24=1&subcl_31_24=1&subcl_32_24=1&subcl_33_24=1&subcl_34_24=1&subcl_35_24=1&cl_36=1&subcl_37_36=1&subcl_38_36=1&subcl_39_36=1&subcl_40_36=1&subcl_41_36=1&subcl_42_36=1&subcl_145_36=1&subcl_43_36=1&subcl_44_36=1&subcl_45_36=1&subcl_46_36=1&subcl_47_36=1&subcl_48_36=1&cl_49=1&subcl_50_49=1&subcl_126_49=1&subcl_51_49=1&subcl_52_49=1&subcl_53_49=1&subcl_54_49=1&subcl_55_49=1&subcl_56_49=1&subcl_57_49=1&subcl_58_49=1&subcl_59_49=1&subcl_60_49=1&subcl_61_49=1&subcl_62_49=1&subcl_63_49=1&subcl_64_49=1&subcl_65_49=1&cl_66=1&subcl_130_66=1&subcl_67_66=1&subcl_68_66=1&subcl_69_66=1&subcl_70_66=1&subcl_71_66=1&subcl_72_66=1&subcl_74_66=1&subcl_133_66=1&subcl_73_66=1&subcl_75_66=1&subcl_131_66=1&subcl_135_66=1&subcl_76_66=1&subcl_77_66=1&subcl_138_66=1&subcl_136_66=1&subcl_78_66=1&cl_79=1&subcl_80_79=1&subcl_81_79=1&subcl_146_79=1&subcl_82_79=1&subcl_134_79=1&subcl_83_79=1&subcl_84_79=1&subcl_85_79=1&subcl_144_79=1&subcl_86_79=1&cl_87=1&subcl_88_87=1&subcl_89_87=1&subcl_90_87=1&subcl_91_87=1&subcl_92_87=1&subcl_93_87=1&subcl_94_87=1&subcl_95_87=1&subcl_96_87=1&subcl_97_87=1&subcl_98_87=1&cl_99=1&subcl_123_99=1&subcl_100_99=1&subcl_139_99=1&subcl_101_99=1&subcl_137_99=1&subcl_102_99=1&subcl_103_99=1&subcl_104_99=1&subcl_105_99=1&subcl_106_99=1&subcl_107_99=1&subcl_108_99=1&subcl_109_99=1&subcl_110_99=1&subcl_112_99=1&subcl_113_99=1&subcl_114_99=1&cl_115=1&subcl_141_115=1&subcl_116_115=1&subcl_117_115=1&subcl_118_115=1&subcl_142_115=1&subcl_120_115=1&subcl_121_115=1&subcl_122_115=1&cl_0=1";
			$request["method"] = "get";
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$result = $r["content"];

			print "<br>\n Get Merchant List : Page: $nPageNo <br>\n";
			//parse HTML
			$strLineStart = '<div style="text-align:center;margin-top:5px;">';
			$nLineStart = 0;
			$bStart = true;
			while ($nLineStart >= 0){
				if($this->debug) 
					//print "Process $Cnt  ";		
					echo $nLineStart;
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
				//values
				$Homepage = urldecode(trim($this->oLinkFeed->ParseStringBy2Tag($result, array('<a target="_blank"', 'href="'), '">', $nLineStart)));
				$strMerName = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<h4 style="margin-bottom:5px;padding-bottom:0;">', '</h4>', $nLineStart));
				$strMerID = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<div style="margin-bottom:10px;">Code:', '</div>', $nLineStart));
				$EPCDefault = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('Vendor EPC:', '</td><td >'), '</td>', $nLineStart));
				$JoinDate = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('Date joined:', '</td><td >'), '</td>', $nLineStart));
				if($JoinDate){
					$JoinDate = date("Y-m-d H:i:s", strtotime($JoinDate));
				}
				$prgm_url = "https://secure.avangate.com/affiliates/".trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($result, array('<td nowrap colspan="2">','<a href="'), '">See Vendor Products', $nLineStart)));
				$CommissionExt = strip_tags(trim($this->oLinkFeed->ParseStringBy2Tag($result, array('Commission up to', '<tr>'), '</tr>', $nLineStart)));
				$strStatus = strip_tags(trim($this->oLinkFeed->ParseStringBy2Tag($result, array('<tr><td colspan="3" align="left">'), '</td></tr>', $nLineStart)));
				if(stripos($strStatus, "Active") !== false){
					$Partnership = "Active";				
				}elseif(stripos($strStatus, "Pending") !== false){
					$Partnership = "Pending";
				}elseif(stripos($strStatus, "Rejected") !== false){
					$Partnership = "Declined";
				}else{
					$Partnership = "NoPartnership";
				}
				//$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
				//$prgm_detail = $prgm_arr["content"];
				//$Contacts = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, 'Support email:</b>', '</li>'));
				//$ReturnDays = trim($this->oLinkFeed->ParseStringBy2Tag($prgm_detail, 'Cookie life:</b>', '<span'));
				$arr_prgm[$strMerID] = array(
					"Name" => addslashes(html_entity_decode(trim($strMerName))),
					"AffId" => $this->info["AffId"],
					//"Contacts" => $Contacts,
					//"CategoryExt" => addslashes($CategoryExt),
					"IdInAff" => addslashes($strMerID),
					"JoinDate" => $JoinDate,
					"StatusInAff" => "Active",						//'Active','TempOffline','Offline'
					"StatusInAffRemark" => addslashes($strStatus),
					"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','Removed'
					//"Description" => addslashes($desc),
					"Homepage" => addslashes($Homepage),
					"CommissionExt" => addslashes($CommissionExt),
					"EPCDefault" => addslashes(preg_replace("/[^0-9.]/", "", $EPCDefault)),
					//"CookieTime" => intval($ReturnDays),
					//"TermAndCondition" => addslashes($TermAndCondition),
					"TargetCountryExt" => '',
					//"SEMPolicyExt" => addslashes($SEMPolicyExt),
					//"BonusExt" => addslashes($BonusExt),
					"LastUpdateTime" => date("Y-m-d H:i:s"),
					"DetailPage" => $prgm_url,
					"SupportDeepUrl" => "YES"
				);
				if(count($arr_prgm) >= 100){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
				//echo '['.count($arr_prgm).']';
				$program_num++;
			}
			if($nPageNo == 1)
			{
				//try to find max page
				$max_page = intval($this->oLinkFeed->ParseStringBy2Tag($result, '<div style="font-size:12px; font-weight:bold; margin:4px 0 0 10px;">', 'vendor(s)'));
				if($max_page > $max_start) $max_start = $max_page;				
			}
			$nPageNo++;
		}//per page		
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
			$arr_prgm = array();
		}

		echo "\tGet Program by page end\r\n";
		if($program_num < 10){
			mydie("die: program count < 10, please check program.\n");
		}
		echo "\tUpdate ({$program_num}) program.\r\n";
		echo "\tSet program country int.\r\n";
		$objProgram->setCountryInt($this->info["AffId"]);
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

