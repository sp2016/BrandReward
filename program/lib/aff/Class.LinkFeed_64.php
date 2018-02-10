<?php

class LinkFeed_64 extends LibFactory{
	function processCommissionTxt($commissionTxt){
		if(empty($commissionTxt))
			return null;

		$commissionTxt = str_replace('\r\n',';',$commissionTxt);
		$commissionTxt = str_replace('\n\r',';',$commissionTxt);
		$commissionTxt = str_replace('\r',';',$commissionTxt);
		$commissionTxt = str_replace('\n',';',$commissionTxt);
		$commission = array();
		$commission = explode(";", $commissionTxt);
		
		$returnData = select_commission_used($commission);
		// $returnData['commission'] = $commissionTxt;
		
		return $returnData;
	}
}
?>