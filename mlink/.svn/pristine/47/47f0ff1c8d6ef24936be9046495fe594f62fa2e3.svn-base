<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

include_once(dirname(__FILE__) . "/program_data_share.php");
define('SOAP_SERVER_URI', 'http://reporting.megainformationtech.com/ppc/api');
define('API_LOCATION', SOAP_SERVER_URI.'/ppc.php');

$user = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : "";
if(!$user && substr($_SERVER["REMOTE_ADDR"],0,8) == "192.168.") $user = "couponsn";
$tpl->assign("user", $user);

$action = trim($resObj->getStrNoSlashes("action"));

switch ($action) {
	case "desc":
		$ID = intval($resObj->getStrNoSlashes('ID'));
		$data = $programModel->getProgramByID($ID, false);
		echo $data["Description"];
		exit;
	case "commissionExt":
		$ID = intval($resObj->getStrNoSlashes('ID'));
		$data = $programModel->getProgramByID($ID, false);
		echo $data["CommissionExt"];
		exit;
	case "editfinish":
		$ID = intval($resObj->getStrNoSlashes('ID'));
		$post = filterStrSlashes($_POST);
				
		if (empty($post['tmp']['internal']['ApplyDate']) || ($post['tmp']['internal']['ApplyDate'] == $post['applydateold'])) {
			unset($post['tmp']['internal']['ApplyDate'], $post['tmp']['internal']['ApplyOperator']);
		}
		
		if (!empty($post['tmp']['internal']['SupportSpread']) && is_array($post['tmp']['internal']['SupportSpread'])) {
			$post['tmp']['internal']['SupportSpread'] = implode(",", $post['tmp']['internal']['SupportSpread']);
		}else{
			$post['tmp']['internal']['SupportSpread'] = "";
		}		
		
		$res1 = $programModel->insertProgramChangeLog($post['tmp'], $ID, false);
		
		$remindata = array();
		if (!empty($post['remind']['RemindDate']) && !empty($post['remind']['Message'])) {
			$remindata = $post['remind'];
			$remindata['ProgramId'] = $post['ID'];
			$remindata['AffId'] = $post['affID'];
			$remindata['Operator'] = $user;
		}
		
		if ($res1) {
			$TMPolicy = $post['tmp']['internal']['TMPolicy'];
			$TMTermsPolicy = $post['tmp']['internal']['TMTermsPolicy'];
			$InquiryStatus = $post['tmp']['internal']['InquiryStatus'];
			$SEMPolicyRemark = $post['tmp']['external']['SEMPolicyRemark'];
			$setTm = $programModel->checkTMS($ID, $TMPolicy, $TMTermsPolicy, $InquiryStatus, $SEMPolicyRemark);
			$set_PPC_log = false;
			if($InquiryStatus == 'Inquiring' || $InquiryStatus == 'Inquired'){
				$set_PPC_log = $programModel->checkPPC($ID, $InquiryStatus);
			}
			
			//sem policy
			$sem_policy_log = false;
			if($InquiryStatus == 'Inquired'){
				$sem_policy_log = $programModel->checkSemPolicy($ID, $TMPolicy, $TMTermsPolicy);
			}
			
			//re-apply status
			$ReApplyStatus = $post['tmp']['internal']['ReApplyStatus'];
			$PartnershipChangeReason = $post['tmp']['external']['PartnershipChangeReason'];			
			$set_reapply_log = false;
			if($ReApplyStatus != 'UNKNOWN'){
				$set_reapply_log = $programModel->checkReApplyStatus($ID, $ReApplyStatus);
				
				$programinfo = $programModel->getProgramByID($ID);
				if($PartnershipChangeReason != $programinfo['PartnershipChangeReason'] && $PartnershipChangeReason != ""){
					$set_reapply_log = true;
				}
			}
			
			
			
			$res = $programModel->updateProgram($post['tmp'], $ID);
			if ($res) {
				$RealDomain = $post['RealDomain'];
				$CommissionUsed = $post['CommissionUsed'];
				$CommissionType = $post['CommissionType'];
				$StatusInBdg = $post['StatusInBdg'];				
				$currency = empty($post['CommissionCurrency'])?'':',CommissionCurrency="'.addslashes($post['CommissionCurrency']).'"';

				$sql = "select programid from program_manual where programid = $ID";
				$tmp_row = $programModel->objMysql->getFirstRow($sql);
				if($tmp_row){
					$sql = 'UPDATE program_manual SET RealDomain = "'.addslashes($RealDomain).'",CommissionUsed = '.floatval($CommissionUsed).',CommissionType= "'.addslashes($CommissionType).'",StatusInBdg = "'.addslashes($StatusInBdg).'"' .$currency.' WHERE ProgramId = '.$ID;
				}else{
					$sql = 'INSERT INTO program_manual SET RealDomain = "'.addslashes($RealDomain).'",CommissionUsed = '.floatval($CommissionUsed).',CommissionType= "'.addslashes($CommissionType).'",StatusInBdg = "'.addslashes($StatusInBdg).'",ProgramId = '.$ID.$currency;
				}
				$programModel->objMysql->query($sql);
				
				if($set_PPC_log === true || $sem_policy_log === true){
					//insert work log
					$objMysqlTask = new Mysql("task", "bcg01.i.mgsvr.com", "couponsn", "rrtTp)91aLL1");
					$addNewLog = true;
					$now = date("Y-m-d H:i:s");
					$remark = $SEMPolicyRemark."\n";
					$tracelog = "{$InquiryStatus} by {$user} @ " . substr($now, 0, 10). "\n".$remark;
					if($InquiryStatus == "Inquiring"){
						$log_status = "In-Progress";
					}else{
						if($TMTermsPolicy == "ALLOWED" || $TMPolicy == "ALLOWED"){
							$log_status = "Positive";
						}else{
							$log_status = "Negative";
						}
					}
					$sql = "select id, tracelog from bd_work_log where programid = $ID and idinaff = '".addslashes($post['IdInAff'])."' and status = 'In-Progress' and type = 'PPCPolicy' LIMIT 1";
					$check_bd_log = array();
					$check_bd_log = $objMysqlTask->getFirstRow($sql);
					if($check_bd_log){
						$addNewLog = false;
						$logid = $check_bd_log["id"];
						$tracelog .= "\n".$check_bd_log["tracelog"];
					}
						
					if($addNewLog){
						$sql = "INSERT IGNORE INTO bd_work_log(type,storeid,affiliateid,programid,idinaff,status,tracelog,result,contact,adduser,lastupdateuser,addtime,lastupdatetime) VALUES('PPCPolicy', '', {$post['affID']}, $ID, '".addslashes($post['IdInAff'])."', '".addslashes($log_status)."', '".addslashes($tracelog)."', '".addslashes($remark)."', '', '".addslashes($user)."', '".addslashes($user)."', '".$now."', '".$now."')";
						$objMysqlTask->query($sql);
					}elseif($logid){
						$sql = "UPDATE bd_work_log SET status = '".addslashes($log_status)."', tracelog = '".addslashes($tracelog)."', result = '".addslashes($remark)."', lastupdateuser = '".addslashes($user)."', lastupdatetime = '{$now}' WHERE id = {$logid}";
						$objMysqlTask->query($sql);
					}
				}

				if($set_reapply_log === true){
					//insert work log
					$objMysqlTask = new Mysql("task", "bcg01.i.mgsvr.com", "couponsn", "rrtTp)91aLL1");
					$addNewLog = true;
					$now = date("Y-m-d H:i:s");
					$remark = $PartnershipChangeReason."\n";
					$tracelog = "{$ReApplyStatus} by {$user} @ " . substr($now, 0, 10). "\n".$remark;
					$log_status = $ReApplyStatus;
					$sql = "select id, tracelog from bd_work_log where programid = $ID and idinaff = '".addslashes($post['IdInAff'])."' and status = 'In-Progress' and type = 'DeclinedProgramHandle' LIMIT 1";
					$check_bd_log = array();
					$check_bd_log = $objMysqlTask->getFirstRow($sql);
					if($check_bd_log){
						$addNewLog = false;
						$logid = $check_bd_log["id"];
						$tracelog .= "\n".$check_bd_log["tracelog"];
					}
					
					if($addNewLog){
						$sql = "INSERT IGNORE INTO bd_work_log(type,storeid,affiliateid,programid,idinaff,status,tracelog,result,contact,adduser,lastupdateuser,addtime,lastupdatetime) VALUES('DeclinedProgramHandle', '', {$post['affID']}, $ID, '".addslashes($post['IdInAff'])."', '".addslashes($log_status)."', '".addslashes($tracelog)."', '".addslashes($remark)."', '', '".addslashes($user)."', '".addslashes($user)."', '".$now."', '".$now."')";
						$objMysqlTask->query($sql);
					}elseif($logid){
						$sql = "UPDATE bd_work_log SET status = '".addslashes($log_status)."', tracelog = '".addslashes($tracelog)."', result = '".addslashes($remark)."', lastupdateuser = '".addslashes($user)."', lastupdatetime = '{$now}' WHERE id = {$logid}";
						$objMysqlTask->query($sql);
					}
				}
				
				if($setTm && substr($_SERVER["REMOTE_ADDR"],0,8) != "192.168."){
					try{
					    $client = new SoapClient(null,array('location' => API_LOCATION,'uri' => SOAP_SERVER_URI, 'login'=>'patrickni', 'password'=>'patrickni123456'));            
					
					    $d = new stdClass();
					    $d->aff = $post['affID']; //affiliate id
					    $d->pid = $post['IdInAff']; // meridinaff (programid)
					    $d->usr = $user; //user
					    $d->tm  = $TMPolicy; // TM Value
					    $d->tt  = $TMTermsPolicy; // TM+ Value
					    					    
					    $d->in  = $InquiryStatus; // INQURIY Value
					    $d->re  = $SEMPolicyRemark; // REMARK Value
					    
					    $rs = $client->__soapCall('setProgramTMS', array($d));
					    $rs = json_decode($rs);    
					    //var_dump($rs);
					    //echo 1;
					   	//exit;					
					
					}
					catch (SoapFault $e) {
					    echo $e->faultcode."\n";
					    echo $e->getMessage()."\n";
					   // print_r($d);
					   // echo 2;
					   // exit(1);
					
					}
					catch (Exception $e) {
					   // echo $e->getMessage();
					   // echo 3;
					   // exit(1);        
					}
				}
				//echo "php /home/bdg/program/cron/first_set_program_intell.php --affid={$post['affID']} --pid=$ID &";
				system("php /home/bdg/program/cron/first_set_program_intell.php --affid={$post['affID']} --pid=$ID &");
				
				echo "<Script Language=\"Javascript\">alert('Edit program successfully');</Script>";
				echo "<Script Language=\"Javascript\">window.opener.location.reload();</Script>";
			    echo "<Script Language=\"Javascript\">location.replace(location.href);</Script>";
			    exit;
			}
		}
		echo "<Script Language=\"Javascript\">alert('Edit program failed');</Script>";
		echo "<Script Language=\"Javascript\">self.close();</Script>";
		
		break;
	default:
		$ID = intval($resObj->getStrNoSlashes("ID"));
		$data = $programModel->getProgramByID($ID);
		
		if (empty($data)) {
			echo "<Script Language=\"Javascript\">alert('Invalid ID');</Script>";
			echo "<Script Language=\"Javascript\">self.close();</Script>";
		}
		
		$affiliateInfo = $programModel->getAffiliateInfoById($data['AffId']);
	    $data['affiliatename'] = isset($affiliateInfo['Name']) ? $affiliateInfo['Name'] : '';
	    $data['IsInHouse'] = $affiliateInfo['IsInHouse'];
		
		if (!empty($data['TargetCountryInt'])) {
		    $data['TargetCountryIntArr'] = explode(',', $data['TargetCountryInt']);
			foreach ((array)$data['TargetCountryIntArr'] as $key => $val) {
				if(isset($countryArr[$val])){
	            	$data['TargetCountryIntFullNameArr'][$val] = $countryArr[$val];
				}
			}
		}
		
		
		$SupportSpread_arr = array();
		$SupportSpread_arr = explode(",", $data["SupportSpread"]);
		$SupportSpread_arr = array_flip($SupportSpread_arr);
		$tpl->assign("SupportSpread_arr", $SupportSpread_arr);
		
		
		
		$AllSite = array("csus","csuk","csau","csca","csde","csfr","hd","ds");
		$tpl->assign("AllSite", $AllSite);
		
		$sql = "select * from program_intell a where a.programid = $ID";
		$prgm_intell = $programModel->objMysql->getFirstRow($sql);
		$tpl->assign("prgm_intell", $prgm_intell);
		
		$sql = "select * from program_manual b where b.programid = $ID";
		$prgm_manual = $programModel->objMysql->getFirstRow($sql);
		$tpl->assign("prgm_manual", $prgm_manual);
		
		//echo "<pre>";print_r($data);
		$tpl->assign("data", $data);
		$tpl->display("program_edit.tpl");
		break;
}



?>