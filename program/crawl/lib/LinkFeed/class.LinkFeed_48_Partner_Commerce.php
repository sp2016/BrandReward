<?php
class LinkFeed_48_Partner_Commerce
{
	var $info = array(
		"ID" => "48",
		"Name" => "Partner Commerce",
		"IsActive" => "YES",
		"ClassName" => "LinkFeed_48_Partner_Commerce",
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
