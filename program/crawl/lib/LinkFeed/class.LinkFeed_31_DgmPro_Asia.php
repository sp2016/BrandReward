<?php
class LinkFeed_31_DgmPro_Asia
{
	var $info = array(
		"ID" => "31",
		"Name" => "dgmPro Asia",
		"IsActive" => "YES",
		"ClassName" => "LinkFeed_31_DgmPro_Asia",
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
}
?>
