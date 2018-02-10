<?php
class LinkFeed_24_Moola_Moola
{
	var $info = array(
		"ID" => "24",
		"Name" => "Moola Moola",
		"IsActive" => "NO",
		"ClassName" => "LinkFeed_24_Moola_Moola",
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
