<?php
global $_cf,$_db,$_req;

echo 'update clicks start : '.date('Y-m-d H:i:s')."\n";

$where_str = '';

if( !isset($_req['datemonth']) ){
	$date = date('Y-m-d',strtotime("-3 day"));
	$where_str = 'WHERE createddate >= "'.$date.'"';
}else{
	$d = new DateTime($_req['datemonth']);
	$date_from  = $d->format('Y-m-01');
	$date_to = $d->modify('+1 month')->format('Y-m-01');

	$where_str = 'WHERE createddate >= "'.$date_from.'" AND createddate < "'.$date_to.'"';
}

echo 'doing data in : '.$where_str."\n";

#last version
$lastversion = date('YmdHis');

update_statis_program($where_str,$lastversion);
update_statis_domain($where_str,$lastversion);
update_statis_affiliate($where_str,$lastversion);
clear_invalid_data($where_str,$lastversion);

update_statis_program_br($where_str,$lastversion);
update_statis_domain_br($where_str,$lastversion);
update_statis_affiliate_br($where_str,$lastversion);
clear_invalid_data_br($where_str,$lastversion);

echo 'update clicks end : '.date('Y-m-d H:i:s')."\n";

function clear_invalid_data($where_str,$lastversion){
    global $_db;
    $sql = "UPDATE statis_program   SET clicks = 0,lastversion=0 ".$where_str." AND lastversion != '".$lastversion."'";
    $_db->query($sql);
    $sql = "UPDATE statis_domain    SET clicks = 0,lastversion=0 ".$where_str." AND lastversion != '".$lastversion."'";
    $_db->query($sql);
    $sql = "UPDATE statis_affiliate SET clicks = 0,lastversion=0 ".$where_str." AND lastversion != '".$lastversion."'";
    $_db->query($sql);
}

function update_statis_program($where_str,$lastversion){
    global $_db;
    $tmp_file = '/tmp/mysql/statis_program.sql';
    if(file_exists($tmp_file)){
        unlink($tmp_file);
    }

    //program clicks update
    $sql = "SELECT createddate,site,programId,COUNT(*) AS clicks FROM bd_out_tracking_min ".$where_str." GROUP BY createddate,site,programId into outfile '".$tmp_file."' fields terminated by '|' enclosed by '\"' lines terminated by '\r\n'";
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
                'clicks'=>trim($data[3],'"'),
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

function update_statis_domain($where_str,$lastversion){
    global $_db;
    $tmp_file = '/tmp/mysql/statis_domain.sql';
    if(file_exists($tmp_file)){
        unlink($tmp_file);
    }

    //program clicks update
    $sql = "SELECT createddate,site,domainId,COUNT(*) AS clicks FROM bd_out_tracking_min ".$where_str." GROUP BY createddate,site,domainId into outfile '".$tmp_file."' fields terminated by '|' enclosed by '\"' lines terminated by '\r\n'";
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
                'clicks'=>trim($data[3],'"'),
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



function update_statis_affiliate($where_str,$lastversion){
    global $_db;
    $tmp_file = '/tmp/mysql/statis_affiliate.sql';
    if(file_exists($tmp_file)){
        unlink($tmp_file);
    }

    //program clicks update
    $sql = "SELECT createddate,site,affId,COUNT(*) AS clicks FROM bd_out_tracking_min ".$where_str." GROUP BY createddate,site,affId into outfile '".$tmp_file."' fields terminated by '|' enclosed by '\"' lines terminated by '\r\n'";
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
                'clicks'=>trim($data[3],'"'),
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

function clear_invalid_data_br($where_str,$lastversion){
    global $_db;
    $sql = "UPDATE statis_program_br   SET clicks = 0,clicks_robot = 0,clicks_robot_p = 0,lastversion=0 ".$where_str." AND lastversion != '".$lastversion."'";
    $_db->query($sql);
    $sql = "UPDATE statis_domain_br    SET clicks = 0,clicks_robot = 0,clicks_robot_p = 0,lastversion=0 ".$where_str." AND lastversion != '".$lastversion."'";
    $_db->query($sql);
    $sql = "UPDATE statis_affiliate_br SET clicks = 0,clicks_robot = 0,clicks_robot_p = 0,lastversion=0 ".$where_str." AND lastversion != '".$lastversion."'";
    $_db->query($sql);
}

function update_statis_program_br($where_str,$lastversion){
    global $_db;
    $tmp_file = '/tmp/mysql/statis_program_br.sql';
    if(file_exists($tmp_file)){
        unlink($tmp_file);
    }

    //program clicks update
    $sql = "SELECT createddate,site,programId,COUNT(*) AS clicks,SUM(CASE WHEN isrobet = 'yes' THEN 1 ELSE 0 END) AS clicks_robot,SUM(CASE WHEN isrobet = 'potential' THEN 1 ELSE 0 END) AS clicks_robot_p FROM bd_out_tracking_min ".$where_str." GROUP BY createddate,site,programId into outfile '".$tmp_file."' fields terminated by '|' enclosed by '\"' lines terminated by '\r\n'";
    // $sql = "SELECT createddate,site,programId,COUNT(*) AS clicks FROM bd_out_tracking_min ".$where_str." GROUP BY createddate,site,programId into outfile '".$tmp_file."' fields terminated by '|' enclosed by '\"' lines terminated by '\r\n'";
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
                'clicks'=>trim($data[3],'"'),
                'clicks_robot'=>trim($data[4],'"'),
                'clicks_robot_p'=>trim($data[5],'"'),
                'lastversion'=>$lastversion,
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

function update_statis_domain_br($where_str,$lastversion){
    global $_db;
    $tmp_file = '/tmp/mysql/statis_domain_br.sql';
    if(file_exists($tmp_file)){
        unlink($tmp_file);
    }

    //program clicks update
    $sql = "SELECT createddate,site,domainId,COUNT(*) AS clicks,SUM(CASE WHEN isrobet = 'yes' THEN 1 ELSE 0 END) AS clicks_robot,SUM(CASE WHEN isrobet = 'potential' THEN 1 ELSE 0 END) AS clicks_robot_p FROM bd_out_tracking_min ".$where_str." GROUP BY createddate,site,domainId into outfile '".$tmp_file."' fields terminated by '|' enclosed by '\"' lines terminated by '\r\n'";
    // $sql = "SELECT createddate,site,domainId,COUNT(*) AS clicks FROM bd_out_tracking_min ".$where_str." GROUP BY createddate,site,domainId into outfile '".$tmp_file."' fields terminated by '|' enclosed by '\"' lines terminated by '\r\n'";
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
                'clicks'=>trim($data[3],'"'),
                'clicks_robot'=>trim($data[4],'"'),
                'clicks_robot_p'=>trim($data[5],'"'),
                'lastversion'=>$lastversion,
        );

        if(count($rows) > 99 ){
            $sql = getBatchUpdateSql($rows,'statis_domain_br','createddate, domainId, site');
            $_db->query($sql);
            $rows = array();
        }
    }
    fclose($fp);

    if(count($rows) > 0 ){
        $sql = getBatchUpdateSql($rows,'statis_domain_br','createddate, domainId, site');
        $_db->query($sql);
        $rows = array();
    }
    
    echo 'update statis_domain_br in row : '.$count."\n";
}



function update_statis_affiliate_br($where_str,$lastversion){
    global $_db;
    $tmp_file = '/tmp/mysql/statis_affiliate_br.sql';
    if(file_exists($tmp_file)){
        unlink($tmp_file);
    }

    //program clicks update
    $sql = "SELECT createddate,site,affId,COUNT(*) AS clicks,SUM(CASE WHEN isrobet = 'yes' THEN 1 ELSE 0 END) AS clicks_robot,SUM(CASE WHEN isrobet = 'potential' THEN 1 ELSE 0 END) AS clicks_robot_p FROM bd_out_tracking_min ".$where_str." GROUP BY createddate,site,affId into outfile '".$tmp_file."' fields terminated by '|' enclosed by '\"' lines terminated by '\r\n'";
    // $sql = "SELECT createddate,site,affId,COUNT(*) AS clicks FROM bd_out_tracking_min ".$where_str." GROUP BY createddate,site,affId into outfile '".$tmp_file."' fields terminated by '|' enclosed by '\"' lines terminated by '\r\n'";
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
                'clicks'=>trim($data[3],'"'),
                'clicks_robot'=>trim($data[4],'"'),
                'clicks_robot_p'=>trim($data[5],'"'),
                'lastversion'=>$lastversion,
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
