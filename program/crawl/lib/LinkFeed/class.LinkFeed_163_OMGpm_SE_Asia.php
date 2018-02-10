<?php
include_once(dirname(__FILE__) . "/class.LinkFeed_OMGpm.php");
class LinkFeed_163_OMGpm_SE_Asia extends LinkFeed_OMGpm
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		$this->DataSource = array("feed" => 218);
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		if (SID == 'bdg01'){
			$this->WID = '77564';
			$this->Agency = 118;
			$this->Affiliate = 1030990;
			$this->Hash = 'E138592C67BA3B6CD3E802CC06515CD3';
			$this->API_Key = 'c5e663e3-e87f-4455-b2b9-9d36ec183208';
			$this->private_key = 'df4e69415924469f9c181259b957b408';
		}else{
			$this->WID = '77163';
			$this->Agency = 118;
			$this->Affiliate = 1023249;
			$this->Hash = '370DB2E3E2C949D8B9E2134D43DD025B';
			$this->API_Key = '92552a1f-2701-4cfb-9815-6e2e0023577d';
			$this->private_key = '515f6b7921b241dc93397096175e2449';
		}
	}	
}
