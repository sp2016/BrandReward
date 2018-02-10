<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');
$do_upload = 0;
$res_upload = 0;
$msg_upload = '';

$objTran = new Transaction();

if(!empty($_POST)){
	if($_POST['act'] == 'check_file_exist'){
		$row = $objTran->table('rpt_transaction_file')->where('file_path = "/transaction/'.addslashes($_POST['file_name']).'"')->find();
		if($row){
			echo 1;exit();
		}else{
			echo 0;exit();
		}
	}
}

if(isset($_FILES['uploadfile']) && !empty($_FILES['uploadfile'])){
	// debug('正在上传文件...');

	$do_upload = 1;
	if(substr($_FILES['uploadfile']['name'],-3) != 'csv'){
		$msg_upload = '文件格式错误,限定csv';
	}elseif($_FILES['uploadfile']['error']){
		$msg_upload = '上传失败';
	}else{
		$data_file_dir = INCLUDE_ROOT.'data/upload/transaction/';
		$data_file_name = $data_file_dir.$_FILES['uploadfile']['name'];
		move_uploaded_file($_FILES['uploadfile']['tmp_name'],$data_file_name);
		$data_file = addslashes('/transaction/'.$_FILES['uploadfile']['name']);
		$data_file_md5 = md5_file($data_file_name);
		// $data_file_name = INCLUDE_ROOT.'data/upload/transaction/Affiliate performance 201501-20150204.csv';
		// $data_file = addslashes('/transaction/Affiliate performance 201501-20150204.csv');
		// $data_file_md5 = md5_file($data_file_name);

		// debug('正在解读文件...');
		if (($handle = fopen($data_file_name, "r")) !== FALSE) {
			$line = 0;
			$updata_data = array();
			$unknownAffName = array();
		    while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
		    	$line++;
		    	if($line == 1)continue;

		    	if(empty($data[0]) || empty($data[1])){
		    		continue;
		    	}

		    	$d = new Datetime($data[1]);
		    	$tmp = array();
		    	$tmp['Af'] = addslashes(strtolower($data[0]));
		    	if(isset($sys_aff_name_id_map[$tmp['Af']])){
		    		$tmp['AffId'] = $sys_aff_name_id_map[$tmp['Af']];
		    	}else{
		    		$tmp['AffId'] = '';
		    		if(!in_array($tmp['Af'],$unknownAffName))
		    			$unknownAffName[] = $tmp['Af'];
		    	}
		    	$tmp['DataFile'] = $data_file;
		    	$tmp['Updated'] = $d->format('Y-m-d H:i:s');
				$tmp['UpdatedDate'] = $d->format('Y-m-d');
				$tmp['Created'] = $d->format('Y-m-d H:i:s');
				$tmp['CreatedDate'] = $d->format('Y-m-d');
				$tmp['Sales'] = floatval($data[3]);
				$tmp['Commission'] = floatval($data[4]);
				$tmp['IdInAff'] = addslashes($data[6]);
				$tmp['ProgramName'] = addslashes($data[5]);
				$tmp['SID'] = isset($data[7])?addslashes($data[7]):'';
				$tmp['OrderId'] = isset($data[8])?addslashes($data[8]):'';
				
				$updata_data[] = $tmp;
		    }
		    fclose($handle);

		    if(!empty($updata_data)){

		    	// debug('正在识别联盟名称...');
			    $aff_tmp = $objTran->table('wf_aff')->where('`Name` in ("'.join('","',$unknownAffName).'")')->field('ID,`Name`')->find();
			    $aff_row = array();
			    foreach($aff_tmp as $k=>$v){
			    	$aff_row[strtolower($v['Name'])] = $v['ID'];
			    }

			    foreach($updata_data as $k=>$v){
			    	if(empty($v['AffId']) && isset($aff_row[$v['Af']]))
			    		$updata_data[$k]['AffId'] = $aff_row[$v['Af']];
			    }

			    //如果存在则删除该文件原有数据
			    $count_row = $objTran->table('rpt_transaction_base')->where('DataFile = "'.$data_file.'"')->count()->findone();
			    if($count_row > 0){
			    	$sql = 'DELETE FROM rpt_transaction_base WHERE DataFile = "'.$data_file.'"';
			    	$objTran->query($sql);
			    }
			    // debug('正在保存文件,数据共'.count($updata_data).'条...');
			    //插入数据
			    if(!empty($updata_data)){
			    	$pz = 2000;
			    	if(count($updata_data) > $pz){
			    		$p = ceil(count($updata_data)/$pz);
			    		$uptmp = array();
			    		for ($i=0; $i < $p; $i++) { 
			    			$uptmp = array_slice($updata_data, $i*$pz,$pz);
			    			$sql = $objTran->getInsertSql($uptmp,'rpt_transaction_base');
							$objTran->query($sql);	
			    		}

			    	}else{
			    		$sql = $objTran->getInsertSql($updata_data,'rpt_transaction_base');
						$objTran->query($sql);
			    	}
			    }
			   
			   	// debug('正在补充文件数据...');
			    //更新 site publishKey等信息
			    info_data($data_file);

			    //更新文件管理
			    $sql = 'REPLACE INTO rpt_transaction_file (file_path,file_md5) VALUE ("'.$data_file.'","'.$data_file_md5.'")' ;
			    $objTran->query($sql);
			    // debug('上传完毕...');

			    $res_upload = 1;
			    $msg_upload = '保存成功,记录共'.count($updata_data).'条';
		    }else{
		    	$msg_upload = '文件有效记录为空';
		    }
		}
	}
	// echo "<pre>";print_r(1);exit();
}


// echo "<pre>";print_r($_GET);exit();
$objTpl->assign('do_upload', $do_upload);
$objTpl->assign('res_upload', $res_upload);
$objTpl->assign('msg_upload', $msg_upload);
$objTpl->assign('sys_header', $sys_header);
$objTpl->display('tran_upload.html');


function info_data($data_file){
	global $sys_site_tracking_code;
	$objTran = new Transaction();
	$site_tracking_code = $sys_site_tracking_code;

	$pageSize = 500;
	$debug_data = array();
	$countRow = $objTran->table('rpt_transaction_base')->where('DataFile = "'.$data_file.'"')->count()->findone();
	$count = $countRow['tp_count'];

	$debug_data['pCount'] = $count;
	$page = ceil($count / $pageSize);
	$debug_data['page'] = $page;

	if($count > 0 ){
		$i = 0;
		while(1){
			$i++;

			$sql = 'SELECT a.ID,a.SID,b.site,b.publishTracking,a.AffId as OAffId,c.AffId  
					FROM rpt_transaction_base AS a 
					LEFT JOIN bd_out_tracking AS b ON a.SID = b.sessionId 
					LEFT JOIN program_intell AS c ON b.programId = c.ProgramId 
					WHERE a.DataFile = "'.$data_file.'" LIMIT '.($i-1)*$pageSize.','.$pageSize;

			$row = $objTran->getRows($sql);

			if(empty($row))
				break;

			$up_data = array();

			foreach($row as $k=>$v){
				$id = $v['ID'];
				$sid = $v['SID'];
				$site = $v['site'];
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
				if(!$site && strlen($sid) == 3){
					$code = $sid;
					if(isset($site_tracking_code[$code]))
						$site = $site_tracking_code[$code];
				}

				$site = $site?$site:'unknown';

				$affid = $v['AffId']?$v['AffId']:$v['OAffId'];
				

				$up_data[] = array(
					'ID'=>$id,
					'Site'=>$site,
					'Affid'=>$affid,
					'PublishTracking'=>$publishTracking,
				);
			}

			if(!empty($up_data)){
				$sql = $objTran->getBatchUpdateSql($up_data,'rpt_transaction_base','ID');	
				
				$objTran->query($sql);
			}
			
			$debug_data['doing'] = $i;
			// debug('doing info data...all count('.$debug_data['pCount'].') doing page('.$i.'/'.$debug_data['page'].')');
		}
	}
}
?>