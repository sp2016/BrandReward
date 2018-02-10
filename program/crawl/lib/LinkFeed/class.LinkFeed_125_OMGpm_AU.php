<?php
include_once(dirname(__FILE__) . "/class.LinkFeed_OMGpm.php");
class LinkFeed_125_OMGpm_AU extends LinkFeed_OMGpm
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->DataSource = array("feed" => 215);
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		if (SID == 'bdg01'){
			$this->WID = '77618';
			$this->Agency = 47;
			$this->Affiliate = 1031465;
			$this->Hash = 'F283ACEAC391AD2E01347D3CBE85A9D1';
			$this->API_Key = 'a64d7fbb-3858-46e3-b5cd-8e96b6b59cdc';
			$this->private_key = 'b487e9da76a240ea8f5c5ac69b97c5e5';
		}else{
			$this->WID = '';
			$this->Agency = 47;
			$this->Affiliate = '';
			$this->Hash = '';
			$this->API_Key = '';
			$this->private_key = '';
		}
	}	
}