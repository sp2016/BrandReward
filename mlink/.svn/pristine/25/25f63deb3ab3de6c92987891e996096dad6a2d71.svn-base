<?php
function load_all_static_url($_site="")
{
	global $g_static_url;
	global $site;
	if($_site == "") $_site = $site;
	$g_static_url = array();
	$sitesort = str_ireplace("cs", "", $_site);
	
	if($sitesort == "ca") $sitesort = "ys";
//	elseif($sitesort == "uk") $sitesort = "hd";
	elseif($sitesort == "au") $sitesort = "oz";
	
	if($sitesort == "ys") return;
	
	$file_last = FRONT_ROOT . "site_" . $sitesort . "/const/rewrite_mapping_last.txt";
	if($sitesort == "us"){
		$file_last = FRONT_ROOT_v4 . "site_" . $sitesort . "/const/rewrite_mapping_last.txt";
	}elseif($sitesort == "uk"){
		$file_last = FRONT_ROOT_UK_v4 . "site_" . $sitesort . "/const/rewrite_mapping_last.txt";
	}elseif($sitesort == "de"){
		$file_last = FRONT_ROOT_DE_V4 . "site_" . $sitesort . "/const/rewrite_mapping_last.txt";
	}

	$lines = @file($file_last);
	if(!is_array($lines)) return;
	foreach($lines as $line)
	{
		$line = rtrim($line);
		$fields = explode("\t",$line);
		if(sizeof($fields) != 2) continue;
		if(preg_match("/merchant\\.php\\?mid=([0-9]+)/",$fields[1],$matches))
		{
			$g_static_url["merchant"][$matches[1]] = $fields[0];
		}
		elseif(preg_match("/tag\\.php\\?tagid=([0-9]+)/",$fields[1],$matches))
		{
			$g_static_url["tag"][$matches[1]] = $fields[0];
		}
		elseif(preg_match("/category\\.php\\?cateid=([0-9]+)/",$fields[1],$matches))
		{
			$g_static_url["category"][$matches[1]] = $fields[0];
		}
	}
}

function get_urlname_type($urlname)
{
	$urlname_type = "";
	if(preg_match("/^\\/[a-zA-Z0-9\\-_\\.%+\\/]+\\.html$/",$urlname))
	{
		$urlname_type = "uri";
	}
	elseif(preg_match("/^[a-zA-Z0-9]+$/",$urlname))
	{
		$urlname_type = "urlname";
	}
	return $urlname_type;
}
?>