<?php

class LinkFeed_2044 extends LibFactory{
    function processCommissionTxt($commissionTxt){

        if(empty($commissionTxt))
            return null;

        $commission = array();
        $commission = currency_match_str($commissionTxt);

        $returnData = select_commission_used($commission);
        //print_r($returnData);

        // $returnData['commission'] = $commissionTxt;
        return $returnData;
    }
}
?>