<?php
include_once(dirname(__FILE__) . "/class.LinkFeed_Webgains.php");
class LinkFeed_14_Webgains_US extends LinkFeed_Webgains
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->DataSource = array("feed" => 16, "website" => 17);
		
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
	}
}
?>
