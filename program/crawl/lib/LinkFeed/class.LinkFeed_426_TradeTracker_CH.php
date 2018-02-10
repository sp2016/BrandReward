<?php
include_once(dirname(__FILE__) . "/class.LinkFeed_TradeTracker.php");
class LinkFeed_426_TradeTracker_CH extends LinkFeed_TradeTracker
{	
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		$this->soapClient = null;
		$this->DataSource = 190;
		if(SID == 'bdg02'){
			$this->affiliateSiteID = 265245;
			$this->customerID = 144691;
			$this->passphrase = '31d8e2966033b34dd22c2ccac2ffc2c5878c360d';
		}else{
			$this->affiliateSiteID = 265910;
			$this->customerID = 147352;
			$this->passphrase = '43307d328bce3d058381b8ab021520fc706c2e41';
		}
	}
}
?>
