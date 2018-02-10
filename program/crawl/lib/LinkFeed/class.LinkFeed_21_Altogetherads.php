<?php
class LinkFeed_21_Altogetherads
{
	var $info = array(
		"ID" => "21",
		"Name" => "altogetherads",
		"IsActive" => "NO",
		"ClassName" => "LinkFeed_21_Altogetherads",
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
		return $this->getaltogetheradsMerIDByURL($strURL);
	}
	
	function getaltogetheradsMerIDByURL($strURL){
		//https://system.altogetherads.com/affiliates/merchants/merchantdetails.php?mid=1115
		//MerID
		
		$strURL = trim($strURL);
		if ((substr($strURL, 0, 7) == 'http://')||(substr($strURL, 0, 8) == 'https://')){
			$arrUrl = parse_url($strURL);
			if ($arrUrl['scheme'] == ''){
			}
			else{
				parse_str($arrUrl['query'], $arrQuery);
//				echo $arrUrl['query'];
//				print_r($arrQuery);
				$strMerID = trim($arrQuery['mid']);
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
