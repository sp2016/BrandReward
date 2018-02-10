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
//	$_SERVER['PHP_AUTH_USER'] ="elsahou"
	$user = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : "";
	if(!$user && substr($_SERVER["REMOTE_ADDR"],0,8) == "192.168.") $user = "couponsn";
	
    //获取状态筛选信息
	$action = $oRequest->getStrNoSlashes("action");
	switch ($action) {
        //暂未使用
		case "reassign":			
			$id = intval($_POST["id"]);			
			$remark = trim($_POST["remark"]);
			
			if($id){
				$sql = "select editor, remark from t_domain_issue where id = {$id}";
				$tmp_arr = $objMysqlTask->getFirstRow($sql);
				$old_remark = $tmp_arr["remark"];
				
				if(strlen($remark)){
					$remark = $user.":".$remark;
				}
				
				if(strlen($old_remark)){
					$remark = $old_remark."\r\n".$remark;
				}
				
				$sql = "UPDATE t_domain_issue SET Editor = 'elsahou', remark = '".addslashes($remark)."' WHERE id = {$id}";
				$objMysqlTask->query($sql);
			}
			echo "success";
			break;

        //assigntoElsa动作
		case "assign":			
			$id = intval($_POST["id"]);			
			$remark = trim($_POST["remark"]);

			if($id){
				$sql = "UPDATE t_domain_issue SET Editor = 'elsahou', remark = '".addslashes($user.":".$remark)."' WHERE id = {$id}";
				$objMysqlTask->query($sql);
			}	
			echo "success";
			break;

        //ignored动作
		case "ignored":			
			$id = intval($_POST["id"]);			
			$remark = addslashes(trim($_POST["remark"]));
			
			if($id){
				$sql = "UPDATE t_domain_issue SET Status = 'Ignored',Remark = '{$remark}' WHERE id = {$id}";
				$objMysqlTask->query($sql);
			}
			echo "success";
			break;	
		case "done":			
			$id = intval($_POST["id"]);			
			$remark = addslashes(trim($_POST["remark"]));
			
			if($id){
				$sql = "UPDATE t_domain_issue SET Status = 'Done',Remark = '{$remark}' WHERE id = {$id}";
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
				$where_str .= " and t.editor = '$editor' ";
			}

			$status = trim($oRequest->getStrNoSlashes("status"));
			if(empty($status)) $status = "Assigned";
			if($status){
				$where_str .= " and t.status = '$status' ";
			}

            $domain = trim($oRequest->getStrNoSlashes("domain"));
            if($domain){
                $where_str.= "  AND d.`DomainName` LIKE '%{$domain}%'";
            }

            //1=1 拼接，抑制错误
			$sql = "SELECT count(*) FROM t_domain_issue t ,domain d WHERE 1=1 AND t.`DomainId`=d.`ID`". $where_str;


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
						
                $sql = "SELECT t.id,t.editor, t.domainid,t.remark, d.domain, d.rank, t.status, t.click FROM domain d inner join `t_domain_issue` t /*FORCE INDEX(idx_cli)*/ on d.id = t.domainid
					WHERE 1=1 ";



			$sql .= $where_str. " ORDER BY t.click DESC LIMIT {$limit_from}, $perpage";
        $data = $objMysqlTask->getRows($sql);
			$d_stats = $d_p_info = array();
			if(count($data)){
				foreach($data as $v){					
					$d_stats[$v["domainid"]] = $v["domainid"];
				}
                //获取issue列表的domain program详细信息
				$sql = "select a.id as pid, a.name, a.idinaff, a.affid, b.name as aff_name, a.statusinaff, a.partnership, r.did from program a inner join wf_aff b on a.affid = b.id
						inner join r_domain_program r on a.id = r.pid where r.status = 'active' and r.did in (".implode(",", $d_stats).")";				
				$tmp_arr = $objMysqlTask->getRows($sql);
				//以domainId为key保存programList
				foreach($tmp_arr as $v){
					$d_p_info[$v["did"]][$v["pid"]] = $v;
				}

                //获取点击信息
				$sql = "select domainid, sum(revenue1M) as rev1m, sum(clicks1M) as cli1m, sum(revenue7D) as rev7d, sum(clicks7D) as cli7d from domain_stats where domainid in (".implode(",", $d_stats).") group by domainid";
				$tmp_arr = $objMysqlTask->getRows($sql);
				$d_stats = array();
				foreach($tmp_arr as $v){
					$d_stats[$v["domainid"]] = $v;
				}		
				//print_r($d_p_info);		
			}
						
			$editorArr = array("" => "All", "miahuang" => "miahuang", "tinading" => "tinading", "kaylayan" => "kaylayan", "harineyou" => "harineyou", "elsahou" => "elsahou");			
			$objTpl->assign("editorArr", $editorArr);
			$objTpl->assign("editor", $editor);
			
			
			$typeArr = array("" => "All", "1" => "Invalid Links", "2" => "New Program Links Preview", "3" => "No Link Program Check", "4" => "Program Homepage Check");			
			$objTpl->assign("typeArr", $typeArr);
			//$objTpl->assign("type", $type);
			
			$statusArr = array("Assigned" => "New", "Done" => "Done", "Ignored" => "Ignored");			
			$objTpl->assign("statusArr", $statusArr);
			$objTpl->assign("status", $status);

			/*$objTpl->assign("important", $important);*/
			//$objTpl->assign("p_info", $p_info);
			$objTpl->assign("d_stats", $d_stats);
			$objTpl->assign("d_p_info", $d_p_info);
			$objTpl->assign("domain", $domain);
			$objTpl->assign("pagebar", $pagebar);
			$objTpl->assign("pagebar1", $pagebar1);
			$objTpl->assign('data', $data);
			
			$objTpl->display("top_noaff_domain.tpl");
			break;
	}
	
	
	
?>