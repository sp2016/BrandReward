<?php
global $aff_conf;

$aff_conf = array(
array('af'=>'mega'          ,'affid'=>'9999'    , 'status'=>array()),
array('af'=>'mk'            ,'affid'=>'9999'    , 'status'=>array()),
/*array('af'=>'cj'            ,'affid'=>'1'       , 'status'=>array('new'=>'PENDING','extended'=>'PENDING','locked'=>'CONFIRMED','closed'=>'CONFIRMED')),
array('af'=>'ls'            ,'affid'=>'2'       , 'status'=>array()),
array('af'=>'td'            ,'affid'=>'5'       , 'status'=>array('p'=>'PENDING','a'=>'CONFIRMED','d'=>'CANCELLED')),
array('af'=>'pjn'           ,'affid'=>'6'       , 'status'=>array('Paid'=>'CONFIRMED','Pending'=>'PENDING','Locked'=>'CONFIRMED','Delayed'=>'PENDING','Unconfirmed'=>'PENDING')),
array('af'=>'sas'           ,'affid'=>'7'       , 'status'=>array('Locked'=>'CONFIRMED','Pending'=>'PENDING','Processing'=>'PENDING','Voided'=>'CANCELLED')),
array('af'=>'affwin'        ,'affid'=>'10'      , 'status'=>array('pending'=>'PENDING','approved'=>'CONFIRMED','confirmed'=>'CONFIRMED','declined'=>'CANCELLED','deleted'=>'CANCELLED')),
array('af'=>'affwin_coupon' ,'affid'=>'2034'    , 'status'=>array('pending'=>'PENDING','approved'=>'CONFIRMED','confirmed'=>'CONFIRMED','declined'=>'CANCELLED','deleted'=>'CANCELLED')),
array('af'=>'lc'            ,'affid'=>'12'      , 'status'=>array('Pending'=>'PENDING','Valid'=>'CONFIRMED','Unfunded'=>'PENDING','Invalidated'=>'CANCELLED')),
array('af'=>'wg'            ,'affid'=>'13'      , 'status'=>array('confirmed'=>'CONFIRMED','cancelled'=>'CANCELLED','delayed'=>'PENDING')),
array('af'=>'zanox'         ,'affid'=>'15'      , 'status'=>array('pending'=>'PENDING','approved'=>'CONFIRMED','declined'=>'CANCELLED','deleted'=>'CANCELLED','confirmed'=>'CONFIRMED','open'=>'PENDING','rejected'=>'CANCELLED')),
array('af'=>'afffus'        ,'affid'=>'20'      , 'status'=>array()),
array('af'=>'afffuk'        ,'affid'=>'22'      , 'status'=>array()),
array('af'=>'dgmnew_au'     ,'affid'=>'28'      , 'status'=>array('APPROVED'=>'CONFIRMED','REVERSED'=>'CANCELLED','PENDING'=>'PENDING')),
array('af'=>'cg'            ,'affid'=>'46'      , 'status'=>array('Approved'=>'CONFIRMED','pending'=>'PENDING','Declined'=>'CANCELLED')),
array('af'=>'tt_uk'         ,'affid'=>'52'      , 'status'=>array('Accepted'=>'CONFIRMED','under evaluation'=>'PENDING','Rejected'=>'CANCELLED')),
array('af'=>'tt_de'         ,'affid'=>'65'      , 'status'=>array('Accepted'=>'CONFIRMED','under evaluation'=>'PENDING','Rejected'=>'CANCELLED')),
array('af'=>'tt_at'         ,'affid'=>'425'     , 'status'=>array('Accepted'=>'CONFIRMED','under evaluation'=>'PENDING','Rejected'=>'CANCELLED')),
array('af'=>'tt_ch'         ,'affid'=>'426'     , 'status'=>array('Accepted'=>'CONFIRMED','under evaluation'=>'PENDING','Rejected'=>'CANCELLED')),
array('af'=>'tt_fr'         ,'affid'=>'427'     , 'status'=>array('Accepted'=>'CONFIRMED','under evaluation'=>'PENDING','Rejected'=>'CANCELLED')),
array('af'=>'tt_it'         ,'affid'=>'2026'    , 'status'=>array('Accepted'=>'CONFIRMED','under evaluation'=>'PENDING','Rejected'=>'CANCELLED')),
array('af'=>'tt_nl'         ,'affid'=>'2027'    , 'status'=>array('Accepted'=>'CONFIRMED','under evaluation'=>'PENDING','Rejected'=>'CANCELLED')),
array('af'=>'tt_ru'         ,'affid'=>'2028'    , 'status'=>array('Accepted'=>'CONFIRMED','under evaluation'=>'PENDING','Rejected'=>'CANCELLED')),
array('af'=>'tt_be'         ,'affid'=>'2029'    , 'status'=>array('Accepted'=>'CONFIRMED','under evaluation'=>'PENDING','Rejected'=>'CANCELLED')),
array('af'=>'impradus'      ,'affid'=>'58'      , 'status'=>array('APPROVED'=>'CONFIRMED','PENDING'=>'PENDING','REVERSED'=>'CANCELLED')),
array('af'=>'cf'            ,'affid'=>'115'     , 'status'=>array('approved'=>'CONFIRMED','pending'=>'PENDING','void'=>'CANCELLED')),
array('af'=>'omgpm_asia'    ,'affid'=>'163'     , 'status'=>array('Pending'=>'PENDING','Validated'=>'CONFIRMED','Rejected'=>'CANCELLED')),
array('af'=>'omgpm_india'   ,'affid'=>'240'     , 'status'=>array('Pending'=>'PENDING','Validated'=>'CONFIRMED','Rejected'=>'CANCELLED')),
array('af'=>'phg'           ,'affid'=>'188'     , 'status'=>array('pending'=>'PENDING','approved'=>'CONFIRMED','rejected'=>'CANCELLED')),
array('af'=>'adcell'        ,'affid'=>'360'     , 'status'=>array('offen'=>'PENDING','storniert'=>'CANCELLED','bestätigt'=>'CONFIRMED')),
array('af'=>'glopss'        ,'affid'=>'557'     , 'status'=>array('pending'=>'PENDING','approved'=>'CONFIRMED','rejected'=>'CANCELLED')),
array('af'=>'por'           ,'affid'=>'29'      , 'status'=>array('pending'=>'PENDING','validated'=>'CONFIRMED','void'=>'CANCELLED')),
array('af'=>'tagau'         ,'affid'=>'49'      , 'status'=>array('Pending'=>'PENDING','Approved'=>'CONFIRMED','Rejected'=>'CANCELLED','declined'=>'CANCELLED')),
array('af'=>'taguk'         ,'affid'=>'124'     , 'status'=>array('Pending'=>'PENDING','Approved'=>'CONFIRMED','Rejected'=>'CANCELLED','declined'=>'CANCELLED')),
array('af'=>'tagsg'         ,'affid'=>'196'     , 'status'=>array('Pending'=>'PENDING','Approved'=>'CONFIRMED','Rejected'=>'CANCELLED','declined'=>'CANCELLED')),
array('af'=>'tagas'         ,'affid'=>'197'     , 'status'=>array('Pending'=>'PENDING','Approved'=>'CONFIRMED','Rejected'=>'CANCELLED','declined'=>'CANCELLED')),
array('af'=>'groupon'       ,'affid'=>'20000'   , 'status'=>array('VALID'=>'CONFIRMED','REFUNDED'=>'CANCELLED')),
array('af'=>'groupon_na'    ,'affid'=>'20001'   , 'status'=>array('VALID'=>'CONFIRMED','REFUNDED'=>'CANCELLED')),
array('af'=>'gameladen'     ,'affid'=>'408'     , 'status'=>array('Approved'=>'CONFIRMED','Declined'=>'CANCELLED','Pending'=>'PENDING')),
array('af'=>'belboon'       ,'affid'=>'152'     , 'status'=>array('approved'=>'CONFIRMED','pending'=>'PENDING','rejected'=>'CANCELLED')),
array('af'=>'mopubi'        ,'affid'=>'533'     , 'status'=>array('pending'=>'PENDING','approved'=>'CONFIRMED','returned'=>'CANCELLED')),
array('af'=>'admit'         ,'affid'=>'679'     , 'status'=>array('pending'=>'PENDING','approved'=>'CONFIRMED','declined'=>'CANCELLED')),
array('af'=>'effil'         ,'affid'=>'64'      , 'status'=>array('Attente'=>'PENDING','Refuse'=>'CANCELLED','Valide'=>'CONFIRMED')),
array('af'=>'gamesdeal'     ,'affid'=>'2004'    , 'status'=>array('A'=>'CONFIRMED','D'=>'CANCELLED','P'=>'PENDING')),
array('af'=>'PublicIdeas'   ,'affid'=>'503'     , 'status'=>array('pending'=>'PENDING','approved'=>'CONFIRMED','rejected'=>'CANCELLED')),
array('af'=>'affili_de'     ,'affid'=>'63'      , 'status'=>array('Open'=>'PENDING','Confirmed'=>'CONFIRMED','Cancelled'=>'CANCELLED')),
array('af'=>'affili_fr'     ,'affid'=>'500'     , 'status'=>array('Open'=>'PENDING','Confirmed'=>'CONFIRMED','Cancelled'=>'CANCELLED')),
array('af'=>'shoogloo'      ,'affid'=>'574'     , 'status'=>array('pending'=>'PENDING','approved'=>'CONFIRMED','rejected'=>'CANCELLED')),
array('af'=>'omg_au'        ,'affid'=>'2002'    , 'status'=>array('Pending'=>'PENDING','Validated'=>'CONFIRMED','Rejected'=>'CANCELLED')),
array('af'=>'daisycon'      ,'affid'=>'548'     , 'status'=>array('Open'=>'PENDING','Approved'=>'CONFIRMED','Disapproved'=>'CANCELLED')),
array('af'=>'appoddo'       ,'affid'=>'2030'    , 'status'=>array('approved'=>'CONFIRMED')),
array('af'=>'affili_uk'     ,'affid'=>'26'      , 'status'=>array('Open'=>'PENDING','Confirmed'=>'CONFIRMED','Cancelled'=>'CANCELLED')),
array('af'=>'affili_at'     ,'affid'=>'418'     , 'status'=>array('Open'=>'PENDING','Confirmed'=>'CONFIRMED','Cancelled'=>'CANCELLED')),
array('af'=>'affili_ch'     ,'affid'=>'491'     , 'status'=>array('Open'=>'PENDING','Confirmed'=>'CONFIRMED','Cancelled'=>'CANCELLED')),
array('af'=>'omg_uae'       ,'affid'=>'2024'    , 'status'=>array('Pending'=>'PENDING','Validated'=>'CONFIRMED','Rejected'=>'CANCELLED')),
array('af'=>'involve_asia'  ,'affid'=>'2025'    , 'status'=>array('Received'=>'PENDING','Pending'=>'PENDING','Approved'=>'CONFIRMED','Rejected'=>'CANCELLED','Paid'=>'CONFIRMED')),
array('af'=>'slice_digital' ,'affid'=>'2031'    , 'status'=>array('pending'=>'PENDING','approved'=>'CONFIRMED','declined'=>'CANCELLED','hold'=>'PENDING','confirmed'=>'CONFIRMED')),
array('af'=>'kelkoo'        ,'affid'=>'2032'    , 'status'=>array()),
array('af'=>'affilae'       ,'affid'=>'604'     , 'status'=>array('Pending'=>'PENDING','Refused'=>'CANCELLED','Accepted'=>'CONFIRMED','Locked'=>'PENDING','Not locked yet'=>'PENDING')),
array('af'=>'autoth'        ,'affid'=>'2005'    , 'status'=>array('Approved'=>'CONFIRMED','accepted'=>'CONFIRMED')),
array('af'=>'thrive'        ,'affid'=>'2006'    , 'status'=>array('accepted'=>'CONFIRMED')),
array('af'=>'fiverr'        ,'affid'=>'2008'    , 'status'=>array('Approved'=>'CONFIRMED','accepted'=>'CONFIRMED')),
array('af'=>'payoom'        ,'affid'=>'2033'    , 'status'=>array('accepted'=>'CONFIRMED','pending'=>'PENDING')),
array('af'=>'chinesean'     ,'affid'=>'2003'    , 'status'=>array('approved'=>'CONFIRMED','pending'=>'PENDING')),*/
);

//print_r($aff_conf);exit;
$sql = "select affid from aff_crawl_config where StatsCrawlStatus = 'Yes'";
$conf =  $pendingdb->getRows($sql);
foreach ($conf as $confValue){
    
    $sql = "SELECT Alias,TransactionStatus FROM wf_aff WHERE id = {$confValue['affid']}";
    $affInfo =  $db->getFirstRow($sql);
    $TransactionStatus = array(); 
    if($affInfo['TransactionStatus']){
        $TransactionStatus = json_decode($affInfo['TransactionStatus'],true);
    }
    $Alias = $affInfo['Alias'];
    if($confValue['affid'] == 52){
        $Alias = 'tt_uk';
    }
    $aff_conf[] = array(
        'af'=>$Alias,
        'affid'=>$confValue['affid'],
        'status'=>$TransactionStatus,
    );
    
}



?>
