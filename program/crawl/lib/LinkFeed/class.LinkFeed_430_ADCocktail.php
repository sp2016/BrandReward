<?php
/**
 * User: rzou
 * Date: 2017/6/21
 * Time: 11:27
 */
require_once 'text_parse_helper.php';

class LinkFeed_430_ADCocktail
{
	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->full_crawl = isset($oLinkFeed->full_crawl) ? $oLinkFeed->full_crawl : false;
		$this->getStatus = false;
		
		$this->api_user_id = 77926;
		$this->api_user_key_pgrm = 'FS4L3A5K9BM4CHXWGFS6LMSEK';
		$this->api_user_key_cpfd = '3DK7D5Q3VP6DK5RTF2UD9CZ9R';
		$this->api_WSID = 174316;
	}
	
	function GetAllLinksByAffId()
	{
		$check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
		
		
		
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link');
		return $arr_return;
	}
	
	function GetProgramFromAff()
	{
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
		$this->GetProgramByApi($check_date);
		$this->checkProgramOffline($check_date);
		
		echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";
	}
	
	function GetProgramByApi($check_date)
	{
//		echo $this->findFinalUrl('http://track.adcocktail.com/?wid=73336&uid=77926&wsid=174316&subid=',10,$filter_arr);exit;
		echo "\tGet Program by api start\r\n";
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "",);
		$url = "https://api-ls-kamps.adcocktail.com/ls_export_programme_xml.php?UID={$this->api_user_id}&HASH={$this->api_user_key_pgrm}&WSID={$this->api_WSID}";
		
		$retry = 1;
		while ($retry)
		{
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$data = json_decode(json_encode(simplexml_load_string($r['content'])), true);
			if ($data['API-Status'] == 'OK')
				$retry = false;
			else
				$retry ++;
			
			if ($retry > 5)
				mydie('Get API content failed!');
		}
		//var_dump($data['Programm']);exit;
		
		foreach ($data['Programm'] as $program)
		{
			$IdInAff = $program['@attributes']['ID'];
			
			if (!$IdInAff) continue;
			
			$Programmname = $program['Programmname'];
			
			$CategoryExt = '';
			if (isset($program['Kategorie']))
				$CategoryExt = is_array($program['Kategorie']) ? implode(',',$program['Kategorie']) : $program['Kategorie'];
			
			$TargetCountryExt = '';
			if (isset($program['Laender']))
				$TargetCountryExt = is_array($program['Laender']) ? implode(',',$program['Laender']) : $program['Laender'];
			
			$Description = empty($program['Beschreibung']) ? '' : addslashes($program['Beschreibung']);
			$TermAndCondition = empty($program['Bedingungen']) ? '' : addslashes($program['Bedingungen']);
			$StartDate = $program['StartDate'];
			
			$CommissionLExt = 'Lead Comm:Standard=';
			if (!empty($program['VerguetungLEAD']['Standard'])){
				if (is_array($program['VerguetungLEAD']['Standard'])){
					foreach ($program['VerguetungLEAD']['Standard'] as $v){
						if (!empty($v)){
							$CommissionLExt .= $v . '€';
							break;
						}
					}
				}else $CommissionLExt .= $program['VerguetungLEAD']['Standard'] . '€';
			}
			
			$CommissionLExt .= '|Sale Comm:Standard=';
			if (!empty($program['VerguetungSALE']['Standard'])){
				if (is_array($program['VerguetungSALE']['Standard'])){
					foreach ($program['VerguetungSALE']['Standard'] as $v){
						if (!empty($v)){
							$CommissionLExt .= $v . '€';
							break;
						}
					}
				}else $CommissionLExt .= $program['VerguetungSALE']['Standard'] . '€';
			}
			
			switch ($program['Partnerschaft'])
			{
				case 'ja' :
					$Partnership = 'Active';
					break;
				case 'nein' :
					$Partnership = 'NoPartnership';
					break;
				default:
					mydie("Partnership is wrong: {$program['Partnerschaft']}");
					break;
			}
			
			switch ($program['Bewerbungsstatus'])
			{
				case 'angenommen' :
					$StatusInAff = 'Active';
					break;
				case 'warten' :
					$Partnership = 'Pending';
					$StatusInAff = 'Active';
					break;
				case 'abgelehnt' :
					$Partnership = 'Declined';
					$StatusInAff = 'Active';
					break;
				case 'nicht beworben' :
					$StatusInAff = 'Offline';
					break;
				default:
					mydie("strStatus is wrong: {$program['Bewerbungsstatus']}");
					break;
			}
			
			
			
			$AffDefaultUrl = empty($program['FreelinkURL']) ? '' : addslashes($program['FreelinkURL']);
			
			$filter_arr = array(
				'//partners.webmasterplan.com/',
				'//tracking.woobi.com/',
				'//www.adcocktail.com/',
				'//yoomedia.de/',
				'//play.leadzupc.com/',
				'//register.pickaflick.co/',
				'//track.adform.net/',
				'//static.tradetracker.net/',
				'//rec-eu.i-say.com/',
				
			);
			$Homepage = '';
			$domain_arr = array();
			if (!empty($AffDefaultUrl)){
				if($tmp_page = $this->oLinkFeed->GetHttpResult($AffDefaultUrl)){
					$tmp_result = $tmp_page['content'];
					preg_match('/http-equiv="refresh" content="1; url=(.*)"/',$tmp_result,$m);
					if (isset($m[1])) {
						$Homepage = $this->findFinalUrl($m[1],10,$filter_arr);
						if (!empty($Homepage)){
							preg_match('@(https?://[\da-z\.-]+\.[a-z]{2,6})\??/@is',$Homepage,$match);
							$domain = $match[1];
							$domain_arr[] = $domain;
							$url_num_arr = array_count_values($domain_arr);
							if ($url_num_arr[$domain] > 3) {
								mydie("There have a duplicate domain : $domain from $Homepage");
							}
						}
					}
				}
			}
			
			$arr_prgm[$IdInAff] = array(
				"Name" => addslashes($Programmname),
				"AffId" => $this->info["AffId"],
				"Homepage" => addslashes($Homepage),
				"IdInAff" => $IdInAff,
				"StatusInAff" => $StatusInAff,						//'Active','TempOffline','Offline'
				"Partnership" => $Partnership,				        //'NoPartnership','Active','Pending','Declined','Expired','Removed'
				"CommissionExt" => addslashes($CommissionLExt),
				"Description" => $Description,
				"DetailPage" => "http://www.adcocktail.com/affiliate-programme-{$IdInAff}.html",
				"AffDefaultUrl" => $AffDefaultUrl,
				"CategoryExt" => addslashes($CategoryExt),
				"TermAndCondition" => $TermAndCondition,
				"TargetCountryExt" => addslashes(trim($TargetCountryExt)),
				"LastUpdateTime" => $check_date
			);
			
			$program_num++;
			
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
		
		echo "\tGet Program by Api end\r\n";
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
	
	function getCouponFeed()
	{
		echo "\tGet CouponFeed by api start\r\n";
		
		$check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
		
		$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "",);
		$url = "https://api-ls-gutscheine.adcocktail.com/xml/{$this->api_user_id}/{$this->api_user_key_cpfd}/{$this->api_WSID}/";
		
		$retry = 1;
		while ($retry) {
			$r = $this->oLinkFeed->GetHttpResult($url, $request);
			$data = json_decode(json_encode(simplexml_load_string($r['content'])), true);
			if ($data['API-Status'] == 'OK') {
				$retry = false;
			} else {
				$retry++;
			}
			if ($retry > 5)
				mydie('Get API content failed!');
		}
//		print_r($data);exit;
		$links = array();
		foreach ($data['Werbemittel'] as $v) {
			$link = array(
				"AffId" => $this->info["AffId"],
				"AffMerchantId" => $v['KID'],
				"AffLinkId" => $v['WMID'],
				"LinkName" => empty($v['Titel'])? '' : addslashes($v['Titel']),
				"LinkDesc" => @addslashes($v['GutscheinText']),
				"LinkStartDate" => parse_time_str($v['GutscheinAbDateTime']),
				"LinkEndDate" => parse_time_str($v['GutscheinBisDateTime']),
				"LinkPromoType" => 'COUPON',
				"LinkHtmlCode" => '',
				"LinkOriginalUrl" => "",
				"LinkImageUrl" => "",
				"LinkCode" => !empty($v['Gutscheincode'])? addslashes($v['Gutscheincode']) : '',
				"LinkAffUrl" => addslashes($v['ZielURL']),
				"DataSource" => "",
				"IsDeepLink" => 'UNKNOWN',
				"Type" => 'promotion'
			);
			
			if ($link['LinkCode'] == '*****')
				$link['LinkCode'] = '';
			
			if (empty($link['LinkName']) && !empty($link['LinkCode']))
				$link['LinkName'] = sprintf('%s. Use code: %s', @$v['Advertiser'], $link['LinkCode']);
			$link['LinkPromoType'] = $this->oLinkFeed->getPromoTypeByLinkContent($link['LinkName']);
			
			if($link['LinkCode'])  $link['LinkPromoType'] = 'COUPON';
			$link['LinkHtmlCode'] = create_link_htmlcode($link);
			
			if (empty($link['AffMerchantId']) || empty($link['AffLinkId']) )
				continue;
			
			$links[] = $link;
			$arr_return['AffectedCount'] ++;
			if (count($links) >= 100 ) {
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
				$links = array();
			}
		}
		if (count($links) > 0){
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		}
		echo sprintf("get coupon by api...%s result(s) find.\n", $arr_return['AffectedCount']);
		
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'promotion');
		return $arr_return;
	}
	
	function findFinalUrl($url, $max_req_num = 10, $filter = array())
	{
		if ($max_req_num < 0)
			return '';
		$r = $this->oLinkFeed->GetHttpResult($url);
		$result = $r["content"];
		if (strlen($result) < 200)
			return '';

		preg_match('@http-equiv="refresh"\s+content="\d+;\s+url=([^"]+)"@is',$result,$u);
		
		if (isset($u[1])){
			$deepUrl = $u[1];
			if(substr($deepUrl,0,4) != 'http'){
				preg_match('@(https?://[\da-z\.-]+\.[a-z]{2,6})[/?]@is',$url,$m);
				$deepUrl = $m[1] . '/' . ltrim($deepUrl,'/');
			}
			
			$hd = @get_headers($deepUrl);
			if (!$hd)
				mydie("Here found the wrong link: $deepUrl from $url\n");
			
			return $this->findFinalUrl($deepUrl, --$max_req_num, $filter);
			
		} else {
			$r = $this->oLinkFeed->GetHttpResult($url, array('FinalUrl' => 1));
			
			if ($r['code'] == 200) {
				$result = $r["content"];
				foreach ($filter as $item) {
					if (stripos($result,$item) !== false)
						return '';
				}
				return $result;
				
			}else {
				return '';
			}
		}
	}
	
}
