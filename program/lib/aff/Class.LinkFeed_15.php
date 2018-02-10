<?php

class LinkFeed_15 extends LibFactory{
	function processCommissionTxt($commissionTxt){
		if(empty($commissionTxt))
			return null;

		/*$objDomTools = new DomTools();
		$objDomTools->flush();
		$objDomTools->setContent($commissionTxt);
		$objDomTools->select('li');
		$liHtmlArr = $objDomTools->get();

		$commission = array();
		$regex_number = get_regex('number');

		$txtTmp = array();
		foreach($liHtmlArr as $a=>$b){
			$str = trim($b['Content']);

			preg_match('/<strong>(\w+)<\/strong>/',$str,$m);
			$key = $m[1];

			$step = strpos($str, '</strong>');
			$value = substr($str,$step+9);
			$txtTmp[$key] = $value;
		}

		$commSelect = '';
		if(isset($txtTmp['Sale'])){
			$commSelect = $txtTmp['Sale'];
		}else{
			$commSelect = array_shift($txtTmp);
		}*/
		
		$commission = currency_match_str($commissionTxt);

		 


		$returnData = select_commission_used($commission);
		// $returnData['commission'] = $commissionTxt;
 
		return $returnData;
	}
}
?>