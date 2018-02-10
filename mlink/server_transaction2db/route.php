<?php
class route
{
	var $param = array();
	var $app = array();

	function __construct(){
		$param = array();

		if(defined('REWRITE_MODE') && REWRITE_MODE){
		}else{
			$param = $_GET;
		}

		global $argv;
		if(isset($argv) && count($argv) > 1){
		    $param = parseArgv($argv);
		}

		$this->param = $param;
		$this->app = $this->getApps();
	}

	function getApps(){
		$app = array();

		if(defined('APP_ROOT') && APP_ROOT && is_dir(APP_ROOT)){
			$files = scandir(APP_ROOT);
			foreach($files as $k=>$v){
				if($v != '.' && $v != '..' && is_dir(APP_ROOT.$v)){
					$app[] = $v;
				}
			}
		}else{
			over('APP_ROOT has not defined.');
		}

		return $app;
	}

	function getAppCore(){
		if(isset($this->param['app']) && $this->param['app']){
			$app = $this->param['app'];

			if(in_array($app,$this->app) && is_file(APP_ROOT.$app.'/core.php')){
				include_once(APP_ROOT.$app.'/core.php');
				$objApp = new core();
				return $objApp;
			}
		}
		over('App has not exist.');
	}
}

?>
