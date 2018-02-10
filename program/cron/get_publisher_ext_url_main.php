<?php
	header("Content-type: text/html; charset=utf-8");
	include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
	include_once(dirname(dirname(__FILE__)) . "/func/func.php");
	define("MAX_PROCESS_CNT", 10);

	ini_set('xdebug.max_nesting_level', 2000);
	$id = 0;
	if(isset($_SERVER["argc"]) && $_SERVER["argc"] > 1)
	{
		foreach($_SERVER["argv"] as $v){
			$tmp = explode("=", $v);
			if($tmp[0] == "-url"){
				$url = trim($tmp[1]);
				echo "Url:".$url.PHP_EOL;
			}
			if($tmp[0] == "-id"){
				$id = intval($tmp[1]);
				echo "Current ID:".$id.PHP_EOL;
			}
		}
	}else{
		echo "System Error,Please input necessary information!\n";
		return 0;
	}

	$objProgram = New Program();
	$mail = New AlertEmail();
	$sql = "UPDATE publisher_domain_info SET `Status`='processing' where id = '$id'";
	$objProgram->objMysql->query($sql);

	$startTime = time();
	$GLOBALS['startTime'] = $startTime;
	$startTimeTime = date('Y-m-d H:i:s',$startTime);

	$GLOBALS['urlDefault'] = $url;
	$GLOBALS['domain'] = get_domain($url);
	
	$file_name_process = dirname(__FILE__).'/log/'.$id.date('Y_m_d-H_i_s',$startTime).'-process.txt';
	$file_name_log = dirname(__FILE__).'/log/'.$id.date('Y_m_d-H_i_s',$startTime).'-log.txt';
	$file_name_result = dirname(__FILE__).'/log/'.$id.date('Y_m_d-H_i_s',$startTime).'-result.txt';
	$GLOBALS['image'] = array( "jpg" , "jpeg" , "png" , "gif" );
	$GLOBALS['pattern'] = '/(^h|H)(t|T){2}(p|P)(s|S)?(\:)(\/){2}/i';
	$GLOBALS['pattern_illegal'] = '{^\/(.)+(\/)$}';
	$GLOBALS['redis_length_wait_times'] = 0;
	$sql = "UPDATE publisher_domain_info SET `StartTime` = '$startTimeTime' WHERE ID = '$id';";
	$objProgram->objMysql->query($sql);
	$sqlCount = 0;
	$aff_passed = array(
		'main'=>'',
		'sub'=>''
	);
	$subaff_domain = array();
	$aff_domian = array();
	$inner = true;
	echo "Program is OK,mission is going to start!\n";
	file_put_contents($file_name_log,"Start time:".date('Y-m-d H:i:s',$startTime)."\n",FILE_APPEND);

	echo "Start time:",$startTimeTime,"\n";
	$sub_aff = '('.implode(',',$objProgram->sub_aff).')';
	$sql = "SELECT Name,AffiliateUrlKeywords FROM wf_aff WHERE ID IN $sub_aff";
	foreach($objProgram->objMysql->getRows($sql) as $v){
		$tmp_arr1 = explode("\r\n", $v['AffiliateUrlKeywords']);
		foreach($tmp_arr1 as $vv){
			$subaff_domain[$v['Name']][] = $vv;
		}
	}
	$sql = "SELECT Name,AffiliateUrlKeywords FROM wf_aff WHERE ID NOT IN $sub_aff";
	foreach($objProgram->objMysql->getRows($sql) as $v){
		$tmp_arr2 = explode("\r\n", $v['AffiliateUrlKeywords']);
		foreach($tmp_arr2 as $vv){
			$aff_domain[$v['Name']][] = $vv;
		}
	}
	$aff_passed = checkoutAff($url,$inner,$subaff_domain,$aff_domian,$aff_passed);

	$GLOBALS['redis'] = new Redis();
	$GLOBALS['redis']->connect(REDIS_HOST,REDIS_PORT);
	$vPing = $GLOBALS['redis']->ping();
	if($vPing === "+PONG")
		echo "Connect to Redis server sucessfully,Server is running!\n";
	else {
		$GLOBALS['redis']->connect(REDIS_HOST,REDIS_PORT);
		$vPing = $GLOBALS['redis']->ping();
		if($vPing === "+PONG")
			echo "Connect to Redis server sucessfully,Server is running!\n";
		else {
			echo "Fail";
			$subject = "Error!Redis Error!";
			$body = "redis error!<br />can not connect to redis!";
			$mail->SendAlert($subject,$body,"mcskyding@meikaitech.com",false);
			exit();
		}
	}
	$redisIndex = 15;
	echo "select DB ",$redisIndex,"\n";
	$GLOBALS['redis']->select($redisIndex);
	$GLOBALS['list'] = 'list_'.$id;
	$GLOBALS['visited'] = 'visited_'.$id;
	$GLOBALS['pending'] = 'pending_'.$id;
	$redisLength = intval($GLOBALS['redis']->hLen($GLOBALS['list'])) + intval($GLOBALS['redis']->hLen($GLOBALS['visited']) + intval($GLOBALS['redis']->hLen($GLOBALS['pending'])));
	if($redisLength){
		$GLOBALS['redis']->del($GLOBALS['list'],$GLOBALS['visited'],$GLOBALS['pending']);
	}

	file_put_contents($file_name_log, date('Y-m-d H:i:s') . "\t" . $url . "\n", FILE_APPEND);
	echo date('Y-m-d H:i:s') . "\t" . $url . "\n";
	$GLOBALS['redis']->hSetNx($GLOBALS['list'], $url, 'succ');
	//增加http前缀
	if (!preg_match($GLOBALS['pattern'], $url)){
		$url = "http://" . $url;
	}
    //get true url
    $urlInfo = getTrueUrl($url);
    if ($urlInfo['http_code'] != '200')
    {
        $GLOBALS['redis']->hSetNx($GLOBALS['list'], $url, 'false');
        unset($urlHead);
        echo "http code error first.\n";
        unset($urlInfo);
        $urlInfo = getTrueUrl($url);
		sleep(60);
        if ($urlInfo['http_code'] != '200'){
            unset($urlHead);
            echo "http code error second.\n";
            unset($urlInfo);
            $urlInfo = getTrueUrl($url);
			sleep(60);
			if ($urlInfo['http_code'] != '200'){
                unset($urlHead);
                echo "http code error third.\n";
				$http_code = $urlInfo['http_code'] != '200';
				unset($urlInfo);
                $sql = "UPDATE publisher_domain_info SET `Status`='error' where id = $id";
                $objProgram->objMysql->query($sql);
				$subject = "Error!get publish error!";
				$body = "get publish error!<br />Error type is http code error,http code = $http_code.<br /> id is $id and url is ". $GLOBALS['urlDefault'] .".";
				$mail->SendAlert($subject,$body,"mcskyding@meikaitech.com",false);
                file_put_contents($file_name_log,"End time:".date('Y-m-d H:i:s',$time_end)."\n",FILE_APPEND);
                file_put_contents($file_name_log,"Mission failed\n",FILE_APPEND);
                $time_spend = $time_end - $startTime;
                file_put_contents($file_name_log,"Spend time:".gmstrftime("%H:%M:%S",$time_spend)."\n",FILE_APPEND);
                echo "CLEAR DATA\n";
                $redisLength = intval($GLOBALS['redis']->hLen($GLOBALS['list'])) + intval($GLOBALS['redis']->hLen($GLOBALS['visited']) + intval($GLOBALS['redis']->hLen($GLOBALS['pending'])));
                if($redisLength){
                    $GLOBALS['redis']->del($GLOBALS['list'],$GLOBALS['visited'],$GLOBALS['pending']);
                }
                echo "length:".$redisLength."\n";
                $GLOBALS['redis']->expire($GLOBALS['list'],100000);
                $GLOBALS['redis']->expire($GLOBALS['visited'],100000);
                echo "CLEAR DB SUCCESS!\n";
                echo "Close Redis.\n";
                $GLOBALS['redis']->close();
                die();
            }
        }
    }
	$url = $urlInfo['final_url'];

	if (!$urlInfo['response'])
	{
		$GLOBALS['redis']->hSetNx($GLOBALS['list'], $url, 'false');
		echo "html error.\n";
		$sql = "UPDATE publisher_domain_info SET `Status`='error' where id = $id";
		$objProgram->objMysql->query($sql);
		$subject = "Error!get publish error!";
		$body = "get publish error!<br />Error type is no response.<br /> id is $id and url is ". $GLOBALS['urlDefault'] .".";
		$mail->SendAlert($subject,$body,"mcskyding@meikaitech.com",false);
		file_put_contents($file_name_log,"End time:".date('Y-m-d H:i:s',$time_end)."\n",FILE_APPEND);
		file_put_contents($file_name_log,"Mission failed\n",FILE_APPEND);
		$time_spend = $time_end - $startTime;
		file_put_contents($file_name_log,"Spend time:".gmstrftime("%H:%M:%S",$time_spend)."\n",FILE_APPEND);
		echo "CLEAR DATA\n";
		$redisLength = intval($GLOBALS['redis']->hLen($GLOBALS['list'])) + intval($GLOBALS['redis']->hLen($GLOBALS['visited']) + intval($GLOBALS['redis']->hLen($GLOBALS['pending'])));
		if($redisLength){
			$GLOBALS['redis']->del($GLOBALS['list'],$GLOBALS['visited'],$GLOBALS['pending']);
		}
		echo "length:".$redisLength."\n";
		$GLOBALS['redis']->expire($GLOBALS['list'],100000);
		$GLOBALS['redis']->expire($GLOBALS['visited'],100000);
		echo "CLEAR DB SUCCESS!\n";
		echo "Close Redis.\n";
		$GLOBALS['redis']->close();
		die();
	}

	$dom = new DOMDocument();
	@$dom->loadHTML($urlInfo['response']);
	$xpath = new DOMXPath($dom);
	$hrefs = $xpath->evaluate('/html/body//a');
	unset($dom);
	unset($xpath);
	$checkCount = intval($hrefs->length);
	if($checkCount == 0)
	{
		echo "Error!the count of url in this domain is 0.\n";
		$subject = "the count of url in this domain is 0!";
		$body = "the count of url in this domain is 0!<br /> id is $id and url is ". $GLOBALS['urlDefault'] .".";
		$mail->SendAlert($subject,$body,"mcskyding@meikaitech.com",false);
		$sql = "UPDATE publisher_domain_info SET `Status`='error' where id = $id";
		$objProgram->objMysql->query($sql);
		file_put_contents($file_name_log,"End time:".date('Y-m-d H:i:s',$time_end)."\n",FILE_APPEND);
		file_put_contents($file_name_log,"Mission failed\n",FILE_APPEND);
		$time_spend = $time_end - $startTime;
		file_put_contents($file_name_log,"Spend time:".gmstrftime("%H:%M:%S",$time_spend)."\n",FILE_APPEND);
		echo "CLEAR DATA\n";
		$redisLength = intval($GLOBALS['redis']->hLen($GLOBALS['list'])) + intval($GLOBALS['redis']->hLen($GLOBALS['visited']) + intval($GLOBALS['redis']->hLen($GLOBALS['pending'])));
		if($redisLength){
			$GLOBALS['redis']->del($GLOBALS['list'],$GLOBALS['visited'],$GLOBALS['pending']);
		}
		echo "length:".$redisLength."\n";
		$GLOBALS['redis']->expire($GLOBALS['list'],100000);
		$GLOBALS['redis']->expire($GLOBALS['visited'],100000);
		echo "CLEAR DB SUCCESS!\n";
		echo "Close Redis.\n";
		$GLOBALS['redis']->close();die();
	}
	$urlArr = array();
	for ($i = 0; $i < $hrefs->length; $i++){
		$href = $hrefs->item($i);
		$url_next = $href->getAttribute('href');
		$url_next = explode('?', $url_next, 2);
		$url_next = explode('#', $url_next[0], 2);
		$url = $url_next[0];
		unset($href);
		unset($url_next);

		// 相对路径增加前缀
		$url = addslashes($url);
		if (preg_match($GLOBALS['pattern_illegal'], $url)){
			$url = $GLOBALS['urlDefault'] . $url;
		}
		$url = stripslashes($url);

		//去除img
		$ext = explode(".",$url);
		$ext = strtolower(end($ext));

		if( !empty($ext) && in_array($ext , $GLOBALS['image'])){
			file_put_contents($file_name_process, "Image:" . $url . "\n", FILE_APPEND);
			unset($ext);
			continue;
		}
		unset($ext);

		//检测非法Url
		if (!preg_match($GLOBALS['pattern'], $url)){
			file_put_contents($file_name_process, "Illegal:" . $url . "\n", FILE_APPEND);
			continue;
		}

		//check if url inner site
		if (!stristr($url, $GLOBALS['domain'])){
			if (!$GLOBALS['redis']->hExists($GLOBALS['list'], $url)){
				$inner = false;
				$aff_passed = checkoutAff($url,$inner,$subaff_domain,$aff_domian,$aff_passed);
				$GLOBALS['redis']->hSetNx($GLOBALS['list'], $url, 'add');
				file_put_contents($file_name_result, $url . "\n", FILE_APPEND);
			}
			continue;
		}
		if (!$GLOBALS['redis']->hExists($GLOBALS['list'], $url) && stristr($url, $GLOBALS['domain'])){
			$inner = true;
			if($i < 10 )
			{
				$aff_passed = checkoutAff($url,$inner,$subaff_domain,$aff_domian,$aff_passed);
			}
			$GLOBALS['redis']->hSetNx($GLOBALS['pending'], $url, 'pending');
		}
	}
	unset($urlArr);
	unset($hrefs);

	$pendLen = intval($GLOBALS['redis']->hLen($GLOBALS['pending']));
	if($pendLen == 0)
	{
		echo "Error!the count of pending url in this domain is 0.\n";
		$subject = "the count of pending url in this domain is 0!";
		$body = "the count of pending url in this domain is 0!<br /> id is $id and url is  ". $GLOBALS['urlDefault'] .".";
		$mail->SendAlert($subject,$body,"mcskyding@meikaitech.com",false);
		$sql = "UPDATE publisher_domain_info SET `Status`='error' where id = $id";
		$objProgram->objMysql->query($sql);
		file_put_contents($file_name_log,"End time:".date('Y-m-d H:i:s',$time_end)."\n",FILE_APPEND);
		file_put_contents($file_name_log,"Mission failed\n",FILE_APPEND);
		$time_spend = $time_end - $startTime;
		file_put_contents($file_name_log,"Spend time:".gmstrftime("%H:%M:%S",$time_spend)."\n",FILE_APPEND);
		echo "CLEAR DATA\n";
		$redisLength = intval($GLOBALS['redis']->hLen($GLOBALS['list'])) + intval($GLOBALS['redis']->hLen($GLOBALS['visited']) + intval($GLOBALS['redis']->hLen($GLOBALS['pending'])));
		if($redisLength){
			$GLOBALS['redis']->del($GLOBALS['list'],$GLOBALS['visited'],$GLOBALS['pending']);
		}
		echo "length:".$redisLength."\n";
		$GLOBALS['redis']->expire($GLOBALS['list'],100000);
		$GLOBALS['redis']->expire($GLOBALS['visited'],100000);
		echo "CLEAR DB SUCCESS!\n";
		echo "Close Redis.\n";
		$GLOBALS['redis']->close();die();
	}
	else if($pendLen <= 50)
	{
		echo "Warning!the count of pending url in this domain is too little.\n";
		$subject = "the count of pending url in this domain is too little!";
		$body = "the count of pending url in this domain is 0!<br /> id is $id and url is  ". $GLOBALS['urlDefault'] .".";
		$mail->SendAlert($subject,$body,"mcskyding@meikaitech.com",false);
	}
	echo "Current memory:".round(memory_get_usage(true)/1024/1024,2) ."M\tMalloc memory:".round(memory_get_usage()/1024/1024,2) ."M\tPending remaining:".$pendLen."\n";

	dealData($id);

	$listUrl = $GLOBALS['redis']->hGetAll($GLOBALS['list']);
	echo "get ext url:".count($listUrl)."\r\n";
	$sql = "INSERT INTO publisher_domain_detail (`DomainInfoID`,`ExtUrl`,`ExtDomain`) VALUES ";
	foreach($listUrl as $k => $v){
		$extUrl = $k;
		$extDomain = get_domain($extUrl);
		$sql .= "('$id','".addslashes($extUrl)."','".addslashes($extDomain)."'),";
		$sqlCount++;
		if($sqlCount > 200){
			$sqlCount = 0;
			$sql = rtrim($sql, ',');
			$objProgram->objMysql->query($sql);
			$sql = "INSERT INTO publisher_domain_detail (`DomainInfoID`,`ExtUrl`,`ExtDomain`) VALUES ";
		}
	}
	if($sqlCount > 0){
		$sql = rtrim($sql, ',');
		$objProgram->objMysql->query($sql);
	}
	if($aff_passed['main'] =='')
		$aff_passed['main'] = 'None';
	if($aff_passed['sub'] =='')
		$aff_passed['sub'] = 'None';
	$time_end = time();
	$time_finish = date('Y-m-d H:i:s',$time_end);
	$sql = "UPDATE publisher_domain_info SET `EndTime` = '$time_finish',`IsPassSubAff` = '" .addslashes($aff_passed['sub'])."',`IsPassAff` = '" .addslashes($aff_passed['main'])."',`Status`='done'  WHERE ID = '$id';";
	$objProgram->objMysql->query($sql);

	file_put_contents($file_name_log,"End time:".date('Y-m-d H:i:s',$time_end)."\n",FILE_APPEND);
	file_put_contents($file_name_log,"Mission succeed\n",FILE_APPEND);
	$time_spend = $time_end - $startTime;
	file_put_contents($file_name_log,"Spend time:".gmstrftime("%H:%M:%S",$time_spend)."\n",FILE_APPEND);
	file_put_contents($file_name_log,"Valid url counts:".$GLOBALS['redis']->hLen($GLOBALS['list'])."\n",FILE_APPEND);
	echo "CLEAR DATA\n";
	$redisLength = intval($GLOBALS['redis']->hLen($GLOBALS['list'])) + intval($GLOBALS['redis']->hLen($GLOBALS['visited']) + intval($GLOBALS['redis']->hLen($GLOBALS['pending'])));
	if($redisLength){
		$GLOBALS['redis']->del($GLOBALS['list'],$GLOBALS['visited'],$GLOBALS['pending']);
	}
	echo "length:".$redisLength."\n";
	$GLOBALS['redis']->expire($GLOBALS['list'],100000);
	$GLOBALS['redis']->expire($GLOBALS['visited'],100000);
	echo "CLEAR DB SUCCESS!\n";
	echo "Close Redis.\n";
	$GLOBALS['redis']->close();

	function dealData($id){
		if(checkProcess($id) <= MAX_PROCESS_CNT){
			$pendLen = intval($GLOBALS['redis']->hLen($GLOBALS['pending']));
			if($pendLen){
				$urlTmp = $GLOBALS['redis']->hGetAll($GLOBALS['pending']);
				$url_pending = key($urlTmp);
				unset($urlTmp);
				$GLOBALS['redis']->hDel($GLOBALS['pending'], $url_pending);
				$startTime = $GLOBALS['startTime'];
				$urlDefault = $GLOBALS['urlDefault'];
				$domain = $GLOBALS['domain'];
				$cmd = "nohup php /home/bdg/program/cron/get_publisher_ext_url_analysis.php -id=$id -url=$url_pending -startTime=$startTime -urlDefault=$urlDefault -domain=$domain>> /home/bdg/program/cron/log/{$id}.log 2>&1 &";
				system($cmd);
				dealData($id);
			}else{
				if(checkProcess($id) == 0)
					return;
				$GLOBALS['redis_length_wait_times']++;
				if($GLOBALS['redis_length_wait_times'] <= 3)
				{
					echo "Sleep ".intval($GLOBALS['redis_length_wait_times']) * 20 ." seconds ......\n";
					sleep(intval($GLOBALS['redis_length_wait_times']) * 20);
					dealData($id);
				}
			}
		} else {
			echo "Sleep 60 seconds......\n";
			sleep(60);
			dealData($id);
		}
	}

	function checkProcess($id){
		$cmd = "ps aux | grep get_publisher_ext_url_analysis.php | grep id=$id | grep -v grep -c";
		$processCount = trim(exec($cmd));
		$error = MAX_PROCESS_CNT + 1;
		
		echo "Current analysis process count:\n";
		var_dump($processCount);
		echo "\n";
		$cmd = "ps aux | grep get_publisher | grep -v grep -c";
		$warningCount = trim(exec($cmd));
		if(!is_numeric($warningCount) || $warningCount>=70)
			return$error;
		if(is_numeric($processCount))
			return $processCount;
		else
			return $error;
	}

	function checkoutAff($url,$inner,$subaff_domain,$aff_domian,$aff_passed)
	{
		$rival = array(
			'FlexOffers' => array(
				'js' => 'http://track.flexlinks.com/i.ashx?foid=',
			),
			'VigLink' => array(
				'js' => '//cdn.viglink.com/api/vglnk.js',
				'short' => 'redirect.viglink.com',
			),
			'SkimLinks'=> array(
				'js' => 's.skimresources.com',
				'js_2nd' => 'skimlinks.js',
				'short' => 'fave.co'
			),

			'YieldKit' => array(
				'js' => 'js.srvtrck.com/v1/js?api_key=',
				'js_2nd' => 'js.cdn.yieldkit.com/v1/js?api_key=',
			),

			'Digidip' => array(
				'js' => '//static.digidip.net/',
			)
		);
		if($inner)
		{
			$data = getTrueUrl($url);
			foreach($rival as $name=>$v)
			{
				foreach($v as $k=>$code)
				{
					if(stripos($data['response'],$code) !== false)
					{
						if(stripos($aff_passed['sub'],$name) === false)
							$aff_passed['sub'] .= $name;
					}
				}
			}
			foreach($subaff_domain as $name=>$v)
			{
				foreach($v as $k=>$code)
				{
					if(stripos($data['response'],$code) !== false)
					{
						if(stripos($aff_passed['sub'],$name) === false)
							$aff_passed['sub'] .= $name;
					}
				}
			}
			foreach($aff_domian as $name=>$v)
			{
				foreach($v as $k=>$code)
				{
					if(stripos($data['response'],$code) !== false)
					{
						if(stripos($aff_passed['main'],$name) === false)
							$aff_passed['main'] .= $name;
					}
				}
			}
		}
		else
		{
			foreach($rival as $name=>$v)
			{
				foreach($v as $k=>$code)
				{
					if(stripos($url,$code) !== false)
					{
						if(stripos($aff_passed['sub'],$name) === false)
							$aff_passed['sub'] .= $name;
					}
				}
			}
			foreach($subaff_domain as $name=>$v)
			{
				foreach($v as $k=>$code)
				{
					if(stripos($url,$code) !== false)
					{
						if(stripos($aff_passed['sub'],$name) === false)
							$aff_passed['sub'] .= $name;
					}
				}
			}
			foreach($aff_domian as $name=>$v)
			{
				foreach($v as $k=>$code)
				{
					if(stripos($url,$code) !== false)
					{
						if(stripos($aff_passed['main'],$name) === false)
							$aff_passed['main'] .= $name;
					}
				}
			}
		}
		return $aff_passed;
	}
