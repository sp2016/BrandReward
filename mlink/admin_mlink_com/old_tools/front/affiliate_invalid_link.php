<?php

include_once(dirname(dirname(__FILE__))."/etc/const.php");
include_once(INCLUDE_ROOT . "func/remote.auth.func.php");
include_once(INCLUDE_ROOT . "lib/Class.TemplateSmarty.php");
include_once(INCLUDE_ROOT . "lib/Class.Mysql.php");
include_once(INCLUDE_ROOT . "lib/Class.MyException.php");
include_once(INCLUDE_ROOT . "lib/Class.Request.php");
define("SYS_FUNC_ID", 427);

$tpl = new TemplateSmarty();
$resobj = new Request();
$objMysql = new Mysql(TASK_DB_NAME, TASK_DB_HOST, TASK_DB_USER, TASK_DB_PASS);
$action = strtolower(trim($_GET["action"]));
$addtime_from = trim($resobj->getStrNoSlashes("addtime_from"));
if($addtime_from) $addtime_from1 = date("Y-m-d H:i:s",strtotime($addtime_from) - 57600);
$addtime_to = trim($resobj->getStrNoSlashes("addtime_to"));
if($addtime_to)	$addtime_to1 = date("Y-m-d H:i:s",strtotime($addtime_to) - 57600);
$keyword = trim($resobj->getStrNoSlashes("keyword"));
$onepage = intval($_GET["onepage"]);
if(empty($onepage))
	$perpage = intval($resobj->getStrNoSlashes("onepage"));
else
	$perpage = $onepage;
if ($perpage < 1 || $perpage > 50 || empty($perpage)) $perpage = 50;
setcookie("onepage", $perpage, time() + 2592000);
$_COOKIE['onepage'] = $perpage;
$tpl->assign("perpage", $perpage);

switch($action)
{
	case "stutus":
		$id = intval($_GET["id"]);
		$status = trim($_GET["status"]);
		if(empty($id) || empty($status)){
			echo "error";
			exit();
		}
		$user = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : "";
		if(!$user && substr($_SERVER["REMOTE_ADDR"], 0, 8) == "192.168.") $user = "couponsn";
		$row['Operator']=$user;
		$row['Status']=$status;
		$res1 = updateData($row,$id);
		if ($res1) {
			echo "success";	
		}else{
			echo "error";
		}
		exit();	
		break;
	case "list":
	default:
		$onepage = intval($_GET["onepage"]);
		if(empty($onepage))
			$perpage = intval($resobj->getStrNoSlashes("onepage"));
		else
			$perpage = $onepage;
		if ($perpage < 1 || empty($perpage)) $perpage = 50;
		if ($perpage > 500) $perpage = 500;
		setcookie("onepage", $perpage, time()+60*60*24*30);		
		$_COOKIE['onepage'] = $perpage;
		$tpl->assign("perpage", $perpage);

		$condition = array('sql' => " and IsShow='YES' ", 'order' => '', 'limit' => '');
		$status = trim($resobj->getStrNoSlashes("status"));
		if (empty($status)) $status = "NEW";
		$affiliate = trim($resobj->getStrNoSlashes("affiliate"));
		if (empty($affiliate)) $affiliate = "All";
		$CSSites = trim($resobj->getStrNoSlashes("CSSites"));
		if (empty($CSSites)) $CSSites = "All";
		if ($status != 'All')
			$condition['sql'] .= " and Status='{$status}' ";
		if ($affiliate != 'All')
			$condition['sql'] .= " and affiliate='{$affiliate}' ";
		if ($CSSites != 'All')
			$condition['sql'] .= " and CSSites='{$CSSites}' ";
		if($addtime_from && $addtime_to)
			$condition['sql'] .= " and AddTime >= '".$addtime_from1."' and AddTime <= '".$addtime_to1."'";
		elseif ($addtime_from)
			$condition['sql'] .= " and AddTime >= '".$addtime_from1."' ";
		elseif ($addtime_to)
			$condition['sql'] .= " and AddTime <= '".$addtime_to1."' and AddTime != '0000-00-00 00:00:00'";
		if ($keyword)
			$condition['sql'] .= sprintf(" and (ProgramName like '%%%s%%' or MerchantName like '%%%s%%') ", addslashes($keyword), addslashes($keyword));
		$countData = getListByCondition($condition, "count(*) as cnt");
		$count = isset($countData[0]['cnt']) ? $countData[0]['cnt'] : 0;

		include_once(INCLUDE_ROOT . "lib/Class.Page.php");
		$objPB = new OPB($count, $perpage);
		$objPB->onepage = $perpage;
		$pagebar = $objPB->whole_bar(3, 8);
		$pagebar1 = $objPB->whole_bar(4, 8);

		$order = trim($resobj->getStrNoSlashes("order"));
		if (empty($order) || $order == 'DEFAULT')
			$condition['order'] = "`affiliate` ASC, `ProgramID` ASC, `AddTime` DESC";
		else
			$condition['order'] .= " {$order} ";
		$condition['limit'] .= $objPB->offset . ", " . $perpage;
		$data = getListByCondition($condition, '*');
		foreach ($data as $k => $v) {
			$data[$k]['ProgramName'] =stripslashes($v['ProgramName']);
			$data[$k]['CreativeType'] =stripslashes($v['CreativeType']);
			$data[$k]['Reason'] =stripslashes($v['Reason']);
			$data[$k]['MerchantName'] =stripslashes($v['MerchantName']);
			$data[$k]['Details'] =nl2br(stripslashes($v['Details']));
			if(!empty($v['ProgramID']) || !empty($v['ProgramName'])){
				$CsMerchantRow=getCsMerchantRow($v['affiliate'],trim($v['ProgramID']),trim($v['ProgramName']));
				if(is_array($CsMerchantRow)){
					$data[$k]['CsMerchantSite'] =$CsMerchantRow['Site'];
					$data[$k]['CsMerchantId'] =$CsMerchantRow['MerchantId'];
					$data[$k]['CsMerchantName'] =stripslashes($CsMerchantRow['MerchantName']);
					$data[$k]['CsMerchantPage'] = $g_SiteUrl[strtolower($CsMerchantRow['Site'])]['front'] . "/front/merchant.php?mid=" . $CsMerchantRow['MerchantId'];
				}
			}
			if (!empty($data[$k]['AddTime']))
				$data[$k]['AddTime'] = date("Y-m-d H:i:s",strtotime($data[$k]['AddTime']) + 57600);
		}
		$status_all_arr = array('All' => 'All', 'NEW' => 'NEW','DONE' => 'DONE', 'IGNORED' => 'IGNORED');
		$tpl->assign("status_all_arr", $status_all_arr);
		$aff_all_arr = array(
				'All'	=> 'All',
				'1'		=> 'Commission Junction',
				'6'		=> 'PepperJam Network',
				'12'	=> 'LinkConnector',
				'7'		=> 'ShareASale',
				'133'	=> 'TradeDoubler',
				'13'	=> 'Webgains UK',
				'14'	=> 'Webgains US',
				'18'	=> 'Webgains IE',
				'34'	=> 'Webgains DE',
				'52'	=> 'TradeTracker UK',
				'65'	=> 'TradeTracker DE',
				'46'	=> 'clixGalore'
				);
		$CSSites_arr = array(
				'All'	=> 'All',
				"CSUS"	=> "CSUS",
				"CSUK"	=> "CSUK",
				"CSAU"	=> "CSAU",
				"CSCA"	=> "CSCA",
				"CSNZ"	=> "CSNZ",
				"CSDE"	=> "CSDE",
				"CSIE"	=> "CSIE",
				"PC2012"=> "PC2012",
				"DC2012"=> "DC2012",
				"C4LP"	=> "C4LP",
				"ANYP"	=> "ANYP",
				"APC"	=> "APC",
				"TASK"	=> "TASK",
				"Unknown" => "Unknown"
				);
		$ordey_arr = array(
				'DEFAULT' => 'default (Program - ASC, Add Time - DESC)',
				'AddTime ASC' => 'Add Time - ASC',
				'Clicks ASC' => 'Clicks - ASC',
				'AddTime DESC' => 'Add Time - DESC',
				'Clicks DESC' => 'Clicks - DESC'
		);
		$tpl->assign("ordey_arr", $ordey_arr);
		$tpl->assign("order", $order);
		$tpl->assign("aff_all_arr", $aff_all_arr);
		$tpl->assign("CSSites_arr", $CSSites_arr);
		$tpl->assign("status", $status);
		$tpl->assign("affiliate", $affiliate);
		$tpl->assign("CSSites", $CSSites);
		$tpl->assign("data", $data);
		$tpl->assign("pagebar", $pagebar);
		$tpl->assign("pagebar1", $pagebar1);
		$tpl->assign("addtime_from", $addtime_from);
		$tpl->assign("addtime_to", $addtime_to);
		$tpl->assign("keyword", $keyword);
		$tpl->display("affiliate_invalid_link.tpl");
		exit;
}

function getCsMerchantRow($affid, $progid="", $progname="")
{
	global $objMysql;
	$row = array();
	if(!empty($progid))
	{
		$sql = "select Site,MerchantId,MerchantName from `merchant_program` WHERE AffId='{$affid}' AND AffMerchantId='".addslashes($progid)."' LIMIT 1";
		$row=$objMysql->getFirstRow($sql);
	}else
	{
		$sql = "select ID from `program` WHERE AffId='{$affid}' AND Name='".addslashes($progname)."' LIMIT 1";
		$program_id=$objMysql->getFirstRowColumn($sql);
		if(!empty($program_id))
		{
			$sql = "select Site,MerchantId,MerchantName from `merchant_program` WHERE ProgramId='{$program_id}' LIMIT 1";
			$row=$objMysql->getFirstRow($sql);
		}
	}
	return $row;
}

function getListByCondition($condition = array(), $fields = '*')
{
	global $objMysql;
	$data = array();
	if (empty($condition)) return $data;
	$sql = "select {$fields} from `affiliate_invalid_link` ";
	if (!empty($condition['sql'])) $sql .= "where 1=1 {$condition['sql']} ";
	if (!empty($condition['order'])) $sql .= "order by {$condition['order']} ";
	if (!empty($condition['limit'])) $sql .= "limit {$condition['limit']} ";
	$data = $objMysql->getRows($sql);
	return $data;
}

function updateData($row, $id) 
{
	global $objMysql;
	if (empty($row))
		return false;
	$sqlQuery = "update `affiliate_invalid_link` set ";
	$storeRelFields = '';
	$storeRelValues = '';
	$commentFields = '';
	$commentValues = '';
	$commentRelFields = '';
	$commentRelValues = '';
	foreach ($row as $k => $v) 
	{
		$sqlQuery .= "`" . $k . "` = '" . addslashes($v) . "', ";
	}
	$sqlQuery = preg_replace("|, $|i", ' ', $sqlQuery);
	$sqlQuery .= " WHERE `ID`={$id}";
	if (!$res = $objMysql->query($sqlQuery))
	{
		return false;
	}
	return true;
}
