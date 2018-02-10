<?php
class LinkFeed_42_Atrinsic
{
	var $info = array(
		"ID" => "42",
		"Name" => "Atrinsic",
		"IsActive" => "YES",
		"ClassName" => "LinkFeed_42_Atrinsic",
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
