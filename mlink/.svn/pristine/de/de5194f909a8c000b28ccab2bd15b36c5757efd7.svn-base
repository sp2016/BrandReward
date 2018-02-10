<?php
	include_once('conf_ini.php');
	include_once(INCLUDE_ROOT.'init.php');
	$objDomain = new Domain;
	if(isset($_POST['upcom']) && !empty($_POST['upcom'])){
		$val = $_POST['val'];
		$unit = $_POST['unit'];
		$id = $_POST['id'];
		$oldval = $_POST['oldval'];
		$uptime = date('Y-m-d H:i:s',time());
		if(empty($unit)){
			$type = 'Percent';
		}else{
			$type = 'Value';
			$unit = strtoupper($unit);
		}
		$sql = "insert into program_manual (`CommissionType`,`CommissionUsed`,`CommissionCurrency`,`ProgramId`,`LastUpdateTime`) VALUES('$type','$val','$unit',$id,'$uptime') ON DUPLICATE KEY UPDATE `CommissionType`='$type',`CommissionUsed`='$val',`CommissionCurrency`='$unit',`LastUpdateTime`='$uptime'";
		if($db->query($sql)){
			$sql = "Update program_intell Set CommissionBackup='$oldval' WHERE ProgramId= $id";
			$db->query($sql);
			echo 1;
		} else {
			echo 2;
		}
		die;
	}
	if(isset($_POST['subtc']) && !empty($_POST['subtc']))
	{
		$id = $_POST['id'];
		$val = addslashes($_POST['val']);
		$sql = "insert into program_int (`ProgramId`,`TermAndConditionInt`) VALUES('$id','$val') ON DUPLICATE KEY UPDATE `ProgramId`='$id',`TermAndConditionInt`='$val'";
		if($db->query($sql)){
			echo 1;
		}else{
			echo 2;
		}
		die;
	}
	if(isset($_POST['upinfo']) && !empty($_POST['upinfo'])){
		$id = $_POST['id'];
		if(empty($unit)){
			$type = 'Percent';
		}else{
			$type = 'Value';
		}
		$sql = "Update program_manual Set `CommissionUsed`='',`CommissionCurrency` ='' WHERE ProgramId= $id";
		if($db->query($sql)){
			$sql = "Update program_intell Set CommissionBackup='' WHERE ProgramId= $id";
			$db->query($sql);
			$sql = "select CommissionType,CommissionUsed,CommissionCurrency from program_intell WHERE ProgramId= $id";
			$res = $objDomain->getRow($sql);
			if($res['CommissionType'] == 'Percent'){
				$commission = $res['CommissionUsed']."%";
			}else{
				$commission = !empty($res['CommissionCurrency'])?$res['CommissionCurrency']:'USD'.$res['CommissionUsed'];
			}
			echo $commission;
		} else {
			echo 2;
		}
		die;
	}
	$program_arr = $objDomain->showAdvertiserSupportTypeList($_POST);

	$objTpl->assign('search', $_POST);
	$objTpl->assign('programs', $program_arr);
	$objTpl->assign('sys_header', $sys_header);
	echo $objTpl->fetch('b_merchant_support_type.html');
	