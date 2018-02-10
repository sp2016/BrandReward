<?php
class LinkFeed_16_RevTrax
{
	var $info = array(
		"ID" => "16",
		"Name" => "RevTrax",
		"IsActive" => "YES",
		"ClassName" => "LinkFeed_16_RevTrax",
		"LastCheckDate" => "1970-01-01",
	);
	
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
	}
	
	function getMerAffIDByURL($strURL)
	{
		return $this->getRevTraxMerIDByURL($strURL);
	}
	
	function getRevTraxMerIDByURL($strURL){
		//https://sec.revtrax.com/RevTrax/affiliate/affiliateMerchantDetails?merId=10002369
		// MerID
		
		$strURL = trim($strURL);
		if ((substr($strURL, 0, 7) == 'http://')||(substr($strURL, 0, 8) == 'https://')){
			$arrUrl = parse_url($strURL);
			if ($arrUrl['scheme'] == ''){
			}
			else{
				parse_str($arrUrl['query'], $arrQuery);
//				echo $arrUrl['query'];
//				print_r($arrQuery);
				$strMerID = trim($arrQuery['merId']);
//				echo $strMerID.'_'.$strCampaignID;

				if ($strMerID == ''){
				}
				else{
					return $strMerID;
				}
			}
		}
		else{
		}
		return $strURL;
	}
}
?>
