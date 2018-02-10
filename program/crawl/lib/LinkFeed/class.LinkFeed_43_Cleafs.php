<?php
class LinkFeed_43_Cleafs
{
	var $info = array(
		"ID" => "43",
		"Name" => "Cleafs",
		"IsActive" => "YES",
		"ClassName" => "LinkFeed_43_Cleafs",
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
