<?php
	/**
	 * Created by PhpStorm.
	 * User: Sky Ting
	 * Date: 2017/11/21
	 * Time: 10:41
	 */
	
	include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
	include_once(dirname(dirname(__FILE__)) . "/func/func.php");
	echo "<< Start @ " . date("Y-m-d H:i:s") ." >>".PHP_EOL;

	$end_date = date("Y-m-d", strtotime("Last Sunday"));
	$start_date = date("Y-m-d", strtotime("-6 day",strtotime($end_date)));
	$range = $start_date . '~' . $end_date;
	$objProgram = New Program();
	$count = 0;
	//Have commissions in sub network last week
	$bdg01 = New MysqlExt(MEGA_DB_NAME, MEGA_DB_HOST, MEGA_DB_USER, MEGA_DB_PASS);
	$sub_aff = implode(',',$objProgram->sub_aff);
	$i = 0;
	$sql = "SELECT a.`programId`, a.`clicks`, a.`revenues` FROM statis_program_br a INNER JOIN program_intell b ON a.programId = b.programId WHERE a.CreatedDate >= '$start_date' AND a.CreatedDate <= '$end_date' AND b.AffId IN ($sub_aff) AND b.isActive = 'Active' and a.revenues > 0;";
	$programs = $bdg01->getRows($sql);
	$domains = array();
	foreach ($programs as $program)
	{
		$sql = "select a.Domain from domain a inner join r_domain_program b on a.ID=b.DID where b.PID='{$program['programId']}'";
		$domain = $bdg01->getFirstRowColumn($sql);
		if($domain)
		{
			if(isset($domains[$domain]['clicks']))
			{
				$domains[$domain]['clicks'] += $program['clicks'];
				$domains[$domain]['revenues'] += $program['revenues'];
			}
			else
			{
				$domains[$domain]['clicks'] = $program['clicks'];
				$domains[$domain]['revenues'] = $program['revenues'];
			}
		}
	}
	
	$stores = array();
	foreach ($domains as $domain=>$info)
	{
		$sql = "select c.ID,c.Name,c.NameOptimized from domain a inner join r_store_domain b on a.ID=b.DomainId inner join store c on b.StoreId=c.ID where a.Domain='{$domain}'";
		$store_info = $objProgram->objMysql->getFirstRow($sql);
		if($store_info)
		{
			if(isset($stores[$store_info['ID']]))
			{
				$stores[$store_info['ID']]['domain'] .= ',' . $domain;
				$stores[$store_info['ID']]['clicks'] += $info['clicks'];
				$stores[$store_info['ID']]['revenues'] += $info['revenues'];
			}
			else
			{
				$stores[$store_info['ID']]['ID'] = $store_info['ID'];
				$stores[$store_info['ID']]['domain'] = $domain;
				$stores[$store_info['ID']]['clicks'] = $info['clicks'];
				$stores[$store_info['ID']]['revenues'] = $info['revenues'];
				$sql = "select distinct c.`ID` from program a inner join r_domain_program b on b.`PID`=a.`ID` inner join wf_aff c on c.`ID`=a.`AffId` where b.`DID`='{$store_info['ID']}'";
				$networks = $objProgram->objMysql->getRows($sql,'ID');
				$stores[$store_info['ID']]['network_ids'] = implode(',',array_keys($networks));
			}
			
		}
	}
	
	foreach ($stores as $store)
	{
		$count ++;
		$sql = "insert into store_in_subaff (`DateRange`,`StoreID`,`Domain`,`Clicks`,`Revenues`,`AffIds`) values ('$range','{$store['ID']}','{$store['domain']}','{$store['clicks']}','{$store['revenues']}','{$store['network_ids']}')";
		$objProgram->objMysql->query($sql);
	}
	echo "Have commissions in sub network last week :" . $count . PHP_EOL;
	
	//Have no commission last week
	$count = 0;
	$sql = "SELECT group_concat(domainId) as dids,StoreId, SUM(clicks) AS clicks FROM statis_domain_br WHERE CreatedDate >= '$start_date' AND CreatedDate <= '$end_date' GROUP BY StoreId HAVING SUM(revenues) = 0 AND SUM(clicks) >= 200 order by clicks desc";
	$data = $objProgram->objMysql->getRows($sql);
	foreach ($data as $value)
	{
		$ids = trim($value['dids'],',');
		$sql = "select c.ID from r_domain_program a inner join program_intell b on a.`PID`=b.`ProgramId` inner join wf_aff c on c.`ID`=b.`AffId` where a.DID in ( $ids ) and (b.`IsActive` != 'Active' or a.`Status`!='Active')";
		$networks = $objProgram->objMysql->getRows($sql,'ID');
		$networks = implode(',',array_keys($networks));
		$count ++;
		$sql = "insert into store_no_commission (`DateRange`,`StoreID`,`Clicks`,`AffIds`) values ('$range','{$value['StoreId']}','{$value['clicks']}','$networks')";
		$objProgram->objMysql->query($sql);
	}
	echo "Have no commission last week :" . $count . PHP_EOL;

	
	//Program have no commission last week
	$count = 0;
	$sql = "SELECT programId, SUM(clicks) clicks FROM statis_program_br WHERE CreatedDate >= '$start_date' AND CreatedDate <= '$end_date' GROUP BY programId HAVING SUM(revenues) = 0 AND SUM(clicks) >= 200 order by clicks desc";
	$data = $objProgram->objMysql->getRows($sql);
	foreach ($data as $value)
	{
		$count ++;
		$sql = "insert into program_no_commission (`DateRange`,`ProgramID`,`Clicks`) values ('$range','{$value['programId']}','{$value['clicks']}')";
		$objProgram->objMysql->query($sql);
	}
	echo "Program have no commission last week :" . $count . PHP_EOL;
	echo "<< End @ " . date("Y-m-d H:i:s") ." >>".PHP_EOL;
