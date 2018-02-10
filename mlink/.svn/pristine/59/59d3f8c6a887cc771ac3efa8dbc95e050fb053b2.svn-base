<?php


try {

    define('AFF_NAME', AFFILIATE_NAME);
    define('USER_NAME', AFFILIATE_USER);
    define('USER_PASS', "7HXuJr3fiWlyQvuV9Kh8");
    define('CURRENCY_CODE', "EUR");
    define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");
    
    if (AFF_NAME == '' || USER_NAME == '' || USER_PASS == '' || CURRENCY_CODE == '')
        throw new Exception ("Error Input");

    define('PAGE_SIZE', 100);
    define('REV_DATA', PATH_CODE . '/log/' . AFF_NAME . '.dat');
    
    define("WSDL_LOGON", "https://api.affili.net/V2.0/Logon.svc?wsdl");
    define("WSDL_STATS", "https://api.affili.net/V2.0/PublisherStatistics.svc?wsdl");

    // SOAP options (http://de.php.net/manual/de/soapclient.soapclient.php)


    if (defined('START_TIME') && defined('END_TIME')) {
        $end_dt = date('Y-m-d', strtotime(END_TIME));
        $start_dt = date('Y-m-d', strtotime(START_TIME));
    } else {
        $end_dt = date('Y-m-d');
        $start_dt = date('Y-m-d', strtotime('-100 days', strtotime($end_dt)));
    }

    echo "Date setting: ST:{$start_dt} ET:{$end_dt} \n";

    $login = new SoapClient(WSDL_LOGON);
    $token = $login->Logon(array('Username' => USER_NAME, 'Password' => USER_PASS, 'WebServiceType' => 'Publisher'));


    $client = new SoapClient(WSDL_STATS);

    $filters = array('TransactionStatus' => 'All',
        'ValuationType' => 'DateOfRegistration',
        'StartDate' => $start_dt,
        'EndDate' => $end_dt
    );



    $page = 1;
    $comm_all=0;
    $fws = $comms = array();
    do {
        echo "Current Page: {$page}\n";

        $result = $client->GetTransactions(array('CredentialToken' => $token,
            'TransactionQuery' => $filters,
            'PageSettings' => array('CurrentPage' => $page, 'PageSize' => PAGE_SIZE)
                )
        );


        if (!isset($result->TotalRecords) || $result->TotalRecords == 0 || !isset($result->TransactionCollection) || !isset($result->TransactionCollection->Transaction)) {
            if ($page == 1)
                throw new Exception("No Data Found");
            else
                break;
        }
      	//print_r($result);exit;
        /*
          object(stdClass)#3 (2) {
          ["TotalRecords"]=>
          int(256)
          ["TransactionCollection"]=>
          object(stdClass)#4 (1) {
          ["Transaction"]=>
          array(100) {
          [0]=>
          object(stdClass)#5 (15) {
          ["BasketInfo"]=>
          NULL
          ["CancellationReason"]=>
          NULL
          ["CheckDate"]=>
          NULL
          ["ClickDate"]=>
          string(19) "2013-02-22T00:42:37"
          ["CreativeInfo"]=>
          object(stdClass)#6 (2) {
          ["CreativeNumber"]=>
          int(15)
          ["CreativeType"]=>
          string(6) "Banner"
          }
          ["NetPrice"]=>
          float(44)
          ["ProgramId"]=>
          int(7663)
          ["ProgramTitle"]=>
          string(12) "Next Ireland"
          ["PublisherCommission"]=>
          float(0.88)
          ["RateInfo"]=>
          object(stdClass)#7 (5) {
          ["IsTieredCommission"]=>
          NULL
          ["RateDescription"]=>
          string(9) "Base Rate"
          ["RateMode"]=>
          string(10) "PayPerSale"
          ["RateNumber"]=>
          int(1)
          ["RateValue"]=>
          float(2)
          }
          ["RegistrationDate"]=>
          string(19) "2013-02-22T00:50:50"
          ["SubId"]=>
          string(23) "s10_3_3_1033673_3680511"
          ["TrackingMethod"]=>
          string(9) "PostClick"
          ["TransactionId"]=>
          int(165702566)
          ["TransactionStatus"]=>
          string(4) "Open"
          }

         */
      	if (is_array($result->TransactionCollection->Transaction)) {
	        foreach ($result->TransactionCollection->Transaction as $v) {
	                
	            $valid = strtolower($v->TransactionStatus) == 'canceled' ? 0 : 1;
	
	            $sid = trim($v->SubId);
	            if(preg_match('/^s\d{2,3}.*/', $sid))
	              $sid = str_replace('aa', '_', $sid);
	            $sale = str_replace(',', '', $v->NetPrice);
	            $rev = str_replace(',', '', $v->PublisherCommission) * $valid;
	
	            //transaction date time
	            $event_dt = $process_dt = date('Y-m-d H:i:s', strtotime($v->RegistrationDate));
	
	            //click date time
	            $click_dt = date('Y-m-d H:i:s', strtotime($v->ClickDate));
	
	            $cur = cur_exchange(CURRENCY_CODE, 'USD', date('Y-m-d', strtotime($event_dt)));            
	
	            $rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . date('Ymd', strtotime($event_dt)) . '.upd';
	            if (!isset($fws[$rev_file])) {
	                $fws[$rev_file] = fopen($rev_file, 'w');
	                $comms[$rev_file] = 0;
	            }
                    $cancelreason = trim($v->CancellationReason);
	            $replace_array = array(
	            		'{createtime}'      => $event_dt,
	            		'{updatetime}'      => $event_dt,
	            		'{sales}'           => round($sale*$cur,4),
	            		'{commission}'      => round($rev*$cur,4),
	            		'{idinaff}'         => trim($v->ProgramId),
	            		'{programname}'     => trim($v->ProgramTitle),
	            		'{sid}'             => $sid,
	            		'{orderid}'         => '',
	            		'{clicktime}'       => $click_dt,
	            		'{tradeid}'         => trim($v->TransactionId),
	            		'{tradestatus}'     => trim($v->TransactionStatus),
	            		'{oldcur}'          => CURRENCY_CODE,
	            		'{oldsales}'        => $sale,
	            		'{oldcommission}'   => $rev,
	            		'{tradetype}'       => trim(isset($v->RateMode)?$v->RateMode:''),
	            		'{referrer}'        => trim(isset($v->CreativeType)?$v->CreativeType:''),
                                '{cancelreason}'    => $cancelreason,
	            );
	            //should have merchant id
	            if ($replace_array['{idinaff}'] == 0 || $replace_array['{idinaff}'] == '' || $v->RegistrationDate == '')
	            	continue;
	            
	            fwrite($fws[$rev_file], strtr(FILE_FORMAT,$replace_array) . "\n");
	            $comms[$rev_file] += $rev;
	            $comm_all+=$rev;
	        }
      	}else{
      		$v = $result->TransactionCollection->Transaction;
      		$valid = strtolower($v->TransactionStatus) == 'canceled' ? 0 : 1;
      		
      		$sid = trim($v->SubId);
      		if(preg_match('/^s\d{2,3}.*/', $sid))
      			$sid = str_replace('aa', '_', $sid);
      		$sale = str_replace(',', '', $v->NetPrice);
      		$rev = str_replace(',', '', $v->PublisherCommission) * $valid;
      		
      		//transaction date time
      		$event_dt = $process_dt = date('Y-m-d H:i:s', strtotime($v->RegistrationDate));
      		
      		//click date time
      		$click_dt = date('Y-m-d H:i:s', strtotime($v->ClickDate));
      		
      		$cur = cur_exchange(CURRENCY_CODE, 'USD', date('Y-m-d', strtotime($event_dt)));
      		
      		$rev_file = PATH_DATA . '/' . AFF_NAME . '/revenue_' . date('Ymd', strtotime($event_dt)) . '.upd';
      		if (!isset($fws[$rev_file])) {
      			$fws[$rev_file] = fopen($rev_file, 'w');
      			$comms[$rev_file] = 0;
      		}
                $cancelreason = trim($v->CancellationReason);
      		$replace_array = array(
      				'{createtime}'      => $event_dt,
      				'{updatetime}'      => $event_dt,
      				'{sales}'           => round($sale*$cur,4),
      				'{commission}'      => round($rev*$cur,4),
      				'{idinaff}'         => trim($v->ProgramId),
      				'{programname}'     => trim($v->ProgramTitle),
      				'{sid}'             => $sid,
      				'{orderid}'         => '',
      				'{clicktime}'       => $click_dt,
      				'{tradeid}'         => trim($v->TransactionId),
      				'{tradestatus}'     => trim($v->TransactionStatus),
      				'{oldcur}'          => CURRENCY_CODE,
      				'{oldsales}'        => $sale,
      				'{oldcommission}'   => $rev,
      				'{tradetype}'       => trim(isset($v->RateMode)?$v->RateMode:''),
      				'{referrer}'        => trim(isset($v->CreativeType)?$v->CreativeType:''),
                                '{cancelreason}'    => $cancelreason,
      		);
      		//should have merchant id
      		if ($replace_array['{idinaff}'] == 0 || $replace_array['{idinaff}'] == '' || $v->RegistrationDate == '')
      			continue;
      		 
      		fwrite($fws[$rev_file], strtr(FILE_FORMAT,$replace_array) . "\n");
      		$comms[$rev_file] += $rev;
      		$comm_all+=$rev;
      	}
    } while (($page++ * PAGE_SIZE) < $result->TotalRecords);


    foreach ($fws as $file => $fp) {
        fclose($fp);
		/*
        $file_old = str_replace('.upd', '.dat', $file);

        if (!file_exists($file_old)) {
            //echo "mv {$file}, {$file_old}\n";
            rename($file, $file_old);
        } else {
            $comm_old = 0;
            $fp = fopen($file_old, 'r');
            if ($fp) {
                while (!feof($fp)) {
                    $lr = trim(fgets($fp));

                    if ($lr == '' || strpos($lr, 'SUMARIZE:') !== false)
                        continue;

                    $lr = explode("\t", $lr);
                    $comm_old += $lr[3];
                }
            }
            fclose($fp);

            $comm_new = isset($comms[$file]) ? $comms[$file] : 0;
            if (round($comm_new, 2) != round($comm_old, 2)) {

                //echo "mv {$file}, {$file_old} --- {$comm_new} : {$comm_old}\n";
                rename($file, $file_old);
            } else {
                unlink($file);
            }
        }
		*/
    }
	/*
    $fp = fopen(REV_DATA, 'a');
    if (!file_exists(REV_DATA))
        throw new Exception(REV_DATA . " not exist");
    fwrite($fp, "{$start_dt}~{$end_dt}\t{$comm_all}\n");
    fclose($fp);
    */
} catch (Exception $e) {
    var_dump($e);
}
?>
