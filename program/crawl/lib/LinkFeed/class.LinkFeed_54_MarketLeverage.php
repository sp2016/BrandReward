<?php
class LinkFeed_54_MarketLeverage
{
	var $info = array(
		"ID" => "54",
		"Name" => "MarketLeverage ",
		"IsActive" => "YES",
		"ClassName" => "LinkFeed_54_MarketLeverage",
		"LastCheckDate" => "1970-01-01",
	);
	
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
	}
}
?>
