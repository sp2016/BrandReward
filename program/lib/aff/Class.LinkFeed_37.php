<?php

class LinkFeed_37 extends LibFactory{
	function processCommissionTxt($commissionTxt){
		if(empty($commissionTxt))
			return null;

		$str = trim($commissionTxt);

		list($a,$b,$c,$d,$e,$f) = explode('|',$str);

		$str = $c;
		$hasIncentive = 0;

		$commission = array();
		$commission = currency_match_str($str);

		$returnData = select_commission_used($commission);
		// $returnData['commission'] = $commissionTxt;
		return $returnData;
	}
}
?>