<?php
if (!defined("__MOD_CLASS_AFFILIATE__"))
{
	define("__MOD_CLASS_AFFILIATE__",1);
	
	class Affiliate
	{
		var $mysqlObjs = array();
		var $logFields = array( "Name" 					=> "Name",
								"Domain" 				=> "Domain",
								"IsActive" 				=> "IsActive",
								"IsInHouse" 			=> "IsInhouse",
								"AffiliateUrlKeywords" 	=> "AffiliateUrl Keyword LIST 1",
								"AffiliateUrlKeywords2" => "AffiliateUrl Keyword LIST 2",
								"DeepUrlParaName" 		=> "Deep Url ParaName",
								"SubTracking" 			=> "Sub Tracking Setting 1",
								"SubTracking2" 			=> "Sub Tracking Setting 2",
								"RevenueAccount" 		=> "Revenue Account",
								"RevenueCycle" 			=> "Revenue Cycle",
								"ProgramCrawled" 		=> "Program Crawled",
								"StatsReportCrawled" 	=> "Stats Report Crawled",
								"StatsAffiliateName" 	=> "Stats Affiliate NAME");
		
		public $country_arr = array(
					'GLOBAL' => 'GLOBAL(GLOBAL)', 
					'EU' => 'European Union(EU)', 
					'AR' => 'Argentina(AR)', 
					'AU' => 'Australia(AU)', 
					'AT' => 'Austria',
				    'BE' => 'Belgium(BE)', 
				    'CA' => 'Canada(CA)', 
				    'CH' => 'Switzerland(CH)', 
				    'CN' => 'China(CN)', 
				    'CR' => 'Costa Rica(CR)', 
				    'CY' => 'Cyprus(CY)',
				    'CZ' => 'Czech Republic(CZ)', 
				    'DK' => 'Denmark(DK)', 
				    'SV' => 'El Salvador(SV)', 
				    'EE' => 'Estonia(EE)', 
				    'FI' => 'Finland(FI)', 
				    'FR' => 'France(FR)', 
				    'DE' => 'German(DE)', 
				    'GI' => 'Gibraltar(GI)', 
				    'GP' => 'Guadeloupe(GP)',  
				    'GR' => 'Greece(GR)', 
				    'HK' => 'Hong Kong(HK)', 
				    'IN' => 'India(IN)', 
				    'ID' => 'Indonesia(ID)', 
				    'IE' => 'Ireland(IE)', 
				    'IL' => 'Israel(IL)', 
				    'IT' => 'Italy(IT)', 
				    'JP' => 'Japan(JP)', 
				    'LV' => 'Latvia(LV)', 
				    'LU' => 'Luxembourg(LU)', 
				    'MA' => 'Morocco(MA)', 
				    'MX' => 'Mexico(MX)', 
				    'MY' => 'Malaysia(MY)', 
				    'NL' => 'Netherlands(NL)', 
				    'NO' => 'Norway(NO)', 
				    'NZ' => 'New Zealand(NZ)', 
				    'PH' => 'Philippines(PH)', 
				    'PL' => 'Poland(PL)', 
				    'PT' => 'Portugal(PT)', 
				    'QA' => 'Qatar(QA)', 
				    'RO' => 'Romania(RO)', 
				    'ZA' => 'South Africa(ZA)', 
				    'SE' => 'Sweden(SE)', 
				    'SG' => 'Singapore(SG)', 
				    'ES' => 'Spain(ES)', 
				    'TW' => 'Taiwan(TW)', 
				    'TH' => 'Thailand(TH)', 
				    'AE' => 'United Arab Emirates(AE)', 
				    'UK' => 'United Kingdom(UK)', 
				    'US' => 'United States(US)', 
				    'VG' => 'Virgin Island, British(VG)');
		function __construct()
		{
			global $databaseInfo;
			global $syncsites;
			$this->objMysql = new Mysql(TASK_DB_NAME, TASK_DB_HOST, TASK_DB_USER, TASK_DB_PASS);
			$this->mysqlObjs = array();
			/*
			foreach ($syncsites as $v) {
				$this->mysqlObjs[$v] = new Mysql($databaseInfo["INFO_" . $v . "_DB_NAME"], $databaseInfo["INFO_" . $v . "_DB_HOST"], $databaseInfo["INFO_" . $v . "_DB_USER"], $databaseInfo["INFO_" . $v . "_DB_PASS"]);
			}
			*/
		}
		
		function getSiteMysqlObj($site)
		{
			$site = strtoupper($site);
			if(!isset($this->mysqlObjs[$site]))
			{
				global $databaseInfo;
				$this->mysqlObjs[$site] = new Mysql($databaseInfo["INFO_" . $site . "_DB_NAME"], $databaseInfo["INFO_" . $site . "_DB_HOST"], $databaseInfo["INFO_" . $site . "_DB_USER"], $databaseInfo["INFO_" . $site . "_DB_PASS"]);
			}
			return $this->mysqlObjs[$site];
		}
		
		function getAffiliates($condition = array())
		{
			$data = array();
			$sql = "select * from `wf_aff` where 1=1 ";
			
			$str_conditon = '';
			if (isset($condition['type']) && $condition['type'] == 'YES') {
				$str_conditon .= " and IsInHouse='YES' ";
			} elseif (isset($condition['type']) && $condition['type'] ==  'NO') {
				$str_conditon .= " and IsInHouse='NO' ";
			}
			if (isset($condition['name']) && !empty($condition['name'])) {
				$str_conditon .= " and Name like '%" . addslashes($condition['name']) . "%' ";
			}
		    if (isset($condition['affurlkw']) && !empty($condition['affurlkw'])) {
				$str_conditon .= " and AffiliateUrlKeywords like '%" . addslashes($condition['affurlkw']) . "%' ";
			}
		    if (isset($condition['isactive']) && !empty($condition['isactive'])) {
				$str_conditon .= " and IsActive='{$condition['isactive']}' ";
			}
			if (isset($condition['RevenueAccount']) && strlen($condition['RevenueAccount'])>0) {
				if($condition['RevenueAccount'] == "12"){
					$str_conditon .= " and (RevenueAccount='{$condition['RevenueAccount']}' or RevenueAccount is null or RevenueAccount = 0) ";
				}else{
					$str_conditon .= " and RevenueAccount='{$condition['RevenueAccount']}' ";
				}
			}
		    if (isset($condition['ProgramCrawled']) && !empty($condition['ProgramCrawled'])) {
				$str_conditon .= " and ProgramCrawled='{$condition['ProgramCrawled']}' ";
			}
			if (isset($condition['countrySel']) && !empty($condition['countrySel'])) {
				$str_conditon .= " and Country like '%{$condition['countrySel']}%' ";
			}
			if (isset($condition['orderby']) && !empty($condition['orderby'])) {
				$str_conditon .= $condition['orderby'];
			}
		    if (isset($condition['limit']) && !empty($condition['limit'])) {
				$str_conditon .= $condition['limit'];
			}
		    
			$sql .= $str_conditon;
			$data = $this->objMysql->getRows($sql);
			
			return $data;
		}
		
		function getAllAffiliate(){
			$sql = "select * from `wf_aff`";
			$data = $this->objMysql->getRows($sql, "ID");
			return $data;
		}
		
		function getAffiliateCount($condition = array()) {
			$sql = "select count(*) as cnt from `wf_aff` where 1=1 ";
			
			$str_conditon = '';
			if (isset($condition['type']) && $condition['type'] == 'YES') {
				$str_conditon .= " and IsInHouse='YES' ";
			} elseif (isset($condition['type']) && $condition['type'] ==  'NO') {
				$str_conditon .= " and IsInHouse='NO' ";
			}
			if (isset($condition['name']) && !empty($condition['name'])) {
				$str_conditon .= " and Name like '%" . addslashes($condition['name']) . "%' ";
			}
		    if (isset($condition['affurlkw']) && !empty($condition['affurlkw'])) {
				$str_conditon .= " and AffiliateUrlKeywords like '%" . addslashes($condition['affurlkw']) . "%' ";
			}
		    if (isset($condition['isactive']) && !empty($condition['isactive'])) {
				$str_conditon .= " and IsActive='{$condition['isactive']}' ";
			}
			if (isset($condition['RevenueAccount']) && strlen($condition['RevenueAccount'])>0) {
				if($condition['RevenueAccount'] == "12"){
					$str_conditon .= " and (RevenueAccount='{$condition['RevenueAccount']}' or RevenueAccount is null or RevenueAccount = 0) ";
				}else{
					$str_conditon .= " and RevenueAccount='{$condition['RevenueAccount']}' ";
				}
			}
		    if (isset($condition['ProgramCrawled']) && !empty($condition['ProgramCrawled'])) {
				$str_conditon .= " and ProgramCrawled='{$condition['ProgramCrawled']}' ";
			}
		    if (isset($condition['countrySel']) && !empty($condition['countrySel'])) {
				$str_conditon .= " and Country like '%{$condition['countrySel']}%' ";
			}
			
			
			$sql .= $str_conditon;
			$query = $this->objMysql->query($sql);
			$data = $this->objMysql->getRow($query);
			
			return $data['cnt'];
			
		}
		
		function getAffilicateById($id = '') {
			if (empty($id) || !is_numeric($id)) return array();
			$sql = "select * from `wf_aff` where ID={$id}";
			return $this->objMysql->getFirstRow($sql);
		}
		
		function getAffilicateByIds($_arr_ids,$_col_names="*")
		{
			if(!is_array($_arr_ids)) return array();
			$sql = "select $_col_names from `wf_aff` where ID in (" . implode(",",$_arr_ids) . ")";
			return $this->objMysql->getRows($sql,"ID");
		}
		
		function getFinRevAccList($nullFlag = false) {
			$tmpdata=$data = array();
			$sql = "select * from `fin_rev_acc` order by Name";
			$tmpdata = $this->objMysql->getRows($sql);
			if($nullFlag){
				$data[0]="";
			}
			foreach ($tmpdata as $v){
				$data[$v['ID']]=$v['Name'];
			}		
			return $data;		
		}
		
		function addAffiliate($row = array()) {
			global $syncsites;
			$msg = array();
			if($row['ImportanceRank'] == "" || $row['ImportanceRank'] == "0" || $row['ImportanceRank'] == 0){
				$row['ImportanceRank'] = "99999999";
			}
			$sql = "insert into `wf_aff` (`Name`, `ShortName`, `Domain`, `BlogUrl`, `FacebookUrl`, `TwitterUrl`, `GetProgramIDInNetworkUrl`, 
			        `AffiliateUrlKeywords`, `AffiliateUrlKeywords2`, `SubTracking`, `SubTracking2`, `IsInHouse`, `IsActive`, `DeepUrlParaName`,`RevenueAccount`, `RevenueCycle`,
			        `RevenueRemark`, `ProgramCrawled`, `ProgramCrawlRemark`, `StatsReportCrawled`, `StatsReportCrawlRemark`, `StatsAffiliateName`, `ImportanceRank`, `ProgramUrlTemplate`, 
			        `Country`, `LoginUrl`, `SupportDeepUrl`, `SupportSubTracking`, `JoinDate`, `Comment`) values (
			        '" . addslashes($row['name']) . "', 
			        '" . addslashes($row['shortname']) . "', 
			        '" . addslashes($row['domain']) . "',
			        '" . addslashes($row['blog']) . "',
			        '" . addslashes($row['facebook']) . "',
			        '" . addslashes($row['twitter']) . "',
			        '" . addslashes($row['proidinnetword']) . "',
			        '" . addslashes($row['affurlkw']) . "',
			        '" . addslashes($row['affurlkw2']) . "',
			        '" . addslashes($row['subtrackingset']) . "',
			        '" . addslashes($row['subtrackingset2']) . "',
			        '" . addslashes($row['isinhouse']) . "',
			        '" . addslashes($row['isactive']) . "',
			        '" . addslashes($row['deepurlparaname']) . "',
			        '" . addslashes($row['RevenueAccount']) . "',
			        '" . addslashes($row['RevenueCycle']) . "',
			        '" . $row['RevenueRemark'] . "',
			        '" . addslashes($row['ProgramCrawled']) . "',
			        '" . addslashes($row['ProgramCrawlRemark']) . "',
			        '" . addslashes($row['StatsReportCrawled']) . "',
			        '" . addslashes($row['StatsReportCrawlRemark']) . "',
			        '" . addslashes($row['StatsAffiliateName']) . "',
			        '" . addslashes($row['ImportanceRank']) . "',
			        '" . addslashes($row['ProgramUrlTemplate']) . "',
			        '" . addslashes($row['counties']) . "',
			        '" . addslashes(trim($row['loginurl'])) . "',
			        '" . addslashes(trim($row['SupportDeepUrl'])) . "',
			        '" . addslashes(trim($row['SupportSubTracking'])) . "',
			        '" . addslashes(trim($row['joindate'])) . "',
			        '" . $row['Comment'] . "'
			        )";
			
			$query = $this->objMysql->query($sql);
			$row['last_insert_id'] = $this->objMysql->getLastInsertId();
			$logRes = $this->insertAffiliateChangeLog($row['last_insert_id'], array(), $reason = "", $source = "");
			if ($query) {
				$msg['TASK'] = "TASK: Add affilicate success";
				$res = $this->addFrontAffiliate($row);
				$msg = array_merge($msg, $res);
				
				return $msg;
			} else {
				$msg['TASK'] = "TASK: Add affilicate fail";
				return $msg;
			}
		}
		
		function updateAffiliate($row = array()) {
			global $syncsites;
			$msg = array();
			$sql = "update `wf_aff` set 
			       `Name`=                     '" . addslashes(trim($row['name'])) . "', 
			       `ShortName`=                '" . addslashes(trim($row['shortname'])) . "',
			       `Domain`=                   '" . addslashes(trim($row['domain'])) . "', 
			       `BlogUrl`=                  '" . addslashes(trim($row['blog'])) . "', 
			       `FacebookUrl`=              '" . addslashes(trim($row['facebook'])) . "', 
			       `TwitterUrl`=               '" . addslashes(trim($row['twitter'])) . "', 
			       `GetProgramIDInNetworkUrl`= '" . addslashes(trim($row['proidinnetword'])) . "', 
			       `AffiliateUrlKeywords`=     '" . addslashes(trim($row['affurlkw'])) . "', 
			       `AffiliateUrlKeywords2`=    '" . addslashes(trim($row['affurlkw2'])) . "', 
			       `SubTracking`=              '" . addslashes(trim($row['subtrackingset'])) . "', 
			       `SubTracking2`=              '" . addslashes(trim($row['subtrackingset2'])) . "', 
			       `IsInHouse`=                '" . addslashes(trim($row['isinhouse'])) . "', 
			       `IsActive`=                 '" . addslashes(trim($row['isactive'])) . "', 
			       `DeepUrlParaName`=          '" . addslashes(trim($row['deepurlparaname'])) . "',
			       `RevenueAccount`=                  '" . addslashes(trim($row['RevenueAccount'])) . "', 
			       `RevenueCycle`=              '" . addslashes(trim($row['RevenueCycle'])) . "', 
			       `RevenueRemark`=               concat(`RevenueRemark`, '{$row['RevenueRemark']}'), 
			       `ProgramCrawled`= '" . addslashes(trim($row['ProgramCrawled'])) . "', 
			       `ProgramCrawlRemark`=     '" . addslashes(trim($row['ProgramCrawlRemark'])) . "', 
			       `StatsReportCrawled`=              '" . addslashes(trim($row['StatsReportCrawled'])) . "', 
			       `StatsReportCrawlRemark`=              '" . addslashes(trim($row['StatsReportCrawlRemark'])) . "', 
			       `StatsAffiliateName`=                '" . addslashes(trim($row['StatsAffiliateName'])) . "',
			       `ImportanceRank`=                '" . addslashes(trim($row['ImportanceRank'])) . "',
			       `ProgramUrlTemplate`=                '" . addslashes(trim($row['ProgramUrlTemplate'])) . "',
			       `Country` =                '" . addslashes(trim($row['counties'])) . "',
			       `LoginUrl` = '" . addslashes(trim($row['loginurl'])) . "',
			       `SupportDeepUrl` = '" . addslashes(trim($row['SupportDeepUrl'])) . "',
			       `SupportSubTracking` = '" . addslashes(trim($row['SupportSubTracking'])) . "',
			       `JoinDate` = '" . addslashes(trim($row['joindate'])) . "',
			       `Comment`=               concat(`Comment`, '{$row['Comment']}') 
			       where `ID`=" . $row['id'];
			$query = $this->objMysql->query($sql);
			if ($query) {
				$msg['TASK'] = "TASK: Edit affilicate success";
				$res = $this->updateFrontAffiliate($row);
				$msg = array_merge($msg, $res);
				
				return $msg;
			} else {
				$msg['TASK'] = "TASK: Edit affilicate fail";
				return $msg;
			}
		}
		
		function write_table_change_log($row = array(),$user="") {
			$arr_change=array();
			if($row['RevenueAccount']!=$row['old_RevenueAccount']){
				$arr_change["RevenueAccount"] = array("from" => $row['old_RevenueAccount'], "to" => $row["RevenueAccount"]);
			}
			if($row['RevenueCycle']!=$row['old_RevenueCycle']){
				$arr_change["RevenueCycle"] = array("from" => $row['old_RevenueCycle'], "to" => $row["RevenueCycle"]);
			}
			if($row['RevenueRemark']!=$row['old_RevenueRemark']){
				$arr_change["RevenueRemark"] = array("from" => $row['old_RevenueRemark'], "to" => $row["RevenueRemark"]);
			}
			if(count($arr_change)>0){
				$arr_batch = array();
				$arr_batch["BatchTableName"] = "wf_aff";
				$arr_batch["BatchOperator"] = $user;
				$arr_batch["BatchComments"] = "";
				$arr_batch["BatchPrimaryKeyValue"] = "";		
				$arr_batch["BatchAction"] = "EDIT";
				$this->addTaskBdChangeLog($arr_batch, $arr_change);
			}
		}
		
		function addTaskBdChangeLog($arr_batch, $arr_change){
			if(is_array($arr_batch) && count($arr_batch)){
				$sql = "INSERT ignore INTO table_change_log_batch(BatchTableName,BatchOperator,BatchCreationTime,BatchComments,BatchPrimaryKeyValue,BatchAction) VALUES('".addslashes($arr_batch["BatchTableName"])."','".addslashes($arr_batch["BatchOperator"])."','".date("Y-m-d H:i:s")."','".addslashes($arr_batch["BatchComments"])."','".addslashes($arr_batch["BatchPrimaryKeyValue"])."','".addslashes($arr_batch["BatchAction"])."')";
				$this->objMysql->query($sql);
				$BatchId = $this->objMysql->getLastInsertId();
				if($BatchId && is_array($arr_change) && count($arr_change)){
					foreach($arr_change as $fieldname => $arr){
						$sql = "INSERT ignore INTO table_change_log_detail(BatchId,FiledName,FiledValueFrom,FiledValueTo) VALUES($BatchId,'".addslashes($fieldname)."','".addslashes($arr["from"])."','".addslashes($arr["to"])."')";
						$this->objMysql->query($sql);
					}
				}
			}
		}
		
		function addFrontAffiliate($row = array()) {
			global $syncsites;
			$res = array();
			
			$sql = $this->createInsertSql($row);
			foreach ($syncsites as $v) {
				$this->getSiteMysqlObj($v);
				$query = $this->mysqlObjs[$v]->query($sql);
				if ($query) {
					$res[$v] = $v . ": Add affilicate success";
				} else {
					$res[$v] = $v . ": Add affilicate fail";
				}
			}
			
			return $res;
		}
		
		function updateFrontAffiliate($row = array()) {
			global $syncsites;
			$res = array();
			
			$sql = $this->createUpdateSql($row);
		    foreach ($syncsites as $v) {
				$this->getSiteMysqlObj($v);
				$query = $this->mysqlObjs[$v]->query($sql);
		    	if ($query) {
					$res[$v] = $v . ": Edit affilicate success";
				} else {
					$res[$v] = $v . ": Edit affilicate fail";
				}
			}
			
			return $res;
		}
		
		function createInsertSql($row = array()) {
			$sql = "insert into `wf_aff` (`ID`, `Name`, `ShortName`, `Domain`, `BlogUrl`, `FacebookUrl`, `TwitterUrl`, `GetProgramIDInNetworkUrl`, 
			        `AffiliateUrlKeywords`, `AffiliateUrlKeywords2`, `SubTracking`, `SubTracking2`, `IsInHouse`, `IsActive`, `DeepUrlParaName`,`RevenueAccount`, `RevenueCycle`,
			        `RevenueRemark`, `ProgramCrawled`, `ProgramCrawlRemark`, `StatsReportCrawled`, `StatsReportCrawlRemark`, `StatsAffiliateName`, `ImportanceRank`, `ProgramUrlTemplate`, 
			        `Country`, `LoginUrl`, `SupportDeepUrl`, `SupportSubTracking`, `JoinDate`, `Comment`) values (
			        '" . addslashes($row['last_insert_id']) . "', 
			        '" . addslashes($row['name']) . "', 
			        '" . addslashes($row['shortname']) . "',
			        '" . addslashes($row['domain']) . "',
			        '" . addslashes($row['blog']) . "',
			        '" . addslashes($row['facebook']) . "',
			        '" . addslashes($row['twitter']) . "',
			        '" . addslashes($row['proidinnetword']) . "',
			        '" . addslashes($row['affurlkw']) . "',
			        '" . addslashes($row['affurlkw2']) . "',
			        '" . addslashes($row['subtrackingset']) . "',
			        '" . addslashes($row['subtrackingset2']) . "',
			        '" . addslashes($row['isinhouse']) . "',
			        '" . addslashes($row['isactive']) . "',
			        '" . addslashes($row['deepurlparaname']) . "',
			        '" . addslashes($row['RevenueAccount']) . "',
			        '" . addslashes($row['RevenueCycle']) . "',
			        '" . $row['RevenueRemark'] . "',
			        '" . addslashes($row['ProgramCrawled']) . "',
			        '" . addslashes($row['ProgramCrawlRemark']) . "',
			        '" . addslashes($row['StatsReportCrawled']) . "',
			        '" . addslashes($row['StatsReportCrawlRemark']) . "',
			        '" . addslashes($row['StatsAffiliateName']) . "',
			        '" . addslashes($row['ImportanceRank']) . "',
			        '" . addslashes($row['ProgramUrlTemplate']) . "',
			        '" . addslashes($row['counties']) . "',
			        '" . addslashes(trim($row['loginurl'])) . "',
			        '" . addslashes(trim($row['SupportDeepUrl'])) . "',
			        '" . addslashes(trim($row['SupportSubTracking'])) . "',
			        '" . addslashes(trim($row['joindate'])) . "',
			        '" . $row['Comment'] . "'
			        )";
			return $sql;
		}
		
		function createUpdateSql($row = array()) {
			$sql = "update `wf_aff` set 
			       `Name`=                     '" . addslashes($row['name']) . "', 
			       `ShortName`=                '" . addslashes($row['shortname']) . "',
			       `Domain`=                   '" . addslashes($row['domain']) . "', 
			       `BlogUrl`=                  '" . addslashes($row['blog']) . "', 
			       `FacebookUrl`=              '" . addslashes($row['facebook']) . "', 
			       `TwitterUrl`=               '" . addslashes($row['twitter']) . "', 
			       `GetProgramIDInNetworkUrl`= '" . addslashes($row['proidinnetword']) . "', 
			       `AffiliateUrlKeywords`=     '" . addslashes($row['affurlkw']) . "', 
			       `AffiliateUrlKeywords2`=    '" . addslashes($row['affurlkw2']) . "', 
			       `SubTracking`=              '" . addslashes($row['subtrackingset']) . "', 
			       `SubTracking2`=              '" . addslashes($row['subtrackingset2']) . "', 
			       `IsInHouse`=                '" . addslashes($row['isinhouse']) . "', 
			       `IsActive`=                 '" . addslashes($row['isactive']) . "', 
			       `DeepUrlParaName`=          '" . addslashes($row['deepurlparaname']) . "',
			       `RevenueAccount`=                  '" . addslashes(trim($row['RevenueAccount'])) . "', 
			       `RevenueCycle`=              '" . addslashes(trim($row['RevenueCycle'])) . "', 
			       `RevenueRemark`=               concat(`RevenueRemark`, '{$row['RevenueRemark']}'),
			       `ProgramCrawled`= '" . addslashes(trim($row['ProgramCrawled'])) . "', 
			       `ProgramCrawlRemark`=     '" . addslashes(trim($row['ProgramCrawlRemark'])) . "', 
			       `StatsReportCrawled`=              '" . addslashes(trim($row['StatsReportCrawled'])) . "', 
			       `StatsReportCrawlRemark`=              '" . addslashes(trim($row['StatsReportCrawlRemark'])) . "', 
			       `StatsAffiliateName`=                '" . addslashes(trim($row['StatsAffiliateName'])) . "',
			       `ImportanceRank`=                '" . addslashes(trim($row['ImportanceRank'])) . "',
			       `ProgramUrlTemplate`=                '" . addslashes(trim($row['ProgramUrlTemplate'])) . "',
			       `Country`=                '" . addslashes(trim($row['counties'])) . "',
			       `LoginUrl`=                '" . addslashes(trim($row['loginurl'])) . "',
			       `SupportDeepUrl`=                '" . addslashes(trim($row['SupportDeepUrl'])) . "',
			       `SupportSubTracking`=                '" . addslashes(trim($row['SupportSubTracking'])) . "',
			       `JoinDate`=                '" . addslashes(trim($row['joindate'])) . "',
			       `Comment`=               concat(`Comment`, '{$row['Comment']}')
			       where `ID`=" . $row['id'];
			return $sql;
		}
		
		function updateAllSites() {
			global $syncsites;
			
			$sourceData = $this->getAffiliates();
			$sql = $this->createMultiInsertSql($sourceData);
			
			foreach ($syncsites as $v) {
				$this->getSiteMysqlObj($v);
				$this->mysqlObjs[$v]->query("drop table if exists wf_aff_bak");
				$this->mysqlObjs[$v]->query("create table wf_aff_bak like wf_aff");
				$query = $this->mysqlObjs[$v]->query($sql);
				if ($query) {
					$this->mysqlObjs[$v]->query("rename table `wf_aff` to `wf_aff_tmp`, `wf_aff_bak` to `wf_aff`, `wf_aff_tmp` to `wf_aff_bak`");
					$res[$v] = $v . ": update success";
				} else {
					$res[$v] = $v . ": update fail";
				}
			}
			
			return $res;
		}
		
		function createMultiInsertSql($data = array()) {
			$sql = '';
			if (empty($data)) return $sql;
			$sql = "insert into `wf_aff_bak`(`ID`, `Name`, `ShortName`, `Domain`, `BlogUrl`, `FacebookUrl`, `TwitterUrl`, `GetProgramIDInNetworkUrl`, `AffiliateUrlKeywords`, `AffiliateUrlKeywords2`, 
			       `SubTracking`, `SubTracking2`, `IsInHouse`, `IsActive`, `DeepUrlParaName`, `RevenueAccount`, `RevenueCycle`, `RevenueRemark`, `ProgramCrawled`, `ProgramCrawlRemark`, 
			       `StatsReportCrawled`, `StatsReportCrawlRemark`, `StatsAffiliateName`, `ImportanceRank`, `ProgramUrlTemplate`, `Country`, `LoginUrl`, `SupportDeepUrl`, `SupportSubTracking`, `JoinDate`, `Comment`) values ";
			foreach ($data as $v) {
				$sql .= "('" . addslashes($v['ID']) . "', '" . addslashes($v['Name']) . "', '" . addslashes($v['ShortName']) . "', '" . addslashes($v['Domain']) ."', '" . addslashes($v['BlogUrl']) . "', '" . addslashes($v['FacebookUrl']) ."', 
				          '" . addslashes($v['TwitterUrl']) ."', '" . addslashes($v['GetProgramIDInNetworkUrl']) ."', '" . addslashes($v['AffiliateUrlKeywords']) ."', '" . addslashes($v['AffiliateUrlKeywords2']) ."', '" . addslashes($v['SubTracking']) ."', '" . addslashes($v['SubTracking2']) ."', 
				          '" . addslashes($v['IsInHouse']) ."', '" . addslashes($v['IsActive']) ."', '" . addslashes($v['DeepUrlParaName']) ."', '" . addslashes($v['RevenueAccount']) . "', '" . addslashes($v['RevenueCycle']) . "', '" . addslashes($v['RevenueRemark']) . "', 
				          '" . addslashes($v['ProgramCrawled']) . "', '" . addslashes($v['ProgramCrawlRemark']) . "', '" . addslashes($v['StatsReportCrawled']) . "', '" . addslashes($v['StatsReportCrawlRemark']) . "', '" . addslashes($v['StatsAffiliateName']) . "', 
				          '" . addslashes($v['ImportanceRank']) . "', '" . addslashes($v['ProgramUrlTemplate']) . "', '" . addslashes($v['counties']) . "', '" . addslashes($v['loginurl']) . "', '" . addslashes($v['SupportDeepUrl']) . "', '" . addslashes($v['SupportSubTracking']) . "', '" . addslashes($v['JoinDate']) . "', '" . addslashes($v['Comment']) . "'),";
			}
			$sql = substr($sql, 0, -1);
			return $sql;
		}
		function checkAffurlValids($affId, $defaultAffUrl, $templateAffUrl){
			$default = $this->checkAffurlValid($affId, $defaultAffUrl);
			$template = $this->checkAffurlValid($affId, $templateAffUrl);
			if($default == $template){
				return true;
			}
			return false;
		}
		
		function getMerUrlBlackList($storeId, $url){
			$sql = "select * from store_merchant_relationship where StoreID = '$storeId'";
			$msRow = $this->objMysql->getRows($sql);
			$resArr = array();
			foreach($msRow as $row){
				$site = strtolower($row["SiteName"]);
				$mysqlObjBase = $this->getSiteMysqlObj($site);
				$sql = "select * from black_list where ObjType = 'MERCHANT' and Scope like '%url%' and ((BlackListType = 'MERCHANT' AND Site = '$site' and ObjID = '{$row["MerchantID"]}') OR BlackListType = 'GLOBAL')";
				$blkRows = $this->objMysql->getRows($sql);
				foreach($blkRows as $row){
					$resArr[$row["Keyword"]] = $row["Keyword"];
				}
			}
			return $resArr;
		}
		function checkMerUrlBlackList($storeId, $url){
			if(trim($url) == ""){
				return true;
			}
			$keywords = $this->getMerUrlBlackList($storeId, $url);
			$rectedCode = "";
			if(count($keywords) > 0 && $url != ""){
				foreach ($keywords as $keyword){
					if(stripos($url, trim($keyword)) !== false){
						$rectedCode .= "$keyword in Url($url) is a Black Keyword.<br/>";
						return $rectedCode;
					}
				}
			}
			return true;
		}
		function checkAmazonUrl($affId, $Url){
			if($affId == "100" || $affId == "101" || $affId == "102"){
				if(stripos($Url, "tag=") !== false){
					return false;
				}
			}
			return true;
		}
		function checkAffurlValid($affId, $affUrl){
			$affInfo = $this->getAffilicateById($affId);
			if($affInfo["IsInHouse"] == 'YES') return true;
			$AffiliateUrlKeywords = trim($affInfo["AffiliateUrlKeywords"]);
			$AffiliateUrlKeywords2 = trim($affInfo["AffiliateUrlKeywords2"]);
			$AffiliateUrlKeywordsArr = explode("\r\n", $AffiliateUrlKeywords);
			$AffiliateUrlKeywords2Arr = explode("\r\n", $AffiliateUrlKeywords2);
			foreach ($AffiliateUrlKeywordsArr as $key => $val){
				if(trim($val) == ""){
					unset($AffiliateUrlKeywordsArr[$key]);
				}else{
					$AffiliateUrlKeywordsArr[$key] = trim($val);
				}
			}
			foreach ($AffiliateUrlKeywords2Arr as $key => $val){
				if(trim($val) == ""){
					unset($AffiliateUrlKeywords2Arr[$key]);
				}else{
					$AffiliateUrlKeywords2Arr[$key] = trim($val);
				}
			}
			//$res['status'] = false;
			//$res['msg'] = "Invalid {$k}({$v}). Please make sure using affiliate url.";
			if($AffiliateUrlKeywords != "" && $AffiliateUrlKeywords2 != ""){
				$AffUrlKwFlg = false;
				foreach ($AffiliateUrlKeywordsArr as $keyword){
					if(stripos($affUrl, trim($keyword)) !== false){
						$AffUrlKwFlg = true;
					}
				}
				$AffUrlKwFlg2 = false;
				foreach ($AffiliateUrlKeywords2Arr as $keyword){
					if(stripos($affUrl, trim($keyword)) !== false){
						$AffUrlKwFlg2 = true;
					}
				}
				if($AffUrlKwFlg && $AffUrlKwFlg2){
					
					return true;
				}
				return false;
			}
			if($AffiliateUrlKeywords != ""){
				foreach ($AffiliateUrlKeywordsArr as $keyword){
					if(stripos($affUrl, trim($keyword)) !== false){
						return true;
					}
				}
				return false;
			}
			if($AffiliateUrlKeywords2 != ""){
				foreach ($AffiliateUrlKeywords2Arr as $keyword){
					if(stripos($affUrl, trim($keyword)) !== false){
						return true;
					}
				}
				return false;
			}
			return true;
		}
		
		function checkGlableLPUrlBlackKeywordList($url, $glableLPUrlBlackKeywordList = ""){
			if($glableLPUrlBlackKeywordList == ""){
				global $glableLPUrlBlackKeywordList;
			}
			if(count($glableLPUrlBlackKeywordList) == 0){
				return true;
			}
			foreach ($glableLPUrlBlackKeywordList as $keyword){
				if(stripos($url, trim($keyword)) !== false){
					return $keyword;
				}
			}
			return true;
		}
		function checkLPUrlBlackKeywordList($url, $lPUrlBlackKeywordList){
			$lPUrlBlackKeywordListArr = array();
			if(!is_array($lPUrlBlackKeywordList)){
				$lPUrlBlackKeywordListArr = explode("\r\n", $lPUrlBlackKeywordList);
			}else{
				$lPUrlBlackKeywordListArr = $lPUrlBlackKeywordList;
			}
			if(count($lPUrlBlackKeywordListArr) == 0){
				return true;
			}
			foreach ($lPUrlBlackKeywordListArr as $keyword){
				if(stripos($url, trim($keyword)) !== false){
					return $keyword;
				}
			}
			return true;
		}
		function insertAffiliateChangeLog($affId, $oldAff, $reason = "", $source = ""){
			$newAff = $this->getAffilicateById($affId);
			
			$logFields = $this->logFields;
			if(count($oldAff) == 0){
				
				//del aff
				$newValue = "";
				foreach ($logFields as $field => $none){
					if($newValue == ""){
						$newValue .= $field . ":" . $newAff[$field];
					}else{
						$newValue .= "||" . $field . ":" . $newAff[$field];
					}
				}
				$this->insertAffLog($affId, $newAff["Name"], "add", "", $newValue, "add", "", $source);
				return true;
				
			}
			if(count($newAff) == 0){
				//add aff
				$oldValue = "";
				foreach ($logFields as $field => $none){
					if($oldValue == ""){
						$oldValue .= $field . ":" . $oldAff[$field];
					}else{
						$oldValue .= "||" . $field . ":" . $oldAff[$field];
					}
				}
				$this->insertAffLog($affId, $oldAff["Name"], "delete", $oldValue, "", "delete", $reason, $source);
				return true;
			}
			foreach ($logFields as $field => $none){
				if($oldAff[$field] != $newAff[$field]){
					$this->insertAffLog($affId, $newAff["Name"], $field, $oldAff[$field], $newAff[$field], "update", $reason, $source);
				}
			}
			return true;
		}
		function insertAffLog($affId, $affName, $field, $valueFrom, $valueTo, $type, $reason = "", $source = ""){
			$user = $_SERVER['PHP_AUTH_USER'] ? $_SERVER['PHP_AUTH_USER'] : $_SERVER["REMOTE_USER"];
			if(trim($user) == ""){
				$user = get_cookie_var("edit_user");;
			}
			$sql = "insert into aff_change_log (
				AffId, 
				AffName, 
				Source, 
				Reason, 
				Type,
				Status, 
				ValueFrom, 
				ValueTo, 
				Creator, 
				FieldName,
				AddTime) values(
				'$affId',
				'" . addslashes($affName) . "',
				'" . addslashes($source) . "',
				'" . addslashes($reason) . "',
				'$type',
				'NEW',
				'" . addslashes($valueFrom) . "',
				'" . addslashes($valueTo) . "',
				'$user',
				'" . addslashes($field) . "',
				'" . date("Y-m-d H:i:s") . "'
				)";
			$this->objMysql->query($sql);
			return true;
		}
		//Add function here
	}
}
?>
