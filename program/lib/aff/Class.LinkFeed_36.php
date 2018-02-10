<?php

class LinkFeed_36 extends LibFactory{
	function processCommissionTxt($commissionTxt){
		if(empty($commissionTxt))
			return null;

		$commission = array();
		$commissionTxt = trim($commissionTxt);
		$commissionTxt = str_replace(',', '.', $commissionTxt);
		$hasIncentive = 0;
		$line1 = $commissionTxt;
		$lineOther = '';

		
		if(strpos($commissionTxt, '<br>') !== false){
			$pos = strpos($commissionTxt, '<br>');
			$line1 = substr($commissionTxt,0,$pos);
			$lineOther = substr($commissionTxt,$pos);
		}elseif(strpos($commissionTxt, '<br/>') !== false){
			$pos = strpos($commissionTxt, '<br/>');
			$line1 = substr($commissionTxt,0,$pos);
			$lineOther = substr($commissionTxt,$pos);
		}elseif(strpos($commissionTxt, '</br>') !== false){
			$pos = strpos($commissionTxt, '</br>');
			$line1 = substr($commissionTxt,0,$pos);
			$lineOther = substr($commissionTxt,$pos);
		}elseif(strpos($commissionTxt, "\n") !== false){
			$pos = strpos($commissionTxt, "\n");
			$line1 = substr($commissionTxt,0,$pos);
			$lineOther = substr($commissionTxt,$pos);
		}


		$commission = currency_match_str($line1);
		$returnData = select_commission_used($commission);

		if(empty($returnData['CommissionValue']) && $lineOther){
			$commission = currency_match_str($lineOther);
		}
		
		$returnData = select_commission_used($commission);
		// $returnData['commission'] = $commissionTxt;
		return $returnData;
		
	}
}
?>