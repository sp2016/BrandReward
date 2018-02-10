<?php
class APIServer
{
	public $oArrayConvert = null;
	public $version = "1.0.0";

	public function __construct()
	{
		$this->oArrayConvert = new ArrayConvert();
	}

	function get_result($ret_type,&$arr_result)
	{
		/*
			$arr = array(
				"resultcode" => $resultcode,
				"errorstring" => $errorstring,
				"results" => string/array,
				"errors" => string/array,
			);
		*/

		$ret_type = strtolower($ret_type);
		switch($ret_type)
		{
			case "tsv":
			case "epf":
				return $this->convert_result_to_tsv_or_epf($arr_result,$ret_type);
			case "xml":
				return $this->convert_result_to_xml($arr_result);
			case "json":
			default:
				return $this->convert_result_to_json($arr_result);
		}
	}
	
	function convert_result_to_json(&$arr_result)
	{
		if($this->is_api_client()) header('Content-Type: application/json; charset=utf-8');
		else header('Content-Type: text/html; charset=utf-8');
		return json_encode($arr_result);
	}
	
	function is_api_client()
	{
		if(isset($_SERVER["HTTP_USER_AGENT"]) && strpos($_SERVER["HTTP_USER_AGENT"],"APIClient") !== false) return true;
		return false;
	}
	
	function convert_result_to_tsv_or_epf(&$arr_result,$type)
	{
		if($type == "epf")
		{
			$field_sep = chr(1);
			$record_sep = chr(2) . "\n";
			header('Content-Type: text/html; charset=utf-8');
		}
		else
		{
			$field_sep = "\t";
			$record_sep = "\n";
			header('Content-Type: text/plain; charset=utf-8');
		}
		
		$content = "";
		$content .= $this->oArrayConvert->get_tsv_from_array($arr_result["resultcode"],"#resultcode:",$field_sep,$record_sep);
		$content .= $this->oArrayConvert->get_tsv_from_array($arr_result["errorstring"],"#errorstring:",$field_sep,$record_sep);
		$content .= $this->oArrayConvert->get_tsv_from_array($arr_result["errors"],"#errors:",$field_sep,$record_sep);
		if(is_array($arr_result["results"]))
		{
			$results = current($arr_result["results"]);
			if(is_array($results))
			{
				$first = current($results);
				if(is_array($first))
				{
					$arr_title = array(array_keys($first));
					$content .= $this->oArrayConvert->get_tsv_from_array($arr_title,"#title:",$field_sep,$record_sep);
				}
			}
			$content .= "#results:\n";
			foreach($arr_result["results"] as $results)
			{
				$content .= $this->oArrayConvert->get_tsv_from_array($results,"",$field_sep,$record_sep);
			}
		}
		else
		{
			$content .= "#results:\n";
			$content .= $this->oArrayConvert->get_tsv_from_array($arr_result["results"],"",$field_sep,$record_sep);
		}
		return $content;
	}
	
	function convert_result_to_xml(&$arr_result)
	{
		header('Content-Type: text/xml; charset=utf-8');
		$content = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$content .= "<body>" . "\n";
		
		if(isset($arr_result["resultcode"]))
		{
			$content .= "<resultcode>" . intval($arr_result["resultcode"]) . "</resultcode>" . "\n";
		}
		
		if(isset($arr_result["errorstring"]))
		{
			$content .= "<errorstring>" . htmlspecialchars($arr_result["errorstring"]) . "</errorstring>" . "\n";
		}
		
		if(isset($arr_result["results"]))
		{
			$content .= "<results>" . $this->oArrayConvert->array_to_xml($arr_result["results"]) . "</results>" . "\n";
		}
		
		if(isset($arr_result["errors"]))
		{
			$content .= "<errors>" . $this->oArrayConvert->array_to_xml($arr_result["errors"]) . "</errors>" . "\n";
		}
		$content .= "</body>";
		return $content;
	}
	
	function get_simple_error_result($ret_type,$str_error)
	{
		$arr_result = $this->get_result_array(500,$str_error,"","");
		return $this->get_result($ret_type,$arr_result);
	}
	
	function get_succ_result($ret_type,$results)
	{
		if(isset($results["str_error"])) unset($results["str_error"]);
		$arr_result = $this->get_result_array(200,"",$results,"");
		return $this->get_result($ret_type,$arr_result);
	}
	
	function get_result_array($resultcode="200",$errorstring="",$results="",$errors="")
	{
		return array(
			"resultcode" => $resultcode,
			"errorstring" => $errorstring,
			"results" => $results,
			"errors" => $errors,
		);
	}
}
?>