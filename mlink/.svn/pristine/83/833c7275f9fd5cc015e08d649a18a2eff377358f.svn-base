<?php
	include_once(dirname(__FILE__) . "/program_data_share.php");
	
	//define("SYS_FUNC_ID", 441);
	//include_once(INCLUDE_ROOT . "func/remote.auth.func.php");
	//include_once(INCLUDE_ROOT . "lib/Class.TaskEmail.php");
	
	$objMysql = array();
	$oTaskEmail = new TaskEmail();
	
	$objMysqlTask = new Mysql(TASK_DB_NAME, TASK_DB_HOST, TASK_DB_USER, TASK_DB_PASS);
	$objStore = new Store();
	
	$isRemind = false;
	/*$programIds4RemindArr = array();
	$programIds4Remind = $programModel->getProgramId4CurrentDate();
	if (!empty($programIds4Remind)) {
		$programIds4RemindArr = explode(',', $programIds4Remind);
		$isRemind = true;
	}
	$tpl->assign('isRemind', $isRemind);*/
	
	$allProgramTemplateUrl = $programModel->getProgramUrlTemplate();
	
	
	$perpage = intval($resObj->getStrNoSlashes("onepage"));
	if(empty($perpage)){
		$perpage = $_COOKIE['onepage'];
	}
	if ($perpage < 1 || $perpage > 100 || empty($perpage)) $perpage = 100;
	setcookie("onepage", $perpage, time()+60*60*24*30);		
	$_COOKIE['onepage'] = $perpage;
			
	$tpl->assign('perpage', $perpage);
	
	$PageNo = intval($resObj->getStrNoSlashes("page"));
	if($PageNo < 1) $PageNo = 1;
	$limit_from = ($PageNo - 1) * $perpage;
	
	$condition = array();
	
	$condition = array('sql' => '', 'order' => '', 'limit' => '');
	
	$affiliatetype = trim($resObj->getStrNoSlashes("affiliatetype"));
	$country = trim($resObj->getStrNoSlashes("country"));
	
	$affiliatename =  trim($resObj->getStrNoSlashes("affiliatename"));
	//if (empty($affiliatename)) $affiliatetype = "";
	
	$merchantname = trim($resObj->getStrNoSlashes("merchantname"));
	$merchantid = trim($resObj->getStrNoSlashes("merchantid"));
	$site = trim($resObj->getStrNoSlashes("site"));
	$hasPM = trim($resObj->getStrNoSlashes("hasPM"));
	$hasCoop = trim($resObj->getStrNoSlashes("hasCoop"));
	$hasPS = trim($resObj->getStrNoSlashes("hasPS"));
	
	$partnership = trim($resObj->getStrNoSlashes("partnership"));
	if (empty($partnership)) $status = "Active";
	
	$wedeclined = trim($resObj->getStrNoSlashes("wedeclined"));
	
	$statusinaff = trim($resObj->getStrNoSlashes("statusinaff"));
	if (empty($statusinaff)) $status = "Active";
	
	$name = trim($resObj->getStrNoSlashes("name"));
	
	$createdatestart = trim($resObj->getStrNoSlashes("createdatestart"));
	$createdatend = trim($resObj->getStrNoSlashes("createdatend"));
	
	$addtimestart = trim($resObj->getStrNoSlashes("addtimestart"));
	$addtimeend = trim($resObj->getStrNoSlashes("addtimeend"));
	
	$expireremind = trim($resObj->getStrNoSlashes("expireremind"));
	
	$order = trim($resObj->getStrNoSlashes("order"));
	
	$down = trim($resObj->getStrNoSlashes("down"));
	$group = trim($resObj->getStrNoSlashes("group"));
	
	$mobilefriendly = trim($resObj->getStrNoSlashes("mobilefriendly"));
	
	if (empty($order)) $order = " p.RankInAff DESC ";
	
	if (!empty($affiliatetype) && $affiliatetype != 'All') {
		$condition['sql'] = " and p.AffId='{$affiliatetype}' ";
	}
	
	if (!empty($country) && $country != 'All') {
		$condition['sql'] .= " and find_in_set('{$country}', p.TargetCountryInt) ";
	}
	
	if (!empty($partnership) && $partnership != 'All') {
		$condition['sql'] .= " and p.Partnership='{$partnership}' ";
	}
	
	if (!empty($wedeclined) && $wedeclined != 'All') {
		$condition['sql'] .= " and p.WeDeclined='{$wedeclined}' ";
	}
	
	if (!empty($statusinaff) && $statusinaff != 'All') {
		$condition['sql'] .= " and p.StatusInAff='{$statusinaff}' ";
	}
	
	if (!empty($name)) {
		$condition['sql'] .= " and (p.Name like '%" . addslashes($name) . "%' or p.IdInAff like '%" . addslashes($name) . "%') ";
	}
	
	if (!empty($createdatestart)) {
		$condition['sql'] .= " and p.CreateDate>='{$createdatestart}' ";
	}
	
	if (!empty($createdatend)) {
		$condition['sql'] .= " and p.CreateDate<='{$createdatend}' ";
	}
	
	if (!empty($addtimestart)) {
		$condition['sql'] .= " and p.AddTime>='{$addtimestart}' ";
	}
	
	if (!empty($addtimeend)) {
		$condition['sql'] .= " and p.AddTime<='{$addtimeend}' ";
	}
	
	if ($isRemind && ($expireremind == 1)) {
		$condition['sql'] .= " and p.ID in (" . $programIds4Remind . ") ";
	}
	
	if (!empty($hasCoop) && $hasCoop != 'All') {
		$condition['sql'] .= " and p.CooperateWithCouponSite = '" . $hasCoop . "' ";
	}
	
	if (!empty($group) && $group != 'All') {
		$condition['sql'] .= " and i.GroupInc = '" . $group . "' ";
	}
	
	if (!empty($mobilefriendly) && $mobilefriendly != 'All') {
		$condition['sql'] .= " and p.mobilefriendly = '" . addslashes($mobilefriendly) . "' ";
	}
	
	
	if($hasPM == 2){
		$condition['sql'] .= "AND p.ID NOT IN (SELECT ProgramId FROM merchant_program)";
	}elseif($site || $merchantname){
		$tmp_condition = "";
		if($site)$tmp_condition = "Site = '". addslashes($site) ."'";
		if($site && $merchantname)$tmp_condition .= " AND ";
		if($merchantname)$tmp_condition .= "MerchantName = '". addslashes($merchantname) ."'";
		$condition['sql'] .= "AND p.ID IN (SELECT ProgramId FROM merchant_program WHERE $tmp_condition)";
	}elseif($hasPM == 1){
		$condition['sql'] .= "AND p.ID IN (SELECT ProgramId FROM merchant_program)";
	}
	
	//P-S Relationship（All、Have(Default)、Have(All)、Have(Active)、Have(Inactive)、No）
	/*
	*	i.	All：缺省值，代表这个条件不起作用
	*	ii.	Have(All)：代表有P-S关系记录
	*	iii.	Have(Default)：代表有P-S关系记录且有某个P-S关系是对应S的缺省值（DefaultUrl、Deep-Url Template两个都要考虑，是或的关系）
	*	iv.	Have(Active)：代表同一个Program有Active的P-S关系记录
	*	v.	Have(Inactive)：代表同一个Program全是 Inactive 的P-S关系记录
	*	vi.	No：代表没有P-S关系记录
	*/
	if(empty($hasPS)){
		$hasPS == "All";
	}
	if($hasPS == "h_all"){
		$condition['sql'] .= "AND p.ID IN (SELECT ProgramId From program_store_relationship)";
	}elseif($hasPS == "h_default"){
		$condition['sql'] .= "AND p.ID IN (SELECT ProgramId from program_store_relationship r inner join store s on(r.storeid = s.id and (s.AffiliateDefaultUrl = r.AffiliateDefaultUrl or (s.DeepUrlTemplate = r.DeepUrlTemplate AND r.DeepUrlTemplate <> '' AND !isnull(r.DeepUrlTemplate)))) WHERE r.Status = 'Active')";		
	}elseif($hasPS == "h_active"){
		$condition['sql'] .= "AND p.ID IN (SELECT ProgramId From program_store_relationship WHERE Status = 'Active')";
	}elseif($hasPS == "h_inactive"){
		$condition['sql'] .= "AND p.ID IN (SELECT ProgramId From program_store_relationship WHERE Status = 'Inactive') AND p.ID NOT IN (SELECT ProgramId From program_store_relationship WHERE Status = 'Active')";
	}elseif($hasPS == "h_no"){
		$condition['sql'] .= "AND p.ID NOT IN (SELECT ProgramId From program_store_relationship)";
	}
	
	if($down){
		$limit_from = 0;
		$perpage = 1000000;
	}
	
	//get ps notice	
	//$sql = "SELECT * FROM program AS p LEFT JOIN program_int AS i ON (p.id = i.ProgramId) WHERE 1=1 {$condition['sql']} ORDER BY {$order} LIMIT {$limit_from}, $perpage";		
	$sql = "SELECT * FROM program AS p WHERE 1=1 {$condition['sql']} ORDER BY {$order} LIMIT {$limit_from}, $perpage";
	$data = $objMysqlTask->getRows($sql, 'ID', true);
	
	$count = $objMysqlTask->FOUND_ROWS;	
	//$countData = $programModel->getProgramListByCondition($condition, "count(*) as cnt");
	//$count = isset($countData[0]['cnt']) ? $countData[0]['cnt'] : 0;
	
	include_once(INCLUDE_ROOT . "lib/Class.Page.php");
	$objPB = new OPB($count, $perpage);
	$objPB->onepage = $perpage;
	$pagebar = $objPB->whole_bar(3, 8);
	$pagebar1 = $objPB->whole_bar(4, 8);
	
	//$condition['order'] .= " {$order} ";
	
	//$condition['limit'] .= $objPB->offset . ", " . $perpage;
	//$data = $programModel->getProgramListByCondition($condition, '*');	
	//$objMerchant = new NormalMerchant($objMysql);
	//$gradeArr = $objMerchant->getMerGrade();
	
	$s_ids = array();
	$p_ids = array();
	foreach ($data as $key => $val) {
		$affiliateInfo = $programModel->getAffiliateInfoById($val['AffId']);
		$data[$key]['AffId'] = $val['AffId'];
		$data[$key]['affiliatename'] = $affiliateInfo['Name'];
		if (isset($val['Homepage']) && !empty($val['Homepage'])) $data[$key]['go_Homepage'] = get_ssl_rd_url(trim($val['Homepage']));
		
		if (isset($allProgramTemplateUrl[$val['AffId']]) && !empty($val['IdInAff'])) {
			if ($idInAffUrl = replaceProgramUrlTemplate($val['IdInAff'], $allProgramTemplateUrl[$val['AffId']]['ProgramUrlTemplate'])) $data[$key]['idInAffUrl'] = $idInAffUrl;
		}
		
		/*$merchantProgramData = array();
		if (!empty($val['ID']) && !empty($val['AffId'])) {
			$merProgramCon = " and AffId={$val['AffId']} and ProgramId={$val['ID']} ";
			$merchantProgramData = $programModel->getMerchantProgram($merProgramCon);
		}
		$data[$key]['merProgramInfo'] = $merchantProgramData;*/
		/*if (in_array($val['ID'], $programIds4RemindArr)) $data[$key]['isremind'] = 1;
		else $data[$key]['isremind'] = 0;
		
		if (strlen($val['Remark']) > 80) $data[$key]['remarkshort'] = substr($val['Remark'], 0, 80) . '...';
		
		
		$pm_arr = $programModel->getPMInfoById($val['ID']);
		foreach($pm_arr as $k=>$v){
			$pm_arr[$k]['Grade'] = merchant_grade($v['Site'],$v['MerchantId']);
		}
		$data[$key]['pm'] = $pm_arr;
		
		$ps_arr = $programModel->getPSInfoById($val['ID']);
		$data[$key]['ps'] = $ps_arr;
		
		$sid = array();
		if(count($ps_arr)){
			foreach($ps_arr as $v){
				$s_ids[] = $v["StoreId"];
				$sid[] = $v["StoreId"];		
			}
		}
		
		$s_arr = array();
		$sm_arr = array();
		$tmp_arr = $objStore->getStoreMerchantRelByStoreIds($sid);
		foreach($tmp_arr as $v){
			$s_arr[strtolower($v['SiteName'])][$v['MerchantID']] = $v['StoreID'];
			
			
		}
		foreach($pm_arr as $v){
			foreach($s_arr as $sitename => $vv){				
				if($v['Site'] == $sitename){					
					if(isset($vv[$v['MerchantId']])) $sm_arr[$vv[$v['MerchantId']]][] = $v;
				}
			}
		}		
		$data[$key]['sm'] = $sm_arr;
		
		
		//store HDD relationships
		$storeRel = getStoreMerchantRelByStoreIds($sid);	
		$data[$key]['storeHDDRel'] = $storeRel;*/
		//print_r($pm_arr);
		//$p_ids[] = $val['ID'];
	}
	//print_r($s_ids);
	/*$s_default = array();
	$p_default = array();	
	if(count($s_ids)){
		//get store default url
		$tmp_arr = array();
		$tmp_arr = $programModel->getStoreDefaultInfoByIds($s_ids);
		//print_r($tmp_arr);
		foreach($tmp_arr as $v){
			if($v["AffiliateDefaultUrl"] == $v["s_AffiliateDefaultUrl"] && !empty($v["AffiliateDefaultUrl"])){
				$s_default[$v["StoreId"]]["AffiliateDefaultUrl"] = $v["ProgramId"];
			}
			if($v["DeepUrlTemplate"] == $v["s_DeepUrlTemplate"] && !empty($v["DeepUrlTemplate"])){
				$s_default[$v["StoreId"]]["DeepUrlTemplate"] = $v["ProgramId"];
			}
			$s_default[$v["StoreId"]]["SEM"] = $v["SEM"];
			$p_ids[] = $v["ProgramId"];
		}
		
		if(count($p_ids)){
			$condition = array();	
			$condition = array('sql' => "AND ID IN ('".implode("','", $p_ids)."')", 'order' => '', 'limit' => '');
			$tmp_arr = array();
			$tmp_arr = $programModel->getProgramListByCondition($condition, '*');
			
			foreach($tmp_arr as $v){
				$p_default[$v["ID"]]["Name"] = $v["Name"];
				$p_default[$v["ID"]]["IdInAff"] = $v["IdInAff"];
				$p_default[$v["ID"]]["AffId"] = $v["AffId"];
				
				$affiliateInfo = $programModel->getAffiliateInfoById($v['AffId']);				
				$p_default[$v["ID"]]['AffName'] = $affiliateInfo['ShortName'];
			}
		}
		
		//get store merchant rel
		$tmp_arr = array();		
		$tmp_arr = $objStore->getStoreMerchantRelByStoreIds($s_ids);
		foreach($tmp_arr as $v){
			$smArr[$v['StoreID']][$v['SiteName']] = $v['MerchantID'];
		}
	}*/
	
	
	
	/*if(count($p_ids)){
		$ps_arr = $programModel->getPSMInfoById($p_ids);
		
		$psm_info = array();
		foreach($ps_arr as $v){
			$psm_info[$v['ProgramId']][$v['StoreId']][$v['MerchantID']] = $v;
			
		}
		
		foreach($data as $key => $val){
			if(isset($psm_info[$val['ID']])){
				$data[$key]['psm'] = $psm_info[$val['ID']];
			}
		}
	}*/
	
	//echo "<pre>";print_r($data);
	
	$siteArr = array("0" => "All", "csus" => "CSUS", "csau" => "CSAU", "csca" => "CSCA", "csde" => "CSDE", "csie" => "CSIE", "csuk" => "CSUK", "csnz" => "CSNZ");
	
	$pmArr = array(0 => "All", 1 => "Has relationship", 2 => "No relationship");
	
	$psArr = array("All" => "All", "h_all" => "Have(All)", "h_default" => "Have(Default)", "h_active" => "Have(Active)", "h_inactive" => "Have(Inactive)", "h_no" => "No");
	
	$groupArr = array();
	/*$sql = "SELECT * FROM `program_int` WHERE GroupInc IS NOT NULL AND GroupInc != '' GROUP BY GroupInc";		
	$arr = $objMysqlTask->getRows($sql);
	foreach($arr as $v){
		$groupArr[$v['GroupInc']] = $v['GroupInc'];
	}*/
	
	$mobilefriendlyArr = array("All", "YES", "NO", "UNKNOWN");
	
	//echo "<pre>";print_r($data);
	if($down){
		header("Content-Type: text/csv");  
		header("Content-Disposition: attachment; filename=program_list.csv");  
		header('Cache-Control:must-revalidate,post-check=0,pre-check=0');  
		header('Expires:0');  
		header('Pragma:public');  
		echo "Program name,Program id in aff,Rank in aff,Status,Partnership,Program edit url\n"; 
		foreach($data as $v){
			$Partnership = $v['Partnership'];
			$Partnership .= '	'.$v['PartnershipChangeReason'];
			if($v['WeDeclined'] == "YES"){
				$Partnership .= '	(We Declined)';
			}elseif($v['WeDeclined'] == "NoNeedToApply"){
				$Partnership .= '	(NoNeedToApply)';
			}
			echo "{$v['Name']},{$v['IdInAff']},{$v['RankInAff']},{$v['StatusInAff']},\"{$Partnership}\",".LINK_ROOT."/front/program_edit.php?ID={$v['ID']}\n";
		}
		exit;
	}
	
	$tpl->assign("p_default", $p_default);
	$tpl->assign("s_default", $s_default);
	$tpl->assign("hasCoop", $hasCoop);
	$tpl->assign("pmArr", $pmArr);
	$tpl->assign("psArr", $psArr);	
	$tpl->assign("hasPM", $hasPM);
	$tpl->assign("hasPS", $hasPS);
	$tpl->assign("site", $site);
	$tpl->assign("merchantname", $merchantname);
	$tpl->assign("merchantid", $merchantid);
	$tpl->assign("siteArr", $siteArr);
	$tpl->assign("affiliatename", $affiliatename);
	$tpl->assign("affiliatetype", $affiliatetype);
	$tpl->assign("country", $country);
	$tpl->assign("partnership", $partnership);
	$tpl->assign("wedeclined", $wedeclined);
	$tpl->assign("statusinaff", $statusinaff);
	$tpl->assign("name", $name);
	$tpl->assign("createdatestart", $createdatestart);
	$tpl->assign("createdatend", $createdatend);
	$tpl->assign("addtimestart", $addtimestart);
	$tpl->assign("addtimeend", $addtimeend);
	$tpl->assign("expireremind", $expireremind);
	$tpl->assign("order", $order);
	$tpl->assign("data", $data);
	$tpl->assign("pagebar", $pagebar);
	$tpl->assign("pagebar1", $pagebar1);
	$tpl->assign("g_SiteUrl", $g_SiteUrl);
	$tpl->assign("groupArr", $groupArr);
	$tpl->assign("group", $group);
	$tpl->assign("mobilefriendly", $mobilefriendly);
	$tpl->assign("mobilefriendlyArr", $mobilefriendlyArr);
	
	
	$tpl->display("program_list.tpl");
	
	function merchant_grade($site,$mid){
		global $objMysql,$oTaskEmail;
		if(isset($objMysql[$site])){
			$db = $objMysql[$site];
		}else{
			$objMysql[$site] = $oTaskEmail->getSiteMysqlObj($site);
			$db = $objMysql[$site];
		}
		$objMerchant = new NormalMerchant($db);
		$gradeArr = $objMerchant->getMerGrade();
		$sql = "SELECT Grade FROM `normalmerchant_addinfo` WHERE ID = " . intval($mid);
		$query = $db->query($sql);
		$row = $db->getRow($query);		
		return $gradeArr[$row['Grade']];
	}
	
	// temp, need move to class.store when hdd is OK
	function getStoreMerchantRelByStoreIds($storeid = array()) {
		global $objMysqlTask;
		$data = array();
		if (!count($storeid)) return $data;
		$sql = "select * from `store_hdd_relationship` where `StoreID` IN ('" . implode("','", $storeid) . "')";
		
		$data = $objMysqlTask->getRows($sql);
		
		return $data;
	}
?>