<?php
	/**
	 * Created by PhpStorm.
	 * User: Sky Ting
	 * Date: 2018/01/09
	 * Time: 19:04
	 */
	
	class FormatProrgam
	{
		public $aff_links_arr = array();
		public $top_domain_arr = array();
		public $aff_url_pattern = array();
		public $exchange_rate = array();
		public $block_rel = array();
		public $aff_rank = array();
		public $network_allow_country = array();
		public $country_code = array();
		public $country_code_arr = array();
		public $country_name_code = array();
		public $country_name_pattern = '';
		public $network_keywords = array();
		
		public function __construct($objProgram)
		{
			$sql = "select Domain from domain_top_level";
			$topDomain_tmp = $objProgram->objMysql->getRows($sql);
            foreach ($topDomain_tmp as $v)
            {
                $this->top_domain_arr[] = '\.'.$v['Domain'];
            }
            $country_arr = explode(",", $objProgram->global_c);
            foreach ($country_arr as $country) {
                if ($country) {
                    $country = "\." . strtolower($country);
                    $this->top_domain_arr[] = "\.com?" . $country;
                    $this->top_domain_arr[] = "\.org?" . $country;
                    $this->top_domain_arr[] = "\.net?" . $country;
                    $this->top_domain_arr[] = "\.gov?".$country;
                    $this->top_domain_arr[] = "\.edu?".$country;
                    $this->top_domain_arr[] =  $country."\.com";
                    $this->top_domain_arr[] = $country;
                }
            }
            
            $sql = "select ID, lower(MarketingContinent) MarketingContinent, lower(MarketingCountry) MarketingCountry from wf_aff where IsActive = 'yes' and  MarketingContinent != 'global' and MarketingContinent != ''";
            $aff_marketing = $objProgram->objMysql->getRows($sql);
            foreach ($aff_marketing as $v){
            	if(empty($v["MarketingCountry"])){
            		foreach($objProgram->country_rel as $country => $continent){
            			if(strcasecmp($v['MarketingContinent'],$continent) == 0)
            				$this->network_allow_country[$v['ID']][] = $country;
            		}
            	}else{
            		$this->network_allow_country[$v['ID']][] = $v["MarketingCountry"];
            	}
            }
            
            $sql = "select lower(countrycode) as countrycode, lower(countryname) as countryname, countrykeywords as countrykeywords from country_codes where countrystatus = 'on'";
            $tmp_arr = $objProgram->objMysql->getRows($sql);
            foreach($tmp_arr as $v){
            	$v['countryname'] = str_replace(".", "\.", $v['countryname']);
            	$this->country_name_code[$v['countryname']] = $v['countrycode'];
            	$this->country_code[$v['countrycode']] = $v['countrycode'];
            	$this->country_code_arr[$v['countrycode']] = $v['countrycode'];
            	if(!empty($v['countrykeywords'])){
            		$v['countrykeywords'] = strtolower($v['countrykeywords']);
            		$tmp = explode("|", $v['countrykeywords']);
            		foreach($tmp as $tmp_v){
            			$tmp_v = str_replace(".", "\.", $tmp_v);
            			$this->country_name_code[$tmp_v] = $v['countrycode'];
            			$this->country_code[$tmp_v] = $v['countrycode'];
            			$this->country_code_arr[$tmp_v] = $v['countrycode'];
            		}
            	}
            }
            $this->country_name_pattern = implode("|", array_keys($this->country_name_code));
            $this->country_code = strtoupper(preg_replace("/\s/", "", implode("|", array_keys($this->country_code))));
            
            $sql = "SELECT AffId, TplAffDefaultUrl, TplDeepUrlTpl, SupportDeepUrlTpl, NeedAffDefaultUrl FROM `aff_url_pattern` ";
			$this->aff_url_pattern = $objProgram->objMysql->getRows($sql, "AffId");
			
			$sql = "SELECT ExchangeRate, `Name` FROM exchange_rate WHERE `Date` = (SELECT MAX(`Date`) FROM exchange_rate) GROUP BY `Name`";
			$this->exchange_rate = $objProgram->objMysql->getRows($sql, "Name");
			
			//get block relationship
			$sql = "select accountid,objid,objtype,`status` from block_relationship where `status` = 'active'";
			$tmp_arr = $objProgram->objMysql->getRows($sql);
			foreach($tmp_arr as $v){
				if($v["objtype"] == "Affiliate"){
					$this->block_rel["aff"][$v["objid"]][$v["accountid"]] = $v["accountid"];
				}elseif($v["objtype"] == "Program"){
					$this->block_rel["program"][$v["objid"]][$v["accountid"]] = $v["accountid"];
				}
			}
			
			$sql = "select Keywords from advertiser_network_keywords";
			$this->network_keywords = array_keys($objProgram->objMysql->getRows($sql,'Keywords'));
			
			$sql = "SELECT ID, ImportanceRank Rank FROM wf_aff WHERE isactive = 'yes'";
			$this->aff_rank = $objProgram->objMysql->getRows($sql, "ID");
		}
		
		function execute($type,$data,$function=''){
			switch ($type){
				case 'ShippingCountry':
					$function = empty($function)?'shippingCountryDefault':$function;
					return strtolower(implode(",", array_unique(explode(',',$this->$function($data['TargetCountryExt'],$data['Name'])))));
					break;
				case 'Commission':
					$function = empty($function)?'commissionDefault':$function;
					if(empty($data))
						return array();
					return $this->$function($data);
					break;
				case 'SupportType':
					$function = empty($function)?'supportTypeDefault':$function;
					return $this->$function();
					break;
				case 'Category':
					$function = empty($function)?'categoryDefault':$function;
					return $this->$function($data['CategoryExt'],$data['AffId']);
					break;
				case 'AffDefaultUrl':
//					if(!empty($data["AffDefaultUrl"]))
//						return  $data["AffDefaultUrl"];
					$function = empty($function)?'affDefaultUrlDefault':$function;
					return $this->$function($data);
					break;
				case 'DeepUrlTpl':
//					if(!empty($data["AffDefaultUrl"]))
//						return  $data["AffDefaultUrl"];
					$function = empty($function)?'deepUrlTplUrlDefault':$function;
					return $this->$function($data);
					break;
				default:
					break;
			}
		return '';
		}
		
		
		
		function shippingCountryDefault($targetCountryExt,$name){
			$shipping_arr = array();
			$targetCountryExtArr = explode(",", $targetCountryExt);
			foreach($this->country_name_code as $k => $v) {
				$k = preg_replace("/\s/", "", $k);
				foreach ($targetCountryExtArr as $targetCountry) {
					$targetCountry = preg_replace("/\s/", "", $targetCountry);
					if (strcasecmp($k, $targetCountry) == 0) {
						$shipping_arr[$v] = $v;
					}
					if (strcasecmp($v, $targetCountry) == 0) {
						$shipping_arr[$v] = $v;
					}
				}
			}
			if(empty($shipping_arr)){
				preg_match_all("/(?:[^a-zA-Z]|)($this->country_name_pattern)(?:[^a-zA-Z]|$)/i", $targetCountryExt, $m);
				if(count($m) && !empty($m[1]) && is_array($m[1])){
					foreach($m[1] as $cc){
						$cc = strtolower($cc);
						$shipping_arr[$this->country_name_code[$cc]] = $this->country_name_code[$cc];
					}
				}
			}
			array_multisort($shipping_arr);
			return strtolower(implode(",", $shipping_arr));
		}
		
		function shippingCountry1FromName($targetCountryExt,$name){
			$shipping_arr = array();
			preg_match("/[^a-zA-Z]+($this->country_name_pattern)(?:[^a-zA-Z]|$)/i", $name . " ", $m);
			if(count($m) && !empty($m[1]) && isset($this->country_name_code[strtolower($m[1])])){
				$shipping_arr[$this->country_name_code[strtolower($m[1])]] = $this->country_name_code[strtolower($m[1])];
			}else {
				preg_match_all("/(?:[^a-zA-Z]|\s)($this->country_code)(?:[^a-zA-Z]|\s)/", $name. " ", $m);
				if(count($m) && !empty($m[1]) && is_array($m[1])){
					foreach($m[1] as $cc){
						if($cc == strtoupper($cc)){
							$cc = strtolower($cc);
							$shipping_arr[$this->country_code_arr[$cc]] = $this->country_code_arr[$cc];
						}
					}
				}
			}
			return strtolower(implode(",", $shipping_arr));
		}
		
		function shippingCountryFromNameTail($targetCountryExt,$name){
			preg_match("/[^a-zA-Z]+($this->country_name_pattern)(?:[^a-zA-Z]|$)/i", $name . " ", $m);
			if(count($m) && !empty($m[1]) && isset($this->country_name_code[strtolower($m[1])])){
				$shipping_arr[$this->country_name_code[strtolower($m[1])]] = $this->country_name_code[strtolower($m[1])];
			}else {
				preg_match_all("/(?:[^a-zA-Z]|\s|^)($this->country_code)(?:[^a-zA-Z]|\s|$)/", $name. " ", $m);
				if(count($m) && !empty($m[1]) && is_array($m[1])){
					foreach($m[1] as $cc){
						if($cc == strtoupper($cc)){
							$cc = strtolower($cc);
							$shipping_arr[$this->country_code_arr[$cc]] = $this->country_code_arr[$cc];
						}
					}
				}
			}
		}
		
		function shippingCountryFromNetwork($program_info){
			$shipping_country = '';
			if(!$this->network_allow_country[$program_info['AffId']]){
				$shipping_country = implode(",", $this->network_allow_country[$program_info['AffId']]);
			}
			return $shipping_country;
		}
		
		function shippingCountryGlobal(){
			return '';
		}
		
	
		
		//AFFID:6,10,15,22,28,29,46,58,124,163,177,188,196,197,533,539,557,604,722,2001,2002,2007,2021,2022,2024,2025,2030,2034,2043,2044,2047,2048,2049
		function commissionDefault($commissionTxt){
			$commission = currency_match_str($commissionTxt);
			print_r($commission);
			$returnData = select_commission_used($commission);
			print_r($returnData);
			return $returnData;
		}
		
		//AFFID:240
		function commissionINR($commissionTxt){
			$commission = currency_match_str($commissionTxt);
			$returnData = select_commission_used($commission,'INR');
			return $returnData;
		}
		
		//AFFID:395
		function commissionUSD($commissionTxt){
			$commission = currency_match_str($commissionTxt);
			$returnData = select_commission_used($commission,'USD');
			return $returnData;
		}
		
		/*
		 * AFFID:13,14,18,34,208
		 * replace span
		 */
		function commissionRemoveSpan($commissionTxt){
			$commissionTxt = str_replace('<span>', '', $commissionTxt);
			$commission = currency_match_str($commissionTxt);
			//currency_match_str方法负责解析数字和百分数。如果$commissionTxt有两个数字，则返回的数组中有两个值。当commissionType是value时，参数中数值和货币单位必须连在一起，否则会把货币单位解析没了
			$returnData = select_commission_used($commission);
			//select_commission_used分析出数字和货币单位
			return $returnData;
		}
		
		/*
		 * AFFID:5,35,429,469,514,769,770,2036,2037,2038,2039
		 * From html
		 */
		function commissionFromHtml($commissionTxt){
			$objDomTools = new DomTools();
			$objDomTools->flush();
			$objDomTools->setContent($commissionTxt);
			$objDomTools->select('tr');
			$trHtmlArr = $objDomTools->get();
			$commission = array();
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
			return $returnData;
		}
		
		/*
		 * AFFID:1,548,2003,2031
		 * Division By Pipe(|)
		 */
		function commissionDivisionByPipe($commissionTxt){
			$commission = $tmp_arr = array();
			$tmp_arr = explode("|", $commissionTxt);
			foreach($tmp_arr as $v){
				$commission[] = substr($v, strrpos($v, ':') ? strrpos($v, ':') + 1 : 0);
			}
			$returnData = select_commission_used($commission);
			return $returnData;
		}
		
		function commissionDivisionByPipeINR($commissionTxt){
			$commission = $tmp_arr = array();
			$tmp_arr = explode("|", $commissionTxt);
			foreach($tmp_arr as $v){
				$commission[] = substr($v, strrpos($v, ':') ? strrpos($v, ':') + 1 : 0);
			}
			$returnData = select_commission_used($commission,'INR');
			return $returnData;
		}
		
		/*
		 * AFFID:679
		 * Division By Semicolon(;)
		 */
		function commissionDivisionBySemicolon($commissionTxt){
			$tmp_arr = explode(";", $commissionTxt);
			$commission = array();
			foreach($tmp_arr as $v){
				$commission[] = substr($v, strrpos($v, ':') ? strrpos($v, ':') + 1 : 0);
			}
			$returnData = select_commission_used($commission);
			return $returnData;
		}
		
		/*
		 * AFFID:596
		 * Division By Slash(/)
		 */
		function commissionDivisionBySlash($commissionTxt){
			$tmp_arr = explode("/", $commissionTxt);
			$commission = array();
			foreach($tmp_arr as $v){
				$commission[] = substr($v, strrpos($v, ':') ? strrpos($v, ':') + 1 : 0);
			}
			$returnData = select_commission_used($commission);
			return $returnData;
		}
		
		/*
		 * AFFID:7
		 * Unique pattern:Sale Comm,Lead Comm,Hit Comm
		 */
		function commissionFromPreg($commissionTxt){
			$arr  = explode('|', $commissionTxt);
			preg_match_all('#Sale Comm:(.*)#', $arr[0],$sale);
			preg_match_all('#Lead Comm:(.*)#', $arr[1],$lead);
			preg_match_all('#Hit Comm:(.*)#', $arr[2],$hit);
			if (!empty($sale[1][0])){
				$commissionTxt = $sale[1][0];
			}elseif (!empty($lead[1][0])){
				$commissionTxt = $lead[1][0];
			}elseif (!empty($hit[1][0])){
				$commissionTxt = $hit[1][0];
			}
			$commission = currency_match_str($commissionTxt);
			$returnData = select_commission_used($commission);
			return $returnData;
		}
		
		/*
		 * AFFID:115
		 * Replace Percent to %
		 */
		function commissionReplacePercent($commissionTxt){
			$commissionTxt = str_replace('Percent', '%', $commissionTxt);
			$commission = currency_match_str($commissionTxt);
			$returnData = select_commission_used($commission);
			return $returnData;
		}
		
		/*
		 * AFFID:65,425,427,2026,2027,2029
		 * Unique pattern and EUR
		 */
		function commissionEURFromArr($commissionTxt){
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
			return $returnData;
		}
		
		
		/*
		 * AFFID:2028
		 * Unique pattern and RUB
		 */
		function commissionRUBFromArr($commissionTxt){
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
			$returnData = select_commission_used($commission,'RUB');
			return $returnData;
		}
		
		/*
		 * Unique pattern and INR
		 */
		function commissionINRFromArr($commissionTxt){
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
			$returnData = select_commission_used($commission,'INR');
			return $returnData;
		}
		
		/*
		 * Unique pattern and USD
		 */
		function commissionUSDFromArr($commissionTxt){
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
			$returnData = select_commission_used($commission,'USD');
			return $returnData;
		}
		
		/*
		 * AFFID:52
		 * Unique pattern and GBP
		 */
		function commissionGBPFromArr($commissionTxt){
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
			$returnData = select_commission_used($commission,'GBP');
			return $returnData;
		}
		
		/*
		 * AFFID:426
		 * Unique pattern and CHF
		 */
		function commissionCHFFromArr($commissionTxt){
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
			$returnData = select_commission_used($commission,'CHF');
			return $returnData;
		}
		
		/*
		 * AFFID:418,500
		 * Currency Match and EUR
		 */
		function commissionEURFromCurrencyMatch($commissionTxt){
			list($saleStr,$leadStr,$clickStr) = explode(',',$commissionTxt);
			$saleArr = currency_match_str($saleStr);
			$leadArr = currency_match_str($leadStr);
			$clickArr = currency_match_str($clickStr);
			$commission = array();
			if(array_sum($saleArr) > 0){
				foreach ($saleArr as $k=>$v){
					$commission[$k] = $v.'%';
				}
			}elseif (array_sum($leadArr) > 0){
				foreach ($leadArr as $k=>$v){
					$commission[$k] = $v;
				}
			}elseif (array_sum($clickArr) > 0){
				foreach ($clickArr as $k=>$v){
					$commission[$k] = $v;
				}
			}
	        $returnData = select_commission_used($commission,'EUR');
	        return $returnData;
		}
		
		/*
		 * AFFID:26
		 * Currency Match and GBP
		 */
		function commissionGBPFromCurrencyMatch($commissionTxt){
			list($saleStr,$leadStr,$clickStr) = explode(',',$commissionTxt);
			$saleArr = currency_match_str($saleStr);
			$leadArr = currency_match_str($leadStr);
			$clickArr = currency_match_str($clickStr);
			$commission = array();
			if(array_sum($saleArr) > 0){
				foreach ($saleArr as $k=>$v){
					$commission[$k] = $v.'%';
				}
			}elseif (array_sum($leadArr) > 0){
				foreach ($leadArr as $k=>$v){
					$commission[$k] = $v;
				}
			}elseif (array_sum($clickArr) > 0){
				foreach ($clickArr as $k=>$v){
					$commission[$k] = $v;
				}
			}
	        $returnData = select_commission_used($commission,'GBP');
	        return $returnData;
		}
		
		/*
		 * AFFID:491
		 * Currency Match and CHF
		 */
		function commissionCHFFromCurrencyMatch($commissionTxt){
			list($saleStr,$leadStr,$clickStr) = explode(',',$commissionTxt);
			$saleArr = currency_match_str($saleStr);
			$leadArr = currency_match_str($leadStr);
			$clickArr = currency_match_str($clickStr);
			$commission = array();
			if(array_sum($saleArr) > 0){
				foreach ($saleArr as $k=>$v){
					$commission[$k] = $v.'%';
				}
			}elseif (array_sum($leadArr) > 0){
				foreach ($leadArr as $k=>$v){
					$commission[$k] = $v;
				}
			}elseif (array_sum($clickArr) > 0){
				foreach ($clickArr as $k=>$v){
					$commission[$k] = $v;
				}
			}
	        $returnData = select_commission_used($commission,'CHF');
	        return $returnData;
		}
		
		/*
		 * AFFID:2
		 */
		function commissionForLinkShare($commissionTxt){
			$hasIncentive = 0;
			$isAnnotation = 0;
			$pos = strpos($commissionTxt,';');
			if($pos !== false){
				$tmpCommissionTxt = currency_match_str(substr($commissionTxt,0,$pos));
				if(intval($tmpCommissionTxt[0]) != 0 )
				{
					$commissionTxt = substr($commissionTxt,0,$pos);
					$hasIncentive = 1;
				}
				else
				{
					$isAnnotation = 1;
				}
			}
			$commission = currency_match_str($commissionTxt);
			if($isAnnotation)
			{
				if(stripos($commission[0],'%'))
					$additional = '0.000001%';
				else
					$additional = 0.0000001;
				$commission[] = $additional;
				$commission = array_unique($commission);
			}
	
			foreach($commission as $k=>$v){
				$commission[$k] = $v.'|'.$hasIncentive;
			}
			$returnData = select_commission_used($commission);
			return $returnData;
		}
		
		/*
		 * AFFID:12
		 */
		function commissionForLinkConnector($commissionTxt){
			$hasIncentive = 0;
			$pos = stripos($commissionTxt, '<br');
			if($pos !== false){
				$commissionTxt = substr($commissionTxt,0,$pos);
				$hasIncentive = 1;
			}
			$commission = currency_match_str($commissionTxt);
			foreach($commission as $k=>$v){
				$commission[$k] = $v.'|'.$hasIncentive;
			}
			$returnData = select_commission_used($commission);
			return $returnData;
		}
		
		/*
		 * AFFID:20
		 */
		function commissionForAffiliateFutureUS($commissionTxt){
			$commissionTxt = trim($commissionTxt);
	
			$lineOther = '';
	
			$pos = strpos($commissionTxt, '<br');
			$pos2 = strpos($commissionTxt, "\n");
			if($pos !== false){
				$line = substr($commissionTxt,0,$pos);
				$lineOther = substr($commissionTxt,$pos);
			}elseif($pos2 !== false){
				$line = substr($commissionTxt,0,$pos2);
				$lineOther = substr($commissionTxt,$pos2);
			}else{
				$line = $commissionTxt;
			}
			$commission = currency_match_str($line);
			if(empty($commission) && $lineOther){
				$commission = currency_match_str($lineOther);
			}
			$returnData = select_commission_used($commission);
			return $returnData;
		}

		/*
		 * AFFID：63
		 */
		function commissionForAffiliNetDE($commissionTxt){
			list($sale,$lead,$click) = explode(',',$commissionTxt);
			$commission = array();
			$regex_number = get_regex('number');
			$flag = 0;
			preg_match_all($regex_number,$sale,$m);
			if($m){
				foreach($m[0] as $k=>$v){
					$str_head = trim($m[1][$k]);
					$CommissionUsed = trim($m[2][$k]);
					$str_end = '%';
	
					$hasIncentive = 0;
					$commission[] = $str_head.$CommissionUsed.$str_end.'|'.$hasIncentive;
	
					if($CommissionUsed > 0)
						$flag = 1;
				}
			}
			if(!$flag){
				preg_match_all($regex_number,$lead,$m);
				if($m){
					foreach($m[0] as $k=>$v){
						$str_head = trim($m[1][$k]);
						$CommissionUsed = trim($m[2][$k]);
						$str_end = trim($m[3][$k]);
						$hasIncentive = 0;
						$commission[] = $str_head.$CommissionUsed.$str_end.'|'.$hasIncentive;
						if($CommissionUsed > 0)
							$flag = 1;
					}
				}
			}
			if(!$flag){
				preg_match_all($regex_number,$click,$m);
				if($m){
					foreach($m[0] as $k=>$v){
						$str_head = trim($m[1][$k]);
						$CommissionUsed = trim($m[2][$k]);
						$str_end = trim($m[3][$k]);
						$hasIncentive = 0;
						$commission[] = $str_head.$CommissionUsed.$str_end.'|'.$hasIncentive;
					}
				}
			}
			$returnData = select_commission_used($commission,'EUR');
			return $returnData;
		}
		
		/*
		 * AFFID:64
		 */
		function commissionForEffiliation($commissionTxt){
			$commissionTxt = str_replace('\r\n',';',$commissionTxt);
			$commissionTxt = str_replace('\n\r',';',$commissionTxt);
			$commissionTxt = str_replace('\r',';',$commissionTxt);
			$commissionTxt = str_replace('\n',';',$commissionTxt);
			$commission = explode(";", $commissionTxt);
			$returnData = select_commission_used($commission);
			return $returnData;
		}
		
		/*
		 * AFFID:360
		 */
		function commissionForAdcell($commissionTxt){
			$commissionTxt = str_replace(',','.',$commissionTxt);
			if(strpos($commissionTxt,'|') !== false){
				if(strpos($commissionTxt,'Sale:') !== false){
					$tmp = explode('|',$commissionTxt);
					foreach($tmp as $v){
						if(strpos($v,'Sale:') !== false){
							$commissionTxt = $v;
						}
					}
				}else{
					$pos = strpos($commissionTxt,'|');
					$commissionTxt = substr($commissionTxt,0,$pos);
				}
			}
			$commission = currency_match_str($commissionTxt);
			$returnData = select_commission_used($commission);
			return $returnData;
		}
		
		/*
		 * AFFID:503
		 */
		function commissionForPublicIdeas($commissionTxt){
	        $commissionTxt = strip_tags($commissionTxt);
	        $commissionTxt = str_replace(',','.',$commissionTxt);
	        $commission = currency_match_str($commissionTxt);
	        $returnData = select_commission_used($commission);
	        return $returnData;
	    }
	    
	    /*
		 * AFFID:152
		 */
	    function commissionForBelboon($commissionTxt){
			$commission = array();
			$commissionTxt = trim($commissionTxt);
			$arr = explode(',',$commissionTxt);
			list(,$saleminpercent_v) = explode(':',trim($arr[0]));
			list(,$salemaxpercent_v) = explode(':',trim($arr[1]));
			list(,$saleminfix_v) = explode(':',trim($arr[2]));
			list(,$salemaxfix_v) = explode(':',trim($arr[3]));
			list(,$leadmin_v) = explode(':',trim($arr[4]));
			list(,$leadmax_v) = explode(':',trim($arr[5]));
			list(,$clickmin_v) = explode(':',trim($arr[6]));
			list(,$clickmax_v) = explode(':',trim($arr[7]));
			list(,$viewmin_v) = explode(':',trim($arr[8]));
			list(,$viewmax_v) = explode(':',trim($arr[9]));
	
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
			$returnData = select_commission_used($commission,'EUR');
			return $returnData;
		}
		
		function supportTypeDefault(){
	    	return 'All';
		}
		
		function supportTypeContent(){
	    	return 'Content';
		}
		
		function categoryDefault($categoryExt,$affid)
		{
			global $objProgram;
			$date = date('Y-m-d H:i:s');
			if (empty($categoryExt))
				return '';
			if($affid == 46)
			{
				$categoryExt = explode(';',$categoryExt);
			}
			else
			{
				$categoryExt = explode(',',$categoryExt);
			}
			
			$cate_id = array();
			foreach ($categoryExt as $cate)
			{
				$cate = htmlspecialchars_decode(trim($cate,"-, \t\n\r\0\x0B"));
				if(!empty($cate))
				{
					$sql = "SELECT IdRelated,AffId,UpdateTime FROM category_ext WHERE `Name` = '" . addslashes($cate) . "'";
					$cate_tmp = $objProgram->objMysql->getFirstRow($sql);
					if($cate_tmp)
					{
						$cate_id = explode(',',$cate_tmp['IdRelated']);
						$cate_id = array_unique(array_filter($cate_id));
						//updated recently
						if((strtotime($date)-strtotime($cate_tmp['UpdateTime'])) < 6*3600)
						{
							$affid .= ',' .$cate_tmp['AffId'];
							$affid_arr  = explode(',',$affid);
							$affid_arr = array_unique(array_filter($affid_arr));
							asort($affid_arr);
							$affid = trim(implode(',',$affid_arr),"-, \t\n\r\0\x0B");
						}
	//					echo $cate . ':'.$affid.PHP_EOL;
						$sql = "update category_ext set `AffId`='{$affid}',UpdateTime='{$date}' where `Name`='". addslashes($cate)."'";
						$objProgram->objMysql->query($sql);
					}
					else
					{
						$sql = 'SELECT MAX(ID) FROM category_ext';
						$id = $objProgram->objMysql->getFirstRowColumn($sql)+1;
						$sql = "INSERT IGNORE INTO category_ext (`ID`,`Name`,`AffId`,`UpdateTime`) VALUES ('$id','" . addslashes($cate) . "','{$affid}','{$date}')";
						$objProgram->objMysql->query($sql);
					}
				}
			}
			asort($cate_id);
			$cate_id = trim(implode(',',$cate_id),"-, \t\n\r\0\x0B");
			return $cate_id;
		}
		
		/*
		 * defaultUrlFromLinks
		 * $prgm_info["AffId"] != 1 && $prgm_info["AffId"] != 160
		 * getDefaultLinkByPrgm
		 * $prgm_info = array()
		 * reqired (AffId, IdInAff, Name, Domain)
		 * choose default link
		 */
		function affDefaultUrlDefault($program_info){
	    	global $objProgram;
			if (empty($program_info["AffId"]) || !is_numeric($program_info["AffId"]) || !$this->checkAffLinkDB($program_info["AffId"]))
				return '';
			//http code must be 200
			//$sql = "SELECT AffLinkId LinkId,AffMerchantId, LinkHtmlCode, LinkName, LinkEndDate, LinkAffUrl, LinkDesc, LinkImageUrl, LinkOriginalUrl, Domain, FinalUrl, HttpCode, LinkPromoType FROM `affiliate_links_{$program_info["AffId"]}` WHERE HttpCode = 200 AND (LinkEndDate = 0 || LinkEndDate > '".date("Y-m-d", strtotime("30 days"))."') AND (LinkStartDate = 0 || LinkStartDate < '".date("Y-m-d H:i:s")."') AND IsActive = 'YES' and AffMerchantId = '" . addslashes($program_info['IdInAff']) ."'";
			$sql = "SELECT AffLinkId LinkId,AffMerchantId, LinkHtmlCode, LinkName, LinkEndDate, LinkAffUrl, LinkDesc, LinkImageUrl, LinkOriginalUrl, Domain, FinalUrl, HttpCode, LinkPromoType FROM `affiliate_links_{$program_info["AffId"]}` WHERE HttpCode = 200 AND (LinkEndDate = 0 || LinkEndDate > '".date("Y-m-d", strtotime("30 days"))."') AND (LinkStartDate = 0 || LinkStartDate < '".date("Y-m-d H:i:s")."') AND IsActive = 'YES' and AffMerchantId = '" . addslashes($program_info['IdInAff']) ."'";
			$links_arr = $objProgram->objPendingMysql->getRows($sql);
			$affurl = $this->defaultUrlPickUrl($program_info,$links_arr);
			if($affurl) {
				foreach ($links_arr as $value) {
					if (stripos($value['LinkAffUrl'],$affurl) !== false){
						$id = 'links___' . addslashes($value['LinkId']);
						$sql = "update r_domain_program set AffLinkId='$id' where PID='{$program_info['ProgramId']}'";
						$objProgram->objMysql->query($sql);
					}
				}
			}
			return $affurl;
		}
		
		/*
		 *Affid:1
		 */
		function affDefaultUrlForCJ($program_info){
	    	if(strcasecmp($program_info['SupportDeepUrl'],'YES') === 0) {
			    return $this->deepUrlTplForCJ($program_info);
		    }
		    else{
	    		return $this->affDefaultUrlDefault($program_info);
		    }
		}
		
		/*
		 * AFFID:12
		 */
		
		function affDefaultUrlFromProduct($program_info){
	    	global $objProgram;
			if (empty($program_info["AffId"]) || !is_numeric($program_info["AffId"]) || !$this->checkAffProductDB($program_info["AffId"]))
					return '';
			$sql = "SELECT AffProductId LinkId,AffMerchantId, ProductUrl AS LinkAffUrl, ProductDestUrl AS LinkOriginalUrl, ProductName AS LinkName , ProductDesc AS LinkDesc FROM  `affiliate_product_{$program_info["AffId"]}` WHERE AffMerchantId = '".addslashes($program_info['IdInAff'])."'  AND IsActive = 'YES'";
			$links_arr = $objProgram->objPendingMysql->getRows($sql);
			$affurl = $this->defaultUrlPickUrl($program_info,$links_arr);
			if($affurl) {
				foreach ($links_arr as $value) {
					if (stripos($value['LinkAffUrl'],$affurl) !== false){
						$id = 'product___' . addslashes($value['LinkId']);
						$sql = "update r_domain_program set AffLinkId='$id' where PID='{$program_info['ProgramId']}'";
						$objProgram->objMysql->query($sql);
					}
				}
			}
			return $affurl;
		}
		
		/*
		 * Affid:152
		 */
		function affDefaultUrlForBelboon($program_info){
	    	$affurl = $this->affDefaultUrlDefault($program_info);
	    	if(!empty($affurl)){
	    		$program_info['AffDefaultUrl'] = $affurl;
	    		$affurl = $this->deepUrlTplUrlDefault($program_info);
		    }
		    return $affurl;
		}
		
		//$p_v["AffId"] == 188 || in_array($p_v["AffId"], $this->aff_tt) $links_arr["AffDefaultUrl"] 不为空
		/*
		 * TradeTracker AFFID:52,65,425,426,427,2026,2027,2028,2029
		 * */
		//empty($links_arr["AffDefaultUrl"]) && $this->aff_url_pattern[$p_v["AffId"]]["NeedAffDefaultUrl"] == "NO"

		/*
		 * default tpl
		 * tplUrlDefault($tpl, $program_info = array())
		 * required
		 * $program_info = array("AffId" =>
		 * 					"IdInAff" =>
		 * 					"AffDefaultUrl" =>
		 * 					)
		 *
		 * need replace val: [AFFDOMAINS], [IDINAFF], [DEFAULTURL], [PARA]
		 * need check connect symbol: [/], [?|&]
		 *
		 * specil aff :
		 * 			TradeDoubler(5,27,35,135,415, 469) has two type tpl
		 * 			LinkShare(2) need replace idinaff
		 * 			CJ(1) choose random outgoing domain to replace [AFFDOMAINS]
		 *			TradeTracker(52,65,425,426,427,2026,2027,2028,2029)
		 */
		function deepUrlTplUrlDefault($program_info){
	    	$tpl = $this->aff_url_pattern[$program_info["AffId"]]["TplDeepUrlTpl"];
	    	return $this->getTpl($program_info,$tpl);
		}
		
		function deepUrlTplForCJ($program_info){
	    	$tpl = strcasecmp($program_info['SupportDeepUrl'],'YES') == 0?'http://[AFFDOMAINS]/links/[SITEIDINAFF]/type/dlg/sid/[SUBTRACKING]/[PURE_DEEPURL]':$this->aff_url_pattern[$program_info["AffId"]]["TplDeepUrlTpl"];
		    $tpl = str_replace("[AFFDOMAINS]", $this->pickRandCJDomain(), $tpl);
		    $tpl = $this->getTpl($program_info,$tpl);
		    if(strpos($tpl, '[SITEIDINAFF]') !== false && strcasecmp($program_info['SupportDeepUrl'],'YES') != 0){
		    	$tpl = '';
		    }
		    return $tpl;
		}
		
		/*
		 * Affid:2
		 * LS idinaff need replace
		 */
		function deepUrlTplForLinkShare($program_info){
			$tpl = $this->aff_url_pattern[$program_info["AffId"]]["TplDeepUrlTpl"];
			$program_info["IdInAff"] = preg_replace("/_\d*/", "", $program_info["IdInAff"]);
			return $this->getTpl($program_info,$tpl);
		}
		
		/*
		 * Affid:5,35,415,429,469,667,769,770,2036,2037,2038,2039
		 * TradeDoubler
		 * http://clkuk.tradedoubler.com/click?p=222731&a=1781705&g=20574256
		 * http://clkuk.tradedoubler.com/click?p(221710)a(1781705)g(20561586)
		 */
		function deepUrlTplForTradeDoubler($program_info){
			$tpl = $this->aff_url_pattern[$program_info["AffId"]]["TplDeepUrlTpl"];
			$tpl_arr = explode("[O|R]", $this->aff_url_pattern[$program_info["AffId"]]["TplDeepUrlTpl"]);
			if(count($tpl_arr)){
				if(stripos($program_info["AffDefaultUrl"], ")") !== false && stripos($program_info["AffDefaultUrl"], "(") !== false){
					$tpl = $tpl_arr[0];
				}else{
					$tpl = $tpl_arr[1];
				}
			}
			return $this->getTpl($program_info,$tpl);
		}
		
		/*
		 * Affid:604
		 */
		function deepUrlTplForAffilae($program_info){
			$tpl = '';
	    	if(!empty($program_info["SecondIdInAff"])){
	    		$tpl = "[PURE_DEEPURL][?|&]#".urlencode($program_info["SecondIdInAff"]);
		    }
		    return $tpl;
		}
		
		/*
		 * Affid：52,65,425,426,427,2026,2027,2028,2029
		 * Tradetracker  tt=[PARA]
		 * 规则：
		 *		Default url： http://tc.tradetracker.net/?c=3728&m=155981&a=62862&r=&u=
		 *		Deeplink url template :	http://tc.tradetracker.net/?c=3728&m=155981&a=62862&r=&u=[ENCODE_URI]
		 * 不规则：
		 *		tracking link：http://www.dorisandco.co.uk/home/?tt=8487_321561_62862_&r=
		 *		Default url: http://www.dorisandco.co.uk/home/?tt=8487_321561_62862_[SUBTRACKING]&r=
		 *		deep tracking link template: http://www.dorisandco.co.uk/home/?tt=8487_321561_62862_[SUBTRACKING]&r=[ENCODE_URI]
		 */
		function deepUrlTplForTradetracker($program_info){
			$tpl = $this->aff_url_pattern[$program_info["AffId"]]["TplDeepUrlTpl"];
			$tpl_arr = explode("[O|R]", $tpl);
			if(count($tpl_arr)){
				if(stripos($program_info["AffDefaultUrl"], "tradetracker.net") !== false){
					$tpl = $tpl_arr[0];
				}else{
					$tpl = $tpl_arr[1];
					preg_match("/[\\?&]{1}tt=([^&?\\/]*)/i", $program_info["AffDefaultUrl"], $m);
					if(isset($m[1])){
						$tt_para = $m[1];
						$tt_para = str_replace("[SUBTRACKING]", "", $tt_para);
					}
				}
			}
			if(strpos($tpl, "[DEFAULTURL]") !== false){
				$pattern_val = true;
			}else{
				$pattern_val = false;
			}
			
			/*
			 * [/|?|&url=[DEEPURL]] 	=> array[1][2][3]
			 * [/subId/[SUBTRACKING]]	=> array[4][5]
			 * [url([DEEPURL])]			=> array[6][7]
			 *
			 */
			preg_match_all("/\\[([&?\\/])([\w]+)=([^&?()\\/]+)\\]|\\[\\/([\w]+)\\/([^&?()\\/]+)\\]|\\[([\w]+)\\(([^)]+)\\)\\]/", $tpl, $m);
			if(count($m)){
				if(count($m[1]) && count($m[2]) && count($m[3])){
					foreach($m[1] as $k => $v){
						if(strlen($v) > 0){
							$para = $m[2][$k];
							if($pattern_val && preg_match("/([?&\\/]{1}{$para}=[^&?\\/]*)[?&\\/]?/i", $program_info["AffDefaultUrl"], $mm)){
								$program_info["AffDefaultUrl"] = str_replace("{$mm[1]}", "", $program_info["AffDefaultUrl"]);
							}
							$tpl = str_replace($m[0][$k], "{$m[1][$k]}{$m[2][$k]}={$m[3][$k]}", $tpl);
							if(isset($tt_para))
								$tpl = str_replace("tt=[SUBTRACKING]", "tt={$tt_para}[SUBTRACKING]", $tpl);
						}
					}
				}
				if(count($m[4]) && count($m[5])){
					foreach($m[4] as $k => $v){
						if(strlen($v) > 0){
							$para = $m[4][$k];
							if($pattern_val && preg_match("/[?&\\/]?({$para}\\/[^\\/]*)\\/?/i", $program_info["AffDefaultUrl"], $mm)){
								$program_info["AffDefaultUrl"] = str_replace("{$mm[1]}", "", $program_info["AffDefaultUrl"]);
							}
							$tpl = str_replace($m[0][$k], "[/]{$m[4][$k]}/{$m[5][$k]}", $tpl);
						}
					}
				
				}
				if(count($m[6]) && count($m[7])){
					foreach($m[6] as $k => $v){
						if(strlen($v) > 0){
							$para = $m[6][$k];
							if($pattern_val && preg_match("/[?&\\/()]?({$para}\\([^)]+\\))/i", $program_info["AffDefaultUrl"], $mm)){
								$program_info["AffDefaultUrl"] = str_replace("{$mm[1]}", "", $program_info["AffDefaultUrl"]);
							}
							$tpl = str_replace($m[0][$k], "{$m[6][$k]}({$m[7][$k]})", $tpl);
						}
					}
				
				}
			}
			return $this->pureUrl($tpl, array("[IDINAFF]" => $program_info["IdInAff"], "[DEFAULTURL]" => $program_info["AffDefaultUrl"]));
		}
		
		function getTpl($program_info,$tpl){
	    	if(strpos($tpl, "[DEFAULTURL]") !== false){
                $pattern_val = true;
            }else{
                $pattern_val = false;
            }
            /*
			 * [/|?|&url=[DEEPURL]] 	=> array[1][2][3]
			 * [/subId/[SUBTRACKING]]	=> array[4][5]
			 * [url([DEEPURL])]			=> array[6][7]
			 *
			 */
			preg_match_all("/\\[([&?\\/])([\w]+)=([^&?()\\/]+)\\]|\\[\\/([\w]+)\\/([^&?()\\/]+)\\]|\\[([\w]+)\\(([^)]+)\\)\\]/", $tpl, $m);
			if(count($m)){
				if(count($m[1]) && count($m[2]) && count($m[3])){
					foreach($m[1] as $k => $v){
						if(strlen($v) > 0){
							$para = $m[2][$k];
							if($pattern_val && preg_match("/([?&\\/]{1}{$para}=[^&?\\/]*)[?&\\/]?/i", $program_info["AffDefaultUrl"], $mm)){
								$program_info["AffDefaultUrl"] = str_replace("{$mm[1]}", "", $program_info["AffDefaultUrl"]);
							}
							$tpl = str_replace($m[0][$k], "{$m[1][$k]}{$m[2][$k]}={$m[3][$k]}", $tpl);
						}
					}
				}
				if(count($m[4]) && count($m[5])){
					foreach($m[4] as $k => $v){
						if(strlen($v) > 0){
							$para = $m[4][$k];
							if($pattern_val && preg_match("/[?&\\/]?({$para}\\/[^\\/]*)\\/?/i", $program_info["AffDefaultUrl"], $mm)){
								$program_info["AffDefaultUrl"] = str_replace("{$mm[1]}", "", $program_info["AffDefaultUrl"]);
							}
							$tpl = str_replace($m[0][$k], "[/]{$m[4][$k]}/{$m[5][$k]}", $tpl);
						}
					}
				
				}
				if(count($m[6]) && count($m[7])){
					foreach($m[6] as $k => $v){
						if(strlen($v) > 0){
							$para = $m[6][$k];
							if($pattern_val && preg_match("/[?&\\/()]?({$para}\\([^)]+\\))/i", $program_info["AffDefaultUrl"], $mm)){
								$program_info["AffDefaultUrl"] = str_replace("{$mm[1]}", "", $program_info["AffDefaultUrl"]);
							}
							$tpl = str_replace($m[0][$k], "{$m[6][$k]}({$m[7][$k]})", $tpl);
						}
					}
				}
			}
			return $this->pureUrl($tpl, array("[IDINAFF]" => $program_info["IdInAff"], "[DEFAULTURL]" => $program_info["AffDefaultUrl"]));
		}
		
		
		
		function defaultUrlPickUrl($program_info,$links_arr){
			foreach($links_arr as $v){
				if(strlen($v["LinkAffUrl"])){
					$affurl = $v["LinkAffUrl"];
				}else{
					preg_match("/<a[^\\/><]+href=(\"|')(.*)\\1/Ui", $v["LinkHtmlCode"], $matches);
					$affurl = isset($matches[2]) ? $matches[2] : "";
				}
				if($affurl){
					if(in_array($this->getSLD($affurl),$this->network_keywords)){
						if($program_info['AffId'] == 152){
							// http://www1.belboon.de/adtracking/0342f308cd7a037d7b0049a6.html/deeplink=[DeepLink-Url]
							if(stripos($affurl, "[DeepLink-Url]") !== false){
								$affurl = str_ireplace("/deeplink=[DeepLink-Url]", "", $affurl);
								$affurl = preg_replace("/html.*/i", "html", $affurl);
							}
						}
						
						/*
						 * #1, LinkName LinkDesc LinkHtmlCode contain program domain	--- 20 ~ 40
						 * #2, LinkName LinkDesc LinkHtmlCode contain program name		--- 10 ~ 20
						 * #3, LinkName LinkDesc LinkHtmlCode contain #homepage# word	--- 10
						 * #4, LinkName LinkDesc contain #logo# word					--- 10
						 * #5, text links > image 										--- 5
						 * #6, no expire > expired always								--- 5
						 *
						 */
						$weight = 0;
						
						#1
						if(stripos($v["LinkName"], $program_info["Domain"]) !== false){
							$weight += (strlen($v["LinkName"]) >= 20) ? 20 : 40 - strlen($v["LinkName"]);
						}elseif(stripos($v["LinkDesc"], $program_info["Domain"]) !== false){
							$weight += (strlen($v["LinkDesc"]) >= 20) ? 20 : 40 - strlen($v["LinkDesc"]);
						}elseif(stripos(strip_tags($v["LinkHtmlCode"]), $program_info["Domain"]) !== false){
							$weight += (strlen(strip_tags($v["LinkHtmlCode"])) >= 20) ? 20 : 40 - strlen(strip_tags($v["LinkHtmlCode"]));
						}
						
						#2
						if(stripos($v["LinkName"], $program_info["Name"]) !== false){
							$weight += (strlen($v["LinkName"]) >= 10) ? 10 : 20 - strlen($v["LinkName"]);
						}elseif(stripos($v["LinkDesc"], $program_info["Name"]) !== false){
							$weight += (strlen($v["LinkDesc"]) >= 10) ? 10 : 20 - strlen($v["LinkDesc"]);
						}elseif(stripos(strip_tags($v["LinkHtmlCode"]), $program_info["Name"]) !== false){
							$weight += (strlen(strip_tags($v["LinkHtmlCode"])) >= 10) ? 10 : 20 - strlen(strip_tags($v["LinkHtmlCode"]));
						}
						
						#3
						if(preg_match("/\bhome *page\b/i", $v["LinkName"]) || preg_match("/\bhome *page\b/i", $v["LinkDesc"]) || preg_match("/\bhome *page\b/i", strip_tags($v["LinkHtmlCode"]))){
							$weight += 10;
						}
						
						#4
						if(preg_match("/\blogo\b/i", $v["LinkName"]) || preg_match("/\blogo\b/i", $v["LinkDesc"])){
							$weight += 10;
						}
						
						#5
						if(strlen($v["LinkImageUrl"]) == 0){
							$weight += 5;
						}
						
						#6
						if($v["LinkEndDate"] == "0000-00-00 00:00:00"){
							$weight += 5;
						}
						$tmp_arr[$weight] = $affurl;
					}
				}
			}
			ksort($tmp_arr);
			return array_pop($tmp_arr);
		}
		
		function checkAffLinkDB($affid){
	    	global $objProgram;
			$hasDB = false;
			if(intval($affid)){
				$hasDB = $objProgram->objPendingMysql->isTableExisting("affiliate_links_$affid");
			}
			return $hasDB;
		}
		
		function pickRandCJDomain(){
			$domain_arr = array("www.jdoqocy.com", "www.anrdoezrs.net", "www.kqzyfj.com", "www.tqlkg.com", "www.dpbolvw.net", "www.tkqlhce.com", "www.qksrv.net");
			return $domain_arr[array_rand($domain_arr)];
		}
	
		function checkAffProductDB($affid){
	    	global $objProgram;
			$hasDB = false;
			if(intval($affid)){
				$hasDB = $objProgram->objPendingMysql->isTableExisting("affiliate_product_$affid");
			}
			return $hasDB;
		}
		
		//replace internal symbol
		function pureUrl($url, $symbol_arr = array()){
			foreach($symbol_arr as $k => $v){
				$url = str_ireplace($k, $v, $url);
			}
			if(strpos($url, 'http') === 0){
				if(($tmp_pos = strpos($url, '[/]')) !== false){
				    if(substr($url, $tmp_pos - 1, 1) == '/'){
				        $url = str_replace('[/]', '', $url);
				    }else{
				        $url = str_replace('[/]', '/', $url);
				    }
				}
			}
			$url = preg_replace("/\\s/", "", $url);
			return $url;
		}
		
		/*
		 * get second level domain
		 */
		function getSLD($domain)
		{
			$sld = '';
			preg_match("/([^\.]*)(" . implode("|", $this->top_domain_arr) . ")$/mi", $domain, $matches);
			if (isset($matches[1]) && strlen($matches[1])) {
				$sld = empty($matches[1])?'':$matches[1];
			}
			return $sld;
		}
		
		function rankDefault($pid){
	    	global $objProgram;
			$sql = "SELECT a.DID,a.PID,b.AffId,b.CommissionType,b.CommissionUsed,b.CommissionCurrency,b.DeniedPubCode,c.Domain,a.AffDefaultUrl,a.DeepUrlTpl,b.SupportDeepUrl,b.OutGoingUrl,c.countrycode AS d_countrycode,b.countrycode AS p_countrycode,b.ShippingCountry FROM r_domain_program a INNER JOIN program_intell b ON a.pid = b.programid INNER JOIN domain c ON a.DID = c.id WHERE b.isactive = 'active' AND a.status = 'active' AND b.programid = $pid";
			$prgm_arr = $objProgram->objMysql->getFirstRow($sql);
			
			$order = 0.0;
			if(!empty($prgm_arr))
			{
				if (!strlen ($prgm_arr["AffDefaultUrl"]) && !strlen ($prgm_arr["DeepUrlTpl"]))
					return $order;
	
				$prgm_arr["LimitAccount"] = array ();
				//check limited aff OR program
				if (isset($this->block_rel["aff"][$prgm_arr["AffId"]]))
				{
					$prgm_arr["LimitAccount"] = count ($prgm_arr["LimitAccount"]) ? array_merge ($prgm_arr["LimitAccount"] , $this->block_rel["aff"][$prgm_arr["AffId"]]) : $this->block_rel["aff"][$prgm_arr["AffId"]];
				}
				if (isset($this->block_rel["program"][$prgm_arr["PID"]]))
				{
					$prgm_arr["LimitAccount"] = count ($prgm_arr["LimitAccount"]) ? array_merge ($prgm_arr["LimitAccount"] , $this->block_rel["program"][$prgm_arr["PID"]]) : $this->block_rel["program"][$prgm_arr["PID"]];
				}
				
				#1
				if ($prgm_arr["AffId"] == "177")
					$order -= 2000;
				
				#2
				if (strlen ($prgm_arr["DeepUrlTpl"]))
				{
					$order += 100;
				} elseif (strlen ($prgm_arr["AffDefaultUrl"]))
				{
					$order += 10;
				}
	
				if (strtolower ($prgm_arr["SupportDeepUrl"]) == "yes")
				{
					$order += 20;
				} elseif (strtolower ($prgm_arr["SupportDeepUrl"]) == "unknown")
				{
					$order += 19.5;
					if (strlen ($prgm_arr["DeepUrlTpl"]))
					{
						$order += 0.5;
					}
				} else
				{
					if (strlen ($prgm_arr["DeepUrlTpl"]))
					{
						$order += 19.99;
					}
				}
	
				#3
				//click bank commission , different
				if ($prgm_arr["CommissionUsed"] == 0)
				{
					$order -= 100;
				}
				
				//error
				if ($prgm_arr["CommissionUsed"] == 100 && $prgm_arr["CommissionType"] == "Percent")
				{
					$prgm_arr["CommissionUsed"] = $prgm_arr["CommissionUsed"] / 10;
				}
	
				//exchange_rate
				if (isset($this->exchange_rate[$prgm_arr["CommissionCurrency"]]) && $prgm_arr["CommissionCurrency"] != "USD")
				{
					//echo $prgm_arr["CommissionUsed"];
					$prgm_arr["CommissionUsed"] = ($prgm_arr["CommissionUsed"] * $this->exchange_rate[$prgm_arr["CommissionCurrency"]]["ExchangeRate"]) / $this->exchange_rate["USD"]["ExchangeRate"];
				}
	
				$order += floatval ($prgm_arr["CommissionUsed"]);
				if ($prgm_arr["CommissionType"] == "Value"){
					$order += 0.1;
				}
	
				#4	//rank 101 ~ 99999999
				$tmp_rank = (isset($this->aff_rank[$prgm_arr["AffId"]]["Rank"]) && intval ($this->aff_rank[$prgm_arr["AffId"]]["Rank"])) ? round ((10 / intval ($this->aff_rank[$prgm_arr["AffId"]]["Rank"])) , 5) : 0;
				$order += ($tmp_rank / 10);
				$order *= 100000;
				$sql = "select ExtraWeight from program_order_manual where ProgramID='$pid'";
				$tmp_data = $objProgram->objMysql->getFirstRow($sql);
				if(!empty($tmp_data)){
					$order += intval($tmp_data['ExtraWeight']);
				}
				$order = intval ($order);
			} else {
				return $order;
			}
			return $order;
		}
	}