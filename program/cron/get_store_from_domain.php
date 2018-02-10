<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
include_once(INCLUDE_ROOT . "func/func.php");

	$date = date("Y-m-d H:i:s");
	echo "<< Start @ $date >>".PHP_EOL;

	$objProgram = New Program();
	$i = $j = $k = 0;
	$sql = "select Domain from domain_top_level";
	$topDomain_tmp = $objProgram->objMysql->getRows($sql);
	$topDomain = array();
	foreach ($topDomain_tmp as $v)
	{
		$topDomain[] = '.'.$v['Domain'];
	}
	$country_arr = explode(",", $objProgram->global_c);
	foreach($country_arr as $country){
		if($country){
			$country = "\.".strtolower($country);
			$topDomain[] = "\.com?".$country;
			$topDomain[] = "\.org?".$country;
			$topDomain[] = "\.gov?".$country;
			$topDomain[] = "\.net?" . $country;
			$topDomain[] = "\.edu?".$country;
			$topDomain[] =  $country."\.com";
			$topDomain[] = $country;
		}
	}
	
	$sql = "SELECT `name`,domain,loginurl,AffiliateUrlKeywords FROM wf_aff WHERE IsInHouse='NO'";
	$domains_tmp = $objProgram->objMysql->getRows($sql);
	$stores_network = array();
	foreach ($domains_tmp as $v)
	{
		if(!empty($v['domain']))
		{
			$domain = preg_replace('/^https?:\\/\\//i','',$v['domain']);
			$domain = trim(preg_replace('/\/.*/i','',$domain)," .\t\n\r\0\x0B");
			if(stripos($domain,'.') !== false)
			{
				$store = get_store_name($domain,$topDomain);
				if($store)
				{
					$stores_network[] = $store;
				}
			}
		}
		if(!empty($v['loginurl']))
		{
			$domain = preg_replace('/^https?:\\/\\//i','',$v['loginurl']);
			$domain = trim(preg_replace('/\/.*/i','',$domain)," .\t\n\r\0\x0B");
			if(stripos($domain,'.') !== false)
			{
				$store = get_store_name($domain,$topDomain);
				if($store)
				{
					$stores_network[] = $store;
				}
			}
		}
		if(!empty($v['AffiliateUrlKeywords']))
		{
			$tmp_arr = explode(PHP_EOL, $v["AffiliateUrlKeywords"]);
			foreach ($tmp_arr as $domain)
			{
				if(!empty($domain))
				{
					$domain = preg_replace('/^https?:\\/\\//i','',$domain);
					$domain = trim(preg_replace('/\/.*/i','',$domain)," .\t\n\r\0\x0B");
					if(stripos($domain,'.') !== false)
					{
						$store = get_store_name($domain,$topDomain);
						if($store)
						{
							$stores_network[] = $store;
						}
					}
				}
			}
		}
	}
	$stores_network = array_unique($stores_network);
	foreach ($stores_network as $keywords){
		$sql = "select * from advertiser_network_keywords where `Keywords` = '$keywords'";
		if(!$objProgram->objMysql->getFirstRow($sql)){
			$sql = "insert into advertiser_network_keywords (`Keywords`) values ('$keywords')";
			$objProgram->objMysql->query($sql);
			
		}
	}

	$i = 1;
	$domainId = 0;
	while(1){
		$sql = "select id, domain from domain where id > ".$domainId." order by id limit 0,10000";
		$domain_arr = $objProgram->objMysql->getRows($sql, "id");
		echo $i++.":".count($domain_arr).PHP_EOL;
		if(!count($domain_arr)) break;
		foreach($domain_arr as $v){
			findDomainStore(strtolower($v["domain"]), $v["id"]);
			$domainId = $v['id'];
		}
	}
	
	$sql = "select count(1) from r_store_domain where LastUpdateTime < '{$date}'";
	echo "Numbers of set DomainAffSupport to NO:" . $objProgram->objMysql->getFirstRowColumn($sql).PHP_EOL;
	$sql = "delete from  r_store_domain where LastUpdateTime < '{$date}'";
	$objProgram->objMysql->query($sql);
