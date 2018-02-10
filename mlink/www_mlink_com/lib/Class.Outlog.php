<?php
class Outlog extends LibFactory
{
	function get_out_ov($data,$type='site'){
		$where = array();
		if(isset($data['sel_createddate_start']) && $data['sel_createddate_start']){
			$where[] = 'created >= "'.addslashes($data['sel_createddate_start']).'"';
		}

		if(isset($data['sel_createddate_end']) && $data['sel_createddate_end']){
			$where[] = 'created <= "'.addslashes($data['sel_createddate_end']).'"';
		}


		if(isset($data['sel_site']) && !empty($data['sel_site'])){
			$where[] = ' site = "'.$data['sel_site'].'"';
		}else{
			$where[] = ' site != ""';
		}

		$where_str = '';
		$where_str = join(' AND ', $where);
		$where_str = $where_str?' WHERE '.$where_str:'';

		if($type == 'site'){
			$sql = 'SELECT site,count(*) as c FROM bd_out_tracking '.$where_str.' GROUP BY site';
			$rows = $this->getRows($sql);

			$tmp = array();
			$c = 0;
			if(!empty($rows)){
				foreach($rows as $k=>$v){
					$tmp[$v['site']] = $v['c'];
					$c += $v['c'];
				}	
			}
		}else{
			$sql = 'SELECT LEFT(created,10) as cd,count(*) as c FROM bd_out_tracking '.$where_str.' GROUP BY cd';
			$rows = $this->getRows($sql);

			$tmp = array();
			$c = 0;
			if(!empty($rows)){
				foreach($rows as $k=>$v){
					$tmp[$v['cd']] = $v['c'];
					$c += $v['c'];
				}	
			}
		}
		


		$return = array();
		$return['row'] = $tmp;
		$return['c'] = $c;
		
		return $return;
	}

	function getUnafiliatedRpt($data,$page_size=20){
		if(!isset($data['uid']) || empty($data['uid'])){
			return array();
		}
// 		$sites_rows = $this->table('publisher_account')->where('PublisherId = '.intval($data['uid']))->find();
		$sites_rows = array();
		$i = 0;
		foreach ($_SESSION['pubAccList'] as $temp){
		    $sites_rows[$i]['ApiKey'] = $temp['ApiKey'];
		    $i++;
		}
		
		$sites = array();
		$site_tmp = array();
		foreach($sites_rows as $v){
			$sites[] = addslashes($v['ApiKey']);
			$site_tmp[$v['ApiKey']] = $v;
		}

		$return_d = array();
		$where_str = '';
		$where_arr = array();

		$page = isset($data['p'])&&$data['p']?intval($data['p']):1;

		$where_arr[] = 'site IN ("'.join('","',$sites).'")';
		$where_arr[] = 'domainUsed != ""';
		if($data['tran_from']){
			$where_arr[] = 'created >= "'.$data['tran_from'].' 00:00:00"';
		}
		if($data['tran_to']){
			$where_arr[] = 'created <= "'.$data['tran_to'].' 23:59:59"';
		}
		if(!empty($where_arr)){
			$where_str = join(' AND ',$where_arr);
		}

		$sql = 'SELECT count(*) as c FROM (
					SELECT domainUsed,COUNT(*) AS c,SUM(CASE WHEN programId = 0 THEN 1 ELSE 0 END ) AS unaff FROM bd_out_tracking WHERE '.$where_str.' GROUP BY domainUsed 
				) AS dd WHERE unaff > 0 ';

		$row_c = $this->getRow($sql);

		$return_d['page_total'] = ceil($row_c['c']/$page_size);
		$return_d['page_now'] = $page;
		$return_d['total_num'] = $row_c['c'];

		$sql = 'SELECT * FROM (
					SELECT domainUsed,COUNT(*) AS c,SUM(CASE WHEN programId = 0 THEN 1 ELSE 0 END ) AS unaff FROM bd_out_tracking WHERE '.$where_str.' GROUP BY domainUsed 
				) AS dd WHERE unaff > 0 ORDER BY unaff DESC LIMIT '.($page-1)*$page_size.','.$page_size;

		$row = $this->getRows($sql);
		$return_d['data'] = $row;
		return $return_d;
	}

	function getClicks($data){
		if(empty($data))
			return 0;


		$where_arr = array();
		$where_str = '';

		if($data['uid']){
			$ApiKey = array();
// 			$row = $this->table('publisher_account')->where('PublisherId = '.intval($data['uid']))->find();
			$row = array();
			$i = 0;
			foreach ($_SESSION['pubAccList'] as $temp){
			    $row[$i]['ApiKey'] = $temp['ApiKey'];
			    $i++;
			}
			
			foreach($row as $v){
				$ApiKey[] = $v['ApiKey'];
			}
			$where_arr[] = 'Site IN ("'.join('","',$ApiKey).'")';
		}

		if($data['from']){
			$where_arr[] = 'created >= "'.addslashes($data['from']).' 00:00:00"';
		}
		if($data['to']){
			$where_arr[] = 'created <= "'.addslashes($data['to']).' 23:59:59"';
		}

		$where_str = empty($where_arr)?'':join(' AND ',$where_arr);
		$row = $this->table('bd_out_tracking')->where($where_str)->count()->findone();
		
		if($row && !empty($row['tp_count'])){
			return $row['tp_count'];
		}else{
			return 0;
		}
	}

	function getAffUsed($data){
		if(empty($data))
			return 0;


		$where_arr = array();
		$where_str = '';

		if($data['uid']){
			$ApiKey = array();
// 			$row = $this->table('publisher_account')->where('PublisherId = '.intval($data['uid']))->find();
			$row = array();
			$i = 0;
			foreach ($_SESSION['pubAccList'] as $temp){
			    $row[$i]['ApiKey'] = $temp['ApiKey'];
			    $i++;
			}
			foreach($row as $v){
				$ApiKey[] = $v['ApiKey'];
			}
			$where_arr[] = 'Site IN ("'.join('","',$ApiKey).'")';
		}

		if($data['from']){
			$where_arr[] = 'created >= "'.addslashes($data['from']).' 00:00:00"';
		}
		if($data['to']){
			$where_arr[] = 'created <= "'.addslashes($data['to']).' 23:59:59"';
		}

		$where_str = empty($where_arr)?'':join(' AND ',$where_arr);
		$row = $this->table('bd_out_tracking')->where($where_str)->field('COUNT(*) total,SUM(CASE WHEN programId =0 THEN 1 ELSE 0 END) AS unaff')->findone();
		
		if($row){
			return $row;
		}else{
			return array('total'=>0,'unaff'=>0);
		}
	}

	function getTrifficList($search,$type= 'advertiser'){
		$return_d = array();
		if($type = 'advertiser'){
			$where_arr = array();
			if(isset($search['tran_to'])){
				$where_arr[] = 'createddate <= "'.$search['tran_to'].'"';
			}
			if(isset($search['tran_from'])){
				$where_arr[] = 'createddate >= "'.$search['tran_from'].'"';
			}

			if(isset($search['id'])){
				list(,$storeid) = explode('_',$search['id']);
				$sql = 'SELECT * FROM r_store_domain WHERE storeid = '.intval($storeid);
				$rows = $this->getRows($sql);
				$domainids = array();
				if(!empty($rows)){
					foreach($rows as $k=>$v){
						$domainids[] = $v['DomainId'];
					}
				}else{
					return array();
				}

				$where_arr[] = 'domainId IN ('.join(',',$domainids).')';
			}else{
				return array();
			}

			$sql = 'SELECT count(*) as c FROM bd_out_tracking WHERE '.join(' AND ',$where_arr).'';
			$arr = $this->getRows($sql);

			$page = isset($search['p'])&&$search['p']?$search['p']:1;
			$page_size = isset($search['page_size'])&&$search['page_size']?$search['page_size']:20;

			$total = $arr['0']['c'];
	        $page_data['total'] = $total;
	        $page_data['page_now'] = $page;

	        $page_data['page_total'] = ceil($total/$page_size);
	        $page_data['page_size'] = $page_size;

	        $sql = 'SELECT * FROM bd_out_tracking WHERE '.join(' AND ',$where_arr).' order by id desc LIMIT '.($page-1)*$page_size.','.$page_size;
			$arr = $this->getRows($sql);

			if(!empty($arr)){
				$siteids = array();
				foreach($arr as $k=>$v){
					$siteids[] = $v['site'];
				}

				$sql = 'SELECT * FROM publisher_account WHERE ApiKey IN ("'.join('","',$siteids).'")';
				$tmp = $this->getRows($sql);
				$tmp_site_domain = array();
				foreach($tmp as $k=>$v){
					$tmp_site_domain[$v['ApiKey']] = $v;
				}

				foreach($arr as $k=>$v){
					$arr[$k]['Domain'] = $tmp_site_domain[$v['site']]['Domain'];
				}
			}
			

	        $return_d['tran'] = $arr;
        	$return_d['page'] = $page_data;

		}

		return $return_d;
	}
}