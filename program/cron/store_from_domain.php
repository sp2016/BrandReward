<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
include_once(INCLUDE_ROOT . "func/func.php");

	echo "<< Start @ " . date("Y-m-d H:i:s") . " >>\r\n";

	$cmd = 'ps aux | grep get_store_from_domain.php | grep -v grep -c';
	$processCount = trim(exec($cmd));
	if(is_numeric($processCount) && $processCount > 0)
	{
		mydie('store_from_domain is running now.Stopped!');
	}
	
	$site_arr = array();
	$did = "";
	if (isset($_SERVER["argc"]) && $_SERVER["argc"] > 1) {
		foreach ($_SERVER["argv"] as $v) {
			$tmp = explode("=", $v);
			if ($tmp[0] == "--site") {
				$site_arr = explode(",", $tmp[1]);
			} elseif ($tmp[0] == "--debug") {
				$is_debug = true;
			} elseif ($tmp[0] == "--did") {
				$did = " and id in ({$tmp[1]})";
			} elseif ($tmp[0] == "--fast") {
				$is_fast = true;
			} elseif ($tmp[0] == "--nottoredis") {
				$nottoredis = true;
			} elseif ($tmp[0] == "--onlyactive") {
				$onlyactive = true;
			}
		}
	}

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
	echo "Get store from domain start @". date('Y-m-d H:i:s') . PHP_EOL;
	$i = 1;
	$domainId = 0;
	while(1){
		$sql = "select id, domain from domain where isnull(domainname) and id > ".$domainId." order by id limit 0,10000";
		$domain_arr = array();
		$domain_arr = $objProgram->objMysql->getRows($sql, "id");
		echo $i++.":".count($domain_arr)."\t";
		if(!count($domain_arr)) break;
		foreach($domain_arr as $v){
			findDomainStore(strtolower($v["domain"]), $v["id"]);
			$domainId = $v['id'];
		}
	}
	echo PHP_EOL ."Get store from r_store_domain start @". date('Y-m-d H:i:s') . PHP_EOL;
	
	while(true){
		$customId='';
		$sql = "select a.id, a.domain,b.`ID` customID from domain a inner join store_custom b on a.`domain`=b.`Domain` where b.`Status`!='PROCESSED'";
		$domain_arr = array();
		$domain_arr = $objProgram->objMysql->getRows($sql, "id");
		echo $i++.":".count($domain_arr)."\t";
		if(!count($domain_arr)) break;
		foreach($domain_arr as $v){
			findDomainStore(strtolower($v["domain"]), $v["id"]);
			$domainId = $v['id'];
			$customId .= $v['customID'].',';
		}
		$customId = trim($customId,',');
		$sql = "update store_custom set `Status`='PROCESSED' where ID in ($customId)";
		$objProgram->objMysql->query($sql);
	}
	//-------------------break domain which is not in table r_store_domain down into store and push store into table store and table r_store_domain-------------------
	$i = 0;
	$domainId = 0;
	while (1) {
		$sql = "SELECT a.ID, a.Domain FROM domain AS a LEFT JOIN  r_store_domain AS b ON a.ID = b.DomainId WHERE b.DomainId IS NULL and a.ID > " .$domainId. " order by a.ID limit 0,1000;";
		$domain_arr = array();
		$domain_arr = $objProgram->objMysql->getRows($sql);
		if (!count($domain_arr)) break;

		foreach ($domain_arr as $v) {
			$domainId = $v['ID'];
			findDomainStore(strtolower($v['Domain']), $v['ID']);
		}
	}
	
	echo "Check domain aff support start @". date('Y-m-d H:i:s') . PHP_EOL;
//--------------------traverse table domain to find if this domain has an aff relationship and if the corresponding store has an aff relationship--------------------
	$i = 0;
	$lastId = 0;
	$domainYesArr = array();
	$storeYesArr = array();
	$sql  = 'SELECT COUNT(*) AS c FROM domain';
	$domainLen = $objProgram->objMysql->getFirstRowColumn($sql);
	
	//set DomainAffSupport YES
	while($i < $domainLen){
		$sql = "SELECT a.DID FROM r_domain_program AS a INNER JOIN program_intell AS b ON a.PID = b.ProgramId inner join domain c on a.DID=c.ID WHERE a.Status = 'Active' AND b.IsActive = 'Active' limit $i,1000";
		$i += 1000;
		$domain_arr = $objProgram->objMysql->getRows($sql,'DID');
		if (!empty($domain_arr))
		{
			$domain_arr = array_keys($domain_arr);
			$domainYesArr = array_merge($domainYesArr,$domain_arr);
		}
		else
			break;
	}
	$domainYesArr = array_unique($domainYesArr);
	echo 'DomainYES:'.count($domainYesArr).PHP_EOL;
	$sql = "UPDATE r_store_domain SET DomainAffSupport = 'NO',LastUpdateTime='{$date}' WHERE DomainId NOT IN (" .implode(",",$domainYesArr).')';
	$objProgram->objMysql->query($sql);
	$sql = "UPDATE r_store_domain SET DomainAffSupport = 'YES',LastUpdateTime='{$date}' WHERE DomainId IN (" .implode(",",$domainYesArr).')';
	$objProgram->objMysql->query($sql);

	echo "Get store aff support start @". date('Y-m-d H:i:s') . PHP_EOL;
	//--------------------set StoreAffSupport YES--------------------
	$sql = "SELECT DISTINCT(StoreId) FROM r_store_domain WHERE DomainAffSupport = 'YES'";
	$storeArr = $objProgram->objMysql->getRows($sql);
	foreach($storeArr as $v){
	    $storeYesArr[] = $v['StoreId'];
	}
	//store belongs to network
	$sql = "SELECT `Keywords` FROM advertiser_network_keywords";
	$stores_network = $objProgram->objMysql->getRows($sql,'Keywords');
	$stores_network = array_keys($stores_network);
	
	echo "Set StoreAffSupport YES start @". date('Y-m-d H:i:s') . PHP_EOL ."StoreYES:".count($storeArr).PHP_EOL;
	$i = 0;
	$update_time = date('Y-m-d H:i:s');
	while (1) {
		$sql = "select `ID`,`Name`,`StoreAffSupport` from store where `StoreAffSupport` = 'YES' or `ID` in (" . implode(',',$storeYesArr).") order by `ID` limit $i,1000";
		$stores = $objProgram->objMysql->getRows($sql);
		$i += 1000;
		if (empty($stores))
			break;
		foreach ($stores as $value) {
			if (in_array($value['ID'], $storeYesArr)) {
				if ($value['StoreAffSupport'] == 'NO') {
					$relatedPro = '';
					if(!in_array($value['Name'],$stores_network))
					{
						//No to Yes
						$sql = "UPDATE store SET StoreAffSupport = 'YES' WHERE `ID`='{$value['ID']}'";
						$objProgram->objMysql->query($sql);
						$sql = "select distinct DomainId from r_store_domain where StoreId = '{$value['ID']}'";
						$dids = array_keys($objProgram->objMysql->getRows($sql,'DomainId'));
						if(!empty($dids)){
							$dids = implode(',',$dids);
							$sql = "select distinct PID from r_domain_program where `Status` = 'Active' and DID in ($dids)";
							$pids = array_keys($objProgram->objMysql->getRows($sql,'PID'));
							if(!empty($pids)){
								$sql = "select distinct ProgramId from program_intell_change_log where FieldName='IsActive' and `AddTime`>='{$last_hour_time}'";
								$relatedPro = trim(implode(',', array_keys($objProgram->objMysql->getRows($sql, 'ProgramId'))), ',');
							}
						}
//						$sql = "select a.ID from program_change_log a inner join r_domain_program b on a.ProgramId=b.PID inner join r_store_domain c on b.DID=c.DomainId where c.StoreId='{$value['ID']}' and a.LastUpdateTime >= '{$last_hour_time}' and (a.FieldName = 'StatusInAff' or a.FieldName = 'Partnership')";
//						$relatedPro = trim(implode(',', array_keys($objProgram->objMysql->getRows($sql, 'ID'))), ',');
						$sql = "insert into store_program_change_log (`StoreId`,`NetworkStatus`,`From`,`To`,`UpdateTime`,`RelatedProgram`) values ('{$value['ID']}','YES','NO','YES','{$update_time}','$relatedPro')";
						$objProgram->objMysql->query($sql);
					}
				}
				else{
					if(in_array($value['Name'],$stores_network))
					{
						$sql = "UPDATE store SET StoreAffSupport = 'NO',IsAffiliate=1 WHERE `ID`='{$value['ID']}'";
						$objProgram->objMysql->query($sql);
					}
				}
			} else {
				$sql = "UPDATE store SET StoreAffSupport = 'NO' WHERE `ID`='{$value['ID']}'";
				$relatedPro = '';
				$objProgram->objMysql->query($sql);
				if ($value['StoreAffSupport'] == 'YES') {
					//Yes to No
//					$sql = "select a.ID from program_change_log a inner join r_domain_program b on a.ProgramId=b.PID inner join r_store_domain c on b.DID=c.DomainId where c.StoreId='{$value['ID']}' and a.LastUpdateTime >= '{$last_hour_time}' and (a.FieldName = 'StatusInAff' or a.FieldName = 'Partnership')";
//					$relatedPro = trim(implode(',', array_keys($objProgram->objMysql->getRows($sql, 'ID'))), ',');
					$sql = "select distinct DomainId from r_store_domain where StoreId = '{$value['ID']}'";
					$dids = array_keys($objProgram->objMysql->getRows($sql,'DomainId'));
					if(!empty($dids)){
						$dids = implode(',',$dids);
						$sql = "select distinct PID from r_domain_program where `Status` = 'Active' and DID in ($dids)";
						$pids = array_keys($objProgram->objMysql->getRows($sql,'PID'));
						if(!empty($pids)){
							$sql = "select distinct ProgramId from program_intell_change_log where FieldName='IsActive' and `AddTime`>='{$last_hour_time}'";
							$relatedPro = trim(implode(',', array_keys($objProgram->objMysql->getRows($sql, 'ProgramId'))), ',');
						}
					}
					$sql = "insert into store_program_change_log (`StoreId`,`NetworkStatus`,`From`,`To`,`UpdateTime`,`RelatedProgram`) values ('{$value['ID']}','NO','YES','NO','{$update_time}','$relatedPro')";
					$objProgram->objMysql->query($sql);
				}
			}
		}
	}
	
	//--------------------set relationship between store and program(All active program)--------------------
	echo "Set relationship between store and program start @".date('Y-m-d H:i:s') . PHP_EOL;
	foreach ($storeYesArr as $store_id)
	{
		$sql = "select distinct d.`ProgramId` from store a inner join r_store_domain b on a.`ID`=b.`StoreId` inner join r_domain_program c on b.`DomainId` =c.`DID` inner join program_intell d on d.`ProgramId`=c.`PID` where c.`Status`='Active' and d.`IsActive`='Active' and a.`StoreAffSupport`='YES' and b.`DomainAffSupport`='YES' and a.`ID`='{$store_id}'";
		$program_ids = array_keys($objProgram->objMysql->getRows($sql,'ProgramId'));
		
		foreach ($program_ids as $program_id)
		{
			$outbound = '';
			$sql = "select DID,Site,`Key` from domain_outgoing_default_other where IsFake='NO' and PID='$program_id'";
			$outbounds = $objProgram->objMysql->getRows($sql);
			$outbound_tmp = array();
			foreach ($outbounds as $outbound)
			{
				$outbound_tmp[$outbound['DID'].'|'.$outbound['Site'].'|'.$outbound['Key']] = 1;
			}
			$sql = "select DID,Site,`Key` from redirect_default where IsFake='NO' and PID='$program_id'";
			$outbounds = $objProgram->objMysql->getRows($sql);
			foreach ($outbounds as $outbound)
			{
				$outbound_tmp[$outbound['DID'].'|'.$outbound['Site'].'|'.$outbound['Key']] = 1;
			}
			$outbound = addslashes(implode(',', array_keys($outbound_tmp)));
			$sql = "INSERT INTO r_store_program (`StoreId`,`ProgramId`,`UpdateTime`,`Outbound`) VALUES ('$store_id','$program_id','$date','$outbound') ON DUPLICATE KEY UPDATE `Outbound`='$outbound',UpdateTime='$date';";
			$objProgram->objMysql->query($sql);
		}
		$sql = "delete from r_store_program where `StoreId`='{$store_id}' and `UpdateTime`< '{$date}'";
		$objProgram->objMysql->query($sql);
	}
	$sql = "delete from r_store_program where `UpdateTime`< '{$date}'";
	$objProgram->objMysql->query($sql);
	
	//--------------------update support type,domain and country code in table store--------------------
	echo "Update support type,RemunerationModel,domain and country code in table store start @".date('Y-m-d H:i:s') . PHP_EOL;
	$i = 0;
	while (1) {
		$sql = "select id from store where StoreAffSupport='YES' order by id limit $i,1000";
		$i+=1000;
		$store_ids = array_keys($objProgram->objMysql->getRows($sql, 'id'));
		if (!count($store_ids))
			break;
		foreach ($store_ids as $store_id)
		{
			//update support type
			$sql = "SELECT c.`SupportType`,c.`RemunerationModel` FROM store a INNER JOIN r_store_program b ON a.`ID` = b.`StoreId` INNER JOIN program_intell c ON c.`ProgramId` = b.`ProgramId` WHERE a.ID='{$store_id}';";
			$data = $objProgram->objMysql->getRows($sql);
			$support_type_tmp = array_map(function($element){return $element['SupportType'];}, $data);
			$support_type_tmp = array_unique($support_type_tmp);
			$support_type = '';
			if($support_type_tmp)
			{
				if (in_array('Content', $support_type_tmp))
				{
					if (count($support_type_tmp) == 1)
						$support_type = 'Content';
					else
						$support_type = 'Mixed';
				}
				else
					$support_type = 'All';
									{
					$sql = "update store set SupportType='All' WHERE ID = '{$store_id}'";
				}
			}
			
			//update RemunerationModel
			$remuneration_model = array_map(function($element){return $element['RemunerationModel'];}, $data);
			$remuneration_model = implode(',',array_unique($remuneration_model));
			
			//update domains and countrycode
			$country_array = $domains_array = $programids_array = array();
			$sql = "select distinct a.`PID`,a.`Key`,a.`site` from domain_outgoing_all a inner join r_store_domain b on a.`DID`=b.`DomainId` where b.`StoreId`='{$store_id}' and a.`DefaultOrder`=0";
			$tmp_info = $objProgram->objMysql->getRows($sql);
			foreach ($tmp_info as $value)
			{
				$country_array[$value['site']] = $value['site'];
				$domains_array[$value['Key']] = $value['Key'];
				$programids_array[$value['PID']] = $value['PID'];
			}
			$country_array = array_keys($country_array);
			$domains_array = array_keys($domains_array);
			$programids_array = array_keys($programids_array);
			$country_code = implode(",", $country_array);
			$domains = implode(",", $domains_array);
			$programids = implode(",", $programids_array);

			$sql = "SELECT distinct b.`AffId` FROM r_store_program a inner join program_intell AS b ON a.`ProgramId` = b.`ProgramId` WHERE a.`StoreId` = " . intval($store_id);
			$tmp_rows = $objProgram->objMysql->getRows($sql,'AffId');
			$affids = implode(",", array_keys($tmp_rows));

			$sql = "update store set SupportType='$support_type',RemunerationModels='$remuneration_model',domains = '$domains',Programids = '$programids', countrycode = '$country_code',Affids = '$affids' where id = $store_id";
			$objProgram->objMysql->query($sql);
		}
	}
	//--------------------update clicks and commission in table store--------------------
        /*
	echo "Update clicks and commission in table store start @".date('Y-m-d H:i:s') . PHP_EOL;
	$sql = "UPDATE statis_domain AS a,r_store_domain AS b SET a.`storeId` = b.`StoreId` WHERE  a.`storeId` = 0  AND a.`domainId` = b.`DomainId`";
	$objProgram->objMysql->query($sql);
	
	$sql = "UPDATE statis_domain_br AS a,r_store_domain AS b SET a.`storeId` = b.`StoreId` WHERE  a.`storeId` = 0  AND a.`domainId` = b.`DomainId`";
	$objProgram->objMysql->query($sql);
	
	$sql = "SELECT storeId,SUM(clicks) AS clicks,SUM(clicks_robot) AS clicks_robot,SUM(clicks_robot_p) AS clicks_robot_p,SUM(sales) AS sales,SUM(revenues) AS commission FROM statis_domain_br WHERE StoreId > 0 GROUP BY StoreId";
	$res = $objProgram->objMysql->getRows($sql);
	
	$sql = "SELECT a.`ApiKey` FROM publisher_account AS a LEFT JOIN publisher AS b ON a.`PublisherId` = b.`ID` WHERE b.`Tax` = 0";
	$rows = $objProgram->objMysql->getRows($sql);
	$site_arr = array();
	foreach($rows as $k=>$v){
	    $site_arr[] = $v['ApiKey'];
	}
	
	$sql = "SELECT storeId,SUM(clicks) AS clicks,SUM(clicks_robot) AS clicks_robot,SUM(clicks_robot_p) AS clicks_robot_p,SUM(sales) AS sales,SUM(revenues) AS commission FROM statis_domain_br WHERE StoreId > 0 AND site NOT IN ('".join("','",$site_arr)."') GROUP BY StoreId";
	$res2 = $objProgram->objMysql->getRows($sql);
	$res_pub = array();
	foreach($res2 as $k=>$v){
	    $res_pub[$v['storeId']] = $v;
	}
	
	
	if(!empty($res)){
	
	    $ids = array();
	
	    foreach($res as $k){
	        $storeid = $k['storeId'];
	        $clicks = $k['clicks'];
	        $sales = $k['sales'];
	        $commission = $k['commission'];
	        $clicks_robot = $k['clicks_robot'];
	        $clicks_robot_p = $k['clicks_robot_p'];
	        if(isset($res_pub[$storeid])){
	            $pclicks = $k['clicks'];
	            $sales_publisher = $k['sales'];
	            $commission_publisher = $k['commission'];
	            $pclicks_robot = $k['clicks_robot'];
	            $pclicks_robot_p = $k['clicks_robot_p'];
	        }else{
	            $pclicks = 0;
	            $sales_publisher = 0.0000;
	            $commission_publisher = 0.0000;
	            $pclicks_robot = 0;
	            $pclicks_robot_p = 0;
	        }
	     
	        $sql = "update store set clicks=".$clicks.",clicks_robot = ".$clicks_robot.",clicks_robot_p = ".$clicks_robot_p.",PClicks = ".$pclicks.",PClicks_robot = ".$pclicks_robot.",PClicks_robot_p = ".$pclicks_robot_p.",commission='$commission',Commission_publisher = ".$commission_publisher.",sales='$sales',Sales_publisher = ".$sales_publisher." where `ID` = ".$storeid;
	        $objProgram->objMysql->query($sql);
	
	        $ids[] = $storeid;
	    }
	
	    if(!empty($ids)){
	        $sql = "UPDATE store SET clicks = 0,clicks_robot = 0,clicks_robot_p = 0,PClicks = 0,PClicks_robot = 0,PClicks_robot_p = 0,Commission_publisher = 0,commission = 0.0000,Sales_publisher = 0,sales = 0.0000 WHERE ID NOT IN (".join(',',$ids).")";
	        $objProgram->objMysql->query($sql);
	    }
	}
	*/
echo "<< End @ " . date("Y-m-d H:i:s") . " >>\r\n";
exit;


?>
