	<?php
include_once(dirname(__FILE__) . "/class.LinkFeed_TradeTracker.php");
class LinkFeed_52_TradeTracker extends LinkFeed_TradeTracker
{	
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		$this->soapClient = null;
		$this->DataSource = 61;
		if(SID == 'bdg02'){
			$this->affiliateSiteID = 264725;
			$this->customerID = 144691;
			$this->passphrase = '01886556b5eb7d819b84932ac5edf98290bee357';
		}else{
			$this->affiliateSiteID = 265037;
			$this->customerID = 146823;
			$this->passphrase = '7db38d3d6cc9368031fb4a5d4b59a083c1d9f6aa';
		}
	}
}	
?>
