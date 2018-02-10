<?php
class LinkFeed_45_BlowFish
{
	var $info = array(
		"ID" => "45",
		"Name" => "BlowFish",
		"IsActive" => "YES",
		"ClassName" => "LinkFeed_45_BlowFish",
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
