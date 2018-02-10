<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");


$checkDay = date('Y-m-d',strtotime("-1 day"));
$checkPreviousDay = date('Y-m-d',strtotime("-2 day"));
$checkPreviousWeek = date('Y-m-d',strtotime("-1 week"));
//echo $checkDay.PHP_EOL.$checkPreviousDay.PHP_EOL.$checkPreviousWeek;exit;
//联盟分组 检测
$affList = getDailyTransactionAff(13);
$alertStr = '';
$dayColumn = '';
foreach ($affList as $key=>$value){
    
    if(!$dayColumn){
        $dayColumn = implode("\t\t\t|", array_keys($value)).'<BR/>';
        $alertStr .= $dayColumn;
    }
    if($value[$checkDay]<=0 && $value[$checkPreviousDay]>0){ //如果小于零，但是前一天大于零
        $value[$checkDay] = '<span style="color: red;">'.$value[$checkDay].'</span>';
        $value[$checkPreviousDay] = '<span style="color: red;">'.$value[$checkPreviousDay].'</span>';
        $alertStr .= $key."\t\t\t:". implode("\t\t\t|", $value) .'<BR/>';
    }elseif($value[$checkPreviousDay] > 0 && (bcdiv($value[$checkDay],$value[$checkPreviousDay],3) > 2.25 || bcdiv($value[$checkDay],$value[$checkPreviousDay],3)  < 0.30 )){ //跟前一天比较
        $value[$checkDay] = '<span style="color: red;">'.$value[$checkDay].'</span>';
        $value[$checkPreviousDay] = '<span style="color: red;">'.$value[$checkPreviousDay].'</span>';
        $alertStr .= $key."\t\t\t:". implode("\t\t\t|", $value) .'<BR/>';
    }elseif($value[$checkPreviousWeek] > 0 && (bcdiv($value[$checkDay],$value[$checkPreviousWeek],3) > 2.25 || bcdiv($value[$checkDay],$value[$checkPreviousWeek],3)  < 0.30 )){ //跟上周比较
        $value[$checkDay] = '<span style="color: red;">'.$value[$checkDay].'</span>';
        $value[$checkPreviousWeek] = '<span style="color: red;">'.$value[$checkPreviousWeek].'</span>';
        $alertStr .= $key."\t\t\t:". implode("\t\t\t|", $value) .'<BR/>';
    }
}

if($alertStr){
    $to = "merlinxu@brandreward.com";
    AlertEmail::SendAlert('CHECK BR Commission + BDG Commission BY AFF',nl2br("Please Check Aff Commission:<BR/>".$alertStr), $to);
}

//站点分组
$siteList =  getDailyTransactionSite(13);
$alertStr = '';
$dayColumn = '';
foreach ($siteList as $key=>$value){

    if(!$dayColumn){
        $dayColumn = implode("\t\t\t|", array_keys($value)).'<BR/>';
        $alertStr .= $dayColumn;
    }
    if($value[$checkDay]<=0 && $value[$checkPreviousDay]>0){ //如果小于零，但是前一天大于零
        $value[$checkDay] = '<span style="color: red;">'.$value[$checkDay].'</span>';
        $value[$checkPreviousDay] = '<span style="color: red;">'.$value[$checkPreviousDay].'</span>';
        $alertStr .= $key."\t\t\t:". implode("\t\t\t|", $value) .'<BR/>';
    }elseif($value[$checkPreviousDay] > 0 && (bcdiv($value[$checkDay],$value[$checkPreviousDay],3) > 2.25 || bcdiv($value[$checkDay],$value[$checkPreviousDay],3)  < 0.30 )){ //跟前一天比较
        $value[$checkDay] = '<span style="color: red;">'.$value[$checkDay].'</span>';
        $value[$checkPreviousDay] = '<span style="color: red;">'.$value[$checkPreviousDay].'</span>';
        $alertStr .= $key."\t\t\t:". implode("\t\t\t|", $value) .'<BR/>';
    }elseif($value[$checkPreviousWeek] > 0 && (bcdiv($value[$checkDay],$value[$checkPreviousWeek],3) > 2.25 || bcdiv($value[$checkDay],$value[$checkPreviousWeek],3)  < 0.30 )){ //跟上周比较
        $value[$checkDay] = '<span style="color: red;">'.$value[$checkDay].'</span>';
        $value[$checkPreviousWeek] = '<span style="color: red;">'.$value[$checkPreviousWeek].'</span>';
        $alertStr .= $key."\t\t\t:". implode("\t\t\t|", $value) .'<BR/>';
    }
}

if($alertStr){
    $to = "merlinxu@brandreward.com";
    AlertEmail::SendAlert('CHECK BR Commission + BDG Commission BY SITE',nl2br("Please Check SITE Commission:<BR/>".$alertStr), $to);
    exit;
}


function getDailyTransactionAff($days = 9){
    $objProgram = New ProgramDb();
    $day = date('Y-m-d', strtotime("-$days day"));
    $sql = "SELECT DISTINCT Af FROM rpt_transaction_unique WHERE CreatedDate >='$day' AND af != 'mega' and af != 'mk'";
    $Aff = $objProgram->objMysql->getRows($sql);
    $list_br =array();
    for($i = 0;$i <= $days;$i++){
        $d = date('Y-m-d', strtotime("-$i day"));
        foreach($Aff as $all){
            $list_br[$all['Af']][$d] = 0;
        }
        $sql = "SELECT SUM(Commission) AS COUNT,Af FROM rpt_transaction_unique  WHERE CreatedDate = '$d' and af != 'mega' and af != 'mk' GROUP BY Af";
        $arr[$i] = $objProgram->objMysql->getRows($sql);
        foreach($arr[$i] as $data){
            $list_br[$data['Af']][$d] = $data['COUNT'];
        }
    }

    
    $db_mk = new Mysql('bdg_go_base', 'bdg01.bwe.io', 'bdg_go', 'sh@#!azS81m');
    $sql = "SELECT DISTINCT Af FROM rpt_transaction_unique WHERE CreatedDate >='$day'";
    $Aff = $db_mk->getRows($sql);
    $list_mk =array();
    for($i = 0;$i <= $days;$i++){
        $d = date('Y-m-d', strtotime("-$i day"));
        foreach($Aff as $all){
            $list_mk[$all['Af']][$d] = 0;
        }
        $sql = "SELECT SUM(Commission) AS COUNT,Af FROM rpt_transaction_unique  WHERE CreatedDate = '$d' GROUP BY Af";
        $arr[$i] = $db_mk->getRows($sql);
        foreach($arr[$i] as $data){
            $list_mk[$data['Af']][$d] = $data['COUNT'];
        }
    }

    foreach($list_br as $af=>$data){
        foreach($data as $date=>$commission){
            if(isset($list_mk[$af]) && isset($list_mk[$af][$date])){
                $list_mk[$af][$date] = bcadd($list_mk[$af][$date],$commission,4);
            }else{
                $list_mk[$af][$date] = $commission;
            }
        }
    }


    return $list_mk;
}

function getDailyTransactionSite($days = 9){
    $objProgram = New ProgramDb();
    $day = date('Y-m-d', strtotime("-$days day"));
    $sql = "SELECT DISTINCT Alias FROM rpt_transaction_unique WHERE CreatedDate >='$day' AND Alias != 'unknown' AND Alias != ''";
    $Aff = $objProgram->objMysql->getRows($sql);
    $list_br =array();
    for($i = 0;$i <= $days;$i++){
        $d = date('Y-m-d', strtotime("-$i day"));
        foreach($Aff as $all){
            $list_br[$all['Alias']][$d] = 0;
        }
        $sql = "SELECT SUM(Commission) AS COUNT,Alias FROM rpt_transaction_unique  WHERE CreatedDate = '$d' AND Alias != 'unknown' AND Alias != '' GROUP BY Alias";
        $arr[$i] = $objProgram->objMysql->getRows($sql);
        foreach($arr[$i] as $data){
            $list_br[$data['Alias']][$d] = $data['COUNT'];
        }
    }

    $db_mk = new Mysql('bdg_go_base', 'bdg01.bwe.io', 'bdg_go', 'sh@#!azS81m');
    $sql = "SELECT DISTINCT Alias FROM rpt_transaction_unique WHERE CreatedDate >='$day' and Alias != 'unknown' AND Alias != '' AND Alias != 'brandreward'";
    $Aff = $db_mk->getRows($sql);
    $list_mk =array();
    for($i = 0;$i <= $days;$i++){
        $d = date('Y-m-d', strtotime("-$i day"));
        foreach($Aff as $all){
            $list_mk[$all['Alias']][$d] = 0;
        }
        $sql = "SELECT SUM(Commission) AS COUNT,Alias FROM rpt_transaction_unique  WHERE CreatedDate = '$d' and Alias != 'unknown' AND Alias != '' AND Alias != 'brandreward' GROUP BY Alias";
        $arr[$i] = $db_mk->getRows($sql);
        foreach($arr[$i] as $data){
            $list_mk[$data['Alias']][$d] = $data['COUNT'];
        }
    }

    foreach($list_br as $af=>$data){
        foreach($data as $date=>$commission){
            if(isset($list_mk[$af]) && isset($list_mk[$af][$date])){
                $list_mk[$af][$date] = bcadd($list_mk[$af][$date],$commission,4);
            }else{
                $list_mk[$af][$date] = $commission;
            }
        }
    }

    

    return $list_mk;
}

?>