<?php

class LinkFeed_360 extends LibFactory{
	function processCommissionTxt($commissionTxt){
		if(empty($commissionTxt))
			return null;

		$hasIncentive = 0;

		$commissionTxt = str_replace(',','.',$commissionTxt);
		if(strpos($commissionTxt,'|') !== false){
			if(strpos($commissionTxt,'Sale:') !== false){
				$tmp = explode('|',$commissionTxt);
				foreach($tmp as $v){
					if(strpos($v,'Sale:') !== false){
						$commissionTxt = $v;
					}
				}
			}else{
				$pos = strpos($commissionTxt,'|');
				$commissionTxt = substr($commissionTxt,0,$pos);
			}
		}

		$commission = array();
		$commission = currency_match_str($commissionTxt);

		$returnData = select_commission_used($commission);
		// $returnData['commission'] = $commissionTxt;
		
		return $returnData;
	}
}
?>