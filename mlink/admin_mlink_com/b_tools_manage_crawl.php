<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');

$d = new DateTime();
$_GET['tran_to'] = isset($_GET['tran_to'])&&$_GET['tran_to']?$_GET['tran_to']:$d->modify('-1 day')->format('Y-m-d');
$_GET['tran_from'] = isset($_GET['tran_from'])&&$_GET['tran_from']?$_GET['tran_from']:$d->modify('-6 day')->format('Y-m-d');

$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_header['js'][] = BASE_URL.'/js/b_account.js';
$sys_header['js'][] = BASE_URL.'/js/Chart.js';

$sys_footer['js'][] = BASE_URL.'/js/back.js';
$sys_footer['js'][] = BASE_URL.'/js/b_performance.js';

$objTools = new Tools;
$affiList = $objTools->getClawerAff();
$affiListConf =  $objTools->getClawerAffConf();

$cmd = 'ps aux | grep grep -v | grep /home/bdg/program/crawl/job.data.php | grep getallfeeds -c';
exec($cmd, $scriptSum);

$list = array();
foreach ($affiListConf as $key=>$value){
    if(isset($affiList[$key])){
        $list[$key] = $value;
        $list[$key]['Name'] = $affiList[$key]['Name'];
        
        //program
        $cmd = 'ps aux | grep grep -v | grep /home/bdg/program/crawl/job.data.php | grep affid='.$key.' | grep getprogram -c';
        exec($cmd, $scriptProgramRun);
        $list[$key]['scriptGetprogram'] = $scriptProgramRun[0];
        unset($scriptProgramRun);
        
        //links
        $cmd = 'ps aux | grep grep -v | grep /home/bdg/program/crawl/job.data.php | grep affid='.$key.' | grep getallpagelinks -c';
        exec($cmd, $scriptLinksRun);
        $list[$key]['scriptGetlinks'] = $scriptLinksRun[0];
        unset($scriptLinksRun);
        
        //feed
        $cmd = 'ps aux | grep grep -v | grep /home/bdg/program/crawl/job.data.php | grep affid='.$key.' | grep getallfeeds -c';
        exec($cmd, $scriptFeedsRun);
        $list[$key]['scriptGetfeed'] = $scriptFeedsRun[0];
        unset($scriptFeedsRun);
    }
}


if($_POST){
    
    //affid:115
    //crawl:program
    //type:1
    $ret = array(
        'flag' => 0,
        'message' => '',
        'html'=>'',
    );
    
    $affid = $_POST['affid'];
    $crawl = $_POST['crawl'];
    $type  = $_POST['type'];
    
    if(!isset($affid) || !isset($crawl) || !isset($type)){
        $ret = array(
            'flag' => 0,
            'message' => 'missing param! please check.',
            'html'=>'',
        );
        echo json_encode($ret);exit;
    }
    
    if($type == 1) //start crawl
    {
        //check this operation has online process
        $cmd = 'ps aux | grep grep -v | grep /home/bdg/program/crawl/job.data.php | grep affid='.$affid.' | grep '.$crawl.' -c';
        exec($cmd, $scriptRun);
        if($scriptRun[0]>0){
            $ret = array(
                'flag' => 0,
                'message' => 'has online process.',
                'html'=>'',
            );
            unset($scriptRun);
            echo  json_encode($ret);exit;
        }
        //开启脚本
        $cmd = "php /home/bdg/program/crawl/job.data.php --affid=$affid --method=$crawl --daemon --silent &";
        system($cmd);
        $ret = array(
            'flag' => 1,
            'message' => 'start '.$crawl.' script.',
            'html'=>'',
        );
        echo  json_encode($ret);exit;
    }
    elseif($type == 2) //end crawl
    {
        //check this operation has online process
        $cmd = 'ps aux | grep grep -v | grep /home/bdg/program/crawl/job.data.php | grep affid='.$affid.' | grep '.$crawl.' -c';
        exec($cmd, $scriptRun);
        if($scriptRun[0]>0){
            
            $checkPidStrCmd = 'ps ax | grep grep -v | grep /home/bdg/program/crawl/job.data.php | grep affid='.$affid.' | grep '.$crawl ;
            $checkPidStr = system($checkPidStrCmd);
            $checkPidArr = explode("\n", $checkPidStr);
            
            foreach($checkPidArr as $v){
                $processInfo = explode(" ", trim($v));
                $pid = $processInfo[0];
            
                if($pid){
                    echo $pid.PHP_EOL;
                    system("kill -9 ".$pid);
                }
            }
            
            $ret = array(
                'flag' => 1,
                'message' => 'succeed kill carwl'.$crawl,
                'html'=>'',
            );
            unset($scriptRun);
            echo json_encode($ret);exit;
            
        }
        else {
            $ret = array(
                'flag' => 0,
                'message' => $crawl.' script already over',
                'html'=>'',
            );
            echo  json_encode($ret);exit;
        }
        
    }
    
    
    
     
    
}



$objTpl->assign('list', $list);
$objTpl->assign('scriptSum', $scriptSum[0]);
$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('sys_footer', $sys_footer);
$objTpl->display('b_tools_manage_crawl.html');
?>