<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init_back.php');

if($_SESSION['u']['Career'] == 'network'){
    header("Location:b_aff_traffic.php");
    exit();
}

if($_SESSION['u']['Career'] == 'advertiser'){
    header("Location:b_ad_traffic.php");
    exit();
}

if($_SESSION['u']['Career'] == 'advertiser'){
    header("Location:b_ad_traffic_ctr.php");
    exit();
}

check_user_login();
include_once('auth_ini.php');

$objStatis = new Statis;
$apiKey = $objStatis->getApiKey($USERINFO['ID']);
$search['apiKey'] = $apiKey;
if(isset($_POST['startDate']) && isset($_POST['endDate'])){
    try {
        $search['startDate'] = $_POST['startDate'];
        $search['endDate'] = $_POST['endDate'];
        if(isset($_POST['type'])){
            switch ($_POST['type']){
                case "Clicks":
                    $detail = $objStatis->getClickDetail($search);
                    break;
                case "Transactions":
                    $detail = $objStatis->getTransactionDetail($search);
                    break;
                default:
                    $detail = $objStatis->getCommissionDetail($search);
                    break;
            }
            $dateList = $dataList = array();
            foreach ($detail as $temp){
                $dateList[] = $temp['createddate'];
                if($temp['data']<0){
                    $dataList[] = 0;
                }else {
                    $dataList[] = round($temp['data'],2);
                }
            }
            $result['dateList'] = $dateList;
            $result['dataList'] = $dataList;
            echo json_encode(array("code"=>1,"result"=>$result));
            exit;
        }
        
        if(isset($_POST['changeMark']) && $_POST['changeMark'] == "1"){
            $clickTotal = $objStatis->getClick($search);
            $transactionTotal = $objStatis->getTransaction($search);
            $commissionTotal = $objStatis->getCommission($search);
            $topDomainRow = $objStatis->getTopAdvertises($search);
            
            $result['clickTotal'] = number_format($clickTotal);
            $result['transactionTotal'] = number_format($transactionTotal);
            $result['commissionTotal'] = "$".number_format($commissionTotal,2);
            $topAdvStoreList = array();
            $topAdvComList = array();
            foreach ($topDomainRow as $temp){
                $topAdvStoreList[] = $temp['store'];
                $topAdvComList[] = round($temp['Commission'],2);
            }
            $result['topAdvStoreList'] = $topAdvStoreList;
            $result['topAdvComList'] = $topAdvComList;
            echo json_encode(array("code"=>1,"result"=>$result));
            exit;
        }
    } catch (\Exception $e) {
        echo json_encode(array("code"=>2,"msg"=>"An unknown error occurred,Please refresh and try again"));
        exit;
    }
    echo json_encode(array("code"=>2,"msg"=>"An unknown error occurred,Please refresh and try again"));
    exit;
}
$objDomain = new Domain;
//advertise总数
// $totalAdvertises = $objDomain->getTotalAdvertisesForHomePage($search, $USERINFO['ID']);

/* $d = new DateTime();
$d0 = $d->format('Y-m-d');
$d30 = $d->modify('-29 day')->format('Y-m-d');
$d31 = $d->modify('-1 day')->format('Y-m-d');
$d60 = $d->modify('-29 day')->format('Y-m-d');



$search['apiKey'] = $apiKey;
$search['endDate'] = $d0;
$search['startDate'] = $d30;
$clickDetail = $objStatis->getTopAdvertises($search);












$url = explode('/',BASE_URL);
array_pop($url);
$fileurl = join('/',$url);
$d = new DateTime();
$d0 = $d->format('Y-m-d');
$d30 = $d->modify('-29 day')->format('Y-m-d');
$d31 = $d->modify('-1 day')->format('Y-m-d');
$d60 = $d->modify('-29 day')->format('Y-m-d');

$objStatis = new Statis;
$objTran = new Transaction;
$search = array();
$search['visitTo'] = $d0;
$search['visitFrom'] = $d30;
$search['uid'] = $USERINFO['ID'];

$commission0t30 = $objStatis->getCommission($search);

//referrer commission
$commissionReferrer0t30 = $objTran->getReferrerCommission($search);

$search = array();
$search['visitTo'] = $d31;
$search['visitFrom'] = $d60;
$search['uid'] = $USERINFO['ID'];

$commission30t60 = $objStatis->getCommission($search);
//referrer commission
$commissionReferrer30t60 = $objTran->getReferrerCommission($search);

$search = array();
$search['to'] = $d0;
$search['from'] = $d30;
$search['uid'] = $USERINFO['ID'];
$click0to30 = $objStatis->getClick($search);

$search = array();
$search['pz'] = 10;
$search['type'] = 'merchants';
$search['tran_from'] = $d30;
$search['tran_to'] = $d0;
$search['uid'] = $USERINFO['ID'];
$topDomainRow = $objStatis->getTransactionRpt($search);
$topDomain = $topDomainRow['tran'];

$objTpl->assign('commissionReferrer0t30', $commissionReferrer0t30);
$objTpl->assign('commissionReferrer30t60', $commissionReferrer30t60);
$objTpl->assign('commission0t30', $commission0t30);
$objTpl->assign('commission30t60', $commission30t60);
$objTpl->assign('click0to30', $click0to30);
$objTpl->assign('topDomain', $topDomain); */
// $objTpl->assign('totalAdvertises', $totalAdvertises);
$objTpl->assign('fileurl', $fileurl.'/admin_mlink_com/');
$sys_header['css'][] = BASE_URL.'/css/daterangepicker.css';
$sys_header['css'][] = BASE_URL.'/css/front.css';
$sys_header['css'][] = BASE_URL.'/css/site.css';
$sys_header['js'][] = BASE_URL.'/js/moment.min.js';
$sys_header['js'][] = BASE_URL.'/js/highcharts.js';
$sys_header['js'][] = BASE_URL.'/js/daterangepicker.js';
$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('sys_userinfo', $USERINFO);
$objTpl->display('b_home.html');
?>
