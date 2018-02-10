<?php
require_once 'text_parse_helper.php';

class LinkFeed_734_Adpump
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);                            
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		
	}
	
	function Login()
	{
		$_ctURL = "https://adpump.com/uk-en/session/login/";
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => "get"
		);
		$_ctResult = $this->oLinkFeed->GetHttpResult($_ctURL, $request);//print_r($_ctResult);exit;
		$_ct = trim($this->oLinkFeed->ParseStringBy2Tag($_ctResult['content'], "data['__ct'] = '", "'"));
		$this->info['AffLoginPostString'] .= "&__ct=$_ct";
		//var_dump($this->info);exit;
		
		$Header = array(
				'X-Requested-With: XMLHttpRequest',
				'Accept-Encoding: gzip, deflate, br',
				'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
				'Accept: */*',
				'Accept-Language: zh-CN,zh;q=0.8',
				'Referer: https://adpump.com/uk-en/',
		);
		
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => "post",
				"postdata" => $this->info['AffLoginPostString'],
				"addheader" => $Header,
		);
		$arr = $this->oLinkFeed->GetHttpResult($this->info['AffLoginUrl'], $request);
		//print_r($arr);exit;
		if (stripos($arr['content'], 'authorized":true') !== false)
			echo "login succ\r\n";
		else
			mydie("login failed\r\n");
		
	}
	
	function GetAllLinksByAffId()
	{
		$check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, );
		
		//step 1,login
		$this->Login();
		
		//step 2,get program from page
		$page = 1;
		$HasNextPage = true;
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => "get"
		);
		while ($HasNextPage)
		{
			$links = array();
			$page_url = "https://adpump.com/uk-en/wmOffers/page:$page?action=&act=";
			$page_r = $this->oLinkFeed->GetHttpResult($page_url, $request);
			$page_r = $page_r['content'];
			//print_r($page_r);exit;
			if (!isset($lastPage))
				$lastPage = trim($this->oLinkFeed->ParseStringBy2Tag($page_r, array('<span class="page last">', '>'), '<'));
				
			if ($page == $lastPage)
				$HasNextPage = false;
				
			$nLineStart = 0;
			while (1)
			{
				$item_r = trim($this->oLinkFeed->ParseStringBy2Tag($page_r, '<tr class="">', '</tr>', $nLineStart));
				if (empty($item_r))
					break;
				$LineStart = 0;
				$AffMerchantId = trim($this->oLinkFeed->ParseStringBy2Tag($item_r, 'href="https://adpump.com/uk-en/wmOffers/view/id:', '"', $LineStart));
				$Name = trim($this->oLinkFeed->ParseStringBy2Tag($item_r, '>', '<', $LineStart));
				$getLink_Url = trim($this->oLinkFeed->ParseStringBy2Tag($item_r, '<button data-wmgetlinks="', '"', $LineStart));
				$AffLinkId = trim($this->oLinkFeed->ParseStringBy2Tag($getLink_Url, 'id:', ''));
				if (empty($AffLinkId))
					continue;
				$link_r = $this->oLinkFeed->GetHttpResult($getLink_Url, $request);
				
				$LinkAffUrl = trim($this->oLinkFeed->ParseStringBy2Tag($link_r['content'], array('<textarea', '>'), '{subaccount}'));
				
				$link = array(
						"AffId" => $this->info["AffId"],
						"AffMerchantId" => $AffMerchantId,
						"AffLinkId" => $AffLinkId,
						"LinkName" => addslashes(trim(html_entity_decode($Name))),
						"LinkDesc" => '',
						"LinkStartDate" => '0000-00-00 00:00:00',
						"LinkEndDate" => '0000-00-00 00:00:00',
						"LinkPromoType" => 'link',
						"LinkHtmlCode" => '',
						"LinkCode" => '',
						"LinkOriginalUrl" => '',
						"LinkImageUrl" => '',
						"LinkAffUrl" => $LinkAffUrl,
						"DataSource" => 440,
						"IsDeepLink" => 'UNKNOWN',
						"Type"       => 'link'
				);
				$link['LinkHtmlCode'] = create_link_htmlcode($link);
				if (empty($link['AffLinkId']) || empty($link['LinkHtmlCode']) || empty($link['LinkAffUrl']))
					continue;
				$this->oLinkFeed->fixEnocding($this->info, $link, "feed");
				$arr_return["AffectedCount"] ++;
				$links[] = $link;
			}
			echo sprintf("page:%s, %s links(s) found. \n", $page, count($links));
			if(count($links) > 0)
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
			$page++;
		}
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link');
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
		
		//step 1,login
		$this->Login();
		
		//step 2,get program from page
		$page = 1;
		$HasNextPage = true;
		$request = array(
				"AffId" => $this->info["AffId"], 
				"method" => "get"
		);
		while ($HasNextPage)
		{
			$page_url = "https://adpump.com/uk-en/wmOffers/page:$page?action=&act=";
			$page_r = $this->oLinkFeed->GetHttpResult($page_url, $request);
			$page_r = $page_r['content'];
			//print_r($page_r);exit;
			if (!isset($lastPage))
				$lastPage = trim($this->oLinkFeed->ParseStringBy2Tag($page_r, array('<span class="page last">', '>'), '<'));
			
			if ($page == $lastPage)
				$HasNextPage = false;
			
			$nLineStart = 0;
			while (1)
			{
				$LogoUrl = trim($this->oLinkFeed->ParseStringBy2Tag($page_r, array('<td data-column="logo">', '<img src="'), '"', $nLineStart));
				if (!empty($LogoUrl))
					$LogoUrl = 'https:'.$LogoUrl;
				else 
					break;
				$RankInAff = trim($this->oLinkFeed->ParseStringBy2Tag($page_r, '<span class="rating-value">', '<', $nLineStart));
				$detail_page = trim($this->oLinkFeed->ParseStringBy2Tag($page_r, '<a target="_blank" href="', '"', $nLineStart));
				$IdInAff = trim($this->oLinkFeed->ParseStringBy2Tag($detail_page, 'id:', ''));
				echo "IdInAff is $IdInAff\r\n";
				if (empty($IdInAff))
					continue;
				$Name = trim($this->oLinkFeed->ParseStringBy2Tag($page_r, '>', '<', $nLineStart));
				$CommissionExt = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($page_r, '<td data-column="maxPrice">', '</td>', $nLineStart)));
				if (!empty($CommissionExt))
					$CommissionExt = str_replace('up to ', '', $CommissionExt);
				$CommissionExt = str_replace('p', 'RUR', $CommissionExt);
				$CommissionExt = str_replace('&euro;', 'EUR', $CommissionExt);
				
				$LineStart = 0;
				$detail_r = $this->oLinkFeed->GetHttpResult($detail_page, $request);
				$detail_r = $detail_r['content'];
				
				if (stripos($detail_r, 'Register and start earning') !== false)
				{
					echo "cookie is Invalid, retry login...\r\n";
					$this->Login();
					$detail_r = $this->oLinkFeed->GetHttpResult($detail_page, $request);
					$detail_r = $detail_r['content'];
				}
				
				$category_arr = array();
				while (1)
				{
					$category = trim($this->oLinkFeed->ParseStringBy2Tag($detail_r, array('<li class="active" >', '>'), '<', $LineStart));
					if (!empty($category))
						$category_arr[] = $category;
					else 
						break;
				}
				$CategoryExt = implode($category_arr, ',');
				
				$Homepage = trim($this->oLinkFeed->ParseStringBy2Tag($detail_r, '<a target="_blank" href="', '"', $LineStart));
				
				$partnership_str = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($detail_r, '<span id="wmOffers-button-add"', '</span>', $LineStart)));
				if (stripos($partnership_str, 'Get links') !== false)
				{
					$Partnership = 'Active';
					$getLink_Url = trim($this->oLinkFeed->ParseStringBy2Tag($detail_r, '<button data-wmgetlinks="', '"'));
					$link_r = $this->oLinkFeed->GetHttpResult($getLink_Url, $request);
					$AffDefaultUrl = trim($this->oLinkFeed->ParseStringBy2Tag($link_r['content'], array('<textarea', '>'), '{subaccount}'));
				}elseif (stripos($partnership_str, 'Request is sent') !== false)
				{
					$Partnership = 'Pending';
					$AffDefaultUrl = '';
				}elseif (stripos($partnership_str, 'Register and start earning') !== false)
				{
					print_r($detail_r);
					mydie("IdInAff is $IdInAff, cookie is Invalid");
				}else
				{
					$Partnership = 'NoPartnership';
					$AffDefaultUrl = '';
				}
				$TargetCountryExt = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($detail_r, '<h4>Geo targeting:</h4>', '</p>', $LineStart)));
				$desc = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($detail_r, '<h4>Description:</h4>', '<div', $LineStart)));
				/* $StatusInAffRemark = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($detail_r, '<td>Status:</td>', '</span>', $LineStart)));
				if (stripos($StatusInAffRemark, 'Active') !== false)
					$StatusInAff = 'Active';
				else 
					mydie("there is new status: $StatusInAffRemark, $Name $IdInAff"); */
				$Deeplink = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($detail_r, '<td>Deeplink:</td>', '</td>', $LineStart)));
				if ($Deeplink == 'Yes')
					$SupportDeepUrl = 'YES';
				elseif ($Deeplink == 'No')
					$SupportDeepUrl = 'NO';
				else 
					$SupportDeepUrl = 'UNKNOWN';
				$JoinDate = trim(strip_tags($this->oLinkFeed->ParseStringBy2Tag($detail_r, '<td>Start date:</td>', '</td>', $LineStart)));
				$JoinDate = date('Y-m-d H:i:s', strtotime($JoinDate));
				
				$arr_prgm[$IdInAff] = array(
						"Name" => addslashes(trim($Name)),
						"IdInAff" => $IdInAff,
						"AffId" => $this->info["AffId"],
						"Homepage" => addslashes($Homepage),
						"RankInAff" => $RankInAff,
						//"StatusInAffRemark" => $StatusInAffRemark,
						"StatusInAff" => 'Active',                        //'Active','TempOffline','Offline'
						"Partnership" => $Partnership,                        //'NoPartnership','Active','Pending','Declined','Expired','Removed'
						"LastUpdateTime" => date("Y-m-d H:i:s"),
						"MobileFriendly" => 'UNKNOWN',
						"SupportDeepUrl" => $SupportDeepUrl,
						"JoinDate" => $JoinDate,
						"CommissionExt" => addslashes($CommissionExt),
						"CategoryExt" => addslashes($CategoryExt),
						"DetailPage" => $detail_page,
						'TargetCountryExt'=> addslashes($TargetCountryExt),
						"Description" => addslashes($desc),
						"SupportDeepUrl" => $SupportDeepUrl,
						"AffDefaultUrl" => addslashes($AffDefaultUrl),
				);
				//print_r($arr_prgm);
				$program_num++;
				if(count($arr_prgm) >= 100){
					$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
					echo "update NO.$program_num\r\n";
					$arr_prgm = array();
				}
			}
			if(count($arr_prgm)){
				$objProgram->updateProgram($this->info["AffId"], $arr_prgm);
				unset($arr_prgm);
			}
			$page++;
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