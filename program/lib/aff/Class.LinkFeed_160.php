<?php

class LinkFeed_160 extends LibFactory{
	function processCommissionTxt($commissionTxt){
		if(empty($commissionTxt))
			return null;

		$hasIncentive = 0;

		$words = array('on sales','all goods','for sales','all sales');
		foreach($words as $k=>$v){
			$pos = strpos($commissionTxt,$v);
			if($pos !== false)
				$commissionTxt = substr($commissionTxt,0,$pos);
		}

		$commission = array();
		$commission = currency_match_str($commissionTxt);

		$returnData = select_commission_used($commission);
		// $returnData['commission'] = $commissionTxt;
		return $returnData;
	}
}
?>