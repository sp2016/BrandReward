<?php
	include_once(dirname(dirname(__FILE__))."/etc/const.php");
	include_once(INCLUDE_ROOT . "func/js.func.php");
	include_once(INCLUDE_ROOT . "func/check.func.php");
	include_once(INCLUDE_ROOT . "func/stats.func.php");
    mysql_query("SET NAMES 'latin1'");
	
	$objMysqlTask = new Mysql();
	$oRequest = new Request();	
	
	$programModel = new Program();
	$objStore = new Store();
	
	$user = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : "";
	if(!$user && substr($_SERVER["REMOTE_ADDR"],0,8) == "192.168.") $user = "couponsn";
	

	$action = $oRequest->getStrNoSlashes("action");
	switch ($action) {	
		case "reassign":			
			$id = intval($_POST["id"]);			
			$remark = trim($_POST["remark"]);
			
			if($id){
				$sql = "select editor, remark from t_check_p_d_links where id = {$id}";
				$tmp_arr = $objMysqlTask->getFirstRow($sql);
				$old_remark = $tmp_arr["remark"];
				
				if(strlen($remark)){
					$remark = $user.":".$remark;
				}
				
				if(strlen($old_remark)){
					$remark = $old_remark."\r\n".$remark;
				}
				
				$sql = "UPDATE t_check_p_d_links SET Editor = 'elsahou', remark = '".addslashes($remark)."' WHERE id = {$id}";
				$objMysqlTask->query($sql);
			}
			echo "success";
			break;		
		case "assign":			
			$id = intval($_POST["id"]);			
			$remark = trim($_POST["remark"]);
			
			if($id){
				$sql = "UPDATE t_check_p_d_links SET Editor = 'elsahou', remark = '".addslashes($user.":".$remark)."' WHERE id = {$id}";
				$objMysqlTask->query($sql);
			}	
			echo "success";
			break;	
		case "ignored":			
			$id = intval($_POST["id"]);
            $remark = addslashes(trim($_POST["remark"]));
			
			if($id){
				$sql = "UPDATE t_check_p_d_links SET Status = 'Ignored',Remark = '{$remark}' WHERE id = {$id}";
				$objMysqlTask->query($sql);
			}	
			echo "success";
			break;
		case "done":			
			$id = intval($_POST["id"]);
            $remark = addslashes(trim($_POST["remark"]));
			if($id){
				$sql = "UPDATE t_check_p_d_links SET Status = 'Done',Remark = '{$remark}' WHERE id = {$id}";
				$objMysqlTask->query($sql);
			}	
			echo "success";
			break;	
		
		case "list":
		default:			
			$objTpl = new TemplateSmarty();			
			
			$where_str = "";
			
			$editor = trim($oRequest->getStrNoSlashes("editor"));			
			if($editor){
				$where_str .= " and a.editor = '$editor' ";
			}
			
			$type = intval($oRequest->getStrNoSlashes("type"));
			if($type){				
				if($type == 1) $where_str .= "  and a.errortype in (1, 11)"; //, 11
				else $where_str .= " and a.errortype = '$type' ";
			}else{
				//$where_str .= " and (a.errortype in (2,3,4) or (a.errortype = 1 and a.ErrorValue LIKE '404%' ))";
				$where_str .= " and a.errortype in (1,2,3,4, 11)"; //,11
			}
			
			$important = intval($oRequest->getStrNoSlashes("important"));			
			if($type == 1 && $important == 1){				
				$where_str .= " and a.rank > 0 ";				
			}
			
			$status = trim($oRequest->getStrNoSlashes("status"));
			if(empty($status)) $status = "Assigned";
			if($status){
				$where_str .= " and a.status = '$status' ";
			}
			
			
			$aff_id = intval($oRequest->getStrNoSlashes("aff_id"));
			$aff_name = trim($oRequest->getStrNoSlashes("aff_name"));
			$program_name = trim($oRequest->getStrNoSlashes("program_name"));
			$program_id = trim($oRequest->getStrNoSlashes("program_id"));
			
			if(empty($aff_name)) $aff_id = 0;
			if(empty($program_name)) $program_id = '';
			
			$objTpl->assign("aff_id", $aff_id);
			$objTpl->assign("aff_name", $aff_name);
			$objTpl->assign("program_name", $program_name);
			$objTpl->assign("program_id", $program_id);
			
			
			if($aff_id){
				$where_str .= " and c.affid = '$aff_id' ";
			}
			
			if($program_id){
				$where_str .= " and a.programid = '".intval($program_id)."' ";
			}
			
			
			$sql = "SELECT count(*) FROM `t_check_p_d_links` a inner join program_intell c on a.programid = c.programid WHERE 1=1 ". $where_str;
			$count = $objMysqlTask->getFirstRowColumn($sql);
			
			$perpage = intval($oRequest->getStrNoSlashes("onepage"));
			if(empty($perpage)){
				$perpage = @$_COOKIE['onepage'];
			}
			if ($perpage < 1 || $perpage > 100 || empty($perpage)) $perpage = 100;
			setcookie("onepage", $perpage, time()+60*60*24*30);		
			$_COOKIE['onepage'] = $perpage;

			$objTpl->assign('perpage', $perpage);
			
			$PageNo = intval($oRequest->getStrNoSlashes("page"));
			if($PageNo < 1) $PageNo = 1;
			$limit_from = ($PageNo - 1) * $perpage;
			
			include_once(INCLUDE_ROOT . "lib/Class.Page.php");
			$objPB = new OPB($count, $perpage);
			$objPB->onepage = $perpage;
			$pagebar = $objPB->whole_bar(3, 8);
			$pagebar1 = $objPB->whole_bar(4, 8);
						
			$sql = "SELECT a.id, a.programid, a.domainid, a.`status`, a.`addtime`, a.`lastupdatetime`, a.editor, b.AffDefaultUrl, b.DeepUrlTpl, a.remark, a.errortype, a.errorvalue FROM `t_check_p_d_links` a left join r_domain_program b on a.programid = b.pid and a.domainid = b.did inner join program_intell c on a.programid = c.programid 
					WHERE 1=1 ";
			$sql .= $where_str. " ORDER BY a.rank ASC LIMIT {$limit_from}, $perpage";
			$data = $objMysqlTask->getRows($sql);
			
			
			$p_info = $d_info = $p_arr = $d_arr = $p_domain = array();			
			foreach($data as $v){
				$p_arr[$v["programid"]] = $v["programid"];
				$d_arr[$v["domainid"]] = $v["domainid"];
			}
			
			if(count($p_arr)){
				$sql = "select a.id, a.name, a.idinaff, a.affid, b.name as aff_name, a.homepage from program a inner join wf_aff b on a.affid = b.id where a.id in (".implode(",", $p_arr).")";				
				$p_info = $objMysqlTask->getRows($sql, "id");
				
				$sql = "select a.domain, b.pid from domain a inner join r_domain_program b on a.id = b.did where b.pid in (".implode(",", $p_arr).") and b.status = 'active'";				
				$tmp_arr = $objMysqlTask->getRows($sql);
				foreach($tmp_arr as $v){
					$p_domain[$v["pid"]][] = $v["domain"];
				}
			}
			if(count($d_arr)){
				$sql = "select a.id, a.domain from domain a where a.id in (".implode(",", $d_arr).")";				
				$d_info = $objMysqlTask->getRows($sql, "id");
			}

			$editorArr = array("" => "All", "miahuang" => "miahuang", "tinading" => "tinading", "kaylayan" => "kaylayan", "harineyou" => "harineyou", "elsahou" => "elsahou");
			$objTpl->assign("editorArr", $editorArr);
			$objTpl->assign("editor", $editor);
			
			
			$typeArr = array("" => "All", "1" => "Invalid Links", "2" => "New Program Links Preview", "3" => "No Link Program Check", "4" => "Program Homepage Check");			
			$objTpl->assign("typeArr", $typeArr);
			$objTpl->assign("type", $type);
			
			$statusArr = array("Assigned" => "New", "Done" => "Done", "Ignored" => "Ignored");			
			$objTpl->assign("statusArr", $statusArr);
			$objTpl->assign("status", $status);
			
			$objTpl->assign("important", $important);
			
			$objTpl->assign("p_info", $p_info);
			$objTpl->assign("d_info", $d_info);
			$objTpl->assign("p_domain", $p_domain);
			
			$objTpl->assign("pagebar", $pagebar);
			$objTpl->assign("pagebar1", $pagebar1);
			$objTpl->assign('data', $data);
			
			$objTpl->display("check_program_domain_links.tpl");
			break;
	}
	
	
	
?>