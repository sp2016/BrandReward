<?php
class LinkFeed_51_RegNow
{
	var $info = array(
		"ID" => "51",
		"Name" => "RegNow",
		"IsActive" => "YES",
		"ClassName" => "LinkFeed_51_RegNow",
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
