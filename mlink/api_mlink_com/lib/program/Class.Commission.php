<?php

class Commission extends LibFactory{
	function processCommissionTxt($data=array()){

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
 	
 		$this->query('SET NAMES latin1');
		// ####
		// $sql = 'SELECT ID,CommissionUsed,CommissionValue,CommissionType,CommissionCurrency,CommissionExt,CommissionIncentive FROM program WHERE CommissionType = "Value" AND CommissionUsed > 0 AND CommissionCurrency = "" AND AffId = '.intval($data['affid']);
		// $row = $this->getRows($sql);
		// echo "<pre>";print_r($row);exit();
		// ####
			
		#do commission format group by affid
		list(,$method) = explode('::',__METHOD__);

		$where_str = 'StatusInAff = "Active" AND Partnership = "Active" AND CommissionExt IS NOT NULL AND CommissionExt != "" ';
		if(isset($data['pid']) && $data['pid']){
			$where_str .= ' AND ID IN ('.$data['pid'].')';
		}
		if(isset($data['affid']) && $data['affid']){
			$where_str .= ' AND AffId IN ('.$data['affid'].')';
		}
		
		$row = $this->table('program')->group('AffId')->where($where_str)->field('AffId')->find();
		$pageSize = 100;

		foreach($row as $AffIdRow){
			$debug_data = array();

			$AffId = $AffIdRow['AffId'];
			if(empty($AffId))
				continue;

			// $AffId = 13;
			$debug_data['AffId'] = $AffId;

			$LinkFeedName = 'LinkFeed_'.$AffId;
			$objLinkFeed = model('LinkFeed/'.$LinkFeedName);
			if(!$objLinkFeed){
				continue;
			}

			$where_str_aff = $where_str . 'AND AffId = '.intval($AffId);
			$pCountRow = $this->table('program')->where($where_str_aff)->count()->findone();
			$pCount = $pCountRow['tp_count'];
			$debug_data['pCount'] = $pCount;

			if($pCount > 0 ){
				$page = ceil($pCount / $pageSize);
				$debug_data['page'] = $page;

				for ($i=1; $i <= $page; $i++) { 
					$programRow = $this->table('program')->where($where_str_aff)->field('ID,CommissionExt,TargetCountryExt')->limit($pageSize)->page($i)->find();

					$updateData = array();

					foreach($programRow as $p){
						// echo $p['ID']."\n";
						$commissionTxt = $p['CommissionExt'];
						$country = $p['TargetCountryExt'];
						$returnData = $objLinkFeed->$method($commissionTxt,$country);
						
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
						$updateSql = $this->getBatchUpdateSql($updateData,'program','ID');
						$this->query($updateSql);
					}

					$debug_data['doing'] = $i;
					debug('update program commission txt AffId('.$debug_data['AffId'].') all count('.$debug_data['pCount'].') doing page('.$i.'/'.$debug_data['page'].')');
				}
			}
		}
		debug('process commission end');
	}

	function debugPrint($str = ''){
		echo $str.'<br>'."\r\n";
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

}
?> 
