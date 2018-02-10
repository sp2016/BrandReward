<?php
/*
    Please change your account.ini profile
*/

require_once 'Common/Lib/Bcgapi.php';

try {
    $o = new BcgApi();
    
    //Generate promotion service 
    $s = $o->GetService('promo');

    //Required, the promotion location valid in (us, uk, ca, au, in, de, fr)    
    $s->Select("market", 'uk');
    
    //Optional, but one of them
    //Filter Column, id, merchantid, lastchangetime, addtime
    //Filter Operator, 
    //  eq : =, 
    //  gt : >, ge : >=
    //  lt : <, le : <=
    //  sw : LIKE 'xx%'
    //  sf : LIKE '%xx%'
    //  li : IN ()
    $s->Select("merchantid", 41309, "li");
//    $s->Select("merchantid", 28443, "li");
//    $s->Select("lastchangetime", "2016-04-19 01:22:22", "ge");


	$p = 1;
	$n = 1000;
    do {
        //Result Navigation, Max return rows is 1000
        $s->Page($p, $n);
        
        $rtn = $s->get();
        print_r($rtn);die;
        if (!isset($rtn->msg) || !$rtn->msg)
            break;
		
        foreach ($rtn->msg as $v) {
            //Return Column: ID, TITLE, CODE, MERCHANTID, REMARK, ADDTIME, STARTTIME, EXPIRETIME, EXPIREDATETYPE, REMINDDATE, PROMOTIONCONTENT, ISEXCLUSIVE, SOURCE, DSTURL, AFFURL, IMGURL
            echo $p."\t".$v->ID ."\t". $v->TITLE."\n";die;
        }

        echo "==============================".($p*$n) ." | ". $rtn->totalrows .'======================'."\n";

    } while (++$p*$n <= $rtn->totalrows);

        

}
catch (Exception $e) {
    var_dump($e);
} 
