<?php
include_once(dirname(__FILE__) . "/class.LinkFeed_TradeTracker.php");
class LinkFeed_795_TradeTracker_ru extends LinkFeed_TradeTracker
{
    function __construct($aff_id,$oLinkFeed)
    {
        $this->oLinkFeed = $oLinkFeed;
        $this->info = $oLinkFeed->getAffById($aff_id);
        $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
        $this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
        $this->soapClient = null;
        $this->DataSource = 61;
        if(SID == 'bdg02'){

        }else{
            $this->affiliateSiteID = '294988';
            $this->customerID = 146823;
            $this->passphrase = '93b30767aa038aa8691a66e89fc37ceefd5de1bf';
        }
    }
}
?>
