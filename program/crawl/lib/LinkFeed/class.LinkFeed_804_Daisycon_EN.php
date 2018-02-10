<?php
require_once 'text_parse_helper.php';

class LinkFeed_804_Daisycon_EN{

	function __construct($aff_id,$oLinkFeed){
        $this->oLinkFeed = $oLinkFeed;
//        $oLinkFeed = new LinkFeed();
        $this->info = $oLinkFeed->getAffById($aff_id);
        $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
        $this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
        $this->getStatus = false;
        if (SID == 'bdg01'){
        	$this->PublisherID = '381734';
        	$this->MediaID = '292456';
        }else{
        	
        }
        $this->headers = array( 'Authorization: Basic ' . base64_encode( $this->info['Account'] . ':' . $this->info['Password'] ) );
        $this->para = array('addheader' => $this->headers);

    }

    function getCouponFeed()
    {
    	$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, "Detail" => array(), );
    	$request = array("AffId" => $this->info["AffId"], "method" => "get", "postdata" => "", );
    	$check_date = date('Y-m-d H:i:s');
    	$request = array(
    			"AffId" => $this->info["AffId"],
    			"method" => "get",
    			"postdata" => '',
    			"addheader" => $this->headers,
    	);
    	$page = 1;
    	while (1)
    	{
    		$links = array();
    		$str_url = "https://services.daisycon.com/publishers/$this->PublisherID/material/promotioncodes?page=$page&per_page=100";
	    	$result = $this->oLinkFeed->GetHttpResult($str_url, $request);
	    	$result = json_decode($result['content'], true);
    		if (!$result)
	    		break;
    		foreach ($result as $v)
    		{
    			if (!$v['subscribed'] && !$v['click_url'])
    				continue;
    			$link = array(
    					"AffId" => $this->info["AffId"],
    					"AffMerchantId" => $v['program_id'],
    					"AffLinkId" => $v['id'],
    					"LinkName" => $v['name'],
    					"LinkDesc" => $v['description'],
    					"LinkStartDate" => $v['start_date'],
    					"LinkEndDate" => $v['end_date'],
    					"LinkPromoType" => 'COUPON',
    					"LinkHtmlCode" => '',
    					"LinkCode" => $v['promotioncode'],
    					"LinkOriginalUrl" => '',
    					"LinkImageUrl" => '',
    					"LinkAffUrl" => '',
    					"DataSource" => '0',
    					"IsDeepLink" => 'UNKNOWN',
    					"Type"       => 'promotion'
    			);
    			$LinkAffUrl = str_replace('#MEDIA_ID#', $this->MediaID, $v['click_url']);
    			$LinkAffUrl = str_replace('//', 'https://', $LinkAffUrl);
    			$link['LinkAffUrl'] = str_replace('&ws=#SUB_ID#', '', $LinkAffUrl);
    			
    			$link['LinkHtmlCode'] = create_link_htmlcode($link);
    			
    			if (empty($link['AffLinkId']) || empty($link['AffMerchantId']) || empty($link['LinkAffUrl']))
    				continue;
    			
    			$links[] = $link;
    			
    		}
    		echo sprintf("program:%s, page:%s, %s coupon(s) found. \n", $v['program_id'], $page, count($links));
    		if(count($links) > 0)
    			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
	    	$page++;
    	}
    	
    	$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'promotion');
    	return $arr_return;
    }
    
    function GetAllLinksByAffId()
    {
    	$check_date = date('Y-m-d H:i:s');
    	$arr_return = array("AffectedCount" => 0, "UpdatedCount" => 0, );
    	$request = array(
    			"AffId" => $this->info["AffId"],
    			"method" => "get",
    			"postdata" => '',
    			"addheader" => $this->headers,
    	);
    	$adgroup_page = 1;
    	while (1)
    	{
    		$adgroup_url = "https://services.daisycon.com/publishers/$this->PublisherID/material/adgroups?page=$adgroup_page&per_page=100";
    		$adgroup_r = $this->oLinkFeed->GetHttpResult($adgroup_url, $request);
    		$adgroup_r = json_decode($adgroup_r['content'], true);
    		//var_dump($adgroup_r);exit;
    		if (!$adgroup_r)
    			break;
    		foreach ($adgroup_r as $group)
    		{
    			$page = 1;
				$has_relactionship = false;
    			
    			
				$sub_url = "https://services.daisycon.com/publishers/$this->PublisherID/programs/{$group['program_id']}/subscriptions";
				$sub_r = $this->oLinkFeed->GetHttpResult($sub_url, $request);
				$sub_r = json_decode($sub_r['content'], true);
				if (!$sub_r)
					continue;
    			foreach ($sub_r as $M)
				{
					if ($M['status'] == 'approved' && $this->MediaID == $M['media_id'])
					{
						$has_relactionship = true;
						break;
					}
				}
    			if (!$has_relactionship)
    				continue;
		    	while (1)
		    	{
		    		$links = array();
		    		$ads_url = "https://services.daisycon.com/publishers/$this->PublisherID/material/adgroups/{$group['id']}/ads?page=$page&per_page=100";
		    		$re = $this->oLinkFeed->GetHttpResult($ads_url, $request);
		    		$re = json_decode($re['content'], true);
		    		//var_dump($re);exit;
		    		if (!$re)
		    			break;
		    		foreach ($re as $v)
		    		{
		    			if ($group['id'] != $v['adgroup_id'])
			    		{
			    			print_r($group);
			    			print_r($v);
			    			mydie("die: adgroup is different");
			    		}
		    			
		    			$link = array(
		    					"AffId" => $this->info["AffId"],
		    					"AffMerchantId" => $group['program_id'],
		    					"AffLinkId" => $v['id'],
		    					"LinkName" => $v['type'],
		    					"LinkDesc" => '',
		    					"LinkStartDate" => $v['last_modified'],
		    					"LinkEndDate" => '',
		    					"LinkPromoType" => 'link',
		    					"LinkHtmlCode" => '',
		    					"LinkCode" => '',
		    					"LinkOriginalUrl" => '',
		    					"LinkImageUrl" => '',
		    					"LinkAffUrl" => '',
		    					"DataSource" =>'0',
		    					"IsDeepLink" => 'UNKNOWN',
		    					"Type"       => 'link'
		    			);
		    			if ($v['width'] && $v['height'])
		    				$link['LinkName'] .= ' '.$v['width'] .'x'. $v['height'];
		    				
		    			$LinkHtmlCode = str_replace('#MEDIA_ID#', $this->MediaID, $v['content']);
		    			$LinkHtmlCode = str_replace('//', 'https://', $LinkHtmlCode);
		    			$link['LinkHtmlCode'] = str_replace('&amp;ws=#SUB_ID#', '', $LinkHtmlCode);
		    			
		    			if (isset($v['ad_url']))
		    			{
		    				$LinkImageUrl = str_replace('#MEDIA_ID#', $this->MediaID, $v['ad_url']);
		    				$LinkImageUrl = str_replace('//', 'https://', $LinkImageUrl);
		    				$link['LinkImageUrl'] = str_replace('&ws=#SUB_ID#', '', $LinkImageUrl);
		    			}
		    			
		    			$LinkAffUrl = str_replace('#MEDIA_ID#', $this->MediaID, $v['click_url']);
		    			$LinkAffUrl = str_replace('//', 'https://', $LinkAffUrl);
		    			$link['LinkAffUrl'] = str_replace('&ws=#SUB_ID#', '', $LinkAffUrl);
		    			
		    			if (empty($link['AffLinkId']) || empty($link['AffMerchantId']) || empty($link['LinkAffUrl']))
		    				continue;
		    				
		    			$links[] = $link;
		    		}
		    		echo sprintf("program:%s, page:%s, %s links(s) found. \n", $group['program_id'], $page, count($links));
		    		if(count($links) > 0)
		    			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($links);
		    		$page++;
		    	}
    		}
    		$adgroup_page++;
    	}
    	
    	echo "Get:({$arr_return["AffectedCount"]})\r\n";
    	$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link');
    	return $arr_return;
    }

	function GetProgramFromAff()
	{
		$check_date = date("Y-m-d H:i:s");
		echo "Craw Program start @ {$check_date}\r\n";
		
		$this->GetProgramByApi();
		$this->checkProgramOffline($this->info["AffId"], $check_date);
	
		echo "Craw Program end @ " . date("Y-m-d H:i:s") . "\r\n";
	}
    
	function GetProgramByApi()
	{
		echo "\tGet Program by api start\r\n";
		$objProgram = new ProgramDb();
		$program_num = 0;
		$arr_prgm = array();
		
		$page = 1;
		$per_page = 100;
		$request = array(
				"AffId" => $this->info["AffId"],
				"method" => "get",
				"postdata" => '',
				"addheader" => $this->headers,
		);
		//get categories arr
		$category_url = "https://services.daisycon.com/categories?page=1&per_page=100";
		$category_r = $this->oLinkFeed->GetHttpResult($category_url, $request);
		$category_r = json_decode($category_r['content'], true);
		//var_dump($category_r);exit;
		foreach ($category_r as $cate)
			$Category_arr[$cate['id']] = $cate['name'];
		
		//get countries arr
		$country_url = "https://services.daisycon.com/locales?page=1&per_page=100";
		$country_r = $this->oLinkFeed->GetHttpResult($country_url, $request);
		$country_r = json_decode($country_r['content'], true);
		//var_dump($country_r);exit;
		foreach ($country_r as $coun)
			$country_arr[$coun['id']] = substr($coun['code'], -2);
		while (1)
		{
			$page_url = "https://services.daisycon.com/publishers/$this->PublisherID/programs?page=$page&per_page=$per_page";
			$re = $this->oLinkFeed->GetHttpResult($page_url, $request);
			$re = json_decode($re['content'], true);
			//var_dump($re);exit;
			if (!$re)
				break;
			foreach ($re as $v)
			{
				$strMerID = $v['id'];
				if (!$strMerID)
					continue;

				$Partnership = 'NoPartnership';
				$sub_url = "https://services.daisycon.com/publishers/$this->PublisherID/programs/$strMerID/subscriptions";
				$sub_r = $this->oLinkFeed->GetHttpResult($sub_url, $request);
				$sub_r = json_decode($sub_r['content'], true);
				//var_dump($sub_r);exit;
				if (!$sub_r)
					continue;
				foreach ($sub_r as $M)
				{
					if ($M['status'] == 'approved' && $this->MediaID == $M['media_id'])
					{
						$Partnership = 'Active';
						break;
					}
				}
				
				$StatusInAffRemark = $v['status'];
				if ($StatusInAffRemark == 'active')
					$StatusInAff = 'Active';
				else 
					mydie("\r\n New status: $StatusInAffRemark");
				//Description
				$desc = trim($v['descriptions'][0]['description']);
				//SupportDeepUrl
				if ($v['deeplink'] == 'true')
					$SupportDeepUrl = 'YES';
				else 
					$SupportDeepUrl = 'NO';
				//AffDefaultUrl
				$AffDefaultUrl = str_replace('#MEDIA_ID#', $this->MediaID, trim($v['url']));
				$AffDefaultUrl = str_replace('&ws=#SUB_ID#', '', $AffDefaultUrl);
				if (substr($AffDefaultUrl, 0, 4) != 'http')
					$AffDefaultUrl = 'https:'.$AffDefaultUrl;
				//LogoUrl
				$LogoUrl = addslashes(trim($v['logo']));
				if (substr($LogoUrl, 0, 4) != 'http')
					$LogoUrl = 'https:'.$LogoUrl;
				//CommissionExt
				if ($v['commission']['min_ratio'] && $v['commission']['max_ratio'])
				{
					$CommissionExt = 'Percent:'.$v['commission']['min_ratio'].'%-'.$v['commission']['max_ratio'].'%';
					if ($v['commission']['min_fixed'] && $v['commission']['max_fixed'])
						$CommissionExt .= '|Amount:'.$v['commission']['min_fixed'].'€-'.$v['commission']['max_fixed'].'€';
				}else if ($v['commission']['min_fixed'] && $v['commission']['max_fixed'])
					$CommissionExt = 'Amount:'.$v['commission']['min_fixed'].'€-'.$v['commission']['max_fixed'].'€';
				$CommissionExt .= '|cpc:'.$v['commission']['min_cpc'].'€-'.$v['commission']['max_cpc'].'€';
				//Category
				$Category = array();
				foreach ($v['category_ids'] as $ca)
				{
					if (!isset($Category_arr[$ca]))
						mydie("die: new categoryID is $ca");
					else
						$Category[] = $Category_arr[$ca];
				}
				$CategoryExt = implode(',', $Category);
				//country
				$country = array();
				foreach ($v['supply_locale_ids'] as $co)
				{
					if (!isset($country_arr[$co]))
						mydie("die: new CountryID is $co");
					else
						$country[] = $country_arr[$co];
				}
				$TargetCountryExt = implode(',', $country);
				
				$arr_prgm[$strMerID] = array(
						"Name" => addslashes($v['name']),
						"AffId" => $this->info["AffId"],
						"IdInAff" => $strMerID,
						"JoinDate" => parse_time_str($v['startdate']),
						"StatusInAffRemark" => $StatusInAffRemark,
						"StatusInAff" => $StatusInAff,                        //'Active','TempOffline','Offline'
						"Partnership" => $Partnership,                        //'NoPartnership','Active','Pending','Declined','Expired','WeDeclined'
						"Description" => addslashes($desc),
						"Homepage" => addslashes(trim($v['display_url'])),
						"CookieTime" => $v['tracking_duration'],
						"TargetCountryExt" => $TargetCountryExt,
						//"TermAndCondition" => addslashes($TermAndCondition),
						"SupportDeepUrl" => $SupportDeepUrl,
						"LastUpdateTime" => date("Y-m-d H:i:s"),
						"DetailPage" => "https://my.daisycon.com/publisher/campaigns/campaign-detail/$strMerID",
						"AffDefaultUrl" => addslashes($AffDefaultUrl),
						"CommissionExt" => addslashes($CommissionExt),
						"CategoryExt" => addslashes($CategoryExt),
						"LogoUrl" => $LogoUrl,
				);
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
			$page++;
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