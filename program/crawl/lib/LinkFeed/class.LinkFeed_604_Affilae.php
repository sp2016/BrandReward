<?php

require_once 'text_parse_helper.php';
require_once 'xml2array.php';


class LinkFeed_604_Affilae
{
	function __construct($aff_id, $oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->getStatus = false;
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		if(SID == 'bdg01'){
			$this->profileID = '584b94fe665e8815378c21aa';
			$this->user_code = '584b930a665e88c3358c129c';
			$this->API_key = '2ed97cd6d26ad8b49c6cc3f2605a1d82';
			$this->Authorization = 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1aWQiOiI1ODRiOTMwYTY2NWU4OGMzMzU4YzEyOWMiLCJpYXQiOjE0ODEzNDc4NTB9.fBDBSI-9vRk5CZStx3GO_eSHeklqyleepUkNvrq0Nm4';
		}else{
			$this->profileID = '58d4b05ae8faceb33e8b4574';
			$this->user_code = '58d0fd1193b58c3d422a51bb';
			$this->API_key = 'd40826f52897177e89b885ebb0fcc928';
			$this->Authorization = 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1aWQiOiI1OGQwZmQxMTkzYjU4YzNkNDIyYTUxYmIiLCJpYXQiOjE0OTAwOTEyODF9.6SvkI2gn5U4FRO-b9Pb0MDn-HUlVgiHvRNAU2HGQOMM';
		}
	}
	
	function LoginIntoAffService()
	{
		$url = $this->info["AffLoginUrl"];
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => "post",
				"postdata" => $this->info["AffLoginPostString"],
				);
		$arr = $this->oLinkFeed->GetHttpResult($url, $request);
		//print_r($arr);
		if($this->info["AffLoginVerifyString"] && stripos($arr["content"], $this->info["AffLoginVerifyString"]) !== false)
		{
			echo "verify succ: " . $this->info["AffLoginVerifyString"] . "\n";
			return true;
		}
		else
		{
			print_r($arr);
			mydie("verify failed: " . $this->info["AffLoginVerifyString"] . "\n");
		}
		return false;
		
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
		$sql = "select IdInAff,AffDefaultUrl from program where affid=604 and AffDefaultUrl !='' and partnership='active'";
		$approval_arr = $this->oLinkFeed->objMysql->getRows($sql, 'IdInAff');
		//print_r($approval_arr);exit;
		$request = array(
				"AffId" => $this->info["AffId"], 
				"method" => "get",
				"postdata" => '',
				"addheader" => array($this->Authorization)
		);
		//$this->LoginIntoAffService();
		$limit = 100;
		$hasNextPage = true;
		$offset = 0;
		$page = 1;
		while ($hasNextPage)
		{
			$url = "https://v3.affilae.com/publisher/ads.list?affiliateProfile=$this->profileID&limit=$limit&offset=$offset&sort=desc";
			$r = $this->oLinkFeed->GetHttpResult($url,$request);
			$r = json_decode($r['content'], true);
			//var_dump($r);exit;
			$total = $r['count'];
			if ($total <= ($offset + 100))
				$hasNextPage = false;
			
			if($r['statusCode'] == 200)
			{
				foreach ($r['ads']['data'] as $v)
				{
					if (!isset($approval_arr[$v['program']]))
						continue;
					preg_match('/[\d\D]*([#|?]ae={0,1}\d+)/', $approval_arr[$v['program']]['AffDefaultUrl'], $g);
					
					$link = array(
							"AffId" => $this->info["AffId"],
							"AffMerchantId" => $v['program'],
							"AffLinkId" => $v['id'],
							"LinkName" => trim($v['name']),
							"LinkDesc" => (!empty($v['content']))?trim($v['content']):'',
							"LinkStartDate" => str_replace('T', ' ', trim(str_replace('.000Z', '', $v['createdAt']))),
							"LinkEndDate" => '',
							"LinkPromoType" => 'link',
							"LinkHtmlCode" => '',
							"LinkCode" => '',
							"LinkOriginalUrl" => trim($v['url']),
							"LinkImageUrl" => (!empty($v['file']))?trim($v['file']):'',
							"LinkAffUrl" => trim($v['url'].$g[1]),
							"DataSource" => 430,
							"IsDeepLink" => 'UNKNOWN',
							"Type"       => 'link'
					);
					$link['LinkHtmlCode'] = create_link_htmlcode($link);
					if (empty($link['AffMerchantId']) || empty($link['LinkName']) || empty($link['AffLinkId']))
						continue;
					
					if(!empty($link['LinkImageUrl']))
						$link['LinkHtmlCode'] = create_link_htmlcode_image($link);
					else
						$link['LinkHtmlCode'] = create_link_htmlcode($link);
						
					$arr_return["AffectedCount"] ++;
					$links [] = $link;
				}
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
				echo sprintf("page:%s, %s links(s) found. \n", $page, count($links));
				$links = array();
			}
			$offset+=100;
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
		
		//1.login
		echo "Login...\r\n";
		$this->LoginIntoAffService();
		
		//2.get my program affDefaultUrl
		echo "get my program's AffDefaultUrl\r\n";
		$default_url = "https://app.affilae.com/en/publisher/$this->profileID/partnerships";
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => "get",
				"postdata" => ""
		);
		$default_r = $this->oLinkFeed->GetHttpResult($default_url, $request);
		$default_r = $default_r['content'];
		//print_r($default_r);exit;
		$LineStart = 0;
		$default_arr = array();
		$default_r = $this->oLinkFeed->ParseStringBy2Tag($default_r, '<tbody>', '</tbody>');
		while (1)
		{
			$per_program = $this->oLinkFeed->ParseStringBy2Tag($default_r, '<tr>', '</tr>', $LineStart);
			if (!$per_program)
				break;
			$affdefaulturl = $this->oLinkFeed->ParseStringBy2Tag($per_program, '<br><i>', '</i>');
			if (!$affdefaulturl)
				continue;
			$programID = $this->oLinkFeed->ParseStringBy2Tag($per_program, 'messages/contact/', '"');
			$default_arr[$programID] = html_entity_decode($affdefaulturl);
		}
		
		//3.get partnership
		echo "get Partnership\r\n";
		$partner_request = array(
				"AffId" => $this->info["AffId"],
				"method" => "get",
				"postdata" => "",
				"addheader" => array($this->Authorization),
		);							
		$partner_url = "https://v3.affilae.com/publisher/partnerships.list?affiliateProfile=$this->profileID";
		$re = $this->oLinkFeed->GetHttpResult($partner_url, $partner_request);
		$result = json_decode($re['content'], true);
		//var_dump($result);exit;
		if ($result['statusCode'] != 200)
			mydie("die: program partnership cann't crawled, please check the page");
		
		$status = array();
		foreach ($result['partnerships']['data'] as $data)
		{
			if(!isset($status[$data['program']['id']]))
				$status[$data['program']['id']] = array(
						'id' => $data['program']['id'],
						'name' => $data['program']['name'],
						'createdAt' => $data['createdAt'],
						'status' => $data['status']
				);
			elseif ($status[$data['program']['id']]['createdAt'] < $data['createdAt']) 
				$status[$data['program']['id']] = array(
						'id' => $data['program']['id'],
						'name' => $data['program']['name'],
						'createdAt' => $data['createdAt'],
						'status' => $data['status']
				);
			else 
				continue;
		}
		
		$objProgram = new ProgramDb();
		$arr_prgm = array();
		$program_num = 0;
		
		//4.get program form marketplace
		echo "get program form marketplace\r\n";
		$hasNextPage = true;
		$offset = 0;
		$limit = 100;
		while ($hasNextPage)
		{
			$url = "https://v3.affilae.com/marketplace/programs.list?offset=$offset&limit=$limit";
			$re = $this->oLinkFeed->GetHttpResult($url);
			$re = json_decode($re['content'], true);
			//var_dump($re);exit;
			if ($re['statusCode'] != 200)
				mydie("Httpcode ".$re['statusCode']." error! Please check it.");
			
			$total = $re['count'];
			if ($total <= ($offset + 100))
				$hasNextPage = false;
			
			foreach ($re['programs']['data'] as $v)
			{
				$strMerID = $v['id'];
				$strMerName = $v['name'];
				//echo $strMerID."\r\n";
				//CategoryExt
				$categories_arr = array();
				foreach ($v['categories'] as $Category)
				{
					$categories_arr[] = $Category['title_en'];
				}
				$CategoryExt = implode(',', $categories_arr);
				
				//CommissionExt
				$Commissions_arr = array();
				foreach ($v['stats'] as $type => $commission)
				{
					if ($commission['kind'] == 'percent')
					{
						$Commissions_arr[] = $type.':'.($commission['value']/100).'%';
					}elseif ($commission['kind'] == 'fixed')
					{
						$Commissions_arr[] = $type.':'.($commission['value']/100).$commission['currency'];
					}else
					{
						if (!empty($commission['kind']))
							$Commissions_arr[] = $type.':'.($commission['value']/100).$commission['currency'];
					}
				}
				$CommissionExt = implode('|', $Commissions_arr);
				
				if (isset($v['createdAt']))
				{
					$CreateDate = date('Y-m-d H:i:s', strtotime($v['createdAt']));
				}else
				{
					$CreateDate = '';
				}
				
				//TargetCountryExt
				if (isset($v['countries']) && !empty($v['countries']))
				{
					$TargetCountryExt = implode(',', $v['countries']);
				}else
				{
					$TargetCountryExt = '';
				}
				
				if (isset($v['description']))
				{
					$desc = trim($v['description']);
				}else
				{
					$desc = '';
				}
				
				if (isset($v['isActivated']) && $v['isActivated'])
				{
					$StatusInAff = 'Active';
				}else
				{
					$StatusInAff = 'Offline';
				}
				
				$detail_url = 'https://affilae.com/en/affiliate-program-'.$v['slug'];
				
				//Partnership
				if (isset($status[$strMerID]))
				{
					if ($status[$strMerID]['name'] != $strMerName)
					{
						print_r($status[$strMerID]['name'].":".$strMerName."\r\n");
						echo "Warning: programName Different from the json, IdInAff is $strMerID!\r\n";
					}
					
					$StatusInAffRemark = $status[$strMerID]['status'];
					if ($StatusInAffRemark == 'pending')
					{
						$Partnership = 'Pending';
					}elseif ($StatusInAffRemark == 'active')
					{
						$Partnership = 'Active';
					}elseif ($StatusInAffRemark == 'refused by advertiser')
					{
						$Partnership = 'Declined';
					}elseif ($StatusInAffRemark == 'cancelled by advertiser')
					{
						$Partnership = 'NoPartnership';
					}elseif ($StatusInAffRemark== 'cancelled by publisher')
					{
						$Partnership = 'NoPartnership';
					}else
					{
						mydie("New status appeared: $StatusInAffRemark");
					}
				}else 
				{
					$StatusInAffRemark = '';
					$Partnership = 'NoPartnership';
				}
				//AffDefaultUrl
				if (isset($default_arr[$strMerID]))
				{
					$AffDefaultUrl = $default_arr[$strMerID];
					preg_match('/[\d\D]*[#|?](ae={0,1}\d+)/', $AffDefaultUrl, $m);
					$SecondIdInAff = $m[1];
				}else
				{
					$AffDefaultUrl = '';
				}
				
				$arr_prgm[$strMerID] = array(
						"Name" => addslashes($strMerName),
						"AffId" => $this->info["AffId"],
						//"Contacts" => $Contacts,
						"TargetCountryExt" => addslashes($TargetCountryExt),
						"IdInAff" => $strMerID,
						"SecondIdInAff" => isset($SecondIdInAff)?$SecondIdInAff:'',
						"JoinDate" => $CreateDate,
						"RankInAff" => isset($v['advertiserWeight'])?$v['advertiserWeight']:'',
						//"StatusInAffRemark" => $StatusInAffRemark,
						"StatusInAff" => $StatusInAff,                        //'Active','TempOffline','Offline'
						"Partnership" => $Partnership,                        //'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
						"Description" => addslashes($desc),
						"Homepage" => isset($v['url'])?addslashes($v['url']):'',
						"TermAndCondition" => isset($v['terms'])?addslashes($v['terms']):'',
						"LastUpdateTime" => date("Y-m-d H:i:s"),
						"DetailPage" => $detail_url,
						"AffDefaultUrl" => addslashes($AffDefaultUrl),
						"CommissionExt" => addslashes($CommissionExt),
						"CategoryExt" => addslashes(trim($CategoryExt)),
						"LogoUrl" => isset($v['logo'])?addslashes($v['logo']):'',
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
			$offset+=100;
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