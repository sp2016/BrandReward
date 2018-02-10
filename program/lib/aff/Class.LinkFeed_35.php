<?php

class LinkFeed_35 extends LibFactory{
    function processCommissionTxt($commissionTxt){

        if(empty($commissionTxt))
            return null;
        $commission = currency_match_str($commissionTxt);
        $returnData = select_commission_used($commission);
        return $returnData;
    }
}
?>