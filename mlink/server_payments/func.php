<?php

function getBatchInsertSql($updateData,$tableName,$replace=false,$ignore=false){
    $column = array();
    $sql = '';
    if(!empty($updateData)){
        foreach($updateData[0] as $k=>$v){
            $column[] = $k;
        }

        $columnStr = '`'.join('`,`',$column).'`';
        $ignore_str = $ignore?' IGNORE ':'';
        $sql = ($replace?'REPLACE':'INSERT').$ignore_str.' INTO '.$tableName.' ('.$columnStr.') VALUES ';
        foreach($updateData as $k=>$row){
            foreach($row as $key=>$value){
                $row[$key] = addslashes($value);
            }

            $sql .= '("'.join('","',$row).'"),';
        }
        $sql = substr($sql,0,-1);
    }
    
    return $sql; 
}

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

function echoMsg($msg,$act=null,$res=null){
    if($act){
        echo $act."\t";
    }

    if($res!==null){
        echo $res."\t";
    }

    echo $msg."\n";
}

function _array_column($input, $column_key, $index_key = null)
{
    if (empty($input)) {
        return array();
    }
    if (!is_array($input)) {
        return array();
    }
    $column_arr = array();
    $index_arr = array();
    foreach ($input as $k => $v) {
        if (!empty($column_key) && isset($v[$column_key])) {
            $column_arr[] = $v[$column_key];
        }
        if (!empty($index_key) && isset($v[$index_key])) {
            $index_arr[] = $v[$index_key];
        }
    }
    if (!empty($index_key)) {
        $output = array();
        foreach ($index_arr as $k => $v) {
            $output[$v] = $column_arr[$k];
        }

        return $output;
    } else {
        return $column_arr;
    }
}

?>
