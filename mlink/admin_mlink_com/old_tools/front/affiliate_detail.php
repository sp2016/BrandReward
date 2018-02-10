<?php
include_once(dirname(dirname(__FILE__))."/etc/const.php");
include_once(INCLUDE_ROOT . "lib/Class.TemplateSmarty.php");
include_once(INCLUDE_ROOT . "lib/Class.Mysql.php");
include_once(INCLUDE_ROOT . "lib/Class.MyException.php");
include_once(INCLUDE_ROOT . "lib/Class.Request.php");
include_once(INCLUDE_ROOT . "lib/Class.Affiliate.php");

$tpl = new TemplateSmarty();
$resobj = new Request();
$objMysql = new Mysql(TASK_DB_NAME, TASK_DB_HOST, TASK_DB_USER, TASK_DB_PASS);
$affiliate_model = new Affiliate($objMysql);

$isactive_arr = array('YES' => 'YES', 'NO' => 'NO');
$tpl->assign("isactive_arr", $isactive_arr);

$id = intval(trim($resobj->getStrNoSlashes("id")));
$type_arr = array('NO' => 'Network', 'YES' => 'InHouse');
$tpl->assign("type_arr", $type_arr);
$data = $affiliate_model->getAffilicateById($id);
if (empty($data)) {
	echo "<Script Language=\"Javascript\">alert('The affiliate is not exists');</Script>";
	echo "<Script Language=\"Javascript\">history.back(-1);</Script>";
	exit;
}

if ($data['IsInHouse'] == 'YES') $data['type_format'] = 'InHouse';
else $data['type_format'] = 'Network';
$data['AffiliateUrlKeywords_format'] = nl2br($data['AffiliateUrlKeywords']);
$data['AffiliateUrlKeywords2_format'] = nl2br($data['AffiliateUrlKeywords2']);
$data['joindate_format'] = (!empty($data['JoinDate']) && $data['JoinDate'] != '0000-00-00 00:00:00') ? date('Y-m-d', strtotime($data['JoinDate'])) : '';
$countrySel = explode("||", $data["Country"]);
$tpl->assign("countrySel", $countrySel);
$tpl->assign("countries", $affiliate_model->country_arr);
$tpl->assign("data", $data);
$tpl->display("affiliate_detail.tpl");
?>