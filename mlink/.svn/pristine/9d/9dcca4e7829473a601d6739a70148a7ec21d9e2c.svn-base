<?php
#use TradeId
$file_cook = PATH_COOKIE.'/'.AFFILIATE_ALIAS.'.cookie';
$file_temp = PATH_TMP.'/'.AFFILIATE_ALIAS.'.tmp';

define('SECURITY_TOKEN', "dbec64f90d497bca3a139cc8403f752fab6a0ce75855811cc9c56ac1b02ec0f9");
define('PAYMENT_URL', 'http://api.pepperjamnetwork.com/20120402/publisher/report/payment?apiKey={TOKEN}&startDate={BEGIN_DATE}&endDate={END_DATE}&format=csv');
define('INVOICE_URL', 'http://api.pepperjamnetwork.com/20120402/publisher/report/payment-details?apiKey={TOKEN}&paymentId={paymentId}&format=csv');
define('REPORT_FIELDS', "transaction_id,order_id,sid,creative_type,commission,sale_amount,type,date,status,program_name,program_id,sub_type");

global $begin_dt,$end_dt;

if (defined('START_TIME') && defined('END_TIME')) {
    $end_dt = date('Y-m-d', strtotime(END_TIME));
    $begin_dt = date('Y-m-d', strtotime(START_TIME));
} else {
    $end_dt = date('Y-m-d');
    $begin_dt = date('Y-m-d', strtotime('-240 days', strtotime($end_dt)));
}

download_payments();
download_invoice();
echoMsg('crawl invoice end','crawl invoice','succ');
exit();

function download_invoice(){
    global $_db;
    
    $sql = 'SELECT * FROM payments_network_remit WHERE NetworkID = '.AFFID.' AND GetInvoice = "no"';
    $rows = $_db->getRows($sql);
    
    foreach($rows as $payments){
        _download_invoice($payments);
    }
}

function _download_invoice($payments){
    $file_temp = PATH_TMP.'/'.AFFILIATE_ALIAS.'.tmp';

    $replaceArr = array(
        '{TOKEN}'=>SECURITY_TOKEN,
        '{paymentId}'=>$payments['IDinNetwork'],
        );
    $url = strtr(INVOICE_URL,$replaceArr);

    _curl($url);

    $data_file = PATH_DATA.'/'.AFFILIATE_ALIAS.'_'.AFFID.'/'.$payments['ID'].'_'.date('ymd',strtotime($payments['Remit_date'])).'.dat';
    $fdw = fopen($data_file, 'w+');

    if(file_exists($file_temp)){
        $csv = array();
$count = 0;
$sum = 0;

        if (($handle = fopen($file_temp, "r")) !== FALSE) {
            $line = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $line++;

                if($line == 1){
                    if(strstr($data[0], 'payment_id') 
                        && strstr($data[1], 'transaction_id')
                        && strstr($data[2], 'order_id')
                        && strstr($data[3], 'sid')
                        && strstr($data[4], 'program_id')
                        && strstr($data[5], 'program_name')
                        && strstr($data[6], 'creative_type')
                        && strstr($data[7], 'commission')
                        && strstr($data[8], 'sale_amount')
                        && strstr($data[9], 'transaction_type')
                        && strstr($data[10], 'transaction_date')
                        && strstr($data[11], 'payment_date')
                    ){
                        continue;
                    }else{
                        print_r('error:title format wrong 1');
                        fclose($handle);
                        exit();
                    }
                }

                $tmp = array(
                    'date'=>trim($data[10]),
                    'transaction_id'=>$data[1],
                    'sales'=>$data[8],
                    'commission'=>$data[7],
                    'sid'=>$data[3],
                    'currency'=>$payments['Remit_currency'],
                    );
                $count++;
                $sum+=$data[7];

                fwrite($fdw , join("\t",$tmp)."\n");
            }

            fclose($handle);
        }
       
    }

    fclose($fdw);
    print_r("doing , payment_id= ".$payments['IDinNetwork'].", count= ".$count." ,sum = ".$sum." \n");
}

function download_payments(){
    global $begin_dt,$end_dt,$_db;
    $file_temp = PATH_TMP.'/'.AFFILIATE_ALIAS.'.tmp';

    $replaceArr = array(
        '{TOKEN}'=>SECURITY_TOKEN,
        '{BEGIN_DATE}'=>$begin_dt,
        '{END_DATE}'=>$end_dt,
        );
    $url = strtr(PAYMENT_URL,$replaceArr);

    _curl($url);

    if(file_exists($file_temp)){
        $csv = array();

        if (($handle = fopen($file_temp, "r")) !== FALSE) {
            $line = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $line++;

                if($line == 1){
                    if(strstr($data[0], 'payment_id') 
                        && strstr($data[1], 'method')
                        && strstr($data[2], 'notes')
                        && strstr($data[3], 'amount')
                        && strstr($data[4], 'date')
                    ){
                        continue;
                    }else{
                        print_r('error:title format wrong 1');
                        fclose($handle);
                        exit();
                    }
                }

                $date = date('Y-m-d',strtotime($data[4]));

                $tmp = array(
                    'NetworkID'=>AFFID,
                    'IDinNetwork'=>trim($data[0]),
                    'Remit_date'=>substr(trim($data[4]),0,10),
                    'Remit_currency'=>'USD',
                    'Remit_amount'=>str_replace(',','',trim($data[3])),
                    );
                $csv[] = $tmp;
            }

            fclose($handle);
        }
       
        $sql = getBatchUpdateSql($csv,'payments_network_remit','IDinNetwork,NetworkID');
        $_db->query($sql);
    }
}






function _curl($url,$post=array(),$return=false,$tmp_file_name=false){
    global $file_cook,$file_temp;

    $file = $tmp_file_name?$tmp_file_name:$file_temp;

    $fw = fopen($file, 'w+');

    print_r("curl :".$url."\n");
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER         , false);
    curl_setopt($ch, CURLOPT_NOBODY         , false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER , true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION , true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
    curl_setopt($ch, CURLOPT_COOKIEJAR      , $file_cook);
    curl_setopt($ch, CURLOPT_COOKIEFILE     , $file_cook);
    curl_setopt($ch, CURLOPT_USERAGENT      , 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2');
    curl_setopt($ch, CURLOPT_FILE           , $fw);
    curl_setopt($ch, CURLOPT_REFERER        , $url);

    if(!empty($post)){
        $post_tmp = array();
        foreach($post as $k=>$v){
            $post_tmp[] = $k.'='.urlencode($v);
        }
        $post_query = join('&',$post_tmp);
        curl_setopt($ch, CURLOPT_POST , true);
        curl_setopt($ch, CURLOPT_POSTFIELDS , $post_query);
        print_r("curl_post :".$post_query."\n");
    }

    $rs = curl_exec($ch);
    curl_close($ch);
    fclose($fw);

    if($return){
        return file_get_contents($file);
    }else{
        return $return;
    }
}

?>
