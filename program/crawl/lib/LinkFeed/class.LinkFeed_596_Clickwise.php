<?php

require_once 'text_parse_helper.php';
require_once 'xml2array.php';


class LinkFeed_596_Clickwise
{
	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->getStatus = false;
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		if(SID == 'bdg01'){
			
		}else{
			
		}
	}
	
	function Login()
	{
		$url = 'http://my.pampanetwork.com/scripts/track.php?url=H_my.pampanetwork.com%2Faffiliates%2Flogin.php&referrer=H_my.pampanetwork.com%2Faffiliates%2Findex.php&getParams=&anchor=login&isInIframe=false&cookies=';
		$re = $this->oLinkFeed->GetHttpResult($url);
		$re = $re['content'];
		$PAPVisitorId = $this->oLinkFeed->ParseStringBy2Tag($re, "('", "')");
		$strUrl = "http://my.pampanetwork.com/affiliates/login.php#login";
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => "get",
				"postdata" => "",
				"addcookie" => array(
						'PAPVisitorId' => array(
								'domain' => 'my.pampanetwork.com',
								'name' => 'PAPVisitorId',
								'value' => $PAPVisitorId,
						),
				),
		);
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];
		$this->S = urlencode($this->oLinkFeed->ParseStringBy2Tag($result, '[\"S\",\"', '\"'));
	
		$this->info["AffLoginPostString"] = str_ireplace('{S}', $this->S, $this->info["AffLoginPostString"]);
	
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info,3,true,false,true);
	}
	
	function getCouponFeed()
	{
		$check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
		
		
		return $arr_return;
	}
	
	function GetAllLinksByAffId()
	{
		$check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(),);
		
		//1.login
		$this->Login();
		
		$page = 1;
		$offset = 0;
		$limit = 100;
		$hasNextPage = true;
		$url = 'http://my.pampanetwork.com/scripts/server.php';
		while ($hasNextPage)
		{
			$request = array(
					"AffId" => $this->info["AffId"],
					"method" => "post",
					"postdata" => "D=%7B%22C%22%3A%22Gpf_Rpc_Server%22%2C+%22M%22%3A%22run%22%2C+%22requests%22%3A%5B%7B%22C%22%3A%22Pap_Affiliates_Promo_BannersGrid%22%2C+%22M%22%3A%22getRows%22%2C+%22offset%22%3A{$offset}%2C+%22limit%22%3A{$limit}%2C+%22filters%22%3A%5B%5B%22type%22%2C%22IN%22%2C%22A%2CE%2CI%2CT%22%5D%5D%2C+%22columns%22%3A%5B%5B%22id%22%5D%2C%5B%22id%22%5D%2C%5B%22destinationurl%22%5D%2C%5B%22name%22%5D%2C%5B%22campaignid%22%5D%2C%5B%22campaignname%22%5D%2C%5B%22bannercode%22%5D%2C%5B%22bannerdirectlinkcode%22%5D%2C%5B%22bannerpreview%22%5D%2C%5B%22rtype%22%5D%2C%5B%22displaystats%22%5D%2C%5B%22channelcode%22%5D%2C%5B%22campaigndetails%22%5D%5D%7D%5D%2C+%22S%22%3A%22{$this->S}%22%7D",
			);
			$r = $this->oLinkFeed->GetHttpResult($url,$request);
			$result = json_decode($r["content"],true);
			//var_dump($result);exit;
			
			/* 
			array (size=101)
				0 =>
					array (size=33)
						0 => string 'id' (length=2)
						1 => string 'bannerid' (length=8)
						2 => string 'accountid' (length=9)
						3 => string 'campaignid' (length=10)
						4 => string 'campaignname' (length=12)
						5 => string 'rtype' (length=5)
						6 => string 'rstatus' (length=7)
						7 => string 'name' (length=4)
						8 => string 'destinationurl' (length=14)
						9 => string 'target' (length=6)
						10 => string 'size' (length=4)
						11 => string 'data1' (length=5)
						12 => string 'data2' (length=5)
						13 => string 'data3' (length=5)
						14 => string 'data4' (length=5)
						15 => string 'data5' (length=5)
						16 => string 'data6' (length=5)
						17 => string 'data7' (length=5)
						18 => string 'data8' (length=5)
						19 => string 'data9' (length=5)
						20 => string 'rorder' (length=6)
						21 => string 'wrapperid' (length=9)
						22 => string 'description' (length=11)
						23 => string 'seostring' (length=9)
						24 => string 'bannercode' (length=10)
						25 => string 'bannerpreview' (length=13)
						26 => string 'bannerclickurl' (length=14)
						27 => string 'bannerdirectlinkcode' (length=20)
						28 => string 'campaigndetails' (length=15)
						29 => string 'displaystats' (length=12)
						30 => string 'userid' (length=6)
						31 => string 'channel' (length=7)
						32 => string 'channelcode' (length=11)
			 */
			
			$count = $result[0]['count'];
			foreach ($result[0]['rows'] as $v)
			{
				if ($offset+100 >= $count)
					$hasNextPage = false;
				if ($v[0] == 'id')				//第一元素是字段展示
					continue;
				if ($v[6] != 'A')				//active
					continue;
				$strMerID = $v[3];
				$link = array(
						"AffId" => $this->info["AffId"],
						"AffMerchantId" => $strMerID,
						"AffLinkId" => $v[1],
						"LinkName" =>  addslashes($v[7]),
						"LinkDesc" =>  isset($v[22]) ? addslashes($v[22]) : '',
						"LinkStartDate" => '',
						"LinkEndDate" => '',
						"LinkPromoType" => 'link',
						"LinkHtmlCode" => '',
						"LinkCode" => '',
						"LinkOriginalUrl" => $v[8],
						"LinkImageUrl" => '',
						"LinkAffUrl" => $v[26],
						"DataSource" => '427',
				);
				
				if (isset($v[28]))
					$link['LinkDesc'] .= $v[28];
				
				if (empty($link['AffMerchantId']) || empty($link['LinkName']) || empty($link['AffLinkId']))
					continue;
				
				if (empty($link['LinkHtmlCode']))
					$link['LinkHtmlCode'] = create_link_htmlcode($link);
				
				$arr_return["AffectedCount"] ++;
				$links [] = $link;
			}
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
			echo sprintf("page:%s, %s links(s) found. \n", $page, count($links));
			$links = array();
			$offset += 100;
			$page++;
		}
		return $arr_return;
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
		
		//1.login
		$this->Login();
		
		//2.get homepage from link
        $homepage_arr = array();
		$page = 1;
		$Hoffset = 0;
		$limit = 100;
		$hasNextPage = true;
		$url = 'http://my.pampanetwork.com/scripts/server.php';
		while ($hasNextPage)
		{
			$request = array(
					"AffId" => $this->info["AffId"],
					"method" => "post",
					"postdata" => "D=%7B%22C%22%3A%22Gpf_Rpc_Server%22%2C+%22M%22%3A%22run%22%2C+%22requests%22%3A%5B%7B%22C%22%3A%22Pap_Affiliates_Promo_BannersGrid%22%2C+%22M%22%3A%22getRows%22%2C+%22offset%22%3A{$Hoffset}%2C+%22limit%22%3A{$limit}%2C+%22filters%22%3A%5B%5B%22type%22%2C%22IN%22%2C%22A%2CE%2CI%2CT%22%5D%5D%2C+%22columns%22%3A%5B%5B%22id%22%5D%2C%5B%22id%22%5D%2C%5B%22destinationurl%22%5D%2C%5B%22name%22%5D%2C%5B%22campaignid%22%5D%2C%5B%22campaignname%22%5D%2C%5B%22bannercode%22%5D%2C%5B%22bannerdirectlinkcode%22%5D%2C%5B%22bannerpreview%22%5D%2C%5B%22rtype%22%5D%2C%5B%22displaystats%22%5D%2C%5B%22channelcode%22%5D%2C%5B%22campaigndetails%22%5D%5D%7D%5D%2C+%22S%22%3A%22{$this->S}%22%7D",
			);
			$r = $this->oLinkFeed->GetHttpResult($url,$request);
			$result = json_decode($r["content"],true);
			$count = $result[0]['count'];
			foreach ($result[0]['rows'] as $v)
			{
				if ($Hoffset+100 >= $count)
					$hasNextPage = false;
				if ($v[0] == 'id')				//第一元素是字段展示
					continue;
				if ($v[6] != 'A')				//active
					continue;
				if (isset($homepage_arr[$v[3]]))
					continue;
				$strMerID = $v[3];
				$OriginalUrl = $this->oLinkFeed->findFinalUrl($v[9]);
				$url_arr = parse_url($OriginalUrl);
				
				if (isset($url_arr['scheme']) && isset($url_arr['host']))
					$homepage = $url_arr['scheme'].'://'.$url_arr['host'];
				else 
					continue;
				
				if (stripos($homepage, 'track') !== false)
					continue;
				echo $OriginalUrl."\r\n";
				echo $homepage."\r\n";
				$homepage_arr[$strMerID] = $homepage;
			}
			$Hoffset += 100;
			$page++;
		}
		
		//3.get program
		$offset = 0;
		$limit = 100;
		$hasNextPage = true;
		$url = 'http://my.pampanetwork.com/scripts/server.php';
		$status = array(
				'A' => 'approved',
				'D' => 'declined',
				'P' => 'waiting for approval',
		);
		while ($hasNextPage)
		{
			$request = array(
					"AffId" => $this->info["AffId"],
					"method" => "post",
					"postdata" => "D=%7B%22C%22%3A%22Gpf_Rpc_Server%22%2C+%22M%22%3A%22run%22%2C+%22requests%22%3A%5B%7B%22C%22%3A%22Pap_Affiliates_Promo_CampaignsGrid%22%2C+%22M%22%3A%22getRows%22%2C+%22offset%22%3A{$offset}%2C+%22limit%22%3A{$limit}%2C+%22columns%22%3A%5B%5B%22id%22%5D%2C%5B%22id%22%5D%2C%5B%22name%22%5D%2C%5B%22description%22%5D%2C%5B%22logourl%22%5D%2C%5B%22banners%22%5D%2C%5B%22commissionsdetails%22%5D%2C%5B%22rstatus%22%5D%2C%5B%22commissionsexist%22%5D%2C%5B%22affstatus%22%5D%2C%5B%22dateinserted%22%5D%2C%5B%22overwritecookie%22%5D%2C%5B%22avarageConversion%22%5D%2C%5B%22actions%22%5D%5D%7D%5D%2C+%22S%22%3A%22{$this->S}%22%7D",
			);
			$r = $this->oLinkFeed->GetHttpResult($url,$request);
			$result = json_decode($r["content"],true);
			//var_dump($result);exit;
			/* array (
				[0] =>  'id',
				[1] =>  'campaignid',
				[2] =>  'rstatus',						'A':Active,'W':TempOffline
				[3] =>  'name',
				[4] =>  'description',
				[5] =>  'dateinserted',
				[6] =>  'logourl',
				[7] =>  'overwritecookie',
				[8] =>  'banners',
				[9] =>  'avarageConversion',
				[10] => 'affstatus',					'A','D','P'
				[11] => 'commissionsexist',				'Y','N'
				[12] => 'commissionsdetails',
			) */
			$count = $result[0]['count'];
			foreach ($result[0]['rows'] as $v)
			{
				if ($offset+100 >= $count)
					$hasNextPage = false;
				if ($v[0] == 'id')
					continue;
				$strMerID = $v[0];
				
				if (empty($v[10]))
					$StatusInAffRemark = 'NoPartnership';
				else 
					$StatusInAffRemark = $status[$v[10]];
				
				if ($StatusInAffRemark == 'approved')
				{
					$Partnership = 'Active';
				}elseif ($StatusInAffRemark == 'declined')
				{
					$Partnership = 'Declined';
				}elseif ($StatusInAffRemark == 'waiting for approval')
				{
					$Partnership = 'Pending';
				}elseif ($StatusInAffRemark == 'NoPartnership')
				{
					$Partnership = 'NoPartnership';
				}else 
				{
					print_r($v);
					mydie("die: New status is $v[10], add it please");
				}
				if ($v[2] == 'A')
					$StatusInAff = 'Active';
				elseif ($v[2] == 'W')
					$StatusInAff = 'TempOffline';
				else 
					mydie("die: there is new rstatus named $v[2], add it please");
				
				$strMerName = trim($v[3]);
				$LogoUrl = trim($v[6]);
				$CreateDate = trim($v[5]);
				
				$search = array (
						'/<!--[\/!]*?[^<>]*?-->/isu', // 去掉 注释标记
						'/<script[^>]*?>.*?<\/script>/isu', // 去掉 javascript
						'/<style[^>]*?>.*?<\/style>/isu', // 去掉 css
				);
				$desc = preg_replace($search,'',$v[4]);
				$CommissionExt = trim(html_entity_decode($v[12]));
				$lineStart = 0;
				//$CategoryExt = trim(strip_tags(html_entity_decode($this->oLinkFeed->ParseStringBy2Tag($desc, '>','<br>', $lineStart))));
				/* if (!empty($CategoryExt))
				{
					$TargetCountryExt = trim(strip_tags(html_entity_decode($this->oLinkFeed->ParseStringBy2Tag($desc, array('<font', '>'),'</font>', $lineStart))));
					if (strpos($TargetCountryExt, 'CP') !== false || empty($TargetCountryExt))
					{
						$TargetCountryExt = trim(strip_tags(html_entity_decode($this->oLinkFeed->ParseStringBy2Tag($desc, '>','<br>', $lineStart))));
						$TermAndCondition = trim(strip_tags(html_entity_decode(substr($desc, $lineStart))));
					}else 
						$TermAndCondition = trim(strip_tags(html_entity_decode(substr($desc, $lineStart))));
				}else{
					$CategoryExt = trim(strip_tags(html_entity_decode($this->oLinkFeed->ParseStringBy2Tag($desc, array('<font', '>'),'</font>', $lineStart))));
					$TargetCountryExt = trim(strip_tags(html_entity_decode($this->oLinkFeed->ParseStringBy2Tag($desc, array('<font', '>'),'</font>', $lineStart))));
					if (strpos($TargetCountryExt, 'CP') !== false || empty($TargetCountryExt))
					{
						$TargetCountryExt = trim(strip_tags(html_entity_decode($this->oLinkFeed->ParseStringBy2Tag($desc, array('<font', '>'),'</font>', $lineStart))));
						$TermAndCondition = trim(strip_tags(html_entity_decode(substr($desc, $lineStart))));
					}else
						$TermAndCondition = trim(strip_tags(html_entity_decode(substr($desc, $lineStart))));
				} */
				
				/* if (!empty($CategoryExt))
				{
					while (1)
					{
						$TargetCountryExt = trim(strip_tags(html_entity_decode($this->oLinkFeed->ParseStringBy2Tag($desc, '>','<', $lineStart))));
						if (strpos($TargetCountryExt, 'CP') !== false || empty($TargetCountryExt))
							continue;
						else 
							break;
					}
					$TermAndCondition = trim(strip_tags(html_entity_decode(substr($desc, $lineStart))));
				}else {
					$CategoryExt = trim(strip_tags(html_entity_decode($this->oLinkFeed->ParseStringBy2Tag($desc, array('<font', '>'),'</font>', $lineStart))));
					while (1)
					{
						$TargetCountryExt = trim(strip_tags(html_entity_decode($this->oLinkFeed->ParseStringBy2Tag($desc, '>','<', $lineStart))));
						if (strpos($TargetCountryExt, 'CP') !== false || empty($TargetCountryExt))
							continue;
						else 
							break;
					}
					$TermAndCondition = trim(strip_tags(html_entity_decode(substr($desc, $lineStart))));
				} */
				while (1)
				{
					$CategoryExt = trim(strip_tags(html_entity_decode($this->oLinkFeed->ParseStringBy2Tag($desc, '>','<', $lineStart))));
					$lastStr = substr($desc, $lineStart+1);
					if (!empty($CategoryExt) || (strpos($lastStr, '<') === false))
						break;
				}
				$country_str = "Armenia,Netherlands Antilles,Angola,Antarctica,Argentina,America,Austria,Australia,Aruba,Azerbaijan,Bosnia and Herzegovina,Barbados,Bangladesh,Belgium,Burkina Faso,Bulgaria,Bahrain,Burundi,Benin,Bermuda,Brunei Darussalam,Bolivia,Brazil,Bahamas,Bhutan,Bouvet Island,Botswana,Belarus,Belize,Canada,Cocos (Keeling) Islands,Congo, The Democratic Republic of the,Central African Republic,Congo,Switzerland,Cote D'Ivoire,Cook Islands,Chile,Cameroon,China,Colombia,Costa Rica,Cuba,Cape Verde,Christmas Island,Cyprus,Czech Republic,Germany,Djibouti,Denmark,Dominica,Dominican Republic,Algeria,Ecuador,Estonia,Egypt,Western Sahara,Eritrea,Spain,Ethiopia,Finland,Fiji,Falkland Islands (Malvinas),Micronesia, Federated States of,Faroe Islands,France,France, Metropolitan,Gabon,United Kingdom,Grenada,Georgia,French Guiana,Ghana,Gibraltar,Greenland,Gambia,Guinea,Guadeloupe,Equatorial Guinea,Greece,South Georgia and the South Sandwich Islands,Guatemala,Guam,Guinea-Bissau,Guyana,Hong Kong,Heard Island and McDonald Islands,Honduras,Croatia,Haiti,Hungary,Indonesia,Ireland,Israel,India,British Indian Ocean Territory,Iraq,Iran, Islamic Republic of,Iceland,Italy,Jamaica,Jordan,Japan,Kenya,Kyrgyzstan,Cambodia,Kiribati,Comoros,Saint Kitts and Nevis,Korea, Democratic People's Republic of,Korea, Republic of,Kuwait,Cayman Islands,Kazakstan,Lao People's Democratic Republic,Lebanon,Saint Lucia,Liechtenstein,SriLanka,Liberia,Lesotho,Lithuania,Luxembourg,Latvia,Libyan,Morocco,Monaco,Moldova, Republic of,Madagascar,Marshall Islands,Macedonia,Mali,Myanmar,Mongolia,Macau,Northern Mariana Islands,Martinique,Mauritania,Montserrat,Malta,Mauritius,Maldives,Malawi,Mexico,Malaysia,Mozambique,Namibia,New Caledonia,Niger,Norfolk Island,Nigeria,Nicaragua,Netherlands,Norway,Nepal,Nauru,Niue,New Zealand,Oman,Panama,Peru,French Polynesia,Papua New Guinea,Philippines,Pakistan,Poland,Saint Pierre and Miquelon,Pitcairn Islands,Puerto Rico,Palestinian Territory,Portugal,Palau,Paraguay,Qatar,Reunion,Romania,Russia,Rwanda,Saudi,Arab,Solomon Islands,Seychelles,Sudan,Sweden,Singapore,Saint Helena,Slovenia,Svalbardand Jan Mayen,Slovakia,Sierra Leone,San Marino,Senegal,Somalia,Suriname,Sao Tome and Principe,El Salvador,Syrian,Swaziland,Turks and Caicos Islands,Chad,French Southern Territories,Togo,Thailand,Tajikistan,Tokelau,Turkmenistan,Tunisia,Tonga,Timor-Leste,Turkey,Trinidad and Tobago,Tuvalu,Taiwan,Tanzania, United Republic of,Ukraine,Uganda,United States Minor Outlying Islands,United States,Uruguay,Uzbekistan,Holy See (Vatican City State),Saint Vincent and the Grenadines,Venezuela,Virgin Islands, British,Virgin Islands, U.S.,Vietnam,Vanuatu,Wallis and Futuna,Samoa,Yemen,Mayotte,Serbia,South Africa,Zambia,Montenegro,Zimbabwe,Anonymous Proxy,Satellite Provider,Other,Aland Islands,Guernsey,Isle of Man,Jersey,world,uk,usa,LATAM,BRASIL,MÉXICO,KENIA,ESPAÑA,Europe,PERÚ,IVORY COAST";
				$country_arr = explode(",", $country_str);
				while (1)
				{
					$TargetCountryExt = trim(strip_tags(html_entity_decode($this->oLinkFeed->ParseStringBy2Tag($desc, '>','<', $lineStart))));
					$lastStr = substr($desc, $lineStart+1);
					foreach ($country_arr as $c)
					{
						if (stripos($TargetCountryExt, $c) !== false)
							break 2;
					}
					if (strpos($lastStr, '<') === false)
						break;
				}
				$TermAndCondition = trim(strip_tags(html_entity_decode(substr($desc, $lineStart))));
				
				$Homepage = isset($homepage_arr[$strMerID])?$homepage_arr[$strMerID]:'';
				$desc = trim(strip_tags(html_entity_decode($desc)));
				
				$arr_prgm[$strMerID] = array(
						"Name" => addslashes($strMerName),
						"AffId" => $this->info["AffId"],
						//"Contacts" => $Contacts,
						"TargetCountryExt" => $TargetCountryExt,
						"IdInAff" => $strMerID,
						"JoinDate" => $CreateDate,
						"StatusInAffRemark" => $StatusInAffRemark,
						"StatusInAff" => $StatusInAff,                        //'Active','TempOffline','Offline'
						"Partnership" => $Partnership,                        //'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
						"Description" => addslashes($desc),
						"Homepage" => addslashes($Homepage),
						"TermAndCondition" => addslashes($TermAndCondition),
						"LastUpdateTime" => date("Y-m-d H:i:s"),
						//"DetailPage" => $detail_url,
						//"AffDefaultUrl" => addslashes($AffDefaultUrl),
						"CommissionExt" => addslashes($CommissionExt),
						"CategoryExt" => addslashes(trim($CategoryExt)),
						"SupportDeepUrl"=>'UNKNOWN',
						"AllowNonaffCoupon"=>'UNKNOWN'
				);
				//print_r($arr_prgm[$strMerID]);
				$program_num++;
				if(count($arr_prgm) >= 100){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					//$this->oLinkFeed->saveLogs($this->info["AffId"], $this->file, $arr_prgm);
					$arr_prgm = array();
				}
			}
			$offset += 100;
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
		echo "\tSet program country int.\r\n";
		$objProgram->setCountryInt($this->info["AffId"]);
	}
	
	function checkProgramOffline($AffId, $check_date)
	{
		$objProgram = new ProgramDb();
		$prgm = array();
		$prgm = $objProgram->getNotUpdateProgram($this->info["AffId"], $check_date);
	
		if (count($prgm) > 30) {
			mydie("die: too many offline program (" . count($prgm) . ").\n");
		} else {
			$objProgram->setProgramOffline($this->info["AffId"], $prgm);
			echo "\tSet (" . count($prgm) . ") offline program.\r\n";
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}
?>