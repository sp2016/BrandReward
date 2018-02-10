<?php
include_once(dirname(__FILE__) . "/class.LinkFeed_TradeTracker.php");
class LinkFeed_2029_TradeTracker_BE extends LinkFeed_TradeTracker
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
			$this->affiliateSiteID = 265014;
			$this->customerID = 144691;
			$this->passphrase = '8acbcf24d39bdaefd93219bee8568082e659becb';
		}else{
			$this->affiliateSiteID = 0;
			$this->customerID = 0;
			$this->passphrase = '';
		}
	}
}
?>
