<?php
include_once(dirname(__FILE__) . "/const.php");

function clearup_file($rel_path,$name_pattern="",$exp_day=7,$dir_pattern="")
{
	//find /mezi/sem/semdata/ -path */temp/* -mtime +5 -delete
	//find /mezi/sem/semdata/ -name 'hourlyrevenue_*' -mtime +5 -delete
	if(!defined("INCLUDE_ROOT")) mydie("die: INCLUDE_ROOT not defined.");
	$rel_path = ltrim($rel_path,"/");
	$path = INCLUDE_ROOT . $rel_path;
	if(!is_dir($path)) return false;
	$cmd = "find $path";
	if($dir_pattern) $cmd .= " -path '$dir_pattern'";
	if($name_pattern) $cmd .= " -name '$name_pattern'";
	$cmd .= " -mtime +" . $exp_day . " -delete";
	echo $cmd . "\n";
	return system($cmd);
}

//1. for cache file
clearup_file("data/LinkFeed_7_SAS/","*.cache",5,"*/cache_*/*");
clearup_file("data","*.cache",5,"*/cache_*/*");
clearup_file("data","*.dat",5);
clearup_file("data","*.csv",5);
clearup_file("logs","*",5);
clearup_file("temp","*",5);

$objMysql = new MysqlExt();
for($i=0;$i<10;$i++)
{
	$sql = "delete from affiliate_links_change_log where `AddTime` < now() - interval 90 day limit 50000";
	$objMysql->query($sql);
	$affected = $objMysql->getAffectedRows();
	if($affected == 0) break;
	echo "$affected records deleted from affiliate_links_change_log\n";
	sleep(10);
}

print "<< Succ >>\n\n";
?>