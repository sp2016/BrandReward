<?php
include_once(dirname(__FILE__) . "/class.LinkFeed_TradeTracker.php");
class LinkFeed_425_TradeTracker_AT extends LinkFeed_TradeTracker
{	
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		$this->soapClient = null;
		$this->DataSource = 187;
		if(SID == 'bdg02'){
			$this->affiliateSiteID = 265244;
			$this->customerID = 144691;
			$this->passphrase = '6ad5eeff1197e60dd04418fd0d09ea0de521b602';
		}else{
			$this->affiliateSiteID = 265912;
			$this->customerID = 147355;
			$this->passphrase = 'a573bcd1b06bf652031166a7206145c35e95d472';
		}
	}
}
?>
