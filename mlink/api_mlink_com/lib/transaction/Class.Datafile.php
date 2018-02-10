<?php

class Datafile extends LibFactory{
	var $AffNameIdMap = array(
						'cj'=>'1',
						'ls'=>'2',
						'ond'=>'30',
						'sas'=>'7',
						'td'=>'133',
						'zanox'=>'15',
						'afffuk'=>'22',
						'affwin'=>'10',
						'avangate'=>'32',
						'pjn'=>'6',
						'wg'=>'14',
						'afffus'=>'20',
						'avt'=>'8',
						'dgmnew_au'=>'28',
						'dgmnew_nz'=>'157',
						'tt'=>'52',
						'tt_de'=>'65',
						'lc'=>'12',
						'cm'=>'62',
						'cf'=>'115',
						'sr'=>'50',
						'cg'=>'46',
						'tagau'=>'49',
						'taguk'=>'124',
						'tagsg'=>'196',
						'tagas'=>'197',
						'belboon'=>'152',
						'por'=>'29',
						'affili'=>'26',
						'affili_de'=>'63',
						'impradus'=>'58',
						'impraduk'=>'59',
						'silvertap'=>'23',
						'viglink'=>'191',
						'skimlinks'=>'223',
						'phg'=>'188',
						'phg_irisa'=>'188',
						'phg_conv'=>'188',
						'phg_horiz'=>'188',
						'adcell'=>'360',
						'zoobax'=>'398',
						'gameladen'=>'408');

	var $data_dir = '';
	var $add_file = array();
	var $update_file = array();



	function info(){
		$method = array(
				array(
					'name'=>'update_data',
					'desc'=>'update transaction to database with type (increase data which is diffrence)',
					'argv'=>'',
					),
				array(
					'name'=>'info_data',
					'desc'=>'info transaction data with (domainUsed,programId,Visited,VisitedDate,Alias)',
					'argv'=>'',
					),
				);
		return $method;
	}

	function update_data($data){
		debug('start update data...'.date('Y-m-d H:i:s'));
		$this->data_dir = DATA_ROOT.'transaction';
		$this->check_file_md5();
		$this->do_update_file();
		debug('end update data...'.date('Y-m-d H:i:s'));
		$this->info_data();
	}

	function info_data(){
		debug('start info data...'.date('Y-m-d H:i:s'));
		#update site
		#update affid
		#update publishTracking

		$site_tracking_code = array(
			's01'=>'csus',
			's09'=>'csca',
			's17'=>'csau',
			's02'=>'csuk',
			's29'=>'csde',
			's49'=>'csusmob',
			's10'=>'csie',
			's32'=>'csnz',
			's42'=>'pc2012',
			's70'=>'hotdeals',
			's16'=>'dealsalbum',
			's16'=>'dealsalbum',
			's46'=>'dc2012',
			's501'=>'acapp',
			's15'=>'anypromocodes',
			's08'=>'c4lp',
			's40'=>'coupondealpro',
			's43'=>'cs6rlease',
			's05'=>'cs3soft',
			's06'=>'cs4soft',
			's07'=>'cs4softus',
			's38'=>'seekcoupon',
			's03'=>'codes',
			's04'=>'perfect',
			's36'=>'esw4u',
			's52'=>'cs6upgrade',
			's37'=>'ifunbox',
			's45'=>'shopwithcoupon',
			's59'=>'laihaitao',
			's61'=>'fiberforme',
			's39'=>'tipdownload',
			's63'=>'ccm',
			's71'=>'fscoupon',
			's64'=>'walletsaving',
			's69'=>'paydayloan',
			's65'=>'appholic',
			's47'=>'bfdc',
			);

		// $site_alias_map = array(
		// 	'csus'=>'1679091c5a880faf6fb5e6087eb1b2dc',
		// 	'csca'=>'c9f0f895fb98ab9159f51fd0297e236d',
		// 	'csau'=>'8f14e45fceea167a5a36dedd4bea2543',
		// 	'csuk'=>'e4da3b7fbbce2345d7772b0674a318d5',
		// 	'csde'=>'c81e728d9d4c2f636f067f89cc14862c',
		// 	'bfdc'=>'c4ca4238a0b923820dcc509a6f75849b',
		// 	'hd'=>'a87ff679a2f3e71d9181a67b7542122c',
		// 	'ss'=>'eccbc87e4b5ce2fe28308fd9f2a7baf3',
		// 	'fr'=>'45c48cce2e2d7fbdea1afc51c7c6ad26',
		// 	);

		$pageSize = 500;
		$debug_data = array();
		$countRow = $this->table('rpt_transaction_unique')->where('Site = ""')->count()->findone();
		$count = $countRow['tp_count'];

		$debug_data['pCount'] = $count;
		$page = ceil($count / $pageSize);
		$debug_data['page'] = $page;

		if($count > 0 ){
			$i = 0;
			while(1){
				$i++;
				$sql = 'SELECT a.ID,a.SID,b.site,b.publishTracking,b.created,b.domainUsed,b.programId,a.AffId as OAffId,c.AffId  ,d.Alias 
						FROM rpt_transaction_unique AS a 
						LEFT JOIN bd_out_tracking AS b ON a.SID = b.sessionId 
						LEFT JOIN program_intell AS c ON b.programId = c.ProgramId 
						LEFT JOIN publisher_account as d ON b.site = d.ApiKey 
						WHERE a.site = "" LIMIT '.$pageSize;

				$row = $this->getRows($sql);

				if(empty($row))
					break;

				$up_data = array();

				foreach($row as $k=>$v){
					$id = $v['ID'];
					$sid = $v['SID'];
					$site = $v['site'];
					$created = $v['created'];
					$domainUsed = $v['domainUsed'];
					$programId = $v['programId'];
					$alias = $v['Alias'];
					$publishTracking = $v['publishTracking'];
					
					if(!$site && $sid){
						list($code) = explode('_',$sid);
						if(isset($site_tracking_code[$code]))
							$site = $site_tracking_code[$code];
					}
					if(!$site && $publishTracking){
						list($code) = explode('_',$publishTracking);
						if(isset($site_tracking_code[$code]))
							$site = $site_tracking_code[$code];
					}
					$site = $site?$site:'unknown';

					$alias = $alias?$alias:$site;

					// if(isset($site_alias_map[$site])){
					// 	$alias = $site;
					// 	$site = $site_alias_map[$site];
					// }

					$affid = $v['AffId']?$v['AffId']:$v['OAffId'];
					

					$up_data[] = array(
						'ID'=>$id,
						'Site'=>$site,
						'Affid'=>$affid,
						'PublishTracking'=>$publishTracking,
						'Visited'=>$created,
						'VisitedDate'=>substr($created,0,10),
						'DomainUsed'=>$domainUsed,
						'ProgramId'=>$programId,
						'Alias'=>$alias,
					);

				}

				if(!empty($up_data)){
					$sql = $this->getBatchUpdateSql($up_data,'rpt_transaction_unique','ID');	
					$this->query($sql);
				}
				
				$debug_data['doing'] = $i;
				debug('doing info data...all count('.$debug_data['pCount'].') doing page('.$i.'/'.$debug_data['page'].')');
			}
		}
		debug('end info data...'.date('Y-m-d H:i:s'));
	}

	function check_file_md5(){
		debug('do check file md5...');
		$dir_name = getDir($this->data_dir,'dir',true);
		$dir_name_active = array_keys($this->AffNameIdMap);
		$dir_used = array_intersect($dir_name, $dir_name_active);

		foreach($dir_used as $k=>$v){
			$dir_used[$k] = $this->data_dir.'/'.$v;
		}

		$data_file = getDir($dir_used,'file');
		foreach($data_file as $k=>$v){
			if(substr($v,-3) != 'dat'){
				unset($data_file[$k]);
				continue;
			}
				
			#只获取13年5/17以后的数据
			$file_time = substr(basename($v),8,-4);
			if(strtotime($file_time) < strtotime('2013-05-17')){
				unset($data_file[$k]);
				continue;
			}
		}

		$data_dir_len = strlen($this->data_dir);
		$data_file_info = array();
		foreach($data_file as $k=>$v){
			$fullname = $v;
			$pos = substr($v,$data_dir_len);
			$md5 = md5_file($v);

			$data_file_info[$pos]['file_path'] = $pos;
			$data_file_info[$pos]['file_md5'] = $md5;
		}
		unset($data_file);

		$file_path_list = array_keys($data_file_info);

		$file_path_in_database = array();
		$tmp = $this->table('rpt_transaction_file')->where('file_path IN ("'.join('","',$file_path_list).'")')->find();
		foreach($tmp as $k=>$v){
			$file_path_in_database[$v['file_path']] = $v;
		}
		unset($tmp);

		$update_file = array();
		foreach($data_file_info as $k=>$v){
			if(isset($file_path_in_database[$k])){
				if($v['file_md5'] != $file_path_in_database[$k]['file_md5']){
					$update_file[] = $v;
				}
			}else{
				$update_file[] = $v;
			}
		}

		$this->update_file = $update_file;
	}

	function do_update_file(){
		foreach($this->update_file as $k=>$v){
			$this->do_update_data($v);
			debug('do update file md5 : '.$v['file_path']);
			//update file md5 recode
			$sql = 'REPLACE INTO rpt_transaction_file SET file_path = "'.addslashes($v['file_path']).'" , file_md5 = "'.addslashes($v['file_md5']).'"';	
			$this->query($sql);
		}
	}

	static function format_txt2data($af,$Arr){
		$tmp = array();
		#所有的新增交易记录 updatedtime = createdtime 
		#如果是修改记录 则updatedtime 取当前时间
		
		if($af == 'ls' || $af == 'viglink'){
			#如果是ls 或者 viglink 这2个aff 都是只有一个变化时间。
			$tmp['Updated'] = addslashes($Arr[0]);
			$tmp['UpdatedDate'] = date('Y-m-d',strtotime($tmp['Updated']));
			$tmp['Created'] = addslashes($Arr[0]);
			$tmp['CreatedDate'] = date('Y-m-d',strtotime($tmp['Created']));
		}else{
			$tmp['Updated'] = addslashes($Arr[1]);
			$tmp['UpdatedDate'] = date('Y-m-d',strtotime($tmp['Updated']));
			$tmp['Created'] = addslashes($Arr[1]);
			$tmp['CreatedDate'] = date('Y-m-d',strtotime($tmp['Created']));
		}
		
		$tmp['Sales'] = floatval($Arr[2]);
		$tmp['Commission'] = floatval($Arr[3]);
		$tmp['IdInAff'] = addslashes($Arr[4]);
		$tmp['ProgramName'] = addslashes($Arr[5]);
		$tmp['SID'] = addslashes($Arr[6]);

		if($af == 'td'){
			$tmp['OrderId'] = isset($Arr[7])?addslashes($Arr[7]):'';
			$tmp['ClickTime'] = isset($Arr[8])?addslashes($Arr[8]):'';
			$tmp['TradeId'] = '';
			$tmp['TradeStatus'] = '';
			$tmp['OldCur'] = isset($Arr[9])?addslashes($Arr[9]):'';
			$tmp['OldSales'] = isset($Arr[10])?addslashes($Arr[10]):'';
			$tmp['OldCommission'] = isset($Arr[11])?addslashes($Arr[11]):'';
			$tmp['TradeType'] = '';
		}elseif($af == 'wg'){
			$tmp['OrderId'] = isset($Arr[7])?addslashes($Arr[7]):'';
			$tmp['ClickTime'] = isset($Arr[8])?addslashes($Arr[8]):'';
			$tmp['TradeId'] = '';
			$tmp['TradeStatus'] = isset($Arr[9])?addslashes($Arr[9]):'';
			$tmp['OldCur'] = isset($Arr[10])?addslashes($Arr[10]):'';
			$tmp['OldSales'] = isset($Arr[11])?addslashes($Arr[11]):'';
			$tmp['OldCommission'] = isset($Arr[12])?addslashes($Arr[12]):'';
			$tmp['TradeType'] = isset($Arr[13])?addslashes($Arr[13]):'';
		}elseif($af == 'avt'){
			$tmp['OrderId'] = isset($Arr[7])?addslashes($Arr[7]):'';
			$tmp['ClickTime'] = isset($Arr[8])?addslashes($Arr[8]):'';
			$tmp['TradeId'] = isset($Arr[9])?addslashes($Arr[9]):'';
			$tmp['TradeStatus'] = '';
			$tmp['OldCur'] = isset($Arr[10])?addslashes($Arr[10]):'';
			$tmp['OldSales'] = isset($Arr[11])?addslashes($Arr[11]):'';
			$tmp['OldCommission'] = isset($Arr[12])?addslashes($Arr[12]):'';
			$tmp['TradeType'] = isset($Arr[13])?addslashes($Arr[13]):'';
		}elseif($af == 'tt' || $af == 'tt_de'){
			$tmp['OrderId'] = '';
			$tmp['ClickTime'] = isset($Arr[7])?addslashes($Arr[7]):'';
			$tmp['TradeId'] = '';
			$tmp['TradeStatus'] = '';
			$tmp['OldCur'] = isset($Arr[8])?addslashes($Arr[8]):'';
			$tmp['OldSales'] = isset($Arr[9])?addslashes($Arr[9]):'';
			$tmp['OldCommission'] = isset($Arr[10])?addslashes($Arr[10]):'';
			$tmp['TradeType'] = isset($Arr[11])?addslashes($Arr[11]):'';
		}elseif($af == 'sr'){
			$tmp['OrderId'] = isset($Arr[7])?addslashes($Arr[7]):'';
			$tmp['ClickTime'] = isset($Arr[8])?addslashes($Arr[8]):'';
			$tmp['TradeId'] = isset($Arr[9])?addslashes($Arr[9]):'';
			$tmp['TradeStatus'] = isset($Arr[10])?addslashes($Arr[10]):'';
			$tmp['OldSales'] = '';
			$tmp['OldCommission'] = '';
			$tmp['OldCur'] = isset($Arr[11])?addslashes($Arr[11]):'';
			$tmp['TradeType'] = '';
		}elseif($af == 'phg' || $af == 'phg_conv' || $af == 'phg_horiz'){
			$tmp['OrderId'] = isset($Arr[7])?addslashes($Arr[7]):'';
			$tmp['ClickTime'] = isset($Arr[8])?addslashes($Arr[8]):'';
			$tmp['TradeId'] = isset($Arr[9])?addslashes($Arr[9]):'';
			$tmp['TradeStatus'] = isset($Arr[10])?addslashes($Arr[10]):'';
			$tmp['OldCur'] = isset($Arr[12])?addslashes($Arr[12]):'';
			$tmp['OldSales'] = isset($Arr[13])?addslashes($Arr[13]):'';
			$tmp['OldCommission'] = isset($Arr[14])?addslashes($Arr[14]):'';
			$tmp['TradeType'] = isset($Arr[15])?addslashes($Arr[15]):'';
		}else{
			$tmp['OrderId'] = isset($Arr[7])?addslashes($Arr[7]):'';
			$tmp['ClickTime'] = isset($Arr[8])?addslashes($Arr[8]):'';
			$tmp['TradeId'] = isset($Arr[9])?addslashes($Arr[9]):'';
			$tmp['TradeStatus'] = isset($Arr[10])?addslashes($Arr[10]):'';
			$tmp['OldCur'] = isset($Arr[11])?addslashes($Arr[11]):'';
			$tmp['OldSales'] = isset($Arr[12])?addslashes($Arr[12]):'';
			$tmp['OldCommission'] = isset($Arr[13])?addslashes($Arr[13]):'';
			$tmp['TradeType'] = isset($Arr[14])?addslashes($Arr[14]):'';
		}

		$rejected_code = array('storniert','cancelled','declined','refunded','rejected','void','invalidated','denied');

		$tmp['TradeStatus'] = strtolower($tmp['TradeStatus']);
		if(in_array($tmp['TradeStatus'], $rejected_code)){
			$tmp['Commission'] = 0;
		}

		return $tmp;
	}

	function do_update_data($data){
		$af = dirname($data['file_path']);
		$af = $af[0] == '/'?substr($af,1):$af;
		$AffId = 0;
		if(isset($this->AffNameIdMap[$af])){
			$AffId = $this->AffNameIdMap[$af];
		}
		
		debug('do update file data : '.$data['file_path']);
		//add transaction data to database
		$file_data = array();

		$file_full_path = $this->data_dir.$data['file_path'];
		// $file_full_path = 'E:\xampp\htdocs\bdg_outgoing\transaction_bdg/app/api/transaction/data/ond/revenue_20150425.dat';
		
		if(is_file($file_full_path)){
			$fp=fopen($file_full_path,'r');
			while(!feof($fp)){
				$line=fgets($fp,4000);
				$line = trim($line);
				if(empty($line))
					continue;

				$Arr = explode("\t",$line);
				if(count($Arr) < 5)
					continue;

				$tmp = $this::format_txt2data($af,$Arr);
				$tmp['Af'] = addslashes($af);
				$tmp['AffId'] = intval($AffId);
				$tmp['TradeKey'] = $this::getTradeKey($tmp['Af'],$tmp['Created'],$tmp['OrderId'],$tmp['SID']);
				$tmp['DataFile'] = $data['file_path'];
				$file_data[] = $tmp;

				// if(count($file_data) > 2000){
				// 	$this->do_update_data_step($file_data);
				// 	$file_data = array();
				// }

			}
			fclose($fp);
		}
		$this->do_update_data_step($file_data);
	}

	function do_update_data_step($file_data){
		//插入 rpt_transaction_base表
		$update_data = array();

		if(!empty($file_data)){
			$DataFile = $file_data[0]['DataFile'];
			
			$TradeKey_tmp = array();
			foreach($file_data as $k=>$v){
				$TradeKey_tmp[] = $v['TradeKey'];
			}

			$where_str = 'Af = "'.$file_data[0]['Af'].'" AND `DataFile` = "'.$DataFile.'" ';
			// $where_str .= 'AND TradeKey IN ("'.join('","', $TradeKey_tmp ).'")';
			$db_data = $this->table('rpt_transaction_base')->where($where_str)->field('Updated,UpdatedDate,Created,CreatedDate,Sales,Commission,IdInAff,ProgramName,SID,OrderId,ClickTime,TradeId,TradeStatus,OldCur,OldSales,OldCommission,TradeType,Af,AffId,TradeKey,DataFile')->find();
			

			$f_d = array();
			//format file data
			foreach($file_data as $k=>$v){
				if(!isset($f_d[$v['TradeKey']])){
					$f_d[$v['TradeKey']]['Commission'] = $v['Commission'].'';
					$f_d[$v['TradeKey']]['Sales'] = $v['Sales'].'';
				}else{
					$f_d[$v['TradeKey']]['Commission'] = $f_d[$v['TradeKey']]['Commission'] + $v['Commission'];
					$f_d[$v['TradeKey']]['Commission'] = $f_d[$v['TradeKey']]['Commission'].'';
					$f_d[$v['TradeKey']]['Sales'] = $f_d[$v['TradeKey']]['Sales'] + $v['Sales'];
					$f_d[$v['TradeKey']]['Sales'] = $f_d[$v['TradeKey']]['Sales'].'';
				}
				$f_d[$v['TradeKey']]['record'][] = $v;
			}


			if($db_data){
				$d_d = array();

				//format db data
				foreach($db_data as $k=>$v){
					if(!isset($d_d[$v['TradeKey']])){
						$d_d[$v['TradeKey']]['Commission'] = $v['Commission'].'';
						$d_d[$v['TradeKey']]['Sales'] = $v['Sales'].'';
					}else{
						$d_d[$v['TradeKey']]['Commission'] = $d_d[$v['TradeKey']]['Commission'] + $v['Commission'];
						$d_d[$v['TradeKey']]['Commission'] = $d_d[$v['TradeKey']]['Commission'].'';
						$d_d[$v['TradeKey']]['Sales'] = $d_d[$v['TradeKey']]['Sales'] + $v['Sales'];
						$d_d[$v['TradeKey']]['Sales'] = $d_d[$v['TradeKey']]['Sales'].'';
					}
					$d_d[$v['TradeKey']]['record'][] = $v;
				}
				
				$new_record = array();
				foreach($f_d as $k=>$v){
					if(!isset($d_d[$k])){
						foreach($v['record'] as $r){
							$r['Updated'] = date('Y-m-d H:i:s');
							$r['UpdatedDate'] = date('Y-m-d');
							$new_record[] = $r;
						}
					}else{
						if($v['Commission'] == $d_d[$k]['Commission']){
							//do nothing
						}else{
							$newCommission = ($v['Commission'] - $d_d[$k]['Commission']).'';
							$newSales = ($v['Sales'] - $d_d[$k]['Sales']).'';
							if($newCommission > 0 || $newCommission < 0){
								$tmp = $v['record'][0];
								$tmp['Commission'] = $newCommission;
								$tmp['Sales'] = $newSales;
								$tmp['Updated'] = date('Y-m-d H:i:s');
								$tmp['UpdatedDate'] = date('Y-m-d');
								$new_record[] = $tmp;	
							}
						}
					}
				}

				#反向比较。如果联盟取消订单为直接删除订单记录
				foreach($d_d as $k=>$v){
					if(!isset($f_d[$k])){
						$newCommission = (0 - $v['Commission']).'';
						$newSales = (0 - $v['Sales']).'';
						if($newCommission > 0 || $newCommission < 0){
							$tmp = $v['record'][0];
							$tmp['Commission'] = $newCommission;	
							$tmp['Sales'] = $newSales;	
							$tmp['Updated'] = date('Y-m-d H:i:s');
							$tmp['UpdatedDate'] = date('Y-m-d');
							$new_record[] = $tmp;
						}
					}
				}

				$update_data = $new_record;
			}else{
				$update_data = $file_data;
			}

			//插入rpt_transaction_unique

			if($db_data){
				$this->update_unique('update',$f_d,$new_record);
			}else{
				$this->update_unique('insert',$f_d);
			}

			#分批执行操作。否则数据库无法插入那么多数据
			if(!empty($update_data)){
				$tmp_data = array();
				foreach($update_data as $v){
					$tmp_data[] = $v;
					if(count($tmp_data) > 500){
						$sql = $this->getInsertSql($tmp_data,'rpt_transaction_base');
						$this->query($sql);
						$tmp_data = array();
					}
				}

				if(!empty($tmp_data)){
					$sql = $this->getInsertSql($tmp_data,'rpt_transaction_base');
					$this->query($sql);
				}
			}

		}
	}

	function update_unique($act="insert",$f_d,$new_record=array()){
		$tmp = array();
		
		if($act == 'insert'){
			$tmp = array();
			foreach($f_d as $k=>$v){
				$a = $v['record'][0];
				$a['Commission'] = $v['Commission'];
				$a['Sales'] = $v['Sales'];
				$tmp[] = $a;
			}
		}else{
			if(!empty($new_record)){
				foreach($new_record as $k=>$v){
					if(isset($f_d[$v['TradeKey']])){
						$a = $f_d[$v['TradeKey']]['record'][0];
						$a['Commission'] = $f_d[$v['TradeKey']]['Commission'];
						$a['Sales'] = $f_d[$v['TradeKey']]['Sales'];
						$a['Updated'] = date('Y-m-d H:i:s');
						$a['UpdatedDate'] = date('Y-m-d');
						$tmp[] = $a;
					}else{
						 if($v['Commission'] > 0 || $v['Commission'] < 0){
						 	$a = $v;
						 	$a['Commission'] = 0;
						 	$a['Sales'] = 0;
						 	$a['Updated'] = date('Y-m-d H:i:s');
							$a['UpdatedDate'] = date('Y-m-d');
							$tmp[] = $a;
						 }
					}
				}
			}
		}

		if(!empty($tmp)){
			$tmp_data = array();
			foreach($tmp as $v){
				$tmp_data[] = $v;
				if(count($tmp_data) > 500){
					$sql = $this->getInsertSql($tmp_data,'rpt_transaction_unique',true);
					$this->query($sql);
					$tmp_data = array();
				}
			}

			if(!empty($tmp_data)){
				$sql = $this->getInsertSql($tmp_data,'rpt_transaction_unique',true);
				$this->query($sql);
			}
		}
	}

	static function getTradeKey($Af,$Created,$OrderId,$SID){
		$key = $Af.'_'.date('YmdHis',strtotime($Created)).'_'.md5($OrderId.'||'.$SID);
		return $key;
	}

}
?> 
