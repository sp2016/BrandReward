<?php
	include_once(dirname(__FILE__) . "/program_data_share.php");
	include_once(INCLUDE_ROOT . "func/js.func.php");
	include_once(INCLUDE_ROOT . "func/check.func.php");
	include_once(INCLUDE_ROOT . "func/stats.func.php");
	$objMysqlTask = new Mysql();
	$oRequest = new Request();	
	
	$programModel = new Program();
	$objStore = new Store();
	
	$user = "";

	$affObj = new Affiliate();
	$action = $oRequest->getStrNoSlashes("action");
	switch ($action) {		
		case "saveLine":			
			$did = intval($oRequest->getStrNoSlashes("storeid"));			
			$programid = intval($oRequest->getStrNoSlashes("programid"));
			$DeepUrlTemplate = trim(addslashes($oRequest->getStrNoSlashes("deepurl")));
			$AffiliateDefaultUrl = trim(addslashes($oRequest->getStrNoSlashes("defaultaffurl")));
			$Status = trim(addslashes($oRequest->getStrNoSlashes("status")));	
			$Uri = trim(addslashes($oRequest->getStrNoSlashes("uri")));
			$IsFake = trim(addslashes($oRequest->getStrNoSlashes("isfake")));			
			
			$resDeepUrl = $objStore->checkCustomDeepUrl($DeepUrlTemplate);          //checkCustomDeepUrl函数用于判断Deep-URL Template是否为空
			if($resDeepUrl === false){
				echo "deepurl error";
				exit;
			}
			//edit P-M relationship
			$sql = "SELECT AffId, IdInAff FROM program WHERE ID = {$programid}";
			$prgm_arr = array();
			$prgm_arr = $objMysqlTask->getFirstRow($sql);
			
			if($Status == "Active"){
				if(!checkUrl($AffiliateDefaultUrl)){
					echo("Affiliate Default Aff URL error. ");
					exit;
				}
				if(!$affObj->checkAffurlValid($prgm_arr["AffId"], $AffiliateDefaultUrl)){
					echo ("Invalid affurldefault({$AffiliateDefaultUrl}). Please make sure using affiliate url.");
					exit;
				}
				if(!$affObj->checkAffurlValid($prgm_arr["AffId"], $DeepUrlTemplate) && $DeepUrlTemplate != ""){
					echo("Invalid deepurltemplate({$DeepUrlTemplate}). Please make sure using affiliate url.");
					exit;
				}				
			}
			
			if($did && $programid){
				/*$sql = "UPDATE r_domain_program SET Status = '{$Status}', Uri = '{$Uri}', IsFake = '{$IsFake}', DeepUrlTpl = '{$DeepUrlTemplate}', AffDefaultUrl = '{$AffiliateDefaultUrl}', LastUpdateTime = '" .date("Y-m-d H:i:s"). "' 
						, IsHandle = '1' WHERE did = {$did} and pid = {$programid}";*/
				$sql = "UPDATE r_domain_program SET Status = '{$Status}', IsFake = '{$IsFake}', DeepUrlTpl = '{$DeepUrlTemplate}', AffDefaultUrl = '{$AffiliateDefaultUrl}', LastUpdateTime = '" .date("Y-m-d H:i:s"). "' 
						, IsHandle = '1' WHERE did = {$did} and pid = {$programid}";
				$objMysqlTask->query($sql);
				
				/*$sql = "UPDATE r_domain_program_copy SET Status = '{$Status}', Uri = '{$Uri}', IsFake = '{$IsFake}', DeepUrlTpl = '{$DeepUrlTemplate}', AffDefaultUrl = '{$AffiliateDefaultUrl}', LastUpdateTime = '" .date("Y-m-d H:i:s"). "' 
						, IsHandle = '1' WHERE did = {$did} and pid = {$programid}";
				$objMysqlTask->query($sql);*/
			}
			
					
			echo "success";
			break;	
		
		case "list":
		default:
			$ProgramId = intval($oRequest->getStrNoSlashes("ProgramId"));
			$DomainId = intval($oRequest->getStrNoSlashes("DomainId"));
			if(empty($ProgramId)){
				alert("Program didn't existed!");
				break;
				exit;
			}
			$objTpl = new TemplateSmarty();
			
			global $g_SiteUrl;
			
			$objTpl->assign('g_SiteUrl', $g_SiteUrl);			
			
			//get program info
			$sql = "SELECT p.Name as pname, p.AffId, p.IdInAff, p.Homepage, a.Name as aname, i.SupportDeepUrl, p.AffDefaultUrl, p.Homepage, p.StatusInAff, p.Partnership, i.IsActive, i.ShippingCountry FROM program as p inner join program_intell i on p.id = i.programid inner join wf_aff as a on (p.AffId = a.ID) WHERE p.ID = {$ProgramId}";
			$prgm_info = array();
			$prgm_info = $objMysqlTask->getFirstRow($sql);			
			
			//ID,ProgramId,StoreId,AffiliateDefaultUrl,DeepUrlTemplate,Order,Status,LastUpdateTime		
			$sql = "SELECT a.did, a.pid, a.`Status`, a.AffDefaultUrl, a.DeepUrlTpl, a.IsFake, b.domain, a.Uri FROM r_domain_program a LEFT JOIN domain b ON (a.did = b.id) WHERE b.domain not like '%/%' and a.pid = {$ProgramId}";
			//$sql .= " UNION SELECT a.did, a.pid, a.`Status`, a.AffDefaultUrl, a.DeepUrlTpl, a.IsFake, b.domain, a.Uri FROM r_domain_program_copy a LEFT JOIN domain b ON (a.did = b.id) WHERE b.domain not like '%/%' and a.pid = {$ProgramId}";
			//echo $sql;
			$rel_arr = array();
			$rel_arr = $objMysqlTask->getRows($sql, "did");
			
			$mer_store = array();
			$site_mer = array();
			$storeid_arr = array();
			
			
			$objTask = new Task();
			$merchantlist = array();
						
			
			
			//get program deep url template
			$sql = "select DeepUrlTpl,Remark,SupportDeepUrlTpl,DefaultUrl from program_aff_default_url where affid = {$prgm_info['AffId']} LIMIT 1";
			
			$deepurltpl = array();
			$deepurltpl = $objMysqlTask->getFirstRow($sql);
				
			/*$source_sel = array("P-S partnership online task", "Not-Crawling Affiliate Network", "Top non-affiliate Store", "Applied New Program Manually", "New Store Request", "M-S Review", "Other");
			$objTpl->assign('source_sel', $source_sel);
			
			$edit_ps_source = "";
			if($prgm_info["Partnership"] == "Active" && $prgm_info["StatusInAff"] == "Active"){
				$sql = "select id, Status from bd_work_log where programid = $ProgramId and Type = 'DeclinedProgramHandle' and status <> 'Invalid' order by lastupdatetime desc limit 1";
				$tmp_arr = $objMysqlTask->getFirstRow($sql);
				if($tmp_arr["Status"] == "Positive" || $tmp_arr["Status"] == "In-Progress"){
					$edit_ps_source = "Applied New Program Manually";
				}
			}			
			$objTpl->assign('edit_ps_source', $edit_ps_source);*/
			
			//$objTpl->assign('prgmint_info', $prgmint_info);
			$objTpl->assign('DomainId', $DomainId);
			$objTpl->assign('prgm_info', $prgm_info);
			$objTpl->assign('ProgramId', $ProgramId);
			$objTpl->assign('rel_arr', $rel_arr);
			$objTpl->assign('deepurltpl', $deepurltpl);
			
			$objTpl->display("program_store_edit.tpl");
			break;
	}
	
	
	
?>