<?php

class LinkFeed_811 extends LibFactory{
    function processCommissionTxt($commissionTxt){
        if(empty($commissionTxt))
            return null;
        $commissionTxt = str_replace('р', ' RUB', $commissionTxt);
        $commission = currency_match_str($commissionTxt);
        $returnData = select_commission_used($commission);
        return $returnData;
    }
}
?>