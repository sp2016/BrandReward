<?php
include_once(dirname(__FILE__) . "/class.LinkFeed_OMGpm.php");
class LinkFeed_57_OMGpm_UK extends LinkFeed_OMGpm
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->DataSource = array("feed" => 212);
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		if (SID == 'bdg01'){
			$this->WID = '78571';
			$this->Agency = 1;
			$this->Affiliate = 1041949;
			$this->Hash = '479704A06A9A7FD384BDBBB2F71EC5E1';
			$this->API_Key = '82d41f69-2d43-4d1f-b6ca-6ad512be570e';
			$this->private_key = '49f305e4c43744c0bcafecb94562350a';
		}else{
			$this->WID = '';
			$this->Agency = 1;
			$this->Affiliate = '';
			$this->Hash = '';
			$this->API_Key = '';
			$this->private_key = '';
		}
	}		
}
