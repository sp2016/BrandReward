<?php

class LinkFeed_503 extends LibFactory{
    function processCommissionTxt($commissionTxt){
        if(empty($commissionTxt))
            return null;
        $commissionTxt = strip_tags($commissionTxt);
        $commissionTxt = str_replace(',','.',$commissionTxt);


        $commission = array();
        $commission = currency_match_str($commissionTxt);



        $returnData = select_commission_used($commission);
        $returnData['commission'] = $commissionTxt;

        return $returnData;
    }
}
?>