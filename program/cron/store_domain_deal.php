<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
include_once(INCLUDE_ROOT . "func/func.php");

echo "<< Start @ " . date("Y-m-d H:i:s") . " >>\r\n";

	$objProgram = New Program();

	$date = date("Y-m-d H:i:s");
	
	$last_hour_time = date('Y-m-d H',strtotime("-1 hour"));
	$i = $j = $k = 0;
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

	
$date_from = date("Y-m-d H:i:s", strtotime("-2 day"));
$createddate = date("Y-m-d", strtotime("-2 day"));
$page = 0;
$domain_cache = array();
	//--------find out domains in table which is not in table domain and push these domains into table domain--------
	while (1) {
		$sql = "SELECT id, domainused FROM bd_out_tracking WHERE domainId = '0' AND domainused <> '' and createddate >= '$createddate' and created >= '$date_from' limit " . $page * 1000 . ", 1000";
	
		//$sql = "SELECT domainused FROM bd_out_tracking WHERE domainId = '0' AND domainused <> '' and createddate = '$createddate' limit ". $page * 1000 .", 1000";
		$domain_arr = array();
		$domain_arr = $objProgram->objMysql->getRows($sql);
	
		if (!count($domain_arr)) break;
		$page++;
	
		foreach ($domain_arr as $v) {
			if (stripos($v["domainused"], ":domain:" !== 0)) continue;
			//echo $v["domainused"]."\r\n";
			$v["domainused"] = str_ireplace(":domain:", "", $v["domainused"]);
			//echo $v["domainused"]."\r\n";
			$v["domainused"] = preg_replace("/^([a-zA-Z]{2}:)/i", "", $v["domainused"]);
	
			$v["domainused"] = preg_replace("/\|.*/i", "", $v["domainused"]);
	
			if (isset($domain_cache[$v["domainused"]])) {
			    $did = intval($domain_cache[$v["domainused"]]);
			    if ($did) {
			        $sql = "update bd_out_tracking set domainId = $did where id = {$v["id"]}";
			        $objProgram->objMysql->query($sql);
			    }
			} else {
				if (preg_match("/([^\.]*)(" . implode("|", $topDomain) . ")$/mi", $v["domainused"])) {
					$objProgram->insertDomain($v["domainused"]);
					$sql = "select id from domain where domain = '" . addslashes($v["domainused"]) . "'";
					$did = intval($objProgram->objMysql->getFirstRowColumn($sql));
					$domain_cache[$v["domainused"]] = $did;
					if ($did) {
						$sql = "update bd_out_tracking set domainId = $did where id = {$v["id"]}";
						$objProgram->objMysql->query($sql);
					}
				}
			}
			$i++;
		}
	}
	echo "get $i | " . date("Y-m-d H:i:s") . PHP_EOL;
	
	
	
//	//--------------------update storeId in table statis_domain--------------------
//	echo "Update storeId in table statis_domain start @".date('Y-m-d H:i:s') . PHP_EOL;
//	$from_date = date("Y-m-d",strtotime("-2 day"));
//	$to_date = date("Y-m-d");
//        $where = "a.`createddate` >= '".$from_date."' AND a.createddate <= '".$to_date."' AND ";
//        $where = "";
//	$sql = "UPDATE statis_domain AS a,r_store_domain AS b SET a.`storeId` = b.`StoreId` WHERE ".$where." a.`storeId` = 0  AND a.`domainId` = b.`DomainId`";
//	$objProgram->objMysql->query($sql);
	
	//--------------------update storeId in table statis_domain_br--------------------
	echo "Update storeId in table statis_domain_br start @".date('Y-m-d H:i:s') . PHP_EOL;
	$from_date = date("Y-m-d",strtotime("-2 day"));
	$to_date = date("Y-m-d");
        $where = "a.`createddate` >= '".$from_date."' AND a.createddate <= '".$to_date."' AND ";
        $where = "";
	$sql = "UPDATE statis_domain_br AS a,r_store_domain AS b SET a.`storeId` = b.`StoreId` WHERE ".$where." a.`storeId` = 0  AND a.`domainId` = b.`DomainId`";
	$objProgram->objMysql->query($sql);
	
	echo "<< End @ " . date("Y-m-d H:i:s") . " >>\r\n";
	exit;


?>
