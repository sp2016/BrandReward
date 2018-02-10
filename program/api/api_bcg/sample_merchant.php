<?php
/*
    Please change your account.ini profile
*/

require_once 'Common/Lib/Bcgapi.php';

try {
    $o = new BcgApi();
    
    //Generate promotion service 
    $s = $o->GetService('merchant');

    //Required, the promotion location valid in (us, uk, ca, au, in, de, fr)    
    $s->Select("market", 'us');
    
    //Optional, but one of them
    //Filter Column, id, merchantid, lastchangetime, addtime
    //Filter Operator, 
    //  eq : =, 
    //  gt : >, ge : >=
    //  lt : <, le : <=
    //  sw : LIKE 'xx%'
    //  sf : LIKE '%xx%'
    //  li : IN ()
    $s->Select("id", 37985, "li");
    $s->Select("id", 28443, "li");
    //$s->Select("lastchangetime", "2016-04-19 01:22:22", "ge");


	$p = 1;
	$n = 1000;
    do {
        //Result Navigation, Max return rows is 1000
        $s->Page($p, $n);
        
        $rtn = $s->get();
        if (!isset($rtn->msg) || !$rtn->msg)
            break;
	    
        //Return Field: ID, NAME, DESCRIPTION, GRADE, LOGO, DSTURL, ORIGINALURL, ADDTIME, LASTCHANGETIME, EDITORTIPS, ADULTSTORE, ALCOHOLSTORE,GUNSTORE, POLITICALSTORE, GAMBLINGSTORE
        print_r($rtn);exit;	

        echo "==============================".($p*$n) ." | ". $rtn->totalrows .'======================'."\n";

    } while (++$p*$n <= $rtn->totalrows);

        

}
catch (Exception $e) {
    var_dump($e);
} 
