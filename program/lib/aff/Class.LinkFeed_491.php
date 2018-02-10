<?php

class LinkFeed_491 extends LibFactory{
	function processCommissionTxt($commissionTxt){
		if(empty($commissionTxt))
			return null;

		list($saleStr,$leadStr,$clickStr) = explode(',',$commissionTxt);
		$saleArr = currency_match_str($saleStr);
		$leadArr = currency_match_str($leadStr);
		$clickArr = currency_match_str($clickStr);
		if(array_sum($saleArr) > 0){
			foreach ($saleArr as $k=>$v){
				$commission[$k] = $v.'%';
			}			
		}elseif (array_sum($leadArr) > 0){
			foreach ($leadArr as $k=>$v){
				$commission[$k] = $v.'CHF';
			}
		}elseif (array_sum($clickArr) > 0){
			foreach ($clickArr as $k=>$v){
				$commission[$k] = $v.'CHF';
			}
		}





 		$returnData = select_commission_used($commission);
		$returnData['commission'] = $commissionTxt;

 		return $returnData;
	}
}
?>