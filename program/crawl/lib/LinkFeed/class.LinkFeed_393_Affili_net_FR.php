<?php

require_once 'text_parse_helper.php';
define('API_USERNAME_63', '571453');
define('API_PASSWORD_63', 'HfxxD1UqNUtRduXQn9A7');

class LinkFeed_63_Affili_net_DE
{
	var $info = array(
		"ID" => "393",
		"Name" => "affili.net FR",
		"IsActive" => "YES",
		"ClassName" => "LinkFeed_393_Affili_net_FR",
		"LastCheckDate" => "1970-01-01",
		'loginurl'	=> "http://www.affili.net/en/desktopdefault.aspx",
		'loginpostdata'	=> '__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=/wEPDwULLTIwNjQyNTkxMzMPZBYCAgQPZBYEZg9kFgJmD2QWAmYPZBYCAgIPZBYKAggPDxYCHgRUZXh0BQpVc2VyIExvZ2luZGQCCg8PFgIfAAUIUGFzc3dvcmRkZAIQDw8WAh8ABQJHT2RkAhIPFgIfAAUFTG9naW5kAhQPDxYEHwAFGEZvcmdvdHRlbiB5b3VyIHBhc3N3b3JkPx4LTmF2aWdhdGVVcmwFIX4vZW4vZGVza3RvcGRlZmF1bHQuYXNweC90YWJpZC04OGRkAgIPDxYCHgdWaXNpYmxlaGRkZA==&pathinfo=/tabid-67/rewritten-1/&ctl00$ctl03$txtLogin=499752&ctl00$ctl03$txtPassword=gReaTaffili816&ctl00$ctl03$lnkLogin=GO',
		'method'	=> 'post',
	);

	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		$this->soapToken = null;
		$this->soapClient = null;
	}

	function getCouponFeed()
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		return $arr_return;
	}

	function GetAllLinksFromAffByMerID($merinfo)
	{
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, );
		list($page, $limit, $total, $links) = array(0, 100, 0, array());
		do
		{
			$page ++;
			$links = array();
			$r = $this->soapSearchCreatives($merinfo['IdInAff'], $page, $limit);
			if (empty($r) || empty($r->CreativeCollection) || empty($r->CreativeCollection->Creative))
				break;
			$total = (int)$r->TotalResults;
			$data = $r->CreativeCollection->Creative;
			if (!is_array($data) && !empty($data))
				$data = array($data);
			foreach ((array)$data as $v)
			{
				$link = array(
					"AffId" => $this->info["AffId"],
					"AffMerchantId" => $merinfo['IdInAff'],
					"AffLinkId" => sprintf('%s_%s_%s', $merinfo['IdInAff'], $v->CreativeTypeEnum, $v->CreativeNumber),
					"LinkName" => html_entity_decode(trim($v->Title)),
					"LinkDesc" => '',
					"LinkStartDate" => '0000-00-00',
					"LinkEndDate" => '0000-00-00',
					"LinkPromoType" => 'N/A',
					"LinkHtmlCode" => $v->IntegrationCode,
					"LinkOriginalUrl" => '',
					"LinkImageUrl" => '',
					"LinkAffUrl" => '',
					"DataSource" => '87',
				);
				if (!empty($v->BannerStub))
				{
					$link['LinkImageUrl'] = $v->BannerStub->BannerURL;
					$link['LinkDesc'] = $v->BannerStub->AltTag;
				}
				if (!empty($v->TextStub))
					$link['LinkDesc'] = $v->TextStub->Header;
				$code = get_linkcode_by_text_de($link['LinkName'] . '|' . $link['LinkDesc']);
				if (!empty($code))
				{
					$link['LinkPromoType'] = 'COUPON';
					$link['LinkCode'] = $code;
				}
				else
					$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
				if (preg_match('@a href="(.*?)"@i', $link['LinkHtmlCode'], $g))
					$link['LinkAffUrl'] = $g[1];
				if (empty($link['AffLinkId']) || empty($link['LinkHtmlCode']))
					continue;
                elseif(empty($link['LinkName'])){
                    $link['LinkPromoType'] = 'link';
                }
				$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
				$links[] = $link;
				$arr_return["AffectedCount"] ++;
			}
			echo sprintf("program:%s, page:%s, %s links(s) found. \n", $merinfo['IdInAff'], $page, count($links));
			if(count($links) > 0)
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
			sleep(1);
		}while($page * $limit < $total && $page * $limit < 9999);
		return $arr_return;
	}

	private function getSoapToken()
	{
		if ($this->soapToken)
			return $this->soapToken;
		$this->oLinkFeed->clearHttpInfos(63);
		$logon = new SoapClient("https://api.affili.net/V2.0/Logon.svc?wsdl", array('trace'=> true));
		$token = $logon->Logon(array(
				'Username'  => API_USERNAME_63,
				'Password'  => API_PASSWORD_63,
				'WebServiceType' => 'Publisher'
		));
		$this->soapToken = $token;
		echo sprintf("Logon token %s created at %s.\n", $token, date('Y-m-d H:i:s', time()));
		return $this->soapToken;
	}

	private function soapSearchCreatives($IdInAff, $page, $limit, $retry = 2)
	{
		$token = $this->getSoapToken();
		$client = $this->soapClient;
		if (!$client)
		{
			$client = new SoapClient('https://api.affili.net/V2.0/PublisherCreative.svc?wsdl', array('trace'=> true));
			$this->soapClient = $client;
		}
		try 
		{
			$r = $client->SearchCreatives(array(
				'CredentialToken' => $token,
				'DisplaySettings' => array('CurrentPage' => $page, 'PageSize' => $limit),
				'SearchCreativesQuery' => array(
						'CreativeTypes' => array('Text', 'Banner'),
						'ProgramIds' => array($IdInAff),
						)
			));
		}
		catch (Exception $e)
		{
			if (preg_match('@Illegal characters@', $e->getMessage()))
			{
				// this exception may caused by the server catch it and return null
				echo sprintf("%s Exception return null\n", $e->getMessage());
				return null;
			}
			// try to relogon.
			$this->soapToken = null;
			$retry --;
			if ($retry < 0)
				throw $e;
			echo sprintf("%s Exception sleep 120...\n", $e->getMessage());
			sleep(120);
			return $this->soapSearchCreatives($IdInAff, $page, $limit, $retry);
		}
		return $r;
	}

	function GetProgramFromAff()
	{
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";

		$this->GetProgramByApi();
		$this->checkProgramOffline($this->info["AffId"], $check_date);

		echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
	}

	function GetProgramByApi()
	{
		echo "\tGet Program by api start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;

		/* <LIVE DATA> */
		define ("WSDL_LOGON", "https://api.affili.net/V2.0/Logon.svc?wsdl");
		define ("WSDL_PROG",  "https://api.affili.net/V2.0/PublisherProgram.svc?wsdl");

		$Username	= API_USERNAME_63; // the publisher ID
		$Password	= API_PASSWORD_63; // the publisher web services password
		
		$SOAP_LOGON = new SoapClient(WSDL_LOGON, array('trace'=> true));
		$Token		= $SOAP_LOGON->Logon(array(
					'Username'  => $Username,
					'Password'  => $Password,
					'WebServiceType' => 'Publisher'
					));

		$params = array('Query' => '');
		try {
			$SOAP_REQUEST = new SoapClient(WSDL_PROG, array('trace'=> true));
			$req = $SOAP_REQUEST->GetAllPrograms(array(
			            'CredentialToken' => $Token,
			            'GetProgramsRequestMessage' => $params
			            ));
			            
			$total = $req->TotalRecords;	
		
			foreach($req->Programs->ProgramSummary as $prgm){					
				$strMerID = $prgm->ProgramId;
				if(!$strMerID) continue;
				
				$Partnership = "NoPartnership";
				$StatusInAffRemark = $prgm->PartnershipStatus;
				if($StatusInAffRemark == 'Active'){
					$Partnership = 'Active';
				}elseif($StatusInAffRemark == 'Declined'){
					$Partnership = 'Declined';					
				}elseif($StatusInAffRemark == 'Waiting'){
					$Partnership = 'Pending';
				}elseif($StatusInAffRemark == 'Paused'){
					$Partnership = 'Expired';
				}elseif($StatusInAffRemark == 'NotApplied'){
					$Partnership = 'NoPartnership';					
				}
				
				$arr_prgm[$strMerID] = array(
					"AffId" => $this->info["AffId"],	
					"IdInAff" => $strMerID,	
					"Name" => addslashes($prgm->ProgramTitle),					
					"Homepage" => $prgm->Url,
					"Description" => addslashes($prgm->Description),
					"TermAndCondition" => addslashes($prgm->Limitations),						
					"StatusInAffRemark" => addslashes($StatusInAffRemark),
					"StatusInAff" => 'Active',						//'Active','TempOffline','Offline'
					"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
					"DetailPage" => "http://publisher.affili.net/Programs/programInfo.aspx?pid=$strMerID",				
					"LastUpdateTime" => date("Y-m-d H:i:s"),					
				);
				
				$program_num++;					
				
				if(count($arr_prgm) >= 100){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
			}			
			
		} catch( Exception $e ) { 
			mydie("die: Api error.\n");
		}
		
		if(count($arr_prgm)){
			$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
			$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
			unset($arr_prgm);
		}
		
		echo "\tGet Program by api end\r\n";
		
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
?>
