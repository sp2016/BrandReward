<?php
class Statis extends LibFactory
{
    //获取用户的apikey
    function getApiKey($uid){
        $ApiKey = array();
        if(isset($_SESSION['pubAccActiveList']['active'])){
            foreach ($_SESSION['pubAccActiveList']['data'] as $temp){
                $ApiKey[] = $temp['ApiKey'];
            }
        }else {
//             $row = $this->table('publisher_account')->where('PublisherId = '.intval($uid))->find();
            $row = array();
            $i = 0;
            foreach ($_SESSION['pubAccList'] as $temp){
                $row[$i]['ApiKey'] = $temp['ApiKey'];
                $i++;
            }
            foreach ($row as $v){
                $ApiKey[] = $v['ApiKey'];
            }
        }
        return $ApiKey;
    }
    
    //时间范围内commission的总数
	function getCommission($data){
		if(empty($data)){
		    return 0;
		}

		$where_arr = array();
		$where_str = '';

		if($data['startDate']){
			$where_arr[] = 'createddate >= "'.addslashes($data['startDate']).'"';
		}
		if($data['endDate']){
			$where_arr[] = 'createddate <= "'.addslashes($data['endDate']).'"';
		}

		if(!empty($data['apiKey'])){
			$where_arr[] = 'site IN ("'.join('","',$data['apiKey']).'")';
		}

		$where_str = empty($where_arr)?'':join(' AND ',$where_arr);
		$row = $this->table('statis_affiliate_br')->where($where_str)->field('SUM(showrevenues) as commissions')->findone();
		if($row && !empty($row['commissions'])){
			return $row['commissions'];
		}else{
			return 0;
		}
	}
	
	//时间范围内commission的详情
	function getCommissionDetail($data){
	    if(empty($data)){
	        return 0;
	    }
	
	    $where_arr = array();
	    $where_str = '';
	
	    if($data['startDate']){
	        $where_arr[] = 'createddate >= "'.addslashes($data['startDate']).'"';
	    }
	    if($data['endDate']){
	        $where_arr[] = 'createddate <= "'.addslashes($data['endDate']).'"';
	    }
	
	   if(!empty($data['apiKey'])){
			$where_arr[] = 'site IN ("'.join('","',$data['apiKey']).'")';
		}
	
	    $where_str = empty($where_arr)?'':join(' AND ',$where_arr);
	    $row = $this->table('statis_affiliate_br')->where($where_str)->field('createddate,SUM(showrevenues) as data')->group('createddate')->order('createddate asc')->find();
	    return $row;
	}
	
	function getpro(){
		$sql = "select * from publisher_upload";
		return $this->getRows($sql);
	}
	
	//时间范围内点击的总数
	function getClick($data){
		if(empty($data))
			return null;

		$where_arr = array();
		$where_str = '';

		if($data['startDate']){
			$where_arr[] = 'createddate >= "'.addslashes($data['startDate']).'"';
		}
		if($data['endDate']){
			$where_arr[] = 'createddate <= "'.addslashes($data['endDate']).'"';
		}

	    if(!empty($data['apiKey'])){
			$where_arr[] = 'site IN ("'.join('","',$data['apiKey']).'")';
		}

		$where_str = empty($where_arr)?'':join(' AND ',$where_arr);
		$row = $this->table('statis_affiliate_br')->where($where_str)->field('SUM(clicks) as clicks,SUM(clicks_robot) as robotClicks')->findone();
		
		if($row && !empty($row['clicks'])){
		    $row['clicks'] = $row['clicks']-$row['robotClicks'];
		    return $row['clicks'];
		}else{
		    return 0;
		}
		
		/* $where_arr = array();
		$where_str = '';

		if($data['startDate']){
			$where_arr[] = 'createddate >= "'.addslashes($data['startDate']).'"';
		}
		if($data['endDate']){
			$where_arr[] = 'createddate <= "'.addslashes($data['endDate']).'"';
		}

		$where_arr[] = 'affId = 0';

		if($data['uid']){
			$ApiKey = array();
			$row = $this->table('publisher_account')->where('PublisherId = '.intval($data['uid']))->find();
			foreach($row as $v){
				$ApiKey[] = $v['ApiKey'];
			}
			$where_arr[] = 'site IN ("'.join('","',$ApiKey).'")';
		}

		$where_str = empty($where_arr)?'':join(' AND ',$where_arr);
		$row = $this->table('statis_affiliate_br')->where($where_str)->field('SUM(clicks) as clicks')->findone();
		$clicks_unaff = $row['clicks']; 

		return array('total'=>$clicks_total,'unaff'=>$clicks_unaff);*/
	}
	
	//时间范围内点击的详情
	function getClickDetail($data){
	    if(empty($data))
	        return null;
	
	    $where_arr = array();
	    $where_str = '';
	
	    if($data['startDate']){
	        $where_arr[] = 'createddate >= "'.addslashes($data['startDate']).'"';
	    }
	    if($data['endDate']){
	        $where_arr[] = 'createddate <= "'.addslashes($data['endDate']).'"';
	    }
	
	    if(!empty($data['apiKey'])){
			$where_arr[] = 'site IN ("'.join('","',$data['apiKey']).'")';
		}
	
	    $where_str = empty($where_arr)?'':join(' AND ',$where_arr);
	    $row = $this->table('statis_affiliate_br')->where($where_str)->field('createddate,SUM(clicks) as data,SUM(clicks_robot) as robotClicks')->group('createddate')->order('createddate asc')->find();
	    foreach ($row as $key=>$temp){
	        $row[$key]['data'] = $temp['data'] - $temp['robotClicks']; 
	    }
	    return $row;
	}
	
	//时间范围内transaction的总数
	function getTransaction($data){
	    if(empty($data)){
	        return 0;
	    }
	
	    $where_arr = array();
	    $where_str = '';
	
	    if($data['startDate']){
	        $where_arr[] = 'createddate >= "'.addslashes($data['startDate']).'"';
	    }
	    if($data['endDate']){
	        $where_arr[] = 'createddate <= "'.addslashes($data['endDate']).'"';
	    }
	
	    if(!empty($data['apiKey'])){
	        $where_arr[] = 'site IN ("'.join('","',$data['apiKey']).'")';
	    }
	
	    $where_str = empty($where_arr)?'':join(' AND ',$where_arr);
	    $row = $this->table('statis_affiliate_br')->where($where_str)->field('SUM(orders) as transactions')->findone();
	    if($row && !empty($row['transactions'])){
	        return $row['transactions'];
	    }else{
	        return 0;
	    }
	}
	
	//时间范围内transaction的详情
	function getTransactionDetail($data){
	    if(empty($data)){
	        return 0;
	    }
	
	    $where_arr = array();
	    $where_str = '';
	
	    if($data['startDate']){
	        $where_arr[] = 'createddate >= "'.addslashes($data['startDate']).'"';
	    }
	    if($data['endDate']){
	        $where_arr[] = 'createddate <= "'.addslashes($data['endDate']).'"';
	    }
	
	    if(!empty($data['apiKey'])){
	        $where_arr[] = 'site IN ("'.join('","',$data['apiKey']).'")';
	    }
	
	    $where_str = empty($where_arr)?'':join(' AND ',$where_arr);
	    $row = $this->table('statis_affiliate_br')->where($where_str)->field('createddate,SUM(orders) as data')->group('createddate')->order('createddate asc')->find();
	    return $row;
	}
	
	function getTopAdvertises($data){
	    if(empty($data['apiKey'])){
	        return array();
	    }
	    
        $where_str = '';
        $where_arr = array();
        	
        if($data['startDate']){
            $where_arr[] = 'a.createddate >= "'.$data['startDate'].'"';
        }
        if($data['endDate']){
            $where_arr[] = 'a.createddate <= "'.$data['endDate'].'"';
        }
        if(!empty($data['apiKey'])){
            $where_arr[] = 'a.site IN ("'.join('","',$data['apiKey']).'")';
        }

        if(!empty($where_arr)){
            $where_str = ' WHERE '.join(' AND ',$where_arr);
        }

        $sql = 'SELECT a.storeId,SUM(a.showrevenues) as Commission,SUM(a.sales) as Sales,SUM(a.orders) as num,SUM(a.clicks) as clicks,b.`Name` as store
				FROM statis_domain_br a LEFT JOIN store b ON a.storeId = b.ID '.$where_str.' GROUP BY a.storeId HAVING Commission > 0 AND a.storeId > 0 ORDER BY Commission DESC LIMIT 20';

        $row = $this->getRows($sql);

	    return $row;
	}

	function getTransactionRpt($data){
		if(empty($data['uid'])){
			return array();
		}
		////temp for bdg
// 		if($data['uid'] == 7){
// 			$sites_rows = $this->table('publisher_account')->where('PublisherId <= 10')->find();
// 		}else{
			
// 		}
	    if(isset($_SESSION['pubAccActiveList']['active'])){
	        $sites_rows = $_SESSION['pubAccActiveList']['data'];
        }else {
            $sites_rows = $this->table('publisher_account')->where('PublisherId = '.intval($data['uid']))->find();
        }
		$sites = array();
		$site_tmp = array();
		foreach($sites_rows as $v){
			$sites[] = addslashes($v['ApiKey']);
			$site_tmp[$v['ApiKey']] = $v;
		}
		/* if(isset($data['site']) && !empty($data['site'])){
			$sites = array($data['site']);
		} */
		$return_d = array();
// 		$page_data = array();
		$tran_row = array();
		$out_data = array();

		if(!isset($data['type']))
			$data['type'] = 'daily';

		if($data['type'] == 'daily'){
// 			$page = isset($data['p'])&&$data['p']?$data['p']:1;
// 			$page_size = 20;
// 			if(isset($data['pz']))
// 				$page_size = intval($data['pz']);

			$where_str = '';
			$where_arr = array();
			if($data['tran_from']){
				$where_arr[] = 'a.createddate >= "'.$data['tran_from'].'"';
			}
			if($data['tran_to']){
				$where_arr[] = 'a.createddate <= "'.$data['tran_to'].'"';
			}
			$where_arr[] = 'a.site IN ("'.join('","',$sites).'")';
			if(!empty($where_arr)){
				$where_str = ' WHERE '.join(' AND ',$where_arr);
			}
			
			if(isset($data['datetype']) && $data['datetype']=="transactiondate"){
			    $select = "SUM(a.showrevenues) as Commission,SUM(a.sales) as Sales,SUM(a.orders) as num,";
			    $totalSelect = "SUM(a.showrevenues) as totalCommission,SUM(a.clicks) as totalClicks,SUM(a.clicks_robot) as robotclicks,SUM(a.orders) as totalNum";
			}else {
			    $select = "SUM(a.c_showrevenues) as Commission,SUM(a.c_sales) as Sales,SUM(a.c_orders) as num,";
			    $totalSelect = "SUM(a.c_showrevenues) as totalCommission,SUM(a.clicks) as totalClicks,SUM(a.clicks_robot) as robotclicks,SUM(a.c_orders) as totalNum";
			}
			/* $sql = 'SELECT COUNT(*) AS c FROM (
									SELECT a.createddate,SUM(a.showrevenues) as Commission,SUM(a.sales) as Sales,SUM(a.orders) as num,SUM(a.clicks) as clicks
									FROM statis_affiliate_br as a LEFT JOIN publisher_account AS b on a.site = b.ApiKey '.$where_str.' GROUP BY a.createddate ORDER BY a.createddate DESC
									) AS bb';

			$c_row = $this->getRow($sql);
			$total = $c_row['c'];
			$page_data['total'] = $total;
			$page_data['page_now'] = $page;
			$page_data['page_total'] = ceil($total/$page_size);
			$page_data['page_size'] = $page_size; */

			$sql = 'SELECT a.createddate,b.Domain,'.$select.'SUM(a.clicks) as totalclicks,SUM(a.clicks_robot) as robotclicks
					FROM statis_affiliate_br as a LEFT JOIN publisher_account AS b on a.site = b.ApiKey '.$where_str.' GROUP BY a.createddate ORDER BY a.createddate ';//LIMIT '.($page-1)*$page_size.','.$page_size;

			$tran_row = $this->getRows($sql);

			if($_SESSION['pubAccActiveList']['active'] == 'all'){
			    $domain = "All";
			}else {
			    $domain = reset($_SESSION['pubAccActiveList']['data'])['Domain'];
			}
			
			$totalClicks = 0;
			foreach ($tran_row as &$val){
			    if($val['totalclicks'] < $val['robotclicks']){
			        $val['clicks'] = 0;
			    }else {
			        $val['clicks'] = $val['totalclicks'] - $val['robotclicks'];
			    }
			    $totalClicks += $val['clicks'];
			    if($val['clicks']!=0){
			        $val['epc'] = number_format($val['Commission']/$val['clicks'],3);
			    }else {
			        $val['epc'] = '';
			    }
			    $val['Domain'] = $domain;
			}

			$sql = 'SELECT '.$totalSelect.' FROM statis_affiliate_br as a'.$where_str;
			$totalAccount = $this->getRows($sql)[0];
			$totalAccount['totalClicks'] = $totalClicks;

		}elseif($data['type'] == 'merchants'){
// 			$page = isset($data['p'])&&$data['p']?$data['p']:1;
// 			$page_size = 20;
// 			if(isset($data['pz']))
// 				$page_size = intval($data['pz']);
			// get transaction data
			$where_str = '';
			$where_arr = array();
			
			if($data['tran_from']){
				$where_arr[] = 'a.createddate >= "'.$data['tran_from'].'"';
			}
			if($data['tran_to']){
				$where_arr[] = 'a.createddate <= "'.$data['tran_to'].'"';
			}
			$where_arr[] = 'a.site IN ("'.join('","',$sites).'")';
			// $where_arr[] = 'b.StoreAffSupport = "YES"';

			if(!empty($where_arr)){
				$where_str = ' WHERE '.join(' AND ',$where_arr);
			}
			
			$having = ' and Commission > 0 ';
			if(isset($data['earningstype']) && $data['earningstype']=='all'){
			    $having = ' and Commission >= 0 ';
			}
			
			if(isset($data['datetype']) && $data['datetype']=="transactiondate"){
			    $select = "SUM(a.showrevenues) as Commission,SUM(a.sales) as Sales,SUM(a.orders) as num,";
			}else {
			    $select = "SUM(a.c_showrevenues) as Commission,SUM(a.c_sales) as Sales,SUM(a.c_orders) as num,";
			}

			/* $sql = 'SELECT COUNT(*) AS c FROM (
						SELECT storeId,SUM(a.showrevenues) as Commission,SUM(a.sales) as Sales,SUM(a.orders) as num,SUM(a.clicks) as clicks
						FROM statis_domain a LEFT JOIN store b ON a.storeId = b.ID  LEFT JOIN publisher_account as c on a.site = c.Apikey '.$where_str.' GROUP BY storeId HAVING Commission > 0 AND storeId > 0
					) AS bb';

			$c_row = $this->getRow($sql);
			$total = $c_row['c'];

			$page_data['total'] = $total;
			$page_data['page_now'] = $page;
			$page_data['page_total'] = ceil($total/$page_size);
			$page_data['page_size'] = $page_size; */


			 $sql = 'SELECT a.storeId,c.Domain,'.$select.'SUM(a.clicks) as totalclicks,SUM(a.clicks_robot) as robotclicks,b.`Name` as store
					FROM statis_domain_br a LEFT JOIN store b ON a.storeId = b.ID LEFT JOIN publisher_account as c on a.site = c.Apikey '.$where_str.' GROUP BY a.storeId HAVING a.storeId > 0 '.$having.' ORDER BY Commission DESC ';//LIMIT '.($page-1)*$page_size.','.$page_size;

			$tran_row = $this->getRows($sql);

			/* $storeIds = array();
			if($tran_row){
				foreach($tran_row as $k=>$v){
					$storeIds[] = intval($v['storeId']);
				}
			}


			if(!empty($storeIds)){
				$sql = 'SELECT ID,`Name` FROM store WHERE ID IN ('.join(',',$storeIds).')';
				$row = $this->getRows($sql);
			}else{
				$row = array();
			}

			$domain_list = array();

				foreach($row as $k=>$v){
					$domain_list[$v['ID']] = $v['Name'];
				}


				foreach($tran_row as $k=>$v){

					if(isset($domain_list[$v['storeId']]))
						$tran_row[$k]['store'] = $domain_list[$v['storeId']];
				} */
			
			if($_SESSION['pubAccActiveList']['active'] == 'all'){
			    $domain = "All";
			}else {
			    $domain = reset($_SESSION['pubAccActiveList']['data'])['Domain'];
			}
			
			$totalAccount['totalCommission'] = 0;
			$totalAccount['totalNum'] = 0;
			$totalAccount['totalClicks'] = 0;
			$totalAccount['robotClicks'] = 0;
			foreach ($tran_row as &$val){
			    if($val['totalclicks'] < $val['robotclicks']){
			        $val['clicks'] = 0;
			    }else {
			        $val['clicks'] = $val['totalclicks'] - $val['robotclicks'];
			    }
			    $totalAccount['totalCommission'] += $val['Commission'];
			    $totalAccount['totalNum'] += $val['num'];
			    $totalAccount['totalClicks'] += $val['clicks'];
			    if($val['clicks']!=0){
			        $val['epc'] = number_format($val['Commission']/$val['clicks'],3);
			    }else {
			        $val['epc'] = '';
			    }
			    $val['Domain'] = $domain;
			}

			/* $sql = 'SELECT SUM(a.showrevenues) as totalCommission,SUM(a.orders) as totalNum
					FROM statis_domain a LEFT JOIN store b ON a.storeId = b.ID '.$where_str;

			$totalAccount = $this->getRows($sql)[0]; */

		}elseif($data['type'] == 'sites'){
			// get transaction data
			$where_str = '';
			$where_arr = array();
			
			if($data['tran_from']){
				$where_arr[] = 'createddate >= "'.$data['tran_from'].'"';
			}
			if($data['tran_to']){
				$where_arr[] = 'createddate <= "'.$data['tran_to'].'"';
			}
			$where_arr[] = 'site IN ("'.join('","',$sites).'")';

			if(!empty($where_arr)){
				$where_str = ' WHERE '.join(' AND ',$where_arr);
			}
			
			if(isset($data['datetype']) && $data['datetype']=="transactiondate"){
			    $select = "SUM(showrevenues) as Commission,SUM(sales) as Sales,SUM(orders) as num,";
			    $totalSelect = "SUM(showrevenues) as totalCommission,SUM(orders) as totalNum";
			}else {
			    $select = "SUM(c_showrevenues) as Commission,SUM(c_sales) as Sales,SUM(c_orders) as num,";
			    $totalSelect = "SUM(c_showrevenues) as totalCommission,SUM(c_orders) as totalNum";
			}
			
			$sql = 'SELECT site,'.$select.'SUM(clicks) as clicks 
					FROM statis_affiliate_br '.$where_str.' GROUP BY site ORDER BY Commission DESC';
			$tran_row_tmp = $this->getRows($sql);
			$tran_row = array();
			foreach($tran_row_tmp as $v){
				$tran_row[$v['site']] = $v;
			}
			foreach($sites as $v){
				if(!isset($tran_row[$v])){
					$tran_row[$v] = array('site'=>$v,'Commission'=>'0.00','Sales'=>'0.00','num'=>'0','Alias'=>$site_tmp[$v]['Alias'],'Domain'=>$site_tmp[$v]['Domain']);
				}else{
					$tran_row[$v]['Alias'] = $site_tmp[$v]['Alias'];
					$tran_row[$v]['Domain'] = $site_tmp[$v]['Domain'];
				}
			}
			$sql = 'SELECT '.$totalSelect.' FROM statis_affiliate_br '.$where_str;
			$totalAccount = $this->getRows($sql)[0];
		}

		$return_d['tran'] = $tran_row;
// 		$return_d['page'] = $page_data;
		$return_d['totalAccount'] = $totalAccount;

		return $return_d;
	}

}