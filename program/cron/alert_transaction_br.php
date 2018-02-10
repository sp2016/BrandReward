 <?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");


$checkDay = date('Y-m-d',strtotime("-1 day"));
$checkPreviousDay = date('Y-m-d',strtotime("-2 day"));
$checkPreviousWeek = date('Y-m-d',strtotime("-1 week"));
//echo $checkDay.PHP_EOL.$checkPreviousDay.PHP_EOL.$checkPreviousWeek;exit;
//联盟分组 检测
$affList = getDailyTransactionAff(13);

$alertStr ="Please Check Aff Commission:<BR/>";
$alertStr .= "<table border=1>";
$dayColumn = '';
foreach ($affList as $key=>$value){
    
    $alertFlag = false;
    $zeroTimes = 0;
    $rangTotal = 0;
    $rangAverage = 0;
    if(!$dayColumn){
        $dayColumn = implode("</th><th>", array_keys($value));
        $alertStr .= "<tr><th>Aff</th><th>".$dayColumn."</th><th>Average</th></tr>";
    }
    
    //计算一批量
    foreach ($value as $rv){
        if($rv == 0)
            $zeroTimes++;
        $rangTotal = $rangTotal+$rv;
    }
    $rangAverage = round($rangTotal/14,2);
    //echo $zeroTimes.'---'.$rangTotal;exit;
    
    //if($value[$checkDay]<=0 && $zeroTimes>=4 && $rangAverage <=20){ //如果小于零，但是前一天大于零
    //   continue;
    //}
    
   
    
    if($value[$checkDay]<=0 && $zeroTimes<4 && $rangAverage >= 20){ //如果小于零，但是前一天大于零
        $value[$checkDay] = '<span style="color: red;">'.$value[$checkDay].'</span>';
        $value[$checkPreviousDay] = '<span style="color: red;">'.$value[$checkPreviousDay].'</span>';
        $alertStr .= "<tr><td>".$key."</td><td>". implode("</td><td>", $value) ."</td><td>$rangAverage</td></tr>";
        $alertFlag = true;
    }elseif($value[$checkDay] > 0 && $value[$checkPreviousDay] > 0 && 
        //(bcdiv($value[$checkDay],$value[$checkPreviousDay],3) > 3.00 || bcdiv($value[$checkDay],$value[$checkPreviousDay],3)  < 0.30 ) &&
        abs(($value[$checkDay]-$value[$checkPreviousDay])/$value[$checkPreviousDay]) > 0.5 && 
        abs(($value[$checkDay]-$rangAverage)/$rangAverage) > 0.5 ){ //跟前一天比较 + 平均
        
        if(bcdiv($value[$checkDay],$value[$checkPreviousDay],3) > 1)
            $color = 'green';
        else
            $color = 'red';
        $value[$checkDay] = '<span style="color: '.$color.';">'.$value[$checkDay].'</span>';
        $value[$checkPreviousDay] = '<span style="color: '.$color.';">'.$value[$checkPreviousDay].'</span>';
        $alertStr .= "<tr><td>".$key."</td><td>". implode("</td><td>", $value) ."</td><td>$rangAverage</td></tr>";
        $alertFlag = true;
    }elseif($value[$checkDay] > 0 && $value[$checkPreviousWeek] > 0 && 
        //(bcdiv($value[$checkDay],$value[$checkPreviousWeek],3) > 3.00 || bcdiv($value[$checkDay],$value[$checkPreviousWeek],3)  < 0.30 ) && 
        abs(($value[$checkDay]-$value[$checkPreviousWeek])/$value[$checkPreviousWeek]) > 0.5 &&
        abs(($value[$checkDay]-$rangAverage)/$rangAverage) > 0.5){ //跟上周比较 + 平均
        if(bcdiv($value[$checkDay],$value[$checkPreviousWeek],3) > 1)
            $color = 'green';
        else 
            $color = 'red';
        $value[$checkDay] = '<span style="color: '.$color.';">'.$value[$checkDay].'</span>';
        $value[$checkPreviousWeek] = '<span style="color: '.$color.';">'.$value[$checkPreviousWeek].'</span>';
        $alertStr .= "<tr><td>".$key."</td><td>". implode("</td><td>", $value) ."</td><td>$rangAverage</td></tr>";
        $alertFlag = true;
    }
    if($alertFlag) unset($affList[$key]);
}
$alertStr .= "</table></br></br>";


//-----------------lost ---------------------------------

$startTime = date('Y-m-d H:i:s', strtotime('-24 hours'));
$endTime = date('Y-m-d H:i:s',time());

$alertStr .="Please Check Aff Lost UPD File:<BR/>";
$alertStr .="------------------------------------------------------------------------<BR/>";
$sql = "select * from crawl_transaction_lost_file_logs where `AddTime` >= '$startTime' and `AddTime` < '$endTime' ";
$objProgram = New ProgramDb();
$alterArr = $objProgram->objMysql->getRows($sql);

foreach ($alterArr as $value){

    $alertStr .= $value['AffName'].'=>'.$value['AffId']." ,LostFile: ".$value['LostFile'].PHP_EOL;

}



//-----------------------------显示不报错的transaction-------------------------------------------------------------
$alertStr .="<BR/><BR/><BR/>Shwo Normal Transaction List:<BR/>";
$alertStr .="------------------------------------------------------------------------<BR/>";
$dayColumn = '';
$alertStr .= "<table border=1>";
foreach ($affList as $key=>$value){
    
    $zeroTimes = 0;
    $rangTotal = 0;
    $rangAverage = 0;
    if(!$dayColumn){
        $dayColumn = implode("</th><th>", array_keys($value));
        $alertStr .= "<tr><th>Aff</th><th>".$dayColumn."</th><th>Average</th></tr>";
    }
    
    //计算一批量
    foreach ($value as $rv){
        if($rv == 0)
            $zeroTimes++;
        $rangTotal = $rangTotal+$rv;
    }
    $rangAverage = round($rangTotal/14,2);
    
    $alertStr .= "<tr><td>".$key."</td><td>". implode("</td><td>", $value) ."</td><td>$rangAverage</td></tr>";
    
}
$alertStr .= "</table></br></br>";

if($alertStr){
    //$to = "merlinxu@brandreward.com";
    $to = "merlinxu@brandreward.com,stanguan@meikaitech.com";
    AlertEmail::SendAlert('CHECK BR Commission BY AFF',nl2br($alertStr), $to);
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

    return $list_br;
}



?>