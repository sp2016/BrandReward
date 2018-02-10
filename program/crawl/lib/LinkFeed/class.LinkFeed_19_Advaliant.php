<?php
class LinkFeed_19_Advaliant
{
	var $info = array(
		"ID" => "19",
		"Name" => "Advaliant",
		"IsActive" => "YES",
		"ClassName" => "LinkFeed_19_Advaliant",
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
