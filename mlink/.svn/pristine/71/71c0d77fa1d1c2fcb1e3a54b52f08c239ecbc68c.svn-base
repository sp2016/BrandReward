<?php
include_once("comm.php");
include_once(PATH_CODE."/lib/currency_exchange.php");
$transaction = new Transaction();
//获取affid
$affid = isset($argv[1]) ? $argv[1] : 0;
$start_time = isset($argv[2]) ? preg_match('/^[\d]{4}-[\d]{2}-[\d]{2}$/', $argv[2],$start_time) : null;
if(isset($argv[2]) && isset($argv[3])){
    preg_match('/^[\d]{4}-[\d]{2}-[\d]{2}$/', $argv[2],$start_time);
    preg_match('/^[\d]{4}-[\d]{2}-[\d]{2}$/', $argv[3],$end_time);
}

//获取联盟id alias A P
$Affiliate = $transaction->getAccountInfoById($affid);
//设置alias&AP
if(is_array($Affiliate)){
    define('AFFILIATE_NAME',$Affiliate[0]['Alias']);
    define('AFFILIATE_USER',$Affiliate[0]['Account']);
    define('AFFILIATE_PASS',$Affiliate[0]['Password']);
    if(!file_exists(PATH_DATA."/".AFFILIATE_NAME)){
        mkdir(PATH_DATA."/".AFFILIATE_NAME);
    }
    if(!is_null($start_time) && !is_null($end_time)){
        define("START_TIME",$start_time[0]);
        define("END_TIME",$end_time[0]);
    }
    //请求爬虫脚本
    $file_name = PATH_CODE."/{$Affiliate[0]['Alias']}_{$Affiliate[0]['ID']}.php";
    $sessionId = md5($file_name.uniqid());
    if(file_exists($file_name)){
        operate_crawl_log(array('sessionId'=>$sessionId,'affid'=>$affid,'method'=>'transactionCrawl','logfile'=>'/home/bdg/transaction/server_transaction/log/'.AFFILIATE_NAME.'.log'),'insert');
        require($file_name);
        operate_crawl_log(array('sessionId'=>$sessionId,'error_descp'=>''),'update');
    }
    else{
        operate_crawl_log(array('sessionId'=>$sessionId,'error_descp'=>"affiliate {$Affiliate[0]['ID']} not exist!"),'update');
        mydie("affiliate {$Affiliate[0]['ID']} not exist!"."\n\r");
    }
}else{
    //无参数
    operate_crawl_log(array('sessionId'=>$sessionId,'error_descp'=>"php murphy.php [affid] [start_time] [end_time]"."\n\r"."example:php murphy.php 1 2016-01-10 2016-02-02"),'update');
    mydie("php murphy.php [affid] [start_time] [end_time]"."\n\r"."example:php murphy.php 1 2016-01-10 2016-02-02"."\n\r");
}

//record crawler log
function operate_crawl_log($data,$flag){

    /*$sessionId = $data['sessionId'];
    $markTime = date('Y-m-d H:i:s');
     
    $platform = 'BR';
     
    $mysqlTmp = new Mysql('bdg_go_base', 'bdg02.i.bwe.io', 'bdg_go', 'shY12Nbd8J');

    if($flag == 'insert'){
        $affid = $data['affid'];
        $method = $data['method'];
        $logfile = $data['logfile'];
        $date  = date('Y-m-d');

        $sql = "INSERT INTO crawl_script_run_log (sessionId,date,startTime,platform,affid,method,logfile)
            VALUES ('".$sessionId."','".$date."','".$markTime."','".$platform."',".$affid.",'".$method."','".$logfile."')";
        $mysqlTmp->query($sql);

    }elseif ($flag == 'update'){
         
        $error_descp = $data['error_descp'];
        $sql = "SELECT * FROM crawl_script_run_log where sessionId = '".$sessionId."'";
        $logInfo = $mysqlTmp->getRows($sql);
        if($logInfo){
            $sql = "UPDATE crawl_script_run_log SET endTime = '".$markTime."', status = 'finish', error_descp = '".$error_descp."'  WHERE sessionId = '".$sessionId."'";
            $mysqlTmp->query($sql);
        }
    }*/

    return;
}
