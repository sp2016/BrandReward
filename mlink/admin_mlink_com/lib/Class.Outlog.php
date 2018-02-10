<?php
class Outlog extends LibFactory
{
	function get_check_jump_res_data($data,$page_size=20){
		$return_d = array();
		$where_str = '';
		$where_arr = array();

		$page = isset($data['p'])?intval($data['p']):1;


		if(isset($data['is200']) && intval($data['is200']) < 3){
			$where_arr[] = 'is200 = '.intval($data['is200']);
		}

		if(isset($data['issame']) && intval($data['issame']) < 3){
			$where_arr[] = 'issame = '.intval($data['issame']);
		}

		if(isset($data['issimilar']) && intval($data['issimilar']) < 3){
			$where_arr[] = 'issimilar = '.intval($data['issimilar']);
		}

		if(isset($data['ishostname']) && intval($data['ishostname']) < 3){
			$where_arr[] = 'ishostname = '.intval($data['ishostname']);
		}

		if(isset($data['checkres']) && $data['checkres']){
			$where_arr[] = 'checkres = "'.addslashes($data['checkres']).'"';
		}

		if(isset($data['pageUrl']) && $data['pageUrl']){
			$where_arr[] = 'pageUrl LIKE "'.addslashes($data['pageUrl']).'%"';
		}

		if(!empty($where_arr)){
			$where_str = join(' AND ',$where_arr);     
		}

		$c_row = $this->table('chk_url_jump_res')->where($where_str)->count()->findone();

		$return_d['page_total'] = ceil($c_row['tp_count']/$page_size);//ceil()向上舍入最为接近的正数
		$return_d['page_now'] = $page;
		$return_d['total_num'] = $c_row['tp_count'];

		$row = $this->table('chk_url_jump_res')->where($where_str)->page($page)->limit($page_size)->find();
		$return_d['data'] = $row;
		return $return_d;
	}

	function get_affname(){
		return $this->table('wf_aff')->field('ID,Name')->where("isactive = 'YES'")->order("Name asc")->find();

	}
	function get_check_jump_mer_data($data,$page_size=20){

		$return_d = array();
		$where_str = '';
		$where_arr = array();

		$page = isset($data['p'])?intval($data['p']):1;


		if(isset($data['is200']) && intval($data['is200']) == 1){
			$where_arr[] = 'pagehttpcode IN ("200","304")';
		}

		if(isset($data['is200']) && intval($data['is200']) == 0){
			$where_arr[] = 'pagehttpcode NOT IN ("200","304")';
		}

		if(isset($data['isbad']) && intval($data['isbad']) == 1){
			$where_arr[] = 'httpcode NOT IN ("200","304")';
		}

		if(isset($data['isbad']) && intval($data['isbad']) == 0){
			$where_arr[] = 'httpcode IN ("200","304")';
		}

		if(isset($data['checkres']) && $data['checkres']){
			$where_arr[] = 'checkres = "'.addslashes($data['checkres']).'"';
		}

		if(isset($data['issame']) && intval($data['issame']) < 3){
			$where_arr[] = 'issame = '.intval($data['issame']);
		}

		if(isset($data['issimilar']) && intval($data['issimilar']) < 3){
			$where_arr[] = 'issimilar = '.intval($data['issimilar']);
		}

		if(isset($data['ishostname']) && intval($data['ishostname']) < 3){
			$where_arr[] = 'ishostname = '.intval($data['ishostname']);
		}

		if(isset($data['pageUrl']) && $data['pageUrl']){
			$where_arr[] = 'pageUrl LIKE "'.addslashes($data['pageUrl']).'%"';
		}

		if(isset($data['site']) && $data['site']){
			$where_arr[] = 'site = "'.addslashes($data['site']).'"';
		}

		if(isset($data['id']) && $data['id']){
			$where_arr[] = 'ID = "'.intval($data['id']).'"';
		}		

		if(isset($data['op_name']) && $data['op_name']){
			$where_arr[] = 'op_name = "'.addslashes($data['op_name']).'"';
		}	

		if(!empty($where_arr)){
			$where_str = join(' AND ',$where_arr);     
		}

		$c_row = $this->table('chk_url_jump_mer')->where($where_str)->count()->findone();

		$return_d['page_total'] = ceil($c_row['tp_count']/$page_size);//ceil()向上舍入最为接近的正数
		$return_d['page_now'] = $page;
		$return_d['total_num'] = $c_row['tp_count'];

		$row = $this->table('chk_url_jump_mer')->where($where_str)->order('id desc')->page($page)->limit($page_size)->find();

		if($row){
			$affids = array();
			$pids = array();
			foreach($row as $k=>$v){
				$affids[] = $v['affId'];
				$pids[] = $v['programId'];
			}

			$aff_tmp = $this->table('wf_aff')->where('ID iN ('.join(',',$affids).')')->find();
			$program_tmp = $this->table('program')->where('ID iN ('.join(',',$pids).')')->find();

			$aff_row = array();
			$program_row = array();

			foreach($aff_tmp as $k=>$v){
				$aff_row[$v['ID']] = $v;
			}

			foreach($program_tmp as $k=>$v){
				$program_row[$v['ID']] = $v;
			}

			foreach($row as $k=>$v){
				if(isset($aff_row[$v['affId']]))
					$row[$k]['aff_name'] = $aff_row[$v['affId']]['Name'];
				if(isset($program_row[$v['programId']]))
					$row[$k]['program_name'] = $program_row[$v['programId']]['Name'];
			}
		}
		
		$return_d['data'] = $row;
		return $return_d;
	}


	function get_out_going_log_data($data,$page,$page_size){
		$return_d = array();
		$where_str = '';
		$where_arr = array();

		//统计Click等信息查询条件
		$where_str_x = '';
		$where_arr_x = array();
		$from = $data['from'];
		$to = $data['to'];
		$table = empty($data['type'])?'bd_out_tracking_min':$data['type'];
		$where_arr[] = "a.createddate>='".$from."' AND a.createddate<='".$to."'";
		//统计Click等信息查询条件
		$where_arr_x[] = "a.createddate>='".$from."' AND a.createddate<='".$to."'";
		if(isset($data['pid']) && !empty($data['pid'])){
			$apikey = array();
			if(is_numeric(trim($data['pid']))){
				$sql = 'SELECT * FROM publisher_account where id = '.intval($data['pid']);
				$rows = $this->getRows($sql);
				if(!empty($rows)){
					foreach($rows as $v){
						$apikey[] = $v['ApiKey'];
					}
				}
			}else{
				$sql = 'SELECT * FROM publisher_account WHERE `Name` = "'.addslashes(trim($data['pid'])).'"';
				$rows = $this->getRows($sql);
				if(!empty($rows)){
					foreach($rows as $v){
						$apikey[] = $v['ApiKey'];
					}
				}

				if(empty($apikey)){
					$sql = 'SELECT * FROM publisher_account WHERE `Domain` = "'.addslashes(trim($data['pid'])).'"';
					$rows = $this->getRows($sql);
					if(!empty($rows)){
						foreach($rows as $v){
							$apikey[] = $v['ApiKey'];
						}
					}
				}

				if(empty($apikey)){
					$sql = 'SELECT * FROM publisher_account WHERE `Alias` = "'.addslashes(trim($data['pid'])).'"';
					$rows = $this->getRows($sql);
					if(!empty($rows)){
						foreach($rows as $v){
							$apikey[] = $v['ApiKey'];
						}
					}
				}

				if(empty($apikey)){
					$sql = 'SELECT * FROM publisher_account WHERE PublisherId IN (SELECT ID FROM publisher WHERE `Name` = "'.addslashes(trim($data['pid'])).'")';
					$rows = $this->getRows($sql);
					if(!empty($rows)){
						foreach($rows as $v){
							$apikey[] = $v['ApiKey'];
						}
					}
				}

				if(empty($apikey)){
					$sql = 'SELECT * FROM publisher_account WHERE PublisherId IN (SELECT ID FROM publisher WHERE `Domain` = "'.addslashes(trim($data['pid'])).'")';
					$rows = $this->getRows($sql);
					if(!empty($rows)){
						foreach($rows as $v){
							$apikey[] = $v['ApiKey'];
						}
					}
				}

				if(empty($apikey)){
					$sql = 'SELECT * FROM publisher_account WHERE PublisherId IN (SELECT ID FROM publisher WHERE `Email` = "'.addslashes(trim($data['pid'])).'")';
					$rows = $this->getRows($sql);
					if(!empty($rows)){
						foreach($rows as $v){
							$apikey[] = $v['ApiKey'];
						}
					}
				}
			}
			if(!empty($apikey)){
				$where_arr[] = 'a.site IN ("' . join('","', $apikey) . '")';
				//统计Click等信息查询条件
				$where_arr_x[] = 'a.site IN ("' . join('","', $apikey) . '")';
			}else{
				$return_d['total_num'] = 0;
				$return_d['hasorder'] = 0;
				$return_d['data'] = '';
				return $return_d;
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
				$where_arr[] = 'a.site IN ("'.join('","',$site_keys).'")';
				//统计Click等信息查询条件
				$where_arr_x[] = 'a.site IN ("' . join('","', $site_keys) . '")';
			}else{
				$where_arr[] = "0=1";
				//统计Click等信息查询条件
				$where_arr_x[] = "0=1";
			}
		}
		if(isset($data['advertiser']) && !empty($data['advertiser'])){
			$adv = addslashes(trim($data['advertiser']));
			$sql = "SELECT r.domainid FROM r_store_domain r WHERE r.storeid IN(SELECT id FROM store s WHERE s.Name ='{$adv}')";
			$result = $this->getRows($sql);
			if(empty($result)){
				$sql = "SELECT r.domainid FROM r_store_domain r WHERE r.storeid IN(SELECT id FROM store s WHERE s.NameOptimized ='{$adv}')";
				$result = $this->getRows($sql);
			}
			if($result){
				$tmp = array();
				foreach ($result as $v) {
					$tmp[] = $v['domainid'];
				}
				$where_arr[] = 'a.DomainId IN (' . join(',', $tmp) . ')';
				//统计Click等信息查询条件
				$where_arr_x[] = 'a.domainid IN (' . join(',', $tmp) . ')';
			}else{
				$return_d['total_num'] = 0;
				$return_d['hasorder'] = 0;
				$return_d['data'] = '';
				return $return_d;
			}
		}
		if(isset($data['affiliate']) && !empty($data['affiliate'])){
			   $aid = $data['affiliate'];
			   $where_arr[] = "a.affid in($aid)";
				//统计Click等信息查询条件
				$where_arr_x[] = "a.affid in($aid)";
		}
		if(isset($data['country']) && !empty($data['country'])){
				$country = $data['country'];
				$where_arr[] = "a.country in($country)";
				//统计Click等信息查询条件
				$where_arr_x[] = "a.country in($country)";
		}
		if(isset($data['linkid']) && !empty($data['linkid']))
		{
			$linkid = $data['linkid'];
			$where_arr[] = "a.linkId = $linkid";

			$xsql = "SELECT GROUP_CONCAT(ProgramId) AS `programs` FROM content_feed_new WHERE EncodeId = $linkid GROUP BY EncodeId LIMIT 1";
			$xrow = $this->getRow($xsql);

			$programs = isset($xrow['programs']) ? $xrow['programs'] : '';

			if (empty($programs)) {
				$where_arr_x[] = "0=1";
			} else {
				$where_arr_x[] = "programid=$programs";
			}

		}

		if(!empty($where_arr)){
			$where_str = 'WHERE '.join(' AND ',$where_arr);
		}else{
			$where_str = '';
		}

		if(!empty($where_arr_x)){
			$where_str_x = 'WHERE '.join(' AND ',$where_arr_x);
		}else{
			$where_str_x = '';
		}
		if($table == 'bd_out_tracking_publisher'){
			$mkWhereSql = mk_publisher_where();
			$sql = "SELECT b.`ApiKey` FROM publisher AS a LEFT JOIN publisher_account AS b ON a.`ID` = b.`PublisherId` WHERE $mkWhereSql AND b.ApiKey IS NOT NULL";
			$res = $this->getRows($sql);
			if(!empty($res)){
				$keyid=' and a.site NOT IN(';
				foreach($res as $k){
					$keyid.='"'.$k['ApiKey'].'",';
				}
				$where_str.= rtrim($keyid,',').")";
				$where_str_x .= rtrim($keyid,',').")";
			}
		}
		$sql = "SELECT SUM(clicks) AS `clicks`,SUM(clicks_robot) AS `rob`,SUM(clicks_robot_p) AS `robp`,SUM(sales) AS `sales`,sum(revenues) AS `commission` FROM  statis_br a $where_str_x LIMIT 1";
//		echo $sql;
//		die;
		$total = $this->getRow($sql);
		$return_d['total_num'] = $total['clicks'];
		$return_d['clicks'] = $total['clicks'] - $total['rob'];
		$return_d['rob'] = $total['rob'];
		$return_d['robp'] = $total['robp'];
		if($data['download'] == 0) {
//			$where_str_s = str_replace('a.createddate', 'a.Visited', $where_str);
//			$sumsql = 'SELECT sum(a.Sales) as sales,sum(a.Commission) as commission FROM rpt_transaction_unique as a '.$where_str_s.' and af NOT IN ("bdg","mk","mega")';
//			$sumrow = $this->getRow($sumsql);
			$return_d['sum'] = $total;
		}
		$sql = "select a.id,a.affId,a.domainId,a.site,a.sessionId,a.IsRobet,a.linkId from bd_out_tracking_min as a ".$where_str." ORDER BY  a.createddate,a.id desc Limit $page,$page_size";
		$row = $this->getRows($sql);
		if(empty($total['clicks'])){
			$return_d['total_num'] = 0;
			$return_d['hasorder'] = 0;
			$return_d['data'] = '';
			$return_d['sum']['commission'] = 0;
			$return_d['sum']['sales'] = 0;
			return $return_d;
		}
		if(!empty($row)){
			$pids = array();
			$affids = array();
			$apikeys = array();
			$tid = array();
			foreach($row as $k=>$v){
				$pids[] = $v['domainId'];
				$affids[] = $v['affId'];
				$apikeys[] = $v['site'];
				$tid[] = $v['id'];
				$sid[] = $v['sessionId'];
			}
			$url_rows = $this->table('bd_out_tracking')->field('id,sessionId,pageUrl,outUrl,country,created')->where('id IN ('.join(',',$tid).')')->find();
			foreach($url_rows as $k=>$v){
				$url_arr[$v['id']] = $v;
			}
			$sql = 'select a.`ID`,IF(c.NameOptimized="",c.Name,c.NameOptimized) as `Name` from domain as a left join r_store_domain as b on a.`ID` = b.`DomainId` left join store as c on b.`StoreId` = c.`ID` where a.`ID` IN ('.join(',',$pids).') group by a.`ID`';
			$p_rows = $this->getRows($sql);
			$aff_rows = $this->table('wf_aff')->field('ID,Name')->where('ID IN ('.join(',',$affids).')')->find();
			$sidtext = "'".join("','",$sid)."'";
			$sid_sql = "select id,Sales,Commission,Sid from rpt_transaction_unique where Sid IN ($sidtext) GROUP by Sid";
			$sid_row = $this->getRows($sid_sql);
			$sql = "SELECT a.*,b.`Country` AS CountryID,b.SiteOption,d.`CountryName` AS CountryName,d.`CountryCode` FROM publisher_account AS a LEFT JOIN publisher AS b ON a.`PublisherId` = b.`ID`  LEFT JOIN country_codes AS d ON b.`Country` = d.`id` WHERE a.`ApiKey` IN ('".join("','",$apikeys)."')";
			$site_rows = $this->getRows($sql);
			$p_arr = array();
			foreach($p_rows as $k=>$v){
				$p_arr[$v['ID']] = $v['Name'];
			}
			$aff_arr = array();
			foreach($aff_rows as $k=>$v){
				$aff_arr[$v['ID']] = $v['Name'];
			}
			$site_arr = array();
			foreach($site_rows as $k=>$v){
				$site_arr[$v['ApiKey']] = $v;
			}
			$sid_arr = array();
			foreach($sid_row as $k=>$v){
				$sid_arr[$v['Sid']] = $v;
			}
			foreach($row as $k=>$v){
				if($v['affId']){
					$row[$k]['affId'] = $aff_arr[$v['affId']];
				}
				if($v['domainId'] && isset($p_arr[$v['domainId']])){
					$row[$k]['domainId'] = $p_arr[$v['domainId']];
				}else{
					$row[$k]['domainId'] = 0;
				}

				if(isset($site_arr[$v['site']])){
					$row[$k]['site'] = $site_arr[$v['site']]['Alias'];
					$row[$k]['SiteOption'] = $site_arr[$v['site']]['SiteOption'];
					$row[$k]['site_country'] = strtoupper($site_arr[$v['site']]['CountryCode']);
				}
				if(isset($sid_arr[$v['sessionId']])){
					$row[$k]['sales'] = '$'.number_format($sid_arr[$v['sessionId']]['Sales'],2);
					$row[$k]['com'] = '$'.number_format($sid_arr[$v['sessionId']]['Commission'],2);
					$row[$k]['hasorder'] = 'Yes';
				}else{
					$row[$k]['hasorder'] = 'No';
					$row[$k]['sales'] = 'Null';
					$row[$k]['com'] = 'Null';
				}
				$row[$k]['pageUrl'] = isset($url_arr[$v['id']]['pageUrl'])?$url_arr[$v['id']]['pageUrl']:'';
				$row[$k]['outUrl'] = isset($url_arr[$v['id']]['outUrl'])?$url_arr[$v['id']]['outUrl']:'';
				$row[$k]['country'] = isset($url_arr[$v['id']]['country'])?strtoupper($url_arr[$v['id']]['country']):'';
				$row[$k]['created'] = isset($url_arr[$v['id']]['created'])?$url_arr[$v['id']]['created']:'';
			}
		}
		if($data['cid'] == 0 && $data['download'] == 0){
			$sql = "SELECT a.country,SUM(clicks) AS `total`,SUM(clicks_robot) AS rob, SUM(clicks_robot_p) AS robp FROM `statis_br` as a $where_str AND a.country !='' GROUP BY a.country ORDER BY total DESC limit 15";
			$cres = $this->getRows($sql);
			if(!empty($cres)){
				$sql = "select CountryCode,CountryName from country_codes";
				$country = $this->objMysql->getRows($sql,'CountryCode');
				$country['UK']['CountryName'] ='United Kingdom';
				foreach($cres as &$k){
					if(isset($country[strtoupper($k['country'])])){
						$k['name'] = $country[strtoupper($k['country'])]['CountryName'];
					}else{
						$k['name'] = $k['country'];
					}
				}
				$return_d['cinfo'] = $cres;
			}else{
				$return_d['cinfo'] = '';
			}
		}
		$return_d['data'] = $row;
		return $return_d;
	}

	function outcsv($data,$page,$page_size){
		$return_d = array();
		$where_str = '';
		$where_arr = array();
		$from = $data['from'];
		$to = $data['to'];
		if(isset($data['pid']) && $data['pid']){
			if(preg_match('/.* \((\d)\)/',$data['pid'],$m)){
				$pid = $m[1];
				$pub_row = $this->table('publisher_account')->where('ID = '.intval($pid))->findone();
				if($pub_row){
					// $where_arr[] = 'site IN ("'.addslashes($pub_row['ApiKey']).'","'.addslashes($pub_row['Alias']).'")';
					$where_arr[] = 'site = "'.addslashes($pub_row['ApiKey']).'"';
					$order_by = "createddate";
				}else{
					$return_d['total_num'] = 0;
					$return_d['hasorder'] = 0;
					$return_d['data'] = '';
					return $return_d;
				}

			}else{
				$pub_rows = $this->table('publisher_account')->where('Alias LIKE "'.addslashes($data['pid']).'%"')->find();
				if($pub_rows){
					$sites = array();
					foreach($pub_rows as $k=>$v){
						// $sites[] = addslashes($v['Alias']);
						$sites[] = addslashes($v['ApiKey']);
					}
					$where_arr[] = 'site IN ("'.join('","',$sites).'")';
					$order_by = "createddate";
				}else{
					$return_d['total_num'] = 0;
					$return_d['hasorder'] = 0;
					$return_d['data'] = '';
					return $return_d;
				}
			}
		}
		if(isset($data['advertiser']) && $data['advertiser']){
			$sql = "SELECT r.domainid FROM r_store_domain r WHERE r.storeid=(SELECT id FROM store s WHERE s.name ='{$data['advertiser']}')";
			$con = new Program();
			$result = $con->getRows($sql);
			if($result){
				$tmp = array();
				foreach ($result as $data) {
					$tmp[] = $data['domainid'];
				}
				$where_arr[] = 'DomainId IN (' . join(',', $tmp) . ')';
			}else{
				$return_d['total_num'] = 0;
				$return_d['hasorder'] = 0;
				$return_d['data'] = '';
				return $return_d;
			}
		}
		if(isset($data['affiliate']) && $data['affiliate']){
			$where_arr[] = 'affid = "'.intval($data['affiliate']).'"';
		}
		if(!empty($where_arr)){
			$where_str = join(' AND ',$where_arr);
		}
		if(!empty($where_str)){
			$a = ' AND';
		}else{
			$a = '';
		}
		$sql = "select affId,programId,site,sessionId from bd_out_tracking_min 
				WHERE createddate >='$from' AND createddate <='$to' $a $where_str
				AND site not in ('c74d97b01eae257e44aa9d5bade97baf','1679091c5a880faf6fb5e6087eb1b2dc','c9f0f895fb98ab9159f51fd0297e236d','8f14e45fceea167a5a36dedd4bea2543','aab3238922bcc25a6f606eb525ffdc56','c81e728d9d4c2f636f067f89cc14862c','eddb904a6db773755d2857aacadb1cb0','eccbc87e4b5ce2fe28308fd9f2a7baf3','d3d9446802a44259755d38e6d163e820','6512bd43d9caa6e02c990b0a82652dca','9bf31c7ff062936a96d3c8bd1f8f2ff3','6d70cb65d15211726dcce4c0e971e21c','45c48cce2e2d7fbdea1afc51c7c6ad26','c20ad4d76fe97759aa27a0c99bff6710','302d3b29ba13d009617ae900bbdfa121') 
				limit $page,$page_size";
		$row = $this->getRows($sql);
		if(!empty($row)){
			$pids = array();
			$affids = array();
			$apikeys = array();
			$sid = array();
			foreach($row as $k=>$v){
				$pids[] = $v['programId'];
				$affids[] = $v['affId'];
				$apikeys[] = $v['site'];
				$sid[] = $v['sessionId'];
			}
			$url_arr = array();
			if(count($sid)){
				$url_rows = $this->table('bd_out_tracking')->field('sessionId,pageUrl,outUrl,created')->where('sessionId IN ("'.join('","',$sid).'")')->find();

				foreach($url_rows as $k=>$v){
					$url_arr[$v['sessionId']] = $v;
				}
			}
			$p_rows = $this->table('program')->field('ID,Name')->where('ID IN ('.join(',',$pids).')')->find();
			$aff_rows = $this->table('wf_aff')->field('ID,Name')->where('ID IN ('.join(',',$affids).')')->find();
			$site_rows = $this->table('publisher_account')->field('ID,ApiKey,Alias')->where('ApiKey IN ("'.join('","',$apikeys).'")')->find();
			$sid_row = $this->table('rpt_transaction_base')->field('id,Sales,Commission')->where('Sid IN ("'.join('","',$sid).'")')->find();
			$p_arr = array();

			foreach($p_rows as $k=>$v){
				$p_arr[$v['ID']] = $v['Name'];
			}
			$aff_arr = array();
			foreach($aff_rows as $k=>$v){
				$aff_arr[$v['ID']] = $v['Name'];
			}
			$site_arr = array();
			foreach($site_rows as $k=>$v){
				$site_arr[$v['ApiKey']] = $v;
			}

			$sid_arr = array();
			foreach($sid_row as $k=>$v){
				$sid_arr[$v['Sid']] = $v;
			}
//				die;
			foreach($row as $k=>$v){
				if($v['affId']){
					$row[$k]['affId'] = $aff_arr[$v['affId']];
				}
				if($v['programId']){
					$row[$k]['programId'] = $p_arr[$v['programId']];
				}

				if(isset($site_arr[$v['site']])){
					$row[$k]['site'] = $site_arr[$v['site']]['Alias'];
				}
				if(isset($sid_arr[$v['sessionId']])){
					$row[$k]['sales'] = round($sid_arr[$v['sessionId']]['Sales'],2);
					$row[$k]['com'] = round($sid_arr[$v['sessionId']]['Commission'],2);
					$row[$k]['hasorder'] = 'Yes';
				}else{
					$row[$k]['hasorder'] = 'No';
					$row[$k]['sales'] = 'Null';
					$row[$k]['com'] = 'Null';
				}
				//if(isset($url_arr[$v['sessionId']])){
				$row[$k]['pageUrl'] = $url_arr[$v['sessionId']]['pageUrl'];
				$row[$k]['outUrl'] = $url_arr[$v['sessionId']]['outUrl'];
				$row[$k]['created'] = $url_arr[$v['sessionId']]['created'];

			}
		}
		$return_d['data'] = $row;
		return $return_d;
	}

	function outcount($data){
		$return_d = array();
		$where_str = '';
		$where_arr = array();
		$from = $data['from'];
		$to = $data['to'];
		if(isset($data['pid']) && $data['pid']){
			if(preg_match('/.* \((\d)\)/',$data['pid'],$m)){
				$pid = $m[1];
				$pub_row = $this->table('publisher_account')->where('ID = '.intval($pid))->findone();
				if($pub_row){
					// $where_arr[] = 'site IN ("'.addslashes($pub_row['ApiKey']).'","'.addslashes($pub_row['Alias']).'")';
					$where_arr[] = 'site = "'.addslashes($pub_row['ApiKey']).'"';
					$order_by = "createddate";
				}else{
					$return_d['total_num'] = 0;
					$return_d['hasorder'] = 0;
					$return_d['data'] = '';
					return $return_d;
				}

			}else{
				$pub_rows = $this->table('publisher_account')->where('Alias LIKE "'.addslashes($data['pid']).'%"')->find();
				if($pub_rows){
					$sites = array();
					foreach($pub_rows as $k=>$v){
						// $sites[] = addslashes($v['Alias']);
						$sites[] = addslashes($v['ApiKey']);
					}
					$where_arr[] = 'site IN ("'.join('","',$sites).'")';
					$order_by = "createddate";
				}else{
					$return_d['total_num'] = 0;
					$return_d['hasorder'] = 0;
					$return_d['data'] = '';
					return $return_d;
				}
			}
		}
		if(isset($data['advertiser']) && $data['advertiser']){
			$sql = "SELECT r.domainid FROM r_store_domain r WHERE r.storeid=(SELECT id FROM store s WHERE s.name ='{$data['advertiser']}')";
			$con = new Program();
			$result = $con->getRows($sql);
			if($result){
				$tmp = array();
				foreach ($result as $data) {
					$tmp[] = $data['domainid'];
				}
				$where_arr[] = 'DomainId IN (' . join(',', $tmp) . ')';
			}else{
				$return_d['total_num'] = 0;
				$return_d['hasorder'] = 0;
				$return_d['data'] = '';
				return $return_d;
			}
		}
		if(isset($data['affiliate']) && $data['affiliate']){
			$where_arr[] = "affid in(15,10)";
		}
		if(!empty($where_arr)){
			$where_str = join(' AND ',$where_arr);
		}
		if(!empty($where_str)){
			$a = ' AND';
		}else{
			$a = '';
		}

		//	$f = $from." 23:59:59";
		//	$t = $to." 23:59:59";
		$c_row = $this->table('bd_out_tracking_min')->where($where_str.$a." createddate>='".$from."' AND createddate<='".$to."'")->count()->findone();
		$return_d['total_num'] = ceil($c_row['tp_count']/1000);//ceil()
		return $return_d;
	}
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
		$where_str = '';
		// if(isset($data['pid']) && !empty($data['pid'])){
		// 	$s = strpos($data['pid'] ,'(');
		// 	$data['pid'] = trim($data['pid']);
		// 	if($s !== false){
		// 		$pid = trim(substr($data['pid'],$s+1,-1));
		// 	}else{
		// 		$row = $this->table('publisher')->where('UserName = "'.addslashes($data['pid']).'"')->findone();
		// 		if($row){
		// 			$pid = $row['ID'];
		// 		}else{
		// 			$pid = 0;
		// 		}
		// 	}
			
		// 	$where_str = 'PublisherId = '.intval($pid);
		// }
		// $sites_rows = $this->table('publisher_account')->where($where_str)->find();


		// if(empty($sites_rows)){
		// 	return array();
		// }
		// $sites = array();
		// $site_tmp = array();
		// foreach($sites_rows as $v){
		// 	$sites[] = addslashes($v['ApiKey']);
		// 	$site_tmp[$v['ApiKey']] = $v;
		// }

		$return_d = array();
		$where_str = '';
		$where_arr = array();

		$page = isset($data['p'])&&$data['p']?intval($data['p']):1;

		// $where_arr[] = 'site IN ("'.join('","',$sites).'")';
		
		if($data['tran_from']){
			$where_arr[] = 'createddate >= "'.$data['tran_from'].'"';
		}
		if($data['tran_to']){
			$where_arr[] = 'createddate <= "'.$data['tran_to'].'"';
		}

		$where_arr[] = 'domainUsed != ""';

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
			$row = $this->table('publisher_account')->where('PublisherId = '.intval($data['uid']))->find();
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
			$row = $this->table('publisher_account')->where('PublisherId = '.intval($data['uid']))->find();
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
}
