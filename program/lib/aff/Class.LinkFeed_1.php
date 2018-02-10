<?php

class LinkFeed_1 extends LibFactory{
	function processCommissionTxt($commissionTxt){
		/*$objDomTools = new DomTools();
		$objDomTools->flush();
		$objDomTools->setContent($commissionTxt);
		$objDomTools->select('tr');
		$trHtmlArr = $objDomTools->get();

		$commission = array();
		$hasIncentive = 0;

		foreach($trHtmlArr as $v){
			if(preg_match('/<nobr>Commission<\/nobr>(.*?)/i', $v['Content'])){
				$match_tmp = array();
				$match_tmp = currency_match_str($v['Content']);
				if(!empty($match_tmp))
					$commission[] = $match_tmp[0];
			}
		}
		
		$returnData = select_commission_used($commission);
		// $returnData['commissionTxt'] = $commissionTxt;
		return $returnData;*/
		
		if(empty($commissionTxt))
			return null;

		$commission = $tmp_arr = array();
		$tmp_arr = explode("|", $commissionTxt);
		foreach($tmp_arr as $v){
			$commission[] = substr($v, strrpos($v, ':') ? strrpos($v, ':') + 1 : 0);
		}
		//print_r($commission);
		
		/*$commission = currency_match_str($commissionTxt);
		foreach($commission as $k=>$v){
			$commission[$k] = $v.'|'.$hasIncentive;
		}
		print_r($commission);*/
		
		$returnData = select_commission_used($commission);
		//print_r($returnData);
		
		// $returnData['commission'] = $commissionTxt;
		return $returnData;
	}
}
?>