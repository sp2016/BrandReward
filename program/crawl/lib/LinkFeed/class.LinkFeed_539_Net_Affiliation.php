<?php
require_once 'text_parse_helper.php';
require_once 'phpQuery.php';
require_once 'xml2array.php';
class LinkFeed_539_Net_Affiliation
{
	function __construct($aff_id,$oLinkFeed)
	{	$this->objMysql = new MysqlExt();
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);                            //返回一维数组，存储当前aff_id对应的各个字段值
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;

		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		$this->isRedirect = false;
		$this->getStatus = false;
		$this->partnership = array();
		if(SID == 'bdg01'){
			$this->API_password = "44232155CAB832F8FB06E7";
			$this->countryarr = array(
                'fr' => '436357',		//法国
                'uk' => '436529',
                'us' => '442651',
                'de' => '439517'
			);
		}else{
			$this->API_password = "44026764F50AC0382EC861";
			$this->countryarr = array(
					'' => '434511',
			);
            $this->coupon_sites = array(
                'CouponUS' => 440267,
                'CouponFR' => 440433,
                'CouponUK' => 447255,
                'CoupENCA' => 453177,
                'CoupFRCA' => 453179,
                'CouponDE' => 453181
            );
		}

	    $this->programCountryList = array();
	}

	function getCouponFeed(){
		$arr_return = array("AffectedCount" => 0,"UpdatedCount" => 0,"Detail" => array(),);
		$request = array("AffId" => $this->info["AffId"],"method" => "post","postdata" => "",);
		$url = "http://flux.netaffiliation.com/rsscp.php?sec=$this->API_password";
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r["content"];
		$programs = $this->oLinkFeed->getAllAffMerchant($this->info["AffId"], "", "MerchantName");
		preg_match_all("@<item>(.*?)</item>@ms", $content, $chapters);
		$links = array();
		foreach ($chapters[0] as $chapter){
			$link = array(
					"AffId" => $this->info["AffId"],
					"AffMerchantId" => "",
					"AffLinkId" => "",
					"LinkName" => "",
					"LinkDesc" => "",
					"LinkStartDate" => "",
					"LinkEndDate" => "",
					"LinkPromoType" => 'deal',
					"LinkHtmlCode" => '',
					"LinkCode" => "",
					"LinkOriginalUrl" => "",
					"LinkImageUrl" => "",
					"LinkAffUrl" => "",
					"DataSource" => 224,
			);
			if (preg_match('@<title>All promotion codes at the time</title>@', $chapter, $g)){
				continue;
			}
			if (preg_match('@<link><\!\[CDATA\[([^\]]+)\]\]></link>@', $chapter, $g)){
				$link["LinkAffUrl"] = $g[1];
			}
			if (preg_match('@<description><\!\[CDATA\[(.*?)\]\]@ms', $chapter, $g)){
				$link["LinkName"] = $g[1];
			}
			if (preg_match('@<code>([^<]+)</code>@ms', $chapter, $g)){
				$link["LinkCode"] = $g[1];
				$link["LinkPromoType"] = 'coupon';
			}
			if (preg_match('@<startdate>([^<]+)</startdate>@ms', $chapter, $g)){
				$link["LinkStartDate"] = parse_time_str($g[1]);
			}
			if (preg_match('@<enddate>([^<]+)</enddate>@ms', $chapter, $g)){
				$link["LinkEndDate"] = parse_time_str($g[1]);
			}
			if (preg_match('@<idcamp>(\d+)</idcamp>@ms', $chapter, $g)){
				$link["AffMerchantId"] = $g[1];
			}
			$link['LinkHtmlCode'] = create_link_htmlcode($link);
			$link['AffLinkId'] = md5("{$link['AffMerchantId']}.{$link['LinkName']}");
			if (empty($link["AffMerchantId"]) || empty($link["LinkName"]) || empty($link["LinkAffUrl"])){
				echo "$chapter\n";
				print_r($link);
				echo "unexpected format: \n";
				continue;
			}
			$arr_return["AffectedCount"] ++;
			$links[] = $link;
			if(sizeof($links) > 100)
			{
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
				$links = array();
			}
		}
		if (sizeof($links) > 0)
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		echo sprintf("get coupon by api...%s link(s) found.\n", $arr_return['AffectedCount']);
		return $arr_return;
	}

	function GetStatus(){
		$this->getStatus = true;
		$this->GetProgramFromAff();
	}

	function GetProgramFromAff()
	{
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
		$this->GetProgramByPage();		
		$this->checkProgramOffline($this->info["AffId"], $check_date);
		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}

	function GetProgramBySearchMK($request,$token,$partnerShipCode,$prgmNbPerPage,$cid){
		$objProgram = new ProgramDb();
		$activeProgram = array();
		$arrFirstPage = array();
		$program_num = 0;
		//先爬取第一页，得到分页栏中的分页个数
		$requestFirst = $request;
		$requestFirst['postdata'] =		 'postulationaff%5Bpage_courante%5D=1'
				.'&postulationaff%5Bnb_resultat_par_page%5D='.$prgmNbPerPage
				.'&postulationaff%5B_csrf_token%5D='.$token
				.'&postulationaff%5Betat_programmme%5D='.$partnerShipCode
				.'&postulationaff%5Bmots_clefs%5D='
				.'&postulationaff%5Bdate%5D='
				.'&formName=postulationaff';
		$requestFirst['addheader'] = array("X-Requested-With:XMLHttpRequest");
		$arrFirstPage = $this->oLinkFeed->GetHttpResult("https://www6.netaffiliation.com/affiliate/program/management/get-programme", $requestFirst);
		$programNum = $this->oLinkFeed->ParseStringBy2Tag($arrFirstPage['content'], '<h2 class=\"gris left\">',' result(s)');//program数
		$pageNum = ceil($programNum/$prgmNbPerPage);//页数
		for ($p=1;$p<=$pageNum;$p++){//分页循环爬取
			$nOffset = 0;
			$requestPrgm = $request;
			$requestPrgm['postdata'] =	'postulationaff%5Bpage_courante%5D='.$p
					.'&postulationaff%5Bnb_resultat_par_page%5D='.$prgmNbPerPage
					.'&postulationaff%5B_csrf_token%5D='.$token
					.'&postulationaff%5Betat_programmme%5D='.$partnerShipCode
					.'&postulationaff%5Bmots_clefs%5D='
					.'&postulationaff%5Bdate%5D='
					.'&formName=postulationaff';
			$requestPrgm['addheader'] = array("X-Requested-With:XMLHttpRequest");
			$arr = $this->oLinkFeed->GetHttpResult("https://www6.netaffiliation.com/affiliate/program/management/get-programme", $requestPrgm);
			$arr = json_decode($arr['content'], true);
			$page_content = str_replace(array("\r","\n","\t"), "", $arr['html']);
			preg_match_all('/id="prog_(\d*)">/', $page_content,$matches);
			foreach ($matches[1] as $prgm){//program循环爬取
				$idInAff = trim($this->oLinkFeed->ParseStringBy2Tag($page_content, array('id="prog_'),'">', $nOffset));$namePos = $nOffset;
				$name = trim($this->oLinkFeed->ParseStringBy2Tag($page_content, 'target="_blank">','</a>',$namePos));$namePos = $nOffset;
				$homePage = trim($this->oLinkFeed->ParseStringBy2Tag($page_content, '<a href="', '" target="_blank"',$namePos));$namePos = $nOffset;
				$contact = trim($this->oLinkFeed->ParseStringBy2Tag($page_content, '<h5>Program manager :</h5>', '</span>',$namePos));$namePos = $nOffset;

				if ($partnerShipCode == 2) {
				    $country_arr = array_flip($this->countryarr);
				    $countryCode = $country_arr[$cid];
                    if (isset($this->programCountryList[$prgm]) && !empty($this->programCountryList[$prgm])) {
                        $this->programCountryList[$prgm] .= ", $countryCode";
                    } else {
                        $this->programCountryList[$prgm] = $countryCode;
                    }
                    $country = $this->programCountryList[$prgm];
                }else{
                    $country = '';
                }
				if($partnerShipCode == '2'){
					$partnerShip = 'Active';
					$StatusInAff = 'Active';
					$this->partnership[$prgm] = 1;
				}elseif($partnerShipCode == '3'){
					$partnerShip = 'Declined';
					$StatusInAff = 'Active';
				}elseif($partnerShipCode == '-1'){
					$partnerShip = 'NoPartnership';
					$StatusInAff = 'Active';					
				}elseif($partnerShipCode == '1'){
					$partnerShip = 'Pending';
					$StatusInAff = 'Active';
				}
				if (isset($this->partnership[$prgm]))
					$partnerShip = 'Active';
				//Description需要另外爬取页面
				$requestDesc = $request;
				$requestDesc['postdata'] = 'id='.$prgm;
				$requestDesc['addheader'] = array("X-Requested-With:XMLHttpRequest");
				$arrDescPage = $this->oLinkFeed->GetHttpResult("https://www6.netaffiliation.com/affiliate/program/management/show-desc", $requestDesc);
				$description = $arrDescPage['content'];


				//commission也需要另外爬页面
				$requestCom = $request;
				$requestCom['postdata'] = 'id='.$prgm;
				$requestCom['addheader'] = array("X-Requested-With:XMLHttpRequest");
				$arrComPage = $this->oLinkFeed->GetHttpResult("https://www6.netaffiliation.com/affiliate/program/management/show-infos-rem", $requestDesc);
				$jsString = $this->oLinkFeed->ParseStringBy2Tag($arrComPage['content'], '<!--  CONTENEUR REMUNERATION   -->','<div');
				$commission = str_replace($jsString, '', $arrComPage['content']);//将commission页面中的javascript代码去除
				//判断此program是否支持deeplink
				$requestDeep = $request;
				$requestDeep['postdata'] = 'id='.$prgm;
				$requestDeep['addheader'] = array("X-Requested-With:XMLHttpRequest");
				$arrDeepPage = $this->oLinkFeed->GetHttpResult("https://www6.netaffiliation.com/affiliate/program/management/show-visual", $requestDeep);
				preg_match_all('#Deeplink#', $arrDeepPage['content'],$matches);
				if(isset($matches[0][0]) && $matches[0][0] == 'Deeplink'){
					$supportDeepUrl = 'YES';
				}else{
					$supportDeepUrl = 'NO';
				}
				$arr_prgm[$prgm] = array(
						"Name" => addslashes(html_entity_decode($name)),
						"StatusInAff" => $StatusInAff,
						"AffId" => $this->info["AffId"],
						"Contacts" => addslashes($contact),
						"TargetCountryExt" => addslashes($country),
						"IdInAff" => $prgm,
						"Partnership" => $partnerShip,						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
						"Description" => addslashes($description),
						"Homepage" => addslashes(trim($homePage)),
						"LastUpdateTime" => date("Y-m-d H:i:s"),
						"CommissionExt" => addslashes(trim($commission)),
						"SupportDeepUrl" => $supportDeepUrl,
				);
				$program_num ++;
				echo $program_num . "\t";
			}
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
		}
		return $program_num;
	}

    function GetProgramBySearchBR($request,$token,$partnerShipCode,$prgmNbPerPage,$cid){
        $this->GetDefultUrlByApi();
        $objProgram = new ProgramDb();
        $program_num = 0;
        //先爬取第一页，得到分页栏中的分页个数
        $requestFirst = $request;
        $requestFirst['postdata'] =		 'postulationaff%5Bpage_courante%5D=1'
            .'&postulationaff%5Bnb_resultat_par_page%5D='.$prgmNbPerPage
            .'&postulationaff%5B_csrf_token%5D='.$token
            .'&postulationaff%5Betat_programmme%5D='.$partnerShipCode
            .'&postulationaff%5Bmots_clefs%5D='
            .'&postulationaff%5Bdate%5D='
            .'&formName=postulationaff';
        $showReq = $request;
        $showReq['addheader'] = array("X-Requested-With:XMLHttpRequest");

        $requestFirst['addheader'] = array("X-Requested-With:XMLHttpRequest");
        $arrFirstPage = $this->oLinkFeed->GetHttpResult("https://www6.netaffiliation.com/affiliate/program/management/get-programme", $requestFirst);
        $programNum = $this->oLinkFeed->ParseStringBy2Tag($arrFirstPage['content'], '<h2 class=\"gris left\">',' result(s)');//program数
        $pageNum = ceil($programNum/$prgmNbPerPage);//页数
        for ($p=1;$p<=$pageNum;$p++){//分页循环爬取
            $nOffset = 0;
            $requestPrgm = $request;
            $requestPrgm['postdata'] =	'postulationaff%5Bpage_courante%5D='.$p
                .'&postulationaff%5Bnb_resultat_par_page%5D='.$prgmNbPerPage
                .'&postulationaff%5B_csrf_token%5D='.$token
                .'&postulationaff%5Betat_programmme%5D='.$partnerShipCode
                .'&postulationaff%5Bmots_clefs%5D='
                .'&postulationaff%5Bdate%5D='
                .'&formName=postulationaff';
            $requestPrgm['addheader'] = array("X-Requested-With:XMLHttpRequest");
            $arr = $this->oLinkFeed->GetHttpResult("https://www6.netaffiliation.com/affiliate/program/management/get-programme", $requestPrgm);
            $arr = json_decode($arr['content'], true);
            $page_content = str_replace(array("\r","\n","\t"), "", $arr['html']);
            preg_match_all('/id="prog_(\d*)">/', $page_content,$matches);
            foreach ($matches[1] as $prgm){//program循环爬取
                $idInAff = trim($this->oLinkFeed->ParseStringBy2Tag($page_content, array('id="prog_'),'">', $nOffset));$namePos = $nOffset;
                $name = trim($this->oLinkFeed->ParseStringBy2Tag($page_content, 'target="_blank">','</a>',$namePos));$namePos = $nOffset;
                $homePage = trim($this->oLinkFeed->ParseStringBy2Tag($page_content, '<a href="', '" target="_blank"',$namePos));$namePos = $nOffset;
                $contact = trim($this->oLinkFeed->ParseStringBy2Tag($page_content, '<h5>Program manager :</h5>', '</span>',$namePos));$namePos = $nOffset;

                if($partnerShipCode == '2'){
                    $partnerShip = 'Active';
                    $StatusInAff = 'Active';
                    $this->partnership[$prgm] = 1;
                }elseif($partnerShipCode == '3'){
                    $partnerShip = 'Declined';
                    $StatusInAff = 'Active';
                }elseif($partnerShipCode == '-1'){
                    $partnerShip = 'NoPartnership';
                    $StatusInAff = 'Active';
                }elseif($partnerShipCode == '1'){
                    $partnerShip = 'Pending';
                    $StatusInAff = 'Active';
                }
                if (isset($this->partnership[$prgm]))
                    $partnerShip = 'Active';
                //Description需要另外爬取页面
                $requestDesc = $request;
                $requestDesc['postdata'] = 'id='.$prgm;
                $requestDesc['addheader'] = array("X-Requested-With:XMLHttpRequest");
                $arrDescPage = $this->oLinkFeed->GetHttpResult("https://www6.netaffiliation.com/affiliate/program/management/show-desc", $requestDesc);
                $description = $arrDescPage['content'];


                //commission也需要另外爬页面
                $requestCom = $request;
                $requestCom['postdata'] = 'id='.$prgm;
                $requestCom['addheader'] = array("X-Requested-With:XMLHttpRequest");
                $arrComPage = $this->oLinkFeed->GetHttpResult("https://www6.netaffiliation.com/affiliate/program/management/show-infos-rem", $requestDesc);
                $jsString = $this->oLinkFeed->ParseStringBy2Tag($arrComPage['content'], '<!--  CONTENEUR REMUNERATION   -->','<div');
                $commission = str_replace($jsString, '', $arrComPage['content']);//将commission页面中的javascript代码去除
                //判断此program是否支持deeplink
                $requestDeep = $request;
                $requestDeep['postdata'] = 'id='.$prgm;
                $requestDeep['addheader'] = array("X-Requested-With:XMLHttpRequest");
                $arrDeepPage = $this->oLinkFeed->GetHttpResult("https://www6.netaffiliation.com/affiliate/program/management/show-visual", $requestDeep);
                preg_match_all('#Deeplink#', $arrDeepPage['content'],$matches);
                if(isset($matches[0][0]) && $matches[0][0] == 'Deeplink'){
                    $supportDeepUrl = 'YES';
                }else{
                    $supportDeepUrl = 'NO';
                }

                //将同一个商家的program，根据不同的coupon站点拆分为不同的program，并得到他们的affdefaulturl。
                $showReq["postdata"] = "id=$idInAff&etatprog=2";
                $showResult = $this->oLinkFeed->GetHttpResult('https://www6.netaffiliation.com/affiliate/program/management/show', $showReq);
                $showResult = preg_replace("@>\s+<@", '><', $showResult['content']);
                $listStr = $this->oLinkFeed->ParseStringBy2Tag($showResult, array('Brandreward Coupon ', 'tr>'), '<tr class="even"');
                $listArr = explode('</tr><tr', $listStr);
                if (empty($listArr)){
                    mydie("The page have changed, please check it.");
                }
                foreach ($listArr as $site) {
                    $partnerShipSite = $partnerShip;
                    $siteStrArr = explode('</td><td', $site);
                    $couponCode = trim($this->oLinkFeed->ParseStringBy2Tag($siteStrArr[1], 'Brandreward ', '<'));
                    if (!isset($this->coupon_sites[$couponCode])) {
                        mydie("Find new coupon country: " . $couponCode);
                    }
                    $programId = $idInAff . '_' . $this->coupon_sites[$couponCode];
                    $country = substr($couponCode, -2, 2);

                    $AffDefaultUrl = '';
                    if (isset($this->AffDefaultUrlList[$programId])) {
                        $AffDefaultUrl = $this->AffDefaultUrlList[$programId];
                    }

                    if (stripos($siteStrArr[5], 'id="postulationSite') === false && $partnerShipSite == 'Active') {
                        $partnerShipSite = 'NoPartnership';
                    }

                    $arr_prgm[$programId] = array(
                        "Name" => addslashes(html_entity_decode($name)),
                        "StatusInAff" => $StatusInAff,
                        "AffId" => $this->info["AffId"],
                        "Contacts" => addslashes($contact),
                        "TargetCountryExt" => addslashes($country),
                        "IdInAff" => $programId,
                        "Partnership" => $partnerShipSite,                        //'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
                        "Description" => addslashes($description),
                        "Homepage" => addslashes(trim($homePage)),
                        "LastUpdateTime" => date("Y-m-d H:i:s"),
                        "CommissionExt" => addslashes(trim($commission)),
                        "SupportDeepUrl" => $supportDeepUrl,
                        "AffDefaultUrl" => addslashes($AffDefaultUrl),
                    );
                    $program_num++;
                }
                echo $program_num . "\t";
                $objProgram->updateProgram($this->info["AffId"], $arr_prgm);
            }
        }
        return $program_num;
    }

	function GetDeepLinkOfActive($request){//合作关系为Active的program的deepLink属性需要另外爬取
        $error_msg = array();
		$objProgram = new ProgramDb();
		$request_deep= $request;
		$request_deep['method'] = "get";
		$arrActive = $this->oLinkFeed->GetHttpResult("https://www6.netaffiliation.com/affiliate/publication-media",$request_deep);
		preg_match_all('#<option value=\"(\d*)\" >#', $arrActive['content'], $matches);
		$activeProgram = $matches[1];

		foreach ($activeProgram as $k => $v) {
            echo $v . "\t";
            $deepM = array();
            $request_deep['method'] = "get";
            $arr = $this->oLinkFeed->GetHttpResult("https://www6.netaffiliation.com/affiliate/publication-media?prog=" . $v, $request_deep);
            preg_match('#/affiliate/publication-media/deepLink-codeTracking\',\'{(.+)}\',#', $arr['content'], $deepM);
			if(isset($deepM[1]) && !empty($deepM[1])) {
                $parsms = json_decode('{' . $deepM[1] . '}', true);
                $request_deep['method'] = 'post';
                $request_deep['referer'] = 'https://www6.netaffiliation.com/affiliate/publication-media?prog=' . $v;
                $request_deep['postdata'] = "r=&idProg={$parsms['idProg']}&idObj={$parsms['idObj']}&idPos={$parsms['idPos']}&idElem={$parsms['idElem']}";
                $request_deep['addheader'] = array(
                    'Accept:text/html, */*; q=0.01',
                    'Accept-Encoding:gzip, deflate, br',
                    'Accept-Language:zh-CN,zh;q=0.8',
                    'Cache-Control:no-cache',
                    'Connection:keep-alive',
                    'Content-Type:application/x-www-form-urlencoded',
                    'Host:www6.netaffiliation.com',
                    'Origin:https://www6.netaffiliation.com',
                    'Pragma:no-cache',
                    'Referer:https://www6.netaffiliation.com/affiliate/publication-media?prog=' . $v,
                    'User-Agent:Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36',
                    'X-Requested-With:XMLHttpRequest'
                );
                $arr = $this->oLinkFeed->GetHttpResult("https://www6.netaffiliation.com/affiliate/publication-media/deepLink-codeTracking", $request_deep);
                if ($arr['code'] != 200) {
                    print_r($arr);
                    $error_msg[] = 'Request deepLink-codeTracking failed for ' . $v;
                }
                $deepLink = html_entity_decode($this->oLinkFeed->ParseStringBy2Tag($arr['content'], 'textOrigine = \'', '{XXX}'));
                if ($deepLink) {
                    $arrOfDeepLink[$v]['AffId'] = $this->info["AffId"];
                    $arrOfDeepLink[$v]['IdInAff'] = $v;
                    $arrOfDeepLink[$v]['AffDefaultUrl'] = $deepLink;
                    $arrOfDeepLink[$v]['LastUpdateTime'] = date("Y-m-d H:i:s");
                }
            }
        }

		if (!empty($arrOfDeepLink))
			$objProgram->updateProgram($this->info["AffId"], $arrOfDeepLink);

        if (!empty($error_msg)){
            mydie(implode("\n",$error_msg));
        }
	}

	function GetProgramByPage()
	{
		$countryarr = $this->countryarr;
		foreach($countryarr as $k){
			echo "\tGet Program by page start\r\n";
			$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);
			$objProgram = new ProgramDb();
			$program_num = 0;
			$request = array("AffId" => $this->info["AffId"],"method" => "post","postdata" => "",);
			//-------------------登陆-----------------------------------
			$request_Login = $request;
			//$request_Login['postdata'] = "login%5Bemail%5D=info%40couponsnapshot.com+&login%5Bmdp%5D=Tskkd14s7j%26d&login%5Bremember%5D=on";
			$request_Login['postdata'] = $this->info['AffLoginPostString'];
			//-------------------选择mega在联盟中登记过的站点,必须要有这一步，不然下面"postulationaff"会变成其他词，从而无法拿到token-----------------------------
			$request_chooseSite = $request;
			$request_chooseSite['postdata'] = "hidden_res_type=s&hidden_res_id=$k";
			$this->oLinkFeed->GetHttpResult("https://www6.netaffiliation.com/",$request_chooseSite);
			//-------------------点击“register a program”之后，获取token-----------
			$request_available = $request;
			$arr_token = $this->oLinkFeed->GetHttpResult("https://www6.netaffiliation.com/affiliate/program/management",$request_available);
			preg_match_all('#name=\"postulationaff\[\_csrf\_token\]\" value=\"(.*)\" id=\"postulationaff\_\_csrf\_token\" \/>#', $arr_token['content'], $matches);
			$token = $matches[1][0];
			//-------------------开始爬取-----------------------------------------------------------------
			$partnershipType = array(
					"active" => 2,
					"pending" => 1,
					"NoPartnership" => '-1',
					"Declined" => 3
			);//2代表active,1代表pending,-1代表NoPartnership，3代表Declined

			foreach ($partnershipType as $status => $v){
				echo "start get $status programs\r\n";
				if (SID == 'bdg02'){
                    $program_num += $this->GetProgramBySearchBR($request,$token,$v,9999,$k);
                }else{
                    $program_num += $this->GetProgramBySearchMK($request,$token,$v,9999,$k);
                }

				echo "finish get $status programs\r\n";
			}

            if (SID != 'bdg02') {
                $this->GetDeepLinkOfActive($request);//历史原因，暂时更新不了AffDefaultUrl字段
            }

			echo "\tGet Program by page end\r\n";
			if($program_num < 10){
				mydie("die: program count < 10, please check program.\n");
			}
			echo "\tUpdate ({$program_num}) program.\r\n";
			echo "\tSet program country int.\r\n";
		}
		//	$objProgram->setCountryInt($this->info["AffId"]);
	}

    function GetDefultUrlByApi(){                   //执行该方法前必须先登录。
        if (!empty($this->AffDefaultUrlList)) {
            return $this->AffDefaultUrlList;
        }

        $arr_return = array();
        $request = array("AffId" => $this->info["AffId"],"method" => "get","postdata" => "",);

        $site_list = $this->coupon_sites;
        foreach ($site_list as $key => $site){
            $api_url = "http://flux.netaffiliation.com/xmltrack.php?sec=2067253FBC6E6EB41EFB90&site=$site&supp=miniboutique,liens_generiques,multimedia&mode=P,S,U,D,T,G&secu=1,2";
            $result = $this->oLinkFeed->GetHttpResult($api_url,$request);
            $result = XML2Array::createArray($result['content']);
            foreach ($result['listing']['prog'] as $val){
                if (!isset($val['@attributes']['id']) || !$val['@attributes']['id']){
                    continue;
                }
                $programId = $val['@attributes']['id'] . "_" . $site;
                $deepUrl = '';
                $tag = $val['tags'];
                if (is_array($tag) && !empty($tag)){
                    if (isset($tag['liens_generiques']['element']['track']['@cdata'])) {
                        $deepUrl = $tag['liens_generiques']['element']['track']['@cdata'];
                    }else{
                        foreach ($tag['liens_generiques']['element']['track'] as $v) {
                            if (stripos($v['@cdata'], 'redir={XXX}') !== false) {
                                $deepUrl = $v['@cdata'];
                            }
                        }
                    }
                }
                if ($deepUrl){
                    $arr_return[$programId] = str_replace('{XXX}', '', $deepUrl);
                }
            }
        }
        $this->AffDefaultUrlList = $arr_return;
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

	function GetAllLinksFromAffByMerID($merinfo = array()){//只爬active状态的program
		echo "begin at :".date("Y-m-d H:i:s")."\r\n";
		$arr_return = array("AffectedCount" => 0,"UpdatedCount" => 0,);
		$request = array("AffId" => $this->info["AffId"],"method" => "post","postdata" => "",);
		$request['addheader'] = array("X-Requested-With:XMLHttpRequest");
		//-------------------登陆-----------------------------------
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"], $this->info);		
		//-------------------选择mega在联盟中登记过的站点,必须要有这一步,否则，下面的结果都爬不到-----------------------------
		
		$countryarr = $this->countryarr;
		foreach($countryarr as $country => $siteid){
			$request_chooseSite = $request;
			$request_chooseSite['postdata'] = "hidden_res_id=$siteid&hidden_type_id=1&hidden_onglet_id=1";
			$this->oLinkFeed->GetHttpResult("https://www6.netaffiliation.com/",$request_chooseSite);
			echo "switch to $country\r\n";
			$activeArr = array();
			//print_r($merinfo);
			if(count($merinfo)){
				$activeArr[] = $merinfo["AffMerchantId"];
			}else{
				$sql = 'SELECT IdInAff FROM program where AffId = '.$this->info["AffId"].' AND Partnership = "Active" and targetcountryext = "'.$country.'"';
				foreach ($this->objMysql->getRows($sql) as $v){
					$activeArr[] = $v['IdInAff'];
				}
			}
	
			//echo "count: ".count($activeArr)."\r\n";
	
			$linkType = array(
					'<span class="bloc_titre_texte">Images',
					'<span class="bloc_titre_texte">Text',
					'<span class="bloc_titre_texte">Deeplink',
					'<span class="bloc_titre_texte">Promotion code',
			);
	
			$requestProg = $request;//获取状态为active的programId
			$requestProg['method'] = 'get';
			/*$arr = $this->oLinkFeed->GetHttpResult("https://www6.netaffiliation.com/affiliate/publication-media",$requestProg);
		    $prgmStr = $this->oLinkFeed->ParseStringBy2Tag($arr['content'], '<select class="selectFiltreProg" id="selectProgsFiltresSupports">',"</select>");
		    preg_match_all('#value="(\d*)\" >#',$prgmStr, $matches);
		  	$activeArr = $matches[1]; */
	
	
	
	
	
			foreach ($activeArr as $pid){
	//			 if($pid != 49077)
	//				 continue;
				$link_table_name = "affiliate_links_" . $this->info["AffId"];
				if($this->objMysql->isTableExisting($link_table_name)){//如果linkFeed表中，已经存在此program相关link，则不再爬取此program的link了
					$sql = 'SELECT * FROM '.$link_table_name.' WHERE AffMerchantId = '.$pid;
					$this->objMysql->getRows($sql);
				}
	
				$arr = $this->oLinkFeed->GetHttpResult("https://www6.netaffiliation.com/affiliate/publication-media?prog=".$pid,$requestProg);
				phpQuery::newDocument($arr['content']);//phpQuery解析器，用于在php环境中解析文本，语法类似于jquery
				foreach (pq('.jsSupportType') as $v){//images,text,deepLink,promodeCode的循环
					if (strpos(pq($v)->html(),'<span class="bloc_titre_texte">Images') || strpos(pq($v)->html(),'<span class="bloc_titre_texte">Text') || strpos(pq($v)->html(),'<span class="bloc_titre_texte">Promotion code') || strpos(pq($v)->html(),'<span class="bloc_titre_texte">Deeplink'))
					{
						phpQuery::newDocument(pq($v)->html());
						$maxUnit = pq($v)->html();
	//					if(!strpos($maxUnit,'<span class="bloc_titre_texte">Promotion code')){
	//						continue;
	//					}
						$links = array();
						foreach (pq('.preview_contenu') as $v){//link的循环
							$minUnit = str_replace(array("\r","\n"),"",pq($v)->html());
							phpQuery::newDocument($minUnit);
							preg_match_all('#\"idObj\":\"(\d*)\"#',$minUnit,$matches);
							$idObj = $matches[1][0];
							preg_match_all('#\"idPos\":\"(\d*)\"#',$minUnit,$matches);
							$idPos = $matches[1][0];
							preg_match_all('#\"idElem\":\"(\d*)\"#',$minUnit,$matches);
							$idElem = $matches[1][0];
	
	
	
							$link['AffId'] = $this->info["AffId"];
							$link['AffMerchantId'] = $pid;
							$link['AffLinkId'] = $pid.$idObj.$idPos.$idElem;
							$link['DataSource'] = 224;
							if (strpos($maxUnit,'<span class="bloc_titre_texte">Images')){
								$link['LinkName'] = trim(pq('span.taille')->html()).trim(pq('span.nom')->html()).trim(pq('span.extension')->html());
								$link['LinkDesc'] = $link['LinkName'];
								$link['LinkPromoType'] = 'link';
							}elseif (strpos($maxUnit,'<span class="bloc_titre_texte">Text')){
								$link['LinkName'] = trim(pq('span.nom')->html());
								$link['LinkDesc'] = addslashes(pq('p')->html());
								$link['LinkPromoType'] = 'link';
							}elseif (strpos($maxUnit,'<span class="bloc_titre_texte">Promotion code')){
								preg_match_all('#<p>([\s\S]*)<br>#',$minUnit,$code);
								$link['LinkCode'] = trim($code[1][0]);
								$link['LinkName'] = addslashes(pq('.description')->html());
								$link['LinkDesc'] = addslashes(pq('.description')->html());
								$link['LinkPromoType'] = 'coupon';
							}elseif (strpos($maxUnit,'<span class="bloc_titre_texte">Deeplink')){
								$link['LinkName'] = trim(pq('span.nom')->html());
								$link['LinkDesc'] = addslashes('Authorized domain(s) :'.pq('.apercu > p > span')->html());
								$link['LinkPromoType'] = 'deeplink';
							}
							preg_match('#to(.*)PM#', pq('.validite > span')->html(),$matches);
							$link['LinkEndDate'] = isset($matches[1])?date("Y-m-d H:i:s",strtotime($matches[1])):"";//有些coupon没有结束时间
	
							$dateStr = pq('.validite > span')->html();
							$orgStartStr = $this->oLinkFeed->ParseStringBy2Tag($dateStr, "from","PM");
							$link['LinkStartDate'] = date("Y-m-d H:i:s",strtotime($orgStartStr));
	
							$link['LastUpdateTime'] = date("Y-m-d H:i:s");
	
							$requestLink = $request;
							$requestLink['postdata'] = 'idProg='.$pid.'&idObj='.$idObj.'&idPos='.$idPos.'&idElem='.$idElem;
							if (!strpos($maxUnit,'<span class="bloc_titre_texte">Deeplink')){
								$linkArr = $this->oLinkFeed->GetHttpResult("https://www6.netaffiliation.com/affiliate/publication-media/tracking-code",$requestLink);
								$link['LinkAffUrl']  = $this->oLinkFeed->ParseStringBy2Tag($linkArr['content'], '<textarea class="jsUrlClic">','</textarea>');
							}else {
								$linkArr = $this->oLinkFeed->GetHttpResult("https://www6.netaffiliation.com/affiliate/publication-media/deepLink-codeTracking",$requestLink);
								$LinkAffUrl = $this->oLinkFeed->ParseStringBy2Tag($linkArr['content'], "var textOrigine = '","';");
							}
							if(!$link['AffMerchantId'] || !$link['AffLinkId'] || !$link['LinkAffUrl'])
								continue;
							if(empty($link['LinkName']) && $link['LinkPromoType'] != 'link')
								continue;
	
							$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
							$links[] = $link;
							$arr_return['AffectedCount'] ++;
						}
						echo sprintf("get banner link...%s result(s) find.\n", count($links));
						if (count($links) > 0)
							$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
					}
	
				}
			}
		}
		echo "finish at :".date("Y-m-d H:i:s");
		return $arr_return;
	}
}

