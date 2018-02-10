<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
include_once(INCLUDE_ROOT . "func/func.php");

$id_arr = array();
$is_debug = false;
$pid = "";
$is_quick = false;
if(isset($_SERVER["argc"]) && $_SERVER["argc"] > 1)
{
	foreach($_SERVER["argv"] as $v){
		$tmp = explode("=", $v);		
		if($tmp[0] == "--affid"){			
			$id_arr = explode(",", $tmp[1]);
		}elseif($tmp[0] == "--debug"){
			$is_debug = true;
		}elseif($tmp[0] == "--pid"){
			$pid = "a.PID = " .intval($tmp[1]);
		}elseif($tmp[0] == "--quick"){
			$is_quick = true;
		}
	}			
}


echo "<< Start @ ".date("Y-m-d H:i:s")." >>\r\n";

define("NO_MYSQL_CACHE", true);

$objProgram = New Program();

$objRedis = new Redis();
$objRedis->pconnect(REDIS_HOST, REDIS_PORT);


$update_time = date("Y-m-d H:i:s");

echo "size:".$objRedis->dbSize()."\t";
echo ":ACCOUNT::".count($objRedis->keys(":ACCOUNT:*"))."\t";
echo ":AFF::".count($objRedis->keys(":AFF:*"))."\t";
echo ":DOMAIN::".count($objRedis->keys(":DOMAIN:*"))."\r\n";
//echo ":DOMAIN::".$objRedis->sSize(":DOMAIN:*")."\r\n";


/*$time = date("Y-m-d");
$did_arr = $objProgram->getNeedCheckDomain("2015-04-01");
echo count($did_arr)."|\r\n";
$cnt = $objProgram->checkDomainProgramRel($did_arr);
echo $cnt."\r\n";*/

$where_arr = array("1=1");
if(count($id_arr)){
	$where_arr[] = "b.affid in (".implode(",", $id_arr).")";
}
if($pid){
	$where_arr[] = $pid;
}


/*$tmp_redis = array();
$tmp_redis = $objRedis->keys(":DOMAIN:*");
foreach($tmp_redis as $v){
	$objRedis->del($v);
}*/
$sql_quick = "";
if($is_quick){
	$sql_quick = " and a.LastUpdateTime > '".date("Y-m-d H:i:s", strtotime("-7 minutes"))."'";
}

$update_key = array();
$i = 0;
$j = 0;
$pos = 0;
$ss_ite = array("","au", "uk", "ca", "us", "de", "fr");

if(SID == 'bdg01'){
	if(count($where_arr)){
		$start = 0;
		while(1){
			$sql = "SELECT a.id, a.DID, a.PID, a.Key, b.IdInAff, b.AffId, b.CommissionUsed, b.CommissionType, b.CommissionIncentive, b.AffDefaultUrl as p_AffDefaultUrl, b.DeepUrlTpl as p_DeepUrlTpl, d.AffDefaultUrl, d.DeepUrlTpl, c.Domain, a.LimitAccount, c.SubDomain, a.IsFake, a.AffiliateDefaultUrl as fake_AffDefaultUrl, a.DeepUrlTemplate as fake_DeepUrlTpl, b.SupportDeepUrl 
					FROM domain_outgoing_default a left join program_intell b on a.pid = b.programid inner join domain c on c.id = a.did left join r_domain_program d on a.did = d.did and a.pid = d.pid
					where ". implode(" and ", $where_arr) ." and b.isactive = 'active' 
					$sql_quick AND a.id > $pos ORDER BY a.id LIMIT 1000";		
					//limit ". $start*1000 . ", 1000";
			//$sql = "SELECT a.DID, a.PID, a.Key, b.IdInAff, b.AffId, b.CommissionUsed, b.CommissionType, b.CommissionIncentive, b.AffDefaultUrl, b.DeepUrlTpl, c.Domain FROM domain_outgoing_default a left join program_intell b on a.pid = b.programid inner join domain c on c.id = a.did WHERE a.Key = 'affiliates.self-publishing-coach.com'";	
			if($is_debug){
				echo $sql."\r\n";
			}
			$tmp_arr = array();
			$tmp_arr = $objProgram->objMysql->getRows($sql);
			
			if(!count($tmp_arr)) break;
			if($start > 1000){
				break;
			}
			
			$start++;
			//echo count($tmp_arr);
			//exit;
			
			foreach($tmp_arr as $row)
			{	
				if($row["id"] > $pos) $pos = $row["id"];
							
				if($row["Key"]){
					//if($row["IsFake"] == "YES" || $row["SupportDeepUrl"] == "No"){
						if(!empty($row["fake_AffDefaultUrl"])){
							$row["AffDefaultUrl"] = $row["fake_AffDefaultUrl"];
							$row["DeepUrlTpl"] = $row["fake_DeepUrlTpl"];						
						}elseif(empty($row["AffDefaultUrl"])){
							$row["AffDefaultUrl"] = $row["p_AffDefaultUrl"];
							$row["DeepUrlTpl"] = $row["p_DeepUrlTpl"];
						}
						
						if($row["IsFake"] == "YES" && !empty($row["DeepUrlTpl"])){					
							$row["AffDefaultUrl"] = $row["DeepUrlTpl"];
						}
	
						$row["OutGoingUrl"] = strlen($row["DeepUrlTpl"]) ? $row["DeepUrlTpl"] : $row["AffDefaultUrl"];
						
					//}
					unset($row["p_AffDefaultUrl"]);
					unset($row["p_DeepUrlTpl"]);
					unset($row["fake_DeepUrlTpl"]);
					unset($row["fake_AffDefaultUrl"]);
					
					$row["LastUpdateTime"] = $update_time;
					
					if($row["PID"] == 137311){
						foreach($row as $k => $v_toutf8){
							$row[$k] = iconv('ISO-8859-1', 'UTF-8', $v_toutf8);
						}
					}
	
					/*
					 * att p:4019 	| att wireless p:2612
					 * dell p:1576 	| dell business p:1531
					 * hanes p:3500 | hanes champion p:3685 | hanes justmysize p:8751 | hanes onehanesplace p:8751
					 * journeys.com p: 42571 | journeys.com shi p:95327 
					 * nytimes.com p: 106294 | nytimes.com store p:168856
					 * nike.com hurley p: 86884
					 * microsoftstore.com MY p: 124610 |  microsoftstore.com PH p: 124611
					 * 
					 */
					switch ($row["PID"]){
						case 2612://"att.com" + "|wireless"
							$objRedis->set(":DOMAIN:"."att.com|wireless", json_encode($row));
							$update_key["att.com|wireless"] = "att.com|wireless";
							break;			
						case 4019://"att.com" + "|uverse"			
							$objRedis->set(":DOMAIN:"."att.com|uverse", json_encode($row));
							$update_key["att.com|uverse"] = "att.com|uverse";
							break;						
						case 1531:
							$objRedis->set(":DOMAIN:"."dell.com|business", json_encode($row));
							$update_key["dell.com|business"] = "dell.com|business";
							break;			
						case 3685:
							$objRedis->set(":DOMAIN:"."hanes.com|champion", json_encode($row));
							$update_key["hanes.com|champion"] = "hanes.com|champion";
							break;
						case 8751:
							$objRedis->set(":DOMAIN:"."hanes.com|justmysize", json_encode($row));
							$update_key["hanes.com|justmysize"] = "hanes.com|justmysize";
							break;
						case 95327:
							$objRedis->set(":DOMAIN:"."journeys.com|shi", json_encode($row));
							$update_key["journeys.com|shi"] = "journeys.com|shi";
							break;
						case 42571:
							$objRedis->set(":DOMAIN:"."journeys.com|normal", json_encode($row));
							$update_key["journeys.com|normal"] = "journeys.com|normal";
							break;
						case 168856:
							$objRedis->set(":DOMAIN:"."nytimes.com|store", json_encode($row));
							$update_key["nytimes.com|store"] = "nytimes.com|store";
							break;
						case 106294:
							$objRedis->set(":DOMAIN:"."nytimes.com|normal", json_encode($row));
							$update_key["nytimes.com|store"] = "nytimes.com|normal";
							break;
						case 86884:
							$objRedis->set(":DOMAIN:"."nike.com|hurley", json_encode($row));
							$update_key["nike.com|hurley"] = "nike.com|hurley";
							break;
							
						case 150539:
							$objRedis->set(":DOMAIN:"."nike.com|converse", json_encode($row));
							$update_key["nike.com|converse"] = "nike.com|converse";
							break;
						
						case 2183:
							$objRedis->set(":DOMAIN:"."aliexpress.com|Dorabeads", json_encode($row));
							$update_key["aliexpress.com|Dorabeads"] = "aliexpress.com|Dorabeads";
							break;
						case 167212:
							$objRedis->set(":DOMAIN:"."h20386.www2.hp.com|HongKong", json_encode($row));
							$update_key["h20386.www2.hp.com|HongKong"] = "h20386.www2.hp.com|HongKong";
							break;
							
						case 167220:
							$objRedis->set(":DOMAIN:"."h20386.www2.hp.com|Malaysia", json_encode($row));
							$update_key["h20386.www2.hp.com|Malaysia"] = "h20386.www2.hp.com|Malaysia";
							break;				
					
						case 124612:
							$tmp_key = "microsoftstore.com|mssg";
							$objRedis->set(":DOMAIN:".$tmp_key, json_encode($row));
							$update_key[$tmp_key] = $tmp_key;
							break;
						case 124609:
							$tmp_key = "microsoftstore.com|msapac";
							$objRedis->set(":DOMAIN:".$tmp_key, json_encode($row));
							$update_key[$tmp_key] = $tmp_key;
							break;
						case 124610:
							$tmp_key = "microsoftstore.com|MY";
							$objRedis->set(":DOMAIN:".$tmp_key, json_encode($row));
							$update_key[$tmp_key] = $tmp_key;
							break;
						case 124611:
							$tmp_key = "microsoftstore.com|PH";
							$objRedis->set(":DOMAIN:".$tmp_key, json_encode($row));
							$update_key[$tmp_key] = $tmp_key;
							break;
						case 85612:
							$tmp_key = "microsoftstore.com|msaus";
							$objRedis->set(":DOMAIN:".$tmp_key, json_encode($row));
							$update_key[$tmp_key] = $tmp_key;
							break;
						case 3071:
							$tmp_key = "microsoftstore.com|msca";
							$objRedis->set(":DOMAIN:".$tmp_key, json_encode($row));
							$update_key[$tmp_key] = $tmp_key;
							break;
						case 45168:
							$tmp_key = "microsoftstore.com|msde";
							$objRedis->set(":DOMAIN:".$tmp_key, json_encode($row));
							$update_key[$tmp_key] = $tmp_key;
							break;
						case 175316:
							$tmp_key = "microsoftstore.com|AT";
							$objRedis->set(":DOMAIN:".$tmp_key, json_encode($row));
							$update_key[$tmp_key] = $tmp_key;
							break;
						case 15733:
							$tmp_key = "microsoftstore.com|IE";
							$objRedis->set(":DOMAIN:".$tmp_key, json_encode($row));
							$update_key[$tmp_key] = $tmp_key;
							break;
						case 243961:
							$tmp_key = "microsoftstore.com|msfr";
							$objRedis->set(":DOMAIN:".$tmp_key, json_encode($row));
							$update_key[$tmp_key] = $tmp_key;
							break;
						case 185881:
							$tmp_key = "microsoftstore.com|msin";
							$objRedis->set(":DOMAIN:".$tmp_key, json_encode($row));
							$update_key[$tmp_key] = $tmp_key;
							break;
						case 85613:
							$tmp_key = "microsoftstore.com|msnz";
							$objRedis->set(":DOMAIN:".$tmp_key, json_encode($row));
							$update_key[$tmp_key] = $tmp_key;
							break;
						case 260444:
							$tmp_key = "microsoftstore.com|msuk";
							$objRedis->set(":DOMAIN:".$tmp_key, json_encode($row));
							$update_key[$tmp_key] = $tmp_key;
							break;
						case 8734:
							$tmp_key = "microsoftstore.com|msusa";
							$objRedis->set(":DOMAIN:".$tmp_key, json_encode($row));
							$update_key[$tmp_key] = $tmp_key;
							break;					
						default:
							break;
					}
					
					
					$objRedis->set(":DOMAIN:".$row["Key"], json_encode($row));
					$update_key[$row["Key"]] = $row["Key"];
					
					//print_r($row);exit;
					
					//echo "set :DOMAIN:".$row["Key"]." '".json_encode($row)."'\r\n";
					if($is_debug){
						/*print_r($row);
						echo "\r\n\r\n";
						$xx = $objRedis->get(":DOMAIN:".$row["Key"]);	
						print_r($xx);
						echo "\r\n";
						exit;*/
						
					}
					$i++;
					$j++;
				}
			}
		}
	}
	echo "o_db finish($j)\r\n";

	//for split
	$ss_ite = array("","au", "uk", "ca", "us", "de", "fr");
	$start = 0;
	$j = 0;
	$pos = 0;
	while(1){			
		$sql = "SELECT a.id, a.Site, a.DID, a.PID, a.Key, b.IdInAff, b.AffId, b.CommissionUsed, b.CommissionType, b.CommissionIncentive, b.AffDefaultUrl as p_AffDefaultUrl, b.DeepUrlTpl as p_DeepUrlTpl, d.AffDefaultUrl, d.DeepUrlTpl, b.OutGoingUrl, c.Domain, a.LimitAccount, c.SubDomain, a.IsFake, a.AffiliateDefaultUrl as fake_AffDefaultUrl, a.DeepUrlTemplate as fake_DeepUrlTpl, b.SupportDeepUrl 
				FROM domain_outgoing_default_site a left join program_intell b on a.pid = b.programid inner join domain c on c.id = a.did left join r_domain_program d on a.did = d.did and a.pid = d.pid 
				where ". implode(" and ", $where_arr) ." and b.isactive = 'active'";
		$sql .= " $sql_quick AND a.id > $pos ORDER BY a.id LIMIT 1000";
		//$sql .= "limit ". $start*1000 . ", 1000";
				//limit ". $start*1000 . ", 1000";
		//$sql = "SELECT a.DID, a.PID, a.Key, b.IdInAff, b.AffId, b.CommissionUsed, b.CommissionType, b.CommissionIncentive, b.AffDefaultUrl, b.DeepUrlTpl, c.Domain FROM domain_outgoing_default a left join program_intell b on a.pid = b.programid inner join domain c on c.id = a.did WHERE a.Key = 'affiliates.self-publishing-coach.com'";	
		if($is_debug){
			echo $sql."\r\n";
		}
		$tmp_arr = array();
		$tmp_arr = $objProgram->objMysql->getRows($sql);	
		
		if(!count($tmp_arr)) break;
		if($start > 1000){
			break;
		}	
		$start++;
	
		//print_r($p_arr);exit;
		foreach($tmp_arr as $row){
			if($row["id"] > $pos) $pos = $row["id"];
			
			if($row["Key"]){
				//if($row["IsFake"] == "YES" || $row["SupportDeepUrl"] == "No"){
					if(!empty($row["fake_AffDefaultUrl"])){
						$row["AffDefaultUrl"] = $row["fake_AffDefaultUrl"];
						$row["DeepUrlTpl"] = $row["fake_DeepUrlTpl"];						
					}elseif(empty($row["AffDefaultUrl"])){
						$row["AffDefaultUrl"] = $row["p_AffDefaultUrl"];
						$row["DeepUrlTpl"] = $row["p_DeepUrlTpl"];
					}
					
					if($row["IsFake"] == "YES" && !empty($row["DeepUrlTpl"])){					
						$row["AffDefaultUrl"] = $row["DeepUrlTpl"];
					}
					
					$row["OutGoingUrl"] = strlen($row["DeepUrlTpl"]) ? $row["DeepUrlTpl"] : $row["AffDefaultUrl"];
					
				//}
				unset($row["p_AffDefaultUrl"]);
				unset($row["p_DeepUrlTpl"]);
				unset($row["fake_DeepUrlTpl"]);
				unset($row["fake_AffDefaultUrl"]);
				
				$row["LastUpdateTime"] = $update_time;
				
				if($row["PID"] == 137311){
					foreach($row as $k => $v_toutf8){
						$row[$k] = iconv('ISO-8859-1', 'UTF-8', $v_toutf8);
					}
				}
				if(!empty($row["Site"])){				
					$row["Key"] = strtolower($row["Site"]).":".$row["Key"];
					$objRedis->set(":DOMAIN:".$row["Key"], json_encode($row));
					$update_key[$row["Key"]] = $row["Key"];
					
					foreach($ss_ite as $s_site){
						if(empty($s_site)) continue;
						if($row["PID"] == 2612){//"att.com" + "|wireless"
							$objRedis->set(":DOMAIN:".strtolower($s_site).":"."att.com|wireless", json_encode($row));
							$update_key[strtolower($s_site).":"."att.com|wireless"] = strtolower($s_site).":"."att.com|wireless";
						}				
						if($row["PID"] == 4019){//"att.com" + "|uverse"			
							$objRedis->set(":DOMAIN:".strtolower($s_site).":"."att.com|uverse", json_encode($row));
							$update_key[strtolower($s_site).":"."att.com|uverse"] = strtolower($s_site).":"."att.com|uverse";
						}
						
						if($row["PID"] == 1531){
							$objRedis->set(":DOMAIN:".strtolower($s_site).":"."dell.com|business", json_encode($row));
							$update_key[strtolower($s_site).":"."dell.com|business"] = strtolower($s_site).":"."dell.com|business";
						}				
						if($row["PID"] == 3685){		
							$objRedis->set(":DOMAIN:".strtolower($s_site).":"."hanes.com|champion", json_encode($row));
							$update_key[strtolower($s_site).":"."hanes.com|champion"] = strtolower($s_site).":"."hanes.com|champion";
						}
						if($row["PID"] == 8751){		
							$objRedis->set(":DOMAIN:".strtolower($s_site).":"."hanes.com|justmysize", json_encode($row));
							$update_key[strtolower($s_site).":"."hanes.com|justmysize"] = strtolower($s_site).":"."hanes.com|justmysize";
						}
						if($row["PID"] == 95327){		
							$objRedis->set(":DOMAIN:".strtolower($s_site).":"."journeys.com|shi", json_encode($row));
							$update_key[strtolower($s_site).":"."journeys.com|shi"] = strtolower($s_site).":"."journeys.com|shi";
						}
						if($row["PID"] == 42571){		
							$objRedis->set(":DOMAIN:".strtolower($s_site).":"."journeys.com|normal", json_encode($row));
							$update_key[strtolower($s_site).":"."journeys.com|normal"] = strtolower($s_site).":"."journeys.com|normal";
						}
						if($row["PID"] == 168856){		
							$objRedis->set(":DOMAIN:".strtolower($s_site).":"."nytimes.com|store", json_encode($row));
							$update_key[strtolower($s_site).":"."nytimes.com|store"] = strtolower($s_site).":"."nytimes.com|store";
						}				
						if($row["PID"] == 106294){		
							$objRedis->set(":DOMAIN:".strtolower($s_site).":"."nytimes.com|normal", json_encode($row));
							$update_key[strtolower($s_site).":"."nytimes.com|normal"] = strtolower($s_site).":"."nytimes.com|normal";
						}
						if($row["PID"] == 86884){		
							$objRedis->set(":DOMAIN:".strtolower($s_site).":"."nike.com|hurley", json_encode($row));
							$update_key[strtolower($s_site).":"."nike.com|hurley"] = strtolower($s_site).":"."nike.com|hurley";
						}
						if($row["PID"] == 2183){
							$objRedis->set(":DOMAIN:".strtolower($s_site).":"."aliexpress.com|Dorabeads", json_encode($row));
							$update_key[strtolower($s_site).":"."aliexpress.com|Dorabeads"] = strtolower($s_site).":"."aliexpress.com|Dorabeads";
						}
						if($row["PID"] == 167212){
							$objRedis->set(":DOMAIN:".strtolower($s_site).":"."h20386.www2.hp.com|HongKong", json_encode($row));
							$update_key[strtolower($s_site).":"."h20386.www2.hp.com|HongKong"] = strtolower($s_site).":"."h20386.www2.hp.com|HongKong";
						}
						if($row["PID"] == 167220){
							$objRedis->set(":DOMAIN:".strtolower($s_site).":"."h20386.www2.hp.com|Malaysia", json_encode($row));
							$update_key[strtolower($s_site).":"."h20386.www2.hp.com|Malaysia"] = strtolower($s_site).":"."h20386.www2.hp.com|Malaysia";
						}
						
						/*
						 * http://www.microsoftstore.com/mssg/en-SG/store/			p:	124612
						 * http://www.microsoftstore.com/store/msapac/en_GB/home/		124609
						 * http://www.microsoftstore.com/store/msapac/en_GB/home/ThemeID.31743200/Currency.MYR/mktp.MY	124610
						 * http://www.microsoftstore.com/store/msapac/en_GB/home/ThemeID.32253000/Currency.PHP/mktp.PH	124611
						 * http://www.microsoftstore.com/store/msaus/home				85612
						 * http://www.microsoftstore.com/store/msca/en_CA/home			3071
						 * http://www.microsoftstore.com/store/msde/de_DE/DisplayHomePage	45168
						 * http://www.microsoftstore.com/store/mseea/de_AT/home			175316
						 * http://www.microsoftstore.com/store/mseea/en_IE/home			15733
						 * http://www.microsoftstore.com/store/msfr/fr_FR/home			243961
						 * http://www.microsoftstore.com/store/msin/en_GB/home			185881
						 * http://www.microsoftstore.com/store/msnz/en_NZ/home/			85613
						 * http://www.microsoftstore.com/store/msuk/en_GB/DisplayHomePage	260444
						 * http://www.microsoftstore.com/store/msusa/en_US/DisplayHomePage	8734						 * 
						 */
						switch ($row["PID"]){
							case 124612:
								$tmp_key = "microsoftstore.com|mssg";
								$objRedis->set(":DOMAIN:".strtolower($s_site).":".$tmp_key, json_encode($row));
								$update_key[strtolower($s_site).":".$tmp_key] = strtolower($s_site).":".$tmp_key;
								break;
							case 124609:
								$tmp_key = "microsoftstore.com|msapac";
								$objRedis->set(":DOMAIN:".strtolower($s_site).":".$tmp_key, json_encode($row));
								$update_key[strtolower($s_site).":".$tmp_key] = strtolower($s_site).":".$tmp_key;
								break;
							case 124610:
								$tmp_key = "microsoftstore.com|MY";
								$objRedis->set(":DOMAIN:".strtolower($s_site).":".$tmp_key, json_encode($row));
								$update_key[strtolower($s_site).":".$tmp_key] = strtolower($s_site).":".$tmp_key;
								break;
							case 124611:
								$tmp_key = "microsoftstore.com|PH";
								$objRedis->set(":DOMAIN:".strtolower($s_site).":".$tmp_key, json_encode($row));
								$update_key[strtolower($s_site).":".$tmp_key] = strtolower($s_site).":".$tmp_key;
								break;
							case 85612:
								$tmp_key = "microsoftstore.com|msaus";
								$objRedis->set(":DOMAIN:".strtolower($s_site).":".$tmp_key, json_encode($row));
								$update_key[strtolower($s_site).":".$tmp_key] = strtolower($s_site).":".$tmp_key;
								break;
							case 3071:
								$tmp_key = "microsoftstore.com|msca";
								$objRedis->set(":DOMAIN:".strtolower($s_site).":".$tmp_key, json_encode($row));
								$update_key[strtolower($s_site).":".$tmp_key] = strtolower($s_site).":".$tmp_key;
								break;
							case 45168:
								$tmp_key = "microsoftstore.com|msde";
								$objRedis->set(":DOMAIN:".strtolower($s_site).":".$tmp_key, json_encode($row));
								$update_key[strtolower($s_site).":".$tmp_key] = strtolower($s_site).":".$tmp_key;
								break;
							case 175316:
								$tmp_key = "microsoftstore.com|AT";
								$objRedis->set(":DOMAIN:".strtolower($s_site).":".$tmp_key, json_encode($row));
								$update_key[strtolower($s_site).":".$tmp_key] = strtolower($s_site).":".$tmp_key;
								break;
							case 15733:
								$tmp_key = "microsoftstore.com|IE";
								$objRedis->set(":DOMAIN:".strtolower($s_site).":".$tmp_key, json_encode($row));
								$update_key[strtolower($s_site).":".$tmp_key] = strtolower($s_site).":".$tmp_key;
								break;
							case 243961:
								$tmp_key = "microsoftstore.com|msfr";
								$objRedis->set(":DOMAIN:".strtolower($s_site).":".$tmp_key, json_encode($row));
								$update_key[strtolower($s_site).":".$tmp_key] = strtolower($s_site).":".$tmp_key;
								break;
							case 185881:
								$tmp_key = "microsoftstore.com|msin";
								$objRedis->set(":DOMAIN:".strtolower($s_site).":".$tmp_key, json_encode($row));
								$update_key[strtolower($s_site).":".$tmp_key] = strtolower($s_site).":".$tmp_key;
								break;
							case 85613:
								$tmp_key = "microsoftstore.com|msnz";
								$objRedis->set(":DOMAIN:".strtolower($s_site).":".$tmp_key, json_encode($row));
								$update_key[strtolower($s_site).":".$tmp_key] = strtolower($s_site).":".$tmp_key;
								break;
							case 260444:
								$tmp_key = "microsoftstore.com|msuk";
								$objRedis->set(":DOMAIN:".strtolower($s_site).":".$tmp_key, json_encode($row));
								$update_key[strtolower($s_site).":".$tmp_key] = strtolower($s_site).":".$tmp_key;
								break;
							case 8734:
								$tmp_key = "microsoftstore.com|msusa";
								$objRedis->set(":DOMAIN:".strtolower($s_site).":".$tmp_key, json_encode($row));
								$update_key[strtolower($s_site).":".$tmp_key] = strtolower($s_site).":".$tmp_key;
								break;
	
							/*case 169085:
								if($row["Site"] == 'us'){								
									$row["AffDefaultUrl"] = 'http://prf.hn/click/camref:111l4Rh';
									$row["DeepUrlTpl"] = 'http://prf.hn/click/camref:111l4Rh/pubref:[SUBTRACKING]';
									$row["OutGoingUrl"] = 'http://prf.hn/click/camref:111l4Rh/pubref:[SUBTRACKING]';
									$objRedis->set(":DOMAIN:".$row["Key"], json_encode($row));								
								}elseif($row["Site"] == 'uk'){								
									$row["AffDefaultUrl"] = 'http://prf.hn/click/camref:110l6iA';
									$row["DeepUrlTpl"] = 'http://prf.hn/click/camref:110l6iA/pubref:[SUBTRACKING]';
									$row["OutGoingUrl"] = 'http://prf.hn/click/camref:110l6iA/pubref:[SUBTRACKING]';
									$objRedis->set(":DOMAIN:".$row["Key"], json_encode($row));								
								}
								break;*/
								
							case 290953:
								if($row["Site"] == 'fr'){								
									$row["AffDefaultUrl"] = 'http://click.linksynergy.com/fs-bin/click?id=oiPsV6mVqoA&offerid=417585.3&type=3&subid=0&u1=[SUBTRACKING]';							
									$objRedis->set(":DOMAIN:".$row["Key"], json_encode($row));								
								}elseif($row["Site"] == 'de'){								
									$row["AffDefaultUrl"] = 'http://click.linksynergy.com/fs-bin/click?id=oiPsV6mVqoA&offerid=417585.4&type=3&subid=0&u1=[SUBTRACKING]';								
									$objRedis->set(":DOMAIN:".$row["Key"], json_encode($row));								
								}
								
							/*case 172265:
								if($row["DID"] == 250012){
									if($row["Site"] == 'us'){								
										$row["AffDefaultUrl"] = 'http://www.anrdoezrs.net/click-2567387-12105491-1444244669000';
										$row["DeepUrlTpl"] = 'http://www.anrdoezrs.net/click-2567387-12102701-1444244669000?url=[DEEPURL]';
										$row["OutGoingUrl"] = 'http://www.anrdoezrs.net/click-2567387-12102701-1444244669000?url=[DEEPURL]';
										$objRedis->set(":DOMAIN:".$row["Key"], json_encode($row));								
									}elseif($row["Site"] == 'uk'){								
										$row["AffDefaultUrl"] = 'http://www.kqzyfj.com/click-2567387-12358755-1452783058000';
										$row["DeepUrlTpl"] = 'http://www.kqzyfj.com/click-2567387-12358755-1452783058000?url=[DEEPURL]';
										$row["OutGoingUrl"] = 'http://www.kqzyfj.com/click-2567387-12358755-1452783058000?url=[DEEPURL]';					
										$objRedis->set(":DOMAIN:".$row["Key"], json_encode($row));								
									}
								}
								break;
								
							case 296140:
								if($row["DID"] == 264079){
									if($row["Site"] == 'de'){								
										$row["AffDefaultUrl"] = 'http://www.jdoqocy.com/click-2567387-12560792-1465202517000';
										$row["DeepUrlTpl"] = 'http://www.jdoqocy.com/click-2567387-12560792-1465202517000?url=[DEEPURL]';
										$row["OutGoingUrl"] = 'http://www.jdoqocy.com/click-2567387-12560792-1465202517000?url=[DEEPURL]';
										$objRedis->set(":DOMAIN:".$row["Key"], json_encode($row));								
									}elseif($row["Site"] == 'fr'){								
										$row["AffDefaultUrl"] = 'http://www.anrdoezrs.net/click-2567387-12560793-1465202570000';
										$row["DeepUrlTpl"] = 'http://www.anrdoezrs.net/click-2567387-12560793-1465202570000?url=[DEEPURL]';
										$row["OutGoingUrl"] = 'http://www.anrdoezrs.net/click-2567387-12560793-1465202570000?url=[DEEPURL]';					
										$objRedis->set(":DOMAIN:".$row["Key"], json_encode($row));								
									}
								}
								break;
							
							case 401818:
								if($row["DID"] == 28220){
									if($row["Site"] == 'de'){								
										$row["AffDefaultUrl"] = 'http://www.jdoqocy.com/click-2567387-12737229-1480432270000';
										$row["DeepUrlTpl"] = 'http://www.dpbolvw.net/click-2567387-12737229-1480432270000?url=[DEEPURL]';
										$row["OutGoingUrl"] = 'http://www.dpbolvw.net/click-2567387-12737229-1480432270000?url=[DEEPURL]';
										$objRedis->set(":DOMAIN:".$row["Key"], json_encode($row));								
									}elseif($row["Site"] == 'fr'){								
										$row["AffDefaultUrl"] = 'http://www.anrdoezrs.net/click-2567387-12737232-1480431843000';
										$row["DeepUrlTpl"] = 'http://www.anrdoezrs.net/click-2567387-12737232-1480431843000?url=[DEEPURL]';
										$row["OutGoingUrl"] = 'http://www.anrdoezrs.net/click-2567387-12737232-1480431843000?url=[DEEPURL]';					
										$objRedis->set(":DOMAIN:".$row["Key"], json_encode($row));								
									}elseif($row["Site"] == 'it'){								
										$row["AffDefaultUrl"] = 'http://www.kqzyfj.com/click-2567387-12737234-1480432242000';
										$row["DeepUrlTpl"] = 'http://www.kqzyfj.com/click-2567387-12737234-1480432242000?url=[DEEPURL]';
										$row["OutGoingUrl"] = 'http://www.kqzyfj.com/click-2567387-12737234-1480432242000?url=[DEEPURL]';					
										$objRedis->set(":DOMAIN:".$row["Key"], json_encode($row));		
									}else{
										$row["AffDefaultUrl"] = 'http://www.jdoqocy.com/click-2567387-12737227-1480432672000';
										$row["DeepUrlTpl"] = 'http://www.jdoqocy.com/click-2567387-12737227-1480432672000?url=[DEEPURL]';
										$row["OutGoingUrl"] = 'http://www.jdoqocy.com/click-2567387-12737227-1480432672000?url=[DEEPURL]';					
										$objRedis->set(":DOMAIN:".$row["Key"], json_encode($row));									
									}
								}
								break;*/
								
							default:
								break;
						}
					}
					
					//print_r($row);exit;
					
					//echo "set :DOMAIN:".$row["Key"]." '".json_encode($row)."'\r\n";
					/*if($is_debug){
						print_r($row);
						echo "\r\n\r\n";
						$xx = $objRedis->get(":DOMAIN:".$row["Key"]);
						print_r($xx);
						echo "\r\n";
						exit;
						
					}*/
					$i++;
					$j++;
				}
			}
		}
	}
	
	echo "site_db finish($j)\r\n";
}





if (SID == 'bdg01') {
	//special domain/uri/
	$ss_ite = array("","au", "uk", "ca", "us", "de", "fr");
	foreach($ss_ite as $site){
		/*$sql = "SELECT a.Site, a.DID, a.PID, a.Key, b.IdInAff, b.AffId, b.CommissionUsed, b.CommissionType, b.CommissionIncentive, b.AffDefaultUrl, b.DeepUrlTpl, b.OutGoingUrl, c.Domain, a.LimitAccount, c.SubDomain, a.IsFake, a.AffiliateDefaultUrl as fake_AffDefaultUrl, a.DeepUrlTemplate as fake_DeepUrlTpl, b.SupportDeepUrl, b.domainspecial FROM domain_outgoing_default_site a left join program_intell b on a.pid = b.programid inner join domain c on c.id = a.did 
				where ". implode(" and ", $where_arr) ." and b.isactive = 'active' and b.domainspecial <> ''";
		$sql .= "AND a.id >= (SELECT id FROM domain_outgoing_default_site ORDER BY id LIMIT " . $start*1000 . " , 1) ORDER BY a.id LIMIT 1000";*/
		$db_name = "base_program_store_relationship";
		if($site) $db_name .= "_".$site;
		$sql = "SELECT b.programid as PID, b.AffId, b.AffiliateDefaultUrl as AffDefaultUrl, b.deepurltemplate as DeepUrlTpl, b.IsFake, b.domainname as Domain, b.domainspecial as `Key` FROM $db_name b inner join program_intell a on a.programid = b.programid WHERE a.isactive = 'active' and b.status = 'active' AND b.domainspecial <> ''";
		
		if($is_debug){
			echo $sql."\r\n";
		}
		$tmp_arr = array();
		$tmp_arr = $objProgram->objMysql->getRows($sql);
	
		//print_r($p_arr);exit;
		foreach($tmp_arr as $row){
			if($row["Key"]){
				$row["LastUpdateTime"] = $update_time;
				
				$row["OutGoingUrl"] = strlen($row["DeepUrlTpl"]) ? $row["DeepUrlTpl"] : $row["AffDefaultUrl"];
				
				if($row["PID"] == 137311){
					foreach($row as $k => $v_toutf8){
						$row[$k] = iconv('ISO-8859-1', 'UTF-8', $v_toutf8);
					}
				}
				if(!empty($site)){
					$row["Key"] = strtolower($site).":".$row["Key"];
				}	
							
				$objRedis->set(":DOMAIN:".$row["Key"], json_encode($row));
				$update_key[$row["Key"]] = $row["Key"];
				
				//print_r($row);exit;
				
				//echo "set :DOMAIN:".$row["Key"]." '".json_encode($row)."'\r\n";
				if($is_debug){
					print_r($row);
					echo "\r\n\r\n";
					$xx = $objRedis->get(":DOMAIN:".$row["Key"]);
					print_r($xx);
					echo "\r\n";
					exit;				
				}			
			}
		}
			
		$sql = "SELECT a.programid AS PID, a.AffId, b.AffDefaultUrl, b.DeepUrlTpl, b.IsFake, c.Domain
				FROM r_domain_program b INNER JOIN program_intell a ON a.programid = b.pid 
				INNER JOIN domain c ON b.did = c.id WHERE a.isactive = 'active' AND b.status = 'active' 
				AND c.domain NOT LIKE '%/%' AND b.pid IN (95327,163081,163030,245110,13586,13622,13623,13671,13624,44834,147964,94046,2612,4019,1531,3685,8751,95327,42571,168856,106294,86884,2183,167212,167220, 124612, 124609, 124610, 124611, 85612, 3071, 45168, 175316, 15733, 243961, 185881, 85613, 260444, 8734, 379601)";
		
		if($is_debug){
			echo $sql."\r\n";
		}
		$tmp_arr = array();
		$tmp_arr = $objProgram->objMysql->getRows($sql);
	
		//print_r($p_arr);exit;
		foreach($tmp_arr as $row){		
			$row["Key"] = $row["Domain"];
			$row["LastUpdateTime"] = $update_time;
			
			$row["OutGoingUrl"] = strlen($row["DeepUrlTpl"]) ? $row["DeepUrlTpl"] : $row["AffDefaultUrl"];
			
			if($row["PID"] == 137311){
				foreach($row as $k => $v_toutf8){
					$row[$k] = iconv('ISO-8859-1', 'UTF-8', $v_toutf8);
				}
			}
			if(!empty($site)){
				$row["Key"] = strtolower($site).":".$row["Key"];
			}	
			
			switch($row["PID"]){
				case 95327:
					$row["Key"] .= "|shi";
					break;			
				case 163081:
					$row["Key"] .= "|eciie";
					break;
				case 163030:
					$row["Key"] .= "|eciuk";
					break;
				case 245110:
					$row["Key"] .= "|tasting-club";
					break;
				case 13586:
					$row["Key"] .= "|car";
					break;
				case 13622:
					$row["Key"] .= "|home";
					break;
				case 13623:
					$row["Key"] .= "|pet";
					break;
				case 13671:
					$row["Key"] .= "|wedding";
					break;
				case 13624:
					$row["Key"] .= "|travel";
					break;
				case 44834:
					$row["Key"] .= "|eu";
					break;
				case 147964:
					$row["Key"] .= "|london";
					break;
				/*case 94046:
					$row["Key"] .= "|zoo";
					break;*/
								
				case 2612:
					$row["Key"] .= "|wireless";
					break;									
				case 4019:
					$row["Key"] .= "|uverse";
					break;
				case 1531:
					$row["Key"] .= "|business";
					break;			
				case 3685:
					$row["Key"] .= "|champion";
					break;
				case 8751:
					$row["Key"] .= "|justmysize";
					break;
				case 95327:
					$row["Key"] .= "|shi";
					break;
				case 42571:
					$row["Key"] .= "|normal";
					break;
				case 168856:
					$row["Key"] .= "|store";
					break;			
				case 106294:
					$row["Key"] .= "|normal";
					break;
				case 86884:
					$row["Key"] .= "|hurley";
					break;
				case 2183:
					$row["Key"] .= "|Dorabeads";
					break;
				case 167212:
					$row["Key"] .= "|HongKong";
					break;
				case 167220:
					$row["Key"] .= "|Malaysia";
					break;
						
				case 124612:
					$row["Key"] .= "|mssg";				
					break;
				case 124609:
					$row["Key"] .= "|msapac";				
					break;
				case 124610:
					$row["Key"] .= "|MY";				
					break;
				case 124611:
					$row["Key"] .= "|PH";				
					break;
				case 85612:
					$row["Key"] .= "|msaus";				
					break;
				case 3071:
					$row["Key"] .= "|msca";				
					break;
				case 45168:
					$row["Key"] .= "|msde";				
					break;
				case 175316:
					$row["Key"] .= "|AT";				
					break;
				case 15733:
					$row["Key"] .= "|IE";				
					break;
				case 243961:
					$row["Key"] .= "|msfr";				
					break;
				case 185881:
					$row["Key"] .= "|msin";				
					break;
				case 85613:
					$row["Key"] .= "|msnz";				
					break;
				case 260444:
					$row["Key"] .= "|msuk";				
					break;
				case 8734:
					$row["Key"] .= "|msusa";				
					break;
					
				case 379601://dell.com/uk/business
					if($site == 'uk'){
						$row["Key"] .= "|ukbusiness";
					}
					break;
				default:
					break;
			}
			
			if(strpos($row["Key"], "|") != false){
				$objRedis->set(":DOMAIN:".$row["Key"], json_encode($row));
				$update_key[$row["Key"]] = $row["Key"];
			}
			//print_r($row);exit;
			
			//echo "set :DOMAIN:".$row["Key"]." '".json_encode($row)."'\r\n";
			if($is_debug){
				print_r($row);
				echo "\r\n\r\n";
				$xx = $objRedis->get(":DOMAIN:".$row["Key"]);
				print_r($xx);
				echo "\r\n";
				exit;				
			}
		}
		
		
		//microsoftstore.com
		$sql = "SELECT b.pid as PID, a.AffId, b.AffDefaultUrl, b.DeepUrlTpl, b.IsFake, c.Domain
				FROM r_domain_program b inner join program_intell a on a.programid = b.pid inner join domain c on b.did = c.id WHERE a.isactive = 'active' and b.status = 'active' 
				AND b.pid = 260444 and b.did = 9730";
		$tmp_arr = array();
		$tmp_arr = $objProgram->objMysql->getRows($sql);
		foreach($tmp_arr as $row){
			$row["Key"] = !isset($row["Key"]) ? $row["Domain"] : $row["Key"];
			$row["LastUpdateTime"] = $update_time;
			
			$row["OutGoingUrl"] = strlen($row["DeepUrlTpl"]) ? $row["DeepUrlTpl"] : $row["AffDefaultUrl"];
					
			switch($row["PID"]){
				case 260444:
					$row["Key"] .= "|msuk";
					break;		
				default:
					break;
			}
			
			if(!empty($site)){
				$row["Key"] = strtolower($site).":".$row["Key"];
			}
			
			if(strpos($row["Key"], "|") != false){
				$objRedis->set(":DOMAIN:".$row["Key"], json_encode($row));
				$update_key[$row["Key"]] = $row["Key"];
			}		
		}
		
		//nike.com & store.nike.com
		if($site == 'us' || $site == 'ca'){
			$sql = "SELECT b.pid as PID, a.AffId, b.AffDefaultUrl, b.DeepUrlTpl, b.IsFake, c.Domain
					FROM r_domain_program b inner join program_intell a on a.programid = b.pid inner join domain c on b.did = c.id WHERE a.isactive = 'active' and b.status = 'active' 
					AND b.pid in (150539, 86884) and b.did = 8761";
			$tmp_arr = array();
			$tmp_arr = $objProgram->objMysql->getRows($sql);
			foreach($tmp_arr as $row){
				$row["Key"] = !isset($row["Key"]) ? $row["Domain"] : $row["Key"];
				$row["LastUpdateTime"] = $update_time;
				
				$row["OutGoingUrl"] = strlen($row["DeepUrlTpl"]) ? $row["DeepUrlTpl"] : $row["AffDefaultUrl"];
						
				switch($row["PID"]){
					case 150539:
						$row["Key"] .= "|converse";
						break;
					case 86884:
						$row["Key"] .= "|hurley";
						break;
					default:
						break;
				}
				
				if(!empty($site)){
					$row["Key"] = strtolower($site).":".$row["Key"];
				}
				
				if(strpos($row["Key"], "|") != false){
					$objRedis->set(":DOMAIN:".$row["Key"], json_encode($row));
					$update_key[$row["Key"]] = $row["Key"];
					
					$row["Key"] = str_replace('nike.com', 'store.nike.com', $row["Key"]);			
					$objRedis->set(":DOMAIN:".$row["Key"], json_encode($row));
					$update_key[$row["Key"]] = $row["Key"];
				}		
			}
		}
		
		//tesco.com wine|grocery|direct|clothing
		$sql = "SELECT b.pid as PID, a.AffId, b.AffDefaultUrl, b.DeepUrlTpl, b.IsFake, c.Domain
				FROM r_domain_program b inner join program_intell a on a.programid = b.pid inner join domain c on b.did = c.id WHERE a.isactive = 'active' and b.status = 'active' 
				AND b.pid in (313055, 316154, 261781, 313053) and b.did = 4513";	
		$tmp_arr = array();
		$tmp_arr = $objProgram->objMysql->getRows($sql);
		foreach($tmp_arr as $row){
			$row["Key"] = !isset($row["Key"]) ? $row["Domain"] : $row["Key"];
			$row["LastUpdateTime"] = $update_time;
			
			$row["OutGoingUrl"] = strlen($row["DeepUrlTpl"]) ? $row["DeepUrlTpl"] : $row["AffDefaultUrl"];
					
			switch($row["PID"]){
				case 261781:
					$row["Key"] .= "|groceries";
					break;
				case 316154:
					$row["Key"] .= "|wine";
					break;	
				case 313055:
					$row["Key"] .= "|direct";
					break;			
				case 313053:
					$row["Key"] .= "|clothing";
					break;	
				default:
					break;
			}
			
			if(!empty($site)){
				$row["Key"] = strtolower($site).":".$row["Key"];
			}
			
			if(strpos($row["Key"], "|") != false){
				$objRedis->set(":DOMAIN:".$row["Key"], json_encode($row));
				$update_key[$row["Key"]] = $row["Key"];
			}
	
			if($row["PID"] == 261781){			
				$row["Key"] = strtolower($site).":"."tesco.com|deliverysaver";
				$row["AffDefaultUrl"] = 'https://clkuk.tradedoubler.com/click?p=266911&a=1470197&g=22924910';
				$row["DeepUrlTpl"] = 'https://clkuk.tradedoubler.com/click?p=266911&a=1470197&g=22924910&url=[DEEPURL]';
				$row["OutGoingUrl"] = strlen($row["DeepUrlTpl"]) ? $row["DeepUrlTpl"] : $row["AffDefaultUrl"];
				$objRedis->set(":DOMAIN:".$row["Key"], json_encode($row));
				$update_key[$row["Key"]] = $row["Key"];
			}
		}
		
		//	zsl.org whipsnade London Zoo
		if($site == 'uk'){
			$sql = "SELECT b.pid as PID, a.AffId, b.AffDefaultUrl, b.DeepUrlTpl, b.IsFake, c.Domain
					FROM r_domain_program b inner join program_intell a on a.programid = b.pid inner join domain c on b.did = c.id WHERE a.isactive = 'active' and b.status = 'active' 
					AND b.pid in (13720, 94046) and b.did = 21286";
			$tmp_arr = array();
			$tmp_arr = $objProgram->objMysql->getRows($sql);
			foreach($tmp_arr as $row){
				$row["Key"] = !isset($row["Key"]) ? $row["Domain"] : $row["Key"];
				$row["LastUpdateTime"] = $update_time;
				
				$row["OutGoingUrl"] = strlen($row["DeepUrlTpl"]) ? $row["DeepUrlTpl"] : $row["AffDefaultUrl"];
						
				switch($row["PID"]){
					case 13720:
						$row["Key"] .= "|zoo";
						break;
					case 94046:
						$row["Key"] .= "|whipsnade";
						break;
					default:
						break;
				}
				
				if(!empty($site)){
					$row["Key"] = strtolower($site).":".$row["Key"];
				}
				
				if(strpos($row["Key"], "|") != false){
					$objRedis->set(":DOMAIN:".$row["Key"], json_encode($row));
					$update_key[$row["Key"]] = $row["Key"];				
				}		
			}
		}
	}
}

if (SID == 'bdg02') {
	//for content publisher can use all type program
	$start = 0;
	$j = 0;
	$pos = 0;
	while(1) {
		$sql = "select a.id, a.Site, a.DID, a.PID, a.Key, b.IdInAff, b.AffId, b.CommissionUsed, b.CommissionType, b.CommissionIncentive, b.AffDefaultUrl as p_AffDefaultUrl, b.DeepUrlTpl as p_DeepUrlTpl, d.AffDefaultUrl, d.DeepUrlTpl, b.OutGoingUrl, c.Domain, a.LimitAccount, c.SubDomain, a.IsFake, a.AffiliateDefaultUrl as fake_AffDefaultUrl, a.DeepUrlTemplate as fake_DeepUrlTpl, b.SupportDeepUrl
		from redirect_default a inner join program_intell b on a.pid = b.programid inner join domain c on c.id = a.did left join r_domain_program d on a.did = d.did and a.pid = d.pid
		where " . implode(" and ", $where_arr) . " and b.isactive = 'active' $sql_quick AND a.id > $pos ORDER BY a.id LIMIT 1000";
		if ($is_debug) {
			echo $sql . "\r\n";
		}
		$tmp_arr = array();
		$tmp_arr = $objProgram->objMysql->getRows($sql);
		if (!count($tmp_arr)) break;
		if ($start > 1000) {
			break;
		}
		$start++;
		
		//print_r($p_arr);exit;
		foreach ($tmp_arr as $row) {
			if ($row["id"] > $pos) $pos = $row["id"];
			
			if ($row["Key"]) {
				if (empty($row["AffDefaultUrl"])) {
					$row["AffDefaultUrl"] = $row["p_AffDefaultUrl"];
					$row["DeepUrlTpl"] = $row["p_DeepUrlTpl"];
				}
				
				if ($row["IsFake"] == "YES" && !empty($row["DeepUrlTpl"])) {
					$row["AffDefaultUrl"] = $row["DeepUrlTpl"];
				}
				
				$row["OutGoingUrl"] = strlen($row["DeepUrlTpl"]) ? $row["DeepUrlTpl"] : $row["AffDefaultUrl"];
				
				unset($row["p_AffDefaultUrl"]);
				unset($row["p_DeepUrlTpl"]);
				unset($row["fake_DeepUrlTpl"]);
				unset($row["fake_AffDefaultUrl"]);
				
				$row["LastUpdateTime"] = $update_time;
				
				if ($row["PID"] == 137311) {
					foreach ($row as $k => $v_toutf8) {
						$row[$k] = iconv('ISO-8859-1', 'UTF-8', $v_toutf8);
					}
				}
				
				
				if (!empty($row["Site"])) {
					if ($row["Site"] == 'global') {
						$row["Key"] = $row["Key"]. ":" ."CONT";
						$objRedis->set(":DOMAIN:" . $row["Key"], json_encode($row));
						$update_key[$row["Key"]] = $row["Key"];
					} else {
						$row["Key"] = strtolower($row["Site"]) . ":" . $row["Key"]. ":" ."CONT";
						$objRedis->set(":DOMAIN:" . $row["Key"], json_encode($row));
						$update_key[$row["Key"]] = $row["Key"];
					}
					$i++;
					$j++;
				}
			}
		}
	}
	echo "for content publisher finish($j)\r\n";
	
	
	//second choice
	$start = 0;
	$j = 0;
	$pos = 0;
	while(1) {
		$sql = "select a.DefaultOrder, a.SupportType, a.id, a.Site, a.DID, a.PID, a.Key, b.IdInAff, b.AffId, b.CommissionUsed, b.CommissionType, b.CommissionIncentive, b.AffDefaultUrl as p_AffDefaultUrl, b.DeepUrlTpl as p_DeepUrlTpl, d.AffDefaultUrl, d.DeepUrlTpl, b.OutGoingUrl, c.Domain, a.LimitAccount, c.SubDomain, a.IsFake, b.SupportDeepUrl
		from domain_outgoing_all a inner join program_intell b on a.pid = b.programid inner join domain c on c.id = a.did left join r_domain_program d on a.did = d.did and a.pid = d.pid
		where " . implode(" and ", $where_arr) . " and b.isactive = 'active' $sql_quick AND a.id > $pos ORDER BY a.id LIMIT 100";
		if ($is_debug) {
			echo $sql . "\r\n";
		}
		$tmp_arr = array();
		$tmp_arr = $objProgram->objMysql->getRows($sql);
		if (!count($tmp_arr)) break;
		if ($start > 10000) {
			break;
		}
		$start++;
		
		//print_r($p_arr);exit;
		foreach ($tmp_arr as $row) {
			if ($row["id"] > $pos) $pos = $row["id"];
			
			if ($row["Key"]) {
				if (empty($row["AffDefaultUrl"])) {
					$row["AffDefaultUrl"] = $row["p_AffDefaultUrl"];
					$row["DeepUrlTpl"] = $row["p_DeepUrlTpl"];
				}
				
				if ($row["IsFake"] == "YES" && !empty($row["DeepUrlTpl"])) {
					$row["AffDefaultUrl"] = $row["DeepUrlTpl"];
				}
				
				$row["OutGoingUrl"] = strlen($row["DeepUrlTpl"]) ? $row["DeepUrlTpl"] : $row["AffDefaultUrl"];
				
				unset($row["p_AffDefaultUrl"]);
				unset($row["p_DeepUrlTpl"]);
				unset($row["fake_DeepUrlTpl"]);
				unset($row["fake_AffDefaultUrl"]);
				
				$row["LastUpdateTime"] = $update_time;
				
				if ($row["PID"] == 137311) {
					foreach ($row as $k => $v_toutf8) {
						$row[$k] = iconv('ISO-8859-1', 'UTF-8', $v_toutf8);
					}
				}
				
				
				if (!empty($row["Site"]) && $row["DefaultOrder"] > 0) {
					if ($row["Site"] != 'global') {						
						$row["Key"] = strtolower($row["Site"]) . ":" . $row["Key"];
					}
					
					if ($row["SupportType"] == 'Content') {
						$row["Key"] = $row["Key"] . ":" . "CONT";
					}
					
					$row["Key"] = $row["Key"]. ":" . $row["DefaultOrder"];
					//echo $row["Key"]. "\r\n";
					$objRedis->set(":DOMAIN:" . $row["Key"], json_encode($row));
					$update_key[$row["Key"]] = $row["Key"];
					
					$i++;
					$j++;
				}
			}
		}
	}
	echo "second choice($j)\r\n";
}

//other site
$start = 0;
$j = 0;
$pos = 0;
while(1){
	if(SID == 'bdg02'){
		$sql = "SELECT a.id, a.Site, a.DID, a.PID, a.Key, b.IdInAff, b.AffId, b.CommissionUsed, b.CommissionType, b.CommissionIncentive, b.AffDefaultUrl as p_AffDefaultUrl, b.DeepUrlTpl as p_DeepUrlTpl, d.AffDefaultUrl, d.DeepUrlTpl, b.OutGoingUrl, c.Domain, a.LimitAccount, c.SubDomain, a.IsFake, a.AffiliateDefaultUrl as fake_AffDefaultUrl, a.DeepUrlTemplate as fake_DeepUrlTpl, b.SupportDeepUrl
			FROM domain_outgoing_default_other a inner join program_intell b on a.pid = b.programid inner join domain c on c.id = a.did left join r_domain_program d on a.did = d.did and a.pid = d.pid
			where ". implode(" and ", $where_arr) ." and b.isactive = 'active'";
	}else{
		$sql = "SELECT a.id, a.Site, a.DID, a.PID, a.Key, b.IdInAff, b.AffId, b.CommissionUsed, b.CommissionType, b.CommissionIncentive, b.AffDefaultUrl as p_AffDefaultUrl, b.DeepUrlTpl as p_DeepUrlTpl, d.AffDefaultUrl, d.DeepUrlTpl, b.OutGoingUrl, c.Domain, a.LimitAccount, c.SubDomain, a.IsFake, a.AffiliateDefaultUrl as fake_AffDefaultUrl, a.DeepUrlTemplate as fake_DeepUrlTpl, b.SupportDeepUrl
				FROM domain_outgoing_default_other a inner join program_intell b on a.pid = b.programid inner join domain c on c.id = a.did left join r_domain_program d on a.did = d.did and a.pid = d.pid
				where ". implode(" and ", $where_arr) ." and b.isactive = 'active' and a.site not in ('au', 'uk', 'ca', 'us', 'de', 'fr')";
	}
	$sql .= " $sql_quick AND a.id > $pos ORDER BY a.id LIMIT 1000";
	//$sql .= "limit ". $start*1000 . ", 1000";
			//limit ". $start*1000 . ", 1000";
	//$sql = "SELECT a.DID, a.PID, a.Key, b.IdInAff, b.AffId, b.CommissionUsed, b.CommissionType, b.CommissionIncentive, b.AffDefaultUrl, b.DeepUrlTpl, c.Domain FROM domain_outgoing_default a left join program_intell b on a.pid = b.programid inner join domain c on c.id = a.did WHERE a.Key = 'affiliates.self-publishing-coach.com'";
	if($is_debug){
		echo $sql."\r\n";
	}
	$tmp_arr = array();
	$tmp_arr = $objProgram->objMysql->getRows($sql);
	if(!count($tmp_arr)) break;
	if($start > 1000){
		break;
	}
	$start++;

	//print_r($p_arr);exit;
	foreach($tmp_arr as $row){
		if($row["id"] > $pos) $pos = $row["id"];
		
		if($row["Key"]){
			if(empty($row["AffDefaultUrl"])){
				$row["AffDefaultUrl"] = $row["p_AffDefaultUrl"];
				$row["DeepUrlTpl"] = $row["p_DeepUrlTpl"];
			}
			
			if($row["IsFake"] == "YES" && !empty($row["DeepUrlTpl"])){
				$row["AffDefaultUrl"] = $row["DeepUrlTpl"];
			}
			
			$row["OutGoingUrl"] = strlen($row["DeepUrlTpl"]) ? $row["DeepUrlTpl"] : $row["AffDefaultUrl"];
			
			unset($row["p_AffDefaultUrl"]);
			unset($row["p_DeepUrlTpl"]);
			unset($row["fake_DeepUrlTpl"]);
			unset($row["fake_AffDefaultUrl"]);
			
			$row["LastUpdateTime"] = $update_time;
			
			if($row["PID"] == 137311){
				foreach($row as $k => $v_toutf8){
					$row[$k] = iconv('ISO-8859-1', 'UTF-8', $v_toutf8);
				}
			}
			
			if(!empty($row["Site"])){
				if($row["Site"] == 'global'){
					$row["Key"] = $row["Key"];
					$objRedis->set(":DOMAIN:".$row["Key"], json_encode($row));
					$update_key[$row["Key"]] = $row["Key"];
					
					/*if (SID == 'bdg02') {
						$row["Key"] = $row["Key"]. ":" ."CONT";
						$objRedis->set(":DOMAIN:" . $row["Key"], json_encode($row));
						$update_key[$row["Key"]] = $row["Key"];
					}*/
				}else{
					$row["Key"] = strtolower($row["Site"]).":".$row["Key"];
					$objRedis->set(":DOMAIN:".$row["Key"], json_encode($row));
					$update_key[$row["Key"]] = $row["Key"];
					
					/*if (SID == 'bdg02') {
						$row["Key"] = $row["Key"]. ":" ."CONT";
						$objRedis->set(":DOMAIN:" . $row["Key"], json_encode($row));
						$update_key[$row["Key"]] = $row["Key"];
					}*/
				}
				$i++;
				$j++;
			}
		}
	}
}

echo "site_other finish($j)\r\n";



/*
 * 
 * 
 * DB in REDIS
 * 
 * # [:ACCOUNT:{$accountapi}]	publisher account info FROM db:publisher_account
 * 								{apikey:, accountid:, publisherid:, status:,} 
 * 
 * 
 * # [:DOMAIN:{$domain}]		domain out going info FROM db:domain_outgoing_default
 * 								{domain:, pid:, did:, affid:, idinaff:, url_type: 'default_url'|'deep_url', outgoingurl:, commission_info:{}, key:, limitaccount:, }
 * 								
 * 
 * # [:AFF:{$affid}] 			affiliate info FROM db:wf_aff | in future db:affiliate
 * 								{affid:, affname:, affdomain:{}, affurl_para:{}, aff_deep_para:{}, aff_subtracking_para:{}, aff_sid:{}, aff_type:'NEWWORK'|'INHOUSE', aff_status:, }
 * 
 * 
 * # [:DOMAIN:{$aff_domain}] 	affiliate out going info FROM db:wf_aff 
 * 								{domain:, affid:, outgoingurl:, isaffurl: 'yes', limitaccount:, }
 * 								if 
 * 									desturl is affurl
 * 								then
 * 									add affurl subtracking 
 * 
 * 
 * 
 */
//$sql = "SELECT a.id AS accountid, a.publisherid, a.apikey, a.`name`, a.`status`, p.`status` AS p_status, a.alias, b.sitetype FROM publisher_account a INNER JOIN publisher p ON a.publisherid = p.id LEFT JOIN publisher_detail b ON a.publisherid = b.publisherid";
$sql = "SELECT a.id AS accountid, a.publisherid, a.apikey, a.`name`, a.`status`, p.`status` AS p_status, a.alias, a.siteoption FROM publisher_account a INNER JOIN publisher p ON a.publisherid = p.id";
$account_arr = array();
$account_arr = $objProgram->objMysql->getRows($sql);
$ii = 0;
foreach($account_arr as $v){
	//{apikey:, accountid:, publisherid:, status:, siteidinaff} 
	
	if($v['status'] == 'Active' && $v['p_status'] == 'Active'){
		$row = array();
		$row["apikey"] = $v["apikey"];
		$row["accountid"] = $v["accountid"];
		$row["publisherid"] = $v["publisherid"];
		$row["status"] = $v["status"];
		$row["alias"] = $v["alias"];
		$row["LastUpdateTime"] = $update_time;
		
		/*if(stripos($v["sitetype"], 'c') !== false){
			$row["isloyalty"] = '1';
		}else{
			$row["isloyalty"] = '0';
		}
		
		if(stripos($v["sitetype"], 'e') !== false){
			$row["iscoupon"] = '1';
		}else{
			$row["iscoupon"] = '0';
		}*/
		
		if($v["siteoption"] == 'Promotion'){
			$row["iscoupon"] = '1';
		}else{
			$row["iscoupon"] = '0';
		}
		
		$row["isloyalty"] = '0';
			
		$objRedis->set(":ACCOUNT:".$v["apikey"], json_encode($row));
	}else{
		$objRedis->del(":ACCOUNT:".$v["apikey"]);
		$ii++;
	}
	
	
	//$xx = $objRedis->get(":ACCOUNT:".$v["apikey"]);	
	//print_r($row);print_r($xx);exit;
}
echo "DEL ACCOUNT: ($ii)\r\n";	

$aff_domain_pattern_arr = array();
$aff_sid_arr = array();

$sql = "SELECT id, `name`, affiliateurlkeywords AS aff_redirect_domain, affiliateurlkeywords2 AS aff_sid, deepurlparaname, subtracking, isactive, supportdeepurl, supportsubtracking, isinhouse, isactive FROM wf_aff WHERE isactive = 'yes' order by id desc";
$aff_arr = array();
$aff_arr = $objProgram->objMysql->getRows($sql, "id");
foreach($aff_arr as $v){
	//{affid:, affname:, aff_domain:{}, affurl_para:{}, aff_deep_para:{}, aff_subtracking_para:{}, aff_sid:{}, aff_type:'NEWWORK'|'INHOUSE', aff_status:, }
	$row = array();
	$row["id"] = $v["id"];
	$row["aff_name"] = $v["name"];
	$row["type"] = ($v["isinhouse"] == "YES") ? "inhouse" : "newwork"; 
	$row["status"] = ($v["isactive"] == "YES") ? "active" : "inactive";
	$v["aff_redirect_domain"] = explode("\r\n", $v["aff_redirect_domain"]);
	$row["aff_domain"] = json_encode($v["aff_redirect_domain"]);
	
	$row["aff_deepurl_para"] = $v["deepurlparaname"];
	$row["aff_subtracking_para"] = preg_replace("/=.*/", "", $v["subtracking"]);
	
	//some account sid different
	$row["aff_sid"] = json_encode(explode("\r\n", $v["aff_sid"]));
	$aff_sid_arr[$v["id"]] = explode("\r\n", $v["aff_sid"]);
	
	if (SID == 'bdg01') {
		$sql = "SELECT affid, accountid, siteidinaff, `status` FROM aff_siteid WHERE `status` = 'active' and affid = {$v["id"]}";
		$aff_siteid_arr = array();
		$aff_siteid_arr = $objProgram->objMysql->getRows($sql);
		$tmp_arr = array();
		foreach($aff_siteid_arr as $vv){
			$tmp_arr[$vv["accountid"]] = $vv["siteidinaff"];
		}
		$row["aff_siteidinaff"] = json_encode($tmp_arr);
	}
	$row["LastUpdateTime"] = $update_time;
	
	//print_r($row);
	$objRedis->set(":AFF:".$v["id"], json_encode($row));
	
	//echo "set :AFF:".$v["id"]." '".json_encode($row)."'\r\n";
	
	//$row["aff_account_sid"] = $v["id"];
	
	foreach($v["aff_redirect_domain"] as $aff_out_domain){
		//{domain:, affid:, outgoingurl:, isaffurl: 'yes', limitaccount:, }
		if($aff_out_domain){
			$tmp_domain_arr = $objProgram->getDomainByHomepage($aff_out_domain);
			foreach($tmp_domain_arr as $tmp_domain){
				if($tmp_domain && ($tmp_domain != $aff_out_domain)){
					$aff_domain_row = array();
					$aff_domain_row["domain"] = $tmp_domain;
					$aff_domain_row["AffId"] = $v["id"];
					$aff_domain_row["isaffurl"] = "yes";
					$aff_domain_row["limitaccount"] = "";
					$aff_domain_row["outgoingurl"] = "";
					$aff_domain_row["LastUpdateTime"] = $update_time;
					
					if($v["isinhouse"] == "YES"){
						if(SID != 'bdg02'){
							foreach($ss_ite as $s_site){
								if(!empty($s_site)) $s_site .= ":";
								$tmp_arr = array();
								$tmp_arr = $objRedis->get(":DOMAIN:".$s_site.$aff_domain_row["domain"]);
								
								if($tmp_arr == false){
									$objRedis->set(":DOMAIN:".$s_site.$aff_domain_row["domain"], json_encode($aff_domain_row));								
									
									$update_key[$s_site.$aff_domain_row["domain"]] = $s_site.$aff_domain_row["domain"];								
								}else{
									$update_key[$s_site.$aff_domain_row["domain"]] = $s_site.$aff_domain_row["domain"];
								}
							}
						}
					}else{
						$objRedis->set(":DOMAIN:".$aff_domain_row["domain"], json_encode($aff_domain_row));
						$update_key[$aff_domain_row["domain"]] = $aff_domain_row["domain"];
						
						foreach($ss_ite as $s_site){
							if(empty($s_site)) continue;
							$objRedis->set(":DOMAIN:"."{$s_site}:".$aff_domain_row["domain"], json_encode($aff_domain_row));
							$update_key["{$s_site}:".$aff_domain_row["domain"]] = "{$s_site}:".$aff_domain_row["domain"];
						}						
						
						$aff_domain_pattern_arr[$v["id"]][$aff_domain_row["domain"]] = $aff_domain_row;
					}
				}
			}
								
			$aff_domain_row = array();
			$aff_domain_row["domain"] = $aff_out_domain;
			$aff_domain_row["AffId"] = $v["id"];
			$aff_domain_row["isaffurl"] = "yes";
			$aff_domain_row["limitaccount"] = "";
			$aff_domain_row["outgoingurl"] = "";
			$aff_domain_row["LastUpdateTime"] = $update_time;
			
			if($v["isinhouse"] == "YES"){
				if(SID != 'bdg02'){
					foreach($ss_ite as $s_site){
						if(!empty($s_site)) $s_site .= ":";
						$tmp_arr = array();
						$tmp_arr = $objRedis->get(":DOMAIN:".$s_site.$aff_domain_row["domain"]);
						
						if($tmp_arr == false){
							$objRedis->set(":DOMAIN:".$s_site.$aff_domain_row["domain"], json_encode($aff_domain_row));								
							
							$update_key[$s_site.$aff_domain_row["domain"]] = $s_site.$aff_domain_row["domain"];								
						}else{						
							if(isset($update_key[$s_site.$aff_domain_row["domain"]])){
								$update_key[$s_site.$aff_domain_row["domain"]] = $s_site.$aff_domain_row["domain"];
							}else{							
								unset($update_key[$s_site.$aff_domain_row["domain"]]);
							}
						}
					}
				}
			}else{				
				$objRedis->set(":DOMAIN:".$aff_domain_row["domain"], json_encode($aff_domain_row));
				$update_key[$aff_domain_row["domain"]] = $aff_domain_row["domain"];
				
				foreach($ss_ite as $s_site){
					if(empty($s_site)) continue;
					$objRedis->set(":DOMAIN:"."{$s_site}:".$aff_domain_row["domain"], json_encode($aff_domain_row));
					$update_key["{$s_site}:".$aff_domain_row["domain"]] = "{$s_site}:".$aff_domain_row["domain"];
				}
				
				$aff_domain_pattern_arr[$v["id"]][$aff_domain_row["domain"]] = $aff_domain_row;
			}
			
			
			//echo "set :DOMAIN:".$aff_out_domain." '".json_encode($aff_domain_row)."'\r\n";
			
		}
	}	
	
}




$tmp_redis = array();
$tmp_redis = $objRedis->keys(":AFF_KEY:*");
foreach($tmp_redis as $v){
	$objRedis->del($v);
}
//special never blue = five [a-zA_Z]{5}\.com + a=251165 , My Help Hub = afl=77881 , medialead = mlpid=2311
//TradeTracker DE tt= + 62862
$special_aff = array_flip(array(123, 80, 423, 425, 426, 427, 52, 65));
$special_aff = array_flip(array(425, 426, 427, 52, 65));

//print_r($aff_sid_arr[123]);
$re_domain = array();
foreach($aff_domain_pattern_arr as $affid => $v){
	if(isset($special_aff[$affid])){
		if(in_array($affid, array(425, 426, 427, 52, 65))){
			$re_domain["tradetracker.net"] = "tradetracker\.net";
			
			$objRedis->set(":AFF_KEY:"."tradetracker.net", json_encode($v["tradetracker.net"]));
			foreach($aff_sid_arr[$affid] as $sid){
				$re_domain[$sid] = "(?:[?|&]tt=[0-9_]+)?(".trim($sid).")";
				
				$objRedis->set(":AFF_KEY:".$sid, json_encode($v["tradetracker.net"]));
			}			
		}
		continue;
	}
	foreach($v as $domain => $aff_domain_row){
		if($domain){
			$domain = trim($domain, ".");
			$domain = trim($domain);
			$domain = strtolower($domain);
			
			$k = $domain;
			
			$domain = str_ireplace(".", "\.", $domain);
			$domain = str_ireplace("/", "\/", $domain);
			$domain = str_ireplace("?", "\?", $domain);
			$domain = str_ireplace(":", "\:", $domain);
			$domain = "\.?(".$domain.")";
			
			$re_domain[$k] = $domain;
			$objRedis->set(":AFF_KEY:".$k, json_encode($aff_domain_row));
		}
	}
}

/*$aff_domain_row = array();
$aff_domain_row["domain"] = "mlpid=2311";
$aff_domain_row["AffId"] = 423;
$aff_domain_row["isaffurl"] = "yes";
$aff_domain_row["limitaccount"] = "";
$aff_domain_row["outgoingurl"] = "";
$aff_domain_row["LastUpdateTime"] = $update_time;

$re_domain["mlpid=2311"] = "[?|&](mlpid=2311)";
$objRedis->set(":AFF_KEY:"."mlpid=2311", json_encode($aff_domain_row));

$aff_domain_row["domain"] = "afl=77881";
$aff_domain_row["AffId"] = 80;
$re_domain["afl=77881"] = "[?|&](afl=77881)";
$objRedis->set(":AFF_KEY:"."afl=77881", json_encode($aff_domain_row));

$aff_domain_row["domain"] = "a=251165";
$aff_domain_row["AffId"] = 123;
$re_domain["a=251165"] = "[a-zA_Z]{5}\.com\/\?(a=251165)";
$objRedis->set(":AFF_KEY:"."a=251165", json_encode($aff_domain_row));*/

//print_r($re_domain);
$re = "/https?:\/\/.*(".implode("|", $re_domain).")/i";
$objRedis->set(":AFF_PATTERN:", $re);
//print_r($aff_domain_pattern_arr);

/*foreach($re_domain as $k => $v){
	$objRedis->set(":AFF_KEY:".$k, json_encode($v));
}*/
echo "aff finish\r\n";

//echo ":DOMAIN::".count($objRedis->keys(":DOMAIN:*"))."\r\n";


if(!$is_debug && !$is_quick){
	$j = 0;
	$del_key = array();
	$tmp_redis = array();
	$tmp_redis = $objRedis->keys(":DOMAIN:*");	
	foreach($tmp_redis as $v){
		if(!isset($update_key[str_ireplace(":DOMAIN:", "", $v)])){
			//if(strpos($v, "/") === false){
				$del_key[$v] = "1";
			//}
		}
	}
	$j_cnt = count($del_key);
	if($j_cnt > 50000){
		echo "Del Redis Key Warning, ($j_cnt) [";
		$domain_arr = array();
		foreach($del_key as $k => $v){
			$tm_k = substr($k, 11);
			$domain_arr[$tm_k] = 1;
		//      echo $k."]\r\n";
		}
		print_r($domain_arr);
		$domain_cnt = count($domain_arr);
		$to = "stanguan@meikaitech.com";
		AlertEmail::SendAlert('Del Redis Key Warning',nl2br("Del Redis Key Warning, ($j_cnt), domain ($domain_cnt)"), $to);
		exit;
		
	}else{
		foreach($del_key as $k => $v){
			$objRedis->del($k);
			$j++;
			/*if($j < 10){
				echo "\t".$v."\r\n";
			}*/
			/*if(empty($vv)){
				$vv = $v;
				echo $vv;
			}*/
		}			
	}
	unset($del_key);
	unset($tmp_redis);
	echo "del old finish($j)\r\n";
}

if(SID == 'bdg01'){
	//add not use viglink out domain :S: => Special Out going
	$k = 0;
	$sql = "select a.domainid, a.redirecttype, b.domain from domain_noaff_redirect_config a inner join domain b on a.domainid = b.id where a.status = 'active'";
	$tmp_arr = array();
	$tmp_arr = $objProgram->objMysql->getRows($sql, "domain");
	foreach($tmp_arr as $v){
		$objRedis->set(":S:".$v["domain"], json_encode($v));
		$k++;
	}
	echo "Add Special Rule:($k)\r\n";
	$k = 0;
	$tmp_redis = array();
	$tmp_redis = $objRedis->keys(":S:*");
	foreach($tmp_redis as $v){
		if(!isset($tmp_arr[str_replace(':S:', '', $v)])){
			$objRedis->del($v);
			$k++;
		}
	}
	echo "DEL Special Rule:($k)\r\n";
}


//add domain info
/*$k = 0;
$sql = "select a.id, a.domain, a.subdomain, a.countrycode, a.domainname from domain a inner join domain_outgoing_default b on a.id = b.did";
$tmp_arr = array();
$tmp_arr = $objProgram->objMysql->getRows($sql, "domain");
foreach($tmp_arr as $v){
	$objRedis->set(":D:".$v["domain"], json_encode($v));
	$k++;
}
echo "Add Domain :($k)\r\n";
$k = 0;
$tmp_redis = array();
$tmp_redis = $objRedis->keys(":D:*");
foreach($tmp_redis as $v){
	if(!isset($tmp_arr[str_replace(':D:', '', $v)])){
		$objRedis->del($v);
		$k++;
	}
}
echo "DEL Domain :($k)\r\n";*/


//add Has Aff domain info
if(SID == 'bdg02'){
	$k = 0;
	/*$sql = "SELECT a.id, a.domain, GROUP_CONCAT(DISTINCT b.site) as country FROM domain a INNER JOIN domain_outgoing_default_other b ON a.id = b.did GROUP BY a.id";
	$tmp_arr = array();
	$tmp_arr = $objProgram->objMysql->getRows($sql, "domain");
	foreach($tmp_arr as $v){
		//$objRedis->set(":D:".$v["domain"], json_encode($v));
		$objRedis->set(":D:".$v["domain"], $v['country']);
		$k++;
	}*/
	$sql = "SELECT id, domain FROM domain ";
	$tmp_arr = array();
	$tmp_arr = $objProgram->objMysql->getRows($sql, "domain");
	foreach($tmp_arr as $v){
		$objRedis->set(":D:".$v["domain"], json_encode($v));		
		$k++;
	}
	echo "Add Domain :($k)\r\n";
	$k = 0;
	$tmp_redis = array();
	$tmp_redis = $objRedis->keys(":D:*");
	foreach($tmp_redis as $v){
		if(!isset($tmp_arr[str_replace(':D:', '', $v)])){
			$objRedis->del($v);
			$k++;
		}
	}
	echo "DEL Domain :($k)\r\n";
}


//add active main program
//[sourceid]-[affid]-[idinaff]
//mk=1;br=2
if(SID == 'bdg02'){
	$sourceid = 2;
}else{
	$sourceid = 1;
}
$k = 0;
$tmp_key = array();
$sql = "select programid, affid, idinaff from program_intell where isactive = 'active' and affid NOT IN (191,223,578)"; //$this->sub_aff = array(160,191,223,237,578,639,652,656);
$tmp_arr = array();
$tmp_arr = $objProgram->objMysql->getRows($sql, 'programid');
foreach($tmp_arr as $v){
	$tmp_key[$sourceid.'-'.$v["affid"].'-'.$v["idinaff"]] = 1;
	$objRedis->set(":P:".$sourceid.'-'.$v["affid"].'-'.$v["idinaff"], 1);
	$k++;
}
echo "Add active P:($k)\r\n";
$k = 0;
$tmp_redis = array();
$tmp_redis = $objRedis->keys(":P:*");
foreach($tmp_redis as $v){
	if(!isset($tmp_key[str_replace(':P:', '', $v)])){
		$objRedis->del($v);
		$k++;
	}
}
echo "DEL inactive P:($k)\r\n";
if(SID == 'bdg01'){
	$k = 0;
	$tmp_key = array();
	foreach($tmp_arr as $key => $v){
		$tmp_key[$key] = 1;
		$objRedis->set(":PID:".$key, 1);
		$k++;
	}
	echo "Add active PID:($k)\r\n";
	$k = 0;
	$tmp_redis = array();
	$tmp_redis = $objRedis->keys(":PID:*");
	foreach($tmp_redis as $v){
		if(!isset($tmp_key[str_replace(':PID:', '', $v)])){
			$objRedis->del($v);
			$k++;
		}
	}
	echo "DEL inactive PID:($k)\r\n";
}
unset($tmp_arr);


if(SID == 'bdg02'){
	//advertiser restriction
	$k = 0;
	$sql = "SELECT c.id AS domainid, c.domain, b.SupportType FROM r_store_domain a INNER JOIN store b ON a.storeid = b.id INNER JOIN domain c ON a.domainid = c.id WHERE b.SupportType = 'Content' OR b.SupportType = 'None'";
	$tmp_arr = array();
	$tmp_arr = $objProgram->objMysql->getRows($sql, "domainid");
	foreach($tmp_arr as $v){
		//if($v['supportcoupon'] == 'NO'){
			$objRedis->set(":RES_C:".$v["domainid"], 1);
			$k++;
		//}elseif($v['supportloyalty'] == 'NO'){
			//$objRedis->set(":RES_L:".$v["domainid"], 1);
			//$k++;
		//}
	}
	echo "Add RES_C+RES_L :($k)\r\n";
	$k = 0;
	$tmp_redis = array();
	$tmp_redis = $objRedis->keys(":RES_C:*");
	foreach($tmp_redis as $v){
		if(!isset($tmp_arr[str_replace(':RES_C:', '', $v)]) || $tmp_arr[str_replace(':RES_C:', '', $v)]['SupportType'] != 'Content'){
			$objRedis->del($v);
			$k++;
		}
	}
	echo "DEL RES_C :($k)\r\n";
	$k = 0;
	$tmp_redis = array();
	$tmp_redis = $objRedis->keys(":RES_L:*");
	foreach($tmp_redis as $v){
		if(!isset($tmp_arr[str_replace(':RES_L:', '', $v)]) || $tmp_arr[str_replace(':RES_L:', '', $v)]['SupportType'] != 'None'){
			$objRedis->del($v);
			$k++;
		}
	}
	echo "DEL RES_L :($k)\r\n";
	
	
	//store
	$k = 0;
	$tmp_key = array();
	
	$sql = "SELECT a.accountid, a.objid AS storeid, b.domainid FROM `block_relationship` a INNER JOIN r_store_domain b ON a.objid = b.storeid WHERE a.status = 'active' AND a.objtype = 'store' AND a.accounttype = 'accountid'";
	$tmp_arr = array();
	$tmp_arr = $objProgram->objMysql->getRows($sql);	
	foreach($tmp_arr as $v){
		$tmp_key[$v["domainid"].":".$v["accountid"]] = 1;
		$objRedis->set(":RES_S:".$v["domainid"].":".$v["accountid"], 1);
		$k++;
	}
	
	$sql = "SELECT c.id as accountid, a.objid AS storeid, b.domainid FROM `block_relationship` a INNER JOIN r_store_domain b ON a.objid = b.storeid inner join publisher_account c on a.accountid = c.publisherid WHERE a.status = 'active' AND a.objtype = 'store' AND a.accounttype = 'publisherid'";
	$tmp_arr = array();
	$tmp_arr = $objProgram->objMysql->getRows($sql);	
	foreach($tmp_arr as $v){
		$tmp_key[$v["domainid"].":".$v["accountid"]] = 1;
		$objRedis->set(":RES_S:".$v["domainid"].":".$v["accountid"], 1);
		$k++;
	}
	
	
	//affiliate || temp need edit 
	$sql = "SELECT a.accountid, c.did as domainid FROM `block_relationship` a INNER JOIN program b ON a.objid = b.id INNER JOIN r_domain_program c ON b.id = c.pid WHERE a.status = 'active' AND c.status = 'active' AND a.blockby = 'affiliate' AND a.objtype = 'program' AND a.accounttype = 'accountid'";
	$tmp_arr = array();
	$tmp_arr = $objProgram->objMysql->getRows($sql);	
	foreach($tmp_arr as $v){
		$tmp_key[$v["domainid"].":".$v["accountid"]] = 1;
		$objRedis->set(":RES_S:".$v["domainid"].":".$v["accountid"], 1);
		$k++;
	}
	
	$sql = "SELECT d.id as accountid, c.did as domainid FROM `block_relationship` a INNER JOIN program b ON a.objid = b.id INNER JOIN r_domain_program c ON b.id = c.pid inner join publisher_account d on a.accountid = d.publisherid  WHERE a.status = 'active' AND c.status = 'active' AND a.blockby = 'affiliate' AND a.objtype = 'program' AND a.accounttype = 'publisherid'";
	$tmp_arr = array();
	$tmp_arr = $objProgram->objMysql->getRows($sql);	
	foreach($tmp_arr as $v){
		$tmp_key[$v["domainid"].":".$v["accountid"]] = 1;
		$objRedis->set(":RES_S:".$v["domainid"].":".$v["accountid"], 1);
		$k++;
	}
	
	//print_r($tmp_key);
	echo "Add RES_S :($k)\r\n";
	$k = 0;
	$tmp_redis = array();
	$tmp_redis = $objRedis->keys(":RES_S:*");
	foreach($tmp_redis as $v){		
		if(!isset($tmp_key[str_replace(':RES_S:', '', $v)])){
			$objRedis->del($v);
			$k++;
		}		
	}	
	echo "DEL RES_S :($k)\r\n";
	
	
	//block by affiliate
	$sql = "SELECT accountid, objid as affid FROM `block_relationship` WHERE status = 'active' AND objtype = 'Affiliate' and accounttype = 'accountid'";
	$tmp_arr = array();
	$tmp_arr = $objProgram->objMysql->getRows($sql);	
	foreach($tmp_arr as $v){
		$tmp_key[$v["accountid"].":".$v["affid"]] = 1;
		$objRedis->set(":BLOCK:".$v["accountid"].":".$v["affid"], 1);
		$k++;
	}
	$sql = "SELECT b.id as accountid, a.objid as affid FROM `block_relationship` a inner join publisher_account b on a.accountid = b.publisherid WHERE a.status = 'active' AND objtype = 'Affiliate' and accounttype = 'publisherid'";
	$tmp_arr = array();
	$tmp_arr = $objProgram->objMysql->getRows($sql);	
	foreach($tmp_arr as $v){
		$tmp_key[$v["accountid"].":".$v["affid"]] = 1;
		$objRedis->set(":BLOCK:".$v["accountid"].":".$v["affid"], 1);
		$k++;
	}
	//print_r($tmp_key);
	echo "Add BLOCK :($k)\r\n";
	$k = 0;
	$tmp_redis = array();
	$tmp_redis = $objRedis->keys(":BLOCK:*");
	foreach($tmp_redis as $v){		
		if(!isset($tmp_key[str_replace(':BLOCK:', '', $v)])){
			$objRedis->del($v);
			$k++;
		}		
	}	
	echo "DEL BLOCK :($k)\r\n";
}


//for digidip only fr & hotdeals can use
if(SID == 'bdg01' && !$is_quick){
	$k = 0;
	$sql = "SELECT b.did, a.id as pid, c.domain FROM program a INNER JOIN r_domain_program b ON a.id = b.pid INNER JOIN domain c ON b.did = c.id WHERE a.affid = 639 AND a.statusinaff = 'active' AND a.partnership = 'active' AND b.status = 'active'";
	$tmp_arr = array();
	$tmp_arr = $objProgram->objMysql->getRows($sql, 'did');
	$tmp_key = array();
	foreach($tmp_arr as $v){
		$tmp_key[$v["domain"]] = 1;
		$objRedis->set(":DIGIDIP:".$v["domain"], json_encode($v));
		$k++;
	}
	echo "Add DIGIDIP :($k)\r\n";
	$k = 0;
	$tmp_redis = array();
	$tmp_redis = $objRedis->keys(":DIGIDIP:*");
	foreach($tmp_redis as $v){		
		if(!isset($tmp_key[str_replace(':DIGIDIP:', '', $v)])){
			$objRedis->del($v);
			$k++;
		}		
	}	
	echo "DEL DIGIDIP :($k)\r\n";	
}
// for fr 
//$objRedis->del(":DOMAIN:fr:alfredetcompagnie.com");
//$objRedis->del(":DOMAIN:fr:boutique.orange.fr");
//$objRedis->del(":DOMAIN:fr:priceminister.com");

//print_r($xx);
echo "size:".$objRedis->dbSize()."\t";
echo ":ACCOUNT::".count($objRedis->keys(":ACCOUNT:*"))."\t";
echo ":AFF::".count($objRedis->keys(":AFF:*"))."\t";
echo ":DOMAIN::".count($objRedis->keys(":DOMAIN:*"))."\r\n";

echo "<< End @$i|$j ".date("Y-m-d H:i:s")." >>\r\n";
exit;



?>