<?php
class LinkFeed_47_Stream20
{
	var $info = array(
		"ID" => "47",
		"Name" => "Stream20",
		"IsActive" => "YES",
		"ClassName" => "LinkFeed_47_Stream20",
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
