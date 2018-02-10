<?php
class appRoute extends route{
	var $api_list = array();
	var $api_obj = array();
	var $method = 'run';

	function __construct(){
		parent::__construct();
		$this->getApiList();
	}

	function doProgram(){
		$objProgram = $this->getProgram();
		call_user_func_array(array($objProgram,$this->method),array($this->param));
	}

	function getProgram(){
		if(!isset($this->param['act']) || !$this->param['act']){
			$this->guide('no_act');
		}elseif($this->param['act'] == 'help'){
			$this->guide('help');
		}else{
			if(strpos($this->param['act'],'.') !== false){
				list($act,$method) = explode('.',$this->param['act']);
				if(in_array($act,$this->api_list)){
					$api_obj = $this->loadApi($act);
					if($method){
						if(method_exists($api_obj, $method)){
							$this->method = $method;
						}else{
							$this->guide('no_method');
						}
					}
					return $api_obj;
				}else{
					$this->guide('no_act');
				}
			}else{
				$this->guide('no_act');
			}
		}
	}

	function guide($type){
		if($type == 'no_act'){
			$html = '';
			$html .= 'the api your input is not exist'."<br>\r\n";
			$html .= 'please use act=help to see api list'."<br>\r\n";
			echo $html;exit();
		}

		if($type == 'help'){
			if(isset($this->param['name'])){
				if(!in_array($this->param['name'],$this->api_list))
					$this->guide('no_act');

				$api_obj = $this->loadApi($this->param['name']);
				if(method_exists($api_obj,'info')){
					$info = $api_obj->info();
					$html = '';
					foreach($info as $k=>$v){
						$html .= 'Name : '.$v['name']."<br>\r\n";
						$html .= 'Desc : '.$v['desc']."<br>\r\n";
						$html .= 'Argv : '.$v['argv']."<br>\r\n";
						$html .= "====================================================<br>\r\n";
					}
				}else{
					$html = 'there is no more info about the api';
				}
				echo $html;exit();
			}else{
				$html = '';
				foreach($this->api_list as $k=>$v){
					$html .= ($k+1).'.'.$v."<br>\r\n";
				}

				$html .= 'you can add param name to see more info about the api'."<br>\r\n";
				$html .= 'look like app=api&act=help&name=[api_name]'."<br>\r\n";
				echo $html;exit();
			}
		}
	}

	function loadApi($api_name){

		if(isset($this->api_obj[$api_name]) && $this->api_obj[$api_name])
			return $this->api_obj[$api_name];
		else{
			include_once(API_ROOT.$api_name.'/api.'.$api_name.'.php');
			$api_obj = new $api_name;
			$this->api_obj[$api_name] = $api_obj;
			return $api_obj;
		}
	}

	function getApiList(){
		$api = array();

		if(defined('API_ROOT') && API_ROOT && is_dir(API_ROOT)){
			$files = scandir(API_ROOT);
			foreach($files as $k=>$v){
				if($v != '.' && $v != '..' && is_dir(API_ROOT.$v)){
					$api[] = $v;
				}
			}
		}else{
			over('API_ROOT has not defined.');
		}

		$this->api_list = $api;
		return $api;
	}
}

?>