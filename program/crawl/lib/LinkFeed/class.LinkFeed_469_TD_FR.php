<?php
include_once(dirname(__FILE__) . "/class.LinkFeed_TD.php");
class LinkFeed_469_TD_FR extends LinkFeed_TD
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->DataSource = array("feed" => 136, "website" => 135);
        $this->getStatus = false;

        $this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
	}
}
?>
