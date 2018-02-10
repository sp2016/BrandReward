<?php
class LinkFeed_40_Apogee
{
	var $info = array(
		"ID" => "40",
		"Name" => "Apogee",
		"IsActive" => "YES",
		"ClassName" => "LinkFeed_40_Apogee",
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
