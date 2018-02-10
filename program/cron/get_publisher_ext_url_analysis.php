<?php
    header("Content-type: text/html; charset=utf-8");
    include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
    include_once(dirname(dirname(__FILE__)) . "/func/func.php");
    define("MAX_PROCESS_CNT", 10);
    ini_set('xdebug.max_nesting_level', 2000);
	if(isset($_SERVER["argc"]) && $_SERVER["argc"] > 1)
	{
	    foreach($_SERVER["argv"] as $v){
	        $tmp = explode("=", $v);
	        if($tmp[0] == "-url"){
	            $url = $tmp[1];
	            echo "Url:",$url,"\n";
	        }
	        if($tmp[0] == "-id"){
	            $id = intval($tmp[1]);
	            echo "Current ID:",$id,"\n";
	        }
	        if($tmp[0] == "-startTime"){
	            $startTime = $tmp[1];
	            echo "startTime ID:",date('Y-m-d H:i:s',$startTime),"\n";
	        }
	        if($tmp[0] == "-urlDefault"){
	            $GLOBALS['urlDefault'] = $tmp[1];
	            echo "url default:",$GLOBALS['urlDefault'],"\n";
	        }
	        if($tmp[0] == "-domain"){
	            $GLOBALS['domain'] = $tmp[1];
	            echo "domain:",$GLOBALS['domain'],"\n";
	        }
	    }
	}else{
	    echo "System Error,Please input necessary information!\n";
	    return 0;
	}

    $objProgram = New Program();

    $GLOBALS['file_name_process'] = dirname(__FILE__).'/log/'.$id.date('Y_m_d-H_i_s',$startTime).'-process.txt';
    $GLOBALS['file_name_log'] = dirname(__FILE__).'/log/'.$id.date('Y_m_d-H_i_s',$startTime).'-log.txt';
    $GLOBALS['file_name_result'] = dirname(__FILE__).'/log/'.$id.date('Y_m_d-H_i_s',$startTime).'-result.txt';
    $GLOBALS['image'] = array( "jpg" , "jpeg" , "png" , "gif" );
    $GLOBALS['pattern'] = '/(^h|H)(t|T){2}(p|P)(s|S)?(\:)(\/){2}/i';
    $GLOBALS['pattern_illegal'] = '{^\/(.)+(\/)$}';

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
            exit();
        }
    }

    $redisIndex = 15;
    echo "select DB ",$redisIndex,"\n";
    $GLOBALS['redis']->select($redisIndex);
    $GLOBALS['list'] = 'list_'.$id;
    $GLOBALS['visited'] = 'visited_'.$id;
    $GLOBALS['pending'] = 'pending_'.$id;

    $cmd = "ps aux | grep get_publisher_ext_url_analysis.php | grep id=$id | grep -v grep -c";
    $processCount = trim(exec($cmd));
	echo "Current analysis process count:\n";
	var_dump($processCount);
	echo "\n";
	$cmd = "ps aux | grep get_publisher | grep -v grep -c";
	$warningCount = trim(exec($cmd));
	if(!is_numeric($warningCount) || $warningCount>=70)
	{
		$GLOBALS['redis']->hSetNx($GLOBALS['pending'], $url, 'pending');
		die;
	}
    if(is_numeric($processCount))
    {
        if($processCount > MAX_PROCESS_CNT)
        {
            $GLOBALS['redis']->hSetNx($GLOBALS['pending'], $url, 'pending');
            die;
        }
    }
    else
    {
        $GLOBALS['redis']->hSetNx($GLOBALS['pending'], $url, 'pending');
        die;
    }
	echo "Current memory:".round(memory_get_usage(true)/1024/1024,2) ."M\tMalloc memory:".round(memory_get_usage()/1024/1024,2) ."M\n";

	file_put_contents($GLOBALS['file_name_log'], date('Y-m-d H:i:s') . "\t" . $url . "\n", FILE_APPEND);
	echo date('Y-m-d H:i:s') . "\t" . $url . "\n";
	$GLOBALS['redis']->hSetNx($GLOBALS['visited'], $url, 'succ');
	//增加http前缀
	if (!preg_match($GLOBALS['pattern'], $url)){
	    $url = "http://" . $url;
	}
	//获取真实Url
	$urlInfo = getTrueUrl($url);
	$url = $urlInfo['final_url'];
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
	                $GLOBALS['redis']->hSetNx($GLOBALS['list'], $url, 'false');
	                $GLOBALS['redis']->hDel($GLOBALS['pending'],$url);
	                file_put_contents($file_name_process, "Http Error:" . $url . "\n", FILE_APPEND);
	                echo "Close Redis.\n";
	                $GLOBALS['redis']->close();
	                die();
	            }
	        }
	    }
	if (!$urlInfo['response'])
	{
	    $GLOBALS['redis']->hSetNx($GLOBALS['visited'], $url, 'false');
	    echo "html error.\n";
	    $GLOBALS['redis']->hDel($GLOBALS['pending'],$url);
	    die();
	}
	$dom = new DOMDocument();
	@$dom->loadHTML($urlInfo['response']);
	$xpath = new DOMXPath($dom);
	$hrefs = $xpath->evaluate('/html/body//a');
	unset($dom);
	unset($xpath);
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
	        file_put_contents($GLOBALS['file_name_process'], "Image:" . $url . "\n", FILE_APPEND);
	        unset($ext);
	        continue;
	    }
	    unset($ext);
	
	    //检测非法Url
	    if (!preg_match($GLOBALS['pattern'], $url)){
	        file_put_contents($GLOBALS['file_name_process'], "Illegal:" . $url . "\n", FILE_APPEND);
	        continue;
	    }
	
	    //检测外站Url
	    if (!stristr($url, $GLOBALS['domain'])){
	        if (!$GLOBALS['redis']->hExists($GLOBALS['list'], $url)){
	            $GLOBALS['redis']->hSetNx($GLOBALS['list'], $url, 'add');
	            file_put_contents($GLOBALS['file_name_result'], $url . "\n", FILE_APPEND);
	        }
	        continue;
	    }
	    if (!$GLOBALS['redis']->hExists($GLOBALS['visited'], $url) && stristr($url, $GLOBALS['domain'])){
	        $GLOBALS['redis']->hSetNx($GLOBALS['pending'], $url, 'pending');
	    }
	}
	unset($urlArr);
	unset($hrefs);
	
	$pendLen = intval($GLOBALS['redis']->hLen($GLOBALS['pending']));
	echo "Current memory:".round(memory_get_usage(true)/1024/1024,2) ."M\tMalloc memory:".round(memory_get_usage()/1024/1024,2) ."M\tPending remaining:".$pendLen."\n";
	
	exit;
