<?php
include_once(dirname(__FILE__) . "/class.LinkFeed_TradeTracker.php");
class LinkFeed_2054_TradeTracker_UAE extends LinkFeed_TradeTracker
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
            $this->affiliateSiteID = 283029;
            $this->customerID = 144691;
            $this->passphrase = '0ba1bbadf5f8433376ed1716d4b0fa82828a1300';
        }else{
            $this->affiliateSiteID = 0;
            $this->customerID = 0;
            $this->passphrase = '';
        }
    }
}
?>
