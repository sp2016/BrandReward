<?php

class LinkFeed_2035 extends LibFactory{
	function processCommissionTxt($commissionTxt){
		if(empty($commissionTxt))
			return null;

		$objDomTools = new DomTools();
		$objDomTools->flush();
		$objDomTools->setContent($commissionTxt);
		$objDomTools->select('tr');
		$trHtmlArr = $objDomTools->get();

		$hasIncentive = 0;

		$commission = array();
		$regex_number = get_regex('number');

		foreach($trHtmlArr as $a=>$b){
			$tr = trim($b['Content']);
			$objDomTools->flush();
			$objDomTools->setContent($tr);
			$objDomTools->select('td');
			$tdHtmlArr = $objDomTools->get();

			if(count($tdHtmlArr) < 3)
				continue;

			if(strpos($tdHtmlArr[0]['Content'] , 'Transaction') !== false)
				continue;

			$txt = $tdHtmlArr[1]['Content'].''.$tdHtmlArr[2]['Content'];
			$tmp = currency_match_str($txt);
			$commission = array_merge($commission,$tmp);
		}
		

		$returnData = select_commission_used($commission);
		// $returnData['commission'] = $commissionTxt;
		return $returnData;
	}
}
?>