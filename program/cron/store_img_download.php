<?php
	include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
	include_once(dirname(dirname(__FILE__)) . "/func/func.php");
	header("Content-Type:text/html;charset=utf-8");
	ini_set ('memory_limit', '512M');
//	ini_set('xdebug.var_display_max_data', 2000000);
//	ini_set('xdebug.var_display_max_children', 2000000);
//	ini_set('xdebug.var_display_max_depth', 2000000);
//	ini_set("max_execution_time",1800);
	$imgPath = "/app/site/ezconnexion.com/web/img/adv_logo/";
	$objProgram = new Program();
	$store_ids = array();
	$sql = "select Domain from domain_top_level";
	$topDomain_tmp = $objProgram->objMysql->getRows($sql);
	$topDomain = array();
	foreach ($topDomain_tmp as $v)
	{
		$topDomain[] = '\.'.$v['Domain'];
	}
	$country_arr = explode(",", $objProgram->global_c);
	foreach ($country_arr as $country) {
		if ($country) {
			$country = "\." . strtolower($country);
			$topDomain[] = "\.com?" . $country;
			$topDomain[] = "\.org?" . $country;
			$topDomain[] = "\.net?" . $country;
			$topDomain[] = "\.gov?".$country;
			$topDomain[] = "\.edu?".$country;
			$topDomain[] =  $country."\.com";
			$topDomain[] = $country;
		}
	}
	$pattern = '/(^h|H)(t|T){2}(p|P)(s|S)?(\:)(\/){2}/i';
	$country_arr = array('us','uk','ca','au','in','de','fr');
	$date = date('Y-m-d H:i:s');
	foreach($country_arr as $country){
		$start = 1;
		do{
			$ch = curl_init();
			$apiUrl = "http://mg_comm_user:mg_comm_user@lab-bcg.bwe.io/bcg/api/merchants?si=$country&limit=500&start=$start";
			echo "API $apiUrl is going to run\n";
			curl_setopt($ch, CURLOPT_URL, "$apiUrl");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			$output = json_decode(curl_exec($ch),1)['rows'];
			curl_close($ch);
			if(empty($output))
			{
				break;
			}
			else
			{
				foreach($output as $key=>$values)
				{
					if(empty($values['logo']) && empty($values['name']))
						continue;
					$url = preg_replace($pattern,'',$values['url']);
					if (strpos($url, "/") !== false) {
						$url = substr($url, 0, strpos($url, "/"));
					}
					$store_name = get_store_name($url,$topDomain);
					if($store_name)
					{
						$sql = "SELECT ID,`Name`,NameOptimized,LogoName FROM store WHERE `Name`='{$store_name}' and LogoName = '' and StoreAffSupport='YES'";
						$results = $objProgram->objMysql->getFirstRow($sql);
						if(!empty($results))
						{
							if(!empty($values['logo']) && !empty($values['name']))
							{
								$img_name = image_download($values['logo'],$imgPath,$results['ID'],$country);
								if($img_name)
								{
									$sql = "update store set NameOptimized='{$values['name']}',LogoName='$img_name' where ID='{$results['ID']}'";
									$objProgram->objMysql->query($sql);
								}
							}
						}
					}
				}
			}
			$start += 500;
		}while(true);
	}

//	$sql = "SELECT count(1) FROM store WHERE LogoName IS NULL OR store.NameOptimized IS NULL;";
//	$num = current($objProgram->objMysql->getFirstRow($sql));
//	echo "There are still $num stores without logo!";