<?php
class LibFactory
{
	public $objMysql;
	public $options = array();
	public $where;
	public $field='*';
	public $tableName;

	public $data;
	public $pk;
	public $returnfield = '';



	function __construct(){
		global $db;
		$this->objMysql = $db;
	}

	public function __call($method,$args){
		if(in_array(strtolower($method),array('table','where','order','limit','page','having','group','field'),true)) {
            // 连贯操作的实现
            $this->options[strtolower($method)] =   $args[0];
            return $this;
        }elseif(in_array(strtolower($method),array('count','sum','min','max','avg'),true)){
            // 统计查询的实现
            $field =  isset($args[0])?$args[0]:'*';
            $this->returnfield = 'tp_'.$method;
            return $this->getField(strtoupper($method).'('.$field.') AS tp_'.$method);
        }elseif(strtolower(substr($method,0,5))=='getby') {
            // 根据某个字段获取记录
            $field   =   parse_name(substr($method,5));
            $where[$field] =  $args[0];
            return $this->where($where)->find();
        }elseif(strtolower(substr($method,0,10))=='getfieldby') {
            // 根据某个字段获取记录的某个值
            $name   =   parse_name(substr($method,10));
            $where[$name] =$args[0];
            return $this->where($where)->getField($args[1])->find();
        }
	}

	function getField($column){
		$this->field = $column;
		return $this;
		return $this;
	}

	public function find(){
		$sql = $this->getSql();
		return $this->objMysql->getRows($sql);
	}

	public function findOne(){
		$sql = $this->getSql();
		$r = $this->objMysql->query($sql);
		$data = $this->objMysql->getRow($r);
		
		return $data;
	}

	public function findColumn(){
		$data = $this->findone();
		if($this->returnfield){
			$data = $data[$this->returnfield];
		}

		$this->returnfield = '';
		return $data;
	}

	public function getSql(){
		$tableName = '';
		$field = '';
		$where = '';
		$limit = '';
		$order = '';
		$group = '';

		if(!empty($this->options['table'])){
			$tableName = $this->options['table'];
		}else{
			$tableName = $this->tableName;
		}

		if(!empty($this->options['field'])){
			$field = $this->options['field'];
		}else{
			$field = $this->field;
		}

		if(!empty($this->options['where'])){
			$where = 'WHERE '.$this->options['where'];
		}else{
			$where = '';
		}

		if(!empty($this->options['limit'])){
			if(!empty($this->options['page'])){
				$limit = 'LIMIT '.($this->options['page']-1)*$this->options['limit'].','.$this->options['limit'];
			}else{
				$limit = 'LIMIT '.$this->options['limit'];
			}
		}else{
			$limit = '';
		}

		if(!empty($this->options['order'])){
			$order = 'ORDER BY '.$this->options['order'];
		}else{
			$order = '';
		}

		if(!empty($this->options['group'])){
			if(!empty($this->options['having'])){
				$group = 'GROUP BY '.$this->options['group'].' HAVING '.$this->options['having'];
			}else{
				$group = 'GROUP BY '.$this->options['group'];
			}
		}else{
			$group = '';
		}
        
		if(!empty($this->options['delete'])){
		    $sql = 'DELETE FROM '.$tableName.' '.$where.' '.$order.' ' .$group.' '.$limit;
		}else{
    		$sql = 'SELECT '.$field.' FROM '.$tableName.' '.$where.' '.$order.' ' .$group.' '.$limit;
		}
		
		$this->clearVar();
		return $sql;
	}

	function clearVar(){
		$this->options = array();
		$this->where = '';
		$this->field = '*';
	}
	
	
 /**
     +----------------------------------------------------------
     * 新增数据
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $data 数据
     * @param array $options 表达式
     * @param boolean $replace 是否replace
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function add($data='',$options=array(),$replace=false) {
        if(empty($data)) {
            // 没有传递数据，获取当前数据对象的值
            if(!empty($this->data)) {
                $data    =   $this->data;
                // 重置数据
                $this->data = array();
            }else{
                $this->error = '_DATA_TYPE_INVALID_';
                return false;
            }
        }
        // 分析表达式
        $options =  $this->_parseOptions($options);
        // 数据处理
//         $data = $this->_facade($data);
        // 写入数据到数据库
        $result = $this->insert($data,$options,$replace);
        if(false !== $result ) {
            $insertId   =   $this->objMysql -> getLastInsertId();
            if($insertId) {
                // 自增主键返回插入ID
                $data[$this->getPk()]  = $insertId;
//                 $this->_after_insert($data,$options);
                return $insertId;
            }
        }
        return $result;
    }
    

    /**
     +----------------------------------------------------------
     * 数据类型检测
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $data 数据
     * @param string $key 字段名
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function _parseType(&$data,$key) {
        $fieldType = strtolower($this->fields['_type'][$key]);
        if(false === strpos($fieldType,'bigint') && false !== strpos($fieldType,'int')) {
            $data[$key]   =  intval($data[$key]);
        }elseif(false !== strpos($fieldType,'float') || false !== strpos($fieldType,'double')){
            $data[$key]   =  floatval($data[$key]);
        }elseif(false !== strpos($fieldType,'bool')){
            $data[$key]   =  (bool)$data[$key];
        }
    }
    
    public function insert($data,$options=array(),$replace=false) {
        $values  =  $fields    = array();
//         $this->model  =   $options['model'];
        foreach ($data as $key=>$val){
//             $value   =  $this->parseValue($val);
            if(is_scalar($val)) { // 过滤非标量数据
                $val = addslashes($val);
                if ($val != 'null') {
                    $val = "'$val'";
                }
                $values[]   =  $val;
                $fields[]     =  '`'.$key.'`';
            }
        }
        $sql   =  ($replace?'REPLACE':'INSERT').' INTO '.$options['table'].' ('.implode(',', $fields).') VALUES ('.implode(',', $values).')';
//         $sql   .= $this->parseLock(isset($options['lock'])?$options['lock']:false);
// echo $sql;
       $this->objMysql -> query($sql);
       
       return  $this->objMysql -> getAffectedRows();
    }
    
    /**
     +----------------------------------------------------------
     * 分析表达式
     +----------------------------------------------------------
     * @access proteced
     +----------------------------------------------------------
     * @param array $options 表达式参数
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    protected function _parseOptions($options=array()) {
        if(is_array($options))
            $options =  array_merge($this->options,$options);
        // 查询过后清空sql表达式组装 避免影响下次查询
        $this->options  =   array();
        
        if (empty($options['table'])) {
            $options['table'] = $this->tableName;
        }
        if(!empty($options['alias'])) {
            $options['table']   .= ' '.$options['alias'];
        }
        // 记录操作的模型名称
//         $options['model'] =  $this->name;
        // 字段类型验证
//         if(C('DB_FIELDTYPE_CHECK')) {
            if(isset($options['where']) && is_array($options['where'])) {
                // 对数组查询条件进行字段类型检查
                foreach ($options['where'] as $key=>$val){
                    if(in_array($key,$this->fields,true) && is_scalar($val)){
                        $this->_parseType($options['where'],$key);
                    }
                }
            }
//         }
        // 表达式过滤
        return $options;
    }
    
    public function delete(){
        $this->options['delete'] = 'yes';
        
        $sql = $this->getSql();
        $this->objMysql->query($sql);
        
        $res =  $this->objMysql->getAffectedRows();
        return $res;
    }
    
    public function query($sql) {
        return $this->objMysql -> query($sql);
    }

    public function getRows($sql){
        return $this->objMysql->getRows($sql);
    }

    public function getRow($sql){
        $r = $this->objMysql->query($sql);
        return $this->objMysql->getRow($r);
    }
    
    /**
     +----------------------------------------------------------
     * 获取主键名称
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function getPk() {
        return isset($this->field['_pk'])?$this->field['_pk']:$this->pk;
    }
    
    /**
     +----------------------------------------------------------
     * 保存数据
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $data 数据
     * @param array $options 表达式
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
    public function save($data='',$options=array()) {
        if(empty($data)) {
            // 没有传递数据，获取当前数据对象的值
            if(!empty($this->data)) {
                $data    =   $this->data;
                // 重置数据
                $this->data = array();
            }else{
                $this->error = '_DATA_TYPE_INVALID_';
                return false;
            }
        }
        // 数据处理
//         $data = $this->_facade($data);
        // 分析表达式
        $options =  $this->_parseOptions($options);
//         if(false === $this->_before_update($data,$options)) {
//             return false;
//         }
        if(!isset($options['where']) ) {
            // 如果存在主键数据 则自动作为更新条件
            if(isset($data[$this->getPk()])) {
                $pk   =  $this->getPk();
                $where[$pk]   =  $data[$pk];
                $options['where']  =  $where;
                $pkValue = $data[$pk];
                unset($data[$pk]);
            }else{
                // 如果没有任何更新条件则不执行
                $this->error = '_OPERATION_WRONG_';
                return false;
            }
        }
        $result = $this->update($data,$options);
        if(false !== $result) {
            if(isset($pkValue)) $data[$pk]   =  $pkValue;
            $this->_after_update($data,$options);
        }
        return $result;
    }
    
    /**
     +----------------------------------------------------------
     * 更新记录
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $data 数据
     * @param array $options 表达式
     +----------------------------------------------------------
     * @return false | integer
     +----------------------------------------------------------
     */
    public function update($data,$options) {
//         $this->model  =   $options['model'];
        $sql   = 'UPDATE '
            .$options['table'] .' '
            .$this->parseSet($data).' '
            .$options['where'].' '
            .$options['order'].' '
            .$options['limit'];
//             .$this->parseLock(isset($options['lock'])?$options['lock']:false);
        return $this->objMysql -> query($sql);
    }
    
    /**
     +----------------------------------------------------------
     * set分析
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param array $data
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function parseSet($data) {
        foreach ($data as $key=>$val){
            $value   =  $this->parseValue($val);
            if(is_scalar($value)) // 过滤非标量数据
                $set[]    = $this->parseKey($key).'='.$value;
        }
        return ' SET '.implode(',',$set);
    }
    
    /**
     +----------------------------------------------------------
     * 字段名分析
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $key
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function parseKey(&$key) {
        return $key;
    }
    
    /**
     +----------------------------------------------------------
     * value分析
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $value
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function parseValue($value) {
        if(is_string($value)) {
            $value = '\''.$this->escapeString($value).'\'';
        }elseif(isset($value[0]) && is_string($value[0]) && strtolower($value[0]) == 'exp'){
            $value   =  $this->escapeString($value[1]);
        }elseif(is_array($value)) {
            $value   =  array_map(array($this, 'parseValue'),$value);
        }elseif(is_null($value)){
            $value   =  'null';
        }
        return $value;
    }
    
    /**
     +----------------------------------------------------------
     * SQL指令安全过滤
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $str  SQL字符串
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function escapeString($str) {
        return addslashes($str);
    }

    function getUpdateSql($updateData,$tableName,$whereStr=''){
        if(empty($updateData))
            return ;
        $sql = 'UPDATE '.$tableName.' SET ';
        foreach($updateData as $k=>$v){
            $sql .= '`'.addslashes($k).'` = "'.addslashes($v).'",';
        }
        $sql = substr($sql,0,-1);
        if($whereStr)
            $sql .= ' WHERE '.$whereStr;

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
        
        $this->clearVar();
        return $sql; 
    }

    function getInsertSql($updateData,$tableName,$replace=false){
        $column = array();
        $sql = '';
        if(!empty($updateData)){
            foreach($updateData[0] as $k=>$v){
                $column[] = $k;
            }

            $columnStr = join(',',$column);
            $sql = $replace?'REPLACE':'INSERT'.' INTO '.$tableName.' ('.$columnStr.') VALUES ';
            foreach($updateData as $k=>$row){
                foreach($row as $key=>$value){
                    $row[$key] = addslashes($value);
                }

                $sql .= '("'.join('","',$row).'"),';
            }
            $sql = substr($sql,0,-1);
        }
        
        $this->clearVar();
        return $sql; 
    }

    static function select_commission_used($commission){
        $usedCommission = '';
        $listCommssion = array();
        $CommissionUsed = '';
        $CommissionType = '';
        $str_head = '';
        $str_end = '';
        $hasIncentive = 0;
        $newCommissionTxt = '';

        $regex_number = get_regex('number');

        $num = count($commission);
        if($num < 1){

        }elseif($num == 1){
            $str = $commission[0];
            $len = strlen($str);
            if($len>2 && $str[$len-2] == '|'){
                $hasIncentive = $str[$len-1];
                $str = substr($str,0,-2);
            }

            preg_match($regex_number, $str, $m);
            if(ceil($m[2]) == 0){
                unset($commission[0]);
            }else{
                $listCommssion[] = $m[2];
                $commission[0] = $str;
                $str_head = $m[1];
                $str_end = $m[3];
            }
        }else{
             $hasIncentive = 1;
            foreach($commission as $k=>$v){
                $str = $v;
                $len = strlen($str);
                if($len>2 && $str[$len-2] == '|'){
                    $str = substr($str,0,-2);
                }

                preg_match($regex_number, $str, $m);
                if(ceil($m[2]) == 0){
                    unset($commission[$k]);
                    continue;
                }
                
            	 if(ceil($m[2]) >= 100 && $m[3] == "%"){
                    unset($commission[$k]);
                    continue;
                }

                if(empty($str_head) && empty($str_end)){
                    $str_head = $m[1];
                    $str_end = $m[3];
                }
                if($m[1] != $str_head || $m[3] != $str_end){
                    unset($commission[$k]);
                    continue;
                }
                $commission[$k] = $str;
                $listCommssion[] = $m[2];
            }
        }


        if(!empty($listCommssion)){
            if(count($listCommssion) > 1){
                $c = count($listCommssion);
                $all = '';
                foreach($listCommssion as $comis){
                    $comis = $comis.'';
                    $all = $all + $comis;
                    $all = $all.'';
                }

                $CommissionUsed = number_format($all/$c,2,'.','');
            }else{
                $CommissionUsed = $listCommssion[0];
            }
            $usedCommission = $str_head.$CommissionUsed.$str_end;

            if(strpos($usedCommission,'%') !== false || strpos($usedCommission,'&#37;') !== false)
                $CommissionType = 'percent';
            else
                $CommissionType = 'value';

            $newCommissionTxt = '['.join(',',$commission).']|'.$hasIncentive.'|'.$usedCommission;
        }

        $returnData = array();
        $returnData['CommissionUsed'] = $CommissionUsed;
        $returnData['CommissionValue'] = $newCommissionTxt;
        $returnData['CommissionIncentive'] = $hasIncentive;
        $returnData['CommissionType'] = $CommissionType;

        return $returnData;
    }


    static function getHttpRes($url = ''){

        $data = array();

        $finalUrl = '';
        $hasResponse = 0;
        $isLocation = 0;
        $is200 = 0;

        if($url){
            $url = str_replace("\n",'',$url);
            $url = str_replace("\r",'',$url);

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER , 1);
            curl_setopt($ch, CURLOPT_HEADER , 1);
            curl_setopt($ch, CURLOPT_NOBODY , 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION , 1);
            curl_setopt($ch, CURLOPT_TIMEOUT , 20);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36");
            curl_setopt($ch, CURLOPT_COOKIEJAR, '/home/outgoing/cookie/cooike');
            $response = curl_exec($ch);
            curl_close($ch);

            $response = trim($response);
            if(!empty($response))
                $hasResponse = 1;


            preg_match_all('/HTTP\/1.1\s+([^\s]+)\s.*/', $response, $n);
            if($n && isset($n[1]) && $n[1]){
                foreach($n[1] as $k=>$v){
                    $n[1][$k] = trim($v);
                }

                if(in_array('200',$n[1])){
                    $is200 = 1;
                }

                if(count($n[1]) > 1){
                    $isLocation = 1;
                }
            }

            if($isLocation){
                preg_match_all('/Location:(.*)/', $response,$m);

                if($m && isset($m[1]) && $m[1]){
                    foreach($m[1] as $k=>$v){
                        $m[1][$k] = trim($v);
                    }
                    $finalUrl = array_pop($m[1]);
                    $finalUrl = trim($finalUrl);
                }
            }
        }
        

        $data['finalUrl'] = $finalUrl;
        $data['hasResponse'] = $hasResponse;
        $data['isLocation'] = $isLocation;
        $data['is200'] = $is200;
        return $data;
    }
}
?>