<?php
// Sale 10.00% <br> (Your Rate:  12.00%)
class LinkFeed_12 extends LibFactory{
	function processCommissionTxt($commissionTxt){
		if(empty($commissionTxt))
			return null;

		$hasIncentive = 0;
		$pos = strpos($commissionTxt, '<br');
		if($pos !== false){
			$commissionTxt = substr($commissionTxt,0,$pos);
			$hasIncentive = 1;
		}

		$pos = strpos($commissionTxt, '<BR');
		if($pos !== false){
			$commissionTxt = substr($commissionTxt,0,$pos);
			$hasIncentive = 1;
		}

		$commission = array();
		$commission = currency_match_str($commissionTxt);
		foreach($commission as $k=>$v){
			$commission[$k] = $v.'|'.$hasIncentive;
		}
		$returnData = select_commission_used($commission);
		// $returnData['commission'] = $commissionTxt;
		return $returnData;
	}
}
?>