<?php

class LinkFeed_152 extends LibFactory{
	function processCommissionTxt($commissionTxt,$country){
		if(!$commissionTxt)
			return null;

		$commission = array();
		$commissionTxt = trim($commissionTxt);

		$arr = explode(',',$commissionTxt);
		list($saleminpercent_k,$saleminpercent_v) = explode(':',trim($arr[0]));
		list($salemaxpercent_k,$salemaxpercent_v) = explode(':',trim($arr[1]));
		list($saleminfix_k,$saleminfix_v) = explode(':',trim($arr[2]));
		list($salemaxfix_k,$salemaxfix_v) = explode(':',trim($arr[3]));
		list($leadmin_k,$leadmin_v) = explode(':',trim($arr[4]));
		list($leadmax_k,$leadmax_v) = explode(':',trim($arr[5]));
		list($clickmin_k,$clickmin_v) = explode(':',trim($arr[6]));
		list($clickmax_k,$clickmax_v) = explode(':',trim($arr[7]));
		list($viewmin_k,$viewmin_v) = explode(':',trim($arr[8]));
		list($viewmax_k,$viewmax_v) = explode(':',trim($arr[9]));

		$saleminpercent_v = trim($saleminpercent_v);
		$salemaxpercent_v = trim($salemaxpercent_v);
		$saleminfix_v = trim($saleminfix_v);
		$salemaxfix_v = trim($salemaxfix_v);
		$leadmin_v = trim($leadmin_v);
		$leadmax_v = trim($leadmax_v);
		$clickmin_v = trim($clickmin_v);
		$clickmax_v = trim($clickmax_v);
		$viewmin_v = trim($viewmin_v);
		$viewmax_v = trim($viewmax_v);

		if($saleminpercent_v)
			$commission[] = $saleminpercent_v.'%';

		if($salemaxpercent_v)
			$commission[] = $salemaxpercent_v.'%';

		if(empty($commission)){
			if($saleminfix_v)
				$commission[] = $saleminfix_v;

			if($salemaxfix_v)
				$commission[] = $salemaxfix_v;
		}

		if(empty($commission)){
			if($leadmin_v)
				$commission[] = $leadmin_v;

			if($leadmax_v)
				$commission[] = $leadmax_v;
		}

		if(empty($commission)){
			if($clickmin_v)
				$commission[] = $clickmin_v;

			if($clickmax_v)
				$commission[] = $clickmax_v;
		}

		if(empty($commission)){
			if($viewmin_v)
				$commission[] = $viewmin_v;

			if($viewmax_v)
				$commission[] = $viewmax_v;
		}

		if($country == 'EN'){
			$cur = 'GBP';
		}else{
			$cur = 'EUR';
		}
		$returnData = select_commission_used($commission,$cur);
		// $returnData['commissionTxt'] = $commissionTxt;
		return $returnData;
	}
}
?>