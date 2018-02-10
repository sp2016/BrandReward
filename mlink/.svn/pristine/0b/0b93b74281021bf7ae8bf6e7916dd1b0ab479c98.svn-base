<?php
if(!defined("SPECIAL_HTTP_HOST")) define("SPECIAL_HTTP_HOST","task");
include_once(dirname(dirname(__FILE__))."/etc/const.php");
include_once(INCLUDE_ROOT . "func/gpc.func.php");

include_once(INCLUDE_ROOT . "lib/Class.TemplateSmarty.php");
$tpl = new TemplateSmarty();
$resObj = new Request();

$programModel = new Program();
$programNoticeModel = new ProgramNotice();


$partnerShipArr = array('NoPartnership' => 'NoPartnership', 'Active' => 'Active', 'Pending' => 'Pending', 'Declined' => 'Declined', 'Expired' => 'Expired', 'Removed' => 'Removed');
$partnerShipAllArr = array_merge(array('All' => 'All'), $partnerShipArr);
$tpl->assign('partnerShipArr', $partnerShipArr);
$tpl->assign('partnerShipAllArr', $partnerShipAllArr);

$countryArr = array('GLOBAL' => 'GLOBAL(GLOBAL)', 'EU' => 'European Union(EU)', 'AR' => 'Argentina(AR)', 'AU' => 'Australia(AU)', 'AT' => 'Austria(AT)',
	'BE' => 'Belgium(BE)', 'CA' => 'Canada(CA)', 'CH' => 'Switzerland(CH)', 'CN' => 'China(CN)', 'CR' => 'Costa Rica(CR)', 'CY' => 'Cyprus(CY)',
	'CZ' => 'Czech Republic(CZ)', 'DK' => 'Denmark(DK)', 'SV' => 'El Salvador(SV)', 'EE' => 'Estonia(EE)', 'FI' => 'Finland(FI)', 
	'FR' => 'France(FR)', 'DE' => 'German(DE)', 'GI' => 'Gibraltar(GI)', 'GP' => 'Guadeloupe(GP)',  'GR' => 'Greece(GR)', 'HK' => 'Hong Kong(HK)', 
	'IN' => 'India(IN)', 'ID' => 'Indonesia(ID)', 'IE' => 'Ireland(IE)', 'IL' => 'Israel(IL)', 'IT' => 'Italy(IT)', 'JP' => 'Japan(JP)', 
	'LV' => 'Latvia(LV)', 'LU' => 'Luxembourg(LU)', 'MA' => 'Morocco(MA)', 'MX' => 'Mexico(MX)', 'MY' => 'Malaysia(MY)', 'NL' => 'Netherlands(NL)', 
	'NO' => 'Norway(NO)', 'NZ' => 'New Zealand(NZ)', 'PH' => 'Philippines(PH)', 'PL' => 'Poland(PL)', 'PT' => 'Portugal(PT)', 'QA' => 'Qatar(QA)', 
	'RO' => 'Romania(RO)', 'ZA' => 'South Africa(ZA)', 'SE' => 'Sweden(SE)', 'SG' => 'Singapore(SG)', 'ES' => 'Spain(ES)', 'TW' => 'Taiwan(TW)', 
	'TH' => 'Thailand(TH)', 'AE' => 'United Arab Emirates(AE)',	'UK' => 'United Kingdom(UK)', 'US' => 'United States(US)', 'VG' => 'Virgin Island, British(VG)');

$countryAllArr = array_merge(array('All' => 'All'), $countryArr);
$tpl->assign('countryArr', $countryArr);
$tpl->assign('countryAllArr', $countryAllArr);

$affiliteTypeAllArr['All'] = 'All';
$affiliates = $programModel->getAllAffiliates();
foreach ($affiliates as $kk => $vv) {
	$affiliteTypeArr[$vv['ID']] = $vv['Name'];
	$affiliteTypeAllArr[$vv['ID']] = $vv['Name'];
}

$tpl->assign('affiliteTypeArr', $affiliteTypeArr);
$tpl->assign('affiliteTypeAllArr', $affiliteTypeAllArr);


$statusInAffiliateArr = array('Active' => 'Active', 'TempOffline' => 'TempOffline', 'Offline' => 'Offline');
$statusInAffiliateAllArr = array_merge(array('All' => 'All'), $statusInAffiliateArr);
$tpl->assign('statusInAffiliateArr', $statusInAffiliateArr);
$tpl->assign('statusInAffiliateAllArr', $statusInAffiliateAllArr);

$orderbyArr = array(
    'p.RankInAff DESC' => 'RankInAff - DESC',
    'p.JoinDate DESC' => 'JoinDate - DESC',
	'i.RevenueOrder ASC' => 'RankInMega - ASC'
);
$tpl->assign('orderbyArr', $orderbyArr);

$TMArr = array('UNKNOWN' => 'Unknown', 'ALLOWED' => 'Allowed' , 'DISALLOWED' => 'Disallowed' , 'CONFIRMED_DISALLOWED' => 'Confirmed&Disallowed');
$tpl->assign('TMArr', $TMArr);

$currency = array('AUD','BRL','CAD','CZK','DKK','EUR','GBP','HKD','INR','JPY','KRW','MYR','NOK','NZD','PHP','PLN','SAR','SEK','SGD','THB','TRY','TWD','USD','ZAR');
$tpl->assign('currency', $currency);



function filterStrSlashes($data, $default = '', $toEncoding = '')
{
	$res = '';
	
	if (is_array($data)) {
		foreach ($data as $k => $v) {
			$res[$k] = filterStrSlashes($v);
		}
	} else {
		$res = $data;
		if (get_magic_quotes_gpc()) $res = stripslashes($res);
		if($res === '') return $default;
		if($toEncoding != '') $res = iconv("UTF-8", $toEncoding, $res);
	}
	
	return $res;
}

//special function for pragram edit
function compareFieldValue($from = array(), $to = array()) {
	$data = array();
	
	//only check field in cfg
	$field_arr = array();
	$field_arr = getCompareField();
	
	if (empty($from)) return $data;
	if (empty($to)) {
		foreach ($from as $k => $v) {
			if (empty($v)) continue;
			if (!in_array($k, $field_arr)) continue;
			$data[$k]['old'] = '';
			$data[$k]['new'] = $v;
		}
		return $data;
	}
	
	foreach ($from as $k => $v) {
		if (!in_array($k, $field_arr)) continue;
		if ($k == 'TargetCountryInt' && !empty($v)) {
			sort($v);
			$v = implode(',', (array)$v);
		}
		if (trim($v) == trim($to[$k])) continue;
		$data[$k]['old'] = $to[$k];
		$data[$k]['new'] = $v;
	}
	
	return $data;
}

function getCompareField(){	
	global $programNoticeModel;
	$field_arr = array();
	$programNoticeCfgFormat = array();
	$programNoticeCfgTmp = $programNoticeModel->getAllProgramNoticeCFG();
	
	foreach ($programNoticeCfgTmp as $k => $v) {
		$filedsTmp = preg_replace(array("/\s+/is", "/[\\r|\\n|\\r\\n]/is"), '', trim($v['Fields']));
		$filedsArr = explode(',', $filedsTmp);
				
		foreach($filedsArr as $val){
			$field_arr[] = trim($val);
		}
	}
	return array_unique($field_arr);
}
?>