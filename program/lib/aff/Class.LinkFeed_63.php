<?php

class LinkFeed_63 extends LibFactory{
	function processCommissionTxt($commissionTxt){
		if(empty($commissionTxt))
			return null;

		list($sale,$lead,$click) = explode(',',$commissionTxt);

		$commission = array();
		$regex_number = get_regex('number');

		$flag = 0;
		preg_match_all($regex_number,$sale,$m);
		if($m){
			foreach($m[0] as $k=>$v){
				$str_head = trim($m[1][$k]);
				$CommissionUsed = trim($m[2][$k]);
				$str_end = '%';

				$hasIncentive = 0;
				$commission[] = $str_head.$CommissionUsed.$str_end.'|'.$hasIncentive;

				if($CommissionUsed > 0)
					$flag = 1;
			}
		}

		if(!$flag){
			preg_match_all($regex_number,$lead,$m);
			if($m){
				foreach($m[0] as $k=>$v){
					$str_head = trim($m[1][$k]);
					$CommissionUsed = trim($m[2][$k]);
					$str_end = trim($m[3][$k]);

					$hasIncentive = 0;
					$commission[] = $str_head.$CommissionUsed.$str_end.'|'.$hasIncentive;

					if($CommissionUsed > 0)
						$flag = 1;
				}
			}
		}

		if(!$flag){
			preg_match_all($regex_number,$click,$m);
			if($m){
				foreach($m[0] as $k=>$v){
					$str_head = trim($m[1][$k]);
					$CommissionUsed = trim($m[2][$k]);
					$str_end = trim($m[3][$k]);

					$hasIncentive = 0;
					$commission[] = $str_head.$CommissionUsed.$str_end.'|'.$hasIncentive;

					if($CommissionUsed > 0)
						$flag = 1;
				}
			}
		}

		$returnData = select_commission_used($commission,'EUR');
		// $returnData['commission'] = $commissionTxt;

		return $returnData;
	}
}
?>