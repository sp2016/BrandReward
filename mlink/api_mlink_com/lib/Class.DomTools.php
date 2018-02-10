<?php 
class DomTools{
	public $content = '';
	public $select = '';
	public $filter = array();
	public $target = array();
	public $element = array();

	function DomTools($content=null){
		if($content){
			$this->content = str_replace("\n","", $content);	
		}
	}

	function setContent($content=null){
		if($content){
			$this->content = str_replace("\n","", $content);	
		}
	}

	function flush(){
		$this->content = '';
		$this->select = '';
		$this->filter = array();
		$this->target = array();
		$this->element = array();
	}

	function select($string){
		$this->select = $string;
		$this->resolve($string);
		$this->target();
		$this->element();
	}

	function get(){
		return $this->element;
	}

	function resolve($string){
		$filterArr = array();

		$selectArr = explode(' ',$string);
		foreach($selectArr as $k=>$v){
			if(empty($v))
				continue;
			switch ($v[0]) {
				case '.':
					$filterArr['class'] = substr($v,1);
					break;
				case '#':
					$filterArr['id'] = substr($v,1);
					break;
				case '[':
					if(substr($v,-1) == ']'){
						$attrStr = substr($v,1,-1);
						list($key,$value) = explode('=',$attrStr);
						$key = trim($key);
						$value = trim($value);$value = trim($value,'"');$value = trim($value,"'");
						$filterArr[$key] = $value;
					}
					break;
				default:
					$filterArr['tag'][] = $v;
					break;
			}
		}

		if(!isset($filterArr['tag']) || empty($filterArr['tag'])){
			$regExpStr = $this::getRegExp($filterArr);

			if(preg_match_all($regExpStr, $this->content, $g)){
				if(isset($g[1]) && !empty($g[1])){
					$filterArr['tag'] = array();

					foreach($g[1] as $v){
						if(!in_array($v,$filterArr['tag']))
							$filterArr['tag'][] = $v;
					}
				}
			}
			
		}
		
		$this->filter = $filterArr;
	}

	static function getRegExp($filter){
		$htmlTagRegExp = '/<';

		if(isset($filter['tag']) && !empty($filter['tag'])){
			$htmlTagRegExp .= $filter['tag'];
			unset($filter['tag']);	
		}else{
			$htmlTagRegExp .= '(\w+)';
		}
		
		if(!empty($filter)){
			$htmlTagRegExp .= '(?:';
			foreach($filter as $k=>$v){
				$htmlTagRegExp .= '[^>]*\s'.$k.'=[\'"]'.$v.'[\'"]|';
			}
			$htmlTagRegExp = substr($htmlTagRegExp,0,-1);
			$htmlTagRegExp .= ')';
			$htmlTagRegExp .= '{'.count($filter).'}';	
		}
		
		$htmlTagRegExp .= '[^>]*>/i';

		return $htmlTagRegExp;
	}

	function target(){
		$target = array();

		$content = $this->content;

		if(isset($this->filter['tag']) && !empty($this->filter['tag'])){
			foreach($this->filter['tag'] as $loopTag){
				$filter = $this->filter;
				$filter['tag'] = $loopTag;

				$TagReg = '/(<'.$filter['tag'].'[^>]*>|<\/'.$filter['tag'].'>)/i';
				$htmlTagRegExp = $this::getRegExp($filter);

				$htmlTagStartRegExp = '/<'.$filter['tag'].'/';
				$htmlTagEndRegExp = '/<\/'.$filter['tag'].'/';

				$htmlTarget = array();
				$htmlTagRes = array();

				$grepMetaChar = array('\\','^','$','*','+','?','{','}','.','|','[',']','-','(',')','/');
				$grepMetaCharRe = array('\\\\','\^','\$','\*','\+','\?','\{','\}','\.','\|','\[','\]','\-','\(','\)','\/');

				if(preg_match_all($TagReg, $content, $g)){
					$matchArr = $g[1];

					//get start step by regexp
					foreach ($matchArr as $key => $value) {
						if (preg_match($htmlTagRegExp,$value)) {
							$htmlTarget[] = $key;
						}
					}

					$htmlMatchNum = count($matchArr);
					$htmlMatchMaxStep = $htmlMatchNum ;

					//get target content expect same tag
					foreach($htmlTarget as $k=>$v){
						$stackStep = array();	//element htmlTag step in the content
						$stackTag = array();	//element htmlTag in the content
						$stackNum = 0;		//if get start tag stackNum++ ,else get end tag stackNum--.
						$isCompleted = false;

						for ($i=$v; $i < $htmlMatchMaxStep; $i++) { 
							if(isset($matchArr[$i])){
								if(preg_match($htmlTagStartRegExp, $matchArr[$i])){
									$stackNum++;
									$stackStep[] = $i;
									$stackTag[] = $matchArr[$i];
								}
								if(preg_match($htmlTagEndRegExp, $matchArr[$i])){
									$stackNum--;
									$stackStep[] = $i;
									$stackTag[] = $matchArr[$i];

									if($stackNum < 1){
										//the tag has been completed. break the loop
										$htmlTagRes[$v]['step'] = $stackStep;
										$htmlTagRes[$v]['tag'] = $stackTag;
										break;
									}
								}

								if($stackNum < 0){
									break;
								}
								
							}
						}
					}

					//to prevent the repeat targetRegExp
					$targetRegExpArr = array();
					$targetRegExpNum = array();

					//get target content
					foreach($htmlTagRes as $k=>$v){
						$targetRegExp = '/';
						foreach($v['tag'] as $tag){
							// $targetRegExp .= $tag;
							$targetRegExp .= str_replace($grepMetaChar, $grepMetaCharRe, $tag);
							$targetRegExp .= '.*?';
						}
						$targetRegExp = substr($targetRegExp, 0,-3);
						$targetRegExp .= '/i';

						if(!in_array($targetRegExp,$targetRegExpArr)){
							preg_match($targetRegExp, $content,$matchTarget);
							$target[] = $matchTarget[0];

							$targetRegExpArr[] = $targetRegExp;
							$key = array_search( $targetRegExp, $targetRegExpArr);
							$targetRegExpNum[$key] = 1;
						}else{
							$key = array_search( $targetRegExp, $targetRegExpArr);
							$order = $targetRegExpNum[$key];

							preg_match_all($targetRegExp, $content,$matchTarget);
							$target[] = $matchTarget[0][$order];
							
							$targetRegExpNum[$key]++;
						}
					}
				}
			}
		}
		
		$this->target = $target;
	}

	function element(){
		$element = array();
		if(!empty($this->target)){
			foreach($this->target as $k=>$v){
				$data = array();
				preg_match('/(<(\w+)[^>]*>)(.*)(<\/\2>)/i', $v,$m);
				$data['Start'] = trim($m['1']);
				$data['End'] = trim($m['4']);
				$data['Content'] = trim($m['3']);
				
				$Attribute = array();
				$attrArr = explode(' ',substr($data['Start'],1,-1));
				unset($attrArr[0]);
				foreach($attrArr as $k=>$v){
					if(empty($v))
						continue;

					if(strpos($v, '=') === false){
						$Attribute[$v] = 1;
					}else{
						list($key,$value) = explode('=',$v);
						$key = trim($key);
						$value = trim($value);$value = trim($value,'"');$value = trim($value,"'");
						$Attribute[$key] = $value;
					}
				}
				$data['Attribute'] = $Attribute;
				$element[] = $data;
			}
		}

		$this->element = $element;
	}
}
?>