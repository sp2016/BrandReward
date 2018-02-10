<?php
include_once(dirname(__FILE__) . "/class.LinkFeed_Webgains.php");
class LinkFeed_34_Webgains_DE extends LinkFeed_Webgains
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->DataSource = array("feed" => 20, "website" => 21);
		
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
	}
}
?>
