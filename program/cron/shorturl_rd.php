<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT.'init.php');
$objMysql = New Mysql('bdg_go_base','localhost','bdg_go','shY12Nbd8J');

$short = trim($_GET['s'], '/');
$xo = $_GET['xo'];

if(strlen($short) == 7){
	$sql = "select a.`long`, a.accountid, b.apikey from short_url a inner join publisher_account b on a.accountid = b.id where b.status = 'active' and a.short = '".addslashes($short)."'";
	$tmp_arr = array();
	$tmp_arr = $objMysql->getFirstRow($sql);
	if(strlen($tmp_arr['long']) && strlen($tmp_arr['apikey'])){
		$url = formatMlinkWay($tmp_arr['long'], $tmp_arr['apikey']);
		if($xo == 'ox'){
			echo $url;exit;
		}			
		$referer = $_SERVER["HTTP_REFERER"];
		header("HTTP/1.1 301 Moved Permanently");
		header("Cache-Control: no-cache");
		header("Referer: ".$referer);
		header("Location: ".$url);
		exit;
	}
}

header("HTTP/1.1 404 Not Found");
exit;


function formatMlinkWay($url, $apikey){
        $domain = parse_url($url);
        if($domain['host']=='r.brandreward.com')
            return $url;
	return "https://r.brandreward.com/?key=$apikey&id=ss&url=".urlencode($url);
}
?>
