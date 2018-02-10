<?php
class Program extends ProgramDb
{		
	var $aff_url_pattern = array();
	var $sub_aff = array();
	var $global_c = "";
	var $url_pattern = "";
	var $aff_rank = array();
	
	function __construct()
	{	
		if(!isset($this->objMysql)) $this->objMysql = new MysqlExt();
		if(!isset($this->objPendingMysql)) $this->objPendingMysql = new MysqlExt(PENDING_DB_NAME, PENDING_DB_HOST, PENDING_DB_USER, PENDING_DB_PASS);
		//if(!isset($this->objTaskMysql)) $this->objTaskMysql = new MysqlExt(TASK_DB_NAME, TASK_DB_HOST, TASK_DB_USER, TASK_DB_PASS);
		
		$this->sub_aff = array(160,191,223,237,578,639,652,656);
		$this->aff_tt = array(52,65,425,426,427,2026,2027,2028,2029,2053,2054);	//TradeTracker
		$this->global_c = ",AD,AE,AF,AG,AI,AL,AM,AO,AQ,AR,AS,AT,AU,AW,AX,AZ,BA,BB,BD,BE,BF,BG,BH,BI,BJ,BL,BM,BN,BO,BQ,BR,BS,BT,BV,BW,BY,BZ,CA,CC,CD,CF,CG,CH,CI,CK,CL,CM,CN,CO,CR,CS,CU,CV,CW,CX,CY,CZ,DE,DJ,DK,DM,DO,DZ,EC,EE,EG,EH,ER,ES,ET,FI,FJ,FK,FM,FO,FR,GA,GB,GD,GE,GF,GG,GH,GI,GL,GM,GN,GP,GQ,GR,GS,GT,GU,GW,GY,HK,HM,HN,HR,HT,HU,ID,IE,IL,IM,IN,IO,IQ,IR,IS,IT,JE,JM,JO,JP,KE,KG,KH,KI,KM,KN,KP,KR,KW,KY,KZ,LA,LB,LC,LI,LK,LR,LS,LT,LU,LV,LY,MA,MC,MD,ME,MF,MG,MH,MK,ML,MM,MN,MO,MP,MQ,MR,MS,MT,MU,MV,MW,MX,MY,MZ,NA,NC,NE,NF,NG,NI,NL,NO,NP,NR,NU,NZ,OM,PA,PE,PF,PG,PH,PK,PL,PM,PN,PR,PS,PT,PW,PY,QA,RE,RO,RS,RU,RW,SA,SB,SC,SD,SE,SG,SH,SI,SJ,SK,SL,SM,SN,SO,SR,SS,ST,SV,SX,SY,SZ,TC,TD,TF,TG,TH,TJ,TK,TL,TM,TN,TO,TR,TT,TV,TW,TZ,UA,UG,UM,US,UY,UZ,VA,VC,VE,VG,VI,VN,VU,WF,WS,XK,YE,YT,YU,ZA,ZM,ZW,UK";
		$this->url_pattern = "^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(([a-z]|\d|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])*([a-z]|\d|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])))\.)+(([a-z]|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(([a-z]|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])*([a-z]|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\x{E000}-\x{F8FF}]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$";		
		$this->country_rel = array(	
									//AISA
									"in" => "as", "jp" => "as", "sg" => "as", "hk" => "as", "cn" => "as", "tw" => "as", "pk" => "as", 
									"my" => "as", "id" => "as", "th" => "as", "ph" => "as",
									//EU
									"at" => "eu", "ch" => "eu", "de" => "eu", "fr" => "eu", "ie" => "eu", "uk" => "eu", 
									"dk" => "eu", "gb" => "eu", "it" => "eu", "es" => "eu", "nl" => "eu", "se" => "eu",
									//NORTH A
									"us" => "na", "ca" => "na",
									//Ocean
									"au" => "oa", "nz" => "oa"
									
									);
	}
	
	function getDomainByHomepage($homepage, $fi = ""){
		global $is_debug;
		$domain_arr = array();
		//echo $homepage;
		if($homepage){
			if(!preg_match("/^(https?)\\/\\//i",$homepage)){
				$homepage = "http://".$homepage;
			}
			$homepage = preg_replace("/(https?:?\\/\\/:?https?:?\\/\\/:?)/i", "http://", $homepage);
						
			$matches = array();
			preg_match_all("/https?:?\\/\\/:?([^\s;,\\?\\/]*)[^\s]*/i", $homepage, $matches);			
			//print_r($matches);
				foreach($matches[1] as $k => $v){
				if($v){				
					if(!preg_match("/^(https?|ftp)/i",$v)){
						$v = "http://".$v;
					}
				}
				
				$domain = "";
				$domain = $this->getUrlDomain($v);
				
				if($domain){
					if($fi == "fi"){
						$country_code = trim(strtolower(str_replace(",", "|", $this->global_c)), "|");
											
						$domain_arr["domain"][] = $domain;
						
						/*preg_match("/^($country_code)\\./i", $domain, $m2);
						if(count($m2) && $this->checkDomainCountry($m2[1])){
							$domain = str_ireplace($m2[1].".", "", $domain);
							//echo "\t[]\t";
							//$this->getUrlDomain($domain);
							if(strpos($domain, ".") !== false && $domain == $this->getUrlDomain("http://".$domain)){
								$domain_arr["domain"][] = $domain;
							}
						}*/
						
						preg_match("/{$domain}[^\?]*[^a-zA-Z\?]+($country_code)(?:[^a-zA-Z]+|$)/i", $matches[0][$k], $m);
						if(count($m) && $this->checkDomainCountry($m[1])){
							$m[1] = strtolower($m[1]);
							if(in_array($m[1], array_keys($this->country_rel)))
								$domain_arr["country"][] = $m[1];
						}else{
							//$domain_arr["country"][] = "";
						}
						
						//remove subdomain
						/*if(!isset($this->topDomain)){
							self::resetTopDomain();
						}
						preg_match("/([^\.]*)(".implode("|", $this->topDomain).")$/mi", $domain, $m2);	
						//print_r($m2);
						if(isset($m2[1]) && strlen($m2[1])){
							//check head
							if($m2[0] != $domain){
								$domain_arr["domain"][] = $m2[0];
							}			
						}*/
												
						
					}else{
						preg_match("/$domain\\/([a-zA-Z]{2})(?:[\\/\\?]|$)/i", $matches[0][$k], $m);					
						
						if(count($m) && $this->checkDomainCountry($m[1])){
							$m[1] = strtolower($m[1]);
							$domain_arr[] = $m[1].".".$domain;
							$domain_arr[] = $domain."/".$m[1];
						}else{
							$country_code = trim(strtolower(str_replace(",", "|", $this->global_c)), "|");
							preg_match("/$domain([^?&:]*)\\/($country_code)(?:[\\/\\?]|$)/i", $matches[0][$k], $m);
							//print_r($m);
							if(count($m) && $this->checkDomainCountry($m[2])){
								$domain_arr[] = $domain.$m[1]."/".$m[2];
							}else{
								$domain_arr[] = $domain;							
							}
						}
					}
					
					//else
					//{				
						
					//}
				}
			}
		}		
		return $domain_arr;		
	}
	
	function getUrlDomain($url = '') {
		if (empty($url)) return false;
		$url = strtolower($url);
		$topDomain = $this->getTopDomain();
				
		mb_regex_encoding("utf-8");
		if (!mb_ereg($this->url_pattern, trim($url))) return false;
		
		$url = trim(preg_replace("/https?:\/\//i", '', trim($url)));
		
		$url = trim(preg_replace("/(:.*)/i", '', trim($url)));
		
		if (strpos($url, '/') !== false) {
			$url = substr($url, 0, strpos($url, '/'));
		}
		
		if (strpos($url, '.') !== false) {
			$firstPart = substr($url, 0, strpos($url, '.') + 1);
			$leftPart = substr($url, strpos($url, '.') + 1);
			if (strpos($firstPart, 'www') !== false) {
				if (!in_array('.' . $leftPart, $topDomain)) return $leftPart;
			}
		}
		
		return $url;
	}
	
	function checkDomainCountry($country){
		$iscountry = false;
		if(strlen($country) == 2){			
			if(stripos($this->global_c, ",$country,") !== false){
				$iscountry = true;
			}
		}
		return $iscountry;
	}
	
	
	/*
	 * getUrlByTpl($tpl, $pattern = array())
	 * required
	 * $pattern = array("AffId" =>
	 * 					"IdInAff" =>
	 * 					"AffDefaultUrl" => 
	 * 					)
	 * 
	 * need replace val: [AFFDOMAINS], [IDINAFF], [DEFAULTURL], [PARA]
	 * need check connect symbol: [/], [?|&]
	 * 
	 * specil aff : 
	 * 			TradeDoubler(5,27,35,135,415, 469) has two type tpl
	 * 			LinkShare(2) need replace idinaff
	 * 			CJ(1) choose random outgoing domain to replace [AFFDOMAINS] 
	 *			TradeTracker(52,65,425,426,427,2026,2027,2028,2029)
	 * 
	 */
	function getUrlByTpl($tpl, $pattern = array()){
		if(isset($pattern["AffId"])){
			switch ($pattern["AffId"]){
				case 1:
					$tpl = str_replace("[AFFDOMAINS]", $this->pickRandCJDomain(), $tpl);
					break;
				/*
				 * LS idinaff need replace
				 */
				case 2: 
					$pattern["IdInAff"] = preg_replace("/_\d*/", "", $pattern["IdInAff"]);
					break;
				/*
				 * TradeDoubler
				 * http://clkuk.tradedoubler.com/click?p=222731&a=1781705&g=20574256
				 * http://clkuk.tradedoubler.com/click?p(221710)a(1781705)g(20561586)
				 */
				case 5:
				case 27:
				case 35:
				case 133:
				case 415:
				case 429:
				case 47:
				case 469:
				case 667:
				case 2038:
				case 769:
				case 2036:
				case 2039:
				case 2037:
				case 770:
				case 2040:
				case 2050:
				case 2046:
					$tpl_arr = array();
					$tpl_arr = explode("[O|R]", $tpl);
					
					if(count($tpl_arr)){
						if(stripos($pattern["AffDefaultUrl"], ")") !== false && stripos($pattern["AffDefaultUrl"], "(") !== false){
							$tpl = $tpl_arr[0];
						}else{
							$tpl = $tpl_arr[1];
						}
					}
					break;
				
				/*
				 * Share Results
				 * http://shareresults.com/t/url.php/cid/23278/sid/21442/affid/18708/acid/[SUBTRACKING]
				 * http://www.musicademy.com?cid=23080&sid=21442&affid=18708/acid/[SUBTRACKING]
				 */
				case 50:				
					$tpl_arr = array();
					$tpl_arr = explode("[O|R]", $tpl);
					
					if(count($tpl_arr)){
						if(stripos($pattern["AffDefaultUrl"], "musicademy.com") !== false){
							$tpl = $tpl_arr[1];
						}else{
							$tpl = $tpl_arr[0];
						}
					}
					break;
					
				case 8:
				case 181:
				case 397:
				case 23:
					$pattern["AffDefaultUrl"] = html_entity_decode($pattern["AffDefaultUrl"]);
					break;				
					
				/*
				 * Tradetracker  tt=[PARA]
				 * 规则： 
				 *		Default url： http://tc.tradetracker.net/?c=3728&m=155981&a=62862&r=&u= 
				 *		Deeplink url template :	http://tc.tradetracker.net/?c=3728&m=155981&a=62862&r=&u=[ENCODE_URI] 
				 * 不规则： 
				 *		tracking link：http://www.dorisandco.co.uk/home/?tt=8487_321561_62862_&r= 
				 *		Default url: http://www.dorisandco.co.uk/home/?tt=8487_321561_62862_[SUBTRACKING]&r= 
				 *		deep tracking link template: http://www.dorisandco.co.uk/home/?tt=8487_321561_62862_[SUBTRACKING]&r=[ENCODE_URI]
				 */ 				
				case 52:
				case 65:
				case 425:
				case 426:
				case 427:
				case 2026:
				case 2027:
				case 2028:
				case 2029:
				case 2053:
				case 2054:
					$tpl_arr = array();
					$tpl_arr = explode("[O|R]", $tpl);
					if(count($tpl_arr)){
						if(stripos($pattern["AffDefaultUrl"], "tradetracker.net") !== false){
							$tpl = $tpl_arr[0];
						}else{
							$tpl = $tpl_arr[1];
							preg_match("/[\\?&]{1}tt=([^&?\\/]*)/i", $pattern["AffDefaultUrl"], $m);
							//print_r($m);
							if(isset($m[1])){
								$tt_para = $m[1];
								$tt_para = str_replace("[SUBTRACKING]", "", $tt_para);
								//$tpl = str_replace("[PARA]", $m[1], $tpl);
							}
						}
					}
					break;
				
				default:
					break;
			}
		}
		
		if(strpos($tpl, "[DEFAULTURL]") !== false){
			$pattern_val = true;
		}else{
			$pattern_val = false;
		}
		
		/*
		 * [/|?|&url=[DEEPURL]] 		=> array[1][2][3]
		 * [/subId/[SUBTRACKING]]	=> array[4][5]
		 * [url([DEEPURL])]			=> array[6][7]
		 * 
		 */
		preg_match_all("/\\[([&?\\/])([\w]+)=([^&?()\\/]+)\\]|\\[\\/([\w]+)\\/([^&?()\\/]+)\\]|\\[([\w]+)\\(([^)]+)\\)\\]/", $tpl, $m);
		//echo $tpl."\r\n";
		//print_r($m);
		if(count($m)){			
			if(count($m[1]) && count($m[2]) && count($m[3])){
				foreach($m[1] as $k => $v){
					if(strlen($v) > 0){
						$para = $m[2][$k];
						//echo "\r\n".$pattern["AffDefaultUrl"]."\r\n";
						if($pattern_val && preg_match("/([?&\\/]{1}{$para}=[^&?\\/]*)[?&\\/]?/i", $pattern["AffDefaultUrl"], $mm)){
						//if($pattern_val && preg_match("/([?&\\/]?{$para}=.*[^&?\\/]?)[?&\\/]?/i", $pattern["AffDefaultUrl"], $mm)){							
							$pattern["AffDefaultUrl"] = str_replace("{$mm[1]}", "", $pattern["AffDefaultUrl"]);
							
							/*print_r($mm);
							echo "\r\n{$mm[1]} => ''\r\n";
							echo $pattern["AffDefaultUrl"]."\r\n";*/
						}										
						$tpl = str_replace($m[0][$k], "{$m[1][$k]}{$m[2][$k]}={$m[3][$k]}", $tpl);

						switch ($pattern["AffId"]){
							case 52:
							case 65:
							case 425:
							case 426:
							case 427:
							case 2026:
							case 2027:
							case 2028:
							case 2029:
							case 2053:
							case 2054:
								if(isset($tt_para))
									$tpl = str_replace("tt=[SUBTRACKING]", "tt={$tt_para}[SUBTRACKING]", $tpl);
								break;
						}
						/*echo "\r\n{$m[0][$k]} => {$m[1][$k]}{$m[2][$k]}={$m[3][$k]}\r\n";
						echo $tpl."\r\n";
						echo "########################################################\r\n";
						exit;		*/
					}					
				}
			}
			if(count($m[4]) && count($m[5])){
				foreach($m[4] as $k => $v){
					if(strlen($v) > 0){
						$para = $m[4][$k];
						if($pattern_val && preg_match("/[?&\\/]?({$para}\\/[^\\/]*)\\/?/i", $pattern["AffDefaultUrl"], $mm)){
							
							//print_r($mm);
							//$pattern["AffDefaultUrl"] = str_replace("{$mm[1]}", "{$m[5][$k]}", $pattern["AffDefaultUrl"]);
							//$tpl = str_replace($m[0][$k], '', $tpl);
							$pattern["AffDefaultUrl"] = str_replace("{$mm[1]}", "", $pattern["AffDefaultUrl"]);
						}
						$tpl = str_replace($m[0][$k], "[/]{$m[4][$k]}/{$m[5][$k]}", $tpl);
					}
				}
			
			}			
			if(count($m[6]) && count($m[7])){
				foreach($m[6] as $k => $v){
					if(strlen($v) > 0){
						$para = $m[6][$k];
						if($pattern_val && preg_match("/[?&\\/()]?({$para}\\([^)]+\\))/i", $pattern["AffDefaultUrl"], $mm)){
							//print_r($m);
							//print_r($mm);
							//echo "\r\n{$mm[1]} => \r\n";
							
							//$pattern["AffDefaultUrl"] = str_replace("{$mm[1]}", "{$m[7][$k]}", $pattern["AffDefaultUrl"]);
							//$tpl = str_replace($m[0][$k], '', $tpl);
							
							$pattern["AffDefaultUrl"] = str_replace("{$mm[1]}", "", $pattern["AffDefaultUrl"]);					
							
							//echo $pattern["AffDefaultUrl"]."\r\n".$tpl."\r\n";
						}
						$tpl = str_replace($m[0][$k], "{$m[6][$k]}({$m[7][$k]})", $tpl);						
					}
					//echo "\r\n".$tpl."\r\n";
				}
			
			}
		}
		$tpl = $this->pureUrl($tpl, array("[IDINAFF]" => $pattern["IdInAff"], "[DEFAULTURL]" => $pattern["AffDefaultUrl"]));
		
//		//$tpl = isset($pattern["IdInAff"]) ? str_replace("[IDINAFF]", $pattern["IdInAff"], $tpl) : $tpl;
//		//$tpl = isset($pattern["AffDefaultUrl"]) ? str_replace("[DEFAULTURL]", $pattern["AffDefaultUrl"], $tpl) : $tpl;
//		$tpl = str_replace("[IDINAFF]", $pattern["IdInAff"], $tpl);
//		$tpl = str_replace("[DEFAULTURL]", $pattern["AffDefaultUrl"], $tpl);		
//		if(strpos($tpl, '[?|&]') !== false){
//		    if (preg_match('/[\?&][^&]+=[^&]*/U', $tpl))
//				$tpl = str_replace('[?|&]', '&', $tpl);
//			else
//				$tpl = str_replace('[?|&]', '?', $tpl);
//		}		
//		if(($tmp_pos = strpos($tpl, '[/]')) !== false){
//		    if(substr($tpl, $tmp_pos - 1, 1) == '/'){
//		    	$tpl = str_replace('[/]', '', $tpl);
//		    }else{
//		    	$tpl = str_replace('[/]', '/', $tpl);
//		    }
//		}
		
		
		return $tpl;
	}
	
	//replace internal symbol
	function pureUrl($url, $symbol_arr = array()){
		foreach($symbol_arr as $k => $v){
			$url = str_ireplace($k, $v, $url);
		}
		if(strpos($url, 'http') === 0){
			//if(strpos($url, '[?|&]') !== false){
			//    if (preg_match('/[\?&][^&]+=[^&]*/U', $url))
			//		$url = str_replace('[?|&]', '&', $url);
			//	else
			//		$url = str_replace('[?|&]', '?', $url);
			//}
			
			if(($tmp_pos = strpos($url, '[/]')) !== false){
			    if(substr($url, $tmp_pos - 1, 1) == '/'){
			    	$url = str_replace('[/]', '', $url);
			    }else{
			    	$url = str_replace('[/]', '/', $url);
			    }
			}
		}
		
		$url = preg_replace("/\\s/", "", $url);
		
		return $url;
	}

	function pickRandCJDomain(){
		//"affiliate.buy.com"
		$domain_arr = array("www.jdoqocy.com", "www.anrdoezrs.net", "www.kqzyfj.com", "www.tqlkg.com", "www.dpbolvw.net", "www.tkqlhce.com", "www.qksrv.net");	
		return $domain_arr[array_rand($domain_arr)];
	}
	
	
	/*
	 * $prgm_info = array()
	 * reqired (AffId, IdInAff, Name, Domain)
	 * 
	 * choose default link	 * 
	 * 
	 */
	function getDefaultLinkByPrgm($prgm_info, $aff_keyword, $finddeep = false){
		$return_link = "";
		
		//temp
		$links_arr = array();
		//echo "\r###########################\n";
		if($prgm_info["AffId"] != 1 && $prgm_info["AffId"] != 160){
		
			if(!isset($this->aff_links_arr[$prgm_info["AffId"]])){
				unset($this->aff_links_arr);
				$tmp_arr = $this->getLinksFromAffiliateBdg($prgm_info["AffId"]);
				//echo count($tmp_arr);
				foreach($tmp_arr as $v){
					$this->aff_links_arr[$prgm_info["AffId"]][$v["AffMerchantId"]][] = $v;
				}
				unset($tmp_arr);
			}
		}
		$links_arr = isset($this->aff_links_arr[$prgm_info["AffId"]][$prgm_info["IdInAff"]]) ? $this->aff_links_arr[$prgm_info["AffId"]][$prgm_info["IdInAff"]] : array();			
	//	print_r($links_arr);
		//if(!count($links_arr) && $prgm_info["AffId"] != 160){
		if(!count($links_arr)){		
			$links_arr = $this->getLinksFromAffiliate($prgm_info["AffId"], $prgm_info["IdInAff"]);
		}
		
		//LinkHtmlCode, LinkName, LinkEndDate
		
		$has_old = false;
		if(isset($prgm_info["old_AffDefaultUrl"]) && strlen($prgm_info["old_AffDefaultUrl"])){
			$has_old = true;
		} 
		
		$tmp_arr = array();
		//$i = 0;
		foreach($links_arr as $v){
			if($has_old && $prgm_info["old_AffDefaultUrl"] == $v["LinkAffUrl"]){
				$tmp_arr[999999] = $prgm_info["old_AffDefaultUrl"];
				break;
			}
			$affurl = "";
			if(strlen($v["LinkAffUrl"])){
				$affurl = $v["LinkAffUrl"];
			}else{
				preg_match("/<a[^\\/><]+href=(\"|')(.*)\\1/Ui", $v["LinkHtmlCode"], $matches);				
				$affurl = isset($matches[2]) ? $matches[2] : "";
			}
			if($affurl){
				$is_aff = false;
				if(isset($aff_keyword[$prgm_info["AffId"]])){					
					foreach($aff_keyword[$prgm_info["AffId"]] as $keyword){
						if(stripos($affurl, $keyword) !== false){
							$is_aff = true;
							break;
						}
					}
				}else{
					 //never blue(123)	
					 if($prgm_info["AffId"] == 123) $is_aff = true;
				}
				
				if($is_aff){
					
					switch($prgm_info["AffId"]){
						case 152:// http://www1.belboon.de/adtracking/0342f308cd7a037d7b0049a6.html/deeplink=[DeepLink-Url] 
							if(stripos($affurl, "[DeepLink-Url]") !== false){
								$affurl = str_ireplace("/deeplink=[DeepLink-Url]", "", $affurl);
								$affurl = preg_replace("/html.*/i", "html", $affurl);
								continue;
							}
							break;						
						default:
							break;
					}
								
					if(!$finddeep && $prgm_info["AffId"] == 1 && $v['LinkPromoType'] == 'deeplink'){								
						continue;						
					}elseif($finddeep && $prgm_info["AffId"] == 1 && $v['LinkPromoType'] != 'deeplink'){
						continue;
					}			
					/*
					 * #1, LinkName LinkDesc LinkHtmlCode contain program domain	--- 20 ~ 40
					 * #2, LinkName LinkDesc LinkHtmlCode contain program name		--- 10 ~ 20
					 * #3, LinkName LinkDesc LinkHtmlCode contain #homepage# word	--- 10
					 * #4, LinkName LinkDesc contain #logo# word					--- 10
					 * #5, text links > image 										--- 5
					 * #6, no expire > expired always								--- 5
					 * 					  
					 */					
					$weight = 0;		
					
					#1
					if(stripos($v["LinkName"], $prgm_info["Domain"]) !== false){
						$weight += (strlen($v["LinkName"]) >= 20) ? 20 : 40 - strlen($v["LinkName"]);
					}elseif(stripos($v["LinkDesc"], $prgm_info["Domain"]) !== false){
						$weight += (strlen($v["LinkDesc"]) >= 20) ? 20 : 40 - strlen($v["LinkDesc"]);
					}elseif(stripos(strip_tags($v["LinkHtmlCode"]), $prgm_info["Domain"]) !== false){
						$weight += (strlen(strip_tags($v["LinkHtmlCode"])) >= 20) ? 20 : 40 - strlen(strip_tags($v["LinkHtmlCode"]));
					}
					
					#2
					if(stripos($v["LinkName"], $prgm_info["Name"]) !== false){
						$weight += (strlen($v["LinkName"]) >= 10) ? 10 : 20 - strlen($v["LinkName"]);
					}elseif(stripos($v["LinkDesc"], $prgm_info["Name"]) !== false){
						$weight += (strlen($v["LinkDesc"]) >= 10) ? 10 : 20 - strlen($v["LinkDesc"]);
					}elseif(stripos(strip_tags($v["LinkHtmlCode"]), $prgm_info["Name"]) !== false){
						$weight += (strlen(strip_tags($v["LinkHtmlCode"])) >= 10) ? 10 : 20 - strlen(strip_tags($v["LinkHtmlCode"]));
					}
					
					#3
					if(preg_match("/\bhome *page\b/i", $v["LinkName"]) || preg_match("/\bhome *page\b/i", $v["LinkDesc"]) || preg_match("/\bhome *page\b/i", strip_tags($v["LinkHtmlCode"]))){
						$weight += 10;
					}
					
					#4
					if(preg_match("/\blogo\b/i", $v["LinkName"]) || preg_match("/\blogo\b/i", $v["LinkDesc"])){
						$weight += 10;
					}
					
					#5
					if(strlen($v["LinkImageUrl"]) == 0){
						$weight += 5;
					}
					
					#6
					if($v["LinkEndDate"] == "0000-00-00 00:00:00"){
						$weight += 5;
					}
					
					/*if($v["LinkEndDate"] == "0000-00-00 00:00:00"){						
						//ignore repeat
						$tmp_arr[1][$weight] = $affurl;
					}else{
						$expire_index = strtotime($v["LinkEndDate"]);						
						$tmp_arr[0][$weight + $expire_index] = $affurl;
					}*/
					$tmp_arr[$weight] = $affurl;
					//$i++;
				}
			}
		}		
		//print_r($tmp_arr);
		/*if(isset($tmp_arr[1])){
			ksort($tmp_arr[1]);
			$return_link = array_pop($tmp_arr[1]);
		}elseif(isset($tmp_arr[0])){
			ksort($tmp_arr[0]);
			$return_link = array_pop($tmp_arr[0]);
		}*/
		ksort($tmp_arr);
		//print_r($tmp_arr);
		$return_link = array_pop($tmp_arr);
		
		return $return_link;
	}
	
	function getProgramOutUrl($p_v, $aff_keyword){
		$links_arr = array("AffDefaultUrl" => "", "DeepUrlTpl" => "", "OutGoingUrl" => "");
		
		if(!count($this->aff_url_pattern)){
			$this->aff_url_pattern = $this->getAffUrlPattern();
		}
		$ishandle = 0;

		if(!isset($this->old_ps[$p_v["AffId"]]) || !count($this->old_ps[$p_v["AffId"]])){
			if($p_v["AffId"] != 191 && $p_v["AffId"] != 2032){				
				$this->old_ps[$p_v["AffId"]] = array();								
				/*$sql = "SELECT a.`AffiliateDefaultUrl`, a.`DeepUrlTemplate`, a.ProgramId, a.Order FROM program_store_relationship a WHERE a.status = 'active' and a.isfake = 'NO' order by a.`order`";			
				$tmp_arr = array();
				$tmp_arr = $this->objTaskMysql->getRows($sql);
				foreach($tmp_arr as $k => $v){
					$this->old_ps[$v["ProgramId"]][$v["Order"]] = $v;
				}
				unset($tmp_arr);*/
				
				$sql = "SELECT a.affdefaulturl as AffiliateDefaultUrl, a.deepurltpl as DeepUrlTemplate, a.pid as ProgramId, a.ishandle, 1 as `Order` 
						FROM r_domain_program a INNER JOIN program b on a.pid = b.id WHERE a.status = 'active' and a.isfake = 'NO' and a.affdefaulturl <> ''
						AND b.affid = {$p_v["AffId"]} #and a.ishandle = '1'";
				$tmp_arr = array();
				$tmp_arr = $this->objMysql->getRows($sql);
				foreach($tmp_arr as $k => $v){
					$this->old_ps[$p_v["AffId"]][$v["ProgramId"]][$v["Order"]] = $v;
				}
				unset($tmp_arr);
			}
		}

		//if(empty($links_arr["OutGoingUrl"])){		
			//$tmp_arr = array();
			//$tmp_arr = $this->getOrginProgramDefaultUrl($p_v["ID"]);
			if(isset($this->old_ps[$p_v["AffId"]][$p_v["ID"]])){
				$tmp_arr = current($this->old_ps[$p_v["AffId"]][$p_v["ID"]]);
				$links_arr = array("AffDefaultUrl" => $tmp_arr["AffiliateDefaultUrl"], "DeepUrlTpl" => $tmp_arr["DeepUrlTemplate"]);
				$links_arr["OutGoingUrl"] = strlen($links_arr["DeepUrlTpl"]) ? $links_arr["DeepUrlTpl"] : $links_arr["AffDefaultUrl"];
					//break;
				if($tmp_arr["ishandle"] == 1){
					$ishandle = 1;
				}
			}
			
			//print_r($links_arr);
		//}
		if(!empty($links_arr["OutGoingUrl"]) && empty($links_arr["DeepUrlTpl"]) && $ishandle != 1){
			if(isset($this->aff_url_pattern[$p_v["AffId"]])){
				if(@$this->aff_url_pattern[$p_v["AffId"]]["SupportDeepUrlTpl"] == "YES" && $p_v["SupportDeepUrl"] != "NO" && strlen($this->aff_url_pattern[$p_v["AffId"]]["TplDeepUrlTpl"])){
					$links_arr["DeepUrlTpl"] = $this->getUrlByTpl($this->aff_url_pattern[$p_v["AffId"]]["TplDeepUrlTpl"], array("AffId" => $p_v["AffId"], "IdInAff" => $p_v["IdInAff"], "AffDefaultUrl" => $links_arr["AffDefaultUrl"]));					
					$links_arr["OutGoingUrl"] = $links_arr["DeepUrlTpl"];
				}
			}
		}
		
		//print_r($links_arr);
		
		if(empty($links_arr["OutGoingUrl"])){
			if(isset($this->aff_url_pattern[$p_v["AffId"]])){
				if($p_v["AffId"] == 191){
					$this->prgm_intell[$p_v["AffId"]][$p_v["ID"]] = $this->getPrgmIntellById($p_v["ID"]);		
				}elseif(!isset($this->prgm_intell[$p_v["AffId"]])){
					$this->prgm_intell[$p_v["AffId"]] = $this->getPrgmIntellByAffId($p_v["AffId"]);				
				}

				$links_arr["AffDefaultUrl"] = $p_v["AffDefaultUrl"];
				if(!strlen($links_arr["AffDefaultUrl"])){
					if(isset($this->prgm_intell[$p_v["AffId"]]["AffDefaultUrl"]) && strlen($this->prgm_intell[$p_v["AffId"]]["AffDefaultUrl"])){
						$p_v["old_AffDefaultUrl"] = $this->prgm_intell[$p_v["AffId"]]["AffDefaultUrl"];
					}

					if($p_v["AffId"] == 1 && $p_v["SupportDeepUrl"] == 'YES'){
						$links_arr["AffDefaultUrl"] = $this->getUrlByTpl('http://[AFFDOMAINS]/links/[SITEIDINAFF]/type/dlg/sid/[SUBTRACKING]/[PURE_DEEPURL]', array("AffId" => $p_v["AffId"], "IdInAff" => $p_v["IdInAff"], "AffDefaultUrl" => ''));
					}else{
						$links_arr["AffDefaultUrl"] = $this->getDefaultLinkByPrgm($p_v, $aff_keyword);
						if($p_v["AffId"] == 152 && $links_arr["AffDefaultUrl"]){
							$links_arr["AffDefaultUrl"] = $this->getUrlByTpl($this->aff_url_pattern[$p_v["AffId"]]["TplAffDefaultUrl"], array("AffId" => $p_v["AffId"], "IdInAff" => $p_v["IdInAff"], "AffDefaultUrl" => $links_arr["AffDefaultUrl"]));
						}
					}

					if(!strlen($links_arr["AffDefaultUrl"]) && $this->aff_url_pattern[$p_v["AffId"]]["NeedAffDefaultUrl"] == "NO"){
						$links_arr["AffDefaultUrl"] = $this->getUrlByTpl($this->aff_url_pattern[$p_v["AffId"]]["TplAffDefaultUrl"], array("AffId" => $p_v["AffId"], "IdInAff" => $p_v["IdInAff"], "AffDefaultUrl" => $links_arr["AffDefaultUrl"]));
					}
				}elseif($p_v["AffId"] == 188 || in_array($p_v["AffId"], $this->aff_tt)){
					$links_arr["AffDefaultUrl"] = $this->getUrlByTpl($this->aff_url_pattern[$p_v["AffId"]]["TplAffDefaultUrl"], array("AffId" => $p_v["AffId"], "IdInAff" => $p_v["IdInAff"], "AffDefaultUrl" => $links_arr["AffDefaultUrl"]));
				}

				if(strlen($links_arr["AffDefaultUrl"]) || $this->aff_url_pattern[$p_v["AffId"]]["NeedAffDefaultUrl"] == "NO"){
					if($p_v["AffId"] == 1){
						if($p_v["SupportDeepUrl"] == 'YES'){
							$links_arr["DeepUrlTpl"] = $this->getUrlByTpl('http://[AFFDOMAINS]/links/[SITEIDINAFF]/type/dlg/sid/[SUBTRACKING]/[PURE_DEEPURL]', array("AffId" => $p_v["AffId"], "IdInAff" => $p_v["IdInAff"], "AffDefaultUrl" => $links_arr["AffDefaultUrl"]));					
							$links_arr["OutGoingUrl"] = $links_arr["DeepUrlTpl"];
						}else{
							$tmp_url = $this->getDefaultLinkByPrgm($p_v, $aff_keyword, 1);
							if(strlen($tmp_url)){
								$links_arr["DeepUrlTpl"] = $this->getUrlByTpl($this->aff_url_pattern[$p_v["AffId"]]["TplDeepUrlTpl"], array("AffId" => $p_v["AffId"], "IdInAff" => $p_v["IdInAff"], "AffDefaultUrl" => strlen($tmp_url) ? $tmp_url : $links_arr["AffDefaultUrl"]));
								$links_arr["OutGoingUrl"] = $links_arr["DeepUrlTpl"];
							}else{
								$links_arr["OutGoingUrl"] = $links_arr["AffDefaultUrl"];
							}
						}

					}elseif($p_v["AffId"] == 604 && !empty($p_v["SecondIdInAff"])){
						$links_arr["DeepUrlTpl"] = "[PURE_DEEPURL][?|&]#".urlencode($p_v["SecondIdInAff"]);
						$links_arr["OutGoingUrl"] = $links_arr["DeepUrlTpl"];
					}
					elseif(@$this->aff_url_pattern[$p_v["AffId"]]["SupportDeepUrlTpl"] == "YES" && $p_v["SupportDeepUrl"] != "NO" && strlen($this->aff_url_pattern[$p_v["AffId"]]["TplDeepUrlTpl"])){
						$links_arr["DeepUrlTpl"] = $this->getUrlByTpl($this->aff_url_pattern[$p_v["AffId"]]["TplDeepUrlTpl"], array("AffId" => $p_v["AffId"], "IdInAff" => $p_v["IdInAff"], "AffDefaultUrl" => $links_arr["AffDefaultUrl"]));					
						$links_arr["OutGoingUrl"] = $links_arr["DeepUrlTpl"];
					}
					
					if(!strlen($links_arr["DeepUrlTpl"])){
						$links_arr["OutGoingUrl"] = $this->getUrlByTpl($this->aff_url_pattern[$p_v["AffId"]]["TplAffDefaultUrl"], array("AffId" => $p_v["AffId"], "IdInAff" => $p_v["IdInAff"], "AffDefaultUrl" => $links_arr["AffDefaultUrl"]));
						//for noneedafdefaulturl
						//$links_arr["OutGoingUrl"] = str_replace("[DEEPURL]", urlencode($p_v["Homepage"]), $links_arr["OutGoingUrl"]);
						//$links_arr["OutGoingUrl"] = str_replace("[PURE_DEEPURL]", $p_v["Homepage"], $links_arr["OutGoingUrl"]);
						$links_arr["AffDefaultUrl"] = $links_arr["OutGoingUrl"];
					}			
				}
			}
		}
		
		if($p_v["AffId"] == 539 && !empty($links_arr["DeepUrlTpl"])){
			$links_arr["AffDefaultUrl"] = str_replace('[DEEPURL]', urlencode(trim($p_v["Homepage"])), $links_arr["DeepUrlTpl"]);
		}
		
		return $links_arr;
	}
	
	function checkProgramDomain($pid, $domain_arr){
		//check if isset new domain
		$this->insertDomain($domain_arr);
		//update domain program relationship
		$this->setDomainProgramRelationship($pid, $domain_arr);		
	}
	
	function setDomainProgramRelationship($pid, $domain_arr){
		$old_active_rel = array();
		$old_active_rel = $this->getDomainProgramRelationshipByPid($pid);
		
		$new_rel = array();
		$new_domain_id = $this->getDomainInfoByDomain($domain_arr);
		foreach($new_domain_id as $did => $v){
			if(!isset($old_active_rel[$did])){
				$new_rel[$did] = $did;		
			}else{
				unset($old_active_rel[$did]);
			}
		}

		$union_rel = array();
		$union_rel = $this->getDomainUnionByDomain(array_keys($new_rel));
		foreach($union_rel as $v){			
			unset($old_active_rel[$v["DomainToid"]]);
			unset($old_active_rel[$v["DomainFromid"]]);			
		}
		if(count($old_active_rel)){
			$this->deleteDomainProgramRelationship(array($pid => array_keys($old_active_rel)));
		}
		
		if(count($new_rel)){
			$this->addDomainProgramRelationship(array($pid => $new_rel));
		}
	}
	
	function checkDefaultOutgoingChanged($did){
		$return_arr = array();
		if(is_numeric($did) && $did > 0){ 
			$old_rel = array();
			$old_rel = $this->getDefaultOutgoingByDomain($did);

			$new_rel = array();
			$new_rel = $this->getDefaultOutgoingByStrategy($did);	

			foreach($new_rel as $k => $v){
				if(isset($old_rel[$k])){
					if($old_rel[$k]["PID"] != $v["PID"] || $old_rel[$k]["LimitAccount"] != $v["LimitAccount"]){
						$return_arr[] = array("old" => $old_rel[$k], "new" => $v);
					}
					$return_arr[] = array("old" => $old_rel[$k], "new" => $v);
					unset($old_rel[$k]);					
				}else{
					$return_arr[] = array("new" => $v);
				}
			}
			
			foreach($old_rel as $v){
				$return_arr[] = array("old" => $v);				
			}
		}
		return $return_arr;
	}
	
	function orderProgram($prgm_arr, $country_code = '', $site = '', $domain_info = array()){
		if(!isset($this->exchange_rate)){
			$sql = "SELECT ExchangeRate, `Name` FROM exchange_rate WHERE `Date` = (SELECT MAX(`Date`) FROM exchange_rate) GROUP BY `Name`";
			$this->exchange_rate = $this->objMysql->getRows($sql, "Name");
		}
		$prgm_order = array("Content" =>array("main" => array(), "sub" => array()),"All" =>array("main" => array(), "sub" => array()));
		$_diff = 1;
		$debug_order = "";		
		if(is_array($prgm_arr)){
			// for FR: AW(10) and Zanox(15); if has AW and Zanox, use AW.
			if($site == 'fr'){
				$has_aw = false;
				foreach($prgm_arr as $v){
					if($v["AffId"] == 10){
						$has_aw = true;
						break;
					}
				}
			}
			$prgm_arr_detail = array();
			foreach ($prgm_arr as $k=>$v)
			{
				if(SID == 'bdg01')
				{
					$prgm_arr_detail['All'][$k] = $v;
				}
				else
				{
					if($v['SupportType'] == 'Content')
						$prgm_arr_detail['Content'][$k] = $v; // add content type programs 
					else
					{
						$prgm_arr_detail['All'][$k] = $v;// only have promotion type programs
						$prgm_arr_detail['Content'][$k] = $v;// add promotion type programs 
					}
				}
				
			}
			unset($prgm_arr);
			foreach ($prgm_arr_detail as $k=>$prgm_arr)
			{
				foreach($prgm_arr as $v){
					if(!strlen($v["AffDefaultUrl"]) && !strlen($v["DeepUrlTpl"])) continue;
					
					$is_subaff = true;
					$v["LimitAccount"] = array();
					//check limited aff OR program
					if(isset($this->block_rel["aff"][$v["AffId"]])){
						$v["LimitAccount"] = count($v["LimitAccount"]) ? array_merge($v["LimitAccount"], $this->block_rel["aff"][$v["AffId"]]) :  $this->block_rel["aff"][$v["AffId"]];
					}
					if(isset($this->block_rel["program"][$v["PID"]])){
						$v["LimitAccount"] = count($v["LimitAccount"]) ? array_merge($v["LimitAccount"], $this->block_rel["program"][$v["PID"]]) :  $this->block_rel["program"][$v["PID"]];
					}
					
					//print_r($this->block_rel);
					
					if($site != "uk" && $v["AffId"] == "57"){
						//OMGpm UK (57) not work with un uk site.
						continue;
					}
					
					$order = 0;
					
					if($domain_info['Domain'] == $v['Domain']) $order += 10;
					if(SID == 'bdg02' && $domain_info['Domain'] == $v['Domain']) $order += 150;
					
					if(!empty($site)){
						if(stripos(",".$v['ShippingCountry'].",", ",{$site},") !== false){
							$order += 20;
						}elseif(empty($v['ShippingCountry'])){
							$order += 19.6;
						}
					}
					
					//if(!empty($country_code) && stripos(",".$v['ShippingCountry'].",", ",{$country_code},") !== false) $order += 10;
					if(empty($site) && empty($country_code)){
						if(empty($v['ShippingCountry'])){
							$order += 100;
						}elseif(stripos(",".$v['ShippingCountry'].",", ",us,") !== false){
							$order += 70;
						}elseif(stripos(",".$v['ShippingCountry'].",", ",uk,") !== false){
							$order += 20;
						}
					}
					$debug_order .= "\r\np({$v["PID"]})[{$v["AffId"]}] country:".$order."\t";
					
					if(isset($v['IsFake']) && $v['IsFake'] == "NO"){
						$order += 100;
						$debug_order .= "\t notfake:".$order."\t";
					}
					
					#1
					//if($v["AffId"] == "37" || $v["AffId"] == "177" || $v["AffId"] == "97") continue;
					if($v["AffId"] == "97") continue;
					if($v["AffId"] == "177") $order -= 2000;
					if($v["AffId"] == "37") $order -= 2000;
					
					if(!in_array($v["AffId"], $this->sub_aff)){
						$order += 2000;
						$is_subaff = false;
						//if($v["AffId"] == "37") $order -= 2000;
						if(isset($v['IsFake']) && $v['IsFake'] == "YES"){
							$order -= 100;
							$revenueorder = isset($v['revenueorder']) ? intval($v['revenueorder']) : 9999999;
							$revenueorder = ($revenueorder < 10000) ? round(100 - ($revenueorder / 100), 5) : 0;
							$order += $revenueorder;
						}
					}else{
						$revenueorder = isset($v['revenueorder']) ? intval($v['revenueorder']) : 9999999;
						$revenueorder = ($revenueorder < 10000) ? round(100 - ($revenueorder / 100), 5) : 0;
						$order += $revenueorder;
						/*if($v["AffId"] == 160){
							$order += 200;
						}elseif($v["AffId"] != 191){// viglink is last hope
							$order += 100;
						}*/
					}
					$debug_order .=  "aff:".$order."\t";
					#2
					if(strlen($v["DeepUrlTpl"])){
						$order += 100;
					}elseif(strlen($v["AffDefaultUrl"])){
						$order += 10;
					}
					
					if(strtolower($v["SupportDeepUrl"]) == "yes"){
						$order += 20;
					}elseif(strtolower($v["SupportDeepUrl"]) != "no"){
						$order += 19.5;
						if(strlen($v["DeepUrlTpl"])){
							$order += 0.5;
						}
					}else{
						if(strlen($v["DeepUrlTpl"])){
							$order += 19.99;
						}
					}
					$debug_order .=  "deepurltpl:".$order."\t";
					
					#3
					//click bank commission , different
					if($v["AffId"] == "37") $v["CommissionUsed"] = $v["CommissionUsed"] / 10 ;
					
					if($v["CommissionUsed"] == 0 || $v["AffId"] == 191){
						$order -= 100;
					}
					
					//error
					if($v["CommissionUsed"] == 100 && $v["CommissionType"] == "Percent"){
						$v["CommissionUsed"] = $v["CommissionUsed"] / 10 ;
					}
					
					//exchange_rate
					if(isset($this->exchange_rate[$v["CommissionCurrency"]]) && $v["CommissionCurrency"] != "USD"){
						//echo $v["CommissionUsed"];
						$v["CommissionUsed"] = ($v["CommissionUsed"]*$this->exchange_rate[$v["CommissionCurrency"]]["ExchangeRate"])/$this->exchange_rate["USD"]["ExchangeRate"];
						
						//echo "/".$v["CommissionUsed"];
					}
					
					$order += floatval($v["CommissionUsed"]);
					if($v["CommissionType"] == "Value"){
						$order += 0.1;
					}
					if($v["CommissionIncentive"] == 1){
						//$order += 0.1;
					}
					$debug_order .=  "commission:".$order."\t";
					#4	//rank 101 ~ 99999999
					if(!count($this->aff_rank)){
						$this->aff_rank = $this->getAffRank();
					}
					$tmp_rank = (isset($this->aff_rank[$v["AffId"]]["Rank"]) && intval($this->aff_rank[$v["AffId"]]["Rank"])) ? round((10 / intval($this->aff_rank[$v["AffId"]]["Rank"])), 5) : 0;
					
					$order += ($tmp_rank / 10);
					
					// for FR: AW(10) and Zanox(15); if has AW and Zanox, use AW.
					if($site == 'fr' && $has_aw && $v["AffId"] == 15){
						$order -= 1000;
					}
					
					
					$debug_order .=  "affrank:".$order."\t";
					
					$order *= 100000;
					//echo "ccc:".$v["AffId"]."_".$country_code."\r\n";
					if(in_array($v["AffId"], array(13,14,34,208,395))){
						if($site == "au" && $v["AffId"] == 395){
							$order += 10;
						}elseif($site == 'uk' && $v["AffId"] == 13){
							$order += 10;
						}elseif($site == "de" && $v["AffId"] == 34){
							$order += 10;
						}elseif($site == 'fr' && $v["AffId"] == 208){
							$order += 10;
						}elseif($site == 'us' && $v["AffId"] == 14){
							$order += 10;
						}
					}
					
					$sql = "select ShippingCountry,ExtraWeight from program_order_manual where ProgramID='{$v['PID']}'";
					$tmp_data = $this->objMysql->getFirstRow($sql);
					if(!empty($tmp_data)){
						if(empty($tmp_data['ShippingCountry']))
							$order += intval($tmp_data['ExtraWeight']);
						else
						{
							$country_arr = explode(',',$tmp_data['ShippingCountry']);
							if(in_array($site,$country_arr))
								$order += intval($tmp_data['ExtraWeight']);
						}
					}
					$order = intval($order);
					if($is_subaff){
						if(isset($prgm_order[$k]["sub"][$order])){
							$order -= $_diff;
							$_diff ++;
						}
						$prgm_order[$k]["sub"][$order] = $v;
					}else{
						if(isset($prgm_order[$k]["main"][$order])){
							$order -= $_diff;
							$_diff ++;
						}
						$prgm_order[$k]["main"][$order] = $v;
					}
					$debug_order .=  "\t$".$order."\r\n";
				}
			}
		}
		ksort($prgm_order['All']["sub"]);
		reset($prgm_order['All']["sub"]);
		ksort($prgm_order['All']["main"]);
		reset($prgm_order['All']["main"]);
		ksort($prgm_order['Content']["sub"]);
		reset($prgm_order['Content']["sub"]);
		ksort($prgm_order['Content']["main"]);
		reset($prgm_order['Content']["main"]);
		
		global $is_debug;
		if($is_debug){
			print_r($prgm_order);
			echo "\r\norder:".$debug_order."\r\n";
		}
		return $prgm_order;
	}
	
	function formatDefaultOutgoing($domain_info, $prgm_main = array(), $prgm_sub = array()){
		$default_prgm = array();
		$tmp_arr = array();
		if(count($prgm_main)){
			$tmp_arr = array_pop($prgm_main);
		}elseif(count($prgm_sub)){
			$tmp_arr = array_pop($prgm_sub);
		}
		if(count($tmp_arr)){
			$default_prgm[$domain_info["Domain"]] = $tmp_arr;
			$default_prgm[$domain_info["Domain"]]["DID"] = $domain_info["ID"];
			$default_prgm[$domain_info["Domain"]]["Domain"] = $domain_info["Domain"];
			$default_prgm[$domain_info["Domain"]]["Key"] = $domain_info["Domain"];
			$default_prgm[$domain_info["Domain"]]["LimitAccount"] = count($tmp_arr["LimitAccount"]) ? implode(",", $tmp_arr["LimitAccount"]) : "";
		}
		return $default_prgm;
	}
	
	/*
	 * 
	 * check block relationship first
	 * 
	 * 
	 * Order by Strategy
	 * #1, main aff, sub aff
	 * #2, SupportDeepUrl
	 * #3, commission order by val, type(%, $), incetive 
	 * #4, affiliate rank
	 * #5, DeniedPubCode
	 */
	function getDefaultOutgoingByStrategy($did, $site = ''){
		$default_prgm = array();
		$old_ps_arr = array();
		global $is_debug, $self;
		//get domain info
		$domain_info = $this->getDomainInfoById($did);
		if($is_debug){
			//echo "checkDomainProgramRel_Sp:getDefaultOutgoingByStrategy:getDomainInfoById\n";
			//print_r($domain_info);
		}
			
		if($domain_info["SupportAff"] == "NO"){
			if($is_debug){
				//echo "checkDomainProgramRel_Sp:getDefaultOutgoingByStrategy:default_prgm\n";
				//print_r($default_prgm);
			}
			return $default_prgm;
		}
			
		$can_ship = array($site => $site);
		if($site == 'de'){
			$can_ship = array('de' => 'de', 'ch' => 'ch', 'at' => 'at', 'dk' => 'dk');
		}elseif($site == 'uk'){
			$can_ship = array('uk' => 'uk', 'gb' => 'gb', 'ie' => 'ie');
		}elseif($site == 'au'){
			$can_ship = array('au' => 'au', 'nz' => 'nz', 'us' => 'us', 'ca' => 'ca');
		}elseif($site == 'us'){
			$can_ship = array('us' => 'us', 'ca' => 'ca', 'uk' => 'uk');
		}elseif($site == 'ca'){
			$can_ship = array('ca' => 'ca');
		}elseif($site == 'fr'){
			$can_ship = array('fr' => 'fr');
		}else{
			$can_ship = array();
		}
		$check_site = array('fr');

		if(!isset($this->aff_marketing)){
			$this->aff_marketing = array();
			$sql = "SELECT id, lower(MarketingContinent) as MarketingContinent, lower(MarketingCountry) as MarketingCountry FROM affiliate WHERE IsActive = 'yes' AND ( MarketingContinent <> '' OR MarketingCountry <> '')";			
			$this->aff_marketing = $this->objMysql->getRows($sql, "id");
		}
		if($is_debug){
			//echo "checkDomainProgramRel_Sp:getDefaultOutgoingByStrategy:aff_marketing\n";
			//print_r($this->aff_marketing);
		}

		//if the relationship by human haven't set,get them(human first)
		if(!isset($this->domain_program_ctrl)){
			$this->domain_program_ctrl = array();
			$sql = "select `domainid`, `programid`, lower(country) as country from r_domain_program_ctrl where status = 'active'";
			$tmp_arr = $this->objMysql->getRows($sql);
			foreach($tmp_arr as $v){
				if(!empty($v["country"])){
					$this->domain_program_ctrl[$v['domainid']][$v["country"]] = $v["programid"];
				}else{
					$this->domain_program_ctrl[$v['domainid']]['global'] = $v["programid"];
				}					
			}
			unset($tmp_arr);
		}
		if($is_debug){
			//echo "checkDomainProgramRel_Sp:getDefaultOutgoingByStrategy:domain_program_ctrl\n";
			//print_r($this->domain_program_ctrl);
		}
		$use_p_ctrl = false;
		$tmp_arr = array();

		//if is set by human,get info
		if(isset($this->domain_program_ctrl[$did][$site]) || isset($this->domain_program_ctrl[$did]['global'])){
			if($is_debug){
				echo "is set domain_program_ctrl did\r\n";
			}
			$pid = isset($this->domain_program_ctrl[$did][$site]) ? intval($this->domain_program_ctrl[$did][$site]) : intval($this->domain_program_ctrl[$did]['global']);
			$sql = "SELECT a.IsFake, a.did DID, b.programid AS PID, b.AffId, b.CommissionType, b.CommissionUsed, b.CommissionIncentive, b.CommissionCurrency, b.DeniedPubCode, c.Domain, a.AffDefaultUrl, a.DeepUrlTpl, b.SupportDeepUrl, '' as OutGoingUrl , b.ShippingCountry
					FROM program_intell b inner join r_domain_program a on b.programid = a.pid inner join domain c on a.did = c.id WHERE b.isactive = 'active' AND a.did = ".intval($did)." AND b.programid = $pid limit 1";
			$tmp_arr = array();
			$tmp_arr = $this->objMysql->getRows($sql);
			//print_r($tmp_arr);
			if(current($tmp_arr)["PID"] == $pid && current($tmp_arr)["DID"] == $did){
				$use_p_ctrl = true;
				if($is_debug){
					echo "set use_p_ctrl true\r\n";
				}
			}
		}

		//temp for base_task,info from db
		/*if(!$use_p_ctrl && !$self && !empty($site)){
			if(!isset($this->base_rel) || !count($this->base_rel)){
				$this->base_rel = array();
				$db_ps_name = "base_program_store_relationship";
				if(in_array($site, array("au","us","de","uk","fr","ca"))){
					$db_ps_name .= "_".$site;
				}
				$sql = "SELECT a.ProgramId as PID, a.AffId, null as LimitAccount, b.domainname, b.merchantdomain, b.order, b.IsFake, b.AffiliateDefaultUrl, b.DeepUrlTemplate, b.programdomains, a.shippingcountry FROM program_intell a INNER JOIN $db_ps_name b ON a.programid = b.programid WHERE b.status = 'active' AND a.isactive = 'active' ";

				$tmp_arr = array();
				$tmp_arr = $this->objMysql->getRows($sql);
				foreach($tmp_arr as $k => $v){
					if(!empty($v["merchantdomain"])){
						$this->base_rel[$v["merchantdomain"]][$v["order"]][] = $v;
					}
					if(!empty($v["programdomains"]) && (empty($v["shippingcountry"]) || (!empty($v["shippingcountry"]) && stripos(','.$site.',', ','.$v["shippingcountry"].',') !== false))){						
						$t_p_arr = explode("\r\n", $v["programdomains"]);
						foreach($t_p_arr as $t_p_d){
							if(!empty($t_p_d) && $t_p_d != $v["merchantdomain"] && $t_p_d != $v["domainname"]){
								$v["order"] += 100;
								$this->base_rel[$t_p_d][$v["order"]][] = $v;
							}
						}
					}
					$this->base_rel[$v["domainname"]][$v["order"]][] = $v;
				}
				unset($tmp_arr);
					
				$sql = "SELECT StoreDomain, domain, site FROM base_ps_domain where StoreDomain <> domain";
				if(!empty($site)){
					$sql .= " and site = '$site'";
				}else{
					$sql .= " and site = ''";
				}
				$tmp_arr = array();
				$tmp_arr = $this->objMysql->getRows($sql, "domain");
				foreach($tmp_arr as $k => $v){
					if(isset($this->base_rel[$v["StoreDomain"]]) && !isset($this->base_rel[$k])){
						$this->base_rel[$k] = $this->base_rel[$v["StoreDomain"]];
					}
				}				
				unset($tmp_arr);
				if($site){
					$sql = "SELECT site, merchantdomain, coupondomain FROM `base_m_domain` where site = '$site'";
					$tmp_arr = array();
					$tmp_arr = $this->objMysql->getRows($sql);					
					foreach($tmp_arr as $v){
						if(isset($this->base_rel[$v["merchantdomain"]])){
							if(!isset($this->base_rel[$v["coupondomain"]])){
								$this->base_rel[$v["coupondomain"]] = $this->base_rel[$v["merchantdomain"]];
							}else{
								$only_sub = true;
								$find_sub_aff = current($this->base_rel[$v["coupondomain"]]);
								foreach($find_sub_aff as $v_find_sub){
									if(!in_array($v_find_sub["AffId"], $this->sub_aff)){
										$only_sub = false;
										break;
									}
								}
								if($only_sub){
									$this->base_rel[$v["coupondomain"]] = $this->base_rel[$v["merchantdomain"]];
								}						
							}
						}
					}
					unset($tmp_arr);
				}
				//if no aff check us
				if(in_array($site, array("au","de","uk","fr","ca"))){ //us
					$sql = "SELECT 'YES' as IsFake, a.ProgramId as PID, a.AffId, null as LimitAccount, b.domainname, b.merchantdomain, b.order, b.AffiliateDefaultUrl, b.DeepUrlTemplate, b.programdomains, a.shippingcountry FROM program_intell a INNER JOIN base_program_store_relationship_us b ON a.programid = b.programid WHERE b.status = 'active' AND a.isactive = 'active' AND a.supportFake <> 'no' AND a.affid not in (".implode(",", $this->sub_aff).")";
					$tmp_arr = array();
					$tmp_arr = $this->objMysql->getRows($sql);
					foreach($tmp_arr as $k => $v){
						if(in_array($site, $check_site)){
							$site_go = false;
							$tmp_shipp = explode(",", $v["shippingcountry"]);
							foreach($tmp_shipp as $v_ship){
								if(in_array($v_ship, $can_ship)){
									$site_go = true;
									break;
								}
							}
							if(!$site_go) continue;							
						}
						if(!isset($this->base_rel[$v["domainname"]]) || (!empty($v["merchantdomain"]) && !isset($this->base_rel[$v["merchantdomain"]]))){
							if(!empty($v["merchantdomain"]) && !isset($this->base_rel[$v["merchantdomain"]])){
								$this->base_rel[$v["merchantdomain"]][$v["order"]][] = $v;
							}elseif(!isset($this->base_rel[$v["domainname"]])){
								$this->base_rel[$v["domainname"]][$v["order"]][] = $v;			
							}
						}
						
					}
					unset($tmp_arr);
					$sql = "SELECT 'YES' as IsFake, a.ProgramId as PID, a.AffId, null as LimitAccount, b.domainname, b.merchantdomain, b.order, b.AffiliateDefaultUrl, b.DeepUrlTemplate, b.programdomains, a.shippingcountry FROM program_intell a INNER JOIN base_program_store_relationship b ON a.programid = b.programid WHERE b.status = 'active' AND a.isactive = 'active' ";
					$tmp_arr = array();
					$tmp_arr = $this->objMysql->getRows($sql);
					foreach($tmp_arr as $k => $v){
						if(empty($v["shippingcountry"])) continue;
						$tmp_shipp = explode(",", $v["shippingcountry"]);
						foreach($tmp_shipp as $v_ship){
							if(!in_array($v_ship, $can_ship)){
								unset($tmp_arr[$k]);
							}
						}							
					}
					foreach($tmp_arr as $k => $v){	
						if(!isset($this->base_rel[$v["domainname"]]) || (!empty($v["merchantdomain"]) && !isset($this->base_rel[$v["merchantdomain"]]))){
							if(!empty($v["merchantdomain"]) && !isset($this->base_rel[$v["merchantdomain"]])){
								$this->base_rel[$v["merchantdomain"]][$v["order"]][] = $v;
							}elseif(!isset($this->base_rel[$v["domainname"]])){
								$this->base_rel[$v["domainname"]][$v["order"]][] = $v;			
							}
						}
						
					}
					unset($tmp_arr);
				}
				//echo count($this->base_rel);
			}
			if($is_debug){
				//echo "checkDomainProgramRel_Sp:getDefaultOutgoingByStrategy:base_rel\n";
				//print_r($this->base_rel);
			}
			$base_domain = $domain_info['Domain'];
			
			if(!isset($this->base_rel[$base_domain]) && isset($domain_info['CountryCode'])){				
				//print_r($domain_info);
				if(!in_array($domain_info['CountryCode'], $can_ship)){
					$tmp_domain_new = str_ireplace("/{$domain_info['CountryCode']}", "" , $base_domain);
					if(!empty($tmp_domain_new) && $tmp_domain_new != $base_domain){
						$base_domain = $tmp_domain_new;
					}
				}
				
				if(!isset($this->base_rel[$base_domain])){
					$base_domain = substr($base_domain, 0, strpos($base_domain, "/"));
				}
			}
		
			if(isset($this->base_rel) && isset($this->base_rel[$base_domain])){
				$tmp_arr = $this->base_rel[$base_domain];
				ksort($tmp_arr);
				if($is_debug){
					//print_r($tmp_arr);
				}
				
				if(count($tmp_arr)){
					$tmp_arr_bak = current($tmp_arr);		
					$tmp_d_countrycode = $domain_info["CountryCode"];
					if(!empty($site)){
						$can_ship_2 = array($site => $site);
						if($site == 'de'){
							$can_ship_2 = array('de' => 'de', 'ch' => 'ch', 'at' => 'at', 'dk' => 'dk');
						}elseif($site == 'uk'){
							$can_ship_2 = array('uk' => 'uk', 'gb' => 'gb', 'ie' => 'ie');							
						}elseif($site == 'au'){
							$can_ship_2 = array('au' => 'au', 'nz' => 'nz', 'us' => 'us', 'ca' => 'ca');
						}elseif($site == 'us'){
							$can_ship_2 = array('us' => 'us', 'ca' => 'ca', 'uk' => 'uk');
						}elseif($site == 'ca'){
							$can_ship_2 = array('us' => 'us', 'ca' => 'ca');
						}elseif($site == 'fr'){
							$can_ship_2 = array('fr' => 'fr');
						}elseif($site == 'ch'){
							$can_ship_2 = array('de' => 'de', 'ch' => 'ch');
						}
						if(!empty($tmp_d_countrycode)){
							$tmp_shipp = explode(",", $tmp_d_countrycode);
							foreach($tmp_shipp as $v_ship){
								$can_ship_2[$v_ship] = $v_ship;
							}
						}
						foreach($tmp_arr as $k_order => $v_order){			
							foreach($v_order as $k => $v){
								if(!empty($v["shippingcountry"])){
									$tmp_shipp = explode(",", $v["shippingcountry"]);
									$is_ship = false;
									foreach($tmp_shipp as $v_ship){
										if(in_array($v_ship, $can_ship_2)){
											$is_ship = true;
											break;											
										}
									}
									if(!$is_ship){
										unset($v_order[$k]);
									}
								}
								if($v["IsFake"] == "YES" || in_array($v["AffId"], $this->sub_aff)){
									unset($v_order[$k]);
								}
							}
							unset($tmp_arr[$k_order]);
							if(count($v_order)){
								$tmp_arr = $v_order;
								break;
							}
						}
						
						if(!count($tmp_arr)) $tmp_arr = $tmp_arr_bak;
					}else{
						$tmp_arr = current($tmp_arr);
					}
					
					if(!empty($tmp_d_countrycode)){						
						foreach($tmp_arr as $k => $v){
							if(isset($this->aff_marketing[$v["AffId"]])){
								if(!empty($this->aff_marketing[$v["AffId"]]["MarketingContinent"]) && $this->aff_marketing[$v["AffId"]]["MarketingContinent"] != "global"){
									if(empty($this->aff_marketing[$v["AffId"]]["MarketingCountry"])){
										if(isset($this->country_rel[$tmp_d_countrycode])){
											if($this->country_rel[$tmp_d_countrycode] != $this->aff_marketing[$v["AffId"]]["MarketingContinent"]){
												unset($tmp_arr[$k]);
												//echo "Continent\r\n";
											}
										}
									}else{
										if($tmp_d_countrycode != $this->aff_marketing[$v["AffId"]]["MarketingCountry"]){
											unset($tmp_arr[$k]);
											//echo "Country\r\n";
										}
									}
								}
							}
						}
						//print_r($tmp_arr);
						if(!count($tmp_arr)) $tmp_arr = $tmp_arr_bak;
					}
				}else{
					$tmp_arr = current($tmp_arr);					
				}
				
				if(count($tmp_arr)){
					//print_r($tmp_arr);
					//$tmp_arr = array($tmp_arr);
					$has_no_fake = false;
					$has_main_aff = false;
					$has_fake_fake = true;
					foreach($tmp_arr as $k => $v){
						if($v['IsFake'] == 'NO'){
							$has_no_fake = true;							
						}
						if($v['order'] < 100){
							$has_fake_fake = false;
						}
						if(!in_array($v['AffId'], $this->sub_aff)){
							$has_main_aff = true;
							break;
						}
					}
					
					if($has_no_fake){
						foreach($tmp_arr as $k => $v){
							if($v['IsFake'] == 'YES'){
								unset($tmp_arr[$k]);
							}
						}
					}
					
					if($has_main_aff){
						foreach($tmp_arr as $k => $v){
							if(in_array($v['AffId'], $this->sub_aff)){
								unset($tmp_arr[$k]);
							}
						}
					}

					if((!$has_no_fake) || (!$has_main_aff && $has_no_fake) ){
						//if only has fake, then unset;						
						$has_fake_fake = true;
					}
					

					if($has_fake_fake){
						$old_ps_arr = $tmp_arr;
						unset($tmp_arr);
					}else{
						krsort($tmp_arr);
						reset($tmp_arr);										
						$default_prgm = $this->formatDefaultOutgoing($domain_info, $tmp_arr);
					}				
				}				
			}
		}*/
		
		if($is_debug)echo "\r\n_____________________\r\n";
		if(!count($default_prgm)){
			if(!$use_p_ctrl){
				$pure_prgm = $sec_prgm = array(); 
				if(!empty($domain_info["SubDomain"])){
					$sql = "SELECT a.IsFake, b.supportFake, a.DID, a.PID, b.AffId, b.CommissionType, b.CommissionUsed, b.CommissionIncentive, b.CommissionCurrency, b.DeniedPubCode, c.Domain, a.AffDefaultUrl, a.DeepUrlTpl, b.SupportDeepUrl, b.OutGoingUrl, c.countrycode as d_countrycode, b.countrycode as p_countrycode, b.ShippingCountry, d.revenueorder
							FROM r_domain_program a INNER JOIN program_intell b ON a.pid = b.programid INNER JOIN domain c ON a.DID = c.id INNER JOIN program_int d on a.pid = d.programid
							WHERE b.isactive = 'active' AND a.status = 'active' AND b.AffId NOT IN (". implode(",", $this->sub_aff). ") AND a.DID = ".intval($did);	
					if($site){
						$sql .= " AND (b.shippingcountry = '' OR b.shippingcountry LIKE '%$site%') ";				
					}				
					$pure_prgm = $this->objMysql->getRows($sql);
					
					
					$sql = "SELECT a.IsFake, b.supportFake, a.DID, a.PID, b.AffId, b.CommissionType, b.CommissionUsed, b.CommissionIncentive, b.CommissionCurrency, b.DeniedPubCode, c.Domain, a.AffDefaultUrl, a.DeepUrlTpl, b.SupportDeepUrl, b.OutGoingUrl, c.countrycode as d_countrycode, b.countrycode as p_countrycode, b.ShippingCountry, d.revenueorder
							FROM r_domain_program a INNER JOIN program_intell b ON a.pid = b.programid INNER JOIN domain c ON a.DID = c.id INNER JOIN program_int d on a.pid = d.programid
							WHERE b.isactive = 'active' AND a.status = 'active' AND c.supportFake = 'YES' AND a.DID = ".intval($did);	//AND c.countrycode = b.countrycode
					$sql .= " union 
							SELECT 'YES' as IsFake, b.supportFake, a.DID, a.PID, b.AffId, b.CommissionType, b.CommissionUsed, b.CommissionIncentive, b.CommissionCurrency, b.DeniedPubCode, c.Domain, a.AffDefaultUrl, a.DeepUrlTpl, b.SupportDeepUrl, b.OutGoingUrl, c.countrycode as d_countrycode, b.countrycode as p_countrycode, b.ShippingCountry, d.revenueorder
							FROM r_domain_program a INNER JOIN program_intell b ON a.pid = b.programid INNER JOIN domain c ON a.DID = c.id INNER JOIN program_int d on a.pid = d.programid
							WHERE b.isactive = 'active' AND a.status = 'active' 
							AND b.supportdeepurlout <> 'no' AND c.supportFake = 'YES'
							AND c.domain = '".addslashes(str_ireplace($domain_info["SubDomain"].".", "", $domain_info["Domain"]))."'";
					if($site == 'fr'){
						$sql .= " AND b.AffId <> 191 ";
					}
					$sec_prgm = $this->objMysql->getRows($sql);
					
				}else{
					$sql = "SELECT a.IsFake, b.supportFake, a.DID, a.PID, b.AffId, b.CommissionType, b.CommissionUsed, b.CommissionIncentive, b.CommissionCurrency, b.DeniedPubCode, c.Domain, a.AffDefaultUrl, a.DeepUrlTpl, b.SupportDeepUrl, b.OutGoingUrl, c.countrycode as d_countrycode, b.countrycode as p_countrycode, b.ShippingCountry, d.revenueorder
							FROM r_domain_program a INNER JOIN program_intell b ON a.pid = b.programid INNER JOIN domain c ON a.DID = c.id INNER JOIN program_int d on a.pid = d.programid
							WHERE b.isactive = 'active' AND a.status = 'active' AND c.supportFake = 'YES' AND a.DID = ".intval($did);	//AND c.countrycode = b.countrycode
					$pure_prgm = $this->objMysql->getRows($sql);
				}
				foreach(array($pure_prgm, $sec_prgm) as $tmp_arr){
					foreach($tmp_arr as $kk => $vv){					
						$tmp_arr[$kk]["OutGoingUrl"] = empty($vv["DeepUrlTpl"]) ? $vv["AffDefaultUrl"] : $vv["DeepUrlTpl"];
					}
					

					if($domain_info["CountryCode"] == "gb") $domain_info["CountryCode"] = "uk";
					
					$need_no_nofake = false;
					
					if((in_array($site, $check_site) && count($can_ship)) || $site == "us"){
						$tmp_not_fake = array();
						$has_main_aff = false;
						$has_no_fake = false;
						if($site == "us"){
							$tmp_us_can_ship = $can_ship;
							$can_ship = array("us", "ca"); 
						}
						foreach($tmp_arr as $k => $v){

							if($v["ShippingCountry"]){
								$tmp_arr[$k]["IsFake"] = "YES";
								$tmp_shipp = explode(",", $v["ShippingCountry"]);
								foreach($tmp_shipp as $v_ship){
									if(in_array($v_ship, $can_ship)){
										$tmp_not_fake[$k] = $v;
										$tmp_arr[$k]["IsFake"] = "NO";
										break;									
									}
								}
								
								if($tmp_arr[$k]["IsFake"] == 'YES' && $v['supportFake'] == 'No'){
									unset($tmp_arr[$k]);
								}
							}else{
								$tmp_not_fake[$k] = $v;
							}						
						}
						
						if(count($tmp_not_fake)){
							$tmp_arr = $tmp_not_fake;
						}
						if($site == "us"){
							$can_ship = $tmp_us_can_ship;						
						}					
					}else{					
						$country_same = $country_empty = array();
						
						if($domain_info["CountryCode"] == $site || empty($domain_info["CountryCode"])){
							$need_no_nofake = true;
						}
						if($site == 'ca' || $site == 'ch'){
							foreach($tmp_arr as $k => $v){
								if(in_array($v["AffId"], $this->sub_aff)) continue;
								if(stripos(','.$v["ShippingCountry"].',', ','.$site.',') !== false || empty($v['ShippingCountry'])){
									$country_same[$k] = $v;
								}elseif(($site == 'ca' && stripos(','.$v["ShippingCountry"].',', ',us,') !== false) || ($site == 'ch' && stripos(','.$v["ShippingCountry"].',', ',de,') !== false)){
									$country_empty[$k] = $v;
								}else{
									$tmp_arr[$k]["IsFake"] = "YES";
								}
								
								if($tmp_arr[$k]["IsFake"] == 'YES' && $v['supportFake'] == 'No'){
									unset($tmp_arr[$k]);
								}
							}
							if(count($country_same)){						
								$tmp_arr = $country_same;
							}elseif(count($country_empty)){
								$tmp_arr = $country_empty;
							}
							
						}else{
							foreach($tmp_arr as $k => $v){
								if(!$need_no_nofake && in_array($v["AffId"], $this->sub_aff)) continue;
								if($v["IsFake"] == "YES") continue;				
								if(stripos(','.$v["ShippingCountry"].',', ','.$site.',') !== false || empty($v['ShippingCountry'])){
									$country_same[$k] = $v;
								}elseif(in_array($site, $can_ship)){
									$country_empty[$k] = $v;
									$tmp_arr[$k]["IsFake"] = "YES";
								}else{
									$tmp_arr[$k]["IsFake"] = "YES";
								}
								if($tmp_arr[$k]["IsFake"] == 'YES' && $v['supportFake'] == 'No'){
									unset($tmp_arr[$k]);
								}
							}
							if(count($country_same)){						
								$tmp_arr = $country_same;
							}
						}					
					}
					
					if(count($tmp_arr)) break;
				}
				if($is_debug){
					if($is_debug)echo "\r\n_____++________\r\n";
				}
				
				if($self){
					$tmp_d_countrycode = $domain_info["CountryCode"];
					if(!empty($tmp_d_countrycode)){
						$tmp_arr_bak = $tmp_arr;
						foreach($tmp_arr as $k => $v){
							if(isset($this->aff_marketing[$v["AffId"]])){
								if(!empty($this->aff_marketing[$v["AffId"]]["MarketingContinent"]) && $this->aff_marketing[$v["AffId"]]["MarketingContinent"] != "global"){
									if(empty($this->aff_marketing[$v["AffId"]]["MarketingCountry"])){
										if(isset($this->country_rel[$tmp_d_countrycode])){
											if($this->country_rel[$tmp_d_countrycode] != $this->aff_marketing[$v["AffId"]]["MarketingContinent"]){
												unset($tmp_arr[$k]);
												echo "Continent\r\n";
											}
										}
									}else{
										if($tmp_d_countrycode != $this->aff_marketing[$v["AffId"]]["MarketingCountry"]){
											unset($tmp_arr[$k]);
											echo "Country\r\n";
										}
									}
								}
							}			
						}
						$has_mainaff = false;
						foreach($tmp_arr as $v){
							if(!in_array($v["AffId"], $this->sub_aff)){
								$has_mainaff = true;
								break;
							}
						}
						if(!count($tmp_arr) || !$has_mainaff) $tmp_arr = $tmp_arr_bak;					
					}
					if($is_debug){
						echo "countrycode:".$tmp_d_countrycode."\r\n";
					}
				}
			}
			
			$prgm_order = array();
			//TODO entrance
			$prgm_order = $this->orderProgram($tmp_arr, $domain_info["CountryCode"], $site, $domain_info);
			unset($tmp_arr);
			if(isset($prgm_order["main"]) && count($prgm_order['All']["main"]) || $use_p_ctrl){
				$default_prgm = $this->formatDefaultOutgoing($domain_info, $prgm_order['All']["main"], $prgm_order['All']["sub"]);
			}elseif(count($old_ps_arr)){
				$default_prgm = $this->formatDefaultOutgoing($domain_info, $old_ps_arr, $prgm_order['All']["sub"]);
			}elseif(($site == "de" || $site == "ca" || $site == "ch") && count($prgm_order['All']["sub"]) && $need_no_nofake){
				$default_prgm = $this->formatDefaultOutgoing($domain_info, array(), $prgm_order['All']["sub"]);
			}else{
				// IsFake
				$sql = "SELECT a.ID, a.Domain, c.affid, a.Existed, a.SubDomain, a.DomainName, a.CountryCode, c.CountryCode as p_country, c.programid, c.SupportDeepUrl, c.shippingcountry, 'YES' as IsFake
						FROM domain a INNER JOIN r_domain_program b ON a.id  = b.did INNER JOIN program_intell c ON b.pid  = c.programid  
						WHERE a.DomainName = '".addslashes($domain_info["DomainName"])."' AND a.Existed = 'YES' AND c.isactive = 'active' 
						and c.affid NOT IN (".implode(",", $this->sub_aff).") and b.status = 'active' and a.Existed = 'yes' and a.SupportFake = 'YES' and c.supportFake <> 'No'";
				if(!empty($domain_info["CountryCode"])){
					$sql .= " AND c.NotAllowCountry <> '".addslashes($domain_info["CountryCode"])."' ";
				}
				$sql .= " and c.SupportDeepUrlOut <> 'no'";
				$sql .= " and (c.DeepUrlTpl <> '' or (c.DeepUrlTpl = '' and a.SubDomain = '' and a.CountryCode = '".addslashes($domain_info["CountryCode"])."'))";
				if($domain_info["DomainName"] == "google"){
					$sql .= " AND a.Domain =  '".addslashes($domain_info["Domain"])."' ";
				}
				
				if($is_debug) echo "\r\n".$sql."\r\n";
				//}
				$domain_arr = array();
				$domain_arr = $this->objMysql->getRows($sql);
				$domain_country = $domain_subdomain = $domain_default = $domain_fake = $domain_fake_default = $domain_fake_gb = array();
				foreach($domain_arr as $k => $v){
					if($v["p_country"] == "gb"){
						$v["p_country"] = "uk";
					}
					
					if(in_array($site, $check_site) && count($can_ship) && !empty($v["shippingcountry"]) && $v["affid"] <> 7){
						$site_go = false;
						$tmp_shipp = array();
						$tmp_shipp = explode(",", $v["shippingcountry"]);
						foreach($tmp_shipp as $v_ship){
							if(in_array($v_ship, $can_ship)){
								$site_go = true;
								break;
							}
						}
						if(!$site_go) continue;
					}
					if($is_debug)
						//print_r($v);
					if(!in_array($v["affid"], array(133,415,429,35,469,27,5))){//td
						if($domain_info["CountryCode"] == $v["p_country"]){
							if($v["CountryCode"] == $domain_info["CountryCode"]){
								$domain_country[$v["programid"]] = $v["programid"];
							}
							if($v["SubDomain"] == $domain_info["SubDomain"]){
								$domain_subdomain[$v["programid"]] = $v["programid"];
							}
							if(empty($v["CountryCode"]) && empty($v["SubDomain"])){
								$domain_default[$v["programid"]] = $v["programid"];
							}
						//}else{
						}elseif($v["SupportDeepUrl"] != 'No'){
							if(empty($domain_info["CountryCode"]) && empty($v["shippingcountry"])){
								$domain_fake_gb[$v["programid"]] = $v["programid"];
							}
						}
					}
				}
				
				if($is_debug){
					echo "\r\n################################\r\n";
				}
				
				$sec_pid_arr = array();
				$sec_did = 0;
				if(count($domain_country)){					
					$sec_pid_arr = array_keys($domain_country);
				}elseif(count($domain_default)){					
					$sec_pid_arr = array_keys($domain_default);
				}elseif(count($domain_subdomain)){					
					$sec_pid_arr = array_keys($domain_subdomain);
				}elseif($domain_fake_gb){
					$sec_pid_arr = array_keys($domain_fake_gb);			
				}
				
				if($is_debug){
					echo "sec\r\n";
					print_r($sec_pid_arr);
				}
				
				$sec_prgm_order = array();
				if(count($sec_pid_arr)){
					$tmp_arr = array();
					$sql = "SELECT 'YES' as IsFake , a.DID, a.PID, b.AffId, b.CommissionType, b.CommissionUsed, b.CommissionIncentive, b.CommissionCurrency, b.DeniedPubCode, c.Domain, a.AffDefaultUrl, a.DeepUrlTpl, b.SupportDeepUrl, b.OutGoingUrl, c.countrycode as d_countrycode, b.countrycode as p_countrycode, b.ShippingCountry, d.revenueorder
							FROM r_domain_program a INNER JOIN program_intell b ON a.pid = b.programid INNER JOIN domain c ON a.DID = c.id INNER JOIN program_int d on a.pid = d.programid
							WHERE b.isactive = 'active' AND a.status = 'active' and c.SupportFake = 'YES' and b.supportFake <> 'No' AND a.PID in (".implode(",", $sec_pid_arr).")";
					$tmp_arr = $this->objMysql->getRows($sql);
					foreach($tmp_arr as $kk => $vv){					
						$tmp_arr[$kk]["OutGoingUrl"] = empty($vv["DeepUrlTpl"]) ? $vv["AffDefaultUrl"] : $vv["DeepUrlTpl"];
					}
					$sec_prgm_order = array();
					$sec_prgm_order = $this->orderProgram($tmp_arr, $domain_info["CountryCode"], $site, $domain_info);
					unset($tmp_arr);
				}
				if($is_debug){
					echo "\nsec_prgm_order\n";
					var_dump($sec_prgm_order);
				}
				if(isset($sec_prgm_order['All']["main"]) && count($sec_prgm_order['All']["main"])){
					$default_prgm = $this->formatDefaultOutgoing($domain_info, $sec_prgm_order['All']["main"], count($prgm_order['All']["sub"]) ? $prgm_order['All']["sub"] : $sec_prgm_order["sub"]);
				}elseif(count($prgm_order['All']["sub"])){
					$default_prgm = $this->formatDefaultOutgoing($domain_info, $prgm_order['All']["sub"]);
				}
			}		
			unset($prgm_order);		
		}

		return $default_prgm;
	}
	
	function findLimitAccountOutgoing($account_arr, $prgm, $tmp_domain, $did){
		$data = array();
		foreach($account_arr as $acc){
			$data[$acc] = array();
		}
		
		$i = 0;
		ksort($prgm);
		reset($prgm);
		//print_r($prgm);
		while(count($tmp_arr = array_pop($prgm)))
		{
			if(!count($tmp_arr["LimitAccount"])){
				foreach($data as $acc => $tmp_v){
					if(!count($data[$acc])){						
						$data[$tmp_domain."|".$acc] = $tmp_arr;
						$data[$tmp_domain."|".$acc]["Key"] = $tmp_domain."|".$acc;						
						$data[$tmp_domain."|".$acc]["LimitAccount"] = "";
						$data[$tmp_domain."|".$acc]["DID"] = $did;
						unset($data[$acc]);
					}
				}
				break;
				
			}else{
				foreach($data as $acc => $tmp_v){
				//foreach($tmp_arr["LimitAccount"] as $acc){
					if(!count($data[$acc]) && !in_array($acc, $tmp_arr["LimitAccount"])){
						$data[$tmp_domain."|".$acc] = $tmp_arr;
						$data[$tmp_domain."|".$acc]["Key"] = $tmp_domain."|".$acc;
						$data[$tmp_domain."|".$acc]["LimitAccount"] = implode(",", $tmp_arr["LimitAccount"]);
						$data[$tmp_domain."|".$acc]["DID"] = $did;
						unset($data[$acc]);
					}
				}
			}
			
			$i++;
			if($i > 5) break;
		}
		
		foreach($data as $acc => $tmp_v){
			if(!count($tmp_v)){
				unset($data[$acc]);
			}
		}
		
		return $data;
	}
	
	
	/*
	 * update domain_outgoing_default & domain_outgoing_default_changelog
	 * array $data("old" => array(), "new" => array())
	 * no return	
	 */
	function updateDefaultDomainOutgoing($data){
		$change_log = array();		
		if(isset($data["new"])){			
			if(!isset($data["new"]["PID"]) || !isset($data["new"]["DID"])){
				print_r($data);
				return 2;
			}else{			
				$this->setDefaultDomainOutgoing($data["new"]);
				$change_log = $data["new"];			
				$change_log["New_PID"] = $change_log["PID"];
				$change_log["Old_PID"] = isset($data["old"]["PID"]) ? $data["old"]["PID"] : 0;
			}
			
		}elseif(isset($data["old"]) && !isset($data["new"])){			
			$this->removeDefaultDomainOutgoing($data["old"]);
			$change_log = $data["old"];
			$change_log["Old_PID"] = $change_log["PID"];
			$change_log["New_PID"] = 0;
		}
		
		if(count($change_log)) {
			return $this->setDefaultDomainOutgoingChangelog($change_log);
		}
	}
	
	
	function getAcitiveBlockRel(){
		if(!isset($this->block_rel) || !is_array($this->block_rel)){
			$this->block_rel = array();
			$data = array();
			//get block relationship
			$tmp_arr = $this->getBlockRelationship(array("status" => "active"));
			foreach($tmp_arr as $v){
				if($v["objtype"] == "Affiliate"){
					$data["aff"][$v["objid"]][$v["accountid"]] = $v["accountid"];
				}elseif($v["objtype"] == "Program"){
					$data["program"][$v["objid"]][$v["accountid"]] = $v["accountid"];
				}
			}
			$this->block_rel = $data;
		}
	}
	
	function checkDomainProgramRel($domain_arr){
		$cnt = 0;
		if(count($domain_arr)){
			$this->getAcitiveBlockRel();			
			
			foreach($domain_arr as $did => $v){
				$tmp_arr = array();
				$tmp_arr = $this->checkDefaultOutgoingChanged($did);
				
				foreach($tmp_arr as $ps_change){
					//print_r($tmp_arr);exit;
					$return_val = $this->updateDefaultDomainOutgoing($ps_change);
					if($return_val === 1){
						//$this->updateDefaultDomainOutgoing($tmp_arr["data"]);
						$cnt++;
					}elseif($return_val == 2){
						echo "did:$did\r\n";
					}
				}				
			}
		}
		return $cnt;
	}
	
	function checkDomainProgramRel_Sp($domain_arr, $site){
		$cnt = 0;

		if(count($domain_arr)){
			//get bad relationship
			$this->getAcitiveBlockRel();
			foreach($domain_arr as $did => $v){
				//get default outgoing by strategy
				$new_rel = $this->getDefaultOutgoingByStrategy($did, $site);

				foreach($new_rel as $new_data){
					$this->setDefaultDomainOutgoing($new_data, $site);
				}				
			}
		}
		return $cnt;
	}
	
	function setDomainTMPolicy($domain_arr, $site){		
		if(count($domain_arr)){			
			foreach($domain_arr as $did => $v){
				$this->setDomainProgramTM($did, $site);							
			}
		}		
	}
	
	function setDomainProgramTM($did, $site){
		$tm_arr = array('TMPolicy' => 'UNKNOWN', 'TMTermsPolicy' => 'UNKNOWN');
		$sql = "SELECT GROUP_CONCAT(TMPolicy) as TMPolicy_str, GROUP_CONCAT(TMTermsPolicy) as TMTermsPolicy_str, GROUP_CONCAT(TMCountries) as TMCountries_str FROM program_int i INNER JOIN program_intell p on i.programid = p.programid inner join r_domain_program r on p.programid = r.pid
				where ((r.did = $did and r.status = 'active' and p.isactive = 'active' and (p.shippingcountry LIKE '%$site%' OR p.shippingcountry = ''))";
		$sql .= " OR p.programid IN (SELECT pid FROM domain_outgoing_default_other WHERE did = $did AND site = '$site')) AND p.affid NOT IN (".implode(",", $this->sub_aff).")";
		$tmp_arr = array();
		$tmp_arr = $this->objMysql->getFirstRow($sql);
		if(stripos($tmp_arr['TMPolicy_str'], 'DISALLOWED') !== false){
			$tm_arr['TMPolicy'] = 'DISALLOWED';
		}elseif(stripos($tmp_arr['TMPolicy_str'], 'UNKNOWN') !== false){
			$tm_arr['TMPolicy'] = 'UNKNOWN';
		}elseif(stripos($tmp_arr['TMPolicy_str'], 'ALLOWED') !== false && (stripos($tmp_arr['TMCountries_str'], $site) !== false || stripos($tmp_arr['TMCountries_str'], 'GLOBAL') !== false)){
			$tm_arr['TMPolicy'] = 'ALLOWED';
		}
		
		if(stripos($tmp_arr['TMTermsPolicy_str'], 'DISALLOWED') !== false){
			$tm_arr['TMTermsPolicy'] = 'DISALLOWED';				
		}elseif(stripos($tmp_arr['TMTermsPolicy_str'], 'UNKNOWN') !== false){
			$tm_arr['TMTermsPolicy'] = 'UNKNOWN';				
		}elseif(stripos($tmp_arr['TMTermsPolicy_str'], 'ALLOWED') !== false && (stripos($tmp_arr['TMCountries_str'], $site) !== false || stripos($tmp_arr['TMCountries_str'], 'GLOBAL') !== false)){				
			$tm_arr['TMTermsPolicy'] = 'ALLOWED';
		}
		
		//$sql = "update domain_outgoing_default_site set TMPolicy = '{$tm_arr['TMPolicy']}', TMTermsPolicy = '{$tm_arr['TMTermsPolicy']}' where DID = {$did} and Site = '$site'";		
		$sql = "update domain_outgoing_default_other set TMPolicy = '{$tm_arr['TMPolicy']}', TMTermsPolicy = '{$tm_arr['TMTermsPolicy']}' where DID = {$did} and Site = '$site'";
		$this->objMysql->query($sql);
	}
	
	function checkDomainProgramRelCountry($domain_arr){
		$cnt = 0;
		if(count($domain_arr)){
			foreach($domain_arr as $did => $v){
				$domain_p_country = $this->getDomainRelProgramCountry($did);
				$new_site_rel = $this->getDefaultOutgoingByStrategyPure($did, $domain_p_country);
				foreach($new_site_rel as $site => $new_rel_type){
					foreach ($new_rel_type as $publisherType=>$new_rel)
					{
						foreach($new_rel as $data){
							if(count($data)){
								if(!isset($data["IsFake"])) $data["IsFake"] = 'NO';
								if(!isset($data["AffiliateDefaultUrl"])) $data["AffiliateDefaultUrl"] = $data["AffDefaultUrl"];
								if(!isset($data["DeepUrlTemplate"])) $data["DeepUrlTemplate"] = $data["DeepUrlTpl"];
								if ($publisherType == 'All')// for promotion type publishers [only have support promotion site programs]
								{
									$sql = "insert into domain_outgoing_default_other(site, DID, PID, `Key`, LimitAccount, IsFake, AddTime, LastUpdateTime, AffiliateDefaultUrl, DeepUrlTemplate) values('$site','".intval($data["DID"])."', '".intval($data["PID"])."', '".addslashes($data["Key"])."', '".addslashes($data["LimitAccount"])."', '".addslashes($data["IsFake"])."', '".date("Y-m-d H:i:s")."', '".date("Y-m-d H:i:s")."', '".addslashes($data["AffiliateDefaultUrl"])."', '".addslashes($data["DeepUrlTemplate"])."') ON DUPLICATE KEY UPDATE PID = '".intval($data["PID"])."', LimitAccount = '".addslashes($data["LimitAccount"])."', IsFake = '".addslashes($data["IsFake"])."', LastUpdateTime = '".date("Y-m-d H:i:s")."', AffiliateDefaultUrl = '".addslashes($data["AffiliateDefaultUrl"])."', DeepUrlTemplate = '".addslashes($data["DeepUrlTemplate"])."'";
									$this->objMysql->query($sql);
								}
								else if ($publisherType == 'Content')// for content type publishers [have all type programs]
								{
									$sql = "insert into redirect_default(site, DID, PID, `Key`, LimitAccount, IsFake, AddTime, LastUpdateTime, AffiliateDefaultUrl, DeepUrlTemplate,SupportType) values('$site','".intval($data["DID"])."', '".intval($data["PID"])."', '".addslashes($data["Key"])."', '".addslashes($data["LimitAccount"])."', '".addslashes($data["IsFake"])."', '".date("Y-m-d H:i:s")."', '".date("Y-m-d H:i:s")."', '".addslashes($data["AffiliateDefaultUrl"])."', '".addslashes($data["DeepUrlTemplate"])."', '".addslashes($publisherType)."') ON DUPLICATE KEY UPDATE PID = '".intval($data["PID"])."', LimitAccount = '".addslashes($data["LimitAccount"])."', IsFake = '".addslashes($data["IsFake"])."', LastUpdateTime = '".date("Y-m-d H:i:s")."', AffiliateDefaultUrl = '".addslashes($data["AffiliateDefaultUrl"])."', DeepUrlTemplate = '".addslashes($data["DeepUrlTemplate"])."', SupportType = '".addslashes($publisherType)."'";
									$this->objMysql->query($sql);
								}								
								$cnt++;
							}
						}
					}
				}
			}
		}
		return $cnt;
	}
	
	function getDomainRelProgramCountry($did){
		$sql = "SELECT b.shippingcountry FROM r_domain_program a INNER JOIN program_intell b ON a.pid = b.programid 
				WHERE b.shippingcountry <> '' AND b.isactive = 'active' AND a.status = 'active' AND a.did = $did";
		$tmp_arr = $this->objMysql->getRows($sql);
		$return_site = array('global' => 'global');
		$shipping_country = array();		
		if(SID == 'bdg01'){
			foreach(array_keys($this->country_rel) as $v){			
				if(SID == 'bdg01'){
					if(!in_array($v, array("us", "uk", "au", "ca", "de", "fr"))){
						$shipping_country[$v] = $v;
					}
				}else{
					$shipping_country[$v] = $v;
				}
			}
		}else{
			$shipping_global = explode(",", strtolower($this->global_c));
			foreach($shipping_global as $v){			
				if(strlen($v)){
					$shipping_country[$v] = $v;
				}
			}
		}
			
		foreach($tmp_arr as $k => $v){
			$tmp_cc = explode(",", $v["shippingcountry"]);
			foreach($tmp_cc as $c_v){
				if(in_array($c_v, $shipping_country)){
					$return_site[$c_v] = $c_v;
				}elseif(SID == 'bdg01' && in_array($c_v, array('de'))){
					$return_site['ch'] = 'ch';
				}
			}
		}
		return $return_site;
	}

	function getDefaultOutgoingByStrategyPure($did, $site_arr = array()){
		$default_prgm = array();
		global $is_debug, $self;
		$domain_info = $this->getDomainInfoById($did);
		if($is_debug){
			print_r($domain_info);
		}
		if(!count($site_arr) || $domain_info["SupportAff"] == "NO")
		{
			return $default_prgm;
		}
		$type_sql = ' ';
		if(SID == 'bdg02')
			$type_sql = ",b.SupportType";
		$sql = "SELECT a.IsFake, a.DID, a.PID, b.AffId, b.CommissionType, b.CommissionUsed, b.CommissionIncentive, b.CommissionCurrency, b.DeniedPubCode, c.Domain, a.AffDefaultUrl, a.DeepUrlTpl, b.SupportDeepUrl, b.OutGoingUrl, c.countrycode as d_countrycode, b.countrycode as p_countrycode, b.ShippingCountry$type_sql FROM r_domain_program a INNER JOIN program_intell b ON a.pid = b.programid INNER JOIN domain c ON a.DID = c.id WHERE b.isactive = 'active' AND a.status = 'active' AND a.DID = ".intval($did);
		$tmp_arr = $this->objMysql->getRows($sql);
		if(empty($tmp_arr) && !empty($domain_info["SubDomain"]))
		{
			$sql = "SELECT a.IsFake, a.DID, a.PID, b.AffId, b.CommissionType, b.CommissionUsed, b.CommissionIncentive, b.CommissionCurrency, b.DeniedPubCode, c.Domain, a.AffDefaultUrl, a.DeepUrlTpl, b.SupportDeepUrl, b.OutGoingUrl, c.countrycode as d_countrycode, b.countrycode as p_countrycode, b.ShippingCountry$type_sql FROM r_domain_program a INNER JOIN program_intell b ON a.pid = b.programid INNER JOIN domain c ON a.DID = c.id WHERE b.affid != 115 and b.isactive = 'active' AND a.status = 'active' AND c.domain = '".addslashes(str_ireplace($domain_info["SubDomain"].".", "", $domain_info["Domain"]))."'";
			$sql .= " and b.affid <> 115";
			$tmp_arr = $this->objMysql->getRows($sql);
		}
		
		foreach($tmp_arr as $kk => $vv){
			//if(in_array($vv["AffId"], $this->sub_aff)){
			if(in_array($vv["AffId"], array(191))){			
				unset($tmp_arr[$kk]);
			}else{
				$tmp_arr[$kk]["OutGoingUrl"] = empty($vv["DeepUrlTpl"]) ? $vv["AffDefaultUrl"] : $vv["DeepUrlTpl"];
			}
		}
		
		//print_r($tmp_arr);
		if($is_debug){
			echo $sql;
			print_r($tmp_arr);
			print_r($site_arr);
		}
		foreach($site_arr as $site){
			$prgm_site = array();
			foreach($tmp_arr as $k => $v){
				if($site == "global"){
					if(empty($v['ShippingCountry'])){
						$prgm_site[$v["PID"]] = $v;
					}
				}else{
					if(stripos(','.$v["ShippingCountry"].',', ','.$site.',') !== false || empty($v['ShippingCountry'])){
						$prgm_site[$v["PID"]] = $v;
					}
				}
			}
			if(count($prgm_site)){
				if($is_debug){
					print_r($prgm_site);
					if($is_debug)echo "\r\n_____++________\r\n";
				}
				$prgm_order = $this->orderProgram($prgm_site, $domain_info["CountryCode"], $site, $domain_info);
				
				
				/*
				 * block by network
				 * save all outgoing ways 
				 */
				if(count($prgm_order)){
				//	if($is_debug){
//						print_r($prgm_order);
						
						if(isset($prgm_order['All']["main"]) && count($prgm_order['All']["main"])){// for promotion publisher
							$ttt = $prgm_order['All']["main"];
							krsort($ttt);
							$tmp_order = 0;
							foreach($ttt as $data){
								$data["LimitAccount"] = count($data["LimitAccount"]) ? implode(",", $data["LimitAccount"]) : "";
								$data["Key"] = $data["Domain"];
								$sql = "insert into domain_outgoing_all (DefaultOrder, site, DID, PID, `Key`, LimitAccount, IsFake, AddTime, LastUpdateTime, SupportType, Domain) 
										values($tmp_order, '$site','".intval($data["DID"])."', '".intval($data["PID"])."', '".addslashes($data["Key"])."', '".addslashes($data["LimitAccount"])."', '".addslashes($data["IsFake"])."', '".date("Y-m-d H:i:s")."', '".date("Y-m-d H:i:s")."', 
										'Promotion', '".addslashes($data["Domain"])."') ON DUPLICATE KEY UPDATE PID = '".intval($data["PID"])."', LimitAccount = '".addslashes($data["LimitAccount"])."', IsFake = '".addslashes($data["IsFake"])."', LastUpdateTime = '".date("Y-m-d H:i:s")."', Domain = '".addslashes($data["Domain"])."', 
										SupportType = 'Promotion'";
								$this->objMysql->query($sql);
								$tmp_order ++;
							}
						
						}
						
						if(isset($prgm_order['Content']["main"]) && count($prgm_order['Content']["main"])){// for content publisher
							$ttt = $prgm_order['Content']["main"];
							krsort($ttt);
							$tmp_order = 0;
							foreach($ttt as $data){
								$data["LimitAccount"] = count($data["LimitAccount"]) ? implode(",", $data["LimitAccount"]) : "";
								$data["Key"] = $data["Domain"];
								$sql = "insert into domain_outgoing_all (DefaultOrder, site, DID, PID, `Key`, LimitAccount, IsFake, AddTime, LastUpdateTime, SupportType, Domain) 
										values($tmp_order, '$site','".intval($data["DID"])."', '".intval($data["PID"])."', '".addslashes($data["Key"])."', '".addslashes($data["LimitAccount"])."', '".addslashes($data["IsFake"])."', '".date("Y-m-d H:i:s")."', '".date("Y-m-d H:i:s")."', 
										'Content', '".addslashes($data["Domain"])."') ON DUPLICATE KEY UPDATE PID = '".intval($data["PID"])."', LimitAccount = '".addslashes($data["LimitAccount"])."', IsFake = '".addslashes($data["IsFake"])."', LastUpdateTime = '".date("Y-m-d H:i:s")."', Domain = '".addslashes($data["Domain"])."', 
										SupportType = 'Content'";
								$this->objMysql->query($sql);
								$tmp_order ++;
							}						
						}
						
				//	}
				}
				
				
				if(isset($prgm_order['All']["main"]) && count($prgm_order['All']["main"])){
					$default_prgm[$site]['All'] = $this->formatDefaultOutgoing($domain_info, $prgm_order['All']["main"], array());
				}elseif(isset($prgm_order['All']["sub"]) && count($prgm_order['All']["sub"])){
					if(SID == 'bdg01' && $site == 'ch'){
						$tmp_de = $this->getDefaultOutgoingByStrategy($did, 'de');
						if(count($tmp_de)){
							$default_prgm[$site]['All'] = $tmp_de;
						}
						$default_prgm[$site]['All'] = $this->getDefaultOutgoingByStrategy($did, 'de');
					}else{
						$default_prgm[$site]['All'] = $this->formatDefaultOutgoing($domain_info, $prgm_order['All']["sub"], array());
					}
				}
				if(isset($prgm_order['Content']["main"]) && count($prgm_order['Content']["main"])){
					$default_prgm[$site]['Content'] = $this->formatDefaultOutgoing($domain_info, $prgm_order['Content']["main"], array());
				}elseif(isset($prgm_order['Content']["sub"]) && count($prgm_order['Content']["sub"])){
					$default_prgm[$site]['Content'] = $this->formatDefaultOutgoing($domain_info, $prgm_order['Content']["sub"], array());
				}
			}
		}
		
		unset($tmp_arr);
		
		if(SID == 'bdg01' && !isset($default_prgm['ch']['All'])){
			$tmp_de = array();
			$tmp_de = $this->getDefaultOutgoingByStrategy($did, 'de');
			if(count($tmp_de)){
				$default_prgm['ch']['All'] = $tmp_de;
			}
		}
		
		if($is_debug){
			print_r($default_prgm);
			exit;
		}
		return $default_prgm;
	}
	
	function resetTopDomain(){
		$this->topDomain = array('\.com', '\.net', '\.org', '\.gov', '\.mobi', '\.info', '\.biz', '\.cc', '\.tv', '\.asia', '\.me', '\.travel', '\.tel', '\.name', '\.co', '\.so', '\.fm', '\.eu', '\.edu', '\.coop', '\.pro', '\.nu', '\.io', '\.as', '\.club', '\.im', '\.zone', '\.tk', '\.ws', '\.gs', '\.re', '\.rs', '\.guru', '\.ac', '\.hr', '\.su');
		$country_arr = explode(",", $this->global_c);
		foreach($country_arr as $country){
			if($country){
				$country = "\.".strtolower($country);
				$this->topDomain[] = "\.com?".$country;
				$this->topDomain[] = "\.org?".$country;
				$this->topDomain[] = "\.gov?".$country;
				$this->topDomain[] = $country;
			}
		}
	}
	
	function findDomainCountry($domain){
		if(!isset($this->topDomain)){
			self::resetTopDomain();
		}
		
		$country_code = "";
		
		$domain = strtolower($domain);
		preg_match("/([^\.]*)(".implode("|", $this->topDomain).")$/mi", $domain, $matches);	
	
		if(isset($matches[1]) && strlen($matches[1])){					
			//check tail
			if(isset($matches[2]) && strlen($matches[2])){
				$tmp_arr = explode(".",$matches[2]);
				$tail = array_pop($tmp_arr);
				
				if(stripos($this->global_c, ",$tail,") !== false){
					$country_code = $tail;
				}				
			}			
			//check head
			if($matches[0] != $domain){
				//need check sec domain
				$sub_domain = trim(substr($domain, 0, stripos($domain, $matches[0])), ".");
				$tmp_arr = explode(".",$sub_domain);
				
				foreach($tmp_arr as $v){
					if(stripos($this->global_c, ",$v,") !== false){
						$country_code = $v;
						break;
					}
				}
			}			
		}
		return $country_code;
	}
	
	function getRealUrl($url, $request_arr = array())
	{
		$return_arr = array("httpcode" => "", "url" => "");
		if($url)
		{			
			$ch = curl_init();		
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_NOBODY, false);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);			
			curl_setopt($ch, CURLOPT_TIMEOUT, 60);			
			curl_exec($ch);			
			$return_arr['httpcode'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$return_arr['url'] = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
			curl_close($ch);			
		}
		return $return_arr;
	}
	
	//for get final domain
	function findFinalUrl($url, $request_arr = array())
	{
		$return_url = "";
		if($url)
		{
			echo $url = "http://go.megasvc.com:9210/curl?url=".urlencode($url);
			$ch = curl_init();		
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_NOBODY, false);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);			
			curl_setopt($ch, CURLOPT_TIMEOUT, 60);			
			$return_url = curl_exec($ch);
			curl_close($ch);							
		}
		return $return_url;
	}
	
	
	
	function getProgramDomainLinks($p_v, $domainid, $affurl = ''){
		$links_arr = array("AffDefaultUrl" => "", "DeepUrlTpl" => "");
		
		if(!count($this->aff_url_pattern)){
			$this->aff_url_pattern = $this->getAffUrlPattern();
		}
		
		if(!$affurl){
			if(!isset($this->old_ps) || !count($this->old_ps)){				
				$this->old_ps = array();					
				
				$sql = "SELECT a.affdefaulturl as AffiliateDefaultUrl, a.deepurltpl as DeepUrlTemplate, a.pid as ProgramId, a.did as DomainId, a.ishandle, a.Order FROM r_domain_program a ";
				$tmp_arr = array();
				$tmp_arr = $this->objMysql->getRows($sql);
				foreach($tmp_arr as $k => $v){
					$this->old_ps[$v["ProgramId"]][$v["DomainId"]] = $v;
				}
				unset($tmp_arr);
			}
			
			if(isset($this->old_ps[$p_v["ID"]][$domainid])){
				$tmp_arr = $this->old_ps[$p_v["ID"]][$domainid];
				if(!empty($tmp_arr["AffiliateDefaultUrl"])){
					$affurl = $tmp_arr["AffiliateDefaultUrl"];
				}else{
					/*$tmp_affurl = $this->getPendingLinks($p_v, $domainid);
					if(!empty($tmp_affurl)){
						$links_arr["AffDefaultUrl"] = $tmp_affurl;
					}*/
				}
			}
		}

		if($affurl){
			$links_arr["AffDefaultUrl"] = $affurl;
			if(@$this->aff_url_pattern[$p_v["AffId"]]["SupportDeepUrlTpl"] == "YES" && $p_v["SupportDeepUrl"] != "No" && strlen($this->aff_url_pattern[$p_v["AffId"]]["TplDeepUrlTpl"])){
				//echo "\r\n@@\r\n";
				$tmp_tpl = $this->getUrlByTpl($this->aff_url_pattern[$p_v["AffId"]]["TplDeepUrlTpl"], array("AffId" => $p_v["AffId"], "IdInAff" => $p_v["IdInAff"], "AffDefaultUrl" => $affurl));
				if(!empty($tmp_tpl)){					
					$links_arr["DeepUrlTpl"] = $tmp_tpl;
				}
			}
			
		}		
		
		return $links_arr;
	}
	
	function findSubDomain($domain){
		if(!isset($this->topDomain)){
			self::resetTopDomain();
		}		
		$tmp_domain = $domain;
		$sub_domain = "";
		
		preg_match("/([^\.]*)(".implode("|", $this->topDomain).")$/mi", $tmp_domain, $matches);
	
		if(isset($matches[1]) && strlen($matches[1])){
			//check head
			if($matches[0] != $domain){
				//need check sec domain
				$sub_domain = trim(substr($domain, 0, strripos($domain, $matches[0])), ".");				
			}
		}
		return $sub_domain;
	}

	function getProgramRank($pid){
		$sql = "SELECT 'YES' AS IsFake,a.DID,a.PID,b.AffId,b.CommissionType,b.CommissionUsed,b.CommissionCurrency,b.DeniedPubCode,c.Domain,a.AffDefaultUrl,a.DeepUrlTpl,b.SupportDeepUrl,b.OutGoingUrl,c.countrycode AS d_countrycode,b.countrycode AS p_countrycode,b.ShippingCountry,
			  d.revenueorder FROM r_domain_program a INNER JOIN program_intell b ON a.pid = b.programid
			  INNER JOIN domain c
				ON a.DID = c.id
			  INNER JOIN program_int d
				ON a.pid = d.programid
			WHERE b.isactive = 'active'
			  AND a.status = 'active'
			  AND c.SupportFake = 'YES'
			  AND b.supportFake <> 'No' AND b.programid = " . $pid;
		$prgm_arr = $this->objMysql->getFirstRow($sql);
		if(count($prgm_arr) ==0)
			return 0.0;
		$objProgram = New Program();
		if(!isset($this->exchange_rate)){
			$sql = "SELECT ExchangeRate, `Name` FROM exchange_rate WHERE `Date` = (SELECT MAX(`Date`) FROM exchange_rate) GROUP BY `Name`";
			$this->exchange_rate = $this->objMysql->getRows($sql, "Name");
		}
		if(!isset($this->block_rel) || !is_array($this->block_rel)){
			$block_rel = array();
			$data = array();
			//get block relationship
			$tmp_arr = $this->getBlockRelationship(array("status" => "active"));
			foreach($tmp_arr as $v){
				if($v["objtype"] == "Affiliate"){
					$data["aff"][$v["objid"]][$v["accountid"]] = $v["accountid"];
				}elseif($v["objtype"] == "Program"){
					$data["program"][$v["objid"]][$v["accountid"]] = $v["accountid"];
				}
			}
			$this->block_rel = $data;
		}
		if(is_array($prgm_arr))
		{
			if (!strlen ($prgm_arr["AffDefaultUrl"]) && !strlen ($prgm_arr["DeepUrlTpl"]))
				return 0;

			$is_subaff = true;
			$prgm_arr["LimitAccount"] = array ();
			//check limited aff OR program
			if (isset($this->block_rel["aff"][$prgm_arr["AffId"]]))
			{
				$prgm_arr["LimitAccount"] = count ($prgm_arr["LimitAccount"]) ? array_merge ($prgm_arr["LimitAccount"] , $this->block_rel["aff"][$prgm_arr["AffId"]]) : $this->block_rel["aff"][$prgm_arr["AffId"]];
			}
			if (isset($this->block_rel["program"][$prgm_arr["PID"]]))
			{
				$prgm_arr["LimitAccount"] = count ($prgm_arr["LimitAccount"]) ? array_merge ($prgm_arr["LimitAccount"] , $this->block_rel["program"][$prgm_arr["PID"]]) : $this->block_rel["program"][$prgm_arr["PID"]];
			}

			//print_r($this->block_rel);

			//if ($site != "uk" && $prgm_arr["AffId"] == "57"){
			//	//OMGpm UK (57) not work with un uk site.
			//	return 0;
			//}

			$order = 0.0;

			if (!empty($prgm_arr['d_countrycode']) && stripos ("," . $prgm_arr['ShippingCountry'] . "," , ",{$prgm_arr['d_countrycode']},") !== false)
				$order += 10;
			if (isset($prgm_arr['IsFake']) && $prgm_arr['IsFake'] == "NO"){
				$order += 100;
			}

			#1
			//if($prgm_arr["AffId"] == "37" || $prgm_arr["AffId"] == "177" || $prgm_arr["AffId"] == "97") continue;
			if ($prgm_arr["AffId"] == "97")
				return 0;
			if ($prgm_arr["AffId"] == "177")
				$order -= 2000;
			if ($prgm_arr["AffId"] == "37")
				$order -= 2000;
			$sub_aff = $this->sub_aff;

			if (!in_array ($prgm_arr["AffId"] , $sub_aff)){
				$order += 2000;
				$is_subaff = false;
				//if($prgm_arr["AffId"] == "37") $order -= 2000;
				if (isset($prgm_arr['IsFake']) && $prgm_arr['IsFake'] == "YES"){
					$order -= 100;
					$revenueorder = isset($prgm_arr['revenueorder']) ? intval ($prgm_arr['revenueorder']) : 9999999;
					$revenueorder = ($revenueorder < 10000) ? round (100 - ($revenueorder / 100) , 5) : 0;
					$order += $revenueorder;
				}
			} else{
				$revenueorder = isset($prgm_arr['revenueorder']) ? intval ($prgm_arr['revenueorder']) : 9999999;
				$revenueorder = ($revenueorder < 10000) ? round (100 - ($revenueorder / 100) , 5) : 0;
				$order += $revenueorder;
			}
			#2
			if (strlen ($prgm_arr["DeepUrlTpl"]))
			{
				$order += 100;
			} elseif (strlen ($prgm_arr["AffDefaultUrl"]))
			{
				$order += 10;
			}

			if (strtolower ($prgm_arr["SupportDeepUrl"]) == "yes")
			{
				$order += 20;
			} elseif (strtolower ($prgm_arr["SupportDeepUrl"]) == "unknown")
			{
				$order += 19.5;
				if (strlen ($prgm_arr["DeepUrlTpl"]))
				{
					$order += 0.5;
				}
			} else
			{
				if (strlen ($prgm_arr["DeepUrlTpl"]))
				{
					$order += 19.99;
				}
			}

			#3
			//click bank commission , different
			if ($prgm_arr["AffId"] == "37")
				$prgm_arr["CommissionUsed"] = $prgm_arr["CommissionUsed"] / 10;

			if ($prgm_arr["CommissionUsed"] == 0 || $prgm_arr["AffId"] == 191)
			{
				$order -= 100;
			}
			
			//FOR skimlink is above all other sub aff
			if ($prgm_arr["AffId"] == 223)
			{
				$order += 20;
			}

			//error
			if ($prgm_arr["CommissionUsed"] == 100 && $prgm_arr["CommissionType"] == "Percent")
			{
				$prgm_arr["CommissionUsed"] = $prgm_arr["CommissionUsed"] / 10;
			}

			//exchange_rate
			if (isset($this->exchange_rate[$prgm_arr["CommissionCurrency"]]) && $prgm_arr["CommissionCurrency"] != "USD")
			{
				//echo $prgm_arr["CommissionUsed"];
				$prgm_arr["CommissionUsed"] = ($prgm_arr["CommissionUsed"] * $this->exchange_rate[$prgm_arr["CommissionCurrency"]]["ExchangeRate"]) / $this->exchange_rate["USD"]["ExchangeRate"];

				//echo "/".$prgm_arr["CommissionUsed"];
			}

			$order += floatval ($prgm_arr["CommissionUsed"]);
			if ($prgm_arr["CommissionType"] == "Value"){
				$order += 0.1;
			}

			#4	//rank 101 ~ 99999999
			if (!isset($aff_rank) || !count ($aff_rank))
			{
				$aff_rank = $this->getAffRank ();
			}
			$tmp_rank = (isset($aff_rank[$prgm_arr["AffId"]]["Rank"]) && intval ($aff_rank[$prgm_arr["AffId"]]["Rank"])) ? round ((10 / intval ($aff_rank[$prgm_arr["AffId"]]["Rank"])) , 5) : 0;

			$order += ($tmp_rank / 10);
			$order *= 100000;
			$order = intval ($order);
		} else {
			return 0;
		}
		return $order;
	}
	
	function getTopDomain()
	{
		$sql = "select Domain from domain_top_level";
		$topDomain_tmp = $this->objMysql->getRows($sql);
		$topDomain = array();
		foreach ($topDomain_tmp as $v)
		{
			$topDomain[] = '\.'.$v['Domain'];
		}
		$country_arr = explode(",", trim($this->global_c,','));
		foreach ($country_arr as $country) {
			if ($country) {
				$country = "\." . strtolower($country);
				$topDomain[] = "\.com?" . $country;
				$topDomain[] = "\.org?" . $country;
				$topDomain[] = "\.net?" . $country;
				$topDomain[] = "\.gov?".$country;
				$topDomain[] = "\.edu?".$country;
				$topDomain[] =  $country."\.com";
				$topDomain[] = $country;
			}
		}
		return $topDomain;
	}

}//end class
?>
