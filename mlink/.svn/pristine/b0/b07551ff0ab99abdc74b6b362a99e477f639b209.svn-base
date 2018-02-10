<?php
class Program extends LibFactory
{		
	var $global_c = "";
	var $url_pattern = "";
	
	function __construct()
	{	
		$this->global_c = ",AD,AE,AF,AG,AI,AL,AM,AO,AR,AT,AU,AZ,BB,BD,BE,BF,BG,BH,BI,BJ,BL,BM,BN,BO,BR,BS,BW,BY,BZ,CA,CF,CG,CH,CK,CL,CM,CN,CO,CR,CS,CU,CY,CZ,DE,DJ,DK,DO,DZ,EC,EE,EG,ES,ET,FI,FJ,FR,GA,GB,GD,GE,GF,GH,GI,GM,GN,GR,GT,GU,GY,HK,HN,HT,HU,ID,IE,IL,IN,IQ,IR,IS,IT,JM,JO,JP,KE,KG,KH,KP,KR,KT,KW,KZ,LA,LB,LC,LI,LK,LR,LS,LT,LU,LV,LY,MA,MC,MD,MG,ML,MM,MN,MO,MS,MT,MU,MV,MW,MX,MY,MZ,NA,NE,NG,NI,NL,NO,NP,NR,NZ,OM,PA,PE,PF,PG,PH,PK,PL,PR,PT,PY,QA,RO,RU,SA,SB,SC,SD,SE,SG,SI,SK,SL,SM,SN,SO,SR,ST,SV,SY,SZ,TD,TG,TH,TJ,TM,TN,TO,TR,TT,TW,TZ,UA,UG,UK,US,UY,UZ,VC,VE,VN,YE,YU,ZA,ZM,ZR,ZW,";
		$this->url_pattern = "^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(([a-z]|\d|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])*([a-z]|\d|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])))\.)+(([a-z]|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(([a-z]|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])*([a-z]|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\x{E000}-\x{F8FF}]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$";		
	}	
	
	function getDomainByHomepage($homepage){
		$domain_arr = array();
		if($homepage){
			if(!preg_match("/^(https?)\\/\\//i",$homepage)){
				$homepage = "http://".$homepage;
			}
			$homepage = preg_replace("/(https?:?\\/\\/:?https?:?\\/\\/:?)/i", "http://", $homepage);
						
			$matches = array();
			preg_match_all("/https?:?\\/\\/:?([^\s;,\\?\\/]*)[^\s]*/i", $homepage, $matches);			
			
			foreach($matches[1] as $k => $v){
				if($v){				
					if(!preg_match("/^(https?|ftp)/i",$v)){
						$v = "http://".$v;
					}
				}
				
				$domain = "";
				$domain = $this->getUrlDomain($v);
				
				if($domain){
					$domain_arr[] = $domain;
					
					preg_match("/$domain\\/([a-zA-Z]{2})(?:[\\/\\?]|$)/i", $matches[0][$k], $m);
					if(count($m) && $this->checkDomainCountry($m[1]))
					{					
						$domain_arr[] = $domain."/".$m[1];
						$domain_arr[] = $m[1].".".$domain;
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
		$topDomain = array('.com', '.net', '.org', '.gov', '.mobi', '.info', '.biz', '.cc', '.tv', '.asia', '.me', '.travel', '.tel', '.name', '.co', '.so', '.com.au', '.co.uk', '.ca');
				
		mb_regex_encoding("utf-8");
		if (!mb_ereg($this->url_pattern, trim($url))) return false;
		
		$url = trim(preg_replace("/https?:\/\//i", '', trim($url)));
		
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
}
?>