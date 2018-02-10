<?php
class Affiliates extends LibFactory
{
    public function edit_aff($table_name, $edit, $where){//表名、传值、查询条件
	 	//----------------------------------------------找出经过edit之后，变化了的字段-------------------------------------------------	
        $query = 'SELECT * FROM ' . $table_name . ' WHERE ' . $where;
        $arr = $this->objMysql->getRows($query);
        $arr = $arr[0];
        $save_data = array();
        $pre_next = array();
        foreach ($arr as $k => $val) {//要想使用这段代码，传值的所有字段的name都必须和数据表中的字段名相同
            if (isset($edit[$k])) {
                if ($val !== $edit[$k]) {
                    $save_data[] = $k;//$save_data存储所有改变了值的字段名
                }
            }
        }

        
        //----------------------------------------------update  $table_name表----------------------------------------------------
        $condition = array();
        foreach($save_data as $val){
        	$condition[] = $val.'="'. $edit[$val].'"';
        	
        	
        }
        $condition = implode(",", $condition);//将数组转化成字符串，如：Name="Co12",ShortName="bbb22"
        $sql   = 'UPDATE '.$table_name.' SET '.$condition.' WHERE '.$where;
        mysql_query($sql);
        
        //------------------------------------------添加数据到表$batch_table----------------------------------------------

        $d = new DateTime();
        $timeNow = $d->format("Y-m-d H:i:s");
        //$_SERVER['PHP_AUTH_USER'] 当前用户名
        $BatchComments = "";
        if(isset($_SERVER['PHP_AUTH_USER'])){
        	$user = $_SERVER['PHP_AUTH_USER'];
        }else{
        	$user = 'test';
        }
        $query_batch = 'INSERT INTO table_change_log_batch (BatchPrimaryKeyValue,BatchOperator,BatchComments,BatchCreationTime,BatchAction,BatchTableName)
        		        VALUES ('.$edit['id'].',"'.$user.'","'.$BatchComments.'","'.$timeNow.'","EDIT","'.$table_name.'")';
        
        mysql_query($query_batch);
        $select = mysql_insert_id();//$select就是最后插入的id值了，mysql_insert_id()必须紧接在insert或者update之后执行
    
        //------------------------------------------添加数据到表$detail_table中--------------------------------------------------------
        
        foreach($save_data as $key){
        
 			
        	$query_detail = 'INSERT INTO table_change_log_detail (BatchId,FiledName,FiledValueFrom,FiledValueTo)
        				VALUES ("'.$select.'","'.$key.'","'.$arr[$key].'","'.$edit[$key].'")';
        	
        	mysql_query($query_detail);
        	
        }
        
     }
    

    
    
    
    
    
    
    
    
    
    
    
    
    
    public function add_aff($table_name, $add){
    	//--------------------------------------------找出经过add之后，增加了的字段-----------------------------------------------------------
    	$temp = array();
    	$keys = array();
    	$value = array();
    	foreach($add as $k => $val){
    		if(!empty($val)){
    			$temp[$k] = $val; //temp存储有添加值的键值对
    		}
    	}

    	
    	foreach($temp as $k => $val){
    		$keys[] = $k;
    		$value[] = '"'.$val.'"';
    	}
    	$keys_str = implode(",", $keys);
    	$value_str = implode(",", $value);
	
    	//add  $table_name表
    	$query = 'INSERT INTO ' . $table_name . '('.$keys_str.') VALUES ('.$value_str.')';
    
    	mysql_query($query);
    	
	
    	
    	
        //------------------------------------------添加数据到表$batch_table----------------------------------------------
    	    	   
        $d = new DateTime();
        $timeNow = $d->format("Y-m-d H:i:s");
        //$_SERVER['PHP_AUTH_USER'] 当前用户名
        $BatchComments = "";
        if(isset($_SERVER['PHP_AUTH_USER'])){
        	$user = $_SERVER['PHP_AUTH_USER'];
        }else{
        	$user = 'test';
        }
        $query_batch = 'INSERT INTO table_change_log_batch (BatchTableName,BatchOperator,BatchComments,BatchCreationTime,BatchAction,BatchPrimaryKeyValue) 
        		        VALUES ("'.$table_name.'","'.$user.'","'.$BatchComments.'","'.$timeNow.'","ADD","0")';
        
       
        mysql_query($query_batch);
        
        $select = mysql_insert_id();//$select就是最后插入的id值了，mysql_insert_id()必须紧接在insert或者update之后执行
                
    	
    	
    	
    	
    	
    	
    	
    	//------------------------------------------添加数据到表$detail_table中--------------------------------------------------------
        $FiledValueFrom = "";
        foreach($temp as $key => $val){

        
        	$query_detail = 'INSERT INTO table_change_log_detail (BatchId,FiledName,FiledValueFrom,FiledValueTo)
        				VALUES ("'.$select.'","'.$key.'","'.$FiledValueFrom.'","'.$val.'")';
        	 
        	mysql_query($query_detail);
        	 
        }
    	
    	
    }
    
    
    function getNetworklist($search){
        $where_arr = array();
        if(isset($search['IsActive']) && !empty($search['IsActive'])){
             $where_arr[] = 'IsActive = "'.addslashes(trim($search['IsActive'])).'"';
        }

        $where_str = empty($where_arr)?'':' WHERE '.join(' AND ',$where_arr);
        $pagesize = isset($search['pagesize'])?$search['pagesize']:10;
        if($pagesize <= 0){
            $limit_str = '';
        }else{
            $limit_str = ' LIMIT '.($page - 1)*$pagesize.','.$pagesize;    
        }
        $sql = 'SELECT * FROM wf_aff'.$where_str.$limit_str;
        $data = $this->getRows($sql);
        return $data;
    }
}