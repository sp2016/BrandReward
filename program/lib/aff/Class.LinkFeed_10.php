<?php
# 5-15 means : from 5% to 15%
class LinkFeed_10 extends LibFactory{
	function processCommissionTxt($commissionTxt){
		if(empty($commissionTxt))
			return null;

		$commission = array();
		$commission = currency_match_str($commissionTxt);
		$returnData = select_commission_used($commission);
		return $returnData;
	}
}
?>