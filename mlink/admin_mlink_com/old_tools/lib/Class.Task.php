<?php
class Task
{
	function __construct($objMysql=null)
	{
		if($objMysql) $this->objMysql = $objMysql;
		else $this->objMysql = new Mysql(TASK_DB_NAME,TASK_DB_HOST,TASK_DB_USER,TASK_DB_PASS);
		
		$this->NormalEmailAddress = array(
			'couponsnapshot.com' => array('info','ranchen','support','pf','ltls','cg','couponalert',),
			'anycodes.com' => array('info','ranchen','support','pf','ltls','cg','couponalert',),
			'promopro.com' => array('info','ranchen','support','pf','ltls','cg','couponalert',),
			'couponsnapshot.co.uk' => array('info','support',),
			'promopro.co.uk' => array('info','support',),
			'halfdiscount.co.uk' => array('info','support',),
			'couponsnapshot.ca' => array('info','support',),
			'yessaving.ca' => array('info','support',),
			'irelandvouchercodes.com' => array('info','support',),
			'couponsnapshot.de' => array('info','support',),
			'mehrgutscheincodes.com' => array('info','support',),
			'allecodes.de' => array('info','support',),
			'couponsnapshot.com.au' => array('info','support',),
			'ozdiscount.com' => array('info','support',),
			'ozcoupons.com' => array('info','support',),
			'couponsnapshot.co.nz' => array('info','support',),
			'urcouponcode.com' => array('info','support',),
		);
		
		$this->MailAcntList = array(
			"csus" => array("couponsnapshot.com", "INFO_CSUS"),
			"csuk" => array("couponsnapshot.co.uk", "INFO_CSUK"),
			//"ppuk" => array("promopro.co.uk", "INFO_PPUK"),
			"csca" => array("yessaving.ca", "INFO_CSCA"),
			"csau" => array("couponsnapshot.com.au", "INFO_CSAU"),
			"csie" => array("irelandvouchercodes.com", "INFO_CSIE"),
			"csde" => array("couponsnapshot.de", "INFO_CSDE"),
			"csnz" => array("couponsnapshot.co.nz", "INFO_CSNZ"),
			"csfr" => array("couponsnapshot.co.fr", "INFO_CSFR"),
			"csin" => array("couponsnapshot.co.in", "INFO_CSIN"),
			"urus" => array("urcouponcode.com", "INFO_URUS")
		);
		
		$this->MailDomainToSite = array(
			"couponsnapshot.com" => "csus",
			"anycodes.com" => "csus",
			"promopro.com" => "csus",
			"couponsnapshot.co.uk" => "csuk",
			"promopro.co.uk" => "csuk",
			"halfdiscount.co.uk" => "csuk",
			"yessaving.ca" => "csca",
			"couponsnapshot.com.au" => "csau",
			"ozdiscount.com" => "csau",
			"ozcoupons.com" => "csau",
//			"irelandvouchercodes.com" => "csie",
			"couponsnapshot.de" => "csde",
			"mehrgutscheincodes.com" => "csde",
			"allecodes.de" => "csde",
//			"couponsnapshot.co.nz" => "csnz",
			"urcouponcode.com" => "urus",
			"codespromofr.com" => "csfr",
			"codespromoin.com" => "csin",
		);
	}

	function getSiteMysqlObj($site)
	{

		global $databaseInfo;
		if(!isset($databaseInfo) || !is_array($databaseInfo)) die("databaseInfo not found\n");
		if(!isset($this->MailAcntList[$site])) die("wrong site:$site\n");

		list(,$infoname) = $this->MailAcntList[$site];
		if(!isset($databaseInfo[$infoname . "_DB_NAME"])) die("database name not found\n");
		
		$db_name = $databaseInfo[$infoname . "_DB_NAME"];
		$db_host = $databaseInfo[$infoname . "_DB_HOST"];
		$db_user = $databaseInfo[$infoname . "_DB_USER"];
		$db_pass = $databaseInfo[$infoname . "_DB_PASS"];

		$objMysql = new Mysql($db_name,$db_host,$db_user,$db_pass);
		if($site == "csde" || $site == "csfr"){
			$objMysql->query("set names utf8");
		}
		return $objMysql;
	}
	function setNames(){
		$this->objMysql->query("set names utf8");
	}
	function getTrackingSiteMysqlObj($site)
	{
		global $databaseInfo;
		if(!isset($databaseInfo) || !is_array($databaseInfo)) die("databaseInfo not found\n");
		if(!isset($this->MailAcntList[$site])) die("wrong site:$site\n");
		list(,$infoname) = $this->MailAcntList[$site];
		if(!isset($databaseInfo[$infoname . "_TRACKING_DB_NAME"])) die("database name not found\n");
		
		$db_name = $databaseInfo[$infoname . "_TRACKING_DB_NAME"];
		$db_host = $databaseInfo[$infoname . "_TRACKING_DB_HOST"];
		$db_user = $databaseInfo[$infoname . "_TRACKING_DB_USER"];
		$db_pass = $databaseInfo[$infoname . "_TRACKING_DB_PASS"];
		
		$objMysql = new Mysql($db_name,$db_host,$db_user,$db_pass);
		return $objMysql;
	}
	
	function getAuthUser()
	{
		$user = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : "";
		return $user;
	}
	
	function GetUserNameFromMapping($arr_from)
	{
		if(!is_array($arr_from) || empty($arr_from)) return array();
		$arr_result = array();
		
		$arr_cond_editors = array();
		foreach($arr_from as $editor)
		{
			$editor = strtolower($editor);
			$arr_cond_editors[] = "'" . addslashes($editor) . "'";
			$arr_result[$editor] = $editor;
		}
		$now = date("Y-m-d H:i:s");
		//$sql = "select Id,StartDate,EndDate,ToEditorName,FromEditorNames from editor_mapping where ToEditorName in (" . implode(",",$arr_cond_editors) . ") and StartDate < now() and EndDate > now() order by StartDate desc limit 1";
		//$row = $this->objMysql->getFirstRow($sql);
		//For multiple mapping
		$sql = "select Id,StartDate,EndDate,ToEditorName,FromEditorNames from editor_mapping where ToEditorName in (" . implode(",",$arr_cond_editors) . ") and StartDate < '$now' and EndDate > '$now' order by StartDate desc";
		
		$rowArr = $this->objMysql->getRows($sql);
		foreach ($rowArr as $row){
		if(!empty($row))
			{
				$to_editors = preg_split("/[^a-zA-Z]/",$row["FromEditorNames"]);
				foreach($to_editors as $editor)
				{
					$editor = trim(strtolower($editor));
					if(empty($editor)) continue;
					$arr_result[$editor] = $editor;
				}
			}
		}
		return $arr_result;
	}
	
	function GetAllValidMapping()
	{
		$arr_result = array();
		$now = date("Y-m-d H:i:s");
		$sql = "select Id, StartDate, EndDate, ToEditorName, FromEditorNames from editor_mapping where StartDate < '$now' and EndDate > '$now' order by StartDate desc";
		
		$rows = $this->objMysql->getRows($sql);
		if(!empty($rows))
		{
			foreach ($rows as $value){
				$to_editors = preg_split("/[^a-zA-Z]/",$value["FromEditorNames"]);
				foreach($to_editors as $editor)
				{
					$editor = trim(strtolower($editor));
					if(empty($editor)) continue;
					$arr_result[$value["ToEditorName"]][$editor] = $editor;
				}
			}
		}
		return $arr_result;
	}
}
?>
