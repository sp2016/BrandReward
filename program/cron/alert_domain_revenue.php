<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");

echo "<< Start @ ".date("Y-m-d H:i:s")." >>\r\n";

$objProgram = New Program();

$alert_body = checkHighRankDomianChange();
AlertEmail::SendAlert("Domain Change Log Alert @ " . date("Y-m-d H:i:s"),nl2br($alert_body), "stanguan@meikaitech.com, sunnychen@meikaitech.com");

$alert_subject = "Domain Revenue Alert  @ " . date("Y-m-d H:i:s");
$alert_body = "";
$alert_body .= checkDomainRevenue(0);
$alert_body .= checkDomainRevenue(1);
$alert_body .= checkDomainRevenue(3);

AlertEmail::SendAlert($alert_subject,nl2br($alert_body), "stanguan@meikaitech.com, sunnychen@meikaitech.com");

if(date('w') === '1'){
	$alert_body = checkDomainRevenuebySite();
	AlertEmail::SendAlert("Domain Revenue Alert Weekly  @ " . date("Y-m-d H:i:s"),nl2br($alert_body), "stanguan@meikaitech.com, sunnychen@meikaitech.com");
}


function checkHighRankDomianChange(){
	global $objProgram;
	$time ='';	
	
    $yesterday1 = date("Y-m-d H:i:s",strtotime("-1 day"));
    $yesterday1 = "'".$yesterday1."'";
    $yesterday2 = date("Y-m-d H:i:s");
    $yesterday2 = "'".$yesterday2."'";
    $time = " AND tb2.ChangeTime >=$yesterday1 AND tb2.ChangeTime <$yesterday2";
	
	$sql = 'SELECT tb2.ID,tb2.DID,tb2.`Key`,tb2.ProgramFrom,tb2.ProgramTo,tb2.ChangeTime,tb2.Site FROM	domain_stats AS tb1 INNER JOIN domain_outgoing_default_changelog_site AS tb2 ON tb1.domainid = tb2.DID WHERE tb1.Rank >= 1 AND tb1.Rank <= 1200'.$time . ' order by  tb2.ChangeTime desc ';
	$res = $objProgram->objMysql->getRows($sql);
	foreach($res as $k=>$v){
	
	    $text = ' AND tb1.ID in('.$v['ProgramFrom'].','.$v['ProgramTo'].')';
	    $sql2 = 'SELECT tb1.id, tb2.NAME,tb1.`Name` as pname,tb1.StatusInAff,tb1.Partnership from program as tb1 LEFT JOIN wf_aff as tb2 on tb1.AffId = tb2.ID WHERE 1=1'.$text;
	    $res2 = $objProgram->objMysql->getRows($sql2, 'id');
	    $res[$k]['det'] = $res2;
	
	}
	$html = "Domain Change Log  (".count($res).") - " . date("Y-m-d") . " \n";
	$html .= "<table border=1>";
    $html.='<tr><th>Domain</th>
                <th>Site</th>
                <th>From</th>
                <th>To</th>
                <th>ChangeTime</th></tr>';
            foreach($res as $k){
                $html.='<tr><td>'.$k['Key'].'</td>';
                $html.='<td>'.$k['Site'].'</td>';
                    if($k['ProgramFrom'] == 0){
                        $html.='<td>N/A</td>';
                    }else{
                        $val = '<a target="_blank" href="http://bdg.meikaiinfotech.com/admin/program_edit.php?ID='.$k['ProgramFrom'].'">'. $k['det'][$k['ProgramFrom']]['pname'].'('.$k['det'][$k['ProgramFrom']]['NAME'].'</a>) StatusInAff:'.$k['det'][$k['ProgramFrom']]['StatusInAff'].' Partnership:'.$k['det'][$k['ProgramFrom']]['Partnership'];
                        $html.="<td>$val</td>";
                    }
                    if($k['ProgramTo'] == 0){
                        $html.='<td>N/A</td>';
                    }else{
                        $val = '<a target="_blank" href="http://bdg.meikaiinfotech.com/admin/program_edit.php?ID='.$k['ProgramTo'].'">'.$k['det'][$k['ProgramTo']]['pname'].'('.$k['det'][$k['ProgramTo']]['NAME'].'</a>) StatusInAff:'.$k['det'][$k['ProgramTo']]['StatusInAff'].' Partnership:'.$k['det'][$k['ProgramTo']]['Partnership'];
                        $html.="<td>$val</td>";
                    }


                $html.='<td>'.$k['ChangeTime'].'</td></tr>';
            }
	$html.='</table>';
	return $html;
}

function checkDomainRevenuebySite(){
	global $objProgram;
	$affiliate_list = $objProgram->getAllAffiliate();
	
	$aff_domain = array();
	foreach($affiliate_list as $v){
		if($v["IsInHouse"] != "NO" || !strlen($v["AffiliateUrlKeywords"])) continue;//affiliate who is not in house and has AffiliateUrlkeywords can go on
		$tmp_arr = explode("\r\n", $v["AffiliateUrlKeywords"]);
		foreach($tmp_arr as $vv){
			$tmp_domain = array();
			$tmp_domain = $objProgram->getDomainByHomepage($vv, "fi");
			//print_r($domain_arr);
			if(count($tmp_domain)){
				$domain = current($tmp_domain["domain"]);
				if(!empty($domain))$aff_domain[$domain] = $domain;
			}
		}
	}
	
	//get domain_stats -7 revenue, order
	$sql = "SELECT b.Domain, a.DomainId, a.Site, a.Orders7D, a.Revenue7D, a.Clicks7D, a.Sales7D from domain_stats a inner join domain b on a.domainid = b.id";
	$tmp_arr = $objProgram->objMysql->getRows($sql);
	
	$last_7_arr = array();
	foreach($tmp_arr as $k => $v){
		//if(!isset($aff_domain[$v['Domain']])){
			$last_7_arr[$v['Site']][$v['DomainId']] = array('Orders7D' => $v['Orders7D'], 'Revenue7D' => $v['Revenue7D'], 'Clicks7D' => $v['Clicks7D'], 'Sales7D' => $v['Sales7D'], 'Domain' => $v['Domain']);
		//}
		unset($tmp_arr[$k]);
	}
	
	//get 14 - 7 revenue, order
	$sql = "SELECT a.domainid, b.alias, SUM(a.revenues) AS rev, SUM(a.orders) AS ord, SUM(a.clicks) AS cli, SUM(a.sales) AS sal FROM `statis_domain` a INNER JOIN publisher_account b ON a.site = b.apikey
			WHERE a.createddate >= '".date("Y-m-d", strtotime("-14 days"))."' AND a.createddate < '".date("Y-m-d", strtotime("-7 days"))."' AND a.domainid > 0
			
			group by a.domainid, b.alias ORDER BY rev DESC";
    $tmp_arr = $objProgram->objMysql->getRows($sql);
    //print_r($tmp_arr);
	
    foreach ($tmp_arr as $k => $v) {
        $site = "";
        //$tmp_alias = $accout_arr[$v["site"]]["alias"];
        $tmp_alias = $v["alias"];
        $tmp_alias = str_ireplace("cs", "", $tmp_alias);
        $tmp_alias = str_ireplace("ds", "", $tmp_alias);
        if (in_array($tmp_alias, array("us", "uk", "ca", "de", "au", "fr"))) {
            $site = $tmp_alias;            
        }
        $tmp_arr[$k]['site'] = $site;
        if ($site){
        	if(isset($last_7_arr[$site][$v['domainid']])){
        		if(isset($aff_domain[$last_7_arr[$site][$v['domainid']]['Domain']])){
        			unset($tmp_arr[$k]);
        		}elseif($v['ord'] < 20){//check orders
        			if($v['rev'] > 2 * $last_7_arr[$site][$v['domainid']]['Revenue7D']){        			
	        			$sql = "SELECT 1 FROM `domain_outgoing_default_changelog_site` WHERE did = {$v['domainid']} AND site = '$site' AND changetime > '".date("Y-m-d", strtotime("-14 days"))."' LIMIT 1";
	        			$has_change = false;
	        			$has_change = $objProgram->objMysql->getRows($sql);
	        			if(!count($has_change)){
	        				unset($tmp_arr[$k]);
	        			}
        			}else{
        				unset($tmp_arr[$k]);
        			}
        			
        		}elseif(!($v['rev'] > 2 * $last_7_arr[$site][$v['domainid']]['Revenue7D'] || $v['ord'] > 2 * $last_7_arr[$site][$v['domainid']]['Orders7D'])){
        			unset($tmp_arr[$k]);
        		}
        	}        	
        }else{
        	unset($tmp_arr[$k]);
        }
    }
  //   print_r($tmp_arr);exit;
    echo count($tmp_arr);
    
    $alert_body = "Domain Revenue Order Alert  (".count($tmp_arr).") - " . date("Y-m-d") . " \n";
	if(count($tmp_arr)){
		$i = 0;
		$alert_body .= "<table border=1>";
		$alert_body .= "<tr><th>Domain</th><th>country</th><th>revenue ".date("Y-m-d", strtotime("-6 days"))." - " . date("Y-m-d"). "</th><th>orders ".date("Y-m-d", strtotime("-6 days"))." - " . date("Y-m-d"). "</th><th>revenue ".date("Y-m-d", strtotime("-13 days"))." - " . date("Y-m-d", strtotime("-7 days")). "</th><th>orders ".date("Y-m-d", strtotime("-13 days"))." - " . date("Y-m-d", strtotime("-7 days")). "</th></tr>";
		foreach($tmp_arr as $k => $v){
			$alert_body .= "<tr><td>{$last_7_arr[$v['site']][$v['domainid']]['Domain']}</td><td>{$v['site']}</td><td>{$last_7_arr[$v['site']][$v['domainid']]['Revenue7D']}</td><td>{$last_7_arr[$v['site']][$v['domainid']]['Orders7D']}</td><td>{$v['rev']}</td><td>{$v['ord']}</td></tr>";
			
			//$i++;
			unset($tmp_arr[$k]);
			//if($i > 100) break;
		}
		$alert_body .= "</table>\n\n";
		
	}else{
		$alert_body .= "No warning.\n\n";
	}
	
	return $alert_body;
}

/*
 * 
 * 
 * 
 */
function checkDomainRevenue($days){
	global $objProgram;
	//$date_check = 4;
	$dd = 2;	//daily 2 days
	$dd_his = 7; //check history 7 days
	$date_check = date("Y-m-d", strtotime("-$dd days"));
	$date_check_e = date("Y-m-d", strtotime("-" . ($dd + $days) . " days"));
	$date_his_s = date("Y-m-d", strtotime("-" . (1+ $dd + $days) . " days"));
	$date_his_e = date("Y-m-d", strtotime("-" . (1+ $dd_his + $dd + $days) . " days"));
	$domain_arr = $domain_check_arr = $alert_domain = $alert_arr = array();
	for($i=(1+ $dd + $days);$i<(1+ $dd_his + $dd + $days);$i++){	
		$date_his = date("Y-m-d", strtotime("-$i days"));
		$sql = "SELECT domainid, SUM(revenues), SUM(orders) FROM `statis_domain` WHERE createddate = '$date_his' AND domainid > 0 AND revenues > 0 GROUP BY domainid";	
		$tmp_arr = array_keys($objProgram->objMysql->getRows($sql, "domainid"));
		
		if($i == (1+ $dd + $days)){
			$domain_arr = $tmp_arr;
		}else{
			$domain_arr = array_diff($domain_arr, array_diff($domain_arr, $tmp_arr));
		}
	}
	
	$sql = "SELECT domainid, SUM(revenues), SUM(orders) FROM `statis_domain` WHERE (createddate <= '$date_check' and createddate >= '$date_check_e') AND domainid > 0 AND revenues > 0 GROUP BY domainid";	
	$domain_check_arr = array_keys($objProgram->objMysql->getRows($sql, "domainid"));	
	$alert_domain = array_diff($domain_arr, $domain_check_arr);
	
	if(count($alert_domain)){
		$sql = "select a.id, a.domain, SUM(b.revenues) as '{$date_check}<br />{$date_check_e}<br />revenues', SUM(orders) as '{$date_check}<br />{$date_check_e}<br />orders', SUM(clicks) as clicks, SUM(clicks) as '{$date_check}<br />{$date_check_e}<br />clicks', SUM(sales) as '{$date_check}<br />{$date_check_e}<br />sales' 
				from domain a inner join statis_domain b on a.id = b.domainid 
				where (createddate <= '$date_check' and createddate >= '$date_check_e') and a.id in (".implode(",", $alert_domain).") and clicks > 25 group by a.id";
		$alert_arr = $objProgram->objMysql->getRows($sql, "id");
		
		$sql = "select a.id, a.domain, SUM(b.revenues) as '{$date_his_s}<br />{$date_his_e}<br />revenues', SUM(orders) as '{$date_his_s}<br />{$date_his_e}<br />orders', SUM(clicks) as '{$date_his_s}<br />{$date_his_e}<br />clicks', SUM(sales) as '{$date_his_s}<br />{$date_his_e}<br />sales' 
				from domain a inner join statis_domain b on a.id = b.domainid 
				where (createddate <= '$date_his_s' and createddate >= '$date_his_e') and a.id in (".implode(",", $alert_domain).")  group by a.id";
		$tmp_arr = $objProgram->objMysql->getRows($sql, "id");
		foreach($tmp_arr as $id => $v){
			if(isset($alert_arr[$id])){
				$alert_arr[$id] += $v;
			}else{
				//$alert_arr[$id] = $v;
				unset($alert_arr[$id]);
			} 
		}
	}
	
	echo count($alert_arr)."\r\n";
	$alert_body = "Domain Revenue Alert (".count($alert_arr).") $date_check - $date_check_e " . "\n";
	if(count($alert_arr)){
		$alert_body .= "<table border=1>";
		$alert_body .= "<tr><th>".implode("</th><th>", array_keys(current($alert_arr)))."</th></tr>";
		foreach($alert_arr as $v){
			$alert_body .= "<tr><td>".implode("</td><td>", $v)."</td></tr>";
		}
		$alert_body .= "</table>\n\n";
		
	}else{
		$alert_body .= "No warning.\n\n";
	}
	
	return $alert_body;
}


echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";
exit;
?>