<?php
#use OrderId
ini_set("auto_detect_line_endings", true);
$file_cook = PATH_COOKIE.'/'.AFFILIATE_ALIAS.'.cookie';
$file_temp = PATH_TMP.'/'.AFFILIATE_ALIAS.'.tmp';


$res_login = login();
if(!$res_login){
    echoMsg('login fail','login','fail');exit();
}

download_payments();

echoMsg('crawl invoice start','crawl invoice','');
download_invoice();
echoMsg('crawl invoice end','crawl invoice','succ');
exit();


function download_invoice(){
    global $_db;
    
    $sql = 'SELECT * FROM payments_network_remit WHERE NetworkID = 2 AND GetInvoice = "no"';
    $rows = $_db->getRows($sql);

    foreach($rows as $payments){
        $invoiceids = _download_invoice_ad($payments);
        _download_invoice($invoiceids,$payments);
        print_r($payments);
    }
}

function _download_invoice($invoiceids,$payments){
    global $file_temp;
    $downloadInvoiceUrl = 'https://cli.linksynergy.com/cli/publisher/my_account/getPaymentInfo.php?reportType=trans&pinvid={pinvid}&mid={mid}&page=0&rows=0&download=1';

    $data_file = PATH_DATA.'/'.AFFILIATE_ALIAS.'_'.AFFID.'/'.$payments['ID'].'_'.date('ymd',strtotime($payments['Remit_date'])).'.dat';
    $fw = fopen($data_file, 'w+');

    $all = count($invoiceids);
    $i = 0;
    foreach($invoiceids as $invoice){
        $i++;
        $rep = array(
            '{pinvid}'=>$invoice['pinvid'],
            '{mid}'=>$invoice['mid'],
            );
        $url = strtr($downloadInvoiceUrl,$rep);
        _curl($url);

        if(file_exists($file_temp)){
        

$count = 0;
$sum = 0;
            if (($handle = fopen($file_temp, "r")) !== FALSE) {
                $line = 0;
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $line++;
                    if($line == 1){

                        if(
                            strstr($data[0], "Transaction Date") 
                            && strstr($data[1], "Advertiser ID") 
                            && strstr($data[2], "Advertiser Name") 
                            && strstr($data[3], "Order ID") 
                            && strstr($data[4], "SKU") 
                            && strstr($data[5], "Product Name") 
                            && strstr($data[6], "Items") 
                            && strstr($data[7], "Sales") 
                            && strstr($data[8], "Baseline Commission") 
                            && strstr($data[9], "Adjusted Commission") 
                            && strstr($data[10], "Commissions Earned") 
                            && strstr($data[11], "Hold/Deny Reason") 
                        ){
                            continue;
                        }else{
                            print_r('error:title format wrong 3');
                            fclose($handle);
                            exit();
                        }
                    }
                    
                    list($m,$d,$y) = explode('-',trim($data[0]));
                    $tmp = array(
                        'date'=>join('-',array($y,$m,$d)),
                        'orderid'=>$data[3],
                        'sales'=>$data[7],
                        'commission'=>$data[10],
                        'sid'=>'',
                        'currency'=>$payments['Remit_currency'],
                        );
                    $count++;
                    $sum+=$data[10];

                    fwrite($fw , join("\t",$tmp)."\n");
                }
                
            }
           
        }


        print_r("doing ($i/$all), invoiceID= ".$invoice['pinvid'].", count= ".$count." ,sum = ".$sum." \n");

    }
    fclose($fw);
}

function _download_invoice_ad($payments){
    $year = date('Y');
    $file_temp = PATH_TMP.'/'.AFFILIATE_ALIAS.'.'.$payments['IDinNetwork'].'.payments.tmp';
    $downloadInvoiceAdUrl = 'https://cli.linksynergy.com/cli/publisher/my_account/getPaymentInfo.php?reportType=invoice&pinvid='.$payments['IDinNetwork'].'&nid=0&mid=0&pstatus=0&download=1';
    _curl($downloadInvoiceAdUrl,array(),false,$file_temp);

    $csv = array();

    if(file_exists($file_temp)){
        

        if (($handle = fopen($file_temp, "r")) !== FALSE) {
            $line = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $line++;
                if($line == 1){

                    if(
                        strstr($data[0], "Invoice Date") 
                        && strstr($data[1], "Invoice ID") 
                        && strstr($data[2], "Advertiser ID") 
                        && strstr($data[3], "Advertiser Name") 
                        && strstr($data[4], "Advertiser Paid Date") 
                        && strstr($data[5], "Advertiser Authorization Date") 
                        && strstr($data[6], "Commissions Earned") 
                        && strstr($data[7], "Commissions Held") 
                        && strstr($data[8], "Commissions Carried Forward") 
                        && strstr($data[9], "Commission Denied") 
                        && strstr($data[10], "Net Bonus") 
                        && strstr($data[11], "CPC/CPM") 
                        && strstr($data[12], "VAT/GST") 
                        && strstr($data[13], "Invoice Amount") 
                        && strstr($data[14], "Invoice Currency") 
                        && strstr($data[15], "Payment Status") 
                    ){
                        continue;
                    }else{
                        print_r('error:title format wrong 2');
                        fclose($handle);
                        exit();
                    }
                }

                $csv[] = array(
                    'pinvid'=>$data[1],
                    'mid'=>$data[2],
                    );
            }

            fclose($handle);
        }
       
    }

    return $csv;
}

function download_payments(){
    global $_db;
    $objD = new DateTime();
    $nextMonth = $objD->modify('+1 month')->format('Y-m-01');
    $halfYearBefore = $objD->modify('-7 month')->format('Y-m-01');

    $file_temp = PATH_TMP.'/'.AFFILIATE_ALIAS.'.payments.tmp';
    #download payments record
    $downloadPaymentsUrl = 'https://cli.linksynergy.com/cli/publisher/my_account/getPaymentInfo.php?reportType=payment&pinvid=&bdate='.$halfYearBefore.'&edate='.$nextMonth.'&nid=0&mid=0&page=0&rows=0&download=1';
    _curl($downloadPaymentsUrl,array(),false,$file_temp);

    if(file_exists($file_temp)){
        $csv = array();

        if (($handle = fopen($file_temp, "r")) !== FALSE) {
            $line = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $line++;
                if($line == 1){
                    if(strstr($data[0], 'Payment ID') 
                        && strstr($data[1], 'Issue Date')
                        && strstr($data[2], 'Type')
                        && strstr($data[3], 'Reference ID')
                        && strstr($data[4], 'Currency')
                        && strstr($data[5], 'Payment Amount')
                        && strstr($data[6], 'Status')
                    ){
                        continue;
                    }else{
                        print_r('error:title format wrong 1');
                        fclose($handle);
                        exit();
                    }
                }

                $time = trim($data[1]);
                list($m,$d,$y) = explode('-',$time);
                $date = $y.'-'.$m.'-'.$d;

                $tmp = array(
                    'IDinNetwork'=>trim($data[0]),
                    'NetworkID'=>AFFID,
                    'Remit_date'=>$date,
                    'Remit_currency'=>trim($data[4]),
                    'Remit_amount'=>str_replace(',','',trim($data[5])),
                    );
                $csv[] = $tmp;
            }

            fclose($handle);
        }
       
    }

    $sql = getBatchUpdateSql($csv,'payments_network_remit','NetworkID,IDinNetwork');
    $_db->query($sql);
}




function login(){
    /**
    lt=LT-330096-B72WdNRHqq4c3RHaY4E6c2GPagJaQ7
    &execution=e1s1
    &_eventId=submit
    &HEALTHCHECK=HEALTHCHECK+PASSED.
    &username=couponsnap
    &password=OWR70Ov8%26F
    &login=Log+In
    */
    $LoginUrl = 'https://login.linkshare.com/sso/login?service=http%3A%2F%2Fcli.linksynergy.com%2Fcli%2Fpublisher%2Fhome.php';
    $loginHtml = _curl($LoginUrl,'',1);

    $postArr = array();
    if(preg_match('/<input.*?name="lt".*?value="([^"]*)"/', $loginHtml,$m)){
        $postArr['lt'] = $m[1];
    }

    if(preg_match('/<input.*?name="execution".*?value="([^"]*)"/', $loginHtml,$m)){
        $postArr['execution'] = $m[1];
    }

    if(preg_match('/<input.*?name="_eventId".*?value="([^"]*)"/', $loginHtml,$m)){
        $postArr['_eventId'] = $m[1];
    }

    if(preg_match('/<input.*?name="HEALTHCHECK".*?value="([^"]*)"/', $loginHtml,$m)){
        $postArr['HEALTHCHECK'] = $m[1];
    }

    $postArr['username'] = AFFILIATE_USER;#AFFILIATE_USER;
    $postArr['password'] = AFFILIATE_PASS;#AFFILIATE_PASS;
    $postArr['login'] = 'Log In';

    $loginHtml = _curl($LoginUrl,$postArr,1);

    $keywords = '3310876';
    if(strpos($loginHtml,$keywords)){
        return true;
    }else{
        return false;
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
