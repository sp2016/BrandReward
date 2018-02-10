<?php

class LinkFeed_191 extends LibFactory{
    
    //规则：百分号&货币单位，混合时优先百分号。‘4-10%’有类似这样的格式，先分解，在计算
	function processCommissionTxt($commissionTxt){
		if(empty($commissionTxt))
			return null;

		$commissionTxt = trim($commissionTxt);
		$commissionTxtArr = explode("\n",$commissionTxt);
		 
		$temp_arr = array();
		foreach ($commissionTxtArr as $ca){
		    if(preg_match('/([0-9]+(\.[0-9]{1,3})?-[0-9]+(\.[0-9]{1,3})?)(\S+)/is', $ca, $match)){
		        $arr = explode('-',$match[1]);
		        foreach ($arr as $av){
		            $temp_arr[] = $av.$match[4];
		        } 
		    }
		    else{
		        $temp_arr[] = $ca;
		    }
		}
		$commissionTxt = implode(" ", $temp_arr);
		 
		 
		$hasIncentive = 0;

		//$pos = strpos($commissionTxt,"\n");
		//if($pos !== false){
		//	$hasIncentive = 1;

		//	$commissionTxt = substr($commissionTxt,0,$pos);
		//}

		$commission = array();
		$commission = currency_match_str($commissionTxt);
		 
		$commissionArr = array();
		foreach ($commission as $v){
		    if(preg_match('/\d%/', $v)){ //有百分号用百分计算
		        $commissionArr[] = $v;
		    }
		}
		 
		$commission = !empty($commissionArr) ? $commissionArr : $commission;
		 
		
		foreach($commission as $k=>$v){
			$commission[$k] = $v.'|'.$hasIncentive;
		}
         
		$returnData = select_commission_used($commission);
		
		// $returnData['commission'] = $commissionTxt;
		return $returnData;
	}
}
?>