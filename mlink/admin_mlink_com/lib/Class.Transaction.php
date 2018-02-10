<?php
class Transaction extends LibFactory
{
	function getTransactionListPage($data,$page,$page_size=20){
		$return_d = array();
		$where_str = '';
		$where_arr = array();
		$where_out_str = '';
		$where1='';
		switch ($data['timetype']) {
			case 'CreatedDate' :
				$searchby = 'createddate';
				break;
			case 'VisitedDate' :
				$searchby = 'Visited';
				break;
			default :
				$searchby = 'createddate';
		}
		$data['type'] = !empty($data['type'])?$data['type']:'2';
		if($data['type'] == 2){
			$mkWhereSql = mk_publisher_where();
			$sql = "SELECT b.`ApiKey` FROM publisher AS a LEFT JOIN publisher_account AS b ON a.`ID` = b.`PublisherId` WHERE $mkWhereSql AND b.ApiKey IS NOT NULL";
			$res = $this->getRows($sql);
			if(!empty($res)){
				$keyid=' p.site NOT IN(';
				foreach($res as $k){
					$keyid.='"'.$k['ApiKey'].'",';
				}
				$where_arr[] = rtrim($keyid,',').")";
			}
		}
		if(isset($data['stime']) && !empty($data['stime'])){
			$where_arr[] = 'p.'.$searchby.' >= "'.$data['stime'].'"'; //VisitedDate应该是数据表里的一个字段
		}
		if(isset($data['etime']) && !empty($data['etime'])){
			$where_arr[] = 'p.'.$searchby.' <= "'.$data['etime'].'"';
		}
		if(isset($data['site']) && !empty($data['site'])){
			$sql = "select  a.`ID`,b.`ApiKey` from publisher as a inner join publisher_account as b on a.ID=b.PublisherId where b.Alias = '".addslashes(trim($data['site']))."' OR b.`Name` = '".addslashes(trim($data['site']))."' OR a.`Name` = '".addslashes(trim($data['site']))."' OR a.`Domain` = '".addslashes(trim($data['site']))."' OR a.`Email` = '".addslashes(trim($data['site']))."' OR a.`UserName` = '".addslashes(trim($data['site']))."' OR b.`Domain` = '".addslashes(trim($data['site']))."' OR b.`Apikey` = '".addslashes(trim($data['site']))."'";
			$rows_p = $this->getRows($sql);
			$site_keys = array();
			if(!empty($rows_p)){
				foreach($rows_p as $k=>$v){
					$site_keys[] = $v['ApiKey'];
				}
			}
			if(!empty($site_keys)){
				$where_arr[] = 'p.site IN ("'.join('","',$site_keys).'")';
			}else{
				$where_arr[] = "0=1";
			}
		}
		if(isset($data['sitetype']) && !empty($data['sitetype'])){
			$stype = $data['sitetype'];
			$sql = "select b.`ApiKey` from publisher as a inner join publisher_account as b on a.ID=b.PublisherId where a.SiteOption='$stype'";
			$rows_p = $this->getRows($sql);
			$site_keys = array();
			if(!empty($rows_p)){
				foreach($rows_p as $k=>$v){
					$site_keys[] = $v['ApiKey'];
				}
			}
			if(!empty($site_keys)){
				$where_arr[] = 'p.site IN ("'.join('","',$site_keys).'")';
			}else{
				$where_arr[] = "0=1";
			}
		}
//		if(isset($data['cstatus']) && !empty($data['cstatus'])){
//			if($data['cstatus'] == 2){
//				$where_arr[] = 'p.CommissionStatus =1';
//			}
//		}
		$subQuery = array();
		if(isset($data['state']) && !empty($data['state'])){
			$state = $data['state'];
			if (in_array('PAID', $state)) {
				$where_arr[] = 'p.PaidDate != "0000-00-00"';
				foreach ($state as $key=>$value)
				{
					if ($value ===  'PAID')
						unset($state[$key]);
				}
			}
			!empty($state) && $where_arr[] = 'p.state IN ("'.join('","',$state).'")';
		}

		$storeName = isset($data['advertiser']) ? $data['advertiser'] : '';
		if (!empty($storeName)) {
			array_push($subQuery, "a.`Name` LIKE '%$storeName%' OR a.`NameOptimized` LIKE '%$storeName%'");
		}
		$storeStatus = isset($data['status']) ?  $data['status'] : '';
		if (!empty($storeStatus)) {
			array_push($subQuery, "a.`SupportType` = '$storeStatus' ");
		}
		if(!empty($subQuery)){
			$val = implode(' AND ', $subQuery);
			$sql1 = "SELECT b.`DomainId` FROM store AS a LEFT JOIN r_store_domain AS b ON a.`ID` = b.`StoreId` WHERE $val";
			$res1 = $this->getRows($sql1);
			if(!empty($res1)){
				$where_arr[] = "p.domainid in(SELECT b.`DomainId` FROM store AS a LEFT JOIN r_store_domain AS b ON a.`ID` = b.`StoreId` WHERE $val)";
				$sql = "SELECT AffiliateUrlKeywords FROM wf_aff where IsActive = 'YES'";
				$res = $this->getRows($sql);
				$domain = '';
				foreach($res as $k){
					if(strstr($k['AffiliateUrlKeywords'],"\r\n")){
						$arr  = explode("\r\n",$k['AffiliateUrlKeywords']);
						foreach($arr as $k){
							$domain.="'".$k."',";
						}
					}else{
						$domain.="'".$k['AffiliateUrlKeywords']."',";
					}
				}
				$domain = rtrim($domain,',');
				$sql = "select id from domain where domain in($domain)";
				$res = $this->getRows($sql);
				if(!empty($res)){
					$nid = "";
					foreach($res as $k){
						$nid.=$k['id'].',';
					}
					$nid = rtrim($nid,',');
					//$where_arr[]= "p.domainid not in($nid)";
				}
			}else{
				$where_arr[] = '0=1';
			}

		}
		if(isset($data['country']) && !empty($data['country'])){
			    $country = $data['country'];
				$where_arr[] = "p.country in($country)";
		}
		if(isset($data['networkname']) && !empty($data['networkname'])){
			$where_arr[] = 'p.affid ='.$data['networkname'];
		}
		if(isset($data['affiliate']) && !empty($data['affiliate'])) {
			$where_arr[] = 'p.affid in (' .$data['affiliate'] . ')';
		}
		if(isset($data['linkid']) && !empty($data['linkid'])) {
			$where_arr[] = 'p.linkId = ' .$data['linkid'];
		}
		$where_arr[] = "p.af NOT IN ('bdg','mk','mega')";
		if(!empty($where_arr)){
			$where_str = join(' AND ',$where_arr);     //join函数把数组的两个值串成新的字符串
		}
		$sql = "SELECT COUNT(p.ID) AS c FROM rpt_transaction_unique p WHERE $where_str";
		$count = $this->getRows($sql);
		$count =$count[0]['c'];
		$sql = "SELECT
                  p.ID,
                  p.BRID,
                  p.Visited,
                  p.Country,
                  p.Created,
                  p.Updated,
                  p.Sales,
                  p.State,
                  p.Commission,
                  p.TradeCancelReason,
                  p.Site,
                  p.Tax,
                  p.TaxCommission,
                  p.ShowRate,
                  p.TradeKey,
                  p.ShowCommission,
                  p.CommissionStatus,
                  p.RefRate,
                  p.RefCommission,
                  p.RefPublisherId,
                  p.SID,
                  p.linkId,
                  p.PaidDate,
                  p.affid,
                  p.domainId
                FROM
                  rpt_transaction_unique p
                WHERE $where_str
                ORDER BY p.ID DESC
                LIMIT $page,$page_size";
		$rows = $this->getRows($sql);
		$sids = array();
		$site = array();
		$did = array();
		$wid = array();
		if(!empty($rows)){
			foreach($rows as &$v){
				if($v['CommissionStatus'] == 1){
					$text = "";
					$sql = "SELECT a.Created, a.Updated, a.Sales, a.Commission, a.`ProgramName` , b.`Name` FROM `rpt_transaction_base` a LEFT JOIN wf_aff b ON a.`Affid` = b.id WHERE a.TradeKey = '{$v['TradeKey']}' ORDER BY a.Updated";
					$res = $this->getRows($sql);
					if(!empty($res)){
						foreach($res as $k){
							$text.=$k['Name'].' -- '.$k['ProgramName'].' -- '.$k['Created'].' -- '.$k['Updated'].' -- Sales:$'.number_format($k['Sales']).' -- Commission:$'.number_format($k['Commission'])." || ";
						}
						$v['comstatus'] = rtrim($text,' || ');
					}else{
						$v['comstatus'] = '---';
					}
				}else{
					$v['comstatus'] = '---';
				}
				$sids[] = $v['SID'];
				$site[]=$v['Site'];
				$did[]=$v['domainId'];
				$wid[]=$v['affid'];
			}
			$site = '"'.join('","',array_unique($site)).'"';
			$did = join(',',array_unique($did));
			$wid = join(',',array_unique($wid));
			$sql = "select a.Domain as SiteUrl,a.ApiKey,a.Alias,b.SiteOption from publisher_account as a left join publisher as b on a.PublisherId = b.ID where a.ApiKey in($site)";
			$wsql = "select `Name` as AffName,`ID` from wf_aff where id in($wid)";
			$dsql = "SELECT IF(a.NameOptimized='' OR a.NameOptimized IS NULL,a.Name,a.NameOptimized) AS StoreName,b.`DomainId` FROM store AS a LEFT JOIN  r_store_domain AS b  ON a.`ID` = b.`StoreId` WHERE b.`DomainId` IN($did)";
			$dres = $this->objMysql->getRows($dsql,'DomainId');
			$siteres = $this->objMysql->getRows($sql,'ApiKey');
			$wffres =  $this->objMysql->getRows($wsql,'ID');
			foreach($rows as &$v){
				$v['sitetype'] = isset($siteres[$v['Site']]['SiteOption']) ? $siteres[$v['Site']]['SiteOption'] : '';
				if(!isset($siteres[$v['Site']])){
					$v['SiteUrl'] = '--';
					$v['SiteAlias'] = '--';
				}else{
					$v['SiteUrl'] = !preg_match('/^https?:\\/\\//',$siteres[$v['Site']]['SiteUrl'])?'http://'.$siteres[$v['Site']]['SiteUrl']:$siteres[$v['Site']]['SiteUrl'];
					$v['SiteAlias'] = $siteres[$v['Site']]['Alias'];
				}
				if(!isset($dres[$v['domainId']])){
					$v['StoreName'] = '--';
				}else{
					$v['StoreName'] = $dres[$v['domainId']]['StoreName'];
				}
				$v['AffName'] = $wffres[$v['affid']]['AffName'];
			};
			$where_out_str .= ' AND sessionId IN ("'.join('","',array_unique($sids)).'")';
			$sql = "SELECT sessionId,pageUrl FROM bd_out_tracking where Createddate >='{$data['stime']}' and Createddate <='{$data['etime']}'".$where_out_str ;
			$out_data = $this->objMysql->getRows($sql,'sessionId');
			foreach($rows as &$v){
				$v['pageUrl'] = isset($out_data[$v['SID']])?$out_data[$v['SID']]['pageUrl']:'';
			}
			}
			if($data['download'] == 1){
				$calObj = new Calculation();
				$calObj->startDate = isset($data['stime']) ? $data['stime'] : '';
				$calObj->endDate = isset($data['etime']) ? $data['etime'] : '';
				$calObj->country = isset($data['country']) ? $data['country'] : '';
				$calObj->network = isset($data['affiliate']) ? $data['affiliate'] : '';
				$calObj->advertiserKeyword = isset($data['advertiser']) ? $data['advertiser'] : '';
				$calObj->publisherSite = isset($data['site']) ? $data['site'] : '';
				$calObj->transactionLinkId = isset($data['linkid']) ? $data['linkid'] : '';
				$calObj->advertiserStatus = isset($data['status']) ? $data['status'] : '';
				switch ($data['timetype']) {
					case 'CreatedDate' :
						$calObj->dateType = 'createddate';
						break;
					case 'VisitedDate' :
						$calObj->dateType = 'visiteddate';
						break;
				}
				$calObj->dataType = $data['type'] != 2 ? 'all' : 'publisher';
				$calObj->publisherSiteType = isset($data['sitetype']) ? $data['sitetype'] : '';
				$calObj->transactionStatus  = isset($data['state']) ? $data['state'] : '';
				$calObj->transactionCommissionStatus  = isset($data['cstatus']) ? $data['cstatus'] : '';
				$sql = "select CountryCode,CountryName from country_codes";
				$country = $this->objMysql->getRows($sql,'CountryCode');
				$country['UK']['CountryName'] ='United Kingdom';
				$cinfo = $calObj->calTransactionUniqueCountry(0,15,array(' com DESC'));
				foreach($cinfo as &$k){
					if(isset($country[strtoupper($k['name'])])){
						$k['name'] = $country[strtoupper($k['name'])]['CountryName'];
					}
				}
				$return_d['sum'] = $calObj->calTransactionUnique();
				$return_d['cinfo'] = $cinfo;
			}
			$return_d['data'] = $rows;
			$return_d['page_now'] = $page;
			$return_d['total_num'] = $count;
			return $return_d;
	}

	function get_affiliate_rpt($data){
		$timecol = 'createddate';
		// $timecol = 'VisitedDate';

		$return_d['tran'] = array();
		$return_d['page'] = array();


		$where_str = '';
		$site = array();
		if(isset($data['pid']) && !empty($data['pid'])){
			$s = strpos($data['pid'] ,'(');
			$data['pid'] = trim($data['pid']);
			if($s !== false){
				$pid = trim(substr($data['pid'],$s+1,-1));
			}else{
				$row = $this->table('publisher_account')->where('Alias = "'.addslashes($data['pid']).'"')->findone();
				if($row){
					$pid = $row['ID'];
				}else{
					$pid = 0;
				}
			}

			$where_str = 'ID = '.intval($pid);

			$sites_rows = $this->table('publisher_account')->where($where_str)->find();

			if(!empty($sites_rows)){
				foreach($sites_rows as $v){
					$site[] = addslashes($v['ApiKey']);
				}
			}
		}



		$where_str = '';
		$where_arr = array();


		if($data['tran_from']){
			$where_arr[] = $timecol.' >= "'.$data['tran_from'].'"';
		}
		if($data['tran_to']){
			$where_arr[] = $timecol.' <= "'.$data['tran_to'].'"';
		}

		if(!empty($site)){
			$where_arr[] = 'site IN ("'.join('","',$site).'")';
		}

		if(!empty($where_arr)){
			$where_str = ' WHERE '.join(' AND ',$where_arr);
		}

		// $groupby = 'Af,'.$timecol;

		// $sql = 'SELECT '.$groupby.',SUM(Commission) as Commission  
		// 		FROM rpt_transaction_unique '.$where_str.' GROUP BY '.$groupby;
		$sql = 'SELECT createddate,affid,SUM(revenues) AS revenues FROM `statis_affiliate_br` '.$where_str.' GROUP BY createddate,affid HAVING revenues > 0';
		$tran_row = $this->getRows($sql);//getRows函数返回所有记录，每个记录又是一个数组，所以返回值是一个二维数组

		$affids = array();
		foreach($tran_row as $k=>$v){
			$affids[] = $v['affid'];
		}
		$sql = 'SELECT ID,Name FROM wf_aff where ID IN ('.join(',',$affids).')';
		$aff_tmp = $this->getRows($sql);
		$aff_row = array();
		foreach($aff_tmp as $k=>$v){
			$aff_row[$v['ID']] = $v;
		}


		$return_d = array();
		$return_d['tran_row'] = $tran_row;
		$return_d['aff_row'] = $aff_row;

		return $return_d;
	}





	function get_affiliate_ov($data){
		$site = array();
		if(isset($data['pid']) && !empty($data['pid'])){
			$s = strpos($data['pid'] ,'(');
			$data['pid'] = trim($data['pid']);
			if($s !== false){
				$pid = trim(substr($data['pid'],$s+1,-1));
			}else{
				$row = $this->table('publisher_account')->where('Alias = "'.addslashes($data['pid']).'"')->findone();
				if($row){
					$pid = $row['ID'];
				}else{
					$pid = 0;
				}
			}

			$where_str = 'ID = '.intval($pid);

			$sites_rows = $this->table('publisher_account')->where($where_str)->find();

			if(!empty($sites_rows)){
				foreach($sites_rows as $v){
					$site[] = addslashes($v['ApiKey']);
				}
			}
		}


		$where_a_str = '';
		$where_a = array();
		if(isset($data['tran_from']) && $data['tran_from']){
			$where_a[] = 'createddate >= "'.$data['tran_from'].'"';
		}
		if(isset($data['tran_to']) && $data['tran_to']){
			$where_a[] = 'createddate <= "'.$data['tran_to'].'"';
		}

		if(!empty($site)){
			$where_a[] = 'site IN ("'.join('","',$site).'")';
		}


		$where_a_str = empty($where_a)?'':' WHERE '.join(' AND ',$where_a);

		$sql = 'SELECT affid,SUM(clicks) as clicks,SUM(orders) as orders,SUM(revenues) as revenues,SUM(sales) as sales FROM `statis_affiliate_br` '.$where_a_str.' GROUP BY affid ORDER BY revenues DESC';
		$row = $this->getRows($sql);

		$affids = array();
		foreach($row as $k=>$v){
			$affids[] = $v['affid'];
		}

		$aff_tmp = $this->getRows('SELECT ID,Name FROM wf_aff WHERE ID IN ('.join(',',$affids).')');
		$aff_row = array();
		foreach($aff_tmp as $k=>$v){
			$aff_row[$v['ID']] = $v['Name'];
		}

		foreach($row as $k=>$v){
			if(isset($aff_row[$v['affid']]))
				$row[$k]['affname'] = $aff_row[$v['affid']];
		}

		return $row;
	}



	//affiliates逻辑
	function getAffiliatesListPage($search,$page,$page_size=10){

		$where_str = '';
		if(isset($search['name']) && $search['name']){
		}else{
			$search['name']="";
		}
		if(isset($search['domain']) && $search['domain']){
		}else{
			$search['domain']="";
		}
		if(isset($search['transactionCrawled']) && $search['transactionCrawled']){
		}else{
			$search['transactionCrawled']="";
		}
		if(isset($search['programCrawled']) && $search['programCrawled']){
		}else{
			$search['programCrawled']="";
		}


		$where=array();
		if(!empty($search['name'])){
			$where[]='`Name` LIKE "'.addslashes($search['name']).'%"';
		}
		if(!empty($search['domain'])){
			$where[]='`Domain` LIKE "'.addslashes($search['domain']).'%"';
		}
		if(!empty($search['affKeywords'])){
			$where[]='`AffiliateUrlKeywords` LIKE "%'.addslashes($search['affKeywords']).'%"';
		}
		if(!empty($search['statsReportCrawled'])){
			$where[]='`StatsReportCrawled` = "'.addslashes($search['statsReportCrawled']).'"';
		}
		if(!empty($search['programCrawled'])){
			$where[]='`ProgramCrawled` = "'.addslashes($search['programCrawled']).'"';
		}
		if(!empty($search['isActive'])){
			$where[]='`IsActive` = "'.addslashes($search['isActive']).'"';
		}
		if(!empty($search['revenueAccount'])){
			$where[]='`RevenueAccount` = "'.addslashes($search['revenueAccount']).'"';
		}
		if(!empty($search['isInHouse'])){
			$where[]='`IsInHouse` = "'.addslashes($search['isInHouse']).'"';
		}
		if(!empty($search['level'])){
		    $where[]='`Level` = "'.addslashes($search['level']).'"';
		}
		if(!empty($search['received'])){
			$where[]='`RevenueReceived` = "'.addslashes($search['received']).'"';
		}
		
		$where_str = empty($where)?"":join('AND', $where);


		//addslashes会自动在预定义字符前面加反斜杠\进而变成转义字符
		//$where_str = '`Name` LIKE "'.addslashes($search['name']).'%" AND `Domain` LIKE "'.$search['domain'].'%" AND `TransactionCrawled` = "'.$search['c'].'"';         //like %ABC%,搜索字符串中含有ABC的字符串
		//print_r($where_str);
		$return_d = array();
		$c_row = $this->table('wf_aff')->count()->where($where_str)->findone();


		$return_d['page_total'] = ceil($c_row['tp_count']/$page_size);
		if (isset($search['idstr'])){
			$arr = array();
			$newarr = array();
			$str = '';
			$arr = explode("|", $search['idstr']);
			foreach ($arr as $val){
				$newarr[] = intval($val); //把数字都转化成整形，防sql注入。intval转化失败，返回0
			}
			$str = implode(",",$newarr);

			$query= ' WHERE `ID` IN ('.$str.')';
			$sql_page_total = 'SELECT COUNT(*) as count FROM wf_aff'.$query;
			$count = mysql_query($sql_page_total);
			$count = mysql_fetch_array($count,MYSQL_ASSOC);
			$c_row['tp_count'] = $count['count'];
			$return_d['page_total'] = ceil($c_row['tp_count']/$page_size);
		}


		$return_d['page_now'] = $page;
		$return_d['total_num'] = $c_row['tp_count'];


		$where_str = $where_str?' WHERE '.$where_str:'';      //看仔细了，是三目运算符



		$sql = 'SELECT Id,Name,Domain,DeepUrlParaName,RevenueAccount,RevenueReceived,AffiliateUrlKeywords,ShortName,AffiliateUrlKeywords2,SubTracking,SubTracking2,Account,Password,StatsReportCrawled,IsInHouse,IsActive,ProgramCrawled,`Level`
				FROM wf_aff   
				'.$where_str.' 
				ORDER BY `Id` 
				LIMIT '.($page-1)*$page_size.','.$page_size;

		if (isset($search['idstr'])){

			$sql = 'SELECT Id,Name,Domain,DeepUrlParaName,RevenueAccount,RevenueReceived,AffiliateUrlKeywords,ShortName,AffiliateUrlKeywords2,SubTracking,SubTracking2,Account,Password,StatsReportCrawled,IsInHouse,IsActive,ProgramCrawled,`Level`
				FROM wf_aff
				'.$query.'
				ORDER BY `Id`
				LIMIT '.($page-1)*$page_size.','.$page_size;
		}


		$row = $this->getRows($sql);
		//$dylan='UPDATE wf_aff SET Transactioncrawled = "NO"';
		//mysql_query($dylan);
		$return_d['data'] = $row;
		return $return_d;
	}


	function getTransactionAffRpt($data,$page,$pagesize)
	{
		$sql_names_set = 'SET NAMES utf8';
		$this->query($sql_names_set);

		$calObj = new Calculation();
		$calObj->startDate = $data['from'];
		$calObj->endDate = $data['to'];
		$calObj->advertiserKeyword = $data['advertiser'];
		$calObj->country = explode(',', $data['country']);
		$calObj->publisherManager = $data['manager'];
		$calObj->publisherSite = $data['site'];
		$calObj->publisherSiteType = $data['sitetype'];
		$calObj->dataType = $data['datatype'];
		$calObj->advertiserStatus = $data['status'];
		$calObj->network = $data['affiliate'];
		switch ($data['timetype']) {
			case '1' :
				$calObj->dateType = 'createddate';
				break;
			case '2' :
				$calObj->dateType = 'clickdate';
				break;
		}
		switch ($data['datatype']) {
			case '1' :
				$calObj->dataType = 'publisher';
				break;
			case '2' :
				$calObj->dataType = 'all';
				break;
		}
		$calObj->advertiserCooperationStatus = $data['ctype'];
		$sumres = $calObj->calTransaction();
		$return_d = [
			'sum_total'      => isset($sumres['clicks']) ? $sumres['clicks'] : 0,
			'sum_sales'      => isset($sumres['sales']) ? $sumres['sales'] : 0,
			'sum_commission' => isset($sumres['commission']) ? $sumres['commission'] : 0,
			'sum_order'      => isset($sumres['orders']) ? $sumres['orders'] : 0,
			'sum_rob'        => isset($sumres['clicks_robot']) ? $sumres['clicks_robot'] : 0,
			'sum_robp'       => isset($sumres['clicks_maybe_robot']) ? $sumres['clicks_maybe_robot'] : 0,
			'sum_clicks'     => $sumres['clicks'] - $sumres['clicks_robot'],
		];
		$dir = !empty($data['order'])?$data['order']:'desc';
		$oname = !empty($data['oname'])?$data['oname']:'commission';
		$offset = ceil($page / $pagesize) + 1;
		$calData = $calObj->calTransactionGroup('network',$offset,$pagesize,array($oname . '_' .$dir ));
		$return_d['count'] = $calData['@total']['total'];
		$res = $calData['Actions'];
		foreach($res as &$v){
			$affid = isset($v['network']) ? $v['network'] : 0;
			if (!empty($affid)) {
				$sSql = "SELECT `IsActive` as status,Name AS `alias`,wf_aff.ID AS `id` FROM  wf_aff WHERE wf_aff.ID = $affid";
				$sRow = $this->getRow($sSql);
				$v = array_merge($v,$sRow);
			} else {
				$v['alias'] = 'NO AFFILIATE';
				$v['status'] = '-';
			}
			if($v['commission'] > 0 && $v['clicks'] > 0){
				$v['epc'] ='$'.number_format(($v['commission']/($v['clicks']-$v['clicks_robot'])),2,'.',',');
			}else{
				$v['epc'] = '-';
			}
			if($v['sales'] >0){
				$v['commrate'] =number_format(($v['commission']/$v['sales']*100),2,'.',',')."%";
			}else{
				$v['commrate'] = '-';
			}
			$v['rob'] =  number_format($v['clicks_robot']);
			$v['robp'] =  number_format($v['clicks_maybe_robot']);
			$v['realclicks'] =  number_format($v['clicks'] - $v['clicks_robot']);
			$v['clicks'] =  number_format($v['clicks']);
			$v['orders'] =  number_format($v['orders']);
			$v['Commission'] =  '$'.number_format($v['commission'],2);
			$v['Sales'] =  '$'.number_format($v['sales'],2);
		}
		$return_d['data'] =$res;
		return $return_d;
	}

	function getTransactionRpt($data,$page,$pagesize){
		$sql_names_set = 'SET NAMES utf8';
		$this->query($sql_names_set);
		$calObj = new Calculation();
		$calObj->startDate = $data['from'];
		$calObj->endDate = $data['to'];
		$calObj->advertiserKeyword = $data['advertiser'];
		$calObj->advertiserStatus = $data['status'];
		$calObj->country = explode(',', $data['country']);
		$calObj->publisherManager = $data['manager'];
		$calObj->publisherSite = $data['site'];
		$calObj->publisherSiteType = $data['sitetype'];
		$calObj->dataType = $data['datatype'];
		$calObj->network = $data['affiliate'];
		switch ($data['timetype']) {
			case '1' :
				$calObj->dateType = 'createddate';
				break;
			case '2' :
				$calObj->dateType = 'clickdate';
				break;
		}
		switch ($data['datatype']) {
			case '1' :
				$calObj->dataType = 'publisher';
				break;
			case '2' :
				$calObj->dataType = 'all';
				break;
		}
		$calObj->advertiserCooperationStatus = $data['ctype'];
		$sumres = $calObj->calTransaction();
		$return_d = [
			'sum_total'      => isset($sumres['clicks']) ? $sumres['clicks'] : 0,
			'sum_sales'      => isset($sumres['sales']) ? $sumres['sales'] : 0,
			'sum_commission' => isset($sumres['commission']) ? $sumres['commission'] : 0,
			'sum_order'      => isset($sumres['orders']) ? $sumres['orders'] : 0,
			'sum_rob'        => isset($sumres['clicks_robot']) ? $sumres['clicks_robot'] : 0,
			'sum_robp'       => isset($sumres['clicks_maybe_robot']) ? $sumres['clicks_maybe_robot'] : 0,
			'sum_clicks'     => $sumres['clicks'] - $sumres['clicks_robot'],
		];
		$dir = !empty($data['order']) ? $data['order'] : 'desc';
		$oname = !empty($data['oname']) ? $data['oname'] : 'commission';
		switch ($oname)
		{
			case 'rob' :
				$oname = 'clicks_robot';
				break;
			case 'robp' :
				$oname = 'clicks_maybe_robot';
				break;
		}
		$offset = ceil($page / $pagesize) + 1;
		$calData = $calObj->calTransactionGroup('advertiser',$offset,$pagesize,array($oname . '_' .$dir ));
		$return_d['count'] = $calData['@total']['total'];
		$res = $calData['Actions'];
		foreach($res as &$v){
			$storeId = isset($v['advertiser']) ? $v['advertiser'] : 0;
			if (!empty($storeId)) {
				$sSql = "SELECT StoreAffSupport as status,store.ID AS `id`,IF(NameOptimized='' OR NameOptimized IS NULL,Name,NameOptimized) AS `alias` FROM  store WHERE store.ID = $storeId";
				$sRow = $this->getRow($sSql);
				$v = array_merge($v,$sRow);
			} else {
				$v['alias'] = $v['status'] = '';
			}
			if($v['commission'] > 0 && $v['clicks'] > 0){
				$v['epc'] ='$'.number_format(($v['commission']/($v['clicks']-$v['clicks_robot'])),2,'.',',');
			}else{
				$v['epc'] = '-';
			}
			if($v['sales'] >0){
				$v['commrate'] =number_format(($v['commission']/$v['sales']*100),2,'.',',')."%";
			}else{
				$v['commrate'] = '-';
			}
			$v['rob'] =  number_format($v['clicks_robot']);
			$v['robp'] =  number_format($v['clicks_maybe_robot']);
			$v['realclicks'] =  number_format($v['clicks'] - $v['clicks_robot']);
			$v['clicks'] =  number_format($v['clicks']);
			$v['orders'] =  number_format($v['orders']);
			$v['Commission'] =  '$'.number_format($v['commission'],2);
			$v['Sales'] =  '$'.number_format($v['sales'],2);
		}
		$return_d['data'] =$res;
		return $return_d;
	}


	function getTransactionRptSite($data,$page,$pagesize){
		$sql_names_set = 'SET NAMES utf8';
		$this->query($sql_names_set);
		$return_d = array(
			'count' => 0,
			'sum_commission' => 0,
			'sum_clicks' => 0,
			'sum_sales' => 0,
			'sum_order' => 0,
			'sum_rob' => 0,
			'sum_robp' => 0
		);
		//calculation 统计类
		$calObj = new Calculation();
		switch ($data['datatype']) {
			case '1' :
				$calObj->dataType = 'publisher';
				break;
			case '2' :
				$calObj->dataType = 'all';
				break;
		}
		switch ($data['timetype']) {
			case '1' :
				$calObj->dateType = 'createddate';
				break;
			case '2' :
				$calObj->dateType = 'clickdate';
				break;
		}
		$calObj->startDate = $data['from'];
		$calObj->endDate = $data['to'];
		$calObj->network = $data['affiliate'];
		$calObj->country = explode(',', $data['country']);
		$calObj->publisherSite = $data['site'];
		$calObj->publisherManager = $data['manager'];
		$calObj->publisherSiteType = $data['sitetype'];
		$calObj->advertiserKeyword = $data['advertiser'];
		$calObj->advertiserStatus  = $data['status'];
		$cal = $calObj->calTransaction();
		$return_d = [
			'sum_total'      => $cal['clicks'],
			'sum_sales'      => $cal['sales'],
			'sum_commission' => $cal['commission'],
			'sum_order'      => $cal['orders'],
			'sum_rob'        => $cal['clicks_robot'],
			'sum_robp'       => $cal['clicks_maybe_robot'],
			'sum_clicks'     => $cal['clicks'] - $cal['clicks_robot'],
		];
		$dir = !empty($data['order'])?$data['order']:'desc';
		$oname = !empty($data['oname']) ? $data['oname'] : 'commission';
		$omap = array(
			'rob'  => 'clicks_robot',
			'robp' => 'clicks_maybe_robot',
		);
		$oname = isset($omap[$oname]) ? $omap[$oname] : $oname;
		$offset = ceil ($page / $pagesize);
		$calData = $calObj->calTransactionGroup('publisher',$offset + 1,$pagesize,array($oname . "_" . $dir));
		$res = $calData['Actions'];
		$return_d['count'] = $calData['@total']['total'];
		foreach($res as &$v){
			$site = isset($v['publisher']) ? $v['publisher'] : '';
			$sRow = array();
			if (!empty($site)) {
				$ssql =  "SELECT b.`Alias` as alias,c.`Manager`,c.`Status`,b.domain,c.SiteOption From publisher_account b left join publisher as c on b.PublisherId = c.ID WHERE b.`PublisherId` = '$site'";
				$sRow = $this->getRow($ssql);
				$v = array_merge($sRow,$v);
			} 
			if (empty($site) || empty($sRow)) {
				$v['alias'] = $v['Manager'] = $v['Status'] = $v['domain'] = $v['SiteOption'] = '';
			}
			if($v['commission'] > 0 && $v['clicks'] > 0){
				$v['epc'] ='$'.number_format(($v['commission']/($v['clicks']-$v['clicks_robot'])),2,'.',',');
			}else{
				$v['epc'] = '-';
			}
			if($v['sales'] >0){
				$v['commrate'] =number_format(($v['commission']/$v['sales']*100),2,'.',',')."%";
			}else{
				$v['commrate'] = '-';
			}
			$v['rob'] =  number_format($v['clicks_robot']);
			$v['robp'] =  number_format($v['clicks_maybe_robot']);
			$v['realclicks'] =  number_format($v['clicks']-$v['clicks_robot']);
			$v['clicks'] =  number_format($v['clicks']);
			$v['orders'] =  number_format($v['orders']);
			$v['Commission'] =  '$'.number_format($v['commission'],2);
			$v['Sales'] =  '$'.number_format($v['sales'],2);
		}
		$return_d['data'] =$res;
		return $return_d;
	}
	function get_history_domain_detail_rpt($data){
		//----------------------------------------------pid搜索--------------------------------------------------------------------------------
		$alias = array();
		if(isset($data['pid']) && !empty($data['pid'])){
			$s = strpos($data['pid'] ,'(');
			$data['pid'] = trim($data['pid']);
			if($s !== false){
				$pid = trim(substr($data['pid'],$s+1,-1));
			}else{
				$row = $this->table('publisher_account')->where('Alias = "'.addslashes($data['pid']).'"')->findone();
				if($row){
					$pid = $row['ID'];
				}else{
					$pid = 0;
				}
			}
			$where_str = 'ID = '.intval($pid);
			$sites_rows = $this->table('publisher_account')->where($where_str)->find();
			if(!empty($sites_rows)){
				foreach($sites_rows as $v){
					$alias[] = addslashes($v['Alias']);
				}
			}
		}
		$where_str = '';
		$where_arr = array();
		if($data['tran_from']){
			$where_arr[] = 'Createddate >= "'.$data['tran_from'].'"';
		}
		if($data['tran_to']){
			$where_arr[] = 'Createddate <= "'.$data['tran_to'].'"';
		}
		if($data['domain']){
			$where_arr[] = 'DomainUsed = ":DOMAIN:'.$data['domain'].'"';
		}
		if(!empty($alias)){
			$where_arr[] = 'Alias IN ("'.join('","',$alias).'")';
		}
		$where_arr[] = 'af NOT IN ("bdg","mk","mega")';
		if(!empty($where_arr)){
			$where_str = ' WHERE '.join(' AND ',$where_arr);
		}

		$groupby = 'Createddate';
		$orderby = 'Createddate DESC';
		$sql = 'SELECT '.$groupby.',SUM(Commission) as Commission,SUM(Sales) as Sales,count(*) as num
					FROM rpt_transaction_unique '.$where_str.' GROUP BY '.$groupby.' ORDER BY '.$orderby;
		$tran_row = $this->getRows($sql);
		return $tran_row;


	}









	function get_transaction_data($data){
		$where = array();
		$start = $data['sel_start'].'-01';
		$where[] = 'UpdatedDate >= "'.addslashes($start).'"';

		$end = $data['sel_end'].'-'.date('t',strtotime($data['sel_end']));
		$where[] = 'UpdatedDate <= "'.addslashes($end).'"';

		if(isset($data['sel_site']) && !empty($data['sel_site'])){
			foreach($data['sel_site'] as $k=>$v){
				$data['sel_site'][$k] = addslashes($v);
			}
			$where[] = 'Site IN ("'.join('","',$data['sel_site']).'") ';
		}

		if(isset($data['sel_aff']) && !empty($data['sel_aff'])){
			foreach($data['sel_aff'] as $k=>$v){
				$data['sel_aff'][$k] = addslashes($v);
			}
			$where[] = 'Af IN ("'.join('","',$data['sel_aff']).'") ';
		}
		$where[] = 'af NOT IN ("bdg","mk","mega")';
		$where_str = join(' AND ', $where);

		$column = $data['sel_mode'];

		$sql = 'SELECT DATE_FORMAT(UpdatedDate,"%Y-%m") AS cm,'.$column.',sum(commission) as commission FROM rpt_transaction_unique WHERE '.$where_str.'  GROUP BY   cm,'.$column.' order by cm';
		$row = $this->getRows($sql);

		$res = array();
		foreach($row as $k=>$v){
			$res[$v['cm']][$v[$column]] = $v;
		}
		return $res;
	}

	function get_transaction_data_daily($data){
		$pz= isset($data['sel_pagesize'])?intval($data['sel_pagesize']):20;
		$p = isset($data['p'])?intval($data['p']):1;
		$order_str = '';
		$order_str .= isset($data['sel_sort'])&&$data['sel_sort']?$data['sel_sort']:'Updated';
		$order_str .= ' ';
		$order_str .= isset($data['sel_orderby'])&&$data['sel_orderby']?$data['sel_orderby']:'DESC';
		$where = array();

		if(isset($data['sel_createddate_start']) && $data['sel_createddate_start']){
			$where[] = 'Createddate >= "'.addslashes($data['sel_createddate_start']).'"';
		}

		if(isset($data['sel_createddate_end']) && $data['sel_createddate_end']){
			$where[] = 'Createddate <= "'.addslashes($data['sel_createddate_end']).'"';
		}

		if(isset($data['sel_updateddate_end']) && $data['sel_updateddate_end']){
			$where[] = 'Updateddate <= "'.addslashes($data['sel_updateddate_end']).'"';
		}

		if(isset($data['sel_updateddate_start']) && $data['sel_updateddate_start']){
			$where[] = 'Updateddate >= "'.addslashes($data['sel_updateddate_start']).'"';
		}

		if(isset($data['sel_site']) && !empty($data['sel_site']) && !in_array('All',$data['sel_site'])){
			foreach($data['sel_site'] as $k=>$v){
				$data['sel_site'][$k] = addslashes($v);
			}
			$where[] = 'Site IN ("'.join('","',$data['sel_site']).'") ';
		}

		if(isset($data['sel_aff']) && !empty($data['sel_aff']) && !in_array('All',$data['sel_aff'])){
			foreach($data['sel_aff'] as $k=>$v){
				$data['sel_aff'][$k] = addslashes($v);
			}
			$where[] = 'Af IN ("'.join('","',$data['sel_aff']).'") ';
		}

		$where_str = join(' AND ', $where);

		$column = 'Created,Updated,Sales,Commission,Site,Af,ProgramName,SID,PublishTracking';

		$CRow = $this->table('rpt_transaction_unique')->where($where_str)->field('COUNT(*) AS c,SUM(Sales) AS Sales,SUM(Commission) AS Commission')->findone();
		$c = $CRow['c'];

		$dataRow = $this->table('rpt_transaction_unique')->where($where_str)->field($column)->limit($pz)->page($p)->order($order_str)->find();
		$return = array();
		$return['c'] = $c;
		$return['d'] = $dataRow;
		$return['t'] = ceil($c/$pz);
		$return['p'] = $p;
		$return['total_count'] = $CRow['c'];
		$return['total_Sales'] = $CRow['Sales'];
		$return['total_Commission'] = $CRow['Commission'];

		return $return;
	}

	function get_aff_ov($data,$type='Af'){
		$typeList = array('Af','Site');
		if(!in_array($type,$typeList)){
			$type = 'Af';
		}

		$where = array();

		if(isset($data['sel_createddate_start']) && $data['sel_createddate_start']){
			$where[] = 'Createddate >= "'.addslashes($data['sel_createddate_start']).'"';
		}

		if(isset($data['sel_createddate_end']) && $data['sel_createddate_end']){
			$where[] = 'Createddate <= "'.addslashes($data['sel_createddate_end']).'"';
		}

		if(isset($data['sel_updateddate_end']) && $data['sel_updateddate_end']){
			$where[] = 'Updateddate <= "'.addslashes($data['sel_updateddate_end']).'"';
		}

		if(isset($data['sel_updateddate_start']) && $data['sel_updateddate_start']){
			$where[] = 'Updateddate >= "'.addslashes($data['sel_updateddate_start']).'"';
		}

		if($type == 'Af'){
			if(isset($data['sel_site']) && !empty($data['sel_site']) && !in_array('All',$data['sel_site'])){
				foreach($data['sel_site'] as $k=>$v){
					$data['sel_site'][$k] = addslashes($v);
				}
				$where[] = 'Site IN ("'.join('","',$data['sel_site']).'") ';
			}
		}else{
			if(isset($data['sel_aff']) && !empty($data['sel_aff']) && !in_array('All',$data['sel_aff'])){
				foreach($data['sel_aff'] as $k=>$v){
					$data['sel_aff'][$k] = addslashes($v);
				}
				$where[] = 'Af IN ("'.join('","',$data['sel_aff']).'") ';
			}
		}
		$where[] = 'Af NOT IN ("bdg","mk","mega")';

		$where_str = '';
		$where_str = join(' AND ', $where);
		$where_str = $where_str?' WHERE '.$where_str:'';

		$order_str = ' ORDER BY ';
		$order_str .= isset($data['sel_sort'])&&$data['sel_sort']?$data['sel_sort']:'Commission';
		$order_str .= ' ';
		$order_str .= isset($data['sel_orderby'])&&$data['sel_orderby']?$data['sel_orderby']:'DESC';

		$sql = 'SELECT '.$type.',COUNT(*) AS c,SUM(Sales) AS Sales ,SUM(Commission) AS Commission FROM rpt_transaction_unique '.$where_str.' GROUP BY '.$type.' 
				 '.$order_str;
		$rows = $this->getRows($sql);

		$return = array();
		$return['rows'] = $rows;
		$total_sales = 0;
		$total_commission = 0;
		$total_ordernum = 0;
		foreach($rows as $k=>$v){
			$total_sales += $v['Sales'];
			$total_commission += $v['Commission'];
			$total_ordernum += $v['c'];
		}
		$return['total_sales'] = $total_sales;
		$return['total_commission'] = $total_commission;
		$return['total_ordernum'] = $total_ordernum;

		return $return;
	}

	function get_aff_ov_daily($data,$type = 'Af'){
		$typeList = array('Af','Site');
		if(!in_array($type,$typeList)){
			$type = 'Af';
		}
		$where = array();

		if(isset($data['sel_createddate_start']) && $data['sel_createddate_start']){
			$where[] = 'Createddate >= "'.addslashes($data['sel_createddate_start']).'"';
		}

		if(isset($data['sel_createddate_end']) && $data['sel_createddate_end']){
			$where[] = 'Createddate <= "'.addslashes($data['sel_createddate_end']).'"';
		}

		if(isset($data['sel_updateddate_end']) && $data['sel_updateddate_end']){
			$where[] = 'Updateddate <= "'.addslashes($data['sel_updateddate_end']).'"';
		}

		if(isset($data['sel_updateddate_start']) && $data['sel_updateddate_start']){
			$where[] = 'Updateddate >= "'.addslashes($data['sel_updateddate_start']).'"';
		}

		if($type == 'Af'){
			if(isset($data['sel_aff']) && $data['sel_aff']){
				$where[] = 'Af = "'.addslashes($data['sel_aff']).'"';
			}

			if(isset($data['sel_site']) && !empty($data['sel_site']) && !in_array('All',$data['sel_site'])){
				foreach($data['sel_site'] as $k=>$v){
					$data['sel_site'][$k] = addslashes($v);
				}
				$where[] = 'Site IN ("'.join('","',$data['sel_site']).'") ';
			}
		}else{
			if(isset($data['sel_site']) && $data['sel_site']){
				$where[] = 'Site = "'.addslashes($data['sel_site']).'"';
			}

			if(isset($data['sel_aff']) && !empty($data['sel_aff']) && !in_array('All',$data['sel_aff'])){
				foreach($data['sel_aff'] as $k=>$v){
					$data['sel_aff'][$k] = addslashes($v);
				}
				$where[] = 'Af IN ("'.join('","',$data['sel_aff']).'") ';
			}
		}
		$where[] = 'af NOT IN ("bdg","mk","mega")';

		$where_str = '';
		$where_str = join(' AND ', $where);
		$where_str = $where_str?' WHERE '.$where_str:'';


		$sql = 'SELECT Createddate,COUNT(*) AS c,SUM(Sales) AS Sales ,SUM(Commission) AS Commission FROM rpt_transaction_unique '.$where_str.' GROUP BY Createddate ORDER BY Createddate DESC';
		$rows = $this->getRows($sql);

		$return = array();
		$return['rows'] = $rows;
		$total_sales = 0;
		$total_commission = 0;
		$total_ordernum = 0;
		foreach($rows as $k=>$v){
			$total_sales += $v['Sales'];
			$total_commission += $v['Commission'];
			$total_ordernum += $v['c'];
		}
		$return['total_sales'] = $total_sales;
		$return['total_commission'] = $total_commission;
		$return['total_ordernum'] = $total_ordernum;

		return $return;
	}

	function getCommission($data){
		if(empty($data))
			return 0;

		$where_arr = array();
		$where_str = '';

		if($data['uid']){
			$ApiKey = array();
			$row = $this->table('publisher_account')->where('PublisherId = '.intval($data['uid']))->find();
			foreach($row as $v){
				$ApiKey[] = $v['ApiKey'];
			}
			$where_arr[] = 'Site IN ("'.join('","',$ApiKey).'")';
		}

		if($data['visitFrom']){
			$where_arr[] = 'VisitedDate >= "'.addslashes($data['visitFrom']).'"';
		}
		if($data['visitTo']){
			$where_arr[] = 'VisitedDate <= "'.addslashes($data['visitTo']).'"';
		}
		$where_arr[] = 'af NOT IN ("bdg","mk","mega")';

		$where_str = empty($where_arr)?'':join(' AND ',$where_arr);
		$row = $this->table('rpt_transaction_unique')->where($where_str)->field('SUM(Commission) as commission')->findone();
		if($row && !empty($row['commission'])){
			return $row['commission'];
		}else{
			return 0;
		}
	}

	function getTopDomain($data){
		if(empty($data))
			return array();

		$where_arr = array();
		$where_str = '';

		$where_arr[] = 'domainUsed != ""';
		if($data['uid']){
			$ApiKey = array();
			$row = $this->table('publisher_account')->where('PublisherId = '.intval($data['uid']))->find();
			foreach($row as $v){
				$ApiKey[] = $v['ApiKey'];
			}
			$where_arr[] = 'Site IN ("'.join('","',$ApiKey).'")';
		}

		if($data['visitFrom']){
			$where_arr[] = 'VisitedDate >= "'.addslashes($data['visitFrom']).'"';
		}
		if($data['visitTo']){
			$where_arr[] = 'VisitedDate <= "'.addslashes($data['visitTo']).'"';
		}
		$where_arr[] = 'af NOT IN ("bdg","mk","mega")';
		$limit = isset($data['limit'])?intval($data['limit']):10;

		$where_str = empty($where_arr)?'':join(' AND ',$where_arr);
		$row = $this->table('rpt_transaction_unique')->where($where_str)->field('domainUsed,SUM(Commission) as commission')->group('domainUsed')->order('commission desc')->limit($limit)->find();

		if($row){
			return $row;
		}else{
			return array();
		}
	}

	function get_history_affiliate_rpt($data){
		$where_arr = array();
		$where_his_arr = array();
		$where_now_arr = array();
		$where_his_str = '';
		$where_now_str = '';

		$alias = array();
		if(isset($data['pid']) && !empty($data['pid'])){
			$s = strpos($data['pid'] ,'(');
			$data['pid'] = trim($data['pid']);
			if($s !== false){
				$pid = trim(substr($data['pid'],$s+1,-1));
			}else{
				$row = $this->table('publisher_account')->where('Alias = "'.addslashes($data['pid']).'"')->findone();
				if($row){
					$pid = $row['ID'];
				}else{
					$pid = 0;
				}
			}

			$where_str = 'ID = '.intval($pid);

			$sites_rows = $this->table('publisher_account')->where($where_str)->find();


			if(!empty($sites_rows)){
				foreach($sites_rows as $v){
					$site[] = addslashes($v['ApiKey']);
				}
			}
		}


		if(!empty($data['his_from'])){
			$where_his_arr[] = 'createddate >= "'.addslashes($data['his_from']).'"';
		}

		if(!empty($data['his_to'])){
			$where_his_arr[] = 'createddate <= "'.addslashes($data['his_to']).'"';
		}

		if(!empty($data['now_from'])){
			$where_now_arr[] = 'createddate >= "'.addslashes($data['now_from']).'"';
		}

		if(!empty($data['now_to'])){
			$where_now_arr[] = 'createddate <= "'.addslashes($data['now_to']).'"';
		}

		$where_arr[] = 'affid > 0';

		if(!empty($site)){
			$where_arr[] = 'site IN ("'.join('","',$site).'")';
		}

		$where_his_str = join(' AND ',array_merge($where_his_arr,$where_arr));
		$where_his_str = $where_his_str?' WHERE '.$where_his_str:'';
		$where_now_str = join(' AND ',array_merge($where_now_arr,$where_arr));
		$where_now_str = $where_now_str?' WHERE '.$where_now_str:'';

		$sql_his = 'SELECT affid,SUM(orders) as ordernum,sum(sales) as ordersales,sum(revenues) as commission,sum(clicks) as clicks FROM statis_affiliate_br '.$where_his_str.' GROUP BY affid';
		// $sql_his = 'SELECT Af,count(*) as ordernum,sum(Sales) as ordersales,sum(Commission) as commission FROM rpt_transaction_unique '.$where_his_str.' GROUP BY Af';
		$row_his = $this->getRows($sql_his);

		$sql_now = 'SELECT affid,SUM(orders) as ordernum,sum(sales) as ordersales,sum(revenues) as commission,sum(clicks) as clicks FROM statis_affiliate_br '.$where_now_str.' GROUP BY affid';
		// $sql_now = 'SELECT Af,count(*) as ordernum,sum(Sales) as ordersales,sum(Commission) as commission FROM rpt_transaction_unique '.$where_now_str.' GROUP BY Af';
		$row_now = $this->getRows($sql_now);

		$affids = array();
		$data_his = array();
		$data_now = array();
		foreach($row_his as $k=>$v){
			$affids[] = $v['affid'];
			$data_his[$v['affid']] = $v;
		}

		foreach($row_now as $k=>$v){
			if(!in_array($v['affid'],$affids)){
				$affids[] = $v['affid'];
			}
			$data_now[$v['affid']] = $v;
		}

		$aff_tmp = $this->getRows('SELECT ID,Name FROM wf_aff WHERE ID IN ('.join(',',$affids).')');
		$aff_rows = array();
		foreach($aff_tmp as $k=>$v){
			$aff_rows[$v['ID']] = $v['Name'];
		}

		$aff_data = array();
		foreach($affids as $k=>$v){
			$tmp = array(
					'name'=>isset($aff_rows[$v])?$aff_rows[$v]:'',
					'his_ordernum'=>isset($data_his[$v]['ordernum'])?$data_his[$v]['ordernum']:0,
					'his_ordersales'=>isset($data_his[$v]['ordersales'])?$data_his[$v]['ordersales']:0,
					'his_commission'=>isset($data_his[$v]['commission'])?$data_his[$v]['commission']:0,
					'now_ordernum'=>isset($data_now[$v]['ordernum'])?$data_now[$v]['ordernum']:0,
					'now_ordersales'=>isset($data_now[$v]['ordersales'])?$data_now[$v]['ordersales']:0,
					'now_commission'=>isset($data_now[$v]['commission'])?$data_now[$v]['commission']:0,
			);
			$tmp['diff_ordernum'] = $tmp['now_ordernum']-$tmp['his_ordernum'];
			$tmp['diff_ordersales'] = $tmp['now_ordersales']-$tmp['his_ordersales'];
			$tmp['diff_commission'] = $tmp['now_commission']-$tmp['his_commission'];
			$tmp['per_ordernum'] = $tmp['his_ordernum']>0?number_format($tmp['now_ordernum']/$tmp['his_ordernum']*100,2,'.',''):0;
			$tmp['per_ordersales'] = $tmp['his_ordersales']>0?number_format($tmp['now_ordersales']/$tmp['his_ordersales']*100,2,'.',''):0;
			$tmp['per_commission'] = $tmp['his_commission']>0?number_format($tmp['now_commission']/$tmp['his_commission']*100,2,'.',''):0;

			$tmp['per_ordernum'] = $tmp['per_ordernum']-100;
			$tmp['per_ordersales'] = $tmp['per_ordersales']-100;
			$tmp['per_commission'] = $tmp['per_commission']-100;

			if($tmp['his_ordernum'] == 0 && $tmp['now_ordernum'] == 0 ){
				$tmp['per_ordernum'] = 0;
			}elseif($tmp['his_ordernum'] == 0 && $tmp['now_ordernum'] > 0){
				$tmp['per_ordernum'] = 100;
			}elseif($tmp['his_ordernum'] == 0 && $tmp['now_ordernum'] < 0){
				$tmp['per_ordernum'] = -100;
			}


			if($tmp['his_ordersales'] == 0 && $tmp['now_ordersales'] == 0 ){
				$tmp['per_ordersales'] = 0;
			}elseif($tmp['his_ordersales'] == 0 && $tmp['now_ordersales'] > 0){
				$tmp['per_ordersales'] = 100;
			}elseif($tmp['his_ordersales'] == 0 && $tmp['now_ordersales'] < 0){
				$tmp['per_ordersales'] = -100;
			}

			if($tmp['his_commission'] == 0 && $tmp['now_commission'] == 0 ){
				$tmp['per_commission'] = 0;
			}elseif($tmp['his_commission'] == 0 && $tmp['now_commission'] > 0){
				$tmp['per_commission'] = 100;
			}elseif($tmp['his_commission'] == 0 && $tmp['now_commission'] < 0){
				$tmp['per_commission'] = -100;
			}

			$aff_data[] = $tmp;
		}

		if(isset($data['orderby']) && $data['orderby']){
			switch ($data['orderby']) {
				case 'ordnumhis':
					$key = 'his_ordernum';
					break;
				case 'ordnumnow':
					$key = 'now_ordernum';
					break;
				case 'ordnumdiff':
					$key = 'diff_ordernum';
					break;
				case 'ordnumchg':
					$key = 'per_ordernum';
					break;
				case 'ordsalhis':
					$key = 'his_ordersales';
					break;
				case 'ordsalnow':
					$key = 'now_ordersales';
					break;
				case 'ordsaldiff':
					$key = 'diff_ordersales';
					break;
				case 'ordsalchg':
					$key = 'per_ordersales';
					break;
				case 'ordcomhis':
					$key = 'his_commission';
					break;
				case 'ordcomnow':
					$key = 'now_commission';
					break;
				case 'ordcomdiff':
					$key = 'diff_commission';
					break;
				case 'ordcomchg':
					$key = 'per_commission';
					break;
				default:
					break;
			}


			$sort_arr = array();
			foreach($aff_data as $v){
				$sort_arr[] = $v[$key];
			}

			array_multisort($sort_arr, SORT_DESC, $aff_data);
		}

		$total_data = array(
				'his_ordernum'=>0,
				'his_ordersales'=>0,
				'his_commission'=>0,
				'now_ordernum'=>0,
				'now_ordersales'=>0,
				'now_commission'=>0,
				'diff_ordernum'=>0,
				'diff_ordersales'=>0,
				'diff_commission'=>0,
		);
		foreach($aff_data as $k=>$v){
			$total_data['his_ordernum'] += $v['his_ordernum'];
			$total_data['his_ordersales'] += $v['his_ordersales'];
			$total_data['his_commission'] += $v['his_commission'];
			$total_data['now_ordernum'] += $v['now_ordernum'];
			$total_data['now_ordersales'] += $v['now_ordersales'];
			$total_data['now_commission'] += $v['now_commission'];
		}

		$total_data['diff_ordernum'] = $total_data['now_ordernum']-$total_data['his_ordernum'];
		$total_data['diff_ordersales'] = $total_data['now_ordersales']-$total_data['his_ordersales'];
		$total_data['diff_commission'] = $total_data['now_commission']-$total_data['his_commission'];

		$total_data['per_ordernum'] = $total_data['his_ordernum']>0?number_format($total_data['now_ordernum']/$total_data['his_ordernum']*100,2,'.',''):0;
		$total_data['per_ordersales'] = $total_data['his_ordersales']>0?number_format($total_data['now_ordersales']/$total_data['his_ordersales']*100,2,'.',''):0;
		$total_data['per_commission'] = $total_data['his_commission']>0?number_format($total_data['now_commission']/$total_data['his_commission']*100,2,'.',''):0;

		$total_data['per_ordernum'] = $total_data['per_ordernum']-100;
		$total_data['per_ordersales'] = $total_data['per_ordersales']-100;
		$total_data['per_commission'] = $total_data['per_commission']-100;

		if($total_data['his_ordernum'] == 0 && $total_data['now_ordernum'] == 0 ){
			$total_data['per_ordernum'] = 0;
		}elseif($total_data['his_ordernum'] == 0 && $total_data['now_ordernum'] > 0){
			$total_data['per_ordernum'] = 100;
		}elseif($total_data['his_ordernum'] == 0 && $total_data['now_ordernum'] < 0){
			$total_data['per_ordernum'] = -100;
		}


		if($total_data['his_ordersales'] == 0 && $total_data['now_ordersales'] == 0 ){
			$total_data['per_ordersales'] = 0;
		}elseif($total_data['his_ordersales'] == 0 && $total_data['now_ordersales'] > 0){
			$total_data['per_ordersales'] = 100;
		}elseif($total_data['his_ordersales'] == 0 && $total_data['now_ordersales'] < 0){
			$total_data['per_ordersales'] = -100;
		}

		if($total_data['his_commission'] == 0 && $total_data['now_commission'] == 0 ){
			$total_data['per_commission'] = 0;
		}elseif($total_data['his_commission'] == 0 && $total_data['now_commission'] > 0){
			$total_data['per_commission'] = 100;
		}elseif($total_data['his_commission'] == 0 && $total_data['now_commission'] < 0){
			$total_data['per_commission'] = -100;
		}

		$return_d = array();
		$return_d['aff_data'] = $aff_data;
		$return_d['total'] = $total_data;
		return $return_d;
	}

	function get_history_program_rpt($data,$page,$page_size=100){


		$where_arr = array();
		$where_his_arr = array();
		$where_now_arr = array();
		$where_his_str = '';
		$where_now_str = '';

		// if(!isset($data['af']) || empty($data['af'])){
		// 	return array();
		// }
		// $where_arr[] = 'af = "'.$data['af'].'"';

		$site = array();
		if(isset($data['pid']) && !empty($data['pid'])){
			$s = strpos($data['pid'] ,'(');
			$data['pid'] = trim($data['pid']);
			if($s !== false){
				$pid = trim(substr($data['pid'],$s+1,-1));//如果遇到()，去掉()并取出里面的值
			}else{
				$row = $this->table('publisher_account')->where('Alias = "'.addslashes($data['pid']).'"')->findone();
				if($row){
					$pid = $row['ID'];
				}else{
					$pid = 0;
				}
			}

			$where_str = 'ID = '.intval($pid);

			$sites_rows = $this->table('publisher_account')->where($where_str)->find();


			if(!empty($sites_rows)){
				foreach($sites_rows as $v){
					$site[] = addslashes($v['ApiKey']);
				}
			}
		}

		if(!empty($data['his_from'])){
			$where_his_arr[] = 'createddate >= "'.addslashes($data['his_from']).'"';
		}

		if(!empty($data['his_to'])){
			$where_his_arr[] = 'createddate <= "'.addslashes($data['his_to']).'"';
		}

		if(!empty($data['now_from'])){
			$where_now_arr[] = 'createddate >= "'.addslashes($data['now_from']).'"';
		}

		if(!empty($data['now_to'])){
			$where_now_arr[] = 'createddate <= "'.addslashes($data['now_to']).'"';
		}

		$where_arr[] = 'programId > 0';

		if(!empty($site)){
			$where_arr[] = 'site IN ("'.join('","',$site).'")';
		}
		$where_arr[] = 'revenues > 0';

		$where_his_str = join(' AND ',array_merge($where_his_arr,$where_arr));
		$where_his_str = $where_his_str?' WHERE '.$where_his_str:'';
		$where_now_str = join(' AND ',array_merge($where_now_arr,$where_arr));
		$where_now_str = $where_now_str?' WHERE '.$where_now_str:'';

		$where_b_str = '';
		if(isset($data['aff']) && !empty($data['aff'])){
			$row = $this->table('wf_aff')->where('Name = "'.addslashes($data['aff']).'"')->findone();
			if($row){
				$where_b_str = 'WHERE b.AffId = '.intval($row['ID']);
			}
		}

		$sql_his = 'SELECT a.* FROM (SELECT programId,SUM(orders) as ordernum,sum(sales) as ordersales,sum(revenues) as commission,sum(clicks) as clicks FROM statis_program_br '.$where_his_str.' GROUP BY programId) as a LEFT JOIN program as b ON a.programId = b.ID '.$where_b_str;
		// $sql_his = 'SELECT IdInAff,ProgramName,count(*) as ordernum,sum(Sales) as ordersales,sum(Commission) as commission FROM rpt_transaction_unique '.$where_his_str.' GROUP BY IdInAff';
		$row_his = $this->getRows($sql_his);

		$sql_now = 'SELECT a.* FROM (SELECT programId,SUM(orders) as ordernum,sum(sales) as ordersales,sum(revenues) as commission,sum(clicks) as clicks FROM statis_program_br '.$where_now_str.' GROUP BY programId) as a LEFT JOIN program as b ON a.programId = b.ID '.$where_b_str;
		// $sql_now = 'SELECT IdInAff,ProgramName,count(*) as ordernum,sum(Sales) as ordersales,sum(Commission) as commission FROM rpt_transaction_unique '.$where_now_str.' GROUP BY IdInAff';
		$row_now = $this->getRows($sql_now);

		$pids = array();
		$data_his = array();
		$data_now = array();
		foreach($row_his as $k=>$v){
			$pids[] = $v['programId'];
			$data_his[$v['programId']] = $v;
		}

		foreach($row_now as $k=>$v){
			if(!in_array($v['programId'],$pids)){ //in_array在数组中搜索固定值，存在，返回true
				$pids[] = $v['programId'];        //存放所有row_his和row_now中的IdInAff
			}
			$data_now[$v['programId']] = $v;
		}

		if(empty($pids))
			return array();

		$p_tmp = $this->getRows('SELECT ID,Name,IdInAff FROM program WHERE ID IN ('.join(',',$pids).')');
		$p_rows = array();
		foreach($p_tmp as $k=>$v){
			$p_rows[$v['ID']] = $v;
		}

		$aff_data = array();
		foreach($pids as $k=>$v){
			$tmp = array(
					'idinaff'=>isset($p_rows[$v]['IdInAff'])?$p_rows[$v]['IdInAff']:0,
					'name'=>isset($p_rows[$v]['Name'])?$p_rows[$v]['Name']:'',
					'his_ordernum'=>isset($data_his[$v]['ordernum'])?$data_his[$v]['ordernum']:0,
					'his_ordersales'=>isset($data_his[$v]['ordersales'])?$data_his[$v]['ordersales']:0,
					'his_commission'=>isset($data_his[$v]['commission'])?$data_his[$v]['commission']:0,
					'now_ordernum'=>isset($data_now[$v]['ordernum'])?$data_now[$v]['ordernum']:0,
					'now_ordersales'=>isset($data_now[$v]['ordersales'])?$data_now[$v]['ordersales']:0,
					'now_commission'=>isset($data_now[$v]['commission'])?$data_now[$v]['commission']:0,
			);
			$tmp['diff_ordernum'] = $tmp['now_ordernum']-$tmp['his_ordernum'];
			$tmp['diff_ordersales'] = $tmp['now_ordersales']-$tmp['his_ordersales'];
			$tmp['diff_commission'] = $tmp['now_commission']-$tmp['his_commission'];
			$tmp['per_ordernum'] = $tmp['his_ordernum']>0?number_format($tmp['now_ordernum']/$tmp['his_ordernum']*100,2,'.',''):0;
			$tmp['per_ordersales'] = $tmp['his_ordersales']>0?number_format($tmp['now_ordersales']/$tmp['his_ordersales']*100,2,'.',''):0;
			$tmp['per_commission'] = $tmp['his_commission']>0?number_format($tmp['now_commission']/$tmp['his_commission']*100,2,'.',''):0;

			$tmp['per_ordernum'] = $tmp['per_ordernum']-100;
			$tmp['per_ordersales'] = $tmp['per_ordersales']-100;
			$tmp['per_commission'] = $tmp['per_commission']-100;

			if($tmp['his_ordernum'] == 0 && $tmp['now_ordernum'] == 0 ){
				$tmp['per_ordernum'] = 0;
			}elseif($tmp['his_ordernum'] == 0 && $tmp['now_ordernum'] > 0){
				$tmp['per_ordernum'] = 100;
			}elseif($tmp['his_ordernum'] == 0 && $tmp['now_ordernum'] < 0){
				$tmp['per_ordernum'] = -100;
			}


			if($tmp['his_ordersales'] == 0 && $tmp['now_ordersales'] == 0 ){
				$tmp['per_ordersales'] = 0;
			}elseif($tmp['his_ordersales'] == 0 && $tmp['now_ordersales'] > 0){
				$tmp['per_ordersales'] = 100;
			}elseif($tmp['his_ordersales'] == 0 && $tmp['now_ordersales'] < 0){
				$tmp['per_ordersales'] = -100;
			}

			if($tmp['his_commission'] == 0 && $tmp['now_commission'] == 0 ){
				$tmp['per_commission'] = 0;
			}elseif($tmp['his_commission'] == 0 && $tmp['now_commission'] > 0){
				$tmp['per_commission'] = 100;
			}elseif($tmp['his_commission'] == 0 && $tmp['now_commission'] < 0){
				$tmp['per_commission'] = -100;
			}

			$aff_data[] = $tmp;
		}

		if(isset($data['orderby']) && $data['orderby']){
			switch ($data['orderby']) {
				case 'ordnumhis':
					$key = 'his_ordernum';
					break;
				case 'ordnumnow':
					$key = 'now_ordernum';
					break;
				case 'ordnumdiff':
					$key = 'diff_ordernum';
					break;
				case 'ordnumchg':
					$key = 'per_ordernum';
					break;
				case 'ordsalhis':
					$key = 'his_ordersales';
					break;
				case 'ordsalnow':
					$key = 'now_ordersales';
					break;
				case 'ordsaldiff':
					$key = 'diff_ordersales';
					break;
				case 'ordsalchg':
					$key = 'per_ordersales';
					break;
				case 'ordcomhis':
					$key = 'his_commission';
					break;
				case 'ordcomnow':
					$key = 'now_commission';
					break;
				case 'ordcomdiff':
					$key = 'diff_commission';
					break;
				case 'ordcomchg':
					$key = 'per_commission';
					break;
				default:
					break;
			}

			$sort_arr = array();
			foreach($aff_data as $v){
				$sort_arr[] = $v[$key];
			}

			array_multisort($sort_arr, SORT_DESC, $aff_data);//$sort_arr降序排列，$aff_data升序排列
		}

		$return_d = array();
		$return_d['page_now'] = $page;
		$return_d['page_total'] = ceil(count($pids)/$page_size);
		$aff_data = array_slice($aff_data,($page-1)*$page_size,$page_size);

		if(isset($data['minorder']) && intval($data['minorder']) > 0){
			$minorder = intval($data['minorder']);
			foreach($aff_data as $k=>$v){
				if($v['his_ordernum'] < $minorder){
					unset($aff_data[$k]);
				}
			}
		}


		$total_data = array(
				'his_ordernum'=>0,
				'his_ordersales'=>0,
				'his_commission'=>0,
				'now_ordernum'=>0,
				'now_ordersales'=>0,
				'now_commission'=>0,
				'diff_ordernum'=>0,
				'diff_ordersales'=>0,
				'diff_commission'=>0,
		);
		foreach($aff_data as $k=>$v){
			$total_data['his_ordernum'] += $v['his_ordernum'];
			$total_data['his_ordersales'] += $v['his_ordersales'];
			$total_data['his_commission'] += $v['his_commission'];
			$total_data['now_ordernum'] += $v['now_ordernum'];
			$total_data['now_ordersales'] += $v['now_ordersales'];
			$total_data['now_commission'] += $v['now_commission'];
		}

		$total_data['diff_ordernum'] = $total_data['now_ordernum']-$total_data['his_ordernum'];
		$total_data['diff_ordersales'] = $total_data['now_ordersales']-$total_data['his_ordersales'];
		$total_data['diff_commission'] = $total_data['now_commission']-$total_data['his_commission'];

		$total_data['per_ordernum'] = $total_data['his_ordernum']>0?number_format($total_data['now_ordernum']/$total_data['his_ordernum']*100,2,'.',''):0;
		$total_data['per_ordersales'] = $total_data['his_ordersales']>0?number_format($total_data['now_ordersales']/$total_data['his_ordersales']*100,2,'.',''):0;
		$total_data['per_commission'] = $total_data['his_commission']>0?number_format($total_data['now_commission']/$total_data['his_commission']*100,2,'.',''):0;

		$total_data['per_ordernum'] = $total_data['per_ordernum']-100;
		$total_data['per_ordersales'] = $total_data['per_ordersales']-100;
		$total_data['per_commission'] = $total_data['per_commission']-100;

		if($total_data['his_ordernum'] == 0 && $total_data['now_ordernum'] == 0 ){
			$total_data['per_ordernum'] = 0;
		}elseif($total_data['his_ordernum'] == 0 && $total_data['now_ordernum'] > 0){
			$total_data['per_ordernum'] = 100;
		}elseif($total_data['his_ordernum'] == 0 && $total_data['now_ordernum'] < 0){
			$total_data['per_ordernum'] = -100;
		}


		if($total_data['his_ordersales'] == 0 && $total_data['now_ordersales'] == 0 ){
			$total_data['per_ordersales'] = 0;
		}elseif($total_data['his_ordersales'] == 0 && $total_data['now_ordersales'] > 0){
			$total_data['per_ordersales'] = 100;
		}elseif($total_data['his_ordersales'] == 0 && $total_data['now_ordersales'] < 0){
			$total_data['per_ordersales'] = -100;
		}

		if($total_data['his_commission'] == 0 && $total_data['now_commission'] == 0 ){
			$total_data['per_commission'] = 0;
		}elseif($total_data['his_commission'] == 0 && $total_data['now_commission'] > 0){
			$total_data['per_commission'] = 100;
		}elseif($total_data['his_commission'] == 0 && $total_data['now_commission'] < 0){
			$total_data['per_commission'] = -100;
		}

		$return_d['aff_data'] = $aff_data;
		$return_d['total'] = $total_data;

		return $return_d;
	}

	function get_history_domain_rpt($data,$page,$page_size=100){

		$where_arr = array();
		$where_his_arr = array();
		$where_now_arr = array();
		$where_his_str = '';
		$where_now_str = '';

		$where_arr_click = array();
		$where_his_arr_click = array();
		$where_now_arr_click = array();
		$where_his_str_click = '';
		$where_now_str_click = '';

		//-----------------------------------------------pid搜索-------------------------------------------------------------------
		$alias = array();
		if(isset($data['pid']) && !empty($data['pid'])){
			$s = strpos($data['pid'] ,'(');
			$data['pid'] = trim($data['pid']);
			if($s !== false){
				$pid = trim(substr($data['pid'],$s+1,-1));//如果遇到()，去掉()并取出里面的值
			}else{
				$row = $this->table('publisher_account')->where('Alias = "'.addslashes($data['pid']).'"')->findone();
				if($row){
					$pid = $row['ID'];
				}else{
					$pid = 0;
				}
			}

			$where_str = 'ID = '.intval($pid);

			$sites_rows = $this->table('publisher_account')->where($where_str)->find();


			if(!empty($sites_rows)){

				foreach($sites_rows as $v){
					$site[] = addslashes($v['ApiKey']);
				}

			}
		}

		//-----------------------------------------------domain搜索-------------------------------------------------------------------

		if(isset($data['domain']) && !empty($data['domain'])){
			$row_d = $this->getRows('SELECT ID FROM domain WHERE Domain Like "'.addslashes($data['domain']).'%"');
			if($row_d){
				$domainids = array();
				foreach($row_d as $v){
					$domainids[] = $v['ID'];
				}
				$where_arr[] = 'domainId IN ('.join(',',$domainids).')';
			}else{
				$where_arr[] = 'domainId > 0';
			}
		}else{
			$where_arr[] = 'domainId > 0';
		}

		if(!empty($site)){
			$where_arr[] = 'Site IN ("'.join('","',$site).'")';
		}

		$where_arr[] = 'revenues > 0';

		if(!empty($data['his_from'])){
			$where_his_arr[] = 'createddate >= "'.addslashes($data['his_from']).'"';
			$where_his_arr_click[] = 'createddate >= "'.addslashes($data['his_from']).'"';
		}

		if(!empty($data['his_to'])){
			$where_his_arr[] = 'createddate <= "'.addslashes($data['his_to']).'"';
			$where_his_arr_click[] = 'createddate <= "'.addslashes($data['his_to']).'"';
		}

		if(!empty($data['now_from'])){
			$where_now_arr[] = 'createddate >= "'.addslashes($data['now_from']).'"';
			$where_now_arr_click[] = 'createddate >= "'.addslashes($data['now_from']).'"';
		}

		if(!empty($data['now_to'])){
			$where_now_arr[] = 'createddate <= "'.addslashes($data['now_to']).'"';
			$where_now_arr_click[] = 'createddate <= "'.addslashes($data['now_to']).'"';
		}

		$where_his_str = join(' AND ',array_merge($where_his_arr,$where_arr));
		$where_his_str = $where_his_str?' WHERE '.$where_his_str:'';
		$where_now_str = join(' AND ',array_merge($where_now_arr,$where_arr));
		$where_now_str = $where_now_str?' WHERE '.$where_now_str:'';

		$sql_his = 'SELECT domainId,SUM(orders) as ordernum,sum(sales) as ordersales,sum(revenues) as commission,sum(clicks) as clicks FROM statis_domain_br '.$where_his_str.' GROUP BY domainId';
		// $sql_his = 'SELECT domainUsed,count(*) as ordernum,sum(Sales) as ordersales,sum(Commission) as commission FROM rpt_transaction_unique '.$where_his_str.' AND domainUsed <> "" GROUP BY domainUsed';
		$row_his = $this->getRows($sql_his);

		$sql_now = 'SELECT domainId,SUM(orders) as ordernum,sum(sales) as ordersales,sum(revenues) as commission,sum(clicks) as clicks FROM statis_domain_br '.$where_now_str.' GROUP BY domainId';
		// $sql_now = 'SELECT domainUsed,count(*) as ordernum,sum(Sales) as ordersales,sum(Commission) as commission FROM rpt_transaction_unique '.$where_now_str.' AND domainUsed <> "" GROUP BY domainUsed';
		$row_now = $this->getRows($sql_now);
// 		echo $sql_his;
// 		echo "<br />";
// 		echo $sql_now;
// 		echo "<br />";
		$dids = array();
		$data_his = array();
		$data_now = array();
		foreach($row_his as $k=>$v){
			$dids[] = $v['domainId'];
			$data_his[$v['domainId']] = $v;
		}

		foreach($row_now as $k=>$v){
			if(!in_array($v['domainId'],$dids)){ //in_array在数组中搜索固定值，存在，返回true
				$dids[] = $v['domainId'];        //存放所有row_his和row_now中的IdInAff
			}
			$data_now[$v['domainId']] = $v;
		}
		sort($dids);                           //sort() 函数按升序对给定数组的值排序

		if(empty($dids))
			return array();

		$d_tmp = $this->getRows('SELECT ID,Domain FROM domain WHERE ID IN ('.join(',',$dids).')');
		$d_rows = array();
		foreach($d_tmp as $k=>$v){
			$d_rows[$v['ID']] = $v['Domain'];
		}

		$aff_data = array();
		foreach($dids as $k=>$v){
			$tmp = array(
					'name'=>$d_rows[$v],
					'his_ordernum'=>isset($data_his[$v]['ordernum'])?$data_his[$v]['ordernum']:0,
					'his_ordersales'=>isset($data_his[$v]['ordersales'])?$data_his[$v]['ordersales']:0,
					'his_commission'=>isset($data_his[$v]['commission'])?$data_his[$v]['commission']:0,
					'his_click'=>isset($data_his[$v]['clicks'])?$data_his[$v]['clicks']:0,
					'now_ordernum'=>isset($data_now[$v]['ordernum'])?$data_now[$v]['ordernum']:0,
					'now_ordersales'=>isset($data_now[$v]['ordersales'])?$data_now[$v]['ordersales']:0,
					'now_commission'=>isset($data_now[$v]['commission'])?$data_now[$v]['commission']:0,
					'now_click'=>isset($data_now[$v]['clicks'])?$data_now[$v]['clicks']:0,
			);
			$tmp['diff_ordernum'] = $tmp['now_ordernum']-$tmp['his_ordernum'];
			$tmp['diff_ordersales'] = $tmp['now_ordersales']-$tmp['his_ordersales'];
			$tmp['diff_commission'] = $tmp['now_commission']-$tmp['his_commission'];
			$tmp['diff_click'] = $tmp['now_click']-$tmp['his_click'];






			$tmp['per_ordernum'] = $tmp['his_ordernum']>0?number_format($tmp['now_ordernum']/$tmp['his_ordernum']*100,2,'.',''):0;
			$tmp['per_ordersales'] = $tmp['his_ordersales']>0?number_format($tmp['now_ordersales']/$tmp['his_ordersales']*100,2,'.',''):0;
			$tmp['per_commission'] = $tmp['his_commission']>0?number_format($tmp['now_commission']/$tmp['his_commission']*100,2,'.',''):0;
			$tmp['per_click'] = $tmp['his_click']>0?number_format($tmp['now_click']/$tmp['his_click']*100,2,'.',''):0;

			$tmp['per_ordernum'] = $tmp['per_ordernum']-100;
			$tmp['per_ordersales'] = $tmp['per_ordersales']-100;
			$tmp['per_commission'] = $tmp['per_commission']-100;
			$tmp['per_click'] = $tmp['per_click']-100;



			if($tmp['his_ordernum'] == 0 && $tmp['now_ordernum'] == 0 ){
				$tmp['per_ordernum'] = 0;
			}elseif($tmp['his_ordernum'] == 0 && $tmp['now_ordernum'] > 0){
				$tmp['per_ordernum'] = 100;
			}elseif($tmp['his_ordernum'] == 0 && $tmp['now_ordernum'] < 0){
				$tmp['per_ordernum'] = -100;
			}

			if($tmp['his_ordersales'] == 0 && $tmp['now_ordersales'] == 0 ){
				$tmp['per_ordersales'] = 0;
			}elseif($tmp['his_ordersales'] == 0 && $tmp['now_ordersales'] > 0){
				$tmp['per_ordersales'] = 100;
			}elseif($tmp['his_ordersales'] == 0 && $tmp['now_ordersales'] < 0){
				$tmp['per_ordersales'] = -100;
			}

			if($tmp['his_commission'] == 0 && $tmp['now_commission'] == 0 ){
				$tmp['per_commission'] = 0;
			}elseif($tmp['his_commission'] == 0 && $tmp['now_commission'] > 0){
				$tmp['per_commission'] = 100;
			}elseif($tmp['his_commission'] == 0 && $tmp['now_commission'] < 0){
				$tmp['per_commission'] = -100;
			}

			if($tmp['his_click'] == 0 && $tmp['now_click'] == 0 ){
				$tmp['per_click'] = 0;
			}elseif($tmp['his_click'] == 0 && $tmp['now_click'] > 0){
				$tmp['per_click'] = 100;
			}elseif($tmp['his_click'] == 0 && $tmp['now_click'] < 0){
				$tmp['per_click'] = -100;
			}



//-----------------------------------------------------CR的计算---------------------------------------------------------------------------

			$tmp['his_cr'] = $tmp['his_ordersales']>0?number_format($tmp['his_commission']/$tmp['his_ordersales']*100,2,'.',''):0;
			$tmp['now_cr'] = $tmp['now_ordersales']>0?number_format($tmp['now_commission']/$tmp['now_ordersales']*100,2,'.',''):0;


			if($tmp['his_commission'] == 0 && $tmp['his_ordersales'] == 0 ){
				$tmp['his_cr'] = 0;
			}
			if($tmp['now_commission'] == 0 && $tmp['now_ordersales'] == 0 ){
				$tmp['now_cr'] = 0;
			}


			$tmp['diff_cr'] = $tmp['now_cr']-$tmp['his_cr'];//可能为负数
			$tmp['per_cr'] = $tmp['his_cr']>0?number_format($tmp['now_cr']/$tmp['his_cr']*100,2,'.',''):0;//只有分母大于零，才能用这个公式，分母等于零的情况，在下面
			$tmp['per_cr'] = $tmp['per_cr']-100;

			if($tmp['his_cr'] == 0 && $tmp['now_cr'] == 0 ){
				$tmp['per_cr'] = 0;
			}elseif($tmp['his_cr'] == 0 && $tmp['now_cr'] > 0){
				$tmp['per_cr'] = 100;
			}











			$aff_data[$v] = $tmp;
		}



		//---------------------------------------------------------click计算---------------------------------------------------------------------------
		// $sql_his_click = 'SELECT COUNT(*) AS click,domainUsed FROM bd_out_tracking '.$where_his_str_click.' AND domainUsed <> "" GROUP BY domainUsed';
		// $row_his_click = $this->getRows($sql_his_click);

		// $sql_now_click = 'SELECT COUNT(*) AS click,domainUsed FROM bd_out_tracking '.$where_now_str_click.' AND domainUsed <> "" GROUP BY domainUsed';
		// $row_now_click = $this->getRows($sql_now_click);
		// // 		echo $sql_his_click;
		// // 		echo "<br />";
		// // 		echo $sql_now_click;
		// // 		echo "<br />";
		// $affs_click = array();
		// $data_his_click = array();
		// $data_now_click = array();
		// foreach($row_his_click as $k=>$v){

		// 	$data_his_click[$v['domainUsed']] = $v['click'];
		// }

		// foreach($row_now_click as $k=>$v){

		// 	$data_now_click[$v['domainUsed']] = $v['click'];
		// }




		// 		foreach ($aff_data as &$v){
		// 			$v['his_click'] = isset($data_his_click[$v['name']])?$data_his_click[$v['name']]:0 ;
		// 			$v['now_click'] = isset($data_now_click[$v['name']])?$data_now_click[$v['name']]:0 ;
		// 			$v['diff_click'] = $v['now_click']-$v['his_click'];
		// 			$v['per_click'] = $v['his_click']>0?number_format($v['diff_click']/$v['his_click']*100,2,'.',''):100;
		// 		}







//-----------------------------------------------------------------------------------------total的计算------------------------------------------------------------------------


		$total_data = array(
				'his_ordernum'=>0,
				'his_ordersales'=>0,
				'his_commission'=>0,
				'his_click'=>0,
				'now_ordernum'=>0,
				'now_ordersales'=>0,
				'now_commission'=>0,
				'now_click'=>0,
				'diff_ordernum'=>0,
				'diff_ordersales'=>0,
				'diff_commission'=>0,
				'diff_click'=>0,
				'per_click'=>0
		);
		foreach($aff_data as $k=>$v){
			$total_data['his_ordernum'] += $v['his_ordernum'];
			$total_data['his_ordersales'] += $v['his_ordersales'];
			$total_data['his_commission'] += $v['his_commission'];
			$total_data['his_click'] += $v['his_click'];

			$total_data['now_ordernum'] += $v['now_ordernum'];
			$total_data['now_ordersales'] += $v['now_ordersales'];
			$total_data['now_commission'] += $v['now_commission'];
			$total_data['now_click'] += $v['now_click'];


		}

		$total_data['diff_ordernum'] = $total_data['now_ordernum']-$total_data['his_ordernum'];
		$total_data['diff_ordersales'] = $total_data['now_ordersales']-$total_data['his_ordersales'];
		$total_data['diff_commission'] = $total_data['now_commission']-$total_data['his_commission'];
		$total_data['diff_click'] = $total_data['now_click']-$total_data['his_click'];


		$total_data['per_ordernum'] = $total_data['his_ordernum']>0?number_format($total_data['now_ordernum']/$total_data['his_ordernum']*100,2,'.',''):0;
		$total_data['per_ordersales'] = $total_data['his_ordersales']>0?number_format($total_data['now_ordersales']/$total_data['his_ordersales']*100,2,'.',''):0;
		$total_data['per_commission'] = $total_data['his_commission']>0?number_format($total_data['now_commission']/$total_data['his_commission']*100,2,'.',''):0;
		$total_data['per_click'] = $total_data['his_click']>0?number_format($total_data['now_click']/$total_data['his_click']*100,2,'.',''):0;

		$total_data['per_ordernum'] = $total_data['per_ordernum']-100;
		$total_data['per_ordersales'] = $total_data['per_ordersales']-100;
		$total_data['per_commission'] = $total_data['per_commission']-100;
		$total_data['per_click'] = $total_data['per_click']-100;

		if($total_data['his_ordernum'] == 0 && $total_data['now_ordernum'] == 0 ){
			$total_data['per_ordernum'] = 0;
		}elseif($total_data['his_ordernum'] == 0 && $total_data['now_ordernum'] > 0){
			$total_data['per_ordernum'] = 100;
		}elseif($total_data['his_ordernum'] == 0 && $total_data['now_ordernum'] < 0){
			$total_data['per_ordernum'] = -100;
		}


		if($total_data['his_ordersales'] == 0 && $total_data['now_ordersales'] == 0 ){
			$total_data['per_ordersales'] = 0;
		}elseif($total_data['his_ordersales'] == 0 && $total_data['now_ordersales'] > 0){
			$total_data['per_ordersales'] = 100;
		}elseif($total_data['his_ordersales'] == 0 && $total_data['now_ordersales'] < 0){
			$total_data['per_ordersales'] = -100;
		}

		if($total_data['his_commission'] == 0 && $total_data['now_commission'] == 0 ){
			$total_data['per_commission'] = 0;
		}elseif($total_data['his_commission'] == 0 && $total_data['now_commission'] > 0){
			$total_data['per_commission'] = 100;
		}elseif($total_data['his_commission'] == 0 && $total_data['now_commission'] < 0){
			$total_data['per_commission'] = -100;
		}

		if($total_data['his_click'] == 0 && $total_data['now_click'] == 0 ){
			$total_data['per_click'] = 0;
		}elseif($total_data['his_click'] == 0 && $total_data['now_click'] > 0){
			$total_data['per_click'] = 100;
		}

		//---------------------------------------------------------cr的total计算-------------------------------------------------------------------
		$total_data['his_cr'] = $total_data['his_ordersales']>0?number_format($total_data['his_commission']/$total_data['his_ordersales']*100,2,'.',''):0;
		$total_data['now_cr'] = $total_data['now_ordersales']>0?number_format($total_data['now_commission']/$total_data['now_ordersales']*100,2,'.',''):0;
		// 		echo $total_data['his_cr'];
		// 		echo "<br/>";
		if($total_data['his_commission'] == 0 && $total_data['his_ordersales'] == 0 ){
			$total_data['his_cr'] = 0;
		}
		if($total_data['now_commission'] == 0 && $total_data['now_ordersales'] == 0 ){
			$total_data['now_cr'] = 0;
		}
		$total_data['diff_cr'] = $total_data['now_cr']-$total_data['his_cr'];//可能为负数
		$total_data['per_cr'] = $total_data['his_cr']>0?number_format($total_data['now_cr']/$total_data['his_cr']*100,2,'.',''):0;//只有分母大于零，才能用这个公式，分母等于零的情况，在下面
		$total_data['per_cr'] = $total_data['per_cr']-100;

		if($total_data['his_cr'] == 0 && $total_data['now_cr'] == 0 ){
			$total_data['per_cr'] = 0;
		}elseif($total_data['his_cr'] == 0 && $total_data['now_cr'] > 0){
			$total_data['per_cr'] = 100;
		}







		if(isset($data['orderby']) && $data['orderby']){
			switch ($data['orderby']) {
				case 'ordnumhis':
					$key = 'his_ordernum';
					break;
				case 'ordnumnow':
					$key = 'now_ordernum';
					break;
				case 'ordnumdiff':
					$key = 'diff_ordernum';
					break;
				case 'ordnumchg':
					$key = 'per_ordernum';
					break;
				case 'ordsalhis':
					$key = 'his_ordersales';
					break;
				case 'ordsalnow':
					$key = 'now_ordersales';
					break;
				case 'ordsaldiff':
					$key = 'diff_ordersales';
					break;
				case 'ordsalchg':
					$key = 'per_ordersales';
					break;
				case 'ordcomhis':
					$key = 'his_commission';
					break;
				case 'ordcomnow':
					$key = 'now_commission';
					break;
				case 'ordcomdiff':
					$key = 'diff_commission';
					break;
				case 'ordcomchg':
					$key = 'per_commission';
					break;
				case 'ordcrhis':
					$key = 'his_cr';
					break;
				case 'ordcrnow':
					$key = 'now_cr';
					break;
				case 'ordcrdiff':
					$key = 'diff_cr';
					break;
				case 'ordcrchg':
					$key = 'per_cr';
					break;
				case 'ordclihis':
					$key = 'his_click';
					break;
				case 'ordclinow':
					$key = 'now_click';
					break;
				case 'ordclidiff':
					$key = 'diff_click';
					break;
				case 'ordclichg':
					$key = 'per_click';
					break;
				default:
					break;
			}

			$sort_arr = array();
			foreach($aff_data as $v){
				$sort_arr[] = $v[$key];
			}
			if ($data['order'] == 'descend'){
				array_multisort($sort_arr, SORT_DESC, $aff_data);
			}else {
				array_multisort($sort_arr, SORT_ASC, $aff_data);
			}

		}




		if(isset($data['minorder']) && intval($data['minorder']) > 0){
			$minorder = intval($data['minorder']);
			foreach($aff_data as $k=>$v){
				if($v['his_ordernum'] < $minorder){
					unset($aff_data[$k]);
				}
			}
		}





		$return_d = array();
		$return_d['page_now'] = $page;
		$return_d['page_total'] = ceil(count($aff_data)/$page_size);
		$aff_data = array_slice($aff_data,($page-1)*$page_size,$page_size);








// echo "<pre>";
// print_r($aff_data);




		$return_d['aff_data'] = $aff_data;
		$return_d['total'] = $total_data;

		return $return_d;
	}


	function save_upload_data(){
		$res = call_sys_api('transaction.upload',array('method'=>'go'));
		return $res;
	}

	function get_upload_info($save_upload_file){
		foreach($save_upload_file as $k=>$v){
			if(!$v['res'])
				continue;

			$res = call_sys_api('transaction.upload',array('file'=>$v['file'],'method'=>'info','datafile'=>$v['datafile']));
			$save_upload_file[$k]['infomation'] = $res;
			$this->query('INSERT INTO rpt_transaction_upload SET old_file_name = "'.addslashes($v['oldname']).'",file_path = "'.$v['file'].'",datafile="'.$v['datafile'].'",created = "'.date('Y-m-d H:i:s').'",status="info"');

		}

		return $save_upload_file;
	}

	function clear_upload_tmp(){
		$res = call_sys_api('transaction.upload',array('method'=>'clear'));
	}
	function getDailySum($para,$days = 29){
		$check = 0;
		$data = array();
//		if(isset($para['start']) && !empty($para['start'])){
//			$st = strtotime($para['start']);
//			$check = 1;
//		}else{
//			$st = strtotime(date('Y-m-d'));
//
//		}
//		if(isset($para['end']) && !empty($para['end'])){
//			$ed = strtotime($para['end']);
//			$check = 1;
//		}else{
//			$ed = strtotime(date('Y-m-d'));
//		}
		if($check == 1){
			//$days =  round(($ed-$st)/3600/24);
			for($i = 0;$i <= $days;$i++){
				$d = date('Y-m-d', strtotime("{$para['end']}-$i day"));
				$sql = "SELECT SUM(Commission) AS `Commission`,CreatedDate AS `Date` FROM rpt_transaction_unique  WHERE CreatedDate = '$d' AND Af NOT IN ('bdg','mk','mega')";
				echo $sql;
				die;
				$arr[$i] = $this->getRows($sql);
			}
			foreach($arr as $v=>$k){
				$data[$v]['Date'] = $k[0]['Date'];
				$data[$v]['Commission'] = sprintf('%.2f',$k[0]['Commission']);
			}
		}else{

			for($i = 0;$i <= 29;$i++){
				$d = date('Y-m-d', strtotime("-$i day"));
				$sql = "SELECT SUM(Commission) AS `Commission`,CreatedDate AS `Date` FROM rpt_transaction_unique  WHERE CreatedDate = '$d' AND Af NOT IN ('bdg','mk','mega')";
				$arr[$i] = $this->getRows($sql);
			}
			foreach($arr as $v=>$k){
				$data[$v]['Date'] = $k[0]['Date'];
				$data[$v]['Commission'] = sprintf('%.2f',$k[0]['Commission']);
			}
		}
		//	return $data;
		print_r($data);
		die;
	}
	function getDailyTransaction($filter)
	{
		$keyid = '';
		if(strtotime($filter['tran_from']) > strtotime($filter['tran_to']))
			return array();
		if($filter['type'] == 1){
			$mkWhereSql = mk_publisher_where();
			$sql = "SELECT b.`ApiKey` FROM publisher AS a LEFT JOIN publisher_account AS b ON a.`ID` = b.`PublisherId` WHERE $mkWhereSql AND b.ApiKey IS NOT NULL";
			$res = $this->getRows($sql);
			if(!empty($res)){
				$keyid=' and site NOT IN(';
				foreach($res as $k){
					$keyid.='"'.$k['ApiKey'].'",';
				}
				$keyid = rtrim($keyid,',').")";
			}
		}
		$sql = "SELECT DISTINCT AffId,wf_aff.Name AS `Af` FROM rpt_transaction_unique p LEFT JOIN wf_aff ON wf_aff.ID = p.AffId WHERE p.CreatedDate >='".$filter['tran_from']."' AND p.CreatedDate <= '".$filter['tran_to']."' AND p.Af NOT IN ('bdg','mk','mega') $keyid order by p.Af";
		$Aff = $this->getRows($sql);

		$list =array();
		$d = $filter['tran_from'];
		while(strtotime($d) <= strtotime($filter['tran_to'])){
			foreach($Aff as $all){
				$list[$all['Af']][$d] = 0;
			}
			$sql = "SELECT SUM(p.Commission) AS COUNT,wf_aff.Name AS `Af`FROM rpt_transaction_unique p LEFT JOIN wf_aff ON wf_aff.ID = p.AffId WHERE p.CreatedDate = '$d' AND p.Af NOT IN ('bdg','mk','mega') $keyid GROUP BY p.Af";
			$arr[$d] = $this->getRows($sql);
			foreach($arr[$d] as $data){
				$list[$data['Af']][$d] = $data['COUNT'];
			}
			$d = date('Y-m-d',strtotime($d.' +1 day'));
		}
		return $list;
	}



	function getAffOrStoreDailyTransaction($data)
	{
		//按日期统计-新统计方式:
		$sObj = new AllCalculation();
		$sObj->setStartDate($data['sdate']);
		$sObj->setEndDate($data['edate']);
		$exceptSiteArray  = array();
		if ($data['datatype'] != 2) {
			$mkWhereSql = mk_publisher_where();
			$sql = "SELECT b.`ApiKey` FROM publisher AS a LEFT JOIN publisher_account AS b ON a.`ID` = b.`PublisherId` WHERE $mkWhereSql AND b.ApiKey IS NOT NULL";
			$res = $this->getRows($sql);
			foreach ($res as $apiKey) {
				$aKey = isset($apiKey['ApiKey']) ? $apiKey['ApiKey'] : null;
				if (empty($aKey)) {
					continue;
				}
				array_push($exceptSiteArray,$aKey);
			}
		}
		$sObj->setExceptSite($exceptSiteArray);
		!empty($data['affid']) && $sObj->setAffiliate(array($data['affid']));
		$cObj = new Calculation();
		$cObj->publisherSite = $data['site'];
		$cObj->publisherSiteType = $data['sitetype'];
		$cObj->advertiserKeyword = $data['store'];
		$cObj->advertiserId = isset($data['storeid']) ? $data['storeid'] : 0;
		$cObj->advertiserCooperationStatus = $data['ctype'];
		$siteSql = $cObj->doPublisherQuery();
		$sObj->setSiteSql($siteSql);
		if (!empty($data['country'])) {
			$countries = explode(',', $data['country']);
			$sObj->setCountry($countries);
		}
		$sObj->setDateType($data['timetype']);
		$domain = $cObj->doAdvertiserQuery('domain');
		$sObj->setDomainSql($domain);
		$calRows = $sObj->doCalculate('Date',false);

		return $calRows;
	}

	function getDailyTransactionAff($days = 9){
		$day = date('Y-m-d', strtotime("-$days day"));
		$sql = "SELECT DISTINCT Af FROM rpt_transaction_unique WHERE CreatedDate >='$day' AND af != 'mega' and af != 'mk'";
		$Aff = $this->getRows($sql);
		$list_br =array();
		for($i = 0;$i <= $days;$i++){
			$d = date('Y-m-d', strtotime("-$i day"));
			foreach($Aff as $all){
				$list_br[$all['Af']][$d] = 0;
			}
			$sql = "SELECT SUM(Commission) AS COUNT,Af FROM rpt_transaction_unique  WHERE CreatedDate = '$d' and af != 'mega' and af != 'mk' GROUP BY Af";
			$arr[$i] = $this->getRows($sql);
			foreach($arr[$i] as $data){
				$list_br[$data['Af']][$d] = $data['COUNT'];
			}
		}

		/*
$db_bdg01 = new Mysql('bdg_go_base', 'bdg01.mgsvr.com', 'bdg_slave', 'SHDbdsg32B');
$sql = "SELECT DISTINCT Af FROM rpt_transaction_unique WHERE CreatedDate >='$day' AND Site = '34173cb38f07f89ddbebc2ac9128303f'";
$Aff = $db_bdg01->getRows($sql);
$list_bdg =array();
for($i = 0;$i <= $days;$i++){
    $d = date('Y-m-d', strtotime("-$i day"));
    foreach($Aff as $all){
        $list_bdg[$all['Af']][$d] = 0;
    }
    $sql = "SELECT SUM(Commission) AS COUNT,Af FROM rpt_transaction_unique  WHERE CreatedDate = '$d'AND Site = '34173cb38f07f89ddbebc2ac9128303f' GROUP BY Af";
    $arr[$i] = $db_bdg01->getRows($sql);
    foreach($arr[$i] as $data){
        $list_bdg[$data['Af']][$d] = $data['COUNT'];
    }
}
        */

		$db_mk = new Mysql('bdg_go_base', 'bdg01.bwe.io', 'bdg_go', 'sh@#!azS81m');
		$sql = "SELECT DISTINCT Af FROM rpt_transaction_unique WHERE CreatedDate >='$day'";
		$Aff = $db_mk->getRows($sql);
		$list_mk =array();
		for($i = 0;$i <= $days;$i++){
			$d = date('Y-m-d', strtotime("-$i day"));
			foreach($Aff as $all){
				$list_mk[$all['Af']][$d] = 0;
			}
			$sql = "SELECT SUM(Commission) AS COUNT,Af FROM rpt_transaction_unique  WHERE CreatedDate = '$d' GROUP BY Af";
			$arr[$i] = $db_mk->getRows($sql);
			foreach($arr[$i] as $data){
				$list_mk[$data['Af']][$d] = $data['COUNT'];
			}
		}

		foreach($list_br as $af=>$data){
			foreach($data as $date=>$commission){
				if(isset($list_mk[$af]) && isset($list_mk[$af][$date])){
					$list_mk[$af][$date] = bcadd($list_mk[$af][$date],$commission,4);
				}else{
					$list_mk[$af][$date] = $commission;
				}
			}
		}

		/*
                foreach($list_bdg as $af=>$data){
                    foreach($data as $date=>$commission){
                        if(isset($list_mk[$af]) && isset($list_mk[$af][$date])){
                            $list_mk[$af][$date] = bcadd($list_mk[$af][$date],$commission,4);
                        }else{
                            $list_mk[$af][$date] = $commission;
                        }
                    }
                }
        */

		return $list_mk;
	}

	function getDailyTransactionSite($days = 9){
		$day = date('Y-m-d', strtotime("-$days day"));
		$sql = "SELECT DISTINCT Alias FROM rpt_transaction_unique WHERE CreatedDate >='$day' AND Alias != 'unknown' AND Alias != ''";
		$Aff = $this->getRows($sql);
		$list_br =array();
		for($i = 0;$i <= $days;$i++){
			$d = date('Y-m-d', strtotime("-$i day"));
			foreach($Aff as $all){
				$list_br[$all['Alias']][$d] = 0;
			}
			$sql = "SELECT SUM(Commission) AS COUNT,Alias FROM rpt_transaction_unique  WHERE CreatedDate = '$d' AND Alias != 'unknown' AND Alias != '' GROUP BY Alias";
			$arr[$i] = $this->getRows($sql);
			foreach($arr[$i] as $data){
				$list_br[$data['Alias']][$d] = $data['COUNT'];
			}
		}

		/*
$db_bdg01 = new Mysql('bdg_go_base', 'bdg01.mgsvr.com', 'bdg_slave', 'SHDbdsg32B');
$sql = "SELECT DISTINCT Alias FROM rpt_transaction_unique WHERE CreatedDate >='$day' and Alias != 'unknown' AND Alias != '' AND Alias != 'brandreward'";
$Aff = $db_bdg01->getRows($sql);
$list_bdg =array();
for($i = 0;$i <= $days;$i++){
    $d = date('Y-m-d', strtotime("-$i day"));
    foreach($Aff as $all){
        $list_bdg[$all['Alias']][$d] = 0;
    }
    $sql = "SELECT SUM(Commission) AS COUNT,Alias FROM rpt_transaction_unique  WHERE CreatedDate = '$d' and Alias != 'unknown' AND Alias != '' AND Alias != 'brandreward' GROUP BY Alias";
    $arr[$i] = $db_bdg01->getRows($sql);
    foreach($arr[$i] as $data){
        $list_bdg[$data['Alias']][$d] = $data['COUNT'];
    }
}
        */

		$db_mk = new Mysql('bdg_go_base', 'bdg01.bwe.io', 'bdg_go', 'sh@#!azS81m');
		$sql = "SELECT DISTINCT Alias FROM rpt_transaction_unique WHERE CreatedDate >='$day' and Alias != 'unknown' AND Alias != '' AND Alias != 'brandreward'";
		$Aff = $db_mk->getRows($sql);
		$list_mk =array();
		for($i = 0;$i <= $days;$i++){
			$d = date('Y-m-d', strtotime("-$i day"));
			foreach($Aff as $all){
				$list_mk[$all['Alias']][$d] = 0;
			}
			$sql = "SELECT SUM(Commission) AS COUNT,Alias FROM rpt_transaction_unique  WHERE CreatedDate = '$d' and Alias != 'unknown' AND Alias != '' AND Alias != 'brandreward' GROUP BY Alias";
			$arr[$i] = $db_mk->getRows($sql);
			foreach($arr[$i] as $data){
				$list_mk[$data['Alias']][$d] = $data['COUNT'];
			}
		}

		foreach($list_br as $af=>$data){
			foreach($data as $date=>$commission){
				if(isset($list_mk[$af]) && isset($list_mk[$af][$date])){
					$list_mk[$af][$date] = bcadd($list_mk[$af][$date],$commission,4);
				}else{
					$list_mk[$af][$date] = $commission;
				}
			}
		}

		foreach($list_bdg as $af=>$data){
			foreach($data as $date=>$commission){
				if(isset($list_mk[$af]) && isset($list_mk[$af][$date])){
					$list_mk[$af][$date] = bcadd($list_mk[$af][$date],$commission,4);
				}else{
					$list_mk[$af][$date] = $commission;
				}
			}
		}

		return $list_mk;
	}

	function getAffiliateCommission($data){
		$where_arr = array();
		$where_str = '';
		if(!isset($data['affid']) || empty($data['affid'])){
			return array();
		}else{
			$where_arr[] = 'AffId IN ('.join(',',$data['affid']).')';
		}
		if(isset($data['from'])){
			$where_arr[] = 'CreatedDate >= "'.$data['from'].'"';
		}
		if(isset($data['to'])){
			$where_arr[] = 'CreatedDate <= "'.$data['to'].'"';
		}
		$where_arr[] = "Af NOT IN ('bdg','mk','mega')";
		$where_str = empty($where_arr)?'':' WHERE '.join(' AND ',$where_arr);
		$sql = 'SELECT AffId,SUM(COMMISSION) as c FROM rpt_transaction_unique '.$where_str.' GROUP BY AffId';
		$rows = $this->getRows($sql);

		$affcomm = array();
		foreach($rows as $v){
			$affcomm[$v['AffId']] = $v['c'];
		}
		return $affcomm;
	}


	function getAffiliateClick($data)
	{
		$where_arr = array();
		if(!isset($data['affid']) || empty($data['affid'])){
			return array();
		}else{
			$where_arr[] = 'affId IN ('.join(',',$data['affid']).')';
		}
		if(isset($data['from'])){
			$where_arr[] = 'createddate >= "'.$data['from'].'"';
		}
		if(isset($data['to'])){
			$where_arr[] = 'createddate <= "'.$data['to'].'"';
		}
		$where_str = empty($where_arr)?'':' WHERE '.join(' AND ',$where_arr);
		$sql = 'SELECT affId,SUM(clicks) as c FROM statis_affiliate_br '.$where_str.' GROUP BY affId';
		$rows = $this->getRows($sql);
		
		$affcomm = array();
		foreach($rows as $v){
			$affcomm[$v['affId']] = $v['c'];
		}
		return $affcomm;
	}
}
