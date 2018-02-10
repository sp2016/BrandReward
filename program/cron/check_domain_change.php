<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");

define("PROCESS_CNT", 5);

echo "<< Start @ ".date("Y-m-d H:i:s")." >>\r\n";

$did_arr = array();
$is_debug = $is_child = false;
$letter = "";
$checktime = date("Y-m-d");
if(isset($_SERVER["argc"]) && $_SERVER["argc"] > 1)
{
	foreach($_SERVER["argv"] as $v){
		$tmp = explode("=", $v);
		if($tmp[0] == "--child"){
			$is_child = true;
		}elseif($tmp[0] == "--letter"){
			$letter = $tmp[1];
		}elseif($tmp[0] == "--debug"){
			$is_debug = true;
		}elseif($tmp[0] == "--did"){
			$did_arr = explode(",", $tmp[1]);
		}elseif($tmp[0] == "--checktime"){
			$checktime = trim($tmp[1],"'");
		}
	}
}

if(!$is_child){	
	$process_name = __FILE__;
	killProcess($process_name);
	
	$letter_str = "abcdefghijklmnopqrstuvwxyz";		
	
	for($i=0;$i<=26;$i++){
		if($i == 26){
			$letter = "other";
		}else{
			$letter = substr($letter_str, $i, 1);
		}
		if($letter){
			echo "\t $letter start \r\n";
			while(1){
				if(checkProcess($process_name)){
					$cmd = "nohup php $process_name --letter=$letter --checktime='$checktime' --child >> /home/bdg/logs/domain/check_domain_{$letter}_{$checktime}.log 2>&1 &";
					echo "\t".$cmd."\r\n";
					system($cmd);			
					sleep(1);
					break;
				}else{
					//echo "\t\tsleep 30...";
					sleep(30);
				}
			}
		}
	}
}else{
	echo "\t<< child $letter start @ ".date("Y-m-d H:i:s")." >>\r\n";	
	
	$objProgram = New Program();
		
	$where_arr = array("1=1");
	$where_arr[] = "a.Existed = 'yes'";
	if($checktime) $where_arr[] = "(a.lastchecktime < '$checktime' or isnull(a.lastchecktime))";
	if($letter){
		if($letter != "other"){
			$where_arr[] = "a.domain like '".addslashes($letter)."%'";
		}else{
			$where_arr[] = "a.domain REGEXP '^[^a-zA-Z]+'";
		}
	}
	if(count($did_arr)) $where_arr[] = "a.id in ('".implode("','",  $did_arr)."')";
	
	$check_cnt = $add_union_cnt = 0;
	$i = 0;
	$limit = 100;
	
	echo $sql = "select count(*) from domain a where ".implode(" and ", $where_arr);
	$cnt =  $objProgram->objMysql->getFirstRowColumn($sql);
	echo "\r\n has $cnt \r\n";
	while(1)
	{	
		$sql = "select a.id, a.domain from domain a where ".implode(" and ", $where_arr)." order by a.id limit " . ($i * $limit) . ",  $limit";
	//echo	$sql = "select a.id, a.domain from domain a where a.id = 3204";
		$domain_arr = array();
		$domain_arr = $objProgram->objMysql->getRows($sql, "id");	
		
		if(!count($domain_arr))	break;
		$i++;
		if($i > 3000)
		{
			echo "while > " . $i * $limit ."\r\n";
			break;
		}
		
		foreach($domain_arr as $v)
		{
			$check_cnt++;
			//echo "\r\n".$v["domain"];
			if($v["domain"])
			{
				//$url = getUrlByNode("http://".$v["domain"]);
				
				$tmp_arr = array();
				$tmp_arr = $objProgram->getRealUrl("http://".$v["domain"]);
				if(!empty($tmp_arr["url"])){
					$url_info = array();
					$url_info = $objProgram->getDomainByHomepage($tmp_arr["url"], "fi");
					
					$country_code = current($url_info["country"]);
					if(strpos($v["domain"], "/") !== false && !empty($country_code)){
						$new_domain = current($url_info["domain"])."/".$country_code;
					}else{
						$new_domain = current($url_info["domain"]);
					}
					//print_r($v);
					//print_r($url_info);
					//print_r($tmp_arr);
					if(($tmp_arr["httpcode"] === 0 && $new_domain == $v["domain"]) || ($tmp_arr["httpcode"] == 200 && $new_domain != $v["domain"])){
						$sql = "update domain set Existed = 'NO', LastCheckTime = now() where id = ".$v["id"];
						$objProgram->objMysql->query($sql);
							
						echo $sql."\r\n";
						$add_union_cnt++;
						
						
					}
					//$tmp_arr["httpcode"] == 200 && 
				}
			}
		}
		
		$sql = "update domain set lastchecktime = '$checktime' where id in (".implode(",", array_keys($domain_arr)).")";
		$objProgram->objMysql->query($sql);	
	}

	echo "\tcheck $letter domain:$check_cnt / $cnt, domain changed:$add_union_cnt\r\n ";
	echo "\t<<child $letter end @ ".date("Y-m-d H:i:s")."\r\n>>";	
	exit;
}

echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";
exit;

function getUrlByNode($domain)
{
	$cmd = `node /app/nodejs/server_outgoing_nodejs/get_domain.js url $domain`;
	$return = ''.$cmd.'';
	return $return;
}

function checkProcess($process_name){
	$cmd = `ps aux | grep $process_name | grep 'child' -c`;
	$return = ''.$cmd.'';
	if($return > PROCESS_CNT){
		return false;
	}else{
		return true;
	}
}

function killProcess($process_name){
	$cmd = `ps ax | grep $process_name | grep 'child' | grep -v 'grep'`;
	$return = ''.$cmd.'';	
	$return = explode("\n", $return);
		
	foreach($return as $v){
		$yy = explode(" ", trim($v));
		//print_r($yy);		
		if(@intval($yy[0])){
			echo system("kill ".$yy[0]);
		}		
	}
}



?>