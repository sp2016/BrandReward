<?php
	include_once(dirname(dirname(__FILE__))."/etc/const.php");
	include_once(INCLUDE_ROOT . "func/js.func.php");
	include_once(INCLUDE_ROOT . "func/check.func.php");
	include_once(INCLUDE_ROOT . "func/stats.func.php");
	include_once(dirname(__FILE__) . "/program_data_share.php");

	
	$objMysqlTask = new Mysql(TASK_DB_NAME, TASK_DB_HOST, TASK_DB_USER, TASK_DB_PASS);
	$oRequest = new Request();
		
	$objMysqlPending = new Mysql(PENDING_DB_NAME, PENDING_DB_HOST, PENDING_DB_USER, PENDING_DB_PASS);
	
	$programModel = new Program();
	
	$affObj = new Affiliate();
	$action = $oRequest->getStrNoSlashes("action");
	switch ($action) {		
		
		case "list":
		default:
			$perpage = intval($resObj->getStrNoSlashes("onepage"));
			if(empty($perpage)){
				$perpage = $_COOKIE['onepage'];
			}
			if ($perpage < 1 || $perpage > 100 || empty($perpage)) $perpage = 100;
			setcookie("onepage", $perpage, time()+60*60*24*30);		
			$_COOKIE['onepage'] = $perpage;
						
			$PageNo = intval($oRequest->getStrNoSlashes("page"));
			if($PageNo < 1) $PageNo = 1;
			$limit_from = ($PageNo - 1) * $perpage;
			
			
			$AffId = intval($oRequest->getStrNoSlashes("affiliatetype"));			
			if(empty($AffId)){
				alert("Aff didn't existed!");
				break;
				exit;
			}
			
			/*$LinkPromoType = trim($oRequest->getStrNoSlashes("type"));
			if($LinkPromoType != "product"){
				$LinkPromoType = "";
			}else{
				$LinkPromoType = " and PromoType = 'product'"; 
			}
			
			$name = trim($oRequest->getStrNoSlashes("name"));
			$pid = intval($oRequest->getStrNoSlashes("pid"));
			if($name){
				//$where_str = " and AffiliateMerchantName = '".addslashes($name)."'";
				$tmp_arr = array();
				$sql = "select name,idinaff from program where name = '".addslashes($name)."' AND affid = $AffId limit 1";
				$tmp_arr = $objMysqlTask->getFirstRow($sql);
				
				$idinaff = trim($tmp_arr["idinaff"]);
				$name = $tmp_arr["name"];
				//$where_str = " and AffiliateMerchantID = '".addslashes($idinaff)."'";
				$where_str = " and (AffiliateMerchantName = '".addslashes($name)."' OR AffiliateMerchantID = '".addslashes($idinaff)."')";
			}elseif($pid){
				$tmp_arr = array();
				$sql = "select name,idinaff from program where id = $pid limit 1";
				$tmp_arr = $objMysqlTask->getFirstRow($sql);
				
				$idinaff = trim($tmp_arr["idinaff"]);
				$name = $tmp_arr["name"];
				//$where_str = " and AffiliateMerchantID = '".addslashes($idinaff)."'";
				$where_str = " and (AffiliateMerchantName = '".addslashes($name)."' OR AffiliateMerchantID = '".addslashes($idinaff)."')";
			}
			
			//$sql = "select * from coupon_queue WHERE SourceType = 'AFFILIATE' and AffiliateID = $AffId $LinkPromoType and (EndDate >= '".date("Y-m-d H:i:s")."' OR EndDate = '0000-00-00 00:00:00') AND !ISNULL(HtmlCode) AND HtmlCode <> '' $where_str ORDER BY AddTime DESC LIMIT {$limit_from}, $perpage";
			$sql = "select * from coupon_queue WHERE SourceType = 'AFFILIATE' and AffiliateID = $AffId $LinkPromoType and (EndDate >= '".date("Y-m-d H:i:s")."' OR EndDate = '0000-00-00 00:00:00') AND !ISNULL(HtmlCode) AND HtmlCode <> '' $where_str UNION ALL select * from coupon_queue_archive WHERE SourceType = 'AFFILIATE' and AffiliateID = $AffId $LinkPromoType and (EndDate >= '".date("Y-m-d H:i:s")."' OR EndDate = '0000-00-00 00:00:00') AND !ISNULL(HtmlCode) AND HtmlCode <> '' $where_str ORDER BY AddTime DESC LIMIT {$limit_from}, $perpage";*/
			
			$sql = "SHOW TABLES LIKE 'affiliate_links_{$AffId}'";
			$tmp_arr = array();
			$tmp_arr = $objMysqlPending->getRows($sql);
			if(!count($tmp_arr)){
				alert("Aff DB didn't existed!");
				break;
				exit;
			}
			
			$where_str = '';
			
			/*$DataSource_arr = array();
			$sql = "SELECT ID FROM coupon_datasource WHERE TYPE = 'AFFILIATE' AND AffiliateId = {$AffId}";
			$DataSource_arr = $objMysqlTask->getRows($sql, "ID");			
			if(count($DataSource_arr)){
				$where_str .= " and DataSource in (".implode(",", array_keys($DataSource_arr)).")";
			}*/
			
			$LinkPromoType = trim($oRequest->getStrNoSlashes("type"));
			if($LinkPromoType == "product"){				
				$where_str .= " and LinkPromoType = 'product'";
			}
			
			$name = trim($oRequest->getStrNoSlashes("name"));
			$pid = intval($oRequest->getStrNoSlashes("pid"));
			if($name){				
				$tmp_arr = array();
				$sql = "select idinaff from program where name = '".addslashes($name)."' AND affid = $AffId limit 1";
				$tmp_arr = $objMysqlTask->getFirstRow($sql);
				if(isset($tmp_arr["idinaff"])){
					$where_str .= " and AffMerchantId = '".addslashes($tmp_arr["idinaff"])."'";
				}
			}elseif($pid){
				$tmp_arr = array();
				$sql = "select idinaff from program where id = $pid limit 1";
				$tmp_arr = $objMysqlTask->getFirstRow($sql);
				if(isset($tmp_arr["idinaff"])){
					$where_str .= " and AffMerchantId = '".addslashes($tmp_arr["idinaff"])."'";
				}
			}
			
			$sql = "select count(*) as cnt from affiliate_links_{$AffId} WHERE !ISNULL(LinkHtmlCode) AND LinkHtmlCode <> '' $where_str ";
			$count = $objMysqlPending->getFirstRowColumn($sql);
			
			$sql = "select AffMerchantId, LinkImageUrl, LinkName, LinkDesc, LinkHtmlCode, LinkAffUrl, LinkAddTime as AddTime, LinkEndDate from affiliate_links_{$AffId} WHERE !ISNULL(LinkHtmlCode) AND LinkHtmlCode <> '' 
					$where_str ORDER BY LinkAddTime DESC LIMIT {$limit_from}, $perpage";
			
			$links_arr = array();
			//$links_arr = $objMysqlTask->getRows($sql, 'ID', true);
			$links_arr = $objMysqlPending->getRows($sql);
			
			//$count = $objMysqlPending->FOUND_ROWS;			
			include_once(INCLUDE_ROOT . "lib/Class.Page.php");
			$objPB = new OPB($count, $perpage);
			$objPB->onepage = $perpage;
			$pagebar = $objPB->whole_bar(3, 8);
			$pagebar1 = $objPB->whole_bar(4, 8);
			
			$prgm_id_arr = array();
			$date_now = date("Y-m-d H:i:s");
			foreach($links_arr as $k => $v){
				$prgm_id_arr[$v["AffMerchantId"]] = 1;
				if($v["LinkEndDate"] != '0000-00-00 00:00:00' && $v["LinkEndDate"] < $date_now){
					$links_arr[$k]["isexpire"] = 1;
				}
			}
			
			$sql = "select id,name,idinaff from program where affid = $AffId and idinaff in ('".implode("','", array_keys($prgm_id_arr))."')";
			$prgm_arr = array();
			$prgm_arr = $objMysqlTask->getRows($sql, "idinaff");
			//print_r($prgm_arr);
			
			$sql = "select name from wf_aff where id = $AffId limit 1";
			$aff_arr = array();
			$aff_arr = $objMysqlTask->getFirstRow($sql);
			$affiliatename = $aff_arr["name"];
			
			$objTpl = new TemplateSmarty();
			
			$objTpl->assign('perpage', $perpage);
			$objTpl->assign("pagebar", $pagebar);
			$objTpl->assign("pagebar1", $pagebar1);
			
			$objTpl->assign('name', $name);
			$objTpl->assign('affid', $AffId);
			$objTpl->assign('type', $LinkPromoType);
			$objTpl->assign('affiliatename', $affiliatename);
			$objTpl->assign('links_arr', $links_arr);
			$objTpl->assign('prgm_arr', $prgm_arr);
			
			$objTpl->display("program_links_list.tpl");
			break;
	}
	
	
	
?>