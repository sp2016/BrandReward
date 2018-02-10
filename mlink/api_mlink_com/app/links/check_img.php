<?php
global $_cf,$_req,$_db;
$_db->query('SET NAMES UTF8');


$img_url = DATA_ROOT.'/linksIMG/';
if(!is_dir($img_url)){
	mkdir($img_url);
}

#check img is download and update download status
$sql = 'SELECT ID,ImgUrl,ImgFile FROM c_content_feed WHERE Updated > "'.date('Y-m-d H:i:s',strtotime('-50 day')).'" ';
$rows = $_db->getRows($sql);
$check_res = array();

foreach($rows as $k=>$v){
	$ImgUrl = json_decode($v['ImgUrl'],true);
	$ImgFile = json_decode($v['ImgFile'],true);
	$flag = 'YES';	

	foreach($ImgUrl as $a=>$b){
		if(!isset($ImgFile[$a]) || !file_exists($img_url.$ImgFile[$a])){
			$flag = 'NO';
		}
	}

	$check_res[] = array('ID'=>$v['ID'],'ImgIsDownload'=>$flag);
}
$sql = getBatchUpdateSql($check_res,'c_content_feed','ID');
echo $sql."\n";
$_db->query($sql);

#get the rows that img has not been download
$sql = 'SELECT ID,ImgUrl,ImgFile FROM c_content_feed WHERE ImgIsDownload = "NO"';
$imgTask = $_db->getRows($sql);


#do download
foreach($imgTask as $k=>$v){
	$flag = 'YES';
	$ImgUrl = json_decode($v['ImgUrl'],true);
        $ImgFile = json_decode($v['ImgFile'],true);

	if(empty($ImgUrl))
		continue;


        foreach($ImgUrl as $a=>$b){
                if(!isset($ImgFile[$a]) || !file_exists($img_url.$ImgFile[$a])){
			$imgFile = $img_url.$ImgFile[$a];
			if(!is_dir(dirname($imgFile))){
				mkdir(dirname($imgFile));
			}
			if(!downloadImg($b,$imgFile)){
				$flag = 'NO';
			}
                }
        }
	
	echo 'ID:'.$v['ID'].' @ImgIsDownload:'.$flag."\n";
	if($flag = "YES"){
		$sql = 'UPDATE c_content_feed SET ImgIsDownload = "YES" WHERE ID = '.intval($v['ID']);
		$_db->query($sql);
	}

}


function downloadImg($url,$filename){
	echo 'download : '.$url."\n";
	$ch = curl_init(); 
        $fp = fopen($filename, 'wb'); 

        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_FILE, $fp); 
        curl_setopt($ch, CURLOPT_HEADER, 0); 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); 

        curl_exec($ch); 
        curl_close($ch); 
        fclose($fp); 

	if(file_exists($filename) && filesize($filename) > 0){
		return true;
	}else{
		return false;
	}
}


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
