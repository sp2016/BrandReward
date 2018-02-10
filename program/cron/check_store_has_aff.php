<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
include_once(INCLUDE_ROOT . "func/func.php");

$site_arr = array();
$sid = "";
$is_debug = $fake = false;
if(isset($_SERVER["argc"]) && $_SERVER["argc"] > 1)
{
	foreach($_SERVER["argv"] as $v){
		$tmp = explode("=", $v);		
		if($tmp[0] == "--site"){			
			$site_arr = explode(",", $tmp[1]);
		}elseif($tmp[0] == "--debug"){
			$is_debug = true;
		}elseif($tmp[0] == "--sid"){
			$sid = " and a.id = " .intval($tmp[1]);
		}elseif($tmp[0] == "--fake"){
			$fake = " and c.isfake = '" .trim($tmp[1]) . "'";
		}
	}			
}


//echo "<< Start @ ".date("Y-m-d H:i:s")." >>\r\n";

$date = date("Y-m-d H:i:s");

$objProgram = New Program();

//$sql = "SELECT a.id, a.url FROM store a INNER JOIN store_merchant_relationship b ON a.id = b.storeid WHERE a.hasaffiliate = 'yes' ";

$i = 0;
foreach($site_arr as $site){
	echo "$site start________________________________________________________________________________\r\n";
	$sql = "SELECT a.id, a.url, b.sitename, c.programid, c.isfake  FROM store a INNER JOIN store_merchant_relationship b ON a.id = b.storeid INNER JOIN program_store_relationship c ON a.id = c.storeid inner join program d on c.programid = d.id
	 WHERE c.status = 'active' AND a.hasaffiliate = 'yes' AND b.sitename = '$site' and d.affid <> 191 $sid $fake";
	
	$store_list = $objProgram->objTaskMysql->getRows($sql, "id");
	
	foreach($store_list as $v){
		$domain_arr = array();
		$domain_arr = $objProgram->getDomainByHomepage($v["url"]);
		if($is_debug){
			print_r($domain_arr);
			exit;
		}
		if(is_array($domain_arr) && count($domain_arr)){
			$sql = "SELECT count(*) as cnt FROM domain a INNER JOIN r_domain_program b ON a.id = b.did and b.status = 'active' WHERE a.domain in ('".implode("','", $domain_arr)."')";
			$xx = $objProgram->objMysql->getFirstRow($sql);
			if(@$xx["cnt"] > 0){
				
			}else{
				$sql = "select count(*) as cnt from domain where domain in ('".implode("','", $domain_arr)."')";
				$xx = $objProgram->objMysql->getFirstRow($sql);
				
				if(@$xx["cnt"] > 0){
					$has_domain = 'yes';
				}else{
					$has_domain = 'no';
					
					$sql = "insert ignore into domain(domain) value('".implode("'),('", $domain_arr)."')";
					$objProgram->objMysql->query($sql);
					
				}
				
				$sql = "select domain from program_intell where programid = {$v["programid"]}";
				$p_domain = $objProgram->objMysql->getFirstRowColumn($sql);
				
				echo "{$v["id"]}\t{$v["url"]}\t".implode("'|'", $domain_arr)."\t";
				echo "fake:{$v["isfake"]}\t";
				echo "pid:{$v["programid"]}\t$p_domain\r\n";
				$i++;
			}
		}
	}
	
	
	echo "$site {$i} end________________________________________________________________________________\r\n";
}


exit;



?>