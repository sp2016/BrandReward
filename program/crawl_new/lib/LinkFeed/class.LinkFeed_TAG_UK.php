<?php
include_once(dirname(__FILE__) . "/class.LinkFeed_TAG.php");
class LinkFeed_TAG_UK extends LinkFeed_TAG
{
	
	function __construct($aff_id, $oLinkFeed)
	{
		
	    $this->oLinkFeed = $oLinkFeed;
	    $this->info = $oLinkFeed->getAffById($aff_id);
	    $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
	    $this->affDomain = 'https://www.tagpm.com';
	     
	}
	
}
