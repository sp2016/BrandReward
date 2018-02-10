<?php 

require_once 'text_parse_helper.php';
require_once 'xml2array.php';

class LinkFeed_2003_Chinesean
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->website = '59834';
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		
		$this->cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"{$this->info["AffId"]}_".date("Y-m").".dat", "program", true);
		$this->cache = array();
		$this->productDir = '/app/site/ezconnexion.com/web/img/';
		if($this->oLinkFeed->fileCacheIsCached($this->cache_file)){
			$this->cache = file_get_contents($this->cache_file);
			$this->cache = json_decode($this->cache,true);
		}
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
		//print_r($this->info);exit;
		
		$programType = array('cpa', 'cps');
		
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => "get",
		);
		$page = 1;
		$HasNextPage = true;
		while ($HasNextPage)
		{
			$strUrl = "https://www.chinesean.com/api/programInfo.do?publisher=".$this->info['Account']."&password=".$this->info['Password']."&websiteId=".$this->website."&output=json&programType=cps&currentPage=$page";
			$re = $this->oLinkFeed->GetHttpResult($strUrl, $request);
			$re = json_decode($re['content'], true);
			//var_dump($re);exit;
			if ($re['maxPages'] == $page)
				$HasNextPage = false;
			
			foreach ($re['data'] as $v)
			{
				$strMerID = $v['ProgramID'];
				$strMerName = $v['Offer_Name(EN)'];
				//country
				$country_arr = array();
				foreach ($v['Region'] as $country)
				{
					$country_arr[] = $country['RegionName(EN)'];
				}
				$TargetCountryExt = implode(',', $country_arr);
				$TargetCountryExt = str_replace('(SAR)', '', $TargetCountryExt);
				$TargetCountryExt = str_replace('Hong Kong', 'HK', $TargetCountryExt);
				$TargetCountryExt = str_replace('Southeast Asia,Asia', 'Brunei, Cambodia, Indonesia, Japan, Korea, South Korea, Laos, Malaysia, Marshall Islands, Micronesia (Federated States of), Nauru, New Zealand, Australia, Palau, Papua New Guinea, Philippines, Samoa, Singapore, Solomon Islands Thailand, East Timor, Tonga, Tuvalu, Vanuatu, Vietnam, China, Mongolia', $TargetCountryExt);
				if ($TargetCountryExt == 'worldwide')
					$TargetCountryExt = 'Global';
				//commission
				$commission_arr = array();
				foreach ($v['Currencys'] as $commission)
				{
					if (isset($commission['CommissionPercentage']))
						$commission_arr[] = $commission['CommissionName(EN)'].': '.$commission['CommissionPercentage'].'%';
					else 
						$commission_arr[] = $commission['CommissionName(EN)'].': '.$commission['Commission'].$commission['Currency'];
				}
				$CommissionExt = implode('|', $commission_arr);
				
				$desc = $v['Description(EN)'];
				$TermAndCondition = $v['Requirement(EN)'];
				$CategoryExt = $v['Category(EN)'];
				$LogoUrl = $v['ProgramLogo'];
				
				$StatusInAffRemark = $v['Status'];
				if ($StatusInAffRemark == '0000')
					$Partnership = 'NoPartnership';
				if ($StatusInAffRemark == '1001')
					$Partnership = 'Active';
				if ($StatusInAffRemark == '1002')
					$Partnership = 'Pending';
				if ($StatusInAffRemark == '1003')
					$Partnership = 'Declined';
				
				$Homepage = '';
				if ($Partnership != 'NoPartnership' && $Partnership != 'Declined')
				{
					if(!isset($this->cache[$strMerID]["Homepage"]))
					{
						$FinaUrl = $this->get_curl_location($v['Url']);
						if (stripos($FinaUrl, 'https://www.chinesean.com/affiliate/expired.jsp') === false)
						{
							$FinaUrl_arr = parse_url($FinaUrl);
							if (isset($FinaUrl_arr['host']))
							{
								if (isset($FinaUrl_arr['scheme']))
									$Homepage = $FinaUrl_arr['scheme'].'://'.$FinaUrl_arr['host'];
								else 
									$Homepage = 'http://'.$FinaUrl_arr['host'];
								$this->cache[$strMerID]["Homepage"] = "1";
							}
						}
					}
				}
				/* echo "<pre>\r\n";
				echo $v['Url']."\r\n";
				echo $FinaUrl."\r\n";
				echo $Homepage."\r\n"; */
				
				$arr_prgm[$strMerID] = array(
						"Name" => addslashes(html_entity_decode($strMerName)),
						"AffId" => $this->info["AffId"],
						"IdInAff" => $strMerID,
						"TargetCountryExt" => trim($TargetCountryExt),
						"StatusInAffRemark" => addslashes($StatusInAffRemark),
						"StatusInAff" => 'Active',						//'Active','TempOffline','Offline'
						"Partnership" => $Partnership,						//'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
						"Description" => addslashes($desc),
						"TermAndCondition" => addslashes($TermAndCondition),
						"CommissionExt" => addslashes($CommissionExt),
						"CookieTime" => $v['RD'],
						"LogoUrl" => addslashes($LogoUrl),
						"CategoryExt" => addslashes($CategoryExt),
						"LastUpdateTime" => date("Y-m-d H:i:s"),
						"AffDefaultUrl" => addslashes($v['Url']),
						//"DetailPage" => $prgm_url,
						//"SupportDeepUrl" => $SupportDeepurl,
				);

				if (!empty($Homepage)){
                    $arr_prgm[$strMerID]['Homepage'] = $Homepage;
                }
				
				$program_num++;
				if(count($arr_prgm) >= 1){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
			}
			if(count($arr_prgm)){
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
				unset($arr_prgm);
			}
			$page++;
		}
		$this->cache = json_encode($this->cache);
		$this->oLinkFeed->fileCachePut($this->cache_file, $this->cache);
		
		echo "\tGet Program by api end\r\n";
		
		if($program_num < 10){
			mydie("die: program count < 10, please check program.\n");
		}
		
		echo "\tUpdate ({$program_num}) program.\r\n";
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

	function get_curl_location($url)
	{
		$url = str_replace(' ', '', trim($url));
		$i = 0;
		do {//do.while循环：先执行一次，判断后再是否循环
			$curl = curl_init($url);
			$curl_opts = array(
				CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_RETURNTRANSFER => true,
				//CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HEADER => true,
				CURLOPT_TIMEOUT => 10,
			);
			curl_setopt_array($curl, $curl_opts);
			
			$header = curl_exec($curl);
			curl_close($curl);
			$i++;
			/* if ($i == 1) {
				echo '<pre>';
				print_r($header);
				exit;
				} */
			$urlPre = '';
			if (preg_match('@(^(?:http|https)://.*)/.*@isU', $url, $matchsPre)) {
				$urlPre = $matchsPre[1];
			}
			$jsPreg = array(
					'/<section> <script type="text\/javascript">var ENV="production";var url="(.*)";var/isU',
					'/<section> <script type="text\/javascript">var ENV="production";var url={.*"portal":"(.*)"/isU',
					'@<script.*>\s+window.location.replace\(\'(.*)\'\)\s+<\/script>@isU',
					'@<meta http-equiv="Refresh" content="1;url=(.*)">@isU',
					'@<meta http-equiv="Refresh" content="1;\surl=(.*)" />@isU',
					'@<meta http-equiv="refresh" content="0;url=(.*)">@isU',
			);
			foreach ($jsPreg as $k => $v) {
				if (preg_match($v, $header, $matchs)) {
					$resUrl = html_entity_decode(str_replace('\\', '', $matchs[1]));
					$url = $matchs ? (substr($matchs[1], 0, 1) == '/' ? $urlPre . $resUrl : $resUrl) : null;
					continue 2;
				}
			}
			$strlen_com = 9500;
			preg_match('|Location:\s(.*?)\s|i', $header, $tdl);
			if (stripos($header, "Location:")) {
				if (strlen($header) > $strlen_com) {
					//echo "a--$i";
					return $url . '';
					break;
				} else {
					$url = $tdl ? (substr($tdl[1], 0, 1) == '/' ? $urlPre . $tdl[1] : $tdl[1]) : null;
				}
			} else {
				//echo "b--$i";
				return $url . '';
				break;
			}
			if ($i > 15) {
				//echo "c--$i";
				return $url . '';
				break;
			}
		} while (true);
	}




}
?>