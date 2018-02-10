<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
include_once(INCLUDE_ROOT . "func/func.php");

echo "<< Start @ ".date("Y-m-d H:i:s")." >>\r\n";

define("PROCESS_CNT", 7);

$process_name = __FILE__;

$idinaff = array();
$is_debug = $is_child = $is_homepage = $ignore = false;
$affid = $programid = 0;
$checktime = date("Y-m-d");
if(isset($_SERVER["argc"]) && $_SERVER["argc"] > 1)
{
	foreach($_SERVER["argv"] as $v){
		$tmp = explode("=", $v);
		if($tmp[0] == "--child"){
			$is_child = true;
		}elseif($tmp[0] == "--affid"){
			$affid = intval($tmp[1]);
		}elseif($tmp[0] == "--debug"){
			$is_debug = true;
		}elseif($tmp[0] == "--idinaff"){
			$idinaff = trim($tmp[1]);
		}elseif($tmp[0] == "--checktime"){
			$checktime = trim($tmp[1],"'");
		}elseif($tmp[0] == "--homepage"){
			$is_homepage = true;
		}elseif($tmp[0] == "--pid"){
			$programid = trim($tmp[1]);
		}elseif($tmp[0] == "--ignore"){
			$ignore = true;
		}
	}
}

$objProgram = New Program();
	$sql = "select Domain from domain_top_level";
	$topDomain_tmp = $objProgram->objMysql->getRows($sql);
	$topDomain = array();
	foreach ($topDomain_tmp as $v)
	{
		$topDomain[] = '\.'.$v['Domain'];
	}
	$country_arr = explode(",", $objProgram->global_c);
	foreach ($country_arr as $country) {
		if ($country) {
			$country = "\." . strtolower($country);
			$topDomain[] = "\.com?" . $country;
			$topDomain[] = "\.org?" . $country;
			$topDomain[] = "\.net?" . $country;
			$topDomain[] = "\.gov?".$country;
			$topDomain[] = "\.edu?".$country;
			$topDomain[] =  $country."\.com";
			$topDomain[] = $country;
		}
	}
	
if(!$is_child){
	$cmd = `ps aux | grep $process_name | grep '/bin/sh' | grep 'grep' -v -c`;
	$return = ''.$cmd.'';
	if($return > 1){
		echo "PROCESS_CNT > 1 {$return}\r\n";
		echo "<< END @ ".date("Y-m-d H:i:s")." >>\r\n";
		exit;
	}

	//"2" => "New Program Links Preview", ONLY crawl aff
	$sql = "select a.id from program a inner join program_intell b on a.id = b.programid inner join r_domain_program c on a.id = c.pid INNER JOIN wf_aff d on d.id = a.affid
			where b.isactive = 'active' and a.addtime > '".date("Y-m-d", strtotime(" -1 days"))."' and d.ProgramCrawled = 'yes'";
	$error_err = $objProgram->objMysql->getRows($sql, "id");
	$error_err = array_keys($error_err);
	foreach($error_err as $pid){
		$sql = "select id from t_check_p_d_links where programid = $pid and errortype = 2 and addtime > '2017-01-01' limit 1";
		$tmp_arr = array();
		$tmp_arr = $objProgram->objMysql->getFirstRow($sql);
		if(!count($tmp_arr)){
			$sql = "insert ignore into t_check_p_d_links (programid, status, errortype, errorvalue, addtime, lastupdatetime)
						values({$pid}, 'New', '2', '', '".date("Y-m-d H:i:s")."', '".date("Y-m-d H:i:s")."')";
			$objProgram->objMysql->query($sql);

		}
	}


	//"3" => "No Link Program Check",
	$sql = "SELECT a.id FROM program a INNER JOIN program_intell b ON a.id = b.programid INNER JOIN r_domain_program c ON a.id = c.pid
			WHERE a.statusinaff = 'active' AND a.partnership = 'active' AND c.AffDefaultUrl = ''
			AND a.affid != 160 AND a.affid != 639 AND a.affid != 191 AND a.affid != 223 AND a.affid != 578";
	$error_err = $objProgram->objMysql->getRows($sql, "id");
	$error_err = array_keys($error_err);
	foreach($error_err as $pid){
		$sql = "select programid from program_manual where programid = $pid and StatusInBdg = 'Inactive' limit 1";
		$tmp_arr = array();
		$tmp_arr = $objProgram->objMysql->getFirstRow($sql);

		if(!count($tmp_arr)){
			$sql = "select id from t_check_p_d_links where programid = $pid and errortype = 3 and addtime > '2017-01-01' limit 1";
			$tmp_arr = array();
			$tmp_arr = $objProgram->objMysql->getFirstRow($sql);
			if(!count($tmp_arr)){
				$sql = "insert ignore into t_check_p_d_links (programid, status, errortype, errorvalue, addtime, lastupdatetime)
							values({$pid}, 'New', '3', '', '".date("Y-m-d H:i:s")."', '".date("Y-m-d H:i:s")."')";
				$objProgram->objMysql->query($sql);
			}
		}
	}
	
	
	$sql = "SELECT a.id FROM program a INNER JOIN program_intell b ON a.id = b.programid 
			WHERE a.statusinaff = 'active' AND a.partnership = 'active' AND b.domain = ''
			AND a.affid != 160 AND a.affid != 639 AND a.affid != 191 AND a.affid != 223 AND a.affid != 578";
	$error_err = $objProgram->objMysql->getRows($sql, "id");
	$error_err = array_keys($error_err);
	foreach($error_err as $pid){
		$sql = "select programid from program_manual where programid = $pid and StatusInBdg = 'Inactive' limit 1";
		$tmp_arr = array();
		$tmp_arr = $objProgram->objMysql->getFirstRow($sql);

		if(!count($tmp_arr)){
			$sql = "select id from t_check_p_d_links where programid = $pid and errortype = 3 and addtime > '2017-01-01' limit 1";
			$tmp_arr = array();
			$tmp_arr = $objProgram->objMysql->getFirstRow($sql);
			if(!count($tmp_arr)){
				$sql = "insert ignore into t_check_p_d_links (programid, status, errortype, errorvalue, addtime, lastupdatetime)
							values({$pid}, 'New', '3', '', '".date("Y-m-d H:i:s")."', '".date("Y-m-d H:i:s")."')";
				$objProgram->objMysql->query($sql);
			}
		}
	}	


	//killProcess($process_name);

	$affiliate_list = $objProgram->getAllAffiliate();
	krsort($affiliate_list);
	foreach($affiliate_list as $affid => $aff_v){
		if(in_array($affid, array(63, 491, 418, 160))) {
			continue;	//echo "Affiliate restraction";
		}
		while(1){
			if(checkProcess($process_name)){
				echo "\t aff $affid start \r\n";
				$cmd = "nohup php $process_name --affid=$affid --child > /home/bdg/program/cron/test/check_program_domain_links_{$affid}.log 2>&1 &";
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

	if(checkProcess($process_name." | grep homepage", 1)){
		$cmd = "nohup php $process_name --child --homepage > /home/bdg/program/cron/test/check_program_domain_links_homepage.log 2>&1 &";
		echo "\t".$cmd."\r\n";
		system($cmd);
	}

}elseif($is_homepage){
	if(checkProcess($process_name." | grep homepage", 1)){
		echo "homepage";
		//"4" => "Program Homepage Check"
		$sql = "SELECT a.id, a.homepage FROM program a INNER JOIN program_intell b ON a.id = b.programid
				WHERE b.isactive = 'active' AND a.affid not in (160, 223, 191,578) limit 100";
		if($programid){
			$sql .= " and a.id in ($programid)";
		}
		$error_err = $objProgram->objMysql->getRows($sql, "id");

		//print_r($error_err);
		foreach($error_err as $p_v){
			if(stripos($p_v["homepage"], "http") !== 0){
				$p_v["homepage"] = 'http://'.$p_v["homepage"];
			}

			$sql = "select id from t_check_p_d_links where programid = {$p_v["id"]} and errortype = 4 and addtime > '2017-01-01' limit 1";
			$tmp_arr = array();
			$tmp_arr = $objProgram->objMysql->getFirstRow($sql);
			if(!count($tmp_arr)){
				$res_str = browser_url($p_v["homepage"]);

				if($is_debug) print_r($res_str);

	        	list($finalUrl,$httpcode) = explode("\t",$res_str);

				if(strlen($finalUrl)){
					$domain_arr = $objProgram->getDomainByHomepage($p_v["homepage"], "fi");
					//print_r($domain_arr);
					$domain_old = current($domain_arr["domain"]);

					$domain_arr = $objProgram->getDomainByHomepage($finalUrl, "fi");
					//print_r($domain_arr);
					$domain = current($domain_arr["domain"]);

					if($domain && $domain_old != $domain){
						$sql = "select * from domain a inner join r_domain_program b on a.id = b.did where a.domain = '".addslashes($domain)."' and b.pid = {$p_v["id"]} and b.status = 'active' limit 1";
						$tmp_arr = array();
						$tmp_arr = $objProgram->objMysql->getFirstRow($sql);

						if(!count($tmp_arr)){
							//echo $sql;
							//print_r($tmp_arr);

							$sql = "insert ignore into t_check_p_d_links (programid, status, errortype, errorvalue, addtime, lastupdatetime)
										values({$p_v["id"]}, 'New', '4', '".addslashes($domain)."', '".date("Y-m-d H:i:s")."', '".date("Y-m-d H:i:s")."')";
							//exit;
							$objProgram->objMysql->query($sql);
						}
					}
				}

			}
		}
	}
}else{
	if(!checkProcess($process_name." | grep '$affid --child'", 1)){
		echo "\t $affid not finished \r\n";
		echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";
		exit;
	}

	if(in_array($affid, array(63, 491, 418, 160))) {
		echo "Affiliate restraction";
		exit;
	}
	if($affid == 15){
		echo "zanox restraction";
		exit;
	}

//	if($affid == 191 || $affid == 578 || $affid == 223){
//		echo "stop for test out traffic";exit;
//	}

	$sql = "SELECT AffiliateUrlKeywords FROM wf_aff WHERE isinhouse = 'no' AND isactive = 'yes' AND AffiliateUrlKeywords <> ''";
	$aff_domain = $tmp_arr = array();
	$tmp_arr = $objProgram->objMysql->getRows($sql);
	foreach($tmp_arr as $v){
		$tmp_arr2 = explode("\r\n", $v["AffiliateUrlKeywords"]);

		foreach($tmp_arr2 as $vv){
			$domain_arr = $objProgram->getDomainByHomepage($vv, "fi");
			//print_r($domain_arr);
			$domain = @current($domain_arr["domain"]);
			if(!empty($domain))$aff_domain[$domain] = $domain;
		}
	}

	$sql = "select a.programid, a.isactive, c.id as domainid, d.homepage, b.affdefaulturl, b.deepurltpl, b.isfake, c.domain, c.domainname, c.subdomain, a.SupportDeepUrl, a.SupportDeepUrlOut
			from program_intell a inner join r_domain_program b on a.programid = b.pid inner join domain c on b.did = c.id inner join program d on a.programid = d.id
			where a.affid = $affid and b.status = 'active'";
	if($idinaff){
		$sql .= " and a.idinaff = '".addslashes($idinaff)."'";
	}
	if($programid){
		$sql .= " and a.programid in ($programid)";
	}
	if(!$is_debug){
		$sql .= " and a.isactive = 'active' ";
	}
	$links_arr = array();
	$links_arr = $objProgram->objMysql->getRows($sql);


//	echo "links count(".count($links_arr).")\r\n";
	$cnt = $valid = $jj = 0;

	if($affid == 578){
		 $sql = "select d.id as programid, c.id as domainid, d.homepage, b.affdefaulturl, b.deepurltpl, b.isfake, c.domain, c.domainname, c.subdomain
				from r_domain_program b
				inner join domain c on b.did = c.id
				inner join program d on b.pid = d.id
				inner join t_check_p_d_links e on d.id = e.programid
				where d.affid = $affid and e.errortype in (1,11,0)";	//" and (e.status = 'assigned' || e.status = 'new')";
		if(!$is_debug){
			$sql .= "and b.status = 'active'";
		}
		if($programid){
			$sql .= " and d.id in ($programid)";
		}
		$tmp_arr = array();
		$tmp_arr = $objProgram->objMysql->getRows($sql);
		if($ignore){
			$links_arr = $tmp_arr;
		}else{
			$links_arr += $tmp_arr;
		}
	}

	echo "links count(".count($links_arr).")\r\n";

	foreach($links_arr as $link){
		if($affid == 578 && isset($link['isactive']) && $link['isactive'] == 'active'){
			$sql = "select id from domain_outgoing_default_site where pid = {$link['programid']} and did = {$link['domainid']} ";
			$tmp_out = array();
			$tmp_out = $objProgram->objMysql->getRows($sql);

			if(!count($tmp_out)) continue;
		}
		if(strpos($link["domain"], "/") !== false) continue;
		foreach(array("affdefaulturl" => $link["affdefaulturl"], "deepurltpl" => $link["deepurltpl"]) as $k_type => $v_affurl){
			if(stripos($v_affurl, "http") !== 0) continue;

			if($k_type == "deepurltpl" && empty($v_affurl)) continue;

			if(stripos($link["homepage"], 'https://') !== false){
				$check_url = 'https://';
			}else{
				$check_url = 'http://';
			}
			
			
			if($link["subdomain"] != ''){
				$check_url .= $link["domain"];
			}else{
				$check_url .= 'www.'.$link["domain"];
			}
			
			if($k_type == "deepurltpl"){
				$v_affurl = clean_parm($check_url, $v_affurl);
			}else{
				$v_affurl = clean_parm($check_url, $v_affurl);
			}

			if($is_debug){
//				print_r($v_affurl);//exit;
			}
			if($v_affurl){
				if($affid == 578 || $affid == 223 || $affid == 191){
					if($k_type == "deepurltpl") continue;
					echo "\n",$affid,$v_affurl,"\n";
					$res_str = checkSpecialAff($affid, $v_affurl);
				}else{
					if($affid == 152 || $affid == 1){
						$res_str = checkSpecialAff($affid, $v_affurl);
					}else{
						$res_str = checkSpecialAff($affid, $v_affurl);

						if(empty($res_str)){
							$res_str = browser_url($v_affurl);
						}
					}
				}

				if($is_debug){
					echo "\r\nres_str:".$res_str."\r\n";exit;
				}
	        	list($finalUrl,$httpcode) = explode("\t",$res_str);
				if(strpos($res_str, '.savings.com') !== false){
					$httpcode = 404;
				}

				$domain = '';
				//print_r($res_str);

				if($httpcode == 200 && strlen($finalUrl)){
					$domain_arr = $objProgram->getDomainByHomepage($finalUrl, "fi");
					//print_r($domain_arr);
					$domain = current($domain_arr["domain"]);

					if(isset($aff_domain[$domain])){
						$httpcode = "999".$finalUrl;

						$sql = "select id from t_check_p_d_links where programid = {$link["programid"]} and domainid = {$link["domainid"]}  and checkurl = '".addslashes($v_affurl)."' and errortype in (1,11,0) and (status = 'new' or status = 'assigned' ) limit 1";
						$tmp_arr = array();
						$tmp_arr = $objProgram->objMysql->getFirstRow($sql);
						if(!count($tmp_arr)){
							if(stripos($finalUrl, ".shareasale.com") !== false || stripos($finalUrl, ".webmasterplan.com") !== false || stripos($finalUrl, "track.omguk.com") !== false || stripos($finalUrl, "track.in.omgpm.com") !== false || stripos($finalUrl, "belboon.de" !== false)){
								$errortype = 0;
							}else{
								if($k_type == "affdefaulturl"){
									$errortype = 1;
								}else{
									$errortype = 11;
								}
							}

							if($affid == 578){
								$sql = "insert into program_manual (programid, StatusInBdg) values ('{$link["programid"]}', 'Inactive') on duplicate key update StatusInBdg = 'Inactive' , lastupdatetime = '".date("Y-m-d H:i:s")."'";
								$objProgram->objMysql->query($sql);
							}

							$sql = "insert ignore into t_check_p_d_links (programid, domainid, status, errortype, errorvalue, addtime, lastupdatetime, checkurl)
									values({$link["programid"]}, {$link["domainid"]}, 'New', '$errortype', '".addslashes($httpcode)."', '".date("Y-m-d H:i:s")."', '".date("Y-m-d H:i:s")."', '".addslashes($v_affurl)."')";
							$objProgram->objMysql->query($sql);
							$cnt++;
						}



					}elseif($domain != $link["domain"]){
						if($affid == 578 || $affid == 223 || $affid == 191) continue;

						$sql = "select subdomain, domainname from domain where domain = '".addslashes($domain)."'";
						$tmp_arr = array();
						$tmp_arr = $objProgram->objMysql->getFirstRow($sql);

						if(!count($tmp_arr)){
							if (preg_match("/([^\.]*)(" . implode("|", $topDomain) . ")$/mi", $domain)) {
								$objProgram->insertDomain(array($domain));
								$sql = "select subdomain, domainname from domain where domain = '" . addslashes($domain) . "'";
								$tmp_arr = array();
								$tmp_arr = $objProgram->objMysql->getFirstRow($sql);
								
								if (!count($tmp_arr)) continue;
							}
						}

						$new_domain = preg_replace("/^".$tmp_arr["subdomain"]."\\./i", "", $domain);
						$link_domain = preg_replace("/^".$link["subdomain"]."\\./i", "", $link["domain"]);
						if($new_domain == $link_domain || stripos($link_domain, $tmp_arr["domainname"]) !== false){
							$sql = "update t_check_p_d_links set status = 'Ignored', remark = 'self fixed', editor = 'System', lastupdatetime = '".date("Y-m-d H:i:s")."' where (status = 'new' or status = 'assigned') and programid = {$link["programid"]} and domainid = {$link["domainid"]}  and checkurl = '".addslashes($v_affurl)."' ";
							$objProgram->objMysql->query($sql);
	
							echo "\t". ++$jj;
						
						}else{

							$httpcode = "888".$finalUrl;
							if($k_type == "affdefaulturl"){
								$errortype = 1;
							}else{
								$errortype = 11;
							}
	
							$sql = "select id from t_check_p_d_links where programid = {$link["programid"]} and domainid = {$link["domainid"]}  and checkurl = '".addslashes($v_affurl)."' and errortype in (1,11,0) and (status = 'new' or status = 'assigned' ) limit 1";
							$tmp_arr = array();
							$tmp_arr = $objProgram->objMysql->getFirstRow($sql);
							if(!count($tmp_arr)){
								$sql = "insert ignore into t_check_p_d_links (programid, domainid, status, errortype, errorvalue, addtime, lastupdatetime, checkurl)
										values({$link["programid"]}, {$link["domainid"]}, 'New', '$errortype', '".addslashes($httpcode)."', '".date("Y-m-d H:i:s")."', '".date("Y-m-d H:i:s")."', '".addslashes($v_affurl)."')";
								$objProgram->objMysql->query($sql);
								$cnt++;
							}
						}
					}else{
						if($affid == 578){
							$sql = "update program_manual set StatusInBdg = 'Unknown' where programid = '{$link["programid"]}' and StatusInBdg = 'Inactive'";
							$objProgram->objMysql->query($sql);
						}

						$sql = "update t_check_p_d_links set status = 'Ignored', remark = 'self fixed', editor = 'System', lastupdatetime = '".date("Y-m-d H:i:s")."' where (status = 'new' or status = 'assigned') and programid = {$link["programid"]} and domainid = {$link["domainid"]}  and checkurl = '".addslashes($v_affurl)."' ";
						$objProgram->objMysql->query($sql);

						echo "\t". ++$jj;
					}

				}else{

					if($affid == 223 || $affid == 191) continue;

					$domain_arr = $objProgram->getDomainByHomepage($finalUrl, "fi");

					if(!count($domain_arr)){
						echo "\r\n no domain p:".$link["programid"] . '[' .$httpcode . ']' . $finalUrl. "\r\n";
						continue;
					}
					//print_r($domain_arr);
					$domain = current($domain_arr["domain"]);


					if($domain != $link["domain"]){
						if($affid == 578 && $httpcode == 0 && strpos($finalUrl, '.tradedoubler.com') !== false){
							$sql = "update program_manual set StatusInBdg = 'Unknown', lastupdatetime = '".date("Y-m-d H:i:s")."' where programid = '{$link["programid"]}' and StatusInBdg = 'Inactive'";
							$objProgram->objMysql->query($sql);
							$sql = "update t_check_p_d_links set status = 'Ignored', remark = 'self fixed', editor = 'System', lastupdatetime = '".date("Y-m-d H:i:s")."' where (status = 'new' or status = 'assigned') and programid = {$link["programid"]} and domainid = {$link["domainid"]}  and checkurl = '".addslashes($v_affurl)."' ";
							$objProgram->objMysql->query($sql);
							continue;
						}

						if($affid == 578 && $httpcode != 503){
							$sql = "insert into program_manual (programid, StatusInBdg) values ('{$link["programid"]}', 'Inactive') on duplicate key update StatusInBdg = 'Inactive' , lastupdatetime = '".date("Y-m-d H:i:s")."'";
							$objProgram->objMysql->query($sql);
						}

						$httpcode = intval($httpcode).$finalUrl;

						if($k_type == "affdefaulturl"){
							$errortype = 1;
						}else{
							$errortype = 11;
						}

						$sql = "select id from t_check_p_d_links where programid = {$link["programid"]} and domainid = {$link["domainid"]} and checkurl = '".addslashes($v_affurl)."' and errortype in (1,11,0) and (status = 'new' or status = 'assigned' ) limit 1";
						$tmp_arr = array();
						$tmp_arr = $objProgram->objMysql->getFirstRow($sql);
						if(!count($tmp_arr)){
							$sql = "insert ignore into t_check_p_d_links (programid, domainid, status, errortype, errorvalue, addtime, lastupdatetime, checkurl)
										values({$link["programid"]}, {$link["domainid"]}, 'New', '$errortype', '".addslashes($httpcode)."', '".date("Y-m-d H:i:s")."', '".date("Y-m-d H:i:s")."', '".addslashes($v_affurl)."')";
							$objProgram->objMysql->query($sql);
							$cnt++;
						}
					}else{
						if($affid == 578){
							$sql = "update program_manual set StatusInBdg = 'Unknown', lastupdatetime = '".date("Y-m-d H:i:s")."' where programid = '{$link["programid"]}' and StatusInBdg = 'Inactive'";
							$objProgram->objMysql->query($sql);
						}

						$sql = "update t_check_p_d_links set status = 'Ignored', remark = 'self fixed', editor = 'System', lastupdatetime = '".date("Y-m-d H:i:s")."' where (status = 'new' or status = 'assigned') and programid = {$link["programid"]} and domainid = {$link["domainid"]}  and checkurl = '".addslashes($v_affurl)."' ";
						$objProgram->objMysql->query($sql);

						echo "\t". ++$jj;
					}
				}
			}
			if($is_debug){
				echo "\r\ncnt:".$cnt."\r\n";//exit;
			}
			//if($cnt > 0)exit;
		}
	}

	echo "||$cnt\r\n ";
}

echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";
exit;

function clean_parm($url, $tpl){
	return getDeepUrl($url, $tpl);
}

function getDeepUrl($strDeepUrl, $strDeepTpl){
	$result = $strDeepTpl;

	if (stripos($result, 'shareasale.com')) {
		$nTmp = stripos($strDeepUrl, 'http://');
		if ($nTmp !== false) {
			$strDeepUrl = substr($strDeepUrl, $nTmp + 7);
		} else {
			$nTmp = stripos($strDeepUrl, urlencode('http://'));
			if ($nTmp !== false) {
			    $strDeepUrl = substr($strDeepUrl, strlen(urlencode('http://')));
			}
    	}
	}

	$mark_and = '&';
	$mark_que = '?';
	$has_deep_mark = false;
	if (preg_match('/(.*)\[(PURE_DEEPURL|DEEPURL|DOUBLE_ENCODE_DEEPURL|URI|ENCODE_URI|DOUBLE_ENCODE_URI)\](\[\?\|&\])*/', $result, $m)) {

	    preg_match('/^http(s)?:\/\/[^\/]+(\/)?(.*)/', $strDeepUrl, $q);
	    $has_deep_mark = @$m[3] != ''? true : $has_deep_mark;

	    switch ($m[2]) {
	case 'PURE_DEEPURL':
	    $result = str_ireplace('[PURE_DEEPURL]', $strDeepUrl, $result);
	    break;
	case 'DEEPURL':
	    $result = str_ireplace('[DEEPURL]', ($m[1] == ''? $strDeepUrl: urlencode($strDeepUrl)), $result);
	    if (@$m[3] == '[?|&]' && $m[1] != '') {
			$mark_and = urlencode($mark_and);
			$mark_que = urlencode($mark_que);
	    }
	    break;
	case 'DOUBLE_ENCODE_DEEPURL':
	    $result = str_ireplace('[DOUBLE_ENCODE_DEEPURL]', ($m[1] == ''? $strDeepUrl : urlencode(urlencode($strDeepUrl))), $result);
	    if ($m[3] == '[?|&]' && $m[1] != '') {
			$mark_and = urlencode(urlencode($mark_and));
			$mark_que = urlencode(urlencode($mark_que));
	    }
	    break;
	case 'URI':
	    $result = preg_replace('/([^:])\/{2,}/', '\1/', str_ireplace('[URI]', '/'.(isset($q[3]) && $q[3] != ''? $q[3] : ''), $result));
	    break;
	case 'ENCODE_URI':
	    $result = preg_replace('/([^:])\/{2,}/', '\1/',  str_ireplace('[ENCODE_URI]', urlencode('/'.(isset($q[3]) && $q[3] != ''? $q[3] : '')), $result));
	    if ($m[3] == '[?|&]' && $m[1] != '') {
			$mark_and = urlencode($mark_and);
			$mark_que = urlencode($mark_que);
	    }
	    break;
	case 'DOUBLE_ENCODE_URI':
	    $result = preg_replace('/([^:])\/{2,}/', '\1/',  str_ireplace('[DOUBLE_ENCODE_URI]', urlencode(urlencode('/'.(isset($q[3]) && $q[3] != ''? $q[3] : ''))), $result));
	    if ($m[3] == '[?|&]' && $m[1] != '') {
			$mark_and = urlencode(urlencode($mark_and));
			$mark_que = urlencode(urlencode($mark_que));
	    }
	    break;
	    }
	}

	$m = array();
	if (preg_match('/(.*)(\[\?\|&\].*)/', $result, $m)) { //&& $start_w_tpl
	    if ($has_deep_mark) {
			$m[1] = $strDeepUrl;
	    }

	    if (preg_match('/[\?&][^&]+=[^&]*/U', $m[1]))
			$result = str_replace('[?|&]', $mark_and, $result);
		else
			$result = str_replace('[?|&]', $mark_que, $result);
	}

	$result = str_ireplace('[SUBTRACKING]', 't', $result);
	$result = str_ireplace('[SITEIDINAFF]', '2567387', $result);
	return $result;
}

function browser_url($url){
    $res_str = '';
    $res_str = getUrlLocation($url);
    list($finalUrl,$httpcode) = explode("\t",$res_str);

    if(empty($finalUrl)){
        $curl_res = getHttpRes($url);
        if($curl_res['hasResponse']){
            $finalUrl = $curl_res['finalUrl'];
            $httpcode = $curl_res['httpcode'];
        }
    }elseif($httpcode != '200' && $httpcode != '304' && $httpcode != '404'){
        $curl_res = getHttpRes($finalUrl);
        if($curl_res['hasResponse']){
            $finalUrl = $curl_res['finalUrl'];
            $httpcode = $curl_res['httpcode'];
        }
    }

    return $finalUrl."\t".$httpcode;
}

function getUrlLocation($url){
        if(!$url){
                echo 'null';
        }
        $cmd = "phantomjs --ignore-ssl-errors=yes /home/bdg/plugin/checkurl.js '".$url."'";
        exec($cmd,$req);

        $flag = 0;
        $res = '';
        foreach($req as $k=>$v){
                $str = trim($v);
                if(empty($str))
                        continue;

                if($v == '---start print---'){
                        $flag = 1;
                        continue;
                }

                if($v == '---end print---'){
                        $flag = 0;
                        continue;
                }

                if($flag){
                        $res = $v;
                }
        }
        return $res;
}

function getHttpRes($url = '',$debug=0, $affid=0){

    $data = array();

    $finalUrl = '';
    $httpcode = '';
    $hasResponse = 0;
    $isLocation = 0;
    $response = '';

    if($url){
        $url = str_replace("\n",'',$url);
        $url = str_replace("\r",'',$url);
        $url = str_replace("&amp;",'&',$url);

        $ch = curl_init();
       
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER , 1);
        curl_setopt($ch, CURLOPT_HEADER , 1);
        if(in_array($affid, array(191,223,578,29,152,32,240,164,7,26,635,573,472,548,503,22,115,133,5,35,133,469,52,65,425,426,427,163))){	// check body if has JS or META jump
        	curl_setopt($ch, CURLOPT_NOBODY , 0);
        }else{
        	curl_setopt($ch, CURLOPT_NOBODY , 1);
        }
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION , 1);
        curl_setopt($ch, CURLOPT_TIMEOUT , 20);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
#        curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST , 2);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36");
        curl_setopt($ch, CURLOPT_COOKIEFILE, '/home/bdg/program/test/a.cookie');
        curl_setopt($ch, CURLOPT_COOKIEJAR, '/home/bdg/program/test/a.cookie');
        $response = curl_exec($ch);
		$fail = curl_error($ch);

    	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

        curl_close($ch);

        $response = trim($response);
        if(!empty($response))
            $hasResponse = 1;


    }


    $data['finalUrl'] = $finalUrl?$finalUrl:$url;
    $data['hasResponse'] = $hasResponse;
    $data['httpcode'] = $httpcode;
    if($debug){
		$data['response'] = $response;
		$data['fail'] = $fail;
    }
    return $data;
}

function checkSpecialAff($affid, $url){
	$return_val = $js_url = '';
	$tmp_arr = array();
	switch($affid){
		case 578:
			$tmp_arr = getHttpRes($url, 1, $affid);
			if(strpos($tmp_arr['finalUrl'], 'srvtrck.com') !== false){
				preg_match("/window\.location\.replace\((\"|')(.*)\\1/i", $tmp_arr['response'], $m);
				if(count($m) && strlen($m[2])){
					$js_url = $m[2];
				}
			}
			break;
		case 223:
			$tmp_arr = getHttpRes($url, 1, $affid);
			if(strpos($tmp_arr['finalUrl'], 'redirectingat.com') !== false){
				preg_match("/<iframe.*?src=(\"|')(.*)\\1/i", $tmp_arr['response'], $m);
				if(count($m) && strlen($m[2])){
					$js_url = $m[2];
				}
			}
			break;
		case 191:
			$tmp_arr = getHttpRes($url, 1, $affid);
			if(strpos($tmp_arr['finalUrl'], 'viglink.com') !== false){
				preg_match("/<iframe.*?src=(\"|')(.*)\\1/i", $tmp_arr['response'], $m);
				if(count($m) && strlen($m[2])){
					$js_url = $m[2];
				}else{
					preg_match("/<meta[^>]*http-equiv=[\"']refresh[\"'][^>]*?url=([^\"']*)/i", $tmp_arr['response'], $m);

					if(count($m) && strlen($m[1])){
						$js_url = $m[1];
					}
				}
			}
			break;
		default:
			$tmp_arr = getHttpRes($url, 1, $affid);
			
			preg_match("/<meta[^>]*http-equiv=[\"']refresh[\"'][^>]*?url=([^\"']*)/i", $tmp_arr['response'], $m);
			//print_r($m);exit;
			if(count($m) && strlen($m[1])){
				$js_url = $m[1];
			}else{
				preg_match("/window\.location\.replace\((\"|')(.*)\\1/i", $tmp_arr['response'], $m);
				if(count($m) && strlen($m[1])){
					$js_url = $m[1];
				}
			}

			if(strlen($js_url) && strpos($js_url, 'http') !== 0){
				preg_match("/(https?:\\/\\/[^\\/]*)\\//i", $url, $m);
				if(count($m) && strlen($m[1])){
					$js_url = $m[1].'/'.$js_url;
				}
			}
			break;
	}

	$js_url = htmlspecialchars_decode($js_url);
	global $is_debug;
	if($is_debug){
		echo $tmp_arr['response'];
		echo "\r\n\t[".$js_url."]\t(".$url.")\r\n";
	}
	if(strlen($js_url)){
		if(strpos($js_url, 'shareasale.com') !== false){
			preg_match("/window\.location\.replace\((\"|')(.*)\\1/i", $tmp_arr['response'], $m);
			if(count($m) && strlen($m[2])){
				$js_url = $m[2];
				$tmp_arr = getHttpRes($js_url, 0, $affid);
				$return_val = $tmp_arr['finalUrl']."\t".$tmp_arr['httpcode'];
			}
		}else{
			$js_url = followAffUrl($js_url, $tmp_arr['response'], $affid);
			$tmp_arr = getHttpRes($js_url, 0, $affid);
			$return_val = $tmp_arr['finalUrl']."\t".$tmp_arr['httpcode'];
		}
	}else{
		if(strpos($tmp_arr['finalUrl'], 'scripts.affiliatefuture.com') !== false
				 || strpos($tmp_arr['finalUrl'], 'linkconnector.com') !== false
				 || strpos($tmp_arr['finalUrl'], 'affiliatetechnology.com') !== false
				 || strpos($tmp_arr['finalUrl'], 'omguk.com') !== false
				 || strpos($tmp_arr['finalUrl'], 'action.metaffiliation.com') !== false
				 || strpos($tmp_arr['finalUrl'], 'clk.tradedoubler.com') !== false
				 || strpos($tmp_arr['finalUrl'], 'partners.webmasterplan.com') !== false
				 || strpos($tmp_arr['finalUrl'], 'clkuk.tradedoubler.com') !== false
			)
		{
			$return_val = followAffUrl($tmp_arr['finalUrl'], '', $affid)."\t".$tmp_arr['httpcode'];
		}else{
			$return_val = $tmp_arr['finalUrl']."\t".$tmp_arr['httpcode'];
		}
	}
	if($affid == 578){
		$tmp_arr = getTrueUrl($return_val);		
		return $tmp_arr['final_url']."\t".$tmp_arr['http_code'];
		
	}else{
		return $return_val;
	}
}

function followAffUrl($url, $body = '', $affid = 0){
	$return_url = '';
	if(strpos($url, 'shareasale.com') !== false){
		preg_match("/window\.location\.replace\((\"|')(.*)\\1/i", $body, $m);
		if(count($m) && strlen($m[2])){
			$return_url = $m[2];
		}
	}elseif(strpos($url, 'scripts.affiliatefuture.com') !== false){
		$tmp_arr = getHttpRes($url, 1, $affid);
		$body = $tmp_arr['response'];
		preg_match("/url[\s]=[\s](\"|')(.*)\\1/i", $body, $m);
		if(count($m) && strlen($m[2])){
			$return_url = $m[2];
			$body = '';
		}
	}elseif(strpos($url, 'linkconnector.com') !== false){
		$tmp_arr = getHttpRes($url, 1, $affid);
		$body = $tmp_arr['response'];
		preg_match("/window\.location\.replace\((\"|')(.*)\\1/i", $body, $m);
		//print_r($m);
		if(count($m) && strlen($m[2])){
			$return_url = $m[2];
			$body = '';
		}
	}elseif(strpos($url, 'affiliatetechnology.com') !== false
			|| strpos($url, 'omguk.com') !== false
			|| strpos($url, 'action.metaffiliation.com') !== false
			|| strpos($url, 'clk.tradedoubler.com') !== false
			|| strpos($url, 'partners.webmasterplan.com') !== false
			|| strpos($url, 'clkuk.tradedoubler.com') !== false
		){
		$tmp_arr = getHttpRes($url, 1, $affid);
		//print_r($tmp_arr);exit;
		$body = $tmp_arr['response'];
		preg_match("/<meta[^>]*http-equiv=[\"']refresh[\"'][^>]*?url=([^\"']*)/i", $body, $m);
		//print_r($m);exit;
		if(count($m) && strlen($m[1])){
			$return_url = $m[1];
			$body = '';
		}
		if(strlen($return_url) && strpos($return_url, 'http') !== 0){
			$finalUrl = $tmp_arr['finalUrl'];
			preg_match("/(https?:\\/\\/[^\\/]*)\\//i", $finalUrl, $m);
			if(count($m) && strlen($m[1])){
				$return_url = $m[1].'/'.$return_url;
			}
		}
	}else{
		$tmp_arr = getHttpRes($url, 1, $affid);
		$body = $tmp_arr['response'];
		preg_match("/<meta[^>]*http-equiv=[\"']refresh[\"'][^>]*?url=([^\"']*)/i", $body, $m);
		if(count($m) && strlen($m[1])){
			$return_url = $m[1];
			$body = '';
		}
		if(strlen($return_url) && strpos($return_url, 'http') !== 0){
			$finalUrl = $tmp_arr['finalUrl'];
			preg_match("/(https?:\\/\\/[^\\/]*)\\//i", $finalUrl, $m);
			if(count($m) && strlen($m[1])){
				$return_url = $m[1].'/'.$return_url;
			}
		}
	}

	if($return_url && $return_url != $url) return followAffUrl($return_url, $body, $affid);
	else return $url;
}

function checkProcess($process_name, $force_process_cnt = 0){
	$cmd = `ps aux | grep $process_name | grep grep -v | grep 'child' -c`;
	$return = ''.$cmd.'';
	$return = intval($return);

	if(intval($force_process_cnt) > 0 && $return > (int)$force_process_cnt){
		return false;
	}elseif($return > PROCESS_CNT){
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
		if(@intval($yy[0])){
			echo system("kill ".$yy[0]);
		}
	}
}

?>
