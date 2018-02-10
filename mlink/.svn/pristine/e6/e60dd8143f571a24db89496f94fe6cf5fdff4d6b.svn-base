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

$d = $d_v;

#update prorgam statis
if(!empty($d)){
    #last version
    /*
    $lastversion = date('YmdHis');
    update_statis_program($d,$lastversion);
    update_statis_domain($d,$lastversion);
    update_statis_affiliate($d,$lastversion);
    clear_invalid_data($d,$lastversion);
    */
    #last version
    $lastversion = date('YmdHis');
    update_statis_program_br($d_c,'CreatedDate',$lastversion);
    update_statis_domain_br($d_c,'CreatedDate',$lastversion);
    update_statis_affiliate_br($d_c,'CreatedDate',$lastversion);
    clear_invalid_data_br($d_c,'CreatedDate',$lastversion);

    #last version
    $lastversion = date('YmdHis');
    update_statis_program_br($d_v,'VisitedDate',$lastversion);
    update_statis_domain_br($d_v,'VisitedDate',$lastversion);
    update_statis_affiliate_br($d_v,'VisitedDate',$lastversion);
    clear_invalid_data_br($d_v,'VisitedDate',$lastversion);
}


echo 'update finance end : '.date('Y-m-d H:i:s')."\n";

function clear_invalid_data($d,$lastversion){
    global $_db;
    $sql = "UPDATE statis_program   SET orders = 0,sales = 0.0000 ,revenues = 0.0000,showrevenues = 0.0000,lastversion=0 WHERE createddate IN ('".join("','",$d)."') AND lastversion != '".$lastversion."'";
    $_db->query($sql);
    $sql = "UPDATE statis_domain    SET orders = 0,sales = 0.0000 ,revenues = 0.0000,showrevenues = 0.0000,lastversion=0 WHERE createddate IN ('".join("','",$d)."') AND lastversion != '".$lastversion."'";
    $_db->query($sql);
    $sql = "UPDATE statis_affiliate SET orders = 0,sales = 0.0000 ,revenues = 0.0000,showrevenues = 0.0000,lastversion=0 WHERE createddate IN ('".join("','",$d)."') AND lastversion != '".$lastversion."'";
    $_db->query($sql);
}

function update_statis_program($d,$lastversion){
    global $_db;
    $tmp_file = '/tmp/mysql/statis_program_f.sql';
    if(file_exists($tmp_file)){
        unlink($tmp_file);
    }
    $sql = " SELECT VisitedDate as createddate,site,programId,count(*) as orders,sum(Sales) as sales,sum(Commission) as revenues,sum(ShowCommission) as showrevenues  FROM rpt_transaction_unique WHERE VisitedDate IN ('".join("','",$d)."') GROUP BY VisitedDate,site,programId into outfile '".$tmp_file."' fields terminated by '|' enclosed by '\"' lines terminated by '\r\n' ";
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
                'programId'=>trim($data[2],'"'),
                'orders'=>trim($data[3],'"'),
                'sales'=>trim($data[4],'"'),
                'revenues'=>trim($data[5],'"'),
                'showrevenues'=>trim($data[6],'"'),
                'lastversion'=>$lastversion,
        );

        if(count($rows) > 99 ){
            $sql = getBatchUpdateSql($rows,'statis_program','createddate, programId, site');
            $_db->query($sql);
            $rows = array();
        }
    }
    fclose($fp);

    if(count($rows) > 0 ){
        $sql = getBatchUpdateSql($rows,'statis_program','createddate, programId, site');
        $_db->query($sql);
        $rows = array();
    }

    echo 'update statis_program in row : '.$count."\n";
}


function update_statis_domain($d,$lastversion){
    global $_db;
    $tmp_file = '/tmp/mysql/statis_domain_f.sql';
    if(file_exists($tmp_file)){
        unlink($tmp_file);
    }
    $sql = " SELECT VisitedDate as createddate,site,domainId,count(*) as orders,sum(Sales) as sales,sum(Commission) as revenues,sum(ShowCommission) as showrevenues  FROM rpt_transaction_unique WHERE VisitedDate IN ('".join("','",$d)."') GROUP BY VisitedDate,site,domainId into outfile '".$tmp_file."' fields terminated by '|' enclosed by '\"' lines terminated by '\r\n' ";
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
                'domainId'=>trim($data[2],'"'),
                'orders'=>trim($data[3],'"'),
                'sales'=>trim($data[4],'"'),
                'revenues'=>trim($data[5],'"'),
                'showrevenues'=>trim($data[6],'"'),
                'lastversion'=>$lastversion,
        );

        if(count($rows) > 99 ){
            $sql = getBatchUpdateSql($rows,'statis_domain','createddate, domainId, site');
            $_db->query($sql);
            $rows = array();
        }
    }
    fclose($fp);

    if(count($rows) > 0 ){
        $sql = getBatchUpdateSql($rows,'statis_domain','createddate, domainId, site');
        $_db->query($sql);
        $rows = array();
    }

    echo 'update statis_domain in row : '.$count."\n";
}

function update_statis_affiliate($d,$lastversion){
    global $_db;
    $tmp_file = '/tmp/mysql/statis_affiliate_f.sql';
    if(file_exists($tmp_file)){
        unlink($tmp_file);
    }
    $sql = " SELECT VisitedDate as createddate,site,affId,count(*) as orders,sum(Sales) as sales,sum(Commission) as revenues,sum(ShowCommission) as showrevenues  FROM rpt_transaction_unique WHERE VisitedDate IN ('".join("','",$d)."') GROUP BY VisitedDate,site,affId into outfile '".$tmp_file."' fields terminated by '|' enclosed by '\"' lines terminated by '\r\n' ";
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
                'affId'=>trim($data[2],'"'),
                'orders'=>trim($data[3],'"'),
                'sales'=>trim($data[4],'"'),
                'revenues'=>trim($data[5],'"'),
                'showrevenues'=>trim($data[6],'"'),
                'lastversion'=>$lastversion,
        );

        if(count($rows) > 99 ){
            $sql = getBatchUpdateSql($rows,'statis_affiliate','createddate, affId, site');
            $_db->query($sql);
            $rows = array();
        }
    }
    fclose($fp);

    if(count($rows) > 0 ){
        $sql = getBatchUpdateSql($rows,'statis_affiliate','createddate, affId, site');
        $_db->query($sql);
        $rows = array();
    }

    echo 'update statis_affiliate in row : '.$count."\n";
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

function clear_invalid_data_br($d,$data_type,$lastversion){
    global $_db;
    $prefix = ($data_type == 'CreatedDate')?'':'c_';

    $sql = "UPDATE statis_program_br   SET ".$prefix."orders = 0,".$prefix."sales = 0.0000 ,".$prefix."revenues = 0.0000,".$prefix."showrevenues = 0.0000,".$prefix."lastversion=0 WHERE createddate IN ('".join("','",$d)."') AND ".$prefix."lastversion != '".$lastversion."'";
    $_db->query($sql);
    $sql = "UPDATE statis_domain_br    SET ".$prefix."orders = 0,".$prefix."sales = 0.0000 ,".$prefix."revenues = 0.0000,".$prefix."showrevenues = 0.0000,".$prefix."lastversion=0 WHERE createddate IN ('".join("','",$d)."') AND ".$prefix."lastversion != '".$lastversion."'";
    $_db->query($sql);
    $sql = "UPDATE statis_affiliate_br SET ".$prefix."orders = 0,".$prefix."sales = 0.0000 ,".$prefix."revenues = 0.0000,".$prefix."showrevenues = 0.0000,".$prefix."lastversion=0 WHERE createddate IN ('".join("','",$d)."') AND ".$prefix."lastversion != '".$lastversion."'";
    $_db->query($sql);
}

function update_statis_program_br($d,$data_type,$lastversion){
    global $_db;
    $prefix = ($data_type == 'CreatedDate')?'':'c_';

    $tmp_file = '/tmp/mysql/statis_program_b.sql';
    if(file_exists($tmp_file)){
        unlink($tmp_file);
    }
    $sql = " SELECT ".$data_type." as createddate,site,programId,count(*) as orders,sum(Sales) as sales,sum(Commission) as revenues,sum(ShowCommission) as showrevenues  FROM rpt_transaction_unique WHERE ".$data_type." IN ('".join("','",$d)."') AND af NOT IN ('bdg','mega','mk') GROUP BY ".$data_type.",site,programId into outfile '".$tmp_file."' fields terminated by '|' enclosed by '\"' lines terminated by '\r\n' ";
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
                'programId'=>trim($data[2],'"'),
                $prefix.'orders'=>trim($data[3],'"'),
                $prefix.'sales'=>trim($data[4],'"'),
                $prefix.'revenues'=>trim($data[5],'"'),
                $prefix.'showrevenues'=>trim($data[6],'"'),
                $prefix.'lastversion'=>$lastversion,
        );

        if(count($rows) > 99 ){
            $sql = getBatchUpdateSql($rows,'statis_program_br','createddate, programId, site');
            $_db->query($sql);
            $rows = array();
        }
    }
    fclose($fp);

    if(count($rows) > 0 ){
        $sql = getBatchUpdateSql($rows,'statis_program_br','createddate, programId, site');
        $_db->query($sql);
        $rows = array();
    }

    echo 'update statis_program_br in row : '.$count."\n";
}


function update_statis_domain_br($d,$data_type,$lastversion){
    global $_db;
    $prefix = ($data_type == 'CreatedDate')?'':'c_';

    $tmp_file = '/tmp/mysql/statis_domain_b.sql';
    if(file_exists($tmp_file)){
        unlink($tmp_file);
    }
    $sql = " SELECT ".$data_type." as createddate,site,domainId,count(*) as orders,sum(Sales) as sales,sum(Commission) as revenues,sum(ShowCommission) as showrevenues  FROM rpt_transaction_unique WHERE ".$data_type." IN ('".join("','",$d)."') AND af NOT IN ('bdg','mega','mk') GROUP BY ".$data_type.",site,domainId into outfile '".$tmp_file."' fields terminated by '|' enclosed by '\"' lines terminated by '\r\n' ";
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
                'domainId'=>trim($data[2],'"'),
                $prefix.'orders'=>trim($data[3],'"'),
                $prefix.'sales'=>trim($data[4],'"'),
                $prefix.'revenues'=>trim($data[5],'"'),
                $prefix.'showrevenues'=>trim($data[6],'"'),
                $prefix.'lastversion'=>$lastversion,
        );

        if(count($rows) > 99 ){
            $sql = getBatchUpdateSql($rows,'statis_domain_br','createddate, domainId, site');
            $_db->query($sql);
            $rows = array();
        }
    }

    if(count($rows) > 0 ){
        $sql = getBatchUpdateSql($rows,'statis_domain_br','createddate, domainId, site');
        $_db->query($sql);
        $rows = array();
    }

    echo 'update statis_domain_br in row : '.$count."\n";
}

function update_statis_affiliate_br($d,$data_type,$lastversion){
    global $_db;
    $prefix = ($data_type == 'CreatedDate')?'':'c_';

    $tmp_file = '/tmp/mysql/statis_affiliate_b.sql';
    if(file_exists($tmp_file)){
        unlink($tmp_file);
    }
    $sql = " SELECT ".$data_type." as createddate,site,affId,count(*) as orders,sum(Sales) as sales,sum(Commission) as revenues,sum(ShowCommission) as showrevenues  FROM rpt_transaction_unique WHERE ".$data_type." IN ('".join("','",$d)."') AND af NOT IN ('bdg','mega','mk') GROUP BY ".$data_type.",site,affId into outfile '".$tmp_file."' fields terminated by '|' enclosed by '\"' lines terminated by '\r\n' ";
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
                'affId'=>trim($data[2],'"'),
                $prefix.'orders'=>trim($data[3],'"'),
                $prefix.'sales'=>trim($data[4],'"'),
                $prefix.'revenues'=>trim($data[5],'"'),
                $prefix.'showrevenues'=>trim($data[6],'"'),
                $prefix.'lastversion'=>$lastversion,
        );

        if(count($rows) > 99 ){
            $sql = getBatchUpdateSql($rows,'statis_affiliate_br','createddate, affId, site');
            $_db->query($sql);
            $rows = array();
        }
    }
    fclose($fp);

    if(count($rows) > 0 ){
        $sql = getBatchUpdateSql($rows,'statis_affiliate_br','createddate, affId, site');
        $_db->query($sql);
        $rows = array();
    }

    echo 'update statis_affiliate_br in row : '.$count."\n";
}
?>
