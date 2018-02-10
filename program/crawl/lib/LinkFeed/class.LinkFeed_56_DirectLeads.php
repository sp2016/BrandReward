<?php
class LinkFeed_56_DirectLeads
{
	var $info = array(
		"ID" => "56",
		"Name" => "DirectLeads",
		"IsActive" => "YES",
		"ClassName" => "LinkFeed_56_DirectLeads",
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
