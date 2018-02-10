<?php
class LinkFeed_10000_Amazon_US
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
		
		//step 2,get all exists merchant
		$arrAllExistsMerchants = $this->oLinkFeed->GetAllExistsAffMerIDForCheckByAffID($this->info["AffId"]);

		$arr_update = array(
				"AffMerchantId" => $this->info["AffId"],
				"AffId" => $this->info["AffId"],
				"MerchantName" => $this->info["AffName"],
				"MerchantEPC30d" => -1,
				"MerchantEPC" => -1,
				"MerchantStatus" => "approval",
				"MerchantRemark" => "",
				"MerchantCountry" => "US",
			);

		$this->oLinkFeed->fixEnocding($this->info,$arr_update,"merchant");
		if($this->oLinkFeed->UpdateMerchantToDB($arr_update,$arrAllExistsMerchants)) $arr_return["UpdatedCount"] ++;
		$arr_return["AffectedCount"] ++;
		
		$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateAllExistsAffMerIDButCannotFetched($this->info["AffId"], $arrAllExistsMerchants);
		return $arr_return;
	}

	function filterParas($_paras,$_arr_para_to_filter)
	{
		$filter = array();
		foreach($_arr_para_to_filter as $para) $filter[$para] = 1;
		$arr_para = explode("&",$_paras);
		foreach($arr_para as $k => $_para)
		{
			$pair = explode("=",$_para);
			if(sizeof($pair) != 2) continue;
			if(isset($filter[$pair[0]])) continue;
			unset($arr_para[$k]);
		}
		sort($arr_para);
		return implode("&",$arr_para);
	}
	
	function getUniqueAmazonUrl($url)
	{
		//http://www.amazon.com/gp/feature.html/ref=amb_link_354706362_4?
		//ie=UTF8&docId=1000638991&pf_rd_m=ATVPDKIKX0DER&pf_rd_s=center-12&pf_rd_r=0JTASS0P5PZ11KM3W7EW&pf_rd_t=101&pf_rd_p=1284043362&pf_rd_i=52129011

		$pattern = "|(/gp/feature.html/ref=.*\\?)(.*pf_rd_m=.*)|";
		if(!preg_match($pattern,$url,$matches)) return false;
		$url_part_1 = $matches[1];
		$url_part_2 = $matches[2];
		return $url_part_1 . $this->filterParas($url_part_2,array("ie"));
	}
	
	function GetAllLinksFromAffByMerID($merinfo)
	{
	    $check_date = date('Y-m-d H:i:s');
		$arr_return = array("AffectedCount" => 0,"UpdatedCount" => 0,);
		
		$strUrl = "http://www.amazon.com/Sales-Grocery/b/ref=sv_gro_7?ie=UTF8&node=52129011"; 
		$r = $this->oLinkFeed->GetHttpResult($strUrl);
		$result = $r["content"];
		
		$all_a_count = preg_match_all("|<a [^>]+>.*</a>|U","$result",$matches_a,PREG_PATTERN_ORDER);
		if($all_a_count < 20) mydie("die: only $all_a_count href matched\n");
		
		$arr_a_temp = array();
		foreach($matches_a[0] as $tag_a)
		{
			if(preg_match("|href=\\\"(.*)\\\".*>(.*)</a>|U",$tag_a,$matches))
			{
				$arr_a_temp[] = array(
					"href" => $matches[1],
					"content" => $matches[2],
				);
			}
		}

		$all_url = array();
		foreach($arr_a_temp as $_a)
		{
			$docId = $this->oLinkFeed->ParseStringBy2Tag($_a["href"],'docId=','&');
			if($docId === false) continue;
			$all_url[$docId] = $_a;
		}
		
		$arrToUpdate = array();
		foreach($all_url as $docId => $_a)
		{
			$strUrl = "http://www.amazon.com" . $_a["href"];
			$r = $this->oLinkFeed->GetHttpResult($strUrl);
			$result = $r["content"];
			
			$strLineStart = 'class="amabot_center"';
			$nLineStart = stripos($result,$strLineStart);
			if($nLineStart === false) continue;
			
			$link_id = $docId;
			
			//<h2>Eight O'Clock Coffee: Save an extra 40%</h2>
			$link_name = $this->oLinkFeed->ParseStringBy2Tag($result, '<h2>', '</h2>', $nLineStart);
			if($link_name === false) mydie("die: link_name not found\n");
			
			$link_desc = $this->oLinkFeed->ParseStringBy2Tag($result, '<p>', '</p>', $nLineStart);
			if($link_desc === false) mydie("die: link_desc not found\n");
			
			//try to get end_date
			$link_end_date = $this->oLinkFeed->ParseStringBy2Tag($link_desc,'valid through ','.');
			if($link_end_date === false)
			{
				echo "warning: end date not found\n";
				$link_end_date = "0000-00-00";
			}
			
			$link_code = $this->oLinkFeed->ParseStringBy2Tag($link_desc,'<b>','</b>');
			if($link_code === false) $link_code = "";
			
			//$link_desc = strip_tags($link_desc) . " Please note: use this URL as Deep URL.";
			$link_desc = strip_tags($link_desc);
			
			$promo_type = $link_code ? 'coupon' : "N/A";
			if ($promo_type != 'coupon') $promo_type = $this->oLinkFeed->getPromoTypeByLinkContent($link_name . ' ' . $link_desc);
			
			$link_desc = html_entity_decode($link_desc);
			$code = "";
			if(preg_match("/enter code ([^ ]+) at checkout/i",$_link_from["LinkDesc"],$matches))
			{
				$code = $matches[1];
			}
			elseif(preg_match("/when you check out: ([^ ]+)\\./i",$_link_from["LinkDesc"],$matches))
			{
				$code = $matches[1];
			}
			elseif(preg_match("/when you enter code ([^ ]+)\\./i",$_link_from["LinkDesc"],$matches))
			{
				$code = $matches[1];
			}
			
			if($code == "")
			{
				echo "warning: cannot get code from desc: $link_desc\n";
				continue;
			}
			
			$arr_one_link = array(
				"AffId" => $this->info["AffId"],
				"AffMerchantId" => $merinfo["AffMerchantId"],
				"AffLinkId" => $link_id,
				"LinkName" => html_entity_decode($link_name),
				"LinkDesc" => html_entity_decode($link_desc),
				"LinkStartDate" => date("Y-m-d"),
				"LinkEndDate" => $link_end_date,
				"LinkPromoType" => $promo_type,
				"LinkHtmlCode" => $strUrl,
				"LinkOriginalUrl" => "",
				"LinkImageUrl" => "",
			    "Type"       => 'link'
			);
			$this->oLinkFeed->fixEnocding($this->info,$arr_one_link,"link");
			$arrToUpdate[] = $arr_one_link;
			$arr_return["AffectedCount"] ++;
		}
		
		if(sizeof($arrToUpdate) > 0)
		{
			$arr_return["UpdatedCount"] += $this->oLinkFeed->UpdateLinkToDB($arrToUpdate);
			$arrToUpdate = array();
		}
		$this->oLinkFeed->checkLinkExists($this->info["AffId"], $check_date, 'link', $merinfo['IdInAff']);
		return $arr_return;
	}
}
?>

