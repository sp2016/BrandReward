<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
$objMysql = New MysqlExt();
$objProgram = New Program();


$key = trim($_GET["key"]);
$page_index = intval($_GET["pageIndex"]);
$page_size = intval($_GET["pageSize"]);

$objMysql->query("SET NAMES utf8");

if($key == "362aa4fd1ce9c73a3915f73bf568fb2f"){//BCG
	$cc = "all";
}else{
	$sql = "SELECT id, alias FROM publisher_account WHERE apikey = '".addslashes($key)."' AND STATUS = 'active' limit 1";
	$acc_arr = array();
	$acc_arr = $objProgram->objMysql->getFirstRow($sql);
	if(!count($acc_arr))
		exit;
	$acc_arr["alias"] = str_ireplace("cs", "", $acc_arr["alias"]);
	$cc = strtolower($acc_arr["alias"]);
}



$sql = "SELECT COUNT(1) FROM store WHERE StoreAffSupport = 'YES'";
$cnt = intval($objProgram->objMysql->getFirstRowColumn($sql));

if($page_index < 1) $page_index = 1;
if($page_size < 1 || $page_size > 1000) $page_size = 1000;
if($page_size * $page_index > $cnt)
	exit("please input correct page number and page size!");

//$sql = "SELECT ID,Name,Domains,CountryCode,AddTime FROM store WHERE StoreAffSupport = 'YES'LIMIT ".($page_index - 1) * $page_size." ,". $page_size;
//$result_store = $objProgram->objMysql->getRows($sql);

$sql = "SELECT
    a.ID AS StoreID,
    a.Name AS StoreName,
    a.CountryCode AS SupportCountry,
    c.ID AS DomainID,
    c.Domain,
    c.DomainName,
    e.ID AS ProgramID,
    e.Name AS ProgramName,
    e.AffId,
    g.ShippingCountry,
    h.Name AS AffName,
    d.isfake,
    d.DeepUrlTpl AS support_deep,
    e.AllowInaccuratePromo,
    e.AllowNonaffCoupon,
	i.AllowInaccuratePromoInt,
	i.AllowNonaffPromoInt,
	e.AllowNonaffPromo,
	i.AllowNonaffCouponInt,
	e.CouponCodesPolicyExt,
	i.CouponCodesPolicyInt,
	d.AffDefaultUrl,
	e.CommissionExt,
	e.Description AS `desc`,
	e.TermAndCondition AS terms
  FROM
    r_store_domain b
    INNER JOIN store a
      ON b.`StoreId` = a.`ID`
    INNER JOIN domain c
      ON c.`ID` = b.`DomainId`
    INNER JOIN r_domain_program d
      ON c.`ID` = d.`DID`
    INNER JOIN program e
      ON d.`PID` = e.`ID`
  	INNER JOIN( SELECT ID FROM store WHERE StoreAffSupport = 'YES' LIMIT ".($page_index - 1) * $page_size ."," .$page_size.") f
	  ON a.ID = f.ID
    INNER JOIN program_intell g
	  ON g.programid = d.pid
	INNER JOIN wf_aff h
	  ON h.id = e.affid
	INNER JOIN program_int i
	  ON g.programid = i.programid
  ORDER BY `StoreID`;";
//echo $sql;

$result = $objProgram->objMysql->getRows($sql);
//var_dump($result);
$store_result = array();
foreach($result as $item){
//	var_dump($item);
	$store_result[$item['StoreID']]['StoreID'] = $item['StoreID'];
	$store_result[$item['StoreID']]['StoreName'] = $item['StoreName'];
	if(!isset($store_result[$item['StoreID']]['CountryCode'])){
		if($item['ShippingCountry'] == '' || is_null($item['ShippingCountry']) || strlen($item['ShippingCountry']) == 0)
			$store_result[$item['StoreID']]['CountryCode'] = null;
		else
			$store_result[$item['StoreID']]['CountryCode'] = $item['ShippingCountry'];
	} else {
		if($item['ShippingCountry'] == '' || is_null($item['ShippingCountry']) || strlen($item['ShippingCountry']) == 0 || stripos($store_result[$item['StoreID']]['CountryCode'],$item['ShippingCountry']) !== false)
			;
		else
			$store_result[$item['StoreID']]['CountryCode'] .= ",".$item['ShippingCountry'];
	}
	$store_result[$item['StoreID']]['aff_info'][$item['ProgramID']] = array(
		'AffId' => $item['AffId'],
		'AffName' => $item['AffName'],
		"AffUrl" => $item["AffDefaultUrl"],
		"AffTpl" => $item["support_deep"],'DomainID' => $item['DomainID'],
		'Domain' => $item['Domain'],
		'DomainName' => $item['DomainName'],
		'ProgramID' => $item['ProgramID'],
		'ProgramName' => $item['ProgramName'],
		"IsFake"=>$item["isfake"],
		"SupportDeep"=>strlen($item["support_deep"])?"YES":"NO",
		"AllowInaccuratePromo" => ( $item["AllowInaccuratePromoInt"] != 'INITIAL') ? $item["AllowInaccuratePromoInt"] : $item["AllowInaccuratePromo"],
		"AllowNonaffPromo" => ($item["AllowNonaffPromoInt"] != 'INITIAL') ? $item["AllowNonaffPromoInt"] : $item["AllowNonaffPromo"],
		"AllowNonaffCoupon" => ($item["AllowNonaffCouponInt"] != 'INITIAL') ? $item["AllowNonaffCouponInt"] : $item["AllowNonaffCoupon"],
		"CouponCodesPolicy" => $item["CouponCodesPolicyExt"] . $item["CouponCodesPolicyInt"],
		"Commission" => $item["CommissionExt"],
		"Description" => $item["desc"],
		"Terms" => $item["terms"],
	);
}

echo json_encode(array("total" => $cnt, "pageIndex" => $page_index, "pageSize" => count($store_result), "merchants" => $store_result));

