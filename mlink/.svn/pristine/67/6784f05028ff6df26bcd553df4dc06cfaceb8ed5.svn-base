<?php
function over($str){
	echo $str."<br>\r\n";
	exit();
}

function debug($str){
	echo $str."<br>\r\n";
}

function parseArgv($argv){
	unset($argv[0]);
	$data = array();
	foreach($argv as $k=>$v){
		$v = trim($v);

		if(empty($v))
			continue;

		if(strpos($v,'=')!==false)
			list($key,$value) = explode('=',$v);
		else
			list($key,$value) = array($v,1);

		if($key[0] == '-')
			$key = substr($key,1);

		$data[$key] = $value;
	}
	return $data;
}

function getDir($dir,$only='',$last_name=false){
	if (empty($dir)) {
		return null;
	}

	$content = array();

	if(is_array($dir)){
		foreach($dir as $d){
			$tmp = getDir($d,$only,$last_name);
			$content = array_merge($content,$tmp);
		}
	}else{
		$ch = '';
		if(substr($dir,-1) != '/')
			$ch = '/';

		$dc = scandir($dir);
		foreach($dc as $k=>$v){
			if($v == '.' || $v == '..')
				continue;
			if($only=='dir' && is_dir($dir.$ch.$v)){
				$content[] = $last_name?$v:$dir.$ch.$v;
			}
			if($only=='file' && is_file($dir.$ch.$v)){
				$content[] = $last_name?$v:$dir.$ch.$v;
			}
		}
	}

	return $content;
}

function _array_column($input,$column_key,$index_key=null){
    if(empty($input)){
        return array();
    }

    if(!is_array($input)){
        return array();
    }

    $column_arr = array();
    $index_arr = array();
    foreach($input as $k=>$v){
        if(!empty($column_key) && isset($v[$column_key])){
            $column_arr[] = $v[$column_key];
        }
        
        if(!empty($index_key) && isset($v[$index_key])){
            $index_arr[] = $v[$index_key];
        }
    }
    
    if(!empty($index_key)){
        $output = array();
        foreach($index_arr as $k=>$v){
            $output[$v] = $column_arr[$k];
        }
        return $output;
    }else{
        return $column_arr;
    }
}
?>
