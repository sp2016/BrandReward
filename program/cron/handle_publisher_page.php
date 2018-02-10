<?php
/**
 * Created by PhpStorm.
 * User: Mcsky Ding
 * Date: 2016/6/1
 * Time: 15:33
 */
	header("Content-type: text/html; charset=utf-8");
	include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
	include_once(dirname(dirname(__FILE__)) . "/func/func.php");

	//	ini_set('xdebug.max_nesting_level', 200);

	echo 'Start@'.date('Y-m-d H:i:s').PHP_EOL;


	$objProgram = New Program();
	$pattern = '/(^h|H)(t|T){2}(p|P)(s|S)?(\:)(\/){2}/i';
	$pattern_illegal = '{^\/(.)+(\/)$}';
	$pattern_image = array( "jpg" , "jpeg" , "png" , "gif" , "bmp");
	
	$cmd = "ps aux | grep handle_publisher_page.php | grep -v grep | grep -v /bin/sh -c";
	
	$processCount = trim(exec($cmd));
	
	echo "Current pending process count:$processCount" . PHP_EOL;
	if(is_numeric($processCount))
	{
		if($processCount > 1){
			echo "One get_publisher_page_pending_url is running now.Stoped!" . PHP_EOL;
			die();
		}
	}
	else
	{
		echo "Error!" . PHP_EOL;
		die();
	}
	
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
	
	while(true)
	{
		$sql = "SELECT * FROM publisher_page WHERE `Status` = 'pending' or `Status` = 'error' limit 0,100";
		$pending_data = $objProgram->objMysql->getRows($sql);
		if(!empty($pending_data))
		{
			foreach($pending_data as $pending)
			{
				$sql = "UPDATE publisher_page SET `Status`='processing' where id = '{$pending['ID']}'";
				$objProgram->objMysql->query($sql);
				$url_default = trim($pending['Url'],'/');
				$urlInfo = getTrueUrl($pending['Url']);
			    if ($urlInfo['http_code'] != '200' || !$urlInfo['response'])
			    {
			        $sql = "UPDATE publisher_page SET `Status`='error' where id = '{$pending['ID']}'";
			        $objProgram->objMysql->query($sql);
			    }
			    else
			    {
			        $dom = new DOMDocument();
					@$dom->loadHTML($urlInfo['response']);
					$xpath = new DOMXPath($dom);
					$hrefs = $xpath->evaluate('/html/body//a');
					unset($dom);
					unset($xpath);
					$checkCount = intval($hrefs->length);
					if($checkCount == 0)
					{
						echo "Error!the count of url in this domain is 0." .PHP_EOL;
						$sql = "UPDATE publisher_page SET `Status`='error' where id = '{$pending['ID']}'";
						$objProgram->objMysql->query($sql);
					}
					else
					{
						$urlArr = array();
						for ($i = 0; $i < $hrefs->length; $i++){
							$href = $hrefs->item($i);
							$url = $href->getAttribute('href');
							unset($href);
					
							// 相对路径增加前缀
							$url = addslashes($url);
							if (preg_match($pattern_illegal, $url)){
								$url = $url_default . '/' . trim($url,'/');
							}
							$url = stripslashes($url);
					
							//去除img
							$ext = explode(".",$url);
							$ext = strtolower(end($ext));
					
							if( !empty($ext) && in_array(strtolower($ext) , $pattern_image)){
								unset($ext);
								continue;
							}
					
							//检测非法Url
							if (!preg_match($pattern, $url)){
								continue;
							}
							$urlArr[] = trim($url,'/');
						}
						unset($hrefs);
						$urlArr = array_unique($urlArr);
						
						$sqlCount = 0;
						$sql = "INSERT INTO publisher_page_detail (`DomainInfoID`,`Store`,`ExtUrl`,`ExtDomain`) VALUES ";
						foreach($urlArr as $v){
							$extDomain = get_domain($v);
							$store = '';
							preg_match("/([^\.]*)(" . implode("|", $topDomain) . ")$/mi", $extDomain, $matches);
							if (isset($matches[1]) && strlen($matches[1])) {
								$store = $matches[1];
							}
							$sql .= "('{$pending['ID']}','". addslashes($store). "','". addslashes($v). "','" . addslashes($extDomain)."'),";
							$sqlCount++;
							if($sqlCount > 500){
								$sqlCount = 0;
								$sql = rtrim($sql, ',');
								$objProgram->objMysql->query($sql);
								$sql = "INSERT INTO publisher_domain_detail (`DomainInfoID`,`Store`,`ExtUrl`,`ExtDomain`) VALUES ";
							}
						}
						if($sqlCount > 0){
							$sql = rtrim($sql, ',');
							$objProgram->objMysql->query($sql);
						}
						$sql = "UPDATE publisher_page SET `Status`='done' where id = '{$pending['ID']}'";
						$objProgram->objMysql->query($sql);
					}
			    }
			}
		}
		else
		{
			break;
		}
	}
	
	echo 'Finished@'.date('Y-m-d H:i:s')."\r\n";
	