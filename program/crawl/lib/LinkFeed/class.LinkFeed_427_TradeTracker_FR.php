<?php
include_once(dirname(__FILE__) . "/class.LinkFeed_TradeTracker.php");
class LinkFeed_427_TradeTracker_FR extends LinkFeed_TradeTracker
{	
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		$this->soapClient = null;
		$this->DataSource = 179;
		if(SID == 'bdg02'){
			$this->affiliateSiteID = 261296;
			$this->customerID = 144691;
			$this->passphrase = 'da76845f503effdbdf99756accc1b2a3e549543c';
		}else{
			$this->affiliateSiteID = 265909;
			$this->customerID = 147347;
			$this->passphrase = '3a6ceb6b670b33dd08666285106ec3695421bd64';
		}
	}
}
?>
