<?php

class LinkFeed_4 extends LibFactory{
	function processCommissionTxt($commissionTxt){
		if(empty($commissionTxt))
			return null;

		$hasIncentive = 0;

		$commission = array();
		$regex_number = get_regex('number');
		preg_match_all($regex_number,$commissionTxt,$m);
		if($m){
			foreach($m[0] as $k=>$v){
				$str_head = trim($m[1][$k]);
				$CommissionUsed = trim($m[2][$k]);
				$str_end = trim($m[3][$k]);

				$commission[] = $str_head.$CommissionUsed.$str_end.'|'.$hasIncentive;
			}
		}

		$returnData = select_commission_used($commission);
		// $returnData['commission'] = $commissionTxt;
		return $returnData;
	}
}
?>