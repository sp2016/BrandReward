<?php
global $_cf,$_req,$_db;
$_db->query('SET NAMES UTF8');
if(!isset($_req['file']) || !file_exists(dirname(__FILE__).'/'.$_req['file'])){
	die('error: no file exist.');
}
$file = dirname(__FILE__).'/'.$_req['file'];

if(($fp = fopen($file,'r')) !== false){
	$k = 0;
 	$sql_data = array();
	while(($data = fgetcsv($fp,null,"\t")) !== false){
		$k++;
		if($k == 1)
			continue;
		if($k == 2)
			continue;

		if(empty($data[0]))
			continue;

		$tmp = array();	
		$tmp['TimeZone'] = 'Europe/Berlin';
		$tmp['Source'] = 'BCG';
		$tmp['SourceKey'] = $data[0];
		//$tmp['Title'] = addslashes(iconv('ISO-8859-1','UTF-8',$data[1]));
		$tmp['Title'] = addslashes($data[1]);
		$tmp['`Desc`'] = addslashes($data[2]);
		$tmp['StartTime'] = date('Y-m-d H:i:s',strtotime($data[6]));
		$tmp['ExpireTime'] = date('Y-m-d H:i:s',strtotime($data[7]));
		$tmp['`Code`'] = $data[8];
		$tmp['Url'] = $data[9];
		$tmp['Advertiser_Name'] = $data[12];
		$tmp['CreateTime'] = date('Y-m-d H:i:s');
		$tmp['Created'] = date('Y-m-d');
		$tmp['UpdateTime'] = date('Y-m-d H:i:s');
		$tmp['Updated'] = date('Y-m-d');
		$tmp['`Type`'] = empty($tmp['Code'])?'promotion':'coupon';

		$tmp['ImgUrl'] = json_encode(array('advertiser'=>$data[14]));
		$tmp['ImgFile'] = json_encode(array('advertiser'=>'/BDG/'.basename($data[14])));
		
		$sql_data[] = $tmp;
	}
}

//print_R($sql_data[0]);exit();
//var_dump(mb_detect_encoding($sql_data[200]['Title'],'ASSCII,GB2312,UTF-8,AUTO,latin1'));exit();


$sql = getBatchUpdateSql($sql_data,'c_content_feed','Source,SourceKey','CreateTime,Created');
$_db->query($sql);
die('update done');

function getBatchUpdateSql($updateData,$tableName,$primary='',$ignore='',$page_size=0){
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

        $ignoreArr = array();
        if(!empty($ignore)){
            $ignoreArr = explode(',',$ignore);
        }

        foreach($column as $c){
            if(!in_array($c,$primaryArr) && !in_array($c,$ignoreArr))
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
