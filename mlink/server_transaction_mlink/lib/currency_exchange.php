<?php
function cur_exchange($fcu, $tcu, $date) {
    
    if($date=='1969-12-31'){
        $date = date('Y-m-d',strtotime("-1 day"));
    }
    if ($fcu == $tcu)
        return 1;

    if (!defined('DB_CONFIG'))
		throw new Exception ("db confige not exists");

    global $CURRENCY_HIS;
    $k = $fcu.'-'.$tcu;
	if ($k == 'MYR-USD')
        return 0.2623;
	if ($k == 'BRL-USD')
        return 0.2981;
	if ($k == 'SEK-USD')
		return 0.1169;
    if ($k == 'DKK-USD')
        return 0.1479;
    if ($k == 'TWD-USD')
        return 0.0317;
    if ($k == 'CZK-USD')
        return 0.041;
    if ($k == 'NGN-USD')
        return 0.0027800;
    if ($k == 'BDT-USD')
        return 0.012310;
    if ($k == 'EGP-USD')
        return 0.1277;
    if ($k == 'PKR-USD')
        return 0.0095;
    if ($k == 'GHS-USD')
        return 0.2258;
    if ($k == 'XAF-USD')
        return 0.002;
    if ($k == 'KES-USD')
        return 0.009732;

    if (!isset($CURRENCY_HIS[$k][$date])) {
        $CURRENCY_HIS[$k] = array();    
    	$db = new PDO('mysql:host='.DB_BDG_HOST.';dbname='.DB_BDG_NAME, DB_BDG_USER, DB_BDG_PASS);
    	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
	    $sql = "SELECT DATE, NAME, EXCHANGERATE FROM exchange_rate FORCE INDEX(PRIMARY) WHERE NAME = '{$fcu}' AND DATE <= '{$date}' ORDER BY DATE DESC LIMIT 1";
	    $sth = $db->prepare($sql);
    	$sth->execute();
	    $fcu_r = $tcu_r = 0;
        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            $CURRENCY_HIS[$k][$date][$row['NAME']] = $row['EXCHANGERATE'];
	    }
    	$sth->closeCursor();

        $sql = "SELECT DATE, NAME, EXCHANGERATE FROM exchange_rate FORCE INDEX(PRIMARY) WHERE NAME = '{$tcu}' AND DATE <= '{$date}' ORDER BY DATE DESC LIMIT 1";
        $sth = $db->prepare($sql);
        $sth->execute();
        $fcu_r = $tcu_r = 0;
        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            $CURRENCY_HIS[$k][$date][$row['NAME']] = $row['EXCHANGERATE'];
        }
        $sth->closeCursor();
	    $sth = null;

        $CURRENCY_HIS[$k][$date]['THB'] = 19.17;
    }

    if(isset($CURRENCY_HIS[$k][$date][$fcu]) && isset($CURRENCY_HIS[$k][$date][$tcu]))
        return round($CURRENCY_HIS[$k][$date][$fcu] / $CURRENCY_HIS[$k][$date][$tcu],6); 
    else{
        //echo $fcu.' Currency Not Found!'.PHP_EOL;
        mail('merlinxu@brandreward.com', $fcu.' Currency Not Found!', $date.'Currency Not Found!');
        return 0;
    }
}

/*
require_once 'comm.php';
try {
    var_dump(cur_exchange('NZD','USD', '2013-03-31'));
//	var_dump(cur_exchange('AUD','USD', '2013-01-05'));        
}
catch(PDOException $e) {
	echo $e->getMessage();
	exit;
}
*/

?>
