<?php
class LinkFeed_44_One4ads
{
	var $info = array(
		"ID" => "44",
		"Name" => "One4ads",
		"IsActive" => "YES",
		"ClassName" => "LinkFeed_44_One4ads",
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
