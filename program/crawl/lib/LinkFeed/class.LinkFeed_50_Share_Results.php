<?php
class LinkFeed_50_Share_Results
{
	var $info = array(
		"ID" => "50",
		"Name" => "Share Results",
		"IsActive" => "YES",
		"ClassName" => "LinkFeed_50_Share_Results",
		"LastCheckDate" => "1970-01-01",
	);
	
	function LoginIntoAffService()
	{
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "",);
		$r = $this->oLinkFeed->GetHttpResult("https://www.shareresults.com/affiliates/login.php", $request);
		$result = $r["content"];
		$token = trim($this->oLinkFeed->ParseStringBy2Tag($result, array("name='token'", "value='"), "'"));

		$request = array("AffId" => $this->info["AffId"], 
			"method" => "post",
			"postdata" => "username=".$this->info['Account']."&password=".$this->info['Password']."&token={$token}&submit=Log+In",
		);
		$r = $this->oLinkFeed->GetHttpResult("https://www.shareresults.com/affiliates/login.php", $request);
	}

	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
	}
	
	function getCouponFeed()
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		return $arr_return;
	}
	
	function GetAllLinksFromAffByMerID($merinfo)
	{
		$exists = $this->oLinkFeed->getAllLinksByAffAndMerchant($this->info['AffId'], $merinfo['IdInAff']);
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		$request = array("AffId" => $this->info["AffId"], "method" => "post", "postdata" => "");

		#$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info, 1, false);
		$this->LoginIntoAffService();

		$url = 'https://www.shareresults.com/affiliates/creatives/creativelist.php';
		$request['postdata'] = 'merchantid=' . $merinfo['IdInAff'];
		$r = $this->oLinkFeed->GetHttpResult($url, $request);
		$content = $r['content'];
		preg_match_all('@<td><a href="(.*?)" class="tableofcontents"@', $content, $pages);
		$links = array();
		if (!empty($pages) && !empty($pages[1]) && is_array($pages[1]))
		{
			foreach ($pages[1] as $page)
			{
				if (preg_match('@type=(\d+)\&@', $page, $g))
				{
					if (!($g[1] == '1' || $g[1] == '2' || $g[1] == '3'))
						continue;
				}
				else 
					continue;
				
				$url = 'https://www.shareresults.com/affiliates/creatives/' . $page;
				$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "");
				$r = $this->oLinkFeed->GetHttpResult($url, $request);
				$content = $r['content'];
				if (empty($content))
					continue;
				preg_match_all('@createWindow\(\'(.*?)\'@', $content, $data);
				if (!empty($data) && !empty($data[1]) && is_array($data[1]))
				{
					foreach ($data[1] as $v)
					{
						if (preg_match('@cid=(\d+)@', $v, $g))
							$id = $g[1];
						else
							continue;
						if (empty($id))
							continue;
						if (key_exists($id, $exists))
						{
							echo sprintf("id:%s exists ignored.\n", $id);
							continue;
						}
						$url = 'https://www.shareresults.com/affiliates/creatives/' . $v;
						$r = $this->oLinkFeed->GetHttpResult($url, $request);
						$content = $r['content'];
						if (empty($content))
							continue;
						$link = array(
								"AffId" => $this->info["AffId"],
								"AffMerchantId" => $merinfo['IdInAff'],
								"AffLinkId" => $id,
								"LinkName" => '',
								"LinkDesc" => '',
								"LinkStartDate" => '0000-00-00 00:00:00',
								"LinkEndDate" => '0000-00-00 00:00:00',
								"LinkPromoType" => 'DEAL',
								"LinkHtmlCode" => '',
								"LinkOriginalUrl" => "",
								"LinkImageUrl" => '',
								"LinkAffUrl" => '',
								"DataSource" => "80",
						);
						if (preg_match('@Creative&nbsp;Name</td><td class="tableofcontents">(.*?)<@', $content, $g))
							$link['LinkName'] = $g[1];
						if (preg_match('@Creative Description</td><td class="tableofcontents">(.*?)<@', $content, $g))
							$link['LinkDesc'] = $g[1];
						if (preg_match('@<textarea.*?>(.*?)</textarea>@', $content, $g))
							$link['LinkHtmlCode'] = $g[1];
						if (preg_match('@a href="(.*?)"@', $link['LinkHtmlCode'], $g))
							$link['LinkAffUrl'] = $g[1];
						if (preg_match('@img src="(.*?)"@', $link['LinkHtmlCode'], $g))
							$link['LinkImageUrl'] = $g[1];
						if (empty($link['AffLinkId']) || empty($link['LinkAffUrl']))
						{
							echo sprintf("can not get link detail. id: %s \n", $id);
							continue;
						}
						if(empty($link['LinkName'])){
                            $link['LinkPromoType'] = 'link';
						}
						$links[] = $link;
					}
				}
				echo sprintf("%s new link(s) found.\n", count($links));
				if (count($links) > 0)
				{
					$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
					$links = array();
				}
			}
		}
		return $arr_return;
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
        $idInAffList = array();

		// login
		$this->LoginIntoAffService();
		
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "",);
		$request['method'] = "post";
		$request['postdata'] = "page=&pg=%2Faffiliates%2Fmerchants%2Fmerchantsearch.php&searchcheckbox1=on&merchantsearchstatus=-1&view=View&recordsperpage=1000";
		$r = $this->oLinkFeed->GetHttpResult("https://www.shareresults.com/affiliates/merchants/merchantsearch.php",$request);
		$result = $r["content"];

		//parse HTML
		$strLineStart = '<tr><td rowspan=5 valign=top>';
		$nLineStart = 0;
		while ($nLineStart >= 0) {
            $nLineStart = stripos($result, $strLineStart, $nLineStart);
            if ($nLineStart === false) break;

            //id
            $strMerID = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('createWindow', 'merchantdetails.php?mid='), "'", $nLineStart));
            if ($strMerID === false) break;
            $idInAffList[] = $strMerID;
			//name
			$strMerName = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<b>' , "</b>", $nLineStart));
			if ($strMerName === false) break;

			//activeDate
			$tmp_CreateDate = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td rowspan=5 valign=top>', '</td>', $nLineStart));

			if($tmp_CreateDate == "Application - Declined"){
				$Partnership = "NoPartnership";
				$CreateDate = "";
			}elseif($tmp_CreateDate == 'Application - Pending') {
                $Partnership = "Pending";
                $CreateDate = "";
			}elseif(stripos($tmp_CreateDate,'Member Since') !== false){
				$Partnership = "Active";
				$CreateDate = str_ireplace("Member Since :", "", $tmp_CreateDate);
				$CreateDate = date("Y-m-d H:i:s", strtotime($CreateDate));
			}else {
			    mydie("There find new partnership symbol : $tmp_CreateDate ");
            }

			$desc = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>', '</td>', $nLineStart));
			$EPCDefault = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>EPC :', '</td>', $nLineStart));
			$Homepage = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td>Website :', '</td>', $nLineStart));

			$prgm_url = "https://www.shareresults.com/affiliates/merchants/merchantdetails.php?mid=$strMerID";
			//$prgm_arr = $this->oLinkFeed->GetHttpResult($prgm_url, $request);
			//$prgm_detail = $prgm_arr["content"];

			$arr_prgm[$strMerID] = array(
				"AffId" => $this->info["AffId"],
				"IdInAff" => $strMerID,
				"Name" => addslashes($strMerName),
				"Homepage" => addslashes($Homepage),
				"Description" => addslashes($desc),
				"CreateDate" => $CreateDate,
				"StatusInAff" => 'Active',						//'Active','TempOffline','Offline'
				"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
				"LastUpdateTime" => date("Y-m-d H:i:s"),
				"DetailPage" => $prgm_url,
			);
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

		echo "\tGet Program by page end\r\n";
		if($program_num < 10){
			mydie("die: program count < 10, please check program.\n");
		}


        echo "\tGet program country and category start.\r\n";
		$data = $this->getProgramCategoryAndCountry($idInAffList);
        if(count($data)){
            $objProgram->updateProgram($this->info["AffId"], $data);
            $this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $data);
            unset($idInAffList);
            unset($data);
        }
        echo "\tGet program country and category end.\r\n";

		echo "\tUpdate ({$program_num}) program.\r\n";
		echo "\tSet program country int.\r\n";

		$objProgram->setCountryInt($this->info["AffId"]);
	}

	function getProgramCategoryAndCountry($idInAffList)
    {
	    $arr_return  =array();
        $request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "");
        $search_home_url = 'https://www.shareresults.com/affiliates/merchants/merchantsearch.php';
        $r = $this->oLinkFeed->GetHttpResult($search_home_url,$request);
        $result = preg_replace('@>\s+<@','><', $r["content"]);

        //category
        echo "Start get category!\r\n";
        $category_list_str = $this->oLinkFeed->ParseStringBy2Tag($result, array('id="merchantsearchcategory"','>') , "</select");
        preg_match_all('@option value="(\d+)" >([^<]+)</option@i', $category_list_str, $m);
        if (!isset($m[1])){
            mydie("Get category list failed!");
        }
        foreach ($m[1] as $key => $val) {
            if ($val == 0){
                continue;
            }
            $ctgr_request = array(
                "AffId" => $this->info["AffId"],
                "method" => "post",
                "postdata" => "page=&pg=%2Faffiliates%2Fmerchants%2Fmerchantsearch.php&merchantsearchstatus=-1&merchantsearchtext=&merchantsearchrevenuemodule=&searchcheckbox4=on&merchantsearchcategory={$val}&merchantsearchgender=0&merchantsearchagegroup=1&merchantsearchatargetmarket=1&merchantsearchaplacement=1&view=View&recordsperpage=1000"
            );
            $pListStr = $this->oLinkFeed->GetHttpResult($search_home_url,$ctgr_request);
            $pListStr = preg_replace('@>\s+<@','><', $pListStr["content"]);
            preg_match_all("@merchantdetails.php\?mid=(\d+)','merchantdetails@i", $pListStr, $idList);
            if (!isset($idList[1]) || empty($idList[1])){
                continue;
            }
            foreach ($idList[1] as $id) {
                if (in_array($id, $idInAffList)){
                    if (!isset($arr_return[$id]['CategoryExt']) || empty($arr_return[$id]['CategoryExt'])) {
                        $arr_return[$id] = array(
                            "AffId" => $this->info["AffId"],
                            "IdInAff" => $id,
                            "CategoryExt" => $m[2][$key],
                            "TargetCountryExt" => ''
                        );
                    }else {
                        $arr_return[$id]['CategoryExt'] .= ';'.$m[2][$key];
                    }
                }
            }
        }


        //country
        echo "Start get country!\r\n";
        $country_list_str = $this->oLinkFeed->ParseStringBy2Tag($result, array('id="merchantsearchatargetmarket"','>') , "</select");
        preg_match_all('@option value="(\d+)" >([^<]+)</option@i', $country_list_str, $c);
        if (!isset($c[1])){
            mydie("Get country list failed!");
        }
        foreach ($c[1] as $key => $val) {
            if (strcmp($c[2][$key], 'Any English Speaking Country') == 0){
                $country = 'USA,GB,AU,CA';
            }elseif (strcmp($c[2][$key], 'All') == 0){
                continue;
            }else {
                $country = $c[2][$key];
            }
            $ctry_request = array(
                "AffId" => $this->info["AffId"],
                "method" => "post",
                "postdata" => "page=&pg=%2Faffiliates%2Fmerchants%2Fmerchantsearch.php&merchantsearchstatus=-1&merchantsearchtext=&merchantsearchrevenuemodule=&merchantsearchcategory=0&merchantsearchgender=0&merchantsearchagegroup=1&searchcheckbox8=on&merchantsearchatargetmarket={$val}&merchantsearchaplacement=1&view=View&recordsperpage=1000"
            );
            $pListStr = $this->oLinkFeed->GetHttpResult($search_home_url,$ctry_request);
            $pListStr = preg_replace('@>\s+<@','><', $pListStr["content"]);
            preg_match_all("@merchantdetails.php\?mid=(\d+)','merchantdetails@i", $pListStr, $idList);
            if (!isset($idList[1]) || empty($idList[1])){
                continue;
            }
            foreach ($idList[1] as $id) {
                if (in_array($id, $idInAffList)){
                    if (!isset($arr_return[$id])) {
                        $arr_return[$id] = array(
                            "AffId" => $this->info["AffId"],
                            "IdInAff" => $id,
                            "CategoryExt" => '',
                            "TargetCountryExt" => $country
                        );
                    }elseif (empty($arr_return[$id]['TargetCountryExt'])) {
                        $arr_return[$id]['TargetCountryExt'] = $country;
                    }else {
                        $arr_return[$id]['TargetCountryExt'] .= ','.$country;
                    }
                }
            }
        }
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
}

