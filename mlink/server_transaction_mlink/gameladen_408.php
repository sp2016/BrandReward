<?php 
	set_time_limit(0);
	try{
		define('USR_NAME',AFFILIATE_USER);
		define('USR_PASS',AFFILIATE_PASS);
		define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");
		
		if (defined('START_TIME') && defined('END_TIME')) {
			$ed = date('Y-m-d', strtotime(END_TIME));
			$fd = date('Y-m-d', strtotime(START_TIME));
		} else {
			$ed = date('Y-m-d');
			$fd = date('Y-m-d', strtotime('-120 days'));
		}
		$begin_dt = $fd;
		$end_dt = $ed;
		echo "Date setting: ST:{$begin_dt} ET:{$end_dt} \n";
		$file_cook = PATH_COOKIE . '/gameladen.cook';
		if(file_exists($file_cook))
			unlink($file_cook);
		
		//login
		$url = "http://affiliate.gameladen.com/affiliates/panel.php";
		$ch = curl_init($url);
		curl_setopt($ch,CURLOPT_HEADER,1);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_COOKIEJAR,$file_cook);
		curl_setopt($ch,CURLOPT_COOKIEFILE,$file_cook);
		$rs = curl_exec($ch);
		preg_match('/Set-Cookie: A_pap_sid=(.*);/iU',$rs,$cook);
        curl_close($ch);
		$cook = $cook[1];	//获取随机cookie
		//echo $cook;
		
		//第一次访问server
		$posts = array('D'=>'{"C":"Gpf_Rpc_Server", "M":"run", "requests":[{"C":"Gpf_Rpc_Server", "M":"syncTime", "offset":"54000000"},{"C":"Gpf_Templates_TemplateService", "M":"getTemplate", "templateName":"window_move_panel"},{"C":"Gpf_Templates_TemplateService", "M":"getTemplate", "templateName":"context_menu"},{"C":"Gpf_Templates_TemplateService", "M":"getTemplate", "templateName":"window_header_refresh"},{"C":"Gpf_Templates_TemplateService", "M":"getTemplate", "templateName":"item"},{"C":"Gpf_Templates_TemplateService", "M":"getTemplate", "templateName":"single_content_panel"},{"C":"Gpf_Templates_TemplateService", "M":"getTemplate", "templateName":"form_field_checkbox"}], "S":"'.$cook.'"}');
		
		$uri = 'http://affiliate.gameladen.com/scripts/server.php';
		$ch = curl_init($uri);		
        $curl_opts = array(CURLOPT_HEADER => true,
            CURLOPT_NOBODY => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_COOKIEJAR => $file_cook,
            CURLOPT_COOKIEFILE => $file_cook,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $posts,
        );
        curl_setopt_array($ch, $curl_opts);
        $rs = curl_exec($ch);
        curl_close($ch);
		preg_match('/Set-Cookie: RPC_pap_sid=(.*);/iU',$rs,$rpc_cook);
		if(empty($rpc_cook[1]))
			exit('RPC_pap_sid IS NULL');
		$rpc_cook = $rpc_cook[1];
		
		$posts = array('D'=>'{"C":"Gpf_Rpc_Server", "M":"run", "requests":[{"C":"Gpf_Auth_Service", "M":"authenticate", "fields":[["name","value"],["Id",""],["username","'.USR_NAME.'"],["password","'.USR_PASS.'"],["rememberMe","Y"],["language","en-US"]]}], "S":"'.$rpc_cook.'"}');
		$uri = 'http://affiliate.gameladen.com/scripts/server.php';
		$ch = curl_init($uri);		
        $curl_opts = array(CURLOPT_HEADER => true,
            CURLOPT_NOBODY => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_COOKIEJAR => $file_cook,
            CURLOPT_COOKIEFILE => $file_cook,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $posts,
        );
        curl_setopt_array($ch, $curl_opts);
        $rs = curl_exec($ch);
        curl_close($ch);
		preg_match('/Set-Cookie: A_auth=(.*);/iU',$rs,$author);
		if(empty($author[1]))
			exit('login failed');
		$rs = _getpage();
		 
		foreach($rs as $v){
			foreach($v as $j){
				$d = date('Ymd',strtotime($j[10]));
				$_d = date('Y-m-d',strtotime($j[10]));
				
				$rate = cur_exchange('EUR','USD',$_d);
				$sales_usd = $j[20] == 'D' ? 0 : round($j[5]*$rate,2);
				$rev_usd = $j[20] == 'D' ? 0 : round($j[4]*$rate,2);
				$arr[$d][] = array($j[10],$j[10],$sales_usd,$rev_usd,$j[2],$j[13],$j[36],$j[8],$j[10],$j[3],$j[20],'EUR',$j[5],$j[4],'','');
			}	
		}

		foreach($arr as $d=>$v){
			$uri = PATH_DATA."/gameladen/revenue_{$d}";
			$fp = fopen($uri.'.upd',"w");
			$sales = 0;
			foreach($v as $vv){
			    
			    $replace_array = array(
			        '{createtime}'      => $vv[0],
			        '{updatetime}'      => $vv[1],
			        '{sales}'           => $vv[2],
			        '{commission}'      => $vv[3],
			        '{idinaff}'         => $vv[4],
			        '{programname}'     => $vv[5],
			        '{sid}'             => $vv[6],
			        '{orderid}'         => $vv[7],
			        '{clicktime}'       => $vv[8],
			        '{tradeid}'         => $vv[9],
			        '{tradestatus}'     => $vv[10],
			        '{oldcur}'          => $vv[11],
			        '{oldsales}'        => $vv[12],
			        '{oldcommission}'   => $vv[13],
			        '{tradetype}'       => '',
			        '{referrer}'        => '',
                                '{cancelreason}'    => '',
			    );
			    
				//fwrite($fp,implode("\t",$vv)."\n");
				fwrite($fp, strtr(FILE_FORMAT,$replace_array) . "\n");
				$sales += $vv[3];
			}
			fclose($fp);
			/*
			if(!file_exists($uri.'.dat')){
				rename($uri.'.upd',$uri.'.dat');
			}else{
				$fp = fopen($uri.'.dat',"r");
				$o_s = 0;
				while(!feof($fp)){
					$lr = explode("\t",trim(fgets($fp)));
					if(empty($lr[0]))
						continue;
					$o_s += $lr[3];
				}
				fclose($fp);
				if($o_s == $sales){
					unlink($uri.'.upd');
				}else{
					unlink($uri.'.dat');
					rename($uri.'.upd',$uri.'.dat');
				}
			}
			*/
		}
		
	}catch (Exception $e) {
		echo $e->getMessage();
		exit(1);
	}
	
	function ch($n){
		global $file_cook,$uri,$rpc_cook,$fd,$ed;
		$posts = array('D'=>'{"C":"Gpf_Rpc_Server", "M":"run", "requests":[{"C":"Pap_Affiliates_Reports_TransactionsGrid", "M":"getRows", "sort_col":"dateinserted", "sort_asc":false, "offset":'.$n.', "limit":100, "filters":[["dateinserted","D>=","'.$fd.'"],["dateinserted","D<=","'.$ed.'"],["rstatus","IN","A,D,P"],["rtype","IN","I,C,S,A,B,U,F,R,H,E"]], "columns":[["id"],["id"],["commission"],["totalcost"],["fixedcost"],["t_orderid"],["productid"],["dateinserted"],["name"],["rtype"],["tier"],["commissionTypeName"],["rstatus"],["firstclickdata1"],["firstclickdata2"],["lastclickdata1"],["lastclickdata2"],["data1"],["data2"],["merchantnote"],["channel"]]}], "S":"'.$rpc_cook.'"}');
        $ch = curl_init("http://affiliate.gameladen.com/scripts/server.php");
        $curl_opts = array(CURLOPT_HEADER => false,
            CURLOPT_NOBODY => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_COOKIEJAR => $file_cook,
            
            CURLOPT_COOKIEFILE => $file_cook,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
			CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $posts,
        );
        curl_setopt_array($ch, $curl_opts);
        $rs = curl_exec($ch);
        curl_close($ch);
		return json_decode($rs,true);
	}
	
	function _getpage(){
		$page = 1;
		$n = ($page-1)*100;
		$r = ch($n);
		// unset($r[0]['rows'][0]);
		$rs[] = $r[0]['rows'];
		$num = ceil($r[0]['count']/100);
		while($page < $num){
			$page++;
			$n = ($page-1)*100;
			$r = ch($n);
			unset($r[0]['rows'][0]);
			$rs[] = $r[0]['rows'];
		}
		return $rs;
	}
