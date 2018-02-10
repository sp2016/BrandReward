<?php
class LinkFeed_17_DgmPro_UK
{
	var $info = array(
		"ID" => "17",
		"Name" => "dgmPro UK",
		"IsActive" => "NO",
		"ClassName" => "LinkFeed_17_DgmPro_UK",
		"LastCheckDate" => "1970-01-01",
		'loginurl'	=> 'http://www.dgmpro.com/index.cfm',
		'loginpostdata'	=> "login=Ran.Chen&password=XsVIE8IwBTT9&x=7&y=18",
		'method'	=> 'post',
	);
	
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
	}
	
	function getMerAffIDByURL($strURL)
	{
		return $this->getDgmMerIDByURL($strURL);
	}
	
	function getDgmMerIDByURL($strURL){
		//http://www.dgmpro.com/affiliates/index.cfm?fuseaction=dgmPro.moreinfo&cpid=16128&cmid=906
		// MerID_CampaignID
		
		$strURL = trim($strURL);
		if (substr($strURL, 0, 7) == 'http://'){
			$arrUrl = parse_url($strURL);
			if ($arrUrl['scheme'] == ''){
			}
			else{
				parse_str($arrUrl['query'], $arrQuery);
//				echo $arrUrl['query'];
//				print_r($arrQuery);
				$strMerID = trim($arrQuery['cmid']);
				$strCampaignID = trim($arrQuery['cpid']);
//				echo $strMerID.'_'.$strCampaignID;

				if (($strMerID == '') || ($strCampaignID == '')){
				}
				else{
					return $strMerID.'_'.$strCampaignID;
				}
			}
		}
		else{
		}
		return $strURL;
	}
	
	function GetMerchantListFromAff($bLogined, &$cookie_jar, &$nMerCnt, &$nMerUpdateCnt)
	{
		return $this->GetMerchantListFromDgmPro($bLogined, $cookie_jar, $nMerCnt, $nMerUpdateCnt, $aff_id);
	}

	function GetMerchantListFromDgmPro($bLogined, &$cookie_jar, &$nMerCnt, &$nMerUpdateCnt, $aff_id){
		$aff_id = $this->info["AffId"];

		//login
		$this->oLinkFeed->LoginIntoAffService($aff_id,$this->info);

		$request = array(
			"AffId" => $this->info["AffId"],
			"method" => "post",
			"postdata" => "", 
		);
		
		if (!$bLogined){
			LoginIntoAffService($aff_id, $cookie_jar);
		}

		$nMerCnt = 0;
		$nMerUpdateCnt = 0;

		//get all exists merchant 
		$arrAllExistsMerchants = GetAllExistsAffMerIDForCheckByAffID($aff_id);

		//Step1 get active merchant list
		$Cnt = 0;
		$UpdateCnt = 0;
		
		$nPageNo = 0;
		$bHaveNextPage = true;
		While($bHaveNextPage){
			$strUrl = "http://www.dgmpro.com/affiliates/index.cfm?fuseaction=campaigns.all_campaigns&Start=".($nPageNo * 50 + 1)."&sort_order=0";
			$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
$result = $r["content"];
			print "<br>\n Get Active Merchant List Page: $nPageNo  <br>\n";

			//parse HTML
			$strLineStart = '<td align="left" class="lightblueRow">&nbsp;&nbsp;';

			$nLineStart = 0;
			while ($nLineStart >= 0){
				//print "Process $Cnt  ";
				$nLineStart = stripos($result, $strLineStart, $nLineStart);
				if ($nLineStart === false) {
					break;
				}
				
				// ID 	Name 	EPC 	Status
				//ID
				//CampID
				$strCampID = $this->oLinkFeed->ParseStringBy2Tag($result, 'index.cfm?fuseaction=dgmPro.moreinfo&cpid=', '&cmid', $nLineStart);
				$strCampID = trim($strCampID);

				//MerID
				$strMerID = $this->oLinkFeed->ParseStringBy2Tag($result, 'cmid=', '">', $nLineStart);
				$strMerID = trim($strMerID);

				$strMerID = $strMerID . '_' . $strCampID;

				//name
				$strMerName = $this->oLinkFeed->ParseStringBy2Tag($result, '<td align="left" class="lightblueRow">&nbsp;', '</td>', $nLineStart);
				$strMerName = trim($strMerName);

				if (stripos($strMerName, 'a title="') !== false){
					$nTmpStart = 0;
					$strMerName = $this->oLinkFeed->ParseStringBy2Tag($strMerName, '<a title="', '">', $nTmpStart);
				}

				//EPC
				$strEPC = '';

				//
				$arr_pattern = array();
				for($i=0;$i<7;$i++) $arr_pattern[] = "<td";
				$arr_pattern[] = '<td align="center" class="lightblueRow">';
				//Status
				$strStatus = $this->oLinkFeed->ParseStringBy2Tag($result,$arr_pattern, '</td>', $nLineStart);
				$strStatus = trim($strStatus);

				if (strpos($strStatus, '<font color="orange"><b>Joined</b></font>') !== false){
					$strStatus = 'approval';
				}
				elseif (strpos($strStatus, '<font color="red"><b>Rejected</b></font>') !== false){
					$strStatus = 'declined';
				}
				elseif (strpos($strStatus, '<font color="orange"><b>Pending</b></font>') !== false){
					$strStatus = 'pending';
				}
				elseif (strpos($strStatus, '<input type="checkbox" name="thisCamp_') !== false){
					$strStatus = 'not apply';
				}
				elseif ($strStatus == ''){
					$strStatus = 'siteclosed';
				}
				else{
					echo " unknown Status: $strStatus "; exit;
				}
				$Cnt++;
				//
				$arrAllExistsMerchants[$strMerID] = 1;
				//
				UpdateMerchantToDB($aff_id, $strMerID, $strMerName, $strEPC, $strEPC30d, $strStatus, $UpdateCnt);
			}
			$nLineStart = 0;
			$strHaveNextPage = $this->oLinkFeed->ParseStringBy2Tag($result, array('&lt;&lt; Previous', '|'), 'Next &gt;&gt;', $nLineStart);
			if (strpos($strHaveNextPage, 'href') !== false){
				$bHaveNextPage = true;
			}
			else{
				$bHaveNextPage = false;
			}

			$nPageNo ++;
		}
		$nMerCnt += $Cnt;
		$nMerUpdateCnt += $UpdateCnt;
		echo " Total: $Cnt ; Updated: $UpdateCnt  <br>\n"; 

		//check all exists merchants;
		$UpdateCnt = 0;
		UpdateAllExistsAffMerIDButCannotFetched($aff_id, $arrAllExistsMerchants, $UpdateCnt);
		unset($arrAllExistsMerchants);
		echo "Found Exists Merchants But Cannot get from Aff: $UpdateCnt  <br>\n"; 
	}

	function getCouponFeed($bLogined, &$cookie_jar, &$nOneMerchantCouponCnt, &$nOneMerchantCouponUpdateCnt,$aff_id)
	{
		return $this->GetPromotionsFromDgmPro(true, $cookie_jar, $nOneMerchantCouponCnt, $nOneMerchantCouponUpdateCnt, $aff_id);
	}

	function GetPromotionsFromDgmPro($bLogined, &$cookie_jar, &$couponCnt, &$couponUpdateCnt, $aff_id){
		if (!$bLogined){
			$result = LoginIntoAffService($aff_id, $cookie_jar);
		}

		$couponCnt = 0;
		$couponUpdateCnt = 0;

		$arrAllExistsInternalMerchants = GetAllExistsInternalMerchantWithAffMerIDByAffID($aff_id);
		$arrAllExistsMerInAff = GetAllExistsAffMerIDWithAffMerNameByAffID($aff_id);

		//get promotion page
		//http://www.dgmpro.com/affiliates/index.cfm?fuseaction=promotions.promotions
		$strUrl = "http://www.dgmpro.com/affiliates/index.cfm?fuseaction=promotions.promotions";
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
$result = $r["content"];

		if($this->debug) print "Get promotion page <br>\n";

		$strLineStart = '<tr>'; //
		$nLineStart = stripos($result, '<td class="darkblueRow"', 0);

		while ($nLineStart >= 0){
			if($this->debug) print "Process $couponCnt  ";

			$nLineStart = stripos($result, $strLineStart, $nLineStart);
			if ($nLineStart === false) break;

			$aff_mer_name = $this->oLinkFeed->ParseStringBy2Tag($result, '<td class="lightblueRow">&nbsp;', '</td>', $nLineStart);
			$aff_mer_name = trim($aff_mer_name);
			if($this->debug) print "aff_mer_name $aff_mer_name  <br>\n";
			if ($aff_mer_name === false) break;

			$affPromType =  $this->oLinkFeed->ParseStringBy2Tag($result, '<td class="lightblueRow">&nbsp;', '</td>', $nLineStart);
			if($this->debug) print "affPromType $affPromType  <br>\n";
			if ($affPromType === false) break;
			
			//link name
			$link_name = trim($this->oLinkFeed->ParseStringBy2Tag($result, '<td class="lightblueRow"><strong>&nbsp;', '</strong></td>', $nLineStart));
			if($this->debug) print "link_name $link_name  <br>\n";
			if ($link_name === false) break;

			$link_desc = $this->oLinkFeed->ParseStringBy2Tag($result, '<td class="lightblueRow">&nbsp;', '</td>', $nLineStart);
			if($this->debug) print "link_desc $link_desc  <br>\n";
			if ($link_desc === false) break;

			//get coupon code
			$strVoucherCode = $this->oLinkFeed->ParseStringBy2Tag($result, '<td class="lightblueRow" align="center">', '</td>', $nLineStart);
			if($this->debug) print "strVoucherCode $strVoucherCode  <br>\n";
			if ($strVoucherCode === false) break;
			if ($strVoucherCode == 'n/a'){
				$strVoucherCode = '';
			}
			else{
				$link_name .= ".  Voucher Code: $strVoucherCode";
				$link_desc .= ".  Voucher Code: $strVoucherCode";
			}
			//

			//get link url
			$strGetUrl = $this->oLinkFeed->ParseStringBy2Tag($result, '<td class="lightblueRow" align="center" colspan="2">', '</td>', $nLineStart);
			if($this->debug) print "strGetUrl $strGetUrl  <br>\n";
			if ($strGetUrl === false) break;
			$strGetUrl = trim($strGetUrl);
			if ($strGetUrl == 'n/a'){
				$strGetUrl = '';
			}
			else if (stripos($strGetUrl, 'Join Campaign', 0) > 0){
				$strGetUrl = '';
			}
			else{
				//
				$nTmpStart = 0;
				$nTmpPID = $this->oLinkFeed->ParseStringBy2Tag($strGetUrl, 'onclick="getURL(\'', '\')">', $nTmpStart);
				if($this->debug) print "nTmpPID $nTmpPID  <br>\n";
				if ($nTmpPID === false) break;
				$strGetUrl = 'http://www.dgmpro.com/affiliates/index.cfm?fuseaction=promotions.destinationURL&pid='.$nTmpPID;
			}
			//
			
			//$aff_mer_id
			//get MerIDinAff
			$aff_mer_id = $arrAllExistsMerInAff[$aff_mer_name];
			if ($aff_mer_id == ''){
				echo " wrong MerIDinAff : $aff_mer_name ";
				if($this->debug) print " Inactive Merhcnat. $aff_mer_name  <br>\n";
				continue;
			}
			if($this->debug) print "aff_mer_id $aff_mer_id - $aff_mer_name  <br>\n";

			if ($nTmpPID != ''){
				$link_id = 'p_'.$aff_mer_id.'_'.$nTmpPID;
			}
			else{
				$link_id = 'p_'.$aff_mer_id.'_'.$link_name.'_'.$strVoucherCode;
			}
			
			$start_date = $this->oLinkFeed->ParseStringBy2Tag($result, '<td class="lightblueRow" align="center">', '</td>', $nLineStart);
			if($this->debug) print "start_date $start_date  <br>\n";
			if ($start_date === false) break;
			$start_date = date("Y-m-d", strtotime($start_date));

			$end_date = $this->oLinkFeed->ParseStringBy2Tag($result, '<td class="lightblueRow" align="center">', '</td>', $nLineStart);
			if($this->debug) print "end_date $end_date  <br>\n";
			if ($end_date === false) break;
			list($strDay, $strMon, $strYear) = explode(" ", trim($end_date));
			$end_date = date("Y-m-d", strtotime($end_date));

			if ($strGetUrl == ''){
				$html_code = 'Please leave URL as blank for using default merchant\'s URL. ';
			}
			else{
				//open detail page to get html code
				$strDetailUrl = $strGetUrl;
				$Detailresult = $this->oLinkFeed->GetHttpResult($strDetailUrl, false, '', $cookie_jar);	
				
				//parse detail page
				$nDetailLineStart = 0;
				$html_code = $this->oLinkFeed->ParseStringBy2Tag($Detailresult, array('<b>Copy your code from below</b>', 'class="generaltext">'), '</textarea>', $nDetailLineStart);
				if($this->debug) print "html_code $html_code  <br>\n";
				if ($html_code === false) break;
			}

			$link_src_lastupdate = "";
		
			if ($affPromType == 'Voucher'){
				$promo_type = 'coupon';
				$promo_type = $this->oLinkFeed->getPromoTypeByLinkContent($link_name. '. '. $link_desc . ' ' . $html_code);
			}
			else{
				$promo_type = $this->oLinkFeed->getPromoTypeByLinkContent($link_name. '. '. $link_desc . ' ' . $html_code);
				if($this->debug) print "promo_type $promo_type  <br>\n";
			}

			//$internal_merid

			$internal_merid = $arrAllExistsInternalMerchants[$aff_mer_id];

			UpdateLinkToDB($aff_id, $aff_mer_id, $aff_mer_name, $link_id, $link_name, $link_desc, $start_date, $end_date, $promo_type, $html_code, '', '', $internal_merid, 'pending', $link_src_lastupdate, $couponUpdateCnt);

			$couponCnt++;
			//exit;
		}
		//end text link

	}

	function GetAllLinksFromAffByMerID($AffName, $aff_mer_id, $internal_merid, $bLogined, &$cookie_jar, &$couponCnt, &$couponUpdateCnt)
	{
		return $this->GetAllLinksFromDgmPro($aff_mer_id, $internal_merid, $bLogined, $cookie_jar, $couponCnt, $couponUpdateCnt, $aff_id);
	}

	function GetAllLinksFromDgmPro($aff_mer_id, $internal_merid, $bLogined, &$cookie_jar, &$couponCnt, &$couponUpdateCnt, $aff_id){
		if (!$bLogined){
			$result = LoginIntoAffService($aff_id, $cookie_jar);
		}

		list($strTmpMerID, $strTmpCampID) = explode('_', $aff_mer_id);
		$strTmpMerID = trim($strTmpMerID);
		$strTmpCampID = trim($strTmpCampID);
		if (($strTmpCampID == '') || ($strTmpMerID == '')){
			echo " Wrong MerIDinAff: $aff_mer_id";
			exit;
		}

		$couponCnt = 0;
		$couponUpdateCnt = 0;

		//get text link
		//http://www.dgmpro.com/affiliates/index.cfm?fuseaction=creatives.text_links&cpid=12753&cmid=563
		$strUrl = "http://www.dgmpro.com/affiliates/index.cfm?fuseaction=creatives.text_links&cpid=".$strTmpCampID."&cmid=".$strTmpMerID."&view=all";
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
$result = $r["content"];

		if($this->debug) print "Get links Data : Page - $nPageNo <br>\n";

		$strLineStart = '<strong>Text link name:</strong>';
		$nLineStart = 0;

		while ($nLineStart >= 0){
			if($this->debug) print "Process $couponCnt  ";

			$nLineStart = stripos($result, $strLineStart, $nLineStart);
			if ($nLineStart === false) break;

			//link name
			$link_name = $this->oLinkFeed->ParseStringBy2Tag($result, '<b>', '</b>', $nLineStart);
			if($this->debug) print "link_name $link_name  <br>\n";
			if ($link_name === false) break;

			//get Link ID
			$link_id = $this->oLinkFeed->ParseStringBy2Tag($result, 'SelectAllCode_', '"', $nLineStart);
			if($this->debug) print "link_id $link_id  <br>\n";
			if ($link_id === false) break;
			$link_id = 'txt_'.$link_id;

			$html_code = $this->oLinkFeed->ParseStringBy2Tag($result, 'rows="3" cols="85">', '</textarea>', $nLineStart);
			if($this->debug) print "html_code $html_code  <br>\n";
			if ($html_code === false) break;

			$aff_mer_name = $this->oLinkFeed->ParseStringBy2Tag($result, array('<strong>Advertiser:</strong>', '">'), '<strong', $nLineStart);
			if($this->debug) print "aff_mer_name $aff_mer_name  <br>\n";
			if ($aff_mer_name === false) break;

			$original_url = $html_code;

			$link_src_lastupdate = "";
		
			$start_date = '0000-00-00';
			$end_date = '0000-00-00';
			$link_desc = $link_name;

			$promo_type = $this->oLinkFeed->getPromoTypeByLinkContent($link_desc . ' ' . $html_code);
			if($this->debug) print "promo_type $promo_type  <br>\n";

			UpdateLinkToDB($aff_id, $aff_mer_id, $aff_mer_name, $link_id, $link_name, $link_desc, $start_date, $end_date, $promo_type, $html_code, $original_url, '', $internal_merid, 'pending', '', $couponUpdateCnt);

			$couponCnt++;
			//exit;
		}
		//end text link

		//get banner link
		//http://www.dgmpro.com/affiliates/index.cfm?fuseaction=creatives.creative&cpid=12753&cmid=563&view=all
		$strUrl = "http://www.dgmpro.com/affiliates/index.cfm?fuseaction=creatives.creative&cpid=".$strTmpCampID."&cmid=".$strTmpMerID."&view=all";
		$r = $this->oLinkFeed->GetHttpResult($strUrl,$request);
$result = $r["content"];

		if($this->debug) print "Get Banner links Data : Page - $nPageNo <br>\n";

		$strLineStart = '<strong>Banner Name:</strong>';
		$nLineStart = 0;

		while ($nLineStart >= 0){
			if($this->debug) print "Process $couponCnt  ";

			$nLineStart = stripos($result, $strLineStart, $nLineStart);
			if ($nLineStart === false) break;

			//link name
			$link_name = $this->oLinkFeed->ParseStringBy2Tag($result, '</strong>', '</td>', $nLineStart);
			if($this->debug) print "link_name $link_name  <br>\n";
			if ($link_name === false) break;

			//get detail page url
			$detailPageUrl = $this->oLinkFeed->ParseStringBy2Tag($result, array('<tr class="ht1">', 'onClick="window.open(\'') , '\'', $nLineStart);
			if($this->debug) print "detailPageUrl $detailPageUrl  <br>\n";
			if ($detailPageUrl === false) break;
			
			//original_url
			$original_url = $this->oLinkFeed->ParseStringBy2Tag($result, '<strong style="align:right;"><a href="', '" target="_blank">Destination Page</a>', $nLineStart);
			if($this->debug) print "original_url $original_url  <br>\n";
			if ($original_url === false) break;

			//Open Detail Page
			$strDetailUrl = trim('http://www.dgmpro.com/affiliates/'.$detailPageUrl);
			$strDetailUrl = str_replace(' ', '%20' , $strDetailUrl);
			if($this->debug) print "strDetailUrl $strDetailUrl  <br>\n";
			$Detailresult = $this->oLinkFeed->GetHttpResult($strDetailUrl, false, '', $cookie_jar);

			//parse detail page
			$nDetailLineStart = 0;
			$html_code = $this->oLinkFeed->ParseStringBy2Tag($Detailresult, array('Copy your code from below ...', '<textarea HTMLEditFormat name="SelectAllCode" rows="5" cols="90" id="pick_up_link">'), '</textarea>', $nDetailLineStart);
			if($this->debug) print "html_code $html_code  <br>\n";
			if ($html_code === false) break;

			//get Link ID
			$link_id = $this->oLinkFeed->ParseStringBy2Tag($Detailresult, '&v=', '&r=', $nDetailLineStart);
			if($this->debug) print "link_id $link_id  <br>\n";
			if ($link_id === false) break;

			$link_id = 'banner_'.$link_id;

			$link_src_lastupdate = "";
		
			$start_date = '0000-00-00';
			$end_date = '0000-00-00';
			$link_desc = $link_name;

			$promo_type = $this->oLinkFeed->getPromoTypeByLinkContent($link_desc . ' ' . $html_code);
			if($this->debug) print "promo_type $promo_type  <br>\n";

			UpdateLinkToDB($aff_id, $aff_mer_id, $aff_mer_name, $link_id, $link_name, $link_desc, $start_date, $end_date, $promo_type, $html_code, $original_url, '', $internal_merid, 'pending', '', $couponUpdateCnt);

			$couponCnt++;
			//exit;
		}
	}

}
?>
