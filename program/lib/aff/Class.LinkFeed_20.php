<?php

class LinkFeed_20 extends LibFactory{
	function processCommissionTxt($commissionTxt){
		if(empty($commissionTxt))
			return null;

		$commission = array();
		$hasIncentive = 0;
		$commissionTxt = trim($commissionTxt);

		$line1 = '';
		$lineOther = '';

		$pos = strpos($commissionTxt, '<br');
		$pos2 = strpos($commissionTxt, "\n");
		if($pos !== false){
			$line1 = substr($commissionTxt,0,$pos);
			$lineOther = substr($commissionTxt,$pos);
		}elseif($pos2 !== false){
			$line1 = substr($commissionTxt,0,$pos2);
			$lineOther = substr($commissionTxt,$pos2);
		}else{
			$line1 = $commissionTxt;
		}

		$regex_number = '/([^\d\s->\.\w,\|;]*)((?:\d+)(?:,\d+)?(?:\.\d+)?)([^\d\s<\(\)\w,\/:]*)/i';
		$commission = currency_match_str($line1);

		if(empty($commission) && $lineOther){
			$commission = currency_match_str($lineOther);
		}
		
		$returnData = select_commission_used($commission);
		// $returnData['commission'] = $commissionTxt;
		return $returnData;
	}
}
?>