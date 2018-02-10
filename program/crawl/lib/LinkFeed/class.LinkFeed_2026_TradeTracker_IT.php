<?php
include_once(dirname(__FILE__) . "/class.LinkFeed_TradeTracker.php");
class LinkFeed_2026_TradeTracker_IT extends LinkFeed_TradeTracker
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
			$this->affiliateSiteID = 278842;
			$this->customerID = 144691;
			$this->passphrase = '3259be8590a9c8d24dfab854f533db7490080b44';
		}else{
			$this->affiliateSiteID = 0;
			$this->customerID = 0;
			$this->passphrase = '';
		}
	}
}
?>
