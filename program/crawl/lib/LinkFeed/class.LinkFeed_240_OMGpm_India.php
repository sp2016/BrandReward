<?php
include_once(dirname(__FILE__) . "/class.LinkFeed_OMGpm.php");
class LinkFeed_240_OMGpm_India extends LinkFeed_OMGpm
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->DataSource = array("feed" => 221);
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		if (SID == 'bdg01'){
			$this->WID = '79001';
			$this->Agency = 95;
			$this->Affiliate = 1045463;
			$this->Hash = '8ECA72D187764A769FB4A43B34E095C6';
			$this->API_Key = '88d726d2-cbd9-45e9-b1b8-63817bb0ae1a';
			$this->private_key = '854cd9e76b854cd492665f37662ccf42';
		}else{
			$this->WID = '77533';
			$this->Agency = 95;
			$this->Affiliate = 1030347;
			$this->Hash = '18127EFF99D78FC02412E148499A8322';
			$this->API_Key = '1489652a-adbe-4bca-adeb-433daecb89b9';
			$this->private_key = 'bf6264dd1f664e549e7ab0b7c7b7ffd7';
		}
			
		
	}	
}
				