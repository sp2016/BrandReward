<?php
include_once(dirname(__FILE__) . "/class.LinkFeed_OMGpm.php");
class LinkFeed_729_OMGpm_Mena extends LinkFeed_OMGpm
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->DataSource = array("feed" => 443);
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		if (SID == 'bdg01'){
			$this->WID = '79904';
			$this->Agency = 172;
			$this->Affiliate = 1059511;
			$this->Hash = '45F6F580B84477555257CBAE1EF94298';
			$this->API_Key = 'd772d30d-feba-4e8e-b04d-94ed88ffe16b';
			$this->private_key = 'd5bb7a6d6e4c48e8bfdaa5683ebd9929';
		}else{
			$this->WID = '';
			$this->Agency = '';
			$this->Affiliate = '';
			$this->Hash = '';
			$this->API_Key = '';
			$this->private_key = '';
		}
	}		
}
