<?php
include_once(dirname(__FILE__) . "/class.LinkFeed_TradeTracker.php");
class LinkFeed_2028_TradeTracker_RU extends LinkFeed_TradeTracker
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
			$this->affiliateSiteID = 281158;
			$this->customerID = 144691;
			$this->passphrase = '4cba49121f61444b6fe4f15fdb3ad9cf1fe60c0e';
		}else{
			$this->affiliateSiteID = 0;
			$this->customerID = 0;
			$this->passphrase = '';
		}
	}
}
?>
