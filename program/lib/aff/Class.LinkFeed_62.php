<?php

class LinkFeed_62 extends LibFactory{
	function processCommissionTxt($commissionTxt){
		if(empty($commissionTxt))
			return null;

		$hasIncentive = 0;

		$commissionType = '';
		if(strpos($commissionTxt,'cpa_percentage') !== false)
			$commissionType = 'percent';

		$commission = array();
		$regex_number = get_regex('number');
		preg_match_all($regex_number,$commissionTxt,$m);
		if($m){
			foreach($m[0] as $k=>$v){
				$str_head = trim($m[1][$k]);
				$CommissionUsed = trim($m[2][$k]);
				$str_end = trim($m[3][$k]);
				if($commissionType == 'percent')
					$str_end = '%';

				$commission[] = $str_head.$CommissionUsed.$str_end.'|'.$hasIncentive;
			}
		}

		$returnData = select_commission_used($commission,'AUD');
		// $returnData['commission'] = $commissionTxt;
		return $returnData;
	}
}
?>