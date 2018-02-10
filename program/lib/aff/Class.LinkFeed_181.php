<?php

class LinkFeed_181 extends LibFactory{
	function processCommissionTxt($commissionTxt){
		if(empty($commissionTxt))
			return null;

		$hasIncentive = 0;

		$pos = strpos($commissionTxt,'-->');
		if($pos !== false){
			$commissionTxt = substr($commissionTxt,$pos+3);
		}

		$commission = array();
		$commission = currency_match_str($commissionTxt);

		$returnData = select_commission_used($commission);
		// $returnData['commission'] = $commissionTxt;
		return $returnData;
	}
}
?>