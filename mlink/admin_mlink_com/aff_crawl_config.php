<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init2.php');



//$pengingLinksObj = new Mysql('pendinglinks', 'localhost', 'root', '');
$pengingLinksObj = new Mysql(PENDING_NAME, PENDING_HOST, PENDING_USER, PENDING_PASS);
$sql = "select * from aff_crawl_config";
$crawl_config = $pengingLinksObj->getRows($sql, "AffId");
//print_r($crawl_config);exit;
$sql = "select name,id,isactive from wf_aff order by name asc";
$affArr = $db->getRows($sql,'id');


if(isset($_POST['updateconfig']) && !empty($_POST['updateconfig']))
{
    $affid = $_POST['affid'];
    $configInfo = $crawl_config[$affid];
    $objTpl->assign('affArr', $affArr);
    $objTpl->assign("info",$configInfo);
    echo $objTpl->fetch('update_aff_crawl_config.html');
    exit();
}

if(isset($_POST['act']) && $_POST['act'] =='updateOperation')
{
    //print_r($_POST);exit;
    if($_POST['affid'] && $_POST['Status'] && $_POST['ProgramCrawlStatus'] && $_POST['LinkCrawlStatus'] && $_POST['FeedCrawlStatus'] && $_POST['ProductCrawlStatus']){
        $affid =  $_POST['affid'];
        $Status =  $_POST['Status'];
        $ProgramCrawlStatus =  $_POST['ProgramCrawlStatus'];
        $LinkCrawlStatus =  $_POST['LinkCrawlStatus'];
        $StatsCrawlStatus =  $_POST['StatsCrawlStatus'];
        $FeedCrawlStatus =  $_POST['FeedCrawlStatus'];
        $ProductCrawlStatus =  $_POST['ProductCrawlStatus'];
        
        
        $sql = "update aff_crawl_config set `Status`='{$Status}',ProgramCrawlStatus='{$ProgramCrawlStatus}',LinkCrawlStatus='{$LinkCrawlStatus}',StatsCrawlStatus='{$StatsCrawlStatus}',FeedCrawlStatus='{$FeedCrawlStatus}',
                ProductCrawlStatus='{$ProductCrawlStatus}' where AffId =  $affid";
        //echo $sql;exit;
        $pengingLinksObj->query($sql);
        $data = array(
            'flag' => 1,
            'msg' => 'Success'
        );
        
    }else{
        $data = array(
            'flag' => 2,
            'msg' => 'Update Error!'
        );
    }
    echo json_encode($data);
    exit;
}


if(isset($_POST['act']) && $_POST['act'] =='addConfig')
{
    
    if($_POST['affid'] && $_POST['Status'] && $_POST['ProgramCrawlStatus'] && $_POST['LinkCrawlStatus'] && $_POST['FeedCrawlStatus'] && $_POST['ProductCrawlStatus']){
        $affid =  $_POST['affid'];
        $Status =  $_POST['Status'];
        $ProgramCrawlStatus =  $_POST['ProgramCrawlStatus'];
        $LinkCrawlStatus =  $_POST['LinkCrawlStatus'];
        $FeedCrawlStatus =  $_POST['FeedCrawlStatus'];
        $StatsCrawlStatus =  $_POST['StatsCrawlStatus'];
        $ProductCrawlStatus =  $_POST['ProductCrawlStatus'];

        //查询是否存在
        $sql = "select * from aff_crawl_config where affid = $affid";
        $confExist = $pengingLinksObj->getRows($sql);
         
        if(!empty($confExist)){
            
            $data = array(
            'flag' => 2,
            'msg' => 'network existing!'
            );
        }
        else{
            //INSERT INTO tbl_name (col1,col2) VALUES(15,col1*2);
            $sql ="INSERT INTO aff_crawl_config (AffId,`Status`,ProgramCrawlStatus,LinkCrawlStatus,StatsCrawlStatus,FeedCrawlStatus,ProductCrawlStatus) VALUES ($affid,'{$Status}',
                 '{$ProgramCrawlStatus}','{$LinkCrawlStatus}','{$StatsCrawlStatus}','{$FeedCrawlStatus}','{$ProductCrawlStatus}')";
            $pengingLinksObj->query($sql);
            $data = array(
            'flag' => 1,
            'msg' => 'Success'
            );
            
        }
    }
    echo json_encode($data);
    exit;
     
}


$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_header['js'][] = BASE_URL.'/js/b_account.js';
$sys_footer['js'][] = BASE_URL.'/js/back.js';
$sys_footer['js'][] = BASE_URL.'/js/b_performance.js';
//print_r($crawl_config);exit;
$objTpl->assign('list', $crawl_config);
$objTpl->assign('affArr', $affArr);

$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('sys_footer', $sys_footer);
$objTpl->display('aff_crawl_config.html');
?>