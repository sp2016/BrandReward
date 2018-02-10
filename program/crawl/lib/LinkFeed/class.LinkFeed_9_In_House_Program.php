<?php
class LinkFeed_9_In_House_Program
{
	var $info = array(
		"ID" => "9",
		"Name" => "In-House Program",
		"IsActive" => "YES",
		"ClassName" => "LinkFeed_9_In_House_Program",
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
