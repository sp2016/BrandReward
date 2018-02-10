<?php

class LinkFeed_394 extends LibFactory{
	function processCommissionTxt($commissionTxt){
		if(empty($commissionTxt))
			return null;

		$str = trim($commissionTxt);
		list($a,$b,$c,$d,$e) = explode(',',$str);
		list(,$percent_payout) = explode(':',$a);
		list(,$conversion_cap) = explode(':',$b);
		list(,$currency) = explode(':',$c);
		list(,$payout_cap) = explode(':',$d);
		list(,$payout_type) = explode(':',$e);

		$hasIncentive = 0;

		$str = $percent_payout;
		$commission[] = $percent_payout.'%';
		$returnData = select_commission_used($commission);
		// $returnData['commission'] = $commissionTxt;
		return $returnData;
	}
}
?>