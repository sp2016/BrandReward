<?php
class Term
{
	var $objMysql;
	var $page_size = 20;
	function __construct(){
		$this->objMysql = $GLOBALS['db'];
	}

	function get_term_data_ds($sd,$ed,$page=1,$order_by,$filter=array()){
		$pz = $this->page_size;
		$filter_str = $this->get_filter_str_ds($filter);
		
		$sql = 'SELECT *,convert(OutClk/IncomingClk*100,DECIMAL(20,3)) as CTR,convert(Revenue/IncomingClk,DECIMAL(20,3)) as RPI,convert(Revenue/OutClk,DECIMAL(20,3)) as RPC FROM BI_char_term_daily_ds WHERE `date` >= "'.$sd.'" AND `date` <= "'.$ed.'" '.$filter_str;

		
		$sql .= ' ORDER BY `date` desc';
		if($order_by)
			$sql .= ' ,'.$order_by;
		$sql .= ' LIMIT '.($page-1)*$pz.','.$pz;

		$row = $this->objMysql->getRows($sql);

		return $row;
	}

	function get_term_data_page_info_ds($sd,$ed,$page,$order_by,$filter=array()){
		$pz = $this->page_size;
		$filter_str = $this->get_filter_str_ds($filter);

		$sql = 'SELECT COUNT(*) as c FROM BI_char_term_daily_ds WHERE `date` >= "'.$sd.'" AND `date` <= "'.$ed.'"'.$filter_str;
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
				if($k == 'search_page_url' && !empty($v))
					$arr[] = 'page_url LIKE "'.addslashes($v).'%"';
			}
		}

		if(!empty($arr))
			$str = ' AND '.join(' AND ',$arr);
		
		return $str;
	}

	function get_avg_data_ds($page_url_arr,$day_num){
		$avg_data = array();
		$day_num = $day_num-1;
		foreach($page_url_arr as $k=>$v){
			$ed = $k;
			$sd = date('Y-m-d',strtotime("-".$day_num." day",strtotime($k)));
			foreach($v as $a=>$b){
				$v[$a] = addslashes($b);
			}

			$sql = 'SELECT *,CONVERT(OutClk/IncomingClk*100,DECIMAL(20,3)) AS CTR,CONVERT(Revenue/IncomingClk,DECIMAL(20,3)) AS RPI,CONVERT(Revenue/OutClk,DECIMAL(20,3)) AS RPC FROM (
						SELECT page_url,
						CONVERT(AVG(PageView),DECIMAL(20,3)) AS PageView,
						CONVERT(AVG(IncomingClk),DECIMAL(20,3)) AS IncomingClk,
						CONVERT(AVG(OutClk),DECIMAL(20,3)) AS OutClk,
						CONVERT(AVG(OutClk_NoAff),DECIMAL(20,3)) AS OutClk_NoAff,
						CONVERT(AVG(Revenue),DECIMAL(20,3)) AS Revenue,
						CONVERT(AVG(OrderCnt),DECIMAL(20,3)) AS OrderCnt
						 FROM BI_char_term_daily_ds WHERE  `date` >= "'.$sd.'" AND `date` <= "'.$ed.'" AND page_url IN ("'.join('","',$v).'") GROUP BY page_url
					) AS cc';
			$row = $this->objMysql->getRows($sql);

			foreach($row as $a=>$b){
				$avg_data[$k][md5($b['page_url'])] = $b;
			}
		}
		return $avg_data;

	}


	function get_term_data_hd($sd,$ed,$page=1,$order_by,$filter=array()){
		$pz = $this->page_size;
		$filter_str = $this->get_filter_str_hd($filter);
		
		$sql = 'SELECT *,CONVERT(OutClk/IncomingClk*100,DECIMAL(20,3)) as CTR,CONVERT(Revenue/IncomingClk,DECIMAL(20,3)) as RPI,CONVERT(Revenue/OutClk,DECIMAL(20,3)) as RPC FROM BI_char_term_daily_hd WHERE `date` >= "'.$sd.'" AND `date` <= "'.$ed.'" '.$filter_str;
		
		$sql .= ' ORDER BY `date` desc';
		if($order_by)
			$sql .= ' ,'.$order_by;
		$sql .= ' LIMIT '.($page-1)*$pz.','.$pz;

		$row = $this->objMysql->getRows($sql);

		return $row;
	}

	function get_term_data_page_info_hd($sd,$ed,$page,$order_by,$filter=array()){
		$pz = $this->page_size;
		$filter_str = $this->get_filter_str_hd($filter);

		$sql = 'SELECT COUNT(*) as c FROM BI_char_term_daily_hd WHERE `date` >= "'.$sd.'" AND `date` <= "'.$ed.'"'.$filter_str;
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