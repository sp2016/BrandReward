<?php

class LinkFeed_7 extends LibFactory{
	function processCommissionTxt($commissionTxt){
		if(empty($commissionTxt))
			return null;

		$hasIncentive = 0;
		$arr  = explode('|', $commissionTxt);
		preg_match_all('#Sale Comm:(.*)#', $arr[0],$sale);
		preg_match_all('#Lead Comm:(.*)#', $arr[1],$lead);
		preg_match_all('#Hit Comm:(.*)#', $arr[2],$hit);
		
		if (isset($sale[1][0]) && !empty($sale[1][0])){
			$commissionTxt = $sale[1][0];
		}elseif (isset($lead[1][0]) && !empty($lead[1][0])){
			$commissionTxt = $lead[1][0];
		}elseif (isset($hit[1][0]) && !empty($hit[1][0])){
			$commissionTxt = $hit[1][0];
		}
		$commission = array();
		$commission = currency_match_str($commissionTxt);

		$returnData = select_commission_used($commission);
		// $returnData['commission'] = $commissionTxt;
		return $returnData;
	}
}
?>