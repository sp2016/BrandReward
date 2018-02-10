<?php
 
    define('AFF_NAME', AFFILIATE_NAME);
    define('USER_NAME', AFFILIATE_USER);
    define('USER_PASS', AFFILIATE_PASS);
    define('REV_DATA', PATH_CODE . '/log/' . AFF_NAME . '.dat');
    define('FILE_FORMAT',"{createtime}\t{updatetime}\t{sales}\t{commission}\t{idinaff}\t{programname}\t{sid}\t{orderid}\t{clicktime}\t{tradeid}\t{tradestatus}\t{oldcur}\t{oldsales}\t{oldcommission}\t{tradetype}\t{referrer}\t{cancelreason}");

    $REPORTWS_URI = 'https://www.tagadmin.com.au/ws/AffiliateSOAP.wsdl';
    $CURRENCY = 'AUD';
    
    if (defined('START_TIME') && defined('END_TIME')) {
        $start_dt = date('Y-m-d', strtotime(START_TIME));
        $end_dt = date('Y-m-d', strtotime(END_TIME));
    } else {
        $end_dt = date('Y-m-d');
        $start_dt = date('Y-m-d', strtotime('-100 days', strtotime($end_dt)));
    }

    echo "Date setting: ST:{$start_dt} ET:{$end_dt} \n";


    $client = new SoapClient($REPORTWS_URI);
    $ua = new SoapVar(USER_NAME, XSD_STRING, '', 'xsd:string');
    $pa = new SoapVar(USER_PASS, XSD_STRING, '', 'xsd:string');
    $fd = new SoapVar($start_dt . ' 00:00:00', XSD_STRING, '', 'xsd:string');
    $td = new SoapVar($end_dt . ' 23:59:59', XSD_STRING, '', 'xsd:string');


    $result = $client->GetSalesData(array('Authentication' => array('username' => $ua, 'apikey' => $pa),
        'Criteria' => array('StartDateTime' => $fd, 'EndDateTime' => $td),
            )
    );

    if (!isset($result->Transactions) || empty($result->Transactions->Transaction)) {
        echo "No Data Found";exit;
    }
     
    $dump = array();
    if(count($result->Transactions->Transaction) > 1){
        foreach ($result->Transactions->Transaction as $val) {
            $TransactionDateTime = date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $val->TransactionDateTime)));
            $SaleApprovalDateTime = !isset($val->SaleApprovalDateTime) || $val->SaleApprovalDateTime == '' ? $TransactionDateTime : date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $val->SaleApprovalDateTime)));
            $ClickDateTime = $val->ClickDateTime == '' ? $TransactionDateTime : date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $val->ClickDateTime)));
            $reg_date = date('Y-m-d', strtotime(str_replace('/', '-', $val->TransactionDateTime)));
            $dump[$reg_date][] = $SaleApprovalDateTime . "\t" . $TransactionDateTime . "\t" . $val->OrderAmount . "\t" . $val->AffiliateCommissionAmount . "\t" . $val->MerchantId . "\t" . $val->MerchantName . "\t" . $val->AffiliateSubId . "\t" . $val->TransactionId . "\t" . $ClickDateTime . "\t" . $val->TransactionId . "\t" . $val->ApprovalStatus. "\t" . $val->TransactionType. "\t" . $val->OriginURL;
        }
    }else{
        $val = $result->Transactions->Transaction;
        $TransactionDateTime = date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $val->TransactionDateTime)));
        $SaleApprovalDateTime = !isset($val->SaleApprovalDateTime) || $val->SaleApprovalDateTime == '' ? $TransactionDateTime : date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $val->SaleApprovalDateTime)));
        $ClickDateTime = $val->ClickDateTime == '' ? $TransactionDateTime : date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $val->ClickDateTime)));
        $reg_date = date('Y-m-d', strtotime(str_replace('/', '-', $val->TransactionDateTime)));
        $dump[$reg_date][] = $SaleApprovalDateTime . "\t" . $TransactionDateTime . "\t" . $val->OrderAmount . "\t" . $val->AffiliateCommissionAmount . "\t" . $val->MerchantId . "\t" . $val->MerchantName . "\t" . $val->AffiliateSubId     . "\t" . $val->TransactionId . "\t" . $ClickDateTime . "\t" . $val->TransactionId . "\t" . $val->ApprovalStatus . "\t" . $val->TransactionType. "\t" . $val->OriginURL;
    }

    foreach ($dump as $d => $v) {
 
        $cur_exr = cur_exchange($CURRENCY, 'USD', $d);
        $file_new = PATH_DATA . '/' . AFF_NAME . '/revenue_' . str_replace('-', '', $d) . '.upd';
        $fw = fopen($file_new, 'w');
        if (!$fw)
            continue;

        foreach ($v as $l) {
        
            $lr = explode("\t", $l);
            
            $created    = $lr[0];
            $updatetime = $lr[1];
            $sales = round($lr[2] * $cur_exr, 4);
            $commission = round($lr[3] * $cur_exr, 4);
            $idinaff = $lr[4];
            $programname = $lr[5];
            $sid = $lr[6];
            $orderid = $lr[7];
            $clicktime = $lr[8];
            $status = trim($lr[10]);
            $tradetype = $lr[11];
            $referrer = trim($lr[12]);
            $cancelreason = '';
            
            $replace_array = array(
                '{createtime}'      => $created,
                '{updatetime}'      => $updatetime,
                '{sales}'           => $sales,
                '{commission}'      => $commission,
                '{idinaff}'         => $idinaff,
                '{programname}'     => $programname,
                '{sid}'             => $sid,
                '{orderid}'         => $orderid,
                '{clicktime}'       => $clicktime,
                '{tradeid}'         => $orderid,
                '{tradestatus}'     => $status,
                '{oldcur}'          => $CURRENCY,
                '{oldsales}'        => $lr[2],
                '{oldcommission}'   => $lr[3],
                '{tradetype}'       => $tradetype,
                '{referrer}'        => $referrer,
                '{cancelreason}'    => $cancelreason,
            );
            fwrite($fw, strtr(FILE_FORMAT,$replace_array) . "\n");
            
        
        }
        fclose($fw);
    }
 
 
 
    
    
    
    
    
?>
