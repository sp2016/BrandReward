<?php
class LinkFeed_11_No_Affiliate_Program
{
	var $info = array(
		"ID" => "11",
		"Name" => "No Affiliate Program",
		"IsActive" => "YES",
		"ClassName" => "LinkFeed_11_No_Affiliate_Program",
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
