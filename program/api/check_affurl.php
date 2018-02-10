<?php
include_once(dirname(__FILE__) . "/etc/const.php");
$objMysql = New MysqlExt();
$objProgram = New Program();

$objMysql->query("SET NAMES utf8");

$domain = trim($_GET["domain"]);
$affurl = trim($_GET["affurl"]);
$debug = trim($_GET["debug"]);

if($domain){
	$return_val = 0;
	
	$domain_arr = array();
	$domain_arr = $objProgram->getDomainByHomepage($domain, "fi");
	
	if(count($domain_arr["domain"])){
		//$sql = "select b.affid from domain_outgoing_default a inner join program_intell b on a.pid = b.programid where a.`key` = '".addslashes(current($domain_arr["domain"]))."'";
		$sql = "SELECT c.affid FROM domain a INNER JOIN r_domain_program b ON a.id = b.did INNER JOIN program_intell c ON b.pid = c.programid WHERE c.isactive = 'active' AND b.status = 'active' AND a.domain = '".addslashes(current($domain_arr["domain"]))."' ";
		$tmp_arr = array();
		$tmp_arr = $objMysql->getRows($sql, "affid");
		
		$d_aff = array();
		if(count($tmp_arr)){
			$d_aff = array_keys($tmp_arr);

			$aff_arr = getAllAffiliateBDG($objMysql, $d_aff);
			$affiliateurlkeywords = array();
			foreach($aff_arr as $k => $v){
				if(!empty($v["AffiliateUrlKeywords"])){
					$tmp_keywords = $v["AffiliateUrlKeywords"];
				}elseif(!empty($v["AffiliateUrlKeywords2"])){
					$tmp_keywords = $v["AffiliateUrlKeywords2"];
				}else{
					$tmp_keywords = "";
				}
				if(!empty($tmp_keywords)){
					$tmp_arr = array();
					$tmp_arr = explode("\r\n", $tmp_keywords);
					foreach($tmp_arr as $vv){
						if(!empty($vv))$affiliateurlkeywords[$vv] = $vv;
					}
				}			
			}
			
			foreach($affiliateurlkeywords as $v){
				if(stripos($affurl, $v) !== false){
					$return_val = 1;
					break;
				}
			}
		}
		
		if($debug == 1){
			print_r($aff_arr);
			print_r($affiliateurlkeywords);
		}
	}	
	
	echo $return_val;
	exit;	
}
exit;
		

function getAllAffiliateBDG($objMysql, $id_arr = array()){
	$data = array();
	$id_list = "";
	if(count($id_arr)){
		foreach($id_arr as &$v) $v = intval($v);  
		$id_list = " AND ID IN ('" . implode("','", $id_arr) . "')";
	}
	$sql = "SELECT ID, Name, ShortName, AffiliateUrlKeywords, AffiliateUrlKeywords2 FROM wf_aff WHERE isactive = 'yes' $id_list";
	$data = $objMysql->getRows($sql, "ID");
	return $data;
}

?>