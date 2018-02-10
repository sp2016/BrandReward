<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');

$objPayments = new Payments();


if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'ajax'){
    if($_REQUEST['act'] == 'paynetwork'){
        $payinfo = $objPayments->getConfirmNetworkTrade($_REQUEST,'info');
        $paylist = isset($payinfo['publisher'])?$payinfo['publisher']:array();
        $objTpl->assign("paylist",$paylist);
        unset($payinfo['publisher']);
        $objTpl->assign("payinfo",$payinfo);
        echo $objTpl->fetch('b_paid_network_confirm.html');
        exit();
    }elseif($_REQUEST['act'] == 'paydetail'){
        $tradelist = $objPayments->getConfirmNetworkTrade($_REQUEST);
        $page_info =  $objPayments->getConfirmNetworkTrade($_REQUEST,'pagination');
        $page_html = get_page_html_ajax($page_info,'pageJump');

        $objTpl->assign("tradelist",$tradelist);
        $objTpl->assign("search",$_REQUEST);
        $objTpl->assign("pageInfo",$page_info);
        $objTpl->assign("pageHtml",$page_html);
        echo $objTpl->fetch('b_paid_detail_confirm.html');
        exit();
    }elseif($_REQUEST['act'] == 'downloadallcomfirmed'){
        $search = array('pagesize'=>0);
        $tradelist = $objPayments->getConfirmNetworkTrade($search);
        header("Content-type:  application/octet-stream ");
        header("Accept-Ranges:  bytes ");
        header("Content-Disposition:  attachment;  filename= pageinvoice.csv");
        echo "\"ID\",\"Commission\",\"Network\",\"Payment Date\",\"Publisher\"\n";
        if(!empty($tradelist)){
            foreach($tradelist as $k=>$v){
                $publisher = $v['publisher']['Name']?$v['publisher']['Name']:$v['publisher']['UserName'];
                echo "\"".$v['ID']."\",\"".$v['ShowCommissionUSD']."\",\"".$v['Network']."\",\"".$v['CreatedDate']."\",\"".$publisher."\"\n";
            }
        }
        exit();
    }elseif($_REQUEST['act'] == 'confirmpay'){
        $payinfo = $objPayments->getConfirmNetworkTrade($_REQUEST,'info');
        $objTpl->assign("payinfo",$payinfo);
        $objTpl->assign("search",$_REQUEST);
        
        echo $objTpl->fetch('b_paid_confirm.html');
        exit();
    }elseif($_REQUEST['act'] == 'dopay'){
        if(isset($_REQUEST['pid']) && !empty($_REQUEST['pid'])){
            $res = $objPayments->createPayRecord($_REQUEST);
            if($res){
                echo 'success';exit();
            }else{
                echo 'error: create paid recode fail.';exit();
            }
        }else{
            echo 'error: empty pid';exit();
        }
    }elseif($_REQUEST['act'] == 'paidinvoice'){
        $tradelist = $objPayments->getPaidInvoiceData($_REQUEST);
        $page_info =  $objPayments->getPaidInvoiceData($_REQUEST,'pagination');
        $page_html = get_page_html_ajax($page_info,'pageJump');

        $objTpl->assign("tradelist",$tradelist);
        $objTpl->assign("search",$_REQUEST);
        $objTpl->assign("pageInfo",$page_info);
        $objTpl->assign("pageHtml",$page_html);
        echo $objTpl->fetch('b_paid_invoice.html');
        exit();
    }elseif($_REQUEST['act'] == 'showcomment'){
        $payinfo = $objPayments->get_pay_history($_REQUEST['ppid']);
        $objTpl->assign("payinfo",$payinfo);
        $objTpl->assign("search",$_REQUEST);
        
        echo $objTpl->fetch('b_paid_comment.html');
        exit();
    }elseif($_REQUEST['act'] == 'savecomment'){
        $sql = 'UPDATE paid_history SET Amount = '.floatval($_REQUEST['amount']).',Code = "'.addslashes(trim($_REQUEST['code'])).'",Comment = "'.addslashes(trim($_REQUEST['comment'])).'" WHERE PPID = '.intval($_REQUEST['ppid']);
        $objPayments->query($sql);
        exit();
    }elseif($_REQUEST['act'] == 'paybdg'){
        $payinfo = $objPayments->getConfirmBDGTrade($_REQUEST,'info');
        $paylist = isset($payinfo['publisher'])?$payinfo['publisher']:array();

        $objTpl->assign("paylist",$paylist);
        unset($payinfo['publisher']);
        $objTpl->assign("payinfo",$payinfo);
        echo $objTpl->fetch('b_paid_bdg_confirm.html');
        exit();
    }elseif($_REQUEST['act'] == 'paydetailBDG'){
        $tradelist = $objPayments->getConfirmBDGTrade($_REQUEST);
        $page_info =  $objPayments->getConfirmBDGTrade($_REQUEST,'pagination');
        $page_html = get_page_html_ajax($page_info,'pageJump');

        $objTpl->assign("tradelist",$tradelist);
        $objTpl->assign("search",$_REQUEST);
        $objTpl->assign("pageInfo",$page_info);
        $objTpl->assign("pageHtml",$page_html);
        echo $objTpl->fetch('b_paid_detail_confirm_bdg.html');
        exit();
    }elseif($_REQUEST['act'] == 'downloadallcomfirmedbdg'){
        $search = array('pagesize'=>0);
        $tradelist = $objPayments->getConfirmBDGTrade($search);
        header("Content-type:  application/octet-stream ");
        header("Accept-Ranges:  bytes ");
        header("Content-Disposition:  attachment;  filename= pageinvoice.csv");
        echo "\"ID\",\"Commission\",\"Network\",\"Date\",\"Publisher\"\n";
        if(!empty($tradelist)){
            foreach($tradelist as $k=>$v){
                $publisher = $v['publisher']['Name']?$v['publisher']['Name']:$v['publisher']['UserName'];
                $Network = isset($v['source'])?$v['source']['Af']:'';
                echo "\"".$v['ID']."\",\"".$v['ShowCommission']."\",\"".$Network."\",\"".$v['Visited']."\",\"".$publisher."\"\n";
            }
        }
        exit();
    }elseif($_REQUEST['act'] == 'confirmpayBDG'){
        $payinfo = $objPayments->getConfirmBDGTrade($_REQUEST,'info');
        $objTpl->assign("payinfo",$payinfo);
        $objTpl->assign("search",$_REQUEST);
        
        echo $objTpl->fetch('b_paid_confirm_bdg.html');
        exit();
    }elseif($_REQUEST['act'] == 'dopayBDG'){
        if(isset($_REQUEST['pid']) && !empty($_REQUEST['pid'])){
            $res = $objPayments->createPayRecordBDG($_REQUEST);
            if($res){
                echo 'success';exit();
            }else{
                echo 'error: create paid recode fail.';exit();
            }
        }else{
            echo 'error: empty pid';exit();
        }
    }
}

$search = $_GET;

$list = $objPayments->getPaidData($search);
$page_info = $objPayments->getPaidData($search,'pagination');
$page_html = get_page_html($page_info);

$objTpl->assign('list',$list);
$objTpl->assign('search',$search);
$objTpl->assign("title","BR Paid");
$objTpl->assign("pageHtml",$page_html);
$objTpl->assign("pageInfo",$page_info);

$sys_header['css'][] = BASE_URL.'/css/front.css';
$objTpl->assign('sys_header', $sys_header);
$objTpl->display('b_paid.html');
