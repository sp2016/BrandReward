<?php
include_once(dirname(__FILE__) . "/class.LinkFeed_TradeTracker.php");
class LinkFeed_2053_TradeTracker_IN extends LinkFeed_TradeTracker
{
    function __construct($aff_id,$oLinkFeed)
    {
        $this->oLinkFeed = $oLinkFeed;
        $this->info = $oLinkFeed->getAffById($aff_id);
        $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
        $this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
        $this->soapClient = null;
        $this->DataSource = 0;
        if(SID == 'bdg02'){
            $this->affiliateSiteID = 294350;
            $this->customerID = 144691;
            $this->passphrase = 'efe55aad9480986dd144715e649e74c1e755194a';
        }else{
            $this->affiliateSiteID = 0;
            $this->customerID = 0;
            $this->passphrase = '';
        }
    }
}
?>
