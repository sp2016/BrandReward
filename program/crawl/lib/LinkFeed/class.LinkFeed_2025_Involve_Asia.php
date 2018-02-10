<?php
/**
 * User: rzou
 * Date: 2017/6/23
 * Time: 14:15
 */
class LinkFeed_2025_Involve_Asia
{
	private $apiKey = 'general';
	private $apiSecret = 'hoBWrv75mjf3l1tNACQVkswRK9wR9clz1P/+ybpoyWM=';
	
	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->full_crawl = isset($oLinkFeed->full_crawl) ? $oLinkFeed->full_crawl : false;
		$this->getStatus = false;
		
	}
	
	private function getProgramObj()
	{
		if (!empty($this->objProgram))
			return $this->objProgram;
		$this->objProgram = new ProgramDb();
		return $this->objProgram;
	}
	
	function GetProgramFromAff()
	{
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
		$this->GetProgramByPage();
		$this->checkProgramOffline($check_date);
		
		echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";
	}
	
	function GetProgramByApi()
	{
		echo "\tGet Program by api start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		
		$this->apiKey = urlencode($this->apiKey);
		$this->apiSecret = urlencode($this->apiSecret);
		
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "post",
			"postdata" => "secret={$this->apiSecret}&key={$this->apiKey}"
		);
		
		$r = $this->oLinkFeed->GetHttpResult('https://api.involve.asia/api/authenticate', $request);
		$rToken = json_decode($r['content'],true);
		if (strpos($rToken['status'],'success') === false) mydie('Failed get token !');
		$token = $rToken['data']['token'];
		
		//stop here!
		
		echo "\tGet Program by Api end\r\n";
		if($program_num < 10){
			mydie("die: program count < 10, please check program.\n");
		}
		echo "\tUpdate ({$program_num}) program.\r\n";
	}
	
	function GetProgramByPage()
	{
		echo "\tGet Program by page start\r\n";
		
		$objProgram = $this->getProgramObj();
		$arr_prgm = array();
		$program_num = 0;
		
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "");
		
		//step 1, login
		$this->LoginIntoAffService($request);
		
		$request['method'] = 'get';
		$list_url = 'https://app.involve.asia/publisher/search?merchant_name=&sort_by=relevance&require_approval=&categories=&countries=';
		$r = $this->oLinkFeed->GetHttpResult($list_url,$request);
		$result = @json_decode($r['content'],true);
		$pageContent = preg_replace("/>\\s+</i", "><", $result['data']['contents']);
		$p_arr = explode('<div class="col-xs-12 col-sm-6 col-md-4">',$pageContent);


		foreach ($p_arr as $program)
		{
			if (empty($program)) continue;
			
			$strPosition = 0;
			
			$IdInAff = intval($this->oLinkFeed->ParseStringBy2Tag($program,'<a href="/publisher/browse/','"',$strPosition));
			if (!$IdInAff) continue;
			
			$name = $this->oLinkFeed->ParseStringBy2Tag($program,'title="','"',$strPosition);
			$logo_url = $this->oLinkFeed->ParseStringBy2Tag($program,'<img class="merchant-list-box-logo img-responsive" src="','"',$strPosition);
			$data_preview_url = $this->oLinkFeed->ParseStringBy2Tag($program,'data-preview_url="','"',$strPosition);
			$status = $this->oLinkFeed->ParseStringBy2Tag($program,array('class="btn btn','>'),'</a>',$strPosition);
			
			switch ($status)
			{
				case 'Get Link':
					$partnership = 'Active';
					break;
				case 'Pending':
					$partnership = 'Pending';
					break;
				case 'Apply':
					$partnership = 'NoPartnership';
					break;
				case 'Rejected':
					$partnership = 'Declined';
					break;
				default:
					mydie("die: new partnership [$status].\n");
					break;
			}
			
			$AffDefualtUrl = html_entity_decode($this->oLinkFeed->ParseStringBy2Tag($program,'a href="','"',$strPosition));

			if ($partnership == 'Active'){
				$homePage = $data_preview_url;
				/*if($offer_id){
					$AffDefualtUrl = "http://invol.co/aff_m?offer_id=$offer_id&aff_id={$this->accountid}&source=deeplink_generator";
				}*/
				
			}else{
				$homePage = $this->oLinkFeed->findFinalUrl($AffDefualtUrl, $request);
			}
			
			$DetailPage = "https://app.involve.asia/publisher/browse/$IdInAff";
			
			$r = $this->oLinkFeed->GetHttpResult($DetailPage, $request);
			$DPresult = preg_replace("/>\\s+</i", "><", $r['content']);
            $description = $this->oLinkFeed->ParseStringBy2Tag($DPresult, 'Description </h4>', '<h4');
            $termAndCondition = $this->oLinkFeed->ParseStringBy2Tag($DPresult,array('Terms and Conditions</span>','</p>'),'</div>');
            $commission = ($this->oLinkFeed->ParseStringBy2Tag($DPresult,array('Commission Structure','>'),'</ul'));
            $a_arr = explode('</li>', $commission);

            $commissionExt = '';
            foreach ($a_arr as $v) {
                $comm = trim(strip_tags($v));
                if ($comm) {
                    $comm = preg_replace('@\\s+@', ' ', $comm);
                    $comm = preg_replace('@Commission@i', '', $comm);
                    $comm = trim($comm);
                    $commissionExt .= $comm . ',';
                }
            }
            $commissionExt = rtrim($commissionExt, ',');
            $categoryExt = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($DPresult,array('Merchant Category','>'),'</ul')));
            $countryExt = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($DPresult,'Available Countries','</ul')));
            $AvailableTools = $this->oLinkFeed->ParseStringBy2Tag($DPresult,'Available Tools','</ul');

            $SupportDeepUrl = 0;
            $tools_arr =  explode('fw-500', $AvailableTools);
            foreach ($tools_arr as $value) {
                $isAvalibe = stripos($value, 'list-orange');
                $isDeeplink = stripos($value, 'Deeplink');
                if ($isAvalibe && $isDeeplink) {
                    $SupportDeepUrl = 1;
                }
            }

			$arr_prgm[$IdInAff] = array(
				"Name" => addslashes($name),
				"AffId" => $this->info["AffId"],
				"Homepage" => addslashes($homePage),
				"IdInAff" => $IdInAff,
				"StatusInAff" => 'Active',				    //'Active','TempOffline','Offline'
				"Partnership" => $partnership,				//'NoPartnership','Active','Pending','Declined','Expired','Removed'
				"CommissionExt" => addslashes($commissionExt),
				"Description" => addslashes($description),
				"TermAndCondition" => addslashes($termAndCondition),
				"SupportDeepUrl" => $SupportDeepUrl ? 'YES' : 'NO',
				//"DetailPage" => $DetailPage,
				"AffDefaultUrl" => addslashes($AffDefualtUrl),
				"TargetCountryExt" => addslashes($countryExt),
				"CategoryExt" => addslashes($categoryExt),
				"LogoUrl" => addslashes($logo_url),
				//"SecondIdInAff" => $offer_id,
				"LastUpdateTime" => date("Y-m-d H:i:s")
			);

			$program_num ++;
            echo $program_num . "\t";
			if(count($arr_prgm) >= 100){
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
				$arr_prgm = array();
			}
		}
		
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
			$arr_prgm = array();
		}
		
		echo "\tGet Program by page end\r\n";
		if($program_num < 10){
			mydie("die: program count < 10, please check program.\n");
		}
		echo "\tUpdate ({$program_num}) program.\r\n";
	}
	
	function checkProgramOffline($check_date)
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
	
	function LoginIntoAffService(&$request)
	{
		echo "login to affservice\n\t";
		
		$loginUrl = "https://app.involve.asia/";
		
		$this->oLinkFeed->clearHttpInfos($this->info["AffId"]);//删除缓存文件，删除httpinfos[$aff_id]变量
		
		$r = $this->oLinkFeed->GetHttpResult($loginUrl,$request);
		
		if ($r["code"] == 0) {
			if (preg_match("/^SSL: certificate subject name .*? does not match target host name/i", $r["error_msg"])) {
				$request["no_ssl_verifyhost"] = 1;
				$r = $this->GetHttpResult($loginUrl, $request);
			}
		}
		if (!strpos($r['content'],'type="hidden"')) mydie("die: login failed for aff({$this->info['AffId']}) when load to loginPage!<br>\n");
		
		$token_key = $this->oLinkFeed->ParseStringBy2Tag($r["content"],'type="hidden" name="','"');
		$token_val = $this->oLinkFeed->ParseStringBy2Tag($r["content"],array('type="hidden"','value="'),'"');
		$request['postdata'] = $token_key . '=' . $token_val;
		
		$this->info["AffLoginPostString"] .= '&' . $token_key . '=' . $token_val;
		
		$request['method'] = 'post';
		$request['postdata'] = $this->info["AffLoginPostString"];
		
		$r = $this->oLinkFeed->GetHttpResult($loginUrl,$request);
		
		if(stripos($r["content"], $this->info['AffLoginVerifyString']) === false) mydie("die: login failed for aff({$this->info['AffId']}) when login in!");
		
		echo "verify succ: " . $this->info["AffLoginVerifyString"] . "\n";
		
		return 'stop here !';
	}
}