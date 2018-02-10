<?php
class LinkFeed_25_Buy_at
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
	}
	
	function GetMerchantListFromAff()
	{
		$arr_return = array("AffectedCount" => 0,"UpdatedCount" => 0,);
		$request = array("AffId" => $this->info["AffId"],"method" => "post","postdata" => "",);
		
		//step 1,login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);

		//step 2,get all exists merchant
		$arrAllExistsMerchants = $this->oLinkFeed->GetAllExistsAffMerIDForCheckByAffID($this->info["AffId"]);

		echo " Get all merchants  <br>\n";

		$strUrl = "https://users.buy.at/ma/index.php/affiliateProgrammes/programmes/perpage/-1";
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];

		//parse HTML
		$nLineStart = 0;
		$strLineStart = '<caption>Programmes: <em>Status: All</em></caption>';
		if($this->oLinkFeed->str_seek($result,$strLineStart,$nLineStart,$seek_result))
		{
			$nLineStart = $seek_result["pos"];
		}
		else mydie("die: $strLineStart not found\n");
		
		$strLineStart = '<tbody>';
		if($this->oLinkFeed->str_seek($result,$strLineStart,$nLineStart,$seek_result))
		{
			$nLineStart = $seek_result["pos"];
		}
		else mydie("die: $strLineStart not found\n");

		/*
<tr>
0<td class="textdata"><a href="/ma/index.php/affiliateProgrammes/programmeDetails/id/1244"><img src="https://b1.perfb.com/logo.php?id=1244"></a></td>
1<td class="textdata"><a href="/ma/index.php/affiliateProgrammes/programmeDetails/id/1244">Aftershock</a></td>
2<td class="textdata"><a href="http://femalefashion.at/couponsnapshot" target="_blank">femalefashion.at/couponsnapshot</a></td>
3<td class="textdata">30 days</td>
4<td class="textdata">Retail</td>
5<td class="textdata">7% - 12%</td>
6<td class="textdata"><a class="approved" href="/ma/index.php/affiliateProgrammes/programmeDetails/id/1244">Approved</a></td>
7<td class="textdata"><span title="The programme is fully live">Fully live</span></td>
8<td>UK</td>
9<td><a href="/ma/index.php/affiliateCreative/productFeeds"><img alt="Has Feed" src="/ma/images/icons/accept.png"></a></td>
10<td><a href="/ma/index.php/affiliateVoucherCodes/list/page/1/prog_id/1244/filter_status/y/perpage/35"><img alt="Has Offer Codes" src="/ma/images/icons/accept.png"></a></td>
11<td class="textdata">aftershock</td>
12<td><a href="/ma/index.php/affiliateCreative/creativeGraphics/prog_id/1244/creative_type_id/1/creativegroup/0/dimension/0">Get Banners</a></td>
13<td></td>
14<td><a href="/ma/index.php/affiliateProgrammes/unsubscribe/prog_id/1244">Unsubscribe</a></td>
</tr>
		*/
		while($nLineStart >= 0)
		{
			$str_tr = $this->oLinkFeed->ParseStringBy2Tag($result,array('<tr','>'),'</tr>',$nLineStart);
			if($str_tr === false) break;
			
			$arr_td = $this->oLinkFeed->ParseStringBy2TagToArray($str_tr,array('<td','>'),'</td>');
			if(sizeof($arr_td) != 15) break;
			
			$strMerID = $this->oLinkFeed->ParseStringBy2Tag($arr_td[0],'https://b1.perfb.com/logo.php?id=','">');
			if($strMerID === false)
			{
				echo "waring: merchant id not found\n";
				break;
			}

			$url_start_pattern = array('href=','id/'.$strMerID.'">');
			$strMerName = $this->oLinkFeed->ParseStringBy2Tag($arr_td[1],$url_start_pattern,'</a>');
			if($strMerName === false)
			{
				echo "waring: merchant name not found\n";
				break;
			}
			$strMerName = html_entity_decode(trim($strMerName));

			//Status
			$strStatus = $this->oLinkFeed->ParseStringBy2Tag($arr_td[6],$url_start_pattern,'</a>');
			$strStatus = trim($strStatus);

			if (stripos($strStatus, 'Approved') !== false) $strStatus = 'approval';
			elseif (stripos($strStatus, 'Rejected') !== false) $strStatus = 'declined';
			elseif (stripos($strStatus, 'Pending') !== false) $strStatus = 'pending';
			elseif (stripos($strStatus, 'Apply') !== false) $strStatus = 'not apply';
			elseif (stripos($strStatus, 'Join') !== false) $strStatus = 'not apply';
			elseif ($strStatus == '') $strStatus = 'siteclosed';
			else mydie("die: unknown Status: $strStatus");
			
			/*$strRegion = trim($arr_td[8]);
			if($strRegion) $strMerName .= ' ('.$strRegion.')';*/

			$arr_return["AffectedCount"] ++;
			$arr_update = array(
				"AffMerchantId" => $strMerID,
				"AffId" => $this->info["AffId"],
				"MerchantName" => html_entity_decode($strMerName),
				"MerchantEPC30d" => "-1",
				"MerchantEPC" => "-1",
				"MerchantStatus" => $strStatus,
				"MerchantRemark" => "",
				"MerchantCountry" => $this->oLinkFeed->GetCountryCodeByStr(""),
			);
			$this->oLinkFeed->fixEnocding($this->info,$arr_update,"merchant");
			if($this->oLinkFeed->UpdateMerchantToDB($arr_update,$arrAllExistsMerchants)) $arr_return["UpdatedCount"] ++;
		}

		$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateAllExistsAffMerIDButCannotFetched($this->info["AffId"], $arrAllExistsMerchants);
		return $arr_return;
	}
	
	function getCouponFeed()
	{
		$arr_return = array(
			"AffectedCount" => 0,
			"UpdatedCount" => 0,
			"Detail" => array(),
		);
		
		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "post",
			"postdata" => "", 
		);
		
		if($this->debug) print "Getting CouponFeed <br>\n";

		$title = '"Status","Programme","Description","Discount","Offer Code","URL","Start","End","Programme Status","Added","Creative","SMS Code"';
		$cache_file = $this->oLinkFeed->fileCacheGetFilePath($this->info["AffId"],"feed.dat","cache_feed");
		if(!$this->oLinkFeed->fileCacheIsCached($cache_file))
		{
			//login
			$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
			
			//step 1, download coupon feed directly
			if($this->debug) print "Get CouponFeed Data  <br>\n";
			$strUrl = "https://users.buy.at/ma/index.php/affiliateVoucherCodes/list?handle=0&filter_status=y&include_pending=1&include_active=1&include_expired=0&prog_id=0&vertical_id=0&orderby=date_added&dir=desc&format=csv&email=info@couponsnapshot.com&password=1b766f204a41eb76a5079b2a0ff324dc";
			$request["method"] = "get";
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
			$result = $r["content"];
			
			if(stripos($result,$title) === false)
			{
				print_r($r);
				mydie("die: get feed failed, title not found \n");
			}
			
			$this->oLinkFeed->fileCachePut($cache_file,$result);
		}
		if(!file_exists($cache_file)) return $arr_return;		
		
		$all_merchant_name = $this->oLinkFeed->getAllAffMerchant($this->info["AffId"],"","MerchantName");
		
		//Open CSV File
		$arr_title = explode(",",$title);
		foreach($arr_title as $i => $v) $arr_title[$i] = trim($v,'"');
		$col_count = sizeof($arr_title);
		
		$fhandle = fopen($cache_file, 'r');
		$arrToUpdate = array();
		while($line = fgetcsv($fhandle, 50000))
		{
			//$title = '"Status","Programme","Description","Discount","Offer Code","URL","Start","End","Programme Status","Added","Creative","SMS Code",""';
			if($line[1] == '' || $line[1] == 'Programme') continue;
			
			if(sizeof($line) < $col_count)
			{
				echo "warning: invalid line found: " . implode(",",$line) . "\n";
				continue;
			}
			
			$row = array();
			foreach($arr_title as $i => $title) $row[$title] = $line[$i];			
			
			$aff_mer_name  = trim($row["Programme"]);
			if($aff_mer_name == '') continue;
			
			$start_date  = trim($row["Start"]);
			$end_date  = trim($row["End"]);
			$couponcode = trim($row["Offer Code"]);
			$link_desc  = trim($row["Description"]);
			$link_name = $strDiscount = trim($row["Discount"]);
			$html_code = trim($row["URL"]);
			$banner_code = trim($row["Creative"]);	
			
			//http://finetea.at/couponsnapshot?CTY=55&CID=2932&DURL=http://www.whittard.com
			if(preg_match("/CID=([^&]*)/i",$html_code,$matches))
			{
				$link_id = "code_" . $matches[1];	
			}
			else mydie("die: unknown destination url format: $html_code \n");
			
			if(!isset($all_merchant_name[$aff_mer_name])) continue;
			$aff_mer_id = $all_merchant_name[$aff_mer_name]["AffMerchantId"];
			
			if ($strDiscount != '') $link_desc  .= '. Discount Detail: ' . $strDiscount;
			if ($couponcode != '') $link_desc  .= '. Voucher Code: ' . $couponcode;
			
			if ($banner_code != '') $html_code = $banner_code;
			
			$promo_type = $this->oLinkFeed->getPromoTypeByLinkContent($link_name . ' ' . $link_desc . ' ' . $html_code);
			
			$arr_one_link = array(
				"AffId" => $this->info["AffId"],
				"AffMerchantId" => $aff_mer_id,
				"AffLinkId" => $link_id,
				"LinkName" => $link_name,
				"LinkDesc" => $link_desc,
				"LinkStartDate" => $start_date,
				"LinkEndDate" => $end_date,
				"LinkPromoType" => $promo_type,
				"LinkHtmlCode" => $html_code,
				"LinkOriginalUrl" => "",
				"LinkImageUrl" => "",
				"LinkAffUrl" => "",
				"DataSource" => "22",
			);			
			
			$this->oLinkFeed->fixEnocding($this->info,$arr_one_link,"feed");
			$arrToUpdate[] = $arr_one_link;
			$arr_return["AffectedCount"] ++;
			if(!isset($arr_return["Detail"][$aff_mer_id]["AffectedCount"])) $arr_return["Detail"][$aff_mer_id]["AffectedCount"] = 0;
			$arr_return["Detail"][$aff_mer_id]["AffectedCount"] ++;

			if(sizeof($arrToUpdate) > 100)
			{
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($arrToUpdate);
				$arrToUpdate = array();
			}
		}
		fclose($fhandle);
		
		if(sizeof($arrToUpdate) > 0)
		{
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($arrToUpdate);
			$arrToUpdate = array();
		}
		return $arr_return;
	}
	
	function getCouponFeed_old()
	{
		$arr_return = array("AffectedCount" => 0,"UpdatedCount" => 0,"Detail" => array(),);
		$request = array("AffId" => $this->info["AffId"],"method" => "post","postdata" => "",);

		$all_merchant_name = array();
		$all_merchant = $this->oLinkFeed->getAllAffMerchant($this->info["AffId"]);
		foreach($all_merchant as $_mer_id => $_mer_info)
		{
			$mer_name = $_mer_info["MerchantName"];
			if(preg_match("/(.*) \\(.*\\)$/",$mer_name,$matches))
			{
				$mer_name = $matches[1];
				$all_merchant_name[$mer_name] = $_mer_id;
			}
		}
		
		//step 1,login
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);
		
		$strUrl = "https://users.buy.at/ma/index.php/affiliateVoucherCodes/list/perpage/-1";
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];
		
		//parse HTML
		$nLineStart = 0;
		
		/*
		$str_mer_list = $this->oLinkFeed->ParseStringBy2Tag($result,array('<select name="prog_id" id="prog_id"','>'),'</select>',$nLineStart);
		if($str_mer_list === false) mydie("die: merchant list not found!\n");
		
		//parse merchant list
		$arr_merchant_in_page = array();
		$nLineStartMerchant = 0;
		while(1)
		{
			$str_mer_id = $this->oLinkFeed->ParseStringBy2Tag($str_mer_list,'value="','"',$nLineStartMerchant);
			if($str_mer_id === false) break;
			$str_mer_name = $this->oLinkFeed->ParseStringBy2Tag($str_mer_list,'>','</option>',$nLineStartMerchant);
			if($str_mer_name === false) break;
			$str_mer_name = html_entity_decode(trim($str_mer_name));
			$arr_merchant_in_page[$str_mer_name] = $str_mer_id;
		}
		
		if(sizeof($arr_merchant_in_page) == 0) mydie("die: merchant list not found!\n");;
		*/
		
		//continue to parse content table
		$strLineStart = '<caption>Offer Codes: <em>Upcoming, Active</em></caption>';
		if($this->oLinkFeed->str_seek($result,$strLineStart,$nLineStart,$seek_result))
		{
			$nLineStart = $seek_result["pos"];
		}
		else mydie("die: $strLineStart not found\n");
		
		$strLineStart = '<tbody>';
		if($this->oLinkFeed->str_seek($result,$strLineStart,$nLineStart,$seek_result))
		{
			$nLineStart = $seek_result["pos"];
		}
		else mydie("die: $strLineStart not found\n");
		
/*
<tr>
0<td class="textdata"><em class="pending">Upcoming</em>          </td>
1<td class="textdata">            Terra Plana          </td>
2<td class="textdata">           Buy One Get One Half Price!          </td>
3<td class="textdata">            50% off 2nd pair          </td>
4<td class="textdata">          <span title="You must be approved on this programme to see this offer code">XXXXX</span>          </td>
5<td class="textdata">           <a onclick="return!window.open(this.href)" href="http://buy-ethicalfashion.at/couponsnapshot?CTY=55&amp;CID=1402&amp;DURL=http://www.terraplana.com/index.php?currency=USD">http://buy-ethicalfashion.at/couponsnapshot?CTY=55&CID=1402&DURL=http://www.terraplana.com/index.php?currency=USD</a>          </td>
6<td class="textdata">            26-11-2010 00:00          </td>
7<td class="textdata">            30-12-2010 00:00          </td>
8<td class="textdata">            <em class="approved">Approved</em>          </td>
9<td class="textdata">            24-11-2010 16:01          </td>
10<td class="textdata">            0          </td>
</tr>
*/
		$arrToUpdate = array();
		while($nLineStart >= 0)
		{
			$str_tr = $this->oLinkFeed->ParseStringBy2Tag($result,array('<tr','>'),'</tr>',$nLineStart);
			if($str_tr === false) break;
			
			$arr_td = $this->oLinkFeed->ParseStringBy2TagToArray($str_tr,array('<td','>'),'</td>');
			if(sizeof($arr_td) != 11) break;
			
			$strLinkStatus = $this->oLinkFeed->ParseStringBy2Tag($arr_td[0],'/<em[^>]*>/i','</em>');
			if($strLinkStatus === false)
			{
				echo "waring: strLinkStatus not found\n";
				break;
			}
			$strLinkStatus = trim($strLinkStatus);
			//we only need active coupon
			if($strLinkStatus != "Active") continue;

			$strMerName = strip_tags($arr_td[1]);
			$strMerName = html_entity_decode(trim($strMerName));
			
			if(!isset($all_merchant_name[$strMerName]))
			{
				echo "warning: merchant name not found: $strMerName\n";
				continue;
			}
			$aff_mer_id = $all_merchant_name[$strMerName];
			
			$link_desc = $arr_td[2];
			$link_desc = html_entity_decode(trim($link_desc));
			
			$link_name = $arr_td[3];
			$link_name = html_entity_decode(trim($link_name));
			
			$link_code = strip_tags($arr_td[4]);
			$link_code = html_entity_decode(trim($link_code));
			
			$link_desc = $link_name .'. '.$link_desc. '. Voucher Code: '.$link_code;
			
			$html_code = $arr_td[5];
			
			$start_date = $arr_td[6];
			$end_date = $arr_td[7];
			
			$promo_type = 'coupon';
			
			$link_id = $this->oLinkFeed->ParseStringBy2Tag($html_code,'&amp;CID=','&amp;');
			if($link_id === false) mydie("die: link_id not found in html_code: $html_code \n");
			$link_id = 'code_' . $link_id;
			
			$arr_one_link = array(
				"AffId" => $this->info["AffId"],
				"AffMerchantId" => $aff_mer_id,
				"AffLinkId" => $link_id,
				"LinkName" => $link_name,
				"LinkDesc" => $link_desc,
				"LinkStartDate" => $start_date,
				"LinkEndDate" => $end_date,
				"LinkPromoType" => $promo_type,
				"LinkHtmlCode" => $html_code,
				"LinkOriginalUrl" => "",
				"LinkImageUrl" => "",
				"Country" => "",
			);
			
			$this->oLinkFeed->fixEnocding($this->info,$arr_one_link,"feed");
			$arrToUpdate[] = $arr_one_link;
			$arr_return["AffectedCount"] ++;
			if(!isset($arr_return["Detail"][$aff_mer_id]["AffectedCount"])) $arr_return["Detail"][$aff_mer_id]["AffectedCount"] = 0;
			$arr_return["Detail"][$aff_mer_id]["AffectedCount"] ++;

			if(sizeof($arrToUpdate) > 100)
			{
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($arrToUpdate);
				$arrToUpdate = array();
			}
		}
		
		if(sizeof($arrToUpdate) > 0)
		{
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($arrToUpdate);
			$arrToUpdate = array();
		}
		return $arr_return;
	}

	function GetLinksByCreativeType($aff_mer_id,$creative_type_id)
	{
		$arr_return = array();
		$request = array("AffId" => $this->info["AffId"],"method" => "post","postdata" => "",);
		
		$arr_config = array(
			1 => array("pageName" => "creativeGraphics","pageType" => "isCreativeGraphics","dimension" => 0,"link_prefix" => "banner_"),
			2 => array("pageName" => "creativeGraphics","pageType" => "isCreativeGraphics","dimension" => 0,"link_prefix" => "banner_"),
			3 => array("pageName" => "creativeText","pageType" => "isCreativeText","link_prefix" => "txt_"),
			4 => array("pageName" => "creativeText","pageType" => "isCreativeText","link_prefix" => "txt_"),
		);
		
		if(!isset($arr_config[$creative_type_id])) mydie("die: creative_type_id $creative_type_id not defined.\n");
		$config = $arr_config[$creative_type_id];
		$strUrl = "https://users.buy.at/ma/index.php/affiliateCreative/" . $config["pageName"] . "?pageType=" . $config["pageType"] . "&prog_id=$aff_mer_id" . "&creative_type_id=$creative_type_id" . "&creativegroup=0&customise=Go";
		if(isset($config["dimension"])) $strUrl .= "&dimension=" . $config["dimension"];
		
		if($this->debug) print "Getting " . $config["pageName"] . " standard text links  <br>\n";
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
		$result = $r["content"];
		
		$nLineStart = 0;
		//check merchant name
		$aff_mer_name = $this->oLinkFeed->ParseStringBy2Tag($result, '<option value="'.$aff_mer_id.'" selected="selected">', '</option>', $nLineStart);
		if ($aff_mer_name === false) 
		{
			echo "warning: check merchant name for merchant $aff_mer_id failed\n";
			return array();
		}
		else
		{
			echo "check merchant name for merchant $aff_mer_id passed: $aff_mer_name\n";
		}

		$strLineStart = '<div class="creative">';
		while ($nLineStart >= 0)
		{
			$nLineStart = stripos($result,$strLineStart,$nLineStart);
			if ($nLineStart === false) break;

			if($creative_type_id == 3 || $creative_type_id == 4)
			{
				$link_name = $this->oLinkFeed->ParseStringBy2Tag($result, array('<a ','>'),'</a>', $nLineStart);
				if ($link_name === false) break;
				$link_name = html_entity_decode(trim($link_name));
			}
			else
			{
				$link_image = $this->oLinkFeed->ParseStringBy2Tag($result, array('<a ','>'),'</a>', $nLineStart);
				if ($link_image === false) break;
				if(!preg_match("/<img/",$link_image))
				{
					echo "warning: image not found\n";
					break;
				}
			}
			
			//src_lastupdate
			$src_lastupdate = $this->oLinkFeed->ParseStringBy2Tag($result, array('<li>Date Added', '<strong>'), '</strong>', $nLineStart);
			if ($src_lastupdate === false) break;

			$html_code = $this->oLinkFeed->ParseStringBy2Tag($result, '<textarea rows="4" readonly=true cols="90" >', '</textarea>', $nLineStart);
			if ($html_code === false) break;
			$html_code = html_entity_decode($html_code);

			//get deal ID
			$link_id = $this->oLinkFeed->ParseStringBy2Tag($html_code, 'CID=', '"');
			if ($link_id === false) break;
			
			if($creative_type_id == 1 || $creative_type_id == 2)
			{
				$link_name = "Banner $link_id ";
			}
			$link_desc = $link_name;
			$link_desc .= ". Link Add Date: $src_lastupdate";
			
			$link_id = $config["link_prefix"] . $link_id;

			$start_date = date("Y-m-d");
			$end_date = '0000-00-00';

			$promo_type = $this->oLinkFeed->getPromoTypeByLinkContent($link_name . ' ' . $link_desc . ' ' . $html_code);
			
			$arr_one_link = array
			(
				"AffId" => $this->info["AffId"],
				"AffMerchantId" => $aff_mer_id,
				"AffLinkId" => $link_id,
				"LinkName" => $link_name,
				"LinkDesc" => $link_desc,
				"LinkStartDate" => $start_date,
				"LinkEndDate" => $end_date,
				"LinkPromoType" => $promo_type,
				"LinkHtmlCode" => $html_code,
				"LinkOriginalUrl" => "",
				"LinkImageUrl" => "",
			);
			$this->oLinkFeed->fixEnocding($this->info,$arr_one_link,"link");
			$arr_return[] = $arr_one_link;
		}
		return $arr_return;
	}
	
	function GetAllLinksFromAffByMerID($merinfo)
	{
		$aff_id = $this->info["AffId"];
		$AffMerchantId = $merinfo["AffMerchantId"];
		
		$arr_return = array("AffectedCount" => 0,"UpdatedCount" => 0,);
		$request = array("AffId" => $this->info["AffId"],"method" => "post","postdata" => "",);
		
		$this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);

		$all_creative_type_id = array(1,2,3,4);
		
		foreach($all_creative_type_id as $creative_type_id)
		{
			$arrToUpdate = $this->GetLinksByCreativeType($AffMerchantId,$creative_type_id);
			if(sizeof($arrToUpdate) > 0)
			{
				$arr_return["AffectedCount"] += sizeof($arrToUpdate);
				$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($arrToUpdate);
			}
		}
		
		return $arr_return;
	}
}
?>
