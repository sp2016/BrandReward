<?php
class LinkFeed_55_GAN_PayPerCall
{
	var $info = array(
		"ID" => "55",
		"Name" => "GAN PayPerCall",
		"IsActive" => "YES",
		"ClassName" => "LinkFeed_55_GAN_PayPerCall",
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
