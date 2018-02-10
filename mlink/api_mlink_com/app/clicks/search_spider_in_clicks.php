<?php
global $_cf,$_req,$_db;

$d = new DateTime($_req['startdate']);
while($d->format('Y-m-d') != $_req['enddate']){
    echo $d->format('Y-m-d')."\n";
    $cdate = $d->format('Y-m-d');
    
    $i = 0;
    do{
        $i++;
        $sql = "SELECT SessionId,CreatedDate,UserAgent FROM bd_out_tracking_for_spider WHERE CreatedDate = '".$cdate."' AND IsRobet = 'unknown' limit 1000";
        $rows = $_db->getRows($sql);
        
        if(!empty($rows)){
        $data = array();
        foreach($rows as $k=>$v){
            if(preg_match('/bot/i',$v['UserAgent']) || preg_match('/spider/i',$v['UserAgent'])){
                $data[] = array('SessionId'=>$v['SessionId'],'CreatedDate'=>$v['CreatedDate'],'IsRobet'=>'YES');
            }else{
                $data[] = array('SessionId'=>$v['SessionId'],'CreatedDate'=>$v['CreatedDate'],'IsRobet'=>'NO');
            }
        }

        $sql = getBatchUpdateSql($data,'bd_out_tracking_for_spider','SessionId,CreatedDate');
        $_db->query($sql);
        }
        print_r($i."\n");

    }while(!empty($rows));

    $sql = "SELECT IP,count(*) as c FROM bd_out_tracking_for_spider WHERE CreatedDate = '".$cdate."' AND IsRobet = 'NO' GROUP BY IP HAVING c > 50";
    $rows = $_db->getRows($sql);
    echo "potential nums : ".count($rows)."\n";
    if(!empty($rows)){
    $ips = array();
    foreach($rows as $k=>$v){
        $ips[] = $v['IP'];
    }
    $sql = "UPDATE bd_out_tracking_for_spider SET IsRobet='POTENTIAL',Mark = 'over 50 clicks in one day by same ip' WHERE CreatedDate = '".$cdate."' AND IP IN ('".join("','",$ips)."') AND IsRobet = 'NO'";
    $_db->query($sql);
    }

    $sql = "update bd_out_tracking_min AS a LEFT JOIN bd_out_tracking_for_spider AS b ON a.createddate = b.`CreatedDate` AND a.sessionid = b.`SessionId` set a.IsRobet = b.`IsRobet` WHERE a.createddate = '".$cdate."'";
    $_db->query($sql);

    $d->modify('+1 day');
}
exit();
print_r($ok);exit();


function getBatchUpdateSql($updateData,$tableName,$primary=''){
        $column = array();
        $sql = '';
        if(!empty($updateData)){
            foreach($updateData[0] as $k=>$v){
                $column[] = $k;
            }

            $columnStr = join(',',$column);
            $sql = 'INSERT INTO '.$tableName.' ('.$columnStr.') VALUES ';
            foreach($updateData as $k=>$row){
                foreach($row as $key=>$value){
                    $row[$key] = addslashes($value);
                }

                $sql .= '("'.join('","',$row).'"),';
            }
            $sql = substr($sql,0,-1);
            $sql .= 'ON DUPLICATE KEY UPDATE ';

            $primaryArr = array();
            if(!empty($primary)){
                $primaryArr = explode(',',$primary);
            }
            foreach($column as $c){
                if(!in_array($c,$primaryArr))
                    $sql .= $c.'=VALUES('.$c.'),';
            }
            $sql = substr($sql,0,-1);    
        }
        
        return $sql; 
    }

?>
