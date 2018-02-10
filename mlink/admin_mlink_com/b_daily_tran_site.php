<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');

$T = new Transaction();
$list = $T->getDailyTransactionSite(13);
for($i = 0; $i <= 13; ++$i ){
    $date[$i] = date('Y-m-d',strtotime("-$i day"));
}

$all = array();
foreach($date as $d){
    $all[$d] = 0;
}

foreach($list as $k=>$v){
    foreach($date as $d){
        if(!isset($list[$k][$d])){
            $list[$k][$d] = 0;
        }else{
            $all[$d] = bcadd($all[$d],$list[$k][$d],4);
        }
    }
}

$list = array_merge(array('Total'=>$all),$list);

//only check different data between this week and last week
$day_this = array_slice($date,0,7);
$day_last = array_slice($date,7);

$warning = array();

foreach($list as $aff=>$revlist){
    foreach($day_this as $dk=>$dv){
        //pass when the rev is less than $100
        if($revlist[$dv] < 100)
            continue;
        if($revlist[$day_last[$dk]] < 100)
            continue;
        if(bcdiv($revlist[$dv],$revlist[$day_last[$dk]],3) > 1.25 || bcdiv($revlist[$dv],$revlist[$day_last[$dk]],3) < 0.75)
            $warning[$aff][$dv] = bcdiv($revlist[$dv],$revlist[$day_last[$dk]],3);
    }
}

$objTpl->assign('warning',$warning);
$sys_header['css'][] = BASE_URL.'/css/front.css';
$objTpl->assign('sys_header', $sys_header);
$objTpl->assign('column',$date);
$objTpl->assign('info',$list);
$objTpl->display('b_daily_tran_site.html');
