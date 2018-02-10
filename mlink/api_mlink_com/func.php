<?php
function over($str){
	echo $str."<br>\r\n";
	exit();
}

function debug($str){
	echo $str."<br>\r\n";
}

function parseArgv($argv){
	unset($argv[0]);
	$data = array();
	foreach($argv as $k=>$v){
		$v = trim($v);

		if(empty($v))
			continue;

		if(strpos($v,'=')!==false)
			list($key,$value) = explode('=',$v);
		else
			list($key,$value) = array($v,1);

		if($key[0] == '-')
			$key = substr($key,1);

		$data[$key] = $value;
	}
	return $data;
}

function getDir($dir,$only='',$last_name=false){
	if (empty($dir)) {
		return null;
	}

	$content = array();

	if(is_array($dir)){
		foreach($dir as $d){
			$tmp = getDir($d,$only,$last_name);
			$content = array_merge($content,$tmp);
		}
	}else{
		$ch = '';
		if(substr($dir,-1) != '/')
			$ch = '/';

		$dc = scandir($dir);
		foreach($dc as $k=>$v){
			if($v == '.' || $v == '..')
				continue;
			if($only=='dir' && is_dir($dir.$ch.$v)){
				$content[] = $last_name?$v:$dir.$ch.$v;
			}
			if($only=='file' && is_file($dir.$ch.$v)){
				$content[] = $last_name?$v:$dir.$ch.$v;
			}
		}
	}

	return $content;
}

function model($name){
	global $models;
	if(!isset($models[$name])){
		if(strpos($name, '/') !== false){
			$arr = explode('/',$name);
			$class = array_pop($arr);
			
			$dirPath = join('/',$arr);
			$class_file = INCLUDE_ROOT . 'lib/'.$dirPath.'/Class.' . $class . '.php';
		}else{
			$class = $name;
			$class_file = INCLUDE_ROOT . 'lib/Class.' . $class . '.php';
		}
		
		if(file_exists($class_file)){
			include_once($class_file);
			$models[$name] = new $class;
		}else{
            $models[$name] = null;
        }
	}

	return $models[$name];
}

function _trim($data){
	if(is_array($data)){
		foreach($data as $k=>$v){
			$data[$k] = _trim($v);
		}
		return $data;
	}else{
		return trim($data);
	}
}

function get_regex($type){
	$regx = '';
	switch ($type) {
		case 'number':
			$regx = '/((?:&#\d+;)|[^\d\s->\.\w,\|;]*)((?:\d+)(?:,\d+)?(?:\.\d+)?)((?:&#\d+;)|[^\d\s-<\(\)\w,\/]*)/i';
			break;
		default:
			break;
	}
	return $regx;
}

function currency_match_str($txt){
    $match = array();
	$currencyMap = currency_get_map();
   
    $cur_merge = array();
    foreach($currencyMap as $v){
        $cur_merge = array_merge($cur_merge,$v);
    }
    $regx = '/(\s?(?:'.join('|',$cur_merge).')\s*)?((?:\d+)(?:,\d+)?(?:\.\d+)?)(\s*(?:'.join('|',$cur_merge).'))?\s?/i';

    preg_match_all($regx, $txt, $m);
    if(!empty($m[0]))
        return $m[0];
    else
        return array();
}

function currency_parse($txt,$currency=''){
	$info = array();
	$info['hasIncentive'] = 0;
	$info['str_head'] = '';
	$info['str_num'] = 0;
	$info['str_end'] = '';
	$info['currency'] = '';
	$info['str'] = '';

	$len = strlen($txt);
	if($len>2 && $txt[$len-2] == '|'){
        $info['hasIncentive'] = $txt[$len-1];
        $txt = substr($txt,0,-2);
    }

	$currencyMap = currency_get_map();
	$parse = array();

    foreach($currencyMap as $cur=>$tag){
        $regx = '/(\s?(?:'.join('|',$tag).')\s*)?((?:\d+)(?:,\d+)?(?:\.\d+)?)(\s*(?:'.join('|',$tag).'))?\s?/i';
        preg_match_all($regx, $txt, $m);

        if(!empty( $m[0] )){
        	if(!empty($m[1][0]) || !empty($m[3][0])){
        		$info['str_head'] = trim($m[1][0]);
	        	$info['str_num'] = trim($m[2][0]);
	        	$info['str_end'] = trim($m[3][0]);
                $info['str']    =  $info['str_head'].' '.$info['str_num'].' '.$info['str_end'];
        		$info['currency'] = $cur == 'PER'?'':$cur;
        		break;	
        	}
        }

    }


    if(empty($info['str']) && !empty($currency) && strpos($txt, $currency) ===false){
        $txt = $txt.' '.$currency;
        $info = currency_parse($txt, $currency);

    }

    return $info;
}

function currency_get_map(){
	$currencyMap = array();
    $currencyMap['USD'] = array('\$','&#36;','USD','Dollar');
    $currencyMap['GBP'] = array('£','&#163;','&pound;','GBP');
    $currencyMap['EUR'] = array('€','&#8364;','&euro;','EUR');
    $currencyMap['SEK'] = array('kr','SEK');
    $currencyMap['INR'] = array('Rs','INR');
    $currencyMap['CNY'] = array('¥','CNY');
    $currencyMap['KER'] = array('WON','KER');
    $currencyMap['CHF'] = array('CHF');
    $currencyMap['PLN'] = array('PLN');
    $currencyMap['AUD'] = array('AUD');
    
    $currencyMap['PER'] = array('%','&#37;');

    return $currencyMap;
}


function select_commission_used($commission,$cur=''){
    $usedCommission = '';
    $listCommssion = array();
    $CommissionUsed = '';
    $CommissionType = '';
    $str_head = '';
    $str_end = '';
    $hasIncentive = 0;
    $newCommissionTxt = '';
    $CommissionCurrency = '';

    $regex_number = get_regex('number');

    foreach($commission as $k=>$v){
    	$commission[$k] = trim($v);
    }
    $num = count($commission);
    if($num < 1){

    }elseif($num == 1){
        $str = array_shift($commission);
        $info = currency_parse($str,$cur);
        if(ceil($info['str_num']) != 0){
        	$hasIncentive = $info['hasIncentive'];
        	$listCommssion[] = $info['str_num'];
            $str_head = $info['str_head'];
            $str_end = $info['str_end'];
            $commission[0] = $info['str'];
            $CommissionCurrency = $info['currency'];
        }else{
        	unset($commission[0]);
        }

    }else{

        $hasIncentive = 1;
        foreach($commission as $k=>$v){
        	$str = $v;
        	
            $info = currency_parse($str,$cur);

            if(ceil($info['str_num']) == 0){
                unset($commission[$k]);
                continue;
            }

            if(empty($str_head) && empty($str_end)){
                $str_head = $info['str_head'];
            	$str_end = $info['str_end'];
            }

            if($info['str_head'] != $str_head || $info['str_end'] != $str_end){
                unset($commission[$k]);
                continue;
            }
            $commission[$k] = $info['str'];
            $listCommssion[] = $info['str_num'];
            $CommissionCurrency = $info['currency'];
        }

    }
    if(!empty($listCommssion)){
        if(count($listCommssion) > 1){
            $c = count($listCommssion);
            $all = '';
            foreach($listCommssion as $comis){
                $comis = $comis.'';
                $all = $all + $comis;
                $all = $all.'';
            }

            $CommissionUsed = number_format($all/$c,2,'.','');
        }else{
            $CommissionUsed = $listCommssion[0];
        }
        $usedCommission = $str_head.$CommissionUsed.$str_end;

        if(strpos($usedCommission,'%') !== false || strpos($usedCommission,'&#37;') !== false)
            $CommissionType = 'percent';
        else
            $CommissionType = 'value';

        $newCommissionTxt = '['.join(',',$commission).']|'.$hasIncentive.'|'.$usedCommission;
    }


    $returnData = array();
    $returnData['CommissionUsed'] = $CommissionUsed;
    $returnData['CommissionValue'] = $newCommissionTxt;
    $returnData['CommissionIncentive'] = $hasIncentive;
    $returnData['CommissionType'] = $CommissionType;
    $returnData['CommissionCurrency'] = $CommissionCurrency;

    return $returnData;
}


function arr_out_format($arr,$type){
    // defult arr format
    // array(
    //         'response'  =>array(
    //             'PageTotal' =>  10,
    //             'PageNow'   =>  1,
    //             'Num'       =>  100,
    //             'NumReturn' =>  10,
    //             ),

    //         'data'      =>array(
    //             'name'      => 'aaa',
    //             'id'        => '12',
    //             ),
    //     )
    $content = '';
    switch ($type) {
        case 'txt':
            if(isset($arr['response'])){
                foreach($arr['response'] as $k=>$v){
                    $content .= '@'.$k.':'.$v."\t";
                }
                $content .= "\n";
            }

            if(isset($arr['data'])){
                $i=0;
                foreach($arr['data'] as $k=>$v){
                    $i++;
                    if($i == 1){
                        $content .= join("\t",array_keys($v))."\n";
                    }
                    $content .= join("\t",$v)."\n";
                }
            }
            break;
        case 'xml':
            $content .= '<?xml version="1.0" encoding="ISO-8859-1"?>';
            $content .= '<br-api>';
            if(isset($arr['response'])){
                $content .= '<response>';
                foreach($arr['response'] as $k=>$v){
                    $content .= '<'.$k.'>'.str_replace(array('&','<','>','"',"'"),array('&amp;','&lt;','&gt;','&quot;','&apos;'),$v).'</'.$k.'>';
                }
                $content .= '</response>';
            }
            if(isset($arr['data'])){
                $content .= '<data>';
                $i=0;
                foreach($arr['data'] as $k=>$v){
                    $content .= '<rows>';
                    foreach($v as $key=>$val){
                        if(is_array($val)){
                            foreach($val as $vv){
                                $content .= '<'.$key.'>'.str_replace(array('&','<','>','"',"'"),array('&amp;','&lt;','&gt;','&quot;','&apos;'),$vv).'</'.$key.'>';
                            }
                        }else{
                            $content .= '<'.$key.'>'.str_replace(array('&','<','>','"',"'"),array('&amp;','&lt;','&gt;','&quot;','&apos;'),$val).'</'.$key.'>';
                        }
                    }
                    $content .= '</rows>';
                }
                $content .= '</data>';
            }
            $content .= '</br-api>';
            break;
        case 'json':
            $content .= json_encode($arr,JSON_UNESCAPED_SLASHES);
            break;
        case 'csv':
            if(isset($arr['response'])){
                $content .= '"'.implode('","',array_keys($arr['response'])).'"'."\n";
                $content .= '"'.implode('","',array_values($arr['response'])).'"'."\n";
            }
            if(isset($arr['data'])){
                if(isset($arr['data'][0])){
                    $content .= '"'.implode('","',array_keys($arr['data'][0])).'"'."\n";
                }
                foreach($arr['data'] as $k=>$v){
                    $content .= '"'.implode('","',array_values($v)).'"'."\n";
                }
            }
            break;
        default:
            $content .= '';
            break;
    }
    echo trim($content);
    exit();
}
?>
