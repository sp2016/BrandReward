<?php

class LinkFeed_425 extends LibFactory{
	//	preg_match_all('#Lead\:(.*)\,Sales\(£\)\:(.*)\,Sales\(%\)\:(.*)#',utf8_encode($commissionTxt),$matches);
	//	if(intval($matches[2][0] > 0)){
	//		$commissionTxt = $matches[2][0]."£";
	//	}elseif(intval($matches[3][0] > 0)){
	//		$commissionTxt = $matches[3][0]."%";
	//	}elseif(intval($matches[1][0] > 0)){
	//		$commissionTxt = $matches[1][0]."%";
	//	}else{
	//		$commissionTxt = 0;
	//	}
	//	$commission = array();
	//	$commission = currency_match_str($commissionTxt);
	//
	//	$returnData = select_commission_used($commission);
	//	// $returnData['commission'] = $commissionTxt;
	//	//var_dump($returnData);
	//	die;
	//	return $returnData;
	//}

	function processCommissionTxt($commissionTxt){
		$commission = array();
		list($lead,$sale_v,$sale_p) = explode(',',$commissionTxt);
		list(,$lead_value) = explode(':',$lead);
		list($sale_v_title,$sale_v_value) = explode(':',$sale_v);
		list(,$sale_p_value) = explode(':',$sale_p);

		if($sale_p_value > 0){
			$commission[] = $sale_p_value.'%' ;
		}elseif($sale_v_value > 0){
			$commission[] = $sale_v_value ;
		}elseif($lead_value > 0){
			$commission[] = $lead_value ;
		}


		$returnData = select_commission_used($commission,'EUR');
		// $returnData['commissionTxt'] = $commissionTxt;
		return $returnData;
	}
}
?>