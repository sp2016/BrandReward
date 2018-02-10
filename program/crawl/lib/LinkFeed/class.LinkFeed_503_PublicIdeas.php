<?php
require_once 'phpQuery.php';
require_once 'text_parse_helper.php';
class LinkFeed_503_PublicIdeas
{
	function __construct($aff_id,$oLinkFeed)
	{	
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);                            //返回一维数组，存储当前aff_id对应的各个字段值
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
        $this->getStatus = false;
        if (SID == "bdg01")
        	$this->site_arr = array('51461' => '38ff8d54c8e74bb024b499b07507b212');
        else
        	$this->site_arr = array('52546' => '1f02774f9221d365c7bdee9cdb2c849a');
	}

    function GetStatus()
    {
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
	
	function GetProgramBySubPage($request,$site,$action,$type,$nb_page)
	{//$site是我们自己的站点在联盟中的代号，如46631;后面三个形参是url中的参数
		echo "\tget $site $action \r\n";
		global $active_program;
		$return = $program_num = array();
		$active = array();
		$noPartnership = array();
		$objProgram = new ProgramDb();

		/*
		//模拟选择站点46631等
		//$request_chooseSite = $request;
		//$request_chooseSite['postdata'] = 'site='.$site.'&page=%2Findex.php%3F';
		//$arr_chooseSite = $this->oLinkFeed->GetHttpResult("http://publisher.publicideas.com/reconnect.php",$request_chooseSite);
		//echo "\t\tchoose site finished \r\n";
		*/

		//模拟选取国家的过程
		$request_chooseCountry = $request;
		$request_chooseCountry['postdata'] = 'country%5B%5D=DE&country%5B%5D=FR&country%5B%5D=GB';
		$arr_chooseCountry = $this->oLinkFeed->GetHttpResult("http://publisher.publicideas.com/index.php?action=country",$request_chooseCountry);
		if($arr_chooseCountry['code'] != 200){
		    mydie("choose country failed！");
        }
		echo "\t\tchoose country finished \r\n";

		//获取program的页数
		$str_url = 'http://publisher.publicideas.com/index.php?action='.$action.'&categorie_id=0&index=0&nb_page='.$nb_page.'&keyword=&type='.$type;
		$result = $this->oLinkFeed->GetHttpResult($str_url,$request);
		$matches = array();
		preg_match_all('/index=(\d*)/', $result['content'], $matches);
		$page = $matches[1];
		
		$page =  array_unique($page);		
		sort($page);//当前页的分页类的总页数
		echo "\t\tget page".count($page)." finished \t";
		//---------------------------------------------循环$page数组即是循环每一个分页页面，爬取每一个页面的数据--------------------------------------------
		if(!count($page)) $page = array(1 => 0);
		foreach ($page as $k=>$p){//分页循环
			echo "p:.$p.\t";
			if($action != 'myprograms_encours' && $action != 'myprograms_rejete'){
				$prgmArr = array();				
				$nOffset = 0;	
				if($p == 0){
					$page_content = $result['content'];					
				}else{
					$page_url = 'http://publisher.publicideas.com/index.php?action='.$action.'&categorie_id=0&nb_page='.$nb_page.'&keyword=&type='.$type.'&index='.$p;
					$page_content = $this->oLinkFeed->GetHttpResult($page_url,$request)['content'];
				}
				$page_content = mb_convert_encoding($page_content,'UTF-8','CP1252');//西欧编码转utf8
				$page_content = str_replace(array("\r","\n","\t"), "", $page_content);
				preg_match_all('#www.publicidees.com/logo/programs/logo#', $page_content, $matches);//有几个logo，就有几个program
				$programNum = count($matches[0]);//当前分页的program个数
				for($k=0;$k<$programNum;$k++){//program循环
					$name = trim($this->oLinkFeed->ParseStringBy2Tag($page_content, 'class="progTitreF">',' &laquo;</td>',$nOffset));$namePos = $nOffset;				
					$idInAff = trim($this->oLinkFeed->ParseStringBy2Tag($page_content, array('<span class="progImg">', 'logo_'), '_', $namePos));$namePos = $nOffset;//ParseStringBy2Tag函数在运行的过程中，$onOffset变量在不断地增加，$onOffset是strpos函数中的第三个变量，意思是从哪里开始搜索自字符串				
	 				$mobile = trim($this->oLinkFeed->ParseStringBy2Tag($page_content, '<li><strong>Mobiles : </strong>', '</li>',$namePos));$namePos = $nOffset;				
	 				$homePage = trim($this->oLinkFeed->ParseStringBy2Tag($page_content, array('width="120" height="60"', '<a href="'), '" target="_blank">',$namePos));$namePos = $nOffset;
	 				$country = trim($this->oLinkFeed->ParseStringBy2Tag($page_content, 'flags/16/', '.png',$namePos));$namePos = $nOffset;		
	 				$commission = trim($this->oLinkFeed->ParseStringBy2Tag($page_content,'<div style="padding:20px">', '</div>',$namePos));$namePos = $nOffset;
                    $publisherPolicy = trim($this->oLinkFeed->ParseStringBy2Tag($page_content,"<li><strong>Reduction vouchers : </strong>", '</li>',$namePos));$namePos = $nOffset;
	 				$contact = trim($this->oLinkFeed->ParseStringBy2Tag($page_content, 'height="9" />&nbsp;<a href="mailto:', '?subject=',$namePos));$namePos = $nOffset;
					if($action == 'catprog'){
						$partnerShip = 'NoPartnership';
	 				}elseif($action == 'myprograms'){
	 					$partnerShip = 'Active';
	 				}
	 				
	 				if(isset($active_program[$idInAff])){
	 					$partnerShip = 'Active';
	 				}
	 					 				
					if($partnerShip == 'Active'){
						$active_program[$idInAff] = 1;
					}
	 				
	 				$prgmArr[$idInAff]['Name'] = addslashes($name);
	 				$prgmArr[$idInAff]['IdInAff'] = $idInAff;
	 				$prgmArr[$idInAff]['AffId'] = $this->info["AffId"];
	 				$prgmArr[$idInAff]['Partnership'] = addslashes($partnerShip);
	 				$prgmArr[$idInAff]['StatusInAff'] = 'Active';
	 				$prgmArr[$idInAff]['MobileFriendly'] = ($mobile == 'Yes') ? 'Yes' : 'No';
	 				$prgmArr[$idInAff]['Homepage'] = addslashes($homePage);				
	 				$prgmArr[$idInAff]['TargetCountryExt'] = addslashes($country);
	 				$prgmArr[$idInAff]['CommissionExt'] = addslashes($commission);
	 				$prgmArr[$idInAff]['Contacts'] = 'Email: '.addslashes($contact);
	 				$prgmArr[$idInAff]['LastUpdateTime'] = date("Y-m-d H:i:s");
                    $prgmArr[$idInAff]['PublisherPolicy'] = 'Reduction vouchers : ' . addslashes($publisherPolicy);

// 	 				if($action == 'myprograms'){
// 	 					$active[] = $idInAff;
// 	 				}
// 	 				if($action == 'catprog'){
// 	 					$noPartnership[] = $idInAff;
// 	 				}
					$program_num[] = $idInAff;
				}

				$objProgram->updateProgram($this->info["AffId"], $prgmArr);
			}
// 			else{//$action == 'myprograms_encours'和$action == 'myprograms_rejete'两种情况
// 				$nOffset = 0;
// 				$page_url = 'http://publisher.publicideas.com/index.php?action='.$action.'&categorie_id=0&nb_page='.$nb_page.'&keyword=&type='.$type.'&index='.$p;				
// 				$page_content = $this->oLinkFeed->GetHttpResult($page_url,$request)['content'];
// 				$page_content = mb_convert_encoding($page_content,'UTF-8','CP1252');//西欧编码转utf8
// 				$page_content = str_replace(array("\r","\n","\t"), "", $page_content);
// 				preg_match_all('#<div class="bloc" >#', $page_content, $matches);
// 				$programNum = count($matches[0]);//当前分页的program个数
// 				for($k=0;$k<$programNum;$k++){
					
// 				}
// 			}
		}
		echo "\r\n";
// 		if($action == 'myprograms'){
// 			return $active;//返回当前合作关系为active的IdInAff
// 		}
// 		if($action == 'catprog'){
// 			return $noPartnership;
// 		}
		return $program_num;
	}
	
	function GetProgramByXml($url,$site)
	{//主要用于爬取description
		$objProgram = new ProgramDb();
		$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"$site" . ".dat","cache_merchant");//返回.cache文件的路径
		if(!$this->oLinkFeed->fileCacheIsCached($cache_file)) //fileCacheIsCached函数检查$cache_file是否存在
		{
			$request["method"] = "get";
			$r = $this->oLinkFeed->GetHttpResult($url,$request);
			$result = $r["content"];//所有“csv文件中的program信息”
			$result = mb_convert_encoding($result,'UTF-8','CP1252');//西欧编码转utf8
			$this->oLinkFeed->fileCachePut($cache_file,$result);//生成.cache文件,并将cvs数据写入此文件
		}
		if(!file_exists($cache_file)) mydie("die: merchant csv file does not exist. \n");
		$nOffset = 0;
		//Open xml File
		$xml = simplexml_load_file($cache_file);
		$prgmArr = array();
		for ($k=0;$k<count($xml->program);$k++){
			$program = $xml->program[$k];
			$IdInAff =  (array)$program['id'];//取attributes value
			$prgmArr[$IdInAff[0]]['IdInAff'] = $IdInAff[0];
			
 			
			$Description = (array)$program->program_description;
 			$str = (STRING)$Description[0];
 			$prgmArr[$IdInAff[0]]['Description'] = addslashes($str);
 			$prgmArr[$IdInAff[0]]['AffId'] = $this->info["AffId"];
 			$prgmArr[$IdInAff[0]]['LastUpdateTime'] = date("Y-m-d H:i:s");

			if(count($prgmArr) >= 100){//当$arr_prgm数组中的记录数大于100，开始往数据库中插入或更新			
				$objProgram->updateProgram($this->info["AffId"], $prgmArr);
				$prgmArr = array();
			}
		}
		if(count($prgmArr)){//记录数也许不是100的整数，所有会有余下的没有插进数据库的记录，在这里进行处理			
			$objProgram->updateProgram($this->info["AffId"], $prgmArr);
			$prgmArr = array();
		}
		
	}
	
	function GetProgramByPage()
	{
		echo "\tGet Program by page start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		$request = array("AffId" => $this->info["AffId"],"method" => "post","postdata" => "",);
		//$this->oLinkFeed->clearHttpInfos($this->info["AffId"]);//re-login each time
		//-------------------第一次登陆，获取h值，h值是第二次登陆的postdata的一部分-----------------------------------
		$request_firstLogin = $request;
		$request_firstLogin['postdata'] = "loginAff=".urlencode($this->info["Account"])."&passAff=".urlencode($this->info["Password"])."&site=pi&userType=aff";
		$arr = $this->oLinkFeed->GetHttpResult("https://performance.timeonegroup.com/logmein.php",$request_firstLogin);
		//print_r($arr);exit;
		$h = json_decode($arr['content'])->h;
		//-------------------第二次登陆，登录前，先将info数组中的AffLoginPostString值，加上&h=----------------------------------
		$this->info['AffLoginPostString'] = $this->info['AffLoginPostString'].'&h='.urlencode($h);
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);//登录成功，返回true	
		//-------------------开始爬取以及数据库操作--------------------------------
		
		//$site_arr = array('45916' => '2c4bcac39fe2e584ac9a4127c52f62b6', '46772' => 'eab0d549287d9bdeb222ef0ecc6290ec', '46631' => '028091e97a76ae26912388c34ee82e83');
		$site_arr = $this->site_arr;		
		global $active_program;
		$active_program = array();
		
		foreach($site_arr as $siteid => $s_key){
			echo "\tprocess site:$siteid myprograms\r\n";
			$program_num_myprograms = $this->GetProgramBySubPage($request, $siteid, 'myprograms', '', '100');
			echo "\tprocess site:$siteid catprog\r\n";
			$program_num_catprog = $this->GetProgramBySubPage($request, $siteid, 'catprog', 'search', '10');
			$program_num_arr = array_merge($program_num_myprograms,$program_num_catprog);
			$program_num += count($program_num_arr);
            if(!$this->getStatus) {
                echo "\tprocess site:$siteid xml\r\n";
                $this->GetProgramByXml("http://publisher.publicideas.com/xmlProgAff.php?partid={$siteid}&key={$s_key}&noDownload=yes", $siteid);
            }
	// 		$active_46772 = $this->GetProgramBySubPage($request, '46772', 'myprograms', '', '5');
	// 		$diff = array_diff($active_46772,$active_45916);
	// 		echo "<pre>";
	// 		print_r($diff);
	// 		exit;	
	// 		$active = array_merge($active_45916,$active_46772);
	// 		$active = array_unique($active);		
	// 		$partnership_46631 = $this->GetProgramBySubPage($request, '46631', 'catprog', '', '10');//前两步所有数据的合作关系，都变成noPartnership
	// 		$diff = array_diff($partnership_46631,$active);
	// 		echo "<pre>";
	// 		print_r($diff);
	// 		exit;
		}
		
		echo "\tGet Program by page end\r\n";
		if($program_num < 10){
			mydie("die: program count < 10, please check program.\n");
		}
		echo "\tUpdate ({$program_num}) program.\r\n";
		echo "\tSet program country int.\r\n";
		//$objProgram->setCountryInt($this->info["AffId"]);
	}//function

	function checkProgramOffline($AffId, $check_date)
	{
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
	
	function GetAllLinksByAffId()
	{
		$check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, );
		$arr_merchant = $this->oLinkFeed->getApprovalAffMerchant($this->info["AffId"]);
		$mySqlExt = new MysqlExt();
		$arr_prgm = array();
		$program_num = 0;
		//$site_arr = array('45916', '46772', '46631');
		$site_arr = $this->site_arr;
		$request = array("AffId" => $this->info["AffId"],"method" => "post","postdata" => "",);
		//第一次登陆，获取h值，h值是第二次登陆的postdata的一部分
		$request_firstLogin = $request;
		$request_firstLogin['postdata'] = "loginAff=".urlencode($this->info["Account"])."&passAff=".urlencode($this->info["Password"])."&site=pi&userType=aff";
		$arr = $this->oLinkFeed->GetHttpResult("https://performance.timeonegroup.com/logmein.php",$request_firstLogin);
		$h = json_decode($arr['content'])->h;
		//第二次登陆，登录前，先将info数组中的AffLoginPostString值，加上&h=
		$this->info['AffLoginPostString'] = $this->info['AffLoginPostString'].'&h='.urlencode($h);
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);//登录成功，返回true
		$request_chooseSite = $request;
		foreach ($site_arr as $site => $key){      //美戈注册站点的循环
			//  		$site = '46772';
			//模拟选择站点46631等,必须有这一步，因为各个注册站点对应的链接，打开是不同的结果
			$request_chooseSite['postdata'] = 'site='.$site.'&page=%2Findex.php%3F';
			$arr_chooseSite = $this->oLinkFeed->GetHttpResult("http://publisher.publicideas.com/reconnect.php",$request_chooseSite);
			$progNumRequest = $request;
			$progNumRequest['method'] = 'get';
	
			//开始爬取deepLink
			$arr = $this->oLinkFeed->GetHttpResult("http://publisher.publicideas.com/index.php?action=lien_profond",$progNumRequest);
			preg_match_all('#<option value=\"(\d+)#',$arr['content'],$matches);//$matches[1]存有所有IdInAff
			unset($matches[1][0]);
			unset($matches[1][1]);
			unset($matches[1][2]);
			$deepRequest = $request;
			$deepLink['AffId'] = $this->info["AffId"];
			$deepLink['LinkPromoType'] = 'deeplink';
			$links = array();
			foreach ($matches[1] as $pid){
				$deepLink['AffMerchantId'] = $pid;
				$deepLink['AffLinkId'] = $pid.$site;
				$deepLink['Type'] = 'link';
				$sqlName = 'SELECT `Name` FROM program WHERE AffId = '.$this->info["AffId"].' AND IdInAff = '.$pid;
				$deepLink['LinkName'] = $mySqlExt->getFirstRowColumn($sqlName);
				if(empty($deepLink['LinkName']))
					continue;
				$deepLink['LinkDesc'] = $deepLink['LinkName'];
				$deepLink['DataSource'] = 359;
				$deepLink['LinkAffUrl'] = 'http://tracking.publicidees.com/clic.php?progid='.$pid.'&partid='.$site.'&dpl=';
				$links[] = $deepLink;
				$arr_return["AffectedCount"] ++;
				if (count($links) > 100){
					$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
					$links = array();
				}
			}
			if (count($links) > 0)
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
			echo 'Get All '.count($links).' deepLink';
			$links = array();
	
				
			//banner,text,coupon的爬取
			$action = array('showban','showlink','showvoucher');
			
			foreach ($arr_merchant as $AffMerchantId => $merinfo){
				foreach ($action as $act){//action循环
					$arr = $this->oLinkFeed->GetHttpResult("http://publisher.publicideas.com/index.php?action=".$act."&progid=$merinfo[IdInAff]",$progNumRequest);
					echo "http://publisher.publicideas.com/index.php?action=".$act."&progid=$merinfo[IdInAff]".PHP_EOL;
					phpQuery::newDocument($arr['content']);
						
		
					$link['AffId'] = $this->info["AffId"];
					$link['AffMerchantId'] = $merinfo['IdInAff'];
					$link['LinkPromoType'] = 'link';
					$link['AffLinkId'] = '';
						
					if ($act == 'showban'){
						foreach (pq('.blocBlanc') as $v){//link循环
							$minUnit = pq($v)->html();
							phpQuery::newDocument($minUnit);
							preg_match_all("#promoid=(\d*)#", $minUnit,$matches);
							$promoId = $matches[1][0];
							$link['AffLinkId'] = $merinfo['IdInAff'].$site.$promoId;
							$temp_linkname = pq('b')->html();
							$temp_linkname = @iconv('iso-8859-15', 'utf-8', $temp_linkname);
							$link['LinkName'] = $temp_linkname;
							$temp_desc = pq('b')->html();
							$temp_desc = @iconv('iso-8859-15', 'utf-8', $temp_desc);
							$link['LinkDesc'] = $temp_desc;
							$link['LinkAffUrl'] = 'http://tracking.publicidees.com/clic.php?partid='.$site.'&progid='.$merinfo['IdInAff'].'&promoid='.$promoId;
							$link['LinkImageUrl'] = 'http://tracking.publicidees.com/banner.php?partid='.$site.'&progid='.$merinfo['IdInAff'].'&promoid='.$promoId;
							$link['LinkHtmlCode'] = create_link_htmlcode($link);
							$link['Type'] = 'link';
							$link['DataSource'] = 359;
							if(!$link['AffMerchantId'] || !$link['AffLinkId'] || !$link['LinkAffUrl'])
								continue;
							$links[] = $link;
							$arr_return["AffectedCount"] ++;
						}
					}elseif ($act == 'showlink'){
						foreach (pq('.bloc') as $v){
							$minUnit = pq($v)->html();
							phpQuery::newDocument($minUnit);
							preg_match_all("#promoid=(\d*)#", $minUnit,$matches);
							$promoId = $matches[1][0];
							$link['AffLinkId'] = $merinfo['IdInAff'].$site.$promoId;
							$temp_linkname = pq('span')->html();
							$temp_linkname = @iconv('iso-8859-15', 'utf-8', $temp_linkname);
							$link['LinkName'] = $temp_linkname;
							preg_match_all('#<\/span>(.*)#', $minUnit,$matches);
							$temp_desc = $matches[1][0];
							$temp_desc = @iconv('iso-8859-15', 'utf-8', $temp_desc);
							$link['LinkDesc'] = $temp_desc;
							$link['Type'] = 'link';
							$link['DataSource'] = 359;
							$link['LinkAffUrl'] = 'http://tracking.publicidees.com/clic.php?partid='.$site.'&progid='.$merinfo['IdInAff'].'&promoid='.$promoId;
							$link['LinkHtmlCode'] = create_link_htmlcode($link);
							if(!$link['AffMerchantId'] || !$link['AffLinkId'] || !$link['LinkAffUrl'])
								continue;
							$links[] = $link;
							$arr_return["AffectedCount"] ++;
						}
					}elseif ($act == 'showvoucher'){
						foreach (pq('.bloc') as $v){
							$minUnit = pq($v)->html();
							phpQuery::newDocument($minUnit);
							preg_match_all("#promoid=(\d*)#", $minUnit,$matches);
							$promoId = $matches[1][0];
							$link['AffLinkId'] = $merinfo['IdInAff'].$site.$promoId;
							$temp_linkname = pq('span')->html();
							$temp_linkname = @iconv('iso-8859-15', 'utf-8', $temp_linkname);
							$link['LinkName'] = $temp_linkname;
							preg_match_all('#<\/span>([\s\S]*)#', $minUnit,$m);
							$temp_desc = @iconv('iso-8859-15', 'utf-8', $m[1][0]);
							$link['LinkDesc'] = $temp_desc;
							$link['LinkAffUrl'] = 'http://tracking.publicidees.com/clic.php?partid='.$site.'&progid='.$merinfo['IdInAff'].'&promoid='.$promoId;
							$link['LinkHtmlCode'] = create_link_htmlcode($link);
							$link['LinkPromoType'] = "coupon";
							$link['Type'] = 'promotion';
							$link['DataSource'] = 359;
							if(!$link['AffMerchantId'] || !$link['AffLinkId'] || !$link['LinkAffUrl'])
								continue;
							$links[] = $link;
							$arr_return["AffectedCount"] ++;
						}
					}
					//print_r($links);exit;
					if (count($links) > 100){
						$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
						$links = array();
					}
						
				}
				echo 'program:'.$merinfo['IdInAff'].'\n';
				echo sprintf("get bannerLink, textLink or coupon...%s result(s) find.\n", count($links));
			}
			if (count($links) > 0) {
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
				$links = array();
			}
		}
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, '');
		return $arr_return;
	}
	
/* 	function GetAllLinksFromAffByMerID($merinfo = array()){
		//if(count($merinfo)) return;
		$mySqlExt = new MysqlExt();
		//$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		//$site_arr = array('45916', '46772', '46631');
		$site_arr = $this->site_arr;
		$request = array("AffId" => $this->info["AffId"],"method" => "post","postdata" => "",);
		//$this->oLinkFeed->clearHttpInfos($this->info["AffId"]);//re-login each time
		//第一次登陆，获取h值，h值是第二次登陆的postdata的一部分
		$request_firstLogin = $request;
		$request_firstLogin['postdata'] = "loginAff=".urlencode($this->info["Account"])."&passAff=".urlencode($this->info["Password"])."&site=pi&userType=aff";
		$arr = $this->oLinkFeed->GetHttpResult("https://performance.timeonegroup.com/logmein.php",$request_firstLogin);
		$h = json_decode($arr['content'])->h;
		//第二次登陆，登录前，先将info数组中的AffLoginPostString值，加上&h=
		$this->info['AffLoginPostString'] = $this->info['AffLoginPostString'].'&h='.urlencode($h);
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);//登录成功，返回true	
		$request_chooseSite = $request;
 	 	foreach ($site_arr as $site => $key){      //美戈注册站点的循环
//  		$site = '46772';
 	 		//模拟选择站点46631等,必须有这一步，因为各个注册站点对应的链接，打开是不同的结果
 			$request_chooseSite['postdata'] = 'site='.$site.'&page=%2Findex.php%3F';
 			$arr_chooseSite = $this->oLinkFeed->GetHttpResult("http://publisher.publicideas.com/reconnect.php",$request_chooseSite);
 			$progNumRequest = $request;
 			$progNumRequest['method'] = 'get';

 			//开始爬取deepLink
 			$arr = $this->oLinkFeed->GetHttpResult("http://publisher.publicideas.com/index.php?action=lien_profond",$progNumRequest);
 			preg_match_all('#<option value=\"(\d+)#',$arr['content'],$matches);//$matches[1]存有所有IdInAff
 			unset($matches[1][0]);
 			unset($matches[1][1]);
 			unset($matches[1][2]);
 			$deepRequest = $request;
 			$deepLink['AffId'] = $this->info["AffId"];
 			$deepLink['LinkPromoType'] = 'deeplink';
 			$links = array();
			foreach ($matches[1] as $pid){
				$deepLink['AffMerchantId'] = $pid;
				$deepLink['AffLinkId'] = $pid.$site;
				$sqlName = 'SELECT `Name` FROM program WHERE AffId = '.$this->info["AffId"].' AND IdInAff = '.$pid;
				$deepLink['LinkName'] = $mySqlExt->getFirstRowColumn($sqlName);
				$deepLink['LinkDesc'] = $deepLink['LinkName'];
				$deepLink['LinkAffUrl'] = 'http://tracking.publicidees.com/clic.php?progid='.$pid.'&partid='.$site.'&dpl=';
				$links[] = $deepLink;
				
				if (count($links) > 100){
					$this->oLinkFeed->UpdateLinkToDB($links);
					$links = array();
				}
			}
			if (count($links) > 0)
				$this->oLinkFeed->UpdateLinkToDB($links);
			echo 'Get All '.count($links).' deepLink';
			$links = array();

 			 
 			//banner,text,coupon的爬取
 			//$arr = $this->oLinkFeed->GetHttpResult("http://publisher.publicideas.com/index.php?action=myprograms&index=0&nb_page=9999&keyword=&type=",$progNumRequest);
			//preg_match_all('#id=\"progRevers(\d*)Img#',$arr['content'],$matches);//$matches[1]存有所有IdInAff
			//foreach ($matches[1] as $pid){//program的循环
				$action = array('showban','showlink','showvoucher');
				$links = array();
				foreach ($action as $act){//action循环
					$arr = $this->oLinkFeed->GetHttpResult("http://publisher.publicideas.com/index.php?action=".$act."&progid=$merinfo[IdInAff]",$progNumRequest);
					echo "http://publisher.publicideas.com/index.php?action=".$act."&progid=$merinfo[IdInAff]".PHP_EOL;
					phpQuery::newDocument($arr['content']);
					
				
					$link['AffId'] = $this->info["AffId"];
					$link['AffMerchantId'] = $merinfo['IdInAff'];
					$link['LinkPromoType'] = 'link';
					$link['AffLinkId'] = '';
					
					if ($act == 'showban'){					
						foreach (pq('.blocBlanc') as $v){//link循环
						    $minUnit = pq($v)->html();
							phpQuery::newDocument($minUnit);
							preg_match_all("#promoid=(\d*)#", $minUnit,$matches);
							$promoId = $matches[1][0];
							$link['AffLinkId'] = $merinfo['IdInAff'].$site.$promoId;
							$link['LinkName'] = pq('b')->html();
							$link['LinkDesc'] = pq('b')->html();				
							$link['LinkAffUrl'] = 'http://tracking.publicidees.com/clic.php?partid='.$site.'&progid='.$merinfo['IdInAff'].'&promoid='.$promoId;
							$link['LinkImageUrl'] = 'http://tracking.publicidees.com/banner.php?partid='.$site.'&progid='.$merinfo['IdInAff'].'&promoid='.$promoId;
							$link['LinkHtmlCode'] = create_link_htmlcode($link);
							$links[] = $link;
						}
					}elseif ($act == 'showlink'){
						foreach (pq('.bloc') as $v){
							$minUnit = pq($v)->html();
							phpQuery::newDocument($minUnit);
							preg_match_all("#promoid=(\d*)#", $minUnit,$matches);
							$promoId = $matches[1][0];
							$link['AffLinkId'] = $merinfo['IdInAff'].$site.$promoId;
							$link['LinkName'] = pq('span')->html();
							preg_match_all('#<\/span>(.*)#', $minUnit,$matches);
							$link['LinkDesc'] = $matches[1][0];
							$link['LinkAffUrl'] = 'http://tracking.publicidees.com/clic.php?partid='.$site.'&progid='.$merinfo['IdInAff'].'&promoid='.$promoId;
							$link['LinkHtmlCode'] = create_link_htmlcode($link);
							$links[] = $link;
						}						
					}elseif ($act == 'showvoucher'){
						foreach (pq('.bloc') as $v){
							$minUnit = pq($v)->html();
							phpQuery::newDocument($minUnit);
							preg_match_all("#promoid=(\d*)#", $minUnit,$matches);
							$promoId = $matches[1][0];
							$link['AffLinkId'] = $merinfo['IdInAff'].$site.$promoId;
							$link['LinkName'] = pq('span')->html();
							preg_match_all('#<\/span>([\s\S]*)#', $minUnit,$m);
							$link['LinkDesc'] = $m[1][0];
							$link['LinkAffUrl'] = 'http://tracking.publicidees.com/clic.php?partid='.$site.'&progid='.$merinfo['IdInAff'].'&promoid='.$promoId;
							$link['LinkHtmlCode'] = create_link_htmlcode($link);
							$link['LinkPromoType'] = "COUPON";
							$link['Type'] = 'promotion';
							$links[] = $link;
							print_r($links);
						}
					}
					if(!$link['AffMerchantId'] || !$link['AffLinkId'] || !$link['LinkAffUrl'])
						continue;
					if (count($links) > 100){
						$this->oLinkFeed->UpdateLinkToDB($links);
						$links = array();
					}
					
				}
				echo 'program:'.$merinfo['IdInAff'].'\n';
				echo sprintf("get bannerLink, textLink or coupon...%s result(s) find.\n", count($links));
				if (count($links) > 0) {
					$this->oLinkFeed->UpdateLinkToDB($links);
					$links = array();
				}
			//}//program循环结束
 		 } 
	} */
}

