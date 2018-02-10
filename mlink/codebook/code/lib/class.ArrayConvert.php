<?php
class ArrayConvert
{
	/*
	input array:
	$array = array(
		"employees" => array(
			"employee" => array(
				array(
					"name" => "name 1",
					"position" => "position 1"
				),
				array(
					"name" => "name 2",
					"position" => "position 2"
				),
				array(
					"name" => "name 3",
					"position" => "position 3"
				)
			)
		)
	);
	
	output xml:
	<employees>
		<employee>
			<name>name 1</name>
			<position>name 1</position>
		</employee>
		<employee>
			<name>name 2</name>
			<position>name 2</position>
		</employee>
		<employee>
			<name>name 3</name>
			<position>name 3</position>
		</employee>
	</employees>
	*/
	function array_to_xml(&$array_or_sting,$level=0)
	{
		if(!is_array($array_or_sting))
		{
			return htmlspecialchars($array_or_sting);
		}
		
		$xml = "";
		foreach($array_or_sting as $k => $v)
		{
			$is_xml_arr = (is_array($v) && isset($v[0])) ? true : false;
			if($is_xml_arr)
			{
				foreach($v as $_v)
				{
					$xml .= "<$k>" . $this->array_to_xml($_v,$level+1) . "</$k>";
				}
			}
			else
			{
				$xml .= "<$k>" . $this->array_to_xml($v,$level+1) . "</$k>";
			}
		}
		
		return $xml;
	}//
	
	function get_indent($n,$m=1,$string="\t")
	{
		if($n <= 0) return "";
		$res = "";
		for($i=0;$i<$n*$m;$i++)
		{
			$res .= $string;
		}
		return $res;
	}
	
	function get_tsv_from_array(&$arr,$prefix="",$field_sep="\t",$record_sep="\n")
	{
		if(!is_array($arr)) return $prefix . $this->replace_str_tsv($arr,$field_sep,$record_sep) . $record_sep;
		$content = "";
		foreach($arr as $v)
		{
			if(is_array($v))
			{
				foreach($v as $_k => $_v)
				{
					if(is_string($_v) || is_bool($_v) || is_numeric($_v))
					{
						$v[$_k] = $this->replace_str_tsv($_v,$field_sep,$record_sep);
					}
					else
					{
						$v[$_k] = "";
					}
				}
				$content .= $prefix . implode($field_sep,$v) . $record_sep;
			}
			elseif(is_string($v) || is_bool($v) || is_numeric($v))
			{
				$content .= $prefix . @trim($v) . $record_sep;
			}
			else
			{
				continue;
			}
		}
		return $content;
	}
	
	function replace_str_tsv($str,$field_sep="\t",$record_sep="\n")
	{
		return str_replace(array("\r",$field_sep,$record_sep)," ",$str);
	}
}
?>