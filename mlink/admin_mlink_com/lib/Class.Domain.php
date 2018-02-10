<?php
class Domain extends LibFactory
{
	function getstoreaff($id){
		$sql = "select StoreAffSupport,affids from store where id=$id";
		$res = $this->getRow($sql);
		if($res['StoreAffSupport'] == 'YES'){
			$affids = isset($res['Affids']) ? $res['Affids'] : '';
			if (empty($affids)) {
				return '/';
			}
			$sql = "select `Name` from wf_aff where ID in({$affids}) order by `Name`";
			$res = $this->getRows($sql);
			if(!empty($res)){
				$text ='';
				foreach($res as $k){
					$text.=$k['Name']."</br>";
				}
				return $text;
			}else{
				return '/';
			}
		}else{
			$sql = "SELECT d.`Name`, d.`ID` FROM `r_store_domain` a INNER JOIN `r_domain_program` b ON a.`DomainId` = b.`DID` INNER JOIN `program_intell` c ON b.`PID` = c.`ProgramId` INNER JOIN wf_aff d ON c.`AffId` = d.`ID` WHERE b.Status = 'Active' AND  a.`StoreId` = $id GROUP BY d.`ID` ORDER BY d.`Name`";
			$res = $this->getRows($sql);
			if(!empty($res)){
				$text ='';
				foreach($res as $k){
					$text.=$k['Name']."</br>";
				}
				return $text;
			}else{
				return '/';
			}
		}
	}
	//merchants domain逻辑
	function getDomainListPage($search,$page,$page_size = 30){

// 		$where_str = '';
// 		if(isset($search['domain_keywords']) && $search['domain_keywords'])
// 			$where_str = '`Key` LIKE "%'.$search['domain_keywords'].'%"';         //like %ABC%,搜索字符串中含有ABC的字符串
		$where_str = '';
		if(isset($search['Domain']) && $search['Domain']){
		}else{
			$search['Domain']="";
		}
		if(isset($search['Program']) && $search['Program']){
		}else{
			$search['Program']="";
		}
		if(isset($search['IsFake']) && $search['IsFake']){
		}else{
			$search['IsFake']="";
		}



		$where=array();
		if(!empty($search['Domain'])){            //domain搜索条件
// 			$query_domain = 'SELECT ID FROM domain WHERE Domain ="'.$search['Domain'].'"';
// 			$result = mysql_query($query_domain);
// 			$arr = mysql_fetch_array($result);
// 			$domain_id = $arr[0];
// 			$where[]='o.`ID` = "'.$domain_id .'"';
			$where[]='o.`Domain` = "'.$search['Domain'].'"';
		}
		if(!empty($search['Program'])){            //program搜索条件
			$query_program = 'SELECT ID FROM program WHERE Name ="'.$search['Program'].'"';

			$result = mysql_query($query_program);
			$arr = mysql_fetch_array($result);

			$program_id = $arr[0];
			if(isset($search['fakeProgram']))
				$program_id = $search['Program'];

			$where[]='b.`ProgramId` = "'.$program_id .'"';
		}
		if(!empty($search['IsFake'])){                //isfake搜索条件
			$where[]='a.`IsFake` = "'.$search['IsFake'].'"';
		}
		if(!empty($search['site'])){				//site搜索条件
			$where[]='a.`Site` = "'.$search['site'].'"';
		}

		$where[]='o.`Domain` NOT LIKE "%/%"';	// ignore / domain

		$where_str = empty($where)?"":join(' AND ', $where);
		$where_str = $where_str?' WHERE '.$where_str:'';      //看仔细了，是三目运算符




		$return_d = array();
		$sql = "SELECT COUNT(*) as count
				FROM domain AS o LEFT JOIN domain_outgoing_default_site  AS a ON o.ID = a.DID
				LEFT JOIN program_intell AS b ON a.PID = b.`ProgramId` $where_str";

//		$sql = 'SELECT COUNT(*) as count FROM domain';
		$count = mysql_query($sql);

		$count = mysql_fetch_array($count,MYSQL_ASSOC);

		$c_row['tp_count'] = $count['count'];

		$return_d['page_total'] = ceil($c_row['tp_count']/$page_size);
		$return_d['page_now'] = $page;
		$return_d['total_num'] = $c_row['tp_count'];

		/*
                $sql = 'SELECT a.DID,a.Key,a.Site,a.IsFake,b.CommissionValue,b.CommissionType,b.CommissionUsed,a.AddTime
                        FROM domain_outgoing_default_site  AS a
                        LEFT JOIN program_intell AS b ON a.PID = b.`ProgramId`
                        '.$where_str.'
                        ORDER BY `AddTime` DESC
                        LIMIT '.($page-1)*$page_size.','.$page_size;       //LEFT JOIN 关键字会从左表 (table_name1) 那里返回所有的行，即使在右表 (table_name2) 中没有匹配的行
        */

		$sql = "SELECT o.Domain,o.ID,a.Site,a.DID,a.IsFake,b.CommissionValue,b.CommissionType,b.CommissionUsed,a.AddTime
				FROM domain AS o LEFT JOIN domain_outgoing_default_other  AS a ON o.ID = a.DID
				LEFT JOIN program_intell AS b ON a.PID = b.`ProgramId` $where_str ORDER BY `AddTime` DESC
				LIMIT ".($page-1)*$page_size.','.$page_size;


		$row = $this->getRows($sql);
		foreach($row as $k=>$v){
			if(strpos($v['CommissionValue'], '|') !== false)
				list(,,$row[$k]['CommissionTxt']) = explode('|',$v['CommissionValue']);
			else
				$row[$k]['CommissionTxt'] = '-';

			//stats_domain data
			$sql = "SELECT * FROM domain_stats WHERE DomainId=".$v['ID']." AND Site='".$v['Site']."'";
			$r = $this->getRow($sql);
			$row[$k]['Sales7D'] = isset($r['Sales7D'])&&!empty($r['Sales7D'])?$r['Sales7D']:0;
			$row[$k]['Sales1M'] = isset($r['Sales1M'])&&!empty($r['Sales1M'])?$r['Sales1M']:0;
			$row[$k]['Sales3M'] = isset($r['Sales3M'])&&!empty($r['Sales3M'])?$r['Sales3M']:0;
			$row[$k]['Orders7D'] = isset($r['Orders7D'])&&!empty($r['Orders7D'])?$r['Orders7D']:0;
			$row[$k]['Orders1M'] = isset($r['Orders1M'])&&!empty($r['Orders1M'])?$r['Orders1M']:0;
			$row[$k]['Orders3M'] = isset($r['Orders3M'])&&!empty($r['Orders3M'])?$r['Orders3M']:0;
			$row[$k]['Revenue7D'] = isset($r['Revenue7D'])&&!empty($r['Revenue7D'])?$r['Revenue7D']:0.0000;
			$row[$k]['Revenue1M'] = isset($r['Revenue1M'])&&!empty($r['Revenue1M'])?$r['Revenue1M']:0.0000;
			$row[$k]['Revenue3M'] = isset($r['Revenue3M'])&&!empty($r['Revenue3M'])?$r['Revenue3M']:0.0000;
			$row[$k]['Clicks7D'] = isset($r['Clicks7D'])&&!empty($r['Clicks7D'])?$r['Clicks7D']:0;
			$row[$k]['Clicks1M'] = isset($r['Clicks1M'])&&!empty($r['Clicks1M'])?$r['Clicks1M']:0;
			$row[$k]['Clicks3M'] = isset($r['Clicks3M'])&&!empty($r['Clicks3M'])?$r['Clicks3M']:0;
		}
// 		echo "<pre>";
// 		print_r($row);
		$return_d['data'] = $row;
		return $return_d;
	}
	function getminestore($id){
		$sql = "select * from store_by_advertiser where StoreId=$id";
		return $this->getRows($sql);
	}
	function updatestore($val){
		$arr = json_decode($val);
		$key = '';
		$vals  = "";
		foreach($arr as $k=>$v){
			$key.= '`'.$k.'`="'.$v.'",';

		}
		$key  = rtrim($key,',');

		$sql = "update store_by_advertiser set($key)";
		echo $sql;
		die;
	}
	//添加url分析
	function innserturl($arr){
		$text = '';
		foreach($arr as $k=>$v){
			$url = $arr[$k]['Url'];
			$time = $arr[$k]['AddTime'];
			$AddUser = $arr[$k]['AddUser'];
			$Origin = $arr[$k]['Origin'];
			$Status = $arr[$k]['Status'];
			$domain = $arr[$k]['Domain'];
			$text.= '("'.$AddUser.'",'.'"'.$time.'",'.'"'.$Origin.'",'.'"'.$Status.'",'.'"'.$url.'",'.'"'.$domain.'"),';
		}
		$newtext = rtrim($text,',');
		$sql_insert = "INSERT INTO publisher_domain_info (AddUser,AddTime,Origin,Status,Url,Domain) VALUES $newtext";
		$res = $this->objMysql->query($sql_insert);
		if($res == 1){
			return 1;
		}else{
			return 0;
		}
	}
	//list
	function  urldata($para = array(), $page, $pagesize=30){
		//头部sql
		$sql_head = "SELECT ID,PublisherId,PublisherName,Url,AddUser,Addtime,Status,IsPassSubAff,IsPassAff FROM publisher_domain_info WHERE 1=1";

		//计算数据起始位
		$page_start = $pagesize * ($page - 1);
		//获取当前页数
		$info['page_now'] = $page;
		//删除当前页数参数
		if (isset($para['p'])) unset($para['p']);
		//搜索条件拼接
		$where = '';
		if(isset($para['status']) && $para['status'] != 'All'){
			$where.= " and Status = '{$para['status']}'";
		}
		if(isset($para['user']) && !empty($para['user'])){
			$where.= " and `AddUser` = '{$para['user']}'";
		}
		if(!empty($para['search'])){
			$search = trim($para['search']);
			$where.= " and Url like '%{$para['search']}%' or PublisherName like '%$search%'";
			$sql_tail = " ORDER BY ID Desc limit 1";

		}else{
			//尾部sql
			$sql_tail = " ORDER BY ID Desc limit $page_start,$pagesize";

		}
		//拼接获取list
		$sql = $sql_head . $where . $sql_tail;

		$info['data'] = $this->getRows($sql);
		if(empty($info['data'])){
			return $info['data'] = 'No Data';
		}
		foreach($info['data'] as $k=>$v){
			$info['data'][$k]['Number'] = $k+($page-1)*$pagesize+1;
		}
		$sqlcount = "SELECT count(*) FROM publisher_domain_info  where 1=1".$where;
		$total = $this->getRows($sqlcount);
		$info['page_total'] = ceil($total[0]['count(*)'] / $pagesize);
		return $info;
	}
	//detail
	function urldetaildata($para = array(), $page, $pagesize=30){
		$sql_head = "SELECT ExtDomain,COUNT(1) AS amount FROM publisher_domain_detail WHERE DomainInfoID = {$para['id']} ";
		$page_start = $pagesize * ($page - 1);
		$info['page_now'] = $page;
		if (isset($para['p']))
			unset($para['p']);
		$where = '';

		if(!empty($para['search'])){
			$where .= " and ExtDomain like '%{$para['search']}%'";
			$sql_tail = " GROUP BY ExtDomain ORDER BY amount DESC limit $page_start,$pagesize";

		}else{
			$sql_tail = " GROUP BY ExtDomain ORDER BY amount DESC limit $page_start,$pagesize";
		}
		$sql = $sql_head . $where . $sql_tail;
		//var_dump($sql);die;
		$info['data'] = $this->getRows($sql);
		if(empty($info['data'])){
			return $info['data'] = 'No Data';
		}
		foreach($info['data'] as $k=>$v){
			$info['data'][$k]['Number'] = $k+($page-1)*$pagesize+1;
		}
		$sqlcount = "SELECT count( DISTINCT ExtDomain) as `count` FROM publisher_domain_detail WHERE DomainInfoID = {$para['id']}".$where;
		$total = $this->getRows($sqlcount);
		$info['page_total'] = ceil($total[0]['count'] / $pagesize);
		return $info;
	}

	//merchants domain逻辑
	function get_domain_program_info($dids){
		//echo "<pre>";
		//print_r($dids);
		if(empty($dids))
			return array();

		$return_d = array();
		foreach($dids as $k=>$v){
			$dids[$k] = intval($v);
		}

		// get domian use current program

		$sql = 'SELECT o.ID,d.`DID`,d.Site,c.ProgramId AS ctrl_PID,c.Status AS ctrl_Status,p.ID AS PID,p.`AffId`,p.`Name` AS p_name,a.`Name` AS a_name FROM domain AS o LEFT JOIN `domain_outgoing_default_site` AS d ON o.ID = d.DID LEFT JOIN r_domain_program_ctrl AS c ON d.DID = c.DomainId LEFT JOIN program AS p ON d.PID = p.ID LEFT JOIN wf_aff AS a ON p.AffId = a.`ID` WHERE o.ID IN ('.join(',',$dids).')';

		$rows = $this->getRows($sql);
// 		echo "<pre>";
// 		print_r($rows);
		foreach($rows as $v){

			if(!empty($v['ctrl_PID'])&&$v['ctrl_Status']=="Active"){
				//如果ctrl表中有DID对应的PID，那么久把这条记录的ctrl_PID对应的name赋值给p_name
				$sql = 'SELECT p.Name FROM r_domain_program_ctrl AS c INNER JOIN program AS p ON p.ID = c.ProgramId WHERE c.ProgramId = "'.$v['ctrl_PID'].'"';
				$rows = $this->getRows($sql);
				$v['p_name'] = $rows[0]['Name'];
			}
			if(!empty($v['Site'])){
				$return_d['current'][$v['ID'].'_'.$v['Site']] = $v;
			}else{
				$return_d['current'][$v['ID'].'_NoAff'] = $v;
			}

		}
		//echo "<pre>";
		//print_r($return_d['current']);


		// get domain has relationship program
		$sql = 'SELECT d.`DID`,p.ID AS PID,p.`AffId`,p.`Name` AS p_name,a.`Name` AS a_name,i.`CommissionValue` FROM r_domain_program AS d LEFT JOIN program AS p ON d.PID = p.ID LEFT JOIN wf_aff AS a ON p.AffId = a.`ID` LEFT JOIN program_intell AS i ON p.`ID` = i.`ProgramId` WHERE a.Status = "Active" AND d.DID IN ('.join(',',$dids).')';
		$rows = $this->getRows($sql);

		foreach($rows as $v){
			if(!empty($v['CommissionValue'])){
				@list(,,$used) = explode('|',$v['CommissionValue']);
				$v['CommissionValue'] = $used;

			}

			$return_d['all'][$v['DID']][] = $v;
		}
		return $return_d;
	}


	//PDC逻辑
	function getPDCListPage($search,$page,$page_size=10){

		$where_str = '';
		if(isset($search['Domain']) && $search['Domain']){
		}else{
			$search['Domain']="";
		}
		if(isset($search['Program']) && $search['Program']){
		}else{
			$search['Program']="";
		}
		if(isset($search['Status']) && $search['Status']){
		}else{
			$search['Status']="";
		}



		$where=array();
		if(!empty($search['Domain'])){
			$query = 'SELECT ID FROM domain WHERE Domain ="'.$search['Domain'].'"';
			//echo $query;
			$result = mysql_query($query);
			$arr = mysql_fetch_array($result);
			$domain_id = $arr[0];
			$where[]='a.DomainId = "'.$domain_id .'"';
		}
		if(!empty($search['Program'])){
			$query = 'SELECT ID FROM program WHERE Name ="'.$search['Program'].'"';
			$result = mysql_query($query);
			$arr = mysql_fetch_array($result);

			$program_id = $arr[0];
			$where[]='a.ProgramId = "'.$program_id .'"';
		}
		if(!empty($search['Status'])){
			$where[]='a.Status = "'.$search['Status'].'"';
		}
		$where_str = empty($where)?"":join(' AND ', $where);

		$return_d = array();
		//$c_row = $this->table('r_domain_program_ctrl')->count()->where($where_str)->findone();

		$where_str = $where_str?' WHERE '.$where_str:'';      //看仔细了，是三目运算符

		$sql_total = "SELECT COUNT(*) as count from r_domain_program_ctrl a inner join domain b on a.DomainId = b.ID inner join program c  on a.ProgramId = c.ID $where_str";

		$count = mysql_query($sql_total);
		$count = mysql_fetch_array($count,MYSQL_ASSOC);

		$c_row['tp_count'] = $count['count'];

		$return_d['page_total'] = ceil($c_row['tp_count']/$page_size);
		$return_d['page_now'] = $page;
		$return_d['total_num'] = $c_row['tp_count'];





		$sql = "SELECT a.ID,a.DomainId,a.ProgramId,a.Status,a.AddUser,b.Domain,c.Name from r_domain_program_ctrl a inner join domain b on a.DomainId = b.ID inner join program c  on a.ProgramId = c.ID $where_str ORDER BY a.ID LIMIT ".($page-1)*$page_size.','.$page_size;



		$row = $this->getRows($sql);

		$return_d['data'] = $row;

		return $return_d;
	}



	function assoc_ID_IdInAff($arr) {//去除二维数组中，IdInAff和ID同时有重复值的记录，实现去重
		$tmp_ID_IdInAff = array();
		foreach($arr as $k => $v) {
			$tmp_str = $v["ID"].'_'.$v["IdInAff"];
			if(in_array($tmp_str, $tmp_ID_IdInAff)) {
				unset($arr[$k]);
			}else {
				$tmp_ID_IdInAff[] = $tmp_str;

			}
		}
		return $arr;
	}//assoc_title end


	public function getPDL($domain_id,$pro){
// 		echo '<pre>';
// 		print_r($pro);
		//根据domain_id查询domain
		$return_d = array();
		$rows = array();
		$country = addslashes($pro['site']);
		$sql = 'SELECT Domain FROM domain WHERE ID = "'.$domain_id.'"';
		$rows = $this->getRows($sql);
		$domain_name = $rows[0]['Domain'];
		$return_d['domain_name'] = $domain_name;



		//-----------------------------------------------------add操作-------------------------------------------------------------------------------------------
		if(isset($pro['add_program'])){
			// 				print_r($_POST);
			$pdlobj = new LibFactory();
			$domain_id = $pro['id'];
			$str = "";
			//检查ctrl表中是否有这样的d-p对
			$sql_ctrl = 'SELECT ProgramId FROM r_domain_program_ctrl WHERE ProgramId = "'.$pro['hidden_pid'].'" AND DomainId = "'.$domain_id.'" AND Country = "'.$country.'"';
			$rows_ctrl = $pdlobj->getRows($sql_ctrl);
			if(empty($rows_ctrl)&&!empty($pro['hidden_pid'])){

				//插入ctrl表

				//将domain_id对应的所有p-d组合全都设置为Inactive
				$sql_Inactive = 'UPDATE r_domain_program_ctrl SET Status = "Inactive" WHERE DomainId = "'.$domain_id.'" and Country = "'.$country.'"';
				mysql_query($sql_Inactive);
				$d = new DateTime();
				$timeNow = $d->format("Y-m-d H:i:s");

				if(isset($_SERVER['PHP_AUTH_USER'])){
					$user = $_SERVER['PHP_AUTH_USER'];
				}else{
					$user = 'test';
				}
				$table_ctrl = "r_domain_program_ctrl";
				$table_log = "r_domain_program_log";
				$add = array();
				$table_name = 'r_domain_program_ctrl';
				$add['DomainId'] = $domain_id;
				$add['ProgramId'] = $pro['hidden_pid'];
				$add['Status'] = "Active";
				$add['AddUser'] = $user;
				$add['AddTime'] = $timeNow;
				$add['LastUpdateTime'] = $timeNow;
				$add['Country'] = $country;
				update_add($table_name, $add);


				//插入log表

				//将domain_id对应的所有p-d组合全都设置为Inactive
				$sql_Inactive = 'UPDATE r_domain_program_log SET Status = "Inactive" WHERE DomainId = "'.$domain_id.'"';
				mysql_query($sql_Inactive);
				$log = array();
				$table_name = 'r_domain_program_log';
				$log['DomainId'] = $domain_id;
				$log['ProgramId'] = $pro['hidden_pid'];
				$log['Status'] = "Active";
				$log['AddUser'] = $user;
				$log['Remark'] = $pro['add_remark'];
				$log['LastUpdateTime'] = $timeNow;
				update_add($table_name, $log);
// 				echo "<pre>";
// 				print_r($log);
			}

		}


		//----------------------------------------------------如果点击了USE，更新或者插入ctrl表-------------------------------------------------------------
		if(isset($pro['id_now'])){
			if(isset($_SERVER['PHP_AUTH_USER'])){
				$user = $_SERVER['PHP_AUTH_USER'];
			}else{
				$user = 'test';
			}
			$d = new DateTime();
			$timeNow = $d->format("Y-m-d H:i:s");
			//插入log表
			$sql_log_Inactive = 'UPDATE r_domain_program_log SET Status = "Inactive"  WHERE DomainId = "'.$pro['id'].'" and Country = "'.$country.'"';
			$sql_log = 'INSERT INTO r_domain_program_log (DomainId,PID_from,PID_to,AddUser,Status,LastUpdateTime,Remark,Country) VALUES ("'.$pro['id'].'","'.$pro['id_last'].'","'.$pro['id_now'].'","'.$user.'","Active","'.$timeNow.'","'.$pro['remark'].'","'.$country.'")';
			mysql_query($sql_log_Inactive);
			mysql_query($sql_log);


			$rows_check = array();
			$sql_check ='SELECT ProgramId FROM r_domain_program_ctrl WHERE ProgramId= "'.$pro['id_now'].'" AND DomainId = "'.$pro['id'].'" and Country = "'.$country.'"';
			$rows_check = $this->getRows($sql_check);
			if(empty($rows_check)){    //当ctrl表中不存在此p-d对，先插入，后更新Active
				$sql_insert = 'INSERT INTO r_domain_program_ctrl (DomainId,ProgramId,Status,AddUser,LastUpdateTime,Country) VALUES ("'.$pro['id'].'","'.$pro['id_now'].'","Active","'.$user.'","'.$timeNow.'", "'.$country.'")';
				mysql_query($sql_insert);
				$sql_update = 'UPDATE r_domain_program_ctrl SET Status = (CASE WHEN ProgramId = "'.$pro['id_now'].'" THEN "Active" ELSE "Inactive" END) WHERE DomainId = "'.$pro['id'].'" and Country = "'.$country.'"';//添加之后，domain_id对应的其他program变成Inactive
				mysql_query($sql_update);
			}else{  //当ctrl表中已经有此p-d对，设置它为active，其他为inactive
				$sql_Active = 'UPDATE r_domain_program_ctrl SET Status = "Active" , LastUpdateTime = "'.$timeNow.'" WHERE DomainId = "'.$pro['id'].'" AND ProgramId = "'.$pro['id_now'].'" and Country = "'.$country.'"';
				//echo "<br>";
				$sql_Inactive = 'UPDATE r_domain_program_ctrl SET Status = "Inactive" WHERE DomainId = "'.$pro['id'].'" AND ProgramId <> "'.$pro['id_now'].'" and Country = "'.$country.'"';

				mysql_query($sql_Active);
				mysql_query($sql_Inactive);

			}
		}
		$sql = 'SELECT ProgramId from r_domain_program_ctrl  WHERE DomainId = "'.$domain_id.'" AND Status = "Active" AND (Country = "'.$country.'"  OR Country = "")';
		$result = mysql_query($sql);
		$arr = mysql_fetch_array($result,MYSQL_ASSOC);
		if(!empty($arr)){
			$program_id = $arr['ProgramId'];
//			echo "走ctrl表";
		}else{
			$sql = 'SELECT PID from domain_outgoing_default_site  WHERE DID = "'.$domain_id.'" AND Site = "'.$pro['site'].'"';
			$result = mysql_query($sql);
			$arr = mysql_fetch_array($result,MYSQL_ASSOC);
			$program_id = $arr['PID'];
//			echo "走default表";
		}
//		echo $program_id."<br/>";

		//-------------------------------------------根据program_id得到affid、commissionvalue的第三个值-------------------------------------

		$sql = 'SELECT a.ID , a.Name , b.AffId , b.CommissionValue from program a inner join program_intell b on a.ID = b.ProgramId WHERE a.ID = "'.$program_id.'"';
		$result = mysql_query($sql);
		$arr = mysql_fetch_array($result,MYSQL_ASSOC);
		$sql_aff = 'SELECT Name FROM wf_aff WHERE ID = "'.$arr['AffId'].'"';//通过affId查询affName
		$aff = $this->getRows($sql_aff);
		$arr['AffName'] = $aff[0]['Name'];
		//echo $sql."<br/>";
//		echo "program_related:<pre>";
//		print_r($arr);
		$comval = $arr['CommissionValue'];
		$comvalarr = explode('|', $comval);
		if(!empty($comvalarr[2])){
			$commission = $comvalarr[2];
		}else{
			$commission = "";
		}
		$arr['CommissionValue'] = $commission;
		$program_related = $arr;
		$return_d['program_related'] = $program_related;


		//------------------------------------------log表中相关domain_id对应的所有program---------------------------------------------------
		$sql = 'SELECT PID_from,PID_to,LastUpdateTime,Remark,Status,Country from r_domain_program_log WHERE DomainId = "'.$domain_id.'" AND (Country = "'.$country.'" OR Country = "") ORDER BY LastUpdateTime DESC';
		$history = array();
		$history = $this->getRows($sql);
		$PID_from = array();
		$PID_to = array();
		foreach ($history as $k=>$v){
			$PID_from[] = $v['PID_from'];
			$PID_to[] = $v['PID_to'];
		}
		$str_from = '("'.implode('","', $PID_from).'")';
		$str_to = '("'.implode('","', $PID_to).'")';
		$sql_from = 'SELECT p.Name,p.ID,w.Name AS AffName,i.CommissionValue,i.IdInAff,l.LastUpdateTime,l.Status,l.Remark from r_domain_program_log as l LEFT JOIN program as p ON l.PID_from = p.ID LEFT JOIN program_intell AS i ON i.ProgramId = l.PID_from LEFT JOIN wf_aff AS w ON i.AffId = w.ID WHERE l.DomainId = "'.$domain_id.'" AND l.PID_from IN '.$str_from;
		$history_from = $this->getRows($sql_from);
		$sql_to = 'SELECT p.Name,p.ID,w.Name AS AffName,i.CommissionValue,i.IdInAff,l.LastUpdateTime,l.Status,l.Remark from r_domain_program_log as l LEFT JOIN program as p ON l.PID_to = p.ID LEFT JOIN program_intell AS i ON i.ProgramId = l.PID_to LEFT JOIN wf_aff AS w ON i.AffId = w.ID WHERE l.DomainId = "'.$domain_id.'" AND l.PID_to IN '.$str_to;
		$history_to = $this->getRows($sql_to);
		function commission(&$history){
			foreach ($history as &$val){
				$comval = $val['CommissionValue'];
				$temparr = explode("|", $comval);
				if(!empty($temparr[2])){
					$commission = $temparr[2];
				}else{
					$commission = "";
				}
				$val['CommissionValue'] = $commission;
			}
		}
		commission($history_from);$from = array();
		commission($history_to);$to = array();
		foreach ($history_from as $k=>$v){
			$from[$v['ID']] = $v;
		}
		foreach ($history_to as $k=>$v){
			$to[$v['ID']] = $v;
		}
// 		echo "<pre>";
// 		print_r($history);
		$return_d['history'] = $history;
		$return_d['from'] = $from;
		$return_d['to'] = $to;
		//-----------------------------------------r_domain_program表中domain_id对应的所有program-------------------------------------------
		$sql = 'SELECT p.Name,p.ID,i.IdInAff,w.Name AS AffName,i.CommissionValue,i.IsActive,d.IsFake,d.LastUpdateTime FROM r_domain_program AS d INNER JOIN program AS p ON p.ID = d.PID  INNER JOIN program_intell AS i ON i.ProgramId = p.ID INNER JOIN wf_aff AS w ON i.AffId = w.ID WHERE d.DID = "'.$domain_id.'" and d.status = "active"';
		//$sql .= ' UNION SELECT p.Name,p.ID,i.IdInAff,w.Name AS AffName,i.CommissionValue,i.IsActive,d.LastUpdateTime FROM domain_outgoing_default_site AS d INNER JOIN program AS p ON p.ID = d.PID  INNER JOIN program_intell AS i ON i.ProgramId = p.ID INNER JOIN wf_aff AS w ON i.AffId = w.ID WHERE d.DID = "'.$domain_id.'" AND d.Site = "'.$pro['site'].'" ORDER BY 6';
//  	  	echo $sql;
		$relation = array();
		$relation = $this->getRows($sql);
// 		echo "<pre>";
// 		print_r($relation);

//将ID_IdInAff相同的记录去重
		$relation = $this->assoc_ID_IdInAff($relation);
// 		echo "<pre>";
// 		print_r($relation);
		foreach ($relation as &$val){
			$comval = $val['CommissionValue'];
			$temp= explode("|", $comval);
			if(!empty($temp[2])){
				$commission = $temp[2];
			}else{
				$commission = "";
			}
			$val['CommissionValue'] = $commission;
		}


		$return_d['relation'] = $relation;
//		echo "<pre>";
//		print_r($relation);
		return $return_d;
	}
	function getstoreadv($search, $page, $page_size)
	{
		$where_str_store = '';
		$dir = $search['order'];
		$oname = "b.".$search['oname'];

		if (isset($search['store_keywords']) && $search['store_keywords']){
			$where_str_store.= ' AND (b.Name LIKE "%' . addslashes(trim($search['store_keywords'])) . '%" OR e.Name LIKE "%'. addslashes(trim($search['store_keywords'])) .'%")';
		}
		if (isset($search['status']) && $search['status']){
			$status = $search['status'];
			$where_str_store.= " AND b.SupportType = '$status'";
		}
		if (isset($search['catestu']) && $search['catestu']){
			$status = $search['catestu'];
			if($status == 'YES'){
				$where_str_store.= " AND b.CategoryId IS NOT NULL AND b.CategoryId != ''";
			}elseif($status == 'NO'){
				$where_str_store.= " AND b.CategoryId IS NULL OR b.CategoryId = ''";
			}

		}
		if (isset($search['ppc']) && $search['ppc']){
			$ppc = $search['ppc'];
			$where_str_store.= " AND b.PPC = '$ppc'";
		}
		if(isset($search['categories']) && $search['categories']){
			$categoryArr = explode(',',trim($search['categories'],','));
			if(!empty($categoryArr))
			{
				$where_str_store .= " AND(";
				foreach($categoryArr as $cateid)
				{
					$where_str_store .= " FIND_IN_SET('$cateid',b.CategoryId) OR";
				}
				$where_str_store = rtrim($where_str_store,'OR').")";
			}
		}
		if (isset($search['country']) && $search['country'])
			$where_str_store.= ' AND f.site = "' . $search['country'] . '"';

		$return_d = array();
		$sql = "SELECT COUNT(*) FROM store_by_advertiser AS a LEFT JOIN store AS b ON a.StoreId = b.`Name`  $where_str_store";
		$return_d['count'] = current($this->getRow($sql));
		$sql = "SELECT a.*,b.`Name` FROM store_by_advertiser AS a LEFT JOIN store AS b ON a.StoreId = b.`Name`  LIMIT  $page,$page_size";
		$return_d['data'] = $this->getRows($sql);
		return $return_d;
	}
	function getStoreListPage($search, $page, $page_size)
	{
		$where_str_store = '';
		$where2='';
		$where3='';
		$where4='';
		$dir = isset($search['order'])?$search['order']:'';
		$oname = isset($search['oname'])?"b.".$search['oname']:' ID ';
		if (isset($search['stime']) && !empty($search['stime']) && isset($search['etime']) && !empty($search['etime'])){
			$type = 2;
		}else{
			$type = 1;
		}
		if(isset($search['store_keywords']) && !empty($search['store_keywords'])){
			$key = addslashes(trim($search['store_keywords']));
			$where_str_store.= " AND( b.Name like '%$key%' OR b.NameOptimized like '%$key%' OR b.Domains like '%$key%')";
			$strlen = "ORDER BY LENGTH(b.`Name`),LENGTH(b.`NameOptimized`),LENGTH(b.`Domains`),";
			if($type == 2){
				$sql = "SELECT DISTINCT a.`DomainId` AS did FROM r_store_domain a LEFT JOIN `store` b ON a.`StoreId` = b.`ID` WHERE b.`Name` LIKE '%$key%' OR b.`NameOptimized` LIKE '%$key%'";
				$row = $this->getRows($sql);
				if(!empty($row)){
					$did = '';
					foreach($row as $k){
						!empty($k['did']) && $did.=$k['did'].',';
					}
					$did = rtrim($did,',');
					$where2.=" and a.domainid in($did)";
				}
			}
		}else{
			$strlen = "ORDER BY";
		}
		if (isset($search['status']) && !empty($search['status'])){
			$status = trim($search['status']);
			$where_str_store.= " AND b.SupportType = '$status'";
			$where4.= " AND a.SupportType = '$status'";
		}
		if (isset($search['catestu']) && !empty($search['catestu'])){
			$status = $search['catestu'];
			if($status == 'YES'){
				$where_str_store.= " AND b.CategoryId IS NOT NULL AND b.CategoryId != ''";
			}elseif($status == 'NO'){
				$where_str_store.= " AND (b.CategoryId IS NULL OR b.CategoryId = '')";
			}
		}
		if (isset($search['ppc']) && !empty($search['ppc'])){
			/*if($search['ppc'] == 'none'){
				$ppc = 0;
			}else{
				$ppc = $search['ppc'];
			}*/
		    $ppc = $search['ppc'];
		    $where_str_store.= " AND b.PPCStatus = '$ppc'";
		    $where4.= " AND a.PPCStatus = '$ppc'";
		}
		if(isset($search['categories']) && !empty($search['categories'])){
			$categoryArr = explode(',',trim($search['categories'],','));
			if(!empty($categoryArr))
			{
				$where_str_store .= " AND(";
				$where4 .= " AND(";
				foreach($categoryArr as $cateid)
				{
					$where_str_store .= " FIND_IN_SET('$cateid',b.CategoryId) OR";
					$where4.= " FIND_IN_SET('$cateid',a.CategoryId) OR";
				}
				$where_str_store = rtrim($where_str_store,'OR').")";
				$where4 = rtrim($where4,'OR').")";
			}
		}
		if (isset($search['aname']) && !empty($search['aname'])){
			if($search['aname'] == '1'){
				$where_str_store.= " AND b.NameOptimized != ''";
			}elseif($search['aname'] == '2'){
				$where_str_store.= " AND ( b.NameOptimized IS NULL OR b.NameOptimized = '') ";
			}
		}
		if(isset($search['cooperation']) && !empty($search['cooperation'])){
			if($search['cooperation'] == '1'){
				$where_str_store.= " AND b.StoreAffSupport = 'Yes'";
				$where3.=" and b.StoreAffSupport = 'Yes'";
			}elseif($search['cooperation'] == '2'){
				$where_str_store.= " AND b.StoreAffSupport = 'No'";
				$where3.=" and b.StoreAffSupport!= 'No'";
			}
		}
		if(isset($search['logo']) && !empty($search['logo'])){
			if($search['logo'] == '1'){
				$where_str_store.= " AND b.LogoName like '%,%'";
			}elseif($search['logo'] == '2'){
				$where_str_store.= " AND (b.LogoName = '' OR b.LogoName IS NULL)";
			}
		}
		if (isset($search['country']) && !empty($search['country'])){
			$str = ' a.country in(';
			$where_str_store .= " AND(";
			$where4 .= " AND(";
            foreach($search['country'] as $c){
				if(!empty($c)){
					$where_str_store.= ' FIND_IN_SET("'.$c.'",b.CountryCode) OR';
					if($c=='UK'){
						$str.="'uk','gb',";
						$where4.= ' FIND_IN_SET("uk",c.ShippingCountry) OR FIND_IN_SET("gb",c.ShippingCountry) OR';
						continue;
					}
					$where4.= ' FIND_IN_SET("'.$c.'",a.CountryCode) OR';
					$str.="'".strtolower($c)."',";
				}
			}
			$where_str_store = rtrim($where_str_store,'OR').")";
			$where4 = rtrim($where4,'OR').")";
			$where2.= ' and '.rtrim($str,',').")";
		}
		if (isset($search['networkid']) && !empty($search['networkid'])){
			$str = ' a.affid in(';
			$str1 = ' c.affid in(';
			$where_str_store .= " AND(";
			foreach($search['networkid'] as $c){
				if(!empty($c)) {
					$where_str_store .= ' FIND_IN_SET("'.$c.'",b.Affids) OR';
					$str.= $c.',';
					$str1.= $c.',';
				}
			}
			$where_str_store = rtrim($where_str_store,'OR').")";
			$where2.= ' and '.rtrim($str,',').")";
			$where4.= ' and '.rtrim($str1,',').")";
		}
		if (isset($search['storeid']) && !empty($search['storeid'])){
			$where_str_store.= ' AND b.ID IN ('.join(',',$search['storeid']).')';
		}
		if (isset($search['datatype']) && !empty($search['datatype'])){
			$datatype = $search['datatype'];
			if($datatype == 1){
				$key1= 'IFNULL(b.PClicks,0) as clicks,IFNULL(b.Commission_publisher,0) as commission ,IFNULL(b.Sales_publisher,0) as sales,IFNULL(b.PClicks_robot,0) as rob,IFNULL(b.PClicks_robot_p,0) as robp';
				$key = 'SUM(b.PClicks_robot) as rob,SUM(b.PClicks_robot_p) as robp,SUM(b.PClicks) as clicks,SUM(b.Commission_publisher) as revenues ,SUM(b.Sales_publisher) as sales';
				if($type == 2){
					$sql = "SELECT b.`ApiKey` FROM publisher AS a LEFT JOIN publisher_account AS b ON a.`ID` = b.`PublisherId` WHERE a.Tax = 0 AND b.ApiKey IS NOT NULL";
					$res = $this->getRows($sql);
					if(!empty($res)){
						$keyid=' and a.site NOT IN(';
						foreach($res as $k){
							$keyid.='"'.$k['ApiKey'].'",';
						}
						$where2.= rtrim($keyid,',').")";
					}
				}
			}else{
				$key1= 'IFNULL(b.clicks,0) as clicks,IFNULL(b.commission,0) as commission ,IFNULL(b.sales,0) as sales,IFNULL(b.Clicks_robot,0) as rob,IFNULL(b.Clicks_robot_p,0) as robp';
				$key = 'SUM(b.Clicks_robot) as rob,SUM(b.Clicks_robot_p) as robp,SUM(b.clicks) as clicks,SUM(b.commission) as revenues ,SUM(b.sales) as sales';
			}
		}


		if (isset($search['coupon_policy']) && !empty($search['coupon_policy'])){
            $coupon_policy_arr = explode(',', $search['coupon_policy']);
            if (in_array('Exclusive Code', $coupon_policy_arr)) {
                $where_str_store .= " AND b.Exclusive_Code='YES'";
            }
            if (in_array('CPA Increase', $coupon_policy_arr)) {
                $where_str_store .= " AND b.CPA_Increase='YES'";
            }
            if (in_array('Allow Inaccurate Promo', $coupon_policy_arr)) {
                $where_str_store .= " AND b.Allow_Inaccurate_Promo='YES'";
            }
            if (in_array('Allow to Change Promotion Title/Description', $coupon_policy_arr)) {
                $where_str_store .= " AND b.Allow_to_Change_Promotion_TitleOrDescription='YES'";
            }
        }


		$return_d = array();
		$sql = "SELECT COUNT(*) FROM store b WHERE 1=1 ".$where_str_store;
		$return_d['count'] = current($this->getRow($sql));
<<<<<<< .mine
	    $sql = "SELECT b.`ID` AS StoreId,IF(b.NameOptimized='' OR b.NameOptimized IS NULL,b.Name,b.NameOptimized) AS storeName,b.StoreAffSupport,b.LogoStatus,b.LogoName,b.CategoryId,b.LogoName,b.`SupportType`,b.Domains,b.`PPC`,b.`PPCStatus`,$key1,b.Description,b.Exclusive_Code,b.CPA_Increase,b.Allow_Inaccurate_Promo,b.Promo_Code_has_been_blacklisted,b.Word_has_been_blacklisted,b.Coupon_Policy_Others,b.Allow_to_Change_Promotion_TitleOrDescription FROM store b WHERE 1=1 $where_str_store $strlen $oname $dir LIMIT  $page,$page_size";
||||||| .r75603
	    $sql = "SELECT b.`ID` AS StoreId,IF(b.NameOptimized='' OR b.NameOptimized IS NULL,b.Name,b.NameOptimized) AS storeName,b.StoreAffSupport,b.LogoStatus,b.LogoName,b.CategoryId,b.LogoName,b.`SupportType`,b.Domains,b.`PPC`,b.`PPCStatus`,$key1,b.Description FROM store b WHERE 1=1 $where_str_store $strlen $oname $dir LIMIT  $page,$page_size";
=======
		$sql = "SELECT b.`ID` AS StoreId,IF(b.NameOptimized='' OR b.NameOptimized IS NULL,b.Name,b.NameOptimized) AS storeName,b.StoreAffSupport,b.LogoStatus,b.LogoName,b.CategoryId,b.LogoName,b.`SupportType`,b.Domains,b.`PPC`,b.`PPCStatus`,$key1,b.Description FROM store b WHERE 1=1 $where_str_store $strlen $oname $dir LIMIT  $page,$page_size";
>>>>>>> .r75689
		$data  = $this->getRows($sql);
		if($type == 2){
			//calculation 统计类
			$calObj = new Calculation();
			switch ($search['datatype']) {
				case '1' :
					$calObj->dataType = 'publisher';
					break;
				case '2' :
					$calObj->dataType = 'all';
					break;
			}
			$calObj->startDate = $search['stime'];
			$calObj->endDate = $search['etime'];
			$calObj->network = $search['networkid'];
			$calObj->advertiserKeyword = $search['store_keywords'];
			$calObj->country = $search['country'];
			$calObj->advertiserPPCStatus = $search['ppc'];
			$calObj->advertiserStatus = $search['status'];
			$calObj->advertiserCategoryStatus = $search['catestu'];
			$calObj->advertiserLogoStatus = $search['logo'];
			$calObj->advertiserNameStatus = $search['aname'];
			$calObj->advertiserCategory = $search['categories'];
			$calObj->advertiserCooperationStatus = $search['cooperation'];
			$info = $calObj->calTransactionTotal();
			$info['revenues'] = $info['commission'];
			$sql = "select count(1) as count from(select count(1) from store AS a LEFT JOIN `store_program_history` AS b ON a.`ID` = b.`StoreId` LEFT JOIN `program_intell` AS c ON b.programId = c.`ProgramId` where b.startdate<'{$search['etime']}' and b.enddate >='{$search['stime']}' and a.`Name` NOT IN('commissionfactory','cfjump','cj','jdoqocy','anrdoezrs','kqzyfj','tqlkg','dpbolvw','tkqlhce','qksrv','buy','shareasale','affilired','linksynergy','linkshare','rakuten','affiliatefuture','tradedoubler','hasoffers','go2cloud','pepperjam','pepperjamnetwork','pjtra','pjatr','pntra','pntrs','pntrac','gopjn','affiliatewindow','awin1','linkconnector','blackforestdecor','alltapestry','aspenlighting','lonestarwesterndecor','camotrading','webgains','mycommerce','zanox','zanox-affiliate','silvertap','affiliatemarketing','affili','successfultogether','apdperformance','dgm-au','apdgroup','paidonresults','onenetworkdirect','digitalriver','avangate','clickbank','clixgalore','clixGalore','stream20','theaffiliategateway','tagserve','shareresults','musicademy','regnow','tradetracker','marketleverage','customermonthly','systemsupdated','exchangeadded','automatedisplay','directleads','optimisemedia','omguk','omgt1','impactradius','7eer','evyy','ojrq','maxcdn','hddn','market-ace','commissionmonster','cmjump','webmasterplan','effiliation','affutd','myhelphub','monetizeit','filitrac','hercle','blueglobalmedia','bgmtracker','matomymarket','matomy','adsmarket','targetctracker','omnicomgroup','omnicomaffiliates','latitudegroup','incomeaccess','gentingaffiliates','tenthousandhours','gogetoffers','belboon','dgm-nz','flexoffers','linkoffers','flexlinks','omgt3','vcommission','vcaa','vcma','vcmr','cpadna','dedicatednetworks','affilisearch','go2jump','offerfactory','admobix','adcanadian','performancehorizon','prf','paydot','viglink','spartasnet','sntrax','infopay','selfdevelopment','jackmedia','czhub','cztrk','skimlinks','simpl','arthsalutions','affiliate-advantage','af-ad','wigify','wig','redirecting2','shoogloonetwork','trootrac','ibibo','komli','quickthinkmedia','moreniche','markethealth','lnk123','adcell','refersion','zoobax','altrk','24-ads','visualsoft','acpromotion','callmarketplace','dgperform','s2d6','digistore24','medialead','salead','adcocktail','ringpartner','cozypartners','reactivpub','reactivpub-track68','reactivpub-track72','mediaffiliation','reussissonsensemble','publicideas','publicidees','nxus','mopubi','tmoki','netaffiliation','metaffiliation','shopstylers','daisycon','ds1','dt51','at19','lt45','casaneo','smart4ads','annnetwork','glopss','adsplay','payoom','digitalsamadhan','shoppingmantra','shoogloo','trackkin','yieldkit','srvtrck','aflite','cpaprosperity','cpaprohits','cpaptrk','eaffiliatez','admitad','alitems','modato','lenkmio','chinesean','affilae','involve','invol','appoddo','apdtrk','slice','roeye','kelkoo','go2pixel','signitechnetwork') $where4 GROUP BY a.`ID`)aa";
			$dcount = $this->getRow($sql);
			$return_d['clicks'] = $info['click'];
			$return_d['dcount'] = $dcount['count'];
		}else{
			$sql = "SELECT $key FROM store b WHERE 1=1 ".$where_str_store;
			$info = $this->getRow($sql);
			$return_d['clicks'] = $info['clicks'];
		}
		//搜索时查找别的store
		if (isset($search['store_keywords']) && !empty($search['store_keywords'])){
			$where_arr = array();
			$where_arr[] = "c.Keywords like '".addslashes($search['store_keywords'])."%'";
			if (isset($search['country']) && !empty($search['country']))
				$where_arr[] = "b.site = '" . $search['country'] . "'";

			if (isset($search['categories']) && !empty($search['categories'])) {
				$category = trim($search['categories'], ',');
				$where_arr[] = "c.CategoryId IN ($category)";
			}
			$where_str = empty($where_arr) ? '' : ' WHERE ' . join(' AND ', $where_arr);
			$sql = "SELECT c.StoreId,c.Keywords,c.StoreName FROM store_multi_brand AS c LEFT JOIN r_store_domain AS a ON c.`StoreId` = a.`StoreId` LEFT JOIN domain_outgoing_default_other AS b ON a.`DomainId` = b.`DID` $where_str GROUP BY c.StoreId,c.Keywords";
			$rows_multi = $this->getRows($sql);
			if(!empty($rows_multi)){
				$multi_data = array();
				$sort_name = array();
				foreach ($rows_multi as $k => $v) {
					$Keywords = strtolower($v['Keywords']);
					if (!isset($multi_data[$Keywords])) {
						$sort_name[] = $Keywords;
						$multi_data[$Keywords]['storeName'] = $Keywords;
						$multi_data[$Keywords]['Store'][] = array('StoreName' => $v['StoreName'], 'StoreId' => $v['StoreId']);
						$multi_data[$Keywords]['Type'] = 'multi';
					} else {
						$multi_data[$Keywords]['Store'][] = array('StoreName' => $v['StoreName'], 'StoreId' => $v['StoreId']);
					}
				}
				$return_d['store'] = array_values($multi_data);
			}
		}
		//找总共有多少条content feed
		if(!empty($data)){
			$storeIdList = array();
			foreach ($data as &$v){
				$storeIdList[] = $v['StoreId'];
			}
			$storeIds = implode($storeIdList, ',');
			if($type == 2){
				//clicks
				$sql = "SELECT SUM(a.clicks) as clicks,SUM(a.clicks_robot) as rob,SUM(a.clicks_robot_p) as robp,b.`StoreId` FROM `statis_br` AS a LEFT JOIN r_store_domain AS b ON a.domainId = b.`DomainId` where a.CreatedDate>='{$search['stime']}' and a.CreatedDate<='{$search['etime']}' $where2 AND b.StoreId in($storeIds) group BY b.StoreId";
				$trares = $this->objMysql->getRows($sql,'StoreId');
				//commission
				$sqlcom = "Select SUM(Sales) AS sales,SUM(Commission) AS commission,b.`StoreId` FROM `rpt_transaction_unique` AS a LEFT JOIN r_store_domain AS b ON a.`domainId` = b.`DomainId` where a.CreatedDate>='{$search['stime']}' and a.CreatedDate<='{$search['etime']}' $where2 AND b.StoreId in($storeIds) group BY b.StoreId";
				$comres = $this->objMysql->getRows($sqlcom,'StoreId');
				foreach($data as &$v){
					if(isset($comres[$v['StoreId']])){
						$v['sales'] = $comres[$v['StoreId']]['sales'];
						$v['commission'] = $comres[$v['StoreId']]['commission'];
					}else{
						$v['sales'] = 0;
						$v['commission'] = 0;
					}
					if(isset($trares[$v['StoreId']])){
						$v['clicks'] = $trares[$v['StoreId']]['clicks'];
						$v['rob'] = $trares[$v['StoreId']]['rob'];
						$v['robp'] = $trares[$v['StoreId']]['robp'];
					}else{
						$v['clicks'] = 0;
						$v['rob'] = 0;
						$v['robp'] = 0;
					}
				}
			}
			$merchant = new MerchantExt();
			$storeCount = $merchant->GetContentNew(array("storeIds"=>$storeIds),1,1,false,true);
			$country = '';
			$country_where = array();
			if (isset($search['country']) && !empty($search['country'])){
				foreach($search['country'] as $val){
					if(!empty($val)){
						$country_where[] = "FIND_IN_SET('$val', d.ShippingCountry)";
					}
				}
			}
			if(!empty($country_where)){
				$country = ' AND ('.join(' OR ',$country_where).')';
			}
			//取出Commission范围
			$sql = 'SELECT rsp.`StoreId`,rsp.`ProgramId`,rsp.`Outbound`,b.`CommissionType`,b.`CommissionUsed`,b.`CommissionCurrency`,b.`CommissionValue` from r_store_program rsp
                 LEFT JOIN program_intell b on b.`ProgramId` = rsp.`ProgramId` WHERE rsp.`Outbound` != "" and rsp.`StoreId` in ('.$storeIds.')';
			$rs =  $this->objMysql->getRows($sql);

			$commissionRangeArr = array();
			foreach ($rs as $val){
				if($val['CommissionValue'] != '' && $val['CommissionValue'] != null){
					$commissionArr = explode("|", $val['CommissionValue'])[0];
					$commissionValText = trim($commissionArr,"[]");
					$commissionValArr = explode(",", $commissionValText);
					foreach ($commissionValArr as $temp){
						preg_match("/\d+(\.\d+)?/", $temp,$number);
						$unit = preg_replace("/[0-9. ]/",'', $temp);
						$commissionRangeArr[$val['StoreId']][$unit][number_format($number[0],3)] = $temp;
					}
				}else {
					if($val['CommissionUsed'] == '0'){
//                         $commissionRangeArr[$val['StoreId']]['value'] = 'other';
					}else if($val['CommissionType'] == 'Value'){
						if($val['CommissionCurrency'] != ''){
							$commissionRangeArr[$val['StoreId']][$val['CommissionCurrency']][number_format($val['CommissionUsed'],3)] = $val['CommissionCurrency'].$val['CommissionUsed'];
						}else{
							$commissionRangeArr[$val['StoreId']]['USD'][number_format($val['CommissionUsed'],3)] = "USD".$val['CommissionUsed'];
						}
					}else{
						$commissionRangeArr[$val['StoreId']]['%'][number_format($val['CommissionUsed'],3)] = $val['CommissionUsed'].'%';
					}
				}
			}
			foreach ($data as &$val){
				if(isset($commissionRangeArr[$val['StoreId']])){
					$val['rate'] = '';
					foreach ($commissionRangeArr[$val['StoreId']] as $tempK=>$tempV){
						ksort($tempV);
						if(count($tempV)<=1){
							$val['rate'] .= trim(current($tempV)).',';
						}else {
							$val['rate'] .= trim(current($tempV)).' ~ '.trim(end($tempV)).',';
						}
					}
					if($val['rate'] != ''){
						$val['rate'] = trim($val['rate'],',');
					}
					else {
						$val['rate'] = 'other';
					}
				}else{
					$val['rate'] = '/';
				}
				if(isset($storeCount[$val['StoreId']]['StoreIdCount'])){
					$val['StoreCount'] = $storeCount[$val['StoreId']]['StoreIdCount'];
				}else {
					$val['StoreCount'] = 0;
				}
			}
		}
		$return_d['data'] = $data;
		$return_d['revenues'] = $info['revenues'];
		$return_d['sales'] = $info['sales'];
		$return_d['rob'] = $info['rob'];
		$return_d['robp'] = $info['robp'];
		return $return_d;
	}
	function getStoreLogo($search, $page, $page_size)
	{
		$where_str_store = '';
		$dir = $search['order'];
		$oname = "b.".$search['oname'];

		if (isset($search['store_keywords']) && $search['store_keywords']){
			$where_str_store.= ' AND (b.Name LIKE "%' . addslashes(trim($search['store_keywords'])) . '%" OR e.Name LIKE "%'. addslashes(trim($search['store_keywords'])) .'%")';
		}
		if (isset($search['status']) && $search['status']){
			$status = $search['status'];
			$where_str_store.= " AND b.SupportType = '$status'";
		}
		if (isset($search['catestu']) && $search['catestu']){
			$status = $search['catestu'];
			if($status == 'YES'){
				$where_str_store.= " AND b.CategoryId IS NOT NULL AND b.CategoryId != ''";
			}elseif($status == 'NO'){
				$where_str_store.= " AND b.CategoryId IS NULL OR b.CategoryId = ''";
			}
		}
		if (isset($search['ppc']) && $search['ppc']){
			$ppc = $search['ppc'];
			$where_str_store.= " AND b.PPC = '$ppc'";
		}
		if(isset($search['categories']) && $search['categories']){
			$categoryArr = explode(',',trim($search['categories'],','));
			if(!empty($categoryArr))
			{
				$where_str_store .= " AND(";
				foreach($categoryArr as $cateid)
				{
					$where_str_store .= " FIND_IN_SET('$cateid',b.CategoryId) OR";
				}
				$where_str_store = rtrim($where_str_store,'OR').")";
			}
		}
		if (isset($search['country']) && $search['country'])
			$where_str_store.= ' AND f.site = "' . $search['country'] . '"';

		$return_d = array();
		$sql = "SELECT COUNT(DISTINCT c.`StoreId`) FROM store b INNER JOIN r_store_domain c ON c.`StoreId` = b.`ID` INNER JOIN r_domain_program d ON d.`DID` = c.`DomainId`INNER JOIN program e ON e.`ID` = d.`PID` INNER JOIN domain_outgoing_default_other f ON f.`DID` = c.DomainId WHERE d.Status = 'Active' AND  e.`Name` IS NOT NULL $where_str_store;";
		$return_d['count'] = current($this->getRow($sql));
		$sql = "SELECT b.`ID` AS StoreId,b.`Name` AS storeName,b.LogoName,b.CategoryId,e.`Homepage`,b.LogoName,b.`SupportType`,b.`PPC`,IFNULL(b.clicks,0) as clicks,IFNULL(b.commission,0) as revenues  FROM store b  INNER JOIN r_store_domain c ON c.`StoreId`=b.`ID` LEFT JOIN r_domain_program d ON d.`DID`=c.`DomainId` LEFT JOIN program e ON e.`ID`=d.`PID` INNER JOIN domain_outgoing_default_other f ON f.`DID` = c.DomainId WHERE d.Status = 'Active' AND e.`Name` IS NOT NULL $where_str_store GROUP BY b.`ID` ORDER BY $oname $dir LIMIT  $page,$page_size";
		$return_d['data'] = $this->getRows($sql);
		return $return_d;
	}

	function showAdvertiserDomainList($search){
		$sql = "select CountryCode,CountryName from country_codes";
		$country = $this->objMysql->getRows($sql,'CountryCode');
		$country['UK']['CountryName'] ='United Kingdom';
		$country['UK']['CountryCode'] ='UK';
		$country['GLOBAL']['CountryName'] ='Global';
		$country['GLOBAL']['CountryCode'] ='Global';
		$sql = 'SELECT
				  e.`Name` AS AffName,
				  d.`Name` AS ProgramName,
				  m.CommissionType AS mtype,
				  m.`CommissionUsed` AS muserd,
				  m.CommissionCurrency AS mcurrency,
				  i.`TermAndConditionInt` AS ntext,
				  c.`ProgramId`,
				  c.`CommissionType`,
				  c.`SupportType`,
				  c.`CommissionUsed`,
				  c.`CommissionCurrency`,
				  d.`TermAndCondition`,
				  c.`ShippingCountry`,
                  b.`Outbound`
				FROM
				  store a
				  INNER JOIN r_store_program b
					ON a.`ID` = b.`StoreId`
				  INNER JOIN program_intell c
					ON c.`ProgramId` = b.`ProgramId`
				  LEFT JOIN program_manual m
					ON c.`ProgramId` = m.`ProgramId`
				  INNER JOIN program d
					ON d.`ID` = c.`ProgramId`
				  LEFT JOIN program_int i
    				ON d.`ID` = i.`ProgramId`
				  INNER JOIN wf_aff e
					ON e.`ID` = d.Affid
				WHERE a.`ID` ='.$search['id'];
		$domain_arr = $this->getRows($sql);
		$sql = "SELECT DomainId FROM r_store_domain where StoreId = '".$search['id']."'";
		$domainId_arr = $this->getRows($sql);
		$domainIdList = array();
		foreach ($domainId_arr as $do){
		    $domainIdList[$do['DomainId']] = $do['DomainId'];
		}
		//content publisher 可视的programs'...
		$ctPrograms = array();
		foreach($domain_arr as $v=>$k1){
			if($k1['muserd'] != '0.00' && !empty($k1['muserd'])){
				if($k1['mtype'] == 'Percent')
					$domain_arr[$v]['commission']= $k1['muserd'].'%';
				else{
					$domain_arr[$v]['commission']= !empty($k1['mcurrency'])?$k1['mcurrency'].$k1['muserd']:'USD'.$k1['muserd'];
				}
			}else{
				if($k1['CommissionType'] == 'Percent')
					$domain_arr[$v]['commission'] = $k1['CommissionUsed'].'%';
				else if($k1['CommissionType'] == 'Value'){
					$domain_arr[$v]['commission']= !empty($k1['CommissionCurrency'])?$k1['CommissionCurrency'].$k1['CommissionUsed']:'USD'.$k1['CommissionUsed'];
				}else{
					$domain_arr[$v]['commission'] = 0;
				}
			}
			$tmp = array();
			if(empty($k1['Outbound']) || $k1['Outbound'] == ''){
				unset($domain_arr[$v]);
				continue;
			}
			if (strstr($k1['Outbound'], ',')) {
				$val = explode(',', $k1['Outbound']);
			}else{
				$val = array($k1['Outbound']);
			}
			foreach($val as $k){
				if(strstr($k,'|')){
					$key = explode('|',$k);
				}else{
					$key = explode('-',$k);
				}
				if(!in_array($key[0], $domainIdList)){
				    continue;
				}

				$valTemp = strtoupper($key[1]);
				$code = strtolower($country[$valTemp]['CountryCode']);
				if(substr($key[2], 0,7) != 'http://' && substr($key[2], 0,7) != 'https://'){
					$url = "<a target=_blank href='http://r.brandreward.com/?key=9dcb88e0137649590b755372b040afad&tsc=".$code."&url=".urlencode('http://'.$key['2'])."'>".$key[2]."</a>";
				}else{
					$url = "<a target=_blank href='http://r.brandreward.com/?key=9dcb88e0137649590b755372b040afad&tsc=".$code."&url=".urlencode($key['2'])."'>".$key[2]."</a>";
				}
				$tmp[] = array(
						$country[$valTemp]['CountryName'],
						$url
				);
				$ctKey = md5($key[1] . "|" . $key[2]);
				if (!isset($ctPrograms[$ctKey]) || (isset($ctPrograms[$ctKey]) && $k1['SupportType'] == 'Content')) {
					$ctPrograms[$ctKey] = array(
						'region' => $country[$valTemp]['CountryName'],
						'ccode' => $url,
						'domain' => $domain_arr[$v]
					);
					$ctPrograms[$ctKey]['domain']['Name'] = $k1['ProgramName'];
				}
			}
			$domain_arr[$v]['Name'] = $k1['ProgramName'];
			$domain_arr[$v]['Outbound'] = $tmp;
		}
		$return_d['data'] = $domain_arr;
		$return_d['programs'] = $ctPrograms;
		return $return_d;
	}

	function showAdvertiserSupportTypeList($search){
		$sql = "SELECT
				  e.CommissionType AS mtype,
				  e.`CommissionUsed` AS muserd,
				  e.CommissionCurrency AS mcurrency,
				  d.CommissionBackup AS OLD,
				  i.`TermAndConditionInt` AS ntext,
				  f.`Name` AS AffName,
				  d.`ProgramId`,
				  g.`Name`,
				  d.`ShippingCountry`,
				  g.`TermAndCondition`,
				  d.`SupportType`,
				  d.`PPC`,
				  IFNULL(e.`SupportType`, 'NO') AS SupportTypeManualCtrl,
				  d.CommissionType,
				  d.CommissionUsed,
				  d.CommissionCurrency
				FROM
				  store a
				  INNER JOIN r_store_program b
					ON a.`ID` = b.`StoreId`
				  INNER JOIN program_intell d
					ON d.`ProgramId` = b.`ProgramId`
				  LEFT JOIN program_manual e
					ON d.`ProgramId` = e.`ProgramId`
				  INNER JOIN wf_aff f
					ON f.`ID` = d.`AffId`
				  INNER JOIN program g
					ON g.`ID` = d.`ProgramId`
				  LEFT JOIN program_int i
    				ON g.`ID` = i.`ProgramId`
				WHERE a.id = '{$search['id']}'
				  AND g.StatusInAff = 'Active'
				  AND g.Partnership = 'Active'
				GROUP BY d.`ProgramId`";
		$program_tmp_arr = $this->getRows($sql);
		$program_arr = array();
		foreach ($program_tmp_arr as &$program) {
			//优先从program_manual获取commission
			if($program['CommissionType'] == 'Percent'){
				$check = $program['CommissionUsed'].'%';
			}else{
				$check = !empty($program['CommissionCurrency'])?$program['CommissionCurrency'].$program['CommissionUsed']:'USD'.$program['CommissionUsed'];
			}
			if($program['muserd'] != '0.00' && !empty($program['muserd'])){
				if($program['mtype'] == 'Percent')
					$program['commission'] = $program['muserd'].'%';
				else{
					$program['commission'] = !empty($program['mcurrency'])?$program['mcurrency'].$program['muserd']:'USD'.$program['muserd'];
				}
			}else{
				$program['commission'] = $check;
			}
			$program['check'] = $check;
			if(!empty($program['old']) && $check != $program['old']){
				$program['uptype'] = 1;
				$program['newcom'] = $check;
			}else{
				$program['uptype'] = 2;
			}

			$sql = "select group_concat(Site) as site, group_concat(distinct `Key`) as domain from domain_outgoing_default_other where pid='{$program['ProgramId']}' GROUP BY pid union
select group_concat(Site) as site, group_concat(distinct `Key`) as domain from redirect_default where pid='{$program['ProgramId']}' group by pid";
			$country_domain_arr = $this->objMysql->getFirstRow($sql);
			$shipping = array_unique(explode(',', $program['ShippingCountry']));
			if (isset($country_domain_arr['site'])) {
				$country = array_unique(explode(',', $country_domain_arr['site']));
				$program['domain'] = trim(implode(',', array_unique(explode(',', $country_domain_arr['domain']))), ',');
			} else {
				$country = array();
				$ySql = "SELECT domain.Domain FROM r_domain_program LEFT JOIN domain ON domain.ID = r_domain_program.DID WHERE r_domain_program.Status = 'Active' AND r_domain_program.PID={$program['ProgramId']}";
				$yRow = $this->objMysql->getFirstRow($ySql);
				$program['domain'] = isset($yRow['Domain']) ? $yRow['Domain'] : '';

			}
			unset($program['ShippingCountry']);
			unset($program['CountryCode']);
			$program['major'] = '';
			$program['minor'] = '';
			$program['general'] = '';

			if (empty($country)) {
				$program['general'] = trim(implode(',', $shipping), ',');
			} else {
				foreach ($country as $k) {
					if (in_array($k, $shipping)) {
						$program['major'] .= $k . ',';
						unset($shipping[array_search($k, $shipping)]);
					} else {
						$program['minor'] .= $k . ',';
					}
				}
				$program['major'] = trim($program['major'], ',');
				$program['minor'] = trim($program['minor'], ',');
				$program['general'] = trim(implode(',', $shipping), ',');
			}
			$program_arr[] = $program;
		}
		return $program_arr;
	}

	function get_multi_brands($search)
	{
		$where_str = '';
		$where_arr = array();

		$where_arr[] = "b.`ID` > 0";

		if(isset($search['store_keywords']) && !empty($search['store_keywords']))
			$where_arr[] = "Keywords like '".$search['store_keywords']."%'";
		else
			return array();

		if (isset($search['country']) && !empty($search['country']))
			$where_arr[] = "b.site = '" . $search['country'] . "'";

		$where_str = empty($where_arr)?'':' WHERE '.join(' AND ',$where_arr);
		$sql = "SELECT c.ID,c.StoreId,c.Keywords,c.StoreName FROM store_multi_brand AS c LEFT JOIN r_store_domain AS a ON c.`StoreId` = a.`StoreId` LEFT JOIN domain_outgoing_default_other AS b ON a.`DomainId` = b.`DID` $where_str GROUP BY c.ID";
		$rows_multi = $this->getRows($sql);

		if(empty($rows_multi))
			return array();

		$storeids = array();
		$store_multi = array();
		foreach($rows_multi as $v){
			$storeids[] = $v['StoreId'];
			$store_multi[$v['StoreId']][] = $v['Keywords'];
		}

		$sql = "SELECT ID,`Name`, SupportCoupon, SupportLoyalty FROM store WHERE ID IN (".join(',',$storeids).")";
		$rows_store = $this->getRows($sql);
		foreach($rows_store as $k=>$v){
			$rows_store[$k]['Keywords'] = join("<br>",$store_multi[$v['ID']]);
		}

		return $rows_store;
	}

	function  get_publisher_page($para = array(), $page, $pagesize=30){
		$sql_head = "SELECT ID,Url,AddUser,Addtime,`Status` FROM publisher_page WHERE 1=1";
		$page_start = $pagesize * ($page - 1);
		$info['page_now'] = $page;
		if (isset($para['p'])) unset($para['p']);
		$where = '';
		if(isset($para['status']) && $para['status'] != 'All'){
			$where.= " and Status = '{$para['status']}'";
		}
		if(isset($para['user']) && !empty($para['user'])){
			$where.= " and `AddUser` = '{$para['user']}'";
		}
		if(!empty($para['search'])){
			$search = trim($para['search']);
			$where.= " and Url like '%$search%'";
		}
		$sql_tail = " ORDER BY ID Desc limit $page_start,$pagesize";
		//拼接获取list
		$sql = $sql_head . $where . $sql_tail;

		$info['data'] = $this->getRows($sql);
		if(empty($info['data'])){
			return $info['data'] = 'No Data';
		}
		foreach($info['data'] as $k=>$v){
			$info['data'][$k]['Number'] = $k+($page-1)*$pagesize+1;
		}
		$sqlcount = "SELECT count(*) FROM publisher_page  where 1=1".$where;
		$total = $this->getRows($sqlcount);
		$info['page_total'] = ceil($total[0]['count(*)'] / $pagesize);
		return $info;
	}

	function get_publisher_page_detail($para = array(), $page, $pagesize=30){
		$sql_head = "SELECT ExtDomain,COUNT(1) AS amount FROM publisher_page_detail WHERE DomainInfoID = {$para['id']} ";
		$page_start = $pagesize * ($page - 1);
		$info['page_now'] = $page;
		if (isset($para['p']))
			unset($para['p']);
		$where = '';

		if(!empty($para['search'])){
			$where .= " and ExtDomain like '%{$para['search']}%'";
			$sql_tail = " GROUP BY ExtDomain ORDER BY amount DESC limit $page_start,$pagesize";

		}else{
			$sql_tail = " GROUP BY ExtDomain ORDER BY amount DESC limit $page_start,$pagesize";
		}
		$sql = $sql_head . $where . $sql_tail;
		//var_dump($sql);die;
		$info['data'] = $this->getRows($sql);
		if(empty($info['data'])){
			return $info['data'] = 'No Data';
		}
		foreach($info['data'] as $k=>$v){
			$info['data'][$k]['Number'] = $k+($page-1)*$pagesize+1;
		}
		$sqlcount = "SELECT count( DISTINCT ExtDomain) as `count` FROM publisher_page_detail WHERE DomainInfoID = {$para['id']}".$where;
		$total = $this->getRows($sqlcount);
		$info['page_total'] = ceil($total[0]['count'] / $pagesize);

		$sql = "SELECT DISTINCT e.`Name` AS Network, d.`ProgramId`, b.`Name` AS Advertiser, concat(d.`CommissionUsed`, CASE WHEN d.`CommissionType` = 'Value' THEN d.`CommissionCurrency` ELSE '%' END) AS Commission FROM publisher_page_detail a INNER JOIN store b ON a.Store = b.`Name` INNER JOIN r_store_program c ON b.`ID` = c.`StoreId` INNER JOIN program_intell d ON c.`ProgramId` = d.`ProgramId` INNER JOIN wf_aff e ON e.`ID` = d.`AffId` WHERE b.`StoreAffSupport` = 'YES' AND d.`IsActive` = 'Active' AND d.`CommissionUsed` != 0 order by e.`Name`";
		$info['network'] = $this->getRows($sql);

		return $info;
	}
}
