<?php
class LinkFeed_39_SuperClix
{
	var $info = array(
		"ID" => "39",
		"Name" => "SuperClix",
		"IsActive" => "YES",
		"ClassName" => "LinkFeed_39_SuperClix",
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
