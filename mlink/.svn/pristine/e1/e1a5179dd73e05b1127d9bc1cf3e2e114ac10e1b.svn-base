<?php
include_once(dirname(__FILE__) . "/program_data_share.php");
define('SOAP_SERVER_URI', 'http://reporting.megainformationtech.com/ppc/api');
define('API_LOCATION', SOAP_SERVER_URI.'/ppc.php');

$user = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : "";
if(!$user && substr($_SERVER["REMOTE_ADDR"],0,8) == "192.168.") $user = "couponsn";
$tpl->assign("user", $user);

$action = trim($resObj->getStrNoSlashes("action"));

switch ($action) {
	case "doadd":
		$affid = trim($resObj->getStrNoSlashes('affiliatetype'));
		$idinaff = trim($resObj->getStrNoSlashes('idinaff'));
		$name = trim($resObj->getStrNoSlashes('name'));
		$homepage = trim($resObj->getStrNoSlashes('homepage'));
		$targetcountryint = trim($resObj->getStrNoSlashes('targetcountryint'));
		$contacts = trim($resObj->getStrNoSlashes('contacts'));
		$categories = trim($resObj->getStrNoSlashes('categories'));
		$partnership = trim($resObj->getStrNoSlashes('partnership'));
		
		$TMPolicy = trim($resObj->getStrNoSlashes('TMPolicy'));
		$TMTermsPolicy = trim($resObj->getStrNoSlashes('TMTermsPolicy'));
		$CommissionInt = trim($resObj->getStrNoSlashes('CommissionInt'));
		$GroupInc = trim($resObj->getStrNoSlashes('GroupInc'));
		
		if (empty($affid) || empty($idinaff) || empty($name)){
			echo "<Script Language=\"Javascript\">alert('Invalid Aff id.');</Script>";
			echo "<Script Language=\"Javascript\">history.go();</Script>";
		}
		
		$arr = array();		
		$arr['affid'] = $affid;
		$arr['idinaff'] = $idinaff;
		$arr['name'] = $name;
		$arr['homepage'] = $homepage;
		$arr['contacts'] = $contacts;
		$arr['categoryext'] = $categories;
		$arr['targetcountryint'] = $targetcountryint;
		$arr['statusinaff'] = 'Active';
		$arr['partnership'] = $partnership;
		$arr['addtime'] = date("Y-m-d H:i:s");
		$arr['lastupdatetime'] = date("Y-m-d H:i:s");
		$arr['creator'] = $user;
		
		foreach($arr as &$v){
			$v = addslashes($v);
		}
		
		$p_id = $programModel->doInsertProgram($arr);
		if($p_id){
			$int_arr = array();
			$int_arr['ProgramId'] = $p_id;
			$int_arr['TMPolicy'] = $TMPolicy;
			$int_arr['TMTermsPolicy'] = $TMTermsPolicy;
			$int_arr['LastUpdateTime'] = date("Y-m-d H:i:s");
			$int_arr['CommissionInt'] = $CommissionInt;
			$int_arr['ContactsInt'] = $contacts;
			$int_arr['GroupInc'] = $GroupInc;
			$programModel->insertProgramInt($int_arr);
			
			$sql = "insert ignore into program_intell (programid, affid, idinaff) values(".intval($p_id).", ".intval($affid).", '".addslashes($idinaff)."')";
			$programModel->objMysql->query($sql);
			
			try{
			    $client = new SoapClient(null,array('location' => API_LOCATION,'uri' => SOAP_SERVER_URI, 'login'=>'patrickni', 'password'=>'patrickni123456'));            
				
			    $d = new stdClass();
			    $d->aff = $post['affID']; //affiliate id
			    $d->pid = $post['IdInAff']; // meridinaff (programid)
			    $d->usr = $user; //user
			    $d->tm  = $TMPolicy; // TM Value
			    $d->tt  = $TMTermsPolicy; // TM+ Value
			    $rs = $client->__soapCall('setProgramTMS', array($d));
			    $rs = json_decode($rs);    
			   // var_dump($rs);				
			   //	exit;
			
			
			}
			catch (SoapFault $e) {
			    echo $e->faultcode."\n";
			   // echo $e->getMessage()."\n";
			   // exit(1);
				
			}
			catch (Exception $e) {
				echo $e->getMessage();
				//exit(1);        
			}
						
			system("php /home/bdg/program/cron/first_set_program_intell.php --affid=$affid --pid=$p_id &");
			
			echo "<Script Language=\"Javascript\">alert('add program succeed');</Script>";
			echo "<Script Language=\"Javascript\">window.opener.location.reload();</Script>";
			echo "<Script Language=\"Javascript\">location='program_edit.php?ID={$p_id}';</Script>";
		}else{
			echo "<Script Language=\"Javascript\">alert('add program failed');</Script>";
			echo "<Script Language=\"Javascript\">history.go(' -1');</Script>";
		}
		
		break;
	default:
		$affid = trim($resObj->getStrNoSlashes("affid"));		
		
		/*if (empty($affid)) {
			echo "<Script Language=\"Javascript\">alert('Invalid ID');</Script>";
			echo "<Script Language=\"Javascript\">self.close();</Script>";
		}*/
		$affiliatename = "";
		if($affid){
			$affiliateInfo = $programModel->getAffiliateInfoById($affid);
	    	$affiliatename = $affiliateInfo['Name'];
		}
	    
		
		//echo "<pre>";print_r($data);
		$tpl->assign("affid", $affid);
		$tpl->assign("affiliatename", $affiliatename);
		$tpl->display("program_add.tpl");
		break;
}



?>