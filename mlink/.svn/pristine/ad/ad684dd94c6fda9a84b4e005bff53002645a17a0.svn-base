<?php
global $_cf,$_db,$_req;

echo 'update finance start : '.date('Y-m-d H:i:s')."\n";

if( !isset($_req['datemonth']) ){
    $date = date('Y-m-d',strtotime("-3 day"));
    $where_str = 'WHERE updateddate >= '.$date;
}else{
    $d = new DateTime($_req['datemonth']);
    $date_from  = $d->format('Y-m-01');
    $date_to = $d->modify('+1 month')->format('Y-m-01');

    $where_str = 'WHERE createddate >= "'.$date_from.'" AND createddate < "'.$date_to.'"';
}

echo 'doing data in : '.$where_str."\n";

$sql = 'SELECT DISTINCT(VisitedDate) as VisitedDate FROM rpt_transaction_unique '.$where_str;
$rows = $_db->getRows($sql);


$d_v = array();
foreach($rows as $k=>$v){
    if($v['VisitedDate'] != '0000-00-00'){
        $d_v[] = $v['VisitedDate'];    
    }
}

$sql = 'SELECT DISTINCT(CreatedDate) as CreatedDate FROM rpt_transaction_unique '.$where_str;
$rows = $_db->getRows($sql);

$d_c = array();
foreach($rows as $k=>$v){
    if($v['CreatedDate'] != '0000-00-00'){
        $d_c[] = $v['CreatedDate'];
    }
}


$lastversion = date('YmdHis');
if(!empty($d_v)){
    update_visite_data($d_v,$lastversion);
    clear_invalid_data($d_v,'VisitedDate',$lastversion);
}
if(!empty($d_c)){
    update_create_data($d_c,$lastversion);
    clear_invalid_data($d_c,'CreatedDate',$lastversion);
}


echo 'update finance end : '.date('Y-m-d H:i:s')."\n";

function clear_invalid_data($d,$type,$lastversion){
    global $_db;
    if($type == 'VisitedDate'){
        $sql = "UPDATE statis_br SET c_orders = 0,c_sales = 0.0000 ,c_revenues = 0.0000,c_showrevenues = 0.0000,c_lastversion=0 WHERE createddate IN ('".join("','",$d)."') AND c_lastversion != '".$lastversion."'";
    }else{
        $sql = "UPDATE statis_br SET orders = 0,sales = 0.0000 ,revenues = 0.0000,showrevenues = 0.0000,lastversion=0 WHERE createddate IN ('".join("','",$d)."') AND lastversion != '".$lastversion."'";
    }
    
    $_db->query($sql);
}

function update_visite_data($d,$lastversion){
    global $_db;
    $tmp_file = '/tmp/mysql/statis_finace_f.sql';
    if(file_exists($tmp_file)){
        unlink($tmp_file);
    }
    $sql = " SELECT VisitedDate as createddate,site,affid,programId,domainid,country,count(*) as orders,sum(Sales) as sales,sum(Commission) as revenues,sum(ShowCommission) as showrevenues  FROM rpt_transaction_unique WHERE VisitedDate IN ('".join("','",$d)."') AND af NOT IN ('bdg','mega','mk') GROUP BY VisitedDate,site,affid,programId,domainid,country into outfile '".$tmp_file."' fields terminated by '|' enclosed by '\"' lines terminated by '\r\n' ";
    // echo $sql."\n";
    $_db->query($sql);
    $fp = fopen($tmp_file,'r');

    $count = 0;
    $rows = array();
    while(!feof($fp)){
        $line = fgets($fp);
        $line = trim($line);

        if(empty($line)){
            continue;
        }
        $count++;
        $data = array();
        $data = explode('|',$line);
        
        $rows[] = array(
                'createddate'=>trim($data[0],'"'),
                'site'=>trim($data[1],'"'),
                'affid'=>trim($data[2],'"'),
                'programid'=>trim($data[3],'"'),
                'domainid'=>trim($data[4],'"'),
                'country'=>trim($data[5],'"'),
                'c_orders'=>trim($data[6],'"'),
                'c_sales'=>trim($data[7],'"'),
                'c_revenues'=>trim($data[8],'"'),
                'c_showrevenues'=>trim($data[9],'"'),
                'c_lastversion'=>$lastversion,
        );

        if(count($rows) > 99 ){
            $sql = getBatchUpdateSql($rows,'statis_br','createddate,site,affid,programid,domainid,country');
            $_db->query($sql);
            $rows = array();
        }
    }
    fclose($fp);

    if(count($rows) > 0 ){
        $sql = getBatchUpdateSql($rows,'statis_br','createddate,site,affid,programid,domainid,country');
        $_db->query($sql);
        $rows = array();
    }
    echo 'update statis_br in row : '.$count."\n";
}


function update_create_data($d,$lastversion){
    global $_db;
    $tmp_file = '/tmp/mysql/statis_finace.sql';
    if(file_exists($tmp_file)){
        unlink($tmp_file);
    }
    $sql = " SELECT CreatedDate as createddate,site,affid,programId,domainid,country,count(*) as orders,sum(Sales) as sales,sum(Commission) as revenues,sum(ShowCommission) as showrevenues  FROM rpt_transaction_unique WHERE CreatedDate IN ('".join("','",$d)."') AND af NOT IN ('bdg','mega','mk') GROUP BY CreatedDate,site,affid,programId,domainid,country into outfile '".$tmp_file."' fields terminated by '|' enclosed by '\"' lines terminated by '\r\n' ";
    // echo $sql."\n";
    $_db->query($sql);
    $fp = fopen($tmp_file,'r');

    $count = 0;
    $rows = array();
    while(!feof($fp)){
        $line = fgets($fp);
        $line = trim($line);

        if(empty($line)){
            continue;
        }
        $count++;
        $data = array();
        $data = explode('|',$line);
        
        $rows[] = array(
                'createddate'=>trim($data[0],'"'),
                'site'=>trim($data[1],'"'),
                'affid'=>trim($data[2],'"'),
                'programid'=>trim($data[3],'"'),
                'domainid'=>trim($data[4],'"'),
                'country'=>trim($data[5],'"'),
                'orders'=>trim($data[6],'"'),
                'sales'=>trim($data[7],'"'),
                'revenues'=>trim($data[8],'"'),
                'showrevenues'=>trim($data[9],'"'),
                'lastversion'=>$lastversion,
        );

        if(count($rows) > 99 ){
            $sql = getBatchUpdateSql($rows,'statis_br','createddate,site,affid,programid,domainid,country');
            $_db->query($sql);
            $rows = array();
        }
    }
    fclose($fp);

    if(count($rows) > 0 ){
        $sql = getBatchUpdateSql($rows,'statis_br','createddate,site,affid,programid,domainid,country');
        $_db->query($sql);
        $rows = array();
    }
    echo 'update statis_br in row : '.$count."\n";
}


function getBatchUpdateSql($updateData,$tableName,$primary='',$page_size=0){
    $column = array();
    $sql = '';
    $sqlArr = array();
    $valArr = array();

    if(!empty($updateData)){
        foreach($updateData[0] as $k=>$v){
            $column[] = $k;
        }

        $columnStr = join(',',$column);
        $sql_header = 'INSERT INTO '.$tableName.' ('.$columnStr.') VALUES ';

        $sql_footer = 'ON DUPLICATE KEY UPDATE ';

        $primaryArr = array();
        if(!empty($primary)){
            $primaryArr = explode(',',$primary);
        }
        foreach($column as $c){
            if(!in_array($c,$primaryArr))
                $sql_footer .= $c.'=VALUES('.$c.'),';
        }
        $sql_footer = substr($sql_footer,0,-1);


        foreach($updateData as $k=>$row){
            foreach($row as $key=>$value){
                $row[$key] = addslashes($value);
            }

            $valArr[] = '("'.join('","',$row).'")';
            if($page_size > 0 && count($valArr) >= $page_size){
                $sqlArr[] = $sql_header.' '.join(',',$valArr).' '.$sql_footer;
                $valArr = array();
            }
        }

        $sqlArr[] = $sql_header.' '.join(',',$valArr).' '.$sql_footer;
    }

    if( $page_size > 0 ){
        return $sqlArr;
    }else{
        return $sqlArr[0];
    }
}
?>
