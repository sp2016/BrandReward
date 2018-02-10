<?php
include_once(dirname(__FILE__) . "/class.LinkFeed_TradeTracker.php");
class LinkFeed_65_TradeTracker_DE extends LinkFeed_TradeTracker
{	
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		$this->soapClient = null;
		$this->DataSource = 181;
		if(SID == 'bdg02'){
			$this->affiliateSiteID = 264723;
			$this->customerID = 144691;
			$this->passphrase = 'b829d68bbdb03509ddcda3940748e54a7926ea12';
		}else{
			$this->affiliateSiteID = 263276;
			$this->customerID = 146819;
			$this->passphrase = 'c983ccf7e603b7b592d6061f906b2310564d8c63';
		}
	}
}
?>
