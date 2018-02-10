<?php
include_once(INCLUDE_ROOT.'func.php');
global $db,$sys_header,$objTpl,$sys_menu,$sys_aff_name_id_map,$sys_site_tracking_code,$USERINFO;
$db = new Mysql(DB_NAME, DB_HOST, DB_USER, DB_PASS);

$objAccount = new Account();
$USERINFO = $objAccount->get_login_user();
$sys_header['css'][] = BASE_URL.'/css/bootstrap.min.css';
$sys_header['css'][] = BASE_URL.'/css/bootstrap-datetimepicker.min.css';
$sys_header['css'][] = BASE_URL.'/css/font-awesome.min.css';
$sys_header['css'][] = BASE_URL.'/css/error.css';

$sys_header['js'][] = BASE_URL.'/js/jquery.min.js';
$sys_header['js'][] = BASE_URL.'/js/bootstrap.min.js';
$sys_header['js'][] = BASE_URL.'/js/bootstrap-datetimepicker.js';
$sys_header['js'][] = BASE_URL.'/js/b_account.js';
foreach($sys_header['css'] as $k=>&$v){
	if($v == BASE_URL.'/css/style.css'){
		unset($sys_header['css'][$k]);
	}
}
date_default_timezone_set("America/Los_Angeles");
$objTpl = new TemplateSmarty();



$sys_aff_name_id_map = array(
						'cj'=>'1',
						'ls'=>'2,4',
						'ond'=>'30',
						'sas'=>'7',
						'td'=>'133,35,27,5',
						'zanox'=>'15',
						'afffuk'=>'22,36',
						'affwin'=>'10',
						'avangate'=>'32',
						'pjn'=>'6',
						'wg'=>'13,14,18,208,34',
						'afffus'=>'20',
						'avt'=>'8,181',
						'dgmnew_au'=>'28',
						'dgmnew_nz'=>'157',
						'tt'=>'52',
						'tt_de'=>'65',
						'lc'=>'12',
						'cm'=>'62',
						'cf'=>'115',
						'sr'=>'50',
						'cg'=>'46',
						'tagau'=>'49',
						'taguk'=>'124',
						'tagsg'=>'196',
						'tagas'=>'197',
						'belboon'=>'152',
						'por'=>'29',
						'affili'=>'26',
						'affili_de'=>'63',
						'impradus'=>'58',
						'impraduk'=>'59',
						'silvertap'=>'23',
						'viglink'=>'191',
						'skimlinks'=>'223',
						'phg'=>'188',
						'phg_irisa'=>'188',
						'phg_conv'=>'188',
						'phg_horiz'=>'188',
						'adcell'=>'360',
						'zoobax'=>'398',
						'gameladen'=>'408');

$sys_site_tracking_code = array(
			's01'=>'csus',
			's09'=>'csca',
			's17'=>'csau',
			's02'=>'csuk',
			's29'=>'csde',
			's49'=>'csusmob',
			's10'=>'csie',
			's32'=>'csnz',
			's42'=>'pc2012',
			's70'=>'hotdeals',
			's16'=>'dealsalbum',
			's16'=>'dealsalbum',
			's46'=>'dc2012',
			's501'=>'acapp',
			's15'=>'anypromocodes',
			's08'=>'c4lp',
			's40'=>'coupondealpro',
			's43'=>'cs6rlease',
			's05'=>'cs3soft',
			's06'=>'cs4soft',
			's07'=>'`',
			's38'=>'seekcoupon',
			's03'=>'codes',
			's04'=>'perfect',
			's36'=>'esw4u',
			's52'=>'cs6upgrade',
			's37'=>'ifunbox',
			's45'=>'shopwithcoupon',
			's59'=>'laihaitao',
			's61'=>'fiberforme',
			's39'=>'tipdownload',
			's63'=>'ccm',
			's71'=>'fscoupon',
			's64'=>'walletsaving',
			's69'=>'paydayloan',
			's65'=>'appholic',
			's47'=>'bfdc',
			's0'=>'unknown',
			);


?>