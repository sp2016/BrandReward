<?php
#5 Percent per Sale 	=> 5%
#40 Dollar per Lead 	=> 40
#200 Dollar per Sale 	=> 200
class LinkFeed_115 extends LibFactory{
	function processCommissionTxt($commissionTxt){
		if(empty($commissionTxt))
			return null;

		$commissionTxt = str_replace('Percent', '%', $commissionTxt);
		$commission = array();
		$commission = currency_match_str($commissionTxt);

		$returnData = select_commission_used($commission);
		// $returnData['commission'] = $commissionTxt;

		return $returnData;
	}
}
?>