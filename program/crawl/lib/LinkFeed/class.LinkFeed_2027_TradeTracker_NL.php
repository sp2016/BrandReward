<?php
include_once(dirname(__FILE__) . "/class.LinkFeed_TradeTracker.php");
class LinkFeed_2027_TradeTracker_NL extends LinkFeed_TradeTracker
{	
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		$this->soapClient = null;
		$this->DataSource = 0;
		if(SID == 'bdg02'){
			$this->affiliateSiteID = 265438;
			$this->customerID = 144691;
			$this->passphrase = '7318d2e3e87aca6aad9f4032927322c3c41da6ea';
		}else{
			$this->affiliateSiteID = 0;
			$this->customerID = 0;
			$this->passphrase = '';
		}
	}
}
?>
