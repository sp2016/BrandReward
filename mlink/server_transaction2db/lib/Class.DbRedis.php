<?php
class DbRedis
{
	var $instance = null;

	function DbRedis(){
		$this->host = REDIS_DB_ADDR;
		$this->port = REDIS_DB_PORT;
		$this->_instance();
	}

	function _instance(){
		if($this->instance)
			return $this->instance;
		else{
			$objRedis = new Redis();
			$objRedis->pconnect('192.168.1.86',6379);
			$this->instance = $objRedis;
		}
	}

	function Keys($keySearch){
		$keys = array();
		if(empty($keySearch))
			return $keys;

		$redis = $this->instance;
		$keys = $redis->keys($keySearch);
		return $keys;
	}

	function Del($keys){
		if(empty($keys))
			return ;

		$redis = $this->instance;
		return $redis->del($keys);
	}

	function Rpop($keys){
		if(empty($keys))
			return ;

		$redis = $this->instance;
		return $redis->rpop($keys);
	}

	function Lpush($keys,$value){
		if(empty($keys))
			return ;

		$redis = $this->instance;
		return $redis->lPush($keys, $value);
	}

	function SetArr($key,$arr){
		$value = json_encode($arr);
		$redis = $this->instance;
		return $redis->set($key,$value);
	}

	function GetArr($key){
		$redis = $this->instance;
		$r = $redis->get($key);
		$arr = array();
		if($r)
			$arr = json_decode($r,true);
		return $arr;
	}
}
?>