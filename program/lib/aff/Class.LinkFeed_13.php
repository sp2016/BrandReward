<?php
# 4% - 6%
class LinkFeed_13 extends LibFactory{
	function processCommissionTxt($commissionTxt){
		if(empty($commissionTxt))
			return null;
		$commissionTxt = str_replace('<span>', '', $commissionTxt);
		$commission = array();
		$commission = currency_match_str($commissionTxt);

		$returnData = select_commission_used($commission);
		// $returnData['commission'] = $commissionTxt;

		return $returnData;
	}
}
?>