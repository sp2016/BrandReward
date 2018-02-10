<?php

class LinkFeed_2031 extends LibFactory{
	function processCommissionTxt($commissionTxt){
		
		if(empty($commissionTxt))
			return null;

		$commission = $tmp_arr = array();
		$tmp_arr = explode("|", $commissionTxt);
		foreach($tmp_arr as $v){
			$commission[] = substr($v, strrpos($v, ':') ? strrpos($v, ':') + 1 : 0);
		}
		
		$returnData = select_commission_used($commission);
		//print_r($returnData);
		
		// $returnData['commission'] = $commissionTxt;
		return $returnData;
	}
}
?>