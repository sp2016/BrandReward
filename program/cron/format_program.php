<?php
	/**
	 * Created by PhpStorm.
	 * User: Sky Ting
	 * Date: 2018/01/02
	 * Time: 15:28
	 */
	
	include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
	include_once(dirname(dirname(__FILE__)) . "/func/func.php");
	$id_arr = array();
	$is_debug = false;
	$pid = "";
	$all = $is_fast = $nottoredis = $onlyactive = $debug_sql = $forcecc = false;
	$date = date("Y-m-d H:i:s");
	$in_house = false;
	if(isset($_SERVER["argc"]) && $_SERVER["argc"] > 1)
	{
		foreach($_SERVER["argv"] as $v){
			$tmp = explode("=", $v);
			if($tmp[0] == "--affid"){
				$id_arr = explode(",", $tmp[1]);
			}elseif($tmp[0] == "--debug"){
				$is_debug = true;
			}elseif($tmp[0] == "--pid"){
				$pid = " and a.id in (" . trim($tmp[1], ",") . ")";
			}elseif($tmp[0] == "--fast"){
				$is_fast = true;
			}elseif($tmp[0] == "--nottoredis"){
				$nottoredis = true;
			}elseif($tmp[0] == "--onlyactive"){
				$onlyactive = true;
			}elseif($tmp[0] == "--in_house"){
				$in_house = true;
			}elseif($tmp[0] == "--sql"){
				define("SQL_CONFIG", 1);
			}elseif($tmp[0] == "--forcecc"){
				$forcecc = true;
			}elseif($tmp[0] == "--all"){
				$all = true;
			}
		}
	}
	
	if(!checkProcess(__FILE__)){
		echo 'process still runing.\r\n';
		echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";
		exit;
	}
	
	function checkProcess($process_name){
		$cmd = `ps aux | grep $process_name | grep 'grep' -v | grep '/bin/sh' -v -c`;
		$return = ''.$cmd.'';
		if($return > 1){
			return false;
		}else{
			return true;
		}
	}
	
	/*
	 * #AffDefaultUrl
	 * 1, provided direct by program.do not need to select from links
	 * 2, has regular pattern. no need select from links
	 * 3, select from links
	 *
	 *
	 * #DeepUrlTpl		mostly base on AffDefaultUrl
	 * 1, provided direct by program.do not need to select from links
	 * 2, has regular pattern. no need select from links
	 * 3, select from links
	 * 4, not support
	 *
	 */
	
	echo "<< Start to format program @ $date >>\r\n";
	
	$objProgram = New Program();
	$formatProrgam = New FormatProrgam($objProgram);
	$objProgram->objMysql->query('SET NAMES latin1');
	
	$fieldArr = array('CommissionExt','Partnership','Homepage','SupportDeepUrl','Name','StatusInAff','AffDefaultUrl','TargetCountryExt','CommissionUsed','SupportType','StatusInBdg','CategoryExt');
	
	$max = $count_test = 0;
	
	$sql = "select `AffId`,`FieldName`,`FunctionName` from program_intell_controller";
	$db_controller = $objProgram->objMysql->getRows($sql);
	$controller = array();
	foreach ($db_controller as $item) {
		$controller[$item['AffId']][$item['FieldName']] = $item['FunctionName'];
	}
	$i = 0;
	$j = 0;
	while (true)
	{
		$update_sql = array();
		$sql = "select ProgramID,group_concat(FieleName) FieleName from program_update_queue where `Status`='NEW' group by ProgramID order by `ID` asc limit 0,1000";
		$queue = $objProgram->objMysql->getRows($sql,'ProgramID');
//		$sql = "select ProgramID,'CommissionExt,Partnership,Homepage,Name,StatusInAff,TargetCountryExt,CommissionUsed,SupportType,StatusInBdg,CategoryExt' FieleName from program_intell limit $i,1000";
//		$sql = "select ProgramID,'CommissionExt,Partnership,Homepage,Name,StatusInAff,TargetCountryExt,CommissionUsed,SupportType,StatusInBdg,CategoryExt' FieleName from program_intell where programid=73311 limit $i,1000";
		$i += 1000;
		$queue = $objProgram->objMysql->getRows($sql,'ProgramID');
		$pids = implode(',',array_keys($queue));
		if(empty($queue))
			break;
		echo count($queue) . "prorgam have changed" . PHP_EOL;
		$update_sql = array();
		$sql = "select a.`ID` ProgramId,a.AffId,a.IdInAff,a.`SecondIdInAff`,a.`Name`,a.Description,a.AffDefaultUrl,a.Partnership,a.StatusInAff,a.CommissionExt,a.Homepage,a.SupportDeepUrl,a.TargetCountryExt,a.CategoryExt,b.TargetCountryInt ShippingCountryManual,b.statusinbdg IsActiveManual,b.realdomain DomainManual,b.CommissionUsed,b.CommissionCurrency,b.CommissionType,b.SupportType SupportTypeManual,c.`Homepage` HomepagePre,c.`ManualChecked` HomepagePreManualChecked,d.`ShippingCountry`,d.`IsActive`,d.`Domain`,d.`Order`,d.`LastChangeTime`,d.`SupportType` from program a left join program_manual b on a.`ID` = b.`ProgramId` left join program_pre c on a.`ID` = c.`ProgramId` left join program_intell d on a.`ID` = d.`ProgramId` where a.`ID` in ($pids)";
		$prgm_arr = $objProgram->objMysql->getRows($sql,"ProgramId");
		foreach ($queue as $pid=>$details) {
			$fields = array_unique(explode(',',$details["FieleName"]));
			$flag_is_active = $flag_shipping_country = $flag_commission = $flag_domain = $flag_support_type = $flag_support_deep_url = $flag_category = $flag_order = false;

			foreach ($fields as $field)
			{
				switch ($field)
				{
					case 'Partnership':
					case 'StatusInAff':
					case 'StatusInBdg':
						$flag_is_active = true;
						break;
					case 'Name':
					case 'TargetCountryExt':
						$flag_shipping_country  =true ;
						break;
					case 'CommissionExt':
					case 'CommissionUsed':
						$flag_commission = true;
						break;
					case 'Homepage':
						$flag_domain = true;
						break;
					case 'AffDefaultUrl':
					case 'SupportDeepUrl':
						$flag_support_deep_url = true;
						break;
					case 'SupportType':
						$flag_support_type = true;
						break;
					case 'CategoryExt':
						$flag_category = true;
						break;
					default:
						break;
				}
			}
			$update_sql[$pid] = array(
				"ProgramId" => $pid,
				"AffId" => $prgm_arr[$pid]["AffId"],
				"IdInAff" => $prgm_arr[$pid]["IdInAff"],
				"LastUpdateTime" => $date
			);
			$domain_arr = array();
			if($flag_domain)
			{
				if(!empty($prgm_arr[$pid]['DomainManual'])){
					$homepage_arr = $prgm_arr[$pid]['DomainManual'];
				}else{
					if(!empty($prgm_arr[$pid]['HomepagePre'])){
						if($prgm_arr[$pid]['HomepagePreManualChecked'] == 'YES')
							$prgm_arr[$pid]["Homepage"] = $prgm_arr[$pid]['HomepagePre'];
						else
							$prgm_arr[$pid]["Homepage"] .= "\r\n" . $prgm_arr[$pid]['HomepagePre'];
						
					}
					$homepage_arr = $prgm_arr[$pid]["Homepage"];
					
					$homepage = preg_replace("/(https?:?\\/\\/:?https?:?\\/\\/:?)/i", "http://", $homepage_arr);
					$tmp_domain = $objProgram->getDomainByHomepage($prgm_arr[$pid]["Homepage"], "fi");
					if(isset($tmp_domain["domain"])){
						$domain_arr[] = current($tmp_domain["domain"]);
					}
				}
				
				foreach(explode("\r\n", strip_tags($homepage_arr)) as $v){
					$tmp_domain = $objProgram->getDomainByHomepage($v, "fi");
					if(isset($tmp_domain["domain"])){
						$domain_arr[] = current($tmp_domain["domain"]);
					}
				}
				$domain_arr = array_unique($domain_arr);
				
				foreach($domain_arr as $k_d => $v_d){
					if(in_array($v_d,$formatProrgam->network_keywords)){
						unset($domain_arr[$k_d]);
					}
				}
				if(count($domain_arr)){
//					$objProgram->checkProgramDomain($pid, $domain_arr);
				}else{
					$flag_is_active = true;
				}

				$update_sql[$pid]['Domain'] = implode("\r\n", $domain_arr);
				$prgm_arr[$pid]['Domain'] = $update_sql[$pid]['Domain'];
				if((isset($prgm_arr[$pid]['Domain']) && $prgm_arr[$pid]['Domain'] != $update_sql[$pid]['Domain']) || !isset($prgm_arr[$pid]['Domain'])){
					$update_sql[$pid]['LastChangeTime'] = $date;
				}
			}
			if($flag_shipping_country)
			{
				$shipping_country = '';
				$global_country_arr = array('all Countries','all','worldwide','global');
				$shipping_arr = $m = array();
				if(!empty($prgm_arr[$pid]['ShippingCountryManual']) && strcasecmp($prgm_arr[$pid]['ShippingCountryManual'], 'global') != 0){
					preg_match_all("/(?:[^a-zA-Z]|)($formatProrgam->country_code)(?:[^a-zA-Z]+|$)/i", $prgm_arr[$pid]["ShippingCountryManual"], $m);
					$shipping_country = strtolower(implode(",", $m[1]));
				}
				else
				{
					$data['TargetCountryExt'] = $prgm_arr[$pid]['TargetCountryExt'];
					$data['Name'] = $prgm_arr[$pid]['Name'];
					$update_sql[$pid]['ShippingCountry'] = $formatProrgam->execute('ShippingCountry',$data,isset($controller[$prgm_arr[$pid]['AffId']]['ShippingCountry'])?$controller[$prgm_arr[$pid]['AffId']]['ShippingCountry']:'');
					if(!isset($prgm_arr[$pid]['ShippingCountry']) || $prgm_arr[$pid]['ShippingCountry'] != $shipping_country ){
						$update_sql[$pid]['LastChangeTime'] = $date;
					}
				}
			}

			if($flag_commission)
			{
				if($prgm_arr[$pid]["CommissionType"] != 'Unknown' && $prgm_arr[$pid]["CommissionUsed"] > 0.00){
					$update_sql[$pid]["CommissionType"] = $prgm_arr[$pid]["CommissionType"];
					$update_sql[$pid]["CommissionUsed"] = $prgm_arr[$pid]["CommissionUsed"];
					$update_sql[$pid]["CommissionCurrency"] = $prgm_arr[$pid]["CommissionCurrency"];
				}else{
					echo $controller[$prgm_arr[$pid]['AffId']]['Commission'];
					$tmp_comm = $formatProrgam->execute('Commission',$prgm_arr[$pid]["CommissionExt"],isset($controller[$prgm_arr[$pid]['AffId']]['Commission'])?$controller[$prgm_arr[$pid]['AffId']]['Commission']:'');
					$update_sql[$pid]["CommissionValue"] = isset($tmp_comm['CommissionValue']) ? $tmp_comm['CommissionValue'] : "";
					$update_sql[$pid]["CommissionType"] = (@$tmp_comm["CommissionType"] == "percent") ? "Percent" : "Value";
					$update_sql[$pid]["CommissionUsed"] = isset($tmp_comm["CommissionUsed"]) ? $tmp_comm["CommissionUsed"] : "";
					$update_sql[$pid]["CommissionIncentive"] = (@$tmp_comm["CommissionIncentive"] == "1") ? 1 : 0;
					$update_sql[$pid]["CommissionCurrency"] = isset($tmp_comm["CommissionCurrency"]) ? $tmp_comm["CommissionCurrency"] : "";
				}
//				$order = $objProgram->getProgramRank($pid);
			}
			
			print_r($update_sql);die;
			if($flag_support_deep_url)
			{

				$update_sql[$pid]['AffDefaultUrl'] = $formatProrgam->execute('AffDefaultUrl',$prgm_arr[$pid],isset($controller[$prgm_arr[$pid]['AffId']]['AffDefaultUrl'])?$controller[$prgm_arr[$pid]['AffId']]['AffDefaultUrl']:'');
				$update_sql[$pid]['DeepUrlTpl'] = $formatProrgam->execute('DeepUrlTpl',$prgm_arr[$pid],isset($controller[$prgm_arr[$pid]['AffId']]['DeepUrlTpl'])?$controller[$prgm_arr[$pid]['AffId']]['DeepUrlTpl']:'');
				$update_sql[$pid]['SupportDeepUrl'] = $prgm_arr[$pid]["SupportDeepUrl"];
			}

			if($flag_category){
				$categoryExt = trim($prgm_arr[$pid]['CategoryExt'],"-, \t\n\r\0\x0B");
				$data = array('CategoryExt' => $categoryExt,'AffId' => $prgm_arr[$pid]["AffId"]);
				$update_sql[$pid]['CategoryId'] = $formatProrgam->execute('Category',$data,isset($controller[$prgm_arr[$pid]['AffId']]['Category'])?$controller[$prgm_arr[$pid]['AffId']]['Category']:'');
			}
			
			if($flag_support_type)
			{
				if(!empty($prgm_arr[$pid]["SupportTypeManual"]) && $prgm_arr[$pid]["SupportTypeManual"] != '')
					$update_sql[$pid]["SupportType"] = $prgm_arr[$pid]["SupportTypeManual"];
				else
					$update_sql[$pid]["SupportType"] = $formatProrgam->execute('SupportType','',isset($controller[$prgm_arr[$pid]['AffId']]['SupportType'])?$controller[$prgm_arr[$pid]['AffId']]['SupportType']:'');
			}
			
			if($flag_is_active)
			{
				if(stripos($prgm_arr[$pid]['IsActiveManual'],'Active') !== false){
					$isactive = $prgm_arr[$pid]['IsActiveManual'];
				}
				else
				{
					$isactive = ($prgm_arr[$pid]["StatusInAff"] == "Active" && $prgm_arr[$pid]["Partnership"] == "Active") ? "Active" : "Inactive";
					if(empty($prgm_arr[$pid]['Domain'])){
						$isactive = 'Inactive';
					}
				}
				//TODO
				/*
				 * if outgoing url is empty,isactive should be inactive
				 */

				$update_sql[$pid]['IsActive'] = $isactive;
				if(!isset($prgm_arr[$pid]["IsActive"]) || $prgm_arr[$pid]['IsActive'] != $isactive ){
					$update_sql[$pid]['LastChangeTime'] = $date;
				}
			}
			
			if($flag_order){
				$update_sql[$pid]['Order'] = $formatProrgam->rankDefault($pid);
			}
			echo ++$j. "\t" . date("Y-m-d H:i:s") . "\t";
			if($j %8 == 0)
				echo PHP_EOL;

		}
		
		if($is_debug){
			print_r($update_sql);
			exit;
		}
		
		if(count($update_sql)){
			insertProgramIntell($update_sql);
		}
		
//		$sql = "update program_update_queue set `Status`='PROCESSED' where ProgramID in ($pids)";
//		$objProgram->objMysql->query($sql);

		if($max > 90000) break;
		$max++;
	}
/*	$sql = "UPDATE r_domain_program a INNER JOIN program_intell b ON a.pid = b.programid SET a.affdefaulturl = b.affdefaulturl, a.deepurltpl = b.deepurltpl	WHERE b.isactive = 'active' AND a.affdefaulturl = '' AND (b.affdefaulturl <> '' OR b.deepurltpl <> '')";
	$objProgram->objMysql->query($sql);
	$sql = "UPDATE r_domain_program a INNER JOIN program_intell b ON a.pid = b.programid SET a.deepurltpl = b.deepurltpl WHERE b.isactive = 'active' AND a.IsHandle = '0' AND a.deepurltpl = '' AND b.deepurltpl <> '' AND b.SupportDeepUrl = 'YES'";
	$objProgram->objMysql->query($sql);
	$sql = "UPDATE r_domain_program a INNER JOIN program_intell b ON a.pid = b.programid SET a.affdefaulturl = b.affdefaulturl, a.deepurltpl = b.deepurltpl	WHERE b.isactive = 'active' AND b.affid = 1 AND a.deepurltpl = '' AND b.deepurltpl <> '' AND b.SupportDeepUrl = 'YES'";
	$objProgram->objMysql->query($sql);
	$sql = "UPDATE r_domain_program a INNER JOIN program_intell b ON a.pid = b.programid SET a.affdefaulturl = b.affdefaulturl, a.deepurltpl = b.deepurltpl WHERE b.isactive = 'active' AND b.affid = 1 AND a.deepurltpl LIKE '%[SITEIDINAFF]%' AND b.deepurltpl = '' AND b.SupportDeepUrl <> 'YES'";
	$objProgram->objMysql->query($sql);*/
	
	
	
//	$i = 0;
//	$sql = "select a.programid from program_intell a inner join wf_aff b on a.affid = b.id where a.isactive = 'active' and b.isactive = 'no'";
//	$tmp_arr = $objProgram->objMysql->getRows($sql, 'programid');
//	$update_sql = array();
//	foreach($tmp_arr as $v){
//		$update_sql[] = array("ProgramId" => $v["programid"],
//										"IsActive" => 'Inactive',
//										"LastUpdateTime" => date("Y-m-d H:i:s"),
//										);
//		$i++;
//		if(count($update_sql) > 100){
//			$objProgram->insertProgramIntell($update_sql);
//			$update_sql = array();
//		}
//	}
//	if(count($update_sql)){
//		$objProgram->insertProgramIntell($update_sql);
//		$update_sql = array();
//	}
//	if(count($tmp_arr)){
//		$sql = "update r_domain_program set LastUpdateTime = '{$date}' where pid in (".implode(",", array_keys($tmp_arr)).")";
//		$objProgram->objMysql->query($sql);
//	}

//active affiliate
//	$sql = "SELECT a.batchprimarykeyvalue FROM `table_change_log_batch` a INNER JOIN `table_change_log_detail` b ON a.batchid = b.batchid WHERE b.FiledName = 'isactive' AND a.batchtablename = 'wf_aff' AND a.batchaction = 'edit' AND b.filedvalueto = 'yes' AND a.batchprimarykeyvalue > 0 and a.batchcreationtime > '".date("Y-m-d H:i", strtotime(" -5 minutes"))."'";
//	$tmp_arr = $objProgram->objMysql->getRows($sql, 'batchprimarykeyvalue');
//	if(count($tmp_arr)){
//		foreach($tmp_arr as $affid => $tmp){
//			$cmd = "php /home/bdg/program/cron/first_set_program_intell.php --affid=$affid --onlyactive --nottoredis > /home/bdg/program/cron/test/temp_$affid.log  2>&1 &";
//			system($cmd);
//			echo $cmd."\r\n";
//		}
//	}

	function insertProgramIntell($insert_sql){
		global $date,$objProgram;
		$default_value = array(
			'IsActive'=>'Inactive',
			'CommissionType'=>'Unknown',
			'CommissionUsed' => 0.00,
			'CommissionIncentive' => 0,
			'SupportDeepUrl' => 'Unknown',
			'SupportDeepUrlOut' => 'Unknown',
			'SupportFake' => 'Unknown',
			'Order' => 0,
			'SupportType' => 'All',
			'LastUpdateTime' => $date
		);
		$column_keys = array("ProgramId", "AffId", "IdInAff", "AffDefaultUrl", "DeepUrlTpl", "IsActive", "Domain", "CommissionValue", "CommissionType", "CommissionUsed", "CommissionIncentive", "SupportDeepUrl", "LastUpdateTime", "OutGoingUrl", "DeniedPubCode", "CommissionCurrency", "CountryCode", "ShippingCountry", "CategoryId", "Order", "LastChangeTime","SupportType");
		$pidArr = i_array_column($insert_sql,'ProgramId');
		$pids = trim(implode(',',$pidArr),',');
		$sql = "select `ProgramId`,`AffDefaultUrl`,`DeepUrlTpl`,`IsActive`,`CommissionValue`,`CommissionType`,`CommissionUsed`,`CommissionIncentive`,`CommissionCurrency`,`Domain`,`SupportDeepUrl`,`DeniedPubCode`,`OutGoingUrl`,`CountryCode`,`ShippingCountry`,`CategoryId`,`Order`,`SupportType`,`LastChangeTime` from program_intell  where ProgramId in ($pids)";
		$program_old_infos = $objProgram->objMysql->getRows($sql,'ProgramId');
		$log_sql = $ins_sql = '';
		foreach($insert_sql as $pid => $v){
			$tmp_insert = array();
			foreach($column_keys as $key){
				$tmp_insert[] = array_key_exists($key, $v) ? addslashes($v[$key]) : ((isset($program_old_infos[$v['ProgramId']][$key])) ? addslashes($program_old_infos[$v['ProgramId']][$key]) : (array_key_exists($key,$default_value)?$default_value[$key] : ''));
				
				if($key == "ProgramId" || $key == "IdInAff" || $key == "AffId")
					continue;
				
				if(isset($v[$key]) && isset($program_old_infos[$v['ProgramId']][$key]) && (strcasecmp($program_old_infos[$v['ProgramId']][$key],$v[$key]) != 0))
				{
					$v[$key] = is_numeric($program_old_infos[$v['ProgramId']][$key])?floatval($v[$key]):$v[$key];
				}
			}
			
			if($ins_sql == '')
			{
				$ins_sql = "INSERT INTO test_program_intell (`".implode("`,`", $column_keys)."`) VALUES ('".implode("','", $tmp_insert)."')";
			}
			else
			{
				$ins_sql .= ",('".implode("','", $tmp_insert)."')";
			}
		}
		if($ins_sql)
		{
			$ins_sql .= " ON DUPLICATE KEY UPDATE `AffDefaultUrl`=values(`AffDefaultUrl`),`DeepUrlTpl`=values(`DeepUrlTpl`),`IsActive`=values(`IsActive`),`Domain`=values(`Domain`),`CommissionValue`=values(`CommissionValue`),`CommissionType`=values(`CommissionType`),`CommissionUsed`=values(`CommissionUsed`),`CommissionIncentive`=values(`CommissionIncentive`),`SupportDeepUrl`=values(`SupportDeepUrl`),`LastUpdateTime`=values(`LastUpdateTime`),`OutGoingUrl`=values(`OutGoingUrl`),`DeniedPubCode`=values(`DeniedPubCode`),`CommissionCurrency`=values(`CommissionCurrency`),`CountryCode`=values(`CountryCode`),`ShippingCountry`=values(`ShippingCountry`),`CategoryId`=values(`CategoryId`),`Order`=values(`Order`),`LastChangeTime`=values(`LastChangeTime`),`SupportType`=values(`SupportType`)";
			$objProgram->objMysql->query($ins_sql);
		}
		echo "insert at :" . date("Y-m-d H:i:s") .PHP_EOL;
	}
	echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";