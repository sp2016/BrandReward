<?php
class LinkFeed_14 extends LibFactory{
	function processCommissionTxt($commissionTxt){
		if(empty($commissionTxt))
			return null;
		$commissionTxt = str_replace('<span>', '', $commissionTxt);
		$commission = array();
		$commission = currency_match_str($commissionTxt);//currency_match_str方法负责解析数字和百分数。如果$commissionTxt有两个数字，则返回的数组中有两个值。当commissionType是value时，参数中数值和货币单位必须连在一起，否则会把货币单位解析没了
		$returnData = select_commission_used($commission);//select_commission_used分析出数字和货币单位
		// $returnData['commission'] = $commissionTxt;
		return $returnData;
	}
}
?>