<?php
class APIClient
{
	public $base_url = "http://codebook.bwe.io/api/";
	public $token_salt = "";
	public $client_name = "";
	public $client_id = "";
	
	public $token_timestamp = 0;
	public $token = "";
	
	public $resultcode = 0;
	public $errorstring = "";
	public $errors = "";
	public $results = "";
	
	public function __construct()
	{
	}

	function get_error_info()
	{
		$str = "call api failed: ";
		$str .= $this->errorstring;
		$str .= "(" . $this->resultcode .")";
		if($this->errors) $str .= "\n" . var_export($this->errors,true);
		return $str;
	}

	public function call($method,$params=array())
	{
		$params["method"] = $method;
		$res = $this->_call($params);
		if($res)
		{
			if($this->resultcode == 200) return $this->results;
			else return false;
		}
		else return false;
	}

	public function get_methods()
	{
		return $this->call("GetMethods");
	}

	public function get_version()
	{
		return $this->call("GetVersion");
	}

	function _call($params=array(), $http_method="get")
	{
		$this->resultcode = 0;
		$this->errorstring = "";
		$this->errors = "";
		$this->results = "";

		if(!is_array($params)) $params = array();
		if(!isset($params['method'])) throw new Exception ("call method not found");
		
		$sys_params = array();
		$sys_params['method'] = $params['method'];
		$sys_params['client_id'] = $this->client_id;
		$sys_params['token'] = $this->generate_token($this->client_name, $this->token_salt);
		$sys_params['ret_type'] = "json";

		if(isset($params['method'])) unset($params['method']);
		if(isset($params['token'])) unset($params['token']);
		if(isset($params['client_id'])) unset($params['client_id']);
		if(isset($params['ret_type'])) unset($params['ret_type']);

		$p1 = http_build_query($sys_params);
		$p2 = http_build_query($params);
		$url = $this->base_url . "?" . $p1;

		if($http_method == "get" && strlen($url) + strlen($p2) < 1000)
		{
			if($p2) $url .= "&" . $p2;
		}
		else $http_method = "post";

		$content = $this->do_request($url,$http_method,$params);
		$r = @json_decode($content, true);

		if(is_array($r))
		{
			$this->resultcode = $r["resultcode"];
			$this->errorstring = $r["errorstring"];
			$this->errors = $r["errors"];
			$this->results = $r["results"];
			return true;
		}

		$this->results = $content;
		return false;
	}
	
	function generate_token($public_salt, $token_salt)
	{
		if(time() - $this->token_timestamp < 300 && $this->token) return $this->token;
		$this->token_timestamp = time();
		$oMcryptString = new McryptString();
		$encoded_info = $oMcryptString->encode_for_transfer($this->token_timestamp,$public_salt, $token_salt);
		$this->token = $encoded_info["EncodedString"];
		return $this->token;
	}
	
	function do_request($url,$http_method="get",$params=array())
	{
		if(defined("PAGE_DEBUG") && PAGE_DEBUG)
		{
			echo "do_request $http_method $url\n";
			if($http_method == "post") print_r($params);
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_NOBODY, false);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3600);
		curl_setopt($ch, CURLOPT_USERAGENT, "APIClient 1.0");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		//curl_setopt($ch, CURLOPT_ENCODING, "gzip, deflate");
		if($http_method == "post")
		{
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		}
		$r = @curl_exec($ch);
		if($r) $this->resultcode = curl_getinfo ($ch,CURLINFO_HTTP_CODE);
		return $r;
	}
}
?>