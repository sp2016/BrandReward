<?php

require_once 'text_parse_helper.php';

class LinkFeed_704_Shareit
{
	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		$this->AffiliateID = '';
	}

    function Login($retry=3)
    {
        $url = $this->info["AffLoginUrl"];
        $request = array(
            "AffId" => $this->info["AffId"],
            "method" => "get",
            "postdata" => ""
        );
        $re = $this->oLinkFeed->GetHttpResult($url, $request);
        $token = urlencode(html_entity_decode($this->oLinkFeed->ParseStringBy2Tag($re['content'], array('__RequestVerificationToken','value="'), '"')));
        if (empty($token)) {
            mydie("Token does not exist ! Please check the login page\r\n");
        }

        $this->info["AffLoginPostString"] = str_replace('{token}', $token, $this->info["AffLoginPostString"]);
        $this->info['AffLoginUrl'] = 'https://account.mycommerce.com/?ReturnUrl=%2FCp';

        $request = array(
            "AffId" => $this->info["AffId"],
            "method" => $this->info["AffLoginMethod"],
            "postdata" => $this->info["AffLoginPostString"]
        );
        if (isset($info["referer"])) $request["referer"] = $this->info["referer"];
        $arr = $this->oLinkFeed->GetHttpResult($this->info['AffLoginUrl'], $request);

        if ($arr["code"] == 0) {
            if (preg_match("/^SSL: certificate subject name .*? does not match target host name/i", $arr["error_msg"])) {
                $request["no_ssl_verifyhost"] = 1;
                $arr = $this->oLinkFeed->GetHttpResult($this->info['AffLoginUrl'], $request);
            }
        }

        if ($arr["code"] == 200) {
            if (isset($this->info["AffLoginVerifyString"]) && $this->info["AffLoginVerifyString"]) {
                if (stripos($arr["content"], $this->info["AffLoginVerifyString"]) !== false) {
                    echo "verify succ: " . $this->info["AffLoginVerifyString"] . "\n";
                    $this->isLogined = true;
                }
                if (!$this->isLogined && stripos($arr["content"], "REFRESH") !== false && isset($this->info["AffLoginSuccUrl"]) && $this->info["AffLoginSuccUrl"]) {
                    $url_path = @parse_url($this->info["AffLoginSuccUrl"], PHP_URL_PATH);//parse_url用于解析url，返回一个关联数组。parse_url("xxx", PHP_URL_PATH)返回数组的path值
                    if ($url_path && stripos($arr["content"], $url_path) !== false) {
                        echo "good, verify succ (redir by meta tag) <br>\n";
                        $this->isLogined = true;
                    }
                }
            }
        }

        if (!$this->isLogined){
            echo "verify login failed(" . $this->info["AffLoginVerifyString"] . "), will be retry $retry times <br>\n";
            if ($retry >0){
                sleep(30);
                $retry --;
                return $this->Login($retry);
            }else{
                mydie("verify login failed(" . $this->info["AffLoginVerifyString"] . ")");
            }
        }else {
            return $arr['final_url'];
        }
	}
	
	function GetProgramFromAff()
	{
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
	
		$this->GetProgramByPage();
		$this->checkProgramOffline($this->info["AffId"], $check_date);
	
		echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";
	}
	
	function GetProgramByPage()
	{
		echo "\tGet Program by page start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
	
		//1.login并获得fina_url,里面的参数sessionID和random每次登陆都有不同
		$final_url = $this->Login();
        $url = 'https://cp.shareit.com/shareit/cp/login/index.html?publisherid=200269163&pageid=personal&key=4001feedbf667ec188e3a464a9150018&timestamp=1516961939&embed=1&cookies=1&ignoreCPRedirect=1';
        $request = array(
            "AffId" => $this->info["AffId"],
            "method" => "get",
        );
        $this->oLinkFeed->GetHttpResult($url, $request);

        $cookiejar = $this->oLinkFeed->getCookieJarByAffId($this->info["AffId"]);
        $cookieArr = explode('sessioncookie', file_get_contents($cookiejar));
        $scid = isset($cookieArr[1]) ? $cookieArr[1] : '';
        if (!$scid){
            mydie("Can't get sessioncookie");
        }
        $scid = urldecode($scid);
        $scidArr = explode(':',$scid);
        $scidArr = array_map(function ($c){return trim($c);}, $scidArr);

		//2.get commission
		$com_url = 'https://cp.shareit.com/shareit/cp/products/find.html?sessionid='.trim($scidArr[0]).'&random='.trim($scidArr[1]);
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => "post",
				"postdata" => 'STATUS=SIGNED&CATEGORIES=-1&PRODUCT_ID=&PRODUCT_NAME=&COMPANY_NAME=&SUBMIT_FILTER=Display',
                "addHeader" => array("Referer: https://cp.shareit.com/shareit/cp/products/find.html?embed=1&cookies=1&sessionid={$scidArr[0]}&random={$scidArr[1]}")
		);
		$result = $this->oLinkFeed->GetHttpResult($com_url, $request);
		print_r($result);exit;
		$result = $this->oLinkFeed->ParseStringBy2Tag($result['content'], '<table cellpadding="2"', '</p></div>');
		$cLineStart = 0;
		$comm_arr = array();
		while (1)
		{
			$strMerID = $this->oLinkFeed->ParseStringBy2Tag($result, array('>Terminate partnership<', '(#'), ')', $cLineStart);
			if (empty($strMerID))
				break;
			$Commission = trim($this->oLinkFeed->ParseStringBy2Tag($result, array('<td>', '<td>', '<td>'), '<', $cLineStart));
			$comm_arr[$strMerID] = array(
					"IdInAff" => $strMerID,
					"CommissionExt" => $Commission,
			);
		}
		//print_r($comm_arr);exit;
		
		
		//3.取的所有program的name和idinaff
		$url = 'https://cp.shareit.com/shareit/cp/products/linkgenerator.html?'.$Param;
		$request['postdata'] = 'LINK_SELECTOR=EXT&SELECT_LINKS=Next';
		$re = $this->oLinkFeed->GetHttpResult($url, $request);
		$re = $this->oLinkFeed->ParseStringBy2Tag($re['content'], array('<select name="PUBLISHER"', '>'), '</select>');
		$LineStart = 0;
		
		//4.通过getlink取得deafultUrl,并通过deafultUrl取得homepage
		while (1)
		{
			$IdInAff = trim($this->oLinkFeed->ParseStringBy2Tag($re, '<option value="', '"', $LineStart));
			if (empty($IdInAff))
				break;
			$name = trim($this->oLinkFeed->ParseStringBy2Tag($re, '>', '(#', $LineStart));
			
			$request['postdata'] = "formtype=external&LINK_SELECTOR=EXT&PUBLISHER=$IdInAff&EXTERNAL_URL=&EXTERNAL_TARGET=1&SUBMIT_FORM2=Generate+link+to+an+external+website";
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			
			$AffDefaultUrl = trim($this->oLinkFeed->ParseStringBy2Tag($r['content'], array('<td nowrap>', '<a href="'), '"'));
			$Homepage = trim($this->oLinkFeed->ParseStringBy2Tag($AffDefaultUrl, 'target=', ''));
			$SupportDeepUrl = 'UNKNOWN';
			if (!empty($Homepage))
				$SupportDeepUrl = 'YES';
			if (isset($comm_arr[$IdInAff]))
				$CommissionExt = $comm_arr[$IdInAff]['CommissionExt'];
			else 
				$CommissionExt = '';
			
			$arr_prgm[$IdInAff] = array(
					"Name" => addslashes($name),
					"AffId" => $this->info["AffId"],
					"IdInAff" => $IdInAff,
					"StatusInAff" => 'Active',                        //'Active','TempOffline','Offline'
					"Partnership" => 'Active',                        //'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
					"Homepage" => addslashes($Homepage),
					"CommissionExt" => $CommissionExt,
					"SupportDeepUrl" => $SupportDeepUrl,
					"LastUpdateTime" => date("Y-m-d H:i:s"),
					"AffDefaultUrl" => addslashes($AffDefaultUrl),
					//"CategoryExt" => addslashes($CategoryExt),
			);
			//print_r($arr_prgm[$strMerID]);
			$program_num++;
			if(count($arr_prgm) >= 100)
			{
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
				$arr_prgm = array();
			}
		}
		if(count($arr_prgm))
		{
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
			$arr_prgm = array();
		}
		
		echo "\tGet Program by page end\r\n";
		if($program_num < 10)
		{
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
?>