<?php
	/*

	*/
	try{
		define('AFF_NAME', AFFILIATE_NAME);
		define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");
		if (defined('START_TIME') && defined('END_TIME')) {
			$end_dt = date('Y-m-d', strtotime(END_TIME));
			$begin_dt = date('Y-m-d', strtotime(START_TIME));
		} else {
			$end_dt = date('Y-m-d');
			$begin_dt = date('Y-m-d', strtotime('-30 days', strtotime($end_dt)));
		}

		$urlarray = array(
				'52546'=>'http://api.publicidees.com/subid.php5?p=52546&k=1f02774f9221d365c7bdee9cdb2c849a&dd='.$begin_dt.'&df='.$end_dt.'',
		);
		$time = timediff(strtotime($begin_dt),strtotime($end_dt));
		$xmlarray = array();
		$fws = array();
		$comm_all = 0;
		$curr = array();


		foreach($urlarray as $k=>$url){
			$file_temp = PATH_TMP . '/' . AFFILIATE_NAME .'_'.$k.'.tmp';
			$fw = fopen($file_temp,'w+');
			$xmldata= file_get_contents($url);
			fwrite($fw,$xmldata);
			fclose($fw);
			$xmldata = file_get_contents($file_temp);
			$data = simplexml_load_string($xmldata);
			if(empty($data[0])){
				break;
			}
			for($i=1;$i<=$time['day'];$i++){
				$t = date("Y-m-d",(strtotime($end_dt) - 3600*24*$i));
				foreach($data->program  as $k ){
					foreach($k->action  as $v ){
						if(preg_match('/'.$t.'/',$v['ActionDate'])){


							$click_dt = trim($v['ActionDate']);
							$currency = trim($v['ProgramCurrency']);
							$date = date('Y-m-d', strtotime($click_dt));
							if(!isset($curr[$currency][$date])){
								$curr[$currency][$date] = cur_exchange($currency, 'USD', $date);
							}



							$oldsale = trim($v['CartAmount']);
							$oldcomm = trim($v['ActionCommission']);
							$idInAff = trim($k['id']);
							$pName = trim($k->name);
							$sid = trim($v['SubID']);
							$orderId = trim($v['id']);
							$tradeType = $v['ActionType'];
							$status = '';
							if($v['ActionStatus'] == 0){
								$status = 'rejected';
							}elseif($v['ActionStatus'] == 1){
								$status = 'pending';
							}elseif($v['ActionStatus'] == 2){
								$status = 'approved';
							}

							$cur_exr = $curr[$currency][$date];
							$sale = round($oldsale * $cur_exr, 4);
							$comm = round($oldcomm * $cur_exr, 4);
							
							$rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . date('Ymd', strtotime($t)) . '.upd';
							if (!isset($fws[$rev_file])) {
								$fws[$rev_file] = fopen($rev_file, 'w');
								$comms[$rev_file] = 0;
							}
                                                        $cancelreason = '';
							$replace_array = array(
									'{createtime}'      => $click_dt,
									'{updatetime}'      => $click_dt,
									'{sales}'           => $sale,
									'{commission}'      => $comm,
									'{idinaff}'         => $idInAff,
									'{programname}'     => $pName,
									'{sid}'             => $sid,
									'{orderid}'         => $orderId,
									'{clicktime}'       => $click_dt,
									'{tradeid}'         => $orderId,
									'{tradestatus}'     => $status,
									'{oldcur}'          => $currency,
									'{oldsales}'        => $oldsale,
									'{oldcommission}'   => $oldcomm,
									'{tradetype}'       => $tradeType,
									'{referrer}'        => '',
                                                                        '{cancelreason}'    => $cancelreason,
							);
							fwrite($fws[$rev_file], strtr(FILE_FORMAT,$replace_array) . "\n");
							$comms[$rev_file] += $comm;
							$comm_all+=$comm;
							
						}
					}
				}
			}
		}
		foreach ($fws as $file => $f) {
	    	fclose($f);
		}
	}
	catch(Exception $e){
		echo $e->getMessage();
		exit(1);
	}


	/*
	   获取时间差函数
	*/
	function timediff( $begin_time, $end_time )
	{
		if ($begin_time < $end_time) {
			$starttime = $begin_time;
			$endtime = $end_time;
		} else {
			$starttime = $end_time;
			$endtime = $begin_time;
		}

		$timediff = $endtime - $starttime;
		$days = intval( $timediff / 86400 );
		$remain = $timediff % 86400;
		$hours = intval( $remain / 3600 );
		$remain = $remain % 3600;
		$mins = intval( $remain / 60 );
		$secs = $remain % 60;
		$res = array( "day" => $days, "hour" => $hours, "min" => $mins, "sec" => $secs );
		return $res;
	}
?>
