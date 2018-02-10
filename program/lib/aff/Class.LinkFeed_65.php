<?php

class LinkFeed_65 extends LibFactory{
	function processCommissionTxt($commissionTxt){
		$commission = array();
		list($lead,$sale_v,$sale_p) = explode(',',$commissionTxt);
		list(,$lead_value) = explode(':',$lead);
		list(,$sale_v_value) = explode(':',$sale_v);
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