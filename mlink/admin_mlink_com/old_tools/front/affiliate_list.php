<?php
//if(!defined("SPECIAL_HTTP_HOST")) define("SPECIAL_HTTP_HOST","task");
include_once(dirname(dirname(__FILE__))."/etc/const.php");
include_once(INCLUDE_ROOT . "lib/Class.TemplateSmarty.php");
include_once(INCLUDE_ROOT . "lib/Class.Mysql.php");
include_once(INCLUDE_ROOT . "lib/Class.MyException.php");
include_once(INCLUDE_ROOT . "lib/Class.Request.php");
include_once(INCLUDE_ROOT . "func/gpc.func.php");
include_once(INCLUDE_ROOT . "lib/Class.Affiliate.php");

$tpl = new TemplateSmarty();
$resobj = new Request();
$objMysql = new Mysql(TASK_DB_NAME, TASK_DB_HOST, TASK_DB_USER, TASK_DB_PASS);
$affiliate_model = new Affiliate($objMysql);
$isactive_arr = array('YES' => 'YES', 'NO' => 'NO');
$tpl->assign("isactive_arr", $isactive_arr);

$action = trim($resobj->getStrNoSlashes("action"));
$condition = array();

switch ($action) {
	case "add":
		$type_arr = array('NO' => 'Network', 'YES' => 'InHouse');
        $tpl->assign("type_arr", $type_arr);
		$fin_rev_acc_list = $affiliate_model->getFinRevAccList();
        $tpl->assign("fin_rev_acc_list", $fin_rev_acc_list);
        $program_crawled_arr = array('NO' => 'NO', 'YES' => 'YES', 'No Need to Crawl' => 'No Need to Crawl', 'Request to Crawl' => 'Request to Crawl', 'Can Not Crawl' => 'Can Not Crawl');
        $stats_report_crawled_arr = array('NO' => 'NO', 'YES' => 'YES', 'No Need to Crawl' => 'No Need to Crawl', 'Request to Crawl' => 'Request to Crawl', 'Can Not Crawl' => 'Can Not Crawl');
        $tpl->assign("countrySel", $countrySel);
        $tpl->assign("countries", $affiliate_model->country_arr);
        $tpl->assign("revacc", "12");
        $tpl->assign("program_crawled_arr", $program_crawled_arr);
        $tpl->assign("stats_report_crawled_arr", $stats_report_crawled_arr);
		$tpl->display("affiliate_add.tpl");
		break;
	case "addfinish":
		$fields = array('name','shortname','domain','blog','facebook','twitter','proidinnetword','affurlkw','affurlkw2','subtrackingset','subtrackingset2','isinhouse',
						'isactive','deepurlparaname','RevenueAccount','RevenueCycle','RevenueRemark','ProgramCrawled','ProgramCrawlRemark','StatsReportCrawled','StatsReportCrawlRemark',
						'StatsAffiliateName','ImportanceRank','ProgramUrlTemplate', 'loginurl', 'SupportDeepUrl', 'SupportSubTracking', 'joindate', 'Comment');
		$post = array();
		$counties = $_REQUEST["countries"];
		$countyStr = "";
		foreach ((array)$counties as $val){
			if($countyStr == ""){
				$countyStr .= $val;
			}else{
				$countyStr = $countyStr . "||" . $val;
			}
		}
		foreach($fields as $name)
		{
			$post[$name] = trim(get_post_var($name));
			if (in_array($name, array('RevenueRemark', 'Comment'))) {
				if (!empty($post[$name])) {
					$post[$name] = "=====\r\n" . date('Y-m-d H:i:s') . " " . (isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : "beta") . "\r\n" . addslashes($post[$name]) . "\r\n";
				}
			}
		}
		$post["counties"] = $countyStr;
		if (!checkSubtrackingSet($post['subtrackingset']) || !checkSubtrackingSet($post['subtrackingset2'])) {
			echo "<Script Language=\"Javascript\">alert('Sub Tracking Setting are invalid!');</Script>";
		    echo "<Script Language=\"Javascript\">history.back();</Script>";
		    exit;
		}
		
		$res = $affiliate_model->addAffiliate($post);
		$res = implode('\n\n', $res);
		
		//前台站点数据更新
		echo "<Script Language=\"Javascript\">alert('" . $res . "');</Script>";
		echo "<Script Language=\"Javascript\">window.location='affiliate_list.php';</Script>";
		break;
	case "edit":
		$id = intval(trim($resobj->getStrNoSlashes("id")));
		$type_arr = array('NO' => 'Network', 'YES' => 'InHouse');
        $tpl->assign("type_arr", $type_arr);
        $fin_rev_acc_list = $affiliate_model->getFinRevAccList();
        $tpl->assign("fin_rev_acc_list", $fin_rev_acc_list);
        $program_crawled_arr = array('NO' => 'NO', 'YES' => 'YES', 'No Need to Crawl' => 'No Need to Crawl', 'Request to Crawl' => 'Request to Crawl', 'Can Not Crawl' => 'Can Not Crawl');
        $stats_report_crawled_arr = array('NO' => 'NO', 'YES' => 'YES', 'No Need to Crawl' => 'No Need to Crawl', 'Request to Crawl' => 'Request to Crawl', 'Can Not Crawl' => 'Can Not Crawl');
        $tpl->assign("program_crawled_arr", $program_crawled_arr);
        $tpl->assign("stats_report_crawled_arr", $stats_report_crawled_arr);
        $data = $affiliate_model->getAffilicateById($id);
        if (empty($data)) {
        	echo "<Script Language=\"Javascript\">alert('The affiliate is not exists');</Script>";
			echo "<Script Language=\"Javascript\">history.back(-1);</Script>";
			exit;
        }
        if($data["RevenueAccount"] == "0" || trim($data["RevenueAccount"]) == ""){
        	$data["RevenueAccount"] = "12";
        }
        
        $countrySel = explode("||", $data["Country"]);
        $data['joindate_format'] = (!empty($data['JoinDate']) && $data['JoinDate'] != '0000-00-00 00:00:00') ? date('Y-m-d', strtotime($data['JoinDate'])) : '';
        
        $tpl->assign("data", $data);
        $tpl->assign("countrySel", $countrySel);
        $tpl->assign("countries", $affiliate_model->country_arr);
		$tpl->display("affiliate_edit.tpl");
		break;
	case "editfinish":
		$counties = $_REQUEST["countries"];
		$countyStr = "";
		if(!isset($_REQUEST["countries"])){
			$counties = array();
		}
		foreach ($counties as $val){
			if($countyStr == ""){
				$countyStr .= $val;
			}else{
				$countyStr = $countyStr . "||" . $val;
			}
		}
		$fields = array('name','shortname','domain','blog','facebook','twitter','proidinnetword','affurlkw','affurlkw2','subtrackingset','subtrackingset2','isinhouse','isactive',
						'deepurlparaname','RevenueAccount','RevenueCycle','RevenueRemark','ProgramCrawled','ProgramCrawlRemark','StatsReportCrawled','StatsReportCrawlRemark','StatsAffiliateName',
						'ImportanceRank','ProgramUrlTemplate','id', 'loginurl', 'SupportDeepUrl', 'SupportSubTracking', 'joindate', 'Comment');
		$post = array();
		foreach($fields as $name)
		{
			$post[$name] = trim(get_post_var($name));
			if (in_array($name, array('RevenueRemark', 'Comment'))) {
				if (!empty($post[$name])) {
					$post[$name] = "=====\r\n" . date('Y-m-d H:i:s') . " " . (isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : "beta") . "\r\n" . addslashes($post[$name]) . "\r\n";
				}
			}
		}
		$post["counties"] = $countyStr;
		$oldAffArr = $affiliate_model->getAffilicateById($post["id"]);
		if (!checkSubtrackingSet($post['subtrackingset']) || !checkSubtrackingSet($post['subtrackingset2'])) {
			echo "<Script Language=\"Javascript\">alert('Sub Tracking Setting are invalid!');</Script>";
		    echo "<Script Language=\"Javascript\">history.back();</Script>";
		    exit;
		}
		
		$user = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : "";
		if(!$user && substr($_SERVER["REMOTE_ADDR"],0,8) == "192.168.") $user = "couponsn";
		$affiliate_model->write_table_change_log($post,$user);
		$res = $affiliate_model->updateAffiliate($post);
		$res = implode('\n\n', $res);
		$logRes = $affiliate_model->insertAffiliateChangeLog($post["id"], $oldAffArr, $reason = "", $source = "");
		//前台站点数据更新
		echo "<Script Language=\"Javascript\">alert('" . $res . "');</Script>";
		echo "<Script Language=\"Javascript\">window.opener.location.reload();</Script>";
	    echo "<Script Language=\"Javascript\">self.close();</Script>";
		break;
	case "sync":
		set_time_limit(0);
		foreach ($syncAffiliateSiteUrl as $k => $url) {
			$res[$k] = file_get_contents($url);
		}
		echo "Return Messages:<br />";
		foreach ($res as $k1 => $v1) {
		    $tmp = json_decode($v1);
		    $tmp = (array)$tmp;
		    echo $k1 . ":<br />";
		    if (empty($tmp)) echo "<font color=red>Synchronous Fail, Please contact the PD department heads immediately (William / Ike)</font><br />";
			foreach ($tmp as $k2 => $v2) {
				if ($v2->status == 1) {
					echo $k2 . ": " . "<font color=green>Synchronous Success</font> <a href='" . $v2->url . "' target='_blank'>view</a><br />";
				} else {
					echo $k2 . ": " . "<font color=red>Synchronous Fail, Please contact the PD department heads immediately (William / Ike)</font><br />"; 
				}
			}
			echo "<br />";
		}
		break;
	case "export";
	    set_time_limit(0);
	    $filepath = INCLUDE_ROOT . 'data' . DIRECTORY_SEPARATOR;
	    
		if (!is_dir($filepath)) {
			mkdir($filepath);
			chmod($filepath, 0777);
		}
	    
	    $t_affurl_name = $filepath . "t_affurl.txt";
	    $t_affdeepurl_name = $filepath . "t_affdeepurl.txt";
	    $t_affsid_name = $filepath . "t_affsid.txt";
	    
	    $as_affurl_name = $filepath . "as_affurl.txt";
	    $as_affdeepurl_name = $filepath . "as_affdeepurl.txt";
	    $as_affsid_name = $filepath . "as_affsid.txt";
	    
	    
	    $t_affurl_fp = fopen($t_affurl_name, 'wb');
	    $t_affdeepurlfp = fopen($t_affdeepurl_name, 'wb');
	    $t_affsid_fp = fopen($t_affsid_name, 'wb');
	    
	    $as_affsid_fp = fopen($as_affsid_name, 'wb');
	    
	    
	    $t_affurl_str = "#####################################\r\n#AffiliateUrl Keyword List\r\n#example: (comments with #)\r\n#affiliateid~name\tvalue\r\n#####################################\r\n\r\n\r\n\r\n\r\n";
	    $t_affdeepurl_str = "#####################################\r\n#AffiliateDeepUrlKeyword List\r\n#example: (comments with #)\r\n#affiliateid~name\tvalue\r\n#####################################\r\n\r\n\r\n\r\n\r\n";
	    $t_affsid_str = "##################################\r\n#Sub Tracking Setting\r\n#example: (comments with #)\r\n#affiliateid~name\tvalue\r\n#####################################\r\n\r\n\r\n\r\n\r\n";
	    $as_affsid_str = "##################################\r\n#Sub Tracking Setting2\r\n#example: (comments with #)\r\n#affiliateid~name\tvalue\r\n#####################################\r\n\r\n\r\n\r\n\r\n";
	    
		$condition = array();
	    $condition['isactive'] = 'YES';
	    $data = $affiliate_model->getAffiliates($condition);
	    
	    foreach ($data as $val) {
	    	$affurl_arr = preg_split("/[\r\n]+/", trim($val['AffiliateUrlKeywords']), -1, PREG_SPLIT_NO_EMPTY);
	    	$val['Name'] = preg_replace('/\s+/is', '_', trim($val['Name']));
	    	foreach ((array)$affurl_arr as $v) {
	    		if (empty($v)) continue;
	    		$t_affurl_str .= $val['ID'] . "~" . $val['Name'] . "\t" . $v . "\r\n";
	    	}
	    	if (!empty($val['DeepUrlParaName'])) {
	    		$t_affdeepurl_str .= $val['ID'] . "~" . $val['Name'] . "\t" . $val['DeepUrlParaName'] . "\r\n";
	    	}
	    	if (!empty($val['SubTracking'])) {
	    		$t_affsid_str .= $val['ID'] . "~" . $val['Name'] . "\t" . $val['SubTracking'] . "\r\n";
	    	}
	    	if (!empty($val['SubTracking2'])) {
	    	    $as_affsid_str .= $val['ID'] . "~" . $val['Name'] . "\t" . $val['SubTracking2'] . "\r\n";
	    	}
	    	
	    }
	    $t_affurl_str .= "#megainfo";
	    $t_affdeepurl_str .= "#megainfo";
	    $t_affsid_str .= "#megainfo";
	    $as_affsid_str .= "#megainfo";
	    
	    fwrite($t_affurl_fp, $t_affurl_str);
	    fwrite($t_affdeepurlfp, $t_affdeepurl_str);
	    fwrite($t_affsid_fp, $t_affsid_str);
	    fwrite($as_affsid_fp, $as_affsid_str);
	    
	    fclose($t_affurl_fp);
	    fclose($t_affdeepurlfp);
	    fclose($t_affsid_fp);
	    fclose($as_affsid_fp);
	    
	    copy($t_affurl_name, $as_affurl_name);
	    copy($t_affdeepurl_name, $as_affdeepurl_name);
	    
	    unset($data);
	    echo "export successfully<br /><br />";
	    echo  "The files follows: <br />t_affurl.txt <a href='/data/t_affurl.txt' target='_blank'>view</a><br /> t_affdeepurl.txt <a href='/data/t_affdeepurl.txt' target='_blank'>view</a><br /> t_affsid.txt <a href='/data/t_affsid.txt' target='_blank'>view</a><br /><br />";
	    echo "as_affurl.txt <a href='/data/as_affurl.txt' target='_blank'>view</a><br /> as_affdeepurl.txt <a href='/data/as_affdeepurl.txt' target='_blank'>view</a><br /> as_affsid.txt <a href='/data/as_affsid.txt' target='_blank'>view</a>";
	    exit;
	    break;
	case "updateall":
		$res = $affiliate_model->updateAllSites();
		$res = implode('\n\n', $res);
		
		echo "<Script Language=\"Javascript\">window.location='http://task.megainformationtech.com/front/affiliate_list.php';</Script>";
	    break;
	default:
		$onepage = intval($_GET["onepage"]);
		if(empty($onepage)){
			$perpage = intval($resobj->getStrNoSlashes("onepage"));
		}else{
			$perpage = $onepage;
		}
		if ($perpage < 1 || $perpage > 500 || empty($perpage)) $perpage = 500;
		setcookie("onepage", $perpage, time()+60*60*24*30);		
		$_COOKIE['onepage'] = $perpage;
		
		$tpl->assign("perpage", $perpage);
		$fin_rev_acc_list = $affiliate_model->getFinRevAccList();
        $tpl->assign("fin_rev_acc_list", $fin_rev_acc_list);
		$type_arr = array('All' => 'All', 'NO' => 'Network', 'YES' => 'InHouse');
		$isactive_all_arr = array('All' => 'All', 'YES' => 'YES', 'NO' => 'NO');
		$program_crawled_arr = array('All' => 'All', 'YES' => 'YES', 'NO' => 'NO', 'No Need to Crawl' => 'No Need to Crawl', 'Request to Crawl' => 'Request to Crawl', 'Can Not Crawl' => 'Can Not Crawl');
        $tpl->assign("type_arr", $type_arr);
       
        $tpl->assign("isactive_all_arr", $isactive_all_arr);
        $tpl->assign("program_crawled_arr", $program_crawled_arr);
        
        
		$condition['type'] = $type = trim($resobj->getStrNoSlashes("type"));
		$countrySel = trim($resobj->getStrNoSlashes("country"));
		
		$condition['name'] = $name = trim($resobj->getStrNoSlashes("name"));
		$condition['affurlkw'] = $affurlkw = trim($resobj->getStrNoSlashes("affurlkw"));
		$isactive = trim($resobj->getStrNoSlashes("isactive"));
		if (empty($isactive)) $isactive = "YES";
		if ($isactive != 'All') {
			$condition['isactive'] = $isactive;
		}
		$condition['RevenueAccount'] = $RevenueAccount = trim($resobj->getStrNoSlashes("RevenueAccount"));
		$ProgramCrawled = trim($resobj->getStrNoSlashes("ProgramCrawled"));
		if (empty($ProgramCrawled)) $ProgramCrawled = "All";
		if ($ProgramCrawled != 'All') {
			$condition['ProgramCrawled'] = $ProgramCrawled;
		}
		if ($countrySel != 'ALL') {
			$condition['countrySel'] = $countrySel;
		}
		$count = $affiliate_model->getAffiliateCount($condition);
		
		include_once(INCLUDE_ROOT . "lib/Class.Page.php");
		$objPB = new OPB($count, $perpage);
		$objPB->onepage = $perpage;
		$pagebar = $objPB->whole_bar(3, 8);
		$pagebar1 = $objPB->whole_bar(4, 8);
		
		$condition['orderby'] = " order by `ImportanceRank` asc ";
		$condition['limit'] = "limit " . $objPB->offset . ", " . $perpage;
		$data = $affiliate_model->getAffiliates($condition);
		
		foreach ($data as $k => $v) {
			if ($v['IsInHouse'] == 'YES') $data[$k]['type_format'] = 'InHouse';
			else $data[$k]['type_format'] = 'Network';
			$data[$k]['AffiliateUrlKeywords_format'] = nl2br($data[$k]['AffiliateUrlKeywords']);
			$data[$k]['AffiliateUrlKeywords2_format'] = nl2br($data[$k]['AffiliateUrlKeywords2']);
			if (isset($v['BlogUrl']) && !empty($v['BlogUrl'])) $data[$k]['go_BlogUrl'] = get_ssl_rd_url(trim($v['BlogUrl']));
			if (isset($v['FacebookUrl']) && !empty($v['FacebookUrl'])) $data[$k]['go_FacebookUrl'] = get_ssl_rd_url(trim($v['FacebookUrl']));
			if (isset($v['TwitterUrl']) && !empty($v['TwitterUrl'])) $data[$k]['go_TwitterUrl'] = get_ssl_rd_url(trim($v['TwitterUrl']));
			
		}
		$countArr = array("ALL" => "ALL");
		$countArr = array_merge($countArr, $affiliate_model->country_arr);
		$tpl->assign("countrySel", $countrySel);
		$tpl->assign("countries", $countArr);
		$tpl->assign("type", $type);
		$tpl->assign("name", $name);
		$tpl->assign("affurlkw", $affurlkw);
		$tpl->assign("isactive", $isactive);
		$tpl->assign("RevenueAccount", $RevenueAccount);
		$tpl->assign("ProgramCrawled", $ProgramCrawled);
		$tpl->assign("data", $data);
		$tpl->assign("pagebar", $pagebar);
		$tpl->assign("pagebar1", $pagebar1);
		$tpl->display("affiliate_list.tpl");
		break;
}



function checkSubtrackingSet($subtrackingset = '') {
	$subtrackingset = trim($subtrackingset);
	
	if (empty($subtrackingset)) return true;
	
	preg_match_all('/=/', $subtrackingset, $matches);
	preg_match_all('/=[^=]+/', $subtrackingset, $matches1);
	
	if (count($matches1[0]) != count($matches[0])) return false;
		
	return true;
}
?>