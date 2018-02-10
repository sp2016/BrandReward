<?php

class LinkFeed_243 extends LibFactory{
	function processCommissionTxt($commissionTxt){
		if(empty($commissionTxt))
			return null;

		$hasIncentive = 0;
		$commission = array();
		$commission = currency_match_str($commissionTxt);

		$returnData = select_commission_used($commission,'GBP');
		// $returnData['commission'] = $commissionTxt;
		return $returnData;
	}
}
?>