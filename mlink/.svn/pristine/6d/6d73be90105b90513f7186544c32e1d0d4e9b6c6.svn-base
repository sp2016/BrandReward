<?php

class Program extends LibFactory{
	function processCommissionTxt($data=array()){
		$this->debugPrint('process commission start');

		#check param
		$flag = 0;
		$msg = '';
		if( (!isset($data['debug']) || empty($data['debug'])) ){
			if(!isset($data['pid']) || empty($data['pid']) )
				$msg = 'error: pid or affid is necessary!';
			else{
				$tmp = explode(',',$data['pid']);
				foreach($tmp as $k=>$v){
					$tmp[$k] = intval(trim($v));
				}
				$data['pid'] = join(',',$tmp);
				if(empty($data['pid']))
					$msg = 'error: pid or affid is necessary!';

				$flag = 1;
			}


			if(!isset($data['affid']) || empty($data['affid']) )
				$msg = 'error: pid or affid is necessary!';
			else{
				$tmp = explode(',',$data['affid']);
				foreach($tmp as $k=>$v){
					$tmp[$k] = intval(trim($v));
				}
				$data['affid'] = join(',',$tmp);
				if(empty($data['affid']))
					$msg = 'error: pid or affid is necessary!';

				$flag = 1;
			}


			if(!$flag)
				over($msg);
		}
			
		
		#do commission format group by affid
		list(,$method) = explode('::',__METHOD__);

		$where_str = 'StatusInAff = "Active" AND Partnership = "Active" AND CommissionExt IS NOT NULL AND CommissionExt != "" ';
		if(isset($data['pid']) && $data['pid']){
			$where_str .= ' AND ID IN ('.$data['pid'].')';
		}
		if(isset($data['affid']) && $data['affid']){
			$where_str .= ' AND AffId IN ('.$data['affid'].')';
		}
		
		$row = $this->table('bd_program')->group('AffId')->where($where_str)->field('AffId')->find();
		$pageSize = 50;

		foreach($row as $AffIdRow){
			$debug_data = array();

			$AffId = $AffIdRow['AffId'];
			if(empty($AffId))
				continue;

			// $AffId = 13;
			$debug_data['AffId'] = $AffId;

			$LinkFeedName = 'LinkFeed_'.$AffId;
			$class_file = INCLUDE_ROOT . 'lib/LinkFeed/Class.' . $LinkFeedName . '.php';
			if(file_exists($class_file))
				$objLinkFeed = new $LinkFeedName;	
			else
				continue;

			$where_str_aff = $where_str . 'AND AffId = '.intval($AffId);
			$pCountRow = $this->table('bd_program')->where($where_str_aff)->count()->findone();
			$pCount = $pCountRow['tp_count'];
			$debug_data['pCount'] = $pCount;

			if($pCount > 0 ){
				$page = ceil($pCount / $pageSize);
				$debug_data['page'] = $page;

				for ($i=1; $i <= $page; $i++) { 
					$programRow = $this->table('bd_program')->where($where_str_aff)->field('ID,CommissionExt')->limit($pageSize)->page($i)->find();

					$updateData = array();

					foreach($programRow as $p){
						$commissionTxt = $p['CommissionExt'];
						$returnData = $objLinkFeed->$method($commissionTxt);
						
						if(!$returnData)
							continue;	

						$returnData['ID'] = $p['ID'];
						$updateData[] = $returnData;
						// echo "<pre>";print_r($p);
						// echo "<pre>";print_r($returnData);
						// print_r("\n========================================\n");
					}
					// exit();
					// echo "<pre>";print_r($updateData);exit();
					if(!empty($updateData)){
						$updateSql = $this->getBatchUpdateSql($updateData,'bd_program','ID');
						$this->query($updateSql);
					}

					$debug_data['doing'] = $i;
					$this->debugPrint('update program commission txt AffId('.$debug_data['AffId'].') all count('.$debug_data['pCount'].') doing page('.$i.'/'.$debug_data['page'].')');
				}
			}
		}
		$this->debugPrint('process commission end');
	}

	function debugPrint($str = ''){
		echo $str.'<br>'."\r\n";
	}

	function setHomePageToDomain(){
		$pageSize = 500;
		$pCountRow = $this->table('bd_program')->count()->findone();
		$pCount = $pCountRow['tp_count'];
		$page = ceil($pCount / $pageSize);
		for ($i=1; $i <= $page; $i++) { 
			$programRow = $this->table('bd_program')->field('ID,Homepage')->limit($pageSize)->page($i)->find();

			foreach ($programRow as $key => $value) {
				$domain = trim($value['Homepage']);
				$domain = trim($value['Homepage'],'/');
				if(substr($domain,0,8) == 'https://'){
					$domain = substr($domain,8);
				}

				if(substr($domain,0,7) == 'http://'){
					$domain = substr($domain,7);
				}

				$row = $this->table('bd_domain')->where('Domain = "'.addslashes($domain).'"')->findone();

				$did = '';

				if($row){
					$did = $row['ID'];
				}else{
					$sql = 'INSERT INTO bd_domain SET Domain = "'.addslashes($domain).'"';
					$this->query($sql);
					$did = $this->objMysql->getLastInsertId();	
				}

				if($did){
					$row = $this->table('bd_program_domain_source')->where('PID = '.intval($value['ID']).' AND DID = '.intval($did))->findone();
					if(!$row){
						$sql = 'INSERT INTO bd_program_domain_source SET PID = '.intval($value['ID']).',DID = '.intval($did);
						$this->query($sql);	
					}
				}
			}

			$this->debugPrint('doing page('.$i.'/'.$page.')');
		}
	}

	function createStoreByDomain(){
		$this->debugPrint('create store by domain start');

		$pageSize = 500;
		$pCountRow = $this->table('bd_domain')->count()->findone();
		$pCount = $pCountRow['tp_count'];
		$page = ceil($pCount / $pageSize);

		for ($i=1; $i <= $page; $i++) { 
			$domainRow = $this->table('bd_domain')->field('ID,Domain')->limit($pageSize)->page($i)->find();

			$updateData = array();
			foreach($domainRow as $k=>$v){
				$domain = trim($v['Domain']);
				if(empty($domain))
					continue;

				if(substr($domain,0,7) == 'http://'){
					$domain = substr($domain,7);
				}
				if(strpos('/',$domain) !== false){
					$pos = strpos('/',$domain);
					$domain = substr($domain,0,$pos);
				}

				$hostName = '';
				$tmp = explode('.',$domain);
				if(count($tmp) 	<3){
					$hostName = $tmp[0];
				}else{
					$hostName = $tmp[1];
				}
				
				$data = array();
				$data['domain'] = $v['Domain'];
				$data['hostName'] = $hostName;
				$data['DID'] = $v['ID'];

				$updateData[] = $data;
			}

			$ids = array();

			$sql = 'REPLACE INTO bd_store (Domain,Name) values ';
			foreach($updateData as $k=>$v){
				$sql .= '("'.$v['domain'].'","'.$v['hostName'].'"),';
				$ids[] = $v['DID'];
			}
			$sql = substr($sql,0,-1);
			$this->query($sql);
			$this->debugPrint('doing page('.$i.'/'.$page.')');

			$sql = 'UPDATE bd_domain AS d , bd_store AS s   SET d.SID = s.ID WHERE d.domain = s.domain AND d.ID IN ('.join(',',$ids).')';
		}

		$this->debugPrint('create store by domain end');
	}

	function sortProgramInDomain($filter=array()){
		$this->debugPrint('sort program in domain start');

		$whereStr = $this->_filter('sortProgramInDomain',$filter);
		
		$pageSize = 500;
		$pCountRow = $this->table('bd_domain')->where($whereStr)->count()->findone();
		$pCount = $pCountRow['tp_count'];
		$page = ceil($pCount / $pageSize);

		for ($i=1; $i <= $page; $i++) { 
			#get all program in domain and sort
			$domainRow = $this->table('bd_domain')->where($whereStr)->limit($pageSize)->page($i)->find();

			$domainIds = array();
			$domainInfo = array();
			foreach($domainRow as $k=>$v){
				$domainIds[] = intval($v['ID']);
				$domainInfo[$v['ID']] = $v;
			}

			$sql = 'SELECT 
			pd.PID,
			pd.DID,
			p.CommissionExt,
			p.CommissionValue,
			p.CommissionType,
			p.CommissionUsed,
			p.CommissionIncentive,
			p.DeniedPubCode 
			FROM `bd_program_domain_source` AS pd LEFT JOIN bd_program AS p ON pd.`PID` = p.`ID` 
			WHERE 
			pd.DID IN ('.join(',',$domainIds).') 
			AND p.`StatusInAff` = "Active" AND 
			p.`Partnership` = "Active"
			ORDER BY p.CommissionUsed DESC ,p.`CommissionIncentive` DESC';
			$pdrRow = $this->objMysql->getRows($sql);

			#use first one in sort program for domain
			$pdrList = array();
			if(empty($pdrRow))
				return ;

			foreach($pdrRow as $k=>$v){
				$pdrList[$v['DID']][] = $v;
			}

			$domainUpdateArr = array();
			$domainLimitedArr = array();
			$domainHasPro = array();
			foreach($pdrList as $k=>$ProList){
				$domainHasPro[] = $k;
				$data = array();
				$data['ID'] = $ProList[0]['DID'];
				$data['PID'] = $ProList[0]['PID'];
				$domainUpdateArr[] = $data;
				if(!empty($ProList[0]['DeniedPubCode'])){
					$puids = explode(',',$ProList[0]['DeniedPubCode']);

					
					foreach($ProList as $pro){
						if(!empty($pro['DeniedPubCode'])){
							$ppuids = explode(',',$pro['DeniedPubCode']);
							foreach($puids as $aa=>$puid){
								if(!in_array($puid,$ppuids)){
									$tmp = array();
									$tmp['DID'] = $pro['DID'];
									$tmp['PID'] = $pro['PID'];
									$tmp['PUBID'] = $puid;
									$domainLimitedArr[] = $tmp;
									unset($puids[$aa]);
								}
							}
						}else{
							foreach($puids as $aa=>$puid){
								$tmp = array();
								$tmp['DID'] = $pro['DID'];
								$tmp['PID'] = $pro['PID'];
								$tmp['PUBID'] = $puid;
								$domainLimitedArr[] = $tmp;
								unset($puids[$aa]);
							}
						}

						if(empty($puids))
								break;
					}
				}
			}

			$updateSql = $this->getBatchUpdateSql($domainUpdateArr,'bd_domain','ID');
			$this->query($updateSql);

			$domainHasNoPro = array_diff($domainIds,$domainHasPro);
			$sql = 'UPDATE bd_domain SET PID = 0 WHERE ID IN ('.join(',',$domainHasNoPro).')';
			$this->query($sql);

			#delete old limited setting
			$sql = 'DELETE FROM bd_domain_limited WHERE DID IN ('.join(',',$domainIds).')';
			$this->query($sql);

			if(!empty($domainLimitedArr)){
				#if exist publish stop coo with any program
				#add extra program
				foreach($domainLimitedArr as $k=>$v){
					$domainLimitedArr[$k]['SID'] = $domainInfo[$v['DID']]['SID'];
					$domainLimitedArr[$k]['Domain'] = $domainInfo[$v['DID']]['Domain'];
				}

				#add domain limited setting
				$sql = $this->getInsertSql($domainLimitedArr,'bd_domain_limited');
				$this->query($sql);
			}

			$this->debugPrint('doing page('.$i.'/'.$page.')');
		}

		$this->debugPrint('sort program in domain end');
	}

	function _filter($act,$filter){
		$filterStr = '';
		if(empty($filter))
			return $filterStr;

		switch ($act) {
			case 'sortProgramInDomain':
					$where = array();

					$pid_str = '';
					if(isset($filter['pid']) && !empty($filter['pid'])){
						$pids = explode(',',$filter['pid']);
						foreach($pids as $k=>$v){
							$pids[$k] = intval($v);
						}
						$pid_str = join(',',$pids);
						if($pid_str)
							$where[] = 'p.ID IN ('.$pid_str.')';
						unset($filter['pid']);
					}

					
					$did_str = '';
					if(isset($filter['did']) && !empty($filter['did'])){
						$dids = explode(',',$filter['did']);
						foreach($dids as $k=>$v){
							$dids[$k] = intval($v);
						}
						$did_str = join(',',$dids);
						if($did_str)
							$where[] = 'pd.DID IN ('.$did_str.')';
						unset($filter['did']);
					}

					
					$affid_str = '';
					if(isset($filter['affid']) && !empty($filter['affid'])){
						$affids = explode(',',$filter['affid']);
						foreach($affids as $k=>$v){
							$affids[$k] = intval($v);
						}
						$affid_str = join(',',$affids);
						if($affid_str)
							$where[] = 'p.AffId IN ('.$affid_str.')';
						unset($filter['AffId']);
					}

					if( empty($pid_str) && empty($did_str) && empty($affid_str) && (!isset($filter['debug']) || empty($filter['debug'])) )
						over('error:pid or did or affid is necessary!');

					$whereStr = '';
					if(empty($where))
						break;

					$whereStr = ' WHERE '.join(' AND ',$where);

					$sql = 'SELECT pd.DID FROM `bd_program_domain_source` AS pd LEFT JOIN bd_program AS p ON pd.`PID` = p.`ID` '.$whereStr;
					$row = $this->objMysql->getRows($sql);

					$dids = array();
					foreach($row as $k=>$v){
						if(!in_array($v['DID'],$dids))
							$dids[] = $v['DID'];
					}
					$filterStr = 'ID IN ('.join(',',$dids).')';
				break;
			case 'updateTempToRedis':
				$where = array();

				$pid_str = '';
				if(isset($filter['pid']) && !empty($filter['pid'])){
					$pids = explode(',',$filter['pid']);
					foreach($pids as $k=>$v){
						$pids[$k] = intval($v);
					}
					$pid_str = join(',',$pids);
					if($pid_str)
						$where[] = 'p.ID IN ('.$pid_str.')';
					unset($filter['pid']);
				}

				
				$did_str = '';
				if(isset($filter['did']) && !empty($filter['did'])){
					$dids = explode(',',$filter['did']);
					foreach($dids as $k=>$v){
						$dids[$k] = intval($v);
					}
					$did_str = join(',',$dids);
					if($did_str)
						$where[] = 'pd.DID IN ('.$did_str.')';
					unset($filter['did']);
				}

				
				$affid_str = '';
				if(isset($filter['affid']) && !empty($filter['affid'])){
					$affids = explode(',',$filter['affid']);
					foreach($affids as $k=>$v){
						$affids[$k] = intval($v);
					}
					$affid_str = join(',',$affids);
					if($affid_str)
						$where[] = 'p.AffId IN ('.$affid_str.')';
					unset($filter['AffId']);
				}

				if( empty($pid_str) && empty($did_str) && empty($affid_str) && (!isset($filter['debug']) || empty($filter['debug'])) )
					over('error:pid or did or affid is necessary!');

				$whereStr = '';
				if(empty($where))
					break;

				$whereStr = ' WHERE '.join(' AND ',$where);

				$sql = 'SELECT pd.DID FROM `bd_program_domain_source` AS pd LEFT JOIN bd_program AS p ON pd.`PID` = p.`ID` '.$whereStr;
				$row = $this->objMysql->getRows($sql);

				$dids = array();
				foreach($row as $k=>$v){
					if(!in_array($v['DID'],$dids))
						$dids[] = $v['DID'];
				}
				$filterStr = 'd.ID IN ('.join(',',$dids).')';
			break;
			default:
				# code...
				break;
		}

		return $filterStr;
	}

	function changePubStoreCoo($PubId,$StoreId,$Status){
		if(empty($StoreId))
			return ;
		#change store publish coo
		$sids = explode(',',$StoreId);
		foreach($sids as $k=>$v){
			$sids[$k] = intval($v);
		}
		$pid = intval($PubId);
		$sql = 'REPLACE INTO bd_publish_store_coo (SID,PUBID,Status) VALUE ';
		foreach($sids as $k=>$v){
			$sql .= '('.$v.','.$pid.',"'.$Status.'"),';
		}
		$sql = substr($sql,0,-1);
		$this->query($sql);

		#change store info
		$spcRow = $this->table('bd_publish_store_coo')->where('SID IN ('.join(',',$sids).') AND Status = "stop"')->find();
		if(!empty($spcRow)){
			$storeDenied = array();
			$updateData = array();

			foreach($spcRow as $k=>$v){
				$storeDenied[$v['SID']][] = intval($v['PUBID']);
			}

			foreach ($storeDenied as $k => $v) {
				$tmp = array();
				$tmp['ID'] = $k;
				$tmp['DeniedPubCode'] = join(',',$v);
				$updateData[] = $tmp;
			}

			$sql = $this->getBatchUpdateSql($updateData,'bd_store','ID');
			$this->query($sql);
		}else{
			$sql = 'UPDATE bd_store SET DeniedPubCode = "" WHERE ID IN ('.join(',',$sids).')';
			$this->query($sql);
		}
		

		#change speical domain for store coo with publish

	}

	function changePubProgramCoo($PubId,$Pid,$Status){
		$this->debugPrint('change publish and program coo start');
		if(empty($Pid))
			return ;
		#change store publish coo
		$pids = explode(',',$Pid);
		foreach($pids as $k=>$v){
			$pids[$k] = intval($v);
		}
		$puid = intval($PubId);
		$sql = 'REPLACE INTO bd_publish_program_coo (PID,PUBID,Status) VALUE ';
		foreach($pids as $k=>$v){
			$sql .= '('.$v.','.$puid.',"'.$Status.'"),';
		}
		$sql = substr($sql,0,-1);
		$this->query($sql);

		#change Program info
		$ppcRow = $this->table('bd_publish_program_coo')->where('PID IN ('.join(',',$pids).') AND Status = "stop"')->find();
		if(!empty($ppcRow)){
			$programDenied = array();
			$updateData = array();

			foreach($ppcRow as $k=>$v){
				$programDenied[$v['PID']][] = intval($v['PUBID']);
			}

			foreach ($programDenied as $k => $v) {
				$tmp = array();
				$tmp['ID'] = $k;
				$tmp['DeniedPubCode'] = join(',',$v);
				$updateData[] = $tmp;
			}

			$sql = $this->getBatchUpdateSql($updateData,'bd_program','ID');
			$this->query($sql);
		}else{
			$sql = 'UPDATE bd_program SET DeniedPubCode = "" WHERE ID IN ('.join(',',$pids).')';
			$this->query($sql);
		}
		$this->debugPrint('change publish and program coo end');
	}

	function updateTempToRedis($filter=array()){
		$this->debugPrint('update temp to redis start');
		#get temp data for redis

		$whereStr = $this->_filter('updateTempToRedis',$filter);
		$whereStr = $whereStr?' WHERE '.$whereStr:'';

		$pageSize = 500;
		$sql = 'SELECT count(*) AS c FROM bd_domain as d '.$whereStr;
		$row = $this->getRow($sql);
		$pCount = $row['c'];
		$page = ceil($pCount / $pageSize);

		$objRedis = new DbRedis();

		for ($i=1; $i <= $page; $i++) { 
			$sql = 'SELECT 
			  d.`Domain`,
			  d.`ID` AS DID,
			  p.`ID` AS PID,
			  p.`AffId`,
			  p.`IdInAff`,
			  pi.`AffDefaultUrl`,
			  pi.`DeepUrlTpl`,
			  p.`CommissionType`,
			  p.`CommissionUsed`,
			  p.`CommissionIncentive`,
			  p.`DeniedPubCode`,
			  p.`Partnership`,
			  p.`StatusInAff`
			FROM
			  bd_domain AS d 
			  LEFT JOIN bd_program AS p 
			    ON d.`PID` = p.`ID` 
			  LEFT JOIN bd_program_intell as pi
			  	ON d.`PID` = pi.`ID` 
			'.$whereStr.' 
			ORDER BY d.ID 
			LIMIT '.($i-1)*$pageSize.','.$pageSize;

			$row = $this->getRows($sql);

			$InsertData = array();
			$DeleteData = array();

			$Dids = array();
			foreach($row as $k=>$v){
				$Dids[] = intval($v['DID']);
				
				$InsertData[$v['DID']][] = $v;
				// if($v['Partnership'] == 'Active' && $v['StatusInAff'] == 'Active'){
				// 	$InsertData[$v['DID']][] = $v;
				// }else{
				// 	$DeleteData[] = $v;
				// }
			}

			$sql = 'SELECT 
			  d.`Domain`,
			  d.`DID` AS DID,
			  d.`PUBID`,
			  p.`ID` AS PID,
			  p.`AffId`,
			  p.`IdInAff`,
			  pi.`AffDefaultUrl`,
			  pi.`DeepUrlTpl`,
			  p.`CommissionType`,
			  p.`CommissionUsed`,
			  p.`CommissionIncentive`,
			  p.`DeniedPubCode`,
			  p.`Partnership`,
			  p.`StatusInAff`
			FROM
			  bd_domain_limited AS d 
			  LEFT JOIN bd_program AS p 
			    ON d.`PID` = p.`ID` 
			  LEFT JOIN bd_program_intell as pi
			  	ON d.`PID` = pi.`ID` 
			WHERE d.DID IN ('.join(',',$Dids).') AND p.Partnership = "Active" AND StatusInAff = "Active"';

			$row = $this->getRows($sql);
			if(!empty($row)){
				foreach($row as $k=>$v){
					$InsertData[$v['DID']][] = $v;
				}
			}

			#update redis data
			if(!empty($InsertData)){
				foreach($InsertData as $k=>$v){
					
					#update row in redis
					foreach($v as $domainArr){
						$key = $this::CreateDomainKey($domainArr);
						$res = $objRedis->SetArr($key,$domainArr);
					}
				}
			}
			

			$this->debugPrint('doing page('.$i.'/'.$page.')');
		}

		$this->debugPrint('update temp to redis end');
	}

	function clearRedis(){
		$objRedis = new DbRedis();
		$keys = $objRedis->Keys('*');
		$objRedis->Del($keys);
	}

	static function CreateDomainKey($domainArr){
		if(empty($domainArr))
			return '';
		if(!isset($domainArr['Domain']))
			return '';
		$key = $domainArr['Domain'];

		if(isset($domainArr['PUBID']) && !empty($domainArr['PUBID']))
			$key = $domainArr['PUBID'].'|'.$key;
		return $key;
	}

	function goOut($url,$mid,$id){
		$outUrl = '';

		$trackingData = date('Y-m-d H:i:s');
		$sql = 'INSERT INTO bd_out_tracking (pageUrl,publishId,publishTracking,created) VALUES ("'.addslashes($url).'",'.intval($mid).',"'.addslashes($id).'","'.$trackingData.'")';
		$this->query($sql);
		$trackingId = $this->objMysql->getLastInsertId();
		$trackingSession = md5($trackingId.'_'.$trackingData);


		$urlParse = parse_url($url);
		$domainUse = $urlParse['host'];

		$objRedis = new DbRedis();
		$domainInRedis = $objRedis->Keys($domainUse.'*');
		$domainInRedisNum = count($domainInRedis);

		$hasAff = 0;

		if($domainInRedisNum < 0){
			#do not find domain

			#go to the landing page
			#$outUrl = $url;
		}elseif($domainInRedisNum == '1'){
			#match one domain
			$hasAff = 1;
			$domainUse = $domainInRedis[0];
			
		}else{
			#match more than one domain
			$hasAff = 1;
		}

		
		$updateData = array();
		$updateData['sessionId'] = $trackingSession;

		if($hasAff){
			$programInfo = $objRedis->GetArr($domainUse);
			########### use aff url templet or default to get out url#################
			$outUrl = $this->getOutUrl($url,$programInfo['AffDefaultUrl'],$programInfo['DeepUrlTpl']);
			$outUrl = $this::addSid($outUrl,$programInfo['AffId'],$trackingSession);
			############################
			$updateData['outUrl'] = $outUrl;
			$updateData['domainUsed'] = $domainUse;
			$updateData['domainId'] = $programInfo['DID'];
			$updateData['programId'] = $programInfo['PID'];
			$updateData['programAffDefaultUrl'] = $programInfo['AffDefaultUrl'];
			$updateData['programDeepUrlTpl'] = $programInfo['DeepUrlTpl'];

		}else{
			$outUrl = $url;
			$updateData['outUrl'] = $outUrl;
		}

		

		$sql = $this->getUpdateSql($updateData,'bd_out_tracking',' ID = '.intval($trackingId));
		$this->query($sql);

		header("HTTP/1.1 301 Moved Permanently");
	    header("Cache-Control: no-cache");
	    header("Location: $outUrl");
	}

	function addSid($outUrl,$AffId,$trackingSession){
		global $AFFILIATE_SIDS;
		if(isset($AFFILIATE_SIDS[$AffId]) && isset($AFFILIATE_SIDS[$AffId])){
			$con = '';
			if(strpos($outUrl, '?') !== false)
				$con = '&';
			else
				$con = '?';

			$outUrl .= $con.$AFFILIATE_SIDS[$AffId].'='.$trackingSession;
		}
		return $outUrl;
	}

	function getOutUrl($url,$AffDefaultUrl='',$template=''){
		if(empty($template) && empty($AffDefaultUrl))
			return $url;

		if(empty($AffDefaultUrl))
			return $AffDefaultUrl;

		if (preg_match('/(.*)\[(PURE_DEEPURL|DEEPURL|DOUBLE_ENCODE_DEEPURL|URI|ENCODE_URI|DOUBLE_ENCODE_URI|DEEPHOMEPAGE)\](\[\?\|&\])*/', $template, $m)) {
			preg_match('/^http(s)?:\/\/[^\/]+(\/)?(.*)/', $url, $q);
			if(isset($m[3]) && !empty($m[3]))
            	$has_deep_mark = true;
            else
            	$has_deep_mark = false;

            switch ($m[2]) {
            	case 'DEEPHOMEPAGE':
            		$template = str_ireplace('[DEEPHOMEPAGE]', $url, $template);                            
                    break;
                case 'PURE_DEEPURL':
                    $template = str_ireplace('[PURE_DEEPURL]', $url, $template);                            
                    break;
                case 'DEEPURL':                                
                    $template = str_ireplace('[DEEPURL]', ($m[1] == ''? $url: urlencode($url)), $template);
                    if ($m[3] == '[?|&]' && $m[1] != '') {
                        $mark_and = urlencode($mark_and);
                        $mark_que = urlencode($mark_que);                            
                    }
                    break;
                case 'DOUBLE_ENCODE_DEEPURL':
                    $template = str_ireplace('[DOUBLE_ENCODE_DEEPURL]', ($m[1] == ''? $url : urlencode(urlencode($url))), $template);                            
                    if ($m[3] == '[?|&]' && $m[1] != '') {
                        $mark_and = urlencode(urlencode($mark_and));
                        $mark_que = urlencode(urlencode($mark_que));                            
                    }                                
                    break;                                               
                case 'URI':
                    $template = preg_replace('/([^:])\/{2,}/', '\1/', str_ireplace('[URI]', '/'.(isset($q[3]) && $q[3] != ''? $q[3] : ''), $template));                               
                    break;
                case 'ENCODE_URI':
                    $template = preg_replace('/([^:])\/{2,}/', '\1/',  str_ireplace('[ENCODE_URI]', urlencode('/'.(isset($q[3]) && $q[3] != ''? $q[3] : '')), $template));
                    if ($m[3] == '[?|&]' && $m[1] != '') {
                        $mark_and = urlencode($mark_and);
                        $mark_que = urlencode($mark_que);                            
                    }                                
                    break;
                case 'DOUBLE_ENCODE_URI':
                    $template = preg_replace('/([^:])\/{2,}/', '\1/',  str_ireplace('[DOUBLE_ENCODE_URI]', urlencode(urlencode('/'.(isset($q[3]) && $q[3] != ''? $q[3] : ''))), $template));
                    if ($m[3] == '[?|&]' && $m[1] != '') {
                        $mark_and = urlencode(urlencode($mark_and));
                        $mark_que = urlencode(urlencode($mark_que));                            
                    }                                                                
                    break;
            }
        }

        $m = array();
        if (preg_match('/(.*)(\[\?\|&\].*)/', $template, $m)) { //&& $start_w_tpl
            if ($has_deep_mark) {
                $m[1] = $url;
            }
                            
            if (preg_match('/[\?&][^&]+=[^&]*/U', $m[1]))
                $template = str_replace('[?|&]', $mark_and, $template);
            else
                $template = str_replace('[?|&]', $mark_que, $template);
        }

        return $template;
	}

	function rebuidDomain(){
		$this->debugPrint('rebuid domain start');

		$pageSize = 500;
		$pCountRow = $this->table('bd_domain')->count()->findone();
		$pCount = $pCountRow['tp_count'];
		$page = ceil($pCount / $pageSize);

		for ($i=1; $i <= $page; $i++) { 
			$domainRow = $this->table('bd_domain')->field('ID,Domain')->limit($pageSize)->page($i)->find();
			foreach($domainRow as $k=>$v){
				if(substr($v['Domain'],0,8) == 'https://'){
					$domainRow[$k]['Domain'] = substr($v['Domain'],8);
				}

				if(substr($v['Domain'],0,7) == 'http://'){
					$domainRow[$k]['Domain'] = substr($v['Domain'],7);
				}
			}

			$updateSql = $this->getBatchUpdateSql($domainRow,'bd_domain','ID');
			$this->query($updateSql);

			$this->debugPrint('doing page('.$i.'/'.$page.')');
		}

		$this->debugPrint('rebuid domain end');
	}

	function log_tracking_data_to_db(){
		$objRedis = new DbRedis();
		while(1){
			$trackId = $objRedis->Rpop('track_log_list');
			if($trackId){
				$key = 'track_log_'.$trackId;
				$data = $objRedis->GetArr($key);

				if($data){
					$sql = 'INSERT INTO bd_out_tracking SET 
						pageUrl = "'.addslashes($data['pageUrl']).'",
						outUrl = "'.addslashes($data['outUrl']).'",
						sessionId = "'.addslashes($data['sessionId']).'",
						created = "'.addslashes($data['created']).'",
						publishId = "'.addslashes($data['publishId']).'",
						publishTracking = "'.addslashes($data['publishTracking']).'",
						domainUsed = "'.addslashes($data['domainUsed']).'",
						domainId = "'.addslashes($data['domainId']).'",
						programId = "'.addslashes($data['programId']).'",
						programAffDefaultUrl = "'.addslashes($data['programAffDefaultUrl']).'",
						programDeepUrlTpl = "'.addslashes($data['programDeepUrlTpl']).'",
						redisId = "'.addslashes($data['trackId']).'",
						site = "'.addslashes($data['site']).'"';

					$res = $this->query($sql);
					if($res){
						$objRedis->Del($key);
					}else{
						if(!isset($data['failed']))
							$data['failed'] = 1;
						else
							$data['failed'] = $data['failed']+1;

						$objRedis->SetArr($key,$data);

						if($data['failed'] > 3){
							$objRedis->Lpush('track_log_failed',$trackId);
						}else{
							$objRedis->Lpush('track_log_list',$trackId);
						}
					}
				}
			}else{
				sleep(5); //wait for 5 second;
			}
		}
	}

	function cron_set_check_outurl(){
		$this->debugPrint('set redis check url start');
		$rows = $this->table('bd_out_tracking')->where('isCheck = 0')->field('id')->order('id asc')->find();
		//key = url_check_list
		if($rows){
			$objRedis = new DbRedis();
			$objRedis->Del('url_check_list');

			foreach($rows as $k=>$v){
				$objRedis->Lpush('url_check_list',$v['id']);
			}
		}
		$this->debugPrint('set redis check url end');
	}

	function cron_check_outurl_res(){
		$this->debugPrint('cron check url start');
		do{
			$objRedis = new DbRedis();
			$id = $objRedis->Rpop('url_check_list');

			if($id){
				$row = $this->table('bd_out_tracking')->where('isCheck = 0 AND id = '.intval($id))->findone();
				if($row && !empty($row['outUrl'])){
					$url = $row['outUrl'];
					$this->debugPrint('loading: '.$url);
					
					$httpRes = $this::getHttpRes($url);

					$sql = 'UPDATE bd_out_tracking SET 
							 finalUrl = "'.addslashes($httpRes['finalUrl']).'",
							 hasResponse = "'.addslashes($httpRes['hasResponse']).'",
							 isLocation = "'.addslashes($httpRes['isLocation']).'",
							 is200 = "'.addslashes($httpRes['is200']).'",
							 isCheck = 1 
							 WHERE id = '.intval($row['id']);
					$this->query($sql);
				}
			}
		}while($id);
		$this->debugPrint('cron check url end');
	}

	function cron_set_check_pageurl(){
		$this->debugPrint('set redis check url start');
		$rows = $this->table('bd_out_tracking')->where('isCheck = 1 AND is200 = 0 AND (programAffDefaultUrl != "" OR programDeepUrlTpl != "")')->field('id')->order('id asc')->find();
		//key = page_check_list
		if($rows){
			$objRedis = new DbRedis();
			$objRedis->Del('page_check_list');

			foreach($rows as $k=>$v){
				$objRedis->Lpush('page_check_list',$v['id']);
			}
		}
		$this->debugPrint('set redis check url end');
	}

	function cron_check_pageurl_res(){
		$this->debugPrint('cron check url start');
		do{
			$objRedis = new DbRedis();
			$id = $objRedis->Rpop('page_check_list');

			if($id){
				$row = $this->table('bd_out_tracking')->where('id = '.intval($id))->findone();
				if($row && !empty($row['pageUrl'])){
					$url = $row['pageUrl'];
					$this->debugPrint('loading: '.$url);
					
					$httpRes = $this::getHttpRes($url);

					$sql = 'UPDATE bd_out_tracking SET 
							 isPageUrl200 = "'.addslashes($httpRes['is200']).'" 
							 WHERE id = '.intval($row['id']);
					$this->query($sql);
				}
			}
		}while($id);
		$this->debugPrint('cron check url end');
	}

	function test_coupon_affurl(){
		$databases = array(
                        'couponau_base',
                        'couponde_base',
                        'couponsn_base',
                        'couponuk_base',
                        );

 
        foreach($databases as $dbname){
                echo "loading: ".$dbname."coupon url..."."\n";
                $objMysql = new Mysql($dbname, '127.0.0.1', 'csg_readonly', 'M6HTUtWBNqgV1');
                $sql = 'SELECT ID FROM normalmerchant';
                $rows = $objMysql->getRows($sql);
                if($rows){
                        foreach($rows as $k=>$v){
                                $mid = $v['ID'];
                                $sql = 'SELECT ID,DstUrl FROM normalcoupon WHERE MerchantID = '.intval($mid).' ORDER BY ExpireTime DESC  LIMIT 2';
                                $couponRows = $objMysql->getRows($sql);
                                if($couponRows){
                                        foreach($couponRows as $a=>$b){
                                                $url = 'http://go.megasvc.com/?url='.urlencode($b['DstUrl']).'&mid=1111&id=fffffff&site=snapus';

                                                while(1){
                                                        $aaa = exec('ps aux | grep curl | wc -l');
                                                        if($aaa > 20){
                                                                sleep(1);
                                                        }else{
                                                                error_log(print_r($b['DstUrl']."\n",1),3,'/home/gordon/logs/mega_go.log');
                                                                exec('curl "'.$url.'" -I >> /home/gordon/logs/curl.log 2>&1 &');
                                                                break;
                                                        }
                                                }
                                                echo $url."\n";
                                        }
                                }
                        }
                }
        }
	}


	function check_diff_in_final_page_url(){
		$this->debugPrint('update bd_out_tracking url diff start');

		$pageSize = 100;
		$where_str = 'programDeepUrlTpl != "" AND is200 = 1';
		$pCountRow = $this->table('bd_out_tracking')->where($where_str)->count()->findone();
		$pCount = $pCountRow['tp_count'];
		$debug_data['pCount'] = $pCount;

		if($pCount > 0 ){
			$page = ceil($pCount / $pageSize);
			$debug_data['page'] = $page;

			for ($i=1; $i <= $page; $i++) { 
				$trackRow = $this->table('bd_out_tracking')->where($where_str)->field('id,pageUrl,finalUrl')->limit($pageSize)->page($i)->find();

				$updataArr = array();

				if($trackRow){
					foreach($trackRow as $k=>$v){
						$diff = 0;
						$pageUrl = $v['pageUrl'];
						$finalUrl = $v['finalUrl'];

						$step = strpos($pageUrl, '?');
						if($step !== false){
							$pageUrl = substr($pageUrl,0,$step);
						}

						$step = strpos($finalUrl, '?');
						if($step !== false){
							$finalUrl = substr($finalUrl,0,$step);
						}

						if($pageUrl != $finalUrl){
							$diff = 1;
						}

						$tmp = array();
						$tmp['id'] = $v['id'];
						$tmp['isDiff'] = $diff;
						$updataArr[] = $tmp;
					}

					if(!empty($updataArr)){
						$updateSql = $this->getBatchUpdateSql($updataArr,'bd_out_tracking','id');
						$this->query($updateSql);
					}

				}

				$debug_data['doing'] = $i;
				$this->debugPrint('update bd_out_tracking url diff on all count('.$debug_data['pCount'].') doing page('.$i.'/'.$debug_data['page'].')');
			}
		}

		$this->debugPrint('update bd_out_tracking url diff end');

	}

}
?> 
