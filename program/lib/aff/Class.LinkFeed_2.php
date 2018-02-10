<?php

class LinkFeed_2 extends LibFactory{
		function processCommissionTxt($commissionTxt){
		if(empty($commissionTxt))
			return null;

		$hasIncentive = 0;
		$isAnnotation = 0;
		$pos = strpos($commissionTxt,';');
		if($pos !== false){
			$tmpCommissionTxt = currency_match_str(substr($commissionTxt,0,$pos));
			if(intval($tmpCommissionTxt[0]) != 0 )
			{
				$commissionTxt = substr($commissionTxt,0,$pos);
				$hasIncentive = 1;
			}
			else
			{
				$isAnnotation = 1;
			}
		}

		$commission = array();
		$commission = currency_match_str($commissionTxt);
		if($isAnnotation)
		{
			if(stripos($commission[0],'%'))
				$additional = '0.000001%';
			else
				$additional = 0.0000001;
			$commission[] = $additional;
			$commission = array_unique($commission);
		}

		foreach($commission as $k=>$v){
			$commission[$k] = $v.'|'.$hasIncentive;
		}
		$returnData = select_commission_used($commission);
		return $returnData;
	}
}
?>


