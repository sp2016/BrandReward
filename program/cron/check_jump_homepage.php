<?php 
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
include_once(INCLUDE_ROOT . "func/func.php");
include_once(INCLUDE_ROOT . "func/nodejs.php");
//global $newPrgmtime;
global $noChkPrgmtime;

echo "<< Start @ ".date("Y-m-d H:i:s")." >>\r\n";

$newPrgmtime = "2 hours";
$checkTime = date("Y-m-d H:i:s",strtotime("-".$newPrgmtime));
$noChkPrgmtime = "30 days";
$nocheckTime = date("Y-m-d H:i:s", strtotime("-".$noChkPrgmtime));
$objProgram = New Program();
$sql = "select affid from aff_crawl_config where Status = 'Active' and ProgramCrawlStatus = 'Yes'";
$checkaff_arr = $objProgram->objPendingMysql->getRows($sql);
//print_r($checkaff_arr);exit;
//$checkaff_arr = array(57);
$changeWords = '';
foreach ($checkaff_arr as $checkaff)
{
	$affid = $checkaff['affid'];
	$checkPrograms = selectNeedCheckProgram($affid,$checkTime,$noChkPrgmtime,$objProgram);
	$changeWords .= checkProgramHomepage($checkPrograms,$objProgram) . "\r\n";
	
}
//print_r($changeWords);exit;

//send alert
$alert_subject = "Homepage jump change: " . date("Y-m-d H:i:s");
$to = "stanguan@meikaitech.com,lightzhang@meikaitech.com";
AlertEmail::SendAlert($alert_subject,nl2br($changeWords), $to);


function selectNeedCheckProgram($affid,$checkTime,$nocheckTime,$objProgram)
{
	echo "start get need to be checked program from aff $affid\r\n ";
	
	//之前从未检查过的program
	$noCheckProgram = array();
	$sql = "select a.AffID,c.name as AffName,a.ID as PID,a.name as programName,a.Homepage from program as a left join chk_homepage_jump_change as b on a.id = b.pid left join wf_aff as c on a.AffID = c.ID where a.AffId = $affid and b.pid is null and a.StatusInAff = 'Active' and a.Partnership = 'Active'";
	$noCheckProgram = $objProgram->objMysql->getRows($sql, "PID");
		
	//新的program
	echo "get new programs from aff $affid\r\n";
	$newProgram = array();
	$sql = "select a.AffID,b.name as AffName,a.ID as PID,a.name as programName,a.Homepage from program as a left join wf_aff as b on a.AffID = b.ID where a.AffId = $affid and AddTime > '$checkTime' and a.StatusInAff = 'Active' and a.Partnership = 'Active'";
	$newProgram = $objProgram->objMysql->getRows($sql, "PID");
	//print_r($newProgram);exit;
	
	//homepage修改过的program
	echo "get homepage was changed programs from aff $affid\r\n";
	$changeProgram = array();
	$sql = "select a.AffID,b.name as AffName,a.ProgramId as PID,c.name as programName,a.FieldValueNew as Homepage from program_change_log as a left join wf_aff as b on a.AffID = b.ID left join program as c on a.ProgramId = c.ID where a.AffId = $affid and a.FieldName = 'Homepage' and c.StatusInAff = 'Active' and c.Partnership = 'Active'";
	$changeProgram = $objProgram->objMysql->getRows($sql, 'PID');
	//print_r($changeProgram);exit;
	
	//一个月前检查过的program
	echo "get 1 mouth wasn't checked programs from aff $affid\r\n";
	$aMouthCheckProgram = array();
	$sql = "select a.AffID,c.name as AffName,b.PID as PID,a.name as programName,a.Homepage from program as a inner join chk_homepage_jump_change as b on a.ID = b.PID left join wf_aff as c on a.AffID = c.ID where a.AffId = $affid and CheckTime < '$nocheckTime' and a.StatusInAff = 'Active' and a.Partnership = 'Active'";
	$aMouthCheckProgram = $objProgram->objMysql->getRows($sql, "PID");

	//上次CRUL错位的program
	echo "get curl error programs from aff $affid\r\n";
	$curlerrorProgram = array();
	$sql = "select a.AffID,c.name as AffName,b.PID as PID,a.name as programName,a.Homepage from program as a inner join chk_homepage_jump_change as b on a.ID = b.PID left join wf_aff as c on a.AffID = c.ID where a.AffId = $affid and HttpNormal = false and a.StatusInAff = 'Active' and a.Partnership = 'Active'";
	$curlerrorProgram = $objProgram->objMysql->getRows($sql, "PID");
	
	$checkProgram = array_merge($newProgram, $changeProgram, $aMouthCheckProgram, $curlerrorProgram);
	return $checkProgram;
}

function checkProgramHomepage($arr_prgm,$objProgram)
{
	//$change_prgm = array();
	$changeWord = '';
	$changedCount = 0;
	$checkCount = 0;
	$newCount = 0;
	foreach ($arr_prgm as $prgm)
	{
		$url = $prgm['Homepage'];
		$PID = $prgm['PID'];
		if(empty($url) || empty($PID))
			continue;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		// 不需要页面内容
		curl_setopt($ch, CURLOPT_NOBODY, 1);
		// 不直接输出
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// 返回最后的Location
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_exec($ch);
		$lastUrl = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);					//最后一个有效的URL地址
		$httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		$urlDomain_arr = $objProgram->getDomainByHomepage($url);
		$urlDomain = $urlDomain_arr[0];
		$lastUrlDomain_arr = $objProgram->getDomainByHomepage($lastUrl);
		$lastUrlDomain = $lastUrlDomain_arr[0];
		//print_r($httpCode."\r\n");exit;
		
		$sql = "SELECT PID FROM chk_homepage_jump_change WHERE PID = $PID";
		$programid_arr = $objProgram->objMysql->getRows($sql);
		
		
		//print_r($httpCode."    $url"."\r\n"."       ".$lastUrl."\r\n");
		if($urlDomain != $lastUrlDomain)
		{	
			//echo "changed\n\r";
			//print_r($httpCode."    $url"."\r\n"."       ".$lastUrl."\r\n");
			if(preg_match('/\b2\d{2}\b/', $httpCode))
			{
				$changeWord .= "Error,aff $prgm[AffID] $prgm[AffName]'s program ,PID is $PID and named $prgm[programName],its Homepage changed from '$url' to '$lastUrl',the Homepage shouldn't change;\n\r";
				if (isset($programid_arr[0]))
				{
					$date = date("Y-m-d H:i:s");
					$sql = "update chk_homepage_jump_change set CheckTime = '$date',HttpNormal = false where PID = $PID";
					$objProgram->objMysql->query($sql);
				}else{
					$date = date("Y-m-d H:i:s");
					$sql = "insert into chk_homepage_jump_change (PID,CheckTime,HttpNormal) values ($PID,'$date',false)";
					$objProgram->objMysql->query($sql);
					$newCount++;
				}
			}else{
				$sql = "select PID,HomepageInt from program_int where ProgramId = $PID and HomepageInt is not null and HomepageInt != ''";
				$homepageInt_arr =  $objProgram->objMysql->getRows($sql);
				
				if(isset($homepageInt_arr[0]))
				{
					$homepageInt = $homepageInt_arr[0][HomepageInt];
					$changeWord .= "aff $prgm[AffID] $prgm[AffName]'s program ,PID is $PID and named $prgm[programName],its HomepageINT already existing,is '$homepageInt';\n\r";
				}
				
				$sql = "update program_int set HomepageInt = '$lastUrl' where ProgramId = $PID";
				$objProgram->objMysql->query($sql);
				//$changeWord .= "affID:$prgm[AffID], AffName:$prgm[AffName], PID:$PID, programName:$prgm[programName], OldHomepage:$url, NewHomepage:$lastUrl;\n\r";
				$changeWord .= "aff $prgm[AffID] $prgm[AffName]'s program ,PID is $PID and named $prgm[programName],its Homepage changed from '$url' to '$lastUrl';\n\r";
				$changedCount++;
				
				if (isset($programid_arr[0]))
				{
					$date = date("Y-m-d H:i:s");
					$sql = "update chk_homepage_jump_change set CheckTime = '$date',HttpNormal = true where PID = $PID";
					$objProgram->objMysql->query($sql);
				}else{
					$date = date("Y-m-d H:i:s");
					$sql = "insert into chk_homepage_jump_change (PID,CheckTime,HttpNormal) values ($PID,'$date',true)";
					$objProgram->objMysql->query($sql);
					$newCount++;
				}
			}
		}else{
			//echo "not changed\n\r";
			//print_r($httpCode."    $url"."\r\n"."       ".$lastUrl."\r\n");
			if (isset($programid_arr[0]))
			{
				$date = date("Y-m-d H:i:s");
				$sql = "update chk_homepage_jump_change set CheckTime = '$date',HttpNormal = true where PID = $PID";
				$objProgram->objMysql->query($sql);
			}else{
				$date = date("Y-m-d H:i:s");
				$sql = "insert into chk_homepage_jump_change (PID,CheckTime,HttpNormal) values ($PID,'$date',true)";
				$objProgram->objMysql->query($sql);
				$newCount++;
			}
		}
		
		
		//print_r($httpCode."\r\n");
		
		$checkCount++;
	}
	return $changeWord;
	echo "check program $checkCount,and there $newCount is new;\r\n";
	echo "there are $changedCount program's homepage changed.\r\n";
}






































echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";
exit;

?>