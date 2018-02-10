<?php
class Coupon
{
	var $objMysql;
	var $page_size = 50;
	function __construct(){
		$this->objMysql = $GLOBALS['db'];
	}

	function get_coupon_data_ds($sd,$ed,$page=1,$order_by,$filter){
		$pz = $this->page_size;
		$filter_str = $this->get_filter_str_ds($filter);

		$sql = 'SELECT * FROM BI_char_coupon_daily_ds WHERE `date` >= "'.$sd.'" AND `date` <= "'.$ed.'" '.$filter_str;
		$sql .= ' ORDER BY `date` desc';
		if($order_by)
			$sql .= ' ,'.$order_by;
		$sql .= ' LIMIT '.($page-1)*$pz.','.$pz;
		$row = $this->objMysql->getRows($sql);

		return $row;
	}

	function get_coupon_data_page_info_ds($sd,$ed,$page,$order_by,$filter){
		$pz = $this->page_size;
		$filter_str = $this->get_filter_str_ds($filter);

		$sql = 'SELECT COUNT(*) as c FROM BI_char_coupon_daily_ds WHERE `date` >= "'.$sd.'" AND `date` <= "'.$ed.'"'.$filter_str;;
		$q = $this->objMysql->query($sql);

		$row = $this->objMysql->getRow($q);
		$total = $row['c'];

		$page_info = array();
		$page_info['total'] = $total;
		$page_info['pz'] = $pz;
		$page_info['page_total'] = ceil($total/$pz);
		$page_info['page_now'] = $page;

		return $page_info;
	}

	function get_filter_str_ds($filter){
		$str = '';
		$arr = array();
		if(!empty($filter)){
			foreach($filter as $k=>$v){
				if($k == 'search_term_id' && !empty($v)){
					$sql = 'SELECT CouponId FROM termcoupon_relationship WHERE TermId = '.intval($v);
					$row = $this->objMysql->getRows($sql);
					if(!empty($row)){
						$coupon_ids = array();
						foreach($row as $k=>$v){
							$coupon_ids[] = $v['CouponId'];
						}

						$arr[] = 'id IN ('.join(',',$coupon_ids).')';
					}
				}

				if($k == 'search_has_code' && !empty($v)){
					$str = '';
					if($v == 'No Code')
						$str = 'CsgCode = ""';
					else
						$str = 'CsgCode != ""';

					$arr[] = 'id IN (SELECT ID FROM coupon WHERE '.$str.')';
				}
			}
		}

		if(!empty($arr))
			$str = ' AND '.join(' AND ',$arr);
		
		return $str;
	}

	function get_coupon_data_hd($sd,$ed,$page=1,$order_by){
		$pz = $this->page_size;
		$filter_str = $this->get_filter_str_hd($filter);

		$sql = 'SELECT * FROM BI_char_coupon_daily_hd WHERE `date` >= "'.$sd.'" AND `date` <= "'.$ed.'" '.$filter_str;

		$sql .= ' ORDER BY `date` desc';
		if($order_by)
			$sql .= ' ,'.$order_by;
		$sql .= ' LIMIT '.($page-1)*$pz.','.$pz;
		$row = $this->objMysql->getRows($sql);

		return $row;
	}

	function get_coupon_data_page_info_hd($sd,$ed,$page){
		$pz = $this->page_size;
		$filter_str = $this->get_filter_str_hd($filter);

		$sql = 'SELECT COUNT(*) as c FROM BI_char_coupon_daily_hd WHERE `date` >= "'.$sd.'" AND `date` <= "'.$ed.'"'.$filter_str;;
		$q = $this->objMysql->query($sql);
		$row = $this->objMysql->getRow($q);
		$total = $row['c'];

		$page_info = array();
		$page_info['total'] = $total;
		$page_info['pz'] = $pz;
		$page_info['page_total'] = ceil($total/$pz);
		$page_info['page_now'] = $page;

		return $page_info;
	}

	function get_filter_str_hd($filter){
		$str = '';
		$arr = array();
		if(!empty($filter)){
			foreach($filter as $k=>$v){
				if($k == 'search_page_url' && !empty($v))
					$arr[] = 'page_url LIKE "'.addslashes($v).'%"';
			}
		}

		if(!empty($arr))
			$str = ' AND '.join(' AND ',$arr);
		
		return $str;
	}
}