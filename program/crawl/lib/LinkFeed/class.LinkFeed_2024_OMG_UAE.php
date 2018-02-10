<?php
include_once(dirname(__FILE__) . "/class.LinkFeed_OMGpm.php");
class LinkFeed_2024_OMG_UAE extends LinkFeed_OMGpm
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->DataSource = array("feed" => 0);
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		if (SID == 'bdg01'){
			$this->Agency = 0;
			$this->Affiliate = 0;
			$this->Hash = '';
			$this->API_Key = '';
			$this->private_key = '';
		}else{
			$this->Agency = 172;
			$this->Affiliate = 1059391;
			$this->Hash = 'B3C83E333B7CC4678D677CE469FB2032';
			$this->API_Key = '4ded3413-3851-4f80-b214-d1c716dae86f';
			$this->private_key = 'bafc339fc6fb40ce969cf8a8d40a2774';
		}
	}	
}
