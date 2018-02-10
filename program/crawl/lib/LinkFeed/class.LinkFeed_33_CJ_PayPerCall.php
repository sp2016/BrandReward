<?php
class LinkFeed_33_CJ_PayPerCall
{
	var $info = array(
		"ID" => "33",
		"Name" => "CJ PayPerCall",
		"IsActive" => "YES",
		"ClassName" => "LinkFeed_33_CJ_PayPerCall",
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
